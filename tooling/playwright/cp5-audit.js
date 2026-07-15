/* eslint-disable import/no-extraneous-dependencies, no-console */
const { chromium } = require( 'playwright' );
const { execFileSync } = require( 'child_process' );
const path = require( 'path' );

const repoRoot = path.resolve( __dirname, '..', '..' );
const baseUrl = process.env.WP_BASE_URL || 'http://localhost:8888';
const fallbackComposeFile = process.env.WP_ENV_COMPOSE_FILE || '';

function runWp( args ) {
	if ( fallbackComposeFile ) {
		return execFileSync(
			'docker',
			[
				'compose',
				'-f',
				fallbackComposeFile,
				'exec',
				'-T',
				'cli',
				'wp',
				...args,
			],
			{
				cwd: repoRoot,
				encoding: 'utf8',
				maxBuffer: 10 * 1024 * 1024,
			}
		);
	}

	return execFileSync( 'npm', [ 'run', 'env:cli', '--', 'wp', ...args ], {
		cwd: repoRoot,
		encoding: 'utf8',
		maxBuffer: 10 * 1024 * 1024,
	} );
}

function runWpEval( code ) {
	return runWp( [ 'eval', code ] );
}

function parseJsonOutput( output ) {
	const lines = String( output )
		.trim()
		.split( /\r?\n/ )
		.map( ( line ) => line.trim() )
		.filter( Boolean );

	return JSON.parse( lines[ lines.length - 1 ] );
}

function updateSettings( patch ) {
	const encoded = JSON.stringify( JSON.stringify( patch ) );
	runWpEval(
		`$current = get_option( 'agency_commerce_core_settings', array() );
		if ( ! is_array( $current ) ) { $current = array(); }
		$patch = json_decode( ${ encoded }, true );
		$merge = function( $base, $overlay ) use ( &$merge ) {
			foreach ( $overlay as $key => $value ) {
				$base[ $key ] = is_array( $value ) && isset( $base[ $key ] ) && is_array( $base[ $key ] ) ? $merge( $base[ $key ], $value ) : $value;
			}
			return $base;
		};
		update_option( 'agency_commerce_core_settings', $merge( $current, $patch ), false );`
	);
}

function seedAuditFixtures() {
	updateSettings( {
		checkout: {
			mode: 'block',
			support_text: 'Audit support context.',
			trust_text: 'Audit trust context.',
			minimum_order_amount: 0,
			enable_cod_controls: true,
			cod_enabled: true,
			cod_instructions: 'Audit COD instructions.',
		},
		recently_viewed: { enabled: true, count: 4 },
	} );

	return parseJsonOutput(
		runWpEval(
			`if ( ! class_exists( 'WooCommerce' ) ) {
				echo wp_json_encode( array( 'skipped' => true ) );
				return;
			}
			if ( class_exists( 'WC_Install' ) ) {
				WC_Install::create_pages();
			}
			update_option( 'woocommerce_enable_guest_checkout', 'yes', false );
			update_option( 'woocommerce_cod_settings', array( 'enabled' => 'yes', 'title' => 'Cash on delivery' ), false );
			update_option( 'woocommerce_bacs_settings', array( 'enabled' => 'yes', 'title' => 'Direct bank transfer' ), false );
			$zone = new WC_Shipping_Zone( 0 );
			$has_flat_rate = false;
			foreach ( $zone->get_shipping_methods() as $method ) {
				if ( 'flat_rate' === $method->id ) {
					$has_flat_rate = true;
				}
			}
			if ( ! $has_flat_rate ) {
				$zone->add_shipping_method( 'flat_rate' );
			}

			$product_id = wc_get_product_id_by_sku( 'cp5-audit-simple' );
			if ( ! $product_id ) {
				$product = new WC_Product_Simple();
				$product->set_name( 'CP5 Audit Simple Product' );
				$product->set_sku( 'cp5-audit-simple' );
				$product->set_regular_price( '25' );
				$product->set_status( 'publish' );
				$product->set_catalog_visibility( 'visible' );
				$product->set_manage_stock( false );
				$product_id = $product->save();
			}

			$private_id = wc_get_product_id_by_sku( 'cp5-audit-private' );
			if ( ! $private_id ) {
				$product = new WC_Product_Simple();
				$product->set_name( 'CP5 Audit Private Product' );
				$product->set_sku( 'cp5-audit-private' );
				$product->set_regular_price( '40' );
				$product->set_status( 'private' );
				$product->set_catalog_visibility( 'visible' );
				$private_id = $product->save();
			}

			$coupon = new WP_Query( array(
				'post_type' => 'shop_coupon',
				'post_status' => 'any',
				'title' => 'CP5 Audit Coupon',
				'fields' => 'ids',
				'posts_per_page' => 1,
			) );
			if ( ! $coupon->have_posts() ) {
				wp_insert_post( array(
					'post_type' => 'shop_coupon',
					'post_status' => 'publish',
					'post_title' => 'CP5 Audit Coupon',
					'meta_input' => array(
						'discount_type' => 'fixed_cart',
						'coupon_amount' => '1',
					),
				) );
			}

			echo wp_json_encode( array(
				'productId' => $product_id,
				'privateId' => $private_id,
				'cartId' => wc_get_page_id( 'cart' ),
				'checkoutId' => wc_get_page_id( 'checkout' ),
			) );`
		)
	);
}

function verifyCheckoutModeSafety() {
	const result = parseJsonOutput(
		runWpEval(
			`if ( ! class_exists( 'AgencyCommerceCore\\\\Checkout\\\\ModeManager' ) || ! function_exists( 'wc_get_page_id' ) ) {
				echo wp_json_encode( array( 'skipped' => true ) );
				return;
			}
			$cart = wc_get_page_id( 'cart' );
			$checkout = wc_get_page_id( 'checkout' );
			$cart_method = new ReflectionMethod( 'WC_Install', 'get_cart_block_content' );
			$cart_method->setAccessible( true );
			$checkout_method = new ReflectionMethod( 'WC_Install', 'get_checkout_block_content' );
			$checkout_method->setAccessible( true );
			$original_cart = $cart_method->invoke( null );
			$original_checkout = $checkout_method->invoke( null );
			foreach ( array( $cart => $original_cart, $checkout => $original_checkout ) as $page_id => $content ) {
				wp_update_post( array( 'ID' => $page_id, 'post_content' => $content ) );
				delete_post_meta( $page_id, '_agency_commerce_core_block_content_backup' );
				delete_post_meta( $page_id, '_agency_commerce_core_latest_block_content_backup' );
				delete_post_meta( $page_id, '_agency_commerce_core_classic_content_backup' );
			}
			$manager = new AgencyCommerceCore\\Checkout\\ModeManager();
			$manager->apply_mode( 'classic' );
			$manager->apply_mode( 'block' );
			$manager->apply_mode( 'classic' );
			$manager->apply_mode( 'block' );
			$manual_checkout = '<!-- wp:paragraph --><p>Manual checkout edit</p><!-- /wp:paragraph -->';
			wp_update_post( array( 'ID' => $checkout, 'post_content' => $manual_checkout ) );
			$manager->apply_mode( 'classic' );
			$manager->apply_mode( 'block' );
			$content_after_manual_restore = get_post_field( 'post_content', $checkout );
			wp_update_post( array( 'ID' => $checkout, 'post_content' => $original_checkout ) );
			$manager->ensure_configured_mode();
			$non_forced_block_content_preserved = get_post_field( 'post_content', $checkout ) === $original_checkout;
			$manager->apply_mode( 'invalid-mode' );

			$option_before = get_option( 'agency_commerce_core_settings', array() );
			$settings_before = agency_commerce_core_get_settings( true );
			$user_id = wp_insert_user( array(
				'user_login' => 'cp5_audit_subscriber_' . wp_generate_password( 6, false ),
				'user_pass' => wp_generate_password(),
				'role' => 'subscriber',
			) );
			wp_set_current_user( $user_id );
			$_SERVER['REQUEST_METHOD'] = 'POST';
			$_POST['option_page'] = AgencyCommerceCore\\Settings\\Registry::OPTION_GROUP;
			$_POST['_agency_commerce_core_tab'] = 'checkout';
			$_POST[ AgencyCommerceCore\\Settings\\Registry::OPTION_NAME ] = array( 'checkout' => array( 'mode' => 'classic' ) );
			$sanitized = AgencyCommerceCore\\Settings\\Sanitizer::sanitize( $_POST[ AgencyCommerceCore\\Settings\\Registry::OPTION_NAME ] );

			$stored = $option_before;
			$stored['checkout']['mode'] = 'unsupported';
			update_option( 'agency_commerce_core_settings', $stored, false );
			$stored_mode = agency_commerce_core_get_setting( 'checkout.mode', 'block' );
			update_option( 'agency_commerce_core_settings', $option_before, false );

			$old_cart_page = get_option( 'woocommerce_cart_page_id' );
			update_option( 'woocommerce_cart_page_id', 0, false );
			$manager->apply_mode( 'classic' );
			update_option( 'woocommerce_cart_page_id', $old_cart_page, false );

			echo wp_json_encode( array(
				'cartContent' => get_post_field( 'post_content', $cart ),
				'checkoutManualRestored' => $content_after_manual_restore === $manual_checkout,
				'nonForcedBlockContentPreserved' => $non_forced_block_content_preserved,
				'cartOriginalBackup' => get_post_meta( $cart, '_agency_commerce_core_block_content_backup', true ) === $original_cart,
				'checkoutOriginalBackup' => get_post_meta( $checkout, '_agency_commerce_core_block_content_backup', true ) === $original_checkout,
				'checkoutLatestBackup' => get_post_meta( $checkout, '_agency_commerce_core_latest_block_content_backup', true ) === $manual_checkout,
				'unauthorizedPreserved' => isset( $sanitized['checkout']['mode'], $settings_before['checkout']['mode'] ) && $sanitized['checkout']['mode'] === $settings_before['checkout']['mode'],
				'invalidStoredModeFallback' => 'block' === $stored_mode,
			) );`
		)
	);

	if (
		! result.cartOriginalBackup ||
		! result.checkoutOriginalBackup ||
		! result.checkoutLatestBackup ||
		! result.checkoutManualRestored ||
		! result.nonForcedBlockContentPreserved ||
		! result.unauthorizedPreserved ||
		! result.invalidStoredModeFallback
	) {
		throw new Error(
			`Checkout mode safety failed: ${ JSON.stringify( result ) }`
		);
	}
}

async function verifyRecentlyViewedEndpoint( request, fixtures ) {
	const endpoint = `${ baseUrl }/wp-json/agency-commerce-core/v1/recently-viewed`;
	const valid = await request.post( endpoint, {
		data: {
			ids: [
				fixtures.privateId,
				fixtures.productId,
				fixtures.productId,
				99999999,
			],
		},
	} );
	const validPayload = await valid.json();

	if (
		! valid.ok() ||
		! validPayload.html.includes( 'CP5 Audit Simple Product' )
	) {
		throw new Error(
			'Recently viewed endpoint did not render public product.'
		);
	}
	if ( validPayload.html.includes( 'CP5 Audit Private Product' ) ) {
		throw new Error( 'Recently viewed endpoint leaked a private product.' );
	}

	const malformed = await request.post( endpoint, {
		data: { ids: [ fixtures.productId, 'bad-id' ] },
	} );
	if ( 400 !== malformed.status() ) {
		throw new Error( 'Malformed recently viewed IDs were not rejected.' );
	}

	const excessive = await request.post( endpoint, {
		data: { ids: Array.from( { length: 25 }, ( _, index ) => index + 1 ) },
	} );
	if ( 400 !== excessive.status() ) {
		throw new Error( 'Excessive recently viewed IDs were not rejected.' );
	}

	const wooInactive = parseJsonOutput(
		runWpEval(
			`require_once ABSPATH . 'wp-admin/includes/plugin.php';
			deactivate_plugins( 'woocommerce/woocommerce.php' );
			echo wp_json_encode( array( 'active' => is_plugin_active( 'woocommerce/woocommerce.php' ) ) );`
		)
	);
	const inactiveResponse = await request.post( endpoint, {
		data: { ids: [ fixtures.productId ] },
	} );
	const inactivePayload = await inactiveResponse.json();
	runWp( [ 'plugin', 'activate', 'woocommerce' ] );

	if (
		wooInactive.active ||
		! inactiveResponse.ok() ||
		'' !== inactivePayload.html
	) {
		throw new Error(
			'Recently viewed endpoint did not degrade safely without WooCommerce.'
		);
	}
}

async function addProductToCart( page, productId ) {
	await page.goto( `${ baseUrl }/?add-to-cart=${ productId }`, {
		waitUntil: 'networkidle',
	} );
}

async function verifyClassicCheckout( page, productId ) {
	updateSettings( {
		checkout: {
			mode: 'classic',
			minimum_order_amount: 1000,
			enable_cod_controls: true,
			cod_enabled: true,
		},
	} );
	await addProductToCart( page, productId );
	await page.goto( `${ baseUrl }/checkout/`, { waitUntil: 'networkidle' } );

	if ( ! ( await page.locator( '.woocommerce-checkout' ).count() ) ) {
		throw new Error( 'Classic checkout surface was not rendered.' );
	}

	await page.locator( '#place_order' ).click();
	await page.waitForSelector(
		'.woocommerce-error, .woocommerce-NoticeGroup'
	);

	await page.locator( '#billing_first_name' ).fill( 'CP5' );
	await page.locator( '#billing_last_name' ).fill( 'Audit' );
	await page.locator( '#billing_address_1' ).fill( '1 Audit Street' );
	await page.locator( '#billing_city' ).fill( 'Audit City' );
	await page.locator( '#billing_postcode' ).fill( '12345' );
	await page.locator( '#billing_phone' ).fill( '5555555555' );
	await page.locator( '#billing_email' ).fill( 'cp5-audit@example.test' );

	await page.locator( '#place_order' ).click();
	await page.waitForSelector( '.woocommerce-error' );
	if (
		! ( await page.locator( '.woocommerce-error' ).innerText() ).includes(
			'minimum order total'
		)
	) {
		throw new Error(
			'Classic checkout did not reject below-minimum order.'
		);
	}

	updateSettings( { checkout: { minimum_order_amount: 0 } } );
	await page.reload( { waitUntil: 'networkidle' } );

	if ( await page.locator( '#payment_method_bacs' ).count() ) {
		await page.locator( '#payment_method_bacs' ).isVisible();
	}
	if ( await page.locator( '#payment_method_cod' ).count() ) {
		await page.locator( '#payment_method_cod' ).check();
	}
	if ( await page.locator( '#terms' ).count() ) {
		await page.locator( '#terms' ).check();
	}

	await page.locator( '#place_order' ).click();
	await page.waitForURL( /order-received/, { timeout: 30000 } );

	const order = parseJsonOutput(
		runWpEval(
			`$orders = wc_get_orders( array( 'limit' => 1, 'orderby' => 'date', 'order' => 'DESC' ) );
			$order = $orders ? $orders[0] : null;
			echo wp_json_encode( array(
				'orderId' => $order ? $order->get_id() : 0,
				'paymentMethod' => $order ? $order->get_payment_method() : '',
				'supportMeta' => $order ? $order->get_meta( '_agency_commerce_checkout_support_text' ) : '',
			) );`
		)
	);

	if (
		! order.orderId ||
		'cod' !== order.paymentMethod ||
		! order.supportMeta
	) {
		throw new Error(
			`Classic checkout order verification failed: ${ JSON.stringify(
				order
			) }`
		);
	}

	updateSettings( { checkout: { mode: 'block', minimum_order_amount: 0 } } );
}

function restoreCheckoutPages() {
	runWpEval(
		`if ( function_exists( 'wc_get_page_id' ) ) {
			$cart_content = '<!-- wp:woocommerce/cart /-->';
			$checkout_content = '<!-- wp:woocommerce/checkout /-->';
			if ( class_exists( 'WC_Install' ) ) {
				$cart_method = new ReflectionMethod( 'WC_Install', 'get_cart_block_content' );
				$cart_method->setAccessible( true );
				$checkout_method = new ReflectionMethod( 'WC_Install', 'get_checkout_block_content' );
				$checkout_method->setAccessible( true );
				$cart_content = $cart_method->invoke( null );
				$checkout_content = $checkout_method->invoke( null );
			}
			$cart = wc_get_page_id( 'cart' );
			$checkout = wc_get_page_id( 'checkout' );
			if ( $cart > 0 ) {
				wp_update_post( array( 'ID' => $cart, 'post_content' => $cart_content ) );
			}
			if ( $checkout > 0 ) {
				wp_update_post( array( 'ID' => $checkout, 'post_content' => $checkout_content ) );
			}
		}`
	);
	updateSettings( { checkout: { mode: 'block', minimum_order_amount: 0 } } );
}

async function main() {
	const fixtures = seedAuditFixtures();
	if ( fixtures.skipped ) {
		console.log( 'CP5 audit skipped: WooCommerce is unavailable.' );
		return;
	}

	verifyCheckoutModeSafety();

	const browser = await chromium.launch();
	try {
		const page = await browser.newPage( {
			viewport: { width: 1280, height: 900 },
		} );
		await verifyRecentlyViewedEndpoint( page.request, fixtures );
		await verifyClassicCheckout( page, fixtures.productId );
		console.log( 'CP5 audit checks passed.' );
	} finally {
		await browser.close();
		restoreCheckoutPages();
	}
}

main().catch( ( error ) => {
	console.error( error );
	process.exitCode = 1;
} );

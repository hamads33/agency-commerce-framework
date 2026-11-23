/* eslint-disable import/no-extraneous-dependencies, no-console */
const { chromium } = require( 'playwright' );
const { execFileSync } = require( 'child_process' );
const path = require( 'path' );
const { randomUUID } = require( 'node:crypto' );

const repoRoot = path.resolve( __dirname, '..', '..' );
const baseUrl = process.env.WP_BASE_URL || 'http://localhost:8888';
const suite = process.env.CP5_SUITE || 'homepage';
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

function deepMerge( target, patch ) {
	if ( Array.isArray( patch ) ) {
		return patch.slice();
	}

	if ( patch && 'object' === typeof patch ) {
		const output = { ...( target || {} ) };

		Object.keys( patch ).forEach( ( key ) => {
			output[ key ] = deepMerge( output[ key ], patch[ key ] );
		} );

		return output;
	}

	return patch;
}

function readOption( name ) {
	try {
		return parseJsonOutput(
			runWp( [ 'option', 'get', name, '--format=json' ] )
		);
	} catch {
		return {};
	}
}

function updateSettings( patch ) {
	const current = readOption( 'agency_commerce_core_settings' );
	const next = deepMerge( current, patch );
	const encoded = JSON.stringify( JSON.stringify( next ) );

	runWpEval(
		`update_option( 'agency_commerce_core_settings', json_decode( ${ encoded }, true ), false );`
	);
}

function seedFixtures() {
	const productId = parseJsonOutput(
		runWpEval(
			`$htaccess = ABSPATH . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents(
				$htaccess,
				"# BEGIN WordPress\\n<IfModule mod_rewrite.c>\\nRewriteEngine On\\nRewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\\nRewriteBase /\\nRewriteRule ^index\\\\.php$ - [L]\\nRewriteCond %{REQUEST_FILENAME} !-f\\nRewriteCond %{REQUEST_FILENAME} !-d\\nRewriteRule . /index.php [L]\\n</IfModule>\\n# END WordPress\\n"
			);
		}
		$upload = wp_upload_dir();
		if ( empty( $upload['error'] ) ) {
			$source = WP_PLUGIN_DIR . '/woocommerce/assets/images/placeholder.webp';
			if ( file_exists( $source ) ) {
				foreach ( array( 'woocommerce-placeholder.webp', 'woocommerce-placeholder-480x480.webp', 'woocommerce-placeholder-160x160.webp', 'woocommerce-placeholder-720x720.webp', 'woocommerce-placeholder-300x300.webp', 'woocommerce-placeholder-1024x1024.webp', 'woocommerce-placeholder-150x150.webp', 'woocommerce-placeholder-768x768.webp' ) as $filename ) {
					$target = trailingslashit( $upload['basedir'] ) . $filename;
					if ( ! file_exists( $target ) ) {
						copy( $source, $target );
					}
				}
			}
		}
		if ( class_exists( 'WooCommerce' ) ) {
			if ( class_exists( 'WC_Install' ) ) {
				WC_Install::create_pages();
			}
			update_option( 'woocommerce_enable_guest_checkout', 'yes', false );
			update_option( 'woocommerce_cod_settings', array( 'enabled' => 'yes', 'title' => 'Cash on delivery' ), false );
			update_option( 'woocommerce_bacs_settings', array( 'enabled' => 'yes', 'title' => 'Direct bank transfer' ), false );

			$zone = new WC_Shipping_Zone( 0 );
			$methods = $zone->get_shipping_methods();
			$has_flat_rate = false;
			foreach ( $methods as $method ) {
				if ( 'flat_rate' === $method->id ) {
					$has_flat_rate = true;
				}
			}
			if ( ! $has_flat_rate ) {
				$zone->add_shipping_method( 'flat_rate' );
			}

			if ( ! get_page_by_path( 'cart' ) || ! get_page_by_path( 'checkout' ) || ! get_page_by_path( 'my-account' ) ) {
				WC_Install::create_pages();
			}

			if ( ! get_page_by_path( 'cp5-home' ) ) {
				$home_id = wp_insert_post( array(
					'post_type' => 'page',
					'post_status' => 'publish',
					'post_name' => 'cp5-home',
					'post_title' => 'CP5 Home',
					'post_content' => '<!-- wp:pattern {"slug":"agency-commerce/minimal-homepage"} /-->',
				) );
			} else {
				$home_id = get_page_by_path( 'cp5-home' )->ID;
			}
			update_option( 'page_on_front', $home_id, false );
			update_option( 'show_on_front', 'page', false );

			if ( ! get_page_by_path( 'cp5-privacy-fixture' ) ) {
				wp_insert_post( array(
					'post_type' => 'page',
					'post_status' => 'publish',
					'post_name' => 'cp5-privacy-fixture',
					'post_title' => 'CP5 Privacy Fixture',
					'post_content' => 'Normal WordPress content remains searchable.',
				) );
			}

			$existing_coupon = new WP_Query( array(
				'post_type' => 'shop_coupon',
				'post_status' => 'any',
				'title' => 'CP5 Test Coupon',
				'fields' => 'ids',
				'posts_per_page' => 1,
			) );
			if ( ! $existing_coupon->have_posts() ) {
				wp_insert_post( array(
					'post_type' => 'shop_coupon',
					'post_status' => 'publish',
					'post_title' => 'CP5 Test Coupon',
					'post_excerpt' => 'Development-only coupon fixture.',
					'meta_input' => array(
						'discount_type' => 'fixed_cart',
						'coupon_amount' => '1',
						'individual_use' => 'no',
					),
				) );
			}
			$product_id = wc_get_product_id_by_sku( 'agency-cp5-cart-fixture' );
			if ( ! $product_id ) {
				$product = new WC_Product_Simple();
				$product->set_name( 'CP5 Cart Fixture' );
				$product->set_sku( 'agency-cp5-cart-fixture' );
				$product->set_regular_price( '25' );
				$product->set_status( 'publish' );
				$product->set_virtual( true );
				$product_id = $product->save();
			}
			echo wp_json_encode( $product_id );
		} else {
			WP_CLI::error( 'WooCommerce must be active for CP5 commerce tests.' );
		}`
		)
	);

	updateSettings( {
		cart: {
			enable_mini_cart: true,
			show_continue_shopping: true,
		},
		checkout: {
			mode: 'block',
			support_text:
				'Need help? Contact store support before placing your order.',
			trust_text: 'Secure checkout powered by WooCommerce.',
			optional_company: true,
			optional_address_2: true,
			optional_order_notes: true,
			optional_postcode: false,
			minimum_order_amount: 0,
			enable_cod_controls: true,
			cod_enabled: true,
			cod_instructions: 'Pay with cash when the order is delivered.',
		},
		recently_viewed: {
			enabled: true,
			count: 4,
		},
	} );
	return productId;
}

function attachErrorCollectors( page ) {
	const errors = [];

	page.on( 'pageerror', ( error ) =>
		errors.push( `Page error: ${ error.message }` )
	);
	page.on( 'console', ( message ) => {
		if ( 'error' === message.type() ) {
			errors.push( `Console error: ${ message.text() }` );
		}
	} );

	return {
		assertClean( context ) {
			if ( errors.length ) {
				throw new Error(
					`${ context } browser errors:\n${ errors.join( '\n' ) }`
				);
			}
		},
	};
}

async function assertNoPhpWarnings( page, context ) {
	const bodyText = await page.locator( 'body' ).innerText();

	if (
		/(?:PHP\s+)?(?:Warning|Notice|Deprecated|Fatal error)\s*:/i.test(
			bodyText
		)
	) {
		throw new Error(
			`PHP warning text found while checking ${ context }.`
		);
	}
}

async function load( page, url, context ) {
	await page.goto( url, { waitUntil: 'networkidle' } );
	await assertNoPhpWarnings( page, context );
}

async function addProductToCart( page, productId ) {
	await page.goto( `${ baseUrl }/cart/`, { waitUntil: 'networkidle' } );
	const result = await page.evaluate( async ( id ) => {
		const nonce = window.wcBlocksMiddlewareConfig?.storeApiNonce;
		const response = await window.fetch(
			'/wp-json/wc/store/v1/cart/add-item',
			{
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					Nonce: nonce,
				},
				body: JSON.stringify( { id, quantity: 1 } ),
			}
		);

		return {
			ok: response.ok,
			status: response.status,
			text: await response.text(),
		};
	}, productId );

	if ( ! result.ok ) {
		throw new Error(
			`Store API add-item failed with ${ result.status }: ${ result.text }`
		);
	}
}

async function verifyHomepage( page ) {
	const patterns = parseJsonOutput(
		runWpEval(
			`$registry = WP_Block_Patterns_Registry::get_instance();
			echo wp_json_encode( array(
				'fashion' => $registry->is_registered( 'agency-commerce/fashion-homepage' ),
				'electronics' => $registry->is_registered( 'agency-commerce/electronics-homepage' ),
				'minimal' => $registry->is_registered( 'agency-commerce/minimal-homepage' ),
			) );`
		)
	);

	if ( ! patterns.fashion || ! patterns.electronics || ! patterns.minimal ) {
		throw new Error(
			`Homepage patterns are not registered: ${ JSON.stringify(
				patterns
			) }`
		);
	}

	await load( page, `${ baseUrl }/`, 'homepage' );

	if ( ! ( await page.locator( '.ac-home-hero' ).count() ) ) {
		throw new Error( 'Expected homepage hero block output.' );
	}
}

async function verifyCart( page, productId ) {
	await load( page, `${ baseUrl }/cart/`, 'empty cart' );

	await addProductToCart( page, productId );
	await load( page, `${ baseUrl }/cart/`, 'populated cart' );
	await page.waitForSelector( '.wc-block-components-product-name' );
	await page.waitForSelector(
		'input[type="number"], .wc-block-components-quantity-selector button, input.qty'
	);

	const hasCartSurface = await page
		.locator( '.wp-block-woocommerce-cart, .woocommerce-cart-form' )
		.count();
	if ( ! hasCartSurface ) {
		throw new Error( 'Expected block or classic cart surface.' );
	}

	const hasQuantity = await page
		.locator(
			'input[type="number"], .wc-block-components-quantity-selector button, input.qty'
		)
		.count();
	if ( ! hasQuantity ) {
		throw new Error( 'Expected cart quantity control.' );
	}
}

async function verifyCheckoutBlock( page, productId ) {
	updateSettings( { checkout: { mode: 'block', minimum_order_amount: 0 } } );
	await addProductToCart( page, productId );
	await load( page, `${ baseUrl }/checkout/`, 'block checkout' );
	await page.waitForSelector( '.wp-block-woocommerce-checkout' );

	if (
		! ( await page.locator( '.wp-block-woocommerce-checkout' ).count() )
	) {
		throw new Error( 'Expected WooCommerce Checkout block.' );
	}

	if ( ! ( await page.locator( '.ac-checkout-messages' ).count() ) ) {
		throw new Error( 'Expected checkout support/trust messaging.' );
	}
}

async function verifyCheckoutClassic( page ) {
	updateSettings( {
		checkout: { mode: 'classic', minimum_order_amount: 0 },
	} );
	const pageState = parseJsonOutput(
		runWpEval(
			`$cart = wc_get_page_id( 'cart' );
			$checkout = wc_get_page_id( 'checkout' );
			echo wp_json_encode( array(
				'cartHasShortcode' => $cart > 0 && has_shortcode( get_post_field( 'post_content', $cart ), 'woocommerce_cart' ),
				'checkoutHasShortcode' => $checkout > 0 && has_shortcode( get_post_field( 'post_content', $checkout ), 'woocommerce_checkout' ),
				'cartBackup' => $cart > 0 && '' !== get_post_meta( $cart, '_agency_commerce_core_block_content_backup', true ),
				'checkoutBackup' => $checkout > 0 && '' !== get_post_meta( $checkout, '_agency_commerce_core_block_content_backup', true ),
			) );`
		)
	);

	if ( ! pageState.cartHasShortcode || ! pageState.checkoutHasShortcode ) {
		throw new Error(
			`Classic mode did not switch to shortcodes: ${ JSON.stringify(
				pageState
			) }`
		);
	}

	await load( page, `${ baseUrl }/cart/`, 'classic cart' );
	await load( page, `${ baseUrl }/checkout/`, 'classic checkout' );
	await assertNoPhpWarnings( page, 'classic checkout' );

	updateSettings( { checkout: { mode: 'block' } } );
}

async function verifyAccount( page ) {
	await load( page, `${ baseUrl }/my-account/`, 'my account' );

	const hasAccountSurface = await page
		.locator( '.woocommerce-account, .woocommerce-form-login' )
		.count();
	if ( ! hasAccountSurface ) {
		throw new Error( 'Expected WooCommerce My Account surface.' );
	}

	const registrationSettings = {
		woocommerce_enable_myaccount_registration: 'yes',
		woocommerce_registration_generate_username: 'yes',
		woocommerce_registration_generate_password: 'no',
	};
	const previous = parseJsonOutput(
		runWpEval( `$settings = json_decode( '${ JSON.stringify(
			registrationSettings
		) }', true );
		$previous = array();
		foreach ( $settings as $name => $value ) {
			$previous[$name] = get_option( $name );
			update_option( $name, $value );
		}
		echo wp_json_encode( $previous );` )
	);
	const email = `agency-account-${ randomUUID() }@example.test`;
	const password = `LocalFixture!${ randomUUID() }`;
	try {
		await load( page, `${ baseUrl }/my-account/`, 'account registration' );
		const register = page.locator( '.woocommerce-form-register' );
		await register.locator( '[name="email"]' ).fill( email );
		await register
			.locator( '[name="password"]' )
			.pressSequentially( password );
		await register.locator( '[name="register"]' ).click();
		await page
			.locator( '.woocommerce-MyAccount-navigation, .woocommerce-error' )
			.first()
			.waitFor();
		if ( await page.locator( '.woocommerce-error' ).count() ) {
			throw new Error(
				await page.locator( '.woocommerce-error' ).first().innerText()
			);
		}

		const user = parseJsonOutput(
			runWpEval( `$user = get_user_by( 'email', '${ email }' );
			echo wp_json_encode( $user ? array( 'roles' => $user->roles, 'email' => $user->user_email ) : null );` )
		);
		if ( user?.email !== email || ! user.roles.includes( 'customer' ) ) {
			throw new Error(
				'Registration did not persist a WooCommerce customer.'
			);
		}

		await page.context().clearCookies();
		await load( page, `${ baseUrl }/my-account/`, 'customer login' );
		const login = page.locator( '.woocommerce-form-login' );
		await login.locator( '[name="username"]' ).fill( email );
		await login.locator( '[name="password"]' ).fill( password );
		await login.locator( '[name="login"]' ).click();
		await page.locator( '.woocommerce-MyAccount-navigation' ).waitFor();
	} finally {
		runWpEval( `$settings = json_decode( '${ JSON.stringify(
			previous
		) }', true );
		foreach ( $settings as $name => $value ) {
			if ( false === $value ) {
				delete_option( $name );
			} else {
				update_option( $name, $value );
			}
		}
		$user = get_user_by( 'email', '${ email }' );
		if ( $user ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			wp_delete_user( $user->ID );
		}` );
	}
}

async function main() {
	const productId = seedFixtures();

	const browser = await chromium.launch();
	try {
		const page = await browser.newPage( {
			viewport: { width: 1280, height: 900 },
		} );
		const collectors = attachErrorCollectors( page );

		if ( 'homepage' === suite ) {
			await verifyHomepage( page );
		} else if ( 'cart' === suite ) {
			await verifyCart( page, productId );
		} else if ( 'checkout-block' === suite ) {
			await verifyCheckoutBlock( page, productId );
		} else if ( 'checkout-classic' === suite ) {
			await verifyCheckoutClassic( page );
		} else if ( 'account' === suite ) {
			await verifyAccount( page );
		} else {
			throw new Error( `Unknown CP5 suite: ${ suite }` );
		}

		collectors.assertClean( suite );
		console.log( `CP5 ${ suite } checks passed.` );
	} finally {
		await browser.close();
	}
}

main().catch( ( error ) => {
	console.error( error );
	process.exitCode = 1;
} );

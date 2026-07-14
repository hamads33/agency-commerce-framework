/* eslint-disable import/no-extraneous-dependencies, no-console */
const { chromium } = require( 'playwright' );
const { execFileSync } = require( 'child_process' );
const path = require( 'path' );

const repoRoot = path.resolve( __dirname, '..', '..' );
const baseUrl = process.env.WP_BASE_URL || 'http://localhost:8888';
const shopUrl = `${ baseUrl }/shop/`;
const simpleProductUrl = `${ baseUrl }/product/cp3-simple-product/`;
const variableProductUrl = `${ baseUrl }/product/cp3-variable-product/`;
const homeUrl = `${ baseUrl }/`;

function runWp( args ) {
	const output = execFileSync(
		'npm',
		[ 'run', 'env:cli', '--', 'wp', ...args ],
		{
			cwd: repoRoot,
			encoding: 'utf8',
			maxBuffer: 10 * 1024 * 1024,
		}
	);

	return String( output );
}

function parseJsonOutput( output ) {
	const lines = output
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
		const output = Array.isArray( target )
			? target.slice()
			: { ...( target || {} ) };

		Object.keys( patch ).forEach( ( key ) => {
			output[ key ] = deepMerge( output[ key ], patch[ key ] );
		} );

		return output;
	}

	return patch;
}

function readOption( name ) {
	return parseJsonOutput(
		runWp( [ 'option', 'get', name, '--format=json' ] )
	);
}

function updateOption( name, patch ) {
	const current = readOption( name );
	const next =
		'function' === typeof patch
			? patch( current )
			: deepMerge( current, patch );
	const phpJson = JSON.stringify( JSON.stringify( next ) );

	runWp( [
		'eval',
		`update_option( ${ JSON.stringify(
			name
		) }, json_decode( ${ phpJson }, true ), false );`,
	] );
}

function setMeta( postId, key, value ) {
	runWp( [
		'eval',
		`update_post_meta( ${ Number( postId ) }, ${ JSON.stringify(
			key
		) }, json_decode( ${ JSON.stringify(
			JSON.stringify( value )
		) }, true ) );`,
	] );
}

function runWpEval( code ) {
	return runWp( [ 'eval', code ] );
}

function setPostDateNow( postId ) {
	runWp( [
		'eval',
		`wp_update_post( array( 'ID' => ${ Number(
			postId
		) }, 'post_date' => current_time( 'mysql' ), 'post_date_gmt' => current_time( 'mysql', true ) ) );`,
	] );
}

function getActiveThemeStylesheet() {
	const output = runWp( [ 'option', 'get', 'stylesheet' ] );
	const lines = output
		.trim()
		.split( /\r?\n/ )
		.map( ( line ) => line.trim() )
		.filter( Boolean );

	return lines[ lines.length - 1 ] || '';
}

function setTheme( slug ) {
	runWp( [ 'theme', 'activate', slug ] );
}

function setPluginState( action, slug ) {
	runWp( [ 'plugin', action, slug ] );
}

function currentPluginState( slug ) {
	try {
		runWp( [ 'plugin', 'is-active', slug ] );
		return 'active';
	} catch {
		return 'inactive';
	}
}

function attachErrorCollectors( page ) {
	const errors = [];

	page.on( 'pageerror', ( error ) => {
		errors.push( `Page error: ${ error.message }` );
	} );

	page.on( 'console', ( message ) => {
		if (
			'error' === message.type() &&
			! message
				.text()
				.includes(
					'Failed to load resource: the server responded with a status of 403 (Forbidden)'
				)
		) {
			errors.push( `Console error: ${ message.text() }` );
		}
	} );

	return {
		assertClean: async ( context ) => {
			if ( errors.length ) {
				throw new Error(
					`${ context } encountered browser errors:\n${ errors.join(
						'\n'
					) }`
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

async function assertCount( locator, expected, label ) {
	const count = await locator.count();

	if ( count !== expected ) {
		throw new Error(
			`Expected ${ label } count ${ expected }, got ${ count }.`
		);
	}
}

async function assertContainsText( locator, expected, label ) {
	const text = await locator.innerText();

	if ( ! text.includes( expected ) ) {
		throw new Error(
			`Expected ${ label } to include "${ expected }", got: ${ text }`
		);
	}
}

async function assertHasClass( locator, className, label ) {
	const classList = await locator.evaluate(
		( element ) => element.className
	);

	if ( ! String( classList ).split( /\s+/ ).includes( className ) ) {
		throw new Error(
			`Expected ${ label } to include class ${ className }, got: ${ classList }`
		);
	}
}

function setSettingsPatch( patch ) {
	updateOption( 'agency_commerce_core_settings', ( current ) =>
		deepMerge( current, patch )
	);
}

function seedCommerceFeatures() {
	setSettingsPatch( {
		product_page: {
			show_delivery_message: true,
			delivery_message: 'Standard delivery in 5-7 business days.',
			show_whatsapp_enquiry: true,
		},
		whatsapp: {
			phone_number: '+92 300 1234567',
			button_text: 'Chat on WhatsApp',
			message_template:
				'Hello, I am interested in {product_name}. Product: {product_url}. SKU: {sku}. Variation: {variation}.',
		},
		social: {
			facebook: 'https://facebook.com/agencycommerce',
			instagram: 'https://instagram.com/agencycommerce',
			linkedin: 'https://linkedin.com/company/agencycommerce',
			x: 'https://x.com/agencycommerce',
			tiktok: '',
			pinterest: '',
			youtube: 'https://youtube.com/@agencycommerce',
		},
		developer: {
			force_product_search: false,
		},
	} );

	setPostDateNow( 14 );
	setMeta( 14, '_manage_stock', true );
	setMeta( 14, '_stock_status', 'instock' );
	setMeta( 14, '_stock', 3 );
	setMeta( 14, '_total_sales', 99 );
	setMeta( 14, '_agency_commerce_badge_new', 'show' );
	setMeta( 14, '_agency_commerce_badge_best_seller', 'show' );
	setMeta( 14, '_agency_commerce_badge_limited', 'show' );
	setMeta( 14, '_agency_commerce_badge_custom_labels', [
		'Editor pick',
		'Limited run',
	] );
	setMeta(
		14,
		'_agency_commerce_delivery_message',
		'Ships in 2-3 business days.'
	);

	runWpEval(
		`$existing = get_posts( array(
			'post_type' => 'page',
			'post_status' => 'any',
			'title' => 'CP4C Search Page',
			'fields' => 'ids',
			'posts_per_page' => 1,
		) );
		if ( empty( $existing ) ) {
			wp_insert_post( array(
				'post_type' => 'page',
				'post_status' => 'publish',
				'post_title' => 'CP4C Search Page',
				'post_content' => 'Normal WordPress search target for CP4C.'
			) );
		}
		update_option( 'agency_commerce_core_settings_schema_version', 2, false );
		( new AgencyCommerceCore\\Migrations\\SettingsMigrationManager() )->maybe_migrate();`
	);
}

async function login( page ) {
	await page.goto( `${ baseUrl }/wp-login.php`, {
		waitUntil: 'networkidle',
	} );
	await page.fill( '#user_login', process.env.WP_USERNAME || 'admin' );
	await page.fill( '#user_pass', process.env.WP_PASSWORD || 'password' );
	await Promise.all( [
		page.waitForSelector( '#wpadminbar' ),
		page.click( '#wp-submit' ),
	] );
}

async function loadAndCheck( page, url, context ) {
	await page.goto( url, { waitUntil: 'networkidle' } );
	await assertNoPhpWarnings( page, context );
}

async function verifySocialLinks( page ) {
	await loadAndCheck( page, homeUrl, 'home page social links' );
	const socialLinks = page.locator( '.ac-social-links li' );
	await assertCount( socialLinks, 5, 'social links' );
	await assertContainsText(
		page.locator( '.ac-social-links' ),
		'Facebook',
		'social links'
	);
	await assertContainsText(
		page.locator( '.ac-social-links' ),
		'Instagram',
		'social links'
	);
	await assertContainsText(
		page.locator( '.ac-social-links' ),
		'LinkedIn',
		'social links'
	);
	await assertContainsText(
		page.locator( '.ac-social-links' ),
		'X',
		'social links'
	);
	await assertContainsText(
		page.locator( '.ac-social-links' ),
		'YouTube',
		'social links'
	);
}

async function verifyShopBadges( page ) {
	await loadAndCheck( page, shopUrl, 'shop page badge rendering' );

	const simpleCard = page.locator( 'article.ac-product-card' ).filter( {
		has: page.getByRole( 'heading', { name: 'CP3 Simple Product' } ),
	} );
	await assertContainsText( simpleCard, 'New', 'simple product badges' );
	await assertContainsText(
		simpleCard,
		'Best seller',
		'simple product badges'
	);
	await assertContainsText( simpleCard, 'Limited', 'simple product badges' );
	await assertContainsText(
		simpleCard,
		'Editor pick',
		'simple product badges'
	);
	await assertContainsText(
		simpleCard,
		'Limited run',
		'simple product badges'
	);

	const featuredCard = page.locator( 'article.ac-product-card' ).filter( {
		has: page.getByRole( 'heading', { name: 'CP3 Featured Product' } ),
	} );
	await assertContainsText(
		featuredCard,
		'Featured',
		'featured product badges'
	);

	const saleCard = page.locator( 'article.ac-product-card' ).filter( {
		has: page.getByRole( 'heading', { name: 'CP3 Sale Product' } ),
	} );
	await assertContainsText( saleCard, 'Sale', 'sale product badges' );

	const stockCard = page.locator( 'article.ac-product-card' ).filter( {
		has: page.getByRole( 'heading', { name: 'CP3 Out Of Stock Product' } ),
	} );
	await assertContainsText(
		stockCard,
		'Out of stock',
		'out of stock badges'
	);
}

async function verifyBadgeMigrationAndLegacyCompatibility( page ) {
	const migrated = runWpEval(
		`$value = get_post_meta( 14, '_agency_commerce_badge_custom_labels', true );
		echo wp_json_encode( array( 'is_array' => is_array( $value ), 'value' => $value ) );`
	);
	const migratedValue = parseJsonOutput( migrated );

	if (
		migratedValue.is_array ||
		! migratedValue.value.includes( 'Editor pick' )
	) {
		throw new Error(
			`Expected migrated custom badge metadata to be newline text, got: ${ migrated }`
		);
	}

	setMeta( 14, '_agency_commerce_badge_custom_labels', [
		'Legacy array badge',
		'Legacy second badge',
	] );

	await loadAndCheck( page, shopUrl, 'legacy badge compatibility' );
	const simpleCard = page.locator( 'article.ac-product-card' ).filter( {
		has: page.getByRole( 'heading', { name: 'CP3 Simple Product' } ),
	} );
	await assertContainsText(
		simpleCard,
		'Legacy array badge',
		'legacy array badge'
	);
	await assertContainsText(
		simpleCard,
		'Legacy second badge',
		'legacy array badge'
	);

	setMeta(
		14,
		'_agency_commerce_badge_custom_labels',
		'Editor pick\nLimited run'
	);
}

async function verifyBuiltAssets( page ) {
	await loadAndCheck( page, shopUrl, 'built asset loading' );

	const frontendStyle = await page
		.locator(
			'link[href*="/wp-content/themes/agency-commerce/build/style-frontend.css"]'
		)
		.count();
	const frontendScript = await page
		.locator(
			'script[src*="/wp-content/themes/agency-commerce/build/frontend.js"]'
		)
		.count();
	const catalogScript = await page
		.locator(
			'script[src*="/wp-content/themes/agency-commerce/build/catalog.js"]'
		)
		.count();

	if ( ! frontendStyle || ! frontendScript || ! catalogScript ) {
		throw new Error(
			`Expected built frontend assets, got style=${ frontendStyle }, frontend=${ frontendScript }, catalog=${ catalogScript }`
		);
	}
}

async function verifySearchBehaviour( page ) {
	await loadAndCheck(
		page,
		`${ baseUrl }/?s=CP4C%20Search%20Page`,
		'normal WordPress search'
	);
	await assertContainsText(
		page.locator( '.ac-search-results' ),
		'CP4C Search Page',
		'normal search results'
	);

	if ( await page.locator( 'article.ac-product-card' ).count() ) {
		throw new Error( 'Normal WordPress search rendered product cards.' );
	}

	await loadAndCheck(
		page,
		`${ baseUrl }/?s=CP3&post_type=product`,
		'product search'
	);

	if ( ! ( await page.locator( 'article.ac-product-card' ).count() ) ) {
		throw new Error( 'Product search did not render product cards.' );
	}
}

function verifyWooCompatibilityHelpers() {
	const output = runWpEval(
		`ob_start();
		add_action( 'woocommerce_before_shop_loop_item_title', static function() { echo '<span class="cp4c-loop-hook">loop hook</span>'; }, 99 );
		agency_commerce_do_product_loop_hook( 'woocommerce_before_shop_loop_item_title' );
		$loop = ob_get_clean();
		ob_start();
		add_action( 'woocommerce_before_main_content', static function() { echo '<span class="cp4c-wrapper-hook">wrapper hook</span>'; }, 99 );
		agency_commerce_do_archive_wrapper_hook( 'woocommerce_before_main_content' );
		$wrapper = ob_get_clean();
		echo wp_json_encode( array( 'loop' => $loop, 'wrapper' => $wrapper ) );`
	);
	const result = parseJsonOutput( output );

	if (
		! result.loop.includes( 'cp4c-loop-hook' ) ||
		! result.wrapper.includes( 'cp4c-wrapper-hook' ) ||
		result.loop.includes( 'woocommerce-loop-product__link' ) ||
		result.wrapper.includes( 'woocommerce-breadcrumb' )
	) {
		throw new Error(
			`WooCommerce compatibility helpers returned unexpected output: ${ output }`
		);
	}
}

async function verifySimpleProductExtensions( page ) {
	await loadAndCheck(
		page,
		simpleProductUrl,
		'simple product page extensions'
	);
	await assertContainsText(
		page.locator( '.ac-product-delivery__message' ),
		'Ships in 2-3 business days.',
		'delivery override'
	);

	const whatsapp = page.locator( '.ac-whatsapp-enquiry' );
	await assertContainsText(
		whatsapp,
		'Chat on WhatsApp',
		'WhatsApp button text'
	);

	const href = await whatsapp.getAttribute( 'href' );
	if ( ! href || ! href.startsWith( 'https://wa.me/' ) ) {
		throw new Error( `Expected a WhatsApp URL, got: ${ href }` );
	}

	const text = new URL( href ).searchParams.get( 'text' ) || '';
	if (
		! text.includes( 'CP3 Simple Product' ) ||
		! text.includes( simpleProductUrl ) ||
		! text.includes( 'CP3-SIMPLE' ) ||
		! text.includes( 'Variation: Not selected' )
	) {
		throw new Error( `Unexpected WhatsApp text payload: ${ text }` );
	}

	await assertContainsText(
		page.locator( '.ac-product-badges' ),
		'New',
		'single product badges'
	);
	await assertContainsText(
		page.locator( '.ac-product-badges' ),
		'Best seller',
		'single product badges'
	);
}

async function verifyVariableProductWhatsApp( page ) {
	await loadAndCheck(
		page,
		variableProductUrl,
		'variable product page extensions'
	);

	const whatsapp = page.locator( '.ac-whatsapp-enquiry' );
	await assertContainsText(
		whatsapp,
		'Chat on WhatsApp',
		'WhatsApp button text'
	);

	const initialHref = await whatsapp.getAttribute( 'href' );
	if ( initialHref !== '#' ) {
		throw new Error(
			`Expected variable product WhatsApp button to wait for a selection, got: ${ initialHref }`
		);
	}

	const selects = page.locator(
		'.variations_form select[name^="attribute_"]'
	);
	const selectCount = await selects.count();

	for ( let index = 0; index < selectCount; index += 1 ) {
		const select = selects.nth( index );
		const options = await select
			.locator( 'option' )
			.evaluateAll( ( nodes ) =>
				nodes
					.map( ( option ) => ( {
						value: option.value,
						label: option.textContent.trim(),
					} ) )
					.filter( ( option ) => option.value )
			);

		if ( options.length ) {
			await select.selectOption( options[ 0 ].value );
		}
	}

	await page.waitForFunction( ( selector ) => {
		const button = document.querySelector( selector );
		return (
			button &&
			button.getAttribute( 'href' ) &&
			button.getAttribute( 'href' ) !== '#'
		);
	}, '.ac-whatsapp-enquiry' );

	const resolvedHref = await whatsapp.getAttribute( 'href' );
	const variationText =
		new URL( resolvedHref ).searchParams.get( 'text' ) || '';
	if (
		! variationText.includes( 'Variation:' ) ||
		variationText.includes( 'Not selected' )
	) {
		throw new Error(
			`Expected variation text in WhatsApp URL, got: ${ variationText }`
		);
	}
}

async function verifyResponsiveLayouts( page ) {
	const viewports = [
		{ width: 390, height: 844, label: 'mobile' },
		{ width: 820, height: 1180, label: 'tablet' },
		{ width: 1440, height: 1200, label: 'desktop' },
	];

	for ( const viewport of viewports ) {
		await page.setViewportSize( {
			width: viewport.width,
			height: viewport.height,
		} );
		await loadAndCheck( page, shopUrl, `${ viewport.label } shop view` );
		await assertHasClass(
			page.locator( 'article.ac-product-card' ).first(),
			'ac-product-card--compact',
			`${ viewport.label } product card`
		);

		await loadAndCheck(
			page,
			simpleProductUrl,
			`${ viewport.label } product view`
		);
		await assertHasClass(
			page.locator( 'div.ac-product-layout' ).first(),
			'ac-product-layout--sticky',
			`${ viewport.label } product layout`
		);
	}
}

async function verifyInactiveStates() {
	const originalTheme = getActiveThemeStylesheet();
	const originalPluginState = currentPluginState( 'agency-commerce-core' );
	const originalWooState = currentPluginState( 'woocommerce' );
	const page = await browser.newPage();
	const errors = attachErrorCollectors( page );

	try {
		setTheme( 'agency-commerce-child' );
		await loadAndCheck( page, homeUrl, 'child theme active check' );

		if ( 'active' === originalPluginState ) {
			setPluginState( 'deactivate', 'agency-commerce-core' );
			await loadAndCheck( page, homeUrl, 'plugin inactive check' );
			setPluginState( 'activate', 'agency-commerce-core' );
		}

		if ( 'active' === originalWooState ) {
			setPluginState( 'deactivate', 'woocommerce' );
			await loadAndCheck( page, homeUrl, 'WooCommerce inactive check' );
			setPluginState( 'activate', 'woocommerce' );
		}

		setTheme( originalTheme );
		await loadAndCheck( page, homeUrl, 'restored theme check' );
		await errors.assertClean( 'Inactive state checks' );
	} finally {
		await page.close();
		setTheme( originalTheme );
		if ( 'active' === originalPluginState ) {
			setPluginState( 'activate', 'agency-commerce-core' );
		}
		if ( 'active' === originalWooState ) {
			setPluginState( 'activate', 'woocommerce' );
		}
	}
}

let browser;

( async () => {
	setTheme( 'agency-commerce' );
	setPluginState( 'activate', 'agency-commerce-core' );
	setPluginState( 'activate', 'woocommerce' );
	seedCommerceFeatures();

	browser = await chromium.launch();
	const page = await browser.newPage();
	const errors = attachErrorCollectors( page );

	try {
		await login( page );
		await verifySocialLinks( page );
		await verifyBuiltAssets( page );
		await verifyShopBadges( page );
		await verifyBadgeMigrationAndLegacyCompatibility( page );
		await verifySimpleProductExtensions( page );
		await verifyVariableProductWhatsApp( page );
		await verifySearchBehaviour( page );
		verifyWooCompatibilityHelpers();
		await verifyResponsiveLayouts( page );
		await verifyInactiveStates();
		await errors.assertClean( 'Storefront checks' );
		console.log( 'Storefront commerce feature checks passed.' );
	} finally {
		await browser.close();
	}
} )().catch( ( error ) => {
	console.error( error );
	process.exit( 1 );
} );

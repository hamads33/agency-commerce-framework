/* eslint-disable import/no-extraneous-dependencies, no-console */
const { chromium } = require( 'playwright' );

const baseUrl = process.env.WP_BASE_URL || 'http://localhost:8888';
const username = process.env.WP_USERNAME || 'admin';
const password = process.env.WP_PASSWORD || 'password';

const shopUrl = `${ baseUrl }/shop/`;
const productUrl = `${ baseUrl }/product/cp3-simple-product/`;
const settingsUrl = ( tab ) =>
	`${ baseUrl }/wp-admin/admin.php?page=agency-commerce-framework&tab=${ tab }`;

const variantRuns = [
	{
		tab: 'header',
		field: '#agency-commerce-core-header-variant',
		value: 'header-default',
		frontendUrl: shopUrl,
		expectation: { headerClass: 'ac-site-header--default' },
	},
	{
		tab: 'header',
		field: '#agency-commerce-core-header-variant',
		value: 'header-centered',
		frontendUrl: shopUrl,
		expectation: { headerClass: 'ac-site-header--centered' },
	},
	{
		tab: 'footer',
		field: '#agency-commerce-core-footer-variant',
		value: 'footer-columns',
		frontendUrl: shopUrl,
		expectation: { footerClass: 'ac-site-footer--columns' },
	},
	{
		tab: 'footer',
		field: '#agency-commerce-core-footer-variant',
		value: 'footer-minimal',
		frontendUrl: shopUrl,
		expectation: { footerClass: 'ac-site-footer--minimal' },
	},
	{
		tab: 'shop',
		field: '#agency-commerce-core-shop-card_variant',
		value: 'product-card-classic',
		frontendUrl: shopUrl,
		expectation: { cardClass: 'ac-product-card--classic' },
	},
	{
		tab: 'shop',
		field: '#agency-commerce-core-shop-card_variant',
		value: 'product-card-minimal',
		frontendUrl: shopUrl,
		expectation: { cardClass: 'ac-product-card--minimal' },
	},
	{
		tab: 'shop',
		field: '#agency-commerce-core-shop-card_variant',
		value: 'product-card-compact',
		frontendUrl: shopUrl,
		expectation: { cardClass: 'ac-product-card--compact' },
	},
	{
		tab: 'product_page',
		field: '#agency-commerce-core-product_page-layout',
		value: 'product-layout-standard',
		frontendUrl: productUrl,
		expectation: { layoutClass: 'ac-product-layout--standard' },
	},
	{
		tab: 'product_page',
		field: '#agency-commerce-core-product_page-layout',
		value: 'product-layout-sticky',
		frontendUrl: productUrl,
		expectation: { layoutClass: 'ac-product-layout--sticky' },
	},
];

const finalValues = {
	header: 'header-centered',
	footer: 'footer-minimal',
	shop: 'product-card-compact',
	product_page: 'product-layout-sticky',
};

async function login( page ) {
	await page.goto( `${ baseUrl }/wp-login.php`, {
		waitUntil: 'networkidle',
	} );
	await page.fill( '#user_login', username );
	await page.fill( '#user_pass', password );
	await Promise.all( [
		page.waitForSelector( '#wpadminbar' ),
		page.click( '#wp-submit' ),
	] );
}

async function logout( page ) {
	await page.goto( `${ baseUrl }/wp-login.php?action=logout`, {
		waitUntil: 'networkidle',
	} );
	const logoutUrl = await page
		.locator( 'a[href*="action=logout"]' )
		.first()
		.getAttribute( 'href' );

	if ( ! logoutUrl ) {
		throw new Error( 'Could not resolve the logout URL.' );
	}

	await page.goto( logoutUrl, { waitUntil: 'networkidle' } );
	await page.waitForURL( /wp-login\.php/ );
}

async function assertNoPhpWarnings( page, context ) {
	const bodyText = await page.locator( 'body' ).innerText();
	const html = await page.content();
	const combined = `${ bodyText }\n${ html }`;

	if (
		/(?:^|[^a-z])(Warning|Notice|Deprecated|Fatal error)\b/i.test(
			combined
		)
	) {
		throw new Error(
			`PHP warning text found while checking ${ context }.`
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

async function submitSettingsForm( page ) {
	await Promise.all( [
		page
			.waitForNavigation( { waitUntil: 'networkidle', timeout: 10000 } )
			.catch( () => {} ),
		page
			.locator( '.agency-commerce-core-settings form' )
			.evaluate( ( form ) => {
				form.requestSubmit();
			} ),
	] );
	await page.waitForSelector( '.agency-commerce-core-settings form' );
}

async function saveVariant( page, run ) {
	await page.goto( settingsUrl( run.tab ), {
		waitUntil: 'networkidle',
	} );

	await page.waitForSelector( run.field );
	await page.selectOption( run.field, run.value );
	await submitSettingsForm( page );

	const selected = await page.$eval( run.field, ( select ) => select.value );

	if ( selected !== run.value ) {
		throw new Error( `Failed to persist ${ run.value } on ${ run.tab }.` );
	}

	console.log( `Saved ${ run.tab } -> ${ run.value }` );
}

async function verifyFrontend( page, run ) {
	await page.goto( run.frontendUrl, {
		waitUntil: 'networkidle',
	} );
	await assertNoPhpWarnings( page, run.value );

	if ( run.expectation.headerClass ) {
		await assertHasClass(
			page.locator( 'header.ac-site-header' ).first(),
			run.expectation.headerClass,
			'header'
		);
	}

	if ( run.expectation.footerClass ) {
		await assertHasClass(
			page.locator( 'footer.ac-site-footer' ).first(),
			run.expectation.footerClass,
			'footer'
		);
	}

	if ( run.expectation.cardClass ) {
		await assertHasClass(
			page.locator( 'article.ac-product-card' ).first(),
			run.expectation.cardClass,
			'product card'
		);
	}

	if ( run.expectation.layoutClass ) {
		await assertHasClass(
			page.locator( 'div.ac-product-layout' ).first(),
			run.expectation.layoutClass,
			'product layout'
		);
	}

	console.log( `Verified frontend for ${ run.value }` );
}

async function verifySavedSelection( page, tab, field, value ) {
	await page.goto( settingsUrl( tab ), {
		waitUntil: 'networkidle',
	} );

	await page.waitForSelector( field );
	const selected = await page.$eval( field, ( select ) => select.value );

	if ( selected !== value ) {
		throw new Error(
			`Expected ${ tab } to persist ${ value } after login, got ${ selected }.`
		);
	}
}

( async () => {
	const browser = await chromium.launch();
	const page = await browser.newPage();

	try {
		await login( page );

		for ( const run of variantRuns ) {
			await saveVariant( page, run );
			await verifyFrontend( page, run );
		}

		await logout( page );
		await page.close();

		const reloginPage = await browser.newPage();
		await login( reloginPage );

		await verifySavedSelection(
			reloginPage,
			'header',
			'#agency-commerce-core-header-variant',
			finalValues.header
		);
		await verifySavedSelection(
			reloginPage,
			'footer',
			'#agency-commerce-core-footer-variant',
			finalValues.footer
		);
		await verifySavedSelection(
			reloginPage,
			'shop',
			'#agency-commerce-core-shop-card_variant',
			finalValues.shop
		);
		await verifySavedSelection(
			reloginPage,
			'product_page',
			'#agency-commerce-core-product_page-layout',
			finalValues.product_page
		);

		await reloginPage.goto( shopUrl, {
			waitUntil: 'networkidle',
		} );
		await assertNoPhpWarnings( reloginPage, 'persisted frontend review' );
		await assertHasClass(
			reloginPage.locator( 'header.ac-site-header' ).first(),
			'ac-site-header--centered',
			'header'
		);
		await assertHasClass(
			reloginPage.locator( 'footer.ac-site-footer' ).first(),
			'ac-site-footer--minimal',
			'footer'
		);
		await assertHasClass(
			reloginPage.locator( 'article.ac-product-card' ).first(),
			'ac-product-card--compact',
			'product card'
		);

		await reloginPage.goto( productUrl, {
			waitUntil: 'networkidle',
		} );
		await assertNoPhpWarnings(
			reloginPage,
			'persisted product layout review'
		);
		await assertHasClass(
			reloginPage.locator( 'div.ac-product-layout' ).first(),
			'ac-product-layout--sticky',
			'product layout'
		);

		console.log( 'Admin settings variant checks passed.' );
	} finally {
		await browser.close();
	}
} )().catch( ( error ) => {
	console.error( error );
	process.exit( 1 );
} );

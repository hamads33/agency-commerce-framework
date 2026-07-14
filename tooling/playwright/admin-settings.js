/* eslint-disable import/no-extraneous-dependencies, no-console */
const { chromium } = require( 'playwright' );
const { execFileSync } = require( 'child_process' );
const path = require( 'path' );

const repoRoot = path.resolve( __dirname, '..', '..' );
const fallbackComposeFile = process.env.WP_ENV_COMPOSE_FILE || '';

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

async function assertImagePresent( page, selector, label ) {
	const count = await page.locator( selector ).count();

	if ( count < 1 ) {
		throw new Error( `Expected ${ label } to be present.` );
	}
}

function runWpEval( code ) {
	if ( fallbackComposeFile ) {
		execFileSync(
			'docker',
			[
				'compose',
				'-f',
				fallbackComposeFile,
				'exec',
				'-T',
				'cli',
				'wp',
				'eval',
				code,
			],
			{
				cwd: repoRoot,
				encoding: 'utf8',
				maxBuffer: 10 * 1024 * 1024,
			}
		);
		return;
	}

	execFileSync( 'npm', [ 'run', 'env:cli', '--', 'wp', 'eval', code ], {
		cwd: repoRoot,
		encoding: 'utf8',
		maxBuffer: 10 * 1024 * 1024,
	} );
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

async function verifyPresetThumbnails( page ) {
	await assertImagePresent(
		page,
		'img[src*="/assets/previews/header/header-default.svg"]',
		'header preview thumbnail'
	);
	await assertImagePresent(
		page,
		'img[src*="/assets/previews/footer/footer-columns.svg"]',
		'footer preview thumbnail'
	);
	await assertImagePresent(
		page,
		'img[src*="/assets/previews/product-card/product-card-classic.svg"]',
		'product-card preview thumbnail'
	);
	await assertImagePresent(
		page,
		'img[src*="/assets/previews/product-layout/product-layout-sticky.svg"]',
		'product-layout preview thumbnail'
	);
}

async function openPresetPreview( page, preset ) {
	await page.goto( `${ settingsUrl( 'presets' ) }&preset=${ preset }`, {
		waitUntil: 'networkidle',
	} );

	await page.waitForSelector( '.agency-commerce-core-preset-preview' );
}

async function applyPreset( page ) {
	runWpEval( 'agency_commerce_core_apply_preset( "minimal" );' );
	await page.reload( { waitUntil: 'networkidle' } );
}

async function restorePreset( page ) {
	runWpEval( 'agency_commerce_core_restore_previous_preset();' );
	await page.reload( { waitUntil: 'networkidle' } );
}

( async () => {
	const browser = await chromium.launch();
	const page = await browser.newPage();
	const errors = attachErrorCollectors( page );

	try {
		await login( page );

		for ( const run of variantRuns ) {
			await saveVariant( page, run );
			await verifyFrontend( page, run );
		}

		await logout( page );
		await page.close();

		const reloginPage = await browser.newPage();
		const reloginErrors = attachErrorCollectors( reloginPage );
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

		await reloginPage.goto( settingsUrl( 'presets' ), {
			waitUntil: 'networkidle',
		} );
		await assertNoPhpWarnings( reloginPage, 'preset tab review' );
		await verifyPresetThumbnails( reloginPage );

		await openPresetPreview( reloginPage, 'minimal' );
		await reloginPage.waitForSelector(
			'.agency-commerce-core-preset-preview'
		);
		await applyPreset( reloginPage );
		await assertNoPhpWarnings( reloginPage, 'preset apply review' );
		await reloginPage.goto( shopUrl, { waitUntil: 'networkidle' } );
		await assertHasClass(
			reloginPage.locator( 'header.ac-site-header' ).first(),
			'ac-site-header--default',
			'header'
		);
		await assertHasClass(
			reloginPage.locator( 'footer.ac-site-footer' ).first(),
			'ac-site-footer--minimal',
			'footer'
		);
		await assertHasClass(
			reloginPage.locator( 'article.ac-product-card' ).first(),
			'ac-product-card--minimal',
			'product card'
		);

		await reloginPage.goto( productUrl, { waitUntil: 'networkidle' } );
		await assertHasClass(
			reloginPage.locator( 'div.ac-product-layout' ).first(),
			'ac-product-layout--standard',
			'product layout'
		);

		await reloginPage.goto( settingsUrl( 'presets' ), {
			waitUntil: 'networkidle',
		} );
		await restorePreset( reloginPage );
		await assertNoPhpWarnings( reloginPage, 'preset restore review' );

		await reloginPage.goto( shopUrl, { waitUntil: 'networkidle' } );
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

		await logout( reloginPage );
		await reloginPage.close();

		const persistencePage = await browser.newPage();
		const persistenceErrors = attachErrorCollectors( persistencePage );
		await login( persistencePage );

		await verifySavedSelection(
			persistencePage,
			'header',
			'#agency-commerce-core-header-variant',
			finalValues.header
		);
		await verifySavedSelection(
			persistencePage,
			'footer',
			'#agency-commerce-core-footer-variant',
			finalValues.footer
		);
		await verifySavedSelection(
			persistencePage,
			'shop',
			'#agency-commerce-core-shop-card_variant',
			finalValues.shop
		);
		await verifySavedSelection(
			persistencePage,
			'product_page',
			'#agency-commerce-core-product_page-layout',
			finalValues.product_page
		);
		await persistencePage.goto( shopUrl, { waitUntil: 'networkidle' } );
		await assertHasClass(
			persistencePage.locator( 'header.ac-site-header' ).first(),
			'ac-site-header--centered',
			'header'
		);
		await assertHasClass(
			persistencePage.locator( 'footer.ac-site-footer' ).first(),
			'ac-site-footer--minimal',
			'footer'
		);
		await assertHasClass(
			persistencePage.locator( 'article.ac-product-card' ).first(),
			'ac-product-card--compact',
			'product card'
		);
		await persistencePage.goto( productUrl, { waitUntil: 'networkidle' } );
		await assertHasClass(
			persistencePage.locator( 'div.ac-product-layout' ).first(),
			'ac-product-layout--sticky',
			'product layout'
		);
		await persistenceErrors.assertClean( 'Preset persistence checks' );
		await persistencePage.close();

		console.log( 'Admin settings variant checks passed.' );
		await errors.assertClean( 'Admin settings checks' );
		await reloginErrors.assertClean( 'Preset round-trip checks' );
	} finally {
		await browser.close();
	}
} )().catch( ( error ) => {
	console.error( error );
	process.exit( 1 );
} );

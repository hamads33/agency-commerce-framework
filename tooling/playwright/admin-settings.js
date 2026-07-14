/* eslint-disable import/no-extraneous-dependencies, no-console */
const { chromium } = require( 'playwright' );

const baseUrl = process.env.WP_BASE_URL || 'http://localhost:8888';
const username = process.env.WP_USERNAME || 'admin';
const password = process.env.WP_PASSWORD || 'password';

const tabs = [
	'store_identity',
	'header',
	'shop',
	'product_page',
	'footer',
	'social',
	'design',
	'performance',
	'developer',
];

async function login( page ) {
	await page.goto( `${ baseUrl }/wp-login.php`, {
		waitUntil: 'networkidle',
	} );
	await page.fill( '#user_login', username );
	await page.fill( '#user_pass', password );
	await Promise.all( [
		page.waitForNavigation( { waitUntil: 'networkidle' } ),
		page.click( '#wp-submit' ),
	] );
}

async function saveTab( page, tab ) {
	await page.goto(
		`${ baseUrl }/wp-admin/admin.php?page=agency-commerce-framework&tab=${ tab }`,
		{ waitUntil: 'networkidle' }
	);

	await page.waitForSelector( '.agency-commerce-core-settings form' );
	await submitSettingsForm( page );

	const formReturned = await page
		.locator( '.agency-commerce-core-settings form' )
		.count();
	const errors = await page.locator( '.notice-error, .error' ).count();

	if ( ! formReturned || errors ) {
		throw new Error(
			`Settings save did not complete cleanly for ${ tab }.`
		);
	}
}

async function verifyInvalidHeaderVariantRejected( page ) {
	await page.goto(
		`${ baseUrl }/wp-admin/admin.php?page=agency-commerce-framework&tab=header`,
		{ waitUntil: 'networkidle' }
	);

	await page.evaluate( () => {
		const select = document.querySelector(
			'#agency-commerce-core-header-variant'
		);
		const option = document.createElement( 'option' );

		option.value = 'invalid-header';
		option.textContent = 'Invalid Header';
		select.appendChild( option );
		select.value = 'invalid-header';
	} );

	await submitSettingsForm( page );

	const selected = await page.$eval(
		'#agency-commerce-core-header-variant',
		( select ) => select.value
	);

	if ( selected === 'invalid-header' ) {
		throw new Error( 'Invalid header variant was not rejected.' );
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

( async () => {
	const browser = await chromium.launch();
	const page = await browser.newPage();

	try {
		await login( page );

		for ( const tab of tabs ) {
			await saveTab( page, tab );
		}

		await verifyInvalidHeaderVariantRejected( page );
		console.log( 'Admin settings Playwright checks passed.' );
	} finally {
		await browser.close();
	}
} )().catch( ( error ) => {
	console.error( error );
	process.exit( 1 );
} );

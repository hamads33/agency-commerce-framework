const assert = require( 'node:assert/strict' );
const path = require( 'node:path' );
const { chromium } = require( 'playwright' );

async function run() {
	const base = new URL( process.env.WP_BASE_URL );
	assert.ok( [ 'localhost', '127.0.0.1' ].includes( base.hostname ) );
	const artifacts = process.env.FORGE_ARTIFACTS;
	const browser = await chromium.launch();
	try {
		const page = await browser.newPage();
		const pages = {
			home: '/',
			shop: '/shop/',
			collection: '/product-category/kitchen-knives/',
			product: '/product/the-hearth-chef-knife/',
		};
		for ( const width of [ 320, 375, 390, 430, 768, 1024, 1280, 1440, 1920 ] ) {
			await page.setViewportSize( { width, height: 950 } );
			for ( const [ name, route ] of Object.entries( pages ) ) {
				const response = await page.goto( new URL( route, base ).href );
				assert.equal( response.status(), 200, route );
				assert.ok( await page.locator( 'main' ).isVisible(), route );
				const documentWidth = await page.locator( 'body' ).evaluate( ( body ) => body.ownerDocument.documentElement.scrollWidth );
				assert.ok( documentWidth <= width + 1, `${ route } at ${ width }: ${ documentWidth }px` );
				if ( [ 390, 1440 ].includes( width ) ) {
					await page.screenshot( { path: path.join( artifacts, `${ name }-${ width }.png` ), fullPage: true } );
				}
			}
		}
		await page.locator( 'select[name="attribute_size"]' ).selectOption( { label: '180 mm' } );
		await page.locator( '.single_add_to_cart_button' ).click();
		await page.goto( new URL( '/cart/', base ).href );
		await page.getByText( 'The Hearth Chef Knife', { exact: false } ).first().waitFor();
		await page.goto( new URL( '/checkout/', base ).href );
		await page.locator( '.wp-block-woocommerce-checkout, .woocommerce-checkout' ).first().waitFor();
		const products = await page.request.get( new URL( '/wp-json/wc/store/v1/products?slug=the-grain-serving-board', base ).href );
		const [ product ] = await products.json();
		assert.ok( product.id > 0 );
		const cart = await fetch( new URL( '/wp-json/wc/store/v1/cart', base ) );
		const token = cart.headers.get( 'cart-token' );
		assert.ok( token );
		const headers = { 'Cart-Token': token, 'Content-Type': 'application/json' };
		const added = await fetch( new URL( '/wp-json/wc/store/v1/cart/add-item', base ), {
			method: 'POST', headers, body: JSON.stringify( { id: product.id, quantity: 1 } ),
		} );
		assert.equal( added.status, 201, await added.text() );
		const checkout = await fetch( new URL( '/wp-json/wc/store/v1/checkout', base ), {
			method: 'POST', headers,
			body: JSON.stringify( {
				billing_address: { first_name: 'Forge', last_name: 'Demo', email: 'forge@example.test', address_1: '1 Test Street', city: 'San Francisco', postcode: '94103', country: 'US', state: 'CA', phone: '4155550100' },
				payment_method: 'cod', payment_data: [],
			} ),
		} );
		const order = await checkout.json();
		assert.equal( checkout.status, 200, JSON.stringify( order ) );
		assert.ok( order.order_id > 0 );
		process.stdout.write( 'Forge responsive storefront, native variation/cart and isolated COD order passed.\n' );
	} finally {
		await browser.close();
	}
}
run().catch( ( error ) => {
	process.stderr.write( error.stack + '\n' );
	process.exitCode = 1;
} );

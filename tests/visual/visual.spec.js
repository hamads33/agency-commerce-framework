const { expect, test } = require( '@playwright/test' );

const pages = [
	{
		name: 'Homepage',
		slug: 'homepage',
		path: process.env.VISUAL_HOME_URL || '/',
	},
	{
		name: 'Shop',
		slug: 'shop',
		path: process.env.VISUAL_SHOP_URL || '/shop/',
	},
	{
		name: 'Product category',
		slug: 'product-category',
		path:
			process.env.VISUAL_CATEGORY_URL || '/product-category/cp3-apparel/',
	},
	{
		name: 'Simple product',
		slug: 'simple-product',
		path:
			process.env.VISUAL_SIMPLE_PRODUCT_URL ||
			'/product/cp3-simple-product/',
	},
	{
		name: 'Variable product',
		slug: 'variable-product',
		path:
			process.env.VISUAL_VARIABLE_PRODUCT_URL ||
			'/product/cp3-variable-product/',
	},
	{
		name: 'Cart',
		slug: 'cart',
		path: process.env.VISUAL_CART_URL || '/cart/',
	},
	{
		name: 'Checkout',
		slug: 'checkout',
		path: process.env.VISUAL_CHECKOUT_URL || '/checkout/',
	},
	{
		name: 'My Account',
		slug: 'my-account',
		path: process.env.VISUAL_MY_ACCOUNT_URL || '/my-account/',
	},
];

for ( const visualPage of pages ) {
	test( visualPage.name, async ( { page } ) => {
		const consoleErrors = [];

		page.on( 'console', ( message ) => {
			if ( 'error' === message.type() ) {
				consoleErrors.push( message.text() );
			}
		} );

		page.on( 'pageerror', ( error ) => {
			consoleErrors.push( error.message );
		} );

		await page.goto( visualPage.path, { waitUntil: 'networkidle' } );
		await expect( page ).toHaveScreenshot( `${ visualPage.slug }.png` );
		expect( consoleErrors ).toEqual( [] );
	} );
}

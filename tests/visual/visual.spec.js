const { expect, test } = require( '@playwright/test' );
const { execFileSync } = require( 'child_process' );
const path = require( 'path' );

const repoRoot = path.resolve( __dirname, '..', '..' );
const fallbackComposeFile = process.env.WP_ENV_COMPOSE_FILE || '';

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

function runWp( args ) {
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
				...args,
			],
			{
				cwd: repoRoot,
				encoding: 'utf8',
				maxBuffer: 10 * 1024 * 1024,
				stdio: 'pipe',
			}
		);
		return;
	}

	execFileSync( 'npm', [ 'run', 'env:cli', '--', 'wp', ...args ], {
		cwd: repoRoot,
		encoding: 'utf8',
		maxBuffer: 10 * 1024 * 1024,
		stdio: 'pipe',
	} );
}

test.beforeAll( () => {
	runWp( [
		'eval',
		`
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$htaccess = ABSPATH . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents(
				$htaccess,
				"# BEGIN WordPress\\n<IfModule mod_rewrite.c>\\nRewriteEngine On\\nRewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\\nRewriteBase /\\nRewriteRule ^index\\\\.php$ - [L]\\nRewriteCond %{REQUEST_FILENAME} !-f\\nRewriteCond %{REQUEST_FILENAME} !-d\\nRewriteRule . /index.php [L]\\n</IfModule>\\n# END WordPress\\n"
			);
		}
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			activate_plugin( 'woocommerce/woocommerce.php' );
		}
		if ( ! is_plugin_active( 'agency-commerce-core/agency-commerce-core.php' ) ) {
			activate_plugin( 'agency-commerce-core/agency-commerce-core.php' );
		}
		switch_theme( 'agency-commerce' );
		delete_option( 'agency_commerce_core_settings' );
		delete_option( 'agency_commerce_core_active_preset' );
		delete_option( 'agency_commerce_core_preset_snapshot' );

		$badge_keys = array(
			'_agency_commerce_badge_new',
			'_agency_commerce_badge_best_seller',
			'_agency_commerce_badge_limited',
			'_agency_commerce_badge_custom_labels',
			'_agency_commerce_delivery_message',
		);
		$products = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		foreach ( $products as $product_id ) {
			foreach ( $badge_keys as $badge_key ) {
				delete_post_meta( (int) $product_id, $badge_key );
			}
		}
		`,
	] );
} );

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

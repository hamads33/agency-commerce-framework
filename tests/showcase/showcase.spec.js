const { expect, test } = require( '@playwright/test' );
const { execFileSync } = require( 'child_process' );
const path = require( 'path' );

const repoRoot = path.resolve( __dirname, '..', '..' );
const fallbackComposeFile = process.env.WP_ENV_COMPOSE_FILE || '';

const themes = [
	{ name: 'Fashion', slug: 'fashion', theme: 'agency-commerce-fashion' },
	{
		name: 'Electronics',
		slug: 'electronics',
		theme: 'agency-commerce-electronics',
	},
	{ name: 'Home', slug: 'home', theme: 'agency-commerce-home' },
];

const pages = [
	{ name: 'Homepage', slug: 'homepage', path: '/' },
	{
		name: 'Category',
		slug: 'category',
		path: '/product-category/cp3-apparel/',
	},
	{ name: 'Shop', slug: 'shop', path: '/shop/' },
	{ name: 'Product', slug: 'product', path: '/product/cp3-simple-product/' },
	{ name: 'Cart', slug: 'cart', path: '/cart/' },
	{ name: 'Checkout', slug: 'checkout', path: '/checkout/' },
	{ name: 'My Account', slug: 'my-account', path: '/my-account/' },
];

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
				stdio: 'pipe',
			}
		);
	}

	return execFileSync( 'npm', [ 'run', 'env:cli', '--', 'wp', ...args ], {
		cwd: repoRoot,
		encoding: 'utf8',
		maxBuffer: 10 * 1024 * 1024,
		stdio: 'pipe',
	} );
}

function prepareTheme( theme ) {
	runWp( [
		'eval',
		`
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			activate_plugin( 'woocommerce/woocommerce.php' );
		}
		if ( ! is_plugin_active( 'agency-commerce-core/agency-commerce-core.php' ) ) {
			activate_plugin( 'agency-commerce-core/agency-commerce-core.php' );
		}
		$upload_dir = wp_upload_dir();
		foreach ( array( '480x480', '720x720', '1024x1024' ) as $placeholder_size ) {
			$placeholder = trailingslashit( $upload_dir['basedir'] ) . 'woocommerce-placeholder-' . $placeholder_size . '.webp';
			if ( ! file_exists( $placeholder ) ) {
				wp_mkdir_p( dirname( $placeholder ) );
				file_put_contents(
					$placeholder,
					base64_decode( 'UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AA/vuUAAA=' )
				);
			}
		}
		if ( ! wp_get_theme( '${ theme }' )->exists() ) {
			throw new RuntimeException( 'Showcase theme is not installed: ${ theme }' );
		}
		switch_theme( '${ theme }' );
		if ( wp_get_theme()->get_stylesheet() !== '${ theme }' ) {
			throw new RuntimeException( 'Showcase theme did not activate: ${ theme }' );
		}
		delete_option( 'agency_commerce_core_settings' );
		delete_option( 'agency_commerce_core_active_preset' );
		delete_option( 'agency_commerce_core_preset_snapshot' );
		`,
	] );
}

for ( const showcaseTheme of themes ) {
	test.describe.serial( showcaseTheme.name, () => {
		test.beforeAll( () => {
			prepareTheme( showcaseTheme.theme );
		} );

		for ( const showcasePage of pages ) {
			test( showcasePage.name, async ( { page } ) => {
				const consoleErrors = [];

				page.on( 'console', ( message ) => {
					if ( 'error' === message.type() ) {
						consoleErrors.push(
							`${ message.text() } ${ message.location().url }`
						);
					}
				} );

				page.on( 'pageerror', ( error ) => {
					consoleErrors.push( error.message );
				} );

				await page.addInitScript( () => {
					window.__acCls = 0;
					new PerformanceObserver( ( list ) => {
						for ( const entry of list.getEntries() ) {
							if ( ! entry.hadRecentInput ) {
								window.__acCls += entry.value;
							}
						}
					} ).observe( { type: 'layout-shift', buffered: true } );
				} );

				await page.goto( showcasePage.path, {
					waitUntil: 'networkidle',
				} );
				await page.waitForTimeout( 500 );

				const metrics = await page.evaluate( () => ( {
					cls: window.__acCls || 0,
					clientWidth: document.documentElement.clientWidth,
					scrollWidth: document.documentElement.scrollWidth,
				} ) );

				expect( consoleErrors ).toEqual( [] );
				expect( metrics.scrollWidth ).toBeLessThanOrEqual(
					metrics.clientWidth + 1
				);
				expect( metrics.cls ).toBeLessThanOrEqual( 0.1 );
				await expect( page ).toHaveScreenshot(
					`${ showcaseTheme.slug }-${ showcasePage.slug }.png`
				);
			} );
		}
	} );
}

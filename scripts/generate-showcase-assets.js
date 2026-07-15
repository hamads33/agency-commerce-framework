const fs = require( 'fs' );
const path = require( 'path' );
const { chromium } = require( '@playwright/test' );
const { execFileSync } = require( 'child_process' );

const repoRoot = path.resolve( __dirname, '..' );
const outputRoot = path.join( repoRoot, 'showcase-screenshots', 'portfolio' );
const boardRoot = path.join( repoRoot, 'showcase-screenshots', 'boards' );
const baseURL = process.env.WP_BASE_URL || 'http://localhost:8888';
const fallbackComposeFile = process.env.WP_ENV_COMPOSE_FILE || '';

const themes = [
	{
		slug: 'fashion',
		theme: 'agency-commerce-fashion',
		name: 'Luxury Fashion',
		direction:
			'Editorial minimalism, large whitespace, restrained palette, and product photography treated as still life.',
	},
	{
		slug: 'electronics',
		theme: 'agency-commerce-electronics',
		name: 'Electronics',
		direction:
			'High-contrast technical surfaces, product-first grids, precision spacing, and specification-led hierarchy.',
	},
	{
		slug: 'home',
		theme: 'agency-commerce-home',
		name: 'Furniture / Home',
		direction:
			'Warm editorial room stories, spacious lifestyle panels, tactile cards, and calm purchase surfaces.',
	},
];

const pages = [
	{ slug: 'homepage', path: '/' },
	{ slug: 'category', path: '/product-category/cp3-apparel/' },
	{ slug: 'shop', path: '/shop/' },
	{ slug: 'product', path: '/product/cp3-simple-product/' },
	{ slug: 'cart', path: '/cart/' },
	{ slug: 'checkout', path: '/checkout/' },
	{ slug: 'my-account', path: '/my-account/' },
];

const viewports = [
	{ slug: 'desktop', width: 1440, height: 1100 },
	{ slug: 'tablet', width: 768, height: 1024 },
	{ slug: 'mobile', width: 390, height: 844 },
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
			{ cwd: repoRoot, encoding: 'utf8', stdio: 'pipe' }
		);
	}

	return execFileSync( 'npm', [ 'run', 'env:cli', '--', 'wp', ...args ], {
		cwd: repoRoot,
		encoding: 'utf8',
		stdio: 'pipe',
	} );
}

function activateTheme( theme ) {
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

function ensureDir( dir ) {
	fs.mkdirSync( dir, { recursive: true } );
}

function boardHtml( theme ) {
	const cards = pages
		.map(
			( page ) => `
			<figure>
				<img src="../portfolio/${ theme.slug }/desktop/${ page.slug }.png" alt="${
					theme.name
				} ${ page.slug } desktop screenshot">
				<figcaption>${ page.slug.replace( '-', ' ' ) }</figcaption>
			</figure>`
		)
		.join( '' );

	return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${ theme.name } Direction Board</title>
<style>
body { margin: 0; background: #111; color: #f6f1e8; font-family: system-ui, sans-serif; }
main { padding: 48px; }
h1 { font-size: 64px; line-height: .92; letter-spacing: -.06em; margin: 0 0 16px; max-width: 980px; }
p { color: #c9c0b6; font-size: 20px; line-height: 1.6; max-width: 860px; }
.grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 42px; }
figure { background: #1c1c1c; border: 1px solid rgba(255,255,255,.1); margin: 0; overflow: hidden; }
img { display: block; height: 360px; object-fit: cover; object-position: top; width: 100%; }
figcaption { color: #f6f1e8; font-size: 13px; letter-spacing: .12em; padding: 14px; text-transform: uppercase; }
</style>
</head>
<body>
<main>
<h1>${ theme.name }</h1>
<p>${ theme.direction }</p>
<section class="grid">${ cards }</section>
</main>
</body>
</html>`;
}

( async () => {
	ensureDir( outputRoot );
	ensureDir( boardRoot );
	const browser = await chromium.launch( { headless: true } );

	for ( const theme of themes ) {
		activateTheme( theme.theme );

		for ( const viewport of viewports ) {
			const page = await browser.newPage( {
				viewport: { width: viewport.width, height: viewport.height },
			} );
			for ( const item of pages ) {
				const directory = path.join(
					outputRoot,
					theme.slug,
					viewport.slug
				);
				ensureDir( directory );
				await page.goto( `${ baseURL }${ item.path }`, {
					waitUntil: 'networkidle',
				} );
				await page.screenshot( {
					path: path.join( directory, `${ item.slug }.png` ),
					fullPage: true,
				} );
			}
			await page.close();
		}

		const htmlPath = path.join( boardRoot, `${ theme.slug }.html` );
		const pngPath = path.join( boardRoot, `${ theme.slug }.png` );
		fs.writeFileSync( htmlPath, boardHtml( theme ) );

		const boardPage = await browser.newPage( {
			viewport: { width: 1800, height: 1400 },
		} );
		await boardPage.goto( `file://${ htmlPath }`, { waitUntil: 'load' } );
		await boardPage.screenshot( { path: pngPath, fullPage: true } );
		await boardPage.close();
	}

	await browser.close();
	// eslint-disable-next-line no-console
	console.log( `Showcase screenshots written to ${ outputRoot }` );
	// eslint-disable-next-line no-console
	console.log( `Comparison boards written to ${ boardRoot }` );
} )().catch( ( error ) => {
	// eslint-disable-next-line no-console
	console.error( error );
	process.exit( 1 );
} );

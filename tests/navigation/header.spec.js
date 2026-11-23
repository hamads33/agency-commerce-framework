const { expect, test } = require( '@playwright/test' );
const { execFileSync } = require( 'node:child_process' );

const baseURL = process.env.WP_BASE_URL || 'http://localhost:8888';
const compose = process.env.WP_ENV_COMPOSE_FILE;
const widths = [ 320, 375, 390, 430, 768, 1024, 1280, 1440, 1920 ];

test.describe.configure( { mode: 'serial' } );

function wp( ...args ) {
	return compose
		? execFileSync(
				'docker',
				[
					'compose',
					'-f',
					compose,
					'exec',
					'-T',
					'cli',
					'wp',
					...args,
				],
				{ encoding: 'utf8' }
		  )
		: execFileSync( 'npm', [ 'run', 'env:cli', '--', 'wp', ...args ], {
				encoding: 'utf8',
		  } );
}

test.beforeAll( () => {
	if ( process.env.AGENCY_NAVIGATION_FIXTURES !== '1' ) {
		throw new Error(
			'Set AGENCY_NAVIGATION_FIXTURES=1 on a disposable WordPress instance.'
		);
	}
	if (
		! [ 'localhost', '127.0.0.1' ].includes( new URL( baseURL ).hostname )
	) {
		throw new Error(
			'Navigation fixtures require a loopback WordPress instance.'
		);
	}
	wp(
		'eval',
		`
		$menu = wp_get_nav_menu_object( 'Phase 1B navigation' );
		$id = $menu ? $menu->term_id : wp_create_nav_menu( 'Phase 1B navigation' );
		foreach ( wp_get_nav_menu_items( $id ) ?: array() as $item ) {
			wp_delete_post( $item->ID, true );
		}
		$add = static function( $title, $parent = 0 ) use ( $id ) {
			return wp_update_nav_menu_item( $id, 0, array(
				'menu-item-title' => $title, 'menu-item-url' => home_url( '/shop/' ),
				'menu-item-status' => 'publish', 'menu-item-type' => 'custom', 'menu-item-parent-id' => $parent,
			) );
		};
		$parent = $add( 'Shop' );
		$add( 'New arrivals', $parent );
		$add( 'Collections and seasonal recommendations' );
		$add( 'About' );
		update_option( 'woocommerce_coming_soon', 'no' );
	`
	);
} );

for ( const theme of [ 'fashion', 'electronics' ] ) {
	test( `${ theme } shared header`, async ( { page }, info ) => {
		test.setTimeout( 180000 );
		wp( 'theme', 'activate', 'agency-commerce-' + theme );
		wp(
			'eval',
			`
			$menu = wp_get_nav_menu_object( 'Phase 1B navigation' );
			set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu->term_id ) );
			update_option( 'blogname', '${
				theme === 'fashion' ? 'Maison Alder' : 'Signal Supply'
			}' );
			delete_option( 'agency_commerce_core_settings' );
		`
		);

		for ( const width of widths ) {
			await page.setViewportSize( { width, height: 900 } );
			await page.goto( baseURL, { waitUntil: 'domcontentloaded' } );
			const header = page.locator( '.ac-site-header' );
			const menu = header.getByRole( 'button', {
				name: 'Menu',
				exact: true,
			} );
			const search = header.getByRole( 'button', {
				name: 'Search',
				exact: true,
			} );
			const account = header.getByRole( 'link', {
				name: 'Account',
				exact: true,
			} );
			const cart = header.locator(
				'.wc-block-mini-cart__button, .ac-cart-link'
			);
			await expect(
				header.locator( '.ac-site-branding a' )
			).toBeVisible();
			await expect( account ).toBeVisible();
			await expect( account ).toHaveAttribute(
				'href',
				/\/my-account\/?$/
			);
			await expect( header.locator( '.ac-announcement' ) ).toBeVisible();
			await expect( cart ).toBeVisible();
			for ( const control of [ search, account, cart ] ) {
				const bounds = await control.boundingBox();
				expect( bounds.width ).toBeGreaterThanOrEqual( 44 );
				expect( bounds.height ).toBeGreaterThanOrEqual( 44 );
				expect( bounds.x + bounds.width ).toBeLessThanOrEqual( width );
			}
			await search.focus();
			await page.keyboard.press( 'Enter' );
			await expect(
				page.locator( '#ac-search-panel input[name="s"]' )
			).toBeFocused();
			await page.keyboard.press( 'Escape' );
			await expect( search ).toBeFocused();

			if ( width === 1024 ) {
				await search.click();
				await page
					.locator( '#ac-search-panel input[name="s"]' )
					.fill( 'CP3 Simple' );
				await Promise.all( [
					page.waitForRequest( ( request ) => {
						const url = new URL( request.url() );
						return (
							request.isNavigationRequest() &&
							url.searchParams.get( 's' ) === 'CP3 Simple' &&
							url.searchParams.get( 'post_type' ) === 'product'
						);
					} ),
					page
						.locator( '#ac-search-panel input[name="s"]' )
						.press( 'Enter' ),
				] );
				await expect(
					page
						.getByRole( 'heading', { name: 'CP3 Simple Product' } )
						.first()
				).toBeVisible();
				await page.goto( baseURL, { waitUntil: 'domcontentloaded' } );
			}

			const desktop = width >= 1120;
			if ( desktop ) {
				await expect( menu ).toBeHidden();
			} else {
				if ( width === 430 ) {
					await page.locator( 'html' ).evaluate( ( root ) => {
						root.dir = 'rtl';
					} );
				}
				await menu.click();
				const drawer = page.getByRole( 'dialog', {
					name: 'Mobile menu',
				} );
				await expect( drawer ).toBeVisible();
				if ( width === 430 ) {
					const bounds = await drawer.boundingBox();
					expect( bounds.x + bounds.width ).toBeCloseTo( width, 0 );
				}
				await expect(
					drawer.getByRole( 'button', { name: 'Close' } )
				).toBeFocused();
				await account.evaluate( ( link ) => link.focus() );
				await expect( account ).not.toBeFocused();
				if ( width === 390 ) {
					await page.screenshot( {
						path: info.outputPath( theme + '-390-drawer.png' ),
					} );
				}
			}

			const nav = page.getByRole( 'navigation', {
				name: desktop ? 'Primary menu' : 'Mobile primary menu',
				exact: true,
			} );
			const disclosure = nav.getByRole( 'button', {
				name: 'Shop',
				exact: true,
			} );
			await disclosure.focus();
			await page.keyboard.press( 'Space' );
			await expect(
				nav.getByRole( 'link', { name: 'New arrivals' } )
			).toBeVisible();
			const longLabel = nav.getByRole( 'link', {
				name: 'Collections and seasonal recommendations',
			} );
			const labelBounds = await longLabel.boundingBox();
			expect( labelBounds.x ).toBeGreaterThanOrEqual( 0 );
			expect( labelBounds.x + labelBounds.width ).toBeLessThanOrEqual(
				width
			);
			if ( width === 1440 ) {
				await page.screenshot( {
					path: info.outputPath( theme + '-1440-dropdown.png' ),
				} );
			}
			await page.keyboard.press( 'Escape' );
			await expect( disclosure ).toBeFocused();
			await expect(
				nav.getByRole( 'link', { name: 'New arrivals' } )
			).toBeHidden();

			if ( ! desktop ) {
				await page.keyboard.press( 'Escape' );
				await expect( menu ).toBeFocused();
			}

			if ( [ 390, 768, 1440 ].includes( width ) ) {
				await page.evaluate( () => document.activeElement.blur() );
				await header.screenshot( {
					path: info.outputPath( `${ theme }-${ width }.png` ),
				} );
			}
		}
	} );
}

test( 'native Mini-Cart and fragment fallback', async ( { page } ) => {
	test.setTimeout( 90000 );
	const productId = Number(
		wp( 'eval', 'echo (int) wc_get_product_id_by_sku( "CP3-SIMPLE" );' )
			.trim()
			.split( /\r?\n/ )
			.pop()
	);
	expect( productId ).toBeGreaterThan( 0 );
	await page.setViewportSize( { width: 390, height: 850 } );
	await page.goto( baseURL, { waitUntil: 'domcontentloaded' } );
	await expect(
		page.locator( '.ac-site-header .wc-block-mini-cart__button' )
	).toBeVisible();
	await page.locator( '.ac-site-header .wc-block-mini-cart__button' ).click();
	await expect( page.locator( '.wc-block-mini-cart__drawer' ) ).toBeVisible();
	await page.goto( `${ baseURL }/?add-to-cart=${ productId }`, {
		waitUntil: 'domcontentloaded',
	} );
	await expect(
		page.locator( '.ac-site-header .wc-block-mini-cart__badge' )
	).toHaveText( '1' );

	wp(
		'option',
		'update',
		'agency_commerce_core_settings',
		'{"cart":{"enable_mini_cart":false}}',
		'--format=json'
	);
	for ( const width of widths ) {
		await page.setViewportSize( { width, height: 850 } );
		await page.goto( baseURL, { waitUntil: 'domcontentloaded' } );
		const fallback = page.locator( '.ac-site-header .ac-cart-link' );
		await expect( fallback ).toHaveAttribute( 'href', /\/cart\/?$/ );
		await expect( fallback.locator( 'span.ac-cart-count' ) ).toHaveText(
			'1'
		);
	}
} );

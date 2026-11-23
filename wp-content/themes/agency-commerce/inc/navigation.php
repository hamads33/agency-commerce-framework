<?php
/**
 * Header and footer presentation helpers.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns a component template path, allowing child-theme overrides.
 *
 * @param string $template Relative template path.
 * @return string
 */
function agency_commerce_component_path( string $template ): string {
	$located = locate_template( $template, false, false );

	if ( $located ) {
		return $located;
	}

	return AGENCY_COMMERCE_THEME_DIR . $template;
}

/**
 * Sanitizes a configured variant against known values.
 *
 * @param string   $configured Configured variant.
 * @param string[] $allowed    Allowed variants.
 * @param string   $fallback   Fallback variant.
 * @return string
 */
function agency_commerce_sanitize_variant( string $configured, array $allowed, string $fallback ): string {
	return in_array( $configured, $allowed, true ) ? $configured : $fallback;
}

/**
 * Returns data shared by all header variants.
 *
 * @return array<string, mixed>
 */
function agency_commerce_get_header_data(): array {
	$variant = agency_commerce_sanitize_variant(
		(string) agency_commerce_get_setting( 'header.variant', 'header-default' ),
		array( 'header-default', 'header-centered' ),
		'header-default'
	);

	$announcement_text = (string) agency_commerce_get_setting( 'header.announcement', '' );
	$announcement_url  = (string) agency_commerce_get_setting( 'header.announcement_link', '' );
	$show_announcement = (bool) agency_commerce_get_setting( 'header.announcement_bar', false ) && '' !== trim( $announcement_text );
	$is_woocommerce    = function_exists( 'WC' );
	$show_cart         = (bool) agency_commerce_get_setting( 'header.show_cart', true ) && $is_woocommerce;

	return array(
		'variant'           => $variant,
		'sticky'            => (bool) agency_commerce_get_setting( 'header.sticky', false ),
		'show_announcement' => $show_announcement,
		'announcement_text' => $announcement_text,
		'announcement_url'  => $announcement_url,
		'show_search'       => (bool) agency_commerce_get_setting( 'header.show_search', true ),
		'show_account'      => (bool) agency_commerce_get_setting( 'header.show_account', true ),
		'show_cart'         => $show_cart,
		'show_cart_count'   => (bool) agency_commerce_get_setting( 'header.show_cart_count', true ) && $show_cart,
		'account_url'       => agency_commerce_get_account_url(),
		'cart_url'          => $is_woocommerce && function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
		'home_url'          => home_url( '/' ),
	);
}

/**
 * Returns data shared by all footer variants.
 *
 * @return array<string, mixed>
 */
function agency_commerce_get_footer_data(): array {
	$variant = agency_commerce_sanitize_variant(
		(string) agency_commerce_get_setting( 'footer.variant', 'footer-columns' ),
		array( 'footer-columns', 'footer-minimal' ),
		'footer-columns'
	);

	$social = agency_commerce_get_setting( 'social', array() );

	if ( ! is_array( $social ) ) {
		$social = array();
	}

	return array(
		'variant'       => $variant,
		'contact_email' => (string) agency_commerce_get_setting( 'store_identity.contact_email', '' ),
		'contact_phone' => (string) agency_commerce_get_setting( 'store_identity.contact_phone', '' ),
		'address'       => (string) agency_commerce_get_setting( 'store_identity.address', '' ),
		'trust_text'    => (string) agency_commerce_get_setting( 'footer.trust_text', '' ),
		'social'        => array_filter( $social, 'is_string' ),
	);
}

/**
 * Returns the account URL with WooCommerce fallback handling.
 */
function agency_commerce_get_account_url(): string {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'myaccount' );

		if ( $url ) {
			return $url;
		}
	}

	return wp_login_url();
}

/**
 * Renders the site logo or text fallback.
 */
function agency_commerce_render_logo(): void {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}

	?>
	<a class="ac-site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
	</a>
	<?php
}

/**
 * Renders a menu fallback when no menu is assigned.
 */
function agency_commerce_menu_fallback(): void {
	?>
	<ul class="ac-menu ac-menu--fallback">
		<li class="menu-item">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'agency-commerce' ); ?></a>
		</li>
		<?php
		wp_list_pages(
			array(
				'depth'    => 1,
				'title_li' => '',
			)
		);
		?>
	</ul>
	<?php
}

/**
 * Renders a navigation menu.
 *
 * @param string $location Menu location.
 * @param string $menu_class Menu class.
 */
function agency_commerce_render_menu( string $location, string $menu_class ): void {
	wp_nav_menu(
		array(
			'container'      => false,
			'fallback_cb'    => 'agency_commerce_menu_fallback',
			'menu_class'     => $menu_class,
			'theme_location' => $location,
			'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
			'depth'          => 2,
		)
	);
}

/**
 * Renders a decorative icon for a labelled storefront control.
 *
 * @param string $name Icon name.
 */
function agency_commerce_header_icon( string $name ): void {
	$paths = array(
		'search'  => 'M15.5 15.5 21 21M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z',
		'account' => 'M20 21v-2a5 5 0 0 0-5-5H9a5 5 0 0 0-5 5v2M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z',
		'cart'    => 'M4 8h16l1 13H3L4 8Zm4 1V6a4 4 0 0 1 8 0v3',
		'menu'    => 'M3 6h18M3 12h18M3 18h18',
		'close'   => 'm5 5 14 14M19 5 5 19',
	);

	if ( isset( $paths[ $name ] ) ) {
		printf( '<svg class="ac-header-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="%s"/></svg>', esc_attr( $paths[ $name ] ) );
	}
}

/**
 * Renders header controls shared by variants.
 *
 * @param array<string, mixed> $header Header data.
 */
function agency_commerce_render_header_controls( array $header ): void {
	?>
	<div class="ac-header-controls">
		<?php if ( ! empty( $header['show_search'] ) ) : ?>
			<button class="ac-icon-button ac-search-toggle" type="button" aria-expanded="false" aria-controls="ac-search-panel">
				<?php agency_commerce_header_icon( 'search' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Search', 'agency-commerce' ); ?></span>
			</button>
		<?php endif; ?>

		<?php if ( ! empty( $header['show_account'] ) ) : ?>
			<a class="ac-icon-button" href="<?php echo esc_url( (string) $header['account_url'] ); ?>">
				<?php agency_commerce_header_icon( 'account' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Account', 'agency-commerce' ); ?></span>
			</a>
		<?php endif; ?>

		<?php if ( ! empty( $header['show_cart'] ) && ! empty( $header['cart_url'] ) ) : ?>
			<?php agency_commerce_render_cart_control( $header ); ?>
		<?php endif; ?>

		<button class="ac-icon-button ac-menu-toggle" type="button" aria-expanded="false" aria-controls="ac-mobile-menu" aria-haspopup="dialog">
			<?php agency_commerce_header_icon( 'menu' ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'agency-commerce' ); ?></span>
		</button>
	</div>
	<?php
}

/**
 * Renders the WooCommerce Mini-Cart block when enabled, with a link fallback.
 *
 * @param array<string, mixed> $header Header data.
 */
function agency_commerce_render_cart_control( array $header ): void {
	$use_mini_cart = (bool) agency_commerce_get_setting( 'cart.enable_mini_cart', true );
	$has_block     = class_exists( 'WP_Block_Type_Registry' ) && WP_Block_Type_Registry::get_instance()->is_registered( 'woocommerce/mini-cart' );

	if ( $use_mini_cart && $has_block && function_exists( 'do_blocks' ) ) {
		?>
		<div class="ac-mini-cart-block" aria-label="<?php esc_attr_e( 'Shopping cart', 'agency-commerce' ); ?>">
			<?php echo do_blocks( '<!-- wp:woocommerce/mini-cart /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce renders and escapes block output. ?>
		</div>
		<?php
		return;
	}

	?>
	<a class="ac-icon-button ac-cart-link" href="<?php echo esc_url( (string) $header['cart_url'] ); ?>">
		<?php agency_commerce_header_icon( 'cart' ); ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Cart', 'agency-commerce' ); ?></span>
		<?php
		if ( ! empty( $header['show_cart_count'] ) && function_exists( 'agency_commerce_cart_count_markup' ) ) {
			echo agency_commerce_cart_count_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is escaped in helper.
		}
		?>
	</a>
	<?php
}

/**
 * Renders the search panel.
 */
function agency_commerce_render_search_panel(): void {
	if ( empty( agency_commerce_get_header_data()['show_search'] ) ) {
		return;
	}

	?>
	<div id="ac-search-panel" class="ac-search-panel" hidden>
		<div class="ac-container ac-search-panel__inner">
			<?php get_search_form(); ?>
			<button class="ac-button ac-button--secondary ac-search-close" type="button">
				<?php esc_html_e( 'Close', 'agency-commerce' ); ?>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Renders the mobile menu panel.
 */
function agency_commerce_render_mobile_menu(): void {
	?>
	<dialog id="ac-mobile-menu" class="ac-mobile-menu" aria-label="<?php esc_attr_e( 'Mobile menu', 'agency-commerce' ); ?>">
		<div class="ac-mobile-menu__header">
			<?php agency_commerce_render_logo(); ?>
			<button class="ac-icon-button ac-mobile-menu__close" type="button" data-mobile-menu-close autofocus>
				<?php agency_commerce_header_icon( 'close' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Close', 'agency-commerce' ); ?></span>
			</button>
		</div>
		<nav class="ac-mobile-navigation" aria-label="<?php esc_attr_e( 'Mobile primary menu', 'agency-commerce' ); ?>">
			<?php agency_commerce_render_menu( 'primary', 'ac-menu ac-menu--mobile' ); ?>
		</nav>
	</dialog>
	<?php
}

/**
 * Renders configured social links.
 *
 * @param array<string, string> $social Social links.
 */
function agency_commerce_render_social_links( array $social ): void {
	$social = array_filter(
		$social,
		static function ( string $url ): bool {
			return '' !== trim( $url );
		}
	);

	if ( empty( $social ) ) {
		return;
	}

	$labels = array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'linkedin'  => 'LinkedIn',
		'tiktok'    => 'TikTok',
		'x'         => 'X',
		'pinterest' => 'Pinterest',
		'youtube'   => 'YouTube',
	);

	?>
	<ul class="ac-social-links">
		<?php foreach ( $social as $network => $url ) : ?>
			<li>
				<a href="<?php echo esc_url( $url ); ?>" rel="me noopener noreferrer">
					<?php echo esc_html( $labels[ $network ] ?? ucwords( str_replace( array( '_', '-' ), ' ', (string) $network ) ) ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

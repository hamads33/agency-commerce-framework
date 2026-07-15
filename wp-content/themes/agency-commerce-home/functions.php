<?php
/**
 * Home and furniture showcase child theme bootstrap.
 *
 * @package AgencyCommerceHome
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AGENCY_COMMERCE_HOME_VERSION', '1.0.0' );

add_action( 'wp_enqueue_scripts', 'agency_commerce_home_enqueue_assets' );
add_filter( 'body_class', 'agency_commerce_home_body_class' );
add_filter( 'agency_commerce_core_defaults', 'agency_commerce_home_defaults' );
add_filter( 'agency_commerce_default_settings', 'agency_commerce_home_defaults' );

/**
 * Enqueues the showcase stylesheet after the parent storefront stylesheet.
 */
function agency_commerce_home_enqueue_assets(): void {
	wp_enqueue_style(
		'agency-commerce-home',
		get_stylesheet_uri(),
		array( 'agency-commerce' ),
		AGENCY_COMMERCE_HOME_VERSION
	);
}

/**
 * Adds a stable body class for storefront-specific presentation.
 *
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function agency_commerce_home_body_class( array $classes ): array {
	$classes[] = 'ac-showcase-home-store';
	return $classes;
}

/**
 * Applies child-theme presentation defaults when no store settings override them.
 *
 * @param array<string, mixed> $defaults Existing defaults.
 * @return array<string, mixed>
 */
function agency_commerce_home_defaults( array $defaults ): array {
	return array_replace_recursive(
		$defaults,
		array(
			'design'       => array(
				'primary_color'          => '#4a3528',
				'secondary_color'        => '#7c5c45',
				'accent_color'           => '#c9824a',
				'text_color'             => '#2f261f',
				'muted_color'            => '#7b6b5e',
				'background_color'       => '#f3eadf',
				'surface_color'          => '#fff8ef',
				'border_color'           => '#d8c7b4',
				'container_width'        => '1320px',
				'narrow_container_width' => '860px',
				'spacing_density'        => 'airy',
				'shadow_level'           => 'balanced',
				'body_font'              => '"Iowan Old Style", "Palatino Linotype", Georgia, serif',
				'heading_font'           => '"Optima", "Gill Sans", "Trebuchet MS", sans-serif',
				'button_radius'          => '999px',
				'card_radius'            => '30px',
				'product_image_ratio'    => '5 / 4',
			),
			'header'       => array(
				'variant'          => 'header-default',
				'sticky'           => true,
				'announcement_bar' => true,
				'announcement'     => 'Design consultation language, warm editorial sections, and room-first shopping',
			),
			'shop'         => array(
				'card_variant'     => 'product-card-classic',
				'products_per_row' => 3,
				'filter_layout'    => 'sidebar',
			),
			'product_page' => array(
				'layout'         => 'product-layout-standard',
				'sticky_summary' => false,
			),
			'footer'       => array(
				'variant'    => 'footer-columns',
				'trust_text' => 'Warm furniture showcase built with Agency Commerce.',
			),
			'checkout'     => array(
				'support_text' => 'Need help planning a room? Add a note and the studio team will follow up.',
				'trust_text'   => 'Secure checkout with delivery and room-planning support.',
			),
		)
	);
}

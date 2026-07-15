<?php
/**
 * Electronics showcase child theme bootstrap.
 *
 * @package AgencyCommerceElectronics
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AGENCY_COMMERCE_ELECTRONICS_VERSION', '1.0.0' );

add_action( 'wp_enqueue_scripts', 'agency_commerce_electronics_enqueue_assets' );
add_filter( 'body_class', 'agency_commerce_electronics_body_class' );
add_filter( 'agency_commerce_core_defaults', 'agency_commerce_electronics_defaults' );
add_filter( 'agency_commerce_default_settings', 'agency_commerce_electronics_defaults' );

/**
 * Enqueues the showcase stylesheet after the parent storefront stylesheet.
 */
function agency_commerce_electronics_enqueue_assets(): void {
	wp_enqueue_style(
		'agency-commerce-electronics',
		get_stylesheet_uri(),
		array( 'agency-commerce' ),
		AGENCY_COMMERCE_ELECTRONICS_VERSION
	);
}

/**
 * Adds a stable body class for storefront-specific presentation.
 *
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function agency_commerce_electronics_body_class( array $classes ): array {
	$classes[] = 'ac-showcase-electronics';
	return $classes;
}

/**
 * Applies child-theme presentation defaults when no store settings override them.
 *
 * @param array<string, mixed> $defaults Existing defaults.
 * @return array<string, mixed>
 */
function agency_commerce_electronics_defaults( array $defaults ): array {
	return array_replace_recursive(
		$defaults,
		array(
			'design'       => array(
				'primary_color'          => '#f5f7fb',
				'secondary_color'        => '#d6e2ff',
				'accent_color'           => '#78ffdb',
				'text_color'             => '#f5f7fb',
				'muted_color'            => '#9aa7bb',
				'background_color'       => '#05070b',
				'surface_color'          => '#10151f',
				'border_color'           => '#263142',
				'container_width'        => '1280px',
				'narrow_container_width' => '780px',
				'spacing_density'        => 'comfortable',
				'shadow_level'           => 'elevated',
				'body_font'              => '"Aptos", "Segoe UI", system-ui, sans-serif',
				'heading_font'           => '"Aptos Display", "Segoe UI", system-ui, sans-serif',
				'button_radius'          => '999px',
				'card_radius'            => '24px',
				'product_image_ratio'    => '1 / 1',
			),
			'header'       => array(
				'variant'          => 'header-default',
				'sticky'           => true,
				'announcement_bar' => true,
				'announcement'     => 'Prototype-grade demo: technical layouts, specs, and product-first flows',
			),
			'shop'         => array(
				'card_variant'     => 'product-card-compact',
				'products_per_row' => 4,
				'filter_layout'    => 'drawer',
				'show_rating'      => false,
			),
			'product_page' => array(
				'layout'         => 'product-layout-sticky',
				'sticky_summary' => true,
			),
			'footer'       => array(
				'variant'    => 'footer-columns',
				'trust_text' => 'Precision storefront showcase built with Agency Commerce.',
			),
			'checkout'     => array(
				'support_text' => 'Technical support is available before and after checkout.',
				'trust_text'   => 'Secure checkout with transparent configuration details.',
			),
		)
	);
}

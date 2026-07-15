<?php
/**
 * Luxury fashion showcase child theme bootstrap.
 *
 * @package AgencyCommerceFashion
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AGENCY_COMMERCE_FASHION_VERSION', '1.0.0' );

add_action( 'wp_enqueue_scripts', 'agency_commerce_fashion_enqueue_assets' );
add_filter( 'body_class', 'agency_commerce_fashion_body_class' );
add_filter( 'agency_commerce_core_defaults', 'agency_commerce_fashion_defaults' );
add_filter( 'agency_commerce_default_settings', 'agency_commerce_fashion_defaults' );

/**
 * Enqueues the showcase stylesheet after the parent storefront stylesheet.
 */
function agency_commerce_fashion_enqueue_assets(): void {
	wp_enqueue_style(
		'agency-commerce-fashion',
		get_stylesheet_uri(),
		array( 'agency-commerce' ),
		AGENCY_COMMERCE_FASHION_VERSION
	);
}

/**
 * Adds a stable body class for storefront-specific presentation.
 *
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function agency_commerce_fashion_body_class( array $classes ): array {
	$classes[] = 'ac-showcase-fashion';
	return $classes;
}

/**
 * Applies child-theme presentation defaults when no store settings override them.
 *
 * @param array<string, mixed> $defaults Existing defaults.
 * @return array<string, mixed>
 */
function agency_commerce_fashion_defaults( array $defaults ): array {
	return array_replace_recursive(
		$defaults,
		array(
			'design'       => array(
				'primary_color'          => '#181614',
				'secondary_color'        => '#3d3730',
				'accent_color'           => '#a8855a',
				'text_color'             => '#181614',
				'muted_color'            => '#736b62',
				'background_color'       => '#f7f2eb',
				'surface_color'          => '#fffaf3',
				'border_color'           => '#ded2c4',
				'container_width'        => '1360px',
				'narrow_container_width' => '820px',
				'spacing_density'        => 'airy',
				'shadow_level'           => 'soft',
				'body_font'              => 'Georgia, "Times New Roman", serif',
				'heading_font'           => 'Didot, "Bodoni 72", Georgia, serif',
				'button_radius'          => '0px',
				'card_radius'            => '2px',
				'product_image_ratio'    => '4 / 5',
			),
			'header'       => array(
				'variant'          => 'header-centered',
				'sticky'           => true,
				'announcement_bar' => true,
				'announcement'     => 'Private editorial preview: complimentary styling notes with every order',
			),
			'shop'         => array(
				'card_variant'     => 'product-card-minimal',
				'products_per_row' => 3,
				'filter_layout'    => 'drawer',
				'show_rating'      => false,
			),
			'product_page' => array(
				'layout'         => 'product-layout-sticky',
				'sticky_summary' => true,
			),
			'footer'       => array(
				'variant'    => 'footer-minimal',
				'trust_text' => 'Quiet luxury showcase built with Agency Commerce.',
			),
			'checkout'     => array(
				'support_text' => 'A stylist reviews every order before dispatch.',
				'trust_text'   => 'Secure checkout, considered packaging, and flexible returns.',
			),
		)
	);
}

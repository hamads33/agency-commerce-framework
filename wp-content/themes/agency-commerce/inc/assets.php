<?php
/**
 * Asset registration.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'agency_commerce_enqueue_assets' );

/**
 * Enqueues the foundational parent stylesheet.
 */
function agency_commerce_enqueue_assets(): void {
	wp_enqueue_style(
		'agency-commerce',
		AGENCY_COMMERCE_THEME_URI . 'style.css',
		array(),
		AGENCY_COMMERCE_VERSION
	);

	wp_enqueue_script(
		'agency-commerce-navigation',
		AGENCY_COMMERCE_THEME_URI . 'assets/js/navigation.js',
		array(),
		AGENCY_COMMERCE_VERSION,
		true
	);
}

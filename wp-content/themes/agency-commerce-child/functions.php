<?php
/**
 * Child theme bootstrap.
 *
 * @package AgencyCommerceChild
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AGENCY_COMMERCE_CHILD_VERSION', '0.1.0' );

add_action( 'wp_enqueue_scripts', 'agency_commerce_child_enqueue_assets' );

/**
 * Enqueues child theme stylesheet after the parent stylesheet.
 */
function agency_commerce_child_enqueue_assets(): void {
	wp_enqueue_style(
		'agency-commerce-child',
		get_stylesheet_uri(),
		array( 'agency-commerce' ),
		AGENCY_COMMERCE_CHILD_VERSION
	);
}

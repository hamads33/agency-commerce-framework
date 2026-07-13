<?php
/**
 * WooCommerce compatibility declarations.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'before_woocommerce_init', 'agency_commerce_declare_woocommerce_features' );

/**
 * Declares compatibility with WooCommerce block cart and checkout where available.
 */
function agency_commerce_declare_woocommerce_features(): void {
	if ( ! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		return;
	}

	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
		'cart_checkout_blocks',
		AGENCY_COMMERCE_THEME_DIR . 'functions.php',
		true
	);
}

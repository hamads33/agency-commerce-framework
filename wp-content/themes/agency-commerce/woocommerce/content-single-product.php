<?php
/**
 * Single product content template.
 *
 * @package AgencyCommerce
 * @version 10.9.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core returns the password form markup.
	return;
}

agency_commerce_render_product_layout();

do_action( 'woocommerce_after_single_product' );

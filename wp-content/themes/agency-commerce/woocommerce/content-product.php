<?php
/**
 * Product content template.
 *
 * @package AgencyCommerce
 * @version 10.9.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
	return;
}

agency_commerce_render_product_card( $product );

<?php
/**
 * Footer component router.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$agency_commerce_footer_variant = (string) $agency_commerce_footer_data['variant'];
$agency_commerce_footer_file    = agency_commerce_component_path( 'components/footer/' . $agency_commerce_footer_variant . '.php' );

if ( ! file_exists( $agency_commerce_footer_file ) ) {
	$agency_commerce_footer_file = agency_commerce_component_path( 'components/footer/footer-columns.php' );
}

require $agency_commerce_footer_file;

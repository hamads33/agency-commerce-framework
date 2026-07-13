<?php
/**
 * Header component router.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$agency_commerce_header_variant = (string) $agency_commerce_header_data['variant'];
$agency_commerce_header_file    = agency_commerce_component_path( 'components/header/' . $agency_commerce_header_variant . '.php' );

if ( ! file_exists( $agency_commerce_header_file ) ) {
	$agency_commerce_header_file = agency_commerce_component_path( 'components/header/header-default.php' );
}

require $agency_commerce_header_file;
agency_commerce_render_search_panel();
agency_commerce_render_mobile_menu();

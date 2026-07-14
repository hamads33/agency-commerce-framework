<?php
/**
 * Theme bootstrap.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AGENCY_COMMERCE_VERSION', '0.1.0' );
define( 'AGENCY_COMMERCE_THEME_DIR', trailingslashit( get_template_directory() ) );
define( 'AGENCY_COMMERCE_THEME_URI', trailingslashit( get_template_directory_uri() ) );

$agency_commerce_files = array(
	'inc/settings.php',
	'inc/setup.php',
	'inc/navigation.php',
	'inc/catalog.php',
	'inc/assets.php',
	'inc/woocommerce.php',
);

foreach ( $agency_commerce_files as $agency_commerce_file ) {
	require_once AGENCY_COMMERCE_THEME_DIR . $agency_commerce_file;
}

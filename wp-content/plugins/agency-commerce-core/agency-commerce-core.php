<?php
/**
 * Plugin Name: Agency Commerce Core
 * Plugin URI: https://example.com/agency-commerce-core
 * Description: Companion functionality layer for the Agency Commerce framework.
 * Version: 0.1.0
 * Requires at least: 7.0
 * Requires PHP: 8.2
 * Author: Agency Commerce
 * Text Domain: agency-commerce-core
 * Domain Path: /languages
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AGENCY_COMMERCE_CORE_VERSION', '0.1.0' );
define( 'AGENCY_COMMERCE_CORE_SETTINGS_SCHEMA_VERSION', 1 );
define( 'AGENCY_COMMERCE_CORE_FILE', __FILE__ );
define( 'AGENCY_COMMERCE_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'AGENCY_COMMERCE_CORE_URL', plugin_dir_url( __FILE__ ) );

$agency_commerce_core_autoload = AGENCY_COMMERCE_CORE_PATH . 'vendor/autoload.php';

if ( file_exists( $agency_commerce_core_autoload ) ) {
	require_once $agency_commerce_core_autoload;
} else {
	spl_autoload_register(
		static function ( string $class_name ): void {
			$prefix = 'AgencyCommerceCore\\';

			if ( 0 !== strpos( $class_name, $prefix ) ) {
				return;
			}

			$relative_class = substr( $class_name, strlen( $prefix ) );
			$file           = AGENCY_COMMERCE_CORE_PATH . 'includes/' . str_replace( '\\', '/', $relative_class ) . '.php';

			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	);
}

require_once AGENCY_COMMERCE_CORE_PATH . 'includes/functions.php';

add_action(
	'plugins_loaded',
	static function (): void {
		AgencyCommerceCore\Plugin::instance()->boot();
	}
);

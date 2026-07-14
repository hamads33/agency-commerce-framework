<?php
/**
 * Uninstall handler.
 *
 * Removes the framework options created by the plugin.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'agency_commerce_core_settings' );
delete_option( 'agency_commerce_core_settings_schema_version' );
delete_option( 'agency_commerce_core_active_preset' );
delete_option( 'agency_commerce_core_preset_snapshot' );

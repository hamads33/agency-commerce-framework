<?php
/**
 * Main plugin bootstrap.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore;

use AgencyCommerceCore\Admin\SettingsPage;
use AgencyCommerceCore\Settings\Defaults;

/**
 * Coordinates the CP1-safe plugin foundation.
 */
final class Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Returns the singleton instance.
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Registers foundational hooks.
	 */
	public function boot(): void {
		load_plugin_textdomain( 'agency-commerce-core', false, dirname( plugin_basename( AGENCY_COMMERCE_CORE_FILE ) ) . '/languages' );

		add_filter( 'agency_commerce_core_default_settings', array( Defaults::class, 'filter_defaults' ) );
		( new SettingsPage() )->register();

		/**
		 * Fires after the core plugin foundation has loaded.
		 *
		 * This is the first stable plugin-level extension point.
		 *
		 * @since 0.1.0
		 */
		do_action( 'agency_commerce_core_loaded' );
	}

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {}
}

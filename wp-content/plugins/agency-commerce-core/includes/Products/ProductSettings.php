<?php
/**
 * Product feature setting reads.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Products;

/**
 * Keeps plugin product features independent from parent-theme helpers.
 */
final class ProductSettings {
	/**
	 * Reads one plugin-owned setting.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $fallback Fallback value.
	 * @return mixed
	 */
	public static function get( string $key, $fallback = null ) {
		return agency_commerce_core_get_setting( $key, $fallback );
	}

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {}
}

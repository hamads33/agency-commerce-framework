<?php
/**
 * Public plugin helper functions.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

use AgencyCommerceCore\Settings\Defaults;
use AgencyCommerceCore\Settings\Sanitizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'agency_commerce_core_defaults' ) ) {
	/**
	 * Returns the plugin settings defaults.
	 *
	 * @return array<string, mixed>
	 */
	function agency_commerce_core_defaults(): array {
		/**
		 * Filters the public plugin defaults array.
		 *
		 * @since 0.1.0
		 *
		 * @param array<string, mixed> $defaults Default settings.
		 */
		return apply_filters( 'agency_commerce_core_default_settings', Defaults::all() );
	}
}

if ( ! function_exists( 'agency_commerce_core_get_settings' ) ) {
	/**
	 * Returns stored settings merged with defaults.
	 *
	 * The result is memoized for the request. Pass true to refresh after tests
	 * or programmatic option updates.
	 *
	 * @param bool $refresh Whether to refresh the memoized cache.
	 * @return array<string, mixed>
	 */
	function agency_commerce_core_get_settings( bool $refresh = false ): array {
		if ( $refresh ) {
			unset( $GLOBALS['agency_commerce_core_settings_cache'] );
		}

		if ( isset( $GLOBALS['agency_commerce_core_settings_cache'] ) && is_array( $GLOBALS['agency_commerce_core_settings_cache'] ) ) {
			return $GLOBALS['agency_commerce_core_settings_cache'];
		}

		$saved = get_option( 'agency_commerce_core_settings', array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$GLOBALS['agency_commerce_core_settings_cache'] = Sanitizer::sanitize_stored( $saved );

		return $GLOBALS['agency_commerce_core_settings_cache'];
	}
}

if ( ! function_exists( 'agency_commerce_core_flush_settings_cache' ) ) {
	/**
	 * Flushes the memoized settings cache.
	 */
	function agency_commerce_core_flush_settings_cache(): void {
		unset( $GLOBALS['agency_commerce_core_settings_cache'] );
	}
}

if ( ! function_exists( 'agency_commerce_core_get_setting' ) ) {
	/**
	 * Reads a setting by dot notation.
	 *
	 * @param string $key     Setting key, for example "header.variant".
	 * @param mixed  $fallback Fallback value.
	 * @return mixed
	 */
	function agency_commerce_core_get_setting( string $key, $fallback = null ) {
		$value = agency_commerce_core_get_settings();

		foreach ( explode( '.', $key ) as $part ) {
			if ( ! is_array( $value ) || ! array_key_exists( $part, $value ) ) {
				return $fallback;
			}

			$value = $value[ $part ];
		}

		return $value;
	}
}

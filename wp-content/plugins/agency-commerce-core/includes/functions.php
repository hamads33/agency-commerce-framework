<?php
/**
 * Public plugin helper functions.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

use AgencyCommerceCore\Settings\Defaults;

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
	 * @return array<string, mixed>
	 */
	function agency_commerce_core_get_settings(): array {
		static $settings = null;

		if ( null !== $settings ) {
			return $settings;
		}

		$saved = get_option( 'agency_commerce_core_settings', array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$settings = array_replace_recursive( agency_commerce_core_defaults(), $saved );

		return $settings;
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

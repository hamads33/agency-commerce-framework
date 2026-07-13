<?php
/**
 * Theme settings compatibility helpers.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'agency_commerce_default_settings' ) ) {
	/**
	 * Returns theme-safe defaults used when the core plugin is inactive.
	 *
	 * @return array<string, mixed>
	 */
	function agency_commerce_default_settings(): array {
		$defaults = array(
			'design'       => array(
				'primary_color'       => '#115e59',
				'secondary_color'     => '#1f2937',
				'accent_color'        => '#f59e0b',
				'body_font'           => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
				'heading_font'        => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
				'button_radius'       => '4px',
				'card_radius'         => '8px',
				'product_image_ratio' => '1 / 1',
			),
			'header'       => array(
				'variant' => 'header-default',
				'sticky'  => false,
			),
			'footer'       => array(
				'variant' => 'footer-columns',
			),
			'shop'         => array(
				'card_variant' => 'product-card-classic',
			),
			'product_page' => array(
				'layout' => 'product-layout-standard',
			),
			'checkout'     => array(
				'mode' => 'block',
			),
		);

		/**
		 * Filters fallback settings used by the parent theme.
		 *
		 * @since 0.1.0
		 *
		 * @param array<string, mixed> $defaults Theme defaults.
		 */
		return apply_filters( 'agency_commerce_default_settings', $defaults );
	}
}

if ( ! function_exists( 'agency_commerce_get_setting' ) ) {
	/**
	 * Reads a setting from the plugin when available, otherwise from theme defaults.
	 *
	 * @param string $key     Setting key, for example "header.variant".
	 * @param mixed  $fallback Fallback value.
	 * @return mixed
	 */
	function agency_commerce_get_setting( string $key, $fallback = null ) {
		if ( function_exists( 'agency_commerce_core_get_setting' ) ) {
			return agency_commerce_core_get_setting( $key, $fallback );
		}

		$value = agency_commerce_default_settings();

		foreach ( explode( '.', $key ) as $part ) {
			if ( ! is_array( $value ) || ! array_key_exists( $part, $value ) ) {
				return $fallback;
			}

			$value = $value[ $part ];
		}

		return $value;
	}
}

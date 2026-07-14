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
			'store_identity' => array(
				'logo_id'        => 0,
				'mobile_logo_id' => 0,
				'contact_email'  => '',
				'contact_phone'  => '',
				'address'        => '',
				'support_hours'  => '',
			),
			'design'         => array(
				'primary_color'       => '#115e59',
				'secondary_color'     => '#1f2937',
				'accent_color'        => '#f59e0b',
				'text_color'          => '#111827',
				'muted_color'         => '#6b7280',
				'background_color'    => '#ffffff',
				'surface_color'       => '#f9fafb',
				'border_color'        => '#d1d5db',
				'success_color'       => '#047857',
				'error_color'         => '#b91c1c',
				'body_font'           => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
				'heading_font'        => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
				'button_radius'       => '4px',
				'card_radius'         => '8px',
				'product_image_ratio' => '1 / 1',
			),
			'header'         => array(
				'variant'           => 'header-default',
				'sticky'            => false,
				'show_search'       => true,
				'show_account'      => true,
				'show_cart'         => true,
				'show_cart_count'   => true,
				'announcement_bar'  => false,
				'announcement'      => '',
				'announcement_link' => '',
			),
			'footer'         => array(
				'variant'    => 'footer-columns',
				'trust_text' => '',
			),
			'shop'           => array(
				'card_variant'        => 'product-card-classic',
				'products_per_row'    => 4,
				'products_per_page'   => 12,
				'filter_layout'       => 'sidebar',
				'show_rating'         => true,
				'show_quick_add'      => true,
				'enable_hover_image'  => true,
				'show_sale_badge'     => true,
				'show_stock_badge'    => true,
				'show_featured_badge' => true,
				'show_product_cat'    => true,
			),
			'product_page'   => array(
				'layout'          => 'product-layout-standard',
				'show_sku'        => true,
				'show_categories' => true,
				'show_tags'       => true,
				'related_count'   => 4,
				'sticky_summary'  => false,
			),
			'social'         => array(
				'facebook'  => '',
				'instagram' => '',
				'tiktok'    => '',
				'youtube'   => '',
				'linkedin'  => '',
				'x'         => '',
				'pinterest' => '',
			),
			'performance'    => array(
				'load_assets_conditionally' => true,
			),
			'developer'      => array(
				'debug_labels' => false,
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

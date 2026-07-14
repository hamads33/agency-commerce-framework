<?php
/**
 * Public plugin helper functions.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

use AgencyCommerceCore\Settings\Defaults;
use AgencyCommerceCore\Settings\Sanitizer;
use AgencyCommerceCore\Presets\Manager as PresetManager;

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

if ( ! function_exists( 'agency_commerce_core_presets' ) ) {
	/**
	 * Returns framework presets.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	function agency_commerce_core_presets(): array {
		return PresetManager::all();
	}
}

if ( ! function_exists( 'agency_commerce_core_get_preset' ) ) {
	/**
	 * Returns one framework preset by slug.
	 *
	 * @param string $slug Preset slug.
	 * @return array<string, mixed>|null
	 */
	function agency_commerce_core_get_preset( string $slug ): ?array {
		return PresetManager::get( $slug );
	}
}

if ( ! function_exists( 'agency_commerce_core_get_active_preset' ) ) {
	/**
	 * Returns the active preset slug.
	 */
	function agency_commerce_core_get_active_preset(): string {
		return PresetManager::active();
	}
}

if ( ! function_exists( 'agency_commerce_core_get_preset_snapshot' ) ) {
	/**
	 * Returns the previous preset snapshot.
	 *
	 * @return array<string, mixed>
	 */
	function agency_commerce_core_get_preset_snapshot(): array {
		return PresetManager::snapshot();
	}
}

if ( ! function_exists( 'agency_commerce_core_apply_preset' ) ) {
	/**
	 * Applies a framework preset.
	 *
	 * @param string $slug Preset slug.
	 */
	function agency_commerce_core_apply_preset( string $slug ): bool {
		return PresetManager::apply( $slug );
	}
}

if ( ! function_exists( 'agency_commerce_core_restore_previous_preset' ) ) {
	/**
	 * Restores the previous preset snapshot.
	 */
	function agency_commerce_core_restore_previous_preset(): bool {
		return PresetManager::restore_previous();
	}
}

if ( ! function_exists( 'agency_commerce_core_preview_asset_url' ) ) {
	/**
	 * Returns a static preview asset URL for a variant group.
	 *
	 * @param string $group Variant group.
	 * @param string $variant Variant slug.
	 */
	function agency_commerce_core_preview_asset_url( string $group, string $variant ): string {
		$group   = sanitize_key( $group );
		$variant = sanitize_key( $variant );
		$path    = 'assets/previews/' . $group . '/' . $variant . '.svg';

		return plugins_url( $path, AGENCY_COMMERCE_CORE_FILE );
	}
}

if ( ! function_exists( 'agency_commerce_get_product_badges' ) ) {
	/**
	 * Returns product badges for a product.
	 *
	 * @param WC_Product $product Product object.
	 * @return array<int, array<string, string>>
	 */
	function agency_commerce_get_product_badges( WC_Product $product ): array {
		return \AgencyCommerceCore\Products\FeatureManager::get_product_badges( $product );
	}
}

if ( ! function_exists( 'agency_commerce_render_product_badges' ) ) {
	/**
	 * Renders product badges.
	 *
	 * @param array<int, array<string, string>> $badges Badge data.
	 * @param string                            $wrapper_class Wrapper class.
	 */
	function agency_commerce_render_product_badges( array $badges, string $wrapper_class = 'ac-product-badges' ): void {
		\AgencyCommerceCore\Products\FeatureManager::render_product_badges( $badges, $wrapper_class );
	}
}

if ( ! function_exists( 'agency_commerce_get_delivery_message' ) ) {
	/**
	 * Returns the resolved delivery message.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	function agency_commerce_get_delivery_message( ?WC_Product $product = null ): string {
		return \AgencyCommerceCore\Products\FeatureManager::get_delivery_message( $product );
	}
}

if ( ! function_exists( 'agency_commerce_render_delivery_message' ) ) {
	/**
	 * Renders the resolved delivery message.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	function agency_commerce_render_delivery_message( ?WC_Product $product = null ): void {
		\AgencyCommerceCore\Products\FeatureManager::render_delivery_message( $product );
	}
}

if ( ! function_exists( 'agency_commerce_build_whatsapp_enquiry_url' ) ) {
	/**
	 * Returns a WhatsApp enquiry URL for a product.
	 *
	 * @param WC_Product $product Product object.
	 */
	function agency_commerce_build_whatsapp_enquiry_url( WC_Product $product ): string {
		$phone    = (string) agency_commerce_core_get_setting( 'whatsapp.phone_number', '' );
		$template = (string) agency_commerce_core_get_setting( 'whatsapp.message_template', '' );

		return \AgencyCommerceCore\Products\FeatureManager::build_whatsapp_url(
			$product,
			$template,
			$phone
		);
	}
}

if ( ! function_exists( 'agency_commerce_render_whatsapp_enquiry_button' ) ) {
	/**
	 * Renders the WhatsApp enquiry button.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	function agency_commerce_render_whatsapp_enquiry_button( ?WC_Product $product = null ): void {
		\AgencyCommerceCore\Products\FeatureManager::render_whatsapp_button( $product );
	}
}

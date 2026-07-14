<?php
/**
 * Framework preset engine.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Presets;

use AgencyCommerceCore\Settings\Sanitizer;

/**
 * Manages reusable framework presets.
 */
final class Manager {
	/**
	 * Active preset option name.
	 */
	public const ACTIVE_PRESET_OPTION = 'agency_commerce_core_active_preset';

	/**
	 * Previous preset snapshot option name.
	 */
	public const SNAPSHOT_OPTION = 'agency_commerce_core_preset_snapshot';

	/**
	 * Returns all presets.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function all(): array {
		$presets = array(
			'minimal'     => array(
				'label'       => __( 'Minimal', 'agency-commerce-core' ),
				'description' => __( 'Monochrome, compact, and understated.', 'agency-commerce-core' ),
				'settings'    => array(
					'design'       => array(
						'primary_color'          => '#111827',
						'secondary_color'        => '#374151',
						'accent_color'           => '#2563eb',
						'text_color'             => '#111827',
						'muted_color'            => '#6b7280',
						'background_color'       => '#ffffff',
						'surface_color'          => '#f8fafc',
						'border_color'           => '#e5e7eb',
						'success_color'          => '#15803d',
						'error_color'            => '#dc2626',
						'container_width'        => '1120px',
						'narrow_container_width' => '720px',
						'spacing_density'        => 'compact',
						'shadow_level'           => 'soft',
						'body_font'              => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
						'heading_font'           => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
						'button_radius'          => '4px',
						'card_radius'            => '8px',
						'product_image_ratio'    => '1 / 1',
					),
					'header'       => array(
						'variant' => 'header-default',
					),
					'footer'       => array(
						'variant' => 'footer-minimal',
					),
					'shop'         => array(
						'card_variant' => 'product-card-minimal',
					),
					'product_page' => array(
						'layout' => 'product-layout-standard',
					),
				),
			),
			'fashion'     => array(
				'label'       => __( 'Fashion', 'agency-commerce-core' ),
				'description' => __( 'Editorial spacing with a refined centered header.', 'agency-commerce-core' ),
				'settings'    => array(
					'design'       => array(
						'primary_color'          => '#7c3aed',
						'secondary_color'        => '#111827',
						'accent_color'           => '#ec4899',
						'text_color'             => '#111827',
						'muted_color'            => '#6b7280',
						'background_color'       => '#fffafc',
						'surface_color'          => '#ffffff',
						'border_color'           => '#f3e8ff',
						'success_color'          => '#0f766e',
						'error_color'            => '#be123c',
						'container_width'        => '1240px',
						'narrow_container_width' => '760px',
						'spacing_density'        => 'airy',
						'shadow_level'           => 'balanced',
						'body_font'              => '"Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
						'heading_font'           => '"Cormorant Garamond", Georgia, serif',
						'button_radius'          => '999px',
						'card_radius'            => '20px',
						'product_image_ratio'    => '4 / 5',
					),
					'header'       => array(
						'variant' => 'header-centered',
					),
					'footer'       => array(
						'variant' => 'footer-columns',
					),
					'shop'         => array(
						'card_variant' => 'product-card-classic',
					),
					'product_page' => array(
						'layout' => 'product-layout-sticky',
					),
				),
			),
			'electronics' => array(
				'label'       => __( 'Electronics', 'agency-commerce-core' ),
				'description' => __( 'High-contrast, practical, and information dense.', 'agency-commerce-core' ),
				'settings'    => array(
					'design'       => array(
						'primary_color'          => '#0f172a',
						'secondary_color'        => '#1d4ed8',
						'accent_color'           => '#22c55e',
						'text_color'             => '#0f172a',
						'muted_color'            => '#475569',
						'background_color'       => '#f8fafc',
						'surface_color'          => '#ffffff',
						'border_color'           => '#cbd5e1',
						'success_color'          => '#16a34a',
						'error_color'            => '#ef4444',
						'container_width'        => '1280px',
						'narrow_container_width' => '800px',
						'spacing_density'        => 'comfortable',
						'shadow_level'           => 'elevated',
						'body_font'              => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
						'heading_font'           => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
						'button_radius'          => '6px',
						'card_radius'            => '10px',
						'product_image_ratio'    => '16 / 10',
					),
					'header'       => array(
						'variant' => 'header-default',
					),
					'footer'       => array(
						'variant' => 'footer-minimal',
					),
					'shop'         => array(
						'card_variant' => 'product-card-compact',
					),
					'product_page' => array(
						'layout' => 'product-layout-sticky',
					),
				),
			),
		);

		/**
		 * Filters available framework presets.
		 *
		 * @param array<string, array<string, mixed>> $presets Preset definitions.
		 */
		return apply_filters( 'agency_commerce_core_presets', $presets );
	}

	/**
	 * Returns a preset by slug.
	 *
	 * @param string $slug Preset slug.
	 * @return array<string, mixed>|null
	 */
	public static function get( string $slug ): ?array {
		$presets = self::all();

		return array_key_exists( $slug, $presets ) ? $presets[ $slug ] : null;
	}

	/**
	 * Returns the active preset slug.
	 */
	public static function active(): string {
		return (string) get_option( self::ACTIVE_PRESET_OPTION, '' );
	}

	/**
	 * Returns the previous preset snapshot.
	 *
	 * @return array<string, mixed>
	 */
	public static function snapshot(): array {
		$snapshot = get_option( self::SNAPSHOT_OPTION, array() );

		return is_array( $snapshot ) ? $snapshot : array();
	}

	/**
	 * Applies a preset to framework settings.
	 *
	 * @param string $slug Preset slug.
	 */
	public static function apply( string $slug ): bool {
		$preset = self::get( $slug );

		if ( ! is_array( $preset ) ) {
			return false;
		}

		$current_settings = agency_commerce_core_get_settings( true );
		update_option(
			self::SNAPSHOT_OPTION,
			array(
				'preset'   => self::active(),
				'settings' => $current_settings,
			),
			false
		);

		$settings = is_array( $preset['settings'] ?? null ) ? $preset['settings'] : array();
		$merged   = array_replace_recursive( agency_commerce_core_defaults(), $current_settings, $settings );
		$merged   = Sanitizer::sanitize_stored( $merged );

		update_option( 'agency_commerce_core_settings', $merged, false );
		update_option( self::ACTIVE_PRESET_OPTION, $slug, false );
		agency_commerce_core_flush_settings_cache();

		/**
		 * Fires after a preset is applied.
		 *
		 * @param string               $slug Preset slug.
		 * @param array<string, mixed>  $preset Preset definition.
		 * @param array<string, mixed>  $settings Sanitized settings after apply.
		 */
		do_action( 'agency_commerce_core_preset_applied', $slug, $preset, $merged );

		return true;
	}

	/**
	 * Restores the previous preset snapshot.
	 */
	public static function restore_previous(): bool {
		$snapshot = self::snapshot();

		if ( ! is_array( $snapshot ) || empty( $snapshot['settings'] ) || ! is_array( $snapshot['settings'] ) ) {
			return false;
		}

		$current_settings = agency_commerce_core_get_settings( true );
		$current_preset   = self::active();
		$restore_preset   = isset( $snapshot['preset'] ) ? sanitize_key( (string) $snapshot['preset'] ) : '';
		$restore_settings = Sanitizer::sanitize_stored( (array) $snapshot['settings'] );

		update_option(
			self::SNAPSHOT_OPTION,
			array(
				'preset'   => $current_preset,
				'settings' => $current_settings,
			),
			false
		);

		update_option( 'agency_commerce_core_settings', $restore_settings, false );
		update_option( self::ACTIVE_PRESET_OPTION, $restore_preset, false );
		agency_commerce_core_flush_settings_cache();

		/**
		 * Fires after a preset snapshot is restored.
		 *
		 * @param string              $preset Restored preset slug.
		 * @param array<string, mixed> $settings Restored settings.
		 */
		do_action( 'agency_commerce_core_preset_restored', $restore_preset, $restore_settings );

		return true;
	}

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {}
}

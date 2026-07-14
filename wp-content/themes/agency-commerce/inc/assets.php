<?php
/**
 * Asset registration.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'agency_commerce_enqueue_assets' );

/**
 * Enqueues the foundational parent stylesheet.
 */
function agency_commerce_enqueue_assets(): void {
	$frontend_asset = agency_commerce_build_asset( 'frontend' );
	$catalog_asset  = agency_commerce_build_asset( 'catalog' );
	$style_src      = ! empty( $frontend_asset['style_url'] ) ? (string) $frontend_asset['style_url'] : AGENCY_COMMERCE_THEME_URI . 'style.css';
	$style_version  = ! empty( $frontend_asset['version'] ) ? (string) $frontend_asset['version'] : AGENCY_COMMERCE_VERSION;

	wp_enqueue_style( 'agency-commerce', $style_src, array(), $style_version );

	wp_add_inline_style( 'agency-commerce', agency_commerce_design_token_css() );

	if ( ! empty( $frontend_asset['script_url'] ) ) {
		wp_enqueue_script(
			'agency-commerce-navigation',
			(string) $frontend_asset['script_url'],
			(array) $frontend_asset['dependencies'],
			(string) $frontend_asset['version'],
			true
		);
	} else {
		wp_enqueue_script(
			'agency-commerce-navigation',
			AGENCY_COMMERCE_THEME_URI . 'assets/js/navigation.js',
			array(),
			AGENCY_COMMERCE_VERSION,
			true
		);
	}

	if ( function_exists( 'agency_commerce_should_load_catalog_assets' ) && agency_commerce_should_load_catalog_assets() ) {
		if ( ! empty( $catalog_asset['script_url'] ) ) {
			wp_enqueue_script(
				'agency-commerce-catalog',
				(string) $catalog_asset['script_url'],
				(array) $catalog_asset['dependencies'],
				(string) $catalog_asset['version'],
				true
			);
		} else {
			wp_enqueue_script(
				'agency-commerce-catalog',
				AGENCY_COMMERCE_THEME_URI . 'assets/js/catalog.js',
				array(),
				AGENCY_COMMERCE_VERSION,
				true
			);
		}
	}
}

/**
 * Returns generated runtime asset metadata.
 *
 * @param string $entry Build entry name.
 * @return array<string, mixed>
 */
function agency_commerce_build_asset( string $entry ): array {
	$entry      = sanitize_key( $entry );
	$build_dir  = AGENCY_COMMERCE_THEME_DIR . 'build/';
	$build_url  = AGENCY_COMMERCE_THEME_URI . 'build/';
	$asset_file = $build_dir . $entry . '.asset.php';
	$script     = $build_dir . $entry . '.js';
	$style      = $build_dir . 'style-' . $entry . '.css';
	$metadata   = array(
		'dependencies' => array(),
		'version'      => AGENCY_COMMERCE_VERSION,
	);

	if ( file_exists( $asset_file ) ) {
		$loaded = require $asset_file;

		if ( is_array( $loaded ) ) {
			$metadata = array_replace( $metadata, $loaded );
		}
	}

	return array(
		'dependencies' => is_array( $metadata['dependencies'] ?? null ) ? $metadata['dependencies'] : array(),
		'version'      => (string) ( $metadata['version'] ?? AGENCY_COMMERCE_VERSION ),
		'script_url'   => file_exists( $script ) ? $build_url . $entry . '.js' : '',
		'style_url'    => file_exists( $style ) ? $build_url . 'style-' . $entry . '.css' : '',
	);
}

/**
 * Returns CSS custom-property overrides from framework settings.
 */
function agency_commerce_design_token_css(): string {
	$density = sanitize_key( (string) agency_commerce_get_setting( 'design.spacing_density', 'comfortable' ) );
	$shadow  = sanitize_key( (string) agency_commerce_get_setting( 'design.shadow_level', 'balanced' ) );

	$spacing = array(
		'compact'     => array(
			'--ac-spacing-1' => '0.2rem',
			'--ac-spacing-2' => '0.4rem',
			'--ac-spacing-3' => '0.6rem',
			'--ac-spacing-4' => '0.8rem',
			'--ac-spacing-6' => '1.2rem',
			'--ac-spacing-8' => '1.6rem',
		),
		'comfortable' => array(
			'--ac-spacing-1' => '0.25rem',
			'--ac-spacing-2' => '0.5rem',
			'--ac-spacing-3' => '0.75rem',
			'--ac-spacing-4' => '1rem',
			'--ac-spacing-6' => '1.5rem',
			'--ac-spacing-8' => '2rem',
		),
		'airy'        => array(
			'--ac-spacing-1' => '0.3rem',
			'--ac-spacing-2' => '0.6rem',
			'--ac-spacing-3' => '0.9rem',
			'--ac-spacing-4' => '1.2rem',
			'--ac-spacing-6' => '1.8rem',
			'--ac-spacing-8' => '2.4rem',
		),
	);

	$shadows = array(
		'soft'     => array(
			'--ac-shadow-sm' => '0 1px 2px rgb(17 24 39 / 0.04)',
			'--ac-shadow-md' => '0 8px 18px rgb(17 24 39 / 0.08)',
		),
		'balanced' => array(
			'--ac-shadow-sm' => '0 1px 2px rgb(17 24 39 / 0.08)',
			'--ac-shadow-md' => '0 8px 24px rgb(17 24 39 / 0.10)',
		),
		'elevated' => array(
			'--ac-shadow-sm' => '0 2px 6px rgb(17 24 39 / 0.10)',
			'--ac-shadow-md' => '0 14px 40px rgb(17 24 39 / 0.16)',
		),
	);

	$tokens = array(
		'--ac-color-primary'          => agency_commerce_get_setting( 'design.primary_color', '#115e59' ),
		'--ac-color-secondary'        => agency_commerce_get_setting( 'design.secondary_color', '#1f2937' ),
		'--ac-color-accent'           => agency_commerce_get_setting( 'design.accent_color', '#f59e0b' ),
		'--ac-color-text'             => agency_commerce_get_setting( 'design.text_color', '#111827' ),
		'--ac-color-muted'            => agency_commerce_get_setting( 'design.muted_color', '#6b7280' ),
		'--ac-color-background'       => agency_commerce_get_setting( 'design.background_color', '#ffffff' ),
		'--ac-color-surface'          => agency_commerce_get_setting( 'design.surface_color', '#f9fafb' ),
		'--ac-color-border'           => agency_commerce_get_setting( 'design.border_color', '#d1d5db' ),
		'--ac-color-success'          => agency_commerce_get_setting( 'design.success_color', '#047857' ),
		'--ac-color-error'            => agency_commerce_get_setting( 'design.error_color', '#b91c1c' ),
		'--ac-container-width'        => agency_commerce_get_setting( 'design.container_width', '1200px' ),
		'--ac-container-narrow-width' => agency_commerce_get_setting( 'design.narrow_container_width', '760px' ),
		'--ac-radius-base'            => agency_commerce_get_setting( 'design.button_radius', '4px' ),
		'--ac-radius-button'          => agency_commerce_get_setting( 'design.button_radius', '4px' ),
		'--ac-radius-card'            => agency_commerce_get_setting( 'design.card_radius', '8px' ),
		'--ac-font-heading'           => agency_commerce_get_setting( 'design.heading_font', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' ),
		'--ac-font-body'              => agency_commerce_get_setting( 'design.body_font', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' ),
		'--ac-product-image-ratio'    => agency_commerce_get_setting( 'design.product_image_ratio', '1 / 1' ),
	);
	$css    = ':root{';

	foreach ( $tokens as $property => $value ) {
		$value = (string) $value;

		if ( str_contains( $property, 'color' ) && ! sanitize_hex_color( $value ) ) {
			continue;
		}

		$css .= $property . ':' . agency_commerce_sanitize_css_token_value( $value ) . ';';
	}

	$spacing_values = $spacing[ array_key_exists( $density, $spacing ) ? $density : 'comfortable' ];
	$shadow_values  = $shadows[ array_key_exists( $shadow, $shadows ) ? $shadow : 'balanced' ];

	foreach ( $spacing_values as $property => $value ) {
		$css .= $property . ':' . agency_commerce_sanitize_css_token_value( $value ) . ';';
	}

	foreach ( $shadow_values as $property => $value ) {
		$css .= $property . ':' . agency_commerce_sanitize_css_token_value( $value ) . ';';
	}

	$css .= '}';

	return $css;
}

/**
 * Sanitizes a CSS token value for inline custom properties.
 *
 * @param string $value Token value.
 */
function agency_commerce_sanitize_css_token_value( string $value ): string {
	return trim( (string) preg_replace( '/[^#,%().\/\-"\' a-zA-Z0-9_-]/', '', $value ) );
}

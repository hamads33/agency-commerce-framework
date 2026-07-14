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
	wp_enqueue_style(
		'agency-commerce',
		AGENCY_COMMERCE_THEME_URI . 'style.css',
		array(),
		AGENCY_COMMERCE_VERSION
	);

	wp_add_inline_style( 'agency-commerce', agency_commerce_design_token_css() );

	wp_enqueue_script(
		'agency-commerce-navigation',
		AGENCY_COMMERCE_THEME_URI . 'assets/js/navigation.js',
		array(),
		AGENCY_COMMERCE_VERSION,
		true
	);

	if ( function_exists( 'agency_commerce_should_load_catalog_assets' ) && agency_commerce_should_load_catalog_assets() ) {
		wp_enqueue_script(
			'agency-commerce-catalog',
			AGENCY_COMMERCE_THEME_URI . 'assets/js/catalog.js',
			array(),
			AGENCY_COMMERCE_VERSION,
			true
		);
	}
}

/**
 * Returns CSS custom-property overrides from framework settings.
 */
function agency_commerce_design_token_css(): string {
	$tokens = array(
		'--ac-color-primary'    => agency_commerce_get_setting( 'design.primary_color', '#115e59' ),
		'--ac-color-secondary'  => agency_commerce_get_setting( 'design.secondary_color', '#1f2937' ),
		'--ac-color-accent'     => agency_commerce_get_setting( 'design.accent_color', '#f59e0b' ),
		'--ac-color-text'       => agency_commerce_get_setting( 'design.text_color', '#111827' ),
		'--ac-color-muted'      => agency_commerce_get_setting( 'design.muted_color', '#6b7280' ),
		'--ac-color-background' => agency_commerce_get_setting( 'design.background_color', '#ffffff' ),
		'--ac-color-surface'    => agency_commerce_get_setting( 'design.surface_color', '#f9fafb' ),
		'--ac-color-border'     => agency_commerce_get_setting( 'design.border_color', '#d1d5db' ),
		'--ac-color-success'    => agency_commerce_get_setting( 'design.success_color', '#047857' ),
		'--ac-color-error'      => agency_commerce_get_setting( 'design.error_color', '#b91c1c' ),
		'--ac-font-heading'     => agency_commerce_get_setting( 'design.heading_font', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' ),
		'--ac-font-body'        => agency_commerce_get_setting( 'design.body_font', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' ),
	);
	$css    = ':root{';

	foreach ( $tokens as $property => $value ) {
		$value = (string) $value;

		if ( str_contains( $property, 'color' ) && ! sanitize_hex_color( $value ) ) {
			continue;
		}

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
	return trim( (string) preg_replace( '/[^#,%().\-"\' a-zA-Z0-9_-]/', '', $value ) );
}

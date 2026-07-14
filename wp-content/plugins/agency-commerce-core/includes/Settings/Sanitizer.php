<?php
/**
 * Settings sanitization.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Settings;

/**
 * Sanitizes the single framework settings option.
 */
final class Sanitizer {
	/**
	 * Sanitizes submitted settings.
	 *
	 * The admin UI saves one tab at a time, so this method merges the sanitized
	 * submitted tab into the existing settings blob instead of replacing it.
	 *
	 * @param mixed $input Raw submitted settings.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ): array {
		$current = agency_commerce_core_get_settings( true );

		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error(
				Registry::OPTION_NAME,
				'agency_commerce_core_capability',
				__( 'You are not allowed to update Commerce Framework settings.', 'agency-commerce-core' ),
				'error'
			);

			return $current;
		}

		if ( ! self::verify_admin_nonce() ) {
			add_settings_error(
				Registry::OPTION_NAME,
				'agency_commerce_core_nonce',
				__( 'Commerce Framework settings could not be verified. Please try again.', 'agency-commerce-core' ),
				'error'
			);

			return $current;
		}

		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$tab      = self::submitted_tab();
		$fields   = Registry::fields_for_tab( $tab );
		$settings = $current;

		foreach ( $fields as $path => $field ) {
			$raw       = self::get_nested_value( $input, $path );
			$sanitized = self::sanitize_field( $path, $field, $raw, agency_commerce_core_get_setting( $path ) );

			self::set_nested_value( $settings, $path, $sanitized );
		}

		agency_commerce_core_flush_settings_cache();

		return $settings;
	}

	/**
	 * Returns a fully sanitized settings array for stored values.
	 *
	 * @param array<string, mixed> $settings Settings.
	 * @return array<string, mixed>
	 */
	public static function sanitize_stored( array $settings ): array {
		$defaults  = agency_commerce_core_defaults();
		$sanitized = array_replace_recursive( $defaults, $settings );

		foreach ( Registry::fields() as $path => $field ) {
			$value = self::get_nested_value( $sanitized, $path );
			self::set_nested_value(
				$sanitized,
				$path,
				self::sanitize_field( $path, $field, $value, self::get_nested_value( $defaults, $path ), false )
			);
		}

		return $sanitized;
	}

	/**
	 * Sanitizes one setting value.
	 *
	 * @param string               $path          Setting path.
	 * @param array<string, mixed> $field         Field definition.
	 * @param mixed                $raw           Raw value.
	 * @param mixed                $fallback      Fallback value.
	 * @param bool                 $report_errors Whether to add settings errors.
	 * @return mixed
	 */
	private static function sanitize_field( string $path, array $field, $raw, $fallback, bool $report_errors = true ) {
		$type = (string) ( $field['type'] ?? 'text' );

		if ( 'checkbox' === $type ) {
			return ! empty( $raw );
		}

		if ( null === $raw ) {
			return $fallback;
		}

		switch ( $type ) {
			case 'email':
				$value = sanitize_email( (string) $raw );
				if ( '' !== (string) $raw && ! is_email( $value ) ) {
					self::reject_invalid_value( $path, $report_errors );
					return $fallback;
				}
				return $value;

			case 'url':
				return esc_url_raw( (string) $raw );

			case 'textarea':
				return sanitize_textarea_field( (string) $raw );

			case 'number':
				return self::sanitize_number( $path, $field, $raw, $fallback, $report_errors );

			case 'select':
				$options = is_array( $field['options'] ?? null ) ? array_keys( $field['options'] ) : array();
				$value   = sanitize_key( (string) $raw );

				if ( ! in_array( $value, $options, true ) ) {
					self::reject_invalid_value( $path, $report_errors );
					return $fallback;
				}

				return $value;

			case 'color':
				$value = sanitize_hex_color( (string) $raw );

				if ( null === $value ) {
					self::reject_invalid_value( $path, $report_errors );
					return $fallback;
				}

				return $value;

			case 'font':
				return substr( sanitize_text_field( (string) $raw ), 0, 160 );

			case 'text':
			default:
				return sanitize_text_field( (string) $raw );
		}
	}

	/**
	 * Sanitizes a bounded integer.
	 *
	 * @param string               $path          Setting path.
	 * @param array<string, mixed> $field         Field definition.
	 * @param mixed                $raw           Raw value.
	 * @param mixed                $fallback      Fallback value.
	 * @param bool                 $report_errors Whether to add settings errors.
	 */
	private static function sanitize_number( string $path, array $field, $raw, $fallback, bool $report_errors ): int {
		$value = filter_var( $raw, FILTER_VALIDATE_INT );

		if ( false === $value ) {
			self::reject_invalid_value( $path, $report_errors );
			return (int) $fallback;
		}

		$min = isset( $field['min'] ) ? (int) $field['min'] : PHP_INT_MIN;
		$max = isset( $field['max'] ) ? (int) $field['max'] : PHP_INT_MAX;

		if ( $value < $min || $value > $max ) {
			self::reject_invalid_value( $path, $report_errors );
			return (int) $fallback;
		}

		return (int) $value;
	}

	/**
	 * Adds a settings error for a rejected value.
	 *
	 * @param string $path          Setting path.
	 * @param bool   $report_errors Whether to add settings errors.
	 */
	private static function reject_invalid_value( string $path, bool $report_errors ): void {
		if ( ! $report_errors ) {
			return;
		}

		add_settings_error(
			Registry::OPTION_NAME,
			'agency_commerce_core_invalid_' . sanitize_key( str_replace( '.', '_', $path ) ),
			sprintf(
				/* translators: %s: Setting key. */
				__( 'Invalid value rejected for %s.', 'agency-commerce-core' ),
				esc_html( $path )
			),
			'error'
		);
	}

	/**
	 * Verifies the Settings API nonce when handling an admin post.
	 */
	private static function verify_admin_nonce(): bool {
		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';

		if ( 'POST' !== strtoupper( $request_method ) ) {
			return true;
		}

		$option_page = isset( $_POST['option_page'] ) ? sanitize_key( wp_unslash( $_POST['option_page'] ) ) : '';

		if ( Registry::OPTION_GROUP !== $option_page ) {
			return true;
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		return (bool) wp_verify_nonce( $nonce, Registry::OPTION_GROUP . '-options' );
	}

	/**
	 * Returns the submitted tab key.
	 */
	private static function submitted_tab(): string {
		$tabs = Registry::tabs();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Called after verify_admin_nonce() in sanitize().
		$tab = isset( $_POST['_agency_commerce_core_tab'] ) ? sanitize_key( wp_unslash( $_POST['_agency_commerce_core_tab'] ) ) : 'store_identity';

		return array_key_exists( $tab, $tabs ) ? $tab : 'store_identity';
	}

	/**
	 * Reads a nested array value by dot path.
	 *
	 * @param array<string, mixed> $source Source array.
	 * @param string               $path Dot path.
	 * @return mixed|null
	 */
	private static function get_nested_value( array $source, string $path ) {
		$value = $source;

		foreach ( explode( '.', $path ) as $part ) {
			if ( ! is_array( $value ) || ! array_key_exists( $part, $value ) ) {
				return null;
			}

			$value = $value[ $part ];
		}

		return $value;
	}

	/**
	 * Writes a nested array value by dot path.
	 *
	 * @param array<string, mixed> $target Target array.
	 * @param string               $path Dot path.
	 * @param mixed                $value Value.
	 */
	private static function set_nested_value( array &$target, string $path, $value ): void {
		$parts  = explode( '.', $path );
		$cursor = &$target;

		foreach ( $parts as $index => $part ) {
			if ( count( $parts ) - 1 === $index ) {
				$cursor[ $part ] = $value;
				return;
			}

			if ( ! isset( $cursor[ $part ] ) || ! is_array( $cursor[ $part ] ) ) {
				$cursor[ $part ] = array();
			}

			$cursor = &$cursor[ $part ];
		}
	}

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {}
}

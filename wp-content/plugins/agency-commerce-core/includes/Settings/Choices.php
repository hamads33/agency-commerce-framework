<?php
/**
 * Settings choices and repeated field definitions.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Settings;

/**
 * Provides reusable settings choice lists.
 */
final class Choices {
	/**
	 * Returns header variants.
	 *
	 * @return array<string, string>
	 */
	public static function header_variants(): array {
		return array(
			'header-default'  => __( 'Default', 'agency-commerce-core' ),
			'header-centered' => __( 'Centered', 'agency-commerce-core' ),
		);
	}

	/**
	 * Returns footer variants.
	 *
	 * @return array<string, string>
	 */
	public static function footer_variants(): array {
		return array(
			'footer-columns' => __( 'Columns', 'agency-commerce-core' ),
			'footer-minimal' => __( 'Minimal', 'agency-commerce-core' ),
		);
	}

	/**
	 * Returns product-card variants.
	 *
	 * @return array<string, string>
	 */
	public static function product_card_variants(): array {
		return array(
			'product-card-classic' => __( 'Classic', 'agency-commerce-core' ),
			'product-card-minimal' => __( 'Minimal', 'agency-commerce-core' ),
			'product-card-compact' => __( 'Compact', 'agency-commerce-core' ),
		);
	}

	/**
	 * Returns product-layout variants.
	 *
	 * @return array<string, string>
	 */
	public static function product_layout_variants(): array {
		return array(
			'product-layout-standard' => __( 'Standard', 'agency-commerce-core' ),
			'product-layout-sticky'   => __( 'Sticky', 'agency-commerce-core' ),
		);
	}

	/**
	 * Returns checkout modes.
	 *
	 * @return array<string, string>
	 */
	public static function checkout_modes(): array {
		return array(
			'block'   => __( 'Block checkout (default)', 'agency-commerce-core' ),
			'classic' => __( 'Classic shortcode checkout', 'agency-commerce-core' ),
		);
	}

	/**
	 * Returns social link fields.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function social_fields(): array {
		$networks = array(
			'facebook'  => __( 'Facebook', 'agency-commerce-core' ),
			'instagram' => __( 'Instagram', 'agency-commerce-core' ),
			'tiktok'    => __( 'TikTok', 'agency-commerce-core' ),
			'youtube'   => __( 'YouTube', 'agency-commerce-core' ),
			'linkedin'  => __( 'LinkedIn', 'agency-commerce-core' ),
			'x'         => __( 'X', 'agency-commerce-core' ),
			'pinterest' => __( 'Pinterest', 'agency-commerce-core' ),
		);
		$fields   = array();

		foreach ( $networks as $network => $label ) {
			$fields[ 'social.' . $network ] = array(
				'label' => $label,
				'type'  => 'url',
			);
		}

		return $fields;
	}

	/**
	 * Returns color token fields.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function color_fields(): array {
		$colors = array(
			'primary_color'    => __( 'Primary', 'agency-commerce-core' ),
			'secondary_color'  => __( 'Secondary', 'agency-commerce-core' ),
			'accent_color'     => __( 'Accent', 'agency-commerce-core' ),
			'text_color'       => __( 'Text', 'agency-commerce-core' ),
			'muted_color'      => __( 'Muted text', 'agency-commerce-core' ),
			'background_color' => __( 'Background', 'agency-commerce-core' ),
			'surface_color'    => __( 'Surface', 'agency-commerce-core' ),
			'border_color'     => __( 'Border', 'agency-commerce-core' ),
			'success_color'    => __( 'Success', 'agency-commerce-core' ),
			'error_color'      => __( 'Error', 'agency-commerce-core' ),
		);
		$fields = array();

		foreach ( $colors as $token => $label ) {
			$fields[ 'design.' . $token ] = array(
				'label' => $label,
				'type'  => 'color',
			);
		}

		return $fields;
	}

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {}
}

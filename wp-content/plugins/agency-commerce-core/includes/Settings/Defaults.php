<?php
/**
 * Default settings contract.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Settings;

/**
 * Provides settings defaults without requiring a saved options row.
 */
final class Defaults {
	/**
	 * Returns default framework settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function all(): array {
		$defaults = array(
			'store_identity' => array(
				'logo_id'        => 0,
				'mobile_logo_id' => 0,
				'contact_email'  => '',
				'contact_phone'  => '',
				'whatsapp'       => '',
				'address'        => '',
				'support_hours'  => '',
			),
			'design'         => array(
				'preset'              => 'minimal',
				'primary_color'       => '#115e59',
				'secondary_color'     => '#1f2937',
				'accent_color'        => '#f59e0b',
				'body_font'           => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
				'heading_font'        => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
				'button_radius'       => '4px',
				'card_radius'         => '8px',
				'product_image_ratio' => '1 / 1',
			),
			'header'         => array(
				'variant'          => 'header-default',
				'sticky'           => false,
				'show_search'      => true,
				'show_account'     => true,
				'show_cart'        => true,
				'show_cart_count'  => true,
				'announcement_bar' => false,
				'announcement'     => '',
			),
			'shop'           => array(
				'card_variant'       => 'product-card-classic',
				'products_per_row'   => 4,
				'products_per_page'  => 12,
				'filter_layout'      => 'sidebar',
				'show_rating'        => true,
				'show_quick_add'     => true,
				'enable_hover_image' => true,
				'show_sale_badge'    => true,
				'show_stock_badge'   => true,
				'show_product_cat'   => true,
			),
			'product_page'   => array(
				'layout'          => 'product-layout-standard',
				'show_delivery'   => true,
				'delivery_text'   => '',
				'show_whatsapp'   => false,
				'show_size_guide' => false,
				'show_sku'        => true,
				'show_categories' => true,
				'show_tags'       => true,
				'related_count'   => 4,
				'recently_viewed' => true,
			),
			'cart'           => array(
				'mini_cart_enabled' => true,
			),
			'checkout'       => array(
				'mode'         => 'block',
				'support_text' => '',
			),
			'footer'         => array(
				'variant'    => 'footer-columns',
				'trust_text' => '',
			),
			'social'         => array(),
			'performance'    => array(
				'load_assets_conditionally' => true,
			),
			'developer'      => array(
				'debug_labels' => false,
			),
		);

		/**
		 * Filters the plugin settings defaults.
		 *
		 * Keep keys backward-compatible once published.
		 *
		 * @since 0.1.0
		 *
		 * @param array<string, mixed> $defaults Default settings.
		 */
		return apply_filters( 'agency_commerce_core_defaults', $defaults );
	}

	/**
	 * Merges external defaults into the framework defaults.
	 *
	 * @param array<string, mixed> $defaults Existing defaults.
	 * @return array<string, mixed>
	 */
	public static function filter_defaults( array $defaults ): array {
		return array_replace_recursive( self::all(), $defaults );
	}
}

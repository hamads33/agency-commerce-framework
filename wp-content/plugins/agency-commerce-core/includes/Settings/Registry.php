<?php
/**
 * Settings schema used by the admin UI and sanitizer.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Settings;

/**
 * Provides CP4A settings tabs, sections, and fields.
 */
final class Registry {
	/**
	 * Settings option name.
	 */
	public const OPTION_NAME = 'agency_commerce_core_settings';

	/**
	 * Settings API group.
	 */
	public const OPTION_GROUP = 'agency_commerce_core_settings';

	/**
	 * Returns tab labels.
	 *
	 * @return array<string, string>
	 */
	public static function tabs(): array {
		return array(
			'store_identity' => __( 'Store Identity', 'agency-commerce-core' ),
			'presets'        => __( 'Presets', 'agency-commerce-core' ),
			'header'         => __( 'Header', 'agency-commerce-core' ),
			'shop'           => __( 'Shop', 'agency-commerce-core' ),
			'product_page'   => __( 'Product Page', 'agency-commerce-core' ),
			'whatsapp'       => __( 'WhatsApp & Enquiries', 'agency-commerce-core' ),
			'footer'         => __( 'Footer', 'agency-commerce-core' ),
			'social'         => __( 'Social Links', 'agency-commerce-core' ),
			'design'         => __( 'Design Tokens', 'agency-commerce-core' ),
			'performance'    => __( 'Performance', 'agency-commerce-core' ),
			'developer'      => __( 'Developer', 'agency-commerce-core' ),
		);
	}

	/**
	 * Returns settings sections and fields.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function sections(): array {
		return array(
			'store_identity' => array(
				'identity' => array(
					'title'  => __( 'Store Identity', 'agency-commerce-core' ),
					'fields' => array(
						'store_identity.logo_id'        => array(
							'label'       => __( 'Logo attachment ID', 'agency-commerce-core' ),
							'type'        => 'number',
							'min'         => 0,
							'max'         => 999999999,
							'description' => __( 'Optional framework logo reference. WordPress custom logo remains the storefront default.', 'agency-commerce-core' ),
						),
						'store_identity.mobile_logo_id' => array(
							'label'       => __( 'Mobile logo attachment ID', 'agency-commerce-core' ),
							'type'        => 'number',
							'min'         => 0,
							'max'         => 999999999,
							'description' => __( 'Optional mobile-specific logo reference for future component use.', 'agency-commerce-core' ),
						),
						'store_identity.contact_email'  => array(
							'label' => __( 'Contact email', 'agency-commerce-core' ),
							'type'  => 'email',
						),
						'store_identity.contact_phone'  => array(
							'label' => __( 'Contact phone', 'agency-commerce-core' ),
							'type'  => 'text',
						),
						'store_identity.address'        => array(
							'label' => __( 'Store address', 'agency-commerce-core' ),
							'type'  => 'textarea',
						),
						'store_identity.support_hours'  => array(
							'label' => __( 'Support hours', 'agency-commerce-core' ),
							'type'  => 'text',
						),
					),
				),
			),
			'header'         => array(
				'header' => array(
					'title'  => __( 'Header', 'agency-commerce-core' ),
					'fields' => array(
						'header.variant'           => array(
							'label'         => __( 'Header variant', 'agency-commerce-core' ),
							'type'          => 'select',
							'options'       => self::header_variants(),
							'preview_group' => 'header',
						),
						'header.sticky'            => array(
							'label' => __( 'Sticky header', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'header.show_search'       => array(
							'label' => __( 'Show search', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'header.show_account'      => array(
							'label' => __( 'Show account link', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'header.show_cart'         => array(
							'label' => __( 'Show cart link', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'header.show_cart_count'   => array(
							'label' => __( 'Show cart count', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'header.announcement_bar'  => array(
							'label' => __( 'Show announcement bar', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'header.announcement'      => array(
							'label' => __( 'Announcement text', 'agency-commerce-core' ),
							'type'  => 'text',
						),
						'header.announcement_link' => array(
							'label' => __( 'Announcement link', 'agency-commerce-core' ),
							'type'  => 'url',
						),
					),
				),
			),
			'shop'           => array(
				'shop' => array(
					'title'  => __( 'Shop', 'agency-commerce-core' ),
					'fields' => array(
						'shop.card_variant'        => array(
							'label'         => __( 'Product-card variant', 'agency-commerce-core' ),
							'type'          => 'select',
							'options'       => self::product_card_variants(),
							'preview_group' => 'product-card',
						),
						'shop.products_per_row'    => array(
							'label' => __( 'Products per row', 'agency-commerce-core' ),
							'type'  => 'number',
							'min'   => 1,
							'max'   => 6,
						),
						'shop.products_per_page'   => array(
							'label' => __( 'Products per page', 'agency-commerce-core' ),
							'type'  => 'number',
							'min'   => 1,
							'max'   => 48,
						),
						'shop.filter_layout'       => array(
							'label'   => __( 'Filter layout', 'agency-commerce-core' ),
							'type'    => 'select',
							'options' => array(
								'sidebar' => __( 'Sidebar and mobile drawer', 'agency-commerce-core' ),
								'drawer'  => __( 'Mobile drawer only', 'agency-commerce-core' ),
							),
						),
						'shop.show_product_cat'    => array(
							'label' => __( 'Show product category', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'shop.show_rating'         => array(
							'label' => __( 'Show rating', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'shop.show_quick_add'      => array(
							'label' => __( 'Show quick add', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'shop.enable_hover_image'  => array(
							'label' => __( 'Enable hover image', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'shop.show_sale_badge'     => array(
							'label' => __( 'Show sale badge', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'shop.show_stock_badge'    => array(
							'label' => __( 'Show out-of-stock badge', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'shop.show_featured_badge' => array(
							'label' => __( 'Show featured badge', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
					),
				),
			),
			'product_page'   => array(
				'product_page' => array(
					'title'  => __( 'Product Page', 'agency-commerce-core' ),
					'fields' => array(
						'product_page.layout'           => array(
							'label'         => __( 'Product layout', 'agency-commerce-core' ),
							'type'          => 'select',
							'options'       => self::product_layout_variants(),
							'preview_group' => 'product-layout',
						),
						'product_page.sticky_summary'   => array(
							'label' => __( 'Sticky summary', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'product_page.show_delivery_message' => array(
							'label' => __( 'Show delivery message', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'product_page.delivery_message' => array(
							'label'       => __( 'Global delivery message', 'agency-commerce-core' ),
							'type'        => 'textarea',
							'description' => __( 'Displayed on product pages unless a product or category override is set.', 'agency-commerce-core' ),
						),
						'product_page.show_whatsapp_enquiry' => array(
							'label' => __( 'Show WhatsApp enquiry button', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'product_page.show_sku'         => array(
							'label' => __( 'Show SKU', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'product_page.show_categories'  => array(
							'label' => __( 'Show categories', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'product_page.show_tags'        => array(
							'label' => __( 'Show tags', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
						'product_page.related_count'    => array(
							'label' => __( 'Related products count', 'agency-commerce-core' ),
							'type'  => 'number',
							'min'   => 0,
							'max'   => 12,
						),
					),
				),
			),
			'footer'         => array(
				'footer' => array(
					'title'  => __( 'Footer', 'agency-commerce-core' ),
					'fields' => array(
						'footer.variant'    => array(
							'label'         => __( 'Footer variant', 'agency-commerce-core' ),
							'type'          => 'select',
							'options'       => self::footer_variants(),
							'preview_group' => 'footer',
						),
						'footer.trust_text' => array(
							'label' => __( 'Trust text', 'agency-commerce-core' ),
							'type'  => 'text',
						),
					),
				),
			),
			'whatsapp'       => array(
				'whatsapp' => array(
					'title'  => __( 'WhatsApp Enquiry', 'agency-commerce-core' ),
					'fields' => array(
						'whatsapp.phone_number'     => array(
							'label'       => __( 'Phone number', 'agency-commerce-core' ),
							'type'        => 'text',
							'description' => __( 'Use international format with country code. The button is hidden until a phone number is configured.', 'agency-commerce-core' ),
						),
						'whatsapp.button_text'      => array(
							'label' => __( 'Button text', 'agency-commerce-core' ),
							'type'  => 'text',
						),
						'whatsapp.message_template' => array(
							'label'       => __( 'Message template', 'agency-commerce-core' ),
							'type'        => 'textarea',
							'description' => __( 'Available placeholders: {product_name}, {product_url}, {sku}, {variation}.', 'agency-commerce-core' ),
						),
					),
				),
			),
			'social'         => array(
				'social' => array(
					'title'  => __( 'Social Links', 'agency-commerce-core' ),
					'fields' => self::social_fields(),
				),
			),
			'design'         => array(
				'colors' => array(
					'title'  => __( 'Color Tokens', 'agency-commerce-core' ),
					'fields' => self::color_fields(),
				),
				'layout' => array(
					'title'  => __( 'Layout Tokens', 'agency-commerce-core' ),
					'fields' => array(
						'design.container_width'        => array(
							'label'       => __( 'Container width', 'agency-commerce-core' ),
							'type'        => 'text',
							'description' => __( 'CSS length value, for example 1200px.', 'agency-commerce-core' ),
						),
						'design.narrow_container_width' => array(
							'label'       => __( 'Narrow container width', 'agency-commerce-core' ),
							'type'        => 'text',
							'description' => __( 'CSS length value, for example 760px.', 'agency-commerce-core' ),
						),
						'design.spacing_density'        => array(
							'label'   => __( 'Spacing density', 'agency-commerce-core' ),
							'type'    => 'select',
							'options' => array(
								'compact'     => __( 'Compact', 'agency-commerce-core' ),
								'comfortable' => __( 'Comfortable', 'agency-commerce-core' ),
								'airy'        => __( 'Airy', 'agency-commerce-core' ),
							),
						),
						'design.shadow_level'           => array(
							'label'   => __( 'Shadow level', 'agency-commerce-core' ),
							'type'    => 'select',
							'options' => array(
								'soft'     => __( 'Soft', 'agency-commerce-core' ),
								'balanced' => __( 'Balanced', 'agency-commerce-core' ),
								'elevated' => __( 'Elevated', 'agency-commerce-core' ),
							),
						),
						'design.button_radius'          => array(
							'label'       => __( 'Button radius', 'agency-commerce-core' ),
							'type'        => 'text',
							'description' => __( 'CSS length value, for example 4px.', 'agency-commerce-core' ),
						),
						'design.card_radius'            => array(
							'label'       => __( 'Card radius', 'agency-commerce-core' ),
							'type'        => 'text',
							'description' => __( 'CSS length value, for example 8px.', 'agency-commerce-core' ),
						),
						'design.product_image_ratio'    => array(
							'label'       => __( 'Product image ratio', 'agency-commerce-core' ),
							'type'        => 'text',
							'description' => __( 'CSS aspect-ratio value, for example 1 / 1 or 4 / 5.', 'agency-commerce-core' ),
						),
					),
				),
				'fonts'  => array(
					'title'  => __( 'Font Tokens', 'agency-commerce-core' ),
					'fields' => array(
						'design.heading_font' => array(
							'label' => __( 'Heading font stack', 'agency-commerce-core' ),
							'type'  => 'font',
						),
						'design.body_font'    => array(
							'label' => __( 'Body font stack', 'agency-commerce-core' ),
							'type'  => 'font',
						),
					),
				),
			),
			'performance'    => array(
				'performance' => array(
					'title'  => __( 'Performance', 'agency-commerce-core' ),
					'fields' => array(
						'performance.load_assets_conditionally' => array(
							'label' => __( 'Load catalog assets conditionally', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
					),
				),
			),
			'developer'      => array(
				'developer' => array(
					'title'  => __( 'Developer', 'agency-commerce-core' ),
					'fields' => array(
						'developer.debug_labels' => array(
							'label' => __( 'Show debug labels', 'agency-commerce-core' ),
							'type'  => 'checkbox',
						),
					),
				),
			),
		);
	}

	/**
	 * Returns all field definitions keyed by dot path.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function fields(): array {
		$fields = array();

		foreach ( self::sections() as $tab_sections ) {
			foreach ( $tab_sections as $section ) {
				foreach ( $section['fields'] as $key => $field ) {
					$fields[ $key ] = $field;
				}
			}
		}

		return $fields;
	}

	/**
	 * Returns field definitions for a tab.
	 *
	 * @param string $tab Tab key.
	 * @return array<string, array<string, mixed>>
	 */
	public static function fields_for_tab( string $tab ): array {
		$sections = self::sections();
		$fields   = array();

		if ( empty( $sections[ $tab ] ) ) {
			return $fields;
		}

		foreach ( $sections[ $tab ] as $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				$fields[ $key ] = $field;
			}
		}

		return $fields;
	}

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
	 * Returns social link fields.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function social_fields(): array {
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
	private static function color_fields(): array {
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

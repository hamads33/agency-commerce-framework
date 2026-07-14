<?php
/**
 * Product-facing reusable commerce features.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Products;

use WC_Product;
use WP_Post;
use WP_Term;

/**
 * Coordinates product badges, delivery messaging, WhatsApp enquiries, and product metadata.
 */
final class FeatureManager {
	/**
	 * Badge meta prefix.
	 */
	private const BADGE_META_PREFIX = '_agency_commerce_badge_';

	/**
	 * Delivery message product meta key.
	 */
	private const DELIVERY_META_KEY = '_agency_commerce_delivery_message';

	/**
	 * Delivery message term meta key.
	 */
	private const DELIVERY_TERM_META_KEY = '_agency_commerce_delivery_message';

	/**
	 * Custom badge labels meta key.
	 */
	private const CUSTOM_BADGES_META_KEY = '_agency_commerce_badge_custom_labels';

	/**
	 * Registers frontend and admin hooks.
	 */
	public function register(): void {
		add_action( 'agency_commerce_single_product_summary_extensions', array( $this, 'render_single_product_extensions' ) );
		add_action( 'woocommerce_single_product_summary', array( $this, 'render_standard_theme_extensions' ), 35 );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'render_product_fields' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_product_fields' ) );
		add_action( 'product_cat_add_form_fields', array( $this, 'render_category_add_fields' ) );
		add_action( 'product_cat_edit_form_fields', array( $this, 'render_category_edit_fields' ) );
		add_action( 'created_product_cat', array( $this, 'save_category_fields' ) );
		add_action( 'edited_product_cat', array( $this, 'save_category_fields' ) );
	}

	/**
	 * Renders extension content on single product pages.
	 */
	public function render_single_product_extensions(): void {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		ob_start();

		$badges = self::get_product_badges( $product );

		if ( ! empty( $badges ) ) {
			self::render_product_badges( $badges, 'ac-product-badges ac-product-badges--summary' );
		}

		self::render_delivery_message( $product );
		self::render_whatsapp_button( $product );

		$content = trim( (string) ob_get_clean() );

		if ( '' === $content ) {
			return;
		}

		// The content is composed only from our own safe rendering helpers.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="ac-product-layout__extensions">' . $content . '</div>';
	}

	/**
	 * Renders product extensions when the parent theme hook is unavailable.
	 */
	public function render_standard_theme_extensions(): void {
		if ( 'agency-commerce' === get_template() ) {
			return;
		}

		$this->render_single_product_extensions();
	}

	/**
	 * Returns all badges for a product.
	 *
	 * @param WC_Product $product Product object.
	 * @return array<int, array<string, string>>
	 */
	public static function get_product_badges( WC_Product $product ): array {
		return BadgeManager::get_product_badges( $product );
	}

	/**
	 * Renders a badge group.
	 *
	 * @param array<int, array<string, string>> $badges Badge definitions.
	 * @param string                            $wrapper_class Wrapper class.
	 */
	public static function render_product_badges( array $badges, string $wrapper_class = 'ac-product-badges' ): void {
		BadgeManager::render_product_badges( $badges, $wrapper_class );
	}

	/**
	 * Renders the product editor fields.
	 */
	public function render_product_fields(): void {
		if ( ! function_exists( 'woocommerce_wp_select' ) ) {
			return;
		}

		global $post;
		$post_id = $post instanceof WP_Post ? $post->ID : 0;

		echo '<div class="options_group">';
		wp_nonce_field( 'agency_commerce_core_product_meta', 'agency_commerce_core_product_meta_nonce' );
		echo '<p><strong>' . esc_html__( 'Agency Commerce badges', 'agency-commerce-core' ) . '</strong></p>';

		$this->render_badge_select( $post_id, 'new', __( 'New badge', 'agency-commerce-core' ) );
		$this->render_badge_select( $post_id, 'featured', __( 'Featured badge', 'agency-commerce-core' ) );
		$this->render_badge_select( $post_id, 'best_seller', __( 'Best seller badge', 'agency-commerce-core' ) );
		$this->render_badge_select( $post_id, 'limited', __( 'Limited badge', 'agency-commerce-core' ) );

		woocommerce_wp_textarea_input(
			array(
				'id'          => self::CUSTOM_BADGES_META_KEY,
				'label'       => __( 'Custom badge labels', 'agency-commerce-core' ),
				'desc_tip'    => true,
				'description' => __( 'Enter one badge label per line.', 'agency-commerce-core' ),
				'value'       => BadgeManager::sanitize_badge_labels_for_storage( get_post_meta( $post_id, self::CUSTOM_BADGES_META_KEY, true ) ),
			)
		);

		woocommerce_wp_textarea_input(
			array(
				'id'          => self::DELIVERY_META_KEY,
				'label'       => __( 'Delivery override', 'agency-commerce-core' ),
				'desc_tip'    => true,
				'description' => __( 'Optional product-specific delivery message.', 'agency-commerce-core' ),
				'value'       => (string) get_post_meta( $post_id, self::DELIVERY_META_KEY, true ),
			)
		);
		echo '</div>';
	}

	/**
	 * Saves product fields.
	 *
	 * @param WC_Product $product Product object.
	 */
	public function save_product_fields( WC_Product $product ): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$product_id = $product->get_id();

		if ( ! current_user_can( 'edit_post', $product_id ) ) {
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			return;
		}

		$nonce = isset( $_POST['agency_commerce_core_product_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['agency_commerce_core_product_meta_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'agency_commerce_core_product_meta' ) ) {
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			return;
		}

		foreach ( array( 'new', 'featured', 'best_seller', 'limited' ) as $badge_key ) {
			$field_key = self::BADGE_META_PREFIX . $badge_key;

			if ( isset( $_POST[ $field_key ] ) ) {
				$value = sanitize_key( wp_unslash( $_POST[ $field_key ] ) );
				$product->update_meta_data( $field_key, in_array( $value, array( 'auto', 'show', 'hide' ), true ) ? $value : 'auto' );
			} else {
				$product->delete_meta_data( $field_key );
			}
		}

		$custom_badges = isset( $_POST[ self::CUSTOM_BADGES_META_KEY ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ self::CUSTOM_BADGES_META_KEY ] ) ) : '';
		$delivery      = isset( $_POST[ self::DELIVERY_META_KEY ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ self::DELIVERY_META_KEY ] ) ) : '';

		$product->update_meta_data( self::CUSTOM_BADGES_META_KEY, BadgeManager::sanitize_badge_labels_for_storage( $custom_badges ) );
		$product->update_meta_data( self::DELIVERY_META_KEY, $delivery );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Renders term fields for category delivery overrides.
	 */
	public function render_category_add_fields(): void {
		wp_nonce_field( 'agency_commerce_core_category_meta', 'agency_commerce_core_category_meta_nonce' );
		?>
		<div class="form-field term-delivery-wrap">
			<label for="<?php echo esc_attr( self::DELIVERY_TERM_META_KEY ); ?>"><?php esc_html_e( 'Delivery override', 'agency-commerce-core' ); ?></label>
			<textarea name="<?php echo esc_attr( self::DELIVERY_TERM_META_KEY ); ?>" id="<?php echo esc_attr( self::DELIVERY_TERM_META_KEY ); ?>" rows="4" class="large-text"></textarea>
			<p><?php esc_html_e( 'Optional delivery message for products in this category.', 'agency-commerce-core' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Renders term fields for category delivery overrides.
	 *
	 * @param WP_Term $term Term object.
	 */
	public function render_category_edit_fields( WP_Term $term ): void {
		$value = (string) get_term_meta( $term->term_id, self::DELIVERY_TERM_META_KEY, true );
		wp_nonce_field( 'agency_commerce_core_category_meta', 'agency_commerce_core_category_meta_nonce' );
		?>
		<tr class="form-field term-delivery-wrap">
			<th scope="row"><label for="<?php echo esc_attr( self::DELIVERY_TERM_META_KEY ); ?>"><?php esc_html_e( 'Delivery override', 'agency-commerce-core' ); ?></label></th>
			<td>
				<textarea name="<?php echo esc_attr( self::DELIVERY_TERM_META_KEY ); ?>" id="<?php echo esc_attr( self::DELIVERY_TERM_META_KEY ); ?>" rows="4" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Optional delivery message for products in this category.', 'agency-commerce-core' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Saves category fields.
	 *
	 * @param int $term_id Term ID.
	 */
	public function save_category_fields( int $term_id ): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! current_user_can( 'manage_options' ) ) {
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			return;
		}

		$nonce = isset( $_POST['agency_commerce_core_category_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['agency_commerce_core_category_meta_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'agency_commerce_core_category_meta' ) ) {
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			return;
		}

		$value = isset( $_POST[ self::DELIVERY_TERM_META_KEY ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ self::DELIVERY_TERM_META_KEY ] ) ) : '';

		if ( '' === $value ) {
			delete_term_meta( $term_id, self::DELIVERY_TERM_META_KEY );
			return;
		}

		update_term_meta( $term_id, self::DELIVERY_TERM_META_KEY, $value );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Renders the delivery message on single product pages.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	public static function render_delivery_message( ?WC_Product $product = null ): void {
		DeliveryManager::render_delivery_message( $product );
	}

	/**
	 * Returns the delivery message for a product.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	public static function get_delivery_message( ?WC_Product $product = null ): string {
		return DeliveryManager::get_delivery_message( $product );
	}

	/**
	 * Renders the WhatsApp enquiry button.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	public static function render_whatsapp_button( ?WC_Product $product = null ): void {
		WhatsAppEnquiry::render_button( $product );
	}

	/**
	 * Returns a WhatsApp message URL.
	 *
	 * @param WC_Product $product Product object.
	 * @param string     $template Message template.
	 * @param string     $phone Sanitized phone number.
	 */
	public static function build_whatsapp_url( WC_Product $product, string $template, string $phone ): string {
		return WhatsAppEnquiry::build_url( $product, $template, $phone );
	}

	/**
	 * Returns a WhatsApp message body.
	 *
	 * @param WC_Product $product Product object.
	 * @param string     $template Message template.
	 * @param string     $variation Variation label.
	 */
	public static function build_whatsapp_message( WC_Product $product, string $template, string $variation = '' ): string {
		return WhatsAppEnquiry::build_message( $product, $template, $variation );
	}

	/**
	 * Returns the current variation label from the request, if available.
	 *
	 * @param WC_Product $product Product object.
	 */
	public static function variation_label_from_product( WC_Product $product ): string {
		return WhatsAppEnquiry::variation_label_from_product( $product );
	}

	/**
	 * Renders a badge select field.
	 *
	 * @param int    $post_id Product post ID.
	 * @param string $badge_key Badge key.
	 * @param string $label Field label.
	 */
	private function render_badge_select( int $post_id, string $badge_key, string $label ): void {
		$field_key = self::BADGE_META_PREFIX . $badge_key;
		$value     = sanitize_key( (string) get_post_meta( $post_id, $field_key, true ) );

		woocommerce_wp_select(
			array(
				'id'      => $field_key,
				'label'   => $label,
				'value'   => in_array( $value, array( 'auto', 'show', 'hide' ), true ) ? $value : 'auto',
				'options' => array(
					'auto' => __( 'Auto', 'agency-commerce-core' ),
					'show' => __( 'Show', 'agency-commerce-core' ),
					'hide' => __( 'Hide', 'agency-commerce-core' ),
				),
			)
		);
	}
}

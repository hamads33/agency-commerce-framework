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
	 * Returns all badges for a product.
	 *
	 * @param WC_Product $product Product object.
	 * @return array<int, array<string, string>>
	 */
	public static function get_product_badges( WC_Product $product ): array {
		$badges = array();

		if ( (bool) agency_commerce_get_setting( 'shop.show_sale_badge', true ) && $product->is_on_sale() ) {
			$badges[] = array(
				'class' => 'is-sale',
				'label' => esc_html__( 'Sale', 'agency-commerce' ),
			);
		}

		if ( (bool) agency_commerce_get_setting( 'shop.show_stock_badge', true ) && ! $product->is_in_stock() ) {
			$badges[] = array(
				'class' => 'is-out-of-stock',
				'label' => esc_html__( 'Out of stock', 'agency-commerce' ),
			);
		}

		if ( (bool) agency_commerce_get_setting( 'shop.show_featured_badge', true ) ) {
			$state = self::badge_state( $product, 'featured' );
			if ( 'show' === $state || ( 'auto' === $state && $product->is_featured() ) ) {
				$badges[] = array(
					'class' => 'is-featured',
					'label' => esc_html__( 'Featured', 'agency-commerce' ),
				);
			}
		}

		if ( self::badge_state( $product, 'new' ) !== 'hide' && self::is_new_product( $product ) ) {
			$badges[] = array(
				'class' => 'is-new',
				'label' => esc_html__( 'New', 'agency-commerce' ),
			);
		}

		if ( self::badge_state( $product, 'best_seller' ) !== 'hide' && self::is_best_seller( $product ) ) {
			$badges[] = array(
				'class' => 'is-best-seller',
				'label' => esc_html__( 'Best seller', 'agency-commerce' ),
			);
		}

		if ( self::badge_state( $product, 'limited' ) !== 'hide' && self::is_limited_stock( $product ) ) {
			$badges[] = array(
				'class' => 'is-limited',
				'label' => esc_html__( 'Limited', 'agency-commerce' ),
			);
		}

		foreach ( self::custom_badges( $product ) as $label ) {
			$badges[] = array(
				'class' => 'is-custom',
				'label' => $label,
			);
		}

		/**
		 * Filters product badges before rendering.
		 *
		 * @param array<int, array<string, string>> $badges Badge definitions.
		 * @param WC_Product                        $product Product object.
		 */
		return apply_filters( 'agency_commerce_product_badges', $badges, $product );
	}

	/**
	 * Renders a badge group.
	 *
	 * @param array<int, array<string, string>> $badges Badge definitions.
	 * @param string                            $wrapper_class Wrapper class.
	 */
	public static function render_product_badges( array $badges, string $wrapper_class = 'ac-product-badges' ): void {
		if ( empty( $badges ) ) {
			return;
		}

		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>" aria-label="<?php esc_attr_e( 'Product badges', 'agency-commerce' ); ?>">
			<?php foreach ( $badges as $badge ) : ?>
				<span class="ac-product-badge <?php echo esc_attr( (string) $badge['class'] ); ?>"><?php echo esc_html( (string) $badge['label'] ); ?></span>
			<?php endforeach; ?>
		</div>
		<?php
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
				'value'       => (string) get_post_meta( $post_id, self::CUSTOM_BADGES_META_KEY, true ),
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

		$product->update_meta_data( self::CUSTOM_BADGES_META_KEY, self::sanitize_badge_labels( $custom_badges ) );
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
		if ( ! (bool) agency_commerce_get_setting( 'product_page.show_delivery_message', true ) ) {
			return;
		}

		$message = self::get_delivery_message( $product );

		if ( '' === $message ) {
			return;
		}

		?>
		<div class="ac-product-delivery">
			<p class="ac-product-delivery__label"><?php esc_html_e( 'Delivery', 'agency-commerce' ); ?></p>
			<p class="ac-product-delivery__message"><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * Returns the delivery message for a product.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	public static function get_delivery_message( ?WC_Product $product = null ): string {
		$product_message = '';

		if ( $product instanceof WC_Product ) {
			$product_message = (string) $product->get_meta( self::DELIVERY_META_KEY, true );
		}

		if ( '' !== trim( $product_message ) ) {
			$message = $product_message;
		} else {
			$message = self::get_category_delivery_message( $product );
		}

		if ( '' === trim( $message ) ) {
			$message = (string) agency_commerce_get_setting( 'product_page.delivery_message', '' );
		}

		/**
		 * Filters the resolved delivery message.
		 *
		 * @param string               $message Resolved message.
		 * @param WC_Product|null      $product Product object.
		 */
		return (string) apply_filters( 'agency_commerce_delivery_message', $message, $product );
	}

	/**
	 * Renders the WhatsApp enquiry button.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	public static function render_whatsapp_button( ?WC_Product $product = null ): void {
		if ( ! (bool) agency_commerce_get_setting( 'product_page.show_whatsapp_enquiry', true ) ) {
			return;
		}

		$product = self::normalize_product( $product );

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$phone = self::sanitize_phone_number( (string) agency_commerce_get_setting( 'whatsapp.phone_number', '' ) );

		if ( '' === $phone ) {
			return;
		}

		$text            = (string) agency_commerce_get_setting( 'whatsapp.button_text', __( 'WhatsApp enquiry', 'agency-commerce-core' ) );
		$template        = (string) agency_commerce_get_setting( 'whatsapp.message_template', '' );
		$button_url      = self::build_whatsapp_url( $product, $template, $phone );
		$needs_variation = $product->is_type( 'variable' );

		?>
		<div class="ac-whatsapp-enquiry-wrap">
			<a
				class="ac-button ac-whatsapp-enquiry<?php echo esc_attr( $needs_variation ? ' is-variation-pending' : '' ); ?>"
				href="<?php echo esc_url( $needs_variation ? '#' : $button_url ); ?>"
				<?php echo $needs_variation ? 'aria-disabled="true" data-whatsapp-needs-variation="true"' : ''; ?>
				data-whatsapp-phone="<?php echo esc_attr( $phone ); ?>"
				data-whatsapp-template="<?php echo esc_attr( $template ); ?>"
				data-product-name="<?php echo esc_attr( $product->get_name() ); ?>"
				data-product-url="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"
				data-product-sku="<?php echo esc_attr( $product->get_sku() ); ?>"
				data-product-variation="<?php echo esc_attr( self::variation_label_from_product( $product ) ); ?>"
			>
				<?php echo esc_html( $text ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Returns a WhatsApp message URL.
	 *
	 * @param WC_Product $product Product object.
	 * @param string     $template Message template.
	 * @param string     $phone Sanitized phone number.
	 */
	public static function build_whatsapp_url( WC_Product $product, string $template, string $phone ): string {
		$phone   = self::sanitize_phone_number( $phone );
		$message = self::build_whatsapp_message( $product, $template, self::variation_label_from_product( $product ) );

		if ( '' === $phone || '' === $message ) {
			return '';
		}

		$url = sprintf(
			'https://wa.me/%1$s?text=%2$s',
			rawurlencode( $phone ),
			rawurlencode( $message )
		);

		/**
		 * Filters the rendered WhatsApp enquiry URL.
		 *
		 * @param string    $url WhatsApp URL.
		 * @param WC_Product $product Product object.
		 * @param string    $message Message body.
		 */
		return (string) apply_filters( 'agency_commerce_whatsapp_enquiry_url', $url, $product, $message );
	}

	/**
	 * Returns a WhatsApp message body.
	 *
	 * @param WC_Product $product Product object.
	 * @param string     $template Message template.
	 * @param string     $variation Variation label.
	 */
	public static function build_whatsapp_message( WC_Product $product, string $template, string $variation = '' ): string {
		if ( '' === trim( $template ) ) {
			$template = "Hello, I am interested in {product_name}.\nProduct: {product_url}\nSKU: {sku}\nVariation: {variation}";
		}

		$replacements = array(
			'{product_name}' => $product->get_name(),
			'{product_url}'  => get_permalink( $product->get_id() ),
			'{sku}'          => $product->get_sku(),
			'{variation}'    => '' !== trim( $variation ) ? $variation : esc_html__( 'Not selected', 'agency-commerce-core' ),
		);

		$message = strtr( $template, $replacements );
		$message = sanitize_textarea_field( $message );

		/**
		 * Filters the rendered WhatsApp enquiry message.
		 *
		 * @param string    $message Message body.
		 * @param WC_Product $product Product object.
		 */
		return (string) apply_filters( 'agency_commerce_whatsapp_message', $message, $product );
	}

	/**
	 * Returns the current variation label from the request, if available.
	 *
	 * @param WC_Product $product Product object.
	 */
	public static function variation_label_from_product( WC_Product $product ): string {
		if ( ! $product->is_type( 'variable' ) ) {
			return '';
		}

		$attributes = array();

		foreach ( $product->get_variation_attributes() as $attribute => $options ) {
			$posted_key = 'attribute_' . sanitize_title( $attribute );
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only variation selection from the current request.
			$value = isset( $_REQUEST[ $posted_key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $posted_key ] ) ) : '';

			if ( '' !== $value ) {
				$attributes[] = wc_attribute_label( $attribute ) . ': ' . $value;
			}
		}

		return implode( ', ', $attributes );
	}

	/**
	 * Returns the category override or global delivery message.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	private static function get_category_delivery_message( ?WC_Product $product ): string {
		if ( ! $product instanceof WC_Product ) {
			return '';
		}

		$terms = get_the_terms( $product->get_id(), 'product_cat' );

		if ( ! is_array( $terms ) ) {
			return '';
		}

		foreach ( $terms as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			$message = (string) get_term_meta( $term->term_id, self::DELIVERY_TERM_META_KEY, true );

			if ( '' !== trim( $message ) ) {
				return $message;
			}
		}

		return '';
	}

	/**
	 * Returns a badge state from product metadata.
	 *
	 * @param WC_Product $product Product object.
	 * @param string     $badge Badge key.
	 */
	private static function badge_state( WC_Product $product, string $badge ): string {
		$value = sanitize_key( (string) $product->get_meta( self::BADGE_META_PREFIX . $badge, true ) );

		return in_array( $value, array( 'auto', 'show', 'hide' ), true ) ? $value : 'auto';
	}

	/**
	 * Determines whether a product is new.
	 *
	 * @param WC_Product $product Product object.
	 */
	private static function is_new_product( WC_Product $product ): bool {
		$days = (int) apply_filters( 'agency_commerce_new_badge_days', 30, $product );
		$date = $product->get_date_created();

		if ( ! $date ) {
			return false;
		}

		return ( time() - $date->getTimestamp() ) <= ( DAY_IN_SECONDS * max( 1, $days ) );
	}

	/**
	 * Determines whether a product is a best seller.
	 *
	 * @param WC_Product $product Product object.
	 */
	private static function is_best_seller( WC_Product $product ): bool {
		$threshold   = (int) apply_filters( 'agency_commerce_best_seller_threshold', 50, $product );
		$total_sales = max(
			(int) $product->get_total_sales(),
			(int) get_post_meta( $product->get_id(), '_total_sales', true )
		);

		return $total_sales >= max( 1, $threshold );
	}

	/**
	 * Determines whether a product has limited stock.
	 *
	 * @param WC_Product $product Product object.
	 */
	private static function is_limited_stock( WC_Product $product ): bool {
		$threshold = (int) apply_filters( 'agency_commerce_limited_stock_threshold', 5, $product );
		$quantity  = $product->get_stock_quantity();

		return null !== $quantity && $quantity > 0 && $quantity <= max( 1, $threshold );
	}

	/**
	 * Returns custom badge labels for a product.
	 *
	 * @param WC_Product $product Product object.
	 * @return string[]
	 */
	private static function custom_badges( WC_Product $product ): array {
		$raw = (string) $product->get_meta( self::CUSTOM_BADGES_META_KEY, true );

		if ( '' === trim( $raw ) ) {
			return array();
		}

		return self::sanitize_badge_labels( $raw );
	}

	/**
	 * Sanitizes badge labels from a newline-separated string.
	 *
	 * @param mixed $raw Raw badge labels.
	 * @return string[]
	 */
	private static function sanitize_badge_labels( $raw ): array {
		$lines = preg_split( '/\r\n|\r|\n/', sanitize_textarea_field( (string) $raw ) );
		$lines = is_array( $lines ) ? $lines : array();

		$labels = array();

		foreach ( $lines as $line ) {
			$label = trim( sanitize_text_field( (string) $line ) );

			if ( '' === $label ) {
				continue;
			}

			$labels[] = $label;
		}

		return array_values( array_unique( $labels ) );
	}

	/**
	 * Sanitizes a phone number to digits only.
	 *
	 * @param string $raw Raw phone number.
	 */
	private static function sanitize_phone_number( string $raw ): string {
		$phone = preg_replace( '/\D+/', '', $raw );

		return is_string( $phone ) ? $phone : '';
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

	/**
	 * Normalizes a product object.
	 *
	 * @param WC_Product|null $candidate Product object.
	 */
	private static function normalize_product( ?WC_Product $candidate ): ?WC_Product {
		if ( $candidate instanceof WC_Product ) {
			return $candidate;
		}

		global $product;

		return $product instanceof WC_Product ? $product : null;
	}
}

<?php
/**
 * WhatsApp enquiry URL and rendering.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Products;

use WC_Product;

/**
 * Builds safe WhatsApp enquiry links.
 */
final class WhatsAppEnquiry {
	/**
	 * Renders the WhatsApp enquiry button.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	public static function render_button( ?WC_Product $product = null ): void {
		if ( ! (bool) ProductSettings::get( 'product_page.show_whatsapp_enquiry', true ) ) {
			return;
		}

		$product = self::normalize_product( $product );

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$phone = self::sanitize_phone_number( (string) ProductSettings::get( 'whatsapp.phone_number', '' ) );

		if ( '' === $phone ) {
			return;
		}

		$text            = (string) ProductSettings::get( 'whatsapp.button_text', __( 'WhatsApp enquiry', 'agency-commerce-core' ) );
		$template        = (string) ProductSettings::get( 'whatsapp.message_template', '' );
		$button_url      = self::build_url( $product, $template, $phone );
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
	public static function build_url( WC_Product $product, string $template, string $phone ): string {
		$phone   = self::sanitize_phone_number( $phone );
		$message = self::build_message( $product, $template, self::variation_label_from_product( $product ) );

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
		 * @param string     $url WhatsApp URL.
		 * @param WC_Product $product Product object.
		 * @param string     $message Message body.
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
	public static function build_message( WC_Product $product, string $template, string $variation = '' ): string {
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
		 * @param string     $message Message body.
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
			unset( $options );
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
	 * Sanitizes a phone number to digits only.
	 *
	 * @param string $raw Raw phone number.
	 */
	private static function sanitize_phone_number( string $raw ): string {
		$phone = preg_replace( '/\D+/', '', $raw );

		return is_string( $phone ) ? $phone : '';
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

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {}
}

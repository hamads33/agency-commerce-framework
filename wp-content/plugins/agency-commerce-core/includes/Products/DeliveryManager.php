<?php
/**
 * Product delivery message policy.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Products;

use WC_Product;
use WP_Term;

/**
 * Resolves global, category, and product delivery messages.
 */
final class DeliveryManager {
	/**
	 * Delivery message product meta key.
	 */
	public const DELIVERY_META_KEY = '_agency_commerce_delivery_message';

	/**
	 * Delivery message term meta key.
	 */
	public const DELIVERY_TERM_META_KEY = '_agency_commerce_delivery_message';

	/**
	 * Renders the delivery message on single product pages.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	public static function render_delivery_message( ?WC_Product $product = null ): void {
		if ( ! (bool) ProductSettings::get( 'product_page.show_delivery_message', true ) ) {
			return;
		}

		$message = self::get_delivery_message( $product );

		if ( '' === $message ) {
			return;
		}

		?>
		<div class="ac-product-delivery">
			<p class="ac-product-delivery__label"><?php esc_html_e( 'Delivery', 'agency-commerce-core' ); ?></p>
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
			$message = (string) ProductSettings::get( 'product_page.delivery_message', '' );
		}

		/**
		 * Filters the resolved delivery message.
		 *
		 * @param string          $message Resolved message.
		 * @param WC_Product|null $product Product object.
		 */
		return (string) apply_filters( 'agency_commerce_delivery_message', $message, $product );
	}

	/**
	 * Returns the category override.
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
	 * Prevent direct construction.
	 */
	private function __construct() {}
}

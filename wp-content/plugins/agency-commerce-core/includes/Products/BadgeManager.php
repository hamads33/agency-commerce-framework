<?php
/**
 * Product badge policy and rendering.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Products;

use WC_Product;

/**
 * Resolves reusable product badge data.
 */
final class BadgeManager {
	/**
	 * Badge meta prefix.
	 */
	public const BADGE_META_PREFIX = '_agency_commerce_badge_';

	/**
	 * Custom badge labels meta key.
	 */
	public const CUSTOM_BADGES_META_KEY = '_agency_commerce_badge_custom_labels';

	/**
	 * Returns all badges for a product.
	 *
	 * @param WC_Product $product Product object.
	 * @return array<int, array<string, string>>
	 */
	public static function get_product_badges( WC_Product $product ): array {
		$badges = array();

		if ( (bool) ProductSettings::get( 'shop.show_sale_badge', true ) && $product->is_on_sale() ) {
			$badges[] = array(
				'class' => 'is-sale',
				'label' => esc_html__( 'Sale', 'agency-commerce-core' ),
			);
		}

		if ( (bool) ProductSettings::get( 'shop.show_stock_badge', true ) && ! $product->is_in_stock() ) {
			$badges[] = array(
				'class' => 'is-out-of-stock',
				'label' => esc_html__( 'Out of stock', 'agency-commerce-core' ),
			);
		}

		if ( (bool) ProductSettings::get( 'shop.show_featured_badge', true ) ) {
			$state = self::badge_state( $product, 'featured' );
			if ( 'show' === $state || ( 'auto' === $state && $product->is_featured() ) ) {
				$badges[] = array(
					'class' => 'is-featured',
					'label' => esc_html__( 'Featured', 'agency-commerce-core' ),
				);
			}
		}

		if ( self::badge_state( $product, 'new' ) !== 'hide' && self::is_new_product( $product ) ) {
			$badges[] = array(
				'class' => 'is-new',
				'label' => esc_html__( 'New', 'agency-commerce-core' ),
			);
		}

		if ( self::badge_state( $product, 'best_seller' ) !== 'hide' && self::is_best_seller( $product ) ) {
			$badges[] = array(
				'class' => 'is-best-seller',
				'label' => esc_html__( 'Best seller', 'agency-commerce-core' ),
			);
		}

		if ( self::badge_state( $product, 'limited' ) !== 'hide' && self::is_limited_stock( $product ) ) {
			$badges[] = array(
				'class' => 'is-limited',
				'label' => esc_html__( 'Limited', 'agency-commerce-core' ),
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
		<div class="<?php echo esc_attr( $wrapper_class ); ?>" aria-label="<?php esc_attr_e( 'Product badges', 'agency-commerce-core' ); ?>">
			<?php foreach ( $badges as $badge ) : ?>
				<span class="ac-product-badge <?php echo esc_attr( (string) ( $badge['class'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $badge['label'] ?? '' ) ); ?></span>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Sanitizes badge labels and returns the canonical stored string.
	 *
	 * Existing CP4B installs may contain an array. Runtime reads accept both array
	 * and newline-separated string values; new saves use newline text.
	 *
	 * @param mixed $raw Raw badge labels.
	 */
	public static function sanitize_badge_labels_for_storage( $raw ): string {
		return implode( "\n", self::sanitize_badge_labels( $raw ) );
	}

	/**
	 * Returns custom badge labels for a product.
	 *
	 * @param WC_Product $product Product object.
	 * @return string[]
	 */
	private static function custom_badges( WC_Product $product ): array {
		$raw = $product->get_meta( self::CUSTOM_BADGES_META_KEY, true );

		if ( is_array( $raw ) ) {
			return self::sanitize_badge_labels( $raw );
		}

		if ( '' === trim( (string) $raw ) ) {
			return array();
		}

		return self::sanitize_badge_labels( $raw );
	}

	/**
	 * Sanitizes badge labels from legacy arrays or newline-separated strings.
	 *
	 * @param mixed $raw Raw badge labels.
	 * @return string[]
	 */
	private static function sanitize_badge_labels( $raw ): array {
		if ( is_array( $raw ) ) {
			$lines = $raw;
		} else {
			$lines = preg_split( '/\r\n|\r|\n/', sanitize_textarea_field( (string) $raw ) );
			$lines = is_array( $lines ) ? $lines : array();
		}

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
	 * Prevent direct construction.
	 */
	private function __construct() {}
}

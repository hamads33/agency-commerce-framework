<?php
/**
 * WooCommerce compatibility declarations.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'before_woocommerce_init', 'agency_commerce_declare_woocommerce_features' );
add_filter( 'woocommerce_add_to_cart_fragments', 'agency_commerce_cart_count_fragment' );

/**
 * Declares compatibility with WooCommerce block cart and checkout where available.
 */
function agency_commerce_declare_woocommerce_features(): void {
	if ( ! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		return;
	}

	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
		'cart_checkout_blocks',
		AGENCY_COMMERCE_THEME_DIR . 'functions.php',
		true
	);
}

/**
 * Returns the current WooCommerce cart count.
 */
function agency_commerce_get_cart_count(): int {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}

	return (int) WC()->cart->get_cart_contents_count();
}

/**
 * Renders the cart-count element used by WooCommerce fragments.
 */
function agency_commerce_cart_count_markup(): string {
	$count = agency_commerce_get_cart_count();
	$label = sprintf(
		/* translators: %d: Number of items in the cart. */
		_n( '%d item in cart', '%d items in cart', $count, 'agency-commerce' ),
		$count
	);

	ob_start();
	?>
	<span class="ac-cart-count" data-cart-count="<?php echo esc_attr( (string) $count ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
		<?php echo esc_html( (string) $count ); ?>
	</span>
	<?php
	return (string) ob_get_clean();
}

/**
 * Updates the cart count through WooCommerce's supported fragment system.
 *
 * @param array<string, string> $fragments Existing fragments.
 * @return array<string, string>
 */
function agency_commerce_cart_count_fragment( array $fragments ): array {
	$fragments['span.ac-cart-count'] = agency_commerce_cart_count_markup();

	return $fragments;
}

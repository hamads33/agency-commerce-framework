<?php
/**
 * Sticky single-product layout.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'ac-product-layout ac-product-layout--sticky', $product ); ?>>
	<div class="ac-product-layout__main">
		<div class="ac-product-layout__gallery">
			<?php do_action( 'woocommerce_before_single_product_summary' ); ?>
		</div>
		<div class="summary entry-summary ac-product-layout__summary">
			<?php do_action( 'woocommerce_single_product_summary' ); ?>
			<?php do_action( 'agency_commerce_single_product_summary_extensions' ); ?>
		</div>
	</div>

	<div class="ac-product-layout__after">
		<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
		<?php do_action( 'agency_commerce_after_single_product_summary_extensions' ); ?>
	</div>
</div>

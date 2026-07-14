<?php
/**
 * Compact product-card variant.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<li <?php wc_product_class( 'ac-product-card-item', $agency_commerce_product_card_data['product'] ); ?>>
	<?php agency_commerce_do_product_loop_hook( 'woocommerce_before_shop_loop_item' ); ?>
	<article class="ac-product-card ac-product-card--compact">
		<div class="ac-product-card__media-wrap">
			<?php agency_commerce_do_product_loop_hook( 'woocommerce_before_shop_loop_item_title' ); ?>
			<?php agency_commerce_render_product_card_media( $agency_commerce_product_card_data ); ?>
			<?php agency_commerce_render_product_card_badges( $agency_commerce_product_card_data['badges'] ); ?>
		</div>
		<div class="ac-product-card__body">
			<?php agency_commerce_render_product_card_category( $agency_commerce_product_card_data ); ?>
			<?php agency_commerce_do_product_loop_hook( 'woocommerce_shop_loop_item_title' ); ?>
			<?php agency_commerce_render_product_card_title( $agency_commerce_product_card_data ); ?>
			<?php agency_commerce_do_product_loop_hook( 'woocommerce_after_shop_loop_item_title' ); ?>
			<?php agency_commerce_render_product_card_price( $agency_commerce_product_card_data ); ?>
			<?php agency_commerce_render_product_card_action( $agency_commerce_product_card_data ); ?>
		</div>
	</article>
	<?php agency_commerce_do_product_loop_hook( 'woocommerce_after_shop_loop_item' ); ?>
</li>

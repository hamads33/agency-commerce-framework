<?php
/**
 * Minimal product-card variant.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<li <?php wc_product_class( 'ac-product-card-item', $agency_commerce_product_card_data['product'] ); ?>>
	<article class="ac-product-card ac-product-card--minimal">
		<div class="ac-product-card__media-wrap">
			<?php agency_commerce_render_product_card_media( $agency_commerce_product_card_data ); ?>
			<?php agency_commerce_render_product_card_badges( $agency_commerce_product_card_data['badges'] ); ?>
		</div>
		<div class="ac-product-card__body">
			<?php agency_commerce_render_product_card_title( $agency_commerce_product_card_data ); ?>
			<div class="ac-product-card__meta-row">
				<?php agency_commerce_render_product_card_price( $agency_commerce_product_card_data ); ?>
				<?php agency_commerce_render_product_card_rating( $agency_commerce_product_card_data ); ?>
			</div>
			<?php agency_commerce_render_product_card_action( $agency_commerce_product_card_data ); ?>
		</div>
	</article>
</li>

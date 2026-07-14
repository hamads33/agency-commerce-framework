<?php
/**
 * Product archive template.
 *
 * @package AgencyCommerce
 * @version 10.9.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

$agency_commerce_filter_layout = (string) agency_commerce_get_setting( 'shop.filter_layout', 'sidebar' );
?>

<?php agency_commerce_do_archive_wrapper_hook( 'woocommerce_before_main_content' ); ?>

<main id="primary" class="site-main ac-shop-archive">
	<?php if ( function_exists( 'woocommerce_breadcrumb' ) ) : ?>
		<div class="ac-shop-archive__breadcrumbs">
			<?php woocommerce_breadcrumb(); ?>
		</div>
	<?php endif; ?>

	<header class="woocommerce-products-header ac-shop-archive__header">
		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) && ! is_product_category() ) : ?>
			<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>

		<?php agency_commerce_render_category_banner(); ?>
	</header>

	<?php agency_commerce_render_filter_drawer(); ?>

	<div class="ac-shop-archive__layout">
		<?php if ( 'drawer' !== $agency_commerce_filter_layout ) : ?>
			<aside class="ac-shop-archive__sidebar" aria-label="<?php esc_attr_e( 'Product filters', 'agency-commerce' ); ?>">
				<?php agency_commerce_render_shop_filters( 'sidebar' ); ?>
			</aside>
		<?php endif; ?>

		<section class="ac-shop-archive__products" aria-label="<?php esc_attr_e( 'Products', 'agency-commerce' ); ?>">
			<?php if ( woocommerce_product_loop() ) : ?>
				<div class="ac-shop-archive__toolbar">
					<?php do_action( 'woocommerce_before_shop_loop' ); ?>
				</div>

				<?php woocommerce_product_loop_start(); ?>

				<?php
				while ( have_posts() ) {
					the_post();

					/**
					 * Fires before an individual product loop item renders.
					 */
					do_action( 'woocommerce_shop_loop' );

					wc_get_template_part( 'content', 'product' );
				}
				?>

				<?php woocommerce_product_loop_end(); ?>

				<?php do_action( 'woocommerce_after_shop_loop' ); ?>
			<?php else : ?>
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			<?php endif; ?>
		</section>
	</div>
</main>

<?php
agency_commerce_do_archive_wrapper_hook( 'woocommerce_after_main_content' );
get_footer( 'shop' );

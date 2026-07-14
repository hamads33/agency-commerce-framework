<?php
/**
 * Search results template.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

get_header();
?>

<main id="primary" class="site-main ac-search-results">
	<header class="ac-search-results__header">
		<h1>
			<?php
			printf(
				/* translators: %s: Search query. */
				esc_html__( 'Search results for "%s"', 'agency-commerce' ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
		<?php get_search_form(); ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<?php if ( agency_commerce_is_product_search() ) : ?>
			<?php woocommerce_product_loop_start(); ?>
			<?php
			while ( have_posts() ) {
				the_post();
				wc_get_template_part( 'content', 'product' );
			}
			?>
			<?php woocommerce_product_loop_end(); ?>
		<?php else : ?>
			<div class="ac-search-results__list">
				<?php
				while ( have_posts() ) {
					the_post();
					?>
					<article <?php post_class( 'ac-search-result' ); ?>>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php the_excerpt(); ?>
					</article>
					<?php
				}
				?>
			</div>
		<?php endif; ?>

		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 1,
				'prev_text' => esc_html__( 'Previous', 'agency-commerce' ),
				'next_text' => esc_html__( 'Next', 'agency-commerce' ),
			)
		);
		?>
	<?php else : ?>
		<section class="ac-empty-state">
			<h2><?php esc_html_e( 'No results found', 'agency-commerce' ); ?></h2>
			<p><?php esc_html_e( 'Try another product name, category, or keyword.', 'agency-commerce' ); ?></p>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();

<?php
/**
 * Furniture and home showcase homepage.
 *
 * @package AgencyCommerceHome
 */

declare( strict_types=1 );

get_header();

$agency_commerce_home_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>

<main id="primary" class="site-main ac-showcase-home ac-home-store-front">
	<section class="ac-home-hero" aria-labelledby="home-hero-title">
		<div class="ac-home-hero__image" aria-hidden="true">
			<span></span>
			<span></span>
			<span></span>
		</div>
		<div class="ac-home-hero__copy">
			<p class="ac-home-kicker"><?php esc_html_e( 'Room study no. 04', 'agency-commerce-home' ); ?></p>
			<h1 id="home-hero-title"><?php esc_html_e( 'Furniture commerce with the pacing of an interior journal.', 'agency-commerce-home' ); ?></h1>
			<p><?php esc_html_e( 'A furniture and home direction built around warm editorial imagery, spacious room stories, refined product cards, and calm conversion surfaces.', 'agency-commerce-home' ); ?></p>
			<a class="ac-home-button" href="<?php echo esc_url( $agency_commerce_home_shop_url ); ?>"><?php esc_html_e( 'Explore the showroom', 'agency-commerce-home' ); ?></a>
		</div>
	</section>

	<section class="ac-home-rooms" aria-label="<?php esc_attr_e( 'Room categories', 'agency-commerce-home' ); ?>">
		<article>
			<span></span>
			<h2><?php esc_html_e( 'Living', 'agency-commerce-home' ); ?></h2>
			<p><?php esc_html_e( 'Soft forms, grounded woods, and layered seating.', 'agency-commerce-home' ); ?></p>
		</article>
		<article>
			<span></span>
			<h2><?php esc_html_e( 'Dining', 'agency-commerce-home' ); ?></h2>
			<p><?php esc_html_e( 'Tables and lighting composed for everyday rituals.', 'agency-commerce-home' ); ?></p>
		</article>
		<article>
			<span></span>
			<h2><?php esc_html_e( 'Workspace', 'agency-commerce-home' ); ?></h2>
			<p><?php esc_html_e( 'Calm utility with material warmth and proportion.', 'agency-commerce-home' ); ?></p>
		</article>
	</section>

	<section class="ac-home-journal">
		<div>
			<p class="ac-home-kicker"><?php esc_html_e( 'Design service', 'agency-commerce-home' ); ?></p>
			<h2><?php esc_html_e( 'Story-led shopping that still reaches checkout quickly.', 'agency-commerce-home' ); ?></h2>
		</div>
		<p><?php esc_html_e( 'This child theme demonstrates furniture-friendly layouts: generous lifestyle panels, warm typography, tactile cards, visible form focus, and WooCommerce account and checkout styling inherited through presentation only.', 'agency-commerce-home' ); ?></p>
	</section>
</main>

<?php
get_footer();

<?php
/**
 * Fashion showcase homepage.
 *
 * @package AgencyCommerceFashion
 */

declare( strict_types=1 );

get_header();

$agency_commerce_fashion_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>

<main id="primary" class="site-main ac-showcase-home ac-fashion-home">
	<section class="ac-fashion-hero" aria-labelledby="fashion-hero-title">
		<div class="ac-fashion-hero__copy">
			<p class="ac-showcase-eyebrow"><?php esc_html_e( 'Autumn editorial', 'agency-commerce-fashion' ); ?></p>
			<h1 id="fashion-hero-title"><?php esc_html_e( 'Reserved silhouettes for a quieter kind of arrival.', 'agency-commerce-fashion' ); ?></h1>
			<p><?php esc_html_e( 'A luxury fashion storefront direction with restrained palettes, generous whitespace, and product photography treated like editorial still life.', 'agency-commerce-fashion' ); ?></p>
			<a class="ac-showcase-button" href="<?php echo esc_url( $agency_commerce_fashion_shop_url ); ?>"><?php esc_html_e( 'View the collection', 'agency-commerce-fashion' ); ?></a>
		</div>
		<div class="ac-fashion-hero__art" aria-hidden="true">
			<span></span>
			<span></span>
		</div>
	</section>

	<section class="ac-fashion-editorial" aria-label="<?php esc_attr_e( 'Editorial collection story', 'agency-commerce-fashion' ); ?>">
		<article>
			<p class="ac-showcase-eyebrow"><?php esc_html_e( '01 / Tailoring', 'agency-commerce-fashion' ); ?></p>
			<h2><?php esc_html_e( 'Negative space, precise crops, and a runway-informed product rhythm.', 'agency-commerce-fashion' ); ?></h2>
		</article>
		<article>
			<p><?php esc_html_e( 'Merino layers', 'agency-commerce-fashion' ); ?></p>
		</article>
		<article>
			<p><?php esc_html_e( 'Sculpted outerwear', 'agency-commerce-fashion' ); ?></p>
		</article>
	</section>

	<section class="ac-showcase-section ac-fashion-story">
		<div>
			<p class="ac-showcase-eyebrow"><?php esc_html_e( 'Material study', 'agency-commerce-fashion' ); ?></p>
			<h2><?php esc_html_e( 'Editorial commerce without visual noise.', 'agency-commerce-fashion' ); ?></h2>
		</div>
		<p><?php esc_html_e( 'The fashion child theme demonstrates large image zones, calm navigation, typography-led hierarchy, and restrained purchase surfaces while preserving WooCommerce behavior underneath.', 'agency-commerce-fashion' ); ?></p>
	</section>
</main>

<?php
get_footer();

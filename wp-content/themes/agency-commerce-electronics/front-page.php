<?php
/**
 * Electronics showcase homepage.
 *
 * @package AgencyCommerceElectronics
 */

declare( strict_types=1 );

get_header();

$agency_commerce_electronics_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>

<main id="primary" class="site-main ac-showcase-home ac-electronics-home">
	<section class="ac-electronics-hero" aria-labelledby="electronics-hero-title">
		<div class="ac-electronics-hero__copy">
			<p class="ac-showcase-kicker"><?php esc_html_e( 'Modular performance system', 'agency-commerce-electronics' ); ?></p>
			<h1 id="electronics-hero-title"><?php esc_html_e( 'Technology presented with signal, structure, and restraint.', 'agency-commerce-electronics' ); ?></h1>
			<p><?php esc_html_e( 'An electronics storefront direction using dark technical surfaces, crisp product grids, specification-led hierarchy, and smooth but minimal interaction cues.', 'agency-commerce-electronics' ); ?></p>
			<a class="ac-tech-button" href="<?php echo esc_url( $agency_commerce_electronics_shop_url ); ?>"><?php esc_html_e( 'Configure products', 'agency-commerce-electronics' ); ?></a>
		</div>
		<div class="ac-electronics-device" aria-hidden="true">
			<span class="ac-electronics-device__screen"></span>
			<span class="ac-electronics-device__module ac-electronics-device__module--one"></span>
			<span class="ac-electronics-device__module ac-electronics-device__module--two"></span>
		</div>
	</section>

	<section class="ac-electronics-specs" aria-label="<?php esc_attr_e( 'Storefront capabilities', 'agency-commerce-electronics' ); ?>">
		<article>
			<strong><?php esc_html_e( '01', 'agency-commerce-electronics' ); ?></strong>
			<h2><?php esc_html_e( 'Product-first grid', 'agency-commerce-electronics' ); ?></h2>
			<p><?php esc_html_e( 'Dense enough for comparison, calm enough for premium buying decisions.', 'agency-commerce-electronics' ); ?></p>
		</article>
		<article>
			<strong><?php esc_html_e( '02', 'agency-commerce-electronics' ); ?></strong>
			<h2><?php esc_html_e( 'Technical details', 'agency-commerce-electronics' ); ?></h2>
			<p><?php esc_html_e( 'Cards, product pages, and checkout surfaces prioritize clarity and scannability.', 'agency-commerce-electronics' ); ?></p>
		</article>
		<article>
			<strong><?php esc_html_e( '03', 'agency-commerce-electronics' ); ?></strong>
			<h2><?php esc_html_e( 'Reduced motion aware', 'agency-commerce-electronics' ); ?></h2>
			<p><?php esc_html_e( 'Transitions are polished without blocking navigation, form entry, or checkout.', 'agency-commerce-electronics' ); ?></p>
		</article>
	</section>

	<section class="ac-electronics-system">
		<div>
			<p class="ac-showcase-kicker"><?php esc_html_e( 'Interface language', 'agency-commerce-electronics' ); ?></p>
			<h2><?php esc_html_e( 'High contrast commerce for complex product lines.', 'agency-commerce-electronics' ); ?></h2>
		</div>
		<div class="ac-electronics-system__panel" aria-hidden="true">
			<span></span>
			<span></span>
			<span></span>
			<span></span>
		</div>
	</section>
</main>

<?php
get_footer();

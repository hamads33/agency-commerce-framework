<?php
/**
 * Columns footer variant.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<footer class="ac-site-footer ac-site-footer--columns">
	<div class="ac-container ac-site-footer__grid">
		<div class="ac-site-footer__brand">
			<?php agency_commerce_render_logo(); ?>
			<?php if ( ! empty( $agency_commerce_footer_data['trust_text'] ) ) : ?>
				<p><?php echo esc_html( (string) $agency_commerce_footer_data['trust_text'] ); ?></p>
			<?php endif; ?>
			<?php agency_commerce_render_social_links( $agency_commerce_footer_data['social'] ); ?>
		</div>

		<nav class="ac-site-footer__nav" aria-label="<?php esc_attr_e( 'Footer menu', 'agency-commerce' ); ?>">
			<?php agency_commerce_render_menu( 'footer', 'ac-menu ac-menu--footer' ); ?>
		</nav>

		<div class="ac-site-footer__contact">
			<h2><?php esc_html_e( 'Contact', 'agency-commerce' ); ?></h2>
			<?php if ( ! empty( $agency_commerce_footer_data['contact_email'] ) ) : ?>
				<p><a href="mailto:<?php echo esc_attr( (string) $agency_commerce_footer_data['contact_email'] ); ?>"><?php echo esc_html( (string) $agency_commerce_footer_data['contact_email'] ); ?></a></p>
			<?php endif; ?>
			<?php if ( ! empty( $agency_commerce_footer_data['contact_phone'] ) ) : ?>
				<p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', (string) $agency_commerce_footer_data['contact_phone'] ) ); ?>"><?php echo esc_html( (string) $agency_commerce_footer_data['contact_phone'] ); ?></a></p>
			<?php endif; ?>
			<?php if ( ! empty( $agency_commerce_footer_data['address'] ) ) : ?>
				<address><?php echo esc_html( (string) $agency_commerce_footer_data['address'] ); ?></address>
			<?php endif; ?>
		</div>
	</div>
	<div class="ac-container ac-site-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
	</div>
</footer>

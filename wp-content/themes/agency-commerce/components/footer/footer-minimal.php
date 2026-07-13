<?php
/**
 * Minimal footer variant.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<footer class="ac-site-footer ac-site-footer--minimal">
	<div class="ac-container ac-site-footer__minimal-inner">
		<div class="ac-site-footer__brand">
			<?php agency_commerce_render_logo(); ?>
		</div>
		<nav class="ac-site-footer__nav" aria-label="<?php esc_attr_e( 'Footer menu', 'agency-commerce' ); ?>">
			<?php agency_commerce_render_menu( 'footer', 'ac-menu ac-menu--footer' ); ?>
		</nav>
		<?php agency_commerce_render_social_links( $agency_commerce_footer_data['social'] ); ?>
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
	</div>
</footer>

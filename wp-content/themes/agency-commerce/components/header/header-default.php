<?php
/**
 * Default header variant.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$agency_commerce_header_classes = array(
	'ac-site-header',
	'ac-site-header--default',
	! empty( $agency_commerce_header_data['sticky'] ) ? 'is-sticky' : '',
);
?>

<header class="<?php echo esc_attr( trim( implode( ' ', $agency_commerce_header_classes ) ) ); ?>" data-sticky-header="<?php echo esc_attr( ! empty( $agency_commerce_header_data['sticky'] ) ? 'true' : 'false' ); ?>">
	<?php if ( ! empty( $agency_commerce_header_data['show_announcement'] ) ) : ?>
		<div class="ac-announcement">
			<div class="ac-container">
				<?php if ( ! empty( $agency_commerce_header_data['announcement_url'] ) ) : ?>
					<a href="<?php echo esc_url( (string) $agency_commerce_header_data['announcement_url'] ); ?>"><?php echo esc_html( (string) $agency_commerce_header_data['announcement_text'] ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( (string) $agency_commerce_header_data['announcement_text'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="ac-container ac-site-header__inner">
		<div class="ac-site-branding">
			<?php agency_commerce_render_logo(); ?>
		</div>

		<nav class="ac-primary-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'agency-commerce' ); ?>">
			<?php agency_commerce_render_menu( 'primary', 'ac-menu ac-menu--desktop' ); ?>
		</nav>

		<?php agency_commerce_render_header_controls( $agency_commerce_header_data ); ?>
	</div>
</header>

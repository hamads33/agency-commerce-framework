<?php
/**
 * Centered header variant.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$agency_commerce_header_classes = array(
	'ac-site-header',
	'ac-site-header--centered',
	! empty( $agency_commerce_header_data['sticky'] ) ? 'is-sticky' : '',
);
?>

<header class="<?php echo esc_attr( trim( implode( ' ', $agency_commerce_header_classes ) ) ); ?>" data-sticky-header="<?php echo esc_attr( ! empty( $agency_commerce_header_data['sticky'] ) ? 'true' : 'false' ); ?>">
	<?php require agency_commerce_component_path( 'components/header/announcement.php' ); ?>

	<div class="ac-container ac-site-header__inner">
		<div class="ac-site-header__top">
			<div class="ac-site-branding">
				<?php agency_commerce_render_logo(); ?>
			</div>
			<?php agency_commerce_render_header_controls( $agency_commerce_header_data ); ?>
		</div>

		<nav class="ac-primary-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'agency-commerce' ); ?>">
			<?php agency_commerce_render_menu( 'primary', 'ac-menu ac-menu--desktop' ); ?>
		</nav>
	</div>
</header>

<?php
/**
 * Shared announcement bar.
 *
 * @package AgencyCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
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

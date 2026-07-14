<?php
/**
 * Search form.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php echo esc_html_x( 'Search for:', 'label', 'agency-commerce' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search products', 'placeholder', 'agency-commerce' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	</label>
	<?php if ( agency_commerce_has_woocommerce() ) : ?>
		<input type="hidden" name="post_type" value="product">
	<?php endif; ?>
	<button type="submit" class="search-submit"><?php echo esc_html_x( 'Search', 'submit button', 'agency-commerce' ); ?></button>
</form>

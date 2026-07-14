<?php
/**
 * Empty product-loop state.
 *
 * @package AgencyCommerce
 * @version 10.9.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="woocommerce-info ac-empty-state">
	<h2><?php esc_html_e( 'No products found', 'agency-commerce' ); ?></h2>
	<p><?php esc_html_e( 'Try changing your search terms, sorting, or active filters.', 'agency-commerce' ); ?></p>
</div>

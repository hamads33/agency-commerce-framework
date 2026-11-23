<?php
/**
 * Measure bounded WooCommerce reads and parent-theme card rendering.
 *
 * @package AgencyCommerceCore
 */

if ( ! function_exists( 'agency_commerce_render_product_card' ) ) {
	WP_CLI::error( 'Activate the parent theme or a compatible child theme.' );
}
$query_start = get_num_queries();
$clock       = microtime( true );
$products    = wc_get_products(
	array(
		'limit'   => 24,
		'status'  => 'publish',
		'type'    => 'simple',
		'orderby' => 'ID',
	)
);
$samples = array(
	'products'   => count( $products ),
	'read_sql'   => get_num_queries() - $query_start,
	'read_ms'    => round( ( microtime( true ) - $clock ) * 1000, 2 ),
);
foreach ( array( 'cold', 'warm' ) as $pass ) {
	$query_start = get_num_queries();
	$clock       = microtime( true );
	ob_start();
	foreach ( $products as $product ) {
		agency_commerce_render_product_card( $product );
	}
	$html = ob_get_clean();
	$samples[$pass] = array(
		'sql'   => get_num_queries() - $query_start,
		'ms'    => round( ( microtime( true ) - $clock ) * 1000, 2 ),
		'bytes' => strlen( $html ),
	);
}
WP_CLI::line( wp_json_encode( $samples ) );

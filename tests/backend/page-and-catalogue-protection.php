<?php
/**
 * Verify checkout backup failure and public catalogue visibility in WordPress.
 *
 * @package AgencyCommerceCore
 */

$mode     = new AgencyCommerceCore\Checkout\ModeManager();
$page_id  = wc_get_page_id( 'checkout' );
$original = get_post_field( 'post_content', $page_id );
$fixture  = '<!-- wp:paragraph --><p>Path C:\\fixture</p><!-- /wp:paragraph -->';
$key      = '_agency_commerce_core_latest_block_content_backup';
$old      = get_post_meta( $page_id, $key, true );

wp_update_post( array( 'ID' => $page_id, 'post_content' => wp_slash( $fixture ) ) );
$reject = static function ( $override, $id, $meta_key ) use ( $page_id, $key ) {
	return $page_id === $id && $key === $meta_key ? false : $override;
};
add_filter( 'update_post_metadata', $reject, 10, 3 );
add_filter( 'add_post_metadata', $reject, 10, 3 );
try {
	$mode->apply_mode( 'classic' );
	$preserved = $fixture === get_post_field( 'post_content', $page_id );
} finally {
	remove_filter( 'update_post_metadata', $reject, 10 );
	remove_filter( 'add_post_metadata', $reject, 10 );
}
$mode->apply_mode( 'classic' );
$switched = '[woocommerce_checkout]' === get_post_field( 'post_content', $page_id );
$mode->apply_mode( 'block' );
$restored = $fixture === get_post_field( 'post_content', $page_id );
wp_update_post( array( 'ID' => $page_id, 'post_content' => wp_slash( $original ) ) );
update_post_meta( $page_id, $key, wp_slash( $old ) );

$product = new WC_Product_Simple();
$product->set_name( 'Protected fixture title' );
$product->set_regular_price( '12' );
$product->set_status( 'publish' );
$id = $product->save();
wp_update_post( array( 'ID' => $id, 'post_password' => 'local-fixture' ) );
$request = new WP_REST_Request( 'POST', '/agency-commerce-core/v1/recently-viewed' );
$request->set_param( 'ids', array( $id ) );
$response = rest_do_request( $request );
$product->delete( true );

if ( ! $preserved || ! $switched || ! $restored ) {
	WP_CLI::error( 'Checkout backup failure or exact restoration regression.' );
}
if ( 200 !== $response->get_status() || str_contains( $response->get_data()['html'], 'Protected fixture title' ) ) {
	WP_CLI::error( 'Public recently viewed must exclude password-protected products.' );
}
WP_CLI::success( 'Verified checkout backup failure, exact restoration and protected catalogue filtering.' );

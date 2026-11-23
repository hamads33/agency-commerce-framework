<?php
/**
 * Verify WooCommerce order ownership and framework category mutations.
 *
 * @package AgencyCommerceCore
 */

$owner = wp_insert_user( array( 'user_login' => 'agency-owner-' . wp_generate_uuid4(), 'user_pass' => wp_generate_password(), 'role' => 'customer' ) );
$other = wp_insert_user( array( 'user_login' => 'agency-other-' . wp_generate_uuid4(), 'user_pass' => wp_generate_password(), 'role' => 'customer' ) );
if ( is_wp_error( $owner ) || is_wp_error( $other ) ) {
	WP_CLI::error( 'Unable to create customer fixtures.' );
}
$order = wc_create_order( array( 'customer_id' => $owner ) );
if ( is_wp_error( $order ) ) {
	WP_CLI::error( $order->get_error_message() );
}
wp_set_current_user( $owner );
$allowed = current_user_can( 'view_order', $order->get_id() );
wp_set_current_user( $other );
$denied = ! current_user_can( 'view_order', $order->get_id() );
$request = new WP_REST_Request( 'GET', '/wc/store/v1/order/' . $order->get_id() );
$request->set_param( 'key', $order->get_order_key() );
$response = rest_do_request( $request );
if ( ! $allowed || ! $denied || $response->get_status() < 400 ) {
	WP_CLI::error( 'Cross-customer order access contract failed.' );
}

$term = wp_insert_term( 'Agency authorization ' . wp_generate_uuid4(), 'product_cat' );
if ( is_wp_error( $term ) ) {
	WP_CLI::error( $term->get_error_message() );
}
$id = (int) $term['term_id'];
$key = '_agency_commerce_delivery_message';
$nonce = 'agency_commerce_core_category_meta_nonce';
$action = 'agency_commerce_core_category_meta';
$_POST = array( $nonce => wp_create_nonce( $action ), $key => '<strong>Two days</strong>' );
do_action( 'edited_product_cat', $id );
$unauthorized = ! metadata_exists( 'term', $id, $key );

wp_set_current_user( (int) get_user_by( 'login', 'admin' )->ID );
$_POST[$nonce] = 'invalid';
do_action( 'edited_product_cat', $id );
$invalid = ! metadata_exists( 'term', $id, $key );
$_POST[$nonce] = wp_create_nonce( $action );
do_action( 'edited_product_cat', $id );
$saved = 'Two days' === get_term_meta( $id, $key, true );
$_POST[$key] = array( 'unexpected' );
do_action( 'edited_product_cat', $id );
$preserved = 'Two days' === get_term_meta( $id, $key, true );
wp_delete_term( $id, 'product_cat' );

if ( ! $unauthorized || ! $invalid || ! $saved || ! $preserved ) {
	WP_CLI::error( 'Category authorization or sanitation regression.' );
}
WP_CLI::success( 'Verified order ownership, Store API denial and category authorization and sanitation.' );

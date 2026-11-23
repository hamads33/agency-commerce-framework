<?php
/**
 * Exercise the WooCommerce product save hook in real WordPress user contexts.
 *
 * @package AgencyCommerceCore
 */

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$product_id = (int) get_page_by_path( 'cp3-simple-product', OBJECT, 'product' )->ID;
$admin_id   = (int) get_user_by( 'login', 'admin' )->ID;
$reader_id  = wp_insert_user(
	array(
		'user_login' => 'agency-product-test',
		'user_pass'  => wp_generate_password(),
		'role'       => 'subscriber',
	)
);
if ( is_wp_error( $reader_id ) ) {
	WP_CLI::error( $reader_id->get_error_message() );
}

$delivery_key = '_agency_commerce_delivery_message';
$badge_key    = '_agency_commerce_badge_new';
$nonce_key    = 'agency_commerce_core_product_meta_nonce';
$nonce_action = 'agency_commerce_core_product_meta';
$save         = static function () use ( $product_id ): void {
	$product = wc_get_product( $product_id );
	do_action( 'woocommerce_admin_process_product_object', $product );
	$product->save();
};

wp_set_current_user( $reader_id );
$_POST = array( // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Simulated form submission.
	$delivery_key => 'Rejected change',
	$nonce_key    => wp_create_nonce( $nonce_action ),
);
$save();
if ( '' !== get_post_meta( $product_id, $delivery_key, true ) ) {
	WP_CLI::error( 'A subscriber changed product metadata.' );
}

wp_set_current_user( $admin_id );
$_POST[ $nonce_key ] = 'invalid';
$save();
if ( '' !== get_post_meta( $product_id, $delivery_key, true ) ) {
	WP_CLI::error( 'An invalid nonce allowed product metadata to change.' );
}

$_POST[ $nonce_key ]    = wp_create_nonce( $nonce_action );
$_POST[ $delivery_key ] = '<strong>Ships tomorrow</strong>';
$_POST[ $badge_key ]    = 'unsupported';
$save();
if ( 'Ships tomorrow' !== get_post_meta( $product_id, $delivery_key, true )
	|| 'auto' !== get_post_meta( $product_id, $badge_key, true ) ) {
	WP_CLI::error( 'Authorized product save did not persist sanitized values.' );
}
delete_post_meta( $product_id, $delivery_key );
delete_post_meta( $product_id, $badge_key );
WP_CLI::success( 'Verified WooCommerce product save capabilities, nonce and sanitized persistence.' );

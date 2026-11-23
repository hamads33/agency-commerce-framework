<?php
/**
 * Seed and verify catalogue products through WooCommerce CRUD.
 *
 * @package AgencyCommerceCore
 */

$category = wp_insert_term( 'CP3 Apparel', 'product_cat', array( 'slug' => 'cp3-apparel' ) );
if ( is_wp_error( $category ) ) {
	WP_CLI::error( $category->get_error_message() );
}

foreach ( array( 'Simple', 'Featured', 'Sale', 'Out Of Stock' ) as $kind ) {
	$product = new WC_Product_Simple();
	$product->set_name( "CP3 $kind Product" );
	$product->set_sku( 'CP3-' . strtoupper( sanitize_title( $kind ) ) );
	$product->set_status( 'publish' );
	$product->set_regular_price( '40' );
	$product->set_virtual( true );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( 'Out Of Stock' === $kind ? 0 : 5 );
	$product->set_stock_status( 'Out Of Stock' === $kind ? 'outofstock' : 'instock' );
	$product->set_featured( 'Featured' === $kind );
	$product->set_category_ids( array( (int) $category['term_id'] ) );
	$id = $product->save();
	if ( 'Sale' === $kind ) {
		$product = wc_get_product( $id );
		$product->set_sale_price( '30' );
		$product->save();
	}
	$stored = wc_get_product( $id );
	if ( ! $stored || $stored->get_price() !== ( 'Sale' === $kind ? '30' : '40' )
		|| $stored->get_category_ids() !== array( (int) $category['term_id'] )
		|| $stored->get_stock_quantity() !== $product->get_stock_quantity()
		|| $stored->get_stock_status() !== $product->get_stock_status()
		|| $stored->get_featured() !== $product->get_featured() ) {
		WP_CLI::error( "Catalogue persistence failed for $kind." );
	}
}

$attribute = new WC_Product_Attribute();
$attribute->set_name( 'Size' );
$attribute->set_options( array( 'Small', 'Large' ) );
$attribute->set_visible( true );
$attribute->set_variation( true );

$variable = new WC_Product_Variable();
$variable->set_name( 'CP3 Variable Product' );
$variable->set_status( 'publish' );
$variable->set_attributes( array( $attribute ) );
$parent_id = $variable->save();

foreach ( array( 'Small' => '45', 'Large' => '50' ) as $size => $price ) {
	$variation = new WC_Product_Variation();
	$variation->set_parent_id( $parent_id );
	$variation->set_attributes( array( 'size' => $size ) );
	$variation->set_regular_price( $price );
	$variation->set_virtual( true );
	$id = $variation->save();
	$stored = wc_get_product( $id );
	if ( ! $stored || $parent_id !== $stored->get_parent_id()
		|| $price !== $stored->get_price() || array( 'size' => $size ) !== $stored->get_attributes() ) {
		WP_CLI::error( "Variation persistence failed for $size." );
	}
}
WC_Product_Variable::sync( $parent_id );
$stored = wc_get_product( $parent_id );
if ( 2 !== count( $stored->get_children() ) || 45.0 !== (float) $stored->get_variation_price() ) {
	WP_CLI::error( 'Variable product prices or children failed to sync.' );
}

WP_CLI::success( 'Verified persisted WooCommerce catalogue fixtures.' );

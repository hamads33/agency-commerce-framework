<?php
/**
 * Populate a disposable Forge demo through WooCommerce CRUD.
 *
 * @package AgencyCommerceForge
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}
if ( 'local' !== wp_get_environment_type()
	|| ! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1' ), true )
	|| ! in_array( 'seed-disposable-forge', $args, true )
	|| 'agency-commerce-forge' !== get_stylesheet()
	|| ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'Use a disposable loopback WooCommerce site with Forge active and pass seed-disposable-forge.' );
}
if ( get_option( 'agency_forge_demo_seeded' ) ) {
	WP_CLI::success( 'Forge is already seeded. Existing content was preserved.' );
	return;
}
if ( wc_get_products( array( 'limit' => 1, 'status' => array( 'publish', 'draft', 'private', 'pending', 'trash' ) ) )
	|| wc_get_orders( array( 'limit' => 1 ) ) || (int) get_option( 'page_on_front' ) ) {
	WP_CLI::error( 'An empty catalogue, order store and front page assignment are required.' );
}

add_filter( 'pre_wp_mail', '__return_true' );
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$assets = array(
	'kitchen' => 'Chef knife and walnut board on a dark counter',
	'chef'    => 'Brushed steel chef knife with walnut handle',
	'board'   => 'Rounded walnut serving board',
	'bowls'   => 'Pair of cream stoneware bowls',
	'utility' => 'Small steel utility knife with a dark handle',
);
$images = array();
foreach ( $assets as $name => $alt ) {
	$path = get_stylesheet_directory() . '/assets/' . $name . '.webp';
	$temp = wp_tempnam( $path );
	if ( ! $temp || ! copy( $path, $temp ) ) {
		WP_CLI::error( "Unable to stage $name." );
	}
	$id = media_handle_sideload( array( 'name' => "forge-$name.webp", 'tmp_name' => $temp ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $temp );
		WP_CLI::error( $id->get_error_message() );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	$images[ $name ] = $id;
}
$collections = array(
	'chef'  => array( 'Kitchen knives', 'Balanced profiles for slicing, chopping and daily prep.' ),
	'board' => array( 'Boards & prep', 'Warm-grained surfaces for prep, plating and the table.' ),
	'bowls' => array( 'Tableware', 'Quiet finishes and useful shapes for everyday meals.' ),
);
$terms = array();
foreach ( $collections as $key => list( $label, $description ) ) {
	$term = wp_insert_term( $label, 'product_cat', array( 'description' => $description ) );
	if ( is_wp_error( $term ) ) {
		WP_CLI::error( $term->get_error_message() );
	}
	$terms[ $key ] = (int) $term['term_id'];
	update_term_meta( $terms[ $key ], 'thumbnail_id', $images[ $key ] );
}
$products = array(
	'chef'    => array( 'The Hearth Chef Knife', '149', 'chef', 'Satin stainless steel', 'A gently curved edge and a rounded walnut grip.' ),
	'board'   => array( 'The Grain Serving Board', '78', 'board', 'Walnut', 'A generous surface for bread, fruit and small plates.' ),
	'bowls'   => array( 'The Silt Bowl Pair', '64', 'bowls', 'Glazed stoneware', 'Two tapered bowls with a soft, speckled finish.' ),
	'utility' => array( 'The Ember Utility Knife', '89', 'chef', 'Satin stainless steel', 'A compact kitchen blade for trimming and daily prep.' ),
);
foreach ( $products as $key => list( $name, $price, $category, $material, $description ) ) {
	$variable = 'chef' === $key;
	$product = $variable ? new WC_Product_Variable() : new WC_Product_Simple();
	$product->set_name( $name );
	$product->set_sku( 'FORGE-' . strtoupper( $key ) );
	$product->set_status( 'publish' );
	$product->set_featured( true );
	$product->set_short_description( $description );
	$product->set_description( '<h2>Materials and care</h2><p>' . esc_html( $description ) . ' Clean and dry after use. The imagery and details illustrate a fictional product.</p><h2>Delivery and returns</h2><p>Sample orders are never dispatched.</p>' );
	$product->set_image_id( $images[ $key ] );
	$product->set_category_ids( array( $terms[ $category ] ) );
	$product->set_virtual( true );
	$attribute = new WC_Product_Attribute();
	$attribute->set_name( 'Material' );
	$attribute->set_options( array( $material ) );
	$attribute->set_visible( true );
	$attributes = array( $attribute );
	if ( $variable ) {
		$size = new WC_Product_Attribute();
		$size->set_name( 'Size' );
		$size->set_options( array( '180 mm', '210 mm' ) );
		$size->set_visible( true );
		$size->set_variation( true );
		$attributes[] = $size;
		$product->set_gallery_image_ids( array( $images['kitchen'] ) );
	} else {
		$product->set_regular_price( $price );
		$product->set_manage_stock( true );
		$product->set_stock_quantity( 'utility' === $key ? 0 : 12 );
	}
	$product->set_attributes( $attributes );
	$id = $product->save();
	if ( $variable ) {
		foreach ( array( '180 mm' => '149', '210 mm' => '169' ) as $size_label => $size_price ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $id );
			$variation->set_attributes( array( 'size' => $size_label ) );
			$variation->set_regular_price( $size_price );
			$variation->set_manage_stock( true );
			$variation->set_stock_quantity( 12 );
			$variation->set_virtual( true );
			$variation->save();
		}
		WC_Product_Variable::sync( $id );
	}
}

WC_Install::create_pages();
$shop = esc_url( wc_get_page_permalink( 'shop' ) );
$cards = '';
foreach ( $collections as $key => list( $label ) ) {
	$link = get_term_link( $terms[ $key ] );
	if ( is_wp_error( $link ) ) {
		WP_CLI::error( $link->get_error_message() );
	}
	$cards .= '<a class="forge-collection" href="' . esc_url( $link ) . '">' . wp_get_attachment_image( $images[ $key ], 'large', false, array( 'loading' => 'lazy' ) ) . esc_html( $label ) . ' →</a>';
}
$hero = wp_get_attachment_image( $images['kitchen'], 'full', false, array( 'fetchpriority' => 'high', 'loading' => 'eager' ) );
$board = wp_get_attachment_image( $images['board'], 'large', false, array( 'loading' => 'lazy' ) );
$content = <<<HTML
<!-- wp:html -->
<section class="forge-hero"><div class="forge-hero__copy"><p class="forge-kicker">The essentials collection</p><h1>Good tools. Better rituals.</h1><p>Considered kitchenware for the everyday pleasure of making something well.</p><a class="forge-button" href="{$shop}">Explore the collection →</a></div><figure class="forge-hero__image">{$hero}</figure></section>
<!-- /wp:html -->
<!-- wp:html -->
<section class="forge-section"><p class="forge-kicker">From prep to table</p><h2>Find your essentials</h2><div class="forge-collections">{$cards}</div></section>
<!-- /wp:html -->
<!-- wp:group {"className":"forge-section"} -->
<div class="wp-block-group forge-section">
<!-- wp:heading -->
<h2 class="wp-block-heading">The kitchen edit</h2>
<!-- /wp:heading -->
<!-- wp:shortcode -->
[products limit="4" columns="4" visibility="featured" orderby="popularity"]
<!-- /wp:shortcode -->
</div>
<!-- /wp:group -->
<!-- wp:html -->
<section class="forge-section forge-editorial" id="materials">{$board}<div><p class="forge-kicker">The material story</p><h2>Things worth keeping.</h2><p>Walnut grain, satin steel and softly glazed stoneware set the palette for Forge. Each piece is imagined around the small, repeated gestures of cooking, serving and gathering.</p><a href="{$shop}" class="forge-button">See the kitchen edit →</a></div></section>
<!-- /wp:html -->
<!-- wp:group {"className":"forge-section forge-notes"} -->
<div class="wp-block-group forge-section forge-notes">
<!-- wp:heading -->
<h2 class="wp-block-heading">Studio notes</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>A space for collection releases and material stories. Newsletter signup is disabled in this demonstration.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
HTML;
$page = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Forge home', 'post_content' => wp_slash( $content ) ), true );
if ( is_wp_error( $page ) ) {
	WP_CLI::error( $page->get_error_message() );
}
update_option( 'blogname', 'Forge' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $page );
update_option( 'woocommerce_coming_soon', 'no' );
update_option( 'woocommerce_default_country', 'US:CA' );
update_option( 'woocommerce_currency', 'USD' );
update_option( 'woocommerce_calc_taxes', 'no' );
update_option( 'widget_woocommerce_product_categories', array( 2 => array( 'title' => 'Collections', 'hierarchical' => 1 ), '_multiwidget' => 1 ) );
update_option( 'widget_woocommerce_price_filter', array( 2 => array( 'title' => 'Price range' ), '_multiwidget' => 1 ) );
$sidebars = wp_get_sidebars_widgets();
$sidebars['shop-filters'] = array( 'woocommerce_product_categories-2', 'woocommerce_price_filter-2' );
wp_set_sidebars_widgets( $sidebars );
update_option( 'woocommerce_enable_guest_checkout', 'yes' );
update_option( 'woocommerce_cod_settings', array( 'enabled' => 'yes', 'enable_for_virtual' => 'yes' ) );
update_option(
	'agency_commerce_core_settings',
	array(
		'header' => array( 'announcement_bar' => true, 'announcement' => 'Forge studio · Portfolio demonstration', 'sticky' => true ),
		'shop'   => array( 'card_variant' => 'product-card-minimal', 'filter_layout' => 'drawer' ),
		'footer' => array( 'trust_text' => 'Forge is a fictional demonstration storefront. No orders are fulfilled.' ),
	)
);
$menu = wp_create_nav_menu( 'Forge collections' );
if ( is_wp_error( $menu ) ) {
	WP_CLI::error( $menu->get_error_message() );
}
$add = static function ( string $label, string $url, int $parent = 0 ) use ( $menu ): int {
	$item = wp_update_nav_menu_item( $menu, 0, array( 'menu-item-type' => 'custom', 'menu-item-title' => $label, 'menu-item-url' => $url, 'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent ) );
	if ( is_wp_error( $item ) ) {
		WP_CLI::error( $item->get_error_message() );
	}
	return (int) $item;
};
$parent = $add( 'Shop', $shop );
foreach ( $collections as $key => list( $label ) ) {
	$add( $label, (string) get_term_link( $terms[ $key ] ), $parent );
}
$add( 'Materials', home_url( '/#materials' ) );
set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu, 'footer' => $menu ) );
update_option( 'agency_forge_demo_seeded', 1 );
WP_CLI::success( 'Forge demo content created.' );

<?php
/**
 * Theme setup hooks.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'agency_commerce_setup' );

/**
 * Registers CP1 theme support and foundational capabilities.
 */
function agency_commerce_setup(): void {
	load_theme_textdomain( 'agency-commerce', AGENCY_COMMERCE_THEME_DIR . 'languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support(
		'html5',
		array(
			'caption',
			'comment-form',
			'comment-list',
			'gallery',
			'navigation-widgets',
			'search-form',
			'script',
			'style',
		)
	);

	add_theme_support(
		'woocommerce',
		array(
			'gallery_thumbnail_image_width' => 160,
			'product_grid'                  => array(
				'default_columns' => 4,
				'default_rows'    => 3,
				'max_columns'     => 6,
				'min_columns'     => 2,
			),
			'single_image_width'            => 720,
			'thumbnail_image_width'         => 480,
		)
	);

	add_editor_style( 'style.css' );

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'agency-commerce' ),
			'footer'  => esc_html__( 'Footer Menu', 'agency-commerce' ),
		)
	);
}

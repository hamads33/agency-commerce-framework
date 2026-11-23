<?php
/**
 * Forge child theme assets.
 *
 * @package AgencyCommerceForge
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$theme = wp_get_theme();
		wp_enqueue_style( 'agency-commerce-forge', get_stylesheet_uri(), array( 'agency-commerce' ), $theme->get( 'Version' ) );
	}
);

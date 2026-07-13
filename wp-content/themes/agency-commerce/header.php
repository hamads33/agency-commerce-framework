<?php
/**
 * Minimal document header for CP1 activation safety.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<?php
	$agency_commerce_header_data = agency_commerce_get_header_data();
	require agency_commerce_component_path( 'components/header/header.php' );
	?>

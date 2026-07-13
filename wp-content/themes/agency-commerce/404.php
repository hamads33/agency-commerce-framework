<?php
/**
 * Not found template.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

get_header();
?>

<main id="primary" class="site-main">
	<h1><?php esc_html_e( 'Page not found', 'agency-commerce' ); ?></h1>
	<p><?php esc_html_e( 'The requested page could not be found.', 'agency-commerce' ); ?></p>
</main>

<?php
get_footer();

<?php
/**
 * Base template.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

get_header();
?>

<main id="primary" class="site-main">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			the_title( '<h1>', '</h1>' );
			the_content();
		}
	} else {
		esc_html_e( 'No content found.', 'agency-commerce' );
	}
	?>
</main>

<?php
get_footer();

<?php
/**
 * Front page template.
 *
 * Keeps homepage content fully editable through Gutenberg.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

get_header();
?>

<main id="primary" class="site-main ac-front-page">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			the_content();
		}
	}
	?>
</main>

<?php
get_footer();

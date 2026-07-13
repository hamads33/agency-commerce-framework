<?php
/**
 * Minimal document footer for CP1 activation safety.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

$agency_commerce_footer_data = agency_commerce_get_footer_data();
require agency_commerce_component_path( 'components/footer/footer.php' );
?>
</div>
<?php wp_footer(); ?>
</body>
</html>

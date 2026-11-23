<?php
/**
 * Verify that an interrupted badge migration can be retried without data loss.
 *
 * @package AgencyCommerceCore
 */

$manager = new AgencyCommerceCore\Migrations\SettingsMigrationManager();
$option  = AgencyCommerceCore\Migrations\SettingsMigrationManager::OPTION_NAME;
$key     = AgencyCommerceCore\Products\BadgeManager::CUSTOM_BADGES_META_KEY;
$id      = (int) get_page_by_path( 'cp3-simple-product', OBJECT, 'product' )->ID;
$version = get_option( $option );
$before  = get_option( 'agency_commerce_core_settings' );
$fixture = array_replace_recursive( (array) ( $before ?: array() ), array( 'checkout' => array( 'support_text' => 'Migration audit fixture' ) ) );
$legacy  = array( 'First badge', 'Second badge' );

update_option( 'agency_commerce_core_settings', $fixture );
update_option( $option, 2 );
update_post_meta( $id, $key, $legacy );
$reject_write = static function ( $override, $post_id, $meta_key ) use ( $id, $key ) {
	return $id === $post_id && $key === $meta_key ? false : $override;
};
add_filter( 'update_post_metadata', $reject_write, 10, 3 );
try {
	$manager->maybe_migrate();
} finally {
	remove_filter( 'update_post_metadata', $reject_write, 10 );
}

$preserved = 2 === $manager->installed_version() && $legacy === get_post_meta( $id, $key, true );
$manager->maybe_migrate();
$recovered = 4 === $manager->installed_version()
	&& "First badge\nSecond badge" === get_post_meta( $id, $key, true )
	&& $fixture === get_option( 'agency_commerce_core_settings' );

update_option( $option, 2 );
$reject_marker = static fn ( $new, $old ) => $old;
add_filter( 'pre_update_option_' . $option, $reject_marker, 10, 2 );
try {
	$manager->maybe_migrate();
} finally {
	remove_filter( 'pre_update_option_' . $option, $reject_marker, 10 );
}
$marker_preserved = 2 === $manager->installed_version();
$manager->maybe_migrate();
$marker_recovered = 4 === $manager->installed_version();

delete_post_meta( $id, $key );
update_option( $option, $version );
if ( false === $before ) {
	delete_option( 'agency_commerce_core_settings' );
} else {
	update_option( 'agency_commerce_core_settings', $before );
}
if ( ! $preserved || ! $recovered || ! $marker_preserved || ! $marker_recovered ) {
	WP_CLI::error( 'Migration advanced after a failed write or failed to recover on retry.' );
}
WP_CLI::success( 'Badge migration preserves the prior schema marker on failure and recovers on retry.' );

<?php
/**
 * Third settings schema migration.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Migrations;

use AgencyCommerceCore\Products\BadgeManager;

/**
 * Migrates CP4B custom badge metadata to canonical text storage.
 */
final class Version3 implements SettingsMigrationInterface {
	/**
	 * Returns the schema version this migration upgrades to.
	 */
	public function version(): int {
		return 3;
	}

	/**
	 * Runs the migration.
	 */
	public function run(): void {
		$product_ids = get_posts(
			array(
				'fields'         => 'ids',
				'meta_key'       => BadgeManager::CUSTOM_BADGES_META_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Bounded one-time migration.
				'post_status'    => 'any',
				'post_type'      => 'product',
				'posts_per_page' => -1,
			)
		);

		foreach ( $product_ids as $product_id ) {
			$raw = get_post_meta( (int) $product_id, BadgeManager::CUSTOM_BADGES_META_KEY, true );

			if ( is_array( $raw ) ) {
				update_post_meta(
					(int) $product_id,
					BadgeManager::CUSTOM_BADGES_META_KEY,
					BadgeManager::sanitize_badge_labels_for_storage( $raw )
				);
			}
		}
	}
}

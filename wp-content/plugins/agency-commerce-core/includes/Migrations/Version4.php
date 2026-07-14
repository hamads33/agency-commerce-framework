<?php
/**
 * Fourth settings schema migration.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Migrations;

/**
 * Marks the CP5 commerce-flow settings schema.
 */
final class Version4 implements SettingsMigrationInterface {
	/**
	 * Returns the schema version this migration upgrades to.
	 */
	public function version(): int {
		return 4;
	}

	/**
	 * Runs the migration.
	 *
	 * CP5 adds cart, checkout, homepage, and recently-viewed settings. Existing
	 * installations receive those keys from defaults, so no stored values are
	 * rewritten here.
	 */
	public function run(): void {}
}

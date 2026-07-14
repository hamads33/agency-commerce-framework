<?php
/**
 * Settings migration contract.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Migrations;

/**
 * Defines a single settings schema migration.
 */
interface SettingsMigrationInterface {
	/**
	 * Returns the schema version this migration upgrades to.
	 */
	public function version(): int;

	/**
	 * Runs the migration.
	 */
	public function run(): void;
}

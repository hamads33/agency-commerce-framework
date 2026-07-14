<?php
/**
 * Second settings schema migration.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Migrations;

/**
 * Marks the CP4B settings schema.
 */
final class Version2 implements SettingsMigrationInterface {
	/**
	 * Returns the schema version this migration upgrades to.
	 */
	public function version(): int {
		return 2;
	}

	/**
	 * Runs the migration.
	 *
	 * CP4B extends the existing settings schema with new framework-only fields,
	 * but existing installations already receive safe defaults through the
	 * memoized settings helper layer. This migration therefore only marks the
	 * new schema version.
	 */
	public function run(): void {}
}

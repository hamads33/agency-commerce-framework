<?php
/**
 * Initial settings schema migration.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Migrations;

/**
 * Marks the first published settings schema.
 */
final class Version1 implements SettingsMigrationInterface {
	/**
	 * Returns the schema version this migration upgrades to.
	 */
	public function version(): int {
		return 1;
	}

	/**
	 * Runs the migration.
	 *
	 * Version 1 is intentionally a no-op because CP4A already introduced the
	 * current settings option shape. Existing installations must keep their
	 * option values untouched when schema versioning is introduced.
	 */
	public function run(): void {}
}

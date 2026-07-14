<?php
/**
 * Settings schema migration manager.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Migrations;

/**
 * Runs ordered settings schema migrations.
 */
final class SettingsMigrationManager {
	/**
	 * Schema version option name.
	 */
	public const OPTION_NAME = 'agency_commerce_core_settings_schema_version';

	/**
	 * Runs pending migrations and stores the installed schema version.
	 */
	public function maybe_migrate(): void {
		$installed_version = $this->installed_version();
		$target_version    = (int) AGENCY_COMMERCE_CORE_SETTINGS_SCHEMA_VERSION;

		if ( $installed_version >= $target_version ) {
			return;
		}

		foreach ( $this->migrations() as $migration ) {
			$version = $migration->version();

			if ( $version <= $installed_version || $version > $target_version ) {
				continue;
			}

			$migration->run();
			update_option( self::OPTION_NAME, $version, false );
			agency_commerce_core_flush_settings_cache();
		}
	}

	/**
	 * Returns the installed schema version.
	 */
	public function installed_version(): int {
		return max( 0, (int) get_option( self::OPTION_NAME, 0 ) );
	}

	/**
	 * Returns registered migrations in ascending version order.
	 *
	 * @return SettingsMigrationInterface[]
	 */
	private function migrations(): array {
		$migrations = array(
			new Version1(),
			new Version2(),
			new Version3(),
		);

		usort(
			$migrations,
			static function ( SettingsMigrationInterface $a, SettingsMigrationInterface $b ): int {
				return $a->version() <=> $b->version();
			}
		);

		return $migrations;
	}
}

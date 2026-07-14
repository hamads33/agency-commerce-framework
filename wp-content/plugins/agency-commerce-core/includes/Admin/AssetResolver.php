<?php
/**
 * Admin build asset resolver.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Admin;

/**
 * Reads generated build metadata for admin assets.
 */
final class AssetResolver {
	/**
	 * Returns generated admin asset metadata.
	 *
	 * @param string $entry Build entry name.
	 * @return array<string, mixed>
	 */
	public static function build_asset( string $entry ): array {
		$entry      = sanitize_key( $entry );
		$build_dir  = AGENCY_COMMERCE_CORE_PATH . 'build/';
		$build_url  = AGENCY_COMMERCE_CORE_URL . 'build/';
		$asset_file = $build_dir . $entry . '.asset.php';
		$style      = $build_dir . $entry . '.css';
		$metadata   = array(
			'dependencies' => array(),
			'version'      => AGENCY_COMMERCE_CORE_VERSION,
		);

		if ( file_exists( $asset_file ) ) {
			$loaded = require $asset_file;

			if ( is_array( $loaded ) ) {
				$metadata = array_replace( $metadata, $loaded );
			}
		}

		return array(
			'dependencies' => is_array( $metadata['dependencies'] ?? null ) ? $metadata['dependencies'] : array(),
			'version'      => (string) ( $metadata['version'] ?? AGENCY_COMMERCE_CORE_VERSION ),
			'style_url'    => file_exists( $style ) ? $build_url . $entry . '.css' : '',
		);
	}

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {}
}

<?php
/**
 * WooCommerce Blocks checkout integration.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Checkout;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Provides framework data/scripts to WooCommerce block checkout.
 */
final class BlockIntegration implements IntegrationInterface {
	/**
	 * Returns the integration name.
	 */
	public function get_name(): string {
		return 'agency-commerce-core-checkout';
	}

	/**
	 * Initializes the integration.
	 */
	public function initialize(): void {
		$asset = $this->asset();

		if ( empty( $asset['url'] ) ) {
			return;
		}

		wp_register_script(
			'agency-commerce-core-checkout-blocks',
			(string) $asset['url'],
			(array) $asset['dependencies'],
			(string) $asset['version'],
			true
		);
	}

	/**
	 * Returns frontend script handles.
	 *
	 * @return string[]
	 */
	public function get_script_handles(): array {
		return array( 'agency-commerce-core-checkout-blocks' );
	}

	/**
	 * Returns editor script handles.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles(): array {
		return array( 'agency-commerce-core-checkout-blocks' );
	}

	/**
	 * Returns data exposed to block scripts.
	 *
	 * @return array<string, mixed>
	 */
	public function get_script_data(): array {
		return array(
			'supportText'        => (string) agency_commerce_core_get_setting( 'checkout.support_text', '' ),
			'trustText'          => (string) agency_commerce_core_get_setting( 'checkout.trust_text', '' ),
			'minimumOrderAmount' => (int) agency_commerce_core_get_setting( 'checkout.minimum_order_amount', 0 ),
		);
	}

	/**
	 * Returns built checkout script metadata.
	 *
	 * @return array<string, mixed>
	 */
	private function asset(): array {
		$build_dir  = AGENCY_COMMERCE_CORE_PATH . 'build/';
		$build_url  = AGENCY_COMMERCE_CORE_URL . 'build/';
		$asset_file = $build_dir . 'checkout.asset.php';
		$script     = $build_dir . 'checkout.js';
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
			'url'          => file_exists( $script ) ? $build_url . 'checkout.js' : '',
		);
	}
}

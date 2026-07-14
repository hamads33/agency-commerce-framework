<?php
/**
 * Checkout block integration registrar.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Checkout;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry;

/**
 * Registers WooCommerce block checkout integration.
 */
final class BlockIntegrationRegistrar {
	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_action( 'woocommerce_blocks_checkout_block_registration', array( $this, 'register_integration' ) );
	}

	/**
	 * Registers the integration with WooCommerce Blocks.
	 *
	 * @param IntegrationRegistry $registry Integration registry.
	 */
	public function register_integration( IntegrationRegistry $registry ): void {
		if ( ! interface_exists( '\Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface' ) ) {
			return;
		}

		$registry->register( new BlockIntegration() );
	}
}

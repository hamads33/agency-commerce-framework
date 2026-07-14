<?php
/**
 * Preset admin actions.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Admin;

use AgencyCommerceCore\Presets\Manager as PresetManager;

/**
 * Handles preset apply and restore requests.
 */
final class PresetActions {
	/**
	 * Registers admin post handlers.
	 */
	public function register(): void {
		add_action( 'admin_post_agency_commerce_core_apply_preset', array( $this, 'handle_apply' ) );
		add_action( 'admin_post_agency_commerce_core_restore_preset', array( $this, 'handle_restore' ) );
	}

	/**
	 * Handles preset application.
	 */
	public function handle_apply(): void {
		$this->require_capability();

		$preset = isset( $_POST['preset'] ) ? sanitize_key( wp_unslash( $_POST['preset'] ) ) : '';
		$nonce  = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'agency_commerce_core_apply_preset_' . $preset ) ) {
			wp_safe_redirect( $this->redirect_url( array( 'preset_action' => 'invalid' ) ) );
			exit;
		}

		if ( ! PresetManager::apply( $preset ) ) {
			wp_safe_redirect( $this->redirect_url( array( 'preset_action' => 'failed' ) ) );
			exit;
		}

		wp_safe_redirect(
			$this->redirect_url(
				array(
					'preset_action' => 'applied',
					'preset'        => $preset,
				)
			)
		);
		exit;
	}

	/**
	 * Handles preset rollback.
	 */
	public function handle_restore(): void {
		$this->require_capability();

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'agency_commerce_core_restore_preset' ) ) {
			wp_safe_redirect( $this->redirect_url( array( 'preset_action' => 'invalid' ) ) );
			exit;
		}

		if ( ! PresetManager::restore_previous() ) {
			wp_safe_redirect( $this->redirect_url( array( 'preset_action' => 'failed' ) ) );
			exit;
		}

		wp_safe_redirect( $this->redirect_url( array( 'preset_action' => 'restored' ) ) );
		exit;
	}

	/**
	 * Requires admin capability.
	 */
	private function require_capability(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to manage presets.', 'agency-commerce-core' ) );
		}
	}

	/**
	 * Returns the settings page redirect URL.
	 *
	 * @param array<string, string> $args Query args.
	 */
	private function redirect_url( array $args = array() ): string {
		$url = add_query_arg(
			array_merge(
				array(
					'page' => 'agency-commerce-framework',
					'tab'  => 'presets',
				),
				$args
			),
			admin_url( 'admin.php' )
		);

		return $url;
	}
}

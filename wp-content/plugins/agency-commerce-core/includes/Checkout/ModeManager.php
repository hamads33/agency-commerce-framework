<?php
/**
 * Cart and checkout mode switching.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Checkout;

use AgencyCommerceCore\Settings\Registry;

/**
 * Safely switches WooCommerce Cart/Checkout pages between block and classic modes.
 */
final class ModeManager {
	private const BACKUP_META_KEY = '_agency_commerce_core_block_content_backup';

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_action( 'update_option_' . Registry::OPTION_NAME, array( $this, 'maybe_switch_mode' ), 10, 2 );
		add_action( 'init', array( $this, 'ensure_configured_mode' ), 30 );
	}

	/**
	 * Reacts to settings updates.
	 *
	 * @param mixed $old_value Previous option value.
	 * @param mixed $new_value New option value.
	 */
	public function maybe_switch_mode( $old_value, $new_value ): void {
		$old_mode = is_array( $old_value ) ? (string) ( $old_value['checkout']['mode'] ?? 'block' ) : 'block';
		$new_mode = is_array( $new_value ) ? (string) ( $new_value['checkout']['mode'] ?? 'block' ) : 'block';

		if ( $old_mode === $new_mode ) {
			return;
		}

		$this->apply_mode( $new_mode );
	}

	/**
	 * Ensures the configured mode is reflected when settings already exist.
	 */
	public function ensure_configured_mode(): void {
		$this->apply_mode( (string) agency_commerce_core_get_setting( 'checkout.mode', 'block' ), false );
	}

	/**
	 * Applies a mode to the Cart and Checkout pages.
	 *
	 * @param string $mode Checkout mode.
	 * @param bool   $force Whether to update even when content appears aligned.
	 */
	public function apply_mode( string $mode, bool $force = true ): void {
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return;
		}

		$pages = array(
			'cart'     => array(
				'id'        => (int) wc_get_page_id( 'cart' ),
				'shortcode' => '[woocommerce_cart]',
			),
			'checkout' => array(
				'id'        => (int) wc_get_page_id( 'checkout' ),
				'shortcode' => '[woocommerce_checkout]',
			),
		);

		foreach ( $pages as $page ) {
			$page_id = (int) $page['id'];

			if ( $page_id <= 0 ) {
				continue;
			}

			if ( 'classic' === $mode ) {
				$this->switch_page_to_classic( $page_id, (string) $page['shortcode'], $force );
			} else {
				$this->restore_page_to_block( $page_id, $force );
			}
		}
	}

	/**
	 * Writes shortcode content after creating a backup.
	 *
	 * @param int    $page_id Page ID.
	 * @param string $shortcode Shortcode content.
	 * @param bool   $force Whether to force update.
	 */
	private function switch_page_to_classic( int $page_id, string $shortcode, bool $force ): void {
		$post = get_post( $page_id );

		if ( ! $post ) {
			return;
		}

		if ( ! get_post_meta( $page_id, self::BACKUP_META_KEY, true ) ) {
			update_post_meta( $page_id, self::BACKUP_META_KEY, (string) $post->post_content );
		}

		if ( ! $force && trim( (string) $post->post_content ) === $shortcode ) {
			return;
		}

		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $shortcode,
			)
		);
	}

	/**
	 * Restores backed-up block content.
	 *
	 * @param int  $page_id Page ID.
	 * @param bool $force Whether to force update.
	 */
	private function restore_page_to_block( int $page_id, bool $force ): void {
		$backup = get_post_meta( $page_id, self::BACKUP_META_KEY, true );

		if ( ! is_string( $backup ) || '' === trim( $backup ) ) {
			return;
		}

		$post = get_post( $page_id );

		if ( ! $post || ( ! $force && trim( (string) $post->post_content ) === trim( $backup ) ) ) {
			return;
		}

		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $backup,
			)
		);
		delete_post_meta( $page_id, self::BACKUP_META_KEY );
	}
}

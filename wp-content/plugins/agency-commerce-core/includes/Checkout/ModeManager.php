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
	private const BACKUP_META_KEY  = '_agency_commerce_core_block_content_backup';
	private const RESTORE_META_KEY = '_agency_commerce_core_latest_block_content_backup';
	private const CLASSIC_META_KEY = '_agency_commerce_core_classic_content_backup';

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
		$old_mode = $this->normalize_mode( $old_mode );
		$new_mode = $this->normalize_mode( $new_mode );

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

		$mode = $this->normalize_mode( $mode );

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

		$current_content = (string) $post->post_content;

		if ( ! $this->contents_match( $current_content, $shortcode ) ) {
			if ( ! metadata_exists( 'post', $page_id, self::BACKUP_META_KEY ) ) {
				add_post_meta( $page_id, self::BACKUP_META_KEY, wp_slash( $current_content ), true );
				if ( ! metadata_exists( 'post', $page_id, self::BACKUP_META_KEY ) ) {
					return;
				}
			}

			if ( ! $this->store_page_content( $page_id, self::RESTORE_META_KEY, $current_content ) ) {
				return;
			}
		} elseif ( ! $force ) {
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
		$key    = self::RESTORE_META_KEY;
		$backup = get_post_meta( $page_id, $key, true );

		if ( ! is_string( $backup ) || ! metadata_exists( 'post', $page_id, $key ) ) {
			$key    = self::BACKUP_META_KEY;
			$backup = get_post_meta( $page_id, $key, true );
		}

		if ( ! is_string( $backup ) || ! metadata_exists( 'post', $page_id, $key ) ) {
			return;
		}

		$post = get_post( $page_id );

		if ( ! $post ) {
			return;
		}

		$current_content = (string) $post->post_content;

		if ( ! $force && ! $this->contents_match( $current_content, $backup ) && ! $this->contains_woocommerce_shortcode( $current_content ) ) {
			return;
		}

		if ( ! $this->contents_match( $current_content, $backup ) && ! $this->contains_woocommerce_shortcode( $current_content ) ) {
			if ( ! $this->store_page_content( $page_id, self::CLASSIC_META_KEY, $current_content ) ) {
				return;
			}
		}

		if ( ! $force && $this->contents_match( $current_content, $backup ) ) {
			return;
		}

		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => wp_slash( $backup ),
			)
		);
	}

	/**
	 * Confirms a recoverable snapshot before replacing a page's content.
	 *
	 * @param int    $page_id Configured WooCommerce page.
	 * @param string $key Backup metadata key.
	 * @param string $content Unslashed post content.
	 */
	private function store_page_content( int $page_id, string $key, string $content ): bool {
		return update_post_meta( $page_id, $key, wp_slash( $content ) )
			|| ( metadata_exists( 'post', $page_id, $key ) && get_post_meta( $page_id, $key, true ) === $content );
	}

	/**
	 * Normalizes checkout modes.
	 *
	 * @param string $mode Mode.
	 */
	private function normalize_mode( string $mode ): string {
		return 'classic' === $mode ? 'classic' : 'block';
	}

	/**
	 * Compares post content without whitespace noise.
	 *
	 * @param string $left First content value.
	 * @param string $right Second content value.
	 */
	private function contents_match( string $left, string $right ): bool {
		return trim( $left ) === trim( $right );
	}

	/**
	 * Detects WooCommerce shortcode page content.
	 *
	 * @param string $content Page content.
	 */
	private function contains_woocommerce_shortcode( string $content ): bool {
		return has_shortcode( $content, 'woocommerce_cart' ) || has_shortcode( $content, 'woocommerce_checkout' );
	}
}

<?php
/**
 * Recently viewed products.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\RecentlyViewed;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Registers privacy-conscious recently viewed product behavior.
 */
final class Manager {
	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'agency_commerce_after_single_product_summary_extensions', array( $this, 'render_product_section' ) );
	}

	/**
	 * Enqueues recently viewed assets only where useful.
	 */
	public function enqueue_assets(): void {
		if ( ! (bool) agency_commerce_core_get_setting( 'recently_viewed.enabled', true ) || ! function_exists( 'is_woocommerce' ) ) {
			return;
		}

		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_front_page() ) {
			return;
		}

		$asset = $this->asset();

		if ( empty( $asset['url'] ) ) {
			return;
		}

		wp_enqueue_script(
			'agency-commerce-core-recently-viewed',
			(string) $asset['url'],
			(array) $asset['dependencies'],
			(string) $asset['version'],
			true
		);

		wp_localize_script(
			'agency-commerce-core-recently-viewed',
			'agencyCommerceRecentlyViewed',
			array(
				'endpoint'  => esc_url_raw( rest_url( 'agency-commerce-core/v1/recently-viewed' ) ),
				'productId' => is_product() ? get_the_ID() : 0,
				'limit'     => (int) agency_commerce_core_get_setting( 'recently_viewed.count', 4 ),
			)
		);
	}

	/**
	 * Registers REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			'agency-commerce-core/v1',
			'/recently-viewed',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_recently_viewed' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'ids' => array(
						'type'    => 'array',
						'items'   => array( 'type' => 'integer' ),
						'default' => array(),
					),
				),
			)
		);
	}

	/**
	 * Returns product card markup for recently viewed product IDs.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public function rest_recently_viewed( WP_REST_Request $request ): WP_REST_Response {
		$ids   = array_map( 'absint', (array) $request->get_param( 'ids' ) );
		$limit = max( 1, min( 12, (int) agency_commerce_core_get_setting( 'recently_viewed.count', 4 ) ) );
		$ids   = array_slice( array_values( array_unique( array_filter( $ids ) ) ), 0, $limit );
		$html  = $this->render_product_cards( $ids );

		return new WP_REST_Response(
			array(
				'html' => $html,
			)
		);
	}

	/**
	 * Renders a recently viewed section on product pages.
	 */
	public function render_product_section(): void {
		if ( ! (bool) agency_commerce_core_get_setting( 'recently_viewed.enabled', true ) ) {
			return;
		}

		?>
		<section class="ac-recently-viewed" data-agency-commerce-recently-viewed hidden>
			<h2><?php esc_html_e( 'Recently viewed', 'agency-commerce-core' ); ?></h2>
			<div class="ac-recently-viewed__grid" data-agency-commerce-recently-viewed-items></div>
		</section>
		<?php
	}

	/**
	 * Renders recently viewed placeholder markup for dynamic block use.
	 */
	public static function render_block(): string {
		if ( ! (bool) agency_commerce_core_get_setting( 'recently_viewed.enabled', true ) ) {
			return '';
		}

		ob_start();
		?>
		<section class="ac-recently-viewed" data-agency-commerce-recently-viewed hidden>
			<h2><?php esc_html_e( 'Recently viewed', 'agency-commerce-core' ); ?></h2>
			<div class="ac-recently-viewed__grid" data-agency-commerce-recently-viewed-items></div>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Renders available products.
	 *
	 * @param int[] $ids Product IDs.
	 */
	private function render_product_cards( array $ids ): string {
		if ( empty( $ids ) || ! function_exists( 'wc_get_product' ) ) {
			return '';
		}

		ob_start();
		echo '<ul class="products columns-' . esc_attr( (string) min( 4, max( 1, count( $ids ) ) ) ) . '">';

		foreach ( $ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}

			if ( function_exists( 'agency_commerce_render_product_card' ) ) {
				agency_commerce_render_product_card( $product );
			} else {
				$this->render_fallback_card( $product );
			}
		}

		echo '</ul>';

		return (string) ob_get_clean();
	}

	/**
	 * Renders a simple product card when the parent theme is unavailable.
	 *
	 * @param \WC_Product $product Product.
	 */
	private function render_fallback_card( \WC_Product $product ): void {
		?>
		<li class="product ac-product-card-item">
			<article class="ac-product-card">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?>
					<h3><?php echo esc_html( $product->get_name() ); ?></h3>
				</a>
				<p class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
			</article>
		</li>
		<?php
	}

	/**
	 * Returns built script metadata.
	 *
	 * @return array<string, mixed>
	 */
	private function asset(): array {
		$build_dir  = AGENCY_COMMERCE_CORE_PATH . 'build/';
		$build_url  = AGENCY_COMMERCE_CORE_URL . 'build/';
		$asset_file = $build_dir . 'recently-viewed.asset.php';
		$script     = $build_dir . 'recently-viewed.js';
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
			'url'          => file_exists( $script ) ? $build_url . 'recently-viewed.js' : '',
		);
	}
}

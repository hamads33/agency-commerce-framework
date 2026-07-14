<?php
/**
 * Catalog, product-card, archive, and single-product helpers.
 *
 * @package AgencyCommerce
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'loop_shop_columns', 'agency_commerce_loop_shop_columns' );
add_action( 'pre_get_posts', 'agency_commerce_configure_product_queries' );

/**
 * Returns true when WooCommerce runtime APIs are available.
 */
function agency_commerce_has_woocommerce(): bool {
	return function_exists( 'WC' ) && class_exists( 'WooCommerce' );
}

if ( ! function_exists( 'agency_commerce_get_product_badges' ) ) {
	/**
	 * Returns product badges for the parent-theme fallback.
	 *
	 * @param WC_Product $product Product object.
	 * @return array<int, array<string, string>>
	 */
	function agency_commerce_get_product_badges( WC_Product $product ): array {
		$badges = array();

		if ( (bool) agency_commerce_get_setting( 'shop.show_sale_badge', true ) && $product->is_on_sale() ) {
			$badges[] = array(
				'class' => 'is-sale',
				'label' => esc_html__( 'Sale', 'agency-commerce' ),
			);
		}

		if ( (bool) agency_commerce_get_setting( 'shop.show_stock_badge', true ) && ! $product->is_in_stock() ) {
			$badges[] = array(
				'class' => 'is-out-of-stock',
				'label' => esc_html__( 'Out of stock', 'agency-commerce' ),
			);
		}

		if ( (bool) agency_commerce_get_setting( 'shop.show_featured_badge', true ) && $product->is_featured() ) {
			$badges[] = array(
				'class' => 'is-featured',
				'label' => esc_html__( 'Featured', 'agency-commerce' ),
			);
		}

		return $badges;
	}
}

if ( ! function_exists( 'agency_commerce_render_product_badges' ) ) {
	/**
	 * Renders product badges for the parent-theme fallback.
	 *
	 * @param array<int, array<string, string>> $badges Badge definitions.
	 * @param string                            $wrapper_class Wrapper class.
	 */
	function agency_commerce_render_product_badges( array $badges, string $wrapper_class = 'ac-product-badges' ): void {
		if ( empty( $badges ) ) {
			return;
		}

		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>" aria-label="<?php esc_attr_e( 'Product badges', 'agency-commerce' ); ?>">
			<?php foreach ( $badges as $badge ) : ?>
				<span class="ac-product-badge <?php echo esc_attr( (string) $badge['class'] ); ?>"><?php echo esc_html( (string) $badge['label'] ); ?></span>
			<?php endforeach; ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'agency_commerce_render_delivery_message' ) ) {
	/**
	 * Parent-theme fallback for delivery messaging.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	function agency_commerce_render_delivery_message( ?WC_Product $product = null ): void {
		unset( $product );

		$message = (string) agency_commerce_get_setting( 'product_page.delivery_message', '' );

		if ( '' === trim( $message ) || ! (bool) agency_commerce_get_setting( 'product_page.show_delivery_message', true ) ) {
			return;
		}

		?>
		<div class="ac-product-delivery">
			<p class="ac-product-delivery__label"><?php esc_html_e( 'Delivery', 'agency-commerce' ); ?></p>
			<p class="ac-product-delivery__message"><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'agency_commerce_render_whatsapp_enquiry_button' ) ) {
	/**
	 * Parent-theme fallback for WhatsApp enquiry rendering.
	 *
	 * @param WC_Product|null $product Product object.
	 */
	function agency_commerce_render_whatsapp_enquiry_button( ?WC_Product $product = null ): void {
		unset( $product );
	}
}

/**
 * Returns the configured product-card variant.
 */
function agency_commerce_get_product_card_variant(): string {
	return agency_commerce_sanitize_variant(
		(string) agency_commerce_get_setting( 'shop.card_variant', 'product-card-classic' ),
		array( 'product-card-classic', 'product-card-minimal', 'product-card-compact' ),
		'product-card-classic'
	);
}

/**
 * Returns the configured product-page layout variant.
 */
function agency_commerce_get_product_layout_variant(): string {
	return agency_commerce_sanitize_variant(
		(string) agency_commerce_get_setting( 'product_page.layout', 'product-layout-standard' ),
		array( 'product-layout-standard', 'product-layout-sticky' ),
		'product-layout-standard'
	);
}

/**
 * Returns configured products-per-row constrained to WooCommerce-safe bounds.
 */
function agency_commerce_get_products_per_row(): int {
	$columns = (int) agency_commerce_get_setting( 'shop.products_per_row', 4 );

	return max( 1, min( 6, $columns ) );
}

/**
 * Returns configured products-per-page constrained to practical bounds.
 */
function agency_commerce_get_products_per_page(): int {
	$per_page = (int) agency_commerce_get_setting( 'shop.products_per_page', 12 );

	return max( 1, min( 48, $per_page ) );
}

/**
 * Filters WooCommerce loop columns.
 */
function agency_commerce_loop_shop_columns(): int {
	return agency_commerce_get_products_per_row();
}

/**
 * Applies product archive/search query defaults without replacing WooCommerce query logic.
 *
 * @param WP_Query $query Query object.
 */
function agency_commerce_configure_product_queries( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! agency_commerce_has_woocommerce() ) {
		return;
	}

	if ( $query->is_search() ) {
		$post_type            = $query->get( 'post_type' );
		$is_product_search    = 'product' === $post_type || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) );
		$force_product_search = (bool) agency_commerce_get_setting( 'developer.force_product_search', false );

		if ( $is_product_search || $force_product_search ) {
			$query->set( 'post_type', 'product' );
			$query->set( 'posts_per_page', agency_commerce_get_products_per_page() );
		}

		return;
	}

	if ( $query->is_post_type_archive( 'product' ) || $query->is_tax( get_object_taxonomies( 'product' ) ) ) {
		$query->set( 'posts_per_page', agency_commerce_get_products_per_page() );
	}
}

/**
 * Returns true when catalog drawer assets should be loaded.
 */
function agency_commerce_should_load_catalog_assets(): bool {
	if ( ! agency_commerce_has_woocommerce() ) {
		return false;
	}

	if ( ! (bool) agency_commerce_get_setting( 'performance.load_assets_conditionally', true ) ) {
		return true;
	}

	return is_shop() || is_product_taxonomy() || is_product() || is_search();
}

/**
 * Builds the shared product-card data model used by every variant.
 *
 * @param WC_Product $product Product object.
 * @return array<string, mixed>
 */
function agency_commerce_prepare_product_card_data( WC_Product $product ): array {
	$product_id    = $product->get_id();
	$category_html = '';

	if ( (bool) agency_commerce_get_setting( 'shop.show_product_cat', true ) ) {
		$terms = get_the_terms( $product_id, 'product_cat' );

		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$term = reset( $terms );
			$link = get_term_link( $term );

			if ( ! is_wp_error( $link ) ) {
				$category_html = sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( $link ),
					esc_html( $term->name )
				);
			}
		}
	}

	$gallery_ids      = $product->get_gallery_image_ids();
	$hover_image_html = '';

	if ( (bool) agency_commerce_get_setting( 'shop.enable_hover_image', true ) && ! empty( $gallery_ids ) ) {
		$hover_image_html = wp_get_attachment_image(
			(int) $gallery_ids[0],
			'woocommerce_thumbnail',
			false,
			array(
				'class'   => 'ac-product-card__image ac-product-card__image--hover',
				'loading' => 'lazy',
			)
		);
	}

	$badges = agency_commerce_get_product_badges( $product );

	$can_quick_add = (bool) agency_commerce_get_setting( 'shop.show_quick_add', true )
		&& $product->is_type( 'simple' )
		&& $product->is_purchasable()
		&& $product->is_in_stock()
		&& $product->supports( 'ajax_add_to_cart' );

	$action = array(
		'type'       => $can_quick_add ? 'quick-add' : 'view-product',
		'url'        => $can_quick_add ? $product->add_to_cart_url() : get_permalink( $product_id ),
		'text'       => $can_quick_add ? $product->add_to_cart_text() : esc_html__( 'View product', 'agency-commerce' ),
		'aria_label' => $can_quick_add ? $product->add_to_cart_description() : sprintf(
			/* translators: %s: Product title. */
			esc_html__( 'View %s', 'agency-commerce' ),
			$product->get_name()
		),
		'class'      => $can_quick_add ? 'button product_type_simple add_to_cart_button ajax_add_to_cart' : 'button ac-button--secondary',
	);

	return array(
		'id'               => $product_id,
		'permalink'        => get_permalink( $product_id ),
		'title'            => $product->get_name(),
		'image_html'       => $product->get_image(
			'woocommerce_thumbnail',
			array(
				'class'   => 'ac-product-card__image ac-product-card__image--primary',
				'loading' => 'lazy',
			)
		),
		'hover_image_html' => $hover_image_html,
		'price_html'       => $product->get_price_html(),
		'regular_price'    => $product->get_regular_price(),
		'sale_price'       => $product->get_sale_price(),
		'badges'           => $badges,
		'category_html'    => $category_html,
		'rating_html'      => (bool) agency_commerce_get_setting( 'shop.show_rating', true ) ? wc_get_rating_html( $product->get_average_rating(), $product->get_rating_count() ) : '',
		'action'           => $action,
		'sku'              => $product->get_sku(),
		'is_in_stock'      => $product->is_in_stock(),
		'is_featured'      => $product->is_featured(),
		'is_on_sale'       => $product->is_on_sale(),
		'product'          => $product,
	);
}

/**
 * Renders the selected product-card component for a product.
 *
 * @param WC_Product|null $product Product object.
 */
function agency_commerce_render_product_card( ?WC_Product $product = null ): void {
	if ( ! agency_commerce_has_woocommerce() ) {
		return;
	}

	if ( null === $product ) {
		global $product;
	}

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$agency_commerce_product_card_data    = agency_commerce_prepare_product_card_data( $product );
	$agency_commerce_product_card_variant = agency_commerce_get_product_card_variant();
	$agency_commerce_product_card_file    = agency_commerce_component_path( 'components/product-card/' . $agency_commerce_product_card_variant . '.php' );

	if ( ! file_exists( $agency_commerce_product_card_file ) ) {
		$agency_commerce_product_card_file = agency_commerce_component_path( 'components/product-card/product-card-classic.php' );
	}

	require $agency_commerce_product_card_file;
}

/**
 * Fires a WooCommerce loop hook while preserving the framework card markup.
 *
 * WooCommerce's default callbacks render their own card pieces on these hooks.
 * The framework temporarily removes those callbacks so third-party extensions
 * keep their standard hook positions without duplicating core card output.
 *
 * @param string $hook Hook name.
 */
function agency_commerce_do_product_loop_hook( string $hook ): void {
	$callbacks = array(
		'woocommerce_before_shop_loop_item'       => array(
			array( 'woocommerce_template_loop_product_link_open', 10 ),
		),
		'woocommerce_before_shop_loop_item_title' => array(
			array( 'woocommerce_show_product_loop_sale_flash', 10 ),
			array( 'woocommerce_template_loop_product_thumbnail', 10 ),
		),
		'woocommerce_shop_loop_item_title'        => array(
			array( 'woocommerce_template_loop_product_title', 10 ),
		),
		'woocommerce_after_shop_loop_item_title'  => array(
			array( 'woocommerce_template_loop_rating', 5 ),
			array( 'woocommerce_template_loop_price', 10 ),
		),
		'woocommerce_after_shop_loop_item'        => array(
			array( 'woocommerce_template_loop_product_link_close', 5 ),
			array( 'woocommerce_template_loop_add_to_cart', 10 ),
		),
	);

	$removed = array();

	foreach ( $callbacks[ $hook ] ?? array() as $callback ) {
		if ( has_action( $hook, $callback[0] ) === $callback[1] ) {
			$removed[] = $callback;
			remove_action( $hook, $callback[0], $callback[1] );
		}
	}

	do_action( $hook );

	foreach ( $removed as $callback ) {
		add_action( $hook, $callback[0], $callback[1] );
	}
}

/**
 * Fires WooCommerce archive wrapper hooks without duplicating wrappers.
 *
 * @param string $hook Hook name.
 */
function agency_commerce_do_archive_wrapper_hook( string $hook ): void {
	$callbacks = array(
		'woocommerce_before_main_content' => array(
			array( 'woocommerce_output_content_wrapper', 10 ),
			array( 'woocommerce_breadcrumb', 20 ),
		),
		'woocommerce_after_main_content'  => array(
			array( 'woocommerce_output_content_wrapper_end', 10 ),
		),
	);

	$removed = array();

	foreach ( $callbacks[ $hook ] ?? array() as $callback ) {
		if ( has_action( $hook, $callback[0] ) === $callback[1] ) {
			$removed[] = $callback;
			remove_action( $hook, $callback[0], $callback[1] );
		}
	}

	do_action( $hook );

	foreach ( $removed as $callback ) {
		add_action( $hook, $callback[0], $callback[1] );
	}
}

/**
 * Renders product-card badges.
 *
 * @param array<int, array{class:string,label:string}> $badges Badge data.
 */
function agency_commerce_render_product_card_badges( array $badges ): void {
	agency_commerce_render_product_badges( $badges, 'ac-product-card__badges' );
}

/**
 * Renders product-card media.
 *
 * @param array<string, mixed> $data Product-card data.
 */
function agency_commerce_render_product_card_media( array $data ): void {
	?>
	<a class="ac-product-card__media" href="<?php echo esc_url( (string) $data['permalink'] ); ?>" aria-label="<?php echo esc_attr( (string) $data['title'] ); ?>">
		<?php
		echo wp_kses_post( (string) $data['image_html'] );

		if ( ! empty( $data['hover_image_html'] ) ) {
			echo wp_kses_post( (string) $data['hover_image_html'] );
		}
		?>
	</a>
	<?php
}

/**
 * Renders product-card category.
 *
 * @param array<string, mixed> $data Product-card data.
 */
function agency_commerce_render_product_card_category( array $data ): void {
	if ( empty( $data['category_html'] ) ) {
		return;
	}

	?>
	<div class="ac-product-card__category">
		<?php echo wp_kses_post( (string) $data['category_html'] ); ?>
	</div>
	<?php
}

/**
 * Renders product-card title.
 *
 * @param array<string, mixed> $data Product-card data.
 */
function agency_commerce_render_product_card_title( array $data ): void {
	?>
	<h2 class="woocommerce-loop-product__title ac-product-card__title">
		<a href="<?php echo esc_url( (string) $data['permalink'] ); ?>"><?php echo esc_html( (string) $data['title'] ); ?></a>
	</h2>
	<?php
}

/**
 * Renders product-card rating.
 *
 * @param array<string, mixed> $data Product-card data.
 */
function agency_commerce_render_product_card_rating( array $data ): void {
	if ( empty( $data['rating_html'] ) ) {
		return;
	}

	?>
	<div class="ac-product-card__rating">
		<?php echo wp_kses_post( (string) $data['rating_html'] ); ?>
	</div>
	<?php
}

/**
 * Renders product-card price.
 *
 * @param array<string, mixed> $data Product-card data.
 */
function agency_commerce_render_product_card_price( array $data ): void {
	if ( empty( $data['price_html'] ) ) {
		return;
	}

	?>
	<div class="ac-product-card__price price">
		<?php echo wp_kses_post( (string) $data['price_html'] ); ?>
	</div>
	<?php
}

/**
 * Renders product-card action.
 *
 * @param array<string, mixed> $data Product-card data.
 */
function agency_commerce_render_product_card_action( array $data ): void {
	$action  = is_array( $data['action'] ) ? $data['action'] : array();
	$product = $data['product'] ?? null;

	if ( empty( $action['url'] ) || empty( $action['text'] ) ) {
		return;
	}

	$classes = 'ac-product-card__action ' . (string) $action['class'];
	?>
	<a
		href="<?php echo esc_url( (string) $action['url'] ); ?>"
		class="<?php echo esc_attr( $classes ); ?>"
		aria-label="<?php echo esc_attr( (string) $action['aria_label'] ); ?>"
		<?php if ( $product instanceof WC_Product && 'quick-add' === $action['type'] ) : ?>
			data-quantity="1"
			data-product_id="<?php echo esc_attr( (string) $product->get_id() ); ?>"
			data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
			rel="nofollow"
		<?php endif; ?>
	>
		<?php echo esc_html( (string) $action['text'] ); ?>
	</a>
	<?php
}

/**
 * Renders the active product-filter chips supported by WooCommerce widgets.
 */
function agency_commerce_render_active_filters(): void {
	if ( ! function_exists( 'woocommerce_layered_nav_filters' ) ) {
		return;
	}

	ob_start();
	woocommerce_layered_nav_filters();
	$filters = trim( (string) ob_get_clean() );

	if ( '' === $filters ) {
		return;
	}

	?>
	<div class="ac-active-filters" aria-label="<?php esc_attr_e( 'Active filters', 'agency-commerce' ); ?>">
		<?php echo wp_kses_post( $filters ); ?>
	</div>
	<?php
}

/**
 * Renders shop filters in sidebar or drawer contexts.
 *
 * @param string $context Render context.
 */
function agency_commerce_render_shop_filters( string $context ): void {
	$has_widgets = is_active_sidebar( 'shop-filters' );
	$classes     = 'ac-shop-filters ac-shop-filters--' . sanitize_html_class( $context );
	?>
	<div class="<?php echo esc_attr( $classes ); ?>">
		<?php agency_commerce_render_active_filters(); ?>
		<?php if ( $has_widgets ) : ?>
			<?php dynamic_sidebar( 'shop-filters' ); ?>
		<?php else : ?>
			<p class="ac-shop-filters__empty"><?php esc_html_e( 'No filters are configured yet.', 'agency-commerce' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Renders the mobile product-filter drawer.
 */
function agency_commerce_render_filter_drawer(): void {
	?>
	<button class="ac-button ac-filter-toggle" type="button" aria-expanded="false" aria-controls="ac-filter-drawer">
		<?php esc_html_e( 'Filters', 'agency-commerce' ); ?>
	</button>
	<div class="ac-filter-overlay" data-filter-close hidden></div>
	<aside id="ac-filter-drawer" class="ac-filter-drawer" aria-label="<?php esc_attr_e( 'Product filters', 'agency-commerce' ); ?>" aria-hidden="true" hidden>
		<div class="ac-filter-drawer__header">
			<h2><?php esc_html_e( 'Filters', 'agency-commerce' ); ?></h2>
			<button class="ac-icon-button" type="button" data-filter-close>
				<span><?php esc_html_e( 'Close', 'agency-commerce' ); ?></span>
			</button>
		</div>
		<?php agency_commerce_render_shop_filters( 'drawer' ); ?>
	</aside>
	<?php
}

/**
 * Renders a category banner when the current archive has category media/details.
 */
function agency_commerce_render_category_banner(): void {
	if ( ! is_product_category() ) {
		/**
		 * Preserves the default WooCommerce archive-description hook for non-category archives.
		 */
		do_action( 'woocommerce_archive_description' );
		return;
	}

	$term = get_queried_object();

	if ( ! $term instanceof WP_Term ) {
		return;
	}

	$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	$description  = term_description( $term->term_id );
	?>
	<section class="ac-category-banner">
		<div class="ac-category-banner__content">
			<h1 class="woocommerce-products-header__title page-title"><?php echo esc_html( $term->name ); ?></h1>
			<?php if ( '' !== trim( wp_strip_all_tags( $description ) ) ) : ?>
				<div class="term-description"><?php echo wp_kses_post( $description ); ?></div>
			<?php endif; ?>
		</div>
		<?php if ( $thumbnail_id ) : ?>
			<div class="ac-category-banner__media">
				<?php echo wp_kses_post( wp_get_attachment_image( $thumbnail_id, 'large' ) ); ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Renders the selected single-product layout.
 */
function agency_commerce_render_product_layout(): void {
	$agency_commerce_product_layout_variant = agency_commerce_get_product_layout_variant();
	$agency_commerce_product_layout_file    = agency_commerce_component_path( 'components/product-layout/' . $agency_commerce_product_layout_variant . '.php' );

	if ( ! file_exists( $agency_commerce_product_layout_file ) ) {
		$agency_commerce_product_layout_file = agency_commerce_component_path( 'components/product-layout/product-layout-standard.php' );
	}

	require $agency_commerce_product_layout_file;
}

/**
 * Returns whether the current request is a product search request.
 */
function agency_commerce_is_product_search(): bool {
	$post_type = get_query_var( 'post_type' );

	return is_search() && agency_commerce_has_woocommerce() && ( 'product' === $post_type || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) ) );
}

<?php
/**
 * Homepage blocks and patterns.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Homepage;

use AgencyCommerceCore\RecentlyViewed\Manager as RecentlyViewedManager;

/**
 * Registers CP5 homepage blocks, styles, and patterns.
 */
final class BlockRegistrar {
	/**
	 * Block slugs.
	 *
	 * @var string[]
	 */
	private array $blocks = array(
		'hero',
		'category-grid',
		'promo-banner',
		'image-text',
		'trust-bar',
		'testimonials',
		'brand-logos',
		'faq',
		'newsletter',
		'recently-viewed-products',
	);

	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'init', array( $this, 'register_patterns' ) );
		add_action( 'init', array( $this, 'register_block_styles' ) );
	}

	/**
	 * Registers custom blocks.
	 */
	public function register_blocks(): void {
		foreach ( $this->blocks as $block ) {
			$path = AGENCY_COMMERCE_CORE_PATH . 'blocks/' . $block;

			if ( file_exists( $path . '/block.json' ) ) {
				register_block_type(
					$path,
					array(
						'render_callback' => array( $this, 'render_block' ),
					)
				);
			}
		}
	}

	/**
	 * Renders dynamic block markup.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content Block content.
	 * @param \WP_Block            $block Block instance.
	 */
	public function render_block( array $attributes, string $content, \WP_Block $block ): string {
		$name = str_replace( 'agency-commerce/', '', $block->name );

		if ( 'recently-viewed-products' === $name ) {
			return RecentlyViewedManager::render_block();
		}

		switch ( $name ) {
			case 'hero':
				return $this->render_hero( $attributes );
			case 'category-grid':
				return $this->render_category_grid( $attributes );
			case 'promo-banner':
				return $this->render_promo_banner( $attributes );
			case 'image-text':
				return $this->render_image_text( $attributes );
			case 'trust-bar':
				return $this->render_trust_bar( $attributes );
			case 'testimonials':
				return $this->render_testimonials( $attributes );
			case 'brand-logos':
				return $this->render_brand_logos( $attributes );
			case 'faq':
				return $this->render_faq( $attributes );
			case 'newsletter':
				return $this->render_newsletter( $attributes );
		}

		return '';
	}

	/**
	 * Registers product-block styles.
	 */
	public function register_block_styles(): void {
		foreach ( array( 'woocommerce/product-collection', 'woocommerce/product-best-sellers', 'woocommerce/product-new', 'woocommerce/product-on-sale', 'woocommerce/handpicked-products' ) as $block_name ) {
			if ( function_exists( 'register_block_style' ) ) {
				register_block_style(
					$block_name,
					array(
						'name'  => 'agency-commerce-section',
						'label' => __( 'Agency Commerce section', 'agency-commerce-core' ),
					)
				);
			}
		}
	}

	/**
	 * Registers reusable homepage patterns.
	 */
	public function register_patterns(): void {
		if ( ! function_exists( 'register_block_pattern_category' ) || ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		register_block_pattern_category(
			'agency-commerce-homepages',
			array( 'label' => __( 'Agency Commerce Homepages', 'agency-commerce-core' ) )
		);

		register_block_pattern(
			'agency-commerce/fashion-homepage',
			array(
				'title'      => __( 'Fashion homepage', 'agency-commerce-core' ),
				'categories' => array( 'agency-commerce-homepages' ),
				'content'    => $this->fashion_pattern(),
			)
		);

		register_block_pattern(
			'agency-commerce/electronics-homepage',
			array(
				'title'      => __( 'Electronics homepage', 'agency-commerce-core' ),
				'categories' => array( 'agency-commerce-homepages' ),
				'content'    => $this->electronics_pattern(),
			)
		);

		register_block_pattern(
			'agency-commerce/minimal-homepage',
			array(
				'title'      => __( 'Minimal store homepage', 'agency-commerce-core' ),
				'categories' => array( 'agency-commerce-homepages' ),
				'content'    => $this->minimal_pattern(),
			)
		);
	}

	/**
	 * Renders hero block.
	 *
	 * @param array<string, mixed> $attributes Attributes.
	 */
	private function render_hero( array $attributes ): string {
		$attrs = wp_parse_args(
			$attributes,
			array(
				'eyebrow'       => '',
				'heading'       => __( 'Build a better store', 'agency-commerce-core' ),
				'description'   => '',
				'primaryText'   => __( 'Shop now', 'agency-commerce-core' ),
				'primaryUrl'    => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
				'secondaryText' => '',
				'secondaryUrl'  => '',
				'layout'        => 'split',
			)
		);

		ob_start();
		?>
		<section class="<?php echo esc_attr( 'ac-home-hero ac-home-hero--' . sanitize_html_class( (string) $attrs['layout'] ) ); ?>">
			<div class="ac-home-hero__content">
				<?php if ( '' !== trim( (string) $attrs['eyebrow'] ) ) : ?>
					<p class="ac-home-hero__eyebrow"><?php echo esc_html( (string) $attrs['eyebrow'] ); ?></p>
				<?php endif; ?>
				<h2><?php echo esc_html( (string) $attrs['heading'] ); ?></h2>
				<?php if ( '' !== trim( (string) $attrs['description'] ) ) : ?>
					<p><?php echo esc_html( (string) $attrs['description'] ); ?></p>
				<?php endif; ?>
				<div class="ac-home-hero__actions">
					<a class="button" href="<?php echo esc_url( (string) $attrs['primaryUrl'] ); ?>"><?php echo esc_html( (string) $attrs['primaryText'] ); ?></a>
					<?php if ( '' !== trim( (string) $attrs['secondaryText'] ) && '' !== trim( (string) $attrs['secondaryUrl'] ) ) : ?>
						<a class="button ac-button--secondary" href="<?php echo esc_url( (string) $attrs['secondaryUrl'] ); ?>"><?php echo esc_html( (string) $attrs['secondaryText'] ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Renders category grid block.
	 *
	 * @param array<string, mixed> $attributes Attributes.
	 */
	private function render_category_grid( array $attributes ): string {
		$count = max( 1, min( 12, (int) ( $attributes['count'] ?? 4 ) ) );
		$terms = taxonomy_exists( 'product_cat' ) ? get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => $count,
			)
		) : array();

		if ( ! is_array( $terms ) || empty( $terms ) ) {
			return '';
		}

		ob_start();
		?>
		<section class="ac-home-section ac-category-grid">
			<h2><?php echo esc_html( (string) ( $attributes['heading'] ?? __( 'Shop by category', 'agency-commerce-core' ) ) ); ?></h2>
			<div class="ac-category-grid__items">
				<?php foreach ( $terms as $term ) : ?>
					<?php
					$link = get_term_link( $term );

					if ( is_wp_error( $link ) ) {
						continue;
					}
					?>
					<a class="ac-category-grid__item" href="<?php echo esc_url( $link ); ?>">
						<span><?php echo esc_html( $term->name ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Renders promo banner.
	 *
	 * @param array<string, mixed> $attributes Attributes.
	 */
	private function render_promo_banner( array $attributes ): string {
		return $this->simple_section( 'ac-promo-banner', $attributes, __( 'Limited-time offer', 'agency-commerce-core' ) );
	}

	/**
	 * Renders image/text section.
	 *
	 * @param array<string, mixed> $attributes Attributes.
	 */
	private function render_image_text( array $attributes ): string {
		return $this->simple_section( 'ac-image-text', $attributes, __( 'Tell your store story', 'agency-commerce-core' ) );
	}

	/**
	 * Renders trust bar.
	 *
	 * @param array<string, mixed> $attributes Attributes.
	 */
	private function render_trust_bar( array $attributes ): string {
		$items = is_array( $attributes['items'] ?? null ) ? $attributes['items'] : array( __( 'Secure checkout', 'agency-commerce-core' ), __( 'Fast dispatch', 'agency-commerce-core' ), __( 'Easy returns', 'agency-commerce-core' ) );
		ob_start();
		?>
		<section class="ac-trust-bar" aria-label="<?php esc_attr_e( 'Store trust signals', 'agency-commerce-core' ); ?>">
			<?php foreach ( $items as $item ) : ?>
				<span class="ac-trust-bar__item"><?php echo esc_html( (string) $item ); ?></span>
			<?php endforeach; ?>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Renders testimonials.
	 *
	 * @param array<string, mixed> $attributes Attributes.
	 */
	private function render_testimonials( array $attributes ): string {
		return $this->simple_section( 'ac-testimonials', $attributes, __( 'Loved by customers', 'agency-commerce-core' ) );
	}

	/**
	 * Renders brand logos.
	 *
	 * @param array<string, mixed> $attributes Attributes.
	 */
	private function render_brand_logos( array $attributes ): string {
		return $this->simple_section( 'ac-brand-logos', $attributes, __( 'Brands we carry', 'agency-commerce-core' ) );
	}

	/**
	 * Renders FAQ block.
	 *
	 * @param array<string, mixed> $attributes Attributes.
	 */
	private function render_faq( array $attributes ): string {
		$items = is_array( $attributes['items'] ?? null ) ? $attributes['items'] : array(
			array(
				'question' => __( 'How fast do you ship?', 'agency-commerce-core' ),
				'answer'   => __( 'Most orders dispatch within 1-2 business days.', 'agency-commerce-core' ),
			),
		);
		ob_start();
		?>
		<section class="ac-home-section ac-faq">
			<h2><?php echo esc_html( (string) ( $attributes['heading'] ?? __( 'Questions', 'agency-commerce-core' ) ) ); ?></h2>
			<?php foreach ( $items as $item ) : ?>
				<details>
					<summary><?php echo esc_html( (string) ( $item['question'] ?? '' ) ); ?></summary>
					<p><?php echo esc_html( (string) ( $item['answer'] ?? '' ) ); ?></p>
				</details>
			<?php endforeach; ?>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Renders newsletter wrapper.
	 *
	 * @param array<string, mixed> $attributes Attributes.
	 */
	private function render_newsletter( array $attributes ): string {
		return $this->simple_section( 'ac-newsletter', $attributes, __( 'Join the list', 'agency-commerce-core' ) );
	}

	/**
	 * Renders a simple section.
	 *
	 * @param string               $section_class Section class.
	 * @param array<string, mixed> $attributes Attributes.
	 * @param string               $fallback_heading Fallback heading.
	 */
	private function simple_section( string $section_class, array $attributes, string $fallback_heading ): string {
		ob_start();
		?>
		<section class="<?php echo esc_attr( 'ac-home-section ' . $section_class ); ?>">
			<h2><?php echo esc_html( (string) ( $attributes['heading'] ?? $fallback_heading ) ); ?></h2>
			<?php if ( ! empty( $attributes['text'] ) ) : ?>
				<p><?php echo esc_html( (string) $attributes['text'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $attributes['url'] ) && ! empty( $attributes['buttonText'] ) ) : ?>
				<a class="button" href="<?php echo esc_url( (string) $attributes['url'] ); ?>"><?php echo esc_html( (string) $attributes['buttonText'] ); ?></a>
			<?php endif; ?>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Returns fashion pattern content.
	 */
	private function fashion_pattern(): string {
		return '<!-- wp:agency-commerce/hero {"eyebrow":"New season","heading":"Editorial essentials for every day","description":"Curated pieces, clean styling, and confident details.","layout":"split"} /--><!-- wp:agency-commerce/trust-bar /--><!-- wp:woocommerce/product-new {"columns":4,"rows":1} /--><!-- wp:agency-commerce/category-grid {"heading":"Shop the edit","count":4} /--><!-- wp:agency-commerce/newsletter {"heading":"Style notes in your inbox","text":"Get launch updates and private offers."} /-->';
	}

	/**
	 * Returns electronics pattern content.
	 */
	private function electronics_pattern(): string {
		return '<!-- wp:agency-commerce/hero {"eyebrow":"Tech ready","heading":"Gear that keeps work moving","description":"Practical devices, sharp pricing, and reliable checkout.","layout":"fullwidth"} /--><!-- wp:agency-commerce/trust-bar {"items":["Warranty support","Secure checkout","Fast dispatch"]} /--><!-- wp:woocommerce/product-best-sellers {"columns":4,"rows":1} /--><!-- wp:woocommerce/product-on-sale {"columns":4,"rows":1} /--><!-- wp:agency-commerce/faq /-->';
	}

	/**
	 * Returns minimal pattern content.
	 */
	private function minimal_pattern(): string {
		return '<!-- wp:agency-commerce/hero {"heading":"Simple commerce, clearly presented","description":"A clean starting point for focused stores."} /--><!-- wp:woocommerce/product-collection /--><!-- wp:agency-commerce/category-grid {"count":3} /--><!-- wp:agency-commerce/recently-viewed-products /-->';
	}
}

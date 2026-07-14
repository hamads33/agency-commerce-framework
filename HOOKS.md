# Hooks And Public API

This file records public extension points introduced by the framework. Keep these names backward-compatible across parent theme and plugin updates.

## Version Constants

- `AGENCY_COMMERCE_VERSION`: parent theme version.
- `AGENCY_COMMERCE_CORE_VERSION`: companion plugin version.
- `AGENCY_COMMERCE_CHILD_VERSION`: example child theme version.

## Theme Functions

### `agency_commerce_get_setting( string $key, mixed $fallback = null ): mixed`

Reads a setting by dot notation. If `agency-commerce-core` is active, the theme reads from the plugin helper. If the plugin is inactive, it reads from parent-theme fallback defaults.

Example:

```php
$variant = agency_commerce_get_setting( 'header.variant', 'header-default' );
```

### `agency_commerce_default_settings(): array`

Returns the parent theme fallback settings used when the plugin is inactive.

### `agency_commerce_get_header_data(): array`

Returns sanitized shared data for header variants.

### `agency_commerce_get_footer_data(): array`

Returns sanitized shared data for footer variants.

### `agency_commerce_component_path( string $template ): string`

Resolves component template paths and allows child-theme overrides through `locate_template()`.

### `agency_commerce_get_cart_count(): int`

Returns the WooCommerce cart count when WooCommerce is active and the cart object exists; otherwise returns `0`.

### `agency_commerce_cart_count_markup(): string`

Returns escaped cart-count markup used by the header and WooCommerce fragments.

### `agency_commerce_get_product_card_variant(): string`

Returns the sanitized product-card variant. Supported values are `product-card-classic`, `product-card-minimal`, and `product-card-compact`.

### `agency_commerce_prepare_product_card_data( WC_Product $product ): array`

Returns the shared product-card presenter data consumed by every card variant.

### `agency_commerce_render_product_card( ?WC_Product $product = null ): void`

Renders the selected product-card variant for a WooCommerce product.

### `agency_commerce_get_product_layout_variant(): string`

Returns the sanitized single-product layout variant. Supported values are `product-layout-standard` and `product-layout-sticky`.

### `agency_commerce_render_product_layout(): void`

Renders the selected single-product layout while preserving WooCommerce single-product hooks.

### `agency_commerce_render_shop_filters( string $context ): void`

Renders the registered WooCommerce filter widget area for sidebar or drawer contexts.

## Menu Locations

- `primary`: desktop and mobile primary navigation.
- `footer`: footer navigation.

## Widget Areas

- `shop-filters`: standard WooCommerce product filter widgets for shop and product-category archives.

## Plugin Functions

### `agency_commerce_core_get_setting( string $key, mixed $fallback = null ): mixed`

Reads a plugin setting by dot notation from saved settings merged with defaults.

### `agency_commerce_core_get_settings(): array`

Returns the memoized settings array.

### `agency_commerce_core_defaults(): array`

Returns plugin defaults after public default filters.

## Actions

### `agency_commerce_core_loaded`

Fires after the core plugin foundation has loaded.

### `agency_commerce_single_product_summary_extensions`

Fires after WooCommerce's native single-product summary hook inside the selected product layout. Reserved for later delivery, enquiry, and size-guide extensions.

### `agency_commerce_after_single_product_summary_extensions`

Fires after WooCommerce's native after-summary hook inside the selected product layout. Reserved for later recently-viewed and related presentation extensions.

## Filters

### `agency_commerce_default_settings`

Filters parent-theme fallback settings.

### `agency_commerce_core_defaults`

Filters the canonical plugin defaults before they are exposed through helper functions.

### `agency_commerce_core_default_settings`

Filters the public plugin defaults array.

## WooCommerce Filters Used

### `woocommerce_add_to_cart_fragments`

The parent theme uses this supported WooCommerce filter to refresh `span.ac-cart-count` after add-to-cart events. It does not implement custom AJAX or custom cart state.

### `loop_shop_columns`

The parent theme uses this filter to apply the configured product grid column count.

### `pre_get_posts`

The parent theme uses this action to apply product-aware frontend search and configured products-per-page values without replacing WooCommerce query logic.

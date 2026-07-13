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

## Menu Locations

- `primary`: desktop and mobile primary navigation.
- `footer`: footer navigation.

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

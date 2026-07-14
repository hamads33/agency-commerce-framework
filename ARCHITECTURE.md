# Architecture

## Theme Bootstrap Flow

`wp-content/themes/agency-commerce/functions.php` defines parent-theme constants and loads focused modules from `inc/`:

1. `settings.php` provides safe setting defaults and plugin fallback reads.
2. `setup.php` registers theme support, menus, and widget areas.
3. `navigation.php` provides shared header/footer component helpers.
4. `catalog.php` provides WooCommerce catalog, card, archive, search, filter, and product-layout helpers.
5. `assets.php` enqueues parent assets and conditionally loads catalog JavaScript.
6. `woocommerce.php` declares WooCommerce feature compatibility and cart-count fragments.

`functions.php` remains a loader only.

## Plugin Bootstrap Flow

`agency-commerce-core.php` loads Composer or the fallback PSR-4 autoloader, includes public helper functions, then boots `AgencyCommerceCore\Plugin` on `plugins_loaded`.

The plugin owns the Commerce Framework admin settings infrastructure and does not require the parent theme. `AgencyCommerceCore\Admin\SettingsPage` registers the `Commerce Framework` menu, WordPress Settings API option, tabbed sections, and fields. Settings are stored as one sanitized option blob: `agency_commerce_core_settings`.

Settings definitions live in `AgencyCommerceCore\Settings\Registry`. Sanitization and validation live in `AgencyCommerceCore\Settings\Sanitizer`. Public reads use `agency_commerce_core_get_settings()` and `agency_commerce_core_get_setting()`, which merge saved values with defaults and memoize the sanitized result for the request.

CP4B adds `AgencyCommerceCore\Presets\Manager` for framework-only preset application and rollback, `AgencyCommerceCore\Admin\PresetActions` for admin-post handling, and reusable product badges, delivery messaging, and WhatsApp enquiry rendering. CP4C splits product responsibilities across `Products\BadgeManager`, `Products\DeliveryManager`, `Products\WhatsAppEnquiry`, and `Products\ProductSettings`, leaving `Products\FeatureManager` as the WooCommerce/admin hook coordinator. The plugin product layer reads only plugin-owned settings helpers and does not depend on parent-theme functions.

Settings schema versioning is separate from the settings blob. `AGENCY_COMMERCE_CORE_SETTINGS_SCHEMA_VERSION` declares the target schema version and `AgencyCommerceCore\Migrations\SettingsMigrationManager` stores the installed version in `agency_commerce_core_settings_schema_version`. Migrations run in ascending version order and update the stored schema version only after each migration succeeds. Version 1 is a no-op marker so existing CP4A installations keep all option values unchanged when versioning is introduced.

CP4C raises the schema to version 3. `AgencyCommerceCore\Migrations\Version3` migrates CP4B custom badge metadata from legacy array storage to canonical newline text. Runtime badge reads continue to accept both legacy arrays and canonical text so existing stores do not produce PHP warnings during or before migration.

The parent theme continues to call `agency_commerce_get_setting()`. When the plugin is active, that helper reads the plugin settings. When the plugin is inactive, it reads the parent fallback defaults in `inc/settings.php`.

CP4A settings cover store identity, header, shop, product page, footer, social links, color/font tokens, performance, and developer toggles. Later feature areas such as WhatsApp, product badges, delivery estimates, checkout controls, cart drawer, presets, recently viewed, size guide, and product enquiries are intentionally not implemented in CP4A.

CP4B adds the commerce features that sit on top of the settings infrastructure:

- Presets only mutate `agency_commerce_core_settings` framework keys and keep catalog, page, menu, widget, media, order, and user data untouched.
- Product badges are resolved in one helper and rendered once in card and single-product contexts.
- Delivery messaging resolves product override, then category override, then the global message.
- WhatsApp enquiry URLs are generated from sanitized phone and template settings, with variable-product support handled through the selected variation.
- Social links continue to render through the theme footer component with empty links filtered out.
- Static admin preview thumbnails are used only in the settings UI and never in live storefront rendering.

## Public Hooks And Filters

Stable public hooks and filters are listed in `HOOKS.md`. CP3 adds product-card presenter helpers, catalog variant helpers, and single-product extension actions.

## Product-Card Presenter Flow

WooCommerce loop rendering enters `woocommerce/content-product.php`, which calls `agency_commerce_render_product_card()`.

That function:

1. Reads the selected card variant through `shop.card_variant`.
2. Calls `agency_commerce_prepare_product_card_data( WC_Product $product )`.
3. Resolves the variant component through `agency_commerce_component_path()`.
4. Requires exactly one of the supported card templates:
   - `components/product-card/product-card-classic.php`
   - `components/product-card/product-card-minimal.php`
   - `components/product-card/product-card-compact.php`

Variant templates arrange shared presenter data only. Price, badge, image, category, rating, stock, and add-to-cart decisions stay in the presenter/helpers. CP4B extends the shared presenter with reusable badge data so product cards do not reimplement badge logic.

## Product Extension Flow

Single-product layouts keep WooCommerce's native gallery, title, rating, price, excerpt, variations, quantity, add-to-cart, meta, tabs, and related-product hooks intact. CP4B injects extension content through `agency_commerce_single_product_summary_extensions`, which now renders:

- reusable product badges,
- delivery messaging, and
- the WhatsApp enquiry button.

This keeps the summary contract stable while allowing new commerce features to be layered in without duplicating template structure.

## Component Hierarchy

- Header router: `components/header/header.php`
- Header variants: `components/header/header-default.php`, `components/header/header-centered.php`
- Footer router: `components/footer/footer.php`
- Footer variants: `components/footer/footer-columns.php`, `components/footer/footer-minimal.php`
- Product-card variants: `components/product-card/product-card-*.php`
- Product-layout variants: `components/product-layout/product-layout-standard.php`, `components/product-layout/product-layout-sticky.php`

## WooCommerce Integration Points

- `loop_shop_columns`: applies configured products per row.
- `pre_get_posts`: applies configured products per page and product-aware search only when the request explicitly searches products or the compatibility setting is enabled.
- `woocommerce_before_main_content` and `woocommerce_after_main_content`: fired by the archive override with WooCommerce's default wrapper callbacks temporarily removed so third-party callbacks still run without duplicating the framework shell.
- `woocommerce_before_shop_loop_item`, `woocommerce_before_shop_loop_item_title`, `woocommerce_shop_loop_item_title`, `woocommerce_after_shop_loop_item_title`, and `woocommerce_after_shop_loop_item`: fired around framework product cards with WooCommerce's default card callbacks temporarily removed so extension callbacks keep standard positions.
- `woocommerce_before_shop_loop`, `woocommerce_after_shop_loop`, and `woocommerce_no_products_found`: preserve WooCommerce archive loop behavior.
- `woocommerce_before_single_product_summary`, `woocommerce_single_product_summary`, and `woocommerce_after_single_product_summary`: preserve native gallery, title, rating, price, excerpt, variations, quantity, add-to-cart, meta, tabs, and related products.
- `woocommerce_add_to_cart_fragments`: refreshes the CP2 cart count.

## Template Override Policy

Overrides are limited to structural shells that cannot be cleanly expressed with hooks alone:

- `woocommerce/archive-product.php`: archive shell with sidebar and mobile filter drawer.
- `woocommerce/content-product.php`: card presenter routing.
- `woocommerce/content-single-product.php`: single-product layout routing.
- `woocommerce/loop/no-products-found.php`: styled empty state.

Overrides must continue calling WooCommerce hooks and must not replace cart, checkout, filter, variation, payment, or shipping logic.

## Child-Theme Override Strategy

Presentation components resolve through `agency_commerce_component_path()`, which uses `locate_template()`. A child theme can override a component by copying only that component path, for example:

```text
wp-content/themes/agency-commerce-child/components/product-card/product-card-minimal.php
```

Child themes should prefer component overrides and CSS variable changes over copying WooCommerce templates.

## CSS Token Flow

`theme.json` defines canonical design tokens. `style.css` mirrors those values as `--ac-*` custom properties for classic PHP templates and WooCommerce markup. CP4A adds settings-backed color and font token overrides emitted by `agency_commerce_design_token_css()` as inline custom properties. Child themes can still customize storefront presentation by overriding CSS variables.

CP4B extends the token flow with container width, spacing density, radius, shadow, and product-image-ratio tokens so presets can reshape the presentation without touching template code.

## Asset Loading Flow

Runtime assets are built with `wp-scripts` from `tooling/assets/*`. Storefront entries are emitted to `wp-content/themes/agency-commerce/build`; admin entries are emitted to `wp-content/plugins/agency-commerce-core/build`. The parent theme reads generated `.asset.php` metadata for `frontend` and `catalog` entries and falls back to source CSS/JS when build artifacts are missing. Header/mobile navigation JavaScript is globally bundled in `frontend.js`. Catalog drawer and WhatsApp variation JavaScript are conditionally enqueued from `catalog.js` only on WooCommerce catalog, product, and search contexts through `agency_commerce_should_load_catalog_assets()`. The `performance.load_assets_conditionally` setting can disable conditional catalog loading for debugging or compatibility.

The admin settings screen reads generated metadata for the `admin` entry and falls back to `assets/admin/settings.css`. Static SVG preview thumbnails remain admin-only and do not affect storefront runtime.

## Visual Regression Flow

Playwright visual regression tests live in `tests/visual/`. Baselines are stored under `tests/visual-baseline/` by viewport project:

- `desktop`
- `tablet`
- `mobile`

The visual suite captures homepage, shop, product category, simple product, variable product, cart, checkout, and My Account. `npm run visual:baseline` updates baseline screenshots. `npm run visual:compare` compares the current render against those baselines and fails when differences exceed `VISUAL_DIFF_THRESHOLD` (`0.01` by default).

## Migration Policy

Future checkpoints that need to alter the settings schema must:

1. Add a migration class in `AgencyCommerceCore\Migrations`.
2. Return the new integer schema version from `version()`.
3. Register the migration in `SettingsMigrationManager::migrations()`.
4. Increment `AGENCY_COMMERCE_CORE_SETTINGS_SCHEMA_VERSION`.
5. Preserve existing option keys and values unless the migration explicitly and safely transforms them.
6. Document the migration in `DEVELOPMENT.md` and `CHANGELOG.md`.

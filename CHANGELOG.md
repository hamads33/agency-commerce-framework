# Changelog

## 0.4.1 - Framework quality checkpoint

- Added settings schema versioning with `AGENCY_COMMERCE_CORE_SETTINGS_SCHEMA_VERSION`.
- Added the stored `agency_commerce_core_settings_schema_version` option.
- Added a settings migration manager and no-op version 1 migration that preserves existing settings.
- Added Playwright visual baseline capture and comparison infrastructure under `tests/visual-baseline/`.
- Added visual baseline and comparison npm scripts.
- Added a sidebar compatibility template for classic theme sidebar lookups.
- Documented future settings migration and visual regression workflows.

## 0.4.0 - CP4A

- Added the `Commerce Framework` admin menu and tabbed Settings API interface.
- Added sanitized, validated, memoized plugin settings retrieval with cache flushing.
- Added CP4A defaults for store identity, header, shop, product page, footer, social links, design tokens, performance, and developer settings.
- Added settings-backed selectors for existing header, footer, product-card, and product-layout variants.
- Added settings-backed sticky header, product grid, product count, filter layout, social link, color token, font token, and performance controls.
- Added parent-theme fallback defaults and inline design-token overrides so the theme continues working when the plugin is inactive.
- Added a Playwright admin settings smoke test command.

## 0.3.0 - CP3

- Added a shared product-card presenter and the `product-card-classic`, `product-card-minimal`, and `product-card-compact` variants.
- Added WooCommerce shop/category archive structure with breadcrumbs, result count, sorting, pagination, empty state, category banner support, and standard filter widget integration.
- Added accessible mobile product-filter drawer JavaScript.
- Added `product-layout-standard` and `product-layout-sticky` while preserving WooCommerce single-product hooks.
- Added product-aware native search form and styled search results.
- Added catalog, card, filter, search, and single-product responsive styling.
- Added `ARCHITECTURE.md` and `COMPONENTS.md` documentation.

## 0.2.0 - CP2

- Added `header-default` and `header-centered` variants with shared header data preparation.
- Added `footer-columns` and `footer-minimal` variants with shared footer data preparation.
- Registered primary and footer menu locations.
- Added announcement bar, logo fallback, search, account, cart count, mobile menu, keyboard support, focus restoration, and Escape-to-close behavior.
- Added WooCommerce cart-count fragment support using `woocommerce_add_to_cart_fragments`.
- Added sticky-header integration through existing safe settings helpers.
- Added global page containers, base typography, buttons, forms, layout primitives, and responsive styling.
- Tightened the PHPCS filename-sniff exclusion to PSR-4 plugin class files only.

## 0.1.0 - CP1

- Added activation-safe parent theme, child theme, and companion plugin foundations.
- Added parent theme support declarations, `theme.json` design tokens, mirrored CSS custom properties, and minimal classic templates.
- Added namespaced plugin bootstrap, PSR-4 loading, version constants, default settings, and public settings helpers.
- Added theme fallback settings helpers so the parent works when the plugin is inactive.
- Documented the initial public API contract in `HOOKS.md`.

## 0.0.0 - CP0

- Established repository-level tooling for `wp-env`, Composer, PHPCS/WPCS, PHP syntax checks, npm, and `@wordpress/scripts`.
- Added skeletal source directories for the future parent theme, companion plugin, and child theme.
- Documented local environment commands and CP0 version/test evidence.
- Corrected CP0 reproducibility by pinning WordPress core to the official `7.0.1` release package.
- Made WooCommerce reproducible with the exact `10.9.4` plugin package and setup command.
- Added Node/npm toolchain pins through `.nvmrc`, `packageManager`, and `engines`.
- Expanded environment recreation, version-pin, and audit documentation.

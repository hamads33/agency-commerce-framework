# Changelog

## 0.5.0 - CP5 Homepage And Commerce Flow

- Added Gutenberg-editable homepage architecture with custom dynamic blocks for hero, category grid, promotional banner, image/text, trust bar, testimonials, brand logos, FAQ, newsletter wrapper, and recently viewed products.
- Added reusable Fashion, Electronics, and Minimal homepage block patterns that insert content without overwriting existing homepage content.
- Added WooCommerce block-first cart and checkout styling while preserving Store API behavior and WooCommerce cart/checkout state.
- Added optional WooCommerce Mini-Cart block rendering in the header with the existing cart link/count fallback when disabled or unavailable.
- Added safe Block/Classic checkout mode switching with non-destructive Cart and Checkout page content backups and restoration.
- Added checkout controls for support text, trust text, optional fields where WooCommerce allows them, minimum order amount, and COD availability/instructions.
- Added privacy-conscious recently viewed products using localStorage plus a public render endpoint; no personal browsing history is stored in the database.
- Added scoped My Account styling while preserving native WooCommerce endpoints.
- Added CP5 Playwright smoke suites for homepage, cart, block checkout, classic checkout, and My Account flows.
- Updated only the 12 affected visual baselines for homepage, cart, checkout, and My Account across desktop, tablet, and mobile after reviewing the intentional CP5 layout changes.
- Updated framework version metadata to `0.5.0`.

## Unreleased - CP4D Visual Regression Review

- Reviewed all 24 visual regression failures across homepage, shop, product category, simple product, variable product, cart, checkout, and My Account at desktop, tablet, and mobile viewports.
- Fixed the unexpected WooCommerce product-loop width regression by overriding core float, margin, and width rules at equivalent specificity so framework product cards render at usable grid widths.
- Made visual regression tests reset mutable framework settings, presets, theme/plugin state, and CP4B product badge metadata before screenshots.
- Made storefront Playwright setup self-contained when the framework settings option row is absent and explicitly seeded the card/layout variants it verifies.
- Updated only the 24 affected visual baselines after approving expected CP4B/CP4C/CP4D changes: reusable badges, product summary extensions, runtime CSS output, restored default footer rendering, and corrected product-card grid layout.

## 0.4.3 - CP4C Audit Remediation

- Fixed custom badge metadata storage by migrating legacy array values to canonical newline text while preserving runtime compatibility for both shapes.
- Removed plugin product-feature reads from parent-theme helpers; plugin commerce features now read plugin-owned settings directly.
- Split product badge, delivery, WhatsApp, settings-choice, and admin asset-resolution responsibilities out of larger coordinator classes.
- Restored WooCommerce archive wrapper and product-loop hook names around the framework templates while preserving existing markup.
- Connected storefront, catalog, and admin runtime assets to the `wp-scripts` production build under `wp-content/build` with asset metadata and cache-busting versions.
- Synchronized framework version metadata to `0.4.3`.
- Changed default search behavior so normal WordPress search remains available and product search is selected explicitly with `post_type=product`; added a compatibility setting for the old forced product-search behavior.

## 0.4.2 - CP4B

- Added a framework preset engine with Minimal, Fashion, and Electronics presets.
- Added preset preview, application, snapshot, and restore support through the admin settings screen.
- Added reusable WhatsApp product enquiry rendering with safe URL generation and variable-product support.
- Added reusable product badges with built-in and custom labels stored in product metadata.
- Added delivery estimate rendering with global, category, and product overrides.
- Added storefront social-link rendering for the configured networks.
- Added static admin preview assets for header, footer, product-card, and product-layout variants.
- Added public helper, filter, action, and component-contract documentation for the new commerce extension surface.
- Added storefront and admin Playwright coverage for the new CP4B behavior.

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

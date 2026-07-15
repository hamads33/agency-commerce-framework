# Changelog

## Unreleased - CP7B Premium Showcase Storefronts

- Added three premium showcase child themes built on top of the parent
  framework: `agency-commerce-fashion`, `agency-commerce-electronics`, and
  `agency-commerce-home`.
- Added unique child-theme homepage templates, token profiles, typography,
  spacing, header/footer treatments, product-card presentation, category/shop
  styling, cart/checkout styling, and My Account styling for each storefront.
- Added original CSS-generated demo imagery and abstract visual treatments
  without copying reference brand logos, photography, text, trademarks, or
  pixel layouts.
- Added a dedicated showcase Playwright visual-regression suite covering
  homepage, category, shop, product, cart, checkout, and My Account across
  desktop, tablet, and mobile.
- Added a reproducible showcase asset generator for portfolio screenshots and
  comparison boards.

## Unreleased - CP7A Premium Design System

- Added a premium storefront design-system layer without adding commerce
  functionality or changing component architecture.
- Expanded theme tokens for typography scale, responsive section spacing,
  radius hierarchy, elevation hierarchy, focus rings, state colors, image
  treatments, skeleton loading, and motion timing.
- Upgraded buttons, icon controls, forms, product cards, pagination, drawers,
  empty states, WooCommerce notices, checkout/cart panels, My Account panels,
  homepage sections, and image treatments to consume the shared token system.
- Replaced the hard-coded WhatsApp button colors with success-token driven
  styling.
- Added tasteful hover/focus/transition behavior while respecting
  `prefers-reduced-motion`.
- Updated design-system documentation in `ARCHITECTURE.md` and `COMPONENTS.md`.

## 1.0.0 - CP6.1 Release Candidate Preparation

- Updated release metadata for the parent theme, child theme, companion plugin,
  root npm package, plugin npm package, and Composer manifests.
- Replaced public-looking `example.com` metadata URLs with reserved
  `agency-commerce.invalid` internal placeholders because no public project URLs
  exist yet.
- Added GPLv2-or-later licensing files and third-party licensing documentation
  for WordPress and WooCommerce compatibility.
- Added `SECURITY_EXCEPTION.md` documenting accepted development-tool npm audit
  risk and why no breaking toolchain upgrade is approved for CP6.1.
- Added release installation, upgrade, and developer guides.
- Added production packaging automation for the parent theme, companion plugin,
  and child theme ZIPs.
- Updated release checklist status tracking for CP6.1.

## 0.5.0 - CP5 Homepage And Commerce Flow

- Added Gutenberg-editable homepage architecture with custom dynamic blocks for hero, category grid, promotional banner, image/text, trust bar, testimonials, brand logos, FAQ, newsletter wrapper, and recently viewed products.
- Added reusable Fashion, Electronics, and Minimal homepage block patterns that insert content without overwriting existing homepage content.
- Added WooCommerce block-first cart and checkout styling while preserving Store API behavior and WooCommerce cart/checkout state.
- Added optional WooCommerce Mini-Cart block rendering in the header with the existing cart link/count fallback when disabled or unavailable.
- Added safe Block/Classic checkout mode switching with non-destructive Cart and Checkout page content backups and restoration.
- Hardened Block/Classic checkout mode switching so original backups remain recoverable, latest block edits are preserved across repeated cycles, invalid mode values are allowlisted, and missing WooCommerce page IDs are skipped safely.
- Added checkout controls for support text, trust text, optional fields where WooCommerce allows them, minimum order amount, and COD availability/instructions.
- Hardened checkout validation so minimum order uses WooCommerce's calculated cart total and disabled COD attempts are rejected server-side in the Store API path.
- Added privacy-conscious recently viewed products using localStorage plus a public render endpoint; no personal browsing history is stored in the database.
- Hardened recently viewed rendering by bounding localStorage IDs, rejecting malformed or excessive REST input, and excluding private, unpublished, deleted, unavailable, or invisible products.
- Added scoped My Account styling while preserving native WooCommerce endpoints.
- Added CP5 Playwright smoke suites for homepage, cart, block checkout, classic checkout, My Account, and focused audit flows.
- Fixed CP5 verification defects so Classic checkout persists framework order context, non-forced Block-mode alignment preserves manual block page content, and audit cleanup restores WooCommerce's canonical block Cart and Checkout page content.
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

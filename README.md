# Agency Commerce Framework

Reusable WooCommerce framework for agency-built stores. The finished framework will consist of:

- `agency-commerce`: parent theme for storefront presentation.
- `agency-commerce-core`: companion plugin for reusable commerce functionality and settings.
- `agency-commerce-child`: example child theme for per-client branding.

CP0 establishes only the repository, tooling, and local WordPress/WooCommerce environment. Theme and plugin features begin in CP1.

CP1 adds the activation-safe foundation:

- Parent theme: `wp-content/themes/agency-commerce`
- Companion plugin: `wp-content/plugins/agency-commerce-core`
- Example child theme: `wp-content/themes/agency-commerce-child`

The parent theme and plugin are intentionally minimal at this checkpoint. Storefront chrome, catalog layouts, settings screens, and checkout behavior begin in later checkpoints.

CP2 adds storefront chrome:

- `header-default` and `header-centered`
- `footer-columns` and `footer-minimal`
- Primary and footer menus
- Announcement bar, search, account, cart count, responsive mobile menu, and sticky header support

CP3 adds catalog and product experience:

- Shared product-card presenter with `product-card-classic`, `product-card-minimal`, and `product-card-compact`
- Shop and product-category archive structure with sorting, result count, pagination, filters, and empty states
- `product-layout-standard` and `product-layout-sticky`
- Product-aware native search and styled search results

CP4A adds Commerce Framework settings infrastructure:

- Professional `Commerce Framework` admin menu using the WordPress Settings API
- Tabbed settings for store identity, header, shop, product page, footer, social links, design tokens, performance, and developer options
- Sanitized, validated, memoized settings reads through the plugin compatibility layer
- Settings-backed selectors for header, footer, product-card, and product-layout variants
- Settings-backed sticky header, products-per-row, products-per-page, color tokens, font tokens, social links, and store identity values

The framework quality checkpoint after CP4A adds:

- Settings schema versioning with a stored schema version and migration manager
- A no-op first migration that preserves existing CP4A settings unchanged
- Playwright visual baseline capture and comparison under `tests/visual-baseline/`
- Visual coverage for homepage, shop, product category, simple product, variable product, cart, checkout, and My Account across desktop, tablet, and mobile

CP4B adds reusable commerce features on top of that settings foundation:

- Framework-only presets for Minimal, Fashion, and Electronics with preview and restore support
- Reusable product badges with built-in and custom labels
- Product-level, category-level, and global delivery messaging
- Sanitized WhatsApp product enquiry links and variable-product support
- Configured social link rendering with empty-link suppression
- Static admin preview thumbnails for header, footer, product-card, and product-layout variants
- Public helper, action, filter, and component-contract documentation in `HOOKS.md`, `ARCHITECTURE.md`, and `COMPONENTS.md`

CP4C resolves the high-priority architectural audit findings before CP5:

- Custom badge metadata migration and legacy compatibility.
- Plugin product features no longer depend on parent-theme helpers.
- WooCommerce archive and loop hook compatibility is restored around framework templates.
- Runtime frontend/catalog assets are built into the parent theme and admin assets into the plugin, each with generated asset metadata.
- Normal WordPress search is preserved; product search remains available through `post_type=product`.
- Framework version metadata is synchronized at `0.4.3`.

CP5 adds homepage and purchase-flow architecture:

- Gutenberg-editable homepage blocks and block patterns for Fashion, Electronics, and Minimal store starts
- WooCommerce block Cart and Checkout as the default purchase path
- Optional WooCommerce Mini-Cart block in the header with the existing cart link fallback
- Safe checkout mode switching between Block and Classic shortcode flows
- Checkout support/trust messaging, optional-field controls, minimum order, and COD controls
- Privacy-conscious recently viewed products stored in the visitor browser only
- Scoped Cart, Checkout, Mini-Cart, My Account, and homepage-section styling

## Requirements

- Git
- Docker and Docker Compose
- PHP 8.3+
- Composer
- Node.js 24.14.x and npm 11.9.x

## Development Commands

```sh
npm ci
composer install
npm run env:start
npm run env:setup
npm run env:cli -- wp core version
npm run env:cli -- wp plugin activate agency-commerce-core
npm run env:cli -- wp theme activate agency-commerce
npm run env:cli -- wp theme activate agency-commerce-child
npm run env:cli -- wp menu location assign "CP2 Primary" primary
npm run env:cli -- wp menu location assign "CP2 Footer" footer
npm run env:stop
npm run build
npm run lint:js
npm run test:admin
npm run test:homepage
npm run test:cart-flow
npm run test:checkout-block
npm run test:checkout-classic
npm run test:account
npm run visual:baseline
npm run visual:compare
npm test
composer lint:php
composer lint
```

Use `npm run visual:baseline` to refresh committed screenshots and `npm run visual:compare` to fail on visual differences above the configured threshold. The default threshold is `0.01`; override it with `VISUAL_DIFF_THRESHOLD`.

See `DEVELOPMENT.md` for pinned versions, WP-CLI commands, reset commands, and checkpoint test evidence. See `HOOKS.md` for the public API contract introduced so far.

See `ARCHITECTURE.md` and `COMPONENTS.md` for the component contracts, WooCommerce integration points, and child-theme override strategy.

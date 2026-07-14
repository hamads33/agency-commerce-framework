# Development

## CP0 Environment

Local WordPress is provisioned with `@wordpress/env` (`wp-env`) because Docker and Docker Compose are available on this host and the build spec prefers `wp-env`.

The repository maps these future framework paths into WordPress:

- `wp-content/plugins/agency-commerce-core`
- `wp-content/themes/agency-commerce`
- `wp-content/themes/agency-commerce-child`

These directories are intentionally skeletal in CP0. Theme and plugin implementation starts in CP1.

## Pinned Versions

- WordPress core: `7.0.1`
- WordPress core source: `https://downloads.wordpress.org/release/wordpress-7.0.1.zip`
- WooCommerce: `10.9.4`
- WooCommerce source: `https://downloads.wordpress.org/plugin/woocommerce.10.9.4.zip`
- wp-env PHP: `8.3`
- Container PHP observed: `8.3.32`
- Host PHP observed: `8.3.6`
- Node.js: `24.14.0`
- npm: `11.9.0`
- Composer: `2.9.7`
- WP-CLI: `2.12.0`
- `@wordpress/env`: `11.10.0`
- `@wordpress/scripts`: `32.6.0`
- Local site URL: `http://localhost:8888`

## Version Decision

WordPress `7.0.1` is pinned because it was confirmed as an official stable release, not merely because a previous unpinned `wp-env` run returned it.

Evidence:

- `https://api.wordpress.org/core/stable-check/1.0/` returned `7.0.1` as `latest`.
- `https://api.wordpress.org/core/version-check/1.7/` returned release offers for `7.0.1`, including `https://downloads.wordpress.org/release/wordpress-7.0.1.zip`.
- The installed `@wordpress/env` documentation and source support ZIP file sources for `core`, `plugins`, and `themes`.

WooCommerce `10.9.4` is pinned because the local CP0 environment had that version active and it is restorable from the exact WordPress.org plugin ZIP.

Node.js `24.14.0` is pinned because the Node.js official release index marks `v24.14.0` as LTS (`Krypton`) and the host uses npm `11.9.0` with that release.

These pins keep CP0 reproducible. Do not change them incidentally while working on later checkpoints.

## Host Tool Versions

- Git: `2.43.0`
- Docker: `29.6.1`
- Docker Compose: `v5.3.0`
- Host PHP: `8.3.6`
- Composer: `2.9.7`
- Node.js: `v24.14.0`
- npm: `11.9.0`
- Playwright: `1.61.1`

## Install

Use the pinned lockfiles:

```sh
npm ci
composer install
```

Use Node from `.nvmrc` before installing:

```sh
nvm use
```

If `nvm` is unavailable, install Node.js `24.14.0` by another version manager and verify:

```sh
node --version
npm --version
```

## Start And Stop

```sh
npm run env:start
npm run env:stop
```

`wp-env` installs and activates WooCommerce from the pinned plugin ZIP in `.wp-env.json`. The explicit setup command is also provided so a clean or partially reset environment can be forced back to the same WooCommerce version:

```sh
npm run env:setup
```

When WooCommerce is already active, this command may emit a WordPress debug notice about WooCommerce translation loading being triggered early, followed by `Success: Plugin already activated.` The notice is from the pinned WooCommerce package running under `WP_DEBUG` and did not prevent CP0 verification.

## WP-CLI

Run WP-CLI commands through `wp-env`:

```sh
npm run env:cli -- wp core version
npm run env:cli -- wp plugin get woocommerce --fields=name,version,status --format=json
npm run env:cli -- wp cli version
npm run env:cli -- wp eval 'echo PHP_VERSION;'
```

## Build

```sh
npm run build
```

The CP0 build verifies `@wordpress/scripts` executes. Feature block and asset builds are added in later checkpoints.

CP5 builds parent theme runtime assets and plugin admin/block/checkout/recently-viewed entries:

- Parent theme: `frontend`, `catalog`, `index`
- Plugin: `admin`, `blocks`, `checkout`, `recently-viewed`

## Lint And Test

```sh
composer dump-autoload
composer lint:php
composer lint
npm run lint:js
npm test
npm run test:admin
npm run test:homepage
npm run test:cart-flow
npm run test:checkout-block
npm run test:checkout-classic
npm run test:account
npm run visual:baseline
npm run visual:compare
npm audit --audit-level=high
```

`composer lint:php` runs `php -l` across `wp-content`. `composer lint` runs PHPCS with WordPress Coding Standards.

`npm run test:admin` runs the CP4A Playwright admin settings smoke test against the local `wp-env` site at `http://localhost:8888`. Override `WP_BASE_URL`, `WP_USERNAME`, or `WP_PASSWORD` if the local admin differs from the default `admin` / `password`.

`npm run test:storefront` runs the CP4B/CP4C storefront Playwright smoke test against the same local environment. It covers badges, badge metadata migration, legacy badge array compatibility, delivery messaging, WhatsApp enquiries, social links, built runtime asset loading, normal WordPress search, product search, WooCommerce hook compatibility helpers, responsive breakpoints, and inactive-state fallbacks.

CP5 test scripts seed development-only fixtures through WP-CLI before browser checks. They create or normalize WooCommerce pages, shipping fallback, COD/BACS settings, one coupon, and a test homepage page. The plugin and theme do not auto-create this content on activation.

- `npm run test:homepage`: verifies custom block/pattern registration and frontend rendering.
- `npm run test:cart-flow`: verifies empty/populated cart surfaces and quantity controls.
- `npm run test:checkout-block`: verifies Block Checkout mode and checkout support/trust messaging.
- `npm run test:checkout-classic`: verifies shortcode switching, backup metadata, and classic cart/checkout surfaces.
- `npm run test:account`: verifies the native WooCommerce My Account surface.

If `wp-env run cli` reports an initialized-container state issue while the generated Docker Compose services are already running, Playwright helpers support `WP_ENV_COMPOSE_FILE=/path/to/docker-compose.yml` as an explicit local fallback. Normal development should continue using `npm run env:cli`.

`npm run visual:baseline` captures Playwright visual baselines under `tests/visual-baseline/`. `npm run visual:compare` compares the current site against those baselines. Override page URLs with `VISUAL_HOME_URL`, `VISUAL_SHOP_URL`, `VISUAL_CATEGORY_URL`, `VISUAL_SIMPLE_PRODUCT_URL`, `VISUAL_VARIABLE_PRODUCT_URL`, `VISUAL_CART_URL`, `VISUAL_CHECKOUT_URL`, and `VISUAL_MY_ACCOUNT_URL` when local slugs differ. Override the allowed diff ratio with `VISUAL_DIFF_THRESHOLD`; the default is `0.01`.

CP5 approved visual baseline updates only for:

- Homepage: now renders an editable Gutenberg homepage pattern instead of the earlier generic page shell.
- Cart and Checkout: now include CP5 block-cart styling and the optional Mini-Cart header control; empty checkout redirects to the WooCommerce cart surface as expected.
- My Account: now includes scoped WooCommerce account/login styling.

Shop, category, simple product, and variable product baselines did not change in CP5.

`npm audit --audit-level=high` currently reports transitive development dependency advisories under `@wordpress/scripts`. Do not claim the audit passes unless those findings are actually resolved.

## Clean Recreation

Before destroying or cleaning the environment, inspect local data:

```sh
npm run env:cli -- wp post list --post_type=any --fields=ID,post_type,post_status,post_title --format=table
npm run env:cli -- wp post list --post_type=attachment --format=count
npm run env:cli -- wp user list --fields=ID,user_login,user_email,roles --format=table
```

Warning: `npm run env:clean` deletes the local WordPress database and uploaded development content. `npm run env:destroy` removes the environment containers and volumes. Do not run either command if the local environment contains meaningful client, store, media, or test data that has not been preserved.

Recreate CP0 from a clean state:

```sh
npm run env:stop
npm run env:destroy
npm ci
composer install
npm run env:start
npm run env:setup
```

Then verify:

```sh
npm run env:cli -- wp core version
npm run env:cli -- wp plugin get woocommerce --fields=name,version,status --format=json
npm run env:cli -- wp cli version
npm run env:cli -- wp eval 'echo PHP_VERSION;'
curl -I -L --max-time 20 http://localhost:8888/
curl -I -L --max-time 20 http://localhost:8888/wp-admin/
composer dump-autoload
composer lint:php
composer lint
npm run build
npm test
npm audit --audit-level=high
```

## Updating Pins Intentionally

Update version pins only as a deliberate maintenance task:

1. Check official WordPress stable metadata:

   ```sh
   curl -fsSL https://api.wordpress.org/core/stable-check/1.0/
   curl -fsSL https://api.wordpress.org/core/version-check/1.7/
   ```

2. Update `.wp-env.json` `core` to the exact official WordPress release ZIP.
3. Update `.wp-env.json` `plugins` and `package.json` `env:setup` to the exact WooCommerce plugin ZIP.
4. Check the Node.js release index and update `.nvmrc`, `package.json` `packageManager`, and `package.json` `engines` together.
5. Run `npm install` only if `package.json` changed and the lockfile needs to reflect metadata changes.
6. Run `composer update` only when intentionally changing Composer dependencies; otherwise keep `composer.lock` intact and use `composer install`.
7. Recreate the environment and rerun the CP0 verification commands.
8. Record the new evidence and audit state in this file and `CHANGELOG.md`.

## CP0 Notes

- The working tree initially contained the checkpoint-aware spec as untracked `BUILD-SPEC-checkpoints.md`; CP0 renamed it to `BUILD-SPEC.md` so the required protocol filename exists.
- CP0 does not implement theme or plugin runtime features.
- `wp-env` is configured with `testsEnvironment: false` because CP0 only requires the development WordPress installation.
- Composer dependencies are locked through `composer.lock`.

## CP0 Correction Evidence

Data inspection before clean recreation showed only default CP0 content:

- WooCommerce setup pages: Shop, Cart, Checkout, My account, Refund and Returns Policy.
- Default WordPress content: Hello world, Sample Page, Privacy Policy.
- One WooCommerce placeholder attachment.
- One default administrator user.

Passed after clean recreation:

```sh
git status --short
npm ci
composer install
npm run env:start
npm run env:setup
npm run env:cli -- wp core version
npm run env:cli -- wp plugin get woocommerce --fields=name,version,status --format=json
npm run env:cli -- wp cli version
npm run env:cli -- wp eval 'echo PHP_VERSION;'
node --version
npm --version
composer --version
curl -I -L --max-time 20 http://localhost:8888/
curl -I -L --max-time 20 http://localhost:8888/wp-admin/
composer dump-autoload
composer lint:php
composer lint
npm run build
npm test
```

Failed or warning-only:

```sh
npm audit --audit-level=high
```

The audit command failed with 28 transitive development dependency advisories: 22 moderate and 6 high. The high-severity paths are under `@wordpress/scripts` dependencies including `linkify-it`, `minimatch`, and `serialize-javascript`; npm also reports moderate paths including `@opentelemetry/core` and `uuid`. npm's suggested force fix would install a breaking older `@wordpress/scripts` release for some paths. No production runtime code exists in CP0.

## CP1 Foundation

CP1 introduces only the framework foundation:

- Parent theme: `wp-content/themes/agency-commerce`
- Companion plugin: `wp-content/plugins/agency-commerce-core`
- Example child theme: `wp-content/themes/agency-commerce-child`

The parent theme is a classic PHP theme with `theme.json`. It includes minimal templates for activation safety, required theme supports, mirrored CSS custom properties, and fallback settings helpers. The companion plugin includes a namespaced bootstrap, PSR-4 loading, defaults, and public helper functions. The child theme proves parent inheritance and child stylesheet loading.

CP1 intentionally does not implement headers, footers, catalog layouts, settings screens, checkout modes, homepage blocks, or storefront features.

## CP1 Test Evidence

Passed:

```sh
npm run env:start
npm run env:setup
npm run env:cli -- wp plugin activate agency-commerce-core
npm run env:cli -- wp theme activate agency-commerce
curl -I -L --max-time 20 http://localhost:8888/
npm run env:cli -- wp plugin deactivate agency-commerce-core
curl -I -L --max-time 20 http://localhost:8888/
npm run env:cli -- wp theme activate agency-commerce-child
curl -I -L --max-time 20 http://localhost:8888/
npm run env:cli -- wp plugin deactivate woocommerce
npm run env:cli -- wp theme activate agency-commerce
npm run env:cli -- wp eval 'echo "theme=" . wp_get_theme()->get( "Name" );'
curl -I -L --max-time 20 http://localhost:8888/
npm run env:cli -- wp plugin activate woocommerce
composer dump-autoload
composer lint:php
composer lint
npm run build
npm test
```

Observed results:

- `agency-commerce-core` activates with the stock Twenty Twenty-Five theme active.
- `agency-commerce` activates without fatal errors.
- `agency-commerce-child` activates and lists `agency-commerce` as its parent.
- The parent theme serves `200 OK` while the plugin is inactive.
- The parent theme and plugin serve `200 OK` while WooCommerce is inactive.
- WooCommerce was restored active after the inactive compatibility test.
- PHP syntax checks and PHPCS pass.
- `@wordpress/scripts` build and npm test pass.

Known CP1 limits:

- The PHPCS config has a narrow `WordPress.Files.FileName` exclusion for the plugin PSR-4 class file path only.
- The theme screenshot is not provided in CP1.
- `npm audit --audit-level=high` remains a CP0-known development-tool audit failure.

## CP2 Storefront Chrome

CP2 introduces only storefront chrome:

- `header-default`
- `header-centered`
- `footer-columns`
- `footer-minimal`
- Primary and footer menu locations
- Optional announcement bar
- Logo fallback, search, account, and cart controls
- WooCommerce cart count via `woocommerce_add_to_cart_fragments`
- Accessible mobile menu with focus movement, focus trap, focus restoration, and Escape-to-close
- Sticky header setting integration
- Global containers, base typography, buttons, forms, layout primitives, and responsive CSS

Variant selection still uses the CP1-safe helper layer:

```php
agency_commerce_get_setting( 'header.variant', 'header-default' );
agency_commerce_get_setting( 'footer.variant', 'footer-columns' );
```

Until the admin settings UI exists, variants can be selected through plugin defaults, theme fallback defaults, filters, or WP-CLI-managed `agency_commerce_core_settings` test values.

CP2 intentionally does not implement product-card variants, shop archive customization, product-page layouts, product search suggestions, filters, admin settings screens, presets, WhatsApp enquiry, badges, homepage blocks, cart drawer, checkout customization, or My Account customization.

The PHPCS `WordPress.Files.FileName` exclusion is now scoped only to `wp-content/plugins/agency-commerce-core/includes/*` for PSR-4 plugin class files. It is not excluded for theme files or non-PSR-4 project files.

## CP2 Test Evidence

Passed:

```sh
composer dump-autoload
composer lint:php
composer lint
npm run build
npm test
npm run env:start
npm run env:setup
npm run env:cli -- wp theme activate agency-commerce
npm run env:cli -- wp theme activate agency-commerce-child
npm run env:cli -- wp plugin activate agency-commerce-core woocommerce
npm run env:cli -- wp plugin deactivate agency-commerce-core
npm run env:cli -- wp plugin deactivate woocommerce
curl -I -L --max-time 20 http://localhost:8888/
curl -I -L --max-time 20 http://localhost:8888/wp-admin/
```

Browser checks passed with Playwright:

- `header-default` rendered.
- `header-centered` rendered.
- `footer-columns` rendered.
- `footer-minimal` rendered.
- Non-sticky and sticky header modes rendered expected attributes/classes.
- Desktop navigation is visible at desktop width.
- Desktop navigation is hidden at mobile width.
- Mobile menu opens by pointer input.
- Focus moves into the mobile menu on open.
- Tab focus remains inside the mobile menu.
- Escape closes the mobile menu.
- Focus restores to the menu trigger after close.
- Responsive passes ran at mobile `390px`, tablet `768px`, and desktop `1440px`.
- Browser console produced no errors during the CP2 flow.
- Cart count rendered `0` in a fresh browser context and increased after adding a simple WooCommerce test product.

Compatibility checks passed:

- Parent theme served `200 OK` with the companion plugin inactive.
- Parent theme and companion plugin served `200 OK` with WooCommerce inactive.
- Child theme activated and served `200 OK` with WooCommerce restored active.
- `wp-admin` redirected to login and login returned `200 OK`.
- A fresh `debug.log` check after compatibility requests showed no PHP warnings.

Failed or warning-only:

```sh
npm audit --audit-level=high
```

The audit command still fails with the known transitive development dependency advisories under `@wordpress/scripts`: 28 total findings, 22 moderate and 6 high. No production runtime dependency was added in CP2.

## CP3 Catalog And Product Experience

CP3 introduces only catalog and product presentation:

- Shared product-card presenter data from `agency_commerce_prepare_product_card_data()`.
- `product-card-classic`, `product-card-minimal`, and `product-card-compact`.
- Shop and product-category archive shells with breadcrumbs, result count, sorting, pagination, empty state, category banner support, and a standard WooCommerce filter widget area.
- Accessible mobile product-filter drawer behavior in `assets/js/catalog.js`.
- `product-layout-standard` and `product-layout-sticky`.
- Product-aware native search through the WordPress main query and a product search form.
- Catalog, product-card, filter, search, and single-product responsive styling.

Variant selection still uses the safe settings helper layer:

```php
agency_commerce_get_setting( 'shop.card_variant', 'product-card-classic' );
agency_commerce_get_setting( 'product_page.layout', 'product-layout-standard' );
```

Until the CP4 admin settings UI exists, variants can be selected through plugin defaults, theme fallback defaults, filters, or WP-CLI-managed `agency_commerce_core_settings` test values.

CP3 intentionally does not implement admin settings screens, preset application UI, WhatsApp enquiry, product badge editing UI, delivery settings UI, homepage blocks, recently viewed products, cart drawer, checkout customization, My Account customization, wishlist, product comparison, advanced faceted search, custom inventory logic, or custom payment/shipping logic.

## CP3 Test Evidence

Passed:

```sh
composer dump-autoload
composer lint:php
composer lint
npx wp-scripts lint-js wp-content/themes/agency-commerce/assets/js/catalog.js wp-content/themes/agency-commerce/assets/js/navigation.js
npm run build
npm test
npm run env:start
npm run env:setup
npm run env:cli -- wp theme activate agency-commerce
npm run env:cli -- wp theme activate agency-commerce-child
npm run env:cli -- wp plugin activate agency-commerce-core woocommerce
npm run env:cli -- wp plugin deactivate agency-commerce-core
npm run env:cli -- wp plugin deactivate woocommerce
curl http://localhost:8888/shop/
curl http://localhost:8888/product-category/cp3-apparel/
curl http://localhost:8888/product/cp3-simple-product/
curl http://localhost:8888/product/cp3-variable-product/
curl 'http://localhost:8888/?s=CP3&post_type=product'
curl http://localhost:8888/wp-admin/
```

Browser checks passed with Playwright:

- `product-card-classic` rendered.
- `product-card-minimal` rendered.
- `product-card-compact` rendered.
- Simple, variable, sale, featured, and out-of-stock products rendered.
- Sale, featured, and out-of-stock badges rendered.
- Simple products used WooCommerce quick-add markup.
- Variable and unsupported products used the view-product fallback.
- Shop archive rendered result count, ordering, standard filter widget output, pagination, and product cards.
- Product category archive rendered breadcrumb, category banner, description, and product cards.
- Product search results rendered product cards through native WooCommerce search behavior.
- Empty product search rendered the archive empty state.
- Mobile filter drawer moved focus inside, trapped focus, closed on Escape, and restored focus to the trigger.
- Standard product layout rendered gallery, summary, quantity, native add-to-cart, tabs, metadata, and related products.
- Sticky product layout rendered and remained usable at desktop and mobile widths.
- Variable product form rendered and accepted a variation selection.
- Responsive passes ran at mobile `390px`, tablet `768px`, and desktop `1440px`.
- Browser console produced no errors during the CP3 flow.

Compatibility checks passed:

- Parent theme served `200 OK`.
- Parent theme served `200 OK` with the companion plugin inactive.
- Parent theme served `200 OK` with WooCommerce inactive.
- Child theme served `200 OK` with WooCommerce restored active.
- `wp-admin` redirected to login and login returned `200 OK`.
- A fresh `debug.log` check after compatibility requests showed no PHP warnings.

Failed or warning-only:

```sh
npm audit --audit-level=high
```

The audit command still fails with the known transitive development dependency advisories under `@wordpress/scripts`: 28 total findings, 22 moderate and 6 high. No production runtime dependency was added in CP3.

## CP4A Commerce Framework Settings Infrastructure

CP4A introduces only settings infrastructure:

- `Commerce Framework` admin menu.
- WordPress Settings API registration for the single `agency_commerce_core_settings` option.
- Tabbed settings interface for store identity, header, shop, product page, footer, social links, design tokens, performance, and developer options.
- Tab-scoped saves so one tab does not erase another tab's values.
- Sanitization and validation for text, textarea, email, URL, checkbox, select, bounded number, color, and font-token inputs.
- Capability checks and nonce verification for settings saves.
- Memoized settings retrieval through `agency_commerce_core_get_settings()` and dot-notation reads through `agency_commerce_core_get_setting()`.
- Parent-theme fallback defaults through `agency_commerce_get_setting()` when the plugin is inactive.
- Settings-backed selectors for header, footer, product-card, and product-layout variants.
- Settings-backed sticky header, products-per-row, products-per-page, filter layout, color token overrides, font token overrides, social links, store identity, performance, and developer toggles.

CP4A intentionally does not implement WhatsApp, product badges, delivery estimates, presets, checkout options, homepage blocks, cart drawer, recently viewed products, size guide, or product enquiries.

## CP4B Core Commerce Features

CP4B adds reusable commerce features on top of the existing settings infrastructure:

- Framework-only presets for Minimal, Fashion, and Electronics with preview-before-apply and restore-previous behavior.
- Reusable product badges with built-in and custom labels stored in product metadata.
- Delivery estimate messaging with global, category, and product-level overrides.
- WhatsApp enquiry support with configurable phone, button text, and message template placeholders.
- Static admin preview thumbnails for header, footer, product-card, and product-layout variants.
- Public helper, filter, action, and component-contract documentation for the new extension surface.

CP4B does not implement homepage blocks, checkout customization, cart drawer, recently viewed products, size guide, wishlist, compare, My Account redesign, product recommendations, or AI features.

## CP4B Test Evidence

Use this verification set for CP4B:

```sh
git status --short
composer dump-autoload
composer lint:php
composer lint
npm run lint:js
npm run build
npm test
npm run test:admin
npm run test:storefront
npm run env:cli -- wp option get agency_commerce_core_settings --format=json
```

The admin smoke test should confirm variant persistence after logout/login, preset preview thumbnails, preset apply/restore, and no PHP warnings or browser console errors. The storefront smoke test should confirm badge rendering, delivery overrides, WhatsApp URLs, variable-product variation handling, social links, responsive layouts, and inactive-state fallbacks.

## CP4A Test Evidence

Use this verification set for CP4A:

```sh
git status --short
composer dump-autoload
composer lint:php
composer lint
npm run lint:js
npm run build
npm test
npm run env:start
npm run env:setup
npm run env:cli -- wp plugin activate agency-commerce-core woocommerce
npm run env:cli -- wp theme activate agency-commerce
npm run test:admin
npm run env:cli -- wp option get agency_commerce_core_settings --format=json
npm run env:cli -- wp plugin deactivate agency-commerce-core
curl -I -L --max-time 20 http://localhost:8888/
npm run env:cli -- wp plugin activate agency-commerce-core
npm run env:cli -- wp theme activate twentytwentyfive
curl -I -L --max-time 20 'http://localhost:8888/wp-admin/admin.php?page=agency-commerce-framework'
npm run env:cli -- wp theme activate agency-commerce-child
npm run env:cli -- wp plugin deactivate woocommerce
curl -I -L --max-time 20 http://localhost:8888/
npm run env:cli -- wp plugin activate woocommerce
```

`npm run test:admin` saves every CP4A settings tab through wp-admin and checks that an invalid header variant injected in the browser is rejected by the Settings API sanitizer.

Failed or warning-only:

```sh
npm audit --audit-level=high
```

The audit command is still expected to fail with the known transitive development dependency advisories under `@wordpress/scripts` until the toolchain advisories are intentionally resolved.

## Settings Schema Migrations

The current settings schema version is declared by `AGENCY_COMMERCE_CORE_SETTINGS_SCHEMA_VERSION` in `agency-commerce-core.php`. The installed schema version is stored separately from the settings blob in the `agency_commerce_core_settings_schema_version` option.

`AgencyCommerceCore\Migrations\SettingsMigrationManager` runs migrations in ascending order. Each migration updates the stored schema version only after its `run()` method completes. The first migration, `Version1`, is intentionally a no-op marker so CP4A settings survive the introduction of versioning without changes.

`Version3` is the CP4C badge metadata migration. It converts legacy `_agency_commerce_badge_custom_labels` arrays to canonical newline-separated text. Runtime badge rendering still accepts both arrays and newline text so stores remain compatible even before migration runs.

## Runtime Assets

Runtime assets are built with:

```sh
npm run build
```

The build writes `frontend`, `catalog`, and tooling `index` entries to `wp-content/themes/agency-commerce/build`, and the `admin` entry to `wp-content/plugins/agency-commerce-core/build`. Theme and plugin enqueue code read the generated `.asset.php` files for dependencies and cache-busting versions. Source CSS/JS remains as a development fallback when build artifacts are absent.

Future checkpoints must use migrations when they add, remove, rename, split, combine, or transform saved settings. The required process is:

1. Add a new migration class under `wp-content/plugins/agency-commerce-core/includes/Migrations/`.
2. Implement `SettingsMigrationInterface`.
3. Return a new integer from `version()`.
4. Register the migration in `SettingsMigrationManager::migrations()`.
5. Increment `AGENCY_COMMERCE_CORE_SETTINGS_SCHEMA_VERSION`.
6. Preserve old settings values unless the migration explicitly transforms them.
7. Document the migration and test evidence here and in `CHANGELOG.md`.

Never change existing setting names or values outside a migration.

## Visual Regression Workflow

The visual suite is configured in `tests/visual/playwright.config.js` and implemented in `tests/visual/visual.spec.js`.

Capture or refresh baselines:

```sh
npm run visual:baseline
```

Compare the current site against committed baselines:

```sh
npm run visual:compare
```

Baselines are committed under `tests/visual-baseline/desktop`, `tests/visual-baseline/tablet`, and `tests/visual-baseline/mobile`. The suite captures:

- Homepage
- Shop
- Product category
- Simple product
- Variable product
- Cart
- Checkout
- My Account

The default diff threshold is `0.01`. Set `VISUAL_DIFF_THRESHOLD=0` for exact comparison or a higher value for controlled, documented rendering noise.

CP4D reviewed and updated all 24 visual baselines after CP4C. The approved baseline changes are limited to:

- corrected WooCommerce product-card grid widths after overriding core product-loop float, margin, and width rules;
- CP4B reusable product badges and product summary extension content on catalog/product screenshots;
- CP4C runtime asset output from the production build pipeline;
- default footer-column rendering after the visual suite was made deterministic.

The visual suite now resets mutable framework options, active presets, preset snapshots, active theme/plugin state, and CP4B product badge/delivery metadata before screenshots. This prevents `npm run test:admin` or `npm run test:storefront` from changing visual results through persistent local database state.

Baselines updated in CP4D:

- `tests/visual-baseline/desktop/homepage.png`
- `tests/visual-baseline/desktop/shop.png`
- `tests/visual-baseline/desktop/product-category.png`
- `tests/visual-baseline/desktop/simple-product.png`
- `tests/visual-baseline/desktop/variable-product.png`
- `tests/visual-baseline/desktop/cart.png`
- `tests/visual-baseline/desktop/checkout.png`
- `tests/visual-baseline/desktop/my-account.png`
- `tests/visual-baseline/tablet/homepage.png`
- `tests/visual-baseline/tablet/shop.png`
- `tests/visual-baseline/tablet/product-category.png`
- `tests/visual-baseline/tablet/simple-product.png`
- `tests/visual-baseline/tablet/variable-product.png`
- `tests/visual-baseline/tablet/cart.png`
- `tests/visual-baseline/tablet/checkout.png`
- `tests/visual-baseline/tablet/my-account.png`
- `tests/visual-baseline/mobile/homepage.png`
- `tests/visual-baseline/mobile/shop.png`
- `tests/visual-baseline/mobile/product-category.png`
- `tests/visual-baseline/mobile/simple-product.png`
- `tests/visual-baseline/mobile/variable-product.png`
- `tests/visual-baseline/mobile/cart.png`
- `tests/visual-baseline/mobile/checkout.png`
- `tests/visual-baseline/mobile/my-account.png`

## Framework Quality Checkpoint

This earlier checkpoint introduced the schema and visual quality infrastructure:

- Settings schema versioning.
- A no-op first settings migration.
- Playwright visual baseline capture and comparison.
- A compatibility `sidebar.php` template to avoid classic theme sidebar lookup noise.
- Documentation for future migration and visual workflows.

Use this verification set:

```sh
git status --short
composer dump-autoload
composer lint:php
composer lint
npm run lint:js
npm run build
npm test
npm run env:start
npm run env:setup
npm run env:cli -- wp plugin activate agency-commerce-core woocommerce
npm run env:cli -- wp theme activate agency-commerce
npm run test:admin
npm run visual:baseline
npm run visual:compare
npm run env:cli -- wp option get agency_commerce_core_settings_schema_version
npm run env:cli -- wp option get agency_commerce_core_settings --format=json
```

Also verify that existing settings survive deleting/recreating the schema-version option:

```sh
npm run env:cli -- wp option delete agency_commerce_core_settings_schema_version
npm run env:cli -- wp option get agency_commerce_core_settings_schema_version
npm run env:cli -- wp option get agency_commerce_core_settings --format=json
```

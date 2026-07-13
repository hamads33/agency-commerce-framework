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

## Lint And Test

```sh
composer dump-autoload
composer lint:php
composer lint
npm test
npm audit --audit-level=high
```

`composer lint:php` runs `php -l` across `wp-content`. `composer lint` runs PHPCS with WordPress Coding Standards.

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

- The PHPCS config excludes the `WordPress.Files.FileName` sniff because CP1 uses Composer-style PSR-4 class file paths for the namespaced plugin.
- The theme screenshot is not provided in CP1.
- `npm audit --audit-level=high` remains a CP0-known development-tool audit failure.

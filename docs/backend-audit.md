# Backend audit

**FREEZE BLOCKED.** See the [candidate freeze](backend-freeze-v1.md) and [regression matrix](backend-regression-matrix.md).

## Source and isolation

- Original branch `main`, tracking `origin/main`, at `c010ffbfa44781a0edf98e7544c720858e0e25b1`. Git history was available. The original working tree had a README edit and in-progress relocation of documentation into `docs/`. The audit used a separate worktree and branch, `audit/backend-freeze`, from the committed source. No original edits were applied or reverted.
- Reviewed the committed Architecture, Development, Known Limitations and Security Exception documents and the corresponding plugin, theme, test and release sources. Plugin services own settings, product extensions, homepage blocks, checkout controls and recently viewed REST rendering. Theme PHP owns presentation, catalog query adaptation, WooCommerce hook adaptation and compatibility/fallbacks. WooCommerce handles purchase processing, product/order persistence and account lifecycle.
- Development pins are WordPress 7.0.1, WooCommerce 10.9.4, PHP 8.3, Node 24.14.0 and npm 11.9.0. Locked test tools are Playwright 1.61.1, wp-env 11.10.0 and wp-scripts 32.6.0. The host supplied Node 24.19.0 and npm 11.17.0, triggering an engine warning. The PHP tooling container supplied 8.3.33 and Composer 2.11.2. No lockfile was changed.
- The host had no PHP or Composer executable. Docker Desktop 29.7.2 was initially stopped and was started for this audit. The audit uses separate Compose projects, data directories and HTTP ports. Existing containers and site databases were outside the test scope.

## Test evidence

The raw command logs are in `/tmp/agency-backend-audit/`. Browser commands were attempted during preflight on an unused local port with a separate wp-env data directory. A preflight failure before setup is environmental evidence only.

| Command | Recorded preflight outcome |
| --- | --- |
| `npm ci` | Engine warning and registry/audit-metadata delay. Original process was stopped and installation retried successfully (exit 0) from the same lockfile with `--no-audit --prefer-offline`. |
| `composer install` | Host executable missing. Container retry with the WordPress PHP 8.3 image and its ZIP extension installed the locked packages. The generic PHP image lacked ZIP/unzip. |
| `composer lint:php` | Passed inside PHP 8.3.33 for PHP under `wp-content`. |
| `composer lint` | Passed inside the WordPress PHP 8.3 container after the locked Composer install. |
| `npm run env:start`, `npm run test:backend` | The gate invoked wp-env. Docker Compose stalled pulling `mariadb:lts` and `phpmyadmin`. The audit stopped the stalled pull (exit 130) after more than six minutes. The gate did not reach fixture setup. |
| `npm run build`, `npm run lint:js` | Both exited 0 on the installed lockfile. |
| `npm run test:admin` | Exited before assertions. Locked Chromium headless shell revision 1228 was missing. |
| `npm run test:storefront` | Exited before assertions. wp-env binary was unavailable during dependency setup. |
| `npm run test:homepage` | Same wp-env setup failure. |
| `npm run test:cart-flow` | Same wp-env setup failure. |
| `npm run test:checkout-block` | Same wp-env setup failure. |
| `npm run test:checkout-classic` | Same wp-env setup failure. |
| `npm run test:account` | Same wp-env setup failure. |
| `npm run test:cp5-audit` | Same wp-env setup failure. |
| `npm run test:visual`, `npm run test:showcase` | Playwright executable was unavailable during dependency setup. Reviewed screenshot scope separately. |

`npm test` runs the asset build. It provides build evidence only.

## Findings

- **Test defect**. Existing storefront code used numeric post ID 14 for the simple product. The audit resolves the fixture by slug and fails explicitly when missing. Added catalogue fixtures cover the named products that the existing browser suites expect. The first independent fixture run also exposed two test expectations: WooCommerce formats aggregate variation prices as decimal strings such as `45.00`, and the Store API returns HTTP 201 for adding a cart item. Both expectations were corrected.
- **Backend defect reproduced and corrected**. v3 does not check the return from `update_post_meta()`. The manager advances the schema option when `run()` returns, so a rejected write can leave legacy data behind a current schema marker. Added a fault-injection regression that checks preservation of the old marker and recovery on retry. The regression failed in the isolated fallback before the fix and passed after it. A migration now reports success explicitly, the manager stops if a data write or schema write fails, and legacy reads still accept arrays.
- **Missing test coverage**. Block checkout UI tests do not place orders. The added Store API regression passed against the isolated store. It verified guest variation add/update/remove, a $5 coupon, an $85 total and the exact persisted COD order and line item. The original Classic audit checked the latest order by date. The revised test uses the order-received URL ID and checks billing email, support metadata and the purchased line. Account registration, full administrative request coverage and order/customer isolation remain explicit gaps. Product-save hook checks passed for subscriber access denial, invalid nonce rejection and sanitized administrator writes.
- **Security source review**. Preset handlers use capabilities and action-bound nonces. Product and category meta handlers validate capabilities and nonces and route writes through sanitization. The public recently viewed REST endpoint validates bounded IDs and limits its read path to published visible product cards. These are code observations. They do not replace independent authorization, sanitization and ownership tests.
- **Dependency audit**. `composer audit --locked` exited 1 with high findings in root development-only PHPCSUtils 1.2.2 (CVE-2026-65954), PHP_CodeSniffer 3.13.5 (CVE-2026-67434) and WPCS 3.3.0 (CVE-2026-45293). `npm audit --omit=dev --json` reported zero production npm findings. `npm audit --json` found 83 development-tree findings (42 moderate, 37 high and four critical: @simple-git/argv-parser, proxy-addr, shell-quote and simple-git). Both dependency roots are development tooling and the release script excludes `node_modules` and `vendor`. The npm production-only result does not cover WordPress, WooCommerce or compiled assets.


## Isolated fallback

A separate Docker Compose instance installed WordPress 7.0.1, activated WooCommerce 10.9.4 and Agency Commerce Core, and switched to the parent theme. It used PHP 8.3.36 and MariaDB 11.4.13 with a loopback-only HTTP binding. A pre-fixture SQL export was saved at `~/.cache/agency-backend-audit/fallback/before-fixtures.sql`. A second fallback used the same releases and a container volume for the WordPress files. It exported its own pre-fixture SQL to `~/.cache/agency-backend-audit/fallback/fast-before-fixtures.sql`. An early WooCommerce textdomain loading notice appeared during theme activation. The fallback uses the repository's WordPress and WooCommerce versions and different container wiring from wp-env.

# Backend audit

See [extended verification](test-evidence.md) for the latest security, release and compatibility evidence.

**FREEZE BLOCKED.** See the [candidate freeze](backend-freeze-v1.md) and [regression matrix](backend-regression-matrix.md).

## Source and isolation

- Original branch `main`, tracking `origin/main`, at `c010ffbfa44781a0edf98e7544c720858e0e25b1`. Git history was available. The original working tree had a README edit and in-progress relocation of documentation into `docs/`. The audit used a separate worktree and branch, `audit/backend-freeze`, from the committed source. The audit branch and its installed dependencies now reside in the original `agency-commerce-framework/` folder. The temporary worktree was removed, and the existing documentation edits were preserved.
- Reviewed the committed Architecture, Development, Known Limitations and Security Exception documents and the corresponding plugin, theme, test and release sources. Plugin services own settings, product extensions, homepage blocks, checkout controls and recently viewed REST rendering. Theme PHP owns presentation, catalog query adaptation, WooCommerce hook adaptation and compatibility/fallbacks. WooCommerce handles purchase processing, product/order persistence and account lifecycle.
- Development pins are WordPress 7.0.1, WooCommerce 10.9.4, PHP 8.3, Node 24.14.0 and npm 11.9.0. Locked test tools are Playwright 1.61.1, wp-env 11.10.0 and wp-scripts 32.6.0. The host supplied Node 24.19.0 and npm 11.17.0, triggering an engine warning. The PHP tooling container supplied 8.3.33 and Composer 2.11.2. No lockfile was changed.
- The host had no PHP or Composer executable. Docker Desktop 29.7.2 was initially stopped and was started for this audit. The audit uses separate Compose projects, data directories and HTTP ports. Existing containers and site databases were outside the test scope.

## Test evidence

The raw command logs are in `/tmp/agency-backend-audit/`. Browser commands were attempted during preflight on an unused local port with a separate wp-env data directory. A preflight failure before setup is environmental evidence only.

| Command | Recorded preflight outcome |
| --- | --- |
| `npm ci` | Engine warning and registry/audit-metadata delay. Original process was stopped and installation retried successfully with `--no-audit --prefer-offline`. A fresh, separate dependency directory subsequently passed exact `npm ci` with Node 24.14.0 and npm 11.9.0. |
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
- **Missing test coverage**. Block checkout UI tests do not place orders. The added Store API regression passed against the isolated store. It verified guest variation add/update/remove, a $5 coupon, an $85 total and the exact persisted COD order and line item. The original Classic audit checked the latest order by date. The revised test uses the order-received URL ID and checks billing email, support metadata and the purchased line. Account registration and login passed on retry. Full administrative request coverage and authenticated cross-customer isolation remain open. Product-save hook checks passed for subscriber access denial, invalid nonce rejection and sanitized administrator writes.
- **Security source review**. Preset handlers use capabilities and action-bound nonces. Product and category meta handlers validate capabilities and nonces and route writes through sanitization. The public recently viewed REST endpoint validates bounded IDs and limits its read path to published visible product cards. These are code observations. They do not replace independent authorization, sanitization and ownership tests.
- **Dependency audit**. `composer audit --locked` exited 1 with high findings in root development-only PHPCSUtils 1.2.2 (CVE-2026-65954), PHP_CodeSniffer 3.13.5 (CVE-2026-67434) and WPCS 3.3.0 (CVE-2026-45293). `npm audit --omit=dev --json` reported zero production npm findings. `npm audit --json` found 83 development-tree findings (42 moderate, 37 high and four critical: @simple-git/argv-parser, proxy-addr, shell-quote and simple-git). Both dependency roots are development tooling and the release script excludes `node_modules` and `vendor`. The npm production-only result does not cover WordPress, WooCommerce or compiled assets.


## Isolated fallback

A separate Docker Compose instance installed WordPress 7.0.1, activated WooCommerce 10.9.4 and Agency Commerce Core, and switched to the parent theme. It used PHP 8.3.36 and MariaDB 11.4.13 with a loopback-only HTTP binding. A pre-fixture SQL export was saved at `~/.cache/agency-backend-audit/fallback/before-fixtures.sql`. A second fallback used the same releases and a container volume for the WordPress files. It exported its own pre-fixture SQL to `~/.cache/agency-backend-audit/fallback/fast-before-fixtures.sql`. An early WooCommerce textdomain loading notice appeared during theme activation. The fallback uses the repository's WordPress and WooCommerce versions and different container wiring from wp-env.

## Live fallback results

The WordPress and WooCommerce releases matched the repository pins. The fallback MariaDB, Apache container and WP-CLI wiring differed from wp-env. Browser runs used the locked Playwright 1.61.1 and its Chromium headless shell revision 1228. Build and JavaScript lint also passed after provisioning the intended Node 24.14.0 and npm 11.9.0. PHP syntax lint and PHPCS passed after the migration fix.

| Check | Result |
| --- | --- |
| `test:admin` | Partial evidence. Saved and rendered all nine header, footer, product-card and product-layout variants. The 210-second runner limit closed the browser during the later persistence pass, before preset apply/rollback validation. |
| `test:storefront` | Partial evidence. Reached the inactive-state cleanup after social links, built assets, badges and legacy migration, delivery, variable WhatsApp, search, compatibility helpers and responsive checks. The 235-second runner limit interrupted theme restoration in the cleanup path. A second run hit its 210-second limit while opening the inactive-state browser context. The suite has no complete exit-zero run. |
| `test:homepage` | PASS. Checked block/pattern registration and homepage rendering. |
| `test:cart-flow` | PASS. Checked empty and populated cart and quantity controls. |
| `test:checkout-block` | PASS after fixing the hard-coded product ID. Earlier attempts selected a variable parent or out-of-stock product and WooCommerce rejected the add. |
| `test:checkout-classic` | PASS on the volume-backed fallback. Checked page switching, backup presence and rendering. A separate slower fallback attempt timed out. |
| `test:account` | PASS after making the registration test emit the password input events WooCommerce listens for. Verified registration, stored customer role and login. |
| `test:cp5-audit` | PASS on retry. Covered repeated mode switching and backup restore, unauthorized/invalid mode handling, recently viewed restrictions, Classic minimum-order rejection and a local COD order. Independent WooCommerce CRUD read of order 49 confirmed guest ownership, $25.00 total, one $25 line, zero shipping and tax, and framework support metadata. |
| Store API COD order | PASS. Verified guest variation add/update/remove, coupon, exact total and persisted order. |
| Parent theme inactive | PASS in a focused WordPress check. Core and WooCommerce stayed active under Twenty Twenty-Five, and recently viewed used its theme-independent renderer. The parent theme was restored afterward. |
| Preset manager | PASS in a focused WordPress test. Preserved the complete settings snapshot and restored both effective settings and the previous active preset. This covers manager behavior, while the admin UI flow remains partial. |
| Product metadata save | PASS. Rejected subscriber and invalid nonce writes. Persisted sanitized values for an administrator. |
| Migration recovery | PASS after the backend fix. Fault injection rejected both badge data and schema-marker writes in separate runs. The previous marker was retained, retry completed and the saved settings fixture was preserved. |
| Public recently viewed and admin authentication | PASS in a focused HTTP check. Valid request returned product markup with no-store caching. Invalid IDs and over-limit input returned 400. A guest settings POST redirected to WordPress login. Guest requests for the Classic order through the WooCommerce REST and Store APIs and for the REST customer collection returned 401 with no fixture personal data. |
| Visual and showcase review | Discovery passed with 24 visual cases and 63 showcase cases. Screenshot assertions were outside the verified backend results. |

Audit code commit: `2a7cda123cc781575d80b44c477e3d230fa4e134`. The source baseline remains `c010ffbfa44781a0edf98e7544c720858e0e25b1`. Persistent evidence is in `~/.cache/agency-backend-audit/logs/`. Neither commit was tagged as a verified freeze.

## Freeze decision

The isolated fallback verified both order paths and migration recovery. The repository wp-env gate is still unverified end to end. The admin and storefront suites have partial evidence and require complete runs. The remaining security gaps include authenticated cross-customer isolation and broader administrative authorization and sanitization cases. Keep frontend readiness on hold until these gates are closed.

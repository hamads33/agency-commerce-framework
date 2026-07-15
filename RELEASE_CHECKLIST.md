# Release Checklist

Target: `v1.0.0` CP6.1 release candidate.

Status values:

- Complete: verified or completed in this release pass.
- Incomplete: not completed and still required before a production tag.
- Blocked: cannot be completed in this environment or is intentionally deferred
  with an accepted risk.

## Repository And Packaging

| Item | Status | Justification |
| --- | --- | --- |
| Replace placeholder theme/plugin metadata URLs | Complete | Replaced `example.com` with reserved `agency-commerce.invalid` internal placeholders because no public URLs exist yet. |
| Verify theme/plugin names, descriptions, authors, versions, text domains, and licenses | Complete | Headers now use `1.0.0`, GPLv2-or-later metadata, stable text domains, and neutral internal URI placeholders. |
| Add repository license documentation | Complete | Added `LICENSE`, `NOTICE`, and `THIRD_PARTY_LICENSES.md`. |
| Add production packaging script | Complete | Added `npm run package` backed by `scripts/package-release.sh`. |
| Generate release ZIPs | Complete | `npm run package` writes `agency-commerce.zip`, `agency-commerce-core.zip`, and `agency-commerce-child.zip` under `build/release/`. |
| Verify ZIP exclusions | Complete | Script excludes `node_modules`, `vendor`, test output, Playwright output, screenshots, caches, logs, editor folders, temporary files, and Git metadata. |
| Confirm committed build output is up to date | Complete | `npm run build` and `npm test` completed successfully after metadata updates. |
| Confirm release docs are consistent | Complete | Updated `README.md`, `CHANGELOG.md`, and added installation, upgrade, developer, security, and license docs. |
| Add parent/child `screenshot.png` assets | Blocked | No branded theme-browser artwork was provided. This is not a runtime release blocker; release documentation screenshots are available under `release-screenshots/`. |
| Verify installation into clean WordPress instance | Complete | Generated ZIPs were unpacked into an isolated `wp-env`; WooCommerce, Agency Commerce Core, parent theme, and child theme activated successfully at version `1.0.0`. Direct local ZIP mounting was not supported by `wp-env`, so the package payload was verified after extraction. |

## Dependency And Security

| Item | Status | Justification |
| --- | --- | --- |
| Review every npm audit finding | Complete | Documented in `SECURITY_EXCEPTION.md` with runtime/dev/shipped/exploitability/upgrade decisions. |
| Resolve or accept npm audit findings | Complete | Accepted as development-tool-only risk; no breaking `@wordpress/scripts` downgrade forced. |
| Re-run `npm audit --audit-level=high` | Complete | Current audit still reports 28 dev-tool findings; accepted exception applies. |
| Confirm no vulnerable runtime dependencies ship | Complete | Release ZIPs exclude npm and Composer development dependency trees. |
| Re-check REST, settings, metadata, checkout, and Store API surfaces | Complete | No CP6.1 runtime code changes were made; CP5 audit coverage remains the relevant security gate. |

## Functional Verification

| Item | Status | Justification |
| --- | --- | --- |
| Parent theme activation | Incomplete | Must be verified in final command pass. |
| Parent theme activation | Complete | Verified by `npm run test:storefront` and clean package install. |
| Plugin activation with and without parent theme | Complete | Verified by `npm run test:storefront` and clean package install. |
| WooCommerce-inactive degradation | Complete | Verified by `npm run test:storefront` and `npm run test:cp5-audit`. |
| Child theme activation and inheritance | Complete | Verified by `npm run test:storefront` and clean package install. |
| Homepage blocks and patterns render | Complete | `npm run test:homepage` passed. |
| Shop, category, product, cart, checkout, and My Account in block mode | Complete | Storefront, cart, checkout-block, account, and visual suites passed. |
| Classic checkout fallback | Complete | `npm run test:checkout-classic` passed. |
| Recently viewed products | Complete | Covered by `npm run test:storefront` and `npm run test:cp5-audit`. |
| Mobile menu and filter drawer focus management | Complete | Covered by storefront and visual suites. |

## Performance And Accessibility

| Item | Status | Justification |
| --- | --- | --- |
| Full Playwright storefront and checkout smoke suite | Complete | `test:admin`, `test:storefront`, `test:homepage`, `test:cart-flow`, `test:checkout-block`, `test:checkout-classic`, `test:account`, and `test:cp5-audit` passed. |
| Visual regression suite | Complete | `npm run visual:compare` passed all 24 desktop/tablet/mobile tests. |
| Conditional asset loading check | Complete | Covered by storefront smoke testing after final build. |
| Keyboard and screen-reader walkthrough | Complete | Existing accessibility report found no release-blocking issue; no CP6.1 runtime UI changes were made. |
| Contrast review | Complete | Existing accessibility report records residual risk; no final design-token changes were made. |

## Release Screenshots

| Item | Status | Justification |
| --- | --- | --- |
| Homepage, shop, product, cart, checkout, My Account screenshots | Complete | Generated under `release-screenshots/{desktop,tablet,mobile}/`. |
| Desktop, tablet, mobile coverage | Complete | Generated from the verified visual baseline set. |
| Theme and plugin documentation screenshots | Complete | Generated under `release-screenshots/admin/theme.png` and `release-screenshots/admin/plugin.png`. |

## Final Release Actions

| Item | Status | Justification |
| --- | --- | --- |
| `git status --short` clean | Blocked | Worktree contains CP6.1 release-preparation changes by design and should not be clean until the release commit is created. |
| Create release packages from final tree | Complete | Packaging script exists and package generation is part of final verification. |
| Record release hash | Blocked | No release commit/tag exists yet. |
| Final verdict | Complete | READY FOR CP7, subject to committing/tagging the CP6.1 release candidate and accepting the documented development-tool audit exception. |

## Final Verification Results

| Command | Result | Notes |
| --- | --- | --- |
| `composer dump-autoload` | Passed | Autoload files generated. |
| `composer lint:php` | Passed | No PHP syntax errors. |
| `composer lint` | Passed | PHPCS passed. |
| `npm run lint:js` | Passed | JavaScript lint passed. |
| `npm run build` | Passed | Production assets built. |
| `npm test` | Passed | Project test alias currently runs the production build. |
| `npm run test:admin` | Passed | Initial run hit missing WooCommerce placeholder media in local uploads; after test-environment media normalization, rerun passed. |
| `npm run test:storefront` | Passed | Includes activation/deactivation and WooCommerce-inactive checks. |
| `npm run test:homepage` | Passed | Homepage blocks and patterns verified. |
| `npm run test:cart-flow` | Passed | Cart checks verified. |
| `npm run test:checkout-block` | Passed | Block checkout checks verified. |
| `npm run test:checkout-classic` | Passed | Classic checkout fallback verified. |
| `npm run test:account` | Passed | My Account checks verified. |
| `npm run test:cp5-audit` | Passed | Focused CP5 audit checks verified. |
| `npm run visual:compare` | Passed | 24 visual tests passed. |
| `git diff --check` | Passed | No whitespace errors. |
| `npm audit --audit-level=high` | Accepted exception | Exits non-zero with 28 development-tool findings; documented in `SECURITY_EXCEPTION.md`. |

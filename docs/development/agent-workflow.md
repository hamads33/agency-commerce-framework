# Agent workflow

Run `npm ci` and `composer install`. Use `npm run build` before browser checks. Install Chromium using `npx playwright install --with-deps --only-shell chromium` on a test host that needs its system dependencies. The [regression gate](../frontend-rebuild-readiness.md) provisions its own WordPress database and seeds WooCommerce fixtures. It does not target the default local store or a client installation.

| Scope | Minimum verification |
| --- | --- |
| PHP service | `composer lint:php`, `composer lint`, affected integration fixture |
| Orders, checkout, inventory | `npm run test:backend`, with Classic and Block order and ownership assertions |
| Settings, presets, migrations | Admin and migration fixtures plus admin browser suite |
| REST | Valid, malformed, private and unauthorized route cases |
| Gutenberg | Build, homepage checks, existing saved-content fixture |
| Product template or shared components | Build, related storefront checks and optional visual comparisons |
| Child theme | Build, relevant storefront checks and showcase review |
| Packaging | Build, `npm run package`, clean-install and upgrade smoke |

For pull requests, keep build products in sync and include evidence from the affected tests. Before release, run two fresh backend gates and verify the release ZIPs on disposable staging. A browser timeout or Docker pull failure is an incomplete run. Inspect the suite log, and use the latest database snapshot only within the corresponding isolated environment.

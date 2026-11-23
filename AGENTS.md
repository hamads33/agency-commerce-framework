# Agency Commerce agent guide

Agency Commerce is a reusable WordPress storefront with WooCommerce as the commerce engine. Read [the architecture map](docs/architecture/overview.md) before changing an integration. Current release evidence lives in [test evidence](docs/test-evidence.md).

| Change | Owner |
| --- | --- |
| Settings, defaults and schema | Core plugin `includes/Settings`, `includes/Admin`, `includes/Migrations` |
| Preset and product/category metadata | Core plugin `includes/Presets` and `includes/Products` |
| Checkout mode, rules and Store API extension | Core plugin `includes/Checkout` |
| Blocks and recently viewed | Core plugin `blocks/`, `includes/Homepage`, `includes/RecentlyViewed` |
| Storefront shells, card presenter and asset enqueue | Parent theme `components/`, `woocommerce/`, `inc/` |
| Client appearance | A child theme, Framework settings and editable WordPress content |
| Tests, assets and releases | `tests/`, `tooling/`, `scripts/`, `.github/workflows/quality.yml` |

Use the Node version in `.nvmrc`, npm in `package.json`, and PHP 8.3 for verification. Run `npm ci`, `composer install`, then the checks in the [agent workflow](docs/development/agent-workflow.md). For full backend verification build assets, install Playwright Chromium and run `npm run test:backend` twice from clean runs. The command prints a private log location. Check its `start.log` first if Docker or wp-env fails.

Follow WordPress coding standards, escape at render time and validate inputs by type and domain. Use capabilities plus nonces for state-changing admin endpoints. WooCommerce owns product and order CRUD, inventory, tax, shipping, totals, payment and account ownership. Keep test payments local and use synthetic fixtures. Use WooCommerce CRUD rather than querying its storage directly.

Use ordered, retryable migrations with preserved data, and persist a schema marker only after the corresponding data operation succeeds. Keep existing public hooks, option keys, block attributes, checkout page metadata and selectors compatible or migrate consumers with regression evidence. Avoid touching customer databases, clearing existing volumes or force-upgrading tooling.

Start new features in the owner listed above. For storefront work follow the [client model](docs/architecture/store-customization.md). For Gutenberg changes, update the metadata, editor source and server renderer together, then build and verify rendering with old and new saved markup. For releases follow the [release gates](docs/production/release-gates.md) and report commands, outcomes, and limitations.

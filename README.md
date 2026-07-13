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
composer lint:php
composer lint
```

See `DEVELOPMENT.md` for pinned versions, WP-CLI commands, reset commands, and checkpoint test evidence. See `HOOKS.md` for the public API contract introduced so far.

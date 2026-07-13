# Agency Commerce Framework

Reusable WooCommerce framework for agency-built stores. The finished framework will consist of:

- `agency-commerce`: parent theme for storefront presentation.
- `agency-commerce-core`: companion plugin for reusable commerce functionality and settings.
- `agency-commerce-child`: example child theme for per-client branding.

CP0 establishes only the repository, tooling, and local WordPress/WooCommerce environment. Theme and plugin features begin in CP1.

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
npm run env:stop
npm run build
composer lint:php
composer lint
```

See `DEVELOPMENT.md` for pinned versions, WP-CLI commands, reset commands, and CP0 test evidence.

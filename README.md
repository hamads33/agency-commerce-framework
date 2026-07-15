# Agency Commerce Framework

Agency Commerce is a reusable WooCommerce framework for agency-built stores. It
ships as a classic parent theme, a companion plugin, and an example child theme:

- `agency-commerce`: parent theme for storefront presentation.
- `agency-commerce-core`: companion plugin for settings, presets, homepage
  blocks, checkout controls, product extensions, and framework integrations.
- `agency-commerce-child`: starter child theme for client branding and safe
  per-store overrides.

CP6.1 prepares the framework as a `1.0.0` release candidate. It does not add new
commerce functionality and does not begin CP7.

## Requirements

- WordPress `7.0.x`
- WooCommerce `10.9.x`
- PHP `8.2+`
- Node.js `24.14.x` and npm `11.9.x` for development
- Composer `2.9.x` for development
- Docker and Docker Compose for the local `wp-env` workflow

## Installation

Install the generated packages:

- `build/release/agency-commerce.zip`
- `build/release/agency-commerce-core.zip`
- `build/release/agency-commerce-child.zip`

Recommended order:

1. Install and activate WooCommerce.
2. Install the parent theme.
3. Install and activate the child theme for client stores.
4. Install and activate the companion plugin.
5. Configure `Commerce Framework` settings.
6. Build the homepage with Agency Commerce patterns and WooCommerce blocks.
7. Configure WooCommerce shipping, taxes, payment gateways, emails, and legal
   pages.

See `INSTALL.md` for full installation details.

## Development Commands

```sh
npm ci
composer install
npm run env:start
npm run env:setup
npm run build
composer lint:php
composer lint
npm run lint:js
npm test
```

Full release verification commands are listed in `DEVELOPER_GUIDE.md` and
`RELEASE_CHECKLIST.md`.

## Packaging

```sh
npm run package
```

The packaging script creates production ZIPs under `build/release/` and excludes
development dependencies, test artifacts, screenshots, caches, logs, editor
folders, temporary files, and Git metadata.

## Child Theme Workflow

Client-specific changes belong in the child theme. Use settings and CSS custom
properties first. If a template override is required, copy only the relevant
component path into the child theme rather than copying the whole parent theme.

## Preset Workflow

The Minimal, Fashion, and Electronics presets write framework settings only.
They do not delete products, pages, menus, media, widgets, users, orders, or
client content.

## Checkout

Block checkout is the default and recommended path. Classic checkout is an
explicit fallback for stores that need shortcode-based WooCommerce checkout
customization. The framework does not replace WooCommerce payment, shipping,
coupon, validation, cart, or order logic.

## Supported Versions

The CP6.1 release candidate is validated against the pinned local stack in
`DEVELOPMENT.md`:

- WordPress `7.0.1`
- WooCommerce `10.9.4`
- PHP `8.3`
- Node.js `24.14.0`
- npm `11.9.0`

## Metadata URLs

The project does not yet have public project pages. Theme, plugin, and author
metadata use reserved `agency-commerce.invalid` URLs as private/internal
placeholders. Replace them only when real public URLs exist.

## Licensing

Agency Commerce is licensed under GPLv2 or later for WordPress and WooCommerce
compatibility. See `LICENSE`, `NOTICE`, and `THIRD_PARTY_LICENSES.md`.

## Dependency Risk

`npm audit --audit-level=high` reports development-tool vulnerabilities through
the `@wordpress/scripts` toolchain. They are not shipped in production ZIPs and
are accepted for CP6.1 rather than forcing a breaking toolchain change. See
`SECURITY_EXCEPTION.md`.

## Documentation

- `INSTALL.md`: installation
- `UPGRADE.md`: update and rollback workflow
- `DEVELOPER_GUIDE.md`: development, testing, packaging, and extension rules
- `ARCHITECTURE.md`: system architecture
- `COMPONENTS.md`: component contracts
- `HOOKS.md`: public API contract
- `CHANGELOG.md`: release history

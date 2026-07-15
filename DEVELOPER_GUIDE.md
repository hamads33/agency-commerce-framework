# Developer Guide

## Architecture Rule

Agency Commerce is a classic WordPress parent theme plus a companion plugin. The
parent theme owns presentation. The plugin owns reusable settings, presets,
blocks, product extensions, checkout controls, and REST behavior. Client stores
customize through a child theme and settings.

## Local Setup

```sh
npm ci
composer install
npm run env:start
npm run env:setup
```

Use Node `24.14.x`, npm `11.9.x`, PHP `8.3`, and Composer `2.9.x` for the
pinned development environment.

## Build And Test

```sh
composer dump-autoload
composer lint:php
composer lint
npm run lint:js
npm run build
npm test
npm run test:admin
npm run test:storefront
npm run test:homepage
npm run test:cart-flow
npm run test:checkout-block
npm run test:checkout-classic
npm run test:account
npm run test:cp5-audit
npm run visual:compare
git diff --check
npm audit --audit-level=high
```

## Packaging

```sh
npm run package
```

The packaging script writes:

- `build/release/agency-commerce.zip`
- `build/release/agency-commerce-core.zip`
- `build/release/agency-commerce-child.zip`

Release ZIPs exclude `node_modules`, repository `vendor`, tests, visual output,
screenshots, caches, logs, editor folders, temporary files, and Git metadata.

## Preset Workflow

Presets write framework settings only. They do not delete content, products,
menus, widgets, orders, users, or media. Presets are starting points and remain
customizable after application.

## Checkout Workflow

Block checkout is the default. Add checkout behavior only through WooCommerce
Store API or documented WooCommerce Blocks integration points. Do not replace
cart, checkout, payment, shipping, coupon, validation, or order logic.

Classic checkout exists only as a compatibility fallback and uses WooCommerce
shortcodes.

## Extension Points

Public hooks, filters, functions, settings keys, REST routes, blocks, and
patterns are documented in `HOOKS.md`. Treat them as the framework's public API
contract.

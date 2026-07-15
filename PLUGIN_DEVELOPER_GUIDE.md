# Plugin Developer Guide

## Plugin Responsibility

`agency-commerce-core` owns settings, presets, admin UI, homepage blocks,
product extensions, checkout controls, recently viewed rendering, and migrations.

## Settings

- Defaults: `AgencyCommerceCore\Settings\Defaults`
- Registry: `AgencyCommerceCore\Settings\Registry`
- Sanitization: `AgencyCommerceCore\Settings\Sanitizer`
- Public reads: `agency_commerce_core_get_setting()`

Settings are stored in `agency_commerce_core_settings`.

## Migrations

Schema migrations live in `includes/Migrations`. Future migrations must be
registered in `SettingsMigrationManager` and must preserve existing values unless
explicitly documented.

## Product Extensions

Badge, delivery, and WhatsApp rendering are reusable extension layers. Product
cards and product layouts should call the shared helpers rather than duplicating
logic.

## Checkout

Block checkout integration exposes support/trust/minimum-order data to the
WooCommerce Blocks integration surface. Classic checkout controls use supported
WooCommerce PHP hooks.

Do not replace Store API, payment, shipping, validation, coupon, cart, or order
processing.

## REST

The plugin exposes one public render endpoint for recently viewed products. It
does not store personal browsing history in the database.

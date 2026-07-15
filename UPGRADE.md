# Upgrade Guide

## Supported Upgrade Path

Upgrade the parent theme, child theme, and companion plugin as a coordinated
release set. The CP6.1 release candidate is version `1.0.0`.

## Before Upgrading

1. Back up the database and `wp-content`.
2. Confirm WooCommerce is active and compatible.
3. Record the active checkout mode from `Commerce Framework > Checkout`.
4. Confirm any client customizations live in the child theme, not the parent
   theme or plugin.

## Upgrade Steps

1. Upload the new `agency-commerce.zip` parent theme.
2. Upload the new `agency-commerce-core.zip` plugin.
3. Upload the new `agency-commerce-child.zip` only if the example child theme is
   being used directly and has not been client-customized.
4. Clear object/page caches.
5. Visit `Commerce Framework` once as an administrator so settings migrations
   can run.
6. Test homepage, shop, product, cart, checkout, and My Account.

## Child Theme Workflow

Client-specific CSS, template overrides, and hooks belong in the child theme.
Do not edit the parent theme or plugin directly. Override only the component
file needed, such as:

```text
components/product-card/product-card-minimal.php
```

Prefer CSS custom properties and settings over PHP overrides.

## Rollback

Restore the previous theme/plugin ZIPs and the database backup together. If
checkout mode switching was interrupted, the plugin preserves block and classic
page-content backups in post meta for recovery.

## Breaking Changes

No breaking runtime changes are introduced in CP6.1. The release changes are
metadata, licensing, documentation, packaging, and release-verification work.

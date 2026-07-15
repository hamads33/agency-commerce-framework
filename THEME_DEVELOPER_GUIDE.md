# Theme Developer Guide

## Parent Theme Responsibility

The parent theme owns presentation structure, WooCommerce template shells,
component routing, asset loading, and design-token consumption.

## Child Theme Responsibility

Child themes own client/store presentation. They may:

- enqueue child CSS;
- override CSS tokens;
- filter default settings;
- override focused component templates;
- add safe presentation hooks.

They must not add business-critical commerce behavior.

## Component Resolution

`agency_commerce_component_path()` uses `locate_template()`, so child themes can
override parent components by matching paths.

## WooCommerce Template Policy

Parent overrides are limited to:

- `wp-content/themes/agency-commerce/woocommerce/archive-product.php`
- `wp-content/themes/agency-commerce/woocommerce/content-product.php`
- `wp-content/themes/agency-commerce/woocommerce/content-single-product.php`
- `wp-content/themes/agency-commerce/woocommerce/loop/no-products-found.php`

Do not override cart or checkout templates for block checkout.

## Assets

Parent runtime assets are built from `tooling/assets/*` into
`wp-content/themes/agency-commerce/build`. Child themes can enqueue their own
stylesheet after `agency-commerce`.

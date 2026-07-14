# Components

## Header Variants

- `header-default`
- `header-centered`

Selected by `header.variant`.

## Footer Variants

- `footer-columns`
- `footer-minimal`

Selected by `footer.variant`.

## Product-Card Variants

- `product-card-classic`
- `product-card-minimal`
- `product-card-compact`

Selected by `shop.card_variant`.

All product-card variants receive the shared presenter data from `agency_commerce_prepare_product_card_data()`. Variant templates must not recalculate product badges, price, stock, images, rating, category, or add-to-cart behavior.

To add a future variant, add the component template, add the variant to the whitelist in `agency_commerce_get_product_card_variant()`, document it here, and add tests. Do not duplicate presenter logic in the template.

## Product-Page Layouts

- `product-layout-standard`
- `product-layout-sticky`

Selected by `product_page.layout`.

Both layouts preserve WooCommerce single-product hooks for gallery, title, rating, price, excerpt, variations, quantity, add-to-cart, meta, tabs, and related products.

## Child Overrides

Child themes can override component templates by matching the parent path. Example:

```text
components/product-layout/product-layout-sticky.php
```

Prefer overriding a single component or CSS variables. Avoid copying full WooCommerce templates unless the parent component contract cannot support the required presentation change.

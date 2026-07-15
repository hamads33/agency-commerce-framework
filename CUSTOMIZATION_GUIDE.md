# Customization Guide

## Preferred Order

1. Use Commerce Framework settings.
2. Apply or adapt a preset.
3. Override CSS custom properties in a child theme.
4. Override a focused component template.
5. Add hooks only when presentation cannot be solved with CSS/templates.

Do not edit the parent theme or plugin directly.

## Token Customization

Child themes can override:

- `--ac-color-primary`
- `--ac-color-secondary`
- `--ac-color-accent`
- `--ac-color-text`
- `--ac-color-background`
- `--ac-color-surface`
- `--ac-container-width`
- `--ac-radius-button`
- `--ac-radius-card`
- `--ac-product-image-ratio`

## Component Overrides

Copy only the needed component path into the child theme:

```text
components/product-card/product-card-minimal.php
components/header/header-default.php
components/footer/footer-columns.php
components/product-layout/product-layout-sticky.php
```

Keep WooCommerce hooks in place. Do not copy or replace cart, checkout,
payment, shipping, coupon, or validation logic.

## Showcase Examples

- `wp-content/themes/agency-commerce-fashion`
- `wp-content/themes/agency-commerce-electronics`
- `wp-content/themes/agency-commerce-home`

These are examples of presentation-only storefront differentiation.

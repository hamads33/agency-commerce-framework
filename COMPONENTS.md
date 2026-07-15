# Components

## Header Variants

- `header-default`
- `header-centered`

Selected by `header.variant`.

## Footer Variants

- `footer-columns`
- `footer-minimal`

Selected by `footer.variant`.

Both footer variants render the configured social links and automatically hide empty URLs.

## Product-Card Variants

- `product-card-classic`
- `product-card-minimal`
- `product-card-compact`

Selected by `shop.card_variant`.

All product-card variants receive the shared presenter data from `agency_commerce_prepare_product_card_data()`. Variant templates must not recalculate product badges, price, stock, images, rating, category, or add-to-cart behavior.

CP4C restores standard WooCommerce product-loop hook names around the card templates. The framework temporarily removes WooCommerce's default card callbacks while firing those hooks so extension callbacks can run without duplicating the framework card markup.

To add a future variant, add the component template, add the variant to the whitelist in `agency_commerce_get_product_card_variant()`, document it here, and add tests. Do not duplicate presenter logic in the template.

CP7A upgrades the shared visual treatment for all product-card variants without
changing their markup contract. Cards use the premium radius, elevation, motion,
focus, badge, image-treatment, and spacing tokens from the parent stylesheet.
Variant templates still receive only presenter data and must not hard-code brand
colors, shadows, radii, or animation timings.

## Product Extensions

The selected single-product layout renders extension content through `agency_commerce_single_product_summary_extensions`.

CP4B currently uses that contract for:

- product badges,
- delivery messaging, and
- WhatsApp enquiry rendering.

The shared extension markup uses:

- `ac-product-badges`
- `ac-product-delivery`
- `ac-whatsapp-enquiry`

Do not duplicate these blocks inside the product-layout variants or in downstream child themes. Add new product-extension output through the shared hook and helpers instead.

## Preset Preview Assets

The admin presets tab renders static preview thumbnails only.

Supported preview groups:

- `header`
- `footer`
- `product-card`
- `product-layout`

These thumbnails are generated from the preset and settings-variant slugs and are not used in live storefront rendering.

## Product-Page Layouts

- `product-layout-standard`
- `product-layout-sticky`

Selected by `product_page.layout`.

Both layouts preserve WooCommerce single-product hooks for gallery, title, rating, price, excerpt, variations, quantity, add-to-cart, meta, tabs, and related products.

## Homepage Blocks

Custom CP5 blocks are registered by `AgencyCommerceCore\Homepage\BlockRegistrar` and use `block.json` plus the plugin `blocks.js` editor entry.

- `agency-commerce/hero`
- `agency-commerce/category-grid`
- `agency-commerce/promo-banner`
- `agency-commerce/image-text`
- `agency-commerce/trust-bar`
- `agency-commerce/testimonials`
- `agency-commerce/brand-logos`
- `agency-commerce/faq`
- `agency-commerce/newsletter`
- `agency-commerce/recently-viewed-products`

These blocks are server-rendered and escaped on output. They should remain presentation-focused. Do not add custom product query logic to them when a WooCommerce product block can provide the data.

## Homepage Patterns

Registered pattern slugs:

- `agency-commerce/fashion-homepage`
- `agency-commerce/electronics-homepage`
- `agency-commerce/minimal-homepage`

Patterns are insertable content only. They do not overwrite existing pages or automatically replace the active homepage.

## Cart, Checkout, And Account Components

WooCommerce owns Cart, Checkout, Mini-Cart, Store API state, payment methods, shipping methods, coupons, validation, notices, and My Account endpoints. The framework styles these surfaces through scoped CSS and adds only documented settings integrations.

Mini-Cart uses `woocommerce/mini-cart` when registered. The fallback is the existing `ac-cart-link` with CP2 cart-count fragments.

Classic cart and checkout use `[woocommerce_cart]` and `[woocommerce_checkout]` shortcodes in Classic mode. The mode manager keeps persistent original and latest block-content backups so repeated Block/Classic switching is idempotent and recoverable.

CP7A gives these surfaces the same premium design-system treatment as the
storefront: token-derived panels, notices, form fields, focus states, button
states, elevation, and spacing. It does not alter WooCommerce ownership of cart,
checkout, payment, shipping, validation, coupon, or account behavior.

## Premium Design System

CP7A defines the shared visual language inherited by future storefronts:

- typography scale, tight heading rhythm, and readable body rhythm;
- responsive section spacing and density-aware spacing tokens;
- base, button, card, and panel radius tokens;
- extra-small through large elevation tokens;
- primary, accent, success, error, soft-surface, overlay, and focus state tokens;
- button, icon-button, form input, card, pagination, notice, drawer, and panel
  treatments;
- token-driven image treatments for product cards, category banners, and home
  sections;
- loading skeleton classes via `.ac-skeleton` and WooCommerce skeleton support;
- tasteful hover/transition states with `prefers-reduced-motion` support.

Component authors should consume the existing `--ac-*` tokens. Do not introduce
hard-coded brand colors, CSS frameworks, Tailwind utilities, Bootstrap classes,
jQuery-driven presentation, or component-specific animation systems.

## Recently Viewed

Recently viewed product IDs are stored in browser `localStorage` under `agencyCommerceRecentlyViewed`. The browser keeps IDs unique and bounded. The plugin REST endpoint accepts only a limited array of positive integers, renders current published visible products from submitted IDs, rejects malformed or excessive requests, and ignores deleted, unavailable, private, or invisible products.

## Child Overrides

Child themes can override component templates by matching the parent path. Example:

```text
components/product-layout/product-layout-sticky.php
```

Prefer overriding a single component or CSS variables. Avoid copying full WooCommerce templates unless the parent component contract cannot support the required presentation change.

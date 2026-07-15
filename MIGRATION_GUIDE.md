# Migration Guide

## Migrating Existing WooCommerce Stores

1. Back up database and `wp-content`.
2. Audit current theme customizations and plugin dependencies.
3. Install Agency Commerce parent theme and companion plugin in staging.
4. Create a client child theme or adapt one showcase child theme.
5. Recreate branding through settings and CSS tokens.
6. Rebuild homepage content with Agency Commerce blocks/patterns and
   WooCommerce product blocks.
7. Test product archives, products, cart, checkout, My Account, emails, taxes,
   shipping, and payment gateways.
8. Run visual and browser smoke tests.
9. Launch only after extension compatibility is confirmed.

## Not Automated

Agency Commerce does not automatically migrate:

- page-builder layouts;
- custom checkout fields from third-party plugins;
- shipping/tax/gateway configuration;
- multilingual content;
- faceted-search configuration;
- ERP/POS integrations.

## Checkout Migration

Use block checkout by default. Use classic checkout only when the project
requires shortcode-compatible server-side customization.

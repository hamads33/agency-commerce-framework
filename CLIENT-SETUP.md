# Client Setup

## Homepage

Create or edit a WordPress page, then insert one of the Agency Commerce homepage patterns:

- Fashion homepage
- Electronics homepage
- Minimal store homepage

Patterns insert blocks into the current page. They do not delete existing content and they do not automatically replace the current homepage.

To replace a homepage safely:

1. Create a new draft page.
2. Insert a framework homepage pattern.
3. Edit the content in Gutenberg.
4. Preview the page.
5. Assign it under `Settings > Reading` only after review.

## Cart And Checkout Mode

The default checkout mode is Block. This uses WooCommerce Cart and Checkout blocks and preserves Store API behavior.

Classic mode is available under `Commerce Framework > Checkout`. When selected, the framework backs up the existing Cart and Checkout page content and writes WooCommerce shortcodes. Switching back to Block restores the saved content when a backup exists.

Do not manually delete the `_agency_commerce_core_block_content_backup` metadata unless the site owner has intentionally discarded the previous block page content.

## Mini-Cart

The header uses the WooCommerce Mini-Cart block when enabled and available. If WooCommerce or the block is unavailable, the theme uses the standard cart link and count fallback.

## Checkout Controls

Checkout controls are compatibility-first:

- Optional fields are applied only through WooCommerce-supported field APIs.
- Minimum order validation is enforced in both Block and Classic modes.
- COD controls affect the built-in WooCommerce COD gateway only.
- The framework does not override tax, shipping, payment-gateway, or legal requirements.

## Recently Viewed Privacy

Recently viewed products are stored in the visitor browser using `localStorage`. The framework does not create a database table or store personal browsing history server-side. The render endpoint receives product IDs from the browser and returns current visible product-card markup only.

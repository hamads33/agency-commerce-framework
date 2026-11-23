# BACKEND FREEZE v1 — BLOCKED

Source baseline: `c010ffbfa44781a0edf98e7544c720858e0e25b1`. Audit branch: `audit/backend-freeze`. This document records candidate contracts. It does not certify a freeze or authorize a tag.

| Contract | Behavior to preserve |
| --- | --- |
| Settings | `agency_commerce_core_settings` stores framework settings. Registry, sanitizer and public getters define supported keys, defaults and read-time fallbacks. Presets use a framework-only settings snapshot. Schema marker `agency_commerce_core_settings_schema_version` targets 4. v1, v2 and v4 are markers. v3 normalizes legacy custom badge arrays to newline text. A failed data or schema-marker write leaves the migration eligible to retry. |
| Catalogue | WooCommerce owns product type, variations, categories, stock and prices. Plugin adds badge visibility and labels, product/category delivery overrides and WhatsApp enquiry composition. Delivery resolves product, category, then global configuration. |
| Purchase and orders | WooCommerce owns sessions, quantities, coupon eligibility, prices, totals, gateways, accounts and order storage. Framework controls minimum order, COD policy and supported checkout fields and adds support/trust order metadata. Preserve Classic and Store API hook registration in `Checkout/Controls.php`. |
| Mode switch | `Checkout/ModeManager.php` uses WooCommerce-configured page IDs, retains the original and latest block content backups, switches to native shortcodes and restores the latest block state. Preserve permission checks and backup metadata. |
| REST | Public render-only `POST /wp-json/agency-commerce-core/v1/recently-viewed` accepts up to 24 positive integer IDs, deduplicates and caps display count, returns `{"html":"…"}` with `Cache-Control: no-store, max-age=0`, and filters unpublished and non-visible products. Malformed input is rejected. Preserve fallback when WooCommerce or the theme presenter is absent. |
| Access controls | WordPress Settings API handles the settings form. Preset mutations use `manage_options` and action nonces. Product metadata checks product edit capability and product nonce. Category overrides use `manage_options` and a category nonce. |
| Theme interfaces | Preserve settings fallbacks, presenter signatures, native WooCommerce hook order and checkout integration. See [frontend boundary](frontend-boundary-contract.md). |

The committed environment requests WordPress 7.0.1, WooCommerce 10.9.4, PHP 8.3, Node 24.14.0 and npm 11.9.0. See the audit for observed versions and test results.

# Plugin and theme boundary

- Core owns schema, security, settings validation, preset snapshots, product metadata, checkout mode and rules, and REST input and output eligibility.
- The parent theme owns layout and adapts WooCommerce templates through `inc/catalog.php` and `inc/woocommerce.php`. Preserve Loop and Single Product hooks when changing wrapper markup.
- Core has a standalone recently viewed renderer when the theme callback is absent. Both use the same eligibility check.
- The theme's settings adapter uses plugin readers when available and supplies presentation defaults when the plugin is disabled.
- WooCommerce owns commerce processing and validation. Keep native Checkout Blocks, Store API, and Classic shortcode flows.

The [frontend boundary contract](../frontend-boundary-contract.md) lists the replaceable paths. Preserve the public interfaces in the existing hook reference.

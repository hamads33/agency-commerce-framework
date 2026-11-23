# Component contracts

Find component overrides with `agency_commerce_component_path()`. Preserve paths for existing children. `agency_commerce_prepare_product_card_data()` supplies card view data. Keep purchase eligibility and variation decisions in WooCommerce and pass the rendering data to the component.

The cart fragment selector is `span.ac-cart-count`. Catalog scripts use the native variation form and connect the selected variation to the WhatsApp link. Coordinate selector changes with both the consumers and keyboard interaction tests.

Server-rendered blocks register from `block.json` in the core plugin. Existing block names, attribute shapes and saved configurations are compatibility contracts. Keep editor registration in sync and escape server output. Rebuild the editor bundle and exercise existing fixtures after changes.

# Hook Reference

See `HOOKS.md` for the canonical public API. This launch reference summarizes
the main extension groups.

## Theme Filters And Helpers

- `agency_commerce_get_setting()`
- `agency_commerce_default_settings`
- `agency_commerce_component_path()`
- `agency_commerce_design_token_css()`

## Product Hooks

- `agency_commerce_single_product_summary_extensions`
- `agency_commerce_after_single_product_summary_extensions`
- WooCommerce loop hooks preserved around framework product cards.

## Plugin Filters

- `agency_commerce_core_defaults`

## Settings Keys

Public setting paths are listed in `HOOKS.md` under `Settings Keys`.

## Compatibility Rule

New hooks must remain backward compatible after publication. Prefer adding new
filters/actions over changing existing callback signatures.

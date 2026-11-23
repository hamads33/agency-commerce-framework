# Data and settings

`Settings/Registry.php` defines the supported settings. `Defaults.php` and `Sanitizer.php` handle defaults, typed normalization and form submission. Read settings via public helpers, which apply defaults and cache normalized options for the request.

Migrations advance `agency_commerce_core_settings_schema_version` only after successful data handling. Preserve compatibility with the legacy custom badge representation. Mode switching uses WooCommerce's configured page IDs and stores original and latest content in post metadata. The switch must stop when its required recovery snapshot cannot be stored.

Use WooCommerce CRUD for products and orders. Keep presets scoped to framework settings, preserve all client content, and isolate database fixtures from commercial data.

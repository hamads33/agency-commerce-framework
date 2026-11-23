# Client store customization

Use one WordPress/WooCommerce installation and its own database per client. This repository provides a shared foundation and does not provision multisite tenants.

1. Install the shared core and parent theme.
2. Start a client child theme with the parent slug in its `Template` header. Use its stylesheet for client design tokens and use settings for variant selection and framing.
3. Use editable WordPress pages and registered Gutenberg patterns for homepage content. Use WooCommerce categories and product collections for catalogue content.
4. Override individual components by their existing relative paths when CSS and settings are insufficient. Keep account, variations, cart, and checkout behavior in the shared stack.

Fashion, Electronics, and Home demonstrate child defaults and distinct styling. Their custom front-page files and sample product/category assumptions are showcase content. New clients should author their own content and inherit shared components.

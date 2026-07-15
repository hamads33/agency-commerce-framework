# Installation

## Requirements

- WordPress `7.0.x`
- WooCommerce `10.9.x`
- PHP `8.2+`
- Modern browser support for the WordPress and WooCommerce block editor flows

## Packages

Install these ZIPs together for a standard store:

- `agency-commerce.zip`: parent theme
- `agency-commerce-core.zip`: companion plugin
- `agency-commerce-child.zip`: example child theme

## WordPress Admin Installation

1. Install and activate WooCommerce.
2. Upload and install `agency-commerce.zip` under `Appearance > Themes`.
3. Upload and install `agency-commerce-child.zip` under `Appearance > Themes`.
4. Activate `Agency Commerce Child` for client stores.
5. Upload and activate `agency-commerce-core.zip` under `Plugins`.
6. Open `Commerce Framework` in the WordPress admin.
7. Apply a preset if desired, then configure brand, header, shop, product,
   cart, checkout, footer, and social settings.
8. Create or edit a homepage with the Agency Commerce patterns and assign it in
   WordPress reading settings.
9. Configure WooCommerce shipping, taxes, payment gateways, transactional email,
   and legal pages before launch.

## WP-CLI Installation

```sh
wp plugin install agency-commerce-core.zip --activate
wp theme install agency-commerce.zip
wp theme install agency-commerce-child.zip --activate
wp plugin activate woocommerce
```

## Checkout Mode

Block checkout is the default and recommended mode. Classic checkout is a
compatibility fallback for stores that require shortcode-based server-side
checkout customization. Switch modes from `Commerce Framework > Checkout`.

## Known Limitations

- The framework does not include payment gateways, shipping rates, tax logic,
  subscriptions, multivendor behavior, multilingual behavior, or faceted search.
- The framework preserves WooCommerce checkout and Store API behavior rather
  than replacing it.
- Development-tool `npm audit` findings are accepted for CP6.1 because they are
  not shipped to client stores. See `SECURITY_EXCEPTION.md`.

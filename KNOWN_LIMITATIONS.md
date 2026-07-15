# Known Limitations

- Agency Commerce does not include payment gateways, shipping rates, tax logic,
  subscriptions, marketplace features, ERP/POS integrations, or a faceted-search
  engine.
- WooCommerce owns cart, checkout, payment, shipping, coupon, validation, and
  order processing.
- Block checkout supports fewer classic PHP checkout hooks than shortcode
  checkout.
- Presets and showcase themes change presentation only; they do not create
  products, orders, customers, menus, or media.
- The local demo setup may create placeholder WooCommerce images for stable
  screenshots.
- Development npm audit findings are documented in `SECURITY_EXCEPTION.md` and
  are not shipped in release ZIPs.
- Metadata URLs use reserved `.invalid` placeholders until real public URLs
  exist.

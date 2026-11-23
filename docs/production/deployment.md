# Deployment

Produce the parent theme, child starter, and core plugin ZIPs with `npm run build` and `npm run package`. Artifacts live under `build/release/`. Install a supported WooCommerce version and activate it. Install the core plugin and parent theme ZIP, then install and activate the chosen child theme. Packaged core uses its bundled namespace autoloader. The hosting server does not need build-time Node or Composer dependencies.

Confirm store pages, environment versions, a disposable test order and its stored total. Set gateway credentials only in the target site's secure configuration. Hosting owns TLS, file permissions, backups, mail delivery, monitoring, and connectivity to payment and shipping providers.

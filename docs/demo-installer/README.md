# Demo Installer

Agency Commerce showcase demos are child-theme presentations on top of the same
parent theme and companion plugin. They do not add ecommerce functionality or
replace WooCommerce data.

## Available Demos

- `fashion`: activates `agency-commerce-fashion`.
- `electronics`: activates `agency-commerce-electronics`.
- `home`: activates `agency-commerce-home`.

## One-Click Local Setup

Inside a running `wp-env` environment:

```sh
npm run env:cli -- bash scripts/setup-demo-store.sh fashion
npm run env:cli -- bash scripts/setup-demo-store.sh electronics
npm run env:cli -- bash scripts/setup-demo-store.sh home
```

The script:

- activates WooCommerce and `agency-commerce-core`;
- activates the selected showcase child theme;
- clears framework settings so child-theme defaults are visible;
- flushes rewrite rules;
- creates local WooCommerce placeholder image variants when the development
  uploads folder does not contain them.

## Production Import Guidance

For a production portfolio environment, install the parent theme, plugin, and
desired showcase child theme from ZIP packages, then import normal WooCommerce
products, menus, and pages using WordPress/WooCommerce import tools. Do not run
development seed scripts against a client production store.

## Screenshots

Generated showcase screenshots live in:

```text
showcase-screenshots/portfolio/
showcase-screenshots/boards/
```

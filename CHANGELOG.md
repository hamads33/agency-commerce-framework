# Changelog

## 0.2.0 - CP2

- Added `header-default` and `header-centered` variants with shared header data preparation.
- Added `footer-columns` and `footer-minimal` variants with shared footer data preparation.
- Registered primary and footer menu locations.
- Added announcement bar, logo fallback, search, account, cart count, mobile menu, keyboard support, focus restoration, and Escape-to-close behavior.
- Added WooCommerce cart-count fragment support using `woocommerce_add_to_cart_fragments`.
- Added sticky-header integration through existing safe settings helpers.
- Added global page containers, base typography, buttons, forms, layout primitives, and responsive styling.
- Tightened the PHPCS filename-sniff exclusion to PSR-4 plugin class files only.

## 0.1.0 - CP1

- Added activation-safe parent theme, child theme, and companion plugin foundations.
- Added parent theme support declarations, `theme.json` design tokens, mirrored CSS custom properties, and minimal classic templates.
- Added namespaced plugin bootstrap, PSR-4 loading, version constants, default settings, and public settings helpers.
- Added theme fallback settings helpers so the parent works when the plugin is inactive.
- Documented the initial public API contract in `HOOKS.md`.

## 0.0.0 - CP0

- Established repository-level tooling for `wp-env`, Composer, PHPCS/WPCS, PHP syntax checks, npm, and `@wordpress/scripts`.
- Added skeletal source directories for the future parent theme, companion plugin, and child theme.
- Documented local environment commands and CP0 version/test evidence.
- Corrected CP0 reproducibility by pinning WordPress core to the official `7.0.1` release package.
- Made WooCommerce reproducible with the exact `10.9.4` plugin package and setup command.
- Added Node/npm toolchain pins through `.nvmrc`, `packageManager`, and `engines`.
- Expanded environment recreation, version-pin, and audit documentation.

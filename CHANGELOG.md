# Changelog

## 0.0.0 - CP0

- Established repository-level tooling for `wp-env`, Composer, PHPCS/WPCS, PHP syntax checks, npm, and `@wordpress/scripts`.
- Added skeletal source directories for the future parent theme, companion plugin, and child theme.
- Documented local environment commands and CP0 version/test evidence.
- Corrected CP0 reproducibility by pinning WordPress core to the official `7.0.1` release package.
- Made WooCommerce reproducible with the exact `10.9.4` plugin package and setup command.
- Added Node/npm toolchain pins through `.nvmrc`, `packageManager`, and `engines`.
- Expanded environment recreation, version-pin, and audit documentation.

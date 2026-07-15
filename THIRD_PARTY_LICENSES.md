# Third-Party Licenses

## Runtime Distribution

The release ZIPs do not include `node_modules` or the repository-level Composer
`vendor` directory. The shipped parent theme, companion plugin, and child theme
therefore contain no bundled third-party runtime libraries beyond WordPress and
WooCommerce APIs provided by the host site.

## WordPress And WooCommerce Compatibility

- WordPress is licensed under GPLv2 or later.
- WooCommerce is licensed under GPLv3 or later.
- Agency Commerce is licensed under GPLv2 or later and is intended to be GPL
  compatible with WordPress themes/plugins and WooCommerce extension usage.
- The framework does not bundle WooCommerce and does not modify WooCommerce core.

## Development-Only Dependencies

These packages are used only in the repository toolchain and are not included in
release ZIPs:

| Ecosystem | Package | Purpose | License source |
| --- | --- | --- | --- |
| npm | `@wordpress/scripts` | Build and lint tooling | package metadata in `package-lock.json` |
| npm | `@wordpress/env` | Local WordPress environment | package metadata in `package-lock.json` |
| npm | `@playwright/test` | Browser smoke and visual tests | package metadata in `package-lock.json` |
| Composer | `wp-coding-standards/wpcs` | PHPCS rules | package metadata in `composer.lock` |
| Composer | `phpcompatibility/php-compatibility` | PHP compatibility sniffs | package metadata in `composer.lock` |
| Composer | `dealerdirect/phpcodesniffer-composer-installer` | PHPCS installer plugin | package metadata in `composer.lock` |

## Distribution Notes

If future releases bundle third-party runtime code, update this file before
packaging with each library name, version, license, copyright notice, and source
URL.

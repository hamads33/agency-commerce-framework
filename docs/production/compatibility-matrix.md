# Compatibility matrix

| Layer | Declaration or pin | Evidence |
| --- | --- | --- |
| WordPress | Package minimum 7.0, local pin 7.0.1 | Isolated ZIP install ran 7.0.1 |
| WooCommerce | Local pin 10.9.4 | Classic, Blocks and order ownership checked on 10.9.4 |
| PHP | Package minimum 8.2, local pin 8.3 | 8.3 checks only in this audit |
| HPOS | WooCommerce CRUD used by integrations | Generated ZIPs passed the Block checkout order test with HPOS enabled. The initial artifact boot also passed with legacy order storage. |
| Browser | Playwright locked by npm | Install its matching Chromium revision |

Declared minimum versions describe package metadata. Qualification for any additional combination requires its own verification. Recheck template overrides and saved block content when updating WooCommerce or WordPress.

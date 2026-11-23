# Technical debt register

| Priority | Status | Item |
| --- | --- | --- |
| P0 | Corrected | Backup-before-write and escaped checkout restore. |
| P0 | Corrected | Protected catalogue content filtered before public REST rendering. |
| P1 | Closed in hosted CI | Two isolated wp-env runs passed. Slow or transient local Docker network provisioning remains a developer-environment issue. |
| P1 | Open gate | Complete supported-version release qualification and frontend acceptance. |
| P1 | Open gate | Review development-tool advisories and any CI exception before distributing. |
| P2 | Improved | Storefront fixture metadata now seeds in one WP-CLI request. Continue measuring any browser or CI bottlenecks before splitting suites. |
| P2 | Open | Measure catalogue presentation on larger client-like datasets and measure legacy badge migration before adding caching or pagination. |
| P3 | Deferred | Frontend visual changes and showcase-specific content cleanup. |

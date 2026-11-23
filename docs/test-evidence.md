# Extended Phase 1A test evidence

Baseline `c1bf9e0`. Earlier evidence is in [the Phase 1A audit](backend-audit.md). Extended local logs are under `~/.cache/agency-hardening/`, with native wp-env logs under the per-run directory reported by the gate. All fixtures were synthetic and ran against disposable Docker projects.

| Command or check | Result |
| --- | --- |
| `composer lint:php && composer lint` and fixture PHP syntax | Exit 0 in PHP 8.3 for the initial hardening changes. |
| `npm run lint:js && npm run build && npm run package` | Exit 0 with Node 24.14.0 and npm 11.9.0. No tracked build artifact drift. ZIP CRC, required bundles and manifest checks passed. |
| `wp eval-file /fixtures/page-and-catalogue-protection.php` | Exit 0 on hardened code. Covered a rejected snapshot write, escaped round trip and protected-product filtering. |
| `wp eval-file /fixtures/authorization.php` | Exit 0. WooCommerce owner received 200 and another authenticated customer received 403. Category nonce and capability checks, sanitization and malformed-input preservation passed. |
| `node tests/backend/block-order.js` against the generated ZIPs | Exit 0 with HPOS enabled. Excessive stock and invalid email were rejected. The valid guest COD checkout stored the expected coupon, variation and $85 total. |
| `wp plugin install` and `wp theme install` with generated ZIPs | Clean package install passed with WordPress 7.0.1, WooCommerce 10.9.4 and PHP 8.3.36. Child inherited the parent. Plugin deactivate and reactivate passed in the initial artifact smoke. |
| `npm run test:backend` local attempt 1 | Exit 124 at the external time limit. Provisioning did not finish. |
| `npm run test:backend` local attempt 2 | Exit 1 during provisioning. Node reported `ETIMEDOUT`. |
| `npm audit --json`, `composer audit --locked --format=json` | Both exited 1 with advisory findings. No toolchain or lockfile upgrade was applied. |
| `npm run test:backend` local debug retry | Exit 1. Image pulls and database startup progressed, then Docker's Alpine package index lookup failed during the wp-env CLI image build. |
| `npm run test:admin` on disposable Compose | Exit 0. Completed variant persistence and preset checks. |
| Prior code regression and plugin ZIP upgrade/rollback | Prior code failed the checkout snapshot regression as expected. Reinstalled the hardened ZIP and verified schema 2-to-4 migration, legacy badge conversion, preserved settings and the existing HPOS order. Reinstalled the previous Git snapshot's ZIP and rechecked them. Both package manifests reported 1.0.0, so this was a code snapshot rehearsal. |
| Bounded catalogue sample | Two fresh PHP processes each read 24 products in 8 queries. The cold rendering pass issued 26 queries and took 170–227 ms. The second pass within each process issued zero queries and took 105–136 ms. No persistent cache was introduced. |

Repeat the performance sample on a disposable database containing 24 or more simple products with `wp eval-file` and [the sampler](../tests/backend/catalogue-performance.php). Log the product count, read and render queries and elapsed time together. CLI timings exclude full HTTP and browser rendering.

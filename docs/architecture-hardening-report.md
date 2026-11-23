# Architecture hardening report

The hardening baseline was `c1bf9e0400d4067da7938639d3d1ea23644c27c5`. Local documentation relocation was already underway and is outside the code commits.

| Priority | Problem | Correction and rationale |
| --- | --- | --- |
| P0 | Classic checkout could replace live page content even when storing its recovery snapshot failed. WordPress unslashing could also damage escaped blocks on the round trip. | Use metadata presence for original snapshots, verify recoverability before replacing content and preserve escaping during writes. Keep the mode setting, page IDs and WooCommerce shortcodes compatible. |
| P0 | The public recently viewed route relied on catalogue visibility for password-protected product eligibility. | Reject protected posts before rendering and check protected parents of variations. Keep the same response format and theme fallback. |
| P1 | Recursive ZIP packaging could include local files and could leave incomplete artifacts after a missing input. | Package tracked runtime files from a bounded layout, validate versions and required bundles, and stage all ZIPs before publication. |
| P1 | Category delivery updates could erase the value when a malformed array sanitized to an empty string. | Reject the malformed field before mutation. Cover capability, nonce and typed write behavior. |
| P1 | Customer ownership and malformed commerce requests lacked independent regression coverage. | Verify owner and other-customer Store API behavior, quantity above stock and invalid checkout email before a valid order. |

Keep WooCommerce as the source of truth for catalogue, accounts, payment and totals. The parent theme continues to bridge hooks and select components. Homepage patterns and child theme preferences remain data and presentation. A second presentation framework or a new commerce abstraction would add migrations without solving these failures. See the [architecture map](architecture/overview.md) and [technical debt register](technical-debt-register.md).

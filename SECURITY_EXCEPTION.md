# Security Exception

## Decision

The current `npm audit --audit-level=high` findings are accepted for the CP6.1
release candidate because every reported vulnerability is confined to
development tooling. No reported vulnerable package is loaded by WordPress,
WooCommerce, the parent theme, the child theme, or the companion plugin in a
deployed client store.

No force upgrade is approved for CP6.1. The audit-proposed root fix downgrades
`@wordpress/scripts` to `19.2.4`, a semver-major and toolchain-breaking change
from the pinned `32.6.0` release. That is not a safe release-candidate change.

## Scope Reviewed

- `package.json`
- `package-lock.json`
- `composer.json`
- `composer.lock`
- `wp-content/plugins/agency-commerce-core/composer.json`
- `wp-content/plugins/agency-commerce-core/package.json`
- `npm audit --audit-level=high --json`

## Runtime Exposure

| Question | Answer |
| --- | --- |
| Runtime dependency? | No. Findings are under npm dev tooling. |
| Development dependency? | Yes. They originate from `@wordpress/scripts`, `@wordpress/env`, Playwright-related tooling, and transitive tooling packages. |
| Shipped to production? | No. Release ZIPs exclude `node_modules`, repository `vendor`, tests, caches, logs, screenshots, and Git metadata. |
| Exploitable in deployed client stores? | No known path. Client stores load built CSS/JS/PHP only; they do not run webpack, markdown linting, Playwright, Lighthouse, Sentry tooling, or the webpack dev server. |
| Upgrade available? | Partially. npm reports fixes, but the root high-severity chain proposes `@wordpress/scripts@19.2.4`, a semver-major downgrade. |
| Safe upgrade? | Not for CP6.1 without a full toolchain compatibility review. |
| Breaking upgrade? | Yes for the proposed root change because it changes the WordPress build toolchain major line and may alter webpack/Babel/style handling. |

## Accepted Findings

| Package | Severity | Runtime? | Shipped? | Actual deployed-store exploitability | Upgrade decision |
| --- | --- | --- | --- | --- | --- |
| `@wordpress/scripts` | High | No | No | None identified; build/lint tool only | Accept until compatible upstream release is verified |
| `copy-webpack-plugin` | Moderate | No | No | None identified; webpack build chain only | Accept |
| `serialize-javascript` | High | No | No | None identified in client stores; build serialization only | Accept |
| `markdown-it` | High via chain | No | No | None identified; markdown lint/tooling only | Accept |
| `linkify-it` | High | No | No | None identified; markdown tooling only | Accept |
| `markdownlint` | Moderate | No | No | None identified; markdown linting only | Accept |
| `markdownlint-cli` | High | No | No | None identified; lint command only | Accept |
| `minimatch` | High | No | No | None identified; hostile glob exposure limited to dev/CI commands | Accept |
| `webpack-dev-server` | Moderate | No | No | None in production; risk only if dev server is exposed to untrusted networks | Accept with local-only dev-server policy |
| `sockjs` | Moderate | No | No | None in production; dev-server transport only | Accept |
| `uuid` | Moderate | No | No | None in production; dev-server chain only | Accept |
| `@opentelemetry/core` | Moderate | No | No | None in production; nested dev instrumentation chain only | Accept |
| `@opentelemetry/instrumentation-*` | Moderate | No | No | None in production; nested dev instrumentation chain only | Accept |
| `@opentelemetry/resources` | Moderate | No | No | None in production; nested dev instrumentation chain only | Accept |
| `@opentelemetry/sdk-trace-base` | Moderate | No | No | None in production; nested dev instrumentation chain only | Accept |
| `@opentelemetry/sql-common` | Moderate | No | No | None in production; nested dev instrumentation chain only | Accept |
| `@sentry/node` | Moderate | No | No | None in production; nested Lighthouse/tooling chain only | Accept |
| `lighthouse` | Moderate | No | No | None in production; development audit tooling only | Accept |

## Required Mitigations

- Do not publish or expose the webpack dev server to public or untrusted
  networks.
- Do not run lint/build/test commands on untrusted repository content in shared
  CI without sandboxing.
- Keep release ZIP exclusions enforced by `scripts/package-release.sh`.
- Re-run `npm audit --audit-level=high` during each release candidate pass.

## Future Upgrade Strategy

1. Track `@wordpress/scripts` releases after `32.6.0` for compatible transitive
   fixes.
2. Test any candidate update with `npm ci`, `npm run build`, `npm run lint:js`,
   the full Playwright suite, and visual comparison.
3. Regenerate `package-lock.json` only after compatibility is verified.
4. Do not use `npm audit fix --force` unless the resulting major-version change
   is intentionally reviewed and approved.

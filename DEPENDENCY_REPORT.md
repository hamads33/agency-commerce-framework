# Dependency Report

## Scope

Reviewed manifests:

- `package.json`
- `package-lock.json`
- `composer.json`
- `composer.lock`
- `wp-content/plugins/agency-commerce-core/composer.json`
- `wp-content/plugins/agency-commerce-core/package.json`

## Inventory Summary

### npm

| Package | Classification | Notes |
| --- | --- | --- |
| `@playwright/test` | Direct, development-only | Used for visual and smoke testing only. |
| `@wordpress/env` | Direct, development-only | Used to provision and control the local WordPress environment. |
| `@wordpress/scripts` | Direct, development-only | Used for JS/CSS builds and linting. |

### Composer

| Package | Classification | Notes |
| --- | --- | --- |
| `dealerdirect/phpcodesniffer-composer-installer` | Direct, development-only | PHPCS installer for local linting. |
| `phpcompatibility/php-compatibility` | Direct, development-only | PHPCS ruleset dependency. |
| `wp-coding-standards/wpcs` | Direct, development-only | PHPCS ruleset dependency. |

### Runtime status

- No production runtime npm dependencies are declared.
- No production runtime Composer dependencies are declared.
- All reported `npm audit` findings are in the development toolchain.

## `npm audit` Findings

Audit command run:

```sh
npm audit --json
```

Result summary:

- 28 total findings
- 22 moderate
- 6 high
- All findings are transitive through direct development tooling

## Findings By Package

### `@wordpress/scripts`

- Classification: Direct, development-only
- Severity: High
- Vulnerability surface: transitive `copy-webpack-plugin`, `markdownlint-cli`, and `webpack-dev-server`
- Production impact: None in shipped storefront code
- Development impact: Build/lint tooling and local dev-server behavior are affected
- Actually exploitable: Yes, but only in the development or CI toolchain, not in the released bundle
- Shipped to client: No
- Recommended action: Do not use `npm audit fix --force`. Track a compatible `@wordpress/scripts` release or targeted upstream fixes. The audit-reported "fix" is a semver-major downgrade and is not an acceptable blind upgrade.

### `copy-webpack-plugin`

- Classification: Transitive, development-only
- Severity: Moderate
- Vulnerability: `serialize-javascript` RCE / CPU-exhaustion advisories in the plugin dependency chain
- Production impact: None
- Development impact: Affects the asset build pipeline
- Actually exploitable: Only if untrusted inputs are processed in the build toolchain
- Shipped to client: No
- Recommended action: Resolve through a compatible upstream toolchain update, not a force fix.

### `markdown-it`

- Classification: Transitive, development-only
- Severity: High
- Vulnerability: Quadratic-complexity DoS in `smartquotes`
- Production impact: None
- Development impact: Affects markdown linting in the toolchain
- Actually exploitable: Only when linting attacker-controlled markdown content
- Shipped to client: No
- Recommended action: Upgrade via a compatible `@wordpress/scripts` release or patched markdownlint toolchain.

### `markdownlint`

- Classification: Transitive, development-only
- Severity: Moderate
- Vulnerability: Inherits `markdown-it` issue
- Production impact: None
- Development impact: Markdown linting only
- Actually exploitable: Only in development tooling
- Shipped to client: No
- Recommended action: Same as above.

### `markdownlint-cli`

- Classification: Transitive, development-only
- Severity: High
- Vulnerabilities: `markdownlint` and `minimatch`
- Production impact: None
- Development impact: Markdown lint command availability and behavior
- Actually exploitable: Only in local/CI lint execution
- Shipped to client: No
- Recommended action: Same upstream toolchain upgrade path.

### `minimatch`

- Classification: Transitive, development-only
- Severity: High
- Vulnerabilities: ReDoS via repeated wildcards, GLOBSTAR backtracking, nested extglobs
- Production impact: None
- Development impact: File-pattern matching in lint/build tooling
- Actually exploitable: Only if hostile glob patterns are processed in development tooling
- Shipped to client: No
- Recommended action: Upgrade the toolchain dependency chain if a compatible `@wordpress/scripts` release is available.

### `serialize-javascript`

- Classification: Transitive, development-only
- Severity: High
- Vulnerabilities: RegExp/date RCE and CPU exhaustion advisories
- Production impact: None
- Development impact: Webpack-related build tooling
- Actually exploitable: Only within the build toolchain
- Shipped to client: No
- Recommended action: Resolve through a compatible webpack/plugin update.

### `webpack-dev-server`

- Classification: Transitive, development-only
- Severity: Moderate
- Vulnerabilities: Source exposure and HMR/WebSocket interception advisories
- Production impact: None
- Development impact: Affects local dev-server behavior
- Actually exploitable: Yes, if a development server is reachable by untrusted traffic
- Shipped to client: No
- Recommended action: Upgrade the toolchain where compatible; otherwise keep the dev server isolated and undocumented for public exposure.

### `sockjs`

- Classification: Transitive, development-only
- Severity: Moderate
- Vulnerability: `uuid`-related advisory in the dev-server chain
- Production impact: None
- Development impact: Dev-server transport layer
- Actually exploitable: Only in a reachable development server
- Shipped to client: No
- Recommended action: Same as `webpack-dev-server`.

### `uuid`

- Classification: Transitive, development-only
- Severity: Moderate
- Vulnerability: Missing buffer bounds check in certain versions
- Production impact: None
- Development impact: Dev-server helper dependency
- Actually exploitable: Only in development tooling paths
- Shipped to client: No
- Recommended action: Same as `webpack-dev-server`.

### `@opentelemetry/core`

- Classification: Transitive, development-only
- Severity: Moderate
- Vulnerability: Unbounded memory allocation in W3C baggage propagation
- Production impact: None in released framework code
- Development impact: Exists only because of dev tooling transitive dependencies
- Actually exploitable: Only in code paths using that instrumentation chain
- Shipped to client: No
- Recommended action: Accept until the upstream toolchain is updated, then regenerate the lockfile.

### `@opentelemetry/*` instrumentation packages

- Classification: Transitive, development-only
- Severity: Moderate
- Vulnerabilities: Propagated from `@opentelemetry/core` and `@opentelemetry/sql-common`
- Production impact: None
- Development impact: Only through dev-toolchain dependency graphs
- Actually exploitable: Only in the affected dev instrumentation paths
- Shipped to client: No
- Recommended action: Upstream toolchain upgrade when compatible.

### `@sentry/node`

- Classification: Transitive, development-only
- Severity: Moderate
- Vulnerability: Inherits OpenTelemetry advisories
- Production impact: None
- Development impact: Only through a nested tooling dependency
- Actually exploitable: Only in the dev tooling chain
- Shipped to client: No
- Recommended action: Upgrade only when the root toolchain can do so compatibly.

### `lighthouse`

- Classification: Transitive, development-only
- Severity: Moderate
- Vulnerability: Inherits OpenTelemetry advisories
- Production impact: None
- Development impact: Only in Lighthouse-based tooling
- Actually exploitable: Only in the dev toolchain
- Shipped to client: No
- Recommended action: Same upstream toolchain upgrade path.

## Composer Audit Status

- `composer` direct dependencies are linting-only and do not introduce runtime packages.
- No Composer security findings were surfaced during this review.

## Release Assessment

- The repository does not ship vulnerable runtime dependencies.
- The repository does ship a vulnerable development toolchain through `@wordpress/scripts`.
- The development-toolchain risk is documented and accepted in `SECURITY_EXCEPTION.md`.
- Do not force `npm audit fix --force` for CP6.1 because the proposed root fix
  changes the `@wordpress/scripts` major line and has not been compatibility
  verified.

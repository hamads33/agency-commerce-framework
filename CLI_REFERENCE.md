# CLI Reference

Agency Commerce does not register production WP-CLI commands in v1.0. CLI usage
is repository tooling and local WordPress administration.

## Environment

```sh
npm run env:start
npm run env:setup
npm run env:stop
```

## WordPress CLI Wrapper

```sh
npm run env:cli -- wp theme list
npm run env:cli -- wp plugin list
npm run env:cli -- wp option get agency_commerce_core_settings --format=json
```

## Build And Test

```sh
npm run build
npm run lint:js
npm test
composer lint:php
composer lint
npm run visual:compare
npm run test:showcase
```

## Demo Store Setup

```sh
npm run env:cli -- bash scripts/setup-demo-store.sh fashion
npm run env:cli -- bash scripts/setup-demo-store.sh electronics
npm run env:cli -- bash scripts/setup-demo-store.sh home
```

## Packaging

```sh
npm run package
```

Generated release ZIPs are written to `build/release/`.

# Release gates

1. Run CI fast checks and ensure build artifacts match source.
2. Complete two fresh backend runs, including full admin and storefront scripts.
3. Build the artifacts. Verify manifest and parent linkage, install the generated ZIPs on a clean WordPress database, and exercise checkout.
4. Rehearse upgrading and rolling back with disposable existing data.
5. Review runtime and build-machine risks separately. Keep any exception time-bounded and scoped to named tooling.
6. Complete frontend and customer acceptance before a production release. Record outcomes in the audit and release evidence.

CI automates lint, build, archive verification, clean ZIP installation and two isolated backend runs. Run `npm run test:release` locally for the ZIP smoke. Verify the supported upgrade and rollback paths separately.

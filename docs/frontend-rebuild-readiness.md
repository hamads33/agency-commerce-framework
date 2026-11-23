# Frontend rebuild readiness

For extended verification use branch `hardening/architecture-production-readiness`. Use the verified `backend-freeze-v1` as the behavior baseline. Track commercial release separately in the [release gates](production/release-gates.md).

**Status: ready to begin the frontend reconstruction phase.** Both clean backend runs passed. Frontend implementation and production release remain pending.

Run `npm ci`, `composer install`, `npm run build`, `npx playwright install --only-shell chromium`, and `npm run test:backend` from the original `agency-commerce-framework/` project folder on branch `hardening/architecture-production-readiness`, using the toolchain specified in the repository. The gate starts a fresh wp-env instance at port 18988 by default. Set `BACKEND_TEST_PORT` if that port is occupied. It prints its private artifact directory, retains a pre-fixture database export when setup reaches that step, and stops the isolated environment on exit. The fixture source is versioned in `tests/backend/`.

The [freeze record](backend-freeze-v1.md) names the tested commit and environment. Repeat affected checks during frontend changes and rerun the full backend gate before release. Use the [frontend boundary contract](frontend-boundary-contract.md) when planning the reconstruction.

After freeze, start with the header and navigation shell. Verify keyboard and selector behavior, then proceed to catalogue and product templates using the [component contracts](architecture/component-contracts.md). Keep Classic and Block order checks in the rebuild regression gate.

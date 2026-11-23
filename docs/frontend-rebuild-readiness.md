# Frontend rebuild readiness

For extended verification use branch `hardening/architecture-production-readiness`. Keep the rebuild on hold until the [release gates](production/release-gates.md) close.

**Status: HOLD.** The backend freeze must pass before frontend reconstruction starts.

Run `npm ci`, `composer install`, `npm run build`, `npx playwright install --only-shell chromium`, and `npm run test:backend` from the original `agency-commerce-framework/` project folder on branch `audit/backend-freeze`, using the toolchain specified in the repository. The gate starts a fresh wp-env instance at port 18988 by default. Set `BACKEND_TEST_PORT` if that port is occupied. It prints its private artifact directory, retains a pre-fixture database export when setup reaches that step, and stops the isolated environment on exit. The fixture source is versioned in `tests/backend/`.

Resolve the open regression rows, check the full suite twice from clean databases, record the tested commit and actual environment versions, and tag that commit only when all mandatory checks pass. Use the [frontend boundary contract](frontend-boundary-contract.md) when planning the reconstruction.

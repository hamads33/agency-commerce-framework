# Responsive header verification

Run `npm run build` and `npm run lint:js`. Use `AGENCY_RUN_NAVIGATION=1 npm run test:backend` to run the header suite after the backend suites in an isolated WordPress environment. Run `AGENCY_NAVIGATION_FIXTURES=1 npm run test:navigation` directly only against a disposable local site with a `CP3-SIMPLE` fixture. It switches showcase themes and sets a long-label menu and fixture settings.

The Chromium matrix covers 320, 375, 390, 430, 768, 1024, 1280, 1440 and 1920px for Fashion and Electronics. It checks utility control dimensions and viewport bounds, keyboard search, mobile modal focus, background isolation, submenu disclosure, and Escape focus restoration. A separate case exercises WooCommerce's Mini-Cart and the fallback count across widths.

Screenshots are in `test-results/navigation/`, organized by theme. The suite captures 390px, 768px and 1440px header references and a 390px open drawer for each child. These are intentional references, independent from the historical storefront screenshot baselines.

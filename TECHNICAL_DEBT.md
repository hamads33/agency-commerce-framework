# Technical Debt

## Immediate

- `@wordpress/scripts` still pulls in audited transitive vulnerabilities.
- No `screenshot.png` is present for the parent theme or child theme.

## Future

- Cache repeated taxonomy and product lookups in catalog, badge, and delivery rendering paths.
- Add a CI accessibility gate if this framework is going to be maintained across many client stores.
- Add a lightweight smoke check that validates theme header metadata and packaging prerequisites.

## Nice-to-have

- Replace reserved `agency-commerce.invalid` metadata placeholders with real project URLs when a public project site exists.
- Add branded screenshot assets for the parent theme and child theme.
- Add a documented release bundle manifest that lists the files intended for a client ZIP.
- Add more exhaustive browser-level contrast checking.

## Deferred

- Upgrade the build toolchain only when a compatible `@wordpress/scripts` release resolves the current audit chain.
- Consider caching expensive checkout and recently-viewed lookups only if profiling shows they become material on high-traffic stores.

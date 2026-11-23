# Backend regression matrix

| Area | Existing or added coverage | Open work |
| --- | --- | --- |
| Catalogue | Added WooCommerce CRUD fixtures for simple product prices, sale editing, stock, categories, featured flag, variable attributes, child prices and persisted variation data. Existing storefront exercises badges, delivery, variable WhatsApp and search. | Product save hook acceptance and rejection checks passed. Category form submission and full product admin HTTP authorization remain open. |
| Cart | Existing cart flow checks browser cart surfaces. Added guest Store API test for variation add, update, removal, coupon and exact discounted total. | Run repeatedly and test coupon rejection and inventory limits. |
| Block checkout | Existing browser suite checks mode and support UI. Added Store API COD checkout and database assertions tied to the returned order ID. | Guest COD purchase and persisted totals passed in the fallback. Independent invalid submission and framework order metadata remain open. |
| Classic checkout | CP5 audit submits COD and checks the returned order, payment method, support metadata, billing email and line. | Run the revised check and assert exact totals. |
| Account | Account suite now covers native registration, persisted customer role and login. | Execute the extended registration and login suite. Account ownership and guest order isolation remain open. |
| Settings and presets | Admin suite exercises saved variants and preset apply/restore. | Full typed sanitization, capability/nonce matrix and persistence after switching themes. |
| Migrations | Storefront exercises the v3 conversion and legacy compatibility. | Injected metadata write failure reproduced and passed after the fix. Extended regression includes marker-write failure and verifies preservation of a saved framework option. |
| REST | CP5 audit exercises invalid arrays, private products and WooCommerce-inactive behavior. | Broader catalogue visibility and order/customer exposure checks. |
| Hooks and blocks | Homepage suite checks block/pattern registration and output. Storefront checks WooCommerce compatibility and inactive states. | Confirm theme-independent fallback and complete integration surface in a clean run. |
| Presentation | Visual suite compares eight storefront surfaces across three viewports. Showcase switches themes and compares their pages serially. | Use for screenshot diagnostics. These suites do not grade frontend design quality. |

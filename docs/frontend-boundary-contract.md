# Frontend boundary contract

This is the source-reviewed boundary for planning. Frontend execution remains on hold pending the backend freeze.

Paths below are relative to `wp-content/themes/agency-commerce/` unless otherwise stated.

| Paths or functions | Presentation change allowed after freeze | Contract to retain |
| --- | --- | --- |
| `style.css`, `theme.json`; child/showcase `style.css` and showcase `front-page.php` | Colors, spacing, typography, markup and composition | Design-token overrides from Framework settings, child header metadata and theme inheritance, accessible interactive states. |
| `header.php`, `footer.php`, `sidebar.php`, `searchform.php`, `front-page.php`, `page.php`, `single.php`, `index.php`, `archive.php`, `search.php`, `404.php` | Structural HTML and layout | WordPress lifecycle hooks, content rendering, menu and widget locations, native search query inputs, WordPress URL generation and escaping. |
| `components/header/header*.php`, `components/footer/footer*.php`, `components/product-card/product-card-*.php`, `components/product-layout/product-layout-*.php` | Presentation markup and CSS classes | Keep component paths for child overrides, variant keys, presenter data consumption and the native product summary sequence. |
| `woocommerce/archive-product.php`, `woocommerce/content-product.php`, `woocommerce/content-single-product.php`, `woocommerce/loop/no-products-found.php` | Archive, card, single-product and empty-state shells | Preserve archive/wrapper and shop loop hooks, `agency_commerce_do_product_loop_hook()`, `agency_commerce_do_archive_wrapper_hook()`, `agency_commerce_single_product_summary_extensions`, native summary hooks and product context. Keep native WooCommerce cart, checkout and variation processing. |
| `inc/navigation.php`, `inc/catalog.php` | Change HTML inside `agency_commerce_render_*` helpers where the function renders presentation | Preserve `agency_commerce_component_path()`, `agency_commerce_prepare_product_card_data()`, settings-backed variants, result/query changes, visibility, product actions, account/cart URLs, Mini-Cart and link/count fallback. |
| `assets/js/navigation.js`, `assets/js/catalog.js`, root `tooling/assets/{frontend,catalog,index}.js` | Navigation, drawer and filter interactions and their selectors | Keep variation selection events and WhatsApp URL refresh, responsive cart behavior, accessibility and asset handles/dependencies in `inc/assets.php`. Regenerate tracked build products from source. |
| `inc/settings.php`, `inc/setup.php`, `inc/woocommerce.php`, all theme `functions.php` | Work around these integration points from markup and styles | Preserve default settings, setup and template discovery, widget/menu registrations, feature declarations, cart count fragment selector and bootstrap. |
| Core plugin `includes/Homepage/BlockRegistrar.php` render methods, `includes/RecentlyViewed/Manager.php` render methods and block styles | Block presentation | Preserve block names, attributes and saved content compatibility, dynamic rendering, escaping and server-side render registration. |

Treat core plugin settings, migrations, presets, product metadata, REST, checkout, WooCommerce hooks and public getters as behavioral dependencies. A renderer can change its HTML while keeping data selection, settings, validation and safeguards. Coordinate selector changes with storefront JS, block integrations and browser assertions.

The product layout order is `woocommerce_before_single_product_summary`, `woocommerce_single_product_summary`, `agency_commerce_single_product_summary_extensions`, `woocommerce_after_single_product_summary`, then `agency_commerce_after_single_product_summary_extensions`. Keep the enclosing `woocommerce_before_single_product` and `woocommerce_after_single_product` actions in `woocommerce/content-single-product.php`.

The cart fragment uses `span.ac-cart-count`. Catalog variation code connects `.variations_form` and its `attribute_*` inputs to `.ac-whatsapp-enquiry`. Navigation uses `.ac-menu-toggle`, `.ac-mobile-overlay`, and the search toggle/close controls. Catalog filters use `.ac-filter-toggle`, `.ac-filter-overlay`, and `[data-filter-close]`. Change markup and its consuming code together where selectors need revision. Retain variation reset, focus restoration, keyboard close and current cart count.

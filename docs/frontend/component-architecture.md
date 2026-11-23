# Header component architecture

The existing router selects the configured header variant through `agency_commerce_component_path()`. Both variants use the same announcement partial, controls and WordPress menu renderer. Child themes retain the original override paths.

Keep the `.ac-site-header` wrapper, `.ac-menu--desktop` and `.ac-menu--mobile` menu classes, search control-to-panel relationship, and `#ac-mobile-menu` dialog relationship. The browser owns modal isolation through `showModal()`. Navigation JavaScript adds focus wrapping and restoration. Submenu buttons use `aria-expanded` alongside the native parent link. Escape closes a submenu and restores its trigger. A second Escape closes the drawer when present.

Search keeps the WordPress form action, search query and WooCommerce product constraint. The WooCommerce Mini-Cart still renders its own control and drawer. The alternate cart link continues to include `span.ac-cart-count`. Keep those native entry points when changing the header shell.

Add shared markup to parent components and store theme variations through child settings or token overrides. Keep interactive behavior in the shared navigation entry and rebuild the tracked assets.

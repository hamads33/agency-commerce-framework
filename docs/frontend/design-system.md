# Storefront design system

The parent theme uses settings-derived semantic colors, font families, spacing, container widths and radius variables. Keep shared tokens in `style.css` and editor-facing values in `theme.json`. Child themes may change identity through existing Framework settings, child defaults or CSS. The header requires no store-specific menus, icons, fonts or product data from the parent theme.

The header applies a fluid wordmark scale, a sparse set of controls, 44px interaction targets, a consistent line icon size and visible focus outlines. The primary header uses navigation between the identity and utility controls. The centered variant places branding over a full-width navigation row. Main navigation switches to a dialog below 70em to preserve space for long translations and labels. The existing page layout breakpoints are independent.

Controls use logical properties for spacing and alignment. The drawer opens on the inline-start side, switches sides in RTL and uses the dynamic viewport height. The modal's contents scroll internally. Existing sitewide reduced-motion rules apply. The new header interactions use no animation.

For client themes, configure contrast across background, text, primary color and focus states. Keep headings, notices and commerce message colors on their semantic token roles. Use the shared container for alignment and avoid fixed content widths.

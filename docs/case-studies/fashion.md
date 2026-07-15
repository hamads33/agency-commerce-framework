# Fashion Showcase Case Study

## Project Goal

Demonstrate how Agency Commerce can support an editorial luxury fashion store
without changing the parent framework or adding commerce logic.

## Design Direction

The fashion showcase uses large whitespace, serif typography, quiet neutrals,
minimal chrome, and editorial image proportions. The direction is inspired by
premium fashion hierarchy and spacing principles, not by copied brand assets or
layouts.

## User Experience Decisions

- Centered header with restrained navigation.
- Drawer filters to keep category pages visually calm.
- Minimal product cards with large image crops and low metadata density.
- Sticky product summary for considered product-page browsing.
- Checkout and account screens styled as quiet editorial panels.

## Framework Features Used

- Child theme defaults through `agency_commerce_core_defaults`.
- CSS token overrides for color, typography, spacing, radius, and image ratio.
- Parent product-card presenter and WooCommerce templates.
- Block checkout styling inherited from the parent.
- Showcase visual-regression suite.

## Responsive Strategy

Desktop prioritizes large editorial composition. Tablet collapses hero and
editorial columns. Mobile keeps typography large but stacks imagery and content
to avoid horizontal overflow.

## Accessibility Decisions

- Visible parent focus states are preserved.
- High-contrast text on quiet backgrounds.
- Reduced-motion behavior remains controlled by the parent design system.
- Native WooCommerce forms, account endpoints, cart, and checkout remain intact.

## Performance Metrics

Lighthouse homepage spot check:

- Performance: `92`
- Accessibility: `96`
- Best Practices: `100`
- SEO: `92`
- CLS: `0`

## Screenshots

- `showcase-screenshots/portfolio/fashion/desktop/homepage.png`
- `showcase-screenshots/portfolio/fashion/desktop/shop.png`
- `showcase-screenshots/portfolio/fashion/desktop/product.png`
- `showcase-screenshots/portfolio/fashion/desktop/checkout.png`
- `showcase-screenshots/portfolio/fashion/tablet/homepage.png`
- `showcase-screenshots/portfolio/fashion/mobile/homepage.png`
- `showcase-screenshots/boards/fashion.png`

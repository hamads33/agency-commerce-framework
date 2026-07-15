# Electronics Showcase Case Study

## Project Goal

Demonstrate a technical, product-first ecommerce storefront on the same Agency
Commerce framework used by the other showcases.

## Design Direction

The electronics showcase uses high contrast, precise grids, technical panels,
rounded hardware-like cards, compact product metadata, and specification-led
hierarchy. All graphics are original CSS/SVG treatments.

## User Experience Decisions

- Sticky dark header with concise navigation.
- Drawer filters for focused product comparison.
- Compact product cards to support dense scanning.
- High-contrast forms and checkout panels.
- Product-first homepage with technical feature panels.

## Framework Features Used

- Child theme token overrides.
- `product-card-compact` parent variant.
- WooCommerce block cart and checkout styling.
- Parent product-page sticky layout.
- Showcase screenshot generator and visual tests.

## Responsive Strategy

Desktop uses side-by-side hero and device panels. Tablet stacks sections while
preserving grid rhythm. Mobile prioritizes one-column product scanning and clear
button targets.

## Accessibility Decisions

- Light text on dark surfaces with tested Lighthouse accessibility score.
- Native keyboard focus remains visible.
- No interaction depends on animation.
- Console errors, overflow, and CLS are checked in Playwright.

## Performance Metrics

Lighthouse homepage spot check:

- Performance: `92`
- Accessibility: `96`
- Best Practices: `100`
- SEO: `92`
- CLS: `0`

## Screenshots

- `showcase-screenshots/portfolio/electronics/desktop/homepage.png`
- `showcase-screenshots/portfolio/electronics/desktop/shop.png`
- `showcase-screenshots/portfolio/electronics/desktop/product.png`
- `showcase-screenshots/portfolio/electronics/desktop/checkout.png`
- `showcase-screenshots/portfolio/electronics/tablet/homepage.png`
- `showcase-screenshots/portfolio/electronics/mobile/homepage.png`
- `showcase-screenshots/boards/electronics.png`

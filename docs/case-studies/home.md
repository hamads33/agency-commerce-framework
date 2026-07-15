# Home Showcase Case Study

## Project Goal

Show how Agency Commerce can present a warm furniture and home storefront with
storytelling sections while leaving WooCommerce commerce behavior untouched.

## Design Direction

The home showcase uses warm neutrals, generous room-like sections, tactile
product cards, soft elevation, and editorial typography. Visuals are abstract
demo compositions generated in CSS.

## User Experience Decisions

- Warm sticky header and full footer.
- Category and shop pages emphasize room browsing.
- Classic product cards with larger lifestyle-feeling image treatments.
- Checkout and account surfaces use soft panels and visible form focus.
- Homepage tells a room-story before product exploration.

## Framework Features Used

- Child theme defaults and CSS variables.
- Parent product-card presenter.
- WooCommerce native product, cart, checkout, and account surfaces.
- Parent token-driven forms, buttons, notices, and panels.
- Showcase screenshot and comparison-board workflow.

## Responsive Strategy

Desktop places large lifestyle panels beside copy. Tablet stacks room cards.
Mobile uses single-column sections and preserves large tap targets.

## Accessibility Decisions

- Warm palette selected for readable contrast.
- Parent focus outline and form states are retained.
- WooCommerce semantics remain native.
- Playwright validates no console errors, no overflow, and CLS below `0.1`.

## Performance Metrics

Lighthouse homepage spot check:

- Performance: `91`
- Accessibility: `91`
- Best Practices: `100`
- SEO: `92`
- CLS: `0`

## Screenshots

- `showcase-screenshots/portfolio/home/desktop/homepage.png`
- `showcase-screenshots/portfolio/home/desktop/shop.png`
- `showcase-screenshots/portfolio/home/desktop/product.png`
- `showcase-screenshots/portfolio/home/desktop/checkout.png`
- `showcase-screenshots/portfolio/home/tablet/homepage.png`
- `showcase-screenshots/portfolio/home/mobile/homepage.png`
- `showcase-screenshots/boards/home.png`

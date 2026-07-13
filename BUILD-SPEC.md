# Build Spec v2 — `agency-commerce` Reusable WooCommerce Framework

> **Purpose of this document.** This is a build brief for an AI coding agent (Claude Code) or a senior WP engineer. It defines a reusable ecommerce **engine** (parent theme + companion plugin) that is installed once and reused across many client stores. Per client, **only the storefront changes** — via a child theme, settings, branding, and content. The foundation is never rebuilt.
>
> v2 keeps the full scope of v1. It adds explicit architecture decisions and corrects the cart/checkout and homepage-block approaches so they match current WooCommerce (block-based cart/checkout default since WC 8.3; Product Collection block since WC 10.x). Nothing is cut.

---

## 0. Architecture decisions (READ FIRST — these are binding)

These resolve ambiguities in v1. Do not deviate without flagging it as an assumption.

**A. Theme type: classic theme + `theme.json`, NOT a full block (FSE) theme.**
- The parent theme uses classic PHP templates (`front-page.php`, `header.php`, `single.php`, the `woocommerce/` override folder, etc.).
- It **also** ships a `theme.json` — used for the design-token layer (color palette, typography, spacing) and, critically, for **global styles that theme the WooCommerce blocks** (cart, checkout, mini-cart). `theme.json` global styles apply to Woo blocks even in a classic theme. This is the only clean, upgrade-safe way to style block cart/checkout from a classic theme.
- Do **not** ship `templates/*.html` block templates for cart/checkout — a classic theme cannot override them that way. That path is block-theme only.

**B. Cart & Checkout: default to the block Cart/Checkout, style via `theme.json` + CSS + Store API, with a documented classic fallback toggle.**
- v1's cart/checkout section implicitly assumed the old shortcode templates. That is no longer the default and must be corrected.
- Primary path: keep the **block** Cart and Checkout. Style them through `theme.json` global styles and scoped CSS. Extend behavior (custom fields, trust messaging inside checkout, etc.) via the **Store API + `IntegrationInterface`** and the supported subset of Woo hooks — never by hacking React internals or replacing cart logic with custom AJAX.
- The block Cart/Checkout **do not honor most classic PHP `woocommerce_*` hooks.** Consult WooCommerce's "Hook alternatives" doc; only use hooks confirmed to work with blocks.
- Fallback path: the plugin exposes a documented setting **"Checkout mode: Block (default) / Classic."** Choosing Classic transforms the pages to the shortcode-based cart/checkout, unlocking full PHP/template control for clients who need deep server-side checkout customization. Ship the classic `woocommerce/` template overrides so this path is fully styled too.
- Mini-cart: use the **Mini-Cart block** (with its `mini-cart.html` template part where a block theme would, but from the plugin/theme provide the styled slide-out via the block + CSS). The custom slide-out drawer wraps the Mini-Cart block / Store API cart fragments — it does not reimplement cart state.

**C. Homepage product sections: wrap and style core Woo blocks; do not author new query blocks.**
- For Featured Products, Best Sellers, New Arrivals, on-sale, hand-picked, cross-sells: use the **Product Collection block** (and the remaining core product blocks) as the data layer. Register **block patterns** and **block styles** that pre-configure and skin them. Do not reinvent product querying in custom blocks — you'd be rebuilding what core ships and re-solving pagination, stock, and caching.
- Author **custom blocks only** for genuinely non-product sections that core doesn't provide (hero, trust bar, testimonials, FAQ, newsletter wrapper, brand logos, category grid presentation). Even these should prefer Product Collection / core blocks internally where products are involved (e.g., category grid can wrap product-category term queries).

**D. Parent = engine, child = storefront (confirmed maintenance model).**
- One parent theme + one plugin, reused across all stores. Each client gets a **child theme** that changes only presentation and branding. This is a deliberate commitment to semver discipline on the parent: a parent update can affect every store, so the parent carries a CHANGELOG, backward-compat guarantees on its hooks, and a test matrix. Client-specific PHP/design lives in the child, never the parent.

**E. Version pinning.**
- At build start, read the actual installed WP and WooCommerce versions and pin the spec to them. Guard every WC-version-sensitive feature (block cart/checkout, Product Collection, Mini-Cart block) behind capability/existence checks so the framework degrades gracefully on older WC and doesn't fatal on newer.

---

## 1. Environment prerequisites (must exist before Milestone 1)

The v1 instructions ("inspect the repo, check WP/WC versions, run Playwright, WP-CLI seed") only work inside a real local WordPress install with a build toolchain. Establish this first; if absent, set it up before writing feature code.

- **Local WP environment:** `wp-env` (preferred, Docker-based, scriptable) or Local by Flywheel or a Docker Compose stack. Must include WP + WooCommerce active and WP-CLI available.
- **Node build toolchain:** Node LTS + npm. Use `@wordpress/scripts` (wp-scripts) for block compilation and for CSS/JS bundling + minification. This satisfies the "minified production CSS/JS" and "compiled Gutenberg blocks" requirements without hand-rolling webpack.
- **PHP tooling:** `composer` for autoloading (PSR-4) inside the plugin, `phpcs` with the **WordPress Coding Standards** ruleset, `php -l` for syntax checks.
- **Browser automation:** Playwright, if available, for the visual test pass.
- Record actual versions of WP, WC, PHP, Node in `DEVELOPMENT.md` before coding.

If any of the above is missing and cannot be provisioned, state that explicitly in the plan and note which testing/seed steps become documentation-only.

---

## 2. Deliverables

1. Reusable parent theme: **`agency-commerce`** (presentation).
2. Reusable companion plugin: **`agency-commerce-core`** (functionality + admin settings).
3. Minimal example child theme: **`agency-commerce-child`** (per-client branding/overrides).

The plugin must activate and function without the parent theme. The theme must degrade gracefully when the plugin is inactive (see §Compatibility layer).

Do not use Elementor or any page builder. Native WordPress, Gutenberg, WooCommerce hooks, PHP templates, CSS, and lightweight vanilla JS only. Avoid jQuery unless WP/WC forces it. No remote runtime dependencies.

---

## 3. Technical standards (unchanged from v1 — all binding)

Follow WordPress Coding Standards and WooCommerce theme/extension best practices. Latest stable WP + WC. PHP namespaces + PSR-4 autoload in the plugin. Escape all output. Sanitize all saved settings. Nonces + capability checks on every admin action and metadata write. Translation functions on all user-facing text. No deprecated WP/WC APIs. Never edit WC core. Prefer hooks/filters over template overrides; override templates only when hooks can't achieve the layout. Business logic stays out of the theme; client design stays out of the parent. No unnecessary third-party deps. Semantic, accessible HTML meeting reasonable WCAG. Responsive across mobile/tablet/desktop. Minimal animation. No bloated CSS frameworks. Never load assets on pages that don't need them.

---

## 4. Required folder structure

```text
wp-content/
├── themes/
│   ├── agency-commerce/
│   │   ├── assets/{css,js,images,fonts}/
│   │   ├── inc/                     # theme bootstrap modules (no business logic)
│   │   ├── components/
│   │   │   ├── header/ footer/ product-card/ hero/ category-grid/
│   │   │   ├── trust-bar/ promo-banner/ testimonials/ newsletter/ faq/
│   │   ├── templates/
│   │   ├── template-parts/
│   │   ├── woocommerce/             # classic-mode WC overrides only (hooks-first)
│   │   ├── languages/
│   │   ├── functions.php            # thin loader → inc/
│   │   ├── front-page.php           # renders editable blocks, NO hard-coded content
│   │   ├── header.php footer.php index.php page.php single.php
│   │   ├── archive.php search.php 404.php
│   │   ├── style.css theme.json screenshot.png
│   │
│   └── agency-commerce-child/
│       ├── assets/css/
│       ├── functions.php style.css screenshot.png
│
└── plugins/
    └── agency-commerce-core/
        ├── admin/                   # settings UI, previews
        ├── includes/                # namespaced classes (PSR-4)
        ├── public/                  # frontend features (badges, whatsapp, recently-viewed)
        ├── blocks/                  # custom non-product blocks + patterns/styles for core blocks
        ├── integrations/            # Store API / block cart-checkout integration, 3rd-party filter plugins
        ├── templates/               # plugin-owned render partials
        ├── languages/
        ├── uninstall.php agency-commerce-core.php readme.txt
        ├── composer.json            # PSR-4 autoload
        └── package.json             # wp-scripts build for blocks/assets
```

You may refine this where technically justified, but preserve the theme-presentation vs plugin-functionality separation.

---

## 5. Parent theme requirements

### 5.1 WooCommerce support
`add_theme_support` for: `woocommerce`, `wc-product-gallery-zoom`, `wc-product-gallery-lightbox`, `wc-product-gallery-slider`, custom-logo, menus, post-thumbnails, HTML5 markup, responsive-embeds, align-wide, editor-styles, wp-block-styles, and Woo product-image sizing. Register Gutenberg editor styles. Use selective refresh for Customizer parts where relevant. Declare block cart/checkout compatibility.

### 5.2 Global design system (CSS custom properties + `theme.json`)
Define tokens once, in `theme.json` (canonical) mirrored to CSS custom properties for the classic templates. Tokens: primary, secondary, accent, text, muted text, background, surface, border, success, error; container width, narrow container width; heading font, body font, base/small/large font size; border radius, button radius, card radius; spacing scale; shadow levels; header height; product image ratio. Client branding changes through these variables + child overrides only. No hard-coded colors/spacing scattered in CSS.

### 5.3 Layout variants (all required — none cut)

**Headers (≥2):** `header-default`, `header-centered`. Each: logo, primary nav, search trigger/bar, My Account link, cart link with live count, mobile menu, optional announcement bar, settings-controlled sticky behavior, accessible keyboard nav.

**Footers (≥2):** `footer-columns`, `footer-minimal`. Support: logo, contact info, footer menus, social links, newsletter area, copyright, payment icons, optional trust text.

**Product cards (≥3):** `product-card-classic`, `product-card-minimal`, `product-card-compact`. Each: image, hover image, title, price, sale price, sale badge, out-of-stock badge, featured badge, category, rating, quick-add, view-product fallback for variable products, accessible focus states. **Shared data preparation** (one function builds the card data model) feeds variant-specific presentation partials — no duplicated card logic across files.

**Product page layouts (≥2):** `product-layout-standard`, `product-layout-sticky`. Support: gallery, title, rating, price, short description, variations, quantity, add to cart, stock status, SKU, categories, tags, delivery message, WhatsApp/enquiry button, size-guide trigger, tabs/accordion (description, additional info, reviews), related products, recently viewed. Built hooks-first over the classic single-product template.

**Hero layouts (≥2):** `hero-split`, `hero-fullwidth`. Support: desktop image, mobile image, eyebrow, heading, description, primary CTA, secondary CTA, text alignment, overlay strength, min height, content position. Implemented as a custom block (non-product).

### 5.4 Reusable homepage sections
Homepage is **fully editable in Gutenberg** — no hard-coded content in `front-page.php` (which only renders the block area / assigned template). Provide, via **block patterns + block styles wrapping core Woo blocks** where products are involved, and custom blocks otherwise:
- Hero banner (custom block), category grid, **featured products / best sellers / new arrivals (Product Collection block presets)**, promotional banner, image+text, trust bar, testimonials, brand logos, FAQ, newsletter signup, recently viewed products.
Editor experience must be understandable to a non-technical store manager. All product listings use WooCommerce product queries (Product Collection), never manually entered product HTML.

### 5.5 Shop archive
Page title, optional category description, breadcrumbs, product count, sorting dropdown, responsive grid, configurable products-per-row and per-page, sidebar **or** drawer filters, active-filter chips, empty state, pagination, mobile filter button, category banner support, consistent card variants. Integrate cleanly with standard WooCommerce filter widgets / common filter plugins — do **not** build a faceted-search engine from scratch.

### 5.6 Cart experience (corrected — block-first)
Styled block **Cart** page; optional slide-out mini-cart wrapping the **Mini-Cart block** / cart fragments; live cart count; quantity update; remove control; coupon entry; shipping summary; subtotal; empty state; continue-shopping; cross-sells (**Product Collection**-powered, per WC 10.2+); accessible modal/drawer. Never replace WC cart logic with custom insecure AJAX — use fragments / Store API. Style via `theme.json` global styles + CSS.

### 5.7 Checkout (corrected — block-first with classic fallback)
Style the block **Checkout**: billing, shipping, order notes, payment methods, shipping methods, coupon, order review, terms, validation messages, responsive layout, trust message, optional support info. Extend via Store API / `IntegrationInterface` and supported hooks only. Never remove legally/operationally required fields by default; expose optional field changes through plugin settings + filters, with admin warnings. The documented **Classic checkout toggle** (§0.B) ships full `woocommerce/` template overrides for clients needing server-side control.

### 5.8 My Account
Style login, registration, dashboard, orders, downloads, addresses, account details, logout nav. Preserve native endpoint functionality.

### 5.9 Search
Product-aware search, results page, empty state, search overlay/dropdown, keyboard accessible. If live search is implemented: product image/name/price in suggestions, lightweight and secure (Store API or a tightly-scoped AJAX endpoint with nonce). A standard secure search form is acceptable for v1 if live search adds undue complexity.

### 5.10 Performance
Conditional asset loading (enqueue per template/context), minified production CSS/JS via wp-scripts, no render-blocking font loading, native lazy loading, correct image sizes + `srcset`, minimal DOM, no frontend frameworks, no remote runtime deps. **Local system font stack by default; external fonts optional.**

---

## 6. Core plugin requirements

Plugin keeps working if the storefront theme changes. Plugin never depends on the parent theme for activation.

### 6.1 Admin settings
Professional interface under a **`Commerce Framework`** menu. WordPress Settings API (or equivalent secure native approach). Sectioned tabs — never one giant form:
`Store Identity`, `Header`, `Shop`, `Product Page`, `Cart`, `Checkout`, `WhatsApp & Enquiries`, `Footer`, `Social Links`, `Performance`, `Developer`.
Prefer core Customizer / site-editor / `theme.json` for anything WP already provides rather than duplicating it. Cached option retrieval (single settings blob, memoized getter). No raw serialized data shown to users.

### 6.2 Store Identity
Store logo reference, mobile logo, favicon guidance, brand colors, heading font, body font, contact email, contact phone, WhatsApp number, store address, support hours.

### 6.3 Header settings
Header variant, enable announcement bar, announcement text, announcement link, sticky header, show search, show account icon, show cart icon, show cart item count.

### 6.4 Shop settings
Card variant; products per row (desktop/tablet/mobile); products per page; product image ratio; show category; show rating; show quick-add; show sale badge; show out-of-stock badge; enable hover image; filter layout.

### 6.5 Product-page settings
Layout variant; sticky summary; show delivery message + text; show WhatsApp enquiry; show size guide + content/page selector; show SKU; show categories; show tags; related-products count; recently-viewed toggle; sticky mobile add-to-cart.

### 6.6 WhatsApp / enquiry
Reusable enquiry button. Message safely includes product name, product URL, selected variation (when available), SKU (when available). Admin-configurable template with placeholders `{product_name}` `{product_url}` `{sku}` `{variation}`. Validate/sanitize phone number. Never expose order/customer data in the URL.

### 6.7 Custom product badges
Badges: New, Featured, Best Seller, Limited, Custom label. Controlled through product metadata with capability + nonce checks on save. Rendered by the card + product-page layers.

### 6.8 Delivery estimate
Global delivery text; optional product-specific override; optional category-level behavior if maintainable. Not a courier-rate engine.

### 6.9 Checkout controls
Safe config for: optional company field, address line 2, order notes, postcode; custom support text; minimum order amount; COD availability + instructions. Never make fields optional where WC, gateways, shipping integrations, or regulations require them. **Admin warnings** where a setting could break shipping/payment. Note the block-checkout constraint (§0.B): field changes go through the Store API / Checkout block field APIs and supported filters, not classic hooks that the block ignores.

### 6.10 Social links
Facebook, Instagram, TikTok, YouTube, LinkedIn, X, Pinterest. Render only configured links.

### 6.11 Recently viewed
Lightweight, privacy-conscious (local storage or WC-compatible cookie). Do not store personal browsing histories in the DB.

### 6.12 Compatibility layer
Helper functions/class letting the theme read plugin settings without fataling when the plugin is inactive (safe getter with defaults). Theme fails gracefully if plugin disabled. Plugin never requires the theme.

---

## 7. Child theme requirements

Example child demonstrating: correct parent declaration; enqueue child styles; override CSS variables; change typography; change button radius; change product-card appearance; override **one** component template; add a client-specific asset; add **one** safe custom WooCommerce hook. Never copy the full parent in. Comment which files a client normally edits per store.

---

## 8. Preset system

At least three starter presets: `minimal`, `fashion`, `electronics`. Each defines: color palette, font stack, button radius, card radius, product image ratio, header variant, footer variant, card variant, product-page layout, container width, shadow level, spacing density. Applying a preset **only writes configuration** — it never deletes store content. A preset is a starting point that stays customizable afterward. Not a visual page-builder.

---

## 9. Admin UX

Logical grouping, clear labels, short descriptions, sensible defaults, validation messages, preview labels for variants, no raw serialized data, no giant single form, no dev jargon in client-facing controls. Small **static visual previews** for header, card, product-page, and footer variants. No full live-preview system in v1.

---

## 10. Sample data & setup

Optional **development-only** content/docs for: categories, simple products, variable products, sale products, out-of-stock products, featured products, homepage sections, menus, widgets. Never auto-insert sample data on production activation. Prefer a **WP-CLI command** (`wp agency-commerce seed`) or a clearly separated dev seed script.

---

## 11. Security review checklist

Capability checks; nonce validation; input sanitization; output escaping; SQL-injection review (use `$wpdb->prepare` only where WP APIs can't; prefer WP/WC APIs); XSS; CSRF; unsafe file access; unsafe AJAX endpoints; unauthorized metadata updates; open redirects; unvalidated URLs. No custom auth. No payment data stored. Never interfere with gateway security or the block-checkout Store API trust boundary.

---

## 12. Documentation

`README.md`, `DEVELOPMENT.md`, `CLIENT-SETUP.md`, `COMPONENTS.md`, `HOOKS.md`, `CHANGELOG.md`.

- **README:** what the framework is; theme vs plugin responsibilities; install; requirements; basic config; dev commands; production build.
- **DEVELOPMENT:** environment (§1), pinned versions, build/lint/test commands, block build via wp-scripts, the block-vs-classic cart/checkout decision and how to work with each.
- **CLIENT-SETUP:** the 18-step per-client workflow (install WP → WC → parent → plugin → child → apply preset → branding → menus → shipping → taxes → gateways → checkout → products → build homepage with blocks → test emails → test mobile → test cart/checkout → launch).
- **COMPONENTS:** every variant + how to add a new one.
- **HOOKS:** every custom action/filter the framework exposes (this is the parent's public API contract — keep it stable across parent updates).

---

## 13. Testing

Verify each of: theme activation; plugin activation; plugin deactivation; WC-inactive state; WC-active state; parent without child; child activation; simple/variable/sale/out-of-stock products; cart; mini-cart; coupon; **block checkout**; **classic checkout fallback**; My Account; product search; shop archive; product category; mobile menu; keyboard nav; empty states; PHP warnings; JS console errors; responsive behavior.

**Playwright pass (if available):** homepage → shop → category → simple product → variable product → add to cart → open mini-cart → cart → checkout → mobile viewport → screenshots → fix visible breakages → repeat until no major visual issues.

---

## 14. Code quality

Avoid: giant `functions.php` / bootstrap files; repeated queries/markup/option reads; global namespace pollution; inline CSS/JS everywhere; excessive overrides; hard-coded URLs/product IDs/page IDs/client names/currencies/phones/shipping rules; plugin logic in the theme; theme presentation in the plugin.
Use: classes/structured modules; light dependency injection (no overengineering); shared component helpers; cached option retrieval; WC conditional tags; native template functions; clear hooks and extension points; DocBlocks on public APIs.

---

## 15. First-version scope restrictions

Do **not** build (unless already provided by WC or a lightweight integration): multivendor, subscriptions, custom gateway, custom courier API, ERP/CRM, loyalty, advanced wishlist/comparison, faceted-search engine, headless frontend, React admin app, full-site visual builder, AI features, multilingual engine, custom tax engine, custom inventory engine. Add **extension points** for these instead of implementing them now.

---

## 16. Acceptance criteria

1. Parent activates, no fatals. 2. Plugin activates, no fatals. 3. Child activates. 4. WC pages visibly styled (block cart/checkout styled via theme.json + CSS; classic fallback styled too). 5. ≥2 header variants. 6. ≥2 footer variants. 7. ≥3 card variants. 8. ≥2 product-page layouts. 9. ≥2 hero variants. 10. Homepage sections editable without touching PHP. 11. Settings saved/retrieved securely. 12. Presets apply safely (config only). 13. Storefront usable with plugin disabled. 14. Plugin active without the parent theme. 15. Cart/checkout use supported WooCommerce logic (blocks/Store API, or classic shortcode when toggled). 16. Responsive. 17. No major PHP warnings. 18. No major JS errors. 19. Docs complete. 20. Framework reusable for another client mainly via child theme + settings + branding + content.

---

## 17. Checkpoint-based execution protocol

This specification is the **source of truth for the complete framework**, but it must **not** be implemented in one uninterrupted agent run.

The coding agent must first read this entire file to understand the architecture, constraints, dependencies, and final acceptance criteria. After reviewing it, the agent must work on **exactly one checkpoint at a time**.

### 17.1 Mandatory agent behavior

At the start of every checkpoint, the agent must:

1. Read `BUILD-SPEC.md` completely.
2. Inspect the current repository and Git history.
3. Review the previous checkpoint summary and test results.
4. Confirm the exact checkpoint being requested.
5. State:
   - the checkpoint objective;
   - dependencies already satisfied;
   - files expected to be created or modified;
   - assumptions or risks;
   - tests that will be run.
6. Implement only the requested checkpoint.
7. Do not begin the next checkpoint automatically.
8. Do not implement future functionality “while already in the file” unless it is strictly required for the current checkpoint.
9. Do not create fake implementations, TODO-only classes, empty placeholder modules, or acceptance-criteria stubs.
10. Stop after the current checkpoint has been implemented, tested, documented, and committed.

A checkpoint is not complete merely because files were generated. It is complete only when its acceptance checks pass or any unresolved failure is explicitly documented.

### 17.2 Git discipline

Before changing code, the working tree should be clean unless the user explicitly approves existing uncommitted work.

For every checkpoint:

- create a focused implementation;
- run the required checks;
- update relevant documentation;
- update `CHANGELOG.md`;
- provide a concise checkpoint report;
- create one clear conventional Git commit.

Do not combine unrelated checkpoints in one commit.

Recommended commit prefixes:

```text
chore: environment and tooling
feat(theme): parent and child foundation
feat(theme): storefront chrome
feat(catalog): product and archive system
feat(plugin): commerce settings and features
feat(checkout): commerce flow
test: hardening and release verification
```

If tests fail, do not hide or bypass the failure merely to create a commit. Fix the issue or clearly stop and report the blocker.

### 17.3 Checkpoint sequence

#### CP0 — Environment and repository foundation

Scope:

- inspect the host system and repository;
- verify Git, Docker, Docker Compose, PHP, Composer, Node, npm, and available browser tooling;
- choose `wp-env` or Docker Compose;
- provision a real local WordPress installation;
- install and activate WooCommerce;
- make WP-CLI available;
- create the repository-level source structure;
- configure `.gitignore`;
- configure Composer, PHPCS/WPCS, `php -l`, npm, and `@wordpress/scripts`;
- record exact WP, WooCommerce, PHP, Node, npm, and Composer versions in `DEVELOPMENT.md`;
- document start, stop, build, lint, test, WP-CLI, and reset commands.

Acceptance checks:

- WordPress frontend loads;
- wp-admin loads;
- WooCommerce is active;
- WP-CLI works;
- Composer autoload generation works;
- npm build tooling executes;
- repository has a clean checkpoint commit.

Do not implement theme or plugin features during CP0.

#### CP1 — Theme and plugin foundation

Scope:

- scaffold `agency-commerce`;
- scaffold `agency-commerce-core`;
- scaffold `agency-commerce-child`;
- create a thin parent `functions.php` that loads modules from `inc/`;
- configure namespaced PSR-4 plugin loading;
- add required theme supports;
- implement canonical `theme.json` tokens and mirrored CSS custom properties;
- add safe theme-to-plugin settings helpers with defaults;
- ensure graceful behavior when WooCommerce or the plugin is inactive;
- establish version constants and public API conventions.

Acceptance checks:

- parent theme activates without fatal errors;
- child theme activates correctly;
- plugin activates independently of the parent theme;
- parent works without the plugin;
- parent and plugin do not fatal when WooCommerce is inactive;
- syntax, coding-standard, and build checks pass.

Do not implement complete headers, catalog layouts, settings screens, or checkout behavior during CP1.

#### CP2 — Storefront chrome

Scope:

- `header-default`;
- `header-centered`;
- `footer-columns`;
- `footer-minimal`;
- primary and footer menu registration;
- announcement bar;
- logo, account, search, and cart controls;
- live cart-count integration using supported WooCommerce behavior;
- accessible desktop navigation;
- accessible mobile menu;
- sticky-header setting integration;
- global page containers, typography, buttons, forms, and layout primitives.

Acceptance checks:

- both header variants render;
- both footer variants render;
- mobile menu works with keyboard and pointer input;
- focus management and escape-to-close behavior work;
- no major responsive breakage;
- no PHP warnings or major console errors.

Do not begin product-card, archive, product-page, or checkout implementation during CP2.

#### CP3 — Catalog and product experience

Scope:

- shared product-card data preparation;
- classic, minimal, and compact product-card variants;
- shop and product-category archives;
- result count, ordering, pagination, empty states, and category presentation;
- filter sidebar or mobile drawer integrating supported WooCommerce filters;
- standard and sticky product-page layouts;
- product gallery, summary, variations, stock, metadata, tabs/accordion, related products;
- catalog and single-product responsive styling;
- secure basic product search and empty-search state.

Acceptance checks:

- simple, variable, sale, featured, and out-of-stock products render correctly;
- all three card variants work from the shared data layer;
- both product layouts work;
- add-to-cart behavior remains native and functional;
- shop, category, search, and product pages work on mobile and desktop;
- no duplicated full card business logic exists across variants.

Do not build the full settings application or commerce flow during CP3.

#### CP4 — Core plugin settings and reusable features

Scope:

- professional tabbed `Commerce Framework` settings interface;
- cached and sanitized settings storage;
- store identity, header, shop, product-page, footer, social, performance, and developer settings;
- minimal, fashion, and electronics presets;
- safe preset application that changes configuration only;
- WhatsApp product enquiry;
- custom product badges and secured product metadata saving;
- delivery estimate global and product override;
- social links;
- static variant preview assets;
- public actions, filters, and compatibility helpers.

Acceptance checks:

- settings save, validate, and retrieve correctly;
- unauthorized users cannot modify settings or product metadata;
- presets do not delete or overwrite content;
- WhatsApp placeholders are escaped and populated correctly;
- badges and delivery text render through documented extension points;
- plugin remains functional with another theme active.

Do not implement the full homepage block library or checkout modes during CP4.

#### CP5 — Homepage and commerce flow

Scope:

- custom non-product Gutenberg blocks for hero and other genuinely custom sections;
- Product Collection-based patterns/styles for featured, best-selling, new, sale, and hand-picked products;
- category grid, promotional content, image-and-text, trust bar, testimonials, logos, FAQ, newsletter wrapper, and recently viewed;
- fully editable homepage with no hard-coded storefront content;
- block Cart styling;
- Mini-Cart block or supported Store API/cart-fragment drawer integration;
- block Checkout styling and supported Store API/`IntegrationInterface` extensions;
- documented Block/Classic checkout mode setting;
- classic cart/checkout fallback setup and styling;
- checkout controls, support messaging, minimum-order and COD behavior;
- My Account styling;
- full purchase-flow verification in both checkout modes.

Acceptance checks:

- homepage can be edited without PHP changes;
- product sections use WooCommerce Product Collection/core product blocks rather than custom query duplication;
- cart state remains controlled by WooCommerce;
- block checkout completes a test order;
- classic checkout fallback completes a test order;
- mini-cart, coupons, shipping, payment selection, validation, and account endpoints work;
- no unsupported React-internal checkout hacks are used.

#### CP6 — Hardening, packaging, and release candidate

Scope:

- accessibility audit and fixes;
- responsive audit;
- performance and conditional-enqueue review;
- security review using §11;
- PHPCS/WPCS and PHP syntax pass;
- production asset build;
- Playwright full-flow test where available;
- screenshots and visual regression review;
- test theme/plugin independence;
- complete all required documentation;
- complete `CHANGELOG.md`;
- produce installable parent-theme, plugin, and child-theme ZIP packages;
- document known limitations and extension points.

Acceptance checks:

- all §16 acceptance criteria are checked individually;
- no major PHP warnings;
- no major JavaScript console errors;
- no known critical/high-severity security issue;
- installation packages work in a clean WordPress test instance;
- final directory tree and exact build/test/package commands are documented.

### 17.4 Required checkpoint report

At the end of every checkpoint, the agent must report:

```text
Checkpoint:
Status: complete | blocked | partially complete

Implemented:
- ...

Files created or modified:
- ...

Commands run:
- ...

Tests passed:
- ...

Tests failed or not run:
- ...

Assumptions and known limitations:
- ...

Git commit:
- <hash and message>

Next checkpoint:
- name only; do not start it
```

Claims such as “tested,” “production-ready,” “secure,” or “complete” must be supported by commands and observable results.

### 17.5 How the user will invoke the agent

The user will normally give a short instruction such as:

```text
Read BUILD-SPEC.md and implement CP0 only.
```

or:

```text
Review BUILD-SPEC.md, the current repository, and the previous checkpoint.
Implement CP3 only. Do not start CP4.
```

The agent must derive the full checkpoint requirements from this file rather than asking the user to paste a separate milestone prompt.

---

## 18. Project-wide completion rules

The complete framework is finished only after CP0 through CP6 have all been completed and the full acceptance criteria in §16 have been verified.

Across all checkpoints:

- Do not edit WooCommerce core.
- Do not silently change the binding architecture decisions in §0.
- Do not mark unavailable tests as passed.
- Do not weaken security or compatibility checks to make progress appear complete.
- Do not add out-of-scope systems listed in §15.
- Keep the parent theme, plugin, and child storefront responsibilities separate.
- Maintain backward compatibility for documented public actions, filters, component contracts, and settings.
- Flag any necessary deviation from the specification before implementing it.

**Architectural priority order:**

```text
maintainability
→ reusability
→ WooCommerce compatibility
→ client editing experience
→ performance
→ visual quality
→ development speed
```

After CP6, provide:

- final directory tree;
- implemented-functionality summary;
- activation and installation instructions;
- client customization workflow;
- complete test evidence;
- known limitations;
- recommended next improvements;
- exact build, lint, test, package, and clean-install commands.

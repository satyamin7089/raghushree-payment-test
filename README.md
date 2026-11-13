# Raghushree — Custom B2B Industrial WordPress Theme

Production-ready custom WordPress theme built specifically for **Raghushree (`Raghushree Plast Products Pvt. Ltd.`)**, an Indian manufacturer, exporter, and supplier of thermoplastic polymer raw materials (`PP`, `HDPE`, `LDPE`, `LLDPE`, `PVC`, `PET`, and `Recycled & Reprocessed Polymers`).

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Theme Installation Steps](#2-theme-installation-steps)
3. [WordPress & Server Requirements](#3-wordpress--server-requirements)
4. [Required Setup Steps](#4-required-setup-steps)
5. [Permalink Configuration](#5-permalink-configuration)
6. [Page Setup Instructions](#6-page-setup-instructions)
7. [Menu Setup Instructions](#7-menu-setup-instructions)
8. [Custom Post Types & Taxonomies Guide](#8-custom-post-types--taxonomies-guide)
9. [How to Add & Edit Polymer Products](#9-how-to-add--edit-polymer-products)
10. [How to Manage Categories & Catalog Filters](#10-how-to-manage-categories--catalog-filters)
11. [How to Edit Home, About, Contact, Products & Product Detail Pages](#11-how-to-edit-home-about-contact-products--product-detail-pages)
12. [How to Replace Temporary Unsplash Images](#12-how-to-replace-temporary-unsplash-images)
13. [Recommended Image Dimensions & Formats](#13-recommended-image-dimensions--formats)
14. [Contact Form Configuration & Email Setup](#14-contact-form-configuration--email-setup)
15. [SEO & Structured Data (Schema.org) Overview](#15-seo--structured-data-schemaorg-overview)
16. [Performance Architecture Notes](#16-performance-architecture-notes)
17. [Responsive Breakpoint Overview](#17-responsive-breakpoint-overview)
18. [Typography & Color Token Overview](#18-typography--color-token-overview)
19. [Industrial Design Rules Summary](#19-industrial-design-rules-summary)
20. [Developer Customization Guide](#20-developer-customization-guide)
21. [Deployment Checklist](#21-deployment-checklist)
22. [Pre-Launch Verification Checklist](#22-pre-launch-verification-checklist)
23. [Troubleshooting Guide](#23-troubleshooting-guide)
24. [Items Client Should Verify Before Launch](#24-items-client-should-verify-before-launch)
25. [Future Scalability Notes](#25-future-scalability-notes)

---

## 1. Project Overview

The `raghushree` theme is a zero-bloat, standalone custom WordPress theme engineered for industrial B2B polymer procurement, domestic distribution, and international export enquiries.

Key architectural highlights:
- **Zero third-party plugin lock-in:** Native WordPress Custom Post Types (`products`, `raghushree_enquiry`), custom taxonomies, structured WP Admin meta boxes, native Customizer company settings, native server-side RFQ handling, and built-in SEO/Schema support.
- **Complete core page suite:**
  - **Homepage (`front-page.php`):** Exact 12-section industrial B2B layout.
  - **About Page (`page-templates/template-about.php`):** 12-section company, capability, technical support, global supply, and enquiry-workflow layout.
  - **Contact & RFQ Page (`page-templates/template-contact.php`):** 9-section enquiry hub with a nonce-verified, honeypot-protected 11-field industrial quotation/sample form.
  - **Products Catalog (`archive-products.php` & `page-templates/template-products.php`):** Dynamic filterable polymer archive with category pills, live search, and verified taxonomy filters.
  - **Product Detail (`single-products.php` & `page-templates/template-product-detail.php`):** Reusable 10-section technical polymer template with sticky horizontal section navigation (`Overview`, `Grades`, `Applications`, `Processing`, `Technical Details`, `Packaging`, `FAQs`, `Enquiry`).
- **Truthful data architecture:** Unverified grades, MFI/density values, pack sizes, MOQ, and datasheets are never fabricated—empty fields automatically collapse or display a clear prompt to confirm current lot specifications with the sales desk.

---

## 2. Theme Installation Steps

1. Compress the `raghushree/` folder into `raghushree.zip` (or upload the `raghushree/` directory directly via SFTP/SSH to `wp-content/themes/raghushree/`).
2. In WordPress Admin, navigate to **Appearance → Themes**.
3. If uploading via browser, click **Add New Theme → Upload Theme**, select `raghushree.zip`, and click **Install Now**.
4. Click **Activate** on **Raghushree Industrial B2B**.
5. Upon first activation, the theme's idempotent seeder (`inc/seed-data.php`) automatically creates:
   - Core pages (`Home`, `About Raghushree`, `Contact & Enquiries`) and sets `Home` as the static front page if none is configured.
   - Core polymer categories and the 7 foundational polymer entries (`PP`, `HDPE`, `LDPE`, `LLDPE`, `PVC`, `PET`, `Recycled & Reprocessed Polymers`) if no `products` posts exist yet.

---

## 3. WordPress & Server Requirements

- **WordPress:** `6.2+` (tested through `6.7+`)
- **PHP:** `8.0+` (`8.1`–`8.4` recommended)
- **Database:** MySQL `5.7+` / MariaDB `10.4+` (also compatible with SQLite integration for staging)
- **PHP Extensions:** `mbstring`, `xml`, `curl`, `gd` or `imagick` (for responsive Media Library thumbnails)
- **HTTPS:** SSL/TLS certificate recommended in production

---

## 4. Required Setup Steps

After activating the theme:
1. Visit **Settings → Permalinks**, select **Post name** (`/%postname%/`), and click **Save Changes** to ensure rewrite rules are flushed.
2. Visit **Appearance → Customize → Raghushree Company & Contact** to verify company details, telephone, WhatsApp number, registered Kanpur address, and the RFQ notification recipient email (`marketing@raghushree.com`).
3. Visit **Polymer Products → All Products** in WP Admin to review the 7 seeded polymer records and add verified grades, technical datasheets, or facility/product photography as they become available.

---

## 5. Permalink Configuration

The theme is architected around clean B2B URL structures:
- `/` — Homepage (`front-page.php`)
- `/about/` — About Raghushree (`page-templates/template-about.php`)
- `/contact/` — Contact & RFQ (`page-templates/template-contact.php`)
- `/products/` — Native `products` Custom Post Type Archive (`archive-products.php`)
- `/products/{polymer-slug}/` — Dynamic Product Detail (`single-products.php`), e.g.:
  - `/products/polypropylene-pp/`
  - `/products/high-density-polyethylene-hdpe/`
  - `/products/low-density-polyethylene-ldpe/`
  - `/products/linear-low-density-polyethylene-lldpe/`
  - `/products/polyvinyl-chloride-pvc/`
  - `/products/polyethylene-terephthalate-pet/`
  - `/products/recycled-reprocessed-polymers/`
- `/polymer-category/{slug}/`, `/material-grade/{slug}/`, `/material-form/{slug}/`, `/application-industry/{slug}/` — Taxonomy archives (`archive.php` automatically routes polymer taxonomies through the filterable catalog layout).

> **Note:** If you Already have a static WordPress Page whose slug is literally `products`, either delete/rename that static page so WordPress routes `/products/` to `archive-products.php`, OR assign the **Products Catalog** page template (`page-templates/template-products.php`) to that page—both render the exact same shared catalog template (`template-parts/sections/product/listing-content.php`).

---

## 6. Page Setup Instructions

If setting up manually on an existing site:
1. **Homepage:** Create/edit a page titled `Home`. Go to **Settings → Reading**, choose **A static page**, and select `Home` under **Homepage**. `front-page.php` renders automatically.
2. **About Page:** Create/edit a page with slug `about`, and in the **Page Attributes / Template** panel select **About Page**.
3. **Contact Page:** Create/edit a page with slug `contact`, and select the **Contact & RFQ Page** template.
4. **Privacy Policy / Legal Pages:** Standard WordPress pages automatically use `page.php` with semantic typography and surface styling.

---

## 7. Menu Setup Instructions

The theme registers 4 WordPress navigation locations under **Appearance → Menus**:
- `primary` — **Primary Header Navigation**
- `footer_products` — **Footer Polymer Products Links**
- `footer_links` — **Footer Quick Links**
- `footer_legal` — **Footer Legal Links** (e.g., Privacy Policy, Terms of Supply)

**Built-in Smart Fallback:** Even before custom menus are assigned, the global header (`header.php`) and global footer (`footer.php`) automatically render `Home`, the accessible `Products` Mega Menu (dynamically populated from published `products` posts), `About`, `Contact`, `Request a Quote` CTA, and verified polymer/enquiry footer links.

---

## 8. Custom Post Types & Taxonomies Guide

Defined in `inc/custom-post-types.php`:

| Type | Slug | Purpose | Public URL |
|---|---|---|---|
| **CPT** | `products` | Polymer raw material entries | `/products/{slug}/` |
| **CPT** | `raghushree_enquiry` | Admin-only backup log of submitted website RFQs | Private (WP Admin → **Client Enquiries**) |
| **Taxonomy** | `polymer_category` | Hierarchical polymer family (`PP`, `HDPE`, `LDPE`, `LLDPE`, `PVC`, `PET`, `Recycled`) | `/polymer-category/{slug}/` |
| **Taxonomy** | `material_grade` | Specific verified grades (e.g., Raffia, Film, Injection, Pipe) | `/material-grade/{slug}/` |
| **Taxonomy** | `material_form` | Physical supply forms (e.g., Granules, Powder, Flakes, Reprocessed) | `/material-form/{slug}/` |
| **Taxonomy** | `application_industry` | End-use manufacturing sectors | `/application-industry/{slug}/` |

---

## 9. How to Add & Edit Polymer Products

1. In WP Admin, go to **Polymer Products → Add New Product** (or edit an existing polymer).
2. Enter the **Product Title** (e.g., `Polypropylene (PP)`), **Excerpt** (concise B2B summary shown on cards and hero), and **Main Content** (detailed overview).
3. Assign **Polymer Categories**, **Material Grades**, **Material Forms**, and **Application Industries** in the right sidebar.
4. Set a **Featured Image** (`1200×900` `4:3` industrial photograph recommended).
5. Populate the **Polymer Product Technical & Commercial Details** meta box (`inc/admin-meta-boxes.php`):
   - **Short Polymer Code** (e.g., `PP`, `HDPE`, `PVC`)
   - **Subtitle / Hero Lead** & **Placeholder Image Key** (fallback key when no Featured Image is uploaded)
   - **Available Forms Summary**, **Packaging Options**, **Minimum Order Quantity (MOQ)**, **Supply Availability**, and **Datasheet PDF URL or Media Attachment**
   - **Grades & Specifications Rows** (`One per line: Grade Name | Form | Typical MFI / Key Metric | Primary Use`)
   - **Manufacturing Applications** & **Industries Served** (`One item per line`)
   - **Processing Methods** (`One per line: Method Name | Guidance Note`)
   - **Technical Specifications Table** (`One per line: Property | Value / Standard | Test Method`)
   - **Product-Specific FAQs** (`One per line: Question? | Answer`)
6. Click **Publish / Update**. Any field left blank is cleanly omitted on the frontend rather than showing broken empty tables.

---

## 10. How to Manage Categories & Catalog Filters

- Navigate to **Polymer Products → Polymer Categories**, **Material Grades**, or **Material Forms**.
- On `/products/`:
  - **Polymer Category Pills & Dropdown** display categories assigned to published products.
  - **Grade & Material Form Dropdowns** automatically appear in the filter toolbar **as soon as at least one published product has a verified `material_grade` or `material_form` term assigned**. While unverified/empty, those dropdowns stay hidden so buyers never encounter dead, zero-result dropdowns.
  - Filtering works both **instantly via progressive vanilla JS** (`assets/js/products.js`) and **server-side via URL query parameters** (`?q=...&polymer_cat=...&material_grade=...&material_form=...`) when JavaScript is disabled or links are shared.

---

## 11. How to Edit Home, About, Contact, Products & Product Detail Pages

| Page / View | Template File | Section Partials |
|---|---|---|
| **Homepage** | `front-page.php` | `template-parts/sections/home/*.php` (12 modular files, 1 per section) |
| **About Page** | `page-templates/template-about.php` | `template-parts/sections/about/*.php` (4 grouped files covering 12 sections) |
| **Contact Page** | `page-templates/template-contact.php` | `template-parts/sections/contact/hero-info-form.php` & `options-channels-faq.php` (9 sections) |
| **Products Listing** | `archive-products.php` & `page-templates/template-products.php` | `template-parts/sections/product/listing-content.php` |
| **Product Detail** | `single-products.php` & `page-templates/template-product-detail.php` | `template-parts/sections/product/detail-content.php` (10 sections) |
| **Global Header / Footer** | `header.php`, `footer.php` | Shared components in `template-parts/components/*.php` |

Additionally:
- Page intros/content and Featured Images on `Home`, `About`, and `Contact` can be updated directly in WP Admin → **Pages**.
- Company name, phone, WhatsApp, email, and address are managed centrally in **Appearance → Customize → Raghushree Company & Contact**.

---

## 12. How to Replace Temporary Unsplash Images

**Zero AI-generated images** are used anywhere in this theme. All temporary visual placeholders are genuine industrial photographs from Unsplash managed in **one single file**: `inc/image-placeholders.php`.

You can replace placeholders with authentic Raghushree photography in **3 ways** (in priority order):
1. **Per Page / Product in WP Admin (No code needed):** Upload a real photo to the WordPress Media Library and set it as the **Featured Image** on any Product (`products`) or Page (`Home`, `About`, `Contact`). `raghushree_render_image()` automatically prioritizes `wp_get_attachment_image()` (`srcset` + `sizes` + Media Library `alt` text) over the placeholder registry.
2. **Centralized Media Attachment ID:** In `inc/image-placeholders.php`, set `'attachment_id' => 123` (your uploaded Media Library ID) on any registry key (`home_hero`, `home_about`, `polymer_pp`, `about_hero`, etc.).
3. **Direct Theme Asset Replacement:** Drop optimized `.webp`/`.jpg` files into `assets/images/` and update the `'url'` and `'alt'` entries in `inc/image-placeholders.php`.

> **Editorial Integrity Rule:** Until replaced with genuine Raghushree photography, stock placeholders are never captioned as actual Raghushree plants, warehouses, labs, or staff.

---

## 13. Recommended Image Dimensions & Formats

- **Preferred Formats:** `WebP` (or `AVIF`) with `JPEG` fallback; `SVG` for logos and vector icons.
- **Hero & Split Feature Visuals (`4:3`):** `1600 × 1200 px` (`< 220 KB` compressed)
- **Product Card & Detail Visuals (`4:3` or `16:10`):** `1200 × 900 px` (`< 160 KB` compressed)
- **Open Graph / Social Share Image:** `1200 × 630 px` (`< 180 KB`, configurable in Customizer)
- **Brand Logo (if replacing inline SVG mark via Customizer Custom Logo):** Transparent `SVG` or `PNG` (`320 × 80 px` max bounding box)

---

## 14. Contact Form Configuration & Email Setup

The 11-field industrial RFQ form (`#raghushree-enquiry-form`) is processed server-side in `inc/template-functions.php`:
1. **Fields captured:** Full Name*, Company Name*, Business Email*, Phone / WhatsApp*, Country / Market*, Enquiry Type*, Material / Polymer Category*, Preferred Grade / Form / Specification, Estimated Quantity, Manufacturing Application, Requirement Details & Message*.
2. **URL Prefill Support:** Links from any product page (e.g., `/contact/?material=Polypropylene+(PP)&enquiry_type=Request+a+Quotation#enquiry-form`) automatically pre-select the corresponding dropdowns.
3. **Security Controls:**
   - WordPress nonce verification (`raghushree_submit_enquiry`)
   - Hidden honeypot trap (`company_website_url`)
   - Per-IP transient rate limiting (max 5 submissions per 10 minutes)
   - Strict server-side sanitization (`sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`) and validation
4. **Dual Delivery (Zero Lost Leads):**
   - Stores every valid enquiry in WP Admin under **Client Enquiries** (`raghushree_enquiry` CPT) with full metadata.
   - Sends a formatted plain-text notification email via `wp_mail()` to the configured recipient (`marketing@raghushree.com` by default, editable in **Appearance → Customize → Raghushree Company & Contact**) with `Reply-To` set to the buyer's verified email.
5. **Recommended Production SMTP:** Install a lightweight SMTP transport plugin (such as *WP Mail SMTP* or *FluentSMTP*) connected to your `raghushree.com` mail server / transactional provider (SPF/DKIM/DMARC aligned) to guarantee inbox delivery.

---

## 15. SEO & Structured Data (Schema.org) Overview

- **Automatic SEO Plugin Detection (`inc/helpers.php`):** `raghushree_has_seo_plugin()` checks for Yoast SEO, Rank Math, All in One SEO, SEOPress, and The SEO Framework. If any SEO plugin is active, the theme automatically suppresses its own meta description, canonical, robots, Open Graph, Twitter Card, and JSON-LD output to guarantee **zero duplicate tags**.
- **Built-in SEO Meta Box (`inc/admin-meta-boxes.php`):** Every page and polymer product includes custom **SEO Title**, **Meta Description**, **Canonical URL Override**, and **Noindex** controls.
- **Built-in Open Graph & Twitter Cards (`inc/seo-meta.php`):** Outputs `og:title`, `og:description`, `og:url`, `og:site_name`, `og:type`, `og:image`, and `twitter:card` (`summary_large_image`).
- **JSON-LD `@graph` (`inc/schema.php`):**
  - `Organization` (with verified Kanpur `PostalAddress` and `ContactPoint`)
  - `WebSite` (with `SearchAction`)
  - Page-specific `WebPage`, `AboutPage`, `ContactPage`, `CollectionPage`
  - `Product` + `BreadcrumbList` on single polymer pages (strictly factual—never fabricates fake `AggregateRating` or `Offer` prices)
  - `FAQPage` generated **exclusively from FAQs visibly rendered on the current page** via `raghushree_register_visible_faqs()`.

---

## 16. Performance Architecture Notes

- **Self-Hosted Fonts (`assets/fonts/`):** `IBM Plex Sans` (`500`, `600`, `700`) and `Inter` (`400`, `500`, `600`, `700`) Latin WOFF2 files are served locally with `font-display: swap` and `<link rel="preload">` for primary weights—zero Google Fonts external blocking requests.
- **Modular Conditional Enqueuing (`inc/enqueue.php`):**
  - Shared core: `global.css`, `components.css`, `header.css`, `footer.css`, `global.js`, `navigation.js`
  - Homepage only: `home.css`
  - About only: `about.css`
  - Contact only: `contact.css` + `contact.js`
  - Products Catalog / Taxonomy Archive only: `products.css` + `products.js`
  - Product Detail only: `product-single.css` + `product-single.js`
- **Frontend De-bloat:** Unused `wp-emoji` scripts and frontend Gutenberg block library styles are dequeued on custom templates. Zero jQuery, Bootstrap, Tailwind, GSAP, or icon-font payloads.
- **Image Loading Strategy:** Above-the-fold hero visuals load with `loading="eager" fetchpriority="high" decoding="async"`; all below-the-fold images load with `loading="lazy" decoding="async"` and explicit `width`, `height`, and CSS `aspect-ratio` containers to prevent CLS.

---

## 17. Responsive Breakpoint Overview

Mobile-first stylesheet architecture tested across **16 viewport widths (`320px` through `1920px`)** plus tablet portrait (`768×1024`) and landscape (`1024×768`):
- **Base (`320px+`):** Single-column fluid layout, `20px` container gutters, `>= 44×44px` touch controls, accessible slide-down mobile drawer with collapsible `Products` accordion.
- **Large Mobile (`480px+`):** 2-column compact stat/badge grids where appropriate.
- **Tablet Portrait (`768px+`):** `32px` gutters, 2-column card grids and form rows.
- **Desktop Navigation & Split Threshold (`900px+`):** Full desktop navigation bar, interactive 2-column + feature card Product Mega Menu, equal-height 2-column split sections.
- **Desktop (`1200px+`):** `40px` gutters, 3- and 4-column industrial bento/card grids, sticky sidebar RFQ panels.
- **Wide Desktop (`1440px` – `1920px`):** `1320px` max content container (`--container-max`) maintaining comfortable `60–72ch` reading measure and balanced whitespace.

---

## 18. Typography & Color Token Overview

Defined in `:root` inside `assets/css/global.css`:

### Typography
- **Headings (`h1`–`h6`):** `'IBM Plex Sans', sans-serif` (tight industrial line-heights `1.08`–`1.28`)
- **Body, Eyebrows, Navigation, Buttons, Inputs, Tables:** `'Inter', sans-serif` (readable paragraph line-height `1.68`)
- **Strict Eyebrow Parity Rule:** `--text-eyebrow: var(--text-body)` (`clamp(0.9688rem, 0.94rem + 0.14vw, 1.0625rem)`). `.eyebrow` always matches `<p>` font size at every breakpoint across all pages, differentiated solely via weight (`600`), tracking (`0.08em`), uppercase transform, and crimson accent bar.

### Color Palette Tokens
| Token | Value | Usage |
|---|---|---|
| `--pearl` | `#FAFAFA` | Default page canvas |
| `--white` | `#FFFFFF` | Elevated card & header surfaces |
| `--tint` | `#F4F4F4` | Alternating section surface & inset form wells |
| `--ink` | `#0F1113` | Deep industrial dark sections & utility bar |
| `--ink-soft` | `#1A1D21` | Elevated cards on dark `--ink` surfaces |
| `--heading` | `#0F1113` | Primary heading & strong label color |
| `--text` | `#5B6067` | Primary body copy |
| `--muted` | `#71767E` | Secondary metadata & captions |
| `--accent` | `#E63946` | Brand industrial crimson indicator |
| `--cta` / `--cta-hover` | `#C5303C` / `#A62530` | High-contrast accessible primary CTA buttons |
| `--accent-light` | `#FF7A83` | Accessible crimson accent text on `--ink` dark surfaces |

---

## 19. Industrial Design Rules Summary

1. **Strictly Zero Visible Borders:** Every card, button, input, table, navigation bar, dropdown, mega menu, and section uses `border: 0`. Separation and subtle 3D industrial depth are achieved purely through tonal surface contrast (`--pearl`, `--white`, `--tint`, `--ink`, `--ink-soft`), ambient layered box-shadows (`--shadow-xs` through `--shadow-xl`, `--shadow-inset`), and spatial rhythm.
2. **Strict `2px`–`4px` Corner Radius:** Controlled globally via `--radius-xs: 2px`, `--radius-sm: 3px`, and `--radius-md: 4px`. Zero pill buttons or oversized rounded cards.
3. **Accessible Focus States:** Keyboard `:focus-visible` states use soft luminous accent `box-shadow` rings (`--focus-ring`) rather than harsh CSS borders.

---

## 20. Developer Customization Guide

- **Design Tokens:** Adjust spacing, fluid type clamps, shadows, or palette variables centrally in `assets/css/global.css`.
- **Reusable UI Partials:** Modify shared cards, buttons, section headers, breadcrumbs, stats, FAQs, or CTA banners once in `template-parts/components/*.php` to update all templates simultaneously.
- **Custom Fields Extension:** Register additional product meta keys in `inc/custom-fields.php` and expose them in `inc/admin-meta-boxes.php`.
- **Child Theme Ready:** All asset paths use `get_template_directory()` / `get_template_directory_uri()` and helper functions are modularly organized under `inc/`.

---

## 21. Deployment Checklist

- [ ] Upload `raghushree/` to `wp-content/themes/raghushree/` on staging/production.
- [ ] Ensure `WP_DEBUG_DISPLAY` is `false` in production `wp-config.php`.
- [ ] Activate the `raghushree` theme in **Appearance → Themes**.
- [ ] Set **Settings → Permalinks** to **Post name** (`/%postname%/`) and save.
- [ ] Configure transactional SMTP email delivery for `marketing@raghushree.com`.
- [ ] Enable page caching / Brotli or Gzip compression and HTTP/2+ static asset caching headers for `.woff2`, `.css`, `.js`, and `.webp`.

---

## 22. Pre-Launch Verification Checklist

- [ ] Confirm all verified company contact details in **Appearance → Customize → Raghushree Company & Contact**.
- [ ] Submit a live test enquiry on `/contact/` and confirm both **Client Enquiries** admin storage and inbox delivery at `marketing@raghushree.com`.
- [ ] Test click, hover, keyboard Tab, and `Escape` behavior on the desktop `Products` Mega Menu and mobile hamburger drawer.
- [ ] Replace temporary Unsplash stock visuals with authentic Raghushree product/facility photos as available.
- [ ] Review all 7 polymer product records and populate verified grade tables, pack weights, MOQs, and PDF technical datasheets where applicable.
- [ ] Verify Google Search Console sitemap submission and run Rich Results Test on `/`, `/contact/`, `/products/`, and `/products/polypropylene-pp/`.

---

## 23. Troubleshooting Guide

- **`/products/` or `/products/polypropylene-pp/` returns a 404:**
  Go to **Settings → Permalinks** and click **Save Changes** to flush WordPress rewrite rules. Also verify there is no conflicting trashed page using the slug `products`.
- **Contact form saves in WP Admin → Client Enquiries, but email does not arrive:**
  PHP `mail()` on shared/cloud hosts is often blocked by recipient spam filters without authenticated SMTP. Configure *WP Mail SMTP* or *FluentSMTP* with valid `raghushree.com` SMTP credentials.
- **Grade or Form filter dropdowns are not visible on `/products/`:**
  By design, `material_grade` and `material_form` dropdowns appear automatically once at least one published product has terms assigned in **Polymer Products → Material Grades / Material Forms**.
- **Duplicate SEO tags after installing an SEO plugin:**
  Clear your page cache. `raghushree_has_seo_plugin()` automatically disables theme meta/OG/schema tags when Yoast, Rank Math, AIOSEO, SEOPress, or The SEO Framework is active.

---

## 24. Items Client Should Verify Before Launch

Before public launch or paid traffic campaigns, Raghushree management should review and confirm:
1. **Specific Commercial Terms per Polymer:** Exact bag weights (e.g., 25 kg woven sacks / jumbo bags), domestic & export MOQs, dispatch lead times, and port/incoterm options.
2. **Verified Polymer Grade Tables & Technical Datasheets (TDS/COA):** MFI ranges, density values, intrinsic viscosity (for PET), K-values (for PVC), and downloadable PDF datasheets per grade.
3. **Quality Certifications & Memberships:** Any official ISO certificates, lab accreditations, GST/IEC export registration numbers, or trade association memberships to display once formally verified.
4. **Authentic Photography:** Real Kanpur office/warehouse, polymer granule close-ups, and packaging photographs to replace temporary Unsplash industrial placeholders.
5. **Legal Pages:** Finalized Privacy Policy and Standard Terms of Supply linked in `footer_legal`.

---

## 25. Future Scalability Notes

- **Expanded Grade-Level Catalog:** As Raghushree publishes dozens or hundreds of specific grades under `products` (or assigns `material_grade` and `material_form` terms), the catalog filter bar, taxonomy archives, and Mega Menu scale automatically without template changes.
- **CRM / ERP / Webhook Integration:** Hook into `save_post_raghushree_enquiry` (or extend `raghushree_handle_enquiry_submission()` in `inc/template-functions.php`) to push verified RFQs directly into Zoho CRM, Salesforce, HubSpot, Tally/ERP, or WhatsApp Business API.
- **Multilingual / Export Market Localization:** All template strings are wrapped in WordPress i18n functions (`__()`, `_e()`, `esc_html__()`, `esc_attr__()`) under the `raghushree` text domain for seamless WPML or Polylang translation (e.g., Arabic, French, Spanish, or Russian export portals).

### Product master and custom prepaid checkout

This theme and `integrations/raghushree-payments/` together provide a custom, WooCommerce-free integration. The plugin depends on this theme's product master, settings and shipping-quote code. It is **disabled by default and not production-verified**. Deploy and test on a staging WordPress site before enabling live payments. No third-party account or end-to-end WordPress test has been run by the repository tests.

#### Setup

1. Deploy the updated Raghushree theme. Copy the `integrations/raghushree-payments` directory into WordPress `wp-content/plugins/raghushree-payments`, then activate **Raghushree Payments & Orders**. The directory inside the theme is a distributable source copy; it will not be activated as a WordPress plugin automatically.
2. In **Products**, edit each product. Under **Product Master — Packs & Shipping**, enter the actual packed weight and outer dimensions in kg/cm for each option. Defaults are 10 kg ₹100, 20 kg ₹200 and 30 kg ₹300; saved prices and visibility are per product. You can enable a separate sample, whose product price is always ₹0 (shipping only). For a Page using Product Detail, save the template first, then edit again. The default prices are publicly displayed when this theme is active. One pack is ordered per checkout; tax is not separately calculated, so configured product prices are treated as final product prices.
3. Set the Shiprocket account's courier priority to **Recommended by Shiprocket**, and configure the complete pickup address in Shiprocket. In **Products → Payment & Shipping Setup**, enter the corresponding pickup location nickname and PIN (default `208001`), Shiprocket API-user credentials, Razorpay **test** key ID and secret, and a separate webhook secret. Blank credential fields retain saved values. The **Test** buttons check authentication only. Shiprocket rate requests use the configured real account; use a Shiprocket sandbox only if the integration is explicitly adapted and verified for that sandbox.
4. Install the webhook shown on the settings page in Razorpay test mode: subscribe to **order.paid**, use the same webhook secret, ensure a public HTTPS endpoint and automatic capture. Configure a reliable system cron to invoke WordPress cron, and confirm WP-Cron events and admin emails work. A WordPress site behind a proxy must correctly report HTTPS to WordPress.
5. On the staging site, enable checkout in the settings (leave **Live payment safety** unchecked), check a quote and run successful and failed Razorpay test payments, duplicate webhook delivery and a closed-browser test. Check **Products → Payment Orders**. Test mode deliberately records `test_simulated` and **never sends a test payment as an order to real Shiprocket**. Verify the sample separately; a zero-value sample item and the shipping-only invoice require testing with Shiprocket before enabling live operation.

#### Behaviour and operations

- The website collects delivery details, obtains Shiprocket's recommended available prepaid courier rate and stores a 15-minute server-side quote. The product and shipping total are determined server-side, then a Razorpay order is created. Customer-facing descriptions and the Shiprocket item name include the product and pack. The chosen quote is an estimate and is not a courier booking; a later manual courier choice or weight adjustment can change actual shipping charges. Fail closed if the recommendation or its rate is absent.
- Checkout signature and server-fetched captured payment/paid order are checked; a separately signed `order.paid` webhook can confirm payment even if the customer closes their browser. Test and live credentials are a single set: do not switch mode, rotate secrets or deactivate the plugin while orders remain unresolved. Configure the correct webhook for each mode. No card data is stored by this code.
- A successful **live** payment queues a Shiprocket prepaid order with the product, variation, price, delivery address, package and quoted shipping charge. The merchant selects the courier and schedules pickup in Shiprocket. Test mode simulates only; it does not create any real order there. A successful API request does not guarantee eventual delivery or availability.
- Shipment creation uses a persistent one-attempt lock. If a create response is uncertain or a worker crashes, **check Shiprocket for the unique reference shown in Payment Orders before any manual action**; the plugin intentionally does not retry an ambiguous request. Authentication/configuration failures before a create request can be retried from Payment Orders. Admin email alerts and WP-Cron are operational dependencies. Monitor paid orders even if email/cron fails. The order screen currently shows the latest 100 orders and is not a complete fulfilment or reconciliation system.
- Credentials and customer/order data are stored in the WordPress database, **not encrypted by this theme/plugin**. Restrict administrator and database access, use HTTPS, secure backups, apply edge rate limiting, and define privacy/retention and refund/cancellation procedures. Blank secret inputs retain their saved values; check Clear to delete them. The system does not yet implement automatic refunds, tax calculation, stock, cart quantities or order tracking.
- **Do not enable live mode until a qualified operator has tested the installed site, real Shiprocket response formats and prices, webhook delivery, sample order acceptance, retries/reconciliation, privacy/security and the failure paths end-to-end.** To use live mode, separate explicit live safety consent and matching live keys/webhook are required. A controlled live order is needed to verify real Shiprocket order creation; Razorpay test mode cannot verify it.

Standalone PHP smoke tests (fake WordPress and provider responses): `php tests/product-master-test.php`, `php tests/shipping-quotes-test.php`, `php tests/checkout-create-test.php`, `php tests/payment-flow-test.php`.

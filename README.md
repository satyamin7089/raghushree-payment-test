# Raghushree — Custom B2B Industrial WordPress Theme

Custom WordPress theme built specifically for **Raghushree (`Raghushree Plast Products Pvt. Ltd.`)**, an Indian manufacturer, exporter, and supplier of thermoplastic polymer raw materials (`PP`, `HDPE`, `LDPE`, `LLDPE`, `PVC`, `PET`, and `Recycled & Reprocessed Polymers`).

> **Start here for the product master, Razorpay and Shiprocket setup:** [Product master and payments guide](#26-product-master-payments-and-manual-delivery). This guide covers installation, dashboard settings, test payments, manual delivery and developer details.
>
> **How the code connects:** [Code architecture README](docs/code-architecture/README.md) traces product data, shipping quotes, checkout, webhooks and order storage through the actual files and hooks.
>
> **Current status:** the code and mocked tests are present, but the payment flow has not been verified end-to-end on the deployed WordPress site with the merchant's actual accounts. Keep live checkout disabled until staging and controlled live checks are complete. Shiprocket is used for **quotes only**; orders and delivery are managed manually in WordPress.

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
26. [Product master, payments and manual delivery](#26-product-master-payments-and-manual-delivery)

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
- **Product information:** Unverified grade specifications, MOQ and datasheets should be left blank until confirmed. The separate product master currently supplies default pack sizes and prices; administrators must verify those commercial values before displaying or selling them.

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

## 26. Product master, payments and manual delivery

This section is the main handover for the custom prepaid checkout. It is written for both the store owner and the developer maintaining the site.

### 26.1 What has been built

The existing WordPress theme controls the product pages and their appearance. A small, separate custom plugin adds payment and order management. **WooCommerce is not required.**

| Part | What it does | What it does not do |
| --- | --- | --- |
| **Product master** | Lets an administrator set pack prices, visibility and package measurements per product. Supports an optional free sample. | Does not currently let an administrator add arbitrary new pack sizes or offer separate purchasable material-grade choices. |
| **Shiprocket** | Provides the recommended available prepaid courier and a shipping-rate quote. | Does not create an order, assign a courier, generate an AWB, or book a pickup in this version. |
| **Razorpay** | Opens checkout and accepts a prepaid test or live payment, according to the selected mode. | Does not arrange delivery. A successful authentication test alone does not verify checkout or webhooks. |
| **Payment Orders** | Shows the order, payment and shipping quote in WordPress. An administrator can record manual fulfilment. | Does not automatically ship, notify customers of tracking, or create an order in Shiprocket. |

**Status and responsibility:** The repository contains implementation and mocked tests, but it has not been verified here end-to-end on the actual WordPress installation or merchant accounts. Do not call this production-ready until the staging and live-readiness checks below have passed. Shiprocket and Razorpay may charge their normal service fees; check their terms separately.

### 26.2 The customer journey, in plain language

```text
Product page → Buy Now → choose pack or sample → enter delivery PIN
→ check Shiprocket shipping rate → enter delivery details
→ pay product price + quoted shipping with Razorpay
→ verified payment recorded in WordPress → owner arranges delivery manually
```

1. **Buy Now** leads to the pack-options section of the same product page. The customer selects one option and enters a delivery PIN.
2. The server asks Shiprocket for a **prepaid** recommended-courier rate using the pickup PIN, delivery PIN and that option's packed weight and dimensions.
3. The customer sees the product price, estimated shipping and total. After a valid quote, the delivery-details form and Razorpay checkout become available when the integration is enabled and configured.
4. WordPress records the pending order and creates a Razorpay order for the server-calculated total. The product title and pack appear in the checkout description and Razorpay order notes, subject to Razorpay's display behavior.
5. WordPress confirms payment using the checkout response and Razorpay API, or a verified `order.paid` webhook. Only a confirmed captured payment / paid order is marked **paid**.
6. The owner views the order in **Products → Payment Orders**, then arranges delivery themselves. The owner can record their chosen courier and tracking reference there.

**Example:** if the 10 kg pack is ₹100 and Shiprocket returns a ₹45 shipping quote, the checkout total is ₹145. For a free sample with a ₹45 quote, the total is ₹45. These are illustrations; the actual shipping quote comes from Shiprocket. A quote is an estimate, not a booking or a guarantee of the final courier charge. Differences caused by later courier or weight changes are not automatically charged to the customer.

### 26.3 Product master: what the administrator can manage

The existing **Products** WordPress custom post type is the product master; there is not a separate WooCommerce catalog. For each product, open **Products → All Products → Edit**, then find **Product Master — Packs & Shipping**.

| Option | Default product price | Default visibility | Editable fields |
| --- | ---: | --- | --- |
| 10 kg | ₹100 | Shown | Price, packed weight, length, width, height, Show checkbox |
| 20 kg | ₹200 | Shown | Same |
| 30 kg | ₹300 | Shown | Same |
| Sample | ₹0 | Hidden | Packed weight, length, width, height, Show checkbox; sample price stays ₹0 |

- Defaults apply to each product until that product is saved with its own settings. Prices for the three pack options can be changed per product; the prices and enabled options are displayed publicly on the product page.
- Enter **actual packed weight in kg**, including packaging, and **outer length, width and height in cm** for each option. A field can be left blank while saving, but a quote for that option will not work until all four measurements are positive and present.
- Tick **Show** for options that should appear; untick it to hide an option. To offer a sample, tick **Show** on the Sample row and enter its measurements. Its product price cannot be changed from ₹0; the shipping quote is charged separately.
- Click **Update** or **Publish** after changes. Prices must be valid for all three pack rows, even if a row is hidden; invalid submitted values can prevent the master data from being saved. Re-open the product to confirm important changes.
- For a normal WordPress Page using the **Product Detail** template, first assign and save that template, then edit the page again to see the master box.
- A **material grade** such as a specific polymer grade is not the same as a pack size. The theme has an existing Material Grades taxonomy for catalog information, but the current checkout does **not** offer a separate grade selector or grade-specific pricing. The three pack sizes are fixed in code; adding or renaming sizes requires development.
- Checkout handles **one selected pack per order**, not a multi-item cart or editable quantity. Product prices are treated as final product prices; the code does not calculate a separate tax.

### 26.4 Files, installation and safe upgrades

| Item | Location | Purpose |
| --- | --- | --- |
| Raghushree theme | Repository root | Product pages, product master, settings and shipping quote integration. |
| Plugin source | `integrations/raghushree-payments/` | PHP payment/order logic and checkout JavaScript. This folder inside the theme is **not automatically activated** as a plugin. |
| Installable plugin ZIP | [`plugin-download/raghushree-payments.zip`](plugin-download/raghushree-payments.zip) | Upload this ZIP in WordPress. The plugin name is **Raghushree Payments & Orders**; the current code version is **0.2.0**. |

To install or update:

1. Back up the website and database. On an existing installation, disable checkout temporarily.
2. Deploy the updated theme, then go to **Plugins → Add New → Upload Plugin** and upload the plugin ZIP. If WordPress offers **Replace current with uploaded**, replace the old copy rather than activating two copies. Alternatively, a developer can copy the source directory to `wp-content/plugins/raghushree-payments`.
3. Activate the plugin and confirm its version. Update **both** theme and plugin: the plugin relies on theme functions and the updated pair includes compatibility safeguards. Settings and orders are stored in WordPress and are not intentionally deleted by the upgrade; still keep the backup.
4. If only the new theme is installed while the old payment plugin remains active, the theme disables new checkout and the old automatic Shiprocket worker. If only the new plugin is installed with an old theme, checkout is disabled. This cannot stop a request already running during an upgrade.
5. **Check older orders before dispatching.** The previous plugin version could have created a Shiprocket order already, or left an uncertain result. The Payment Orders screen retains available legacy status, reference and Shiprocket IDs. Check Shiprocket before creating a manual shipment to avoid duplicates; this update does not cancel existing Shiprocket orders.

### 26.5 Connect Razorpay and Shiprocket in WordPress

Use a **staging site** for setup and testing. Your site needs HTTPS; the webhook URL must be publicly reachable by Razorpay. On a reverse proxy, WordPress must correctly detect HTTPS. The theme declares WordPress 6.2+ and PHP 8.0+; verify the actual WordPress/PHP combination and outbound HTTPS connectivity on your host.

Open **Products → Payment & Shipping Setup** in WordPress:

| Field | What to enter or select |
| --- | --- |
| **Razorpay mode** | **Test** while testing. Test and live keys are separate; test Key IDs normally begin with `rzp_test_`. |
| **Pickup PIN code** | The origin PIN used to request the shipping quote. The supplied default is `208001`; confirm it is your actual pickup PIN. A PIN does not create a Shiprocket address or book a pickup. |
| **Razorpay Key ID** | Your Razorpay test Key ID. |
| **Razorpay Key Secret** | The matching Razorpay test API secret. |
| **Razorpay Webhook Secret** | A separate strong random secret that **you choose** for the webhook; the same value must be entered in Razorpay. It is not the API Key Secret. |
| **Shiprocket API user email / password** | Credentials for a Shiprocket **API user**, created in the Shiprocket dashboard. These are not necessarily your normal seller-login credentials. |
| **Enable checkout** | Keep off until setup is complete; enable it on staging for test checkout. |
| **Live payment safety** | Keep **unchecked** in testing. Live checkout requires both this confirmation and Enable checkout. |

Click **Save Changes**. Saved secrets are not shown again: a blank field preserves its existing value; the **Clear** checkbox removes it. Restrict administrator and database access and protect backups because the theme stores these credentials in the WordPress database **without application-level encryption**. Never paste keys, passwords or webhook secrets in chat or commit them to Git.

For Shiprocket, create an API user under the dashboard's **Settings → API** area and grant the relevant API access. Set the account's courier priority to **Recommended by Shiprocket**. The code uses the configured Shiprocket production API endpoint for quotes in both Razorpay modes; it does **not** provide a Shiprocket sandbox or a simulated shipping-rate option. A working Shiprocket API user and rate response are therefore required even for the Razorpay test checkout.

After saving, click **Test Razorpay** and **Test Shiprocket**. These buttons check authentication only: green success does **not** prove that the webhook, shipping quote, product page or complete payment flow works.

### 26.6 Configure the Razorpay webhook

A webhook is a server-to-server notification from Razorpay. It helps the site learn that an order was paid even if the customer closes the browser before the checkout response reaches WordPress.

1. In **Products → Payment & Shipping Setup**, copy the displayed **Razorpay webhook URL**. Copy the exact value from the installed site; do not guess it from the domain. The route is registered by the active custom plugin.
2. In the Razorpay Dashboard, select **Test Mode**, then go to **Accounts & Settings → Webhooks → Add New Webhook**.
3. Paste the WordPress URL. Create a strong, random webhook secret using a password manager and enter it there. Select **`order.paid`**; selecting every event is unnecessary for this implementation.
4. Enter the **same** webhook secret in WordPress under **Razorpay Webhook Secret**, then save both dashboards. The webhook secret is separate from the Key Secret.
5. Configure Razorpay payment capture appropriately; the integration requires a **captured** payment and **paid** order before treating an order as paid. Check Razorpay's webhook delivery logs during testing.

Use the exact displayed public HTTPS URL, not localhost. Test and live webhooks and keys must be configured for their respective modes. Only one set of credentials and one webhook secret are stored in this implementation: do not switch mode or rotate secrets while orders remain unresolved without a reconciliation plan. If a secret is rotated, older retries may still be signed with the previous secret, which this implementation does not retain.

### 26.7 Test the complete flow

**Before starting:** confirm the updated theme and plugin are active; Razorpay is in Test mode; the test keys, webhook secret and Shiprocket API-user credentials are saved; `order.paid` webhook delivery is configured; and the selected pack has complete dimensions and weight. Leave **Live payment safety** unchecked.

1. On staging, check **Enable checkout** and save. Open a published product page and choose an enabled pack.
2. Enter a valid delivery PIN and click **Check shipping**. Confirm the product name, pack, quoted courier, shipping charge and total. If no rate is returned, no checkout should proceed for that quote.
3. Enter the customer details and open Razorpay checkout. Use Razorpay's official **test-mode** payment instructions; do not use a real card or make a live payment as a substitute.
4. Complete a successful test payment. In **Products → Payment Orders**, find the order marked **TEST**, verify the product, option, address, product amount, shipping, total and payment IDs, and confirm the payment status is **paid**. Check Razorpay's test dashboard and the `order.paid` webhook delivery log too. A test payment uses no real money.
5. Verify there is **no new Shiprocket order or pickup**. The Shiprocket production account is contacted for the rate only; the test uses real-account quote data, not a separate Shiprocket sandbox.
6. Repeat with a sample: enable the sample and its measurements, check that product price is ₹0 and total equals quoted shipping. If Shiprocket rejects or cannot quote a zero-value sample, treat it as an unresolved issue—do not invent a shipping charge or turn on live checkout.
7. Also test a failed or closed checkout, an unserviceable PIN, missing measurements, a changed pack/price, an expired quote, and a customer closing the browser after payment. Confirm that unpaid or uncertain orders are not fulfilled and that duplicate webhooks do not reset manual delivery status. Some failed/cancelled attempts may remain **pending** in WordPress; this implementation does not automatically mark every failed attempt as failed.

**If payment is uncertain:** do not ask the customer to pay again immediately and do not ship. Compare the Razorpay order/payment status, amount and currency with the WordPress order and inspect webhook delivery. A payment can succeed even when the browser response fails; seek technical help and reconcile before taking action. There is no administrator button that automatically reconciles an unresolved order.

**Before accepting live payments:** resolve any staging failures and have a developer review the security and reconciliation limitations below. Set up the live API keys and a live-mode `order.paid` webhook with the matching secret, confirm there are no unresolved test orders, switch WordPress to Live, and enable both checkout and live safety. Run a small, controlled real payment and confirm it in WordPress and Razorpay before allowing ordinary customers to pay; this payment uses real money. Keep delivery manual and have a documented process to handle refunds and discrepancies.

### 26.8 Manage paid orders and delivery

Open **Products → Payment Orders**. The paginated screen shows the internal order number, **TEST/LIVE** mode, customer and address, product/pack, package measurements, payment status and IDs, product/shipping/total amounts, and quoted courier information.

- **Pending** means the order has not been confirmed paid by this integration. Do not fulfil it. `order_creation_failed` means the Razorpay order creation did not complete successfully in the integration; investigate before retrying.
- **Paid** means the integration recorded a verified paid order. Before delivery, also review the order and reconcile with Razorpay as appropriate. A **TEST** order is not a real payment and must not be shipped as a live order.
- For a paid order, the administrator can save **Awaiting manual fulfilment** or **Fulfilled manually**, plus optional courier and tracking reference. These are internal notes/status only: they do not book delivery, create a Shiprocket order, or send tracking notifications to the customer.
- Payment status and manual delivery status are separate. Duplicate payment notifications should not reset a manually saved delivery status. Only paid orders can have their manual delivery details updated through the admin form.
- Quote amounts are stored with the order. If the actual delivery cost differs later, decide how to handle the difference with the customer; the integration does not automatically collect an extra amount.

### 26.9 Troubleshooting

| Issue | What to check |
| --- | --- |
| Product master box missing | Edit a **Products** item, or save a Page with the **Product Detail** template and reopen it. Confirm the updated theme is active. |
| Pack or sample missing | Check its **Show** box and update the product. Sample is hidden by default. |
| Shipping details not configured | Enter positive packed weight and all three dimensions for the selected option; save and retry. |
| Shipping rate unavailable | Check Shiprocket API-user credentials, origin and destination PINs, prepaid serviceability, package data and recommended-courier response. Credentials may authenticate even when a usable quote is unavailable. |
| Razorpay/Shiprocket authentication test fails | Save the right credentials first; verify test/live mode and API permissions, and check the server can make HTTPS requests. Don't share secrets in a support screenshot. |
| Delivery form or Pay button does not appear | Confirm matching updated theme and plugin, active plugin, HTTPS, checkout enabled, test/live credentials and webhook secret saved, Shiprocket credentials saved, and a successful quote. In live mode the extra live-safety confirmation is also required. |
| Payment completed but order remains pending | Check Razorpay payment is captured and order paid, amounts/currency match, the site's public webhook URL/secret and `order.paid` subscription are correct, and webhook delivery logs. Reconcile before retrying or dispatching. |
| Old plugin / theme compatibility notice | Update both components to the matching rates-only versions. Check older Shiprocket orders before manual delivery. |
| Actual shipping charge differs | The rate shown at checkout is an estimate, not a courier booking. Compare package measurements, weight and courier choice with the quote recorded in the order. |

### 26.10 Technical reference

**Key files**

- `inc/custom-post-types.php` registers `products` and product taxonomies; `inc/product-master.php` supplies default packs and stores per-product master data in `_raghushree_master_variations` post meta. Pack prices are stored as integer paise.
- `template-parts/sections/product/detail-content.php`, `assets/js/shipping-quote.js` and `assets/css/product-single.css` render the existing product page, quote form and checkout section.
- `inc/payment-settings.php` provides the administrator-only settings and authentication checks. The WordPress option `raghushree_payment_settings` contains the configured credentials and mode.
- `inc/shipping-quotes.php` validates product, enabled option, PIN and measurements, authenticates with Shiprocket, and calls its courier serviceability endpoint with prepaid `cod=0`. It selects the rate matching `recommended_courier_company_id`; missing recommendation/rate fails closed. The quote endpoint has a basic transient-based per-client throttle; use infrastructure-level rate limiting as well.
- `integrations/raghushree-payments/raghushree-payments.php` implements checkout, payment verification, webhooks and the order screen; `integrations/raghushree-payments/checkout.js` opens Razorpay Checkout. The plugin depends on theme functions and the rates-only compatibility marker.
- `inc/payment-compat.php` guards against using the updated theme with the older automatic-shipment plugin. The plugin similarly blocks new checkout when the matching theme is missing.

**Quote and order lifecycle**

1. Public AJAX action `raghushree_shipping_quote` retrieves a quote. The `raghushree_quote_result` filter stores a **15-minute** server-side transient containing product/pack, price, shipping quote, courier, pickup/delivery PIN, dimensions, mode and quote time. A random checkout token refers to the snapshot; the browser is not trusted for price.
2. `rgpay_start_checkout` validates the token and current product/settings, validates customer details, and uses an atomic WordPress option claim to prevent reuse of a quote. It records an `rgpay_order` and creates a Razorpay Orders API order in INR paise. A scheduled WordPress event cleans up the quote claim; it is not a shipment worker.
3. The Razorpay checkout handler sends the returned payment ID, order ID and signature to `rgpay_verify_checkout`. The server verifies the checkout HMAC using the stored order ID and API secret, fetches Razorpay payment and order status, and checks IDs, currency and amount before recording paid status.
4. `POST` to the displayed `raghushree/v1/razorpay-webhook` REST route verifies `X-Razorpay-Signature` against the **raw body** and configured webhook secret. For `order.paid`, it matches the stored order and captured payment details before recording paid status. Unknown/non-`order.paid` events are not used to fulfil an order. **Known reconciliation risk:** an unknown Razorpay order receives HTTP 200, so a webhook arriving before its order ID is saved in WordPress could be acknowledged without recording payment and without a Razorpay retry. Do not rely solely on the browser callback or webhook delivery status; review and resolve this risk before live use.
5. `rgpay_order` is a non-public custom post type; `_rgpay_record` stores the order snapshot and customer data, while other `_rgpay_*` post meta fields store payment and manual delivery details. The admin order screen requires `manage_options`; manual updates require that capability, a nonce and a paid order. No Shiprocket order-creation action or fulfilment worker is registered in this version.

**Security and operational limits:** Product and customer data, payment configuration and secrets reside in WordPress's database; the code does not add application-level encryption. Use least-privilege admin access, HTTPS, protected backups, privacy/retention policies, edge rate limits and monitoring. Keep the public webhook reachable and review failed deliveries. WP-Cron is used for quote-claim housekeeping; paid-order recording does not require a shipping cron worker. There is no automatic refund/cancellation reconciliation, stock management, cart/quantity system, tax calculation, customer tracking notification or Shiprocket sandbox. Establish manual processes for those needs before launch.

**Tests:** The repository provides standalone PHP smoke tests with mocked WordPress/provider responses; they are not real integration tests:

```bash
php tests/product-master-test.php
php tests/shipping-quotes-test.php
php tests/checkout-create-test.php
php tests/payment-flow-test.php
php tests/payment-compat-test.php
php tests/plugin-compat-test.php
```

The plugin ZIP can be regenerated from source with `python3 scripts/package-payment-plugin.py`. Ensure the ZIP matches the source after any plugin change. Run staging tests against the actual services and review security, webhook handling, failure recovery and reconciliation before any live rollout.

### 26.11 Official references

For Razorpay behavior, treat its documentation as the source of truth:

- [Razorpay documentation home](https://razorpay.com/docs/home/)
- [Razorpay PHP integration and signature verification](https://razorpay.com/docs/payments/server-integration/php/integration-steps/?preferred-country=IN)
- [Razorpay Standard Checkout best practices](https://razorpay.com/docs/payment-gateway/web-integration/standard/best-practices)
- [Razorpay payment webhook setup](https://razorpay.com/docs/webhooks/setup-edit-payments/?preferred-country=IN) and [webhook best practices](https://razorpay.com/docs/webhooks/best-practices/?preferred-country=IN)
- [Shiprocket API documentation](https://apidocs.shiprocket.in/) and [Shiprocket API user setup](https://support.shiprocket.in/support/solutions/articles/43000337456-shiprocket-api-document-helpsheet)

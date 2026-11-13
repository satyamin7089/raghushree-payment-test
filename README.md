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

### Product master, prepaid checkout and manual delivery

The Raghushree theme and `integrations/raghushree-payments/` provide a custom, WooCommerce-free checkout. The plugin depends on the theme's product master, settings and shipping-quote code. **Shiprocket is used only for rate lookup**; this version never creates a Shiprocket order, assigns a courier, or books a pickup, in either test or live mode. The merchant arranges delivery manually. The integration is **not production-verified**: test it on staging with the real provider accounts before considering live payments.

#### Install or upgrade

1. Back up your WordPress site and disable checkout before updating. Deploy the updated theme **and** replace/activate the updated `Raghushree Payments & Orders` plugin (`integrations/raghushree-payments/`). Updating only one component will not apply the complete change. The plugin source inside the theme is not automatically activated; copy that directory to `wp-content/plugins/raghushree-payments`, or upload `plugin-download/raghushree-payments.zip` using WordPress's plugin replacement flow. The installable ZIP is generated from the plugin source with `python3 scripts/package-payment-plugin.py`. Existing settings and orders are stored in WordPress and are not intentionally removed by the update. The updated theme disables new checkout and the old automatic-shipment worker while the previous plugin is installed; the new plugin similarly disables checkout with an old theme. This cannot stop a request already in progress during an upgrade, so disable checkout and review old orders first.
2. Before manually shipping any pre-upgrade order, inspect it in **Products → Payment Orders** and check Shiprocket. The previous plugin version could have created an order already, or left an uncertain request. Historical Shiprocket status, reference and IDs remain visible; no automatic retry or cancellation occurs. Previously scheduled fulfilment events have no worker in this version.
3. In **Products**, edit each product. Under **Product Master — Packs & Shipping**, enter actual packed weights and outer dimensions in kg/cm. Default pack prices are 10 kg ₹100, 20 kg ₹200 and 30 kg ₹300, editable per product. Enable the optional sample separately; the sample product costs ₹0 and the customer pays only the shipping quote. For a Page using the Product Detail template, save the template first, then edit again. Product prices are publicly displayed and shipping is additional. One pack is ordered per checkout; tax is not separately calculated, so product prices are treated as final product prices.
4. In Shiprocket set courier priority to **Recommended by Shiprocket**. In **Products → Payment & Shipping Setup**, enter pickup PIN (default `208001`), Shiprocket API-user credentials, Razorpay **test** Key ID and Secret, and a separate webhook secret. The pickup-location nickname is not needed for a quote and no pickup is booked. Blank credential fields retain saved values; use Clear to delete them. The Test buttons check authentication only. Shiprocket rate requests use the configured real account; the code does not provide a Shiprocket sandbox.
5. Activate the webhook URL shown on the settings page in Razorpay **test mode**, subscribe to **order.paid**, set the same webhook secret in both places, and configure automatic capture. The WordPress HTTPS site and webhook must be publicly reachable; a reverse proxy must correctly report HTTPS. Keep live safety unchecked. On staging, enable checkout and test successful and failed payments, duplicate webhooks, the browser closing before callback, different PINs, normal packs and shipping-only samples. Check **Products → Payment Orders**; test payments are marked TEST and never represent real money. Confirm no Shiprocket order is created.

#### Operation and safety

- The server looks up the recommended available prepaid courier and its rate, then saves a 15-minute server-side quote snapshot including product and variation, product price, shipping charge, courier ID/name, origin/destination PIN, package details and quote time. The amount charged through Razorpay is the saved product price plus the saved shipping quote; browser-provided prices are not trusted. When a recommended courier or rate is missing, quoting fails closed. The quote is an estimate, not a booking; subsequent courier selection, dimensions or weight adjustments can change your actual delivery cost. Such differences are not automatically charged to the customer.
- Razorpay checkout signatures and server-fetched captured payment/paid order details are verified. A separately signed `order.paid` webhook also confirms payments even if a customer leaves the site. Paid orders are retained in WordPress; no shipment API is called. Only fulfil orders marked paid. In **Products → Payment Orders**, the admin can view the item, customer/address, payment references, item and shipping breakdown, courier quote and package details. The admin can manually mark a paid order awaiting fulfilment or fulfilled, and optionally save courier and tracking details. These fields are internal records only; they do not book a courier or send a customer notification. Test orders should not be fulfilled as real orders. The list is paginated.
- Payment state and manual delivery state are separate. Duplicate webhook deliveries do not reset an admin's manual fulfilment status. Pending or failed orders cannot be marked fulfilled. Payment verification does not depend on a shipment-processing cron job; however WP-Cron is still used for housekeeping and reliable webhook delivery is essential. Do not switch Razorpay mode or rotate secrets while orders remain unresolved; the settings store one set of keys and one webhook secret.
- Credentials and customer/order data are stored in the WordPress database, **not encrypted by this theme/plugin**. Restrict administrator and database access, use HTTPS, secure backups, apply edge rate limiting, and define privacy/retention, refunds, cancellation and payment-reconciliation procedures. Automatic refunds, tracking notifications, tax calculation, stock and cart quantities are not implemented.
- **Do not enable live mode until the installed site, Shiprocket response formats/rates, webhook delivery, payment failure paths and manual fulfilment process have been tested end to end.** Live mode requires matching live keys and webhook plus explicit live-safety confirmation. A successful authentication test or mocked code test does not prove the integration works against an account.

Standalone PHP smoke tests (fake WordPress and provider responses): `php tests/product-master-test.php`, `php tests/shipping-quotes-test.php`, `php tests/checkout-create-test.php`, `php tests/payment-flow-test.php`, `php tests/payment-compat-test.php`, `php tests/plugin-compat-test.php`.

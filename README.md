# Raghushree — Custom B2B Industrial WordPress Theme

Custom WordPress theme built specifically for **Raghushree (`Raghushree Plast Products Pvt. Ltd.`)**, an Indian manufacturer, exporter, and supplier of thermoplastic polymer raw materials (`PP`, `HDPE`, `LDPE`, `LLDPE`, `PVC`, `PET`, and `Recycled & Reprocessed Polymers`).

> **Start here for the product master, Razorpay and Shiprocket setup:** [Product master and payments guide](#26-product-master-gst-checkout-and-manual-delivery). This guide covers installation, dashboard settings, test payments, manual delivery and developer details.
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
26. [Product master, GST, checkout and manual delivery](#26-product-master-gst-checkout-and-manual-delivery)

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
- **Product information:** Unverified grade specifications, MOQ and datasheets should be left blank until confirmed. The product master supports configurable pack prices; administrators must verify commercial values before displaying or selling them.

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

## 9. How to edit a product, its card and gallery

The **Products** menu contains both polymer and pipe products (some older editor labels still say “Polymer”). The same content box is also available on an existing Page using the Product Detail template after saving that template choice.

### Write the page and card

1. Go to **Products → All Products → Edit** (or choose **Add New**). Enter the title. Write your paragraphs, headings and lists in the main editor; they appear under **Product overview**. Leave the content empty if you do not need an overview.
2. In **Raghushree Polymer Product Specifications & Supply Data**, enter the **Short card and product introduction**. It appears both on the card and at the top of the product page. If it is empty, the WordPress **Excerpt** is used; if that is also empty, the first 26 words of the main description are used. If changing the Excerpt does not change the card, check this short introduction first.
3. Add **Product highlights**, one point per line, for the bullets at the top. These are optional. The page no longer adds generic product claims or stock slideshow images.
4. Set a **Featured Image** in the product sidebar. It is the product card image and first product-page image. Without one, the card uses its configured placeholder; the product page uses the first gallery image, or a neutral empty image area if there are no photos. Add useful alternative text in Media Library.
5. Choose an existing supported **Polymer Category** (the categories also include pipes). You may also add verified **Material Grades** and **Material Forms** as card tags. Only categories in the predefined catalog appear in its listing.
6. Open **Additional structured details (optional)** only if you need extra tables, applications, packaging, processing, a datasheet or FAQs. Existing values remain saved and populated values appear on both polymer and pipe pages. For example, enter a FAQ on each line as `Question | Answer`. You can instead use regular headings and lists in the main editor.
7. Configure **Product Master — Pricing, GST & Shipping** separately if online purchase is needed; content editing does not set up checkout. Click **Update** or **Publish**, then preview the page and `/products/`.

The layout retains functional labels, purchase/enquiry controls and headings for optional sections. Old hard-coded marketing sections and generic slider images are no longer shown. Previously saved structured details are not deleted; review them and clear any you no longer want displayed. Existing related-product IDs remain stored but are not displayed in the simplified layout.

### Add, remove and arrange gallery images

1. Edit a product, find **Additional product images** and click **Choose images**. Upload or select one or more images from the WordPress Media Library, then click **Add images**. No image IDs are needed.
2. The image previews let you **Move earlier**, **Move later** or **Remove**. Click **Update** to save. The Featured Image is managed separately.
3. Both pipe and polymer product pages show the Featured Image followed by your gallery images, in your chosen order. Duplicate Featured Images and invalid image references are skipped. With two or more images, visitors can use the arrows/dots; automatic rotation respects reduced-motion settings. A single image is shown without slider controls.
4. Existing saved gallery IDs continue to work. If the Media Library JavaScript is unavailable, the old comma-separated ID field remains available as a fallback; do not remove existing values unless intended.

---

## 10. How to manage the product catalog

- Edit supported categories under **Products → Polymer Categories** and assign the relevant supported category to each product. See [Install the requested catalog](#install-the-requested-catalog) for how existing and new products are handled.
- The `/products/` catalog groups matching published products by category, with server-side search (`product_q`), category selection (`polymer_cat`) and pagination (`catalog_page`). Each group displays two cards per row; a single remaining card leaves the second position empty.
- The category list in the current template is predefined by the catalog setup; creating a new category in WordPress alone will not automatically add it to this listing.

---

## 11. How to Edit Home, About, Contact, Products & Product Detail Pages

| Page / View | Template File | Section Partials |
|---|---|---|
| **Homepage** | `front-page.php` | `template-parts/sections/home/*.php` (12 modular files, 1 per section) |
| **About Page** | `page-templates/template-about.php` | `template-parts/sections/about/*.php` (4 grouped files covering 12 sections) |
| **Contact Page** | `page-templates/template-contact.php` | `template-parts/sections/contact/hero-info-form.php` & `options-channels-faq.php` (9 sections) |
| **Products Listing** | `archive-products.php` & `page-templates/template-products.php` | `template-parts/sections/product/listing-content.php` |
| **Product Detail** | `single-products.php` & `page-templates/template-product-detail.php` | `template-parts/sections/product/detail-content.php` (owner-entered sections) |
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

## 26. Product master, GST, checkout and manual delivery

> **Deployment status:** This version changes totals and requires the matching theme and **Raghushree Payments & Orders plugin 0.3.0**. Back up the database and site, disable checkout, update both components, and test on staging before enabling payment. The code cannot itself establish that the correct tax treatment or combined package measurements were entered. Confirm both with your accountant and Shiprocket. Shipping is still an estimate; orders and delivery are managed manually.

### Install the requested catalog

1. In WordPress, go to **Products → New Catalog Setup** and choose **Install / repair requested catalog** after backing up. It creates the specified polymer and pipe categories and listings, with no invented online prices, measurements or HSN codes. Existing products, their URLs and existing orders are not deleted or overwritten. Products that existed before this catalog was installed are excluded from the new catalog listing, even if they share a category. New listings are published for browsing but are enquiry-only until configured.
2. Edit each new product under **Products → All Products**. Add verified descriptions and images, select its category and configure the Product Master below. Update the product and re-open it to verify saved values.
3. The catalog sidebar and menu link to the requested categories. The new archive is organized by category and supports server-side search and pagination. The existing old product URLs may still be accessible directly; they are not automatically unpublished. Review them separately if they must no longer be public.

### Product Master

The **Product Master — Pricing, GST & Shipping** box is available on Products and on Pages saved with the Product Detail template. Set **Product type** to Polymer or Pipe, enter the product GST percentage (default **18%**) and the verified HSN code (4–8 digits, optional until known). Product prices are **before GST**. Confirm GST and HSN with your tax adviser.

- **Polymer:** Configure 5, 10, 15, 20 and 25 kg rows individually: set price, packed weight and dimensions, then tick Show. 30 kg and larger orders are enquiry-only. A free sample can still be enabled separately; only shipping and its GST are charged for the sample. Previously saved 10/20 kg values are retained; legacy 30 kg data remains in the database but is not offered for checkout. New rows default to unavailable, not fabricated prices.
- **Pipe:** Set a packet description and price per packet. Enter one line per supported quantity in the packet measurement box as `quantity, total packed weight kg, total length cm, total width cm, total height cm`. For example: `1, 8, 40, 30, 20` and `2, 17, 60, 40, 25`. These are illustrative, not default measurements. Each quantity needs actual **combined** measurements; unconfigured quantities require an enquiry. The server rejects quantities with a configured combined weight over 30 kg before asking Shiprocket. Price is per packet multiplied by the selected quantity. An optional free sample is configured in the sample row.
- If validation fails, the master values are not saved and an admin notice appears. The product post itself may already have been saved by WordPress. Correct the fields and update again.

### How the total is calculated

The server requests Shiprocket prepaid serviceability, selects the **lowest valid available courier rate**, and computes all amounts in integer paise, rounding each tax component to the nearest paise:

```text
product price + product GST (product-specific rate)
+ quoted shipping + shipping GST (18%) = total payable
```

For example, ₹100 product at 18% GST plus ₹45 shipping plus 18% shipping GST is ₹171.10. The code adds 18% to the `rate` returned by Shiprocket as requested; **verify whether your actual Shiprocket rate already includes tax**, so the customer is not charged twice. Do not enable live checkout before your accountant confirms the correct treatment. No separate CGST/SGST or IGST invoice calculation, tax invoice generation, refund reconciliation or inventory system is provided.

The product page shows a breakdown alongside delivery details. The amount and tax breakdown are saved in the server-side quote snapshot and order; the Razorpay order amount comes from that snapshot, not a browser-entered price. Shiprocket only supplies a rate; it does not create an order or pickup. The actual delivery charge may differ from the estimate.

### Settings and testing

- Under **Products → Payment & Shipping Setup**, save the Razorpay test Key ID/Secret, a separate webhook secret, Shiprocket API-user email/password and the pickup PIN. Configure the displayed public HTTPS Razorpay webhook URL for `order.paid`. Saved credentials are stored in the WordPress database without application-level encryption; protect admin access and backups.
- **Test Shiprocket** checks authentication only. **Test courier rate** uses a published product ID, option key (`10kg` or `packet_1`, for example) and delivery PIN to check the lowest available prepaid rate without creating an order or pickup. The configured real Shiprocket account is contacted even in Razorpay Test mode.
- **If heavier packs show “shipping unavailable”:** In the affected product, check that the exact pack is enabled and has its own positive actual packed weight and all three dimensions. Under **Products → Payment & Shipping Setup → Test Shiprocket courier rate**, choose the product and pack (or enter their ID/key manually) and the same customer PIN. Run the test for both the working 5 kg and failing pack. The admin-only result distinguishes configuration, connection, provider HTTP status and no-rate failures; it does not expose credentials or provider response bodies. Ask your host or Shiprocket support about the reported stage/status if needed. Authentication success alone does not prove a given pack is serviceable. The code contains no general 5 kg maximum for polymer products, but the real reason for a specific failure cannot be determined without that test.
- On staging, test polymer pack, sample and pipe quantities, including an unconfigured quantity and a pipe over 30 kg. Confirm product price, both GST amounts, total, lowest courier and customer details in **Products → Payment Orders** and Razorpay test mode. Check failed/closed payments and webhook delivery; do not dispatch test or uncertain orders. Live mode requires both the existing live-safety opt-in and the new **GST live-pricing safety** confirmation. The new checkbox is unchecked on upgrade; verify the tax treatment and staging totals before enabling it.
- Theme/plugin mixed versions disable new checkout to avoid an outdated amount. Install [`plugin-download/raghushree-payments.zip`](plugin-download/raghushree-payments.zip) as a replacement, not an additional copy. Legacy order snapshots and old manual-delivery data remain available; check Shiprocket for older shipments before booking a new one.
- A known reconciliation risk remains: an unknown Razorpay order webhook is acknowledged with HTTP 200, including if it arrives before its order ID has been stored. A delivered webhook alone is not proof of a paid WordPress order. Manually reconcile uncertain payments with Razorpay; do not ask a customer to pay twice or ship until resolved.

For implementation details, see the [code architecture README](docs/code-architecture/README.md). The repository includes mocked smoke tests; they are not a real WordPress or provider end-to-end test. Consult the [Razorpay documentation](https://razorpay.com/docs/home/) and [Shiprocket API documentation](https://apidocs.shiprocket.in/) as provider sources of truth.

# Raghushree theme — where every piece of content lives

This guide tells you **exactly** where each heading, paragraph, card, image, button and price lives, and what to change to edit it. Use it when you want to change a single card, a whole section, or an image — without guessing.

> **Read this first:** there are only three places content comes from.
>
> | Layer | What it controls | Edited in |
> |---|---|---|
> | **1. WordPress content** | Products, categories, product prices/GST, pages, menus | WP Admin (no code) |
> | **2. Theme settings** | Company name, phone, WhatsApp, email, address, social links, every placeholder image | **Appearance → Customize** |
> | **3. Template text** | Section headings, card titles/descriptions, FAQ answers, button labels | The theme files listed below (one string per line) |

---

## Contents

1. [Before you edit a template file](#1-before-you-edit-a-template-file)
2. [Global settings (company details, logo, menus, footer)](#2-global-settings)
3. [Replacing any image](#3-replacing-any-image)
4. [Buttons and where they point](#4-buttons-and-where-they-point)
5. [Homepage — all 15 sections, card by card](#5-homepage)
6. [About page — every section](#6-about-page)
7. [Contact page — every section and form field](#7-contact-page)
8. [Products — cards, catalogue, product page, prices](#8-products)
9. [The two new shop sections (sliders and category cards)](#9-shop-sections)
10. [Menus, footer and legal links](#10-menus-and-footer)
11. [SEO, meta titles and structured data](#11-seo-and-structured-data)
12. [Troubleshooting](#12-troubleshooting)
13. [Quick recipes](#13-quick-recipes)

---

## 1. Before you edit a template file

1. **Back up** the site (files + database) and, if possible, work on staging first.
2. **Only change the text inside the quotes.** Every string looks like this:

   ```php
   'title' => __( 'Dependable Polymer Raw Materials for Industrial Manufacturing', 'raghushree' ),
   ```

   Change `Dependable Polymer…` — keep `'title' =>`, the `__(`, the `, 'raghushree' )` and the trailing comma exactly as they are.
3. **Never delete a comma or a bracket.** A PHP syntax error makes the whole page blank.
4. **Keep the theme updatable with a child theme.** Copy the file you want to change to the same path inside `wp-content/themes/your-child-theme/` and edit it there; WordPress loads the child copy first.
5. **CSS/JS are cached by file timestamp** — saving the file is enough, then hard-refresh the browser (`Ctrl/Cmd + Shift + R`).
6. **Find a string fast.** In a code editor, search the whole theme folder for the exact sentence you see on the page; the search result is the line to edit.

---

## 2. Global settings

**Appearance → Customize → Raghushree Company & Contact.**

| Field | What it changes |
|---|---|
| Brand Name | Header logo text / footer brand (when no logo image is set) |
| Registered Legal Name | Legal name used in About copy, schema, footer |
| Business Positioning Line | One-line role statement used in header/footer meta |
| Full Registered Address / Street / City / Postal / State / Country | Footer address, contact page, schema |
| Sales & Marketing Email | Footer, contact panel, CTA panel, enquiry notifications |
| Primary Phone Number | Header, footer, CTA panel, contact page |
| WhatsApp Number | Every WhatsApp button (header, hero badge, footer, CTA) |
| LinkedIn / Facebook / X / YouTube URL | Footer social icons — leave empty to hide |

These values feed `raghushree_get_company_info()` (`inc/helpers.php`), so changing them once updates every place automatically.

**Logo:** Appearance → Customize → Site Identity → Logo. Without a logo, the brand name text is used.

---

## 3. Replacing any image

Every photograph in the theme is a **placeholder slot** registered in `inc/image-placeholders.php`. **Each page owns its own slots** — the home page, the About page, the Contact page, the catalogue and the product cards never share a photo. Uploading an image in one slot changes exactly one place on the site.

The Customizer therefore has **six separate image sections**:

| Customizer section | Slots | Notes |
|---|---|---|
| **Home Page Images** | `home_*` (25) | Banners, about split, quality split, the 5 story slides, the 4 application cards, 12 category cards |
| **About Page Images** | `about_*` (32) | Hero, 4 overview stack images, quality, supply, the 5 story slides, the 4 application cards, 4 showcase cards, 12 category cards |
| **Contact Page Images** | `contact_hero` | Contact page hero |
| **Catalogue & Standard Pages Images** | `products_hero`, `page_hero` | Products listing hero; banner for standard pages, posts, search and 404 |
| **Header Images** | `mega_menu_feature` | Mega menu feature photo |
| **Product Card Fallback Images** | `polymer_*` (7) | Only used when a product has **no Featured Image** |

### 3.1 Home page slots

| Slot | Where it appears |
|---|---|
| `home_hero_pipes` | Banner 1 — agriculture & irrigation pipes |
| `home_hero` | Banner 2 — polymer raw materials (*also* the Home page Featured Image, if set) |
| `home_about` | “About Raghushree” split image |
| `home_quality` | “Quality & Supply” split image |
| `home_story_pp`, `home_story_pe`, `home_story_pvc`, `home_story_recycled`, `home_story_supply` | Visual Product Story slides 01–05 |
| `home_app_injection`, `home_app_blow`, `home_app_extrusion`, `home_app_film` | The four application flip cards |
| `home_cat_pp`, `home_cat_ldpe`, `home_cat_lldpe`, `home_cat_hdpe`, `home_cat_pvc`, `home_cat_pvc_resin`, `home_cat_pet`, `home_cat_recycled`, `home_cat_hoses`, `home_cat_suction_pipes`, `home_cat_tubing`, `home_cat_lay_flat` | The category cards (one slot per card) |

### 3.2 About page slots

| Slot | Where it appears |
|---|---|
| `about_hero` | About page hero |
| `about_overview`, `about_stack_2`, `about_stack_3`, `about_stack_4` | The four stacked photos in the company overview triptych |
| `about_quality` | Quality commitment split image |
| `about_supply` | Supply & distribution split image |
| `about_story_pp`, `about_story_pe`, `about_story_pvc`, `about_story_recycled`, `about_story_supply` | Visual Product Story slides 01–05 (About copy) |
| `about_app_injection`, `about_app_blow`, `about_app_extrusion`, `about_app_film` | The four application flip cards |
| `about_show_1` … `about_show_4` | The four “Inside Our Supply Capabilities” showcase cards |
| `about_cat_pp` … `about_cat_lay_flat` (12) | The category cards (one slot per card) |

### 3.3 Other slots

| Slot | Where it appears |
|---|---|
| `contact_hero` | Contact page hero |
| `products_hero` | Products listing hero |
| `page_hero` | Banner of standard pages, single posts, search results and 404 |
| `mega_menu_feature` | Mega menu feature image in the header |
| `polymer_pp`, `polymer_hdpe`, `polymer_ldpe`, `polymer_lldpe`, `polymer_pvc`, `polymer_pet`, `polymer_recycled` | Product card / tile / slider **fallback** when the product has no Featured Image |

> `app_injection`, `app_blow`, `app_extrusion`, `app_film` still exist inside the file for products saved before this change, but they are **hidden** from the Customizer — every visible surface now uses the page-scoped slots above.

**Three ways to replace a photo (in priority order):**

1. **Per product/page (recommended):** set a **Featured Image** on the product or page in WP Admin. It always wins over the placeholder.
2. **Per slot:** Appearance → Customize → the page's image section (Home / About / Contact / Catalogue / Header / Product fallbacks) → choose the labelled image.
3. **Code default:** edit the `base_url` or `local_file` of the slot in `inc/image-placeholders.php`.

**Recommended sizes**

| Kind of slot | Upload size | Ratio |
|---|---|---|
| Banners (`home_hero*`), story slides | `1600 × 1200 px` and `1920 × 1080 px` | 4:3 / 16:9 |
| Split images (`home_about`, `home_quality`, `about_quality`, `about_supply`, stack images) | `1200 × 900 px` | 4:3 |
| Category cards (`*_cat_*`) | `1000 × 750 px` | 4:3 |
| Application flip cards (`*_app_*`) | `960 × 540 px` | 16:9 |
| Showcase cards (`about_show_*`) | `1200 × 750 px` | 16:10 |
| Product featured images, gallery images and fallbacks (`polymer_*`) | `1200 × 1200 px` | 1:1 |
| Hero (`about_hero`, `contact_hero`, `products_hero`, `page_hero`) | `1400 × 900 px` | ~3:2 |

Use square product images for the best fit on product cards, the product page and the product showcase. Non-square product images are displayed in full with empty space around them, rather than cropped. Existing uploads do not need thumbnail regeneration.

Use WebP or JPEG under ~200 KB. The temporary Unsplash defaults repeat a few photos because the theme ships a small stock set — each slot is independent, so upload your own photo in every card you want to differentiate.

**Category cards** take their slot from `raghushree_category_image_slot()` in `inc/helpers.php`, which maps each catalog category slug to that page's own card slot, e.g.:

```php
'pvc-granules' => 'pvc',    // → home_cat_pvc on Home, about_cat_pvc on About
```

A category created later is matched by keyword (`ldpe`, `pp`, `pet`, `pvc`, `hose`, `layflat`, …) and falls back to the page's tubing card, so a new category never shows a broken image.

## 4. Buttons and where they point

| Button (where it appears) | Destination | Change the label | Change the destination |
|---|---|---|---|
| Explore Products (hero, banner 2) | Products catalogue | `hero.php` → `buttons` array | same |
| Request a Quote (hero, banner 2) | Contact form pre-set to “Request a Quotation” | `hero.php` | `raghushree_get_enquiry_url()` args |
| Explore Pipe Range (hero, banner 1) | Products filtered by `polymer_cat=pvc-tubing` | `hero.php` | change the slug in `raghushree_get_products_url( array( 'polymer_cat' => … ) )` |
| Get a Pipe Quote (hero, banner 1) | Contact form with the pipe material pre-filled | `hero.php` | same |
| Buy now / View product (packet grid tiles) | Product page (purchase area `#product-purchase`) | `template-parts/components/product-tile.php` | automatic per product |
| **+** on the centre slide (coverflow) | That product's page | — (icon) | automatic per product |
| Explore All (packet grid) | Products catalogue | `template-parts/sections/home/product-categories.php` | same file |
| Category cards | `/polymer-category/{slug}/` | — (category name) | Products → Polymer Categories |
| Flip-card buttons | Contact form (technical consultation) | `template-parts/components/flip-cards-section.php` | same file |
| Final CTA buttons | Contact form + WhatsApp | `template-parts/components/cta-banner.php` | same file |
| Hero arrows (banner), slider arrows/dots | Move the slider — not links | — | — |

Buttons are rendered by `template-parts/components/buttons.php` with a small array: `label`, `url`, `variant` (`primary`, `dark`, `secondary`, `on-dark`), optional `icon`.

---

## 5. Homepage

Order of sections in `front-page.php`:

| # | Section | File |
|---|---|---|
| 1 | Banner carousel (2 banners, 12 s) | `template-parts/sections/home/hero.php` |
| 2 | Packet grid (3 × 3) + Explore All | `template-parts/sections/home/product-categories.php` |
| 3 | Centre-focused coverflow slider | `template-parts/components/product-coverflow.php` |
| 4 | Category cards | `template-parts/components/category-grid.php` |
| 5 | Trust bar (4 items) | `template-parts/sections/home/trust-bar.php` |
| 6 | About Raghushree (split) | `template-parts/sections/home/about-preview.php` |
| 7 | 5-slide pinned visual story | `template-parts/components/visual-product-story.php` |
| 8 | Grades & material forms (4 cards) | `template-parts/sections/home/grades-forms.php` |
| 9 | Application flip cards (4 cards) | `template-parts/components/flip-cards-section.php` |
| 10 | Why choose (6 cards) | `template-parts/sections/home/why-choose.php` |
| 11 | Industries served (4 cards + 6 roles) | `template-parts/sections/home/industries-served.php` |
| 12 | Quality & supply (4 points + image) | `template-parts/sections/home/quality-supply.php` |
| 13 | Supply process (5 steps) | `template-parts/sections/home/supply-process.php` |
| 14 | FAQ (5 questions) | `template-parts/sections/home/faq-section.php` |
| 15 | Final CTA | `template-parts/sections/home/final-cta.php` |

### 5.1 Banner carousel — `hero.php`

Two banners share one layout. Everything is in the `$banners` array: element `[0]` is the agriculture/irrigation banner (shown first), element `[1]` is the polymer banner.

```php
$banners = array(
    array(
        'eyebrow'  => __( 'Agriculture & Irrigation Pipes', 'raghushree' ),   // small red label
        'title'    => __( 'Pipes That Keep Farms Watered…', 'raghushree' ),   // <h1> of the page
        'desc'     => __( 'PVC garden pipes, suction pipes…', 'raghushree' ),
        'slot'     => 'home_hero_pipes',                                      // image slot
        'post_id'  => 0,                                                      // 0 = use the slot
        'alt'      => __( 'Representative irrigation pipes…', 'raghushree' ),  // image alt text
        'buttons'  => array( … ),                                             // label, url, variant, icon
        'tags'     => array( 'PVC Garden Pipes', … ),                         // last tag is highlighted
    ),
    array( … same keys, slot 'home_hero', post_id $front_id … ),
);
```

* **Change the photo:** Customizer → *Homepage Banner 1 — Agriculture & Irrigation Pipes Image* / *Homepage Hero Image*.
* **Change the rotation speed:** `assets/js/home-hero.js` → `var DELAY = 12000;` (milliseconds).
* **Remove one banner:** delete its array block (keep at least one).
* **Headings:** banner 1 uses `<h1>`, banner 2 uses `<h2>` (automatic — do not hand-edit the tags).
* **Phone/WhatsApp/email badge** on the photo comes from the Customizer; empty fields disappear.

### 5.2 Packet grid — `product-categories.php`

* Section heading: the `get_template_part( '…/section-heading', … )` array (`eyebrow`, `title`, `description`).
* Number of tiles: `$catalog_items = raghushree_get_catalog_items( 9 );` — change `9`.
* Tile content is **automatic** from each product (see §8): photo, name, price, badge.
* “Explore All” label and link: the `shop-grid__footer` block at the bottom.
* **Order of tiles:** Products → edit product → **Page Attributes → Order** (`menu_order`), then title.

### 5.3 Coverflow slider — `product-coverflow.php`

* Heading: the `section-heading` array (`eyebrow`, `title`).
* Slides: the same products, `raghushree_get_catalog_items( 9 )`.
* Slide caption: category name + price, both automatic (`kicker_bits`).
* It loops infinitely in both directions; the second neighbour on each side is visible from 768 px up.
* Editing behaviour lives in `assets/js/shop.js` (`circularOffset()` and the arrow/dot/swipe handlers) and `assets/css/shop.css`.

### 5.4 Category cards — `category-grid.php`

* Heading defaults live in the file; each page can pass its own (`front-page.php` passes only the id).
* Cards are built from `polymer_category` terms that contain products, in the order of `raghushree_catalog_definitions()` (`inc/catalog-v2.php`).
* Card name and count come from WordPress; the link is the category archive.
* **Rename a category:** Products → Polymer Categories → edit Name/Slug.
* **Hide a category:** it disappears automatically when it has no products.

### 5.5 Trust bar — `trust-bar.php`

`$strengths` array, four entries, each `title` + `desc` (+ icon). Add a fifth entry and it flows into the grid automatically.

### 5.6 About preview — `about-preview.php`

* Eyebrow, `<h2>`, two paragraphs (the first inserts the *Registered Legal Name* from the Customizer).
* Three stat cards: `label` + `value` (`Business Role`, `Headquarters`, `Market Focus`).
* Buttons array and the split image slot `home_about`.

### 5.7 Pinned visual story — `visual-product-story.php`

`$slides` array, five entries. Each slide:

| Key | Meaning |
|---|---|
| `num` | “01”–“05” badge |
| `kicker` | small label above the title |
| `title` | slide heading |
| `desc` | paragraph |
| `points` | up to 3 bullet lines |
| `slot` | background image slot — `home_story_*` on Home, `about_story_*` on About (the component picks the page automatically) |
| `cta_label` / `cta_url` | primary button |
| `rfq_url` | secondary “Enquire Now” button |

The polymer links are resolved from the seeded slugs; if you rename a product slug, update the `$poly_links` lookup at the top of the file.

### 5.8 Grades & forms — `grades-forms.php`

`$forms` array, four entries: `meta` (small grey label), `title`, `desc` (+ icon). Section heading is in the same file.

### 5.9 Application flip cards — `flip-cards-section.php`

`$cards` array, four entries: `num`, `slot` (image — resolved as `home_app_*` or `about_app_*` from the section's `page` arg), `title`, `front` (text on the front), `polymers` (tags on the front), `details` (3 bullet lines on the back), `rfq_url`. The same component is reused on the About page with its own images.

### 5.10 Why choose — `why-choose.php`

`$reasons` array, six entries (`title`, `desc`, icon) + the section heading.

### 5.11 Industries served — `industries-served.php`

* Four industry cards in `$sectors` (`title`, `desc`).
* The six buyer roles list in `$buyer_roles` (`Procurement & Purchasing Teams`, …).
* Section heading.

### 5.12 Quality & supply — `quality-supply.php`

Eyebrow, `<h2>`, intro paragraph, four `$points` (`title`, `desc`), image slot `home_quality`, and the button under the points.

### 5.13 Supply process — `supply-process.php`

`$steps` array, five entries: `title` + `desc` (the number is generated). Section heading at the bottom of the file.

### 5.14 FAQ — `faq-section.php`

`$home_faqs` array of `question` + `answer`. The accordion behaviour is automatic; add or remove entries freely.

### 5.15 Final CTA — `final-cta.php`

Eyebrow, title, description passed to `template-parts/components/cta-banner.php`. The buttons (“Request a Quotation” + call/WhatsApp) and the phone/email shown inside the panel come from the component and the Customizer.

---

## 6. About page

Rendered by `page-templates/template-about.php`, which loads four grouped files in this order:

| Sections | File |
|---|---|
| 1–3: hero, company overview, business approach | `template-parts/sections/about/hero-overview.php` |
| 4–5: packet grid, coverflow, category cards, image showcase, flip cards | `template-parts/sections/about/portfolio-applications.php` |
| 6–8: quality commitment, supply & distribution, technical support | `template-parts/sections/about/quality-supply-tech.php` |
| 9–12: markets & global reach, why choose, enquiry process, final CTA | `template-parts/sections/about/markets-why-process.php` |

| What you see | Edit here |
|---|---|
| About hero eyebrow, `<h1>`, intro paragraph | `hero-overview.php` → the `page-banner` args (`eyebrow`, `title`, `description`) |
| Hero buttons (“Explore Polymer Portfolio”, “Contact Sales Desk”) | `hero-overview.php` → `primary_cta` / `secondary_cta` |
| Hero photo + caption | slot `about_hero`, caption keys `caption_title` / `caption_sub` |
| Company overview paragraphs and facts | `hero-overview.php` (`%s` in the first paragraph inserts the legal name) |
| Business approach cards (3) | `hero-overview.php` → `$principles` array |
| Material portfolio heading + tiles | `portfolio-applications.php` (grid heading and `raghushree_get_catalog_items( 9 )`) |
| Category cards heading | `portfolio-applications.php` → `get_template_part( '…/category-grid', …, array( 'id' => 'about-categories', 'surface' => 'surface--pearl' ) )` |
| Image showcase cards (4) | `portfolio-applications.php` → `$about_image_cards` (text keys) and Customizer → About Page Images → `about_show_1…4` (photos) |
| Quality / supply copy | `quality-supply-tech.php` (inline eyebrow, `<h2>` and paragraphs of the Quality Commitment and Supply & Distribution sections) |
| Technical support cards (3) | `quality-supply-tech.php` → `$tech_support` array |
| Markets & global reach copy | `markets-why-process.php` (inline eyebrow, `<h2>`, paragraphs) |
| Why choose cards | `markets-why-process.php` → `$why_items` array |
| Enquiry process steps | `markets-why-process.php` → `$enquiry_steps` array |
| About final CTA | bottom of `markets-why-process.php` |

---

## 7. Contact page

`page-templates/template-contact.php` loads two files.

**`template-parts/sections/contact/hero-info-form.php`**

| What you see | Edit here |
|---|---|
| Contact hero eyebrow, `<h1>`, intro, buttons | the `page-banner` args near the top of `hero-info-form.php` |
| Contact photo | slot `contact_hero` (Customizer) |
| Direct contact cards (phone / WhatsApp / email / address) | generated from the Customizer values |
| Form field labels and placeholders | the `<label>` and `placeholder` lines inside the `<form>` |
| Enquiry type options | `$enquiry_types` array (`Request a Quotation`, `…`, `Sample Request`, …) |
| Material dropdown options | `$material_options` (built from the catalogue) |
| Application dropdown options | `$app_options` array |
| Success / error messages | `inc/template-functions.php` → `raghushree_handle_enquiry_submission()` |

**`template-parts/sections/contact/options-channels-faq.php`**

| What you see | Edit here |
|---|---|
| “Material Enquiry Options” cards (6, one per category) | generated from the categories; headings from the `section-heading` args |
| Supported business enquiries block | `$supported_types` array |
| Contact FAQs | `$contact_faqs` array |

The notification email recipient is the *Sales & Marketing Email* in the Customizer.

---

## 8. Products

### 8.1 Everything that feeds a product card, tile or slider slide

| Card element | Comes from |
|---|---|
| Photo | Product **Featured Image** → else the product's placeholder slot |
| Category badge | First `polymer_category` term |
| Title | Product title |
| Short text (archive cards) | **Short card and product introduction** field → else the Excerpt → else the first 26 words |
| Price + pack note | Lowest **enabled** packet price in the Product Master (`inc/product-master.php`) |
| Tags | `material_grade` / `material_form` terms |
| Buttons | Fixed: *Product Details* + *Enquire* on the archive card; *Buy now* / *View product* on the home & About tiles |

Until a packet price is enabled, every card and slide shows **“Price on request”** — the theme never invents a price.

### 8.2 Add or edit a product

Products → All Products → **Add New / Edit**:

1. **Title** and main editor content (shown as *Product overview*).
2. **Raghushree Polymer Product Specifications & Supply Data** box: short introduction, highlights, packaging, MOQ, datasheet URL, gallery images.
3. **Product page sections**: features, applications, technical properties, FAQs (with optional introductions).
4. **Featured Image**, and the taxonomy boxes (Polymer Category, Material Grade, Material Form, Application Industry).
5. **Page Attributes → Order** if you want it earlier in the grids (lower number = earlier).
6. **Product Master — Pricing, GST & Shipping**: product type (polymer/pipe), 5–25 kg packet prices, sample, GST %, HSN, pipe packet quantity + measurements. **A pack only becomes buyable when its price is filled in.**

### 8.3 Catalogue page

`template-parts/sections/product/listing-content.php`:

| What you see | Edit here |
|---|---|
| Catalogue hero eyebrow, `<h1>`, intro | the `catalog-hero` block |
| Sidebar category list and grouping (“Polymer materials” / “Pipes & hoses”) | generated from `raghushree_catalog_definitions()` in `inc/catalog-v2.php` |
| Search box and button labels | the `catalog-search` form |
| Empty state text | the `products-empty` block |
| Cards per page | `posts_per_page => 24` in the `WP_Query` |

> Only categories listed in `raghushree_catalog_definitions()` appear in the catalogue and its sidebar. Adding a brand-new category means adding a line to that function (and re-running **Products → New Catalog Setup** if you want its products created).

### 8.4 Product detail page

`template-parts/sections/product/detail-content.php` — the sticky section navigation and the blocks (Overview, Grades, Applications, Processing, Technical Details, Packaging, FAQs, Enquiry). All text inside those blocks is the content you typed in the product editor; the labels of the navigation come from this file.

---

## 9. Shop sections

### 9.1 How many products appear

| Place | Setting |
|---|---|
| Homepage packet grid | `raghushree_get_catalog_items( 9 )` in `product-categories.php` |
| Homepage coverflow | `raghushree_get_catalog_items( 9 )` in `product-coverflow.php` |
| About packet grid | `raghushree_get_catalog_items( 9 )` in `portfolio-applications.php` |
| Category cards | `'limit' => 9` passed to `category-grid.php` (default 9) |

### 9.2 Slider behaviour (`assets/js/shop.js`, `assets/css/shop.css`)

* Infinite in **both** directions — the last product always sits to the left of the first.
* Visible neighbours: two per side (offset ±1, ±2) from 768 px; one per side below that.
* Change the blur/scale/opacity of each ring in `assets/css/shop.css` (`[data-offset="1"]`, `[data-offset="2"]`, …).
* The **+** in the centre opens the product page; clicking a blurred neighbour brings it to the centre.

### 9.3 Hiding a section on one page

Comment out its `get_template_part()` line in `front-page.php` (Home) or `portfolio-applications.php` (About) — e.g. `// get_template_part( 'template-parts/components/product-coverflow', … );`.

---

## 10. Menus and footer

* **Header menu:** Appearance → Menus → *Primary Header Navigation*. Without a menu the theme builds one automatically (Home, Products mega menu, About, Contact, Request a Quote).
* **Footer menus:** `footer_products` (polymer links), `footer_links` (quick links), `footer_legal` (Privacy, Terms).
* **Footer text, address, social icons:** `footer.php` reads the Customizer values; the column headings and the small print are in `footer.php`.
* **Mega menu feature image:** slot `mega_menu_feature`.

---

## 11. SEO and structured data

| What | File |
|---|---|
| Meta title/description fallbacks, Open Graph tags | `inc/seo-meta.php` |
| Organization / Product / Breadcrumb / FAQ schema | `inc/schema.php` |
| Per-page meta title & description | Edit the page/product with your SEO plugin, or the theme fallbacks above |

Meta descriptions that are not set explicitly fall back to the page excerpt/content — fill in the excerpt to control them.

---

## 12. Troubleshooting

| Symptom | Cause / fix |
|---|---|
| Page is blank after an edit | PHP syntax error — a missing `,`, `'`, `(` or `)`. Restore your backup and re-apply the change. |
| Text change does not appear | Wrong file (Home vs About), or a caching plugin/CDN. Purge caches and hard-refresh. |
| Tiles show “Price on request” | No packet price enabled in the Product Master for that product. |
| A product is missing from the grid/coverflow | It is not published, or it has no category inside `raghushree_catalog_definitions()`. |
| Category cards are missing | Every category is empty, or products were not assigned to `polymer_category`. |
| Image did not change | A Featured Image on that product/page overrides the slot, or the image was published in a different page's section (Home vs About). |
| Slider shows only one side | Screen narrower than 768 px (one neighbour per side by design), or JavaScript blocked — the first slide still shows. |
| Banner 2 never appears | JavaScript disabled, or a page builder script error; the carousel needs `assets/js/home-hero.js`. |
| A section disappeared | Its `get_template_part()` line is commented out, or its data array is empty (e.g. no FAQ entries). |

---

## 13. Quick recipes

| Task | Steps |
|---|---|
| Change the first banner photo | Customizer → *Homepage Banner 1 — Agriculture & Irrigation Pipes Image* → upload → Publish. |
| Change the agriculture banner wording | `template-parts/sections/home/hero.php` → `$banners[0]` → `eyebrow` / `title` / `desc` / `tags`. |
| Change the rotation speed | `assets/js/home-hero.js` → `DELAY` (milliseconds). |
| Change a homepage section heading | Open the section file listed in §5 and edit the `section-heading` array. |
| Add a sixth trust card | `trust-bar.php` → append one `array( 'title' => …, 'desc' => … )` entry to `$strengths`. |
| Add a FAQ | `faq-section.php` → append `array( 'question' => …, 'answer' => … )` to `$home_faqs`. |
| Reorder the packet grid | Products → edit → Page Attributes → Order (lower = earlier). |
| Show 12 products instead of 9 | Replace `( 9 )` with `( 12 )` in the three files listed in §9.1. |
| Change the “Buy now” wording | `template-parts/components/product-tile.php` → `$cta_label`. |
| Change a category card photo | Customizer → Home Page Images (or About Page Images) → “Category Card — …”. |
| Rename a category | Products → Polymer Categories → edit Name / Slug. |
| Make a product buyable | Product Master → enable a pack size and enter its price (₹ before GST). |
| Change a product's packet label (“25 kg” etc.) | Product Master → packet table (polymer) or *Packet size / description* (pipe). |
| Replace the contact hero photo | Customizer → *Contact Page Hero Image*. |
| Hide the pinned 5-slide story | Comment out that `get_template_part()` in `front-page.php` (§9.3). |
| Change the enquiry notification email | Customizer → *Sales & Marketing Email*. |
| Add a footer legal page | Appearance → Menus → `footer_legal`, then add the page. |

---

*Design previews used during development live in `docs/previews/` — they are documentation only and are never loaded by the live site.*

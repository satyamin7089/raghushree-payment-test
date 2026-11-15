# Phase 1 — Complete Existing WordPress Project Audit & Technical Inventory

**Source Repository:** `satyamin7089/raghushree-payment-test`  
**Audit Date:** 2026-11-15  
**Target Architecture:** React + Vite + TypeScript + Node.js + Express.js + Supabase PostgreSQL  

---

## 1. Existing Directory & Module Inventory

### Theme Root Templates
| File | WordPress Role | Target React Route |
|---|---|---|
| `front-page.php` | 12-section Homepage | `/` |
| `page-templates/template-about.php` | 12-section About Raghushree page | `/about` |
| `page-templates/template-contact.php` | 9-section Contact & 11-field RFQ page | `/contact` |
| `archive-products.php`, `page-templates/template-products.php` | Filterable Product Catalog (sidebar + search + category grouping + pagination) | `/products` |
| `single-products.php`, `page-templates/template-product-detail.php` | Product Detail with Hero Slider, Product Master Purchase, Shiprocket Quote, Razorpay Checkout & Owner Sections | `/products/:slug` |
| `archive.php` | Taxonomy Archives (`polymer_category`, `material_grade`, `material_form`, `manufacturing_application`) | `/polymer-category/:slug`, `/material-grade/:slug`, `/material-form/:slug`, `/application/:slug` |
| `search.php` | Site-wide search results | `/search` |
| `page.php`, `single.php` | Generic CMS / Legal pages (Privacy Policy, etc.) | `/privacy-policy`, `/:slug` |
| `404.php` | Not Found page | `*` (404 fallback) |
| `header.php`, `footer.php` | Global Utility Bar, Sticky Header, Products Mega Menu, Mobile Drawer, 4-Col Dark Footer | Shared `SiteLayout` |

### Theme Modules (`inc/`)
| File | Responsibilities & Business Rules |
|---|---|
| `inc/custom-post-types.php` | Registers `products` CPT, `raghushree_enquiry` private CPT, and 4 taxonomies: `polymer_category`, `material_grade`, `material_form`, `manufacturing_application`. |
| `inc/custom-fields.php` | `raghushree_parse_pipe_rows()`, `raghushree_get_product_data()` assembling product meta (`_raghushree_polymer_code`, `_raghushree_image_slot`, `_raghushree_short_desc`, `_raghushree_highlights`, `_raghushree_grades_notes`, `_raghushree_grades_rows`, `_raghushree_applications_rows`, `_raghushree_industries_rows`, `_raghushree_processing_rows`, `_raghushree_tech_specs`, `_raghushree_datasheet_url`, `_raghushree_packaging`, `_raghushree_pack_size`, `_raghushree_moq`, `_raghushree_availability`, `_raghushree_gallery_ids`, `_raghushree_related_ids`, `_raghushree_product_faqs`). |
| `inc/product-sections.php` | Structured editable sections (`features`, `applications`, `specifications`, `faqs`) with optional per-section plain-text introductions (`_raghushree_section_intros`), atomic validation (max 50 rows/section, max 2000 chars/field, max 4800 chars intro), and legacy pipe-row fallback. |
| `inc/product-master.php` | **Product Master**: `type` (`polymer` \| `pipe`), `gst_bps` (0–10000 basis points, default 1800 = 18%), `hsn` (4–8 digits), `polymer` fixed packs (`5kg`, `10kg`, `15kg`, `20kg`, `25kg` + `sample` at ₹0; legacy `30kg` retained in DB but hidden from checkout), `pipe` (`packet_label`, `packet_price_paise`, quantity-specific `packet_rows` with `weight_kg`, `length_cm`, `width_cm`, `height_cm`). Integer-paise decimal parser (`max 10,000,000 ₹`). |
| `inc/catalog-v2.php` | Defines the 9 requested V2 categories (`pvc-granules`, `pvc-resin`, `pp-granules`, `ldpe-granules`, `hdpe-granules`, `industrial-hoses`, `pvc-suction-pipes`, `pvc-tubing`, `lay-flat-pipes`) and their 22 products, plus non-destructive installer that snapshots legacy IDs (`raghushree_legacy_catalog_ids`). |
| `inc/shipping-quotes.php` | **Shiprocket Rate Engine**: `raghushree_tax_paise($paise, $bps)` = `intdiv($paise * $bps + 5000, 10000)`. Authenticates `POST https://apiv2.shiprocket.in/v1/external/auth/login`, calls `GET https://apiv2.shiprocket.in/v1/external/courier/serviceability/` with `cod=0`, `pickup_postcode`, `delivery_postcode`, `weight`, `length`, `breadth`, `height`, `declared_value`. Enforces 6-digit PIN (`^[1-9][0-9]{5}$`), pipe 30 kg max weight limit before API call, rejects blocked couriers (`!empty($courier['blocked'])`) and rates `< 0.005`, selects **lowest valid rate** (not recommended ID), adds 18% shipping GST (`1800` bps), returns fixed safe error references (`SR-AUTH-NET`, `SR-AUTH-HTTP`, `SR-AUTH-DATA`, `SR-RATE-NET`, `SR-RATE-HTTP`, `SR-RATE-DATA`, `SR-NO-RATE`). Rate limit: 20 req / 10 min per IP. |
| `inc/payment-settings.php` | Stores Razorpay (`mode`, `checkout_enabled`, `allow_live`, `allow_tax_live`, `razorpay_key_id`, `razorpay_key_secret`, `razorpay_webhook_secret`) and Shiprocket (`shiprocket_email`, `shiprocket_password`, `pickup_pincode` default `208001`). Includes admin-only Razorpay auth check, Shiprocket auth check, and courier rate diagnostic tool. |
| `inc/payment-compat.php` | Version gate (`raghushree_tax_pricing_theme_ready`, `raghushree_rates_only_theme_ready`). In Node.js/Express, unified single codebase removes theme/plugin version skew while retaining all safety gates (`checkout_enabled`, `allow_live`, `allow_tax_live`, key prefix `rzp_test_`/`rzp_live_`). |
| `inc/template-functions.php` | Processes 11-field RFQ form (`name`, `company`, `email`, `phone`, `country`, `enquiry_type`, `material`, `grade`, `quantity`, `application`, `message`), honeypot (`company_website_url`), per-IP rate limit (6 per 15 min), persistent enquiry storage, and email notification. |
| `inc/helpers.php` | Company info (`Raghushree` / `Raghushree Plast Products Pvt. Ltd.`, `78/205, Latouche Road, General Ganj, Kanpur-208001, U.P., India`, `marketing@raghushree.com`, `+91-830-3715-071`), WhatsApp URL builder, URL helpers, inline SVG icon library. |
| `inc/image-placeholders.php` | Centralized 20-slot placeholder registry (Unsplash URLs + local `assets/images/bg-cta-3.jpg`, `logo-default-334x88.png`, `logo-wht.png`). |
| `inc/seo-meta.php` & `inc/schema.php` | SEO titles, meta descriptions, canonical URLs, robots directives, Open Graph, Twitter Cards, and JSON-LD `@graph` (`Organization`, `WebSite` with `SearchAction`, `WebPage`/`AboutPage`/`ContactPage`/`CollectionPage`, `Product`, and `FAQPage` synced strictly to visibly rendered FAQs). |
| `inc/seed-data.php` | 7 foundational V1 polymer definitions (`PP`, `HDPE`, `LDPE`, `LLDPE`, `PVC`, `PET`, `Recycled & Reprocessed Polymers`), core pages, and core taxonomy terms. |

### Custom Plugins (`integrations/`)
| Plugin | Responsibilities & Business Rules |
|---|---|
| `integrations/raghushree-payments/raghushree-payments.php` & `checkout.js` | 15-minute server quote snapshot (`sha256` token), single-use atomic quote claim, customer delivery validation (Indian 10-digit mobile `^[6-9][0-9]{9}$`), re-verification of product/pack/GST/mode/pickup PIN, Razorpay Orders API (`POST /v1/orders`), receipt token (`sha256`), client signature + server-to-server payment/order fetch verification, raw-body `X-Razorpay-Signature` HMAC-SHA256 `order.paid` webhook, idempotent `paid` state preserving manual delivery fields, manual delivery admin (`awaiting` \| `fulfilled`, optional courier & tracking). |
| `integrations/raghushree-product-access/raghushree-product-access.php` & `admin-workspace.php` | Restricted `Product Manager` role (`raghushree_product_manager`) scoped strictly to Products, 4 product taxonomies, image uploads, and Payment Orders (blocked from payment settings, catalog setup, general settings, enquiries, and non-product pages). Custom workspace visual layer. |

### Visual Assets (`assets/`)
- **CSS:** `global.css`, `typography.css`, `components.css`, `header.css`, `footer.css`, `home.css`, `about.css`, `contact.css`, `products.css`, `product-single.css`, `interactive-sections.css`, `product-gallery-admin.css`
- **Fonts:** `IBMPlexSans-{Medium,SemiBold,Bold}.woff2`, `Inter-{Regular,Medium,SemiBold,Bold}.woff2`
- **Images:** `bg-cta-3.jpg`, `logo-default-334x88.png`, `logo-wht.png`
- **Vendor:** `assets/vendor/gsap/gsap.min.js`, `assets/vendor/gsap/ScrollTrigger.min.js`

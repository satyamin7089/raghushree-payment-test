# Phase 2 — Migration Plan, Architecture & Risk Report

## 1. Independent Application Architecture

```text
apps/web (React 19 + Vite + TypeScript + React Router + Original CSS)
   │  Public Pages: /, /about, /contact, /products, /products/:slug, taxonomy routes, /search
   │  Admin Dashboard: /admin (Overview, Products & Product Master, Categories, Orders, Enquiries, Users, Settings, Audit Logs)
   │  Browser never holds Supabase Service Role Key, Razorpay Secret, or Shiprocket Credentials
   ▼  HTTP / REST (/api/*) + Vite Dev Proxy / Express Static & SSR Meta Injection
apps/api (Node.js + Express.js + TypeScript)
   │  Owns: Auth/RBAC middleware, Product Master rules, Integer-paise GST, Shiprocket API + 15m quote snapshots,
   │        Razorpay Orders + Signature/API verification + Webhook HMAC verification, RFQ validation/rate-limit/email,
   │        SEO SSR metadata & /sitemap.xml + /robots.txt, Media upload validation -> Supabase Storage
   ▼  PostgreSQL Driver (pg / Supabase Service Client)
Supabase (PostgreSQL + Auth + Storage + RLS)
```

## 2. Database Schema Proposal (`supabase/migrations/`)

Normalized relational PostgreSQL schema with UUID primary keys, foreign keys, check constraints, unique constraints, indexes, and updated_at triggers:
1. `roles` & `user_profiles` (linked to `auth.users`, supporting `administrator`, `manager`, `staff`, granular `permissions` JSONB + `is_active`).
2. `product_categories` (slug, title, category_type `polymer|pipe`, description, sort_order, is_catalog_v2).
3. `taxonomy_terms` (`material_grade`, `material_form`, `manufacturing_application`).
4. `media_assets` (storage_bucket, storage_path, public_url, alt_text, mime_type, file_size_bytes, width, height, uploaded_by).
5. `products` (slug, title, polymer_code, primary_category_id, image_slot, short_desc, content_html, highlights JSONB, grades_notes, grades_rows JSONB, industries_rows JSONB, processing_rows JSONB, datasheet_url, packaging, pack_size, moq, availability, status `draft|published|archived`, is_legacy_catalog, catalog_key, menu_order, SEO/OG/Twitter fields, created_by, updated_by).
6. `product_taxonomy_links` (product_id, term_id) & `product_gallery_items` (product_id, media_id, is_featured, sort_order).
7. `product_sections` & `product_section_intros` (normalized `features`, `applications`, `specifications`, `faqs` with row order & validation).
8. `product_master_configs` (product_id PK, product_type `polymer|pipe`, gst_bps 0..10000, hsn `^[0-9]{4,8}$`, packet_label, packet_price_paise).
9. `product_variations` (id, product_id, option_key, label, variation_kind `polymer_pack|sample|pipe_packet`, quantity, price_paise, enabled, weight_kg, length_cm, width_cm, height_cm).
10. `shipping_quotes` (id, token_hash UNIQUE, product_id, option_key, pickup_pincode, delivery_pincode, mode, courier_id, courier_name, product_paise, product_gst_paise, product_gst_bps, rate_paise, shipping_gst_paise, shipping_gst_bps, total_paise, hsn, pack_snapshot JSONB, quote_snapshot JSONB, claimed_at, expires_at).
11. `customers` (id, first_name, last_name, email, phone, address, address_2, city, state, pincode).
12. `orders` (id, order_number SERIAL/unique, customer_id, quote_id, product_id, option_key, mode `test|live`, payment_status `pending|paid|order_creation_failed|review_required`, fulfilment_status `awaiting|fulfilled`, manual_courier, manual_tracking, razorpay_order_id UNIQUE, razorpay_payment_id, receipt_hash, product_paise, product_gst_paise, product_gst_bps, shipping_paise, shipping_gst_paise, shipping_gst_bps, total_paise, hsn, order_snapshot JSONB, legacy_fulfilment, legacy_shiprocket_order, legacy_shiprocket_shipment).
13. `order_items` (id, order_id, product_id, product_title, option_key, option_label, quantity, unit_price_paise, gst_bps, gst_paise, hsn, packed_weight_kg, length_cm, width_cm, height_cm).
14. `payment_transactions` & `payment_events` (idempotent `event_key` unique constraint, event_type, source `checkout_verify|webhook`, signature_verified, payload JSONB, status).
15. `enquiries` (11-field RFQ + `status`: `new|in_review|quoted|closed|spam`, `internal_notes`, `assigned_to`, `ip_hash`).
16. `business_settings` (singleton/keyed config: company info, payment/shipping secrets stored server-side only, never exposed to public RLS).
17. `audit_logs` (actor_id, actor_email, action, resource_type, resource_id, metadata JSONB, ip_address, created_at).

## 3. API Architecture (`apps/api/src/routes/`)

- `GET /api/health` — Health & DB connectivity check
- `GET /api/public/site-config` — Public company info & active catalog categories
- `GET /api/public/products` — Catalog listing (supports `polymer_cat`, `product_q`, `material_grade`, `material_form`, `page`, `limit`, `include_legacy`)
- `GET /api/public/products/:slug` — Product detail + enabled variations + sections + gallery + SEO metadata
- `POST /api/public/enquiries` — 11-field RFQ submission with honeypot, Zod validation, IP rate limiting, DB insert & email notification
- `POST /api/public/shipping/quote` — Shiprocket serviceability quote + integer-paise GST + 15-min quote snapshot token (rate-limited 20/10m)
- `POST /api/public/checkout/start` — Atomic single-use quote claim, customer validation, `orders` + `order_items` creation, Razorpay Order creation
- `POST /api/public/checkout/verify` — Receipt token + HMAC-SHA256 signature verification + Razorpay API server-to-server verification
- `POST /api/webhooks/razorpay` — Raw-body `X-Razorpay-Signature` verification, idempotent `order.paid` reconciliation
- `POST /api/auth/login`, `POST /api/auth/logout`, `POST /api/auth/forgot-password`, `POST /api/auth/reset-password`, `GET /api/auth/me`
- `GET /api/admin/dashboard` — Stats, payment counts, recent activity
- `CRUD /api/admin/products`, `PUT /api/admin/products/:id/master`, `PUT /api/admin/products/:id/sections`, `POST /api/admin/media`
- `CRUD /api/admin/categories`, `CRUD /api/admin/taxonomy-terms`
- `GET/PATCH /api/admin/orders` & `PATCH /api/admin/orders/:id/delivery`
- `GET/PATCH /api/admin/enquiries`
- `GET/POST/PATCH /api/admin/users` (Administrator only)
- `GET/PUT /api/admin/settings`, `POST /api/admin/diagnostics/razorpay`, `POST /api/admin/diagnostics/shiprocket`, `POST /api/admin/diagnostics/courier-rate`, `POST /api/admin/catalog/install-v2`
- `GET /api/admin/audit-logs`

## 4. Risk Report & Mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| **GST / Shipping Tax Accuracy** | Incorrect invoice totals or live overcharging | Preserve exact `intdiv` basis-point rounding (`Math.floor((p * bps + 5000)/10000)`), enforce `allow_live` + `allow_tax_live` gates before live Razorpay activation. |
| **Shiprocket Live Quotes in Razorpay Test Mode** | Shiprocket authenticates against production API even in test mode | Isolate Shiprocket calls to rate-quote-only endpoint; never create Shiprocket orders/pickups; validate pipe >30 kg limit before API call. |
| **Quote Tampering / Double Checkout** | Client modifying price or replaying a quote token | Store full snapshot in PostgreSQL `shipping_quotes` indexed by `sha256(token)`, enforce atomic `UPDATE ... WHERE claimed_at IS NULL`, verify pack/GST/mode haven't changed. |
| **Duplicate or Out-of-Order Webhooks** | Overwriting manual delivery status or double-recording payment | Verify raw-body HMAC signature, enforce unique `payment_events` key, `markOrderPaid` uses DB transaction and never alters `fulfilment_status`/`manual_courier`/`manual_tracking`. |
| **BOLA / Privilege Escalation in Admin** | Staff (`product_manager`) accessing settings, enquiries, or user roles | Express `requireRole` / `requirePermission` middleware on every admin endpoint + Supabase RLS policies. |
| **SPA SEO Regression** | Search engines missing meta tags or JSON-LD | Express injects route-specific `<title>`, `<meta>`, canonical, OG, Twitter, and JSON-LD `@graph` into initial HTML + serves dynamic `/sitemap.xml` and `/robots.txt`. |
| **Legacy URL Breakage** | Loss of search rankings on WordPress URLs | Support both `/products/:slug` and trailing-slash equivalents, `/polymer-category/:slug`, `/about`, `/contact`, `/products` identically. |

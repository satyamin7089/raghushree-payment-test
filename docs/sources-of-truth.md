# Sources of truth

This file is the project **memory** for provider behaviour and for where each piece of logic actually lives. Read it before changing payment, shipping or deployment code. It is intentionally short and dated so stale claims are visible.

> **Owner designation:** the four links in [§2](#2-provider-documentation--designated-authorities) were marked by the site owner as the authoritative external references for this project (recorded 2026-11-15).

---

## 1. Authority order

When two sources disagree, resolve in this order and say which one you used:

1. **Provider documentation** (§2). Authoritative for endpoints, request parameters, response shapes, status and error codes, signature schemes, limits and dashboard steps. It changes without us; the link always wins over a memory of how it used to work, over this file, and over model training data.
2. **This repository's code** for *what this site actually does today* — not for what the provider does (§4).
3. **`tests/`** only prove the code's own mocked contract. A passing test is **not** evidence that a provider behaves that way.

If the code contradicts the provider documentation, treat it as a **bug candidate**: report it, quote the doc section, and only then change code. Never "fix" an integration from memory or from a third-party blog.

---

## 2. Provider documentation — designated authorities

| Provider | Authority URL | Use it for | Do **not** use it for |
| --- | --- | --- | --- |
| **Shiprocket** | <https://apidocs.shiprocket.in/> | Auth/login, courier serviceability (`courier/serviceability/`), orders, shipments, labels, NDR, tracking, webhooks, error/response codes, API-user setup | WordPress hooks, Razorpay behaviour |
| **Razorpay** | <https://razorpay.com/docs/home> (hub: <https://razorpay.com/docs/>) | Orders API, Payments, Checkout.js, signature verification, webhooks, test/live keys, error codes, settlements, refunds | Shipping rates, WordPress internals |
| **WordPress.com Developer Resources** | <https://developer.wordpress.com/docs/> | Platform and deployment: WordPress Studio, staging sites, GitHub deployments, WP-CLI, SFTP, platform features, performance, troubleshooting | Theme/plugin PHP APIs — those are core WordPress reference, not WordPress.com hosting |

Razorpay publishes an LLM index at <https://razorpay.com/docs/llms.txt>; fetch it first to find the exact deep page rather than guessing a URL.

**Suggested, pending owner confirmation (do not treat as authoritative yet):** core theme and plugin API questions (hooks, `register_post_type`, `WP_REST_Request`, options) would map to `developer.wordpress.org` rather than the WordPress.com hosting docs. Confirm before relying on it.

---

## 3. Verified provider facts

Everything below was read from the authority links on **2026-11-15** and matches what this repository already implements. Re-check the doc before changing any of it.

### Shiprocket

- **Base URL:** `https://apiv2.shiprocket.in/v1/external/`
- **Auth:** `POST /auth/login` with JSON `{ "email": ..., "password": ... }` → response contains `token`. Tokens are JWT and **valid for 10 days**; send `Authorization: Bearer <token>`. API user is created in Shiprocket under **Settings → API → Add New API User** (needs a unique email and explicit module access, e.g. the courier module).
- **Errors:** `200/202` OK, `400` bad request, `401` auth/token problem, `404` bad URI, `405` wrong method, `422` unprocessable, `429` rate limited, `500/502/503/504` server errors.
- **Webhooks:** configured at **Settings → API → Webhooks**; JSON `POST`; optional security token delivered in the **`x-api-key`** header; URL must not contain the keywords `shiprocket`, `kartrocket`, `sr` or `kr`; endpoint should answer only HTTP 200.
- **Serviceability (used by this repo for quotes):** `GET /courier/serviceability/` with `pickup_postcode`, `delivery_postcode`, `cod` (`0` prepaid / `1` COD) and `weight` (kg), plus optional dimensions. Rates come back per courier in `data.available_courier_companies[]` with `courier_company_id`, `courier_name`, `rate`, `etd` and a `blocked` flag.
  *Provenance:* endpoint/parameter names and the `available_courier_companies` shape are corroborated by the official Postman-backed doc plus maintained client libraries; the pre-rendered doc page is a JS app, so confirm the exact parameter list on the live page before adding or removing query arguments.
- **Note from the doc that matters commercially:** any request with valid credentials affects real-time data on the account. Shiprocket is used here for **rates only** — no order, shipment or pickup is created.
- **API-support contacts listed in the doc:** `integration@shiprocket.in` (integration), `support@shiprocket.in` (everything else).

### Razorpay

- **API base:** `https://api.razorpay.com`; server calls use HTTP Basic auth with `key_id:key_secret`. Keys are prefixed `rzp_test_…` and `rzp_live_…` and must match the selected mode.
- **Orders:** `POST /v1/orders` returns an `order_…` id. All amounts are integers in the **smallest currency unit** (paise for INR; `5000` = ₹50.00). Fetch with `GET /v1/orders/{id}`, payments with `GET /v1/payments/{id}`.
- **Checkout.js:** `https://checkout.razorpay.com/v1/checkout.js`; the success handler receives `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`.
- **Payment signature (checkout return):** `HMAC-SHA256(key_secret, order_id + "|" + payment_id)`, hex — verify server-side, and it is **not** the webhook signature.
- **Webhook signature:** header `X-Razorpay-Signature` = `HMAC-SHA256(webhook_secret, raw request body)`, hex. Verify against the **raw, unparsed body** — never a re-serialised JSON object. The webhook secret is separate from the API key secret and separate for Test and Live modes.
- **Webhook operational rules:** must be a public URL on **port 80 or 443** only (no localhost, no tunnelling/testing domains); deliveries can repeat and can arrive out of order → be **idempotent**, dedupe on the `x-razorpay-event-id` header; the `event` name is in the **body**, not a header; acknowledge fast and process asynchronously.
- **`callback_url` is not a webhook.** It is a checkout parameter for WebView/redirect flows only; do not use it as the server-side source of payment status. Rely on webhooks, with an immediate API fetch (payment/order) when a user-facing confirmation cannot wait.
- **`order.paid`** is the event this project subscribes to; server-side verification here requires order status `paid` and payment status `captured`, matching currency and amount.

### WordPress.com

- Scope is the **hosted platform**, not the theme/plugin API: fast managed hosting, WordPress Studio (local), staging sites, GitHub deployments, WP-CLI, SFTP, platform features, performance, troubleshooting, support tiers.
- Relevant to deployment of this project only insofar as the site is hosted on WordPress.com; nothing in `inc/`, `integrations/` or `template-parts/` depends on it.

---

## 4. Repository source of truth map

Use this instead of grepping from scratch. Structure and data-flow narrative: [`docs/code-architecture/README.md`](code-architecture/README.md); owner/setup narrative: [`README.md` §26](../README.md#26-product-master-gst-checkout-and-manual-delivery).

| Concern | Source of truth |
| --- | --- |
| Retry/limit, GST rounding and the quote total | `inc/shipping-quotes.php` — `raghushree_tax_paise()`, `raghushree_fetch_shipping_quote()` |
| Selection of the courier rate charged to the customer | `inc/shipping-quotes.php` — `raghushree_lowest_shipping_rate()` (lowest valid, non-blocked, positive rate; **not** the recommended courier id) |
| Shiprocket HTTP calls (auth + serviceability) | `inc/shipping-quotes.php` — `raghushree_shiprocket_authenticate()`, `raghushree_fetch_shipping_quote()` |
| Customer-visible error references (`SR-*`) | `inc/shipping-quotes.php` — `raghushree_shipping_error_reference()` |
| Stored credentials, mode, pickup PIN, admin diagnostics | `inc/payment-settings.php` (option `raghushree_payment_settings`) |
| Product price, GST bps, HSN, packed weight/dimensions | `inc/product-master.php` over meta `_raghushree_master_variations` / `_raghushree_commerce` |
| Quote snapshot → Razorpay order, amount authority | `integrations/raghushree-payments/raghushree-payments.php` — `rgpay_quote_snapshot()`, `rgpay_start_checkout()` (server snapshot only, never the browser) |
| Checkout signature + fetch verification, paid state | `integrations/raghushree-payments/raghushree-payments.php` — `rgpay_verify_checkout()`, `rgpay_entities_match()`, `rgpay_mark_paid()` |
| Razorpay webhook (raw-body signature) | `integrations/raghushree-payments/raghushree-payments.php` — `rgpay_webhook()`, route `raghushree/v1/razorpay-webhook` |
| Browser-side checkout UX | `integrations/raghushree-payments/checkout.js` |
| Version gate between theme and plugin | `RGPAY_RATES_ONLY_VERSION`, `RGPAY_TAX_PRICING_VERSION`, `raghushree_tax_pricing_theme_ready()` |
| Installable plugin ZIPs | `plugin-download/*.zip`, rebuilt via `scripts/package-payment-plugin.py`, `scripts/package-product-access-plugin.py` |
| Pinned provider contract in tests | `tests/shipping-quotes-test.php` (auth URL, serviceability URL, `cod=0`, bearer header), `tests/checkout-create-test.php`, `tests/payment-flow-test.php` |

---

## 5. Guardrails for future changes

- **Never invent a provider parameter, endpoint, header or status.** Open the authority link, quote the section, then code. If the doc is unreachable (the Shiprocket page is a JS app), say so and ask for a dashboard screenshot or the Postman collection instead of guessing.
- **Date any provider fact** you add here; the three docs are versioned and change.
- **Do not weaken the current controls** while "fixing" an integration: server-computed amounts, raw-body webhook HMAC, manual capture checks, the theme/plugin version gate, and live checkout kept off until staging verification.
- **No credentials in the repository.** Provider secrets live only in the WordPress option above; never log, echo or commit them.
- **Changing a provider call is a deliberate contract change:** update `tests/` in the same change and re-check the doc.
- Known open risks recorded in the README (unknown Razorpay order webhook acknowledged with HTTP 200; whether Shiprocket's `rate` already includes GST) stay open until verified with the real accounts — do not quietly mark them resolved.

## 6. Change log

| Date | Change |
| --- | --- |
| 2026-11-15 | Created. Owner designated Shiprocket API, Razorpay docs, WordPress.com developer docs as external authorities; recorded verified facts (§3) and the repository map (§4). |

# Code architecture: how product ordering works

This guide explains **how the code connects and what happens at each step**. It complements the [main README](../../README.md), which covers installation, dashboard setup and operation. The names in backticks are actual files, WordPress hooks, functions or stored keys, so a developer can search for them in the repository.

> **Scope and status:** This is a custom WordPress theme plus a separately activated custom plugin; WooCommerce is not involved. Shiprocket is used for **recommended prepaid rate quotes only**, and the merchant handles delivery manually. The implementation has mocked smoke tests, but has not been verified end-to-end here on the deployed WordPress site and merchant accounts. Review the known risks and perform staging tests before live use.

## 1. The big picture

```text
 ADMIN                                CUSTOMER / EXTERNAL SERVICES
 ─────                                ────────────────────────────
 Product editor                       Product page
   │                                    │
   ▼                                    ▼
 Product master ── post meta ──► Pack options + delivery PIN
   │                                    │
   │                                    ▼
   │                          Theme AJAX shipping-quote handler
   │                                    │
   │                                    ▼
 Settings ───── WordPress option ──► Shiprocket API ──► recommended rate
   │                                    │
   │                                    ▼
   │                          Plugin stores temporary quote snapshot
   │                                    │
   │                                    ▼
   │                          Plugin creates a WordPress pending order
   │                                    │
   └───────────────────────────────────► Razorpay Orders API
                                        │
                                        ▼
                                Razorpay Checkout in browser
                                        │
                       ┌────────────────┴─────────────────┐
                       ▼                                  ▼
                Checkout response               Razorpay webhook: order.paid
                + Razorpay API check             + webhook signature check
                       └────────────────┬─────────────────┘
                                        ▼
                              Verified paid WordPress order
                                        │
                                        ▼
                           Admin reviews and arranges delivery
```

The browser collects selections and displays the results; the WordPress server validates data, contacts APIs, calculates the payable total and records payment. A shipping quote **does not** create a Shiprocket order, shipment, AWB or pickup. The quoted cost can differ from the final delivery cost.

## 2. Code map

| File | Responsibility | Connects to |
| --- | --- | --- |
| [`functions.php`](../../functions.php) | Loads the theme's `inc/` modules. | Product master, shipping, settings, compatibility and other theme code. |
| [`inc/custom-post-types.php`](../../inc/custom-post-types.php) | Registers the existing `products` custom post type and catalog taxonomies. | WordPress product editor and product templates. |
| [`inc/product-master.php`](../../inc/product-master.php) | Defaults, product master meta box, validation, save and read functions. | Product post meta, product template and shipping quote. |
| [`single-products.php`](../../single-products.php) and [`page-templates/template-product-detail.php`](../../page-templates/template-product-detail.php) | Two wrappers for a product post or a Page using Product Detail template. | Both load the same `detail-content` template part. |
| [`template-parts/sections/product/detail-content.php`](../../template-parts/sections/product/detail-content.php) | Existing product layout, pack cards, quote form and conditional checkout form. | Product master, theme quote JS and plugin readiness check. |
| [`inc/enqueue.php`](../../inc/enqueue.php) | Enqueues product-page scripts and styles. | [`assets/js/shipping-quote.js`](../../assets/js/shipping-quote.js), product styling and other theme assets. |
| [`inc/shipping-quotes.php`](../../inc/shipping-quotes.php) | Validates the quote request and calls Shiprocket. | WordPress AJAX, Shiprocket API, plugin's quote filter. |
| [`inc/payment-settings.php`](../../inc/payment-settings.php) | Admin settings, authentication tests and courier-rate diagnostic. | Settings option, Razorpay and Shiprocket authentication tests. |
| [`inc/payment-compat.php`](../../inc/payment-compat.php) | Theme-side guard against an outdated automatic-shipment plugin. | Plugin hooks and version marker. |
| [`integrations/raghushree-payments/raghushree-payments.php`](../../integrations/raghushree-payments/raghushree-payments.php) | Checkout, Razorpay verification, webhook, order storage and manual-delivery admin. | Theme functions, WordPress AJAX/REST, Razorpay API. |
| [`integrations/raghushree-payments/checkout.js`](../../integrations/raghushree-payments/checkout.js) | Receives quote events and opens Razorpay Checkout. | Theme quote form, WordPress AJAX and Razorpay's browser script. |
| [`plugin-download/raghushree-payments.zip`](../../plugin-download/raghushree-payments.zip) | Installable copy of the payment plugin. | Installed and activated separately from the theme. |

**Important:** The plugin source folder in the theme repository is not automatically active as a WordPress plugin. Install/activate the ZIP, or install its source under `wp-content/plugins/raghushree-payments`. Both the matching theme and plugin are needed for checkout.

## 3. How WordPress loads the pieces

1. WordPress loads active plugins and the active theme. The plugin registers its callbacks and defines `RGPAY_RATES_ONLY_VERSION`. The theme's `functions.php` loads the product master, settings, compatibility and quote modules.
2. `inc/custom-post-types.php` registers `products`; `inc/product-master.php` registers its editor box and save hooks. The plugin registers a separate non-public `rgpay_order` post type for orders.
3. On a product detail page, `single-products.php` or the Product Detail page template loads the same `template-parts/sections/product/detail-content.php`.
4. `inc/enqueue.php` loads the quote script on supported product pages. At `wp_enqueue_scripts` priority 20, the plugin loads Razorpay's hosted checkout script and its own `checkout.js` only if `rgpay_checkout_ready()` passes.
5. The theme exposes `raghushree_rates_only_theme_ready()`. The plugin requires this and other theme functions. Conversely, the theme detects the old plugin without the rates-only marker and removes its checkout and old fulfilment hooks. This guard cannot interrupt a request already running during an upgrade.

WordPress **actions** run registered callbacks at a point in the request. A **filter** lets a callback modify a value. **Post meta** stores extra data for a WordPress post; an **option** stores site-wide settings; a **transient** is a temporary cached value that can expire earlier than its maximum lifetime. An **AJAX action** here is a request to WordPress `admin-ajax.php`; a **REST route** is used for the webhook.

## 4. Product master: from admin screen to product page

### Save path

1. In WordPress, the administrator edits a product. `raghushree_master_box_render()` displays fixed rows defined by `raghushree_master_defaults()`:

   | Internal key | Default price | Default state |
   | --- | ---: | --- |
   | `10kg` | ₹100 (`10000` paise) | enabled |
   | `20kg` | ₹200 (`20000` paise) | enabled |
   | `30kg` | ₹300 (`30000` paise) | enabled |
   | `sample` | ₹0 (`0` paise) | disabled |

2. On `save_post_products` or `save_post_page`, `raghushree_master_box_save()` checks the nonce, edit permission and that this is not an autosave or revision. It validates **all** rows before updating the product master; a bad price or measurement can reject the whole master update.
3. Decimal rupee prices are parsed into integer paise. Each row holds `label`, `price_paise`, `enabled`, `weight_kg`, `length_cm`, `width_cm` and `height_cm`. Measurements can be blank on save but must be positive for a quote.
4. The array is stored in the product's `_raghushree_master_variations` post meta. `raghushree_master_variations($post_id)` merges saved values with defaults; it fixes labels from the defaults and forces sample price to zero.

The meta box also appears on a Page using the Product Detail template after the template is saved. Pack sizes are fixed in code, not a general-purpose variation editor. Material-grade taxonomies are separate catalog metadata and are not checkout variation choices.

### Display path

`detail-content.php` calls `raghushree_master_variations($post_id)`, skips disabled rows and prints cards and a select box. It provides the quote form with the WordPress AJAX endpoint, product ID and nonce as HTML `data-*` attributes. The checkout form is rendered only when at least one option is enabled and `rgpay_checkout_ready()` is true; it starts hidden until a valid token is returned.

## 5. Settings and readiness

`inc/payment-settings.php` registers **Products → Payment & Shipping Setup**. `raghushree_payment_settings_save()` checks the administrator capability and nonce, then stores `raghushree_payment_settings` as a site-wide WordPress option. It includes mode, pickup PIN, enabled flags, Razorpay keys/webhook secret and Shiprocket API-user credentials. Blank secret fields retain existing values; a clear checkbox removes them.

The option is kept out of the autoloaded options cache, but **the credentials are not encrypted by this application**. Restrict database, administrator and backup access; use HTTPS and never commit credentials to the repository. The Test Razorpay and Test Shiprocket buttons authenticate only. A separate administrator-only Test courier rate form calls the same product quote function with a published product ID, enabled pack and destination PIN; it does not create a shipment or test payment or webhooks. It reports fixed safe messages and HTTP status when available, never provider response bodies or credentials.

`rgpay_checkout_ready()` checks for the matching theme functions, enabled checkout, test/live mode, appropriate Razorpay key prefix, API and webhook secrets, Shiprocket credentials and `is_ssl()`. Live mode additionally needs `allow_live`. This is a configuration gate, **not** proof of correct credentials, a working webhook or successful end-to-end checkout.

## 6. Shipping quote: browser → WordPress → Shiprocket

1. The customer selects an enabled pack and enters a delivery PIN. [`shipping-quote.js`](../../assets/js/shipping-quote.js) sends `action=raghushree_shipping_quote`, nonce, product ID, option and PIN to the form's `admin-ajax.php` endpoint.
2. `raghushree_ajax_shipping_quote()` checks the nonce and a transient-based per-client throttle (20 requests per 10-minute window), sanitizes inputs, and calls `raghushree_fetch_shipping_quote()`. This simple throttle is not a substitute for server/edge rate limiting.
3. The fetch function requires a published `products` post or Product Detail Page, an enabled option, valid six-digit destination and pickup PINs, all four positive package measurements, and saved Shiprocket credentials.
4. Server-side, WordPress authenticates with Shiprocket's `/v1/external/auth/login`, then requests `/v1/external/courier/serviceability/` using the bearer token, `cod=0`, pickup/delivery PIN, packed measurements and declared product value. Razorpay test mode does **not** switch Shiprocket to a sandbox; the configured Shiprocket account is contacted in either payment mode.
5. `raghushree_recommended_shipping_rate()` matches `recommended_courier_company_id` against `available_courier_companies`, requiring a valid positive rate. If it cannot find that recommended rate, the request fails instead of selecting a different courier. The rate is converted to paise.
6. The response contains product/option names, product price, courier ID/name and rate. Before returning it, the theme runs the `raghushree_quote_result` filter. If the plugin is active and ready, `rgpay_quote_snapshot()` adds a checkout token as described next. The browser displays the estimate and dispatches `raghushree:quote`; changing the selection or PIN dispatches `raghushree:quote-invalid` and clears the visible result.

## 7. The theme–plugin connection: quote snapshot

The shared filter is the hand-off between quote code in the theme and payment code in the plugin:

```php
// Theme: inc/shipping-quotes.php
$quote = apply_filters( 'raghushree_quote_result', $quote, $id, $option, $pin );

// Plugin: integrations/raghushree-payments/raghushree-payments.php
add_filter( 'raghushree_quote_result', 'rgpay_quote_snapshot', 10, 4 );
```

When checkout is ready, `rgpay_quote_snapshot()` creates a random 32-byte token (64 hexadecimal characters). It stores a snapshot under a transient key derived from the **SHA-256 hash** of the token, with a 15-minute maximum lifetime. The snapshot contains product ID, selected option, destination PIN, quote, pack settings, payment mode, pickup PIN and quote time. The raw token is returned to the browser as `checkout_token`; the server keeps the authoritative price and shipping amount.

`checkout.js` listens for `raghushree:quote` and reveals the delivery form only if the response includes a token. It listens for `raghushree:quote-invalid` and hides the form before checkout starts. If checkout is not ready, a shipping estimate may still be returned without a token.

## 8. Creating a pending order and opening checkout

1. After delivery details are entered, `checkout.js` sends `rgpay_start_checkout` to `admin-ajax.php`, with the token, customer fields and nonce. Both logged-in and guest AJAX hooks are registered.
2. `rgpay_start_checkout()` rechecks readiness, validates the token, reads the snapshot, and checks that mode, pickup PIN, published product and selected pack still match. It validates customer details, including email and a 10-digit Indian mobile number; the delivery PIN comes from the quote snapshot.
3. The server calculates `total_paise = product_paise + rate_paise`. It rejects totals below 100 paise or above 2,000,000,000 paise. A sample has product price zero; its shipping rate still contributes to the total.
4. The plugin uses a unique `rgpay_quote_<token hash>` option as an atomic claim, deletes the quote transient and schedules `rgpay_cleanup_quote_claim` for one hour later via WP-Cron. This limits parallel reuse; housekeeping depends on WP-Cron running and is not a shipping worker.
5. It inserts a non-public `rgpay_order` post and saves `_rgpay_record` (snapshot, customer, total, mode, receipt-token hash, creation time) and `_rgpay_status = pending`.
6. WordPress calls Razorpay's **Orders API** with the calculated INR paise total, a receipt and product/internal-order notes. It checks the response ID, amount and currency and stores `_rgpay_razorpay_order`. If order creation or reference storage fails, it records `order_creation_failed` where possible and returns an error.
7. The AJAX response includes the Razorpay Key ID, Razorpay order ID, amount, internal order ID, a random receipt token, description and customer prefill. It does **not** return API or webhook secrets. `checkout.js` opens the hosted Razorpay Checkout. If the modal is closed, its Resume button opens the **same** Razorpay order rather than creating another.

Example: 10 kg at ₹100 plus a ₹45 recommended quote produces `10000 + 4500 = 14500` paise (₹145). The browser displays this, but cannot set the amount used to create the order.

## 9. How payment is confirmed

There are two independent entry paths to the same paid-state update:

### A. Browser checkout response

After Razorpay Checkout calls its success handler, `checkout.js` posts `rgpay_verify_checkout`, the internal order ID, receipt token, Razorpay order ID, payment ID and signature. The server:

1. Checks the AJAX nonce, receipt-token hash, matching stored Razorpay order, mode and expected payment-ID format.
2. Recomputes the HMAC-SHA256 checkout signature from the **stored** Razorpay order ID, payment ID and API Key Secret.
3. Fetches the payment and order from Razorpay's API. `rgpay_entities_match()` requires the expected IDs, INR, exact amount and amount paid, payment status `captured` and order status `paid`.
4. Calls `rgpay_mark_paid()` only after those checks pass. If the API cannot be reached or statuses don't match, the browser receives a pending message rather than a false paid confirmation.

### B. Server-to-server webhook

The plugin registers `POST /wp-json/raghushree/v1/razorpay-webhook`; copy the actual URL from the WordPress settings screen because the installation URL can differ. For each request, `rgpay_webhook()` verifies the `X-Razorpay-Signature` HMAC-SHA256 against the **raw request body** and saved webhook secret. For `order.paid`, it finds a unique internal order by `_rgpay_razorpay_order`, checks mode and the order/payment entities against the saved total, then calls `rgpay_mark_paid()`. Other event types are acknowledged but not used for fulfilment. The API Key Secret and webhook secret have distinct roles and should not be confused.

`rgpay_mark_paid()` stores `_rgpay_payment_id` and `_rgpay_status = paid`; a different payment ID for an already recorded order is rejected. Repeat notifications for the same payment do not overwrite manual-delivery fields. Neither confirmation path schedules a Shiprocket shipment.

**Known limitation:** An unknown Razorpay order receives HTTP 200. If `order.paid` arrives before the plugin has stored its Razorpay order ID, the webhook could be acknowledged without recording payment and without a retry. Successful webhook delivery alone is therefore not proof that the WordPress order is paid. There is no built-in automatic reconciliation button. Review this risk before live use and reconcile uncertain payments with Razorpay; do not ask the customer to pay again or dispatch until the discrepancy is resolved.

## 10. Where the information is stored

| WordPress storage | Key / type | Contents and purpose |
| --- | --- | --- |
| Product post meta | `_raghushree_master_variations` | Saved pack prices in paise, enabled flags and package measurements. |
| Site option | `raghushree_payment_settings` | Mode, pickup PIN, enable flags and provider credentials; not application-encrypted. |
| Transient | `rgpay_q_<SHA-256 of token>` | Short-lived quote/pack snapshot; max 15 minutes. |
| Option | `rgpay_quote_<SHA-256 of token>` | One-use quote claim; cleanup scheduled through WP-Cron. |
| Custom post type | `rgpay_order` | Internal, non-public order post. |
| Order post meta | `_rgpay_record` | Snapshot, customer details, total, mode, hashed receipt token and creation time. |
| Order post meta | `_rgpay_status`, `_rgpay_razorpay_order`, `_rgpay_payment_id` | Payment state and provider identifiers. |
| Order post meta | `_rgpay_manual_status`, `_rgpay_manual_courier`, `_rgpay_manual_tracking` | Merchant-entered delivery status and notes. |
| Legacy order post meta | `_rgpay_fulfilment`, `_rgpay_shiprocket_order`, `_rgpay_shiprocket_shipment` | Previous integration's available data, retained for duplicate-shipment checks. |

An order's quote and pack are snapshots, so later product edits do not retroactively change the saved order. Customer addresses and contact information are stored in the database: set an appropriate access, backup and retention policy.

## 11. Manual delivery and admin order screen

`rgpay_admin_menu()` registers **Products → Payment Orders**. `rgpay_orders_screen()` queries orders in pages of 25 and displays customer, pack, package, quote, payment IDs/status and legacy Shiprocket information. The manual delivery form is shown only for paid orders.

Submitting the form invokes `admin_post_rgpay_update_delivery`. The handler requires `manage_options`, verifies the nonce, and `rgpay_set_manual_delivery()` validates the order and status before saving `awaiting` or `fulfilled` plus optional courier and tracking reference. These are WordPress records only: the code does not book a courier, send tracking notifications, or automatically reconcile refunds/cancellations. Legacy Shiprocket orders may already exist; check that account before arranging a new shipment.

## 12. Request and hook reference

| Entry point | Registered callback | Purpose |
| --- | --- | --- |
| `save_post_products`, `save_post_page` | `raghushree_master_box_save` | Validate and save the master data. |
| `admin_post_raghushree_payment_settings_save` | `raghushree_payment_settings_save` | Save admin settings. |
| `admin_post_raghushree_test_service_connection` | `raghushree_test_service_connection` | Test saved provider authentication. |
| `admin_post_raghushree_test_courier_rate` | `raghushree_test_courier_rate` | Administrator-only prepaid courier quote diagnostic; no checkout or shipment. |
| `wp_ajax[_nopriv]_raghushree_shipping_quote` | `raghushree_ajax_shipping_quote` | Get a recommended prepaid quote. |
| `raghushree_quote_result` filter | `rgpay_quote_snapshot` | Attach a checkout token to a successful quote when ready. |
| `wp_ajax[_nopriv]_rgpay_start_checkout` | `rgpay_start_checkout` | Claim quote and create internal/Razorpay orders. |
| `wp_ajax[_nopriv]_rgpay_verify_checkout` | `rgpay_verify_checkout` | Verify browser response and provider status. |
| REST `raghushree/v1/razorpay-webhook` | `rgpay_webhook` | Verify and process Razorpay `order.paid`. |
| `admin_post_rgpay_update_delivery` | `rgpay_admin_update_delivery` | Save manual delivery details on a paid order. |
| `rgpay_cleanup_quote_claim` | Anonymous callback in plugin | Remove an old quote claim. |

In the table, `wp_ajax[_nopriv]_...` is shorthand for **two separate hooks**: `wp_ajax_...` for logged-in users and `wp_ajax_nopriv_...` for guests.

## 13. Testing and changing the code

The following are standalone PHP smoke tests with mocked WordPress/provider behavior, not a real WordPress or provider integration environment:

```bash
php tests/product-master-test.php
php tests/shipping-quotes-test.php
php tests/courier-diagnostics-test.php
php tests/checkout-create-test.php
php tests/payment-flow-test.php
php tests/payment-compat-test.php
php tests/plugin-compat-test.php
```

- For a pack-field change, review the product master validation, template, quote inputs, snapshot comparison, saved order display and tests together.
- For a payment or webhook change, review both confirmation paths, existing orders and failure/retry behavior. Follow the [official Razorpay documentation](https://razorpay.com/docs/home/) for provider requirements.
- For a plugin change, rebuild [`plugin-download/raghushree-payments.zip`](../../plugin-download/raghushree-payments.zip) using `python3 scripts/package-payment-plugin.py`, and deploy the matching theme and plugin. Back up first and check older orders for prior Shiprocket activity.
- Run a full staging checkout with Razorpay test credentials, a configured test webhook and working Shiprocket API-user credentials. Verify a successful payment, sample, failures and pending/reconciliation cases. Authentication tests alone are insufficient.
- Keep live checkout disabled until the real environment is validated and the webhook/reconciliation, security and operational risks are addressed. Read the [setup and testing instructions](../../README.md#26-product-master-payments-and-manual-delivery) for the operational checklist.

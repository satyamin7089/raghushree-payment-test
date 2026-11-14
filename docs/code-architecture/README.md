# Product, shipping and payment code architecture

This document describes the current custom theme and plugin. See the [main README](../../README.md#26-product-master-gst-checkout-and-manual-delivery) for the owner setup, GST warning, catalog installation and test checklist. The implementation is not a substitute for real WordPress/provider end-to-end and tax validation.

## Connections

```text
Product editor ──► product master / GST / HSN / package measurements
                            │
Product page ─► option and PIN ─► WordPress AJAX ─► Shiprocket serviceability
                            │                          │
                            └────── lowest rate ◄──────┘
                                      │
                    server computes product GST + shipping GST
                                      │
                       15-minute quote snapshot and token
                                      │
                         WordPress pending order + Razorpay order
                                      │
           Razorpay browser response and/or signed order.paid webhook
                                      │
                           verified paid WordPress order
                                      │
                              manual delivery
```

Shiprocket is used for rates only. It does not create a shipment, order or pickup. Its production account is contacted even when Razorpay is in Test mode.

## File map

| Path | Responsibility |
| --- | --- |
| [`functions.php`](../../functions.php) | Loads the theme modules. |
| [`inc/catalog-v2.php`](../../inc/catalog-v2.php) | Requested category and product definitions and the administrator-only, non-destructive catalog installer. |
| [`inc/admin-meta-boxes.php`](../../inc/admin-meta-boxes.php), [`assets/js/product-gallery-admin.js`](../../assets/js/product-gallery-admin.js) | Optional highlights, existing structured fields and Media Library selection; gallery IDs remain in their existing meta format. |
| [`template-parts/sections/product/detail-content.php`](../../template-parts/sections/product/detail-content.php) | Owner-authored content, optional saved sections and selected-image slider for both product types. |
| [`inc/product-sections.php`](../../inc/product-sections.php), [`assets/js/product-sections-admin.js`](../../assets/js/product-sections-admin.js) | Structured feature, application, specification and FAQ editor, including optional section introductions. Legacy text rows are read until the new format is first saved; the original meta is not deleted. Invalid edits leave published section data unchanged. |
| [`template-parts/components/product-enquiry.php`](../../template-parts/components/product-enquiry.php) | Legacy inline product enquiry component; product pages instead link to the contact enquiry form. |
| [`inc/product-master.php`](../../inc/product-master.php) | Product type, polymer variations, packet quantities, per-product GST and HSN, meta box and validation. |
| [`inc/shipping-quotes.php`](../../inc/shipping-quotes.php) | Shiprocket authentication, prepaid serviceability, lowest valid courier, GST calculation and AJAX endpoint. |
| [`inc/payment-settings.php`](../../inc/payment-settings.php) | Credentials, setup and admin-only provider diagnostics. |
| [`inc/payment-compat.php`](../../inc/payment-compat.php) | Prevents an old plugin from accepting checkout after the GST-pricing theme upgrade. |
| [`template-parts/components/product-purchase.php`](../../template-parts/components/product-purchase.php) | Product selection, quantity, Buy/Enquire and PIN controls in the existing hero. |
| [`template-parts/components/product-checkout.php`](../../template-parts/components/product-checkout.php) | Delivery form and responsive order summary. |
| [`assets/js/shipping-quote.js`](../../assets/js/shipping-quote.js) | Selection, quote request and quote invalidation. |
| [`integrations/raghushree-payments/raghushree-payments.php`](../../integrations/raghushree-payments/raghushree-payments.php) | Payment plugin, snapshots, Razorpay orders, verification, webhook and manual-order admin. |
| [`integrations/raghushree-payments/checkout.js`](../../integrations/raghushree-payments/checkout.js) | Shows server quote breakdown, opens Razorpay and submits its response for verification. |
| [`template-parts/sections/product/listing-content.php`](../../template-parts/sections/product/listing-content.php), [`header.php`](../../header.php) | Category sidebar, server-side search and category mega/mobile menus. |

## Data and purchase lifecycle

1. Product meta `_raghushree_master_variations` stores polymer pack prices in paise, enabled state and measurements. `_raghushree_commerce` stores type, GST in basis points, HSN, packet price and quantity-specific combined package measurements. New polymer sizes and pipe quantities are unavailable until configured. Sample price remains zero. Existing legacy 30 kg metadata is retained but not presented for checkout.
2. For a pipe, `raghushree_master_variations()` derives `packet_N` rows from the per-packet price and the exact combined measurements configured for quantity N. A quantity with no configured row fails; a pipe over 30 kg fails before Shiprocket is called. Polymer checkout uses the five fixed options 5–25 kg.
3. `raghushree_ajax_shipping_quote()` checks nonce and inputs, and `raghushree_fetch_shipping_quote()` validates the product, option, PIN and packed measurements. It authenticates using `POST /v1/external/auth/login` and calls `GET /v1/external/courier/serviceability/` with `cod=0`, weight, dimensions and PINs. `raghushree_lowest_shipping_rate()` chooses the lowest valid available non-blocked rate, not the recommended ID. The recommended courier is included in the same valid-rate selection; its ID alone cannot provide a price. No valid rate means an error, not an invented fallback. Public responses contain only fixed stage references; administrator diagnostics give a provider HTTP status when available.
4. `raghushree_tax_paise()` calculates rounded integer-paise product GST and 18% shipping GST. The returned quote has `product_paise`, `product_gst_paise`, `product_gst_bps`, `rate_paise`, `shipping_gst_paise`, `shipping_gst_bps`, `total_paise`, `hsn` and the courier/product details. The configured shipping GST is **added** to the provider's rate; tax-inclusive treatment must be independently verified.
5. The theme runs `raghushree_quote_result`; plugin callback `rgpay_quote_snapshot()` stores the server quote and selected pack in a 15-minute transient indexed by a hash of a random token. `checkout.js` displays the server-supplied totals; the browser does not set the Razorpay amount.
6. `rgpay_start_checkout()` checks the snapshot against current product and pack details, validates customer fields, claims the token once, creates an internal `rgpay_order` and a Razorpay order. It adds the four saved components to determine the total and verifies the stored `total_paise`. Live checkout additionally requires the new `allow_tax_live` confirmation (unset on upgrade); old plugin/theme combinations are blocked by `RGPAY_TAX_PRICING_VERSION` and `raghushree_tax_pricing_theme_ready()`.
7. `rgpay_verify_checkout()` validates the checkout signature and fetches Razorpay payment/order status. The `raghushree/v1/razorpay-webhook` REST endpoint verifies the raw-body webhook signature and handles `order.paid`. Both require matching paid/captured entities, currency and amount before recording paid status. `rgpay_mark_paid()` preserves manual delivery fields on duplicate events.
8. The Payment Orders admin lists the saved breakdown and HSN for new orders, as well as legacy order data. No Shiprocket fulfilment worker is used. An unknown Razorpay order currently receives webhook HTTP 200; a webhook arriving before its order ID is saved may be acknowledged without recording payment. Reconciliation and staging review remain necessary.

## Catalog and upgrade notes

The installer is deliberately manual and idempotent: **Products → New Catalog Setup** creates missing requested terms and product records without deleting existing posts/orders. The installer records IDs of existing products so the new catalog excludes them without deleting them; new products can still be added later. Product data and pricing need administrator review. Old URLs remain reachable; hiding them requires a separately planned migration. The new public archive filters categories server-side and displays a left sidebar.

Deploy the updated theme and plugin **together**. Back up and disable checkout first. The installable plugin is [`plugin-download/raghushree-payments.zip`](../../plugin-download/raghushree-payments.zip), rebuilt from source with `python3 scripts/package-payment-plugin.py`. Credentials are stored in WordPress without application-level encryption. Protect administrator access and backups.

## Tests

```bash
php tests/product-master-test.php
php tests/product-content-test.php
php tests/product-sections-test.php
php tests/product-enquiry-test.php
php tests/catalog-v2-test.php
php tests/shipping-quotes-test.php
php tests/courier-diagnostics-test.php
php tests/checkout-create-test.php
php tests/payment-flow-test.php
php tests/payment-compat-test.php
php tests/plugin-compat-test.php
```

The PHP tests are mocked smoke tests, not real WordPress or provider tests. The separate gallery JavaScript and catalog layout browser tests require external test dependencies. Test staging with actual provider accounts, including per-quantity measurements, GST, minimum courier selection, webhook/retry cases, and compatibility upgrades. Confirm applicable GST and whether the provider rate already includes tax with the merchant's tax adviser before live checkout.

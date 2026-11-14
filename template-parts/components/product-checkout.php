<?php
/** Checkout layout. Summary values are taken from the server quote, not calculated in the browser. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! function_exists( 'rgpay_checkout_ready' ) || ! defined( 'RGPAY_TAX_PRICING_VERSION' ) || ! rgpay_checkout_ready() ) { return; }
?>
<section class="section surface--pearl checkout-section" id="checkout" hidden><div class="container">
<form class="product-checkout" hidden>
	<div class="checkout-heading"><span class="eyebrow"><?php esc_html_e( 'Secure checkout', 'raghushree' ); ?></span><h2><?php esc_html_e( 'Complete your order', 'raghushree' ); ?></h2><p><?php esc_html_e( 'Your delivery details and a clear breakdown, all in one place.', 'raghushree' ); ?></p></div>
	<div class="checkout-grid"><div class="checkout-details"><h3><?php esc_html_e( 'Delivery details', 'raghushree' ); ?></h3>
		<?php if ( ( rgpay_settings()['mode'] ?? 'test' ) === 'test' ) : ?><p><strong><?php esc_html_e( 'Test mode: no real money will be charged and no shipment will be booked.', 'raghushree' ); ?></strong></p><?php endif; ?>
		<div class="product-checkout__fields">
			<label><?php esc_html_e( 'First name', 'raghushree' ); ?><input name="first_name" required maxlength="50" autocomplete="given-name"></label><label><?php esc_html_e( 'Last name', 'raghushree' ); ?><input name="last_name" required maxlength="50" autocomplete="family-name"></label>
			<label><?php esc_html_e( 'Email', 'raghushree' ); ?><input name="email" type="email" required maxlength="100" autocomplete="email"></label><label><?php esc_html_e( 'Mobile number', 'raghushree' ); ?><input name="phone" type="tel" inputmode="numeric" pattern="[6-9][0-9]{9}" maxlength="10" required autocomplete="tel-national"></label>
			<label><?php esc_html_e( 'Address', 'raghushree' ); ?><input name="address" required maxlength="100" autocomplete="address-line1"></label><label><?php esc_html_e( 'Address line 2 (optional)', 'raghushree' ); ?><input name="address_2" maxlength="100" autocomplete="address-line2"></label>
			<label><?php esc_html_e( 'City', 'raghushree' ); ?><input name="city" required maxlength="30" autocomplete="address-level2"></label><label><?php esc_html_e( 'State', 'raghushree' ); ?><input name="state" required maxlength="50" autocomplete="address-level1"></label>
		</div><p class="checkout-note"><?php esc_html_e( 'Delivery PIN is taken from your shipping quote above.', 'raghushree' ); ?></p></div>
		<aside class="checkout-summary" aria-label="<?php esc_attr_e( 'Estimated order summary', 'raghushree' ); ?>"><span class="checkout-summary__eyebrow"><?php esc_html_e( 'YOUR ORDER', 'raghushree' ); ?></span><h3><?php esc_html_e( 'Estimated total', 'raghushree' ); ?></h3><p class="checkout-summary__product" data-summary="product">—</p><dl>
			<div><dt><?php esc_html_e( 'Product', 'raghushree' ); ?></dt><dd data-summary="product_paise">—</dd></div><div><dt data-summary="gst_label"><?php esc_html_e( 'Product GST', 'raghushree' ); ?></dt><dd data-summary="product_gst_paise">—</dd></div><div><dt><?php esc_html_e( 'Estimated shipping', 'raghushree' ); ?></dt><dd data-summary="rate_paise">—</dd></div><div><dt><?php esc_html_e( 'Shipping GST (18%)', 'raghushree' ); ?></dt><dd data-summary="shipping_gst_paise">—</dd></div><div class="checkout-summary__total"><dt><?php esc_html_e( 'Total payable', 'raghushree' ); ?></dt><dd data-summary="total_paise">—</dd></div></dl><p class="checkout-note" data-summary="courier"></p><p class="checkout-note"><?php esc_html_e( 'Shipping is an estimate; actual delivery cost may differ. Prepaid only.', 'raghushree' ); ?></p><button type="submit"><?php esc_html_e( 'Pay securely with Razorpay', 'raghushree' ); ?></button><p class="product-checkout__status" role="status" aria-live="polite"></p></aside>
	</div>
</form></div></section>

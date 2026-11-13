<?php
/** Fail safely when only one half of the rates-only integration has been upgraded. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function raghushree_rates_only_theme_ready() { return true; }

function raghushree_guard_legacy_payments() {
	// Plugins load before the theme. Disable the old worker and checkout if the old
	// automatic-Shiprocket plugin is still installed, including old queued events.
	if ( ! function_exists( 'rgpay_checkout_ready' ) || defined( 'RGPAY_RATES_ONLY_VERSION' ) ) { return; }
	remove_action( 'rgpay_fulfil_order', 'rgpay_fulfil_order' );
	remove_action( 'admin_post_rgpay_process', 'rgpay_admin_process' );
	remove_action( 'wp_ajax_rgpay_start_checkout', 'rgpay_start_checkout' );
	remove_action( 'wp_ajax_nopriv_rgpay_start_checkout', 'rgpay_start_checkout' );
	remove_filter( 'raghushree_quote_result', 'rgpay_quote_snapshot', 10 );
	add_action( 'admin_notices', 'raghushree_legacy_payment_notice' );
}
add_action( 'after_setup_theme', 'raghushree_guard_legacy_payments', 1 );

function raghushree_legacy_payment_notice() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	?><div class="notice notice-error"><p><?php esc_html_e( 'The installed Raghushree Payments plugin is outdated. Checkout and the old Shiprocket order worker have been disabled by the theme. Replace it with the rates-only plugin before accepting payments. Check Shiprocket for previously created or uncertain orders.', 'raghushree' ); ?></p></div><?php
}

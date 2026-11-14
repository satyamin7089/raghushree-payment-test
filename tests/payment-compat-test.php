<?php
// Simulate activating the new theme while the old automatic-order plugin is active.
define( 'ABSPATH', __DIR__ );
$GLOBALS['removed'] = array();
function add_action( ...$args ) {}
function rgpay_checkout_ready() { return true; }
define( 'RGPAY_RATES_ONLY_VERSION', '0.2.0' ); // Previous rates-only plugin lacks the GST-pricing marker.
function remove_action( $hook, $callback ) { $GLOBALS['removed'][] = $hook . ':' . $callback; }
function remove_filter( $hook, $callback, $priority = 10 ) { $GLOBALS['removed'][] = $hook . ':' . $callback; }
require __DIR__ . '/../inc/payment-compat.php';
raghushree_guard_legacy_payments();
foreach ( array( 'rgpay_fulfil_order:rgpay_fulfil_order', 'admin_post_rgpay_process:rgpay_admin_process', 'wp_ajax_rgpay_start_checkout:rgpay_start_checkout', 'wp_ajax_nopriv_rgpay_start_checkout:rgpay_start_checkout', 'raghushree_quote_result:rgpay_quote_snapshot' ) as $item ) {
	if ( ! in_array( $item, $GLOBALS['removed'], true ) ) { throw new Exception( 'Missing legacy guard: ' . $item ); }
}
echo "Upgrade compatibility smoke test passed.\n";

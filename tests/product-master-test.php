<?php
// Standalone smoke tests for the product master; no WordPress installation required.
define( 'ABSPATH', __DIR__ );
$GLOBALS['mock_meta'] = array();
$GLOBALS['allowed'] = true;
function add_action( ...$args ) {}
function __( $value, $domain = null ) { return $value; }
function get_post_meta( $id, $key, $single ) { return $GLOBALS['mock_meta'][$id] ?? ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['mock_meta'][$id] = $value; }
function wp_verify_nonce( $value, $action ) { return $value === 'valid'; }
function sanitize_text_field( $value ) { return $value; }
function wp_unslash( $value ) { return $value; }
function wp_is_post_revision( $id ) { return false; }
function current_user_can( ...$args ) { return $GLOBALS['allowed']; }
require __DIR__ . '/../inc/product-master.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new Exception( $message ); } }

$default = raghushree_master_variations( 42 );
check( $default['10kg']['price_paise'] === 10000 && $default['20kg']['price_paise'] === 20000 && $default['30kg']['price_paise'] === 30000, 'default prices' );
check( $default['sample']['price_paise'] === 0 && ! $default['sample']['enabled'], 'sample defaults' );
$GLOBALS['mock_meta'][42] = array( '10kg' => array( 'price_paise' => 12345, 'enabled' => true ) );
$old = raghushree_master_variations( 42 );
check( $old['10kg']['price_paise'] === 12345 && ! $old['sample']['enabled'], 'upgrade preserves old prices and defaults sample off' );

$_POST = array(
 'raghushree_master_nonce' => 'valid',
 'raghushree_master_price' => array( '10kg' => '100.25', '20kg' => '200', '30kg' => '300.00', 'sample' => '500' ),
 'raghushree_master_enabled' => array( '10kg' => '1', 'sample' => '1' ),
 'raghushree_master_shipping' => array( '10kg' => array( 'weight_kg' => '10.5', 'length_cm' => '30', 'width_cm' => '20', 'height_cm' => '10' ), 'sample' => array( 'weight_kg' => '0.250', 'length_cm' => '10', 'width_cm' => '5', 'height_cm' => '4' ) ),
);
raghushree_master_box_save( 42 );
$saved = $GLOBALS['mock_meta'][42];
check( $saved['10kg']['price_paise'] === 10025 && $saved['10kg']['weight_kg'] === '10.5', 'save price and package' );
check( $saved['sample']['enabled'] && $saved['sample']['price_paise'] === 0 && $saved['sample']['weight_kg'] === '0.250', 'free sample and package' );
check( ! $saved['20kg']['enabled'] && $saved['20kg']['weight_kg'] === '', 'disabled option and blank package' );
$_POST['raghushree_master_shipping']['sample']['weight_kg'] = '-1';
raghushree_master_box_save( 42 );
check( $GLOBALS['mock_meta'][42] === $saved, 'invalid weight does not overwrite saved master' );
$_POST['raghushree_master_shipping']['sample']['weight_kg'] = '0.25';
$_POST['raghushree_master_price']['10kg'] = '0.001';
raghushree_master_box_save( 42 );
check( $GLOBALS['mock_meta'][42] === $saved, 'invalid price does not overwrite saved master' );
$_POST['raghushree_master_price']['10kg'] = '500';
$_POST['raghushree_master_nonce'] = 'bad';
raghushree_master_box_save( 42 );
check( $GLOBALS['mock_meta'][42] === $saved, 'invalid nonce cannot save' );
echo "Product master smoke tests passed.\n";

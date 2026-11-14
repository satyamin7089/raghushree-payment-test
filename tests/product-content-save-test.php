<?php
define( 'ABSPATH', __DIR__ );
$GLOBALS['saved'] = array(); $GLOBALS['allowed'] = true;
function add_action( ...$args ) {} function __( $text, $domain = null ) { return $text; }
function wp_unslash( $value ) { return $value; } function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function sanitize_textarea_field( $value ) { return trim( strip_tags( $value ) ); } function esc_url_raw( $value ) { return $value; }
function wp_verify_nonce( $nonce, $action ) { return 'valid' === $nonce; }
function current_user_can( ...$args ) { return $GLOBALS['allowed']; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['saved'][$key] = $value; }
require __DIR__ . '/../inc/admin-meta-boxes.php';
function check( $value, $message ) { if ( ! $value ) { throw new Exception( $message ); } }
$_POST = array( 'raghushree_product_meta_nonce' => 'valid', 'raghushree_short_desc' => 'My intro', 'raghushree_highlights' => "One\nTwo", 'raghushree_gallery_ids' => '22, 11', 'raghushree_grades_rows' => 'Grade | Form | Use' );
raghushree_save_product_meta_box( 42 );
check( $GLOBALS['saved']['_raghushree_highlights'] === "One\nTwo" && $GLOBALS['saved']['_raghushree_gallery_ids'] === '22, 11', 'highlights and gallery order saved' );
check( $GLOBALS['saved']['_raghushree_grades_rows'] === 'Grade | Form | Use', 'structured fields saved' );
$before = $GLOBALS['saved']; $_POST['raghushree_product_meta_nonce'] = 'invalid'; $_POST['raghushree_highlights'] = 'unexpected'; raghushree_save_product_meta_box( 42 ); check( $GLOBALS['saved'] === $before, 'nonce is required' );
$_POST['raghushree_product_meta_nonce'] = 'valid'; $GLOBALS['allowed'] = false; raghushree_save_product_meta_box( 42 ); check( $GLOBALS['saved'] === $before, 'permission is required' );
echo "Product content save smoke tests passed.\n";

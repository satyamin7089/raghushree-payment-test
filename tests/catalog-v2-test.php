<?php
// Mocked catalog installer: importing never modifies pre-existing catalog or orders.
define( 'ABSPATH', __DIR__ );
$GLOBALS['terms'] = array(); $GLOBALS['posts'] = array( 1 => array( 'title' => 'Old product', 'status' => 'publish' ) ); $GLOBALS['meta'] = array(); $GLOBALS['assigned'] = array(); $GLOBALS['options'] = array(); $GLOBALS['allowed'] = true; $GLOBALS['nonce'] = true;
function add_action( ...$args ) {} function current_user_can( $x ) { return $GLOBALS['allowed']; } function wp_die( $m ) { throw new Exception( 'denied' ); }
function check_admin_referer( $x ) { if ( ! $GLOBALS['nonce'] ) { throw new Exception( 'nonce' ); } }
function get_term_by( $field, $slug, $tax ) { return $GLOBALS['terms'][$slug] ?? false; }
function wp_insert_term( $title, $tax, $args ) { $id = count( $GLOBALS['terms'] ) + 1; $GLOBALS['terms'][$args['slug']] = (object) array( 'term_id' => $id ); return array( 'term_id' => $id ); }
function sanitize_title( $s ) { return trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $s ) ), '-' ); }
function get_posts( $args ) { if ( ! isset( $args['meta_key'] ) ) { return array_keys( $GLOBALS['posts'] ); } foreach ( $GLOBALS['meta'] as $id => $meta ) { if ( ( $meta[$args['meta_key']] ?? null ) === $args['meta_value'] ) { return array( $id ); } } return array(); }
function wp_insert_post( $post, $error = false ) { $id = count( $GLOBALS['posts'] ) + 1; $GLOBALS['posts'][$id] = $post; return $id; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['meta'][$id][$key] = $value; }
function wp_set_object_terms( $id, $terms, $tax, $append = false ) { $GLOBALS['assigned'][$id] = $terms; return $terms; }
function is_wp_error( $value ) { return false; } function get_option( $key, $default = false ) { return $GLOBALS['options'][$key] ?? $default; } function add_option( $key, $value, ...$args ) { if ( isset( $GLOBALS['options'][$key] ) ) { return false; } $GLOBALS['options'][$key] = $value; return true; } function update_option( $key, $value ) { $GLOBALS['options'][$key] = $value; }
function admin_url( $s ) { return '/wp-admin/' . $s; } function add_query_arg( $args, $url ) { return $url; } function wp_safe_redirect( $url ) { throw new Exception( 'redirect' ); }
require __DIR__ . '/../inc/catalog-v2.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new Exception( $message ); } }
function invoke( $expected ) { try { raghushree_install_catalog(); throw new Exception( 'no redirect' ); } catch ( Exception $e ) { if ( $e->getMessage() !== $expected ) { throw $e; } } }
$GLOBALS['allowed'] = false; invoke( 'denied' ); check( count( $GLOBALS['posts'] ) === 1, 'permission checked' );
$GLOBALS['allowed'] = true; $GLOBALS['nonce'] = false; invoke( 'nonce' ); check( count( $GLOBALS['posts'] ) === 1, 'nonce checked' );
$GLOBALS['nonce'] = true; invoke( 'redirect' );
check( count( $GLOBALS['terms'] ) === 9 && count( $GLOBALS['posts'] ) === 23, 'requested catalog added' );
check( $GLOBALS['posts'][1]['title'] === 'Old product' && $GLOBALS['posts'][1]['status'] === 'publish', 'old product retained' );
check( $GLOBALS['options']['raghushree_catalog_v2'] === 1 && $GLOBALS['options']['raghushree_legacy_catalog_ids'] === array(1), 'old catalog preserved as an exclusion after success' );
$GLOBALS['meta'][2]['custom'] = 'merchant data'; invoke( 'redirect' );
check( count( $GLOBALS['posts'] ) === 23 && $GLOBALS['options']['raghushree_legacy_catalog_ids'] === array(1) && $GLOBALS['meta'][2]['custom'] === 'merchant data', 'repeat import does not duplicate or overwrite merchant data' );
echo "Catalog installer smoke tests passed.\n";

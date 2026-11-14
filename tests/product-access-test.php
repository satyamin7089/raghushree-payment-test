<?php
/** Integration test: run inside a WordPress Playground installation with this repo mounted at /repo. */
register_shutdown_function( function () { $e = error_get_last(); if ( $e && in_array( $e['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR ), true ) ) { file_put_contents( '/results/product-access.txt', 'FATAL ' . print_r( $e, true ) ); } } );
try {
	require '/wordpress/wp-load.php';
	require_once ABSPATH . 'wp-admin/includes/admin.php';
	require_once '/repo/integrations/raghushree-product-access/raghushree-product-access.php';
	require_once '/repo/inc/custom-post-types.php';
	require_once '/repo/inc/product-master.php';
	require_once '/repo/integrations/raghushree-payments/raghushree-payments.php';
	raghushree_register_content_types();
	rgpa_activate();
	// A plugin update must upgrade existing roles without reactivation.
	get_role( RGPA_ROLE )->remove_cap( 'rgpa_manage_product_terms' );
	get_role( RGPA_ROLE )->remove_cap( 'rgpay_manage_orders' );
	update_option( 'rgpa_role_version', '1.0.0' );
	do_action( 'init' );
	function rgpa_assert( $condition, $message ) { if ( ! $condition ) { throw new Exception( $message ); } }
	$client = wp_insert_user( array( 'user_login' => 'rgpatest' . wp_rand(), 'user_pass' => 'test-pass', 'role' => RGPA_ROLE ) );
	$editor = wp_insert_user( array( 'user_login' => 'rgpaeditor' . wp_rand(), 'user_pass' => 'test-pass', 'role' => 'editor' ) );
	wp_set_current_user( 1 );
	$product = wp_insert_post( array( 'post_type' => 'products', 'post_title' => 'Test Product', 'post_status' => 'publish' ), true );
	$page = wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'Page', 'post_status' => 'publish' ), true );
	$post = wp_insert_post( array( 'post_type' => 'post', 'post_title' => 'Post', 'post_status' => 'publish' ), true );
	$enquiry = wp_insert_post( array( 'post_type' => 'raghushree_enquiry', 'post_title' => 'Private', 'post_status' => 'draft' ), true );
	$foreign = wp_insert_attachment( array( 'post_mime_type' => 'image/jpeg', 'post_title' => 'Foreign image', 'post_status' => 'inherit' ) );
	rgpa_assert( current_user_can( 'edit_post', $product ) && current_user_can( 'manage_options' ) && current_user_can( 'rgpay_manage_orders' ) && current_user_can( get_taxonomy( 'polymer_category' )->cap->manage_terms ), 'admin changed' );
	wp_set_current_user( $editor );
	rgpa_assert( current_user_can( 'edit_post', $product ) && current_user_can( 'delete_post', $product ) && ! current_user_can( 'rgpay_manage_orders' ) && current_user_can( get_taxonomy( 'polymer_category' )->cap->manage_terms ), 'existing editor permissions changed' );
	wp_set_current_user( $client );
	foreach ( array( 'edit_post', 'delete_post', 'read_post' ) as $cap ) { rgpa_assert( current_user_can( $cap, $product ), 'client missing ' . $cap ); }
	foreach ( array( $page, $post, $enquiry ) as $id ) { rgpa_assert( ! current_user_can( 'edit_post', $id ) && ! current_user_can( 'delete_post', $id ), 'client can modify other content' ); }
	foreach ( array( 'manage_options', 'edit_posts', 'edit_pages', 'manage_categories', 'unfiltered_html' ) as $cap ) { rgpa_assert( ! current_user_can( $cap ), 'unexpected capability ' . $cap ); }
	rgpa_assert( current_user_can( 'upload_files' ) && current_user_can( get_taxonomy( 'polymer_category' )->cap->assign_terms ), 'media or taxonomy assignment denied' );
	foreach ( rgpa_taxonomies() as $taxonomy ) {
		$tax = get_taxonomy( $taxonomy );
		rgpa_assert( current_user_can( $tax->cap->manage_terms ) && current_user_can( $tax->cap->edit_terms ) && current_user_can( $tax->cap->delete_terms ), 'taxonomy capability missing: ' . $taxonomy );
		$term = wp_insert_term( 'Client ' . $taxonomy, $taxonomy );
		rgpa_assert( ! is_wp_error( $term ) && current_user_can( 'edit_term', $term['term_id'] ) && current_user_can( 'delete_term', $term['term_id'] ), 'term creation/edit/delete denied: ' . $taxonomy );
	}
	rgpa_assert( current_user_can( 'rgpay_manage_orders' ) && ! current_user_can( 'manage_options' ), 'order capability or settings isolation failed' );
	$order = wp_insert_post( array( 'post_type' => 'rgpay_order', 'post_title' => 'Paid order', 'post_status' => 'publish' ) );
	update_post_meta( $order, '_rgpay_status', 'paid' );
	$_POST = array( 'order_id' => $order, 'delivery_status' => 'fulfilled', 'delivery_courier' => 'Courier', 'delivery_tracking' => 'TRACK-1', '_wpnonce' => wp_create_nonce( 'rgpay_delivery_' . $order ) ); $_REQUEST = $_POST;
	$redirect_guard = function () { throw new Exception( 'ORDER_REDIRECT' ); }; add_filter( 'wp_redirect', $redirect_guard );
	try { rgpay_admin_update_delivery(); } catch ( Exception $e ) { if ( 'ORDER_REDIRECT' !== $e->getMessage() ) { throw $e; } }
	remove_filter( 'wp_redirect', $redirect_guard );
	rgpa_assert( 'fulfilled' === get_post_meta( $order, '_rgpay_manual_status', true ) && 'TRACK-1' === get_post_meta( $order, '_rgpay_manual_tracking', true ), 'manual order update failed' );
	$_POST = $_REQUEST = array();
	rgpa_assert( ! current_user_can( get_taxonomy( 'category' )->cap->manage_terms ), 'unrelated taxonomy allowed' );
	rgpa_assert( ! current_user_can( 'edit_post', $foreign ) && ! current_user_can( 'delete_post', $foreign ), 'foreign image modifiable' );
	$own = wp_insert_attachment( array( 'post_mime_type' => 'image/jpeg', 'post_title' => 'Own image', 'post_status' => 'inherit', 'post_author' => $client ), '/tmp/own.jpg' );
	rgpa_assert( current_user_can( 'edit_post', $own ) && ! current_user_can( 'delete_post', $own ), 'own image capabilities incorrect' );
	$client_user = get_userdata( $client ); $client_user->add_role( 'administrator' );
	rgpa_assert( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_post', $page ), 'extra role bypassed restrictions' );
	$client_user->remove_role( 'administrator' ); wp_set_current_user( $client );
	$mimes = get_allowed_mime_types(); rgpa_assert( isset( $mimes['jpg|jpeg|jpe'] ) && ! isset( $mimes['pdf'] ), 'upload MIME restriction' );
	$_POST = array( 'raghushree_master_nonce' => wp_create_nonce( 'raghushree_master_save' ), 'rg_product_type' => 'pipe', 'rg_gst' => '18', 'rg_hsn' => '', 'rg_packet_label' => 'Packet', 'rg_packet_price' => '125.50', 'rg_packet_rows' => '1, 8, 40, 30, 20' );
	raghushree_master_box_save( $product );
	$commerce = get_post_meta( $product, '_raghushree_commerce', true );
	rgpa_assert( isset( $commerce['gst_bps'], $commerce['packet_price_paise'] ) && 1800 === $commerce['gst_bps'] && 12550 === $commerce['packet_price_paise'] && ! empty( $commerce['packet_rows'] ), 'product pricing/dimensions not saved: ' . wp_json_encode( $commerce ) );
	$_POST = array();
	$server = rest_get_server();
	foreach ( array( '/wp/v2/pages', '/wp/v2/posts', '/wp/v2/users', '/wp/v2/settings', '/wp/v2/media/' . $foreign ) as $route ) {
		$request = new WP_REST_Request( 'POST', $route );
		$response = $server->dispatch( $request );
		rgpa_assert( $response->get_status() >= 400, 'REST write allowed: ' . $route );
	}
	$request = new WP_REST_Request( 'POST', '/wp/v2/polymer_category' ); $request->set_param( 'name', 'REST term' );
	$term_response = $server->dispatch( $request );
	rgpa_assert( 201 === $term_response->get_status(), 'product taxonomy REST create denied: ' . wp_json_encode( $term_response->get_data() ) );
	$term_id = $term_response->get_data()['id'];
	$request = new WP_REST_Request( 'DELETE', '/wp/v2/polymer_category/' . $term_id ); $request->set_param( 'force', true );
	rgpa_assert( 200 === $server->dispatch( $request )->get_status(), 'product taxonomy REST delete denied' );
	$request = new WP_REST_Request( 'POST', '/wp/v2/categories' ); $request->set_param( 'name', 'Forbidden' );
	rgpa_assert( 403 === $server->dispatch( $request )->get_status(), 'other taxonomy REST allowed' );
	$request = new WP_REST_Request( 'GET', '/wp/v2/products/' . $product ); $request->set_param( 'context', 'edit' );
	rgpa_assert( 200 === $server->dispatch( $request )->get_status(), 'product REST edit context denied' );
	$request = new WP_REST_Request( 'GET', '/wp/v2/media' ); $request->set_param( 'context', 'edit' );
	rgpa_assert( 200 === $server->dispatch( $request )->get_status(), 'media REST list denied' );
	rgpa_assert( ! current_user_can( 'edit_post', $post ) && ! current_user_can( 'edit_posts' ), 'media context leaked post capabilities' );
	$request = new WP_REST_Request( 'POST', '/wp/v2/products/' . $product ); $request->set_param( 'title', 'Updated via REST' );
	rgpa_assert( 200 === $server->dispatch( $request )->get_status() && 'Updated via REST' === get_post( $product )->post_title, 'product REST update denied' );
	// Exercise the exact admin_init gate without invoking unrelated WordPress hooks.
	$callbacks = $GLOBALS['wp_filter']['admin_init']->callbacks[0]; $guard = null;
	foreach ( $callbacks as $callback ) { if ( $callback['function'] instanceof Closure ) { $guard = $callback['function']; break; } }
	rgpa_assert( null !== $guard, 'admin guard missing' );
	add_filter( 'wp_die_handler', function () { return function () { throw new Exception( 'DENIED' ); }; } );
	foreach ( array( array('options-general.php',array()), array('edit.php',array('post_type'=>'products','page'=>'raghushree-payment-setup')), array('edit.php',array('post_type'=>'products','page'=>'raghushree-catalog-setup')), array('edit-tags.php',array('taxonomy'=>'category')), array('admin-post.php',array('action'=>'raghushree_payment_settings_save')), array('admin-post.php',array('action'=>'raghushree_install_catalog')), array('admin-ajax.php',array('action'=>'add-tag','taxonomy'=>'category')),  array('admin.php',array('page'=>'raghushree-payment-settings')), array('post.php',array('post'=>$page)), array('edit.php',array('post_type'=>'post')), array('admin-ajax.php',array('action'=>'some_plugin_action')), array('admin-ajax.php',array('action'=>'get-permalink','post_id'=>$page)) ) as $test ) {
		$GLOBALS['pagenow'] = $test[0]; $_GET = $_REQUEST = $_POST = $test[1]; $denied = false;
		try { $guard(); } catch ( Exception $e ) { if ( 'DENIED' !== $e->getMessage() ) { throw $e; } $denied = true; }
		rgpa_assert( $denied, 'admin bypass: ' . wp_json_encode( $test ) );
	}
	foreach ( array( array('edit.php',array('post_type'=>'products')), array('post.php',array('post'=>$product)), array('post-new.php',array('post_type'=>'products')), array('admin-ajax.php',array('action'=>'query-attachments')), array('edit.php',array('post_type'=>'products','page'=>'rgpay-orders')), array('admin.php',array('page'=>'rgpay-orders')),  array('edit-tags.php',array('taxonomy'=>'polymer_category')), array('term.php',array('taxonomy'=>'material_form')), array('admin-post.php',array('action'=>'rgpay_update_delivery')), array('admin-ajax.php',array('action'=>'add-tag','taxonomy'=>'polymer_category')), array('admin-ajax.php',array('action'=>'add-material_grade')) ) as $test ) {
		$GLOBALS['pagenow'] = $test[0]; $_GET = $_REQUEST = $_POST = $test[1]; $guard();
	}
	file_put_contents( '/results/product-access.txt', 'PASS: upgrade, products, taxonomy management, orders capability, media, pricing/shipping save, REST and admin gates; existing admin/editor permissions.' );
} catch ( Throwable $e ) { file_put_contents( '/results/product-access.txt', 'FAIL: ' . $e->getMessage() . "\n" . $e->getTraceAsString() ); }

<?php
/**
 * Plugin Name: Raghushree Product Access
 * Description: Restricts the Product Manager role to the existing Products editor and its image picker.
 * Version: 1.2.0
 * Requires PHP: 7.4
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const RGPA_ROLE = 'raghushree_product_manager';
const RGPA_VERSION = '1.1.0';

function rgpa_taxonomies() {
	return array( 'polymer_category', 'material_grade', 'material_form', 'manufacturing_application' );
}

function rgpa_product_caps() {
	return array( 'edit_rg_products', 'edit_others_rg_products', 'publish_rg_products', 'read_private_rg_products', 'delete_rg_products', 'delete_private_rg_products', 'delete_published_rg_products', 'delete_others_rg_products', 'edit_private_rg_products', 'edit_published_rg_products' );
}
function rgpa_activate() {
	$caps = array_fill_keys( rgpa_product_caps(), true );
	$caps['read'] = true;
	$caps['upload_files'] = true;
	$caps['rgpa_manage_product_terms'] = true;
	$caps['rgpay_manage_orders'] = true;
	add_role( RGPA_ROLE, 'Product Manager (restricted)', $caps );
	$role = get_role( RGPA_ROLE );
	if ( $role ) {
		// Reconcile capabilities on activation, including after a plugin upgrade.
		foreach ( array_keys( $role->capabilities ) as $cap ) { $role->remove_cap( $cap ); }
		foreach ( $caps as $cap => $grant ) { $role->add_cap( $cap, $grant ); }
	}
	update_option( 'rgpa_role_version', RGPA_VERSION );
}
function rgpa_deactivate() { remove_role( RGPA_ROLE ); delete_option( 'rgpa_role_version' ); }
register_activation_hook( __FILE__, 'rgpa_activate' );
register_deactivation_hook( __FILE__, 'rgpa_deactivate' );
// Plugin updates do not fire the activation hook; upgrade existing role assignments.
add_action( 'init', function () {
	if ( RGPA_VERSION !== get_option( 'rgpa_role_version' ) || ! get_role( RGPA_ROLE ) ) {
		rgpa_activate();
		$user = wp_get_current_user();
		if ( $user && $user->ID ) { $user->get_role_caps(); }
	}
}, 1 );

function rgpa_restricted( $user = null ) {
	$user = $user ?: wp_get_current_user();
	return $user && in_array( RGPA_ROLE, (array) $user->roles, true );
}

// Isolate Products from the shared post capabilities used by posts and enquiries.
add_filter( 'register_post_type_args', function ( $args, $type ) {
	if ( 'products' === $type ) {
		$args['capability_type'] = array( 'rg_product', 'rg_products' );
		$args['capabilities'] = array();
		$args['map_meta_cap'] = true;
	}
	return $args;
}, 10, 2 );

// Existing users retain the same product access as their standard post permissions.
add_filter( 'user_has_cap', function ( $allcaps, $caps, $args, $user ) {
	if ( rgpa_restricted( $user ) ) {
		$allowed = array_fill_keys( array_merge( array( 'read', 'upload_files', 'rgpa_manage_product_terms', 'rgpay_manage_orders', RGPA_ROLE ), rgpa_product_caps() ), true );
		$allcaps = array_intersect_key( $allcaps, $allowed );
		// The REST media controller checks this primitive directly for listing/uploading.
		// Never grant it to object checks, even inside a media request.
		if ( 'edit_posts' === ( $args[0] ?? '' ) && ! empty( $GLOBALS['rgpa_media_rest'] ) ) {
			$allcaps['edit_posts'] = true;
		}
		return $allcaps;
	}
	if ( ! array_key_exists( 'rgpa_manage_product_terms', $allcaps ) ) {
		$allcaps['rgpa_manage_product_terms'] = ! empty( $allcaps['manage_categories'] );
	}
	foreach ( rgpa_product_caps() as $product_cap ) {
		$post_cap = str_replace( 'rg_products', 'posts', $product_cap );
		if ( ! array_key_exists( $product_cap, $allcaps ) ) {
			$allcaps[ $product_cap ] = ! empty( $allcaps[ $post_cap ] );
		}
	}
	return $allcaps;
}, 1000, 4 );

// Give this role its own management capability for only the four product taxonomies.
add_filter( 'register_taxonomy_args', function ( $args, $taxonomy ) {
	if ( in_array( $taxonomy, rgpa_taxonomies(), true ) ) {
		$args['capabilities'] = isset( $args['capabilities'] ) ? $args['capabilities'] : array();
		foreach ( array( 'manage_terms', 'edit_terms', 'delete_terms' ) as $cap ) { $args['capabilities'][ $cap ] = 'rgpa_manage_product_terms'; }
		$args['capabilities']['assign_terms'] = 'edit_rg_products';
	}
	return $args;
}, 10, 2 );

// Image metadata may be edited only for the client's own uploads. No media deletion.
add_filter( 'map_meta_cap', function ( $caps, $cap, $user_id, $args ) {
	if ( ! rgpa_restricted( get_userdata( $user_id ) ) || ! in_array( $cap, array( 'edit_post', 'edit_page', 'delete_post', 'delete_page' ), true ) || empty( $args[0] ) ) { return $caps; }
	$post = get_post( $args[0] );
	if ( $post && 'attachment' === $post->post_type ) {
		if ( 'edit_post' === $cap && (int) $post->post_author === (int) $user_id && wp_attachment_is_image( $post ) ) { return array( 'upload_files' ); }
		return array( 'do_not_allow' );
	}
	return $caps;
}, 1000, 4 );

add_filter( 'upload_mimes', function ( $mimes ) {
	if ( rgpa_restricted() ) { return array_intersect_key( $mimes, array_flip( array( 'jpg|jpeg|jpe', 'gif', 'png', 'webp', 'avif', 'bmp', 'tif|tiff', 'heic' ) ) ); }
	return $mimes;
}, 1000 );

function rgpa_product_id( $id ) {
	$post = get_post( (int) $id );
	if ( $post && 'revision' === $post->post_type ) { $post = get_post( $post->post_parent ); }
	return $post && 'products' === $post->post_type;
}
function rgpa_deny() { wp_die( esc_html__( 'This account can only access Products.', 'raghushree-product-access' ), '', array( 'response' => 403 ) ); }

add_action( 'admin_init', function () {
	if ( ! rgpa_restricted() ) { return; }
	global $pagenow;
	if ( is_network_admin() || is_user_admin() ) { rgpa_deny(); }
	if ( 'index.php' === $pagenow && 'GET' === ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=products' ) ); exit;
	}
	if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'products' === $_GET['post_type'] && ( ! isset( $_GET['page'] ) || 'rgpay-orders' === $_GET['page'] ) ) { return; }
	if ( 'admin.php' === $pagenow && ( $_GET['page'] ?? '' ) === 'rgpay-orders' ) { return; }
	if ( in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) && isset( $_REQUEST['taxonomy'] ) && is_string( $_REQUEST['taxonomy'] ) && in_array( $_REQUEST['taxonomy'], rgpa_taxonomies(), true ) ) { return; }
	if ( 'admin-post.php' === $pagenow && ( $_REQUEST['action'] ?? '' ) === 'rgpay_update_delivery' ) { return; }
	if ( 'post-new.php' === $pagenow && isset( $_GET['post_type'] ) && 'products' === $_GET['post_type'] ) { return; }
	if ( 'post.php' === $pagenow && rgpa_product_id( $_REQUEST['post'] ?? $_POST['post_ID'] ?? 0 ) ) { return; }
	if ( 'revision.php' === $pagenow && rgpa_product_id( $_REQUEST['revision'] ?? $_REQUEST['to'] ?? 0 ) ) { return; }
	if ( 'async-upload.php' === $pagenow && ( $_REQUEST['action'] ?? '' ) === 'upload-attachment' ) { return; }
	if ( 'admin-ajax.php' === $pagenow ) {
		$action = isset( $_REQUEST['action'] ) && is_string( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
		$allowed = array( 'heartbeat', 'autosave', 'inline-save', 'query-attachments', 'get-attachment', 'upload-attachment', 'save-attachment', 'save-attachment-compat', 'save-attachment-order', 'set-post-thumbnail', 'get-post-thumbnail-html', 'get-permalink', 'sample-permalink', 'get-revision-diffs', 'wp-link-ajax', 'parse-media-shortcode', 'get-tagcloud', 'get-post-formats', 'add-tag', 'delete-tag', 'inline-save-tax', 'ajax-tag-search' );
		foreach ( rgpa_taxonomies() as $taxonomy ) { $allowed[] = 'add-' . $taxonomy; }
		if ( ! in_array( $action, $allowed, true ) ) { rgpa_deny(); }
		if ( in_array( $action, array( 'add-tag', 'delete-tag', 'inline-save-tax', 'ajax-tag-search' ), true ) ) {
			$taxonomy = $_REQUEST['taxonomy'] ?? $_REQUEST['tax'] ?? '';
			if ( ! is_string( $taxonomy ) || ! in_array( $taxonomy, rgpa_taxonomies(), true ) ) { rgpa_deny(); }
		}
		if ( in_array( $action, array( 'get-permalink', 'sample-permalink' ), true ) && ! rgpa_product_id( $_POST['post_id'] ?? 0 ) ) { rgpa_deny(); }
		if ( 'save-attachment' === $action && ! empty( $_REQUEST['changes']['parent'] ) && ! rgpa_product_id( $_REQUEST['changes']['parent'] ) ) { rgpa_deny(); }
		return;
	}
	rgpa_deny();
}, 0 );

// Limit the editor API to Products, its taxonomies, media and editor discovery data.
add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( ! rgpa_restricted() ) { return $result; }
	$route = $request->get_route(); $method = $request->get_method();
	$read = in_array( $method, array( 'GET', 'HEAD', 'OPTIONS' ), true );
	$product = (bool) preg_match( '#^/wp/v2/products(?:/\d+(?:/autosaves(?:/\d+)?|/revisions(?:/\d+)?)?)?/?$#', $route );
	$media = (bool) preg_match( '#^/wp/v2/media(?:/\d+)?/?$#', $route );
	$tax = (bool) preg_match( '#^/wp/v2/(?:polymer_category|material_grade|material_form|manufacturing_application)(?:/\d+)?/?$#', $route );
	$discovery = (bool) preg_match( '#^/wp/v2/(?:types(?:/products)?|taxonomies(?:/(?:polymer_category|material_grade|material_form|manufacturing_application))?|statuses(?:/[^/]+)?|settings|users/me|block-types(?:/[^/]+(?:/[^/]+)?)?|blocks(?:/\d+)?|block-patterns/(?:patterns|categories)|search|themes|font-families(?:/.*)?)/?$#', $route );
	if ( $media && ! $read && ! in_array( $method, array( 'POST' ), true ) ) { $media = false; }
	if ( $media && ! $read && ! empty( $request['post'] ) && ! rgpa_product_id( $request['post'] ) ) { $media = false; }
	if ( ! ( $product || $media || $tax || ( $read && $discovery ) ) ) {
		return new WP_Error( 'rgpa_forbidden', 'This account can only access the Products editor.', array( 'status' => 403 ) );
	}
	return $result;
}, 10, 3 );
add_filter( 'rest_request_before_callbacks', function ( $response, $handler, $request ) {
	$GLOBALS['rgpa_media_stack'][] = ! empty( $GLOBALS['rgpa_media_rest'] );
	$GLOBALS['rgpa_media_rest'] = rgpa_restricted() && (bool) preg_match( '#^/wp/v2/media(?:/\d+)?/?$#', $request->get_route() );
	return $response;
}, 10, 3 );
add_filter( 'rest_request_after_callbacks', function ( $response ) {
	$GLOBALS['rgpa_media_rest'] = array_pop( $GLOBALS['rgpa_media_stack'] );
	return $response;
}, PHP_INT_MAX );

add_action( 'admin_menu', function () {
	if ( ! rgpa_restricted() ) { return; }
	global $menu, $submenu;
	foreach ( $menu as $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=products' !== $item[2] ) { remove_menu_page( $item[2] ); }
	}
	if ( isset( $submenu['edit.php?post_type=products'] ) ) {
		foreach ( $submenu['edit.php?post_type=products'] as $item ) {
			$allowed_submenus = array( 'edit.php?post_type=products', 'post-new.php?post_type=products', 'rgpay-orders' );
			foreach ( rgpa_taxonomies() as $taxonomy ) { $allowed_submenus[] = 'edit-tags.php?taxonomy=' . $taxonomy . '&amp;post_type=products'; $allowed_submenus[] = 'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=products'; }
			if ( isset( $item[2] ) && ! in_array( $item[2], $allowed_submenus, true ) ) { remove_submenu_page( 'edit.php?post_type=products', $item[2] ); }
		}
	}
}, 9999 );
add_filter( 'show_admin_bar', function ( $show ) { return rgpa_restricted() ? false : $show; } );
add_filter( 'login_redirect', function ( $redirect, $requested, $user ) { return $user instanceof WP_User && rgpa_restricted( $user ) ? admin_url( 'edit.php?post_type=products' ) : $redirect; }, 10, 3 );

// Keep a sign-out control, but do not advertise unrelated admin screens.
add_action( 'admin_bar_menu', function ( $bar ) {
	if ( ! rgpa_restricted() ) { return; }
	foreach ( $bar->get_nodes() as $node ) {
		if ( ! in_array( $node->id, array( 'top', 'top-secondary', 'menu-toggle', 'my-account', 'user-actions', 'logout' ), true ) ) { $bar->remove_node( $node->id ); }
	}
	$bar->add_node( array( 'id' => 'my-account', 'href' => false ) );
}, 9999 );

// Presentation only: the native WordPress screens and capability checks remain authoritative.
require_once __DIR__ . '/admin-workspace.php';

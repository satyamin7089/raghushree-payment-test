<?php
/** Requested catalog definitions and non-destructive admin import. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function raghushree_catalog_definitions() {
	return array(
		'pvc-granules' => array( 'title' => 'PVC Granules', 'type' => 'polymer', 'products' => array( 'Rigid PVC Granules', 'PVC Soft Granules' ) ),
		'pvc-resin' => array( 'title' => 'PVC Resin', 'type' => 'polymer', 'products' => array( 'PVC K57 Resin', 'PVC Resin K-70 (Prime)', 'Off Grade PVC Resin' ) ),
		'pp-granules' => array( 'title' => 'PP Granules', 'type' => 'polymer', 'products' => array( 'PP Granules' ) ),
		'ldpe-granules' => array( 'title' => 'LDPE Granules', 'type' => 'polymer', 'products' => array( 'Mixed Color', 'LDPE Milky Granules', 'LDPE Natural Granules' ) ),
		'hdpe-granules' => array( 'title' => 'HDPE Granules', 'type' => 'polymer', 'products' => array( 'HDPE PE-100 Granules', 'HDPE Blue Drum Granules' ) ),
		'industrial-hoses' => array( 'title' => 'Industrial Hoses', 'type' => 'pipe', 'products' => array( 'Oil Hoses' ) ),
		'pvc-suction-pipes' => array( 'title' => 'PVC Suction Pipes', 'type' => 'pipe', 'products' => array( 'PVC Flexible Suction Pipe', 'PVC Duct Pipe', 'Suction Hose PVC Pipe' ) ),
		'pvc-tubing' => array( 'title' => 'PVC Tubing', 'type' => 'pipe', 'products' => array( 'Vipul PVC Flexible & Garden Pipes', 'Tiger PVC Flexible Garden Pipes', 'Raghusree Abir PVC Flexible and Garden Pipes', 'Bhavik PVC Flexible & Garden Pipes' ) ),
		'lay-flat-pipes' => array( 'title' => 'Lay Flat Pipes', 'type' => 'pipe', 'products' => array( 'Lay Flat Tubes', 'Lay Flat Hoses', 'High Pressure Lay Flat Pipe' ) ),
	);
}
function raghushree_catalog_admin_menu() { add_submenu_page( 'edit.php?post_type=products', 'New Catalog Setup', 'New Catalog Setup', 'manage_options', 'raghushree-catalog-setup', 'raghushree_catalog_admin_screen' ); }
add_action( 'admin_menu', 'raghushree_catalog_admin_menu' );
function raghushree_catalog_admin_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	?><div class="wrap"><h1>New Product Catalog</h1><p>Back up first. This creates the requested categories and product listings without deleting or changing existing products or orders. The new archive will show the new catalog after successful installation; old product URLs remain available. New products have no online prices or shipping dimensions until configured in Product Master.</p>
	<?php if ( isset( $_GET['catalog_result'] ) ) : ?><div class="notice <?php echo 'success' === sanitize_key( wp_unslash( $_GET['catalog_result'] ) ) ? 'notice-success' : 'notice-error'; ?>"><p><?php echo 'success' === sanitize_key( wp_unslash( $_GET['catalog_result'] ) ) ? 'Catalog installed. Configure prices, GST, HSN, images and dimensions before selling.' : 'Some catalog items could not be created. Check the site and try again; existing products were not removed.'; ?></p></div><?php endif; ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="raghushree_install_catalog"><?php wp_nonce_field( 'raghushree_install_catalog' ); ?><button class="button button-primary" type="submit">Install / repair requested catalog</button></form></div><?php
}
function raghushree_install_catalog() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Permission denied.' ); }
	check_admin_referer( 'raghushree_install_catalog' );
	// Preserve the old public catalog without deleting records; do this only once,
	// before creating any new entries, so retries never hide newly added products.
	if ( false === get_option( 'raghushree_legacy_catalog_ids', false ) ) {
		$legacy = get_posts( array( 'post_type' => 'products', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1, 'no_found_rows' => true ) );
		add_option( 'raghushree_legacy_catalog_ids', array_map( 'intval', $legacy ), '', false );
		if ( false === get_option( 'raghushree_legacy_catalog_ids', false ) ) { wp_die( 'Could not save the existing catalog snapshot. No new products were created; check database access and try again.' ); }
	}
	$ok = true;
	foreach ( raghushree_catalog_definitions() as $slug => $category ) {
		$term = get_term_by( 'slug', $slug, 'polymer_category' );
		if ( ! $term ) { $created = wp_insert_term( $category['title'], 'polymer_category', array( 'slug' => $slug ) ); if ( is_wp_error( $created ) ) { $ok = false; continue; } $term_id = (int) $created['term_id']; } else { $term_id = (int) $term->term_id; }
		foreach ( $category['products'] as $title ) {
			$key = $slug . ':' . sanitize_title( $title );
			$found = get_posts( array( 'post_type' => 'products', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 1, 'meta_key' => '_raghushree_catalog_key', 'meta_value' => $key ) );
			if ( $found ) { if ( is_wp_error( wp_set_object_terms( (int) $found[0], array( $term_id ), 'polymer_category', true ) ) ) { $ok = false; } continue; }
			$id = wp_insert_post( array( 'post_type' => 'products', 'post_status' => 'publish', 'post_title' => $title ), true );
			if ( is_wp_error( $id ) || ! $id ) { $ok = false; continue; }
			update_post_meta( $id, '_raghushree_catalog_key', $key );
			update_post_meta( $id, '_raghushree_commerce', array( 'type' => $category['type'], 'gst_bps' => 1800, 'hsn' => '', 'packet_label' => '', 'packet_price_paise' => 0, 'packet_rows' => array() ) );
			if ( is_wp_error( wp_set_object_terms( $id, array( $term_id ), 'polymer_category' ) ) ) { $ok = false; }
		}
	}
	if ( $ok ) { update_option( 'raghushree_catalog_v2', 1 ); }
	wp_safe_redirect( add_query_arg( array( 'post_type' => 'products', 'page' => 'raghushree-catalog-setup', 'catalog_result' => $ok ? 'success' : 'failed' ), admin_url( 'edit.php' ) ) ); exit;
}
add_action( 'admin_post_raghushree_install_catalog', 'raghushree_install_catalog' );

<?php
/** The restricted client's visual layer for the existing WordPress screens. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Return a workspace context only on screens the client is permitted to use. */
function rgpa_workspace_context() {
	if ( ! rgpa_restricted() || ! function_exists( 'get_current_screen' ) ) { return ''; }
	$screen = get_current_screen();
	if ( ! $screen ) { return ''; }
	global $pagenow;
	if ( isset( $_GET['page'] ) && 'rgpay-orders' === $_GET['page'] && in_array( $pagenow, array( 'admin.php', 'edit.php' ), true ) ) { return 'orders'; }
	if ( in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) && in_array( $screen->taxonomy, rgpa_taxonomies(), true ) ) { return $screen->taxonomy; }
	if ( 'products' === $screen->post_type ) {
		if ( 'edit.php' === $pagenow ) { return 'products'; }
		if ( in_array( $pagenow, array( 'post.php', 'post-new.php', 'revision.php' ), true ) ) { return 'editor'; }
	}
	return '';
}

add_filter( 'admin_body_class', function ( $classes ) {
	$context = rgpa_workspace_context();
	return $context ? $classes . ' rgpa-workspace rgpa-workspace-' . sanitize_html_class( $context ) : $classes;
} );
add_action( 'admin_enqueue_scripts', function () {
	if ( ! rgpa_workspace_context() ) { return; }
	wp_enqueue_style( 'rgpa-admin-workspace', plugins_url( 'assets/admin-workspace.css', __FILE__ ), array(), '1.2.0' );
} );

/** A small, accessible wayfinding header; content and forms below are still WordPress's own. */
add_action( 'all_admin_notices', function () {
	$context = rgpa_workspace_context();
	if ( ! $context || 'editor' === $context ) { return; }
	$sections = array(
		'products' => array( 'Products', 'Your catalogue, in focus.', 'Create, update and organise the products your customers see.', admin_url( 'edit.php?post_type=products' ) ),
		'polymer_category' => array( 'Categories', 'Give every product a place.', 'Organise your catalogue by polymer category.', admin_url( 'edit-tags.php?taxonomy=polymer_category&post_type=products' ) ),
		'material_grade' => array( 'Grades', 'The details that define quality.', 'Manage the material grades used across your catalogue.', admin_url( 'edit-tags.php?taxonomy=material_grade&post_type=products' ) ),
		'material_form' => array( 'Forms', 'Materials, clearly organised.', 'Keep your material forms accurate and easy to find.', admin_url( 'edit-tags.php?taxonomy=material_form&post_type=products' ) ),
		'manufacturing_application' => array( 'Applications', 'Connect materials with purpose.', 'Manage the applications associated with your products.', admin_url( 'edit-tags.php?taxonomy=manufacturing_application&post_type=products' ) ),
		'orders' => array( 'Orders', 'Every order, in one place.', 'Review payment details and keep manual delivery information up to date.', admin_url( 'admin.php?page=rgpay-orders' ) ),
	);
	if ( ! isset( $sections[ $context ] ) ) { return; }
	$section = $sections[ $context ];
	?>
	<section class="rgpa-hero" aria-labelledby="rgpa-hero-title">
		<div class="rgpa-hero__content">
			<div class="rgpa-hero__eyebrow"><span class="rgpa-hero__mark" aria-hidden="true">R</span><span>RAGHUSHREE <span class="rgpa-hero__slash">/</span> PRODUCT WORKSPACE</span></div>
			<h2 id="rgpa-hero-title"><?php echo esc_html( $section[1] ); ?></h2>
			<p><?php echo esc_html( $section[2] ); ?></p>
		</div>
		<div class="rgpa-hero__signature" aria-hidden="true"><span>R</span><small>THOUGHTFULLY<br>ORGANISED</small></div>
	</section>
	<nav class="rgpa-nav" aria-label="Product workspace sections">
		<?php foreach ( $sections as $key => $item ) :
			if ( 'orders' === $key && ( ! function_exists( 'rgpay_orders_screen' ) || ! current_user_can( 'rgpay_manage_orders' ) ) ) { continue; }
			?>
			<a class="rgpa-nav__link<?php echo $key === $context ? ' is-active' : ''; ?>" href="<?php echo esc_url( $item[3] ); ?>"<?php echo $key === $context ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $item[0] ); ?></a>
		<?php endforeach; ?>
		<a class="rgpa-nav__link rgpa-nav__logout" href="<?php echo esc_url( wp_logout_url() ); ?>">Sign out</a>
	</nav>
	<?php if ( 'products' === $context ) :
		$counts = wp_count_posts( 'products', 'readable' );
		$metrics = array(
			array( 'Published', (int) ( $counts->publish ?? 0 ), 'publish', 'Live in your catalogue' ),
			array( 'Drafts', (int) ( $counts->draft ?? 0 ), 'draft', 'Ready for your attention' ),
			array( 'In trash', (int) ( $counts->trash ?? 0 ), 'trash', 'Review or restore' ),
		);
		?>
		<div class="rgpa-metrics" aria-label="Product status overview">
			<?php foreach ( $metrics as $metric ) : ?>
			<a class="rgpa-metric" href="<?php echo esc_url( add_query_arg( array( 'post_type' => 'products', 'post_status' => $metric[2] ), admin_url( 'edit.php' ) ) ); ?>">
				<span class="rgpa-metric__top"><span><?php echo esc_html( $metric[0] ); ?></span><svg class="rgpa-metric__arrow" aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 12L12 4M6 4h6v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
				<strong><?php echo esc_html( number_format_i18n( $metric[1] ) ); ?></strong>
				<span class="rgpa-metric__caption"><?php echo esc_html( $metric[3] ); ?></span>
			</a>
			<?php endforeach; ?>
		</div>
	<?php endif;
}, 5 );

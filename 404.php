<?php
/**
 * 404 Not Found Template (`404.php`).
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main-content">
	<?php
	get_template_part(
		'template-parts/components/page-banner',
		null,
		array(
			'eyebrow'       => __( 'Error 404', 'raghushree' ),
			'title'         => __( 'Requested Page Could Not Be Found', 'raghushree' ),
			'description'   => __( 'The link you followed may have moved. Explore our plastic raw material catalog or contact Raghushree directly for assistance.', 'raghushree' ),
			'primary_cta'   => array(
				'label' => __( 'Return to Homepage', 'raghushree' ),
				'url'   => home_url( '/' ),
			),
			'secondary_cta' => array(
				'kicker' => __( 'Explore Catalog', 'raghushree' ),
				'label'  => __( 'Explore Polymer Catalog', 'raghushree' ),
				'url'    => raghushree_get_products_url(),
				'icon'   => 'layers',
			),
			'image_slot'    => 'products_hero',
			'caption_title' => __( 'Industrial Polymer Raw Material Supply', 'raghushree' ),
			'caption_sub'   => __( 'Bulk orders, grade consultation & sample enquiries', 'raghushree' ),
		)
	);
	?>
</main>

<?php
get_footer();

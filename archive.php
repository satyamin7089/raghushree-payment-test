<?php
/**
 * Generic Archive Fallback Template (`archive.php`).
 *
 * Routes polymer taxonomy archives to the unified dynamic product catalog.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_tax( array( 'polymer_category', 'material_grade', 'material_form', 'manufacturing_application' ) ) ) {
	get_template_part( 'archive-products' );
	return;
}

get_header();
?>

<main id="main-content">
	<?php
	get_template_part(
		'template-parts/components/page-banner',
		null,
		array(
			'eyebrow'       => __( 'Archive', 'raghushree' ),
			'title'         => wp_strip_all_tags( get_the_archive_title() ),
			'description'   => wp_strip_all_tags( get_the_archive_description() ),
			'primary_cta'   => array(
				'label' => __( 'Explore Products', 'raghushree' ),
				'url'   => raghushree_get_products_url(),
			),
			'secondary_cta' => array(
				'kicker' => __( 'Bulk Order Enquiries', 'raghushree' ),
				'label'  => __( 'Request a Quote', 'raghushree' ),
				'url'    => raghushree_get_enquiry_url( array( 'enquiry_type' => 'Request a Quotation' ) ),
				'icon'   => 'phone',
			),
			'image_slot'    => 'products_hero',
			'caption_title' => __( 'Industrial Polymer Raw Material Supply', 'raghushree' ),
			'caption_sub'   => __( 'Bulk orders, grade consultation & sample enquiries', 'raghushree' ),
		)
	);
	?>

	<section class="section surface--white">
		<div class="container">
			<?php if ( have_posts() ) : ?>
				<div class="grid-3">
					<?php
					while ( have_posts() ) :
						the_post();
						if ( 'products' === get_post_type() ) {
							get_template_part( 'template-parts/components/product-card', null, array( 'product_id' => get_the_ID() ) );
						} else {
							get_template_part(
								'template-parts/components/feature-card',
								null,
								array(
									'title'       => get_the_title(),
									'description' => wp_trim_words( get_the_excerpt(), 22 ),
									'link_label'  => __( 'Read More', 'raghushree' ),
									'link_url'    => get_permalink(),
								)
							);
						}
					endwhile;
					?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'No entries found in this archive.', 'raghushree' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();

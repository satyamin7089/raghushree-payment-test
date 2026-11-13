<?php
/**
 * Default WordPress Page Template (`page.php`) — Supports Privacy Policy, Legal & Custom Pages.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Route /about/ or /contact/ slugs cleanly even if edited without template meta
if ( is_page( 'about' ) ) {
	get_template_part( 'page-templates/template-about' );
	return;
}
if ( is_page( 'contact' ) ) {
	get_template_part( 'page-templates/template-contact' );
	return;
}

get_header();
?>

<main id="main-content">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<?php
		get_template_part(
			'template-parts/components/page-banner',
			null,
			array(
				'eyebrow'       => __( 'Raghushree', 'raghushree' ),
				'title'         => get_the_title(),
				'description'   => has_excerpt() ? get_the_excerpt() : '',
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
				'image_slot'    => 'about_hero',
				'post_id'       => get_the_ID(),
				'caption_title' => __( 'Industrial Polymer Raw Material Supply', 'raghushree' ),
				'caption_sub'   => __( 'Bulk orders, grade consultation & sample enquiries', 'raghushree' ),
			)
		);
		?>

		<section class="section surface--white">
			<div class="container--narrow entry-content">
				<?php the_content(); ?>
			</div>
		</section>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();

<?php
/**
 * Standard Single Post Fallback Template (`single.php`).
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
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php
			get_template_part(
				'template-parts/components/page-banner',
				null,
				array(
					'eyebrow'       => get_the_date(),
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
		</article>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();

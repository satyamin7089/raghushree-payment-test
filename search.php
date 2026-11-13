<?php
/**
 * Search Results Template (`search.php`).
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
			'eyebrow'       => __( 'Site Search', 'raghushree' ),
			'title'         => sprintf(
				/* translators: %s: Search query */
				__( 'Search Results for "%s"', 'raghushree' ),
				get_search_query()
			),
			'description'   => __( 'Browse matching polymer categories and pages below, or submit a direct raw material enquiry.', 'raghushree' ),
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
									'meta'        => ucfirst( get_post_type() ),
									'title'       => get_the_title(),
									'description' => wp_trim_words( wp_strip_all_tags( get_the_excerpt() ? get_the_excerpt() : get_the_content() ), 22 ),
									'link_label'  => __( 'Open Page', 'raghushree' ),
									'link_url'    => get_permalink(),
								)
							);
						}
					endwhile;
					?>
				</div>
			<?php else : ?>
				<div class="products-empty" style="background-color: var(--pearl);">
					<h2><?php esc_html_e( 'No Matching Results Found', 'raghushree' ); ?></h2>
					<p style="max-width: 520px; margin: var(--space-3) auto var(--space-5);">
						<?php esc_html_e( 'Explore our full polymer catalog or contact our sales team directly with your material specification.', 'raghushree' ); ?>
					</p>
					<div class="btn-group" style="justify-content: center;">
						<a class="btn btn--primary" href="<?php echo esc_url( raghushree_get_products_url() ); ?>">
							<span><?php esc_html_e( 'View All Polymers', 'raghushree' ); ?></span>
						</a>
						<a class="btn btn--secondary" href="<?php echo esc_url( raghushree_get_page_url( 'contact' ) ); ?>">
							<span><?php esc_html_e( 'Contact Sales Team', 'raghushree' ); ?></span>
						</a>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();

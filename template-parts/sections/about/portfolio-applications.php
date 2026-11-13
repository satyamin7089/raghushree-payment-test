<?php
/**
 * About Page Portfolio, Visual Product Story, Industrial Image Showcase &
 * Interactive 180° Conversion Flip Cards.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$catalog_items = raghushree_get_catalog_items( 7 );

$about_image_cards = array(
	array(
		'slot'   => 'polymer_hdpe',
		'kicker' => __( '01 · Polymer Resins', 'raghushree' ),
		'title'  => __( 'Multi-Polymer Raw Material Portfolio', 'raghushree' ),
		'desc'   => __( 'Supplying PP, HDPE, LDPE, LLDPE, PVC, PET, and sorted recycled polymer grades across verified forms.', 'raghushree' ),
		'url'    => raghushree_get_products_url(),
	),
	array(
		'slot'   => 'home_about',
		'kicker' => __( '02 · Conversion Line Focus', 'raghushree' ),
		'title'  => __( 'Shop-Floor Equipment Compatibility', 'raghushree' ),
		'desc'   => __( 'Aligned with injection moulding, blow moulding, pipe extrusion, raffia tape, and flexible film lines.', 'raghushree' ),
		'url'    => raghushree_get_enquiry_url( array( 'enquiry_type' => 'Technical Consultation' ) ),
	),
	array(
		'slot'   => 'home_quality',
		'kicker' => __( '03 · Pre-Order Validation', 'raghushree' ),
		'title'  => __( 'Grade & Sample Evaluation', 'raghushree' ),
		'desc'   => __( 'Supporting QC and production teams with trial sample coordination before committing full commercial loads.', 'raghushree' ),
		'url'    => raghushree_get_enquiry_url( array( 'enquiry_type' => 'Sample Request' ) ),
	),
	array(
		'slot'   => 'about_supply',
		'kicker' => __( '04 · Pan-India & Export Reach', 'raghushree' ),
		'title'  => __( 'Structured Bulk Dispatch', 'raghushree' ),
		'desc'   => __( 'Organized raw material dispatch from Kanpur for Indian manufacturing clusters and international buyers.', 'raghushree' ),
		'url'    => raghushree_get_enquiry_url( array( 'enquiry_type' => 'Bulk Order Enquiry' ) ),
	),
);
?>
<!-- Material Portfolio -->
<section class="section surface--white" id="material-portfolio">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Material Portfolio', 'raghushree' ),
				'title'       => __( 'Seven Core Polymer Categories Supporting Industrial Buyers', 'raghushree' ),
				'description' => __( 'Our portfolio spans commodity polyolefins, engineering vinyls and polyesters, and recycled & reprocessed polymer grades across granules, powder, and applicable forms.', 'raghushree' ),
				'align'       => 'split',
			)
		);
		?>

		<?php if ( ! empty( $catalog_items ) ) : ?>
			<div class="grid-3">
				<?php foreach ( $catalog_items as $item ) : ?>
					<?php
					get_template_part(
						'template-parts/components/product-card',
						null,
						array( 'product' => $item )
					);
					?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- Five-Slide Scroll-Pinned Visual Product Story (Integrated on About Page) -->
<?php
get_template_part(
	'template-parts/components/visual-product-story',
	null,
	array( 'id' => 'about-visual-product-story' )
);
?>

<!-- Visual Operations & Supply Image Showcase Section -->
<section class="section surface--pearl" id="about-image-showcase">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Inside Our Supply Capabilities', 'raghushree' ),
				'title'       => __( 'Visual Overview of Material Sourcing, Conversion & Dispatch', 'raghushree' ),
				'description' => __( 'Representative views across raw material forms, downstream conversion machinery, pre-bulk verification, and commercial logistics.', 'raghushree' ),
				'align'       => 'split',
			)
		);
		?>

		<div class="showcase-img-grid">
			<?php foreach ( $about_image_cards as $acard ) : ?>
				<article class="showcase-img-card reveal">
					<div class="showcase-img-card__media">
						<?php
						raghushree_render_image(
							$acard['slot'],
							array(
								'size'     => 'raghushree-card',
								'sizes'    => '(min-width: 1200px) 300px, (min-width: 768px) 46vw, 100vw',
								'priority' => false,
							)
						);
						?>
					</div>
					<div class="showcase-img-card__body">
						<span class="sp-kicker"><?php echo esc_html( $acard['kicker'] ); ?></span>
						<h3 class="showcase-img-card__title"><?php echo esc_html( $acard['title'] ); ?></h3>
						<p class="showcase-img-card__desc"><?php echo esc_html( $acard['desc'] ); ?></p>
						<a class="showcase-img-card__link" href="<?php echo esc_url( $acard['url'] ); ?>">
							<span><?php esc_html_e( 'Learn More', 'raghushree' ); ?></span>
							<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Four-Card 180° Interactive Flip Section ("Useful detail, on the other side.") -->
<?php
get_template_part(
	'template-parts/components/flip-cards-section',
	null,
	array(
		'id'      => 'about-applications',
		'surface' => 'surface--tint',
	)
);

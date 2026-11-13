<?php
/**
 * Homepage Section 9: Quality and Supply Capabilities.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$points = array(
	array(
		'title' => __( 'Batch-Focused Material Alignment', 'raghushree' ),
		'desc'  => __( 'Matching polymer family, physical form, and processing grade to your target machine parameters.', 'raghushree' ),
	),
	array(
		'title' => __( 'Pre-Order Sample Evaluation', 'raghushree' ),
		'desc'  => __( 'Supporting trial sample requests so QC and production teams can validate processability on line.', 'raghushree' ),
	),
	array(
		'title' => __( 'Bulk & Scheduled Supply', 'raghushree' ),
		'desc'  => __( 'Coordinating full-load, bulk, and repeat industrial dispatches for manufacturing continuity.', 'raghushree' ),
	),
	array(
		'title' => __( 'Domestic & Export Documentation', 'raghushree' ),
		'desc'  => __( 'Structured invoicing, packing coordination, and commercial support for Indian and export buyers.', 'raghushree' ),
	),
);
?>
<section class="section surface--white" id="quality-and-supply">
	<div class="container">
		<div class="split-layout split-layout--reverse">
			<div class="split-layout__content">
				<div class="eyebrow"><?php esc_html_e( 'Quality & Supply Capabilities', 'raghushree' ); ?></div>
				<h2><?php esc_html_e( 'Consistent Material Discipline & Practical Supply Execution', 'raghushree' ); ?></h2>
				<p>
					<?php esc_html_e( 'In plastic manufacturing, raw material consistency directly affects cycle times, melt stability, surface finish, and reject rates. Raghushree prioritizes transparent specification dialogue before every order is finalized.', 'raghushree' ); ?>
				</p>

				<div class="capability-points" style="margin-bottom: var(--space-5);">
					<?php foreach ( $points as $pt ) : ?>
						<div class="capability-point" style="background-color: var(--pearl);">
							<h3 class="capability-point__title"><?php echo esc_html( $pt['title'] ); ?></h3>
							<p><?php echo esc_html( $pt['desc'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>

				<div>
					<a class="btn btn--primary" href="<?php echo esc_url( raghushree_get_enquiry_url( array( 'enquiry_type' => 'Technical Consultation' ) ) ); ?>">
						<span><?php esc_html_e( 'Discuss Technical Requirements', 'raghushree' ); ?></span>
						<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>
			</div>

			<div class="split-layout__media">
				<?php
				raghushree_render_image(
					'home_quality',
					array(
						'sizes'    => '(min-width: 1200px) 580px, (min-width: 900px) 46vw, 100vw',
						'priority' => false,
					)
				);
				?>
			</div>
		</div>
	</div>
</section>

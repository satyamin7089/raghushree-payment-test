<?php
/**
 * About Page Sections 6–8: Quality Commitment, Supply & Distribution, Technical Support.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tech_support = array(
	array(
		'icon'  => 'settings',
		'title' => __( 'Process & Grade Guidance', 'raghushree' ),
		'desc'  => __( 'Helping production managers and R&D teams identify suitable polymer grades for injection, blow moulding, film, or extrusion.', 'raghushree' ),
	),
	array(
		'icon'  => 'file-text',
		'title' => __( 'Sample Evaluation Requests', 'raghushree' ),
		'desc'  => __( 'Coordinating material samples for machine trials and quality control verification before committing to full order volumes.', 'raghushree' ),
	),
	array(
		'icon'  => 'layers',
		'title' => __( 'Form & Blending Consultation', 'raghushree' ),
		'desc'  => __( 'Advising buyers on available physical forms (granules vs powder) and reprocessed/recycled polymer suitability.', 'raghushree' ),
	),
);
?>
<!-- Section 6: Quality Commitment -->
<section class="section surface--white" id="quality-commitment">
	<div class="container">
		<div class="split-layout">
			<div class="split-layout__content">
				<div class="eyebrow"><?php esc_html_e( 'Quality Commitment', 'raghushree' ); ?></div>
				<h2><?php esc_html_e( 'Focused on Processability, Cleanliness & Specification Clarity', 'raghushree' ); ?></h2>
				<p>
					<?php esc_html_e( 'Industrial converters rely on predictable melt behaviour, clean granulation, and honest grade classification to protect tooling and maintain output targets.', 'raghushree' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Whether supplying primary polymer categories or reprocessed and recycled grades, Raghushree emphasizes clear pre-sale specification review, batch transparency, and alignment with your quality control team\'s expectations.', 'raghushree' ); ?>
				</p>
				<div style="margin-top: var(--space-5);">
					<a class="btn btn--primary" href="<?php echo esc_url( raghushree_get_enquiry_url( array( 'enquiry_type' => 'Technical Consultation' ) ) ); ?>">
						<span><?php esc_html_e( 'Discuss Your Requirements', 'raghushree' ); ?></span>
						<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>
			</div>
			<div class="split-layout__media">
				<?php
				raghushree_render_image(
					'about_quality',
					array(
						'sizes'    => '(min-width: 1200px) 560px, (min-width: 900px) 46vw, 100vw',
						'priority' => false,
					)
				);
				?>
			</div>
		</div>
	</div>
</section>

<!-- Section 7: Supply and Distribution Capabilities -->
<section class="section surface--pearl" id="supply-capabilities">
	<div class="container">
		<div class="split-layout split-layout--reverse">
			<div class="split-layout__content">
				<div class="eyebrow"><?php esc_html_e( 'Supply & Distribution Capabilities', 'raghushree' ); ?></div>
				<h2><?php esc_html_e( 'Organized Raw Material Dispatch for Bulk & Recurring Orders', 'raghushree' ); ?></h2>
				<p>
					<?php esc_html_e( 'From our commercial base in Kanpur, Uttar Pradesh, we coordinate raw material supply for manufacturing plants, contract moulders, and regional dealers across India as well as export buyers.', 'raghushree' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Packaging formats, dispatch timelines, and minimum order quantities are confirmed transparently at the quotation stage based on the selected polymer grade and destination.', 'raghushree' ); ?>
				</p>
				<div style="margin-top: var(--space-5);">
					<a class="btn btn--primary" href="<?php echo esc_url( raghushree_get_enquiry_url( array( 'enquiry_type' => 'Bulk Order Enquiry' ) ) ); ?>">
						<span><?php esc_html_e( 'Enquire About Bulk Supply', 'raghushree' ); ?></span>
						<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>
			</div>
			<div class="split-layout__media">
				<?php
				raghushree_render_image(
					'about_supply',
					array(
						'sizes'    => '(min-width: 1200px) 560px, (min-width: 900px) 46vw, 100vw',
						'priority' => false,
					)
				);
				?>
			</div>
		</div>
	</div>
</section>

<!-- Section 8: Technical Support -->
<section class="section surface--white" id="technical-support">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Technical & Procurement Support', 'raghushree' ),
				'title'       => __( 'Collaborating with Production, R&D, QC & Purchasing Teams', 'raghushree' ),
				'description' => __( 'Selecting the right polymer grade reduces machine adjustments and trial waste. Our team supports technical and commercial stakeholders throughout evaluation.', 'raghushree' ),
			)
		);
		?>
		<div class="grid-3">
			<?php foreach ( $tech_support as $ts ) : ?>
				<?php
				get_template_part(
					'template-parts/components/feature-card',
					null,
					array(
						'icon'        => $ts['icon'],
						'title'       => $ts['title'],
						'description' => $ts['desc'],
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

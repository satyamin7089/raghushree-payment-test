<?php
/**
 * Homepage Section 5: Grades and Material Forms.
 *
 * Explicitly avoids assuming every grade or form applies to every polymer.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$forms = array(
	array(
		'icon'  => 'layers',
		'meta'  => __( 'Material Grade', 'raghushree' ),
		'title' => __( 'Reprocessed Grades', 'raghushree' ),
		'desc'  => __( 'Carefully processed polymer grades suitable for industrial injection moulding, extrusion, utility goods, conduits, and cost-sensitive manufacturing lines.', 'raghushree' ),
	),
	array(
		'icon'  => 'shield',
		'meta'  => __( 'Material Grade', 'raghushree' ),
		'title' => __( 'Recycled Grades', 'raghushree' ),
		'desc'  => __( 'Sorted and reprocessed recycled polymer materials supporting sustainable manufacturing targets across compatible moulding, pipe, and secondary packaging uses.', 'raghushree' ),
	),
	array(
		'icon'  => 'box',
		'meta'  => __( 'Physical Form', 'raghushree' ),
		'title' => __( 'Plastic Granules', 'raghushree' ),
		'desc'  => __( 'Uniform pelletized polymer granules engineered for consistent hopper feeding, screw plastification, and stable melt flow in moulding and extrusion machinery.', 'raghushree' ),
	),
	array(
		'icon'  => 'settings',
		'meta'  => __( 'Physical Form', 'raghushree' ),
		'title' => __( 'Plastic Powder & Applicable Forms', 'raghushree' ),
		'desc'  => __( 'Pulverized plastic powder and specialized material forms supplied for applicable polymers and processes such as rotational moulding, compounding, or coating.', 'raghushree' ),
	),
);
?>
<section class="section surface--tint" id="grades-and-forms">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Grades & Physical Forms', 'raghushree' ),
				'title'       => __( 'Polymer Grades and Material Forms Matched to Your Process', 'raghushree' ),
				'description' => __( 'Different thermoplastics and processing lines demand specific melt flow behaviour and physical forms. We supply verified grades and forms tailored to industrial production requirements.', 'raghushree' ),
			)
		);
		?>

		<div class="grid-4">
			<?php foreach ( $forms as $f ) : ?>
				<?php
				get_template_part(
					'template-parts/components/feature-card',
					null,
					array(
						'icon'        => $f['icon'],
						'meta'        => $f['meta'],
						'title'       => $f['title'],
						'description' => $f['desc'],
					)
				);
				?>
			<?php endforeach; ?>
		</div>

		<div class="forms-notice">
			<div>
				<strong><?php esc_html_e( 'Important Availability Note:', 'raghushree' ); ?></strong>
				<span><?php esc_html_e( 'Not every grade or physical form is produced or stocked for every polymer category simultaneously. Please confirm exact grade and form availability with our sales desk.', 'raghushree' ); ?></span>
			</div>
			<a class="btn btn--primary btn--sm" href="<?php echo esc_url( raghushree_get_enquiry_url( array( 'enquiry_type' => 'Material Availability' ) ) ); ?>">
				<span><?php esc_html_e( 'Check Grade Availability', 'raghushree' ); ?></span>
				<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
	</div>
</section>

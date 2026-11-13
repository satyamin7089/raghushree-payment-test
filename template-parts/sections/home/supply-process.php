<?php
/**
 * Homepage Section 10: Manufacturing / Supply Process (5-Step Workflow).
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$steps = array(
	array(
		'num'   => '01',
		'title' => __( 'Enquiry', 'raghushree' ),
		'desc'  => __( 'Submit your polymer requirement via our RFQ form, email, phone, or WhatsApp with your target material and volume.', 'raghushree' ),
	),
	array(
		'num'   => '02',
		'title' => __( 'Requirement Understanding', 'raghushree' ),
		'desc'  => __( 'Our team reviews your processing method (moulding, extrusion, film), target end-use, and delivery destination.', 'raghushree' ),
	),
	array(
		'num'   => '03',
		'title' => __( 'Grade Selection', 'raghushree' ),
		'desc'  => __( 'We confirm currently available polymer grades, physical forms (granules or powder), and sample options if required.', 'raghushree' ),
	),
	array(
		'num'   => '04',
		'title' => __( 'Quotation', 'raghushree' ),
		'desc'  => __( 'Receive a clear commercial quotation detailing verified grade specification, packaging format, and dispatch terms.', 'raghushree' ),
	),
	array(
		'num'   => '05',
		'title' => __( 'Order & Supply', 'raghushree' ),
		'desc'  => __( 'Upon confirmation, orders are scheduled, packed, and dispatched smoothly for domestic or international delivery.', 'raghushree' ),
	),
);
?>
<section class="section surface--ink" id="supply-process">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Procurement Workflow', 'raghushree' ),
				'title'       => __( 'Streamlined Five-Step Material Enquiry & Supply Process', 'raghushree' ),
				'description' => __( 'From initial technical specification review to commercial quotation and dispatch, our workflow keeps procurement straightforward and transparent.', 'raghushree' ),
			)
		);
		?>

		<div class="process-grid process-grid--5">
			<?php foreach ( $steps as $step ) : ?>
				<div class="process-card reveal">
					<span class="process-card__step"><?php echo esc_html( $step['num'] ); ?></span>
					<h3 class="process-card__title"><?php echo esc_html( $step['title'] ); ?></h3>
					<p><?php echo esc_html( $step['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

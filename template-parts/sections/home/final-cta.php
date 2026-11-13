<?php
/**
 * Homepage Section 12: Final CTA and Enquiry.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="section surface--white" id="home-final-cta">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/cta-banner',
			null,
			array(
				'eyebrow'     => __( 'Ready to Source Polymer Raw Materials?', 'raghushree' ),
				'title'       => __( 'Request a Quotation or Discuss Your Polymer Grade Specification', 'raghushree' ),
				'description' => __( 'Connect directly with Raghushree Plast Products Pvt. Ltd. for bulk order quotations, current grade and form availability, sample requests, or distributor enquiries.', 'raghushree' ),
			)
		);
		?>
	</div>
</section>

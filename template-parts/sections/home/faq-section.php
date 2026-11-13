<?php
/**
 * Homepage Section 11: Frequently Asked Questions.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$home_faqs = array(
	array(
		'question' => __( 'Which plastic raw materials does Raghushree supply?', 'raghushree' ),
		'answer'   => __( 'Raghushree supplies thermoplastic raw materials across Polypropylene (PP), High-Density Polyethylene (HDPE), Low-Density Polyethylene (LDPE), Linear Low-Density Polyethylene (LLDPE), Polyvinyl Chloride (PVC), Polyethylene Terephthalate (PET), and recycled & reprocessed polymers.', 'raghushree' ),
	),
	array(
		'question' => __( 'Are all polymer categories available in every grade and physical form at all times?', 'raghushree' ),
		'answer'   => __( 'No. Specific grades (including reprocessed or recycled grades) and forms (such as plastic granules or plastic powder) vary by polymer category and current batch supply. Please contact our sales team to verify exact availability for your application.', 'raghushree' ),
	),
	array(
		'question' => __( 'Can industrial buyers request material samples before placing a bulk order?', 'raghushree' ),
		'answer'   => __( 'Yes. Production, R&D, and quality control teams can submit a sample request specifying their polymer, processing method, and trial requirement so we can coordinate appropriate evaluation material.', 'raghushree' ),
	),
	array(
		'question' => __( 'Does Raghushree handle both domestic Indian orders and international export enquiries?', 'raghushree' ),
		'answer'   => __( 'Yes. India is our core market, and we also welcome export enquiries from international manufacturers, converters, and plastic raw material importers.', 'raghushree' ),
	),
	array(
		'question' => __( 'How can plastic raw material distributors or dealers partner with Raghushree?', 'raghushree' ),
		'answer'   => __( 'Distributors and dealers can submit a dedicated Distributor Enquiry through our Contact page detailing their target territory, polymer categories of interest, and recurring volume expectations.', 'raghushree' ),
	),
);
?>
<section class="section surface--pearl" id="home-faqs">
	<div class="container--narrow">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Frequently Asked Questions', 'raghushree' ),
				'title'       => __( 'Common Questions from Industrial Polymer Buyers', 'raghushree' ),
				'description' => __( 'Clear answers regarding our polymer categories, grade verification, sample requests, bulk procurement, and export enquiries.', 'raghushree' ),
				'align'       => 'center',
			)
		);

		get_template_part(
			'template-parts/components/faq',
			null,
			array(
				'faqs'      => $home_faqs,
				'id_prefix' => 'home-faq',
			)
		);
		?>
	</div>
</section>

<?php
/**
 * Template Name: Contact Page
 * Template Post Type: page
 *
 * Contact & Industrial RFQ Page — 9 Meaningful Sections + 11-Field Secure Form.
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
	// Sections 1–3: Contact Hero, Verified Contact Information & 11-Field Enquiry Form
	get_template_part( 'template-parts/sections/contact/hero-info-form' );

	// Sections 4–9: Material Enquiry Options, Supported Enquiry Types, Direct Phone/WhatsApp, Location & Coverage, FAQs, Final CTA
	get_template_part( 'template-parts/sections/contact/options-channels-faq' );
	?>
</main>

<?php
get_footer();

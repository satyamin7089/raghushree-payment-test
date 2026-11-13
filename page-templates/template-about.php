<?php
/**
 * Template Name: About Page
 * Template Post Type: page
 *
 * About Raghushree — 12 Meaningful Industrial Sections.
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
	// Sections 1–3: About Hero, Company Overview, Business Approach
	get_template_part( 'template-parts/sections/about/hero-overview' );

	// Sections 4–5: Material Portfolio & Manufacturing Applications
	get_template_part( 'template-parts/sections/about/portfolio-applications' );

	// Sections 6–8: Quality Commitment, Supply & Distribution, Technical Support
	get_template_part( 'template-parts/sections/about/quality-supply-tech' );

	// Sections 9–12: Markets & Global Reach, Why Choose Raghushree, Customer Enquiry Process, Final CTA
	get_template_part( 'template-parts/sections/about/markets-why-process' );
	?>
</main>

<?php
get_footer();

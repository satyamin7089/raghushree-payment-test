<?php
/**
 * Homepage Template — Image-first consumer layout.
 *
 * Section order: hero → 3 × 3 packet grid (with "Explore All") → centre-focused
 * product coverflow → the remaining industrial proof sections. Content, tone and
 * styling of the existing sections are unchanged.
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
	// 1. Hero Section
	get_template_part( 'template-parts/sections/home/hero' );

	// 2. Shop the Polymer Packets — 3 × 3 image-first grid + Explore All
	get_template_part( 'template-parts/sections/home/product-categories' );

	// 3. Product Showcase — centre-focused coverflow with blurred side slides
	get_template_part(
		'template-parts/components/product-coverflow',
		null,
		array( 'id' => 'home-product-showcase' )
	);

	// 4. Business Strength / Trust Bar
	get_template_part( 'template-parts/sections/home/trust-bar' );

	// 5. About Raghushree
	get_template_part( 'template-parts/sections/home/about-preview' );

	// 6. Five-Slide Scroll-Pinned Visual Product Story
	get_template_part(
		'template-parts/components/visual-product-story',
		null,
		array( 'id' => 'home-visual-product-story' )
	);

	// 7. Grades and Material Forms
	get_template_part( 'template-parts/sections/home/grades-forms' );

	// 8. Manufacturing Applications
	get_template_part( 'template-parts/sections/home/applications' );

	// 9. Why Choose Raghushree
	get_template_part( 'template-parts/sections/home/why-choose' );

	// 10. Industries and Customers Served
	get_template_part( 'template-parts/sections/home/industries-served' );

	// 11. Quality and Supply Capabilities
	get_template_part( 'template-parts/sections/home/quality-supply' );

	// 12. Manufacturing / Supply Process
	get_template_part( 'template-parts/sections/home/supply-process' );

	// 13. Frequently Asked Questions
	get_template_part( 'template-parts/sections/home/faq-section' );

	// 14. Final CTA and Enquiry
	get_template_part( 'template-parts/sections/home/final-cta' );
	?>
</main>

<?php
get_footer();

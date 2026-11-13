<?php
/**
 * Homepage Template — Exactly 12 Meaningful Industrial Sections.
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

	// 2. Business Strength / Trust Bar
	get_template_part( 'template-parts/sections/home/trust-bar' );

	// 3. About Raghushree
	get_template_part( 'template-parts/sections/home/about-preview' );

	// 4. Five-Slide Scroll-Pinned Visual Product Story
	get_template_part(
		'template-parts/components/visual-product-story',
		null,
		array( 'id' => 'home-visual-product-story' )
	);

	// 5. Product Categories
	get_template_part( 'template-parts/sections/home/product-categories' );

	// 5. Grades and Material Forms
	get_template_part( 'template-parts/sections/home/grades-forms' );

	// 6. Manufacturing Applications
	get_template_part( 'template-parts/sections/home/applications' );

	// 7. Why Choose Raghushree
	get_template_part( 'template-parts/sections/home/why-choose' );

	// 8. Industries and Customers Served
	get_template_part( 'template-parts/sections/home/industries-served' );

	// 9. Quality and Supply Capabilities
	get_template_part( 'template-parts/sections/home/quality-supply' );

	// 10. Manufacturing / Supply Process
	get_template_part( 'template-parts/sections/home/supply-process' );

	// 11. Frequently Asked Questions
	get_template_part( 'template-parts/sections/home/faq-section' );

	// 12. Final CTA and Enquiry
	get_template_part( 'template-parts/sections/home/final-cta' );
	?>
</main>

<?php
get_footer();

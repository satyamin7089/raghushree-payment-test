<?php
/**
 * Dynamic Single Product Detail Template (`products` CPT).
 *
 * Reusable across PP, HDPE, LDPE, LLDPE, PVC, PET, and Recycled Polymers.
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
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/sections/product/detail-content' );
	endwhile;
	?>
</main>

<?php
get_footer();

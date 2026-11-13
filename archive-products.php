<?php
/**
 * Native Custom Post Type Archive Template for `products` (`/products/`).
 *
 * Also handles taxonomy archives (`polymer_category`, `material_grade`, `material_form`)
 * with unified design system and zero duplicated template markup.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main-content">
	<?php get_template_part( 'template-parts/sections/product/listing-content' ); ?>
</main>

<?php
get_footer();

<?php
/**
 * Template Name: Products Listing
 * Template Post Type: page
 *
 * Optional Page Template sharing the exact same dynamic catalog implementation
 * as `archive-products.php`.
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

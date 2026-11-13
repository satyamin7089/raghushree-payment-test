<?php
/**
 * Template Name: Product Detail
 * Template Post Type: products, page
 *
 * Optional Page/Post Template wrapper sharing `detail-content.php`.
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

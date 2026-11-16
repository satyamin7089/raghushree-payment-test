<?php
/**
 * Polymer Category Grid — image-led category cards.
 *
 * Sits directly after the product coverflow on the homepage and the About page
 * so the category navigation that used to live above the slider is still
 * available, now as photography-first cards. Cards come from the
 * `polymer_category` taxonomy, in the order of the requested catalog.
 *
 * Args:
 * - id (string)          Optional section anchor id.
 * - eyebrow (string)     Section eyebrow.
 * - title (string)       Section heading.
 * - description (string) Section description.
 * - surface (string)     Surface class (default: surface--white).
 * - limit (int)          Maximum category cards (default: 9).
 * - page (string)        Image scope: 'home' (default) or 'about'. Every page
 *                        uses its own category images, never another page's.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args        = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$section_id  = ! empty( $args['id'] ) ? sanitize_html_class( $args['id'] ) : 'polymer-categories';
$eyebrow     = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : __( 'Product Categories', 'raghushree' );
$title       = isset( $args['title'] ) ? (string) $args['title'] : __( 'Browse Polymer & Pipe Categories', 'raghushree' );
$description = isset( $args['description'] ) ? (string) $args['description'] : __( 'Open any category to see the granules, resins, pipes and hoses that sit inside it, with packet prices before GST.', 'raghushree' );
$surface     = isset( $args['surface'] ) ? (string) $args['surface'] : 'surface--white';
$limit       = isset( $args['limit'] ) ? absint( $args['limit'] ) : 9;
$limit       = $limit > 0 ? $limit : 9;
$page_scope  = ( isset( $args['page'] ) && 'about' === $args['page'] ) ? 'about' : 'home';

$cards = array();

// 1. Preferred order: the requested catalog definitions.
$definitions = function_exists( 'raghushree_catalog_definitions' ) ? raghushree_catalog_definitions() : array();
foreach ( $definitions as $slug => $definition ) {
	$term = get_term_by( 'slug', $slug, 'polymer_category' );
	if ( ! ( $term instanceof WP_Term ) || 0 === (int) $term->count ) {
		continue;
	}
	$term_link = get_term_link( $term );
	if ( is_wp_error( $term_link ) ) {
		continue;
	}
	$cards[] = array(
		'name'  => $term->name,
		'url'   => $term_link,
		'count' => (int) $term->count,
		'slot'  => raghushree_category_image_slot( $slug, isset( $definition['type'] ) ? $definition['type'] : 'polymer', $page_scope ),
	);
}

// 2. Fallback: any published category that already holds products.
if ( empty( $cards ) ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'polymer_category',
			'hide_empty' => true,
			'number'     => $limit,
			'orderby'    => 'name',
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$term_link = get_term_link( $term );
			if ( is_wp_error( $term_link ) ) {
				continue;
			}
			$cards[] = array(
				'name'  => $term->name,
				'url'   => $term_link,
				'count' => (int) $term->count,
				'slot'  => raghushree_category_image_slot( $term->slug, 'polymer', $page_scope ),
			);
		}
	}
}

$cards = array_slice( $cards, 0, $limit );

if ( empty( $cards ) ) {
	return;
}
?>
<section class="section <?php echo esc_attr( $surface ); ?>" id="<?php echo esc_attr( $section_id ); ?>">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => $eyebrow,
				'title'       => $title,
				'description' => $description,
				'align'       => 'split',
			)
		);
		?>

		<div class="cat-grid">
			<?php foreach ( $cards as $card ) : ?>
				<a class="cat-tile reveal" href="<?php echo esc_url( $card['url'] ); ?>">
					<?php
					raghushree_render_image(
						$card['slot'],
						array(
							'size'     => 'raghushree-card',
							'sizes'    => '(min-width: 1200px) 400px, (min-width: 900px) 32vw, (min-width: 600px) 46vw, 92vw',
							'priority' => false,
							'alt'      => $card['name'],
						)
					);
					?>
					<span class="cat-tile__scrim" aria-hidden="true"></span>
					<span class="cat-tile__body">
						<span class="cat-tile__name"><?php echo esc_html( $card['name'] ); ?></span>
						<span class="cat-tile__count">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: number of products in the category. */
									_n( '%s product', '%s products', $card['count'], 'raghushree' ),
									number_format_i18n( $card['count'] )
								)
							);
							?>
						</span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

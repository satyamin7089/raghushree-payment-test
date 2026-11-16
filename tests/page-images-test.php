<?php
/**
 * Page-scoped image slot smoke tests.
 *
 * Verifies that:
 * - every registry entry is complete and lives in a declared Customizer group;
 * - the home page and the About page never use the same image slot;
 * - the shared components (visual product story, application flip cards,
 *   category cards) resolve page-specific slots at render time;
 * - the Customizer creates one media control per visible slot in the right
 *   page section, and legacy hidden slots are skipped.
 *
 * Run with: php tests/page-images-test.php
 */

define( 'ABSPATH', __DIR__ );

// Any notice or warning must fail the run: the theme must render cleanly.
set_error_handler(
	function ( $severity, $message, $file, $line ) {
		throw new ErrorException( $message, 0, $severity, $file, $line );
	}
);

// ---------------------------------------------------------------------------
// Minimal WordPress stubs.
// ---------------------------------------------------------------------------

function __( $text, $domain = null ) { return $text; }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function esc_url( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function esc_url_raw( $text ) { return (string) $text; }
function esc_html_e( $text, $domain = null ) { echo esc_html( $text ); }
function esc_attr_e( $text, $domain = null ) { echo esc_attr( $text ); }
function _n( $single, $plural, $number, $domain = null ) { return 1 === (int) $number ? $single : $plural; }
function number_format_i18n( $number ) { return (string) $number; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_html_class( $value ) { return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $value ); }
function wp_parse_args( $args, $defaults = array() ) { return array_merge( $defaults, (array) $args ); }
function add_query_arg( $args, $url = '' ) { return $url . '?' . http_build_query( (array) $args ); }
function add_action( ...$args ) {}
function get_template_directory() { return dirname( __DIR__ ); }
function get_template_directory_uri() { return 'https://example.test/wp-content/themes/raghushree'; }
function get_post_thumbnail_id( $post_id = 0 ) { return 0; }
function has_post_thumbnail( $post_id = 0 ) { return false; }
function get_the_post_thumbnail_url( $post_id = 0, $size = '' ) { return ''; }
function is_wp_error( $value ) { return false; }
function get_term_link( $term ) { return 'https://example.test/category/' . $term->slug . '/'; }
function get_term_by( $field, $value, $taxonomy = '' ) {
	return isset( $GLOBALS['terms'][ $value ] ) ? $GLOBALS['terms'][ $value ] : false;
}
function get_terms( $args = array() ) { return array_values( $GLOBALS['terms'] ); }
function get_template_part( $slug, $name = null, $args = array() ) {
	// The section heading is not under test; skip it so the assertions only see images.
	if ( 'template-parts/components/section-heading' === $slug ) {
		return;
	}
}

// Slot resolution stubs: every theme_mod resolves to a real attachment id, and
// wp_get_attachment_image echoes the slot key that was requested, so a wrong
// slot name in a template shows up directly in the rendered HTML.
function get_theme_mod( $key, $default = false ) {
	if ( ! empty( $GLOBALS['no_media'] ) ) {
		return $default;
	}
	$prefix = 'raghushree_media_';
	if ( 0 === strpos( $key, $prefix ) ) {
		$slot = substr( $key, strlen( $prefix ) );
		if ( isset( $GLOBALS['slot_index'][ $slot ] ) ) {
			return $GLOBALS['slot_index'][ $slot ] + 1;
		}
		return 0;
	}
	return $default;
}
function wp_get_attachment_image( $attachment_id, $size = '', $icon = false, $attr = array() ) {
	$index = (int) $attachment_id - 1;
	$slot  = isset( $GLOBALS['slots'][ $index ] ) ? $GLOBALS['slots'][ $index ] : 'UNKNOWN_SLOT';
	return '<img data-slot="' . esc_attr( $slot ) . '" />';
}
function wp_get_attachment_image_url( $attachment_id, $size = '' ) { return ''; }

class WP_Post {
	public $ID        = 0;
	public $post_name = '';
}
class WP_Term {
	public $term_id = 0;
	public $slug    = '';
	public $name    = '';
	public $count   = 0;
	public function __construct( $slug, $name, $count ) {
		$this->slug  = $slug;
		$this->name  = $name;
		$this->count = $count;
	}
}

function raghushree_get_catalog_items( $limit = 7 ) {
	$items = array();
	foreach ( array( 'polypropylene-pp', 'high-density-polyethylene-hdpe', 'polyvinyl-chloride-pvc', 'recycled-reprocessed-polymers' ) as $i => $slug ) {
		$items[] = array(
			'id'        => $i + 1,
			'permalink' => 'https://example.test/product/' . $slug . '/',
			'title'     => $slug,
		);
	}
	return array_slice( $items, 0, (int) $limit );
}
function get_post( $post_id ) {
	$post            = new WP_Post();
	$post->ID        = (int) $post_id;
	$post->post_name = 'product-' . (int) $post_id;
	return $post;
}
// URL helpers live in inc/helpers.php; only their WordPress dependencies are stubbed.
function home_url( $path = '/' ) { return 'https://example.test' . $path; }
function get_page_by_path( $slug ) { return null; }
function get_permalink( $post_id = 0 ) { return 'https://example.test/?p=' . (int) $post_id; }
function get_post_type_archive_link( $post_type ) { return 'https://example.test/products/'; }
function sanitize_email( $email ) { return (string) $email; }

// Customizer stubs.
class WP_Customize_Manager {
	public $sections = array();
	public $settings = array();
	public $controls = array();
	public function add_section( $id, $args = array() ) { $this->sections[ $id ] = $args; }
	public function add_setting( $id, $args = array() ) { $this->settings[ $id ] = $args; }
	public function add_control( $control ) { $this->controls[] = $control; }
}
class WP_Customize_Media_Control {
	public $id;
	public $section;
	public $label;
	public function __construct( $manager, $id, $args = array() ) {
		$this->id      = $id;
		$this->section = isset( $args['section'] ) ? $args['section'] : '';
		$this->label   = isset( $args['label'] ) ? $args['label'] : '';
	}
}

// ---------------------------------------------------------------------------
// Load the theme code under test.
// ---------------------------------------------------------------------------

require __DIR__ . '/../inc/image-placeholders.php';
require __DIR__ . '/../inc/helpers.php';
require __DIR__ . '/../inc/catalog-v2.php';
require __DIR__ . '/../inc/setup.php';

function check( $condition, $message ) {
	if ( ! $condition ) {
		throw new Exception( $message );
	}
}

$GLOBALS['slots']      = array_keys( raghushree_get_placeholder_registry() );
$GLOBALS['slot_index'] = array_flip( $GLOBALS['slots'] );
$registry              = raghushree_get_placeholder_registry();
$groups                = raghushree_get_placeholder_groups();

// 1. Registry integrity.
check( count( $GLOBALS['slots'] ) === count( array_unique( $GLOBALS['slots'] ) ), 'slot keys are unique' );
$labels = array();
foreach ( $registry as $slot_key => $slot ) {
	foreach ( array( 'group', 'base_url', 'width', 'height', 'alt', 'label' ) as $required ) {
		check( isset( $slot[ $required ] ), "slot $slot_key has $required" );
	}
	check( isset( $groups[ $slot['group'] ] ), "slot $slot_key belongs to a declared group" );
	check( (int) $slot['width'] > 0 && (int) $slot['height'] > 0, "slot $slot_key has dimensions" );
	check( 0 === strpos( $slot['base_url'], 'https://images.unsplash.com/' ) || ! empty( $slot['local_file'] ), "slot $slot_key has a real image source" );
	check( ! isset( $labels[ $slot['label'] ] ), "slot label is unique: {$slot['label']}" );
	$labels[ $slot['label'] ] = $slot_key;
}
foreach ( $groups as $group_key => $group ) {
	check( isset( $group['title'], $group['description'], $group['priority'] ), "group $group_key is complete" );
}

// 2. Home and About never share a slot.
$home_slots  = array();
$about_slots = array();
foreach ( $registry as $slot_key => $slot ) {
	if ( 'home' === $slot['group'] ) {
		$home_slots[] = $slot_key;
	}
	if ( 'about' === $slot['group'] ) {
		$about_slots[] = $slot_key;
	}
}
check( array() === array_intersect( $home_slots, $about_slots ), 'home and about slots are disjoint' );
check( count( $home_slots ) >= 20 && count( $about_slots ) >= 25, 'home and about each own a full image set' );

// 3. Category slot helper is page-scoped and always resolves to a real slot.
$catalog = raghushree_catalog_definitions();
$seen    = array();
foreach ( $catalog as $slug => $definition ) {
	$home_key  = raghushree_category_image_slot( $slug, $definition['type'], 'home' );
	$about_key = raghushree_category_image_slot( $slug, $definition['type'], 'about' );
	check( isset( $registry[ $home_key ] ), "home category slot exists for $slug" );
	check( isset( $registry[ $about_key ] ), "about category slot exists for $slug" );
	check( $home_key !== $about_key, "category $slug uses different home/about slots" );
	check( 0 === strpos( $home_key, 'home_cat_' ), "category $slug resolves to a home slot" );
	check( 0 === strpos( $about_key, 'about_cat_' ), "category $slug resolves to an about slot" );
	check( ! in_array( $home_key, $seen, true ), "home category slot is not repeated for $slug" );
	$seen[] = $home_key;
}
check( isset( $registry[ raghushree_category_image_slot( 'custom-lldpe-compound', 'polymer', 'about' ) ] ), 'unknown polymer slug falls back to a real slot' );
check( 'home_cat_hoses' === raghushree_category_image_slot( 'brand-new-hose', 'pipe', 'home' ), 'unknown hose slug maps to the page hose card' );
check( 'about_cat_tubing' === raghushree_category_image_slot( 'garden-water-line', 'pipe', 'about' ), 'unknown pipe slug falls back to the page tubing card' );
check( isset( $registry[ raghushree_category_image_slot( 'unknown', 'polymer', 'about' ) ] ), 'fallback slot always exists' );

// 4. Customizer wiring: one section per page group, one control per visible slot.
$customizer = new WP_Customize_Manager();
raghushree_customize_register( $customizer );
foreach ( $groups as $group_key => $group ) {
	check( isset( $customizer->sections[ 'raghushree_media_' . $group_key ] ), "Customizer section exists for $group_key" );
}
$visible_slots = 0;
foreach ( $registry as $slot_key => $slot ) {
	if ( ! empty( $slot['hidden'] ) ) {
		continue;
	}
	$visible_slots++;
	$section_id = raghushree_placeholder_section_id( $slot_key );
	check( isset( $customizer->sections[ $section_id ] ), "slot $slot_key maps to an existing section" );
	check( isset( $customizer->settings[ 'raghushree_media_' . $slot_key ] ), "setting registered for $slot_key" );
}
$media_controls = array_values(
	array_filter(
		$customizer->controls,
		function ( $control ) {
			return $control instanceof WP_Customize_Media_Control;
		}
	)
);
check( count( $media_controls ) === $visible_slots, 'one media control per visible slot (' . $visible_slots . ')' );
foreach ( $media_controls as $control ) {
	check( isset( $customizer->sections[ $control->section ] ), 'every control points at a real section' );
	check( '' !== (string) $control->label, 'every control has a label' );
}
check( ! isset( $customizer->sections['raghushree_media_section'] ), 'the old combined image section is gone' );
foreach ( array( 'app_injection', 'app_blow', 'app_extrusion', 'app_film' ) as $legacy ) {
	check( ! empty( $registry[ $legacy ]['hidden'] ), "legacy slot $legacy stays resolvable but hidden" );
	check( ! isset( $customizer->settings[ 'raghushree_media_' . $legacy ] ), "legacy slot $legacy has no control" );
}

/**
 * Render a component and return the slot keys its images used.
 *
 * @param string $file Component file.
 * @param array  $args Template args.
 * @return string[]
 */
function rendered_slots( $file, $args ) {
	$GLOBALS['args'] = $args;
	ob_start();
	include __DIR__ . '/../template-parts/components/' . $file;
	$html = ob_get_clean();

	preg_match_all( '/data-slot="([a-z0-9_]+)"/', $html, $matches );
	return $matches[1];
}

// 5. Shared components render page-specific slots.
$home_story  = rendered_slots( 'visual-product-story.php', array( 'id' => 'home-story', 'page' => 'home' ) );
$about_story = rendered_slots( 'visual-product-story.php', array( 'id' => 'about-story', 'page' => 'about' ) );
check(
	array( 'home_story_pp', 'home_story_pe', 'home_story_pvc', 'home_story_recycled', 'home_story_supply' ) === $home_story,
	'home story uses the five home story slots: ' . implode( ',', $home_story )
);
check(
	array( 'about_story_pp', 'about_story_pe', 'about_story_pvc', 'about_story_recycled', 'about_story_supply' ) === $about_story,
	'about story uses the five about story slots: ' . implode( ',', $about_story )
);

$home_apps  = rendered_slots( 'flip-cards-section.php', array( 'id' => 'home-apps', 'page' => 'home' ) );
$about_apps = rendered_slots( 'flip-cards-section.php', array( 'id' => 'about-apps', 'page' => 'about' ) );
check( array( 'home_app_injection', 'home_app_blow', 'home_app_extrusion', 'home_app_film' ) === $home_apps, 'home flip cards use home application slots' );
check( array( 'about_app_injection', 'about_app_blow', 'about_app_extrusion', 'about_app_film' ) === $about_apps, 'about flip cards use about application slots' );

// Category terms: the nine requested catalog categories, three products each.
$GLOBALS['terms'] = array();
foreach ( $catalog as $slug => $definition ) {
	$GLOBALS['terms'][ $slug ] = new WP_Term( $slug, $definition['title'], 3 );
}

$home_cards  = rendered_slots( 'category-grid.php', array( 'id' => 'home-cards', 'page' => 'home' ) );
$about_cards = rendered_slots( 'category-grid.php', array( 'id' => 'about-cards', 'page' => 'about' ) );
check( 9 === count( $home_cards ), 'home renders nine category cards, got ' . count( $home_cards ) );
check( 9 === count( $about_cards ), 'about renders nine category cards, got ' . count( $about_cards ) );
check( array() === array_intersect( $home_cards, $about_cards ), 'category cards never share a slot between pages' );
foreach ( $home_cards as $key ) {
	check( 0 === strpos( $key, 'home_cat_' ), "home category card uses a home slot ($key)" );
}
foreach ( $about_cards as $key ) {
	check( 0 === strpos( $key, 'about_cat_' ), "about category card uses an about slot ($key)" );
}
check( 9 === count( array_unique( $home_cards ) ), 'all nine home category cards have different images' );
check( 9 === count( array_unique( $about_cards ) ), 'all nine about category cards have different images' );

// 6. Call sites pass the right page scope to the shared components.
$call_sites = array(
	'front-page.php'                                        => 'home',
	'template-parts/sections/home/applications.php'         => 'home',
	'template-parts/sections/about/portfolio-applications.php' => 'about',
);
foreach ( $call_sites as $file => $expected_page ) {
	$source = file_get_contents( __DIR__ . '/../' . $file );
	check( false !== $source, "call site file readable: $file" );
	preg_match_all(
		"/get_template_part\(\s*'template-parts\/components\/(category-grid|visual-product-story|flip-cards-section)',.*?;/s",
		$source,
		$matches
	);
	check( ! empty( $matches[0] ), "call site file includes a shared image component: $file" );
	foreach ( $matches[0] as $call ) {
		check(
			1 === preg_match( "/'page'\s*=>\s*'" . $expected_page . "'/", $call ),
			"$file passes page => $expected_page to the component"
		);
	}
}

// 7. Placeholder markup is valid: single-encoded URLs, responsive srcset, alt text.
$GLOBALS['no_media'] = true;
$GLOBALS['args']     = array( 'id' => 'home-story', 'page' => 'home' );
ob_start();
include __DIR__ . '/../template-parts/components/visual-product-story.php';
$placeholder_html = ob_get_clean();
$GLOBALS['no_media'] = false;

check( false === strpos( $placeholder_html, '&amp;amp;' ), 'image URLs are escaped once, not twice' );
check( false !== strpos( $placeholder_html, 'srcset=' ), 'placeholder images render a srcset' );
check( 1 === preg_match( '/srcset="[^"]*w=480[^"]*480w/', $placeholder_html ), 'srcset lists the 480w candidate' );
check( 1 === preg_match( '/alt="Representative [^"]+"/', $placeholder_html ), 'placeholder images carry descriptive alt text' );
check( false === strpos( $placeholder_html, 'data-slot' ), 'placeholder path does not fake Media Library output' );

// 8. Every page image set is fully replaceable from the Customizer.
foreach ( array_merge( $home_story, $about_story, $home_apps, $about_apps, $home_cards, $about_cards ) as $slot_key ) {
	check( isset( $customizer->settings[ 'raghushree_media_' . $slot_key ] ), "slot $slot_key has its own Customizer setting" );
}

echo "Page image slot smoke tests passed: " . count( $registry ) . " slots, " . count( $media_controls ) . " Customizer controls, 6 page sections.\n";

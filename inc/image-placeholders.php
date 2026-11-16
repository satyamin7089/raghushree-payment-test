<?php
/**
 * Page-scoped Temporary Unsplash Image Placeholder Registry & Image Renderer.
 *
 * IMPORTANT IMAGE POLICY:
 * - Zero AI-generated images are used anywhere in this theme.
 * - Every temporary placeholder below is a genuine Unsplash photograph of
 *   industrial polymer raw materials, processing machinery, or warehousing.
 * - Placeholders are never presented as Raghushree's own plant or staff.
 *
 * EVERY PAGE OWNS ITS OWN IMAGES:
 * - Home page slots are prefixed `home_`, About page slots `about_`, the Contact
 *   page slot `contact_`, catalogue slots `catalogue`, and Header/Product
 *   fallbacks live in their own groups.
 * - A slot is never shared between two pages, so replacing the homepage story
 *   photo never changes the About page (or any product card).
 * - Customizer controls are grouped per page in Appearance -> Customize.
 * - Uploading a Featured Image on a Product/Page always wins over a slot.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customizer groups (one panel section per page).
 *
 * @return array<string, array<string, mixed>>
 */
function raghushree_get_placeholder_groups() {
	return array(
		'home'      => array(
			'title'        => __( 'Home Page Images', 'raghushree' ),
			'description'  => __( 'Every photo used on the home page only. Upload a Media Library image in any slot to replace that single placeholder; the About, Contact and Products pages are not affected.', 'raghushree' ),
			'priority'     => 35,
		),
		'about'     => array(
			'title'        => __( 'About Page Images', 'raghushree' ),
			'description'  => __( 'Every photo used on the About page only. These are separate from the home page: changing a slot here never changes the home page.', 'raghushree' ),
			'priority'     => 36,
		),
		'contact'   => array(
			'title'        => __( 'Contact Page Images', 'raghushree' ),
			'description'  => __( 'Photos used on the Contact page only.', 'raghushree' ),
			'priority'     => 37,
		),
		'catalogue' => array(
			'title'        => __( 'Catalogue & Standard Pages Images', 'raghushree' ),
			'description'  => __( 'The Products listing hero and the banner used by standard pages (single posts, default pages, search, 404).', 'raghushree' ),
			'priority'     => 38,
		),
		'header'    => array(
			'title'        => __( 'Header Images', 'raghushree' ),
			'description'  => __( 'Feature image inside the header mega menu.', 'raghushree' ),
			'priority'     => 39,
		),
		'products'  => array(
			'title'        => __( 'Product Card Fallback Images', 'raghushree' ),
			'description'  => __( 'Used by a product card, tile or slider slide only when that product has no Featured Image. Uploading a Featured Image on the product always wins.', 'raghushree' ),
			'priority'     => 40,
		),
	);
}

/**
 * Return the centralized map of temporary Unsplash placeholder images.
 *
 * Each entry: group, base_url (or local_file), width, height, alt, label,
 * and an optional `hidden` flag for legacy keys that keep resolving but are
 * no longer offered as an upload control.
 *
 * @return array<string, array<string, mixed>>
 */
function raghushree_get_placeholder_registry() {
	$photos = array(
		'facility' => 'https://images.unsplash.com/photo-1785900976288-31f0f42a7364',
		'pipes' => 'https://images.unsplash.com/photo-1779718811629-92d973f0badb',
		'injection' => 'https://images.unsplash.com/photo-1730705788367-dbd288c40ee7',
		'processing' => 'https://images.unsplash.com/photo-1777464375799-768774f30542',
		'raffia' => 'https://images.unsplash.com/photo-1675176785803-bffbbb0cd2f4',
		'automation' => 'https://images.unsplash.com/photo-1717386255773-1e3037c81788',
		'lab' => 'https://images.unsplash.com/photo-1781330173666-11f8818d6eb2',
		'warehouse' => 'https://images.unsplash.com/photo-1689942010216-dc412bb1e7a9',
		'film' => 'https://images.unsplash.com/photo-1675176785665-8d6aef6b7d8a',
		'spools' => 'https://images.unsplash.com/photo-1675176788145-928336d4ba14',
		'pet' => 'https://images.unsplash.com/photo-1536939459926-301728717817',
		'recycled' => 'https://images.unsplash.com/photo-1760307837830-5d5d31fcf25f',
		'extrusion' => 'https://images.unsplash.com/photo-1787672357830-ef4281855d47',
	);

	return array(
		'home_hero_pipes' => array(
			'group'     => 'home',
			'base_url'  => $photos['pipes'],
			'width'     => 1600,
			'height'    => 1200,
			'alt'       => 'Representative PVC irrigation and suction pipes laid out for farm water supply',
			'label'     => 'Home · Banner 1 Image — Agriculture & Irrigation Pipes',
		),
		'home_hero' => array(
			'group'       => 'home',
			'local_file'  => 'assets/images/bg-cta-3.jpg',
			'base_url'    => $photos['facility'],
			'width'       => 1600,
			'height'      => 1200,
			'alt'         => 'Representative industrial thermoplastic polymer processing and raw material production facility',
			'label'       => 'Home · Banner 2 Image — Polymer Raw Materials',
		),
		'home_about' => array(
			'group'     => 'home',
			'base_url'  => $photos['injection'],
			'width'     => 1200,
			'height'    => 900,
			'alt'       => 'Representative thermoplastic injection moulding machines in an industrial production facility',
			'label'     => 'Home · About Raghushree Split Image',
		),
		'home_quality' => array(
			'group'     => 'home',
			'base_url'  => $photos['processing'],
			'width'     => 1200,
			'height'    => 900,
			'alt'       => 'Representative plastic processing machinery with hopper dryers and polymer raw material bags',
			'label'     => 'Home · Quality & Supply Split Image',
		),
		'home_story_pp' => array(
			'group'     => 'home',
			'base_url'  => $photos['raffia'],
			'width'     => 1920,
			'height'    => 1080,
			'alt'       => 'Representative polypropylene tape and raffia extrusion winding machinery',
			'label'     => 'Home · Visual Product Story — Slide 01 (Polypropylene (PP))',
		),
		'home_story_pe' => array(
			'group'     => 'home',
			'base_url'  => $photos['film'],
			'width'     => 1920,
			'height'    => 1080,
			'alt'       => 'Representative extruded polyethylene film rolls in an industrial production hall',
			'label'     => 'Home · Visual Product Story — Slide 02 (Polyethylene Family)',
		),
		'home_story_pvc' => array(
			'group'     => 'home',
			'base_url'  => $photos['extrusion'],
			'width'     => 1920,
			'height'    => 1080,
			'alt'       => 'Representative continuous industrial extrusion and profile forming rollers',
			'label'     => 'Home · Visual Product Story — Slide 03 (PVC & PET)',
		),
		'home_story_recycled' => array(
			'group'     => 'home',
			'base_url'  => $photos['recycled'],
			'width'     => 1920,
			'height'    => 1080,
			'alt'       => 'Representative sorted polyolefin plastic feedstock prepared for recycling and reprocessing',
			'label'     => 'Home · Visual Product Story — Slide 04 (Recycled Polymers)',
		),
		'home_story_supply' => array(
			'group'     => 'home',
			'base_url'  => $photos['warehouse'],
			'width'     => 1920,
			'height'    => 1080,
			'alt'       => 'Representative industrial warehouse pallet racking and raw material logistics handling',
			'label'     => 'Home · Visual Product Story — Slide 05 (Kanpur Supply Network)',
		),
		'home_app_injection' => array(
			'group'     => 'home',
			'base_url'  => $photos['injection'],
			'width'     => 960,
			'height'    => 540,
			'alt'       => 'Representative thermoplastic injection moulding machines in an industrial production facility',
			'label'     => 'Home · Application Card — Injection Moulding',
		),
		'home_app_blow' => array(
			'group'     => 'home',
			'base_url'  => $photos['pet'],
			'width'     => 960,
			'height'    => 540,
			'alt'       => 'Representative blow-moulded clear PET packaging containers on a production lot',
			'label'     => 'Home · Application Card — Blow Moulding',
		),
		'home_app_extrusion' => array(
			'group'     => 'home',
			'base_url'  => $photos['automation'],
			'width'     => 960,
			'height'    => 540,
			'alt'       => 'Representative automated industrial manufacturing and material forming production line',
			'label'     => 'Home · Application Card — Extrusion',
		),
		'home_app_film' => array(
			'group'     => 'home',
			'base_url'  => $photos['spools'],
			'width'     => 960,
			'height'    => 540,
			'alt'       => 'Representative extruded linear low-density polyethylene spools and flexible packaging material',
			'label'     => 'Home · Application Card — Film & Packaging',
		),
		'home_cat_pp' => array(
			'group'     => 'home',
			'base_url'  => $photos['raffia'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative polypropylene tape and raffia extrusion winding machinery',
			'label'     => 'Home · Category Card — PP Granules',
		),
		'home_cat_ldpe' => array(
			'group'     => 'home',
			'base_url'  => $photos['film'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative extruded polyethylene film rolls in an industrial production hall',
			'label'     => 'Home · Category Card — LDPE Granules',
		),
		'home_cat_lldpe' => array(
			'group'     => 'home',
			'base_url'  => $photos['spools'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative extruded linear low-density polyethylene spools and flexible packaging material',
			'label'     => 'Home · Category Card — LLDPE Granules',
		),
		'home_cat_hdpe' => array(
			'group'     => 'home',
			'base_url'  => $photos['facility'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative industrial thermoplastic polymer processing and raw material production facility',
			'label'     => 'Home · Category Card — HDPE Granules',
		),
		'home_cat_pvc' => array(
			'group'     => 'home',
			'base_url'  => $photos['pipes'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative PVC irrigation, suction and garden pipes prepared for farm water supply',
			'label'     => 'Home · Category Card — PVC Granules',
		),
		'home_cat_pvc_resin' => array(
			'group'     => 'home',
			'base_url'  => $photos['lab'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative industrial testing and process control instrumentation',
			'label'     => 'Home · Category Card — PVC Resin',
		),
		'home_cat_pet' => array(
			'group'     => 'home',
			'base_url'  => $photos['pet'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative blow-moulded clear PET packaging containers on a production lot',
			'label'     => 'Home · Category Card — PET Granules & Resin',
		),
		'home_cat_recycled' => array(
			'group'     => 'home',
			'base_url'  => $photos['recycled'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative sorted polyolefin plastic feedstock prepared for recycling and reprocessing',
			'label'     => 'Home · Category Card — Recycled & Reprocessed',
		),
		'home_cat_hoses' => array(
			'group'     => 'home',
			'base_url'  => $photos['extrusion'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative continuous industrial extrusion and profile forming rollers',
			'label'     => 'Home · Category Card — Industrial Hoses',
		),
		'home_cat_suction_pipes' => array(
			'group'     => 'home',
			'base_url'  => $photos['processing'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative plastic processing machinery with hopper dryers and polymer raw material bags',
			'label'     => 'Home · Category Card — PVC Suction Pipes',
		),
		'home_cat_tubing' => array(
			'group'     => 'home',
			'base_url'  => $photos['automation'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative automated industrial manufacturing and material forming production line',
			'label'     => 'Home · Category Card — PVC Tubing',
		),
		'home_cat_lay_flat' => array(
			'group'     => 'home',
			'base_url'  => $photos['warehouse'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative industrial warehouse pallet racking and raw material logistics handling',
			'label'     => 'Home · Category Card — Lay Flat Pipes',
		),
		'about_hero' => array(
			'group'     => 'about',
			'base_url'  => $photos['automation'],
			'width'     => 1400,
			'height'    => 920,
			'alt'       => 'Representative automated industrial manufacturing and material forming production line',
			'label'     => 'About · Page Hero Image',
		),
		'about_overview' => array(
			'group'     => 'about',
			'base_url'  => $photos['processing'],
			'width'     => 1200,
			'height'    => 900,
			'alt'       => 'Representative plastic processing machinery with hopper dryers and polymer raw material bags',
			'label'     => 'About · Overview Stack Image 1',
		),
		'about_stack_2' => array(
			'group'     => 'about',
			'base_url'  => $photos['injection'],
			'width'     => 1200,
			'height'    => 900,
			'alt'       => 'Representative thermoplastic injection moulding machines in an industrial production facility',
			'label'     => 'About · Overview Stack Image 2',
		),
		'about_stack_3' => array(
			'group'     => 'about',
			'base_url'  => $photos['extrusion'],
			'width'     => 1200,
			'height'    => 900,
			'alt'       => 'Representative continuous industrial extrusion and profile forming rollers',
			'label'     => 'About · Overview Stack Image 3',
		),
		'about_stack_4' => array(
			'group'     => 'about',
			'base_url'  => $photos['pet'],
			'width'     => 1200,
			'height'    => 900,
			'alt'       => 'Representative blow-moulded clear PET packaging containers on a production lot',
			'label'     => 'About · Overview Stack Image 4',
		),
		'about_quality' => array(
			'group'     => 'about',
			'base_url'  => $photos['lab'],
			'width'     => 1200,
			'height'    => 900,
			'alt'       => 'Representative industrial testing and process control instrumentation',
			'label'     => 'About · Quality Commitment Image',
		),
		'about_supply' => array(
			'group'     => 'about',
			'base_url'  => $photos['warehouse'],
			'width'     => 1200,
			'height'    => 900,
			'alt'       => 'Representative industrial warehouse pallet racking and raw material logistics handling',
			'label'     => 'About · Supply & Distribution Image',
		),
		'about_story_pp' => array(
			'group'     => 'about',
			'base_url'  => $photos['raffia'],
			'width'     => 1920,
			'height'    => 1080,
			'alt'       => 'Representative polypropylene tape and raffia extrusion winding machinery',
			'label'     => 'About · Visual Product Story — Slide 01 (Polypropylene (PP))',
		),
		'about_story_pe' => array(
			'group'     => 'about',
			'base_url'  => $photos['film'],
			'width'     => 1920,
			'height'    => 1080,
			'alt'       => 'Representative extruded polyethylene film rolls in an industrial production hall',
			'label'     => 'About · Visual Product Story — Slide 02 (Polyethylene Family)',
		),
		'about_story_pvc' => array(
			'group'     => 'about',
			'base_url'  => $photos['extrusion'],
			'width'     => 1920,
			'height'    => 1080,
			'alt'       => 'Representative continuous industrial extrusion and profile forming rollers',
			'label'     => 'About · Visual Product Story — Slide 03 (PVC & PET)',
		),
		'about_story_recycled' => array(
			'group'     => 'about',
			'base_url'  => $photos['recycled'],
			'width'     => 1920,
			'height'    => 1080,
			'alt'       => 'Representative sorted polyolefin plastic feedstock prepared for recycling and reprocessing',
			'label'     => 'About · Visual Product Story — Slide 04 (Recycled Polymers)',
		),
		'about_story_supply' => array(
			'group'     => 'about',
			'base_url'  => $photos['spools'],
			'width'     => 1920,
			'height'    => 1080,
			'alt'       => 'Representative extruded linear low-density polyethylene spools and flexible packaging material',
			'label'     => 'About · Visual Product Story — Slide 05 (Kanpur Supply Network)',
		),
		'about_app_injection' => array(
			'group'     => 'about',
			'base_url'  => $photos['injection'],
			'width'     => 960,
			'height'    => 540,
			'alt'       => 'Representative thermoplastic injection moulding machines in an industrial production facility',
			'label'     => 'About · Application Card — Injection Moulding',
		),
		'about_app_blow' => array(
			'group'     => 'about',
			'base_url'  => $photos['pet'],
			'width'     => 960,
			'height'    => 540,
			'alt'       => 'Representative blow-moulded clear PET packaging containers on a production lot',
			'label'     => 'About · Application Card — Blow Moulding',
		),
		'about_app_extrusion' => array(
			'group'     => 'about',
			'base_url'  => $photos['pipes'],
			'width'     => 960,
			'height'    => 540,
			'alt'       => 'Representative PVC irrigation, suction and garden pipes prepared for farm water supply',
			'label'     => 'About · Application Card — Extrusion',
		),
		'about_app_film' => array(
			'group'     => 'about',
			'base_url'  => $photos['processing'],
			'width'     => 960,
			'height'    => 540,
			'alt'       => 'Representative plastic processing machinery with hopper dryers and polymer raw material bags',
			'label'     => 'About · Application Card — Film & Packaging',
		),
		'about_show_1' => array(
			'group'     => 'about',
			'base_url'  => $photos['facility'],
			'width'     => 1200,
			'height'    => 750,
			'alt'       => 'Representative industrial thermoplastic polymer processing and raw material production facility',
			'label'     => 'About · Showcase Card 1 — Multi-Polymer Portfolio',
		),
		'about_show_2' => array(
			'group'     => 'about',
			'base_url'  => $photos['injection'],
			'width'     => 1200,
			'height'    => 750,
			'alt'       => 'Representative thermoplastic injection moulding machines in an industrial production facility',
			'label'     => 'About · Showcase Card 2 — Shop-Floor Compatibility',
		),
		'about_show_3' => array(
			'group'     => 'about',
			'base_url'  => $photos['lab'],
			'width'     => 1200,
			'height'    => 750,
			'alt'       => 'Representative industrial testing and process control instrumentation',
			'label'     => 'About · Showcase Card 3 — Grade & Sample Evaluation',
		),
		'about_show_4' => array(
			'group'     => 'about',
			'base_url'  => $photos['warehouse'],
			'width'     => 1200,
			'height'    => 750,
			'alt'       => 'Representative industrial warehouse pallet racking and raw material logistics handling',
			'label'     => 'About · Showcase Card 4 — Structured Bulk Dispatch',
		),
		'about_cat_pp' => array(
			'group'     => 'about',
			'base_url'  => $photos['raffia'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative polypropylene tape and raffia extrusion winding machinery',
			'label'     => 'About · Category Card — PP Granules',
		),
		'about_cat_ldpe' => array(
			'group'     => 'about',
			'base_url'  => $photos['film'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative extruded polyethylene film rolls in an industrial production hall',
			'label'     => 'About · Category Card — LDPE Granules',
		),
		'about_cat_lldpe' => array(
			'group'     => 'about',
			'base_url'  => $photos['spools'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative extruded linear low-density polyethylene spools and flexible packaging material',
			'label'     => 'About · Category Card — LLDPE Granules',
		),
		'about_cat_hdpe' => array(
			'group'     => 'about',
			'base_url'  => $photos['facility'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative industrial thermoplastic polymer processing and raw material production facility',
			'label'     => 'About · Category Card — HDPE Granules',
		),
		'about_cat_pvc' => array(
			'group'     => 'about',
			'base_url'  => $photos['pipes'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative PVC irrigation, suction and garden pipes prepared for farm water supply',
			'label'     => 'About · Category Card — PVC Granules',
		),
		'about_cat_pvc_resin' => array(
			'group'     => 'about',
			'base_url'  => $photos['lab'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative industrial testing and process control instrumentation',
			'label'     => 'About · Category Card — PVC Resin',
		),
		'about_cat_pet' => array(
			'group'     => 'about',
			'base_url'  => $photos['pet'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative blow-moulded clear PET packaging containers on a production lot',
			'label'     => 'About · Category Card — PET Granules & Resin',
		),
		'about_cat_recycled' => array(
			'group'     => 'about',
			'base_url'  => $photos['recycled'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative sorted polyolefin plastic feedstock prepared for recycling and reprocessing',
			'label'     => 'About · Category Card — Recycled & Reprocessed',
		),
		'about_cat_hoses' => array(
			'group'     => 'about',
			'base_url'  => $photos['extrusion'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative continuous industrial extrusion and profile forming rollers',
			'label'     => 'About · Category Card — Industrial Hoses',
		),
		'about_cat_suction_pipes' => array(
			'group'     => 'about',
			'base_url'  => $photos['processing'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative plastic processing machinery with hopper dryers and polymer raw material bags',
			'label'     => 'About · Category Card — PVC Suction Pipes',
		),
		'about_cat_tubing' => array(
			'group'     => 'about',
			'base_url'  => $photos['automation'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative automated industrial manufacturing and material forming production line',
			'label'     => 'About · Category Card — PVC Tubing',
		),
		'about_cat_lay_flat' => array(
			'group'     => 'about',
			'base_url'  => $photos['warehouse'],
			'width'     => 1000,
			'height'    => 750,
			'alt'       => 'Representative industrial warehouse pallet racking and raw material logistics handling',
			'label'     => 'About · Category Card — Lay Flat Pipes',
		),
		'contact_hero' => array(
			'group'       => 'contact',
			'local_file'  => 'assets/images/bg-cta-3.jpg',
			'base_url'    => $photos['facility'],
			'width'       => 1200,
			'height'      => 800,
			'alt'         => 'Representative industrial polymer raw material facility',
			'label'       => 'Contact · Page Hero Image',
		),
		'products_hero' => array(
			'group'     => 'catalogue',
			'base_url'  => $photos['facility'],
			'width'     => 1280,
			'height'    => 800,
			'alt'       => 'Representative industrial thermoplastic polymer processing and raw material production facility',
			'label'     => 'Catalogue · Listing Page Hero Image',
		),
		'page_hero' => array(
			'group'     => 'catalogue',
			'base_url'  => $photos['injection'],
			'width'     => 1280,
			'height'    => 800,
			'alt'       => 'Representative thermoplastic injection moulding machines in an industrial production facility',
			'label'     => 'Standard Pages · Page Banner Image',
		),
		'mega_menu_feature' => array(
			'group'     => 'header',
			'base_url'  => $photos['raffia'],
			'width'     => 800,
			'height'    => 450,
			'alt'       => 'Representative polypropylene tape and raffia extrusion winding machinery',
			'label'     => 'Header · Mega Menu Feature Image',
		),
		'polymer_pp' => array(
			'group'     => 'products',
			'base_url'  => $photos['raffia'],
			'width'     => 1000,
			'height'    => 625,
			'alt'       => 'Representative polypropylene tape and raffia extrusion winding machinery',
			'label'     => 'Product Fallback — Polypropylene (PP)',
		),
		'polymer_hdpe' => array(
			'group'     => 'products',
			'base_url'  => $photos['facility'],
			'width'     => 1000,
			'height'    => 625,
			'alt'       => 'Representative industrial thermoplastic polymer processing and raw material production facility',
			'label'     => 'Product Fallback — HDPE',
		),
		'polymer_ldpe' => array(
			'group'     => 'products',
			'base_url'  => $photos['film'],
			'width'     => 1000,
			'height'    => 625,
			'alt'       => 'Representative extruded polyethylene film rolls in an industrial production hall',
			'label'     => 'Product Fallback — LDPE',
		),
		'polymer_lldpe' => array(
			'group'     => 'products',
			'base_url'  => $photos['spools'],
			'width'     => 1000,
			'height'    => 625,
			'alt'       => 'Representative extruded linear low-density polyethylene spools and flexible packaging material',
			'label'     => 'Product Fallback — LLDPE',
		),
		'polymer_pvc' => array(
			'group'     => 'products',
			'base_url'  => $photos['pipes'],
			'width'     => 1000,
			'height'    => 625,
			'alt'       => 'Representative PVC irrigation, suction and garden pipes prepared for farm water supply',
			'label'     => 'Product Fallback — PVC',
		),
		'polymer_pet' => array(
			'group'     => 'products',
			'base_url'  => $photos['pet'],
			'width'     => 1000,
			'height'    => 625,
			'alt'       => 'Representative blow-moulded clear PET packaging containers on a production lot',
			'label'     => 'Product Fallback — PET',
		),
		'polymer_recycled' => array(
			'group'     => 'products',
			'base_url'  => $photos['recycled'],
			'width'     => 1000,
			'height'    => 625,
			'alt'       => 'Representative sorted polyolefin plastic feedstock prepared for recycling and reprocessing',
			'label'     => 'Product Fallback — Recycled & Reprocessed',
		),
		'app_injection' => array(
			'group'     => 'products',
			'base_url'  => $photos['injection'],
			'width'     => 800,
			'height'    => 450,
			'alt'       => 'Representative thermoplastic injection moulding machines in an industrial production facility',
			'label'     => 'Legacy Application Image — app_injection',
			'hidden'    => true,
		),
		'app_blow' => array(
			'group'     => 'products',
			'base_url'  => $photos['processing'],
			'width'     => 800,
			'height'    => 450,
			'alt'       => 'Representative plastic processing machinery with hopper dryers and polymer raw material bags',
			'label'     => 'Legacy Application Image — app_blow',
			'hidden'    => true,
		),
		'app_extrusion' => array(
			'group'     => 'products',
			'base_url'  => $photos['extrusion'],
			'width'     => 800,
			'height'    => 450,
			'alt'       => 'Representative continuous industrial extrusion and profile forming rollers',
			'label'     => 'Legacy Application Image — app_extrusion',
			'hidden'    => true,
		),
		'app_film' => array(
			'group'     => 'products',
			'base_url'  => $photos['film'],
			'width'     => 800,
			'height'    => 450,
			'alt'       => 'Representative extruded polyethylene film rolls in an industrial production hall',
			'label'     => 'Legacy Application Image — app_film',
			'hidden'    => true,
		),
	);
}

/**
 * Resolve the Customizer section that owns a slot.
 *
 * @param string $slot_key Registry key.
 * @return string Section id.
 */
function raghushree_placeholder_section_id( $slot_key ) {
	$registry = raghushree_get_placeholder_registry();
	$groups   = raghushree_get_placeholder_groups();
	$group    = isset( $registry[ $slot_key ]['group'] ) ? $registry[ $slot_key ]['group'] : 'home';

	if ( ! isset( $groups[ $group ] ) ) {
		$group = 'home';
	}

	return 'raghushree_media_' . $group;
}

/**
 * Build an optimized Unsplash URL with automatic modern format negotiation (WebP/AVIF).
 *
 * @param string $base_url Base Unsplash photo URL without query string.
 * @param int    $width    Target pixel width.
 * @param int    $height   Optional target pixel height.
 * @return string
 */
function raghushree_build_unsplash_url( $base_url, $width = 1000, $height = 0 ) {
	$args = array(
		'auto' => 'format',
		'fit'  => 'crop',
		'w'    => absint( $width ),
		'q'    => 80,
	);
	if ( $height > 0 ) {
		$args['h'] = absint( $height );
	}
	return add_query_arg( $args, $base_url );
}

/**
 * Return the resolved image URL for Open Graph / Schema / templates.
 *
 * @param string $slot_key Placeholder key.
 * @param int    $post_id  Optional post ID to check Featured Image first.
 * @return string
 */
function raghushree_get_image_url( $slot_key, $post_id = 0 ) {
	if ( $post_id && has_post_thumbnail( $post_id ) ) {
		$featured_url = get_the_post_thumbnail_url( $post_id, 'raghushree-hero' );
		if ( $featured_url ) {
			return $featured_url;
		}
	}

	$customizer_attachment_id = absint( get_theme_mod( 'raghushree_media_' . $slot_key, 0 ) );
	if ( $customizer_attachment_id ) {
		$custom_url = wp_get_attachment_image_url( $customizer_attachment_id, 'raghushree-hero' );
		if ( $custom_url ) {
			return $custom_url;
		}
	}

	$registry = raghushree_get_placeholder_registry();
	if ( isset( $registry[ $slot_key ] ) ) {
		return raghushree_build_unsplash_url(
			$registry[ $slot_key ]['base_url'],
			$registry[ $slot_key ]['width'],
			$registry[ $slot_key ]['height']
		);
	}

	return raghushree_build_unsplash_url( $registry['home_hero']['base_url'], 1200, 800 );
}

/**
 * Render a responsive image using WordPress Media Library first, with centralized
 * Unsplash placeholder fallback.
 *
 * @param string               $slot_key Centralized registry key.
 * @param array<string, mixed> $args     Optional arguments: post_id, attachment_id, size, sizes, priority, alt, class.
 * @return void
 */
function raghushree_render_image( $slot_key, $args = array() ) {
	$defaults = array(
		'post_id'       => 0,
		'attachment_id' => 0,
		'size'          => 'raghushree-split',
		'sizes'         => '(min-width: 1200px) 600px, (min-width: 900px) 48vw, 100vw',
		'priority'      => false,
		'alt'           => '',
		'class'         => '',
	);
	$args     = wp_parse_args( $args, $defaults );

	$attachment_id = absint( $args['attachment_id'] );
	if ( ! $attachment_id && ! empty( $args['post_id'] ) && has_post_thumbnail( $args['post_id'] ) ) {
		$attachment_id = (int) get_post_thumbnail_id( $args['post_id'] );
	}
	if ( ! $attachment_id ) {
		$attachment_id = absint( get_theme_mod( 'raghushree_media_' . $slot_key, 0 ) );
	}

	// 1. Native WordPress Media Library output when replaced by admin
	if ( $attachment_id ) {
		$img_attrs = array(
			'sizes'     => $args['sizes'],
			'decoding'  => 'async',
			'loading'   => $args['priority'] ? 'eager' : 'lazy',
		);
		if ( $args['priority'] ) {
			$img_attrs['fetchpriority'] = 'high';
		}
		if ( ! empty( $args['class'] ) ) {
			$img_attrs['class'] = $args['class'];
		}
		if ( ! empty( $args['alt'] ) ) {
			$img_attrs['alt'] = $args['alt'];
		}

		$wp_img = wp_get_attachment_image( $attachment_id, $args['size'], false, $img_attrs );
		if ( $wp_img ) {
			echo $wp_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_get_attachment_image.
			return;
		}
	}

	// 2. Centralized Theme Asset or Temporary Unsplash Placeholder Fallback
	$registry = raghushree_get_placeholder_registry();
	$item     = isset( $registry[ $slot_key ] ) ? $registry[ $slot_key ] : $registry['home_hero'];
	$width    = (int) $item['width'];
	$height   = (int) $item['height'];
	$alt      = ! empty( $args['alt'] ) ? $args['alt'] : $item['alt'];

	if ( ! empty( $item['local_file'] ) && file_exists( get_template_directory() . '/' . ltrim( $item['local_file'], '/' ) ) ) {
		$local_src = get_template_directory_uri() . '/' . ltrim( $item['local_file'], '/' );
		printf(
			'<img src="%1$s" width="%2$d" height="%3$d" alt="%4$s"%5$s decoding="async" loading="%6$s"%7$s />',
			esc_url( $local_src ),
			absint( $width ),
			absint( $height ),
			esc_attr( $alt ),
			! empty( $args['class'] ) ? ' class="' . esc_attr( $args['class'] ) . '"' : '',
			$args['priority'] ? 'eager' : 'lazy',
			$args['priority'] ? ' fetchpriority="high"' : ''
		);
		return;
	}

	$base_url = $item['base_url'];
	$ratio    = $height > 0 ? ( $height / $width ) : 0.65;

	$widths  = array( 480, 768, 1080, 1440 );
	$srcset  = array();
	foreach ( $widths as $w ) {
		$h = (int) round( $w * $ratio );
		// esc_url_raw keeps the query string single-encoded; the whole srcset is
		// escaped once below when it is printed into the attribute.
		$srcset[] = esc_url_raw( raghushree_build_unsplash_url( $base_url, $w, $h ) ) . ' ' . $w . 'w';
	}

	$src = raghushree_build_unsplash_url( $base_url, $width, $height );

	printf(
		'<img src="%1$s" srcset="%2$s" sizes="%3$s" width="%4$d" height="%5$d" alt="%6$s"%7$s decoding="async" loading="%8$s"%9$s />',
		esc_url( $src ),
		esc_attr( implode( ', ', $srcset ) ),
		esc_attr( $args['sizes'] ),
		absint( $width ),
		absint( $height ),
		esc_attr( $alt ),
		! empty( $args['class'] ) ? ' class="' . esc_attr( $args['class'] ) . '"' : '',
		$args['priority'] ? 'eager' : 'lazy',
		$args['priority'] ? ' fetchpriority="high"' : ''
	);
}

<?php
/**
 * Idempotent Theme Content Seeder (Core Pages & 7 Polymer Records) + Admin Setup Screen.
 *
 * Strictly adheres to the non-fabrication rule: seeds factual polymer category overviews,
 * processing methods, and applications while leaving unverified grade codes, technical
 * specifications, datasheets, pack sizes, and MOQs blank until entered in WP Admin.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the 7 core polymer definitions for initial catalog seeding.
 *
 * @return array<int, array<string, mixed>>
 */
function raghushree_get_core_polymer_seed_definitions() {
	return array(
		array(
			'slug'         => 'polypropylene-pp',
			'title'        => 'Polypropylene (PP)',
			'code'         => 'PP',
			'category'     => 'Polypropylene (PP)',
			'image_slot'   => 'polymer_pp',
			'menu_order'   => 1,
			'short_desc'   => 'Versatile thermoplastic polymer supplied for injection moulding, extrusion, raffia/woven packaging, household goods, automotive, and industrial components.',
			'content'      => "<p>Polypropylene (PP) is one of the most widely processed thermoplastics in industrial manufacturing, valued for its balanced stiffness, chemical resistance, fatigue endurance, and processing versatility across moulding and extrusion lines.</p>\n<p>Raghushree supplies Polypropylene raw materials to industrial manufacturers, contract moulders, packaging converters, and distributors across India and international markets. Specific grade selection and form availability are matched to your processing method and end-product requirements upon enquiry.</p>",
			'grades_notes' => 'Available Polypropylene grades and material forms are confirmed against your required melt flow characteristics, colour/natural preference, and processing equipment. Share your target application with our team to verify current grade availability.',
			'applications' => "Injection Moulded Components | Rigid industrial parts, closures, caps, containers, crates, and technical housings.\nRaffia, Tape & Woven Packaging | Stretched tapes, woven sacks, FIBC bags, and industrial strapping.\nHousehold & Consumer Goods | Storage articles, furniture components, buckets, and utility ware.\nElectrical & Automotive Parts | Appliance housings, battery casings, and interior moulded components.",
			'industries'   => "Plastic Product Manufacturers\nContract Moulding Companies\nPackaging & Woven Sack Converters\nHousehold & Consumer Goods Manufacturers\nElectrical & Industrial Component Makers",
			'processing'   => "Injection Moulding | Suitable for thin-wall packaging, general-purpose moulded articles, and structural industrial parts.\nExtrusion & Tape Stretching | Used in raffia tape lines, sheet extrusion, strapping, and monofilament production.\nThermoforming | Applicable where sheet-grade polypropylene is formed into trays, cups, and packaging.",
			'faqs'         => "How do I confirm which Polypropylene (PP) grade suits my moulding line? | Share your part type, mould configuration, and preferred melt flow range in our enquiry form so our team can recommend currently available PP grades.\nCan I request a PP material sample before placing a bulk order? | Yes. Production, R&D, and quality control teams can submit a sample request specifying their application and trial quantity requirements.",
			'grades_terms' => array(),
		),
		array(
			'slug'         => 'high-density-polyethylene-hdpe',
			'title'        => 'High-Density Polyethylene (HDPE)',
			'code'         => 'HDPE',
			'category'     => 'High-Density Polyethylene (HDPE)',
			'image_slot'   => 'polymer_hdpe',
			'menu_order'   => 2,
			'short_desc'   => 'High-strength polyethylene raw material for blow-moulded containers, extruded pipes and conduits, industrial crates, and heavy-duty packaging.',
			'content'      => "<p>High-Density Polyethylene (HDPE) offers a strong strength-to-density ratio, impact resistance, moisture barrier performance, and chemical stability, making it a core raw material across rigid packaging, piping, and industrial moulding sectors.</p>\n<p>Raghushree supports industrial buyers seeking HDPE raw materials for blow moulding, pipe and profile extrusion, injection moulding, and film conversion. Exact grade specifications and material forms are confirmed directly against your production requirements.</p>",
			'grades_notes' => 'HDPE processing requirements differ significantly between blow moulding, pipe extrusion, film extrusion, and injection moulding. Contact our sales team to confirm current availability of specific HDPE grades and forms.',
			'applications' => "Blow Moulded Containers | Bottles, jerrycans, chemical drums, household liquid containers, and industrial canisters.\nPipes, Conduits & Fittings | Water supply pipes, agricultural piping, cable ducts, and industrial conduits.\nCrates, Pallets & Material Handling | Heavy-duty injection moulded crates, bins, caps, and closures.\nHDPE Film & Liners | Carry bags, industrial liners, and moisture-barrier packaging.",
			'industries'   => "Rigid Packaging Manufacturers\nPipe & Conduit Extruders\nContract Blow & Injection Moulders\nIndustrial Container Manufacturers\nPolymer Distributors & Dealers",
			'processing'   => "Blow Moulding | Extrusion blow moulding for hollow bottles, jerrycans, drums, and industrial containers.\nPipe & Profile Extrusion | Continuous extrusion for pressure pipes, conduits, ducts, and sheets.\nInjection Moulding | High-rigidity moulded crates, caps, closures, and industrial pails.\nBlown Film Extrusion | High-strength thin-gauge films and industrial liners.",
			'faqs'         => "Do you supply HDPE for both blow moulding and pipe extrusion? | Grade suitability depends on melt flow and density requirements. Please specify whether your line runs blow moulding, extrusion, or injection moulding when submitting your enquiry.\nHow can procurement teams verify current HDPE availability? | Submit an availability or quotation request via our contact page or WhatsApp with your required grade specification and estimated quantity.",
			'grades_terms' => array(),
		),
		array(
			'slug'         => 'low-density-polyethylene-ldpe',
			'title'        => 'Low-Density Polyethylene (LDPE)',
			'code'         => 'LDPE',
			'category'     => 'Low-Density Polyethylene (LDPE)',
			'image_slot'   => 'polymer_ldpe',
			'menu_order'   => 3,
			'short_desc'   => 'Flexible, tough polyethylene polymer widely used in packaging films, shrink wraps, extrusion coating, squeeze bottles, and flexible moulded parts.',
			'content'      => "<p>Low-Density Polyethylene (LDPE) is characterized by high flexibility, optical clarity in film grades, good toughness, and reliable melt strength during blown and cast film processing as well as extrusion coating.</p>\n<p>Raghushree supplies LDPE raw materials for packaging manufacturers, film extruders, and flexible component moulders. Grade and form availability is verified per order enquiry to ensure compatibility with your extrusion or moulding equipment.</p>",
			'grades_notes' => 'LDPE grades vary by film clarity, slip/additive profile, and melt index. Please share your film thickness or moulding application so we can confirm suitable available LDPE grades and forms.',
			'applications' => "Flexible Packaging Films | General-purpose packaging bags, pouches, shrink films, and protective wraps.\nAgricultural & Industrial Liners | Greenhouse films, heavy-duty sack liners, and moisture barriers.\nExtrusion Coating & Lamination | Paper, board, and woven fabric coating layers.\nFlexible Moulded Articles | Squeeze bottles, flexible closures, lids, and soft industrial components.",
			'industries'   => "Film & Packaging Converters\nFlexible Packaging Manufacturers\nLamination & Coating Units\nConsumer & Industrial Moulders",
			'processing'   => "Blown Film Extrusion | Stable bubble formation for general packaging, liners, and shrink films.\nCast Film & Extrusion Coating | Uniform melt flow for coating substrates and flexible laminates.\nInjection & Blow Moulding | Soft, impact-resistant closures, caps, and squeezable containers.",
			'faqs'         => "What details should film manufacturers include in an LDPE enquiry? | Mention your film process (blown or cast), target gauge/micron range, and estimated monthly or batch volume so we can confirm matching LDPE availability.\nAre domestic and export enquiries supported for LDPE? | Yes. Raghushree handles enquiries from manufacturing units across India as well as international buyers.",
			'grades_terms' => array(),
		),
		array(
			'slug'         => 'linear-low-density-polyethylene-lldpe',
			'title'        => 'Linear Low-Density Polyethylene (LLDPE)',
			'code'         => 'LLDPE',
			'category'     => 'Linear Low-Density Polyethylene (LLDPE)',
			'image_slot'   => 'polymer_lldpe',
			'menu_order'   => 4,
			'short_desc'   => 'High-puncture-resistance polyethylene raw material for stretch film, multilayer packaging, rotational moulding, and durable industrial liners.',
			'content'      => "<p>Linear Low-Density Polyethylene (LLDPE) delivers high tensile strength, puncture resistance, and elongation performance, making it indispensable for stretch films, multilayer flexible packaging, heavy-duty liners, and rotational moulding applications.</p>\n<p>Raghushree works with packaging converters, rotational moulders, and polymer buyers to supply LLDPE in verified grades and forms aligned with their processing method—whether granules for film extrusion or powder/granules where applicable for moulding.</p>",
			'grades_notes' => 'LLDPE may be required in granule form for film/blending lines or specific powder/moulding forms depending on application. Do not assume all forms are stocked simultaneously—please confirm current grade and form availability with our sales desk.',
			'applications' => "Stretch & Cling Films | Pallet stretch wrap, industrial bundling films, and protective transit films.\nMultilayer & Blending Films | Co-extruded packaging films and LDPE/LLDPE film blends for enhanced toughness.\nRotational & Hollow Moulding | Water storage tanks, industrial bins, and hollow moulded articles where rotomoulding grades/forms are specified.\nCable Jacketing & Tubing | Flexible protective sheathing and industrial tubing.",
			'industries'   => "Stretch & Multilayer Film Extruders\nFlexible Packaging Converters\nRotational & Industrial Moulders\nPlastic Raw Material Distributors",
			'processing'   => "Blown & Cast Film Extrusion | High drawdown capability for thin, puncture-resistant packaging and stretch films.\nPolymer Blending | Frequently blended with LDPE or HDPE to balance film stiffness, sealability, and tear resistance.\nRotational & Injection Moulding | Applicable when appropriate moulding-grade LLDPE forms are selected.",
			'faqs'         => "Do you supply LLDPE in granules or powder form? | Material form availability (such as granules or powder) depends on the specific LLDPE grade currently in supply. Contact us with your process details to verify exact form availability.\nCan we discuss technical blending requirements with your team? | Yes. Production and technical teams can reach out to discuss film extrusion or moulding grade suitability prior to quotation.",
			'grades_terms' => array(),
		),
		array(
			'slug'         => 'polyvinyl-chloride-pvc',
			'title'        => 'Polyvinyl Chloride (PVC)',
			'code'         => 'PVC',
			'category'     => 'Polyvinyl Chloride (PVC)',
			'image_slot'   => 'polymer_pvc',
			'menu_order'   => 5,
			'short_desc'   => 'Durable polymer raw material supporting rigid pipes and fittings, profiles, electrical cable insulation, tubing, and industrial extrusion applications.',
			'content'      => "<p>Polyvinyl Chloride (PVC) is a widely utilized engineering and commodity thermoplastic valued for flame retardancy, weatherability, chemical durability, and formulation flexibility across both rigid and flexible applications.</p>\n<p>Raghushree supplies PVC raw materials for manufacturers producing pipes, fittings, profiles, cable sheathing, and industrial components. Available resin/compound grades and forms are confirmed directly per technical enquiry.</p>",
			'grades_notes' => 'PVC processing depends heavily on rigid vs flexible formulation requirements and equipment type. Contact our team with your target specification or K-value/compound need to confirm verified availability.',
			'applications' => "Pipes, Conduits & Fittings | Potable water pipes, drainage systems, agricultural piping, and electrical conduits.\nProfiles, Sheets & Building Products | Window/door profiles, wall panels, trunking, and industrial sheets.\nWire & Cable Insulation | Electrical cable sheathing, insulation compounds, and flexible conduits.\nFlexible Tubing & Industrial Goods | Hoses, footwear components, seals, and calendered sheets.",
			'industries'   => "Pipe & Fitting Manufacturers\nElectrical & Cable Manufacturers\nProfile & Building Material Extruders\nIndustrial Polymer Processors",
			'processing'   => "Twin-Screw & Single-Screw Extrusion | Rigid pipes, conduits, profiles, flexible hoses, and cable jacketing.\nInjection Moulding | Pipe fittings, electrical junction boxes, valves, and industrial connectors.\nCalendering & Compounding | Sheet production and custom PVC formulation workflows.",
			'faqs'         => "How do I enquire about PVC grades for pipe or cable manufacturing? | Specify whether your requirement is for rigid extrusion, fittings moulding, or flexible cable/tubing applications, along with your target specification and order volume.\nCan distributors enquire for recurring PVC supply? | Yes. Dealers, distributors, and repeat manufacturing buyers can submit bulk or distributor enquiries through our contact page.",
			'grades_terms' => array(),
		),
		array(
			'slug'         => 'polyethylene-terephthalate-pet',
			'title'        => 'Polyethylene Terephthalate (PET)',
			'code'         => 'PET',
			'category'     => 'Polyethylene Terephthalate (PET)',
			'image_slot'   => 'polymer_pet',
			'menu_order'   => 6,
			'short_desc'   => 'High-clarity, high-strength polyester polymer used in bottle preforms, thermoformed packaging sheets, strapping tapes, and fibre/extrusion applications.',
			'content'      => "<p>Polyethylene Terephthalate (PET) combines dimensional stability, mechanical strength, gas and moisture barrier properties, and optical clarity, serving beverage/food container preforms, thermoformed packaging, industrial strapping, and textile/fibre extrusion.</p>\n<p>Raghushree handles industrial enquiries for PET raw materials across virgin-compatible and reprocessed/recycled pathways where available. Contact our team to verify available PET grades, intrinsic viscosity (IV) suitability, and material forms.</p>",
			'grades_notes' => 'PET selection depends on target application (preform injection, sheet extrusion, strapping, or fibre) and drying/processing conditions. Reach out to confirm currently available PET grades and forms.',
			'applications' => "Preforms & Blow Moulded Bottles | Beverage, edible oil, personal care, household, and pharmaceutical containers.\nThermoformed Sheets & Trays | Blister packs, clamshells, food trays, and transparent rigid packaging.\nIndustrial Strapping & Monofilament | High-tensile packaging strapping tapes and technical bristles.\nFibre & Sheet Extrusion | Non-woven, staple fibre, and industrial sheet applications.",
			'industries'   => "Preform & Bottle Manufacturers\nRigid Thermoforming & Packaging Units\nStrapping & Extrusion Manufacturers\nIndustrial Polymer Buyers",
			'processing'   => "Injection Stretch Blow Moulding (ISBM) | Preform injection followed by biaxial stretch blow moulding.\nSheet Extrusion & Thermoforming | Transparent rigid sheets formed into trays and blister packaging.\nExtrusion & Orientation | High-tensile PET strapping bands and industrial filaments.",
			'faqs'         => "What technical details help match the right PET grade? | Sharing your processing method (preform moulding, sheet thermoforming, or strapping extrusion) and target specification helps our team confirm available PET options.\nHow do I request a PET quotation? | Use the Request a Quotation button on this page to pre-select PET on our enquiry form and receive a prompt commercial response.",
			'grades_terms' => array(),
		),
		array(
			'slug'         => 'recycled-reprocessed-polymers',
			'title'        => 'Recycled & Reprocessed Polymers',
			'code'         => 'R-POLY',
			'category'     => 'Recycled and Reprocessed Polymers',
			'image_slot'   => 'polymer_recycled',
			'menu_order'   => 7,
			'short_desc'   => 'Cost-effective recycled and reprocessed polymer raw materials supplied in verified grades and forms for industrial moulding, extrusion, pipes, and non-food packaging.',
			'content'      => "<p>Recycled and reprocessed polymers enable manufacturers to optimize raw material costs and meet circular material objectives while maintaining dependable processability across injection moulding, extrusion, blow moulding, and utility product manufacturing.</p>\n<p>Raghushree supplies verified reprocessed and recycled polymer grades—primarily in plastic granules and other applicable material forms—matched carefully to each buyer's mechanical, colour, and processing requirements.</p>",
			'grades_notes' => 'Reprocessed and recycled polymer availability varies by polymer family (such as PP, HDPE, LDPE, LLDPE, PVC, or PET), colour sorting (natural, white, black, or coloured), filtration mesh, and processing grade. Contact us to check current batch availability and request evaluation samples.',
			'applications' => "Industrial & Utility Moulding | Crates, pallets, bins, buckets, planters, furniture parts, and material-handling articles.\nNon-Pressure Pipes, Conduits & Profiles | Agricultural pipes, cable ducts, drainage conduits, and extruded profiles.\nSecondary Packaging & Liners | Refuse sacks, construction films, protective wraps, and industrial liners.\nCompound Blending | Controlled blending with primary resins to balance cost and part performance.",
			'industries'   => "Contract & Utility Moulders\nPipe, Conduit & Profile Extruders\nIndustrial & Secondary Packaging Makers\nHousehold & Hardware Product Manufacturers\nPlastic Raw Material Dealers",
			'processing'   => "Injection Moulding | Moulded utility, household, automotive non-critical, and industrial components.\nExtrusion | Pipes, conduits, sheets, profiles, and heavy-duty industrial liners.\nBlow Moulding | Non-food household and industrial containers where reprocessed grades are suitable.",
			'faqs'         => "Can I request a sample of reprocessed or recycled granules for machine trials? | Yes. Because reprocessed polymer lots should align with your mould and machine settings, we encourage production and QC teams to request a trial sample and discuss specifications.\nAre recycled grades available across every polymer and colour at all times? | Availability depends on current feedstock and production batches. Please contact our sales team to verify exact polymer family, colour, and grade availability.",
			'grades_terms' => array( 'Reprocessed Grades', 'Recycled Grades' ),
			'forms_terms'  => array( 'Plastic Granules' ),
		),
	);
}

/**
 * Seed default pages, taxonomies, and core polymer records idempotently.
 * Never overwrites existing user-edited posts or creates duplicates.
 *
 * @param bool $force Whether triggered manually from admin setup button.
 * @return void
 */
function raghushree_seed_initial_content( $force = false ) {
	if ( ! $force && get_option( 'raghushree_seeded_v1' ) ) {
		return;
	}

	// Ensure CPTs and taxonomies are registered before seeding
	if ( ! post_type_exists( 'products' ) ) {
		raghushree_register_content_types();
	}

	// 1. Seed Primary Pages (Home, About, Contact) if missing
	$core_pages = array(
		'home'    => array(
			'title'    => 'Home',
			'template' => '',
		),
		'about'   => array(
			'title'    => 'About',
			'template' => 'page-templates/template-about.php',
		),
		'contact' => array(
			'title'    => 'Contact',
			'template' => 'page-templates/template-contact.php',
		),
	);

	$created_page_ids = array();
	foreach ( $core_pages as $slug => $page_cfg ) {
		$existing = get_page_by_path( $slug );
		if ( $existing instanceof WP_Post ) {
			$page_id = $existing->ID;
		} else {
			$page_id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $page_cfg['title'],
					'post_name'    => $slug,
					'post_content' => '',
				)
			);
		}

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			$created_page_ids[ $slug ] = $page_id;
			if ( ! empty( $page_cfg['template'] ) ) {
				update_post_meta( $page_id, '_wp_page_template', $page_cfg['template'] );
			}
		}
	}

	// Set static front page if currently using default latest posts
	if ( ! empty( $created_page_ids['home'] ) && 'page' !== get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $created_page_ids['home'] );
	}

	// 2. Seed Core Taxonomy Terms (Grades & Forms exist ready for admin assignment)
	$grade_terms = array( 'Reprocessed Grades', 'Recycled Grades' );
	foreach ( $grade_terms as $g_name ) {
		if ( ! term_exists( $g_name, 'material_grade' ) ) {
			wp_insert_term( $g_name, 'material_grade' );
		}
	}

	$form_terms = array( 'Plastic Granules', 'Plastic Powder' );
	foreach ( $form_terms as $f_name ) {
		if ( ! term_exists( $f_name, 'material_form' ) ) {
			wp_insert_term( $f_name, 'material_form' );
		}
	}

	$app_terms = array(
		'Injection Moulding',
		'Blow Moulding',
		'Extrusion',
		'Film and Packaging',
		'Pipes and Fittings',
		'Electrical and Electronics',
		'Household and Consumer Products',
	);
	foreach ( $app_terms as $a_name ) {
		if ( ! term_exists( $a_name, 'manufacturing_application' ) ) {
			wp_insert_term( $a_name, 'manufacturing_application' );
		}
	}

	// 3. Seed the 7 Core Polymer Records if missing
	foreach ( raghushree_get_core_polymer_seed_definitions() as $def ) {
		$existing_prod = get_page_by_path( $def['slug'], OBJECT, 'products' );
		if ( $existing_prod instanceof WP_Post ) {
			continue;
		}

		$prod_id = wp_insert_post(
			array(
				'post_type'    => 'products',
				'post_status'  => 'publish',
				'post_title'   => $def['title'],
				'post_name'    => $def['slug'],
				'post_excerpt' => $def['short_desc'],
				'post_content' => $def['content'],
				'menu_order'   => $def['menu_order'],
			)
		);

		if ( ! $prod_id || is_wp_error( $prod_id ) ) {
			continue;
		}

		if ( ! term_exists( $def['category'], 'polymer_category' ) ) {
			wp_insert_term( $def['category'], 'polymer_category' );
		}
		wp_set_object_terms( $prod_id, array( $def['category'] ), 'polymer_category' );

		if ( ! empty( $def['grades_terms'] ) ) {
			wp_set_object_terms( $prod_id, $def['grades_terms'], 'material_grade' );
		}
		if ( ! empty( $def['forms_terms'] ) ) {
			wp_set_object_terms( $prod_id, $def['forms_terms'], 'material_form' );
		}

		update_post_meta( $prod_id, '_raghushree_polymer_code', $def['code'] );
		update_post_meta( $prod_id, '_raghushree_image_slot', $def['image_slot'] );
		update_post_meta( $prod_id, '_raghushree_short_desc', $def['short_desc'] );
		update_post_meta( $prod_id, '_raghushree_grades_notes', $def['grades_notes'] );
		update_post_meta( $prod_id, '_raghushree_applications_rows', $def['applications'] );
		update_post_meta( $prod_id, '_raghushree_industries_rows', $def['industries'] );
		update_post_meta( $prod_id, '_raghushree_processing_rows', $def['processing'] );
		update_post_meta( $prod_id, '_raghushree_product_faqs', $def['faqs'] );
	}

	update_option( 'raghushree_seeded_v1', 1 );
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'raghushree_seed_initial_content' );

/**
 * Ensure initial seeding runs once even if theme is activated via WP-CLI or DB config.
 *
 * @return void
 */
function raghushree_maybe_seed_on_init() {
	if ( ! get_option( 'raghushree_seeded_v1' ) ) {
		raghushree_seed_initial_content( false );
	}
}
add_action( 'init', 'raghushree_maybe_seed_on_init', 30 );

/**
 * Add an Appearance -> Raghushree Setup admin page for easy maintenance & image docs.
 *
 * @return void
 */
function raghushree_register_setup_admin_page() {
	add_theme_page(
		__( 'Raghushree Theme Setup', 'raghushree' ),
		__( 'Raghushree Setup', 'raghushree' ),
		'manage_options',
		'raghushree-setup',
		'raghushree_render_setup_admin_page'
	);
}
add_action( 'admin_menu', 'raghushree_register_setup_admin_page' );

/**
 * Render Appearance -> Raghushree Setup page.
 *
 * @return void
 */
function raghushree_render_setup_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$synced = false;
	if ( isset( $_POST['raghushree_sync_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['raghushree_sync_nonce'] ) ), 'raghushree_manual_sync' ) ) {
		raghushree_seed_initial_content( true );
		$synced = true;
	}

	$company = raghushree_get_company_info();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Raghushree Industrial Theme — Setup & Content Guide', 'raghushree' ); ?></h1>
		<?php if ( $synced ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Core pages, taxonomies,polymer records, and rewrite rules synchronized.', 'raghushree' ); ?></p></div>
		<?php endif; ?>

		<div class="card" style="max-width:820px;margin-top:16px;">
			<h2><?php esc_html_e( 'Verified Company Configuration', 'raghushree' ); ?></h2>
			<p><strong><?php echo esc_html( $company['legal_name'] ); ?></strong><br />
			<?php echo esc_html( $company['full_address'] ); ?><br />
			Email: <?php echo esc_html( $company['email'] ); ?> | Phone / WhatsApp: <?php echo esc_html( $company['phone'] ); ?></p>
			<p><a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Edit in WordPress Customizer', 'raghushree' ); ?></a></p>
		</div>

		<div class="card" style="max-width:820px;margin-top:16px;">
			<h2><?php esc_html_e( 'Replacing Temporary Unsplash Placeholder Images', 'raghushree' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'For Products: Edit any polymer under Products -> All Products and set a Featured Image.', 'raghushree' ); ?></li>
				<li><?php esc_html_e( 'For Pages & Sections: Open Appearance -> Customize -> Raghushree Image Placeholders to select real company photographs from your Media Library.', 'raghushree' ); ?></li>
				<li><?php esc_html_e( 'All fallback URLs are centralized in one file: inc/image-placeholders.php.', 'raghushree' ); ?></li>
			</ol>
		</div>

		<div class="card" style="max-width:820px;margin-top:16px;">
			<h2><?php esc_html_e( 'Sync Default Pages & Core Polymer Records', 'raghushree' ); ?></h2>
			<p><?php esc_html_e( 'Safely creates any missing core pages (Home, About, Contact) or missing core polymer entries without overwriting existing posts.', 'raghushree' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'raghushree_manual_sync', 'raghushree_sync_nonce' ); ?>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Sync Missing Pages & Polymer Records', 'raghushree' ); ?></button>
			</form>
		</div>
	</div>
	<?php
}

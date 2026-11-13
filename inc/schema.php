<?php
/**
 * Structured Data (JSON-LD) Generator with Strict Non-Fabrication & Visible FAQ Sync.
 *
 * Outputs JSON-LD in wp_footer so that any FAQs visibly rendered during template
 * execution via `raghushree_register_visible_faqs()` are 100% accurately reflected.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register FAQ items AT RENDER TIME when the FAQ template component outputs them visibly.
 * Guarantees FAQPage schema is NEVER generated for hidden or absent FAQs.
 *
 * @param array<int, array<string, string>> $faqs Visible FAQ items (`question`, `answer`).
 * @return void
 */
function raghushree_register_visible_faqs( $faqs ) {
	global $raghushree_visible_faqs;
	if ( ! is_array( $raghushree_visible_faqs ) ) {
		$raghushree_visible_faqs = array();
	}
	foreach ( $faqs as $faq ) {
		if ( ! empty( $faq['question'] ) && ! empty( $faq['answer'] ) ) {
			$raghushree_visible_faqs[] = array(
				'question' => wp_strip_all_tags( (string) $faq['question'] ),
				'answer'   => wp_strip_all_tags( (string) $faq['answer'] ),
			);
		}
	}
}

/**
 * Output sanitized JSON-LD Structured Data (`@graph`).
 *
 * @return void
 */
function raghushree_output_json_ld_schema() {
	if ( raghushree_has_seo_plugin() || is_404() ) {
		return;
	}

	global $raghushree_visible_faqs;
	$company  = raghushree_get_company_info();
	$seo      = raghushree_resolve_current_seo();
	$site_url = trailingslashit( home_url( '/' ) );
	$org_id   = $site_url . '#organization';
	$web_id   = $site_url . '#website';
	$page_url = ! empty( $seo['canonical'] ) ? $seo['canonical'] : $site_url;
	$page_id  = $page_url . '#webpage';

	$graph = array();

	// 1. Organization Entity (Verified Details Only — Zero Fabricated Ratings/Certs)
	$org_entity = array(
		'@type'         => 'Organization',
		'@id'           => $org_id,
		'name'          => $company['legal_name'],
		'alternateName' => $company['brand_name'],
		'url'           => $site_url,
		'logo'          => get_template_directory_uri() . '/assets/images/logo-default-334x88.png',
		'description'   => $company['positioning'],
	);

	if ( ! empty( $company['email'] ) ) {
		$org_entity['email'] = $company['email'];
	}
	if ( ! empty( $company['phone'] ) ) {
		$org_entity['telephone'] = $company['phone'];
	}
	if ( ! empty( $company['street_address'] ) || ! empty( $company['city'] ) ) {
		$org_entity['address'] = array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $company['street_address'],
			'addressLocality' => $company['city'],
			'addressRegion'   => $company['state'],
			'postalCode'      => $company['postal_code'],
			'addressCountry'  => $company['country_code'],
		);
	}
	if ( ! empty( $company['socials'] ) ) {
		$same_as = array();
		foreach ( $company['socials'] as $soc ) {
			$same_as[] = $soc['url'];
		}
		$org_entity['sameAs'] = $same_as;
	}

	$graph[] = $org_entity;

	// 2. WebSite Entity
	$graph[] = array(
		'@type'       => 'WebSite',
		'@id'         => $web_id,
		'url'         => $site_url,
		'name'        => $company['brand_name'],
		'publisher'   => array( '@id' => $org_id ),
		'inLanguage'  => get_bloginfo( 'language' ) ? get_bloginfo( 'language' ) : 'en-IN',
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => add_query_arg( 'product_q', '{search_term_string}', raghushree_get_products_url() ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	// 3. Page-Specific WebPage / CollectionPage / AboutPage / ContactPage / Product Entities
	if ( is_front_page() ) {
		$graph[] = array(
			'@type'       => 'WebPage',
			'@id'         => $page_id,
			'url'         => $page_url,
			'name'        => $seo['title'],
			'description' => $seo['description'],
			'isPartOf'    => array( '@id' => $web_id ),
			'about'       => array( '@id' => $org_id ),
		);
	} elseif ( is_page_template( 'page-templates/template-about.php' ) || is_page( 'about' ) ) {
		$graph[] = array(
			'@type'       => 'AboutPage',
			'@id'         => $page_id,
			'url'         => $page_url,
			'name'        => $seo['title'],
			'description' => $seo['description'],
			'isPartOf'    => array( '@id' => $web_id ),
			'mainEntity'  => array( '@id' => $org_id ),
		);
	} elseif ( is_page_template( 'page-templates/template-contact.php' ) || is_page( 'contact' ) ) {
		$graph[] = array(
			'@type'       => 'ContactPage',
			'@id'         => $page_id,
			'url'         => $page_url,
			'name'        => $seo['title'],
			'description' => $seo['description'],
			'isPartOf'    => array( '@id' => $web_id ),
			'mainEntity'  => array( '@id' => $org_id ),
		);
	} elseif ( is_post_type_archive( 'products' ) || is_page_template( 'page-templates/template-products.php' ) ) {
		$items       = raghushree_get_catalog_items( 20 );
		$list_els    = array();
		$pos         = 1;
		foreach ( $items as $item ) {
			$list_els[] = array(
				'@type'    => 'ListItem',
				'position' => $pos++,
				'url'      => $item['permalink'],
				'name'     => $item['title'],
			);
		}

		$collection = array(
			'@type'       => 'CollectionPage',
			'@id'         => $page_id,
			'url'         => $page_url,
			'name'        => $seo['title'],
			'description' => $seo['description'],
			'isPartOf'    => array( '@id' => $web_id ),
			'about'       => array( '@id' => $org_id ),
		);
		if ( ! empty( $list_els ) ) {
			$collection['mainEntity'] = array(
				'@type'           => 'ItemList',
				'itemListElement' => $list_els,
			);
		}
		$graph[] = $collection;
	} elseif ( is_singular( 'products' ) ) {
		$post_id = get_queried_object_id();
		$prod    = raghushree_get_product_data( $post_id );

		$graph[] = array(
			'@type'       => 'WebPage',
			'@id'         => $page_id,
			'url'         => $page_url,
			'name'        => $seo['title'],
			'description' => $seo['description'],
			'isPartOf'    => array( '@id' => $web_id ),
		);

		// Product Schema — strictly omits fabricated offers/prices, stock, or ratings
		$product_entity = array(
			'@type'        => 'Product',
			'@id'          => $page_url . '#product',
			'name'         => $prod['title'],
			'description'  => $prod['short_desc'],
			'url'          => $page_url,
			'category'     => $prod['primary_cat'],
			'image'        => array( raghushree_get_image_url( $prod['image_slot'], $post_id ) ),
			'brand'        => array(
				'@type' => 'Brand',
				'name'  => $company['brand_name'],
			),
			'manufacturer' => array( '@id' => $org_id ),
		);
		$graph[] = $product_entity;
	} else {
		$graph[] = array(
			'@type'       => 'WebPage',
			'@id'         => $page_id,
			'url'         => $page_url,
			'name'        => $seo['title'],
			'description' => $seo['description'],
			'isPartOf'    => array( '@id' => $web_id ),
		);
	}

	// 4. Conditional FAQPage Entity (ONLY when matching FAQs are visibly displayed on screen)
	if ( ! empty( $raghushree_visible_faqs ) && is_array( $raghushree_visible_faqs ) ) {
		$faq_entities = array();
		foreach ( $raghushree_visible_faqs as $faq_item ) {
			$faq_entities[] = array(
				'@type'          => 'Question',
				'name'           => $faq_item['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $faq_item['answer'],
				),
			);
		}
		if ( ! empty( $faq_entities ) ) {
			$graph[] = array(
				'@type'      => 'FAQPage',
				'@id'        => $page_url . '#faq',
				'isPartOf'   => array( '@id' => $page_id ),
				'mainEntity' => $faq_entities,
			);
		}
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'raghushree_output_json_ld_schema', 20 );

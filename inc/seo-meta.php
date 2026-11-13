<?php
/**
 * Technical SEO Meta Boxes, Canonical URLs, Robots, Open Graph & Twitter Metadata.
 *
 * Automatically detects active SEO plugins (Yoast, Rank Math, AIOSEO, SEOPress, TSF)
 * to prevent duplicate title, canonical, Open Graph, or Twitter tags.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check whether an external SEO plugin already manages metadata/schema.
 *
 * @return bool
 */
function raghushree_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| defined( 'THE_SEO_FRAMEWORK_VERSION' );
}

/**
 * Register native SEO Meta Box on Pages, Posts, and Products when no SEO plugin is active.
 *
 * @return void
 */
function raghushree_register_seo_meta_box() {
	if ( raghushree_has_seo_plugin() ) {
		return;
	}

	$screens = array( 'page', 'post', 'products' );
	foreach ( $screens as $screen ) {
		add_meta_box(
			'raghushree_seo_meta_box',
			__( 'Raghushree SEO, Open Graph & Twitter Metadata', 'raghushree' ),
			'raghushree_render_seo_meta_box',
			$screen,
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'raghushree_register_seo_meta_box' );

/**
 * Render SEO Meta Box fields in WP Admin.
 *
 * @param WP_Post $post Current post object.
 * @return void
 */
function raghushree_render_seo_meta_box( $post ) {
	wp_nonce_field( 'raghushree_save_seo_meta', 'raghushree_seo_meta_nonce' );

	$meta = array(
		'title'        => get_post_meta( $post->ID, '_raghushree_seo_title', true ),
		'description'  => get_post_meta( $post->ID, '_raghushree_seo_desc', true ),
		'canonical'    => get_post_meta( $post->ID, '_raghushree_seo_canonical', true ),
		'robots'       => get_post_meta( $post->ID, '_raghushree_seo_robots', true ),
		'og_title'     => get_post_meta( $post->ID, '_raghushree_og_title', true ),
		'og_desc'      => get_post_meta( $post->ID, '_raghushree_og_desc', true ),
		'og_image'     => get_post_meta( $post->ID, '_raghushree_og_image', true ),
		'tw_title'     => get_post_meta( $post->ID, '_raghushree_tw_title', true ),
		'tw_desc'      => get_post_meta( $post->ID, '_raghushree_tw_desc', true ),
		'tw_image'     => get_post_meta( $post->ID, '_raghushree_tw_image', true ),
	);
	if ( empty( $meta['robots'] ) ) {
		$meta['robots'] = 'index,follow';
	}
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="raghushree_seo_title"><?php esc_html_e( 'SEO Title', 'raghushree' ); ?></label></th>
			<td><input type="text" id="raghushree_seo_title" name="raghushree_seo_title" value="<?php echo esc_attr( $meta['title'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Defaults to Post/Page Title — Raghushree', 'raghushree' ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="raghushree_seo_desc"><?php esc_html_e( 'Meta Description', 'raghushree' ); ?></label></th>
			<td><textarea id="raghushree_seo_desc" name="raghushree_seo_desc" rows="2" class="large-text"><?php echo esc_textarea( $meta['description'] ); ?></textarea></td>
		</tr>
		<tr>
			<th><label for="raghushree_seo_canonical"><?php esc_html_e( 'Canonical URL Override', 'raghushree' ); ?></label></th>
			<td><input type="url" id="raghushree_seo_canonical" name="raghushree_seo_canonical" value="<?php echo esc_url( $meta['canonical'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Defaults to page permalink', 'raghushree' ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="raghushree_seo_robots"><?php esc_html_e( 'Robots Directives', 'raghushree' ); ?></label></th>
			<td>
				<select id="raghushree_seo_robots" name="raghushree_seo_robots">
					<option value="index,follow" <?php selected( $meta['robots'], 'index,follow' ); ?>>index, follow</option>
					<option value="noindex,follow" <?php selected( $meta['robots'], 'noindex,follow' ); ?>>noindex, follow</option>
					<option value="index,nofollow" <?php selected( $meta['robots'], 'index,nofollow' ); ?>>index, nofollow</option>
					<option value="noindex,nofollow" <?php selected( $meta['robots'], 'noindex,nofollow' ); ?>>noindex, nofollow</option>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="raghushree_og_title"><?php esc_html_e( 'Open Graph Title', 'raghushree' ); ?></label></th>
			<td><input type="text" id="raghushree_og_title" name="raghushree_og_title" value="<?php echo esc_attr( $meta['og_title'] ); ?>" class="large-text" /></td>
		</tr>
		<tr>
			<th><label for="raghushree_og_desc"><?php esc_html_e( 'Open Graph Description', 'raghushree' ); ?></label></th>
			<td><textarea id="raghushree_og_desc" name="raghushree_og_desc" rows="2" class="large-text"><?php echo esc_textarea( $meta['og_desc'] ); ?></textarea></td>
		</tr>
		<tr>
			<th><label for="raghushree_og_image"><?php esc_html_e( 'Open Graph Image URL', 'raghushree' ); ?></label></th>
			<td><input type="url" id="raghushree_og_image" name="raghushree_og_image" value="<?php echo esc_url( $meta['og_image'] ); ?>" class="large-text" /></td>
		</tr>
		<tr>
			<th><label for="raghushree_tw_title"><?php esc_html_e( 'Twitter Title', 'raghushree' ); ?></label></th>
			<td><input type="text" id="raghushree_tw_title" name="raghushree_tw_title" value="<?php echo esc_attr( $meta['tw_title'] ); ?>" class="large-text" /></td>
		</tr>
		<tr>
			<th><label for="raghushree_tw_desc"><?php esc_html_e( 'Twitter Description', 'raghushree' ); ?></label></th>
			<td><textarea id="raghushree_tw_desc" name="raghushree_tw_desc" rows="2" class="large-text"><?php echo esc_textarea( $meta['tw_desc'] ); ?></textarea></td>
		</tr>
		<tr>
			<th><label for="raghushree_tw_image"><?php esc_html_e( 'Twitter Image URL', 'raghushree' ); ?></label></th>
			<td><input type="url" id="raghushree_tw_image" name="raghushree_tw_image" value="<?php echo esc_url( $meta['tw_image'] ); ?>" class="large-text" /></td>
		</tr>
	</table>
	<?php
}

/**
 * Save sanitized SEO Meta Box fields.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function raghushree_save_seo_meta_box( $post_id ) {
	if ( ! isset( $_POST['raghushree_seo_meta_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['raghushree_seo_meta_nonce'] ) ), 'raghushree_save_seo_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text_keys = array( 'seo_title', 'seo_robots', 'og_title', 'tw_title' );
	foreach ( $text_keys as $k ) {
		$field = 'raghushree_' . $k;
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_raghushree_' . $k, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	$desc_keys = array( 'seo_desc', 'og_desc', 'tw_desc' );
	foreach ( $desc_keys as $k ) {
		$field = 'raghushree_' . $k;
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_raghushree_' . $k, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	$url_keys = array( 'seo_canonical', 'og_image', 'tw_image' );
	foreach ( $url_keys as $k ) {
		$field = 'raghushree_' . $k;
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_raghushree_' . $k, esc_url_raw( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}
add_action( 'save_post', 'raghushree_save_seo_meta_box' );

/**
 * Resolve complete SEO metadata with sensible fallbacks for the current request.
 *
 * @return array<string, string>
 */
function raghushree_resolve_current_seo() {
	$company   = raghushree_get_company_info();
	$site_name = $company['brand_name'];

	$title       = '';
	$description = 'Raghushree Plast Products Pvt. Ltd. — manufacturer, exporter, and supplier of plastic raw materials including PP, HDPE, LDPE, LLDPE, PVC, PET, and recycled polymer grades.';
	$canonical   = home_url( '/' );
	$robots      = 'index,follow';
	$og_type     = 'website';
	$image       = raghushree_get_image_url( 'home_hero' );

	if ( is_front_page() ) {
		$front_id  = (int) get_option( 'page_on_front' );
		$title     = 'Raghushree — Plastic Raw Materials Manufacturer, Exporter & Supplier';
		$canonical = home_url( '/' );
		if ( $front_id ) {
			$custom_t = get_post_meta( $front_id, '_raghushree_seo_title', true );
			$custom_d = get_post_meta( $front_id, '_raghushree_seo_desc', true );
			if ( $custom_t ) {
				$title = $custom_t;
			}
			if ( $custom_d ) {
				$description = $custom_d;
			}
		}
	} elseif ( is_post_type_archive( 'products' ) ) {
		$title       = 'Polymer Products & Plastic Raw Materials | ' . $site_name;
		$description = 'Explore plastic raw materials supplied by Raghushree including Polypropylene (PP), HDPE, LDPE, LLDPE, PVC, PET, and recycled & reprocessed polymer grades.';
		$canonical   = raghushree_get_products_url();
		$image       = raghushree_get_image_url( 'products_hero' );
	} elseif ( is_tax( array( 'polymer_category', 'material_grade', 'material_form', 'manufacturing_application' ) ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$title       = $term->name . ' — Plastic Raw Materials | ' . $site_name;
			$description = ! empty( $term->description ) ? wp_strip_all_tags( $term->description ) : $description;
			$term_link   = get_term_link( $term );
			if ( ! is_wp_error( $term_link ) ) {
				$canonical = $term_link;
			}
		}
	} elseif ( is_singular() ) {
		$post_id   = get_queried_object_id();
		$canonical = (string) get_permalink( $post_id );
		$title     = get_the_title( $post_id ) . ' | ' . $site_name;

		if ( is_singular( 'products' ) ) {
			$prod        = raghushree_get_product_data( $post_id );
			$description = ! empty( $prod['short_desc'] ) ? $prod['short_desc'] : $description;
			$image       = raghushree_get_image_url( $prod['image_slot'], $post_id );
			$og_type     = 'product';
		} elseif ( is_page_template( 'page-templates/template-about.php' ) || is_page( 'about' ) ) {
			$title       = 'About Raghushree | Plastic Raw Materials Manufacturer & Exporter';
			$description = 'Learn about Raghushree Plast Products Pvt. Ltd., Kanpur, India — supplying PP, HDPE, LDPE, LLDPE, PVC, PET, and recycled polymer raw materials for industrial manufacturers.';
			$image       = raghushree_get_image_url( 'about_hero', $post_id );
		} elseif ( is_page_template( 'page-templates/template-contact.php' ) || is_page( 'contact' ) ) {
			$title       = 'Contact & Request a Quotation | ' . $site_name;
			$description = 'Contact Raghushree Plast Products Pvt. Ltd. in Kanpur, India to request polymer quotations, check grade availability, request material samples, or discuss bulk supply.';
			$image       = raghushree_get_image_url( 'home_hero', $post_id );
		} elseif ( has_excerpt( $post_id ) ) {
			$description = wp_strip_all_tags( get_the_excerpt( $post_id ) );
		}

		// Apply explicit Meta Box overrides if set
		$m_title = get_post_meta( $post_id, '_raghushree_seo_title', true );
		$m_desc  = get_post_meta( $post_id, '_raghushree_seo_desc', true );
		$m_canon = get_post_meta( $post_id, '_raghushree_seo_canonical', true );
		$m_robot = get_post_meta( $post_id, '_raghushree_seo_robots', true );

		if ( ! empty( $m_title ) ) {
			$title = $m_title;
		}
		if ( ! empty( $m_desc ) ) {
			$description = $m_desc;
		}
		if ( ! empty( $m_canon ) ) {
			$canonical = $m_canon;
		}
		if ( ! empty( $m_robot ) ) {
			$robots = $m_robot;
		}
	} elseif ( is_search() ) {
		$title     = sprintf( 'Search Results for "%s" | %s', get_search_query(), $site_name );
		$robots    = 'noindex,follow';
		$canonical = get_search_link();
	} elseif ( is_404() ) {
		$title  = 'Page Not Found | ' . $site_name;
		$robots = 'noindex,follow';
	}

	$post_id  = is_singular() ? get_queried_object_id() : 0;
	$og_title = ( $post_id && get_post_meta( $post_id, '_raghushree_og_title', true ) ) ? get_post_meta( $post_id, '_raghushree_og_title', true ) : $title;
	$og_desc  = ( $post_id && get_post_meta( $post_id, '_raghushree_og_desc', true ) ) ? get_post_meta( $post_id, '_raghushree_og_desc', true ) : $description;
	$og_img   = ( $post_id && get_post_meta( $post_id, '_raghushree_og_image', true ) ) ? get_post_meta( $post_id, '_raghushree_og_image', true ) : $image;

	$tw_title = ( $post_id && get_post_meta( $post_id, '_raghushree_tw_title', true ) ) ? get_post_meta( $post_id, '_raghushree_tw_title', true ) : $og_title;
	$tw_desc  = ( $post_id && get_post_meta( $post_id, '_raghushree_tw_desc', true ) ) ? get_post_meta( $post_id, '_raghushree_tw_desc', true ) : $og_desc;
	$tw_img   = ( $post_id && get_post_meta( $post_id, '_raghushree_tw_image', true ) ) ? get_post_meta( $post_id, '_raghushree_tw_image', true ) : $og_img;

	return array(
		'site_name'   => $site_name,
		'title'       => $title,
		'description' => $description,
		'canonical'   => $canonical,
		'robots'      => $robots,
		'og_type'     => $og_type,
		'og_title'    => $og_title,
		'og_desc'     => $og_desc,
		'og_image'    => $og_img,
		'tw_title'    => $tw_title,
		'tw_desc'     => $tw_desc,
		'tw_image'    => $tw_img,
	);
}

/**
 * Remove core duplicate canonical/robots hooks when Raghushree native SEO is active.
 *
 * @return void
 */
function raghushree_setup_native_seo_hooks() {
	if ( raghushree_has_seo_plugin() ) {
		return;
	}
	remove_action( 'wp_head', 'rel_canonical' );
	remove_filter( 'wp_robots', 'wp_robots_max_image_preview_large' );
}
add_action( 'init', 'raghushree_setup_native_seo_hooks', 20 );

/**
 * Filter document `<title>` cleanly when no SEO plugin is active.
 *
 * @param string $title Existing title.
 * @return string
 */
function raghushree_filter_document_title( $title ) {
	if ( raghushree_has_seo_plugin() || is_admin() ) {
		return $title;
	}
	$seo = raghushree_resolve_current_seo();
	return ! empty( $seo['title'] ) ? $seo['title'] : $title;
}
add_filter( 'pre_get_document_title', 'raghushree_filter_document_title', 20 );

/**
 * Output Meta Description, Canonical, Robots, Open Graph & Twitter tags in `<head>`.
 *
 * @return void
 */
function raghushree_output_seo_meta_tags() {
	if ( raghushree_has_seo_plugin() ) {
		return;
	}

	$seo = raghushree_resolve_current_seo();

	if ( ! empty( $seo['description'] ) ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $seo['description'] ) );
	}
	if ( ! empty( $seo['robots'] ) ) {
		printf( '<meta name="robots" content="%s, max-image-preview:large" />' . "\n", esc_attr( $seo['robots'] ) );
	}
	if ( ! empty( $seo['canonical'] ) && ! is_404() ) {
		printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $seo['canonical'] ) );
	}

	// Open Graph Metadata
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( $seo['site_name'] ) );
	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( $seo['og_type'] ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $seo['og_title'] ) );
	printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $seo['og_desc'] ) );
	if ( ! empty( $seo['canonical'] ) && ! is_404() ) {
		printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $seo['canonical'] ) );
	}
	if ( ! empty( $seo['og_image'] ) ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $seo['og_image'] ) );
	}

	// Twitter Card Metadata
	$card_type = ! empty( $seo['tw_image'] ) ? 'summary_large_image' : 'summary';
	printf( '<meta name="twitter:card" content="%s" />' . "\n", esc_attr( $card_type ) );
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $seo['tw_title'] ) );
	printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $seo['tw_desc'] ) );
	if ( ! empty( $seo['tw_image'] ) ) {
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $seo['tw_image'] ) );
	}
}
add_action( 'wp_head', 'raghushree_output_seo_meta_tags', 5 );

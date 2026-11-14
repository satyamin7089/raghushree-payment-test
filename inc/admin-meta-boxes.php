<?php
/**
 * WordPress Admin Meta Boxes for `products` CPT and `raghushree_enquiry` Log.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register product data and enquiry details meta boxes.
 *
 * @return void
 */
function raghushree_register_admin_meta_boxes() {
	add_meta_box(
		'raghushree_product_specs_box',
		__( 'Raghushree Polymer Product Specifications & Supply Data', 'raghushree' ),
		'raghushree_render_product_meta_box',
		'products',
		'normal',
		'high'
	);

	add_meta_box(
		'raghushree_enquiry_details_box',
		__( 'Submitted Buyer Enquiry Details', 'raghushree' ),
		'raghushree_render_enquiry_meta_box',
		'raghushree_enquiry',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'raghushree_register_admin_meta_boxes' );
function raghushree_register_page_product_content_box( $post ) {
	if ( 'page-templates/template-product-detail.php' === get_page_template_slug( $post ) ) {
		add_meta_box( 'raghushree_product_specs_box', __( 'Product Content & Images', 'raghushree' ), 'raghushree_render_product_meta_box', 'page', 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes_page', 'raghushree_register_page_product_content_box' );
function raghushree_product_content_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'products', 'page' ), true ) ) { return; }
	if ( 'page' === $screen->post_type ) { $post_id = isset( $_GET['post'] ) && is_scalar( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; if ( ! $post_id || 'page-templates/template-product-detail.php' !== get_page_template_slug( $post_id ) ) { return; } }
	wp_enqueue_media();
	wp_enqueue_script( 'raghushree-product-gallery-editor', get_template_directory_uri() . '/assets/js/product-gallery-admin.js', array( 'media-views' ), raghushree_asset_version( 'assets/js/product-gallery-admin.js' ), true );
	wp_enqueue_style( 'raghushree-product-gallery-editor', get_template_directory_uri() . '/assets/css/product-gallery-admin.css', array(), raghushree_asset_version( 'assets/css/product-gallery-admin.css' ) );
}
add_action( 'admin_enqueue_scripts', 'raghushree_product_content_admin_assets' );

/**
 * Render the Product Meta Box in WP Admin.
 *
 * @param WP_Post $post Current product post object.
 * @return void
 */
function raghushree_render_product_meta_box( $post ) {
	wp_nonce_field( 'raghushree_save_product_meta', 'raghushree_product_meta_nonce' );

	$fields = array(
		'polymer_code'      => get_post_meta( $post->ID, '_raghushree_polymer_code', true ),
		'image_slot'        => get_post_meta( $post->ID, '_raghushree_image_slot', true ),
		'short_desc'        => get_post_meta( $post->ID, '_raghushree_short_desc', true ),
		'highlights'        => get_post_meta( $post->ID, '_raghushree_highlights', true ),
		'grades_notes'      => get_post_meta( $post->ID, '_raghushree_grades_notes', true ),
		'grades_rows'       => get_post_meta( $post->ID, '_raghushree_grades_rows', true ),
		'applications_rows' => get_post_meta( $post->ID, '_raghushree_applications_rows', true ),
		'industries_rows'   => get_post_meta( $post->ID, '_raghushree_industries_rows', true ),
		'processing_rows'   => get_post_meta( $post->ID, '_raghushree_processing_rows', true ),
		'tech_specs'        => get_post_meta( $post->ID, '_raghushree_tech_specs', true ),
		'datasheet_url'     => get_post_meta( $post->ID, '_raghushree_datasheet_url', true ),
		'packaging'         => get_post_meta( $post->ID, '_raghushree_packaging', true ),
		'pack_size'         => get_post_meta( $post->ID, '_raghushree_pack_size', true ),
		'moq'               => get_post_meta( $post->ID, '_raghushree_moq', true ),
		'availability'      => get_post_meta( $post->ID, '_raghushree_availability', true ),
		'gallery_ids'       => get_post_meta( $post->ID, '_raghushree_gallery_ids', true ),
		'related_ids'       => get_post_meta( $post->ID, '_raghushree_related_ids', true ),
		'product_faqs'      => get_post_meta( $post->ID, '_raghushree_product_faqs', true ),
	);
	?>
	<p class="description">
		<?php esc_html_e( 'Leave any unverified field blank — empty fields are automatically hidden on the frontend rather than displaying placeholder claims.', 'raghushree' ); ?>
	</p>
	<p><label for="raghushree_short_desc"><strong><?php esc_html_e( 'Short card and product introduction', 'raghushree' ); ?></strong></label><br><textarea id="raghushree_short_desc" name="raghushree_short_desc" rows="3" class="large-text"><?php echo esc_textarea( $fields['short_desc'] ); ?></textarea><br><span class="description"><?php esc_html_e( 'Shown on the product card and at the top of this page. If empty, the excerpt or main description is used.', 'raghushree' ); ?></span></p>
	<p><label for="raghushree_highlights"><strong><?php esc_html_e( 'Product highlights', 'raghushree' ); ?></strong></label><br><textarea id="raghushree_highlights" name="raghushree_highlights" rows="4" class="large-text"><?php echo esc_textarea( $fields['highlights'] ); ?></textarea><br><span class="description"><?php esc_html_e( 'Optional: one point per line. These points appear below the introduction. Add your paragraphs, headings and lists in the main product editor above.', 'raghushree' ); ?></span></p>
	<?php
	$gallery_items = array();
	foreach ( explode( ',', (string) $fields['gallery_ids'] ) as $id ) {
		$id = absint( trim( $id ) );
		if ( $id && ! isset( $gallery_items[$id] ) ) { $gallery_items[$id] = array( 'id' => $id, 'url' => wp_get_attachment_image_url( $id, 'thumbnail' ) ?: '' ); }
	}
	?>
	<div class="rg-gallery-editor" data-gallery-items="<?php echo esc_attr( wp_json_encode( array_values( $gallery_items ) ) ); ?>">
		<p><strong><?php esc_html_e( 'Additional product images', 'raghushree' ); ?></strong><br><span class="description"><?php esc_html_e( 'Choose images from your Media Library for the product-page slider. Set the main card image separately as the Featured Image.', 'raghushree' ); ?></span></p>
		<div data-gallery-preview class="rg-gallery-preview"></div>
		<p><button type="button" class="button" data-gallery-add hidden><?php esc_html_e( 'Choose images', 'raghushree' ); ?></button></p>
		<p data-gallery-fallback><label for="raghushree_gallery_ids"><?php esc_html_e( 'Gallery attachment IDs (comma separated; used if JavaScript is unavailable)', 'raghushree' ); ?></label><br><input type="text" id="raghushree_gallery_ids" name="raghushree_gallery_ids" value="<?php echo esc_attr( $fields['gallery_ids'] ); ?>" class="regular-text" /></p>
	</div>
	<details><summary><strong><?php esc_html_e( 'Additional structured details (optional)', 'raghushree' ); ?></strong></summary><p class="description"><?php esc_html_e( 'Use these optional fields for tables and FAQs. Existing values are kept. You can also write all your details as normal paragraphs or lists in the main editor.', 'raghushree' ); ?></p>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="raghushree_polymer_code"><?php esc_html_e( 'Polymer Short Code', 'raghushree' ); ?></label></th>
			<td><input type="text" id="raghushree_polymer_code" name="raghushree_polymer_code" value="<?php echo esc_attr( $fields['polymer_code'] ); ?>" class="regular-text" placeholder="e.g. PP, HDPE, LDPE" /></td>
		</tr>
		<tr>
			<th><label for="raghushree_image_slot"><?php esc_html_e( 'Placeholder Image Key', 'raghushree' ); ?></label></th>
			<td>
				<select id="raghushree_image_slot" name="raghushree_image_slot">
					<?php foreach ( raghushree_get_placeholder_registry() as $slot_key => $slot_info ) : ?>
						<option value="<?php echo esc_attr( $slot_key ); ?>" <?php selected( $fields['image_slot'], $slot_key ); ?>>
							<?php echo esc_html( $slot_info['label'] . ' (' . $slot_key . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Used as a card fallback until you upload a Featured Image. The product page uses a neutral image area when no product photos are set.', 'raghushree' ); ?></p>
			</td>
		</tr>

		<tr>
			<th><label for="raghushree_grades_notes"><?php esc_html_e( 'Grades & Forms Overview', 'raghushree' ); ?></label></th>
			<td><textarea id="raghushree_grades_notes" name="raghushree_grades_notes" rows="3" class="large-text"><?php echo esc_textarea( $fields['grades_notes'] ); ?></textarea></td>
		</tr>
		<tr>
			<th><label for="raghushree_grades_rows"><?php esc_html_e( 'Verified Grades Table (Optional)', 'raghushree' ); ?></label></th>
			<td>
				<textarea id="raghushree_grades_rows" name="raghushree_grades_rows" rows="4" class="large-text" placeholder="Grade / Category | Material Form | Typical Processing Use"><?php echo esc_textarea( $fields['grades_rows'] ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One verified grade per line: Grade Name | Form | Typical Use', 'raghushree' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="raghushree_applications_rows"><?php esc_html_e( 'Applications (Title | Description)', 'raghushree' ); ?></label></th>
			<td><textarea id="raghushree_applications_rows" name="raghushree_applications_rows" rows="4" class="large-text"><?php echo esc_textarea( $fields['applications_rows'] ); ?></textarea></td>
		</tr>
		<tr>
			<th><label for="raghushree_industries_rows"><?php esc_html_e( 'Target Industries (One per line)', 'raghushree' ); ?></label></th>
			<td><textarea id="raghushree_industries_rows" name="raghushree_industries_rows" rows="3" class="large-text"><?php echo esc_textarea( $fields['industries_rows'] ); ?></textarea></td>
		</tr>
		<tr>
			<th><label for="raghushree_processing_rows"><?php esc_html_e( 'Processing Methods (Method | Notes)', 'raghushree' ); ?></label></th>
			<td><textarea id="raghushree_processing_rows" name="raghushree_processing_rows" rows="4" class="large-text"><?php echo esc_textarea( $fields['processing_rows'] ); ?></textarea></td>
		</tr>
		<tr>
			<th><label for="raghushree_tech_specs"><?php esc_html_e( 'Verified Technical Specs (Optional)', 'raghushree' ); ?></label></th>
			<td>
				<textarea id="raghushree_tech_specs" name="raghushree_tech_specs" rows="4" class="large-text" placeholder="Parameter | Value | Test Standard"><?php echo esc_textarea( $fields['tech_specs'] ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Leave blank unless verified technical values are confirmed.', 'raghushree' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="raghushree_datasheet_url"><?php esc_html_e( 'Datasheet PDF URL (Optional)', 'raghushree' ); ?></label></th>
			<td><input type="url" id="raghushree_datasheet_url" name="raghushree_datasheet_url" value="<?php echo esc_url( $fields['datasheet_url'] ); ?>" class="large-text" placeholder="https://..." /></td>
		</tr>
		<tr>
			<th><label for="raghushree_packaging"><?php esc_html_e( 'Verified Packaging Format', 'raghushree' ); ?></label></th>
			<td><input type="text" id="raghushree_packaging" name="raghushree_packaging" value="<?php echo esc_attr( $fields['packaging'] ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="raghushree_pack_size"><?php esc_html_e( 'Verified Pack Size', 'raghushree' ); ?></label></th>
			<td><input type="text" id="raghushree_pack_size" name="raghushree_pack_size" value="<?php echo esc_attr( $fields['pack_size'] ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="raghushree_moq"><?php esc_html_e( 'Verified Minimum Order Quantity (MOQ)', 'raghushree' ); ?></label></th>
			<td><input type="text" id="raghushree_moq" name="raghushree_moq" value="<?php echo esc_attr( $fields['moq'] ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="raghushree_availability"><?php esc_html_e( 'Verified Availability Note', 'raghushree' ); ?></label></th>
			<td><input type="text" id="raghushree_availability" name="raghushree_availability" value="<?php echo esc_attr( $fields['availability'] ); ?>" class="regular-text" /></td>
		</tr>

		<tr>
			<th><label for="raghushree_product_faqs"><?php esc_html_e( 'Product FAQs (Question | Answer)', 'raghushree' ); ?></label></th>
			<td><textarea id="raghushree_product_faqs" name="raghushree_product_faqs" rows="4" class="large-text"><?php echo esc_textarea( $fields['product_faqs'] ); ?></textarea></td>
		</tr>
	</table></details>
	<?php
}

/**
 * Save sanitized Product Meta Box values.
 *
 * @param int $post_id Product post ID.
 * @return void
 */
function raghushree_save_product_meta_box( $post_id ) {
	if ( ! isset( $_POST['raghushree_product_meta_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['raghushree_product_meta_nonce'] ) ), 'raghushree_save_product_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text_fields = array(
		'polymer_code',
		'image_slot',
		'packaging',
		'pack_size',
		'moq',
		'availability',
		'gallery_ids',
		'related_ids',
	);

	foreach ( $text_fields as $key ) {
		$input_key = 'raghushree_' . $key;
		if ( isset( $_POST[ $input_key ] ) ) {
			update_post_meta( $post_id, '_raghushree_' . $key, sanitize_text_field( wp_unslash( $_POST[ $input_key ] ) ) );
		}
	}

	$textarea_fields = array(
		'short_desc',
		'highlights',
		'grades_notes',
		'grades_rows',
		'applications_rows',
		'industries_rows',
		'processing_rows',
		'tech_specs',
		'product_faqs',
	);

	foreach ( $textarea_fields as $key ) {
		$input_key = 'raghushree_' . $key;
		if ( isset( $_POST[ $input_key ] ) ) {
			update_post_meta( $post_id, '_raghushree_' . $key, sanitize_textarea_field( wp_unslash( $_POST[ $input_key ] ) ) );
		}
	}

	if ( isset( $_POST['raghushree_datasheet_url'] ) ) {
		update_post_meta( $post_id, '_raghushree_datasheet_url', esc_url_raw( wp_unslash( $_POST['raghushree_datasheet_url'] ) ) );
	}
}
add_action( 'save_post_products', 'raghushree_save_product_meta_box' );
add_action( 'save_post_page', 'raghushree_save_product_meta_box' );

/**
 * Render read-only Buyer Enquiry details inside WP Admin.
 *
 * @param WP_Post $post Enquiry post object.
 * @return void
 */
function raghushree_render_enquiry_meta_box( $post ) {
	$keys = array(
		'name'         => __( 'Contact Name', 'raghushree' ),
		'company'      => __( 'Company Name', 'raghushree' ),
		'email'        => __( 'Email Address', 'raghushree' ),
		'phone'        => __( 'Phone / WhatsApp', 'raghushree' ),
		'country'      => __( 'Country', 'raghushree' ),
		'enquiry_type' => __( 'Enquiry Type', 'raghushree' ),
		'material'     => __( 'Polymer / Material', 'raghushree' ),
		'grade'        => __( 'Grade / Specification', 'raghushree' ),
		'quantity'     => __( 'Estimated Quantity', 'raghushree' ),
		'application'  => __( 'Manufacturing Application', 'raghushree' ),
		'message'      => __( 'Buyer Message', 'raghushree' ),
	);
	?>
	<table class="widefat striped" role="presentation">
		<tbody>
			<?php foreach ( $keys as $meta_key => $label ) : ?>
				<tr>
					<th style="width:220px;"><strong><?php echo esc_html( $label ); ?></strong></th>
					<td><?php echo nl2br( esc_html( (string) get_post_meta( $post->ID, '_enquiry_' . $meta_key, true ) ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

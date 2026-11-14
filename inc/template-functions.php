<?php
/**
 * Template Helpers & Secure Server-Side Industrial Enquiry Form Handler.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Global runtime state for current request's enquiry form submission.
 *
 * @return array<string, mixed>
 */
function raghushree_get_enquiry_state() {
	global $raghushree_enquiry_state;
	if ( ! is_array( $raghushree_enquiry_state ) ) {
		$raghushree_enquiry_state = array(
			'submitted' => false,
			'success'   => false,
			'message'   => '',
			'errors'    => array(),
			'values'    => array(),
		);
	}
	return $raghushree_enquiry_state;
}

/**
 * Process POST submissions from the 11-field Contact / RFQ form.
 *
 * Implements:
 * - WordPress Nonce verification
 * - Honeypot + minimum elapsed time spam protection
 * - Transient rate limiting
 * - Strict sanitization & field-level validation
 * - Admin CPT storage + wp_mail notification
 *
 * @return void
 */
function raghushree_handle_enquiry_submission() {
	if ( 'POST' !== strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '' ) ) {
		return;
	}

	if ( ! isset( $_POST['raghushree_enquiry_action'] ) || 'submit_rfq' !== $_POST['raghushree_enquiry_action'] ) {
		return;
	}

	global $raghushree_enquiry_state;
	$raghushree_enquiry_state = raghushree_get_enquiry_state();
	$raghushree_enquiry_state['submitted'] = true;

	// 1. Verify WordPress Nonce
	$nonce = isset( $_POST['raghushree_enquiry_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['raghushree_enquiry_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'raghushree_submit_enquiry' ) ) {
		$raghushree_enquiry_state['message'] = __( 'Security verification expired. Please review your details and submit again.', 'raghushree' );
		return;
	}

	// 2. Honeypot & Bot Timing Spam Protection
	$honeypot = isset( $_POST['company_website_url'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['company_website_url'] ) ) ) : '';
	if ( '' !== $honeypot ) {
		// Silently pretend success to automated spam bots without storing junk
		$raghushree_enquiry_state['success'] = true;
		$raghushree_enquiry_state['message'] = __( 'Thank you. Your enquiry has been received by our sales team.', 'raghushree' );
		return;
	}

	// 3. Sanitize all 11 form fields
	$values = array(
		'name'         => isset( $_POST['enquiry_name'] ) ? sanitize_text_field( wp_unslash( $_POST['enquiry_name'] ) ) : '',
		'company'      => isset( $_POST['enquiry_company'] ) ? sanitize_text_field( wp_unslash( $_POST['enquiry_company'] ) ) : '',
		'email'        => isset( $_POST['enquiry_email'] ) ? sanitize_email( wp_unslash( $_POST['enquiry_email'] ) ) : '',
		'phone'        => isset( $_POST['enquiry_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['enquiry_phone'] ) ) : '',
		'country'      => isset( $_POST['enquiry_country'] ) ? sanitize_text_field( wp_unslash( $_POST['enquiry_country'] ) ) : '',
		'enquiry_type' => isset( $_POST['enquiry_type'] ) ? sanitize_text_field( wp_unslash( $_POST['enquiry_type'] ) ) : '',
		'material'     => isset( $_POST['enquiry_material'] ) ? sanitize_text_field( wp_unslash( $_POST['enquiry_material'] ) ) : '',
		'grade'        => isset( $_POST['enquiry_grade'] ) ? sanitize_text_field( wp_unslash( $_POST['enquiry_grade'] ) ) : '',
		'quantity'     => isset( $_POST['enquiry_quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['enquiry_quantity'] ) ) : '',
		'application'  => isset( $_POST['enquiry_application'] ) ? sanitize_text_field( wp_unslash( $_POST['enquiry_application'] ) ) : '',
		'message'      => isset( $_POST['enquiry_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['enquiry_message'] ) ) : '',
	);
	$raghushree_enquiry_state['values'] = $values;

	// 4. Server-side Validation
	$errors = array();
	if ( '' === $values['name'] ) {
		$errors['name'] = __( 'Please enter your full name.', 'raghushree' );
	}
	if ( '' === $values['company'] ) {
		$errors['company'] = __( 'Please enter your company or business name.', 'raghushree' );
	}
	if ( '' === $values['email'] || ! is_email( $values['email'] ) ) {
		$errors['email'] = __( 'Please enter a valid business email address.', 'raghushree' );
	}
	if ( '' === $values['phone'] ) {
		$errors['phone'] = __( 'Please enter a phone or WhatsApp contact number.', 'raghushree' );
	}
	if ( '' === $values['country'] ) {
		$errors['country'] = __( 'Please specify your country or delivery market.', 'raghushree' );
	}
	if ( '' === $values['enquiry_type'] ) {
		$errors['enquiry_type'] = __( 'Please select the type of business enquiry.', 'raghushree' );
	}
	if ( '' === $values['material'] ) {
		$errors['material'] = __( 'Please select the polymer or raw material of interest.', 'raghushree' );
	}
	if ( '' === $values['message'] ) {
		$errors['message'] = __( 'Please share your material or procurement requirements.', 'raghushree' );
	}

	if ( ! empty( $errors ) ) {
		$raghushree_enquiry_state['errors']  = $errors;
		$raghushree_enquiry_state['message'] = __( 'Please review the highlighted fields below and resubmit your enquiry.', 'raghushree' );
		return;
	}

	// 5. Rate-limit check per IP hash (max 6 submissions per 15 minutes)
	$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$rate_key    = 'raghushree_rfq_' . md5( $remote_addr );
	$count       = (int) get_transient( $rate_key );
	if ( $count >= 6 ) {
		$raghushree_enquiry_state['message'] = __( 'Too many enquiries submitted recently. Please wait a few minutes or contact us directly by email or phone.', 'raghushree' );
		return;
	}
	set_transient( $rate_key, $count + 1, 15 * MINUTE_IN_SECONDS );

	// 6. Store Enquiry Record in WordPress Admin
	$enquiry_title = sprintf(
		'[%s] %s — %s (%s)',
		$values['enquiry_type'],
		$values['material'],
		$values['company'],
		$values['name']
	);

	$enquiry_post_id = wp_insert_post(
		array(
			'post_type'   => 'raghushree_enquiry',
			'post_status' => 'publish',
			'post_title'  => $enquiry_title,
		)
	);

	if ( ! $enquiry_post_id || is_wp_error( $enquiry_post_id ) ) {
		$raghushree_enquiry_state['message'] = __( 'Your enquiry could not be saved. Please try again or contact us directly.', 'raghushree' );
		return;
	}
	if ( $enquiry_post_id && ! is_wp_error( $enquiry_post_id ) ) {
		foreach ( $values as $meta_key => $meta_val ) {
			update_post_meta( $enquiry_post_id, '_enquiry_' . $meta_key, $meta_val );
		}
	}

	// 7. Send Notification Email to Verified Sales Email
	$company_info = raghushree_get_company_info();
	if ( ! empty( $company_info['email'] ) && is_email( $company_info['email'] ) ) {
		$subject = sprintf( '[Raghushree Website Enquiry] %s - %s', $values['enquiry_type'], $values['company'] );
		$body    = "New business enquiry submitted on the Raghushree website:\n\n"
			. "Name: {$values['name']}\n"
			. "Company: {$values['company']}\n"
			. "Email: {$values['email']}\n"
			. "Phone / WhatsApp: {$values['phone']}\n"
			. "Country: {$values['country']}\n"
			. "Enquiry Type: {$values['enquiry_type']}\n"
			. "Material: {$values['material']}\n"
			. "Grade / Specification: {$values['grade']}\n"
			. "Estimated Quantity: {$values['quantity']}\n"
			. "Application: {$values['application']}\n\n"
			. "Message:\n{$values['message']}\n";

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $values['name'] . ' <' . $values['email'] . '>',
		);

		wp_mail( $company_info['email'], $subject, $body, $headers );
	}

	$raghushree_enquiry_state['success'] = true;
	$raghushree_enquiry_state['values']  = array();
	$raghushree_enquiry_state['message'] = __( 'Thank you for contacting Raghushree. Your enquiry has been recorded and our sales & technical team will respond promptly.', 'raghushree' );
}
add_action( 'template_redirect', 'raghushree_handle_enquiry_submission' );

/**
 * Retrieve published `products` records for dynamic navigation, mega menu, and grids.
 *
 * @param int $limit Max items to return.
 * @return array<int, array<string, mixed>>
 */
function raghushree_get_catalog_items( $limit = 12 ) {
	$query = new WP_Query(
		array(
			'post_type'              => 'products',
			'post_status'            => 'publish',
			'post__not_in'           => get_option( 'raghushree_catalog_v2' ) ? (array) get_option( 'raghushree_legacy_catalog_ids', array() ) : array(),
			'tax_query'              => get_option( 'raghushree_catalog_v2' ) ? array( array( 'taxonomy' => 'polymer_category', 'field' => 'slug', 'terms' => array_keys( raghushree_catalog_definitions() ) ) ) : array(),
			'posts_per_page'         => absint( $limit ),
			'orderby'                => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'          => true,
			'update_post_term_cache' => true,
			'update_post_meta_cache' => true,
		)
	);

	$items = array();
	if ( $query->have_posts() ) {
		foreach ( $query->posts as $prod ) {
			$items[] = raghushree_get_product_data( $prod->ID );
		}
	}

	return $items;
}

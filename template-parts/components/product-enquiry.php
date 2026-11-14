<?php
/** On-page enquiry uses the existing nonce-protected, rate-limited enquiry handler. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$state = raghushree_get_enquiry_state();
$values = isset( $state['values'] ) && is_array( $state['values'] ) ? $state['values'] : array();
$errors = isset( $state['errors'] ) && is_array( $state['errors'] ) ? $state['errors'] : array();
?>
<section class="section product-enquiry single-product-section" id="product-enquiry"><div class="container product-editorial">
	<div class="product-editorial__heading"><span class="eyebrow"><?php esc_html_e( 'Enquiry', 'raghushree' ); ?></span><h2><?php esc_html_e( 'Ask about this product', 'raghushree' ); ?></h2></div>
	<div class="product-enquiry__card">
		<?php if ( ! empty( $state['submitted'] ) && ! empty( $state['message'] ) ) : ?><div class="product-enquiry__notice" role="alert"><?php echo esc_html( $state['message'] ); ?><?php if ( $errors ) : ?><ul><?php foreach ( $errors as $error ) : ?><li><?php echo esc_html( $error ); ?></li><?php endforeach; ?></ul><?php endif; ?></div><?php endif; ?>
		<form method="post" action="<?php echo esc_url( get_permalink( $args['product_id'] ) . '#product-enquiry' ); ?>">
			<?php wp_nonce_field( 'raghushree_submit_enquiry', 'raghushree_enquiry_nonce' ); ?>
			<input type="hidden" name="raghushree_enquiry_action" value="submit_rfq"><input type="hidden" name="enquiry_type" value="Request a Quotation"><input type="hidden" name="enquiry_material" value="<?php echo esc_attr( $args['product_title'] ); ?>">
			<div class="raghushree-hp" aria-hidden="true"><label for="product-company-website"><?php esc_html_e( 'Leave this field blank', 'raghushree' ); ?></label><input type="text" id="product-company-website" name="company_website_url" tabindex="-1" autocomplete="off"></div>
			<div class="product-enquiry__grid">
			<?php foreach ( array( 'name' => array( __( 'Full name', 'raghushree' ), 'text', 'name' ), 'company' => array( __( 'Company or business', 'raghushree' ), 'text', 'organization' ), 'email' => array( __( 'Email', 'raghushree' ), 'email', 'email' ), 'phone' => array( __( 'Phone / WhatsApp', 'raghushree' ), 'tel', 'tel' ), 'country' => array( __( 'Country / market', 'raghushree' ), 'text', 'country-name' ) ) as $key => $field ) : ?>
			<label><?php echo esc_html( $field[0] ); ?> <span aria-hidden="true">*</span><input type="<?php echo esc_attr( $field[1] ); ?>" name="enquiry_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $values[$key] ?? '' ); ?>" autocomplete="<?php echo esc_attr( $field[2] ); ?>" required <?php echo isset( $errors[$key] ) ? 'aria-invalid="true"' : ''; ?>></label><?php endforeach; ?>
			<label class="product-enquiry__message"><?php esc_html_e( 'Your requirements', 'raghushree' ); ?> <span aria-hidden="true">*</span><textarea name="enquiry_message" required <?php echo isset( $errors['message'] ) ? 'aria-invalid="true"' : ''; ?>><?php echo esc_textarea( $values['message'] ?? '' ); ?></textarea></label></div>
			<button class="btn btn--primary product-enquiry__submit" type="submit"><?php esc_html_e( 'Send enquiry', 'raghushree' ); ?></button>
		</form>
	</div>
</div></section>

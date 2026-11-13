<?php
/**
 * Global Site Footer — Shared Across All Pages.
 *
 * Uses verified company contact details and conditionally renders social links
 * ONLY when actual URLs are configured in WordPress Customizer.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$raghushree_company       = raghushree_get_company_info();
$raghushree_catalog_items = raghushree_get_catalog_items( 7 );
$raghushree_products_url  = raghushree_get_products_url();
$raghushree_about_url     = raghushree_get_page_url( 'about' );
$raghushree_contact_url   = raghushree_get_page_url( 'contact' );
$raghushree_whatsapp_url  = raghushree_get_whatsapp_url();
$privacy_policy_url       = get_privacy_policy_url();
?>

<footer class="site-footer" id="site-footer">
	<div class="site-footer__main">
		<div class="container site-footer__grid">
			<!-- Column 1: Brand & Introduction -->
			<div>
				<a class="site-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $raghushree_company['brand_name'] . ' Home' ); ?>">
					<img
						class="site-footer__logo"
						src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-wht.png' ); ?>"
						width="300"
						height="76"
						alt="<?php echo esc_attr( $raghushree_company['legal_name'] ); ?>"
						loading="lazy"
						decoding="async"
					/>
				</a>
				<p class="site-footer__intro">
					<?php esc_html_e( 'Manufacturer, exporter, and supplier of plastic raw materials serving industrial manufacturers, moulders, packaging converters, and distributors across India and global markets.', 'raghushree' ); ?>
				</p>
				<span class="site-footer__Badge">
					<?php echo raghushree_icon( 'globe' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php esc_html_e( 'Domestic India & International Export Supply', 'raghushree' ); ?></span>
				</span>
			</div>

			<!-- Column 2: Polymer Portfolio Links -->
			<nav aria-label="<?php esc_attr_e( 'Polymer Portfolio', 'raghushree' ); ?>">
				<h2 class="site-footer__heading"><?php esc_html_e( 'Polymer Portfolio', 'raghushree' ); ?></h2>
				<ul class="site-footer__links">
					<?php if ( ! empty( $raghushree_catalog_items ) ) : ?>
						<?php foreach ( $raghushree_catalog_items as $f_item ) : ?>
							<li>
								<a href="<?php echo esc_url( $f_item['permalink'] ); ?>">
									<?php echo esc_html( $f_item['title'] ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
					<li>
						<a href="<?php echo esc_url( $raghushree_products_url ); ?>">
							<?php esc_html_e( 'View All Polymer Products', 'raghushree' ); ?>
						</a>
					</li>
				</ul>
			</nav>

			<!-- Column 3: Important Page & Conversion Links -->
			<nav aria-label="<?php esc_attr_e( 'Company & Enquiries', 'raghushree' ); ?>">
				<h2 class="site-footer__heading"><?php esc_html_e( 'Quick Links', 'raghushree' ); ?></h2>
				<ul class="site-footer__links">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'raghushree' ); ?></a></li>
					<li><a href="<?php echo esc_url( $raghushree_about_url ); ?>"><?php esc_html_e( 'About Raghushree', 'raghushree' ); ?></a></li>
					<li><a href="<?php echo esc_url( $raghushree_products_url ); ?>"><?php esc_html_e( 'Products Catalog', 'raghushree' ); ?></a></li>
					<li><a href="<?php echo esc_url( raghushree_get_enquiry_url( array( 'enquiry_type' => 'Request a Quotation' ) ) ); ?>"><?php esc_html_e( 'Request a Quotation', 'raghushree' ); ?></a></li>
					<li><a href="<?php echo esc_url( raghushree_get_enquiry_url( array( 'enquiry_type' => 'Sample Request' ) ) ); ?>"><?php esc_html_e( 'Request a Sample', 'raghushree' ); ?></a></li>
					<li><a href="<?php echo esc_url( raghushree_get_enquiry_url( array( 'enquiry_type' => 'Bulk Order Enquiry' ) ) ); ?>"><?php esc_html_e( 'Bulk Order Enquiry', 'raghushree' ); ?></a></li>
					<li><a href="<?php echo esc_url( raghushree_get_enquiry_url( array( 'enquiry_type' => 'Distributor Enquiry' ) ) ); ?>"><?php esc_html_e( 'Distributor Enquiry', 'raghushree' ); ?></a></li>
					<li><a href="<?php echo esc_url( $raghushree_contact_url ); ?>"><?php esc_html_e( 'Contact Sales Team', 'raghushree' ); ?></a></li>
				</ul>
			</nav>

			<!-- Column 4: Verified Contact Details -->
			<div>
				<h2 class="site-footer__heading"><?php esc_html_e( 'Contact & Registered Office', 'raghushree' ); ?></h2>
				<div class="site-footer__contact-card">
					<?php if ( ! empty( $raghushree_company['legal_name'] ) ) : ?>
						<div class="site-footer__company-legal"><?php echo esc_html( $raghushree_company['legal_name'] ); ?></div>
					<?php endif; ?>

					<?php if ( ! empty( $raghushree_company['full_address'] ) ) : ?>
						<address class="site-footer__address">
							<?php echo esc_html( $raghushree_company['full_address'] ); ?>
						</address>
					<?php endif; ?>

					<div class="site-footer__contact-list">
						<?php if ( ! empty( $raghushree_company['phone'] ) && ! empty( $raghushree_company['phone_href'] ) ) : ?>
							<a class="site-footer__contact-link" href="<?php echo esc_url( $raghushree_company['phone_href'] ); ?>">
								<?php echo raghushree_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php echo esc_html( $raghushree_company['phone'] ); ?></span>
							</a>
						<?php endif; ?>

						<?php if ( ! empty( $raghushree_company['email'] ) ) : ?>
							<a class="site-footer__contact-link" href="mailto:<?php echo esc_attr( $raghushree_company['email'] ); ?>">
								<?php echo raghushree_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php echo esc_html( $raghushree_company['email'] ); ?></span>
							</a>
						<?php endif; ?>

						<?php if ( ! empty( $raghushree_whatsapp_url ) ) : ?>
							<a class="site-footer__contact-link" href="<?php echo esc_url( $raghushree_whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo raghushree_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php esc_html_e( 'Start WhatsApp Conversation', 'raghushree' ); ?></span>
							</a>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $raghushree_company['socials'] ) ) : ?>
						<div class="site-footer__socials" aria-label="<?php esc_attr_e( 'Social Media Links', 'raghushree' ); ?>">
							<?php foreach ( $raghushree_company['socials'] as $soc ) : ?>
								<a class="site-footer__social-link" href="<?php echo esc_url( $soc['url'] ); ?>" target="_blank" rel="noopener noreferrer">
									<?php echo esc_html( $soc['label'] ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="site-footer__bottom">
		<div class="container site-footer__bottom-inner">
			<div>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( rtrim( $raghushree_company['legal_name'], '.' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'raghushree' ); ?>
			</div>
			<div class="site-footer__legal-links">
				<?php if ( ! empty( $privacy_policy_url ) ) : ?>
					<a href="<?php echo esc_url( $privacy_policy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'raghushree' ); ?></a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $raghushree_products_url ); ?>"><?php esc_html_e( 'Polymer Catalog', 'raghushree' ); ?></a>
				<a href="<?php echo esc_url( $raghushree_contact_url ); ?>"><?php esc_html_e( 'Business Enquiries', 'raghushree' ); ?></a>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

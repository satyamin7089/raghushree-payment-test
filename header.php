<?php
/**
 * Global Site Header, Accessible Product Mega Menu & Responsive Mobile Drawer.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$raghushree_company       = raghushree_get_company_info();
$raghushree_catalog_items = raghushree_get_catalog_items( 8 );
$raghushree_products_url  = raghushree_get_products_url();
$raghushree_about_url     = raghushree_get_page_url( 'about' );
$raghushree_contact_url   = raghushree_get_page_url( 'contact' );
$raghushree_quote_url     = raghushree_get_enquiry_url( array( 'enquiry_type' => 'Request a Quotation' ) );
$raghushree_whatsapp_url  = raghushree_get_whatsapp_url();

$is_home_active     = is_front_page();
$is_products_active = is_post_type_archive( 'products' ) || is_singular( 'products' ) || is_tax( array( 'polymer_category', 'material_grade', 'material_form' ) ) || is_page_template( 'page-templates/template-products.php' );
$is_about_active    = is_page_template( 'page-templates/template-about.php' ) || is_page( 'about' );
$is_contact_active  = is_page_template( 'page-templates/template-contact.php' ) || is_page( 'contact' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main-content"><?php esc_html_e( 'Skip to main content', 'raghushree' ); ?></a>

<?php if ( ! empty( $raghushree_company['email'] ) || ! empty( $raghushree_company['phone'] ) ) : ?>
<div class="utility-bar">
	<div class="container utility-bar__inner">
		<span class="utility-bar__tagline">
			<?php echo esc_html( $raghushree_company['legal_name'] . ' — ' . $raghushree_company['positioning'] ); ?>
		</span>
		<div class="utility-bar__links">
			<?php if ( ! empty( $raghushree_company['email'] ) ) : ?>
				<a class="utility-bar__link" href="mailto:<?php echo esc_attr( $raghushree_company['email'] ); ?>">
					<?php echo raghushree_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( $raghushree_company['email'] ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( ! empty( $raghushree_company['phone'] ) && ! empty( $raghushree_company['phone_href'] ) ) : ?>
				<a class="utility-bar__link" href="<?php echo esc_url( $raghushree_company['phone_href'] ); ?>">
					<?php echo raghushree_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( $raghushree_company['phone'] ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php endif; ?>

<header class="site-header" id="site-header">
	<div class="container site-header__inner">
		<?php if ( has_custom_logo() ) : ?>
			<div class="site-brand site-brand--custom">
				<?php the_custom_logo(); ?>
			</div>
		<?php else : ?>
			<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $raghushree_company['brand_name'] . ' Home' ); ?>">
				<img
					class="site-brand__logo"
					src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-default-334x88.png' ); ?>"
					width="210"
					height="53"
					alt="<?php echo esc_attr( $raghushree_company['legal_name'] ); ?>"
					decoding="async"
					fetchpriority="high"
				/>
			</a>
		<?php endif; ?>

		<nav class="desktop-nav" aria-label="<?php esc_attr_e( 'Primary Navigation', 'raghushree' ); ?>">
			<ul class="desktop-nav__list">
				<li class="desktop-nav__item">
					<a class="desktop-nav__link<?php echo $is_home_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>"<?php echo $is_home_active ? ' aria-current="page"' : ''; ?>>
						<?php esc_html_e( 'Home', 'raghushree' ); ?>
					</a>
				</li>
				<li class="desktop-nav__item" data-mega-wrapper>
					<button
						type="button"
						class="desktop-nav__trigger<?php echo $is_products_active ? ' is-active' : ''; ?>"
						data-mega-trigger
						aria-expanded="false"
						aria-controls="products-mega-menu"
					>
						<span><?php esc_html_e( 'Products', 'raghushree' ); ?></span>
						<?php echo raghushree_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				</li>
				<li class="desktop-nav__item">
					<a class="desktop-nav__link<?php echo $is_about_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $raghushree_about_url ); ?>"<?php echo $is_about_active ? ' aria-current="page"' : ''; ?>>
						<?php esc_html_e( 'About', 'raghushree' ); ?>
					</a>
				</li>
				<li class="desktop-nav__item">
					<a class="desktop-nav__link<?php echo $is_contact_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $raghushree_contact_url ); ?>"<?php echo $is_contact_active ? ' aria-current="page"' : ''; ?>>
						<?php esc_html_e( 'Contact', 'raghushree' ); ?>
					</a>
				</li>
			</ul>
		</nav>

		<div class="site-header__cta">
			<a class="btn btn--primary btn--sm" href="<?php echo esc_url( $raghushree_quote_url ); ?>">
				<span><?php esc_html_e( 'Request a Quote', 'raghushree' ); ?></span>
				<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<button
			type="button"
			class="mobile-toggle"
			data-mobile-toggle
			aria-expanded="false"
			aria-controls="mobile-nav-drawer"
			aria-label="<?php esc_attr_e( 'Open main menu', 'raghushree' ); ?>"
		>
			<span class="icon-menu"><?php echo raghushree_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="icon-close"><?php echo raghushree_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</button>

		<!-- Accessible Product Mega Menu -->
		<div class="mega-menu" id="products-mega-menu" role="region" aria-label="<?php esc_attr_e( 'Polymer Products Menu', 'raghushree' ); ?>" aria-hidden="true">
			<div class="mega-menu__grid">
				<div>
					<div class="mega-menu__header">
						<h2 class="mega-menu__heading"><?php esc_html_e( 'Polymer Raw Material Portfolio', 'raghushree' ); ?></h2>
						<a class="link-arrow" href="<?php echo esc_url( $raghushree_products_url ); ?>">
							<span><?php esc_html_e( 'View All Products', 'raghushree' ); ?></span>
							<?php echo raghushree_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>
					<div class="mega-menu__categories">
						<?php if ( ! empty( $raghushree_catalog_items ) ) : ?>
							<?php foreach ( $raghushree_catalog_items as $m_item ) : ?>
								<a class="mega-menu__item" href="<?php echo esc_url( $m_item['permalink'] ); ?>">
									<span class="mega-menu__item-title">
										<span><?php echo esc_html( $m_item['title'] ); ?></span>
										<?php if ( ! empty( $m_item['polymer_code'] ) ) : ?>
											<span class="mega-menu__item-code"><?php echo esc_html( $m_item['polymer_code'] ); ?></span>
										<?php endif; ?>
									</span>
									<span class="mega-menu__item-desc"><?php echo esc_html( wp_trim_words( $m_item['short_desc'], 13 ) ); ?></span>
								</a>
							<?php endforeach; ?>
						<?php else : ?>
							<a class="mega-menu__item" href="<?php echo esc_url( $raghushree_products_url ); ?>">
								<span class="mega-menu__item-title"><?php esc_html_e( 'Explore Polymer Catalog', 'raghushree' ); ?></span>
								<span class="mega-menu__item-desc"><?php esc_html_e( 'PP, HDPE, LDPE, LLDPE, PVC, PET & Recycled Polymers.', 'raghushree' ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<div class="mega-menu__aside">
					<div>
						<div class="mega-menu__media">
							<?php
							raghushree_render_image(
								'mega_menu_feature',
								array(
									'sizes'    => '360px',
									'priority' => false,
								)
							);
							?>
						</div>
						<h3 class="mega-menu__aside-title"><?php esc_html_e( 'Grade Selection & Bulk Supply', 'raghushree' ); ?></h3>
						<div class="mega-menu__aside-desc">
							<?php esc_html_e( 'Confirm current polymer grade availability, granules or powder forms, sample evaluation, and domestic or export quotations.', 'raghushree' ); ?>
						</div>
					</div>
					<a class="btn btn--primary btn--full" href="<?php echo esc_url( $raghushree_quote_url ); ?>">
						<span><?php esc_html_e( 'Submit Material Enquiry', 'raghushree' ); ?></span>
						<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>
			</div>
		</div>
	</div>

	<!-- Touch-Friendly Mobile Navigation Drawer -->
	<div class="mobile-drawer" id="mobile-nav-drawer" hidden>
		<div class="container mobile-drawer__inner">
			<nav aria-label="<?php esc_attr_e( 'Mobile Navigation', 'raghushree' ); ?>">
				<ul class="mobile-nav__list">
					<li>
						<a class="mobile-nav__link<?php echo $is_home_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<?php esc_html_e( 'Home', 'raghushree' ); ?>
						</a>
					</li>
					<li>
						<button
							type="button"
							class="mobile-nav__accordion-btn<?php echo $is_products_active ? ' is-active' : ''; ?>"
							data-mobile-products-toggle
							aria-expanded="false"
							aria-controls="mobile-products-sublist"
						>
							<span><?php esc_html_e( 'Products', 'raghushree' ); ?></span>
							<?php echo raghushree_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
						<div class="mobile-nav__sublist" id="mobile-products-sublist" hidden>
							<a class="mobile-nav__sublink mobile-nav__sublink--all" href="<?php echo esc_url( $raghushree_products_url ); ?>">
								<span><?php esc_html_e( 'View Complete Polymer Catalog', 'raghushree' ); ?></span>
								<?php echo raghushree_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
							<?php foreach ( $raghushree_catalog_items as $m_item ) : ?>
								<a class="mobile-nav__sublink" href="<?php echo esc_url( $m_item['permalink'] ); ?>">
									<span><?php echo esc_html( $m_item['title'] ); ?></span>
									<?php echo raghushree_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							<?php endforeach; ?>
						</div>
					</li>
					<li>
						<a class="mobile-nav__link<?php echo $is_about_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $raghushree_about_url ); ?>">
							<?php esc_html_e( 'About', 'raghushree' ); ?>
						</a>
					</li>
					<li>
						<a class="mobile-nav__link<?php echo $is_contact_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $raghushree_contact_url ); ?>">
							<?php esc_html_e( 'Contact', 'raghushree' ); ?>
						</a>
					</li>
				</ul>
			</nav>

			<div class="mobile-drawer__actions">
				<a class="btn btn--primary btn--full" href="<?php echo esc_url( $raghushree_quote_url ); ?>">
					<span><?php esc_html_e( 'Request a Quotation', 'raghushree' ); ?></span>
					<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<?php if ( ! empty( $raghushree_whatsapp_url ) ) : ?>
					<a class="btn btn--dark btn--full" href="<?php echo esc_url( $raghushree_whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo raghushree_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'WhatsApp Sales Team', 'raghushree' ); ?></span>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $raghushree_company['phone'] ) || ! empty( $raghushree_company['email'] ) ) : ?>
				<div class="mobile-drawer__contact">
					<?php if ( ! empty( $raghushree_company['phone'] ) && ! empty( $raghushree_company['phone_href'] ) ) : ?>
						<a href="<?php echo esc_url( $raghushree_company['phone_href'] ); ?>">
							<?php echo raghushree_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( $raghushree_company['phone'] ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $raghushree_company['email'] ) ) : ?>
						<a href="mailto:<?php echo esc_attr( $raghushree_company['email'] ); ?>">
							<?php echo raghushree_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( $raghushree_company['email'] ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</header>

import { useEffect, useRef, useState, type ReactNode } from 'react';
import { Link, Outlet, useLocation } from 'react-router-dom';
import { getSlotImage } from '../lib/images';
import { Icon } from './Icon';
import { buildWhatsappUrl, useSite } from './SiteContext';

export function Layout() {
  return (
    <SiteLayout>
      <Outlet />
    </SiteLayout>
  );
}

export function SiteLayout({ children }: { children: ReactNode }) {
  const { config, featuredProducts } = useSite();
  const location = useLocation();
  const [megaOpen, setMegaOpen] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [mobileProductsOpen, setMobileProductsOpen] = useState(false);
  const headerRef = useRef<HTMLElement | null>(null);

  const company = config?.company || {
    brand_name: 'Raghushree',
    legal_name: 'Raghushree Plast Products Pvt. Ltd.',
    positioning: 'Manufacturer, Exporter & Supplier of Plastic Raw Materials',
    street_address: '78/205, Latouche Road, General Ganj',
    city: 'Kanpur',
    postal_code: '208001',
    state: 'Uttar Pradesh',
    country: 'India',
    country_code: 'IN',
    full_address: '78/205, Latouche Road, General Ganj, Kanpur-208001, U.P., India',
    email: 'marketing@raghushree.com',
    phone: '+91-830-3715-071',
    whatsapp: '+91-830-3715-071',
    socials: {},
  };

  const categories = config?.categories || [];
  const phoneHref = company.phone ? `tel:${company.phone.replace(/[^0-9+]/g, '')}` : '';
  const whatsappUrl = buildWhatsappUrl(company.whatsapp || company.phone);
  const megaFeatureImg = getSlotImage('mega_menu_feature');

  const isHome = location.pathname === '/';
  const isProducts =
    location.pathname.startsWith('/products') ||
    location.pathname.startsWith('/polymer-category') ||
    location.pathname.startsWith('/material-grade') ||
    location.pathname.startsWith('/material-form') ||
    location.pathname.startsWith('/application');
  const isAbout = location.pathname.startsWith('/about');
  const isContact = location.pathname.startsWith('/contact');

  useEffect(() => {
    setMegaOpen(false);
    setMobileOpen(false);
    window.scrollTo(0, 0);
  }, [location.pathname, location.search]);

  useEffect(() => {
    const onKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        setMegaOpen(false);
        setMobileOpen(false);
      }
    };
    const onDocClick = (e: MouseEvent) => {
      if (headerRef.current && !headerRef.current.contains(e.target as Node)) {
        setMegaOpen(false);
      }
    };
    document.addEventListener('keydown', onKeyDown);
    document.addEventListener('click', onDocClick);
    return () => {
      document.removeEventListener('keydown', onKeyDown);
      document.removeEventListener('click', onDocClick);
    };
  }, []);

  return (
    <>
      <a className="skip-link" href="#main-content">
        Skip to main content
      </a>

      {(company.email || company.phone) && (
        <div className="utility-bar">
          <div className="container utility-bar__inner">
            <span className="utility-bar__tagline">
              {company.legal_name} — {company.positioning}
            </span>
            <div className="utility-bar__links">
              {company.email && (
                <a className="utility-bar__link" href={`mailto:${company.email}`}>
                  <Icon name="mail" />
                  <span>{company.email}</span>
                </a>
              )}
              {company.phone && phoneHref && (
                <a className="utility-bar__link" href={phoneHref}>
                  <Icon name="phone" />
                  <span>{company.phone}</span>
                </a>
              )}
            </div>
          </div>
        </div>
      )}

      <header className="site-header" id="site-header" ref={headerRef}>
        <div className="container site-header__inner">
          <Link className="site-brand" to="/" aria-label={`${company.brand_name} Home`}>
            <img
              className="site-brand__logo"
              src="/assets/images/logo-default-334x88.png"
              width={210}
              height={53}
              alt={company.legal_name}
              decoding="async"
            />
          </Link>

          <nav className="desktop-nav" aria-label="Primary Navigation">
            <ul className="desktop-nav__list">
              <li className="desktop-nav__item">
                <Link
                  className={`desktop-nav__link${isHome ? ' is-active' : ''}`}
                  to="/"
                  aria-current={isHome ? 'page' : undefined}
                >
                  Home
                </Link>
              </li>
              <li className="desktop-nav__item" data-mega-wrapper>
                <button
                  type="button"
                  className={`desktop-nav__trigger${isProducts ? ' is-active' : ''}`}
                  aria-expanded={megaOpen}
                  aria-controls="products-mega-menu"
                  onClick={() => setMegaOpen((prev) => !prev)}
                >
                  <span>Products</span>
                  <Icon name="chevron-down" />
                </button>
              </li>
              <li className="desktop-nav__item">
                <Link
                  className={`desktop-nav__link${isAbout ? ' is-active' : ''}`}
                  to="/about/"
                  aria-current={isAbout ? 'page' : undefined}
                >
                  About
                </Link>
              </li>
              <li className="desktop-nav__item">
                <Link
                  className={`desktop-nav__link${isContact ? ' is-active' : ''}`}
                  to="/contact/"
                  aria-current={isContact ? 'page' : undefined}
                >
                  Contact
                </Link>
              </li>
            </ul>
          </nav>

          <div className="site-header__cta">
            <Link
              className="btn btn--primary btn--sm"
              to="/contact/?enquiry_type=Request%20a%20Quotation#enquiry-form"
            >
              <span>Request a Quote</span>
              <Icon name="arrow-right" className="icon-arrow" />
            </Link>
          </div>

          <button
            type="button"
            className="mobile-toggle"
            aria-expanded={mobileOpen}
            aria-controls="mobile-nav-drawer"
            aria-label={mobileOpen ? 'Close main menu' : 'Open main menu'}
            onClick={() => setMobileOpen((prev) => !prev)}
          >
            {mobileOpen ? (
              <span className="icon-close" style={{ display: 'inline-flex' }}>
                <Icon name="close" />
              </span>
            ) : (
              <span className="icon-menu" style={{ display: 'inline-flex' }}>
                <Icon name="menu" />
              </span>
            )}
          </button>

          {/* Accessible Product Mega Menu */}
          <div
            className={`mega-menu${megaOpen ? ' is-open' : ''}`}
            id="products-mega-menu"
            role="region"
            aria-label="Product Categories Menu"
            aria-hidden={!megaOpen}
          >
            <div className="mega-menu__grid">
              <div>
                <div className="mega-menu__header">
                  <h2 className="mega-menu__heading">Explore Product Categories</h2>
                  <Link className="link-arrow" to="/products/">
                    <span>View All Products</span>
                    <Icon name="arrow-right" />
                  </Link>
                </div>
                <div className="mega-menu__categories">
                  {categories.map((cat) => (
                    <Link
                      key={cat.slug}
                      className="mega-menu__item"
                      to={`/products/?polymer_cat=${encodeURIComponent(cat.slug)}`}
                    >
                      <span className="mega-menu__item-title">{cat.title}</span>
                      <span className="mega-menu__item-desc">
                        {cat.category_type === 'pipe' ? 'Pipes & hoses' : 'Polymer materials'}
                      </span>
                    </Link>
                  ))}
                </div>
              </div>

              <div className="mega-menu__aside">
                <div>
                  <div className="mega-menu__media">
                    <img
                      src={megaFeatureImg.src}
                      alt={megaFeatureImg.alt}
                      width={megaFeatureImg.width}
                      height={megaFeatureImg.height}
                      loading="lazy"
                    />
                  </div>
                  <h3 className="mega-menu__aside-title">Grade Selection &amp; Bulk Supply</h3>
                  <div className="mega-menu__aside-desc">
                    Confirm current polymer grade availability, granules or powder forms, sample
                    evaluation, and domestic or export quotations.
                  </div>
                </div>
                <Link
                  className="btn btn--primary btn--full"
                  to="/contact/?enquiry_type=Request%20a%20Quotation#enquiry-form"
                >
                  <span>Submit Material Enquiry</span>
                  <Icon name="arrow-right" className="icon-arrow" />
                </Link>
              </div>
            </div>
          </div>
        </div>

        {/* Touch-Friendly Mobile Navigation Drawer */}
        <div className="mobile-drawer" id="mobile-nav-drawer" hidden={!mobileOpen}>
          <div className="container mobile-drawer__inner">
            <nav aria-label="Mobile Navigation">
              <ul className="mobile-nav__list">
                <li>
                  <Link className={`mobile-nav__link${isHome ? ' is-active' : ''}`} to="/">
                    Home
                  </Link>
                </li>
                <li>
                  <button
                    type="button"
                    className={`mobile-nav__accordion-btn${isProducts ? ' is-active' : ''}`}
                    aria-expanded={mobileProductsOpen}
                    aria-controls="mobile-products-sublist"
                    onClick={() => setMobileProductsOpen((p) => !p)}
                  >
                    <span>Products</span>
                    <Icon name="chevron-down" />
                  </button>
                  <div
                    className="mobile-nav__sublist"
                    id="mobile-products-sublist"
                    hidden={!mobileProductsOpen}
                  >
                    <Link className="mobile-nav__sublink mobile-nav__sublink--all" to="/products/">
                      <span>View All Products</span>
                      <Icon name="arrow-right" />
                    </Link>
                    {categories.map((cat) => (
                      <Link
                        key={cat.slug}
                        className="mobile-nav__sublink"
                        to={`/products/?polymer_cat=${encodeURIComponent(cat.slug)}`}
                      >
                        <span>{cat.title}</span>
                        <Icon name="chevron-right" />
                      </Link>
                    ))}
                  </div>
                </li>
                <li>
                  <Link className={`mobile-nav__link${isAbout ? ' is-active' : ''}`} to="/about/">
                    About
                  </Link>
                </li>
                <li>
                  <Link
                    className={`mobile-nav__link${isContact ? ' is-active' : ''}`}
                    to="/contact/"
                  >
                    Contact
                  </Link>
                </li>
              </ul>
            </nav>

            <div className="mobile-drawer__actions">
              <Link
                className="btn btn--primary btn--full"
                to="/contact/?enquiry_type=Request%20a%20Quotation#enquiry-form"
              >
                <span>Request a Quotation</span>
                <Icon name="arrow-right" className="icon-arrow" />
              </Link>
              {whatsappUrl && (
                <a
                  className="btn btn--dark btn--full"
                  href={whatsappUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <Icon name="whatsapp" />
                  <span>WhatsApp Sales Team</span>
                </a>
              )}
            </div>

            {(company.phone || company.email) && (
              <div className="mobile-drawer__contact">
                {company.phone && phoneHref && (
                  <a href={phoneHref}>
                    <Icon name="phone" />
                    <span>{company.phone}</span>
                  </a>
                )}
                {company.email && (
                  <a href={`mailto:${company.email}`}>
                    <Icon name="mail" />
                    <span>{company.email}</span>
                  </a>
                )}
              </div>
            )}
          </div>
        </div>
      </header>

      <main id="main-content">{children}</main>

      <footer className="site-footer" id="site-footer">
        <div className="site-footer__main">
          <div className="container site-footer__grid">
            <div>
              <Link className="site-footer__brand" to="/" aria-label={`${company.brand_name} Home`}>
                <img
                  className="site-footer__logo"
                  src="/assets/images/logo-wht.png"
                  width={300}
                  height={76}
                  alt={company.legal_name}
                  loading="lazy"
                  decoding="async"
                />
              </Link>
              <p className="site-footer__intro">
                Manufacturer, exporter, and supplier of plastic raw materials serving industrial
                manufacturers, moulders, packaging converters, and distributors across India and
                global markets.
              </p>
              <span className="site-footer__Badge">
                <Icon name="globe" />
                <span>Domestic India &amp; International Export Supply</span>
              </span>
            </div>

            <nav aria-label="Polymer Portfolio">
              <h2 className="site-footer__heading">Polymer Portfolio</h2>
              <ul className="site-footer__links">
                {featuredProducts.slice(0, 7).map((item) => (
                  <li key={item.id}>
                    <Link to={`/products/${item.slug}/`}>{item.title}</Link>
                  </li>
                ))}
                <li>
                  <Link to="/products/">View All Polymer Products</Link>
                </li>
              </ul>
            </nav>

            <nav aria-label="Company & Enquiries">
              <h2 className="site-footer__heading">Quick Links</h2>
              <ul className="site-footer__links">
                <li>
                  <Link to="/">Home</Link>
                </li>
                <li>
                  <Link to="/about/">About Raghushree</Link>
                </li>
                <li>
                  <Link to="/products/">Products Catalog</Link>
                </li>
                <li>
                  <Link to="/contact/?enquiry_type=Request%20a%20Quotation#enquiry-form">
                    Request a Quotation
                  </Link>
                </li>
                <li>
                  <Link to="/contact/?enquiry_type=Sample%20Request#enquiry-form">
                    Request a Sample
                  </Link>
                </li>
                <li>
                  <Link to="/contact/?enquiry_type=Bulk%20Order%20Enquiry#enquiry-form">
                    Bulk Order Enquiry
                  </Link>
                </li>
                <li>
                  <Link to="/contact/?enquiry_type=Distributor%20Enquiry#enquiry-form">
                    Distributor Enquiry
                  </Link>
                </li>
                <li>
                  <Link to="/contact/">Contact Sales Team</Link>
                </li>
              </ul>
            </nav>

            <div>
              <h2 className="site-footer__heading">Contact &amp; Registered Office</h2>
              <div className="site-footer__contact-card">
                {company.legal_name && (
                  <div className="site-footer__company-legal">{company.legal_name}</div>
                )}
                {company.full_address && (
                  <address className="site-footer__address">{company.full_address}</address>
                )}
                <div className="site-footer__contact-list">
                  {company.phone && phoneHref && (
                    <a className="site-footer__contact-link" href={phoneHref}>
                      <Icon name="phone" />
                      <span>{company.phone}</span>
                    </a>
                  )}
                  {company.email && (
                    <a className="site-footer__contact-link" href={`mailto:${company.email}`}>
                      <Icon name="mail" />
                      <span>{company.email}</span>
                    </a>
                  )}
                  {whatsappUrl && (
                    <a
                      className="site-footer__contact-link"
                      href={whatsappUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <Icon name="whatsapp" />
                      <span>Start WhatsApp Conversation</span>
                    </a>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="site-footer__bottom">
          <div className="container site-footer__bottom-inner">
            <div>
              &copy; {new Date().getUTCFullYear()} {company.legal_name.replace(/\.$/, '')}. All
              rights reserved.
            </div>
            <div className="site-footer__legal-links">
              <Link to="/privacy-policy/">Privacy Policy</Link>
              <Link to="/terms-of-use/">Terms of Use</Link>
              <Link to="/products/">Polymer Catalog</Link>
              <Link to="/contact/">Business Enquiries</Link>
            </div>
          </div>
        </div>
      </footer>
    </>
  );
}

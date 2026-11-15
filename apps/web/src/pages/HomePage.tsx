import { Link } from 'react-router-dom';
import {
  CtaBanner,
  FaqAccordion,
  FeatureCard,
  ProductCard,
  SectionHeading,
  StatCard,
  useScrollReveal,
} from '../components/CommonBlocks';
import { FlipCardsSection } from '../components/FlipCardsSection';
import { Icon } from '../components/Icon';
import { buildWhatsappUrl, useSite } from '../components/SiteContext';
import { VisualProductStory } from '../components/VisualProductStory';
import { getSlotImage } from '../lib/images';
import { SeoHead } from '../lib/seo';

const HOME_FAQS = [
  {
    question: 'Which plastic raw materials does Raghushree supply?',
    answer:
      'Raghushree supplies thermoplastic raw materials across Polypropylene (PP), High-Density Polyethylene (HDPE), Low-Density Polyethylene (LDPE), Linear Low-Density Polyethylene (LLDPE), Polyvinyl Chloride (PVC), Polyethylene Terephthalate (PET), and recycled & reprocessed polymers.',
  },
  {
    question:
      'Are all polymer categories available in every grade and physical form at all times?',
    answer:
      'No. Specific grades (including reprocessed or recycled grades) and forms (such as plastic granules or plastic powder) vary by polymer category and current batch supply. Please contact our sales team to verify exact availability for your application.',
  },
  {
    question: 'Can industrial buyers request material samples before placing a bulk order?',
    answer:
      'Yes. Production, R&D, and quality control teams can submit a sample request specifying their polymer, processing method, and trial requirement so we can coordinate appropriate evaluation material.',
  },
  {
    question:
      'Does Raghushree handle both domestic Indian orders and international export enquiries?',
    answer:
      'Yes. India is our core market, and we also welcome export enquiries from international manufacturers, converters, and plastic raw material importers.',
  },
  {
    question: 'How can plastic raw material distributors or dealers partner with Raghushree?',
    answer:
      'Distributors and dealers can submit a dedicated Distributor Enquiry through our Contact page detailing their target territory, polymer categories of interest, and recurring volume expectations.',
  },
];

export function HomePage() {
  const { config, featuredProducts } = useSite();
  const company = config?.company;
  const phone = company?.phone || '+91-830-3715-071';
  const email = company?.email || 'marketing@raghushree.com';
  const phoneHref = `tel:${phone.replace(/[^0-9+]/g, '')}`;
  const whatsappUrl = buildWhatsappUrl(
    company?.whatsapp || phone,
    'Hello Raghushree team, I would like to enquire about plastic raw materials.'
  );

  const heroImg = getSlotImage('home_hero');
  const aboutImg = getSlotImage('home_about');
  const qualityImg = getSlotImage('home_quality');

  useScrollReveal(featuredProducts.length);

  return (
    <>
      <SeoHead
        title="Raghushree — Plastic Raw Materials & Industrial Pipes | India"
        description="Raghushree Plast Products Pvt. Ltd. — Manufacturer, exporter, and supplier of PP, HDPE, LDPE, LLDPE, PVC, PET, recycled polymers, and industrial PVC pipes."
        canonicalPath="/"
        company={company}
        faqs={HOME_FAQS}
      />

      {/* Section 1: Full-Width Hero Banner */}
      <section className="section section--hero home-hero" id="home-hero">
        <div className="home-banner">
          <div className="container home-banner__inner">
            <div className="home-banner__content">
              <div className="eyebrow">
                Manufacturer, Exporter &amp; Supplier of Plastic Raw Materials
              </div>

              <h1 className="home-banner__title">
                Dependable Polymer Raw Materials for Industrial Manufacturing
              </h1>

              <p className="home-banner__desc">
                Raghushree supplies Polypropylene (PP), HDPE, LDPE, LLDPE, PVC, PET, and recycled
                &amp; reprocessed polymer grades—supporting manufacturers, contract moulders,
                packaging converters, and distributors across India and global export markets.
              </p>

              <div className="home-banner__actions">
                <div className="btn-group">
                  <Link className="btn btn--primary" to="/products/">
                    <span>Explore Products</span>
                    <Icon name="arrow-right" className="icon-arrow" />
                  </Link>
                  <Link
                    className="btn btn--dark"
                    to="/contact/?enquiry_type=Request%20a%20Quotation#enquiry-form"
                  >
                    <span>Request a Quote</span>
                  </Link>
                </div>
              </div>

              <div className="home-banner__highlights" aria-label="Supported Polymer Families">
                <span className="tag">PP</span>
                <span className="tag">HDPE</span>
                <span className="tag">LDPE</span>
                <span className="tag">LLDPE</span>
                <span className="tag">PVC</span>
                <span className="tag">PET</span>
                <span className="tag tag--accent">Recycled &amp; Reprocessed Polymers</span>
              </div>
            </div>
          </div>

          {/* Desktop Vertical S-Curve SVG Separator */}
          <svg
            className="home-banner__desktop-wave"
            viewBox="0 0 1400 560"
            preserveAspectRatio="none"
            aria-hidden="true"
            focusable="false"
          >
            <defs>
              <linearGradient id="raghushreeHomeWaveDesktop" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stopColor="#E63946" />
                <stop offset="50%" stopColor="#C5303C" />
                <stop offset="100%" stopColor="#A62530" />
              </linearGradient>
            </defs>
            <path
              d="M 0,0 L 830,0 C 720,90 690,210 810,330 C 900,420 870,500 930,560 L 0,560 Z"
              fill="url(#raghushreeHomeWaveDesktop)"
            />
            <path
              d="M 0,0 L 790,0 C 680,90 650,210 770,330 C 860,420 830,500 890,560 L 0,560 Z"
              fill="#FFFFFF"
            />
          </svg>

          {/* Right / Bottom Industrial Image & Quick Badges */}
          <div className="home-banner__media">
            <svg
              className="home-banner__mobile-wave"
              viewBox="0 0 1000 120"
              preserveAspectRatio="none"
              aria-hidden="true"
              focusable="false"
            >
              <defs>
                <linearGradient id="raghushreeHomeWaveMobile" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stopColor="#E63946" />
                  <stop offset="50%" stopColor="#C5303C" />
                  <stop offset="100%" stopColor="#A62530" />
                </linearGradient>
              </defs>
              <path
                d="M0,0 L1000,0 L1000,80 C600,150 400,-10 0,40 Z"
                fill="url(#raghushreeHomeWaveMobile)"
              />
              <path d="M0,0 L1000,0 L1000,50 C600,120 400,-40 0,10 Z" fill="#FFFFFF" />
            </svg>

            <img
              className="home-banner__img"
              src={heroImg.src}
              alt={heroImg.alt}
              width={heroImg.width}
              height={heroImg.height}
              decoding="async"
            />

            <div className="home-banner__quick-badge" aria-label="Direct Contact Channels">
              <a
                className="home-banner__quick-btn"
                href={phoneHref}
                aria-label="Call Raghushree Sales Desk"
              >
                <Icon name="phone" />
              </a>
              {whatsappUrl && (
                <a
                  className="home-banner__quick-btn"
                  href={whatsappUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  aria-label="WhatsApp Raghushree Sales Desk"
                >
                  <Icon name="whatsapp" />
                </a>
              )}
              <a
                className="home-banner__quick-btn"
                href={`mailto:${email}`}
                aria-label="Email Raghushree Marketing Desk"
              >
                <Icon name="mail" />
              </a>
            </div>
          </div>
        </div>
      </section>

      {/* Section 2: Business Strength / Trust Bar */}
      <section
        className="section section--compact surface--ink"
        id="business-strengths"
        aria-label="Core Business Strengths"
      >
        <div className="container">
          <div className="trust-bar__grid">
            <div className="trust-item">
              <div className="trust-item__icon">
                <Icon name="layers" />
              </div>
              <div>
                <h2 className="trust-item__title">Broad Polymer Portfolio</h2>
                <div className="trust-item__desc">
                  Supporting PP, HDPE, LDPE, LLDPE, PVC, PET, and recycled &amp; reprocessed
                  polymers.
                </div>
              </div>
            </div>
            <div className="trust-item">
              <div className="trust-item__icon">
                <Icon name="box" />
              </div>
              <div>
                <h2 className="trust-item__title">Multiple Material Forms</h2>
                <div className="trust-item__desc">
                  Available in plastic granules, plastic powder, and verified reprocessed or
                  recycled grades.
                </div>
              </div>
            </div>
            <div className="trust-item">
              <div className="trust-item__icon">
                <Icon name="settings" />
              </div>
              <div>
                <h2 className="trust-item__title">Application-Aligned Guidance</h2>
                <div className="trust-item__desc">
                  Helping production, R&amp;D, and procurement teams match grades to moulding and
                  extrusion lines.
                </div>
              </div>
            </div>
            <div className="trust-item">
              <div className="trust-item__icon">
                <Icon name="globe" />
              </div>
              <div>
                <h2 className="trust-item__title">India &amp; Export Supply</h2>
                <div className="trust-item__desc">
                  Serving manufacturing hubs across India alongside international export business
                  enquiries.
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Section 3: About Raghushree */}
      <section className="section surface--white" id="about-raghushree">
        <div className="container">
          <div className="split-layout">
            <div className="split-layout__content">
              <div className="eyebrow">About Raghushree</div>
              <h2>A Focused Raw Material Partner for Plastic Product Manufacturers</h2>
              <p>
                {company?.legal_name || 'Raghushree Plast Products Pvt. Ltd.'} operates as a
                manufacturer, exporter, and supplier of thermoplastic raw materials from Kanpur,
                Uttar Pradesh, India—working directly with industrial manufacturers, contract
                moulding units, packaging converters, and polymer distributors.
              </p>
              <p>
                Our commercial approach centres on clear material communication, practical grade
                matching for your machinery, transparent availability confirmation, and responsive
                coordination from initial sample evaluation through bulk order supply.
              </p>

              <div className="about-facts" style={{ marginBottom: 'var(--space-6)' }}>
                <StatCard label="Business Role" value="Manufacturer, Exporter & Supplier" />
                <StatCard label="Headquarters" value="Kanpur, U.P., India" />
                <StatCard label="Market Focus" value="Industrial & B2B Buyers" />
              </div>

              <div>
                <Link className="btn btn--secondary" to="/about/">
                  <span>Learn More About Raghushree</span>
                  <Icon name="arrow-right" className="icon-arrow" />
                </Link>
              </div>
            </div>

            <div className="split-layout__media">
              <img src={aboutImg.src} alt={aboutImg.alt} loading="lazy" />
            </div>
          </div>
        </div>
      </section>

      {/* Section 4: Product Categories */}
      <section className="section surface--pearl" id="product-categories">
        <div className="container">
          <SectionHeading
            eyebrow="Polymer Portfolio"
            title="Plastic Raw Material Categories Supplied by Raghushree"
            description="Explore our core polymer families including PP, HDPE, LDPE, LLDPE, PVC, PET, and recycled & reprocessed polymers. Select any material to review processing applications or submit a grade enquiry."
            align="split"
          />

          {featuredProducts.length > 0 && (
            <div className="grid-3">
              {featuredProducts.slice(0, 6).map((prod) => (
                <ProductCard key={prod.id} product={prod} />
              ))}
            </div>
          )}

          <div style={{ marginTop: 'var(--space-7)', textAlign: 'center' }}>
            <Link className="btn btn--dark" to="/products/">
              <span>View Complete Polymer Catalog</span>
              <Icon name="arrow-right" className="icon-arrow" />
            </Link>
          </div>
        </div>
      </section>

      {/* Section 5: Grades and Material Forms */}
      <section className="section surface--tint" id="grades-and-forms">
        <div className="container">
          <SectionHeading
            eyebrow="Grades & Physical Forms"
            title="Polymer Grades and Material Forms Matched to Your Process"
            description="Different thermoplastics and processing lines demand specific melt flow behaviour and physical forms. We supply verified grades and forms tailored to industrial production requirements."
          />

          <div className="grid-4">
            <FeatureCard
              icon="layers"
              meta="Material Grade"
              title="Reprocessed Grades"
              description="Carefully processed polymer grades suitable for industrial injection moulding, extrusion, utility goods, conduits, and cost-sensitive manufacturing lines."
            />
            <FeatureCard
              icon="shield"
              meta="Material Grade"
              title="Recycled Grades"
              description="Sorted and reprocessed recycled polymer materials supporting sustainable manufacturing targets across compatible moulding, pipe, and secondary packaging uses."
            />
            <FeatureCard
              icon="box"
              meta="Physical Form"
              title="Plastic Granules"
              description="Uniform pelletized polymer granules engineered for consistent hopper feeding, screw plastification, and stable melt flow in moulding and extrusion machinery."
            />
            <FeatureCard
              icon="settings"
              meta="Physical Form"
              title="Plastic Powder & Applicable Forms"
              description="Pulverized plastic powder and specialized material forms supplied for applicable polymers and processes such as rotational moulding, compounding, or coating."
            />
          </div>

          <div className="forms-notice">
            <div>
              <strong>Important Availability Note: </strong>
              <span>
                Not every grade or physical form is produced or stocked for every polymer category
                simultaneously. Please confirm exact grade and form availability with our sales
                desk.
              </span>
            </div>
            <Link
              className="btn btn--primary btn--sm"
              to="/contact/?enquiry_type=Material%20Availability#enquiry-form"
            >
              <span>Check Grade Availability</span>
              <Icon name="arrow-right" className="icon-arrow" />
            </Link>
          </div>
        </div>
      </section>

      {/* Section 6: Scroll-Pinned 5-Slide Visual Product Story */}
      <VisualProductStory id="visual-product-story" />

      {/* Section 7: 4-Card 180° Interactive Flip Cards */}
      <FlipCardsSection id="manufacturing-applications" surface="surface--white" />

      {/* Section 8: Why Choose Raghushree */}
      <section className="section surface--pearl" id="why-choose-raghushree">
        <div className="container">
          <SectionHeading
            eyebrow="Why Choose Raghushree"
            title="Built Around Industrial Procurement & Production Realities"
            description="Raw material procurement requires accurate specification matching, responsive communication, and reliable commercial execution across every order cycle."
          />

          <div className="grid-3">
            <FeatureCard
              icon="shield"
              title="Verified Grade Communication"
              description="We communicate material characteristics, polymer origin, and physical form clearly—helping buyers procure with confidence without guesswork."
            />
            <FeatureCard
              icon="settings"
              title="Process-Focused Consultation"
              description="Whether your shop floor runs high-cavity injection moulds, blown film lines, or pipe extruders, we help align raw material selection to your machinery."
            />
            <FeatureCard
              icon="file-text"
              title="Sample & Trial Support"
              description="Production, R&D, and quality control teams can request evaluation samples and discuss processing parameters prior to bulk procurement."
            />
            <FeatureCard
              icon="truck"
              title="Bulk & Repeat Order Coordination"
              description="Structured quotation turnaround and dependable dispatch planning for recurring factory schedules, dealers, and international buyers."
            />
            <FeatureCard
              icon="layers"
              title="Primary & Reprocessed Options"
              description="Access commodity polymers alongside cost-effective recycled and reprocessed polymer grades from a single commercial point of contact."
            />
            <FeatureCard
              icon="whatsapp"
              title="Direct Sales Accessibility"
              description="Fast, direct access to our Kanpur commercial team via structured RFQ forms, email, telephone, and WhatsApp for real-time procurement support."
            />
          </div>
        </div>
      </section>

      {/* Section 9: Industries & Customers Served */}
      <section className="section surface--tint" id="industries-served">
        <div className="container">
          <SectionHeading
            eyebrow="Industries & Customers Served"
            title="Supplying Diverse Manufacturing Sectors & Procurement Teams"
            description="We work directly with industrial manufacturers, converters, and distribution partners across domestic Indian industrial clusters and international markets."
          />

          <div className="grid-4">
            <FeatureCard
              title="Plastic Product Manufacturers"
              description="OEM and branded manufacturers producing rigid, flexible, household, and industrial plastic goods."
            />
            <FeatureCard
              title="Contract Moulding Companies"
              description="Custom injection, blow, and rotational moulding units running multi-polymer customer tooling."
            />
            <FeatureCard
              title="Packaging Manufacturers"
              description="Blown/cast film extruders, stretch wrap makers, woven sack units, and bottle/container converters."
            />
            <FeatureCard
              title="Pipes, Electrical & Industrial Units"
              description="Manufacturers of water/agri pipes, cable conduits, electrical housings, and technical components."
            />
          </div>

          <div className="buyer-roles">
            <h3>Decision-Makers &amp; Teams We Support</h3>
            <p>
              Whether you are evaluating raw material grades for a new mould, managing plant-wide
              resin purchasing, or sourcing recurring distributor lots, our team structures clear
              commercial and technical responses.
            </p>
            <div className="buyer-roles__list">
              <span className="tag tag--accent">Procurement &amp; Purchasing Teams</span>
              <span className="tag tag--accent">Production Managers</span>
              <span className="tag tag--accent">Technical &amp; R&amp;D Teams</span>
              <span className="tag tag--accent">Quality Control Teams</span>
              <span className="tag tag--accent">Plastic Distributors &amp; Dealers</span>
              <span className="tag tag--accent">Business Owners &amp; Directors</span>
            </div>
          </div>
        </div>
      </section>

      {/* Section 10: Quality & Supply Capabilities */}
      <section className="section surface--white" id="quality-and-supply">
        <div className="container">
          <div className="split-layout split-layout--reverse">
            <div className="split-layout__content">
              <div className="eyebrow">Quality &amp; Supply Capabilities</div>
              <h2>Consistent Material Discipline &amp; Practical Supply Execution</h2>
              <p>
                In plastic manufacturing, raw material consistency directly affects cycle times,
                melt stability, surface finish, and reject rates. Raghushree prioritizes transparent
                specification dialogue before every order is finalized.
              </p>

              <div className="capability-points" style={{ marginBottom: 'var(--space-5)' }}>
                <div className="capability-point" style={{ backgroundColor: 'var(--pearl)' }}>
                  <h3 className="capability-point__title">Batch-Focused Material Alignment</h3>
                  <p>
                    Matching polymer family, physical form, and processing grade to your target
                    machine parameters.
                  </p>
                </div>
                <div className="capability-point" style={{ backgroundColor: 'var(--pearl)' }}>
                  <h3 className="capability-point__title">Pre-Order Sample Evaluation</h3>
                  <p>
                    Supporting trial sample requests so QC and production teams can validate
                    processability on line.
                  </p>
                </div>
                <div className="capability-point" style={{ backgroundColor: 'var(--pearl)' }}>
                  <h3 className="capability-point__title">Bulk &amp; Scheduled Supply</h3>
                  <p>
                    Coordinating full-load, bulk, and repeat industrial dispatches for manufacturing
                    continuity.
                  </p>
                </div>
                <div className="capability-point" style={{ backgroundColor: 'var(--pearl)' }}>
                  <h3 className="capability-point__title">Domestic &amp; Export Documentation</h3>
                  <p>
                    Structured invoicing, packing coordination, and commercial support for Indian
                    and export buyers.
                  </p>
                </div>
              </div>

              <div>
                <Link
                  className="btn btn--primary"
                  to="/contact/?enquiry_type=Technical%20Consultation#enquiry-form"
                >
                  <span>Discuss Technical Requirements</span>
                  <Icon name="arrow-right" className="icon-arrow" />
                </Link>
              </div>
            </div>

            <div className="split-layout__media">
              <img src={qualityImg.src} alt={qualityImg.alt} loading="lazy" />
            </div>
          </div>
        </div>
      </section>

      {/* Section 11: Supply Process */}
      <section className="section surface--ink" id="supply-process">
        <div className="container">
          <SectionHeading
            eyebrow="Procurement Workflow"
            title="Streamlined Five-Step Material Enquiry & Supply Process"
            description="From initial technical specification review to commercial quotation and dispatch, our workflow keeps procurement straightforward and transparent."
          />

          <div className="process-grid process-grid--5">
            {[
              [
                '01',
                'Enquiry',
                'Submit your polymer requirement via our RFQ form, email, phone, or WhatsApp with your target material and volume.',
              ],
              [
                '02',
                'Requirement Understanding',
                'Our team reviews your processing method (moulding, extrusion, film), target end-use, and delivery destination.',
              ],
              [
                '03',
                'Grade Selection',
                'We confirm currently available polymer grades, physical forms (granules or powder), and sample options if required.',
              ],
              [
                '04',
                'Quotation',
                'Receive a clear commercial quotation detailing verified grade specification, packaging format, and dispatch terms.',
              ],
              [
                '05',
                'Order & Supply',
                'Upon confirmation, orders are scheduled, packed, and dispatched smoothly for domestic or international delivery.',
              ],
            ].map(([num, title, desc]) => (
              <div key={num} className="process-card reveal">
                <span className="process-card__step">{num}</span>
                <h3 className="process-card__title">{title}</h3>
                <p>{desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Section 12: Home FAQs */}
      <section className="section surface--pearl" id="home-faqs">
        <div className="container--narrow">
          <SectionHeading
            eyebrow="Frequently Asked Questions"
            title="Common Questions from Industrial Polymer Buyers"
            description="Clear answers regarding our polymer categories, grade verification, sample requests, bulk procurement, and export enquiries."
            align="center"
          />
          <FaqAccordion faqs={HOME_FAQS} idPrefix="home-faq" />
        </div>
      </section>

      {/* Section 13: Final CTA */}
      <section className="section surface--white" id="home-final-cta">
        <div className="container">
          <CtaBanner
            eyebrow="Ready to Source Polymer Raw Materials?"
            title="Request a Quotation or Discuss Your Polymer Grade Specification"
            description="Connect directly with Raghushree Plast Products Pvt. Ltd. for bulk order quotations, current grade and form availability, sample requests, or distributor enquiries."
          />
        </div>
      </section>
    </>
  );
}

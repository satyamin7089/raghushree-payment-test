import { Link } from 'react-router-dom';
import {
  CtaBanner,
  FeatureCard,
  PageBanner,
  ProductCard,
  SectionHeading,
  useScrollReveal,
} from '../components/CommonBlocks';
import { FlipCardsSection } from '../components/FlipCardsSection';
import { Icon } from '../components/Icon';
import { useSite } from '../components/SiteContext';
import { VisualProductStory } from '../components/VisualProductStory';
import { getSlotImage } from '../lib/images';
import { SeoHead } from '../lib/seo';

export function AboutPage() {
  const { config, featuredProducts } = useSite();
  const company = config?.company;
  const legalName = company?.legal_name || 'Raghushree Plast Products Pvt. Ltd.';
  const fullAddress =
    company?.full_address || '78/205, Latouche Road, General Ganj, Kanpur-208001, U.P., India';

  const imgOverview = getSlotImage('about_overview');
  const imgInjection = getSlotImage('app_injection');
  const imgQuality = getSlotImage('about_quality');
  const imgSupply = getSlotImage('about_supply');

  useScrollReveal(featuredProducts.length);

  return (
    <>
      <SeoHead
        title="About Raghushree Plast Products Pvt. Ltd. | Kanpur, India"
        description="Learn about Raghushree Plast Products Pvt. Ltd., an Indian manufacturer, exporter, and supplier of thermoplastic polymer raw materials and industrial pipes."
        canonicalPath="/about/"
        company={company}
      />

      {/* Section 1: About Hero */}
      <PageBanner
        id="about-hero"
        eyebrow="About Raghushree"
        title="Industrial Plastic Raw Materials Backed by Practical Commercial Reliability"
        description={`${legalName} is an Indian manufacturer, exporter, and supplier of thermoplastic raw materials—serving plastic product manufacturers, contract moulders, packaging converters, and distributors.`}
        primaryCta={{ label: 'Explore Polymer Portfolio', url: '/products/' }}
        secondaryCta={{
          kicker: 'Bulk Order Enquiries',
          label: 'Contact Sales Desk',
          url: '/contact/',
          icon: 'phone',
        }}
        imageSlot="about_hero"
        captionTitle="Industrial Polymer Raw Material Supply"
        captionSub="Bulk orders, grade consultation & sample enquiries"
      />

      {/* Section 2: Three-Column Company Overview Triptych */}
      <section className="section surface--white" id="company-overview">
        <div className="container">
          <div className="about-triptych">
            <div className="about-triptych__stack">
              <div className="about-triptych__media reveal">
                <img src={imgOverview.src} alt={imgOverview.alt} loading="lazy" />
                <span className="about-triptych__caption">Thermoplastic Processing Support</span>
              </div>
              <div className="about-triptych__media reveal">
                <img src={imgInjection.src} alt={imgInjection.alt} loading="lazy" />
                <span className="about-triptych__caption">Moulding &amp; Extrusion Alignment</span>
              </div>
            </div>

            <div className="about-triptych__center">
              <div className="eyebrow">Company Overview</div>
              <h2>Rooted in Kanpur, Serving Industrial Polymer Buyers Across Markets</h2>
              <p>
                Headquartered at {fullAddress}, {legalName} focuses on supplying dependable plastic
                raw materials tailored to real shop-floor conversion needs.
              </p>
              <p>
                We bridge material availability and manufacturing demand across commodity
                thermoplastics as well as recycled and reprocessed polymer categories—helping buyers
                maintain continuity across single orders, trial evaluations, and ongoing supply
                schedules.
              </p>

              <div className="about-triptych__facts">
                <div className="about-triptych__fact">
                  <span className="about-triptych__fact-label">Legal Entity</span>
                  <strong className="about-triptych__fact-val">{legalName}</strong>
                </div>
                <div className="about-triptych__fact">
                  <span className="about-triptych__fact-label">Operations Scope</span>
                  <strong className="about-triptych__fact-val">
                    Manufacturer, Exporter &amp; Supplier
                  </strong>
                </div>
                <div className="about-triptych__fact">
                  <span className="about-triptych__fact-label">Commercial Base</span>
                  <strong className="about-triptych__fact-val">
                    General Ganj, Kanpur-208001, U.P., India
                  </strong>
                </div>
              </div>

              <div className="about-triptych__actions">
                <Link className="btn btn--primary" to="/products/">
                  <span>Explore Polymer Portfolio</span>
                  <Icon name="arrow-right" className="icon-arrow" />
                </Link>
                <Link className="btn btn--secondary" to="/contact/">
                  <span>Enquire Now</span>
                </Link>
              </div>
            </div>

            <div className="about-triptych__stack">
              <div className="about-triptych__media reveal">
                <img src={imgQuality.src} alt={imgQuality.alt} loading="lazy" />
                <span className="about-triptych__caption">
                  Specification &amp; Sample Verification
                </span>
              </div>
              <div className="about-triptych__media reveal">
                <img src={imgSupply.src} alt={imgSupply.alt} loading="lazy" />
                <span className="about-triptych__caption">Domestic &amp; Export Logistics</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Section 3: Business Approach */}
      <section className="section surface--pearl" id="business-approach">
        <div className="container">
          <SectionHeading
            eyebrow="Business Approach"
            title="Transparent Specification Matching Rather Than Generic Commodity Selling"
            description="Every plastic manufacturing line has distinct melt flow, tooling, colour, and cost targets. Our commercial methodology starts with understanding your process before quoting a grade."
          />
          <div className="grid-3">
            <FeatureCard
              icon="check"
              title="Verified Information Only"
              description="We confirm actual grade and form availability per enquiry rather than assuming every specification is universally applicable."
            />
            <FeatureCard
              icon="settings"
              title="Application-First Dialogue"
              description="We align polymer categories and physical forms—granules or powder—around your moulding, extrusion, or film equipment."
            />
            <FeatureCard
              icon="truck"
              title="Long-Term Buyer Partnerships"
              description="Structured support for repeat industrial buyers, contract moulders, procurement departments, and regional dealers."
            />
          </div>
        </div>
      </section>

      {/* Section 4: Material Portfolio */}
      <section className="section surface--white" id="material-portfolio">
        <div className="container">
          <SectionHeading
            eyebrow="Material Portfolio"
            title="Core Polymer & Pipe Categories Supporting Industrial Buyers"
            description="Our portfolio spans commodity polyolefins, engineering vinyls and polyesters, recycled & reprocessed polymer grades, and industrial PVC piping."
            align="split"
          />
          {featuredProducts.length > 0 && (
            <div className="grid-3">
              {featuredProducts.slice(0, 6).map((prod) => (
                <ProductCard key={prod.id} product={prod} />
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Section 5: 5-Slide Visual Product Story */}
      <VisualProductStory id="about-visual-product-story" />

      {/* Section 6: Visual Operations & Supply Showcase */}
      <section className="section surface--pearl" id="about-image-showcase">
        <div className="container">
          <SectionHeading
            eyebrow="Inside Our Supply Capabilities"
            title="Visual Overview of Material Sourcing, Conversion & Dispatch"
            description="Representative views across raw material forms, downstream conversion machinery, pre-bulk verification, and commercial logistics."
            align="split"
          />
          <div className="showcase-img-grid">
            {[
              {
                slot: 'polymer_hdpe',
                kicker: '01 · Polymer Resins',
                title: 'Multi-Polymer Raw Material Portfolio',
                desc: 'Supplying PP, HDPE, LDPE, LLDPE, PVC, PET, and sorted recycled polymer grades across verified forms.',
                url: '/products/',
              },
              {
                slot: 'home_about',
                kicker: '02 · Conversion Line Focus',
                title: 'Shop-Floor Equipment Compatibility',
                desc: 'Aligned with injection moulding, blow moulding, pipe extrusion, raffia tape, and flexible film lines.',
                url: '/contact/?enquiry_type=Technical%20Consultation#enquiry-form',
              },
              {
                slot: 'home_quality',
                kicker: '03 · Pre-Order Validation',
                title: 'Grade & Sample Evaluation',
                desc: 'Supporting QC and production teams with trial sample coordination before committing full commercial loads.',
                url: '/contact/?enquiry_type=Sample%20Request#enquiry-form',
              },
              {
                slot: 'about_supply',
                kicker: '04 · Pan-India & Export Reach',
                title: 'Structured Bulk Dispatch',
                desc: 'Organized raw material dispatch from Kanpur for Indian manufacturing clusters and international buyers.',
                url: '/contact/?enquiry_type=Bulk%20Order%20Enquiry#enquiry-form',
              },
            ].map((card) => {
              const img = getSlotImage(card.slot);
              return (
                <article key={card.kicker} className="showcase-img-card reveal">
                  <div className="showcase-img-card__media">
                    <img src={img.src} alt={img.alt} loading="lazy" />
                  </div>
                  <div className="showcase-img-card__body">
                    <span className="sp-kicker">{card.kicker}</span>
                    <h3 className="showcase-img-card__title">{card.title}</h3>
                    <p className="showcase-img-card__desc">{card.desc}</p>
                    <Link className="showcase-img-card__link" to={card.url}>
                      <span>Learn More</span>
                      <Icon name="arrow-right" className="icon-arrow" />
                    </Link>
                  </div>
                </article>
              );
            })}
          </div>
        </div>
      </section>

      {/* Section 7: 4-Card Interactive Flip Cards */}
      <FlipCardsSection id="about-applications" surface="surface--tint" />

      {/* Section 8: Quality Commitment */}
      <section className="section surface--white" id="quality-commitment">
        <div className="container">
          <div className="split-layout">
            <div className="split-layout__content">
              <div className="eyebrow">Quality Commitment</div>
              <h2>Focused on Processability, Cleanliness &amp; Specification Clarity</h2>
              <p>
                Industrial converters rely on predictable melt behaviour, clean granulation, and
                honest grade classification to protect tooling and maintain output targets.
              </p>
              <p>
                Whether supplying primary polymer categories or reprocessed and recycled grades,
                Raghushree emphasizes clear pre-sale specification review, batch transparency, and
                alignment with your quality control team&apos;s expectations.
              </p>
              <div style={{ marginTop: 'var(--space-5)' }}>
                <Link
                  className="btn btn--primary"
                  to="/contact/?enquiry_type=Technical%20Consultation#enquiry-form"
                >
                  <span>Discuss Your Requirements</span>
                  <Icon name="arrow-right" className="icon-arrow" />
                </Link>
              </div>
            </div>
            <div className="split-layout__media">
              <img src={imgQuality.src} alt={imgQuality.alt} loading="lazy" />
            </div>
          </div>
        </div>
      </section>

      {/* Section 9: Supply & Distribution Capabilities */}
      <section className="section surface--pearl" id="supply-capabilities">
        <div className="container">
          <div className="split-layout split-layout--reverse">
            <div className="split-layout__content">
              <div className="eyebrow">Supply &amp; Distribution Capabilities</div>
              <h2>Organized Raw Material Dispatch for Bulk &amp; Recurring Orders</h2>
              <p>
                From our commercial base in Kanpur, Uttar Pradesh, we coordinate raw material
                supply for manufacturing plants, contract moulders, and regional dealers across
                India as well as export buyers.
              </p>
              <p>
                Packaging formats, dispatch timelines, and minimum order quantities are confirmed
                transparently at the quotation stage based on the selected polymer grade and
                destination.
              </p>
              <div style={{ marginTop: 'var(--space-5)' }}>
                <Link
                  className="btn btn--primary"
                  to="/contact/?enquiry_type=Bulk%20Order%20Enquiry#enquiry-form"
                >
                  <span>Enquire About Bulk Supply</span>
                  <Icon name="arrow-right" className="icon-arrow" />
                </Link>
              </div>
            </div>
            <div className="split-layout__media">
              <img src={imgSupply.src} alt={imgSupply.alt} loading="lazy" />
            </div>
          </div>
        </div>
      </section>

      {/* Section 10: Technical Support */}
      <section className="section surface--white" id="technical-support">
        <div className="container">
          <SectionHeading
            eyebrow="Technical & Procurement Support"
            title="Collaborating with Production, R&D, QC & Purchasing Teams"
            description="Selecting the right polymer grade reduces machine adjustments and trial waste. Our team supports technical and commercial stakeholders throughout evaluation."
          />
          <div className="grid-3">
            <FeatureCard
              icon="settings"
              title="Process & Grade Guidance"
              description="Helping production managers and R&D teams identify suitable polymer grades for injection, blow moulding, film, or extrusion."
            />
            <FeatureCard
              icon="file-text"
              title="Sample Evaluation Requests"
              description="Coordinating material samples for machine trials and quality control verification before committing to full order volumes."
            />
            <FeatureCard
              icon="layers"
              title="Form & Blending Consultation"
              description="Advising buyers on available physical forms (granules vs powder) and reprocessed/recycled polymer suitability."
            />
          </div>
        </div>
      </section>

      {/* Section 11: Markets & Global Reach */}
      <section className="section surface--tint" id="markets-and-reach">
        <div className="container">
          <SectionHeading
            eyebrow="Markets & Global Reach"
            title="Serving Core Indian Industrial Hubs & International Export Buyers"
            description="From Uttar Pradesh and pan-India manufacturing corridors to overseas industrial importers, Raghushree welcomes domestic and global polymer enquiries."
          />
          <div className="market-regions">
            <div className="market-card">
              <h3>Domestic Market — India</h3>
              <p>
                Supporting plastic product manufacturers, moulding units, pipe extruders, film
                converters, and polymer dealers across major Indian industrial regions.
              </p>
              <div className="market-card__list">
                <span className="tag tag--accent">Bulk Factory Supply</span>
                <span className="tag">Repeat Scheduled Orders</span>
                <span className="tag">Dealer &amp; Distributor Enquiries</span>
              </div>
            </div>
            <div className="market-card">
              <h3>International Export Enquiries</h3>
              <p>
                Handling global business enquiries from overseas converters, industrial buyers, and
                raw material distributors seeking Indian polymer supply.
              </p>
              <div className="market-card__list">
                <span className="tag tag--accent">Export Quotations</span>
                <span className="tag">Container &amp; Bulk Enquiries</span>
                <span className="tag">Specification Verification</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Section 12: Why Choose & Enquiry Process + Final CTA */}
      <section className="section surface--white" id="about-why-choose">
        <div className="container">
          <SectionHeading
            eyebrow="Why Choose Raghushree"
            title="Dependable Execution for Procurement & Manufacturing Leaders"
          />
          <div className="grid-4">
            <FeatureCard
              icon="layers"
              title="Multi-Polymer Coverage"
              description="PP, HDPE, LDPE, LLDPE, PVC, PET, and recycled & reprocessed polymers managed through one responsive supplier."
            />
            <FeatureCard
              icon="shield"
              title="Honest Commercial Representation"
              description="Zero exaggerated claims—every quotation reflects verified material availability, grade form, and dispatch terms."
            />
            <FeatureCard
              icon="box"
              title="Flexible Buyer Engagement"
              description="Supporting trial sample evaluations, bulk factory orders, repeat procurement schedules, and distributor enquiries."
            />
            <FeatureCard
              icon="whatsapp"
              title="Responsive Communication"
              description="Direct coordination via email, phone, WhatsApp, and structured RFQ submissions from our Kanpur office."
            />
          </div>
        </div>
      </section>

      <section className="section surface--ink" id="customer-enquiry-process">
        <div className="container">
          <SectionHeading
            eyebrow="Customer Enquiry Process"
            title="How Industrial Buyers Work with Raghushree"
            description="A clear four-stage engagement process designed for purchasing managers, production heads, and business directors."
          />
          <div className="process-grid process-grid--4">
            {[
              [
                '01',
                'Share Specification',
                'Tell us your polymer category, preferred grade or form, application, and estimated quantity.',
              ],
              [
                '02',
                'Availability Review',
                'We check current verified grades and recommend compatible options or evaluation samples.',
              ],
              [
                '03',
                'Commercial Quote',
                'Receive clear pricing, packaging details, MOQ confirmation, and dispatch terms.',
              ],
              [
                '04',
                'Dispatch & Support',
                'Orders are coordinated smoothly for domestic Indian delivery or international export shipment.',
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

      <section className="section surface--pearl" id="about-final-cta">
        <div className="container">
          <CtaBanner
            eyebrow="Connect with Raghushree"
            title="Start a Conversation About Your Polymer Raw Material Needs"
            description="Reach out to our Kanpur commercial team to verify grade availability, request evaluation samples, or obtain a bulk order quotation."
          />
        </div>
      </section>
    </>
  );
}

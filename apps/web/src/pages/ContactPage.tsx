import { useEffect, useState, type FormEvent } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import {
  CtaBanner,
  FaqAccordion,
  FeatureCard,
  PageBanner,
  SectionHeading,
  useScrollReveal,
} from '../components/CommonBlocks';
import { Icon } from '../components/Icon';
import { buildWhatsappUrl, useSite } from '../components/SiteContext';
import { apiRequest } from '../lib/api';
import { getSlotImage } from '../lib/images';
import { SeoHead } from '../lib/seo';

const ENQUIRY_TYPES = [
  'Request a Quotation',
  'Material Availability',
  'Sample Request',
  'Technical Consultation',
  'Bulk Order Enquiry',
  'Distributor Enquiry',
];

const APP_OPTIONS = [
  'Injection Moulding',
  'Blow Moulding',
  'Film & Packaging',
  'Extrusion',
  'Pipes & Fittings',
  'Electrical & Electronics',
  'Household & Consumer Products',
  'Distribution / Resale',
  'Other Industrial Application',
];

export function ContactPage() {
  const { config, featuredProducts } = useSite();
  const [searchParams] = useSearchParams();
  const company = config?.company;
  const legalName = company?.legal_name || 'Raghushree Plast Products Pvt. Ltd.';
  const fullAddress =
    company?.full_address || '78/205, Latouche Road, General Ganj, Kanpur-208001, U.P., India';
  const phone = company?.phone || '+91-830-3715-071';
  const email = company?.email || 'marketing@raghushree.com';
  const phoneHref = `tel:${phone.replace(/[^0-9+]/g, '')}`;
  const whatsappUrl = buildWhatsappUrl(company?.whatsapp || phone);

  const prefillType = searchParams.get('enquiry_type') || '';
  const prefillMaterial = searchParams.get('material') || '';
  const prefillApp = searchParams.get('application') || '';

  const [fullName, setFullName] = useState('');
  const [companyName, setCompanyName] = useState('');
  const [contactEmail, setContactEmail] = useState('');
  const [contactPhone, setContactPhone] = useState('');
  const [country, setCountry] = useState('India');
  const [enquiryType, setEnquiryType] = useState(prefillType);
  const [polymerProduct, setPolymerProduct] = useState(prefillMaterial);
  const [materialGrade, setMaterialGrade] = useState('');
  const [estimatedQuantity, setEstimatedQuantity] = useState('');
  const [application, setApplication] = useState(prefillApp);
  const [message, setMessage] = useState('');
  const [honeypot, setHoneypot] = useState('');

  const [errors, setErrors] = useState<Record<string, string>>({});
  const [submitting, setSubmitting] = useState(false);
  const [submitResult, setSubmitResult] = useState<{
    success: boolean;
    message: string;
  } | null>(null);

  useEffect(() => {
    if (prefillType) setEnquiryType(prefillType);
    if (prefillMaterial) setPolymerProduct(prefillMaterial);
    if (prefillApp) setApplication(prefillApp);
  }, [prefillType, prefillMaterial, prefillApp]);

  const materialOptions = (() => {
    const list =
      featuredProducts.length > 0
        ? featuredProducts.map((p) => p.title)
        : [
            'Polypropylene (PP)',
            'High-Density Polyethylene (HDPE)',
            'Low-Density Polyethylene (LDPE)',
            'Linear Low-Density Polyethylene (LLDPE)',
            'Polyvinyl Chloride (PVC)',
            'Polyethylene Terephthalate (PET)',
            'Recycled & Reprocessed Polymers',
          ];
    list.push('Multiple Polymers / Custom Requirement');
    if (polymerProduct && !list.includes(polymerProduct)) {
      list.push(polymerProduct);
    }
    return list;
  })();

  const contactFaqs = [
    {
      question: 'What information should I include when requesting a polymer quotation?',
      answer:
        'Please specify the polymer category (PP, HDPE, LDPE, LLDPE, PVC, PET, or recycled/reprocessed), preferred physical form or grade requirement, manufacturing application, estimated quantity, and delivery city or country.',
    },
    {
      question: 'Can I enquire about multiple polymer categories in a single submission?',
      answer:
        'Yes. Select "Multiple Polymers / Custom Requirement" in the Material field and list each polymer grade and estimated quantity in your message.',
    },
    {
      question: 'Do you support WhatsApp for quick procurement communication?',
      answer:
        'Yes. Buyers can initiate a direct WhatsApp conversation with our commercial team for quick availability questions or follow-ups alongside formal email quotations.',
    },
    {
      question: 'Where is Raghushree located?',
      answer: `${legalName} is located at ${fullAddress}, serving industrial customers across India and international export markets.`,
    },
  ];

  useScrollReveal(featuredProducts.length);

  const validate = () => {
    const next: Record<string, string> = {};
    if (!fullName.trim()) next.name = 'Please enter your full name.';
    if (!companyName.trim()) next.company = 'Please enter your company name.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(contactEmail.trim()))
      next.email = 'Please enter a valid business email.';
    if (contactPhone.replace(/[^0-9]/g, '').length < 7)
      next.phone = 'Please enter a valid phone or WhatsApp number.';
    if (!country.trim()) next.country = 'Please enter your country / market.';
    if (!enquiryType.trim()) next.enquiry_type = 'Please select an enquiry type.';
    if (!polymerProduct.trim()) next.material = 'Please select a polymer material.';
    if (!message.trim()) next.message = 'Please describe your requirement.';
    setErrors(next);
    return Object.keys(next).length === 0;
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setSubmitResult(null);
    if (!validate()) return;

    setSubmitting(true);
    try {
      const res = await apiRequest<{ reference_code: string }>('/api/v1/enquiries', {
        method: 'POST',
        body: JSON.stringify({
          full_name: fullName.trim(),
          company_name: companyName.trim(),
          email: contactEmail.trim(),
          phone: contactPhone.trim(),
          country: country.trim(),
          enquiry_type: enquiryType.trim(),
          polymer_product: polymerProduct.trim(),
          material_grade: materialGrade.trim(),
          material_form: application.trim(),
          estimated_quantity: estimatedQuantity.trim(),
          message: message.trim(),
          honeypot,
          source_page: '/contact/',
        }),
      });
      setSubmitResult({
        success: true,
        message: `Thank you. Your enquiry (${res.reference_code}) has been received and our commercial team will respond promptly.`,
      });
      setMessage('');
      setEstimatedQuantity('');
      setMaterialGrade('');
    } catch (err) {
      setSubmitResult({
        success: false,
        message:
          err instanceof Error
            ? err.message
            : 'Unable to submit enquiry at this moment. Please contact us via phone or WhatsApp.',
      });
    } finally {
      setSubmitting(false);
    }
  };

  const contactHeroImg = getSlotImage('contact_hero');

  return (
    <>
      <SeoHead
        title="Contact Raghushree | Polymer Quotation, Sample & RFQ Desk"
        description="Contact Raghushree Plast Products Pvt. Ltd. in Kanpur, India for plastic raw material quotations, sample requests, bulk orders, and distributor enquiries."
        canonicalPath="/contact/"
        company={company}
        faqs={contactFaqs}
      />

      {/* Section 1: Contact Hero */}
      <PageBanner
        id="contact-hero"
        eyebrow="Contact Sales & Technical Desk"
        title="Request a Polymer Quotation, Sample or Availability Check"
        description="Connect directly with Raghushree Plast Products Pvt. Ltd. for bulk raw material quotations, polymer grade selection, sample evaluation requests, export enquiries, or distributor partnerships."
        primaryCta={{ label: 'Submit Material Enquiry', url: '#enquiry-form' }}
        secondaryCta={{
          kicker: 'Polymer Portfolio',
          label: 'Explore Polymer Catalog',
          url: '/products/',
          icon: 'layers',
        }}
        imageSlot="contact_hero"
        captionTitle="Direct Commercial & RFQ Support"
        captionSub="Bulk orders, grade consultation & sample enquiries"
      />

      {/* Section 2: Verified Contact Information */}
      <section className="section section--compact surface--pearl" id="contact-information">
        <div className="container">
          <div className="contact-cards">
            <div className="contact-card">
              <div className="contact-card__icon">
                <Icon name="map-pin" />
              </div>
              <div className="contact-card__label">Registered Address</div>
              <div className="contact-card__value">{legalName}</div>
              <p>{fullAddress}</p>
            </div>
            <div className="contact-card">
              <div className="contact-card__icon">
                <Icon name="phone" />
              </div>
              <div className="contact-card__label">Sales Telephone</div>
              <div className="contact-card__value">
                <a href={phoneHref}>{phone}</a>
              </div>
              <p>Direct telephone line for domestic &amp; international business enquiries.</p>
            </div>
            <div className="contact-card">
              <div className="contact-card__icon">
                <Icon name="mail" />
              </div>
              <div className="contact-card__label">Marketing &amp; RFQ Email</div>
              <div className="contact-card__value">
                <a href={`mailto:${email}`}>{email}</a>
              </div>
              <p>Send detailed purchase schedules, technical requirements, or export RFQs.</p>
            </div>
            {whatsappUrl && (
              <div className="contact-card">
                <div className="contact-card__icon">
                  <Icon name="whatsapp" />
                </div>
                <div className="contact-card__label">WhatsApp Business Chat</div>
                <div className="contact-card__value">
                  <a href={whatsappUrl} target="_blank" rel="noopener noreferrer">
                    {company?.whatsapp || phone}
                  </a>
                </div>
                <p>Start a quick WhatsApp conversation with our commercial desk.</p>
              </div>
            )}
          </div>
        </div>
      </section>

      {/* Section 3: 11-Field Industrial Enquiry Form */}
      <section className="section surface--tint" id="enquiry-form">
        <div className="container">
          <div className="enquiry-layout">
            <div className="enquiry-form-card">
              <div className="eyebrow">Business Enquiry &amp; RFQ Form</div>
              <h2>Submit Your Material Specification</h2>
              <p style={{ marginBottom: 'var(--space-6)' }}>
                Complete the details below so our sales and technical team can confirm verified
                polymer grade availability, forms, and commercial pricing.
              </p>

              {submitResult && (
                <div
                  className={`form-notice ${
                    submitResult.success ? 'form-notice--success' : 'form-notice--error'
                  }`}
                  role="alert"
                >
                  {submitResult.message}
                </div>
              )}

              <form id="raghushree-enquiry-form" onSubmit={handleSubmit} noValidate>
                <div className="raghushree-hp" aria-hidden="true">
                  <label htmlFor="company_website_url">Leave this field blank</label>
                  <input
                    type="text"
                    id="company_website_url"
                    name="company_website_url"
                    value={honeypot}
                    tabIndex={-1}
                    autoComplete="off"
                    onChange={(e) => setHoneypot(e.target.value)}
                  />
                </div>

                <div className="form-grid form-grid--2">
                  <div className={`form-field${errors.name ? ' form-field--error' : ''}`}>
                    <label className="form-label" htmlFor="enquiry_name">
                      Full Name<span className="form-label__req">*</span>
                    </label>
                    <input
                      className="form-input"
                      type="text"
                      id="enquiry_name"
                      value={fullName}
                      required
                      onChange={(e) => setFullName(e.target.value)}
                    />
                    {errors.name && (
                      <span className="form-error" role="alert">
                        {errors.name}
                      </span>
                    )}
                  </div>

                  <div className={`form-field${errors.company ? ' form-field--error' : ''}`}>
                    <label className="form-label" htmlFor="enquiry_company">
                      Company Name<span className="form-label__req">*</span>
                    </label>
                    <input
                      className="form-input"
                      type="text"
                      id="enquiry_company"
                      value={companyName}
                      required
                      onChange={(e) => setCompanyName(e.target.value)}
                    />
                    {errors.company && (
                      <span className="form-error" role="alert">
                        {errors.company}
                      </span>
                    )}
                  </div>

                  <div className={`form-field${errors.email ? ' form-field--error' : ''}`}>
                    <label className="form-label" htmlFor="enquiry_email">
                      Business Email<span className="form-label__req">*</span>
                    </label>
                    <input
                      className="form-input"
                      type="email"
                      id="enquiry_email"
                      value={contactEmail}
                      required
                      onChange={(e) => setContactEmail(e.target.value)}
                    />
                    {errors.email && (
                      <span className="form-error" role="alert">
                        {errors.email}
                      </span>
                    )}
                  </div>

                  <div className={`form-field${errors.phone ? ' form-field--error' : ''}`}>
                    <label className="form-label" htmlFor="enquiry_phone">
                      Phone / WhatsApp Number<span className="form-label__req">*</span>
                    </label>
                    <input
                      className="form-input"
                      type="tel"
                      id="enquiry_phone"
                      placeholder="+91 ..."
                      value={contactPhone}
                      required
                      onChange={(e) => setContactPhone(e.target.value)}
                    />
                    {errors.phone && (
                      <span className="form-error" role="alert">
                        {errors.phone}
                      </span>
                    )}
                  </div>

                  <div className={`form-field${errors.country ? ' form-field--error' : ''}`}>
                    <label className="form-label" htmlFor="enquiry_country">
                      Country / Market<span className="form-label__req">*</span>
                    </label>
                    <input
                      className="form-input"
                      type="text"
                      id="enquiry_country"
                      value={country}
                      required
                      onChange={(e) => setCountry(e.target.value)}
                    />
                    {errors.country && (
                      <span className="form-error" role="alert">
                        {errors.country}
                      </span>
                    )}
                  </div>

                  <div className={`form-field${errors.enquiry_type ? ' form-field--error' : ''}`}>
                    <label className="form-label" htmlFor="enquiry_type">
                      Enquiry Type<span className="form-label__req">*</span>
                    </label>
                    <select
                      className="form-select"
                      id="enquiry_type"
                      value={enquiryType}
                      required
                      onChange={(e) => setEnquiryType(e.target.value)}
                    >
                      <option value="">Select enquiry type</option>
                      {ENQUIRY_TYPES.map((etype) => (
                        <option key={etype} value={etype}>
                          {etype}
                        </option>
                      ))}
                    </select>
                    {errors.enquiry_type && (
                      <span className="form-error" role="alert">
                        {errors.enquiry_type}
                      </span>
                    )}
                  </div>

                  <div className={`form-field${errors.material ? ' form-field--error' : ''}`}>
                    <label className="form-label" htmlFor="enquiry_material">
                      Material / Polymer Category<span className="form-label__req">*</span>
                    </label>
                    <select
                      className="form-select"
                      id="enquiry_material"
                      value={polymerProduct}
                      required
                      onChange={(e) => setPolymerProduct(e.target.value)}
                    >
                      <option value="">Select polymer material</option>
                      {materialOptions.map((m) => (
                        <option key={m} value={m}>
                          {m}
                        </option>
                      ))}
                    </select>
                    {errors.material && (
                      <span className="form-error" role="alert">
                        {errors.material}
                      </span>
                    )}
                  </div>

                  <div className="form-field">
                    <label className="form-label" htmlFor="enquiry_grade">
                      Preferred Grade / Form / Specification
                    </label>
                    <input
                      className="form-input"
                      type="text"
                      id="enquiry_grade"
                      placeholder="e.g. Granules, Powder, Reprocessed, MFI target"
                      value={materialGrade}
                      onChange={(e) => setMaterialGrade(e.target.value)}
                    />
                  </div>

                  <div className="form-field">
                    <label className="form-label" htmlFor="enquiry_quantity">
                      Estimated Quantity
                    </label>
                    <input
                      className="form-input"
                      type="text"
                      id="enquiry_quantity"
                      placeholder="e.g. Trial sample, MT / kg per month"
                      value={estimatedQuantity}
                      onChange={(e) => setEstimatedQuantity(e.target.value)}
                    />
                  </div>

                  <div className="form-field">
                    <label className="form-label" htmlFor="enquiry_application">
                      Manufacturing Application
                    </label>
                    <select
                      className="form-select"
                      id="enquiry_application"
                      value={application}
                      onChange={(e) => setApplication(e.target.value)}
                    >
                      <option value="">Select primary application (optional)</option>
                      {APP_OPTIONS.map((opt) => (
                        <option key={opt} value={opt}>
                          {opt}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div
                    className={`form-field form-field--full${
                      errors.message ? ' form-field--error' : ''
                    }`}
                  >
                    <label className="form-label" htmlFor="enquiry_message">
                      Requirement Details &amp; Message<span className="form-label__req">*</span>
                    </label>
                    <textarea
                      className="form-textarea"
                      id="enquiry_message"
                      placeholder="Share details on your end-product, colour/natural preference, processing equipment, delivery location, or questions for our team."
                      value={message}
                      required
                      onChange={(e) => setMessage(e.target.value)}
                    />
                    {errors.message && (
                      <span className="form-error" role="alert">
                        {errors.message}
                      </span>
                    )}
                  </div>
                </div>

                <div style={{ marginTop: 'var(--space-5)' }}>
                  <button type="submit" className="btn btn--primary" disabled={submitting}>
                    <span>{submitting ? 'Submitting…' : 'Submit Business Enquiry'}</span>
                    <Icon name="arrow-right" className="icon-arrow" />
                  </button>
                </div>
              </form>
            </div>

            <aside className="enquiry-sidebar" aria-label="Enquiry Guidance">
              <div className="enquiry-sidebar__box">
                <h3>What Helps Us Quote Faster</h3>
                <ul className="enquiry-checklist">
                  <li>
                    <Icon name="check" />
                    <span>Polymer family (PP, HDPE, LDPE, LLDPE, PVC, PET, or Recycled)</span>
                  </li>
                  <li>
                    <Icon name="check" />
                    <span>
                      Processing method (injection, blow moulding, film, pipe extrusion)
                    </span>
                  </li>
                  <li>
                    <Icon name="check" />
                    <span>Preferred form (plastic granules or powder where applicable)</span>
                  </li>
                  <li>
                    <Icon name="check" />
                    <span>Estimated trial or bulk quantity and destination city/port</span>
                  </li>
                </ul>
              </div>

              <div className="enquiry-sidebar__box enquiry-sidebar__box--dark surface--ink">
                <h3>Direct Sales Assistance</h3>
                <p>
                  Prefer speaking directly with our commercial team in Kanpur? Reach out via
                  telephone or WhatsApp.
                </p>
                <div
                  style={{
                    marginTop: 'var(--space-4)',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 'var(--space-2)',
                  }}
                >
                  {whatsappUrl && (
                    <a
                      className="btn btn--primary btn--full"
                      href={whatsappUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <Icon name="whatsapp" />
                      <span>WhatsApp Sales Team</span>
                    </a>
                  )}
                  <a className="btn btn--on-dark btn--full" href={phoneHref}>
                    <Icon name="phone" />
                    <span>{phone}</span>
                  </a>
                </div>
              </div>

              <div className="enquiry-sidebar__media">
                <img src={contactHeroImg.src} alt={contactHeroImg.alt} loading="lazy" />
              </div>
            </aside>
          </div>
        </div>
      </section>

      {/* Section 4: Enquire Directly by Polymer Category */}
      <section className="section surface--white" id="material-enquiry-options">
        <div className="container">
          <SectionHeading
            eyebrow="Material Enquiry Options"
            title="Enquire Directly by Polymer Category"
            description="Review individual polymer pages or pre-select a material category to open a targeted quotation request."
          />
          <div className="grid-4">
            {featuredProducts.slice(0, 8).map((p) => (
              <FeatureCard
                key={p.id}
                meta={p.polymer_code || p.primary_category?.title}
                title={p.title}
                description={p.short_desc}
                linkLabel="View Polymer Details"
                linkUrl={`/products/${p.slug}/`}
              />
            ))}
          </div>
        </div>
      </section>

      {/* Section 5: Supported Business Enquiries */}
      <section className="section surface--pearl" id="supported-enquiries">
        <div className="container">
          <SectionHeading
            eyebrow="Supported Business Enquiries"
            title="Dedicated Support Across Every Stage of Procurement"
          />
          <div className="grid-3">
            <FeatureCard
              icon="file-text"
              title="Commercial Quotations"
              description="Prompt pricing proposals for verified polymer grades, granules, or powder based on volume and destination."
            />
            <FeatureCard
              icon="layers"
              title="Material Availability Checks"
              description="Confirm current supply of specific polymer categories, reprocessed/recycled grades, or colour classifications."
            />
            <FeatureCard
              icon="box"
              title="Sample Evaluation Requests"
              description="Arrange evaluation material for production trials, laboratory checks, and quality control sign-off."
            />
            <FeatureCard
              icon="settings"
              title="Technical Consultation"
              description="Discuss melt flow behaviour, moulding/extrusion compatibility, and polymer form suitability."
            />
            <FeatureCard
              icon="truck"
              title="Bulk & Repeat Order Planning"
              description="Coordinate scheduled dispatches and full-load raw material procurement for ongoing plant production."
            />
            <FeatureCard
              icon="globe"
              title="Distributor & Export Enquiries"
              description="Connect regarding regional dealer distribution in India or international polymer export shipments."
            />
          </div>
        </div>
      </section>

      {/* Section 6: Direct Channels */}
      <section className="section surface--white" id="phone-whatsapp-options">
        <div className="container">
          <SectionHeading
            eyebrow="Direct Channels"
            title="Telephone & WhatsApp Sales Coordination"
          />
          <div className="direct-channels">
            <div className="channel-panel">
              <div>
                <div className="eyebrow">Telephone Desk</div>
                <h3>Speak with Our Commercial Team</h3>
                <p>
                  Discuss urgent raw material requirements, bulk order dispatches, or grade
                  specifications directly by phone.
                </p>
              </div>
              <div>
                <a className="btn btn--dark" href={phoneHref}>
                  <Icon name="phone" />
                  <span>{phone}</span>
                </a>
              </div>
            </div>
            {whatsappUrl && (
              <div className="channel-panel">
                <div>
                  <div className="eyebrow">Instant Messaging</div>
                  <h3>Start a WhatsApp Conversation</h3>
                  <p>
                    Share your required polymer grade, target application, and quantity on WhatsApp
                    for fast sales coordination.
                  </p>
                </div>
                <div>
                  <a
                    className="btn btn--primary"
                    href={whatsappUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    <Icon name="whatsapp" />
                    <span>Message on WhatsApp</span>
                  </a>
                </div>
              </div>
            )}
          </div>
        </div>
      </section>

      {/* Section 7: Location & Coverage */}
      <section className="section surface--tint" id="location-and-coverage">
        <div className="container">
          <div className="market-regions">
            <div className="market-card">
              <div className="eyebrow">Verified Office Location</div>
              <h3>{legalName}</h3>
              <p>{fullAddress}</p>
              <p style={{ marginTop: 'var(--space-3)' }}>
                <strong>Email: </strong>
                <a href={`mailto:${email}`}>{email}</a>
              </p>
            </div>
            <div className="market-card">
              <div className="eyebrow">Supply Coverage</div>
              <h3>Domestic India &amp; Global Export Enquiries</h3>
              <p>
                We coordinate polymer raw material supply across Indian manufacturing states as
                well as international export destinations based on verified commercial terms.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Section 8: Contact FAQs */}
      <section className="section surface--white" id="contact-faqs">
        <div className="container--narrow">
          <SectionHeading
            eyebrow="Enquiry FAQs"
            title="Frequently Asked Procurement Questions"
            align="center"
          />
          <FaqAccordion faqs={contactFaqs} idPrefix="contact-faq" />
        </div>
      </section>

      {/* Section 9: Final CTA */}
      <section className="section surface--pearl" id="contact-final-cta">
        <div className="container">
          <CtaBanner
            eyebrow="Explore Polymer Portfolio"
            title="Review Polymer Categories Before Submitting Your RFQ"
            description="Browse our complete catalog of PP, HDPE, LDPE, LLDPE, PVC, PET, and recycled & reprocessed polymer raw materials."
          />
        </div>
      </section>
    </>
  );
}

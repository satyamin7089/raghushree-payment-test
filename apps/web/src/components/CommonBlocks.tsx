import { useEffect, useState, type ReactNode } from 'react';
import { Link } from 'react-router-dom';
import { getProductImage, getSlotImage } from '../lib/images';
import type { ProductDto } from '../types/api';
import { Icon, type IconName } from './Icon';
import { buildWhatsappUrl, useSite } from './SiteContext';

export function useScrollReveal(depKey?: unknown) {
  useEffect(() => {
    const els = Array.from(document.querySelectorAll('.reveal'));
    if (!els.length) return;
    const reduced =
      typeof window !== 'undefined' &&
      typeof window.matchMedia === 'function' &&
      Boolean(window.matchMedia('(prefers-reduced-motion: reduce)')?.matches);
    if (reduced || typeof IntersectionObserver === 'undefined') {
      els.forEach((el) => el.classList.add('is-visible'));
      return;
    }
    document.documentElement.classList.add('js-ready');
    const obs = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { rootMargin: '0px 0px -6% 0px', threshold: 0.08 }
    );
    els.forEach((el) => obs.observe(el));
    return () => obs.disconnect();
  }, [depKey]);
}

export function SectionHeading({
  eyebrow,
  title,
  description,
  align = 'left',
}: {
  eyebrow?: string;
  title: string;
  description?: string;
  align?: 'left' | 'center' | 'split';
}) {
  const cls =
    align === 'center'
      ? 'section-heading section-heading--center'
      : align === 'split'
      ? 'section-heading section-heading--split'
      : 'section-heading';

  return (
    <div className={cls}>
      <div>
        {eyebrow && <div className="eyebrow">{eyebrow}</div>}
        <h2>{title}</h2>
      </div>
      {description && <p className="section-heading__desc">{description}</p>}
    </div>
  );
}

export function StatCard({ label, value }: { label: string; value: string }) {
  return (
    <div className="stat-card">
      <div className="stat-card__label">{label}</div>
      <div className="stat-card__value">{value}</div>
    </div>
  );
}

export function FeatureCard({
  icon,
  meta,
  title,
  description,
  linkLabel,
  linkUrl,
}: {
  icon?: IconName;
  meta?: string;
  title: string;
  description: string;
  linkLabel?: string;
  linkUrl?: string;
}) {
  return (
    <div className="feature-card reveal">
      {icon && (
        <div className="feature-card__icon">
          <Icon name={icon} />
        </div>
      )}
      {meta && <span className="feature-card__meta">{meta}</span>}
      <h3 className="feature-card__title">{title}</h3>
      <p className="feature-card__desc">{description}</p>
      {linkLabel && linkUrl && (
        <div style={{ marginTop: 'var(--space-4)' }}>
          <Link className="link-arrow" to={linkUrl}>
            <span>{linkLabel}</span>
            <Icon name="arrow-right" />
          </Link>
        </div>
      )}
    </div>
  );
}

export function ProductCard({ product }: { product: ProductDto }) {
  const img = getProductImage(product);
  const badge = product.primary_category?.title || product.polymer_code || 'Polymer';

  return (
    <article className="product-card reveal">
      <Link className="product-card__media" to={`/products/${product.slug}/`}>
        <img src={img.src} alt={img.alt} loading="lazy" decoding="async" />
        <span className="product-card__badge">{badge}</span>
      </Link>
      <div className="product-card__body">
        <h3 className="product-card__title">
          <Link to={`/products/${product.slug}/`}>{product.title}</Link>
        </h3>
        {product.short_desc && <p className="product-card__desc">{product.short_desc}</p>}
        <div className="product-card__footer">
          <Link className="link-arrow" to={`/products/${product.slug}/`}>
            <span>View Details</span>
            <Icon name="arrow-right" />
          </Link>
          <Link
            className="product-card__enquire"
            to={`/contact/?material=${encodeURIComponent(product.title)}&enquiry_type=Request%20a%20Quotation#enquiry-form`}
          >
            Enquire
          </Link>
        </div>
      </div>
    </article>
  );
}

export function FaqAccordion({
  faqs,
  idPrefix = 'faq',
}: {
  faqs: Array<{ question: string; answer: string }>;
  idPrefix?: string;
}) {
  const [openIndices, setOpenIndices] = useState<Record<number, boolean>>({ 0: true });

  const toggle = (idx: number) => {
    setOpenIndices((prev) => ({ ...prev, [idx]: !prev[idx] }));
  };

  return (
    <div className="faq-list">
      {faqs.map((faq, idx) => {
        const isOpen = Boolean(openIndices[idx]);
        const panelId = `${idPrefix}-panel-${idx}`;
        return (
          <div key={idx} className={`faq-item${isOpen ? ' is-open' : ''}`}>
            <button
              type="button"
              className="faq-trigger"
              aria-expanded={isOpen}
              aria-controls={panelId}
              onClick={() => toggle(idx)}
            >
              <span>{faq.question}</span>
              <Icon name="plus" />
            </button>
            <div id={panelId} className="faq-panel" hidden={!isOpen}>
              <p>{faq.answer}</p>
            </div>
          </div>
        );
      })}
    </div>
  );
}

export function CtaBanner({
  eyebrow = 'Procurement & Technical Enquiries',
  title = 'Discuss Your Polymer Raw Material Requirements with Raghushree',
  description = 'Request a quotation, confirm current polymer grade and form availability, arrange material samples, or discuss domestic and export bulk orders with our sales team.',
  material = '',
}: {
  eyebrow?: string;
  title?: string;
  description?: string;
  material?: string;
}) {
  const { config } = useSite();
  const company = config?.company;
  const phone = company?.phone || '+91-830-3715-071';
  const email = company?.email || 'marketing@raghushree.com';
  const phoneHref = `tel:${phone.replace(/[^0-9+]/g, '')}`;
  const waMsg = material
    ? `Hello Raghushree team, I would like to enquire about ${material} raw material availability and pricing.`
    : 'Hello Raghushree team, I would like to enquire about plastic raw materials.';
  const whatsappUrl = buildWhatsappUrl(company?.whatsapp || phone, waMsg);
  const quoteUrl = material
    ? `/contact/?enquiry_type=Request%20a%20Quotation&material=${encodeURIComponent(material)}#enquiry-form`
    : '/contact/?enquiry_type=Request%20a%20Quotation#enquiry-form';

  return (
    <div className="cta-panel surface--ink reveal">
      <div className="cta-panel__grid">
        <div>
          <div className="eyebrow">{eyebrow}</div>
          <h2 className="cta-panel__title">{title}</h2>
          <p>{description}</p>
          <div className="cta-panel__meta">
            <a href={phoneHref}>
              <Icon name="phone" />
              <span>{phone}</span>
            </a>
            <a href={`mailto:${email}`}>
              <Icon name="mail" />
              <span>{email}</span>
            </a>
          </div>
        </div>
        <div className="cta-panel__actions">
          <Link className="btn btn--primary" to={quoteUrl}>
            <span>Request a Quotation</span>
            <Icon name="arrow-right" className="icon-arrow" />
          </Link>
          {whatsappUrl && (
            <a
              className="btn btn--on-dark"
              href={whatsappUrl}
              target="_blank"
              rel="noopener noreferrer"
            >
              <Icon name="whatsapp" />
              <span>Start WhatsApp Chat</span>
            </a>
          )}
        </div>
      </div>
    </div>
  );
}

export interface BannerSlide {
  src: string;
  alt: string;
  captionTitle?: string;
  captionSub?: string;
}

export function PageBanner({
  id,
  eyebrow,
  badges,
  title,
  description,
  points,
  primaryCta,
  secondaryCta,
  imageSlot = 'about_hero',
  sliderSlides,
  captionTitle = 'Industrial Polymer Raw Material Supply',
  captionSub = 'Bulk orders, grade consultation & sample enquiries',
  emptyImage = false,
  purchaseSlot,
}: {
  id?: string;
  eyebrow?: string;
  badges?: Array<{ label: string; variant?: 'accent' | 'default' }>;
  title: string;
  description?: string;
  points?: string[];
  primaryCta?: { label: string; url: string };
  secondaryCta?: { kicker?: string; label: string; url: string; icon?: IconName };
  imageSlot?: string;
  sliderSlides?: BannerSlide[];
  captionTitle?: string;
  captionSub?: string;
  emptyImage?: boolean;
  purchaseSlot?: ReactNode;
}) {
  const [activeSlide, setActiveSlide] = useState(0);
  const slides = sliderSlides && sliderSlides.length > 0 ? sliderSlides : [];
  const hasSlider = slides.length > 1;
  const fallbackSlotImg = getSlotImage(imageSlot);

  useEffect(() => {
    setActiveSlide(0);
  }, [slides.length]);

  useEffect(() => {
    if (!hasSlider) return;
    const timer = window.setInterval(() => {
      setActiveSlide((prev) => (prev + 1) % slides.length);
    }, 5200);
    return () => window.clearInterval(timer);
  }, [hasSlider, slides.length]);

  const currentSlide = slides[activeSlide];

  return (
    <section className="section section--hero page-banner-section" id={id}>
      <div className="page-banner-card">
        <div className="page-banner-card__dots" aria-hidden="true" />
        <div className="page-banner-card__accent-bar" aria-hidden="true" />

        <div className="container page-banner-card__grid">
          <div className="page-banner-card__left">
            <div className="page-banner-card__top">
              {badges && badges.length > 0 && (
                <div className="page-banner-card__badges">
                  {badges.map((b, i) => (
                    <span key={i} className={b.variant === 'accent' ? 'tag tag--accent' : 'tag'}>
                      {b.label}
                    </span>
                  ))}
                </div>
              )}
              {eyebrow && <div className="eyebrow">{eyebrow}</div>}
              <h1 className="page-banner-card__title">{title}</h1>
              {description && <p className="page-banner-card__desc">{description}</p>}
              {points && points.length > 0 && (
                <ul className="page-banner-card__points">
                  {points.map((pt, idx) => (
                    <li key={idx}>
                      <span className="page-banner-card__point-icon" aria-hidden="true">
                        <Icon name="check" />
                      </span>
                      <span>{pt}</span>
                    </li>
                  ))}
                </ul>
              )}
            </div>

            {purchaseSlot}

            {(primaryCta || secondaryCta) && (
              <div className="page-banner-card__cta-row">
                {primaryCta &&
                  (primaryCta.url.startsWith('#') ? (
                    <a className="btn btn--primary" href={primaryCta.url}>
                      <span>{primaryCta.label}</span>
                      <Icon name="arrow-right" className="icon-arrow" />
                    </a>
                  ) : (
                    <Link className="btn btn--primary" to={primaryCta.url}>
                      <span>{primaryCta.label}</span>
                      <Icon name="arrow-right" className="icon-arrow" />
                    </Link>
                  ))}
                {secondaryCta && (
                  <Link className="page-banner-card__callout" to={secondaryCta.url}>
                    <span className="page-banner-card__callout-icon" aria-hidden="true">
                      <Icon name={secondaryCta.icon || 'phone'} />
                    </span>
                    <span className="page-banner-card__callout-info">
                      {secondaryCta.kicker && (
                        <span className="page-banner-card__callout-kicker">
                          {secondaryCta.kicker}
                        </span>
                      )}
                      <strong className="page-banner-card__callout-label">
                        {secondaryCta.label}
                      </strong>
                    </span>
                  </Link>
                )}
              </div>
            )}
          </div>

          <div className="page-banner-card__right">
            <div className={`page-banner-card__frame${hasSlider ? ' banner-fade-slider' : ''}`}>
              {hasSlider ? (
                <>
                  <div className="banner-fade-slider__slides">
                    {slides.map((sl, idx) => (
                      <div
                        key={idx}
                        className={`banner-fade-slider__slide${idx === activeSlide ? ' is-active' : ''}`}
                        aria-hidden={idx !== activeSlide}
                      >
                        <img src={sl.src} alt={sl.alt} loading={idx === 0 ? 'eager' : 'lazy'} />
                      </div>
                    ))}
                  </div>
                  <div className="page-banner-card__caption page-banner-card__caption--slider">
                    <div className="page-banner-card__caption-text">
                      <span className="page-banner-card__caption-title">
                        {currentSlide?.captionTitle || captionTitle}
                      </span>
                      <span className="page-banner-card__caption-sub">
                        {currentSlide?.captionSub || captionSub}
                      </span>
                    </div>
                    <div className="banner-fade-slider__nav" role="group" aria-label="Product banner image controls">
                      <button
                        type="button"
                        className="banner-fade-slider__btn"
                        aria-label="Previous image"
                        onClick={() =>
                          setActiveSlide((prev) => (prev - 1 + slides.length) % slides.length)
                        }
                      >
                        <Icon name="chevron-left" />
                      </button>
                      <div className="banner-fade-slider__dots">
                        {slides.map((_, idx) => (
                          <button
                            key={idx}
                            type="button"
                            className={`banner-fade-slider__dot${idx === activeSlide ? ' is-active' : ''}`}
                            aria-label={`View image ${idx + 1}`}
                            aria-pressed={idx === activeSlide}
                            onClick={() => setActiveSlide(idx)}
                          />
                        ))}
                      </div>
                      <button
                        type="button"
                        className="banner-fade-slider__btn"
                        aria-label="Next image"
                        onClick={() => setActiveSlide((prev) => (prev + 1) % slides.length)}
                      >
                        <Icon name="chevron-right" />
                      </button>
                    </div>
                  </div>
                </>
              ) : (
                <>
                  {emptyImage ? (
                    <div className="product-image-empty" role="img" aria-label="Product image not available">
                      Product image
                    </div>
                  ) : (
                    <img
                      src={slides[0]?.src || fallbackSlotImg.src}
                      alt={slides[0]?.alt || fallbackSlotImg.alt}
                      decoding="async"
                    />
                  )}
                  <div className="page-banner-card__caption">
                    <span className="page-banner-card__caption-title">{captionTitle}</span>
                    {captionSub && (
                      <span className="page-banner-card__caption-sub">{captionSub}</span>
                    )}
                  </div>
                </>
              )}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import {
  CtaBanner,
  FaqAccordion,
  PageBanner,
  useScrollReveal,
  type BannerSlide,
} from '../components/CommonBlocks';
import {
  ProductCheckoutSection,
  ProductPurchaseHero,
} from '../components/ProductPurchasePanel';
import { useSite } from '../components/SiteContext';
import { apiRequest } from '../lib/api';
import { getSlotImage } from '../lib/images';
import { SeoHead } from '../lib/seo';
import type { ProductDto, ShippingQuoteDto } from '../types/api';

export function ProductDetailPage() {
  const { slug } = useParams<{ slug: string }>();
  const { config } = useSite();
  const [product, setProduct] = useState<ProductDto | null>(null);
  const [loading, setLoading] = useState(true);
  const [notFound, setNotFound] = useState(false);

  const [activeQuote, setActiveQuote] = useState<ShippingQuoteDto | null>(null);
  const [checkoutStarted, setCheckoutStarted] = useState(false);

  useEffect(() => {
    if (!slug) return;
    let cancelled = false;
    setLoading(true);
    setNotFound(false);
    setActiveQuote(null);
    setCheckoutStarted(false);

    apiRequest<ProductDto>(`/api/v1/products/${encodeURIComponent(slug)}`)
      .then((res) => {
        if (!cancelled) setProduct(res);
      })
      .catch(() => {
        if (!cancelled) {
          setProduct(null);
          setNotFound(true);
        }
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [slug]);

  useScrollReveal(product?.id);

  if (loading) {
    return (
      <section className="section surface--white">
        <div className="container" style={{ paddingBlock: 'var(--space-10)' }}>
          <p>Loading product specifications…</p>
        </div>
      </section>
    );
  }

  if (notFound || !product) {
    return (
      <section className="section surface--white">
        <div className="container--narrow" style={{ paddingBlock: 'var(--space-10)', textAlign: 'center' }}>
          <div className="eyebrow">Product Not Found</div>
          <h1>Requested Material Not Found</h1>
          <p style={{ marginBottom: 'var(--space-6)' }}>
            The polymer or piping product you requested could not be located. Browse our complete
            catalog below.
          </p>
          <Link className="btn btn--primary" to="/products/">
            View Complete Product Catalog
          </Link>
        </div>
      </section>
    );
  }

  const enquireUrl = `/contact/?material=${encodeURIComponent(
    product.title
  )}&enquiry_type=Request%20a%20Quotation#enquiry-form`;

  const badges: Array<{ label: string; variant?: 'accent' | 'default' }> = [];
  if (product.primary_category) {
    badges.push({ label: product.primary_category.title, variant: 'accent' });
  }
  if (product.polymer_code) {
    badges.push({ label: product.polymer_code });
  }

  const slides: BannerSlide[] = [];
  if (product.featured_image_url) {
    slides.push({
      src: product.featured_image_url,
      alt: product.featured_image_alt || product.title,
      captionTitle: product.title,
      captionSub: product.primary_category?.title || '',
    });
  }
  for (const g of product.gallery || []) {
    slides.push({
      src: g.url,
      alt: g.alt_text || product.title,
      captionTitle: product.title,
      captionSub: '',
    });
  }
  if (slides.length === 0) {
    const slotImg = getSlotImage(product.image_slot || 'polymer_hdpe');
    slides.push({
      src: slotImg.src,
      alt: product.title,
      captionTitle: product.title,
      captionSub: '',
    });
  }

  const faqsFormatted = (product.sections.faqs || []).map((row) => ({
    question: row[0] || '',
    answer: row[1] || '',
  })).filter((f) => f.question && f.answer);

  const firstEnabledPrice = Object.values(product.variations || {}).find(
    (v) => v.enabled && v.price_paise > 0
  );

  return (
    <>
      <SeoHead
        title={product.seo.seo_title || `${product.title} | Raghushree`}
        description={product.seo.seo_description || product.short_desc}
        canonicalPath={product.seo.seo_canonical || `/products/${product.slug}/`}
        robots={product.seo.seo_robots || 'index,follow'}
        ogTitle={product.seo.og_title || product.title}
        ogDescription={product.seo.og_description || product.short_desc}
        ogImage={product.seo.og_image_url || slides[0]?.src}
        company={config?.company}
        faqs={faqsFormatted}
        productSchema={{
          name: product.title,
          description: product.short_desc,
          sku: product.polymer_code || product.slug,
          category: product.primary_category?.title,
          image: slides[0]?.src,
          priceRupees: firstEnabledPrice ? firstEnabledPrice.price_paise / 100 : undefined,
        }}
      />

      <PageBanner
        id="product-hero"
        badges={badges}
        title={product.title}
        description={product.short_desc}
        points={product.highlights}
        imageSlot={product.image_slot}
        sliderSlides={slides}
        captionTitle={product.title}
        captionSub=""
        purchaseSlot={
          <ProductPurchaseHero
            product={product}
            enquireUrl={enquireUrl}
            onQuoteReady={(q) => setActiveQuote(q)}
            onQuoteInvalidate={() => {
              if (!checkoutStarted) setActiveQuote(null);
            }}
            locked={checkoutStarted}
          />
        }
      />

      <ProductCheckoutSection
        quote={activeQuote}
        onCheckoutStarted={() => setCheckoutStarted(true)}
      />

      {product.content_html && product.content_html.trim() !== '' && (
        <section className="section surface--white single-product-section" id="overview">
          <div className="container product-editorial">
            <div className="product-editorial__heading">
              <span className="eyebrow">Product overview</span>
              <h2>{product.title}</h2>
            </div>
            <div
              className="product-editorial__body entry-content"
              dangerouslySetInnerHTML={{ __html: product.content_html }}
            />
          </div>
        </section>
      )}

      {(product.grades_notes || product.grades_rows.length > 0) && (
        <section className="section surface--pearl single-product-section" id="grades">
          <div className="container product-editorial">
            <div className="product-editorial__heading">
              <span className="eyebrow">Product details</span>
              <h2>Grades &amp; forms</h2>
            </div>
            <div className="product-editorial__body">
              {product.grades_notes && <p>{product.grades_notes}</p>}
              {product.grades_rows.length > 0 && (
                <div className="table-wrap">
                  <table className="data-table">
                    <thead>
                      <tr>
                        <th>Grade</th>
                        <th>Form</th>
                        <th>Typical use</th>
                      </tr>
                    </thead>
                    <tbody>
                      {product.grades_rows.map((row, idx) => (
                        <tr key={idx}>
                          <td>{row[0] || ''}</td>
                          <td>{row[1] || ''}</td>
                          <td>{row[2] || ''}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>
        </section>
      )}

      {(product.sections.features.length > 0 || product.section_intros.features) && (
        <section
          className="section product-features single-product-section"
          id="features"
          aria-labelledby="features-title"
        >
          <div className="container product-editorial product-detail-layout">
            <div className="product-editorial__heading product-detail-heading">
              <h2 id="features-title">Features</h2>
              {product.section_intros.features && (
                <p className="product-detail-heading__intro">{product.section_intros.features}</p>
              )}
            </div>
            {product.sections.features.length > 0 && (
              <div className="product-features__list">
                {product.sections.features.map((row, index) => (
                  <article key={index} className="product-feature">
                    <span className="product-feature__number" aria-hidden="true">
                      {String(index + 1).padStart(2, '0')}
                    </span>
                    <div>
                      <h3>{row[0]}</h3>
                      {row[1] && <p>{row[1]}</p>}
                    </div>
                  </article>
                ))}
              </div>
            )}
          </div>
        </section>
      )}

      {(product.sections.applications.length > 0 ||
        product.industries_rows.length > 0 ||
        product.section_intros.applications) && (
        <section
          className="section product-applications single-product-section"
          id="applications"
          aria-labelledby="applications-title"
        >
          <div className="container product-editorial product-detail-layout">
            <div className="product-editorial__heading product-detail-heading">
              <h2 id="applications-title">Applications</h2>
              {product.section_intros.applications && (
                <p className="product-detail-heading__intro">
                  {product.section_intros.applications}
                </p>
              )}
            </div>
            <div className="product-applications__body">
              {product.sections.applications.length > 0 && (
                <div className="product-applications__grid">
                  {product.sections.applications.map((row, index) => (
                    <article key={index} className="product-application">
                      <span className="product-application__number" aria-hidden="true">
                        {String(index + 1).padStart(2, '0')}
                      </span>
                      <h3>{row[0]}</h3>
                      {row[1] && <p>{row[1]}</p>}
                    </article>
                  ))}
                </div>
              )}
              {product.industries_rows.length > 0 && (
                <div className="product-industries">
                  <h3>Industries</h3>
                  <ul>
                    {product.industries_rows.map((ind, i) => (
                      <li key={i}>{ind}</li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          </div>
        </section>
      )}

      {(product.sections.specifications.length > 0 ||
        product.datasheet_url ||
        product.section_intros.specifications) && (
        <section className="section surface--pearl single-product-section" id="technical-details">
          <div className="container product-editorial product-detail-layout">
            <div className="product-editorial__heading product-detail-heading">
              <h2>Technical specifications</h2>
              {product.section_intros.specifications && (
                <p className="product-detail-heading__intro">
                  {product.section_intros.specifications}
                </p>
              )}
            </div>
            <div className="product-editorial__body">
              {product.sections.specifications.length > 0 && (
                <div className="table-wrap">
                  <table className="data-table">
                    <thead>
                      <tr>
                        <th>Property</th>
                        <th>Value</th>
                        <th>Notes</th>
                      </tr>
                    </thead>
                    <tbody>
                      {product.sections.specifications.map((row, idx) => (
                        <tr key={idx}>
                          <td>{row[0] || ''}</td>
                          <td>{row[1] || ''}</td>
                          <td>{row[2] || ''}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
              {product.datasheet_url && (
                <p style={{ marginTop: 'var(--space-4)' }}>
                  <a
                    className="btn btn--secondary"
                    href={product.datasheet_url}
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    Download datasheet
                  </a>
                </p>
              )}
            </div>
          </div>
        </section>
      )}

      {(product.processing_rows.length > 0 ||
        product.packaging ||
        product.pack_size ||
        product.moq ||
        product.availability) && (
        <section className="section surface--white single-product-section" id="additional-details">
          <div className="container product-editorial">
            <div className="product-editorial__heading">
              <span className="eyebrow">Product details</span>
              <h2>Additional information</h2>
            </div>
            <div className="product-editorial__body">
              {product.packaging && (
                <div className="product-editorial__item">
                  <h3>Packaging</h3>
                  <p>{product.packaging}</p>
                </div>
              )}
              {product.pack_size && (
                <div className="product-editorial__item">
                  <h3>Pack size</h3>
                  <p>{product.pack_size}</p>
                </div>
              )}
              {product.moq && (
                <div className="product-editorial__item">
                  <h3>Minimum order quantity</h3>
                  <p>{product.moq}</p>
                </div>
              )}
              {product.availability && (
                <div className="product-editorial__item">
                  <h3>Availability</h3>
                  <p>{product.availability}</p>
                </div>
              )}
              {product.processing_rows.map((row, idx) => (
                <div key={idx} className="product-editorial__item">
                  <h3>{row[0]}</h3>
                  <p>{row[1]}</p>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {(faqsFormatted.length > 0 || product.section_intros.faqs) && (
        <section className="section surface--white single-product-section" id="faqs">
          <div className="container product-editorial product-detail-layout">
            <div className="product-editorial__heading product-detail-heading">
              <h2>Questions &amp; answers</h2>
              {product.section_intros.faqs && (
                <p className="product-detail-heading__intro">{product.section_intros.faqs}</p>
              )}
            </div>
            <div className="product-editorial__body">
              {faqsFormatted.length > 0 && (
                <FaqAccordion faqs={faqsFormatted} idPrefix={`prod-faq-${product.id}`} />
              )}
            </div>
          </div>
        </section>
      )}

      <section className="section surface--pearl">
        <div className="container">
          <CtaBanner material={product.title} />
        </div>
      </section>
    </>
  );
}

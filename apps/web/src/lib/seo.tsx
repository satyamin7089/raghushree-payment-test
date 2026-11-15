import { useEffect } from 'react';
import type { CompanyInfoDto } from '../types/api';

export interface SeoHeadProps {
  title: string;
  description: string;
  canonicalPath?: string;
  robots?: string;
  ogTitle?: string;
  ogDescription?: string;
  ogImage?: string;
  company?: CompanyInfoDto | null;
  faqs?: Array<{ question: string; answer: string }>;
  productSchema?: {
    name: string;
    description: string;
    sku?: string;
    category?: string;
    image?: string;
    priceRupees?: number;
  };
}

function upsertMeta(attr: 'name' | 'property', key: string, content: string) {
  let el = document.head.querySelector(`meta[${attr}="${key}"]`) as HTMLMetaElement | null;
  if (!el) {
    el = document.createElement('meta');
    el.setAttribute(attr, key);
    document.head.appendChild(el);
  }
  el.setAttribute('content', content);
}

export function SeoHead({
  title,
  description,
  canonicalPath,
  robots = 'index,follow',
  ogTitle,
  ogDescription,
  ogImage,
  company,
  faqs,
  productSchema,
}: SeoHeadProps) {
  useEffect(() => {
    const origin = window.location.origin;
    const canonicalUrl = canonicalPath
      ? canonicalPath.startsWith('http')
        ? canonicalPath
        : `${origin}${canonicalPath}`
      : window.location.href;

    document.title = title;
    upsertMeta('name', 'description', description);
    upsertMeta('name', 'robots', robots);
    upsertMeta('property', 'og:type', productSchema ? 'product' : 'website');
    upsertMeta('property', 'og:title', ogTitle || title);
    upsertMeta('property', 'og:description', ogDescription || description);
    upsertMeta('property', 'og:url', canonicalUrl);
    if (ogImage) upsertMeta('property', 'og:image', ogImage);
    upsertMeta('name', 'twitter:card', 'summary_large_image');
    upsertMeta('name', 'twitter:title', ogTitle || title);
    upsertMeta('name', 'twitter:description', ogDescription || description);

    let canonical = document.head.querySelector('link[rel="canonical"]') as HTMLLinkElement | null;
    if (!canonical) {
      canonical = document.createElement('link');
      canonical.rel = 'canonical';
      document.head.appendChild(canonical);
    }
    canonical.href = canonicalUrl;

    const graph: Array<Record<string, unknown>> = [
      {
        '@type': 'Organization',
        '@id': `${origin}/#organization`,
        name: company?.legal_name || 'Raghushree Plast Products Pvt. Ltd.',
        alternateName: company?.brand_name || 'Raghushree',
        url: `${origin}/`,
        email: company?.email || 'marketing@raghushree.com',
        telephone: company?.phone || '+91-830-3715-071',
        address: {
          '@type': 'PostalAddress',
          streetAddress: company?.street_address || '78/205, Latouche Road, General Ganj',
          addressLocality: company?.city || 'Kanpur',
          addressRegion: company?.state || 'Uttar Pradesh',
          postalCode: company?.postal_code || '208001',
          addressCountry: company?.country_code || 'IN',
        },
      },
      {
        '@type': 'WebSite',
        '@id': `${origin}/#website`,
        url: `${origin}/`,
        name: company?.brand_name || 'Raghushree',
        publisher: { '@id': `${origin}/#organization` },
      },
      {
        '@type': 'WebPage',
        '@id': `${canonicalUrl}#webpage`,
        url: canonicalUrl,
        name: title,
        description,
        isPartOf: { '@id': `${origin}/#website` },
      },
    ];

    if (productSchema) {
      const prodNode: Record<string, unknown> = {
        '@type': 'Product',
        name: productSchema.name,
        description: productSchema.description,
        brand: { '@type': 'Brand', name: company?.brand_name || 'Raghushree' },
      };
      if (productSchema.sku) prodNode.sku = productSchema.sku;
      if (productSchema.category) prodNode.category = productSchema.category;
      if (productSchema.image) prodNode.image = productSchema.image;
      if (productSchema.priceRupees && productSchema.priceRupees > 0) {
        prodNode.offers = {
          '@type': 'Offer',
          priceCurrency: 'INR',
          price: productSchema.priceRupees.toFixed(2),
          availability: 'https://schema.org/InStock',
        };
      }
      graph.push(prodNode);
    }

    if (faqs && faqs.length > 0) {
      graph.push({
        '@type': 'FAQPage',
        mainEntity: faqs.map((f) => ({
          '@type': 'Question',
          name: f.question,
          acceptedAnswer: {
            '@type': 'Answer',
            text: f.answer,
          },
        })),
      });
    }

    let ldEl = document.head.querySelector('script[data-rg-jsonld]') as HTMLScriptElement | null;
    if (!ldEl) {
      ldEl = document.createElement('script');
      ldEl.type = 'application/ld+json';
      ldEl.setAttribute('data-rg-jsonld', 'true');
      document.head.appendChild(ldEl);
    }
    ldEl.textContent = JSON.stringify({ '@context': 'https://schema.org', '@graph': graph });
  }, [title, description, canonicalPath, robots, ogTitle, ogDescription, ogImage, company, faqs, productSchema]);

  return null;
}

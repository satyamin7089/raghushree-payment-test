import { query } from '../db/client.js';
import { getEnv } from '../config/env.js';
import { getCompanyInfo } from './settingsService.js';

export async function generateSitemapXml(origin?: string): Promise<string> {
  const env = getEnv();
  const baseUrl = (origin || env.publicSiteUrl || 'https://raghushree.com').replace(/\/+$/, '');

  const [productsRes, catsRes, termsRes] = await Promise.all([
    query<{ slug: string; updated_at: string }>(
      `SELECT slug, updated_at FROM public.products WHERE status = 'published' ORDER BY menu_order ASC`
    ),
    query<{ slug: string }>(
      `SELECT slug FROM public.product_categories WHERE is_catalog_v2 = TRUE ORDER BY sort_order ASC`
    ),
    query<{ taxonomy: string; slug: string }>(
      `SELECT taxonomy, slug FROM public.taxonomy_terms ORDER BY taxonomy, name`
    ),
  ]);

  const urls: Array<{ loc: string; lastmod?: string; priority: string }> = [
    { loc: `${baseUrl}/`, priority: '1.0' },
    { loc: `${baseUrl}/about/`, priority: '0.8' },
    { loc: `${baseUrl}/products/`, priority: '0.9' },
    { loc: `${baseUrl}/contact/`, priority: '0.8' },
    { loc: `${baseUrl}/privacy-policy/`, priority: '0.3' },
    { loc: `${baseUrl}/terms-of-use/`, priority: '0.3' },
  ];

  for (const c of catsRes.rows) {
    urls.push({ loc: `${baseUrl}/polymer-category/${c.slug}/`, priority: '0.7' });
  }
  for (const t of termsRes.rows) {
    const prefix =
      t.taxonomy === 'material_grade'
        ? 'material-grade'
        : t.taxonomy === 'material_form'
        ? 'material-form'
        : 'application';
    urls.push({ loc: `${baseUrl}/${prefix}/${t.slug}/`, priority: '0.6' });
  }
  for (const p of productsRes.rows) {
    urls.push({
      loc: `${baseUrl}/products/${p.slug}/`,
      lastmod: new Date(p.updated_at).toISOString().split('T')[0],
      priority: '0.8',
    });
  }

  const entries = urls
    .map((u) => {
      const lastmodTag = u.lastmod ? `<lastmod>${u.lastmod}</lastmod>` : '';
      return `  <url><loc>${escapeXml(u.loc)}</loc>${lastmodTag}<priority>${u.priority}</priority></url>`;
    })
    .join('\n');

  return `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${entries}\n</urlset>\n`;
}

export async function generateRobotsTxt(origin?: string): Promise<string> {
  const env = getEnv();
  const baseUrl = (origin || env.publicSiteUrl || 'https://raghushree.com').replace(/\/+$/, '');
  return [
    'User-agent: *',
    'Allow: /',
    'Disallow: /admin/',
    'Disallow: /api/',
    `Sitemap: ${baseUrl}/sitemap.xml`,
    '',
  ].join('\n');
}

export async function getOrganizationSchema(baseUrl: string) {
  const company = await getCompanyInfo();
  return {
    '@type': 'Organization',
    '@id': `${baseUrl}/#organization`,
    name: company.legal_name,
    alternateName: company.brand_name,
    url: `${baseUrl}/`,
    email: company.email,
    telephone: company.phone,
    address: {
      '@type': 'PostalAddress',
      streetAddress: company.street_address,
      addressLocality: company.city,
      addressRegion: company.state,
      postalCode: company.postal_code,
      addressCountry: company.country_code,
    },
  };
}

function escapeXml(str: string): string {
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;');
}

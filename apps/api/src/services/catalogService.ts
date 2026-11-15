import { query, withTransaction } from '../db/client.js';
import { AppError } from '../utils/errors.js';
import {
  buildMasterVariations,
  isValidMeasurement,
  parseDecimalPaise,
  POLYMER_PACK_KEYS,
  POLYMER_PACK_LABELS,
  type ProductMasterConfig,
  type ProductVariationRow,
} from '../utils/pricing.js';
import { CATALOG_V2_DEFINITIONS, CORE_POLYMER_LEGACY_DEFS, slugify } from './catalogSeedData.js';

export interface ProductSectionRow {
  col_1: string;
  col_2: string;
  col_3?: string;
}

export interface ProductFullDto {
  id: string;
  slug: string;
  title: string;
  polymer_code: string;
  primary_category: {
    id: string;
    slug: string;
    title: string;
    category_type: 'polymer' | 'pipe';
  } | null;
  categories: Array<{ id: string; slug: string; title: string; category_type: 'polymer' | 'pipe' }>;
  grades_terms: Array<{ id: string; slug: string; name: string }>;
  forms_terms: Array<{ id: string; slug: string; name: string }>;
  applications_terms: Array<{ id: string; slug: string; name: string }>;
  image_slot: string;
  featured_image_url: string | null;
  featured_image_alt: string;
  gallery: Array<{ media_id: string; url: string; alt_text: string; sort_order: number }>;
  short_desc: string;
  content_html: string;
  highlights: string[];
  grades_notes: string;
  grades_rows: string[][];
  industries_rows: string[];
  processing_rows: string[][];
  datasheet_url: string;
  packaging: string;
  pack_size: string;
  moq: string;
  availability: string;
  status: 'draft' | 'published' | 'archived';
  is_legacy_catalog: boolean;
  catalog_key: string | null;
  menu_order: number;
  sections: {
    features: string[][];
    applications: string[][];
    specifications: string[][];
    faqs: string[][];
  };
  section_intros: {
    features: string;
    applications: string;
    specifications: string;
    faqs: string;
  };
  master_config: ProductMasterConfig;
  variations: Record<string, ProductVariationRow>;
  seo: {
    seo_title: string;
    seo_description: string;
    seo_canonical: string;
    seo_robots: string;
    og_title: string;
    og_description: string;
    og_image_url: string;
    twitter_title: string;
    twitter_description: string;
    twitter_image_url: string;
  };
  created_at: string;
  updated_at: string;
}

export async function seedInitialCatalogIfEmpty(): Promise<void> {
  const existing = await query<{ cnt: string }>('SELECT COUNT(*)::text AS cnt FROM public.products');
  if (Number(existing.rows[0]?.cnt || 0) > 0) {
    return;
  }

  await withTransaction(async (tx) => {
    const catRes = await tx.query<{ id: string; slug: string }>(
      'SELECT id, slug FROM public.product_categories'
    );
    const catBySlug = new Map(catRes.rows.map((c) => [c.slug, c.id]));

    const termRes = await tx.query<{ id: string; taxonomy: string; name: string }>(
      'SELECT id, taxonomy, name FROM public.taxonomy_terms'
    );
    const termKey = (tax: string, name: string) => `${tax}:${name.toLowerCase()}`;
    const termByKey = new Map(termRes.rows.map((t) => [termKey(t.taxonomy, t.name), t.id]));

    // 1. Install the 22 Catalog V2 Products (primary active catalog)
    let orderIdx = 1;
    for (const cat of CATALOG_V2_DEFINITIONS) {
      const catId = catBySlug.get(cat.slug) || null;
      for (const prod of cat.products) {
        const catalogKey = `${cat.slug}:${prod.slug}`;
        const insertRes = await tx.query<{ id: string }>(
          `INSERT INTO public.products (
            slug, title, polymer_code, primary_category_id, image_slot, short_desc,
            status, is_legacy_catalog, catalog_key, menu_order
          ) VALUES ($1, $2, $3, $4, $5, $6, 'published', FALSE, $7, $8)
          ON CONFLICT (slug) DO NOTHING
          RETURNING id`,
          [
            prod.slug,
            prod.title,
            cat.type === 'polymer' ? cat.title.split(' ')[0] : 'PIPE',
            catId,
            prod.imageSlot,
            prod.shortDesc,
            catalogKey,
            orderIdx++,
          ]
        );
        const prodId = insertRes.rows[0]?.id;
        if (!prodId) continue;
        if (catId) {
          await tx.query(
            'INSERT INTO public.product_category_links (product_id, category_id) VALUES ($1, $2) ON CONFLICT DO NOTHING',
            [prodId, catId]
          );
        }
        await tx.query(
          `INSERT INTO public.product_master_configs (product_id, product_type, gst_bps, hsn, packet_label, packet_price_paise)
           VALUES ($1, $2, 1800, '', '', 0) ON CONFLICT DO NOTHING`,
          [prodId, cat.type]
        );
      }
    }

    // 2. Also seed the 7 foundational V1 Polymer Reference entries (marked legacy so V2 catalog stays clean,
    //    while /products/polypropylene-pp/ etc. remain 100% reachable as in WordPress!)
    for (const def of CORE_POLYMER_LEGACY_DEFS) {
      const catId = catBySlug.get(def.categorySlug) || null;
      const insertRes = await tx.query<{ id: string }>(
        `INSERT INTO public.products (
          slug, title, polymer_code, primary_category_id, image_slot, short_desc,
          content_html, grades_notes, industries_rows, processing_rows,
          status, is_legacy_catalog, menu_order
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9::jsonb, $10::jsonb, 'published', TRUE, $11)
        ON CONFLICT (slug) DO NOTHING
        RETURNING id`,
        [
          def.slug,
          def.title,
          def.code,
          catId,
          def.imageSlot,
          def.shortDesc,
          def.contentHtml,
          def.gradesNotes,
          JSON.stringify(def.industries),
          JSON.stringify(def.processing),
          def.menuOrder,
        ]
      );
      const prodId = insertRes.rows[0]?.id;
      if (!prodId) continue;
      if (catId) {
        await tx.query(
          'INSERT INTO public.product_category_links (product_id, category_id) VALUES ($1, $2) ON CONFLICT DO NOTHING',
          [prodId, catId]
        );
      }
      for (const gName of def.gradesTerms) {
        const tid = termByKey.get(termKey('material_grade', gName));
        if (tid) {
          await tx.query(
            'INSERT INTO public.product_taxonomy_links (product_id, term_id) VALUES ($1, $2) ON CONFLICT DO NOTHING',
            [prodId, tid]
          );
        }
      }
      for (const fName of def.formsTerms) {
        const tid = termByKey.get(termKey('material_form', fName));
        if (tid) {
          await tx.query(
            'INSERT INTO public.product_taxonomy_links (product_id, term_id) VALUES ($1, $2) ON CONFLICT DO NOTHING',
            [prodId, tid]
          );
        }
      }
      for (let i = 0; i < def.applications.length; i++) {
        await tx.query(
          `INSERT INTO public.product_sections (product_id, section_key, sort_order, col_1, col_2)
           VALUES ($1, 'applications', $2, $3, $4)`,
          [prodId, i, def.applications[i][0], def.applications[i][1]]
        );
      }
      for (let i = 0; i < def.faqs.length; i++) {
        await tx.query(
          `INSERT INTO public.product_sections (product_id, section_key, sort_order, col_1, col_2)
           VALUES ($1, 'faqs', $2, $3, $4)`,
          [prodId, i, def.faqs[i][0], def.faqs[i][1]]
        );
      }
      await tx.query(
        `INSERT INTO public.product_master_configs (product_id, product_type, gst_bps, hsn, packet_label, packet_price_paise)
         VALUES ($1, 'polymer', 1800, '', '', 0) ON CONFLICT DO NOTHING`,
        [prodId]
      );
    }
  });
}

export async function listCategories() {
  const res = await query<{
    id: string;
    slug: string;
    title: string;
    category_type: 'polymer' | 'pipe';
    description: string;
    sort_order: number;
    is_catalog_v2: boolean;
  }>(
    `SELECT id, slug, title, category_type, description, sort_order, is_catalog_v2
     FROM public.product_categories
     ORDER BY sort_order ASC, title ASC`
  );
  return res.rows;
}

export async function listTaxonomyTerms(taxonomy?: string) {
  const params: unknown[] = [];
  let where = '';
  if (taxonomy) {
    params.push(taxonomy);
    where = 'WHERE taxonomy = $1';
  }
  const res = await query<{
    id: string;
    taxonomy: string;
    slug: string;
    name: string;
    description: string;
  }>(
    `SELECT id, taxonomy, slug, name, description
     FROM public.taxonomy_terms
     ${where}
     ORDER BY taxonomy ASC, name ASC`,
    params
  );
  return res.rows;
}

export interface ListProductsParams {
  status?: 'published' | 'draft' | 'archived' | 'all';
  polymerCat?: string;
  materialGrade?: string;
  materialForm?: string;
  application?: string;
  search?: string;
  includeLegacy?: boolean;
  page?: number;
  limit?: number;
}

export async function listProducts(params: ListProductsParams = {}) {
  const page = Math.max(1, params.page || 1);
  const limit = Math.min(100, Math.max(1, params.limit || 24));
  const offset = (page - 1) * limit;

  const conditions: string[] = [];
  const values: unknown[] = [];
  let idx = 1;

  if (params.status && params.status !== 'all') {
    conditions.push(`p.status = $${idx++}`);
    values.push(params.status);
  } else if (!params.status) {
    conditions.push(`p.status = 'published'`);
  }

  if (!params.includeLegacy) {
    conditions.push('p.is_legacy_catalog = FALSE');
  }

  if (params.polymerCat) {
    conditions.push(`EXISTS (
      SELECT 1 FROM public.product_category_links pcl
      JOIN public.product_categories pc ON pc.id = pcl.category_id
      WHERE pcl.product_id = p.id AND pc.slug = $${idx++}
    )`);
    values.push(params.polymerCat);
  }

  if (params.materialGrade) {
    conditions.push(`EXISTS (
      SELECT 1 FROM public.product_taxonomy_links ptl
      JOIN public.taxonomy_terms tt ON tt.id = ptl.term_id
      WHERE ptl.product_id = p.id AND tt.taxonomy = 'material_grade' AND tt.slug = $${idx++}
    )`);
    values.push(params.materialGrade);
  }

  if (params.materialForm) {
    conditions.push(`EXISTS (
      SELECT 1 FROM public.product_taxonomy_links ptl
      JOIN public.taxonomy_terms tt ON tt.id = ptl.term_id
      WHERE ptl.product_id = p.id AND tt.taxonomy = 'material_form' AND tt.slug = $${idx++}
    )`);
    values.push(params.materialForm);
  }

  if (params.application) {
    conditions.push(`EXISTS (
      SELECT 1 FROM public.product_taxonomy_links ptl
      JOIN public.taxonomy_terms tt ON tt.id = ptl.term_id
      WHERE ptl.product_id = p.id AND tt.taxonomy = 'manufacturing_application' AND tt.slug = $${idx++}
    )`);
    values.push(params.application);
  }

  if (params.search && params.search.trim() !== '') {
    const q = `%${params.search.trim()}%`;
    conditions.push(`(p.title ILIKE $${idx} OR p.short_desc ILIKE $${idx} OR p.polymer_code ILIKE $${idx} OR p.content_html ILIKE $${idx})`);
    values.push(q);
    idx++;
  }

  const whereClause = conditions.length > 0 ? `WHERE ${conditions.join(' AND ')}` : '';

  const countRes = await query<{ total: string }>(
    `SELECT COUNT(*)::text AS total FROM public.products p ${whereClause}`,
    values
  );
  const total = Number(countRes.rows[0]?.total || 0);

  const listRes = await query<{ id: string }>(
    `SELECT p.id
     FROM public.products p
     ${whereClause}
     ORDER BY p.menu_order ASC, p.title ASC
     LIMIT $${idx++} OFFSET $${idx++}`,
    [...values, limit, offset]
  );

  const items: ProductFullDto[] = [];
  for (const row of listRes.rows) {
    const full = await getProductByIdOrSlug({ id: row.id }, true);
    if (full) items.push(full);
  }

  return {
    items,
    total,
    page,
    limit,
    totalPages: Math.max(1, Math.ceil(total / limit)),
  };
}

export async function getProductByIdOrSlug(
  identifier: { id?: string; slug?: string },
  includeUnpublished = false
): Promise<ProductFullDto | null> {
  const byId = Boolean(identifier.id);
  const value = identifier.id || identifier.slug;
  if (!value) return null;

  const statusFilter = includeUnpublished ? '' : `AND p.status = 'published'`;
  const prodRes = await query<{
    id: string;
    slug: string;
    title: string;
    polymer_code: string;
    primary_category_id: string | null;
    image_slot: string;
    featured_media_id: string | null;
    featured_url: string | null;
    featured_alt: string | null;
    short_desc: string;
    content_html: string;
    highlights: string[];
    grades_notes: string;
    grades_rows: string[][];
    industries_rows: string[];
    processing_rows: string[][];
    datasheet_url: string;
    packaging: string;
    pack_size: string;
    moq: string;
    availability: string;
    status: 'draft' | 'published' | 'archived';
    is_legacy_catalog: boolean;
    catalog_key: string | null;
    menu_order: number;
    seo_title: string;
    seo_description: string;
    seo_canonical: string;
    seo_robots: string;
    og_title: string;
    og_description: string;
    og_image_url: string;
    twitter_title: string;
    twitter_description: string;
    twitter_image_url: string;
    created_at: string;
    updated_at: string;
  }>(
    `SELECT p.*,
            ma.public_url AS featured_url,
            ma.alt_text AS featured_alt
     FROM public.products p
     LEFT JOIN public.media_assets ma ON ma.id = p.featured_media_id
     WHERE ${byId ? 'p.id = $1' : 'p.slug = $1'} ${statusFilter}
     LIMIT 1`,
    [value]
  );

  const p = prodRes.rows[0];
  if (!p) return null;

  const [catRes, termRes, galleryRes, sectionsRes, introsRes, masterRes, varsRes] =
    await Promise.all([
      query<{ id: string; slug: string; title: string; category_type: 'polymer' | 'pipe' }>(
        `SELECT pc.id, pc.slug, pc.title, pc.category_type
         FROM public.product_category_links pcl
         JOIN public.product_categories pc ON pc.id = pcl.category_id
         WHERE pcl.product_id = $1
         ORDER BY pc.sort_order ASC, pc.title ASC`,
        [p.id]
      ),
      query<{ id: string; taxonomy: string; slug: string; name: string }>(
        `SELECT tt.id, tt.taxonomy, tt.slug, tt.name
         FROM public.product_taxonomy_links ptl
         JOIN public.taxonomy_terms tt ON tt.id = ptl.term_id
         WHERE ptl.product_id = $1
         ORDER BY tt.name ASC`,
        [p.id]
      ),
      query<{ media_id: string; url: string; alt_text: string; sort_order: number }>(
        `SELECT pgi.media_id, ma.public_url AS url, ma.alt_text, pgi.sort_order
         FROM public.product_gallery_items pgi
         JOIN public.media_assets ma ON ma.id = pgi.media_id
         WHERE pgi.product_id = $1
         ORDER BY pgi.sort_order ASC`,
        [p.id]
      ),
      query<{ section_key: string; col_1: string; col_2: string; col_3: string }>(
        `SELECT section_key, col_1, col_2, col_3
         FROM public.product_sections
         WHERE product_id = $1
         ORDER BY section_key ASC, sort_order ASC`,
        [p.id]
      ),
      query<{ section_key: string; intro_text: string }>(
        `SELECT section_key, intro_text
         FROM public.product_section_intros
         WHERE product_id = $1`,
        [p.id]
      ),
      query<{
        product_type: 'polymer' | 'pipe';
        gst_bps: number;
        hsn: string;
        packet_label: string;
        packet_price_paise: string | number;
      }>(
        `SELECT product_type, gst_bps, hsn, packet_label, packet_price_paise
         FROM public.product_master_configs
         WHERE product_id = $1`,
        [p.id]
      ),
      query<{
        option_key: string;
        variation_kind: string;
        label: string;
        quantity: number;
        price_paise: string | number;
        enabled: boolean;
        weight_kg: string | null;
        length_cm: string | null;
        width_cm: string | null;
        height_cm: string | null;
      }>(
        `SELECT option_key, variation_kind, label, quantity, price_paise, enabled,
                weight_kg::text, length_cm::text, width_cm::text, height_cm::text
         FROM public.product_variations
         WHERE product_id = $1`,
        [p.id]
      ),
    ]);

  const primaryCat =
    catRes.rows.find((c) => c.id === p.primary_category_id) || catRes.rows[0] || null;

  const sections = {
    features: [] as string[][],
    applications: [] as string[][],
    specifications: [] as string[][],
    faqs: [] as string[][],
  };
  for (const row of sectionsRes.rows) {
    if (row.section_key === 'features') sections.features.push([row.col_1, row.col_2]);
    if (row.section_key === 'applications') sections.applications.push([row.col_1, row.col_2]);
    if (row.section_key === 'specifications')
      sections.specifications.push([row.col_1, row.col_2, row.col_3]);
    if (row.section_key === 'faqs') sections.faqs.push([row.col_1, row.col_2]);
  }

  const sectionIntros = { features: '', applications: '', specifications: '', faqs: '' };
  for (const r of introsRes.rows) {
    if (r.section_key in sectionIntros) {
      sectionIntros[r.section_key as keyof typeof sectionIntros] = r.intro_text;
    }
  }

  const mc = masterRes.rows[0];
  const masterConfig: ProductMasterConfig = {
    product_type: mc?.product_type || 'polymer',
    gst_bps: mc ? Number(mc.gst_bps) : 1800,
    hsn: mc?.hsn || '',
    packet_label: mc?.packet_label || '',
    packet_price_paise: mc ? Number(mc.packet_price_paise) : 0,
  };

  const variations = buildMasterVariations(masterConfig, varsRes.rows);

  return {
    id: p.id,
    slug: p.slug,
    title: p.title,
    polymer_code: p.polymer_code,
    primary_category: primaryCat,
    categories: catRes.rows,
    grades_terms: termRes.rows.filter((t) => t.taxonomy === 'material_grade'),
    forms_terms: termRes.rows.filter((t) => t.taxonomy === 'material_form'),
    applications_terms: termRes.rows.filter((t) => t.taxonomy === 'manufacturing_application'),
    image_slot: p.image_slot,
    featured_image_url: p.featured_url,
    featured_image_alt: p.featured_alt || p.title,
    gallery: galleryRes.rows,
    short_desc: p.short_desc,
    content_html: p.content_html,
    highlights: Array.isArray(p.highlights) ? p.highlights : [],
    grades_notes: p.grades_notes,
    grades_rows: Array.isArray(p.grades_rows) ? p.grades_rows : [],
    industries_rows: Array.isArray(p.industries_rows) ? p.industries_rows : [],
    processing_rows: Array.isArray(p.processing_rows) ? p.processing_rows : [],
    datasheet_url: p.datasheet_url,
    packaging: p.packaging,
    pack_size: p.pack_size,
    moq: p.moq,
    availability: p.availability,
    status: p.status,
    is_legacy_catalog: Boolean(p.is_legacy_catalog),
    catalog_key: p.catalog_key,
    menu_order: p.menu_order,
    sections,
    section_intros: sectionIntros,
    master_config: masterConfig,
    variations,
    seo: {
      seo_title: p.seo_title,
      seo_description: p.seo_description,
      seo_canonical: p.seo_canonical,
      seo_robots: p.seo_robots,
      og_title: p.og_title,
      og_description: p.og_description,
      og_image_url: p.og_image_url,
      twitter_title: p.twitter_title,
      twitter_description: p.twitter_description,
      twitter_image_url: p.twitter_image_url,
    },
    created_at: p.created_at,
    updated_at: p.updated_at,
  };
}

export interface SaveProductInput {
  title: string;
  slug?: string;
  polymer_code?: string;
  primary_category_id?: string | null;
  category_ids?: string[];
  taxonomy_term_ids?: string[];
  image_slot?: string;
  featured_media_id?: string | null;
  gallery_media_ids?: string[];
  short_desc?: string;
  content_html?: string;
  highlights?: string[];
  grades_notes?: string;
  grades_rows?: string[][];
  industries_rows?: string[];
  processing_rows?: string[][];
  datasheet_url?: string;
  packaging?: string;
  pack_size?: string;
  moq?: string;
  availability?: string;
  status?: 'draft' | 'published' | 'archived';
  menu_order?: number;
  seo?: Partial<ProductFullDto['seo']>;
}

export async function createOrUpdateProduct(
  id: string | null,
  input: SaveProductInput,
  actorId: string
): Promise<ProductFullDto> {
  const title = input.title.trim();
  if (!title) throw new AppError('Product title is required.', 400, 'invalid_title');
  const slug = (input.slug ? slugify(input.slug) : slugify(title)) || `product-${Date.now()}`;

  const savedId = await withTransaction(async (tx) => {
    let productId = id;
    const primaryCatId =
      input.primary_category_id || (input.category_ids && input.category_ids[0]) || null;

    if (!productId) {
      const res = await tx.query<{ id: string }>(
        `INSERT INTO public.products (
          slug, title, polymer_code, primary_category_id, image_slot, featured_media_id,
          short_desc, content_html, highlights, grades_notes, grades_rows, industries_rows,
          processing_rows, datasheet_url, packaging, pack_size, moq, availability,
          status, is_legacy_catalog, menu_order,
          seo_title, seo_description, seo_canonical, seo_robots,
          og_title, og_description, og_image_url, twitter_title, twitter_description, twitter_image_url,
          created_by, updated_by
        ) VALUES (
          $1, $2, $3, $4, $5, $6,
          $7, $8, $9::jsonb, $10, $11::jsonb, $12::jsonb,
          $13::jsonb, $14, $15, $16, $17, $18,
          $19, FALSE, $20,
          $21, $22, $23, $24,
          $25, $26, $27, $28, $29, $30,
          $31, $31
        ) RETURNING id`,
        [
          slug,
          title,
          input.polymer_code || '',
          primaryCatId,
          input.image_slot || 'polymer_hdpe',
          input.featured_media_id || null,
          input.short_desc || '',
          input.content_html || '',
          JSON.stringify(input.highlights || []),
          input.grades_notes || '',
          JSON.stringify(input.grades_rows || []),
          JSON.stringify(input.industries_rows || []),
          JSON.stringify(input.processing_rows || []),
          input.datasheet_url || '',
          input.packaging || '',
          input.pack_size || '',
          input.moq || '',
          input.availability || '',
          input.status || 'published',
          input.menu_order ?? 0,
          input.seo?.seo_title || '',
          input.seo?.seo_description || '',
          input.seo?.seo_canonical || '',
          input.seo?.seo_robots || 'index,follow',
          input.seo?.og_title || '',
          input.seo?.og_description || '',
          input.seo?.og_image_url || '',
          input.seo?.twitter_title || '',
          input.seo?.twitter_description || '',
          input.seo?.twitter_image_url || '',
          actorId,
        ]
      );
      productId = res.rows[0].id;
      await tx.query(
        `INSERT INTO public.product_master_configs (product_id, product_type, gst_bps, hsn, packet_label, packet_price_paise)
         VALUES ($1, 'polymer', 1800, '', '', 0) ON CONFLICT DO NOTHING`,
        [productId]
      );
    } else {
      const upd = await tx.query<{ id: string }>(
        `UPDATE public.products SET
          slug = $2, title = $3, polymer_code = $4, primary_category_id = $5, image_slot = $6,
          featured_media_id = $7, short_desc = $8, content_html = $9, highlights = $10::jsonb,
          grades_notes = $11, grades_rows = $12::jsonb, industries_rows = $13::jsonb,
          processing_rows = $14::jsonb, datasheet_url = $15, packaging = $16, pack_size = $17,
          moq = $18, availability = $19, status = $20, menu_order = $21,
          seo_title = $22, seo_description = $23, seo_canonical = $24, seo_robots = $25,
          og_title = $26, og_description = $27, og_image_url = $28,
          twitter_title = $29, twitter_description = $30, twitter_image_url = $31,
          updated_by = $32
         WHERE id = $1
         RETURNING id`,
        [
          productId,
          slug,
          title,
          input.polymer_code || '',
          primaryCatId,
          input.image_slot || 'polymer_hdpe',
          input.featured_media_id || null,
          input.short_desc || '',
          input.content_html || '',
          JSON.stringify(input.highlights || []),
          input.grades_notes || '',
          JSON.stringify(input.grades_rows || []),
          JSON.stringify(input.industries_rows || []),
          JSON.stringify(input.processing_rows || []),
          input.datasheet_url || '',
          input.packaging || '',
          input.pack_size || '',
          input.moq || '',
          input.availability || '',
          input.status || 'published',
          input.menu_order ?? 0,
          input.seo?.seo_title || '',
          input.seo?.seo_description || '',
          input.seo?.seo_canonical || '',
          input.seo?.seo_robots || 'index,follow',
          input.seo?.og_title || '',
          input.seo?.og_description || '',
          input.seo?.og_image_url || '',
          input.seo?.twitter_title || '',
          input.seo?.twitter_description || '',
          input.seo?.twitter_image_url || '',
          actorId,
        ]
      );
      if (!upd.rows[0]) throw new AppError('Product not found.', 404, 'not_found');
    }

    if (Array.isArray(input.category_ids)) {
      await tx.query('DELETE FROM public.product_category_links WHERE product_id = $1', [productId]);
      const catSet = new Set(input.category_ids);
      if (primaryCatId) catSet.add(primaryCatId);
      for (const cid of catSet) {
        await tx.query(
          'INSERT INTO public.product_category_links (product_id, category_id) VALUES ($1, $2) ON CONFLICT DO NOTHING',
          [productId, cid]
        );
      }
    }

    if (Array.isArray(input.taxonomy_term_ids)) {
      await tx.query('DELETE FROM public.product_taxonomy_links WHERE product_id = $1', [productId]);
      for (const tid of new Set(input.taxonomy_term_ids)) {
        await tx.query(
          'INSERT INTO public.product_taxonomy_links (product_id, term_id) VALUES ($1, $2) ON CONFLICT DO NOTHING',
          [productId, tid]
        );
      }
    }

    if (Array.isArray(input.gallery_media_ids)) {
      await tx.query('DELETE FROM public.product_gallery_items WHERE product_id = $1', [productId]);
      let sortOrder = 0;
      for (const mid of new Set(input.gallery_media_ids)) {
        await tx.query(
          'INSERT INTO public.product_gallery_items (product_id, media_id, sort_order) VALUES ($1, $2, $3)',
          [productId, mid, sortOrder++]
        );
      }
    }

    return productId;
  });

  const full = await getProductByIdOrSlug({ id: savedId }, true);
  if (!full) throw new AppError('Product save failed.', 500, 'save_failed');
  return full;
}

export async function deleteProductSafely(productId: string): Promise<{ archived: boolean }> {
  const ordersCheck = await query<{ cnt: string }>(
    'SELECT COUNT(*)::text AS cnt FROM public.orders WHERE product_id = $1',
    [productId]
  );
  if (Number(ordersCheck.rows[0]?.cnt || 0) > 0) {
    // Safeguard: preserve referential integrity for historical orders by archiving instead of hard-deleting
    await query(`UPDATE public.products SET status = 'archived' WHERE id = $1`, [productId]);
    return { archived: true };
  }
  const del = await query('DELETE FROM public.products WHERE id = $1 RETURNING id', [productId]);
  if (del.rowCount === 0) throw new AppError('Product not found.', 404, 'not_found');
  return { archived: false };
}

export interface SaveProductMasterInput {
  product_type: 'polymer' | 'pipe';
  gst_percent: string;
  hsn: string;
  polymer_packs: Record<
    string,
    {
      price_rupees?: string;
      enabled?: boolean;
      weight_kg?: string;
      length_cm?: string;
      width_cm?: string;
      height_cm?: string;
    }
  >;
  packet_label?: string;
  packet_price_rupees?: string;
  packet_rows_text?: string;
}

/**
 * Exact validation & atomic persistence matching `raghushree_master_box_save()`.
 */
export async function saveProductMaster(productId: string, input: SaveProductMasterInput) {
  const gstBps = parseDecimalPaise(String(input.gst_percent ?? ''), 10000);
  const hsn = (input.hsn || '').trim();
  if (
    !['polymer', 'pipe'].includes(input.product_type) ||
    gstBps === null ||
    (hsn !== '' && !/^[0-9]{4,8}$/.test(hsn))
  ) {
    throw new AppError(
      'Product Master was not saved. Check GST (0-100%) and HSN (4-8 digits).',
      400,
      'invalid_master_config'
    );
  }

  const parsedPacks: Array<{
    option_key: string;
    variation_kind: 'polymer_pack' | 'sample';
    label: string;
    quantity: number;
    price_paise: number;
    enabled: boolean;
    weight_kg: string | null;
    length_cm: string | null;
    width_cm: string | null;
    height_cm: string | null;
  }> = [];

  for (const key of [...POLYMER_PACK_KEYS, 'sample'] as const) {
    const row = input.polymer_packs?.[key] || {};
    const rawPrice = (row.price_rupees || '').trim();
    const paise =
      key === 'sample' || rawPrice === '' ? 0 : parseDecimalPaise(rawPrice, 1000000000);
    const enabled = Boolean(row.enabled);

    if (paise === null || (key !== 'sample' && enabled && paise < 1)) {
      throw new AppError(
        `Invalid price for pack "${key}". Enabled packs require a positive price.`,
        400,
        'invalid_pack_price'
      );
    }

    const fields = ['weight_kg', 'length_cm', 'width_cm', 'height_cm'] as const;
    const measurements: Record<string, string | null> = {};
    for (const f of fields) {
      const val = (row[f] || '').trim();
      if (val !== '' && !isValidMeasurement(val)) {
        throw new AppError(
          `Invalid ${f} measurement for "${key}". Must be between 0.001 and 99999.`,
          400,
          'invalid_measurement'
        );
      }
      measurements[f] = val === '' ? null : val;
    }

    parsedPacks.push({
      option_key: key,
      variation_kind: key === 'sample' ? 'sample' : 'polymer_pack',
      label: POLYMER_PACK_LABELS[key],
      quantity: 1,
      price_paise: paise,
      enabled,
      weight_kg: measurements.weight_kg,
      length_cm: measurements.length_cm,
      width_cm: measurements.width_cm,
      height_cm: measurements.height_cm,
    });
  }

  const rawPacketPrice = (input.packet_price_rupees || '').trim();
  const packetPricePaise =
    rawPacketPrice === '' ? 0 : parseDecimalPaise(rawPacketPrice, 1000000000);
  if (packetPricePaise === null) {
    throw new AppError('Invalid packet price.', 400, 'invalid_packet_price');
  }

  const packetLabel = (input.packet_label || '').trim();
  if (packetLabel.length > 80) {
    throw new AppError('Packet label must be 80 characters or fewer.', 400, 'invalid_packet_label');
  }

  const packetRowsRaw = (input.packet_rows_text || '').trim();
  const packetMap = new Map<
    number,
    { weight_kg: string; length_cm: string; width_cm: string; height_cm: string }
  >();

  if (packetRowsRaw !== '') {
    const lines = packetRowsRaw.split(/\r\n|\r|\n/).map((l) => l.trim()).filter(Boolean);
    for (const line of lines) {
      const parts = line.split(',').map((p) => p.trim());
      if (parts.length !== 5 || !/^[1-9][0-9]{0,2}$/.test(parts[0])) {
        throw new AppError(
          'Each packet row must be: quantity (1-999), weight_kg, length_cm, width_cm, height_cm.',
          400,
          'invalid_packet_row'
        );
      }
      const qty = Number.parseInt(parts[0], 10);
      if (packetMap.has(qty)) {
        throw new AppError(
          `Duplicate packet quantity ${qty}. Each quantity may appear only once.`,
          400,
          'duplicate_packet_qty'
        );
      }
      for (let i = 1; i <= 4; i++) {
        if (!isValidMeasurement(parts[i])) {
          throw new AppError(
            `Invalid measurement "${parts[i]}" on packet row ${qty}.`,
            400,
            'invalid_packet_measurement'
          );
        }
      }
      packetMap.set(qty, {
        weight_kg: parts[1],
        length_cm: parts[2],
        width_cm: parts[3],
        height_cm: parts[4],
      });
    }
  }

  await withTransaction(async (tx) => {
    await tx.query(
      `INSERT INTO public.product_master_configs (product_id, product_type, gst_bps, hsn, packet_label, packet_price_paise)
       VALUES ($1, $2, $3, $4, $5, $6)
       ON CONFLICT (product_id) DO UPDATE SET
         product_type = EXCLUDED.product_type,
         gst_bps = EXCLUDED.gst_bps,
         hsn = EXCLUDED.hsn,
         packet_label = EXCLUDED.packet_label,
         packet_price_paise = EXCLUDED.packet_price_paise`,
      [productId, input.product_type, gstBps, hsn, packetLabel, packetPricePaise]
    );

    // Upsert polymer_pack and sample rows; preserve legacy_pack (30kg) untouched
    for (const p of parsedPacks) {
      await tx.query(
        `INSERT INTO public.product_variations (
          product_id, option_key, variation_kind, label, quantity, price_paise, enabled,
          weight_kg, length_cm, width_cm, height_cm
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)
        ON CONFLICT (product_id, option_key) DO UPDATE SET
          variation_kind = EXCLUDED.variation_kind,
          label = EXCLUDED.label,
          quantity = EXCLUDED.quantity,
          price_paise = EXCLUDED.price_paise,
          enabled = EXCLUDED.enabled,
          weight_kg = EXCLUDED.weight_kg,
          length_cm = EXCLUDED.length_cm,
          width_cm = EXCLUDED.width_cm,
          height_cm = EXCLUDED.height_cm`,
        [
          productId,
          p.option_key,
          p.variation_kind,
          p.label,
          p.quantity,
          p.price_paise,
          p.enabled,
          p.weight_kg,
          p.length_cm,
          p.width_cm,
          p.height_cm,
        ]
      );
    }

    // Replace pipe_packet rows atomically
    await tx.query(
      `DELETE FROM public.product_variations WHERE product_id = $1 AND variation_kind = 'pipe_packet'`,
      [productId]
    );
    const sortedQtys = Array.from(packetMap.keys()).sort((a, b) => a - b);
    for (const qty of sortedQtys) {
      const m = packetMap.get(qty)!;
      await tx.query(
        `INSERT INTO public.product_variations (
          product_id, option_key, variation_kind, label, quantity, price_paise, enabled,
          weight_kg, length_cm, width_cm, height_cm
        ) VALUES ($1, $2, 'pipe_packet', $3, $4, $5, $6, $7, $8, $9, $10)`,
        [
          productId,
          `packet_${qty}`,
          `${qty} ${packetLabel || 'packet(s)'}`,
          qty,
          packetPricePaise * qty,
          packetPricePaise > 0,
          m.weight_kg,
          m.length_cm,
          m.width_cm,
          m.height_cm,
        ]
      );
    }
  });

  const updated = await getProductByIdOrSlug({ id: productId }, true);
  return updated;
}

export interface SaveProductSectionsInput {
  intros: {
    features?: string;
    applications?: string;
    specifications?: string;
    faqs?: string;
  };
  sections: {
    features?: string[][];
    applications?: string[][];
    specifications?: string[][];
    faqs?: string[][];
  };
}

/**
 * Exact validation & atomic persistence matching `raghushree_save_product_sections()`.
 */
export async function saveProductSections(productId: string, input: SaveProductSectionsInput) {
  const defs = {
    features: { cols: 2, required: 1 },
    applications: { cols: 2, required: 1 },
    specifications: { cols: 3, required: 2 },
    faqs: { cols: 2, required: 2 },
  } as const;

  const cleanIntros: Record<string, string> = {};
  const cleanSections: Record<string, string[][]> = {};

  for (const key of Object.keys(defs) as Array<keyof typeof defs>) {
    const intro = (input.intros?.[key] ?? '').trim();
    if (intro.length > 4800) {
      throw new AppError(
        `Introduction for ${key} exceeds maximum length (4800 chars).`,
        400,
        'invalid_section_intro'
      );
    }
    cleanIntros[key] = intro;

    const rawRows = input.sections?.[key] ?? [];
    if (!Array.isArray(rawRows) || rawRows.length > 50) {
      throw new AppError(`Too many rows in ${key} (max 50).`, 400, 'invalid_section_rows');
    }
    const spec = defs[key];
    cleanSections[key] = [];
    for (const row of rawRows) {
      if (!Array.isArray(row)) {
        throw new AppError(`Invalid row structure in ${key}.`, 400, 'invalid_section_row');
      }
      const cleanRow: string[] = [];
      let anyFilled = false;
      for (let i = 0; i < spec.cols; i++) {
        const val = String(row[i] ?? '').trim();
        if (val.length > 2000) {
          throw new AppError(
            `Field value in ${key} exceeds 2000 characters.`,
            400,
            'invalid_section_length'
          );
        }
        cleanRow.push(val);
        if (val !== '') anyFilled = true;
      }
      if (!anyFilled) continue;
      for (let i = 0; i < spec.required; i++) {
        if (cleanRow[i] === '') {
          throw new AppError(
            `Required field missing in ${key} row.`,
            400,
            'missing_required_section_field'
          );
        }
      }
      cleanSections[key].push(cleanRow);
    }
  }

  await withTransaction(async (tx) => {
    for (const key of Object.keys(defs)) {
      await tx.query(
        `INSERT INTO public.product_section_intros (product_id, section_key, intro_text)
         VALUES ($1, $2, $3)
         ON CONFLICT (product_id, section_key) DO UPDATE SET intro_text = EXCLUDED.intro_text`,
        [productId, key, cleanIntros[key]]
      );
    }
    await tx.query('DELETE FROM public.product_sections WHERE product_id = $1', [productId]);
    for (const key of Object.keys(defs)) {
      const rows = cleanSections[key];
      for (let i = 0; i < rows.length; i++) {
        await tx.query(
          `INSERT INTO public.product_sections (product_id, section_key, sort_order, col_1, col_2, col_3)
           VALUES ($1, $2, $3, $4, $5, $6)`,
          [productId, key, i, rows[i][0] || '', rows[i][1] || '', rows[i][2] || '']
        );
      }
    }
  });

  return getProductByIdOrSlug({ id: productId }, true);
}

/**
 * Non-destructive idempotent V2 catalog installer matching `raghushree_install_catalog()`.
 */
export async function installCatalogV2() {
  let createdCategories = 0;
  let createdProducts = 0;

  await withTransaction(async (tx) => {
    for (let i = 0; i < CATALOG_V2_DEFINITIONS.length; i++) {
      const def = CATALOG_V2_DEFINITIONS[i];
      const existingCat = await tx.query<{ id: string }>(
        'SELECT id FROM public.product_categories WHERE slug = $1',
        [def.slug]
      );
      let catId = existingCat.rows[0]?.id;
      if (!catId) {
        const ins = await tx.query<{ id: string }>(
          `INSERT INTO public.product_categories (slug, title, category_type, sort_order, is_catalog_v2)
           VALUES ($1, $2, $3, $4, TRUE) RETURNING id`,
          [def.slug, def.title, def.type, i + 1]
        );
        catId = ins.rows[0].id;
        createdCategories++;
      }

      for (const prod of def.products) {
        const catalogKey = `${def.slug}:${prod.slug}`;
        const existingProd = await tx.query<{ id: string }>(
          'SELECT id FROM public.products WHERE catalog_key = $1 OR slug = $2 LIMIT 1',
          [catalogKey, prod.slug]
        );
        let prodId = existingProd.rows[0]?.id;
        if (!prodId) {
          const insP = await tx.query<{ id: string }>(
            `INSERT INTO public.products (
              slug, title, polymer_code, primary_category_id, image_slot, short_desc,
              status, is_legacy_catalog, catalog_key
            ) VALUES ($1, $2, $3, $4, $5, $6, 'published', FALSE, $7)
            RETURNING id`,
            [
              prod.slug,
              prod.title,
              def.type === 'polymer' ? def.title.split(' ')[0] : 'PIPE',
              catId,
              prod.imageSlot,
              prod.shortDesc,
              catalogKey,
            ]
          );
          prodId = insP.rows[0].id;
          createdProducts++;
          await tx.query(
            `INSERT INTO public.product_master_configs (product_id, product_type, gst_bps, hsn, packet_label, packet_price_paise)
             VALUES ($1, $2, 1800, '', '', 0) ON CONFLICT DO NOTHING`,
            [prodId, def.type]
          );
        }
        await tx.query(
          'INSERT INTO public.product_category_links (product_id, category_id) VALUES ($1, $2) ON CONFLICT DO NOTHING',
          [prodId, catId]
        );
      }
    }
  });

  return { createdCategories, createdProducts };
}

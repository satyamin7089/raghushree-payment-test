import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import dotenv from 'dotenv';
import { initDatabase, closeDatabase, runMigrations, query } from '../../../apps/api/src/db/client.js';
import {
  installCatalogV2,
  seedInitialCatalogIfEmpty,
  createOrUpdateProduct,
  saveProductMaster,
  saveProductSections,
  getProductByIdOrSlug,
} from '../../../apps/api/src/services/catalogService.js';
import { ensureBootstrapAdmin } from '../../../apps/api/src/services/userService.js';
import { parseWxrXml, type ParsedWpProduct } from './parser.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
dotenv.config({ path: path.resolve(__dirname, '../../../.env') });

export interface MigrationReport {
  mode: 'seed' | 'wxr' | 'json';
  dryRun: boolean;
  categoriesCreated: number;
  v2ProductsCreated: number;
  legacyProductsCreated: number;
  importedProducts: number;
  updatedProducts: number;
  skippedProducts: number;
  errors: Array<{ slug: string; error: string }>;
  totals: {
    categories: number;
    products: number;
    variations: number;
    sections: number;
  };
}

function parseCliArgs(argv: string[]) {
  const out: Record<string, string | boolean> = {};
  for (const arg of argv) {
    if (arg === '--dry-run') {
      out.dryRun = true;
    } else if (arg.startsWith('--')) {
      const [k, ...rest] = arg.slice(2).split('=');
      out[k] = rest.join('=') || 'true';
    }
  }
  return out;
}

async function importParsedProducts(
  items: ParsedWpProduct[],
  dryRun: boolean
): Promise<{ imported: number; updated: number; skipped: number; errors: Array<{ slug: string; error: string }> }> {
  let imported = 0;
  let updated = 0;
  let skipped = 0;
  const errors: Array<{ slug: string; error: string }> = [];

  for (const item of items) {
    try {
      const existing = await getProductByIdOrSlug({ slug: item.slug }, true);
      if (dryRun) {
        if (existing) updated++;
        else imported++;
        continue;
      }

      // Resolve primary category id if present
      let primaryCategoryId: string | null = null;
      const catIds: string[] = [];
      for (const catSlug of item.categories) {
        const res = await query<{ id: string }>(
          'SELECT id FROM public.product_categories WHERE slug = $1',
          [catSlug]
        );
        if (res.rows[0]) {
          catIds.push(res.rows[0].id);
          if (!primaryCategoryId) primaryCategoryId = res.rows[0].id;
        }
      }

      const m = item.meta;
      const saved = await createOrUpdateProduct(
        existing ? existing.id : null,
        {
          title: item.title,
          slug: item.slug,
          polymer_code: String(m._raghushree_polymer_code || ''),
          primary_category_id: primaryCategoryId,
          category_ids: catIds,
          image_slot: String(m._raghushree_image_slot || 'polymer_hdpe'),
          short_desc: String(m._raghushree_short_desc || item.excerpt || ''),
          content_html: item.contentHtml,
          highlights: Array.isArray(m._raghushree_highlights)
            ? (m._raghushree_highlights as string[])
            : undefined,
          grades_notes: String(m._raghushree_grades_notes || ''),
          packaging: String(m._raghushree_packaging || ''),
          pack_size: String(m._raghushree_pack_size || ''),
          moq: String(m._raghushree_moq || ''),
          availability: String(m._raghushree_availability || ''),
          status: item.status,
          menu_order: item.menuOrder,
          seo: {
            seo_title: String(m._raghushree_seo_title || ''),
            seo_description: String(m._raghushree_seo_description || ''),
            seo_canonical: String(m._raghushree_seo_canonical || ''),
            seo_robots: String(m._raghushree_seo_robots || 'index,follow'),
          },
        },
        null
      );

      const masterRaw = m.raghushree_product_master as Record<string, unknown> | undefined;
      if (masterRaw && typeof masterRaw === 'object') {
        await saveProductMaster(saved.id, {
          product_type: masterRaw.product_type === 'pipe' ? 'pipe' : 'polymer',
          gst_percent: String(masterRaw.gst_percent || '18'),
          hsn: String(masterRaw.hsn || ''),
          polymer_packs: (masterRaw.polymer_packs as Record<string, never>) || {},
          packet_price_rupees: String(masterRaw.packet_price_rupees || ''),
          packet_label: String(masterRaw.packet_label || ''),
          packet_rows_text: String(masterRaw.packet_rows_text || ''),
        });
      }

      const sectionsRaw = m.raghushree_product_sections as Record<string, unknown> | undefined;
      if (sectionsRaw && typeof sectionsRaw === 'object') {
        await saveProductSections(saved.id, {
          intros: (sectionsRaw.intros as Record<string, string>) || {},
          sections: (sectionsRaw.sections as Record<string, string[][]>) || {},
        });
      }

      if (existing) updated++;
      else imported++;
    } catch (err) {
      skipped++;
      errors.push({
        slug: item.slug,
        error: err instanceof Error ? err.message : String(err),
      });
    }
  }

  return { imported, updated, skipped, errors };
}

export async function runMigrationCli(options: {
  source?: 'seed' | 'wxr' | 'json';
  file?: string;
  dryRun?: boolean;
}): Promise<MigrationReport> {
  const mode = options.source || 'seed';
  const dryRun = Boolean(options.dryRun);

  await initDatabase();
  await runMigrations();
  await ensureBootstrapAdmin();

  let categoriesCreated = 0;
  let v2ProductsCreated = 0;
  let legacyProductsCreated = 0;
  let importedProducts = 0;
  let updatedProducts = 0;
  let skippedProducts = 0;
  let errors: Array<{ slug: string; error: string }> = [];

  if (!dryRun || mode === 'seed') {
    const beforeProd = await query<{ c: string }>('SELECT COUNT(*)::text AS c FROM public.products');
    await seedInitialCatalogIfEmpty();
    const v2Res = await installCatalogV2();
    const afterProd = await query<{ c: string }>('SELECT COUNT(*)::text AS c FROM public.products');
    categoriesCreated = v2Res.createdCategories;
    v2ProductsCreated = v2Res.createdProducts;
    legacyProductsCreated = Math.max(0, Number(afterProd.rows[0].c) - Number(beforeProd.rows[0].c) - v2ProductsCreated);
  }

  if ((mode === 'wxr' || mode === 'json') && options.file) {
    const raw = await fs.readFile(path.resolve(options.file), 'utf8');
    let parsedProducts: ParsedWpProduct[] = [];
    if (mode === 'wxr') {
      parsedProducts = parseWxrXml(raw).products;
    } else {
      const json = JSON.parse(raw) as ParsedWpProduct[] | { products?: ParsedWpProduct[] };
      parsedProducts = Array.isArray(json) ? json : json.products || [];
    }
    const res = await importParsedProducts(parsedProducts, dryRun);
    importedProducts = res.imported;
    updatedProducts = res.updated;
    skippedProducts = res.skipped;
    errors = res.errors;
  }

  const [catCount, prodCount, varCount, secCount] = await Promise.all([
    query<{ c: string }>('SELECT COUNT(*)::text AS c FROM public.product_categories'),
    query<{ c: string }>('SELECT COUNT(*)::text AS c FROM public.products'),
    query<{ c: string }>('SELECT COUNT(*)::text AS c FROM public.product_variations'),
    query<{ c: string }>('SELECT COUNT(*)::text AS c FROM public.product_sections'),
  ]);

  return {
    mode,
    dryRun,
    categoriesCreated,
    v2ProductsCreated,
    legacyProductsCreated,
    importedProducts,
    updatedProducts,
    skippedProducts,
    errors,
    totals: {
      categories: Number(catCount.rows[0].c),
      products: Number(prodCount.rows[0].c),
      variations: Number(varCount.rows[0].c),
      sections: Number(secCount.rows[0].c),
    },
  };
}

if (process.argv[1] && path.resolve(process.argv[1]) === path.resolve(fileURLToPath(import.meta.url))) {
  const args = parseCliArgs(process.argv.slice(2));
  const source = (args.source as 'seed' | 'wxr' | 'json') || 'seed';
  const file = typeof args.file === 'string' ? args.file : undefined;
  const dryRun = Boolean(args.dryRun);

  runMigrationCli({ source, file, dryRun })
    .then(async (report) => {
      console.log(JSON.stringify(report, null, 2));
      await closeDatabase();
      process.exit(report.errors.length > 0 ? 1 : 0);
    })
    .catch(async (err) => {
      console.error('Migration failed:', err);
      await closeDatabase();
      process.exit(1);
    });
}

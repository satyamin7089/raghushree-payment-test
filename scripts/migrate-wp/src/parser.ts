import { XMLParser } from 'fast-xml-parser';

export interface ParsedWpProduct {
  wpId: number;
  slug: string;
  title: string;
  status: 'published' | 'draft' | 'archived';
  contentHtml: string;
  excerpt: string;
  menuOrder: number;
  categories: string[];
  grades: string[];
  forms: string[];
  applications: string[];
  meta: Record<string, unknown>;
}

/**
 * Lightweight PHP serialized string/array extractor for common WordPress postmeta arrays
 * (handles `a:N:{...}`, `s:N:"...";`, `i:N;`, `b:0|1;`, `d:N;`, `N;`).
 */
export function parsePhpSerialized(input: string): unknown {
  const trimmed = input.trim();
  if (!trimmed) return '';
  if (trimmed.startsWith('{') || trimmed.startsWith('[')) {
    try {
      return JSON.parse(trimmed);
    } catch {
      return trimmed;
    }
  }
  if (!/^[aidsbN]:/.test(trimmed)) return trimmed;

  let pos = 0;
  const readUntil = (ch: string) => {
    const idx = trimmed.indexOf(ch, pos);
    if (idx === -1) return '';
    const out = trimmed.slice(pos, idx);
    pos = idx + ch.length;
    return out;
  };

  const readVal = (): unknown => {
    const type = trimmed[pos];
    pos++;
    if (type === 'N') {
      pos++; // ';'
      return null;
    }
    pos++; // ':'
    if (type === 'b') {
      const v = readUntil(';');
      return v === '1';
    }
    if (type === 'i' || type === 'd') {
      const v = readUntil(';');
      return Number(v);
    }
    if (type === 's') {
      const len = Number(readUntil(':'));
      pos++; // '"'
      // PHP counts UTF-8 bytes; use byte-safe slice via Buffer
      const restBuf = Buffer.from(trimmed.slice(pos), 'utf8');
      const str = restBuf.subarray(0, len).toString('utf8');
      pos += str.length + 2; // '";'
      return str;
    }
    if (type === 'a') {
      const count = Number(readUntil(':'));
      pos++; // '{'
      const entries: Array<[string | number, unknown]> = [];
      let isSequential = true;
      for (let i = 0; i < count; i++) {
        const k = readVal() as string | number;
        const v = readVal();
        if (k !== i) isSequential = false;
        entries.push([k, v]);
      }
      pos++; // '}'
      if (isSequential) {
        return entries.map(([, v]) => v);
      }
      const obj: Record<string, unknown> = {};
      for (const [k, v] of entries) {
        obj[String(k)] = v;
      }
      return obj;
    }
    return trimmed;
  };

  try {
    return readVal();
  } catch {
    return trimmed;
  }
}

function asArray<T>(val: T | T[] | undefined): T[] {
  if (!val) return [];
  return Array.isArray(val) ? val : [val];
}

export function parseWxrXml(xmlContent: string): {
  products: ParsedWpProduct[];
  attachments: Map<number, { url: string; title: string }>;
} {
  const parser = new XMLParser({
    ignoreAttributes: false,
    attributeNamePrefix: '@_',
    removeNSPrefix: true,
    parseTagValue: false,
  });

  const parsed = parser.parse(xmlContent) as {
    rss?: { channel?: { item?: unknown } };
  };
  const items = asArray(parsed?.rss?.channel?.item) as Array<Record<string, unknown>>;

  const attachments = new Map<number, { url: string; title: string }>();
  const products: ParsedWpProduct[] = [];

  for (const item of items) {
    const postType = String(item.post_type || '');
    const wpId = Number(item.post_id || 0);
    if (postType === 'attachment') {
      const url = String(item.attachment_url || item.guid || '');
      if (wpId && url) {
        attachments.set(wpId, { url, title: String(item.title || '') });
      }
      continue;
    }
    if (postType !== 'polymer_product') continue;

    const rawStatus = String(item.status || 'publish');
    const status: ParsedWpProduct['status'] =
      rawStatus === 'publish' ? 'published' : rawStatus === 'trash' ? 'archived' : 'draft';

    const meta: Record<string, unknown> = {};
    for (const m of asArray(item.postmeta as Record<string, unknown>[])) {
      const key = String(m.meta_key || '');
      const val = String(m.meta_value || '');
      if (key) {
        meta[key] = parsePhpSerialized(val);
      }
    }

    const categories: string[] = [];
    const grades: string[] = [];
    const forms: string[] = [];
    const applications: string[] = [];

    for (const c of asArray(item.category as Array<Record<string, unknown> | string>)) {
      if (typeof c === 'string') continue;
      const domain = String(c['@_domain'] || '');
      const nicename = String(c['@_nicename'] || '');
      if (!nicename) continue;
      if (domain === 'polymer_category') categories.push(nicename);
      if (domain === 'material_grade') grades.push(nicename);
      if (domain === 'material_form') forms.push(nicename);
      if (domain === 'application') applications.push(nicename);
    }

    products.push({
      wpId,
      slug: String(item.post_name || `product-${wpId}`),
      title: String(item.title || 'Untitled Product'),
      status,
      contentHtml: String(item.encoded || ''),
      excerpt: String(item.excerpt || ''),
      menuOrder: Number(item.menu_order || 0),
      categories,
      grades,
      forms,
      applications,
      meta,
    });
  }

  return { products, attachments };
}

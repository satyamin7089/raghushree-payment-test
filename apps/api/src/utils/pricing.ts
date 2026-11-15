/**
 * Exact migration of Raghushree Product Master, Integer-Paise GST & Shiprocket Rate Selection.
 * Mirrors `inc/product-master.php` and `inc/shipping-quotes.php` rules faithfully.
 */

export const POLYMER_PACK_KEYS = ['5kg', '10kg', '15kg', '20kg', '25kg'] as const;

export const POLYMER_PACK_LABELS: Record<string, string> = {
  '5kg': '5 kg',
  '10kg': '10 kg',
  '15kg': '15 kg',
  '20kg': '20 kg',
  '25kg': '25 kg',
  sample: 'Sample',
};

export interface ProductMasterConfig {
  product_type: 'polymer' | 'pipe';
  gst_bps: number;
  hsn: string;
  packet_label: string;
  packet_price_paise: number;
}

export interface ProductVariationRow {
  option_key: string;
  variation_kind: 'polymer_pack' | 'sample' | 'pipe_packet' | 'legacy_pack';
  label: string;
  quantity: number;
  price_paise: number;
  enabled: boolean;
  weight_kg: string;
  length_cm: string;
  width_cm: string;
  height_cm: string;
  gst_bps: number;
  hsn: string;
  product_type: 'polymer' | 'pipe';
}

/**
 * Round GST to nearest paise using integer basis points:
 * intdiv(paise * basis_points + 5000, 10000)
 */
export function taxPaise(paise: number, basisPoints: number): number {
  const p = Math.trunc(paise);
  const bps = Math.trunc(basisPoints);
  return Math.floor((p * bps + 5000) / 10000);
}

/**
 * Parse positive decimal rupee or percentage string into integer paise/basis-points.
 * Matches PHP: `/^(\d{1,8})(?:\.(\d{1,2}))?$/D`
 */
export function parseDecimalPaise(value: string, maxPaise: number): number | null {
  const trimmed = value.trim();
  const match = /^(\d{1,8})(?:\.(\d{1,2}))?$/.exec(trimmed);
  if (!match) return null;
  const whole = Number.parseInt(match[1], 10);
  const frac = Number.parseInt((match[2] ?? '').padEnd(2, '0'), 10);
  const result = whole * 100 + frac;
  return result <= maxPaise ? result : null;
}

export function isValidMeasurement(val: unknown): boolean {
  if (typeof val !== 'string' && typeof val !== 'number') return false;
  const str = String(val).trim();
  if (!/^\d{1,5}(?:\.\d{1,3})?$/.test(str)) return false;
  const num = Number(str);
  return Number.isFinite(num) && num > 0 && num <= 99999;
}

/**
 * Build runtime variations matching `raghushree_master_variations()`.
 * - Legacy `30kg` rows are retained in DB but never exposed for checkout.
 * - Sample price is always 0.
 * - Pipe products derive `packet_N` rows from `packet_price_paise * qty` and per-qty combined measurements.
 */
export function buildMasterVariations(
  config: ProductMasterConfig,
  savedRows: Array<{
    option_key: string;
    variation_kind: string;
    label: string;
    quantity: number;
    price_paise: number | string;
    enabled: boolean;
    weight_kg: string | number | null;
    length_cm: string | number | null;
    width_cm: string | number | null;
    height_cm: string | number | null;
  }>
): Record<string, ProductVariationRow> {
  const byKey = new Map(savedRows.map((r) => [r.option_key, r]));
  const result: Record<string, ProductVariationRow> = {};

  if (config.product_type === 'pipe') {
    const packetRows = savedRows
      .filter((r) => r.variation_kind === 'pipe_packet' && r.quantity >= 1)
      .sort((a, b) => a.quantity - b.quantity);

    for (const row of packetRows) {
      const qty = Math.trunc(row.quantity);
      const key = `packet_${qty}`;
      const packetLabel = config.packet_label || 'packet(s)';
      result[key] = {
        option_key: key,
        variation_kind: 'pipe_packet',
        label: `${qty} ${packetLabel}`,
        quantity: qty,
        price_paise: Math.trunc(Number(config.packet_price_paise)) * qty,
        enabled: Number(config.packet_price_paise) > 0,
        weight_kg: row.weight_kg != null ? String(row.weight_kg) : '',
        length_cm: row.length_cm != null ? String(row.length_cm) : '',
        width_cm: row.width_cm != null ? String(row.width_cm) : '',
        height_cm: row.height_cm != null ? String(row.height_cm) : '',
        gst_bps: config.gst_bps,
        hsn: config.hsn,
        product_type: 'pipe',
      };
    }
  } else {
    for (const key of POLYMER_PACK_KEYS) {
      const saved = byKey.get(key);
      result[key] = {
        option_key: key,
        variation_kind: 'polymer_pack',
        label: POLYMER_PACK_LABELS[key],
        quantity: 1,
        price_paise: saved ? Math.trunc(Number(saved.price_paise)) : 0,
        enabled: saved ? Boolean(saved.enabled) : false,
        weight_kg: saved?.weight_kg != null ? String(saved.weight_kg) : '',
        length_cm: saved?.length_cm != null ? String(saved.length_cm) : '',
        width_cm: saved?.width_cm != null ? String(saved.width_cm) : '',
        height_cm: saved?.height_cm != null ? String(saved.height_cm) : '',
        gst_bps: config.gst_bps,
        hsn: config.hsn,
        product_type: 'polymer',
      };
    }
  }

  const savedSample = byKey.get('sample');
  result.sample = {
    option_key: 'sample',
    variation_kind: 'sample',
    label: 'Sample',
    quantity: 1,
    price_paise: 0,
    enabled: savedSample ? Boolean(savedSample.enabled) : false,
    weight_kg: savedSample?.weight_kg != null ? String(savedSample.weight_kg) : '',
    length_cm: savedSample?.length_cm != null ? String(savedSample.length_cm) : '',
    width_cm: savedSample?.width_cm != null ? String(savedSample.width_cm) : '',
    height_cm: savedSample?.height_cm != null ? String(savedSample.height_cm) : '',
    gst_bps: config.gst_bps,
    hsn: config.hsn,
    product_type: config.product_type,
  };

  return result;
}

export interface ShiprocketCourierRate {
  courier_id: string;
  courier_name: string;
  rate_paise: number;
}

/**
 * Exact migration of `raghushree_lowest_shipping_rate()`:
 * Picks the lowest valid non-blocked prepaid courier rate from available_courier_companies.
 */
export function selectLowestShippingRate(data: unknown): ShiprocketCourierRate | null {
  if (!data || typeof data !== 'object') return null;
  const companies = (data as Record<string, unknown>).available_courier_companies;
  if (!Array.isArray(companies) || companies.length === 0) return null;

  let best: ShiprocketCourierRate | null = null;
  for (const courier of companies) {
    if (!courier || typeof courier !== 'object') continue;
    const c = courier as Record<string, unknown>;
    const rawId = c.courier_company_id;
    const rawRate = c.rate;
    const blocked = c.blocked;

    if (rawId == null || rawRate == null) continue;
    if (typeof rawId !== 'string' && typeof rawId !== 'number') continue;
    const idStr = String(rawId);
    if (!/^[1-9][0-9]*$/.test(idStr)) continue;
    if (blocked && blocked !== 0 && blocked !== '0') continue;
    if (typeof rawRate !== 'number' && typeof rawRate !== 'string') continue;
    if (typeof rawRate === 'string' && rawRate.trim() === '') continue;

    const rateNum = Number(rawRate);
    if (!Number.isFinite(rateNum) || rateNum < 0.005 || rateNum > 10000000) continue;

    const ratePaise = Math.round(rateNum * 100);
    const courierName =
      typeof c.courier_name === 'string' ? c.courier_name.replace(/<[^>]*>/g, '').trim() : '';

    if (best === null || ratePaise < best.rate_paise) {
      best = {
        courier_id: idStr,
        courier_name: courierName,
        rate_paise: ratePaise,
      };
    }
  }

  return best;
}

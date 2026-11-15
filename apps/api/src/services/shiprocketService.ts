import { query, withTransaction } from '../db/client.js';
import { AppError } from '../utils/errors.js';
import { randomTokenHex, sha256Hex } from '../utils/crypto.js';
import {
  buildMasterVariations,
  selectLowestShippingRate,
  taxPaise,
  type ProductMasterConfig,
} from '../utils/pricing.js';
import { getPaymentShippingConfig, isCheckoutReadyFromConfig } from './settingsService.js';

let cachedToken: { token: string; expiresAt: number; cacheKey: string } | null = null;

export interface ShiprocketQuoteInput {
  productIdOrSlug: string;
  variationKey: string;
  pincode: string;
}

export interface ShiprocketQuoteResult {
  quote_token: string;
  pincode: string;
  product_id: string;
  product_title: string;
  variation: string;
  option_label: string;
  product_paise: number;
  product_gst_paise: number;
  gst_bps: number;
  hsn: string;
  rate_paise: number;
  shipping_gst_paise: number;
  total_paise: number;
  courier_name: string;
  courier_id: string;
  weight_kg: number;
  dimensions_cm: { length: number; width: number; height: number };
  expires_at: string;
}

async function getShiprocketToken(email: string, password: string): Promise<string> {
  const cacheKey = sha256Hex(`${email}:${password}`).slice(0, 16);
  if (cachedToken && cachedToken.cacheKey === cacheKey && cachedToken.expiresAt > Date.now()) {
    return cachedToken.token;
  }

  const res = await fetch('https://apiv2.shiprocket.in/v1/external/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password }),
    signal: AbortSignal.timeout(15000),
  });
  const body = (await res.json().catch(() => ({}))) as { token?: string; message?: string };
  if (!res.ok || !body.token) {
    throw new AppError(
      body.message || 'Could not authenticate with shipping carrier.',
      502,
      'shiprocket_auth_failed'
    );
  }
  cachedToken = {
    token: body.token,
    cacheKey,
    expiresAt: Date.now() + 9 * 24 * 60 * 60 * 1000,
  };
  return body.token;
}

export async function createShippingQuote(
  input: ShiprocketQuoteInput,
  fetchFn: typeof fetch = fetch
): Promise<ShiprocketQuoteResult> {
  const pincode = input.pincode.trim();
  if (!/^[1-9][0-9]{5}$/.test(pincode)) {
    throw new AppError('Enter a valid 6-digit Indian PIN code.', 400, 'invalid_pincode');
  }

  const cfg = await getPaymentShippingConfig();
  if (!isCheckoutReadyFromConfig(cfg)) {
    throw new AppError(
      'Online shipping quote and checkout are currently disabled.',
      503,
      'checkout_disabled'
    );
  }

  const prodRes = await query<{ id: string; title: string; status: string }>(
    `SELECT id, title, status FROM public.products
     WHERE (id::text = $1 OR slug = $1) AND status = 'published' LIMIT 1`,
    [input.productIdOrSlug]
  );
  const product = prodRes.rows[0];
  if (!product) {
    throw new AppError('Product not found.', 404, 'product_not_found');
  }

  const [mcRes, varsRes] = await Promise.all([
    query<ProductMasterConfig>(
      'SELECT product_type, gst_bps, hsn, packet_label, packet_price_paise FROM public.product_master_configs WHERE product_id = $1',
      [product.id]
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
       FROM public.product_variations WHERE product_id = $1`,
      [product.id]
    ),
  ]);

  const masterCfg: ProductMasterConfig = mcRes.rows[0]
    ? {
        ...mcRes.rows[0],
        gst_bps: Number(mcRes.rows[0].gst_bps),
        packet_price_paise: Number(mcRes.rows[0].packet_price_paise),
      }
    : {
        product_type: 'polymer',
        gst_bps: 1800,
        hsn: '',
        packet_label: '',
        packet_price_paise: 0,
      };

  const variations = buildMasterVariations(masterCfg, varsRes.rows);
  const variation = variations[input.variationKey];
  if (!variation || !variation.enabled) {
    throw new AppError('Selected pack/option is not available for purchase.', 400, 'invalid_variation');
  }

  const weightKg = Number(variation.weight_kg);
  const lengthCm = Number(variation.length_cm);
  const widthCm = Number(variation.width_cm);
  const heightCm = Number(variation.height_cm);

  if (!weightKg || !lengthCm || !widthCm || !heightCm) {
    throw new AppError(
      'Shipping dimensions and weight are not configured for this pack.',
      400,
      'missing_shipping_dimensions'
    );
  }

  if (masterCfg.product_type === 'pipe' && weightKg > 30) {
    throw new AppError(
      'Shipments above 30 kg for pipe packets require a bulk freight quotation via our Contact page.',
      400,
      'weight_exceeds_30kg'
    );
  }

  const token =
    fetchFn === fetch
      ? await getShiprocketToken(cfg.shiprocket_email, cfg.shiprocket_password)
      : await (async () => {
          const authRes = await fetchFn('https://apiv2.shiprocket.in/v1/external/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: cfg.shiprocket_email, password: cfg.shiprocket_password }),
          });
          const authJson = (await authRes.json()) as { token?: string };
          if (!authRes.ok || !authJson.token) {
            throw new AppError('Shiprocket authentication failed.', 502, 'shiprocket_auth_failed');
          }
          return authJson.token;
        })();

  const qs = new URLSearchParams({
    pickup_postcode: cfg.pickup_pincode,
    delivery_postcode: pincode,
    cod: '0',
    weight: String(weightKg),
    length: String(lengthCm),
    breadth: String(widthCm),
    height: String(heightCm),
  });

  const svcRes = await fetchFn(
    `https://apiv2.shiprocket.in/v1/external/courier/serviceability/?${qs.toString()}`,
    {
      method: 'GET',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      signal: AbortSignal.timeout(15000),
    }
  );
  const svcBody = (await svcRes.json().catch(() => ({}))) as {
    data?: Record<string, unknown>;
    message?: string;
  };
  if (!svcRes.ok) {
    throw new AppError(
      svcBody.message || 'Courier serviceability check failed.',
      502,
      'shiprocket_serviceability_failed'
    );
  }

  const selected = selectLowestShippingRate(svcBody.data);
  if (!selected) {
    throw new AppError(
      `No courier serviceability available for PIN code ${pincode}.`,
      400,
      'pincode_not_serviceable'
    );
  }

  const productPaise = variation.price_paise;
  const productGstPaise = taxPaise(productPaise, masterCfg.gst_bps);
  const ratePaise = selected.rate_paise;
  const shippingGstPaise = taxPaise(ratePaise, 1800);
  const totalPaise = productPaise + productGstPaise + ratePaise + shippingGstPaise;

  const quoteToken = randomTokenHex(32);
  const tokenHash = sha256Hex(quoteToken);
  const expiresAt = new Date(Date.now() + 15 * 60 * 1000);

  const packSnapshot = {
    option_key: input.variationKey,
    option_label: variation.label,
    quantity: variation.quantity,
    weight_kg: weightKg,
    length_cm: lengthCm,
    width_cm: widthCm,
    height_cm: heightCm,
  };

  await query(
    `INSERT INTO public.shipping_quotes (
      token_hash, product_id, option_key, pickup_pincode, delivery_pincode, mode,
      courier_id, courier_name, product_paise, product_gst_paise, product_gst_bps,
      rate_paise, shipping_gst_paise, shipping_gst_bps, total_paise, hsn,
      pack_snapshot, quote_snapshot, expires_at
    ) VALUES (
      $1, $2, $3, $4, $5, $6,
      $7, $8, $9, $10, $11,
      $12, $13, 1800, $14, $15,
      $16::jsonb, $17::jsonb, $18
    )`,
    [
      tokenHash,
      product.id,
      input.variationKey,
      cfg.pickup_pincode,
      pincode,
      cfg.mode,
      selected.courier_id,
      selected.courier_name,
      productPaise,
      productGstPaise,
      masterCfg.gst_bps,
      ratePaise,
      shippingGstPaise,
      totalPaise,
      masterCfg.hsn,
      JSON.stringify(packSnapshot),
      JSON.stringify(svcBody.data || {}),
      expiresAt.toISOString(),
    ]
  );

  return {
    quote_token: quoteToken,
    pincode,
    product_id: product.id,
    product_title: product.title,
    variation: input.variationKey,
    option_label: variation.label,
    product_paise: productPaise,
    product_gst_paise: productGstPaise,
    gst_bps: masterCfg.gst_bps,
    hsn: masterCfg.hsn,
    rate_paise: ratePaise,
    shipping_gst_paise: shippingGstPaise,
    total_paise: totalPaise,
    courier_name: selected.courier_name,
    courier_id: selected.courier_id,
    weight_kg: weightKg,
    dimensions_cm: { length: lengthCm, width: widthCm, height: heightCm },
    expires_at: expiresAt.toISOString(),
  };
}

export async function consumeShippingQuote(
  quoteToken: string,
  expectedProductId: string,
  expectedVariationKey: string,
  expectedPincode: string
) {
  if (!/^[a-f0-9]{64}$/i.test(quoteToken.trim())) {
    throw new AppError('Invalid shipping quote token.', 400, 'invalid_quote_token');
  }
  const tokenHash = sha256Hex(quoteToken.trim());

  return withTransaction(async (tx) => {
    const res = await tx.query<{
      id: string;
      product_id: string;
      option_key: string;
      pickup_pincode: string;
      delivery_pincode: string;
      mode: string;
      courier_id: string;
      courier_name: string;
      product_paise: string | number;
      product_gst_paise: string | number;
      product_gst_bps: number;
      rate_paise: string | number;
      shipping_gst_paise: string | number;
      total_paise: string | number;
      hsn: string;
      pack_snapshot: {
        option_label?: string;
        quantity?: number;
        weight_kg?: number;
        length_cm?: number;
        width_cm?: number;
        height_cm?: number;
      };
    }>(
      `UPDATE public.shipping_quotes
       SET claimed_at = NOW()
       WHERE token_hash = $1
         AND claimed_at IS NULL
         AND expires_at > NOW()
         AND product_id = $2
         AND option_key = $3
         AND delivery_pincode = $4
       RETURNING *`,
      [tokenHash, expectedProductId, expectedVariationKey, expectedPincode]
    );

    if (!res.rows[0]) {
      throw new AppError(
        'Your shipping quote has expired or was already used. Please recalculate shipping.',
        400,
        'quote_expired_or_used'
      );
    }

    const q = res.rows[0];
    return {
      ...q,
      rate_paise: Number(q.rate_paise),
      shipping_gst_paise: Number(q.shipping_gst_paise),
      product_paise: Number(q.product_paise),
      product_gst_paise: Number(q.product_gst_paise),
      total_paise: Number(q.total_paise),
    };
  });
}

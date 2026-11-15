import { getEnv } from '../config/env.js';
import { query } from '../db/client.js';

export interface CompanyInfo {
  brand_name: string;
  legal_name: string;
  positioning: string;
  street_address: string;
  city: string;
  postal_code: string;
  state: string;
  country: string;
  country_code: string;
  full_address: string;
  email: string;
  phone: string;
  whatsapp: string;
  socials: Record<string, string>;
}

export interface PaymentShippingConfig {
  mode: 'test' | 'live';
  pickup_pincode: string;
  checkout_enabled: boolean;
  allow_live: boolean;
  allow_tax_live: boolean;
  razorpay_key_id: string;
  razorpay_key_secret: string;
  razorpay_webhook_secret: string;
  shiprocket_email: string;
  shiprocket_password: string;
}

export async function getSetting<T = Record<string, unknown>>(key: string): Promise<T | null> {
  const res = await query<{ setting_value: T }>(
    'SELECT setting_value FROM public.business_settings WHERE setting_key = $1',
    [key]
  );
  return res.rows[0]?.setting_value ?? null;
}

export async function upsertSetting(
  key: string,
  value: Record<string, unknown>,
  isSecret: boolean,
  actorId?: string | null
): Promise<void> {
  await query(
    `INSERT INTO public.business_settings (setting_key, setting_value, is_secret, updated_by)
     VALUES ($1, $2::jsonb, $3, $4)
     ON CONFLICT (setting_key) DO UPDATE SET
       setting_value = EXCLUDED.setting_value,
       is_secret = EXCLUDED.is_secret,
       updated_by = EXCLUDED.updated_by`,
    [key, JSON.stringify(value), isSecret, actorId || null]
  );
}

export async function getCompanyInfo(): Promise<CompanyInfo> {
  const saved = await getSetting<Partial<CompanyInfo>>('company_info');
  return {
    brand_name: saved?.brand_name || 'Raghushree',
    legal_name: saved?.legal_name || 'Raghushree Plast Products Pvt. Ltd.',
    positioning: saved?.positioning || 'Manufacturer, Exporter & Supplier of Plastic Raw Materials',
    street_address: saved?.street_address || '78/205, Latouche Road, General Ganj',
    city: saved?.city || 'Kanpur',
    postal_code: saved?.postal_code || '208001',
    state: saved?.state || 'Uttar Pradesh',
    country: saved?.country || 'India',
    country_code: saved?.country_code || 'IN',
    full_address:
      saved?.full_address || '78/205, Latouche Road, General Ganj, Kanpur-208001, U.P., India',
    email: saved?.email || 'marketing@raghushree.com',
    phone: saved?.phone || '+91-830-3715-071',
    whatsapp: saved?.whatsapp || '+91-830-3715-071',
    socials: saved?.socials || {},
  };
}

export async function getCatalogConfig(): Promise<{ catalog_v2_enabled: boolean }> {
  const saved = await getSetting<{ catalog_v2_enabled?: boolean }>('catalog_config');
  return {
    catalog_v2_enabled: saved?.catalog_v2_enabled !== false,
  };
}

export async function getPaymentShippingConfig(): Promise<PaymentShippingConfig> {
  const env = getEnv();
  const saved = await getSetting<Partial<PaymentShippingConfig>>('payment_shipping_config');
  return {
    mode: saved?.mode === 'live' ? 'live' : saved?.mode === 'test' ? 'test' : env.razorpayMode,
    pickup_pincode: saved?.pickup_pincode || env.shiprocketPickupPincode || '208001',
    checkout_enabled: Boolean(saved?.checkout_enabled),
    allow_live: Boolean(saved?.allow_live),
    allow_tax_live: Boolean(saved?.allow_tax_live),
    razorpay_key_id: saved?.razorpay_key_id || env.razorpayKeyId || '',
    razorpay_key_secret: saved?.razorpay_key_secret || env.razorpayKeySecret || '',
    razorpay_webhook_secret: saved?.razorpay_webhook_secret || env.razorpayWebhookSecret || '',
    shiprocket_email: saved?.shiprocket_email || env.shiprocketEmail || '',
    shiprocket_password: saved?.shiprocket_password || env.shiprocketPassword || '',
  };
}

/** Redacted view for Admin UI — never returns stored secret values! */
export async function getPaymentShippingStatusForAdmin() {
  const cfg = await getPaymentShippingConfig();
  return {
    mode: cfg.mode,
    pickup_pincode: cfg.pickup_pincode,
    checkout_enabled: cfg.checkout_enabled,
    allow_live: cfg.allow_live,
    allow_tax_live: cfg.allow_tax_live,
    has_razorpay_key_id: Boolean(cfg.razorpay_key_id),
    has_razorpay_key_secret: Boolean(cfg.razorpay_key_secret),
    has_razorpay_webhook_secret: Boolean(cfg.razorpay_webhook_secret),
    has_shiprocket_email: Boolean(cfg.shiprocket_email),
    has_shiprocket_password: Boolean(cfg.shiprocket_password),
    checkout_ready: isCheckoutReadyFromConfig(cfg),
  };
}

export function isCheckoutReadyFromConfig(cfg: PaymentShippingConfig): boolean {
  const prefix = cfg.mode === 'live' ? 'rzp_live_' : 'rzp_test_';
  const modeAllowed =
    cfg.mode === 'test' || (cfg.mode === 'live' && cfg.allow_live && cfg.allow_tax_live);

  return (
    cfg.checkout_enabled &&
    modeAllowed &&
    Boolean(cfg.razorpay_key_id) &&
    cfg.razorpay_key_id.startsWith(prefix) &&
    Boolean(cfg.razorpay_key_secret) &&
    Boolean(cfg.razorpay_webhook_secret) &&
    Boolean(cfg.shiprocket_email) &&
    Boolean(cfg.shiprocket_password)
  );
}

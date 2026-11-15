-- ============================================================================
-- Migration 001: Raghushree Independent PostgreSQL Schema (Supabase-Ready)
-- ============================================================================

CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Shared updated_at trigger function
CREATE OR REPLACE FUNCTION public.set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ----------------------------------------------------------------------------
-- 1. Roles & User Profiles (RBAC)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.roles (
  slug TEXT PRIMARY KEY CHECK (slug IN ('administrator', 'manager', 'staff', 'customer')),
  name TEXT NOT NULL,
  description TEXT NOT NULL DEFAULT '',
  permissions JSONB NOT NULL DEFAULT '[]'::jsonb,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.user_profiles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  auth_user_id UUID UNIQUE,
  email TEXT NOT NULL UNIQUE,
  full_name TEXT NOT NULL,
  role_slug TEXT NOT NULL REFERENCES public.roles(slug) ON UPDATE CASCADE,
  custom_permissions JSONB NOT NULL DEFAULT '[]'::jsonb,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  password_hash TEXT,
  reset_token_hash TEXT,
  reset_token_expires_at TIMESTAMPTZ,
  last_login_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TRIGGER trg_user_profiles_updated_at
  BEFORE UPDATE ON public.user_profiles
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

-- ----------------------------------------------------------------------------
-- 2. Categories, Taxonomies & Media Assets
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.product_categories (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  slug TEXT NOT NULL UNIQUE,
  title TEXT NOT NULL,
  category_type TEXT NOT NULL DEFAULT 'polymer' CHECK (category_type IN ('polymer', 'pipe')),
  description TEXT NOT NULL DEFAULT '',
  sort_order INTEGER NOT NULL DEFAULT 0,
  is_catalog_v2 BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TRIGGER trg_product_categories_updated_at
  BEFORE UPDATE ON public.product_categories
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TABLE IF NOT EXISTS public.taxonomy_terms (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  taxonomy TEXT NOT NULL CHECK (taxonomy IN ('material_grade', 'material_form', 'manufacturing_application')),
  slug TEXT NOT NULL,
  name TEXT NOT NULL,
  description TEXT NOT NULL DEFAULT '',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (taxonomy, slug)
);

CREATE TABLE IF NOT EXISTS public.media_assets (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  storage_bucket TEXT NOT NULL DEFAULT 'product-media',
  storage_path TEXT NOT NULL,
  public_url TEXT NOT NULL,
  file_name TEXT NOT NULL,
  mime_type TEXT NOT NULL,
  file_size_bytes BIGINT NOT NULL CHECK (file_size_bytes > 0),
  width INTEGER,
  height INTEGER,
  alt_text TEXT NOT NULL DEFAULT '',
  legacy_wp_attachment_id BIGINT,
  uploaded_by UUID REFERENCES public.user_profiles(id) ON DELETE SET NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ----------------------------------------------------------------------------
-- 3. Products, Gallery, Structured Sections & Product Master
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.products (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  slug TEXT NOT NULL UNIQUE,
  title TEXT NOT NULL,
  polymer_code TEXT NOT NULL DEFAULT '',
  primary_category_id UUID REFERENCES public.product_categories(id) ON DELETE SET NULL,
  image_slot TEXT NOT NULL DEFAULT 'polymer_hdpe',
  featured_media_id UUID REFERENCES public.media_assets(id) ON DELETE SET NULL,
  short_desc TEXT NOT NULL DEFAULT '',
  content_html TEXT NOT NULL DEFAULT '',
  highlights JSONB NOT NULL DEFAULT '[]'::jsonb,
  grades_notes TEXT NOT NULL DEFAULT '',
  grades_rows JSONB NOT NULL DEFAULT '[]'::jsonb,
  industries_rows JSONB NOT NULL DEFAULT '[]'::jsonb,
  processing_rows JSONB NOT NULL DEFAULT '[]'::jsonb,
  datasheet_url TEXT NOT NULL DEFAULT '',
  packaging TEXT NOT NULL DEFAULT '',
  pack_size TEXT NOT NULL DEFAULT '',
  moq TEXT NOT NULL DEFAULT '',
  availability TEXT NOT NULL DEFAULT '',
  status TEXT NOT NULL DEFAULT 'published' CHECK (status IN ('draft', 'published', 'archived')),
  is_legacy_catalog BOOLEAN NOT NULL DEFAULT FALSE,
  catalog_key TEXT UNIQUE,
  menu_order INTEGER NOT NULL DEFAULT 0,
  legacy_wp_post_id BIGINT,
  -- SEO & Social Metadata
  seo_title TEXT NOT NULL DEFAULT '',
  seo_description TEXT NOT NULL DEFAULT '',
  seo_canonical TEXT NOT NULL DEFAULT '',
  seo_robots TEXT NOT NULL DEFAULT 'index,follow',
  og_title TEXT NOT NULL DEFAULT '',
  og_description TEXT NOT NULL DEFAULT '',
  og_image_url TEXT NOT NULL DEFAULT '',
  twitter_title TEXT NOT NULL DEFAULT '',
  twitter_description TEXT NOT NULL DEFAULT '',
  twitter_image_url TEXT NOT NULL DEFAULT '',
  created_by UUID REFERENCES public.user_profiles(id) ON DELETE SET NULL,
  updated_by UUID REFERENCES public.user_profiles(id) ON DELETE SET NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_products_status ON public.products(status);
CREATE INDEX IF NOT EXISTS idx_products_category ON public.products(primary_category_id);
CREATE INDEX IF NOT EXISTS idx_products_legacy ON public.products(is_legacy_catalog);

CREATE TRIGGER trg_products_updated_at
  BEFORE UPDATE ON public.products
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TABLE IF NOT EXISTS public.product_category_links (
  product_id UUID NOT NULL REFERENCES public.products(id) ON DELETE CASCADE,
  category_id UUID NOT NULL REFERENCES public.product_categories(id) ON DELETE CASCADE,
  PRIMARY KEY (product_id, category_id)
);

CREATE TABLE IF NOT EXISTS public.product_taxonomy_links (
  product_id UUID NOT NULL REFERENCES public.products(id) ON DELETE CASCADE,
  term_id UUID NOT NULL REFERENCES public.taxonomy_terms(id) ON DELETE CASCADE,
  PRIMARY KEY (product_id, term_id)
);

CREATE TABLE IF NOT EXISTS public.product_gallery_items (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  product_id UUID NOT NULL REFERENCES public.products(id) ON DELETE CASCADE,
  media_id UUID NOT NULL REFERENCES public.media_assets(id) ON DELETE CASCADE,
  sort_order INTEGER NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (product_id, media_id)
);

CREATE TABLE IF NOT EXISTS public.product_section_intros (
  product_id UUID NOT NULL REFERENCES public.products(id) ON DELETE CASCADE,
  section_key TEXT NOT NULL CHECK (section_key IN ('features', 'applications', 'specifications', 'faqs')),
  intro_text TEXT NOT NULL DEFAULT '',
  PRIMARY KEY (product_id, section_key)
);

CREATE TABLE IF NOT EXISTS public.product_sections (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  product_id UUID NOT NULL REFERENCES public.products(id) ON DELETE CASCADE,
  section_key TEXT NOT NULL CHECK (section_key IN ('features', 'applications', 'specifications', 'faqs')),
  sort_order INTEGER NOT NULL DEFAULT 0,
  col_1 TEXT NOT NULL,
  col_2 TEXT NOT NULL DEFAULT '',
  col_3 TEXT NOT NULL DEFAULT ''
);

CREATE INDEX IF NOT EXISTS idx_product_sections_prod ON public.product_sections(product_id, section_key, sort_order);

-- Product Master Configuration (Pricing, GST, HSN, Pipe Packet Pricing)
CREATE TABLE IF NOT EXISTS public.product_master_configs (
  product_id UUID PRIMARY KEY REFERENCES public.products(id) ON DELETE CASCADE,
  product_type TEXT NOT NULL DEFAULT 'polymer' CHECK (product_type IN ('polymer', 'pipe')),
  gst_bps INTEGER NOT NULL DEFAULT 1800 CHECK (gst_bps >= 0 AND gst_bps <= 10000),
  hsn TEXT NOT NULL DEFAULT '' CHECK (hsn = '' OR hsn ~ '^[0-9]{4,8}$'),
  packet_label TEXT NOT NULL DEFAULT '',
  packet_price_paise BIGINT NOT NULL DEFAULT 0 CHECK (packet_price_paise >= 0 AND packet_price_paise <= 1000000000),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TRIGGER trg_product_master_configs_updated_at
  BEFORE UPDATE ON public.product_master_configs
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

-- Product Variations (Polymer 5-25kg, Sample, Legacy 30kg hidden, Pipe packet_N rows)
CREATE TABLE IF NOT EXISTS public.product_variations (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  product_id UUID NOT NULL REFERENCES public.products(id) ON DELETE CASCADE,
  option_key TEXT NOT NULL,
  variation_kind TEXT NOT NULL CHECK (variation_kind IN ('polymer_pack', 'sample', 'pipe_packet', 'legacy_pack')),
  label TEXT NOT NULL,
  quantity INTEGER NOT NULL DEFAULT 1 CHECK (quantity >= 1 AND quantity <= 999),
  price_paise BIGINT NOT NULL DEFAULT 0 CHECK (price_paise >= 0 AND price_paise <= 2000000000),
  enabled BOOLEAN NOT NULL DEFAULT FALSE,
  weight_kg NUMERIC(10,3) CHECK (weight_kg IS NULL OR (weight_kg > 0 AND weight_kg <= 99999)),
  length_cm NUMERIC(10,3) CHECK (length_cm IS NULL OR (length_cm > 0 AND length_cm <= 99999)),
  width_cm NUMERIC(10,3) CHECK (width_cm IS NULL OR (width_cm > 0 AND width_cm <= 99999)),
  height_cm NUMERIC(10,3) CHECK (height_cm IS NULL OR (height_cm > 0 AND height_cm <= 99999)),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (product_id, option_key)
);

CREATE TRIGGER trg_product_variations_updated_at
  BEFORE UPDATE ON public.product_variations
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

-- ----------------------------------------------------------------------------
-- 4. Shipping Quotes (15-min immutable snapshots), Customers, Orders & Payments
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.shipping_quotes (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  token_hash TEXT NOT NULL UNIQUE,
  product_id UUID NOT NULL REFERENCES public.products(id) ON DELETE RESTRICT,
  option_key TEXT NOT NULL,
  pickup_pincode TEXT NOT NULL CHECK (pickup_pincode ~ '^[1-9][0-9]{5}$'),
  delivery_pincode TEXT NOT NULL CHECK (delivery_pincode ~ '^[1-9][0-9]{5}$'),
  mode TEXT NOT NULL CHECK (mode IN ('test', 'live')),
  courier_id TEXT NOT NULL,
  courier_name TEXT NOT NULL DEFAULT '',
  product_paise BIGINT NOT NULL CHECK (product_paise >= 0),
  product_gst_paise BIGINT NOT NULL CHECK (product_gst_paise >= 0),
  product_gst_bps INTEGER NOT NULL CHECK (product_gst_bps >= 0 AND product_gst_bps <= 10000),
  rate_paise BIGINT NOT NULL CHECK (rate_paise >= 0),
  shipping_gst_paise BIGINT NOT NULL CHECK (shipping_gst_paise >= 0),
  shipping_gst_bps INTEGER NOT NULL DEFAULT 1800,
  total_paise BIGINT NOT NULL CHECK (total_paise >= 0),
  hsn TEXT NOT NULL DEFAULT '',
  pack_snapshot JSONB NOT NULL,
  quote_snapshot JSONB NOT NULL,
  claimed_at TIMESTAMPTZ,
  expires_at TIMESTAMPTZ NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_shipping_quotes_expires ON public.shipping_quotes(expires_at);

CREATE TABLE IF NOT EXISTS public.customers (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT NOT NULL,
  address TEXT NOT NULL,
  address_2 TEXT NOT NULL DEFAULT '',
  city TEXT NOT NULL,
  state TEXT NOT NULL,
  pincode TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_customers_email ON public.customers(email);

CREATE TRIGGER trg_customers_updated_at
  BEFORE UPDATE ON public.customers
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE SEQUENCE IF NOT EXISTS public.order_number_seq START 1001;

CREATE TABLE IF NOT EXISTS public.orders (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  order_number BIGINT NOT NULL UNIQUE DEFAULT nextval('public.order_number_seq'),
  customer_id UUID NOT NULL REFERENCES public.customers(id) ON DELETE RESTRICT,
  quote_id UUID REFERENCES public.shipping_quotes(id) ON DELETE RESTRICT,
  product_id UUID REFERENCES public.products(id) ON DELETE RESTRICT,
  option_key TEXT NOT NULL,
  mode TEXT NOT NULL CHECK (mode IN ('test', 'live')),
  payment_status TEXT NOT NULL DEFAULT 'pending' CHECK (payment_status IN ('pending', 'paid', 'order_creation_failed', 'review_required')),
  fulfilment_status TEXT NOT NULL DEFAULT 'awaiting' CHECK (fulfilment_status IN ('awaiting', 'fulfilled')),
  manual_courier TEXT NOT NULL DEFAULT '',
  manual_tracking TEXT NOT NULL DEFAULT '',
  razorpay_order_id TEXT UNIQUE,
  razorpay_payment_id TEXT,
  receipt_hash TEXT NOT NULL,
  product_paise BIGINT NOT NULL CHECK (product_paise >= 0),
  product_gst_paise BIGINT NOT NULL CHECK (product_gst_paise >= 0),
  product_gst_bps INTEGER NOT NULL,
  shipping_paise BIGINT NOT NULL CHECK (shipping_paise >= 0),
  shipping_gst_paise BIGINT NOT NULL CHECK (shipping_gst_paise >= 0),
  shipping_gst_bps INTEGER NOT NULL DEFAULT 1800,
  total_paise BIGINT NOT NULL CHECK (total_paise >= 100),
  hsn TEXT NOT NULL DEFAULT '',
  courier_id TEXT NOT NULL DEFAULT '',
  courier_name TEXT NOT NULL DEFAULT '',
  pickup_pincode TEXT NOT NULL DEFAULT '208001',
  order_snapshot JSONB NOT NULL,
  legacy_wp_post_id BIGINT,
  legacy_fulfilment TEXT,
  legacy_shiprocket_order TEXT,
  legacy_shiprocket_shipment TEXT,
  paid_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_orders_payment_status ON public.orders(payment_status);
CREATE INDEX IF NOT EXISTS idx_orders_fulfilment_status ON public.orders(fulfilment_status);
CREATE INDEX IF NOT EXISTS idx_orders_created_at ON public.orders(created_at DESC);

CREATE TRIGGER trg_orders_updated_at
  BEFORE UPDATE ON public.orders
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TABLE IF NOT EXISTS public.order_items (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  order_id UUID NOT NULL REFERENCES public.orders(id) ON DELETE CASCADE,
  product_id UUID REFERENCES public.products(id) ON DELETE RESTRICT,
  product_title TEXT NOT NULL,
  option_key TEXT NOT NULL,
  option_label TEXT NOT NULL,
  quantity INTEGER NOT NULL DEFAULT 1,
  unit_price_paise BIGINT NOT NULL,
  total_price_paise BIGINT NOT NULL,
  gst_bps INTEGER NOT NULL,
  gst_paise BIGINT NOT NULL,
  hsn TEXT NOT NULL DEFAULT '',
  packed_weight_kg NUMERIC(10,3),
  length_cm NUMERIC(10,3),
  width_cm NUMERIC(10,3),
  height_cm NUMERIC(10,3),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON public.order_items(order_id);

CREATE TABLE IF NOT EXISTS public.payment_transactions (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  order_id UUID NOT NULL REFERENCES public.orders(id) ON DELETE CASCADE,
  provider TEXT NOT NULL DEFAULT 'razorpay',
  razorpay_order_id TEXT NOT NULL,
  razorpay_payment_id TEXT,
  amount_paise BIGINT NOT NULL,
  currency TEXT NOT NULL DEFAULT 'INR',
  status TEXT NOT NULL CHECK (status IN ('created', 'pending', 'captured', 'failed', 'mismatch')),
  raw_response JSONB NOT NULL DEFAULT '{}'::jsonb,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TRIGGER trg_payment_transactions_updated_at
  BEFORE UPDATE ON public.payment_transactions
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TABLE IF NOT EXISTS public.payment_events (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  order_id UUID REFERENCES public.orders(id) ON DELETE SET NULL,
  event_key TEXT NOT NULL UNIQUE,
  event_type TEXT NOT NULL,
  source TEXT NOT NULL CHECK (source IN ('checkout_verify', 'webhook', 'admin')),
  signature_verified BOOLEAN NOT NULL DEFAULT FALSE,
  payload JSONB NOT NULL DEFAULT '{}'::jsonb,
  processing_status TEXT NOT NULL DEFAULT 'processed',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ----------------------------------------------------------------------------
-- 5. Enquiries (11-field RFQ), Business Settings & Audit Logs
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.enquiries (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  company TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT NOT NULL,
  country TEXT NOT NULL,
  enquiry_type TEXT NOT NULL,
  material TEXT NOT NULL,
  grade TEXT NOT NULL DEFAULT '',
  quantity TEXT NOT NULL DEFAULT '',
  application TEXT NOT NULL DEFAULT '',
  message TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'new' CHECK (status IN ('new', 'in_review', 'quoted', 'closed', 'spam')),
  internal_notes TEXT NOT NULL DEFAULT '',
  assigned_to UUID REFERENCES public.user_profiles(id) ON DELETE SET NULL,
  ip_hash TEXT NOT NULL DEFAULT '',
  legacy_wp_post_id BIGINT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_enquiries_status ON public.enquiries(status);
CREATE INDEX IF NOT EXISTS idx_enquiries_created_at ON public.enquiries(created_at DESC);

CREATE TRIGGER trg_enquiries_updated_at
  BEFORE UPDATE ON public.enquiries
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TABLE IF NOT EXISTS public.business_settings (
  setting_key TEXT PRIMARY KEY,
  setting_value JSONB NOT NULL DEFAULT '{}'::jsonb,
  is_secret BOOLEAN NOT NULL DEFAULT FALSE,
  updated_by UUID REFERENCES public.user_profiles(id) ON DELETE SET NULL,
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TRIGGER trg_business_settings_updated_at
  BEFORE UPDATE ON public.business_settings
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TABLE IF NOT EXISTS public.audit_logs (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  actor_id UUID REFERENCES public.user_profiles(id) ON DELETE SET NULL,
  actor_email TEXT NOT NULL DEFAULT 'system',
  action TEXT NOT NULL,
  resource_type TEXT NOT NULL,
  resource_id TEXT NOT NULL DEFAULT '',
  metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
  ip_address TEXT NOT NULL DEFAULT '',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON public.audit_logs(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_audit_logs_resource ON public.audit_logs(resource_type, resource_id);

-- Rate Limit Bucket Table (PostgreSQL-backed, works across restarts & instances)
CREATE TABLE IF NOT EXISTS public.rate_limits (
  bucket_key TEXT PRIMARY KEY,
  hit_count INTEGER NOT NULL DEFAULT 1,
  reset_at TIMESTAMPTZ NOT NULL
);

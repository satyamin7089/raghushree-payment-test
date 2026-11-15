-- ============================================================================
-- Migration 002: Supabase Row Level Security (RLS) Policies & Storage Policies
-- ============================================================================
-- Note: The Node.js backend uses the service_role key (bypassing RLS after
-- enforcing application-level RBAC + Zod validation). These policies protect
-- direct Supabase PostgREST / Storage access so anon/authenticated clients
-- only ever see strictly permitted public catalog/media rows.

-- Helper to look up current authenticated user's active role in user_profiles
CREATE OR REPLACE FUNCTION public.current_app_role()
RETURNS TEXT AS $$
  SELECT role_slug
  FROM public.user_profiles
  WHERE auth_user_id = auth.uid()
    AND is_active = TRUE
  LIMIT 1;
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Enable RLS across all tables
ALTER TABLE public.roles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.product_categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.taxonomy_terms ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.media_assets ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.products ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.product_category_links ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.product_taxonomy_links ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.product_gallery_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.product_section_intros ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.product_sections ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.product_master_configs ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.product_variations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.shipping_quotes ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.customers ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.orders ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.order_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.payment_transactions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.payment_events ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.enquiries ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.business_settings ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.audit_logs ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.rate_limits ENABLE ROW LEVEL SECURITY;

-- Public Read Policies (Catalog, Published Products, Categories, Taxonomies, Media, Enabled Variations)
CREATE POLICY "Public read categories" ON public.product_categories
  FOR SELECT USING (TRUE);

CREATE POLICY "Public read taxonomy terms" ON public.taxonomy_terms
  FOR SELECT USING (TRUE);

CREATE POLICY "Public read media assets" ON public.media_assets
  FOR SELECT USING (TRUE);

CREATE POLICY "Public read published products" ON public.products
  FOR SELECT USING (
    status = 'published'
    OR public.current_app_role() IN ('administrator', 'manager', 'staff')
  );

CREATE POLICY "Public read product category links" ON public.product_category_links
  FOR SELECT USING (TRUE);

CREATE POLICY "Public read product taxonomy links" ON public.product_taxonomy_links
  FOR SELECT USING (TRUE);

CREATE POLICY "Public read product gallery items" ON public.product_gallery_items
  FOR SELECT USING (TRUE);

CREATE POLICY "Public read product section intros" ON public.product_section_intros
  FOR SELECT USING (TRUE);

CREATE POLICY "Public read product sections" ON public.product_sections
  FOR SELECT USING (TRUE);

CREATE POLICY "Public read product master configs" ON public.product_master_configs
  FOR SELECT USING (TRUE);

CREATE POLICY "Public read enabled variations" ON public.product_variations
  FOR SELECT USING (
    (enabled = TRUE AND variation_kind <> 'legacy_pack')
    OR public.current_app_role() IN ('administrator', 'manager', 'staff')
  );

-- Public read of non-secret business settings only (company contact info, never credentials)
CREATE POLICY "Public read non-secret business settings" ON public.business_settings
  FOR SELECT USING (
    is_secret = FALSE
    OR public.current_app_role() = 'administrator'
  );

-- Staff/Manager/Admin read & write policies for products & taxonomies
CREATE POLICY "Admin staff manage products" ON public.products
  FOR ALL USING (public.current_app_role() IN ('administrator', 'manager', 'staff'))
  WITH CHECK (public.current_app_role() IN ('administrator', 'manager', 'staff'));

CREATE POLICY "Admin staff manage categories" ON public.product_categories
  FOR ALL USING (public.current_app_role() IN ('administrator', 'manager', 'staff'))
  WITH CHECK (public.current_app_role() IN ('administrator', 'manager', 'staff'));

CREATE POLICY "Admin staff manage taxonomy terms" ON public.taxonomy_terms
  FOR ALL USING (public.current_app_role() IN ('administrator', 'manager', 'staff'))
  WITH CHECK (public.current_app_role() IN ('administrator', 'manager', 'staff'));

-- Users can read their own profile; only administrators manage user profiles
CREATE POLICY "Read own user profile" ON public.user_profiles
  FOR SELECT USING (auth_user_id = auth.uid() OR public.current_app_role() = 'administrator');

CREATE POLICY "Admin manage user profiles" ON public.user_profiles
  FOR ALL USING (public.current_app_role() = 'administrator')
  WITH CHECK (public.current_app_role() = 'administrator');

-- Enquiries: only admin/manager can read/manage (never staff or anon)
CREATE POLICY "Admin manager access enquiries" ON public.enquiries
  FOR ALL USING (public.current_app_role() IN ('administrator', 'manager'))
  WITH CHECK (public.current_app_role() IN ('administrator', 'manager'));

-- Orders & Order Items: admin, manager, staff can read/update delivery; never anon
CREATE POLICY "Authorized staff read orders" ON public.orders
  FOR SELECT USING (public.current_app_role() IN ('administrator', 'manager', 'staff'));

CREATE POLICY "Authorized staff read order items" ON public.order_items
  FOR SELECT USING (public.current_app_role() IN ('administrator', 'manager', 'staff'));

-- Sensitive backend-only tables (customers, shipping_quotes, payment_transactions, payment_events, audit_logs, rate_limits)
-- have NO public SELECT/INSERT/UPDATE/DELETE policies; only Node.js backend service_role accesses them.
CREATE POLICY "Admin read audit logs" ON public.audit_logs
  FOR SELECT USING (public.current_app_role() = 'administrator');

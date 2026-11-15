-- ============================================================================
-- Migration 003: Seed Roles, Taxonomies, Catalog V2 Categories & Default Settings
-- ============================================================================

INSERT INTO public.roles (slug, name, description, permissions) VALUES
  ('administrator', 'Administrator', 'Full platform access including payment/shipping credentials, user roles, settings, catalog, orders, and enquiries.', '["*"]'::jsonb),
  ('manager', 'Manager', 'Manage products, Product Master, categories, orders, fulfilment, and customer enquiries.', '["products:read","products:write","categories:write","orders:read","orders:fulfil","enquiries:read","enquiries:write","media:upload"]'::jsonb),
  ('staff', 'Staff (Product Manager)', 'Scoped workspace for Products, Product Master, product categories/taxonomies, media uploads, and Order fulfilment.', '["products:read","products:write","categories:write","orders:read","orders:fulfil","media:upload"]'::jsonb),
  ('customer', 'Customer', 'Public buyer role with access only to own account context.', '["orders:read_own"]'::jsonb)
ON CONFLICT (slug) DO UPDATE SET
  name = EXCLUDED.name,
  description = EXCLUDED.description,
  permissions = EXCLUDED.permissions;

-- Seed default Company Info (non-secret) & Payment/Shipping structure (no secrets hardcoded)
INSERT INTO public.business_settings (setting_key, setting_value, is_secret) VALUES
  ('company_info', '{
    "brand_name": "Raghushree",
    "legal_name": "Raghushree Plast Products Pvt. Ltd.",
    "positioning": "Manufacturer, Exporter & Supplier of Plastic Raw Materials",
    "street_address": "78/205, Latouche Road, General Ganj",
    "city": "Kanpur",
    "postal_code": "208001",
    "state": "Uttar Pradesh",
    "country": "India",
    "country_code": "IN",
    "full_address": "78/205, Latouche Road, General Ganj, Kanpur-208001, U.P., India",
    "email": "marketing@raghushree.com",
    "phone": "+91-830-3715-071",
    "whatsapp": "+91-830-3715-071",
    "socials": {}
  }'::jsonb, FALSE),
  ('catalog_config', '{"catalog_v2_enabled": true}'::jsonb, FALSE),
  ('payment_shipping_config', '{
    "mode": "test",
    "pickup_pincode": "208001",
    "checkout_enabled": false,
    "allow_live": false,
    "allow_tax_live": false,
    "razorpay_key_id": "",
    "razorpay_key_secret": "",
    "razorpay_webhook_secret": "",
    "shiprocket_email": "",
    "shiprocket_password": ""
  }'::jsonb, TRUE)
ON CONFLICT (setting_key) DO NOTHING;

-- Seed Catalog V2 Categories (9 categories matching inc/catalog-v2.php)
INSERT INTO public.product_categories (slug, title, category_type, sort_order, is_catalog_v2) VALUES
  ('pvc-granules', 'PVC Granules', 'polymer', 1, TRUE),
  ('pvc-resin', 'PVC Resin', 'polymer', 2, TRUE),
  ('pp-granules', 'PP Granules', 'polymer', 3, TRUE),
  ('ldpe-granules', 'LDPE Granules', 'polymer', 4, TRUE),
  ('hdpe-granules', 'HDPE Granules', 'polymer', 5, TRUE),
  ('industrial-hoses', 'Industrial Hoses', 'pipe', 6, TRUE),
  ('pvc-suction-pipes', 'PVC Suction Pipes', 'pipe', 7, TRUE),
  ('pvc-tubing', 'PVC Tubing', 'pipe', 8, TRUE),
  ('lay-flat-pipes', 'Lay Flat Pipes', 'pipe', 9, TRUE)
ON CONFLICT (slug) DO NOTHING;

-- Seed Core Taxonomy Terms
INSERT INTO public.taxonomy_terms (taxonomy, slug, name) VALUES
  ('material_grade', 'reprocessed-grades', 'Reprocessed Grades'),
  ('material_grade', 'recycled-grades', 'Recycled Grades'),
  ('material_form', 'plastic-granules', 'Plastic Granules'),
  ('material_form', 'plastic-powder', 'Plastic Powder'),
  ('manufacturing_application', 'injection-moulding', 'Injection Moulding'),
  ('manufacturing_application', 'blow-moulding', 'Blow Moulding'),
  ('manufacturing_application', 'extrusion', 'Extrusion'),
  ('manufacturing_application', 'film-and-packaging', 'Film and Packaging'),
  ('manufacturing_application', 'pipes-and-fittings', 'Pipes and Fittings'),
  ('manufacturing_application', 'electrical-and-electronics', 'Electrical and Electronics'),
  ('manufacturing_application', 'household-and-consumer-products', 'Household and Consumer Products')
ON CONFLICT (taxonomy, slug) DO NOTHING;

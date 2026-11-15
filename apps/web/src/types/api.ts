export interface CategoryDto {
  id: string;
  slug: string;
  title: string;
  category_type: 'polymer' | 'pipe';
  description: string;
  sort_order: number;
  is_catalog_v2: boolean;
  product_count?: number;
}

export interface CompanyInfoDto {
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

export interface SiteConfigDto {
  company: CompanyInfoDto;
  catalog_v2_enabled: boolean;
  checkout_ready: boolean;
  payment_mode: 'test' | 'live';
  categories: CategoryDto[];
}

export interface ProductVariationDto {
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

export interface ProductDto {
  id: string;
  slug: string;
  title: string;
  polymer_code: string;
  primary_category: CategoryDto | null;
  categories: CategoryDto[];
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
  master_config: {
    product_type: 'polymer' | 'pipe';
    gst_bps: number;
    hsn: string;
    packet_label: string;
    packet_price_paise: number;
  };
  variations: Record<string, ProductVariationDto>;
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

export interface ShippingQuoteDto {
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

export interface AuthUserDto {
  id: string;
  email: string;
  fullName: string;
  role: 'administrator' | 'manager' | 'staff' | 'customer';
  permissions: string[];
}

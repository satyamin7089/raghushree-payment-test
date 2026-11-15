import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AppRoutes } from '../src/App';
import { AuthProvider } from '../src/components/AuthContext';
import { ProductCheckoutSection, ProductPurchaseHero } from '../src/components/ProductPurchasePanel';
import { SiteProvider } from '../src/components/SiteContext';
import type { ProductDto, ShippingQuoteDto, SiteConfigDto } from '../src/types/api';

const mockConfig: SiteConfigDto = {
  company: {
    brand_name: 'Raghushree',
    legal_name: 'Raghushree Plast Products Pvt. Ltd.',
    positioning: 'Manufacturer, Exporter & Supplier of Plastic Raw Materials',
    street_address: '78/205, Latouche Road, General Ganj',
    city: 'Kanpur',
    postal_code: '208001',
    state: 'U.P.',
    country: 'India',
    country_code: 'IN',
    full_address: '78/205, Latouche Road, General Ganj, Kanpur-208001, U.P., India',
    phone: '+91-830-3715-071',
    email: 'marketing@raghushree.com',
    whatsapp: '+91-830-3715-071',
    socials: {},
  },
  catalog_v2_enabled: true,
  payment_mode: 'test',
  checkout_ready: true,
  categories: [
    {
      id: 'cat-1',
      slug: 'pvc-granules',
      title: 'PVC Granules',
      description: 'Compound PVC granules',
      category_type: 'polymer',
      sort_order: 1,
      is_catalog_v2: true,
    },
    {
      id: 'cat-2',
      slug: 'pvc-suction-pipes',
      title: 'PVC Suction Pipes',
      description: 'Spiral suction pipes',
      category_type: 'pipe',
      sort_order: 7,
      is_catalog_v2: true,
    },
  ],
};

const mockPolymerProduct: ProductDto = {
  id: 'prod-1',
  slug: 'pvc-pipe-grade-granules',
  title: 'PVC Pipe Grade Granules',
  polymer_code: 'PVC-P',
  short_desc: 'Rigid and flexible PVC granules for extrusion.',
  content_html: '<p>Consistent extrusion compound.</p>',
  is_legacy_catalog: false,
  catalog_key: 'pvc-granules:pvc-pipe-grade-granules',
  menu_order: 1,
  status: 'published',
  image_slot: 'polymer_pvc',
  featured_image_url: null,
  featured_image_alt: '',
  gallery: [],
  primary_category: mockConfig.categories[0],
  categories: [mockConfig.categories[0]],
  grades_terms: [],
  forms_terms: [],
  applications_terms: [],
  highlights: ['Consistent melt flow', 'Clean granulation'],
  grades_notes: '',
  grades_rows: [['RG-PVC-67', 'Granules', 'Rigid pipe']],
  industries_rows: ['Agriculture'],
  processing_rows: [['Extrusion', '165-185C']],
  section_intros: { features: '', applications: '', specifications: '', faqs: '' },
  sections: {
    features: [['Uniform Pellets', 'Smooth screw feeding']],
    applications: [['Pressure Pipes', 'Water distribution']],
    specifications: [['K-Value', '67', 'ISO 1628']],
    faqs: [['What pack sizes are offered?', '5kg to 25kg online packs and bulk MT loads.']],
  },
  packaging: '25 kg laminated bags',
  pack_size: '5-25 kg',
  moq: '5 kg online / 1 MT bulk',
  availability: 'In stock',
  datasheet_url: '',
  seo: {
    seo_title: '',
    seo_description: '',
    seo_canonical: '',
    seo_robots: 'index,follow',
    og_title: '',
    og_description: '',
    og_image_url: '',
    twitter_title: '',
    twitter_description: '',
    twitter_image_url: '',
  },
  master_config: {
    product_type: 'polymer',
    gst_bps: 1800,
    hsn: '3904',
    packet_label: 'One quantity is one packet.',
    packet_price_paise: 0,
  },
  variations: {
    '5kg': {
      option_key: '5kg',
      variation_kind: 'polymer_pack',
      label: '5 kg',
      quantity: 1,
      enabled: true,
      price_paise: 75000,
      weight_kg: '5',
      length_cm: '30',
      width_cm: '25',
      height_cm: '10',
      gst_bps: 1800,
      hsn: '3904',
      product_type: 'polymer',
    },
    '10kg': {
      option_key: '10kg',
      variation_kind: 'polymer_pack',
      label: '10 kg',
      quantity: 1,
      enabled: true,
      price_paise: 145000,
      weight_kg: '10',
      length_cm: '30',
      width_cm: '25',
      height_cm: '15',
      gst_bps: 1800,
      hsn: '3904',
      product_type: 'polymer',
    },
    sample: {
      option_key: 'sample',
      variation_kind: 'sample',
      label: 'Sample (0.5 kg)',
      quantity: 1,
      enabled: true,
      price_paise: 0,
      weight_kg: '0.5',
      length_cm: '20',
      width_cm: '15',
      height_cm: '5',
      gst_bps: 1800,
      hsn: '3904',
      product_type: 'polymer',
    },
  },
  created_at: new Date().toISOString(),
  updated_at: new Date().toISOString(),
};

function renderWithProviders(initialPath = '/') {
  return render(
    <MemoryRouter initialEntries={[initialPath]}>
      <AuthProvider>
        <SiteProvider>
          <AppRoutes />
        </SiteProvider>
      </AuthProvider>
    </MemoryRouter>
  );
}

describe('Raghushree React Web App', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
    vi.stubGlobal(
      'fetch',
      vi.fn(async (input: RequestInfo | URL) => {
        const url = String(input);
        if (url.includes('/api/v1/site/config')) {
          return new Response(JSON.stringify({ success: true, data: mockConfig }), { status: 200 });
        }
        if (url.includes('/api/v1/products/pvc-pipe-grade-granules')) {
          return new Response(JSON.stringify({ success: true, data: mockPolymerProduct }), {
            status: 200,
          });
        }
        if (url.includes('/api/v1/products')) {
          return new Response(
            JSON.stringify({
              success: true,
              data: { items: [mockPolymerProduct], total: 1, page: 1, totalPages: 1 },
            }),
            { status: 200 }
          );
        }
        if (url.includes('/api/v1/shipping/quote')) {
          const quote: ShippingQuoteDto = {
            quote_token: 'tok-123',
            product_id: 'prod-1',
            product_title: 'PVC Pipe Grade Granules',
            variation: '5kg',
            option_label: '5 kg',
            pincode: '208001',
            weight_kg: 5,
            dimensions_cm: { length: 30, width: 25, height: 10 },
            product_paise: 75000,
            gst_bps: 1800,
            hsn: '3904',
            product_gst_paise: 13500,
            rate_paise: 12000,
            shipping_gst_paise: 2160,
            total_paise: 102660,
            courier_name: 'Delhivery Surface',
            courier_id: 'delhivery',
            expires_at: new Date(Date.now() + 900000).toISOString(),
          };
          return new Response(JSON.stringify({ success: true, data: quote }), { status: 200 });
        }
        if (url.includes('/api/v1/enquiries')) {
          return new Response(
            JSON.stringify({
              success: true,
              data: { id: 'enq-1', reference_code: 'ENQ-TEST-1', status: 'new' },
            }),
            { status: 201 }
          );
        }
        return new Response(JSON.stringify({ error: 'Not found' }), { status: 404 });
      })
    );
  });

  it('renders homepage hero, polymer trust bar, and featured products', async () => {
    renderWithProviders('/');
    expect(
      await screen.findByText(/Dependable Polymer Raw Materials for Industrial Manufacturing/i)
    ).toBeInTheDocument();
    const items = await screen.findAllByText('PVC Pipe Grade Granules');
    expect(items.length).toBeGreaterThanOrEqual(1);
  });

  it('renders product detail page, computes shipping quote, and opens checkout summary', async () => {
    renderWithProviders('/products/pvc-pipe-grade-granules/');
    expect(
      await screen.findByRole('heading', { level: 1, name: 'PVC Pipe Grade Granules' })
    ).toBeInTheDocument();

    const pinInput = screen.getByLabelText(/Check delivery/i);
    fireEvent.change(pinInput, { target: { value: '208001' } });
    fireEvent.click(screen.getByRole('button', { name: 'Check' }));

    await waitFor(() => {
      expect(screen.getByText(/Estimated total ₹1026.60/i)).toBeInTheDocument();
    });
    expect(screen.getByText(/Complete your order/i)).toBeInTheDocument();
    expect(screen.getByText(/Lowest available courier: Delhivery Surface/i)).toBeInTheDocument();
  });

  it('validates and submits the 11-field B2B RFQ enquiry form', async () => {
    renderWithProviders('/contact/');
    expect(await screen.findByText(/Submit Your Material Specification/i)).toBeInTheDocument();

    fireEvent.change(screen.getByLabelText(/Full Name/i), { target: { value: 'Amit Kumar' } });
    fireEvent.change(screen.getByLabelText(/Company Name/i), {
      target: { value: 'Kanpur Polymers' },
    });
    fireEvent.change(screen.getByLabelText(/Business Email/i), {
      target: { value: 'amit@kanpurpolymers.in' },
    });
    fireEvent.change(screen.getByLabelText(/Phone \/ WhatsApp Number/i), {
      target: { value: '+919876543210' },
    });
    fireEvent.change(screen.getByLabelText(/Enquiry Type/i), {
      target: { value: 'Request a Quotation' },
    });
    fireEvent.change(screen.getByLabelText(/Material \/ Polymer Category/i), {
      target: { value: 'PVC Pipe Grade Granules' },
    });
    fireEvent.change(screen.getByLabelText(/Requirement Details/i), {
      target: { value: 'Need 5 MT quote for rigid pipe extrusion.' },
    });

    fireEvent.click(screen.getByRole('button', { name: /Submit Business Enquiry/i }));

    await waitFor(() => {
      expect(screen.getByText(/ENQ-TEST-1/i)).toBeInTheDocument();
    });
  });

  it('redirects unauthenticated /admin visitors to /admin/login', async () => {
    renderWithProviders('/admin');
    expect(await screen.findByText(/Staff & Admin Sign In/i)).toBeInTheDocument();
  });

  it('updates purchase price dynamically when pack size changes', () => {
    const onQuoteReady = vi.fn();
    const onQuoteInvalidate = vi.fn();
    render(
      <MemoryRouter>
        <AuthProvider>
          <SiteProvider>
            <ProductPurchaseHero
              product={mockPolymerProduct}
              enquireUrl="/contact/"
              onQuoteReady={onQuoteReady}
              onQuoteInvalidate={onQuoteInvalidate}
              locked={false}
            />
            <ProductCheckoutSection quote={null} onCheckoutStarted={vi.fn()} />
          </SiteProvider>
        </AuthProvider>
      </MemoryRouter>
    );
    expect(screen.getByText('₹750.00')).toBeInTheDocument();
    fireEvent.change(screen.getByLabelText(/Choose pack size/i), { target: { value: '10kg' } });
    expect(screen.getByText('₹1450.00')).toBeInTheDocument();
  });
});

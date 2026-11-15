import { Router } from 'express';
import { z } from 'zod';
import { query } from '../db/client.js';
import { AppError } from '../utils/errors.js';
import {
  getProductByIdOrSlug,
  listCategories,
  listProducts,
  listTaxonomyTerms,
} from '../services/catalogService.js';
import { createEnquiry } from '../services/enquiryService.js';
import { createShippingQuote } from '../services/shiprocketService.js';
import {
  handleRazorpayWebhook,
  startCheckout,
  verifyPayment,
} from '../services/razorpayService.js';
import {
  getCatalogConfig,
  getCompanyInfo,
  getPaymentShippingConfig,
  isCheckoutReadyFromConfig,
} from '../services/settingsService.js';
import { getClientIp, rateLimitMiddleware } from '../middleware/rateLimit.js';

export const publicRouter = Router();

publicRouter.get('/health', async (_req, res, next) => {
  try {
    await query('SELECT 1');
    res.json({ ok: true, status: 'healthy', timestamp: new Date().toISOString() });
  } catch (err) {
    next(err);
  }
});

publicRouter.get('/site/config', async (_req, res, next) => {
  try {
    const [company, catalogCfg, payCfg, cats] = await Promise.all([
      getCompanyInfo(),
      getCatalogConfig(),
      getPaymentShippingConfig(),
      listCategories(),
    ]);
    const countsRes = await query<{ category_id: string; cnt: string }>(
      `SELECT pcl.category_id, COUNT(DISTINCT p.id)::text AS cnt
       FROM public.product_category_links pcl
       JOIN public.products p ON p.id = pcl.product_id
       WHERE p.status = 'published' AND p.is_legacy_catalog = FALSE
       GROUP BY pcl.category_id`
    );
    const countMap = new Map(countsRes.rows.map((r) => [r.category_id, Number(r.cnt)]));

    res.json({
      success: true,
      data: {
        company,
        catalog_v2_enabled: catalogCfg.catalog_v2_enabled,
        checkout_ready: isCheckoutReadyFromConfig(payCfg),
        payment_mode: payCfg.mode,
        categories: cats
          .filter((c) => c.is_catalog_v2)
          .map((c) => ({ ...c, product_count: countMap.get(c.id) || 0 })),
      },
    });
  } catch (err) {
    next(err);
  }
});

publicRouter.get('/categories', async (_req, res, next) => {
  try {
    const cats = await listCategories();
    res.json({ success: true, data: cats });
  } catch (err) {
    next(err);
  }
});

publicRouter.get('/taxonomies', async (req, res, next) => {
  try {
    const taxonomy = typeof req.query.taxonomy === 'string' ? req.query.taxonomy : undefined;
    const terms = await listTaxonomyTerms(taxonomy);
    res.json({ success: true, data: terms });
  } catch (err) {
    next(err);
  }
});

publicRouter.get('/products', async (req, res, next) => {
  try {
    const polymerCat =
      typeof req.query.polymer_category === 'string' ? req.query.polymer_category : undefined;
    const materialGrade =
      typeof req.query.material_grade === 'string' ? req.query.material_grade : undefined;
    const materialForm =
      typeof req.query.material_form === 'string' ? req.query.material_form : undefined;
    const application =
      typeof req.query.application === 'string' ? req.query.application : undefined;
    const search = typeof req.query.q === 'string' ? req.query.q : undefined;
    const includeLegacy = req.query.include_legacy === '1' || req.query.include_legacy === 'true';
    const page = Number(req.query.page || 1) || 1;
    const limit = Number(req.query.limit || 24) || 24;

    const result = await listProducts({
      status: 'published',
      polymerCat,
      materialGrade,
      materialForm,
      application,
      search,
      includeLegacy,
      page,
      limit,
    });
    res.json({ success: true, data: result });
  } catch (err) {
    next(err);
  }
});

publicRouter.get('/products/:slug', async (req, res, next) => {
  try {
    const product = await getProductByIdOrSlug({ slug: req.params.slug }, false);
    if (!product) {
      return next(new AppError('Product not found.', 404, 'product_not_found'));
    }
    res.json({ success: true, data: product });
  } catch (err) {
    next(err);
  }
});

const enquirySchema = z.object({
  full_name: z.string().min(1).max(150),
  company_name: z.string().max(150).optional().default(''),
  phone: z.string().min(5).max(50),
  email: z.string().email().max(150),
  country: z.string().max(100).optional().default(''),
  enquiry_type: z.string().min(1).max(80),
  polymer_product: z.string().min(1).max(150),
  material_grade: z.string().max(100).optional().default(''),
  material_form: z.string().max(100).optional().default(''),
  estimated_quantity: z.string().max(100).optional().default(''),
  message: z.string().min(2).max(5000),
  honeypot: z.string().max(200).optional().default(''),
  source_page: z.string().max(200).optional().default('/contact/'),
});

publicRouter.post(
  '/enquiries',
  rateLimitMiddleware({
    namespace: 'enquiry',
    maxHits: 10,
    windowSeconds: 600,
    message: 'Too many enquiry submissions. Please wait a few minutes and try again.',
  }),
  async (req, res, next) => {
    try {
      const parsed = enquirySchema.parse(req.body);
      const created = await createEnquiry(parsed, getClientIp(req));
      res.status(201).json({
        success: true,
        data: created,
        message: 'Thank you. Your enquiry has been logged and our team will respond promptly.',
      });
    } catch (err) {
      next(err);
    }
  }
);

const quoteSchema = z.object({
  product_id: z.string().min(1),
  variation: z.string().min(1).max(60),
  pincode: z.string().regex(/^[1-9][0-9]{5}$/, 'Enter a valid 6-digit Indian PIN code.'),
});

publicRouter.post(
  '/shipping/quote',
  rateLimitMiddleware({
    namespace: 'ship_quote',
    maxHits: 30,
    windowSeconds: 300,
    message: 'Too many shipping quote requests. Please wait a moment.',
  }),
  async (req, res, next) => {
    try {
      const parsed = quoteSchema.parse(req.body);
      const quote = await createShippingQuote({
        productIdOrSlug: parsed.product_id,
        variationKey: parsed.variation,
        pincode: parsed.pincode,
      });
      res.json({ success: true, data: quote });
    } catch (err) {
      next(err);
    }
  }
);

const checkoutStartSchema = z.object({
  product_id: z.string().min(1),
  variation: z.string().min(1).max(60),
  quote_token: z.string().min(32).max(128),
  customer: z.object({
    name: z.string().min(2).max(150),
    email: z.string().email().max(150),
    phone: z.string().min(7).max(30),
    company: z.string().max(150).optional().default(''),
    gstin: z.string().max(20).optional().default(''),
    address: z.string().min(5).max(500),
    city: z.string().min(2).max(100),
    state: z.string().min(2).max(100),
    pincode: z.string().regex(/^[1-9][0-9]{5}$/),
    notes: z.string().max(1000).optional().default(''),
  }),
});

publicRouter.post(
  '/checkout/start',
  rateLimitMiddleware({
    namespace: 'checkout_start',
    maxHits: 20,
    windowSeconds: 600,
    message: 'Too many checkout attempts. Please wait a few minutes.',
  }),
  async (req, res, next) => {
    try {
      const parsed = checkoutStartSchema.parse(req.body);
      const data = await startCheckout({
        productIdOrSlug: parsed.product_id,
        variationKey: parsed.variation,
        quoteToken: parsed.quote_token,
        customer: parsed.customer,
      });
      res.status(201).json({ success: true, data });
    } catch (err) {
      next(err);
    }
  }
);

const checkoutVerifySchema = z.object({
  order_id: z.string().uuid(),
  razorpay_order_id: z.string().min(5),
  razorpay_payment_id: z.string().min(5),
  razorpay_signature: z.string().min(16),
});

publicRouter.post('/checkout/verify', async (req, res, next) => {
  try {
    const parsed = checkoutVerifySchema.parse(req.body);
    const result = await verifyPayment({
      orderId: parsed.order_id,
      razorpayOrderId: parsed.razorpay_order_id,
      razorpayPaymentId: parsed.razorpay_payment_id,
      razorpaySignature: parsed.razorpay_signature,
    });
    res.json({ success: true, data: result });
  } catch (err) {
    next(err);
  }
});

publicRouter.post('/webhooks/razorpay', async (req, res, next) => {
  try {
    const rawBody =
      typeof req.body === 'string'
        ? req.body
        : Buffer.isBuffer(req.body)
        ? req.body.toString('utf8')
        : JSON.stringify(req.body || {});
    const signature = String(req.headers['x-razorpay-signature'] || '');
    const eventId = String(req.headers['x-razorpay-event-id'] || '');
    const result = await handleRazorpayWebhook(rawBody, signature, eventId || undefined);
    res.json({ success: true, data: result });
  } catch (err) {
    next(err);
  }
});

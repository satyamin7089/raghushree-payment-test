import { Router } from 'express';
import multer from 'multer';
import { z } from 'zod';
import { query } from '../db/client.js';
import {
  authenticate,
  hasPermission,
  requirePermission,
  requireRole,
} from '../middleware/auth.js';
import { getClientIp } from '../middleware/rateLimit.js';
import { listAuditLogs, recordAuditLog } from '../services/auditService.js';
import {
  createOrUpdateProduct,
  deleteProductSafely,
  getProductByIdOrSlug,
  installCatalogV2,
  listCategories,
  listProducts,
  listTaxonomyTerms,
  saveProductMaster,
  saveProductSections,
} from '../services/catalogService.js';
import { slugify } from '../services/catalogSeedData.js';
import { listEnquiries, updateEnquiry } from '../services/enquiryService.js';
import { deleteMediaAsset, listMediaAssets, uploadMediaBuffer } from '../services/mediaService.js';
import {
  getOrderDetail,
  listOrders,
  updateOrderFulfilment,
} from '../services/razorpayService.js';
import {
  getCatalogConfig,
  getCompanyInfo,
  getPaymentShippingConfig,
  getPaymentShippingStatusForAdmin,
  upsertSetting,
} from '../services/settingsService.js';
import { createUser, listRoles, listUsers, updateUser } from '../services/userService.js';
import { AppError } from '../utils/errors.js';

export const adminRouter = Router();
const upload = multer({
  storage: multer.memoryStorage(),
  limits: { fileSize: 10 * 1024 * 1024 },
});

adminRouter.use(authenticate);
adminRouter.use(requireRole('administrator', 'manager', 'staff'));

adminRouter.get('/overview', async (req, res, next) => {
  try {
    const user = req.authUser!;
    const canViewOrders = hasPermission(user, 'orders:read');
    const canViewEnquiries = hasPermission(user, 'enquiries:read');

    const [prodStats, enqStats, orderStats] = await Promise.all([
      query<{ status: string; cnt: string }>(
        `SELECT status, COUNT(*)::text AS cnt FROM public.products WHERE is_legacy_catalog = FALSE GROUP BY status`
      ),
      canViewEnquiries
        ? query<{ status: string; cnt: string }>(
            `SELECT status, COUNT(*)::text AS cnt FROM public.enquiries GROUP BY status`
          )
        : Promise.resolve({ rows: [] }),
      canViewOrders
        ? query<{ payment_status: string; cnt: string; total_paise: string }>(
            `SELECT payment_status, COUNT(*)::text AS cnt, COALESCE(SUM(total_paise), 0)::text AS total_paise
             FROM public.orders GROUP BY payment_status`
          )
        : Promise.resolve({ rows: [] }),
    ]);

    res.json({
      success: true,
      data: {
        role: user.role,
        permissions: user.permissions,
        products: prodStats.rows,
        enquiries: canViewEnquiries ? enqStats.rows : null,
        orders: canViewOrders ? orderStats.rows : null,
      },
    });
  } catch (err) {
    next(err);
  }
});

// ---- Products ----
adminRouter.get('/products', requirePermission('products:read'), async (req, res, next) => {
  try {
    const statusParam = typeof req.query.status === 'string' ? req.query.status : 'all';
    const status = (
      ['published', 'draft', 'archived', 'all'].includes(statusParam) ? statusParam : 'all'
    ) as 'published' | 'draft' | 'archived' | 'all';
    const includeLegacy = req.query.include_legacy === '1' || req.query.include_legacy === 'true';
    const search = typeof req.query.q === 'string' ? req.query.q : undefined;
    const polymerCat = typeof req.query.category === 'string' ? req.query.category : undefined;
    const page = Number(req.query.page || 1) || 1;
    const limit = Number(req.query.limit || 50) || 50;

    const result = await listProducts({
      status,
      includeLegacy,
      search,
      polymerCat,
      page,
      limit,
    });
    res.json({ success: true, data: result });
  } catch (err) {
    next(err);
  }
});

adminRouter.get('/products/:id', requirePermission('products:read'), async (req, res, next) => {
  try {
    const p = await getProductByIdOrSlug({ id: String(req.params.id) }, true);
    if (!p) return next(new AppError('Product not found.', 404, 'not_found'));
    res.json({ success: true, data: p });
  } catch (err) {
    next(err);
  }
});

const productSaveSchema = z.object({
  title: z.string().min(1).max(200),
  slug: z.string().max(200).optional(),
  polymer_code: z.string().max(40).optional(),
  primary_category_id: z.string().uuid().nullable().optional(),
  category_ids: z.array(z.string().uuid()).optional(),
  taxonomy_term_ids: z.array(z.string().uuid()).optional(),
  image_slot: z.string().max(80).optional(),
  featured_media_id: z.string().uuid().nullable().optional(),
  gallery_media_ids: z.array(z.string().uuid()).optional(),
  short_desc: z.string().max(2000).optional(),
  content_html: z.string().max(50000).optional(),
  highlights: z.array(z.string().max(500)).optional(),
  grades_notes: z.string().max(5000).optional(),
  grades_rows: z.array(z.array(z.string().max(1000))).optional(),
  industries_rows: z.array(z.string().max(500)).optional(),
  processing_rows: z.array(z.array(z.string().max(1000))).optional(),
  datasheet_url: z.string().max(1000).optional(),
  packaging: z.string().max(500).optional(),
  pack_size: z.string().max(500).optional(),
  moq: z.string().max(500).optional(),
  availability: z.string().max(500).optional(),
  status: z.enum(['draft', 'published', 'archived']).optional(),
  menu_order: z.number().int().optional(),
  seo: z
    .object({
      seo_title: z.string().max(300).optional(),
      seo_description: z.string().max(600).optional(),
      seo_canonical: z.string().max(500).optional(),
      seo_robots: z.string().max(100).optional(),
      og_title: z.string().max(300).optional(),
      og_description: z.string().max(600).optional(),
      og_image_url: z.string().max(500).optional(),
      twitter_title: z.string().max(300).optional(),
      twitter_description: z.string().max(600).optional(),
      twitter_image_url: z.string().max(500).optional(),
    })
    .optional(),
});

adminRouter.post('/products', requirePermission('products:write'), async (req, res, next) => {
  try {
    const parsed = productSaveSchema.parse(req.body);
    const created = await createOrUpdateProduct(null, parsed, req.authUser!.id);
    await recordAuditLog({
      actorId: req.authUser!.id,
      actorEmail: req.authUser!.email,
      action: 'product.create',
      resourceType: 'product',
      resourceId: created.id,
      metadata: { title: created.title, slug: created.slug },
      ipAddress: getClientIp(req),
    });
    res.status(201).json({ success: true, data: created });
  } catch (err) {
    next(err);
  }
});

adminRouter.put('/products/:id', requirePermission('products:write'), async (req, res, next) => {
  try {
    const id = String(req.params.id);
    const parsed = productSaveSchema.parse(req.body);
    const updated = await createOrUpdateProduct(id, parsed, req.authUser!.id);
    await recordAuditLog({
      actorId: req.authUser!.id,
      actorEmail: req.authUser!.email,
      action: 'product.update',
      resourceType: 'product',
      resourceId: updated.id,
      metadata: { title: updated.title, slug: updated.slug },
      ipAddress: getClientIp(req),
    });
    res.json({ success: true, data: updated });
  } catch (err) {
    next(err);
  }
});

adminRouter.delete(
  '/products/:id',
  requirePermission('products:delete'),
  async (req, res, next) => {
    try {
      const id = String(req.params.id);
      const result = await deleteProductSafely(id);
      await recordAuditLog({
        actorId: req.authUser!.id,
        actorEmail: req.authUser!.email,
        action: result.archived ? 'product.archive' : 'product.delete',
        resourceType: 'product',
        resourceId: id,
        ipAddress: getClientIp(req),
      });
      res.json({ success: true, data: result });
    } catch (err) {
      next(err);
    }
  }
);

adminRouter.put(
  '/products/:id/master',
  requirePermission('products:write'),
  async (req, res, next) => {
    try {
      const id = String(req.params.id);
      const updated = await saveProductMaster(id, req.body);
      await recordAuditLog({
        actorId: req.authUser!.id,
        actorEmail: req.authUser!.email,
        action: 'product.master.update',
        resourceType: 'product',
        resourceId: id,
        metadata: { product_type: req.body?.product_type },
        ipAddress: getClientIp(req),
      });
      res.json({ success: true, data: updated });
    } catch (err) {
      next(err);
    }
  }
);

adminRouter.put(
  '/products/:id/sections',
  requirePermission('products:write'),
  async (req, res, next) => {
    try {
      const id = String(req.params.id);
      const updated = await saveProductSections(id, req.body);
      await recordAuditLog({
        actorId: req.authUser!.id,
        actorEmail: req.authUser!.email,
        action: 'product.sections.update',
        resourceType: 'product',
        resourceId: id,
        ipAddress: getClientIp(req),
      });
      res.json({ success: true, data: updated });
    } catch (err) {
      next(err);
    }
  }
);

adminRouter.post(
  '/catalog/install-v2',
  requirePermission('settings:write'),
  async (req, res, next) => {
    try {
      const result = await installCatalogV2();
      await recordAuditLog({
        actorId: req.authUser!.id,
        actorEmail: req.authUser!.email,
        action: 'catalog.install_v2',
        resourceType: 'catalog',
        metadata: result,
        ipAddress: getClientIp(req),
      });
      res.json({ success: true, data: result });
    } catch (err) {
      next(err);
    }
  }
);

// ---- Categories & Taxonomies ----
adminRouter.get('/categories', requirePermission('products:read'), async (_req, res, next) => {
  try {
    res.json({ success: true, data: await listCategories() });
  } catch (err) {
    next(err);
  }
});

adminRouter.post('/categories', requirePermission('categories:write'), async (req, res, next) => {
  try {
    const schema = z.object({
      title: z.string().min(1).max(120),
      slug: z.string().max(120).optional(),
      category_type: z.enum(['polymer', 'pipe']).default('polymer'),
      description: z.string().max(1000).optional().default(''),
      sort_order: z.number().int().optional().default(10),
      is_catalog_v2: z.boolean().optional().default(true),
    });
    const parsed = schema.parse(req.body);
    const slug = parsed.slug ? slugify(parsed.slug) : slugify(parsed.title);
    const result = await query(
      `INSERT INTO public.product_categories (slug, title, category_type, description, sort_order, is_catalog_v2)
       VALUES ($1, $2, $3, $4, $5, $6) RETURNING *`,
      [slug, parsed.title, parsed.category_type, parsed.description, parsed.sort_order, parsed.is_catalog_v2]
    );
    res.status(201).json({ success: true, data: result.rows[0] });
  } catch (err) {
    next(err);
  }
});

adminRouter.put('/categories/:id', requirePermission('categories:write'), async (req, res, next) => {
  try {
    const id = String(req.params.id);
    const schema = z.object({
      title: z.string().min(1).max(120),
      category_type: z.enum(['polymer', 'pipe']),
      description: z.string().max(1000).optional().default(''),
      sort_order: z.number().int().optional().default(10),
      is_catalog_v2: z.boolean().optional().default(true),
    });
    const parsed = schema.parse(req.body);
    const result = await query(
      `UPDATE public.product_categories
       SET title = $2, category_type = $3, description = $4, sort_order = $5, is_catalog_v2 = $6
       WHERE id = $1 RETURNING *`,
      [id, parsed.title, parsed.category_type, parsed.description, parsed.sort_order, parsed.is_catalog_v2]
    );
    if (!result.rows[0]) return next(new AppError('Category not found.', 404, 'not_found'));
    res.json({ success: true, data: result.rows[0] });
  } catch (err) {
    next(err);
  }
});

adminRouter.get('/taxonomies', requirePermission('products:read'), async (req, res, next) => {
  try {
    const tax = typeof req.query.taxonomy === 'string' ? req.query.taxonomy : undefined;
    res.json({ success: true, data: await listTaxonomyTerms(tax) });
  } catch (err) {
    next(err);
  }
});

// ---- Media ----
adminRouter.get('/media', requirePermission('products:read'), async (req, res, next) => {
  try {
    const items = await listMediaAssets(Number(req.query.limit || 50), Number(req.query.offset || 0));
    res.json({ success: true, data: items });
  } catch (err) {
    next(err);
  }
});

adminRouter.post(
  '/media/upload',
  requirePermission('media:upload'),
  upload.single('file'),
  async (req, res, next) => {
    try {
      if (!req.file) return next(new AppError('No file provided.', 400, 'missing_file'));
      const asset = await uploadMediaBuffer({
        buffer: req.file.buffer,
        originalName: req.file.originalname,
        mimeType: req.file.mimetype,
        altText: typeof req.body?.alt_text === 'string' ? req.body.alt_text : '',
        uploadedBy: req.authUser!.id,
      });
      const assetRecord = asset as Record<string, unknown>;
      await recordAuditLog({
        actorId: req.authUser!.id,
        actorEmail: req.authUser!.email,
        action: 'media.upload',
        resourceType: 'media_asset',
        resourceId: String(assetRecord.id || ''),
        metadata: { url: assetRecord.public_url },
        ipAddress: getClientIp(req),
      });
      res.status(201).json({ success: true, data: asset });
    } catch (err) {
      next(err);
    }
  }
);

adminRouter.delete('/media/:id', requirePermission('media:upload'), async (req, res, next) => {
  try {
    const result = await deleteMediaAsset(String(req.params.id));
    res.json({ success: true, data: result });
  } catch (err) {
    next(err);
  }
});

// ---- Orders ----
adminRouter.get('/orders', requirePermission('orders:read'), async (req, res, next) => {
  try {
    const result = await listOrders({
      paymentStatus: typeof req.query.payment_status === 'string' ? req.query.payment_status : undefined,
      fulfilmentStatus: typeof req.query.fulfilment_status === 'string' ? req.query.fulfilment_status : undefined,
      search: typeof req.query.q === 'string' ? req.query.q : undefined,
      page: Number(req.query.page || 1) || 1,
      limit: Number(req.query.limit || 25) || 25,
    });
    res.json({ success: true, data: result });
  } catch (err) {
    next(err);
  }
});

adminRouter.get('/orders/:id', requirePermission('orders:read'), async (req, res, next) => {
  try {
    const detail = await getOrderDetail(String(req.params.id));
    res.json({ success: true, data: detail });
  } catch (err) {
    next(err);
  }
});

adminRouter.patch('/orders/:id/fulfilment', requirePermission('orders:fulfil'), async (req, res, next) => {
  try {
    const id = String(req.params.id);
    const schema = z.object({
      fulfilment_status: z.enum(['awaiting', 'fulfilled']),
      manual_courier: z.string().max(120).optional(),
      manual_tracking: z.string().max(120).optional(),
    });
    const parsed = schema.parse(req.body);
    const updated = await updateOrderFulfilment(id, parsed);
    await recordAuditLog({
      actorId: req.authUser!.id,
      actorEmail: req.authUser!.email,
      action: 'order.fulfilment.update',
      resourceType: 'order',
      resourceId: id,
      metadata: parsed,
      ipAddress: getClientIp(req),
    });
    res.json({ success: true, data: updated });
  } catch (err) {
    next(err);
  }
});

// ---- Enquiries ----
adminRouter.get('/enquiries', requirePermission('enquiries:read'), async (req, res, next) => {
  try {
    const result = await listEnquiries({
      status: typeof req.query.status === 'string' ? req.query.status : undefined,
      enquiryType: typeof req.query.enquiry_type === 'string' ? req.query.enquiry_type : undefined,
      search: typeof req.query.q === 'string' ? req.query.q : undefined,
      page: Number(req.query.page || 1) || 1,
      limit: Number(req.query.limit || 25) || 25,
    });
    res.json({ success: true, data: result });
  } catch (err) {
    next(err);
  }
});

adminRouter.patch('/enquiries/:id', requirePermission('enquiries:write'), async (req, res, next) => {
  try {
    const id = String(req.params.id);
    const schema = z.object({
      status: z.enum(['new', 'in_review', 'quoted', 'closed', 'spam']).optional(),
      internal_notes: z.string().max(3000).optional(),
      assigned_to: z.string().uuid().nullable().optional(),
    });
    const parsed = schema.parse(req.body);
    const updated = await updateEnquiry(id, parsed);
    await recordAuditLog({
      actorId: req.authUser!.id,
      actorEmail: req.authUser!.email,
      action: 'enquiry.update',
      resourceType: 'enquiry',
      resourceId: id,
      metadata: parsed,
      ipAddress: getClientIp(req),
    });
    res.json({ success: true, data: updated });
  } catch (err) {
    next(err);
  }
});

// ---- Users & Roles (Administrator only) ----
adminRouter.get('/users', requirePermission('users:read'), async (_req, res, next) => {
  try {
    const [users, roles] = await Promise.all([listUsers(), listRoles()]);
    res.json({ success: true, data: { users, roles } });
  } catch (err) {
    next(err);
  }
});

adminRouter.post('/users', requirePermission('users:write'), async (req, res, next) => {
  try {
    const schema = z.object({
      email: z.string().email(),
      full_name: z.string().min(2).max(150),
      password: z.string().min(8).max(120),
      role_slug: z.enum(['administrator', 'manager', 'staff', 'customer']),
      is_active: z.boolean().optional(),
    });
    const parsed = schema.parse(req.body);
    const created = await createUser(parsed);
    const createdId = String((created as Record<string, unknown>).id || '');
    await recordAuditLog({
      actorId: req.authUser!.id,
      actorEmail: req.authUser!.email,
      action: 'user.create',
      resourceType: 'user_profile',
      resourceId: createdId,
      metadata: {
        email: (created as Record<string, unknown>).email,
        role: (created as Record<string, unknown>).role_slug,
      },
      ipAddress: getClientIp(req),
    });
    res.status(201).json({ success: true, data: created });
  } catch (err) {
    next(err);
  }
});

adminRouter.patch('/users/:id', requirePermission('users:write'), async (req, res, next) => {
  try {
    const id = String(req.params.id);
    const schema = z.object({
      full_name: z.string().min(2).max(150).optional(),
      role_slug: z.enum(['administrator', 'manager', 'staff', 'customer']).optional(),
      is_active: z.boolean().optional(),
      password: z.string().min(8).max(120).optional(),
    });
    const parsed = schema.parse(req.body);
    const updated = await updateUser(id, parsed);
    await recordAuditLog({
      actorId: req.authUser!.id,
      actorEmail: req.authUser!.email,
      action: 'user.update',
      resourceType: 'user_profile',
      resourceId: id,
      metadata: { role_slug: parsed.role_slug, is_active: parsed.is_active },
      ipAddress: getClientIp(req),
    });
    res.json({ success: true, data: updated });
  } catch (err) {
    next(err);
  }
});

// ---- Settings (Administrator only) ----
adminRouter.get('/settings', requirePermission('settings:read'), async (_req, res, next) => {
  try {
    const [company, catalog, paymentShipping] = await Promise.all([
      getCompanyInfo(),
      getCatalogConfig(),
      getPaymentShippingStatusForAdmin(),
    ]);
    res.json({
      success: true,
      data: { company, catalog, payment_shipping: paymentShipping },
    });
  } catch (err) {
    next(err);
  }
});

adminRouter.put('/settings/company', requirePermission('settings:write'), async (req, res, next) => {
  try {
    await upsertSetting('company_info', req.body, false, req.authUser!.id);
    await recordAuditLog({
      actorId: req.authUser!.id,
      actorEmail: req.authUser!.email,
      action: 'settings.company.update',
      resourceType: 'business_settings',
      resourceId: 'company_info',
      ipAddress: getClientIp(req),
    });
    res.json({ success: true, data: await getCompanyInfo() });
  } catch (err) {
    next(err);
  }
});

adminRouter.put(
  '/settings/payment-shipping',
  requirePermission('settings:write'),
  async (req, res, next) => {
    try {
      const existing = await getPaymentShippingConfig();
      const body = req.body as Record<string, unknown>;
      const merged = {
        mode: body.mode === 'live' ? 'live' : 'test',
        pickup_pincode: /^[1-9][0-9]{5}$/.test(String(body.pickup_pincode || ''))
          ? String(body.pickup_pincode)
          : existing.pickup_pincode,
        checkout_enabled: Boolean(body.checkout_enabled),
        allow_live: Boolean(body.allow_live),
        allow_tax_live: Boolean(body.allow_tax_live),
        razorpay_key_id:
          typeof body.razorpay_key_id === 'string' && body.razorpay_key_id.trim() !== ''
            ? body.razorpay_key_id.trim()
            : existing.razorpay_key_id,
        razorpay_key_secret:
          typeof body.razorpay_key_secret === 'string' && body.razorpay_key_secret.trim() !== ''
            ? body.razorpay_key_secret.trim()
            : existing.razorpay_key_secret,
        razorpay_webhook_secret:
          typeof body.razorpay_webhook_secret === 'string' &&
          body.razorpay_webhook_secret.trim() !== ''
            ? body.razorpay_webhook_secret.trim()
            : existing.razorpay_webhook_secret,
        shiprocket_email:
          typeof body.shiprocket_email === 'string' && body.shiprocket_email.trim() !== ''
            ? body.shiprocket_email.trim()
            : existing.shiprocket_email,
        shiprocket_password:
          typeof body.shiprocket_password === 'string' && body.shiprocket_password.trim() !== ''
            ? body.shiprocket_password.trim()
            : existing.shiprocket_password,
      };
      await upsertSetting('payment_shipping_config', merged, true, req.authUser!.id);
      await recordAuditLog({
        actorId: req.authUser!.id,
        actorEmail: req.authUser!.email,
        action: 'settings.payment_shipping.update',
        resourceType: 'business_settings',
        resourceId: 'payment_shipping_config',
        metadata: { mode: merged.mode, checkout_enabled: merged.checkout_enabled },
        ipAddress: getClientIp(req),
      });
      res.json({ success: true, data: await getPaymentShippingStatusForAdmin() });
    } catch (err) {
      next(err);
    }
  }
);

adminRouter.put('/settings/catalog', requirePermission('settings:write'), async (req, res, next) => {
  try {
    const enabled = Boolean(req.body?.catalog_v2_enabled);
    await upsertSetting('catalog_config', { catalog_v2_enabled: enabled }, false, req.authUser!.id);
    res.json({ success: true, data: await getCatalogConfig() });
  } catch (err) {
    next(err);
  }
});

// ---- Audit Logs ----
adminRouter.get('/audit-logs', requirePermission('audit:read'), async (req, res, next) => {
  try {
    const logs = await listAuditLogs(Number(req.query.limit || 100), Number(req.query.offset || 0));
    res.json({ success: true, data: logs });
  } catch (err) {
    next(err);
  }
});

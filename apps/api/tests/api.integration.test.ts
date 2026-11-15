import { after, before, describe, it } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import path from 'node:path';
import os from 'node:os';
import request from 'supertest';
import type { Express } from 'express';

let app: Express;
let tmpDir: string;
let adminToken = '';
let staffToken = '';

before(async () => {
  tmpDir = await fs.mkdtemp(path.join(os.tmpdir(), 'rg-api-test-'));
  process.env.NODE_ENV = 'test';
  process.env.DB_DRIVER = 'pglite';
  process.env.PGLITE_DATA_DIR = path.join(tmpDir, 'pglite');
  process.env.BOOTSTRAP_ADMIN_EMAIL = 'admin@raghushree.test';
  process.env.BOOTSTRAP_ADMIN_PASSWORD = 'TestPassword#2026';
  process.env.AUTH_JWT_SECRET = 'test-jwt-secret-1234567890';

  const { bootstrapBackend } = await import('../src/server.js');
  app = await bootstrapBackend();
});

after(async () => {
  const { closeDatabase } = await import('../src/db/client.js');
  await closeDatabase();
  await fs.rm(tmpDir, { recursive: true, force: true }).catch(() => undefined);
});

describe('Raghushree API Integration', () => {
  it('serves health check, site config, catalog V2 products, and SEO files', async () => {
    const health = await request(app).get('/api/v1/health');
    assert.equal(health.status, 200);
    assert.equal(health.body.ok, true);

    const siteCfg = await request(app).get('/api/v1/site/config');
    assert.equal(siteCfg.status, 200);
    assert.equal(siteCfg.body.data.categories.length, 9);

    const catalog = await request(app).get('/api/v1/products?limit=50');
    assert.equal(catalog.status, 200);
    assert.equal(catalog.body.data.total, 22);

    // Legacy product slug remains directly reachable
    const legacySingle = await request(app).get('/api/v1/products/polypropylene-pp');
    assert.equal(legacySingle.status, 200);
    assert.equal(legacySingle.body.data.polymer_code, 'PP');

    // Legacy URL redirects
    const redir = await request(app).get('/polymer-products/pp-granules/');
    assert.equal(redir.status, 301);
    assert.equal(redir.headers.location, '/products/pp-granules/');

    const sitemap = await request(app).get('/sitemap.xml');
    assert.equal(sitemap.status, 200);
    assert.match(sitemap.text, /<urlset/);
    assert.match(sitemap.text, /\/products\/rigid-pvc-granules\//);

    const robots = await request(app).get('/robots.txt');
    assert.equal(robots.status, 200);
    assert.match(robots.text, /Disallow: \/admin\//);
  });

  it('authenticates administrator and enforces strict RBAC for staff user', async () => {
    const loginRes = await request(app)
      .post('/api/v1/auth/login')
      .send({ email: 'admin@raghushree.test', password: 'TestPassword#2026' });
    assert.equal(loginRes.status, 200);
    adminToken = loginRes.body.data.token;
    assert.ok(adminToken);

    // Create a staff user
    const createStaff = await request(app)
      .post('/api/v1/admin/users')
      .set('Authorization', `Bearer ${adminToken}`)
      .send({
        email: 'staff@raghushree.test',
        full_name: 'Catalog Staff',
        password: 'StaffPassword#2026',
        role_slug: 'staff',
      });
    assert.equal(createStaff.status, 201);

    const staffLogin = await request(app)
      .post('/api/v1/auth/login')
      .send({ email: 'staff@raghushree.test', password: 'StaffPassword#2026' });
    assert.equal(staffLogin.status, 200);
    staffToken = staffLogin.body.data.token;

    // Staff can read products and orders (for fulfilment) but is blocked from enquiries, users, settings, and audit
    const staffProducts = await request(app)
      .get('/api/v1/admin/products')
      .set('Authorization', `Bearer ${staffToken}`);
    assert.equal(staffProducts.status, 200);

    const staffOrders = await request(app)
      .get('/api/v1/admin/orders')
      .set('Authorization', `Bearer ${staffToken}`);
    assert.equal(staffOrders.status, 200);

    const staffEnquiries = await request(app)
      .get('/api/v1/admin/enquiries')
      .set('Authorization', `Bearer ${staffToken}`);
    assert.equal(staffEnquiries.status, 403);

    const staffUsers = await request(app)
      .get('/api/v1/admin/users')
      .set('Authorization', `Bearer ${staffToken}`);
    assert.equal(staffUsers.status, 403);

    const staffSettings = await request(app)
      .get('/api/v1/admin/settings')
      .set('Authorization', `Bearer ${staffToken}`);
    assert.equal(staffSettings.status, 403);
  });

  it('saves Product Master & Structured Sections with validation', async () => {
    const prodRes = await request(app).get('/api/v1/products/rigid-pvc-granules');
    const productId = prodRes.body.data.id;

    // Invalid HSN rejected
    const badMaster = await request(app)
      .put(`/api/v1/admin/products/${productId}/master`)
      .set('Authorization', `Bearer ${staffToken}`)
      .send({
        product_type: 'polymer',
        gst_percent: '18',
        hsn: '12',
        polymer_packs: {},
      });
    assert.equal(badMaster.status, 400);

    // Valid Polymer Master saved
    const goodMaster = await request(app)
      .put(`/api/v1/admin/products/${productId}/master`)
      .set('Authorization', `Bearer ${staffToken}`)
      .send({
        product_type: 'polymer',
        gst_percent: '18',
        hsn: '390410',
        polymer_packs: {
          '5kg': {
            price_rupees: '850.00',
            enabled: true,
            weight_kg: '5.3',
            length_cm: '30',
            width_cm: '25',
            height_cm: '12',
          },
          sample: {
            enabled: true,
            weight_kg: '0.5',
            length_cm: '15',
            width_cm: '10',
            height_cm: '5',
          },
        },
      });
    assert.equal(goodMaster.status, 200);
    assert.equal(goodMaster.body.data.variations['5kg'].price_paise, 85000);
    assert.equal(goodMaster.body.data.variations.sample.price_paise, 0);

    // Structured sections saved
    const secRes = await request(app)
      .put(`/api/v1/admin/products/${productId}/sections`)
      .set('Authorization', `Bearer ${staffToken}`)
      .send({
        intros: { features: 'Key technical properties for rigid extrusion.' },
        sections: {
          features: [['Thermal Stability', 'Consistent melt stability for twin-screw extrusion.']],
          specifications: [['K-Value', '65', 'ISO 1628-2']],
          faqs: [['Minimum trial order?', '5 kg pack and sample packs are available online.']],
        },
      });
    assert.equal(secRes.status, 200);
    assert.equal(secRes.body.data.sections.features.length, 1);
    assert.equal(secRes.body.data.sections.specifications[0][2], 'ISO 1628-2');
  });

  it('handles Shiprocket quoting, 30kg pipe ceiling, Razorpay verification, and idempotent webhook', async () => {
    const { hmacSha256Hex } = await import('../src/utils/crypto.js');
    const { createShippingQuote } = await import('../src/services/shiprocketService.js');
    const { startCheckout, verifyPayment } = await import('../src/services/razorpayService.js');

    // Enable payment/shipping config in test mode via admin settings
    const updSettings = await request(app)
      .put('/api/v1/admin/settings/payment-shipping')
      .set('Authorization', `Bearer ${adminToken}`)
      .send({
        mode: 'test',
        pickup_pincode: '208001',
        checkout_enabled: true,
        razorpay_key_id: 'rzp_test_1234567890',
        razorpay_key_secret: 'rzp_test_secret_abc',
        razorpay_webhook_secret: 'whsec_test_xyz',
        shiprocket_email: 'shipping@raghushree.test',
        shiprocket_password: 'sr_password',
      });
    assert.equal(updSettings.status, 200);
    assert.equal(updSettings.body.data.checkout_ready, true);

    // Configure a pipe product exceeding 30kg on packet_2
    const pipeProd = await request(app).get('/api/v1/products/oil-hoses');
    const pipeId = pipeProd.body.data.id;
    await request(app)
      .put(`/api/v1/admin/products/${pipeId}/master`)
      .set('Authorization', `Bearer ${adminToken}`)
      .send({
        product_type: 'pipe',
        gst_percent: '18',
        hsn: '391723',
        polymer_packs: {},
        packet_label: '30m coil',
        packet_price_rupees: '1500.00',
        packet_rows_text: '1,18,45,45,20\n2,36,45,45,40',
      });

    // Mock fetch for Shiprocket + Razorpay
    const mockFetch: typeof fetch = async (url) => {
      const u = String(url);
      if (u.includes('shiprocket.in/v1/external/auth/login')) {
        return new Response(JSON.stringify({ token: 'sr_tok_123' }), { status: 200 });
      }
      if (u.includes('shiprocket.in/v1/external/courier/serviceability')) {
        return new Response(
          JSON.stringify({
            data: {
              available_courier_companies: [
                { courier_company_id: 55, courier_name: 'Delhivery Surface', rate: 150, blocked: 0 },
                { courier_company_id: 66, courier_name: 'Xpressbees', rate: 200, blocked: 0 },
              ],
            },
          }),
          { status: 200 }
        );
      }
      if (u.includes('api.razorpay.com/v1/orders') && !u.includes('order_test_')) {
        return new Response(
          JSON.stringify({ id: 'order_test_999', amount: 118000, currency: 'INR', status: 'created' }),
          { status: 200 }
        );
      }
      if (u.includes('api.razorpay.com/v1/payments/pay_test_111')) {
        return new Response(
          JSON.stringify({
            id: 'pay_test_111',
            order_id: 'order_test_999',
            status: 'captured',
            amount: 118000,
            currency: 'INR',
            method: 'upi',
          }),
          { status: 200 }
        );
      }
      if (u.includes('api.razorpay.com/v1/orders/order_test_999')) {
        return new Response(
          JSON.stringify({
            id: 'order_test_999',
            status: 'paid',
            amount_paid: 118000,
            currency: 'INR',
          }),
          { status: 200 }
        );
      }
      throw new Error(`Unexpected fetch URL: ${u}`);
    };

    // Pipe > 30kg rejected before Shiprocket
    await assert.rejects(
      () =>
        createShippingQuote(
          { productIdOrSlug: 'oil-hoses', variationKey: 'packet_2', pincode: '110001' },
          mockFetch
        ),
      /30 kg/
    );

    // Polymer 5kg quote: product ₹850 + 18% GST (₹153) = ₹1003; ship ₹150 + 18% GST (₹27) = ₹177; total = ₹1180 (118000 paise)
    const quote = await createShippingQuote(
      { productIdOrSlug: 'rigid-pvc-granules', variationKey: '5kg', pincode: '110001' },
      mockFetch
    );
    assert.equal(quote.total_paise, 118000);
    assert.equal(quote.courier_name, 'Delhivery Surface');

    // Start checkout consumes quote token
    const checkout = await startCheckout(
      {
        productIdOrSlug: 'rigid-pvc-granules',
        variationKey: '5kg',
        quoteToken: quote.quote_token,
        customer: {
          name: 'Rajesh Sharma',
          email: 'rajesh@factory.in',
          phone: '9876543210',
          company: 'Sharma Extrusions',
          gstin: '09AAACS1234A1Z5',
          address: 'Plot 14, Industrial Area',
          city: 'New Delhi',
          state: 'Delhi',
          pincode: '110001',
        },
      },
      mockFetch
    );
    assert.equal(checkout.razorpay_order_id, 'order_test_999');
    assert.equal(checkout.amount_paise, 118000);

    // Reusing quote token must fail
    await assert.rejects(
      () =>
        startCheckout(
          {
            productIdOrSlug: 'rigid-pvc-granules',
            variationKey: '5kg',
            quoteToken: quote.quote_token,
            customer: {
              name: 'Rajesh Sharma',
              email: 'rajesh@factory.in',
              phone: '9876543210',
              address: 'Plot 14',
              city: 'Delhi',
              state: 'Delhi',
              pincode: '110001',
            },
          },
          mockFetch
        ),
      /expired or was already used/
    );

    // Verify payment with valid HMAC signature
    const sig = hmacSha256Hex('order_test_999|pay_test_111', 'rzp_test_secret_abc');
    const verified = await verifyPayment(
      {
        orderId: checkout.order_id,
        razorpayOrderId: 'order_test_999',
        razorpayPaymentId: 'pay_test_111',
        razorpaySignature: sig,
      },
      mockFetch
    );
    assert.equal(verified.payment_status, 'paid');

    // Webhook idempotency test via HTTP endpoint
    const webhookPayload = JSON.stringify({
      event: 'order.paid',
      payload: {
        order: { entity: { id: 'order_test_999', amount_paid: 118000, currency: 'INR', status: 'paid' } },
        payment: { entity: { id: 'pay_test_111', order_id: 'order_test_999', amount: 118000 } },
      },
    });
    const whSig = hmacSha256Hex(webhookPayload, 'whsec_test_xyz');
    const wh1 = await request(app)
      .post('/api/v1/webhooks/razorpay')
      .set('Content-Type', 'application/json')
      .set('x-razorpay-signature', whSig)
      .set('x-razorpay-event-id', 'evt_12345')
      .send(webhookPayload);
    assert.equal(wh1.status, 200);
    assert.equal(wh1.body.data.duplicate, false);

    const wh2 = await request(app)
      .post('/api/v1/webhooks/razorpay')
      .set('Content-Type', 'application/json')
      .set('x-razorpay-signature', whSig)
      .set('x-razorpay-event-id', 'evt_12345')
      .send(webhookPayload);
    assert.equal(wh2.status, 200);
    assert.equal(wh2.body.data.duplicate, true);

    // Admin updates manual delivery fulfilment
    const fulfill = await request(app)
      .patch(`/api/v1/admin/orders/${checkout.order_id}/fulfilment`)
      .set('Authorization', `Bearer ${adminToken}`)
      .send({
        fulfilment_status: 'fulfilled',
        manual_courier: 'Delhivery Surface',
        manual_tracking: 'DLV-99887766',
      });
    assert.equal(fulfill.status, 200);
    assert.equal(fulfill.body.data.fulfilment_status, 'fulfilled');
    assert.equal(fulfill.body.data.manual_tracking, 'DLV-99887766');
  });

  it('validates & persists RFQ enquiries and blocks honeypot spam', async () => {
    const spam = await request(app)
      .post('/api/v1/enquiries')
      .send({
        full_name: 'Bot',
        phone: '9999999999',
        email: 'bot@spam.test',
        enquiry_type: 'Bulk Order',
        polymer_product: 'PP Granules',
        message: 'Buy now',
        honeypot: 'http://spam.invalid',
      });
    assert.equal(spam.status, 400);

    const good = await request(app)
      .post('/api/v1/enquiries')
      .send({
        full_name: 'Anita Verma',
        company_name: 'Verma Packaging',
        phone: '+91-9811122233',
        email: 'anita@vermapack.in',
        country: 'India',
        enquiry_type: 'Bulk Order',
        polymer_product: 'PP Granules',
        material_grade: 'Virgin Grades',
        material_form: 'Plastic Granules',
        estimated_quantity: '5 MT',
        message: 'Need monthly supply quote for raffia extrusion.',
      });
    assert.equal(good.status, 201);
    assert.ok(good.body.data.reference_code);

    const enqList = await request(app)
      .get('/api/v1/admin/enquiries')
      .set('Authorization', `Bearer ${adminToken}`);
    assert.equal(enqList.status, 200);
    assert.equal(enqList.body.data.total, 1);
  });
});

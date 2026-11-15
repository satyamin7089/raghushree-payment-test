import { query, withTransaction } from '../db/client.js';
import { AppError } from '../utils/errors.js';
import { hmacSha256Hex, sha256Hex, timingSafeEqualStr } from '../utils/crypto.js';
import { getPaymentShippingConfig, isCheckoutReadyFromConfig } from './settingsService.js';
import { consumeShippingQuote } from './shiprocketService.js';

export interface StartCheckoutInput {
  productIdOrSlug: string;
  variationKey: string;
  quoteToken: string;
  customer: {
    name: string;
    email: string;
    phone: string;
    company?: string;
    gstin?: string;
    address: string;
    city: string;
    state: string;
    pincode: string;
    notes?: string;
  };
}

export async function startCheckout(
  input: StartCheckoutInput,
  fetchFn: typeof fetch = fetch
) {
  const cfg = await getPaymentShippingConfig();
  if (!isCheckoutReadyFromConfig(cfg)) {
    throw new AppError('Online checkout is currently unavailable.', 503, 'checkout_disabled');
  }

  const pincode = input.customer.pincode.trim();
  if (!/^[1-9][0-9]{5}$/.test(pincode)) {
    throw new AppError('Enter a valid 6-digit Indian PIN code.', 400, 'invalid_pincode');
  }
  const gstin = (input.customer.gstin || '').trim().toUpperCase();
  if (gstin !== '' && !/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/.test(gstin)) {
    throw new AppError('Enter a valid 15-character GSTIN.', 400, 'invalid_gstin');
  }

  const prodRes = await query<{ id: string; title: string; slug: string }>(
    `SELECT id, title, slug FROM public.products
     WHERE (id::text = $1 OR slug = $1) AND status = 'published' LIMIT 1`,
    [input.productIdOrSlug]
  );
  const product = prodRes.rows[0];
  if (!product) {
    throw new AppError('Product not found.', 404, 'product_not_found');
  }

  const quote = await consumeShippingQuote(
    input.quoteToken,
    product.id,
    input.variationKey,
    pincode
  );

  const nameParts = input.customer.name.trim().split(/\s+/);
  const firstName = nameParts[0] || 'Customer';
  const lastName = nameParts.slice(1).join(' ') || '-';

  const orderSnapshot = {
    customer_name: input.customer.name.trim(),
    customer_email: input.customer.email.trim().toLowerCase(),
    customer_phone: input.customer.phone.trim(),
    customer_company: (input.customer.company || '').trim(),
    customer_gstin: gstin,
    shipping_address: input.customer.address.trim(),
    shipping_city: input.customer.city.trim(),
    shipping_state: input.customer.state.trim(),
    shipping_pincode: pincode,
    customer_notes: (input.customer.notes || '').trim(),
    pack: quote.pack_snapshot,
  };

  const receiptHash = sha256Hex(`${quote.id}:${Date.now()}`).slice(0, 32);

  const orderRecord = await withTransaction(async (tx) => {
    const custRes = await tx.query<{ id: string }>(
      `INSERT INTO public.customers (first_name, last_name, email, phone, address, address_2, city, state, pincode)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
       RETURNING id`,
      [
        firstName,
        lastName,
        input.customer.email.trim().toLowerCase(),
        input.customer.phone.trim(),
        input.customer.address.trim(),
        (input.customer.company || '').trim(),
        input.customer.city.trim(),
        input.customer.state.trim(),
        pincode,
      ]
    );
    const customerId = custRes.rows[0].id;

    const ordRes = await tx.query<{ id: string; order_number: string }>(
      `INSERT INTO public.orders (
        customer_id, quote_id, product_id, option_key, mode, payment_status, fulfilment_status,
        receipt_hash, product_paise, product_gst_paise, product_gst_bps,
        shipping_paise, shipping_gst_paise, shipping_gst_bps, total_paise,
        hsn, courier_id, courier_name, pickup_pincode, order_snapshot
      ) VALUES (
        $1, $2, $3, $4, $5, 'pending', 'awaiting',
        $6, $7, $8, $9,
        $10, $11, 1800, $12,
        $13, $14, $15, $16, $17::jsonb
      ) RETURNING id, order_number::text`,
      [
        customerId,
        quote.id,
        product.id,
        quote.option_key,
        cfg.mode,
        receiptHash,
        quote.product_paise,
        quote.product_gst_paise,
        quote.product_gst_bps,
        quote.rate_paise,
        quote.shipping_gst_paise,
        quote.total_paise,
        quote.hsn,
        quote.courier_id,
        quote.courier_name,
        quote.pickup_pincode,
        JSON.stringify(orderSnapshot),
      ]
    );
    const orderId = ordRes.rows[0].id;
    const orderNumber = ordRes.rows[0].order_number;

    await tx.query(
      `INSERT INTO public.order_items (
        order_id, product_id, product_title, option_key, option_label, quantity,
        unit_price_paise, total_price_paise, gst_bps, gst_paise, hsn,
        packed_weight_kg, length_cm, width_cm, height_cm
      ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15)`,
      [
        orderId,
        product.id,
        product.title,
        quote.option_key,
        quote.pack_snapshot?.option_label || quote.option_key,
        quote.pack_snapshot?.quantity || 1,
        quote.product_paise,
        quote.product_paise + quote.product_gst_paise,
        quote.product_gst_bps,
        quote.product_gst_paise,
        quote.hsn,
        quote.pack_snapshot?.weight_kg ?? null,
        quote.pack_snapshot?.length_cm ?? null,
        quote.pack_snapshot?.width_cm ?? null,
        quote.pack_snapshot?.height_cm ?? null,
      ]
    );

    return { orderId, orderNumber };
  });

  const authHeader =
    'Basic ' + Buffer.from(`${cfg.razorpay_key_id}:${cfg.razorpay_key_secret}`).toString('base64');

  const rzRes = await fetchFn('https://api.razorpay.com/v1/orders', {
    method: 'POST',
    headers: {
      Authorization: authHeader,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      amount: quote.total_paise,
      currency: 'INR',
      receipt: `RG-${orderRecord.orderNumber}`,
      notes: {
        order_id: orderRecord.orderId,
        product_slug: product.slug,
        variation: quote.option_key,
        pincode,
      },
    }),
    signal: AbortSignal.timeout(15000),
  });

  const rzBody = (await rzRes.json().catch(() => ({}))) as {
    id?: string;
    error?: { description?: string };
  };

  if (!rzRes.ok || !rzBody.id) {
    await query(
      `UPDATE public.orders SET payment_status = 'order_creation_failed' WHERE id = $1`,
      [orderRecord.orderId]
    );
    throw new AppError(
      rzBody.error?.description || 'Failed to create Razorpay payment order.',
      502,
      'razorpay_order_failed'
    );
  }

  await withTransaction(async (tx) => {
    await tx.query('UPDATE public.orders SET razorpay_order_id = $2 WHERE id = $1', [
      orderRecord.orderId,
      rzBody.id,
    ]);
    await tx.query(
      `INSERT INTO public.payment_transactions (order_id, razorpay_order_id, amount_paise, currency, status, raw_response)
       VALUES ($1, $2, $3, 'INR', 'created', $4::jsonb)`,
      [orderRecord.orderId, rzBody.id, quote.total_paise, JSON.stringify(rzBody)]
    );
    await tx.query(
      `INSERT INTO public.payment_events (order_id, event_key, event_type, source, signature_verified, payload)
       VALUES ($1, $2, 'order.created', 'checkout_verify', TRUE, $3::jsonb)`,
      [
        orderRecord.orderId,
        `created:${rzBody.id}`,
        JSON.stringify({ razorpay_order_id: rzBody.id, total_paise: quote.total_paise }),
      ]
    );
  });

  return {
    order_id: orderRecord.orderId,
    order_number: orderRecord.orderNumber,
    razorpay_order_id: rzBody.id,
    razorpay_key_id: cfg.razorpay_key_id,
    amount_paise: quote.total_paise,
    currency: 'INR',
    mode: cfg.mode,
    product_title: product.title,
    option_label: quote.pack_snapshot?.option_label || quote.option_key,
    prefill: {
      name: input.customer.name.trim(),
      email: input.customer.email.trim(),
      contact: input.customer.phone.trim(),
    },
  };
}

export interface VerifyPaymentInput {
  orderId: string;
  razorpayOrderId: string;
  razorpayPaymentId: string;
  razorpaySignature: string;
}

export async function verifyPayment(
  input: VerifyPaymentInput,
  fetchFn: typeof fetch = fetch
) {
  const cfg = await getPaymentShippingConfig();
  if (!cfg.razorpay_key_id || !cfg.razorpay_key_secret) {
    throw new AppError('Razorpay credentials are not configured.', 503, 'razorpay_not_configured');
  }

  const orderRes = await query<{
    id: string;
    order_number: string;
    razorpay_order_id: string | null;
    total_paise: string | number;
    payment_status: string;
  }>(
    'SELECT id, order_number::text, razorpay_order_id, total_paise, payment_status FROM public.orders WHERE id = $1',
    [input.orderId]
  );
  const order = orderRes.rows[0];
  if (!order) {
    throw new AppError('Order not found.', 404, 'order_not_found');
  }
  if (order.razorpay_order_id !== input.razorpayOrderId) {
    throw new AppError('Order mismatch.', 400, 'razorpay_order_mismatch');
  }

  if (order.payment_status === 'paid') {
    return {
      order_id: order.id,
      order_number: order.order_number,
      payment_status: 'paid',
      already_paid: true,
    };
  }

  const expectedSig = hmacSha256Hex(
    `${input.razorpayOrderId}|${input.razorpayPaymentId}`,
    cfg.razorpay_key_secret
  );
  if (!timingSafeEqualStr(expectedSig, input.razorpaySignature)) {
    throw new AppError('Invalid Razorpay payment signature.', 400, 'invalid_signature');
  }

  const authHeader =
    'Basic ' + Buffer.from(`${cfg.razorpay_key_id}:${cfg.razorpay_key_secret}`).toString('base64');

  const [payRes, rzOrdRes] = await Promise.all([
    fetchFn(`https://api.razorpay.com/v1/payments/${encodeURIComponent(input.razorpayPaymentId)}`, {
      headers: { Authorization: authHeader },
      signal: AbortSignal.timeout(15000),
    }),
    fetchFn(`https://api.razorpay.com/v1/orders/${encodeURIComponent(input.razorpayOrderId)}`, {
      headers: { Authorization: authHeader },
      signal: AbortSignal.timeout(15000),
    }),
  ]);

  const payData = (await payRes.json().catch(() => ({}))) as {
    id?: string;
    status?: string;
    amount?: number;
    currency?: string;
    order_id?: string;
    method?: string;
  };
  const rzOrdData = (await rzOrdRes.json().catch(() => ({}))) as {
    id?: string;
    status?: string;
    amount_paid?: number;
    currency?: string;
  };

  if (!payRes.ok || !rzOrdRes.ok) {
    throw new AppError('Failed to verify payment with Razorpay.', 502, 'razorpay_verify_failed');
  }

  const expectedAmount = Number(order.total_paise);
  const validPayment =
    payData.id === input.razorpayPaymentId &&
    payData.order_id === input.razorpayOrderId &&
    payData.status === 'captured' &&
    payData.currency === 'INR' &&
    Number(payData.amount) === expectedAmount &&
    rzOrdData.id === input.razorpayOrderId &&
    rzOrdData.status === 'paid' &&
    Number(rzOrdData.amount_paid) >= expectedAmount;

  if (!validPayment) {
    throw new AppError(
      'Payment verification failed: payment status or amount did not match order.',
      400,
      'payment_verification_mismatch'
    );
  }

  await withTransaction(async (tx) => {
    await tx.query(
      `UPDATE public.orders
       SET payment_status = 'paid', razorpay_payment_id = $2, paid_at = NOW()
       WHERE id = $1`,
      [order.id, input.razorpayPaymentId]
    );
    await tx.query(
      `INSERT INTO public.payment_transactions (
        order_id, razorpay_order_id, razorpay_payment_id, amount_paise, currency, status, raw_response
      ) VALUES ($1, $2, $3, $4, 'INR', 'captured', $5::jsonb)`,
      [
        order.id,
        input.razorpayOrderId,
        input.razorpayPaymentId,
        expectedAmount,
        JSON.stringify({ payment: payData, order: rzOrdData }),
      ]
    );
    await tx.query(
      `INSERT INTO public.payment_events (order_id, event_key, event_type, source, signature_verified, payload)
       VALUES ($1, $2, 'payment.verified', 'checkout_verify', TRUE, $3::jsonb)
       ON CONFLICT (event_key) DO NOTHING`,
      [
        order.id,
        `verify:${input.razorpayPaymentId}`,
        JSON.stringify({
          razorpay_payment_id: input.razorpayPaymentId,
          method: payData.method || '',
          amount_paise: expectedAmount,
        }),
      ]
    );
  });

  return {
    order_id: order.id,
    order_number: order.order_number,
    payment_status: 'paid',
    razorpay_payment_id: input.razorpayPaymentId,
  };
}

export async function handleRazorpayWebhook(
  rawBody: string,
  signature: string,
  eventIdHeader?: string
) {
  const cfg = await getPaymentShippingConfig();
  if (!cfg.razorpay_webhook_secret) {
    throw new AppError('Webhook secret is not configured.', 503, 'webhook_not_configured');
  }

  const expectedSig = hmacSha256Hex(rawBody, cfg.razorpay_webhook_secret);
  if (!timingSafeEqualStr(expectedSig, signature)) {
    throw new AppError('Invalid webhook signature.', 401, 'invalid_webhook_signature');
  }

  const payload = JSON.parse(rawBody) as {
    event?: string;
    payload?: {
      order?: { entity?: { id?: string; amount_paid?: number; currency?: string; status?: string } };
      payment?: { entity?: { id?: string; order_id?: string; amount?: number; currency?: string; status?: string } };
    };
  };

  const eventType = payload.event || 'unknown';
  const rzOrderId =
    payload.payload?.order?.entity?.id || payload.payload?.payment?.entity?.order_id || '';
  const rzPaymentId = payload.payload?.payment?.entity?.id || '';
  const eventKey = eventIdHeader || `wh:${eventType}:${rzOrderId}:${rzPaymentId || sha256Hex(rawBody).slice(0, 12)}`;

  const dup = await query<{ id: string }>(
    'SELECT id FROM public.payment_events WHERE event_key = $1 LIMIT 1',
    [eventKey]
  );
  if (dup.rows[0]) {
    return { received: true, duplicate: true };
  }

  let orderId: string | null = null;
  if (rzOrderId) {
    const ordRes = await query<{ id: string; total_paise: string | number; payment_status: string }>(
      'SELECT id, total_paise, payment_status FROM public.orders WHERE razorpay_order_id = $1 LIMIT 1',
      [rzOrderId]
    );
    const ord = ordRes.rows[0];
    if (ord) {
      orderId = ord.id;
      if (eventType === 'order.paid' && ord.payment_status !== 'paid') {
        const amountPaid = Number(
          payload.payload?.order?.entity?.amount_paid ??
            payload.payload?.payment?.entity?.amount ??
            0
        );
        if (amountPaid >= Number(ord.total_paise)) {
          await withTransaction(async (tx) => {
            await tx.query(
              `UPDATE public.orders
               SET payment_status = 'paid',
                   razorpay_payment_id = COALESCE(NULLIF($2, ''), razorpay_payment_id),
                   paid_at = COALESCE(paid_at, NOW())
               WHERE id = $1`,
              [ord.id, rzPaymentId]
            );
            await tx.query(
              `INSERT INTO public.payment_transactions (
                order_id, razorpay_order_id, razorpay_payment_id, amount_paise, currency, status, raw_response
              ) VALUES ($1, $2, $3, $4, 'INR', 'captured', $5::jsonb)`,
              [ord.id, rzOrderId, rzPaymentId || null, amountPaid, JSON.stringify(payload)]
            );
          });
        }
      }
    }
  }

  await query(
    `INSERT INTO public.payment_events (order_id, event_key, event_type, source, signature_verified, payload)
     VALUES ($1, $2, $3, 'webhook', TRUE, $4::jsonb)
     ON CONFLICT (event_key) DO NOTHING`,
    [orderId, eventKey, eventType, JSON.stringify(payload)]
  );

  return { received: true, duplicate: false, event: eventType };
}

export async function listOrders(params: {
  paymentStatus?: string;
  fulfilmentStatus?: string;
  search?: string;
  page?: number;
  limit?: number;
}) {
  const page = Math.max(1, params.page || 1);
  const limit = Math.min(100, Math.max(1, params.limit || 25));
  const offset = (page - 1) * limit;

  const conds: string[] = [];
  const vals: unknown[] = [];
  let idx = 1;

  if (params.paymentStatus) {
    conds.push(`o.payment_status = $${idx++}`);
    vals.push(params.paymentStatus);
  }
  if (params.fulfilmentStatus) {
    conds.push(`o.fulfilment_status = $${idx++}`);
    vals.push(params.fulfilmentStatus);
  }
  if (params.search && params.search.trim()) {
    const q = `%${params.search.trim()}%`;
    conds.push(
      `(o.order_number::text ILIKE $${idx} OR c.email ILIKE $${idx} OR c.first_name ILIKE $${idx} OR o.razorpay_order_id ILIKE $${idx} OR o.razorpay_payment_id ILIKE $${idx})`
    );
    vals.push(q);
    idx++;
  }

  const where = conds.length ? `WHERE ${conds.join(' AND ')}` : '';
  const countRes = await query<{ total: string }>(
    `SELECT COUNT(*)::text AS total
     FROM public.orders o
     JOIN public.customers c ON c.id = o.customer_id
     ${where}`,
    vals
  );

  const rowsRes = await query(
    `SELECT o.*, p.title AS product_title, p.slug AS product_slug,
            c.first_name, c.last_name, c.email AS customer_email, c.phone AS customer_phone,
            c.city AS shipping_city, c.state AS shipping_state, c.pincode AS shipping_pincode
     FROM public.orders o
     JOIN public.customers c ON c.id = o.customer_id
     LEFT JOIN public.products p ON p.id = o.product_id
     ${where}
     ORDER BY o.created_at DESC
     LIMIT $${idx++} OFFSET $${idx++}`,
    [...vals, limit, offset]
  );

  return {
    items: rowsRes.rows,
    total: Number(countRes.rows[0]?.total || 0),
    page,
    limit,
  };
}

export async function getOrderDetail(orderId: string) {
  const ordRes = await query(
    `SELECT o.*, p.title AS product_title, p.slug AS product_slug,
            c.first_name, c.last_name, c.email AS customer_email, c.phone AS customer_phone,
            c.address AS shipping_address, c.address_2, c.city AS shipping_city,
            c.state AS shipping_state, c.pincode AS shipping_pincode
     FROM public.orders o
     JOIN public.customers c ON c.id = o.customer_id
     LEFT JOIN public.products p ON p.id = o.product_id
     WHERE o.id = $1`,
    [orderId]
  );
  if (!ordRes.rows[0]) throw new AppError('Order not found.', 404, 'order_not_found');

  const [items, transactions, events] = await Promise.all([
    query('SELECT * FROM public.order_items WHERE order_id = $1', [orderId]),
    query(
      'SELECT * FROM public.payment_transactions WHERE order_id = $1 ORDER BY created_at DESC',
      [orderId]
    ),
    query('SELECT * FROM public.payment_events WHERE order_id = $1 ORDER BY created_at DESC', [
      orderId,
    ]),
  ]);

  return {
    order: ordRes.rows[0],
    items: items.rows,
    transactions: transactions.rows,
    events: events.rows,
  };
}

export async function updateOrderFulfilment(
  orderId: string,
  input: {
    fulfilment_status: 'awaiting' | 'fulfilled';
    manual_courier?: string;
    manual_tracking?: string;
  }
) {
  const res = await query(
    `UPDATE public.orders
     SET fulfilment_status = $2,
         manual_courier = COALESCE($3, manual_courier),
         manual_tracking = COALESCE($4, manual_tracking)
     WHERE id = $1
     RETURNING *`,
    [
      orderId,
      input.fulfilment_status,
      input.manual_courier ?? null,
      input.manual_tracking ?? null,
    ]
  );
  if (!res.rows[0]) throw new AppError('Order not found.', 404, 'order_not_found');
  return res.rows[0];
}

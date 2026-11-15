import nodemailer from 'nodemailer';
import { getEnv } from '../config/env.js';
import { query } from '../db/client.js';
import { sha256Hex } from '../utils/crypto.js';
import { AppError } from '../utils/errors.js';
import { getCompanyInfo } from './settingsService.js';

export interface CreateEnquiryInput {
  full_name: string;
  company_name?: string;
  phone: string;
  email: string;
  country?: string;
  enquiry_type: string;
  polymer_product: string;
  material_grade?: string;
  material_form?: string;
  estimated_quantity?: string;
  message: string;
  honeypot?: string;
  source_page?: string;
}

export async function createEnquiry(input: CreateEnquiryInput, ipAddress: string) {
  if (input.honeypot && input.honeypot.trim() !== '') {
    throw new AppError('Spam submission rejected.', 400, 'spam_detected');
  }

  const fullName = input.full_name.trim();
  const phone = input.phone.trim();
  const email = input.email.trim().toLowerCase();
  const enquiryType = input.enquiry_type.trim();
  const polymerProduct = input.polymer_product.trim();
  const message = input.message.trim();

  if (!fullName || !phone || !email || !enquiryType || !polymerProduct || !message) {
    throw new AppError('Please complete all required fields.', 400, 'missing_required_fields');
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    throw new AppError('Enter a valid business email address.', 400, 'invalid_email');
  }
  const phoneDigits = phone.replace(/[^0-9]/g, '');
  if (phoneDigits.length < 7) {
    throw new AppError('Enter a valid phone / WhatsApp number.', 400, 'invalid_phone');
  }

  const ipHash = sha256Hex(ipAddress).slice(0, 32);

  const res = await query<{ id: string; created_at: string }>(
    `INSERT INTO public.enquiries (
      name, company, email, phone, country, enquiry_type, material,
      grade, quantity, application, message, status, ip_hash
    ) VALUES (
      $1, $2, $3, $4, $5, $6, $7,
      $8, $9, $10, $11, 'new', $12
    ) RETURNING id, created_at`,
    [
      fullName,
      (input.company_name || '').trim() || '-',
      email,
      phone,
      (input.country || '').trim() || 'India',
      enquiryType,
      polymerProduct,
      (input.material_grade || '').trim(),
      (input.estimated_quantity || '').trim(),
      (input.material_form || '').trim(),
      message,
      ipHash,
    ]
  );

  const referenceCode = `RFQ-${res.rows[0].id.slice(0, 8).toUpperCase()}`;

  void sendEnquiryNotification(referenceCode, input).catch((err) => {
    console.warn('[enquiryService] Email notification skipped/failed:', err?.message || err);
  });

  return {
    id: res.rows[0].id,
    reference_code: referenceCode,
    created_at: res.rows[0].created_at,
  };
}

async function sendEnquiryNotification(referenceCode: string, input: CreateEnquiryInput) {
  const env = getEnv();
  if (!env.smtpHost || !env.smtpUser || !env.smtpPass) return;

  const company = await getCompanyInfo();
  const recipient = process.env.ENQUIRY_NOTIFY_EMAIL || company.email;
  if (!recipient) return;

  const transporter = nodemailer.createTransport({
    host: env.smtpHost,
    port: env.smtpPort,
    secure: env.smtpSecure,
    auth: { user: env.smtpUser, pass: env.smtpPass },
  });

  await transporter.sendMail({
    from: env.smtpFrom,
    to: recipient,
    replyTo: input.email,
    subject: `[${referenceCode}] New ${input.enquiry_type} Enquiry — ${input.polymer_product}`,
    text: [
      `Reference: ${referenceCode}`,
      `Name: ${input.full_name}`,
      `Company: ${input.company_name || '-'}`,
      `Phone / WhatsApp: ${input.phone}`,
      `Email: ${input.email}`,
      `Country: ${input.country || '-'}`,
      `Enquiry Type: ${input.enquiry_type}`,
      `Product / Polymer: ${input.polymer_product}`,
      `Material Grade: ${input.material_grade || '-'}`,
      `Material Form: ${input.material_form || '-'}`,
      `Estimated Quantity: ${input.estimated_quantity || '-'}`,
      '',
      'Message:',
      input.message,
    ].join('\n'),
  });
}

export async function listEnquiries(params: {
  status?: string;
  enquiryType?: string;
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

  if (params.status) {
    conds.push(`status = $${idx++}`);
    vals.push(params.status);
  }
  if (params.enquiryType) {
    conds.push(`enquiry_type = $${idx++}`);
    vals.push(params.enquiryType);
  }
  if (params.search && params.search.trim()) {
    const q = `%${params.search.trim()}%`;
    conds.push(
      `(name ILIKE $${idx} OR company ILIKE $${idx} OR email ILIKE $${idx} OR material ILIKE $${idx})`
    );
    vals.push(q);
    idx++;
  }

  const where = conds.length ? `WHERE ${conds.join(' AND ')}` : '';
  const countRes = await query<{ total: string }>(
    `SELECT COUNT(*)::text AS total FROM public.enquiries ${where}`,
    vals
  );

  const listRes = await query(
    `SELECT * FROM public.enquiries ${where}
     ORDER BY created_at DESC
     LIMIT $${idx++} OFFSET $${idx++}`,
    [...vals, limit, offset]
  );

  return {
    items: listRes.rows,
    total: Number(countRes.rows[0]?.total || 0),
    page,
    limit,
  };
}

export async function updateEnquiry(
  id: string,
  input: {
    status?: 'new' | 'in_review' | 'quoted' | 'closed' | 'spam';
    internal_notes?: string;
    assigned_to?: string | null;
  }
) {
  const res = await query(
    `UPDATE public.enquiries
     SET status = COALESCE($2, status),
         internal_notes = COALESCE($3, internal_notes),
         assigned_to = COALESCE($4, assigned_to)
     WHERE id = $1
     RETURNING *`,
    [id, input.status ?? null, input.internal_notes ?? null, input.assigned_to ?? null]
  );
  if (!res.rows[0]) throw new AppError('Enquiry not found.', 404, 'not_found');
  return res.rows[0];
}

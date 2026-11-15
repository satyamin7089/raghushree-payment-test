import fs from 'node:fs/promises';
import path from 'node:path';
import crypto from 'node:crypto';
import { getEnv } from '../config/env.js';
import { query } from '../db/client.js';
import { getSupabaseAdmin } from '../db/supabase.js';
import { AppError } from '../utils/errors.js';

const ALLOWED_MIME_TYPES: Record<string, string> = {
  'image/jpeg': '.jpg',
  'image/png': '.png',
  'image/webp': '.webp',
  'image/svg+xml': '.svg',
  'application/pdf': '.pdf',
};

export async function uploadMediaBuffer(input: {
  buffer: Buffer;
  originalName: string;
  mimeType: string;
  altText?: string;
  uploadedBy?: string | null;
}) {
  const ext = ALLOWED_MIME_TYPES[input.mimeType];
  if (!ext) {
    throw new AppError(
      'Unsupported file type. Allowed: JPEG, PNG, WebP, SVG, PDF.',
      400,
      'unsupported_media_type'
    );
  }
  if (input.buffer.length === 0 || input.buffer.length > 10 * 1024 * 1024) {
    throw new AppError('File size must be between 1 byte and 10 MB.', 400, 'invalid_file_size');
  }

  const env = getEnv();
  const safeBase = path
    .basename(input.originalName, path.extname(input.originalName))
    .toLowerCase()
    .replace(/[^a-z0-9_-]+/g, '-')
    .slice(0, 48);
  const uniqueKey = `products/${Date.now()}-${crypto.randomBytes(6).toString('hex')}-${
    safeBase || 'asset'
  }${ext}`;

  let publicUrl = '';
  let storageBucket = env.supabaseStorageBucket;

  const supabase = getSupabaseAdmin();
  if (supabase) {
    const { error } = await supabase.storage
      .from(storageBucket)
      .upload(uniqueKey, input.buffer, {
        contentType: input.mimeType,
        upsert: false,
      });
    if (error) {
      throw new AppError(`Supabase Storage upload failed: ${error.message}`, 502, 'storage_failed');
    }
    const { data } = supabase.storage.from(storageBucket).getPublicUrl(uniqueKey);
    publicUrl = data.publicUrl;
  } else {
    storageBucket = 'local';
    const uploadDir = path.resolve(process.cwd(), '.uploads');
    const targetPath = path.join(uploadDir, uniqueKey);
    await fs.mkdir(path.dirname(targetPath), { recursive: true });
    await fs.writeFile(targetPath, input.buffer);
    publicUrl = `/uploads/${uniqueKey}`;
  }

  const res = await query(
    `INSERT INTO public.media_assets (
      storage_bucket, storage_path, public_url, file_name, mime_type, file_size_bytes, alt_text, uploaded_by
    ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
    RETURNING *`,
    [
      storageBucket,
      uniqueKey,
      publicUrl,
      input.originalName || safeBase || 'asset',
      input.mimeType,
      input.buffer.length,
      input.altText || safeBase || 'Product media',
      input.uploadedBy || null,
    ]
  );

  return res.rows[0];
}

export async function listMediaAssets(limit = 50, offset = 0) {
  const res = await query(
    `SELECT * FROM public.media_assets ORDER BY created_at DESC LIMIT $1 OFFSET $2`,
    [Math.min(100, Math.max(1, limit)), Math.max(0, offset)]
  );
  return res.rows;
}

export async function deleteMediaAsset(id: string) {
  const existing = await query<{ storage_bucket: string; storage_path: string }>(
    'SELECT storage_bucket, storage_path FROM public.media_assets WHERE id = $1',
    [id]
  );
  const row = existing.rows[0];
  if (!row) throw new AppError('Media asset not found.', 404, 'not_found');

  if (row.storage_bucket === 'local') {
    const uploadDir = path.resolve(process.cwd(), '.uploads');
    const targetPath = path.join(uploadDir, row.storage_path);
    await fs.unlink(targetPath).catch(() => undefined);
  } else {
    const supabase = getSupabaseAdmin();
    if (supabase) {
      await supabase.storage.from(row.storage_bucket).remove([row.storage_path]);
    }
  }

  await query('DELETE FROM public.media_assets WHERE id = $1', [id]);
  return { deleted: true };
}

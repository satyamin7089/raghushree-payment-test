import type { Request, Response, NextFunction } from 'express';
import { query } from '../db/client.js';
import { sha256Hex } from '../utils/crypto.js';
import { AppError } from '../utils/errors.js';

export function getClientIp(req: Request): string {
  const forwarded = req.headers['x-forwarded-for'];
  if (typeof forwarded === 'string' && forwarded.length > 0) {
    return forwarded.split(',')[0].trim();
  }
  return req.ip || req.socket.remoteAddress || 'unknown';
}

export function rateLimitMiddleware(options: {
  namespace: string;
  maxHits: number;
  windowSeconds: number;
  message: string;
}) {
  return async (req: Request, _res: Response, next: NextFunction) => {
    try {
      const ip = getClientIp(req);
      const bucketKey = `${options.namespace}:${sha256Hex(ip).slice(0, 32)}`;
      const now = new Date();
      const resetAt = new Date(now.getTime() + options.windowSeconds * 1000);

      const existing = await query<{ hit_count: number; reset_at: string }>(
        'SELECT hit_count, reset_at FROM public.rate_limits WHERE bucket_key = $1',
        [bucketKey]
      );

      if (existing.rows.length === 0 || new Date(existing.rows[0].reset_at) <= now) {
        await query(
          `INSERT INTO public.rate_limits (bucket_key, hit_count, reset_at)
           VALUES ($1, 1, $2)
           ON CONFLICT (bucket_key) DO UPDATE SET hit_count = 1, reset_at = EXCLUDED.reset_at`,
          [bucketKey, resetAt.toISOString()]
        );
        return next();
      }

      if (existing.rows[0].hit_count >= options.maxHits) {
        return next(new AppError(options.message, 429, 'rate_limited'));
      }

      await query(
        'UPDATE public.rate_limits SET hit_count = hit_count + 1 WHERE bucket_key = $1',
        [bucketKey]
      );
      return next();
    } catch (err) {
      return next(err);
    }
  };
}

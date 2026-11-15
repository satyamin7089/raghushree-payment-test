import type { Request, Response, NextFunction } from 'express';
import { getEnv } from '../config/env.js';
import { query } from '../db/client.js';
import { getSupabaseAdmin } from '../db/supabase.js';
import { verifySessionJwt } from '../utils/crypto.js';
import { AppError } from '../utils/errors.js';

export interface AuthenticatedUser {
  id: string;
  email: string;
  fullName: string;
  role: 'administrator' | 'manager' | 'staff' | 'customer';
  permissions: string[];
}

declare global {
  namespace Express {
    interface Request {
      authUser?: AuthenticatedUser;
    }
  }
}

export const SESSION_COOKIE_NAME = 'rg_session';

export async function loadActiveUserById(userId: string): Promise<AuthenticatedUser | null> {
  const res = await query<{
    id: string;
    email: string;
    full_name: string;
    role_slug: AuthenticatedUser['role'];
    custom_permissions: string[];
    role_permissions: string[];
    is_active: boolean;
  }>(
    `SELECT u.id, u.email, u.full_name, u.role_slug, u.custom_permissions, u.is_active,
            r.permissions AS role_permissions
     FROM public.user_profiles u
     JOIN public.roles r ON r.slug = u.role_slug
     WHERE u.id = $1`,
    [userId]
  );
  const row = res.rows[0];
  if (!row || !row.is_active) return null;
  const merged = new Set<string>([
    ...(Array.isArray(row.role_permissions) ? row.role_permissions : []),
    ...(Array.isArray(row.custom_permissions) ? row.custom_permissions : []),
  ]);
  return {
    id: row.id,
    email: row.email,
    fullName: row.full_name,
    role: row.role_slug,
    permissions: Array.from(merged),
  };
}

export async function loadActiveUserByAuthId(authUserId: string): Promise<AuthenticatedUser | null> {
  const res = await query<{ id: string }>(
    'SELECT id FROM public.user_profiles WHERE auth_user_id = $1 AND is_active = TRUE LIMIT 1',
    [authUserId]
  );
  if (!res.rows[0]) return null;
  return loadActiveUserById(res.rows[0].id);
}

export function extractToken(req: Request): string | null {
  const authHeader = req.headers.authorization;
  if (typeof authHeader === 'string' && authHeader.startsWith('Bearer ')) {
    return authHeader.slice(7).trim();
  }
  const cookieToken = req.cookies?.[SESSION_COOKIE_NAME];
  if (typeof cookieToken === 'string' && cookieToken.length > 0) {
    return cookieToken;
  }
  return null;
}

export async function authenticate(req: Request, _res: Response, next: NextFunction) {
  try {
    const token = extractToken(req);
    if (!token) {
      return next(new AppError('Authentication required.', 401, 'unauthorized'));
    }

    const env = getEnv();
    // 1. Try local/API-issued signed session JWT first
    const localPayload = verifySessionJwt(token, env.authJwtSecret);
    if (localPayload) {
      const user = await loadActiveUserById(localPayload.sub);
      if (!user) {
        return next(new AppError('Account is inactive or not found.', 401, 'unauthorized'));
      }
      req.authUser = user;
      return next();
    }

    // 2. If Supabase is configured, also accept Supabase Auth access tokens
    const supabase = getSupabaseAdmin();
    if (supabase) {
      const { data, error } = await supabase.auth.getUser(token);
      if (!error && data?.user) {
        const user = await loadActiveUserByAuthId(data.user.id);
        if (user) {
          req.authUser = user;
          return next();
        }
      }
    }

    return next(new AppError('Invalid or expired session.', 401, 'unauthorized'));
  } catch (err) {
    return next(err);
  }
}

export function requireRole(...allowedRoles: Array<AuthenticatedUser['role']>) {
  return (req: Request, _res: Response, next: NextFunction) => {
    if (!req.authUser) {
      return next(new AppError('Authentication required.', 401, 'unauthorized'));
    }
    if (!allowedRoles.includes(req.authUser.role)) {
      return next(new AppError('You do not have permission to perform this action.', 403, 'forbidden'));
    }
    return next();
  };
}

export function hasPermission(user: AuthenticatedUser, permission: string): boolean {
  if (user.role === 'administrator' || user.permissions.includes('*')) return true;
  return user.permissions.includes(permission);
}

export function requirePermission(permission: string) {
  return (req: Request, _res: Response, next: NextFunction) => {
    if (!req.authUser) {
      return next(new AppError('Authentication required.', 401, 'unauthorized'));
    }
    if (!hasPermission(req.authUser, permission)) {
      return next(
        new AppError('Insufficient permissions for this resource.', 403, 'forbidden')
      );
    }
    return next();
  };
}

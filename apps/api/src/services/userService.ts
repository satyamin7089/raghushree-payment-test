import { getEnv } from '../config/env.js';
import { query } from '../db/client.js';
import { getSupabaseAdmin, getSupabasePublic } from '../db/supabase.js';
import { hashPassword, signSessionJwt, verifyPassword } from '../utils/crypto.js';
import { AppError } from '../utils/errors.js';
import { loadActiveUserByAuthId, loadActiveUserById } from '../middleware/auth.js';

export async function ensureBootstrapAdmin() {
  const env = getEnv();
  const countRes = await query<{ cnt: string }>('SELECT COUNT(*)::text AS cnt FROM public.user_profiles');
  if (Number(countRes.rows[0]?.cnt || 0) > 0) return;

  const email = env.bootstrapAdminEmail;
  const password = env.bootstrapAdminPassword;
  const name = env.bootstrapAdminName;
  if (!email || !password) return;

  const hash = hashPassword(password);
  await query(
    `INSERT INTO public.user_profiles (email, full_name, role_slug, password_hash, is_active)
     VALUES ($1, $2, 'administrator', $3, TRUE)
     ON CONFLICT (email) DO NOTHING`,
    [email.toLowerCase(), name, hash]
  );
}

export async function loginWithPassword(emailInput: string, password: string) {
  const email = emailInput.trim().toLowerCase();
  if (!email || !password) {
    throw new AppError('Email and password are required.', 400, 'invalid_credentials');
  }

  const env = getEnv();
  const res = await query<{
    id: string;
    auth_user_id: string | null;
    email: string;
    full_name: string;
    role_slug: 'administrator' | 'manager' | 'staff' | 'customer';
    password_hash: string | null;
    is_active: boolean;
  }>(
    'SELECT id, auth_user_id, email, full_name, role_slug, password_hash, is_active FROM public.user_profiles WHERE LOWER(email) = $1 LIMIT 1',
    [email]
  );
  const profile = res.rows[0];

  // If Supabase Auth is configured and profile uses Supabase Auth
  const supabasePublic = getSupabasePublic();
  if (supabasePublic && (!profile || !profile.password_hash)) {
    const { data, error } = await supabasePublic.auth.signInWithPassword({ email, password });
    if (error || !data.user || !data.session) {
      throw new AppError('Invalid email or password.', 401, 'invalid_credentials');
    }
    const active = await loadActiveUserByAuthId(data.user.id);
    if (!active) {
      throw new AppError('User account is inactive or unauthorized.', 403, 'account_inactive');
    }
    await query('UPDATE public.user_profiles SET last_login_at = NOW() WHERE id = $1', [active.id]);
    const token = signSessionJwt(
      { sub: active.id, role: active.role, email: active.email },
      env.authJwtSecret,
      60 * 60 * 12
    );
    return { token, user: active };
  }

  if (!profile || !profile.is_active || !profile.password_hash) {
    throw new AppError('Invalid email or password.', 401, 'invalid_credentials');
  }

  const valid = verifyPassword(password, profile.password_hash);
  if (!valid) {
    throw new AppError('Invalid email or password.', 401, 'invalid_credentials');
  }

  await query('UPDATE public.user_profiles SET last_login_at = NOW() WHERE id = $1', [profile.id]);
  const user = await loadActiveUserById(profile.id);
  if (!user) {
    throw new AppError('Account is inactive.', 403, 'account_inactive');
  }

  const token = signSessionJwt(
    { sub: user.id, role: user.role, email: user.email },
    env.authJwtSecret,
    60 * 60 * 12
  );
  return { token, user };
}

export async function listUsers() {
  const res = await query(
    `SELECT u.id, u.email, u.full_name, u.role_slug, u.is_active, u.custom_permissions,
            u.last_login_at, u.created_at, r.name AS role_name
     FROM public.user_profiles u
     JOIN public.roles r ON r.slug = u.role_slug
     ORDER BY u.created_at ASC`
  );
  return res.rows;
}

export async function listRoles() {
  const res = await query('SELECT slug, name, description, permissions FROM public.roles ORDER BY slug');
  return res.rows;
}

export async function createUser(input: {
  email: string;
  full_name: string;
  password: string;
  role_slug: 'administrator' | 'manager' | 'staff' | 'customer';
  is_active?: boolean;
}) {
  const email = input.email.trim().toLowerCase();
  if (!email || !input.password || input.password.length < 8) {
    throw new AppError('Valid email and password (min 8 chars) are required.', 400, 'invalid_user');
  }

  let authUserId: string | null = null;
  const supabaseAdmin = getSupabaseAdmin();
  if (supabaseAdmin) {
    const created = await supabaseAdmin.auth.admin.createUser({
      email,
      password: input.password,
      email_confirm: true,
      user_metadata: { full_name: input.full_name, role: input.role_slug },
    });
    if (created.error) {
      throw new AppError(created.error.message, 400, 'supabase_user_create_failed');
    }
    authUserId = created.data.user.id;
  }

  const passwordHash = await hashPassword(input.password);
  const res = await query(
    `INSERT INTO public.user_profiles (auth_user_id, email, full_name, role_slug, password_hash, is_active)
     VALUES ($1, $2, $3, $4, $5, $6)
     RETURNING id, email, full_name, role_slug, is_active, created_at`,
    [
      authUserId,
      email,
      input.full_name.trim(),
      input.role_slug,
      passwordHash,
      input.is_active !== false,
    ]
  );
  return res.rows[0];
}

export async function updateUser(
  userId: string,
  input: {
    full_name?: string;
    role_slug?: 'administrator' | 'manager' | 'staff' | 'customer';
    is_active?: boolean;
    password?: string;
  }
) {
  let passwordHash: string | null = null;
  if (input.password) {
    if (input.password.length < 8) {
      throw new AppError('Password must be at least 8 characters.', 400, 'weak_password');
    }
    passwordHash = await hashPassword(input.password);
  }

  const res = await query(
    `UPDATE public.user_profiles
     SET full_name = COALESCE($2, full_name),
         role_slug = COALESCE($3, role_slug),
         is_active = COALESCE($4, is_active),
         password_hash = COALESCE($5, password_hash)
     WHERE id = $1
     RETURNING id, email, full_name, role_slug, is_active, updated_at`,
    [
      userId,
      input.full_name ?? null,
      input.role_slug ?? null,
      typeof input.is_active === 'boolean' ? input.is_active : null,
      passwordHash,
    ]
  );
  if (!res.rows[0]) throw new AppError('User not found.', 404, 'user_not_found');
  return res.rows[0];
}

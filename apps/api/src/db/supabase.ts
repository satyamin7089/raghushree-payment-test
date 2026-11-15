import { createClient, type SupabaseClient } from '@supabase/supabase-js';
import { getEnv } from '../config/env.js';

let adminClient: SupabaseClient | null = null;
let publicClient: SupabaseClient | null = null;

export function isSupabaseConfigured(): boolean {
  const env = getEnv();
  return Boolean(env.supabaseUrl && env.supabaseServiceRoleKey);
}

export function getSupabaseAdmin(): SupabaseClient | null {
  if (!isSupabaseConfigured()) return null;
  if (!adminClient) {
    const env = getEnv();
    adminClient = createClient(env.supabaseUrl, env.supabaseServiceRoleKey, {
      auth: { persistSession: false, autoRefreshToken: false },
    });
  }
  return adminClient;
}

export function getSupabasePublic(): SupabaseClient | null {
  const env = getEnv();
  if (!env.supabaseUrl || !env.supabaseAnonKey) return null;
  if (!publicClient) {
    publicClient = createClient(env.supabaseUrl, env.supabaseAnonKey, {
      auth: { persistSession: false, autoRefreshToken: false },
    });
  }
  return publicClient;
}

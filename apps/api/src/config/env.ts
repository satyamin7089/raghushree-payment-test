import dotenv from 'dotenv';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
export const REPO_ROOT = path.resolve(__dirname, '../../../..');

dotenv.config({ path: path.join(REPO_ROOT, '.env') });

export interface EnvConfig {
  nodeEnv: 'development' | 'test' | 'production';
  port: number;
  appOrigin: string;
  publicSiteUrl: string;
  dbDriver: 'pg' | 'pglite';
  pgliteDataDir: string;
  databaseUrl: string;
  supabaseUrl: string;
  supabaseAnonKey: string;
  supabaseServiceRoleKey: string;
  supabaseStorageBucket: string;
  authJwtSecret: string;
  razorpayMode: 'test' | 'live';
  razorpayKeyId: string;
  razorpayKeySecret: string;
  razorpayWebhookSecret: string;
  shiprocketEmail: string;
  shiprocketPassword: string;
  shiprocketPickupPincode: string;
  smtpHost: string;
  smtpPort: number;
  smtpSecure: boolean;
  smtpUser: string;
  smtpPass: string;
  smtpFrom: string;
  bootstrapAdminEmail: string;
  bootstrapAdminPassword: string;
  bootstrapAdminName: string;
}

export function getEnv(): EnvConfig {
  const nodeEnvRaw = process.env.NODE_ENV || 'development';
  const nodeEnv =
    nodeEnvRaw === 'production' || nodeEnvRaw === 'test' ? nodeEnvRaw : 'development';
  const dbDriverRaw = process.env.DB_DRIVER || (process.env.DATABASE_URL ? 'pg' : 'pglite');
  const dbDriver = dbDriverRaw === 'pg' ? 'pg' : 'pglite';

  if (nodeEnv === 'production' && !process.env.AUTH_JWT_SECRET) {
    throw new Error('AUTH_JWT_SECRET must be set in production.');
  }

  return {
    nodeEnv,
    port: Number(process.env.PORT || 4000),
    appOrigin: process.env.APP_ORIGIN || 'http://localhost:5173',
    publicSiteUrl: (process.env.PUBLIC_SITE_URL || 'http://localhost:5173').replace(/\/$/, ''),
    dbDriver,
    pgliteDataDir: process.env.PGLITE_DATA_DIR || path.join(REPO_ROOT, '.pglite-data'),
    databaseUrl: process.env.DATABASE_URL || '',
    supabaseUrl: (process.env.SUPABASE_URL || '').replace(/\/$/, ''),
    supabaseAnonKey: process.env.SUPABASE_ANON_KEY || '',
    supabaseServiceRoleKey: process.env.SUPABASE_SERVICE_ROLE_KEY || '',
    supabaseStorageBucket: process.env.SUPABASE_STORAGE_BUCKET || 'product-media',
    authJwtSecret:
      process.env.AUTH_JWT_SECRET || 'raghushree-dev-only-jwt-secret-change-in-production',
    razorpayMode: process.env.RAZORPAY_MODE === 'live' ? 'live' : 'test',
    razorpayKeyId: process.env.RAZORPAY_KEY_ID || '',
    razorpayKeySecret: process.env.RAZORPAY_KEY_SECRET || '',
    razorpayWebhookSecret: process.env.RAZORPAY_WEBHOOK_SECRET || '',
    shiprocketEmail: process.env.SHIPROCKET_EMAIL || '',
    shiprocketPassword: process.env.SHIPROCKET_PASSWORD || '',
    shiprocketPickupPincode: process.env.SHIPROCKET_PICKUP_PINCODE || '208001',
    smtpHost: process.env.SMTP_HOST || '',
    smtpPort: Number(process.env.SMTP_PORT || 587),
    smtpSecure: process.env.SMTP_SECURE === 'true',
    smtpUser: process.env.SMTP_USER || '',
    smtpPass: process.env.SMTP_PASS || '',
    smtpFrom: process.env.SMTP_FROM || 'no-reply@raghushree.com',
    bootstrapAdminEmail: process.env.BOOTSTRAP_ADMIN_EMAIL || 'admin@raghushree.com',
    bootstrapAdminPassword: process.env.BOOTSTRAP_ADMIN_PASSWORD || 'RaghushreeAdmin#2026',
    bootstrapAdminName: process.env.BOOTSTRAP_ADMIN_NAME || 'Raghushree Administrator',
  };
}

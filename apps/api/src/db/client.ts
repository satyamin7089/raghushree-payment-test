import fs from 'node:fs/promises';
import path from 'node:path';
import { Pool, type PoolClient } from 'pg';
import { PGlite } from '@electric-sql/pglite';
import { getEnv, REPO_ROOT } from '../config/env.js';

export interface QueryResult<T = Record<string, unknown>> {
  rows: T[];
  rowCount: number;
}

export interface DbExecutor {
  query<T = Record<string, unknown>>(sql: string, params?: unknown[]): Promise<QueryResult<T>>;
}

class PgClientAdapter implements DbExecutor {
  constructor(private readonly client: Pool | PoolClient) {}

  async query<T = Record<string, unknown>>(
    sql: string,
    params: unknown[] = []
  ): Promise<QueryResult<T>> {
    const res = await this.client.query(sql, params);
    return {
      rows: res.rows as T[],
      rowCount: res.rowCount ?? res.rows.length,
    };
  }
}

class PGliteAdapter implements DbExecutor {
  constructor(private readonly db: PGlite) {}

  async query<T = Record<string, unknown>>(
    sql: string,
    params: unknown[] = []
  ): Promise<QueryResult<T>> {
    const res = await this.db.query<T>(sql, params);
    return {
      rows: res.rows,
      rowCount: res.affectedRows ?? res.rows.length,
    };
  }
}

let pgPool: Pool | null = null;
let pgliteInstance: PGlite | null = null;
let initPromise: Promise<void> | null = null;

export async function initDatabase(overrideDataDir?: string): Promise<void> {
  if (initPromise) return initPromise;
  initPromise = (async () => {
    const env = getEnv();
    if (env.dbDriver === 'pg') {
      if (!env.databaseUrl) {
        throw new Error('DATABASE_URL is required when DB_DRIVER=pg');
      }
      pgPool = new Pool({
        connectionString: env.databaseUrl,
        ssl: env.databaseUrl.includes('localhost') || env.databaseUrl.includes('127.0.0.1')
          ? false
          : { rejectUnauthorized: false },
      });
      await pgPool.query('SELECT 1');
    } else {
      const dataDir = overrideDataDir || env.pgliteDataDir;
      if (dataDir !== 'memory://') {
        await fs.mkdir(dataDir, { recursive: true });
      }
      pgliteInstance = dataDir === 'memory://' ? new PGlite() : new PGlite(dataDir);
      await pgliteInstance.waitReady;
    }
  })();
  return initPromise;
}

export async function closeDatabase(): Promise<void> {
  if (pgPool) {
    await pgPool.end();
    pgPool = null;
  }
  if (pgliteInstance) {
    await pgliteInstance.close();
    pgliteInstance = null;
  }
  initPromise = null;
}

export function getDb(): DbExecutor {
  if (pgPool) return new PgClientAdapter(pgPool);
  if (pgliteInstance) return new PGliteAdapter(pgliteInstance);
  throw new Error('Database not initialized. Call initDatabase() first.');
}

export async function query<T = Record<string, unknown>>(
  sql: string,
  params: unknown[] = []
): Promise<QueryResult<T>> {
  return getDb().query<T>(sql, params);
}

export async function withTransaction<T>(fn: (tx: DbExecutor) => Promise<T>): Promise<T> {
  if (pgPool) {
    const client = await pgPool.connect();
    try {
      await client.query('BEGIN');
      const result = await fn(new PgClientAdapter(client));
      await client.query('COMMIT');
      return result;
    } catch (err) {
      await client.query('ROLLBACK');
      throw err;
    } finally {
      client.release();
    }
  }
  if (pgliteInstance) {
    return pgliteInstance.transaction(async (tx) => {
      return fn({
        async query<R = Record<string, unknown>>(sql: string, params: unknown[] = []) {
          const res = await tx.query<R>(sql, params);
          return {
            rows: res.rows,
            rowCount: res.affectedRows ?? res.rows.length,
          };
        },
      });
    });
  }
  throw new Error('Database not initialized.');
}

export async function runMigrations(): Promise<void> {
  const migrationsDir = path.join(REPO_ROOT, 'supabase', 'migrations');
  const env = getEnv();

  await query(`
    CREATE TABLE IF NOT EXISTS public._app_migrations (
      filename TEXT PRIMARY KEY,
      applied_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
    )
  `);

  const appliedRes = await query<{ filename: string }>('SELECT filename FROM public._app_migrations');
  const appliedSet = new Set(appliedRes.rows.map((r) => r.filename));

  const files = ['001_schema.sql', '003_seed_defaults.sql'];
  if (env.dbDriver === 'pg' && env.supabaseUrl) {
    files.splice(1, 0, '002_rls_policies.sql');
  }

  for (const file of files) {
    if (appliedSet.has(file)) continue;
    if (file === '001_schema.sql') {
      const existingTbl = await query<{ tbl: string | null }>(
        "SELECT to_regclass('public.user_profiles') AS tbl"
      );
      if (existingTbl.rows[0]?.tbl) {
        await query(
          'INSERT INTO public._app_migrations (filename) VALUES ($1) ON CONFLICT DO NOTHING',
          [file]
        );
        continue;
      }
    }
    const sql = await fs.readFile(path.join(migrationsDir, file), 'utf8');
    if (pgliteInstance) {
      // PGlite supports multi-statement exec; pgcrypto extension may not be bundled in WASM PGlite,
      // so strip CREATE EXTENSION pgcrypto if running in PGlite (gen_random_uuid() is built into PG 13+ core).
      const sanitized = sql.replace(/CREATE EXTENSION IF NOT EXISTS pgcrypto;/gi, '');
      await pgliteInstance.exec(sanitized);
    } else if (pgPool) {
      await pgPool.query(sql);
    }
    await query('INSERT INTO public._app_migrations (filename) VALUES ($1) ON CONFLICT DO NOTHING', [
      file,
    ]);
  }
}

import { getEnv } from './config/env.js';
import { initDatabase, runMigrations } from './db/client.js';
import { seedInitialCatalogIfEmpty } from './services/catalogService.js';
import { ensureBootstrapAdmin } from './services/userService.js';
import { createApp } from './app.js';

export async function bootstrapBackend() {
  await initDatabase();
  await runMigrations();
  await ensureBootstrapAdmin();
  await seedInitialCatalogIfEmpty();
  return createApp();
}

async function main() {
  const env = getEnv();
  const host = process.env.HOST || '0.0.0.0';
  const app = await bootstrapBackend();
  app.listen(env.port, host, () => {
    console.info(
      `[raghushree-api] Listening on http://${host}:${env.port} (db=${env.dbDriver})`
    );
  });
}

// Start server when executed directly
const isMain =
  import.meta.url === `file://${process.argv[1]}` ||
  process.argv[1]?.endsWith('server.ts') ||
  process.argv[1]?.endsWith('server.js');

if (isMain) {
  main().catch((err) => {
    console.error('[raghushree-api] Fatal startup error:', err);
    process.exit(1);
  });
}

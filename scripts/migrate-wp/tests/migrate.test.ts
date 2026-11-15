import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import { after, describe, it } from 'node:test';
import { closeDatabase } from '../../../apps/api/src/db/client.js';
import { parsePhpSerialized, parseWxrXml } from '../src/parser.js';
import { runMigrationCli } from '../src/index.js';

describe('WordPress to PostgreSQL Migration Tooling', () => {
  after(async () => {
    await closeDatabase();
  });

  it('parses PHP serialized arrays and strings accurately', () => {
    const raw = 'a:2:{i:0;s:13:"Melt flow 12g";s:4:"code";s:4:"PP-H";}';
    const parsed = parsePhpSerialized(raw) as Record<string, string>;
    assert.equal(parsed['0'], 'Melt flow 12g');
    assert.equal(parsed['code'], 'PP-H');
  });

  it('parses WXR XML polymer_product items, taxonomies, and postmeta', () => {
    const sampleWxr = `<?xml version="1.0" encoding="UTF-8" ?>
      <rss version="2.0" xmlns:wp="http://wordpress.org/export/1.2/" xmlns:content="http://purl.org/rss/1.0/modules/content/">
        <channel>
          <item>
            <title>Custom HDPE Pipe Compound</title>
            <wp:post_id>901</wp:post_id>
            <wp:post_name>custom-hdpe-pipe-compound</wp:post_name>
            <wp:post_type>polymer_product</wp:post_type>
            <wp:status>publish</wp:status>
            <content:encoded><![CDATA[<p>High-density PE100 compound.</p>]]></content:encoded>
            <category domain="polymer_category" nicename="hdpe"><![CDATA[HDPE]]></category>
            <wp:postmeta>
              <wp:meta_key>_raghushree_polymer_code</wp:meta_key>
              <wp:meta_value><![CDATA[HDPE-100]]></wp:meta_value>
            </wp:postmeta>
          </item>
        </channel>
      </rss>`;

    const res = parseWxrXml(sampleWxr);
    assert.equal(res.products.length, 1);
    assert.equal(res.products[0].slug, 'custom-hdpe-pipe-compound');
    assert.deepEqual(res.products[0].categories, ['hdpe']);
    assert.equal(res.products[0].meta._raghushree_polymer_code, 'HDPE-100');
  });

  it('seeds full catalog idempotently and imports WXR into PostgreSQL', async () => {
    process.env.DB_DRIVER = 'pglite';
    process.env.PGLITE_DATA_DIR = 'memory://';
    const tmpDir = await fs.mkdtemp(path.join(os.tmpdir(), 'raghushree-wxr-'));
    const wxrPath = path.join(tmpDir, 'export.xml');
    await fs.writeFile(
      wxrPath,
      `<?xml version="1.0" encoding="UTF-8" ?>
      <rss version="2.0" xmlns:wp="http://wordpress.org/export/1.2/" xmlns:content="http://purl.org/rss/1.0/modules/content/">
        <channel>
          <item>
            <title>Imported Recycled PP Granules</title>
            <wp:post_id>902</wp:post_id>
            <wp:post_name>imported-recycled-pp-granules</wp:post_name>
            <wp:post_type>polymer_product</wp:post_type>
            <wp:status>publish</wp:status>
            <content:encoded><![CDATA[<p>Imported via WXR CLI.</p>]]></content:encoded>
            <category domain="polymer_category" nicename="pp"><![CDATA[PP]]></category>
          </item>
        </channel>
      </rss>`,
      'utf8'
    );

    const report = await runMigrationCli({ source: 'wxr', file: wxrPath, dryRun: false });
    assert.equal(report.importedProducts, 1);
    assert.equal(report.errors.length, 0);
    assert.ok(report.totals.categories >= 9);
    assert.ok(report.totals.products >= 30);

    // Second run must update idempotently with 0 errors and 0 duplicate creations
    const rerun = await runMigrationCli({ source: 'wxr', file: wxrPath, dryRun: false });
    assert.equal(rerun.updatedProducts, 1);
    assert.equal(rerun.importedProducts, 0);
    assert.equal(rerun.totals.products, report.totals.products);
  });
});

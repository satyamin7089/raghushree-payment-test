// Browser-based CSS regression test. Install playwright-core and @sparticuz/chromium
// outside the repository, then run with NODE_PATH pointing at those node_modules.
const { chromium } = require('playwright-core');
const chromiumModule = require('@sparticuz/chromium');
const chromiumBinary = chromiumModule.default || chromiumModule;
const fs = require('fs');
const assert = require('assert');
const styles = ['assets/css/global.css', 'assets/css/components.css', 'assets/css/products.css', 'assets/css/typography.css'].map((file) => fs.readFileSync(file, 'utf8')).join('\n');
const card = (name) => `<article class="product-card"><a class="product-card__media" href="#"><span class="product-card__badge">PVC Flexible Suction Pipes</span></a><div class="product-card__body"><h3 class="product-card__title"><a href="#">${name}</a></h3><p class="product-card__desc">A verified description of this product and its use.</p><div class="product-card__actions"><a class="btn btn--secondary btn--sm" href="#">Product Details</a><a class="btn btn--primary btn--sm" href="#">Enquire</a></div></div></article>`;
(async () => {
  const browser = await chromium.launch({ executablePath: await chromiumBinary.executablePath(), args: chromiumBinary.args, headless: true });
  try {
    const page = await browser.newPage();
    for (const width of [320, 375, 768, 900, 1024, 1200, 1440]) {
      await page.setViewportSize({ width, height: 900 });
      for (const count of [1, 2, 3]) {
        const cards = Array.from({ length: count }, (_, i) => card(i === 0 ? 'PVC Flexible Suction Pipe' : `Product ${i + 1}`)).join('');
        await page.setContent(`<style>${styles}</style><main><section class="section surface--pearl"><div class="container catalog-layout"><aside class="catalog-sidebar">Categories</aside><div class="catalog-main"><section class="catalog-group"><div class="product-grid">${cards}</div></section></div></div></section></main>`);
        const measurements = await page.evaluate(() => {
          const grid = document.querySelector('.product-grid');
          const cards = [...grid.children].map((element) => { const r = element.getBoundingClientRect(); return { x: r.x, y: r.y, width: r.width }; });
          const buttonsFit = [...grid.querySelectorAll('.btn')].every((button) => button.scrollWidth <= button.clientWidth + 1);
          return { columns: getComputedStyle(grid).gridTemplateColumns.split(' ').length, gridWidth: grid.getBoundingClientRect().width, cards, buttonsFit, overflow: document.documentElement.scrollWidth > innerWidth };
        });
        assert.strictEqual(measurements.columns, 2, `${width}px: expected two tracks`);
        assert(measurements.cards[0].width < measurements.gridWidth * 0.51, `${width}px: card stretches into empty column`);
        if (count >= 2) assert(Math.abs(measurements.cards[0].y - measurements.cards[1].y) < 1 && measurements.cards[1].x > measurements.cards[0].x, `${width}px: cards not side by side`);
        if (count === 3) assert(measurements.cards[2].y > measurements.cards[0].y, `${width}px: third card did not wrap`);
        assert(measurements.buttonsFit && !measurements.overflow, `${width}px: horizontal overflow`);
      }
    }
    console.log('Catalog layout browser tests passed.');
  } finally { await browser.close(); }
})().catch((error) => { console.error(error); process.exitCode = 1; });

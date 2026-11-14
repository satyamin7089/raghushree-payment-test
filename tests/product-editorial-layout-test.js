// Synthetic responsive coverage; the PHP rendering test checks the actual template.
const { chromium } = require('playwright-core');
const chromiumModule = require('@sparticuz/chromium');
const binary = chromiumModule.default || chromiumModule;
const fs = require('fs'); const assert = require('assert');
const styles = ['assets/css/global.css', 'assets/css/components.css', 'assets/css/product-single.css', 'assets/css/typography.css'].map(p => fs.readFileSync(p, 'utf8')).join('\n');
const entries = ['A long descriptive title that needs to wrap properly', 'Second item', 'Third item', 'Fourth item'];
const html = `<style>${styles}</style><main><section class="section product-features"><div class="container product-features__layout"><div class="product-features__intro"><span class="product-section-kicker">Product details</span><h2>Features</h2><span class="product-features__rule"></span></div><div class="product-features__list">${entries.map((e, i) => `<article class="product-feature"><span class="product-feature__number">0${i+1}</span><div><h3>${e}</h3><p>Owner description with enough detail to wrap naturally across different screen sizes.</p></div></article>`).join('')}</div></div></section><section class="section product-applications"><div class="container"><div class="product-applications__header"><div><span class="product-section-kicker">Product details</span><h2>Applications</h2></div><span class="product-applications__mark">↗</span></div><div class="product-applications__grid">${entries.map((e, i) => `<article class="product-application"><span class="product-application__number">0${i+1}</span><h3>${e}</h3><p>Owner description for this application.</p></article>`).join('')}</div><div class="product-industries"><h3>Industries</h3><ul><li>First industry</li><li>${'Long'.repeat(24)}</li></ul></div></div></section></main>`;
(async () => { const browser = await chromium.launch({executablePath: await binary.executablePath(), args: binary.args, headless: true});
try { const page = await browser.newPage(); for (const width of [320, 375, 599, 600, 768, 900, 1024, 1440]) {
 await page.setViewportSize({width, height: 800}); await page.setContent(html);
 const result = await page.evaluate(() => { const apps = [...document.querySelectorAll('.product-application')].map(el => el.getBoundingClientRect()); const feature = document.querySelector('.product-feature').getBoundingClientRect(); const text = document.querySelector('.product-feature h3').getBoundingClientRect(); return {overflow: document.documentElement.scrollWidth > innerWidth, apps: apps.map(a => ({x: a.x, y: a.y, width: a.width})), contained: text.left >= feature.left && text.right <= feature.right + 1}; });
 assert(!result.overflow && result.contained, `content fits at ${width}px`);
 if (width < 600) assert(result.apps[1].y > result.apps[0].y, `single column at ${width}`);
 else assert(Math.abs(result.apps[1].y - result.apps[0].y) < 2 && result.apps[1].x > result.apps[0].x, `multi-column at ${width}`);
 } console.log('Product editorial responsive browser tests passed.'); } finally { await browser.close(); } })().catch(err => { console.error(err); process.exitCode = 1; });

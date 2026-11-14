// Requires playwright-core and @sparticuz/chromium, installed outside the repository.
const { chromium } = require('playwright-core'); const chromiumModule = require('@sparticuz/chromium'); const binary = chromiumModule.default || chromiumModule; const fs = require('fs'); const assert = require('assert');
const styles = ['assets/css/global.css', 'assets/css/components.css', 'assets/css/interactive-sections.css', 'assets/css/product-single.css', 'assets/css/typography.css'].map((p) => fs.readFileSync(p, 'utf8')).join('\n');
(async () => {
 const browser = await chromium.launch({ executablePath: await binary.executablePath(), args: binary.args, headless: true });
 try { const page = await browser.newPage({ viewport: { width: 1200, height: 800 } });
 await page.setContent(`<style>${styles}</style><main><section id="product-hero" class="section section--hero page-banner-section"><div class="page-banner-card"><div class="container page-banner-card__grid"><div class="page-banner-card__left" style="height:1800px">Left content</div><div class="page-banner-card__right"><div class="page-banner-card__frame">Image slider</div></div></div></div></section><section style="height:1200px">After hero</section></main>`);
 let m = await page.evaluate(() => { const f = document.querySelector('.page-banner-card__frame').getBoundingClientRect(); return { height: f.height, top: f.top }; });
 assert(m.height <= 560 && m.height > 200, 'image must not stretch to left column');
 await page.evaluate(() => window.scrollTo(0, 400)); await page.waitForTimeout(80); const top1 = await page.evaluate(() => document.querySelector('.page-banner-card__frame').getBoundingClientRect().top);
 await page.evaluate(() => window.scrollTo(0, 800)); await page.waitForTimeout(80); const top2 = await page.evaluate(() => document.querySelector('.page-banner-card__frame').getBoundingClientRect().top);
 assert(Math.abs(top1 - top2) < 2, 'image should stick while scrolling the hero');
 await page.evaluate(() => window.scrollTo(0, 1850)); await page.waitForTimeout(80); const bottom = await page.evaluate(() => document.querySelector('.page-banner-card__frame').getBoundingClientRect().bottom);
 assert(bottom < 800, 'image must leave as hero ends');
 await page.setViewportSize({ width: 375, height: 800 }); await page.evaluate(() => window.scrollTo(0, 0)); m = await page.evaluate(() => ({ height: document.querySelector('.page-banner-card__frame').getBoundingClientRect().height, overflow: document.documentElement.scrollWidth > innerWidth })); assert(m.height < 400 && !m.overflow, 'mobile layout fits');
 console.log('Product page layout browser tests passed.');
 } finally { await browser.close(); }
})().catch((err) => { console.error(err); process.exitCode = 1; });

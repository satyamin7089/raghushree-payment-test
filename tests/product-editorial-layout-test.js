// Synthetic browser checks; PHP tests exercise the real template and editor.
const { chromium } = require('playwright-core');
const chromiumModule = require('@sparticuz/chromium');
const binary = chromiumModule.default || chromiumModule;
const fs = require('fs'); const assert = require('assert');
const styles = ['assets/css/global.css', 'assets/css/components.css', 'assets/css/product-single.css', 'assets/css/typography.css'].map(p => fs.readFileSync(p, 'utf8')).join('\n');
const entries = ['A long descriptive title that needs to wrap properly', 'Second item', 'Third item', 'Fourth item'];
const heading = title => `<div class="product-editorial__heading product-detail-heading" tabindex="0"><h2>${title}</h2><p class="product-detail-heading__intro">Owner introduction with details.</p></div>`;
const html = `<style>${styles}
html { scroll-behavior: auto !important; }</style><main><form class="product-quote"><div class="packet-stepper"><button type="button">−</button><input type="number" value="2"><button type="button">+</button></div></form>
<section class="section product-features" id="features"><div class="container product-editorial product-detail-layout">${heading('Features')}<div class="product-features__list">${entries.map((e, i) => `<article class="product-feature"><span class="product-feature__number">0${i+1}</span><div><h3>${e}</h3><p>Owner description with enough detail to wrap naturally across different screen sizes.</p></div></article>`).join('')}</div></div></section>
<section class="section product-applications" id="applications"><div class="container product-editorial product-detail-layout">${heading('Applications')}<div class="product-applications__body"><div class="product-applications__grid">${entries.map((e, i) => `<article class="product-application"><span class="product-application__number">0${i+1}</span><h3>${e}</h3><p>Owner description for this application.</p></article>`).join('')}</div><div class="product-industries"><h3>Industries</h3><ul><li>First industry</li><li>${'Long'.repeat(24)}</li></ul></div></div></div></section>
<section class="section surface--pearl" id="technical-details"><div class="container product-editorial product-detail-layout">${heading('Technical specifications')}<div class="product-editorial__body"><div class="table-wrap"><table class="data-table"><tbody><tr><td>Property</td><td>${'Long'.repeat(32)}</td></tr></tbody></table></div></div></div></section>
<section class="section surface--white" id="faqs"><div class="container product-editorial product-detail-layout">${heading('Questions & answers')}<div class="product-editorial__body">Questions</div></div></section><div style="height:1000px"></div></main>`;
(async () => { const browser = await chromium.launch({executablePath: await binary.executablePath(), args: binary.args, headless: true});
try { const page = await browser.newPage(); for (const width of [320, 375, 599, 600, 767, 768, 899, 900, 1024, 1200, 1440]) {
 await page.setViewportSize({width, height: 800}); await page.setContent(html);
 const r = await page.evaluate(() => {
  const rect = e => { const r = e.getBoundingClientRect(); return {x:r.x,y:r.y,width:r.width,height:r.height,right:r.right}; };
  const apps = [...document.querySelectorAll('.product-application')].map(rect);
  const heads = [...document.querySelectorAll('.product-detail-heading h2')].map(e => ({size:getComputedStyle(e).fontSize, line:getComputedStyle(e).lineHeight}));
  const [minus,input,plus] = [...document.querySelector('.packet-stepper').children];
  return {overflow:document.documentElement.scrollWidth > innerWidth, apps, heads, position:getComputedStyle(document.querySelector('.product-detail-heading')).position, sizes:[...document.querySelectorAll('.product-feature h3,.product-application h3,.product-feature p,.product-application p')].map(e=>getComputedStyle(e).fontSize), controls:[minus,input,plus].map(e => ({...rect(e),radius:getComputedStyle(e).borderRadius}))};
 });
 assert(!r.overflow, `no page overflow at ${width}`);
 assert(r.heads.every(h => h.size === r.heads[0].size && h.line === r.heads[0].line), `consistent heading scale at ${width}`);
 assert(r.sizes.slice(0, 8).every((v,i) => v === (i % 2 ? '17px' : '22px')) && r.sizes.slice(8).every((v,i) => v === (i % 2 ? '17px' : '22px')), `feature and application typography at ${width}`);
 assert(r.position === (width >= 900 ? 'sticky' : 'static'), `responsive sticky at ${width}`);
 assert(r.controls.every(c => c.height === 54 && c.radius === '8px') && r.controls[1].x - r.controls[0].right === 8 && r.controls[2].x - r.controls[1].right === 8, `aligned rounded controls at ${width}`);
 if (width < 600) assert(r.apps[1].y > r.apps[0].y, `single application column at ${width}`);
 else assert(Math.abs(r.apps[1].y - r.apps[0].y) < 2 && r.apps[1].x > r.apps[0].x, `two application columns at ${width}`);
 }
 await page.setViewportSize({width:1200,height:800}); await page.setContent(html);
 for (const id of ['features','applications','technical-details','faqs']) {
  await page.evaluate(id => { const s=document.getElementById(id); s.querySelector('.product-detail-heading').nextElementSibling.style.minHeight='1800px'; }, id);
  const sectionTop = await page.$eval('#'+id+' .product-detail-layout', el => el.getBoundingClientRect().top + window.scrollY);
  await page.evaluate(y => window.scrollTo(0,y), sectionTop + 300); await page.waitForTimeout(30);
  const top1 = await page.$eval('#'+id+' .product-detail-heading', el => el.getBoundingClientRect().top);
  await page.evaluate(y => window.scrollTo(0,y), sectionTop + 800); await page.waitForTimeout(30);
  const top2 = await page.$eval('#'+id+' .product-detail-heading', el => el.getBoundingClientRect().top);
  assert(Math.abs(top1-top2)<2, `${id} sticks during its content`);
  await page.evaluate(y => window.scrollTo(0,y), sectionTop + 1800); await page.waitForTimeout(30);
  const bottom = await page.$eval('#'+id+' .product-detail-heading', el => el.getBoundingClientRect().bottom);
  assert(bottom < top2 + 30, `${id} leaves at end of content`);
 }
 console.log('Product editorial responsive browser tests passed.'); } finally { await browser.close(); } })().catch(err => { console.error(err); process.exitCode = 1; });

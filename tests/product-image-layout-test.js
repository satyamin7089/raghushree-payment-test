// Run with playwright-core and @sparticuz/chromium available via NODE_PATH.
const { chromium } = require('playwright-core');
const moduleChromium = require('@sparticuz/chromium');
const binary = moduleChromium.default || moduleChromium;
const fs = require('fs');
const assert = require('assert');
const styles = ['global', 'components', 'interactive-sections', 'products', 'shop', 'product-single', 'typography'].map((name) => fs.readFileSync(`assets/css/${name}.css`, 'utf8')).join('\n');
const svg = (w, h) => `data:image/svg+xml,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}"><rect width="100%" height="100%" fill="red"/></svg>`)}`;
const sources = [svg(400, 400), svg(600, 400), svg(400, 600)];
const html = (src) => `<style>${styles}</style><main>
<section class="container"><div class="product-grid"><article class="product-card"><a class="product-card__media"><img src="${src}"></a><div class="product-card__body"><span class="product-card__badge">Category</span>Product</div></article></div>
<div class="shop-grid"><article class="shop-tile"><a class="shop-tile__media"><img src="${src}"></a><div class="shop-tile__body"><span class="shop-tile__tag">Category</span>Product</div></article></div></section>
<section class="shopflow"><div class="shopflow__stage"><div class="shopflow__viewport"><article class="shopflow__slide is-active" data-offset="0"><a class="shopflow__link"><img class="shopflow__img" src="${src}"><span class="shopflow__caption">Product</span></a></article></div></div></section>
<section id="product-hero" class="section section--hero page-banner-section"><div class="page-banner-card"><div class="container page-banner-card__grid"><div class="page-banner-card__left">Product</div><div class="page-banner-card__right"><div class="page-banner-card__frame"><div class="page-banner-card__product-image"><img src="${src}"></div><div class="page-banner-card__caption">Product</div></div></div></div></div>
<div id="slider-test"><div class="page-banner-card__frame banner-fade-slider"><div class="page-banner-card__product-image"><div class="banner-fade-slider__slides"><div class="banner-fade-slider__slide is-active"><img src="${src}"></div></div></div><div class="page-banner-card__caption page-banner-card__caption--slider">Controls</div></div></div></section>
</main>`;
(async () => {
 const browser = await chromium.launch({ executablePath: await binary.executablePath(), args: binary.args, headless: true });
 try {
  const page = await browser.newPage();
  for (const width of [320, 375, 768, 900, 1200, 1440]) {
   await page.setViewportSize({width, height: 900});
   for (const src of sources) {
    await page.setContent(html(src));
    for (const [frame, img] of [['.product-card__media', '.product-card__media img'], ['.shop-tile__media', '.shop-tile__media img'], ['.shopflow__img', '.shopflow__img'], ['#product-hero .page-banner-card__right .page-banner-card__product-image', '#product-hero .page-banner-card__right img'], ['#slider-test .page-banner-card__product-image', '#slider-test img']]) {
     const result = await page.evaluate(([frame, img]) => { const a = document.querySelector(frame).getBoundingClientRect(), i = document.querySelector(img), b = i.getBoundingClientRect(), c = getComputedStyle(i); return { fw:a.width, fh:a.height, iw:b.width, ih:b.height, fit:c.objectFit, transform:c.transform }; }, [frame, img]);
     assert(Math.abs(result.fw - result.fh) < 1, `${width} ${frame}: expected square: ${JSON.stringify(result)}`);
     assert(Math.abs(result.iw - result.fw) < 1 && Math.abs(result.ih - result.fh) < 1 && result.fit === 'contain' && result.transform === 'none', `${width} ${img}: image should fill square with no crop: ${JSON.stringify(result)}`);
    }
    for (const selector of ['.product-card', '.shop-tile', '#product-hero .page-banner-card__frame']) {
     await page.locator(selector).first().hover();
     const transform = await page.locator(`${selector} img`).first().evaluate((el) => getComputedStyle(el).transform);
     assert.strictEqual(transform, 'none', `${width} ${selector}: hover must not crop image`);
    }
    const overlap = await page.evaluate(() => {
      const pairs = [['.product-card__media','.product-card__badge'], ['.shop-tile__media','.shop-tile__tag'], ['#product-hero .page-banner-card__product-image','#product-hero .page-banner-card__caption'], ['#slider-test .page-banner-card__product-image','#slider-test .page-banner-card__caption'], ['.shopflow__img','.shopflow__caption']];
      return pairs.map(([a,b]) => [a, document.querySelector(a).getBoundingClientRect().bottom, document.querySelector(b).getBoundingClientRect().top]);
    });
    overlap.forEach(([name, bottom, top]) => assert(top >= bottom - 1, `${width} ${name}: text overlaps image`));
    assert(!(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)), `${width}: horizontal overflow`);
   }
  }
  // Ensure every product template requests an uncropped attachment, including every hero slide.
  for (const file of ['product-card.php', 'product-tile.php', 'product-coverflow.php']) assert(fs.readFileSync(`template-parts/components/${file}`, 'utf8').includes("'size'     => 'full'"), `${file}: product image must not request cropped size`);
  const banner = fs.readFileSync('template-parts/components/page-banner.php', 'utf8');
  assert(banner.includes("$image_size = $is_product_banner ? 'full' : 'raghushree-hero'"));
  assert.strictEqual((banner.match(/'size'\s*=>\s*\$image_size/g) || []).length, 3);
  console.log('Product image layout browser tests passed.');
 } finally { await browser.close(); }
})().catch((error) => { console.error(error); process.exitCode = 1; });

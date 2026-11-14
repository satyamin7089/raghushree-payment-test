// End-to-end check against a disposable WordPress site with both plugins active.
// Set RGPA_TEST_URL, RGPA_CLIENT_USER, RGPA_CLIENT_PASS, RGPA_ADMIN_USER and
// RGPA_ADMIN_PASS. The fixture must contain a paid Payment Order. Creates data.
const assert = require('assert');
const { chromium } = require('playwright-core');
const chromiumModule = require('@sparticuz/chromium');
const binary = chromiumModule.default || chromiumModule;
const base = process.env.RGPA_TEST_URL;
for (const key of ['RGPA_TEST_URL', 'RGPA_CLIENT_USER', 'RGPA_CLIENT_PASS', 'RGPA_ADMIN_USER', 'RGPA_ADMIN_PASS']) {
  if (!process.env[key]) throw new Error(`Set ${key}; use a disposable test site only.`);
}
const image = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/aL8AAAAASUVORK5CYII=', 'base64');
async function login(page, user, pass) {
  await page.goto(`${base}/wp-login.php`);
  await page.locator('#user_login').fill(user);
  await page.locator('#user_pass').fill(pass);
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');
}
(async () => {
  const browser = await chromium.launch({ executablePath: await binary.executablePath(), args: binary.args, headless: true });
  try {
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await login(page, process.env.RGPA_CLIENT_USER, process.env.RGPA_CLIENT_PASS);
    assert(await page.locator('body.rgpa-workspace-products').count(), 'client product workspace is styled');
    assert.deepStrictEqual(await page.locator('.rgpa-nav__link').allTextContents(), ['Products', 'Categories', 'Grades', 'Forms', 'Applications', 'Orders', 'Sign out']);
    assert.strictEqual(await page.locator('.rgpa-metric').count(), 3);
    assert(await page.locator('.wp-list-table').count(), 'native product list remains');
    assert(!await page.getByRole('link', { name: 'New Catalog Setup' }).count());
    assert(!await page.getByRole('link', { name: 'Payment & Shipping Setup' }).count());

    for (const path of ['admin.php?page=raghushree-payment-setup', 'admin.php?page=raghushree-catalog-setup', 'options-general.php']) {
      const response = await page.goto(`${base}/wp-admin/${path}`);
      assert.strictEqual(response.status(), 403, `${path} is blocked`);
    }
    // Taxonomy page uses the actual WordPress create form and AJAX handler.
    await page.goto(`${base}/wp-admin/edit-tags.php?taxonomy=polymer_category&post_type=products`);
    assert(await page.locator('body.rgpa-workspace-polymer_category').count());
    const term = `Workspace test ${Date.now()}`;
    await page.locator('#tag-name').fill(term);
    await page.locator('#submit').click();
    await page.locator('.row-title').filter({ hasText: term }).first().waitFor({ timeout: 15000 });

    await page.goto(`${base}/wp-admin/admin.php?page=rgpay-orders`);
    assert(await page.locator('body.rgpa-workspace-orders').count());
    assert(await page.locator('.wrap').getByText('Payment Orders', { exact: true }).count());
    const order = page.locator('form[action*="admin-post.php"]').filter({ has: page.locator('[name="action"][value="rgpay_update_delivery"]') }).first();
    assert(await order.count(), 'fixture must contain a paid order');
    await order.locator('[name="delivery_status"]').selectOption('fulfilled');
    await order.locator('[name="delivery_courier"]').fill('Workspace test');
    await order.locator('[name="delivery_tracking"]').fill('TEST-123');
    await order.getByRole('button', { name: 'Save delivery' }).click();
    await page.getByText('Manual delivery details saved.').waitFor();
    assert.strictEqual(await page.locator('[name="delivery_tracking"]').first().inputValue(), 'TEST-123');

    await page.goto(`${base}/wp-admin/post-new.php?post_type=products`);
    await page.locator('body.rgpa-workspace-editor').waitFor();
    const welcome = page.getByRole('dialog', { name: 'Welcome to the block editor' });
    if (await welcome.isVisible().catch(() => false)) await welcome.getByRole('button', { name: 'Close' }).click();
    const title = `Workspace product ${Date.now()}`;
    await page.frameLocator('iframe[name="editor-canvas"]').getByRole('textbox', { name: 'Add title' }).fill(title);
    await page.locator('[name="rg_product_type"]').selectOption('pipe');
    await page.locator('[name="rg_gst"]').fill('18');
    await page.locator('[name="rg_packet_price"]').fill('125.50');
    await page.locator('[name="rg_packet_rows"]').fill('1, 8, 40, 30, 20');
    await page.getByRole('button', { name: 'Save draft', exact: true }).first().click();
    await page.waitForURL('**/post.php?post=*&action=edit', { timeout: 20000 });
    await page.getByText('Draft saved.').first().waitFor({ timeout: 20000 });
    await page.getByRole('button', { name: 'Choose images', exact: true }).click();
    await page.locator('input[type=file]').last().setInputFiles({ name: 'workspace-test.png', mimeType: 'image/png', buffer: image });
    await page.locator('.media-modal').getByText('workspace-test.png').first().waitFor({ timeout: 20000 });
    await page.getByRole('button', { name: 'Add images', exact: true }).click();
    assert((await page.locator('#raghushree_gallery_ids').inputValue()).trim(), 'uploaded image selected');
    await page.getByRole('button', { name: 'Publish', exact: true }).first().click();
    await page.getByRole('button', { name: 'Publish', exact: true }).last().click();
    await page.getByText('Post published.').first().waitFor({ timeout: 20000 });
    const postId = new URL(page.url()).searchParams.get('post');
    assert(postId, 'new product ID');
    await page.reload(); await page.locator('[name="rg_packet_price"]').waitFor();
    assert.strictEqual(await page.locator('[name="rg_packet_price"]').inputValue(), '125.50');
    assert.strictEqual(await page.locator('[name="rg_packet_rows"]').inputValue(), '1, 8, 40, 30, 20');
    assert((await page.locator('#raghushree_gallery_ids').inputValue()).trim(), 'image saved');

    await page.goto(`${base}/wp-admin/edit.php?post_type=products`);
    const row = page.locator(`#post-${postId}`);
    await row.hover();
    await row.locator('.editinline').click();
    const quick = page.locator(`#edit-${postId}`);
    await quick.locator('[name="post_title"]').fill(`${title} edited`);
    await quick.locator('.save').click();
    await page.locator(`#post-${postId} .row-title`).getByText(`${title} edited`).waitFor({ timeout: 15000 });
    await page.locator(`#post-${postId}`).hover();
    await page.locator(`#post-${postId} .submitdelete`).click();
    await page.getByText('1 post moved to the Trash.').waitFor();
    await page.goto(`${base}/wp-admin/edit.php?post_type=products&post_status=trash`);
    await page.locator(`#post-${postId}`).hover();
    await page.locator(`#post-${postId} .submitdelete`).click();
    await page.getByText('1 post permanently deleted.').waitFor();

    for (const width of [320, 390, 768, 1024, 1440]) {
      await page.setViewportSize({ width, height: 850 });
      await page.goto(`${base}/wp-admin/edit.php?post_type=products`);
      const dimensions = await page.evaluate(() => ({ viewport: innerWidth, document: document.documentElement.scrollWidth, hero: document.querySelector('.rgpa-hero').getBoundingClientRect().width }));
      assert(dimensions.document <= dimensions.viewport, `no whole-page overflow at ${width}`);
      assert(dimensions.hero >= width - (width > 782 ? 250 : 40), `hero uses available width at ${width}`);
      if (width < 782) {
        await page.locator('#wp-admin-bar-menu-toggle > a').click();
        assert(await page.locator('#adminmenu').isVisible(), 'mobile WordPress navigation remains available');
      }
    }
    await page.context().clearCookies();
    await login(page, process.env.RGPA_ADMIN_USER, process.env.RGPA_ADMIN_PASS);
    await page.goto(`${base}/wp-admin/edit.php?post_type=products`);
    assert(!await page.locator('body.rgpa-workspace').count(), 'administrator interface is unchanged');
    assert(await page.getByRole('link', { name: 'Payment & Shipping Setup' }).count(), 'administrator setup remains available');
    console.log('PASS: workspace screens, editor/media, orders, taxonomies, responsive layout and access boundaries.');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exit(1); });

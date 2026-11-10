// Browser checks with fixture APIs only. Build/package donor assets before running.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const build = path.resolve(process.env.DONOR_BUILD_DIR || 'public_html/benefactor-dashboard');
const user = { id: 1, first_name: 'سارا', last_name: 'احمدی', email: 'qa@example.test', phone: '09123456789', tier: { name: 'همراه' } };
const dashboard = { stats: { total_amount: 28500000, donation_count: 12, days_supporting: 187, monthly_series: [{ month: '2026-08', amount: 1500000 }, { month: '2026-09', amount: 2500000 }, { month: '2026-10', amount: 4500000 }] }, recent_activity: [{ reference: 'Q1', campaign_title: 'حمایت از بیماران', amount: 1500000, created_at: '2026-10-29' }], kindness_points: 120, tier: user.tier };
const campaign = { id: '1', slug: 'care', title: 'حمایت از درمان', description: 'حمایت از بیماران مکسا', image_url: '/qa.svg', category: 'درمان', goal_amount: 10000000, raised_amount: 5000000, donor_count: 12, is_general_fund: false, status: 'active', progress: 50, days_left: 30 };
async function setup(page) {
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await page.route('**/*', async route => {
    const url = new URL(route.request().url());
    if (url.hostname !== 'macsa.test') return route.abort();
    assert.equal(route.request().method(), 'GET', 'Theme checks never mutate data');
    const responses = {
      '/api/user/me': { user }, '/api/user/dashboard': dashboard,
      '/api/campaigns': { campaigns: [campaign] }, '/api/user/orders': { orders: [] },
      '/api/user/impacts': { impacts: [] }, '/api/medical-records/me': { record: null },
      '/api/user/notification-prefs': { preferences: { news: true, impact_reports: true, new_campaigns: true } },
      '/api/donations': { items: [], total: 0 },
    };
    if (url.pathname in responses) return route.fulfill({ json: { success: true, data: responses[url.pathname] } });
    if (url.pathname === '/qa.svg') return route.fulfill({ contentType: 'image/svg+xml', body: '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="240"><rect width="600" height="240" fill="#376f6b"/></svg>' });
    const file = path.join(build, url.pathname.replace('/benefactor-dashboard/', ''));
    if (fs.existsSync(file) && fs.statSync(file).isFile()) return route.fulfill({ path: file });
    return route.fulfill({ path: path.join(build, 'index.html') });
  });
  return errors;
}
async function home(page) {
  await page.goto('http://macsa.test/benefactor-dashboard/');
  await page.getByRole('heading', { name: 'مشارکت‌های شما در مکسا' }).waitFor();
}
async function settled(page, dark) {
  await page.waitForFunction(wanted => document.documentElement.classList.contains('dark') === wanted && !document.documentElement.dataset.themeTransition, dark);
  assert.equal(await page.getByRole('button', { name: 'حالت تاریک', exact: true }).getAttribute('aria-pressed'), String(dark));
  assert.equal(await page.evaluate(() => document.documentElement.style.colorScheme), dark ? 'dark' : 'light');
}
async function shot(page, name) {
  if (process.env.QA_SHOTS) await page.screenshot({ path: path.join(process.env.QA_SHOTS, name + '.png') });
}
function contrast(a, b) {
  const luminance = rgb => rgb.match(/[\d.]+/g).slice(0, 3).map(Number).map(v => v / 255).map(v => v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4).reduce((sum, v, i) => sum + v * [.2126, .7152, .0722][i], 0);
  const x = luminance(a), y = luminance(b);
  return (Math.max(x, y) + .05) / (Math.min(x, y) + .05);
}
(async () => {
  const browser = await chromium.launch({ headless: true, ...(process.env.CHROME_PATH ? { executablePath: process.env.CHROME_PATH } : {}) });
  try {
    for (const width of [1440, 390, 320]) {
      const page = await browser.newPage({ viewport: { width, height: 1000 } });
      const errors = await setup(page);
      await home(page);
      await settled(page, false);
      const button = page.getByRole('button', { name: 'حالت تاریک', exact: true });
      const bounds = await button.boundingBox();
      assert.ok(bounds.x >= 0 && bounds.x + bounds.width <= width, 'Toggle stays in viewport');
      await button.focus();
      await page.keyboard.press('Enter');
      await page.waitForFunction(() => document.documentElement.dataset.themeTransition === 'reveal');
      assert.equal(await page.evaluate(() => getComputedStyle(document.documentElement, '::view-transition-new(root)').animationName), 'donor-theme-reveal');
      if (width === 1440 && process.env.QA_SHOTS) {
        await page.waitForFunction(() => document.getAnimations().some(animation => animation.animationName === 'donor-theme-reveal'));
        await page.evaluate(() => {
          const animation = document.getAnimations().find(animation => animation.animationName === 'donor-theme-reveal');
          animation.pause(); animation.currentTime = 80;
        });
        await shot(page, 'donor-theme-transition');
        // A second input during the reveal must not queue a conflicting theme change.
        await page.evaluate(() => document.querySelectorAll('.donor-theme-toggle').forEach(button => button.click()));
        await page.evaluate(() => document.getAnimations().filter(animation => animation.animationName === 'donor-theme-reveal').forEach(animation => animation.play()));
      }
      await settled(page, true);
      assert.equal(await page.evaluate(() => localStorage.getItem('macsa-donor-theme')), 'dark');
      assert.equal(await page.locator('.donor-panel').first().evaluate(node => getComputedStyle(node).backgroundColor), 'rgb(26, 39, 46)');
      for (const selector of ['.donor-stat-value', '.donor-stat .text-muted-foreground', '.donor-activity .text-primary']) {
        const colors = await page.locator(selector).first().evaluate(node => ({ text: getComputedStyle(node).color, bg: getComputedStyle(node.closest('.donor-stat,.donor-panel')).backgroundColor }));
        assert.ok(contrast(colors.text, colors.bg) >= 4.5, 'Readable dark text: ' + selector);
      }
      assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'No horizontal overflow');
      await shot(page, `donor-dark-${width}`);
      await page.reload();
      await page.getByRole('heading', { name: 'مشارکت‌های شما در مکسا' }).waitFor();
      await settled(page, true);
      if (width < 768) {
        await page.getByRole('button', { name: 'منوی پیشخوان' }).click();
        await shot(page, `donor-dark-sidebar-${width}`);
        await page.getByRole('button', { name: 'منوی پیشخوان' }).click();
      }
      await button.click();
      await settled(page, false);
      assert.equal(await page.locator('.donor-panel').first().evaluate(node => getComputedStyle(node).backgroundColor), 'rgb(255, 255, 255)');
      await page.emulateMedia({ reducedMotion: 'reduce' });
      await page.evaluate(() => { window.themeAnimations = 0; const original = document.startViewTransition.bind(document); document.startViewTransition = update => { window.themeAnimations++; return original(update); }; });
      await button.click();
      await settled(page, true);
      assert.equal(await page.evaluate(() => window.themeAnimations), 0, 'Reduced motion skips circular reveal');
      assert.deepEqual(errors, []);
      await page.close();
      console.log(`PASS ${width}px toggle, keyboard, circular transition, persistence, contrast and reduced motion`);
    }

    const page = await browser.newPage({ viewport: { width: 1440, height: 1000 }, reducedMotion: 'reduce' });
    const errors = await setup(page);
    await page.addInitScript(() => localStorage.setItem('macsa-donor-theme', 'dark'));
    let releaseBundle;
    const bundleReady = new Promise(resolve => { releaseBundle = resolve; });
    await page.route('**/assets/*.js', async route => { await bundleReady; await route.fallback(); });
    try {
      await page.goto('http://macsa.test/benefactor-dashboard/', { waitUntil: 'commit' });
      await page.waitForFunction(() => document.documentElement.classList.contains('dark'));
      assert.equal(await page.evaluate(() => document.documentElement.style.colorScheme), 'dark', 'Saved dark mode applies before the React bundle loads');
    } finally { releaseBundle(); }
    await page.getByRole('heading', { name: 'مشارکت‌های شما در مکسا' }).waitFor();
    for (const [route, heading] of [['payments', 'پرداخت آنلاین'], ['history', 'تاریخچه پرداخت‌ها'], ['campaigns', 'کمپین‌های پیشنهادی'], ['profile', 'اطلاعات شخصی'], ['orders', 'سفارشات اخیر'], ['impact', 'اثر پرمهر شما']]) {
      await page.goto('http://macsa.test/benefactor-dashboard/' + route);
      await page.getByRole('heading', { name: heading, exact: true }).last().waitFor();
      await settled(page, true);
      const surfaces = await page.locator('.donor-shell .bg-surface').evaluateAll(nodes => nodes.map(node => getComputedStyle(node).backgroundColor));
      assert.ok(surfaces.length > 0 && surfaces.every(color => color === 'rgb(26, 39, 46)'), 'Dark surfaces on ' + route);
      await shot(page, 'donor-dark-' + route);
    }
    assert.deepEqual(errors, []);
    await page.close();
    console.log('PASS dark surfaces across all donor routes');

    const fallback = await browser.newPage();
    await setup(fallback);
    await fallback.addInitScript(() => {
      document.startViewTransition = undefined;
      const get = Storage.prototype.getItem, set = Storage.prototype.setItem;
      Storage.prototype.getItem = function(key) { if (key === 'macsa-donor-theme') throw new Error('Storage disabled'); return get.call(this, key); };
      Storage.prototype.setItem = function(key, value) { if (key === 'macsa-donor-theme') throw new Error('Storage disabled'); return set.call(this, key, value); };
    });
    await home(fallback);
    await fallback.getByRole('button', { name: 'حالت تاریک', exact: true }).click();
    assert.equal(await fallback.evaluate(() => document.documentElement.dataset.themeTransition), 'fade');
    await settled(fallback, true);
    await fallback.getByRole('button', { name: 'حالت تاریک', exact: true }).click();
    await settled(fallback, false);
    await fallback.close();
    console.log('PASS fallback transition and unavailable localStorage');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });

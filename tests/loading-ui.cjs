// No live services: render the real templates and bundles with delayed fixture responses.
// Build and copy the donor assets first. PLAYWRIGHT_MODULE may point to an existing install.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = path.resolve(__dirname, '..');
const publicRoot = path.join(root, 'public_html');
const donorRoot = path.join(publicRoot, 'benefactor-dashboard');
const phpPath = file => JSON.stringify(path.join(publicRoot, file).replaceAll('\\', '/'));
const php = code => execFileSync('php', { input: '<?php ' + code, encoding: 'utf8', cwd: root });
const source = fs.readFileSync(path.join(publicRoot, 'dashboard/index.php'), 'utf8');
const admin = php(`
require ${phpPath('core/icons.php')}; require ${phpPath('core/loading-ui.php')};
function e($s){return htmlspecialchars($s);} function money_compact($s){return number_format($s);}
function dash_is_branch_admin(){return true;}
if(!function_exists('mb_substr')){function mb_substr($s,$start,$len,$encoding){return iconv_substr($s,$start,$len,$encoding);}}
$isSuper=true; $ACTIVE_BRANCH_ROW=['name'=>'دفتر مرکزی مکسا']; $U=['full_name'=>'کاربر آزمایشی'];
$series=['labels'=>['مهر','آبان'],'campaigns'=>[100,200],'periods'=>[50,75],'free'=>[10,20]];
$SERVER=['stats'=>['totalDonors'=>187,'activeDonors'=>63,'totalCollected'=>62000000,'activeCampaigns'=>4,'trends'=>[]],
 'chart'=>['day'=>$series,'week'=>$series,'month'=>$series,'year'=>$series],'donors'=>[],'tx'=>[]];
$MENU=['isSuper'=>true,'isHqView'=>true,'can'=>['campaigns'=>true,'financial'=>true],'branches'=>[['id'=>1,'name'=>'مکسا']],'activeBranch'=>1];
?>` + source.slice(source.indexOf('<!DOCTYPE html>')).replaceAll('__DIR__', phpPath('dashboard')));
const publicPage = php(`require ${phpPath('core/site-locale.php')}; echo site_document('<!doctype html><html lang="fa"><head><meta charset="utf-8"></head><body><a id="cancel" href="/other">لغو</a><a id="anchor" href="#part">بخش</a><a id="download" href="/report.pdf" download>گزارش</a><a id="next" href="/next">بعدی</a><div id="part">مکسا</div></body></html>');`);
const user = { id: 1, first_name: 'سارا', last_name: 'احمدی', email: 'qa@example.test', tier: { name: 'همراه' } };
const dashboard = { stats: { total_amount: 2850000, donation_count: 2, days_supporting: 90, monthly_series: [] }, recent_activity: [], kindness_points: 20, tier: user.tier };
function gate() { let release; const promise = new Promise(resolve => { release = resolve; }); return { promise, release }; }
async function screenshot(page, name) {
  if (process.env.QA_SHOTS) await page.screenshot({ path: path.join(process.env.QA_SHOTS, name + '.png') });
}
async function fits(page) { assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'No horizontal overflow'); }

(async () => {
  const browser = await chromium.launch({ headless: true, ...(process.env.CHROME_PATH ? { executablePath: process.env.CHROME_PATH } : {}) });
  try {
    for (const width of [1440, 390, 320]) {
      const session = gate(), data = gate(), destination = gate();
      const page = await browser.newPage({ viewport: { width, height: 900 } });
      const errors = [];
      page.on('pageerror', error => errors.push(error.message));
      await page.route('**/*', async route => {
        const url = new URL(route.request().url());
        if (url.pathname.endsWith('chart.umd.min.js')) {
          // Chart rendering is outside this test; keep the unmodified shell initialization running.
          return route.fulfill({ contentType: 'application/javascript', body: 'window.Chart=class{static defaults={font:{}};constructor(ctx,opts){this.data=opts.data}destroy(){}update(){}};' });
        }
        if (url.hostname !== 'macsa.test') return route.abort();
        if (url.pathname === '/dashboard/index.php') return route.fulfill({ contentType: 'text/html', body: admin });
        if (url.pathname === '/dashboard/loading-test.php') {
          await destination.promise;
          return route.fulfill({ contentType: 'text/html', body: '<html><head><title>آزمایش</title></head><body><h1>اطلاعات آماده است</h1><a href="#part">بخش</a><form onsubmit="event.preventDefault()"><button>ذخیره آزمایشی</button></form></body></html>' });
        }
        if (url.pathname === '/api/user/me') { await session.promise; return route.fulfill({ json: { success: true, data: { user } } }); }
        if (url.pathname === '/api/user/dashboard') { await data.promise; return route.fulfill({ json: { success: true, data: dashboard } }); }
        if (url.pathname.startsWith('/api/')) return route.fulfill({ json: { success: true, data: dashboard } });
        let file = url.pathname.startsWith('/benefactor-dashboard/')
          ? path.join(donorRoot, url.pathname.slice('/benefactor-dashboard/'.length) || 'index.html')
          : path.join(publicRoot, url.pathname);
        if (fs.existsSync(file) && fs.statSync(file).isFile()) return route.fulfill({ path: file });
        return route.fulfill({ status: 404, body: '' });
      });
      await page.goto('http://macsa.test/benefactor-dashboard/');
      await page.getByRole('status', { name: 'در حال آماده‌سازی پنل خیرین' }).waitFor();
      await fits(page);
      const dots = page.locator('.macsa-loader__pair').first().locator('i');
      const transforms = await dots.evaluateAll(nodes => nodes.map(node => getComputedStyle(node).transform));
      assert.notEqual(transforms[0], transforms[1], 'Helix strands travel in opposite phases');
      await screenshot(page, `macsa-loading-session-${width}`);
      session.release();
      await page.getByRole('status', { name: 'در حال دریافت اطلاعات داشبورد' }).waitFor();
      assert.equal(await page.locator('.macsa-loading-stats .macsa-loading-card').count(), 4);
      await fits(page);
      await screenshot(page, `macsa-loading-donor-${width}`);
      await page.emulateMedia({ reducedMotion: 'reduce' });
      assert.equal(await dots.first().evaluate(node => getComputedStyle(node).animationName), 'none');
      assert.equal(await page.locator('.macsa-skeleton').first().evaluate(node => getComputedStyle(node, '::after').display), 'none');
      data.release();
      await page.getByText('مشارکت‌های شما در مکسا', { exact: true }).waitFor();
      assert.equal(await page.locator('.macsa-loading-layout').count(), 0, 'Skeleton clears on data');
      if (width === 1440) {
        await page.route('**/api/user/dashboard', route => route.fulfill({ status: 503, json: { success: false, error: { message: 'Fixture failure' } } }));
        await page.reload();
        await page.getByText('دریافت اطلاعات داشبورد ناموفق بود.', { exact: true }).waitFor();
        assert.equal(await page.locator('.macsa-loading-layout').count(), 0, 'Data errors leave the loading state');
      }
      await page.emulateMedia({ reducedMotion: 'no-preference' });
      await page.addInitScript(() => localStorage.setItem('maxa-theme', 'dark'));
      await page.goto('http://macsa.test/dashboard/index.php#page=loading-test.php', { waitUntil: 'domcontentloaded' });
      await page.locator('#spaPending:not([hidden])').waitFor();
      assert.equal(await page.locator('#spaFrame').evaluate(node => node.inert), true);
      assert.equal(await page.locator('#spaFrame').getAttribute('aria-busy'), 'true');
      await fits(page);
      await screenshot(page, `macsa-loading-admin-${width}-dark`);
      await page.evaluate(() => document.documentElement.removeAttribute('data-theme'));
      await screenshot(page, `macsa-loading-admin-${width}-light`);
      destination.release();
      await page.frameLocator('#spaFrame').getByText('اطلاعات آماده است').waitFor();
      await page.waitForFunction(() => document.getElementById('spaPending').hidden);
      assert.equal(await page.locator('#spaFrame').evaluate(node => node.inert), false);
      await page.frameLocator('#spaFrame').getByRole('button').click();
      assert.equal(await page.locator('#spaPending').evaluate(node => node.hidden), true, 'AJAX forms do not trigger navigation loading');
      await page.evaluate(() => { location.hash = ''; });
      await page.waitForFunction(() => !document.body.classList.contains('spa-active'));
      assert.equal(await page.locator('#spaFrame').getAttribute('aria-busy'), 'false', 'Returning home resets loading');
      assert.deepEqual(errors, []);
      await page.close();
      console.log(`PASS donor/admin loading, completion, reduced motion and layout at ${width}px`);
    }

    const page = await browser.newPage();
    const first = gate(), second = gate(), navigation = gate();
    await page.route('**/*', async route => {
      const url = new URL(route.request().url());
      if (url.hostname !== 'macsa.test') return route.abort();
      if (url.pathname === '/home') return route.fulfill({ contentType: 'text/html', body: publicPage });
      if (url.pathname === '/dashboard/hero-list.php') { await first.promise; return route.fulfill({ json: [] }); }
      if (url.pathname === '/api-stories.php') { await second.promise; return route.abort('failed'); }
      if (url.pathname === '/next') { await navigation.promise; return route.fulfill({ contentType: 'text/html', body: '<h1>Next page</h1>' }); }
      if (url.pathname.startsWith('/assets/')) return route.fulfill({ path: path.join(publicRoot, url.pathname) });
      return route.fulfill({ body: 'ready' });
    });
    await page.goto('http://macsa.test/home');
    await page.evaluate(() => {
      document.getElementById('cancel').addEventListener('click', event => event.preventDefault());
      window.requestsDone = Promise.allSettled([fetch('/dashboard/hero-list.php'), fetch('/api-stories.php')]);
    });
    await page.locator('#macsaSiteLoading:not([hidden])').waitFor();
    first.release();
    await page.waitForTimeout(250);
    assert.equal(await page.locator('#macsaSiteLoading').evaluate(node => node.hidden), false, 'Concurrent request keeps loader visible');
    second.release();
    await page.evaluate(() => window.requestsDone);
    assert.equal(await page.locator('#macsaSiteLoading').evaluate(node => node.hidden), true, 'Failures also clear loading');
    await page.locator('#cancel').click();
    await page.locator('#anchor').click();
    await page.waitForTimeout(250);
    assert.equal(await page.locator('#macsaSiteLoading').evaluate(node => node.hidden), true, 'Cancelled navigation and anchors do not show loader');
    await page.evaluate(async () => { await fetch('/api/user/me'); });
    assert.equal(await page.locator('#macsaSiteLoading').evaluate(node => node.hidden), true, 'Private APIs are excluded');
    await page.evaluate(() => {
      window.dispatchEvent(new CustomEvent('macsa:loading', { detail: 1 }));
      window.dispatchEvent(new CustomEvent('macsa:loading', { detail: -1 }));
    });
    await page.waitForTimeout(250);
    assert.equal(await page.locator('#macsaSiteLoading').evaluate(node => node.hidden), true, 'Fast requests never flash');
    const visibility = [];
    await page.exposeFunction('loadingChanged', hidden => visibility.push(hidden));
    await page.evaluate(() => {
      const node = document.getElementById('macsaSiteLoading');
      new MutationObserver(() => window.loadingChanged(node.hidden)).observe(node, { attributes: true, attributeFilter: ['hidden'] });
    });
    try {
      await page.locator('#next').click({ noWaitAfter: true });
      await new Promise(resolve => setTimeout(resolve, 400));
      assert.ok(visibility.includes(false), 'Slow native navigation shows loading');
    } finally { navigation.release(); }
    await page.getByRole('heading', { name: 'Next page' }).waitFor();
    await page.goBack();
    assert.equal(await page.locator('#macsaSiteLoading').evaluate(node => node.hidden), true, 'Back navigation restores a clear page');
    await page.close();
    console.log('PASS public concurrent requests, failure cleanup, cancelled navigation and fast requests');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });

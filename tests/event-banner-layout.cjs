// Run with Node, PHP on PATH, and Playwright installed (or PLAYWRIGHT_MODULE set).
const assert = require('node:assert/strict');
const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = path.resolve(__dirname, '../public_html');
const pages = {};
for (const component of ['home', 'header']) {
    for (const state of ['active', 'disabled']) {
        const result = spawnSync('php', [path.join(__dirname, 'fixtures/event-banner.php'), component, state], { encoding: 'utf8' });
        assert.equal(result.status, 0, result.stderr);
        pages[`/${component}/${state}`] = result.stdout;
    }
}
const types = { '.css': 'text/css', '.js': 'text/javascript', '.woff2': 'font/woff2', '.png': 'image/png', '.jpg': 'image/jpeg', '.svg': 'image/svg+xml' };
const server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://localhost');
    if (pages[url.pathname]) {
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        return res.end(pages[url.pathname]);
    }
    if (url.pathname === '/dashboard/hero-list.php' || url.pathname.startsWith('/api/')) {
        res.setHeader('Content-Type', 'application/json');
        return res.end(JSON.stringify({ status: 'success', data: [] }));
    }
    const file = path.resolve(root, '.' + decodeURIComponent(url.pathname));
    if (file.startsWith(root + path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
        res.setHeader('Content-Type', types[path.extname(file)] || 'application/octet-stream');
        return fs.createReadStream(file).pipe(res);
    }
    res.writeHead(404).end();
});
const closeTo = (actual, expected, label) => assert.ok(Math.abs(actual - expected) <= 1, `${label}: ${actual} != ${expected}`);
async function geometry(page, component) {
    return page.evaluate(component => {
        const rect = selector => document.querySelector(selector)?.getBoundingClientRect();
        const header = rect('.cta-topbar');
        const banner = rect('.event-global-banner');
        const content = rect(component === 'home' ? '.cta-hero' : '#test-content');
        return { top: header.top, bottom: header.bottom, bannerBottom: banner?.bottom || 0, contentTop: content.top, bannerHeight: banner?.height || 0 };
    }, component);
}
async function settle(page) {
    await page.evaluate(() => document.fonts.ready);
    await page.waitForTimeout(400); // Allow the existing navbar height transition to finish.
}
(async () => {
    let browser;
    try {
        await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
        const origin = `http://127.0.0.1:${server.address().port}`;
        browser = await chromium.launch({ headless: true, ...(process.env.BROWSER_CHANNEL ? { channel: process.env.BROWSER_CHANNEL } : {}) });
        for (const component of ['home', 'header']) {
            for (const width of [1440, 390]) {
                const context = await browser.newContext({ viewport: { width, height: 900 } });
                const page = await context.newPage();
                const errors = [];
                page.on('pageerror', error => errors.push(error.message));
                await page.goto(`${origin}/${component}/active`);
                await settle(page);
                let g = await geometry(page, component);
                closeTo(g.top, g.bannerBottom, 'Initial header follows banner');
                closeTo(g.contentTop, g.bottom, 'No initial content gap');
                await page.evaluate(() => scrollTo(0, 20));
                await settle(page);
                g = await geometry(page, component);
                closeTo(g.top, Math.max(0, g.bannerBottom), 'Partial scroll follows visible banner');
                await page.evaluate(() => scrollTo(0, 450));
                await settle(page);
                closeTo((await geometry(page, component)).top, 0, 'Scrolled header touches viewport top');
                if (process.env.BANNER_SCREENSHOTS) {
                    fs.mkdirSync(process.env.BANNER_SCREENSHOTS, { recursive: true });
                    await page.screenshot({ path: path.join(process.env.BANNER_SCREENSHOTS, `${component}-${width}-scrolled.png`) });
                }
                await page.evaluate(() => scrollTo(0, 0));
                await settle(page);
                g = await geometry(page, component);
                closeTo(g.top, g.bannerBottom, 'Return to top restores banner offset');
                closeTo(g.contentTop, g.bottom, 'Return to top has no gap');
                if (process.env.BANNER_SCREENSHOTS) {
                    await page.screenshot({ path: path.join(process.env.BANNER_SCREENSHOTS, `${component}-${width}-top.png`) });
                }
                await page.setViewportSize({ width: width === 390 ? 1440 : 390, height: 900 });
                await settle(page);
                g = await geometry(page, component);
                closeTo(g.top, g.bannerBottom, 'Resize uses actual banner height');
                closeTo(g.contentTop, g.bottom, 'Resize has no content gap');
                await page.click('.event-banner-dismiss');
                await settle(page);
                g = await geometry(page, component);
                closeTo(g.top, 0, 'Dismiss resets offset');
                closeTo(g.contentTop, g.bottom, 'Dismiss leaves no gap');
                await page.reload();
                await settle(page);
                assert.equal(await page.locator('.event-global-banner').count(), 0, 'Dismiss persists within session');
                g = await geometry(page, component);
                closeTo(g.top, 0, 'Reload after dismiss has no offset');
                closeTo(g.contentTop, g.bottom, 'Reload after dismiss has no gap');
                await page.goto(`${origin}/${component}/disabled`);
                await settle(page);
                g = await geometry(page, component);
                closeTo(g.top, 0, 'No event has no offset');
                closeTo(g.contentTop, g.bottom, 'No event has no gap');
                assert.deepEqual(errors, [], 'No browser script exceptions');
                await context.close();
                console.log(`PASS ${component}, ${width}px: initial, partial/full scroll, return, resize, dismiss, reload, no event`);
            }
        }
        const context = await browser.newContext();
        await context.addInitScript(() => Object.defineProperty(window, 'sessionStorage', { get() { throw new Error('Storage blocked'); } }));
        const page = await context.newPage();
        await page.goto(`${origin}/home/active`);
        await settle(page);
        await page.evaluate(() => scrollTo(0, 450));
        await settle(page);
        closeTo((await geometry(page, 'home')).top, 0, 'Blocked storage does not break positioning');
        await page.evaluate(() => scrollTo(0, 0));
        await page.click('.event-banner-dismiss');
        await settle(page);
        closeTo((await geometry(page, 'home')).top, 0, 'Blocked storage still allows dismissal');
        await context.close();
        console.log('PASS storage unavailable');
    } finally {
        if (browser) await browser.close();
        server.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });

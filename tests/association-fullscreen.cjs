// Run with Playwright installed (or PLAYWRIGHT_MODULE set).
// SWIPER_TEST_ASSETS may point to an offline copy of the existing Swiper 11 JS/CSS.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = path.resolve(__dirname, '../public_html');
const source = fs.readFileSync(path.join(root, 'dashboard/components/association/component.php'), 'utf8')
    .replace(/{{image(\d+)}}/g, '/dashboard/components/association/images/$1.png');
const html = `<!doctype html><html lang="fa" dir="rtl" style="overflow:scroll"><head><meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>body{margin:0;overflow:auto}#site-header{position:fixed;inset:0 0 auto;height:60px;z-index:200000;background:orange}main{padding-top:60px}#after{height:500px}</style>
    </head><body style="overflow:auto"><header id="site-header">سربرگ سایت</header><main>${source}</main><footer id="after">پایان صفحه</footer></body></html>`;
const server = http.createServer((req, res) => {
    const pathname = decodeURIComponent(new URL(req.url, 'http://localhost').pathname);
    if (pathname === '/') {
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        return res.end(html);
    }
    const file = path.resolve(root, '.' + pathname);
    if (file.startsWith(root + path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
        res.setHeader('Content-Type', { '.png': 'image/png', '.woff2': 'font/woff2' }[path.extname(file)] || 'application/octet-stream');
        return fs.createReadStream(file).pipe(res);
    }
    res.writeHead(404).end();
});
async function geometry(page) {
    return page.evaluate(() => {
        const card = document.querySelector('.charity-doc-card');
        const rect = selector => {
            const r = document.querySelector(selector).getBoundingClientRect();
            return { top: r.top, left: r.left, right: r.right, bottom: r.bottom, width: r.width, height: r.height };
        };
        return {
            viewport: { width: innerWidth, height: innerHeight },
            card: rect('.charity-doc-card'),
            viewer: rect('.charity-doc-viewer-shell'),
            swiper: rect('.charityDocSwiper'),
            image: rect('.swiper-slide-active .charity-doc-img'),
            nativeCard: document.fullscreenElement === card,
            titleOutside: !card.contains(document.querySelector('.page-header-wrapper')),
            aboveHeader: card.contains(document.elementFromPoint(10, 10)),
            protection: document.querySelector('.js-protection-layer').classList.contains('is-active'),
        };
    });
}
async function assertExpanded(page, native) {
    await page.waitForFunction(() => document.querySelector('.charity-doc-card').classList.contains('is-fullscreen'));
    await page.waitForFunction(() => {
        const shell = document.querySelector('.charity-doc-viewer-shell');
        const swiper = document.querySelector('.charityDocSwiper');
        return Math.abs(shell.clientHeight - swiper.clientHeight) < 2;
    });
    const g = await geometry(page);
    assert.equal(g.nativeCard, native, 'Only the document card enters native fullscreen');
    assert.ok(g.titleOutside && g.aboveHeader, 'Page title and site header stay outside the expanded viewer');
    for (const [actual, expected] of [[g.card.left, 0], [g.card.top, 0], [g.card.width, g.viewport.width], [g.card.height, g.viewport.height]]) {
        assert.ok(Math.abs(actual - expected) < 2, `Fullscreen bounds: ${actual} != ${expected}`);
    }
    assert.ok(g.viewer.height > g.viewport.height * 0.6, 'Document gets most of the viewport');
    assert.ok(g.swiper.bottom <= g.card.bottom + 1 && g.image.bottom <= g.viewer.bottom + 1, 'Document fits the available height');
    assert.ok(g.image.left >= g.viewer.left - 1 && g.image.right <= g.viewer.right + 1, 'Document fits the available width');
    assert.ok(!g.protection, 'Fullscreen does not leave the temporary hiding layer active');
}
(async () => {
    let browser;
    try {
        await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
        browser = await chromium.launch({ headless: true, ...(process.env.BROWSER_CHANNEL ? { channel: process.env.BROWSER_CHANNEL } : {}) });
        for (const [mode, width, height] of [['native', 1366, 768], ['unavailable', 390, 844], ['rejected', 1366, 768], ['webkit', 390, 844]]) {
            const context = await browser.newContext({ viewport: { width, height } });
            if (process.env.SWIPER_TEST_ASSETS) {
                await context.route('https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.*', route => {
                    const name = new URL(route.request().url()).pathname.split('/').pop();
                    return route.fulfill({ path: path.join(process.env.SWIPER_TEST_ASSETS, name), contentType: name.endsWith('.js') ? 'text/javascript' : 'text/css' });
                });
            }
            if (mode !== 'native') {
                await context.addInitScript(mode => {
                    const nativeRequest = Element.prototype.requestFullscreen;
                    if (mode === 'rejected') {
                        Element.prototype.requestFullscreen = () => Promise.reject(new Error('Fullscreen denied'));
                    } else {
                        Object.defineProperty(Element.prototype, 'requestFullscreen', { value: undefined, configurable: true });
                        Object.defineProperty(Element.prototype, 'webkitRequestFullscreen', {
                            value: mode === 'webkit' ? function () { return nativeRequest.call(this); } : undefined,
                            configurable: true,
                        });
                    }
                }, mode);
            }
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            await page.goto(`http://127.0.0.1:${server.address().port}/`);
            await page.evaluate(() => document.fonts.ready);
            await page.waitForFunction(() => [...document.querySelectorAll('.charity-doc-img')].every(img => img.complete && img.naturalWidth > 0));
            await page.evaluate(() => document.querySelector('.charityDocSwiper').swiper.slideTo(2, 0));
            await page.click('.js-fullscreen');
            const native = mode === 'native' || mode === 'webkit';
            await assertExpanded(page, native);
            assert.equal(await page.locator('.js-page-current').innerText(), '3', 'Current page retained');
            assert.equal(await page.locator('.js-fullscreen').getAttribute('aria-pressed'), 'true');
            await page.click('.js-zoom-in');
            assert.equal(await page.locator('.js-zoom-label').innerText(), '120%', 'Zoom still works');
            await page.click('.js-reset-zoom');
            await page.click('.charity-doc-next');
            assert.equal(await page.locator('.js-page-current').innerText(), '4', 'Navigation still works');
            await page.waitForFunction(() => !document.querySelector('.charityDocSwiper').swiper.animating);
            await assertExpanded(page, native);
            if (mode === 'unavailable') {
                await page.setViewportSize({ width: 844, height: 390 });
                await assertExpanded(page, false);
            }
            if (process.env.ASSOCIATION_SCREENSHOTS) {
                await page.screenshot({ path: path.join(process.env.ASSOCIATION_SCREENSHOTS, `association-${mode}.png`) });
            }
            if (mode === 'native') await page.keyboard.press('Escape');
            else if (native) await page.click('.js-fullscreen');
            else await page.keyboard.press('Escape');
            await page.waitForFunction(() => !document.querySelector('.charity-doc-card').classList.contains('is-fullscreen'));
            assert.equal(await page.evaluate(() => document.fullscreenElement), null, 'Native fullscreen exited');
            assert.deepEqual(await page.evaluate(() => [document.documentElement.style.overflow, document.body.style.overflow]), ['scroll', 'auto'], 'Scrolling restored');
            assert.equal(await page.locator('.js-fullscreen').getAttribute('aria-pressed'), 'false');
            assert.equal(await page.locator('.charity-doc-card').getAttribute('aria-modal'), null);
            // Repeated entry and the explicit exit button work in fallback mode too.
            await page.click('.js-fullscreen');
            await assertExpanded(page, native);
            await page.click('.js-fullscreen');
            await page.waitForFunction(() => !document.querySelector('.charity-doc-card').classList.contains('is-fullscreen'));
            if (mode === 'native') {
                await page.evaluate(() => document.dispatchEvent(new KeyboardEvent('keyup', { key: 'PrintScreen' })));
                assert.ok(await page.locator('.js-protection-layer').evaluate(el => el.classList.contains('is-active')), 'Existing document protection retained');
                await page.waitForFunction(() => !document.querySelector('.js-protection-layer').classList.contains('is-active'));
            }
            assert.deepEqual(errors, [], 'No rejected fullscreen promises or browser errors');
            console.log(`PASS association fullscreen: ${mode}, ${width}x${height}`);
            await context.close();
        }
    } finally {
        if (browser) await browser.close();
        server.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });

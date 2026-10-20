// PHP on PATH; set PLAYWRIGHT_MODULE and BROWSER_CHANNEL for a bundled runtime.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = path.resolve(__dirname, '../public_html');
const rendered = spawnSync('php', [path.join(__dirname, 'fixtures/home-layout.php')], { encoding: 'utf8' });
assert.equal(rendered.status, 0, rendered.stderr || rendered.stdout.slice(-2000));
assert.ok(!rendered.stderr, rendered.stderr);
const image = '/dashboard/components/hamrah/images/1.webp';
const news = Array.from({ length: 3 }, (_, i) => ({
    title: 'گسترش خدمات مراقبت‌های حمایتی و تسکینی برای بیماران و خانواده‌های آنان در سراسر کشور',
    url: `/news.php?id=${i + 1}`, image, category: 'اخبار و رویدادهای مکسا', date: '1405/07/28', read_time: 3
}));
const types = { '.css': 'text/css', '.js': 'text/javascript', '.woff2': 'font/woff2', '.png': 'image/png', '.jpg': 'image/jpeg', '.webp': 'image/webp', '.svg': 'image/svg+xml' };
const server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://localhost');
    if (url.pathname === '/') {
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        return res.end(rendered.stdout);
    }
    if (url.pathname.endsWith('.php') || url.pathname.startsWith('/api/')) {
        res.setHeader('Content-Type', 'application/json');
        if (url.pathname.includes('recent-news-feed')) return res.end(JSON.stringify({ items: news }));
        if (url.pathname.includes('hero-list')) return res.end(JSON.stringify({ status: 'success', data: [
            { title: 'مکسا؛ مؤسسه نیکوکاری کنترل سرطان ایرانیان', description: 'مدیریت بهینه سرطان و ارائه رایگان خدمات تخصصی مراقبت‌های حمایتی و تسکینی', image, button_link: '/single-fundraising-option' },
            { title: 'همراه با هم به زندگی بیماران نور هدیه کنیم', description: 'در مسیر درمان و امید همراه شما هستیم.', image, button_link: '/onlinedonation' }
        ] }));
        return res.end(JSON.stringify({ status: 'success', success: true, data: [] }));
    }
    const file = path.resolve(root, '.' + decodeURIComponent(url.pathname));
    if (file.startsWith(root + path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
        res.setHeader('Content-Type', types[path.extname(file)] || 'application/octet-stream');
        return fs.createReadStream(file).pipe(res);
    }
    res.writeHead(404).end();
});
const selectors = ['.cta-header', '.cta-brand', '.cta-left', '.cta-content', '.cta-text', '.cta-cards', '.macsa-gallery', '.skill-hero-container', '.skill-hero-left', '.skill-hero-right', '.skill-video-column', '.skill-main-image', '.maxsa-container', '.maxsa-left', '.maxsa-right', '.rnhv2-grid', '.rnhv2-main-content', '.travel-box', '.gf-container', '.gf-contact', '.gf-bottom'];
async function assertInside(page, child, parent, label) {
    const c = await page.locator(child).first().boundingBox();
    const p = await page.locator(parent).first().boundingBox();
    assert.ok(c && p && c.width > 0 && c.height > 0, `${label}: visible content`);
    assert.ok(c.x >= p.x - 1 && c.x + c.width <= p.x + p.width + 1 && c.y >= p.y - 1 && c.y + c.height <= p.y + p.height + 1, `${label}: content clipped`);
}
async function inspect(page) {
    return page.evaluate(selectors => Object.fromEntries(selectors.map(selector => {
        const el = document.querySelector(selector), r = el.getBoundingClientRect();
        return [selector, { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height), scroll: el.scrollWidth, client: el.clientWidth }];
    })), selectors);
}
(async () => {
    let browser;
    try {
        await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
        browser = await chromium.launch({ headless: true, ...(process.env.BROWSER_CHANNEL ? { channel: process.env.BROWSER_CHANNEL } : {}) });
        const widths = process.env.HOME_WIDTHS ? process.env.HOME_WIDTHS.split(',').map(Number) : [320, 360, 390, 540, 700, 768, 900, 1024, 1100, 1200, 1279, 1280, 1360, 1440, 1920];
        const viewports = widths.map(width => ({ width, height: 850 }));
        if (!process.env.HOME_WIDTHS) viewports.push({ width: 568, height: 390 }, { width: 844, height: 390 });
        for (const { width, height } of viewports) {
            const context = await browser.newContext({ viewport: { width, height }, isMobile: width <= 540 || height < 500, hasTouch: width <= 900 });
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', e => errors.push(e.message));
            await page.route('https://**/*', route => route.abort());
            await page.goto(`http://127.0.0.1:${server.address().port}/`);
            await page.evaluate(() => document.fonts.ready);
            await page.evaluate(async () => {
                await Promise.all([...document.images].map(img => {
                    img.loading = 'eager';
                    return img.decode().catch(() => {});
                }));
            });
            await page.waitForTimeout(450);
            const boxes = await inspect(page);
            if (process.env.HOME_LAYOUT_AUDIT) console.log(width, JSON.stringify(boxes));
            if (process.env.HOME_SCREENSHOTS && [390, 768, 1440].includes(width)) {
                fs.mkdirSync(process.env.HOME_SCREENSHOTS, { recursive: true });
                await page.screenshot({ path: path.join(process.env.HOME_SCREENSHOTS, `home-${width}.png`), fullPage: true });
                for (const [name, selector] of Object.entries({ hero: '.cta-hero', services: '.skill-hero', news: '.rnhv2-section' })) {
                    await page.locator(selector).screenshot({ path: path.join(process.env.HOME_SCREENSHOTS, `${name}-${width}.png`) });
                }
            }
            if (!process.env.HOME_LAYOUT_AUDIT) {
                for (const [selector, box] of Object.entries(boxes)) {
                    assert.ok(box.x >= -1 && box.x + box.w <= width + 1, `${width}px ${selector} leaves viewport: ${JSON.stringify(box)}`);
                    if (selector !== '.macsa-gallery') assert.ok(box.scroll <= box.client + 1, `${width}px ${selector} clips content: ${JSON.stringify(box)}`);
                }
                for (const image of ['.skill-video-box img', '.skill-main-image img', '.maxsa-right img']) {
                    assert.ok((await page.locator(image).boundingBox()).width >= 100, `${width}px ${image} must not collapse`);
                }
                await assertInside(page, '.rnhv2-main-content', '.rnhv2-main', `${width}px long news headline`);
                await assertInside(page, '#heroTitle', '.cta-hero', `${width}px hero title`);
                await assertInside(page, '#heroBtn', '.cta-hero', `${width}px hero action`);
                if (width <= 700) {
                    // In landscape the controls can be below the image; make them
                    // visible before measuring page movement caused by a dot click.
                    await page.locator('.macsa-dots').scrollIntoViewIfNeeded();
                    const scrollY = await page.evaluate(() => window.scrollY);
                    for (const index of [4, 0, 2]) {
                        await page.locator('.macsa-dot').nth(index).click();
                        await page.waitForTimeout(650);
                        const offset = await page.evaluate(index => {
                            const gallery = document.querySelector('.macsa-gallery').getBoundingClientRect();
                            const card = document.querySelectorAll('.macsa-gallery > .gallery-item')[index].getBoundingClientRect();
                            return Math.abs(card.x + card.width / 2 - gallery.x - gallery.width / 2);
                        }, index);
                        assert.ok(offset < 2, `${width}px RTL gallery dot ${index + 1} must center its card, offset ${offset}`);
                        assert.equal(await page.locator('.macsa-dot').nth(index).getAttribute('aria-current'), 'true');
                    }
                    assert.ok(Math.abs(await page.evaluate(() => window.scrollY) - scrollY) < 2, 'Gallery does not move the whole page');
                } else {
                    const rows = await page.locator('.macsa-gallery > .gallery-item').evaluateAll(cards => cards.map(c => Math.round(c.getBoundingClientRect().bottom)));
                    assert.ok(Math.max(...rows) - Math.min(...rows) <= 1, `${width}px gallery keeps all five cards on one row`);
                }
                if (width <= 1279) {
                    await page.locator('#menuToggle').click();
                    await page.waitForTimeout(450);
                    assert.equal(await page.locator('#menuToggle').getAttribute('aria-expanded'), 'true');
                    await assertInside(page, '.mobile-menu-header', '#mobileMenu', 'Mobile menu header');
                    await page.locator('#mobileMenu .mega-toggle').first().click();
                    assert.ok(await page.locator('#mobileMenu .mega-menu-content.active').first().isVisible(), 'Mobile submenu opens');
                    await page.keyboard.press('Escape');
                    assert.equal(await page.locator('#menuToggle').getAttribute('aria-expanded'), 'false');
                    await page.locator('#menuToggle').click();
                    await page.setViewportSize({ width: 1440, height: 850 });
                    await page.waitForTimeout(450);
                    assert.equal(await page.locator('body').evaluate(el => el.classList.contains('mobile-menu-open')), false, 'Resize releases page scroll lock');
                } else {
                    const header = await page.locator('.cta-center').boundingBox();
                    const menu = await page.locator('.cta-menu').boundingBox();
                    assert.ok(menu.x >= header.x - 1 && menu.x + menu.width <= header.x + header.width + 1, `${width}px desktop navigation fits without overlapping actions: ${JSON.stringify({ header, menu })}`);
                }
                assert.deepEqual(errors, [], 'No browser script exceptions');
            }
            await context.close();
            console.log(`PASS home ${width}x${height}px`);
        }
    } finally {
        if (browser) await browser.close();
        server.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });

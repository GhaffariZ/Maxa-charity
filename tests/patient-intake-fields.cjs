// Run with Playwright installed or NODE_PATH pointing to the bundled packages.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');
const { chromium } = require('playwright');
const root = path.resolve(__dirname, '../public_html');
const component = fs.readFileSync(path.join(root, 'dashboard/components/patient_intake/component.php'), 'utf8');
const server = http.createServer((req, res) => {
    if (req.url.startsWith('/webfont/')) {
        res.setHeader('Content-Type', 'font/woff2');
        return fs.createReadStream(path.join(root, 'webfont/Vazirmatn[wght].woff2')).pipe(res);
    }
    res.setHeader('Content-Type', 'text/html; charset=utf-8');
    res.end('<!doctype html><html lang="fa" dir="rtl"><meta name="viewport" content="width=device-width, initial-scale=1"><style>*{box-sizing:border-box}body{margin:0;background:#f7f7f7}</style>' + component + '</html>');
});
(async () => {
    let browser;
    try {
        await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
        browser = await chromium.launch({ channel: process.env.BROWSER_CHANNEL || 'chrome', headless: true });
        const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
        await page.clock.install({ time: new Date('2026-10-26T12:00:00Z') });
        let submissions = 0;
        let payload;
        let rejectSubmission = true;
        await page.route('**/api/medical-records/intake', async route => {
            submissions++;
            payload = route.request().postDataBuffer().toString();
            if (rejectSubmission) {
                return route.fulfill({ status: 422, json: { error: { message: 'داده نامعتبر', fields: { identity_code: 'خطای اعتبارسنجی کد ملی' } } } });
            }
            await route.fulfill({ json: { data: { record_id: 123, user: {} } } });
        });
        await page.goto(`http://127.0.0.1:${server.address().port}`);
        for (const selector of ['#miBirthDay', '#miBirthMonth', '#miBirthYear', '[name=gender]']) {
            assert.equal(await page.locator(selector).evaluate(el => getComputedStyle(el).backgroundColor), 'rgb(255, 255, 255)', 'White field background: ' + selector);
        }
        await page.locator('[name=full_name]').fill('کاربر آزمایشی');
        await page.locator('[name=phone]').fill('09123456789');
        await page.locator('[name=province]').fill('تهران');
        await page.locator('[name=city]').fill('تهران');
        await page.locator('#consent').check();
        const identity = page.locator('#miIdentity');
        for (const invalid of ['1111111111', '0000000000', '0084575949', '1234', '00845759x8']) {
            await identity.fill(invalid);
            await page.locator('.mi-submit').click();
            assert.equal(submissions, 0, 'Invalid national code must block submission');
            assert.equal(await identity.evaluate(el => el.checkValidity()), false);
        }
        await identity.fill('۰۰۸٤٥٧۵۹۴۸');
        assert.equal(await identity.inputValue(), '0084575948');
        assert.equal(await identity.evaluate(el => el.checkValidity()), true);
        await page.locator('#miForeign').check();
        assert.equal(await page.locator('#miIdentityLabel').textContent(), 'کد فراگیر');
        assert.equal(await identity.getAttribute('maxlength'), '12');
        await identity.fill('۰۱۲۳۴۵۶۷۸۹۰۱');
        assert.equal(await identity.evaluate(el => el.checkValidity()), true);
        await page.locator('#miForeign').uncheck();
        assert.equal(await identity.evaluate(el => el.checkValidity()), false);
        await identity.fill('0084575948');
        await page.locator('#miBirthYear').selectOption('1403');
        await page.locator('#miBirthMonth').selectOption('12');
        await page.locator('#miBirthDay').selectOption('30');
        assert.equal(await page.locator('#miBirthDay').inputValue(), '30');
        await page.locator('#miBirthYear').selectOption('1404');
        assert.equal(await page.locator('#miBirthDay').inputValue(), '');
        assert.equal(await page.locator('#miBirthDay option[value="30"]').count(), 0);
        await page.locator('.mi-submit').click();
        assert.equal(submissions, 0, 'Incomplete birth date must block submission');
        await page.locator('#miBirthYear').selectOption('1405');
        await page.locator('#miBirthMonth').selectOption('8');
        await page.locator('#miBirthDay').selectOption('5');
        assert.equal(await page.locator('#miBirthDay').evaluate(el => el.checkValidity()), false, 'Future birth date');
        await page.locator('#miBirthYear').selectOption('1370');
        await page.locator('#miBirthMonth').selectOption('7');
        await page.locator('#miBirthDay').selectOption('15');
        await page.evaluate(() => document.fonts.ready);
        const output = process.env.SCREENSHOT_DIR;
        if (output) await page.screenshot({ path: path.join(output, 'patient-intake-desktop.png'), fullPage: true });
        await page.setViewportSize({ width: 375, height: 812 });
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), true, 'Mobile overflow');
        if (output) await page.screenshot({ path: path.join(output, 'patient-intake-mobile.png'), fullPage: true });
        await page.locator('.mi-submit').click();
        await page.locator('.mi-form-message').waitFor();
        assert.equal(await page.locator('.mi-form-message').textContent(), 'خطای اعتبارسنجی کد ملی');
        rejectSubmission = false;
        await page.locator('.mi-submit').click();
        await page.locator('.mi-success-message').waitFor();
        assert.equal(submissions, 2);
        assert.ok(payload.includes('0084575948'));
        assert.ok(payload.includes('name="birth_year"\r\n\r\n1370'));
        await page.reload();
        await page.locator('[name=full_name]').fill('کاربر آزمایشی');
        await page.locator('[name=phone]').fill('09123456789');
        await page.locator('[name=province]').fill('تهران');
        await page.locator('[name=city]').fill('تهران');
        await page.locator('#consent').check();
        await page.locator('#miForeign').check();
        await identity.fill('012345678901');
        await page.locator('.mi-submit').click();
        await page.locator('.mi-success-message').waitFor();
        assert.equal(submissions, 3, 'Foreign applicant can submit without optional birth date');
        assert.ok(payload.includes('name="is_foreign"\r\n\r\n1'));
        assert.ok(payload.includes('012345678901'));
        console.log('patient intake fields and mobile layout: OK');
    } finally {
        if (browser) await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
})().catch(error => { console.error(error); process.exitCode = 1; });

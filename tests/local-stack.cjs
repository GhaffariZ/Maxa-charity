// Integration check for tools/local.ps1: only localhost and the private local DB.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = path.resolve(__dirname, '..');
const secrets = JSON.parse(fs.readFileSync(path.join(root, '.local/conf/secrets.json')));
const localEnv = fs.readFileSync(path.join(root, '.local/conf/api.env'), 'utf8');
for (const setting of ['APP_ENV=local', 'MAIL_HOST=127.0.0.1', 'PAYMENT_GATEWAY=stub']) {
    assert.ok(localEnv.split(/\r?\n/).includes(setting), 'Integration tests require local database/email/payment settings');
}
const php = path.join(root, '.local/runtime/php/php.exe');
const metadata = spawnSync(php, ['-c', path.join(root, '.local/conf/php.ini'), '-r', `
$cfg=json_decode(file_get_contents('.local/conf/secrets.json'),true);
$db=new PDO('mysql:host=127.0.0.1;dbname=macsa_local;charset=utf8mb4','macsa_local',$cfg['dbPassword']);
$pages=$db->query("SELECT DISTINCT slug FROM pages WHERE status='published' AND branch_id=(SELECT id FROM branches WHERE is_hq=1 LIMIT 1)")->fetchAll(PDO::FETCH_COLUMN);
$branches=$db->query("SELECT slug FROM branches WHERE status='active'")->fetchAll(PDO::FETCH_COLUMN);
$news=$db->query("SELECT id FROM news WHERE status='published' AND publish_date<=NOW() ORDER BY publish_date DESC LIMIT 1")->fetchColumn();
echo json_encode(compact('pages','branches','news'));
`], { cwd: root, encoding: 'utf8' });
assert.equal(metadata.status, 0, metadata.stderr);
const data = JSON.parse(metadata.stdout);
const origin = 'http://localhost:8080';
async function healthy(response, label) {
    assert.equal(response.status(), 200, `${label}: HTTP ${response.status()}`);
    const body = await response.text();
    assert.ok(!/Fatal error:|DATABASE ERROR:|Uncaught (?:PDOException|Error)|Warning: require/.test(body), `${label}: PHP error`);
    return body;
}
(async () => {
    const browser = await chromium.launch({ headless: true, channel: process.env.BROWSER_CHANNEL || 'chrome' });
    try {
        const context = await browser.newContext();
        const routes = new Set(['/home', '/news.php', '/branches.php', '/contactus', '/courses', '/macsapedia', '/stand-order', '/benefactor-dashboard/', '/benefactor-dashboard/campaigns', '/api/health', '/api/campaigns', '/api-stories.php?limit=3', '/dashboard/hero-list.php', '/dashboard/recent-news-feed.php?limit=3']);
        for (const slug of [...data.pages, ...data.branches]) {
            if (/^[a-zA-Z0-9_-]+$/.test(slug)) routes.add('/' + slug);
        }
        if (data.news) routes.add('/' + data.news + '/');
        for (const route of routes) await healthy(await context.request.get(origin + route), route);
        console.log(`PASS ${routes.size} database-backed pages, branches, feeds, API and React routes`);
        for (const route of ['/api/src/Core/Config.php', '/core/db-config.php', '/dashboard/components/heroindex/component.php']) {
            assert.equal((await context.request.get(origin + route)).status(), 403, `Protected source: ${route}`);
        }
        const loginHtml = await healthy(await context.request.get(origin + '/dashboard/login.php'), 'Admin sign-in');
        const csrf = loginHtml.match(/name="csrf_token" value="([^"]+)"/)[1];
        const admin = await context.request.post(origin + '/dashboard/login.php', { form: { username: 'local-admin', password: secrets.loginPassword, csrf_token: csrf } });
        await healthy(admin, 'Authenticated admin dashboard');
        assert.ok(admin.url().endsWith('/dashboard/index.php'), 'Admin session must survive redirect');
        for (const route of ['/dashboard/news-list.php', '/dashboard/hero-management.php', '/dashboard/financial-management.php']) {
            await healthy(await context.request.get(origin + route), route);
        }
        const login = await context.request.post(origin + '/api/auth/login', { data: { email: 'local-donor@example.test', password: secrets.loginPassword } });
        const session = JSON.parse(await healthy(login, 'Donor sign-in'));
        const token = session.data.access_token;
        assert.ok(token, 'Donor access token');
        for (const route of ['/api/user/me', '/api/user/dashboard', '/api/donations', '/api/notifications']) {
            await healthy(await context.request.get(origin + route, { headers: { Authorization: `Bearer ${token}` } }), route);
        }
        console.log('PASS admin session, donor login, profile, dashboard, donations, notifications');
        const email = `local-test-${Date.now()}@example.test`;
        const registration = await context.request.post(origin + '/api/auth/register', { data: { email, password: 'LocalPreview-2026!', first_name: 'Local', last_name: 'Test' } });
        assert.equal(registration.status(), 201, await registration.text());
        const inbox = await context.request.get('http://localhost:8025/api/v1/messages');
        const messages = await inbox.json();
        const message = messages.messages.find(m => m.To.some(to => to.Address === email));
        assert.ok(message, 'Verification email must arrive in local Mailpit through SMTP/TLS');
        const mail = await (await context.request.get(`http://localhost:8025/api/v1/message/${message.ID}`)).json();
        const link = (mail.HTML + mail.Text).match(/http:\/\/localhost:8080\/benefactor-dashboard\/verify\?token=([a-zA-Z0-9_-]+)/);
        assert.ok(link, 'Verification link points to the local React panel');
        await healthy(await context.request.post(origin + '/api/auth/verify-email', { data: { token: link[1] } }), 'Email verification');
        console.log('PASS registration, captured SMTP email, local verification link, account activation');
        const page = await context.newPage();
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.route('https://**/*', route => route.abort());
        await page.goto(origin + '/home', { waitUntil: 'domcontentloaded' });
        await page.locator('.rnhv2-main').waitFor();
        await page.waitForLoadState('networkidle'); // Let the shared header finish refreshing the session.
        const dashboardResponse = page.waitForResponse(response => new URL(response.url()).pathname === '/api/user/dashboard');
        await page.goto(origin + '/benefactor-dashboard/', { waitUntil: 'domcontentloaded' });
        assert.equal((await dashboardResponse).status(), 200, 'React dashboard loads authenticated local API data');
        assert.ok((await page.locator('body').innerText()).trim().length > 40, 'React application renders content');
        assert.deepEqual(errors, [], 'No browser script exceptions');
        console.log('PASS live home feed and built React UI');
        await context.close();
    } finally { await browser.close(); }
})().catch(error => { console.error(error.message); process.exitCode = 1; });

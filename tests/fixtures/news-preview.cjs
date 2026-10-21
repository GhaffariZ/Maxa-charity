// Local visual fixture: no database, authentication, or production writes.
const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');
const {execFileSync} = require('node:child_process');

const root = path.resolve(__dirname, '../..');
const webRoot = path.join(root, 'public_html');
const headerPath = 'public_html/dashboard/components/header/component.php';
const stripPhp = source => source.replace(/<\?(?:php|=)[\s\S]*?\?>/g, '');
const originalHeader = stripPhp(execFileSync('git', ['show', `HEAD:${headerPath}`], {cwd: root, encoding: 'utf8'}));
const view = fs.readFileSync(path.join(webRoot, 'dashboard/news-view.php'), 'utf8');
const css = view.match(/<style>([\s\S]*?)<\/style>/)[1];
const article = `<main class="news-detail-wrapper"><div class="news-detail-layout">
<article class="article-container has-hero"><header class="article-hero-banner">
<img class="article-hero-img" src="/dashboard/components/aboutus/images/1.webp" alt="تصویر خبر">
<div class="article-hero-gradient"></div><div class="article-hero-content">
<span class="article-hero-category">اخبار مکسا</span><h1 class="article-hero-title">خدمات حمایتی برای بیماران و خانواده‌ها</h1>
<div class="article-hero-meta">روابط عمومی مکسا — ۱۹ مهر ۱۴۰۵</div></div></header>
<div class="article-body-wrapper"><div class="article-content"><h2>همراه بیماران</h2><p>متن نمونه برای بررسی صفحهٔ خبر، تصویر افقی و منوی سایت.</p></div></div>
</article></div></main>`;

http.createServer((req, res) => {
    const url = new URL(req.url, 'http://localhost');
    if (url.pathname === '/' || url.pathname === '/before') {
        const currentHeader = stripPhp(fs.readFileSync(path.join(root, headerPath), 'utf8'));
        const before = url.pathname === '/before';
        res.setHeader('Content-Type', 'text/html');
        const shell = before ? '' : '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>پیش‌نمایش اصلاح خبر</title></head><body>';
        res.end(shell + (before ? originalHeader : currentHeader) + `<style>${css}</style>` + article + (before ? '' : '</body></html>'));
        return;
    }
    if (url.pathname.startsWith('/api/')) {
        res.writeHead(401, {'Content-Type': 'application/json'}).end('{}');
        return;
    }
    if (!/^\/(webfont\/|dashboard\/components\/(header|aboutus)\/images\/)/.test(url.pathname)) {
        res.writeHead(404).end(); return;
    }
    const file = path.resolve(webRoot, '.' + decodeURIComponent(url.pathname));
    if (!file.startsWith(webRoot + path.sep) || !fs.existsSync(file) || !fs.statSync(file).isFile()) {
        res.writeHead(404).end(); return;
    }
    const types = {'.woff2': 'font/woff2', '.png': 'image/png', '.webp': 'image/webp'};
    res.setHeader('Content-Type', types[path.extname(file)] || 'application/octet-stream');
    fs.createReadStream(file).pipe(res);
}).listen(8765, '127.0.0.1', () => console.log('News preview: http://127.0.0.1:8765 (original header: /before)'));

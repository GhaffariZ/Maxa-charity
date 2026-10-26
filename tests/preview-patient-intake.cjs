// Visual preview only; saving records requires the PHP API and local database.
const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '../public_html');
const port = Number(process.env.PORT || 8000);
const server = http.createServer((req, res) => {
    const pathname = new URL(req.url, 'http://localhost').pathname;
    if (pathname === '/api/medical-records/intake') {
        res.writeHead(501, { 'Content-Type': 'application/json; charset=utf-8' });
        return res.end(JSON.stringify({ error: { message: 'این صفحه پیش‌نمایش محلی است. برای ذخیره پرونده، سرور PHP و پایگاه داده باید راه‌اندازی شوند.' } }));
    }
    if (pathname === '/webfont/Vazirmatn[wght].woff2' || pathname === '/webfont/Vazirmatn%5Bwght%5D.woff2') {
        res.writeHead(200, { 'Content-Type': 'font/woff2' });
        return res.end(fs.readFileSync(path.join(root, 'webfont/Vazirmatn[wght].woff2')));
    }
    if (pathname !== '/' && pathname !== '/patientintake') {
        res.writeHead(404);
        return res.end('Not found');
    }
    const component = fs.readFileSync(path.join(root, 'dashboard/components/patient_intake/component.php'), 'utf8');
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(`<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>پیش‌نمایش فرم پذیرش بیمار</title>
        <style>*{box-sizing:border-box}body{margin:0;background:#f7f7f7}</style>
        </head><body>${component}</body></html>`);
});
server.on('error', error => { console.error(error.message); process.exitCode = 1; });
server.listen(port, '127.0.0.1', () => console.log(`Patient intake preview: http://127.0.0.1:${port}/patientintake`));

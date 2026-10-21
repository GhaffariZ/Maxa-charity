const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.resolve(__dirname, '..');
const editor = fs.readFileSync(path.join(root, 'public_html/dashboard/news-create.php'), 'utf8');
const start = editor.indexOf('async function uploadInlineImage(file)');
const end = editor.indexOf('\nfunction replaceSelectedImage()', start);
assert.ok(start >= 0 && end > start, 'The editor upload helper is missing.');
const uploadSource = editor.slice(start, end);

async function runUpload(response, verifyRequest = () => {}) {
    const context = vm.createContext({
        FormData, CSRF_TOKEN: 'test-csrf',
        fetch: async (url, options) => {
            assert.equal(url, 'upload-inline-image.php');
            assert.equal(options.method, 'POST');
            assert.equal(options.credentials, 'same-origin');
            assert.equal(options.headers['X-CSRF-Token'], 'test-csrf');
            assert.equal(options.body.get('csrf_token'), 'test-csrf');
            assert.ok(options.body.get('image'));
            verifyRequest(options);
            return response;
        },
    });
    vm.runInContext(uploadSource, context);
    return context.uploadInlineImage(new Blob(['image'], {type: 'image/png'}));
}

async function main() {
    assert.equal(await runUpload({ok: true, json: async () => ({url: '/uploads/editor/image.png'})}), '/uploads/editor/image.png');
    await assert.rejects(runUpload({ok: false, status: 500, json: async () => ({error: 'GD is unavailable'})}), /GD is unavailable/);
    await assert.rejects(runUpload({ok: false, status: 413, json: async () => { throw new Error('HTML response'); }}), /حجم درخواست/);
    await assert.rejects(runUpload({ok: false, status: 419, json: async () => { throw new Error('CSRF response'); }}), /نشست مدیریت/);
    await assert.rejects(runUpload({ok: true, status: 200, redirected: true, json: async () => { throw new Error('Login page'); }}), /نشست مدیریت/);
    await assert.rejects(runUpload({ok: true, status: 200, json: async () => ({})}), /پاسخ نامعتبر/);

    // Compile every editor script, replacing PHP output with a neutral literal.
    let scripts = 0;
    for (const match of editor.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/gi)) {
        const js = match[1].replace(/<\?(?:php|=)[\s\S]*?\?>/g, 'null');
        new vm.Script(js, {filename: `news-editor-script-${++scripts}.js`});
    }
    console.log(`News editor regression checks passed; ${scripts} scripts compiled.`);
}

main().catch(error => { console.error(error); process.exitCode = 1; });

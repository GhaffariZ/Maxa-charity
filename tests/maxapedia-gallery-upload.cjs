// Exercise the real upload controller with native File/FormData and a 20-file server limit.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../public_html/dashboard/assets/js/maxapedia-gallery-upload.js'), 'utf8');
async function scenario(count, mode) {
  let submit, destination, calls = 0, alerts = 0;
  const elements = Object.fromEntries(['galleryUploadSubmitBtn', 'galleryCompBox', 'galleryCompStatus', 'galleryCompBar', 'galleryCompPercent', 'gallerySelectedSummary'].map(id => [id, {style: {}, setAttribute() {}}]));
  const input = {files: Array.from({length: count}, (_, i) => new File(['image'], `photo-${i}.jpg`, {type: 'image/jpeg'}))};
  const form = {
    // Real forms expose <input name="action"> here, shadowing the native URL property.
    action: {name: 'action', value: 'gallery_upload_photos', toString() { return '[object HTMLInputElement]'; }},
    getAttribute(name) { assert.equal(name, 'action'); return 'maxapedia.php?section=gallery'; },
    addEventListener(name, listener) { submit = listener; },
    querySelectorAll() { return [{name: 'csrf_token', value: 'test-csrf'}, {name: 'action', value: 'gallery_upload_photos'}, {name: 'album_id', value: '7'}]; }
  };
  class Transfer {
    files = [];
    items = {add: file => this.files.push(file)};
  }
  const context = {
    window: {location: {href: 'https://example.test/dashboard/maxapedia.php?section=gallery&album_id=7', assign(url) { destination = url; }}},
    document: {getElementById: id => elements[id]}, URL, FormData, DataTransfer: Transfer,
    alert() { alerts++; },
    async fetch(url, options) {
      calls++;
      const body = options.body;
      assert.equal(new URL(url).pathname, '/dashboard/maxapedia.php', 'POST must reach the PHP handler despite the input named action');
      assert.equal(new URL(url).searchParams.get('upload_response'), 'json');
      assert.equal(body.get('csrf_token'), 'test-csrf');
      assert.equal(body.get('album_id'), '7');
      const images = body.getAll('gallery_photos[]');
      assert.ok(images.length <= 20, 'PHP rejects files beyond its 20-file limit');
      assert.equal(images.length, 1, 'Only the current file is sent, including on retries');
      assert.equal(input.disabled, true);
      if (mode === 'network' && calls === 3) throw new Error('Connection lost');
      if (mode === 'html' && calls === 3) return new Response('<html>Login</html>');
      if (mode === 'partial' && images[0].name === 'photo-5.jpg') {
        return Response.json({ok: false, error: 'Invalid image'}, {status: 400});
      }
      return Response.json({ok: true, uploaded: 1, errors: []});
    }
  };
  vm.runInNewContext(source, context);
  context.window.maxapediaGalleryUpload(form, input, async file => file);
  await submit({preventDefault() {}});
  return {input, elements, destination, calls, alerts, submit};
}
(async () => {
  for (const count of [21, 100]) {
    const run = await scenario(count);
    assert.equal(run.calls, count);
    assert.equal(new URL(run.destination).pathname, '/dashboard/maxapedia.php');
    assert.equal(new URL(run.destination).searchParams.get('album_id'), '7');
    assert.equal(new URL(run.destination).searchParams.get('msg'), 'photos_uploaded');
  }
  const partial = await scenario(21, 'partial');
  assert.equal(partial.calls, 21);
  assert.equal(partial.destination, undefined, 'Never show complete success for a partial upload');
  assert.deepEqual(partial.input.files.map(f => f.name), ['photo-5.jpg']);
  assert.match(partial.elements.galleryCompStatus.textContent, /photo-5.jpg.*Invalid image/);
  assert.equal(partial.elements.galleryUploadSubmitBtn.disabled, false);
  await partial.submit({preventDefault() {}});
  assert.deepEqual(partial.input.files.map(f => f.name), ['photo-5.jpg'], 'Retry preserves only failed files');
  for (const mode of ['network', 'html']) {
    const run = await scenario(21, mode);
    assert.equal(run.calls, 3, 'Stop on uncertain outcomes; never silently duplicate an upload');
    assert.equal(run.destination, undefined);
    assert.equal(run.input.files.length, 19);
    assert.equal(run.elements.galleryUploadSubmitBtn.disabled, true);
    await run.submit({preventDefault() {}});
    assert.equal(run.input.files.length, 19, 'A second submit cannot resend an uncertain upload');
  }
  const tooMany = await scenario(101);
  assert.equal(tooMany.calls, 0);
  assert.equal(tooMany.alerts, 1);
  console.log('Gallery uploads passed: 21 and 100 files, partial errors, lost responses, HTML responses, selection limit.');
})().catch(error => { console.error(error); process.exitCode = 1; });

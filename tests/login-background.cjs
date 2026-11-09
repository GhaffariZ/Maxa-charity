const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const script = fs.readFileSync(path.join(__dirname, '../public_html/dashboard/assets/js/login-background.js'), 'utf8');
const engine = fs.readFileSync(path.join(__dirname, '../public_html/dashboard/assets/js/vendor/wayglim.js'), 'utf8');
let settings, cleared = 0, hasCanvas = true;
const pauseStates = [], events = {};
const toggle = { setAttribute(name, value) { this[name] = value; }, addEventListener: (name, callback) => { events[name] = callback; } };
const media = { matches: false, addEventListener: (name, callback) => { events.mediaChange = callback; } };
const background = { replaceChildren: () => cleared++, appendChild() { hasCanvas = true; }, querySelector: () => hasCanvas };
const context = {
  btoa: value => Buffer.from(value, 'binary').toString('base64'),
  matchMedia: () => media,
  document: { getElementById: id => id === 'loginBackground' ? background : toggle },
  window: { Wayglim: { mount: (element, options) => { assert.equal(element, background); settings = options; return { setPaused: value => pauseStates.push(value) }; } } }
};
vm.runInNewContext(script, context);
assert.equal(settings.respectReducedMotion, true);
assert.equal(settings.pauseOffscreen, true);
assert.equal(settings.maxPixelRatio, 1);
assert.ok(settings.pace > 0 && settings.pace < 0.5, 'Slow ambient motion');
assert.equal(settings.params.spectrum, 'custom');
for (const color of settings.params.customColors) {
  assert.equal(color.length, 3);
  assert.ok(color.every(channel => Number.isFinite(channel) && channel >= 0 && channel <= 1));
}
assert.equal(toggle.hidden, false);
events.click();
assert.equal(toggle['aria-pressed'], 'true');
assert.equal(toggle.textContent, 'ادامهٔ حرکت پس‌زمینه');
events.click();
assert.deepEqual(pauseStates, [true, false]);
assert.equal(toggle['aria-pressed'], 'false');
media.matches = true;
events.mediaChange();
assert.equal(toggle.hidden, true, 'Motion control is hidden when reduced motion is requested');
context.window.Wayglim.mount = () => { throw new Error('GPU unavailable'); };
vm.runInNewContext(script, context);
assert.equal(cleared, 1, 'Rendering failure preserves the CSS background');

let removed = 0;
context.document.createElement = () => ({ style: {}, setAttribute() {}, getContext: () => null, remove: () => { removed++; hasCanvas = false; } });
vm.runInNewContext(engine, context);
vm.runInNewContext(script, context);
assert.equal(removed, 1, 'Unsupported WebGL leaves no blank canvas over the fallback');
assert.equal(cleared, 1, 'Unsupported WebGL exits normally');
assert.equal(toggle.hidden, true, 'No ineffective pause button without WebGL');
context.window.Wayglim = undefined;
assert.doesNotThrow(() => vm.runInNewContext(script, context), 'Missing effect cannot block the login page');
console.log('PASS Macsa palette, motion settings and missing/failed/unsupported WebGL fallback');

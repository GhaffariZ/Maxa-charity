const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const script = fs.readFileSync(path.join(__dirname, '../public_html/dashboard/assets/js/login-background.js'), 'utf8');
const engine = fs.readFileSync(path.join(__dirname, '../public_html/dashboard/assets/js/vendor/wayglim.js'), 'utf8');

function preview(reduced = false) {
  const state = { mounted: 0, destroyed: 0, cleared: 0 };
  const media = { matches: reduced, addEventListener: (name, callback) => { state.preferenceChanged = callback; } };
  const background = { replaceChildren: () => state.cleared++, appendChild() {} };
  state.context = {
    btoa: value => Buffer.from(value, 'binary').toString('base64'),
    matchMedia: () => media,
    document: { getElementById: () => background },
    window: { Wayglim: { mount: (element, options) => {
      assert.equal(element, background); state.mounted++; state.settings = options;
      return { destroy: () => state.destroyed++ };
    } } }
  };
  vm.runInNewContext(script, state.context);
  return state;
}
const normal = preview();
assert.ok(!('maxFps' in normal.settings), 'No explicit frame cap');
assert.equal(normal.settings.maxPixelRatio, 0.5);
assert.equal(normal.settings.pauseOffscreen, true);
assert.equal(normal.settings.respectReducedMotion, true);
assert.equal(normal.settings.params.spectrum, 'custom');
for (const color of normal.settings.params.customColors) {
  assert.equal(color.length, 3);
  assert.ok(color.every(channel => Number.isFinite(channel) && channel >= 0 && channel <= 1));
}
normal.settings.onSlow();
assert.equal(normal.destroyed, 1, 'Slow-device fallback releases GPU resources');
assert.equal(normal.cleared, 1);
normal.preferenceChanged({ matches: true });
assert.equal(normal.destroyed, 1, 'Fallback is safe when reduced motion changes later');
assert.equal(preview(true).mounted, 0, 'Reduced motion uses the image without initializing WebGL');
const changed = preview(); changed.preferenceChanged({ matches: true });
assert.equal(changed.destroyed, 1, 'A live preference change also stops rendering');
normal.context.window.Wayglim.mount = () => { throw new Error('GPU unavailable'); };
assert.doesNotThrow(() => vm.runInNewContext(script, normal.context));
normal.context.document.createElement = () => ({ style: {}, setAttribute() {}, getContext: () => null, remove() {} });
vm.runInNewContext(engine, normal.context);
assert.doesNotThrow(() => vm.runInNewContext(script, normal.context), 'Unsupported WebGL keeps the image');
normal.context.window.Wayglim = undefined;
assert.doesNotThrow(() => vm.runInNewContext(script, normal.context), 'Missing engine cannot block login');

// Exercise the actual scheduling function with a renderer stub, without a GPU.
const mountSource = engine.slice(engine.indexOf('function mountCfMotion('), engine.indexOf('window.Wayglim ='));
function renderLoop() {
  let next, now = -1, slow = 0, disposed = 0;
  const draws = [], listeners = {};
  const canvas = { style: {}, setAttribute() {}, addEventListener() {}, removeEventListener() {}, remove() {} };
  const document = { documentElement: { removeEventListener() {} }, visibilityState: 'visible', createElement: () => canvas, addEventListener: (name, fn) => { listeners[name] = fn; }, removeEventListener() {} };
  const context = {
    document, Math, console, getComputedStyle: () => ({ position: 'fixed' }),
    createCfMotion: () => ({ render: (params, options) => draws.push({ time: now, options }), maxSize: () => 8192, dispose: () => disposed++, reset() {} }),
    window: { devicePixelRatio: 2, matchMedia: () => ({ matches: false }), requestAnimationFrame: fn => { next = fn; return 1; }, cancelAnimationFrame: () => { next = null; }, addEventListener() {}, removeEventListener() {} }
  };
  const mount = vm.runInNewContext(mountSource + '\nmountCfMotion', context);
  const element = { appendChild() {}, getBoundingClientRect: () => ({ width: 800, height: 600 }) };
  let motion;
  motion = mount(element, { ...normal.settings, onSlow: () => { slow++; motion.destroy(); } });
  return { draws, document, listeners, step(time) { now = time; const frame = next; next = null; frame?.(time); }, state: () => ({ slow, disposed, pending: !!next }), motion };
}
for (const hz of [30, 60, 144]) {
  const loop = renderLoop();
  for (let frame = 0; frame < hz * 2; frame++) loop.step(frame * 1000 / hz);
  const count = loop.draws.filter(draw => draw.time >= 0).length;
  assert.equal(count, hz * 2, `${hz} Hz display renders on every refresh`);
  assert.ok(Math.abs(loop.draws.at(-1).options.loopProgress - (18 + 2 * 0.28) / 60) < 0.001, 'Animation speed remains consistent across refresh rates');
  assert.equal(loop.draws[0].options.width, 400, 'Half-width buffer even on a retina screen');
  assert.equal(loop.draws[0].options.height, 300);
  assert.equal(loop.state().slow, 0, 'Normal refresh rates do not trigger fallback');
  const before = loop.draws.length;
  loop.document.visibilityState = 'hidden'; loop.listeners.visibilitychange(); loop.step(2100);
  assert.equal(loop.draws.length, before, 'Hidden tabs stop drawing');
  loop.motion.destroy();
}
const slow = renderLoop();
for (let time = 0; time < 3000; time += 100) slow.step(time);
assert.deepEqual(slow.state(), { slow: 1, disposed: 1, pending: false }, 'Sustained 10 fps switches to the image and stops scheduling');
console.log('PASS native refresh at 30/60/144 Hz, half-resolution rendering, slow-device fallback, reduced motion and unavailable WebGL');

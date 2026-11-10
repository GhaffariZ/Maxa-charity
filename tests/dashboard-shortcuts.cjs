const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../public_html/dashboard/assets/js/dashboard-tools.js'), 'utf8');
const handler = source.slice(source.indexOf('  function shortcuts(event)'), source.indexOf("  document.addEventListener('keydown', shortcuts);"));
let opened = 0;
const shortcuts = vm.runInNewContext(handler + '\nshortcuts', { openCommands: () => opened++ });

for (const key of ['k', 'K', 'ن', 'л']) {
  for (const modifier of ['ctrlKey', 'metaKey']) {
    let prevented = false;
    const before = opened;
    shortcuts({ key, code: 'KeyK', [modifier]: true, preventDefault: () => { prevented = true; } });
    assert.equal(opened, before + 1, `${modifier} + physical K opens commands with ${key}`);
    assert.ok(prevented, 'Browser shortcut is prevented');
  }
}
for (const event of [
  { key: 'ن', code: 'KeyK' },
  { key: 'k', code: 'KeyK', ctrlKey: true, altKey: true },
  { key: 'k', code: 'KeyJ', ctrlKey: true }
]) {
  const before = opened;
  shortcuts({ ...event, preventDefault: () => assert.fail('Unrelated shortcut must remain available') });
  assert.equal(opened, before);
}
console.log('PASS layout-independent Ctrl/Cmd+K and unrelated shortcut handling');

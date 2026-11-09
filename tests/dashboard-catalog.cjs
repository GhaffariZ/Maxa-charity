const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const catalog = require('../public_html/dashboard/assets/js/dashboard-catalog.js');
const source = fs.readFileSync(require('node:path').join(__dirname, '../public_html/dashboard/index.php'), 'utf8');
const navSource = source.slice(source.indexOf('  const MENU = SERVER.menu'), source.indexOf('  const navEl=document.getElementById'));
function menuFor(overrides = {}) {
  const menu = {can:new Proxy({}, {get:()=>true}),isSuper:true,isBranchAdmin:true,isHqView:true,isNewsEditor:true,canMaxapedia:true,...overrides};
  return vm.runInNewContext(navSource + '\n({nav:NAV,menu:MENU})', {SERVER:{menu}});
}
const all = menuFor();
const commands = catalog.buildCommands(all.nav, all.menu);
for (const item of all.nav) for (const entry of item.children || (item.href ? [item] : [])) {
  assert.ok(commands.some(command => command.href === entry.href), `Missing navigation command: ${entry.href}`);
  assert.notEqual(catalog.helpFor(entry.href)[1], 'راهنمای این صفحه', `Missing section help: ${entry.href}`);
}
for (const label of ['تأیید و انتشار خبر','حذف خبر','ویرایش کمپین','خروجی اکسل گزارش مالی','مدارک']) {
  assert.ok(commands.some(command => catalog.matches(command,label)), `Missing operation: ${label}`);
}
assert.ok(commands.some(command => catalog.matches(command,'ويرايش كمپين')), 'Arabic/Persian character normalization');
assert.equal(catalog.normalize('پرونده ۱۲٣'), 'پرونده 123');
assert.ok(commands.some(command => command.href === 'event-news-list.php' && command.type === 'pages' && catalog.matches(command,'خبر')), 'News aliases cover event news navigation too');
assert.ok(commands.some(command => command.href === 'medical-records.php' && catalog.matches(command,'medical')), 'English route names are searchable too');
const restricted = menuFor({can:{tickets:true},isSuper:false,isBranchAdmin:false,isHqView:false,isNewsEditor:false,canMaxapedia:false});
const limited = catalog.buildCommands(restricted.nav,restricted.menu);
assert.ok(!limited.some(command => /news-|medical|financial|branch-|user-manage/.test(command.href || '')), 'Unauthorized routes must not be searchable');
assert.ok(!limited.some(command => command.label.includes('ارجاع تیکت')), 'Branch-only operation stays out of normal-user commands');
console.log(`PASS all ${commands.length} catalog entries, section help, command discovery, normalization and restricted access`);

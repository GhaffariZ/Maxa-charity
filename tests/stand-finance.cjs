const assert = require('node:assert/strict');
const path = require('node:path');
const vm = require('node:vm');
const {spawnSync} = require('node:child_process');
function render(mode,branch='branch') {
  const result=spawnSync(process.env.PHP_BINARY || 'php',['-d','extension=pdo_sqlite',path.join(__dirname,'fixtures/stand-finance-render.php'),mode,branch],{encoding:'utf8'});
  assert.equal(result.status,0,result.stderr || result.stdout);
  assert.doesNotMatch(result.stdout,/Fatal error|Warning:/);
  return result.stdout;
}
function data(html) {
  const found=html.match(/const SERVER = (.*);/);
  assert.ok(found,'Financial hydration data must render');
  for (const script of html.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)) new vm.Script(script[1]);
  return JSON.parse(found[1]);
}
const branch=data(render('view'));
assert.equal(branch.distribution.data[4],300000);
assert.deepEqual(branch.distribution.labels,['کمک آنلاین مستقیم','کمپین‌ها','استندها و سفارشات','دوره‌های آموزشی','استند'],'Existing four categories stay intact; stands is an additional fifth category');
assert.equal(branch.distribution.data[2],0,'Stand revenue must not be counted again as product sales');
assert.match(render('view'),/فروش محصولات\/سفارشات/,'The original product sales card stays present');
assert.equal(branch.distribution.data[0],0,'Stand-only report excludes online support');
assert.equal(branch.monthly.stands.reduce((a,b)=>a+b,0),300000);
assert.equal(branch.dbError,null,'Production SQL must run without swallowed database errors');
const all=data(render('all-view'));
assert.equal(all.distribution.data[0],500000,'Online support keeps its own indicator');
assert.equal(all.distribution.data[4],300000,'Stand receipts keep their own indicator');
assert.equal(data(render('view','hq')).distribution.data[4],450000,'HQ aggregates the two branch receipts');
const csv=render('csv');
assert.match(csv,/PAID-STAND/);
assert.match(csv,/,استند,/);
assert.doesNotMatch(csv,/UNPAID-LEGACY|PENDING-STAND|FAILED-STAND|OTHER-BRANCH|ONLINE-SUPPORT/);
assert.match(render('csv','hq'),/OTHER-BRANCH/);
const excel=render('excel');
assert.match(excel,/<Workbook/);
assert.match(excel,/PAID-STAND/);
assert.doesNotMatch(excel,/UNPAID-LEGACY|PENDING-STAND|FAILED-STAND|OTHER-BRANCH|ONLINE-SUPPORT/);
console.log('PASS actual financial page, inline JS syntax, independent support/stand indicators, stand-only report, payment-date filtering, CSV/XML exports and branch/HQ isolation');

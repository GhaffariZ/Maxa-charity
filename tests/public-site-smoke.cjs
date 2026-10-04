// PLAYWRIGHT_MODULE may point at the bundled runtime. Optional: BROWSER_CHANNEL=msedge.
const assert = require('node:assert/strict');
const { spawnSync } = require('node:child_process');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { gzipSync } = require('node:zlib');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root = path.resolve(__dirname, '../public_html');
const output = path.resolve(__dirname, '../.next/taste-audit');
fs.mkdirSync(output, { recursive: true });
const pages = {};
for (const kind of ['home','contact']) {
  const result = spawnSync('php', ['-d','extension=mbstring',path.join(__dirname, 'fixtures/public-site.php'), kind, 'active'], { encoding: 'utf8' });
  assert.equal(result.status, 0, result.stderr);
  assert.ok(!result.stdout.includes('Fatal error'), result.stdout.slice(-800));
  assert.equal((result.stdout.match(/<!doctype/gi)||[]).length, 1, 'One document');
  assert.equal((result.stdout.match(/<\/html>/gi)||[]).length, 1, 'One document close');
  pages[kind] = result.stdout;
}
const changed = spawnSync('git', ['diff','--name-only'], {encoding:'utf8'}).stdout.trim().split('\n');
const group = ['dashboard/components/header/component.php','dashboard/components/heroindex/component.php'];
function protectedValues(text) {
  const values = { href: new Set(), 'data-link': new Set(), name: new Set(), id: new Set() };
  text = text.replace(/<!--[^]*?-->/g, '').replace(/<(?:link|meta)\b[^>]*>/g, '');
  for (const match of text.matchAll(/\b(href|data-link|name|id)=["']([^"']+)["']/g)) values[match[1]].add(match[2]);
  return values;
}
function original(relative) {
  const result = spawnSync('git',['show',`HEAD:public_html/${relative}`], {encoding:'utf8'});
  assert.equal(result.status,0,result.stderr);
  return result.stdout;
}
function preserve(before, after, label) {
  const old=protectedValues(before), now=protectedValues(after);
  for(const key of Object.keys(old)) assert.deepEqual([...old[key]].sort(),[...now[key]].sort(),`${label}: ${key}`);
}
preserve(group.map(original).join('\n'),group.map(p=>fs.readFileSync(path.join(root,p),'utf8')).join('\n'),'Shared navigation/hero');
const nav = text => text.match(/<ul class="cta-menu"[^]*?<\/ul>/)[0];
assert.equal(nav(original(group[0])),nav(fs.readFileSync(path.join(root,group[0]),'utf8')),'Primary nav labels and order');
const footerPath='dashboard/components/footer/component.php';
for(const className of ['gf-privacy','gf-copy']) {
  const text=source=>source.match(new RegExp(`class="${className}"[^>]*>([^]*?)<\/`))[1].trim();
  assert.equal(text(original(footerPath)),text(fs.readFileSync(path.join(root,footerPath),'utf8')),'Legal copy unchanged');
}
for (const file of changed) {
  const relative=file.replace(/^public_html\//,'');
  if (!file.startsWith('public_html/') || group.includes(relative) || !/\.(php|js|css)$/.test(file)) continue;
  preserve(original(relative),fs.readFileSync(path.join(root,relative),'utf8'),relative);
}
const rewrites=source=>source.split(/\r?\n/).filter(line=>/^Rewrite|^ErrorDocument/.test(line)).join('\n');
assert.equal(rewrites(original('.htaccess')),rewrites(fs.readFileSync(path.join(root,'.htaccess'),'utf8')),'Routing rules unchanged');
for (const file of ['robots.txt','sitemap.php','dashboard/components/header/images/1.png','dashboard/components/footer/images/1.png']) {
  const result=spawnSync('git',['diff','--exit-code','--',`public_html/${file}`]);
  assert.equal(result.status,0,`${file} unchanged`);
}
let newsletterFailure=false, lastSubmission=null;
const types={'.css':'text/css','.js':'text/javascript','.woff2':'font/woff2','.png':'image/png','.jpg':'image/jpeg','.webp':'image/webp','.svg':'image/svg+xml','.ico':'image/x-icon'};
const server=http.createServer((req,res)=>{
  const end=res.end.bind(res);
  res.end=body=>{
    if(body&&/gzip/.test(req.headers['accept-encoding']||'')&&/text\/|application\/json/.test(res.getHeader('Content-Type')||'')) {
      res.setHeader('Content-Encoding','gzip');return end(gzipSync(body));
    }
    return end(body);
  };
  const url=new URL(req.url,'http://localhost');
  if(url.pathname==='/home'||url.pathname==='/contactus') {res.setHeader('Content-Type','text/html; charset=utf-8');return res.end(pages[url.pathname==='/home'?'home':'contact']);}
  if(url.pathname==='/api/contact') {
    let body='';req.on('data',chunk=>body+=chunk);req.on('end',()=>{
      lastSubmission=JSON.parse(body);res.setHeader('Content-Type','application/json');
      res.statusCode=newsletterFailure?503:201;res.end(JSON.stringify({success:!newsletterFailure}));
    });return;
  }
  if(url.pathname==='/dashboard/hero-list.php') {
    res.setHeader('Content-Type','application/json');
    return res.end(JSON.stringify({status:'success',data:[
      {title:'موسسه نیکوکاری کنترل سرطان ایرانیان (مکسا)',description:'هم سنگر بیماران در مبارزه با سرطان',image:'/uploads/hero/hero_1780827718_9482.png',image_webp:'/uploads/hero/hero_1780827718_9482.png.webp',image_mobile:'/uploads/hero/hero_1780827718_9482.mobile.webp',button_link:'/onlinedonation'},
      {title:'در کنار شما',description:'مکسا در کنار شماست',image:'/uploads/hero/hero_1780343574_6054.png',image_webp:'/uploads/hero/hero_1780343574_6054.png.webp',image_mobile:'/uploads/hero/hero_1780343574_6054.mobile.webp',button_link:'/patientintake'}
    ]}));
  }
  if(url.pathname==='/api-stories.php') {res.setHeader('Content-Type','application/json');return res.end(JSON.stringify({success:true,data:[]}));}
  if(url.pathname==='/dashboard/recent-news-feed.php') {res.setHeader('Content-Type','application/json');return res.end(JSON.stringify({items:[]}));}
  const file=path.resolve(root,'.'+decodeURIComponent(url.pathname));
  if(file.startsWith(root+path.sep)&&types[path.extname(file)]&&fs.existsSync(file)) {res.setHeader('Content-Type',types[path.extname(file)]);res.setHeader('Cache-Control','public, max-age=31536000');return res.end(fs.readFileSync(file));}
  res.writeHead(404).end();
});
(async()=>{
  let browser;
  try {
    await new Promise(resolve=>server.listen(process.env.PREVIEW_PORT||0,'127.0.0.1',resolve));
    const origin=`http://127.0.0.1:${server.address().port}`;
    if(process.argv.includes('--serve')) {console.log(`Local fixture preview: ${origin}/home`);return;}
    browser=await chromium.launch({headless:true,...(process.env.BROWSER_CHANNEL?{channel:process.env.BROWSER_CHANNEL}:{})});
    const findings=[];
    for(const width of [1440,1024,768,390,320]) for(const colorScheme of ['light','dark']) {
      const context=await browser.newContext({viewport:{width,height:900},colorScheme,reducedMotion:'reduce'});
      const page=await context.newPage(), errors=[];
      page.on('pageerror',e=>errors.push(e.message));
      await page.goto(origin+'/home');
      await page.evaluate(()=>document.fonts.ready);
      await page.waitForFunction(()=>document.querySelector('#ctaDots').children.length===2);
      if (width < 768) assert.ok(await page.locator('#ctaTrack img').first().evaluate(img=>img.currentSrc.endsWith('.mobile.webp')),'Mobile hero source');
      assert.deepEqual(errors,[],'No script exceptions');
      assert.equal(await page.locator('h1').count(),1,'One page H1');
      assert.equal(await page.locator('#menu').count(),1,'No duplicate navigation ID');
      assert.equal(await page.locator('.cta-topbar').count(),1,'One shared header');
      const geometry=await page.evaluate(()=>({scroll:document.documentElement.scrollWidth,width:innerWidth,hero:document.querySelector('.cta-hero').getBoundingClientRect().bottom,cta:document.querySelector('#heroBtn').getBoundingClientRect().bottom}));
      assert.ok(geometry.scroll<=width+1,`No overflow ${width}: ${geometry.scroll}`);
      assert.ok(geometry.cta<900,`Hero CTA visible ${width}: ${geometry.cta}`);
      await page.locator('#ctaNext').click();
      await page.waitForFunction(()=>document.querySelector('#heroTitle').textContent==='در کنار شما');
      if(width===1440&&colorScheme==='light') {
        await page.waitForTimeout(4200);
        assert.equal(await page.locator('#heroTitle').textContent(),'در کنار شما','No autoplay');
      }
      if(width<=1320) {
        await page.locator('#menuToggle').click();
        assert.equal(await page.locator('#menuToggle').getAttribute('aria-expanded'),'true');
        await page.keyboard.press('Escape');
        assert.equal(await page.locator('#menuToggle').getAttribute('aria-expanded'),'false');
        assert.equal(await page.locator('#menuToggle').evaluate(el=>el===document.activeElement),true,'Focus restored');
      } else {
        await page.locator('.cta-menu .mega-toggle').first().focus();
        await page.keyboard.press('ArrowDown');
        assert.equal(await page.locator('.cta-menu .mega-toggle').first().getAttribute('aria-expanded'),'true');
        await page.keyboard.press('Escape');
      }
      await page.evaluate(()=>scrollTo(0,450));
      await page.waitForTimeout(100);
      assert.ok(Math.abs(await page.locator('.cta-topbar').evaluate(el=>el.getBoundingClientRect().top))<1,'Native sticky header');
      assert.equal(await page.locator('.maxsa-testimonial-track').evaluate(el=>getComputedStyle(el).animationName),'none','Static stories');
      assert.ok(!await page.locator('body').innerText().then(s=>s.includes('\u2014')),'No visible em dashes');
      if([1440,390].includes(width)) {
        await page.locator('img[loading="lazy"]').evaluateAll(els=>els.forEach(img=>img.loading='eager'));
        await page.waitForFunction(()=>Array.from(document.querySelectorAll('img')).every(img=>img.complete));
        await page.locator('.maxsa-right img').scrollIntoViewIfNeeded();
        await page.waitForFunction(()=>document.querySelector('.maxsa-right img').complete);
        await page.evaluate(()=>scrollTo(0,0));
        await page.screenshot({path:path.join(output,`home-${width}-${colorScheme}.png`),fullPage:true});
      }
      findings.push({width,colorScheme,...geometry});
      await context.close();
    }
    const context=await browser.newContext({viewport:{width:390,height:900}}), page=await context.newPage();
    await page.goto(origin+'/home');
    await page.route('**/dashboard/hero-list.php',route=>route.fulfill({status:500,contentType:'application/json',body:'{}'}));
    await page.reload();
    await page.waitForFunction(()=>document.querySelector('.hero-status').textContent.includes('بارگذاری نشد'));
    assert.ok((await page.locator('#heroTitle').textContent()).length>0,'Fallback heading survives API failure');
    await page.locator('.gf-newsletter input').fill('reader@example.org');
    newsletterFailure=true;
    await page.locator('.gf-newsletter button').click();
    await page.waitForFunction(()=>document.querySelector('.newsletter-status').textContent.includes('ارسال نشد'));
    assert.equal(await page.locator('.gf-newsletter input').inputValue(),'reader@example.org','Keep email after failure');
    newsletterFailure=false;
    await page.locator('.gf-newsletter button').click();
    await page.waitForFunction(()=>document.querySelector('.newsletter-status').textContent.includes('تیم مکسا'));
    assert.equal(lastSubmission.email,'reader@example.org');
    assert.equal(lastSubmission.subject,'درخواست عضویت در خبرنامه');
    await page.goto(origin+'/contactus');
    assert.deepEqual(await page.locator('#cuForm [name]').evaluateAll(els=>els.map(el=>el.name)),['name','email','phone','subject','message','website'],'Contact field order');
    await page.screenshot({path:path.join(output,'contact-390.png'),fullPage:true});
    await context.close();
    fs.writeFileSync(path.join(output,'checks.json'),JSON.stringify(findings,null,2));
    console.log('PASS preservation, 10 responsive/theme views, sticky navigation, keyboard/drawer focus, slider/no autoplay, API fallback, newsletter failure/retry, contact field order.');
  } finally {if(browser)await browser.close();if(!process.argv.includes('--serve'))server.close();}
})().catch(error=>{console.error(error);process.exitCode=1;server.close();});

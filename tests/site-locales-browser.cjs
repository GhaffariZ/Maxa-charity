// Full local preview required; no production writes or external messages.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const origin='http://localhost:8080';
(async()=>{
 const browser=await chromium.launch({headless:true,channel:process.env.BROWSER_CHANNEL||'chrome'});
 try {
  const context=await browser.newContext();
  const page=await context.newPage();
  const errors=[];page.on('pageerror',e=>errors.push(e.message));
  const routes=['home','history','mission-vision','association','medicalcare','nursecare','nutritioncare','psychologicalcare','spritualcare','socialworkcare','medicalequipments','genetic_conseuling','physiotherapycare','doctorspage','patient-option','contactus','news.php','branches.php','events.php','courses','macsapedia','network.php'];
  let count=0;
  for(const locale of ['fa','en','ar'])for(const route of routes){
   const url=origin+(locale==='fa'?'':'/'+locale)+'/'+route;
   const response=await context.request.get(url);
   assert.equal(response.status(),200,url);
   const html=await response.text();
   assert.ok(!/Fatal error:|DATABASE ERROR:|Warning: require/.test(html),url+' PHP error');
   assert.ok(html.includes(`lang="${locale}" dir="${locale==='en'?'ltr':'rtl'}"`),url+' document locale');
   for(const script of html.matchAll(/<script\b([^>]*)>([\s\S]*?)<\/script>/gi)) {
    if (/\b(?:src=|type=["'](?:application\/ld\+json|application\/json|module))/.test(script[1]) || !script[2].trim()) continue;
    new vm.Script(script[2],{filename:url});
   }
   count++;
  }
  const hero=await (await context.request.get(origin+'/dashboard/hero-list.php')).json();
  const image=hero.data?.[0]?.image||'/favicon.png';
  await page.route('**/dashboard/hero-list.php*',route=>route.fulfill({json:{status:'success',data:[{title:'تماس با ما',description:'خدمات مکسا',image,button_link:''}]}}));
  for(const locale of ['en','ar']) {
   await page.goto(origin+'/'+locale+'/home',{waitUntil:'networkidle'});
   assert.equal(await page.locator('#heroTitle').innerText(),'تماس با ما','Database-like hero title must remain Persian');
   assert.equal(await page.locator('#heroDesc').innerText(),'خدمات مکسا','Hero description must remain Persian');
   assert.ok((await page.locator('body').innerText()).includes(locale==='en'?'MACSA services':'خدمات مكسا'));
   await page.evaluate(()=>{const a=document.createElement('a');a.id='live-link-check';a.href='/123/example';a.textContent='تماس با ما';document.body.append(a)});
   await page.waitForFunction(locale=>document.querySelector('#live-link-check').getAttribute('href')==='/'+locale+'/123/example',locale);
   assert.equal(await page.locator('#live-link-check').innerText(),'تماس با ما');
   const picker=page.locator('.site-language').first();
   await picker.locator('summary').click();
   assert.ok(await picker.locator('a[lang="fa"]').isVisible());
   assert.equal(new URL(await picker.locator('a[lang="fa"]').getAttribute('href'),origin).pathname,'/home');
   await page.keyboard.press('Escape');
   assert.equal(await picker.getAttribute('open'),null);
   for(const width of [360,390,768,1024,1280,1366,1440]) {
    await page.setViewportSize({width,height:900});
    await page.evaluate(()=>new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve))));
    const metrics=await page.evaluate(()=>({width:innerWidth,scroll:document.documentElement.scrollWidth,button:(()=>{const r=document.querySelector('.site-language summary').getBoundingClientRect();return{x:r.x,right:r.right,width:r.width}})()}));
    assert.ok(metrics.scroll<=width+1,`${locale} overflow at ${width}: ${metrics.scroll}`);
    assert.ok(metrics.button.x>=0&&metrics.button.right<=width&&metrics.button.width>=40,`${locale} language button outside viewport at ${width}`);
    const nav=await page.evaluate(()=>{const center=document.querySelector('.cta-center'),menu=document.querySelector('.cta-menu');if(document.querySelector('[data-site-nav]').dataset.navMode==='compact')return null;const c=center.getBoundingClientRect(),m=menu.getBoundingClientRect();return{left:c.left,right:c.right,menuLeft:m.left,menuRight:m.right}});
    if(nav) assert.ok(nav.menuLeft>=nav.left-1&&nav.menuRight<=nav.right+1,`${locale} desktop menu overlap at ${width}`);
   }
   await page.setViewportSize({width:1440,height:1000});
   await page.screenshot({path:path.join('.local',`locale-${locale}-desktop.png`)});
   await page.setViewportSize({width:390,height:844});
   await page.screenshot({path:path.join('.local',`locale-${locale}-mobile.png`)});
  }
  await page.goto(origin+'/en/contactus',{waitUntil:'networkidle'});
  assert.ok((await page.locator('body').innerText()).includes('Send message'));
  const feed=await (await context.request.get(origin+'/dashboard/recent-news-feed.php?limit=1')).json();
  const article=feed.items?.[0];
  const articleUrl=article?.url||article?.link;
  if(articleUrl) {
   const plain=new URL(articleUrl,origin);
   await page.goto(origin+plain.pathname,{waitUntil:'domcontentloaded'});
   assert.equal(await page.locator('.article-content').count(),1);
   for(const locale of ['en','ar']) {
    const localizedFeed=await (await context.request.get(origin+'/dashboard/recent-news-feed.php?limit=12&lang='+locale)).json();
    const available=localizedFeed.items?.some(item=>item.id===article.id);
    const response=await page.goto(origin+'/'+locale+plain.pathname,{waitUntil:'domcontentloaded'});
    if(!available)assert.equal(response.status(),404,'Unconfigured article has no Persian fallback');
    else assert.equal(await page.locator('.article-content').count(),1);
   }
  }
  await page.goto(origin+'/dashboard/login.php',{waitUntil:'domcontentloaded'});
  assert.equal(await page.locator('html').getAttribute('lang'),'fa');
  assert.equal(await page.locator('.site-language').count(),0);
  await page.goto(origin+'/benefactor-dashboard/',{waitUntil:'networkidle'});
  assert.equal(await page.locator('.site-language').count(),0);
  assert.deepEqual(errors,[],'JavaScript errors');
  for(const locale of ['en','ar']) {
   const missing=await context.request.get(origin+'/'+locale+'/no-such-public-page');
   assert.equal(missing.status(),404);
   assert.ok((await missing.text()).includes(locale==='en'?'Page not found!':'لم يتم العثور على الصفحة!'));
   assert.equal((await context.request.get(origin+'/'+locale+'/dashboard/login.php')).status(),404);
  }
  console.log(`PASS ${count} public routes and inline scripts; hero data isolation; language control; RTL/LTR; seven viewport widths; localised 404; both panels excluded.`);
 }finally{await browser.close()}
})().catch(e=>{console.error(e);process.exitCode=1});

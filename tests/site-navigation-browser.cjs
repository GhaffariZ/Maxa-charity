// Navigation audit: run --pass=layout, review results, then --pass=interaction.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const {chromium, devices} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const pass = process.argv.find(value => value.startsWith('--pass='))?.split('=')[1] || 'all';
const origin = process.env.LOCAL_ORIGIN || 'http://localhost:8080';
const widths = [320,360,390,430,600,768,820,1024,1100,1280,1366,1440,1920];
const locales = ['fa','en','ar'];
const url = (locale, route) => origin + (locale === 'fa' ? '' : '/' + locale) + '/' + route;
const picture = async (page, name) => { if (fs.existsSync('.local')) await page.screenshot({path:'.local/nav-'+name+'.png'}); };

async function ready(page, locale, route) {
 const response = await page.goto(url(locale,route),{waitUntil:'domcontentloaded'});
 assert.equal(response.status(),200,`${locale}/${route}`);
 await page.waitForFunction(()=>document.querySelector('[data-site-nav]')?.dataset.navReady==='true');
 await page.evaluate(()=>document.fonts.ready);
 await page.evaluate(()=>new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve))));
 assert.equal(await page.locator('[data-site-nav]').count(),1,'Exactly one public navbar');
}
async function insideAndClickable(locator, page, label) {
 const state=await locator.evaluate(element=>{
  const box=element.getBoundingClientRect();
  const hit=document.elementFromPoint(box.x+box.width/2,box.y+box.height/2);
  return {left:box.left,right:box.right,top:box.top,bottom:box.bottom,width:box.width,height:box.height,hit:element.contains(hit)};
 });
 const viewport=page.viewportSize();
 assert.ok(state.left>=0&&state.right<=viewport.width+1&&state.top>=0&&state.bottom<=viewport.height+1,`${label} outside viewport: ${JSON.stringify(state)}`);
 assert.ok(state.hit,`${label} covered by another element`);
 return state;
}
async function languageMenu(page, label) {
 const picker=page.locator('.site-language').first();
 const button=picker.locator('summary');
 const box=await insideAndClickable(button,page,label+' language button');
 assert.ok(box.width>=44&&box.height>=44,label+' language touch target');
 await button.click();
 for(const locale of locales) await insideAndClickable(picker.locator(`a[lang="${locale}"]`),page,label+' '+locale+' option');
 await page.keyboard.press('Escape');
 assert.equal(await picker.getAttribute('open'),null,label+' Escape closes language menu');
 assert.ok(await button.evaluate(element=>element===document.activeElement),label+' language focus restoration');
}
async function drawerMenu(page, label) {
 const toggle=page.locator('#menuToggle');
 await insideAndClickable(toggle,page,label+' menu button');
 assert.equal(await toggle.evaluate(e=>e.tagName),'BUTTON',label+' native menu button');
 await toggle.click();
 const drawer=page.locator('[data-site-drawer]');
 await page.waitForFunction(()=>document.querySelector('[data-site-drawer]').getBoundingClientRect().left>=-1);
 await page.waitForFunction(()=>document.activeElement.id==='mobileMenuClose');
 assert.equal(await drawer.getAttribute('aria-modal'),'true',label+' modal semantics');
 assert.equal(await drawer.evaluate(e=>e.inert),false,label+' open drawer is interactive');
 assert.notEqual(await page.locator('body').evaluate(e=>getComputedStyle(e).touchAction),'none',label+' touch scrolling allowed');
 assert.equal(await drawer.locator('.mobile-action-auth').evaluate(e=>getComputedStyle(e).color),'rgb(7, 87, 93)',label+' readable sign-in action');
 await insideAndClickable(drawer.locator('#mobileMenuClose'),page,label+' close button');
 const sections=drawer.locator('.site-nav-submenu');
 for(let i=0;i<await sections.count();i++) {
  const button=sections.nth(i);
  await button.scrollIntoViewIfNeeded();
  await button.click();
  assert.equal(await button.getAttribute('aria-expanded'),'true',label+' submenu expands');
  const last=button.locator('..').locator('.mega-col a').last();
  await last.scrollIntoViewIfNeeded();
  await insideAndClickable(last,page,label+' last submenu link');
 }
 await page.keyboard.press('Escape');
 assert.equal(await drawer.getAttribute('aria-hidden'),'true',label+' drawer closes');
 assert.equal(await drawer.evaluate(e=>e.inert),true,label+' closed drawer is inert');
 assert.ok(await toggle.evaluate(e=>e===document.activeElement),label+' menu focus restoration');
 assert.equal(await page.evaluate(()=>document.body.classList.contains('mobile-menu-open')),false,label+' body unlocks');
}

async function layout(browser) {
 const context=await browser.newContext({reducedMotion:'reduce'});
 const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
 let cases=0;
 for(const route of ['home','contactus'])for(const locale of locales)for(const width of widths) {
  await page.setViewportSize({width,height:844});
  await ready(page,locale,route);
  const label=`${locale}/${route} ${width}`;
  if(width>=1366)assert.equal(await page.locator('[data-site-nav]').getAttribute('data-nav-mode'),'desktop',label+' full desktop navigation');
  assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),label+' horizontal overflow');
  await languageMenu(page,label);
  if(await page.locator('[data-site-nav]').getAttribute('data-nav-mode')==='compact') await drawerMenu(page,label);
  else {
   const bounds=await page.locator('[data-site-nav]').evaluate(root=>{
    const c=root.querySelector('.cta-center').getBoundingClientRect(),m=root.querySelector('.cta-menu').getBoundingClientRect();
    return {left:c.left,right:c.right,menuLeft:m.left,menuRight:m.right};
   });
   assert.ok(bounds.menuLeft>=bounds.left-1&&bounds.menuRight<=bounds.right+1,label+' desktop overlap');
   for(const button of await page.locator('[data-site-nav] .site-nav-submenu').all()) {
    await button.focus();await page.keyboard.press('Enter');
    assert.equal(await button.getAttribute('aria-expanded'),'true',label+' desktop disclosure');
    const panel=button.locator('..').locator('.mega-menu-content');
    const rectangle=await panel.boundingBox();
    assert.ok(rectangle.x>=0&&rectangle.x+rectangle.width<=width+1,label+' mega menu outside viewport');
    await insideAndClickable(panel.locator('a').first(),page,label+' desktop submenu link');
    await page.keyboard.press('Escape');
   }
  }
  if([390,1440].includes(width))await picture(page,`layout-${locale}-${route}-${width}`);
  cases++;
 }
 // Other public structures use the same header but different body layouts.
 const pages=['history','mission-vision','medicalcare','psychologicalcare','medicalequipments','patient-option','patientintake','branches.php','news.php','courses','events.php','macsapedia','association','CEOoffice'];
 for(const locale of locales)for(const route of pages)for(const width of [390,820]) {
  await page.setViewportSize({width,height:844});await ready(page,locale,route);
  await languageMenu(page,`${locale}/${route} ${width}`);
  assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),`${locale}/${route} body overflow`);
  cases++;
 }
 assert.deepEqual(errors,[],'Unexpected public JavaScript errors');
 await context.close();
 console.log(`PASS layout round: ${cases} page/language/viewport states; popover hit tests, drawer submenus, keyboard and desktop bounds.`);
}

async function interaction(browser) {
 let cases=0;
 for(const device of [devices['iPhone 13'],devices['Pixel 7'],{viewport:{width:1440,height:1000},hasTouch:false}]) {
  const context=await browser.newContext({...device,reducedMotion:'reduce'});const page=await context.newPage();const errors=[];
  page.on('pageerror',e=>errors.push(e.message));
  for(const route of ['home','contactus']) {
   await ready(page,'fa',route);
   for(const next of ['en','ar','fa']) {
    const picker=page.locator('.site-language').first();await picker.locator('summary').click();
    await picker.locator(`a[lang="${next}"]`).click();
    await page.waitForFunction(locale=>document.documentElement.lang===locale&&document.querySelector('[data-site-nav]')?.dataset.navReady==='true',next);
    assert.equal(new URL(page.url()).pathname,(next==='fa'?'':'/'+next)+'/'+route,'Real language switch keeps page');
    await languageMenu(page,`${route} switched to ${next}`);
    if(await page.locator('[data-site-nav]').getAttribute('data-nav-mode')==='compact') {
     await drawerMenu(page,`${route} switched ${next}`);
     await page.locator('#menuToggle').click();
     await page.waitForFunction(()=>document.activeElement.id==='mobileMenuClose');
     if(device.hasTouch) {
      await page.locator('[data-site-drawer] .site-nav-submenu').first().click();
      const container=page.locator('[data-site-drawer] .mobile-menu-container');
      await container.evaluate(e=>{e.scrollTop=0});
      const bounds=await container.boundingBox();
      const touch=await context.newCDPSession(page);
      const x=Math.round(bounds.x+bounds.width/2),y=Math.round(bounds.y+bounds.height-30);
      await touch.send('Input.dispatchTouchEvent',{type:'touchStart',touchPoints:[{x,y}]});
      for(let step=1;step<=8;step++)await touch.send('Input.dispatchTouchEvent',{type:'touchMove',touchPoints:[{x,y:y-step*30}]});
      await touch.send('Input.dispatchTouchEvent',{type:'touchEnd',touchPoints:[]});
      await touch.detach();
      assert.ok(await container.evaluate(e=>e.scrollTop>60),'Finger gesture scrolls the drawer');
     }
     // Keyboard focus stays in the open dialog, including backwards traversal.
     await page.locator('#mobileMenuClose').focus();await page.keyboard.press('Shift+Tab');
     assert.ok(await page.evaluate(()=>document.querySelector('[data-site-drawer]').contains(document.activeElement)));
     await page.keyboard.press('Escape');
    }
    await page.evaluate(()=>scrollTo(0,600));
    await page.evaluate(()=>new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve))));
    await languageMenu(page,`${route}/${next} scrolled`);
    await page.evaluate(()=>scrollTo(0,0));
    cases++;
   }
  }
  await ready(page,'en','contactus?nav_audit=1#contact');
  await page.locator('.site-language summary').click();
  await page.locator('.site-language a[lang="ar"]').click();
  await page.waitForFunction(()=>document.documentElement.lang==='ar');
  assert.equal(new URL(page.url()).search,'?nav_audit=1','Language switch keeps query');
  assert.equal(new URL(page.url()).hash,'#contact','Language switch keeps fragment');
  // Portrait-to-landscape and compact-to-desktop transitions must unlock scrolling.
  await page.setViewportSize({width:390,height:844});await ready(page,'en','home');
  await page.locator('#menuToggle').click();
  await page.setViewportSize({width:844,height:390});
  await insideAndClickable(page.locator('#mobileMenuClose'),page,'landscape close');
  await page.locator('#mobileMenuClose').click();
  await page.setViewportSize({width:390,height:844});await page.locator('#menuToggle').click();
  await page.setViewportSize({width:1920,height:1080});
  await page.waitForFunction(()=>document.querySelector('[data-site-nav]').dataset.navMode==='desktop');
  assert.equal(await page.locator('[data-site-drawer]').getAttribute('aria-hidden'),'true');
  assert.equal(await page.evaluate(()=>document.documentElement.style.overflow),'');
  await page.waitForFunction(()=>document.querySelector('[data-site-nav] .cta-menu').contains(document.activeElement));
  // Static cards must preserve the selected language instead of returning to Persian.
  assert.match(await page.locator('#ctaCards [data-link]').first().getAttribute('data-link'),/^\/en\//);
  assert.deepEqual(errors,[],'Interaction JavaScript errors');
  await context.close();
 }
 // Authenticated public navigation, using local fixtures rather than live account changes.
 const context=await browser.newContext({viewport:{width:390,height:844},reducedMotion:'reduce'});
 await context.addInitScript(()=>localStorage.setItem('maksa_logged','1'));
 const page=await context.newPage();
 await page.route('**/api/auth/refresh',r=>r.fulfill({json:{data:{access_token:'local-navigation-fixture'}}}));
 await page.route('**/api/user/me',r=>r.fulfill({json:{data:{user:{first_name:'نام بسیار طولانی برای آزمایش',last_name:'نمایش منوی حساب کاربری',email:'navigation-fixture@example.test'}}}}));
 for(const locale of locales)for(const width of [320,390,768,1366]) {
  await page.setViewportSize({width,height:844});await ready(page,locale,'contactus');
  const account=page.locator('.cta-account-btn');await account.waitFor({state:'visible'});await account.click();
  await insideAndClickable(page.locator('.cta-account-menu a').first(),page,`account menu ${locale}/${width}`);
  await page.locator('.cta-account-menu a').first().focus();await page.keyboard.press('Escape');
  assert.ok(await account.evaluate(e=>e===document.activeElement),'Account restores focus');
  await languageMenu(page,'authenticated '+locale);
  // Opening one popup closes the other even when the original auth handler stops bubbling.
  await page.locator('.site-language summary').click();await account.click();
  assert.equal(await page.locator('.site-language').getAttribute('open'),null);
  await page.keyboard.press('Escape');
 }
 await context.close();
 console.log(`PASS interaction round: ${cases} real language transitions across iPhone/Android/desktop profiles; scrolling, focus, rotation, resizing, card links and authenticated navigation.`);
}
(async()=>{const browser=await chromium.launch({headless:true,channel:process.env.BROWSER_CHANNEL||'chrome'});try{
 if(pass==='all'||pass==='layout')await layout(browser);
 if(pass==='all'||pass==='interaction')await interaction(browser);
}finally{await browser.close()}})().catch(e=>{console.error(e);process.exitCode=1});

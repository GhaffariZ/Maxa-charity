const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const{chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const{installTranslationMock,translateTab}=require('./content-translation-mock.cjs');
const root=path.resolve(__dirname,'..'),origin='http://localhost:8080';
(async()=>{
 const browser=await chromium.launch({headless:true,channel:process.env.BROWSER_CHANNEL||'chrome'});
 try{
  const context=await browser.newContext({viewport:{width:390,height:844}}),page=await context.newPage(),errors=[];
  page.on('pageerror',error=>errors.push(error.message));
  const secrets=JSON.parse(fs.readFileSync(path.join(root,'.local/conf/secrets.json'))),html=await(await context.request.get(origin+'/dashboard/login.php')).text(),csrf=html.match(/name="csrf_token" value="([^"]+)"/)[1];
  await context.request.post(origin+'/dashboard/login.php',{form:{username:'local-admin',password:secrets.loginPassword,csrf_token:csrf}});
  assert.equal((await context.request.get(origin+'/dashboard/content-translate.php')).status(),405,'Translation is POST-only');
  const noCsrf=await context.request.post(origin+'/dashboard/content-translate.php',{form:{type:'news',locale:'en',fields:'{}'}});assert.equal(noCsrf.status(),403,'CSRF required: '+(await noCsrf.text()).slice(0,500));
  assert.equal((await context.request.post(origin+'/dashboard/content-translate.php',{form:{csrf_token:csrf,type:'news',locale:'en',fields:'{}'}})).status(),422,'Empty source rejected without calling Google');
  assert.equal((await context.request.post(origin+'/dashboard/content-translate.php',{form:{csrf_token:csrf,type:'tickets',locale:'en',fields:'{}'}})).status(),403,'Private type excluded');
  let mode='success',release;
  await installTranslationMock(page,async(input,fields)=>{
   assert.equal(input.csrf_token,csrf);assert.equal(input.type,'hero_slides');assert.equal(input.locale,'en');
   if(mode==='quota')return{status:429,body:{message:'سقف سرویس ترجمه پر شده است.'}};
   if(mode==='pending')await new Promise(resolve=>release=resolve);
   return {body:{fields,model:'gemini-3.5-flash-lite'}};
  });
  await page.goto(origin+'/dashboard/hero-create.php',{waitUntil:'networkidle'});
  await page.locator('#title').fill('خبر فارسی');await page.locator('#editor').fill('متن فارسی');await page.locator('[data-content-language=en]').check();
  const target=page.locator('.cl-panel:not([hidden])'),title=target.locator('[data-cl-field=title] input'),status=target.locator('[data-translation-status]');
  assert.equal(await page.locator('[data-copy-fa]').count(),0);
  await translateTab(page,'en');assert.equal(await title.inputValue(),'EN: خبر فارسی');
  await title.fill('Manually edited title');
  await page.locator('[role=tab]').filter({hasText:/^فارسی$/}).click();await page.locator('#title').fill('خبر جدید فارسی');
  await page.locator('[role=tab]').filter({hasText:/^انگلیسی$/}).click();assert.equal(await title.inputValue(),'Manually edited title','Later Persian changes cannot overwrite edited translations');
  mode='quota';await target.locator('[data-translate-fa]').click();await status.filter({hasText:'سقف سرویس'}).waitFor();assert.equal(await title.inputValue(),'Manually edited title','Quota errors preserve every existing field');
  mode='success';await translateTab(page,'en');assert.equal(await title.inputValue(),'EN: خبر جدید فارسی','Repeat action uses current Persian');
  mode='pending';await target.locator('[data-translate-fa]').click();await page.waitForFunction(()=>document.querySelector('[data-translate-fa=en]')?.getAttribute('aria-busy')==='true');
  assert.equal(await target.locator('[data-translate-fa]').isDisabled(),true);
  assert.equal(await page.evaluate(()=>ContentLanguages.validate()),false,'Save waits for a pending translation');
  await title.fill('Edit during request');
  while(!release)await new Promise(resolve=>setTimeout(resolve,10));release();
  await status.filter({hasText:'هنگام ترجمه تغییر کرد'}).waitFor();assert.equal(await title.inputValue(),'Edit during request','Late responses cannot overwrite concurrent edits');
  await page.evaluate(()=>document.documentElement.setAttribute('data-theme','dark'));
  const colors=await target.locator('.cl-copy-box').evaluate(e=>({background:getComputedStyle(e).backgroundColor,color:getComputedStyle(e).color}));assert.notEqual(colors.background,'rgb(255, 255, 255)');assert.notEqual(colors.background,colors.color);
  assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,'Translation action fits mobile');
  assert.deepEqual(errors,[]);console.log('PASS translation endpoint guards, button/overwrite/edit behavior, quota errors, races, busy state, dark mode and mobile; no live Gemini calls.');
 }finally{await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});

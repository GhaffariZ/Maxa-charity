// Local browser regression: native editorial layouts, shared settings and independent media.
// Uses unsaved forms only; never writes content or updates live campaigns.
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const {spawnSync}=require('node:child_process');
const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const root=path.resolve(__dirname,'..'),origin='http://localhost:8080';
const panelSelector='.cl-panel:not([hidden])';

(async()=>{
 const browser=await chromium.launch({headless:true,channel:process.env.BROWSER_CHANNEL||'chrome'});
 try{
  const context=await browser.newContext({viewport:{width:1366,height:950},reducedMotion:'reduce'}),page=await context.newPage(),errors=[];
  page.on('pageerror',error=>errors.push(page.url()+': '+error.message));
  const secrets=JSON.parse(fs.readFileSync(path.join(root,'.local/conf/secrets.json')));
  const html=await(await context.request.get(origin+'/dashboard/login.php')).text();
  await context.request.post(origin+'/dashboard/login.php',{form:{username:'local-admin',password:secrets.loginPassword,csrf_token:html.match(/name="csrf_token" value="([^"]+)"/)[1]}});
  const routes=['personal-resume-create.php','event-create.php','hero-create.php','news-create.php','courses-create.php','campaign-create.php','branch-create.php','macsa-stories.php','maxapedia.php','component-create.php','template-create.php','stands.php'];
  for(const route of routes){
   await page.goto(origin+'/dashboard/'+route,{waitUntil:'networkidle'});
   if(route==='stands.php'){const branch=await page.locator('.branch-select option').first().getAttribute('value');await page.goto(origin+'/dashboard/stands.php?branch_id='+branch,{waitUntil:'networkidle'});}
   if(route==='macsa-stories.php')await page.locator('[onclick="openCreateModal()"]').click();
   if(route==='stands.php')await page.locator('[onclick="openAddModal()"]').click();
   const config=await page.locator('.content-locale-config').last().textContent();
   assert.ok(config,route+': editor exists');
   const scope=page.locator(JSON.parse(config).selector),panel=scope.locator(panelSelector);
   for(const locale of ['en','ar']){
    await scope.locator('[data-content-language='+locale+']').check();
    assert.equal(await panel.locator('[name]').count(),0,route+': translated controls cannot overwrite shared form names');
    assert.equal(await panel.locator('[onclick],[onchange]').count(),0,route+': target controls cannot call Persian handlers');
    const duplicateIds=await page.evaluate(()=>{const ids=[...document.querySelectorAll('[id]')].map(e=>e.id);return ids.filter((id,i)=>ids.indexOf(id)!==i);});
    assert.deepEqual(duplicateIds,[],route+': unique ids for labels and guides');
    for(const width of [1366,768,390,360]){
     await page.setViewportSize({width,height:900});
     assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,route+' '+locale+' '+width+': no horizontal overflow');
    }
    await page.setViewportSize({width:1366,height:950});
   }
  }

  // Branch introductions belong to non-HQ branches; use an isolated login session.
  const read=spawnSync(path.join(root,'.local/runtime/php/php.exe'),['-c',path.join(root,'.local/conf/php.ini'),'-r',"$c=json_decode(file_get_contents('.local/conf/secrets.json'),true);$d=new PDO('mysql:host=127.0.0.1;dbname=macsa_local;charset=utf8mb4','macsa_local',$c['dbPassword']);echo $d->query('SELECT id FROM branches WHERE is_hq=0 LIMIT 1')->fetchColumn();"],{cwd:root,encoding:'utf8',windowsHide:true});
  assert.equal(read.status,0,read.stderr);assert.ok(Number(read.stdout));
  const branchContext=await browser.newContext(),branchPage=await branchContext.newPage();
  const branchLogin=await(await branchContext.request.get(origin+'/dashboard/login.php')).text(),branchCsrf=branchLogin.match(/name="csrf_token" value="([^"]+)"/)[1];
  await branchContext.request.post(origin+'/dashboard/login.php',{form:{username:'local-admin',password:secrets.loginPassword,csrf_token:branchCsrf}});
  await branchContext.request.post(origin+'/dashboard/switch-branch.php',{form:{csrf_token:branchCsrf,branch_id:read.stdout}});
  await branchPage.goto(origin+'/dashboard/branch-intro.php',{waitUntil:'networkidle'});
  for(const locale of ['en','ar']){
   await branchPage.locator('[data-content-language='+locale+']').check();assert.equal(await branchPage.locator(panelSelector+' [data-cl-field=phone]').count(),0);assert.equal(await branchPage.locator(panelSelector+' [data-cl-field=images] input[type=file][multiple]').count(),1);
   await branchPage.locator(panelSelector+' [data-cl-field=images] input[type=file]').setInputFiles([path.join(root,'public_html/favicon.png'),path.join(root,'public_html/favicon.png')]);
   assert.equal(await branchPage.locator(panelSelector+' .cl-multi-item').count(),2);
   await branchPage.locator(panelSelector+' .cl-multi-item button').first().click();
   assert.equal(await branchPage.locator(panelSelector+' .cl-multi-item').count(),1,'Removing one translated gallery image keeps the other');
  }
  await branchContext.close();

  // Every library section keeps its own cover/media picker; albums keep their photo editor.
  for(const section of ['books','brochures','videos','podcasts','clips','gallery']){
   await page.goto(origin+'/dashboard/maxapedia.php?section='+section,{waitUntil:'networkidle'});
   for(const locale of ['en','ar']){
    await page.locator('[data-content-language='+locale+']').check();const target=page.locator(panelSelector);
    assert.equal(await target.locator('[data-cl-field=thumbnail] input[type=file]').count(),1,section+': independent cover picker');
    if(section==='gallery'){
     assert.equal(await target.locator('[data-cl-field=url]').count(),0,'Album has photos instead of a document field');
     await target.locator('[data-cl-field=photos]>.cl-add').click();
     assert.equal(await target.locator('.mx-photo-item').count(),1,'Album uses native photo cards');
     await target.locator('[data-cl-field="photos.0.image_url"] input[type=file]').setInputFiles(path.join(root,'public_html/favicon.png'));
     await target.locator('[data-cl-field="photos.0.caption"] input').fill(locale+' photo caption');
     await page.setViewportSize({width:360,height:900});assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,'Filled album fits mobile');await page.setViewportSize({width:1366,height:950});
    }else{
     assert.equal(await target.locator('[data-cl-field=photos]').count(),0,'Non-album forms do not ask for album photos');
     assert.equal(await target.locator('[data-cl-field=url] input[type=file]').count(),1,section+': own document/media picker');
     await target.locator('[data-cl-field=url] input[type=file]').setInputFiles({name:'local-guide.pdf',mimeType:'application/pdf',buffer:Buffer.from('%PDF-1.4\nlocal fixture')});
     const field=await page.evaluate(locale=>{const fd=new FormData();ContentLanguages.append(fd);return JSON.parse(fd.get('content_languages')).translations[locale].fields.url;},locale);assert.ok(field.upload,'Language-specific file reaches the save envelope');
     await target.locator('[data-cl-asset-part=url] [data-cl-asset-url]').fill('https://example.test/'+locale+'-guide.pdf');
    }
   }
  }

  // Partner fields use the same order, icons and input styles as the Persian form.
  await page.goto(origin+'/dashboard/personal-resume-create.php',{waitUntil:'networkidle'});
  const native=await page.locator('#fullname').evaluate(input=>({icon:input.closest('.input-group').querySelector('label svg')?.outerHTML,styles:['borderRadius','fontFamily','padding','border'].map(key=>getComputedStyle(input)[key])}));
  await page.locator('[data-content-language=en]').check();
  const panel=page.locator(panelSelector);
  assert.deepEqual(await panel.locator('[data-cl-field]').evaluateAll(fields=>fields.map(e=>e.dataset.clField)),['fullname','profile_pic','branch','role','study_field','instagram','linkedin','bio_professional','academic_background','maxa_responsibilities']);
  assert.deepEqual(await panel.locator('[data-cl-field=fullname] [data-cl-value]').evaluate(input=>({icon:input.closest('.input-group').querySelector('label svg')?.outerHTML,styles:['borderRadius','fontFamily','padding','border'].map(key=>getComputedStyle(input)[key])})),native);
  assert.equal(await panel.locator('[data-cl-field=email],[data-cl-field=job_category]').count(),0,'Email and job classification are shared');
  await panel.locator('[data-cl-field=fullname] input').fill('English partner');
  assert.equal(await page.locator('#fullname').inputValue(),'','Independent values leave Persian untouched');

  // The event sidebar navigates the target sections. Date/time and native shared controls are omitted.
  await page.goto(origin+'/dashboard/event-create.php',{waitUntil:'networkidle'});
  await page.locator('#eventForm .cl-primary [name=title]').fill('Persian event');
  const persianProgress=await page.locator('.cl-primary #sidebarFilledCount').textContent();
  for(const locale of ['en','ar']){
   await page.locator('[data-content-language='+locale+']').check();
   const target=page.locator(panelSelector);
   assert.equal(await target.locator('.hq-sidebar-nav').count(),1,'Event guide exists: '+locale);
   assert.equal(await target.locator('input[type=date],input[type=time],input[type=datetime-local]').count(),0,'Event schedule is entered once');
   assert.equal(await target.locator('[data-cl-field=poster] input[type=file],[data-cl-field=schedule_pdf] input[type=file]').count(),2,'Localized poster and PDF remain');
   const links=target.locator('.hq-sidebar-link');
   assert.ok(await links.count()>=7,'Guide retains localized event sections');
   for(const link of await links.all()){
    assert.doesNotMatch(await link.innerText(),/تاریخ|زمان‌بندی|خبر/,'Guide omits shared scheduling and separate news');
    assert.ok(await page.locator(await link.getAttribute('href')).count(),'Every guide link has its own section');
   }
   await target.locator('[data-copy-fa]').check();
   assert.equal(await target.locator('[data-cl-field=title] [data-cl-value]').inputValue(),'Persian event');
   await target.locator('[data-cl-field=title] [data-cl-value]').fill(locale+' event');
   assert.equal(await target.locator('[data-copy-fa]').isChecked(),false,'Editing unlinks the copy');
   await target.locator('[data-cl-field=heroes]>.cl-add').click();
   const hero=target.locator('[data-cl-field="heroes.0.title"] input');
   await hero.fill(locale+' hero');
   assert.equal(await page.locator('.cl-primary #sidebarFilledCount').textContent(),persianProgress,'Translated guide progress cannot change Persian progress');
   await target.locator('[data-cl-field="heroes.0.image"] input[type=file]').setInputFiles(path.join(root,'public_html/favicon.png'));
   await target.locator('[data-cl-field=schedule_pdf] input[type=file]').setInputFiles({name:locale+'-program.pdf',mimeType:'application/pdf',buffer:Buffer.from('%PDF-1.4\nlocal layout fixture')});
   const envelope=await page.evaluate(()=>{const fd=new FormData();ContentLanguages.append(fd);return {data:JSON.parse(fd.get('content_languages')),uploads:[...fd.keys()].filter(key=>key.startsWith('content_uploads['))};});
   assert.equal(envelope.data.translations[locale].fields.heroes[0].title,locale+' hero');
   assert.ok(envelope.data.translations[locale].fields.heroes[0].image.upload,'Own hero file retained');
   assert.ok(envelope.data.translations[locale].fields.schedule_pdf.upload,'Own PDF retained');
   assert.ok(envelope.uploads.length>=2,'Translated uploads reach the save envelope');
   assert.equal(await page.locator('.cl-primary #hero-list .hq-repeat-card').count(),0,'Adding a translated hero does not add a Persian hero');
   await target.locator('[data-cl-field=heroes] .remove-row').click();
   assert.equal(await target.locator('[data-cl-field=heroes] .hq-repeat-card').count(),0,'Native remove action updates its own language');
   assert.deepEqual(await page.evaluate(locale=>{const fd=new FormData();ContentLanguages.append(fd);return JSON.parse(fd.get('content_languages')).translations[locale].fields.heroes;},locale),[],'Removed heroes are also removed from saved data');
  }

  // Course chapters, lesson type icons and tag editors use their native controls.
  await page.goto(origin+'/dashboard/courses-create.php',{waitUntil:'networkidle'});
  await page.locator('[data-content-language=en]').check();
  await panel.locator('[data-cl-field=curriculum]>.cl-add').click();
  await panel.locator('.cur-sec-title').fill('Chapter one');
  await panel.locator('.addLesson[data-type=text]').click();
  await panel.locator('.lesson-title-inp').fill('Lesson one');
  await panel.locator('.cfg').click();
  await panel.locator('[data-cl-field="curriculum.0.lessons.0.content"] textarea').fill('An independent English lesson');
  assert.equal(await panel.locator('.type-seg button.on').getAttribute('data-t'),'text');
  assert.equal(await panel.locator('.prev-chk').count(),0,'Free preview is a shared access setting');
  await panel.locator('.type-seg button[data-t=pdf]').click();
  await panel.locator('.cfg').click();
  await panel.locator('[data-cl-field="curriculum.0.lessons.0.content"] input[type=text]').fill('/uploads/english-lesson.pdf');
  await panel.locator('[data-cl-field=what_you_learn] input').fill('A learning outcome');
  await panel.locator('[data-cl-field=what_you_learn] input').press('Enter');
  assert.equal(await panel.locator('.tag-pill').textContent(),'A learning outcome');
  await panel.locator('[data-cl-field=curriculum]>.cl-add').click();
  await panel.locator('.cur-sec-title').nth(1).fill('Chapter two');
  await panel.locator('.cur-sec-head>.grip').nth(1).press('ArrowUp');
  const course=await page.evaluate(()=>{const fd=new FormData();ContentLanguages.append(fd);return JSON.parse(fd.get('content_languages')).translations.en.fields;});
  assert.equal(course.curriculum[0].title,'Chapter two','Keyboard reorder updates language data');
  assert.equal(course.curriculum[1].lessons[0].type,'pdf');
  assert.equal(course.curriculum[1].lessons[0].content,'/uploads/english-lesson.pdf');
  assert.equal(course.what_you_learn,'A learning outcome');
  await page.setViewportSize({width:360,height:900});
  assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,'Filled native curriculum fits mobile');
  await page.setViewportSize({width:1366,height:950});
  assert.equal(await page.locator('.cl-primary .cur-section').count(),1,'Translated curriculum never changes Persian chapters');
  await panel.locator('.tag-pill button').click();
  assert.equal(await panel.locator('.tag-pill').count(),0);

  // Rich text keeps the original SVG toolbar and changes only the target editor.
  await page.goto(origin+'/dashboard/news-create.php',{waitUntil:'networkidle'});
  const sourceIcons=await page.locator('.cl-primary .editor-toolbar svg').evaluateAll(elements=>elements.map(e=>e.outerHTML));
  await page.locator('[data-content-language=en]').check();
  assert.deepEqual(await panel.locator('.editor-toolbar svg').evaluateAll(elements=>elements.map(e=>e.outerHTML)),sourceIcons);
  const editor=panel.locator('[contenteditable=true]');
  await editor.fill('English article');
  await editor.evaluate(e=>{const range=document.createRange();range.selectNodeContents(e);const selection=getSelection();selection.removeAllRanges();selection.addRange(range);e.dispatchEvent(new MouseEvent('mouseup',{bubbles:true}));});
  await panel.locator('.editor-toolbar [title="درشت"]').click();
  assert.match(await editor.innerHTML(),/font-weight: bold|<b>|<strong>/);
  assert.equal(await page.locator('.cl-primary #editor').innerText(),'');
  assert.deepEqual(errors,[],'No browser runtime errors');
  console.log('PASS native layout/icon parity across 13 forms in English/Arabic at four widths; event guide, shared fields, own media, course controls, copy and rich text.');
 }finally{await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});

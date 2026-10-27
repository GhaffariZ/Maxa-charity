// Local end-to-end editorial workflow. Never connects to a production database or sends email.
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const {spawnSync}=require('node:child_process');
const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const {installTranslationMock,translateTab}=require('./content-translation-mock.cjs');
const root=path.resolve(__dirname,'..'),origin='http://localhost:8080';
const php=path.join(root,'.local/runtime/php/php.exe');
const prefix='content-integration-'+Date.now();
const created=[],files=new Set(),directories=new Set();
function query(sql,parameters=[]){
 const code=`$c=json_decode(file_get_contents('.local/conf/secrets.json'),true);$d=new PDO('mysql:host=127.0.0.1;dbname=macsa_local;charset=utf8mb4','macsa_local',$c['dbPassword'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);if($d->query('SELECT DATABASE()')->fetchColumn()!=='macsa_local')exit(9);$a=json_decode($argv[1],true);$s=$d->prepare($a['sql']);$s->execute($a['parameters']);echo json_encode(preg_match('/^SELECT|^SHOW/i',$a['sql'])?$s->fetchAll():['id'=>$d->lastInsertId()],JSON_UNESCAPED_UNICODE);`;
 const result=spawnSync(php,['-c',path.join(root,'.local/conf/php.ini'),'-r',code,JSON.stringify({sql,parameters})],{cwd:root,encoding:'utf8'});
 assert.equal(result.status,0,result.stderr);return JSON.parse(result.stdout);
}
function remember(type,id){created.push({type,id:String(id)});return id;}
function state(type,id){return query('SELECT locale,enabled,copy_fa,payload FROM public_content_locales WHERE content_type=? AND content_id=?',[type,String(id)]);}
function assets(value){if(typeof value==='string'&&value.startsWith('/uploads/content-locales/'))files.add(value);else if(Array.isArray(value))value.forEach(assets);else if(value&&typeof value==='object')Object.values(value).forEach(assets);}
function track(type,id){for(const row of state(type,id))assets(JSON.parse(row.payload));}
function versions(fields){return JSON.stringify({enabled:['fa','en'],translations:{en:{copy:false,fields},ar:{copy:false,fields:{}}}});}
async function json(response){const text=await response.text();assert.ok(response.ok(),text.slice(0,600));return JSON.parse(text);}
async function form(api,route,data){return json(await api.post(origin+'/dashboard/'+route,{form:data}));}
async function get(api,route){return json(await api.get(origin+route));}

(async()=>{
 const browser=await chromium.launch({headless:true,channel:process.env.BROWSER_CHANNEL||'chrome'});
 const context=await browser.newContext({viewport:{width:390,height:844},reducedMotion:'reduce'});
 const errors=[];const page=await context.newPage();page.on('pageerror',error=>errors.push(page.url()+': '+error.message));
 try{
  const secrets=JSON.parse(fs.readFileSync(path.join(root,'.local/conf/secrets.json')));
  const login=await (await context.request.get(origin+'/dashboard/login.php')).text();
  const csrf=login.match(/name="csrf_token" value="([^"]+)"/)[1];
  await context.request.post(origin+'/dashboard/login.php',{form:{username:'local-admin',password:secrets.loginPassword,csrf_token:csrf}});
  const api=context.request;
  await installTranslationMock(page);
  // Hero: real translation actions (mock provider), editable snapshots, files and language removal.
  await page.goto(origin+'/dashboard/hero-create.php',{waitUntil:'networkidle'});
  await page.locator('[data-content-locale-ready=true]').waitFor();
  assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),'Mobile editor has no horizontal overflow');
  await page.locator('#title').fill(prefix+' FA');await page.locator('#editor').fill('متن فارسی');
  await page.locator('#featured_image').setInputFiles({name:'fixture.png',mimeType:'image/png',buffer:Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=','base64')});
  await page.locator('[data-content-language=en]').check();await translateTab(page,'en');
  await page.locator('.cl-panel:not([hidden]) input[type=text]').first().fill(prefix+' EN');
  assert.equal(await page.locator('[data-copy-fa]').count(),0,'Translation replaces the former Persian-copy checkbox');
  await translateTab(page,'en');
  assert.equal(await page.locator('.cl-panel:not([hidden]) input[type=text]').first().inputValue(),'EN: '+prefix+' FA','Clicking again refills a translated snapshot of current Persian text');
  await page.locator('.cl-panel:not([hidden]) input[type=text]').first().fill(prefix+' EN');
  await page.locator('[data-content-language=ar]').check();await translateTab(page,'ar');
  await page.locator('[role=tab]').filter({hasText:/^فارسی$/}).click();
  await page.locator('#title').fill(prefix+' FA revised');await page.evaluate(()=>document.getElementById('publish_date').value='');
  if(process.env.CONTENT_DEBUG)console.log(await page.evaluate(()=>{const fd=new FormData();ContentLanguages.append(fd);return JSON.parse(fd.get('content_languages'));}));
  await Promise.all([page.waitForURL(/hero-create\.php\?id=\d+/),page.locator('[onclick="saveHero()"]').click()]);
  const heroId=remember('hero_slides',new URL(page.url()).searchParams.get('id'));
  await page.waitForFunction(()=>document.querySelector('[data-content-locale-ready]'));
  track('hero_slides',heroId);
  const baseHero=query('SELECT * FROM hero_slides WHERE id=?',[heroId])[0];files.add(baseHero.image);
  for(const [locale,title]of [['fa',prefix+' FA revised'],['en',prefix+' EN'],['ar','AR: '+prefix+' FA']]){
   const feed=await get(api,'/dashboard/hero-list.php?lang='+locale);const record=feed.data.find(row=>Number(row.id)===Number(heroId));assert.equal(record?.title,title,'Hero visibility: '+locale);
  }
  const englishImage=JSON.parse(state('hero_slides',heroId).find(row=>row.locale==='en').payload).image;
  assert.match(englishImage,/^\/uploads\/content-locales\/hero_slides\/en\//);
  assert.notEqual(englishImage,baseHero.image,'Copied image is independent');
  await page.locator('[data-content-language=ar]').uncheck();
  await page.locator('#featured_image').setInputFiles(path.join(root,'public_html/favicon.png'));
  await Promise.all([page.waitForNavigation({waitUntil:'domcontentloaded'}),page.locator('[onclick="saveHero()"]').click()]);
  assert.equal(new URL(page.url()).searchParams.get('id'),String(heroId));track('hero_slides',heroId);
  files.add(query('SELECT image FROM hero_slides WHERE id=?',[heroId])[0].image);
  assert.equal(JSON.parse(state('hero_slides',heroId).find(row=>row.locale==='en').payload).image,englishImage,'Persian image replacement cannot change English');
  assert.ok(!(await get(api,'/dashboard/hero-list.php?lang=ar')).data.some(row=>Number(row.id)===Number(heroId)),'Unpublished Arabic hero removed');
  await page.locator('[role=tab]').filter({hasText:/^انگلیسی$/}).click();
  await page.locator('.cl-panel:not([hidden]) input[type=file]').setInputFiles(path.join(root,'public_html/favicon.png'));
  await Promise.all([page.waitForNavigation({waitUntil:'domcontentloaded'}),page.locator('[onclick="saveHero()"]').click()]);track('hero_slides',heroId);
  const uploadedImage=JSON.parse(state('hero_slides',heroId).find(row=>row.locale==='en').payload).image;assert.match(uploadedImage,/^\/uploads\/content-locales\/hero_slides\/en\//);assert.notEqual(uploadedImage,englishImage,'English tab uploads its own media');

  // News: workflow stays shared, language content and search stay independent.
  const category=query('SELECT id FROM news_categories WHERE is_active=1 LIMIT 1')[0].id;
  const news=await form(api,'news-save.php',{csrf_token:csrf,title:prefix+' خبر',content:'<p>متن فارسی آزمایش</p>',category_id:category,content_languages:versions({title:prefix+' English article',subtitle:'English subtitle',content:'<p>English article body</p>',category_name:'Editorial tests',author:'Editor',tags:'English tag'})});
  const newsId=remember('news',query('SELECT id FROM news WHERE news_code=?',[news.news_code])[0].id);
  assert.equal((await api.get(origin+'/en/'+newsId+'/')).status(),404,'Drafts are not public');
  await form(api,'news-status-update.php',{csrf_token:csrf,id:newsId,status:'published'});
  const englishArticle=await api.get(origin+'/en/'+newsId+'/');assert.equal(englishArticle.status(),200);assert.ok((await englishArticle.text()).includes('English article body'));
  assert.equal((await api.get(origin+'/ar/'+newsId+'/')).status(),404,'No Arabic article fallback');
  const search=await api.get(origin+'/en/news.php?q='+encodeURIComponent(prefix.toUpperCase()));assert.ok((await search.text()).includes(prefix+' English article'),'Search uses localized text without case sensitivity');
  const enFeed=await get(api,'/dashboard/recent-news-feed.php?lang=en');assert.ok(enFeed.items.some(row=>Number(row.id)===Number(newsId)));
  // Partner: create/edit preserves one identity and translates the public profile.
  const partner=await form(api,'personal-resume-create.php',{csrf_token:csrf,fullname:prefix+' همکار',email:'content-fixture@example.test',content_languages:versions({fullname:prefix+' Partner',bio_professional:'English biography',role:'Specialist',branch:'Test branch'})});
  const partnerId=remember('employee_profiles',partner.id);
  assert.ok((await get(api,'/api-employees.php?lang=en')).some(row=>Number(row.id)===Number(partnerId)&&row.fullname===prefix+' Partner'));
  assert.ok(!(await get(api,'/api-employees.php?lang=ar')).some(row=>Number(row.id)===Number(partnerId)));
  const profile=await api.get(origin+'/en/personal-resume-detail.php?id='+partnerId);assert.equal(profile.status(),200);assert.ok((await profile.text()).includes('English biography'));
  // Course: independent full curriculum, same course/payment identity.
  const courseResponse=await api.post(origin+'/dashboard/courses-create.php',{form:{csrf_token:csrf,title:prefix+' دوره',action:'publish',is_free:1,price:0,curriculum:JSON.stringify([{title:'فصل فارسی',lessons:[{title:'درس فارسی',type:'text',content:'متن',duration:5,is_preview:1}]}]),content_languages:versions({title:prefix+' Course',description:'<p>English course</p>',curriculum:[{title:'English section',lessons:[{title:'English lesson',type:'text',content:'English lesson body',duration:7,is_preview:1}]}]})}});
  assert.equal(courseResponse.status(),200);
  const courseId=remember('courses',query('SELECT id FROM courses WHERE title=?',[prefix+' دوره'])[0].id);
  const coursePage=await api.get(origin+'/en/courses/'+courseId);assert.equal(coursePage.status(),200);const courseHtml=await coursePage.text();assert.ok(courseHtml.includes('English section')&&courseHtml.includes('English lesson'));
  assert.equal((await api.get(origin+'/ar/courses/'+courseId)).status(),404,'Missing course language is unavailable');
  // Real native save adapters: stories, library, event children, branches, stands and CMS.
  const storyResponse=await api.post(origin+'/dashboard/macsa-stories.php',{form:{csrf_token:csrf,action:'create',title:prefix+' روایت',narrator_name:'راوی',content:'متن روایت',tag:'روایت کادر درمان',status:'published',content_languages:versions({title:prefix+' Story',narrator_name:'Narrator',content:'English story body',tag:'Clinical team'})}});
  assert.equal(storyResponse.status(),200);
  const storyId=remember('macsa_stories',query('SELECT id FROM macsa_stories WHERE title=?',[prefix+' روایت'])[0].id);
  const storyFeed=await get(api,'/api-stories.php?lang=en');assert.ok(storyFeed.data.some(row=>Number(row.id)===Number(storyId)&&row.tag_key==='staff'));
  assert.equal((await api.get(origin+'/ar/macsa-story.php?id='+storyId)).status(),404);
  assert.ok((await (await api.get(origin+'/en/macsa-story.php?id='+storyId)).text()).includes('English story body'));
  await api.post(origin+'/dashboard/maxapedia.php?section=books',{form:{csrf_token:csrf,action:'create',title:prefix+' کتاب',url:'https://example.test/fa.pdf',category:'آموزشی',status:'published',content_languages:versions({title:prefix+' Book',description:'English book body',url:'https://example.test/en.pdf',category:'Education'})}});
  const bookId=remember('maxapedia_items',query('SELECT id FROM maxapedia_items WHERE title=?',[prefix+' کتاب'])[0].id);
  assert.equal(state('maxapedia_items',bookId).find(row=>row.locale==='en').enabled,1);
  const bookPage=await (await api.get(origin+'/en/macsapedia-section.php?section=books')).text();assert.ok(bookPage.includes(prefix+' Book')&&bookPage.includes('https://example.test/en.pdf'));assert.ok(!bookPage.includes(prefix+' کتاب'));
  await api.post(origin+'/dashboard/maxapedia.php?section=gallery',{form:{csrf_token:csrf,action:'create',title:prefix+' آلبوم',status:'published',content_languages:versions({title:prefix+' Album',description:'English album',photos:[{image_url:'/favicon.png',caption:'English photo caption',layout_span:'wide'}]})}});
  const albumId=remember('maxapedia_items',query('SELECT id FROM maxapedia_items WHERE title=?',[prefix+' آلبوم'])[0].id);
  const albumPage=await (await api.get(origin+'/en/macsapedia-section.php?section=gallery&album='+albumId)).text();assert.ok(albumPage.includes('English photo caption'));assert.ok(!albumPage.includes(prefix+' آلبوم'));
  await api.post(origin+'/dashboard/maxapedia.php?section=books',{multipart:{csrf_token:csrf,action:'create',title:prefix+' سند',status:'published',content_languages:versions({title:prefix+' Document',url:{upload:'document'}}),'content_uploads[document]':{name:'translation.docx',mimeType:'application/vnd.openxmlformats-officedocument.wordprocessingml.document',buffer:Buffer.from('UEsDBBQAAAAIAHIAW11Rl+gEsQAAABQBAAATAAAAW0NvbnRlbnRfVHlwZXNdLnhtbH2QvQ7CMAyEX6XKilpXDAyo7QKswMALWInbRjQ/StxS3p4UUAcGRvu7O59c3Z6eYjabwcZa9Mx+DxBlTwZj4TzZRFoXDHIaQwce5R07gm1Z7kA6y2Q55yVDNNWRWhwHzk5zWkftbC2SXWSHj245VQv0ftASOWFYKDTVZaIQtKLsioHPaJIKHi4oUE6OJjmL/zGTVT9dc9e2WtLqX9J8cJJi1LYzQ7ESg9puvj3g/YzmBVBLAwQUAAAACAByAFtdR39RO3kAAAClAAAAEQAAAHdvcmQvZG9jdW1lbnQueG1sRc5RCsIwDAbgq5QdwAwffCi1J/ASta3boGlKEqne3nUivnwh/PwkrttE8Ym5qnlhqWL7dVpVmwWQuGYMcqKW6549iDHovvICnTg1pphFtrpggfM8XwDDVifvur1Teo/ZBjxQf6MYitEsan4XHYxgyIft8FuG/2P+A1BLAQIUABQAAAAIAHIAW11Rl+gEsQAAABQBAAATAAAAAAAAAAAAAACAAQAAAABbQ29udGVudF9UeXBlc10ueG1sUEsBAhQAFAAAAAgAcgBbXUd/UTt5AAAApQAAABEAAAAAAAAAAAAAAIAB4gAAAHdvcmQvZG9jdW1lbnQueG1sUEsFBgAAAAACAAIAgAAAAIoBAAAAAA==','base64')}}});
  const documentId=remember('maxapedia_items',query('SELECT id FROM maxapedia_items WHERE title=?',[prefix+' سند'])[0].id);track('maxapedia_items',documentId);
  const documentUrl=JSON.parse(state('maxapedia_items',documentId).find(row=>row.locale==='en').payload).url;assert.match(documentUrl,/\.docx$/,'Localized Office uploads preserve their download extension');assert.equal((await api.get(origin+documentUrl)).status(),200);
  const eventResponse=await api.post(origin+'/dashboard/event-save.php',{form:{csrf_token:csrf,title:prefix+' رویداد',jalali_date:'1405/08/05',status:'published','speaker_name[]':'سخنران','speaker_title[]':'عنوان',content_languages:versions({title:prefix+' Event',about:'<p>English event body</p>',speakers:[{name:'English speaker',title:'Researcher',image:''}]})}});
  assert.equal(eventResponse.status(),200,await eventResponse.text());
  const eventId=remember('events',query('SELECT id FROM events WHERE title=?',[prefix+' رویداد'])[0].id);
  // Editing an event with a Persian poster must leave the English picker visible and independent.
  query('UPDATE events SET poster=? WHERE id=?',['/favicon.png',eventId]);
  await page.goto(origin+'/dashboard/event-create.php?id='+eventId,{waitUntil:'networkidle'});
  await page.locator('[role=tab]').filter({hasText:/^انگلیسی$/}).click();
  assert.equal(await page.locator('.cl-panel:not([hidden]) [data-cl-field=poster] .hq-dropzone').isVisible(),true,'Persian media state cannot hide the English upload area');
  await page.locator('.cl-panel:not([hidden]) [data-cl-field=poster] input[type=file]').setInputFiles(path.join(root,'public_html/favicon.png'));
  await Promise.all([page.waitForURL(/event-list\.php/),page.locator('.cl-footer button').first().click()]);track('events',eventId);
  assert.equal(query('SELECT poster FROM events WHERE id=?',[eventId])[0].poster,'/favicon.png','English poster upload preserves Persian media');
  assert.match(JSON.parse(state('events',eventId).find(row=>row.locale==='en').payload).poster,/^\/uploads\/content-locales\/events\/en\//);
  const eventHtml=await (await api.get(origin+'/en/event.php?id='+eventId)).text();assert.ok(eventHtml.includes('English event body')&&eventHtml.includes('English speaker'));
  assert.equal((await api.get(origin+'/ar/event.php?id='+eventId)).status(),404);
  const eventNews=await json(await api.post(origin+'/dashboard/event-news-save.php',{headers:{Accept:'application/json'},form:{csrf_token:csrf,event_id:eventId,title:prefix+' خبر رویداد',content:'<p>خبر فارسی</p>',status:'published',content_languages:versions({title:prefix+' Event news',content:'<p>English event news</p>'})}}));
  assert.equal(eventNews.status,'success');const eventNewsId=remember('event_news',query('SELECT id FROM event_news WHERE title=?',[prefix+' خبر رویداد'])[0].id);
  assert.ok((await (await api.get(origin+'/en/event-news.php?id='+eventNewsId)).text()).includes('English event news'));
  assert.equal((await api.get(origin+'/ar/event-news.php?id='+eventNewsId)).status(),404);
  const branchSlug=prefix+'-branch';directories.add(path.join(root,'public_html/branches',branchSlug));
  await api.post(origin+'/dashboard/branch-create.php',{form:{csrf_token:csrf,name:prefix+' شعبه',slug:branchSlug,province:'استان',city:'شهر',admin_user:prefix.slice(-20),admin_pass:'Local-Test-Only-123','features[0]':'stands','features[1]':'pages',content_languages:versions({name:prefix+' Branch',province:'Test province',city:'Test city'})}});
  const branchId=remember('branches',query('SELECT id FROM branches WHERE slug=?',[branchSlug])[0].id);
  assert.equal(state('branches',branchId).find(row=>row.locale==='en').enabled,1);
  await api.post(origin+'/dashboard/switch-branch.php',{form:{csrf_token:csrf,branch_id:branchId}});
  assert.equal((await api.get(origin+'/dashboard/content-languages.php?type=macsa_stories&id='+storyId)).status(),200,'Global story editor keeps existing super-admin access from a branch');
  await api.post(origin+'/dashboard/branch-intro.php',{form:{csrf_token:csrf,title:prefix+' معرفی',intro_text:'معرفی فارسی',address:'نشانی',content_languages:versions({title:prefix+' Introduction',intro_text:'English branch introduction',address:'English address',images:[]})}});
  remember('branch_intros',branchId);
  const introHtml=await (await api.get(origin+'/en/branch-intro.php?branch='+branchSlug)).text();assert.ok(introHtml.includes('English branch introduction'));
  await api.post(origin+'/dashboard/stands.php?branch_id='+branchId,{form:{csrf_token:csrf,action:'create',title:prefix+' استند',preset_image:'/favicon.png',stand_type:'congrats',unit_price:1000,is_active:1,content_languages:versions({title:prefix+' Stand',description:'English stand body',image:'/favicon.png'})}});
  const standId=remember('stands',query('SELECT id FROM stands WHERE title=?',[prefix+' استند'])[0].id);
  await api.post(origin+'/dashboard/stands.php?branch_id='+branchId,{form:{csrf_token:csrf,action:'create',title:prefix+' English only stand',unit_price:1000,is_active:1,content_languages:JSON.stringify({enabled:['en'],translations:{en:{copy:false,fields:{title:'English only stand',image:'/favicon.png'}}}})}});
  const onlyEnglishStand=remember('stands',query('SELECT id FROM stands WHERE title=?',[prefix+' English only stand'])[0].id);
  assert.equal(state('stands',onlyEnglishStand).find(row=>row.locale==='fa').enabled,0,'English-only media does not require publishing Persian');
  const restricted=await browser.newContext();
  const restrictedHtml=await (await restricted.request.get(origin+'/dashboard/login.php')).text();
  const restrictedCsrf=restrictedHtml.match(/name="csrf_token" value="([^"]+)"/)[1];
  await restricted.request.post(origin+'/dashboard/login.php',{form:{username:prefix.slice(-20),password:'Local-Test-Only-123',csrf_token:restrictedCsrf}});
  assert.equal((await restricted.request.get(origin+'/dashboard/content-languages.php?type=stands&id='+standId)).status(),200,'Branch admin can load its own public content');
  const hqPage=query('SELECT p.id FROM pages p JOIN branches b ON b.id=p.branch_id WHERE b.is_hq=1 LIMIT 1')[0].id;
  assert.equal((await restricted.request.get(origin+'/dashboard/content-languages.php?type=pages&id='+hqPage)).status(),403,'Language metadata cannot cross branch boundaries');
  assert.equal((await restricted.request.get(origin+'/dashboard/content-languages.php?type=orders&id=1')).status(),403,'Private tables cannot enter the editorial endpoint');
  assert.equal((await restricted.request.post(origin+'/dashboard/content-translate.php',{form:{csrf_token:restrictedCsrf,type:'pages',id:hqPage,locale:'en',fields:'{}'}})).status(),403,'Translation cannot cross branch boundaries');
  assert.equal((await restricted.request.post(origin+'/dashboard/content-translate.php',{form:{csrf_token:restrictedCsrf,type:'orders',id:'1',locale:'en',fields:'{}'}})).status(),403,'Translation cannot access private modules');
  await restricted.close();
  const stands=await get(api,'/api/stands?branch_id='+branchId+'&lang=en');assert.ok(JSON.stringify(stands).includes(prefix+' Stand'));assert.ok(!JSON.stringify(await get(api,'/api/stands?branch_id='+branchId+'&lang=ar')).includes(prefix+' استند'));
  const component=prefix+'-component';directories.add(path.join(root,'public_html/dashboard/components',component));remember('components',component);
  await api.post(origin+'/dashboard/components-save.php',{form:{csrf_token:csrf,component_name:component,component_tag:'سایر',component_code:'<p>'+prefix+' محتوای فارسی</p>',content_languages:versions({content:'<p>'+prefix+' English component</p>',images:[]})}});
  const savedPage=await json(await api.post(origin+'/dashboard/template-save.php',{headers:{'X-CSRF-TOKEN':csrf},data:{name:prefix+' page',components:['header',component,'footer'],content_languages:versions({title:prefix+' English page'})}}));assert.equal(savedPage.status,'success');
  const pageId=remember('pages',query('SELECT id FROM pages WHERE title=?',[prefix+' page'])[0].id);query("UPDATE pages SET status='published' WHERE id=?",[pageId]);
  const savedSlug=query('SELECT slug FROM pages WHERE id=?',[pageId])[0].slug;
  assert.ok((await (await api.get(origin+'/en/'+branchSlug+'/'+savedSlug)).text()).includes(prefix+' English component'));
  assert.equal((await api.get(origin+'/ar/'+branchSlug+'/'+savedSlug)).status(),404);
  // Existing page editing must update the same record and preserve its original URL.
  const editedPage=await json(await api.post(origin+'/dashboard/template-save.php',{headers:{'X-CSRF-TOKEN':csrf},data:{id:pageId,name:prefix+' page revised',components:['header',component,'footer'],content_languages:versions({title:prefix+' English page revised'})}}));assert.equal(editedPage.status,'success');assert.equal(editedPage.slug,savedSlug);
  await api.post(origin+'/dashboard/switch-branch.php',{form:{csrf_token:csrf,branch_id:query('SELECT id FROM branches WHERE is_hq=1 LIMIT 1')[0].id}});
  assert.equal((await api.get(origin+'/dashboard/content-languages.php?type=stands&id='+standId)).status(),200,'HQ stand manager can load branch language versions');
  // Every public editorial screen mounts the shared editor; private screens never do.
  for(const route of ['news-create.php','courses-create.php','personal-resume-create.php','event-create.php','macsa-stories.php','maxapedia.php','campaign-create.php','component-create.php','template-create.php']){
   await page.goto(origin+'/dashboard/'+route,{waitUntil:'networkidle'});assert.ok(await page.locator('.cl-editor').count()>0,route+' language editor');
   if(process.env.CONTENT_DEBUG&&!await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1))console.log(route,await page.locator('body *').evaluateAll(elements=>elements.map(e=>({tag:e.tagName,cl:e.className,width:e.getBoundingClientRect().width,right:e.getBoundingClientRect().right})).filter(e=>e.right>innerWidth+1&&e.width>10).slice(0,15)));
   assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),route+' mobile overflow');
  }
  for(const route of ['tickets.php','medical-records.php','orders.php','financial-management.php','donation-impacts.php']){
   const response=await api.get(origin+'/dashboard/'+route);assert.ok(!(await response.text()).includes('content-locale-config'),route+' stays private');
  }
  assert.deepEqual(errors,[],'Unexpected editor JavaScript errors');
  console.log('PASS editorial browser workflows: real tabs/copy/edit/files, all local editorial save adapters and public versions, publication/search, mobile editors and private-module exclusion.');
 }finally{
  for(const row of query('SELECT id,image FROM hero_slides WHERE title LIKE ?',[prefix+'%']))if(!created.some(record=>record.type==='hero_slides'&&record.id===String(row.id))){remember('hero_slides',row.id);files.add(row.image);}
  for(const {type,id}of created.reverse()){
   track(type,id);
   if(type==='courses'){query('DELETE FROM course_lessons WHERE course_id=?',[id]);query('DELETE FROM course_sections WHERE course_id=?',[id]);}
   query('DELETE FROM public_content_locales WHERE content_type=? AND content_id=?',[type,id]);
   if(type==='components')continue;
   if(type==='events')for(const table of ['event_heroes','event_people','event_speakers','event_partners','event_news'])query('DELETE FROM '+table+' WHERE event_id=?',[id]);
   if(type==='branches')for(const table of ['dashboard_users','dashboard_roles','branch_features','pages'])query('DELETE FROM '+table+' WHERE branch_id=?',[id]);
   query('DELETE FROM `'+type+'` WHERE '+(type==='branch_intros'?'branch_id':'id')+'=?',[id]);
  }
  for(const url of files){const absolute=path.resolve(root,'public_html','.'+url);const allowed=path.resolve(root,'public_html/uploads')+path.sep;if(absolute.startsWith(allowed)&&fs.existsSync(absolute))fs.unlinkSync(absolute);}
  for(const directory of directories)if(path.resolve(directory).startsWith(root+path.sep)&&path.basename(directory).startsWith(prefix))fs.rmSync(directory,{recursive:true,force:true});
  await browser.close();
 }
})().catch(error=>{console.error(error);process.exitCode=1});

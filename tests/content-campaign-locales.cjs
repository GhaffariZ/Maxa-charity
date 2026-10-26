// Full campaign save/edit contract against an in-process localhost service, never the live service.
const assert=require('node:assert/strict');
const http=require('node:http');
const fs=require('node:fs');
const path=require('node:path');
const {spawn,spawnSync}=require('node:child_process');
const {request}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const root=path.resolve(__dirname,'..'),php=path.join(root,'.local/runtime/php/php.exe');
const secrets=JSON.parse(fs.readFileSync(path.join(root,'.local/conf/secrets.json')));
function database(sql,params=[]){
 const code=`$cfg=json_decode(file_get_contents('.local/conf/secrets.json'),true);$db=new PDO('mysql:host=127.0.0.1;dbname=macsa_local;charset=utf8mb4','macsa_local',$cfg['dbPassword'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);$a=json_decode($argv[1],true);$s=$db->prepare($a['sql']);$s->execute($a['params']);echo json_encode($s->columnCount()?$s->fetchAll():[]);`;
 const r=spawnSync(php,['-c','.local/conf/php.ini','-d','display_errors=stderr','-r',code,JSON.stringify({sql,params})],{cwd:root,encoding:'utf8',windowsHide:true});assert.equal(r.status,0,r.stderr);return JSON.parse(r.stdout);
}
const envelope=(enabled,title)=>JSON.stringify({enabled,translations:{en:{copy:false,fields:{title,description:'<p>Independent English campaign</p>'}},ar:{copy:false,fields:{}}}});
(async()=>{
 const branch=database('SELECT id,slug FROM branches WHERE is_hq=1 LIMIT 1')[0];
 const campaigns=[],updates=[],createdIds=[];let child,api,sparseResponse=false;
 const stub=http.createServer(async(req,res)=>{
  const chunks=[];for await(const chunk of req)chunks.push(chunk);
  const body=chunks.length?JSON.parse(Buffer.concat(chunks)):{};
  const url=new URL(req.url,'http://localhost');let data;
  if(url.pathname==='/api/v1/auth/client/token')data={access_token:'local-only-token',expires_in:900};
  else if(url.pathname==='/api/v1/campaign-categories')data=[{id:'local-food',name:'غذا'}];
  else if(req.method==='POST'&&url.pathname==='/api/v1/campaigns'){
   const campaign={...body,id:require('node:crypto').randomUUID(),created_at:new Date(Date.now()+campaigns.length*1000).toISOString(),raised_amount:1230,category:{name:'غذا'},image_url:'/favicon.png'};
   campaigns.unshift(campaign);createdIds.push(campaign.id);data=campaign;
  }else if(req.method==='PATCH'&&url.pathname.startsWith('/api/v1/campaigns/')){
   updates.push(body);const campaign=campaigns.find(c=>c.id===url.pathname.split('/').pop());Object.assign(campaign,body);data=sparseResponse?{id:campaign.id}:campaign;
  }else if(req.method==='GET'&&['/api/v1/campaigns','/public/v1/campaigns'].includes(url.pathname))data=campaigns.slice(0,Number(url.searchParams.get('limit')||100));
  else{res.writeHead(500);res.end(JSON.stringify({error:'Unexpected mock request'}));return;}
  res.setHeader('Content-Type','application/json');res.end(JSON.stringify({data,page:{next_cursor:null}}));
 });
 await new Promise(resolve=>stub.listen(0,'127.0.0.1',resolve));
 try{
  // Reserve a separate app port; only this child's environment points to the mock.
  const reserve=http.createServer();await new Promise(resolve=>reserve.listen(0,'127.0.0.1',resolve));const port=reserve.address().port;await new Promise(resolve=>reserve.close(resolve));
  const prefix='CAMPAIGN_CENTER_'+branch.slug.toUpperCase().replaceAll('-','_')+'_';
  const env={...process.env,DB_HOST:'127.0.0.1',DB_NAME:'macsa_local',DB_USER:'macsa_local',DB_PASS:secrets.dbPassword,CAMPAIGN_API_BASE_URL:'http://127.0.0.1:'+stub.address().port,[prefix+'CLIENT_ID']:'local-only-client',[prefix+'CLIENT_SECRET']:'local-only-secret',[prefix+'PUBLIC_KEY']:'local-only-key'};
  child=spawn(php,['-c','.local/conf/php.ini','-d','auto_prepend_file=','-S','127.0.0.1:'+port,'-t','public_html'],{cwd:root,env,windowsHide:true,stdio:'ignore'});
  const origin='http://127.0.0.1:'+port;api=await request.newContext({baseURL:origin});
  let login;for(let attempt=0;attempt<30;attempt++){try{login=await api.get('/dashboard/login.php');break;}catch{await new Promise(resolve=>setTimeout(resolve,100));}}
  assert.ok(login,'Isolated local PHP server starts');const token=(await login.text()).match(/name="csrf_token" value="([^"]+)"/)[1];
  await api.post('/dashboard/login.php',{form:{username:'local-admin',password:secrets.loginPassword,csrf_token:token}});
  async function save(data){const response=await api.post('/dashboard/campaign-save.php',{form:{csrf_token:token,target_amount:1000,category:'food',branch_slug:branch.slug,...data}});const body=await response.json();assert.equal(response.status(),200,JSON.stringify(body));assert.equal(body.status,'success');return body.id;}
  const id=await save({title:'Local campaign فارسی',description:'<p>شرح فارسی</p>',content_languages:envelope(['fa','en'],'Local English campaign')});
  const other=await save({title:'Newer Persian only',content_languages:envelope(['fa'],'')});assert.notEqual(id,other);
  const feed=await (await api.get('/dashboard/campaign-list.php?lang=en&branch='+branch.slug)).json();assert.equal(feed.data.length,1);assert.equal(feed.data[0].title,'Local English campaign');assert.equal(feed.data[0].short_description,'Independent English campaign');assert.equal(feed.data[0].target_amount,1000);assert.equal(feed.data[0].collected_amount,123);
  const latest=await (await api.get('/dashboard/campaign-latest.php?lang=en&branch='+branch.slug)).json();assert.equal(latest.data.id,id,'Latest selects a published version after language filtering');
  const edit=await api.get('/dashboard/campaign-edit.php?id='+id);assert.equal(edit.status(),200);assert.ok((await edit.text()).includes('Local English campaign'),'UUID edit form loads its versions');
  await save({id,title:'Edited Persian campaign',content_languages:envelope(['fa','en'],'Edited English campaign')});
  assert.equal(campaigns.length,2,'Editing never creates a second campaign identity');assert.equal(updates.length,1);
  for(const key of ['starts_at','ends_at','status','manual_raised_amount','participation_type'])assert.ok(!(key in updates[0]),'Editing preserves shared financial/status data: '+key);
  const changed=await (await api.get('/dashboard/campaign-latest.php?lang=en&branch='+branch.slug)).json();assert.equal(changed.data.title,'Edited English campaign','Edited language invalidates latest cache');
  sparseResponse=true;
  await save({id,title:'Copied Persian campaign',content_languages:JSON.stringify({enabled:['fa','en'],translations:{en:{copy:true,fields:{}}}})});
  const copied=await (await api.get('/dashboard/campaign-list.php?lang=en&branch='+branch.slug)).json();assert.equal(copied.data[0].image_url,'/favicon.png','Partial remote update responses preserve the copied image');assert.equal(copied.data[0].title,'Copied Persian campaign');
  await save({id,title:'Edited Persian campaign',content_languages:envelope(['fa'],'Edited English campaign')});
  assert.deepEqual((await (await api.get('/dashboard/campaign-list.php?lang=en&branch='+branch.slug)).json()).data,[]);
  assert.equal((await (await api.get('/dashboard/campaign-latest.php?lang=en&branch='+branch.slug)).json()).data,null);
  console.log('PASS campaign locale contract: real UUID create/edit adapters, independent feeds, latest/cache filtering, preserved financial identity; localhost mock only.');
 }finally{
  if(api)await api.dispose();if(child)child.kill();await new Promise(resolve=>stub.close(resolve));
  for(const id of createdIds)database("DELETE FROM public_content_locales WHERE content_type='campaigns' AND content_id=?",[id]);
 }
})().catch(error=>{console.error(error);process.exitCode=1});

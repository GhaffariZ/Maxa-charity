// Shared browser fixture. Translation never calls Google or consumes a real API quota.
async function installTranslationMock(page,respond){
 await page.route('**/dashboard/content-translate.php*',async route=>{
  const request=route.request(),form=await new Response(request.postDataBuffer(),{headers:{'Content-Type':request.headers()['content-type']}}).formData();
  const input=Object.fromEntries(form.entries());input.fields=JSON.parse(input.fields);
  const config=await page.locator('.content-locale-config').evaluateAll((elements,type)=>elements.map(e=>JSON.parse(e.textContent)).find(c=>c.type===type),input.type);
  const prefix=input.locale==='en'?'EN: ':'AR: ';
  function translate(schema,source){const result={};for(const[key,definition]of Object.entries(schema)){
   const value=source[key]??(definition.type==='list'?[]:'');
   if(definition.type==='list')result[key]=value.map(row=>translate(definition.fields,row));
   else if(['asset','select','number','checkbox'].includes(definition.type)||['button_link','banner_link','registration_url','instagram','linkedin'].includes(key)||(input.type==='courses'&&key==='content'&&['video','image','pdf'].includes(source.type)))result[key]=value;
   else result[key]=value?prefix+value:value;
  }return result;}
  const response=respond?await respond(input,translate(config.fields,input.fields)):null;
  if(response?.abort)return route.abort();
  await route.fulfill({status:response?.status||200,contentType:'application/json',body:JSON.stringify(response?.body||{fields:translate(config.fields,input.fields),model:'gemini-3.5-flash-lite'})});
 });
}
async function translateTab(page,locale){
 await page.locator('.cl-panel:not([hidden]) [data-translate-fa='+locale+']').click();
 await page.waitForFunction(()=>document.querySelector('.cl-panel:not([hidden]) [data-translation-status]')?.textContent.includes('ترجمه و پرکردن انجام شد'));
}
module.exports={installTranslationMock,translateTab};

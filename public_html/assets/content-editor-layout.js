/* Reuse each screen's Persian presentation. Only field values belong to a locale. */
(() => {
  'use strict';
  const wrappers='.input-group,.hq-field-group,.field-group,.form-group,.mx-field,.mx-photo-field,.field,.figma-field-col,.hq-state-box,.title-card,.subtitle-card,.editor-shell';
  const blocks='h1,h2,h3,h4,legend,.panel-heading,.card-title,.card-header,.hq-card-header,.fsection-head,.hint,.sub,.hq-card-subtitle,.hq-repeat-card-top,.hq-repeat-title,.hq-repeat-desc';
  const extra={events:{heroes:'#hero-list',people:'#person-list',speakers:'#speaker-list',partners:'#partner-list'},components:{images:'input[name="component_images[]"],input[name="new_component_images[]"]'},branch_intros:{images:'input[name="photos[]"]'},news:{category_name:'#category_id'}};
  const aliases={heroes:{title:'hero_title[]',description:'hero_description[]',button_label:'hero_button_label[]',button_link:'hero_link[]',image:'hero_image[]'},people:{role:'person_role[]',name:'person_name[]',title:'person_title[]',image:'person_image[]'},speakers:{name:'speaker_name[]',title:'speaker_title[]',image:'speaker_image[]'},partners:{name:'partner_name[]',logo:'partner_logo[]'}};

  function source(root,config,key,definition,path=''){
    if(path&&config.type==='courses'){
      const selector=root.matches('.cur-section')?{title:'.cur-sec-title',lessons:'.lessons'}[key]:{title:'.lesson-title-inp',type:'.type-seg',content:'.content-inp',duration:'.dur-inp'}[key];
      return selector?root.querySelector(selector):null;
    }
    if(path&&config.type==='events'){
      const list=path.split('.')[0],name=aliases[list]?.[key];
      return name?root.querySelector('[name="'+name+'"]'):null;
    }
    const custom=extra[config.type]?.[key]||config.sourceFields?.[key],selector=custom||definition.input||key;
    try{return root.querySelector(custom||/^[.#[]/.test(selector)?selector:'[name="'+CSS.escape(selector)+'"],#'+CSS.escape(selector));}catch{return null;}
  }
  function host(control,definition,root){
    if(!control)return null;
    if(control.matches('.cur-sec-title,.lesson-title-inp'))return control;
    if(definition.type==='list'&&control.type!=='file')return control;
    if(control.type==='file'&&control.closest('.hq-repeat-card'))return control.closest('.hq-repeat-card').querySelector('.hq-repeat-media');
    if(control.isContentEditable)return control.closest('.card')||control.parentElement;
    return control.closest(wrappers)||control.closest('label')||(control.parentElement===root?control:control.parentElement);
  }
  function scrub(root,prefix){
    for(const e of [root,...root.querySelectorAll('*')]){
      e.removeAttribute('name');e.removeAttribute('required');e.removeAttribute('autofocus');e.removeAttribute('disabled');
      if(e.id){e.dataset.clSourceId=e.id;e.id=prefix+'-'+e.id;}
      if(e.matches('label[for]'))e.htmlFor=prefix+'-'+e.htmlFor;
      for(const attr of [...e.attributes])if(/^on/i.test(attr.name)){e.dataset['cl'+attr.name[0].toUpperCase()+attr.name.slice(1)]=attr.value;e.removeAttribute(attr.name);}
    }
    root.querySelectorAll('script,iframe,template,style').forEach(e=>e.remove());return root;
  }
  function copyStyle(value,original){
    const style=getComputedStyle(original);
    value.className=original.className+' cl-native-value';
    value.classList.remove('input-disabled');
    value.removeAttribute('style');value.removeAttribute('hidden');
    if(original.required)value.dataset.clRequired='true';
    for(const key of ['fontFamily','fontSize','fontWeight','lineHeight','padding','border','borderRadius','backgroundColor','color','minHeight','boxSizing'])value.style[key]=style[key];
    value.style.width='100%';value.style.maxWidth='100%';
    if(original.rows)value.rows=original.rows;if(original.maxLength>0)value.maxLength=original.maxLength;
    if(original.placeholder)value.placeholder=original.placeholder;
    if(value.type==='file'&&original.accept)value.accept=original.accept;
    value.dir=value.type==='file'?'rtl':(value.closest('[lang]')?.lang==='ar'?'rtl':'auto');
  }
  function toolbar(bar,value){
    let range;
    const remember=()=>{const selection=getSelection();if(selection.rangeCount&&value.contains(selection.anchorNode)&&value.contains(selection.focusNode))range=selection.getRangeAt(0).cloneRange();};
    value.addEventListener('keyup',remember);value.addEventListener('mouseup',remember);value.addEventListener('input',remember);
    const execute=(command,arg)=>{value.focus();if(range&&value.contains(range.commonAncestorContainer)){const selection=getSelection();selection.removeAllRanges();selection.addRange(range);}document.execCommand('styleWithCSS',false,true);document.execCommand(command,false,arg);value.dispatchEvent(new Event('input',{bubbles:true}));remember();};
    bar.querySelectorAll('[data-cl-onclick],select').forEach(button=>{
      const code=button.dataset.clOnclick||button.dataset.clOnchange||'';
      button.addEventListener('mousedown',event=>{if(button.tagName!=='SELECT')event.preventDefault();});
      if(button.tagName==='SELECT'){
        button.addEventListener('change',()=>{if(!button.value)return;if(/FontSize/.test(code)){execute('fontSize','7');value.querySelectorAll('font[size="7"],span[style*="xxx-large"]').forEach(font=>{font.removeAttribute('size');font.style.fontSize=button.value+'px';});value.dispatchEvent(new Event('input',{bubbles:true}));}else execute(/Heading/.test(code)?'formatBlock':'fontName',button.value);if(!/Heading/.test(code))button.value='';});return;
      }
      button.addEventListener('click',()=>{
        const command=code.match(/format\(['"]([^'"]+)/)?.[1]||({alignRight:'justifyRight',alignCenter:'justifyCenter',alignLeft:'justifyLeft',alignJustify:'justifyFull',clearFormat:'removeFormat'})[code.split('(')[0]];
        if(command)execute(command);
        else if(/applyColor/.test(code))execute('foreColor',code.match(/['"]([^'"]+)/)?.[1]);
        else if(/insertLink/.test(code)){const url=prompt('نشانی لینک (https://… یا /…)');if(url&&/^(?:https?:\/\/|\/(?!\/)|#)/i.test(url))execute('createLink',url);}
        else if(/Image|image/.test(code)){
          const file=document.createElement('input');file.type='file';file.accept='image/jpeg,image/png,image/gif,image/webp';
          file.addEventListener('change',async()=>{if(!file.files[0])return;button.disabled=true;const data=new FormData();data.append('image',file.files[0]);data.append('csrf_token',document.querySelector('input[name="csrf_token"]')?.value||'');try{const response=await fetch('/dashboard/upload-inline-image.php',{method:'POST',body:data}),result=await response.json();if(!response.ok||!result.url)throw new Error(result.error||'آپلود تصویر انجام نشد.');execute('insertImage',result.url);}catch(error){alert(error.message);}finally{button.disabled=false;}});file.click();
        }
      });
    });
  }
  function decorate(field,original,control,definition,prefix){
    if(original===control&&definition.type==='list')return field;
    if(original===control){const value=field.querySelector('[data-cl-value]');if(value)copyStyle(value,control);field.querySelector(':scope>label')?.remove();field.className='cl-native-field';return field;}
    const result=scrub(original.cloneNode(true),prefix);result.dataset.clField=field.dataset.clField;result.classList.add('cl-native-field');
    const isAsset=definition.type==='asset',value=field.querySelector('[data-cl-value]');
    const sourceControls=[...original.querySelectorAll('input,select,textarea,[contenteditable=true]')];
    const copies=[...result.querySelectorAll('input,select,textarea,[contenteditable=true]')];
    const index=sourceControls.indexOf(control),placeholder=original===control?result:copies[index];
    if(isAsset){
      const file=field.querySelector('input[type=file]'),url=field.querySelector('[data-cl-asset-url]'),preview=field.querySelector('.cl-asset-preview');
      let nativeFile=control.type==='file'?placeholder:result.querySelector('input[type=file]');
      if(control.type==='file'&&!nativeFile){nativeFile=file;result.append(file);}
      if(nativeFile){copyStyle(file,control.type==='file'?control:original.querySelector('input[type=file]'));nativeFile.replaceWith(file);}
      else file.remove();
      if(control.type!=='file'&&control.type!=='hidden'&&placeholder){copyStyle(url,control);placeholder.replaceWith(url);}
      else url?.remove();
      const preset=result.querySelector('select');
      if(preset&&url){const blank=document.createElement('option');blank.value='';blank.textContent='انتخاب طرح';preset.prepend(blank);preset.value=url.value;url.hidden=true;result.append(url);preset.addEventListener('change',()=>{file.value='';url.value=preset.value;url.dispatchEvent(new Event('input',{bubbles:true}));});file.addEventListener('change',()=>{preset.value='';url.value='';});}
      if(nativeFile){file.id=prefix+'-'+(control.id||field.dataset.clField.replaceAll('.','-'))+'-file';if(!result.contains(url)||url.hidden)result.querySelector('label')?.setAttribute('for',file.id);}
      copies.forEach(e=>{if(e!==preset&&(e.isConnected||result.contains(e)))e.remove();});
      result.querySelectorAll('img,a,[id*="CurrentContainer"],.hq-meta-pills').forEach(e=>e.remove());
      const drop=result.querySelector('.dropzone,.uploader,.hq-dropzone,.upload-area,.uploader-box,.mx-dropzone')||(result.matches('.hq-repeat-media,.uploader-box,.mx-dropzone')?result:null);
      if(drop){drop.hidden=false;drop.style.removeProperty('display');}
      const previewSlot=result.querySelector('.dz-preview')||drop;
      (previewSlot||result).append(preview);
      const sync=()=>{const hasImage=!!preview.querySelector('img'),hasFile=!!preview.querySelector('img,a');if(previewSlot?.classList.contains('dz-preview'))previewSlot.classList.toggle('show',hasImage);if(drop){drop.classList.toggle('has-img',hasImage);drop.querySelectorAll('.dz-icon,.dz-main,.dz-hint,.up-inner,.hq-dropzone-text,.hq-dropzone-sub,.hq-repeat-media-empty,.mx-dropzone-icon,.mx-dropzone-title,.mx-dropzone-hint').forEach(e=>e.hidden=hasFile);}};
      new MutationObserver(sync).observe(preview,{childList:true,subtree:true});sync();
      if(drop&&nativeFile){file.hidden=true;drop.addEventListener('click',event=>{if(!event.target.closest('button,a'))file.click();});drop.addEventListener('dragover',e=>e.preventDefault());drop.addEventListener('drop',e=>{e.preventDefault();if(e.dataTransfer.files.length){file.files=e.dataTransfer.files;file.dispatchEvent(new Event('change'));}});}
      result.querySelectorAll('button').forEach(button=>{if(button.closest('.cl-asset-preview'))return;button.type='button';button.addEventListener('click',event=>{event.preventDefault();event.stopPropagation();if(/remove|delete|حذف/i.test(button.className+' '+button.textContent+' '+button.dataset.clOnclick)){file.value='';file.dispatchEvent(new Event('cl-clear'));}else file.click();});});
      if(!nativeFile&&!result.contains(url)){field.classList.add('cl-native-fallback');return field;}
      return result;
    }
    if(!value)return field;
    if(control.type==='hidden'&&result.querySelector('.tag-list')){
      const entry=result.querySelector('input:not([type=hidden])'),add=result.querySelector('button'),list=result.querySelector('.tag-list'),removeIcon=document.createElement('template');
      removeIcon.innerHTML='<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg>';
      value.hidden=true;result.querySelector('input[type=hidden]')?.replaceWith(value);
      const write=items=>{value.value=items.join('\n');value.dispatchEvent(new Event('input',{bubbles:true}));draw();};
      const items=()=>value.value.split('\n').map(item=>item.trim()).filter(Boolean);
      const draw=()=>{list.replaceChildren();items().forEach((text,index)=>{const pill=document.createElement('span'),label=document.createElement('span'),remove=document.createElement('button');pill.className='tag-pill';label.textContent=text;remove.type='button';remove.setAttribute('aria-label','حذف '+text);remove.append(removeIcon.content.cloneNode(true));remove.addEventListener('click',()=>write(items().filter((_,i)=>i!==index)));pill.append(label,remove);list.append(pill);});};
      const push=()=>{if(entry.value.trim()){write([...items(),entry.value.trim()]);entry.value='';}};add.addEventListener('click',push);entry.addEventListener('keydown',event=>{if(event.key==='Enter'){event.preventDefault();push();}});draw();return result;
    }
    if(control.matches('.type-seg')){
      value.hidden=true;result.append(value);
      result.querySelectorAll('button[data-t]').forEach(button=>{button.classList.toggle('on',button.dataset.t===value.value);button.addEventListener('click',event=>{event.stopPropagation();value.value=button.dataset.t;value.dispatchEvent(new Event('input',{bubbles:true}));});});
      return result;
    }
    copyStyle(value,control);if(placeholder&&placeholder!==result)placeholder.replaceWith(value);else result.append(value);
    copies.forEach(e=>{if(result.contains(e)&&!e.closest('.editor-toolbar'))e.remove();});
    const bar=result.querySelector('.editor-toolbar');
    if(bar&&value.isContentEditable){field.querySelector('.cl-tools')?.remove();toolbar(bar,value);}
    else if(value.isContentEditable){const tools=field.querySelector('.cl-tools');if(tools)result.insertBefore(tools,value);}
    result.querySelectorAll('.field-error,.char-counter,.field-meta,.subtitle-meta,.msel-trigger,.msel-options').forEach(e=>e.remove());
    result.querySelectorAll('.img-float-bar').forEach(e=>e.remove());
    result.querySelectorAll('button,select,input,textarea').forEach(e=>{if(e!==value&&!e.closest('.editor-toolbar'))e.remove();});
    if(field.dataset.clField==='branch')result.querySelectorAll('small').forEach(e=>e.textContent='شعبه مشترک است؛ فقط نام نمایشی محل خدمت را برای این زبان وارد کنید.');
    if(result.matches('label'))result.htmlFor=value.id;else result.querySelector('label')?.setAttribute('for',value.id);
    return result;
  }
  function project(generic,primary,config,prefix,path='',onRemove=null){
    const fields=new Map([...generic.children].filter(e=>e.dataset.clField).map(e=>[e.dataset.clField.split('.').pop(),e]));
    const definitions=path?config.rowFields:config.fields,slots=new Map(),controls=new Map(),parts=new Map(),unplaced=new Set(fields.keys());
    for(const [key,definition]of Object.entries(definitions||{})){
      let c=source(primary,config,key,definition,path),h=host(c,definition,primary);
      // Library uploads and their optional URL are separate native fields for one asset.
      const file=config.type==='maxapedia_items'&&key==='url'?primary.querySelector('#mxFileInput'):null;
      if(file&&c&&fields.has(key)){
        parts.set(h,{control:c,value:fields.get(key).querySelector('[data-cl-asset-url]')});c=file;h=host(c,definition,primary);
      }
      if(c&&h&&fields.has(key)&&!slots.has(h)){slots.set(h,key);controls.set(key,c);}
    }
    if(!slots.size)return generic;
    const contains=node=>[...slots.keys(),...parts.keys()].some(h=>node===h||node.contains(h));
    function visit(node){
      if(node.nodeType!==1)return node.nodeType===3&&node.textContent.trim()?document.createTextNode(node.textContent):null;
      if(parts.has(node)){
        const {control,value}=parts.get(node),copy=scrub(node.cloneNode(true),prefix),original=node.querySelectorAll('input,textarea'),index=[...original].indexOf(control),placeholder=copy.querySelectorAll('input,textarea')[index];copyStyle(value,control);placeholder.replaceWith(value);copy.classList.add('cl-native-field');copy.dataset.clAssetPart='url';copy.querySelector('label')?.setAttribute('for',value.id);return copy;
      }
      const key=slots.get(node);
      if(key){unplaced.delete(key);const field=fields.get(key),definition=definitions[key];if(field.dataset.clMulti)return decorate(field,node,controls.get(key),{...definition,type:'asset'},prefix);if(definition.type==='list'){
        field.querySelector(':scope>label')?.remove();const list=field.querySelector(':scope>.cl-list'),add=field.querySelector(':scope>.cl-add');
        if(list&&node.type!=='file')list.className='cl-list '+node.className;
        const prefixName={heroes:'hero',people:'person',speakers:'speaker',partners:'partner'}[key];
        const originalAdd=prefixName?primary.querySelector('[data-add="'+prefixName+'"]'):key==='curriculum'?primary.querySelector('#addSectionBtn'):null;
        if(add&&originalAdd){add.className='cl-add '+originalAdd.className;add.innerHTML=originalAdd.innerHTML;}
        field.className='cl-native-list-field';return field;
      }return decorate(field,node,controls.get(key),definition,prefix);}
      if(path&&node.matches('[data-cl-remove-row]')){const button=scrub(node.cloneNode(true),prefix);button.addEventListener('click',event=>{event.preventDefault();event.stopPropagation();onRemove?.();});return button;}
      if(path&&config.type==='courses'&&node.matches('.collapse,.delSec,.cfg,.delLesson')){
        const button=scrub(node.cloneNode(true),prefix);button.addEventListener('click',event=>{event.preventDefault();event.stopPropagation();if(button.matches('.delSec,.delLesson'))onRemove?.();else if(button.matches('.collapse'))button.closest('.cur-section').classList.toggle('collapsed');else button.closest('.lesson').classList.toggle('cfg-open');});return button;
      }
      if(node.matches('script,style,template,input,select,textarea,button,[contenteditable]'))return null;
      if(!contains(node)){
        if(path&&node.matches('.hq-repeat-card-top,.hq-repeat-actions')){const copy=scrub(node.cloneNode(true),prefix);copy.querySelectorAll('button').forEach(button=>button.addEventListener('click',event=>{event.preventDefault();event.stopPropagation();if(button.classList.contains('remove-row'))onRemove?.();else{const drawer=copy.closest('.hq-repeat-card').querySelector('.hq-repeat-drawer');drawer?.classList.toggle('is-open');button.textContent=drawer?.classList.contains('is-open')?'بستن ویرایش':'ویرایش';}}));return copy;}
        if(node.matches(blocks))return scrub(node.cloneNode(true),prefix);
        if(config.type==='events'&&node.matches('.hq-sidebar-nav'))return scrub(node.cloneNode(true),prefix);
        if(config.type==='courses'&&node.matches('.rail')){const copy=scrub(node.cloneNode(true),prefix);copy.querySelectorAll('.rail-actions,.chk[data-chk="price"],input,select,textarea,button').forEach(e=>e.remove());return copy;}
        if(path&&config.type==='courses'&&node.matches('.lesson-type-ic,.lesson-sub,.cur-sec-meta,.grip'))return scrub(node.cloneNode(true),prefix);
        if(path&&node.matches('.mx-drag-handle'))return scrub(node.cloneNode(true),prefix);
        if(path&&node.matches('.lesson-actions')){const copy=scrub(node.cloneNode(false),prefix);for(const child of node.children){const rendered=visit(child);if(rendered)copy.append(rendered);}return copy;}
        return null;
      }
      const copy=scrub(node.cloneNode(false),prefix);
      for(const child of [...node.childNodes]){const rendered=visit(child);if(rendered)copy.append(rendered);}
      return copy;
    }
    const result=document.createElement('div');result.className='cl-native-layout';result.dir='rtl';
    for(const child of [...primary.children]){const rendered=visit(child);if(rendered)result.append(rendered);}
    if(path&&primary.matches('.hq-repeat-card,.cur-section,.lesson-wrap,.mx-photo-item')){
      const card=scrub(primary.cloneNode(false),prefix);while(result.firstChild)card.append(result.firstChild);result.append(card);
    }
    for(const key of unplaced){const field=fields.get(key);if(config.type==='maxapedia_items'&&key==='photos'){field.className='cl-native-list-field';const add=field.querySelector(':scope>.cl-add');if(add){add.classList.add('mx-btn-upload-submit');add.textContent='افزودن تصویر';}}else field.classList.add('cl-native-fallback');result.append(field);}
    const ids=new Set([...result.querySelectorAll('[id]')].map(e=>e.id));
    result.querySelectorAll('.hq-sidebar-link').forEach(link=>{const target=prefix+'-'+(link.dataset.anchor||link.hash.slice(1));if(!ids.has(target)){link.remove();return;}link.href='#'+target;link.dataset.anchor=target;link.addEventListener('click',event=>{event.preventDefault();document.getElementById(target)?.scrollIntoView({behavior:'smooth',block:'start'});});});
    result.querySelectorAll('.hq-sidebar-tools').forEach(tools=>tools.querySelectorAll('button').forEach((button,index)=>button.addEventListener('click',()=>result.querySelectorAll('.hq-repeat-drawer').forEach(drawer=>drawer.classList.toggle('is-open',index===0)))));
    result.querySelectorAll('a[href^="#"]').forEach(link=>{if(link.classList.contains('hq-sidebar-link'))return;const id=prefix+'-'+link.hash.slice(1);if(ids.has(id))link.href='#'+id;else if(link.closest('.checklist'))link.remove();});
    result.querySelectorAll('.hq-step-group').forEach(e=>e.classList.add('is-active-tab','is-active-step'));
    result.querySelectorAll('.cl-native-field').forEach(e=>{e.hidden=false;e.style.removeProperty('display');for(let p=e.parentElement;p&&p!==result;p=p.parentElement)if(p.id.includes('role_container')||p.id.includes('job_category_container')||p.classList.contains('link-box'))p.style.removeProperty('display');});
    generic.replaceChildren(...result.childNodes);generic.className='cl-native-layout';generic.dir='rtl';
    const updateGuide=()=>{
      let done=0;const links=[...generic.querySelectorAll('.hq-sidebar-link')];
      for(const link of links){const section=generic.querySelector('#'+CSS.escape(link.hash.slice(1))),controls=[...section?.querySelectorAll('[data-cl-value]')||[]],required=controls.filter(e=>e.dataset.clRequired==='true'),filled=e=>String(e.isContentEditable?e.textContent:e.value||'').trim()!=='';const complete=required.length?required.every(filled):controls.some(filled)||!!section?.querySelector('.cl-asset-preview img,.cl-asset-preview a,.hq-repeat-card');link.querySelector('.hq-check')?.classList.toggle('is-done',complete);if(complete)done++;}
      const count=generic.querySelector('[data-cl-source-id="sidebarFilledCount"]');if(count)count.textContent=done.toLocaleString('fa-IR')+' از '+links.length.toLocaleString('fa-IR')+' پر شده';
      if(config.type==='courses')for(const [key,id,fallback]of [['title','railTitle','عنوان دوره شما'],['subtitle','railSub',''],['category','railCat','دسته‌بندی']]){const control=generic.querySelector('[data-cl-field="'+key+'"] [data-cl-value]'),target=generic.querySelector('[data-cl-source-id="'+id+'"]');if(target)target.textContent=control?.value||fallback;}
      if(config.type==='courses'){const image=generic.querySelector('[data-cl-field="thumbnail"] .cl-asset-preview img'),target=generic.querySelector('[data-cl-source-id="railThumb"]'),placeholder=generic.querySelector('[data-cl-source-id="railThumbPh"]');if(target){target.src=image?.src||'';target.style.display=image?'block':'none';}if(placeholder)placeholder.hidden=!!image;}
      if(config.type==='courses'&&!path){
        const lessons=generic.querySelectorAll('.lesson-wrap'),chapters=generic.querySelectorAll('.cur-section'),minutes=[...generic.querySelectorAll('[data-cl-field$=".duration"] [data-cl-value]')].reduce((sum,input)=>sum+Math.max(0,Number(input.value)||0),0),set=(id,text)=>{const target=generic.querySelector('[data-cl-source-id="'+id+'"]');if(target)target.textContent=text;};
        set('railLessons',lessons.length.toLocaleString('fa-IR'));set('railDur',minutes.toLocaleString('fa-IR')+' دقیقه');set('curStat',lessons.length.toLocaleString('fa-IR')+' درس · '+chapters.length.toLocaleString('fa-IR')+' فصل');
        const value=key=>generic.querySelector('[data-cl-field="'+key+'"] [data-cl-value]')?.value?.trim(),checks={title:!!value('title'),desc:(value('description')||'').length>20,thumb:!!generic.querySelector('[data-cl-field="thumbnail"] .cl-asset-preview img'),lessons:lessons.length>0};
        const list=[...generic.querySelectorAll('.chk')];for(const item of list)item.classList.toggle('done',!!checks[item.dataset.chk]);const percent=list.length?Math.round(list.filter(item=>item.classList.contains('done')).length/list.length*100):0;set('chkPct',percent.toLocaleString('fa-IR')+'٪');const bar=generic.querySelector('[data-cl-source-id="chkBar"]');if(bar)bar.style.width=percent+'%';
      }
    };
    generic.addEventListener('input',updateGuide);generic.addEventListener('change',updateGuide);updateGuide();
    const guideLinks=[...generic.querySelectorAll('.hq-sidebar-link')];
    const select=link=>guideLinks.forEach(other=>other.classList.toggle('is-active',other===link));
    if(guideLinks.length){select(guideLinks[0]);guideLinks.forEach(link=>link.addEventListener('click',()=>select(link)));if('IntersectionObserver'in window){const observer=new IntersectionObserver(entries=>{for(const entry of entries)if(entry.isIntersecting){const link=guideLinks.find(item=>item.hash.slice(1)===entry.target.id);if(link)select(link);}},{rootMargin:'-10% 0px -65% 0px'});guideLinks.forEach(link=>{const section=generic.querySelector('#'+CSS.escape(link.hash.slice(1)));if(section)observer.observe(section);});generic._clDispose=()=>observer.disconnect();}}
    if(path){const sync=()=>{const name=generic.querySelector('[data-cl-field$=".name"] [data-cl-value]')?.value,title=generic.querySelector('[data-cl-field$=".title"] [data-cl-value]')?.value,description=generic.querySelector('[data-cl-field$=".description"] [data-cl-value]')?.value;const h=generic.querySelector('.hq-repeat-title'),p=generic.querySelector('.hq-repeat-desc');if(h&&(name||title))h.textContent=name||title;if(p)p.textContent=description||((name&&title)?title:'');const count=generic.querySelector('.l-count');if(count)count.textContent=generic.querySelectorAll('.lesson-wrap').length.toLocaleString('fa-IR');const duration=generic.querySelector('[data-cl-field$=".duration"] [data-cl-value]')?.value,tag=generic.querySelector('.dur-tag');if(tag)tag.textContent=duration?'· '+Number(duration).toLocaleString('fa-IR')+' دقیقه':'';generic.querySelector('.lesson-prev-tag')?.remove();};generic.addEventListener('input',sync);sync();}
    return generic;
  }
  window.ContentEditorLayout={source,project};
})();

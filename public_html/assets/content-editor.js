(() => {
  'use strict';
  const labels={fa:'فارسی',ar:'عربی',en:'انگلیسی'};
  const editors=[];
  const node=(tag,attributes={},text='')=>{const e=document.createElement(tag);for(const [key,value]of Object.entries(attributes))e.setAttribute(key,value);e.textContent=text;return e;};
  const clone=value=>JSON.parse(JSON.stringify(value));
  let serial=0;

  function mount(config){
    const scope=document.querySelector(config.selector);if(!scope)return;
    const uid='content-languages-'+(++serial),form=scope.closest('form');
    let id=String(config.id||''),state=clone(config.state),active='fa',pending=new Map(),loading=false,failed=false;
    const originalSubmit=(form||document).querySelector(config.submit||'button[type="submit"],input[type="submit"],.btn-save');
    const primary=node('div',{'class':'cl-primary','role':'tabpanel',id:uid+'-fa','aria-labelledby':uid+'-tab-fa'});
    while(scope.firstChild)primary.append(scope.firstChild);
    const box=node('section',{'class':'cl-editor','data-content-type':config.type});
    const title=node('h3',{},'زبان‌های انتشار محتوا');
    const help=node('p',{'class':'cl-help'},'زبان‌های موردنظر را انتخاب کنید و محتوای هر زبان را در تب خودش وارد کنید. تنظیمات مشترک مثل تاریخ، مبلغ و وضعیت انتشار در تب فارسی قرار دارند. تغییر زبان‌های انتشار پس از ذخیره اعمال می‌شود.');
    const choices=node('fieldset',{'class':'cl-choices'});choices.append(node('legend',{},'این محتوا در کدام زبان‌ها نمایش داده شود؟'));
    const tabs=node('div',{'class':'cl-tabs','role':'tablist','aria-label':'ویرایش محتوای زبان‌ها'});
    const error=node('p',{'class':'cl-error','role':'alert',hidden:''});
    const panes=node('div',{'class':'cl-panes'}),buttons={},checks={},panels={fa:primary};
    const footer=node('div',{'class':'cl-footer'}),save=node('button',{'type':'button','class':'cl-save'},'ذخیرهٔ محتوا و زبان‌ها');
    save.addEventListener('click',()=>{if(!validate())return;if(originalSubmit){originalSubmit.disabled=false;originalSubmit.click();}else if(form)form.requestSubmit();});footer.append(save);
    box.append(title,help,choices,tabs,error,panes);
    scope.append(box,primary,footer);

    for(const locale of Object.keys(labels)){
      const label=node('label'),check=node('input',{type:'checkbox','data-content-language':locale});checks[locale]=check;
      check.checked=state.enabled.includes(locale);label.append(check,document.createTextNode(labels[locale]));choices.append(label);
      const tab=node('button',{'type':'button','role':'tab',id:uid+'-tab-'+locale,'aria-controls':uid+'-'+locale},labels[locale]);
      buttons[locale]=tab;tabs.append(tab);tab.addEventListener('click',()=>activate(locale));
      tab.addEventListener('keydown',event=>{if(!['ArrowLeft','ArrowRight','Home','End'].includes(event.key))return;event.preventDefault();const visible=Object.keys(labels).filter(l=>!buttons[l].hidden);let index=visible.indexOf(locale);index=event.key==='Home'?0:event.key==='End'?visible.length-1:(index+(event.key==='ArrowLeft'?1:-1)+visible.length)%visible.length;activate(visible[index]);buttons[visible[index]].focus();});
      check.addEventListener('change',()=>{
        state.enabled=Object.keys(labels).filter(l=>checks[l].checked);
        if(check.checked&&locale!=='fa'){ensure(locale);render(locale);activate(locale);}else if(active===locale&&locale!=='fa')activate('fa');
        refresh();
      });
      if(locale!=='fa'){
        panels[locale]=node('div',{'class':'cl-panel','role':'tabpanel',id:uid+'-'+locale,'aria-labelledby':uid+'-tab-'+locale,hidden:''});panes.append(panels[locale]);
      }
    }
    function ensure(locale){
      if(Array.isArray(state.translations))state.translations={...state.translations};
      const translation=state.translations[locale]??={copy:false,fields:{}};
      if(Array.isArray(translation.fields))translation.fields={...translation.fields};
      return translation;
    }
    function find(input,key){
      const selector=config.sourceFields?.[key]||input||key;
      if(selector.startsWith('#')||selector.startsWith('.'))return primary.querySelector(selector);
      return primary.querySelector('[name="'+CSS.escape(selector)+'"]')||primary.querySelector('#'+CSS.escape(selector));
    }
    function readPrimary(){
      const fields={...clone(state.source||{})};
      for(const [key,definition]of Object.entries(config.fields)){
        const input=find(definition.input,key);
        if(definition.type==='asset') {fields[key]={source_fa:true};continue;}
        if(key==='category_name'){const category=find('category_id','category_id');if(category?.selectedOptions?.length)fields[key]=category.selectedOptions[0].textContent.trim();continue;}
        if(definition.type==='list'){
          if(key==='images'&&['components','branch_intros'].includes(config.type)){
            const selector=config.type==='components'?'[name="component_images[]"],[name="new_component_images[]"]':'[name="photos[]"]';
            const added=[...primary.querySelectorAll(selector)].reduce((sum,input)=>sum+(input.files?.length||0),0);
            fields[key]=Array.from({length:(state.source?.[key]?.length||0)+added},()=>({url:{source_fa:true}}));
          }
          if(key==='curriculum'){
            const hidden=primary.querySelector('[name="curriculum"]');if(hidden?.value){try{fields[key]=JSON.parse(hidden.value);}catch{}}
          }
          if(config.type==='events'){
            const prefixes={heroes:'hero',people:'person',speakers:'speaker',partners:'partner'},prefix=prefixes[key];
            if(prefix){
              const mapping={name:'name',title:key==='heroes'?'title':'title',description:'description',role:'role',button_label:'button_label',button_link:'link',image:'existing',logo:'existing'};
              const values=child=>[...primary.querySelectorAll('[name="'+prefix+'_'+mapping[child]+'[]"]')];
              const count=Math.max(0,...Object.keys(definition.fields).map(child=>values(child).length));
              fields[key]=Array.from({length:count},(_,index)=>Object.fromEntries(Object.entries(definition.fields).map(([child,desc])=>[child,desc.type==='asset'?{source_fa:true}:(values(child)[index]?.value||'')])));
            }
          }
          continue;
        }
        if(input)fields[key]=input.isContentEditable?input.innerHTML:input.value;
        if(config.type==='employee_profiles'&&key==='role'){const select=primary.querySelector('#role_select');if(select&&getComputedStyle(select).display!=='none')fields[key]=select.value;}
      }
      return fields;
    }
    function refresh(){
      for(const locale of Object.keys(labels))buttons[locale].hidden=locale!=='fa'&&!state.enabled.includes(locale);
      buttons.fa.textContent=state.enabled.includes('fa')?'فارسی':'فارسی (منتشر نمی‌شود)';
      for(const locale of ['ar','en'])if(state.translations[locale]?.copy){state.translations[locale].fields=readPrimary();if(active===locale)render(locale);}
      sync();
    }
    function activate(locale){
      active=locale;
      for(const key of Object.keys(labels)){panels[key].hidden=key!==locale;buttons[key].setAttribute('aria-selected',String(key===locale));buttons[key].tabIndex=key===locale?0:-1;}
      footer.hidden=locale==='fa'&&!!originalSubmit;
      if(locale!=='fa')render(locale);
    }
    function changed(locale){const t=ensure(locale);t.copy=false;const checkbox=panels[locale].querySelector('[data-copy-fa]');if(checkbox)checkbox.checked=false;sync();}
    function render(locale){
      const panel=panels[locale],translation=ensure(locale);panel.replaceChildren();
      const copyLabel=node('label',{'class':'cl-copy'}),copy=node('input',{type:'checkbox','data-copy-fa':locale});copy.checked=translation.copy;
      copyLabel.append(copy,document.createTextNode('استفاده از همین محتوای تب فارسی برای '+labels[locale]));panel.append(copyLabel,node('p',{'class':'cl-help'},'این گزینه ترجمه انجام نمی‌دهد؛ متن و فایل‌های فارسی کپی می‌شوند. با ویرایش هر فیلد، تیک به‌طور خودکار برداشته می‌شود.'));
      copy.addEventListener('change',()=>{translation.copy=copy.checked;if(copy.checked)translation.fields=readPrimary();render(locale);sync();});
      const grid=node('div',{'class':'cl-fields',dir:locale==='en'?'ltr':'rtl',lang:locale});panel.append(grid);
      renderFields(grid,config.fields,translation.fields,locale,'');
    }
    function renderFields(parent,definitions,values,locale,path){
      for(const [key,originalDefinition]of Object.entries(definitions)){
        let definition=originalDefinition;
        if(config.type==='courses'&&key==='content'&&values.type){
          const accept={video:'video/*',image:'image/*',pdf:'application/pdf'};
          definition={...definition,...(accept[values.type]?{type:'asset',accept:accept[values.type]}:{type:'textarea'})};
        }
        const fieldPath=path?path+'.'+key:key,field=node('div',{'class':'cl-field'}),fieldId=uid+'-'+locale+'-'+fieldPath.replaceAll('.','-');
        const label=node('label',{'for':fieldId},definition.label);field.append(label);parent.append(field);
        let value=values[key]??(definition.type==='list'?[]:''),input;
        const update=newValue=>{values[key]=newValue;changed(locale);};
        if(definition.type==='list'){
          values[key]=Array.isArray(value)?value:[];
          const list=node('div',{'class':'cl-list'});field.append(list);
          values[key].forEach((item,index)=>{const row=node('div',{'class':'cl-list-row'}),remove=node('button',{type:'button','class':'cl-remove'},'حذف مورد');remove.addEventListener('click',()=>{values[key].splice(index,1);changed(locale);render(locale)});row.append(remove);list.append(row);renderFields(row,definition.fields,item,locale,fieldPath+'.'+index);});
          const add=node('button',{type:'button','class':'cl-add'},'افزودن مورد');add.addEventListener('click',()=>{values[key].push({});changed(locale);render(locale)});field.append(add);continue;
        }
        if(definition.type==='asset'){
          input=node('input',{type:'url',id:fieldId,placeholder:'https://… یا /uploads/…',dir:'ltr'});
          input.type='text';input.value=typeof value==='string'?value:'';field.append(input);
          input.addEventListener('input',()=>update(input.value));
          const file=node('input',{type:'file',accept:definition.accept||'image/*','aria-label':definition.label});field.append(file);
          file.addEventListener('change',()=>{if(!file.files[0])return;const token='asset_'+(++serial);pending.set(token,file.files[0]);update({upload:token});showPreview(URL.createObjectURL(file.files[0]));});
          const preview=node('div',{'class':'cl-asset-preview'});field.append(preview);
          function showPreview(url){preview.replaceChildren();if(url&&/^(?:https?:\/\/|blob:|\/(?!\/))/.test(url)){if((definition.accept||'image/*').startsWith('image/'))preview.append(node('img',{src:url,alt:definition.label}));else preview.append(node('a',{href:url,target:'_blank',rel:'noopener'},'مشاهدهٔ فایل'));}}
          const primaryValue=state.source?.[key];
          if(typeof value==='string')showPreview(value);else if(value?.source_fa&&typeof primaryValue==='string')showPreview(primaryValue);
          if(value?.source_fa)preview.append(node('span',{},'فایل تب فارسی پس از ذخیره کپی می‌شود.'));
          if(value?.upload)preview.append(node('span',{},pending.get(value.upload)?.name||'فایل انتخاب‌شده'));
          continue;
        }
        if(definition.type==='html'){
          const tools=node('div',{'class':'cl-tools'});input=node('div',{contenteditable:'true',id:fieldId,role:'textbox','aria-multiline':'true','aria-label':definition.label,'class':'cl-rich'});input.innerHTML=typeof value==='string'?value:'';
          for(const [command,text]of [['bold','B'],['italic','I'],['insertUnorderedList','فهرست'],['undo','بازگشت']]){const button=node('button',{type:'button','aria-label':text},text);button.addEventListener('mousedown',event=>event.preventDefault());button.addEventListener('click',()=>{input.focus();document.execCommand(command);update(input.innerHTML)});tools.append(button);}field.append(tools,input);input.addEventListener('input',()=>update(input.innerHTML));continue;
        }
        if(definition.type==='select'){input=node('select',{id:fieldId});for(const [option,text]of Object.entries(definition.options))input.append(node('option',{value:option},text));if(!Object.hasOwn(definition.options,value))value=values[key]=Object.keys(definition.options)[0];}
        else if(definition.type==='textarea')input=node('textarea',{id:fieldId,rows:'4'});
        else input=node('input',{id:fieldId,type:definition.type==='checkbox'?'checkbox':definition.type==='number'?'number':'text'});
        if(definition.type==='checkbox')input.checked=!!value;else input.value=String(value??'');
        input.addEventListener('input',()=>{update(definition.type==='checkbox'?input.checked:input.value);if(config.type==='courses'&&key==='type')render(locale);});field.append(input);
      }
    }
    const envelope=node('input',{type:'hidden',name:'content_languages'});scope.append(envelope);
    function sync(){envelope.value=JSON.stringify({enabled:state.enabled,translations:state.translations});}
    function validate(){
      error.hidden=true;
      if(loading||failed){error.textContent='نسخه‌های زبان هنوز بارگذاری نشده‌اند؛ دوباره این محتوا را باز کنید.';error.hidden=false;return false;}
      if(!state.enabled.length){error.textContent='حداقل یک زبان را برای انتشار انتخاب کنید.';error.hidden=false;return false;}
      for(const locale of ['ar','en'])if(state.enabled.includes(locale)){
        const t=ensure(locale),fields=t.copy?readPrimary():t.fields,key=['title','fullname','name'].find(k=>config.fields[k]);
        if(key&&!String(fields[key]||'').trim()){activate(locale);error.textContent='عنوان یا نام را در تب '+labels[locale]+' وارد کنید.';error.hidden=false;error.scrollIntoView({block:'center'});return false;}
      }
      if(!state.enabled.includes('fa')){
        const t=state.translations[state.enabled[0]];
        if(t&&!t.copy)for(const [key,definition]of Object.entries(config.fields)){
          if(!['text','textarea','html'].includes(definition.type))continue;
          const original=find(definition.input,key),value=t.fields[key];
          if(original&&typeof value==='string'&&!(original.isContentEditable?original.textContent:original.value).trim()){
            if(original.isContentEditable)original.innerHTML=value;else original.value=value;
          }
        }
      }
      refresh();return true;
    }
    function append(fd){sync();fd.set('content_languages',envelope.value);for(const [token,file]of pending)fd.set('content_uploads['+token+']',file);}
    async function load(nextId){
      id=String(nextId||'');loading=true;failed=true;error.hidden=true;
      try{
        const response=await fetch('/dashboard/content-languages.php?type='+encodeURIComponent(config.type)+'&id='+encodeURIComponent(id),{credentials:'same-origin'});
        const data=await response.json();if(!response.ok||!data.state)throw Error(data.message||'بارگذاری نسخه‌های زبان انجام نشد.');
        state=data.state;pending.clear();failed=false;for(const locale of Object.keys(labels))checks[locale].checked=state.enabled.includes(locale);activate('fa');refresh();
      }catch(e){error.textContent=e.message;error.hidden=false;throw e;}finally{loading=false;}
    }
    primary.addEventListener('input',refresh);primary.addEventListener('change',refresh);
    if(form){form.addEventListener('submit',event=>{if(event.submitter?.matches('[formaction*="delete"]'))return;if(!validate()){event.preventDefault();event.stopImmediatePropagation();}},true);form.addEventListener('formdata',event=>append(event.formData));form.addEventListener('invalid',()=>activate('fa'),true);}
    for(const locale of ['ar','en'])ensure(locale);
    activate('fa');refresh();
    editors.push({scope,form,type:config.type,validate,append,load});
    scope.dataset.contentLocaleReady='true';
  }
  window.ContentLanguages={
    validate(scope=document){return editors.filter(editor=>scope===document||scope===editor.scope||scope.contains(editor.scope)).every(editor=>editor.validate());},
    append(fd,scope=document){for(const editor of editors)if(scope===document||scope===editor.scope||scope.contains(editor.scope))editor.append(fd);},
    load(type,id){return Promise.all(editors.filter(editor=>editor.type===type).map(editor=>editor.load(id)));}
  };
  document.querySelectorAll('.content-locale-config').forEach(element=>mount(JSON.parse(element.textContent)));
})();

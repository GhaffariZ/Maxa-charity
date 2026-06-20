/* ── Theme ── */
(function(){
  try{
    const t=localStorage.getItem('maxa-theme');
    if(t==='dark') document.documentElement.setAttribute('data-theme','dark');
  }catch(e){}
})();

const themeToggle=document.getElementById('themeToggle');
const themeIcDark=document.getElementById('themeIcDark');
const themeIcLight=document.getElementById('themeIcLight');
function applyTheme(dark){
  document.documentElement.setAttribute('data-theme',dark?'dark':'light');
  themeIcDark.style.display=dark?'none':'block';
  themeIcLight.style.display=dark?'block':'none';
  try{localStorage.setItem('maxa-theme',dark?'dark':'light');}catch(e){}
}
applyTheme(document.documentElement.getAttribute('data-theme')==='dark');
themeToggle.addEventListener('click',()=>applyTheme(document.documentElement.getAttribute('data-theme')!=='dark'));

/* ── Toast ── */
function toast(msg,type='ok',ms=3800){
  const wrap=document.getElementById('toastWrap');
  const t=document.createElement('div');
  t.className=`toast ${type}`;
  t.textContent=msg;
  wrap.appendChild(t);
  requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));
  setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),350);},ms);
}

/* ── JWT helper ── */
function getToken(){
  const keys=['access_token','token','jwt','maxa_token','auth_token','maxa_access_token'];
  for(const k of keys){
    try{
      const v=localStorage.getItem(k)||sessionStorage.getItem(k);
      if(v && v.split('.').length===3) return v;
    }catch(e){}
  }
  // Try parsing auth objects
  const objKeys=['auth','user','maxa_auth','maxa_user','benefactor_auth'];
  for(const k of objKeys){
    try{
      const raw=localStorage.getItem(k)||sessionStorage.getItem(k);
      if(!raw) continue;
      const obj=JSON.parse(raw);
      const t=obj?.token||obj?.access_token||obj?.jwt||obj?.data?.token||obj?.data?.access_token;
      if(t && typeof t==='string' && t.split('.').length===3) return t;
    }catch(e){}
  }
  return null;
}

/* ── API ── */
const API='/api';
async function apiFetch(path,opts={}){
  const token=getToken();
  const headers={'Content-Type':'application/json',...(opts.headers||{})};
  if(token) headers['Authorization']='Bearer '+token;
  const res=await fetch(API+path,{...opts,headers});
  const json=await res.json().catch(()=>({}));
  return {ok:res.ok,status:res.status,json};
}

/* ── Date formatter ── */
function fmtDate(dt){
  if(!dt) return '';
  try{
    return new Intl.DateTimeFormat('fa-IR',{year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit'}).format(new Date(dt));
  }catch(e){return dt;}
}

/* ── State ── */
let tickets=[];

/* ── Render ── */
const STATUS_FA={open:'باز',answered:'پاسخ داده شد',closed:'بسته‌شده'};
const STATUS_CLS={open:'badge-open',answered:'badge-answered',closed:'badge-closed'};
const CAT_FA={general:'عمومی',donation:'کمک‌ها و پرداخت',technical:'مشکل فنی',campaign:'کمپین‌ها',other:'سایر'};
const PRI_FA={low:'کم',normal:'عادی',high:'فوری'};
const PRI_CLS={low:'badge-low',normal:'badge-normal',high:'badge-high'};

function renderList(){
  const list=document.getElementById('tkList');
  if(!tickets.length){
    list.innerHTML=`<div class="tk-empty">
      <div class="tk-empty-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
      <p>هنوز تیکتی ثبت نکرده‌اید.<br>برای ارتباط با پشتیبانی روی «تیکت جدید» کلیک کنید.</p>
    </div>`;
    return;
  }
  list.innerHTML=tickets.map(t=>renderCard(t)).join('');
  list.querySelectorAll('.tk-card-head').forEach(h=>{
    h.addEventListener('click',()=>toggleCard(h.closest('.tk-card'),h.dataset.id));
  });
  list.querySelectorAll('.btn-reply-send').forEach(btn=>{
    btn.addEventListener('click',()=>sendReply(btn.dataset.id,btn));
  });
}

function renderCard(t){
  const hasUnread=t.has_unread;
  const initials=t.subject ? t.subject.trim()[0]||'ت' : 'ت';
  return `<div class="tk-card${hasUnread?' has-unread':''}" id="card-${t.id}">
    <div class="tk-card-head" data-id="${t.id}">
      <div class="tk-avatar">${initials}</div>
      <div class="tk-meta">
        <div class="tk-subject">
          ${esc(t.subject)}
          ${hasUnread?'<span class="tk-unread-dot" title="پاسخ جدید دارید"></span>':''}
        </div>
        <div class="tk-badges">
          <span class="badge ${STATUS_CLS[t.status]||'badge-open'}">${STATUS_FA[t.status]||t.status}</span>
          <span class="badge badge-cat">${CAT_FA[t.category]||t.category}</span>
          <span class="badge ${PRI_CLS[t.priority]||'badge-normal'}">${PRI_FA[t.priority]||t.priority}</span>
        </div>
        <div class="tk-date">${fmtDate(t.updated_at||t.created_at)}</div>
      </div>
      <div class="tk-chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
    </div>
    <div class="tk-thread" id="thread-${t.id}">
      <div class="loader-wrap" id="thread-loader-${t.id}"><div class="spinner" style="width:28px;height:28px;border-width:2px"></div></div>
    </div>
  </div>`;
}

function renderThread(ticket,threadEl){
  const closed=ticket.status==='closed';
  let html=`<div class="tk-msg-orig"><div class="tk-msg-orig-label">پیام اولیه</div><p>${esc(ticket.message)}</p></div>`;
  if(ticket.replies && ticket.replies.length){
    html+=`<div class="reply-list">`;
    for(const r of ticket.replies){
      const isAdmin=r.author==='admin';
      html+=`<div class="reply-item ${isAdmin?'admin':'user'}">
        <div class="reply-av ${isAdmin?'admin':'user'}">${isAdmin?'پ':'ش'}</div>
        <div class="reply-content">
          <div class="reply-author">${isAdmin?'پشتیبانی مکسا':'شما'}</div>
          <div class="reply-msg">${esc(r.message)}</div>
          <div class="reply-date">${fmtDate(r.created_at)}</div>
        </div>
      </div>`;
    }
    html+=`</div>`;
  }
  if(closed){
    html+=`<div class="tk-status-closed-note">این تیکت بسته شده است.</div>`;
  } else {
    html+=`<div class="reply-form">
      <textarea placeholder="پیام خود را بنویسید…" id="reply-txt-${ticket.id}" maxlength="5000"></textarea>
      <div class="reply-form-footer">
        <button class="btn-reply-cancel" data-collapse="${ticket.id}">بستن</button>
        <button class="btn-reply-send" data-id="${ticket.id}" id="reply-btn-${ticket.id}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          ارسال پیام
        </button>
      </div>
    </div>`;
  }
  threadEl.innerHTML=html;
  threadEl.querySelectorAll('.btn-reply-send').forEach(btn=>{
    btn.addEventListener('click',()=>sendReply(btn.dataset.id,btn));
  });
  threadEl.querySelectorAll('[data-collapse]').forEach(btn=>{
    btn.addEventListener('click',()=>collapseCard(btn.dataset.collapse));
  });
}

function toggleCard(card,id){
  const isExp=card.classList.contains('expanded');
  if(isExp){collapseCard(id);return;}
  card.classList.add('expanded');
  const thread=document.getElementById('thread-'+id);
  thread.style.display='flex';
  if(!thread.dataset.loaded){
    loadTicket(id,thread);
  }
}

function collapseCard(id){
  const card=document.getElementById('card-'+id);
  if(!card) return;
  card.classList.remove('expanded');
  const thread=document.getElementById('thread-'+id);
  if(thread) thread.style.display='none';
}

async function loadTicket(id,threadEl){
  const {ok,json}=await apiFetch('/tickets/'+id);
  threadEl.dataset.loaded='1';
  if(!ok){
    threadEl.innerHTML=`<div style="padding:20px;color:var(--err);font-size:13px">خطا در بارگذاری تیکت.</div>`;
    return;
  }
  const ticket=json.data?.ticket;
  if(!ticket){threadEl.innerHTML='';return;}
  // Update local unread flag
  const t=tickets.find(t=>t.id==id);
  if(t) t.has_unread=false;
  const card=document.getElementById('card-'+id);
  if(card) card.classList.remove('has-unread');
  renderThread(ticket,threadEl);
}

async function sendReply(id,btn){
  const txt=document.getElementById('reply-txt-'+id);
  const msg=(txt?.value||'').trim();
  if(!msg){toast('پیام نمی‌تواند خالی باشد.','fail');return;}
  btn.disabled=true;
  btn.textContent='در حال ارسال…';
  const {ok,json}=await apiFetch('/tickets/'+id+'/reply',{method:'POST',body:JSON.stringify({message:msg})});
  if(ok){
    toast('پیام شما ثبت شد.','ok');
    // Reload this ticket thread
    const thread=document.getElementById('thread-'+id);
    thread.dataset.loaded='';
    await loadTicket(id,thread);
    // Also refresh list summary
    await loadTickets();
  } else {
    toast(json.error?.message||'خطا در ارسال پیام.','fail');
    btn.disabled=false;
    btn.innerHTML=`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> ارسال پیام`;
  }
}

/* ── Load tickets list ── */
async function loadTickets(){
  const {ok,json}=await apiFetch('/tickets');
  if(ok){
    tickets=json.data?.tickets||[];
    renderList();
  }
}

/* ── New ticket form ── */
document.getElementById('btnNewTicket').addEventListener('click',()=>{
  const sec=document.getElementById('formSection');
  const isHidden=sec.style.display==='none';
  sec.style.display=isHidden?'block':'none';
  if(isHidden) sec.scrollIntoView({behavior:'smooth',block:'start'});
});
document.getElementById('btnCancelForm').addEventListener('click',()=>{
  document.getElementById('formSection').style.display='none';
});

document.getElementById('ticketForm').addEventListener('submit',async(e)=>{
  e.preventDefault();
  const form=e.target;
  const btn=document.getElementById('btnSubmit');
  btn.disabled=true;
  btn.innerHTML=`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4m0 12v4m-8-8H0m20 0h-4m-1.17-7.07-2.83 2.83M7 17l-2.83 2.83M4.93 4.93 7.76 7.76M17 17l2.83 2.83"/></svg> در حال ارسال…`;
  const data={
    subject: form.subject.value.trim(),
    message: form.message.value.trim(),
    category: form.category.value,
    priority: form.priority.value,
    contact: form.contact.value.trim()||undefined,
  };
  const {ok,json}=await apiFetch('/tickets',{method:'POST',body:JSON.stringify(data)});
  if(ok){
    toast('تیکت شما با موفقیت ثبت شد.','ok');
    form.reset();
    document.getElementById('formSection').style.display='none';
    await loadTickets();
  } else {
    const err=json.error;
    let msg='خطا در ارسال تیکت.';
    if(err?.fields){
      const first=Object.values(err.fields)[0];
      if(first) msg=first;
    } else if(err?.message){
      msg=err.message;
    }
    toast(msg,'fail');
    btn.disabled=false;
    btn.innerHTML=`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> ارسال تیکت`;
  }
});

/* ── XSS helper ── */
function esc(s){
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ── Boot ── */
async function boot(){
  const token=getToken();
  document.getElementById('pageLoader').style.display='none';
  if(!token){
    document.getElementById('authWall').style.display='block';
    return;
  }
  // Verify token by calling /user/me
  const {ok}=await apiFetch('/user/me');
  if(!ok){
    document.getElementById('authWall').style.display='block';
    return;
  }
  document.getElementById('ticketUI').style.display='block';
  await loadTickets();
}

boot();

<!-- ============================================================================
     کامپوننت «همکارانِ شعبه» — اسلایدرِ همکارانِ همان شعبه + دکمه‌ی «همه‌ی همکاران»
     ----------------------------------------------------------------------------
     داده از /api-employees.php می‌آید و فقط همکارانِ «همین شعبه» (تطبیق با نام/slugِ
     شعبه از window.__MAXA_BRANCH__/NAME) به‌صورتِ اسلایدر نمایش داده می‌شوند. دکمه‌ی
     «مشاهده‌ی همه‌ی همکاران» به /{branch}/network می‌رود که همان صفحه‌ی شبکه‌ی همکارانِ
     مکسا است ولی فقط همکارانِ همین شعبه را نشان می‌دهد.

     دیزاین کاملاً منطبق با صفحه‌ی شبکه‌ی همکاران (کارتِ گرد، پیلِ محل خدمت/سمت) و با
     پالتِ برندِ مکسا (فیروزه‌ای #007b7a + طلایی/نارنجی) هم‌خانواده با بقیه‌ی سایت.
============================================================================ -->
<style>
  @font-face{font-family:'Vazirmatn';src:url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations');font-weight:100 900;font-style:normal;font-display:swap}
  .bp{--teal:#007b7a;--teal-d:#006665;--teal-l:#0d9488;--gold:#d97706;--gold-glow:rgba(217,119,6,.10);
      --text:#1e293b;--muted:#64748b;--line:#e2e8f0;--surface:#fff;--bg:#f4f7f6;
      --ease:cubic-bezier(.25,1,.5,1);font-family:'Vazirmatn',Tahoma,sans-serif;direction:rtl;color:var(--text);
      background:var(--bg);padding:60px 0}
  .bp *{box-sizing:border-box}
  .bp-wrap{max-width:1200px;margin:0 auto;padding:0 20px}
  .bp a{text-decoration:none;color:inherit}
  .bp-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:28px;flex-wrap:wrap}
  .bp-head h2{margin:0;font-size:28px;font-weight:800;position:relative;padding-bottom:12px}
  .bp-head h2::after{content:"";position:absolute;right:0;bottom:0;width:48px;height:3px;border-radius:999px;background:var(--teal)}
  .bp-head .bp-sub{margin:6px 0 0;color:var(--muted);font-size:13.5px}
  .bp-all{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,var(--teal),var(--teal-d));
      color:#fff;font-weight:700;font-size:14px;padding:11px 20px;border-radius:13px;white-space:nowrap;
      box-shadow:0 10px 22px -10px rgba(0,123,122,.7);transition:transform .15s var(--ease),box-shadow .22s}
  .bp-all:hover{transform:translateY(-2px);box-shadow:0 16px 30px -10px rgba(0,123,122,.85)}

  /* slider */
  .bp-slider{position:relative}
  .bp-track{display:flex;gap:22px;overflow-x:auto;scroll-behavior:smooth;scroll-snap-type:x mandatory;
      padding:6px 2px 16px;-ms-overflow-style:none;scrollbar-width:none}
  .bp-track::-webkit-scrollbar{display:none}
  .bp-card{flex:0 0 250px;scroll-snap-align:start;background:var(--surface);border:1px solid var(--line);
      border-radius:22px;overflow:hidden;box-shadow:0 10px 30px -12px rgba(0,0,0,.07);
      transition:transform .4s var(--ease),box-shadow .4s var(--ease),border-color .4s var(--ease)}
  .bp-card:hover{transform:translateY(-6px);box-shadow:0 22px 40px -16px rgba(0,123,122,.28);border-color:var(--teal)}
  .bp-img{width:100%;height:240px;background:var(--bg);overflow:hidden;position:relative}
  .bp-img img{width:100%;height:100%;object-fit:cover;transition:transform .6s var(--ease)}
  .bp-card:hover .bp-img img{transform:scale(1.06)}
  .bp-ph{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:54px;color:#b8c2c6}
  .bp-info{padding:20px 18px 18px;text-align:center}
  .bp-name{margin:0 0 14px;font-size:17px;font-weight:800;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .bp-pills{display:flex;flex-direction:column;gap:8px}
  .bp-pill{display:flex;align-items:center;justify-content:center;gap:9px;padding:8px 12px;border-radius:13px;
      font-size:12.5px;font-weight:700;border:1px solid transparent}
  .bp-pill .dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
  .bp-pill .vt{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .bp-pill-branch{background:var(--bg);color:var(--text);border-color:var(--line)}
  .bp-pill-branch .dot{background:var(--teal)}
  .bp-pill-role{background:var(--gold-glow);color:var(--gold)}
  .bp-pill-role .dot{background:var(--gold)}
  .bp-more{margin-top:14px}
  .bp-detail{display:inline-flex;align-items:center;gap:6px;color:var(--teal);font-weight:800;font-size:12.5px}

  /* arrows */
  .bp-nav{position:absolute;top:42%;width:42px;height:42px;border-radius:999px;border:1px solid var(--line);
      background:#fff;color:var(--teal);display:grid;place-items:center;cursor:pointer;z-index:3;
      box-shadow:0 8px 20px -8px rgba(0,0,0,.18);transition:.2s var(--ease)}
  .bp-nav:hover{background:var(--teal);color:#fff;border-color:var(--teal)}
  .bp-nav svg{width:18px;height:18px}
  .bp-nav.prev{right:-6px}.bp-nav.next{left:-6px}
  .bp-nav[hidden]{display:none}

  .bp-soon{text-align:center;padding:54px 20px;border:1.5px dashed var(--line);border-radius:18px;
      background:rgba(0,123,122,.02);color:var(--muted)}
  .bp-soon svg{width:40px;height:40px;color:var(--teal);opacity:.7;margin-bottom:12px}
  .bp-soon b{display:block;font-size:17px;font-weight:800;color:var(--text);margin-bottom:6px}

  @media(max-width:600px){.bp{padding:46px 0}.bp-head h2{font-size:23px}.bp-card{flex-basis:78%}.bp-nav{display:none}}
</style>

<section class="bp" id="bp-root" data-branch-partners>
  <div class="bp-wrap">
    <div class="bp-head">
      <div>
        <h2>همکاران شعبه</h2>
        <p class="bp-sub" id="bp-sub">تیمی که در این شعبه در کنار شما هستند.</p>
      </div>
      <a class="bp-all" id="bp-all" href="#" target="_top">
        مشاهده‌ی همه‌ی همکاران
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </a>
    </div>
    <div class="bp-slider">
      <button class="bp-nav prev" id="bp-prev" aria-label="قبلی" hidden><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></button>
      <button class="bp-nav next" id="bp-next" aria-label="بعدی" hidden><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></button>
      <div class="bp-track" id="bp-track"><div class="bp-soon" style="flex:1">در حال بارگذاری همکاران...</div></div>
    </div>
  </div>
</section>

<script>
(function(){
  var root=document.getElementById('bp-root'); if(!root) return;
  var SLUG=(typeof window.__MAXA_BRANCH__==='string')?window.__MAXA_BRANCH__:'';
  var NAME=(typeof window.__MAXA_BRANCH_NAME__==='string'&&window.__MAXA_BRANCH_NAME__)?window.__MAXA_BRANCH_NAME__:'';
  var track=document.getElementById('bp-track');
  var esc=function(s){return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});};

  // نگاشتِ کلید→نامِ شعبه و نقش (همان نگاشتِ صفحه‌ی شبکه‌ی همکاران)
  var BRANCHES={tehran:'شعبه تهران',isfahan:'شعبه اصفهان',mashhad:'شعبه مشهد',qom:'شعبه قم',tabriz:'شعبه تبریز',ahvaz:'شعبه اهواز',kerman:'شعبه کرمان',kashan:'شعبه کاشان',telemedicine:'پزشکی از راه دور مکسا',setad_markazi:'ستاد مرکزی'};
  var ROLES={administrative:'سمت‌های اداری',doctors:'پزشک متخصص',nurses:'کادر پرستاری',psychologists:'روانشناس سلامت',social_workers:'مددکار اجتماعی',spiritual_care:'مراقب معنوی',nutritionists:'متخصص تغذیه',rehabilitation:'متخصص توانبخشی',genetic_counselors:'مشاور ژنتیک و غربالگری',deputies:'معاونت'};

  // دکمه‌ی «همه‌ی همکاران» → صفحه‌ی شبکه‌ی همکارانِ همین شعبه
  var allBtn=document.getElementById('bp-all');
  if(allBtn){ allBtn.setAttribute('href', SLUG?('/'+encodeURIComponent(SLUG)+'/network'):'#'); }
  if(NAME){ var sub=document.getElementById('bp-sub'); if(sub) sub.textContent='تیمی که در شعبه‌ی '+NAME+' در کنار شما هستند.'; }

  function inBranch(emp){
    if(!SLUG && !NAME) return true;
    var b=(emp.branch||'').trim();
    if(NAME && b===NAME) return true;
    if(SLUG && b===SLUG) return true;
    if(NAME && BRANCHES[b]===NAME) return true;
    return false;
  }

  function card(emp){
    var name=emp.fullname||'همکار مکسا';
    var branchText=BRANCHES[emp.branch]|| (emp.branch||NAME||'مکسا');
    var rawRole=emp.role||'', rawCat=emp.job_category||'';
    var roleText=ROLES[rawRole]||(rawRole?rawRole:(ROLES[rawCat]||'ثبت نشده'));
    var slug=encodeURIComponent(String(name).trim().replace(/\s+/g,'-'));
    var img=emp.profile_pic
      ? '<img src="'+esc(emp.profile_pic)+'" alt="'+esc(name)+'">'
      : '<div class="bp-ph">👤</div>';
    return '<article class="bp-card"><div class="bp-img">'+img+'</div>'
      +'<div class="bp-info"><h3 class="bp-name" title="'+esc(name)+'">'+esc(name)+'</h3>'
      +'<div class="bp-pills">'
      +'<div class="bp-pill bp-pill-branch" title="'+esc(branchText)+'"><span class="dot"></span><span class="vt">'+esc(branchText)+'</span></div>'
      +'<div class="bp-pill bp-pill-role" title="'+esc(roleText)+'"><span class="dot"></span><span class="vt">'+esc(roleText)+'</span></div>'
      +'</div>'
      +'<div class="bp-more"><a class="bp-detail" href="/dashboard/personal-resume-detail.php?name='+slug+'" target="_top">اطلاعات بیشتر ‹</a></div>'
      +'</div></article>';
  }

  fetch('/api-employees.php').then(function(r){return r.json();}).then(function(list){
    var items=(Array.isArray(list)?list:[]).filter(inBranch).slice(0,12);
    if(!items.length){
      track.innerHTML='<div class="bp-soon" style="flex:1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg><b>به‌زودی</b><span>هنوز همکاری برای این شعبه ثبت نشده است.</span></div>';
      if(allBtn) allBtn.style.display='none';
      return;
    }
    track.innerHTML=items.map(card).join('');

    // اسلایدر: فلش‌ها اگر محتوا بیش از عرض باشد
    var prev=document.getElementById('bp-prev'), next=document.getElementById('bp-next');
    function step(){ var c=track.querySelector('.bp-card'); return c?(c.offsetWidth+22):280; }
    function sync(){ var sc=track.scrollWidth>track.clientWidth+8; prev.hidden=!sc; next.hidden=!sc; }
    next.addEventListener('click',function(){ track.scrollBy({left:-step()*2,behavior:'smooth'}); });
    prev.addEventListener('click',function(){ track.scrollBy({left:step()*2,behavior:'smooth'}); });
    sync(); window.addEventListener('resize',sync);
  }).catch(function(){
    track.innerHTML='<div class="bp-soon" style="flex:1">خطا در بارگذاری همکاران.</div>';
  });
})();
</script>

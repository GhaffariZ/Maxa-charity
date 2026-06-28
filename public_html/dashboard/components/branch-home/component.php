<!-- ============================================================================
     کامپوننت «صفحه‌ی اصلیِ شعبه» — طراحیِ اختصاصی و منطبق با برندِ مکسا
     ----------------------------------------------------------------------------
     این کامپوننت بدنه‌ی صفحه‌ی هر شعبه است (بین هدر و فوترِ مشترکِ برند). هر بخش
     فقط محتوای «همان شعبه» را نشان می‌دهد و داده را از فیدهای عمومیِ scope‌شده با
     window.__MAXA_BRANCH__ می‌گیرد (هیرو/کمپین/خبر/دوره). در نبودِ داده، حالتِ
     مودبانه‌ی «به‌زودی» نمایش داده می‌شود.

     زبان بصری (فونت وزیرمتن، فیروزه‌ای #007b7a + نارنجی #f5a623، گرادیانِ فوتر،
     کارت‌های تصویردار با سایه‌ی نرم) عیناً از کامپوننت‌های زنده‌ی سایت گرفته شده تا
     کاملاً هم‌خانواده‌ی صفحه‌ی اصلی باشد؛ اما چیدمان مدرن و مختص یک شعبه است.
============================================================================ -->
<style>
  @font-face{
    font-family:'Vazirmatn';
    src:url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
        url('/webfont/Vazirmatn[wght].woff2') format('woff2');
    font-weight:100 900; font-style:normal; font-display:swap;
  }
  .bh{
    --teal:#007b7a; --teal-d:#006665; --teal-l:#10aeb8;
    --orange:#f5a623; --orange-2:#f39a20;
    --text:#2f3437; --muted:#7e858a; --line:#e6e8ea; --bg:#f8f9fa; --surface:#fff;
    --radius:18px; --radius-sm:12px;
    --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 6px rgba(16,40,40,.05);
    --shadow-md:0 10px 24px rgba(16,40,40,.07),0 4px 10px rgba(16,40,40,.05);
    --ease:cubic-bezier(.2,.8,.2,1);
    font-family:'Vazirmatn',Tahoma,sans-serif;
    direction:rtl; color:var(--text); background:var(--surface);
  }
  .bh *{box-sizing:border-box}
  .bh-wrap{max-width:1200px;margin:0 auto;padding:0 20px}
  .bh a{text-decoration:none;color:inherit}

  /* ===== HERO ===== */
  .bh-hero{position:relative;min-height:560px;display:flex;align-items:center;overflow:hidden;background:#0b3b3a}
  .bh-hero-slides{position:absolute;inset:0;z-index:1}
  .bh-hero-track{display:flex;height:100%;width:100%;transition:transform .65s var(--ease);will-change:transform}
  .bh-slide{position:relative;min-width:100%;height:100%;background-size:cover;background-position:center;filter:saturate(1.02) contrast(1.03)}
  .bh-slide::after{content:"";position:absolute;inset:0;background:
      linear-gradient(to left,rgba(0,40,40,.62),rgba(0,40,40,.30) 55%,rgba(0,40,40,.14)),
      radial-gradient(circle at 14% 28%,rgba(0,0,0,.10),transparent 55%);z-index:0}
  .bh-hero-inner{position:relative;z-index:5;width:100%;padding:96px 0 84px}
  .bh-hero-text{width:min(580px,100%);color:#fff;text-align:right}
  .bh-kicker{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:700;
      color:#fff;background:rgba(245,166,35,.92);padding:6px 14px;border-radius:999px;margin-bottom:16px}
  .bh-hero-title{font-size:42px;line-height:1.18;margin:0 0 14px;font-weight:800;text-shadow:0 10px 18px rgba(0,0,0,.22)}
  .bh-hero-desc{margin:0 0 22px;color:rgba(255,255,255,.86);font-size:15px;line-height:1.95;max-width:56ch}
  .bh-hero-btn{display:inline-flex;align-items:center;gap:10px;border:none;cursor:pointer;
      background:var(--orange);color:#1a1a1a;font-weight:800;font-size:15px;padding:13px 24px;border-radius:12px;
      box-shadow:0 12px 24px rgba(245,166,35,.26);transition:.2s var(--ease)}
  .bh-hero-btn:hover{transform:translateY(-2px);filter:brightness(1.03)}
  .bh-arrow{position:absolute;top:50%;transform:translateY(-50%);z-index:7;width:44px;height:44px;border-radius:999px;
      border:1px solid rgba(255,255,255,.24);background:rgba(0,0,0,.26);backdrop-filter:blur(6px);color:#fff;
      display:grid;place-items:center;cursor:pointer;transition:.2s var(--ease)}
  .bh-arrow:hover{background:rgba(0,0,0,.42)}
  .bh-arrow.prev{right:16px}.bh-arrow.next{left:16px}
  .bh-arrow svg{width:18px;height:18px;opacity:.92}
  .bh-dots{position:absolute;bottom:18px;left:50%;transform:translateX(-50%);z-index:7;display:flex;gap:8px}
  .bh-dot{width:9px;height:9px;border-radius:999px;border:1px solid rgba(255,255,255,.6);background:rgba(255,255,255,.15);cursor:pointer;transition:.2s var(--ease)}
  .bh-dot.active{width:22px;background:var(--orange);border-color:transparent}
  .bh-hero-soon{position:relative;z-index:5;width:100%;text-align:center;color:#fff;padding:120px 0}
  .bh-hero-soon h2{font-size:30px;font-weight:800;margin:0 0 10px}
  .bh-hero-soon p{margin:0;color:rgba(255,255,255,.8);font-size:15px}

  /* ===== STAT STRIP ===== */
  .bh-stats{background:linear-gradient(180deg,#fff, var(--bg));border-bottom:1px solid var(--line)}
  .bh-stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;padding:26px 0}
  .bh-stat{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius-sm);
      padding:18px 16px;text-align:center;box-shadow:var(--shadow-sm)}
  .bh-stat b{display:block;font-size:28px;font-weight:900;color:var(--teal);line-height:1}
  .bh-stat span{display:block;margin-top:8px;font-size:13px;color:var(--muted);font-weight:600}

  /* ===== SECTIONS ===== */
  .bh-sec{padding:64px 0}
  .bh-sec.alt{background:var(--bg)}
  .bh-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:34px}
  .bh-head h2{margin:0;font-size:28px;font-weight:800;position:relative;padding-bottom:12px}
  .bh-head h2::after{content:"";position:absolute;right:0;bottom:0;width:48px;height:3px;border-radius:999px;background:var(--teal)}
  .bh-head .bh-more{font-size:13px;font-weight:700;color:var(--teal);white-space:nowrap;display:inline-flex;align-items:center;gap:6px;transition:.2s}
  .bh-head .bh-more:hover{gap:10px}
  .bh-grid{display:grid;grid-template-columns:1fr;gap:24px}
  @media(min-width:680px){.bh-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media(min-width:1000px){.bh-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
  .bh-grid.cols-2{grid-template-columns:1fr}
  @media(min-width:760px){.bh-grid.cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}}

  /* ===== CARD (shared grammar) ===== */
  .bh-card{display:flex;flex-direction:column;background:var(--surface);border:1px solid var(--line);
      border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);transition:transform .28s var(--ease),box-shadow .28s var(--ease),border-color .28s}
  .bh-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-md);border-color:rgba(0,123,122,.28)}
  .bh-thumb{position:relative;aspect-ratio:16/9;background:#eef1f2}
  .bh-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .bh-body{padding:18px 18px 20px;display:flex;flex-direction:column;gap:10px;flex:1}
  .bh-tag{display:inline-flex;align-items:center;gap:5px;align-self:flex-start;font-size:11px;font-weight:700;
      color:var(--teal);background:rgba(0,123,122,.10);padding:3px 10px;border-radius:999px}
  .bh-card h3{margin:0;font-size:17px;line-height:1.6;font-weight:800;color:#1c2022}
  .bh-card p{margin:0;color:var(--muted);font-size:13.5px;line-height:1.9}
  .bh-meta{display:flex;align-items:center;gap:10px;color:#a3a9ad;font-size:12px;flex-wrap:wrap}
  .bh-cat{background:rgba(0,123,122,.08);color:var(--teal);border-radius:6px;padding:2px 8px;font-weight:700}
  .bh-foot{margin-top:auto;padding-top:12px;border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-between}
  .bh-link{font-size:12.5px;font-weight:800;color:var(--teal);display:inline-flex;align-items:center;gap:6px}

  /* campaign progress */
  .bh-prog{margin-top:4px}
  .bh-prog-info{display:flex;justify-content:space-between;font-size:11.5px;color:var(--muted);margin-bottom:6px}
  .bh-prog-bar{height:8px;border-radius:999px;background:#eef1f2;overflow:hidden}
  .bh-prog-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--teal-l),var(--teal))}
  .bh-prog-badge{display:inline-block;font-size:12px;font-weight:800;color:var(--teal)}

  /* course price */
  .bh-price b{font-size:15px;font-weight:900;color:var(--text)}
  .bh-price .free{color:#16a37a}

  /* empty / coming soon */
  .bh-soon{grid-column:1/-1;text-align:center;padding:54px 20px;border:1.5px dashed var(--line);
      border-radius:var(--radius);background:rgba(0,123,122,.02);color:var(--muted)}
  .bh-soon svg{width:40px;height:40px;color:var(--teal);opacity:.7;margin-bottom:12px}
  .bh-soon b{display:block;font-size:17px;font-weight:800;color:var(--text);margin-bottom:6px}
  .bh-soon span{font-size:13.5px}

  /* ===== BRANCH CTA BAND (teal, not HQ's orange) ===== */
  .bh-cta{position:relative;overflow:hidden;color:#fff;padding:56px 0;
      background:
        radial-gradient(circle at 12% 12%,rgba(255,255,255,.18),transparent 36%),
        radial-gradient(circle at 88% 86%,rgba(255,255,255,.12),transparent 40%),
        linear-gradient(155deg,var(--teal-l) 0%,var(--teal-d) 58%,#05646e 100%)}
  .bh-cta-inner{display:flex;align-items:center;justify-content:space-between;gap:28px;flex-wrap:wrap}
  .bh-cta h3{margin:0 0 8px;font-size:24px;font-weight:900}
  .bh-cta p{margin:0;color:rgba(255,255,255,.88);font-size:14px;line-height:1.85;max-width:60ch}
  .bh-cta-btn{display:inline-flex;align-items:center;gap:10px;background:var(--orange);color:#1a1a1a;
      font-weight:800;font-size:15px;padding:14px 28px;border-radius:12px;white-space:nowrap;
      box-shadow:0 12px 26px rgba(0,0,0,.18);transition:.2s var(--ease)}
  .bh-cta-btn:hover{transform:translateY(-2px);filter:brightness(1.04)}

  @media(max-width:760px){
    .bh-hero-title{font-size:30px}
    .bh-stats-grid{grid-template-columns:repeat(2,1fr)}
    .bh-sec{padding:48px 0}
    .bh-head h2{font-size:23px}
    .bh-cta-inner{flex-direction:column;text-align:center;align-items:center}
  }
</style>

<div class="bh" id="bh-root" data-branch-home>

  <!-- HERO -->
  <section class="bh-hero" id="bh-hero">
    <div class="bh-hero-slides"><div class="bh-hero-track" id="bh-hero-track"></div></div>
    <button class="bh-arrow prev" id="bh-prev" aria-label="قبلی" hidden><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></button>
    <button class="bh-arrow next" id="bh-next" aria-label="بعدی" hidden><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></button>
    <div class="bh-dots" id="bh-dots"></div>
  </section>

  <!-- STAT STRIP -->
  <section class="bh-stats">
    <div class="bh-wrap">
      <div class="bh-stats-grid" id="bh-stats">
        <div class="bh-stat"><b id="bh-st-campaigns">۰</b><span>کمپین فعال</span></div>
        <div class="bh-stat"><b id="bh-st-news">۰</b><span>خبر منتشرشده</span></div>
        <div class="bh-stat"><b id="bh-st-courses">۰</b><span>دوره‌ی آموزشی</span></div>
        <div class="bh-stat"><b id="bh-st-hero">۰</b><span>پویش ویژه</span></div>
      </div>
    </div>
  </section>

  <!-- CAMPAIGNS -->
  <section class="bh-sec" id="bh-campaigns">
    <div class="bh-wrap">
      <div class="bh-head"><h2>کمپین‌های شعبه</h2></div>
      <div class="bh-grid" id="bh-campaigns-grid"></div>
    </div>
  </section>

  <!-- NEWS -->
  <section class="bh-sec alt" id="bh-news">
    <div class="bh-wrap">
      <div class="bh-head"><h2>آخرین اخبار شعبه</h2></div>
      <div class="bh-grid" id="bh-news-grid"></div>
    </div>
  </section>

  <!-- COURSES -->
  <section class="bh-sec" id="bh-courses">
    <div class="bh-wrap">
      <div class="bh-head"><h2>دوره‌های شعبه</h2></div>
      <div class="bh-grid cols-2" id="bh-courses-grid"></div>
    </div>
  </section>

  <!-- BRANCH CTA -->
  <section class="bh-cta">
    <div class="bh-wrap bh-cta-inner">
      <div>
        <h3 id="bh-cta-title">در کنار ما باشید</h3>
        <p>با حمایت از فعالیت‌های این شعبه، در رساندنِ خدمات به نیازمندان سهیم شوید. هر کمک، هرچند کوچک، اثرگذار است.</p>
      </div>
      <a class="bh-cta-btn" id="bh-cta-btn" href="#" target="_top">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/></svg>
        حمایت می‌کنم
      </a>
    </div>
  </section>

</div>

<script>
(function(){
  var root=document.getElementById('bh-root'); if(!root) return;
  var SLUG=(typeof window.__MAXA_BRANCH__==='string')?window.__MAXA_BRANCH__:'';
  var NAME=(typeof window.__MAXA_BRANCH_NAME__==='string'&&window.__MAXA_BRANCH_NAME__)?window.__MAXA_BRANCH_NAME__:'';
  var Q=SLUG?('?branch='+encodeURIComponent(SLUG)):'';
  var QA=SLUG?('&branch='+encodeURIComponent(SLUG)):'';
  var fa=function(v){return String(v).replace(/[0-9]/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[d];});};
  var esc=function(s){return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});};
  var cut=function(t,m){t=(t||'').trim();return t.length>m?t.slice(0,m)+'…':t;};
  var ph='data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="1000" height="560"><rect width="100%" height="100%" fill="#e9edee"/><text x="50%" y="52%" dominant-baseline="middle" text-anchor="middle" fill="#9aa4a6" font-size="40" font-family="Vazirmatn,Tahoma">مکسا</text></svg>');
  var money=function(n){return Number(n||0).toLocaleString('fa-IR');};

  function soon(msg){
    return '<div class="bh-soon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>'
      +'<b>به‌زودی</b><span>'+esc(msg)+'</span></div>';
  }
  function jget(url){return fetch(url).then(function(r){return r.json();}).catch(function(){return null;});}

  /* ---- HERO ---- */
  (function(){
    var track=document.getElementById('bh-hero-track');
    var dots=document.getElementById('bh-dots');
    var prev=document.getElementById('bh-prev'), next=document.getElementById('bh-next');
    var kickerName=NAME?('شعبه‌ی '+NAME):'شعبه‌ی مکسا';
    jget('/dashboard/hero-list.php'+Q).then(function(d){
      var items=(d&&d.data)||[];
      document.getElementById('bh-st-hero').textContent=fa(items.length);
      if(!items.length){
        document.getElementById('bh-hero').innerHTML='<div class="bh-wrap bh-hero-soon"><h2>'+esc(kickerName)+'</h2><p>محتوای ویژه‌ی این شعبه به‌زودی منتشر می‌شود.</p></div>';
        return;
      }
      track.innerHTML=items.map(function(s){
        var img=s.image?String(s.image):ph;
        var link=s.button_link||'#';
        return '<div class="bh-slide" style="background-image:url(\''+esc(img)+'\')">'
          +'<div class="bh-wrap bh-hero-inner"><div class="bh-hero-text">'
          +'<span class="bh-kicker">'+esc(kickerName)+'</span>'
          +'<h1 class="bh-hero-title">'+esc(s.title||'')+'</h1>'
          +(s.description?('<p class="bh-hero-desc">'+esc(cut(s.description,180))+'</p>'):'')
          +'<a class="bh-hero-btn" href="'+esc(link)+'" target="_top">بیشتر بدانید'
          +'<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></a>'
          +'</div></div></div>';
      }).join('');
      var n=items.length, i=0;
      if(n>1){
        prev.hidden=false; next.hidden=false;
        dots.innerHTML=items.map(function(_,k){return '<span class="bh-dot'+(k===0?' active':'')+'" data-i="'+k+'"></span>';}).join('');
        var go=function(k){i=(k+n)%n;track.style.transform='translateX('+(i*100)+'%)';
          Array.prototype.forEach.call(dots.children,function(el,idx){el.classList.toggle('active',idx===i);});};
        prev.addEventListener('click',function(){go(i-1);});
        next.addEventListener('click',function(){go(i+1);});
        Array.prototype.forEach.call(dots.children,function(el){el.addEventListener('click',function(){go(+el.getAttribute('data-i'));});});
        var timer=setInterval(function(){go(i+1);},6000);
        document.getElementById('bh-hero').addEventListener('mouseenter',function(){clearInterval(timer);});
      }
    });
  })();

  /* ---- CAMPAIGNS ---- */
  jget('/dashboard/campaign-list.php'+Q).then(function(d){
    var items=(d&&d.data)||[]; var grid=document.getElementById('bh-campaigns-grid');
    document.getElementById('bh-st-campaigns').textContent=fa(items.length);
    if(!items.length){grid.innerHTML=soon('هنوز کمپینی برای این شعبه تعریف نشده است.');return;}
    grid.innerHTML=items.slice(0,3).map(function(c){
      var tgt=parseFloat(c.target_amount)||0, col=parseFloat(c.collected_amount)||0;
      var pct=tgt>0?Math.min(100,Math.round(col/tgt*100)):0;
      var img=c.image_url||ph;
      return '<article class="bh-card"><div class="bh-thumb"><img src="'+esc(img)+'" alt="" onerror="this.src=\''+ph+'\'"></div>'
        +'<div class="bh-body">'
        +(c.branch_name?('<span class="bh-tag">🏢 '+esc(c.branch_name)+'</span>'):'')
        +'<h3>'+esc(c.title||'')+'</h3>'
        +(c.description?('<p>'+esc(cut(String(c.description).replace(/<[^>]*>/g,''),90))+'</p>'):'')
        +'<div class="bh-prog"><div class="bh-prog-info"><span class="bh-prog-badge">'+fa(pct)+'٪</span>'
        +'<span>'+money(col)+' از '+money(tgt)+' تومان</span></div>'
        +'<div class="bh-prog-bar"><div class="bh-prog-fill" style="width:'+pct+'%"></div></div></div>'
        +'</div></article>';
    }).join('');
  });

  /* ---- NEWS ---- */
  jget('/dashboard/recent-news-feed.php?limit=3'+QA).then(function(d){
    var items=(d&&d.items)||[]; var grid=document.getElementById('bh-news-grid');
    document.getElementById('bh-st-news').textContent=fa(items.length);
    if(!items.length){grid.innerHTML=soon('هنوز خبری برای این شعبه منتشر نشده است.');return;}
    grid.innerHTML=items.map(function(n){
      var img=n.image||ph;
      return '<a class="bh-card" href="'+esc(n.url||'#')+'" target="_top"><div class="bh-thumb"><img src="'+esc(img)+'" alt="" onerror="this.src=\''+ph+'\'"></div>'
        +'<div class="bh-body"><div class="bh-meta"><span class="bh-cat">'+esc(n.category||'اخبار')+'</span><span>'+fa(n.date||'')+'</span></div>'
        +'<h3>'+esc(n.title||'')+'</h3>'
        +(n.excerpt?('<p>'+esc(cut(n.excerpt,100))+'</p>'):'')
        +'<div class="bh-foot"><span class="bh-meta">'+fa(n.read_time||1)+' دقیقه مطالعه</span>'
        +'<span class="bh-link">ادامه ‹</span></div></div></a>';
    }).join('');
  });

  /* ---- COURSES ---- */
  jget('/dashboard/branch-courses-feed.php?limit=4'+QA).then(function(d){
    var items=(d&&d.items)||[]; var grid=document.getElementById('bh-courses-grid');
    document.getElementById('bh-st-courses').textContent=fa(items.length);
    if(!items.length){grid.innerHTML=soon('هنوز دوره‌ای برای این شعبه منتشر نشده است.');return;}
    grid.innerHTML=items.map(function(c){
      var img=c.image||ph;
      var price=c.is_free?'<b class="free">رایگان</b>':(c.discount>0?('<b>'+money(c.discount)+' ت</b>'):('<b>'+money(c.price)+' ت</b>'));
      return '<a class="bh-card" href="'+esc(c.url||'#')+'" target="_top"><div class="bh-thumb"><img src="'+esc(img)+'" alt="" onerror="this.src=\''+ph+'\'"></div>'
        +'<div class="bh-body"><div class="bh-meta"><span class="bh-cat">'+esc(c.category||'عمومی')+'</span>'
        +(c.lessons?('<span>'+fa(c.lessons)+' درس</span>'):'')+'</div>'
        +'<h3>'+esc(c.title||'')+'</h3>'
        +'<p>'+esc(c.instructor||'مدرس مکسا')+'</p>'
        +'<div class="bh-foot"><span class="bh-price">'+price+'</span><span class="bh-link">مشاهده ‹</span></div>'
        +'</div></a>';
    }).join('');
  });

  /* ---- CTA copy + donation link ---- */
  (function(){
    if(NAME){document.getElementById('bh-cta-title').textContent='به شعبه‌ی '+NAME+' کمک کنید';}
    // مسیر مشارکت: صفحه‌ی کمپین‌های همان شعبه (در نبودِ مسیر اختصاصیِ پرداخت)
    document.getElementById('bh-cta-btn').setAttribute('href', SLUG?('/'+encodeURIComponent(SLUG)+'#bh-campaigns'):'#bh-campaigns');
  })();
})();
</script>

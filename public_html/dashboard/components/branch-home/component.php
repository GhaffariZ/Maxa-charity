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

  /* ===== NAVBAR FLOATS OVER HERO =====
     هدرِ مشترک با position:fixed یک «spacer» سفید بعد از خودش می‌گذارد؛ آن را حذف
     می‌کنیم تا نوار روی هیرو شناور شود و زیرِ آن سفیدیِ خالی نیفتد. */
  .cta-navbar-spacer{display:none !important}

  /* ===== HERO ===== */
  .bh-hero{position:relative;min-height:620px;display:flex;align-items:center;overflow:hidden;background:#0b3b3a}
  .bh-hero-slides{position:absolute;inset:0;z-index:1}
  .bh-hero-track{display:flex;height:100%;width:100%;transition:transform .65s var(--ease);will-change:transform}
  .bh-slide{position:relative;min-width:100%;height:100%;background-size:cover;background-position:center;filter:saturate(1.02) contrast(1.03)}
  .bh-slide::after{content:"";position:absolute;inset:0;background:
      linear-gradient(to left,rgba(0,40,40,.62),rgba(0,40,40,.30) 55%,rgba(0,40,40,.14)),
      radial-gradient(circle at 14% 28%,rgba(0,0,0,.10),transparent 55%);z-index:0}
  .bh-hero-inner{position:relative;z-index:5;width:100%;padding:140px 0 90px}
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
  .bh-hero-soon{position:relative;z-index:5;width:100%;text-align:center;color:#fff;padding:160px 0 120px}
  .bh-hero-soon h2{font-size:30px;font-weight:800;margin:0 0 10px}
  .bh-hero-soon p{margin:0;color:rgba(255,255,255,.8);font-size:15px}

  /* ===== BRANCH IDENTITY BAR (portfolio-style) =====
     نواری که می‌گوید این صفحه مربوط به کدام شعبه است، با طراحیِ شاخص. */
  .bh-id{position:relative;overflow:hidden;color:#fff;
      background:
        radial-gradient(circle at 86% -10%,rgba(245,166,35,.28),transparent 42%),
        radial-gradient(circle at 8% 120%,rgba(16,174,184,.30),transparent 46%),
        linear-gradient(135deg,#063a3c 0%,#0a5c5b 55%,#063a3c 100%)}
  .bh-id::before{content:"";position:absolute;inset:0;opacity:.06;pointer-events:none;
      background-image:radial-gradient(rgba(255,255,255,.9) 1px,transparent 1px);background-size:22px 22px}
  .bh-id-inner{position:relative;z-index:2;display:flex;align-items:center;gap:28px;padding:46px 0;flex-wrap:wrap}
  .bh-id-badge{flex:0 0 auto;width:92px;height:92px;border-radius:24px;display:grid;place-items:center;
      background:linear-gradient(145deg,rgba(255,255,255,.16),rgba(255,255,255,.05));
      border:1px solid rgba(255,255,255,.22);backdrop-filter:blur(6px);
      font-size:38px;font-weight:900;color:#fff;box-shadow:0 14px 30px rgba(0,0,0,.22)}
  .bh-id-main{flex:1 1 280px;min-width:240px}
  .bh-id-kicker{display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:700;letter-spacing:.04em;
      color:#0a3a3a;background:var(--orange);padding:5px 13px;border-radius:999px;margin-bottom:12px}
  .bh-id-name{margin:0;font-size:34px;font-weight:900;line-height:1.2;letter-spacing:-.5px}
  .bh-id-sub{margin:8px 0 0;color:rgba(255,255,255,.78);font-size:14px;line-height:1.85;max-width:62ch}
  .bh-id-line{display:flex;align-items:center;gap:10px;margin-top:16px;color:rgba(255,255,255,.9);font-size:13px;font-weight:600}
  .bh-id-line svg{width:17px;height:17px;color:var(--orange)}
  .bh-id-rule{height:1px;flex:1;background:linear-gradient(90deg,rgba(255,255,255,.32),transparent)}
  @media(max-width:760px){.bh-id-inner{gap:18px;padding:34px 0}.bh-id-badge{width:70px;height:70px;font-size:28px;border-radius:18px}.bh-id-name{font-size:26px}}

  /* ===== CONTENT BACKDROP (behind news + campaigns + courses) =====
     یک گرادیانِ سبکِ برندی با پالتِ مکسا (فیروزه‌ای → نعنایی → کرمِ گرم) به‌جای
     سفیدیِ خامِ بی‌هویتِ قبلی. */
  .bh-content{position:relative;overflow:hidden;
      background:
        radial-gradient(60% 50% at 88% -4%,rgba(16,174,184,.22),transparent 70%),
        radial-gradient(52% 44% at 6% 14%,rgba(245,166,35,.16),transparent 68%),
        radial-gradient(60% 50% at 50% 108%,rgba(0,123,122,.18),transparent 72%),
        linear-gradient(165deg,#eaf5f4 0%,#eef6f1 36%,#fbf4e6 72%,#e9f4f3 100%)}
  /* یک خطِ گرادیانِ نازکِ برندی در بالای ناحیه برای جداسازیِ نرم از نوار هویت */
  .bh-content::after{content:"";position:absolute;top:0;inset-inline:0;height:3px;
      background:linear-gradient(90deg,var(--teal),var(--teal-l) 45%,var(--orange));opacity:.85}
  .bh-content::before{content:"";position:absolute;inset:0;pointer-events:none;opacity:.6;
      background-image:radial-gradient(rgba(0,123,122,.07) 1px,transparent 1px);background-size:28px 28px}
  .bh-content>*{position:relative;z-index:1}

  /* ===== SECTIONS ===== */
  .bh-sec{padding:60px 0}
  .bh-sec.tight{padding-bottom:24px}
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

  /* ===== NEWS (feature + side list) ===== */
  .bh-news-grid{display:grid;grid-template-columns:1fr;gap:24px}
  @media(min-width:1000px){.bh-news-grid{grid-template-columns:repeat(12,minmax(0,1fr))}.bh-news-main{grid-column:span 7}.bh-news-side{grid-column:span 5}}
  .bh-news-main{position:relative;display:block;overflow:hidden;border-radius:var(--radius);background:#000;aspect-ratio:16/10;
      box-shadow:var(--shadow-md)}
  .bh-news-main img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.66;transition:transform 1s var(--ease)}
  .bh-news-main:hover img{transform:scale(1.05)}
  .bh-news-main::after{content:"";position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.88),rgba(0,0,0,.05) 60%,transparent)}
  .bh-news-main-c{position:absolute;inset-inline:0;bottom:0;padding:28px;color:#fff;z-index:2}
  .bh-news-badge{display:inline-block;background:var(--teal);color:#fff;border-radius:999px;font-size:12px;font-weight:700;padding:4px 12px;margin-bottom:12px}
  .bh-news-main-c h3{margin:0 0 12px;font-size:26px;line-height:1.4;font-weight:800}
  .bh-news-main-c .bh-meta{color:#dfe5e5}
  .bh-news-side{display:flex;flex-direction:column;gap:16px}
  .bh-news-card{display:flex;gap:14px;background:var(--surface);padding:14px;border-radius:var(--radius);border:1px solid var(--line);
      box-shadow:var(--shadow-sm);transition:box-shadow .2s var(--ease),transform .2s var(--ease)}
  .bh-news-card:hover{box-shadow:var(--shadow-md);transform:translateY(-2px)}
  .bh-news-thumb{width:92px;height:92px;flex-shrink:0;border-radius:14px;overflow:hidden}
  .bh-news-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .35s var(--ease)}
  .bh-news-card:hover .bh-news-thumb img{transform:scale(1.08)}
  .bh-news-cbody{display:flex;flex-direction:column;justify-content:center;gap:5px}
  .bh-news-cbody .bh-cat{align-self:flex-start}
  .bh-news-cbody h4{margin:0;font-size:15.5px;line-height:1.55;font-weight:700;color:#1c2022}
  .bh-news-cbody time{font-size:11px;color:#a3a9ad}

  /* campaign progress */
  .bh-prog{margin-top:4px}
  .bh-prog-info{display:flex;justify-content:space-between;font-size:11.5px;color:var(--muted);margin-bottom:6px}
  .bh-prog-bar{height:8px;border-radius:999px;background:#eef1f2;overflow:hidden}
  .bh-prog-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--teal-l),var(--teal))}
  .bh-prog-badge{display:inline-block;font-size:12px;font-weight:800;color:var(--teal)}

  /* course price */
  .bh-price b{font-size:15px;font-weight:900;color:var(--text)}
  .bh-price .free{color:#16a37a}

  /* ===== COURSES — minimal compact cards ===== */
  .bh-courses-grid{display:grid;grid-template-columns:1fr;gap:16px}
  @media(min-width:560px){.bh-courses-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media(min-width:920px){.bh-courses-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
  @media(min-width:1200px){.bh-courses-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
  .bh-ccard{display:flex;flex-direction:column;background:var(--surface);border:1px solid var(--line);
      border-radius:14px;overflow:hidden;box-shadow:var(--shadow-sm);
      transition:transform .22s var(--ease),box-shadow .22s var(--ease),border-color .22s}
  .bh-ccard:hover{transform:translateY(-3px);box-shadow:var(--shadow-md);border-color:rgba(0,123,122,.28)}
  .bh-ccard-thumb{position:relative;aspect-ratio:5/3;background:#eef1f2}
  .bh-ccard-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .bh-ccard-cat{position:absolute;top:8px;inset-inline-end:8px;font-size:10px;font-weight:700;color:#fff;
      background:rgba(0,123,122,.92);padding:2px 8px;border-radius:999px;backdrop-filter:blur(2px)}
  .bh-ccard-body{padding:11px 13px 13px;display:flex;flex-direction:column;gap:5px;flex:1}
  .bh-ccard-body h3{margin:0;font-size:14px;line-height:1.55;font-weight:800;color:#1c2022;
      display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .bh-ccard-inst{font-size:11.5px;color:var(--muted)}
  .bh-ccard-foot{margin-top:auto;padding-top:9px;display:flex;align-items:center;justify-content:space-between;
      border-top:1px solid var(--line)}
  .bh-ccard-meta{font-size:11px;color:#a3a9ad}
  .bh-ccard-price b{font-size:13px;font-weight:900;color:var(--text)}
  .bh-ccard-price .free{color:#16a37a}

  /* empty / coming soon */
  .bh-soon{grid-column:1/-1;text-align:center;padding:54px 20px;border:1.5px dashed var(--line);
      border-radius:var(--radius);background:rgba(0,123,122,.02);color:var(--muted)}
  .bh-soon svg{width:40px;height:40px;color:var(--teal);opacity:.7;margin-bottom:12px}
  .bh-soon b{display:block;font-size:17px;font-weight:800;color:var(--text);margin-bottom:6px}
  .bh-soon span{font-size:13.5px}

  @media(max-width:760px){
    .bh-hero-title{font-size:30px}
    .bh-sec{padding:48px 0}
    .bh-head h2{font-size:23px}
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

  <!-- BRANCH IDENTITY BAR (portfolio-style) -->
  <section class="bh-id">
    <div class="bh-wrap bh-id-inner">
      <div class="bh-id-badge" id="bh-id-badge">م</div>
      <div class="bh-id-main">
        <span class="bh-id-kicker">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/></svg>
          شعبه‌ای از خیریه مکسا
        </span>
        <h2 class="bh-id-name" id="bh-id-name">شعبه‌ی مکسا</h2>
        <p class="bh-id-sub">به صفحه‌ی این شعبه از مؤسسه‌ی نیکوکاری مکسا خوش آمدید؛ این‌جا کمپین‌ها، اخبار و دوره‌های همین شعبه را دنبال می‌کنید.</p>
        <div class="bh-id-line">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="10" r="3"/><path d="M12 21s-7-5.2-7-11a7 7 0 0 1 14 0c0 5.8-7 11-7 11Z"/></svg>
          <span id="bh-id-tagline">در خدمت مردم، در کنار شما</span>
          <span class="bh-id-rule"></span>
        </div>
      </div>
    </div>
  </section>

  <!-- CONTENT (news → campaigns → courses) over a soft branded backdrop -->
  <div class="bh-content">

    <!-- NEWS -->
    <section class="bh-sec" id="bh-news">
      <div class="bh-wrap">
        <div class="bh-head"><h2>آخرین اخبار شعبه</h2></div>
        <div class="bh-news-grid" id="bh-news-grid"></div>
      </div>
    </section>

    <!-- CAMPAIGNS -->
    <section class="bh-sec" id="bh-campaigns">
      <div class="bh-wrap">
        <div class="bh-head"><h2>کمپین‌های شعبه</h2></div>
        <div class="bh-grid" id="bh-campaigns-grid"></div>
      </div>
    </section>

    <!-- COURSES -->
    <section class="bh-sec" id="bh-courses">
      <div class="bh-wrap">
        <div class="bh-head"><h2>دوره‌های شعبه</h2></div>
        <div class="bh-courses-grid" id="bh-courses-grid"></div>
      </div>
    </section>

  </div>

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

  /* ---- BRANCH IDENTITY BAR ---- */
  (function(){
    var nm=NAME||'مکسا';
    var nameEl=document.getElementById('bh-id-name');
    if(nameEl) nameEl.textContent='شعبه‌ی '+nm;
    var badge=document.getElementById('bh-id-badge');
    if(badge){ var ch=(nm.trim()[0]||'م'); badge.textContent=ch; }
    var tag=document.getElementById('bh-id-tagline');
    if(tag) tag.textContent='شعبه‌ی '+nm+' — در خدمت مردم، در کنار شما';
  })();

  /* ---- HERO ---- */
  (function(){
    var track=document.getElementById('bh-hero-track');
    var dots=document.getElementById('bh-dots');
    var prev=document.getElementById('bh-prev'), next=document.getElementById('bh-next');
    var kickerName=NAME?('شعبه‌ی '+NAME):'شعبه‌ی مکسا';
    jget('/dashboard/hero-list.php'+Q).then(function(d){
      var items=(d&&d.data)||[];
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

  /* ---- NEWS (feature + side list, branch-scoped) ---- */
  jget('/dashboard/recent-news-feed.php?limit=3'+QA).then(function(d){
    var items=(d&&d.items)||[]; var grid=document.getElementById('bh-news-grid');
    grid.className='bh-grid';   // برای حالتِ خالی، گریدِ معمولی تا «به‌زودی» تمام‌عرض شود
    if(!items.length){grid.innerHTML=soon('هنوز خبری برای این شعبه منتشر نشده است.');return;}
    grid.className='bh-news-grid';
    var first=items[0], rest=items.slice(1);
    var main='<a class="bh-news-main" href="'+esc(first.url||'#')+'" target="_top">'
      +'<img src="'+esc(first.image||ph)+'" alt="" onerror="this.src=\''+ph+'\'">'
      +'<div class="bh-news-main-c"><span class="bh-news-badge">'+esc(first.category||'اخبار')+'</span>'
      +'<h3>'+esc(first.title||'')+'</h3>'
      +'<div class="bh-meta"><span>'+fa(first.date||'')+'</span><span>•</span><span>'+fa(first.read_time||1)+' دقیقه مطالعه</span></div>'
      +'</div></a>';
    var side='<div class="bh-news-side">'+rest.map(function(n){
      return '<a class="bh-news-card" href="'+esc(n.url||'#')+'" target="_top">'
        +'<div class="bh-news-thumb"><img src="'+esc(n.image||ph)+'" alt="" onerror="this.src=\''+ph+'\'"></div>'
        +'<div class="bh-news-cbody"><span class="bh-cat">'+esc(n.category||'اخبار')+'</span>'
        +'<h4>'+esc(cut(n.title||'',70))+'</h4><time>'+fa(n.date||'')+'</time></div></a>';
    }).join('')+'</div>';
    grid.innerHTML=main+side;
  });

  /* ---- CAMPAIGNS ---- */
  jget('/dashboard/campaign-list.php'+Q).then(function(d){
    var items=(d&&d.data)||[]; var grid=document.getElementById('bh-campaigns-grid');
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

  /* ---- COURSES (minimal compact cards) ---- */
  jget('/dashboard/branch-courses-feed.php?limit=8'+QA).then(function(d){
    var items=(d&&d.items)||[]; var grid=document.getElementById('bh-courses-grid');
    if(!items.length){grid.className='bh-grid';grid.innerHTML=soon('هنوز دوره‌ای برای این شعبه منتشر نشده است.');return;}
    grid.innerHTML=items.map(function(c){
      var img=c.image||ph;
      var price=c.is_free?'<b class="free">رایگان</b>':(c.discount>0?('<b>'+money(c.discount)+' ت</b>'):('<b>'+money(c.price)+' ت</b>'));
      return '<a class="bh-ccard" href="'+esc(c.url||'#')+'" target="_top">'
        +'<div class="bh-ccard-thumb"><img src="'+esc(img)+'" alt="" onerror="this.src=\''+ph+'\'">'
        +'<span class="bh-ccard-cat">'+esc(c.category||'عمومی')+'</span></div>'
        +'<div class="bh-ccard-body">'
        +'<h3>'+esc(c.title||'')+'</h3>'
        +'<div class="bh-ccard-inst">'+esc(c.instructor||'مدرس مکسا')+'</div>'
        +'<div class="bh-ccard-foot"><span class="bh-ccard-price">'+price+'</span>'
        +'<span class="bh-ccard-meta">'+(c.lessons?(fa(c.lessons)+' درس'):'مشاهده ‹')+'</span></div>'
        +'</div></a>';
    }).join('');
  });
})();
</script>

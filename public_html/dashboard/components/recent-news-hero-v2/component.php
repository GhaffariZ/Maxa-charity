<style>
  /* Self-hosted Vazirmatn variable font (reliable on the Iran network, no external CDN) */
  @font-face {
    font-family: 'Vazirmatn';
    src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
         url('/webfont/Vazirmatn[wght].woff2') format('woff2');
    font-weight: 100 900;
    font-style: normal;
    font-display: swap;
  }
  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }
</style>
<section class="rnhv2-section" dir="rtl" data-recent-news="hero-v2">
  <div class="rnhv2-container">
    <div class="rnhv2-head">
      <span class="rnhv2-kicker">تازه چه خبر؟</span>
      <h2 class="rnhv2-title">تازه ترین اخبار</h2>
    </div>
    <div class="rnhv2-grid" id="rnhv2-grid">
      <div class="rnhv2-empty">در حال بارگذاری اخبار...</div>
    </div>
  </div>

</section>
<style>
.rnhv2-section{padding:64px 0;  background:transparent !important;font-family:'Vazirmatn',Tahoma,sans-serif}.rnhv2-container{max-width:1280px;margin:0 auto;padding:0 16px}
.rnhv2-head{margin-bottom:48px}.rnhv2-kicker{color:#2563eb;font-weight:700;font-size:14px;letter-spacing:.14em;text-transform:uppercase;display:block;margin-bottom:8px}
.rnhv2-title{font-size:42px;line-height:1.1;font-weight:900;margin:0;color:#0a0a0a}.rnhv2-grid{display:grid;grid-template-columns:1fr;gap:32px}
.rnhv2-main{position:relative;overflow:hidden;border-radius:24px;background:#000;aspect-ratio:16/10;display:block;text-decoration:none}.rnhv2-main-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.6;transition:transform 1s ease}
.rnhv2-main:hover .rnhv2-main-img{transform:scale(1.05)}.rnhv2-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.9),rgba(0,0,0,0),rgba(0,0,0,0))}
.rnhv2-main-content{position:absolute;right:0;left:0;bottom:0;padding:32px;color:#fff}.rnhv2-badge{display:inline-block;background:#2563eb;color:#fff;border-radius:999px;font-size:12px;font-weight:700;padding:4px 12px;margin-bottom:14px}
.rnhv2-main-title{margin:0 0 14px;font-size:36px;line-height:1.3;font-weight:700}.rnhv2-meta{display:flex;align-items:center;gap:12px;color:#d4d4d8;font-size:14px}.rnhv2-dot{width:4px;height:4px;border-radius:999px;background:#737373}
.rnhv2-side{display:flex;flex-direction:column;gap:20px}.rnhv2-side-card{display:flex;gap:16px;background:#fff;padding:16px;border-radius:24px;border:1px solid #f5f5f5;box-shadow:0 1px 2px rgba(0,0,0,.04);text-decoration:none;transition:box-shadow .2s ease}
.rnhv2-side-card:hover{box-shadow:0 10px 22px rgba(0,0,0,.08)}.rnhv2-side-thumb-wrap{width:96px;height:96px;flex-shrink:0;border-radius:16px;overflow:hidden}
.rnhv2-side-thumb{width:100%;height:100%;object-fit:cover;transition:transform .35s ease}.rnhv2-side-card:hover .rnhv2-side-thumb{transform:scale(1.1)}
.rnhv2-side-body{display:flex;flex-direction:column;justify-content:center}.rnhv2-side-cat{font-size:10px;font-weight:700;color:#a3a3a3;margin-bottom:4px}
.rnhv2-side-title{margin:0;font-size:16px;line-height:1.45;font-weight:700;color:#171717;transition:color .2s ease}.rnhv2-side-card:hover .rnhv2-side-title{color:#2563eb}
.rnhv2-side-time{margin-top:8px;font-size:10px;color:#a3a3a3}.rnhv2-more{display:flex;align-items:center;justify-content:center;gap:8px;border:2px dashed #e5e5e5;border-radius:24px;min-height:92px;color:#a3a3a3;text-decoration:none;font-weight:700;transition:all .2s ease}
.rnhv2-more:hover{color:#2563eb;border-color:#2563eb}.rnhv2-more-arrow{font-size:20px;line-height:1;transition:transform .2s ease}.rnhv2-more:hover .rnhv2-more-arrow{transform:translateX(-4px)}
.rnhv2-empty{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:24px;color:#6b7280}
.rnhv2-soon{grid-column:1/-1;text-align:center;padding:54px 20px;border:1.5px dashed #d6e0e0;border-radius:24px;background:rgba(0,123,122,.03);color:#7e858a}
.rnhv2-soon svg{width:40px;height:40px;color:#007b7a;opacity:.7;margin-bottom:12px}
.rnhv2-soon b{display:block;font-size:18px;font-weight:800;color:#2f3437;margin-bottom:6px}
.rnhv2-soon span{font-size:13.5px}
@media (min-width:1024px){.rnhv2-grid{grid-template-columns:repeat(12,minmax(0,1fr))}.rnhv2-main{grid-column:span 7}.rnhv2-side{grid-column:span 5}}
@media (max-width:768px){.rnhv2-section{padding:44px 0}.rnhv2-title{font-size:32px}.rnhv2-main-content{padding:20px}.rnhv2-main-title{font-size:26px}}
</style>
<script>
(function(){
  const host=document.querySelector('[data-recent-news="hero-v2"]'); if(!host) return;
  const grid=host.querySelector('#rnhv2-grid');
  const fa=(v)=>String(v).replace(/\d/g,d=>'۰۱۲۳۴۵۶۷۸۹'[d]);
  const ph='data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800"><rect width="100%" height="100%" fill="#e5e7eb"/><text x="50%" y="52%" dominant-baseline="middle" text-anchor="middle" fill="#6b7280" font-size="56" font-family="Vazirmatn,Tahoma">بدون تصویر</text></svg>');
  const __b=(typeof window.__MAXA_BRANCH__==='string'&&window.__MAXA_BRANCH__)?('&branch='+encodeURIComponent(window.__MAXA_BRANCH__)):'';
  fetch('/dashboard/recent-news-feed.php?limit=3'+__b).then(r=>r.json()).then(d=>{
    const items=(d&&d.items)||[]; if(!items.length){grid.innerHTML='<div class="rnhv2-soon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg><b>به‌زودی</b><span>هنوز خبری برای نمایش منتشر نشده است.</span></div>'; return;}
    const first=items[0], rest=items.slice(1);
    grid.innerHTML='<a class="rnhv2-main" href="'+first.url+'"><img class="rnhv2-main-img" src="'+(first.image||ph)+'" alt=""><div class="rnhv2-overlay"></div><div class="rnhv2-main-content"><span class="rnhv2-badge">'+first.category+'</span><h3 class="rnhv2-main-title">'+first.title+'</h3><div class="rnhv2-meta"><span>'+fa(first.date)+'</span><span class="rnhv2-dot"></span><span>'+fa(first.read_time)+' دقیقه مطالعه</span></div></div></a><div class="rnhv2-side"></div>';
    const side=grid.querySelector('.rnhv2-side');
    rest.forEach(n=>{side.insertAdjacentHTML('beforeend','<a class="rnhv2-side-card" href="'+n.url+'"><div class="rnhv2-side-thumb-wrap"><img class="rnhv2-side-thumb" src="'+(n.image||ph)+'" alt=""></div><div class="rnhv2-side-body"><span class="rnhv2-side-cat">'+n.category+'</span><h4 class="rnhv2-side-title">'+n.title+'</h4><time class="rnhv2-side-time">'+fa(n.date)+'</time></div></a>')});
    side.insertAdjacentHTML('beforeend','<a href="/news.php" class="rnhv2-more">مشاهده همه موارد <span class="rnhv2-more-arrow">‹</span></a>');
  }).catch(()=>{grid.innerHTML='<div class="rnhv2-empty">خطا در بارگذاری اخبار.</div>';});
})();
</script>

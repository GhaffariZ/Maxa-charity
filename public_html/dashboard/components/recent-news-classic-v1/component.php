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
<section class="rncv1-sec" dir="rtl" data-recent-news="classic-v1">
  <div class="rncv1-wrap">
    <div class="rncv1-head">
      <h2>خبرهای منتخب</h2>
      <a href="/news.php">مشاهده آرشیو کامل <span>‹</span></a>
    </div>
    <div class="rncv1-grid" id="rncv1-grid"><div class="rncv1-empty">در حال بارگذاری اخبار...</div></div>
  </div>
</section>
<style>
.rncv1-sec{padding:64px 0;background:#fff;font-family:'Vazirmatn',Tahoma,sans-serif}.rncv1-wrap{max-width:1280px;margin:0 auto;padding:0 16px}
.rncv1-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:44px}.rncv1-head h2{margin:0;font-size:30px;font-weight:800;border-right:4px solid #111;padding-right:14px}
.rncv1-head a{text-decoration:none;color:#737373;font-size:14px;font-weight:600}.rncv1-head a:hover{color:#111}
.rncv1-grid{display:grid;grid-template-columns:1fr;gap:28px}.rncv1-card{text-decoration:none;color:inherit;display:block}.rncv1-img-box{aspect-ratio:4/3;overflow:hidden;border-radius:8px;margin-bottom:16px}
.rncv1-img-box img{width:100%;height:100%;object-fit:cover;filter:grayscale(100%);transition:all .7s ease}.rncv1-card:hover .rncv1-img-box img{filter:grayscale(0);transform:scale(1.1)}
.rncv1-cat{margin:0 0 8px;color:#2563eb;font-weight:700;font-size:12px}.rncv1-card h3{margin:0 0 10px;font-size:24px;line-height:1.45;font-weight:800}
.rncv1-excerpt{margin:0 0 10px;color:#737373;font-size:14px;line-height:1.9}.rncv1-card time{color:#a3a3a3;font-size:12px}.rncv1-empty{color:#6b7280}
@media (min-width:768px){.rncv1-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
</style>
<script>
(function(){
  const host=document.querySelector('[data-recent-news="classic-v1"]'); if(!host) return;
  const grid=host.querySelector('#rncv1-grid');
  const fa=(v)=>String(v).replace(/\d/g,d=>'۰۱۲۳۴۵۶۷۸۹'[d]);
  const ph='data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="1000" height="750"><rect width="100%" height="100%" fill="#e5e7eb"/><text x="50%" y="52%" dominant-baseline="middle" text-anchor="middle" fill="#6b7280" font-size="48" font-family="Vazirmatn,Tahoma">بدون تصویر</text></svg>');
  const ex=(t,m=110)=>{t=(t||'').trim();return t.length>m?t.slice(0,m)+'…':t;};
  fetch('/dashboard/recent-news-feed.php?limit=3').then(r=>r.json()).then(d=>{
    const items=(d&&d.items)||[]; if(!items.length){grid.innerHTML='<div class="rncv1-empty">خبری برای نمایش وجود ندارد.</div>';return;}
    grid.innerHTML=items.map(n=>'<a class="rncv1-card" href="'+n.url+'"><div class="rncv1-img-box"><img src="'+(n.image||ph)+'" alt=""></div><p class="rncv1-cat">'+n.category+'</p><h3>'+n.title+'</h3><p class="rncv1-excerpt">'+ex(n.excerpt||'')+'</p><time>'+fa(n.date)+'</time></a>').join('');
  }).catch(()=>{grid.innerHTML='<div class="rncv1-empty">خطا در بارگذاری اخبار.</div>';});
})();
</script>

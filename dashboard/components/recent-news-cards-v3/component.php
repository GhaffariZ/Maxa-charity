<!-- Vazirmatn Variable Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

<style>
  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }
</style>
<section class="rncv3-sec" dir="rtl" data-recent-news="cards-v3">
  <div class="rncv3-wrap">
    <div class="rncv3-head"><span></span><h2>داغ‌ترین‌ها</h2></div>
    <div class="rncv3-grid" id="rncv3-grid"><div class="rncv3-empty">در حال بارگذاری اخبار...</div></div>
  </div>
</section>
<style>
.rncv3-sec{padding:64px 0;background:#fff;font-family:'Vazirmatn',Tahoma,sans-serif}.rncv3-wrap{max-width:1280px;margin:0 auto;padding:0 16px}
.rncv3-head{display:flex;align-items:center;gap:12px;margin-bottom:40px}.rncv3-head span{width:48px;height:4px;background:#2563eb;border-radius:999px}.rncv3-head h2{margin:0;font-size:32px;font-weight:800}
.rncv3-grid{display:grid;grid-template-columns:1fr;gap:24px}.rncv3-card{background:#fff;border:1px solid #f5f5f5;border-radius:18px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.04);display:flex;flex-direction:column}
.rncv3-img{aspect-ratio:16/9}.rncv3-img img{width:100%;height:100%;object-fit:cover}.rncv3-body{padding:20px;display:flex;flex-direction:column;gap:12px}
.rncv3-meta{display:flex;align-items:center;gap:8px;color:#a3a3a3;font-size:11px}.rncv3-cat{background:#eff6ff;color:#2563eb;border-radius:6px;padding:2px 8px;font-weight:700}
.rncv3-body h3{margin:0;font-size:20px;line-height:1.5;font-weight:800;color:#171717}.rncv3-body p{margin:0;color:#737373;font-size:14px;line-height:1.9}
.rncv3-foot{margin-top:8px;padding-top:12px;border-top:1px solid #f5f5f5;display:flex;align-items:center;justify-content:space-between}
.rncv3-read{font-size:12px;color:#525252}.rncv3-foot a{text-decoration:none;font-size:12px;font-weight:700;color:#171717}.rncv3-foot a:hover{color:#2563eb}
.rncv3-empty{color:#6b7280}
@media (min-width:768px){.rncv3-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (min-width:1024px){.rncv3-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
</style>
<script>
(function(){
  const host=document.querySelector('[data-recent-news="cards-v3"]'); if(!host) return;
  const grid=host.querySelector('#rncv3-grid');
  const fa=(v)=>String(v).replace(/\d/g,d=>'۰۱۲۳۴۵۶۷۸۹'[d]);
  const ph='data:image/svg+xml;utf8,'+encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="1000" height="560"><rect width="100%" height="100%" fill="#e5e7eb"/><text x="50%" y="52%" dominant-baseline="middle" text-anchor="middle" fill="#6b7280" font-size="42" font-family="Vazirmatn,Tahoma">بدون تصویر</text></svg>');
  const ex=(t,m=100)=>{t=(t||'').trim();return t.length>m?t.slice(0,m)+'…':t;};
  fetch('/dashboard/recent-news-feed.php?limit=3').then(r=>r.json()).then(d=>{
    const items=(d&&d.items)||[]; if(!items.length){grid.innerHTML='<div class="rncv3-empty">خبری برای نمایش وجود ندارد.</div>';return;}
    grid.innerHTML=items.map(n=>'<article class="rncv3-card"><div class="rncv3-img"><img src="'+(n.image||ph)+'" alt=""></div><div class="rncv3-body"><div class="rncv3-meta"><span class="rncv3-cat">'+n.category+'</span><span>'+fa(n.date)+'</span></div><h3>'+n.title+'</h3><p>'+ex(n.excerpt||'')+'</p><div class="rncv3-foot"><span class="rncv3-read">'+fa(n.read_time)+' دقیقه</span><a href="'+n.url+'">بیشتر بخوانید ‹</a></div></div></article>').join('');
  }).catch(()=>{grid.innerHTML='<div class="rncv3-empty">خطا در بارگذاری اخبار.</div>';});
})();
</script>

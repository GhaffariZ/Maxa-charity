<?php
/* ============================================================================
 *  پوسته‌ی مشترک رابط کاربری دوره‌ها — آکادمی مکسا
 *  - course_base_css() : توکن‌های طراحی و کامپوننت‌های مشترک (بدون تگ <style>)
 *  - course_site_head()/course_site_footer() : هدر و فوتر رسمیِ سایت (یکپارچه‌سازی)
 *  - course_html_head() : فقط برای صفحه‌ی پخش (یادگیری) و صفحه‌های ادمینِ دوره‌ها
 *    که تمام‌صفحه و مستقل از هدر سایت رندر می‌شوند.
 * ========================================================================== */

/* توکن‌های طراحی + کامپوننت‌های مشترک (سراسری، بدون تگ <style>) */
if (!function_exists('course_base_css')):
function course_base_css(): string {
  return <<<'CSS'
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --violet:#8b5cf6; --success:#16a37a; --danger:#e0556b; --warning:#f4a61e;
  --primary-08:rgba(0,123,122,.08); --primary-12:rgba(0,123,122,.12); --primary-16:rgba(0,123,122,.16);
  --secondary-12:rgba(244,166,30,.14); --violet-12:rgba(139,92,246,.14);
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04), 0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06), 0 2px 6px rgba(16,40,40,.04);
  --shadow-lg:0 20px 44px -14px rgba(0,102,101,.20), 0 8px 20px -10px rgba(16,40,40,.12);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --primary-08:rgba(79,178,176,.10); --primary-12:rgba(79,178,176,.16); --primary-16:rgba(79,178,176,.24);
  --secondary-12:rgba(244,166,30,.18); --violet-12:rgba(139,92,246,.20);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  --shadow-lg:0 24px 48px -16px rgba(0,0,0,.62),0 10px 24px -12px rgba(0,0,0,.5);
  color-scheme:dark;
}

/* ---- صفحه و سرصفحه ---- */
.page{max-width:1320px;margin:0 auto;padding:24px}
.page-head{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:22px}
.page-head .ph-icon{width:54px;height:54px;border-radius:16px;display:grid;place-items:center;color:#fff;flex-shrink:0;
  background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));box-shadow:0 12px 24px -12px rgba(0,102,101,.7)}
.page-head .ph-icon .ic{width:27px;height:27px}
.page-head h1{font-size:23px;font-weight:800;letter-spacing:-.01em;line-height:1.25}
.page-head .ph-sub{font-size:13px;color:var(--color-muted);font-weight:500;margin-top:2px}
.page-head .ph-spacer{flex:1}

/* ---- دکمه‌ها ---- */
.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 18px;border-radius:13px;font-weight:700;font-size:13px;
  transition:transform .15s var(--ease),box-shadow .22s,background .2s,color .2s,border-color .2s;white-space:nowrap}
.btn .ic{width:17px;height:17px}
.btn:active{transform:scale(.96)}
.btn-primary{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff;box-shadow:0 10px 22px -10px rgba(0,123,122,.7)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 16px 30px -10px rgba(0,123,122,.8)}
.btn-gold{background:linear-gradient(135deg,#ffc24d,var(--color-secondary));color:#5c3d00;box-shadow:0 10px 24px -10px rgba(244,166,30,.75)}
.btn-gold:hover{transform:translateY(-2px);box-shadow:0 16px 30px -10px rgba(244,166,30,.9)}
.btn-ghost{background:var(--color-surface);color:var(--color-text);border:1px solid var(--color-border)}
.btn-ghost:hover{border-color:var(--color-primary-light);color:var(--color-primary-dark);background:var(--primary-08)}
.btn-soft{background:var(--primary-08);color:var(--color-primary-dark)}
.btn-soft:hover{background:var(--primary-12)}
.btn-danger{background:rgba(224,85,107,.12);color:var(--danger)}
.btn-danger:hover{background:rgba(224,85,107,.2)}
.btn-block{width:100%;justify-content:center}
.btn-lg{padding:14px 24px;font-size:14.5px;border-radius:15px}

/* ---- کارت‌ها ---- */
.card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);box-shadow:var(--shadow-sm)}
.card-pad{padding:22px}
.card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:6px}
.card-head h3{font-size:16.5px;font-weight:800;letter-spacing:-.01em}
.card-head .sub{font-size:12.5px;color:var(--color-muted);margin-top:3px}

/* ---- فرم ---- */
.field{margin-bottom:16px}
.field>label,.lbl{display:flex;align-items:center;gap:7px;font-weight:700;font-size:13px;margin-bottom:8px}
.lbl .req{color:var(--danger)}
.hint{font-size:11.5px;color:var(--color-muted);font-weight:500;margin-top:6px}
.inp,.txa,.sel{width:100%;background:var(--color-bg);border:1.5px solid var(--color-border);border-radius:13px;padding:12px 14px;
  font-size:13.5px;color:var(--color-text);transition:border-color .2s,box-shadow .2s,background .2s}
.inp:focus,.txa:focus,.sel:focus{outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:var(--color-surface)}
.txa{resize:vertical;min-height:110px;line-height:1.8}
.sel{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239d9d9d' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:left 14px center;background-size:16px;padding-left:42px}
.inp-group{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:640px){.inp-group{grid-template-columns:1fr}}

/* ---- سوییچ ---- */
.switch{display:inline-flex;align-items:center;gap:11px;cursor:pointer;font-size:13px;font-weight:600}
.switch input{display:none}
.switch .track{width:44px;height:25px;border-radius:99px;background:var(--color-border);position:relative;transition:background .25s var(--ease);flex-shrink:0}
.switch .track::after{content:'';position:absolute;top:3px;right:3px;width:19px;height:19px;border-radius:50%;background:#fff;box-shadow:0 2px 5px rgba(0,0,0,.25);transition:transform .25s var(--ease)}
.switch input:checked+.track{background:var(--color-primary)}
.switch input:checked+.track::after{transform:translateX(-19px)}

/* ---- بَج‌ها ---- */
.badge{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:99px;font-size:11.5px;font-weight:700;white-space:nowrap}
.badge .bd{width:6px;height:6px;border-radius:50%}
.badge.b-ok{background:rgba(22,163,122,.12);color:var(--success)} .b-ok .bd{background:var(--success)}
.badge.b-draft{background:rgba(244,166,30,.14);color:#b9760a} .b-draft .bd{background:var(--warning)}
.badge.b-free{background:var(--violet-12);color:#6d3fd1} .b-free .bd{background:var(--violet)}
.badge.b-primary{background:var(--primary-08);color:var(--color-primary-dark)} .b-primary .bd{background:var(--color-primary)}

/* ---- توست ---- */
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(120%);z-index:200;display:flex;align-items:center;gap:11px;
  background:var(--color-surface);border:1px solid var(--color-border);box-shadow:var(--shadow-lg);border-radius:15px;padding:13px 18px;font-weight:700;font-size:13.5px;
  transition:transform .45s var(--ease);max-width:92vw;font-family:'Vazirmatn',sans-serif;color:var(--color-text)}
.toast.show{transform:translateX(-50%) translateY(0)}
.toast .ti{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;flex-shrink:0;background:rgba(22,163,122,.14);color:var(--success)}
.toast .ti .ic{width:19px;height:19px}
.toast.err .ti{background:rgba(224,85,107,.14);color:var(--danger)}

@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
.reveal{opacity:0;animation:fadeUp .55s var(--ease) forwards}
CSS;
}
endif;

/* پوسته‌ی <head> برای صفحه‌های مستقلِ تمام‌صفحه (پخش/یادگیری و ادمینِ دوره‌ها) */
if (!function_exists('course_html_head')):
function course_html_head(string $title, string $pageStyles = ''): void { ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title) ?> | آکادمی مکسا</title>
<script>(function(){try{var t=localStorage.getItem('maxa-theme');if(t==='dark'){document.documentElement.setAttribute('data-theme','dark');}}catch(e){}})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
html,body{min-height:100%}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility}
a{text-decoration:none;color:inherit}
button{font-family:inherit;cursor:pointer;border:none;background:none;color:inherit}
ul{list-style:none}
input,textarea,select{font-family:inherit}
svg.ic{display:block;width:18px;height:18px;flex-shrink:0}
::selection{background:var(--primary-16);color:var(--color-primary-dark)}
*::-webkit-scrollbar{width:9px;height:9px}
*::-webkit-scrollbar-thumb{background:rgba(0,123,122,.18);border-radius:99px;border:2px solid transparent;background-clip:padding-box}
*::-webkit-scrollbar-thumb:hover{background:rgba(0,123,122,.34);background-clip:padding-box}
[data-theme="dark"] *::-webkit-scrollbar-thumb{background:rgba(255,255,255,.18);background-clip:padding-box}
<?= course_base_css() ?>
<?= $pageStyles ?>
@media (prefers-reduced-motion:reduce){*{animation-duration:.001ms!important;transition-duration:.01ms!important}}
</style>
</head>
<?php }
endif;

/* پوسته‌ی هدر رسمی سایت برای صفحه‌های عمومیِ دوره‌ها (کاتالوگ/جزئیات/سبد/دوره‌های من).
   محتوای صفحه داخل <div class="course-shell"> قرار می‌گیرد تا ریست‌ها فقط روی محتوای
   دوره اعمال شوند و هدر/فوتر سایت دست‌نخورده بمانند. */
if (!function_exists('course_site_head')):
function course_site_head(string $title, string $pageCss = ''): void {
  /* این دو متغیر در دامنه‌ی همین تابع‌اند و هدرِ require شده آن‌ها را می‌بیند:
     - pageTitle: عنوان تب مرورگر   - maxaCoursesContext: نمایش آیتم‌های دوره در منوی حساب کاربری */
  $pageTitle = $title . ' | آکادمی مکسا';
  $maxaCoursesContext = true;
  require __DIR__ . '/components/header/component.php';
  ?>
<style>
.course-shell{font-family:'Vazirmatn',sans-serif;color:var(--color-text);font-size:14px;line-height:1.7;background:var(--color-bg);min-height:46vh}
.course-shell, .course-shell *{box-sizing:border-box}
.course-shell h1,.course-shell h2,.course-shell h3,.course-shell h4,.course-shell h5,.course-shell p,.course-shell ul,.course-shell ol,.course-shell figure,.course-shell blockquote,.course-shell del{margin:0}
.course-shell ul,.course-shell ol{list-style:none;padding:0}
.course-shell a{text-decoration:none;color:inherit}
.course-shell button{font-family:inherit;cursor:pointer;border:none;background:none;color:inherit}
.course-shell input,.course-shell textarea,.course-shell select{font-family:inherit}
.course-shell svg.ic{display:block;width:18px;height:18px;flex-shrink:0}
<?= course_base_css() ?>
<?= $pageCss ?>
</style>
<div class="course-shell">
<?php }
endif;

/* بستن پوسته + نشانگر سبد خرید + فوتر رسمی سایت */
if (!function_exists('course_site_footer')):
function course_site_footer(): void {
  echo "\n</div><!-- /.course-shell -->\n";
  course_cart_marker();
  require __DIR__ . '/components/footer/component.php';
}
endif;

/* نشانگر شناور سبد خرید در گوشه‌ی صفحه + همگام‌سازی شمارنده‌ها (نشانگر و منوی حساب) */
if (!function_exists('course_cart_marker')):
function course_cart_marker(): void { ?>
<style>
  #maxaCartFab{position:fixed;bottom:22px;left:22px;z-index:1000;width:54px;height:54px;border-radius:16px;
    display:none;place-items:center;background:linear-gradient(135deg,#0899A9,#067d8a);color:#fff;
    box-shadow:0 12px 28px -8px rgba(8,153,169,.7);transition:transform .2s ease,box-shadow .2s ease}
  #maxaCartFab:hover{transform:translateY(-3px);box-shadow:0 16px 34px -8px rgba(8,153,169,.85)}
  #maxaCartFab svg{width:24px;height:24px}
  #maxaCartFab .mx-cart-count{position:absolute;top:-6px;right:-6px;min-width:22px;height:22px;padding:0 5px;border-radius:99px;
    background:#f5a623;color:#3a2a00;font-size:12px;font-weight:800;display:grid;place-items:center;border:2px solid #fff;font-family:'Vazirmatn',sans-serif}
</style>
<a id="maxaCartFab" href="/courses/checkout" target="_top" aria-label="سبد خرید" title="سبد خرید">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1.6"/><circle cx="19" cy="21" r="1.6"/><path d="M2.5 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 2-1.6L23 7H5.6"/></svg>
  <span class="mx-cart-count">۰</span>
</a>
<script>
(function(){
  function toFa(s){return String(s).replace(/\d/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[d];});}
  function cartCount(){ try{ return (JSON.parse(localStorage.getItem('maxa-cart')||'[]')||[]).length; }catch(e){ return 0; } }
  window.maxaCartSync=function(){
    var n=cartCount();
    var fab=document.getElementById('maxaCartFab');
    if(fab){ fab.style.display = n>0 ? 'grid' : 'none'; var c=fab.querySelector('.mx-cart-count'); if(c) c.textContent=toFa(n); }
    Array.prototype.forEach.call(document.querySelectorAll('[data-cart-badge]'), function(b){
      if(n>0){ b.hidden=false; b.style.display='inline-grid'; b.textContent=toFa(n); }
      else { b.hidden=true; b.style.display='none'; }
    });
  };
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', window.maxaCartSync);
  else window.maxaCartSync();
  window.addEventListener('storage', function(e){ if(!e || e.key===null || e.key==='maxa-cart') window.maxaCartSync(); });
})();
</script>
<?php }
endif;

/* دکمه‌ی شناور تغییر تم — برای صفحه‌های مستقل (پخش/ادمینِ دوره‌ها) که از course_html_head استفاده می‌کنند */
if (!function_exists('course_theme_fab')):
function course_theme_fab(): void { ?>
<button id="maxaThemeFab" type="button" aria-label="تغییر تم" title="روشن / تاریک"
  style="position:fixed;bottom:20px;right:20px;z-index:150;width:46px;height:46px;border-radius:14px;display:grid;place-items:center;
  background:var(--color-surface);border:1px solid var(--color-border);box-shadow:var(--shadow-md);color:var(--color-text)">
  <span class="fab-sun"><?= cic('sun') ?></span><span class="fab-moon" style="display:none"><?= cic('moon') ?></span>
</button>
<script>
(function(){
  var fab=document.getElementById('maxaThemeFab'); if(!fab) return;
  var root=document.documentElement, sun=fab.querySelector('.fab-sun'), moon=fab.querySelector('.fab-moon');
  function sync(){var d=root.getAttribute('data-theme')==='dark';sun.style.display=d?'none':'block';moon.style.display=d?'block':'none';}
  try{ if(window.top!==window.self){ fab.style.display='none'; } }catch(e){}
  sync();
  fab.addEventListener('click',function(){
    var d=root.getAttribute('data-theme')==='dark';
    if(d) root.removeAttribute('data-theme'); else root.setAttribute('data-theme','dark');
    try{localStorage.setItem('maxa-theme',d?'light':'dark');}catch(e){}
    sync();
  });
})();
</script>
<?php }
endif;

/* مجموعه‌ی آیکن‌های مشترک (همان زبان تصویری index) */
if (!function_exists('cic')):
function cic(string $name, string $cls = ''): string {
  static $I = [
    'book'=>'<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
    'plus'=>'<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
    'list'=>'<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3.6" cy="6" r="1.2" fill="currentColor" stroke="none"/><circle cx="3.6" cy="12" r="1.2" fill="currentColor" stroke="none"/><circle cx="3.6" cy="18" r="1.2" fill="currentColor" stroke="none"/>',
    'video'=>'<rect x="2" y="5" width="14" height="14" rx="3"/><path d="m16 9 5-3v12l-5-3"/>',
    'text'=>'<line x1="5" y1="6" x2="19" y2="6"/><line x1="5" y1="11" x2="19" y2="11"/><line x1="5" y1="16" x2="13" y2="16"/>',
    'image'=>'<rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="9" r="1.8"/><path d="m21 16-5-5L5 21"/>',
    'pdf'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
    'quiz'=>'<circle cx="12" cy="12" r="9"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12" y2="17.01"/>',
    'trash'=>'<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
    'edit'=>'<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4z"/>',
    'drag'=>'<circle cx="9" cy="6" r="1.4" fill="currentColor" stroke="none"/><circle cx="15" cy="6" r="1.4" fill="currentColor" stroke="none"/><circle cx="9" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="15" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="9" cy="18" r="1.4" fill="currentColor" stroke="none"/><circle cx="15" cy="18" r="1.4" fill="currentColor" stroke="none"/>',
    'chevron'=>'<polyline points="6 9 12 15 18 9"/>',
    'check'=>'<polyline points="20 6 9 17 4 12"/>',
    'eye'=>'<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
    'save'=>'<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
    'rocket'=>'<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91 0z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/>',
    'users'=>'<path d="M16 21v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V21"/><circle cx="9.5" cy="7" r="3.5"/><path d="M21 21v-1.5a4 4 0 0 0-3-3.87"/><path d="M16.5 3.6a3.5 3.5 0 0 1 0 6.8"/>',
    'star'=>'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
    'clock'=>'<circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/>',
    'wallet'=>'<path d="M19 7V5.5A1.5 1.5 0 0 0 17.5 4H6a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2H6"/><circle cx="16.5" cy="12" r="1.3" fill="currentColor" stroke="none"/>',
    'search'=>'<circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
    'cart'=>'<circle cx="9" cy="21" r="1.6"/><circle cx="19" cy="21" r="1.6"/><path d="M2.5 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 2-1.6L23 7H5.6"/>',
    'play'=>'<polygon points="6 4 20 12 6 20 6 4"/>',
    'lock'=>'<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
    'arrow'=>'<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
    'globe'=>'<circle cx="12" cy="12" r="9"/><line x1="3" y1="12" x2="21" y2="12"/><path d="M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18z"/>',
    'grad'=>'<path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1 3 3 6 3s6-2 6-3v-5"/>',
    'target'=>'<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none"/>',
    'moon'=>'<path d="M21 12.8A8.5 8.5 0 1 1 11.2 3a6.6 6.6 0 0 0 9.8 9.8Z"/>',
    'sun'=>'<circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.6M12 19.4V22M2 12h2.6M19.4 12H22M4.93 4.93l1.84 1.84M17.23 17.23l1.84 1.84M19.07 4.93l-1.84 1.84M6.77 17.23l-1.84 1.84"/>',
    'filter'=>'<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
    'menu'=>'<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
    'copy'=>'<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
    'eyeoff'=>'<path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 10 8 10 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="2" y1="2" x2="22" y2="22"/><path d="M6.61 6.61A18.5 18.5 0 0 0 2 12s3 8 10 8a9.12 9.12 0 0 0 4.91-1.43"/>',
    'award'=>'<circle cx="12" cy="9" r="5.5"/><path d="M8.2 13.2 6.5 21l5.5-3 5.5 3-1.7-7.8"/>',
    'infinity'=>'<path d="M18.18 8A4.18 4.18 0 1 0 18.18 16C20 16 21 14 22 12c-1-2-2-4-3.82-4zM5.82 8A4.18 4.18 0 1 1 5.82 16C4 16 3 14 2 12c1-2 2-4 3.82-4z"/>',
  ];
  return '<svg class="ic '.$cls.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($I[$name]??'').'</svg>';
}
endif;

/* کارت دوره برای فروشگاه و فهرست‌ها */
if (!function_exists('course_card')):
function course_card(array $c): void {
  $id = (int)($c['id'] ?? 0);
  $accent = course_accent($id);
  $isFree = !empty($c['is_free']) || (int)($c['price'] ?? 0) === 0;
  $price = (float)($c['price'] ?? 0);
  $disc = ($c['discount_price'] ?? null) ? (float)$c['discount_price'] : 0;
  $hasDisc = $disc > 0 && $disc < $price;
  $thumb = (string)($c['thumbnail'] ?? '');
  $lessons = (int)($c['lessons'] ?? 0);
  $dur = (int)($c['duration'] ?? 0);
  ?>
  <a class="ccard" href="/courses/<?= $id ?>" target="_top">
    <div class="ccard-thumb" style="background:linear-gradient(135deg,<?= $accent ?>,<?= $accent ?>cc)">
      <?php if ($thumb): ?><img src="<?= e($thumb) ?>" alt=""><?php else: ?><span class="ph"><?= cic('grad') ?></span><?php endif; ?>
      <?php if ($hasDisc): $off = round((1-$disc/$price)*100); ?><span class="ccard-off"><?= fa_digits($off) ?>٪ تخفیف</span><?php endif; ?>
      <span class="ccard-play"><?= cic('play') ?></span>
    </div>
    <div class="ccard-body">
      <span class="ccard-cat"><?= e($c['category'] ?? 'عمومی') ?></span>
      <h3 class="ccard-title"><?= e($c['title'] ?? 'بدون عنوان') ?></h3>
      <div class="ccard-inst"><?= e($c['instructor'] ?? 'مدرس مکسا') ?></div>
      <div class="ccard-meta">
        <span><?= cic('star','star-y') ?><b><?= fa_digits(number_format((float)($c['rating'] ?? 0),1)) ?></b></span>
        <span><?= cic('users') ?><?= fa_digits(number_format((int)($c['students'] ?? 0))) ?></span>
        <?php if ($lessons): ?><span><?= cic('video') ?><?= fa_digits($lessons) ?> درس</span><?php endif; ?>
      </div>
      <div class="ccard-foot">
        <span class="badge b-primary"><span class="bd"></span><?= level_label((string)($c['level'] ?? 'all')) ?></span>
        <div class="ccard-price">
          <?php if ($isFree): ?><b style="color:var(--violet)">رایگان</b>
          <?php elseif ($hasDisc): ?><del><?= fa_digits(number_format($price)) ?></del><b><?= fa_digits(number_format($disc)) ?><small> ت</small></b>
          <?php else: ?><b><?= fa_digits(number_format($price)) ?><small> ت</small></b><?php endif; ?>
        </div>
      </div>
    </div>
  </a>
  <?php
}
endif;

/* استایل مشترک کارت دوره (یک‌بار در هر صفحه include صدا زده می‌شود) */
if (!function_exists('course_card_styles')):
function course_card_styles(): string {
  return <<<CSS
.ccard{display:flex;flex-direction:column;background:var(--color-surface);border:1px solid var(--color-border);border-radius:18px;overflow:hidden;box-shadow:var(--shadow-sm);transition:transform .3s var(--ease),box-shadow .3s var(--ease),border-color .3s}
.ccard:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg);border-color:transparent}
.ccard-thumb{height:160px;position:relative;display:grid;place-items:center;color:#fff;overflow:hidden}
.ccard-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.ccard-thumb .ph{opacity:.55}.ccard-thumb .ph .ic{width:46px;height:46px}
.ccard-off{position:absolute;top:11px;right:11px;background:linear-gradient(135deg,#ffc24d,var(--color-secondary));color:#5c3d00;font-size:11px;font-weight:800;padding:5px 10px;border-radius:99px;box-shadow:0 6px 14px -6px rgba(244,166,30,.7)}
.ccard-play{position:absolute;inset:0;margin:auto;width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.25);backdrop-filter:blur(4px);display:grid;place-items:center;color:#fff;opacity:0;transform:scale(.7);transition:opacity .3s,transform .3s var(--ease)}
.ccard-play .ic{width:22px;height:22px;margin-right:-2px}
.ccard:hover .ccard-play{opacity:1;transform:scale(1)}
.ccard-body{padding:15px;display:flex;flex-direction:column;flex:1}
.ccard-cat{font-size:11px;font-weight:700;color:var(--color-primary-dark);background:var(--primary-08);padding:4px 10px;border-radius:99px;align-self:flex-start}
.ccard-title{font-size:15px;font-weight:800;line-height:1.55;margin:9px 0 4px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.ccard-inst{font-size:12px;color:var(--color-muted);font-weight:500}
.ccard-meta{display:flex;align-items:center;gap:13px;margin-top:11px;font-size:11.5px;color:var(--color-muted);font-weight:600}
.ccard-meta span{display:inline-flex;align-items:center;gap:4px}.ccard-meta .ic{width:14px;height:14px}
.ccard-meta .star-y{fill:var(--warning);stroke:var(--warning)}
.ccard-foot{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:13px;padding-top:13px;border-top:1px solid var(--color-border)}
.ccard-price{display:flex;align-items:baseline;gap:7px}
.ccard-price b{font-size:16px;font-weight:800}.ccard-price b small{font-size:11px;font-weight:600;color:var(--color-muted)}
.ccard-price del{font-size:12px;color:var(--color-muted)}
CSS;
}
endif;

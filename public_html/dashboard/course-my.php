<?php
/* ============================================================================
 *  دوره‌های من (بخش کاربری) — آکادمی مکسا
 *  دوره‌های خریداری/ثبت‌نام‌شده + میزان پیشرفت (localStorage)
 * ========================================================================== */
require __DIR__ . '/course-db.php';
require __DIR__ . '/course-ui.php';

$all = load_courses($pdo, $coursesSchemaReady, 'published');
$map = [];
foreach ($all as $c) {
  $id = (int)($c['id'] ?? 0);
  $map[$id] = [
    'id'=>$id, 'title'=>$c['title'] ?? '', 'instructor'=>$c['instructor'] ?? 'مدرس مکسا',
    'category'=>$c['category'] ?? '', 'thumbnail'=>$c['thumbnail'] ?? '', 'accent'=>course_accent($id),
    'lessons'=>(int)($c['lessons'] ?? 0),
  ];
}

$pageCss = <<<CSS
.mc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px}
.mc{display:flex;flex-direction:column;background:var(--color-surface);border:1px solid var(--color-border);border-radius:18px;overflow:hidden;box-shadow:var(--shadow-sm);transition:transform .3s var(--ease),box-shadow .3s var(--ease)}
.mc:hover{transform:translateY(-5px);box-shadow:var(--shadow-lg)}
.mc-thumb{height:150px;position:relative;display:grid;place-items:center;color:#fff;overflow:hidden}
.mc-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.mc-thumb .ph .ic{width:42px;height:42px;opacity:.6}
.mc-badge{position:absolute;top:11px;right:11px;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);color:#fff;font-size:11px;font-weight:700;padding:4px 11px;border-radius:99px}
.mc-body{padding:16px;display:flex;flex-direction:column;flex:1}
.mc-cat{font-size:11px;font-weight:700;color:var(--color-primary-dark)}
.mc-title{font-size:15.5px;font-weight:800;line-height:1.5;margin:5px 0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.mc-inst{font-size:12px;color:var(--color-muted)}
.mc-prog{margin-top:14px}
.mc-prog .pb{height:7px;border-radius:99px;background:var(--color-bg);overflow:hidden}
.mc-prog .pb i{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,var(--color-primary-light),var(--color-primary));transition:width .5s var(--ease)}
.mc-prog .pm{display:flex;justify-content:space-between;font-size:11.5px;color:var(--color-muted);font-weight:600;margin-top:7px}
.mc-prog .pm b{color:var(--color-primary-dark)}
.mc-btn{margin-top:14px}
.mc-empty{text-align:center;padding:64px 20px}
.mc-empty .ei{width:84px;height:84px;border-radius:24px;display:grid;place-items:center;margin:0 auto 18px;background:var(--primary-08);color:var(--color-primary)}
.mc-empty .ei .ic{width:40px;height:40px}
.mc-stats{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:24px}
.mc-stat{flex:1;min-width:160px;display:flex;align-items:center;gap:13px;padding:16px;border:1px solid var(--color-border);border-radius:16px;background:var(--color-surface)}
.mc-stat .si{width:44px;height:44px;border-radius:13px;display:grid;place-items:center;background:var(--accent-bg);color:var(--accent)}
.mc-stat .si .ic{width:22px;height:22px}
.mc-stat .sv{font-size:21px;font-weight:800}.mc-stat .sl{font-size:12px;color:var(--color-muted);font-weight:600}
CSS;

course_site_head('دوره‌های من', $pageCss);
?>

<div class="page">
  <div class="page-head">
    <div class="ph-icon"><?= cic('grad') ?></div>
    <div><h1>دوره‌های من</h1><div class="ph-sub">به یادگیری ادامه دهید و پیشرفت خود را دنبال کنید.</div></div>
  </div>

  <div class="mc-stats" id="mcStats" style="display:none">
    <div class="mc-stat" style="--accent:#007b7a;--accent-bg:var(--primary-08)"><div class="si"><?= cic('book') ?></div><div><div class="sv" id="stCount">۰</div><div class="sl">دوره‌ی فعال</div></div></div>
    <div class="mc-stat" style="--accent:#16a37a;--accent-bg:rgba(22,163,122,.12)"><div class="si"><?= cic('check') ?></div><div><div class="sv" id="stDone">۰</div><div class="sl">دوره‌ی تکمیل‌شده</div></div></div>
    <div class="mc-stat" style="--accent:#7c4ddb;--accent-bg:var(--violet-12)"><div class="si"><?= cic('target') ?></div><div><div class="sv" id="stAvg">۰٪</div><div class="sl">میانگین پیشرفت</div></div></div>
  </div>

  <div id="mcArea"></div>
</div>

<script>
(function(){
  "use strict";
  var COURSES = <?= json_encode($map, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var toFa=function(s){return String(s).replace(/\d/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[d];});};
  var $=function(id){return document.getElementById(id);};
  var BOOK='<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>';

  function purchased(){ try{ return JSON.parse(localStorage.getItem('maxa-purchased')||'[]'); }catch(e){ return []; } }
  function progress(id){
    var c=COURSES[id]; if(!c||!c.lessons) return 0;
    var done={}; try{ done=JSON.parse(localStorage.getItem('maxa-progress-'+id)||'{}')||{}; }catch(e){}
    var n=0; for(var k in done){ if(done[k]) n++; }
    return Math.min(100, Math.round(n/c.lessons*100));
  }

  var ids=purchased().filter(function(id){ return COURSES[id]; });
  var area=$('mcArea');

  if(!ids.length){
    area.innerHTML='<div class="mc-empty card card-pad">'+
      '<div class="ei">'+BOOK+'</div>'+
      '<h2 style="font-size:20px;font-weight:800">هنوز در دوره‌ای ثبت‌نام نکرده‌اید</h2>'+
      '<p style="color:var(--color-muted);margin:8px 0 22px">از فروشگاه ما یک دوره انتخاب کنید و یادگیری را شروع کنید.</p>'+
      '<a href="/courses" class="btn btn-primary" target="_top">مشاهده‌ی دوره‌ها</a></div>';
    return;
  }

  $('mcStats').style.display='flex';
  var html='<div class="mc-grid">', sumPct=0, doneCount=0;
  ids.forEach(function(id){
    var c=COURSES[id]; var pct=progress(id); sumPct+=pct; if(pct>=100) doneCount++;
    var cta = pct>=100?'مرور دوباره':(pct>0?'ادامه‌ی یادگیری':'شروع دوره');
    html+='<div class="mc">'+
      '<div class="mc-thumb" style="background:linear-gradient(135deg,'+c.accent+','+c.accent+'cc)">'+
        (c.thumbnail?'<img src="'+c.thumbnail+'" alt="">':'<span class="ph">'+BOOK+'</span>')+
        '<span class="mc-badge">'+(pct>=100?'تکمیل‌شده ✓':toFa(pct)+'٪')+'</span></div>'+
      '<div class="mc-body">'+
        (c.category?'<span class="mc-cat">'+c.category+'</span>':'')+
        '<div class="mc-title">'+c.title+'</div>'+
        '<div class="mc-inst">'+c.instructor+'</div>'+
        '<div class="mc-prog"><div class="pb"><i style="width:'+pct+'%"></i></div>'+
          '<div class="pm"><span>پیشرفت شما</span><b>'+toFa(pct)+'٪</b></div></div>'+
        '<a href="/dashboard/course-learn.php?id='+id+'" class="btn btn-primary mc-btn" target="_top" style="justify-content:center"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="6 4 20 12 6 20 6 4"/></svg> '+cta+'</a>'+
      '</div></div>';
  });
  html+='</div>';
  area.innerHTML=html;

  $('stCount').textContent=toFa(ids.length);
  $('stDone').textContent=toFa(doneCount);
  $('stAvg').textContent=toFa(ids.length?Math.round(sumPct/ids.length):0)+'٪';
})();
</script>
<?php course_site_footer(); ?>

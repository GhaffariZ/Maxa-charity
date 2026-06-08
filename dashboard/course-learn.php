<?php
/* ============================================================================
 *  محیط یادگیری / پخش‌کننده‌ی دوره — آکادمی مکسا
 *  نمایش درس‌ها (ویدئو/متن/تصویر/PDF/آزمون) + پیشرفت فراگیر
 * ========================================================================== */
require __DIR__ . '/course-db.php';
require __DIR__ . '/course-ui.php';

$id = (int)($_GET['id'] ?? 0);
$course = load_course_full($pdo, $coursesSchemaReady, $id);
if (!$course) {
  course_html_head('دوره یافت نشد');
  echo '<body style="display:grid;place-items:center;min-height:100vh"><div style="text-align:center"><h1>دوره پیدا نشد</h1><a href="courses.php" class="btn btn-primary" target="_top" style="margin-top:14px">بازگشت به دوره‌ها</a></div></body></html>';
  exit;
}
$curr = $course['curriculum'] ?? [];
$lessons = (int)($course['lessons'] ?? 0);

$pageCss = <<<CSS
body{overflow:hidden}
.learn{display:flex;flex-direction:column;height:100vh;height:100dvh}
/* نوار بالا */
.lbar{height:60px;display:flex;align-items:center;gap:14px;padding:0 18px;background:var(--color-surface);border-bottom:1px solid var(--color-border);flex-shrink:0;z-index:20}
.lbar .back{width:40px;height:40px;border-radius:11px;display:grid;place-items:center;color:#5b6469;transition:background .2s}
.lbar .back:hover{background:var(--primary-08);color:var(--color-primary-dark)}
.lbar .ttl{font-weight:800;font-size:15px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lbar .ttl small{display:block;font-size:11px;color:var(--color-muted);font-weight:500}
.lbar .sp{flex:1}
.lprog{display:flex;align-items:center;gap:10px}
.lprog .ring{position:relative;width:38px;height:38px}
.lprog .ring svg{transform:rotate(-90deg)}
.lprog .ring b{position:absolute;inset:0;display:grid;place-items:center;font-size:10px;font-weight:800;color:var(--color-primary-dark)}
.lprog .txt{font-size:12px;font-weight:700}.lprog .txt small{color:var(--color-muted);font-weight:500;font-size:11px}
.menu-tog{display:none;width:40px;height:40px;border-radius:11px;place-items:center;color:#5b6469}
@media(max-width:900px){.menu-tog{display:grid}}

/* بدنه */
.lbody{flex:1;display:grid;grid-template-columns:1fr 340px;min-height:0}
@media(max-width:900px){.lbody{grid-template-columns:1fr}}
.lstage{overflow-y:auto;padding:0 0 40px}
.stage-media{background:#0a0f12;aspect-ratio:16/9;width:100%;display:grid;place-items:center;color:#fff;position:relative}
.stage-media video,.stage-media iframe,.stage-media img{width:100%;height:100%;object-fit:contain;border:0}
.stage-media .ph{text-align:center;opacity:.6}.stage-media .ph .ic{width:54px;height:54px;margin:0 auto}
.stage-text{max-width:760px;margin:0 auto;padding:28px 24px;font-size:15px;line-height:2.1;white-space:pre-line}
.stage-img{max-width:900px;margin:0 auto;padding:24px;text-align:center}
.stage-img img{max-width:100%;border-radius:14px;box-shadow:var(--shadow-md)}
.stage-head{max-width:900px;margin:0 auto;padding:20px 24px 0;display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap}
.stage-head h1{font-size:21px;font-weight:800;line-height:1.45}
.stage-head .meta{font-size:12.5px;color:var(--color-muted);margin-top:5px;display:flex;gap:12px}
.stage-nav{max-width:900px;margin:24px auto 0;padding:0 24px;display:flex;justify-content:space-between;gap:12px}

/* سایدبار سرفصل */
.lside{border-inline-start:1px solid var(--color-border);background:var(--color-surface);overflow-y:auto;display:flex;flex-direction:column}
.lside-head{padding:16px 16px 12px;border-bottom:1px solid var(--color-border);position:sticky;top:0;background:var(--color-surface);z-index:2}
.lside-head h3{font-size:14.5px;font-weight:800}
.lside-head .pbar{height:7px;border-radius:99px;background:var(--color-bg);overflow:hidden;margin-top:10px}
.lside-head .pbar i{display:block;height:100%;background:linear-gradient(90deg,var(--color-primary-light),var(--color-primary));width:0;transition:width .4s var(--ease)}
.lside-head .pmeta{font-size:11.5px;color:var(--color-muted);font-weight:600;margin-top:7px}
.lsec{border-bottom:1px solid var(--color-border)}
.lsec-h{display:flex;align-items:center;gap:8px;padding:13px 16px;cursor:pointer;font-weight:800;font-size:13.5px;transition:background .18s}
.lsec-h:hover{background:var(--primary-08)}
.lsec-h .chev{margin-inline-start:auto;color:var(--color-muted);transition:transform .3s}
.lsec.collapsed .chev{transform:rotate(-90deg)}
.lsec.collapsed .lsec-b{display:none}
.lsec-h .sm{font-size:11px;color:var(--color-muted);font-weight:600}
.litem{display:flex;align-items:center;gap:11px;padding:11px 16px 11px 14px;cursor:pointer;border-top:1px solid var(--color-border);transition:background .15s;position:relative}
.litem:hover{background:var(--color-bg)}
.litem.active{background:var(--primary-08)}
.litem.active::before{content:'';position:absolute;right:0;top:0;bottom:0;width:3px;background:var(--color-primary)}
.litem .chk{width:22px;height:22px;border-radius:50%;border:2px solid var(--color-border);display:grid;place-items:center;flex-shrink:0;transition:background .2s,border-color .2s}
.litem .chk .ic{width:12px;height:12px;color:#fff;opacity:0;stroke-width:3.4;transition:opacity .2s}
.litem.done .chk{background:var(--success);border-color:var(--success)}.litem.done .chk .ic{opacity:1}
.litem .li-ic{width:16px;height:16px;color:var(--color-muted);flex-shrink:0}
.litem .li-name{flex:1;font-size:12.8px;font-weight:600;line-height:1.5}
.litem.active .li-name{color:var(--color-primary-dark)}
.litem .li-dur{font-size:10.5px;color:var(--color-muted);font-variant-numeric:tabular-nums}
@media(max-width:900px){.lside{position:fixed;top:60px;left:0;bottom:0;width:320px;max-width:88vw;transform:translateX(-100%);transition:transform .35s var(--ease);z-index:30;box-shadow:var(--shadow-lg)}.lside.open{transform:none}}
.lside-ov{display:none;position:fixed;inset:60px 0 0;background:rgba(16,40,40,.4);z-index:25}
.lside-ov.show{display:block}
CSS;

course_html_head('یادگیری: ' . ($course['title'] ?? ''), $pageCss);
?>
<body>
<div class="learn">
  <!-- نوار بالا -->
  <div class="lbar">
    <a href="course-details.php?id=<?= $id ?>" class="back" target="_top" title="بازگشت"><?= cic('arrow') ?></a>
    <div class="ttl"><?= e($course['title'] ?? '') ?><small><?= e($course['instructor'] ?? 'آکادمی مکسا') ?></small></div>
    <div class="sp"></div>
    <div class="lprog">
      <div class="ring">
        <svg width="38" height="38"><circle cx="19" cy="19" r="15" fill="none" stroke="var(--color-border)" stroke-width="4"/>
          <circle id="ringFg" cx="19" cy="19" r="15" fill="none" stroke="var(--color-primary)" stroke-width="4" stroke-linecap="round" stroke-dasharray="94.2" stroke-dashoffset="94.2"/></svg>
        <b id="ringPct">۰٪</b>
      </div>
      <div class="txt"><span id="doneCount">۰</span> از <?= fa_digits($lessons) ?><small> درس</small></div>
    </div>
    <button class="menu-tog" id="menuTog"><?= cic('menu') ?></button>
  </div>

  <div class="lbody">
    <!-- صحنه -->
    <div class="lstage" id="stage">
      <div class="stage-media" id="media"><div class="ph"><?= cic('play') ?><p style="margin-top:8px">یک درس را از فهرست انتخاب کنید</p></div></div>
      <div class="stage-head">
        <div><h1 id="lessonTitle">به دوره خوش آمدید</h1><div class="meta" id="lessonMeta"></div></div>
        <button class="btn btn-primary" id="completeBtn" style="display:none"><?= cic('check') ?> تکمیل و درس بعد</button>
      </div>
      <div id="textContent"></div>
      <div class="stage-nav">
        <button class="btn btn-ghost" id="prevBtn"><?= cic('chevron','') ?> درس قبلی</button>
        <button class="btn btn-ghost" id="nextBtn">درس بعدی <?= cic('chevron','') ?></button>
      </div>
    </div>

    <!-- سایدبار -->
    <aside class="lside" id="lside">
      <div class="lside-head">
        <h3>محتوای دوره</h3>
        <div class="pbar"><i id="sideBar"></i></div>
        <div class="pmeta"><span id="sideMeta">۰٪ تکمیل شده</span></div>
      </div>
      <div id="sideList"></div>
    </aside>
    <div class="lside-ov" id="lsideOv"></div>
  </div>
</div>

<script>
(function(){
  "use strict";
  var CID = <?= $id ?>;
  var CURR = <?= json_encode($curr, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var toFa=function(s){return String(s).replace(/\d/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[d];});};
  var $=function(id){return document.getElementById(id);};

  // مسطح‌کردن درس‌ها
  var FLAT=[];
  CURR.forEach(function(sec,si){ (sec.lessons||[]).forEach(function(l,li){ FLAT.push({sec:si,idx:li,data:l}); }); });

  var TYPE_ICONS={video:'<polygon points="6 4 20 12 6 20 6 4"/>',text:'<line x1="5" y1="6" x2="19" y2="6"/><line x1="5" y1="11" x2="19" y2="11"/><line x1="5" y1="16" x2="13" y2="16"/>',image:'<rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="9" r="1.8"/><path d="m21 16-5-5L5 21"/>',pdf:'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',quiz:'<circle cx="12" cy="12" r="9"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12" y2="17.01"/>'};
  var TYPE_LABEL={video:'ویدئو',text:'متن',image:'تصویر',pdf:'PDF',quiz:'آزمون'};
  function svg(name,cls){return '<svg class="ic '+(cls||'')+'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'+(TYPE_ICONS[name]||TYPE_ICONS.video)+'</svg>';}
  var I_CHECK='<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
  var I_CHEV='<svg class="ic chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';

  // وضعیت پیشرفت از localStorage
  var KEY='maxa-progress-'+CID;
  var done={}; try{ done=JSON.parse(localStorage.getItem(KEY)||'{}')||{}; }catch(e){ done={}; }
  function save(){ try{ localStorage.setItem(KEY, JSON.stringify(done)); }catch(e){} }
  function gIndex(si,li){ return si+'-'+li; }

  // رندر سایدبار
  function renderSide(){
    var html='';
    CURR.forEach(function(sec,si){
      var ls=sec.lessons||[];
      html+='<div class="lsec" data-si="'+si+'">'+
        '<div class="lsec-h"><span>'+(sec.title||('فصل '+toFa(si+1)))+'</span>'+
        '<span class="sm">'+toFa(ls.length)+' درس</span>'+I_CHEV+'</div><div class="lsec-b">';
      ls.forEach(function(l,li){
        var d=done[gIndex(si,li)];
        var dur=(l.duration?toFa(l.duration)+':۰۰':'');
        html+='<div class="litem'+(d?' done':'')+'" data-si="'+si+'" data-li="'+li+'">'+
          '<span class="chk">'+I_CHECK+'</span>'+
          svg(l.type,'li-ic')+
          '<span class="li-name">'+(l.title||'درس')+'</span>'+
          '<span class="li-dur">'+dur+'</span></div>';
      });
      html+='</div></div>';
    });
    $('sideList').innerHTML=html||'<div style="padding:30px;text-align:center;color:var(--color-muted)">محتوایی برای این دوره ثبت نشده است.</div>';
    $('sideList').querySelectorAll('.lsec-h').forEach(function(h){ h.addEventListener('click',function(){ h.closest('.lsec').classList.toggle('collapsed'); }); });
    $('sideList').querySelectorAll('.litem').forEach(function(it){ it.addEventListener('click',function(){ openLesson(+it.getAttribute('data-si'),+it.getAttribute('data-li')); closeSide(); }); });
  }

  var cur={si:-1,li:-1};
  function flatPos(si,li){ for(var i=0;i<FLAT.length;i++){ if(FLAT[i].sec===si&&FLAT[i].idx===li) return i; } return -1; }

  function openLesson(si,li){
    var l=(CURR[si]&&CURR[si].lessons[li])?CURR[si].lessons[li]:null; if(!l) return;
    cur={si:si,li:li};
    $('lessonTitle').textContent=l.title||'درس';
    $('lessonMeta').innerHTML='<span>'+TYPE_LABEL[l.type]+'</span>'+(l.duration?'<span>'+toFa(l.duration)+' دقیقه</span>':'');
    var media=$('media'), text=$('textContent');
    media.style.display='none'; text.innerHTML='';
    var c=(l.content||'').trim();
    if(l.type==='video'){
      media.style.display='grid';
      if(!c){ media.innerHTML='<div class="ph">'+svg('video')+'<p style="margin-top:8px">ویدئوی این درس هنوز بارگذاری نشده است</p></div>'; }
      else if(/<iframe/i.test(c)) media.innerHTML=c;
      else if(/youtube|youtu\.be|aparat/i.test(c)) media.innerHTML='<iframe src="'+c+'" allowfullscreen></iframe>';
      else media.innerHTML='<video src="'+c+'" controls></video>';
    } else if(l.type==='image'){
      text.innerHTML='<div class="stage-img">'+(c?'<img src="'+c+'" alt="">':'<p style="color:var(--color-muted);padding:40px">تصویری ثبت نشده است</p>')+'</div>';
    } else if(l.type==='pdf'){
      media.style.display='grid';
      media.innerHTML = c? '<iframe src="'+c+'"></iframe>' : '<div class="ph">'+svg('pdf')+'<p style="margin-top:8px">فایل PDF ثبت نشده است</p></div>';
    } else {
      text.innerHTML='<div class="stage-text">'+(c?c.replace(/</g,'&lt;'):'<span style="color:var(--color-muted)">محتوای متنی این درس هنوز اضافه نشده است.</span>')+'</div>';
    }
    // فعال‌سازی آیتم
    $('sideList').querySelectorAll('.litem').forEach(function(x){ x.classList.remove('active'); });
    var act=$('sideList').querySelector('.litem[data-si="'+si+'"][data-li="'+li+'"]'); if(act){ act.classList.add('active'); act.closest('.lsec').classList.remove('collapsed'); }
    $('completeBtn').style.display='inline-flex';
    $('completeBtn').innerHTML = done[gIndex(si,li)] ? I_CHECK+' تکمیل‌شده — درس بعد' : I_CHECK+' تکمیل و درس بعد';
    $('stage').scrollTop=0;
    var p=flatPos(si,li);
    $('prevBtn').disabled=(p<=0); $('nextBtn').disabled=(p>=FLAT.length-1);
    $('prevBtn').style.opacity=$('prevBtn').disabled?'.4':'1';
    $('nextBtn').style.opacity=$('nextBtn').disabled?'.4':'1';
  }

  function markDone(si,li){ done[gIndex(si,li)]=1; save();
    var it=$('sideList').querySelector('.litem[data-si="'+si+'"][data-li="'+li+'"]'); if(it) it.classList.add('done');
    updateProgress();
  }
  function updateProgress(){
    var total=FLAT.length, d=0; FLAT.forEach(function(f){ if(done[gIndex(f.sec,f.idx)]) d++; });
    var pct= total? Math.round(d/total*100):0;
    $('ringPct').textContent=toFa(pct)+'٪';
    $('ringFg').style.strokeDashoffset = (94.2*(1-pct/100)).toFixed(1);
    $('doneCount').textContent=toFa(d);
    $('sideBar').style.width=pct+'%';
    $('sideMeta').textContent=toFa(pct)+'٪ تکمیل شده';
  }

  function go(delta){ var p=flatPos(cur.si,cur.li); var n=p+delta; if(n>=0&&n<FLAT.length) openLesson(FLAT[n].sec,FLAT[n].idx); }

  $('completeBtn').addEventListener('click', function(){ markDone(cur.si,cur.li); go(1); });
  $('nextBtn').addEventListener('click', function(){ go(1); });
  $('prevBtn').addEventListener('click', function(){ go(-1); });

  // سایدبار موبایل
  function closeSide(){ $('lside').classList.remove('open'); $('lsideOv').classList.remove('show'); }
  $('menuTog').addEventListener('click', function(){ $('lside').classList.toggle('open'); $('lsideOv').classList.toggle('show'); });
  $('lsideOv').addEventListener('click', closeSide);

  // راه‌اندازی
  renderSide(); updateProgress();
  if(FLAT.length) openLesson(FLAT[0].sec,FLAT[0].idx);
})();
</script>
</body>
</html>

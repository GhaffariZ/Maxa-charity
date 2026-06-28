<?php
require_once __DIR__ . '/_guard.php';
dash_require('hero');
require_once __DIR__ . "/../../config/database.php";

// ایزولاسیون چندمستأجری: فقط هیروهای شعبه‌ی فعال
$__branch = dash_active_branch_id();
$__hstmt = $pdo->prepare("SELECT * FROM hero_slides WHERE branch_id = ? ORDER BY sort_order ASC");
$__hstmt->execute([$__branch]);
$slides = $__hstmt->fetchAll(PDO::FETCH_ASSOC);

if(count($slides) == 0){
$slides = [
['id'=>'','title'=>'','description'=>'','button_link'=>'','publish_date'=>'','sort_order'=>1,'status'=>1,'image'=>''],
['id'=>'','title'=>'','description'=>'','button_link'=>'','publish_date'=>'','sort_order'=>2,'status'=>1,'image'=>''],
['id'=>'','title'=>'','description'=>'','button_link'=>'','publish_date'=>'','sort_order'=>3,'status'=>1,'image'=>'']
];
}

/* شمارشِ کلِ هیروها (فقط برای نمایش در کارتِ بالای صفحه) */
$activeSlides = count(array_filter($slides, function($s){ return !empty($s['id']); }));
if(!function_exists('hm_fa')){
  function hm_fa($n){ return strtr((string)$n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']); }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مدیریت هیرو | پنل مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-text:#2f3437; --color-muted:#9d9d9d;
  --success:#16a37a; --danger:#e0556b; --violet:#7c4ddb;
  --primary-08:rgba(0,123,122,.08); --primary-12:rgba(0,123,122,.12);
  --secondary-12:rgba(244,166,30,.14); --danger-12:rgba(224,85,107,.12);
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --shadow-lg:0 20px 44px -14px rgba(0,102,101,.20),0 8px 20px -10px rgba(16,40,40,.12);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --primary-08:rgba(79,178,176,.10); --primary-12:rgba(79,178,176,.16);
  --secondary-12:rgba(244,166,30,.16); --danger-12:rgba(224,85,107,.16);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  --shadow-lg:0 24px 48px -16px rgba(0,0,0,.62),0 10px 24px -12px rgba(0,0,0,.5);
  color-scheme:dark; background-color:var(--color-bg);
}
:root{--color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;padding:28px 22px;transition:background .3s,color .3s}
*::-webkit-scrollbar{width:9px;height:9px}
*::-webkit-scrollbar-thumb{background:rgba(0,123,122,.20);border-radius:99px;border:2px solid transparent;background-clip:padding-box}
[data-theme="dark"] *::-webkit-scrollbar-thumb{background:rgba(255,255,255,.16);background-clip:padding-box}

.hm-wrap{max-width:1000px;margin:0 auto}

.hm-head{display:flex;align-items:center;gap:16px;margin-bottom:18px}
.hm-head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.hm-head-ic svg{width:27px;height:27px}
.hm-head-txt{flex:1;min-width:0}
.hm-head h1{font-size:22px;font-weight:800;letter-spacing:-.01em}
.hm-head p{font-size:13px;color:var(--color-muted);margin-top:3px;max-width:640px}

/* کارتِ آماری */
.hm-stats{display:grid;grid-template-columns:minmax(0,260px);gap:14px;margin-bottom:22px}
.hm-stat{font-family:inherit;text-align:start;background:var(--color-surface);border:1.5px solid var(--color-border);border-radius:16px;
  padding:15px 18px;box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:13px}
.hm-stat-ic{width:44px;height:44px;border-radius:13px;display:grid;place-items:center;flex-shrink:0}
.hm-stat-ic svg{width:22px;height:22px}
.hm-stat b{font-size:22px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums;display:block;color:var(--color-text)}
.hm-stat span{font-size:12px;color:var(--color-muted);font-weight:600;margin-top:4px;display:block}
.ic-active{background:rgba(22,163,122,.12);color:var(--success)}

/* عنوان ستونِ ترتیب */
.hm-list-head{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:var(--color-muted);padding:2px 16px 10px}
.hm-list-head svg{width:15px;height:15px}

/* آکاردئونِ اسلایدها */
.hm-item{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);box-shadow:var(--shadow-sm);
  margin-bottom:14px;overflow:hidden;transition:box-shadow .25s var(--ease),border-color .2s,opacity .2s}
.hm-item:hover{box-shadow:var(--shadow-md)}
.hm-item.open{border-color:var(--color-primary-light)}
.hm-item.dragging{opacity:.55;box-shadow:var(--shadow-lg);border-color:var(--color-primary-light)}
body.hm-dragging{cursor:grabbing;user-select:none;-webkit-user-select:none;touch-action:none}
body.hm-dragging .hm-drag{cursor:grabbing}

.hm-item-head{display:flex;align-items:stretch}
.hm-reorder{display:flex;align-items:center;gap:9px;padding:0 14px;border-inline-end:1px solid var(--color-border);flex-shrink:0}
.hm-drag{display:grid;place-items:center;width:26px;height:26px;color:var(--color-muted);cursor:grab;touch-action:none;border-radius:8px;transition:background .2s,color .2s}
.hm-drag:hover{background:var(--primary-08);color:var(--color-primary-dark)}
.hm-drag:active{cursor:grabbing}
.hm-drag svg{width:18px;height:18px;pointer-events:none}
.hm-order{min-width:28px;height:28px;padding:0 6px;border-radius:9px;display:grid;place-items:center;font-size:13px;font-weight:800;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));font-variant-numeric:tabular-nums}

.hm-item-toggle{flex:1;min-width:0;display:flex;align-items:center;gap:12px;background:none;border:none;cursor:pointer;font-family:inherit;
  color:var(--color-text);padding:15px 18px;text-align:start;transition:background .2s}
.hm-item-toggle:hover{background:var(--primary-08)}
.hm-item-title{flex:1;min-width:0;font-weight:700;font-size:14.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-align:start}
.hm-item-title.empty{color:var(--color-muted);font-weight:600}
.hm-pic-flag{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;color:var(--color-primary-dark);background:var(--primary-08);padding:5px 10px;border-radius:99px;flex-shrink:0}
.hm-pic-flag svg{width:13px;height:13px}
.hm-chev{width:20px;height:20px;color:var(--color-muted);transition:transform .38s var(--ease);flex-shrink:0}
.hm-item.open .hm-chev{transform:rotate(180deg)}

.hm-item-body{max-height:0;overflow:hidden;opacity:0;transition:max-height .45s var(--ease),opacity .35s ease}
.hm-item.open .hm-item-body{max-height:1800px;opacity:1}
.hm-body-inner{padding:6px 18px 22px;border-top:1px solid var(--color-border)}

/* بنرِ بزرگِ تصویر */
.hm-banner{width:100%;margin:16px 0 6px;border-radius:16px;overflow:hidden;border:1px solid var(--color-border);background:var(--color-bg)}
.hm-banner img{width:100%;max-height:300px;object-fit:cover;display:block}

.hm-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:14px}
.hm-col-2{grid-column:1 / -1}
.hm-field{display:flex;flex-direction:column;gap:7px;min-width:0}
.hm-field label{font-size:12.5px;font-weight:700;color:var(--color-text)}
.hm-field input,.hm-field textarea{font-family:inherit;font-size:13.5px;color:var(--color-text);background:var(--color-bg);
  border:1.5px solid var(--color-border);border-radius:var(--radius-sm);padding:11px 13px;width:100%;transition:border-color .2s,box-shadow .2s,background .2s}
.hm-field textarea{min-height:90px;resize:vertical;line-height:1.8}
.hm-field input:focus,.hm-field textarea:focus{outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:var(--color-surface)}
.hm-field input[type="number"]{direction:ltr;text-align:right}
.hm-field input[type="file"]{padding:9px 12px;cursor:pointer}
.hm-field input[type="file"]::file-selector-button{font-family:inherit;font-weight:700;font-size:12.5px;color:var(--color-primary-dark);
  background:var(--primary-08);border:none;border-radius:9px;padding:8px 14px;margin-inline-end:12px;cursor:pointer;transition:filter .2s}
.hm-field input[type="file"]::file-selector-button:hover{filter:brightness(.96)}

.hm-empty{text-align:center;padding:50px 20px;color:var(--color-muted);font-weight:600;background:var(--color-surface);
  border:1.5px dashed var(--color-border);border-radius:var(--radius)}

.hm-actions{position:sticky;bottom:14px;display:flex;align-items:center;justify-content:flex-end;gap:14px;margin-top:18px}
.hm-msg{font-size:13px;font-weight:700}
.hm-msg.ok{color:var(--success)}
.hm-msg.err{color:var(--danger)}
.btn{display:inline-flex;align-items:center;gap:9px;font-family:inherit;font-weight:800;font-size:14px;border:none;cursor:pointer;text-decoration:none;
  padding:13px 28px;border-radius:14px;transition:transform .15s var(--ease),box-shadow .25s,filter .2s}
.btn svg{width:18px;height:18px}
.btn:active{transform:scale(.97)}
.btn-primary{color:#fff;background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));box-shadow:0 12px 26px -10px rgba(0,123,122,.7)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 18px 32px -10px rgba(0,123,122,.8)}
/* دکمه‌ی «ساخت هیرو جدید» در هدر */
.btn-new{flex-shrink:0;color:#fff;padding:11px 20px;font-size:13.5px;border-radius:12px;
  background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));box-shadow:0 10px 22px -10px rgba(0,123,122,.7)}
.btn-new:hover{transform:translateY(-2px);box-shadow:0 16px 28px -10px rgba(0,123,122,.8)}

/* دکمه‌ی حذفِ هر اسلاید (داخل آکاردئون) */
.hm-row-actions{display:flex;justify-content:flex-end;margin-top:16px}
.hm-del{display:inline-flex;align-items:center;gap:7px;font-family:inherit;font-weight:700;font-size:13px;cursor:pointer;
  color:var(--danger);background:var(--danger-12);border:1px solid transparent;border-radius:11px;padding:9px 16px;transition:filter .2s,border-color .2s}
.hm-del:hover{border-color:var(--danger);filter:brightness(.98)}
.hm-del svg{width:15px;height:15px}

@media (max-width:680px){
  body{padding:18px 14px}
  .hm-head{flex-wrap:wrap}
  .btn-new{width:100%;justify-content:center}
  .hm-stats{grid-template-columns:1fr}
  .hm-grid{grid-template-columns:1fr}
  .hm-head h1{font-size:19px}
  .hm-banner img{max-height:210px}
  .hm-stat b{font-size:20px}
}
</style>
</head>
<body>

<main class="hm-wrap">
  <header class="hm-head">
    <div class="hm-head-ic">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2.5"/><path d="m3 14 4.5-4.5a2 2 0 0 1 2.8 0L16 15"/><path d="m14 13 1.8-1.8a2 2 0 0 1 2.8 0L21 14"/><circle cx="8.5" cy="8.5" r="1.4"/></svg>
    </div>
    <div class="hm-head-txt">
      <h1>مدیریت هیرو</h1>
      <p>هیروهای فعالِ این شعبه را اینجا ویرایش کنید. با گرفتنِ آیکونِ کنار هر هیرو، ترتیب نمایش را با کشیدن جابه‌جا کنید.</p>
    </div>
    <a href="hero-create.php" class="btn btn-new">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      ساخت هیرو جدید
    </a>
  </header>

  <section class="hm-stats">
    <div class="hm-stat">
      <div class="hm-stat-ic ic-active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
      <div><b><?= hm_fa($activeSlides) ?></b><span>هیروهای فعال</span></div>
    </div>
  </section>

  <div class="hm-list-head">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="4" cy="6" r="1" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1" fill="currentColor" stroke="none"/></svg>
    ترتیب نمایش — برای جابه‌جایی، آیکونِ کنار هر هیرو را بکشید
  </div>

  <form id="heroForm" action="hero-save.php" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div id="heroList">
    <?php foreach($slides as $i=>$slide){
      $tt    = trim((string)$slide['title']);
      $hid   = (int)($slide['id'] ?? 0);
      // تاریخِ دیتابیس (datetime) را به فرمتِ ورودیِ <input type="date"> تبدیل کن
      $pdate = !empty($slide['publish_date']) ? substr((string)$slide['publish_date'], 0, 10) : '';
    ?>

    <section class="hm-item">
      <div class="hm-item-head">
        <span class="hm-reorder">
          <span class="hm-drag" title="کشیدن برای تغییر ترتیب" aria-label="جابه‌جایی">
            <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>
          </span>
          <span class="hm-order"><?= hm_fa($i + 1) ?></span>
        </span>

        <button type="button" class="hm-item-toggle" onclick="toggleHero(this)" aria-expanded="false">
          <span class="hm-item-title <?= $tt==='' ? 'empty' : '' ?>" id="hmtitle<?= $i ?>"><?= $tt==='' ? 'اسلاید بدون عنوان' : htmlspecialchars($tt) ?></span>
          <?php if(!empty($slide['image'])){ ?>
            <span class="hm-pic-flag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2.5"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="m21 16-5-5L5 21"/></svg>تصویر</span>
          <?php } ?>
          <svg class="hm-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
      </div>

      <div class="hm-item-body">
        <div class="hm-body-inner">

          <input type="hidden" name="id[]" value="<?php echo $slide['id']; ?>">
          <!-- ترتیب نمایش با کشیدن (drag) کنترل می‌شود و در این ورودیِ مخفی نگه داشته می‌شود -->
          <input type="hidden" name="sort_order[]" value="<?php echo $slide['sort_order']; ?>">
          <!-- ورودیِ تصویر برای سازگاری با hero-save.php نگه داشته شده ولی پنهان است -->
          <input type="file" name="image[]" accept="image/*" style="display:none" tabindex="-1" aria-hidden="true">

          <?php if(!empty($slide['image'])){ ?>
            <div class="hm-banner"><img src="<?php echo htmlspecialchars($slide['image']); ?>" alt="تصویر اسلاید"></div>
          <?php } ?>

          <div class="hm-grid">
            <div class="hm-field hm-col-2">
              <label>عنوان</label>
              <input type="text" name="title[]" value="<?php echo htmlspecialchars($slide['title']); ?>" placeholder="عنوان اسلاید را وارد کنید..."
                     oninput="var t=this.value.trim();var el=document.getElementById('hmtitle<?= $i ?>');el.textContent=t||'اسلاید بدون عنوان';el.classList.toggle('empty',!t)">
            </div>

            <div class="hm-field hm-col-2">
              <label>توضیحات</label>
              <textarea name="description[]" placeholder="توضیحات اسلاید..."><?php echo htmlspecialchars($slide['description']); ?></textarea>
            </div>

            <div class="hm-field">
              <label>لینک دکمه</label>
              <input type="text" name="button_link[]" value="<?php echo htmlspecialchars($slide['button_link'] ?? ''); ?>" placeholder="https://...">
            </div>

            <div class="hm-field">
              <label>تاریخ انتشار</label>
              <input type="date" name="publish_date[]" dir="ltr" value="<?php echo htmlspecialchars($pdate); ?>">
            </div>
          </div>

          <?php if($hid > 0){ ?>
          <div class="hm-row-actions">
            <button type="button" class="hm-del" onclick="deleteHero(<?= $hid ?>, this)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              حذف این هیرو
            </button>
          </div>
          <?php } ?>
        </div>
      </div>
    </section>

    <?php } ?>
    </div>

    <div class="hm-actions">
      <span id="hmMsg" class="hm-msg" role="status"></span>
      <button type="submit" id="hmSaveBtn" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
        ذخیره هیرو
      </button>
    </div>
  </form>
</main>

<script>
(function(){
  "use strict";
  var heroList = document.getElementById('heroList');

  function faNum(n){ return String(n).replace(/\d/g, function(d){ return '۰۱۲۳۴۵۶۷۸۹'[d]; }); }

  /* آکاردئون: کلیک روی عنوان، باز/بسته می‌کند */
  window.toggleHero = function(btn){
    var item = btn.closest('.hm-item');
    var open = item.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  };

  function allItems(){
    return Array.prototype.slice.call(heroList.querySelectorAll('.hm-item'));
  }

  /* شماره‌گذاری مجددِ ترتیب نمایش (هم نشانگرِ عددی هم مقدارِ sort_order) */
  function renumber(){
    allItems().forEach(function(it, idx){
      var badge = it.querySelector('.hm-order'); if(badge) badge.textContent = faNum(idx + 1);
      var si = it.querySelector('input[name="sort_order[]"]'); if(si) si.value = idx + 1;
    });
  }

  /* درگ‌اند‌دراپِ ترتیب — Pointer Events با شنونده‌های سطحِ document
     (مقاوم: بدون setPointerCapture که هنگام جابه‌جاییِ DOM رها می‌شد، و رهاکردن همیشه گرفته می‌شود) */
  var drag = { item:null, started:false, startY:0 };

  function getDragAfter(y){
    var closest = { offset:-Infinity, el:null };
    allItems().forEach(function(child){
      if(child === drag.item) return;
      var box = child.getBoundingClientRect();
      var offset = y - box.top - box.height/2;
      if(offset < 0 && offset > closest.offset) closest = { offset:offset, el:child };
    });
    return closest.el;
  }
  function onMove(e){
    if(!drag.item) return;
    if(!drag.started){
      if(Math.abs(e.clientY - drag.startY) < 5) return;   // آستانه‌ی شروع (تکانِ کوچک نادیده)
      drag.started = true;
      drag.item.classList.add('dragging');
      drag.item.classList.remove('open');
      document.body.classList.add('hm-dragging');
    }
    if(e.cancelable) e.preventDefault();
    var after = getDragAfter(e.clientY);
    if(after == null) heroList.appendChild(drag.item);
    else if(after !== drag.item) heroList.insertBefore(drag.item, after);
  }
  function onUp(){
    document.removeEventListener('pointermove', onMove);
    document.removeEventListener('pointerup', onUp);
    document.removeEventListener('pointercancel', onUp);
    if(drag.started){
      drag.item.classList.remove('dragging');
      document.body.classList.remove('hm-dragging');
      renumber();
    }
    drag.item = null; drag.started = false;
  }
  heroList.addEventListener('pointerdown', function(e){
    var handle = e.target.closest('.hm-drag');
    if(!handle) return;
    e.preventDefault();
    drag.item = handle.closest('.hm-item');
    drag.started = false;
    drag.startY = e.clientY;
    document.addEventListener('pointermove', onMove);
    document.addEventListener('pointerup', onUp);
    document.addEventListener('pointercancel', onUp);
  });

  /* شماره‌گذاریِ اولیه‌ی ترتیب */
  renumber();

  /* ذخیره‌ی گروهی با AJAX (تا پاسخِ JSON به‌جای نمایشِ خام، پردازش شود) */
  var form    = document.getElementById('heroForm');
  var saveBtn = document.getElementById('hmSaveBtn');
  var msgBox  = document.getElementById('hmMsg');

  function showMsg(text, ok){
    msgBox.textContent = text;
    msgBox.className = 'hm-msg ' + (ok ? 'ok' : 'err');
  }

  form.addEventListener('submit', function(e){
    e.preventDefault();
    renumber();
    saveBtn.disabled = true;
    showMsg('در حال ذخیره...', true);

    fetch('hero-save.php', { method:'POST', body:new FormData(form) })
      .then(function(r){ return r.json(); })
      .then(function(data){
        if(data && data.status === 'success'){
          showMsg(data.message || 'ذخیره شد ✅', true);
          // بارگذاری مجدد تا ردیف‌های جدید id واقعی بگیرند و دکمه‌ی حذف ظاهر شود
          setTimeout(function(){ location.reload(); }, 700);
        } else {
          saveBtn.disabled = false;
          showMsg((data && data.message) ? data.message : 'خطا در ذخیره‌سازی', false);
        }
      })
      .catch(function(){
        saveBtn.disabled = false;
        showMsg('خطا در ارتباط با سرور', false);
      });
  });

  /* حذفِ یک هیرو */
  var CSRF = (form.querySelector('input[name="csrf_token"]') || {}).value || '';
  window.deleteHero = function(id, btn){
    if(!window.confirm('آیا از حذف این هیرو اطمینان دارید؟ این کار قابل بازگشت نیست.')) return;
    btn.disabled = true;
    var fd = new FormData();
    fd.append('id', id);
    fd.append('csrf_token', CSRF);
    fetch('hero-delete.php', { method:'POST', body:fd })
      .then(function(r){ return r.json(); })
      .then(function(data){
        if(data && data.status === 'success'){
          var item = btn.closest('.hm-item');
          if(item){ item.parentNode.removeChild(item); }
          renumber();
        } else {
          btn.disabled = false;
          alert((data && data.message) ? data.message : 'خطا در حذف هیرو');
        }
      })
      .catch(function(){
        btn.disabled = false;
        alert('خطا در ارتباط با سرور');
      });
  };

  /* تم (دارک/لایت) از «داشبورد مدیریت» کنترل می‌شود — کلید مشترک: maxa-theme */
  function applyMaxaTheme(){
    var d = false; try{ d = localStorage.getItem('maxa-theme') === 'dark'; }catch(e){}
    if(d) document.documentElement.setAttribute('data-theme','dark'); else document.documentElement.removeAttribute('data-theme');
  }
  applyMaxaTheme();
  window.addEventListener('storage', function(e){ if(!e || e.key === 'maxa-theme' || e.key === null) applyMaxaTheme(); });
})();
</script>
</body>
</html>

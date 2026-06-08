<?php
/* ============================================================================
 *  جزئیات دوره (بخش کاربری) — آکادمی مکسا
 *  صفحه‌ی فروش/معرفی یک دوره؛ سرفصل‌ها، مدرس و خرید
 * ========================================================================== */
require __DIR__ . '/course-db.php';
require __DIR__ . '/course-ui.php';

$id = (int)($_GET['id'] ?? 0);
$course = load_course_full($pdo, $coursesSchemaReady, $id);

if (!$course) {
  course_html_head('دوره یافت نشد');
  echo '<body>'; course_public_nav('catalog');
  echo '<div class="page" style="text-align:center;padding:80px 20px">'
     . '<div style="width:80px;height:80px;border-radius:22px;display:grid;place-items:center;margin:0 auto 18px;background:var(--primary-08);color:var(--color-primary)">'.cic('search').'</div>'
     . '<h1 style="font-size:22px;font-weight:800">دوره‌ای با این شناسه پیدا نشد</h1>'
     . '<p style="color:var(--color-muted);margin:8px 0 22px">شاید حذف شده باشد یا هنوز منتشر نشده است.</p>'
     . '<a href="courses.php" class="btn btn-primary" target="_top">'.cic('arrow').' بازگشت به فروشگاه دوره‌ها</a>'
     . '</div></body></html>';
  exit;
}

$accent  = course_accent($id);
$isFree  = !empty($course['is_free']) || (int)($course['price'] ?? 0) === 0;
$price   = (float)($course['price'] ?? 0);
$disc    = ($course['discount_price'] ?? null) ? (float)$course['discount_price'] : 0;
$hasDisc = $disc > 0 && $disc < $price;
$eff     = $isFree ? 0 : ($hasDisc ? $disc : $price);
$thumb   = (string)($course['thumbnail'] ?? '');
$learn   = array_filter(array_map('trim', explode("\n", (string)($course['what_you_learn'] ?? ''))));
$reqs    = array_filter(array_map('trim', explode("\n", (string)($course['requirements'] ?? ''))));
$curr    = $course['curriculum'] ?? [];
$lessons = (int)($course['lessons'] ?? 0);
$dur     = (int)($course['duration'] ?? 0);

/* دوره‌های مرتبط */
$related = array_values(array_filter(load_courses($pdo, $coursesSchemaReady, 'published'),
  fn($c) => (int)($c['id'] ?? 0) !== $id));
$related = array_slice($related, 0, 3);

$pageCss = course_card_styles() . <<<CSS
.cd-hero{background:linear-gradient(120deg,#01514f,#013533);color:#fff;position:relative;overflow:hidden}
.cd-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(70% 120% at 90% 0,rgba(0,123,122,.4),transparent 60%)}
.cd-hero-in{max-width:1320px;margin:0 auto;padding:34px 22px 40px;position:relative;z-index:1;max-width:760px;margin-inline-start:max(22px,calc((100% - 1320px)/2 + 22px))}
.crumb{font-size:12.5px;opacity:.85;margin-bottom:14px;display:flex;gap:7px;align-items:center}
.crumb a{opacity:.9}.crumb a:hover{opacity:1;text-decoration:underline}
.cd-cat{display:inline-block;font-size:11.5px;font-weight:700;background:rgba(255,255,255,.16);padding:5px 12px;border-radius:99px;margin-bottom:14px}
.cd-hero h1{font-size:30px;font-weight:800;line-height:1.35;letter-spacing:-.01em;margin-bottom:12px}
.cd-sub{font-size:15px;opacity:.92;line-height:1.8;margin-bottom:18px;max-width:640px}
.cd-facts{display:flex;align-items:center;gap:18px;flex-wrap:wrap;font-size:13px}
.cd-facts span{display:inline-flex;align-items:center;gap:6px;opacity:.95}
.cd-facts .ic{width:16px;height:16px}.cd-facts .star-y{fill:#ffd98a;stroke:#ffd98a}
.cd-rate-b{color:#ffd98a;font-weight:800}

.cd-wrap{max-width:1320px;margin:0 auto;padding:24px 22px 50px;display:grid;grid-template-columns:1fr 380px;gap:30px;align-items:start}
@media(max-width:980px){.cd-wrap{grid-template-columns:1fr}}
.cd-block{margin-bottom:26px}
.cd-block h2{font-size:20px;font-weight:800;margin-bottom:16px;display:flex;align-items:center;gap:9px}
.cd-block h2 .ic{width:21px;height:21px;color:var(--color-primary)}

.learn-grid{display:grid;grid-template-columns:1fr 1fr;gap:13px}
@media(max-width:600px){.learn-grid{grid-template-columns:1fr}}
.learn-item{display:flex;gap:11px;align-items:flex-start;font-size:13.5px;line-height:1.7}
.learn-item .ck{width:23px;height:23px;border-radius:8px;background:rgba(22,163,122,.12);color:var(--success);display:grid;place-items:center;flex-shrink:0;margin-top:1px}
.learn-item .ck .ic{width:14px;height:14px;stroke-width:3}

.acc-sec{border:1px solid var(--color-border);border-radius:14px;margin-bottom:10px;overflow:hidden;background:var(--color-surface)}
.acc-head{display:flex;align-items:center;gap:12px;padding:15px 16px;cursor:pointer;transition:background .18s;background:var(--color-bg)}
.acc-head:hover{background:var(--primary-08)}
.acc-head .chev{margin-inline-start:auto;transition:transform .3s var(--ease);color:var(--color-muted)}
.acc-sec.open .acc-head .chev{transform:rotate(180deg)}
.acc-head .a-title{font-weight:800;font-size:14.5px}
.acc-head .a-meta{font-size:11.5px;color:var(--color-muted);font-weight:600}
.acc-body{max-height:0;overflow:hidden;transition:max-height .35s var(--ease)}
.acc-lesson{display:flex;align-items:center;gap:12px;padding:12px 16px;border-top:1px solid var(--color-border);font-size:13.5px}
.acc-lesson .lt{width:30px;height:30px;border-radius:9px;display:grid;place-items:center;flex-shrink:0;color:#fff}
.acc-lesson .lt .ic{width:16px;height:16px}
.lt-video{background:linear-gradient(135deg,#007b7a,#006665)}.lt-text{background:linear-gradient(135deg,#7c4ddb,#6d3fd1)}
.lt-image{background:linear-gradient(135deg,#f4a61e,#db8d0c)}.lt-pdf{background:linear-gradient(135deg,#e0556b,#c33f54)}.lt-quiz{background:linear-gradient(135deg,#16a37a,#0e7d5c)}
.acc-lesson .l-name{flex:1}
.acc-lesson .l-dur{font-size:11.5px;color:var(--color-muted);font-variant-numeric:tabular-nums}
.acc-lesson .l-prev{font-size:10.5px;font-weight:700;color:var(--color-primary-dark);background:var(--primary-08);padding:3px 9px;border-radius:99px}
.acc-lesson .l-lock{color:var(--color-muted)}.acc-lesson .l-lock .ic{width:15px;height:15px}

.req-list li{display:flex;gap:11px;align-items:flex-start;font-size:13.5px;line-height:1.8;margin-bottom:9px}
.req-list .dot{width:7px;height:7px;border-radius:50%;background:var(--color-primary);margin-top:9px;flex-shrink:0}

.inst-card{display:flex;gap:16px;align-items:center;padding:18px;border:1px solid var(--color-border);border-radius:16px;background:var(--color-surface)}
.inst-av{width:68px;height:68px;border-radius:18px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:24px;flex-shrink:0}
.inst-card .nm{font-weight:800;font-size:16px}
.inst-card .ti{font-size:12.5px;color:var(--color-muted);margin-top:2px}
.inst-card .bio{font-size:13px;line-height:1.7;margin-top:8px;color:var(--color-text)}

/* کارت خرید چسبان */
.buy-rail{position:sticky;top:86px}
.buy-card{border:1px solid var(--color-border);border-radius:20px;overflow:hidden;background:var(--color-surface);box-shadow:var(--shadow-lg)}
.buy-media{height:200px;position:relative;display:grid;place-items:center;color:#fff;cursor:pointer;overflow:hidden}
.buy-media img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.buy-media .play{position:relative;z-index:1;width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,.28);backdrop-filter:blur(4px);display:grid;place-items:center;transition:transform .25s var(--ease)}
.buy-media:hover .play{transform:scale(1.12)}.buy-media .play .ic{width:26px;height:26px;margin-right:-3px}
.buy-media .ov{position:absolute;inset:0;background:linear-gradient(0deg,rgba(0,0,0,.35),transparent)}
.buy-pad{padding:20px}
.buy-price{display:flex;align-items:baseline;gap:10px;margin-bottom:4px}
.buy-price b{font-size:30px;font-weight:800}
.buy-price del{font-size:16px;color:var(--color-muted)}
.buy-off{display:inline-block;font-size:12px;font-weight:700;color:var(--danger);background:rgba(224,85,107,.12);padding:3px 10px;border-radius:99px;margin-bottom:14px}
.buy-actions{display:flex;flex-direction:column;gap:10px;margin:16px 0}
.includes li{display:flex;align-items:center;gap:11px;font-size:13px;padding:8px 0;border-top:1px solid var(--color-border);color:var(--color-text)}
.includes li .ic{width:18px;height:18px;color:var(--color-primary);flex-shrink:0}
.guarantee{text-align:center;font-size:12px;color:var(--color-muted);margin-top:12px}
.rel-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:18px}
CSS;

course_html_head($course['title'] ?? 'دوره', $pageCss);
?>
<body>
<?php course_public_nav('catalog'); ?>

<!-- قهرمان -->
<section class="cd-hero">
  <div class="cd-hero-in">
    <div class="crumb"><a href="courses.php" target="_top">دوره‌ها</a><?= cic('chevron','') ?><span><?= e($course['category'] ?? 'عمومی') ?></span></div>
    <span class="cd-cat"><?= e($course['category'] ?? 'عمومی') ?> · <?= level_label((string)($course['level'] ?? 'all')) ?></span>
    <h1><?= e($course['title'] ?? '') ?></h1>
    <?php if (!empty($course['subtitle'])): ?><p class="cd-sub"><?= e($course['subtitle']) ?></p><?php endif; ?>
    <div class="cd-facts">
      <span><?= cic('star','star-y') ?><b class="cd-rate-b"><?= fa_digits(number_format((float)($course['rating'] ?? 0),1)) ?></b></span>
      <span><?= cic('users') ?><?= fa_digits(number_format((int)($course['students'] ?? 0))) ?> فراگیر</span>
      <span><?= cic('video') ?><?= fa_digits($lessons) ?> درس</span>
      <span><?= cic('clock') ?><?= fmt_duration($dur) ?></span>
      <span><?= cic('grad') ?><?= e($course['instructor'] ?? 'مدرس مکسا') ?></span>
    </div>
  </div>
</section>

<div class="cd-wrap">
  <!-- ستون محتوا -->
  <div class="cd-main">
    <?php if ($learn): ?>
    <div class="cd-block card card-pad">
      <h2><?= cic('target') ?> در این دوره چه می‌آموزید</h2>
      <div class="learn-grid">
        <?php foreach ($learn as $l): ?>
          <div class="learn-item"><span class="ck"><?= cic('check') ?></span><span><?= e($l) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($course['description'])): ?>
    <div class="cd-block">
      <h2><?= cic('book') ?> درباره‌ی این دوره</h2>
      <div style="font-size:14px;line-height:2;white-space:pre-line;color:var(--color-text)"><?= e($course['description']) ?></div>
    </div>
    <?php endif; ?>

    <!-- سرفصل‌ها -->
    <div class="cd-block">
      <h2><?= cic('list') ?> سرفصل‌های دوره</h2>
      <div style="font-size:12.5px;color:var(--color-muted);margin:-8px 0 14px;font-weight:600">
        <?= fa_digits(count($curr)) ?> فصل · <?= fa_digits($lessons) ?> درس · <?= fmt_duration($dur) ?>
      </div>
      <?php if (!$curr): ?>
        <div style="padding:24px;text-align:center;color:var(--color-muted);border:2px dashed var(--color-border);border-radius:14px">سرفصل‌های این دوره به‌زودی اضافه می‌شود.</div>
      <?php else: foreach ($curr as $si => $sec):
        $secLessons = $sec['lessons'] ?? [];
        $secDur = 0; foreach ($secLessons as $l) $secDur += (int)($l['duration'] ?? 0);
      ?>
        <div class="acc-sec<?= $si===0?' open':'' ?>">
          <div class="acc-head">
            <span class="a-title"><?= e($sec['title'] ?? 'فصل') ?></span>
            <span class="a-meta"><?= fa_digits(count($secLessons)) ?> درس · <?= fmt_duration($secDur) ?></span>
            <span class="chev"><?= cic('chevron') ?></span>
          </div>
          <div class="acc-body">
            <?php foreach ($secLessons as $l):
              $type = (string)($l['type'] ?? 'video');
              $isPrev = !empty($l['is_preview']);
            ?>
              <div class="acc-lesson">
                <span class="lt lt-<?= e($type) ?>"><?= cic($type==='video'?'play':$type) ?></span>
                <span class="l-name"><?= e($l['title'] ?? 'درس') ?></span>
                <?php if ($isPrev): ?><a class="l-prev" href="course-learn.php?id=<?= $id ?>&preview=1" target="_top">پیش‌نمایش</a>
                <?php else: ?><span class="l-lock"><?= cic('lock') ?></span><?php endif; ?>
                <?php if ((int)($l['duration'] ?? 0)>0): ?><span class="l-dur"><?= fa_digits((int)$l['duration']) ?>:۰۰</span><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <?php if ($reqs): ?>
    <div class="cd-block">
      <h2><?= cic('check') ?> پیش‌نیازها</h2>
      <ul class="req-list"><?php foreach ($reqs as $r): ?><li><span class="dot"></span><span><?= e($r) ?></span></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <!-- مدرس -->
    <div class="cd-block">
      <h2><?= cic('grad') ?> مدرس دوره</h2>
      <div class="inst-card">
        <div class="inst-av" style="background:linear-gradient(135deg,<?= $accent ?>,<?= $accent ?>aa)"><?= e(mb_substr(trim((string)($course['instructor'] ?? 'م')),0,1)) ?></div>
        <div>
          <div class="nm"><?= e($course['instructor'] ?? 'مدرس مکسا') ?></div>
          <?php if (!empty($course['instructor_bio'])): ?><div class="ti"><?= e($course['instructor_bio']) ?></div><?php endif; ?>
          <div class="bio">مدرس باتجربه‌ی آکادمی مکسا با تمرکز بر آموزش کاربردی و پروژه‌محور.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- کارت خرید -->
  <aside class="buy-rail">
    <div class="buy-card">
      <div class="buy-media" style="background:linear-gradient(135deg,<?= $accent ?>,<?= $accent ?>bb)" id="promoBtn">
        <?php if ($thumb): ?><img src="<?= e($thumb) ?>" alt=""><div class="ov"></div><?php endif; ?>
        <span class="play"><?= cic('play') ?></span>
      </div>
      <div class="buy-pad">
        <div class="buy-price">
          <?php if ($isFree): ?><b style="color:var(--violet)">رایگان</b>
          <?php elseif ($hasDisc): ?><b><?= fa_digits(number_format($disc)) ?> تومان</b><del><?= fa_digits(number_format($price)) ?></del>
          <?php else: ?><b><?= fa_digits(number_format($price)) ?> تومان</b><?php endif; ?>
        </div>
        <?php if ($hasDisc): $off=round((1-$disc/$price)*100); ?><span class="buy-off"><?= fa_digits($off) ?>٪ تخفیف ویژه</span><?php endif; ?>

        <div class="buy-actions">
          <?php if ($isFree): ?>
            <a href="course-learn.php?id=<?= $id ?>" class="btn btn-primary btn-block btn-lg" target="_top"><?= cic('play') ?> شروع رایگان دوره</a>
          <?php else: ?>
            <button class="btn btn-primary btn-block btn-lg" id="buyNow"><?= cic('cart') ?> خرید و ثبت‌نام</button>
            <button class="btn btn-ghost btn-block" id="addCart"><?= cic('plus') ?> افزودن به سبد خرید</button>
          <?php endif; ?>
        </div>

        <div class="includes">
          <div style="font-weight:800;font-size:13.5px;padding-bottom:6px">این دوره شامل:</div>
          <li><?= cic('video') ?><?= fmt_duration($dur) ?> ویدئوی آموزشی</li>
          <li><?= cic('list') ?><?= fa_digits($lessons) ?> درس در <?= fa_digits(count($curr)) ?> فصل</li>
          <li><?= cic('infinity') ?>دسترسی مادام‌العمر</li>
          <li><?= cic('pdf') ?>جزوه و منابع قابل دانلود</li>
          <li><?= cic('award') ?>گواهی پایان دوره</li>
          <li><?= cic('globe') ?>دسترسی روی موبایل و دسکتاپ</li>
        </div>
        <div class="guarantee"><?= cic('check') ?> ضمانت بازگشت وجه تا ۷ روز</div>
      </div>
    </div>
  </aside>
</div>

<?php if ($related): ?>
<div class="page" style="padding-top:0">
  <h2 style="font-size:20px;font-weight:800;margin-bottom:18px">دوره‌های مرتبط</h2>
  <div class="rel-grid"><?php foreach ($related as $rc) course_card($rc); ?></div>
</div>
<?php endif; ?>

<!-- مودال ویدئوی معرفی -->
<div class="modal-ov" id="promoModal" style="position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:120;display:none;place-items:center;padding:20px">
  <div style="max-width:840px;width:100%;background:#000;border-radius:18px;overflow:hidden;position:relative;aspect-ratio:16/9">
    <button id="promoClose" style="position:absolute;top:12px;left:12px;z-index:2;width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.15);color:#fff;display:grid;place-items:center"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    <div id="promoSlot" style="width:100%;height:100%;display:grid;place-items:center;color:#fff;text-align:center;padding:20px">
      <div><?= cic('play','') ?><p style="margin-top:10px;opacity:.8">ویدئوی معرفی این دوره</p></div>
    </div>
  </div>
</div>

<div class="toast" id="toast"><div class="ti"><?= cic('check') ?></div><span id="toastMsg"></span></div>
<?php course_theme_fab(); ?>

<script>
(function(){
  "use strict";
  var COURSE = {
    id: <?= $id ?>,
    title: <?= json_encode($course['title'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
    price: <?= (int)$eff ?>,
    instructor: <?= json_encode($course['instructor'] ?? '', JSON_UNESCAPED_UNICODE) ?>
  };
  var promoUrl = <?= json_encode((string)($course['promo_video'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;
  var $=function(id){return document.getElementById(id);};

  function toast(msg){ var t=$('toast'); $('toastMsg').textContent=msg; t.classList.add('show'); clearTimeout(t._t); t._t=setTimeout(function(){t.classList.remove('show');},2800); }

  // آکاردئون سرفصل‌ها
  document.querySelectorAll('.acc-head').forEach(function(h){
    h.addEventListener('click', function(){
      var sec=h.closest('.acc-sec'), body=h.nextElementSibling;
      var open=sec.classList.toggle('open');
      body.style.maxHeight = open ? (body.scrollHeight+'px') : '0px';
    });
  });
  document.querySelectorAll('.acc-sec.open .acc-body').forEach(function(b){ b.style.maxHeight=b.scrollHeight+'px'; });

  // سبد خرید
  function getCart(){ try{ return JSON.parse(localStorage.getItem('maxa-cart')||'[]'); }catch(e){ return []; } }
  function setCart(c){ try{ localStorage.setItem('maxa-cart', JSON.stringify(c)); }catch(e){} }
  function inCart(){ return getCart().some(function(x){ return x.id===COURSE.id; }); }

  var addBtn=$('addCart');
  if(addBtn){
    if(inCart()) addBtn.innerHTML='در سبد خرید ✓';
    addBtn.addEventListener('click', function(){
      var c=getCart();
      if(!inCart()){ c.push(COURSE); setCart(c); toast('دوره به سبد خرید اضافه شد.'); addBtn.innerHTML='در سبد خرید ✓'; }
      else toast('این دوره از قبل در سبد شماست.');
      var el=document.getElementById('cartCount'); if(el){ el.style.display='grid'; el.textContent=String(getCart().length).replace(/\d/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[d];}); }
    });
  }
  var buyBtn=$('buyNow');
  if(buyBtn){
    buyBtn.addEventListener('click', function(){
      var c=getCart(); if(!inCart()){ c.push(COURSE); setCart(c); }
      window.top.location.href='course-checkout.php?id='+COURSE.id;
    });
  }

  // ویدئوی معرفی
  var modal=$('promoModal');
  $('promoBtn').addEventListener('click', function(){
    if(promoUrl){
      var slot=$('promoSlot');
      if(/<iframe/i.test(promoUrl)) slot.innerHTML=promoUrl;
      else if(/youtube|youtu\.be|aparat/i.test(promoUrl)) slot.innerHTML='<iframe src="'+promoUrl+'" style="width:100%;height:100%;border:0" allowfullscreen></iframe>';
      else slot.innerHTML='<video src="'+promoUrl+'" controls autoplay style="width:100%;height:100%"></video>';
    }
    modal.style.display='grid';
  });
  function closeP(){ modal.style.display='none'; }
  $('promoClose').addEventListener('click', closeP);
  modal.addEventListener('click', function(e){ if(e.target===modal) closeP(); });
})();
</script>
</body>
</html>

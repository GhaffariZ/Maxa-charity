<?php
/* ============================================================================
 *  فروشگاه دوره‌ها (بخش کاربری) — آکادمی مکسا
 *  مرور، جست‌وجو، فیلتر دسته‌بندی/سطح و مرتب‌سازی دوره‌ها
 * ========================================================================== */
require __DIR__ . '/course-db.php';
require __DIR__ . '/course-ui.php';

// ایزولاسیون: اگر ?branch=<slug> آمد، فقط دوره‌های همان شعبه؛ وگرنه همه‌ی دوره‌های منتشرشده.
$__branchScope = null;
if ($pdo && !empty($_GET['branch'])) {
  $__bslug = preg_replace('/[^a-z0-9-]/', '', strtolower((string)$_GET['branch']));
  if ($__bslug !== '') {
    try {
      $bs = $pdo->prepare("SELECT id FROM branches WHERE slug = ? AND status='active' LIMIT 1");
      $bs->execute([$__bslug]);
      $br = $bs->fetch();
      if ($br) { $__branchScope = (int)$br['id']; }
    } catch (Throwable $e) { /* بی‌اثر */ }
  }
}
$courses = load_courses($pdo, $coursesSchemaReady, 'published', $__branchScope);

/* دسته‌بندی‌های موجود */
$cats = [];
foreach ($courses as $c) { $cat = trim((string)($c['category'] ?? '')); if ($cat !== '') $cats[$cat] = ($cats[$cat] ?? 0) + 1; }
arsort($cats);

$featured = $courses[0] ?? null;

$pageCss = course_card_styles() . <<<CSS
.hero{position:relative;overflow:hidden;border-radius:24px;background:linear-gradient(120deg,#008786 0%,var(--color-primary-dark) 58%,#004a49 100%);color:#fff;padding:46px 40px;margin-bottom:26px}
.hero::before{content:'';position:absolute;inset:0;background:radial-gradient(60% 130% at 88% -10%,rgba(255,255,255,.18),transparent 55%)}
.hero-in{position:relative;z-index:1;max-width:640px}
.hero h1{font-size:32px;font-weight:800;letter-spacing:-.02em;line-height:1.3;margin-bottom:12px}
.hero p{font-size:15px;opacity:.92;line-height:1.8;margin-bottom:22px}

/* نوار جست‌وجوی هدر */
.hero-search{
  display:flex;
  align-items:center;
  gap:10px;
  background:#fff;
  border-radius:16px;
  padding:6px 14px 6px 6px;
  max-width:520px;
  box-shadow:0 16px 36px -12px rgba(0,0,0,.35);
  border:2px solid transparent;
  transition:border-color .25s ease, box-shadow .25s ease;
}
.hero-search:focus-within{
  border-color:var(--color-secondary);
  box-shadow:0 18px 40px -10px rgba(0,0,0,.45);
}
.hero-search-ic{
  width:20px;
  height:20px;
  color:#94a3b8;
  display:flex;
  align-items:center;
  justify-content:center;
  flex-shrink:0;
}
.hero-search-ic svg{width:20px;height:20px;display:block}
.hero-search input{
  flex:1;
  border:none;
  outline:none;
  font-family:inherit;
  font-size:14.5px;
  font-weight:500;
  color:#1e293b;
  background:transparent;
  padding:6px 4px;
}
.hero-search input::placeholder{
  color:#94a3b8;
}
.hero-search .btn-hero-search{
  flex-shrink:0;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:7px;
  padding:10px 22px;
  border-radius:12px;
  background:linear-gradient(135deg, #faa61a 0%, #f59e0b 100%) !important;
  color:#3a2000 !important;
  font-weight:800;
  font-size:13.5px;
  border:none !important;
  cursor:pointer;
  box-shadow:0 8px 20px -6px rgba(245, 158, 11, 0.75);
  transition:all .2s ease;
}
.hero-search .btn-hero-search:hover{
  transform:translateY(-1px);
  box-shadow:0 12px 24px -6px rgba(245, 158, 11, 0.9);
  background:linear-gradient(135deg, #fbbf24 0%, #d97706 100%) !important;
}
.hero-search .btn-hero-search:active{
  transform:scale(0.97);
}
.hero-search .btn-hero-search svg{
  width:16px;
  height:16px;
}

.hero-stats{display:flex;gap:26px;margin-top:24px}
.hero-stats div{font-size:13px;opacity:.9}.hero-stats b{display:block;font-size:22px;font-weight:800}

/* نوار چیپ‌های دسته‌بندی */
.cat-bar{
  display:flex;
  gap:10px;
  overflow-x:auto;
  padding:4px 2px 14px;
  margin-bottom:20px;
  -webkit-overflow-scrolling:touch;
  scrollbar-width:none;
}
.cat-bar::-webkit-scrollbar{display:none}
.cat-chip{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:10px 18px;
  border-radius:99px;
  border:1.5px solid var(--color-border) !important;
  background:var(--color-surface) !important;
  font-weight:700;
  font-size:13px;
  color:#475569 !important;
  white-space:nowrap;
  transition:all .2s cubic-bezier(.2,.8,.2,1);
  cursor:pointer;
  user-select:none;
}
.cat-chip:hover{
  border-color:var(--color-primary-light) !important;
  color:var(--color-primary-dark) !important;
  transform:translateY(-2px);
}
.cat-chip.on{
  background:linear-gradient(135deg, var(--color-primary), var(--color-primary-dark)) !important;
  color:#ffffff !important;
  border-color:transparent !important;
  box-shadow:0 8px 22px -6px rgba(0, 143, 138, 0.65);
}
.cat-chip .n{
  font-size:11px;
  opacity:.8;
  font-variant-numeric:tabular-nums;
  pointer-events:none;
}
.cat-chip.on .n{
  color:#ffffff;
  opacity:.9;
}

.results-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.results-head h2{font-size:19px;font-weight:800}
.results-head .cnt{font-size:12.5px;color:var(--color-muted);font-weight:600;margin-top:2px}
.sortbox{display:flex;align-items:center;gap:8px}
.sortbox .sel{width:auto;min-width:150px;padding:9px 38px 9px 14px;background-position:left 12px center}

.cgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(275px,1fr));gap:22px}
.empty{grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--color-muted)}
.empty .ei{width:64px;height:64px;border-radius:18px;display:grid;place-items:center;margin:0 auto 14px;background:var(--primary-08);color:var(--color-primary)}
.empty .ei .ic{width:30px;height:30px}
.hide{display:none!important}
@media(max-width:760px){
  .hero{padding:32px 20px}
  .hero h1{font-size:24px}
  .cgrid{grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px}
}
CSS;

$totalStudents = 0; foreach ($courses as $c) { $totalStudents += (int)($c['students'] ?? 0); }

course_site_head('دوره‌های آموزشی', $pageCss);
?>

<div class="page">
  <!-- قهرمان -->
  <section class="hero reveal">
    <div class="hero-in">
      <h1>هر مهارتی که می‌خواهی، همین‌جا یاد بگیر</h1>
      <p>به جمع هزاران فراگیر آکادمی مکسا بپیوند؛ دوره‌های باکیفیت در برنامه‌نویسی، طراحی، کسب‌وکار و توسعه‌ی فردی.</p>
      <form class="hero-search" onsubmit="event.preventDefault(); document.getElementById('heroSearchBtn').click();">
        <span class="hero-search-ic" aria-hidden="true"><?= cic('search') ?></span>
        <input id="heroSearch" type="search" placeholder="دنبال چه چیزی می‌گردی؟ مثلاً «طراحی وب»" autocomplete="off">
        <button type="button" class="btn-hero-search" id="heroSearchBtn">
          <?= cic('search') ?>
          <span>جست‌وجو</span>
        </button>
      </form>
      <div class="hero-stats">
        <div><b><?= fa_digits(number_format(count($courses))) ?>+</b> دوره</div>
        <div><b><?= fa_digits(number_format($totalStudents)) ?>+</b> فراگیر</div>
        <div><b><?= fa_digits(count($cats)) ?></b> دسته‌بندی</div>
      </div>
    </div>
  </section>

  <!-- دسته‌بندی‌ها -->
  <div class="cat-bar" id="cats">
    <button type="button" class="cat-chip on" data-cat="all">همه دوره‌ها</button>
    <?php foreach ($cats as $cat => $n): ?>
      <button type="button" class="cat-chip" data-cat="<?= e(trim((string)$cat)) ?>"><?= e($cat) ?><span class="n">(<?= fa_digits($n) ?>)</span></button>
    <?php endforeach; ?>
  </div>

  <!-- نتایج -->
  <div class="results-head">
    <div>
      <h2 id="resTitle">همه‌ی دوره‌ها</h2>
      <div class="cnt"><span id="resCount"><?= fa_digits(count($courses)) ?></span> دوره یافت شد</div>
    </div>
    <div class="sortbox">
      <label class="lbl" style="margin:0">مرتب‌سازی:</label>
      <select class="sel" id="sortSel">
        <option value="pop">محبوب‌ترین</option>
        <option value="new">جدیدترین</option>
        <option value="rate">بیشترین امتیاز</option>
        <option value="cheap">ارزان‌ترین</option>
        <option value="exp">گران‌ترین</option>
      </select>
    </div>
  </div>

  <div class="cgrid" id="grid">
    <?php foreach ($courses as $c):
      $price = (float)($c['price'] ?? 0);
      $disc = ($c['discount_price'] ?? null) ? (float)$c['discount_price'] : 0;
      $eff = (!empty($c['is_free']) || $price==0) ? 0 : ($disc>0?$disc:$price);
    ?>
    <div class="grid-item"
         data-cat="<?= e(trim((string)($c['category'] ?? ''))) ?>"
         data-search="<?= e(mb_strtolower(trim(($c['title']??'').' '.($c['instructor']??'').' '.($c['subtitle']??'').' '.($c['category']??'')))) ?>"
         data-students="<?= (int)($c['students'] ?? 0) ?>"
         data-rating="<?= (float)($c['rating'] ?? 0) ?>"
         data-price="<?= (int)$eff ?>"
         data-date="<?= e($c['created_at'] ?? '') ?>">
      <?php course_card($c); ?>
    </div>
    <?php endforeach; ?>
    <div class="empty hide" id="emptyState">
      <div class="ei"><?= cic('search') ?></div>
      <b style="font-size:16px">دوره‌ای پیدا نشد</b>
      <p style="margin-top:6px">جست‌وجو یا دسته‌بندی دیگری را امتحان کنید.</p>
    </div>
  </div>
</div>

<script>
(function(){
  "use strict";
  var $ = function(id){ return document.getElementById(id); };
  var grid = $('grid');
  if(!grid) return;
  var items = [].slice.call(grid.querySelectorAll('.grid-item'));
  var curCat = 'all', term = '';

  function apply(){
    var shown = 0;
    var normCurCat = (curCat || 'all').trim();
    var normTerm = (term || '').trim().toLowerCase();

    items.forEach(function(it){
      var itemCat = (it.getAttribute('data-cat') || '').trim();
      var itemSearch = (it.getAttribute('data-search') || '').trim().toLowerCase();

      var okC = normCurCat === 'all' || itemCat === normCurCat;
      var okT = !normTerm || itemSearch.indexOf(normTerm) > -1;
      var vis = okC && okT;

      it.classList.toggle('hide', !vis);
      if(vis) shown++;
    });

    var countEl = $('resCount');
    if(countEl){
      countEl.textContent = String(shown).replace(/\d/g, function(d){ return '۰۱۲۳۴۵۶۷۸۹'[d]; });
    }
    var emptyEl = $('emptyState');
    if(emptyEl){
      emptyEl.classList.toggle('hide', shown > 0);
    }
  }

  function sortBy(mode){
    var arr = items.slice();
    arr.sort(function(a,b){
      var f = function(el,k){ return parseFloat(el.getAttribute(k)) || 0; };
      if(mode === 'pop') return f(b, 'data-students') - f(a, 'data-students');
      if(mode === 'rate') return f(b, 'data-rating') - f(a, 'data-rating');
      if(mode === 'cheap') return f(a, 'data-price') - f(b, 'data-price');
      if(mode === 'exp') return f(b, 'data-price') - f(a, 'data-price');
      if(mode === 'new') return (b.getAttribute('data-date') || '').localeCompare(a.getAttribute('data-date') || '');
      return 0;
    });
    var emptyState = $('emptyState');
    arr.forEach(function(el){
      if(emptyState) grid.insertBefore(el, emptyState);
      else grid.appendChild(el);
    });
  }

  var searchInp = $('heroSearch');
  if(searchInp){
    searchInp.addEventListener('input', function(){
      term = this.value;
      apply();
    });
  }

  var searchBtn = $('heroSearchBtn');
  if(searchBtn){
    searchBtn.addEventListener('click', function(e){
      e.preventDefault();
      var head = document.querySelector('.results-head');
      if(head) head.scrollIntoView({behavior:'smooth'});
    });
  }

  var catChips = document.querySelectorAll('.cat-chip');
  catChips.forEach(function(btn){
    btn.addEventListener('click', function(e){
      e.preventDefault();
      catChips.forEach(function(x){ x.classList.remove('on'); });
      btn.classList.add('on');
      curCat = (btn.getAttribute('data-cat') || 'all').trim();
      var titleEl = $('resTitle');
      if(titleEl){
        titleEl.textContent = curCat === 'all' ? 'همه‌ی دوره‌ها' : ('دوره‌های ' + curCat);
      }
      apply();
    });
  });

  var sortSel = $('sortSel');
  if(sortSel){
    sortSel.addEventListener('change', function(){ sortBy(this.value); });
  }
})();
</script>
<?php course_site_footer(); ?>

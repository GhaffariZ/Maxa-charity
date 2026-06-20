<?php
/* ============================================================================
 *  مدیریت دوره‌ها — آکادمی مکسا (پنل مدیر / مدرس)
 *  مشاهده، فیلتر، انتشار/پیش‌نویس و حذف دوره‌ها
 * ========================================================================== */
require __DIR__ . '/course-db.php';
require __DIR__ . '/course-ui.php';

/* ---------- اکشن‌ها (انتشار/پیش‌نویس/حذف) ---------- */
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && $coursesSchemaReady) {
  $id = (int)($_POST['id'] ?? 0);
  $act = (string)($_POST['do'] ?? '');
  try {
    if ($id && $act === 'delete') {
      q($pdo, "DELETE FROM course_lessons WHERE course_id=?", [$id]);
      q($pdo, "DELETE FROM course_sections WHERE course_id=?", [$id]);
      q($pdo, "DELETE FROM courses WHERE id=?", [$id]);
      $flash = 'دوره حذف شد.';
    } elseif ($id && $act === 'publish') {
      q($pdo, "UPDATE courses SET status='published' WHERE id=?", [$id]); $flash = 'دوره منتشر شد.';
    } elseif ($id && $act === 'draft') {
      q($pdo, "UPDATE courses SET status='draft' WHERE id=?", [$id]); $flash = 'دوره به پیش‌نویس منتقل شد.';
    }
  } catch (Throwable $e) { $flash = 'خطا: ' . $e->getMessage(); }
}

$courses = load_courses($pdo, $coursesSchemaReady);

/* ---------- آمار ---------- */
$cTotal = count($courses);
$cPublished = 0; $cDraft = 0; $cStudents = 0; $cRevenue = 0.0;
foreach ($courses as $c) {
  if (($c['status'] ?? '') === 'published') $cPublished++; else $cDraft++;
  $cStudents += (int)($c['students'] ?? 0);
  $price = ($c['discount_price'] ?? null) ? (float)$c['discount_price'] : (float)($c['price'] ?? 0);
  $cRevenue += $price * (int)($c['students'] ?? 0);
}

$pageCss = <<<CSS
.stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px}
@media(max-width:900px){.stat-row{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.stat-row{grid-template-columns:1fr}}
.mstat{position:relative;overflow:hidden;padding:18px}
.mstat .top{display:flex;align-items:center;justify-content:space-between}
.mstat .mi{width:44px;height:44px;border-radius:13px;display:grid;place-items:center;background:var(--accent-bg);color:var(--accent)}
.mstat .mi .ic{width:22px;height:22px}
.mstat .v{font-size:25px;font-weight:800;margin-top:14px;font-variant-numeric:tabular-nums}
.mstat .l{font-size:12.5px;color:var(--color-muted);font-weight:600;margin-top:3px}

.toolbar{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.search-box{display:flex;align-items:center;gap:9px;background:var(--color-surface);border:1.5px solid var(--color-border);border-radius:13px;padding:0 14px;height:44px;flex:1;min-width:220px;transition:border-color .2s,box-shadow .2s}
.search-box:focus-within{border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08)}
.search-box .ic{width:18px;height:18px;color:var(--color-muted)}
.search-box input{border:none;background:none;outline:none;flex:1;font-size:13.5px;color:var(--color-text)}
.fbtns{display:flex;gap:6px;background:var(--color-surface);border:1px solid var(--color-border);border-radius:13px;padding:4px}
.fbtns button{padding:9px 15px;border-radius:10px;font-weight:700;font-size:12.5px;color:var(--color-muted);transition:background .18s,color .18s}
.fbtns button.on{background:var(--primary-08);color:var(--color-primary-dark)}

.ctable{width:100%;border-collapse:collapse;font-size:13px}
.ctable th{text-align:right;font-size:11.5px;font-weight:700;color:var(--color-muted);padding:0 14px 14px;border-bottom:1px solid var(--color-border);white-space:nowrap}
.ctable th:last-child,.ctable td:last-child{text-align:left}
.ctable td{padding:13px 14px;border-bottom:1px solid var(--color-border);vertical-align:middle}
.ctable tbody tr{transition:background .18s}
.ctable tbody tr:hover{background:var(--color-bg)}
.ctable tbody tr:last-child td{border-bottom:none}
.c-cell{display:flex;align-items:center;gap:12px;min-width:240px}
.c-thumb{width:64px;height:42px;border-radius:10px;flex-shrink:0;display:grid;place-items:center;color:#fff;overflow:hidden;position:relative}
.c-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.c-thumb .ic{width:18px;height:18px;opacity:.85}
.c-name{font-weight:700;font-size:13.5px;line-height:1.4}
.c-meta{font-size:11.5px;color:var(--color-muted);margin-top:2px}
.c-price{font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}
.c-price small{font-weight:600;color:var(--color-muted);font-size:10px}
.c-old{font-size:11px;color:var(--color-muted);text-decoration:line-through}
.rate{display:inline-flex;align-items:center;gap:4px;font-weight:700;color:#b9760a}
.rate .ic{width:14px;height:14px;fill:var(--warning);stroke:var(--warning)}
.row-actions{display:flex;align-items:center;gap:3px;justify-content:flex-end}
.menu-wrap{position:relative}
.menu-pop{position:absolute;top:calc(100% + 6px);left:0;min-width:180px;background:var(--color-surface);border:1px solid var(--color-border);border-radius:14px;box-shadow:var(--shadow-lg);padding:6px;z-index:30;
  opacity:0;visibility:hidden;transform:translateY(-8px) scale(.97);transform-origin:top left;transition:opacity .18s,transform .2s var(--ease),visibility .18s}
.menu-wrap.open .menu-pop{opacity:1;visibility:visible;transform:none}
.menu-pop button,.menu-pop a{width:100%;display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;font-size:12.5px;font-weight:600;color:#5b6469;transition:background .15s,color .15s}
.menu-pop button:hover,.menu-pop a:hover{background:var(--primary-08);color:var(--color-primary-dark)}
.menu-pop .danger:hover{background:rgba(224,85,107,.12);color:var(--danger)}
.menu-pop .ic{width:16px;height:16px}
.menu-sep{height:1px;background:var(--color-border);margin:4px 6px}
.empty{text-align:center;padding:54px 20px;color:var(--color-muted)}
.empty .ei{width:64px;height:64px;border-radius:18px;display:grid;place-items:center;margin:0 auto 14px;background:var(--primary-08);color:var(--color-primary)}
.empty .ei .ic{width:30px;height:30px}
.hide{display:none!important}

/* مودال تأیید */
.modal-ov{position:fixed;inset:0;background:rgba(16,40,40,.45);backdrop-filter:blur(2px);z-index:120;display:none;place-items:center;padding:20px}
.modal-ov.show{display:grid}
.modal{background:var(--color-surface);border-radius:20px;box-shadow:var(--shadow-lg);max-width:400px;width:100%;padding:24px;text-align:center;animation:fadeUp .35s var(--ease)}
.modal .mic{width:58px;height:58px;border-radius:16px;display:grid;place-items:center;margin:0 auto 14px;background:rgba(224,85,107,.12);color:var(--danger)}
.modal .mic .ic{width:28px;height:28px}
.modal h3{font-size:17px;font-weight:800;margin-bottom:6px}
.modal p{font-size:13px;color:var(--color-muted);margin-bottom:20px}
.modal .row{display:flex;gap:10px}
CSS;

course_html_head('مدیریت دوره‌ها', $pageCss);
?>
<body>
<div class="page">
  <!-- سرصفحه -->
  <div class="page-head">
    <div class="ph-icon"><?= cic('list') ?></div>
    <div>
      <h1>مدیریت دوره‌ها</h1>
      <div class="ph-sub">دوره‌ها را ویرایش، منتشر یا حذف کنید و عملکردشان را زیر نظر بگیرید.</div>
    </div>
    <div class="ph-spacer"></div>
    <a href="Courses-create.php" class="btn btn-primary"><?= cic('plus') ?> ساخت دوره جدید</a>
  </div>

  <?php if (!$pdo || !$coursesSchemaReady): ?>
    <div class="card card-pad" style="margin-bottom:18px;border-color:var(--warning);background:var(--secondary-12)">
      <b style="color:#b9760a"><?= cic('quiz') ?> حالت نمایشی:</b>
      <span style="font-size:13px"> اتصال دیتابیس برقرار نیست؛ داده‌های زیر نمونه‌اند و عملیات‌ها ذخیره نمی‌شوند.</span>
    </div>
  <?php endif; ?>

  <!-- آمار -->
  <div class="stat-row">
    <div class="card mstat" style="--accent:#007b7a;--accent-bg:var(--primary-08)">
      <div class="top"><div class="mi"><?= cic('book') ?></div></div>
      <div class="v"><?= fa_digits($cTotal) ?></div><div class="l">کل دوره‌ها</div>
    </div>
    <div class="card mstat" style="--accent:#16a37a;--accent-bg:rgba(22,163,122,.12)">
      <div class="top"><div class="mi"><?= cic('rocket') ?></div></div>
      <div class="v"><?= fa_digits($cPublished) ?></div><div class="l">منتشرشده</div>
    </div>
    <div class="card mstat" style="--accent:#7c4ddb;--accent-bg:var(--violet-12)">
      <div class="top"><div class="mi"><?= cic('users') ?></div></div>
      <div class="v"><?= fa_digits(number_format($cStudents)) ?></div><div class="l">کل فراگیران</div>
    </div>
    <div class="card mstat" style="--accent:#db8d0c;--accent-bg:var(--secondary-12)">
      <div class="top"><div class="mi"><?= cic('wallet') ?></div></div>
      <div class="v"><?= fa_digits(number_format(round($cRevenue/1e6))) ?><span style="font-size:13px;font-weight:600;color:var(--color-muted)"> م تومان</span></div>
      <div class="l">درآمد تخمینی</div>
    </div>
  </div>

  <!-- ابزار -->
  <div class="toolbar">
    <div class="search-box"><?= cic('search') ?><input id="searchInput" placeholder="جست‌وجوی دوره بر اساس عنوان یا مدرس…"></div>
    <div class="fbtns" id="statusFilter">
      <button class="on" data-f="all">همه</button>
      <button data-f="published">منتشرشده</button>
      <button data-f="draft">پیش‌نویس</button>
    </div>
  </div>

  <!-- جدول -->
  <div class="card" style="padding:22px 22px 14px">
    <div style="overflow-x:auto">
      <table class="ctable" id="coursesTable">
        <thead>
          <tr><th>دوره</th><th>دسته‌بندی</th><th>قیمت</th><th>فراگیران</th><th>امتیاز</th><th>وضعیت</th><th></th></tr>
        </thead>
        <tbody id="tbody">
        <?php foreach ($courses as $c):
          $id = (int)($c['id'] ?? 0);
          $accent = course_accent($id);
          $isPub = ($c['status'] ?? '') === 'published';
          $isFree = !empty($c['is_free']) || (int)($c['price'] ?? 0) === 0;
          $price = (float)($c['price'] ?? 0);
          $disc = ($c['discount_price'] ?? null) ? (float)$c['discount_price'] : 0;
          $thumb = (string)($c['thumbnail'] ?? '');
        ?>
          <tr data-status="<?= $isPub?'published':'draft' ?>" data-search="<?= e(mb_strtolower(($c['title']??'').' '.($c['instructor']??''))) ?>">
            <td>
              <div class="c-cell">
                <div class="c-thumb" style="background:linear-gradient(135deg,<?= $accent ?>,<?= $accent ?>cc)">
                  <?php if ($thumb): ?><img src="<?= e($thumb) ?>" alt=""><?php else: ?><?= cic('book') ?><?php endif; ?>
                </div>
                <div>
                  <div class="c-name"><?= e($c['title'] ?? 'بدون عنوان') ?></div>
                  <div class="c-meta"><?= e($c['instructor'] ?? 'مدرس نامشخص') ?> · <?= fa_digits((int)($c['lessons'] ?? 0)) ?> درس</div>
                </div>
              </div>
            </td>
            <td><span class="badge b-primary"><span class="bd"></span><?= e($c['category'] ?? '—') ?></span></td>
            <td>
              <?php if ($isFree): ?>
                <span class="c-price" style="color:var(--violet)">رایگان</span>
              <?php elseif ($disc>0 && $disc<$price): ?>
                <div class="c-price"><?= fa_digits(number_format($disc)) ?><small> تومان</small></div>
                <div class="c-old"><?= fa_digits(number_format($price)) ?></div>
              <?php else: ?>
                <span class="c-price"><?= fa_digits(number_format($price)) ?><small> تومان</small></span>
              <?php endif; ?>
            </td>
            <td><b style="font-variant-numeric:tabular-nums"><?= fa_digits(number_format((int)($c['students'] ?? 0))) ?></b></td>
            <td><span class="rate"><?= cic('star') ?><?= fa_digits(number_format((float)($c['rating'] ?? 0),1)) ?></span></td>
            <td>
              <?php if ($isPub): ?><span class="badge b-ok"><span class="bd"></span>منتشرشده</span>
              <?php else: ?><span class="badge b-draft"><span class="bd"></span>پیش‌نویس</span><?php endif; ?>
            </td>
            <td>
              <div class="row-actions">
                <a href="/courses/course-details.php?id=<?= $id ?>" class="icon-btn-sm" title="مشاهده" target="_top"><?= cic('eye') ?></a>
                <div class="menu-wrap">
                  <button type="button" class="icon-btn-sm menu-btn" title="عملیات بیشتر">
                    <svg class="ic" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
                  </button>
                  <div class="menu-pop">
                    <a href="Courses-create.php?edit=<?= $id ?>"><?= cic('edit') ?> ویرایش دوره</a>
                    <a href="/courses/course-learn.php?id=<?= $id ?>" target="_top"><?= cic('play') ?> پیش‌نمایش محتوا</a>
                    <div class="menu-sep"></div>
                    <?php if ($isPub): ?>
                      <form method="post" style="margin:0"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="do" value="draft"><button type="submit"><?= cic('eyeoff') ?> انتقال به پیش‌نویس</button></form>
                    <?php else: ?>
                      <form method="post" style="margin:0"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="do" value="publish"><button type="submit"><?= cic('rocket') ?> انتشار دوره</button></form>
                    <?php endif; ?>
                    <div class="menu-sep"></div>
                    <button type="button" class="danger del-trigger" data-id="<?= $id ?>" data-name="<?= e($c['title'] ?? '') ?>"><?= cic('trash') ?> حذف دوره</button>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="empty hide" id="emptyState">
      <div class="ei"><?= cic('search') ?></div>
      <b style="font-size:15px">دوره‌ای یافت نشد</b>
      <p style="margin-top:6px;font-size:13px">عبارت جست‌وجو یا فیلتر را تغییر دهید.</p>
    </div>
  </div>
</div>

<!-- مودال حذف -->
<div class="modal-ov" id="delModal">
  <div class="modal">
    <div class="mic"><?= cic('trash') ?></div>
    <h3>حذف دوره</h3>
    <p id="delText">آیا از حذف این دوره مطمئن هستید؟ این عمل قابل بازگشت نیست.</p>
    <form method="post" class="row">
      <input type="hidden" name="id" id="delId"><input type="hidden" name="do" value="delete">
      <button type="button" class="btn btn-ghost btn-block" id="delCancel">انصراف</button>
      <button type="submit" class="btn btn-danger btn-block"><?= cic('trash') ?> حذف</button>
    </form>
  </div>
</div>

<div class="toast" id="toast"><div class="ti"><?= cic('check') ?></div><span id="toastMsg"></span></div>
<?php course_theme_fab(); ?>

<script>
(function(){
  "use strict";
  var $=function(id){return document.getElementById(id);};

  /* جست‌وجو + فیلتر */
  var search=$('searchInput'), tbody=$('tbody'), empty=$('emptyState');
  var curFilter='all';
  function apply(){
    var term=(search.value||'').trim().toLowerCase(); var shown=0;
    tbody.querySelectorAll('tr').forEach(function(tr){
      var okS = curFilter==='all' || tr.getAttribute('data-status')===curFilter;
      var okT = !term || (tr.getAttribute('data-search')||'').indexOf(term)>-1;
      var vis = okS && okT; tr.classList.toggle('hide', !vis); if(vis) shown++;
    });
    empty.classList.toggle('hide', shown>0);
  }
  search.addEventListener('input', apply);
  $('statusFilter').querySelectorAll('button').forEach(function(b){
    b.addEventListener('click', function(){
      $('statusFilter').querySelectorAll('button').forEach(function(x){x.classList.remove('on');});
      b.classList.add('on'); curFilter=b.getAttribute('data-f'); apply();
    });
  });

  /* منوهای کشویی */
  document.addEventListener('click', function(e){
    var btn=e.target.closest('.menu-btn');
    document.querySelectorAll('.menu-wrap.open').forEach(function(w){ if(!btn||!w.contains(btn)) w.classList.remove('open'); });
    if(btn){ btn.closest('.menu-wrap').classList.toggle('open'); }
  });

  /* مودال حذف */
  var modal=$('delModal');
  document.querySelectorAll('.del-trigger').forEach(function(b){
    b.addEventListener('click', function(){
      $('delId').value=b.getAttribute('data-id');
      $('delText').textContent='آیا از حذف دوره‌ی «'+b.getAttribute('data-name')+'» مطمئن هستید؟ این عمل قابل بازگشت نیست.';
      modal.classList.add('show');
    });
  });
  $('delCancel').addEventListener('click', function(){ modal.classList.remove('show'); });
  modal.addEventListener('click', function(e){ if(e.target===modal) modal.classList.remove('show'); });

  /* توست فلش */
  <?php if ($flash): ?>
  (function(){ var t=$('toast'); $('toastMsg').textContent=<?= json_encode($flash, JSON_UNESCAPED_UNICODE) ?>;
    t.classList.add('show'); setTimeout(function(){t.classList.remove('show');},3000); })();
  <?php endif; ?>
})();
</script>
</body>
</html>

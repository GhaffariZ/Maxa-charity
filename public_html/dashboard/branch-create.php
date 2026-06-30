<?php
/* ============================================================================
 *  تعریف شعبه‌ی جدید (فقط مدیر مرکزی)
 * ----------------------------------------------------------------------------
 *  ورودی‌ها: نام شعبه، تگ/slug، نام‌کاربری و رمزِ ادمین شعبه، و تیکِ بخش‌های فعال.
 *  خروجی: ردیف branches + branch_features + ادمین شعبه (dashboard_users) +
 *          صفحه‌ی home پیش‌فرض + پوشه‌ی public_html/branches/{slug} + نقش پیش‌فرض.
 *  امنیت: CSRF، فقط سوپرادمین، slug پاکسازی‌شده و یکتایِ جهانی، تراکنش اتمیک،
 *          ساخت پوشه فقط از روی slug تأییدشده (جلوگیری از path traversal).
 * ========================================================================== */
require_once __DIR__ . '/_guard.php';

if (!dash_is_super()) {
    http_response_code(403);
    exit('۴۰۳ | فقط مدیر مرکزی به این بخش دسترسی دارد.');
}
// بخش «شعب» فقط از «ستاد مرکزی» در دسترس است (نه وقتی سوپرادمین شعبه‌ی دیگری را انتخاب کرده)
dash_require_hq();

$err = '';
$ok  = '';
$created = null;  // پس از ساختِ موفق پر می‌شود تا مودالِ اعتبارنامه نمایش داده شود
$old = ['name' => '', 'slug' => '', 'admin_user' => '', 'features' => DASH_FEATURES];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name      = trim((string)($_POST['name'] ?? ''));
    $slugRaw   = (string)($_POST['slug'] ?? '');
    $slug      = dash_sanitize_slug($slugRaw);
    $adminUser = trim((string)($_POST['admin_user'] ?? ''));
    $adminPass = (string)($_POST['admin_pass'] ?? '');
    $features  = array_values(array_intersect((array)($_POST['features'] ?? []), DASH_FEATURES));

    $old = ['name' => $name, 'slug' => $slug, 'admin_user' => $adminUser, 'features' => $features];

    // ---- اعتبارسنجی ----
    if ($name === '' || mb_strlen($name) > 150) {
        $err = 'نام شعبه را به‌درستی وارد کنید.';
    } elseif ($slug === '') {
        $err = 'تگ شعبه (slug) نامعتبر است؛ فقط حروف انگلیسی، عدد و خط تیره.';
    } elseif (!dash_slug_available($slug)) {
        $err = 'این تگ قبلاً استفاده شده یا رزرو است. تگ دیگری انتخاب کنید.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $adminUser)) {
        $err = 'نام کاربری ادمین شعبه نامعتبر است (۳ تا ۶۰ کاراکترِ مجاز).';
    } elseif (strlen($adminPass) < 8) {
        $err = 'رمز عبور ادمین شعبه باید حداقل ۸ کاراکتر باشد.';
    } else {
        // یکتایی نام کاربری در کل سامانه
        $chk = $pdo->prepare('SELECT 1 FROM dashboard_users WHERE username = ? LIMIT 1');
        $chk->execute([$adminUser]);
        if ($chk->fetch()) {
            $err = 'این نام کاربری از قبل وجود دارد.';
        }
    }

    if ($err === '') {
        try {
            $pdo->beginTransaction();

            // 1) ردیف شعبه
            $st = $pdo->prepare("INSERT INTO branches (name, slug, is_hq, status) VALUES (?,?,0,'active')");
            $st->execute([$name, $slug]);
            $branchId = (int)$pdo->lastInsertId();

            // 2) قابلیت‌ها
            if ($features) {
                $ins = $pdo->prepare('INSERT INTO branch_features (branch_id, feature, enabled) VALUES (?,?,1)');
                foreach ($features as $f) { $ins->execute([$branchId, $f]); }
            }

            // 3) نقش پیش‌فرض خبرنگار (اگر خبر فعال است)
            if (in_array('news', $features, true)) {
                $pdo->prepare("INSERT INTO dashboard_roles (branch_id, name, permissions, is_preset) VALUES (?, 'خبرنگار', JSON_ARRAY('news'), 1)")
                    ->execute([$branchId]);
            }

            // 4) ادمین شعبه
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO dashboard_users (branch_id, username, password_hash, full_name, is_super, is_branch_admin, status) VALUES (?,?,?,?,0,1,'active')")
                ->execute([$branchId, $adminUser, $hash, 'مدیر ' . $name]);

            // 5) صفحه‌ی home پیش‌فرض برای شعبه (تا mymacsa.ir/{slug} بلافاصله کار کند)
            //    بدنه = کامپوننتِ اختصاصیِ شعبه (branch-home: هیرو + هویتِ شعبه + کمپین‌ها
            //    + دوره‌ها) و سپس کامپوننتِ خبرِ مشترکِ سایت (recent-news-hero-v2) که اخبارِ
            //    همین شعبه را نشان می‌دهد؛ نه heroindexِ ستاد (که صفحه را کپی‌به‌نظر می‌رساند).
            $defaultComponents = json_encode(['header', 'branch-home', 'recent-news-hero-v2', 'footer'], JSON_UNESCAPED_UNICODE);
            $pdo->prepare("INSERT INTO pages (title, slug, components, status, branch_id) VALUES (?, 'home', ?, 'published', ?)")
                ->execute([$name, $defaultComponents, $branchId]);

            $pdo->commit();

            // 6) پوشه‌ی فایل‌های شعبه — نام فقط از slug تأییدشده ساخته می‌شود
            $baseDir = realpath(__DIR__ . '/..') . '/branches';
            if (!is_dir($baseDir)) { @mkdir($baseDir, 0755, true); }
            // slug فقط [a-z0-9-] است؛ امکان path traversal وجود ندارد
            $branchDir = $baseDir . '/' . $slug;
            if (!is_dir($branchDir)) {
                @mkdir($branchDir, 0755, true);
                @mkdir($branchDir . '/uploads', 0755, true);
                // غیرفعال‌کردن اجرای PHP در پوشه‌ی آپلودِ شعبه
                @file_put_contents($branchDir . '/uploads/.htaccess',
                    "php_flag engine off\nOptions -ExecCGI\nRemoveHandler .php .phtml .php3 .php4 .php5\n");
            }

            dash_audit('branch_created', ['branch_id' => $branchId, 'slug' => $slug, 'admin' => $adminUser]);
            // اطلاعاتِ نمایش در مودالِ پروفایل (رمز فقط همین یک‌بار به سوپرادمینِ سازنده نشان داده می‌شود)
            $created = ['name' => $name, 'slug' => $slug, 'admin_user' => $adminUser, 'admin_pass' => $adminPass];
            $old = ['name' => '', 'slug' => '', 'admin_user' => '', 'features' => DASH_FEATURES];

        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $err = 'خطا در ساخت شعبه. لطفاً دوباره تلاش کنید.';
        }
    }
}

$FEATURE_LABELS = [
    'hero' => 'هیروها', 'news' => 'خبرها', 'campaigns' => 'کمپین‌ها', 'partners' => 'همکاران',
    'courses' => 'دوره‌ها', 'pages' => 'کامپوننت‌ها و صفحات', 'financial' => 'گزارش مالی',
    'feedback' => 'انتقادات و پیشنهادات', 'medical' => 'پرونده‌های پزشکی',
];

$PANEL_TITLE = 'تعریف شعبه‌ی جدید';
require __DIR__ . '/_panel_head.php';
?>
  <div class="page-head">
    <span class="ph-ic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg></span>
    <div><h1>تعریف شعبه‌ی جدید</h1><p>یک شعبه‌ی مستقل با مدیر و بخش‌های اختصاصی بسازید.</p></div>
  </div>

  <?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
  <?php if ($ok):  ?><div class="msg ok"><?= e($ok) ?></div><?php endif; ?>

  <form method="POST" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="card">
      <h2>مشخصات شعبه</h2>
      <div class="hint">نام نمایشی و تگِ آدرس (slug). آدرس عمومی شعبه: <code>mymacsa.ir/{تگ}</code></div>
      <div class="grid2">
        <div class="field">
          <label>نام شعبه</label>
          <input type="text" name="name" value="<?= e($old['name']) ?>" placeholder="مثلاً: شعبه بیمارستان امام خمینی" required>
        </div>
        <div class="field">
          <label>تگ شعبه (slug)</label>
          <input type="text" name="slug" value="<?= e($old['slug']) ?>" placeholder="tabriz-branch" required dir="ltr">
          <div class="sub">فقط حروف انگلیسی کوچک، عدد و خط تیره. به‌صورت خودکار پاکسازی می‌شود.</div>
        </div>
      </div>
    </div>

    <div class="card">
      <h2>مدیر شعبه</h2>
      <div class="hint">یک حساب مدیر برای این شعبه ساخته می‌شود (دسترسی فقط به همین شعبه).</div>
      <div class="grid2">
        <div class="field">
          <label>نام کاربری مدیر شعبه</label>
          <input type="text" name="admin_user" value="<?= e($old['admin_user']) ?>" placeholder="branch-admin" required dir="ltr">
        </div>
        <div class="field">
          <label>رمز عبور مدیر شعبه</label>
          <div class="pass-row">
            <input type="text" id="adminPass" name="admin_pass" placeholder="حداقل ۸ کاراکتر" required dir="ltr" autocomplete="new-password">
            <button type="button" class="btn-gen" id="genPass">
              <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
              تولید رمز
            </button>
          </div>
          <div class="sub">دکمه‌ی «تولید رمز» یک رمز قوی می‌سازد. آن را یادداشت کنید.</div>
        </div>
      </div>
    </div>

    <div class="card">
      <h2>بخش‌های فعال شعبه</h2>
      <div class="hint">هر بخشی که فعال کنید، برای این شعبه به‌صورت مستقل (با branch_id خودش) فراهم می‌شود.</div>
      <div class="checks">
        <?php foreach ($FEATURE_LABELS as $key => $label):
          $checked = in_array($key, $old['features'], true); ?>
          <label class="check <?= $checked ? 'on' : '' ?>">
            <input type="checkbox" name="features[]" value="<?= e($key) ?>" <?= $checked ? 'checked' : '' ?>>
            <span><?= e($label) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="actions">
      <button class="btn btn-primary" type="submit">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        ساخت شعبه
      </button>
      <a class="btn btn-ghost" href="branch-list.php">مدیریت شعبه‌ها</a>
    </div>
  </form>

  <!-- مودالِ پروفایلِ شعبه‌ی ساخته‌شده -->
  <?php if ($created): ?>
  <div class="modal-overlay show" id="createdModal">
    <div class="modal-card">
      <div class="modal-top">
        <div class="m-ic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
        <h3>شعبه با موفقیت ساخته شد</h3>
        <p>این اطلاعات را برای مدیر شعبه ذخیره کنید.</p>
      </div>
      <div class="modal-body">
        <div class="profile-row"><span class="pk">نام شعبه</span><span class="pv"><?= e($created['name']) ?></span></div>
        <div class="profile-row"><span class="pk">تگ / آدرس</span><span class="pv ltr">/<?= e($created['slug']) ?></span></div>
        <div class="profile-row"><span class="pk">نام کاربری مدیر</span><span class="pv ltr" id="cUser"><?= e($created['admin_user']) ?></span></div>
        <div class="profile-row"><span class="pk">رمز عبور مدیر</span><span class="pv ltr" id="cPass"><?= e($created['admin_pass']) ?></span></div>
        <div class="modal-foot">
          <button type="button" class="btn btn-primary" id="copyCreds">
            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            کپی نام کاربری و رمز عبور مدیر شعبه
          </button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- توست -->
  <div class="toast" id="toast">
    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
    <span id="toastMsg"></span>
  </div>

<script>
  // تعاملِ تیک‌ها (هماهنگ با ظاهر داشبورد)
  document.querySelectorAll('.check input').forEach(function(cb){
    cb.addEventListener('change',function(){ cb.closest('.check').classList.toggle('on',cb.checked); });
  });

  // تولید رمز قوی
  (function(){
    var btn=document.getElementById('genPass'); if(!btn) return;
    var chars='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%*';
    btn.addEventListener('click',function(){
      var n=14, out='', arr=new Uint32Array(n);
      (window.crypto||window.msCrypto).getRandomValues(arr);
      for(var i=0;i<n;i++){ out+=chars[arr[i]%chars.length]; }
      var f=document.getElementById('adminPass'); f.value=out; f.focus();
    });
  })();

  // توست
  function showToast(msg){
    var t=document.getElementById('toast'); document.getElementById('toastMsg').textContent=msg;
    t.classList.add('show'); setTimeout(function(){ t.classList.remove('show'); },2200);
  }

  // کپیِ اعتبارنامه و بازگشت به فهرست شعبه‌ها
  (function(){
    var btn=document.getElementById('copyCreds'); if(!btn) return;
    btn.addEventListener('click',function(){
      var u=document.getElementById('cUser').textContent.trim();
      var p=document.getElementById('cPass').textContent.trim();
      var text='username: "'+u+'"\npassword: "'+p+'"';
      function done(){ showToast('نام کاربری و رمز عبور کپی شد!'); setTimeout(function(){ window.location.href='branch-list.php'; },1200); }
      if(navigator.clipboard && navigator.clipboard.writeText){
        navigator.clipboard.writeText(text).then(done,function(){ fallback(); });
      } else { fallback(); }
      function fallback(){
        var ta=document.createElement('textarea'); ta.value=text; ta.style.position='fixed'; ta.style.opacity='0';
        document.body.appendChild(ta); ta.select();
        try{ document.execCommand('copy'); }catch(e){}
        document.body.removeChild(ta); done();
      }
    });
  })();
</script>
</div></body></html>

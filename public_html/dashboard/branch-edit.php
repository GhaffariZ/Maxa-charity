<?php
/* ============================================================================
 *  ویرایش شعبه (فقط مدیر مرکزی، و فقط از «ستاد مرکزی»)
 * ----------------------------------------------------------------------------
 *  امکان ویرایشِ همه‌ی مواردِ زمانِ ساخت:
 *    - نام شعبه، تگ/slug (با تغییرِ نامِ پوشه‌ی فایل‌ها)، قابلیت‌ها (افزودن/حذف
 *      بدون پاک‌کردنِ محتوا)، و نام‌کاربری/رمزِ مدیر شعبه (رمز اختیاری: خالی = بدون تغییر).
 *  امنیت: CSRF، فقط سوپرادمین + HQ، IDOR (شعبه باید واقعی باشد)، تراکنش اتمیک،
 *          تغییرِ نامِ پوشه فقط از روی slugِ تأییدشده (جلوگیری از path traversal).
 *  محدودیتِ HQ: تگ و نام‌کاربری/رمزِ ادمینِ HQ از این‌جا تغییر نمی‌کنند (HQ ادمینِ شعبه ندارد).
 * ========================================================================== */
require_once __DIR__ . '/_guard.php';

if (!dash_is_super()) {
    http_response_code(403);
    exit('۴۰۳ | فقط مدیر مرکزی به این بخش دسترسی دارد.');
}
dash_require_hq();

$FEATURE_LABELS = [
    'hero' => 'هیروها', 'news' => 'خبرها', 'campaigns' => 'کمپین‌ها', 'partners' => 'همکاران',
    'courses' => 'دوره‌ها', 'pages' => 'کامپوننت‌ها و صفحات', 'financial' => 'گزارش مالی',
    'feedback' => 'انتقادات و پیشنهادات', 'medical' => 'پرونده‌های پزشکی',
];

$branchId = (int)($_GET['id'] ?? ($_POST['branch_id'] ?? 0));
$branch   = dash_load_branch($branchId);
if (!$branch) {
    http_response_code(404);
    exit('۴۰۴ | شعبه یافت نشد.');
}
$isHq = (int)$branch['is_hq'] === 1;

// مدیرِ فعلیِ شعبه (در HQ ممکن است نباشد)
$adminRow = null;
$st = $pdo->prepare("SELECT id, username FROM dashboard_users WHERE branch_id = ? AND is_branch_admin = 1 ORDER BY id ASC LIMIT 1");
$st->execute([$branchId]);
$adminRow = $st->fetch() ?: null;

// قابلیت‌های فعلی
$curFeatures = [];
$st = $pdo->prepare("SELECT feature FROM branch_features WHERE branch_id = ? AND enabled = 1");
$st->execute([$branchId]);
foreach ($st->fetchAll() as $r) { $curFeatures[] = $r['feature']; }

$err = '';
$ok  = '';
$passChanged = false;
$newPassForModal = '';

// مقادیرِ فرم (پیش‌فرض از دیتابیس)
$old = [
    'name'       => $branch['name'],
    'slug'       => $branch['slug'],
    'admin_user' => $adminRow['username'] ?? '',
    'features'   => $curFeatures,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name      = trim((string)($_POST['name'] ?? ''));
    $slug      = $isHq ? $branch['slug'] : dash_sanitize_slug((string)($_POST['slug'] ?? ''));
    $adminUser = trim((string)($_POST['admin_user'] ?? ''));
    $adminPass = (string)($_POST['admin_pass'] ?? '');
    $features  = array_values(array_intersect((array)($_POST['features'] ?? []), DASH_FEATURES));

    $old = ['name' => $name, 'slug' => $slug, 'admin_user' => $adminUser, 'features' => $features];

    // ---- اعتبارسنجی ----
    if ($name === '' || mb_strlen($name) > 150) {
        $err = 'نام شعبه را به‌درستی وارد کنید.';
    } elseif (!$isHq && $slug === '') {
        $err = 'تگ شعبه (slug) نامعتبر است؛ فقط حروف انگلیسی، عدد و خط تیره.';
    } elseif (!$isHq && !dash_slug_available($slug, $branchId)) {
        $err = 'این تگ قبلاً استفاده شده یا رزرو است. تگ دیگری انتخاب کنید.';
    } elseif (!$isHq && $adminRow && !preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $adminUser)) {
        $err = 'نام کاربری مدیر شعبه نامعتبر است (۳ تا ۶۰ کاراکترِ مجاز).';
    } elseif ($adminPass !== '' && strlen($adminPass) < 8) {
        $err = 'رمز عبور باید حداقل ۸ کاراکتر باشد (یا برای عدم تغییر، خالی بگذارید).';
    } elseif (!$isHq && $adminRow && $adminUser !== $adminRow['username']) {
        // یکتایی نام کاربریِ جدید (به‌جز خودِ همین رکورد)
        $chk = $pdo->prepare('SELECT 1 FROM dashboard_users WHERE username = ? AND id <> ? LIMIT 1');
        $chk->execute([$adminUser, (int)$adminRow['id']]);
        if ($chk->fetch()) { $err = 'این نام کاربری از قبل وجود دارد.'; }
    }

    if ($err === '') {
        try {
            $pdo->beginTransaction();

            $oldSlug = $branch['slug'];

            // 1) نام + slug
            if ($isHq) {
                $pdo->prepare('UPDATE branches SET name = ? WHERE id = ?')->execute([$name, $branchId]);
            } else {
                $pdo->prepare('UPDATE branches SET name = ?, slug = ? WHERE id = ?')->execute([$name, $slug, $branchId]);
            }

            // 2) قابلیت‌ها — diff (افزودن جدیدها، حذفِ برداشته‌شده‌ها؛ محتوا پاک نمی‌شود)
            $toAdd    = array_diff($features, $curFeatures);
            $toRemove = array_diff($curFeatures, $features);
            if ($toAdd) {
                $ins = $pdo->prepare('INSERT INTO branch_features (branch_id, feature, enabled) VALUES (?,?,1)
                                      ON DUPLICATE KEY UPDATE enabled = 1');
                foreach ($toAdd as $f) { $ins->execute([$branchId, $f]); }
            }
            if ($toRemove) {
                $del = $pdo->prepare('DELETE FROM branch_features WHERE branch_id = ? AND feature = ?');
                foreach ($toRemove as $f) { $del->execute([$branchId, $f]); }
            }
            // اگر خبر تازه فعال شد و نقشِ پیش‌فرضِ خبرنگار نبود، بساز
            if (in_array('news', $toAdd, true)) {
                $rc = $pdo->prepare("SELECT 1 FROM dashboard_roles WHERE branch_id = ? AND name = 'خبرنگار' LIMIT 1");
                $rc->execute([$branchId]);
                if (!$rc->fetch()) {
                    $pdo->prepare("INSERT INTO dashboard_roles (branch_id, name, permissions, is_preset) VALUES (?, 'خبرنگار', JSON_ARRAY('news'), 1)")
                        ->execute([$branchId]);
                }
            }

            // 3) مدیر شعبه — نام‌کاربری و/یا رمز (فقط برای شعبه‌های غیر-HQ که ادمین دارند)
            if (!$isHq && $adminRow) {
                if ($adminUser !== $adminRow['username']) {
                    $pdo->prepare('UPDATE dashboard_users SET username = ? WHERE id = ?')->execute([$adminUser, (int)$adminRow['id']]);
                }
                if ($adminPass !== '') {
                    $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                    $pdo->prepare('UPDATE dashboard_users SET password_hash = ? WHERE id = ?')->execute([$hash, (int)$adminRow['id']]);
                    $passChanged = true;
                    $newPassForModal = $adminPass;
                }
            }

            // 4) صفحه‌ی home شعبه: عنوان را با نام جدید همگام کن (slug ثابت می‌ماند: 'home')
            $pdo->prepare("UPDATE pages SET title = ? WHERE branch_id = ? AND slug = 'home'")->execute([$name, $branchId]);

            $pdo->commit();

            // 5) تغییرِ نامِ پوشه‌ی فایل‌های شعبه در صورت تغییر slug (نام فقط از slug تأییدشده)
            if (!$isHq && $slug !== $oldSlug) {
                $baseDir = realpath(__DIR__ . '/..') . '/branches';
                $oldDir  = $baseDir . '/' . $oldSlug;
                $newDir  = $baseDir . '/' . $slug;
                if (is_dir($oldDir) && !is_dir($newDir)) {
                    @rename($oldDir, $newDir);
                } elseif (!is_dir($newDir)) {
                    @mkdir($newDir, 0755, true);
                    @mkdir($newDir . '/uploads', 0755, true);
                    @file_put_contents($newDir . '/uploads/.htaccess',
                        "php_flag engine off\nOptions -ExecCGI\nRemoveHandler .php .phtml .php3 .php4 .php5\n");
                }
            }

            dash_audit('branch_updated', ['branch_id' => $branchId, 'slug' => $slug, 'pass_changed' => $passChanged]);

            // اگر رمز عوض شد، مودالِ اعتبارنامه را نشان بده؛ در غیر این صورت به فهرست برگرد.
            if ($passChanged) {
                $ok = 'تغییرات ذخیره شد.';
                // مقادیرِ تازه برای نمایش
                $branch['name'] = $name; $branch['slug'] = $slug; $old['admin_user'] = $adminUser;
            } else {
                header('Location: branch-list.php');
                exit;
            }

        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $err = 'خطا در ذخیره‌ی تغییرات. دوباره تلاش کنید.';
        }
    }
}

$PANEL_TITLE = 'ویرایش شعبه';
require __DIR__ . '/_panel_head.php';
?>
  <div class="page-head">
    <span class="ph-ic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></span>
    <div><h1>ویرایش شعبه</h1><p>ویرایشِ مشخصات، بخش‌ها و مدیرِ شعبه‌ی «<?= e($branch['name']) ?>»<?php if ($isHq): ?> (ستاد مرکزی)<?php endif; ?>.</p></div>
  </div>

  <?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
  <?php if ($ok && !$passChanged): ?><div class="msg ok"><?= e($ok) ?></div><?php endif; ?>

  <form method="POST" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="branch_id" value="<?= (int)$branchId ?>">

    <div class="card">
      <h2>مشخصات شعبه</h2>
      <div class="grid2">
        <div class="field">
          <label>نام شعبه</label>
          <input type="text" name="name" value="<?= e($old['name']) ?>" required>
        </div>
        <div class="field">
          <label>تگ شعبه (slug)</label>
          <input type="text" name="slug" value="<?= e($old['slug']) ?>" dir="ltr" <?= $isHq ? 'readonly' : 'required' ?>>
          <div class="sub"><?= $isHq ? 'تگِ ستاد مرکزی قابل تغییر نیست.' : 'تغییرِ تگ، نامِ پوشه‌ی فایل‌های شعبه را هم تغییر می‌دهد.' ?></div>
        </div>
      </div>
    </div>

    <?php if (!$isHq && $adminRow): ?>
    <div class="card">
      <h2>مدیر شعبه</h2>
      <div class="hint">برای تغییرِ رمز، رمز جدید وارد کنید؛ برای حفظِ رمزِ فعلی خالی بگذارید.</div>
      <div class="grid2">
        <div class="field">
          <label>نام کاربری مدیر شعبه</label>
          <input type="text" name="admin_user" value="<?= e($old['admin_user']) ?>" required dir="ltr">
        </div>
        <div class="field">
          <label>رمز عبور جدید (اختیاری)</label>
          <div class="pass-row">
            <input type="text" id="adminPass" name="admin_pass" placeholder="خالی = بدون تغییر" dir="ltr" autocomplete="new-password">
            <button type="button" class="btn-gen" id="genPass">
              <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
              تولید رمز
            </button>
          </div>
        </div>
      </div>
    </div>
    <?php else: ?>
      <input type="hidden" name="admin_user" value="<?= e($old['admin_user']) ?>">
    <?php endif; ?>

    <div class="card">
      <h2>بخش‌های فعال شعبه</h2>
      <div class="hint">برداشتنِ تیکِ یک بخش، آن را از منوی شعبه حذف می‌کند ولی محتوای آن پاک نمی‌شود.</div>
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
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        ذخیره‌ی تغییرات
      </button>
      <a class="btn btn-ghost" href="branch-list.php">بازگشت</a>
    </div>
  </form>

  <!-- مودالِ رمزِ جدید (وقتی رمز عوض شد) -->
  <?php if ($passChanged): ?>
  <div class="modal-overlay show" id="createdModal">
    <div class="modal-card">
      <div class="modal-top">
        <div class="m-ic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
        <h3>رمز عبور تغییر کرد</h3>
        <p>اطلاعاتِ تازه‌ی ورودِ مدیر شعبه را ذخیره کنید.</p>
      </div>
      <div class="modal-body">
        <div class="profile-row"><span class="pk">نام شعبه</span><span class="pv"><?= e($branch['name']) ?></span></div>
        <div class="profile-row"><span class="pk">نام کاربری مدیر</span><span class="pv ltr" id="cUser"><?= e($old['admin_user']) ?></span></div>
        <div class="profile-row"><span class="pk">رمز عبور جدید</span><span class="pv ltr" id="cPass"><?= e($newPassForModal) ?></span></div>
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

  <div class="toast" id="toast">
    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
    <span id="toastMsg"></span>
  </div>

<script>
  document.querySelectorAll('.check input').forEach(function(cb){
    cb.addEventListener('change',function(){ cb.closest('.check').classList.toggle('on',cb.checked); });
  });
  (function(){
    var btn=document.getElementById('genPass'); if(!btn) return;
    var chars='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%*';
    btn.addEventListener('click',function(){
      var n=14,out='',arr=new Uint32Array(n);
      (window.crypto||window.msCrypto).getRandomValues(arr);
      for(var i=0;i<n;i++){ out+=chars[arr[i]%chars.length]; }
      var f=document.getElementById('adminPass'); f.value=out; f.focus();
    });
  })();
  function showToast(msg){
    var t=document.getElementById('toast'); document.getElementById('toastMsg').textContent=msg;
    t.classList.add('show'); setTimeout(function(){ t.classList.remove('show'); },2200);
  }
  (function(){
    var btn=document.getElementById('copyCreds'); if(!btn) return;
    btn.addEventListener('click',function(){
      var u=document.getElementById('cUser').textContent.trim();
      var p=document.getElementById('cPass').textContent.trim();
      var text='username: "'+u+'"\npassword: "'+p+'"';
      function done(){ showToast('نام کاربری و رمز عبور کپی شد!'); setTimeout(function(){ window.location.href='branch-list.php'; },1200); }
      if(navigator.clipboard && navigator.clipboard.writeText){ navigator.clipboard.writeText(text).then(done,fallback); }
      else { fallback(); }
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

<?php
/* ============================================================================
 *  افزودن کاربر شعبه (مدیر شعبه یا مدیر مرکزی — در محدوده‌ی شعبه‌ی فعال)
 * ----------------------------------------------------------------------------
 *  - نام، نام‌کاربری، رمز و دسترسی‌ها.
 *  - کاربر در شعبه‌ی فعال ساخته می‌شود. مدیر شعبه نمی‌تواند سوپرادمین بسازد.
 *  امنیت: CSRF، فقط ادمین، ایزولاسیون شعبه روی همه‌ی کوئری‌ها.
 * ========================================================================== */
require_once __DIR__ . '/_guard.php';

// فقط مدیر شعبه یا مدیر مرکزی
if (!dash_is_branch_admin() && !dash_is_super()) {
    http_response_code(403);
    exit('۴۰۳ | دسترسی غیرمجاز');
}

$BRANCH_ID = dash_active_branch_id();   // شعبه‌ای که کاربر در آن ساخته می‌شود
$err = ''; $ok = '';
$old = ['full_name' => '', 'username' => '', 'permissions' => []];

$FEATURE_LABELS = dash_feature_labels(dash_is_hq_view());
if (!dash_is_hq_view()) {
    unset($FEATURE_LABELS['events']);
}
$GRANTABLE = array_keys($FEATURE_LABELS);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $posted = $_POST['permissions'] ?? [];
    $permissions = is_array($posted)
        ? array_values(array_intersect($GRANTABLE, array_filter($posted, 'is_string')))
        : [];
    $old = ['full_name' => $fullName, 'username' => $username, 'permissions' => $permissions];

    if (($_POST['edit_permissions'] ?? '') !== '1' || !is_array($posted)) {
        $err = 'فرم را دوباره باز کنید و دسترسی‌ها را انتخاب کنید.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $username)) {
        $err = 'نام کاربری نامعتبر است (۳ تا ۶۰ کاراکترِ مجاز).';
    } elseif (strlen($password) < 8) {
        $err = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
    } else {
        $chk = $pdo->prepare('SELECT 1 FROM dashboard_users WHERE username = ? LIMIT 1');
        $chk->execute([$username]);
        if ($chk->fetch()) {
            $err = 'این نام کاربری از قبل وجود دارد.';
        }
    }

    if ($err === '') {
        try {
            $pdo->beginTransaction();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // is_super و is_branch_admin همیشه 0 — مدیر شعبه نمی‌تواند ارتقا دهد (least privilege)
            $pdo->prepare("INSERT INTO dashboard_users (branch_id, username, password_hash, full_name, direct_permissions, is_super, is_branch_admin, status) VALUES (?,?,?,?,?,0,0,'active')")
                ->execute([$BRANCH_ID, $username, $hash, $fullName ?: null, json_encode($permissions, JSON_THROW_ON_ERROR)]);
            $pdo->commit();
            dash_audit('user_created', ['branch_id' => $BRANCH_ID, 'username' => $username, 'permissions' => $permissions]);
            $ok = 'کاربر «' . $username . '» با موفقیت ساخته شد.';
            $old = ['full_name' => '', 'username' => '', 'permissions' => []];
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $err = 'خطا در ساخت کاربر. دوباره تلاش کنید.';
        }
    }
}

$branchRow = dash_load_branch($BRANCH_ID);

$PANEL_TITLE = 'افزودن کاربر';
require __DIR__ . '/_panel_head.php';
?>
  <div class="page-head">
    <span class="ph-ic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V21"/><circle cx="9.5" cy="7" r="3.5"/><path d="M19 8v6M22 11h-6"/></svg></span>
    <div><h1>افزودن کاربر</h1><p>کاربر جدید برای شعبه‌ی «<?= e($branchRow['name'] ?? '') ?>».</p></div>
  </div>

  <?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
  <?php if ($ok):  ?><div class="msg ok"><?= e($ok) ?></div><?php endif; ?>

  <form method="POST" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="card">
      <h2>مشخصات کاربر</h2>
      <div class="grid2">
        <div class="field">
          <label>نام و نام خانوادگی</label>
          <input type="text" name="full_name" value="<?= e($old['full_name']) ?>" placeholder="نام کامل">
        </div>
        <div class="field">
          <label>نام کاربری</label>
          <input type="text" name="username" value="<?= e($old['username']) ?>" placeholder="username" required dir="ltr">
        </div>
      </div>
      <div class="field">
        <label>رمز عبور</label>
        <div class="pass-row">
          <input type="text" id="pass" name="password" placeholder="حداقل ۸ کاراکتر" required dir="ltr" autocomplete="new-password">
          <button type="button" class="btn-gen" id="genPass">
            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
            تولید رمز
          </button>
        </div>
      </div>
    </div>

    <div class="card">
      <h2>دسترسی‌های کاربر</h2>
      <div class="hint">بخش‌هایی را که این کاربر به آن‌ها نیاز دارد انتخاب کنید</div>
      <input type="hidden" name="edit_permissions" value="1">
      <div class="checks">
        <?php foreach ($FEATURE_LABELS as $key => $label):
          $checked = in_array($key, $old['permissions'], true); ?>
          <label class="check <?= $checked ? 'on' : '' ?>">
            <input type="checkbox" name="permissions[]" value="<?= e($key) ?>" <?= $checked ? 'checked' : '' ?>>
            <span><?= e($label) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="actions">
      <button class="btn btn-primary" type="submit">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        افزودن کاربر
      </button>
      <a class="btn btn-ghost" href="user-manage.php">مدیریت کاربران</a>
    </div>
  </form>

<script>
  document.querySelectorAll('.check input[type=checkbox]').forEach(function(cb){
    cb.addEventListener('change',function(){ cb.closest('.check').classList.toggle('on',cb.checked); });
  });

  (function(){
    var btn=document.getElementById('genPass'); if(!btn) return;
    var chars='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%*';
    btn.addEventListener('click',function(){
      var n=14,out='',arr=new Uint32Array(n);
      (window.crypto||window.msCrypto).getRandomValues(arr);
      for(var i=0;i<n;i++){ out+=chars[arr[i]%chars.length]; }
      var f=document.getElementById('pass'); f.value=out; f.focus();
    });
  })();
</script>
</div></body></html>

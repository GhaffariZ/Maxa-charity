<?php
/* ============================================================================
 *  ویرایش کاربر شعبه (مدیر شعبه یا مدیر مرکزی — در محدوده‌ی شعبه‌ی فعال)
 * ----------------------------------------------------------------------------
 *  ویرایشِ نام، نام‌کاربری، نقش و (اختیاری) رمز عبور.
 *  امنیت: CSRF، IDOR (کاربرِ هدف باید در همین شعبه و غیرسوپر باشد)، least privilege
 *  (is_super/is_branch_admin از این‌جا تغییر نمی‌کنند؛ مدیرِ شعبه قابلِ ویرایش نیست).
 * ========================================================================== */
require_once __DIR__ . '/_guard.php';

if (!dash_is_branch_admin() && !dash_is_super()) {
    http_response_code(403);
    exit('۴۰۳ | دسترسی غیرمجاز');
}

$BRANCH_ID = dash_active_branch_id();
$me        = (int)$DASH_USER['id'];

$FEATURE_LABELS = [
    'hero' => 'هیروها', 'news' => 'خبرها', 'campaigns' => 'کمپین‌ها', 'partners' => 'همکاران',
    'courses' => 'دوره‌ها', 'pages' => 'کامپوننت‌ها و صفحات', 'financial' => 'گزارش مالی',
    'feedback' => 'انتقادات و پیشنهادات', 'medical' => 'پرونده‌های پزشکی',
];
if (dash_is_hq_view()) {
    $FEATURE_LABELS['news_editor'] = 'سردبیری خبر (تایید/انتشار)';
    $FEATURE_LABELS['maxapedia']   = 'مکساپدیا';
}
$GRANTABLE = array_keys($FEATURE_LABELS);

// کاربرِ هدف
$uid = (int)($_GET['id'] ?? ($_POST['user_id'] ?? 0));
$st = $pdo->prepare("SELECT * FROM dashboard_users WHERE id = ? LIMIT 1");
$st->execute([$uid]);
$target = $st->fetch();

// IDOR: باید در همین شعبه، غیرسوپر، غیرِ مدیرِ شعبه (مدیر شعبه از این‌جا ویرایش نمی‌شود) و خودِ من نباشد
if (!$target || (int)$target['branch_id'] !== $BRANCH_ID || (int)$target['is_super'] === 1
    || (int)$target['is_branch_admin'] === 1 || (int)$target['id'] === $me) {
    http_response_code(403);
    exit('۴۰۳ | این کاربر قابل ویرایش نیست.');
}

$err = ''; $ok = '';
$old = [
    'full_name'     => $target['full_name'] ?? '',
    'username'      => $target['username'],
    'role_mode'     => 'existing',
    'role_id'       => $target['role_id'] !== null ? (int)$target['role_id'] : '',
    'new_role_name' => '',
    'new_perms'     => [],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $roleMode = (string)($_POST['role_mode'] ?? 'existing');
    $roleId   = (int)($_POST['role_id'] ?? 0);
    $newName  = trim((string)($_POST['new_role_name'] ?? ''));
    $newPerms = array_values(array_intersect((array)($_POST['new_perms'] ?? []), $GRANTABLE));

    $old = ['full_name' => $fullName, 'username' => $username, 'role_mode' => $roleMode,
            'role_id' => $roleId ?: '', 'new_role_name' => $newName, 'new_perms' => $newPerms];

    if (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $username)) {
        $err = 'نام کاربری نامعتبر است (۳ تا ۶۰ کاراکترِ مجاز).';
    } elseif ($password !== '' && strlen($password) < 8) {
        $err = 'رمز عبور باید حداقل ۸ کاراکتر باشد (یا برای عدم تغییر، خالی بگذارید).';
    } elseif ($username !== $target['username']) {
        // یکتایی نام کاربریِ جدید (به‌جز خودِ این کاربر)
        $chk = $pdo->prepare('SELECT 1 FROM dashboard_users WHERE username = ? AND id <> ? LIMIT 1');
        $chk->execute([$username, $uid]);
        if ($chk->fetch()) { $err = 'این نام کاربری از قبل وجود دارد.'; }
    }

    // نقش: یا انتخابِ نقشِ موجود (اختیاری)، یا تعریفِ نقشِ جدید با دسترسی‌های دلخواه.
    $finalRoleId = null;
    if ($err === '') {
        if ($roleMode === 'new') {
            if ($newName === '' || mb_strlen($newName) > 80) {
                $err = 'نام نقش جدید را وارد کنید.';
            } elseif (!$newPerms) {
                $err = 'برای نقش جدید حداقل یک دسترسی انتخاب کنید.';
            } else {
                // یکتایی نام نقش در همین شعبه
                $rc = $pdo->prepare('SELECT 1 FROM dashboard_roles WHERE branch_id = ? AND name = ? LIMIT 1');
                $rc->execute([$BRANCH_ID, $newName]);
                if ($rc->fetch()) { $err = 'نقشی با این نام در این شعبه وجود دارد.'; }
            }
        } elseif ($roleId > 0) {
            // نقشِ موجود — باید متعلق به همین شعبه باشد
            $rc = $pdo->prepare('SELECT id FROM dashboard_roles WHERE id = ? AND branch_id = ? LIMIT 1');
            $rc->execute([$roleId, $BRANCH_ID]);
            if (!$rc->fetch()) { $err = 'نقش انتخاب‌شده معتبر نیست.'; }
            else { $finalRoleId = $roleId; }
        }
        // در غیر این صورت (existing بدون انتخاب) → بدون نقش (null)
    }

    if ($err === '') {
        try {
            $pdo->beginTransaction();
            if ($roleMode === 'new') {
                $pdo->prepare('INSERT INTO dashboard_roles (branch_id, name, permissions, is_preset) VALUES (?,?,?,0)')
                    ->execute([$BRANCH_ID, $newName, json_encode($newPerms, JSON_UNESCAPED_UNICODE)]);
                $finalRoleId = (int)$pdo->lastInsertId();
            }
            $pdo->prepare('UPDATE dashboard_users SET full_name = ?, username = ?, role_id = ? WHERE id = ? AND branch_id = ?')
                ->execute([$fullName ?: null, $username, $finalRoleId, $uid, $BRANCH_ID]);
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE dashboard_users SET password_hash = ?, failed_attempts = 0, locked_until = NULL WHERE id = ? AND branch_id = ?')
                    ->execute([$hash, $uid, $BRANCH_ID]);
            }
            $pdo->commit();
            dash_audit('user_updated', ['user_id' => $uid, 'branch_id' => $BRANCH_ID, 'pass_changed' => $password !== '']);
            header('Location: user-manage.php');
            exit;
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $err = 'خطا در ذخیره‌ی تغییرات. دوباره تلاش کنید.';
        }
    }
}

// نقش‌های موجودِ همین شعبه
$roleStmt = $pdo->prepare('SELECT id, name, permissions, is_preset FROM dashboard_roles WHERE branch_id = ? ORDER BY is_preset DESC, name ASC');
$roleStmt->execute([$BRANCH_ID]);
$roles = $roleStmt->fetchAll();

$branchRow = dash_load_branch($BRANCH_ID);

$PANEL_TITLE = 'ویرایش کاربر';
require __DIR__ . '/_panel_head.php';
?>
  <div class="page-head">
    <span class="ph-ic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V21"/><circle cx="9.5" cy="7" r="3.5"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L14 13l-4 1 1-4Z"/></svg></span>
    <div><h1>ویرایش کاربر</h1><p>کاربرِ شعبه‌ی «<?= e($branchRow['name'] ?? '') ?>».</p></div>
  </div>

  <?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>

  <form method="POST" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="user_id" value="<?= (int)$uid ?>">

    <div class="card">
      <h2>مشخصات کاربر</h2>
      <div class="grid2">
        <div class="field">
          <label>نام و نام خانوادگی</label>
          <input type="text" name="full_name" value="<?= e($old['full_name']) ?>" placeholder="نام کامل">
        </div>
        <div class="field">
          <label>نام کاربری</label>
          <input type="text" name="username" value="<?= e($old['username']) ?>" required dir="ltr">
        </div>
      </div>
      <div class="field">
        <label>رمز عبور جدید (اختیاری)</label>
        <div class="pass-row">
          <input type="text" id="pass" name="password" placeholder="خالی = بدون تغییر" dir="ltr" autocomplete="new-password">
          <button type="button" class="btn-gen" id="genPass">
            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
            تولید رمز
          </button>
        </div>
      </div>
    </div>

    <div class="card">
      <h2>نقش و دسترسی</h2>
      <div class="hint">یا نقشِ موجودِ کاربر را تغییر دهید، یا نقشِ جدیدی با دسترسی‌های دلخواه برای او بسازید.</div>

      <div class="field">
        <label class="check" style="display:inline-flex;margin-inline-end:10px">
          <input type="radio" name="role_mode" value="existing" <?= $old['role_mode'] !== 'new' ? 'checked' : '' ?>>
          <span>نقش موجود</span>
        </label>
        <label class="check" style="display:inline-flex">
          <input type="radio" name="role_mode" value="new" <?= $old['role_mode'] === 'new' ? 'checked' : '' ?>>
          <span>افزودن نقش جدید</span>
        </label>
      </div>

      <div id="existingBox" class="field">
        <label>انتخاب نقش</label>
        <select name="role_id">
          <option value="">— بدون نقش —</option>
          <?php foreach ($roles as $r):
            $perms = implode('، ', array_map(fn($p)=>$FEATURE_LABELS[$p] ?? $p, (array)json_decode($r['permissions'], true) ?: [])); ?>
            <option value="<?= (int)$r['id'] ?>" <?= (string)$old['role_id'] === (string)$r['id'] ? 'selected' : '' ?>>
              <?= e($r['name']) ?> (<?= e($perms) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div id="newBox" class="field" style="display:none">
        <label>نام نقش جدید</label>
        <input type="text" name="new_role_name" value="<?= e($old['new_role_name']) ?>" placeholder="مثلاً: مسئول مکساپدیا">
        <div class="sub" style="margin:12px 0 8px">دسترسی‌های این نقش:</div>
        <div class="checks">
          <?php foreach ($FEATURE_LABELS as $key => $label):
            $checked = in_array($key, $old['new_perms'], true); ?>
            <label class="check <?= $checked ? 'on' : '' ?>">
              <input type="checkbox" name="new_perms[]" value="<?= e($key) ?>" <?= $checked ? 'checked' : '' ?>>
              <span><?= e($label) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="actions">
      <button class="btn btn-primary" type="submit">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        ذخیره‌ی تغییرات
      </button>
      <a class="btn btn-ghost" href="user-manage.php">بازگشت</a>
    </div>
  </form>

<script>
  function syncRoleMode(){
    var isNew=document.querySelector('input[name=role_mode]:checked').value==='new';
    document.getElementById('newBox').style.display=isNew?'block':'none';
    document.getElementById('existingBox').style.display=isNew?'none':'block';
  }
  document.querySelectorAll('input[name=role_mode]').forEach(r=>r.addEventListener('change',syncRoleMode));
  document.querySelectorAll('.check input[type=checkbox]').forEach(function(cb){
    cb.addEventListener('change',function(){ cb.closest('.check').classList.toggle('on',cb.checked); });
  });
  syncRoleMode();

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

<?php
/* ============================================================================
 *  افزودن کاربر شعبه (مدیر شعبه یا مدیر مرکزی — در محدوده‌ی شعبه‌ی فعال)
 * ----------------------------------------------------------------------------
 *  - نام، نام‌کاربری، رمز، و نقش (از نقش‌های موجود یا ساختِ نقش جدید با تیک‌ها).
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
$old = ['full_name' => '', 'username' => '', 'role_mode' => 'existing', 'role_id' => '', 'new_role_name' => '', 'new_perms' => []];

$FEATURE_LABELS = [
    'hero' => 'هیروها', 'news' => 'خبرها', 'campaigns' => 'کمپین‌ها', 'partners' => 'همکاران',
    'courses' => 'دوره‌ها', 'pages' => 'کامپوننت‌ها و صفحات', 'financial' => 'گزارش مالی',
    'feedback' => 'انتقادات و پیشنهادات', 'medical' => 'پرونده‌های پزشکی',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $roleMode = (string)($_POST['role_mode'] ?? 'existing');
    $roleId   = (int)($_POST['role_id'] ?? 0);
    $newName  = trim((string)($_POST['new_role_name'] ?? ''));
    $newPerms = array_values(array_intersect((array)($_POST['new_perms'] ?? []), DASH_FEATURES));

    $old = ['full_name' => $fullName, 'username' => $username, 'role_mode' => $roleMode,
            'role_id' => $roleId ?: '', 'new_role_name' => $newName, 'new_perms' => $newPerms];

    if (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $username)) {
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

    // تعیین نقش
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
        } else { // existing
            if ($roleId <= 0) {
                $err = 'یک نقش انتخاب کنید یا نقش جدید بسازید.';
            } else {
                // IDOR: نقش باید متعلق به همین شعبه باشد
                $rc = $pdo->prepare('SELECT id FROM dashboard_roles WHERE id = ? AND branch_id = ? LIMIT 1');
                $rc->execute([$roleId, $BRANCH_ID]);
                if (!$rc->fetch()) { $err = 'نقش انتخاب‌شده معتبر نیست.'; }
                else { $finalRoleId = $roleId; }
            }
        }
    }

    if ($err === '') {
        try {
            $pdo->beginTransaction();
            if ($roleMode === 'new') {
                $pdo->prepare('INSERT INTO dashboard_roles (branch_id, name, permissions, is_preset) VALUES (?,?,?,0)')
                    ->execute([$BRANCH_ID, $newName, json_encode($newPerms, JSON_UNESCAPED_UNICODE)]);
                $finalRoleId = (int)$pdo->lastInsertId();
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // is_super و is_branch_admin همیشه 0 — مدیر شعبه نمی‌تواند ارتقا دهد (least privilege)
            $pdo->prepare("INSERT INTO dashboard_users (branch_id, username, password_hash, full_name, role_id, is_super, is_branch_admin, status) VALUES (?,?,?,?,?,0,0,'active')")
                ->execute([$BRANCH_ID, $username, $hash, $fullName ?: null, $finalRoleId]);
            $pdo->commit();
            dash_audit('user_created', ['branch_id' => $BRANCH_ID, 'username' => $username, 'role_id' => $finalRoleId]);
            $ok = 'کاربر «' . $username . '» با موفقیت ساخته شد.';
            $old = ['full_name' => '', 'username' => '', 'role_mode' => 'existing', 'role_id' => '', 'new_role_name' => '', 'new_perms' => []];
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $err = 'خطا در ساخت کاربر. دوباره تلاش کنید.';
        }
    }
}

// نقش‌های موجودِ همین شعبه
$roleStmt = $pdo->prepare('SELECT id, name, permissions, is_preset FROM dashboard_roles WHERE branch_id = ? ORDER BY is_preset DESC, name ASC');
$roleStmt->execute([$BRANCH_ID]);
$roles = $roleStmt->fetchAll();

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
        <input type="password" name="password" placeholder="حداقل ۸ کاراکتر" required dir="ltr">
      </div>
    </div>

    <div class="card">
      <h2>نقش و دسترسی</h2>
      <div class="hint">یک نقش موجود انتخاب کنید یا نقش جدیدی با دسترسی‌های دلخواه بسازید.</div>

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
          <option value="">— انتخاب کنید —</option>
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
        <input type="text" name="new_role_name" value="<?= e($old['new_role_name']) ?>" placeholder="مثلاً: خبرنگار">
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
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        افزودن کاربر
      </button>
      <a class="btn btn-ghost" href="user-manage.php">مدیریت کاربران</a>
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
</script>
</div></body></html>

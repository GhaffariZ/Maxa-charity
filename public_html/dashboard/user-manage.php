<?php
/* ============================================================================
 *  مدیریت کاربران شعبه (مدیر شعبه یا مدیر مرکزی — در محدوده‌ی شعبه‌ی فعال)
 *  - فهرست کاربرانِ همین شعبه با نقش و سطح دسترسی.
 *  - فعال/غیرفعال‌کردن هر کاربر (status). کاربر غیرفعال نمی‌تواند وارد شود.
 *  امنیت: CSRF، IDOR (کاربرِ هدف باید در همین شعبه باشد)، least privilege.
 * ========================================================================== */
require_once __DIR__ . '/_guard.php';

if (!dash_is_branch_admin() && !dash_is_super()) {
    http_response_code(403);
    exit('۴۰۳ | دسترسی غیرمجاز');
}

$BRANCH_ID = dash_active_branch_id();
$me        = (int)$DASH_USER['id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $uid    = (int)($_POST['user_id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');

    // IDOR: کاربر هدف باید در همین شعبه باشد و سوپرادمین نباشد و خودِ من نباشد
    $st = $pdo->prepare('SELECT id, branch_id, is_super FROM dashboard_users WHERE id = ? LIMIT 1');
    $st->execute([$uid]);
    $target = $st->fetch();

    if (!$target || (int)$target['branch_id'] !== $BRANCH_ID || (int)$target['is_super'] === 1 || (int)$target['id'] === $me) {
        $msg = 'عملیات روی این کاربر مجاز نیست.';
    } elseif (in_array($action, ['enable', 'disable'], true)) {
        $newStatus = $action === 'enable' ? 'active' : 'disabled';
        // قید مجدد branch_id در WHERE برای ایمنی مضاعف
        $pdo->prepare('UPDATE dashboard_users SET status = ?, failed_attempts = 0, locked_until = NULL WHERE id = ? AND branch_id = ? AND is_super = 0')
            ->execute([$newStatus, $uid, $BRANCH_ID]);
        dash_audit('user_status_changed', ['user_id' => $uid, 'status' => $newStatus, 'branch_id' => $BRANCH_ID]);
        $msg = 'وضعیت کاربر به‌روزرسانی شد.';
    } elseif ($action === 'delete') {
        if (dash_is_hq_view()) {
            $pdo->prepare('DELETE FROM dashboard_users WHERE id = ? AND branch_id = ? AND is_super = 0')
                ->execute([$uid, $BRANCH_ID]);
            dash_audit('user_deleted', ['user_id' => $uid, 'branch_id' => $BRANCH_ID]);
            $msg = 'کاربر با موفقیت از دیتابیس حذف شد.';
        } else {
            $msg = 'حذف کاربر فقط از طریق ستاد مرکزی مجاز است.';
        }
    }
}

// فقط کاربرانِ همین شعبه (سوپرادمین‌ها در فهرست نمی‌آیند)
$st = $pdo->prepare(
    "SELECT u.id, u.username, u.full_name, u.status, u.is_branch_admin, u.last_login_at, r.name AS role_name, r.permissions
       FROM dashboard_users u
       LEFT JOIN dashboard_roles r ON r.id = u.role_id
      WHERE u.branch_id = ? AND u.is_super = 0
      ORDER BY u.is_branch_admin DESC, u.created_at DESC"
);
$st->execute([$BRANCH_ID]);
$users = $st->fetchAll();

$FEATURE_LABELS = [
    'hero' => 'هیروها', 'news' => 'خبرها', 'campaigns' => 'کمپین‌ها', 'partners' => 'همکاران',
    'courses' => 'دوره‌ها', 'pages' => 'صفحات', 'financial' => 'مالی', 'feedback' => 'انتقادات', 'medical' => 'پزشکی',
];
$branchRow = dash_load_branch($BRANCH_ID);

$PANEL_TITLE = 'مدیریت کاربران';
require __DIR__ . '/_panel_head.php';
?>
  <div class="page-head">
    <span class="ph-ic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V21"/><circle cx="9.5" cy="7" r="3.5"/><path d="M21 21v-1.5a4 4 0 0 0-3-3.87"/></svg></span>
    <div><h1>مدیریت کاربران</h1><p>کاربران شعبه‌ی «<?= e($branchRow['name'] ?? '') ?>».</p></div>
  </div>

  <?php if ($msg): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>

  <div class="card" style="padding:14px 18px">
    <table class="tbl">
      <thead><tr><th>کاربر</th><th>نقش</th><th>دسترسی‌ها</th><th>آخرین ورود</th><th>وضعیت</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($users as $u):
        $active = $u['status'] === 'active';
        $isAdmin = (int)$u['is_branch_admin'] === 1;
        if ($isAdmin) { $permsTxt = 'همه (مدیر شعبه)'; }
        else {
          $perms = (array)json_decode((string)$u['permissions'], true) ?: [];
          $permsTxt = $perms ? implode('، ', array_map(fn($p)=>$FEATURE_LABELS[$p] ?? $p, $perms)) : '—';
        } ?>
        <tr>
          <td data-label="کاربر">
            <div style="font-weight:700"><?= e($u['full_name'] ?: $u['username']) ?></div>
            <div style="font-size:11px;color:var(--color-muted)" dir="ltr"><?= e($u['username']) ?></div>
          </td>
          <td data-label="نقش"><?= $isAdmin ? '<span class="badge hq">مدیر شعبه</span>' : e($u['role_name'] ?: '—') ?></td>
          <td data-label="دسترسی‌ها" style="font-size:12px;color:var(--color-muted)"><?= e($permsTxt) ?></td>
          <td data-label="آخرین ورود" style="font-size:12px;color:var(--color-muted)"><?= $u['last_login_at'] ? e($u['last_login_at']) : 'بدون ورود' ?></td>
          <td data-label="وضعیت"><?= $active ? '<span class="badge ok">فعال</span>' : '<span class="badge off">غیرفعال</span>' ?></td>
          <td data-label="عملیات" style="text-align:left">
            <?php if (!$isAdmin): ?>
              <div style="display:inline-flex;gap:8px;flex-wrap:wrap;justify-content:flex-end">
                <a class="tbtn" href="user-edit.php?id=<?= (int)$u['id'] ?>">
                  <svg class="ic" style="width:15px;height:15px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                  ویرایش
                </a>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <input type="hidden" name="action" value="<?= $active ? 'disable' : 'enable' ?>">
                  <button class="tbtn <?= $active ? 'danger' : '' ?>" type="submit"><?= $active ? 'غیرفعال‌سازی' : 'فعال‌سازی' ?></button>
                </form>
                <?php if (dash_is_hq_view()): ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('آیا از حذف دائم این همکار اطمینان دارید؟ این عملیات قابل بازگشت نیست و کاربر از دیتابیس حذف می‌شود.');">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <button class="tbtn danger" type="submit">حذف</button>
                </form>
                <?php endif; ?>
              </div>
            <?php else: ?><span style="color:var(--color-muted);font-size:12px">—</span><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$users): ?><tr><td colspan="6" style="text-align:center;color:var(--color-muted)">هنوز کاربری برای این شعبه ساخته نشده است.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="actions">
    <a class="btn btn-primary" href="user-add.php">
      <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      افزودن کاربر
    </a>
  </div>
</div></body></html>

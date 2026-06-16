<?php
/* ============================================================================
 *  مدیریت شعبه‌ها (فقط مدیر مرکزی)
 *  - فهرست شعبه‌ها با وضعیت، تگ، و آدرس عمومی.
 *  - فعال/غیرفعال‌کردن شعبه (دفتر مرکزی قابل غیرفعال‌کردن نیست).
 * ========================================================================== */
require_once __DIR__ . '/_guard.php';

if (!dash_is_super()) {
    http_response_code(403);
    exit('۴۰۳ | فقط مدیر مرکزی به این بخش دسترسی دارد.');
}
// بخش «شعب» فقط از «ستاد مرکزی» در دسترس است
dash_require_hq();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $bid    = (int)($_POST['branch_id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');
    $b = dash_load_branch($bid);
    if ($b && (int)$b['is_hq'] !== 1 && in_array($action, ['enable', 'disable'], true)) {
        $newStatus = $action === 'enable' ? 'active' : 'disabled';
        $pdo->prepare('UPDATE branches SET status = ? WHERE id = ? AND is_hq = 0')->execute([$newStatus, $bid]);
        // وضعیتِ کاربرانِ شعبه را با شعبه همگام می‌کنیم.
        // نکته: مدلِ ساده‌ی status تفکیکی بین «غیرفعالِ شعبه» و «غیرفعالِ دستیِ ادمین» ندارد؛
        // پس فعال‌سازیِ مجددِ شعبه، همه‌ی کاربرانِ غیرسوپرِ آن را هم فعال می‌کند (رفعِ باگِ
        // قبلی که کاربران پس از فعال‌سازیِ شعبه برای همیشه غیرفعال می‌ماندند).
        if ($newStatus === 'disabled') {
            $pdo->prepare("UPDATE dashboard_users SET status='disabled' WHERE branch_id = ? AND is_super = 0")->execute([$bid]);
        } else {
            $pdo->prepare("UPDATE dashboard_users SET status='active', failed_attempts=0, locked_until=NULL WHERE branch_id = ? AND is_super = 0")->execute([$bid]);
        }
        dash_audit('branch_status_changed', ['branch_id' => $bid, 'status' => $newStatus]);
        $msg = 'وضعیت شعبه به‌روزرسانی شد.';
    } else {
        $msg = 'عملیات نامعتبر بود.';
    }
}

// فهرست شعبه‌ها + شمارش کاربران و کمپین‌ها
$rows = $pdo->query(
    "SELECT b.*,
            (SELECT COUNT(*) FROM dashboard_users u WHERE u.branch_id = b.id) AS users_cnt,
            (SELECT COUNT(*) FROM campaigns c WHERE c.branch_id = b.id)       AS camp_cnt
       FROM branches b ORDER BY b.is_hq DESC, b.name ASC"
)->fetchAll();

$PANEL_TITLE = 'مدیریت شعبه‌ها';
require __DIR__ . '/_panel_head.php';
?>
  <div class="page-head">
    <span class="ph-ic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="2.4"/><circle cx="18" cy="6" r="2.4"/><circle cx="12" cy="18" r="2.4"/><path d="M7.6 7.8 11 15.6"/><path d="M16.4 7.8 13 15.6"/></svg></span>
    <div><h1>مدیریت شعبه‌ها</h1><p>فهرست شعبه‌های تعریف‌شده و وضعیت آن‌ها.</p></div>
  </div>

  <?php if ($msg): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>

  <div class="card" style="padding:14px 18px">
    <table class="tbl">
      <thead><tr><th>نام شعبه</th><th>تگ / آدرس</th><th>کاربران</th><th>کمپین‌ها</th><th>وضعیت</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r):
        $isHq = (int)$r['is_hq'] === 1;
        $active = $r['status'] === 'active'; ?>
        <tr>
          <td data-label="نام شعبه" style="font-weight:700"><?= e($r['name']) ?> <?php if ($isHq): ?><span class="badge hq">مرکزی</span><?php endif; ?></td>
          <td data-label="تگ / آدرس" dir="ltr" style="text-align:right"><span class="badge tag">/<?= e($r['slug']) ?></span></td>
          <td data-label="کاربران"><?= (int)$r['users_cnt'] ?></td>
          <td data-label="کمپین‌ها"><?= (int)$r['camp_cnt'] ?></td>
          <td data-label="وضعیت">
            <?php if ($active): ?><span class="badge ok">فعال</span>
            <?php else: ?><span class="badge off">غیرفعال</span><?php endif; ?>
          </td>
          <td data-label="عملیات" style="text-align:left">
            <div style="display:inline-flex;gap:8px;flex-wrap:wrap;justify-content:flex-end">
              <a class="tbtn" href="branch-edit.php?id=<?= (int)$r['id'] ?>">
                <svg class="ic" style="width:15px;height:15px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                ویرایش
              </a>
              <?php if (!$isHq): ?>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="branch_id" value="<?= (int)$r['id'] ?>">
                  <input type="hidden" name="action" value="<?= $active ? 'disable' : 'enable' ?>">
                  <button class="tbtn <?= $active ? 'danger' : '' ?>" type="submit">
                    <?= $active ? 'غیرفعال‌سازی' : 'فعال‌سازی' ?>
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" style="text-align:center;color:var(--color-muted)">شعبه‌ای ثبت نشده است.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="actions">
    <a class="btn btn-primary" href="branch-create.php">
      <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      تعریف شعبه‌ی جدید
    </a>
  </div>
</div></body></html>

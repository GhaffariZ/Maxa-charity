<?php
/* ============================================================================
 *  تغییر شعبه‌ی فعال (فقط سوپرادمین) — Box 1
 * ----------------------------------------------------------------------------
 *  امنیت: CSRF + بررسی سوپرادمین + اعتبارسنجی وجود شعبه. غیرسوپرادمین هرگز
 *  نمی‌تواند شعبه‌ی فعال را تغییر دهد (dash_set_active_branch خودش چک می‌کند).
 * ========================================================================== */
require_once __DIR__ . '/_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/index.php');
    exit;
}
csrf_check();

if (!dash_is_super()) {
    http_response_code(403);
    exit('۴۰۳ | فقط مدیر مرکزی می‌تواند شعبه را تغییر دهد.');
}

$branchId = (int)($_POST['branch_id'] ?? 0);
if (dash_set_active_branch($branchId)) {
    dash_audit('switch_active_branch', ['branch_id' => $branchId]);
}

header('Location: /dashboard/index.php');
exit;

<?php
/* ============================================================================
 *  تغییر وضعیتِ خبر (گردش‌کارِ تحریریه) — امن‌سازی‌شده
 * ----------------------------------------------------------------------------
 *  قواعد:
 *    - review   : نویسنده می‌تواند خبرِ شعبه‌ی خودش را برای سردبیر بفرستد.
 *    - published/rejected : فقط سردبیر/ادمینِ ستاد مرکزی (dash_is_news_editor()).
 *    - ایزولاسیون: نویسنده فقط روی اخبارِ شعبه‌ی خودش؛ سردبیر روی همه‌ی شعب.
 *  امنیت: CSRF + اعتبارسنجیِ وضعیت + بررسیِ مالکیتِ شعبه (IDOR).
 * ========================================================================== */
require_once __DIR__ . '/_guard.php';
dash_require('news');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'متد نامعتبر']);
    exit;
}
csrf_check();

$id     = (int)($_POST['id'] ?? 0);
$status = (string)($_POST['status'] ?? '');
$reason = trim((string)($_POST['reason'] ?? ''));

$allowed = ['review', 'published', 'rejected'];
if ($id <= 0 || !in_array($status, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'درخواست نامعتبر']);
    exit;
}

$isEditor = dash_is_news_editor();
$branchId = dash_active_branch_id();

// خبرِ هدف را با شعبه‌اش بخوان
$st = $pdo->prepare("SELECT id, branch_id, status FROM news WHERE id = ? LIMIT 1");
$st->execute([$id]);
$news = $st->fetch(PDO::FETCH_ASSOC);
if (!$news) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'خبر یافت نشد']);
    exit;
}

// مجوزها
if ($status === 'review') {
    // نویسنده فقط روی خبرِ شعبه‌ی خودش (سردبیر هم مجاز است ولی معمولاً لازم نیست)
    if (!$isEditor && (int)$news['branch_id'] !== $branchId) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'دسترسی غیرمجاز']);
        exit;
    }
} else {
    // published / rejected فقط سردبیر/ادمینِ ستاد
    if (!$isEditor) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'فقط سردبیر می‌تواند خبر را تایید یا رد کند.']);
        exit;
    }
}

// عدم تایید نیازمندِ دلیل است
$rejectReason = $status === 'rejected' ? $reason : '';
if ($status === 'rejected' && $rejectReason === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'دلیلِ عدم تایید الزامی است']);
    exit;
}

try {
    $pdo->prepare("UPDATE news SET status = ?, reject_reason = ? WHERE id = ?")
        ->execute([$status, $rejectReason, $id]);
    dash_audit('news_status_changed', ['news_id' => $id, 'status' => $status, 'branch_id' => (int)$news['branch_id']]);
    echo json_encode(['status' => 'success']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'خطا در به‌روزرسانی']);
}

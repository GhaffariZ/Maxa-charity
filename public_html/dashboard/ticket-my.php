<?php
/* ============================================================================
 *  فهرست تیکت‌های کاربرِ واردشده (پنل حامیان) + پاسخ‌ها — به‌صورت JSON
 *  خروجی: { status, data: [ { ...ticket, status_fa, category_fa, created_fa,
 *                              replies: [ {author, message, created_fa} ] } ] }
 * ========================================================================== */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '0');

require __DIR__ . '/ticket-db.php';

if (!$pdo) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => 'خطا در اتصال به دیتابیس.'], JSON_UNESCAPED_UNICODE);
  exit;
}

$user = ticket_current_user($pdo);
if (!$user) {
  // کاربر واردنشده تیکتی برای نمایش ندارد (تیکت‌های مهمان به حساب گره نمی‌خورند).
  echo json_encode(['status' => 'success', 'data' => []], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $tickets = ticket_for_user($pdo, (int)$user['id']);
  $replies = ticket_replies_for($pdo, array_column($tickets, 'id'));

  $data = array_map(static function (array $t) use ($replies): array {
    $tid = (int)$t['id'];
    $t['status_fa']   = TICKET_STATUS_FA[$t['status']]     ?? $t['status'];
    $t['category_fa'] = TICKET_CATEGORY_FA[$t['category']] ?? $t['category'];
    $t['priority_fa'] = TICKET_PRIORITY_FA[$t['priority']] ?? $t['priority'];
    $t['created_fa']  = jalali_datetime($t['created_at']);
    $t['replies']     = array_map(static function (array $r): array {
      return [
        'author'     => $r['author'],
        'message'    => $r['message'],
        'created_fa' => jalali_datetime($r['created_at']),
      ];
    }, $replies[$tid] ?? []);
    return $t;
  }, $tickets);

  echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => 'خطا در دریافت تیکت‌ها.'], JSON_UNESCAPED_UNICODE);
}

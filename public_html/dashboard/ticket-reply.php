<?php
/* ============================================================================
 *  ثبت پاسخِ مدیر برای یک تیکت (از صفحه‌ی مدیریت تیکت‌ها)
 *  ورودی: POST — id, message
 *  خروجی: JSON
 * ========================================================================== */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '0');

require __DIR__ . '/ticket-db.php';

function fail(string $msg, int $code = 400): void {
  http_response_code($code);
  echo json_encode(['status' => 'error', 'message' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('متد نامعتبر است.', 405);
if (!$pdo) fail('خطا در اتصال به دیتابیس.', 500);

$id      = (int)($_POST['id'] ?? 0);
$message = trim((string)($_POST['message'] ?? ''));

if ($id <= 0)                 fail('شناسه‌ی تیکت نامعتبر است.');
if (mb_strlen($message) < 1)  fail('متن پاسخ را وارد کنید.');
if (mb_strlen($message) > 5000) fail('متن پاسخ بیش از حد طولانی است.');

$ticket = ticket_find($pdo, $id);
if (!$ticket) fail('تیکت یافت نشد.', 404);

try {
  $replyId = ticket_add_reply($pdo, $id, 'admin', $message);
} catch (Throwable $e) {
  fail('ثبت پاسخ با خطا مواجه شد.', 500);
}

echo json_encode([
  'status'     => 'success',
  'message'    => 'پاسخ شما ثبت شد.',
  'reply_id'   => $replyId,
  'created_fa' => jalali_datetime('now'),
  'new_status' => 'answered',
], JSON_UNESCAPED_UNICODE);

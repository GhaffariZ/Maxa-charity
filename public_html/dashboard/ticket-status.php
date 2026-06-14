<?php
/* ============================================================================
 *  تغییر وضعیت تیکت (باز/بسته) و علامت‌گذاری به‌عنوان خوانده‌شده توسط مدیر
 *  ورودی: POST — id, status (open|answered|closed) یا action=mark_read
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

$id     = (int)($_POST['id'] ?? 0);
$action = (string)($_POST['action'] ?? '');
$status = (string)($_POST['status'] ?? '');

if ($id <= 0) fail('شناسه‌ی تیکت نامعتبر است.');
if (!ticket_find($pdo, $id)) fail('تیکت یافت نشد.', 404);

try {
  if ($action === 'mark_read') {
    ticket_mark_admin_read($pdo, $id);
    echo json_encode(['status' => 'success', 'message' => 'به‌عنوان خوانده‌شده علامت خورد.'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  if (!in_array($status, TICKET_STATUSES, true)) fail('وضعیت نامعتبر است.');
  ticket_set_status($pdo, $id, $status);
  echo json_encode([
    'status'     => 'success',
    'message'    => 'وضعیت تیکت به‌روزرسانی شد.',
    'new_status' => $status,
    'status_fa'  => TICKET_STATUS_FA[$status] ?? $status,
  ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  fail('به‌روزرسانی وضعیت با خطا مواجه شد.', 500);
}

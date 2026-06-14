<?php
/* ============================================================================
 *  ثبت تیکت جدید از سمت کاربر (پنل حامیان یا بازدیدکننده)
 *  ورودی: POST (فرم یا JSON) — subject, message, category, priority, name?, contact?
 *  خروجی: JSON
 * ========================================================================== */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '0'); // جلوگیری از خراب شدن خروجی JSON

require __DIR__ . '/ticket-db.php';

function fail(string $msg, int $code = 400): void {
  http_response_code($code);
  echo json_encode(['status' => 'error', 'message' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  fail('متد نامعتبر است.', 405);
}
if (!$pdo) {
  fail('خطا در اتصال به دیتابیس.', 500);
}

/* ---------- خواندن ورودی (هم فرم، هم JSON) ---------- */
$input = $_POST;
if (empty($input)) {
  $raw = file_get_contents('php://input');
  if ($raw) {
    $json = json_decode($raw, true);
    if (is_array($json)) $input = $json;
  }
}

$subject  = trim((string)($input['subject']  ?? ''));
$message  = trim((string)($input['message']  ?? ''));
$category = (string)($input['category'] ?? 'general');
$priority = (string)($input['priority'] ?? 'normal');
$name     = trim((string)($input['name']    ?? ''));
$contact  = trim((string)($input['contact'] ?? ''));

/* ---------- شناسایی کاربرِ واردشده ---------- */
$user = ticket_current_user($pdo);
if ($user) {
  // نام و راه ارتباطی از حساب کاربری گرفته می‌شود مگر آنکه کاربر مقداری وارد کرده باشد.
  if ($name === '')    $name    = (string)($user['full_name'] ?: $user['username']);
  if ($contact === '') $contact = (string)($user['phone'] ?? '');
}

/* ---------- اعتبارسنجی ---------- */
if (mb_strlen($subject) < 3)  fail('موضوع تیکت را وارد کنید (حداقل ۳ نویسه).');
if (mb_strlen($subject) > 200) fail('موضوع تیکت بیش از حد طولانی است.');
if (mb_strlen($message) < 5)  fail('متن پیام را وارد کنید (حداقل ۵ نویسه).');
if (mb_strlen($message) > 5000) fail('متن پیام بیش از حد طولانی است.');
if ($name === '')             fail('نام خود را وارد کنید.');
if (!in_array($category, TICKET_CATEGORIES, true)) $category = 'general';
if (!in_array($priority, TICKET_PRIORITIES, true)) $priority = 'normal';

try {
  $id = ticket_create($pdo, [
    'user_id'  => $user['id'] ?? null,
    'name'     => mb_substr($name, 0, 120),
    'contact'  => $contact !== '' ? mb_substr($contact, 0, 255) : null,
    'subject'  => $subject,
    'category' => $category,
    'priority' => $priority,
    'message'  => $message,
  ]);
} catch (Throwable $e) {
  fail('ثبت تیکت با خطا مواجه شد.', 500);
}

echo json_encode([
  'status'  => 'success',
  'message' => 'تیکت شما با موفقیت ثبت شد و پس از بررسی پاسخ داده می‌شود.',
  'id'      => $id,
], JSON_UNESCAPED_UNICODE);

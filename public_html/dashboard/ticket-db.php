<?php
/* ============================================================================
 *  لایه‌ی داده‌ی «تیکت‌ها / پشتیبانی» — خیریه مکسا
 *  این فایل توسط صفحه‌ی مدیریت تیکت‌ها، پنل حامیان و هندلرهای تیکت include می‌شود
 *  و موارد زیر را فراهم می‌کند:
 *    • اتصال به دیتابیس (هم‌سنگ با index/course-db)
 *    • ساخت خودکار جداول تیکت‌ها (در صورت نبودن)
 *    • توابع کمکی مشترک (اتصال، CRUD تیکت‌ها، شناسایی کاربر پنل)
 *  ساختار جداول در database/migrations/2026_06_14_create_tickets.sql نسخه‌بندی شده است.
 * ========================================================================== */
declare(strict_types=1);

if (!defined('MAXA_TICKETS_BOOT')) {
  define('MAXA_TICKETS_BOOT', 1);
  mb_internal_encoding('UTF-8');
  date_default_timezone_set('Asia/Tehran');
}

/* ---------- اتصال (همانند index/course-db) ---------- */
$DB = require __DIR__ . '/../core/db-config.php';

$pdo = null; $dbError = null;
try {
  $opts = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];
  if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
    $opts[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$DB['charset']}";
  }
  $pdo = new PDO("mysql:host={$DB['host']};dbname={$DB['name']}", $DB['user'], $DB['pass'], $opts);
  try { $pdo->exec("SET NAMES {$DB['charset']}"); } catch (Throwable $e) { $pdo->exec("SET NAMES utf8"); }
} catch (Throwable $e) {
  $dbError = $e->getMessage();
}

/* ---------- توابع کمکی مشترک (در صورت نبودن) ---------- */
if (!function_exists('q')) {
  function q(PDO $pdo, string $sql, array $p = []): PDOStatement {
    $st = $pdo->prepare($sql); $st->execute($p); return $st;
  }
}
if (!function_exists('fa_digits')) {
  function fa_digits($s): string {
    return strtr((string)$s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹','.'=>'٫']);
  }
}
if (!function_exists('gregorian_to_jalali')) {
  function gregorian_to_jalali(int $gy, int $gm, int $gd): array {
    $g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100)
          + intdiv($gy2 + 399, 400) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * intdiv($days, 12053)); $days %= 12053;
    $jy += 4 * intdiv($days, 1461); $days %= 1461;
    if ($days > 365) { $jy += intdiv($days - 1, 365); $days = ($days - 1) % 365; }
    if ($days < 186) { $jm = 1 + intdiv($days, 31); $jd = 1 + ($days % 31); }
    else { $jm = 7 + intdiv($days - 186, 30); $jd = 1 + (($days - 186) % 30); }
    return [$jy, $jm, $jd];
  }
}
if (!function_exists('jalali_datetime')) {
  function jalali_datetime($ts): string {
    $ts = is_numeric($ts) ? (int)$ts : strtotime((string)$ts);
    if (!$ts) return '—';
    list($jy, $jm, $jd) = gregorian_to_jalali((int)date('Y',$ts), (int)date('n',$ts), (int)date('j',$ts));
    return fa_digits(sprintf('%04d/%02d/%02d – %02d:%02d', $jy, $jm, $jd, (int)date('H',$ts), (int)date('i',$ts)));
  }
}
if (!function_exists('e')) {
  function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

/* ---------- ساخت خودکار جداول (در صورت دسترسی به دیتابیس) ---------- */
$ticketsSchemaReady = false;
if ($pdo) {
  try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
      id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      user_id      INT DEFAULT NULL,
      name         VARCHAR(120) NOT NULL,
      contact      VARCHAR(255) DEFAULT NULL,
      subject      VARCHAR(200) NOT NULL,
      category     ENUM('general','donation','technical','campaign','other') NOT NULL DEFAULT 'general',
      priority     ENUM('low','normal','high') NOT NULL DEFAULT 'normal',
      message      TEXT NOT NULL,
      status       ENUM('open','answered','closed') NOT NULL DEFAULT 'open',
      admin_unread TINYINT(1) NOT NULL DEFAULT 1,
      user_unread  TINYINT(1) NOT NULL DEFAULT 0,
      created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_tickets_user (user_id),
      INDEX idx_tickets_status (status),
      INDEX idx_tickets_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_replies (
      id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      ticket_id  BIGINT UNSIGNED NOT NULL,
      author     ENUM('admin','user') NOT NULL DEFAULT 'admin',
      message    TEXT NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_replies_ticket (ticket_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $ticketsSchemaReady = true;
  } catch (Throwable $e) {
    $dbError = $dbError ?? $e->getMessage();
  }
}

/* ---------- اعتبارسنجی مقادیر مجاز ---------- */
const TICKET_CATEGORIES = ['general','donation','technical','campaign','other'];
const TICKET_PRIORITIES = ['low','normal','high'];
const TICKET_STATUSES   = ['open','answered','closed'];

const TICKET_CATEGORY_FA = [
  'general'   => 'عمومی',
  'donation'  => 'کمک‌ها و پرداخت',
  'technical' => 'مشکل فنی',
  'campaign'  => 'کمپین‌ها',
  'other'     => 'سایر',
];
const TICKET_PRIORITY_FA = ['low' => 'کم', 'normal' => 'عادی', 'high' => 'فوری'];
const TICKET_STATUS_FA   = ['open' => 'باز', 'answered' => 'پاسخ داده شد', 'closed' => 'بسته‌شده'];

/* ---------- شناسایی کاربرِ پنل حامیان (در صورت ورود) ---------- */
/**
 * نشست را آغاز کرده و در صورت ورودِ کاربر، ردیفِ webusers را برمی‌گرداند؛
 * در غیر این صورت null. (پنل حامیان از جدول webusers استفاده می‌کند.)
 */
function ticket_current_user(?PDO $pdo): ?array {
  if (session_status() === PHP_SESSION_NONE) {
    @session_start();
  }
  if (!$pdo || empty($_SESSION['user_id'])) {
    return null;
  }
  try {
    $st = $pdo->prepare("SELECT id, username, full_name, phone FROM webusers WHERE id = ? LIMIT 1");
    $st->execute([(int)$_SESSION['user_id']]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  } catch (Throwable $e) {
    return null;
  }
}

/* ---------- عملیات تیکت ---------- */
function ticket_create(PDO $pdo, array $d): int {
  $st = $pdo->prepare(
    "INSERT INTO tickets (user_id, name, contact, subject, category, priority, message, status)
     VALUES (:user_id, :name, :contact, :subject, :category, :priority, :message, 'open')"
  );
  $st->execute([
    ':user_id'  => $d['user_id'] ?? null,
    ':name'     => $d['name'],
    ':contact'  => $d['contact'] ?? null,
    ':subject'  => $d['subject'],
    ':category' => in_array($d['category'] ?? '', TICKET_CATEGORIES, true) ? $d['category'] : 'general',
    ':priority' => in_array($d['priority'] ?? '', TICKET_PRIORITIES, true) ? $d['priority'] : 'normal',
    ':message'  => $d['message'],
  ]);
  return (int)$pdo->lastInsertId();
}

/** همه‌ی تیکت‌ها برای پنل مدیریت (اختیاری: فیلتر وضعیت). */
function ticket_all(PDO $pdo, ?string $status = null): array {
  if ($status && in_array($status, TICKET_STATUSES, true)) {
    $st = $pdo->prepare("SELECT * FROM tickets WHERE status = ? ORDER BY admin_unread DESC, updated_at DESC");
    $st->execute([$status]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }
  return $pdo->query("SELECT * FROM tickets ORDER BY admin_unread DESC, updated_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}

/** تیکت‌های یک کاربرِ مشخص (پنل حامیان). */
function ticket_for_user(PDO $pdo, int $userId): array {
  $st = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY updated_at DESC");
  $st->execute([$userId]);
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

function ticket_find(PDO $pdo, int $id): ?array {
  $st = $pdo->prepare("SELECT * FROM tickets WHERE id = ? LIMIT 1");
  $st->execute([$id]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

/** پاسخ‌های یک تیکت به‌ترتیب زمان. */
function ticket_replies(PDO $pdo, int $ticketId): array {
  $st = $pdo->prepare("SELECT * FROM ticket_replies WHERE ticket_id = ? ORDER BY created_at ASC, id ASC");
  $st->execute([$ticketId]);
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

/** پاسخ‌ها برای چند تیکت، گروه‌بندی‌شده بر اساس ticket_id (برای نمایش لیست). */
function ticket_replies_for(PDO $pdo, array $ticketIds): array {
  $ids = array_values(array_filter(array_map('intval', $ticketIds)));
  if (!$ids) return [];
  $in  = implode(',', array_fill(0, count($ids), '?'));
  $st  = $pdo->prepare("SELECT * FROM ticket_replies WHERE ticket_id IN ($in) ORDER BY created_at ASC, id ASC");
  $st->execute($ids);
  $out = [];
  foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $out[(int)$r['ticket_id']][] = $r;
  }
  return $out;
}

/**
 * افزودنِ پاسخ به تیکت.
 *  - پاسخِ مدیر  → وضعیت=answered، user_unread=1، admin_unread=0
 *  - پاسخِ کاربر → وضعیت=open،     admin_unread=1
 */
function ticket_add_reply(PDO $pdo, int $ticketId, string $author, string $message): int {
  $author = $author === 'user' ? 'user' : 'admin';
  $pdo->prepare("INSERT INTO ticket_replies (ticket_id, author, message) VALUES (?, ?, ?)")
      ->execute([$ticketId, $author, $message]);
  if ($author === 'admin') {
    $pdo->prepare("UPDATE tickets SET status='answered', admin_unread=0, user_unread=1 WHERE id=?")
        ->execute([$ticketId]);
  } else {
    $pdo->prepare("UPDATE tickets SET status='open', admin_unread=1 WHERE id=?")
        ->execute([$ticketId]);
  }
  return (int)$pdo->lastInsertId();
}

function ticket_set_status(PDO $pdo, int $ticketId, string $status): bool {
  if (!in_array($status, TICKET_STATUSES, true)) return false;
  return $pdo->prepare("UPDATE tickets SET status=? WHERE id=?")->execute([$status, $ticketId]);
}

function ticket_mark_admin_read(PDO $pdo, int $ticketId): void {
  $pdo->prepare("UPDATE tickets SET admin_unread=0 WHERE id=?")->execute([$ticketId]);
}

<?php
/* ============================================================================
 *  لایه‌ی داده‌ی «مکساپدیا» — خیریه مکسا
 *  این فایل توسط صفحه‌ی مدیریت مکساپدیا (داشبورد)، صفحه‌های عمومی مکساپدیا و
 *  هندلرهای ذخیره/حذف include می‌شود و موارد زیر را فراهم می‌کند:
 *    • اتصال به دیتابیس (هم‌سنگ با course-db / ticket-db)
 *    • ساخت خودکار جدول محتوای مکساپدیا (در صورت نبودن)
 *    • تعریف بخش‌های شش‌گانه و توابع کمکی CRUD
 *  هر «کارت» در صفحه‌ی مکساپدیا یک بخش (section) است و هر بخش می‌تواند چند
 *  آیتمِ محتوا (ویدیو، فایل، صوت، تصویر و …) داشته باشد.
 * ========================================================================== */
declare(strict_types=1);

if (!defined('MAXA_PEDIA_BOOT')) {
  define('MAXA_PEDIA_BOOT', 1);
  mb_internal_encoding('UTF-8');
  date_default_timezone_set('Asia/Tehran');
}

/* ---------- اتصال (همانند course-db / ticket-db) ---------- */
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
if (!function_exists('jalali_date')) {
  function jalali_date($ts): string {
    static $jm_names = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
    $ts = is_numeric($ts) ? (int)$ts : strtotime((string)$ts);
    if (!$ts) return '—';
    list($jy, $jm, $jd) = gregorian_to_jalali((int)date('Y',$ts), (int)date('n',$ts), (int)date('j',$ts));
    return fa_digits($jd).' '.$jm_names[$jm-1].' '.fa_digits((string)$jy);
  }
}
if (!function_exists('e')) {
  function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

/* ---------- تعریف بخش‌های شش‌گانه (هم‌راستا با کارت‌های صفحه‌ی عمومی) ---------- */
if (!defined('MAXAPEDIA_SECTIONS')) {
  define('MAXAPEDIA_SECTIONS', [
    'videos'    => ['title'=>'ویدیوهای آموزشی', 'icon'=>'🎬', 'desc'=>'مجموعه ویدیوهای آموزشی مکسا برای آشنایی با بیماری‌های نادر و مراقبت از بیماران.'],
    'brochures' => ['title'=>'بروشورها',        'icon'=>'📄', 'desc'=>'بروشورهای اطلاع‌رسانی و آموزشی قابل دانلود و چاپ.'],
    'books'     => ['title'=>'کتاب‌های آموزشی',  'icon'=>'📚', 'desc'=>'کتاب‌ها و جستارهای تخصصی در حوزه بیماری‌های نادر.'],
    'podcasts'  => ['title'=>'پادکست‌ها',        'icon'=>'🎙️', 'desc'=>'گفت‌وگوها و برنامه‌های صوتی مکسا.'],
    'clips'     => ['title'=>'کلیپ‌های مکسا',     'icon'=>'🎞️', 'desc'=>'کلیپ‌های کوتاه و انگیزشی مکسی.'],
    'gallery'   => ['title'=>'گالری مکسا',        'icon'=>'🖼️', 'desc'=>'تصاویر و خاطرات تصویری از فعالیت‌های مکسا.'],
  ]);
}

/* یک بخش معتبر است؟ */
function maxapedia_is_section(string $slug): bool {
  return array_key_exists($slug, MAXAPEDIA_SECTIONS);
}
function maxapedia_section_meta(string $slug): ?array {
  return MAXAPEDIA_SECTIONS[$slug] ?? null;
}

/* ============================================================================
 *  امبد محتوا — تبدیل لینکِ ورودی (واچ/اشتراک/امبد) به پخش‌کننده‌ی قابل‌نمایش.
 *  پشتیبانی: یوتیوب، آپارات، نماشا (ویدیو) و اسپاتیفای، کست‌باکس، ساندکلاد (صوت)
 *  و نیز فایل‌های مستقیم رسانه‌ای (mp4/mp3/…). برای میزبان‌های ناشناخته null
 *  برمی‌گرداند تا فقط به‌صورت لینک نمایش داده شود (نه iframe دلخواه).
 * ========================================================================== */

/* خروجی: ['type'=>'iframe|video|audio','kind'=>'video|audio','provider'=>..,'src'=>..] یا null */
function maxapedia_embed(string $url): ?array {
  $url = trim($url);
  if ($url === '') { return null; }

  /* فایل‌های مستقیم رسانه‌ای */
  if (preg_match('~\.(mp4|webm|ogv|mov)(\?.*)?$~i', $url)) {
    return ['type'=>'video', 'kind'=>'video', 'provider'=>'فایل ویدیویی', 'src'=>$url];
  }
  if (preg_match('~\.(mp3|m4a|aac|ogg|oga|wav|flac)(\?.*)?$~i', $url)) {
    return ['type'=>'audio', 'kind'=>'audio', 'provider'=>'فایل صوتی', 'src'=>$url];
  }

  $host = strtolower((string)parse_url($url, PHP_URL_HOST));
  $host = preg_replace('~^www\.~', '', $host);

  /* یوتیوب: watch / youtu.be / embed / shorts / live */
  if (preg_match('~(?:youtube\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
    return ['type'=>'iframe', 'kind'=>'video', 'provider'=>'یوتیوب',
            'src'=>'https://www.youtube.com/embed/'.$m[1],
            'poster'=>'https://i.ytimg.com/vi/'.$m[1].'/hqdefault.jpg'];
  }

  /* آپارات: /v/HASH یا لینک امبد */
  if ($host === 'aparat.com' && preg_match('~/(?:v|video/video/embed/videohash)/([A-Za-z0-9]+)~', $url, $m)) {
    return ['type'=>'iframe', 'kind'=>'video', 'provider'=>'آپارات', 'src'=>'https://www.aparat.com/video/video/embed/videohash/'.$m[1].'/vt/frame'];
  }

  /* نماشا */
  if ($host === 'namasha.com' && preg_match('~/(?:v/|embed/)?([A-Za-z0-9]+)~', $url, $m)) {
    return ['type'=>'iframe', 'kind'=>'video', 'provider'=>'نماشا', 'src'=>'https://namasha.com/embed/'.$m[1]];
  }

  /* اسپاتیفای: track/episode/show/playlist/album */
  if ($host === 'open.spotify.com' && preg_match('~/(?:embed/)?(track|episode|show|playlist|album)/([A-Za-z0-9]+)~', $url, $m)) {
    return ['type'=>'iframe', 'kind'=>'audio', 'provider'=>'اسپاتیفای', 'src'=>'https://open.spotify.com/embed/'.$m[1].'/'.$m[2]];
  }

  /* کست‌باکس: لینکِ پلیر آماده، یا استخراج id کانال/قسمت از لینک اشتراک */
  if ($host === 'castbox.fm') {
    if (strpos($url, '/app/castbox/player/') !== false) {
      return ['type'=>'iframe', 'kind'=>'audio', 'provider'=>'کست‌باکس', 'src'=>$url];
    }
    if (preg_match_all('~id(\d+)~', $url, $mm) && count($mm[1]) >= 2) {
      return ['type'=>'iframe', 'kind'=>'audio', 'provider'=>'کست‌باکس',
              'src'=>'https://castbox.fm/app/castbox/player/id'.$mm[1][0].'/id'.$mm[1][1].'?v=8.22.11&autoplay=0'];
    }
  }

  /* ساندکلاد */
  if ($host === 'w.soundcloud.com') {
    return ['type'=>'iframe', 'kind'=>'audio', 'provider'=>'ساندکلاد', 'src'=>$url];
  }
  if ($host === 'soundcloud.com') {
    return ['type'=>'iframe', 'kind'=>'audio', 'provider'=>'ساندکلاد',
            'src'=>'https://w.soundcloud.com/player/?url='.rawurlencode($url).'&color=%23007b7a&auto_play=false&show_comments=false&visual=false'];
  }

  return null;
}

/* HTML آماده‌ی نمایشِ امبد (یا null اگر لینک قابل‌امبد نباشد). کلاس‌ها بین داشبورد
 * و صفحه‌ی عمومی مشترک‌اند؛ استایلِ هر طرف در همان صفحه تعریف شده است. */
function maxapedia_embed_html(string $url, string $title = ''): ?string {
  $em = maxapedia_embed($url);
  if (!$em) { return null; }
  $src = htmlspecialchars($em['src'], ENT_QUOTES, 'UTF-8');
  $ttl = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
  $cls = 'mxp-embed ' . ($em['kind'] === 'audio' ? 'mxp-embed--audio' : 'mxp-embed--video');

  if ($em['type'] === 'video') {
    return '<div class="'.$cls.'"><video controls preload="metadata" src="'.$src.'" title="'.$ttl.'"></video></div>';
  }
  if ($em['type'] === 'audio') {
    return '<div class="'.$cls.'"><audio controls preload="metadata" src="'.$src.'"></audio></div>';
  }
  return '<div class="'.$cls.'"><iframe src="'.$src.'" title="'.$ttl.'" loading="lazy"'
       . ' referrerpolicy="strict-origin-when-cross-origin"'
       . ' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"'
       . ' allowfullscreen></iframe></div>';
}

/* ---------- ساخت خودکار جدول ---------- */
$maxapediaSchemaReady = false;
if ($pdo) {
  try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS maxapedia_items (
      id          INT AUTO_INCREMENT PRIMARY KEY,
      section     VARCHAR(40)  NOT NULL,
      title       VARCHAR(255) NOT NULL,
      description MEDIUMTEXT,
      url         VARCHAR(1024) DEFAULT NULL,
      thumbnail   MEDIUMTEXT,
      status      VARCHAR(20) DEFAULT 'published',
      sort_order  INT DEFAULT 0,
      created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX(section), INDEX(status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $maxapediaSchemaReady = true;
  } catch (Throwable $e) {
    $dbError = $dbError ?? $e->getMessage();
  }
}

/* ---------- CRUD ---------- */

/* همه‌ی آیتم‌های یک بخش (اختیاری: فقط منتشرشده‌ها) */
function maxapedia_items(PDO $pdo, string $section, bool $onlyPublished = false): array {
  $sql = "SELECT * FROM maxapedia_items WHERE section = ?";
  $p = [$section];
  if ($onlyPublished) { $sql .= " AND status = 'published'"; }
  $sql .= " ORDER BY sort_order ASC, created_at DESC, id DESC";
  return q($pdo, $sql, $p)->fetchAll();
}

/* شمارش آیتم‌های هر بخش — برای نمایش در داشبورد (map: section => count) */
function maxapedia_counts(PDO $pdo): array {
  $rows = q($pdo, "SELECT section, COUNT(*) AS c FROM maxapedia_items GROUP BY section")->fetchAll();
  $map = [];
  foreach ($rows as $r) { $map[$r['section']] = (int)$r['c']; }
  return $map;
}

function maxapedia_find(PDO $pdo, int $id): ?array {
  $row = q($pdo, "SELECT * FROM maxapedia_items WHERE id = ?", [$id])->fetch();
  return $row ?: null;
}

function maxapedia_create(PDO $pdo, array $d): int {
  $section = (string)($d['section'] ?? '');
  if (!maxapedia_is_section($section)) { $section = 'videos'; }
  $status = in_array(($d['status'] ?? ''), ['published','draft'], true) ? $d['status'] : 'published';
  q($pdo,
    "INSERT INTO maxapedia_items (section, title, description, url, thumbnail, status, sort_order)
     VALUES (?,?,?,?,?,?,?)",
    [
      $section,
      (string)($d['title'] ?? ''),
      (string)($d['description'] ?? ''),
      (string)($d['url'] ?? ''),
      (string)($d['thumbnail'] ?? ''),
      $status,
      (int)($d['sort_order'] ?? 0),
    ]
  );
  return (int)$pdo->lastInsertId();
}

function maxapedia_update(PDO $pdo, int $id, array $d): bool {
  $status = in_array(($d['status'] ?? ''), ['published','draft'], true) ? $d['status'] : 'published';
  return q($pdo,
    "UPDATE maxapedia_items
        SET title=?, description=?, url=?, thumbnail=?, status=?, sort_order=?
      WHERE id=?",
    [
      (string)($d['title'] ?? ''),
      (string)($d['description'] ?? ''),
      (string)($d['url'] ?? ''),
      (string)($d['thumbnail'] ?? ''),
      $status,
      (int)($d['sort_order'] ?? 0),
      $id,
    ]
  )->rowCount() >= 0;
}

function maxapedia_delete(PDO $pdo, int $id): bool {
  return q($pdo, "DELETE FROM maxapedia_items WHERE id = ?", [$id])->rowCount() > 0;
}

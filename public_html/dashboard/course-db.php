<?php
/* ============================================================================
 *  لایه‌ی داده‌ی «دوره‌ها» — خیریه/آکادمی مکسا
 *  این فایل توسط همه‌ی صفحات دوره‌ها include می‌شود و موارد زیر را فراهم می‌کند:
 *    • اتصال به دیتابیس (هم‌سنگ با index)
 *    • ساخت خودکار جداول دوره‌ها (در صورت نبودن)
 *    • توابع کمکی مشترک (اعداد فارسی، قیمت، تاریخ شمسی، آیکن‌ها)
 *    • داده‌ی نمونه به‌عنوان fallback تا صفحات همیشه رندر شوند
 * ========================================================================== */
declare(strict_types=1);
if (!defined('MAXA_COURSES_BOOT')) {
  define('MAXA_COURSES_BOOT', 1);
  mb_internal_encoding('UTF-8');
  date_default_timezone_set('Asia/Tehran');
}

/* ---------- تنظیمات اتصال (همانند index) ---------- */
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

/* ---------- توابع کمکی ---------- */
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
if (!function_exists('toman')) {
  function toman($v): string { return fa_digits(number_format((float)$v)).' تومان'; }
}
if (!function_exists('e')) {
  function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

/* ---------- ساخت خودکار جداول (در صورت دسترسی به دیتابیس) ---------- */
$coursesSchemaReady = false;
if ($pdo) {
  try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
      id            INT AUTO_INCREMENT PRIMARY KEY,
      title         VARCHAR(255) NOT NULL,
      slug          VARCHAR(255) DEFAULT NULL,
      subtitle      VARCHAR(512) DEFAULT NULL,
      description   MEDIUMTEXT,
      category      VARCHAR(120) DEFAULT NULL,
      level         VARCHAR(40)  DEFAULT 'all',
      language      VARCHAR(40)  DEFAULT 'fa',
      price         DECIMAL(12,0) DEFAULT 0,
      discount_price DECIMAL(12,0) DEFAULT NULL,
      is_free       TINYINT(1) DEFAULT 0,
      thumbnail     MEDIUMTEXT,
      promo_video   VARCHAR(512) DEFAULT NULL,
      instructor    VARCHAR(160) DEFAULT NULL,
      instructor_bio VARCHAR(1024) DEFAULT NULL,
      what_you_learn MEDIUMTEXT,
      requirements   MEDIUMTEXT,
      status        VARCHAR(20) DEFAULT 'draft',
      students      INT DEFAULT 0,
      rating        DECIMAL(3,2) DEFAULT 0,
      created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS course_sections (
      id         INT AUTO_INCREMENT PRIMARY KEY,
      course_id  INT NOT NULL,
      title      VARCHAR(255) NOT NULL,
      sort_order INT DEFAULT 0,
      INDEX(course_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS course_lessons (
      id          INT AUTO_INCREMENT PRIMARY KEY,
      section_id  INT NOT NULL,
      course_id   INT NOT NULL,
      title       VARCHAR(255) NOT NULL,
      type        VARCHAR(20) DEFAULT 'video',
      content     MEDIUMTEXT,
      duration    INT DEFAULT 0,
      is_preview  TINYINT(1) DEFAULT 0,
      sort_order  INT DEFAULT 0,
      INDEX(section_id), INDEX(course_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS course_enrollments (
      id          INT AUTO_INCREMENT PRIMARY KEY,
      course_id   INT NOT NULL,
      user_id     INT DEFAULT NULL,
      progress    INT DEFAULT 0,
      price_paid  DECIMAL(12,0) DEFAULT 0,
      created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
      INDEX(course_id), INDEX(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $coursesSchemaReady = true;
  } catch (Throwable $e) {
    $dbError = $dbError ?? $e->getMessage();
  }
}

/* ---------- داده‌ی نمونه (fallback وقتی دیتابیس در دسترس نیست یا خالی است) ---------- */
function maxa_demo_courses(): array {
  return [
    [
      'id'=>1,'title'=>'دوره جامع طراحی وب با HTML و CSS','subtitle'=>'از صفر تا ساخت وب‌سایت واکنش‌گرا و حرفه‌ای',
      'category'=>'برنامه‌نویسی','level'=>'beginner','price'=>1290000,'discount_price'=>790000,'is_free'=>0,
      'instructor'=>'مهندس سارا کریمی','rating'=>4.8,'students'=>2431,'status'=>'published',
      'thumbnail'=>'','duration'=>740,'lessons'=>52,
      'accent'=>'#007b7a','created_at'=>'2025-09-12',
    ],
    [
      'id'=>2,'title'=>'مبانی هوش مصنوعی و یادگیری ماشین','subtitle'=>'مفاهیم پایه تا پیاده‌سازی مدل‌های واقعی با پایتون',
      'category'=>'هوش مصنوعی','level'=>'intermediate','price'=>1990000,'discount_price'=>null,'is_free'=>0,
      'instructor'=>'دکتر امیر رضایی','rating'=>4.9,'students'=>1875,'status'=>'published',
      'thumbnail'=>'','duration'=>1120,'lessons'=>68,
      'accent'=>'#7c4ddb','created_at'=>'2025-10-02',
    ],
    [
      'id'=>3,'title'=>'مهارت‌های ارتباط مؤثر و کار تیمی','subtitle'=>'دوره‌ی رایگان توسعه‌ی فردی برای داوطلبان خیریه',
      'category'=>'توسعه فردی','level'=>'all','price'=>0,'discount_price'=>null,'is_free'=>1,
      'instructor'=>'مریم حسینی','rating'=>4.7,'students'=>3902,'status'=>'published',
      'thumbnail'=>'','duration'=>320,'lessons'=>24,
      'accent'=>'#f4a61e','created_at'=>'2025-08-21',
    ],
    [
      'id'=>4,'title'=>'دوره مقدماتی زبان انگلیسی کاربردی','subtitle'=>'مکالمه‌ی روزمره و گرامر پایه به‌زبان ساده',
      'category'=>'زبان','level'=>'beginner','price'=>890000,'discount_price'=>590000,'is_free'=>0,
      'instructor'=>'علی محمدی','rating'=>4.6,'students'=>1240,'status'=>'draft',
      'thumbnail'=>'','duration'=>560,'lessons'=>40,
      'accent'=>'#16a37a','created_at'=>'2025-11-15',
    ],
  ];
}

function maxa_demo_curriculum(): array {
  return [
    ['title'=>'فصل اول: مقدمه و پیش‌نیازها','lessons'=>[
      ['title'=>'معرفی دوره و سرفصل‌ها','type'=>'video','duration'=>8,'is_preview'=>1],
      ['title'=>'نصب ابزارها و آماده‌سازی محیط','type'=>'video','duration'=>14,'is_preview'=>1],
      ['title'=>'جزوه‌ی شروع به کار','type'=>'text','duration'=>5,'is_preview'=>0],
    ]],
    ['title'=>'فصل دوم: مفاهیم اصلی','lessons'=>[
      ['title'=>'ساختار پایه و عناصر','type'=>'video','duration'=>22,'is_preview'=>0],
      ['title'=>'نمودار معماری (تصویر)','type'=>'image','duration'=>3,'is_preview'=>0],
      ['title'=>'تمرین عملی شماره ۱','type'=>'text','duration'=>18,'is_preview'=>0],
    ]],
    ['title'=>'فصل سوم: پروژه‌ی پایانی','lessons'=>[
      ['title'=>'طراحی پروژه‌ی واقعی','type'=>'video','duration'=>35,'is_preview'=>0],
      ['title'=>'جمع‌بندی و گام‌های بعدی','type'=>'video','duration'=>12,'is_preview'=>0],
    ]],
  ];
}

/* بارگذاری دوره‌ها: از دیتابیس یا داده‌ی نمونه
 * $branchId: اگر null باشد همه‌ی شعب (نمای ستاد/عمومیِ مرکزی)، وگرنه فقط همان شعبه.
 * نامِ شعبه (branch_name) برای نمایشِ تگ همراه می‌شود. */
function load_courses(?PDO $pdo, bool $ready, ?string $status = null, ?int $branchId = null): array {
  if ($pdo && $ready) {
    try {
      $sql = "SELECT c.*, b.name AS branch_name, b.slug AS branch_slug,
                (SELECT COUNT(*) FROM course_lessons l WHERE l.course_id=c.id) AS lessons,
                (SELECT COALESCE(SUM(l.duration),0) FROM course_lessons l WHERE l.course_id=c.id) AS duration
              FROM courses c LEFT JOIN branches b ON b.id = c.branch_id";
      $p = []; $conds = [];
      if ($status)            { $conds[] = "c.status=?";    $p[] = $status; }
      if ($branchId !== null) { $conds[] = "c.branch_id=?"; $p[] = $branchId; }
      if ($conds) { $sql .= " WHERE " . implode(' AND ', $conds); }
      $sql .= " ORDER BY c.created_at DESC, c.id DESC";
      $rows = q($pdo, $sql, $p)->fetchAll();
      if ($rows) return $rows;
    } catch (Throwable $e) { /* fall through to demo */ }
  }
  $demo = maxa_demo_courses();
  if ($status) $demo = array_values(array_filter($demo, fn($c)=>$c['status']===$status));
  return $demo;
}

/* بارگذاری یک دوره به‌همراه سرفصل‌ها (یا داده‌ی نمونه) */
function load_course_full(?PDO $pdo, bool $ready, int $id): ?array {
  if ($pdo && $ready) {
    try {
      $c = q($pdo, "SELECT * FROM courses WHERE id=?", [$id])->fetch();
      if ($c) {
        $secs = q($pdo, "SELECT * FROM course_sections WHERE course_id=? ORDER BY sort_order,id", [$id])->fetchAll();
        $curriculum = [];
        foreach ($secs as $s) {
          $ls = q($pdo, "SELECT * FROM course_lessons WHERE section_id=? ORDER BY sort_order,id", [(int)$s['id']])->fetchAll();
          $curriculum[] = ['title'=>$s['title'], 'lessons'=>$ls];
        }
        $c['curriculum'] = $curriculum;
        $c['lessons'] = array_sum(array_map(fn($s)=>count($s['lessons']), $curriculum));
        $c['duration'] = 0;
        foreach ($curriculum as $s) foreach ($s['lessons'] as $l) $c['duration'] += (int)($l['duration'] ?? 0);
        return $c;
      }
    } catch (Throwable $e) { /* fall through */ }
  }
  // داده‌ی نمونه
  foreach (maxa_demo_courses() as $c) {
    if ((int)$c['id'] === $id) {
      $c['curriculum'] = maxa_demo_curriculum();
      $c['description'] = $c['description'] ?? 'این یک دوره‌ی نمونه است که برای نمایش طراحی صفحه استفاده می‌شود. در حالت واقعی، توضیحات کامل دوره از پایگاه داده بارگذاری می‌شود و شامل اهداف، سرفصل‌ها و جزئیات کامل خواهد بود.';
      $c['what_you_learn'] = "تسلط بر مفاهیم پایه تا پیشرفته\nساخت پروژه‌های واقعی و کاربردی\nآمادگی برای ورود به بازار کار\nدریافت گواهی پایان دوره";
      $c['requirements'] = "یک رایانه با دسترسی به اینترنت\nانگیزه و پشتکار برای یادگیری\nبدون نیاز به دانش قبلی";
      $c['instructor_bio'] = 'مدرس و متخصص با سال‌ها تجربه‌ی حرفه‌ای و آموزشی در این حوزه.';
      $c['lessons'] = array_sum(array_map(fn($s)=>count($s['lessons']), $c['curriculum']));
      return $c;
    }
  }
  return null;
}

/* پالت رنگی پایدار برای کارت‌ها بر اساس شناسه */
function course_accent($id): string {
  $palette = ['#007b7a','#7c4ddb','#f4a61e','#16a37a','#2f9e9c','#e0556b','#3b82f6','#db8d0c'];
  return $palette[((int)$id) % count($palette)];
}
function level_label(string $lv): string {
  return ['beginner'=>'مقدماتی','intermediate'=>'متوسط','advanced'=>'پیشرفته','all'=>'همه سطوح'][$lv] ?? 'همه سطوح';
}
function fmt_duration(int $min): string {
  if ($min <= 0) return '—';
  $h = intdiv($min, 60); $m = $min % 60;
  if ($h && $m) return fa_digits($h).' ساعت و '.fa_digits($m).' دقیقه';
  if ($h) return fa_digits($h).' ساعت';
  return fa_digits($m).' دقیقه';
}

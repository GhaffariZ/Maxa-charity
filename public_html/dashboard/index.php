<?php
/* ============================================================================
 *  پنل مدیریت خیریه مکسا — لایه‌ی داده (Back-end)
 *  این فایل باید توسط PHP اجرا شود. اگر سرور شما html را به‌صورت php پردازش
 *  نمی‌کند، کافیست نام فایل را به maxa-dashboard.php تغییر دهید.
 * ========================================================================== */
declare(strict_types=1);
require_once __DIR__ . '/_guard.php';
mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Tehran');

/* ---------- تنظیمات اتصال به دیتابیس (از فایل کانفیگ خارج از گیت) ---------- */
$DB = require __DIR__ . '/../core/db-config.php';

/* ---------- اتصال ---------- */
$pdo = null; $dbError = null;
try {
  // نکته: charset را داخل DSN نمی‌گذاریم چون برخی نسخه‌های کتابخانه‌ی MySQL
  // خطای «Can't initialize character set» می‌دهند؛ به‌جایش با SET NAMES (سمت سرور) تنظیم می‌کنیم.
  $opts = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];
  if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
    $opts[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$DB['charset']}";
  }
  $pdo = new PDO(
    "mysql:host={$DB['host']};dbname={$DB['name']}",
    $DB['user'], $DB['pass'],
    $opts
  );
  // اطمینان مجدد از charset (در صورتی که INIT_COMMAND پشتیبانی نشود)
  try { $pdo->exec("SET NAMES {$DB['charset']}"); } catch (Throwable $e) { $pdo->exec("SET NAMES utf8"); }
} catch (Throwable $e) {
  $dbError = $e->getMessage();
}

/* ---------- توابع کمکی ---------- */
function q(PDO $pdo, string $sql, array $p = []): PDOStatement {
  $st = $pdo->prepare($sql); $st->execute($p); return $st;
}
function fa_digits($s): string {
  return strtr((string)$s, [
    '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹','.'=>'٫'
  ]);
}
/* میلادی به شمسی */
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
const JMONTHS = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
function jmonth_name(int $jm): string { return JMONTHS[max(1, min(12, $jm)) - 1]; }
function jalali_short($ts): string {
  list(, $jm, $jd) = gregorian_to_jalali((int)date('Y',$ts), (int)date('n',$ts), (int)date('j',$ts));
  return fa_digits(sprintf('%02d/%02d', $jm, $jd));
}
function jalali_datetime($ts): string {
  list($jy, $jm, $jd) = gregorian_to_jalali((int)date('Y',$ts), (int)date('n',$ts), (int)date('j',$ts));
  return fa_digits(sprintf('%04d/%02d/%02d - %02d:%02d', $jy, $jm, $jd, (int)date('H',$ts), (int)date('i',$ts)));
}
function money_compact($v): string {
  $v = (float)$v;
  if ($v >= 1e9) return fa_digits(number_format($v / 1e9, 1)).' میلیارد';
  if ($v >= 1e6) return fa_digits((string)round($v / 1e6)).' م';
  if ($v >= 1e3) return fa_digits((string)round($v / 1e3)).' هزار';
  return fa_digits(number_format($v));
}

/* ---------- محدوده‌ی شعبه‌ی فعال (ایزولاسیون چندمستأجری) ---------- */
$BRANCH_ID = dash_active_branch_id();
$IS_HQ_VIEW = false;
$ACTIVE_BRANCH_ROW = dash_load_branch($BRANCH_ID);
if ($ACTIVE_BRANCH_ROW) { $IS_HQ_VIEW = ((int)$ACTIVE_BRANCH_ROW['is_hq'] === 1); }

/* ---------- آمار کلی (محدودشده به شعبه‌ی فعال) ----------
 *  قواعد کمک‌ها:
 *   - مجموع کمک‌های یک شعبه = فقط کمک‌هایی که branch_id آن‌ها برابر شعبه است.
 *   - کمک‌های عمومی (آنلاین بدون کمپین) فقط در دفتر مرکزی شمرده می‌شوند و در branch_id=HQ هستند.
 *  چون panel_donations.branch_id مستقیماً مقداردهی شده، کافی است بر آن فیلتر بزنیم.
 */
$totalDonors = 0; $activeDonors = 0; $totalCollected = 0.0; $activeCampaigns = 0; $newThisWeek = 0;
if ($pdo) { try {
  // خیرین معیارهای سراسری‌اند (پنل خیرین مشترک است)؛ فقط در نمای مرکزی نمایش کامل دارند.
  $totalDonors    = (int)q($pdo, "SELECT COUNT(*) c FROM panel_users")->fetch()['c'];
  $activeDonors   = (int)q($pdo, "SELECT COUNT(*) c FROM panel_users WHERE last_login_at >= (NOW() - INTERVAL 7 DAY)")->fetch()['c'];
  $newThisWeek    = (int)q($pdo, "SELECT COUNT(*) c FROM panel_users WHERE created_at >= (NOW() - INTERVAL 7 DAY)")->fetch()['c'];
  // مجموع کمک‌های موفقِ این شعبه
  $totalCollected = (float)q($pdo, "SELECT COALESCE(SUM(amount),0) s FROM panel_donations WHERE status='success' AND branch_id = ?", [$BRANCH_ID])->fetch()['s'];
  // کمپین‌های فعالِ این شعبه
  $activeCampaigns= (int)q($pdo, "SELECT COUNT(*) c FROM campaigns WHERE is_active=1 AND branch_id = ?", [$BRANCH_ID])->fetch()['c'];
} catch (Throwable $e) { $dbError = $dbError ?? $e->getMessage(); } }

/* ---------- هدف ماهانه (حلقه‌ی پیشرفت) ---------- */
$goalTarget = 650000000.0;  // هدف پیش‌فرض ماهانه (تومان) — قابل تنظیم در جدول settings
$goalCollected = 0.0;
if ($pdo) { try {
  $goalCollected = (float)q($pdo,
    "SELECT COALESCE(SUM(amount),0) s FROM panel_donations
     WHERE status='success' AND branch_id = ?
       AND YEAR(COALESCE(paid_at,created_at))=YEAR(NOW())
       AND MONTH(COALESCE(paid_at,created_at))=MONTH(NOW())", [$BRANCH_ID])->fetch()['s'];
  $row = q($pdo, "SELECT value FROM settings WHERE name='monthly_goal_irt' LIMIT 1")->fetch();
  if ($row && is_numeric($row['value'])) $goalTarget = (float)$row['value'];
} catch (Throwable $e) { $dbError = $dbError ?? $e->getMessage(); } }
$ringPct = $goalTarget > 0 ? (int)min(100, round($goalCollected / $goalTarget * 100)) : 0;

/* ---------- خیرین برتر (بیشترین مبلغ حمایت) ---------- */
$donorsRaw = [];
if ($pdo) { try {
  $donorsRaw = q($pdo,
    "SELECT pd.user_id,
            NULLIF(TRIM(CONCAT(COALESCE(up.first_name,''),' ',COALESCE(up.last_name,''))),'') AS name,
            pu.email AS email,
            COUNT(*) AS cnt,
            COALESCE(SUM(pd.amount),0) AS total
       FROM panel_donations pd
       JOIN panel_users pu        ON pu.id = pd.user_id
       LEFT JOIN user_profiles up ON up.user_id = pd.user_id
      WHERE pd.status='success' AND pd.branch_id = ?
      GROUP BY pd.user_id, pu.email
      ORDER BY total DESC, cnt DESC
      LIMIT 5", [$BRANCH_ID])->fetchAll();
} catch (Throwable $e) { $dbError = $dbError ?? $e->getMessage(); } }
$donorsOut = [];
foreach ($donorsRaw as $d) {
  $nm = $d['name'] ?: ($d['email'] ? explode('@', $d['email'])[0] : 'خیر ناشناس');
  $donorsOut[] = ['name'=>$nm, 'count'=>(int)$d['cnt'], 'amt'=>(float)$d['total']];
}

/* ---------- تراکنش‌های اخیر ---------- */
$txRaw = [];
if ($pdo) { try {
  $txRaw = q($pdo,
    "SELECT pd.reference, pd.amount, pd.type, pd.status, pd.campaign_id,
            COALESCE(pd.paid_at, pd.created_at) AS ts,
            NULLIF(TRIM(CONCAT(COALESCE(up.first_name,''),' ',COALESCE(up.last_name,''))),'') AS name,
            pu.email AS email,
            c.title AS campaign_title
       FROM panel_donations pd
       LEFT JOIN panel_users pu   ON pu.id = pd.user_id
       LEFT JOIN user_profiles up ON up.user_id = pd.user_id
       LEFT JOIN campaigns c      ON c.id = pd.campaign_id
      WHERE pd.branch_id = ?
      ORDER BY COALESCE(pd.paid_at, pd.created_at) DESC, pd.id DESC
      LIMIT 60", [$BRANCH_ID])->fetchAll();
} catch (Throwable $e) { $dbError = $dbError ?? $e->getMessage(); } }
$stMap = [
  'success'  => ['ok',  'موفق'],
  'pending'  => ['wait','در انتظار'],
  'failed'   => ['fail','ناموفق'],
  'refunded' => ['fail','مسترد شده'],
  'canceled' => ['fail','لغو شده'],
];
$tx = [];
foreach ($txRaw as $r) {
  $nm = $r['name'] ?: ($r['email'] ? explode('@', $r['email'])[0] : 'خیر ناشناس');
  $st = $stMap[$r['status']] ?? ['wait', $r['status']];
  if (!empty($r['campaign_title'])) { $tk = 'campaign'; $tl = $r['campaign_title']; }
  else                              { $tk = 'free';     $tl = 'پرداخت آزاد'; }
  $tx[] = [
    'name'      => $nm,
    'amt'       => (float)$r['amount'],
    'typeKey'   => $tk,
    'typeLabel' => $tl,
    'stKey'     => $st[0],
    'stLabel'   => $st[1],
    'date'      => jalali_datetime(strtotime($r['ts'])),
    'ref'       => $r['reference'],
  ];
}

/* ---------- داده‌ی نمودار: مجموع کمک‌های موفق در بازه‌های زمانی ---------- */
$don = [];
if ($pdo) { try {
  $don = q($pdo,
    "SELECT amount, campaign_id, COALESCE(paid_at, created_at) AS ts
       FROM panel_donations
      WHERE status='success' AND branch_id = ?", [$BRANCH_ID])->fetchAll();
} catch (Throwable $e) { $dbError = $dbError ?? $e->getMessage(); } }

/* ---------- داده‌ی خیرین برای سری زمانی «تعداد کل» و «خیرین فعال» ---------- */
$users = [];
if ($pdo) { try {
  $users = q($pdo, "SELECT created_at, last_login_at FROM panel_users")->fetchAll();
} catch (Throwable $e) { $dbError = $dbError ?? $e->getMessage(); } }

function build_series(array $don, array $users, string $mode): array {
  $now = time();
  $buckets = [];
  if ($mode === 'day') {                 // ۷ روز اخیر
    $today = strtotime(date('Y-m-d', $now));
    for ($i = 6; $i >= 0; $i--) { $s = $today - $i*86400; $buckets[] = ['s'=>$s,'e'=>$s+86400,'label'=>jalali_short($s)]; }
  } elseif ($mode === 'week') {          // ۸ هفته اخیر
    $end0 = strtotime(date('Y-m-d', $now)) + 86400;
    for ($i = 7; $i >= 0; $i--) { $e = $end0 - $i*7*86400; $s = $e - 7*86400; $buckets[] = ['s'=>$s,'e'=>$e,'label'=>jalali_short($s)]; }
  } elseif ($mode === 'month') {         // ۱۲ ماه اخیر
    for ($i = 11; $i >= 0; $i--) {
      $d = new DateTime('first day of this month 00:00:00'); $d->modify("-$i month");
      $s = $d->getTimestamp(); $d2 = clone $d; $d2->modify('+1 month'); $e = $d2->getTimestamp();
      list(, $jm) = gregorian_to_jalali((int)$d->format('Y'), (int)$d->format('n'), 15);
      $buckets[] = ['s'=>$s,'e'=>$e,'label'=>jmonth_name($jm)];
    }
  } else {                               // ۵ سال اخیر
    $cy = (int)date('Y', $now);
    for ($i = 4; $i >= 0; $i--) {
      $y = $cy - $i; $s = strtotime("$y-01-01 00:00:00"); $e = strtotime(($y+1)."-01-01 00:00:00");
      list($jy) = gregorian_to_jalali($y, 7, 1);
      $buckets[] = ['s'=>$s,'e'=>$e,'label'=>fa_digits((string)$jy)];
    }
  }
  $labels = []; $camp = []; $periods = []; $free = []; $totalDonors = []; $activeDonors = [];
  // $periods فعلاً صفر است؛ بعداً منبع داده‌ی «دوره‌ها» به همین آرایه وصل می‌شود.
  foreach ($buckets as $b) { $labels[] = $b['label']; $camp[] = 0; $periods[] = 0; $free[] = 0; $totalDonors[] = 0; $activeDonors[] = 0; }

  // مبالغ کمک‌ها
  foreach ($don as $row) {
    $ts = strtotime((string)$row['ts']); if ($ts === false) continue;
    foreach ($buckets as $k => $b) {
      if ($ts >= $b['s'] && $ts < $b['e']) {
        if ($row['campaign_id'] !== null && $row['campaign_id'] !== '') $camp[$k] += (float)$row['amount'];
        else                                                            $free[$k] += (float)$row['amount'];
        break;
      }
    }
  }
  // تعداد کل خیرین = شمارش تجمعی کاربرانِ ثبت‌شده تا پایان هر بازه
  // خیرین فعال = کاربرانی که آخرین ورودشان داخل آن بازه بوده است
  foreach ($buckets as $k => $b) {
    foreach ($users as $u) {
      $cts = $u['created_at']    ? strtotime((string)$u['created_at'])    : false;
      $lts = $u['last_login_at'] ? strtotime((string)$u['last_login_at']) : false;
      if ($cts !== false && $cts < $b['e'])              $totalDonors[$k]++;
      if ($lts !== false && $lts >= $b['s'] && $lts < $b['e']) $activeDonors[$k]++;
    }
  }

  return [
    'labels'       => $labels,
    'campaigns'    => $camp,
    'periods'      => $periods,
    'free'         => $free,
    'totalDonors'  => $totalDonors,
    'activeDonors' => $activeDonors,
  ];
}

/* ---------- نشانگرهای رشد ---------- */
$trends = [
  'donors'    => ($newThisWeek > 0 ? '+' : '').fa_digits((string)$newThisWeek).' این هفته',
  'active'    => ($totalDonors > 0 ? fa_digits((string)round($activeDonors / $totalDonors * 100)) : '۰').'٪',
  'collected' => fa_digits((string)$ringPct).'٪ هدف',
  'campaigns' => fa_digits((string)$activeCampaigns).' فعال',
];

/* ---------- بسته‌ی نهایی برای فرانت‌اند ---------- */
$SERVER = [
  'stats' => [
    'totalDonors'    => $totalDonors,
    'activeDonors'   => $activeDonors,
    'totalCollected' => $totalCollected,
    'activeCampaigns'=> $activeCampaigns,
    'trends'         => $trends,
  ],
  'chart' => [
    'day'   => build_series($don, $users, 'day'),
    'week'  => build_series($don, $users, 'week'),
    'month' => build_series($don, $users, 'month'),
    'year'  => build_series($don, $users, 'year'),
  ],
  'donors'  => $donorsOut,
  'tx'      => $tx,
  'dbError' => $dbError,
];

/* ============================================================================
 *  بسته‌ی چندشعبه‌ای برای منو و انتخابگر شعبه (Box 1 و Box 2)
 * ========================================================================== */
$U = dash_user();
$isSuper = dash_is_super();

// قابلیت‌های فعالِ شعبه‌ی فعلی
$activeFeatures = [];
foreach (DASH_FEATURES as $f) {
  if (dash_branch_feature_enabled($BRANCH_ID, $f)) $activeFeatures[] = $f;
}
// آیتم‌های منو فقط وقتی نمایش داده می‌شوند که هم برای شعبه فعال باشند و هم کاربر دسترسی داشته باشد
$visible = static function (string $feature) use ($U, $BRANCH_ID): bool {
  return in_array($feature, $U['permissions'], true) && dash_branch_feature_enabled($BRANCH_ID, $feature);
};

// فهرست شعبه‌ها برای dropdown (فقط سوپرادمین همه را می‌بیند)
$branchList = $isSuper ? dash_all_branches() : [array_filter($ACTIVE_BRANCH_ROW ?? [], static fn($k)=>in_array($k,['id','name','slug','is_hq','status'],true), ARRAY_FILTER_USE_KEY)];

$MENU = [
  'features'      => $activeFeatures,
  'can'           => [
    'hero'      => $visible('hero'),
    'news'      => $visible('news'),
    'campaigns' => $visible('campaigns'),
    'partners'  => $visible('partners'),
    'courses'   => $visible('courses'),
    'pages'     => $visible('pages'),
    'financial' => $visible('financial'),
    'feedback'  => $visible('feedback'),
    'medical'   => $visible('medical'),
  ],
  'isSuper'       => $isSuper,
  'isBranchAdmin' => dash_is_branch_admin(),
  'isHqView'      => $IS_HQ_VIEW,
  'isNewsEditor'  => dash_is_news_editor(),
  'canMaxapedia'  => dash_can_maxapedia(),
  'activeBranch'  => $BRANCH_ID,
  'activeBranchName' => $ACTIVE_BRANCH_ROW['name'] ?? '',
  'branches'      => array_map(static fn($b)=>[
                       'id'=>(int)$b['id'],'name'=>$b['name'],'slug'=>$b['slug'],
                       'is_hq'=>(int)($b['is_hq']??0),'status'=>$b['status']??'active',
                     ], $branchList),
  'user'          => ['name'=>$U['full_name'] ?: $U['username'],
                      'role'=>$isSuper ? 'مدیر مرکزی' : (dash_is_branch_admin() ? 'مدیر شعبه' : 'کاربر شعبه')],
  'csrf'          => csrf_token(),
];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>پنل مدیریت | خیریه مکسا</title>
<script>(function(){try{var t=localStorage.getItem('maxa-theme');if(t==='dark'){document.documentElement.setAttribute('data-theme','dark');}}catch(e){}})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --violet:#8b5cf6;
  --success:#16a37a; --danger:#e0556b; --warning:#f4a61e;
  --primary-08:rgba(0,123,122,.08); --primary-12:rgba(0,123,122,.12); --primary-16:rgba(0,123,122,.16);
  --secondary-12:rgba(244,166,30,.14); --violet-12:rgba(139,92,246,.14);
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04), 0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06), 0 2px 6px rgba(16,40,40,.04);
  --shadow-lg:0 20px 44px -14px rgba(0,102,101,.20), 0 8px 20px -10px rgba(16,40,40,.12);
  --sb-w:286px;
  --ease:cubic-bezier(.4,0,.2,1);
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.65;-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility;}
a{text-decoration:none;color:inherit}
button{font-family:inherit;cursor:pointer;border:none;background:none;color:inherit}
ul{list-style:none}
input{font-family:inherit}
::selection{background:var(--primary-16);color:var(--color-primary-dark)}
svg.ic{display:block}

*::-webkit-scrollbar{width:9px;height:9px}
*::-webkit-scrollbar-thumb{background:rgba(0,123,122,.18);border-radius:99px;border:2px solid transparent;background-clip:padding-box}
*::-webkit-scrollbar-thumb:hover{background:rgba(0,123,122,.34);background-clip:padding-box}
*::-webkit-scrollbar-track{background:transparent}

.app{min-height:100vh}

/* ============ SIDEBAR ============ */
.sidebar{position:fixed;top:0;right:0;height:100vh;width:var(--sb-w);background:var(--color-surface);border-left:1px solid var(--color-border);
  transform:translateX(0);transition:transform .42s var(--ease);z-index:60;display:flex;flex-direction:column;}
.app.collapsed .sidebar{transform:translateX(100%)}

.sb-header{padding:22px 20px 16px;display:flex;align-items:center;gap:13px;position:relative;overflow:hidden;flex-shrink:0}
.sb-header::before{content:'';position:absolute;inset:0;background:
  radial-gradient(120% 110% at 100% 0,rgba(0,123,122,.12),transparent 58%),
  radial-gradient(90% 90% at 0 0,rgba(244,166,30,.10),transparent 60%);pointer-events:none}
.brand-logo{width:48px;height:48px;border-radius:15px;object-fit:cover;box-shadow:0 6px 16px -6px rgba(0,102,101,.5);flex-shrink:0;position:relative;z-index:1}
.brand-text{position:relative;z-index:1}
.brand-name{font-weight:800;font-size:19px;letter-spacing:-.01em;line-height:1.2}
.brand-sub{font-size:11.5px;color:var(--color-muted);font-weight:500;margin-top:1px}

.sb-nav{flex:1;overflow-y:auto;padding:6px 12px 14px}
.nav-section-title{font-size:11px;color:var(--color-muted);font-weight:700;padding:16px 12px 7px;letter-spacing:.04em;display:flex;align-items:center;gap:9px}
.nav-section-title::before{content:'';width:16px;height:3px;border-radius:99px;background:linear-gradient(90deg,var(--color-secondary),#e08e12);flex-shrink:0}
.nav-list>*{animation:navIn .5s var(--ease) both}
@keyframes navIn{from{opacity:0;transform:translateX(14px)}to{opacity:1;transform:none}}

.nav-item,.nav-group-header{width:100%;display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:14px;font-weight:600;font-size:13.5px;color:#5b6469;transition:background .22s,color .22s,box-shadow .22s;position:relative}
.nav-item:hover,.nav-group-header:hover{background:var(--primary-08);color:var(--color-primary-dark)}
.nav-item.active{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff;box-shadow:0 10px 20px -10px rgba(0,123,122,.75)}
.nav-item .ic,.nav-group-header .ic{width:20px;height:20px;flex-shrink:0;opacity:.92}
.nav-item.active .ic{opacity:1}
.nav-item>span,.nav-group-header>span{flex:1;text-align:start}
.nav-group-header .chev{flex:0 0 auto;width:16px;height:16px;transition:transform .35s var(--ease);opacity:.6}
.nav-group.open>.nav-group-header .chev{transform:rotate(180deg)}
.nav-group.open>.nav-group-header{color:var(--color-primary-dark);background:var(--primary-08)}

.nav-sub{max-height:0;overflow:hidden;opacity:0;transition:max-height .4s var(--ease),opacity .3s ease}
.nav-group.open>.nav-sub{opacity:1}
.nav-sub-inner{margin:3px 14px 8px;padding-inline-start:11px;border-inline-start:1.5px solid var(--color-border);display:flex;flex-direction:column;gap:2px}
.nav-sub-link{display:flex;align-items:center;gap:10px;padding:9px 11px;border-radius:11px;font-size:12.5px;color:#6c7479;font-weight:500;transition:background .18s,color .18s,transform .18s;position:relative}
.nav-sub-link .ic{width:15px;height:15px;opacity:.7;flex-shrink:0}
.nav-sub-link:hover{background:var(--primary-08);color:var(--color-primary-dark);transform:translateX(-2px)}
.nav-sub-link:hover .ic{opacity:1}

.sb-footer{padding:12px;border-top:1px solid var(--color-border);flex-shrink:0;display:flex;align-items:center;gap:8px}
.user-card{order:1;flex:1;min-width:0;display:flex;align-items:center;gap:11px;padding:9px 10px;border-radius:14px;transition:background .2s}
.user-card:hover{background:var(--primary-08)}
.user-av{width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));color:#fff;display:grid;place-items:center;font-weight:700;font-size:14px;flex-shrink:0}
.user-meta{flex:1;min-width:0}
.user-name{font-weight:700;font-size:13px}
.user-role{font-size:11px;color:var(--color-muted)}
.user-card .ic{width:17px;height:17px;color:var(--color-muted)}
.logout-link{order:2;flex-shrink:0;width:42px;height:42px;display:grid;place-items:center;border-radius:13px;color:var(--danger);transition:background .2s,transform .14s}
.logout-link:hover{background:rgba(224,85,107,.10)}
.logout-link:active{transform:scale(.93)}
.logout-link .ic{width:20px;height:20px}

/* ============ MAIN / TOPBAR ============ */
.main{margin-right:var(--sb-w);transition:margin-right .42s var(--ease);min-height:100vh;display:flex;flex-direction:column}
.app.collapsed .main{margin-right:0}

.topbar{position:sticky;top:0;z-index:40;height:70px;display:flex;align-items:center;gap:13px;padding:0 22px;
  background:rgba(255,255,255,.82);backdrop-filter:saturate(180%) blur(16px);-webkit-backdrop-filter:saturate(180%) blur(16px);border-bottom:1px solid var(--color-border)}
.icon-btn{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;color:#5b6469;transition:background .2s,color .2s,transform .14s;position:relative;flex-shrink:0}
.icon-btn:hover{background:var(--primary-08);color:var(--color-primary-dark)}
.icon-btn:active{transform:scale(.93)}
.icon-btn .ic{width:21px;height:21px}
.tb-title{display:flex;flex-direction:column;line-height:1.25;min-width:0}
.tb-title h1{font-size:17px;font-weight:800;letter-spacing:-.01em;white-space:nowrap}
.tb-title span{font-size:12px;color:var(--color-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tb-spacer{flex:1}
.search{display:flex;align-items:center;gap:9px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:13px;padding:0 14px;height:42px;width:250px;transition:border-color .2s,box-shadow .2s,background .2s}
.search:focus-within{border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:#fff}
.search .ic{width:18px;height:18px;color:var(--color-muted);flex-shrink:0}
.search input{border:none;background:none;outline:none;flex:1;font-size:13px;color:var(--color-text);width:100%}
.search input::placeholder{color:var(--color-muted)}
.bell-dot{position:absolute;top:8px;left:9px;width:9px;height:9px;border-radius:50%;background:var(--color-secondary);border:2px solid #fff;box-shadow:0 0 0 2px rgba(244,166,30,.25)}
.profile{display:flex;align-items:center;gap:10px;padding:5px 8px 5px 5px;border-radius:14px;transition:background .2s}
.profile:hover{background:var(--primary-08)}
.profile .av{width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,var(--color-secondary),#e08e12);color:#fff;display:grid;place-items:center;font-weight:700;font-size:14px;flex-shrink:0}
.profile .pm{line-height:1.25}
.profile .pm b{font-size:13px;font-weight:700;display:block}
.profile .pm span{font-size:11px;color:var(--color-muted)}
.profile .ic{width:16px;height:16px;color:var(--color-muted)}

.overlay{position:fixed;inset:0;background:rgba(16,40,40,.40);backdrop-filter:blur(2px);opacity:0;visibility:hidden;transition:opacity .3s,visibility .3s;z-index:55}
.app.show-overlay .overlay{opacity:1;visibility:visible}

/* ============ CONTENT ============ */
.content{padding:24px;max-width:1520px;width:100%;margin:0 auto;flex:1;position:relative}

/* کابل اتصال نمودار — پشت کارت‌ها تا دوشاخه «زیر کارت» فرو برود */
.cable-svg{position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:0;overflow:visible;opacity:0;transition:opacity .45s var(--ease)}
.cable-svg.show{opacity:1}

.welcome{position:relative;overflow:hidden;border-radius:var(--radius-lg);
  background:linear-gradient(120deg,#008786 0%,var(--color-primary-dark) 58%,#004a49 100%);
  color:#fff;padding:28px 30px;display:flex;align-items:center;justify-content:space-between;gap:22px;box-shadow:var(--shadow-lg)}
.welcome::before{content:'';position:absolute;inset:0;background:radial-gradient(60% 130% at 88% -10%,rgba(255,255,255,.18),transparent 55%);pointer-events:none}
.welcome::after{content:'';position:absolute;left:-26px;bottom:-72px;width:240px;height:240px;opacity:.10;transform:rotate(-14deg);pointer-events:none;transition:transform .7s var(--ease),opacity .7s ease;
  background:url("data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%20100%20100'%3E%3Cg%20fill='%23ffffff'%3E%3Cellipse%20cx='50'%20cy='25'%20rx='15'%20ry='22'/%3E%3Cellipse%20cx='50'%20cy='75'%20rx='15'%20ry='22'/%3E%3Cellipse%20cx='25'%20cy='50'%20rx='22'%20ry='15'/%3E%3Cellipse%20cx='75'%20cy='50'%20rx='22'%20ry='15'/%3E%3Ccircle%20cx='50'%20cy='50'%20r='14'/%3E%3C/g%3E%3C/svg%3E") center/contain no-repeat}
.welcome-text{position:relative;z-index:1;min-width:0}
.welcome:hover::after{transform:rotate(3deg) scale(1.08);opacity:.15}
.welcome-goal{position:relative;z-index:1;display:flex;align-items:center;gap:18px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);border-radius:20px;padding:15px 18px;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);flex-shrink:0}
.ring{position:relative;width:100px;height:100px;flex-shrink:0}
.ring svg{width:100%;height:100%;transform:rotate(-90deg)}
.ring-bg{fill:none;stroke:rgba(255,255,255,.22);stroke-width:9}
.ring-fg{fill:none;stroke:url(#ringg);stroke-width:9;stroke-linecap:round;stroke-dasharray:314.159;stroke-dashoffset:314.159;transition:stroke-dashoffset 1.3s var(--ease)}
.ring-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff}
.ring-center b{font-size:21px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums}
.ring-center span{font-size:10px;opacity:.85;margin-top:3px}
.goal-meta{display:flex;flex-direction:column;gap:6px;min-width:165px}
.goal-title{font-size:13px;font-weight:800;color:#fff;margin-bottom:2px}
.goal-fig{display:flex;align-items:center;justify-content:space-between;gap:16px;font-size:12px;color:rgba(255,255,255,.82);font-weight:600}
.goal-fig b{color:#fff;font-weight:800;font-variant-numeric:tabular-nums}
.goal-meta .btn{margin-top:8px;justify-content:center}
.grid-2>*{min-width:0}
.chart-wrap{min-width:0}
.chart-wrap canvas{max-width:100%!important}
.welcome-text h2{font-size:23px;font-weight:800;margin-bottom:7px;letter-spacing:-.01em}
.welcome-text p{font-size:13.5px;opacity:.92;max-width:560px}
.welcome-actions{position:relative;z-index:1;display:flex;gap:10px;flex-shrink:0;flex-wrap:wrap}

.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 18px;border-radius:13px;font-weight:700;font-size:13px;transition:transform .15s var(--ease),box-shadow .22s,background .2s,color .2s;white-space:nowrap}
.btn .ic{width:17px;height:17px}
.btn:active{transform:scale(.96)}
.btn-light{background:#fff;color:var(--color-primary-dark);box-shadow:0 8px 22px -10px rgba(0,0,0,.45)}
.btn-light:hover{box-shadow:0 14px 28px -10px rgba(0,0,0,.5);transform:translateY(-2px)}
.btn-gold{background:linear-gradient(135deg,#ffc24d,var(--color-secondary));color:#5c3d00;box-shadow:0 10px 24px -10px rgba(244,166,30,.75)}
.btn-gold:hover{transform:translateY(-2px);box-shadow:0 16px 30px -10px rgba(244,166,30,.9)}
.btn-ghost-light{background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.30)}
.btn-ghost-light:hover{background:rgba(255,255,255,.24)}
.btn-primary{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff;box-shadow:0 10px 22px -10px rgba(0,123,122,.7)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 16px 30px -10px rgba(0,123,122,.8)}

/* stat cards */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:22px}
.stat-card{position:relative;z-index:1;overflow:hidden;background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow-sm);transition:transform .3s var(--ease),box-shadow .3s var(--ease),border-color .3s}
.stat-card:hover{transform:translateY(-5px);box-shadow:var(--shadow-lg);border-color:transparent}
.stat-card::after{content:'';position:absolute;left:-26px;top:-26px;width:98px;height:98px;border-radius:50%;background:var(--accent-bg);opacity:.55;transition:transform .45s var(--ease)}
.stat-card:hover::after{transform:scale(1.35)}
.stat-top{display:flex;align-items:center;justify-content:space-between;position:relative;z-index:1}
.stat-icon{width:47px;height:47px;border-radius:15px;display:grid;place-items:center;background:var(--accent-bg);color:var(--accent)}
.stat-icon .ic{width:23px;height:23px}
.stat-trend{display:inline-flex;align-items:center;gap:3px;font-size:11.5px;font-weight:800;padding:5px 9px;border-radius:99px}
.stat-trend.up{background:rgba(22,163,122,.12);color:var(--success)}
.stat-trend.down{background:rgba(224,85,107,.12);color:var(--danger)}
.stat-trend .ic{width:13px;height:13px}
.stat-value{font-size:28px;font-weight:800;letter-spacing:-.02em;margin-top:17px;position:relative;z-index:1;font-variant-numeric:tabular-nums;display:flex;align-items:baseline;gap:5px}
.stat-unit{font-size:13px;font-weight:600;color:var(--color-muted)}
.stat-label{font-size:12.5px;color:var(--color-muted);font-weight:600;margin-top:5px;position:relative;z-index:1}
.stat-foot{text-align:left;margin-top:13px;position:relative;z-index:1}
.stat-chart-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:11px;font-weight:700;font-size:11.5px;color:var(--accent);background:var(--accent-bg);transition:filter .2s,transform .14s,box-shadow .2s}
.stat-chart-btn:hover{filter:brightness(.96)}
.stat-chart-btn:active{transform:scale(.95)}
.stat-chart-btn .ic{width:14px;height:14px}
.stat-card.charting{box-shadow:0 0 0 2px var(--accent) inset, var(--shadow-md)}
.stat-card.charting .stat-chart-btn{color:#fff;background:var(--accent)}

/* generic cards */
.grid-2{display:grid;grid-template-columns:1.85fr 1fr;gap:18px;margin-top:52px;align-items:stretch}
.card{position:relative;z-index:1;background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);box-shadow:var(--shadow-sm);padding:20px 22px}
.card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.card-head h3{font-size:16.5px;font-weight:800;letter-spacing:-.01em}
.card-head .sub{font-size:12px;color:var(--color-muted);margin-top:3px}
.tag{font-size:11px;font-weight:700;color:var(--color-primary-dark);background:var(--primary-08);padding:5px 11px;border-radius:99px}
.link-more{display:inline-flex;align-items:center;gap:4px;font-size:12.5px;font-weight:700;color:var(--color-primary);padding:6px 10px;border-radius:10px;transition:background .2s;flex-shrink:0}
.link-more:hover{background:var(--primary-08)}
.link-more .ic{width:14px;height:14px}

/* custom select */
.select{position:relative;flex-shrink:0}
.select-btn{display:flex;align-items:center;gap:8px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:12px;padding:9px 13px;font-weight:700;font-size:12.5px;color:var(--color-text);transition:border-color .2s,box-shadow .2s,background .2s}
.select-btn:hover{border-color:var(--color-primary-light)}
.select-btn .ic{width:15px;height:15px;color:var(--color-muted);transition:transform .3s var(--ease)}
.select.open .select-btn{border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:#fff}
.select.open .select-btn .ic{transform:rotate(180deg)}
.select-menu{position:absolute;top:calc(100% + 8px);inset-inline-end:0;min-width:142px;background:#fff;border:1px solid var(--color-border);border-radius:14px;box-shadow:var(--shadow-lg);padding:6px;
  opacity:0;visibility:hidden;transform:translateY(-8px) scale(.97);transform-origin:top left;transition:opacity .2s,transform .22s var(--ease),visibility .2s;z-index:30}
.select.open .select-menu{opacity:1;visibility:visible;transform:translateY(0) scale(1)}
.select-menu li{padding:9px 12px;border-radius:10px;font-size:12.5px;font-weight:600;color:#5b6469;cursor:pointer;transition:background .15s,color .15s;display:flex;align-items:center;justify-content:space-between;gap:10px}
.select-menu li:hover{background:var(--primary-08);color:var(--color-primary-dark)}
.select-menu li.sel{color:var(--color-primary-dark);background:var(--primary-08)}
.select-menu li.sel::after{content:'';width:7px;height:7px;border-radius:50%;background:var(--color-primary);flex-shrink:0}

/* chips */
.chips{display:flex;flex-wrap:wrap;gap:10px;margin:16px 0 4px}
.chip{display:inline-flex;align-items:center;gap:9px;padding:8px 14px 8px 11px;border:1.5px solid var(--color-border);border-radius:99px;font-weight:600;font-size:12.5px;color:#6c7479;background:#fff;transition:border-color .22s,background .22s,color .22s}
.chip:hover{border-color:var(--c);color:var(--color-text)}
.chip .check{width:19px;height:19px;border-radius:50%;border:2px solid #c4cace;display:grid;place-items:center;transition:background .22s,border-color .22s;flex-shrink:0}
.chip .check .ic{width:11px;height:11px;color:#fff;opacity:0;transform:scale(.4);transition:opacity .2s,transform .25s var(--ease);stroke-width:3.5}
.chip .dot{width:9px;height:9px;border-radius:50%;background:var(--c);opacity:.5;transition:opacity .2s}
.chip.on{border-color:var(--c);color:var(--color-text);background:var(--cbg)}
.chip.on .check{background:var(--c);border-color:var(--c)}
.chip.on .check .ic{opacity:1;transform:scale(1)}
.chip.on .dot{opacity:1}
.chip-total{font-size:11px;color:var(--color-muted);font-weight:800;font-variant-numeric:tabular-nums}
.chip.on .chip-total{color:var(--c)}

.chart-wrap{position:relative;height:300px;margin-top:8px}

/* donors */
.donors-card{display:flex;flex-direction:column}
.donor-list{display:flex;flex-direction:column;gap:2px;margin-top:10px;flex:1}
.donor{display:flex;align-items:center;gap:12px;padding:10px 8px;border-radius:14px;transition:background .2s}
.donor:hover{background:var(--color-bg)}
.rank-badge{width:26px;height:26px;border-radius:9px;display:grid;place-items:center;font-size:12px;font-weight:800;flex-shrink:0;background:var(--color-bg);color:var(--color-muted)}
.donor-av{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;color:#fff;font-weight:700;font-size:13.5px;flex-shrink:0;box-shadow:var(--shadow-sm)}
.donor-info{flex:1;min-width:0}
.donor-name{font-weight:700;font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.donor-bar{height:5px;border-radius:99px;background:#eef1f3;margin-top:7px;overflow:hidden}
.donor-bar i{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,var(--color-primary-light),var(--color-primary));width:0;transition:width 1.1s var(--ease)}
.donor-amt-wrap{text-align:left;flex-shrink:0}
.donor-amt{font-weight:800;font-size:13px;color:var(--color-primary-dark);font-variant-numeric:tabular-nums;white-space:nowrap}
.donor-amt small{font-size:10px;color:var(--color-muted);font-weight:600}
.donor-sub{font-size:11px;color:var(--color-muted);font-weight:600;margin-top:2px}

/* table */
.table-card{margin-top:18px;padding-bottom:14px}
.table-scroll{overflow-x:auto;margin:14px -22px 0;padding:0 22px}
.dtable{width:100%;border-collapse:collapse;font-size:13px;min-width:720px}
.dtable th{text-align:right;font-size:11.5px;font-weight:700;color:var(--color-muted);padding:0 14px 13px;border-bottom:1px solid var(--color-border);white-space:nowrap}
.dtable th:last-child,.dtable td:last-child{text-align:left}
.dtable td{padding:12px 14px;border-bottom:1px solid var(--color-border);vertical-align:middle}
.dtable tbody tr{transition:background .18s}
.dtable tbody tr:hover{background:var(--color-bg)}
.dtable tbody tr:last-child td{border-bottom:none}
.cell-user{display:flex;align-items:center;gap:11px}
.cell-av{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;color:#fff;font-weight:700;font-size:12.5px;flex-shrink:0}
.cell-name{font-weight:700;font-size:13px}
.cell-ref{font-size:11px;color:var(--color-muted);font-variant-numeric:tabular-nums;direction:ltr;text-align:right}
.amt{font-weight:800;color:var(--color-text);font-variant-numeric:tabular-nums;white-space:nowrap}
.amt small{font-weight:600;color:var(--color-muted);font-size:10px}
.badge{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:99px;font-size:11.5px;font-weight:700;white-space:nowrap}
.badge .bd{width:6px;height:6px;border-radius:50%;flex-shrink:0}
.badge.type-campaign{background:var(--primary-08);color:var(--color-primary-dark)} .type-campaign .bd{background:var(--color-primary)}
.badge.type-period{background:var(--secondary-12);color:#b9760a} .type-period .bd{background:var(--color-secondary)}
.badge.type-free{background:var(--violet-12);color:#6d3fd1} .type-free .bd{background:var(--violet)}
.badge.st-ok{background:rgba(22,163,122,.12);color:var(--success)} .st-ok .bd{background:var(--success)}
.badge.st-wait{background:rgba(244,166,30,.14);color:#b9760a} .st-wait .bd{background:var(--warning)}
.badge.st-fail{background:rgba(224,85,107,.12);color:var(--danger)} .st-fail .bd{background:var(--danger)}
.date-cell{color:#6c7479;font-size:12.5px;white-space:nowrap}
.tbtn{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;color:var(--color-muted);transition:background .2s,color .2s}
.tbtn:hover{background:var(--primary-08);color:var(--color-primary-dark)}
.tbtn .ic{width:18px;height:18px}
@keyframes rowIn{from{opacity:0;transform:translateY(9px)}to{opacity:1;transform:none}}
.dtable tbody tr.enter{animation:rowIn .42s var(--ease) both}

/* pager */
.pager{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:18px;flex-wrap:wrap}
.pager-info{font-size:12px;color:var(--color-muted);font-weight:500}
.pager-controls{display:flex;align-items:center;gap:6px}
.page-btn{min-width:36px;height:36px;padding:0 11px;border-radius:11px;display:inline-flex;align-items:center;justify-content:center;gap:5px;font-weight:700;font-size:13px;color:#5b6469;transition:background .2s,color .2s,box-shadow .2s,opacity .2s}
.page-btn:hover:not(:disabled):not(.active){background:var(--primary-08);color:var(--color-primary-dark)}
.page-btn.active{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff;box-shadow:0 8px 16px -8px rgba(0,123,122,.65)}
.page-btn:disabled{opacity:.38;cursor:not-allowed}
.page-btn .ic{width:16px;height:16px}
.page-nav{border:1px solid var(--color-border);background:#fff;font-size:12px}

/* reveal */
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
.reveal{opacity:0;animation:fadeUp .6s var(--ease) forwards}
.sidebar{animation:sbIn .5s ease both}
@keyframes sbIn{from{opacity:0}to{opacity:1}}

/* responsive */
@media (max-width:1200px){ .stats{grid-template-columns:repeat(2,1fr)} .grid-2{grid-template-columns:1fr} }
@media (max-width:1024px){ .main{margin-right:0 !important} .sidebar{box-shadow:var(--shadow-lg)} }
@media (max-width:760px){
  .search{display:none}
  .tb-title span{display:none}
  .welcome{flex-direction:column;align-items:flex-start;padding:24px}
  .welcome-actions{width:100%}
  .welcome-actions .btn{flex:1;justify-content:center}
  .welcome-goal{width:100%;justify-content:center}
  .content{padding:16px}
  .profile .pm{display:none}
  .chart-wrap{height:260px}
  .grid-2{margin-top:42px}
}
@media (max-width:480px){ .stats{grid-template-columns:1fr} }

@media (prefers-reduced-motion:reduce){
  *{animation-duration:.001ms !important;animation-iteration-count:1 !important;transition-duration:.01ms !important}
}
/* ============ SPA — پوسته‌ی ثابت (پنل + هدر) ============ */
/* محتوای صفحات داخل این آی‌فریم بارگذاری می‌شود؛ پنل و هدر دیگر دوباره لود نمی‌شوند */
.spa-frame{display:none;width:100%;height:calc(100vh - 70px);border:0;background:var(--color-bg)}
body.spa-active .spa-frame{display:block;animation:fadeUp .35s var(--ease)}
body.spa-active .content{display:none}
/* نوار پیشرفت نازک هنگام جابه‌جایی بین صفحات */
.spa-bar{position:fixed;top:0;right:0;left:0;height:3px;z-index:80;transform:scaleX(0);transform-origin:right center;opacity:0;
  background:linear-gradient(90deg,var(--color-primary),var(--color-secondary));
  transition:transform .35s var(--ease),opacity .3s}
.spa-bar.show{opacity:1}
/* حالت فعال برای زیرلینک‌های منو هنگام باز بودن یک صفحه */
.nav-sub-link.active{background:var(--primary-08);color:var(--color-primary-dark);font-weight:700}
.nav-sub-link.active .ic{opacity:1}

/* ============ دکمه‌ی «داشبورد مدیریت» (زیر لوگو) ============ */
.dash-btn{display:flex;align-items:center;gap:11px;margin:10px 12px 4px;padding:13px 14px;border-radius:16px;font-family:inherit;font-weight:800;font-size:13.5px;color:#fff;cursor:pointer;
  background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));
  box-shadow:0 12px 24px -12px rgba(0,102,101,.85);position:relative;overflow:hidden;
  transition:transform .16s var(--ease),box-shadow .25s var(--ease),filter .2s}
.dash-btn::before{content:'';position:absolute;inset:0;background:radial-gradient(120% 120% at 100% 0,rgba(255,255,255,.22),transparent 55%);pointer-events:none}
.dash-btn:hover{transform:translateY(-2px);box-shadow:0 18px 30px -12px rgba(0,102,101,.95)}
.dash-btn:active{transform:scale(.97)}
.dash-btn .dash-ic{width:30px;height:30px;border-radius:10px;display:grid;place-items:center;background:rgba(255,255,255,.20);flex-shrink:0;position:relative;z-index:1}
.dash-btn .dash-ic .ic{width:18px;height:18px}
.dash-btn>span:last-child{flex:1;text-align:start;position:relative;z-index:1}
.dash-btn.active{box-shadow:0 0 0 2px var(--color-surface),0 0 0 4px var(--color-primary-light),0 12px 24px -12px rgba(0,102,101,.85)}

/* ============ Box 1: انتخابگر شعبه ============ */
.branch-pick{position:relative;margin:8px 12px 2px}
.branch-btn{width:100%;display:flex;align-items:center;gap:11px;padding:11px 13px;border-radius:14px;
  background:var(--color-bg);border:1px solid var(--color-border);font-family:inherit;cursor:pointer;
  transition:border-color .2s,box-shadow .2s,background .2s}
.branch-btn:hover:not(:disabled){border-color:var(--color-primary-light)}
.branch-btn:disabled{cursor:default;opacity:.95}
.branch-ic{width:34px;height:34px;border-radius:11px;display:grid;place-items:center;flex-shrink:0;
  background:var(--secondary-12);color:#b9760a}
.branch-ic .ic{width:18px;height:18px}
.branch-meta{flex:1;min-width:0;text-align:start}
.branch-label{display:block;font-size:10.5px;color:var(--color-muted);font-weight:600}
.branch-name{display:block;font-size:13px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.branch-btn .chev{width:16px;height:16px;color:var(--color-muted);transition:transform .3s var(--ease);flex-shrink:0}
.branch-pick.open .branch-btn .chev{transform:rotate(180deg)}
.branch-pick.open .branch-btn{border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:#fff}
.branch-menu{position:absolute;top:calc(100% + 6px);inset-inline:0;background:#fff;border:1px solid var(--color-border);
  border-radius:14px;box-shadow:var(--shadow-lg);padding:6px;z-index:50;max-height:320px;overflow-y:auto;
  opacity:0;visibility:hidden;transform:translateY(-8px) scale(.98);transform-origin:top;transition:opacity .2s,transform .22s var(--ease),visibility .2s}
.branch-pick.open .branch-menu{opacity:1;visibility:visible;transform:translateY(0) scale(1)}
.branch-menu li{padding:10px 12px;border-radius:11px;font-size:12.5px;font-weight:600;color:#5b6469;cursor:pointer;
  display:flex;align-items:center;gap:9px;transition:background .15s,color .15s}
.branch-menu li:hover{background:var(--primary-08);color:var(--color-primary-dark)}
.branch-menu li.sel{background:var(--primary-08);color:var(--color-primary-dark)}
.branch-menu li .bdot{width:7px;height:7px;border-radius:50%;background:var(--color-primary);flex-shrink:0;margin-inline-start:auto}
.branch-menu li.disabled{opacity:.5}
.branch-menu li .hq-tag{font-size:9.5px;font-weight:800;color:#b9760a;background:var(--secondary-12);padding:2px 7px;border-radius:99px}
[data-theme="dark"] .branch-pick.open .branch-btn,[data-theme="dark"] .branch-menu{background:var(--color-surface)}

/* ============ دکمه‌ی تغییر تم (روشن/تاریک) ============ */
.theme-toggle{position:relative;overflow:hidden}
.theme-toggle .ic{position:absolute;inset:0;margin:auto;width:21px;height:21px;transition:transform .45s var(--ease),opacity .35s var(--ease)}
.theme-toggle .ic-sun{transform:rotate(0) scale(1);opacity:1}
.theme-toggle .ic-moon{transform:rotate(-90deg) scale(.4);opacity:0}
[data-theme="dark"] .theme-toggle .ic-sun{transform:rotate(90deg) scale(.4);opacity:0}
[data-theme="dark"] .theme-toggle .ic-moon{transform:rotate(0) scale(1);opacity:1}

/* ============ پنل اعلان‌ها ============ */
.notif-overlay{position:fixed;inset:0;background:rgba(16,40,40,.42);backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);opacity:0;visibility:hidden;transition:opacity .3s,visibility .3s;z-index:70}
.notif-overlay.show{opacity:1;visibility:visible}
.notif-panel{position:fixed;top:0;left:0;height:100vh;height:100dvh;width:390px;max-width:92vw;background:var(--color-surface);border-right:1px solid var(--color-border);box-shadow:var(--shadow-lg);transform:translateX(-100%);transition:transform .42s var(--ease);z-index:75;display:flex;flex-direction:column}
.notif-panel.show{transform:translateX(0)}
.notif-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 18px 16px;border-bottom:1px solid var(--color-border);flex-shrink:0}
.notif-head-title{display:flex;align-items:center;gap:12px;min-width:0}
.notif-head-ic{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));color:#fff;flex-shrink:0}
.notif-head-ic .ic{width:21px;height:21px}
.notif-head h3{font-size:16px;font-weight:800}
.notif-head span{font-size:12px;color:var(--color-muted)}
.notif-list{flex:1;overflow-y:auto;padding:10px 12px;display:flex;flex-direction:column;gap:4px}
.notif-item{display:flex;gap:12px;padding:13px 12px;border-radius:15px;transition:background .2s;position:relative}
.notif-item:hover{background:var(--primary-08)}
.notif-ic{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;flex-shrink:0}
.notif-ic .ic{width:20px;height:20px}
.notif-body{flex:1;min-width:0}
.notif-title{font-weight:700;font-size:13.5px;display:flex;align-items:center;gap:7px}
.notif-item.unread .notif-title::after{content:'';width:8px;height:8px;border-radius:50%;background:var(--color-secondary);flex-shrink:0;box-shadow:0 0 0 3px var(--secondary-12)}
.notif-text{font-size:12.5px;color:var(--color-muted);margin-top:3px;line-height:1.6}
.notif-time{font-size:11px;color:var(--color-muted);font-weight:600;margin-top:6px;opacity:.85}
.notif-foot{padding:14px;border-top:1px solid var(--color-border);flex-shrink:0}
.notif-foot .btn{width:100%;justify-content:center}

/* ============ تم تاریک ============ */
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --primary-08:rgba(79,178,176,.10); --primary-12:rgba(79,178,176,.16); --primary-16:rgba(79,178,176,.24);
  --secondary-12:rgba(244,166,30,.18); --violet-12:rgba(139,92,246,.20);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  --shadow-lg:0 24px 48px -16px rgba(0,0,0,.62),0 10px 24px -12px rgba(0,0,0,.5);
  color-scheme:dark;
  background-color:var(--color-bg);                   /* ناودانِ اسکرول‌بار/کانواسِ html در دارک‌مود تیره شود */
  scrollbar-color:rgba(255,255,255,.22) transparent;  /* اسکرول‌بار فایرفاکس */
}
/* اسکرول‌بارِ وب‌کیت (کروم/اج/سافاری) در دارک‌مود — هم خودِ html و هم عناصر داخلی */
[data-theme="dark"]::-webkit-scrollbar-thumb,
[data-theme="dark"] *::-webkit-scrollbar-thumb{background:rgba(255,255,255,.18);background-clip:padding-box}
[data-theme="dark"]::-webkit-scrollbar-thumb:hover,
[data-theme="dark"] *::-webkit-scrollbar-thumb:hover{background:rgba(255,255,255,.32);background-clip:padding-box}
[data-theme="dark"] .topbar{background:rgba(16,23,27,.82)}
[data-theme="dark"] .search:focus-within,
[data-theme="dark"] .select.open .select-btn,
[data-theme="dark"] .select-menu,
[data-theme="dark"] .chip,
[data-theme="dark"] .page-nav{background:var(--color-surface)}
[data-theme="dark"] .nav-item,
[data-theme="dark"] .nav-group-header{color:#abb5ba}
[data-theme="dark"] .nav-sub-link{color:#9aa4a9}
[data-theme="dark"] .donor-bar{background:rgba(255,255,255,.08)}
[data-theme="dark"] .rank-badge{background:rgba(255,255,255,.06)}
[data-theme="dark"] .dtable tbody tr:hover,
[data-theme="dark"] .donor:hover{background:rgba(255,255,255,.04)}

/* ============ موبایل ============ */
@media (max-width:760px){
  .topbar{padding:0 12px;gap:8px}
  .tb-title{overflow:hidden}
  .tb-title h1{overflow:hidden;text-overflow:ellipsis;max-width:46vw}
  .notif-panel{width:100vw;max-width:100vw}
}
</style>
</head>
<body>
<div class="app" id="app">
  <div class="spa-bar" id="spaBar" aria-hidden="true"></div>

  <!-- ============ SIDEBAR ============ -->
  <aside class="sidebar" id="sidebar">
    <div class="sb-header">
      <img class="brand-logo" src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/4gHYSUNDX1BST0ZJTEUAAQEAAAHIAAAAAAQwAABtbnRyUkdCIFhZWiAH4AABAAEAAAAAAABhY3NwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAA9tYAAQAAAADTLQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlkZXNjAAAA8AAAACRyWFlaAAABFAAAABRnWFlaAAABKAAAABRiWFlaAAABPAAAABR3dHB0AAABUAAAABRyVFJDAAABZAAAAChnVFJDAAABZAAAAChiVFJDAAABZAAAAChjcHJ0AAABjAAAADxtbHVjAAAAAAAAAAEAAAAMZW5VUwAAAAgAAAAcAHMAUgBHAEJYWVogAAAAAAAAb6IAADj1AAADkFhZWiAAAAAAAABimQAAt4UAABjaWFlaIAAAAAAAACSgAAAPhAAAts9YWVogAAAAAAAA9tYAAQAAAADTLXBhcmEAAAAAAAQAAAACZmYAAPKnAAANWQAAE9AAAApbAAAAAAAAAABtbHVjAAAAAAAAAAEAAAAMZW5VUwAAACAAAAAcAEcAbwBvAGcAbABlACAASQBuAGMALgAgADIAMAAxADb/2wBDAAUDBAQEAwUEBAQFBQUGBwwIBwcHBw8LCwkMEQ8SEhEPERETFhwXExQaFRERGCEYGh0dHx8fExciJCIeJBweHx7/2wBDAQUFBQcGBw4ICA4eFBEUHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh7/wAARCACFAJIDASIAAhEBAxEB/8QAHAABAQACAwEBAAAAAAAAAAAAAAcFBgEECAID/8QAThAAAQMDAQQGAgwIDQUBAAAAAQACAwQFEQYHEiExE0FRYXGBIpEUFjI2UlWUobGz0dIVFzNic5KywSM0QmNygoOToqTCw+IkRVNUhKP/xAAbAQACAwEBAQAAAAAAAAAAAAAABQMEBgECB//EADwRAAEDAgIECgYKAwAAAAAAAAEAAgMEEQUhBhIxcRMUMzRBUWGBobEWYnKRweEVIjJSU2OSotHwJEOy/9oADAMBAAIRAxEAPwDxkiIhCIstpew1l/uHsamwyJmDNM4ejG395PUOvuAJFZselrJaYmiGijmmHEzztD3k9ozwb5YU8NO+XMbEqxDF6eh+q/N3UPj1KILkAnkCfBei99+Mb7vWnSP+G71qzxD1klOlg6IvH5Lzu2GZ/uYpHeDSV9to6twy2lncO6Mr0L0j/hu9a433/Cd613iHreC8+ln5X7vkvPzbdcHDLaCqI7oXfYvsWm6kZFsrSO6B32K/bzvhH1pk9pRxD1vBHpZ+V+75KBizXgjItVf8nf8AYufwHevie4fJn/Yr1k9qI4h63guelh/C/d8lBvwDfPia4/JX/YufwBffiW5fJX/YrwnHvXeID7y56WO/C8fkvPdXS1VJJ0VXTTU78Z3ZWFp9RX4r0PUQw1MDoKiKOaJ3uo5GhzT5Hgp1rnQ8cEEtzsrXBjBvTUuc7o63MPPHWQe/HYoJaNzBcZplQaRQVTxG8apOzpHvU9REVRaFEREIREXZtcbJrnSxSjLHzMa7wLgChCtWkLSyy2Cmo9zdmLekqD1mQjiPLl5LsX67Udltzq2teQwHdYxvFz3dgXfJySSpLtZrpJ9T+wiT0dHE1rW9W84BxPjxA/qhOJn8BEA1fOcOpzitcXSnLMn+P70Lm47Rb7NI72EKeijJ4BsYkdjvLgePgAun7e9VfGg+TRfdWtIlZledpK3TMPpWCzY2+4LZPb1qr40HyeL7qe3nVPxp/l4vurW1+9vpKivrYaOljMk0zg1jR2/uHaepc4R/WV7NJTAXLG+4LOe3jVHxp/8AhH91cHW+qD/3Q/3Ef3Vvdi0FZqGFpuDPwhUcC4ucWxg/mtGMjxznu5Lu3DRunKyIsNubTuxgSU7iwt8uR8wVbFNORe/ikD8awtr9UMuOvVFv58FNjrXU553R391H91ce3PU3xtIPCNn2Lr6tsNRp+5+xZXdLE9u/DMBgPb+4jrH2hYdVXOe02JT6KGmkYHsaCD2BZ/246m+N5/IN+xcN1jqcO3vwxUHudgj1EYWBRedd3WpOLQ/cHuCpGkNfSVFVHQ33ox0hDWVTQG4d+eOWD2jGOvtFD4td2ELzornoyslr9KW6qmyZHRbjiTku3HFmT3ndz5phRzOcSxyx+kWGRQNbURC1zYgealevrSy0alnhhZuU8wE8IHINdzA7g4OA7gFgFvu2djRdbbIB6TqUgntw92PpWhKjM0NkIC1OHTOmpY5HbSAiIijV1F2bUQLpSE8hOz9oLrL96AhtdTuPIStJ9YQhegzzKjW0738V/hF9UxWV3uj4qO7VI+j1zXDOcsgd64WJpXfYG9YTRY/5Lx6vxC1dEWz6G0q/UEz555HwUMLt172j0nu57rc8OXM9WR2pa1pebBbaeeOCMySGwC1hbhsjZE7VbnPA3mUr3R56nZA4eRK3huiNLiMNNr3iBjeNRJk9/usfMsHV6Vn0zcob/YnSVUNOS6emeRv7hGHbpHPgT1ZHA8VaFNJEQ8jIJE7GaWujfTsJDnAgXyzIW/IvwoKumrqOKspJWywSt3mOHX9h6iOpfuBk4Caggi4WBexzHFrhYhaHtlYw2u3SOHptme1p7i0Z+hq0C12e6XPJoKCoqGg4L2s9EHsLuQVGuNLFrDVnsdxLrVaAWyuacdLK48Wg9nogEj4JweIK3OKOOGJkMMbI42DdYxjQGtHYAOSXmDh5C++S17MW+i6SODVu+1z2XNxft7FB7pZbtawHV9BPAwnAe5uWE9m8OGVj16HniingfBPG2WKRu69jhkOHYQopreyixX+SkiJNPI0SwZPHcOeB8CCPJQVFMYswck1wjGm15LHCzhnvCwatWzj3kWzwl+teoqrVs495Ft8JfrXr1Rcp3KHSfmY9oeRWq7aP49bP0D/2lP1Qds/8bth/mn/tBT5Q1HKuV/BuYxbkREUKZovuBwbPG48g4H518Llpw4HsKEL0U/3Z8VH9rHHXFWe2KD6lisD/AHZ8VHtqpzrWqP8ANQ/VtTSu5Mb1g9Fudu9k+YWqq36FpmUukbdGzB34ulce0vO9x9ePJRBVTZdf6aotUdlnkDKuDIiDj+VYSTw7SMkY7AO/FajcGyZp7pHDJLR/UF7EE7s1uyAkHI4FF1rjX0VtpjU19THTRD+U88z2Acye4cU2JAFyvn8bHSODWC57Fouo6qs0TqEVNuY11sryZHUruDA8Y3w34J4ggjhxAwQF2tY6kvQsAkobHdbfHM3E1VUQlojBwMNI7c43jjuGTkLPqej1Hr220kkQjoIHvkgEoG9JMGHdJ7MdQHXjnwxT5GskY6OZrXxuaWva8ZaWnmDnqwqUURlDtR1gtJXVjaJ8HGYdaS1yb9thvO9aRs7ofYGkaMFu7JUA1D+PPe9yf1Q1bCp3p3U15hqJaKitFTd7XDKYqaSGN2+yMHDRvAEEBvbx71uEN6Y6RzJ7VeaXAzmSge8Hzj3l7gmZqAdSrYrhtVxl7yL6xvkfdltWUUw2xyRG70MQA6VtOXOPcXHA+Y+tZ6+bQbTRtfHb45q2oHAbzDGxp784dw7MeYUwutfU3O4TV1ZJvzSuy48h2ADuAwFDVzsc3Vbmmmj+EzwzcPMNUWyHSuqrTs395Nu8JfrXqLKz7NfeVQf2n1jlDRcor+k3Mu8fFa1tnH/UWw/mSfS1T1UPbQP4S1HtbL9LFPFHU8q5XcF5jHuRERQJoiIiEL0XJ7t3iVHtqnvyqD2xRfsBWKX8o7xKju1T33y/oY/2U0reTG9YPRjKsePVPmFqqIiVreKvbFNI6y1y6WZ+objb7FA/clqOlLnyOxxZHk88czyGRz5K+W7ZToSlAdUWRt0qMAPqLjK+okfjt3jgeQAX3sOho4dkunG0Ib0bqMPeW9chJMme/ez6lua+L6R6TV89ZJCyQsYwkAA22G1yRnn4Lc4Vg9JDC2QsBc4XJt1qO7d9n2l6TZpcrrY7BQW64UBjqI5qWLo3boeA4cO4k+S8+3a+6lq9KQTPvFTNQSPNNOx26HCQDOC4AFwc05494PLJ9X7dauGi2R6ikmIAkpehb3ue4NH0rypaKU/itvVTIPRNTH0We0OYCR5OI9a3GgdTUVGHvMribOOZJOVgVmtKYoIalhDR0dA2k2/u5Vtwa07jAA1ow0DkAOQXCx2mrg26WCirg7edJC0Sf0xwd/iBWRX09hBaCF8QqWOjme120ErBav03SX+ifmNkde1v8BPyOeprj1t+jmO+KSMfHI6ORpY9pIc0jBBHUV6I9XmoFfqmKsvlfVw56KepkkZkY9FziR9KX1zGggjpWx0XqZZI3xvNw21u++S6Ss2zP3l0XjJ9Y5RlWTZiQdG0vc+T9sqOi5VWtJeZd4WA20jjaT3TfSxTpUbbTytH9t/oU5UdTypVzBeYx7viiIigTREREIXoyX8q/wDpFR7ar77ZP0LPoVhl/KO8SpBtX99h/QM/emlbyQ3rCaNZVz/ZPmFqSIiVrdqybAtrkOkIXad1EJHWZ7zJBOxpc6lcfdZaOJYefDiDngc8PQ7NoGhnUPs0ausnQ4zxrGB3LON3Oc92Mrws1j3Bxa1zg0Zdgch2lfKyWLaHUWIzmckscdtrZ+/pTmjxuelj4OwIGy/QrZtp2ifjIu1FpLS/Sfgpk3SPneC3p3gH0t3mGNGTg8SergFq+0uopbTYaDS9DkBoEj+3cGcZ6sudlx7x3rs7HbaxlFVXaQAPlf0DCRyYMFx8CSP1VoWo7i67XyruBziaQlgPMMHBo8gAtTQYfBhlG2CAWH9uT2krK1FXJieJOdIco/8AojLuA8VmNC6rfp+V9PUxvmoJnbz2t90x2Mbzc8+GMjuHYqXBqfT89Mahl4pAwdT37jv1XYPzKGIrkVS+MWGxQV2CU1a/hHXB7OlUPW+uaepopbZZS9zJW7s1S5pblp5taDx48iTjr4daniIopJHSG7lepKOKkj4OIWHmisWy4g6Pg7pJB/iUdVf2VkHSMY7JpB86sUXKpVpLzE7wsNtp9zaP7f8A21OVR9tPuLP4z/7anCjqeVKtYJzCPd8SiIigTVEREIXouT3bvFSXa60t1TGSODqVhH6zh+5UyxVjbjZaKua4O6aFrnEdTsYcPIgjyWu7S9Oy3ehjrqGIyVlKCCxo9KSPngdpByQOvJ68BNqhpfCCN6+f4NM2lxFzZMr3b33+SkiIeBwUSlfQFuGy2mZW3K6UTyAJ7dJGSeoOc0Z+cLUp4pIJnwzMLJI3Fr2kcWkHBBWY0Pdm2bUlNVzOLad2Ypj+Y7hnyOD5LadpmlZn1El8tkXSNeM1UTBkg/8AkAHMHr7OfWcThmvHcbQljqjgKzUecngW3jIjvFll9BA12zs0lI8Nn3J4N7luyOLiPmc0qTSMfHI6ORrmPaSHNcMEEdRWV0zqG4afqHyUbmPjkx0kMgyx+OXLiD3hfWq73FfayOrbbYaOUNxK5jiTKe08vt7+S494exo6QilppKepldta83v0g9RWGREUKZoiIhCKwbLI3s0hE5wwJJpHN7xnH0gqZadsldfa8UtHGd0EdLKR6Ebe0n18OZVutlFBbrdT0FMMQwMDG55ntJ7yck95V6iYS7W6Fl9J6pjYBBf6xN+5aLtp/J2fxn/21OFvO2GvZNeKS3sIPsWIuf3Ofg4/VDT5rRlBUm8rrJrgzCyhjB6vPNERFAmaIiIQt32a6oitrjabjLuUsr96GVx4ROPMHsae3kD4kip9686rP2DV17s0bYYKhs9O3lDON5o8OIIHcCArlPVcGNV2xZvF8BFW/hoTZ3TfYfmqxc9P2S5yGWvtkE0hOTIMse497mkE+a6XtK0r8UD5RL95axDtNkEYE1mY9/WWVG6PUWn6V9Haceqyf5r/AIKczUxzI8ErZhuMsGq15t7S2KfQ+mJIHxx20wvc0hsjZ5CWHtALsHzWL0/fZ9O1TdOakJjbF6NJWcdxzP5IJ+D1A9XI4xwx52mvzwszR/8AT/xXUuevYLnTmmr9PU1RDzDXzHLT2ggZB7xhRuliB1o8ircGH18jTFWDXYe3MHrCoVTZ7NXfw81soZzJ6XS9C0l+eveHPxXXOltNHnY6Q+bx9BUkodQXO2TvNoqp6OBzi5sBf0jG57nDB8cZWbp9o1+jYGyQ0E5+E+JwJ/VcAuiphP2mrkmC4jHlDOSO0kfyFQBpbTI5WOk9b/vLn2sabHKyUnqd9q0IbSr1njQ20juZIP8AWvv8Zd0+LqH/AB/eXvh6b7vgq5wzGfxD+ore/azp3qstGP6i5bpuwNORZ6LziB+laGdpd1xwt9BnvD/vL4/GVe//AEbZ/dyffRw9P1eC59FYwf8AYf1FVCnhhp4RDTwxQxN5MjYGtHgBwWJ1XqKi0/Rl8rmyVb25hpweLuwnsb39fUp5X7QdQ1LNyJ1LR9RMEXE+bi7HktWqJpqiZ89RLJNK85c97i5zj3k814krRa0YVij0aeZOEq3X7BnfeV9VtTPWVctXUvMk0zy97j1kr8URL1rgLZBERELqIiIQiIiEIiIhCIiIQiIiEIiIhCIiIQiIiEIiIhCIiIQv/9k=" alt="لوگوی خیریه مکسا">
      <div class="brand-text">
        <div class="brand-name">مکسا</div>
        <div class="brand-sub">پنل مدیریت خیریه</div>
      </div>
    </div>
    <button class="dash-btn" id="dashHomeBtn" type="button">
      <span class="dash-ic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="2"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="2"/></svg></span>
      <span>داشبورد مدیریت</span>
    </button>

    <!-- ============ Box 1: انتخابگر شعبه ============ -->
    <div class="branch-pick" id="branchPick" data-super="<?= $isSuper ? '1':'0' ?>">
      <button class="branch-btn" id="branchBtn" type="button" <?= $isSuper ? 'aria-haspopup="listbox"' : 'disabled' ?>>
        <span class="branch-ic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/></svg></span>
        <span class="branch-meta">
          <span class="branch-label">شعبه‌ی فعال</span>
          <span class="branch-name" id="branchName"><?= e($ACTIVE_BRANCH_ROW['name'] ?? '—') ?></span>
        </span>
        <?php if ($isSuper): ?>
        <svg class="ic chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
        <?php endif; ?>
      </button>
      <?php if ($isSuper): ?>
      <ul class="branch-menu" id="branchMenu" role="listbox"></ul>
      <?php endif; ?>
    </div>
    <nav class="sb-nav"><div class="nav-list" id="navList"></div></nav>
    <div class="sb-footer">
      <a href="logout.php" id="logoutLink" class="logout-link" target="_top" title="خروج از حساب" aria-label="خروج از حساب">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </a>
      <a href="account.php" class="user-card">
        <div class="user-av"><?= e(mb_substr($U['full_name'] ?: $U['username'], 0, 2, 'UTF-8')) ?></div>
        <div class="user-meta">
          <div class="user-name"><?= e($U['full_name'] ?: $U['username']) ?></div>
          <div class="user-role"><?= e($isSuper ? 'مدیر مرکزی' : (dash_is_branch_admin() ? 'مدیر شعبه' : 'کاربر شعبه')) ?></div>
        </div>
      </a>
    </div>
  </aside>
  <div class="overlay" id="overlay"></div>

  <!-- ============ MAIN ============ -->
  <div class="main">
    <header class="topbar">
      <button class="icon-btn" id="toggleBtn" aria-label="باز و بسته کردن منو">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <div class="tb-title">
        <h1 id="pageTitle">داشبورد</h1>
        <span id="topDate">—</span>
      </div>
      <div class="tb-spacer"></div>
      <button class="icon-btn theme-toggle" id="themeBtn" type="button" aria-label="تغییر تم روشن و تاریک" title="روشن / تاریک" aria-pressed="false">
        <svg class="ic ic-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.6M12 19.4V22M2 12h2.6M19.4 12H22M4.93 4.93l1.84 1.84M17.23 17.23l1.84 1.84M19.07 4.93l-1.84 1.84M6.77 17.23l-1.84 1.84"/></svg>
        <svg class="ic ic-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A8.5 8.5 0 1 1 11.2 3a6.6 6.6 0 0 0 9.8 9.8Z"/></svg>
      </button>
      <button class="icon-btn" id="notifBtn" type="button" aria-label="اعلان‌ها">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        <span class="bell-dot"></span>
      </button>
      <!-- حساب کاربریِ هدر طبق درخواست مخفی شد (کد حفظ شده، فقط کامنت است)
      <a href="maxa-link.php" class="profile">
        <div class="av">م‌ا</div>
        <div class="pm"><b>مدیر اصلی</b><span>admin@maxa.org</span></div>
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      </a>
      -->
    </header>

    <main class="content">
      <svg class="cable-svg" id="cableSvg" aria-hidden="true"></svg>
      <!-- welcome -->
      <section class="welcome reveal" style="animation-delay:.05s">
        <div class="welcome-text">
          <h2>سلام، خوش آمدید 👋</h2>
          <p id="welcomeDate">نگاهی سریع به وضعیت کمک‌ها، خیرین و فعالیت‌های اخیر خیریه مکسا.</p>
        </div>
        <div class="welcome-goal">
          <div class="ring" data-pct="<?= $ringPct ?>">
            <svg viewBox="0 0 120 120">
              <defs><linearGradient id="ringg" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#ffd98a"/><stop offset="100%" stop-color="#f4a61e"/>
              </linearGradient></defs>
              <circle class="ring-bg" cx="60" cy="60" r="50"/>
              <circle class="ring-fg" cx="60" cy="60" r="50"/>
            </svg>
            <div class="ring-center"><b data-pctlabel>۰٪</b><span>تحقق هدف</span></div>
          </div>
          <div class="goal-meta">
            <div class="goal-title">هدف ماهانه خیریه</div>
            <div class="goal-fig"><span>جمع‌آوری‌شده</span><b><?= money_compact($goalCollected) ?> تومان</b></div>
            <div class="goal-fig"><span>هدف این ماه</span><b><?= money_compact($goalTarget) ?> تومان</b></div>
            <a href="maxa-link.php" class="btn btn-gold">
              <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              گزارش‌گیری
            </a>
          </div>
        </div>
      </section>

      <!-- stats -->
      <section class="stats" id="statsGrid"></section>

      <!-- chart + donors -->
      <section class="grid-2">
        <div class="card chart-card reveal" style="animation-delay:.35s">
          <div class="card-head">
            <div>
              <h3 id="chartTitle">روند کمک‌های جمع‌آوری‌شده</h3>
              <div class="sub" id="chartSub">مجموع مبالغ اهدایی در بازه‌ی انتخابی</div>
            </div>
            <div class="select" id="periodSelect">
              <button class="select-btn" id="selectBtn" type="button" aria-haspopup="listbox" aria-expanded="false">
                <span id="selectLabel">ماهانه</span>
                <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <ul class="select-menu" id="selectMenu" role="listbox">
                <li data-val="day" role="option">روزانه</li>
                <li data-val="week" role="option">هفتگی</li>
                <li data-val="month" class="sel" role="option">ماهانه</li>
                <li data-val="year" role="option">سالانه</li>
              </ul>
            </div>
          </div>
          <div class="chips" id="chips">
            <button class="chip on" type="button" data-ds="0" style="--c:#007b7a;--cbg:rgba(0,123,122,.08)">
              <span class="check"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
              <span class="dot"></span><span>کمک به کمپین‌ها</span><span class="chip-total"></span>
            </button>
            <button class="chip on" type="button" data-ds="1" style="--c:#f4a61e;--cbg:rgba(244,166,30,.10)">
              <span class="check"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
              <span class="dot"></span><span>دوره‌ها</span><span class="chip-total"></span>
            </button>
            <button class="chip on" type="button" data-ds="2" style="--c:#8b5cf6;--cbg:rgba(139,92,246,.10)">
              <span class="check"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
              <span class="dot"></span><span>پرداخت آزاد</span><span class="chip-total"></span>
            </button>
          </div>
          <div class="chart-wrap"><canvas id="chart"></canvas></div>
        </div>

        <div class="card donors-card reveal" style="animation-delay:.45s">
          <div class="card-head">
            <div><h3>خیرین برتر</h3><div class="sub">بیشترین مبلغ اهدایی</div></div>
            <span class="tag">این ماه</span>
          </div>
          <div class="donor-list" id="donorsList"></div>
        </div>
      </section>

      <!-- recent table -->
      <section class="card table-card reveal" style="animation-delay:.55s">
        <div class="card-head">
          <div><h3>حمایت‌های اخیر</h3><div class="sub">آخرین تراکنش‌های ثبت‌شده در سامانه</div></div>
          <a href="maxa-link.php" class="link-more">مشاهده همه
            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          </a>
        </div>
        <div class="table-scroll">
          <table class="dtable">
            <thead>
              <tr><th>خیر</th><th>مبلغ</th><th>نوع</th><th>تاریخ</th><th>وضعیت</th><th></th></tr>
            </thead>
            <tbody id="tableBody"></tbody>
          </table>
        </div>
        <div class="pager">
          <span class="pager-info" id="pagerInfo"></span>
          <div class="pager-controls" id="pagerControls"></div>
        </div>
      </section>
    </main>

    <!-- ناحیه‌ی محتوای SPA: صفحات دیگر این‌جا (داخل آی‌فریم) بارگذاری می‌شوند -->
    <iframe class="spa-frame" id="spaFrame" title="محتوای صفحه" frameborder="0"></iframe>
  </div>
</div>

<!-- ============ پنل اعلان‌ها ============ -->
<div class="notif-overlay" id="notifOverlay"></div>
<aside class="notif-panel" id="notifPanel" aria-hidden="true" aria-label="اعلان‌ها">
  <div class="notif-head">
    <div class="notif-head-title">
      <div class="notif-head-ic"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg></div>
      <div><h3>اعلان‌ها</h3><span>آخرین رویدادهای سامانه</span></div>
    </div>
    <button class="icon-btn" id="notifClose" type="button" aria-label="بستن">
      <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <div class="notif-list" id="notifList"></div>
  <div class="notif-foot"><button class="btn btn-primary" id="notifMark" type="button">علامت‌گذاری همه به‌عنوان خوانده‌شده</button></div>
</aside>

<script>
(function(){
  "use strict";
  /* ---------- داده‌های دریافت‌شده از سرور (PHP) ---------- */
  const SERVER = <?= json_encode($SERVER + ['menu' => $MENU], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  if (SERVER && SERVER.dbError) { try{ console.warn('DB:', SERVER.dbError); }catch(e){} }

  /* ---------- helpers ---------- */
  const toFa = s => String(s).replace(/\d/g,d=>'۰۱۲۳۴۵۶۷۸۹'[d]).replace(/\./g,'٫');
  const sep  = n => String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g,'٬');
  const faNum= n => toFa(sep(n));
  function compact(v){
    if(v>=1e9) return toFa((v/1e9).toFixed(1))+' میلیارد';
    if(v>=1e6) return toFa(Math.round(v/1e6))+' م';
    if(v>=1e3) return toFa(Math.round(v/1e3))+' هزار';
    return faNum(v);
  }
  const fullToman = v => faNum(v)+' تومان';
  function shade(hex,p){ p=p||-20; const h=hex.replace('#','');
    let r=parseInt(h.substr(0,2),16),g=parseInt(h.substr(2,2),16),b=parseInt(h.substr(4,2),16);
    r=Math.max(0,Math.min(255,r+p));g=Math.max(0,Math.min(255,g+p));b=Math.max(0,Math.min(255,b+p));
    return 'rgb('+r+','+g+','+b+')'; }
  function hexA(hex,a){ const h=hex.replace('#','');
    return 'rgba('+parseInt(h.substr(0,2),16)+','+parseInt(h.substr(2,2),16)+','+parseInt(h.substr(4,2),16)+','+a+')'; }
  function initials(name){ const p=name.trim().split(/\s+/); return (p[0][0]||'')+(p[1]?p[1][0]:''); }

  /* ---------- icons ---------- */
  const ICONS={
    grid:'<rect x="3" y="3" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="2"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="2"/>',
    award:'<circle cx="12" cy="9" r="5.5"/><path d="M8.2 13.2 6.5 21l5.5-3 5.5 3-1.7-7.8"/>',
    news:'<path d="M4 5a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Z"/><path d="M18 8h1.5A1.5 1.5 0 0 1 21 9.5V18a2 2 0 0 1-2 2"/><line x1="8" y1="8" x2="14" y2="8"/><line x1="8" y1="12" x2="14" y2="12"/><line x1="8" y1="16" x2="12" y2="16"/>',
    users:'<path d="M16 21v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V21"/><circle cx="9.5" cy="7" r="3.5"/><path d="M21 21v-1.5a4 4 0 0 0-3-3.87"/><path d="M16.5 3.6a3.5 3.5 0 0 1 0 6.8"/>',
    flag:'<path d="M5 21V4"/><path d="M5 4h12l-2.5 4L17 12H5"/>',
    box:'<path d="M21 8.5 12 3 3 8.5 12 14l9-5.5Z"/><path d="M3 8.5v7L12 21l9-5.5v-7"/><line x1="12" y1="14" x2="12" y2="21"/>',
    plus:'<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
    list:'<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3.6" cy="6" r="1.2" fill="currentColor" stroke="none"/><circle cx="3.6" cy="12" r="1.2" fill="currentColor" stroke="none"/><circle cx="3.6" cy="18" r="1.2" fill="currentColor" stroke="none"/>',
    eye:'<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
    network:'<circle cx="6" cy="6" r="2.4"/><circle cx="18" cy="6" r="2.4"/><circle cx="12" cy="18" r="2.4"/><path d="M7.6 7.8 11 15.6"/><path d="M16.4 7.8 13 15.6"/><path d="M8.4 6h7.2"/>',
    chevron:'<polyline points="6 9 12 15 18 9"/>',
    chevright:'<polyline points="9 18 15 12 9 6"/>',
    chevleft:'<polyline points="15 18 9 12 15 6"/>',
    activity:'<path d="M22 12h-4l-3 8L9 4l-3 8H2"/>',
    wallet:'<path d="M19 7V5.5A1.5 1.5 0 0 0 17.5 4H6a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2H6"/><circle cx="16.5" cy="12" r="1.3" fill="currentColor" stroke="none"/>',
    target:'<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none"/>',
    up:'<polyline points="3 17 9 11 13 15 21 7"/><polyline points="15 7 21 7 21 13"/>',
    down:'<polyline points="3 7 9 13 13 9 21 17"/><polyline points="15 17 21 17 21 11"/>',
    more:'<circle cx="5" cy="12" r="1.6" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none"/><circle cx="19" cy="12" r="1.6" fill="currentColor" stroke="none"/>',
    chat:'<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/>',
    medical:'<path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2"/><rect x="8" y="2" width="8" height="4" rx="1"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/>',
    book:'<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
    pedia:'<path d="M12 7c-1.5-1-3.5-1.5-5.5-1.5S3.5 6 3 6.5v12c.5-.5 2.5-1 4.5-1S11 18 12 19c1-1 3-1.5 4.5-1.5s4 .5 4.5 1v-12c-.5-.5-2.5-1-4.5-1S13.5 6 12 7Z"/><path d="M12 7v12"/>',
    ticket:'<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 11v2"/><path d="M13 17v2"/>'
  };
  function icon(name,cls){ return '<svg class="ic '+(cls||'')+'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'+(ICONS[name]||'')+'</svg>'; }

  /* ---------- date ---------- */
  try{
    const d=new Intl.DateTimeFormat('fa-IR-u-ca-persian',{weekday:'long',day:'numeric',month:'long',year:'numeric'}).format(new Date());
    document.getElementById('topDate').textContent=d;
    document.getElementById('welcomeDate').textContent='امروز '+d+' — نگاهی سریع به وضعیت کمک‌ها، خیرین و فعالیت‌های اخیر خیریه مکسا.';
  }catch(e){}

  /* ---------- sidebar nav (سرور-محور: محدود به شعبه و دسترسی‌ها) ---------- */
  const MENU = SERVER.menu || {can:{},isSuper:false,isBranchAdmin:false};
  const CAN  = MENU.can || {};
  const NAV  = [];

  // --- مدیریت محتوا ---
  const content = [];
  if (CAN.hero)      content.push({label:'هیروها',icon:'award',children:[
                       {label:'ساخت هیرو جدید',icon:'plus',href:'hero-create.php'},
                       {label:'مدیریت هیروها',icon:'list',href:'hero-management.php'}]});
  if (CAN.news){
    var newsChildren=[
      {label:'ساخت خبر جدید',icon:'plus',href:'news-create.php'},
      {label:'مدیریت اخبار',icon:'list',href:'news-list.php'}];
    // سردبیر (ستاد مرکزی): صفِ بررسیِ اخبارِ ارسال‌شده
    if (MENU.isNewsEditor) newsChildren.push({label:'بررسی سردبیری',icon:'eye',href:'news-list.php?review=1'});
    content.push({label:'خبرها',icon:'news',children:newsChildren});
  }
  if (CAN.partners)  content.push({label:'همکاران',icon:'users',children:[
                       {label:'ساخت همکار جدید',icon:'plus',href:'personal-resume-create.php'},
                       {label:'مدیریت همکاران',icon:'list',href:'Admin-personal-resume-list.php'},
                       {label:'شبکه همکاران مکسا',icon:'network',href:'personal-resume-list.php'}]});
  if (CAN.campaigns) content.push({label:'کمپین‌ها',icon:'flag',children:[
                       {label:'ساخت کمپین جدید',icon:'plus',href:'campaign-create.php'},
                       {label:'مدیریت کمپین‌ها',icon:'list',href:'campaign-status.php'}]});
  if (CAN.courses)   content.push({label:'دوره‌ها',icon:'book',children:[
                       {label:'ساخت دوره جدید',icon:'plus',href:'courses-create.php'},
                       {label:'مدیریت دوره‌ها',icon:'list',href:'courses-manage.php'}]});
  // مکساپدیا فقط از «ستاد مرکزی» و فقط برای مدیر مرکزی (یا کاربرِ دارای دسترسیِ صریح)
  if (MENU.canMaxapedia) content.push({single:true,label:'مکساپدیا',icon:'pedia',href:'maxapedia.php'});
  if (CAN.pages)     content.push({label:'کامپوننت‌ها و صفحات',icon:'box',children:[
                       {label:'ویرایش و ساخت کامپوننت‌ها',icon:'plus',href:'component-create.php'},
                       {label:'ساخت صفحه جدید',icon:'plus',href:'template-create.php'},
                       {label:'مدیریت صفحات',icon:'list',href:'page-list.php'}]});
  if (content.length){ NAV.push({title:'مدیریت محتوا'}); content.forEach(c=>NAV.push(c)); }

  // --- مالی ---
  if (CAN.financial){ NAV.push({title:'مالی'});
    NAV.push({single:true,label:'مشاهده گزارش',icon:'wallet',href:'financial-management.php'}); }

  // --- روابط عمومی ---
  const people=[];
  if (CAN.feedback) people.push({single:true,label:'انتقادات و پیشنهادات',icon:'chat',href:'feedback.php'});
  if (CAN.medical)  people.push({single:true,label:'پرونده‌های پزشکی',icon:'medical',href:'medical-records.php'});
  // سیستم تیکتینگ — برای همه‌ی کاربرانِ پنل در دسترس است (سطحِ دسترسی داخلِ خودِ صفحه اعمال می‌شود)
  people.push({single:true,label:'تیکت‌ها',icon:'ticket',href:'tickets.php'});
  // اثرات کمک (Impact of Donations)
  people.push({single:true,label:'اثرات کمک',icon:'award',href:'donation-impacts.php'});
  if (people.length){ NAV.push({title:'روابط عمومی'}); people.forEach(p=>NAV.push(p)); }

  // --- مدیریت کاربران (ادمین شعبه و سوپرادمین) ---
  if (MENU.isBranchAdmin || MENU.isSuper){
    NAV.push({title:'کاربران شعبه'});
    NAV.push({label:'مدیریت کاربران',icon:'users',children:[
      {label:'افزودن کاربر',icon:'plus',href:'user-add.php'},
      {label:'مدیریت کاربران',icon:'list',href:'user-manage.php'}]});
  }

  // --- شعب (فقط سوپرادمین، و فقط وقتی «ستاد مرکزی» شعبه‌ی فعال است) ---
  if (MENU.isSuper && MENU.isHqView){
    NAV.push({title:'شعب'});
    NAV.push({label:'شعبه‌ها',icon:'network',children:[
      {label:'تعریف شعبه‌ی جدید',icon:'plus',href:'branch-create.php'},
      {label:'مدیریت شعبه‌ها',icon:'list',href:'branch-list.php'}]});
  }
  const navEl=document.getElementById('navList');
  let delay=0;
  NAV.forEach(item=>{
    if(item.title){ navEl.insertAdjacentHTML('beforeend','<div class="nav-section-title" style="animation-delay:'+(delay+=0.04)+'s">'+item.title+'</div>'); return; }
    if(item.single){
      navEl.insertAdjacentHTML('beforeend','<a href="'+item.href+'" class="nav-item '+(item.active?'active':'')+'" style="animation-delay:'+(delay+=0.04)+'s">'+icon(item.icon)+'<span>'+item.label+'</span></a>');
      return;
    }
    const sub=item.children.map(c=>'<a href="'+c.href+'" class="nav-sub-link">'+icon(c.icon)+'<span>'+c.label+'</span></a>').join('');
    navEl.insertAdjacentHTML('beforeend',
      '<div class="nav-group" style="animation-delay:'+(delay+=0.04)+'s">'+
        '<button class="nav-group-header" type="button">'+icon(item.icon)+'<span>'+item.label+'</span>'+icon('chevron','chev')+'</button>'+
        '<div class="nav-sub"><div class="nav-sub-inner">'+sub+'</div></div>'+
      '</div>');
  });
  navEl.querySelectorAll('.nav-group-header').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const group=btn.closest('.nav-group');
      const sub=group.querySelector('.nav-sub');
      const open=group.classList.contains('open');
      navEl.querySelectorAll('.nav-group.open').forEach(g=>{ if(g!==group){ g.classList.remove('open'); g.querySelector('.nav-sub').style.maxHeight='0px'; }});
      if(open){ group.classList.remove('open'); sub.style.maxHeight='0px'; }
      else { group.classList.add('open'); sub.style.maxHeight=(sub.scrollHeight+8)+'px'; }
    });
  });

  /* ---------- Box 1: انتخابگر شعبه (فقط سوپرادمین) ---------- */
  (function(){
    const pick=document.getElementById('branchPick');
    if(!pick || pick.dataset.super!=='1') return;
    const btn=document.getElementById('branchBtn');
    const menu=document.getElementById('branchMenu');
    const branches=(MENU.branches||[]);
    const active=MENU.activeBranch;
    branches.forEach(b=>{
      const li=document.createElement('li');
      li.setAttribute('role','option');
      li.dataset.id=b.id;
      if(b.id===active) li.classList.add('sel');
      if(b.status==='disabled') li.classList.add('disabled');
      li.innerHTML='<span>'+ (b.name||'') +'</span>'+
        (b.is_hq?'<span class="hq-tag">مرکزی</span>':'')+
        (b.id===active?'<span class="bdot"></span>':'');
      li.addEventListener('click',function(){
        if(b.id===active) return;
        // ارسالِ امن: POST با CSRF به switch-branch.php سپس رفرش
        const f=document.createElement('form');
        f.method='POST'; f.action='switch-branch.php';
        f.innerHTML='<input type="hidden" name="csrf_token" value="'+(MENU.csrf||'')+'">'+
                    '<input type="hidden" name="branch_id" value="'+b.id+'">';
        document.body.appendChild(f); f.submit();
      });
      menu.appendChild(li);
    });
    btn.addEventListener('click',function(e){ e.stopPropagation(); pick.classList.toggle('open'); });
    document.addEventListener('click',function(){ pick.classList.remove('open'); });
  })();

  /* ---------- sidebar toggle ---------- */
  const app=document.getElementById('app');
  const mq=()=>window.innerWidth<=1024;
  function setOverlay(){ if(mq() && !app.classList.contains('collapsed')) app.classList.add('show-overlay'); else app.classList.remove('show-overlay'); }
  function toggle(){ app.classList.toggle('collapsed'); setOverlay(); }
  document.getElementById('toggleBtn').addEventListener('click',toggle);
  document.getElementById('overlay').addEventListener('click',()=>{ app.classList.add('collapsed'); setOverlay(); });
  if(mq()) app.classList.add('collapsed');
  setOverlay();
  let wasMobile=mq();
  window.addEventListener('resize',()=>{
    const m=mq();
    if(m!==wasMobile){ app.classList.toggle('collapsed',m); wasMobile=m; }
    setOverlay();
    const og=navEl.querySelector('.nav-group.open .nav-sub'); if(og) og.style.maxHeight=(og.scrollHeight+8)+'px';
  });

  /* ---------- ناوبری SPA: محتوا داخل آی‌فریم؛ پنل و هدر ثابت می‌مانند ---------- */
  (function(){
    const frame=document.getElementById('spaFrame');
    const bar=document.getElementById('spaBar');
    const dashBtn=document.getElementById('dashHomeBtn');
    const tbTitle=document.querySelector('.tb-title');
    const homeTitleHTML=tbTitle?tbTitle.innerHTML:'';   // عنوان/زیرعنوان داشبورد برای بازگشت به خانه
    if(!frame) return;

    // نگاشت «نام فایل» → برچسب و گروهِ آن (برای عنوان هدر و حالت فعالِ منو)
    const MAP={};
    NAV.forEach(it=>{
      if(it.children) it.children.forEach(c=>{ MAP[c.href]={label:c.label,group:it.label}; });
      else if(it.href) MAP[it.href]={label:it.label,group:''};
    });

    let current=null;     // نام فایلِ بازشده (null یعنی داشبورد/خانه)
    let syncing=false;    // محافظ در برابر حلقه‌ی hashchange

    // کلیدِ هویتِ صفحه = نام فایل + query (مثلاً news-list.php?review=1).
    // نگه‌داشتنِ query لازم است تا دو لینکِ هم‌فایل اما با پارامترِ متفاوت
    // («مدیریت اخبار» = news-list.php و «بررسی سردبیری» = news-list.php?review=1)
    // از هم تفکیک شوند؛ وگرنه هر دو با هم active می‌شوند و کلیک روی دومی صفحه را بار نمی‌کند.
    const fileOf=href=>{ try{ const u=new URL(href,location.href); return u.pathname.replace(/^.*\//,'')+u.search; }
                         catch(e){ return (href||'').replace(/#.*$/,'').replace(/^.*\//,''); } };

    function isInternal(a){
      const href=a.getAttribute('href')||'';
      if(!href || href.charAt(0)==='#' || /^(https?:|mailto:|tel:|javascript:)/i.test(href)) return false;
      if(a.target && a.target!=='_self') return false;   // target=_top/_blank → ناوبریِ عادیِ مرورگر (مثلِ خروج)
      if(a.id==='logoutLink' || /(^|\/)logout\.php(\?|#|$)/i.test(href)) return false; // خروج هرگز داخل آی‌فریم باز نشود
      return /\.php(\?|#|$)/i.test(href);   // فقط صفحات داخلیِ .php را داخل آی‌فریم باز کن
    }

    const baseOf=key=>(key||'').split('?')[0];   // فقط نام فایل، بدون query
    function setActive(file){
      navEl.querySelectorAll('.nav-sub-link.active,.nav-item.active').forEach(e=>e.classList.remove('active'));
      if(!file) return;
      const links=Array.from(navEl.querySelectorAll('a[href]'));
      // ۱) تطبیقِ دقیق (شاملِ query): «بررسی سردبیری» را از «مدیریت اخبار» جدا می‌کند
      let matches=links.filter(a=>fileOf(a.getAttribute('href'))===file);
      // ۲) اگر تطبیقِ دقیق نبود (مثلاً صفحه‌بندیِ news-list.php?page=2)، به تطبیقِ فقط-فایل
      //    برگرد، ولی فقط لینک‌هایی که خودشان query ندارند تا لینکِ پایه active بماند.
      if(!matches.length){
        const base=baseOf(file);
        matches=links.filter(a=>{ const k=fileOf(a.getAttribute('href')); return baseOf(k)===base && k.indexOf('?')===-1; });
      }
      matches.forEach(a=>{
        a.classList.add('active');
        const g=a.closest('.nav-group');
        if(g && !g.classList.contains('open')){
          navEl.querySelectorAll('.nav-group.open').forEach(o=>{ if(o!==g){ o.classList.remove('open'); const s=o.querySelector('.nav-sub'); if(s) s.style.maxHeight='0px'; }});
          g.classList.add('open');
          const sub=g.querySelector('.nav-sub'); if(sub) sub.style.maxHeight=(sub.scrollHeight+8)+'px';
        }
      });
    }
    function setTitle(file,override){
      if(!tbTitle) return;
      if(!file){ tbTitle.innerHTML=homeTitleHTML; return; }
      const m=MAP[file]||{};
      const label=m.label || override || file.replace(/\.php.*$/,'');
      tbTitle.innerHTML='<h1 id="pageTitle">'+label+'</h1><span>'+(m.group||'پنل مدیریت مکسا')+'</span>';
    }
    function showBar(){ if(!bar) return; bar.classList.add('show'); bar.style.transform='scaleX(.25)'; requestAnimationFrame(()=>{ bar.style.transform='scaleX(.7)'; }); }
    function hideBar(){ if(!bar) return; bar.style.transform='scaleX(1)'; setTimeout(()=>{ bar.classList.remove('show'); bar.style.transform='scaleX(0)'; },280); }
    // نوار لودینگ همیشه تمام می‌شود: به‌محضِ آماده‌شدنِ DOMِ صفحه‌ی هدف (نه منتظرِ فونت/عکس) + تایم‌اوتِ ایمنی
    let barTimer=null, barPoll=null;
    function clearBarTimers(){ clearTimeout(barTimer); clearInterval(barPoll); barTimer=null; barPoll=null; }
    function finishBar(){ clearBarTimers(); hideBar(); }
    function startBar(){
      showBar(); clearBarTimers();
      barPoll=setInterval(function(){
        let d=null; try{ d=frame.contentDocument; }catch(e){}
        try{ if(d && fileOf(d.location.href)===current && /interactive|complete/.test(d.readyState)) finishBar(); }catch(e){}
      },100);
      barTimer=setTimeout(finishBar,2000);
    }

    function showHome(){
      clearBarTimers();
      current=null;
      document.body.classList.remove('spa-active');
      try{ frame.contentWindow.location.replace('about:blank'); }catch(e){}  // بدون افزودن تاریخچه
      frame.removeAttribute('src');
      setActive(null); setTitle(null);
      if(dashBtn) dashBtn.classList.add('active');
      // اطمینان از اندازه‌ی درستِ نمودار داشبورد پس از بازگشت به خانه
      // پس از نمایشِ دوبارهٔ داشبورد، چند بار رفرش تا چیدمان جا بیفتد و سیمِ نمودار سرِ جایش بنشیند
      [60,280,560].forEach(function(t){ setTimeout(function(){ try{ window.dispatchEvent(new Event('resize')); }catch(e){} }, t); });
    }
    function load(href,push){
      const file=fileOf(href);
      if(file===current && document.body.classList.contains('spa-active')) return;
      current=file;
      startBar();
      document.body.classList.add('spa-active');
      if(dashBtn) dashBtn.classList.remove('active');
      setActive(file); setTitle(file);
      // ناوبریِ آی‌فریم با location.replace تا یک ورودیِ اضافه در تاریخچه‌ی مرورگر ساخته نشود
      // (رفعِ باگِ «دوبار زدنِ back/forward»: قبلاً تغییرِ src یک ورودی می‌ساخت و hash هم یکی)
      try{ frame.contentWindow.location.replace(href); }catch(e){ frame.setAttribute('src',href); }
      if(push){ syncing=true; location.hash='page='+encodeURIComponent(href); setTimeout(()=>{ syncing=false; },0); }
      app.classList.add('collapsed'); setOverlay();   // پس از ناوبری، پنل بسته می‌شود (در همه‌ی اندازه‌ها) — با دکمه‌ی هدر دوباره باز می‌شود
    }

    // پس از بارگذاری صفحه‌ی داخلی: پنل و هدرِ خودِ آن صفحه را مخفی کن و عنوان را همگام کن
    frame.addEventListener('load',function(){
      finishBar();
      // اگر به داشبورد برگشته‌ایم، رویدادِ load مربوط به about:blank نباید تیترِ خانه را خراب کند
      if(!document.body.classList.contains('spa-active')) return;
      let doc=null; try{ doc=frame.contentDocument; }catch(e){}
      if(!doc) return;
      try{
        const st=doc.createElement('style');
        st.textContent='.sidebar,.topbar,.overlay{display:none!important}.main{margin:0!important}.app.collapsed .main{margin:0!important}';
        (doc.head||doc.documentElement).appendChild(st);
      }catch(e){}
      try{
        const p=fileOf(doc.location.href)||current;
        const docTitle=(doc.title||'').split('|')[0].trim();
        if(p!==current){            // ناوبری داخلِ خودِ صفحه (لینک‌های داخل محتوا)
          current=p; setActive(p);
          syncing=true;
          location.hash='page='+encodeURIComponent(doc.location.pathname.replace(/^.*\//,'')+doc.location.search);
          setTimeout(()=>{ syncing=false; },0);
        }
        setTitle(p,docTitle);
      }catch(e){}
    });

    // رهگیری کلیک روی همه‌ی لینک‌های داخلیِ پوسته (منو، هدر، محتوای داشبورد)
    document.addEventListener('click',function(e){
      const a=e.target.closest?e.target.closest('a[href]'):null;
      if(!a || !isInternal(a)) return;
      e.preventDefault();
      load(a.getAttribute('href'),true);
    });

    // دکمه‌های بازگشت/جلوی مرورگر و بازخوانی صفحه
    function route(){
      if(syncing) return;
      const m=/(?:^|[#&])page=([^&]+)/.exec(location.hash);
      if(m) load(decodeURIComponent(m[1]),false);
      else showHome();
    }
    window.addEventListener('hashchange',route);

    // دکمه‌ی «داشبورد مدیریت» = بازگشت به داشبورد (لوگو دیگر دکمه نیست)
    if(dashBtn){
      dashBtn.addEventListener('click',function(){
        if(current!==null) location.hash=''; else showHome();
        app.classList.add('collapsed'); setOverlay();   // پس از رفتن به داشبورد هم پنل بسته می‌شود
      });
    }

    // مسیر اولیه از روی hash (برای بازخوانی/بوکمارک یک صفحه‌ی خاص)
    route();
  })();

  /* ---------- تغییر تم روشن/تاریک ---------- */
  (function(){
    const btn=document.getElementById('themeBtn');
    const root=document.documentElement;
    function apply(t){
      if(t==='dark') root.setAttribute('data-theme','dark'); else root.removeAttribute('data-theme');
      if(btn) btn.setAttribute('aria-pressed', t==='dark' ? 'true' : 'false');
    }
    let cur='light';
    try{
      cur=localStorage.getItem('maxa-theme') ||
          ((window.matchMedia && matchMedia('(prefers-color-scheme:dark)').matches) ? 'dark' : 'light');
    }catch(e){}
    apply(cur);
    if(btn) btn.addEventListener('click',function(){
      cur = (root.getAttribute('data-theme')==='dark') ? 'light' : 'dark';
      apply(cur);
      try{ localStorage.setItem('maxa-theme',cur); }catch(e){}
    });
  })();

  /* ---------- پنل اعلان‌ها ---------- */
  (function(){
    const btn=document.getElementById('notifBtn');
    const panel=document.getElementById('notifPanel');
    const ov=document.getElementById('notifOverlay');
    const closeBtn=document.getElementById('notifClose');
    const markBtn=document.getElementById('notifMark');
    const listEl=document.getElementById('notifList');
    if(!btn||!panel) return;

    // اعلان‌های نمونه (بعداً می‌توان از سرور پر کرد)
    const NOTIFS=[
      {ic:'wallet',accent:'#007b7a',bg:'rgba(0,123,122,.12)',title:'کمک جدید ثبت شد',text:'یک حمایت موفق ۲٬۵۰۰٬۰۰۰ تومانی دریافت شد.',time:'چند لحظه پیش',unread:true},
      {ic:'users', accent:'#dd8d0c',bg:'rgba(244,166,30,.16)',title:'همکار جدید',text:'پروفایل یک همکار جدید در انتظار تأیید است.',time:'۲۰ دقیقه پیش',unread:true},
      {ic:'flag',  accent:'#7c4ddb',bg:'rgba(139,92,246,.16)',title:'کمپین به هدف رسید',text:'کمپین «مهر تحصیلی» به ۱۰۰٪ هدف رسید.',time:'۳ ساعت پیش',unread:false},
      {ic:'news',  accent:'#2f9e9c',bg:'rgba(79,178,176,.18)',title:'خبر منتشر شد',text:'خبر تازه با موفقیت روی سایت منتشر شد.',time:'دیروز',unread:false}
    ];
    if(listEl){
      listEl.innerHTML = NOTIFS.length ? NOTIFS.map(n=>(
        '<div class="notif-item'+(n.unread?' unread':'')+'">'+
          '<div class="notif-ic" style="background:'+n.bg+';color:'+n.accent+'">'+icon(n.ic)+'</div>'+
          '<div class="notif-body">'+
            '<div class="notif-title">'+n.title+'</div>'+
            '<div class="notif-text">'+n.text+'</div>'+
            '<div class="notif-time">'+n.time+'</div>'+
          '</div>'+
        '</div>')).join('') :
        '<div style="display:grid;place-items:center;flex:1;min-height:200px;color:var(--color-muted);font-size:13px;font-weight:600">اعلان جدیدی وجود ندارد</div>';
    }
    function open(){ panel.classList.add('show'); ov.classList.add('show'); panel.setAttribute('aria-hidden','false'); }
    function close(){ panel.classList.remove('show'); ov.classList.remove('show'); panel.setAttribute('aria-hidden','true'); }
    btn.addEventListener('click',open);
    if(closeBtn) closeBtn.addEventListener('click',close);
    if(ov) ov.addEventListener('click',close);
    if(markBtn) markBtn.addEventListener('click',function(){
      listEl.querySelectorAll('.notif-item.unread').forEach(x=>x.classList.remove('unread'));
      const dot=btn.querySelector('.bell-dot'); if(dot) dot.style.display='none';
    });
    document.addEventListener('keydown',function(e){ if(e.key==='Escape') close(); });
  })();

  /* ---------- stat cards ---------- */
  const SS=(SERVER&&SERVER.stats)||{}, TR=SS.trends||{};
  const STATS=[
    {label:'تعداد کل خیرین',icon:'users',target:+SS.totalDonors||0,dec:0,unit:'نفر',trend:TR.donors||'',up:true,accent:'#007b7a',bg:'rgba(0,123,122,.10)',metric:'totalDonors'},
    {label:'خیرین فعال',icon:'activity',target:+SS.activeDonors||0,dec:0,unit:'نفر',trend:TR.active||'',up:true,accent:'#dd8d0c',bg:'rgba(244,166,30,.14)',metric:'activeDonors'},
    {label:'مجموع کمک‌های جمع‌آوری‌شده',icon:'wallet',target:(+SS.totalCollected||0)/1e9,dec:2,unit:'میلیارد تومان',trend:TR.collected||'',up:true,accent:'#7c4ddb',bg:'rgba(139,92,246,.14)',metric:'amount'},
    {label:'کمپین‌های فعال',icon:'target',target:+SS.activeCampaigns||0,dec:0,unit:'کمپین',trend:TR.campaigns||'',up:true,accent:'#2f9e9c',bg:'rgba(79,178,176,.18)'}
  ];
  const statsEl=document.getElementById('statsGrid');
  STATS.forEach((s,i)=>{
    statsEl.insertAdjacentHTML('beforeend',
      '<div class="stat-card reveal" data-metric="'+(s.metric||'')+'" style="--accent:'+s.accent+';--accent-bg:'+s.bg+';animation-delay:'+(0.08+i*0.08)+'s">'+
        '<div class="stat-top"><div class="stat-icon">'+icon(s.icon)+'</div>'+
        (s.trend?'<span class="stat-trend '+(s.up?'up':'down')+'">'+icon(s.up?'up':'down')+s.trend+'</span>':'')+'</div>'+
        '<div class="stat-value"><span data-count>۰</span><span class="stat-unit">'+s.unit+'</span></div>'+
        '<div class="stat-label">'+s.label+'</div>'+
        (s.metric?'<div class="stat-foot"><button class="stat-chart-btn" type="button" data-metric="'+s.metric+'">'+icon('activity')+'نمایش روی نمودار</button></div>':'')+
      '</div>');
  });
  function fmtStat(v,d){ return d>0 ? toFa(v.toFixed(d)) : faNum(v); }
  function countUp(el,target,dec){
    const dur=1300,start=performance.now();
    function tick(now){
      let p=Math.min((now-start)/dur,1); p=1-Math.pow(1-p,3);
      el.textContent=fmtStat(target*p,dec);
      if(p<1) requestAnimationFrame(tick); else el.textContent=fmtStat(target,dec);
    }
    requestAnimationFrame(tick);
  }
  setTimeout(()=>{ statsEl.querySelectorAll('[data-count]').forEach((el,i)=>countUp(el,STATS[i].target,STATS[i].dec)); },250);

  /* ---------- donors ---------- */
  const AVC=['#007b7a','#f4a61e','#8b5cf6','#4fb2b0','#e0556b','#2f9e9c','#dd8d0c','#5b8def'];
  const DONORS=((SERVER&&SERVER.donors)||[]);
  const RANKBG=['linear-gradient(135deg,#f6c453,#e0a528)','linear-gradient(135deg,#cdd5dd,#a9b3bc)','linear-gradient(135deg,#e7b182,#cf8a4f)'];
  const donorsEl=document.getElementById('donorsList');
  const maxAmt=DONORS.length?Math.max.apply(null,DONORS.map(d=>d.amt))||1:1;
  if(!DONORS.length){ donorsEl.innerHTML='<div style="display:grid;place-items:center;flex:1;min-height:160px;color:#9d9d9d;font-size:13px;font-weight:600">هنوز حمایتی ثبت نشده است</div>'; }
  DONORS.forEach((d,i)=>{
    const rb = i<3 ? ' style="background:'+RANKBG[i]+';color:#fff"' : '';
    const c=AVC[i%AVC.length];
    donorsEl.insertAdjacentHTML('beforeend',
      '<div class="donor">'+
        '<div class="rank-badge"'+rb+'>'+toFa(i+1)+'</div>'+
        '<div class="donor-av" style="background:linear-gradient(135deg,'+c+','+shade(c,-22)+')">'+initials(d.name)+'</div>'+
        '<div class="donor-info"><div class="donor-name">'+d.name+'</div>'+
          '<div class="donor-bar"><i data-w="'+Math.round(d.amt/maxAmt*100)+'"></i></div></div>'+
        '<div class="donor-amt-wrap"><div class="donor-amt">'+faNum(d.amt)+' <small>تومان</small></div>'+
          '<div class="donor-sub">'+toFa(d.count)+' کمک</div></div>'+
      '</div>');
  });
  setTimeout(()=>{ donorsEl.querySelectorAll('.donor-bar i').forEach(i=>i.style.width=i.dataset.w+'%'); },500);

  /* ---------- monthly goal ring ---------- */
  (function(){
    const ring=document.querySelector('.ring'); if(!ring) return;
    const pct=+ring.dataset.pct, circ=2*Math.PI*50;
    const fg=ring.querySelector('.ring-fg'), lbl=ring.querySelector('[data-pctlabel]');
    setTimeout(()=>{ fg.style.strokeDashoffset=circ*(1-pct/100); },420);
    const s=performance.now()+420, dur=1300;
    function t(now){ let p=Math.min(Math.max((now-s)/dur,0),1); p=1-Math.pow(1-p,3);
      lbl.textContent=toFa(Math.round(pct*p))+'٪'; if(p<1) requestAnimationFrame(t); else lbl.textContent=toFa(pct)+'٪'; }
    requestAnimationFrame(t);
  })();

  /* ---------- chart ---------- */
  const EMPTY={labels:[],campaigns:[],periods:[],free:[],totalDonors:[],activeDonors:[]};
  const DATA=(SERVER&&SERVER.chart)||{day:EMPTY,week:EMPTY,month:EMPTY,year:EMPTY};
  ['day','week','month','year'].forEach(k=>{
    if(!DATA[k]) DATA[k]=EMPTY;
    const n=(DATA[k].labels||[]).length;
    ['campaigns','periods','free','totalDonors','activeDonors'].forEach(key=>{
      if(!Array.isArray(DATA[k][key])) DATA[k][key]=Array(n).fill(0);
    });
  });

  // پیکربندی متریک‌های قابل‌نمایش روی نمودار
  const METRICS={
    amount:{
      title:'روند کمک‌های جمع‌آوری‌شده', sub:'مجموع مبالغ اهدایی در بازه‌ی انتخابی',
      money:true,
      series:[
        {key:'campaigns',label:'کمک به کمپین‌ها',color:'#007b7a'},
        {key:'periods',   label:'دوره‌ها',        color:'#f4a61e'},
        {key:'free',      label:'پرداخت آزاد',     color:'#8b5cf6'}
      ]
    },
    totalDonors:{
      title:'روند تعداد کل خیرین', sub:'تعداد تجمعی خیرین ثبت‌شده تا هر بازه',
      money:false, unit:'نفر',
      series:[{key:'totalDonors', label:'تعداد کل خیرین', color:'#007b7a'}]
    },
    activeDonors:{
      title:'روند خیرین فعال', sub:'تعداد خیرین فعال در هر بازه',
      money:false, unit:'نفر',
      series:[{key:'activeDonors', label:'خیرین فعال', color:'#dd8d0c'}]
    }
  };
  // META برای سازگاری چیپ‌ها (حالت مبالغ)
  const META=METRICS.amount.series;
  let period='month', chart=null, chartMetric='amount';

  function buildChart(){
    if(!window.Chart){ document.querySelector('.chart-wrap').innerHTML='<div style="display:grid;place-items:center;height:100%;color:#9d9d9d;font-size:13px">نمودار در دسترس نیست</div>'; return; }
    Chart.defaults.font.family='Vazirmatn, sans-serif';
    Chart.defaults.font.size=12;
    Chart.defaults.color='#9d9d9d';
    if(chart){ chart.destroy(); chart=null; }
    const cfg=METRICS[chartMetric]||METRICS.amount;
    const isMoney=!!cfg.money, unit=cfg.unit||'';
    const ctx=document.getElementById('chart').getContext('2d');
    const datasets=cfg.series.map(m=>({
      label:m.label,
      data:DATA[period][m.key],
      borderColor:m.color,
      borderWidth:3,
      tension:.4,
      cubicInterpolationMode:'monotone',
      fill:true,
      backgroundColor:function(c){ const a=c.chart.chartArea; if(!a) return hexA(m.color,.06);
        const g=c.chart.ctx.createLinearGradient(0,a.top,0,a.bottom); g.addColorStop(0,hexA(m.color,.30)); g.addColorStop(.9,hexA(m.color,0)); return g; },
      pointRadius:0, pointHoverRadius:6,
      pointBackgroundColor:'#fff', pointBorderColor:m.color, pointBorderWidth:3,
      pointHoverBackgroundColor:'#fff', pointHoverBorderWidth:3,
      hidden:false
    }));
    chart=new Chart(ctx,{
      type:'line',
      data:{labels:DATA[period].labels,datasets:datasets},
      options:{
        responsive:true, maintainAspectRatio:false,
        layout:{padding:{top:8,bottom:0,left:2,right:2}},
        interaction:{mode:'index',intersect:false},
        plugins:{
          legend:{display:false},
          tooltip:{
            rtl:true, textDirection:'rtl',
            backgroundColor:'#ffffff', titleColor:'#2f3437', bodyColor:'#5b6469',
            borderColor:'#e6e8ea', borderWidth:1, padding:12, cornerRadius:12, boxPadding:6,
            usePointStyle:true, boxWidth:8, boxHeight:8, caretSize:6,
            titleFont:{family:'Vazirmatn',weight:'700',size:13},
            bodyFont:{family:'Vazirmatn',size:12.5},
            callbacks:{
              title:items=>items.length?items[0].label:'',
              label:it=> isMoney
                ? ' '+it.dataset.label+': '+fullToman(it.parsed.y)
                : ' '+it.dataset.label+': '+faNum(Math.round(it.parsed.y))+' '+unit
            }
          }
        },
        scales:{
          x:{reverse:false, grid:{display:false}, border:{display:false}, ticks:{padding:8,font:{family:'Vazirmatn',size:12}}},
          y:{beginAtZero:true, border:{display:false}, grid:{color:'#eef1f3',drawTicks:false},
             ticks:{padding:10,maxTicksLimit:5,precision:isMoney?undefined:0,font:{family:'Vazirmatn',size:11.5},
               callback:v=> isMoney ? compact(v) : faNum(Math.round(v))}}
        },
        animation:{duration:850,easing:'easeInOutQuart'}
      }
    });
  }

  function updateTotals(){
    if(chartMetric!=='amount') return;
    document.querySelectorAll('.chip').forEach(chip=>{
      const i=+chip.dataset.ds;
      const arr=(DATA[period][META[i].key])||[];
      const sum=arr.reduce((a,b)=>a+b,0);
      chip.querySelector('.chip-total').textContent=compact(sum);
    });
  }
  function applyHeader(){
    const cfg=METRICS[chartMetric]||METRICS.amount;
    const t=document.getElementById('chartTitle'), s=document.getElementById('chartSub');
    if(t) t.textContent=cfg.title; if(s) s.textContent=cfg.sub;
  }
  function applyChips(){
    const chipsEl=document.getElementById('chips');
    if(chipsEl) chipsEl.style.display = (chartMetric==='amount') ? '' : 'none';
  }
  function markActiveCard(){
    document.querySelectorAll('.stat-card').forEach(c=>{
      c.classList.toggle('charting', !!c.dataset.metric && c.dataset.metric===chartMetric);
    });
  }
  function setPeriod(p){
    period=p;
    buildChart();
    updateTotals();
  }
  function setChartMetric(m, animate){
    if(!METRICS[m]) m='amount';
    chartMetric=m;
    if(m==='amount'){ document.querySelectorAll('.chip').forEach(c=>c.classList.add('on')); }
    applyHeader(); applyChips();
    buildChart();
    updateTotals();
    markActiveCard();
    Cable.attach(m, animate!==false);
  }

  /* ---------- کابل اتصال نمودار (انیمیشن سوییچ) ---------- */
  const Cable=(function(){
    const svg=document.getElementById('cableSvg');
    const content=document.querySelector('.content');
    if(!svg||!content) return {attach:function(){},refresh:function(){},show:function(){}};
    const NS='http://www.w3.org/2000/svg';
    function mk(tag,attrs){ const e=document.createElementNS(NS,tag); for(const k in attrs) e.setAttribute(k,attrs[k]); return e; }

    // کابل
    const path = mk('path',{fill:'none',stroke:'#c8cfd4','stroke-width':'3.5','stroke-linecap':'round'});
    // دوشاخه‌ی متحرک (سرِ بالایی زیر کارت فرو می‌رود و پنهان می‌شود)
    const plug = mk('g',{});
    const head   = mk('rect',{x:'-7',y:'-10',width:'14',height:'19',rx:'4',fill:'#7c4ddb'});   // بخشی که زیر کارت می‌رود
    const headHi = mk('rect',{x:'-7',y:'-10',width:'14',height:'7',rx:'4',fill:'rgba(255,255,255,.30)'});
    const grip   = mk('rect',{x:'-10.5',y:'9',width:'21',height:'12',rx:'5',fill:'#7c4ddb'});    // دسته در فاصله‌ی بین کارت و نمودار
    const gripHi = mk('rect',{x:'-10.5',y:'9',width:'21',height:'5',rx:'5',fill:'rgba(255,255,255,.22)'});
    plug.appendChild(head); plug.appendChild(headHi); plug.appendChild(grip); plug.appendChild(gripHi);
    svg.appendChild(path); svg.appendChild(plug);

    function source(){
      const cr=content.getBoundingClientRect();
      const cc=document.querySelector('.chart-card');
      if(!cc) return {x:cr.width/2,y:0};
      const r=cc.getBoundingClientRect();
      return { x:(r.left-cr.left)+r.width/2, y:(r.top-cr.top)+8 };   // پشت نمودار پنهان می‌شود
    }
    function contact(el){
      const cr=content.getBoundingClientRect(), r=el.getBoundingClientRect();
      return { x:(r.left-cr.left)+r.width/2, y:(r.bottom-cr.top) };
    }
    function accentOf(el){ return (getComputedStyle(el).getPropertyValue('--accent')||'#7c4ddb').trim(); }
    function plugColor(c){ head.setAttribute('fill',c); grip.setAttribute('fill',c); }
    function drawPlug(C){
      const S=source(), Pb={x:C.x,y:C.y+21};
      const vgap=Math.max(36,Math.min(160,Math.abs(S.y-Pb.y)*0.45));
      path.setAttribute('d','M '+Pb.x+' '+Pb.y+' C '+Pb.x+' '+(Pb.y+vgap)+' '+S.x+' '+(S.y-vgap)+' '+S.x+' '+S.y);
      plug.setAttribute('transform','translate('+C.x+','+C.y+')');
    }
    // پالس انرژی: یک نقطه از دوشاخه به سمت نمودار جریان می‌یابد
    function pulse(accent){
      let len=0; try{ len=path.getTotalLength(); }catch(e){}
      if(!len) return;
      const dot=mk('circle',{r:'3.8',fill:accent}); svg.appendChild(dot);
      const t0=performance.now(), d=520;
      (function go(now){
        let p=Math.min((now-t0)/d,1);
        const pt=path.getPointAtLength(len*p);
        dot.setAttribute('cx',pt.x); dot.setAttribute('cy',pt.y);
        dot.setAttribute('opacity',(Math.sin(p*Math.PI)).toFixed(3));
        if(p<1) requestAnimationFrame(go); else if(dot.parentNode) dot.parentNode.removeChild(dot);
      })(t0);
    }

    let cur=null, raf=0, activeEl=null;
    function attach(metric, animate){
      const el=document.querySelector('.stat-card[data-metric="'+metric+'"]');
      if(!el) return;
      activeEl=el;
      const accent=accentOf(el), target=contact(el);
      plugColor(accent);
      if(!animate || !cur){ cur=target; drawPlug(cur); return; }
      const from={x:cur.x,y:cur.y};
      cancelAnimationFrame(raf);
      const dur=640, t0=performance.now();
      (function tick(now){
        let p=Math.min((now-t0)/dur,1);
        const e=p<.5?4*p*p*p:1-Math.pow(-2*p+2,3)/2;        // easeInOutCubic
        let y=from.y+(target.y-from.y)*e + Math.sin(p*Math.PI)*28;  // بیرون‌کشیدن و سفر با قوس
        if(p>0.78){ const q=(p-0.78)/0.22; y-=Math.sin(q*Math.PI)*8; }  // پرشِ جا افتادن زیر کارت
        cur={ x:from.x+(target.x-from.x)*e, y:y };
        drawPlug(cur);
        if(p<1){ raf=requestAnimationFrame(tick); }
        else { cur=target; drawPlug(cur); pulse(accent); }
      })(t0);
    }
    function refresh(){
      if(!activeEl) return;
      // اگر داشبورد مخفی است (صفحه‌ی دیگری در آی‌فریم باز است) اندازه‌گیری نکن؛
      // وگرنه getBoundingClientRect صفر می‌شود و سیم به گوشه می‌پرد.
      if(document.body.classList.contains('spa-active')) return;
      const cc=document.querySelector('.chart-card');
      if(!cc || cc.getClientRects().length===0) return;   // هنوز رندر/مرئی نشده
      cancelAnimationFrame(raf); cur=contact(activeEl); drawPlug(cur);
    }
    function show(){ svg.classList.add('show'); }
    return { attach, refresh, show };
  })();

  // دکمه‌های «نمایش روی نمودار» در باکس‌های آماری
  document.querySelectorAll('.stat-chart-btn').forEach(b=>{
    b.addEventListener('click',()=>{
      if(b.dataset.metric===chartMetric) return;   // همین متریک فعال است → هیچ کاری نکن
      setChartMetric(b.dataset.metric, true);
      const card=document.querySelector('.chart-card');
      if(card) card.scrollIntoView({behavior:'smooth',block:'nearest'});
    });
  });

  // chips (فقط در حالت مبالغ)
  document.querySelectorAll('.chip').forEach(chip=>{
    chip.addEventListener('click',()=>{
      if(chartMetric!=='amount' || !chart) return;
      const i=+chip.dataset.ds;
      const ds=chart.data.datasets[i];
      if(!ds) return;
      ds.hidden=!ds.hidden;
      chip.classList.toggle('on',!ds.hidden);
      chart.update();
    });
  });
  // select
  const sel=document.getElementById('periodSelect');
  document.getElementById('selectBtn').addEventListener('click',e=>{ e.stopPropagation(); sel.classList.toggle('open'); document.getElementById('selectBtn').setAttribute('aria-expanded',sel.classList.contains('open')); });
  sel.querySelectorAll('li').forEach(li=>{
    li.addEventListener('click',()=>{
      sel.querySelectorAll('li').forEach(x=>x.classList.remove('sel'));
      li.classList.add('sel');
      document.getElementById('selectLabel').textContent=li.textContent;
      setPeriod(li.dataset.val);
      sel.classList.remove('open');
    });
  });
  document.addEventListener('click',e=>{ if(!sel.contains(e.target)) sel.classList.remove('open'); });

  setChartMetric('amount', false);
  // به‌خاطر انیمیشن ظاهرشدن کارت‌ها، موقعیت دقیق کابل کمی بعد محاسبه و نمایش داده می‌شود
  setTimeout(function(){ Cable.refresh(); Cable.show(); }, 1100);
  setTimeout(function(){ Cable.refresh(); }, 1700);
  window.addEventListener('load', function(){ Cable.refresh(); });
  window.addEventListener('resize', function(){ Cable.refresh(); });
  // هنگام باز/بسته شدن پنل، margin-rightِ .main با انیمیشن تغییر می‌کند و کارت‌ها جابه‌جا می‌شوند؛
  // به‌جای یک‌بار به‌روزرسانی در پایان (که باعث پریدنِ سیم می‌شد)، در طول کل انیمیشن هر فریم سیم را دنبال می‌کنیم.
  const mainEl=document.querySelector('.main');
  if(mainEl){
    let cableRAF=0;
    function followCable(){ Cable.refresh(); cableRAF=requestAnimationFrame(followCable); }
    function stopFollow(){ cancelAnimationFrame(cableRAF); cableRAF=0; Cable.refresh(); }
    mainEl.addEventListener('transitionrun',   function(e){ if(e.propertyName==='margin-right'){ cancelAnimationFrame(cableRAF); followCable(); } });
    mainEl.addEventListener('transitionend',   function(e){ if(e.propertyName==='margin-right') stopFollow(); });
    mainEl.addEventListener('transitioncancel',function(e){ if(e.propertyName==='margin-right') stopFollow(); });
    // پشتیبان: اگر مرورگری transitionrun را پشتیبانی نکند، با کلیکِ دکمه/پوشش هم حلقه را روشن می‌کنیم
    function kickFollow(){ cancelAnimationFrame(cableRAF); followCable(); setTimeout(stopFollow, 480); }
    const tgl=document.getElementById('toggleBtn'); if(tgl) tgl.addEventListener('click',kickFollow);
    const ovl=document.getElementById('overlay');   if(ovl) ovl.addEventListener('click',kickFollow);
  }

  /* ---------- recent transactions table ---------- */
  const TX=((SERVER&&SERVER.tx)||[]).map((t,i)=>({
    name:t.name||'خیر ناشناس',
    amt:+t.amt||0,
    typeKey:t.typeKey||'free',
    typeLabel:t.typeLabel||'پرداخت آزاد',
    stKey:t.stKey||'wait',
    stLabel:t.stLabel||'',
    date:t.date||'—',
    ref:t.ref||'',
    color:AVC[i%AVC.length]
  }));

  const PER=8;
  const PAGES=Math.max(1,Math.ceil(TX.length/PER));
  let page=1;
  const bodyEl=document.getElementById('tableBody');
  function renderTable(){
    const start=(page-1)*PER;
    const rows=TX.slice(start,start+PER);
    if(!rows.length){
      bodyEl.innerHTML='<tr><td colspan="6" style="text-align:center;color:#9d9d9d;font-weight:600;padding:34px 14px">هنوز تراکنشی ثبت نشده است</td></tr>';
      renderPager(); return;
    }
    bodyEl.innerHTML=rows.map((t,idx)=>(
      '<tr class="enter" style="animation-delay:'+(idx*0.04)+'s">'+
        '<td><div class="cell-user"><div class="cell-av" style="background:linear-gradient(135deg,'+t.color+','+shade(t.color,-22)+')">'+initials(t.name)+'</div>'+
          '<div><div class="cell-name">'+t.name+'</div><div class="cell-ref">'+(t.ref?'#'+t.ref:'')+'</div></div></div></td>'+
        '<td><span class="amt">'+faNum(t.amt)+' <small>تومان</small></span></td>'+
        '<td><span class="badge type-'+t.typeKey+'"><span class="bd"></span>'+t.typeLabel+'</span></td>'+
        '<td><span class="date-cell">'+t.date+'</span></td>'+
        '<td><span class="badge st-'+t.stKey+'"><span class="bd"></span>'+t.stLabel+'</span></td>'+
        '<td><button class="tbtn" type="button" aria-label="عملیات">'+icon('more')+'</button></td>'+
      '</tr>')).join('');
    renderPager();
  }
  function renderPager(){
    const pc=document.getElementById('pagerControls');
    let h='<button class="page-btn page-nav" '+(page===1?'disabled':'')+' data-go="prev">'+icon('chevright')+'قبلی</button>';
    for(let p=1;p<=PAGES;p++) h+='<button class="page-btn '+(p===page?'active':'')+'" data-go="'+p+'">'+toFa(p)+'</button>';
    h+='<button class="page-btn page-nav" '+(page===PAGES?'disabled':'')+' data-go="next">بعدی'+icon('chevleft')+'</button>';
    pc.innerHTML=h;
    pc.querySelectorAll('[data-go]').forEach(b=>b.addEventListener('click',()=>{
      const g=b.dataset.go;
      if(g==='prev'&&page>1)page--; else if(g==='next'&&page<PAGES)page++; else if(/^\d+$/.test(g))page=+g;
      renderTable();
    }));
    const shownFrom=TX.length?start1():0;
    const shownTo=Math.min(page*PER,TX.length);
    document.getElementById('pagerInfo').textContent='نمایش '+faNum(shownFrom)+' تا '+faNum(shownTo)+' از '+faNum(TX.length)+' تراکنش';
  }
  function start1(){ return (page-1)*PER+1; }
  renderTable();

})();
</script>
</body>
</html>

<?php
/* ============================================================================
 * پنل مدیریت خیریه مکسا — نسخه تمام‌صفحه با تفکیک جامع منابع مالی
 * این فایل به صورت یکپارچه بدون سایدبار و متمرکز بر گزارش مبالغ موفق پیاده‌سازی شده است.
 * ========================================================================== */
declare(strict_types=1);
require_once __DIR__ . '/_guard.php';
dash_require('financial');
mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Tehran');

/* ---------- تنظیمات اتصال به دیتابیس (از فایل کانفیگ خارج از گیت) ---------- */
$DB = require __DIR__ . '/../core/db-config.php';

/* ---------- اتصال ---------- */
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
function q(PDO $pdo, string $sql, array $p = []): PDOStatement {
  $st = $pdo->prepare($sql); $st->execute($p); return $st;
}
function fa_digits($s): string {
  return strtr((string)$s, [
    '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹','.'=>'٫'
  ]);
}
function gregorian_to_jalali(int $gy, int $gm, int $gd): array {
  $g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
  $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
  $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400) + $gd + $g_d_m[$gm - 1];
  $jy = -1595 + (33 * intdiv($days, 12053)); $days %= 12053;
  $jy += 4 * intdiv($days, 1461); $days %= 1461;
  if ($days > 365) { $jy += intdiv($days - 1, 365); $days = ($days - 1) % 365; }
  if ($days < 186) { $jm = 1 + intdiv($days, 31); $jd = 1 + ($days % 31); }
  else { $jm = 7 + intdiv($days - 186, 30); $jd = 1 + (($days - 186) % 30); }
  return [$jy, $jm, $jd];
}
const JMONTHS = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
function jmonth_name(int $jm): string { return JMONTHS[max(1, min(12, $jm)) - 1]; }
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
function format_gregorian_to_jalali_string(string $gDate): string {
  $parts = explode('-', explode(' ', $gDate)[0]);
  if (count($parts) < 3) return '';
  list($jy, $jm, $jd) = gregorian_to_jalali((int)$parts[0], (int)$parts[1], (int)$parts[2]);
  return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
}

/* ---------- دریافت و اعتبارسنجی فیلترهای تاریخ ---------- */
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
  $startDate = null;
}
if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
  $endDate = null;
}

$dateCond = "";
$params = [];
if ($startDate) {
  $dateCond .= " AND COALESCE(pd.paid_at, pd.created_at) >= :start_date";
  $params['start_date'] = $startDate . ' 00:00:00';
}
if ($endDate) {
  $dateCond .= " AND COALESCE(pd.paid_at, pd.created_at) <= :end_date";
  $params['end_date'] = $endDate . ' 23:59:59';
}

/* ---------- محاسبات تفکیکی منابع مالی (فقط موفق) ---------- */
$onlineHelpTotal = 0.0;
$campaignHelpTotal = 0.0;
$coursesTotal = 0.0; // ساختار موقت طبق درخواست شما
$ordersTotal = 0.0;  // ساختار موقت طبق درخواست شما

if ($pdo) {
  try {
    // کمک آنلاین مستقیم (بدون کمپین)
    $onlineHelpTotal = (float)q($pdo, "SELECT COALESCE(SUM(pd.amount), 0) s FROM panel_donations pd WHERE pd.status='success' AND pd.campaign_id IS NULL" . $dateCond, $params)->fetch()['s'];
    // کمک‌های مربوط به کمپین‌ها
    $campaignHelpTotal = (float)q($pdo, "SELECT COALESCE(SUM(pd.amount), 0) s FROM panel_donations pd WHERE pd.status='success' AND pd.campaign_id IS NOT NULL" . $dateCond, $params)->fetch()['s'];
  } catch (Throwable $e) { $dbError = $dbError ?? $e->getMessage(); }
}
$grandTotalCollected = $onlineHelpTotal + $campaignHelpTotal + $coursesTotal + $ordersTotal;

/* ---------- ۱۰ کاربر برتر (بیشترین مبالغ موفق) ---------- */
$donorsOut = [];
if ($pdo) {
  try {
    $donorsRaw = q($pdo,
      "SELECT pd.user_id,
              NULLIF(TRIM(CONCAT(COALESCE(up.first_name,''),' ',COALESCE(up.last_name,''))),'') AS name,
              pu.email AS email,
              COUNT(*) AS cnt,
              COALESCE(SUM(pd.amount),0) AS total
         FROM panel_donations pd
         JOIN panel_users pu        ON pu.id = pd.user_id
         LEFT JOIN user_profiles up ON up.user_id = pd.user_id
        WHERE pd.status='success'" . $dateCond . "
        GROUP BY pd.user_id, pu.email
        ORDER BY total DESC
        LIMIT 10", $params)->fetchAll();
    foreach ($donorsRaw as $d) {
      $nm = $d['name'] ?: ($d['email'] ? explode('@', $d['email'])[0] : 'خیر ناشناس');
      $donorsOut[] = ['name'=>$nm, 'count'=>(int)$d['cnt'], 'amt'=>(float)$d['total']];
    }
  } catch (Throwable $e) { $dbError = $dbError ?? $e->getMessage(); }
}

/* ---------- تفکیک مبالغ هر کمپین ---------- */
$campaignsBreakdown = [];
if ($pdo) {
  try {
    $campaignsBreakdown = q($pdo,
      "SELECT c.title, COALESCE(SUM(pd.amount), 0) AS total_collected
         FROM campaigns c
         LEFT JOIN panel_donations pd ON c.id = pd.campaign_id AND pd.status='success'" . $dateCond . "
        GROUP BY c.id, c.title
        ORDER BY total_collected DESC", $params)->fetchAll();
  } catch (Throwable $e) { $dbError = $dbError ?? $e->getMessage(); }
}

/* ---------- ساختار موقت برای دوره‌ها و محصولات (مقدار صفر) ---------- */
$coursesBreakdown = [
  ['title' => 'دوره آموزشی اتوماسیون هوشمند آبزی‌پروری', 'total_collected' => 0.0],
  ['title' => 'دوره پردازش تصویر پیشرفته (سورتر الگو)', 'total_collected' => 0.0],
];
$productsBreakdown = [
  ['title' => 'دستگاه خودکار شمارش و تفکیک تخم ماهی', 'total_collected' => 0.0],
  ['title' => 'قطعات اتوماسیون کارگاهی مکسا', 'total_collected' => 0.0],
];

/* ---------- تراکنش‌های اخیر (فقط پرداخت‌های موفق) ---------- */
$tx = [];
if ($pdo) {
  try {
    $txRaw = q($pdo,
      "SELECT pd.reference, pd.amount, COALESCE(pd.paid_at, pd.created_at) AS ts,
              NULLIF(TRIM(CONCAT(COALESCE(up.first_name,''),' ',COALESCE(up.last_name,''))),'') AS name,
              pu.email AS email, c.title AS campaign_title
         FROM panel_donations pd
         LEFT JOIN panel_users pu   ON pu.id = pd.user_id
         LEFT JOIN user_profiles up ON up.user_id = pd.user_id
         LEFT JOIN campaigns c      ON c.id = pd.campaign_id
        WHERE pd.status='success'" . $dateCond . "
        ORDER BY COALESCE(pd.paid_at, pd.created_at) DESC
        LIMIT 20", $params)->fetchAll();
    foreach ($txRaw as $r) {
      $nm = $r['name'] ?: ($r['email'] ? explode('@', $r['email'])[0] : 'خیر ناشناس');
      $tx[] = [
        'name' => $nm,
        'amt' => (float)$r['amount'],
        'typeLabel' => !empty($r['campaign_title']) ? $r['campaign_title'] : 'کمک مستقیم آنلاین',
        'date' => jalali_datetime(strtotime($r['ts'])),
        'ref' => $r['reference']
      ];
    }
  } catch (Throwable $e) { $dbError = $dbError ?? $e->getMessage(); }
}

/* ---------- آماده‌سازی داده‌های نمودار توسعه ماهیانه ---------- */
$don = []; $users = [];
if ($pdo) {
  try {
    $don = q($pdo, "SELECT pd.amount, pd.campaign_id, COALESCE(pd.paid_at, pd.created_at) AS ts FROM panel_donations pd WHERE pd.status='success'" . $dateCond, $params)->fetchAll();
    $users = q($pdo, "SELECT created_at, last_login_at FROM panel_users")->fetchAll();
  } catch (Throwable $e) {}
}

function build_monthly_series(array $don): array {
  $buckets = [];
  for ($i = 11; $i >= 0; $i--) {
    $d = new DateTime('first day of this month 00:00:00'); $d->modify("-$i month");
    $s = $d->getTimestamp(); $d2 = clone $d; $d2->modify('+1 month'); $e = $d2->getTimestamp();
    list(, $jm) = gregorian_to_jalali((int)$d->format('Y'), (int)$d->format('n'), 15);
    $buckets[] = ['s'=>$s, 'e'=>$e, 'label'=>jmonth_name($jm)];
  }
  $labels = []; $free = []; $camp = []; $total = [];
  foreach ($buckets as $b) { $labels[] = $b['label']; $free[] = 0; $camp[] = 0; $total[] = 0; }
  foreach ($don as $row) {
    $ts = strtotime((string)$row['ts']); if ($ts === false) continue;
    foreach ($buckets as $k => $b) {
      if ($ts >= $b['s'] && $ts < $b['e']) {
        $amt = (float)$row['amount'];
        if ($row['campaign_id'] !== null && $row['campaign_id'] !== '') {
          $camp[$k] += $amt;
        } else {
          $free[$k] += $amt;
        }
        $total[$k] += $amt;
        break;
      }
    }
  }
  return ['labels'=>$labels, 'free'=>$free, 'campaigns'=>$camp, 'total'=>$total];
}
$monthChartData = build_monthly_series($don);

$SERVER = [
  'distribution' => [
    'labels' => ['کمک آنلاین مستقیم', 'کمپین‌ها', 'دوره‌ها (موقتاً صفر)', 'سفارش‌ها (موقتاً صفر)'],
    'data' => [$onlineHelpTotal, $campaignHelpTotal, $coursesTotal, $ordersTotal]
  ],
  'monthly' => $monthChartData,
  'dbError' => $dbError
];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>داشبورد جامع مالی | خیریه مکسا</title>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<!-- تقویم شمسی -->
<link href="assets/css/persian-datepicker.min.css" rel="stylesheet" type="text/css">
<!-- تم دارک/لایت از «داشبورد مدیریت» تبعیت می‌کند (کلید مشترک: maxa-theme) -->
<script>
(function(){
  function applyMaxaTheme(){
    var d = false;
    try {
      var localVal = localStorage.getItem('maxa-theme');
      if (localVal !== null) {
        d = (localVal === 'dark');
      } else {
        // Fallback for first load
        if (window.parent && window.parent !== window) {
          try {
            d = window.parent.document.documentElement.getAttribute('data-theme') === 'dark';
          } catch(e) {
            d = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
          }
        } else {
          d = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
      }
    } catch(e) {
      d = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    if(d) document.documentElement.setAttribute('data-theme','dark'); else document.documentElement.removeAttribute('data-theme');
  }
  applyMaxaTheme();
  window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });
})();
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --violet:#8b5cf6; --success:#16a37a; --danger:#e0556b; --warning:#f4a61e;
  --primary-08:rgba(0,123,122,.08); --primary-12:rgba(0,123,122,.12);
  --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04), 0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06), 0 2px 6px rgba(16,40,40,.04);
  --ease:cubic-bezier(.4,0,.2,1);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.65;padding-bottom:60px;}
.topbar{height:70px;display:flex;align-items:center;padding:0 30px;background:#fff;border-bottom:1px solid var(--color-border);margin-bottom:24px;}
.tb-title h1{font-size:18px;font-weight:800;color:var(--color-primary-dark)}
.content{max-width:1600px;width:100%;margin:0 auto;padding:0 24px;}

/* ۵ باکس آماری تفکیکی */
.stats-five-grid {
  display: grid; grid-template-columns: repeat(5, 1fr); gap: 18px; margin-bottom: 24px;
}
.stat-card {
  background: var(--color-surface); padding: 22px 18px; border-radius: var(--radius);
  box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 14px; border: 1px solid var(--color-border);
}
.sc-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: bold;}
.sc-info span { font-size: 12px; color: var(--color-muted); font-weight: 600; display: block; }
.sc-info h3 { font-size: 16px; font-weight: 800; color: var(--color-text); margin-top: 4px; }
.sc-info h3 small { font-size: 11px; color: var(--color-muted); font-weight: 500; }

/* ساختار نمودارها */
.grid-charts { display: grid; grid-template-columns: 3fr 2fr; gap: 20px; margin-bottom: 24px; }
.grid-charts > * { min-width: 0; }
.card { background: var(--color-surface); border-radius: var(--radius); padding: 24px; border: 1px solid var(--color-border); box-shadow: var(--shadow-sm); }
.card-head { margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; }
.card-head h3 { font-size: 15px; font-weight: 800; color: var(--color-text); }
.chart-wrap { position: relative; height: 320px; width: 100%; }
.chart-wrap canvas { max-width: 100% !important; }

/* جداول */
.grid-tables-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
.grid-tables-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.dtable { width: 100%; border-collapse: collapse; text-align: right; }
.dtable th { padding: 12px; font-weight: 700; font-size: 12.5px; color: var(--color-muted); border-bottom: 2px solid var(--color-border); }
.dtable td { padding: 13px 12px; font-size: 13px; border-bottom: 1px solid var(--color-border); color: var(--color-text); vertical-align: middle; }
.badge { display: inline-block; padding: 4px 10px; border-radius: 8px; font-size: 11.5px; font-weight: 700; }

@media (max-width: 1200px){
  .stats-five-grid { grid-template-columns: repeat(2, 1fr); }
  .grid-charts, .grid-tables-2, .grid-tables-3 { grid-template-columns: 1fr !important; }
}

/* ===== دارک‌مود: تبعیت از «داشبورد مدیریت» (کلید مشترک maxa-theme) ===== */
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --primary-08:rgba(79,178,176,.10); --primary-12:rgba(79,178,176,.16);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  color-scheme:dark; background-color:var(--color-bg);
}
[data-theme="dark"] .topbar{background:var(--color-surface)}
[data-theme="dark"] *::-webkit-scrollbar-thumb{background:rgba(255,255,255,.16);background-clip:padding-box}

/* ============ بهبودِ UI (افزوده‌شده؛ قواعدِ بالاتر را override می‌کند) ============ */
:root{
  --color-secondary-dark:#b9760a; --radius-sm:12px; --color-primary-light:#4fb2b0;
  --secondary-12:rgba(244,166,30,.14); --violet-12:rgba(124,77,219,.14); --success-12:rgba(22,163,122,.12);
  --shadow-lg:0 20px 44px -14px rgba(0,102,101,.20), 0 8px 20px -10px rgba(16,40,40,.12);
}
:root[data-theme="dark"]{
  --color-secondary-dark:#e0a528;
  --secondary-12:rgba(244,166,30,.16); --violet-12:rgba(124,77,219,.20); --success-12:rgba(22,163,122,.16);
  --shadow-lg:0 24px 48px -16px rgba(0,0,0,.62),0 10px 24px -12px rgba(0,0,0,.5);
}
body{padding:26px 0 60px;-webkit-font-smoothing:antialiased;transition:background .3s,color .3s}
.content{max-width:1500px;padding:0 24px}

/* هدرِ صفحه */
.fm-head{display:flex;align-items:center;gap:16px;margin-bottom:24px}
.fm-head-ic{width:56px;height:56px;border-radius:17px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 14px 26px -10px rgba(0,123,122,.6)}
.fm-head-ic svg{width:28px;height:28px}
.fm-head h1{font-size:22px;font-weight:800;letter-spacing:-.01em;color:var(--color-text)}
.fm-head p{font-size:13px;color:var(--color-muted);margin-top:3px;font-weight:500}

/* باکس‌های آماری */
.stats-five-grid{gap:16px}
.stat-card{position:relative;overflow:hidden;display:block;padding:20px;
  transition:transform .28s var(--ease),box-shadow .28s var(--ease),border-color .28s}
.stat-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg);border-color:transparent}
.stat-card::after{content:'';position:absolute;inset-inline-start:0;top:0;bottom:0;width:4px;background:var(--accent,var(--color-primary))}
.sc-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px}
.sc-icon{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;background:var(--accent-bg,var(--primary-12));color:var(--accent,var(--color-primary))}
.sc-icon svg{width:23px;height:23px}
.sc-share{font-size:11px;font-weight:800;color:var(--accent,var(--color-primary));background:var(--accent-bg,var(--primary-12));padding:4px 10px;border-radius:99px;white-space:nowrap}
.sc-label{font-size:12.5px;color:var(--color-muted);font-weight:600}
.sc-amount{font-size:18px;font-weight:800;color:var(--color-text);margin-top:5px;font-variant-numeric:tabular-nums;letter-spacing:-.01em}
.sc-amount small{font-size:11px;color:var(--color-muted);font-weight:600;margin-inline-start:2px}

/* کارت‌ها و سرتیترها */
.grid-charts{grid-template-columns:1.6fr 1fr}
.card{padding:22px 24px;transition:box-shadow .28s var(--ease),border-color .28s}
.card:hover{box-shadow:var(--shadow-md)}
.card-head{margin-bottom:18px;display:flex;align-items:center;gap:0}
.card-head h3{position:relative;padding-inline-start:14px;font-size:15px;font-weight:800;letter-spacing:-.01em}
.card-head h3::before{content:'';position:absolute;inset-inline-start:0;top:50%;transform:translateY(-50%);width:4px;height:17px;border-radius:99px;background:linear-gradient(180deg,var(--color-secondary),#e08e12)}

/* جدول‌ها */
.dtable th{font-size:11.5px;border-bottom:1px solid var(--color-border);padding:11px 12px;white-space:nowrap}
.dtable td{padding:13px 12px}
.dtable tbody tr{transition:background .18s}
.dtable tbody tr:hover td{background:var(--color-bg)}
.dtable tbody tr:last-child td{border-bottom:none}
.badge{min-width:26px;height:26px;display:inline-grid;place-items:center;padding:0 8px;border-radius:9px;font-weight:800}

@media (max-width:560px){
  .content{padding:0 14px}
  .stats-five-grid{grid-template-columns:1fr}
  .fm-head h1{font-size:19px}
}

/* ورودِ نرمِ باکس‌های آماری (فقط ظاهری) */
@keyframes fmUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
.stat-card{animation:fmUp .5s var(--ease) both}
.stats-five-grid .stat-card:nth-child(2){animation-delay:.06s}
.stats-five-grid .stat-card:nth-child(3){animation-delay:.12s}
.stats-five-grid .stat-card:nth-child(4){animation-delay:.18s}
.stats-five-grid .stat-card:nth-child(5){animation-delay:.24s}
@media (prefers-reduced-motion:reduce){.stat-card{animation:none}}

/* دکمه‌ی گزارش‌گیری در هدر */
.fm-head{flex-wrap:wrap}
.fm-head-text{flex:1;min-width:200px}
.fm-report-btn{display:inline-flex;align-items:center;gap:8px;font-family:inherit;font-weight:800;font-size:14px;border:none;cursor:pointer;
  padding:12px 22px;border-radius:13px;color:#5c3d00;background:linear-gradient(135deg,#ffc24d,var(--color-secondary));
  box-shadow:0 12px 26px -10px rgba(244,166,30,.7);transition:transform .15s var(--ease),box-shadow .25s}
.fm-report-btn:hover{transform:translateY(-2px);box-shadow:0 18px 32px -10px rgba(244,166,30,.85)}
.fm-report-btn:active{transform:scale(.97)}
.fm-report-btn svg{width:18px;height:18px}

/* ============ ریسپانسیو ============ */
@media (max-width:1200px){
  .stats-five-grid{grid-template-columns:repeat(3,1fr)}
}
@media (max-width:768px){
  .content{padding:0 16px}
  .stats-five-grid{grid-template-columns:repeat(2,1fr)}
  .card{overflow-x:auto;-webkit-overflow-scrolling:touch}      /* جدول‌ها در موبایل اسکرول افقی می‌شوند */
  .dtable{min-width:460px}
  .chart-wrap{height:300px}
  .fm-report-btn{width:100%;justify-content:center}
  .fm-head-text{min-width:0}
}
@media (max-width:560px){
  .content{padding:0 13px}
  .stats-five-grid{grid-template-columns:1fr}
  .chart-wrap{height:260px}
  .card{padding:18px 16px}
  .stat-card{padding:18px 16px}
  .fm-head h1{font-size:19px}
}

/* ============ چاپِ گزارش ============ */
@media print{
  .fm-report-btn{display:none}
  body{padding:0;background:#fff;color:#111}
  .content{max-width:none;padding:0}
  .card,.stat-card{box-shadow:none;border:1px solid #ddd;break-inside:avoid}
  .grid-charts,.grid-tables-2,.grid-tables-3{break-inside:avoid}
}

/* ============ REPORT MODAL CSS ============ */
.report-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(16, 40, 40, 0.45);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s var(--ease), visibility 0.3s var(--ease);
}
.report-modal-overlay.show {
  opacity: 1;
  visibility: visible;
}
.report-modal-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  width: 460px;
  max-width: 92%;
  box-shadow: var(--shadow-lg);
  transform: translateY(20px) scale(0.96);
  transition: transform 0.3s var(--ease);
  overflow: hidden;
  direction: rtl;
}
.report-modal-overlay.show .report-modal-card {
  transform: translateY(0) scale(1);
}
.report-modal-header {
  padding: 18px 24px;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.report-modal-header h3 {
  font-size: 16px;
  font-weight: 800;
  color: var(--color-text);
  position: relative;
  padding-inline-start: 14px;
}
.report-modal-header h3::before {
  content: '';
  position: absolute;
  inset-inline-start: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 18px;
  border-radius: 99px;
  background: linear-gradient(180deg, var(--color-secondary), #e08e12);
}
.report-modal-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: var(--color-muted);
  transition: color 0.2s, background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
}
.report-modal-close:hover {
  color: var(--danger);
  background: rgba(224, 85, 107, 0.1);
}
.report-modal-body {
  padding: 24px;
}
.form-group-modal {
  margin-bottom: 18px;
}
.modal-label {
  display: block;
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 8px;
  color: var(--color-text);
  text-align: right;
}
.format-options {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.format-option {
  cursor: pointer;
}
.format-option input[type="radio"] {
  display: none;
}
.format-box {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 12px;
  border: 1.5px solid var(--color-border);
  border-radius: 12px;
  background: var(--color-surface);
  transition: all 0.2s var(--ease);
  color: var(--color-text);
  font-weight: 700;
}
.format-box svg {
  width: 20px;
  height: 20px;
  color: var(--color-muted);
  transition: transform 0.2s, color 0.2s;
}
.format-option input[type="radio"]:checked + .format-box {
  border-color: var(--color-primary);
  background: var(--primary-08);
  color: var(--color-primary-dark);
}
.format-option input[type="radio"]:checked + .format-box svg {
  transform: scale(1.1);
  color: var(--color-primary);
}
.date-range-modal {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.modal-input, .modal-select {
  width: 100%;
  font-family: inherit;
  font-size: 13px;
  color: var(--color-text);
  background: var(--color-bg);
  border: 1.5px solid var(--color-border);
  border-radius: 12px;
  padding: 10px 14px;
  outline: none;
  transition: all 0.2s;
  text-align: right;
}
.modal-input:focus, .modal-select:focus {
  border-color: var(--color-primary-light);
  box-shadow: 0 0 0 4px var(--primary-08);
  background: var(--color-surface);
}
.modal-select {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239d9d9d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: left 12px center;
  background-size: 16px;
  padding-left: 36px;
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}
.modal-btn-cancel {
  padding: 10px 18px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 13px;
  color: var(--color-muted);
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  cursor: pointer;
  transition: all 0.2s;
}
.modal-btn-cancel:hover {
  background: rgba(224, 85, 107, 0.08);
  color: var(--danger);
  border-color: transparent;
}
.modal-btn-submit {
  padding: 10px 22px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 13.5px;
  color: #5c3d00;
  background: linear-gradient(135deg, #ffc24d, var(--color-secondary));
  box-shadow: 0 8px 20px -8px rgba(244, 166, 30, 0.6);
  border: none;
  cursor: pointer;
  transition: all 0.2s var(--ease);
}
.modal-btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px -8px rgba(244, 166, 30, 0.75);
}
.modal-btn-submit:active {
  transform: translateY(0);
}
@media print {
  .report-modal-overlay {
    display: none !important;
  }
}
</style>
</head>
<body>

<main class="content">

  <header class="fm-head">
    <div class="fm-head-ic">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7V5.5A1.5 1.5 0 0 0 17.5 4H6a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2H6"/><circle cx="16.5" cy="12" r="1.4" fill="currentColor" stroke="none"/></svg>
    </div>
    <div class="fm-head-text">
      <h1>مدیریت مالی و اعانات</h1>
      <?php if ($startDate || $endDate): ?>
        <p style="font-weight: 700; color: var(--color-primary-dark);">
          بازهٔ گزارش: 
          <?= $startDate ? 'از ' . fa_digits(format_gregorian_to_jalali_string($startDate)) : '' ?>
          <?= $endDate ? ' تا ' . fa_digits(format_gregorian_to_jalali_string($endDate)) : '' ?>
        </p>
      <?php else: ?>
        <p>گزارشِ جامعِ منابعِ مالی و تراکنش‌های موفقِ خیریهٔ مکسا.</p>
      <?php endif; ?>
    </div>
    <button type="button" class="fm-report-btn" onclick="openReportModal()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      گزارش‌گیری
    </button>
  </header>

  <section class="stats-five-grid">
    <div class="stat-card" style="--accent:var(--color-primary);--accent-bg:var(--primary-12)">
      <div class="sc-top"><div class="sc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7V5.5A1.5 1.5 0 0 0 17.5 4H6a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h12a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2H6"/><circle cx="16.5" cy="12" r="1.4" fill="currentColor" stroke="none"/></svg></div></div>
      <div class="sc-label">مجموع کل دریافتی‌ها</div>
      <div class="sc-amount"><?= fa_digits(number_format($grandTotalCollected)) ?> <small>تومان</small></div>
    </div>
    <div class="stat-card" style="--accent:var(--success);--accent-bg:var(--success-12)">
      <div class="sc-top"><div class="sc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18"/></svg></div></div>
      <div class="sc-label">کمک آنلاین مستقیم</div>
      <div class="sc-amount"><?= fa_digits(number_format($onlineHelpTotal)) ?> <small>تومان</small></div>
    </div>
    <div class="stat-card" style="--accent:var(--color-secondary);--accent-bg:var(--secondary-12)">
      <div class="sc-top"><div class="sc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none"/></svg></div></div>
      <div class="sc-label">کل کمک‌های کمپین‌ها</div>
      <div class="sc-amount"><?= fa_digits(number_format($campaignHelpTotal)) ?> <small>تومان</small></div>
    </div>
    <div class="stat-card" style="--accent:var(--violet);--accent-bg:var(--violet-12)">
      <div class="sc-top"><div class="sc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div></div>
      <div class="sc-label">درآمد دوره‌ها (به‌زودی)</div>
      <div class="sc-amount"><?= fa_digits(number_format($coursesTotal)) ?> <small>تومان</small></div>
    </div>
    <div class="stat-card" style="--accent:var(--color-primary-light);--accent-bg:rgba(79,178,176,.14)">
      <div class="sc-top"><div class="sc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8.5 12 3 3 8.5 12 14l9-5.5Z"/><path d="M3 8.5v7L12 21l9-5.5v-7"/><line x1="12" y1="14" x2="12" y2="21"/></svg></div></div>
      <div class="sc-label">فروش محصولات/سفارشات</div>
      <div class="sc-amount"><?= fa_digits(number_format($ordersTotal)) ?> <small>تومان</small></div>
    </div>
  </section>

  <section class="grid-charts">
    <div class="card">
      <div class="card-head"><h3>نمودار روند جمع‌آوری ماهیانه (۱۲ ماه اخیر)</h3></div>
      <div class="chart-wrap"><canvas id="monthlyTrendChart"></canvas></div>
    </div>
    <div class="card">
      <div class="card-head"><h3>سهم هر بخش از کل درآمدهای خیریه</h3></div>
      <div class="chart-wrap"><canvas id="distributionChart"></canvas></div>
    </div>
  </section>

  <section class="grid-tables-2">
    <div class="card">
      <div class="card-head"><h3>۱۰ کاربر برتر (بیشترین مبالغ حمایت مالی موفق)</h3></div>
      <table class="dtable">
        <thead>
          <tr>
            <th>رتبه</th>
            <th>نام خیر / شناسگر</th>
            <th>تعداد دفعات مشارکت</th>
            <th>مجموع حمایت (تومان)</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($donorsOut)): ?>
            <tr><td colspan="4" style="text-align:center; color:var(--color-muted);">داده‌ای موجود نیست</td></tr>
          <?php else: foreach($donorsOut as $index => $donor): ?>
            <tr>
              <td><span class="badge" style="background:var(--primary-08); color:var(--color-primary);"><?= fa_digits($index + 1) ?></span></td>
              <td><b><?= htmlspecialchars($donor['name']) ?></b></td>
              <td><?= fa_digits($donor['count']) ?> بار</td>
              <td style="color:var(--success); font-weight:700;"><?= fa_digits(number_format($donor['amt'])) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <div class="card-head"><h3>مجموع مبالغ جمع‌آوری شده به تفکیک ماه</h3></div>
      <table class="dtable">
        <thead>
          <tr>
            <th>ماه</th>
            <th>کمک مستقیم (تومان)</th>
            <th>کمپین‌ها (تومان)</th>
            <th>مجموع درآمد ماه (تومان)</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          for($i = 0; $i < count($monthChartData['labels']); $i++): 
            if($monthChartData['total'][$i] == 0) continue; // ماه‌های خالی را نشان نمی‌دهیم
          ?>
            <tr>
              <td><b><?= $monthChartData['labels'][$i] ?></b></td>
              <td><?= fa_digits(number_format($monthChartData['free'][$i])) ?></td>
              <td><?= fa_digits(number_format($monthChartData['campaigns'][$i])) ?></td>
              <td style="font-weight:700; color:var(--color-primary-dark);"><?= fa_digits(number_format($monthChartData['total'][$i])) ?></td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="grid-tables-3" style="margin-top: 20px;">
    <div class="card">
      <div class="card-head"><h3>درآمد حاصل از هر کمپین</h3></div>
      <table class="dtable">
        <thead>
          <tr>
            <th>عنوان کمپین</th>
            <th>کل دریافتی (تومان)</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($campaignsBreakdown)): ?>
            <tr><td colspan="2" style="text-align:center; color:var(--color-muted);">کمپین فعالی یافت نشد.</td></tr>
          <?php else: foreach($campaignsBreakdown as $c): ?>
            <tr>
              <td><b><?= htmlspecialchars($c['title']) ?></b></td>
              <td style="color:var(--color-secondary); font-weight:700;"><?= fa_digits(number_format((float)$c['total_collected'])) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <div class="card-head"><h3>درآمد حاصل از هر دوره آموزشی</h3></div>
      <table class="dtable">
        <thead>
          <tr>
            <th>عنوان دوره</th>
            <th>کل دریافتی (تومان)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($coursesBreakdown as $course): ?>
            <tr>
              <td><?= htmlspecialchars($course['title']) ?></td>
              <td style="color:var(--violet); font-weight:700;"><?= fa_digits(number_format($course['total_collected'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <div class="card-head"><h3>درآمد حاصل از هر محصول / سفارش</h3></div>
      <table class="dtable">
        <thead>
          <tr>
            <th>نام محصول</th>
            <th>کل دریافتی (تومان)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($productsBreakdown as $prod): ?>
            <tr>
              <td><?= htmlspecialchars($prod['title']) ?></td>
              <td style="color:var(--color-primary-light); font-weight:700;"><?= fa_digits(number_format($prod['total_collected'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

</main>

  <!-- پنل گزارش‌گیری مالی -->
  <div id="reportModal" class="report-modal-overlay" onclick="handleOverlayClick(event)">
    <div class="report-modal-card">
      <div class="report-modal-header">
        <h3>تنظیمات گزارش‌گیری</h3>
        <button type="button" class="report-modal-close" onclick="closeReportModal()">&times;</button>
      </div>
      <div class="report-modal-body">
        <form id="reportForm" onsubmit="handleGenerateReport(event)">
          
          <div class="form-group-modal">
            <label class="modal-label">فرمت خروجی</label>
            <div class="format-options">
              <label class="format-option">
                <input type="radio" name="format" value="pdf" checked>
                <div class="format-box">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                  <span>PDF (پی‌دی‌اف)</span>
                </div>
              </label>
              <label class="format-option">
                <input type="radio" name="format" value="excel">
                <div class="format-box">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="20" x2="8" y2="13"/><line x1="16" y1="20" x2="16" y2="13"/><line x1="8" y1="13" x2="16" y2="13"/></svg>
                  <span>Excel (اکسل)</span>
                </div>
              </label>
            </div>
          </div>

          <div class="date-range-modal form-group-modal">
            <div>
              <label for="startDate" class="modal-label">تاریخ شروع</label>
              <input type="text" id="startDate" name="startDate" placeholder="مثال: ۱۴۰۲/۰۱/۰۱" class="modal-input">
            </div>
            <div>
              <label for="endDate" class="modal-label">تاریخ پایان</label>
              <input type="text" id="endDate" name="endDate" placeholder="مثال: ۱۴۰۲/۱۲/۲۹" class="modal-input">
            </div>
          </div>

          <div class="form-group-modal">
            <label for="reportType" class="modal-label">نوع گزارش</label>
            <select id="reportType" name="reportType" class="modal-select">
              <option value="all">همه تراکنش‌های موفق</option>
              <option value="direct">کمک آنلاین مستقیم</option>
              <option value="campaigns">کمپین‌های خیریه</option>
              <option value="courses_products">دوره‌ها و محصولات</option>
            </select>
          </div>

          <div class="form-group-modal">
            <label for="reportDetail" class="modal-label">سطح جزئیات گزارش</label>
            <select id="reportDetail" name="reportDetail" class="modal-select">
              <option value="summary">خلاصه مدیریتی (نمودارها و آمار کل)</option>
              <option value="detailed">تفصیلی (همراه با لیست حامیان برتر)</option>
            </select>
          </div>

          <div class="modal-actions">
            <button type="button" class="modal-btn-cancel" onclick="closeReportModal()">انصراف</button>
            <button type="submit" class="modal-btn-submit">تولید گزارش</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- کتابخانه‌های تقویم شمسی -->
  <script src="assets/vendor/libs/jquery/jquery.js"></script>
  <script src="assets/js/datepicker/persian-date.min.js"></script>
  <script src="assets/js/datepicker/persian-datepicker.min.js"></script>

<script>
const SERVER = <?= json_encode($SERVER, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

/* ---------- نمودارها (هماهنگ با تم، با رندرِ مجدد هنگام تغییر تم) — داده‌ها دست‌نخورده ---------- */
(function(){
  var chDist=null, chMonth=null;

  function fa(n){ var s=String(Math.round(+n||0)).replace(/\B(?=(\d{3})+(?!\d))/g,'٬'); return s.replace(/[0-9]/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[d];}); }
  function faDec(x){ return (Math.round(x*10)/10).toFixed(1).replace(/[0-9]/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[d];}).replace('.','٫'); }
  function shortMoney(v){ v=+v||0;
    if(v>=1e9) return faDec(v/1e9)+' م.د';
    if(v>=1e6) return fa(Math.round(v/1e6))+' م';
    if(v>=1e3) return fa(Math.round(v/1e3))+' هـ';
    return fa(v);
  }
  function colors(){
    var dark = document.documentElement.getAttribute('data-theme')==='dark';
    return { text: dark?'#9aa4a9':'#7a8488', grid: dark?'rgba(255,255,255,.07)':'rgba(16,40,40,.07)', surface: dark?'#19232a':'#ffffff' };
  }
  function legendOpts(c){ return { position:'bottom', labels:{ font:{family:'Vazirmatn',size:12}, color:c.text, usePointStyle:true, pointStyle:'circle', boxWidth:8, boxHeight:8, padding:14 } }; }
  function tooltipBase(c){ return { rtl:true, textDirection:'rtl', backgroundColor:c.surface, titleColor:c.text, bodyColor:c.text,
    borderColor:'rgba(128,128,128,.22)', borderWidth:1, padding:12, cornerRadius:12, boxPadding:6, usePointStyle:true,
    titleFont:{family:'Vazirmatn',weight:'700'}, bodyFont:{family:'Vazirmatn'} }; }

  function render(){
    if(!window.Chart) return;
    var c = colors();
    Chart.defaults.font.family = 'Vazirmatn, sans-serif';
    Chart.defaults.color = c.text;
    if(chDist) chDist.destroy();
    if(chMonth) chMonth.destroy();

    chDist = new Chart(document.getElementById('distributionChart').getContext('2d'), {
      type:'doughnut',
      data:{ labels:SERVER.distribution.labels, datasets:[{ data:SERVER.distribution.data,
        backgroundColor:['#16a37a','#f4a61e','#7c4ddb','#4fb2b0'], borderWidth:3, borderColor:c.surface, hoverOffset:8 }] },
      options:{ responsive:true, maintainAspectRatio:false, cutout:'62%',
        transitions:{ resize:{ animation:{ duration:400 } } },
        plugins:{ legend:legendOpts(c),
          tooltip:Object.assign(tooltipBase(c),{ callbacks:{ label:function(ctx){ return ' '+ctx.label+': '+fa(ctx.parsed)+' تومان'; } } }) } }
    });

    chMonth = new Chart(document.getElementById('monthlyTrendChart').getContext('2d'), {
      type:'bar',
      data:{ labels:SERVER.monthly.labels, datasets:[
        { label:'کمک آنلاین مستقیم', data:SERVER.monthly.free, backgroundColor:'#16a37a', borderRadius:6, maxBarThickness:30 },
        { label:'کمک‌های کمپین‌ها', data:SERVER.monthly.campaigns, backgroundColor:'#f4a61e', borderRadius:6, maxBarThickness:30 }
      ]},
      options:{ responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false},
        transitions:{ resize:{ animation:{ duration:400 } } },
        scales:{
          x:{ stacked:true, grid:{display:false}, border:{display:false}, ticks:{ font:{family:'Vazirmatn'}, color:c.text } },
          y:{ stacked:true, grid:{color:c.grid, drawTicks:false}, border:{display:false},
              ticks:{ font:{family:'Vazirmatn'}, color:c.text, maxTicksLimit:5, callback:function(v){ return shortMoney(v); } } }
        },
        plugins:{ legend:legendOpts(c),
          tooltip:Object.assign(tooltipBase(c),{ callbacks:{ label:function(ctx){ return ' '+ctx.dataset.label+': '+fa(ctx.parsed.y)+' تومان'; } } }) } }
    });
  }

  render();
  /* رندرِ مجدد هنگام تغییرِ تم از داشبورد */
  window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) setTimeout(render, 60); });
})();

// ============ REPORT MODAL JS ============
window.openReportModal = function() {
  const modal = document.getElementById('reportModal');
  if (modal) modal.classList.add('show');
};
window.closeReportModal = function() {
  const modal = document.getElementById('reportModal');
  if (modal) modal.classList.remove('show');
};
window.handleOverlayClick = function(e) {
  if (e.target.id === 'reportModal') closeReportModal();
};

// Date range variables in Gregorian
window.selectedStartDateGregorian = '<?= $startDate ?? "" ?>';
window.selectedEndDateGregorian = '<?= $endDate ?? "" ?>';

function formatDateToGregorian(unix) {
  if (!unix) return '';
  const jsDate = new Date(unix);
  const y = jsDate.getFullYear();
  const m = String(jsDate.getMonth() + 1).padStart(2, '0');
  const d = String(jsDate.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

// Initialize Jalali Datepickers on load
$(document).ready(function() {
  if (typeof $.fn.pDatepicker !== 'undefined') {
    $("#startDate").pDatepicker({
      timePicker: { enabled: false },
      format: 'YYYY/MM/DD',
      autoClose: true,
      observer: true,
      onSelect: function(unix) {
        window.selectedStartDateGregorian = formatDateToGregorian(unix);
      }
    });

    $("#endDate").pDatepicker({
      timePicker: { enabled: false },
      format: 'YYYY/MM/DD',
      autoClose: true,
      observer: true,
      onSelect: function(unix) {
        window.selectedEndDateGregorian = formatDateToGregorian(unix);
      }
    });
    
    // Set the inputs if there are active dates
    const activeStartJalali = '<?= $startDate ? format_gregorian_to_jalali_string($startDate) : "" ?>';
    const activeEndJalali = '<?= $endDate ? format_gregorian_to_jalali_string($endDate) : "" ?>';
    if (activeStartJalali) $("#startDate").val(activeStartJalali);
    if (activeEndJalali) $("#endDate").val(activeEndJalali);
  }
  
  // Auto-run formats on load
  const urlParams = new URLSearchParams(window.location.search);
  const format = urlParams.get('format');
  if (format === 'pdf') {
    showToast('در حال آماده‌سازی گزارش PDF...', 'success');
    setTimeout(() => {
      window.print();
      // Clear format query parameter
      const url = new URL(window.location.href);
      url.searchParams.delete('format');
      window.history.replaceState({}, '', url);
    }, 1200);
  } else if (format === 'excel') {
    showToast('گزارش اکسل با موفقیت دانلود شد.', 'success');
    setTimeout(() => {
      triggerExcelDownload();
      // Clear format query parameter
      const url = new URL(window.location.href);
      url.searchParams.delete('format');
      window.history.replaceState({}, '', url);
    }, 600);
  }
});

window.handleGenerateReport = function(e) {
  e.preventDefault();
  const form = e.target;
  const format = form.format.value;
  const reportDetail = form.reportDetail.value;
  
  const start = window.selectedStartDateGregorian || '';
  const end = window.selectedEndDateGregorian || '';
  
  closeReportModal();
  
  // Clear dates in window if inputs were cleared manually
  const startInputVal = document.getElementById('startDate').value;
  const endInputVal = document.getElementById('endDate').value;
  const finalStart = startInputVal ? start : '';
  const finalEnd = endInputVal ? end : '';
  
  // Redirect page with date queries and format selection
  window.location.href = `financial-management.php?start_date=${finalStart}&end_date=${finalEnd}&format=${format}&detail=${reportDetail}`;
};

function triggerExcelDownload() {
  const urlParams = new URLSearchParams(window.location.search);
  const start = urlParams.get('start_date') || 'کل دوره';
  const end = urlParams.get('end_date') || 'کل دوره';
  const detail = urlParams.get('detail') || 'summary';
  
  let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
  csvContent += "عنوان گزارش,گزارش مالی خیریه مکسا\n";
  csvContent += "بازه گزارش,از " + start + " تا " + end + "\n";
  csvContent += "سطح جزئیات," + (detail === 'summary' ? 'خلاصه مدیریتی' : 'تفصیلی') + "\n\n";
  
  csvContent += "بخش,مبلغ دریافتی (تومان)\n";
  csvContent += "کمک مستقیم آنلاین," + SERVER.distribution.data[0] + "\n";
  csvContent += "کمپین‌ها," + SERVER.distribution.data[1] + "\n";
  csvContent += "دوره‌ها (موقتاً صفر)," + SERVER.distribution.data[2] + "\n";
  csvContent += "محصولات/سفارشات (موقتاً صفر)," + SERVER.distribution.data[3] + "\n";
  csvContent += "مجموع کل," + (SERVER.distribution.data[0] + SERVER.distribution.data[1] + SERVER.distribution.data[2] + SERVER.distribution.data[3]) + "\n";
  
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `گزارش_مالی_مکسا_${start}_تا_${end}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

window.showToast = function(message, type = 'success') {
  let container = document.getElementById('toast-container-modal');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container-modal';
    container.style.cssText = 'position:fixed;bottom:24px;left:24px;z-index:10000;display:flex;flex-direction:column;gap:8px;';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.style.cssText = `
    padding: 12px 20px;
    border-radius: 12px;
    background: ${type === 'success' ? '#16a37a' : '#e0556b'};
    color: #fff;
    font-family: 'Vazirmatn', sans-serif;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    direction: rtl;
  `;
  
  const icon = type === 'success' ? 
    `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>` : 
    `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;

  toast.innerHTML = icon + `<span>${message}</span>`;
  container.appendChild(toast);
  
  setTimeout(() => {
    toast.style.transform = 'translateY(0)';
    toast.style.opacity = '1';
  }, 10);
  
  setTimeout(() => {
    toast.style.transform = 'translateY(-20px)';
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
};
</script>
</body>
</html>
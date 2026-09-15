<?php
/* ============================================================================
 * موتور خروجی اکسل چند شیته و گزارش مالی خیریه مکسا
 * Multi-Sheet XML Spreadsheet & CSV Export Engine
 * ========================================================================== */
declare(strict_types=1);

require_once __DIR__ . '/_guard.php';
dash_require('financial');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Tehran');

$pdo = dash_pdo();

/* ---------- توابع کمکی تبدیل تاریخ و اعداد ---------- */
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

function format_jalali_datetime(?string $dateStr): string {
    if (!$dateStr) return '—';
    $ts = strtotime($dateStr);
    if (!$ts) return '—';
    list($jy, $jm, $jd) = gregorian_to_jalali((int)date('Y', $ts), (int)date('n', $ts), (int)date('j', $ts));
    return sprintf('%04d/%02d/%02d %02d:%02d', $jy, $jm, $jd, (int)date('H', $ts), (int)date('i', $ts));
}

function format_jalali_date(?string $dateStr): string {
    if (!$dateStr) return '—';
    $ts = strtotime($dateStr);
    if (!$ts) return '—';
    list($jy, $jm, $jd) = gregorian_to_jalali((int)date('Y', $ts), (int)date('n', $ts), (int)date('j', $ts));
    return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
}

function xml_esc($val): string {
    return htmlspecialchars((string)($val ?? ''), ENT_QUOTES | ENT_XML1, 'UTF-8');
}

/* ---------- فیلترها و ایزولاسیون شعبه ---------- */
$isSuper = dash_is_super();
$isHq    = dash_is_hq_view();
$activeBranchId = dash_active_branch_id();

// دریافت فیلتر تاریخ
$startDate = trim((string)($_GET['start_date'] ?? ''));
$endDate   = trim((string)($_GET['end_date'] ?? ''));
if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) { $startDate = ''; }
if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) { $endDate = ''; }

// دریافت فیلتر منبع
$sourceFilter = trim((string)($_GET['source'] ?? 'all')); // all, direct, campaign, stands, courses
$campaignFilter = (int)($_GET['campaign_id'] ?? 0);
$statusFilter = trim((string)($_GET['status'] ?? 'success')); // success, all, pending, failed
if (!in_array($statusFilter, ['success', 'all', 'pending', 'failed'], true)) {
    $statusFilter = 'success';
}

// تعیین شعبه هدف
$targetBranchId = $activeBranchId;
if ($isSuper && $isHq && isset($_GET['branch_id']) && $_GET['branch_id'] !== 'all') {
    $targetBranchId = (int)$_GET['branch_id'];
}
$isAllBranches = ($isSuper && $isHq && (($_GET['branch_id'] ?? '') === 'all'));

$branchRow = $isAllBranches ? null : dash_load_branch($targetBranchId);
$branchName = $isAllBranches ? 'کل شعب کشور' : ($branchRow['name'] ?? 'شعبه فعال');

// ساخت شرط تاریخ
$dateCondDonations = "";
$dateCondOrders = "";
$paramsDonations = [];
$paramsOrders = [];

if ($startDate !== '') {
    $dateCondDonations .= " AND COALESCE(pd.paid_at, pd.created_at) >= :start_date";
    $paramsDonations['start_date'] = $startDate . ' 00:00:00';
    $dateCondOrders .= " AND o.created_at >= :start_date_o";
    $paramsOrders['start_date_o'] = $startDate . ' 00:00:00';
}
if ($endDate !== '') {
    $dateCondDonations .= " AND COALESCE(pd.paid_at, pd.created_at) <= :end_date";
    $paramsDonations['end_date'] = $endDate . ' 23:59:59';
    $dateCondOrders .= " AND o.created_at <= :end_date_o";
    $paramsOrders['end_date_o'] = $endDate . ' 23:59:59';
}

// شرط شعبه
$branchCondDonations = "";
$branchCondOrders = "";
if (!$isAllBranches) {
    $branchCondDonations = " AND pd.branch_id = :branch_id";
    $paramsDonations['branch_id'] = $targetBranchId;
    $branchCondOrders = " AND o.branch_id = :branch_id_o";
    $paramsOrders['branch_id_o'] = $targetBranchId;
}

// شرط وضعیت
$statusCondDonations = "";
if ($statusFilter !== 'all') {
    $statusCondDonations = " AND pd.status = :status";
    $paramsDonations['status'] = $statusFilter;
}

/* ============================================================================
 * ۱. محاسبات خلاصه مدیریتی (Executive Summary)
 * ========================================================================== */
$onlineHelpTotal = 0.0;
$campaignHelpTotal = 0.0;
$standsTotal = 0.0;
$coursesTotal = 0.0;

// الف) کمک‌های آنلاین مستقیم (بدون کمپین)
try {
    $st = $pdo->prepare("SELECT COALESCE(SUM(pd.amount), 0) s FROM panel_donations pd WHERE pd.campaign_id IS NULL AND pd.status='success' {$dateCondDonations} {$branchCondDonations}");
    $st->execute($paramsDonations);
    $onlineHelpTotal = (float)$st->fetch()['s'];
} catch (Throwable $e) {}

// ب) کمک‌های کمپین‌ها
try {
    $st = $pdo->prepare("SELECT COALESCE(SUM(pd.amount), 0) s FROM panel_donations pd WHERE pd.campaign_id IS NOT NULL AND pd.status='success' {$dateCondDonations} {$branchCondDonations}");
    $st->execute($paramsDonations);
    $campaignHelpTotal = (float)$st->fetch()['s'];
} catch (Throwable $e) {}

// ج) استندها و سفارشات
try {
    $st = $pdo->prepare("SELECT COALESCE(SUM(o.total_price), 0) s FROM orders o WHERE 1=1 {$dateCondOrders} {$branchCondOrders}");
    $st->execute($paramsOrders);
    $standsTotal = (float)$st->fetch()['s'];
} catch (Throwable $e) {}

$grandTotal = $onlineHelpTotal + $campaignHelpTotal + $standsTotal + $coursesTotal;

// تفکیک کمپین‌ها
$campaignsData = [];
try {
    $cSql = "SELECT c.id, c.title, c.target_amount, b.name AS branch_name,
                    COALESCE(SUM(pd.amount), 0) AS total_collected,
                    COUNT(pd.id) AS tx_count
               FROM campaigns c
               LEFT JOIN branches b ON b.id = c.branch_id
               LEFT JOIN panel_donations pd ON pd.campaign_id = c.id AND pd.status='success' {$dateCondDonations}
              WHERE 1=1 " . ($isAllBranches ? "" : " AND c.branch_id = :b_id") . "
              GROUP BY c.id, c.title, c.target_amount, b.name
              ORDER BY total_collected DESC";
    $cParams = $paramsDonations;
    if (!$isAllBranches) {
        $cParams['b_id'] = $targetBranchId;
    }
    // حذف پارامتر branch_id اضافی اگر در کوئری کمپین استفاده نمی‌شود
    unset($cParams['branch_id'], $cParams['status']);
    $stC = $pdo->prepare($cSql);
    $stC->execute($cParams);
    $campaignsData = $stC->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

/* ============================================================================
 * ۲. استخراج ریز تراکنش‌ها (Detailed Transactions)
 * ========================================================================== */
$transactions = [];

// استخراج از panel_donations (اگر فیلتر منبع روی stands یا courses محدود نشده باشد)
if (in_array($sourceFilter, ['all', 'direct', 'campaign'], true)) {
    $sourceSubCond = "";
    if ($sourceFilter === 'direct') {
        $sourceSubCond = " AND pd.campaign_id IS NULL";
    } elseif ($sourceFilter === 'campaign') {
        $sourceSubCond = " AND pd.campaign_id IS NOT NULL";
        if ($campaignFilter > 0) {
            $sourceSubCond .= " AND pd.campaign_id = " . (int)$campaignFilter;
        }
    }

    try {
        $txSql = "SELECT pd.id, pd.reference, pd.amount, pd.type, pd.status,
                         COALESCE(pd.paid_at, pd.created_at) AS tx_date,
                         TRIM(CONCAT(COALESCE(up.first_name,''),' ',COALESCE(up.last_name,''))) AS donor_name,
                         pu.email, pu.phone,
                         c.title AS campaign_title,
                         b.name AS branch_name
                    FROM panel_donations pd
                    LEFT JOIN panel_users pu   ON pu.id = pd.user_id
                    LEFT JOIN user_profiles up ON up.user_id = pd.user_id
                    LEFT JOIN campaigns c      ON c.id = pd.campaign_id
                    LEFT JOIN branches b       ON b.id = pd.branch_id
                   WHERE 1=1 {$sourceSubCond} {$dateCondDonations} {$branchCondDonations} {$statusCondDonations}
                   ORDER BY COALESCE(pd.paid_at, pd.created_at) DESC
                   LIMIT 5000";
        $st = $pdo->prepare($txSql);
        $st->execute($paramsDonations);
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $srcLabel = !empty($row['campaign_title']) ? 'کمپین خیریه' : 'کمک مستقیم آنلاین';
            $contact = $row['phone'] ?: ($row['email'] ?: '—');
            $name = trim((string)$row['donor_name']) ?: ($row['email'] ? explode('@', $row['email'])[0] : 'خیر ناشناس');
            $statusLabel = ($row['status'] === 'success') ? 'موفق' : (($row['status'] === 'pending') ? 'در انتظار' : 'ناموفق');
            
            $transactions[] = [
                'ref'         => $row['reference'] ?: ('TX-' . $row['id']),
                'date'        => format_jalali_datetime($row['tx_date']),
                'donor_name'  => $name,
                'contact'     => $contact,
                'source'      => $srcLabel,
                'detail'      => $row['campaign_title'] ?: 'کمک مستقیم آنلاین',
                'branch'      => $row['branch_name'] ?: 'مرکزی',
                'amount'      => (float)$row['amount'],
                'status'      => $statusLabel,
                'raw_ts'      => strtotime((string)$row['tx_date']),
            ];
        }
    } catch (Throwable $e) {}
}

// استخراج از orders (استندها) (اگر فیلتر منبع روی all یا stands باشد)
if (in_array($sourceFilter, ['all', 'stands'], true) && in_array($statusFilter, ['all', 'success'], true)) {
    try {
        $ordSql = "SELECT o.id, o.tracking_code, o.total_price, o.created_at,
                          o.from_user, o.to_user, b.name AS branch_name, s.title AS stand_title
                     FROM orders o
                     LEFT JOIN branches b ON b.id = o.branch_id
                     LEFT JOIN stands s   ON s.id = o.stand_id
                    WHERE 1=1 {$dateCondOrders} {$branchCondOrders}
                    ORDER BY o.created_at DESC
                    LIMIT 2000";
        $st = $pdo->prepare($ordSql);
        $st->execute($paramsOrders);
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $ref = $row['tracking_code'] ?: ('ORD-' . $row['id']);
            $transactions[] = [
                'ref'         => $ref,
                'date'        => format_jalali_datetime($row['created_at']),
                'donor_name'  => $row['from_user'] ?: 'سفارش‌دهنده استند',
                'contact'     => 'تحویل‌گیرنده: ' . ($row['to_user'] ?: '—'),
                'source'      => 'استند خیریه',
                'detail'      => $row['stand_title'] ?: 'سفارش استند',
                'branch'      => $row['branch_name'] ?: 'شعبه',
                'amount'      => (float)$row['total_price'],
                'status'      => 'موفق',
                'raw_ts'      => strtotime((string)$row['created_at']),
            ];
        }
    } catch (Throwable $e) {}
}

// مرتب‌سازی تجمیعی بر اساس زمان
usort($transactions, static function($a, $b) {
    return ($b['raw_ts'] ?? 0) <=> ($a['raw_ts'] ?? 0);
});

/* ============================================================================
 * ۳. استخراج حامیان برتر (Top Donors)
 * ========================================================================== */
$topDonors = [];
try {
    $donSql = "SELECT TRIM(CONCAT(COALESCE(up.first_name,''),' ',COALESCE(up.last_name,''))) AS donor_name,
                      pu.email, pu.phone,
                      COUNT(pd.id) AS tx_count,
                      COALESCE(SUM(pd.amount), 0) AS total_amount
                 FROM panel_donations pd
                 JOIN panel_users pu ON pu.id = pd.user_id
                 LEFT JOIN user_profiles up ON up.user_id = pd.user_id
                WHERE pd.status = 'success' {$dateCondDonations} {$branchCondDonations}
                GROUP BY pd.user_id, pu.email, pu.phone, donor_name
                ORDER BY total_amount DESC
                LIMIT 50";
    $st = $pdo->prepare($donSql);
    $st->execute($paramsDonations);
    $topDonors = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

/* ============================================================================
 * ۴. خروجی در فرمت انتخابی (CSV یا XML Spreadsheet Multi-sheet)
 * ========================================================================== */
$format = strtolower(trim((string)($_GET['format'] ?? 'excel')));

if ($format === 'csv') {
    // خروجی تک‌شیت استاندارد CSV با UTF-8 BOM
    $filename = "گزارش_ریز_تراکنش_های_مکسا_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // چاپ UTF-8 BOM برای جلوگیری از به هم ریختگی فارسی در اکسل
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ردیف', 'کد پیگیری / ارجاع', 'تاریخ و ساعت شمسی', 'نام پرداخت‌کننده', 'اطلاعات تماس', 'منبع مالی', 'عنوان / شرح', 'شعبه', 'مبلغ (تومان)', 'وضعیت']);

    $idx = 1;
    foreach ($transactions as $t) {
        fputcsv($out, [
            $idx++,
            $t['ref'],
            $t['date'],
            $t['donor_name'],
            $t['contact'],
            $t['source'],
            $t['detail'],
            $t['branch'],
            number_format($t['amount']),
            $t['status'],
        ]);
    }
    fclose($out);
    exit;
}

// فرمت پیش‌فرض: XML Spreadsheet 2003 Multi-Sheet (اکسل واقعی با چند کاربرگ)
$filename = "گزارش_جامع_مالی_مکسا_" . date('Y-m-d') . ".xls";
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$generatedAtJalali = format_jalali_datetime(date('Y-m-d H:i:s'));
$dateRangeLabel = ($startDate && $endDate)
    ? ('از ' . format_jalali_date($startDate) . ' تا ' . format_jalali_date($endDate))
    : ($startDate ? ('از ' . format_jalali_date($startDate)) : ($endDate ? ('تا ' . format_jalali_date($endDate)) : 'کل دوره'));

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Center" ss:ReadingOrder="RightToLeft"/>
   <Borders/>
   <Font ss:FontName="Vazirmatn" x:Family="Swiss" ss:Size="11" ss:Color="#2F3437"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <!-- سربرگ اصلی شیت -->
  <Style ss:ID="TitleStyle">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Vazirmatn" ss:Size="14" ss:Color="#FFFFFF" ss:Bold="1"/>
   <Interior ss:Color="#007B7A" ss:Pattern="Solid"/>
  </Style>
  <!-- زیرعنوان -->
  <Style ss:ID="SubTitleStyle">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Vazirmatn" ss:Size="10" ss:Color="#5B6469" ss:Bold="1"/>
   <Interior ss:Color="#F8F9FA" ss:Pattern="Solid"/>
  </Style>
  <!-- سرستون‌های جداول -->
  <Style ss:ID="HeaderStyle">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#006665"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#006665"/>
   </Borders>
   <Font ss:FontName="Vazirmatn" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
   <Interior ss:Color="#006665" ss:Pattern="Solid"/>
  </Style>
  <!-- ردیف عادی جدول -->
  <Style ss:ID="DataCell">
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E6E8EA"/>
   </Borders>
  </Style>
  <Style ss:ID="DataCellCenter">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E6E8EA"/>
   </Borders>
  </Style>
  <!-- مبالغ ریالی/تومانی -->
  <Style ss:ID="AmountCell">
   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E6E8EA"/>
   </Borders>
   <Font ss:FontName="Vazirmatn" ss:Size="11" ss:Bold="1" ss:Color="#006665"/>
   <NumberFormat ss:Format="#,##0"/>
  </Style>
  <!-- جمع کل (Total) -->
  <Style ss:ID="TotalRow">
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#007B7A"/>
    <Border ss:Position="Bottom" ss:LineStyle="Double" ss:Weight="3" ss:Color="#007B7A"/>
   </Borders>
   <Font ss:FontName="Vazirmatn" ss:Size="12" ss:Bold="1" ss:Color="#007B7A"/>
   <Interior ss:Color="#EEF6F6" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="TotalAmount">
   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#007B7A"/>
    <Border ss:Position="Bottom" ss:LineStyle="Double" ss:Weight="3" ss:Color="#007B7A"/>
   </Borders>
   <Font ss:FontName="Vazirmatn" ss:Size="12" ss:Bold="1" ss:Color="#007B7A"/>
   <Interior ss:Color="#EEF6F6" ss:Pattern="Solid"/>
   <NumberFormat ss:Format="#,##0"/>
  </Style>
 </Styles>

 <!-- ======================================================================= -->
 <!-- کاربرگ ۱: خلاصه مدیریتی (Executive Summary) -->
 <!-- ======================================================================= -->
 <Worksheet ss:Name="خلاصه مدیریتی" ss:RightToLeft="1">
  <Table ss:DefaultColumnWidth="120" ss:DefaultRowHeight="24">
   <Column ss:Width="40"/>
   <Column ss:Width="200"/>
   <Column ss:Width="160"/>
   <Column ss:Width="120"/>

   <Row ss:Height="36">
    <Cell ss:MergeAcross="3" ss:StyleID="TitleStyle">
     <Data ss:Type="String">گزارش جامع مالی خیریه مکسا — خلاصه مدیریتی</Data>
    </Cell>
   </Row>
   <Row ss:Height="22">
    <Cell ss:MergeAcross="3" ss:StyleID="SubTitleStyle">
     <Data ss:Type="String">شعبه: <?= xml_esc($branchName) ?>  |  بازه زمانی: <?= xml_esc($dateRangeLabel) ?>  |  تاریخ گزارش: <?= xml_esc($generatedAtJalali) ?></Data>
    </Cell>
   </Row>
   <Row ss:Height="12"></Row>

   <!-- جدول تفکیک منابع -->
   <Row ss:Height="28">
    <Cell ss:MergeAcross="3" ss:StyleID="HeaderStyle">
     <Data ss:Type="String">تفکیک وصولی بر اساس منابع مالی</Data>
    </Cell>
   </Row>
   <Row ss:Height="24">
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">ردیف</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">منبع مالی</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">مبلغ وصولی (تومان)</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">سهم از کل</Data></Cell>
   </Row>

   <?php
   $sources = [
       ['title' => 'کمک مستقیم آنلاین خیرین (عمومی)', 'amount' => $onlineHelpTotal],
       ['title' => 'کمپین‌های هدفمند خیریه', 'amount' => $campaignHelpTotal],
       ['title' => 'عواید استندها و تاج‌گل‌های خیریه', 'amount' => $standsTotal],
       ['title' => 'ثبت‌نام دوره‌ها و کارگاه‌های آموزشی', 'amount' => $coursesTotal],
   ];
   $sIdx = 1;
   foreach ($sources as $s):
       $pct = $grandTotal > 0 ? round(($s['amount'] / $grandTotal) * 100, 1) : 0;
   ?>
   <Row ss:Height="24">
    <Cell ss:StyleID="DataCellCenter"><Data ss:Type="Number"><?= $sIdx++ ?></Data></Cell>
    <Cell ss:StyleID="DataCell"><Data ss:Type="String"><?= xml_esc($s['title']) ?></Data></Cell>
    <Cell ss:StyleID="AmountCell"><Data ss:Type="Number"><?= $s['amount'] ?></Data></Cell>
    <Cell ss:StyleID="DataCellCenter"><Data ss:Type="String"><?= $pct ?>٪</Data></Cell>
   </Row>
   <?php endforeach; ?>

   <Row ss:Height="28">
    <Cell ss:MergeAcross="1" ss:StyleID="TotalRow"><Data ss:Type="String">مجموع کل درآمدها و کمک‌های وصول‌شده:</Data></Cell>
    <Cell ss:StyleID="TotalAmount"><Data ss:Type="Number"><?= $grandTotal ?></Data></Cell>
    <Cell ss:StyleID="TotalRow"><Data ss:Type="String">۱۰۰٪</Data></Cell>
   </Row>

   <Row ss:Height="20"></Row>

   <!-- جدول کمپین‌ها -->
   <?php if (!empty($campaignsData)): ?>
   <Row ss:Height="28">
    <Cell ss:MergeAcross="3" ss:StyleID="HeaderStyle">
     <Data ss:Type="String">عملکرد مالی کمپین‌های فعال در این بازه</Data>
    </Cell>
   </Row>
   <Row ss:Height="24">
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">ردیف</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">عنوان کمپین</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">مبلغ جمع‌آوری‌شده (تومان)</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">تعداد واریزی</Data></Cell>
   </Row>
   <?php
   $cIdx = 1;
   foreach ($campaignsData as $cRow):
   ?>
   <Row ss:Height="24">
    <Cell ss:StyleID="DataCellCenter"><Data ss:Type="Number"><?= $cIdx++ ?></Data></Cell>
    <Cell ss:StyleID="DataCell"><Data ss:Type="String"><?= xml_esc($cRow['title']) ?></Data></Cell>
    <Cell ss:StyleID="AmountCell"><Data ss:Type="Number"><?= (float)$cRow['total_collected'] ?></Data></Cell>
    <Cell ss:StyleID="DataCellCenter"><Data ss:Type="Number"><?= (int)$cRow['tx_count'] ?></Data></Cell>
   </Row>
   <?php endforeach; ?>
   <?php endif; ?>

  </Table>
 </Worksheet>

 <!-- ======================================================================= -->
 <!-- کاربرگ ۲: ریز تراکنش‌ها (Detailed Transactions) -->
 <!-- ======================================================================= -->
 <Worksheet ss:Name="ریز تراکنش‌ها" ss:RightToLeft="1">
  <Table ss:DefaultColumnWidth="120" ss:DefaultRowHeight="24">
   <Column ss:Width="40"/>
   <Column ss:Width="160"/>
   <Column ss:Width="130"/>
   <Column ss:Width="150"/>
   <Column ss:Width="140"/>
   <Column ss:Width="130"/>
   <Column ss:Width="180"/>
   <Column ss:Width="110"/>
   <Column ss:Width="130"/>
   <Column ss:Width="80"/>

   <Row ss:Height="30">
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">ردیف</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">شناسه / کد رهگیری</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">تاریخ و ساعت شمسی</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">نام پرداخت‌کننده</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">اطلاعات تماس</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">منبع مالی</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">عنوان / کمپین</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">شعبه</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">مبلغ (تومان)</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">وضعیت</Data></Cell>
   </Row>

   <?php
   $tIdx = 1;
   $sumTx = 0.0;
   foreach ($transactions as $t):
       $sumTx += (float)$t['amount'];
   ?>
   <Row ss:Height="22">
    <Cell ss:StyleID="DataCellCenter"><Data ss:Type="Number"><?= $tIdx++ ?></Data></Cell>
    <Cell ss:StyleID="DataCellCenter"><Data ss:Type="String"><?= xml_esc($t['ref']) ?></Data></Cell>
    <Cell ss:StyleID="DataCellCenter"><Data ss:Type="String"><?= xml_esc($t['date']) ?></Data></Cell>
    <Cell ss:StyleID="DataCell"><Data ss:Type="String"><?= xml_esc($t['donor_name']) ?></Data></Cell>
    <Cell ss:StyleID="DataCell"><Data ss:Type="String"><?= xml_esc($t['contact']) ?></Data></Cell>
    <Cell ss:StyleID="DataCell"><Data ss:Type="String"><?= xml_esc($t['source']) ?></Data></Cell>
    <Cell ss:StyleID="DataCell"><Data ss:Type="String"><?= xml_esc($t['detail']) ?></Data></Cell>
    <Cell ss:StyleID="DataCellCenter"><Data ss:Type="String"><?= xml_esc($t['branch']) ?></Data></Cell>
    <Cell ss:StyleID="AmountCell"><Data ss:Type="Number"><?= (float)$t['amount'] ?></Data></Cell>
    <Cell ss:StyleID="DataCellCenter"><Data ss:Type="String"><?= xml_esc($t['status']) ?></Data></Cell>
   </Row>
   <?php endforeach; ?>

   <Row ss:Height="26">
    <Cell ss:MergeAcross="7" ss:StyleID="TotalRow"><Data ss:Type="String">مجموع تراکنش‌های فوق:</Data></Cell>
    <Cell ss:StyleID="TotalAmount"><Data ss:Type="Number"><?= $sumTx ?></Data></Cell>
    <Cell ss:StyleID="TotalRow"><Data ss:Type="String">—</Data></Cell>
   </Row>
  </Table>
 </Worksheet>

 <!-- ======================================================================= -->
 <!-- کاربرگ ۳: حامیان برتر (Top Donors) -->
 <!-- ======================================================================= -->
 <Worksheet ss:Name="حامیان برتر" ss:RightToLeft="1">
  <Table ss:DefaultColumnWidth="130" ss:DefaultRowHeight="24">
   <Column ss:Width="40"/>
   <Column ss:Width="200"/>
   <Column ss:Width="160"/>
   <Column ss:Width="120"/>
   <Column ss:Width="160"/>

   <Row ss:Height="30">
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">رتبه</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">نام و نام خانوادگی خیر</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">اطلاعات تماس (ایمیل/تلفن)</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">تعداد دفعات حمایت</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">مجموع مبلغ اهدایی (تومان)</Data></Cell>
   </Row>

   <?php
   $dIdx = 1;
   foreach ($topDonors as $d):
       $dName = trim((string)$d['donor_name']) ?: ($d['email'] ? explode('@', $d['email'])[0] : 'خیر ناشناس');
       $dContact = $d['phone'] ?: ($d['email'] ?: '—');
   ?>
   <Row ss:Height="22">
    <Cell ss:StyleID="DataCellCenter"><Data ss:Type="Number"><?= $dIdx++ ?></Data></Cell>
    <Cell ss:StyleID="DataCell"><Data ss:Type="String"><?= xml_esc($dName) ?></Data></Cell>
    <Cell ss:StyleID="DataCell"><Data ss:Type="String"><?= xml_esc($dContact) ?></Data></Cell>
    <Cell ss:StyleID="DataCellCenter"><Data ss:Type="Number"><?= (int)$d['tx_count'] ?></Data></Cell>
    <Cell ss:StyleID="AmountCell"><Data ss:Type="Number"><?= (float)$d['total_amount'] ?></Data></Cell>
   </Row>
   <?php endforeach; ?>
  </Table>
 </Worksheet>

</Workbook>

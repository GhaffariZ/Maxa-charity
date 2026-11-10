<?php
declare(strict_types=1);

require_once __DIR__ . '/_guard.php';
dash_require('medical');

$isSuper = dash_is_super();
$isHq = dash_is_hq_view();
$activeBranchId = dash_active_branch_id();
$search = trim((string)($_GET['q'] ?? ''));
$recordId = max(0, (int)($_GET['id'] ?? 0));
$statusFilter = is_string($_GET['status'] ?? null) ? $_GET['status'] : '';
$where = [];
$params = [];

if (!$isSuper || !$isHq || ($_GET['scope'] ?? '') === 'active') {
    $where[] = 'mr.branch_id = ?';
    $params[] = $activeBranchId;
}
if ($recordId > 0) {
    $where[] = 'mr.id = ?';
    $params[] = $recordId;
}
if ($statusFilter === 'pending') {
    $where[] = "mr.status IN ('new','reviewing')";
}
if ($search !== '') {
    $where[] = '(mr.full_name LIKE ? OR mr.phone LIKE ? OR mr.province LIKE ? OR mr.city LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term, $term);
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$records = [];
$filesByRecord = [];
$dbError = null;

try {
    $stmt = $pdo->prepare(
        "SELECT mr.*, b.name AS branch_name
           FROM medical_records mr
           LEFT JOIN branches b ON b.id = mr.branch_id
           {$whereSql}
          ORDER BY mr.id DESC
          LIMIT 200"
    );
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($records) {
        $ids = array_column($records, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $fileStmt = $pdo->prepare("SELECT * FROM medical_record_files WHERE record_id IN ({$placeholders}) ORDER BY id");
        $fileStmt->execute($ids);
        foreach ($fileStmt->fetchAll(PDO::FETCH_ASSOC) as $file) {
            $filesByRecord[(int)$file['record_id']][] = $file;
        }
    }
} catch (Throwable $e) {
    error_log('Dashboard medical records: ' . $e->getMessage());
    $dbError = 'دریافت پرونده‌های پزشکی ناموفق بود. مدیر سامانه باید اتصال و سازگاری ساختار دیتابیس با مهاجرت‌های 017 و 027 را بررسی کند.';
}

function medical_e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function medical_status(string $status): string {
    return ['new'=>'جدید', 'reviewing'=>'در حال بررسی', 'contacted'=>'تماس گرفته شد', 'closed'=>'بسته شده'][$status] ?? $status;
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>پرونده‌های پزشکی | پنل مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<style>
@font-face{font-family:Vazirmatn;src:url('/webfont/Vazirmatn[wght].woff2') format('woff2');font-weight:100 900;font-display:swap}
:root{--primary:#008f8a;--text:#2f3437;--muted:#707b81;--border:#e4e8ea;--bg:#f6f8f8;--surface:#fff}
:root[data-theme="dark"]{--primary:#79cbc7;--text:#e7ecee;--muted:#a4aeb4;--border:#2a343a;--bg:#0f1518;--surface:#19232a;color-scheme:dark}
[data-theme="dark"] th{background:#203337}[data-theme="dark"] .badge{background:#203c3c;color:#9bd8d5}
[data-theme="dark"] .search button{background:#008f8a;color:#fff}.medical-table a{color:var(--primary)}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:Vazirmatn,Tahoma,sans-serif;padding:28px 20px}.wrap{max-width:1200px;margin:auto}.top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:22px}.top h1{margin:0;font-size:25px}.back{color:var(--primary);text-decoration:none;font-weight:600}.search{display:flex;gap:8px;margin-bottom:18px}.search input{min-width:280px;max-width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px;font:inherit}.search button{padding:10px 18px;border:0;border-radius:10px;background:var(--primary);color:#fff;font:inherit;font-weight:600;cursor:pointer}.table-wrap{overflow:auto;background:var(--surface);border:1px solid var(--border);border-radius:16px}table{width:100%;border-collapse:collapse;min-width:900px}th,td{padding:13px 15px;text-align:right;border-bottom:1px solid var(--border);vertical-align:top}th{background:#eef7f7;font-size:13px}td{font-size:13px}.badge{display:inline-block;padding:3px 9px;border-radius:20px;background:#e9f7f5;color:#08766f;font-weight:600}.details{max-width:330px;color:var(--muted)}details summary{cursor:pointer;color:var(--primary);font-weight:600}.files{display:flex;flex-wrap:wrap;gap:7px;margin-top:8px}.files a{color:var(--primary)}.notice{padding:16px;border-radius:12px;background:#fff3f1;color:#b42318;margin-bottom:16px}.empty{text-align:center;padding:45px;color:var(--muted)}
@media(max-width:600px){body{padding:18px 12px}.search{display:grid}.search input{min-width:0;width:100%}}
@media(max-width:760px){
  .medical-table{min-width:0}.medical-table thead{position:absolute;inline-size:1px;block-size:1px;clip-path:inset(50%);overflow:hidden}
  .medical-table,.medical-table tbody,.medical-table tr,.medical-table td{display:block}.medical-table tr{padding:8px 14px;border-bottom:1px solid var(--border)}
  .medical-table td{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;border:0;padding:9px 0;font-size:13px;text-align:end}
  .medical-table td::before{content:attr(data-label);font-size:12px;color:var(--muted);text-align:start;flex-shrink:0}
  .medical-table td>div{min-width:0;overflow-wrap:anywhere}.medical-table .details{max-width:none}.search select{padding:10px;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);font:inherit}
}
</style>
<link rel="stylesheet" href="/dashboard/assets/css/admin-ledger.css">
<link rel="stylesheet" href="/dashboard/assets/css/dashboard-responsive.css">
</head>
<body>
<main class="wrap">
  <div class="top">
    <div><h1>پرونده‌های پزشکی</h1><div><?= count($records) ?> پرونده اخیر</div></div>
    <a class="back" href="index.php">بازگشت به داشبورد</a>
  </div>

  <?php if ($dbError): ?><div class="notice"><?= medical_e($dbError) ?></div><?php endif; ?>

  <form class="search" method="get">
    <input name="q" value="<?= medical_e($search) ?>" placeholder="جستجو با نام، موبایل، استان یا شهر">
    <select name="status" aria-label="وضعیت پرونده"><option value="">همهٔ وضعیت‌ها</option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>جدید و در حال بررسی</option></select>
    <?php if (($_GET['scope'] ?? '') === 'active'): ?><input type="hidden" name="scope" value="active"><?php endif; ?>
    <button type="submit">جستجو</button>
    <?php if ($recordId || $statusFilter || $search !== ''): ?><a class="back" href="medical-records.php">نمایش همهٔ پرونده‌های مجاز</a><?php endif; ?>
  </form>

  <div class="table-wrap">
    <?php if (!$records): ?>
      <div class="empty">پرونده‌ای برای نمایش وجود ندارد.</div>
    <?php else: ?>
    <table class="medical-table">
      <thead><tr><th>شماره</th><th>بیمار</th><th>موقعیت</th><th>اطلاعات پزشکی</th><th>وضعیت</th><th>تاریخ ثبت</th></tr></thead>
      <tbody>
      <?php foreach ($records as $record): ?>
        <tr>
          <td data-label="شماره"><div>#<?= (int)$record['id'] ?><br><small><?= medical_e($record['branch_name']) ?></small></div></td>
          <td data-label="بیمار"><div><strong><?= medical_e($record['full_name']) ?></strong><br><a href="tel:<?= medical_e($record['phone']) ?>"><?= medical_e($record['phone']) ?></a><?php if ($record['age']): ?><br><?= (int)$record['age'] ?> سال<?php endif; ?></div></td>
          <td data-label="موقعیت"><div><?= medical_e($record['province']) ?>، <?= medical_e($record['city']) ?></div></td>
          <td class="details" data-label="اطلاعات پزشکی"><div>
            <div><?= medical_e($record['cancer_type'] ?: 'نوع سرطان ثبت نشده') ?> — <?= medical_e($record['diagnosis_status'] ?: 'وضعیت تشخیص ثبت نشده') ?></div>
            <details><summary>توضیحات و مدارک</summary><p><?= nl2br(medical_e($record['description'] ?: 'بدون توضیحات')) ?></p>
              <div class="files">
              <?php foreach ($filesByRecord[(int)$record['id']] ?? [] as $file): ?>
                <a href="medical-record-file.php?id=<?= (int)$file['id'] ?>" target="_blank" rel="noopener"><?= medical_e($file['original_name']) ?></a>
              <?php endforeach; ?>
              </div>
            </details>
          </div></td>
          <td data-label="وضعیت"><span class="badge"><?= medical_e(medical_status($record['status'])) ?></span></td>
          <td data-label="تاریخ ثبت"><div><?= medical_e($record['created_at']) ?></div></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</main>
</body>
</html>

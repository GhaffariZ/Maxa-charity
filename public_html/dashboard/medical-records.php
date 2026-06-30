<?php
require_once __DIR__ . '/_guard.php';
dash_require('medical');

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
  $pdo = new PDO(
    "mysql:host={$DB['host']};dbname={$DB['name']}",
    $DB['user'], $DB['pass'],
    $opts
  );
  try { $pdo->exec("SET NAMES {$DB['charset']}"); } catch (Throwable $e) { $pdo->exec("SET NAMES utf8"); }
} catch (Throwable $e) {
  $dbError = $e->getMessage();
}

$items = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM medical_records ORDER BY created_at DESC");
        $items = $stmt->fetchAll();
    } catch (Throwable $e) {
        $dbError = $e->getMessage();
    }
}

$total = count($items);

if(!function_exists('fb_fa')){
  function fb_fa($n){ return strtr((string)$n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']); }
}
if(!function_exists('fb_initials')){
  function fb_initials($n){ $p=preg_split('/\s+/',trim($n)); $a=mb_substr($p[0]??'',0,1,'UTF-8'); $b=isset($p[1])?mb_substr($p[1],0,1,'UTF-8'):''; return $a.$b; }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>پرونده‌های پزشکی | پنل مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b;
  --primary-08:rgba(0,123,122,.08); --primary-12:rgba(0,123,122,.12);
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --primary-08:rgba(79,178,176,.10);
  color-scheme:dark; background-color:var(--color-bg);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;padding:28px 22px;transition:background .3s,color .3s}
.fb-wrap{max-width:1000px;margin:0 auto}
.fb-head{display:flex;align-items:center;gap:16px;margin-bottom:20px}
.fb-head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.fb-head-ic svg{width:27px;height:27px}
.fb-head h1{font-size:22px;font-weight:800;letter-spacing:-.01em}
.fb-head p{font-size:13px;color:var(--color-muted);margin-top:3px}

.dtable{width:100%;border-collapse:collapse;font-size:13.5px;background:var(--color-surface);border-radius:16px;overflow:hidden;box-shadow:var(--shadow-sm);}
.dtable thead{background:var(--primary-08);}
.dtable th{text-align:right;font-weight:700;color:var(--color-primary-dark);padding:14px 18px;}
.dtable td{padding:14px 18px;border-bottom:1px solid var(--color-border);vertical-align:middle;}
.dtable tbody tr:hover{background:var(--color-bg);}
.dtable tbody tr:last-child td{border-bottom:none;}
.cell-user{display:flex;align-items:center;gap:11px}
.cell-av{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;background:var(--color-primary-light)}
.cell-name{font-weight:800;font-size:14px}
.cell-sub{font-size:12px;color:var(--color-muted);margin-top:2px}

.tbtn{display:inline-flex;align-items:center;justify-content:center;height:34px;padding:0 14px;border-radius:10px;font-family:inherit;font-weight:700;font-size:12px;color:var(--color-primary-dark);background:var(--primary-08);cursor:pointer;transition:background .2s,color .2s;text-decoration:none;}
.tbtn:hover{background:var(--color-primary);color:#fff;}
</style>
</head>
<body>
<div class="fb-wrap">
  <div class="fb-head">
    <div class="fb-head-ic">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2"/><rect x="8" y="2" width="8" height="4" rx="1"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/>
      </svg>
    </div>
    <div>
      <h1>پرونده‌های پزشکی</h1>
      <p>مدیریت و مشاهده پرونده‌های پزشکی مجازی ثبت‌شده توسط کاربران.</p>
    </div>
  </div>
  
  <?php if ($dbError): ?>
    <div style="background:var(--danger);color:#fff;padding:12px;border-radius:8px;margin-bottom:15px;"><?= htmlspecialchars($dbError) ?></div>
  <?php endif; ?>

  <table class="dtable">
    <thead>
      <tr>
        <th>بیمار</th>
        <th>نوع بیماری / تشخیص</th>
        <th>استان / شهر</th>
        <th>تاریخ ثبت</th>
        <th>عملیات</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($items)): ?>
      <tr>
        <td colspan="5" style="text-align:center;color:var(--color-muted);padding:30px;">هیچ پرونده‌ای یافت نشد.</td>
      </tr>
      <?php else: foreach($items as $row): ?>
      <tr>
        <td>
          <div class="cell-user">
            <div class="cell-av"><?= fb_initials($row['full_name']) ?></div>
            <div>
              <div class="cell-name"><?= htmlspecialchars($row['full_name']) ?></div>
              <div class="cell-sub"><?= fb_fa(htmlspecialchars($row['mobile'])) ?> (<?= $row['age'] ? fb_fa($row['age']) . ' ساله' : 'سن نامشخص' ?>)</div>
            </div>
          </div>
        </td>
        <td>
          <div style="font-weight:700;"><?= htmlspecialchars($row['cancer_type'] ?: 'نامشخص') ?></div>
          <div class="cell-sub"><?= htmlspecialchars($row['diagnosis_status'] ?: 'نامشخص') ?></div>
        </td>
        <td>
          <div style="font-weight:600;"><?= htmlspecialchars($row['province']) ?></div>
          <div class="cell-sub"><?= htmlspecialchars($row['city']) ?></div>
        </td>
        <td class="cell-sub" dir="ltr" style="text-align:right">
          <?= fb_fa(date('Y/m/d H:i', strtotime($row['created_at']))) ?>
        </td>
        <td>
          <a href="medical-record-details.php?id=<?= $row['id'] ?>" class="tbtn">مشاهده کامل</a>
        </td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

</div>
</body>
</html>

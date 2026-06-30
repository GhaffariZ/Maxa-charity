<?php
require_once __DIR__ . '/_guard.php';
dash_require('medical');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    die("شناسه پرونده نامعتبر است.");
}

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
  try { $pdo->exec("SET NAMES {$DB['charset']}"); } catch (Throwable $e) {}
} catch (Throwable $e) {
  $dbError = $e->getMessage();
}

$record = null;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM medical_records WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch();
    } catch (Throwable $e) {
        $dbError = $e->getMessage();
    }
}

if (!$record) {
    die("پرونده یافت نشد.");
}

if(!function_exists('fb_fa')){
  function fb_fa($n){ return strtr((string)$n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']); }
}
$documents = json_decode($record['documents'], true);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>جزئیات پرونده پزشکی | پنل مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  color-scheme:dark; background-color:var(--color-bg);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;padding:28px 22px;transition:background .3s,color .3s}
.fb-wrap{max-width:900px;margin:0 auto}

.card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:18px;box-shadow:var(--shadow-sm);padding:30px;margin-bottom:20px;}
.card-title{font-size:18px;font-weight:800;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
.card-title::before{content:'';width:4px;height:20px;background:var(--color-primary);border-radius:4px;}

.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
.detail-item{margin-bottom:15px;}
.detail-label{font-size:12px;color:var(--color-muted);font-weight:700;margin-bottom:5px;}
.detail-value{font-size:15px;font-weight:700;background:var(--color-bg);padding:10px 15px;border-radius:10px;border:1px solid var(--color-border);}
.detail-desc{grid-column:1 / -1;}
.detail-desc .detail-value{min-height:80px;white-space:pre-wrap;}

.doc-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));gap:15px;}
.doc-item{display:block;border-radius:12px;overflow:hidden;border:1px solid var(--color-border);background:var(--color-bg);transition:transform .2s;}
.doc-item:hover{transform:translateY(-3px);box-shadow:var(--shadow-md);}
.doc-img{width:100%;height:150px;object-fit:cover;}
.doc-name{font-size:12px;text-align:center;padding:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;direction:ltr;}

.back-btn{display:inline-flex;align-items:center;gap:8px;background:var(--color-surface);border:1px solid var(--color-border);padding:8px 16px;border-radius:12px;font-weight:700;color:var(--color-text);text-decoration:none;margin-bottom:20px;transition:all .2s;}
.back-btn:hover{background:var(--color-bg);border-color:var(--color-primary-light);}
</style>
</head>
<body>
<div class="fb-wrap">
  
  <a href="medical-records.php" class="back-btn">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    بازگشت به لیست
  </a>

  <div class="card">
    <div class="card-title">اطلاعات فردی بیمار</div>
    <div class="detail-grid">
      <div class="detail-item">
        <div class="detail-label">نام و نام خانوادگی</div>
        <div class="detail-value"><?= htmlspecialchars($record['full_name']) ?></div>
      </div>
      <div class="detail-item">
        <div class="detail-label">شماره موبایل</div>
        <div class="detail-value"><?= fb_fa(htmlspecialchars($record['mobile'])) ?></div>
      </div>
      <div class="detail-item">
        <div class="detail-label">سن</div>
        <div class="detail-value"><?= $record['age'] ? fb_fa($record['age']) . ' سال' : '—' ?></div>
      </div>
      <div class="detail-item">
        <div class="detail-label">جنسیت</div>
        <div class="detail-value"><?= htmlspecialchars($record['gender'] ?: '—') ?></div>
      </div>
      <div class="detail-item">
        <div class="detail-label">استان</div>
        <div class="detail-value"><?= htmlspecialchars($record['province']) ?></div>
      </div>
      <div class="detail-item">
        <div class="detail-label">شهر</div>
        <div class="detail-value"><?= htmlspecialchars($record['city']) ?></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">اطلاعات پزشکی</div>
    <div class="detail-grid">
      <div class="detail-item">
        <div class="detail-label">نوع سرطان</div>
        <div class="detail-value"><?= htmlspecialchars($record['cancer_type'] ?: '—') ?></div>
      </div>
      <div class="detail-item">
        <div class="detail-label">وضعیت تشخیص</div>
        <div class="detail-value"><?= htmlspecialchars($record['diagnosis_status'] ?: '—') ?></div>
      </div>
      <div class="detail-item detail-desc">
        <div class="detail-label">توضیحات بیشتر</div>
        <div class="detail-value"><?= nl2br(htmlspecialchars($record['description'] ?: 'بدون توضیحات')) ?></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">مدارک پزشکی</div>
    <?php if(!empty($documents) && is_array($documents)): ?>
      <div class="doc-grid">
        <?php foreach($documents as $doc): ?>
          <a href="<?= htmlspecialchars($doc) ?>" target="_blank" class="doc-item">
            <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $doc)): ?>
              <img src="<?= htmlspecialchars($doc) ?>" class="doc-img" alt="مدرک پزشکی">
            <?php else: ?>
              <div class="doc-img" style="display:grid;place-items:center;background:var(--primary-08);color:var(--color-primary);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
              </div>
            <?php endif; ?>
            <div class="doc-name"><?= basename($doc) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="color:var(--color-muted);font-weight:600;padding:20px;text-align:center;border:2px dashed var(--color-border);border-radius:12px;">مدرکی آپلود نشده است.</div>
    <?php endif; ?>
  </div>

</div>
</body>
</html>

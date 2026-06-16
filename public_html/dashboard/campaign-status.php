<?php
require_once __DIR__ . '/_guard.php';
dash_require('campaigns'); 
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

// عملیات تغییر وضعیت کمپین (فعال / غیرفعال سازی) بدون نیاز به alert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $campaign_id = (int)$_POST['toggle_id'];
    $current_status = (int)$_POST['current_status'];
    $new_status = $current_status === 1 ? 0 : 1;
    
    // IDOR: فقط کمپین‌های شعبه‌ی فعال (سوپرادمین هر شعبه‌ای که فعال کرده باشد)
    $__branch = dash_active_branch_id();
    $stmt = $pdo->prepare("UPDATE campaigns SET is_active = ? WHERE id = ? AND branch_id = ?");
    $stmt->execute([$new_status, $campaign_id, $__branch]);

    // رفرش صفحه برای اعمال تغییرات
    header("Location: campaign-status.php");
    exit;
}

// نمای مرکزی: همه‌ی کمپین‌های همه‌ی شعب با برچسبِ شعبه. سایر شعب: فقط کمپین‌های خودشان.
$__branch = dash_active_branch_id();
$__row = dash_load_branch($__branch);
if ($__row && (int)$__row['is_hq'] === 1) {
    $campaigns = $pdo->query(
        "SELECT c.*, b.name AS branch_name, b.slug AS branch_slug
           FROM campaigns c LEFT JOIN branches b ON b.id = c.branch_id
          ORDER BY c.id DESC"
    )->fetchAll();
} else {
    $stmt = $pdo->prepare(
        "SELECT c.*, b.name AS branch_name, b.slug AS branch_slug
           FROM campaigns c LEFT JOIN branches b ON b.id = c.branch_id
          WHERE c.branch_id = ? ORDER BY c.id DESC"
    );
    $stmt->execute([$__branch]);
    $campaigns = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>لیست کمپین‌ها | پنل مکسا</title>
<!-- اعمالِ تم پیش از رنگ‌آمیزی تا از پرشِ نور→تاریک جلوگیری شود (کلید مشترک: maxa-theme) -->
<script>(function(){try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/panel.css">
<style>
    /* استایل‌های ویژه‌ی این صفحه (توکن‌ها و کامپوننت‌های مشترک از panel.css می‌آیند) */
    .container { max-width: 1200px; }

    .card {
        padding: 0;
        margin-bottom: 0;
        overflow: hidden;
        cursor: pointer;
        transition: transform .4s cubic-bezier(.165,.84,.44,1), box-shadow .4s, filter .4s, opacity .4s, border-color .3s;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    /* کمپین‌های غیرفعال خاکستری می‌شوند */
    .card.disabled-campaign { filter: grayscale(1) contrast(.9); opacity: .75; }

    .card-img-wrapper { position: relative; width: 100%; height: 210px; overflow: hidden; background: var(--primary-08); }
    .card-img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }

    /* بج وضعیت روی تصویر */
    .status-badge {
        position: absolute; top: 15px; right: 15px; color: #fff;
        padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;
        backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); z-index: 2;
    }
    .status-badge.active { background: rgba(22,163,122,.92); }
    .status-badge.inactive { background: rgba(224,85,107,.92); }

    /* دکمه‌ی تغییر وضعیت در بالای کارت */
    .toggle-status-form { position: absolute; top: 15px; left: 15px; z-index: 5; }
    .btn-toggle-status {
        background: rgba(255,255,255,.92); border: none; color: var(--color-text);
        padding: 7px 12px; border-radius: 9px; font-size: 11px; font-family: inherit;
        cursor: pointer; font-weight: 700; box-shadow: 0 4px 10px rgba(0,0,0,.1);
        transition: transform .2s, background .2s; display: flex; align-items: center; gap: 6px;
    }
    .btn-toggle-status .ic { width: 14px; height: 14px; }
    [data-theme="dark"] .btn-toggle-status { background: rgba(25,35,42,.92); color: #fff; }
    .btn-toggle-status:hover { transform: scale(1.05); background: #fff; }
    [data-theme="dark"] .btn-toggle-status:hover { background: #223038; }

    .card-body { padding: 24px; display: flex; flex-direction: column; flex-grow: 1; }
    .card-title { margin: 0 0 12px; color: var(--color-primary); font-size: 18px; font-weight: 800; line-height: 1.5; }
    [data-theme="dark"] .card-title { color: var(--color-primary-light); }

    .card-desc {
        font-size: 13px; line-height: 1.8; color: var(--color-muted); margin-bottom: 15px;
        position: relative; max-height: 48px; overflow: hidden;
        transition: max-height .5s cubic-bezier(.25,1,.5,1);
    }
    .card-desc::after {
        content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 25px;
        background: linear-gradient(transparent, var(--color-surface));
        transition: opacity .3s ease; pointer-events: none;
    }

    .progress-container { margin-top: auto; padding-top: 15px; }
    .progress-container .progress { margin: 10px 0; }
    .funding-stats { display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; }

    .btn-support {
        width: 100%; padding: 12px; border: 2px solid var(--color-secondary);
        background: none; color: var(--color-secondary-dark); font-weight: 800;
        border-radius: 12px; cursor: pointer; transition: .3s; margin-top: 20px; font-size: 14px;
        display: flex; align-items: center; justify-content: center; gap: 8px; font-family: inherit;
    }
    .btn-support .ic { width: 16px; height: 16px; }
    .btn-support:hover { background: var(--color-secondary); color: #fff; box-shadow: 0 5px 15px rgba(244,166,30,.3); }

    .disabled-campaign .btn-support { border-color: var(--color-border); color: var(--color-muted); cursor: not-allowed; }
    .disabled-campaign .btn-support:hover { background: none; color: var(--color-muted); box-shadow: none; }

    @media (min-width: 1024px) {
        .card:not(.disabled-campaign):hover { transform: translateY(-8px); box-shadow: var(--shadow-md); border-color: var(--color-primary); }
        .card:not(.disabled-campaign):hover .card-img { transform: scale(1.06); }
        .card:hover .card-desc { max-height: 300px; }
        .card:hover .card-desc::after { opacity: 0; }
        .mobile-indicator { display: none; }
    }

    @media (max-width: 1023px) {
        .card-grid { grid-template-columns: 1fr; gap: 20px; }
        .mobile-indicator {
            position: absolute; bottom: 15px; left: 15px; background: var(--primary-08);
            color: var(--color-muted); padding: 4px 8px; border-radius: 6px; font-size: 11px;
            display: flex; align-items: center; gap: 4px;
        }
        .mobile-indicator .ic { width: 13px; height: 13px; }
        .card.expanded .card-desc { max-height: 400px; }
        .card.expanded .card-desc::after { opacity: 0; }
    }
</style>
</head>
<body>

<div class="container">
    <div class="page-head">
        <div class="ph-ic">
            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 21V4"/><path d="M5 4h12l-2.5 4L17 12H5"/></svg>
        </div>
        <div class="ph-tx">
            <h1>کمپین‌های حمایتی</h1>
            <p>کمپین‌های جمع‌آوری کمک را مدیریت و وضعیت نمایش‌شان را تنظیم کنید.</p>
        </div>
        <div class="ph-actions">
            <a class="btn btn-primary" href="campaign-create.php">
                <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                کمپین جدید
            </a>
        </div>
    </div>

    <?php if (empty($campaigns)): ?>
        <div class="card"><div class="empty">هیچ کمپینی ثبت نشده است.</div></div>
    <?php endif; ?>

    <div class="card-grid">
        <?php foreach($campaigns as $c): 
            // تعیین وضعیت فعال بودن (اگر ستون در دیتابیس نبود یا ۱ بود، فعال است)
            $is_active = isset($c['is_active']) ? (int)$c['is_active'] : 1;
            
            $target = (float)$c['target_amount'];
            $collected = (float)$c['collected_amount'];
            $progress = ($target > 0) ? ($collected / $target) * 100 : 0;
        ?>
        <div class="card <?= $is_active === 0 ? 'disabled-campaign' : '' ?>" onclick="this.classList.toggle('expanded')">
            
            <?php if($is_active === 1): ?>
                <div class="status-badge active">فعال</div>
            <?php else: ?>
                <div class="status-badge inactive">غیرفعال</div>
            <?php endif; ?>

            <form method="POST" class="toggle-status-form" onclick="event.stopPropagation();">
                <input type="hidden" name="toggle_id" value="<?= $c['id'] ?>">
                <input type="hidden" name="current_status" value="<?= $is_active ?>">
                <button type="submit" class="btn-toggle-status">
                    <?php if($is_active === 1): ?>
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><line x1="5.6" y1="5.6" x2="18.4" y2="18.4"/></svg> غیرفعال‌سازی
                    <?php else: ?>
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> فعال‌سازی
                    <?php endif; ?>
                </button>
            </form>

            <div class="card-img-wrapper">
<?php $ph = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMjUwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZWVmMWYyIi8+PGcgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYjljMmM2IiBzdHJva2Utd2lkdGg9IjMiPjxyZWN0IHg9IjE1OCIgeT0iOTIiIHdpZHRoPSI4NCIgaGVpZ2h0PSI2NCIgcng9IjciLz48Y2lyY2xlIGN4PSIxODIiIGN5PSIxMTYiIHI9IjkiLz48cGF0aCBkPSJNMTYyIDE1MGwyNC0yMiAxNSAxMyAxOS0xNyAxOCAxNnYxMnoiLz48L2c+PC9zdmc+'; ?>
                <img src="<?= !empty($c['image_url']) ? htmlspecialchars($c['image_url']) : $ph ?>" class="card-img" onerror="this.onerror=null;this.src='<?= $ph ?>'">
            </div>
            
            <div class="card-body">
                <h4 class="card-title"><?= htmlspecialchars($c['title']) ?></h4>
                
                <div class="card-desc">
                    <?= nl2br(htmlspecialchars($c['description'])) ?>
                </div>

                <div class="progress-container">
                    <div class="funding-stats">
                        <span>هدف: <?= number_format($target) ?> تومان</span>
                        <span style="color: var(--color-secondary-dark); font-size: 14px;"><?= round($progress) ?>%</span>
                    </div>
                    <div class="progress">
                        <i style="width: <?= min($progress, 100) ?>%"></i>
                    </div>
                    <div class="funding-stats" style="color: var(--color-muted); font-size: 11px; font-weight: normal; margin-top: 8px;">
                        <span>جمع‌آوری شده: <?= number_format($collected) ?> تومان</span>
                    </div>
                </div>

                <button class="btn-support" <?= $is_active === 0 ? 'disabled' : '' ?>>
                    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg> حمایت مالی مستقیم
                </button>

                <div class="mobile-indicator"><svg class="ic" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/></svg></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
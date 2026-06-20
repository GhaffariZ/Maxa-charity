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
<title>لیست کمپین‌ها</title>
<!-- تم دارک/لایت از «داشبورد مدیریت» تبعیت می‌کند (کلید مشترک: maxa-theme) -->
<script>
(function(){
  function applyMaxaTheme(){
    var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
    if(d) document.documentElement.setAttribute('data-theme','dark'); else document.documentElement.removeAttribute('data-theme');
  }
  applyMaxaTheme();
  window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });
})();
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    :root { 
        --primary-color: #007D75; 
        --secondary-color: #F79F1F; 
        --bg-color: #f4f7f6; 
        --card-bg: #ffffff; 
        --text-main: #333; 
        --text-muted: #777; 
        --border-light: rgba(0,0,0,0.06);
        --success-color: #2ed573;
        --danger-color: #ff4757;
    }
    [data-theme="dark"] { 
        --bg-color: #121212; 
        --card-bg: #1e1e1e; 
        --text-main: #eee; 
        --text-muted: #aaa; 
        --border-light: rgba(255,255,255,0.08);
    }

    body { 
        background-color: var(--bg-color); 
        color: var(--text-main); 
        font-family: Tahoma, sans-serif; 
        padding: 40px 20px; 
        margin: 0;
    }

    .container { max-width: 1200px; margin: 0 auto; }

    .page-header {
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--primary-color);
    }
    .page-header h2 { margin: 0; font-size: 22px; font-weight: bold; }

    .campaign-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
        gap: 30px; 
    }

    .card {
        background: var(--card-bg); 
        border-radius: 20px; 
        border: 1px solid var(--border-light);
        overflow: hidden; 
        cursor: pointer; 
        transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s, filter 0.4s, opacity 0.4s;
        display: flex; 
        flex-direction: column; 
        position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }

    /* استایل اختصاصی برای کمپین‌های غیرفعال (خاکستری شدن) */
    .card.disabled-campaign {
        filter: grayscale(1) contrast(0.9);
        opacity: 0.75;
    }

    .card-img-wrapper {
        position: relative;
        width: 100%;
        height: 210px;
        overflow: hidden;
        background: #eee;
    }

    .card-img { 
        width: 100%; 
        height: 100%; 
        object-fit: cover; 
        transition: transform 0.5s ease;
    }

    /* بج وضعیت فعال / غیرفعال */
    .status-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        color: white;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: bold;
        backdrop-filter: blur(5px);
        z-index: 2;
    }
    .status-badge.active { background: rgba(46, 213, 115, 0.9); }
    .status-badge.inactive { background: rgba(255, 71, 87, 0.9); }

    /* دکمه مدیریت وضعیت در بالای کارت */
    .toggle-status-form {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 5;
    }
    .btn-toggle-status {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        color: #333;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-family: Tahoma;
        cursor: pointer;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: 0.2s;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    [data-theme="dark"] .btn-toggle-status { background: rgba(30, 30, 30, 0.9); color: #fff; }
    .btn-toggle-status:hover { transform: scale(1.05); background: #fff; }
    [data-theme="dark"] .btn-toggle-status:hover { background: #2a2a2a; }

    .card-body { 
        padding: 24px; 
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .card-title { 
        margin: 0 0 12px 0; 
        color: var(--primary-color); 
        font-size: 18px; 
        font-weight: bold;
        line-height: 1.5;
    }

    .card-desc {
    font-size: 13px; 
    line-height: 1.8; 
    color: var(--text-muted);
    margin-bottom: 15px;
    position: relative;
    max-height: 48px; /* ارتفاع دقیق برای نشان دادن حدود ۲ خط */
    overflow: hidden;
    transition: max-height 0.5s cubic-bezier(0.25, 1, 0.5, 1); /* انیمیشن نرم روان */
}
.card-desc::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 25px;
    background: linear-gradient(transparent, var(--card-bg));
    transition: opacity 0.3s ease;
    pointer-events: none; /* جلوگیری از اختلال در کلیک */
}

    .progress-container {
        margin-top: auto; 
        padding-top: 15px;
    }

    .progress-bar { 
        height: 8px; 
        background: rgba(0,0,0,0.05); 
        border-radius: 10px; 
        margin: 10px 0; 
        overflow: hidden;
    }
    [data-theme="dark"] .progress-bar { background: rgba(255,255,255,0.1); }

    .progress-fill { 
        height: 100%; 
        background: linear-gradient(90deg, var(--secondary-color), #ffb94b); 
        border-radius: 10px; 
        width: 0;
        transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .funding-stats {
        display: flex; 
        justify-content: space-between; 
        font-size: 12px; 
        font-weight: bold;
    }

    .btn-support {
        width: 100%; 
        padding: 12px; 
        border: 2px solid var(--secondary-color);
        background: none; 
        color: var(--secondary-color); 
        font-weight: bold;
        border-radius: 12px; 
        cursor: pointer; 
        transition: 0.3s; 
        margin-top: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-support:hover { 
        background: var(--secondary-color); 
        color: white; 
        box-shadow: 0 5px 15px rgba(247, 159, 31, 0.3);
    }

    /* کمپین غیرفعال دکمه حمایتش قفل می‌شود */
    .disabled-campaign .btn-support {
        border-color: #aaa;
        color: #aaa;
        cursor: not-allowed;
    }
    .disabled-campaign .btn-support:hover { background: none; color: #aaa; box-shadow: none; }

 @media (min-width: 1024px) {
    .card:not(.disabled-campaign):hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 35px rgba(0,0,0,0.08);
        border-color: var(--primary-color);
    }
    
    .card:not(.disabled-campaign):hover .card-img {
        transform: scale(1.06);
    }

    /* انیمیشن باز شدن متن روی دسکتاپ */
    .card:hover .card-desc {
        max-height: 300px; /* مقداری بیشتر از ارتفاع متن برای جا شدن کامل */
    }

    /* حذف افکت محوشدگی هنگام باز شدن */
    .card:hover .card-desc::after {
        opacity: 0;
    }
    
    .mobile-indicator { display: none; }
}

    @media (max-width: 1023px) {
    body { padding: 20px 10px; }
    .campaign-grid { grid-template-columns: 1fr; gap: 20px; }
    
    .mobile-indicator { 
        position: absolute; 
        bottom: 15px; 
        left: 15px; 
        background: rgba(0,0,0,0.05); 
        color: var(--text-muted); 
        padding: 4px 8px; 
        border-radius: 6px; 
        font-size: 11px; 
    }

    /* کلاس زنده باز شدن روی موبایل */
    .card.expanded .card-desc { 
        max-height: 400px; 
    }

    /* حذف افکت محوشدگی در موبایل */
    .card.expanded .card-desc::after {
        opacity: 0;
    }
}
</style>
</head>
<body>

<?php include "./components/back/back.php" ?>

<div class="container">
    <div class="page-header">
        <i class="fas fa-layer-group fa-lg"></i>
        <h2>کمپین‌های حمایتی </h2>
    </div>

    <div class="campaign-grid">
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
                        <i class="fas fa-ban" style="color: var(--danger-color);"></i> غیرفعال‌سازی
                    <?php else: ?>
                        <i class="fas fa-check" style="color: var(--success-color);"></i> فعال‌سازی
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
                        <span style="color: var(--secondary-color); font-size: 14px;"><?= round($progress) ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= min($progress, 100) ?>%"></div>
                    </div>
                    <div class="funding-stats" style="color: var(--text-muted); font-size: 11px; font-weight: normal; margin-top: 4px;">
                        <span>جمع‌آوری شده: <?= number_format($collected) ?> تومان</span>
                    </div>
                </div>

                <button class="btn-support" <?= $is_active === 0 ? 'disabled' : '' ?>>
                    <i class="fas fa-heart"></i> حمایت مالی مستقیم
                </button>
                
                <div class="mobile-indicator"><i class="fas fa-ellipsis-h"></i></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
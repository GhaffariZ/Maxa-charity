<?php
require_once __DIR__ . '/_guard.php';
dash_require('campaigns');

if (!isset($pdo) || !($pdo instanceof PDO)) {
    $pdo = dash_pdo();
}

// عملیات تغییر وضعیت کمپین (فعال / غیرفعال سازی)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $campaign_id = (int)$_POST['toggle_id'];
    $current_status = (int)$_POST['current_status'];
    $new_status = $current_status === 1 ? 0 : 1;
    
    $__branch = dash_active_branch_id();
    if (dash_is_hq_view()) {
        $stmt = $pdo->prepare("UPDATE campaigns SET is_active = ? WHERE id = ?");
        $stmt->execute([$new_status, $campaign_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE campaigns SET is_active = ? WHERE id = ? AND branch_id = ?");
        $stmt->execute([$new_status, $campaign_id, $__branch]);
    }

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
<style>
    @font-face {
        font-family: 'Vazirmatn';
        src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations');
        font-weight: 100 900;
        font-style: normal;
        font-display: swap;
    }

    :root { 
        --primary-color: #007D75; 
        --primary-hover: #006159;
        --secondary-color: #F79F1F; 
        --bg-color: #f4f7f6; 
        --card-bg: #ffffff; 
        --text-main: #333333; 
        --text-muted: #6b7674; 
        --border-light: rgba(0,0,0,0.07);
        --success-color: #2ed573;
        --danger-color: #ff4757;
    }
    [data-theme="dark"] { 
        --bg-color: #121212; 
        --card-bg: #1e1e1e; 
        --text-main: #eeeeee; 
        --text-muted: #aaaaaa; 
        --border-light: rgba(255,255,255,0.08);
    }

    * { box-sizing: border-box; }
    body { 
        background-color: var(--bg-color); 
        color: var(--text-main); 
        font-family: 'Vazirmatn', Tahoma, sans-serif; 
        padding: 30px 20px; 
        margin: 0;
        line-height: 1.6;
    }

    .container { max-width: 1200px; margin: 0 auto; }

    .page-header {
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }
    .page-header-title {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--primary-color);
    }
    .page-header h2 { margin: 0; font-size: 22px; font-weight: 800; }

    .btn-create-campaign {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary-color);
        color: #ffffff;
        padding: 10px 18px;
        border-radius: 12px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(0, 125, 117, 0.2);
    }
    .btn-create-campaign:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 125, 117, 0.3);
    }

    .campaign-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); 
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
        background: #eaeaea;
    }
    [data-theme="dark"] .card-img-wrapper { background: #252525; }

    .card-img { 
        width: 100%; 
        height: 100%; 
        object-fit: cover; 
        transition: transform 0.5s ease;
        display: block;
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
        background: rgba(255, 255, 255, 0.92);
        border: none;
        color: #333;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-family: inherit;
        cursor: pointer;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    [data-theme="dark"] .btn-toggle-status { background: rgba(30, 30, 30, 0.92); color: #fff; }
    .btn-toggle-status:hover { transform: scale(1.05); background: #fff; }
    [data-theme="dark"] .btn-toggle-status:hover { background: #2a2a2a; }

    .card-body { 
        padding: 24px; 
        display: flex; 
        flex-direction: column; 
        flex-grow: 1; 
    }

    .card-title { 
        margin: 0 0 10px 0; 
        color: var(--primary-color); 
        font-size: 18px; 
        font-weight: 800; 
        line-height: 1.5;
    }

    .card-desc {
        font-size: 13px; 
        line-height: 1.8; 
        color: var(--text-muted);
        margin-bottom: 15px;
        position: relative;
        max-height: 48px; /* ارتفاع برای حدود ۲ خط */
        overflow: hidden;
        transition: max-height 0.4s ease;
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
        pointer-events: none;
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

    /* نوار دکمه‌های عملیاتی زیر کارت */
    .card-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-edit {
        flex: 1;
        padding: 10px 14px;
        background: var(--primary-color);
        color: #ffffff;
        font-weight: 700;
        border-radius: 12px;
        text-decoration: none;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    .btn-edit:hover {
        background: var(--primary-hover);
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 125, 117, 0.25);
    }

    .btn-support {
        flex: 1;
        padding: 10px 12px; 
        border: 1.5px solid var(--secondary-color); 
        background: none; 
        color: var(--secondary-color); 
        font-weight: 700;
        border-radius: 12px; 
        cursor: pointer; 
        transition: all 0.2s; 
        font-size: 13px;
        font-family: inherit;
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        gap: 6px;
    }
    .btn-support:hover:not(:disabled) { 
        background: var(--secondary-color); 
        color: white; 
        box-shadow: 0 4px 12px rgba(247, 159, 31, 0.3);
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

        .card:hover .card-desc {
            max-height: 320px;
        }
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
            bottom: 12px; 
            left: 12px; 
            background: rgba(0,0,0,0.05); 
            color: var(--text-muted); 
            padding: 3px 6px; 
            border-radius: 6px; 
            font-size: 11px; 
        }

        .card.expanded .card-desc { 
            max-height: 400px; 
        }
        .card.expanded .card-desc::after {
            opacity: 0;
        }
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: var(--card-bg);
        border-radius: 20px;
        border: 1px dashed var(--border-light);
        grid-column: 1 / -1;
    }
    .empty-state h3 {
        color: var(--text-main);
        font-size: 18px;
        margin-bottom: 8px;
    }
    .empty-state p {
        color: var(--text-muted);
        font-size: 14px;
        margin-bottom: 20px;
    }
</style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <div class="page-header-title">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 12.5-8.58 3.91a2 2 0 0 1-1.66 0L2.6 12.5"/><path d="m22 17.5-8.58 3.91a2 2 0 0 1-1.66 0L2.6 17.5"/></svg>
            <h2>کمپین‌های حمایتی</h2>
        </div>
        <a href="campaign-create.php" class="btn-create-campaign">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            ایجاد کمپین جدید
        </a>
    </div>

    <div class="campaign-grid">
        <?php if (empty($campaigns)): ?>
            <div class="empty-state">
                <h3>هیچ کمپینی ثبت نشده است</h3>
                <p>در حال حاضر کمپینی برای این شعبه وجود ندارد. با کلیک روی دکمه زیر اولین کمپین را بسازید.</p>
                <a href="campaign-create.php" class="btn-create-campaign" style="display:inline-flex;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    ایجاد کمپین جدید
                </a>
            </div>
        <?php else: ?>
        <?php foreach($campaigns as $c): 
            $is_active = isset($c['is_active']) ? (int)$c['is_active'] : 1;
            
            $target = (float)$c['target_amount'];
            $collected = (float)$c['collected_amount'];
            $progress = ($target > 0) ? ($collected / $target) * 100 : 0;
            $ph = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMjUwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZWVmMWYyIi8+PGcgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYjljMmM2IiBzdHJva2Utd2lkdGg9IjMiPjxyZWN0IHg9IjE1OCIgeT0iOTIiIHdpZHRoPSI4NCIgaGVpZ2h0PSI2NCIgcng9IjciLz48Y2lyY2xlIGN4PSIxODIiIGN5PSIxMTYiIHI9IjkiLz48cGF0aCBkPSJNMTYyIDE1MGwyNC0yMiAxNSAxMyAxOS0xNyAxOCAxNnYxMnoiLz48L2c+PC9zdmc+';
        ?>
        <div class="card <?= $is_active === 0 ? 'disabled-campaign' : '' ?>" onclick="this.classList.toggle('expanded')">
            
            <?php if($is_active === 1): ?>
                <div class="status-badge active">فعال</div>
            <?php else: ?>
                <div class="status-badge inactive">غیرفعال</div>
            <?php endif; ?>

            <form method="POST" class="toggle-status-form" onclick="event.stopPropagation();">
                <input type="hidden" name="toggle_id" value="<?= (int)$c['id'] ?>">
                <input type="hidden" name="current_status" value="<?= $is_active ?>">
                <button type="submit" class="btn-toggle-status" title="<?= $is_active === 1 ? 'غیرفعال‌سازی این کمپین' : 'فعال‌سازی مجدد این کمپین' ?>">
                    <?php if($is_active === 1): ?>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--danger-color)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        غیرفعال‌سازی
                    <?php else: ?>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--success-color)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        فعال‌سازی
                    <?php endif; ?>
                </button>
            </form>

            <div class="card-img-wrapper">
                <img src="<?= !empty($c['image_url']) ? htmlspecialchars($c['image_url']) : $ph ?>" class="card-img" loading="lazy" alt="<?= htmlspecialchars($c['title']) ?>" onerror="this.onerror=null;this.src='<?= $ph ?>'">
            </div>
            
            <div class="card-body">
                <h4 class="card-title"><?= htmlspecialchars($c['title']) ?></h4>
                <?php if (dash_is_hq_view() && !empty($c['branch_name'])): ?>
                  <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;color:#007b7a;background:rgba(0,123,122,.10);padding:3px 10px;border-radius:99px;margin-bottom:8px;align-self:flex-start;"><?= iconoir('building', '', 13) ?> <?= htmlspecialchars($c['branch_name']) ?></span>
                <?php endif; ?>

                <div class="card-desc">
                    <?= nl2br(htmlspecialchars($c['description'] ?? '')) ?>
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

                <div class="card-actions" onclick="event.stopPropagation();">
                    <a href="campaign-edit.php?id=<?= (int)$c['id'] ?>" class="btn-edit" title="ویرایش اطلاعات این کمپین" onclick="event.stopPropagation();">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        ویرایش کمپین
                    </a>
                    <button type="button" class="btn-support" <?= $is_active === 0 ? 'disabled' : '' ?> onclick="event.stopPropagation();">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        حمایت مالی
                    </button>
                </div>
                
                <div class="mobile-indicator">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><circle cx="5" cy="12" r="2"/></svg>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
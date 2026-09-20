<?php
require_once __DIR__ . '/_guard.php';
dash_require('campaigns');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$__branch = dash_active_branch_id();
$is_hq = dash_is_hq_view();

$campaign = null;
if ($id > 0) {
    if ($is_hq) {
        $stmt = $pdo->prepare(
            "SELECT c.*, b.name AS branch_name, b.slug AS branch_slug
               FROM campaigns c
          LEFT JOIN branches b ON b.id = c.branch_id
              WHERE c.id = ?
              LIMIT 1"
        );
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare(
            "SELECT c.*, b.name AS branch_name, b.slug AS branch_slug
               FROM campaigns c
          LEFT JOIN branches b ON b.id = c.branch_id
              WHERE c.id = ? AND c.branch_id = ?
              LIMIT 1"
        );
        $stmt->execute([$id, $__branch]);
    }
    $campaign = $stmt->fetch();
}

$ph = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMjUwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZWVmMWYyIi8+PGcgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYjljMmM2IiBzdHJva2Utd2lkdGg9IjMiPjxyZWN0IHg9IjE1OCIgeT0iOTIiIHdpZHRoPSI4NCIgaGVpZ2h0PSI2NCIgcng9IjciLz48Y2lyY2xlIGN4PSIxODIiIGN5PSIxMTYiIHI9IjkiLz48cGF0aCBkPSJNMTYyIDE1MGwyNC0yMiAxNSAxMyAxOS0xNyAxOCAxNnYxMnoiLz48L2c+PC9zdmc+';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $campaign ? 'ویرایش کمپین: ' . htmlspecialchars($campaign['title']) : 'ویرایش کمپین' ?></title>
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
        --panel-bg: #ffffff;
        --text-color: #333333;
        --text-muted: #6b7674;
        --border-color: #e0e0e0;
        --card-bg: #ffffff;
        --success-color: #2ed573;
        --danger-color: #ff4757;
    }
    [data-theme="dark"] {
        --bg-color: #121212;
        --panel-bg: #1e1e1e;
        --text-color: #eeeeee;
        --text-muted: #aaaaaa;
        --border-color: #333333;
        --card-bg: #1e1e1e;
    }

    * { box-sizing: border-box; }
    body {
        background-color: var(--bg-color);
        color: var(--text-color);
        font-family: 'Vazirmatn', Tahoma, sans-serif;
        margin: 0;
        padding: 24px 20px;
        line-height: 1.6;
    }

    .container { max-width: 1100px; margin: 0 auto; }

    .header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .header-title-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .header-title-group h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .header-badges {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }
    .badge-branch {
        background: rgba(0, 125, 117, 0.1);
        color: var(--primary-color);
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 99px;
    }
    .badge-code {
        background: rgba(247, 159, 31, 0.12);
        color: #d97706;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 99px;
        font-family: monospace;
        letter-spacing: 0.5px;
    }
    [data-theme="dark"] .badge-code { color: #f59e0b; }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        color: var(--text-color);
        padding: 8px 16px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        transition: all 0.2s;
    }
    .btn-back:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        transform: translateX(-3px);
    }

    .main-card {
        background: var(--panel-bg);
        border-radius: 20px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    }
    .panel-header {
        background: var(--primary-color);
        color: #ffffff;
        padding: 18px 24px;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .panel-body {
        display: flex;
        gap: 36px;
        padding: 30px;
    }

    .form-side { flex: 1; min-width: 0; }
    .preview-side { width: 340px; flex-shrink: 0; }

    .form-group { margin-bottom: 22px; }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
        color: var(--primary-color);
        font-size: 14px;
    }
    .input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-color);
        color: var(--text-color);
        font-family: inherit;
        font-size: 14px;
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.15);
    }
    textarea.input { resize: vertical; min-height: 120px; }

    .current-img-box {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 12px;
        background: var(--bg-color);
        border-radius: 12px;
        border: 1px dashed var(--border-color);
        margin-bottom: 10px;
    }
    .current-img-thumb {
        width: 70px;
        height: 52px;
        border-radius: 8px;
        object-fit: cover;
        background: #ddd;
    }
    .current-img-info {
        font-size: 12px;
        color: var(--text-muted);
    }

    .preview-sticky { position: sticky; top: 20px; }
    .preview-label {
        text-align: center;
        display: block;
        margin-bottom: 12px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-muted);
    }
    .live-card {
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    }
    .live-img-wrapper {
        width: 100%;
        height: 190px;
        background: #eaeaea;
        overflow: hidden;
        position: relative;
    }
    [data-theme="dark"] .live-img-wrapper { background: #252525; }
    .live-img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .live-content { padding: 20px; }
    .live-title {
        margin: 0 0 12px 0;
        color: var(--primary-color);
        font-size: 16px;
        font-weight: 700;
        line-height: 1.5;
        min-height: 48px;
    }
    .live-progress {
        height: 7px;
        background: rgba(0,0,0,0.06);
        border-radius: 10px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    [data-theme="dark"] .live-progress { background: rgba(255,255,255,0.1); }
    .live-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--secondary-color), #ffb94b);
        border-radius: 10px;
        width: 0%;
        transition: width 0.4s ease;
    }
    .live-stats {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .live-collected {
        font-size: 11px;
        color: var(--text-muted);
        font-weight: normal;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 25px;
    }
    .btn-submit {
        flex: 1;
        background: var(--primary-color);
        color: #ffffff;
        border: none;
        padding: 14px 20px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 700;
        font-size: 15px;
        font-family: inherit;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-submit:hover:not(:disabled) {
        background: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 125, 117, 0.25);
    }
    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-cancel {
        background: transparent;
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        padding: 14px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .btn-cancel:hover {
        background: var(--bg-color);
        color: var(--text-color);
    }

    /* Toast Notification */
    .toast-container { position: fixed; bottom: 25px; left: 25px; z-index: 99999; }
    .toast {
        background: var(--panel-bg);
        color: var(--text-color);
        padding: 14px 22px;
        border-radius: 14px;
        box-shadow: 0 10px 35px rgba(0,0,0,0.18);
        margin-top: 10px;
        transform: translateX(-150%);
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border-right: 5px solid var(--primary-color);
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 700;
    }
    .toast.active { transform: translateX(0); }
    .toast.error { border-right-color: var(--danger-color); }

    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .error-card {
        background: var(--panel-bg);
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 60px 30px;
        text-align: center;
        max-width: 600px;
        margin: 40px auto;
    }
    .error-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 20px;
        color: var(--danger-color);
    }
    .error-title {
        font-size: 20px;
        font-weight: 800;
        margin: 0 0 10px 0;
        color: var(--text-color);
    }
    .error-desc {
        color: var(--text-muted);
        font-size: 14px;
        margin-bottom: 25px;
    }

    @media (max-width: 900px) {
        body { padding: 15px 10px; }
        .panel-body { flex-direction: column-reverse; padding: 20px; gap: 24px; }
        .preview-side { width: 100%; }
        .preview-sticky { position: static; margin-bottom: 20px; }
        .form-actions { flex-direction: column; }
    }
</style>
</head>
<body>

<div class="container">
<?php if (!$campaign): ?>
    <div class="error-card">
        <div class="error-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h3 class="error-title">کمپین یافت نشد</h3>
        <p class="error-desc">کمپین درخواستی وجود ندارد یا شما دسترسی لازم برای ویرایش آن در این شعبه را ندارید.</p>
        <a href="campaign-status.php" class="btn-submit" style="display:inline-flex; width:auto; text-decoration:none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            بازگشت به لیست کمپین‌ها
        </a>
    </div>
<?php else: 
    $target = (float)$campaign['target_amount'];
    $collected = (float)$campaign['collected_amount'];
    $progress = ($target > 0) ? ($collected / $target) * 100 : 0;
    $initialImg = !empty($campaign['image_url']) ? $campaign['image_url'] : $ph;
?>
    <div class="header-bar">
        <div class="header-title-group">
            <h2>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                ویرایش کمپین
            </h2>
            <div class="header-badges">
                <?php if (!empty($campaign['branch_name'])): ?>
                    <span class="badge-branch">🏢 <?= htmlspecialchars($campaign['branch_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($campaign['campaign_code'])): ?>
                    <span class="badge-code"><?= htmlspecialchars($campaign['campaign_code']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <a href="campaign-status.php" class="btn-back">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            بازگشت به لیست کمپین‌ها
        </a>
    </div>

    <div class="main-card">
        <div class="panel-header">
            <span>فرم تغییر و به‌روزرسانی مشخصات کمپین</span>
            <span style="font-size:12px; font-weight:normal; opacity:0.9;">کد شناسه: <?= (int)$campaign['id'] ?></span>
        </div>
        
        <div class="panel-body">
            <!-- ستون پیش‌نمایش زنده -->
            <div class="preview-side">
                <div class="preview-sticky">
                    <span class="preview-label">پیش‌نمایش زنده کارت</span>
                    <div class="live-card">
                        <div class="live-img-wrapper">
                            <img src="<?= htmlspecialchars($initialImg) ?>" id="imgPreview" class="live-img" onerror="this.onerror=null;this.src='<?= $ph ?>'">
                        </div>
                        <div class="live-content">
                            <h4 class="live-title" id="titlePreview"><?= htmlspecialchars($campaign['title']) ?></h4>
                            <div class="live-progress">
                                <div class="live-bar" id="barPreview" style="width: <?= min($progress, 100) ?>%;"></div>
                            </div>
                            <div class="live-stats">
                                <span id="targetPreview">هدف: <?= number_format($target) ?> تومان</span>
                                <span style="color: var(--secondary-color);" id="percentPreview"><?= round($progress) ?>٪</span>
                            </div>
                            <div class="live-collected">
                                جمع‌آوری شده: <?= number_format($collected) ?> تومان
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ستون فرم ویرایش -->
            <div class="form-side">
                <form id="campEditForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= (int)$campaign['id'] ?>">

                    <div class="form-group">
                        <label>تصویر شاخص کمپین</label>
                        <?php if (!empty($campaign['image_url'])): ?>
                        <div class="current-img-box">
                            <img src="<?= htmlspecialchars($campaign['image_url']) ?>" class="current-img-thumb" onerror="this.onerror=null;this.src='<?= $ph ?>'">
                            <div class="current-img-info">
                                <strong>تصویر فعلی در سامانه ذخیره است.</strong>
                                <div>برای تغییر، فایل تصویر جدیدی در کادر زیر انتخاب کنید.</div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="featured_image" id="imageInput" class="input" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small style="font-size: 11px; color: var(--text-muted); margin-top: 5px; display: block;">فرمت‌های مجاز: JPG, PNG, WEBP (حداکثر ۵ مگابایت). در صورت خالی ماندن، تصویر قبلی حفظ می‌شود.</small>
                    </div>

                    <div class="form-group">
                        <label for="titleInput">عنوان کمپین</label>
                        <input type="text" name="title" id="titleInput" class="input" value="<?= htmlspecialchars($campaign['title']) ?>" required maxlength="70" placeholder="یک عنوان جذاب برای کمپین بنویسید...">
                    </div>

                    <div class="form-group">
                        <label for="categoryInput">دسته‌بندی کمپین</label>
                        <select name="category" id="categoryInput" class="input" required>
                            <option value="food" <?= $campaign['category'] === 'food' ? 'selected' : '' ?>>غذا و معیشت</option>
                            <option value="drug" <?= $campaign['category'] === 'drug' ? 'selected' : '' ?>>دارو و درمان</option>
                            <option value="education" <?= $campaign['category'] === 'education' ? 'selected' : '' ?>>مهارت‌آموزی و آموزش</option>
                        </select>
                        <small style="font-size: 11px; color: var(--text-muted); margin-top: 5px; display: block;">این دسته در صفحه اصلی و فیلترهای وب‌سایت برای مخاطبان استفاده می‌شود.</small>
                    </div>

                    <div class="form-group">
                        <label for="targetInput">مبلغ مورد نیاز هدف (تومان)</label>
                        <input type="number" name="target_amount" id="targetInput" class="input" value="<?= (int)$campaign['target_amount'] ?>" required min="10000" step="1000" placeholder="مثلاً 50000000">
                    </div>

                    <div class="form-group">
                        <label for="descInput">توضیحات و داستان کمپین</label>
                        <textarea name="description" id="descInput" class="input" rows="7" placeholder="شرح کامل اهداف، چرخه حمایت و نیازهای این کمپین..."><?= htmlspecialchars($campaign['description']) ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit" id="submitBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            ذخیره تغییرات کمپین
                        </button>
                        <a href="campaign-status.php" class="btn-cancel">انصراف</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>

<div class="toast-container" id="toastBox"></div>

<script>
(function(){
    const collectedAmount = <?= isset($campaign['collected_amount']) ? (float)$campaign['collected_amount'] : 0 ?>;
    const initialImgSrc = <?= json_encode($initialImg ?? $ph) ?>;

    const imgInput = document.getElementById('imageInput');
    const imgPreview = document.getElementById('imgPreview');
    const titleInput = document.getElementById('titleInput');
    const titlePreview = document.getElementById('titlePreview');
    const targetInput = document.getElementById('targetInput');
    const targetPreview = document.getElementById('targetPreview');
    const percentPreview = document.getElementById('percentPreview');
    const barPreview = document.getElementById('barPreview');
    const form = document.getElementById('campEditForm');
    const submitBtn = document.getElementById('submitBtn');

    function toPersianDigits(n) {
        return String(n).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    }

    function formatNumber(num) {
        return toPersianDigits(Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, "٬"));
    }

    // پیش‌نمایش تصویر جدید هنگام انتخاب
    if (imgInput) {
        imgInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (imgPreview) imgPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                if (imgPreview) imgPreview.src = initialImgSrc;
            }
        });
    }

    // به‌روزرسانی زنده عنوان در کارت
    if (titleInput && titlePreview) {
        titleInput.addEventListener('input', function() {
            titlePreview.textContent = this.value.trim() || 'عنوان کمپین در این قسمت...';
        });
    }

    // به‌روزرسانی زنده درصد و مبلغ هدف در کارت
    if (targetInput && targetPreview && percentPreview && barPreview) {
        targetInput.addEventListener('input', function() {
            const targetVal = parseFloat(this.value) || 0;
            if (targetVal > 0) {
                targetPreview.textContent = 'هدف: ' + formatNumber(targetVal) + ' تومان';
                const pct = Math.min(Math.round((collectedAmount / targetVal) * 100), 100);
                percentPreview.textContent = toPersianDigits(pct) + '٪';
                barPreview.style.width = pct + '%';
            } else {
                targetPreview.textContent = 'هدف: ۰ تومان';
                percentPreview.textContent = '۰٪';
                barPreview.style.width = '0%';
            }
        });
    }

    // سیستم نوتیفیکیشن اختصاصی
    function notify(msg, isError) {
        const box = document.getElementById('toastBox');
        if (!box) return;
        const toast = document.createElement('div');
        toast.className = 'toast' + (isError ? ' error' : '');
        toast.innerHTML = isError
            ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff4757" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> ' + msg
            : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2ed573" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> ' + msg;
        box.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('active'));
        setTimeout(() => {
            toast.classList.remove('active');
            setTimeout(() => toast.remove(), 400);
        }, 3500);
    }

    // ارسال فرم با fetch
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const originalHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> در حال ذخیره اطلاعات...';

            const formData = new FormData(form);

            fetch('campaign-save.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    notify(data.message || 'کمپین با موفقیت به‌روزرسانی شد.', false);
                    setTimeout(() => {
                        window.location.href = 'campaign-status.php';
                    }, 1200);
                } else {
                    throw new Error(data.message || 'خطا در ذخیره تغییرات.');
                }
            })
            .catch(err => {
                notify(err.message || 'خطا در برقراری ارتباط با سرور.', true);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            });
        });
    }
})();
</script>

</body>
</html>

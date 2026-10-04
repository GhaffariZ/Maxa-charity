<?php
require_once __DIR__ . '/_guard.php';
dash_require('campaigns');

$ph = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMjUwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZWVmMWYyIi8+PGcgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYjljMmM2IiBzdHJva2Utd2lkdGg9IjMiPjxyZWN0IHg9IjE1OCIgeT0iOTIiIHdpZHRoPSI4NCIgaGVpZ2h0PSI2NCIgcng9IjciLz48Y2lyY2xlIGN4PSIxODIiIGN5PSIxMTYiIHI9IjkiLz48cGF0aCBkPSJNMTYyIDE1MGwyNC0yMiAxNSAxMyAxOS0xNyAxOCAxNnYxMnoiLz48L2c+PC9zdmc+';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ایجاد کمپین حمایتی جدید</title>
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
        --panel-bg: #ffffff;
        --text-color: #333333;
        --text-muted: #6b7674;
        --border-color: #e0e0e0;
        --success-color: #2ed573;
        --danger-color: #ff4757;
    }
    [data-theme="dark"] {
        --bg-color: #121212;
        --panel-bg: #1e1e1e;
        --text-color: #eeeeee;
        --text-muted: #aaaaaa;
        --border-color: #333333;
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
        color: white;
        padding: 18px 24px;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* چیدمان دو ستونه */
    .panel-body { display: flex; gap: 36px; padding: 30px; }
    
    .form-side { flex: 1; min-width: 0; }
    .preview-side { width: 340px; flex-shrink: 0; }

    .form-group { margin-bottom: 22px; }
    label {
        display: flex;
        align-items: center;
        gap: 6px;
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

    /* استایل کارت پیش‌نمایش زنده */
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
        width: 15%;
    }
    .live-stats {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .btn-submit {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 14px 20px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 700;
        width: 100%;
        font-size: 15px;
        font-family: inherit;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        margin-top: 15px;
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

    /* بهینه‌سازی موبایل */
    @media (max-width: 900px) {
        body { padding: 15px 10px; }
        .panel-body { flex-direction: column-reverse; padding: 20px; gap: 24px; }
        .preview-side { width: 100%; }
        .preview-sticky { position: static; margin-bottom: 20px; }
    }
</style>
</head>
<body>

<div class="container">
    <div class="header-bar">
        <div class="header-title-group">
            <h2>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                ایجاد کمپین جدید
            </h2>
        </div>
        <a href="campaign-status.php" class="btn-back">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            بازگشت به لیست کمپین‌ها
        </a>
    </div>

    <div class="main-card">
        <div class="panel-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            طراحی و ایجاد کمپین حمایتی جدید
        </div>
        
        <div class="panel-body">
            <div class="preview-side">
                <div class="preview-sticky">
                    <span class="preview-label">پیش‌نمایش زنده کارت</span>
                    <div class="live-card">
                        <div class="live-img-wrapper">
                            <img src="<?= $ph ?>" id="imgPreview" class="live-img" onerror="this.onerror=null;this.src='<?= $ph ?>'">
                        </div>
                        <div class="live-content">
                            <h4 class="live-title" id="titlePreview">عنوان کمپین شما در این قسمت...</h4>
                            <div class="live-progress"><div class="live-bar" id="barPreview"></div></div>
                            <div class="live-stats">
                                <span id="targetPreview">هدف: ۰ تومان</span>
                                <span style="color: var(--secondary-color);" id="percentPreview">۰٪</span>
                            </div>
                            <button style="width: 100%; background: none; border: 1.5px solid var(--secondary-color); color: var(--secondary-color); padding: 8px; border-radius: 8px; font-weight: bold; cursor: default; font-family: inherit; font-size: 13px;" disabled>حمایت مالی</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-side">
                <form id="campForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="imageInput">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            تصویر شاخص کمپین
                        </label>
                        <input type="file" name="featured_image" id="imageInput" class="input" accept="image/jpeg,image/png,image/gif,image/webp" required>
                        <small style="font-size: 11px; color: var(--text-muted); margin-top: 5px; display: block;">تصویر با کیفیت مطلوب برای کارت آپلود کنید (حداکثر ۵ مگابایت).</small>
                    </div>

                    <div class="form-group">
                        <label for="titleInput">عنوان کمپین</label>
                        <input type="text" name="title" id="titleInput" class="input" placeholder="یک عنوان جذاب بنویسید..." required maxlength="70">
                    </div>

                    <div class="form-group">
                        <label for="categoryInput">دسته‌بندی کمپین</label>
                        <select name="category" id="categoryInput" class="input" required>
                            <option value="food">غذا و معیشت</option>
                            <option value="drug">دارو و درمان</option>
                            <option value="education">مهارت‌آموزی و آموزش</option>
                        </select>
                        <small style="font-size: 11px; color: var(--text-muted); margin-top: 5px; display: block;">این دسته در صفحه کاربران برای فیلتر کردن کمپین‌ها استفاده می‌شود.</small>
                    </div>

                    <div class="form-group">
                        <label for="targetInput">مبلغ مورد نیاز هدف (تومان)</label>
                        <input type="number" name="target_amount" id="targetInput" class="input" placeholder="مثلاً 50000000" required min="10000" step="1000">
                    </div>

                    <div class="form-group">
                        <label for="descInput">توضیحات تکمیلی</label>
                        <textarea name="description" id="descInput" class="input" rows="6" placeholder="داستان کمپین و چرخه حمایت را شرح دهید..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>
                        انتشار نهایی کمپین
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="toast-container" id="toastBox"></div>

<script>
(function(){
    const defaultImg = <?= json_encode($ph) ?>;
    const imgInput = document.getElementById('imageInput');
    const imgPreview = document.getElementById('imgPreview');
    const titleInput = document.getElementById('titleInput');
    const titlePreview = document.getElementById('titlePreview');
    const targetInput = document.getElementById('targetInput');
    const targetPreview = document.getElementById('targetPreview');
    const percentPreview = document.getElementById('percentPreview');
    const barPreview = document.getElementById('barPreview');
    const form = document.getElementById('campForm');
    const submitBtn = document.getElementById('submitBtn');

    function toPersianDigits(n) {
        return String(n).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    }

    function formatNumber(num) {
        return toPersianDigits(Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, "٬"));
    }

    // پیش‌نمایش تصویر
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
                if (imgPreview) imgPreview.src = defaultImg;
            }
        });
    }

    // پیش‌نمایش زنده عنوان
    if (titleInput && titlePreview) {
        titleInput.addEventListener('input', function() {
            titlePreview.textContent = this.value.trim() || 'عنوان کمپین شما در این قسمت...';
        });
    }

    // پیش‌نمایش مبلغ هدف
    if (targetInput && targetPreview && percentPreview && barPreview) {
        targetInput.addEventListener('input', function() {
            const targetVal = parseFloat(this.value) || 0;
            if (targetVal > 0) {
                targetPreview.textContent = 'هدف: ' + formatNumber(targetVal) + ' تومان';
                percentPreview.textContent = '۰٪';
                barPreview.style.width = '5%';
            } else {
                targetPreview.textContent = 'هدف: ۰ تومان';
                percentPreview.textContent = '۰٪';
                barPreview.style.width = '0%';
            }
        });
    }

    // نوتیفیکیشن
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

    // ثبت فرم با fetch
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const originalHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> در حال ثبت و انتشار کمپین...';

            const formData = new FormData(form);

            fetch('campaign-save.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    notify(data.message || 'کمپین با موفقیت ایجاد و منتشر شد.', false);
                    setTimeout(() => {
                        window.location.href = 'campaign-status.php';
                    }, 1200);
                } else {
                    throw new Error(data.message || 'خطا در ثبت اطلاعات.');
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

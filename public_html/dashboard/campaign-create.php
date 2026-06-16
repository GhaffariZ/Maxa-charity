<?php require_once __DIR__ . '/_guard.php';
dash_require('campaigns'); ?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ایجاد کمپین حمایتی | پنل مکسا</title>
<!-- اعمالِ تم پیش از رنگ‌آمیزی تا از پرشِ نور→تاریک جلوگیری شود (کلید مشترک: maxa-theme) -->
<script>(function(){try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/panel.css">
<style>
    /* استایل‌های ویژه‌ی این صفحه (توکن‌ها و کامپوننت‌های مشترک از panel.css می‌آیند) */
    .panel-body { display: grid; grid-template-columns: 1fr 320px; gap: 36px; }
    .form-side { min-width: 0; }
    .preview-side { min-width: 0; }
    .preview-sticky { position: sticky; top: 26px; }
    .preview-label { text-align: center; display: block; margin-bottom: 14px; font-weight: 800; font-size: 12.5px; color: var(--color-muted); }

    .live-card { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow-md); }
    .live-img-wrapper { width: 100%; height: 180px; background: var(--primary-08); overflow: hidden; position: relative; }
    .live-img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .live-content { padding: 16px; }
    .live-title { margin: 0 0 12px; color: var(--color-primary); font-size: 16px; font-weight: 800; height: 45px; overflow: hidden; line-height: 1.4; }
    [data-theme="dark"] .live-title { color: var(--color-primary-light); }
    .live-card .progress { margin-bottom: 14px; }
    .live-support { width: 100%; background: none; border: 1.5px solid var(--color-secondary); color: var(--color-secondary-dark); padding: 8px; border-radius: 9px; font-weight: 800; font-family: inherit; cursor: default; }

    /* توستِ این صفحه: کارت‌های روی‌هم‌چیده با ورود از سمت چپ */
    .toast-container { position: fixed; bottom: 20px; left: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
    .ctoast {
        background: var(--color-surface); color: var(--color-text); padding: 13px 18px; border-radius: 12px;
        box-shadow: var(--shadow-lg); border: 1px solid var(--color-border); border-right: 4px solid var(--success);
        display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700;
        transform: translateX(-150%); transition: transform .5s cubic-bezier(.175,.885,.32,1.275);
    }
    .ctoast.active { transform: translateX(0); }
    .ctoast .ic { width: 18px; height: 18px; color: var(--success); flex-shrink: 0; }

    @media (max-width: 900px) {
        .panel-body { grid-template-columns: 1fr; }
        .preview-side { order: -1; }
        .preview-sticky { position: static; margin-bottom: 8px; }
    }
</style>
</head>
<body>
<div class="container wrap">
    <div class="page-head">
        <div class="ph-ic">
            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </div>
        <div class="ph-tx">
            <h1>ایجاد کمپین جدید</h1>
            <p>اطلاعات کمپین را وارد کنید و پیش‌نمایش زنده‌ی کارت آن را ببینید.</p>
        </div>
    </div>

    <div class="card">
        <div class="panel-body">
            <div class="form-side">
                <form id="campForm">
                    <div class="field">
                        <label>تصویر شاخص کمپین</label>
                        <input type="file" name="featured_image" id="imageInput" class="input" accept="image/*" required>
                        <div class="sub">تصویر به صورت خودکار برای کارت کراپ می‌شود.</div>
                    </div>

                    <div class="field">
                        <label>عنوان کمپین</label>
                        <input type="text" name="title" id="titleInput" class="input" placeholder="یک عنوان جذاب بنویسید..." required maxlength="70">
                    </div>

                    <div class="field">
                        <label>دسته‌بندی کمپین</label>
                        <select name="category" id="categoryInput" class="input" required>
                            <option value="food">غذا</option>
                            <option value="drug">دارو</option>
                            <option value="education">مهارت آموزی</option>
                        </select>
                        <div class="sub">این دسته در صفحه کاربران برای فیلتر کردن کمپین‌ها استفاده می‌شود.</div>
                    </div>

                    <div class="field">
                        <label>توضیحات تکمیلی</label>
                        <textarea name="description" class="input" rows="6" placeholder="داستان کمپین و چرخه حمایت را شرح دهید..."></textarea>
                    </div>

                    <div class="field">
                        <label>مبلغ مورد نیاز (تومان)</label>
                        <input type="number" name="target_amount" class="input" placeholder="مثلا 10000000" required>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitBtn" style="width:100%">
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 16.5 3 21l4.5-1.5"/><path d="M15 5s4 1 6 3-1 6-3 6c0 0-1.5-3-4.5-6S12 5 15 5Z"/><path d="M9 12s-3 .5-4 2 1 4 1 4 .5-2.5 2-4"/></svg>
                        انتشار نهایی کمپین
                    </button>
                </form>
            </div>

            <div class="preview-side">
                <div class="preview-sticky">
                    <span class="preview-label">پیش‌نمایش زنده کارت</span>
                    <div class="live-card">
                        <div class="live-img-wrapper">
                            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMjUwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZWVmMWYyIi8+PGcgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYjljMmM2IiBzdHJva2Utd2lkdGg9IjMiPjxyZWN0IHg9IjE1OCIgeT0iOTIiIHdpZHRoPSI4NCIgaGVpZ2h0PSI2NCIgcng9IjciLz48Y2lyY2xlIGN4PSIxODIiIGN5PSIxMTYiIHI9IjkiLz48cGF0aCBkPSJNMTYyIDE1MGwyNC0yMiAxNSAxMyAxOS0xNyAxOCAxNnYxMnoiLz48L2c+PC9zdmc+" id="imgPreview" class="live-img" onerror="this.onerror=null;this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMjUwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZWVmMWYyIi8+PGcgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYjljMmM2IiBzdHJva2Utd2lkdGg9IjMiPjxyZWN0IHg9IjE1OCIgeT0iOTIiIHdpZHRoPSI4NCIgaGVpZ2h0PSI2NCIgcng9IjciLz48Y2lyY2xlIGN4PSIxODIiIGN5PSIxMTYiIHI9IjkiLz48cGF0aCBkPSJNMTYyIDE1MGwyNC0yMiAxNSAxMyAxOS0xNyAxOCAxNnYxMnoiLz48L2c+PC9zdmc+'">
                        </div>
                        <div class="live-content">
                            <h4 class="live-title" id="titlePreview">عنوان کمپین شما در این قسمت...</h4>
                            <div class="progress"><i style="width:35%"></i></div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; margin-bottom: 12px;">
                                <span>هدف: ۵۰,۰۰۰,۰۰۰</span>
                                <span style="color: var(--color-secondary-dark);">۳۵٪</span>
                            </div>
                            <button class="live-support" disabled>حمایت مالی</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="toast-container" id="toastBox"></div>

<script src="assets/vendor/libs/jquery/jquery.js"></script>
<script>
// پیش‌نمایش تصویر بلافاصله پس از انتخاب
$('#imageInput').on('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#imgPreview').attr('src', e.target.result);
        }
        reader.readAsDataURL(file);
    }
});

// پیش‌نمایش زنده عنوان
$('#titleInput').on('input', function() {
    $('#titlePreview').text($(this).val() || 'عنوان کمپین شما در این قسمت...');
});

// نوتیفیکیشن اختصاصی
var CHECK_SVG = '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
function notify(msg) {
    const id = 't' + Date.now();
    $('#toastBox').append(`<div class="ctoast active" id="${id}">${CHECK_SVG}<span>${msg}</span></div>`);
    setTimeout(() => {
        $(`#${id}`).removeClass('active');
        setTimeout(() => $(`#${id}`).remove(), 500);
    }, 4000);
}

var ROCKET_SVG = '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5 3 21l4.5-1.5"/><path d="M15 5s4 1 6 3-1 6-3 6c0 0-1.5-3-4.5-6S12 5 15 5Z"/><path d="M9 12s-3 .5-4 2 1 4 1 4 .5-2.5 2-4"/></svg>';
var SPIN_SVG = '<svg class="ic spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M21 12a9 9 0 1 1-6.2-8.6"/></svg>';

$('#campForm').on('submit', function(e) {
    e.preventDefault();
    $('#submitBtn').prop('disabled', true).html(SPIN_SVG + ' در حال ثبت...');

    $.ajax({
        url: 'campaign-save.php',
        type: 'POST',
        data: new FormData(this),
        contentType: false, processData: false,
        success: function() {
            notify('کمپین با موفقیت ایجاد و منتشر شد.');
            setTimeout(() => window.location.href = 'campaign-status.php', 1500);
        },
        error: function() {
            notify('خطا در ثبت اطلاعات.');
            $('#submitBtn').prop('disabled', false).html(ROCKET_SVG + ' تلاش مجدد');
        }
    });
});
</script>
</body>
</html>
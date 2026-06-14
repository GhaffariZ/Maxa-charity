<?php require_once __DIR__ . '/_guard.php';
dash_require('campaigns'); ?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ایجاد کمپین حمایتی</title>
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
        --primary-color: #008075;
        --secondary-color: #F79F1F;
        --bg-color: #f4f7f6;
        --panel-bg: #ffffff;
        --text-color: #333;
        --border-color: #e0e0e0;
    }
    [data-theme="dark"] {
        --bg-color: #121212;
        --panel-bg: #1e1e1e;
        --text-color: #eee;
        --border-color: #333;
    }

    body { background-color: var(--bg-color); color: var(--text-color); font-family: Tahoma, sans-serif; margin: 0; padding: 20px; }
    .container { max-width: 1100px; margin: 0 auto; }
    
    .main-card { background: var(--panel-bg); border-radius: 20px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .panel-header { background: var(--primary-color); color: white; padding: 20px; font-weight: bold; font-size: 18px; display: flex; align-items: center; gap: 10px; }

    /* چیدمان دو ستونه */
    .panel-body { display: flex; gap: 40px; padding: 30px; }
    
    .form-side { flex: 1; } /* ستون سمت راست (فرم) */
    .preview-side { width: 320px; flex-shrink: 0; } /* ستون سمت چپ (پیش‌نمایش) */

    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: bold; color: var(--primary-color); font-size: 14px; }
    .input { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-color); color: var(--text-color); box-sizing: border-box; transition: 0.3s; }
    .input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.1); }

    /* استایل کارت پیش‌نمایش زنده */
    .preview-sticky { position: sticky; top: 20px; }
    .live-card {
        background: var(--panel-bg); border: 1px solid var(--border-color);
        border-radius: 16px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    .live-img-wrapper { width: 100%; height: 180px; background: #eee; overflow: hidden; position: relative; }
    .live-img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .live-content { padding: 15px; }
    .live-title { margin: 0 0 10px 0; color: var(--primary-color); font-size: 16px; font-weight: bold; height: 45px; overflow: hidden; }
    .live-progress { height: 6px; background: #eee; border-radius: 10px; margin-bottom: 15px; }
    .live-bar { height: 100%; background: var(--secondary-color); border-radius: 10px; width: 35%; }

    .btn-submit { background: var(--primary-color); color: white; border: none; padding: 15px; border-radius: 12px; cursor: pointer; font-weight: bold; width: 100%; font-size: 16px; transition: 0.3s; margin-top: 10px; }
    .btn-submit:hover { opacity: 0.9; transform: translateY(-2px); }

    /* Toast Notification */
    .toast-container { position: fixed; bottom: 20px; left: 20px; z-index: 9999; }
    .toast { background: var(--panel-bg); color: var(--text-color); padding: 15px 25px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-top: 10px; transform: translateX(-150%); transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); border-right: 5px solid var(--primary-color); display: flex; align-items: center; gap: 12px; }
    .toast.active { transform: translateX(0); }

    /* بهینه‌سازی موبایل */
    @media (max-width: 900px) {
        .panel-body { flex-direction: column-reverse; padding: 20px; }
        .preview-side { width: 100%; }
        .preview-sticky { position: static; margin-bottom: 30px; }
    }
</style>
</head>
<body>
<?php include "./components/back/back.php" ?> 
<div class="container">
    <div class="main-card">
        <div class="panel-header">
            <i class="fas fa-plus-circle"></i> طراحی و ایجاد کمپین جدید
        </div>
        
        <div class="panel-body">
            <div class="preview-side">
                <div class="preview-sticky">
                    <label style="text-align: center; display: block; margin-bottom: 15px;">پیش‌نمایش زنده کارت</label>
                    <div class="live-card">
                        <div class="live-img-wrapper">
                            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMjUwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZWVmMWYyIi8+PGcgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYjljMmM2IiBzdHJva2Utd2lkdGg9IjMiPjxyZWN0IHg9IjE1OCIgeT0iOTIiIHdpZHRoPSI4NCIgaGVpZ2h0PSI2NCIgcng9IjciLz48Y2lyY2xlIGN4PSIxODIiIGN5PSIxMTYiIHI9IjkiLz48cGF0aCBkPSJNMTYyIDE1MGwyNC0yMiAxNSAxMyAxOS0xNyAxOCAxNnYxMnoiLz48L2c+PC9zdmc+" id="imgPreview" class="live-img" onerror="this.onerror=null;this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMjUwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZWVmMWYyIi8+PGcgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYjljMmM2IiBzdHJva2Utd2lkdGg9IjMiPjxyZWN0IHg9IjE1OCIgeT0iOTIiIHdpZHRoPSI4NCIgaGVpZ2h0PSI2NCIgcng9IjciLz48Y2lyY2xlIGN4PSIxODIiIGN5PSIxMTYiIHI9IjkiLz48cGF0aCBkPSJNMTYyIDE1MGwyNC0yMiAxNSAxMyAxOS0xNyAxOCAxNnYxMnoiLz48L2c+PC9zdmc+'">
                        </div>
                        <div class="live-content">
                            <h4 class="live-title" id="titlePreview">عنوان کمپین شما در این قسمت...</h4>
                            <div class="live-progress"><div class="live-bar"></div></div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: bold; margin-bottom: 10px;">
                                <span>هدف: ۵۰,۰۰۰,۰۰۰</span>
                                <span style="color: var(--secondary-color);">۳۵٪</span>
                            </div>
                            <button style="width: 100%; background: none; border: 1.5px solid var(--secondary-color); color: var(--secondary-color); padding: 8px; border-radius: 8px; font-weight: bold; cursor: default;" disabled>حمایت مالی</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-side">
                <form id="campForm">
                    <div class="form-group">
                        <label><i class="fas fa-camera"></i> تصویر شاخص کمپین</label>
                        <input type="file" name="featured_image" id="imageInput" class="input" accept="image/*" required>
                        <small style="font-size: 11px; color: var(--text-muted); margin-top: 5px; display: block;">تصویر به صورت خودکار برای کارت کراپ می‌شود.</small>
                    </div>

                    <div class="form-group">
                        <label>عنوان کمپین</label>
                        <input type="text" name="title" id="titleInput" class="input" placeholder="یک عنوان جذاب بنویسید..." required maxlength="70">
                    </div>

                    <div class="form-group">
                        <label>دسته‌بندی کمپین</label>
                        <select name="category" id="categoryInput" class="input" required>
                            <option value="food">غذا</option>
                            <option value="drug">دارو</option>
                            <option value="education">مهارت آموزی</option>
                        </select>
                        <small style="font-size: 11px; color: var(--text-muted); margin-top: 5px; display: block;">این دسته در صفحه کاربران برای فیلتر کردن کمپین‌ها استفاده می‌شود.</small>
                    </div>

                    <div class="form-group">
                        <label>توضیحات تکمیلی</label>
                        <textarea name="description" class="input" rows="6" placeholder="داستان کمپین و چرخه حمایت را شرح دهید..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>مبلغ مورد نیاز (تومان)</label>
                        <input type="number" name="target_amount" class="input" placeholder="مثلا 10000000" required>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-rocket"></i> انتشار نهایی کمپین
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="toast-container" id="toastBox"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
function notify(msg) {
    const id = Date.now();
    $('#toastBox').append(`<div class="toast active" id="${id}"><i class="fas fa-check-circle"></i> ${msg}</div>`);
    setTimeout(() => { 
        $(`#${id}`).removeClass('active'); 
        setTimeout(() => $(`#${id}`).remove(), 500); 
    }, 4000);
}

$('#campForm').on('submit', function(e) {
    e.preventDefault();
    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> در حال ثبت...');
    
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
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-rocket"></i> تلاش مجدد');
        }
    });
});
</script>
</body>
</html>
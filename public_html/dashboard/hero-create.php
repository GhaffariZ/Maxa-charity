<?php require_once __DIR__ . '/_guard.php';
dash_require('hero'); ?>
<!DOCTYPE html>

<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ایجاد هیرو جدید</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* پالت رنگی لایت مود */
            --primary-color: #007D75;
            --secondary-color: #F79F1F;
            --bg-color: #f4f7f6;
            --text-color: #333333;
            --panel-bg: #ffffff;
            --border-color: #dddddd;
            --input-bg: #ffffff;
            --header-text: #007D75;
            --btn-hover-opacity: 0.9;
        }

        [data-theme="dark"] {
            /* پالت رنگی دارک مود */
            --primary-color: #00a89d;
            --secondary-color: #ffb142;
            --bg-color: #121212;
            --text-color: #e0e0e0;
            --panel-bg: #1e1e1e;
            --border-color: #444444;
            --input-bg: #2d2d2d;
            --header-text: #00a89d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
            font-family: 'Vazirmatn', sans-serif !important;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 15px;
        }

        .card {
            background-color: var(--panel-bg);
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-radius: 12px;
            overflow: hidden;
        }

        .panel-heading {
            background-color: var(--panel-bg);
            color: var(--header-text);
            border-bottom: 2px solid var(--primary-color);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-title {
            margin: 0;
            font-weight: bold;
            font-size: 1.25rem;
        }

        .theme-toggle-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 42px;
            height: 42px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            transition: transform 0.2s, background-color 0.3s;
            outline: none;
        }

        .theme-toggle-btn:hover {
            transform: scale(1.08);
            opacity: var(--btn-hover-opacity);
        }

        .theme-toggle-btn svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .panel-body {
            padding: 20px;
        }

        /* گرید سیستم اختصاصی برای ریسپانسیو */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -10px;
            margin-right: -10px;
        }

        .col-4, .col-6, .col-12 {
            padding: 0 10px;
            margin-bottom: 15px;
        }

        .col-4 { width: 33.333%; }
        .col-6 { width: 50%; }
        .col-12 { width: 100%; }

        .input-group {
            margin-bottom: 20px;
            width: 100%;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .input {
            width: 100%;
            background-color: var(--input-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 12px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.3s, box-shadow 0.3s;
            height: 46px;
        }

        .input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.15);
        }

        input[type="file"].input {
            padding: 9px;
        }

        /* استایل چک باکس */
        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: var(--primary-color);
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 0;
            height: 46px;
        }

        .checkbox-label input[type="checkbox"] {
            margin-left: 10px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
            cursor: pointer;
        }

        /* استایل اختصاصی باکس لینک برای ترنزیشن دسکتاپ */
        .link-box-transition {
            opacity: 0;
            visibility: hidden;
            transform: translateX(40px);
            transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                        transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                        visibility 0.4s;
            margin-bottom: 15px;
        }

        .link-box-transition.show {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        /* ادیتور */
        .editor-toolbar {
            background-color: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-bottom: none;
            border-radius: 8px 8px 0 0;
            padding: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }

        .tb-btn {
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            border-radius: 4px;
            cursor: pointer;
            padding: 6px 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .tb-btn:hover {
            background-color: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
        }

        .tb-btn svg {
            stroke: currentColor;
        }

        .tb-btn:hover svg {
            stroke: #fff;
        }

        #editor {
            min-height: 250px;
            border: 1px solid var(--border-color);
            border-radius: 0 0 8px 8px;
            padding: 15px;
            background-color: var(--input-bg);
            color: var(--text-color);
            overflow-y: auto;
            line-height: 1.8;
        }

        #editor:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        /* دکمه‌ها */
        .builder-toolbar {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: opacity 0.3s;
            font-family: inherit;
            flex-grow: 1;
            text-align: center;
        }

        .btn:hover {
            opacity: var(--btn-hover-opacity);
        }

        .btn-save {
            background-color: var(--primary-color);
            color: white;
        }

        /* استاتوس */
        .status-msg {
            margin-top: 15px;
            padding: 10px;
            border-radius: 6px;
            display: none;
            font-weight: bold;
        }

        .status-msg:not(:empty) {
            display: block;
        }

        .status-err { background: #ffeaa7; color: #d63031; border: 1px solid #fdcb6e; }
        .status-ok { background: #55efc4; color: #00b894; border: 1px solid #00b894; }

        /* استایل‌های اختصاصی موبایل */
        @media (max-width: 768px) {
            .col-4, .col-6 { width: 100%; }
            .panel-heading { padding: 15px; }
            .btn { width: 100%; }
            .checkbox-label { height: auto; margin-bottom: 15px; }
            
            /* حذف مارجین ستون در موبایل برای جلوگیری از ایجاد فضای خالی */
            .link-wrapper-col {
                margin-bottom: 0 !important;
            }
            
            /* بازسازی ترنزیشن برای حالت موبایل (همراستا با تغییر ارتفاع و جابجایی) */
            .link-box-transition {
                max-height: 0;
                margin-bottom: 0;
                overflow: hidden;
                transform: translateX(40px);
                transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                            transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                            visibility 0.4s,
                            max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                            margin-bottom 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .link-box-transition.show {
                max-height: 60px; /* فضای کافی برای ارتفاع اینپوت */
                margin-bottom: 15px; /* ایجاد فاصله با باکس‌های پایینی بعد از باز شدن */
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="panel-heading">
            <h3 class="panel-title">ایجاد هیرو جدید</h3>
            <!-- دکمه‌ی تغییر تم حذف شد — تم از «داشبورد مدیریت» کنترل می‌شود -->
        </div>

        <div class="panel-body">
            <div class="input-group">
                <label for="title">عنوان هیرو</label>
                <input type="text" class="input" id="title" placeholder="عنوان هیرو را وارد کنید...">
            </div>

            <div class="input-group">
                <label for="featured_image">تصویر هیرو</label>
                <input type="file" class="input" id="featured_image" accept="image/*" onchange="previewImage(event)">
                <div id="featuredPreview"></div>
            </div>

            <div class="input-group">
                <label>توضیحات هیرو</label>
                <div class="editor-toolbar">
                    <button type="button" onclick="format('bold')" class="tb-btn" title="ضخیم">
                        <svg width="18" viewBox="0 0 24 24"><path d="M7 5v14h6a4 4 0 0 0 0-8H7m6 0a4 4 0 0 0 0-8H7" fill="none" stroke-width="2"/></svg>
                    </button>
                    <button type="button" onclick="format('italic')" class="tb-btn" title="کج">
                        <svg width="18" viewBox="0 0 24 24"><line x1="19" y1="4" x2="10" y2="4" stroke-width="2"/><line x1="14" y1="20" x2="5" y2="20" stroke-width="2"/><line x1="15" y1="4" x2="9" y2="20" stroke-width="2"/></svg>
                    </button>
                    <button type="button" onclick="format('underline')" class="tb-btn" title="خط زیرین">
                        <svg width="18" viewBox="0 0 24 24"><path d="M6 4v6a6 6 0 0 0 12 0V4" fill="none" stroke-width="2"/><line x1="4" y1="20" x2="20" y2="20" stroke-width="2"/></svg>
                    </button>
                </div>
                <div id="editor" contenteditable="true" placeholder="توضیحات هیرو را اینجا بنویسید..."></div>
            </div>

            <div class="row" style="align-items: center;">
                <div class="col-6">
                    <label class="checkbox-label">
                        <input type="checkbox" id="has_link" onchange="toggleLinkBox()">
                        این هیرو حاوی لینک است
                    </label>
                </div>
                
                <div class="col-6 link-wrapper-col">
                    <div class="input-group link-box-transition" id="link_box_container">
                        <input type="url" class="input" id="hero_link" placeholder="لینک هیرو را وارد کنید (مثلا https://example.com)">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="input-group">
                        <label for="category">دسته هیرو</label>
                        <select class="input" id="category">
                            <option value="صفحه اصلی">صفحه اصلی</option>
                            <option value="درباره ما">درباره ما</option>
                            <option value="تبلیغاتی">تبلیغاتی</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="input-group">
                        <label for="publish_date">تاریخ و زمان انتشار</label>
                        <input type="datetime-local" class="input" id="publish_date">
                    </div>
                </div>
            </div>

            <div class="builder-toolbar">
                <button class="btn btn-save" onclick="saveHero()">ثبت هیرو نهایی</button>
            </div>
            
            <div id="statusBox" class="status-msg"></div>
        </div>
    </div>
</div>

<script>
    // مدیریت تم (دارک/لایت)
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggleBtn = document.getElementById('theme-toggle');
        const currentTheme = localStorage.getItem('theme') || 'light';
        
        const sunIcon = `<svg viewBox="0 0 24 24"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41L5.99 4.58zm12.37 12.37c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41l-1.06-1.06zm1.06-10.96c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06zM7.05 18.36c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06z"/></svg>`;
        const moonIcon = `<svg viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9c0-.46-.04-.92-.1-1.36-.98 1.37-2.58 2.26-4.4 2.26-2.98 0-5.4-2.42-5.4-5.4 0-1.81.89-3.42 2.26-4.4-.44-.06-.9-.1-1.36-.1z"/></svg>`;

        // تم از «داشبورد مدیریت» کنترل می‌شود (کلید مشترک: maxa-theme)
        var applyMaxaTheme = function(){
            var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
            if(d){ document.documentElement.setAttribute('data-theme','dark'); if(document.body) document.body.setAttribute('data-theme','dark'); }
            else { document.documentElement.removeAttribute('data-theme'); if(document.body) document.body.removeAttribute('data-theme'); }
        };
        applyMaxaTheme();
        window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });

        if (themeToggleBtn) themeToggleBtn.addEventListener('click', () => {
            let theme = document.body.getAttribute('data-theme');
            
            if (theme === 'dark') {
                document.body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                themeToggleBtn.innerHTML = moonIcon;
            } else {
                document.body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeToggleBtn.innerHTML = sunIcon;
            }
        });
    });

    // مدیریت انیمیشن باز و بسته شدن باکس لینک هیرو
    function toggleLinkBox() {
        const checkbox = document.getElementById('has_link');
        const linkBoxContainer = document.getElementById('link_box_container');
        
        if (checkbox.checked) {
            linkBoxContainer.classList.add('show');
        } else {
            linkBoxContainer.classList.remove('show');
            setTimeout(() => {
                if(!checkbox.checked) {
                    document.getElementById('hero_link').value = '';
                }
            }, 400);
        }
    }

    // ابزارهای ادیتور
    function format(command, value = null) {
        document.execCommand(command, false, value);
        document.getElementById('editor').focus();
    }

    // پیش‌نمایش تصویر
    function previewImage(event) {
        const preview = document.getElementById('featuredPreview');
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="تصویر هیرو" style="max-width: 100%; width: 250px; margin-top: 15px; border-radius: 8px; border: 1px solid var(--border-color);">`;
            }
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    }

    // ذخیره هیرو
// جایگزین این تابع در فایل خودت کن
function saveHero() {
    const statusBox = document.getElementById("statusBox");
    statusBox.style.display = "block";
    statusBox.className = "status-msg";
    statusBox.innerText = "در حال ذخیره...";

    const formData = new FormData();
    formData.append("title", document.getElementById("title").value);
    formData.append("description", document.getElementById("editor").innerHTML);
    formData.append("link", document.getElementById("hero_link").value);
    formData.append("category", document.getElementById("category").value);
    formData.append("publish_date", document.getElementById("publish_date").value);

    const imageFile = document.getElementById("featured_image").files[0];
    if (imageFile) {
        formData.append("image", imageFile);
    }

    // ارسال درخواست
    fetch('./hero-save.php', {
        method: "POST",
        body: formData
    })
    .then(response => response.json()) // حتما باید json باشد
    .then(data => {
        if (data.status === "success") {
            statusBox.className = "status-msg status-ok";
            statusBox.innerText = data.message;
            
            // پاک کردن فرم در صورت موفقیت
            document.getElementById("title").value = '';
            document.getElementById("editor").innerHTML = '';
            document.getElementById("featured_image").value = '';
            document.getElementById("featuredPreview").innerHTML = '';
        } else {
            statusBox.className = "status-msg status-err";
            statusBox.innerText = data.message;
        }
    })
    .catch(error => {
        statusBox.className = "status-msg status-err";
        statusBox.innerText = "خطا در ارتباط با سرور: " + error;
        console.error("Error:", error);
    });
}


</script>

</body>
</html>
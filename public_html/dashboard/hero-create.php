<?php require_once __DIR__ . '/_guard.php';
dash_require('hero'); ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ایجاد هیرو جدید</title>

<!-- تم دارک/لایت از «داشبورد مدیریت» تبعیت می‌کند (کلید مشترک: maxa-theme) -->
<script>
(function(){
  function applyMaxaTheme(){
    var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
    if(d){ document.documentElement.setAttribute('data-theme','dark'); if(document.body) document.body.setAttribute('data-theme','dark'); }
    else { document.documentElement.removeAttribute('data-theme'); if(document.body) document.body.removeAttribute('data-theme'); }
  }
  applyMaxaTheme();
  window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });
})();
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

<style>
:root {
    /* پالت رنگی لایت مود (برگرفته از لوگو مکسا) */
    --primary-color: #007D75;
    --primary-dark: #006159;
    --secondary-color: #F79F1F;
    --bg-color: #f4f7f6;
    --text-color: #333333;
    --muted-color: #6b7674;
    --panel-bg: #ffffff;
    --surface-2: #f7faf9;
    --border-color: #e3e9e8;
    --input-bg: #ffffff;
    --header-text: #007D75;
    --danger: #e74c3c;
    --modal-overlay: rgba(0,0,0,0.6);
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
    --shadow-md: 0 8px 24px rgba(0,0,0,0.07);
    --radius: 16px;
    --radius-sm: 10px;
    --anim-fast: 220ms;
    --anim-mid: 420ms;
    --anim-slow: 700ms;
}

[data-theme="dark"] {
    --primary-color: #00a89d;
    --primary-dark: #00897e;
    --secondary-color: #ffb142;
    --bg-color: #121212;
    --text-color: #e0e0e0;
    --muted-color: #9aa3a1;
    --panel-bg: #1e1e1e;
    --surface-2: #262626;
    --border-color: #383838;
    --input-bg: #2d2d2d;
    --header-text: #00a89d;
    --modal-overlay: rgba(0,0,0,0.8);
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.35);
    --shadow-md: 0 8px 24px rgba(0,0,0,0.45);
}

* { box-sizing: border-box; }

body {
    background-color: var(--bg-color);
    color: var(--text-color);
    font-family: 'Vazirmatn', Tahoma, Arial, sans-serif;
    transition: background-color 0.3s, color 0.3s;
    margin: 0;
    padding: 0;
    min-height: 100vh;
}

.container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 16px 40px;
    animation: pageRise var(--anim-slow) cubic-bezier(.2,.8,.2,1) both;
}

/* ===== هدر چسبان ===== */
.editor-header {
    position: sticky;
    top: 0;
    z-index: 30;
    background: color-mix(in srgb, var(--bg-color) 88%, transparent);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border-color);
    margin: 0 -16px 24px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.eh-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
.ph-ic {
    width: 44px; height: 44px; flex-shrink: 0; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; color: #fff;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    box-shadow: var(--shadow-sm);
}
.ph-ic svg { width: 22px; height: 22px; }
.eh-title h1 { margin: 0; font-size: 1.3rem; font-weight: 800; color: var(--header-text); }
.eh-title p { margin: 2px 0 0; font-size: 12px; color: var(--muted-color); }
.eh-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

/* ===== شبکه اصلی ===== */
.layout { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start; }
.col-main { display: flex; flex-direction: column; gap: 24px; min-width: 0; }
.col-side { display: flex; flex-direction: column; gap: 24px; }

/* ===== کارت‌ها ===== */
.card {
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
    animation: cardPop var(--anim-mid) cubic-bezier(.2,.8,.2,1) both;
}
.card-pad { padding: 20px; }
.card-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 15px; font-weight: 800; color: var(--header-text); margin: 0 0 16px;
}
.card-title svg { width: 19px; height: 19px; color: var(--primary-color); }

/* ===== عنوان هیرو ===== */
.title-card { padding: 20px; transition: box-shadow var(--anim-fast), border-color var(--anim-fast); }
.title-card:focus-within { border-color: color-mix(in srgb, var(--primary-color) 55%, var(--border-color)); box-shadow: 0 0 0 4px rgba(0,125,117,0.08); }
#title {
    width: 100%; border: none; background: transparent; color: var(--text-color);
    font-family: inherit; font-size: 26px; font-weight: 800; padding: 0; line-height: 1.5;
}
#title:focus { outline: none; }
#title::placeholder { color: var(--muted-color); opacity: 0.55; }

/* ===== فیلدهای عمومی ===== */
.input-group { margin-bottom: 18px; }
.input-group:last-child { margin-bottom: 0; }
label.field-label {
    display: flex; align-items: center; gap: 6px; margin-bottom: 8px;
    font-size: 13px; font-weight: 700; color: var(--primary-color);
}
label.field-label svg { width: 16px; height: 16px; }
.input {
    width: 100%; background: var(--input-bg); color: var(--text-color);
    border: 1px solid var(--border-color); border-radius: var(--radius-sm);
    padding: 11px 12px; font-size: 15px; font-family: inherit;
    transition: border-color var(--anim-fast), box-shadow var(--anim-fast);
}
.input:focus { border-color: var(--primary-color); outline: none; box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.14); }
select.input { appearance: none; cursor: pointer; }

/* ===== حالت خطای فیلدها ===== */
.input.field-invalid,
.title-card.field-invalid {
    border-color: var(--danger) !important;
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.14) !important;
}
.title-card.field-invalid, .input.field-invalid { animation: fieldShake 0.32s ease; }
.input-group.has-error label.field-label { color: var(--danger); }
.field-error {
    display: none; align-items: center; gap: 5px; margin-top: 7px;
    font-size: 12px; font-weight: 600; color: var(--danger);
}
.field-error.show { display: flex; animation: toastSlide 0.28s ease; }
.field-error svg { width: 14px; height: 14px; flex-shrink: 0; }
@keyframes fieldShake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-4px)} 75%{transform:translateX(4px)} }

/* ===== ادیتور ===== */
.editor-toolbar {
    display: flex; flex-wrap: wrap; gap: 6px; align-items: center;
    padding-bottom: 16px; margin-bottom: 16px; border-bottom: 1px solid var(--border-color);
}
.tb-btn {
    background: var(--surface-2); border: 1px solid var(--border-color); color: var(--text-color);
    border-radius: 8px; cursor: pointer; padding: 7px 8px;
    display: flex; align-items: center; justify-content: center; transition: all var(--anim-fast);
}
.tb-btn:hover { background: var(--primary-color); color: #fff; border-color: var(--primary-color); transform: translateY(-2px); }
.tb-btn svg { stroke: currentColor; }
.tb-btn:hover svg { stroke: #fff; }
#editor {
    min-height: 280px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);
    padding: 16px; background: var(--input-bg); color: var(--text-color);
    overflow-y: auto; line-height: 1.9; font-size: 16px;
}
#editor:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(0,125,117,0.10); }
#editor:empty:before { content: attr(data-placeholder); color: var(--muted-color); opacity: 0.55; }
#editor img { max-width: 100%; height: auto; border-radius: 10px; }

/* ===== دکمه‌ها ===== */
.btn {
    padding: 10px 20px; border: none; border-radius: 999px; cursor: pointer;
    font-weight: 700; font-size: 14px; font-family: inherit;
    display: inline-flex; align-items: center; justify-content: center; gap: 7px;
    transition: transform var(--anim-fast), box-shadow var(--anim-fast), opacity var(--anim-fast), background var(--anim-fast);
}
.btn svg { width: 17px; height: 17px; }
.btn:hover { transform: translateY(-2px); }
.btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.btn-save { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: #fff; box-shadow: var(--shadow-sm); }
.btn-save:hover { box-shadow: var(--shadow-md); }

/* ===== آپلود تصویر (Dropzone) ===== */
.dropzone {
    position: relative; border: 2px dashed var(--border-color); border-radius: var(--radius-sm);
    background: var(--surface-2); min-height: 190px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    text-align: center; padding: 20px; cursor: pointer; transition: all var(--anim-fast); overflow: hidden;
}
.dropzone:hover, .dropzone.drag-over { border-color: var(--primary-color); background: rgba(0,125,117,0.05); }
.dropzone .dz-icon { color: var(--muted-color); transition: color var(--anim-fast); }
.dropzone:hover .dz-icon { color: var(--primary-color); }
.dropzone .dz-icon svg { width: 38px; height: 38px; }
.dropzone .dz-main { font-size: 14px; font-weight: 600; color: var(--text-color); margin-top: 8px; }
.dropzone .dz-hint { font-size: 12px; color: var(--muted-color); margin-top: 4px; }
.dz-preview { position: absolute; inset: 0; display: none; }
.dz-preview.show { display: block; }
.dz-preview img { width: 100%; height: 100%; object-fit: cover; }
.dz-remove {
    position: absolute; top: 8px; left: 8px; background: rgba(231,76,60,0.92); color: #fff;
    border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);
}
.dz-remove svg { width: 16px; height: 16px; }

/* ===== چک‌باکس لینک ===== */
.switch-row {
    display: flex; align-items: center; gap: 10px; cursor: pointer;
    font-size: 13px; font-weight: 700; color: var(--text-color); user-select: none;
}
.switch-row input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--primary-color); cursor: pointer; }
.link-box {
    overflow: hidden; max-height: 0; opacity: 0; margin-top: 0;
    transition: max-height var(--anim-mid) cubic-bezier(.2,.8,.2,1), opacity var(--anim-mid) ease, margin-top var(--anim-mid) ease;
}
.link-box.show { max-height: 80px; opacity: 1; margin-top: 12px; }

/* بخش‌بندی کارت تنظیمات */
.settings-card .input-group { padding: 16px; margin: 0; border-bottom: 1px solid var(--border-color); }
.settings-card .input-group:last-child { border-bottom: none; }

/* ===== توست وضعیت ===== */
.toast {
    position: fixed; bottom: 22px; left: 22px; z-index: 10001;
    width: min(380px, calc(100vw - 44px)); display: none;
    background: var(--panel-bg); border: 1px solid var(--border-color);
    border-radius: var(--radius); box-shadow: var(--shadow-md); overflow: hidden;
}
.toast.show { display: block; animation: toastSlide 360ms cubic-bezier(.2,.8,.2,1) both; }
.toast.hide { animation: toastOut 260ms ease both; }
.toast::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 5px; background: var(--primary-color); }
.toast.is-error::before { background: var(--danger); }
.toast.is-success::before { background: #00b894; }
.toast.is-info::before { background: var(--secondary-color); }
.toast-row { display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; }
.toast-ic { flex-shrink: 0; width: 38px; height: 38px; border-radius: 11px; display: flex; align-items: center; justify-content: center; color: #fff; }
.toast-ic svg { width: 20px; height: 20px; }
.toast.is-error .toast-ic { background: var(--danger); }
.toast.is-success .toast-ic { background: #00b894; }
.toast.is-info .toast-ic { background: var(--secondary-color); }
.toast.is-info .toast-ic.spin svg { animation: toastSpin 0.9s linear infinite; }
.toast-body { flex: 1; min-width: 0; padding-top: 1px; }
.toast-title { font-size: 14px; font-weight: 800; color: var(--text-color); margin: 0 0 2px; }
.toast-msg { font-size: 13px; color: var(--muted-color); line-height: 1.7; word-break: break-word; }
.toast-close {
    flex-shrink: 0; background: transparent; border: none; color: var(--muted-color); cursor: pointer;
    width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all var(--anim-fast);
}
.toast-close:hover { background: var(--surface-2); color: var(--text-color); }
.toast-close svg { width: 16px; height: 16px; }

@keyframes pageRise { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes cardPop { from { opacity: 0; transform: translateY(16px) scale(0.985); } to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes toastSlide { from { opacity: 0; transform: translateY(16px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes toastOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(16px); } }
@keyframes toastSpin { to { transform: rotate(360deg); } }

.col-side > .card { animation-delay: 90ms; }
.col-side > .card:nth-child(2) { animation-delay: 150ms; }

/* ===== ریسپانسیو ===== */
@media (max-width: 980px) { .layout { grid-template-columns: 1fr; } }
@media (max-width: 600px) {
    #title { font-size: 22px; }
    .eh-actions { width: 100%; }
    .eh-actions .btn { flex: 1; }
    .tb-btn { flex: 1 1 calc(33% - 6px); }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation: none !important; transition: none !important; }
}
</style>
</head>

<body>

<div class="container">

    <!-- هدر چسبان -->
    <div class="editor-header">
        <div class="eh-title">
            <span class="ph-ic">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </span>
            <div>
                <h1>ایجاد هیرو جدید</h1>
                <p>مدیریت اسلایدهای صفحه</p>
            </div>
        </div>
        <div class="eh-actions">
            <button type="button" class="btn btn-save" onclick="saveHero()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                ثبت هیرو نهایی
            </button>
        </div>
    </div>

    <div class="layout">

        <!-- ستون اصلی -->
        <div class="col-main">

            <!-- عنوان هیرو -->
            <div class="card title-card" id="titleCard">
                <input type="text" id="title" placeholder="عنوان هیرو را اینجا بنویسید...">
                <div class="field-error" id="titleError">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>وارد کردن عنوان هیرو الزامی است.</span>
                </div>
            </div>

            <!-- توضیحات هیرو -->
            <div class="card card-pad">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
                    توضیحات هیرو
                </h3>
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
                <div id="editor" contenteditable="true" data-placeholder="توضیحات هیرو را اینجا بنویسید..."></div>
            </div>

        </div>

        <!-- ستون کناری -->
        <div class="col-side">

            <!-- تصویر هیرو -->
            <div class="card card-pad">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    تصویر هیرو
                </h3>
                <input type="file" id="featured_image" accept="image/*" style="display:none;" onchange="onPickImage(event)">
                <div class="dropzone" id="dropzone">
                    <div class="dz-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    </div>
                    <div class="dz-main">تصویر را اینجا بکشید یا کلیک کنید</div>
                    <div class="dz-hint">PNG, JPG</div>
                    <div class="dz-preview" id="dzPreview">
                        <img id="dzImg" src="" alt="پیش‌نمایش تصویر">
                        <button class="dz-remove" type="button" onclick="removeImage(event)" title="حذف تصویر">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- تنظیمات -->
            <div class="card settings-card">

                <div class="input-group">
                    <label class="field-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        لینک هیرو
                    </label>
                    <label class="switch-row">
                        <input type="checkbox" id="has_link" onchange="toggleLinkBox()">
                        این هیرو حاوی لینک است
                    </label>
                    <div class="link-box" id="link_box_container">
                        <input type="url" class="input" id="hero_link" placeholder="مثلاً https://example.com">
                    </div>
                </div>

                <div class="input-group">
                    <label class="field-label" for="category">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        دسته هیرو
                    </label>
                    <select class="input" id="category">
                        <option value="صفحه اصلی">صفحه اصلی</option>
                        <option value="درباره ما">درباره ما</option>
                        <option value="تبلیغاتی">تبلیغاتی</option>
                    </select>
                </div>

                <div class="input-group">
                    <label class="field-label" for="publish_date">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        تاریخ و زمان انتشار
                    </label>
                    <input type="datetime-local" class="input" id="publish_date">
                </div>

            </div>

        </div>

    </div>

</div>

<!-- توست وضعیت -->
<div id="statusToast" class="toast" role="status" aria-live="polite">
    <div class="toast-row">
        <div class="toast-ic" id="toastIcon"></div>
        <div class="toast-body">
            <p class="toast-title" id="toastTitle"></p>
            <div class="toast-msg" id="toastMsg"></div>
        </div>
        <button type="button" class="toast-close" onclick="hideStatus()" aria-label="بستن">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
</div>

<!-- statusBox مخفی برای سازگاری عقب‌رو (در صورت ارجاع از جای دیگر) -->
<div id="statusBox" style="display:none;"></div>

<script>
/* ===================== توست وضعیت ===================== */
const TOAST_ICONS = {
    success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
    error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/></svg>'
};
const TOAST_TITLES = { success: "انجام شد", error: "خطا", info: "در حال انجام" };
let toastHideTimer = null;

function showStatus(msg, type){
    let kind = "info";
    if (type === true) kind = "success";
    else if (type === false) kind = "error";
    else if (typeof type === "string") kind = type;

    const cleanMsg = String(msg).replace(/^[✅❌⏳⚡🗜🔼⬆\s]+/u, "").trim();
    const toast = document.getElementById("statusToast");
    const iconEl = document.getElementById("toastIcon");
    toast.classList.remove("is-success", "is-error", "is-info", "hide");
    toast.classList.add("is-" + kind, "show");
    iconEl.className = "toast-ic" + (kind === "info" ? " spin" : "");
    iconEl.innerHTML = TOAST_ICONS[kind];
    document.getElementById("toastTitle").textContent = TOAST_TITLES[kind];
    document.getElementById("toastMsg").textContent = cleanMsg;

    clearTimeout(toastHideTimer);
    if (kind !== "info") {
        toastHideTimer = setTimeout(hideStatus, kind === "error" ? 6000 : 3500);
    }
}

function hideStatus(){
    const toast = document.getElementById("statusToast");
    if (!toast.classList.contains("show")) return;
    clearTimeout(toastHideTimer);
    toast.classList.add("hide");
    setTimeout(() => { toast.classList.remove("show", "hide", "is-success", "is-error", "is-info"); }, 250);
}

/* ===================== اعتبارسنجی فیلد ===================== */
function setFieldError(boxId, errId, on){
    const box = document.getElementById(boxId);
    const err = document.getElementById(errId);
    if (box) box.classList.toggle("field-invalid", on);
    if (err) err.classList.toggle("show", on);
    const grp = box ? box.closest(".input-group") : null;
    if (grp) grp.classList.toggle("has-error", on);
}
document.getElementById("title").addEventListener("input", () => setFieldError("titleCard", "titleError", false));

/* ===================== چک‌باکس لینک ===================== */
function toggleLinkBox() {
    const checkbox = document.getElementById('has_link');
    const linkBoxContainer = document.getElementById('link_box_container');
    if (checkbox.checked) {
        linkBoxContainer.classList.add('show');
    } else {
        linkBoxContainer.classList.remove('show');
        setTimeout(() => {
            if (!checkbox.checked) document.getElementById('hero_link').value = '';
        }, 420);
    }
}

/* ===================== ابزارهای ادیتور ===================== */
function format(command, value = null) {
    document.execCommand(command, false, value);
    document.getElementById('editor').focus();
}

/* ===================== آپلود تصویر (Dropzone) ===================== */
const featuredInput = document.getElementById("featured_image");
const dropzone = document.getElementById("dropzone");
const dzPreview = document.getElementById("dzPreview");
const dzImg = document.getElementById("dzImg");

dropzone.addEventListener("click", (e) => {
    if (e.target.closest(".dz-remove")) return;
    featuredInput.click();
});
["dragenter","dragover","dragleave","drop"].forEach(ev => {
    dropzone.addEventListener(ev, (e) => { e.preventDefault(); e.stopPropagation(); });
});
["dragenter","dragover"].forEach(ev => dropzone.addEventListener(ev, () => dropzone.classList.add("drag-over")));
["dragleave","drop"].forEach(ev => dropzone.addEventListener(ev, () => dropzone.classList.remove("drag-over")));
dropzone.addEventListener("drop", (e) => {
    const files = e.dataTransfer.files;
    if (files && files[0]) { featuredInput.files = files; showImagePreview(files[0]); }
});

function onPickImage(event){
    const file = event.target.files[0];
    if (file) showImagePreview(file);
}
function showImagePreview(file){
    const reader = new FileReader();
    reader.onload = e => { dzImg.src = e.target.result; dzPreview.classList.add("show"); };
    reader.readAsDataURL(file);
}
function removeImage(e){
    if (e) e.stopPropagation();
    featuredInput.value = "";
    dzImg.src = "";
    dzPreview.classList.remove("show");
}

/* ===================== ذخیره هیرو ===================== */
function validateHeroForm(){
    const title = document.getElementById("title").value.trim();
    const titleBad = !title;
    setFieldError("titleCard", "titleError", titleBad);
    if (titleBad) {
        const el = document.getElementById("title");
        el.scrollIntoView({ behavior: "smooth", block: "center" });
        try { el.focus({ preventScroll: true }); } catch (_) { el.focus(); }
    }
    return !titleBad;
}

function saveHero() {
    if (!validateHeroForm()) {
        showStatus("لطفاً عنوان هیرو را وارد کنید.", false);
        return;
    }

    const saveBtn = document.querySelector('.btn-save');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = 'در حال ذخیره...';
    showStatus("در حال ذخیره...", "info");

    const formData = new FormData();
    formData.append("title", document.getElementById("title").value);
    formData.append("description", document.getElementById("editor").innerHTML);
    formData.append("link", document.getElementById("hero_link").value);
    formData.append("category", document.getElementById("category").value);
    formData.append("publish_date", document.getElementById("publish_date").value);

    const imageFile = document.getElementById("featured_image").files[0];
    if (imageFile) formData.append("image", imageFile);

    formData.append("csrf_token", <?= json_encode(csrf_token()) ?>);

    fetch('./hero-save.php', { method: "POST", body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            showStatus(data.message, true);
            // پاک کردن فرم
            document.getElementById("title").value = '';
            document.getElementById("editor").innerHTML = '';
            document.getElementById("hero_link").value = '';
            document.getElementById("has_link").checked = false;
            toggleLinkBox();
            removeImage();
        } else {
            showStatus(data.message, false);
        }
    })
    .catch(error => {
        showStatus("خطا در ارتباط با سرور: " + error, false);
        console.error("Error:", error);
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    });
}
</script>

</body>
</html>

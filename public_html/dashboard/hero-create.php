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

.btn-insert {
    background: transparent; border: 1px dashed var(--primary-color); color: var(--primary-color);
    padding: 8px 12px; border-radius: var(--radius-sm); cursor: pointer; font-family: inherit;
    font-size: 13px; font-weight: 600; transition: all var(--anim-fast);
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-insert:hover { background: var(--primary-color); color: #fff; }

/* ===== دراپ‌داون سفارشی مدرن ===== */
.msel { position: relative; width: 100%; font-family: inherit; }
.msel-trigger {
    width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 8px;
    background: var(--input-bg); color: var(--text-color);
    border: 1px solid var(--border-color); border-radius: var(--radius-sm);
    padding: 11px 12px; font-size: 15px; font-family: inherit; cursor: pointer; text-align: right;
    transition: border-color var(--anim-fast), box-shadow var(--anim-fast);
}
.msel-trigger:hover { border-color: color-mix(in srgb, var(--primary-color) 45%, var(--border-color)); }
.msel.open .msel-trigger { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(0,125,117,0.14); }
.msel-trigger .msel-value { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msel-trigger.is-placeholder .msel-value { color: var(--muted-color); }
.msel-caret { color: var(--muted-color); transition: transform var(--anim-fast); flex-shrink: 0; display: flex; }
.msel-caret svg { width: 18px; height: 18px; }
.msel.open .msel-caret { transform: rotate(180deg); color: var(--primary-color); }
.msel-menu {
    position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 50;
    background: var(--panel-bg); border: 1px solid var(--border-color);
    border-radius: var(--radius-sm); box-shadow: var(--shadow-md);
    padding: 6px; max-height: 240px; overflow-y: auto;
    opacity: 0; visibility: hidden; transform: translateY(-6px);
    transition: opacity var(--anim-fast), transform var(--anim-fast), visibility var(--anim-fast);
}
.msel.open .msel-menu { opacity: 1; visibility: visible; transform: translateY(0); }
.msel-opt {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    padding: 10px 11px; border-radius: 8px; cursor: pointer; font-size: 14px; color: var(--text-color);
    transition: background var(--anim-fast); user-select: none;
}
.msel-opt:hover, .msel-opt.is-active { background: rgba(0,125,117,0.10); }
.msel-opt.is-selected { color: var(--primary-color); font-weight: 700; }
.msel-opt .msel-check { opacity: 0; color: var(--primary-color); display: flex; flex-shrink: 0; }
.msel-opt .msel-check svg { width: 16px; height: 16px; }
.msel-opt.is-selected .msel-check { opacity: 1; }
.msel.field-invalid .msel-trigger { border-color: var(--danger) !important; box-shadow: 0 0 0 3px rgba(231,76,60,0.14) !important; }

/* ===== مودال تقویم شمسی ===== */
.date-modal {
    position: fixed; inset: 0; background: var(--modal-overlay);
    display: none; align-items: center; justify-content: center; z-index: 10000; padding: 12px;
}
.date-modal.show { display: flex; }
.date-modal.show .date-modal-box { transform: translateY(0) scale(1); opacity: 1; }
.date-modal-box {
    background: var(--panel-bg); color: var(--text-color); width: 100%; max-width: 360px;
    border-radius: 16px; border: 1px solid var(--border-color); padding: 16px; box-shadow: var(--shadow-md);
    transform: translateY(10px) scale(0.98); opacity: 0;
    transition: transform var(--anim-mid) ease, opacity var(--anim-mid) ease;
}
.date-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.date-modal-title { margin: 0; color: var(--primary-color); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 7px; }
.date-modal-title svg { width: 18px; height: 18px; }
.dp-close {
    width: 32px; height: 32px; border-radius: 9px; border: 1px solid var(--border-color);
    background: var(--surface-2); color: var(--muted-color); cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all var(--anim-fast);
}
.dp-close:hover { background: var(--danger); color: #fff; border-color: var(--danger); }
.dp-close svg { width: 15px; height: 15px; }
.dp-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 8px; }
.dp-nav-btn {
    width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--border-color);
    background: var(--surface-2); color: var(--text-color); cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all var(--anim-fast); flex-shrink: 0;
}
.dp-nav-btn:hover { background: var(--primary-color); color: #fff; border-color: var(--primary-color); }
.dp-nav-btn svg { width: 17px; height: 17px; }
.dp-month-label { font-size: 14px; font-weight: 800; color: var(--text-color); text-align: center; flex: 1; }
.dp-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 4px; }
.dp-weekdays span { text-align: center; font-size: 11px; font-weight: 700; color: var(--muted-color); padding: 4px 0; }
.dp-days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
.dp-day {
    aspect-ratio: 1; border: none; background: transparent; color: var(--text-color);
    border-radius: 10px; cursor: pointer; font-family: inherit; font-size: 13px; font-weight: 600;
    display: flex; align-items: center; justify-content: center; transition: background var(--anim-fast), color var(--anim-fast), transform var(--anim-fast);
}
.dp-day:hover { background: rgba(0,125,117,0.10); }
.dp-day.is-empty { background: transparent; cursor: default; pointer-events: none; }
.dp-day.is-today { box-shadow: inset 0 0 0 1.5px var(--secondary-color); color: var(--secondary-color); }
.dp-day.is-selected { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: #fff; box-shadow: var(--shadow-sm); transform: scale(1.04); }
.dp-time { margin-top: 14px; border-top: 1px solid var(--border-color); padding-top: 14px; }
.dp-time-head { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--primary-color); margin-bottom: 10px; }
.dp-time-head svg { width: 15px; height: 15px; }
.dp-time-row { display: flex; align-items: center; justify-content: center; gap: 10px; }
.dp-stepper { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.dp-step-btn {
    width: 30px; height: 24px; border-radius: 7px; border: 1px solid var(--border-color);
    background: var(--surface-2); color: var(--text-color); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all var(--anim-fast);
}
.dp-step-btn:hover { background: var(--primary-color); color: #fff; border-color: var(--primary-color); }
.dp-step-btn svg { width: 14px; height: 14px; }
.dp-time-val {
    width: 56px; text-align: center; font-size: 22px; font-weight: 800; color: var(--text-color);
    background: var(--surface-2); border: 1px solid var(--border-color); border-radius: 10px; padding: 6px 0; font-family: inherit;
}
.dp-time-colon { font-size: 22px; font-weight: 800; color: var(--primary-color); }
.date-actions { margin-top: 16px; display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap; }

.publish-date-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.publish-date-row .input { flex: 1; min-width: 130px; }

/* تا منوی دراپ‌داون سفارشی بریده نشود */
.settings-card { overflow: visible; }
.settings-card .input-group { position: relative; }

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
                    <label class="field-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        تاریخ و زمان انتشار
                    </label>
                    <div class="publish-date-row">
                        <input type="hidden" id="publish_date">
                        <input type="text" id="publish_date_display" class="input" readonly placeholder="تاریخ و زمان شمسی را انتخاب کنید">
                        <button type="button" class="btn-insert" onclick="openDatePicker()">📅 انتخاب</button>
                        <button type="button" class="btn-insert" onclick="setNow()">همین الان</button>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<!-- مودال انتخاب تاریخ (تقویم شمسی مدرن) -->
<div id="dateModal" class="date-modal" onclick="if(event.target===this) closeDatePicker()">
    <div class="date-modal-box">
        <div class="date-header">
            <h4 class="date-modal-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                تقویم شمسی
            </h4>
            <button type="button" class="dp-close" onclick="closeDatePicker()" aria-label="بستن">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="dp-nav">
            <button type="button" class="dp-nav-btn" onclick="dpChangeMonth(1)" aria-label="ماه بعد">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div class="dp-month-label" id="dpMonthLabel">—</div>
            <button type="button" class="dp-nav-btn" onclick="dpChangeMonth(-1)" aria-label="ماه قبل">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>

        <div class="dp-weekdays">
            <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span>
        </div>
        <div class="dp-days" id="dpDays"></div>

        <div class="dp-time">
            <div class="dp-time-head">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                انتخاب زمان
            </div>
            <div class="dp-time-row">
                <div class="dp-stepper">
                    <button type="button" class="dp-step-btn" onclick="dpStepTime('hh', 1)" aria-label="ساعت بیشتر"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></button>
                    <input type="text" class="dp-time-val" id="dpHourVal" value="۰۰" readonly inputmode="numeric">
                    <button type="button" class="dp-step-btn" onclick="dpStepTime('hh', -1)" aria-label="ساعت کمتر"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>
                </div>
                <span class="dp-time-colon">:</span>
                <div class="dp-stepper">
                    <button type="button" class="dp-step-btn" onclick="dpStepTime('mm', 1)" aria-label="دقیقه بیشتر"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></button>
                    <input type="text" class="dp-time-val" id="dpMinuteVal" value="۰۰" readonly inputmode="numeric">
                    <button type="button" class="dp-step-btn" onclick="dpStepTime('mm', -1)" aria-label="دقیقه کمتر"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>
                </div>
            </div>
        </div>

        <div class="date-actions">
            <button type="button" class="btn-insert" onclick="setPickerNow()">اکنون</button>
            <button type="button" class="btn-insert" onclick="closeDatePicker()">انصراف</button>
            <button type="button" class="btn btn-save" style="padding:10px 16px;" onclick="applyDatePicker()">تایید</button>
        </div>

        <input type="hidden" id="jy"><input type="hidden" id="jm"><input type="hidden" id="jd">
        <input type="hidden" id="hh"><input type="hidden" id="mm">
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

/* ===================== دیت‌پیکر شمسی مدرن ===================== */
function setNow() { setDateFromGregorianDate(new Date()); }
function toFaDigits(value) { return String(value).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]); }
function pad2(num) { return String(num).padStart(2, '0'); }

function gregorianToJalali(gy, gm, gd) {
    const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    let jy;
    if (gy > 1600) { jy = 979; gy -= 1600; } else { jy = 0; gy -= 621; }
    const gy2 = gm > 2 ? gy + 1 : gy;
    let days = (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100)
        + Math.floor((gy2 + 399) / 400) - 80 + gd + g_d_m[gm - 1];
    jy += 33 * Math.floor(days / 12053); days %= 12053;
    jy += 4 * Math.floor(days / 1461); days %= 1461;
    if (days > 365) { jy += Math.floor((days - 1) / 365); days = (days - 1) % 365; }
    let jm, jd;
    if (days < 186) { jm = 1 + Math.floor(days / 31); jd = 1 + (days % 31); }
    else { jm = 7 + Math.floor((days - 186) / 30); jd = 1 + ((days - 186) % 30); }
    return [jy, jm, jd];
}

function jalaliToGregorian(jy, jm, jd) {
    let gy;
    if (jy > 979) { gy = 1600; jy -= 979; } else { gy = 621; }
    let days = (365 * jy) + (Math.floor(jy / 33) * 8) + Math.floor(((jy % 33) + 3) / 4) + 78 + jd
        + (jm < 7 ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
    gy += 400 * Math.floor(days / 146097); days %= 146097;
    if (days > 36524) { gy += 100 * Math.floor(--days / 36524); days %= 36524; if (days >= 365) days++; }
    gy += 4 * Math.floor(days / 1461); days %= 1461;
    if (days > 365) { gy += Math.floor((days - 1) / 365); days = (days - 1) % 365; }
    let gd = days + 1;
    const sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    let gm;
    for (gm = 1; gm <= 12; gm++) { if (gd <= sal_a[gm]) break; gd -= sal_a[gm]; }
    return [gy, gm, gd];
}

function getJalaliMonthDays(jy, jm) {
    if (jm <= 6) return 31;
    if (jm <= 11) return 30;
    const leap = (((jy + 38) * 682) % 2816) < 682;
    return leap ? 30 : 29;
}

function renderPublishDisplay(jy, jm, jd, hh, mm) {
    document.getElementById("publish_date_display").value = toFaDigits(`${jy}/${pad2(jm)}/${pad2(jd)} - ${pad2(hh)}:${pad2(mm)}`);
}

function setDateFromGregorianDate(dateObj) {
    const gy = dateObj.getFullYear(), gm = dateObj.getMonth() + 1, gd = dateObj.getDate();
    const hh = dateObj.getHours(), mm = dateObj.getMinutes();
    const [jy, jm, jd] = gregorianToJalali(gy, gm, gd);
    document.getElementById("publish_date").value = `${gy}-${pad2(gm)}-${pad2(gd)} ${pad2(hh)}:${pad2(mm)}:00`;
    renderPublishDisplay(jy, jm, jd, hh, mm);
    setPickerValues(jy, jm, jd, hh, mm);
}

const PERSIAN_MONTHS = ["فروردین","اردیبهشت","خرداد","تیر","مرداد","شهریور","مهر","آبان","آذر","دی","بهمن","اسفند"];
let dpViewYear = 0, dpViewMonth = 1;
function dpGet(id){ return parseInt(document.getElementById(id).value || "0", 10); }
function dpSet(id, v){ document.getElementById(id).value = String(v); }

function jalaliFirstDow(jy, jm) {
    const [gy, gm, gd] = jalaliToGregorian(jy, jm, 1);
    const js = new Date(gy, gm - 1, gd).getDay();
    return (js + 1) % 7;
}

function setPickerValues(jy, jm, jd, hh, mm) {
    dpSet("jy", jy); dpSet("jm", jm); dpSet("jd", jd); dpSet("hh", hh); dpSet("mm", mm);
    dpViewYear = jy; dpViewMonth = jm;
    dpRenderTime(); dpRenderCalendar();
}

function dpRenderCalendar() {
    const label = document.getElementById("dpMonthLabel");
    if (label) label.textContent = `${PERSIAN_MONTHS[dpViewMonth - 1]} ${toFaDigits(dpViewYear)}`;
    const grid = document.getElementById("dpDays");
    if (!grid) return;
    grid.innerHTML = "";
    const lead = jalaliFirstDow(dpViewYear, dpViewMonth);
    const days = getJalaliMonthDays(dpViewYear, dpViewMonth);
    const now = new Date();
    const [tjy, tjm, tjd] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
    const selJy = dpGet("jy"), selJm = dpGet("jm"), selJd = dpGet("jd");
    for (let i = 0; i < lead; i++) { const sp = document.createElement("span"); sp.className = "dp-day is-empty"; grid.appendChild(sp); }
    for (let d = 1; d <= days; d++) {
        const btn = document.createElement("button");
        btn.type = "button"; btn.className = "dp-day"; btn.textContent = toFaDigits(d);
        if (dpViewYear === tjy && dpViewMonth === tjm && d === tjd) btn.classList.add("is-today");
        if (dpViewYear === selJy && dpViewMonth === selJm && d === selJd) btn.classList.add("is-selected");
        btn.addEventListener("click", () => dpSelectDay(d));
        grid.appendChild(btn);
    }
}

function dpSelectDay(d) { dpSet("jy", dpViewYear); dpSet("jm", dpViewMonth); dpSet("jd", d); dpRenderCalendar(); }
function dpChangeMonth(dir) {
    dpViewMonth += dir;
    if (dpViewMonth > 12) { dpViewMonth = 1; dpViewYear++; }
    else if (dpViewMonth < 1) { dpViewMonth = 12; dpViewYear--; }
    dpRenderCalendar();
}
function dpRenderTime() {
    document.getElementById("dpHourVal").value = toFaDigits(pad2(dpGet("hh")));
    document.getElementById("dpMinuteVal").value = toFaDigits(pad2(dpGet("mm")));
}
function dpStepTime(unit, dir) {
    let v = dpGet(unit) + dir; const max = unit === "hh" ? 24 : 60;
    v = (v + max) % max; dpSet(unit, v); dpRenderTime();
}
function openDatePicker() { document.getElementById("dateModal").classList.add("show"); }
function closeDatePicker() { document.getElementById("dateModal").classList.remove("show"); }
function setPickerNow() {
    const now = new Date();
    const [jy, jm, jd] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
    setPickerValues(jy, jm, jd, now.getHours(), now.getMinutes());
}
function applyDatePicker() {
    const jy = dpGet("jy"), jm = dpGet("jm"), jd = dpGet("jd"), hh = dpGet("hh"), mm = dpGet("mm");
    if (!jy || !jm || !jd) { closeDatePicker(); return; }
    const [gy, gm, gd] = jalaliToGregorian(jy, jm, jd);
    document.getElementById("publish_date").value = `${gy}-${pad2(gm)}-${pad2(gd)} ${pad2(hh)}:${pad2(mm)}:00`;
    renderPublishDisplay(jy, jm, jd, hh, mm);
    closeDatePicker();
}

/* ===================== دراپ‌داون سفارشی ===================== */
const MSEL_CARET = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
const MSEL_CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

function enhanceSelect(select){
    if (!select || select.dataset.enhanced) return;
    select.dataset.enhanced = "1";
    const placeholderText = (select.options[0] && select.options[0].value === "") ? select.options[0].textContent : "";
    const wrap = document.createElement("div");
    wrap.className = "msel";
    const trigger = document.createElement("button");
    trigger.type = "button"; trigger.className = "msel-trigger";
    trigger.innerHTML = '<span class="msel-value"></span><span class="msel-caret">' + MSEL_CARET + '</span>';
    const menu = document.createElement("div");
    menu.className = "msel-menu"; menu.setAttribute("role", "listbox");
    Array.from(select.options).forEach(opt => {
        if (opt.value === "" && placeholderText) return;
        const o = document.createElement("div");
        o.className = "msel-opt"; o.setAttribute("role", "option"); o.dataset.value = opt.value;
        o.innerHTML = '<span>' + opt.textContent + '</span><span class="msel-check">' + MSEL_CHECK + '</span>';
        o.addEventListener("click", () => {
            select.value = opt.value;
            select.dispatchEvent(new Event("change", { bubbles: true }));
            syncMsel(); wrap.classList.remove("open");
        });
        menu.appendChild(o);
    });
    select.style.display = "none";
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(trigger); wrap.appendChild(menu); wrap.appendChild(select);
    function syncMsel(){
        const valEl = trigger.querySelector(".msel-value");
        const sel = select.options[select.selectedIndex];
        const isPlaceholder = !select.value && placeholderText;
        valEl.textContent = isPlaceholder ? placeholderText : (sel ? sel.textContent : "");
        trigger.classList.toggle("is-placeholder", !!isPlaceholder);
        menu.querySelectorAll(".msel-opt").forEach(o => o.classList.toggle("is-selected", o.dataset.value === select.value));
        wrap.classList.toggle("field-invalid", select.classList.contains("field-invalid"));
    }
    trigger.addEventListener("click", (e) => {
        e.stopPropagation();
        document.querySelectorAll(".msel.open").forEach(m => { if (m !== wrap) m.classList.remove("open"); });
        wrap.classList.toggle("open");
    });
    select.addEventListener("change", syncMsel);
    select._mselSync = syncMsel;
    syncMsel();
}
document.addEventListener("click", () => document.querySelectorAll(".msel.open").forEach(m => m.classList.remove("open")));
document.addEventListener("keydown", (e) => { if (e.key === "Escape") document.querySelectorAll(".msel.open").forEach(m => m.classList.remove("open")); });

document.addEventListener("DOMContentLoaded", () => {
    setDateFromGregorianDate(new Date());
    enhanceSelect(document.getElementById("category"));
});
</script>

</body>
</html>

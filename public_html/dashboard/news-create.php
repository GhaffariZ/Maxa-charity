<?php
require_once __DIR__ . '/_guard.php';
dash_require('news');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$news_data = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$news_data) {
        die("خبر یافت نشد.");
    }
}
// دریافت لیست دسته‌بندی‌ها از جدول جدید
$categories = [];
try {
    $cat_stmt = $pdo->query("SELECT id, name FROM news_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}
// بارگذاری مسیر تصویر موجود برای نمایش در حالت ویرایش
$existing_image_url = '';
if ($id > 0 && !empty($news_data['featured_image'])) {
    $existing_image_url = "/uploads/news/{$news_data['news_code']}/{$news_data['featured_image']}";
}

$tags = [];
$selectedCategoryId = (int)($news_data['category_id'] ?? 0);

// آماده‌سازی زمان برای فرمت datetime-local مرورگر
$pub_val = '';
if ($news_data && !empty($news_data['publish_date'])) {
    $pub_val = date('Y-m-d\TH:i', strtotime($news_data['publish_date']));
}

?>
<!DOCTYPE html>
<link rel="stylesheet" href="/font.css">
<html lang="fa" dir="rtl">
<head>
        <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

<style>
  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }
</style>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>ایجاد خبر</title>

<style>
:root {
    /* پالت رنگی لایت مود (برگرفته از لوگو مکسا) */
    --primary-color: #007D75; /* سبز-آبی مکسا */
    --secondary-color: #F79F1F; /* نارنجی مکسا */
    --bg-color: #f4f7f6;
    --text-color: #333333;
    --panel-bg: #ffffff;
    --border-color: #dddddd;
    --input-bg: #ffffff;
    --header-text: #007D75;
    --btn-hover-opacity: 0.9;
    --modal-overlay: rgba(0,0,0,0.6);
    --anim-fast: 220ms;
    --anim-mid: 420ms;
    --anim-slow: 700ms;
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
    --modal-overlay: rgba(0,0,0,0.8);
}

* {
    box-sizing: border-box;
}

body {
    background-color: var(--bg-color);
    color: var(--text-color);
    transition: background-color 0.3s, color 0.3s;
    /*font-family: Tahoma, Arial, sans-serif;*/
    margin: 0;
    padding: 0;
    min-height: 100vh;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 15px;
    animation: pageRise var(--anim-slow) cubic-bezier(.2,.8,.2,1) both;
}

.card {
    background-color: var(--panel-bg);
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    animation: cardPop var(--anim-mid) cubic-bezier(.2,.8,.2,1) both;
}

.card::before {
    content: "";
    position: absolute;
    inset: -1px;
    background: linear-gradient(120deg, rgba(0,125,117,.12), rgba(247,159,31,.12), rgba(0,125,117,.08));
    opacity: 0;
    transition: opacity var(--anim-mid) ease;
    pointer-events: none;
}

.card:hover::before {
    opacity: 1;
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
    border-radius: 50%; /* دایره‌ای کردن دکمه */
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
    transition: border-color var(--anim-fast), box-shadow var(--anim-fast), transform var(--anim-fast);
}

.input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.15);
    transform: translateY(-1px);
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
    transition: all var(--anim-fast);
}

.tb-btn:hover {
    background-color: var(--primary-color);
    color: #fff;
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

.tb-btn svg {
    stroke: currentColor;
}

.tb-btn:hover svg {
    stroke: #fff;
}

.tb-btn svg circle, .tb-btn svg path[fill="#333"] {
    fill: currentColor;
    stroke: none;
}

.tb-btn:hover svg circle, .tb-btn:hover svg path[fill="#333"] {
    fill: #fff;
}

.tb-select {
    background-color: var(--input-bg);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 6px;
    font-family: inherit;
}

.tb-select.small {
    width: 70px;
}

#editor {
    min-height: 300px;
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

#editor img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

.color-row {
    display: flex;
    gap: 4px;
    margin-right: auto;
}

.color-box {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    cursor: pointer;
    border: 1px solid rgba(0,0,0,0.1);
}

/* دکمه‌ها */
.builder-toolbar {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    font-size: 15px;
    transition: transform var(--anim-fast), box-shadow var(--anim-fast), opacity var(--anim-fast);
    font-family: inherit;
    flex-grow: 1;
    text-align: center;
}

.btn:hover {
    opacity: var(--btn-hover-opacity);
    transform: translateY(-2px);
    box-shadow: 0 10px 18px rgba(0,0,0,0.12);
}

.btn-save {
    background: linear-gradient(135deg, var(--primary-color), #009b90);
    color: white;
}

.btn-preview {
    background: linear-gradient(135deg, var(--secondary-color), #ffbf5a);
    color: white;
}

.btn-insert {
    background-color: var(--panel-bg);
    border: 1px dashed var(--primary-color);
    color: var(--primary-color);
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all var(--anim-fast);
    font-family: inherit;
}

.btn-insert:hover {
    background-color: var(--primary-color);
    color: #fff;
    transform: translateY(-1px);
}

/* تصاویر شاخص و گالری */
input[type="file"] {
    width: 100%;
    padding: 10px;
    background: var(--input-bg);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: 6px;
}

.image-preview-wrapper {
    margin-top: 10px;
}

.image-preview-item {
    position: relative;
    display: inline-block;
}

.image-preview-item img {
    max-width: 150px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.image-remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: red;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
}

.gallery-row {
    display: flex;
    gap: 10px;
    margin: 10px 0;
    flex-wrap: wrap;
}

.gallery-row img {
    flex: 1;
    min-width: 30%;
    max-width: 100%;
    border-radius: 8px;
    object-fit: cover;
}

.status-msg {
    margin-top: 15px;
    padding: 10px;
    border-radius: 6px;
    display: none;
}

.status-msg:not(:empty) {
    display: block;
    animation: toastIn 340ms ease both;
}

.status-err { background: #ffeaa7; color: #d63031; border: 1px solid #fdcb6e; }
.status-ok { background: #55efc4; color: #00b894; border: 1px solid #00b894; }

/* نوار پیشرفت آپلود */
.upload-progress {
    margin-top: 12px;
    display: none;
}
.upload-progress.active { display: block; }
.upload-progress .bar-track {
    width: 100%;
    height: 10px;
    background: #e9ecef;
    border-radius: 6px;
    overflow: hidden;
}
.upload-progress .bar-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(135deg, var(--primary-color), #009b90);
    border-radius: 6px;
    transition: width 0.15s ease;
}
.upload-progress .bar-label {
    margin-top: 6px;
    font-size: 13px;
    color: var(--primary-color);
    text-align: center;
}

.publish-date-row {
    display: flex;
    gap: 8px;
    align-items: center;
}

.publish-date-row .input {
    flex: 1;
}

.date-picker-btn {
    background-color: var(--panel-bg);
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
    padding: 10px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-family: inherit;
    white-space: nowrap;
}

.date-picker-btn:hover {
    background-color: var(--primary-color);
    color: #fff;
}

.date-modal {
    position: fixed;
    inset: 0;
    background: var(--modal-overlay);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: 12px;
}

.date-modal.show {
    display: flex;
}

.date-modal.show .date-modal-box {
    transform: translateY(0) scale(1);
    opacity: 1;
}

.date-modal-box {
    background: var(--panel-bg);
    color: var(--text-color);
    width: 100%;
    max-width: 380px;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 14px;
    box-shadow: 0 20px 35px rgba(0,0,0,0.18);
    transform: translateY(10px) scale(0.98);
    opacity: 0;
    transition: transform var(--anim-mid) ease, opacity var(--anim-mid) ease;
}

.date-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.date-modal-title {
    margin: 0;
    color: var(--primary-color);
    font-size: 16px;
    font-weight: 800;
}

.date-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.date-grid .input-group {
    margin-bottom: 0;
}

.date-actions {
    margin-top: 14px;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.calendar-box,
.time-box {
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 10px;
    background: var(--input-bg);
}

.time-box {
    margin-top: 10px;
}

.time-box-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 8px;
}

.time-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 8px;
    align-items: center;
}

.time-separator {
    font-size: 20px;
    font-weight: 800;
    color: var(--primary-color);
}

/* پیش‌نمایش مودال */
#previewModal {
    position: fixed;
    inset: 0;
    background: var(--modal-overlay);
    display: none;
    z-index: 9999;
    overflow-y: auto;
    padding: 20px;
}

.modal-content {
    max-width: 900px;
    margin: 30px auto;
    background: var(--panel-bg);
    color: var(--text-color);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    position: relative;
    transform: translateY(14px) scale(0.98);
    opacity: 0;
    transition: transform var(--anim-mid) ease, opacity var(--anim-mid) ease;
}

#previewModal.show .modal-content {
    transform: translateY(0) scale(1);
    opacity: 1;
}

/* استایل‌های ریسپانسیو (برای موبایل) */
@media (max-width: 768px) {
    .col-4, .col-6 { width: 100%; }
    
    .panel-heading {
        padding: 15px;
    }

    .btn {
        width: 100%;
    }
    
    .color-row {
        margin-top: 10px;
        width: 100%;
        justify-content: space-between;
    }
    
    .modal-content {
        padding: 15px;
        margin: 10px auto;
    }
    
    .tb-btn {
        flex: 1 1 calc(16.66% - 6px); /* شش دکمه در هر ردیف موبایل */
    }
    
    .tb-select {
        flex: 1 1 100%;
        margin-top: 5px;
    }
}

.panel-body > .input-group,
.panel-body > .row,
.panel-body > .builder-toolbar,
.panel-body > #statusBox {
    opacity: 0;
    transform: translateY(10px);
    animation: revealItem 500ms cubic-bezier(.2,.8,.2,1) both;
}

.panel-body > .input-group:nth-of-type(1) { animation-delay: 60ms; }
.panel-body > .input-group:nth-of-type(2) { animation-delay: 120ms; }
.panel-body > .input-group:nth-of-type(3) { animation-delay: 180ms; }
.panel-body > .row:nth-of-type(1) { animation-delay: 220ms; }
.panel-body > .row:nth-of-type(2) { animation-delay: 260ms; }
.panel-body > .input-group:nth-of-type(4) { animation-delay: 300ms; }
.panel-body > .builder-toolbar { animation-delay: 340ms; }

@keyframes pageRise {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes cardPop {
    from { opacity: 0; transform: translateY(16px) scale(0.985); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes revealItem {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes toastIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation: none !important;
        transition: none !important;
    }
}
</style>
</head>

<body>

<div class="container">
<div class="card">

<div class="panel-heading">
    <h3 class="panel-title">ایجاد خبر جدید</h3>
    <!-- دکمه‌ی تغییر تم حذف شد — تم از «داشبورد مدیریت» کنترل می‌شود -->
</div>

<div class="panel-body">
    <div class="input-group">
        <label>عنوان خبر</label>
        <input type="text" class="input" id="title" value="<?= $news_data ? htmlspecialchars($news_data['title']) : '' ?>">
    </div>

    <div class="input-group">
        <label>متن خبر</label>

        <div class="editor-toolbar">

            <button type="button" onclick="format('bold')" class="tb-btn">
                <svg width="18" viewBox="0 0 24 24"><path d="M7 5v14h6a4 4 0 0 0 0-8H7m6 0a4 4 0 0 0 0-8H7" fill="none" stroke-width="2"/></svg>
            </button>

            <button type="button" onclick="format('italic')" class="tb-btn">
                <svg width="18" viewBox="0 0 24 24"><line x1="19" y1="4" x2="10" y2="4" stroke-width="2"/><line x1="14" y1="20" x2="5" y2="20" stroke-width="2"/><line x1="15" y1="4" x2="9" y2="20" stroke-width="2"/></svg>
            </button>

            <button type="button" onclick="format('underline')" class="tb-btn">
                <svg width="18" viewBox="0 0 24 24"><path d="M6 4v6a6 6 0 0 0 12 0V4" fill="none" stroke-width="2"/><line x1="4" y1="20" x2="20" y2="20" stroke-width="2"/></svg>
            </button>

            <select id="fontSelect" onchange="setFont(this)" class="tb-select">
                <option value="">فونت</option>
                <option value="Tahoma">Tahoma</option>
                <option value="Arial">Arial</option>
                <option value="Vazirmatn">Vazirmatn</option>
                <option value="Sahel">Sahel</option>
                <option value="Shabnam">Shabnam</option>
            </select>

            <select id="fontSizeSelect" onchange="setFontSize(this)" class="tb-select small">
                <option value="">سایز</option>
                <option value="12">12</option>
                <option value="14">14</option>
                <option value="16">16</option>
                <option value="18">18</option>
                <option value="20">20</option>
                <option value="24">24</option>
                <option value="28">28</option>
            </select>

            <select id="headingSelect" onchange="setHeading(this)" class="tb-select small">
                <option value="p"> عادی </option>
                <option value="h2">H2</option>
                <option value="h3">H3</option>
                <option value="h4">H4</option>
                <option value="h5">H5</option>
                <option value="h6">H6</option>
            </select>

            <button type="button" onclick="format('insertUnorderedList')" class="tb-btn">
                <svg width="18" viewBox="0 0 24 24">
                    <circle cx="5" cy="6" r="2" fill="#333"/>
                    <circle cx="5" cy="12" r="2" fill="#333"/>
                    <circle cx="5" cy="18" r="2" fill="#333"/>
                    <line x1="10" y1="6" x2="20" y2="6" stroke-width="2"/>
                    <line x1="10" y1="12" x2="20" y2="12" stroke-width="2"/>
                    <line x1="10" y1="18" x2="20" y2="18" stroke-width="2"/>
                </svg>
            </button>

            <button type="button" onclick="insertLink()" class="tb-btn">
                <svg width="18" viewBox="0 0 24 24">
                    <path d="M10 14a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1" fill="none" stroke-width="2"/>
                    <path d="M14 10a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1" fill="none" stroke-width="2"/>
                </svg>
            </button>

            <button type="button" onclick="clearFormat()" class="tb-btn">
                <svg width="18" viewBox="0 0 24 24">
                    <path d="M3 6h18M8 6v12m8-12v12" stroke-width="2" fill="none"/>
                    <line x1="4" y1="18" x2="20" y2="4" stroke="red" stroke-width="2"/>
                </svg>
            </button>

            <button type="button" onclick="alignLeft()" class="tb-btn">
                <svg width="18" viewBox="0 0 24 24">
                    <line x1="3" y1="6" x2="21" y2="6" stroke-width="2"/>
                    <line x1="3" y1="12" x2="15" y2="12" stroke-width="2"/>
                    <line x1="3" y1="18" x2="18" y2="18" stroke-width="2"/>
                </svg>
            </button>

            <button type="button" onclick="alignCenter()" class="tb-btn">
                <svg width="18" viewBox="0 0 24 24">
                    <line x1="6" y1="6" x2="18" y2="6" stroke-width="2"/>
                    <line x1="4" y1="12" x2="20" y2="12" stroke-width="2"/>
                    <line x1="6" y1="18" x2="18" y2="18" stroke-width="2"/>
                </svg>
            </button>

            <button type="button" onclick="alignRight()" class="tb-btn">
                <svg width="18" viewBox="0 0 24 24">
                    <line x1="21" y1="6" x2="3" y2="6" stroke-width="2"/>
                    <line x1="21" y1="12" x2="9" y2="12" stroke-width="2"/>
                    <line x1="21" y1="18" x2="6" y2="18" stroke-width="2"/>
                </svg>
            </button>

            <button type="button" onclick="alignJustify()" class="tb-btn">
                <svg width="18" viewBox="0 0 24 24">
                    <line x1="3" y1="6"  x2="21" y2="6"  stroke-width="2"/>
                    <line x1="3" y1="12" x2="21" y2="12" stroke-width="2"/>
                    <line x1="3" y1="18" x2="21" y2="18" stroke-width="2"/>
                </svg>
            </button>

            <div class="color-row">
                <div class="color-box" style="background:#000" onclick="applyColor('#000')"></div>
                <div class="color-box" style="background:#333" onclick="applyColor('#333')"></div>
                <div class="color-box" style="background:#007D75" onclick="applyColor('#007D75')"></div>
                <div class="color-box" style="background:#009688" onclick="applyColor('#009688')"></div>
                <div class="color-box" style="background:#1565c0" onclick="applyColor('#1565c0')"></div>
                <div class="color-box" style="background:#c62828" onclick="applyColor('#c62828')"></div>
                <div class="color-box" style="background:#F79F1F" onclick="applyColor('#F79F1F')"></div>
                <div class="color-box" style="background:#6a1b9a" onclick="applyColor('#6a1b9a')"></div>
            </div>

        </div>

        <div id="editor" contenteditable="true"><?= $news_data ? $news_data['content'] : '' ?></div>
    </div>

    <div class="input-group">
        <label>کلمات کلیدی</label>
        <input type="text" class="input" id="keywords" value="<?= $news_data ? htmlspecialchars($news_data['keywords']) : '' ?>">
    </div>

 <div class="row">
    <div class="col-6">
        <div class="input-group">
            <label>📷 تصویر شاخص</label>
            <input type="file" id="featured_image" name="featured_image" accept="image/*">
            <input type="hidden" id="remove_featured_flag" name="remove_featured_flag" value="0">

            <div id="featuredPreview">
                <?php if (!empty($existing_image_url)): ?>
                    <div class="image-preview-item">
                        <img src="<?= htmlspecialchars($existing_image_url) ?>" alt="تصویر شاخص">
                        <button class="image-remove-btn" type="button" onclick="removeFeatured()">×</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="input-group">
            <label>درج تصویر در متن</label>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button type="button" class="btn-insert" onclick="uploadSingleImage()">📷 درج تصویر تکی</button>
                <button type="button" class="btn-insert" onclick="uploadGalleryInline()">🖼️ درج گالری سه‌تایی</button>
            </div>
            <div style="font-size:12px;color:var(--text-color);opacity:0.7;margin-top:6px">
                تصاویر در محل کرسر وارد می‌شوند.
            </div>
        </div>
    </div>
</div>


    <div class="row">
        <div class="col-4">
            <div class="input-group">
                <label>نویسنده</label>
                <input type="text" class="input" id="author" value="<?= $news_data ? htmlspecialchars($news_data['author']) : '' ?>">
            </div>
        </div>

        <div class="col-4">
            <div class="input-group">
                <label>تاریخ و زمان انتشار</label>
                <div class="publish-date-row">
                    <input type="hidden" id="publish_date" name="publish_date" value="<?= htmlspecialchars($news_data['publish_date'] ?? '') ?>">
                    <input type="text" id="publish_date_display" class="input" readonly placeholder="تاریخ و زمان شمسی را انتخاب کنید">
                    <button type="button" class="date-picker-btn" onclick="openDatePicker()">📅 انتخاب</button>
                    <button type="button" class="btn-insert now-btn" onclick="setNow()">همین الان</button>
                </div>
            </div>
        </div>

        <div class="col-4">
            <div class="input-group">
                <label>دسته خبر</label>
                <select id="category_id" name="category_id" class="input" required>
                    <option value="">انتخاب دسته</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id'] ?>" <?= $selectedCategoryId === (int)$category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="input-group">
        <label>تگ‌های SEO</label>
        <input type="text" class="input" id="tags" value="<?= $news_data ? htmlspecialchars($news_data['tags']) : '' ?>" placeholder="مثلاً اقتصاد، مدیریت، دانشگاه...">
    </div>

    <div class="builder-toolbar">
        <button class="btn btn-save" onclick="saveNews()"><?= $id > 0 ? 'ویرایش خبر' : 'ثبت خبر نهایی' ?></button>
        <button type="button" class="btn btn-preview" onclick="openPreview()">پیش‌نمایش</button>
    </div>

    <div id="statusBox" class="status-msg"></div>

    <div id="uploadProgress" class="upload-progress">
        <div class="bar-track"><div id="uploadBarFill" class="bar-fill"></div></div>
        <div id="uploadBarLabel" class="bar-label">در حال آپلود...</div>
    </div>

</div> </div> </div> <div id="previewModal">
    <div class="modal-content">
        <button onclick="closePreview()" style="position:absolute;top:15px;left:15px;background:#e74c3c;color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-family:inherit;">بستن</button>
        <div id="previewContent" style="margin-top: 20px;"></div>
    </div>
</div>

<div id="dateModal" class="date-modal" onclick="if(event.target===this) closeDatePicker()">
    <div class="date-modal-box">
        <div class="date-header">
            <h4 class="date-modal-title">تقویم فارسی</h4>
            <button type="button" class="btn-insert" style="padding:6px 10px" onclick="closeDatePicker()">✕</button>
        </div>
        <div class="calendar-box">
            <div class="date-grid">
                <div class="input-group">
                    <label>سال</label>
                    <select id="jy" class="input"></select>
                </div>
                <div class="input-group">
                    <label>ماه</label>
                    <select id="jm" class="input"></select>
                </div>
                <div class="input-group">
                    <label>روز</label>
                    <select id="jd" class="input"></select>
                </div>
            </div>
        </div>
        <div class="time-box">
            <div class="time-box-title">انتخاب زمان</div>
            <div class="time-grid">
                <select id="hh" class="input"></select>
                <span class="time-separator">:</span>
                <select id="mm" class="input"></select>
            </div>
        </div>
        <div class="date-actions">
            <button type="button" class="btn-insert" onclick="setPickerNow()">اکنون</button>
            <button type="button" class="btn-insert" onclick="closeDatePicker()">انصراف</button>
            <button type="button" class="btn btn-save" style="flex-grow:0;padding:10px 16px;" onclick="applyDatePicker()">تایید</button>
        </div>
    </div>
</div>

<script>
/* ذخیره محدوده انتخاب */
let savedRange = null;
const editor = document.getElementById("editor");

editor.addEventListener("mouseup", saveSelection);
editor.addEventListener("keyup", saveSelection);
editor.addEventListener("focus", saveSelection);

function saveSelection(){
    const sel = window.getSelection();
    if(sel.rangeCount > 0){
        savedRange = sel.getRangeAt(0);
    }
}

function restoreSelection(){
    if(savedRange){
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(savedRange);
    }
}

/* ===================== ابزارهای پایه ===================== */
function format(cmd,val=null){
    editor.focus();
    restoreSelection();
    document.execCommand(cmd,false,val);
    saveSelection();
}

/* ===================== رنگ متن ===================== */
function applyColor(color){
    restoreSelection();
    document.execCommand("foreColor", false, color);
    saveSelection();
}

/* ===================== هدینگ ===================== */
function setHeading(selectEl){
    editor.focus();
    restoreSelection();
    const tag = selectEl.value;
    if(tag==="p"){
        document.execCommand("formatBlock",false,"p");
    }else{
        document.execCommand("formatBlock",false,tag);
    }
    saveSelection();
}

/* ===================== فونت ===================== */
function setFont(selectEl){
    const font = selectEl.value;
    if(!font) return;
    editor.focus();
    restoreSelection();
    document.execCommand("fontName",false,font);
    selectEl.value="";
    saveSelection();
}

/* ===================== سایز فونت ===================== */
function setFontSize(selectEl){
    const size = selectEl.value;
    if(!size) return;
    editor.focus();
    restoreSelection();
    document.execCommand("fontSize",false,"7");
    const fonts = editor.querySelectorAll("font[size='7']");
    fonts.forEach(f=>{
        f.removeAttribute("size");
        f.style.fontSize = size + "px";
    });
    selectEl.value="";
    saveSelection();
}

/* ===================== لینک ===================== */
function insertLink(){
    editor.focus();
    restoreSelection();
    const url = prompt("آدرس لینک:");
    if(url){
        document.execCommand("createLink",false,url);
    }
    saveSelection();
}

/* ===================== حذف فرمت ===================== */
function clearFormat(){
    editor.focus();
    restoreSelection();
    document.execCommand("removeFormat");
    saveSelection();
}

/* ===================== چینش متن ===================== */
function alignRight(){ format("justifyRight"); }
function alignLeft(){ format("justifyLeft"); }
function alignCenter(){ format("justifyCenter"); }
function alignJustify(){ format("justifyFull"); }

/* =================== تصویر شاخص =================== */
const featuredInput = document.getElementById("featured_image");
const featuredPreview = document.getElementById("featuredPreview");

featuredInput.onchange = function(){
    featuredPreview.innerHTML = "";
    document.getElementById("remove_featured_flag").value = "0";
    if (this.files[0]) {
        const reader = new FileReader();
        reader.onload = e=>{
            featuredPreview.innerHTML = `
                <div class="image-preview-item">
                    <img src="${e.target.result}">
                    <button class="image-remove-btn" type="button" onclick="removeFeatured()">×</button>
                </div>`;
        };
        reader.readAsDataURL(this.files[0]);
    }
};

function removeFeatured(){
    featuredInput.value = "";
    featuredPreview.innerHTML = "";
    document.getElementById("remove_featured_flag").value = "1";
}

/* ============== درج تصویر داخل متن ============== */
function uploadSingleImage(){
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";

    input.onchange = ()=>{
        const file = input.files[0];
        if (!file) return;

        const fd = new FormData();
        fd.append("image", file);

        fetch("upload-inline-image.php", {method:"POST", body:fd})
        .then(r=>r.json())
        .then(d=>{
            if (d.url) insertSingleImage(d.url);
        });
    };
    input.click();
}

function insertSingleImage(url){
    const img = document.createElement("img");
    img.src = url;

    restoreSelection();
    const sel = window.getSelection();
    if (sel.rangeCount){
        const range = sel.getRangeAt(0);
        range.insertNode(img);
    }
    saveSelection();
}

function uploadGalleryInline(){
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";
    input.multiple = true;

    input.onchange = ()=>{
        const files = Array.from(input.files);
        if (!files.length) return;

        let urls = [];

        Promise.all(files.map(file=>{
            const fd = new FormData();
            fd.append("image", file);
            return fetch("upload-inline-image.php", {method:"POST", body:fd})
                .then(r=>r.json())
                .then(d=>{ if (d.url) urls.push(d.url); });
        })).then(()=> insertGalleryRow(urls));
    };
    input.click();
}

function insertGalleryRow(urls){
    const row = document.createElement("div");
    row.className = "gallery-row";

    urls.slice(0,3).forEach(u=>{
        const img = document.createElement("img");
        img.src = u;
        row.appendChild(img);
    });

    restoreSelection();
    const sel = window.getSelection();
    if (sel.rangeCount){
        sel.getRangeAt(0).insertNode(row);
    }
    saveSelection();
}

/* =================== ذخیره خبر (بازطراحی شده) =================== */
function showStatus(msg, ok){
    const box = document.getElementById("statusBox");
    box.textContent = msg;
    box.className = "status-msg " + (ok ? "status-ok" : "status-err");
}

/* توابع مربوط به زمان‌بندی */
function setNow() {
    setDateFromGregorianDate(new Date());
}

function toFaDigits(value) {
    return String(value).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}

function pad2(num) {
    return String(num).padStart(2, '0');
}

function gregorianToJalali(gy, gm, gd) {
    const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    let jy;
    if (gy > 1600) {
        jy = 979;
        gy -= 1600;
    } else {
        jy = 0;
        gy -= 621;
    }
    const gy2 = gm > 2 ? gy + 1 : gy;
    let days = (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100)
        + Math.floor((gy2 + 399) / 400) - 80 + gd + g_d_m[gm - 1];
    jy += 33 * Math.floor(days / 12053);
    days %= 12053;
    jy += 4 * Math.floor(days / 1461);
    days %= 1461;
    if (days > 365) {
        jy += Math.floor((days - 1) / 365);
        days = (days - 1) % 365;
    }
    let jm, jd;
    if (days < 186) {
        jm = 1 + Math.floor(days / 31);
        jd = 1 + (days % 31);
    } else {
        jm = 7 + Math.floor((days - 186) / 30);
        jd = 1 + ((days - 186) % 30);
    }
    return [jy, jm, jd];
}

function jalaliToGregorian(jy, jm, jd) {
    let gy;
    if (jy > 979) {
        gy = 1600;
        jy -= 979;
    } else {
        gy = 621;
    }
    let days = (365 * jy) + (Math.floor(jy / 33) * 8) + Math.floor(((jy % 33) + 3) / 4) + 78 + jd
        + (jm < 7 ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
    gy += 400 * Math.floor(days / 146097);
    days %= 146097;
    if (days > 36524) {
        gy += 100 * Math.floor(--days / 36524);
        days %= 36524;
        if (days >= 365) days++;
    }
    gy += 4 * Math.floor(days / 1461);
    days %= 1461;
    if (days > 365) {
        gy += Math.floor((days - 1) / 365);
        days = (days - 1) % 365;
    }
    let gd = days + 1;
    const sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    let gm;
    for (gm = 1; gm <= 12; gm++) {
        if (gd <= sal_a[gm]) break;
        gd -= sal_a[gm];
    }
    return [gy, gm, gd];
}

function getJalaliMonthDays(jy, jm) {
    if (jm <= 6) return 31;
    if (jm <= 11) return 30;
    const leap = (((jy + 38) * 682) % 2816) < 682;
    return leap ? 30 : 29;
}

function renderPublishDisplay(jy, jm, jd, hh, mm) {
    const formatted = `${jy}/${pad2(jm)}/${pad2(jd)} - ${pad2(hh)}:${pad2(mm)}`;
    document.getElementById("publish_date_display").value = toFaDigits(formatted);
}

function setDateFromGregorianDate(dateObj) {
    const gy = dateObj.getFullYear();
    const gm = dateObj.getMonth() + 1;
    const gd = dateObj.getDate();
    const hh = dateObj.getHours();
    const mm = dateObj.getMinutes();
    const [jy, jm, jd] = gregorianToJalali(gy, gm, gd);
    document.getElementById("publish_date").value = `${gy}-${pad2(gm)}-${pad2(gd)} ${pad2(hh)}:${pad2(mm)}:00`;
    renderPublishDisplay(jy, jm, jd, hh, mm);
    setPickerValues(jy, jm, jd, hh, mm);
}

function setPickerValues(jy, jm, jd, hh, mm) {
    document.getElementById("jy").value = String(jy);
    document.getElementById("jm").value = String(jm);
    updateDayOptions();
    document.getElementById("jd").value = String(jd);
    document.getElementById("hh").value = String(hh);
    document.getElementById("mm").value = String(mm);
}

function updateDayOptions() {
    const jy = parseInt(document.getElementById("jy").value, 10);
    const jm = parseInt(document.getElementById("jm").value, 10);
    const daySelect = document.getElementById("jd");
    const current = parseInt(daySelect.value || "1", 10);
    const maxDays = getJalaliMonthDays(jy, jm);
    daySelect.innerHTML = "";
    for (let d = 1; d <= maxDays; d++) {
        const op = document.createElement("option");
        op.value = String(d);
        op.textContent = toFaDigits(pad2(d));
        daySelect.appendChild(op);
    }
    daySelect.value = String(Math.min(current, maxDays));
}

function openDatePicker() {
    document.getElementById("dateModal").classList.add("show");
}

function closeDatePicker() {
    document.getElementById("dateModal").classList.remove("show");
}

function setPickerNow() {
    const now = new Date();
    const [jy, jm, jd] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
    setPickerValues(jy, jm, jd, now.getHours(), now.getMinutes());
}

function applyDatePicker() {
    const jy = parseInt(document.getElementById("jy").value, 10);
    const jm = parseInt(document.getElementById("jm").value, 10);
    const jd = parseInt(document.getElementById("jd").value, 10);
    const hh = parseInt(document.getElementById("hh").value, 10);
    const mm = parseInt(document.getElementById("mm").value, 10);
    const [gy, gm, gd] = jalaliToGregorian(jy, jm, jd);
    document.getElementById("publish_date").value = `${gy}-${pad2(gm)}-${pad2(gd)} ${pad2(hh)}:${pad2(mm)}:00`;
    renderPublishDisplay(jy, jm, jd, hh, mm);
    closeDatePicker();
}

function initDatePicker() {
    const yearSel = document.getElementById("jy");
    const monthSel = document.getElementById("jm");
    const hourSel = document.getElementById("hh");
    const minuteSel = document.getElementById("mm");
    const persianMonths = [
        "فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور",
        "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"
    ];

    const now = new Date();
    const [nowJy] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
    for (let y = nowJy - 3; y <= nowJy + 5; y++) {
        const op = document.createElement("option");
        op.value = String(y);
        op.textContent = toFaDigits(String(y));
        yearSel.appendChild(op);
    }
    for (let m = 1; m <= 12; m++) {
        const op = document.createElement("option");
        op.value = String(m);
        op.textContent = `${toFaDigits(pad2(m))} - ${persianMonths[m - 1]}`;
        monthSel.appendChild(op);
    }
    for (let h = 0; h <= 23; h++) {
        const op = document.createElement("option");
        op.value = String(h);
        op.textContent = toFaDigits(pad2(h));
        hourSel.appendChild(op);
    }
    for (let m = 0; m <= 59; m++) {
        const op = document.createElement("option");
        op.value = String(m);
        op.textContent = toFaDigits(pad2(m));
        minuteSel.appendChild(op);
    }
    yearSel.addEventListener("change", updateDayOptions);
    monthSel.addEventListener("change", updateDayOptions);

    const savedGregorian = `<?= $pub_val ? htmlspecialchars(str_replace('T', ' ', $pub_val) . ':00') : '' ?>`;
    if (savedGregorian) {
        const parsed = new Date(savedGregorian.replace(' ', 'T'));
        if (!Number.isNaN(parsed.getTime())) {
            setDateFromGregorianDate(parsed);
        } else {
            setDateFromGregorianDate(now);
        }
    } else {
        setDateFromGregorianDate(now);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    initDatePicker();
});
/* ======================== */

/* ====== فشرده‌سازی هوشمند تصویر شاخص (حفظ کیفیت، کاهش حجم) ====== */
// فقط در صورت نیاز فشرده می‌کند: اگر فایل کوچک باشد دست‌نخورده برمی‌گردد.
async function compressFeaturedImage(file) {
    const MAX_DIMENSION = 1920;   // حداکثر عرض/ارتفاع
    const QUALITY = 0.85;         // کیفیت بالا
    const SIZE_THRESHOLD = 1.5 * 1024 * 1024; // زیر ۱.۵ مگ نیازی به فشرده‌سازی نیست

    // فایل‌های غیرتصویری یا PNG شفاف را دست‌نخورده برمی‌گردانیم تا کیفیت/شفافیت حفظ شود
    if (!file.type.startsWith("image/")) return file;
    if (file.size <= SIZE_THRESHOLD) return file;

    try {
        const dataUrl = await new Promise((res, rej) => {
            const r = new FileReader();
            r.onload = () => res(r.result);
            r.onerror = rej;
            r.readAsDataURL(file);
        });

        const img = await new Promise((res, rej) => {
            const i = new Image();
            i.onload = () => res(i);
            i.onerror = rej;
            i.src = dataUrl;
        });

        let { width, height } = img;
        if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
            const scale = MAX_DIMENSION / Math.max(width, height);
            width = Math.round(width * scale);
            height = Math.round(height * scale);
        }

        const canvas = document.createElement("canvas");
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, width, height);

        const blob = await new Promise(res => canvas.toBlob(res, "image/jpeg", QUALITY));
        if (!blob || blob.size >= file.size) return file; // اگر فشرده‌سازی سود نداشت، اصل را نگه‌دار

        const newName = file.name.replace(/\.[^.]+$/, "") + ".jpg";
        return new File([blob], newName, { type: "image/jpeg" });
    } catch (e) {
        console.warn("Image compression skipped:", e);
        return file; // در صورت خطا، فایل اصلی ارسال می‌شود
    }
}

/* ====== کنترل نوار پیشرفت آپلود ====== */
function setUploadProgress(percent, label) {
    const box = document.getElementById("uploadProgress");
    const fill = document.getElementById("uploadBarFill");
    const lbl = document.getElementById("uploadBarLabel");
    box.classList.add("active");
    fill.style.width = percent + "%";
    if (label) lbl.textContent = label;
}
function hideUploadProgress() {
    document.getElementById("uploadProgress").classList.remove("active");
    document.getElementById("uploadBarFill").style.width = "0%";
}

async function saveNews() {
    const title = document.getElementById("title").value.trim();
    const content = document.getElementById("editor").innerHTML.trim();

    if (!title || content === "" || content === "<br>") {
        showStatus("❌ لطفا تمام فیلدها را پر کنید.", false);
        return;
    }

    const saveBtn = document.querySelector('.btn-save');
    const originalBtnText = saveBtn.innerHTML;

    try {
        // تغییر وضعیت دکمه به لودینگ
        saveBtn.disabled = true;
        saveBtn.innerHTML = 'در حال ذخیره...';
        showStatus("⏳ در حال آماده‌سازی اطلاعات...", true);

        const fd = new FormData();
        fd.append("id", "<?= $id ?>");
        fd.append("title", title);
        fd.append("content", content);
        fd.append("author", document.getElementById("author").value);
        fd.append("category_id", document.getElementById("category_id").value);
        fd.append("keywords", document.getElementById("keywords").value);
        fd.append("publish_date", document.getElementById("publish_date").value);
        fd.append("tags", document.getElementById("tags").value);
        fd.append("remove_featured_flag", document.getElementById("remove_featured_flag").value);

        let featuredFile = document.getElementById("featured_image").files[0];
        if (featuredFile) {
            showStatus("🗜️ در حال بهینه‌سازی تصویر...", true);
            featuredFile = await compressFeaturedImage(featuredFile);
            fd.append("featured_image", featuredFile);
        }

        // استفاده از XMLHttpRequest برای نمایش درصد پیشرفت آپلود
        const data = await new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "news-save.php", true);
            xhr.setRequestHeader("Accept", "application/json");

            if (featuredFile) {
                showStatus("⏫ در حال آپلود به سرور...", true);
                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const pct = Math.round((e.loaded / e.total) * 100);
                        setUploadProgress(pct, `در حال آپلود... ${pct}%`);
                    }
                };
                xhr.upload.onload = () => setUploadProgress(100, "آپلود کامل شد، در حال پردازش...");
            }

            xhr.onload = () => {
                let parsed;
                try { parsed = JSON.parse(xhr.responseText); }
                catch (_) { return reject(new Error("پاسخ نامعتبر از سرور دریافت شد.")); }
                if (xhr.status >= 200 && xhr.status < 300 && parsed.status === "success") {
                    resolve(parsed);
                } else {
                    reject(new Error(parsed.message || "خطایی در پردازش اطلاعات رخ داد."));
                }
            };
            xhr.onerror = () => reject(new Error("ارتباط با سرور برقرار نشد."));
            xhr.send(fd);
        });

        setUploadProgress(100, "✅ با موفقیت ذخیره شد");
        showStatus(`✅ ${data.message} در حال انتقال...`, true);
        setTimeout(() => {
            window.location.href = "news-list.php";
        }, 1000);

    } catch (err) {
        console.error("Save Error:", err);
        hideUploadProgress();
        showStatus("❌ خطایی رخ داد: " + err.message, false);
    } finally {
        // بازگشت دکمه به حالت عادی در صورت خطا
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    }
}

function openPreview(){
    const title = document.getElementById("title").value;
    const author = document.getElementById("author").value;
    const date = document.getElementById("publish_date_display").value;
    const content = document.getElementById("editor").innerHTML;
    const featured = featuredInput.files[0]
        ? URL.createObjectURL(featuredInput.files[0])
        : "";

    const html = `
        <h1 style="font-size:26px;margin-bottom:10px;color:var(--primary-color)">${title}</h1>
        <div style="color:var(--text-color); opacity:0.7; font-size:13px;margin-bottom:20px;">
            ${author ? "✍ " + author : ""} 
            ${date ? " | 📅 " + date : ""}
        </div>
        ${featured ? `<img src="${featured}" style="width:100%;max-height:400px;object-fit:cover;border-radius:12px;margin-bottom:20px;">` : ""}
        <div style="line-height:2;font-size:15px;color:var(--text-color);">
            ${content}
        </div>
        <hr style="margin:30px 0; border-color: var(--border-color);">
        <div id="seoScoreBox"></div>
    `;

    document.getElementById("previewContent").innerHTML = html;
    calculateSEO();
    const modal = document.getElementById("previewModal");
    modal.style.display = "block";
    requestAnimationFrame(() => modal.classList.add("show"));
}

function closePreview(){
    const modal = document.getElementById("previewModal");
    modal.classList.remove("show");
    setTimeout(() => {
        modal.style.display = "none";
    }, 260);
}

function calculateSEO(){
    let score = 0;
    const title = document.getElementById("title").value.trim();
    const contentText = editor.innerText.trim();
    const wordCount = contentText.split(/\s+/).length;
    const hasImage = featuredInput.files[0];
    const keywords = document.getElementById("keywords").value.trim();
    const hasH2 = editor.querySelector("h2");

    if(title.length > 5) score += 20;
    if(wordCount > 300) score += 25;
    if(hasImage) score += 15;
    if(keywords.length > 3) score += 20;
    if(hasH2) score += 20;

    let color = "#d63031";
    if(score >= 70) color = "#00b894";
    else if(score >= 40) color = "#f9a825";

    document.getElementById("seoScoreBox").innerHTML = `
        <h3 style="margin-bottom:10px;color:var(--text-color)">SEO Score</h3>
        <div style="
            background: var(--border-color);
            border-radius:8px;
            overflow:hidden;
            height:20px;
        ">
            <div style="
                width:${score}%;
                background:${color};
                height:100%;
                transition:.4s;
            "></div>
        </div>
        <div style="margin-top:8px;font-weight:bold;color:${color}">
            امتیاز: ${score} از 100
        </div>
    `;
}

document.getElementById("editor").addEventListener("keydown", function(e) {
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        document.execCommand("insertLineBreak");
    }
});

// مدیریت تم (دارک/لایت)
document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // آیکون‌های SVG برای تم
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
</script>

</body>
</html>

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

// دریافت لیست تگ‌ها و تگ‌های انتخاب شده
$all_tags = [];
$selected_tag_ids = [];
try {
    $tag_stmt = $pdo->query("SELECT id, name FROM news_tags ORDER BY id ASC");
    $all_tags = $tag_stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($id > 0) {
        $stmt_selected_tags = $pdo->prepare("SELECT tag_id FROM news_tags_map WHERE news_id = ?");
        $stmt_selected_tags->execute([$id]);
        $selected_tag_ids = $stmt_selected_tags->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (Exception $e) {
    $all_tags = [];
    $selected_tag_ids = [];
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
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $id > 0 ? 'ویرایش خبر' : 'ایجاد خبر' ?></title>

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
    --primary-color: #007D75;       /* سبز-آبی مکسا */
    --primary-dark: #006159;
    --secondary-color: #F79F1F;     /* نارنجی مکسا */
    --bg-color: #f4f7f6;
    --text-color: #333333;
    --muted-color: #6b7674;
    --panel-bg: #ffffff;
    --surface-2: #f7faf9;
    --border-color: #e3e9e8;
    --input-bg: #ffffff;
    --header-text: #007D75;
    --btn-hover-opacity: 0.92;
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
    /* پالت رنگی دارک مود */
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

/* ===== هدر چسبان ویرایشگر ===== */
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

.eh-title {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.ph-ic {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    box-shadow: var(--shadow-sm);
}

.ph-ic svg { width: 22px; height: 22px; }

.eh-title h1 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--header-text);
}

.eh-title p {
    margin: 2px 0 0;
    font-size: 12px;
    color: var(--muted-color);
}

.draft-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    padding: 5px 12px;
    border-radius: 999px;
    background: var(--surface-2);
    color: var(--muted-color);
    border: 1px solid var(--border-color);
    white-space: nowrap;
}

.eh-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* ===== شبکه اصلی ===== */
.layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    align-items: start;
}

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
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
    font-weight: 800;
    color: var(--header-text);
    margin: 0 0 16px;
}

.card-title svg { width: 19px; height: 19px; color: var(--primary-color); }

/* ===== عنوان خبر ===== */
.title-card { padding: 20px; transition: box-shadow var(--anim-fast), border-color var(--anim-fast); }
.title-card:focus-within { border-color: color-mix(in srgb, var(--primary-color) 55%, var(--border-color)); box-shadow: 0 0 0 4px rgba(0,125,117,0.08); }

#title {
    width: 100%;
    border: none;
    background: transparent;
    color: var(--text-color);
    font-family: inherit;
    font-size: 26px;
    font-weight: 800;
    padding: 0;
    line-height: 1.5;
}
#title:focus { outline: none; }
#title::placeholder { color: var(--muted-color); opacity: 0.55; }

.title-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid var(--border-color);
    font-size: 12px;
    color: var(--muted-color);
}
#titleCounter.over { color: #e74c3c; font-weight: 700; }

/* ===== زیرعنوان خبر ===== */
.subtitle-card {
    background: var(--panel-bg);
    border: 1px dashed var(--border-color);
    border-radius: var(--radius);
    padding: 20px;
    margin-top: 16px;
    box-shadow: var(--shadow-sm);
    transition: all var(--anim-fast);
}
.subtitle-card:focus-within {
    border-color: var(--primary-color);
    background: var(--panel-bg);
    box-shadow: 0 0 0 4px rgba(0, 125, 117, 0.08);
}
#subtitle {
    width: 100%;
    border: none;
    background: transparent;
    color: var(--text-color);
    font-family: inherit;
    font-size: 16px;
    font-weight: 600;
    padding: 0;
    line-height: 1.6;
    resize: none;
}
#subtitle:focus {
    outline: none;
}
#subtitle::placeholder {
    color: var(--muted-color);
    opacity: 0.65;
}
.subtitle-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid var(--border-color);
    font-size: 11px;
    color: var(--muted-color);
}
#subtitleCounter.over { color: #e74c3c; font-weight: 700; }

/* ===== برچسب‌های خبر (چند انتخابی) ===== */
.tag-checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 999px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all var(--anim-fast);
    user-select: none;
    color: var(--text-color);
}
.tag-checkbox-label:hover {
    border-color: var(--primary-color);
    background: rgba(0, 125, 117, 0.04);
}
.tag-checkbox-label input[type="checkbox"] {
    accent-color: var(--primary-color);
    cursor: pointer;
    width: 15px;
    height: 15px;
    margin: 0;
}
.tag-checkbox-label:has(input[type="checkbox"]:checked) {
    background: rgba(0, 125, 117, 0.08);
    border-color: var(--primary-color);
    color: var(--primary-color);
}

/* ===== فیلدهای عمومی ===== */
.input-group { margin-bottom: 18px; }
.input-group:last-child { margin-bottom: 0; }

label.field-label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 700;
    color: var(--primary-color);
}
label.field-label svg { width: 16px; height: 16px; }

.input {
    width: 100%;
    background: var(--input-bg);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 11px 12px;
    font-size: 15px;
    font-family: inherit;
    transition: border-color var(--anim-fast), box-shadow var(--anim-fast);
}
.input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.14);
}
select.input { appearance: none; cursor: pointer; }

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

/* ===== حالت خطای فیلدها ===== */
.input.field-invalid,
.title-card.field-invalid,
.editor-shell.field-invalid #editor {
    border-color: var(--danger, #e74c3c) !important;
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.14) !important;
}
.title-card.field-invalid { animation: fieldShake 0.32s ease; }
.input.field-invalid { animation: fieldShake 0.32s ease; }
/* لیبل فیلدِ خطادار قرمز شود تا سریع دیده شود */
.input-group.has-error label.field-label { color: var(--danger, #e74c3c); }

.field-error {
    display: none;
    align-items: center;
    gap: 5px;
    margin-top: 7px;
    font-size: 12px;
    font-weight: 600;
    color: var(--danger, #e74c3c);
}
.field-error.show { display: flex; animation: toastSlide 0.28s ease; }
.field-error svg { width: 14px; height: 14px; flex-shrink: 0; }

@keyframes fieldShake {
    0%,100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    75% { transform: translateX(4px); }
}

/* ===== ادیتور ===== */
.editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    padding-bottom: 16px;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}

.tb-btn {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    color: var(--text-color);
    border-radius: 8px;
    cursor: pointer;
    padding: 7px 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--anim-fast);
}
.tb-btn:hover {
    background: var(--primary-color);
    color: #fff;
    border-color: var(--primary-color);
    transform: translateY(-2px);
}
.tb-btn svg { stroke: currentColor; }
.tb-btn:hover svg { stroke: #fff; }
.tb-btn svg circle, .tb-btn svg path[fill="#333"] { fill: currentColor; stroke: none; }
.tb-btn:hover svg circle, .tb-btn:hover svg path[fill="#333"] { fill: #fff; }

.tb-sep { width: 1px; height: 24px; background: var(--border-color); margin: 0 2px; }

.tb-select {
    background: var(--surface-2);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 7px 6px;
    font-family: inherit;
    cursor: pointer;
}
.tb-select.small { width: 72px; }

.color-row { display: flex; gap: 5px; margin-right: auto; }
.color-box {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    cursor: pointer;
    border: 1px solid rgba(0,0,0,0.12);
    transition: transform var(--anim-fast);
}
.color-box:hover { transform: scale(1.15); }

.editor-shell {
    position: relative;
}

#editor {
    min-height: 380px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 16px;
    background: var(--input-bg);
    color: var(--text-color);
    overflow-y: auto;
    line-height: 1.9;
    font-size: 16px;
    position: relative;
}
#editor::after {
    content: "";
    display: table;
    clear: both;
}
#editor:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(0,125,117,0.10); }
#editor:empty:before { content: attr(data-placeholder); color: var(--muted-color); opacity: 0.55; }

/* ===== استایل و کنترل تصاویر داخل ادیتور و محتوا ===== */
.article-img-wrap,
figure.article-img-wrap {
    display: block;
    max-width: 100%;
    margin: 20px auto;
    position: relative;
    box-sizing: border-box;
    vertical-align: top;
    clear: both;
    transition: width 0.15s ease, margin 0.15s ease;
}

.article-img-wrap img,
.article-inline-img,
#editor img,
.pv-body img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    transition: outline 0.15s ease, box-shadow 0.2s ease;
    cursor: pointer;
    vertical-align: middle;
}

/* حالت‌های چینش تصاویر (Alignments) */
.article-img-wrap.align-right,
.article-inline-img.align-right,
#editor img.align-right,
.pv-body img.align-right,
.pv-body figure.align-right {
    float: right !important;
    margin: 8px 0 20px 24px !important;
    display: block !important;
    clear: right !important;
    text-align: right;
}

.article-img-wrap.align-left,
.article-inline-img.align-left,
#editor img.align-left,
.pv-body img.align-left,
.pv-body figure.align-left {
    float: left !important;
    margin: 8px 24px 20px 0 !important;
    display: block !important;
    clear: left !important;
    text-align: left;
}

.article-img-wrap.align-center,
.article-inline-img.align-center,
#editor img.align-center,
.pv-body img.align-center,
.pv-body figure.align-center {
    display: block !important;
    margin: 24px auto !important;
    text-align: center !important;
    float: none !important;
    clear: both !important;
}

.article-img-wrap.align-full,
.article-inline-img.align-full,
#editor img.align-full,
.pv-body img.align-full,
.pv-body figure.align-full {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 28px 0 !important;
    float: none !important;
    clear: both !important;
    text-align: center;
}

/* کپشن / توضیح تصویر */
figcaption.img-caption,
#editor figcaption,
.pv-body figcaption {
    margin-top: 8px;
    font-size: 13px;
    color: var(--muted-color);
    text-align: center;
    line-height: 1.6;
    padding: 4px 8px;
    font-weight: 500;
}
#editor figcaption:focus {
    outline: none;
    background: rgba(0, 125, 117, 0.08);
    border-radius: 6px;
}
#editor figcaption:empty:before {
    content: "توضیح زیر عکس (اختیاری)...";
    color: var(--muted-color);
    opacity: 0.6;
}

/* حالت انتخاب تصویر در ادیتور */
#editor img.img-selected,
#editor figure.img-selected img {
    outline: 3px solid var(--primary-color) !important;
    outline-offset: 3px;
    box-shadow: 0 0 0 6px rgba(0, 125, 117, 0.2), 0 8px 24px rgba(0,0,0,0.12) !important;
}

/* نوار ابزار شناور اختصاصی تصویر */
.img-float-bar {
    position: absolute;
    z-index: 1000;
    display: none;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    background: #1e293b;
    color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.38), 0 0 0 1px rgba(255,255,255,0.12);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    font-family: inherit;
    font-size: 13px;
    user-select: none;
    transform: translateX(-50%);
    pointer-events: auto;
    animation: imgBarPop 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes imgBarPop {
    from { opacity: 0; transform: translate(-50%, 6px) scale(0.96); }
    to { opacity: 1; transform: translate(-50%, 0) scale(1); }
}
.img-float-bar::after {
    content: "";
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    border-width: 6px;
    border-style: solid;
}
.img-float-bar.arrow-bottom::after {
    top: 100%;
    border-color: #1e293b transparent transparent transparent;
}
.img-float-bar.arrow-top::after {
    bottom: 100%;
    border-color: transparent transparent #1e293b transparent;
}

.img-btn {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.08);
    color: #f1f5f9;
    padding: 6px 9px;
    border-radius: 7px;
    cursor: pointer;
    font-family: inherit;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.img-btn:hover {
    background: var(--primary-color);
    color: #ffffff;
    border-color: var(--primary-color);
    transform: translateY(-1px);
}
.img-btn.active {
    background: var(--primary-color);
    color: #ffffff;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(255,255,255,0.25);
}
.img-btn.btn-danger-light:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: #ffffff;
}
.img-btn svg {
    width: 15px;
    height: 15px;
    stroke: currentColor;
    fill: none;
    flex-shrink: 0;
}
.img-bar-sep {
    width: 1px;
    height: 20px;
    background: rgba(255,255,255,0.18);
    margin: 0 3px;
    flex-shrink: 0;
}

/* لایه پوششی ریسایزر با دستگیره‌ها (Drag Resizer Overlay) */
.img-resize-box {
    position: absolute;
    pointer-events: none;
    border: 2px dashed var(--primary-color);
    border-radius: 12px;
    display: none;
    z-index: 999;
}
.img-resize-handle {
    position: absolute;
    width: 13px;
    height: 13px;
    background: #ffffff;
    border: 2.5px solid var(--primary-color);
    border-radius: 4px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    pointer-events: auto;
    z-index: 1001;
    transition: transform 0.1s ease, background 0.1s ease;
}
.img-resize-handle:hover {
    transform: scale(1.35);
    background: var(--primary-color);
}
.handle-nw { top: -6px; left: -6px; cursor: nwse-resize; }
.handle-ne { top: -6px; right: -6px; cursor: nesw-resize; }
.handle-se { bottom: -6px; right: -6px; cursor: nwse-resize; }
.handle-sw { bottom: -6px; left: -6px; cursor: nesw-resize; }

.img-size-badge {
    position: absolute;
    bottom: -28px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(15, 23, 42, 0.9);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    white-space: nowrap;
    pointer-events: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

/* ===== مودال تنظیمات پیشرفته تصویر ===== */
.img-settings-modal {
    position: fixed;
    inset: 0;
    z-index: 10002;
    background: var(--modal-overlay);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.25s ease;
}
.img-settings-modal.show {
    display: flex;
    opacity: 1;
}
.img-modal-box {
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    width: 100%;
    max-width: 680px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: cardPop 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}
.img-modal-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--surface-2);
}
.img-modal-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--header-text);
    display: flex;
    align-items: center;
    gap: 8px;
}
.img-modal-title svg {
    width: 20px;
    height: 20px;
    color: var(--primary-color);
}
.img-modal-close {
    background: transparent;
    border: none;
    color: var(--muted-color);
    cursor: pointer;
    padding: 4px;
    border-radius: 8px;
    display: flex;
}
.img-modal-close:hover {
    color: #e74c3c;
    background: rgba(231, 76, 60, 0.08);
}
.img-modal-close svg {
    width: 20px;
    height: 20px;
}
.img-modal-body {
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.img-modal-foot {
    padding: 14px 20px;
    border-top: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    background: var(--surface-2);
}

.img-modal-preview-box {
    background: var(--surface-2);
    border: 1px dashed var(--border-color);
    border-radius: var(--radius-sm);
    padding: 14px;
    text-align: center;
    max-height: 180px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.img-modal-preview-box img {
    max-height: 120px;
    max-width: 100%;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.img-field-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.img-field-label {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.img-slider-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.img-range {
    flex: 1;
    min-width: 150px;
    accent-color: var(--primary-color);
    cursor: pointer;
}
.img-quick-pills {
    display: flex;
    gap: 6px;
}
.pill-btn {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    color: var(--text-color);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.15s ease;
}
.pill-btn:hover, .pill-btn.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
}

.img-align-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
@media (max-width: 600px) {
    .img-align-grid { grid-template-columns: repeat(2, 1fr); }
}
.img-align-card {
    cursor: pointer;
}
.img-align-card input[type="radio"] {
    display: none;
}
.align-card-inner {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 10px 8px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-color);
    transition: all 0.2s ease;
}
.align-card-inner svg {
    width: 22px;
    height: 22px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
}
.img-align-card:hover .align-card-inner {
    border-color: var(--primary-color);
    background: rgba(0, 125, 117, 0.04);
}
.img-align-card input[type="radio"]:checked + .align-card-inner {
    border-color: var(--primary-color);
    background: rgba(0, 125, 117, 0.12);
    color: var(--primary-color);
    font-weight: 800;
    box-shadow: 0 0 0 2px rgba(0, 125, 117, 0.25);
}

.img-style-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.pill-choice {
    cursor: pointer;
}
.pill-choice input[type="radio"] {
    display: none;
}
.pill-choice span {
    display: inline-block;
    padding: 6px 14px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-color);
    transition: all 0.15s ease;
}
.pill-choice:hover span {
    border-color: var(--primary-color);
}
.pill-choice input[type="radio"]:checked + span {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
    font-weight: 700;
}

.img-checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-color);
    cursor: pointer;
}
.img-checkbox-label input[type="checkbox"] {
    accent-color: var(--primary-color);
    width: 16px;
    height: 16px;
    cursor: pointer;
}

/* ریسپانسیو تصاویر شناور روی صفحات کوچک */
@media (max-width: 768px) {
    .article-img-wrap.align-right,
    .article-img-wrap.align-left,
    .article-inline-img.align-right,
    .article-inline-img.align-left,
    #editor img.align-right,
    #editor img.align-left,
    .pv-body img.align-right,
    .pv-body img.align-left {
        float: none !important;
        margin: 18px auto !important;
        display: block !important;
        max-width: 100% !important;
        width: 100% !important;
        text-align: center !important;
    }
}

/* ===== دکمه‌ها ===== */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 999px;
    cursor: pointer;
    font-weight: 700;
    font-size: 14px;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: transform var(--anim-fast), box-shadow var(--anim-fast), opacity var(--anim-fast), background var(--anim-fast);
}
.btn svg { width: 17px; height: 17px; }
.btn:hover { transform: translateY(-2px); }
.btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.btn-save {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: #fff;
    box-shadow: var(--shadow-sm);
}
.btn-save:hover { box-shadow: var(--shadow-md); }

.btn-ghost {
    background: transparent;
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
}
.btn-ghost:hover { background: rgba(0,125,117,0.06); }

.btn-insert {
    background: transparent;
    border: 1px dashed var(--primary-color);
    color: var(--primary-color);
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    transition: all var(--anim-fast);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-insert:hover { background: var(--primary-color); color: #fff; }

/* ===== آپلود تصویر شاخص (Dropzone) ===== */
.dropzone {
    position: relative;
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-sm);
    background: var(--surface-2);
    min-height: 190px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 20px;
    cursor: pointer;
    transition: all var(--anim-fast);
    overflow: hidden;
}
.dropzone:hover, .dropzone.drag-over {
    border-color: var(--primary-color);
    background: rgba(0,125,117,0.05);
}
.dropzone .dz-icon { color: var(--muted-color); transition: color var(--anim-fast); }
.dropzone:hover .dz-icon { color: var(--primary-color); }
.dropzone .dz-icon svg { width: 38px; height: 38px; }
.dropzone .dz-main { font-size: 14px; font-weight: 600; color: var(--text-color); margin-top: 8px; }
.dropzone .dz-hint { font-size: 12px; color: var(--muted-color); margin-top: 4px; }

.dz-preview {
    position: absolute;
    inset: 0;
    display: none;
}
.dz-preview.show { display: block; }
.dz-preview img { width: 100%; height: 100%; object-fit: cover; }
.dz-remove {
    position: absolute;
    top: 8px;
    left: 8px;
    background: rgba(231,76,60,0.92);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}
.dz-remove svg { width: 16px; height: 16px; }

/* ===== ویجت امتیاز سئو ===== */
.seo-widget { display: flex; align-items: center; gap: 16px; }
.seo-ring { position: relative; width: 64px; height: 64px; flex-shrink: 0; }
.seo-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.seo-ring .ring-track { stroke: var(--border-color); }
.seo-ring .ring-fill { transition: stroke-dasharray var(--anim-mid) ease, stroke var(--anim-mid) ease; }
.seo-ring .ring-num {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; font-weight: 800; color: var(--text-color);
}
.seo-info { flex: 1; min-width: 0; }
.seo-state { font-size: 14px; color: var(--text-color); }
.seo-state b { font-weight: 800; }
.seo-tip { font-size: 12px; color: var(--muted-color); margin-top: 4px; }

/* ===== چیپ تگ‌ها ===== */
.chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    background: var(--surface-2);
    color: var(--text-color);
    border: 1px solid var(--border-color);
    border-radius: 999px;
    font-size: 12px;
}
.chip button {
    background: none; border: none; cursor: pointer;
    color: var(--muted-color); padding: 0; display: flex;
    line-height: 1;
}
.chip button:hover { color: #e74c3c; }
.chip button svg { width: 13px; height: 13px; }

/* بخش‌بندی کارت تنظیمات */
.settings-card { overflow: visible; }   /* تا منوی دراپ‌داون سفارشی بریده نشود */
.settings-card .input-group { padding: 16px; margin: 0; border-bottom: 1px solid var(--border-color); position: relative; }
.settings-card .input-group:last-child { border-bottom: none; }

.publish-date-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.publish-date-row .input { flex: 1; min-width: 140px; }

/* ===== توست وضعیت (موفقیت / خطا / در حال انجام) ===== */
.toast {
    position: fixed;
    bottom: 22px;
    left: 22px;
    z-index: 10001;
    width: min(380px, calc(100vw - 44px));
    display: none;
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}
.toast.show { display: block; animation: toastSlide 360ms cubic-bezier(.2,.8,.2,1) both; }
.toast.hide { animation: toastOut 260ms ease both; }

/* نوار رنگی کناری بر اساس نوع پیام */
.toast::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;     /* در RTL سمت چپ کارت */
    width: 5px;
    background: var(--primary-color);
}
.toast.is-error::before  { background: var(--danger, #e74c3c); }
.toast.is-success::before { background: #00b894; }
.toast.is-info::before    { background: var(--secondary-color); }

.toast-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
}
.toast-ic {
    flex-shrink: 0;
    width: 38px; height: 38px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
}
.toast-ic svg { width: 20px; height: 20px; }
.toast.is-error  .toast-ic { background: var(--danger, #e74c3c); }
.toast.is-success .toast-ic { background: #00b894; }
.toast.is-info    .toast-ic { background: var(--secondary-color); }
/* اسپینر چرخان برای حالت در حال انجام */
.toast.is-info .toast-ic.spin svg { animation: toastSpin 0.9s linear infinite; }

.toast-body { flex: 1; min-width: 0; padding-top: 1px; }
.toast-title { font-size: 14px; font-weight: 800; color: var(--text-color); margin: 0 0 2px; }
.toast-msg { font-size: 13px; color: var(--muted-color); line-height: 1.7; word-break: break-word; }

.toast-close {
    flex-shrink: 0;
    background: transparent;
    border: none;
    color: var(--muted-color);
    cursor: pointer;
    width: 26px; height: 26px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    transition: all var(--anim-fast);
}
.toast-close:hover { background: var(--surface-2); color: var(--text-color); }
.toast-close svg { width: 16px; height: 16px; }

/* نوار پیشرفت داخل توست */
.toast-progress { display: none; padding: 0 16px 14px; }
.toast-progress.active { display: block; }
.toast-progress .bar-track { width: 100%; height: 8px; background: var(--border-color); border-radius: 999px; overflow: hidden; }
.toast-progress .bar-fill {
    height: 100%; width: 0%; border-radius: 999px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    transition: width 0.15s ease;
}

@keyframes toastSlide { from { opacity: 0; transform: translateY(16px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes toastOut   { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(16px); } }
@keyframes toastSpin  { to { transform: rotate(360deg); } }

/* ===== مودال‌ها ===== */
.date-modal {
    position: fixed; inset: 0;
    background: var(--modal-overlay);
    display: none; align-items: center; justify-content: center;
    z-index: 10000; padding: 12px;
}
.date-modal.show { display: flex; }
.date-modal.show .date-modal-box { transform: translateY(0) scale(1); opacity: 1; }
.date-modal-box {
    background: var(--panel-bg);
    color: var(--text-color);
    width: 100%; max-width: 380px;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    padding: 16px;
    box-shadow: var(--shadow-md);
    transform: translateY(10px) scale(0.98);
    opacity: 0;
    transition: transform var(--anim-mid) ease, opacity var(--anim-mid) ease;
}
.date-modal-box { max-width: 360px; }
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

/* ===== تقویم گرید ===== */
.dp-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 8px; }
.dp-nav-btn {
    width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--border-color);
    background: var(--surface-2); color: var(--text-color); cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all var(--anim-fast); flex-shrink: 0;
}
.dp-nav-btn:hover { background: var(--primary-color); color: #fff; border-color: var(--primary-color); }
.dp-nav-btn svg { width: 17px; height: 17px; }
.dp-month-label { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; }
.dp-label-btn {
    border: none; background: transparent; color: var(--text-color); cursor: pointer;
    font-family: inherit; font-size: 14px; font-weight: 800; padding: 5px 10px; border-radius: 8px;
    transition: background var(--anim-fast), color var(--anim-fast);
}
.dp-label-btn:hover { background: rgba(0,125,117,0.10); color: var(--primary-color); }

/* پنل انتخاب ماه/سال */
.dp-grid-pick { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; padding: 4px 0; max-height: 252px; overflow-y: auto; }
.dp-pick-item {
    border: 1px solid var(--border-color); background: var(--surface-2); color: var(--text-color);
    border-radius: 10px; padding: 12px 6px; cursor: pointer; font-family: inherit; font-size: 13px; font-weight: 700;
    transition: all var(--anim-fast);
}
.dp-pick-item:hover { background: rgba(0,125,117,0.10); border-color: var(--primary-color); }
.dp-pick-item.is-selected { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: #fff; border-color: transparent; }
.dp-pick-item.is-today { box-shadow: inset 0 0 0 1.5px var(--secondary-color); }

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

/* ===== انتخاب زمان ===== */
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

#previewModal {
    position: fixed; inset: 0;
    background: var(--modal-overlay);
    display: none; z-index: 9999; overflow-y: auto;
    padding: 24px 16px;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
.modal-content {
    max-width: 880px; margin: 0 auto;
    background: var(--panel-bg); color: var(--text-color);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    position: relative;
    overflow: hidden;
    transform: translateY(14px) scale(0.98);
    opacity: 0;
    transition: transform var(--anim-mid) ease, opacity var(--anim-mid) ease;
}
#previewModal.show .modal-content { transform: translateY(0) scale(1); opacity: 1; }

/* نوار بالای مودال پیش‌نمایش */
.preview-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border-color);
    background: var(--surface-2);
    position: sticky;
    top: 0;
    z-index: 2;
}
.preview-bar-title {
    display: flex; align-items: center; gap: 10px;
    font-size: 15px; font-weight: 800; color: var(--header-text);
}
.preview-bar-title .ph-ic { width: 34px; height: 34px; border-radius: 10px; }
.preview-bar-title .ph-ic svg { width: 17px; height: 17px; }
.preview-close {
    background: transparent;
    color: var(--muted-color);
    border: 1px solid var(--border-color);
    width: 34px; height: 34px;
    border-radius: 10px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all var(--anim-fast);
    font-family: inherit;
}
.preview-close:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; }
.preview-close svg { width: 18px; height: 18px; }

.preview-scroll { padding: 22px; }

/* مقاله پیش‌نمایش */
.pv-article { animation: toastIn 380ms ease both; }
.pv-hero {
    position: relative;
    width: 100%;
    height: 280px;
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 20px;
    background: var(--surface-2);
}
.pv-hero img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pv-hero::after {
    content: ""; position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.55), rgba(0,0,0,0) 55%);
}
.pv-hero-cat {
    position: absolute; top: 14px; right: 14px; z-index: 2;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: #fff; font-size: 12px; font-weight: 700;
    padding: 5px 12px; border-radius: 999px;
    box-shadow: var(--shadow-sm);
}
.pv-hero-title {
    position: absolute; right: 18px; left: 18px; bottom: 16px; z-index: 2;
    color: #fff; font-size: 26px; font-weight: 800; line-height: 1.5;
    text-shadow: 0 2px 12px rgba(0,0,0,0.4);
    margin: 0;
}
.pv-title-fallback {
    font-size: 26px; font-weight: 800; color: var(--header-text);
    margin: 0 0 14px; line-height: 1.5;
}
.pv-meta {
    display: flex; flex-wrap: wrap; gap: 8px;
    margin-bottom: 20px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--border-color);
}
.pv-meta .chip { background: var(--surface-2); }
.pv-meta .chip svg { width: 14px; height: 14px; color: var(--primary-color); }
.pv-body {
    line-height: 2.1; font-size: 16px; color: var(--text-color);
}
.pv-body::after {
    content: "";
    display: table;
    clear: both;
}
.pv-body img { max-width: 100%; height: auto; border-radius: 10px; }
.pv-body figure, .pv-body .article-img-wrap { display: block; max-width: 100%; margin: 20px auto; clear: both; }
.pv-body figcaption, .pv-body .img-caption { margin-top: 8px; font-size: 13px; color: var(--muted-color); text-align: center; }
.pv-body .gallery-row { display: flex; gap: 12px; margin: 20px 0; flex-wrap: wrap; clear: both; }
.pv-body .gallery-row img { border-radius: 8px; flex: 1; min-width: 30%; max-width: 100%; }
.pv-empty { color: var(--muted-color); font-style: italic; }

/* پنل خلاصه سئو در پیش‌نمایش */
.pv-seo {
    margin-top: 26px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    background: var(--surface-2);
    padding: 18px;
}
.pv-seo-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; margin-bottom: 14px; flex-wrap: wrap;
}
.pv-seo-head .card-title { margin: 0; }
.pv-seo-badge {
    font-size: 13px; font-weight: 800;
    padding: 4px 12px; border-radius: 999px; color: #fff;
}
.pv-seo-bar-track {
    height: 12px; background: var(--border-color);
    border-radius: 999px; overflow: hidden;
}
.pv-seo-bar-fill { height: 100%; border-radius: 999px; transition: width .5s ease, background .5s ease; }
.pv-seo-foot {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 8px; font-size: 13px; color: var(--muted-color);
}
.pv-seo-foot b { color: var(--text-color); }
.pv-checks { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px; margin-top: 14px; }
.pv-check {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: var(--text-color);
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 8px 10px;
}
.pv-check svg { width: 16px; height: 16px; flex-shrink: 0; }
.pv-check.ok svg { color: #00b894; }
.pv-check.no { color: var(--muted-color); }
.pv-check.no svg { color: var(--muted-color); opacity: 0.7; }

/* ===== ریسپانسیو ===== */
@media (max-width: 980px) {
    .layout { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    #title { font-size: 22px; }
    .eh-actions { width: 100%; }
    .eh-actions .btn { flex: 1; }
    .tb-btn { flex: 1 1 calc(16.66% - 6px); }
}

/* ===== انیمیشن‌ها ===== */
.col-side > .card { animation-delay: 90ms; }
.col-side > .card:nth-child(2) { animation-delay: 150ms; }
.col-side > .card:nth-child(3) { animation-delay: 210ms; }

@keyframes pageRise { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes cardPop { from { opacity: 0; transform: translateY(16px) scale(0.985); } to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes toastIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </span>
            <div>
                <h1><?= $id > 0 ? 'ویرایش خبر' : 'ایجاد خبر جدید' ?></h1>
                <p>مدیریت محتوای دیجیتال</p>
            </div>
            <span class="draft-badge">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                پیش‌نویس ذخیره نشده
            </span>
        </div>
        <div class="eh-actions">
            <button type="button" class="btn btn-ghost" onclick="openPreview()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                پیش‌نمایش
            </button>
            <button type="button" class="btn btn-save" onclick="saveNews()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <?= $id > 0 ? 'ذخیره ویرایش' : 'ثبت خبر نهایی' ?>
            </button>
        </div>
    </div>

    <div class="layout">

        <!-- ستون اصلی: عنوان + ادیتور -->
        <div class="col-main">

            <!-- عنوان خبر -->
            <div class="card title-card" id="titleCard">
                <input type="text" class="" id="title" maxlength="120" placeholder="عنوان خبر را اینجا بنویسید..." value="<?= $news_data ? htmlspecialchars($news_data['title']) : '' ?>">
                <div class="title-meta">
                    <span>حداکثر ۱۲۰ کاراکتر</span>
                    <span id="titleCounter">0/120</span>
                </div>
                <div class="field-error" id="titleError">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>وارد کردن عنوان خبر الزامی است.</span>
                </div>
            </div>

            <!-- زیرعنوان خبر -->
            <div class="subtitle-card" id="subtitleCard">
                <textarea id="subtitle" maxlength="200" rows="2" placeholder="زیرعنوان (توضیح کوتاه) خبر را اینجا بنویسید..."><?= $news_data ? htmlspecialchars($news_data['subtitle'] ?? '') : '' ?></textarea>
                <div class="subtitle-meta">
                    <span>حداکثر ۲۰۰ کاراکتر</span>
                    <span id="subtitleCounter">0/200</span>
                </div>
            </div>

            <!-- متن خبر -->
            <div class="card card-pad">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                    متن خبر
                </h3>

                <div class="editor-toolbar">

                    <button type="button" onclick="format('bold')" class="tb-btn" title="درشت">
                        <svg width="18" viewBox="0 0 24 24"><path d="M7 5v14h6a4 4 0 0 0 0-8H7m6 0a4 4 0 0 0 0-8H7" fill="none" stroke-width="2"/></svg>
                    </button>

                    <button type="button" onclick="format('italic')" class="tb-btn" title="مورب">
                        <svg width="18" viewBox="0 0 24 24"><line x1="19" y1="4" x2="10" y2="4" stroke-width="2"/><line x1="14" y1="20" x2="5" y2="20" stroke-width="2"/><line x1="15" y1="4" x2="9" y2="20" stroke-width="2"/></svg>
                    </button>

                    <button type="button" onclick="format('underline')" class="tb-btn" title="زیرخط">
                        <svg width="18" viewBox="0 0 24 24"><path d="M6 4v6a6 6 0 0 0 12 0V4" fill="none" stroke-width="2"/><line x1="4" y1="20" x2="20" y2="20" stroke-width="2"/></svg>
                    </button>

                    <div class="tb-sep"></div>

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

                    <div class="tb-sep"></div>

                    <button type="button" onclick="format('insertUnorderedList')" class="tb-btn" title="لیست">
                        <svg width="18" viewBox="0 0 24 24">
                            <circle cx="5" cy="6" r="2" fill="#333"/>
                            <circle cx="5" cy="12" r="2" fill="#333"/>
                            <circle cx="5" cy="18" r="2" fill="#333"/>
                            <line x1="10" y1="6" x2="20" y2="6" stroke-width="2"/>
                            <line x1="10" y1="12" x2="20" y2="12" stroke-width="2"/>
                            <line x1="10" y1="18" x2="20" y2="18" stroke-width="2"/>
                        </svg>
                    </button>

                    <button type="button" onclick="uploadSingleImage()" class="tb-btn" title="درج تصویر در متن">
                        <svg width="18" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </button>

                    <button type="button" onclick="insertLink()" class="tb-btn" title="پیوند">
                        <svg width="18" viewBox="0 0 24 24">
                            <path d="M10 14a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1" fill="none" stroke-width="2"/>
                            <path d="M14 10a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1" fill="none" stroke-width="2"/>
                        </svg>
                    </button>

                    <button type="button" onclick="clearFormat()" class="tb-btn" title="حذف فرمت">
                        <svg width="18" viewBox="0 0 24 24">
                            <path d="M3 6h18M8 6v12m8-12v12" stroke-width="2" fill="none"/>
                            <line x1="4" y1="18" x2="20" y2="4" stroke="red" stroke-width="2"/>
                        </svg>
                    </button>

                    <div class="tb-sep"></div>

                    <button type="button" onclick="alignRight()" class="tb-btn" title="راست‌چین">
                        <svg width="18" viewBox="0 0 24 24">
                            <line x1="21" y1="6" x2="3" y2="6" stroke-width="2"/>
                            <line x1="21" y1="12" x2="9" y2="12" stroke-width="2"/>
                            <line x1="21" y1="18" x2="6" y2="18" stroke-width="2"/>
                        </svg>
                    </button>

                    <button type="button" onclick="alignCenter()" class="tb-btn" title="وسط‌چین">
                        <svg width="18" viewBox="0 0 24 24">
                            <line x1="6" y1="6" x2="18" y2="6" stroke-width="2"/>
                            <line x1="4" y1="12" x2="20" y2="12" stroke-width="2"/>
                            <line x1="6" y1="18" x2="18" y2="18" stroke-width="2"/>
                        </svg>
                    </button>

                    <button type="button" onclick="alignLeft()" class="tb-btn" title="چپ‌چین">
                        <svg width="18" viewBox="0 0 24 24">
                            <line x1="3" y1="6" x2="21" y2="6" stroke-width="2"/>
                            <line x1="3" y1="12" x2="15" y2="12" stroke-width="2"/>
                            <line x1="3" y1="18" x2="18" y2="18" stroke-width="2"/>
                        </svg>
                    </button>

                    <button type="button" onclick="alignJustify()" class="tb-btn" title="هم‌تراز">
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

                <div class="editor-shell" id="editorShell">
                    <div id="editor" contenteditable="true" data-placeholder="متن خبر خود را اینجا آغاز کنید..."><?= $news_data ? $news_data['content'] : '' ?></div>

                    <!-- نوار ابزار شناور اختصاصی تصویر در ادیتور -->
                    <div id="imageFloatingToolbar" class="img-float-bar">
                        <!-- دکمه‌های چینش سریع -->
                        <button type="button" class="img-btn" id="imgBtnAlignRight" onclick="setImageAlignment('align-right')" title="راست‌چین (گردش متن دور عکس)">
                            <svg viewBox="0 0 24 24"><line x1="21" y1="6" x2="11" y2="6"/><line x1="21" y1="12" x2="11" y2="12"/><line x1="21" y1="18" x2="3" y2="18"/><rect x="3" y="4" width="6" height="8" rx="1"/></svg>
                            <span>راست</span>
                        </button>
                        <button type="button" class="img-btn" id="imgBtnAlignCenter" onclick="setImageAlignment('align-center')" title="وسط‌چین (خط مستقل)">
                            <svg viewBox="0 0 24 24"><line x1="3" y1="3" x2="21" y2="3"/><rect x="6" y="7" width="12" height="10" rx="1.5"/><line x1="3" y1="21" x2="21" y2="21"/></svg>
                            <span>وسط</span>
                        </button>
                        <button type="button" class="img-btn" id="imgBtnAlignLeft" onclick="setImageAlignment('align-left')" title="چپ‌چین (گردش متن دور عکس)">
                            <svg viewBox="0 0 24 24"><line x1="13" y1="6" x2="3" y2="6"/><line x1="13" y1="12" x2="3" y2="12"/><line x1="21" y1="18" x2="3" y2="18"/><rect x="15" y="4" width="6" height="8" rx="1"/></svg>
                            <span>چپ</span>
                        </button>
                        <button type="button" class="img-btn" id="imgBtnAlignFull" onclick="setImageAlignment('align-full')" title="تمام‌عرض (۱۰۰٪ کادر)">
                            <svg viewBox="0 0 24 24"><line x1="3" y1="4" x2="21" y2="4"/><rect x="3" y="8" width="18" height="8" rx="1.5"/><line x1="3" y1="20" x2="21" y2="20"/></svg>
                            <span>تمام‌عرض</span>
                        </button>

                        <div class="img-bar-sep"></div>

                        <!-- دکمه‌های اندازه سریع -->
                        <button type="button" class="img-btn" id="imgBtnSize25" onclick="setImageSize(25)" title="اندازه ۲۵٪">۲۵٪</button>
                        <button type="button" class="img-btn" id="imgBtnSize50" onclick="setImageSize(50)" title="اندازه ۵۰٪">۵۰٪</button>
                        <button type="button" class="img-btn" id="imgBtnSize75" onclick="setImageSize(75)" title="اندازه ۷۵٪">۷۵٪</button>
                        <button type="button" class="img-btn" id="imgBtnSize100" onclick="setImageSize(100)" title="اندازه ۱۰۰٪">۱۰۰٪</button>

                        <div class="img-bar-sep"></div>

                        <!-- ابزارهای تکمیلی -->
                        <button type="button" class="img-btn" onclick="toggleImageCaption()" title="افزودن یا ویرایش توضیح زیر عکس">
                            <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <span>کپشن</span>
                        </button>
                        <button type="button" class="img-btn" onclick="openImageSettings()" title="تنظیمات پیشرفته تصویر">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        </button>
                        <button type="button" class="img-btn" onclick="replaceSelectedImage()" title="تعویض تصویر با فایل دیگر">
                            <svg viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                        </button>
                        <button type="button" class="img-btn btn-danger-light" onclick="deleteSelectedImage()" title="حذف تصویر">
                            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        </button>
                    </div>

                    <!-- لایه ریسایزر و دستگیره‌های چهارگوشه -->
                    <div id="imageResizeOverlay" class="img-resize-box">
                        <div class="img-resize-handle handle-nw" data-handle="nw"></div>
                        <div class="img-resize-handle handle-ne" data-handle="ne"></div>
                        <div class="img-resize-handle handle-se" data-handle="se"></div>
                        <div class="img-resize-handle handle-sw" data-handle="sw"></div>
                        <div id="imgSizeBadge" class="img-size-badge">۵۰٪</div>
                    </div>
                </div>

                <div class="field-error" id="contentError">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>نوشتن متن خبر الزامی است.</span>
                </div>

                <!-- درج تصویر در متن -->
                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:14px;">
                    <button type="button" class="btn-insert" onclick="uploadSingleImage()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        درج تصویر تکی
                    </button>
                    <button type="button" class="btn-insert" onclick="uploadGalleryInline()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        درج گالری سه‌تایی
                    </button>
                    <span style="font-size:12px;color:var(--muted-color);align-self:center;">می‌توانید تصویر را با ماوس بکشید یا مستقیماً در متن پیست (Ctrl+V) کنید.</span>
                </div>

            </div>

        </div>

        <!-- ستون کناری: متادیتا -->
        <div class="col-side">

            <!-- تصویر شاخص -->
            <div class="card card-pad">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    تصویر شاخص
                </h3>

                <input type="file" id="featured_image" name="featured_image" accept="image/*" style="display:none;">
                <input type="hidden" id="remove_featured_flag" name="remove_featured_flag" value="0">

                <div class="dropzone" id="dropzone">
                    <div class="dz-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    </div>
                    <div class="dz-main">تصویر را اینجا بکشید یا کلیک کنید</div>
                    <div class="dz-hint">PNG, JPG حداکثر ۵ مگابایت</div>

                    <div class="dz-preview <?= !empty($existing_image_url) ? 'show' : '' ?>" id="dzPreview">
                        <img id="dzImg" src="<?= htmlspecialchars($existing_image_url) ?>" alt="پیش‌نمایش تصویر">
                        <button class="dz-remove" type="button" onclick="removeFeatured(event)" title="حذف تصویر">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- امتیاز سئو -->
            <div class="card card-pad">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    امتیاز سئو
                </h3>
                <div class="seo-widget">
                    <div class="seo-ring">
                        <svg viewBox="0 0 36 36">
                            <path class="ring-track" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke-width="3"/>
                            <path class="ring-fill" id="seoRingFill" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#F79F1F" stroke-dasharray="0, 100" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        <span class="ring-num" id="seoRingNum">۰</span>
                    </div>
                    <div class="seo-info">
                        <div class="seo-state">وضعیت: <b id="seoState" style="color:var(--muted-color)">—</b></div>
                        <div class="seo-tip" id="seoTip">با پر کردن فیلدها امتیاز محاسبه می‌شود.</div>
                    </div>
                </div>
            </div>

            <!-- تنظیمات -->
            <div class="card settings-card">

                <div class="input-group">
                    <label class="field-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        تاریخ و زمان انتشار
                    </label>
                    <div class="publish-date-row">
                        <input type="hidden" id="publish_date" name="publish_date" value="<?= htmlspecialchars($news_data['publish_date'] ?? '') ?>">
                        <input type="text" id="publish_date_display" class="input" readonly placeholder="تاریخ و زمان شمسی را انتخاب کنید">
                        <button type="button" class="btn-insert" onclick="openDatePicker()">📅 انتخاب</button>
                        <button type="button" class="btn-insert now-btn" onclick="setNow()">همین الان</button>
                    </div>
                </div>

                <div class="input-group" id="categoryGroup">
                    <label class="field-label" for="category_id">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        دسته خبر
                    </label>
                    <select id="category_id" name="category_id" class="input" required>
                        <option value="">انتخاب دسته</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int)$category['id'] ?>" <?= $selectedCategoryId === (int)$category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="field-error" id="categoryError">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>انتخاب دسته‌بندی الزامی است.</span>
                    </div>
                </div>

                <div class="input-group">
                    <label class="field-label" for="author">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        نویسنده
                    </label>
                    <input type="text" class="input" id="author" value="<?= $news_data ? htmlspecialchars($news_data['author']) : '' ?>" placeholder="نام نویسنده">
                </div>

                <div class="input-group">
                    <label class="field-label" for="keywords">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><circle cx="12" cy="12" r="2"/></svg>
                        کلمات کلیدی
                    </label>
                    <input type="text" class="input" id="keywords" value="<?= $news_data ? htmlspecialchars($news_data['keywords']) : '' ?>" placeholder="کلمات کلیدی را وارد کنید">
                </div>

                <div class="input-group">
                    <label class="field-label" for="tags">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        تگ‌های سئو
                    </label>
                    <input type="text" class="input" id="tags" value="<?= $news_data ? htmlspecialchars($news_data['tags']) : '' ?>" placeholder="تگ‌ها را با کاما جدا کنید">
                    <div class="chips" id="tagsChips"></div>
                </div>

                <div class="input-group">
                    <label class="field-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        برچسب‌های موضوعی خبر (چند انتخابی)
                    </label>
                    <div class="tags-checklist" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
                        <?php foreach ($all_tags as $t): ?>
                            <label class="tag-checkbox-label">
                                <input type="checkbox" name="tag_ids[]" value="<?= $t['id'] ?>" <?= in_array($t['id'], $selected_tag_ids) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($t['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<!-- توست وضعیت (موفقیت / خطا / در حال انجام) -->
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
    <div id="uploadProgress" class="toast-progress">
        <div class="bar-track"><div id="uploadBarFill" class="bar-fill"></div></div>
    </div>
</div>

<!-- مودال پیش‌نمایش -->
<div id="previewModal">
    <div class="modal-content">
        <div class="preview-bar">
            <div class="preview-bar-title">
                <span class="ph-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </span>
                پیش‌نمایش خبر
            </div>
            <button onclick="closePreview()" class="preview-close" title="بستن">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="preview-scroll">
            <div id="previewContent"></div>
        </div>
    </div>
</div>

<!-- مودال تنظیمات پیشرفته تصویر -->
<div id="imageSettingsModal" class="img-settings-modal" onclick="if(event.target===this) closeImageSettings()">
    <div class="img-modal-box">
        <div class="img-modal-head">
            <div class="img-modal-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                تنظیمات پیشرفته تصویر
            </div>
            <button type="button" class="img-modal-close" onclick="closeImageSettings()" aria-label="بستن">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="img-modal-body">
            <!-- پیش‌نمایش تصویر در مودال -->
            <div class="img-modal-preview-box">
                <img id="imgModalPreviewImg" src="" alt="پیش‌نمایش">
                <div id="imgModalPreviewCaption" style="font-size:12px; color:var(--muted-color); margin-top:6px;"></div>
            </div>

            <!-- تنظیم عرض -->
            <div class="img-field-group">
                <label class="img-field-label">
                    <span>عرض تصویر در متن:</span>
                    <strong id="imgModalWidthLabel" style="color:var(--primary-color);">50%</strong>
                </label>
                <div class="img-slider-row">
                    <input type="range" id="imgModalWidthSlider" min="15" max="100" step="5" value="50" class="img-range" oninput="updateImageModalWidth(this.value)">
                    <div class="img-quick-pills">
                        <button type="button" class="pill-btn" onclick="setImageModalWidth(25)">۲۵٪</button>
                        <button type="button" class="pill-btn" onclick="setImageModalWidth(50)">۵۰٪</button>
                        <button type="button" class="pill-btn" onclick="setImageModalWidth(75)">۷۵٪</button>
                        <button type="button" class="pill-btn" onclick="setImageModalWidth(100)">۱۰۰٪</button>
                    </div>
                </div>
            </div>

            <!-- نحوه چینش -->
            <div class="img-field-group">
                <label class="img-field-label">موقعیت و چینش تصویر:</label>
                <div class="img-align-grid">
                    <label class="img-align-card">
                        <input type="radio" name="img_align" value="align-right">
                        <div class="align-card-inner">
                            <svg viewBox="0 0 24 24"><line x1="21" y1="6" x2="11" y2="6"/><line x1="21" y1="12" x2="11" y2="12"/><line x1="21" y1="18" x2="3" y2="18"/><rect x="3" y="4" width="6" height="8" rx="1"/></svg>
                            <span>راست‌چین (شناور)</span>
                        </div>
                    </label>
                    <label class="img-align-card">
                        <input type="radio" name="img_align" value="align-center" checked>
                        <div class="align-card-inner">
                            <svg viewBox="0 0 24 24"><line x1="3" y1="3" x2="21" y2="3"/><rect x="6" y="7" width="12" height="10" rx="1.5"/><line x1="3" y1="21" x2="21" y2="21"/></svg>
                            <span>وسط‌چین (مستقل)</span>
                        </div>
                    </label>
                    <label class="img-align-card">
                        <input type="radio" name="img_align" value="align-left">
                        <div class="align-card-inner">
                            <svg viewBox="0 0 24 24"><line x1="13" y1="6" x2="3" y2="6"/><line x1="13" y1="12" x2="3" y2="12"/><line x1="21" y1="18" x2="3" y2="18"/><rect x="15" y="4" width="6" height="8" rx="1"/></svg>
                            <span>چپ‌چین (شناور)</span>
                        </div>
                    </label>
                    <label class="img-align-card">
                        <input type="radio" name="img_align" value="align-full">
                        <div class="align-card-inner">
                            <svg viewBox="0 0 24 24"><line x1="3" y1="4" x2="21" y2="4"/><rect x="3" y="8" width="18" height="8" rx="1.5"/><line x1="3" y1="20" x2="21" y2="20"/></svg>
                            <span>تمام‌عرض</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- توضیح زیر تصویر (کپشن) -->
            <div class="img-field-group">
                <label class="img-field-label" for="imgModalCaption">توضیح زیر عکس (کپشن):</label>
                <input type="text" id="imgModalCaption" class="input" placeholder="متنی که زیر عکس نمایش داده می‌شود..." oninput="document.getElementById('imgModalPreviewCaption').textContent = this.value">
            </div>

            <!-- متن جایگزین Alt -->
            <div class="img-field-group">
                <label class="img-field-label" for="imgModalAlt">متن جایگزین (Alt برای سئو):</label>
                <input type="text" id="imgModalAlt" class="input" placeholder="توضیح موضوع تصویر برای موتورهای جستجو...">
            </div>

            <!-- پیوند / لینک تصویر -->
            <div class="img-field-group">
                <label class="img-field-label" for="imgModalLink">پیوند روی عکس (اختیاری):</label>
                <input type="url" id="imgModalLink" class="input" placeholder="https://example.com" dir="ltr">
                <label class="img-checkbox-label" style="margin-top:6px;">
                    <input type="checkbox" id="imgModalLinkBlank" checked>
                    <span>باز شدن لینک در پنجره/تب جدید</span>
                </label>
            </div>

            <!-- سبک قاب و گردی گوشه‌ها -->
            <div class="img-field-group">
                <label class="img-field-label">سبک و گردی گوشه‌ها:</label>
                <div class="img-style-pills">
                    <label class="pill-choice">
                        <input type="radio" name="img_radius" value="0px" onchange="document.getElementById('imgModalPreviewImg').style.borderRadius = this.value">
                        <span>گوشه‌های تیز</span>
                    </label>
                    <label class="pill-choice">
                        <input type="radio" name="img_radius" value="12px" checked onchange="document.getElementById('imgModalPreviewImg').style.borderRadius = this.value">
                        <span>گرد معمولی</span>
                    </label>
                    <label class="pill-choice">
                        <input type="radio" name="img_radius" value="24px" onchange="document.getElementById('imgModalPreviewImg').style.borderRadius = this.value">
                        <span>بسیار گرد</span>
                    </label>
                </div>
            </div>

        </div>

        <div class="img-modal-foot">
            <button type="button" class="btn btn-ghost" onclick="closeImageSettings()">انصراف</button>
            <button type="button" class="btn btn-save" onclick="applyImageSettingsModal()">اعمال تغییرات</button>
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

        <!-- ناوبری ماه -->
        <div class="dp-nav">
            <button type="button" class="dp-nav-btn" onclick="dpChangeMonth(1)" aria-label="ماه بعد">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div class="dp-month-label">
                <button type="button" class="dp-label-btn" id="dpMonthBtn" onclick="dpToggleView('months')">—</button>
                <button type="button" class="dp-label-btn" id="dpYearBtn" onclick="dpToggleView('years')">—</button>
            </div>
            <button type="button" class="dp-nav-btn" onclick="dpChangeMonth(-1)" aria-label="ماه قبل">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>

        <!-- نمای روز -->
        <div class="dp-view" id="dpDayView">
            <div class="dp-weekdays">
                <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span>
            </div>
            <div class="dp-days" id="dpDays"></div>
        </div>
        <!-- نمای ماه -->
        <div class="dp-view dp-grid-pick" id="dpMonthView" style="display:none;"></div>
        <!-- نمای سال -->
        <div class="dp-view dp-grid-pick" id="dpYearView" style="display:none;"></div>

        <!-- انتخاب زمان -->
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

        <!-- state مخفی که توابع تبدیل از آن می‌خوانند/می‌نویسند -->
        <input type="hidden" id="jy"><input type="hidden" id="jm"><input type="hidden" id="jd">
        <input type="hidden" id="hh"><input type="hidden" id="mm">
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

/* =================== شمارنده عنوان و زیرعنوان =================== */
const titleInput = document.getElementById("title");
const titleCounter = document.getElementById("titleCounter");
function updateTitleCounter(){
    const len = titleInput.value.length;
    titleCounter.textContent = len + "/120";
    titleCounter.classList.toggle("over", len > 100);
}
titleInput.addEventListener("input", updateTitleCounter);

const subtitleInput = document.getElementById("subtitle");
const subtitleCounter = document.getElementById("subtitleCounter");
function updateSubtitleCounter(){
    const len = subtitleInput.value.length;
    subtitleCounter.textContent = len + "/200";
    subtitleCounter.classList.toggle("over", len > 180);
}
subtitleInput.addEventListener("input", updateSubtitleCounter);

/* =================== تصویر شاخص (Dropzone) =================== */
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
["dragenter","dragover"].forEach(ev => {
    dropzone.addEventListener(ev, () => dropzone.classList.add("drag-over"));
});
["dragleave","drop"].forEach(ev => {
    dropzone.addEventListener(ev, () => dropzone.classList.remove("drag-over"));
});
dropzone.addEventListener("drop", (e) => {
    const files = e.dataTransfer.files;
    if (files && files[0]) {
        featuredInput.files = files;
        showFeaturedPreview(files[0]);
    }
});

featuredInput.onchange = function(){
    document.getElementById("remove_featured_flag").value = "0";
    if (this.files[0]) showFeaturedPreview(this.files[0]);
};

function showFeaturedPreview(file){
    document.getElementById("remove_featured_flag").value = "0";
    const reader = new FileReader();
    reader.onload = e => {
        dzImg.src = e.target.result;
        dzPreview.classList.add("show");
    };
    reader.readAsDataURL(file);
}

function removeFeatured(e){
    if (e) e.stopPropagation();
    featuredInput.value = "";
    dzImg.src = "";
    dzPreview.classList.remove("show");
    document.getElementById("remove_featured_flag").value = "1";
}

/* =================================================================
   مدیریت پیشرفته و تعاملی تصاویر داخل متن خبر (Image Controller Engine)
   ================================================================= */
let activeSelectedImg = null;
let activeSelectedWrapper = null;
let isResizingImage = false;

// انتخاب تصویر با کلیک روی هر عکس در ادیتور
editor.addEventListener("click", function(e) {
    const targetImg = e.target.closest("img");
    if (targetImg && editor.contains(targetImg)) {
        e.stopPropagation();
        selectEditorImage(targetImg);
        return;
    }
    // اگر روی دکمه‌های نوار ابزار یا ریسایزر یا مودال کلیک نشده باشد، عدم انتخاب
    if (!e.target.closest(".img-float-bar") && !e.target.closest(".img-resize-box") && !e.target.closest(".img-settings-modal")) {
        deselectEditorImage();
    }
});

// بستن نوار ابزار در صورت کلیک خارج از محیط ادیتور
document.addEventListener("click", function(e) {
    if (!editor.contains(e.target) && !e.target.closest(".img-float-bar") && !e.target.closest(".img-resize-box") && !e.target.closest(".img-settings-modal") && !e.target.closest(".tb-btn")) {
        deselectEditorImage();
    }
});

// همگام‌سازی مکان تولبار هنگام اسکرول ادیتور یا تغییر اندازه پنجره
editor.addEventListener("scroll", updateFloatingToolbarPosition);
window.addEventListener("scroll", updateFloatingToolbarPosition, true);
window.addEventListener("resize", updateFloatingToolbarPosition);

function selectEditorImage(imgEl) {
    if (!imgEl) return;
    deselectEditorImage();

    activeSelectedImg = imgEl;
    activeSelectedWrapper = imgEl.closest(".article-img-wrap") || imgEl.closest("figure") || null;

    if (activeSelectedWrapper) {
        activeSelectedWrapper.classList.add("img-selected");
    } else {
        activeSelectedImg.classList.add("img-selected");
    }

    updateFloatingToolbarPosition();
    updateFloatingToolbarState();
}

function deselectEditorImage() {
    if (activeSelectedImg) {
        activeSelectedImg.classList.remove("img-selected");
    }
    if (activeSelectedWrapper) {
        activeSelectedWrapper.classList.remove("img-selected");
    }
    activeSelectedImg = null;
    activeSelectedWrapper = null;

    const bar = document.getElementById("imageFloatingToolbar");
    const overlay = document.getElementById("imageResizeOverlay");
    if (bar) bar.style.display = "none";
    if (overlay) overlay.style.display = "none";
}

function updateFloatingToolbarPosition() {
    if (!activeSelectedImg) return;

    const targetEl = activeSelectedWrapper || activeSelectedImg;
    const editorShell = document.getElementById("editorShell");
    if (!editorShell || !targetEl.isConnected) {
        deselectEditorImage();
        return;
    }

    const shellRect = editorShell.getBoundingClientRect();
    const imgRect = targetEl.getBoundingClientRect();

    const topRel = imgRect.top - shellRect.top + editorShell.scrollTop;
    const leftRel = imgRect.left - shellRect.left + editorShell.scrollLeft;
    const width = imgRect.width;
    const height = imgRect.height;

    // موقعیت‌دهی کادر ریسایزر
    const overlay = document.getElementById("imageResizeOverlay");
    if (overlay) {
        overlay.style.display = "block";
        overlay.style.top = topRel + "px";
        overlay.style.left = leftRel + "px";
        overlay.style.width = width + "px";
        overlay.style.height = height + "px";

        const badge = document.getElementById("imgSizeBadge");
        if (badge) {
            const editorWidth = editor.clientWidth - 32;
            const pct = Math.round((width / editorWidth) * 100);
            badge.textContent = toFaDigits(pct + "٪") + " • " + toFaDigits(Math.round(width) + "px");
        }
    }

    // موقعیت‌دهی نوار ابزار شناور
    const bar = document.getElementById("imageFloatingToolbar");
    if (bar) {
        bar.style.display = "flex";
        
        const centerX = leftRel + (width / 2);
        bar.style.left = Math.max(160, Math.min(shellRect.width - 160, centerX)) + "px";

        if (topRel > 56) {
            bar.style.top = (topRel - 52) + "px";
            bar.classList.add("arrow-bottom");
            bar.classList.remove("arrow-top");
        } else {
            bar.style.top = (topRel + height + 12) + "px";
            bar.classList.add("arrow-top");
            bar.classList.remove("arrow-bottom");
        }
    }
}

function updateFloatingToolbarState() {
    if (!activeSelectedImg) return;
    const targetEl = activeSelectedWrapper || activeSelectedImg;

    const isRight = targetEl.classList.contains("align-right");
    const isLeft = targetEl.classList.contains("align-left");
    const isFull = targetEl.classList.contains("align-full");
    const isCenter = targetEl.classList.contains("align-center") || (!isRight && !isLeft && !isFull);

    document.getElementById("imgBtnAlignRight")?.classList.toggle("active", isRight);
    document.getElementById("imgBtnAlignCenter")?.classList.toggle("active", isCenter);
    document.getElementById("imgBtnAlignLeft")?.classList.toggle("active", isLeft);
    document.getElementById("imgBtnAlignFull")?.classList.toggle("active", isFull);

    const wStyle = targetEl.style.width || activeSelectedImg.style.width || "";
    const pct = parseInt(wStyle) || 0;
    document.getElementById("imgBtnSize25")?.classList.toggle("active", pct >= 20 && pct <= 30);
    document.getElementById("imgBtnSize50")?.classList.toggle("active", pct >= 45 && pct <= 55);
    document.getElementById("imgBtnSize75")?.classList.toggle("active", pct >= 70 && pct <= 80);
    document.getElementById("imgBtnSize100")?.classList.toggle("active", isFull || (pct >= 95 && pct <= 100));
}

/* تغییر چینش تصویر (راست، وسط، چپ، تمام‌عرض) */
function setImageAlignment(alignClass) {
    if (!activeSelectedImg) return;
    const targetEl = activeSelectedWrapper || activeSelectedImg;

    ["align-right", "align-center", "align-left", "align-full"].forEach(cls => {
        targetEl.classList.remove(cls);
        activeSelectedImg.classList.remove(cls);
    });

    targetEl.classList.add(alignClass);
    if (activeSelectedWrapper) {
        activeSelectedImg.classList.add(alignClass);
    }

    if (alignClass === "align-full") {
        targetEl.style.width = "100%";
        activeSelectedImg.style.width = "100%";
    } else if (targetEl.style.width === "100%" || !targetEl.style.width) {
        targetEl.style.width = "50%";
        if (activeSelectedWrapper) activeSelectedImg.style.width = "100%";
    }

    updateFloatingToolbarState();
    setTimeout(updateFloatingToolbarPosition, 40);
}

/* تغییر اندازه سریع درصدی */
function setImageSize(percentage) {
    if (!activeSelectedImg) return;
    const targetEl = activeSelectedWrapper || activeSelectedImg;

    if (percentage === 100) {
        setImageAlignment("align-full");
        return;
    }

    if (targetEl.classList.contains("align-full")) {
        setImageAlignment("align-center");
    }

    targetEl.style.width = percentage + "%";
    targetEl.style.maxWidth = "100%";
    if (activeSelectedWrapper) {
        activeSelectedImg.style.width = "100%";
    }

    updateFloatingToolbarState();
    setTimeout(updateFloatingToolbarPosition, 40);
}

/* تغییر وضعیت و ویرایش کپشن */
function toggleImageCaption() {
    if (!activeSelectedImg) return;

    if (!activeSelectedWrapper) {
        const figure = document.createElement("figure");
        figure.className = "article-img-wrap " + (activeSelectedImg.className || "align-center");
        figure.style.cssText = activeSelectedImg.style.cssText;
        if (!figure.style.width) figure.style.width = "60%";

        activeSelectedImg.parentNode.insertBefore(figure, activeSelectedImg);
        figure.appendChild(activeSelectedImg);
        activeSelectedImg.style.width = "100%";

        const figcap = document.createElement("figcaption");
        figcap.className = "img-caption";
        figcap.contentEditable = "true";
        figcap.setAttribute("data-placeholder", "توضیح زیر عکس (اختیاری)...");
        figcap.textContent = activeSelectedImg.alt && activeSelectedImg.alt !== "تصویر خبر" ? activeSelectedImg.alt : "";
        figure.appendChild(figcap);

        activeSelectedWrapper = figure;
        selectEditorImage(activeSelectedImg);
        figcap.focus();
    } else {
        let figcap = activeSelectedWrapper.querySelector("figcaption");
        if (figcap) {
            figcap.focus();
        } else {
            figcap = document.createElement("figcaption");
            figcap.className = "img-caption";
            figcap.contentEditable = "true";
            figcap.setAttribute("data-placeholder", "توضیح زیر عکس (اختیاری)...");
            activeSelectedWrapper.appendChild(figcap);
            figcap.focus();
        }
    }
    updateFloatingToolbarPosition();
}

/* حذف تصویر انتخاب شده */
function deleteSelectedImage() {
    if (!activeSelectedImg) return;
    const targetEl = activeSelectedWrapper || activeSelectedImg;
    targetEl.remove();
    deselectEditorImage();
    showStatus("تصویر حذف شد.", true);
}

/* تعویض تصویر جاری */
function replaceSelectedImage() {
    if (!activeSelectedImg) return;
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";
    input.onchange = async () => {
        const file = input.files[0];
        if (!file) return;
        showStatus("⏳ در حال بارگذاری تصویر جدید...", true);
        const fd = new FormData();
        fd.append("image", file);
        try {
            const res = await fetch("upload-inline-image.php", { method: "POST", body: fd });
            const data = await res.json();
            if (data.url && activeSelectedImg) {
                activeSelectedImg.src = data.url;
                showStatus("✅ تصویر جایگزین شد.", true);
                setTimeout(updateFloatingToolbarPosition, 100);
            }
        } catch(err) {
            showStatus("❌ خطا در آپلود تصویر جایگزین", false);
        }
    };
    input.click();
}

/* =================================================================
   دستگیره‌های تغییر اندازه تعاملی با ماوس (Interactive Drag-to-Resize)
   ================================================================= */
function initImageDragResizer() {
    const handles = document.querySelectorAll(".img-resize-handle");
    handles.forEach(handle => {
        handle.addEventListener("mousedown", startImageResize);
    });
}

function startImageResize(e) {
    if (!activeSelectedImg) return;
    e.preventDefault();
    e.stopPropagation();

    isResizingImage = true;
    const handleType = e.target.getAttribute("data-handle");
    const targetEl = activeSelectedWrapper || activeSelectedImg;
    const initialWidth = targetEl.offsetWidth;
    const initialX = e.clientX;
    const editorWidth = editor.clientWidth - 32;

    function onMouseMove(moveEvent) {
        if (!isResizingImage) return;
        moveEvent.preventDefault();

        const deltaX = moveEvent.clientX - initialX;
        let newWidth;

        if (handleType === "se" || handleType === "ne") {
            newWidth = initialWidth - deltaX;
        } else {
            newWidth = initialWidth + deltaX;
        }

        newWidth = Math.max(100, Math.min(editorWidth, newWidth));
        const pct = Math.max(15, Math.min(100, Math.round((newWidth / editorWidth) * 100)));

        targetEl.style.width = pct + "%";
        targetEl.style.maxWidth = "100%";
        if (activeSelectedWrapper) {
            activeSelectedImg.style.width = "100%";
        }

        updateFloatingToolbarPosition();
    }

    function onMouseUp() {
        isResizingImage = false;
        window.removeEventListener("mousemove", onMouseMove);
        window.removeEventListener("mouseup", onMouseUp);
        updateFloatingToolbarState();
        updateFloatingToolbarPosition();
    }

    window.addEventListener("mousemove", onMouseMove);
    window.addEventListener("mouseup", onMouseUp);
}

// مقداردهی اولیه ریسایزر
initImageDragResizer();

/* =================================================================
   مودال تنظیمات پیشرفته تصویر (Image Settings Modal)
   ================================================================= */
function openImageSettings() {
    if (!activeSelectedImg) return;
    const targetEl = activeSelectedWrapper || activeSelectedImg;

    const prevImg = document.getElementById("imgModalPreviewImg");
    const prevCap = document.getElementById("imgModalPreviewCaption");
    prevImg.src = activeSelectedImg.src;

    const wVal = parseInt(targetEl.style.width) || 50;
    document.getElementById("imgModalWidthSlider").value = wVal;
    document.getElementById("imgModalWidthLabel").textContent = wVal + "%";

    let currentAlign = "align-center";
    if (targetEl.classList.contains("align-right")) currentAlign = "align-right";
    else if (targetEl.classList.contains("align-left")) currentAlign = "align-left";
    else if (targetEl.classList.contains("align-full")) currentAlign = "align-full";

    const alignRadios = document.querySelectorAll('input[name="img_align"]');
    alignRadios.forEach(r => { r.checked = (r.value === currentAlign); });

    const figcap = activeSelectedWrapper ? activeSelectedWrapper.querySelector("figcaption") : null;
    const capText = figcap ? figcap.textContent.trim() : "";
    document.getElementById("imgModalCaption").value = capText;
    prevCap.textContent = capText;

    document.getElementById("imgModalAlt").value = activeSelectedImg.alt || "";

    const parentLink = activeSelectedImg.closest("a");
    document.getElementById("imgModalLink").value = parentLink ? parentLink.getAttribute("href") : "";
    document.getElementById("imgModalLinkBlank").checked = parentLink ? (parentLink.target === "_blank") : true;

    const rVal = activeSelectedImg.style.borderRadius || "12px";
    const radiusRadios = document.querySelectorAll('input[name="img_radius"]');
    radiusRadios.forEach(r => { r.checked = (r.value === rVal); });
    prevImg.style.borderRadius = rVal;

    const modal = document.getElementById("imageSettingsModal");
    modal.style.display = "flex";
    requestAnimationFrame(() => modal.classList.add("show"));
}

function closeImageSettings() {
    const modal = document.getElementById("imageSettingsModal");
    modal.classList.remove("show");
    setTimeout(() => { modal.style.display = "none"; }, 220);
}

function updateImageModalWidth(val) {
    document.getElementById("imgModalWidthLabel").textContent = val + "%";
}

function setImageModalWidth(val) {
    document.getElementById("imgModalWidthSlider").value = val;
    document.getElementById("imgModalWidthLabel").textContent = val + "%";
}

function applyImageSettingsModal() {
    if (!activeSelectedImg) return;

    const wVal = document.getElementById("imgModalWidthSlider").value;
    const alignVal = document.querySelector('input[name="img_align"]:checked')?.value || "align-center";
    const capVal = document.getElementById("imgModalCaption").value.trim();
    const altVal = document.getElementById("imgModalAlt").value.trim();
    const linkVal = document.getElementById("imgModalLink").value.trim();
    const isBlank = document.getElementById("imgModalLinkBlank").checked;
    const radiusVal = document.querySelector('input[name="img_radius"]:checked')?.value || "12px";

    if (capVal && !activeSelectedWrapper) {
        const figure = document.createElement("figure");
        figure.className = "article-img-wrap " + alignVal;
        figure.style.width = wVal + "%";
        activeSelectedImg.parentNode.insertBefore(figure, activeSelectedImg);
        figure.appendChild(activeSelectedImg);
        activeSelectedWrapper = figure;
    }

    const targetEl = activeSelectedWrapper || activeSelectedImg;

    ["align-right", "align-center", "align-left", "align-full"].forEach(cls => {
        targetEl.classList.remove(cls);
        activeSelectedImg.classList.remove(cls);
    });
    targetEl.classList.add(alignVal);
    if (activeSelectedWrapper) activeSelectedImg.classList.add(alignVal);

    if (alignVal === "align-full") {
        targetEl.style.width = "100%";
        activeSelectedImg.style.width = "100%";
    } else {
        targetEl.style.width = wVal + "%";
        if (activeSelectedWrapper) activeSelectedImg.style.width = "100%";
    }

    activeSelectedImg.alt = altVal;
    activeSelectedImg.style.borderRadius = radiusVal;

    if (activeSelectedWrapper) {
        let figcap = activeSelectedWrapper.querySelector("figcaption");
        if (capVal) {
            if (!figcap) {
                figcap = document.createElement("figcaption");
                figcap.className = "img-caption";
                figcap.contentEditable = "true";
                figcap.setAttribute("data-placeholder", "توضیح زیر عکس (اختیاری)...");
                activeSelectedWrapper.appendChild(figcap);
            }
            figcap.textContent = capVal;
        } else if (figcap) {
            figcap.remove();
        }
    }

    const existingLink = activeSelectedImg.closest("a");
    if (linkVal) {
        if (existingLink) {
            existingLink.href = linkVal;
            existingLink.target = isBlank ? "_blank" : "_self";
            if (isBlank) existingLink.rel = "noopener noreferrer";
        } else {
            const a = document.createElement("a");
            a.href = linkVal;
            a.target = isBlank ? "_blank" : "_self";
            if (isBlank) a.rel = "noopener noreferrer";
            activeSelectedImg.parentNode.insertBefore(a, activeSelectedImg);
            a.appendChild(activeSelectedImg);
        }
    } else if (existingLink) {
        existingLink.parentNode.insertBefore(activeSelectedImg, existingLink);
        existingLink.remove();
    }

    closeImageSettings();
    selectEditorImage(activeSelectedImg);
    showStatus("✅ تنظیمات تصویر با موفقیت ذخیره شد.", true);
}

/* =================================================================
   درج تصویر و آپلود مستقیم (Paste & Drag Drop & Gallery)
   ================================================================= */
// پیست مستقیم عکس (Ctrl+V) در ادیتور
editor.addEventListener("paste", function(e) {
    const items = (e.clipboardData || window.clipboardData)?.items;
    if (!items) return;
    for (let i = 0; i < items.length; i++) {
        if (items[i].type.indexOf("image") !== -1) {
            e.preventDefault();
            const file = items[i].getAsFile();
            if (file) {
                uploadAndInsertInlineFile(file);
                return;
            }
        }
    }
});

// کشیدن و رها کردن فایل عکس به داخل ادیتور (Drag & Drop)
editor.addEventListener("dragover", function(e) {
    if (e.dataTransfer.types && Array.from(e.dataTransfer.types).includes("Files")) {
        e.preventDefault();
        editor.style.borderColor = "var(--primary-color)";
    }
});
editor.addEventListener("dragleave", function(e) {
    editor.style.borderColor = "";
});
editor.addEventListener("drop", function(e) {
    editor.style.borderColor = "";
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        const file = e.dataTransfer.files[0];
        if (file.type.startsWith("image/")) {
            e.preventDefault();
            uploadAndInsertInlineFile(file);
        }
    }
});

async function uploadAndInsertInlineFile(file) {
    showStatus("⏳ در حال آپلود تصویر در متن...", true);
    const fd = new FormData();
    fd.append("image", file);
    try {
        const res = await fetch("upload-inline-image.php", { method: "POST", body: fd });
        const data = await res.json();
        if (data.url) {
            insertSingleImage(data.url);
            showStatus("✅ تصویر در متن درج شد.", true);
        }
    } catch (err) {
        showStatus("❌ خطا در آپلود تصویر", false);
    }
}

function uploadSingleImage(){
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";

    input.onchange = ()=>{
        const file = input.files[0];
        if (!file) return;

        showStatus("⏳ در حال آپلود تصویر...", true);
        const fd = new FormData();
        fd.append("image", file);

        fetch("upload-inline-image.php", {method:"POST", body:fd})
        .then(r=>r.json())
        .then(d=>{
            if (d.url) {
                insertSingleImage(d.url);
                showStatus("✅ تصویر درج شد.", true);
            }
        })
        .catch(() => showStatus("❌ خطا در ارسال تصویر", false));
    };
    input.click();
}

function insertSingleImage(url){
    const figure = document.createElement("figure");
    figure.className = "article-img-wrap align-center";
    figure.style.cssText = "width:60%; max-width:100%; margin:24px auto; display:block; text-align:center;";

    const img = document.createElement("img");
    img.src = url;
    img.alt = "تصویر خبر";
    img.className = "article-inline-img align-center";
    img.style.cssText = "width:100%; height:auto; border-radius:12px; display:block;";
    figure.appendChild(img);

    const figcap = document.createElement("figcaption");
    figcap.className = "img-caption";
    figcap.contentEditable = "true";
    figcap.setAttribute("data-placeholder", "توضیح زیر عکس (اختیاری)...");
    figure.appendChild(figcap);

    const p = document.createElement("p");
    p.innerHTML = "<br>";

    restoreSelection();
    const sel = window.getSelection();
    if (sel.rangeCount){
        const range = sel.getRangeAt(0);
        range.deleteContents();
        range.insertNode(p);
        range.insertNode(figure);
    } else {
        editor.appendChild(figure);
        editor.appendChild(p);
    }
    saveSelection();

    setTimeout(() => {
        selectEditorImage(img);
    }, 120);
}

function uploadGalleryInline(){
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";
    input.multiple = true;

    input.onchange = ()=>{
        const files = Array.from(input.files);
        if (!files.length) return;

        showStatus("⏳ در حال آپلود گالری تصاویر...", true);
        let urls = [];

        Promise.all(files.map(file=>{
            const fd = new FormData();
            fd.append("image", file);
            return fetch("upload-inline-image.php", {method:"POST", body:fd})
                .then(r=>r.json())
                .then(d=>{ if (d.url) urls.push(d.url); });
        })).then(()=> {
            insertGalleryRow(urls);
            showStatus("✅ گالری تصاویر درج شد.", true);
        }).catch(() => showStatus("❌ خطا در آپلود گالری", false));
    };
    input.click();
}

function insertGalleryRow(urls){
    const row = document.createElement("div");
    row.className = "gallery-row";
    row.style.cssText = "display:flex;gap:12px;margin:20px 0;flex-wrap:wrap;clear:both;";

    urls.slice(0,4).forEach(u=>{
        const img = document.createElement("img");
        img.src = u;
        img.style.cssText = "flex:1;min-width:30%;max-width:100%;border-radius:10px;object-fit:cover;cursor:pointer;";
        row.appendChild(img);
    });

    const p = document.createElement("p");
    p.innerHTML = "<br>";

    restoreSelection();
    const sel = window.getSelection();
    if (sel.rangeCount){
        const range = sel.getRangeAt(0);
        range.deleteContents();
        range.insertNode(p);
        range.insertNode(row);
    } else {
        editor.appendChild(row);
        editor.appendChild(p);
    }
    saveSelection();
}

/* =================== چیپ تگ‌ها (نمایشی) =================== */
const tagsInput = document.getElementById("tags");
const tagsChips = document.getElementById("tagsChips");
function renderTagChips(){
    const parts = tagsInput.value.split("،").join(",").split(",").map(t=>t.trim()).filter(Boolean);
    tagsChips.innerHTML = "";
    parts.forEach((t, idx) => {
        const chip = document.createElement("span");
        chip.className = "chip";
        chip.textContent = t;
        const btn = document.createElement("button");
        btn.type = "button";
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
        btn.onclick = () => { parts.splice(idx,1); tagsInput.value = parts.join("، "); renderTagChips(); };
        chip.appendChild(btn);
        tagsChips.appendChild(chip);
    });
}
tagsInput.addEventListener("input", renderTagChips);

/* =================== توست وضعیت (موفقیت / خطا / در حال انجام) =================== */
const TOAST_ICONS = {
    success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
    error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/></svg>'
};
const TOAST_TITLES = { success: "انجام شد", error: "خطا", info: "در حال انجام" };

let toastHideTimer = null;

/*
 * نمایش توست وضعیت.
 *   showStatus(msg)               → اطلاع‌رسانی (info)
 *   showStatus(msg, true)         → موفقیت (success)
 *   showStatus(msg, false)        → خطا (error)
 *   showStatus(msg, "info")       → حالت در حال انجام با اسپینر
 * برای سازگاری با فراخوانی‌های قبلی، ایموجی‌های ابتدای پیام حذف می‌شوند.
 */
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

    // نوار پیشرفت فقط در حالتِ «در حال انجام» معنا دارد
    if (kind !== "info") {
        document.getElementById("uploadProgress").classList.remove("active");
    }

    // پیام موفقیت/خطا پس از چند ثانیه خودکار بسته می‌شود؛ حالت در حال انجام باز می‌ماند.
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
    setTimeout(() => {
        toast.classList.remove("show", "hide", "is-success", "is-error", "is-info");
        hideUploadProgress();
    }, 250);
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

/* ===== دیت‌پیکر شمسی مدرن (تقویم گرید) ===== */
const PERSIAN_MONTHS = [
    "فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور",
    "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"
];
// ماهی که اکنون در گرید نمایش داده می‌شود (ممکن است با روزِ انتخاب‌شده فرق کند)
let dpViewYear = 0, dpViewMonth = 1;

function dpGet(id){ return parseInt(document.getElementById(id).value || "0", 10); }
function dpSet(id, v){ document.getElementById(id).value = String(v); }

// روزِ هفته‌ی اولِ ماهِ شمسی (شنبه=0 ... جمعه=6)
function jalaliFirstDow(jy, jm) {
    const [gy, gm, gd] = jalaliToGregorian(jy, jm, 1);
    const js = new Date(gy, gm - 1, gd).getDay(); // یکشنبه=0 ... شنبه=6
    return (js + 1) % 7;                          // شنبه=0 ... جمعه=6
}

// state کامل پیکر را ست می‌کند (روزِ انتخاب‌شده + زمان) و گرید را روی همان ماه می‌برد
function setPickerValues(jy, jm, jd, hh, mm) {
    dpSet("jy", jy); dpSet("jm", jm); dpSet("jd", jd);
    dpSet("hh", hh); dpSet("mm", mm);
    dpViewYear = jy; dpViewMonth = jm;
    dpRenderTime();
    dpRenderCalendar();
}

// نمای فعالِ پیکر: 'days' | 'months' | 'years'
let dpView = 'days';

function dpToggleView(which) {
    dpView = (dpView === which) ? 'days' : which;
    dpApplyView();
}
function dpApplyView() {
    document.getElementById("dpDayView").style.display   = dpView === 'days'   ? '' : 'none';
    document.getElementById("dpMonthView").style.display = dpView === 'months' ? 'grid' : 'none';
    document.getElementById("dpYearView").style.display  = dpView === 'years'  ? 'grid' : 'none';
    if (dpView === 'months') dpRenderMonths();
    else if (dpView === 'years') dpRenderYears();
}

// رندرِ شبکه‌ی روزها برای ماهِ جاریِ نمایش
function dpRenderCalendar() {
    const mBtn = document.getElementById("dpMonthBtn");
    const yBtn = document.getElementById("dpYearBtn");
    if (mBtn) mBtn.textContent = PERSIAN_MONTHS[dpViewMonth - 1];
    if (yBtn) yBtn.textContent = toFaDigits(dpViewYear);

    const grid = document.getElementById("dpDays");
    if (!grid) return;
    grid.innerHTML = "";

    const lead = jalaliFirstDow(dpViewYear, dpViewMonth);
    const days = getJalaliMonthDays(dpViewYear, dpViewMonth);

    // روزِ امروز (شمسی) برای هایلایت
    const now = new Date();
    const [tjy, tjm, tjd] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());

    const selJy = dpGet("jy"), selJm = dpGet("jm"), selJd = dpGet("jd");

    // خانه‌های خالیِ ابتدای ماه
    for (let i = 0; i < lead; i++) {
        const sp = document.createElement("span");
        sp.className = "dp-day is-empty";
        grid.appendChild(sp);
    }
    for (let d = 1; d <= days; d++) {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "dp-day";
        btn.textContent = toFaDigits(d);
        if (dpViewYear === tjy && dpViewMonth === tjm && d === tjd) btn.classList.add("is-today");
        if (dpViewYear === selJy && dpViewMonth === selJm && d === selJd) btn.classList.add("is-selected");
        btn.addEventListener("click", () => dpSelectDay(d));
        grid.appendChild(btn);
    }
}

function dpSelectDay(d) {
    dpSet("jy", dpViewYear);
    dpSet("jm", dpViewMonth);
    dpSet("jd", d);
    dpRenderCalendar();
}

// شبکه‌ی ۱۲ ماه برای انتخابِ سریع
function dpRenderMonths() {
    const wrap = document.getElementById("dpMonthView");
    if (!wrap) return;
    wrap.innerHTML = "";
    const now = new Date();
    const [tjy, tjm] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
    for (let m = 1; m <= 12; m++) {
        const b = document.createElement("button");
        b.type = "button"; b.className = "dp-pick-item"; b.textContent = PERSIAN_MONTHS[m - 1];
        if (m === dpViewMonth) b.classList.add("is-selected");
        if (dpViewYear === tjy && m === tjm) b.classList.add("is-today");
        b.addEventListener("click", () => dpPickMonth(m));
        wrap.appendChild(b);
    }
}

// شبکه‌ی سال‌ها (۱۰ سال قبل تا ۱۰ سال بعدِ سالِ نمایش)
function dpRenderYears() {
    const wrap = document.getElementById("dpYearView");
    if (!wrap) return;
    wrap.innerHTML = "";
    const now = new Date();
    const [tjy] = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
    const start = dpViewYear - 10, end = dpViewYear + 10;
    for (let y = start; y <= end; y++) {
        const b = document.createElement("button");
        b.type = "button"; b.className = "dp-pick-item"; b.textContent = toFaDigits(y);
        if (y === dpViewYear) b.classList.add("is-selected");
        if (y === tjy) b.classList.add("is-today");
        b.addEventListener("click", () => dpPickYear(y));
        wrap.appendChild(b);
    }
}

function dpPickMonth(m) {
    dpViewMonth = m;
    dpView = 'days'; dpApplyView();
    dpRenderCalendar();
}
function dpPickYear(y) {
    dpViewYear = y;
    dpView = 'months'; dpApplyView();   // پس از سال، به انتخابِ ماه برو
    dpRenderCalendar();
}

function dpChangeMonth(dir) {
    // dir=+1 یعنی ماهِ بعد، dir=-1 یعنی ماهِ قبل
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
    let v = dpGet(unit) + dir;
    const max = unit === "hh" ? 24 : 60;
    v = (v + max) % max;            // چرخشی
    dpSet(unit, v);
    dpRenderTime();
}

function openDatePicker() {
    dpView = 'days'; dpApplyView();   // همیشه با نمای روز باز شود
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
    const jy = dpGet("jy"), jm = dpGet("jm"), jd = dpGet("jd");
    const hh = dpGet("hh"), mm = dpGet("mm");
    if (!jy || !jm || !jd) { closeDatePicker(); return; }
    const [gy, gm, gd] = jalaliToGregorian(jy, jm, jd);
    document.getElementById("publish_date").value = `${gy}-${pad2(gm)}-${pad2(gd)} ${pad2(hh)}:${pad2(mm)}:00`;
    renderPublishDisplay(jy, jm, jd, hh, mm);
    closeDatePicker();
}

function initDatePicker() {
    const now = new Date();
    const savedGregorian = `<?= $pub_val ? htmlspecialchars(str_replace('T', ' ', $pub_val) . ':00') : '' ?>`;
    if (savedGregorian) {
        const parsed = new Date(savedGregorian.replace(' ', 'T'));
        setDateFromGregorianDate(Number.isNaN(parsed.getTime()) ? now : parsed);
    } else {
        setDateFromGregorianDate(now);
    }
}

/* ===== ارتقای <select> به دراپ‌داون سفارشی مدرن ===== */
const MSEL_CARET = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
const MSEL_CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

function enhanceSelect(select){
    if (!select || select.dataset.enhanced) return;
    select.dataset.enhanced = "1";

    const placeholderText = (select.options[0] && select.options[0].value === "") ? select.options[0].textContent : "";

    const wrap = document.createElement("div");
    wrap.className = "msel";

    const trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "msel-trigger";
    trigger.innerHTML = '<span class="msel-value"></span><span class="msel-caret">' + MSEL_CARET + '</span>';

    const menu = document.createElement("div");
    menu.className = "msel-menu";
    menu.setAttribute("role", "listbox");

    // ساختِ گزینه‌ها از روی <option>ها
    Array.from(select.options).forEach(opt => {
        if (opt.value === "" && placeholderText) return; // placeholder را گزینه نکن
        const o = document.createElement("div");
        o.className = "msel-opt";
        o.setAttribute("role", "option");
        o.dataset.value = opt.value;
        o.innerHTML = '<span>' + opt.textContent + '</span><span class="msel-check">' + MSEL_CHECK + '</span>';
        o.addEventListener("click", () => {
            select.value = opt.value;
            select.dispatchEvent(new Event("change", { bubbles: true }));
            syncMsel();
            closeMenu();
        });
        menu.appendChild(o);
    });

    // مخفی‌کردنِ select بومی و درج کامپوننت
    select.style.display = "none";
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(trigger);
    wrap.appendChild(menu);
    wrap.appendChild(select); // select داخلِ wrap بماند تا fieldها سالم بمانند

    function syncMsel(){
        const valEl = trigger.querySelector(".msel-value");
        const sel = select.options[select.selectedIndex];
        const isPlaceholder = !select.value && placeholderText;
        valEl.textContent = isPlaceholder ? placeholderText : (sel ? sel.textContent : "");
        trigger.classList.toggle("is-placeholder", !!isPlaceholder);
        menu.querySelectorAll(".msel-opt").forEach(o => {
            o.classList.toggle("is-selected", o.dataset.value === select.value);
        });
        // انتقالِ حالتِ خطا از select به wrap
        wrap.classList.toggle("field-invalid", select.classList.contains("field-invalid"));
    }

    function openMenu(){ wrap.classList.add("open"); }
    function closeMenu(){ wrap.classList.remove("open"); }

    trigger.addEventListener("click", (e) => {
        e.stopPropagation();
        // بستنِ سایر دراپ‌داون‌های باز
        document.querySelectorAll(".msel.open").forEach(m => { if (m !== wrap) m.classList.remove("open"); });
        wrap.classList.toggle("open");
    });
    // وقتی خطای فیلد پاک/ست می‌شود، ظاهرِ دراپ‌داون را همگام کن
    select.addEventListener("change", syncMsel);
    select._mselSync = syncMsel;

    syncMsel();
}

// بستنِ دراپ‌داون‌ها با کلیک بیرون یا Esc
document.addEventListener("click", () => document.querySelectorAll(".msel.open").forEach(m => m.classList.remove("open")));
document.addEventListener("keydown", (e) => { if (e.key === "Escape") document.querySelectorAll(".msel.open").forEach(m => m.classList.remove("open")); });

document.addEventListener("DOMContentLoaded", () => {
    initDatePicker();
    updateTitleCounter();
    updateSubtitleCounter();
    renderTagChips();
    updateSeoWidget();
    enhanceSelect(document.getElementById("category_id"));
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

/* ====== کنترل نوار پیشرفت آپلود (داخل توست) ====== */
function setUploadProgress(percent, label) {
    document.getElementById("uploadProgress").classList.add("active");
    document.getElementById("uploadBarFill").style.width = percent + "%";
    // متنِ پیشرفت در همان توستِ «در حال انجام» نمایش داده می‌شود
    if (label) showStatus(label, "info");
}
function hideUploadProgress() {
    document.getElementById("uploadProgress").classList.remove("active");
    document.getElementById("uploadBarFill").style.width = "0%";
}

/* =================== اعتبارسنجی فیلدها (علامت‌گذاری بصری) =================== */
function setFieldError(boxId, errId, on){
    const box = document.getElementById(boxId);
    const err = document.getElementById(errId);
    if (box) box.classList.toggle("field-invalid", on);
    if (err) err.classList.toggle("show", on);
    // لیبلِ گروهِ والد را هم قرمز کن (برای فیلدهای داخل .input-group)
    const grp = box ? box.closest(".input-group") : null;
    if (grp) grp.classList.toggle("has-error", on);
    // اگر فیلد یک <select>‌ ارتقایافته باشد، حالتِ خطا را به دراپ‌داون سفارشی منتقل کن
    if (box && box._mselSync) box._mselSync();
}

/* با اولین تعاملِ کاربر، خطای همان فیلد پاک می‌شود */
document.getElementById("title").addEventListener("input", () => setFieldError("titleCard", "titleError", false));
document.getElementById("editor").addEventListener("input", () => setFieldError("editorShell", "contentError", false));
document.getElementById("category_id").addEventListener("change", () => setFieldError("category_id", "categoryError", false));

function validateNewsForm(){
    const title = document.getElementById("title").value.trim();
    const editorEl = document.getElementById("editor");
    const contentText = editorEl.innerText.trim();
    const categoryVal = document.getElementById("category_id").value;

    const titleBad = !title;
    const contentBad = contentText === "";
    const categoryBad = !categoryVal;

    setFieldError("titleCard", "titleError", titleBad);
    setFieldError("editorShell", "contentError", contentBad);
    setFieldError("category_id", "categoryError", categoryBad);

    // اسکرول و فوکوس به اولین فیلدِ خطادار
    let firstBad = null;
    if (titleBad) firstBad = document.getElementById("title");
    else if (contentBad) firstBad = editorEl;
    else if (categoryBad) firstBad = document.getElementById("category_id");

    if (firstBad) {
        firstBad.scrollIntoView({ behavior: "smooth", block: "center" });
        try { firstBad.focus({ preventScroll: true }); } catch (_) { firstBad.focus(); }
    }

    return !(titleBad || contentBad || categoryBad);
}

async function saveNews() {
    const title = document.getElementById("title").value.trim();
    const content = document.getElementById("editor").innerHTML.trim();

    if (!validateNewsForm()) {
        showStatus("لطفاً فیلدهای مشخص‌شده را پر کنید.", false);
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
        fd.append("subtitle", document.getElementById("subtitle").value.trim());
        fd.append("content", content);
        fd.append("author", document.getElementById("author").value);
        fd.append("category_id", document.getElementById("category_id").value);
        fd.append("keywords", document.getElementById("keywords").value);
        fd.append("publish_date", document.getElementById("publish_date").value);
        fd.append("tags", document.getElementById("tags").value);
        fd.append("remove_featured_flag", document.getElementById("remove_featured_flag").value);

        const checkedTags = document.querySelectorAll('input[name="tag_ids[]"]:checked');
        checkedTags.forEach(cb => {
            fd.append("tag_ids[]", cb.value);
        });

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

function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function openPreview(){
    const title = document.getElementById("title").value.trim();
    const author = document.getElementById("author").value.trim();
    const date = document.getElementById("publish_date_display").value.trim();
    const content = document.getElementById("editor").innerHTML;
    const catSelect = document.getElementById("category_id");
    const category = catSelect.value ? catSelect.options[catSelect.selectedIndex].text.trim() : "";
    const featured = featuredInput.files[0]
        ? URL.createObjectURL(featuredInput.files[0])
        : (dzImg.getAttribute("src") || "");

    const contentHasText = editor.innerText.trim().length > 0;
    const titleSafe = escapeHtml(title || "بدون عنوان");

    /* آیکون‌های متادیتا */
    const icAuthor = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
    const icDate = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';

    /* هیرو: اگر تصویر بود عنوان روی تصویر، وگرنه عنوان معمولی */
    let heroHtml;
    if (featured) {
        heroHtml = `
            <div class="pv-hero">
                ${category ? `<span class="pv-hero-cat">${escapeHtml(category)}</span>` : ""}
                <img src="${featured}" alt="تصویر شاخص">
                <h1 class="pv-hero-title">${titleSafe}</h1>
            </div>`;
    } else {
        heroHtml = `<h1 class="pv-title-fallback">${titleSafe}</h1>`;
    }

    const metaChips = [];
    if (author) metaChips.push(`<span class="chip">${icAuthor}${escapeHtml(author)}</span>`);
    if (date) metaChips.push(`<span class="chip">${icDate}${escapeHtml(date)}</span>`);
    if (category && !featured) metaChips.push(`<span class="chip">${escapeHtml(category)}</span>`);

    const html = `
        <div class="pv-article">
            ${heroHtml}
            ${metaChips.length ? `<div class="pv-meta">${metaChips.join("")}</div>` : ""}
            <div class="pv-body">
                ${contentHasText ? content : '<p class="pv-empty">هنوز متنی برای این خبر نوشته نشده است.</p>'}
            </div>
            <div id="seoScoreBox" class="pv-seo"></div>
        </div>
    `;

    document.getElementById("previewContent").innerHTML = html;
    calculateSEO();
    const modal = document.getElementById("previewModal");
    modal.style.display = "block";
    requestAnimationFrame(() => modal.classList.add("show"));
    document.querySelector(".preview-scroll").scrollTop = 0;
}

function closePreview(){
    const modal = document.getElementById("previewModal");
    modal.classList.remove("show");
    setTimeout(() => {
        modal.style.display = "none";
    }, 260);
}

/* محاسبه امتیاز سئو (مشترک بین ویجت و پیش‌نمایش) */
function computeSeoScore(){
    let score = 0;
    const title = document.getElementById("title").value.trim();
    const contentText = editor.innerText.trim();
    const wordCount = contentText ? contentText.split(/\s+/).length : 0;
    const hasImage = featuredInput.files[0] || dzPreview.classList.contains("show");
    const keywords = document.getElementById("keywords").value.trim();
    const hasH2 = editor.querySelector("h2");

    if(title.length > 5) score += 20;
    if(wordCount > 300) score += 25;
    if(hasImage) score += 15;
    if(keywords.length > 3) score += 20;
    if(hasH2) score += 20;
    return score;
}

/* به‌روزرسانی ویجت حلقه‌ای امتیاز سئو در ستون کناری */
function updateSeoWidget(){
    const score = computeSeoScore();
    const fill = document.getElementById("seoRingFill");
    const num = document.getElementById("seoRingNum");
    const stateEl = document.getElementById("seoState");
    const tipEl = document.getElementById("seoTip");

    let color = "#e74c3c", label = "ضعیف", tip = "عنوان و متن را کامل‌تر کنید.";
    if (score >= 70) { color = "#00b894"; label = "خوب"; tip = "محتوای شما برای سئو مناسب است."; }
    else if (score >= 40) { color = "#F79F1F"; label = "متوسط"; tip = "تصویر، کلمات کلیدی یا تیتر H2 اضافه کنید."; }

    fill.setAttribute("stroke-dasharray", score + ", 100");
    fill.setAttribute("stroke", color);
    num.textContent = toFaDigits(score);
    stateEl.textContent = label;
    stateEl.style.color = color;
    tipEl.textContent = tip;
}

/* به‌روزرسانی زنده ویجت با تغییر ورودی‌ها */
["title","keywords"].forEach(id => {
    document.getElementById(id).addEventListener("input", updateSeoWidget);
});
editor.addEventListener("input", updateSeoWidget);
featuredInput.addEventListener("change", updateSeoWidget);

function calculateSEO(){
    const score = computeSeoScore();

    let color = "#e74c3c", label = "ضعیف";
    if(score >= 70) { color = "#00b894"; label = "خوب"; }
    else if(score >= 40) { color = "#F79F1F"; label = "متوسط"; }

    /* وضعیت هر معیار برای چک‌لیست */
    const title = document.getElementById("title").value.trim();
    const wordCount = editor.innerText.trim() ? editor.innerText.trim().split(/\s+/).length : 0;
    const hasImage = featuredInput.files[0] || dzPreview.classList.contains("show");
    const keywords = document.getElementById("keywords").value.trim();
    const hasH2 = !!editor.querySelector("h2");

    const checks = [
        { ok: title.length > 5,   text: "عنوان مناسب" },
        { ok: wordCount > 300,    text: "طول متن کافی" },
        { ok: !!hasImage,         text: "تصویر شاخص" },
        { ok: keywords.length > 3,text: "کلمات کلیدی" },
        { ok: hasH2,              text: "تیتر H2 در متن" },
    ];

    const icOk = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    const icNo = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="9" y1="12" x2="15" y2="12"/></svg>';

    const checksHtml = checks.map(c =>
        `<div class="pv-check ${c.ok ? "ok" : "no"}">${c.ok ? icOk : icNo}<span>${c.text}</span></div>`
    ).join("");

    document.getElementById("seoScoreBox").innerHTML = `
        <div class="pv-seo-head">
            <h3 class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                امتیاز سئو
            </h3>
            <span class="pv-seo-badge" style="background:${color}">${label}</span>
        </div>
        <div class="pv-seo-bar-track">
            <div class="pv-seo-bar-fill" style="width:${score}%;background:${color}"></div>
        </div>
        <div class="pv-seo-foot">
            <span>امتیاز کلی</span>
            <span><b>${toFaDigits(score)}</b> از ${toFaDigits(100)}</span>
        </div>
        <div class="pv-checks">${checksHtml}</div>
    `;
}

document.getElementById("editor").addEventListener("keydown", function(e) {
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        document.execCommand("insertLineBreak");
    }
});
</script>

</body>
</html>

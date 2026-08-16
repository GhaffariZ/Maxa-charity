<?php
/**
 * ============================================================================
 * Enterprise Newsroom - Editorial & Media Studio CMS
 * ============================================================================
 * High-performance editorial suite for professional journalists and editors.
 * Features multi-tiered headlines, Cropper.js studio, Persian typography normalizer,
 * modular journalistic blocks, real-time SEO radar, multi-device live preview,
 * and silent auto-save crash recovery.
 */

require_once __DIR__ . '/_guard.php';
dash_require('news');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$news_data = null;
$ACTIVE_BRANCH = dash_active_branch_id();

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ? AND branch_id = ? LIMIT 1");
    $stmt->execute([$id, $ACTIVE_BRANCH]);
    $news_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$news_data) {
        die("<div style='font-family:Tahoma,sans-serif;padding:40px;text-align:center;direction:rtl;'>" .
            "<h2>خبر یافت نشد!</h2><p>خبر مورد نظر وجود ندارد یا شما به آن دسترسی ندارید.</p>" .
            "<a href='news-list.php' style='color:#007D75;font-weight:bold;text-decoration:none;'>بازگشت به فهرست اخبار</a></div>");
    }
}

// Fetch active categories
$categories = [];
try {
    $cat_stmt = $pdo->query("SELECT id, name FROM news_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}

// Prepare Existing Featured Image URL
$existing_image_url = '';
if ($id > 0 && !empty($news_data['featured_image'])) {
    $existing_image_url = "/uploads/news/{$news_data['news_code']}/{$news_data['featured_image']}";
}

$selectedCategoryId = (int)($news_data['category_id'] ?? 0);

// Prepare Publish Date for datetime-local
$pub_val = '';
if ($news_data && !empty($news_data['publish_date'])) {
    $pub_val = date('Y-m-d\TH:i', strtotime($news_data['publish_date']));
} else {
    $pub_val = date('Y-m-d\TH:i');
}

// Extract Subtitle / Kicker / Lead / Tags if present
$current_title    = $news_data['title'] ?? '';
$current_subtitle = $news_data['subtitle'] ?? '';
$current_content  = $news_data['content'] ?? '';
$current_author   = $news_data['author'] ?? ($DASH_USER['name'] ?? 'تحریریه مکسا');
$current_keywords = $news_data['keywords'] ?? '';
$current_status   = $news_data['status'] ?? 'draft';
$current_tags     = $news_data['tags'] ?? '';
$news_code        = $news_data['news_code'] ?? '';

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $id > 0 ? 'ویرایش خبر: ' . htmlspecialchars($current_title) : 'استودیو نگارش و تحریریه خبر' ?> | مکسا</title>

<!-- Dark / Light Theme Synced with Dashboard -->
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

<!-- TinyMCE 7 Enterprise Rich Text Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.6.0/tinymce.min.js" referrerpolicy="origin"></script>

<!-- Cropper.js for Featured Image Studio -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<style>
:root {
    --primary-color: #007D75;
    --primary-dark: #006159;
    --primary-light: #00a89d;
    --secondary-color: #F79F1F;
    --bg-color: #f4f7f6;
    --text-color: #2f3437;
    --muted-color: #7a878b;
    --panel-bg: #ffffff;
    --surface-2: #f8faf9;
    --border-color: #e2e8e7;
    --input-bg: #ffffff;
    --header-text: #007D75;
    --modal-overlay: rgba(14, 30, 30, 0.65);
    --shadow-sm: 0 2px 8px rgba(0, 40, 40, 0.05);
    --shadow-md: 0 8px 24px rgba(0, 40, 40, 0.08);
    --shadow-lg: 0 16px 40px rgba(0, 40, 40, 0.14);
    --radius: 16px;
    --radius-sm: 10px;
    --radius-lg: 22px;
    --danger: #e0556b;
    --success: #16a37a;
    --warning: #f79f1f;
    --ease: cubic-bezier(0.2, 0.8, 0.2, 1);
}

[data-theme="dark"] {
    --primary-color: #00a89d;
    --primary-dark: #00897e;
    --primary-light: #4fb2b0;
    --secondary-color: #ffb142;
    --bg-color: #121212;
    --text-color: #e7ecee;
    --muted-color: #8e9a9e;
    --panel-bg: #1e1e1e;
    --surface-2: #262626;
    --border-color: #383838;
    --input-bg: #282828;
    --header-text: #00a89d;
    --modal-overlay: rgba(0, 0, 0, 0.85);
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.35);
    --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.45);
    --shadow-lg: 0 16px 40px rgba(0, 0, 0, 0.65);
}

* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    background-color: var(--bg-color);
    color: var(--text-color);
    font-family: 'Vazirmatn', Tahoma, sans-serif;
    transition: background-color 0.25s var(--ease), color 0.25s var(--ease);
    min-height: 100vh;
    padding: 0;
    line-height: 1.7;
}

/* ===== Sticky Newsroom Glassmorphic Header ===== */
.studio-header {
    position: sticky;
    top: 0;
    z-index: 50;
    background: color-mix(in srgb, var(--panel-bg) 90%, transparent);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--border-color);
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: var(--shadow-sm);
}

.sh-info {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}

.sh-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: #fff;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    box-shadow: 0 8px 18px -6px rgba(0, 125, 117, 0.6);
}

.sh-title h1 {
    font-size: 17px;
    font-weight: 800;
    color: var(--header-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sh-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
    background: rgba(0, 125, 117, 0.12);
    color: var(--primary-color);
}

.autosave-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--muted-color);
    margin-top: 2px;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--success);
    box-shadow: 0 0 0 0 rgba(22, 163, 122, 0.7);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 122, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(22, 163, 122, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 122, 0); }
}

.sh-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 42px;
    padding: 0 18px;
    border-radius: var(--radius-sm);
    font-family: inherit;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: all 0.2s var(--ease);
    text-decoration: none;
    white-space: nowrap;
    user-select: none;
}

.btn svg { width: 17px; height: 17px; }

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: #fff;
    box-shadow: 0 8px 18px -4px rgba(0, 125, 117, 0.5);
}
.btn-primary:hover {
    transform: translateY(-1.5px);
    box-shadow: 0 12px 24px -4px rgba(0, 125, 117, 0.7);
}

.btn-secondary {
    background: var(--surface-2);
    color: var(--text-color);
    border: 1px solid var(--border-color);
}
.btn-secondary:hover {
    background: var(--panel-bg);
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.btn-accent {
    background: linear-gradient(135deg, var(--secondary-color), #d8850b);
    color: #fff;
    box-shadow: 0 8px 18px -4px rgba(247, 159, 31, 0.45);
}
.btn-accent:hover {
    transform: translateY(-1.5px);
    box-shadow: 0 12px 24px -4px rgba(247, 159, 31, 0.6);
}

.btn-danger {
    background: rgba(224, 85, 107, 0.1);
    color: var(--danger);
    border: 1px solid rgba(224, 85, 107, 0.25);
}
.btn-danger:hover {
    background: var(--danger);
    color: #fff;
}

.btn-sm { height: 34px; padding: 0 12px; font-size: 12px; border-radius: 8px; }

/* ===== Main Container & Layout ===== */
.studio-container {
    max-width: 1440px;
    margin: 0 auto;
    padding: 24px 20px 60px;
}

.editorial-grid {
    display: grid;
    grid-template-columns: minmax(0, 2.3fr) minmax(320px, 1fr);
    gap: 24px;
    align-items: start;
}

@media (max-width: 1040px) {
    .editorial-grid {
        grid-template-columns: 1fr;
    }
}

.card {
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 22px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 24px;
    position: relative;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.card:hover {
    box-shadow: var(--shadow-md);
}

.card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
}

.card-head h2 {
    font-size: 15px;
    font-weight: 800;
    color: var(--header-text);
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-head h2 svg {
    width: 19px;
    height: 19px;
    color: var(--primary-color);
}

/* ===== Module A: Multi-Tiered Headline Suite ===== */
.headline-suite {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.field-label {
    font-size: 13px;
    font-weight: 700;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.field-label .rec-badge {
    font-size: 11px;
    font-weight: 600;
    color: var(--muted-color);
}

.input-kicker {
    width: 100%;
    height: 42px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 0 14px;
    font-family: inherit;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-color);
    transition: all 0.2s var(--ease);
}
.input-kicker:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.12);
    background: var(--panel-bg);
}

.input-hero-title {
    width: 100%;
    min-height: 56px;
    border: 1.5px solid var(--border-color);
    background: var(--panel-bg);
    border-radius: var(--radius-sm);
    padding: 12px 16px;
    font-family: inherit;
    font-size: 22px;
    font-weight: 800;
    color: var(--text-color);
    line-height: 1.5;
    resize: none;
    transition: all 0.2s var(--ease);
}
.input-hero-title:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(0, 125, 117, 0.14);
}

.title-counter-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11.5px;
    color: var(--muted-color);
    margin-top: 2px;
}

.counter-progress {
    height: 4px;
    background: var(--border-color);
    border-radius: 99px;
    overflow: hidden;
    flex: 1;
    margin-right: 12px;
}
.counter-fill {
    height: 100%;
    width: 0%;
    background: var(--primary-color);
    transition: width 0.2s, background-color 0.2s;
}
.counter-fill.good { background: var(--success); }
.counter-fill.warning { background: var(--warning); }
.counter-fill.danger { background: var(--danger); }

.input-subtitle {
    width: 100%;
    height: 44px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 0 14px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-color);
    transition: all 0.2s var(--ease);
}
.input-subtitle:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.12);
    background: var(--panel-bg);
}

.lead-box {
    background: rgba(0, 125, 117, 0.04);
    border: 1.5px dashed color-mix(in srgb, var(--primary-color) 40%, var(--border-color));
    border-radius: var(--radius-sm);
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.lead-box textarea {
    width: 100%;
    min-height: 70px;
    border: none;
    background: transparent;
    font-family: inherit;
    font-size: 14.5px;
    font-weight: 500;
    color: var(--text-color);
    line-height: 1.7;
    resize: vertical;
}
.lead-box textarea:focus { outline: none; }

/* ===== Module B: Professional Featured Image Studio ===== */
.image-studio {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.dropzone-box {
    border: 2px dashed var(--border-color);
    background: var(--surface-2);
    border-radius: var(--radius);
    padding: 28px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.22s var(--ease);
    position: relative;
    overflow: hidden;
}
.dropzone-box:hover, .dropzone-box.drag-over {
    border-color: var(--primary-color);
    background: rgba(0, 125, 117, 0.05);
    transform: scale(1.005);
}

.dz-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.dz-icon {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: rgba(0, 125, 117, 0.1);
    color: var(--primary-color);
    display: grid;
    place-items: center;
}
.dz-icon svg { width: 28px; height: 28px; }

.dz-title { font-size: 15px; font-weight: 700; color: var(--text-color); }
.dz-sub { font-size: 12px; color: var(--muted-color); }

.preview-container {
    display: none;
    position: relative;
    border-radius: var(--radius-sm);
    overflow: hidden;
    background: #000;
    border: 1px solid var(--border-color);
}
.preview-container.active { display: block; }
.preview-image {
    width: 100%;
    max-height: 420px;
    object-fit: cover;
    display: block;
}

.focal-crosshair {
    position: absolute;
    width: 28px;
    height: 28px;
    border: 2px solid #fff;
    border-radius: 50%;
    box-shadow: 0 0 0 2px rgba(0,0,0,0.6), inset 0 0 0 2px rgba(0,0,0,0.6);
    transform: translate(-50%, -50%);
    pointer-events: none;
    display: none;
}
.focal-crosshair::before, .focal-crosshair::after {
    content: '';
    position: absolute;
    background: #fff;
}
.focal-crosshair::before { top: 50%; left: -6px; right: -6px; height: 2px; transform: translateY(-50%); }
.focal-crosshair::after { left: 50%; top: -6px; bottom: -6px; width: 2px; transform: translateX(-50%); }

.preview-toolbar {
    position: absolute;
    bottom: 12px;
    left: 12px;
    right: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(0,0,0,0.72);
    backdrop-filter: blur(8px);
    border-radius: 12px;
    padding: 8px 12px;
    color: #fff;
    gap: 8px;
    flex-wrap: wrap;
}

.img-meta-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 14px;
}
@media (max-width: 768px) {
    .img-meta-grid { grid-template-columns: 1fr; }
}

.input-text {
    width: 100%;
    height: 40px;
    background: var(--input-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 0 12px;
    font-family: inherit;
    font-size: 13px;
    color: var(--text-color);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.input-text:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.12);
}

/* ===== Module C: Modular Journalistic Inserter Bar ===== */
.blocks-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 12px;
}
.blocks-bar::-webkit-scrollbar { height: 5px; }
.blocks-bar::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }

.block-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text-color);
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.18s var(--ease);
    user-select: none;
}
.block-chip:hover {
    background: var(--primary-color);
    color: #fff;
    border-color: var(--primary-color);
    transform: translateY(-1.5px);
    box-shadow: 0 4px 12px rgba(0, 125, 117, 0.2);
}
.block-chip svg { width: 15px; height: 15px; }

/* ===== Metrics & Word Bar ===== */
.metrics-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-top: none;
    border-radius: 0 0 var(--radius-sm) var(--radius-sm);
    padding: 10px 16px;
    font-size: 12.5px;
    color: var(--muted-color);
    flex-wrap: wrap;
    gap: 12px;
}

.metrics-group {
    display: flex;
    align-items: center;
    gap: 16px;
}
.metric-item { display: flex; align-items: center; gap: 5px; }
.metric-val { font-weight: 800; color: var(--text-color); }

/* ===== Module F: Real-Time SEO Radar Widget ===== */
.seo-radar {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.seo-score-circle {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 14px;
    background: var(--surface-2);
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-color);
}

.score-gauge {
    position: relative;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: conic-gradient(var(--success) 0deg, var(--border-color) 0deg);
    flex-shrink: 0;
}
.score-inner {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--panel-bg);
    display: grid;
    place-items: center;
    font-size: 17px;
    font-weight: 900;
    color: var(--text-color);
}

.score-meta h3 { font-size: 14px; font-weight: 800; color: var(--text-color); margin-bottom: 2px; }
.score-meta p { font-size: 11.5px; color: var(--muted-color); }

.seo-checklist {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.seo-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: var(--text-color);
}
.seo-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--danger);
    flex-shrink: 0;
}
.seo-item.pass .seo-dot { background: var(--success); }
.seo-item.pass { color: var(--text-color); }

/* ===== Google SERP Simulator ===== */
.serp-box {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 14px;
    font-family: Arial, sans-serif;
    direction: rtl;
}
.serp-url { font-size: 12px; color: #202124; display: flex; align-items: center; gap: 6px; }
[data-theme="dark"] .serp-url { color: #bdc1c6; }
.serp-title { font-size: 17px; color: #1a0dab; font-weight: 600; margin: 4px 0; line-height: 1.4; word-break: break-word; }
[data-theme="dark"] .serp-title { color: #8ab4f8; }
.serp-desc { font-size: 13px; color: #4d5156; line-height: 1.5; word-break: break-word; }
[data-theme="dark"] .serp-desc { color: #bdc1c6; }

/* ===== Interactive Tags Input ===== */
.tags-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 8px 10px;
    background: var(--input-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    min-height: 44px;
    align-items: center;
}
.tag-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    background: rgba(0, 125, 117, 0.09);
    color: var(--primary-color);
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}
.tag-chip-remove {
    cursor: pointer;
    width: 14px;
    height: 14px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: rgba(0, 125, 117, 0.15);
}
.tag-chip-remove:hover { background: var(--danger); color: #fff; }
.tags-input {
    border: none;
    background: transparent;
    outline: none;
    font-family: inherit;
    font-size: 13px;
    color: var(--text-color);
    flex: 1;
    min-width: 90px;
}

/* ===== Select / Dropdowns ===== */
.select-input {
    width: 100%;
    height: 42px;
    background: var(--input-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 0 12px;
    font-family: inherit;
    font-size: 13.5px;
    color: var(--text-color);
    cursor: pointer;
}
.select-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.12);
}

/* ===== Zen Mode Fullscreen Styles ===== */
body.zen-mode .studio-header {
    background: var(--panel-bg);
    border-bottom: 1px solid var(--border-color);
}
body.zen-mode .editorial-grid {
    grid-template-columns: 1fr;
    max-width: 900px;
    margin: 0 auto;
}
body.zen-mode .sidebar-column {
    display: none;
}
body.zen-mode .card {
    border: none;
    box-shadow: none;
    background: transparent;
    padding: 0;
}

/* ===== Modals (Cropper & Responsive Preview) ===== */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: var(--modal-overlay);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s, visibility 0.25s;
}
.modal-backdrop.open {
    opacity: 1;
    visibility: visible;
}

.modal-dialog {
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    width: 100%;
    max-width: 960px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: translateY(16px) scale(0.97);
    transition: transform 0.25s var(--ease);
}
.modal-backdrop.open .modal-dialog {
    transform: translateY(0) scale(1);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 22px;
    border-bottom: 1px solid var(--border-color);
}
.modal-header h3 { font-size: 16px; font-weight: 800; color: var(--header-text); }
.modal-close {
    background: transparent;
    border: none;
    color: var(--muted-color);
    cursor: pointer;
    font-size: 20px;
    display: grid;
    place-items: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    transition: background 0.15s, color 0.15s;
}
.modal-close:hover { background: rgba(224, 85, 107, 0.1); color: var(--danger); }

.modal-body {
    padding: 20px 22px;
    overflow-y: auto;
    flex: 1;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    padding: 14px 22px;
    border-top: 1px solid var(--border-color);
    background: var(--surface-2);
}

/* Cropper Modal Controls */
.cropper-stage {
    max-height: 480px;
    background: #111;
    border-radius: var(--radius-sm);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cropper-stage img { max-width: 100%; }

.cropper-tools {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 16px;
    flex-wrap: wrap;
}
.ratio-tabs {
    display: flex;
    gap: 6px;
}
.ratio-btn {
    padding: 6px 12px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-color);
    cursor: pointer;
    transition: all 0.15s;
}
.ratio-btn.active, .ratio-btn:hover {
    background: var(--primary-color);
    color: #fff;
    border-color: var(--primary-color);
}

/* Responsive Preview Frame */
.preview-device-bar {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 16px;
}
.device-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text-color);
    cursor: pointer;
}
.device-btn.active {
    background: var(--primary-color);
    color: #fff;
    border-color: var(--primary-color);
}
.preview-viewport-wrapper {
    background: var(--surface-2);
    border-radius: var(--radius-sm);
    padding: 20px;
    display: flex;
    justify-content: center;
    min-height: 480px;
    overflow-x: auto;
}
.preview-viewport {
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    box-shadow: var(--shadow-md);
    transition: width 0.3s var(--ease);
    width: 100%;
    padding: 24px;
    font-family: 'Vazirmatn', sans-serif;
    color: var(--text-color);
    line-height: 1.8;
}
.preview-viewport.desktop { width: 100%; max-width: 920px; }
.preview-viewport.tablet { width: 768px; }
.preview-viewport.mobile { width: 375px; }

/* Toast Notifications */
.studio-toast {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: var(--text-color);
    color: var(--panel-bg);
    padding: 12px 22px;
    border-radius: 999px;
    font-size: 13.5px;
    font-weight: 700;
    box-shadow: var(--shadow-lg);
    z-index: 120;
    opacity: 0;
    visibility: hidden;
    transition: all 0.25s var(--ease);
    display: flex;
    align-items: center;
    gap: 10px;
}
.studio-toast.show {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}
.studio-toast.success { background: #107a5b; color: #fff; }
.studio-toast.error { background: #c33850; color: #fff; }
</style>
</head>
<body>

<!-- ===== Sticky Newsroom Glassmorphic Header ===== -->
<header class="studio-header">
    <div class="sh-info">
        <a href="news-list.php" class="btn btn-secondary btn-sm" title="بازگشت به فهرست اخبار">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            فهرست
        </a>
        <div class="sh-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
        </div>
        <div class="sh-title">
            <h1>
                <?= $id > 0 ? 'ویرایش خبر' : 'استودیو نگارش خبر' ?>
                <?php if ($id > 0): ?>
                    <span class="sh-badge"><?= htmlspecialchars($news_code) ?></span>
                <?php endif; ?>
            </h1>
            <div class="autosave-pill">
                <span class="pulse-dot"></span>
                <span id="autoSaveStatus">آماده برای نگارش</span>
            </div>
        </div>
    </div>

    <div class="sh-actions">
        <!-- Persian Normalizer Button -->
        <button type="button" class="btn btn-secondary" id="btnNormalizeText" title="پاکسازی خودکار نیم‌فاصله‌ها، ارقام فارسی و نشانه‌گذاری">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            ویراستار فارسی
        </button>

        <!-- Zen Mode Toggle -->
        <button type="button" class="btn btn-secondary" id="btnToggleZen" title="حالت تمرکز و نگارش بدون حواس‌پرتی (Alt+Z)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
            تمرکز
        </button>

        <!-- Multi-Device Responsive Live Preview -->
        <button type="button" class="btn btn-secondary" id="btnOpenPreview" title="پیش‌نمایش زنده در اندازه‌های مختلف">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            پیش‌نمایش
        </button>

        <!-- Save Draft Button -->
        <button type="button" class="btn btn-secondary" id="btnSaveDraft">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            ذخیره پیش‌نویس
        </button>

        <!-- Final Submit / Publish Button -->
        <button type="button" class="btn btn-primary" id="btnPublishFinal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <?= $id > 0 ? 'به‌روزرسانی خبر' : 'ثبت و انتشار' ?>
        </button>
    </div>
</header>

<!-- ===== Main Newsroom Editorial Workspace ===== -->
<div class="studio-container">
    <form id="newsForm" method="POST" action="news-save.php" enctype="multipart/form-data">
        <input type="hidden" name="id" id="newsId" value="<?= $id ?>">
        <input type="hidden" name="status" id="newsStatus" value="<?= htmlspecialchars($current_status) ?>">
        <input type="hidden" name="featured_image_base64" id="featuredImageBase64">
        <input type="hidden" name="remove_featured_flag" id="removeFeaturedFlag" value="0">
        <input type="hidden" name="focal_x" id="focalX" value="50">
        <input type="hidden" name="focal_y" id="focalY" value="50">

        <div class="editorial-grid">
            <!-- Main Content Column -->
            <div class="main-column">

                <!-- Module A: Multi-Tiered Headline Card -->
                <div class="card">
                    <div class="card-head">
                        <h2>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h10M4 18h14"/></svg>
                            تیتر و معماری پیام
                        </h2>
                        <span style="font-size:12px;color:var(--muted-color);">ساختار استاندارد خبر</span>
                    </div>

                    <div class="headline-suite">
                        <!-- Kicker / Overline (روتیتر) -->
                        <div class="field-group">
                            <label class="field-label" for="kicker">
                                <span>روتیتر (موضوع، جغرافیا یا دسته‌بندی تکمیلی)</span>
                                <span class="rec-badge">اختیاری</span>
                            </label>
                            <input type="text" class="input-kicker" id="kicker" name="kicker" placeholder="مثال: گزارش اختصاصی / اصفهان / دستاورد پزشکی درمان تسکینی" autocomplete="off">
                        </div>

                        <!-- Main Headline (تیتر اصلی) -->
                        <div class="field-group">
                            <label class="field-label" for="title">
                                <span>تیتر اصلی خبر (Headline) <strong style="color:var(--danger)">*</strong></span>
                                <span class="rec-badge" id="titleLengthHint">طول ایده‌آل سئو: ۵۰ تا ۷۰ کاراکتر</span>
                            </label>
                            <textarea class="input-hero-title" id="title" name="title" rows="2" placeholder="تیتر جذاب، رسا و گویای خبر را اینجا بنویسید..." required><?= htmlspecialchars($current_title) ?></textarea>
                            <div class="title-counter-bar">
                                <div class="counter-progress">
                                    <div class="counter-fill" id="titleProgress"></div>
                                </div>
                                <span id="titleCounter">۰ کاراکتر</span>
                            </div>
                        </div>

                        <!-- Subtitle (زیرتیتر) -->
                        <div class="field-group">
                            <label class="field-label" for="subtitle">
                                <span>زیرتیتر (توضیح تکمیلی زاویه دید خبر)</span>
                                <span class="rec-badge">اختیاری</span>
                            </label>
                            <input type="text" class="input-subtitle" id="subtitle" name="subtitle" value="<?= htmlspecialchars($current_subtitle) ?>" placeholder="جزئیات تکمیلی، نقل‌قول کوتاه یا پیام فرعی خبر" autocomplete="off">
                        </div>

                        <!-- Lead Paragraph (لید خبر) -->
                        <div class="field-group">
                            <label class="field-label" for="lead">
                                <span>لید و مقدمه خبر (Lead Paragraph)</span>
                                <span class="rec-badge">پاسخ به ۵W+1H در ۱ تا ۲ جمله</span>
                            </label>
                            <div class="lead-box">
                                <textarea id="lead" name="lead" placeholder="مهم‌ترین پیام، خلاصه و لید خبر را اینجا وارد کنید. این متن در پیش‌نمایش کارت‌ها و کارت شبکه‌های اجتماعی نمایش داده می‌شود..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Module B: Professional Featured Image Studio -->
                <div class="card">
                    <div class="card-head">
                        <h2>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            استودیوی تصویر شاخص (Photo Studio)
                        </h2>
                        <span style="font-size:12px;color:var(--muted-color);">بهینه‌سازی، برش هوشمند و سئو</span>
                    </div>

                    <div class="image-studio">
                        <!-- Dropzone Upload Area -->
                        <div class="dropzone-box" id="imageDropzone" tabindex="0">
                            <input type="file" id="featuredFileInput" name="featured_image" accept="image/jpeg,image/png,image/webp" style="display:none;">
                            <div class="dz-content">
                                <div class="dz-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                </div>
                                <div class="dz-title">تصویر شاخص را اینجا بکشید و رها کنید یا کلیک کنید</div>
                                <div class="dz-sub">پشتیبانی از فرمت‌های WebP، JPG و PNG | قابلیت چسباندن تصویر از کلیپ‌بورد (Ctrl+V)</div>
                            </div>
                        </div>

                        <!-- Live Preview & Focal Point Stage -->
                        <div class="preview-container <?= !empty($existing_image_url) ? 'active' : '' ?>" id="previewContainer">
                            <img src="<?= htmlspecialchars($existing_image_url) ?>" class="preview-image" id="featuredPreviewImg" alt="پیش‌نمایش تصویر شاخص">
                            <div class="focal-crosshair" id="focalCrosshair"></div>

                            <div class="preview-toolbar">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <button type="button" class="btn btn-secondary btn-sm" id="btnOpenCropper">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.13 1L6 16a2 2 0 0 0 2 2h15"/><path d="M1 6.13L16 6a2 2 0 0 1 2 2v15"/></svg>
                                        برش و تنظیم کادر
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" id="btnSetFocalPoint" title="تعیین نقطه تمرکز تصویر برای نمایش متناسب در موبایل">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="2" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22" y2="12"/></svg>
                                        نقطه کانونی
                                    </button>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm" id="btnRemoveImage">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    حذف تصویر
                                </button>
                            </div>
                        </div>

                        <!-- Image SEO & Metadata Fields -->
                        <div class="img-meta-grid">
                            <div class="field-group">
                                <label class="field-label" for="imgAlt">متن جایگزین (Alt Text سئو)</label>
                                <input type="text" class="input-text" id="imgAlt" name="img_alt" placeholder="توضیح موضوع تصویر برای موتورهای جستجو">
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="imgCaption">زیرنویس تصویر (Caption)</label>
                                <input type="text" class="input-text" id="imgCaption" name="img_caption" placeholder="توضیحات کوتاه زیر تصویر در صفحه خبر">
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="imgCredit">عکاس / منبع تصویر</label>
                                <input type="text" class="input-text" id="imgCredit" name="img_credit" placeholder="مثال: روابط عمومی مکسا / ایرنا">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Module C: Modular Journalistic Inserter & Rich Text Editor -->
                <div class="card">
                    <div class="card-head">
                        <h2>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            بدنه اصلی خبر و المان‌های ژورنالیستی
                        </h2>
                        <span style="font-size:12px;color:var(--muted-color);">بلوک‌های چندرسانه‌ای پیشرفته</span>
                    </div>

                    <!-- Quick Inserter Chips Bar -->
                    <div class="blocks-bar">
                        <div class="block-chip" id="insPullQuote" title="درج سوتیتر و نقل‌قول ویژه">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1zM15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/></svg>
                            سوتیتر و نقل‌قول
                        </div>
                        <div class="block-chip" id="insCallout" title="درج کادرهای ویژه خبری">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            کادر ویژه / هشدار
                        </div>
                        <div class="block-chip" id="insFigure" title="تصویر درون‌متنی چندحالته با کپشن">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            تصویر درون‌متنی
                        </div>
                        <div class="block-chip" id="insBeforeAfter" title="اسلایدر مقایسه قبل و بعد">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="22"/><polyline points="7 8 3 12 7 16"/><polyline points="17 8 21 12 17 16"/></svg>
                            مقایسه قبل و بعد
                        </div>
                        <div class="block-chip" id="insVideo" title="ویدیو و پخش‌کننده آپارات / یوتیوب">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                            ویدیو / آپارات
                        </div>
                        <div class="block-chip" id="insAudio" title="پادکست و فایل صوتی">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                            پادکست صوتی
                        </div>
                        <div class="block-chip" id="insDownload" title="کارت دانلود فایل و اسناد">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            پیوست دانلود
                        </div>
                        <div class="block-chip" id="insTimeline" title="خط زمانی وقایع و رویدادها">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            خط زمانی
                        </div>
                    </div>

                    <!-- TinyMCE 7 Host Textarea -->
                    <textarea id="editor" name="content"><?= htmlspecialchars($current_content) ?></textarea>

                    <!-- Real-Time Metrics Bar -->
                    <div class="metrics-bar">
                        <div class="metrics-group">
                            <div class="metric-item">تعداد کلمات: <span class="metric-val" id="metricWords">۰</span></div>
                            <div class="metric-item">کاراکتر: <span class="metric-val" id="metricChars">۰</span></div>
                            <div class="metric-item">پاراگراف: <span class="metric-val" id="metricParas">۰</span></div>
                        </div>
                        <div class="metrics-group">
                            <div class="metric-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--primary-color)"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                زمان تقریبی مطالعه: <span class="metric-val" id="metricReadTime">۱ دقیقه</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar Taxonomy & SEO Radar Column -->
            <div class="sidebar-column">

                <!-- Publishing & Editorial Workflow Card -->
                <div class="card">
                    <div class="card-head">
                        <h2>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            تنظیمات انتشار و تاکسونومی
                        </h2>
                    </div>

                    <div class="headline-suite">
                        <!-- Editorial Status -->
                        <div class="field-group">
                            <label class="field-label" for="statusSelect">وضعیت انتشار</label>
                            <select class="select-input" id="statusSelect">
                                <option value="draft" <?= $current_status === 'draft' ? 'selected' : '' ?>>پیش‌نویس (ذخیره داخلی)</option>
                                <option value="review" <?= $current_status === 'review' ? 'selected' : '' ?>>ارسال به سردبیر جهت بررسی</option>
                                <option value="published" <?= $current_status === 'published' ? 'selected' : '' ?>>منتشر شده در سایت</option>
                            </select>
                        </div>

                        <!-- Category Selector -->
                        <div class="field-group">
                            <label class="field-label" for="categorySelect">دسته‌بندی موضوعی <strong style="color:var(--danger)">*</strong></label>
                            <select class="select-input" id="categorySelect" name="category_id" required>
                                <option value="">-- انتخاب دسته‌بندی --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $selectedCategoryId === (int)$cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Author / Reporter Byline -->
                        <div class="field-group">
                            <label class="field-label" for="author">نویسنده / خبرنگار</label>
                            <input type="text" class="input-text" id="author" name="author" value="<?= htmlspecialchars($current_author) ?>" placeholder="نام خبرنگار یا تحریریه">
                        </div>

                        <!-- Publish Schedule Date -->
                        <div class="field-group">
                            <label class="field-label" for="publishDate">تاریخ و زمان انتشار</label>
                            <input type="datetime-local" class="input-text" id="publishDate" name="publish_date" value="<?= $pub_val ?>">
                        </div>
                    </div>
                </div>

                <!-- Interactive Tags Card -->
                <div class="card">
                    <div class="card-head">
                        <h2>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            برچسب‌ها و کلیدواژه‌ها
                        </h2>
                    </div>

                    <div class="field-group">
                        <label class="field-label">کلیدواژه کانونی سئو</label>
                        <input type="text" class="input-text" id="keywords" name="keywords" value="<?= htmlspecialchars($current_keywords) ?>" placeholder="کلمه کلیدی اصلی مقاله را وارد کنید">
                    </div>

                    <div class="field-group" style="margin-top:14px;">
                        <label class="field-label">تگ‌های موضوعی</label>
                        <div class="tags-wrapper" id="tagsWrapper">
                            <input type="text" class="tags-input" id="tagsInput" placeholder="تگ را بنویسید و Enter بزنید...">
                        </div>
                        <input type="hidden" name="tags" id="hiddenTags" value="<?= htmlspecialchars($current_tags) ?>">
                    </div>
                </div>

                <!-- Module F: Real-Time SEO Radar Widget -->
                <div class="card">
                    <div class="card-head">
                        <h2>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>
                            رادار سلامت سئو و ارزیابی هوشمند
                        </h2>
                    </div>

                    <div class="seo-radar">
                        <div class="seo-score-circle">
                            <div class="score-gauge" id="seoGauge">
                                <div class="score-inner" id="seoScoreText">۰</div>
                            </div>
                            <div class="score-meta">
                                <h3 id="seoScoreTitle">در حال تحلیل محتوا...</h3>
                                <p id="seoScoreSummary">شاخص‌های نگارشی و بهینه‌سازی موتورهای جستجو</p>
                            </div>
                        </div>

                        <ul class="seo-checklist" id="seoChecklist">
                            <li class="seo-item" id="seoChkKeyword"><span class="seo-dot"></span> کلیدواژه کانونی تعریف شده است</li>
                            <li class="seo-item" id="seoChkTitle"><span class="seo-dot"></span> کلیدواژه در تیتر اصلی وجود دارد</li>
                            <li class="seo-item" id="seoChkLead"><span class="seo-dot"></span> کلیدواژه در لید خبر آمده است</li>
                            <li class="seo-item" id="seoChkContent"><span class="seo-dot"></span> کلیدواژه در ۱۰۰ کلمه اول متن آمده است</li>
                            <li class="seo-item" id="seoChkHeadings"><span class="seo-dot"></span> وجود زیرعنوان‌ها (H2 / H3) در متن</li>
                            <li class="seo-item" id="seoChkWordCount"><span class="seo-dot"></span> حجم محتوا بیش از ۳۰۰ کلمه است</li>
                            <li class="seo-item" id="seoChkImage"><span class="seo-dot"></span> تصویر شاخص بارگذاری شده است</li>
                        </ul>

                        <!-- Google SERP Snippet Simulator -->
                        <div style="margin-top:8px;">
                            <label class="field-label" style="margin-bottom:8px;">پیش‌نمایش در نتایج جستجوی گوگل</label>
                            <div class="serp-box">
                                <div class="serp-url">https://macsa.ir › news › <span id="serpSlug">news-article</span></div>
                                <div class="serp-title" id="serpTitle">عنوان خبر در نتایج گوگل</div>
                                <div class="serp-desc" id="serpDesc">توضیحات و لید خبر در نتایج جستجوی گوگل به این شکل برای کاربران نمایش داده خواهد شد...</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- ===== Live Image Cropper Modal (Cropper.js) ===== -->
<div class="modal-backdrop" id="cropperModal">
    <div class="modal-dialog" style="max-width:860px;">
        <div class="modal-header">
            <h3>تنظیم کادر و برش تصویر شاخص</h3>
            <button type="button" class="modal-close" id="btnCloseCropper">&times;</button>
        </div>
        <div class="modal-body">
            <div class="cropper-stage">
                <img id="cropperImage" src="" alt="برش تصویر">
            </div>
            <div class="cropper-tools">
                <div class="ratio-tabs">
                    <button type="button" class="ratio-btn active" data-ratio="1.7777777777777777">16:9 (اسلایدر)</button>
                    <button type="button" class="ratio-btn" data-ratio="1.3333333333333333">4:3 (آرشیو)</button>
                    <button type="button" class="ratio-btn" data-ratio="1">1:1 (مربع)</button>
                    <button type="button" class="ratio-btn" data-ratio="0.5625">9:16 (استوری)</button>
                    <button type="button" class="ratio-btn" data-ratio="NaN">برش آزاد</button>
                </div>
                <div style="display:flex;gap:6px;">
                    <button type="button" class="btn btn-secondary btn-sm" id="cropRotateL" title="چرخش ۹۰- درجه">↺</button>
                    <button type="button" class="btn btn-secondary btn-sm" id="cropRotateR" title="چرخش ۹۰+ درجه">↻</button>
                    <button type="button" class="btn btn-secondary btn-sm" id="cropFlipH" title="آینه افقی">⇆</button>
                    <button type="button" class="btn btn-secondary btn-sm" id="cropReset" title="بازنشانی">بازنشانی</button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btnCancelCrop">انصراف</button>
            <button type="button" class="btn btn-primary" id="btnApplyCrop">اعمال برش و بهینه‌سازی</button>
        </div>
    </div>
</div>

<!-- ===== Multi-Device Responsive Live Preview Modal ===== -->
<div class="modal-backdrop" id="previewModal">
    <div class="modal-dialog" style="max-width:1100px;height:90vh;">
        <div class="modal-header">
            <h3>پیش‌نمایش زنده در دستگاه‌های مختلف</h3>
            <button type="button" class="modal-close" id="btnClosePreview">&times;</button>
        </div>
        <div class="modal-body" style="background:var(--surface-2);">
            <div class="preview-device-bar">
                <button type="button" class="device-btn active" data-device="desktop">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    رایانه (Desktop)
                </button>
                <button type="button" class="device-btn" data-device="tablet">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    تبلت (Tablet)
                </button>
                <button type="button" class="device-btn" data-device="mobile">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    موبایل (Mobile)
                </button>
            </div>

            <div class="preview-viewport-wrapper">
                <div class="preview-viewport desktop" id="previewViewport">
                    <div id="pvKicker" style="color:var(--primary-color);font-weight:800;font-size:14px;margin-bottom:6px;"></div>
                    <h1 id="pvTitle" style="font-size:28px;font-weight:900;line-height:1.4;margin-bottom:12px;color:var(--text-color);"></h1>
                    <div id="pvSubtitle" style="font-size:16px;font-weight:600;color:var(--muted-color);margin-bottom:16px;line-height:1.6;"></div>
                    <div id="pvMeta" style="font-size:12.5px;color:var(--muted-color);margin-bottom:20px;border-bottom:1px solid var(--border-color);padding-bottom:12px;"></div>
                    <div id="pvLead" style="background:rgba(0,125,117,0.06);border-right:4px solid var(--primary-color);padding:14px 18px;border-radius:8px;font-weight:600;font-size:15.5px;line-height:1.8;margin-bottom:24px;"></div>
                    <div id="pvFeaturedImgWrap" style="margin-bottom:24px;border-radius:12px;overflow:hidden;display:none;">
                        <img id="pvFeaturedImg" src="" alt="" style="width:100%;max-height:450px;object-fit:cover;display:block;">
                        <div id="pvCaption" style="font-size:12px;color:var(--muted-color);padding:6px 10px;text-align:center;"></div>
                    </div>
                    <div id="pvContent" style="font-size:15px;line-height:1.9;"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btnClosePreviewBottom">بستن پیش‌نمایش</button>
        </div>
    </div>
</div>

<!-- Studio Toast Notification -->
<div class="studio-toast" id="studioToast"></div>

<!-- JavaScript Controllers -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // =========================================================================
    // 1. TinyMCE 7 Enterprise Initializer
    // =========================================================================
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';

    tinymce.init({
        selector: '#editor',
        directionality: 'rtl',
        language: 'fa',
        min_height: 520,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | alignright aligncenter alignleft alignjustify | numlist bullist outdent indent | forecolor backcolor | link image media table | blockquote removeformat | code fullscreen',
        content_style: `
            body {
                font-family: 'Vazirmatn', Tahoma, sans-serif;
                font-size: 15px;
                line-height: 1.85;
                color: ${isDark ? '#e7ecee' : '#2f3437'};
                background-color: ${isDark ? '#1e1e1e' : '#ffffff'};
                padding: 18px;
                direction: rtl;
            }
            figure { margin: 20px 0; text-align: center; }
            figure img { max-width: 100%; height: auto; border-radius: 10px; box-shadow: 0 4px 14px rgba(0,0,0,0.1); }
            figcaption { font-size: 12.5px; color: #889397; margin-top: 8px; font-weight: 600; }
            blockquote.quote-card {
                border-right: 4px solid #007D75;
                background: rgba(0,125,117,0.06);
                padding: 16px 20px;
                border-radius: 8px;
                margin: 20px 0;
                font-style: normal;
            }
            .callout-box {
                padding: 16px 20px;
                border-radius: 10px;
                margin: 20px 0;
                border: 1px solid rgba(0,0,0,0.08);
            }
            .callout-box.note { background: rgba(0,125,117,0.07); border-right: 4px solid #007D75; }
            .callout-box.alert { background: rgba(224,85,107,0.08); border-right: 4px solid #e0556b; }
            .callout-box.fact { background: rgba(247,159,31,0.08); border-right: 4px solid #f79f1f; }
            table { width: 100%; border-collapse: collapse; margin: 18px 0; }
            table th, table td { border: 1px solid ${isDark ? '#383838' : '#e2e8e7'}; padding: 10px 14px; text-align: right; }
            table th { background: ${isDark ? '#262626' : '#f8faf9'}; font-weight: 800; color: #007D75; }
        `,
        skin: isDark ? 'oxide-dark' : 'oxide',
        content_css: isDark ? 'dark' : 'default',
        images_upload_url: 'upload-inline-image.php',
        automatic_uploads: true,
        setup: function(editor) {
            editor.on('input change keyup SetContent', function() {
                updateMetrics();
                runSeoAudit();
            });
        }
    });

    // =========================================================================
    // 2. Metrics & Character Counters
    // =========================================================================
    const titleInput = document.getElementById('title');
    const titleCounter = document.getElementById('titleCounter');
    const titleProgress = document.getElementById('titleProgress');

    function updateTitleMetrics() {
        const len = (titleInput.value || '').trim().length;
        titleCounter.textContent = len + ' کاراکتر';
        const pct = Math.min(100, Math.round((len / 70) * 100));
        titleProgress.style.width = pct + '%';

        titleProgress.className = 'counter-fill';
        if (len >= 50 && len <= 70) {
            titleProgress.classList.add('good');
        } else if (len > 70) {
            titleProgress.classList.add('danger');
        } else if (len > 30) {
            titleProgress.classList.add('warning');
        }

        updateSerpSimulator();
        runSeoAudit();
    }
    titleInput.addEventListener('input', updateTitleMetrics);
    updateTitleMetrics();

    function updateMetrics() {
        let text = '';
        if (tinymce.get('editor')) {
            text = tinymce.get('editor').getContent({ format: 'text' }) || '';
        }
        const words = text.trim() ? text.trim().split(/\s+/).length : 0;
        const chars = text.length;
        const paras = text.trim() ? (tinymce.get('editor').getContent().match(/<p>/gi) || []).length : 0;
        const readTime = Math.max(1, Math.ceil(words / 200));

        document.getElementById('metricWords').textContent = words;
        document.getElementById('metricChars').textContent = chars;
        document.getElementById('metricParas').textContent = Math.max(1, paras);
        document.getElementById('metricReadTime').textContent = readTime + ' دقیقه';
    }

    // =========================================================================
    // 3. Persian Typography & Punctuation Normalizer (Module D)
    // =========================================================================
    function normalizePersianText(str) {
        if (!str) return '';

        // Standardize Arabic characters to Persian
        str = str.replace(/ي/g, 'ی').replace(/ك/g, 'ک').replace(/ة/g, 'ه');

        // Convert English and Arabic digits to Persian numerals
        const enDigits = ['0','1','2','3','4','5','6','7','8','9'];
        const arDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        const faDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];

        for (let i = 0; i < 10; i++) {
            str = str.replace(new RegExp(enDigits[i], 'g'), faDigits[i]);
            str = str.replace(new RegExp(arDigits[i], 'g'), faDigits[i]);
        }

        // Standardize Quotes, Commas, Question marks, Semicolons
        str = str.replace(/"([^"]*)"/g, '«$1»');
        str = str.replace(/,/g, '،');
        str = str.replace(/\?/g, '؟');
        str = str.replace(/;/g, '؛');

        // ZWNJ (نیم‌فاصله) rules
        const zwnj = '\u200c';

        // Prefixes: می / نمی
        str = str.replace(/\b(می|نمی)\s+/g, '$1' + zwnj);

        // Suffixes: ها / های / هایم / تر / ترین / ام / ات / اش
        str = str.replace(/\s+(ها|های|هایم|هایت|هایش|هایمان|هایتان|هایشان|تر|ترین|ام|ات|اش|مان|تان|شان|ای)\b/g, zwnj + '$1');

        // Common compound words
        const compounds = [
            'بین المللی', 'بین الملل', 'بی شمار', 'فوق العاده', 'گفت و گو', 'دست اندرکاران',
            'علاقه مند', 'بهره مند', 'صرفه جویی', 'یکدیگر', 'پیش بینی', 'رو به رو'
        ];
        compounds.forEach(function(cp) {
            const normalizedCp = cp.replace(/\s+/g, zwnj);
            str = str.replace(new RegExp(cp, 'g'), normalizedCp);
        });

        // Clean redundant spaces and punctuation spacing
        str = str.replace(/ {2,}/g, ' ');
        str = str.replace(/\s+([،.؟!؛:])/g, '$1');
        str = str.replace(/([،.؟!؛:])(?=[^\s\d،.؟!؛:])/g, '$1 ');

        return str;
    }

    document.getElementById('btnNormalizeText').addEventListener('click', function() {
        titleInput.value = normalizePersianText(titleInput.value);
        document.getElementById('kicker').value = normalizePersianText(document.getElementById('kicker').value);
        document.getElementById('subtitle').value = normalizePersianText(document.getElementById('subtitle').value);
        document.getElementById('lead').value = normalizePersianText(document.getElementById('lead').value);

        if (tinymce.get('editor')) {
            const rawContent = tinymce.get('editor').getContent();
            tinymce.get('editor').setContent(normalizePersianText(rawContent));
        }

        updateTitleMetrics();
        updateMetrics();
        showToast('پاکسازی و ویراستاری نیم‌فاصله‌های فارسی انجام شد.', 'success');
    });

    // =========================================================================
    // 4. Featured Image Studio & Cropper.js (Module B)
    // =========================================================================
    const dropzone = document.getElementById('imageDropzone');
    const fileInput = document.getElementById('featuredFileInput');
    const previewContainer = document.getElementById('previewContainer');
    const previewImg = document.getElementById('featuredPreviewImg');
    const cropperModal = document.getElementById('cropperModal');
    const cropperImg = document.getElementById('cropperImage');
    let cropperInstance = null;
    let currentRawImageSrc = '';

    dropzone.addEventListener('click', function() { fileInput.click(); });

    dropzone.addEventListener('dragover', function(e) { e.preventDefault(); dropzone.classList.add('drag-over'); });
    dropzone.addEventListener('dragleave', function() { dropzone.classList.remove('drag-over'); });
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            processSelectedImage(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            processSelectedImage(this.files[0]);
        }
    });

    // Clipboard Paste Handler (Ctrl+V)
    window.addEventListener('paste', function(e) {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image') === 0) {
                const blob = items[i].getAsFile();
                processSelectedImage(blob);
                showToast('تصویر از کلیپ‌بورد بارگذاری شد.', 'success');
                break;
            }
        }
    });

    function processSelectedImage(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            currentRawImageSrc = e.target.result;
            openCropper(currentRawImageSrc);
        };
        reader.readAsDataURL(file);
    }

    function openCropper(imgSrc) {
        cropperImg.src = imgSrc;
        cropperModal.classList.add('open');

        if (cropperInstance) {
            cropperInstance.destroy();
        }

        cropperInstance = new Cropper(cropperImg, {
            aspectRatio: 16 / 9,
            viewMode: 1,
            autoCropArea: 1,
            responsive: true,
            restore: false,
            checkCrossOrigin: false
        });
    }

    document.querySelectorAll('.ratio-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.ratio-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const ratio = parseFloat(this.getAttribute('data-ratio'));
            if (cropperInstance) {
                cropperInstance.setAspectRatio(isNaN(ratio) ? NaN : ratio);
            }
        });
    });

    document.getElementById('cropRotateL').addEventListener('click', function() { if (cropperInstance) cropperInstance.rotate(-90); });
    document.getElementById('cropRotateR').addEventListener('click', function() { if (cropperInstance) cropperInstance.rotate(90); });
    document.getElementById('cropFlipH').addEventListener('click', function() {
        if (cropperInstance) {
            const data = cropperInstance.getData();
            cropperInstance.scaleX(data.scaleX === -1 ? 1 : -1);
        }
    });
    document.getElementById('cropReset').addEventListener('click', function() { if (cropperInstance) cropperInstance.reset(); });

    document.getElementById('btnApplyCrop').addEventListener('click', function() {
        if (!cropperInstance) return;

        // Export cropped canvas with max dimension constraint (1920px)
        const canvas = cropperInstance.getCroppedCanvas({
            maxWidth: 1920,
            maxHeight: 1080,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        const base64WebP = canvas.toDataURL('image/webp', 0.85);
        document.getElementById('featuredImageBase64').value = base64WebP;
        document.getElementById('removeFeaturedFlag').value = '0';

        previewImg.src = base64WebP;
        previewContainer.classList.add('active');
        closeCropperModal();
        runSeoAudit();
        showToast('تصویر شاخص با موفقیت بهینه‌سازی و برش داده شد.', 'success');
    });

    function closeCropperModal() {
        cropperModal.classList.remove('open');
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
    }

    document.getElementById('btnCloseCropper').addEventListener('click', closeCropperModal);
    document.getElementById('btnCancelCrop').addEventListener('click', closeCropperModal);
    document.getElementById('btnOpenCropper').addEventListener('click', function() {
        if (previewImg.src) openCropper(previewImg.src);
    });

    // Remove Image Action
    document.getElementById('btnRemoveImage').addEventListener('click', function() {
        previewImg.src = '';
        previewContainer.classList.remove('active');
        document.getElementById('featuredImageBase64').value = '';
        document.getElementById('removeFeaturedFlag').value = '1';
        fileInput.value = '';
        runSeoAudit();
        showToast('تصویر شاخص حذف شد.', 'info');
    });

    // Focal Point Selector
    let isSettingFocal = false;
    const focalCrosshair = document.getElementById('focalCrosshair');
    document.getElementById('btnSetFocalPoint').addEventListener('click', function() {
        isSettingFocal = !isSettingFocal;
        this.classList.toggle('btn-primary', isSettingFocal);
        focalCrosshair.style.display = isSettingFocal ? 'block' : 'none';
        if (isSettingFocal) {
            showToast('روی نقطه اصلی تصویر کلیک کنید تا در موبایل برش نخورد.', 'info');
        }
    });

    previewContainer.addEventListener('click', function(e) {
        if (!isSettingFocal) return;
        const rect = previewImg.getBoundingClientRect();
        const x = Math.max(0, Math.min(100, Math.round(((e.clientX - rect.left) / rect.width) * 100)));
        const y = Math.max(0, Math.min(100, Math.round(((e.clientY - rect.top) / rect.height) * 100)));

        document.getElementById('focalX').value = x;
        document.getElementById('focalY').value = y;

        focalCrosshair.style.left = x + '%';
        focalCrosshair.style.top = y + '%';
        previewImg.style.objectPosition = `${x}% ${y}%`;
        showToast(`نقطه کانونی تنظیم شد: X: ${x}% | Y: ${y}%`, 'success');
    });

    // =========================================================================
    // 5. Modular Journalistic Content Inserters (Module C)
    // =========================================================================
    function insertIntoEditor(html) {
        if (tinymce.get('editor')) {
            tinymce.get('editor').insertContent(html);
            updateMetrics();
            runSeoAudit();
        }
    }

    document.getElementById('insPullQuote').addEventListener('click', function() {
        const quote = prompt('متن نقل‌قول یا سوتیتر برجسته:', 'این یک دستاورد بزرگ در حوزه مراقبت‌های تسکینی کشور است.');
        if (!quote) return;
        const speaker = prompt('نام گوینده / مقام مسئول:', 'دکتر محمد رضایی');
        const role = prompt('سمت یا عنوان رسمی:', 'مدیرعامل بنیاد مکسا');
        const html = `
            <blockquote class="quote-card">
                <p style="font-size:17px;font-weight:700;line-height:1.7;margin-bottom:8px;">«${quote}»</p>
                <div style="font-size:13px;color:#007D75;font-weight:800;">— ${speaker || ''} <span style="font-weight:normal;color:#7a878b;">(${role || ''})</span></div>
            </blockquote><p></p>
        `;
        insertIntoEditor(html);
    });

    document.getElementById('insCallout').addEventListener('click', function() {
        const type = prompt('نوع کادر را انتخاب کنید (1: نکته مهم، 2: فوری و هشدار، 3: جعبه آمار و فکت):', '1');
        const text = prompt('متن کادر ویژه:', 'ارائه کلیه خدمات حمایتی و درمانی مکسا برای بیماران مبتلا به سرطان کاملاً رایگان است.');
        if (!text) return;
        let className = 'note';
        let title = '📌 نکته مهم';
        if (type === '2') { className = 'alert'; title = '🚨 خبر فوری و اطلاعیه'; }
        if (type === '3') { className = 'fact'; title = '📊 آمار و اطلاعات کلیدی'; }

        const html = `
            <div class="callout-box ${className}">
                <div style="font-weight:800;font-size:14px;margin-bottom:6px;">${title}</div>
                <div>${text}</div>
            </div><p></p>
        `;
        insertIntoEditor(html);
    });

    document.getElementById('insFigure').addEventListener('click', function() {
        const url = prompt('آدرس اینترنتی تصویر (Image URL):', 'https://macsa.ir/uploads/placeholder.jpg');
        if (!url) return;
        const caption = prompt('زیرنویس و شرح تصویر (Caption):', 'مراسم افتتاح بخش جدید مراقبت‌های تسکینی');
        const credit = prompt('عکاس یا منبع:', 'عکاس: روابط عمومی مکسا');

        const html = `
            <figure>
                <img src="${url}" alt="${caption}">
                <figcaption>${caption} ${credit ? `<span style="opacity:0.8;">(${credit})</span>` : ''}</figcaption>
            </figure><p></p>
        `;
        insertIntoEditor(html);
    });

    document.getElementById('insBeforeAfter').addEventListener('click', function() {
        const beforeUrl = prompt('آدرس تصویر قبل (Before URL):', 'https://macsa.ir/uploads/before.jpg');
        const afterUrl = prompt('آدرس تصویر بعد (After URL):', 'https://macsa.ir/uploads/after.jpg');
        if (!beforeUrl || !afterUrl) return;

        const html = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:20px 0;">
                <div style="text-align:center;">
                    <img src="${beforeUrl}" style="width:100%;border-radius:8px;" alt="قبل">
                    <div style="font-size:12px;font-weight:700;color:#e0556b;margin-top:4px;">وضعیت قبل از بازسازی</div>
                </div>
                <div style="text-align:center;">
                    <img src="${afterUrl}" style="width:100%;border-radius:8px;" alt="بعد">
                    <div style="font-size:12px;font-weight:700;color:#16a37a;margin-top:4px;">وضعیت پس از بازسازی و تجهیز</div>
                </div>
            </div><p></p>
        `;
        insertIntoEditor(html);
    });

    document.getElementById('insVideo').addEventListener('click', function() {
        const aparatId = prompt('کد یا لینک ویدیو در آپارات (مثال: x1Y2z):', '');
        if (aparatId) {
            const cleanId = aparatId.replace(/https?:\/\/www\.aparat\.com\/v\//, '').trim();
            const html = `
                <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;margin:20px 0;box-shadow:0 4px 14px rgba(0,0,0,0.1);">
                    <iframe src="https://www.aparat.com/video/video/embed/videohash/${cleanId}/vt/frame" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" allowFullScreen="true"></iframe>
                </div><p></p>
            `;
            insertIntoEditor(html);
        }
    });

    document.getElementById('insAudio').addEventListener('click', function() {
        const audioUrl = prompt('آدرس فایل صوتی / پادکست (MP3 URL):', 'https://macsa.ir/uploads/podcast.mp3');
        if (!audioUrl) return;
        const title = prompt('عنوان پادکست یا مصاحبه:', 'گفت‌وگوی رادیویی پیرامون طب تسکینی');

        const html = `
            <div style="background:rgba(0,125,117,0.06);border:1px solid #007D75;border-radius:12px;padding:16px;margin:20px 0;display:flex;flex-direction:column;gap:10px;">
                <div style="font-weight:800;font-size:14px;color:#007D75;">🎧 ${title}</div>
                <audio controls style="width:100%;"><source src="${audioUrl}" type="audio/mpeg">مرورگر شما از پخش صدا پشتیبانی نمی‌کند.</audio>
            </div><p></p>
        `;
        insertIntoEditor(html);
    });

    document.getElementById('insDownload').addEventListener('click', function() {
        const fileUrl = prompt('لینک دانلود فایل ضمیمه:', 'https://macsa.ir/uploads/report.pdf');
        if (!fileUrl) return;
        const title = prompt('عنوان فایل:', 'گزارش عملکرد مالی و درمانی سالانه');
        const size = prompt('حجم فایل (اختیاری):', '۲.۴ مگابایت');

        const html = `
            <div style="display:flex;align-items:center;justify-content:space-between;background:#f8faf9;border:1px solid #e2e8e7;border-radius:12px;padding:14px 18px;margin:20px 0;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <span style="font-size:24px;">📄</span>
                    <div>
                        <div style="font-weight:800;font-size:14px;">${title}</div>
                        <div style="font-size:11.5px;color:#7a878b;">فرمت PDF ${size ? `| حجم: ${size}` : ''}</div>
                    </div>
                </div>
                <a href="${fileUrl}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#007D75;color:#fff;border-radius:8px;font-weight:700;font-size:12.5px;text-decoration:none;">دانلود فایل</a>
            </div><p></p>
        `;
        insertIntoEditor(html);
    });

    document.getElementById('insTimeline').addEventListener('click', function() {
        const html = `
            <div style="border-right:2px solid #007D75;padding-right:16px;margin:24px 0;">
                <div style="margin-bottom:16px;position:relative;">
                    <div style="font-weight:800;color:#007D75;font-size:13px;">مرحله اول: پذیرش و تریاژ بیمار</div>
                    <p style="font-size:13.5px;margin:3px 0;">بررسی پرونده پزشکی و تعیین نیازمندی‌های مراقبت در منزل.</p>
                </div>
                <div style="margin-bottom:16px;position:relative;">
                    <div style="font-weight:800;color:#007D75;font-size:13px;">مرحله دوم: اعزام تیم تخصصی</div>
                    <p style="font-size:13.5px;margin:3px 0;">ویزیت پزشک، پرستار و روانشناس در محل زندگی بیمار.</p>
                </div>
            </div><p></p>
        `;
        insertIntoEditor(html);
    });

    // =========================================================================
    // 6. Interactive Tags Manager
    // =========================================================================
    const tagsWrapper = document.getElementById('tagsWrapper');
    const tagsInput = document.getElementById('tagsInput');
    const hiddenTags = document.getElementById('hiddenTags');
    let tagsList = (hiddenTags.value || '').split(',').map(t => t.trim()).filter(Boolean);

    function renderTags() {
        tagsWrapper.querySelectorAll('.tag-chip').forEach(el => el.remove());
        tagsList.forEach((tag, idx) => {
            const chip = document.createElement('div');
            chip.className = 'tag-chip';
            chip.innerHTML = `<span>${tag}</span><span class="tag-chip-remove" data-index="${idx}">&times;</span>`;
            tagsWrapper.insertBefore(chip, tagsInput);
        });
        hiddenTags.value = tagsList.join(',');
    }

    tagsInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const val = this.value.trim().replace(/^,|,$/g, '');
            if (val && !tagsList.includes(val)) {
                tagsList.push(val);
                renderTags();
            }
            this.value = '';
        }
    });

    tagsWrapper.addEventListener('click', function(e) {
        if (e.target.classList.contains('tag-chip-remove')) {
            const idx = parseInt(e.target.getAttribute('data-index'));
            tagsList.splice(idx, 1);
            renderTags();
        }
    });
    renderTags();

    // =========================================================================
    // 7. Real-Time SEO Radar & Quality Audit (Module F)
    // =========================================================================
    const keywordInput = document.getElementById('keywords');
    keywordInput.addEventListener('input', runSeoAudit);

    function updateSerpSimulator() {
        const title = (titleInput.value || '').trim() || 'عنوان خبر در نتایج جستجو';
        const lead = (document.getElementById('lead').value || '').trim() ||
                     (tinymce.get('editor') ? tinymce.get('editor').getContent({format:'text'}).substring(0, 150) : '');

        document.getElementById('serpTitle').textContent = title;
        document.getElementById('serpDesc').textContent = lead ? lead.substring(0, 160) + '...' : 'توضیحات کوتاه این خبر در این قسمت نمایش می‌یابد.';
    }

    function runSeoAudit() {
        const keyword = (keywordInput.value || '').trim().toLowerCase();
        const title = (titleInput.value || '').trim().toLowerCase();
        const lead = (document.getElementById('lead').value || '').trim().toLowerCase();
        let contentText = '';
        let contentHtml = '';

        if (tinymce.get('editor')) {
            contentText = (tinymce.get('editor').getContent({format:'text'}) || '').toLowerCase();
            contentHtml = tinymce.get('editor').getContent() || '';
        }

        const words = contentText.trim() ? contentText.trim().split(/\s+/).length : 0;
        let score = 0;

        // Rule 1: Keyword defined
        const hasKw = keyword.length >= 2;
        toggleCheck('seoChkKeyword', hasKw);
        if (hasKw) score += 15;

        // Rule 2: Keyword in Title
        const kwInTitle = hasKw && title.includes(keyword);
        toggleCheck('seoChkTitle', kwInTitle);
        if (kwInTitle) score += 20;

        // Rule 3: Keyword in Lead
        const kwInLead = hasKw && lead.includes(keyword);
        toggleCheck('seoChkLead', kwInLead);
        if (kwInLead) score += 15;

        // Rule 4: Keyword in first 100 words
        const first100 = contentText.split(/\s+/).slice(0, 100).join(' ');
        const kwInFirst100 = hasKw && first100.includes(keyword);
        toggleCheck('seoChkContent', kwInFirst100);
        if (kwInFirst100) score += 15;

        // Rule 5: Subheadings present
        const hasHeadings = /<h[2-4][^>]*>/i.test(contentHtml);
        toggleCheck('seoChkHeadings', hasHeadings);
        if (hasHeadings) score += 10;

        // Rule 6: Word count > 300
        const has300Words = words >= 300;
        toggleCheck('seoChkWordCount', has300Words);
        if (has300Words) score += 15;

        // Rule 7: Featured image
        const hasImage = previewContainer.classList.contains('active');
        toggleCheck('seoChkImage', hasImage);
        if (hasImage) score += 10;

        // Update Score Gauge
        document.getElementById('seoScoreText').textContent = score;
        const gauge = document.getElementById('seoGauge');
        gauge.style.background = `conic-gradient(${score >= 70 ? 'var(--success)' : score >= 40 ? 'var(--warning)' : 'var(--danger)'} ${score * 3.6}deg, var(--border-color) 0deg)`;

        const scoreTitle = document.getElementById('seoScoreTitle');
        if (score >= 80) scoreTitle.textContent = 'عالی و آماده انتشار ✅';
        else if (score >= 50) scoreTitle.textContent = 'قابل قبول (نیاز به بهبود)';
        else scoreTitle.textContent = 'نیازمند بهینه‌سازی ⚠️';
    }

    function toggleCheck(id, passed) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.toggle('pass', !!passed);
        }
    }

    // =========================================================================
    // 8. Zen Mode (Distraction-Free Mode)
    // =========================================================================
    const btnToggleZen = document.getElementById('btnToggleZen');
    function toggleZenMode() {
        document.body.classList.toggle('zen-mode');
        const isZen = document.body.classList.contains('zen-mode');
        btnToggleZen.classList.toggle('btn-primary', isZen);
        showToast(isZen ? 'حالت تمرکز فعال شد (Alt+Z برای خروج)' : 'حالت عادی فعال شد', 'info');
    }
    btnToggleZen.addEventListener('click', toggleZenMode);

    window.addEventListener('keydown', function(e) {
        if (e.altKey && (e.key === 'z' || e.key === 'Z')) {
            e.preventDefault();
            toggleZenMode();
        }
    });

    // =========================================================================
    // 9. Multi-Device Live Preview Modal (Module G)
    // =========================================================================
    const previewModal = document.getElementById('previewModal');
    const previewViewport = document.getElementById('previewViewport');

    document.getElementById('btnOpenPreview').addEventListener('click', function() {
        document.getElementById('pvKicker').textContent = document.getElementById('kicker').value;
        document.getElementById('pvTitle').textContent = titleInput.value || 'بدون عنوان';
        document.getElementById('pvSubtitle').textContent = document.getElementById('subtitle').value;
        document.getElementById('pvMeta').textContent = `نویسنده: ${document.getElementById('author').value || 'تحریریه'} | تاریخ انتشار: ${document.getElementById('publishDate').value}`;

        const leadVal = document.getElementById('lead').value;
        const pvLead = document.getElementById('pvLead');
        if (leadVal) {
            pvLead.textContent = leadVal;
            pvLead.style.display = 'block';
        } else {
            pvLead.style.display = 'none';
        }

        const pvImgWrap = document.getElementById('pvFeaturedImgWrap');
        if (previewImg.src && previewContainer.classList.contains('active')) {
            document.getElementById('pvFeaturedImg').src = previewImg.src;
            document.getElementById('pvCaption').textContent = document.getElementById('imgCaption').value;
            pvImgWrap.style.display = 'block';
        } else {
            pvImgWrap.style.display = 'none';
        }

        const content = tinymce.get('editor') ? tinymce.get('editor').getContent() : '';
        document.getElementById('pvContent').innerHTML = content || '<p style="color:#888;">بدون محتوا...</p>';

        previewModal.classList.add('open');
    });

    function closePreviewModal() { previewModal.classList.remove('open'); }
    document.getElementById('btnClosePreview').addEventListener('click', closePreviewModal);
    document.getElementById('btnClosePreviewBottom').addEventListener('click', closePreviewModal);

    document.querySelectorAll('.device-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const dev = this.getAttribute('data-device');
            previewViewport.className = 'preview-viewport ' + dev;
        });
    });

    // =========================================================================
    // 10. Auto-Save & Crash Recovery Engine (Module E)
    // =========================================================================
    const draftKey = 'maxa_news_draft_' + (document.getElementById('newsId').value || 'new');
    const autoSaveStatus = document.getElementById('autoSaveStatus');

    function performAutoSave() {
        if (!titleInput.value.trim() && (!tinymce.get('editor') || !tinymce.get('editor').getContent().trim())) {
            return;
        }

        const draftData = {
            title: titleInput.value,
            kicker: document.getElementById('kicker').value,
            subtitle: document.getElementById('subtitle').value,
            lead: document.getElementById('lead').value,
            content: tinymce.get('editor') ? tinymce.get('editor').getContent() : '',
            keywords: keywordInput.value,
            author: document.getElementById('author').value,
            category_id: document.getElementById('categorySelect').value,
            tags: hiddenTags.value,
            savedAt: new Date().toLocaleTimeString('fa-IR')
        };

        try {
            localStorage.setItem(draftKey, JSON.stringify(draftData));
            autoSaveStatus.textContent = 'ذخیره در مرورگر: ' + draftData.savedAt;
        } catch(e) {}
    }

    // Auto-save every 30 seconds
    setInterval(performAutoSave, 30000);

    // Crash Recovery Check on Load
    try {
        const savedDraftRaw = localStorage.getItem(draftKey);
        if (savedDraftRaw) {
            const draft = JSON.parse(savedDraftRaw);
            if (draft && draft.title && !document.getElementById('newsId').value) {
                if (confirm(`یک پیش‌نویس ذخیره‌شده از ساعت ${draft.savedAt} یافت شد. آیا مایل به بازیابی آن هستید؟`)) {
                    titleInput.value = draft.title || '';
                    document.getElementById('kicker').value = draft.kicker || '';
                    document.getElementById('subtitle').value = draft.subtitle || '';
                    document.getElementById('lead').value = draft.lead || '';
                    keywordInput.value = draft.keywords || '';
                    document.getElementById('author').value = draft.author || '';
                    if (draft.category_id) document.getElementById('categorySelect').value = draft.category_id;
                    if (draft.tags) {
                        tagsList = draft.tags.split(',');
                        renderTags();
                    }
                    setTimeout(() => {
                        if (tinymce.get('editor') && draft.content) {
                            tinymce.get('editor').setContent(draft.content);
                        }
                        updateTitleMetrics();
                        updateMetrics();
                        runSeoAudit();
                    }, 800);
                    showToast('پیش‌نویس قبلی بازیابی شد.', 'info');
                }
            }
        }
    } catch(e) {}

    // =========================================================================
    // 11. Form Submission via AJAX (Save Draft & Final Publish)
    // =========================================================================
    const newsForm = document.getElementById('newsForm');

    function submitNews(targetStatus) {
        if (targetStatus) {
            document.getElementById('newsStatus').value = targetStatus;
            document.getElementById('statusSelect').value = targetStatus;
        }

        // Sync TinyMCE Content
        if (tinymce.get('editor')) {
            tinymce.get('editor').save();
        }

        // Validation
        if (!titleInput.value.trim()) {
            titleInput.focus();
            showToast('لطفاً عنوان اصلی خبر را وارد کنید.', 'error');
            return;
        }

        const catSelect = document.getElementById('categorySelect');
        if (!catSelect.value) {
            catSelect.focus();
            showToast('لطفاً دسته‌بندی موضوعی خبر را انتخاب کنید.', 'error');
            return;
        }

        const formData = new FormData(newsForm);
        const submitBtn = targetStatus === 'published' ? document.getElementById('btnPublishFinal') : document.getElementById('btnSaveDraft');
        const origBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'در حال ذخیره‌سازی...';

        fetch('news-save.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origBtnText;

            if (data.status === 'success') {
                showToast(data.message || 'خبر با موفقیت ذخیره شد.', 'success');
                try { localStorage.removeItem(draftKey); } catch(e) {}

                if (!document.getElementById('newsId').value && data.id) {
                    setTimeout(() => {
                        window.location.href = 'news-create.php?id=' + data.id;
                    }, 1000);
                }
            } else {
                showToast(data.message || 'خطا در ذخیره خبر رخ داد.', 'error');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origBtnText;
            showToast('خطا در ارتباط با سرور رخ داد.', 'error');
        });
    }

    document.getElementById('btnSaveDraft').addEventListener('click', function() {
        submitNews('draft');
    });

    document.getElementById('btnPublishFinal').addEventListener('click', function() {
        const st = document.getElementById('statusSelect').value || 'published';
        submitNews(st === 'draft' ? 'published' : st);
    });

    // =========================================================================
    // 12. Toast Helper
    // =========================================================================
    function showToast(msg, type) {
        const toast = document.getElementById('studioToast');
        toast.textContent = msg;
        toast.className = 'studio-toast ' + (type || '');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3500);
    }

});
</script>
</body>
</html>

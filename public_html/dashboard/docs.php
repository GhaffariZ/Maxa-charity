<?php
/**
 * پایگاه مستندات و راهنمای جامع سامانه وب مکسا (MACSA Docs Portal)
 * طراحی مدرن مبتنی بر استانداردهای مستندسازی مهندسی (مشابه ReadTheDocs / MkDocs Material)
 * یکپارچه با تم رنگی و توکن‌های طراحی پنل ادمین مکسا
 */

require_once __DIR__ . '/_guard.php';

// مسیر پایه پوشه مستندات (خارج از public_html برای امنیت بالاتر)
$baseDocsDir = realpath(dirname(dirname(__DIR__)) . '/docs');

// اگر درخواست خام (AJAX برای واکشی مارک‌داون) بود:
if (isset($_GET['raw']) && !empty($_GET['file'])) {
    $reqFile = ltrim((string)$_GET['file'], "/\\");
    $targetPath = realpath($baseDocsDir . '/' . $reqFile);

    if (
        !$targetPath ||
        strpos($targetPath, $baseDocsDir) !== 0 ||
        !preg_match('/\.md$/i', $targetPath) ||
        !file_exists($targetPath)
    ) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'سند مورد نظر یافت نشد یا دسترسی به آن مجاز نیست.']);
        exit;
    }

    header('Content-Type: text/markdown; charset=utf-8');
    header('Cache-Control: private, max-age=60');
    readfile($targetPath);
    exit;
}

// سند پیش‌فرض اولیه
$currentDoc = isset($_GET['doc']) ? trim((string)$_GET['doc']) : 'user-manual/README.md';
$currentDoc = ltrim($currentDoc, "/\\");

$targetPath = realpath($baseDocsDir . '/' . $currentDoc);
if (
    !$targetPath ||
    strpos($targetPath, $baseDocsDir) !== 0 ||
    !preg_match('/\.md$/i', $targetPath) ||
    !file_exists($targetPath)
) {
    $currentDoc = 'user-manual/README.md';
    $targetPath = realpath($baseDocsDir . '/' . $currentDoc);
}

$initialContent = ($targetPath && file_exists($targetPath)) ? file_get_contents($targetPath) : '# راهنمای سامانه مکسا';
$docLastModified = ($targetPath && file_exists($targetPath)) ? filemtime($targetPath) : time();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مستندات و راهنمای سامانه | مرکز کنترل سرطان مکسا</title>

<!-- همگام‌سازی بلادرنگ تم (دارک/لایت) با پنل مدیریت -->
<script>
(function(){
  try {
    var t = localStorage.getItem('maxa-theme');
    if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    else document.documentElement.removeAttribute('data-theme');
  } catch(e) {}
})();
</script>

<!-- فونت استاندارد سیستم مکسا -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<!-- کتابخانه‌های پردازش کلاینت برای هایلایت کد و مارک‌داون -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/highlight.js@11.9.0/styles/github-dark-dimmed.min.css" id="hljsDarkTheme">
<script src="https://cdn.jsdelivr.net/npm/highlight.js@11.9.0/highlight.min.js"></script>

<style>
/* ==========================================================================
   طراحی هماهنگ با سیستم طراحی پنل مکسا + استایل مستندات مهندسی (MkDocs/ReadTheDocs)
   ========================================================================== */
:root {
  --color-primary: #007b7a;
  --color-primary-dark: #006665;
  --color-primary-light: #4fb2b0;
  --color-secondary: #f4a61e;
  --color-text: #24292f;
  --color-text-muted: #57606a;
  --color-border: #d0d7de;
  --color-border-subtle: #eaeef2;
  --color-bg: #f6f8fa;
  --color-surface: #ffffff;
  --color-sidebar-bg: #f8fafc;
  --color-card-bg: #ffffff;
  --code-bg: #f6f8fa;
  --kbd-bg: #f3f4f6;
  --header-bg: rgba(255, 255, 255, 0.92);

  --primary-08: rgba(0, 123, 122, 0.08);
  --primary-14: rgba(0, 123, 122, 0.14);
  --secondary-12: rgba(244, 166, 30, 0.14);

  --callout-note-bg: rgba(9, 105, 218, 0.08);
  --callout-note-border: #0969da;
  --callout-tip-bg: rgba(26, 127, 55, 0.08);
  --callout-tip-border: #1a7f37;
  --callout-important-bg: rgba(130, 80, 223, 0.08);
  --callout-important-border: #8250df;
  --callout-warning-bg: rgba(154, 103, 0, 0.09);
  --callout-warning-border: #9a6700;
  --callout-caution-bg: rgba(207, 34, 46, 0.08);
  --callout-caution-border: #cf222e;

  --header-height: 64px;
  --sidebar-width: 320px;
  --toc-width: 250px;
  --content-max-width: 920px;

  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;

  --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
  --shadow-md: 0 4px 14px rgba(0,0,0,0.08);
  --shadow-lg: 0 12px 28px rgba(0,0,0,0.12);

  --ease: cubic-bezier(0.4, 0, 0.2, 1);
}

:root[data-theme="dark"] {
  --color-primary: #4fb2b0;
  --color-primary-dark: #007b7a;
  --color-primary-light: #77d3d1;
  --color-secondary: #f4a61e;
  --color-text: #e6edf3;
  --color-text-muted: #8b949e;
  --color-border: #30363d;
  --color-border-subtle: #21262d;
  --color-bg: #0d1117;
  --color-surface: #161b22;
  --color-sidebar-bg: #0d1117;
  --color-card-bg: #161b22;
  --code-bg: #161b22;
  --kbd-bg: #21262d;
  --header-bg: rgba(13, 17, 23, 0.90);

  --primary-08: rgba(79, 178, 176, 0.12);
  --primary-14: rgba(79, 178, 176, 0.20);
  --secondary-12: rgba(244, 166, 30, 0.20);

  --callout-note-bg: rgba(56, 139, 253, 0.12);
  --callout-note-border: #388bfd;
  --callout-tip-bg: rgba(46, 160, 67, 0.12);
  --callout-tip-border: #2ea043;
  --callout-important-bg: rgba(163, 113, 247, 0.12);
  --callout-important-border: #a371f7;
  --callout-warning-bg: rgba(210, 153, 34, 0.14);
  --callout-warning-border: #d29922;
  --callout-caution-bg: rgba(248, 81, 73, 0.12);
  --callout-caution-border: #f85149;

  color-scheme: dark;
}

* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  background-color: var(--color-bg);
  color: var(--color-text);
  font-size: 14.5px;
  line-height: 1.8;
  direction: rtl;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  -webkit-font-smoothing: antialiased;
}

/* ==========================================================================
   Header (هدر اصلی)
   ========================================================================== */
.docs-header {
  position: sticky;
  top: 0;
  z-index: 100;
  height: var(--header-height);
  background: var(--header-bg);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
}

.header-left, .header-right, .header-center {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: inherit;
}
.brand-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: linear-gradient(135deg, #007b7a, #005554);
  color: #ffffff;
  display: grid;
  place-items: center;
  box-shadow: 0 6px 14px -4px rgba(0, 123, 122, 0.6);
  flex-shrink: 0;
}
.brand-icon svg { width: 22px; height: 22px; }
.brand-text {
  display: flex;
  flex-direction: column;
}
.brand-title {
  font-weight: 800;
  font-size: 16px;
  line-height: 1.2;
  color: var(--color-text);
  letter-spacing: -0.2px;
}
.brand-sub {
  font-size: 11px;
  color: var(--color-text-muted);
  font-weight: 500;
}
.version-badge {
  font-size: 10.5px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 99px;
  background: var(--primary-08);
  color: var(--color-primary);
  border: 1px solid var(--primary-14);
}

/* کلید جستجوی سریع در هدر */
.search-trigger-btn {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 99px;
  padding: 8px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--color-text-muted);
  cursor: pointer;
  width: 260px;
  transition: all 0.2s var(--ease);
  font-family: inherit;
  font-size: 13px;
}
.search-trigger-btn:hover {
  border-color: var(--color-primary);
  color: var(--color-text);
  box-shadow: var(--shadow-sm);
}
.search-trigger-btn kbd {
  margin-inline-start: auto;
  background: var(--kbd-bg);
  border: 1px solid var(--color-border);
  border-radius: 4px;
  padding: 2px 6px;
  font-size: 11px;
  font-family: 'JetBrains Mono', monospace;
  color: var(--color-text-muted);
}

/* اکشن‌های هدر (تم و بازگشت به پیشخوان) */
.header-btn {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  height: 38px;
  padding: 0 14px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: inherit;
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text);
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s var(--ease);
}
.header-btn:hover {
  background: var(--primary-08);
  border-color: var(--color-primary);
  color: var(--color-primary);
}
.header-btn-icon {
  width: 38px;
  padding: 0;
  justify-content: center;
  border-radius: 50%;
}
.header-btn-primary {
  background: linear-gradient(135deg, #007b7a, #005c5b);
  border-color: transparent;
  color: #ffffff !important;
  box-shadow: 0 4px 12px -2px rgba(0, 123, 122, 0.4);
}
.header-btn-primary:hover {
  background: linear-gradient(135deg, #006665, #004b4a);
  color: #ffffff;
  transform: translateY(-1px);
}

.mobile-menu-btn {
  display: none;
  background: none;
  border: none;
  color: var(--color-text);
  cursor: pointer;
  padding: 6px;
  border-radius: 6px;
}

/* ==========================================================================
   Layout (3 ستونه: منو چپ، محتوا وسط، فهرست راست)
   ========================================================================== */
.docs-layout {
  display: flex;
  flex: 1;
  width: 100%;
  max-width: 1680px;
  margin: 0 auto;
}

/* ستون ناوبری اسناد (Navigation Sidebar) */
.docs-nav-sidebar {
  width: var(--sidebar-width);
  flex-shrink: 0;
  background: var(--color-sidebar-bg);
  border-inline-end: 1px solid var(--color-border);
  height: calc(100vh - var(--header-height));
  position: sticky;
  top: var(--header-height);
  overflow-y: auto;
  padding: 20px 16px 40px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.docs-nav-sidebar::-webkit-scrollbar { width: 6px; }
.docs-nav-sidebar::-webkit-scrollbar-thumb {
  background: var(--color-border);
  border-radius: 4px;
}

/* فیلتر سریع منو */
.sidebar-filter-wrap {
  position: relative;
}
.sidebar-filter-input {
  width: 100%;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 8px 12px 8px 34px;
  font-family: inherit;
  font-size: 12.5px;
  color: var(--color-text);
  outline: none;
  transition: border-color 0.2s;
}
.sidebar-filter-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px var(--primary-08);
}
.sidebar-filter-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-text-muted);
  pointer-events: none;
}

/* دسته‌بندی‌های منو */
.nav-group-title {
  font-size: 11.5px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--color-text-muted);
  padding: 6px 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.nav-group-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.nav-item-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border-radius: var(--radius-sm);
  color: var(--color-text-muted);
  text-decoration: none;
  font-size: 13.5px;
  font-weight: 500;
  line-height: 1.4;
  transition: all 0.18s var(--ease);
  position: relative;
}
.nav-item-link:hover {
  background: var(--color-surface);
  color: var(--color-text);
}
.nav-item-link.active {
  background: var(--primary-08);
  color: var(--color-primary);
  font-weight: 700;
}
.nav-item-link.active::before {
  content: '';
  position: absolute;
  right: 0;
  top: 6px;
  bottom: 6px;
  width: 3.5px;
  border-radius: 4px;
  background: var(--color-primary);
}
.nav-badge-num {
  font-size: 11px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  opacity: 0.6;
  min-width: 22px;
}
.nav-item-link.active .nav-badge-num {
  opacity: 1;
}

/* ستون محتوای اصلی (Article Area) */
.docs-main-container {
  flex: 1;
  min-width: 0;
  padding: 32px 48px 60px;
  display: flex;
  justify-content: center;
}
.docs-article-wrapper {
  width: 100%;
  max-width: var(--content-max-width);
}

/* بردکرامب و اطلاعات مقاله */
.article-header-meta {
  margin-bottom: 28px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--color-border-subtle);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}
.article-breadcrumbs {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  color: var(--color-text-muted);
}
.article-breadcrumbs a {
  color: inherit;
  text-decoration: none;
  transition: color 0.15s;
}
.article-breadcrumbs a:hover {
  color: var(--color-primary);
}
.article-breadcrumbs .sep {
  opacity: 0.4;
}
.article-tools {
  display: flex;
  align-items: center;
  gap: 8px;
}
.tool-pill {
  font-size: 12px;
  color: var(--color-text-muted);
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  padding: 4px 10px;
  border-radius: 99px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s;
}
.tool-pill:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

/* رندر مارک‌داون (Typography & Component Styles) */
.markdown-body {
  color: var(--color-text);
  line-height: 1.85;
  font-size: 15px;
}
.markdown-body h1,
.markdown-body h2,
.markdown-body h3,
.markdown-body h4 {
  font-weight: 800;
  line-height: 1.35;
  color: var(--color-text);
  position: relative;
  scroll-margin-top: calc(var(--header-height) + 24px);
}
.markdown-body h1 {
  font-size: 30px;
  margin-bottom: 24px;
  letter-spacing: -0.4px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--color-border);
}
.markdown-body h2 {
  font-size: 22px;
  margin-top: 42px;
  margin-bottom: 18px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--color-border-subtle);
}
.markdown-body h3 {
  font-size: 18px;
  margin-top: 30px;
  margin-bottom: 14px;
}
.markdown-body h4 {
  font-size: 15.5px;
  margin-top: 22px;
  margin-bottom: 10px;
}

.markdown-body h2 .header-anchor,
.markdown-body h3 .header-anchor {
  margin-inline-start: 8px;
  color: var(--color-primary);
  text-decoration: none;
  opacity: 0;
  transition: opacity 0.2s;
  font-size: 0.85em;
  font-weight: normal;
}
.markdown-body h2:hover .header-anchor,
.markdown-body h3:hover .header-anchor {
  opacity: 0.8;
}

.markdown-body p {
  margin-bottom: 18px;
}
.markdown-body ul, .markdown-body ol {
  margin-bottom: 20px;
  padding-inline-start: 26px;
}
.markdown-body li {
  margin-bottom: 8px;
}
.markdown-body li::marker {
  color: var(--color-primary);
}

.markdown-body a {
  color: var(--color-primary);
  text-decoration: underline;
  text-underline-offset: 3px;
  font-weight: 500;
  transition: color 0.15s;
}
.markdown-body a:hover {
  color: var(--color-primary-dark);
}

.markdown-body hr {
  border: 0;
  height: 1px;
  background: var(--color-border);
  margin: 36px 0;
}

.markdown-body strong {
  font-weight: 700;
  color: var(--color-text);
}

.markdown-body img {
  max-width: 100%;
  height: auto;
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
  margin: 18px 0;
  display: block;
}

/* بلوک نقل قول (Blockquote) */
.markdown-body blockquote {
  background: var(--primary-08);
  border-inline-start: 4px solid var(--color-primary);
  padding: 14px 20px;
  margin: 20px 0;
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
  color: var(--color-text);
  font-style: normal;
}
.markdown-body blockquote p:last-child {
  margin-bottom: 0;
}

/* کادرهای هشدار GitHub Callouts (Alerts) */
.callout {
  padding: 16px 20px;
  margin: 24px 0;
  border-radius: var(--radius-md);
  border-inline-start: 4px solid;
  position: relative;
}
.callout-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 800;
  font-size: 14px;
  margin-bottom: 8px;
}
.callout p:last-child { margin-bottom: 0; }

.callout-note {
  background: var(--callout-note-bg);
  border-color: var(--callout-note-border);
  color: var(--color-text);
}
.callout-note .callout-title { color: var(--callout-note-border); }

.callout-tip {
  background: var(--callout-tip-bg);
  border-color: var(--callout-tip-border);
  color: var(--color-text);
}
.callout-tip .callout-title { color: var(--callout-tip-border); }

.callout-important {
  background: var(--callout-important-bg);
  border-color: var(--callout-important-border);
  color: var(--color-text);
}
.callout-important .callout-title { color: var(--callout-important-border); }

.callout-warning {
  background: var(--callout-warning-bg);
  border-color: var(--callout-warning-border);
  color: var(--color-text);
}
.callout-warning .callout-title { color: var(--callout-warning-border); }

.callout-caution {
  background: var(--callout-caution-bg);
  border-color: var(--callout-caution-border);
  color: var(--color-text);
}
.callout-caution .callout-title { color: var(--callout-caution-border); }

/* جداول (Tables) */
.table-container {
  width: 100%;
  overflow-x: auto;
  margin: 24px 0;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-surface);
  box-shadow: var(--shadow-sm);
}
.markdown-body table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13.5px;
  text-align: right;
}
.markdown-body th, .markdown-body td {
  padding: 12px 18px;
  border-bottom: 1px solid var(--color-border);
}
.markdown-body th {
  background: var(--primary-08);
  font-weight: 700;
  color: var(--color-text);
}
.markdown-body tr:last-child td {
  border-bottom: none;
}
.markdown-body tr:nth-child(even) td {
  background: rgba(0,0,0,0.015);
}
:root[data-theme="dark"] .markdown-body tr:nth-child(even) td {
  background: rgba(255,255,255,0.02);
}

/* کد و تکه برنامه‌ها (Code Blocks) */
.markdown-body code {
  font-family: 'JetBrains Mono', Consolas, Monaco, monospace;
  font-size: 13px;
  padding: 2.5px 6px;
  border-radius: 6px;
  background: var(--code-bg);
  color: var(--color-primary);
  border: 1px solid var(--color-border-subtle);
  direction: ltr;
  display: inline-block;
}
.code-block-wrapper {
  position: relative;
  margin: 22px 0;
  border-radius: var(--radius-md);
  overflow: hidden;
  border: 1px solid var(--color-border);
  background: #161b22;
  box-shadow: var(--shadow-sm);
}
.code-block-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 16px;
  background: #0d1117;
  border-bottom: 1px solid #30363d;
  color: #8b949e;
  font-size: 12px;
  font-family: 'JetBrains Mono', monospace;
  direction: ltr;
}
.code-copy-btn {
  background: #21262d;
  border: 1px solid #30363d;
  border-radius: 6px;
  color: #c9d1d9;
  font-size: 11.5px;
  font-family: inherit;
  padding: 4px 10px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}
.code-copy-btn:hover {
  background: #30363d;
  color: #ffffff;
}
.markdown-body pre {
  margin: 0;
  padding: 16px 20px;
  overflow-x: auto;
  direction: ltr;
  text-align: left;
  background: #161b22;
}
.markdown-body pre code {
  background: transparent;
  border: none;
  padding: 0;
  color: #e6edf3;
  font-size: 13.5px;
  line-height: 1.6;
}

/* کلیدهای صفحه کلید (kbd) */
.markdown-body kbd {
  font-family: 'JetBrains Mono', monospace;
  background: var(--kbd-bg);
  border: 1px solid var(--color-border);
  border-bottom: 2px solid var(--color-border);
  border-radius: 4px;
  padding: 2px 6px;
  font-size: 12px;
  box-shadow: inset 0 -1px 0 rgba(0,0,0,0.1);
}

/* ناوبری پایین صفحه (صفحه بعدی / قبلی) */
.article-pagination {
  margin-top: 56px;
  padding-top: 32px;
  border-top: 1px solid var(--color-border);
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
.pagination-card {
  display: flex;
  flex-direction: column;
  padding: 18px 20px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  text-decoration: none;
  color: inherit;
  background: var(--color-surface);
  transition: all 0.2s var(--ease);
}
.pagination-card:hover {
  border-color: var(--color-primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}
.pagination-card.next {
  text-align: left;
  align-items: flex-end;
}
.pagination-label {
  font-size: 12px;
  color: var(--color-text-muted);
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 6px;
}
.pagination-title {
  font-size: 14.5px;
  font-weight: 700;
  color: var(--color-primary);
}

/* ستون فهرست مطالب صفحه (On this page - Table of Contents) */
.docs-toc-sidebar {
  width: var(--toc-width);
  flex-shrink: 0;
  height: calc(100vh - var(--header-height));
  position: sticky;
  top: var(--header-height);
  overflow-y: auto;
  padding: 28px 18px 40px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.toc-title {
  font-size: 12.5px;
  font-weight: 800;
  color: var(--color-text);
  display: flex;
  align-items: center;
  gap: 8px;
}
.toc-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 6px;
  border-inline-start: 2px solid var(--color-border-subtle);
  padding-inline-start: 12px;
}
.toc-item-link {
  font-size: 13px;
  color: var(--color-text-muted);
  text-decoration: none;
  line-height: 1.5;
  display: block;
  transition: color 0.15s;
  padding: 2px 0;
}
.toc-item-link:hover {
  color: var(--color-primary);
}
.toc-item-link.active {
  color: var(--color-primary);
  font-weight: 700;
}
.toc-h3 {
  padding-inline-start: 12px;
  font-size: 12px;
}

.back-to-top-btn {
  margin-top: 18px;
  font-size: 12px;
  color: var(--color-text-muted);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: none;
  background: none;
  font-family: inherit;
  transition: color 0.15s;
}
.back-to-top-btn:hover {
  color: var(--color-primary);
}

/* ==========================================================================
   Search Modal (مودال جستجوی بلادرنگ Ctrl+K)
   ========================================================================== */
.search-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: none;
  align-items: flex-start;
  justify-content: center;
  padding-top: 10vh;
}
.search-modal-backdrop.open {
  display: flex;
}
.search-modal-box {
  width: 90%;
  max-width: 640px;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: modalIn 0.2s var(--ease);
}
@keyframes modalIn {
  from { opacity: 0; transform: translateY(-12px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.search-input-wrap {
  display: flex;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid var(--color-border);
  gap: 12px;
}
.search-input-field {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  font-family: inherit;
  font-size: 16px;
  color: var(--color-text);
}
.search-results-list {
  max-height: 420px;
  overflow-y: auto;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.search-result-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border-radius: var(--radius-sm);
  text-decoration: none;
  color: var(--color-text);
  transition: background 0.15s;
}
.search-result-item:hover, .search-result-item.selected {
  background: var(--primary-08);
  color: var(--color-primary);
}
.search-result-title {
  font-weight: 700;
  font-size: 14px;
}
.search-result-sub {
  font-size: 12px;
  color: var(--color-text-muted);
}
.search-footer {
  padding: 10px 20px;
  border-top: 1px solid var(--color-border);
  background: var(--color-bg);
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 11.5px;
  color: var(--color-text-muted);
}

/* ==========================================================================
   Responsive Breakpoints (واکنش‌گرایی موبایل و تبلت)
   ========================================================================== */
@media (max-width: 1200px) {
  .docs-toc-sidebar { display: none; }
  .docs-main-container { padding: 28px 32px 50px; }
}

@media (max-width: 860px) {
  .mobile-menu-btn { display: block; }
  .search-trigger-btn { width: 160px; }
  .search-trigger-btn kbd { display: none; }
  .docs-nav-sidebar {
    position: fixed;
    top: var(--header-height);
    right: 0;
    bottom: 0;
    z-index: 90;
    width: 300px;
    transform: translateX(100%);
    transition: transform 0.25s var(--ease);
    box-shadow: var(--shadow-lg);
    background: var(--color-surface);
  }
  .docs-nav-sidebar.open {
    transform: translateX(0);
  }
  .docs-main-container {
    padding: 20px 18px 40px;
  }
  .article-pagination {
    grid-template-columns: 1fr;
  }
}
</style>
</head>
<body>

<!-- هدر اصلی پورتال مستندات -->
<header class="docs-header">
  <div class="header-right">
    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="منوی سرفصل‌ها">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="docs.php" class="brand-wrap">
      <div class="brand-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
          <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
        </svg>
      </div>
      <div class="brand-text">
        <span class="brand-title">مستندات مکسا</span>
        <span class="brand-sub">مرکز کنترل سرطان مکسا</span>
      </div>
    </a>
    <span class="version-badge">نسخه آزمایشی v2.4</span>
  </div>

  <div class="header-center">
    <button class="search-trigger-btn" id="searchTriggerBtn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <span>جستجو در مستندات...</span>
      <kbd>Ctrl K</kbd>
    </button>
  </div>

  <div class="header-left">
    <!-- تغییر تم دارک/لایت -->
    <button class="header-btn header-btn-icon" id="themeToggleBtn" title="تغییر حالت شب/روز">
      <svg id="themeIconSun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      <svg id="themeIconMoon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    </button>

    <!-- بازگشت مستقیم به پیشخوان مدیریت -->
    <a href="index.php" class="header-btn header-btn-primary" target="_top">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span>بازگشت به پیشخوان</span>
    </a>
  </div>
</header>

<!-- ساختار ۳ ستونه داکیومنت -->
<div class="docs-layout">

  <!-- ستون راست: منوی درختی سرفصل‌ها (Nav Drawer) -->
  <aside class="docs-nav-sidebar" id="docsSidebar">
    <div class="sidebar-filter-wrap">
      <input type="text" class="sidebar-filter-input" id="sidebarFilter" placeholder="فیلتر سرفصل‌ها..." autocomplete="off">
      <svg class="sidebar-filter-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    </div>

    <!-- گروه اول: راهنمای جامع کاربران (User Manual) -->
    <div>
      <div class="nav-group-title">
        <span>📖 راهنمای کاربران (User Manual)</span>
        <span style="font-size:10px;opacity:0.7">۲۲ بخش</span>
      </div>
      <ul class="nav-group-list" id="userManualNavList">
        <!-- آیتم‌های منو به صورت داینامیک یا سروری رندر می‌شوند -->
      </ul>
    </div>

    <!-- گروه دوم: مستندات فنی و مهندسی (Developer Docs) -->
    <div>
      <div class="nav-group-title">
        <span>🛠️ مستندات فنی (Engineering)</span>
      </div>
      <ul class="nav-group-list" id="devDocsNavList">
        <!-- لینک‌های فنی -->
      </ul>
    </div>
  </aside>

  <!-- ستون وسط: محتوای اصلی سند (Article) -->
  <main class="docs-main-container">
    <div class="docs-article-wrapper">
      
      <!-- بردکرامب و متادیتای بالا -->
      <div class="article-header-meta">
        <div class="article-breadcrumbs" id="breadcrumbs">
          <a href="docs.php">مستندات مکسا</a>
          <span class="sep">/</span>
          <span id="breadcrumbCategory">راهنمای کاربران</span>
          <span class="sep">/</span>
          <span id="breadcrumbCurrent" style="color:var(--color-primary);font-weight:700">مقدمه</span>
        </div>

        <div class="article-tools">
          <span class="tool-pill" id="readingTimePill" title="تخمین زمان مطالعه">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span id="readingTimeText">۳ دقیقه مطالعه</span>
          </span>
          <button class="tool-pill" id="copyPageLinkBtn" title="کپی پیوند این سند">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            <span>کپی پیوند</span>
          </button>
        </div>
      </div>

      <!-- بدنه اصلی مارک‌داون -->
      <article class="markdown-body" id="markdownContent">
        <!-- رندر اولیه در کلاینت یا سرور -->
      </article>

      <!-- ناوبری صفحه قبلی و بعدی در انتهای مقاله -->
      <nav class="article-pagination" id="articlePagination">
        <a href="#" class="pagination-card prev" id="prevArticleCard" style="display:none">
          <span class="pagination-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span>بخش قبلی</span>
          </span>
          <span class="pagination-title" id="prevArticleTitle"></span>
        </a>

        <a href="#" class="pagination-card next" id="nextArticleCard" style="display:none">
          <span class="pagination-label">
            <span>بخش بعدی</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          </span>
          <span class="pagination-title" id="nextArticleTitle"></span>
        </a>
      </nav>

    </div>
  </main>

  <!-- ستون چپ: فهرست مطالب این صفحه (On this page TOC) -->
  <aside class="docs-toc-sidebar">
    <div class="toc-title">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      <span>در این صفحه</span>
    </div>
    <ul class="toc-list" id="tocList">
      <!-- توسط جاوااسکریپت از روی تگ‌های H2 و H3 پر می‌شود -->
    </ul>
    <button class="back-to-top-btn" id="backToTopBtn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
      <span>بازگشت به بالای صفحه</span>
    </button>
  </aside>

</div>

<!-- مودال جستجوی بلادرنگ (Ctrl+K) -->
<div class="search-modal-backdrop" id="searchModal">
  <div class="search-modal-box">
    <div class="search-input-wrap">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" class="search-input-field" id="searchModalInput" placeholder="جستجو در تمام سرفصل‌ها، واژه‌ها و دستورالعمل‌ها..." autocomplete="off">
      <kbd>ESC</kbd>
    </div>
    <div class="search-results-list" id="searchResultsList">
      <!-- نتایج جستجو -->
    </div>
    <div class="search-footer">
      <span>پیمایش با کلیدهای ↑ و ↓</span>
      <span>انتخاب با Enter</span>
      <span>خروج با ESC</span>
    </div>
  </div>
</div>

<!-- متن سند اولیه برای لود آنی و بدون تأخیر در اولین باز شدن -->
<script type="text/markdown" id="rawInitialContent"><?= htmlspecialchars($initialContent, ENT_QUOTES, 'UTF-8') ?></script>

<script>
/* ==========================================================================
   بانک اطلاعات جامع سرفصل‌های مستندات سامانه مکسا
   ========================================================================== */
const DOCS_CATALOG = [
  // راهنمای کاربران (User Manual)
  { id: 'user-manual/README.md', num: '۰۰', title: 'مقدمه و نقشه راهنمای سامانه', group: 'manual', desc: 'معرفی جامع سامانه وب مکسا، مخاطبان و نقشه راه مطالعه' },
  { id: 'user-manual/01-getting-started.md', num: '۰۱', title: 'مفاهیم پایه و معماری سامانه', group: 'manual', desc: 'معماری چندشعبه‌ای، ایزولاسیون اطلاعات و مدل هیبرید استاتیک-داینامیک' },
  { id: 'user-manual/02-roles-and-permissions.md', num: '۰۲', title: 'نقش‌ها و سطوح دسترسی', group: 'manual', desc: 'ماتریس کامل دسترسی‌های سوپرادمین، مدیر شعبه، سردبیر، پذیرش و نیکوکار' },
  { id: 'user-manual/03-login-and-account.md', num: '۰۳', title: 'ورود، خروج و حساب کاربری', group: 'manual', desc: 'احراز هویت دومرحله‌ای، امنیت نشست، نشست‌های همزمان و انقضا' },
  { id: 'user-manual/04-admin-dashboard.md', num: '۰۴', title: 'پیشخوان مدیریت و شاخص‌های کلیدی', group: 'manual', desc: 'آمار تجمیعی، نمودارها، سوئیچر شعب و وضعیت لاگ‌ها' },
  { id: 'user-manual/05-branch-management.md', num: '۰۵', title: 'مدیریت و تعریف شعب جدید', group: 'manual', desc: 'ایجاد شعبه جدید، ساب‌دامین اختصاصی و تخصیص سهمیه‌ها' },
  { id: 'user-manual/06-user-management.md', num: '۰۶', title: 'مدیریت کاربران و نقش‌های شعبه', group: 'manual', desc: 'افزودن کاربر جدید، تخصیص دسترسی‌های ماژولار و غیرفعال‌سازی' },
  { id: 'user-manual/07-page-builder.md', num: '۰۷', title: 'صفحه‌ساز و کامپوننت‌های مکسا', group: 'manual', desc: 'راهنمای کامل ۴۸ کامپوننت ماژولار، تمپلیت‌ها و انتشار صفحات' },
  { id: 'user-manual/08-news.md', num: '۰۸', title: 'مدیریت اخبار و گردش‌کار تحریریه', group: 'manual', desc: 'نگارش خبر، صف بررسی سردبیری ستاد، برچسب‌ها و زمان‌بندی' },
  { id: 'user-manual/09-campaigns.md', num: '۰۹', title: 'مدیریت کمپین‌های حمایتی', group: 'manual', desc: 'تعریف کمپین، سقف مالی، نوار پیشرفت کمک‌ها و گزارش‌گیری' },
  { id: 'user-manual/10-stands-and-orders.md', num: '۱۰', title: 'استندهای همدلی و کارتابل سفارشات', group: 'manual', desc: 'مدیریت انواع استند تسلیت و شادباش، چاپ فاکتور و وضعیت سفارش' },
  { id: 'user-manual/11-courses.md', num: '۱۱', title: 'سامانه آموزش و آکادمی مکسا (LMS)', group: 'manual', desc: 'دوره‌های آموزشی مراقبت حمایتی، سرفصل‌ها، ویدیوها و ثبت‌نام' },
  { id: 'user-manual/12-maxapedia.md', num: '۱۲', title: 'دانشنامه سلامت و چندرسانه‌ای مکساپدیا', group: 'manual', desc: 'مقالات تخصصی سرطان و تسکینی، پادکست‌ها، ویدیوها و اینفوگرافیک' },
  { id: 'user-manual/13-stories.md', num: '۱۳', title: 'بانک روایات امید مکسا', group: 'manual', desc: 'داستان‌های بهبودیافتگان و امیدآفرین، مصاحبه‌ها و رضایت‌نامه‌ها' },
  { id: 'user-manual/14-partners.md', num: '۱۴', title: 'شبکه همکاران و معرفی پرسنل', group: 'manual', desc: 'ثبت رزومه پزشکان، پرستاران، مددکاران و داوطلبان خیریه' },
  { id: 'user-manual/15-ticketing.md', num: '۱۵', title: 'سامانه تیکتینگ و مکاتبات سازمانی', group: 'manual', desc: 'ارسال تیکت پشتیبانی، ارجاع بین شعب، اولویت‌بندی و پیوست فایل' },
  { id: 'user-manual/16-financial.md', num: '۱۶', title: 'گزارش‌های مالی و تحلیل تراکنش‌ها', group: 'manual', desc: 'داشبورد مالی، تفکیک درگاه‌ها، خروجی اکسل و سهم شعب' },
  { id: 'user-manual/17-feedback.md', num: '۱۷', title: 'انتقادات و پیشنهادات مراجعان', group: 'manual', desc: 'صندوق پیشنهادات بیماران و همراهان، بررسی محرمانه و پاسخگویی' },
  { id: 'user-manual/18-benefactor-dashboard.md', num: '۱۸', title: 'راهنمای پرتال و داشبورد خیرین', group: 'manual', desc: 'ورود با پیامک، مشاهده اثر کمک‌ها، گواهی کسر مالیات و پرداخت دوره‌ای' },
  { id: 'user-manual/19-public-site.md', num: '۱۹', title: 'ساختار سایت عمومی و صفحات شعب', group: 'manual', desc: 'لندینگ پیج، مسیریابی ساب‌دامین شعب، فرم‌ها و صفحات تماس' },
  { id: 'user-manual/20-troubleshooting.md', num: '۲۰', title: 'راهنمای عیب‌یابی و رفع خطاها', group: 'manual', desc: 'کدهای خطا، مشکلات ورود، نشست، کش و راه‌حل‌های فوری' },
  { id: 'user-manual/21-faq.md', num: '۲۱', title: 'پرسش‌های متداول (FAQ)', group: 'manual', desc: 'پاسخ به سوالات پرتکرار کاربران سامانه، اپراتورها و مدیران' },

  // مستندات مهندسی و فنی (Developer Docs)
  { id: 'README.md', num: 'ENG', title: 'پایگاه جامع مستندات فنی مکسا', group: 'dev', desc: 'ساختار کلی پوشه docs، استانداردها و راهنمای مشارکت' },
  { id: 'MULTI-BRANCH-IMPLEMENTATION.md', num: 'ARC', title: 'پیاده‌سازی احراز هویت و معماری چندشعبه‌ای', group: 'dev', desc: 'تشریح سشن امن، Rate Limiter، ایزولاسیون Tenant و اسکریپت‌های مایگریشن' },
  { id: 'PROMPT-multi-branch-dashboard.md', num: 'SPC', title: 'اسپک فنی و الزامات مهندسی داشبورد', group: 'dev', desc: 'سند اصلی الزامات مهندسی داشبورد مکسا و مشخصات عملکردی' }
];

let activeDocId = '<?= addslashes($currentDoc) ?>';

/* ==========================================================================
   مدیریت تم (دارک و لایت) با ذخیره در localStorage و همگام با داشبورد
   ========================================================================== */
const themeToggleBtn = document.getElementById('themeToggleBtn');
const themeIconSun   = document.getElementById('themeIconSun');
const themeIconMoon  = document.getElementById('themeIconMoon');

function updateThemeIcons(isDark) {
  if (isDark) {
    themeIconSun.style.display = 'block';
    themeIconMoon.style.display = 'none';
  } else {
    themeIconSun.style.display = 'none';
    themeIconMoon.style.display = 'block';
  }
}

function initTheme() {
  var isDark = false;
  try {
    isDark = localStorage.getItem('maxa-theme') === 'dark';
  } catch(e){}

  if (isDark) {
    document.documentElement.setAttribute('data-theme', 'dark');
  } else {
    document.documentElement.removeAttribute('data-theme');
  }
  updateThemeIcons(isDark);
}

themeToggleBtn.addEventListener('click', () => {
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  const newTheme = isDark ? 'light' : 'dark';
  if (newTheme === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
  } else {
    document.documentElement.removeAttribute('data-theme');
  }
  try {
    localStorage.setItem('maxa-theme', newTheme);
  } catch(e){}
  updateThemeIcons(newTheme === 'dark');
});

// شنونده رویداد ذخیره‌سازی برای تب‌های دیگر مرورگر
window.addEventListener('storage', (e) => {
  if (!e || e.key === 'maxa-theme' || e.key === null) {
    initTheme();
  }
});

/* ==========================================================================
   رندر منوی کناری (Sidebar Navigation Tree)
   ========================================================================== */
function renderSidebarNav() {
  const manualList = document.getElementById('userManualNavList');
  const devList = document.getElementById('devDocsNavList');

  manualList.innerHTML = '';
  devList.innerHTML = '';

  DOCS_CATALOG.forEach(doc => {
    const isActive = doc.id === activeDocId;
    const li = document.createElement('li');
    li.innerHTML = `
      <a href="docs.php?doc=${encodeURIComponent(doc.id)}" 
         class="nav-item-link ${isActive ? 'active' : ''}" 
         data-doc="${doc.id}">
        <span class="nav-badge-num">${doc.num}</span>
        <span>${doc.title}</span>
      </a>
    `;

    li.querySelector('a').addEventListener('click', (e) => {
      e.preventDefault();
      loadDoc(doc.id);
      // بستن منو در موبایل پس از کلیک
      document.getElementById('docsSidebar').classList.remove('open');
    });

    if (doc.group === 'manual') {
      manualList.appendChild(li);
    } else {
      devList.appendChild(li);
    }
  });
}

/* فیلتر سریع منوی کناری */
document.getElementById('sidebarFilter').addEventListener('input', (e) => {
  const q = e.target.value.trim().toLowerCase();
  document.querySelectorAll('.nav-item-link').forEach(a => {
    const text = a.textContent.toLowerCase();
    const li = a.closest('li');
    if (!q || text.includes(q)) {
      li.style.display = '';
    } else {
      li.style.display = 'none';
    }
  });
});

/* دکمه منوی موبایل */
document.getElementById('mobileMenuBtn').addEventListener('click', () => {
  document.getElementById('docsSidebar').classList.toggle('open');
});

/* ==========================================================================
   پردازش و شخصی‌سازی مارک‌داون (GitHub Alerts, Syntax Highlight, Tables)
   ========================================================================== */
function processCustomMarkdownElements(html) {
  // ۱. تبدیل خودکار نقل‌قول‌های اخطار گیت‌هاب (GitHub Style Alerts)
  // > [!NOTE], > [!TIP], > [!IMPORTANT], > [!WARNING], > [!CAUTION]
  const alertTypes = {
    'NOTE':      { cls: 'callout-note',      title: 'یادداشت',         icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>' },
    'TIP':       { cls: 'callout-tip',       title: 'نکته کاربردی',     icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>' },
    'IMPORTANT': { cls: 'callout-important', title: 'مهم',             icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' },
    'WARNING':   { cls: 'callout-warning',   title: 'هشدار و توجه',    icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>' },
    'CAUTION':   { cls: 'callout-caution',   title: 'احتیاط و هشدار امنیتی', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' }
  };

  const container = document.createElement('div');
  container.innerHTML = html;

  // تبدیل blockquoteهای حاوی تگ alert
  container.querySelectorAll('blockquote').forEach(bq => {
    const text = bq.innerHTML.trim();
    for (const [key, cfg] of Object.entries(alertTypes)) {
      const regex = new RegExp(`\\[!${key}\\]`, 'i');
      if (regex.test(text)) {
        const cleanHtml = text.replace(regex, '').replace(/^<p>\s*<\/p>/, '').trim();
        const callout = document.createElement('div');
        callout.className = `callout ${cfg.cls}`;
        callout.innerHTML = `
          <div class="callout-title">${cfg.icon} <span>${cfg.title}</span></div>
          <div>${cleanHtml}</div>
        `;
        bq.replaceWith(callout);
        break;
      }
    }
  });

  // ۲. بسته‌بندی جداول داخل ظرف افقی اسکرول‌پذیر
  container.querySelectorAll('table').forEach(tbl => {
    if (!tbl.parentElement.classList.contains('table-container')) {
      const wrap = document.createElement('div');
      wrap.className = 'table-container';
      tbl.parentNode.insertBefore(wrap, tbl);
      wrap.appendChild(tbl);
    }
  });

  // ۳. افزودن هدر و دکمه کپی به کدهای برنامه‌نویسی
  container.querySelectorAll('pre').forEach(pre => {
    const code = pre.querySelector('code');
    let lang = 'CODE';
    if (code) {
      const cls = code.className || '';
      const match = cls.match(/language-([a-zA-Z0-9_-]+)/);
      if (match) lang = match[1].toUpperCase();
    }
    const wrapper = document.createElement('div');
    wrapper.className = 'code-block-wrapper';
    wrapper.innerHTML = `
      <div class="code-block-header">
        <span>${lang}</span>
        <button class="code-copy-btn" type="button">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
          <span>کپی کد</span>
        </button>
      </div>
    `;
    pre.parentNode.insertBefore(wrapper, pre);
    wrapper.appendChild(pre);

    const copyBtn = wrapper.querySelector('.code-copy-btn');
    copyBtn.addEventListener('click', () => {
      const rawText = code ? code.innerText : pre.innerText;
      navigator.clipboard.writeText(rawText).then(() => {
        copyBtn.innerHTML = `
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#2ea043"><polyline points="20 6 9 17 4 12"/></svg>
          <span style="color:#2ea043">کپی شد!</span>
        `;
        setTimeout(() => {
          copyBtn.innerHTML = `
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            <span>کپی کد</span>
          `;
        }, 2000);
      });
    });
  });

  // ۴. افزودن شناسه و لنگر به سرتیترهای H2 و H3
  container.querySelectorAll('h2, h3').forEach((heading, idx) => {
    const rawText = heading.textContent.trim();
    // ایجاد slug ساده و سازگار با فارسی
    let slug = heading.id || ('sec-' + idx + '-' + rawText.toLowerCase().replace(/[^a-zA-Z0-9\u0600-\u06FF]+/g, '-').replace(/^-+|-+$/g, ''));
    heading.id = slug;

    const anchor = document.createElement('a');
    anchor.className = 'header-anchor';
    anchor.href = '#' + slug;
    anchor.innerHTML = '#';
    anchor.title = 'پیوند به این بخش';
    heading.appendChild(anchor);
  });

  // ۵. تبدیل لینک‌های نسبی به فایل‌های md برای پیمایش کلاینتی بدون ریلود
  container.querySelectorAll('a[href]').forEach(a => {
    const href = a.getAttribute('href');
    if (href && !href.startsWith('http') && !href.startsWith('#') && !href.startsWith('mailto:')) {
      if (href.endsWith('.md') || href.includes('.md#')) {
        a.addEventListener('click', (e) => {
          e.preventDefault();
          const parts = href.split('#');
          let targetDoc = parts[0];
          const targetHash = parts[1] || '';

          // حل مسیر نسبی نسبت به سند فعلی
          if (targetDoc.startsWith('../')) {
            targetDoc = targetDoc.replace(/^\.\.\//, '');
          } else if (targetDoc.startsWith('./')) {
            targetDoc = targetDoc.replace(/^\.\//, '');
            if (activeDocId.includes('/')) {
              targetDoc = activeDocId.substring(0, activeDocId.lastIndexOf('/') + 1) + targetDoc;
            }
          } else if (!targetDoc.includes('/') && activeDocId.includes('/')) {
            targetDoc = activeDocId.substring(0, activeDocId.lastIndexOf('/') + 1) + targetDoc;
          }

          loadDoc(targetDoc, targetHash);
        });
      }
    }
  });

  return container.innerHTML;
}

/* ==========================================================================
   ایجاد فهرست محتوای سمت چپ (On this page TOC) و شنونده اسکرول
   ========================================================================== */
function buildTableOfContents() {
  const tocList = document.getElementById('tocList');
  tocList.innerHTML = '';

  const headings = document.querySelectorAll('#markdownContent h2, #markdownContent h3');
  if (!headings.length) {
    tocList.innerHTML = '<li style="font-size:12px;color:var(--color-text-muted)">سرفصل خاصی در این سند یافت نشد.</li>';
    return;
  }

  headings.forEach(h => {
    const isH3 = h.tagName.toLowerCase() === 'h3';
    const text = h.childNodes[0] ? h.childNodes[0].textContent.trim() : h.textContent.trim();
    const id = h.id;

    const li = document.createElement('li');
    li.innerHTML = `
      <a href="#${id}" class="toc-item-link ${isH3 ? 'toc-h3' : ''}" data-target="${id}">
        ${text}
      </a>
    `;

    li.querySelector('a').addEventListener('click', (e) => {
      e.preventDefault();
      const targetEl = document.getElementById(id);
      if (targetEl) {
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        history.replaceState(null, null, '#' + id);
      }
    });

    tocList.appendChild(li);
  });

  setupScrollSpy();
}

/* هایلایت بلادرنگ سرفصل در حال مطالعه هنگام اسکرول (Scroll-Spy) */
let scrollSpyObserver = null;
function setupScrollSpy() {
  if (scrollSpyObserver) scrollSpyObserver.disconnect();

  const headings = document.querySelectorAll('#markdownContent h2, #markdownContent h3');
  if (!headings.length) return;

  scrollSpyObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const id = entry.target.id;
        document.querySelectorAll('.toc-item-link').forEach(link => {
          if (link.getAttribute('data-target') === id) {
            link.classList.add('active');
          } else {
            link.classList.remove('active');
          }
        });
      }
    });
  }, {
    rootMargin: '-80px 0px -70% 0px',
    threshold: 0
  });

  headings.forEach(h => scrollSpyObserver.observe(h));
}

/* بازگشت به بالای صفحه */
document.getElementById('backToTopBtn').addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

/* ==========================================================================
   به‌روزرسانی متادیتا، بردکرامب، تخمین مطالعه و کارت‌های قبلی/بعدی
   ========================================================================== */
function updateDocMetadata(docId, rawMarkdown) {
  const meta = DOCS_CATALOG.find(d => d.id === docId) || {
    title: 'سند راهنما',
    group: 'manual'
  };

  // عنوان تب و صفحه
  document.title = meta.title + ' | مستندات سامانه مکسا';

  // بردکرامب
  document.getElementById('breadcrumbCategory').textContent = 
    meta.group === 'manual' ? 'راهنمای کاربران' : 'مستندات مهندسی';
  document.getElementById('breadcrumbCurrent').textContent = meta.title;

  // تخمین زمان مطالعه بر اساس تعداد کلمات فارسی (میانگین ۱۸۰ کلمه در دقیقه)
  const wordCount = rawMarkdown.replace(/[`#*_\-[\]()]/g, ' ').trim().split(/\s+/).length;
  const minutes = Math.max(1, Math.ceil(wordCount / 180));
  document.getElementById('readingTimeText').textContent = minutes + ' دقیقه مطالعه';

  // محاسبه کارت‌های صفحه قبلی و بعدی
  const currentIndex = DOCS_CATALOG.findIndex(d => d.id === docId);
  const prevCard = document.getElementById('prevArticleCard');
  const nextCard = document.getElementById('nextArticleCard');

  if (currentIndex > 0) {
    const prevDoc = DOCS_CATALOG[currentIndex - 1];
    prevCard.style.display = 'flex';
    document.getElementById('prevArticleTitle').textContent = prevDoc.title;
    prevCard.onclick = (e) => { e.preventDefault(); loadDoc(prevDoc.id); };
  } else {
    prevCard.style.display = 'none';
  }

  if (currentIndex >= 0 && currentIndex < DOCS_CATALOG.length - 1) {
    const nextDoc = DOCS_CATALOG[currentIndex + 1];
    nextCard.style.display = 'flex';
    document.getElementById('nextArticleTitle').textContent = nextDoc.title;
    nextCard.onclick = (e) => { e.preventDefault(); loadDoc(nextDoc.id); };
  } else {
    nextCard.style.display = 'none';
  }
}

/* ==========================================================================
   بارگذاری و رندر سند (Client-Side Dynamic Loading with Cache)
   ========================================================================== */
const docCache = {};

function renderMarkdownText(rawMarkdown, docId, targetHash = '') {
  let html = '';
  if (window.marked) {
    // تنظیمات Marked برای شکست خطوط و هایلایت کدها
    marked.setOptions({
      gfm: true,
      breaks: false,
      highlight: function(code, lang) {
        if (window.hljs) {
          if (lang && hljs.getLanguage(lang)) {
            try { return hljs.highlight(code, { language: lang }).value; } catch(e){}
          }
          return hljs.highlightAuto(code).value;
        }
        return code;
      }
    });
    html = marked.parse(rawMarkdown);
  } else {
    // فال‌بک پایه‌ای در صورت عدم بارگذاری CDN
    html = rawMarkdown
      .replace(/^# (.*$)/gim, '<h1>$1</h1>')
      .replace(/^## (.*$)/gim, '<h2>$1</h2>')
      .replace(/^### (.*$)/gim, '<h3>$1</h3>')
      .replace(/\n\n/g, '<p></p>');
  }

  const processedHtml = processCustomMarkdownElements(html);
  document.getElementById('markdownContent').innerHTML = processedHtml;

  // هایلایت مجدد کدهای احتمالی باقیمانده
  if (window.hljs) {
    document.querySelectorAll('#markdownContent pre code').forEach(block => {
      hljs.highlightElement(block);
    });
  }

  updateDocMetadata(docId, rawMarkdown);
  buildTableOfContents();

  // هایلایت آیتم فعال در منوی سایدبار
  activeDocId = docId;
  document.querySelectorAll('.nav-item-link').forEach(a => {
    if (a.getAttribute('data-doc') === docId) {
      a.classList.add('active');
    } else {
      a.classList.remove('active');
    }
  });

  // اسکرول به هش در صورت وجود، یا به بالای صفحه
  if (targetHash) {
    setTimeout(() => {
      const el = document.getElementById(targetHash);
      if (el) el.scrollIntoView({ behavior: 'smooth' });
    }, 100);
  } else {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

function loadDoc(docId, targetHash = '') {
  const cleanId = docId.replace(/^\/+/, '');

  // اگر قبلاً کش شده بود، بدون درخواست شبکه رندر کن
  if (docCache[cleanId]) {
    history.pushState({ docId: cleanId }, '', `docs.php?doc=${encodeURIComponent(cleanId)}${targetHash ? '#' + targetHash : ''}`);
    renderMarkdownText(docCache[cleanId], cleanId, targetHash);
    return;
  }

  document.getElementById('markdownContent').innerHTML = `
    <div style="text-align:center;padding:80px 20px;color:var(--color-text-muted)">
      <div style="font-size:32px;margin-bottom:12px">⏳</div>
      <p style="font-weight:700">در حال بارگذاری سند...</p>
    </div>
  `;

  fetch(`docs.php?raw=1&file=${encodeURIComponent(cleanId)}`)
    .then(res => {
      if (!res.ok) throw new Error('فایل یافت نشد');
      return res.text();
    })
    .then(markdown => {
      docCache[cleanId] = markdown;
      history.pushState({ docId: cleanId }, '', `docs.php?doc=${encodeURIComponent(cleanId)}${targetHash ? '#' + targetHash : ''}`);
      renderMarkdownText(markdown, cleanId, targetHash);
    })
    .catch(err => {
      document.getElementById('markdownContent').innerHTML = `
        <div class="callout callout-caution">
          <div class="callout-title">خطا در دریافت سند</div>
          <p>امکان بارگذاری پرونده <code>${cleanId}</code> میسر نشد. لطفاً اتصال اینترنت یا درستی نشانی را بررسی فرمایید.</p>
        </div>
      `;
    });
}

// پشتیبانی از دکمه‌های بازگشت/جلو مرورگر (Browser History Back/Forward)
window.addEventListener('popstate', (e) => {
  const urlParams = new URLSearchParams(window.location.search);
  const docFromUrl = urlParams.get('doc') || 'user-manual/README.md';
  const hashFromUrl = window.location.hash.replace('#', '');
  if (docFromUrl !== activeDocId) {
    loadDoc(docFromUrl, hashFromUrl);
  }
});

/* دکمه کپی لینک مستقیم این صفحه */
document.getElementById('copyPageLinkBtn').addEventListener('click', () => {
  navigator.clipboard.writeText(window.location.href).then(() => {
    const btn = document.getElementById('copyPageLinkBtn');
    const prev = btn.innerHTML;
    btn.innerHTML = `
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#2ea043"><polyline points="20 6 9 17 4 12"/></svg>
      <span style="color:#2ea043">پیوند کپی شد!</span>
    `;
    setTimeout(() => { btn.innerHTML = prev; }, 2000);
  });
});

/* ==========================================================================
   جستجوی سراسری پیشرفته (Ctrl+K Modal Search)
   ========================================================================== */
const searchModal = document.getElementById('searchModal');
const searchTriggerBtn = document.getElementById('searchTriggerBtn');
const searchModalInput = document.getElementById('searchModalInput');
const searchResultsList = document.getElementById('searchResultsList');

function openSearchModal() {
  searchModal.classList.add('open');
  searchModalInput.value = '';
  renderSearchResults('');
  setTimeout(() => searchModalInput.focus(), 50);
}

function closeSearchModal() {
  searchModal.classList.remove('open');
}

searchTriggerBtn.addEventListener('click', openSearchModal);

window.addEventListener('keydown', (e) => {
  if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
    e.preventDefault();
    if (searchModal.classList.contains('open')) closeSearchModal();
    else openSearchModal();
  }
  if (e.key === 'Escape' && searchModal.classList.contains('open')) {
    closeSearchModal();
  }
});

searchModal.addEventListener('click', (e) => {
  if (e.target === searchModal) closeSearchModal();
});

function renderSearchResults(query) {
  const q = query.trim().toLowerCase();
  searchResultsList.innerHTML = '';

  const matches = DOCS_CATALOG.filter(doc => {
    if (!q) return true;
    return doc.title.toLowerCase().includes(q) || 
           doc.desc.toLowerCase().includes(q) ||
           doc.num.toLowerCase().includes(q);
  });

  if (!matches.length) {
    searchResultsList.innerHTML = `
      <div style="text-align:center;padding:32px;color:var(--color-text-muted);font-size:13px">
        موردی مطابق با عبارت «${query}» یافت نشد.
      </div>
    `;
    return;
  }

  matches.slice(0, 8).forEach((doc, idx) => {
    const item = document.createElement('a');
    item.className = `search-result-item ${idx === 0 ? 'selected' : ''}`;
    item.href = `docs.php?doc=${encodeURIComponent(doc.id)}`;
    item.innerHTML = `
      <div style="width:28px;height:28px;border-radius:6px;background:var(--primary-08);color:var(--color-primary);display:grid;place-items:center;font-weight:700;font-size:11px">
        ${doc.num}
      </div>
      <div>
        <div class="search-result-title">${doc.title}</div>
        <div class="search-result-sub">${doc.desc}</div>
      </div>
    `;

    item.addEventListener('click', (e) => {
      e.preventDefault();
      closeSearchModal();
      loadDoc(doc.id);
    });

    searchResultsList.appendChild(item);
  });
}

searchModalInput.addEventListener('input', (e) => {
  renderSearchResults(e.target.value);
});

// ناوبری با کیبورد در نتایج جستجو
searchModalInput.addEventListener('keydown', (e) => {
  const items = searchResultsList.querySelectorAll('.search-result-item');
  if (!items.length) return;

  let current = Array.from(items).findIndex(it => it.classList.contains('selected'));
  if (e.key === 'ArrowDown') {
    e.preventDefault();
    if (current < items.length - 1) {
      items[current]?.classList.remove('selected');
      items[current + 1].classList.add('selected');
      items[current + 1].scrollIntoView({ block: 'nearest' });
    }
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    if (current > 0) {
      items[current]?.classList.remove('selected');
      items[current - 1].classList.add('selected');
      items[current - 1].scrollIntoView({ block: 'nearest' });
    }
  } else if (e.key === 'Enter') {
    e.preventDefault();
    if (current >= 0 && items[current]) {
      items[current].click();
    }
  }
});

/* ==========================================================================
   راه‌اندازی اولیه صفحه
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  renderSidebarNav();

  // رندر متن سند اولیه تعبیه شده در تگ اسکریپت
  const initialRaw = document.getElementById('rawInitialContent').textContent;
  docCache[activeDocId] = initialRaw;
  const hash = window.location.hash.replace('#', '');
  renderMarkdownText(initialRaw, activeDocId, hash);
});
</script>

</body>
</html>

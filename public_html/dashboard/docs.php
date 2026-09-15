<?php
/**
 * پایگاه مستندات و راهنمای جامع سامانه وب مکسا (MACSA Docs Portal)
 * طراحی مدرن، فوق‌العاده واکنش‌گرا (موبایل، تبلت، دسکتاپ و صفحات اولتراواید)
 * همگام با پالت رنگی و استانداردهای طراحی پنل ادمین مکسا
 */

require_once __DIR__ . '/_guard.php';

// تابع هوشمند تشخیص مسیر پایگاه مستندات (با چند لایه فال‌بک برای محیط‌های سروری مختلف)
function getDocsBaseDir() {
    $candidates = [
        realpath(__DIR__ . '/../../docs'),
        realpath(__DIR__ . '/../docs'),
        realpath(__DIR__ . '/docs'),
        __DIR__ . '/../../docs',
        __DIR__ . '/../docs',
        __DIR__ . '/docs',
        dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'docs',
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'docs'
    ];
    foreach ($candidates as $dir) {
        if ($dir && is_dir($dir) && (file_exists($dir . '/user-manual/README.md') || file_exists($dir . '/README.md'))) {
            return $dir;
        }
    }
    return realpath(__DIR__ . '/../docs') ?: (__DIR__ . '/../docs');
}

$baseDocsDir = getDocsBaseDir();

// اگر درخواست خام (AJAX برای واکشی مارک‌داون) بود:
if (isset($_GET['raw']) && !empty($_GET['file'])) {
    // جلوگیری کامل از Path Traversal
    $reqFile = ltrim(str_replace(['\\', '..'], ['/', ''], (string)$_GET['file']), '/');
    if (strpos($reqFile, 'docs/') === 0) {
        $reqFile = substr($reqFile, 5);
    }

    $candidates = [
        $baseDocsDir . '/' . $reqFile,
        __DIR__ . '/../docs/' . $reqFile,
        __DIR__ . '/../../docs/' . $reqFile,
        realpath(__DIR__ . '/../docs') ? realpath(__DIR__ . '/../docs') . '/' . $reqFile : null,
        realpath(__DIR__ . '/../../docs') ? realpath(__DIR__ . '/../../docs') . '/' . $reqFile : null,
        // فال‌بک برای فایل‌های بدون پوشه
        $baseDocsDir . '/user-manual/' . $reqFile,
        __DIR__ . '/../docs/user-manual/' . $reqFile,
        __DIR__ . '/../../docs/user-manual/' . $reqFile,
    ];

    $resolvedPath = null;
    foreach ($candidates as $cand) {
        if (!$cand) continue;
        $real = realpath($cand);
        if ($real && file_exists($real) && preg_match('/\.md$/i', $real)) {
            $resolvedPath = $real;
            break;
        } elseif (file_exists($cand) && preg_match('/\.md$/i', $cand)) {
            $resolvedPath = $cand;
            break;
        }
    }

    if (!$resolvedPath) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'سند مورد نظر یافت نشد.',
            'requested' => $reqFile
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Content-Type: text/markdown; charset=utf-8');
    header('Cache-Control: private, max-age=180');
    readfile($resolvedPath);
    exit;
}

// سند پیش‌فرض اولیه
$currentDoc = isset($_GET['doc']) ? trim((string)$_GET['doc']) : 'user-manual/README.md';
$currentDoc = ltrim(str_replace(['\\', '..'], ['/', ''], $currentDoc), '/');
if (strpos($currentDoc, 'docs/') === 0) {
    $currentDoc = substr($currentDoc, 5);
}

$initialResolved = null;
$checkFiles = [
    $baseDocsDir . '/' . $currentDoc,
    __DIR__ . '/../docs/' . $currentDoc,
    __DIR__ . '/../../docs/' . $currentDoc,
    $baseDocsDir . '/user-manual/' . $currentDoc,
    __DIR__ . '/../docs/user-manual/' . $currentDoc,
    __DIR__ . '/../../docs/user-manual/' . $currentDoc,
];
foreach ($checkFiles as $cf) {
    $real = realpath($cf);
    if ($real && file_exists($real) && preg_match('/\.md$/i', $real)) {
        $initialResolved = $real;
        break;
    } elseif (file_exists($cf) && preg_match('/\.md$/i', $cf)) {
        $initialResolved = $cf;
        break;
    }
}

if (!$initialResolved) {
    $currentDoc = 'user-manual/README.md';
    $fallbackFiles = [
        $baseDocsDir . '/' . $currentDoc,
        __DIR__ . '/../docs/' . $currentDoc,
        __DIR__ . '/../../docs/' . $currentDoc
    ];
    foreach ($fallbackFiles as $ff) {
        $real = realpath($ff);
        if ($real && file_exists($real)) {
            $initialResolved = $real;
            break;
        } elseif (file_exists($ff)) {
            $initialResolved = $ff;
            break;
        }
    }
}

$initialContent = ($initialResolved && file_exists($initialResolved)) ? file_get_contents($initialResolved) : "# راهنمای سامانه مکسا\n\nبه پایگاه جامع مستندات و راهنمای سامانه وب مرکز کنترل سرطان مکسا خوش آمدید.";
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<title>مستندات و راهنمای سامانه | مرکز کنترل سرطان مکسا</title>

<!-- همگام‌سازی بلادرنگ تم (دارک/لایت) با محافظت کامل در برابر مسدودکننده‌های کوکی/استوریج -->
<script>
(function(){
  try {
    var t = localStorage.getItem('maxa-theme');
    if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    else document.documentElement.removeAttribute('data-theme');
  } catch(e) {}
})();
</script>

<!-- فونت‌های استاندارد سامانه مکسا -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../webfont/rooyin/rooyin.css">
<link rel="stylesheet" href="webfont/rooyin/rooyin.css">

<!-- کتابخانه‌های پردازش کلاینت برای هایلایت کد و مارک‌داون -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/highlight.js@11.9.0/styles/github-dark-dimmed.min.css" id="hljsDarkTheme">
<script src="https://cdn.jsdelivr.net/npm/highlight.js@11.9.0/highlight.min.js"></script>

<!-- کتابخانه استاندارد رسم فلوچارت و نمودارهای گرافیکی (Mermaid.js) -->
<script src="https://cdn.jsdelivr.net/npm/mermaid@10.9.1/dist/mermaid.min.js"></script>

<style>
/* ==========================================================================
   سیستم طراحی و متغیرهای رنگی یکپارچه با پنل مکسا + استایل مستندات مهندسی
   ========================================================================== */
:root {
  --color-primary: #007b7a;
  --color-primary-dark: #006665;
  --color-primary-light: #4fb2b0;
  --color-secondary: #f4a61e;
  --color-text: #1e293b;
  --color-text-muted: #64748b;
  --color-border: #e2e8f0;
  --color-border-subtle: #f1f5f9;
  --color-bg: #f8fafc;
  --color-surface: #ffffff;
  --color-sidebar-bg: #f8fafc;
  --code-bg: #f1f5f9;
  --kbd-bg: #f3f4f6;
  --header-bg: rgba(255, 255, 255, 0.94);

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
  --sidebar-width: 310px;
  --toc-width: 250px;
  --content-max-width: 960px;

  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;

  --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
  --shadow-md: 0 4px 14px rgba(0,0,0,0.08);
  --shadow-lg: 0 16px 36px rgba(0,0,0,0.12);

  --ease: cubic-bezier(0.4, 0, 0.2, 1);
}

:root[data-theme="dark"] {
  --color-primary: #4fb2b0;
  --color-primary-dark: #007b7a;
  --color-primary-light: #77d3d1;
  --color-secondary: #f4a61e;
  --color-text: #f1f5f9;
  --color-text-muted: #94a3b8;
  --color-border: #334155;
  --color-border-subtle: #1e293b;
  --color-bg: #0b0f17;
  --color-surface: #141b26;
  --color-sidebar-bg: #0f1520;
  --code-bg: #1a2232;
  --kbd-bg: #1e293b;
  --header-bg: rgba(15, 21, 32, 0.94);

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

/* ==========================================================================
   تعریف قلم فارسی «رویین» (Rooyin Font) جهت متون و کدهای فارسی
   ========================================================================== */
@font-face {
  font-family: 'Rooyin';
  src: local('Rooyin'),
       local('Rooyin-Regular'),
       url('../webfont/rooyin/RooyinFree-Regular.woff2') format('woff2'),
       url('webfont/rooyin/RooyinFree-Regular.woff2') format('woff2'),
       url('/webfont/rooyin/RooyinFree-Regular.woff2') format('woff2'),
       url('/assets/fonts/rooyin/RooyinFree-Regular.woff2') format('woff2');
  font-weight: 400;
  font-style: normal;
  font-display: swap;
}

@font-face {
  font-family: 'Rooyin';
  src: local('Rooyin Bold'),
       local('Rooyin-Bold'),
       url('../webfont/rooyin/RooyinFree-Bold.woff2') format('woff2'),
       url('webfont/rooyin/RooyinFree-Bold.woff2') format('woff2'),
       url('/webfont/rooyin/RooyinFree-Bold.woff2') format('woff2'),
       url('/assets/fonts/rooyin/RooyinFree-Bold.woff2') format('woff2');
  font-weight: 700;
  font-style: normal;
  font-display: swap;
}

@font-face {
  font-family: 'RooyinDots2';
  src: url('../webfont/rooyin/RooyinFree-RegularDots2.woff2') format('woff2'),
       url('webfont/rooyin/RooyinFree-RegularDots2.woff2') format('woff2'),
       url('/webfont/rooyin/RooyinFree-RegularDots2.woff2') format('woff2'),
       url('/assets/fonts/rooyin/RooyinFree-RegularDots2.woff2') format('woff2');
  font-weight: 400;
  font-style: normal;
  font-display: swap;
}

.font-rooyin,
.font-rooyin * {
  font-family: 'Rooyin', 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
  font-feature-settings: "liga" 1, "calt" 1;
  letter-spacing: 0 !important;
  word-spacing: normal !important;
}

.font-rooyin-dots2,
.font-rooyin-dots2 * {
  font-family: 'RooyinDots2', 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
}

* { box-sizing: border-box; margin: 0; padding: 0; }
button, input, select, textarea {
  font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
html { scroll-behavior: smooth; }
body {
  font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  background-color: var(--color-bg);
  color: var(--color-text);
  font-size: 15px;
  line-height: 1.85;
  direction: rtl;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  -webkit-font-smoothing: antialiased;
  position: relative;
  overflow-x: hidden;
}

/* افکت درخشش ملایم پس‌زمینه (Next.js Ambient Glow) */
body::before {
  content: '';
  position: fixed;
  top: -120px;
  left: 50%;
  transform: translateX(-50%);
  width: 960px;
  height: 420px;
  background: radial-gradient(ellipse at center, rgba(0, 123, 122, 0.12) 0%, rgba(79, 178, 176, 0.04) 50%, transparent 75%);
  pointer-events: none;
  z-index: 0;
}
:root[data-theme="dark"] body::before {
  background: radial-gradient(ellipse at center, rgba(79, 178, 176, 0.16) 0%, rgba(0, 123, 122, 0.04) 50%, transparent 75%);
}

/* ==========================================================================
   Header (هدر شیشه‌ای مایع - Liquid Glass Header)
   ========================================================================== */
.docs-header {
  position: sticky;
  top: 0;
  z-index: 100;
  height: var(--header-height);
  background: rgba(255, 255, 255, 0.82);
  backdrop-filter: blur(16px) saturate(180%);
  -webkit-backdrop-filter: blur(16px) saturate(180%);
  border-bottom: 1px solid rgba(0, 0, 0, 0.07);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  width: 100%;
}
:root[data-theme="dark"] .docs-header {
  background: rgba(11, 15, 23, 0.82);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.header-left, .header-right, .header-center {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
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

/* کلید جستجو در هدر (Liquid Glass Style) */
.search-trigger-btn {
  background: rgba(255, 255, 255, 0.65);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 99px;
  padding: 8px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--color-text-muted);
  cursor: pointer;
  width: clamp(180px, 22vw, 260px);
  transition: all 0.25s var(--ease);
  font-family: inherit;
  font-size: 13px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03), inset 0 1px 0 rgba(255,255,255,0.8);
}
:root[data-theme="dark"] .search-trigger-btn {
  background: rgba(20, 27, 38, 0.65);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 2px 10px rgba(0,0,0,0.25), inset 0 1px 0 rgba(255,255,255,0.05);
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

/* اکشن‌های هدر */
.header-btn {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  height: 38px;
  padding: 0 12px;
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
  flex-shrink: 0;
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
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  color: var(--color-text);
  cursor: pointer;
  width: 38px;
  height: 38px;
  border-radius: var(--radius-sm);
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* ==========================================================================
   Layout (3 ستونه: منو راست چسبیده به لبه، محتوا وسط، فهرست چپ)
   ========================================================================== */
.docs-layout {
  display: flex;
  flex: 1;
  width: 100%;
  margin: 0;
  padding: 0;
  position: relative;
}

/* پس‌زمینه نیمه‌شفاف برای بستن منوی موبایل با لمس بیرون */
.sidebar-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  z-index: 85;
  opacity: 0;
  visibility: hidden;
  transition: all 0.25s var(--ease);
}
.sidebar-backdrop.open {
  opacity: 1;
  visibility: visible;
}

/* ستون ناوبری سرفصل‌ها (Sidebar Navigation Drawer چسبیده به سمت راست) */
.docs-nav-sidebar {
  width: var(--sidebar-width);
  flex-shrink: 0;
  background: rgba(248, 250, 252, 0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-inline-end: 1px solid var(--color-border);
  height: calc(100vh - var(--header-height));
  position: sticky;
  top: var(--header-height);
  overflow-y: auto;
  padding: 22px 14px 40px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}
:root[data-theme="dark"] .docs-nav-sidebar {
  background: rgba(15, 21, 32, 0.80);
}
.docs-nav-sidebar::-webkit-scrollbar { width: 5px; }
.docs-nav-sidebar::-webkit-scrollbar-thumb {
  background: var(--color-border);
  border-radius: 4px;
}

.sidebar-header-mobile {
  display: none;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--color-border);
  margin-bottom: 4px;
}
.sidebar-close-btn {
  background: none;
  border: none;
  color: var(--color-text-muted);
  cursor: pointer;
  padding: 6px;
  border-radius: 6px;
  display: grid;
  place-items: center;
}

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
  font-size: 13px;
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

.nav-group-title {
  font-size: 12px;
  font-weight: 800;
  color: var(--color-text-muted);
  padding: 6px 10px;
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
  padding: 8px 10px;
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
  padding: 32px clamp(16px, 4vw, 48px) 60px;
  display: flex;
  justify-content: center;
}
.docs-article-wrapper {
  width: 100%;
  max-width: var(--content-max-width);
}

/* بردکرامب و متادیتا */
.article-header-meta {
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--color-border-subtle);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
}
.article-breadcrumbs {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  color: var(--color-text-muted);
  overflow-x: auto;
  white-space: nowrap;
  padding-bottom: 2px;
}
.article-breadcrumbs a {
  color: inherit;
  text-decoration: none;
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
.tool-pill,
#copyPageLinkBtn,
#readingTimePill {
  font-size: 12px;
  font-family: 'Rooyin', 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
  color: var(--color-text-muted);
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  padding: 5px 12px;
  border-radius: 99px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s;
  font-feature-settings: "liga" 1, "calt" 1;
  letter-spacing: 0 !important;
  word-spacing: normal !important;
  line-height: 1.4;
  -webkit-appearance: none;
  appearance: none;
}
.tool-pill:hover,
#copyPageLinkBtn:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}
.tool-pill span,
.tool-pill button,
#copyPageLinkBtn span,
#readingTimePill span {
  font-family: 'Rooyin', 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
  font-feature-settings: "liga" 1, "calt" 1;
  letter-spacing: 0 !important;
  word-spacing: normal !important;
}

/* جعبه سرفصل درون-صفحه برای موبایل و تبلت (In-Article Mobile TOC Accordion) */
.mobile-inline-toc {
  display: none;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  margin-bottom: 24px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.mobile-inline-toc-header {
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  font-weight: 700;
  font-size: 13.5px;
  color: var(--color-text);
  user-select: none;
}
.mobile-inline-toc-header svg.chev {
  transition: transform 0.2s var(--ease);
}
.mobile-inline-toc.open .mobile-inline-toc-header svg.chev {
  transform: rotate(180deg);
}
.mobile-inline-toc-body {
  display: none;
  padding: 0 16px 14px;
  border-top: 1px solid var(--color-border-subtle);
}
.mobile-inline-toc.open .mobile-inline-toc-body {
  display: block;
}

/* رندر مارک‌داون */
.markdown-body {
  color: var(--color-text);
  line-height: 1.85;
  font-size: 15.5px;
  word-break: break-word;
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
  font-size: clamp(22px, 4vw, 30px);
  margin-bottom: 24px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--color-border);
}
.markdown-body h2 {
  font-size: clamp(18px, 3.2vw, 22px);
  margin-top: 40px;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--color-border-subtle);
}
.markdown-body h3 {
  font-size: clamp(16px, 2.5vw, 18px);
  margin-top: 28px;
  margin-bottom: 12px;
}
.markdown-body h4 {
  font-size: 15px;
  margin-top: 20px;
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
  padding-inline-start: 24px;
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
}
.markdown-body a:hover {
  color: var(--color-primary-dark);
}

.markdown-body hr {
  border: 0;
  height: 1px;
  background: var(--color-border);
  margin: 32px 0;
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

.markdown-body blockquote {
  background: var(--primary-08);
  border-inline-start: 4px solid var(--color-primary);
  padding: 14px 18px;
  margin: 20px 0;
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
  color: var(--color-text);
}
.markdown-body blockquote p:last-child {
  margin-bottom: 0;
}

/* کادرهای هشدار گیت‌هاب (Liquid Glass Callouts) */
.callout {
  padding: 16px 20px;
  margin: 24px 0;
  border-radius: 14px;
  border: 1px solid;
  border-inline-start-width: 4px;
  position: relative;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 4px 18px -4px rgba(0, 0, 0, 0.04);
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

/* جداول واکنش‌گرا */
.table-container {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  margin: 22px 0;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-surface);
  box-shadow: var(--shadow-sm);
}
.markdown-body table {
  width: 100%;
  min-width: 480px;
  border-collapse: collapse;
  font-size: 13.5px;
  text-align: right;
}
.markdown-body th, .markdown-body td {
  padding: 11px 16px;
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

/* برچسب‌های وضعیت برداری مدرن (Iconly Style Status Badges) */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  border-radius: 99px;
  font-size: 11.5px;
  font-weight: 700;
  line-height: 1.2;
}
.status-badge svg { flex-shrink: 0; }
.status-active {
  background: rgba(22, 163, 122, 0.12);
  color: #16a37a;
  border: 1px solid rgba(22, 163, 122, 0.25);
}
.status-warning {
  background: rgba(244, 166, 30, 0.12);
  color: #d97706;
  border: 1px solid rgba(244, 166, 30, 0.25);
}
:root[data-theme="dark"] .status-warning {
  color: #f59e0b;
}
.status-muted {
  background: rgba(148, 163, 184, 0.12);
  color: var(--color-text-muted);
  border: 1px solid var(--color-border);
}

/* اسپینر بارگذاری مدرن */
@keyframes docsSpin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.docs-spinner {
  animation: docsSpin 0.9s linear infinite;
  color: var(--color-primary);
}

/* ==========================================================================
   طراحی مدرن بلاک کد با هایلایت رنگی دقیق (One Dark Pro / Next.js Code Blocks)
   ========================================================================== */
.markdown-body code {
  font-family: 'JetBrains Mono', 'Rooyin', 'Vazirmatn', Consolas, Monaco, monospace;
  font-size: 13px;
  padding: 2.5px 6.5px;
  border-radius: 6px;
  background: var(--code-bg);
  color: var(--color-primary);
  border: 1px solid var(--color-border-subtle);
  display: inline-block;
  unicode-bidi: isolate;
  font-feature-settings: "liga" 1, "calt" 1;
  letter-spacing: 0 !important;
  word-spacing: normal !important;
}

.markdown-body code.has-persian,
.markdown-body code .font-rooyin {
  font-family: 'Rooyin', 'Vazirmatn', sans-serif !important;
  font-feature-settings: "liga" 1, "calt" 1;
  letter-spacing: 0 !important;
  word-spacing: normal !important;
}

.code-block-wrapper {
  position: relative;
  margin: 26px 0;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: #0d1117;
  box-shadow: 0 10px 30px -6px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.04);
}
:root[data-theme="light"] .code-block-wrapper {
  border: 1px solid #e2e8f0;
  background: #141b24;
  box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.14);
}

.code-block-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  background: #090d13;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  direction: ltr;
}

/* دکمه‌های ۳ رنگ پنجره مک (Mac Dots) */
.code-mac-dots {
  display: flex;
  align-items: center;
  gap: 7px;
}
.mac-dot {
  width: 10.5px;
  height: 10.5px;
  border-radius: 50%;
  display: inline-block;
}
.dot-red    { background: #ff5f56; box-shadow: 0 0 6px rgba(255,95,86,0.35); }
.dot-yellow { background: #ffbd2e; box-shadow: 0 0 6px rgba(255,189,46,0.35); }
.dot-green  { background: #27c93f; box-shadow: 0 0 6px rgba(39,201,63,0.35); }

.code-lang-pill {
  font-size: 11px;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  color: #8b949e;
  background: rgba(255, 255, 255, 0.06);
  padding: 2px 8px;
  border-radius: 6px;
  letter-spacing: 0.5px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.code-copy-btn {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 7px;
  color: #c9d1d9;
  font-size: 11.5px;
  font-family: 'Rooyin', 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
  padding: 4px 10px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s var(--ease);
  font-feature-settings: "liga" 1, "calt" 1;
  letter-spacing: 0 !important;
  word-spacing: normal !important;
}
.code-copy-btn:hover {
  background: rgba(255, 255, 255, 0.14);
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.25);
}
.code-copy-btn span {
  font-family: 'Rooyin', 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
  font-feature-settings: "liga" 1, "calt" 1;
  letter-spacing: 0 !important;
}

.markdown-body pre {
  margin: 0;
  padding: 18px 20px;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  direction: ltr;
  text-align: left;
  background: transparent !important;
}

.markdown-body pre code {
  font-family: 'JetBrains Mono', 'Rooyin', 'Vazirmatn', 'Fira Code', Consolas, Monaco, monospace !important;
  font-size: 13.5px;
  line-height: 1.7;
  color: #e6edf3;
  background: transparent !important;
  border: none !important;
  padding: 0 !important;
  tab-size: 2;
  font-feature-settings: "liga" 1, "calt" 1;
  letter-spacing: 0;
  word-spacing: normal;
}

.markdown-body pre code .font-rooyin,
.markdown-body pre code .hljs-string.font-rooyin,
.markdown-body pre code .hljs-comment.font-rooyin {
  font-family: 'Rooyin', 'Vazirmatn', sans-serif !important;
  font-feature-settings: "liga" 1, "calt" 1;
  letter-spacing: 0 !important;
  word-spacing: normal !important;
}

/* رنگ‌بندی تفکیک‌شده و استاندارد سینتکس کد (One Dark Pro) */
.hljs-keyword, 
.hljs-selector-tag, 
.hljs-built_in, 
.hljs-name, 
.hljs-tag {
  color: #ff7b72 !important;
  font-weight: 600;
}
.hljs-function .hljs-title, 
.hljs-title.function_, 
.hljs-title.class_ {
  color: #d2a8ff !important;
  font-weight: 600;
}
.hljs-variable, 
.hljs-template-variable, 
.hljs-property {
  color: #79c0ff !important;
}
.hljs-string, 
.hljs-attr {
  color: #7ee787 !important;
}
.hljs-number, 
.hljs-literal {
  color: #ffab70 !important;
}
.hljs-comment, 
.hljs-quote {
  color: #8b949e !important;
  font-style: italic;
}
.hljs-type, 
.hljs-params {
  color: #ffa657 !important;
}
.hljs-symbol, 
.hljs-bullet, 
.hljs-meta {
  color: #56d364 !important;
}
.hljs-operator, 
.hljs-punctuation {
  color: #c9d1d9 !important;
}

.markdown-body kbd {
  font-family: 'JetBrains Mono', monospace;
  background: var(--kbd-bg);
  border: 1px solid var(--color-border);
  border-bottom: 2px solid var(--color-border);
  border-radius: 4px;
  padding: 2px 6px;
  font-size: 12px;
}

/* ==========================================================================
   طراحی مدرن کارت‌های فلوچارت و نمودار گرافیکی (Mermaid Diagram Cards)
   ========================================================================== */
.mermaid-diagram-card {
  margin: 28px 0;
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border);
  background: var(--color-surface);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  transition: border-color 0.2s, box-shadow 0.2s;
}
:root[data-theme="dark"] .mermaid-diagram-card {
  border-color: #334155;
  background: #141b26;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}
.mermaid-diagram-card:hover {
  box-shadow: var(--shadow-md);
  border-color: rgba(0, 123, 122, 0.4);
}
:root[data-theme="dark"] .mermaid-diagram-card:hover {
  border-color: rgba(79, 178, 176, 0.5);
}

.mermaid-diagram-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  background: rgba(0, 123, 122, 0.05);
  border-bottom: 1px solid var(--color-border-subtle);
}
:root[data-theme="dark"] .mermaid-diagram-header {
  background: rgba(79, 178, 176, 0.08);
  border-bottom-color: rgba(255, 255, 255, 0.07);
}

.mermaid-diagram-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  font-weight: 700;
  color: var(--color-primary);
  font-family: 'Rooyin', 'Vazirmatn', sans-serif;
}

.mermaid-diagram-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.mermaid-action-btn {
  background: transparent;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  color: var(--color-text-muted);
  font-size: 11px;
  font-family: 'Rooyin', 'Vazirmatn', sans-serif !important;
  padding: 3px 9px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: all 0.2s;
}
.mermaid-action-btn:hover,
.mermaid-action-btn.active {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background: var(--color-surface);
}

.mermaid-canvas-area {
  padding: 26px 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  background: var(--color-surface);
  min-height: 100px;
}
:root[data-theme="dark"] .mermaid-canvas-area {
  background: #0d1117;
}

.mermaid-render-container {
  width: 100%;
  display: flex;
  justify-content: center;
}
.mermaid-render-container svg {
  max-width: 100% !important;
  height: auto !important;
  font-family: 'Vazirmatn', 'Rooyin', sans-serif !important;
}

.mermaid-render-container .node .label,
.mermaid-render-container .label text,
.mermaid-render-container text {
  font-family: 'Rooyin', 'Vazirmatn', -apple-system, sans-serif !important;
  font-feature-settings: "liga" 1, "calt" 1;
  letter-spacing: 0 !important;
}

.mermaid-source-wrapper {
  border-top: 1px solid var(--color-border);
  background: #090d13;
  padding: 14px 18px;
  display: none;
}
.mermaid-source-wrapper pre {
  margin: 0;
  padding: 0;
  background: transparent !important;
  font-family: 'JetBrains Mono', 'Rooyin', monospace;
  font-size: 12.5px;
  color: #79c0ff;
  line-height: 1.6;
  direction: ltr;
  text-align: left;
}

/* ناوبری بعدی / قبلی */
.article-pagination {
  margin-top: 48px;
  padding-top: 28px;
  border-top: 1px solid var(--color-border);
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.pagination-card {
  display: flex;
  flex-direction: column;
  padding: 16px 18px;
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
  font-size: 11.5px;
  color: var(--color-text-muted);
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 4px;
}
.pagination-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--color-primary);
}

/* ستون فهرست مطالب صفحه (On this page TOC) */
.docs-toc-sidebar {
  width: var(--toc-width);
  flex-shrink: 0;
  height: calc(100vh - var(--header-height));
  position: sticky;
  top: var(--header-height);
  overflow-y: auto;
  padding: 28px 16px 40px;
  display: flex;
  flex-direction: column;
  gap: 12px;
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
  gap: 5px;
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
  margin-top: 16px;
  font-size: 12px;
  color: var(--color-text-muted);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: none;
  background: none;
  font-family: inherit;
}
.back-to-top-btn:hover {
  color: var(--color-primary);
}

/* ==========================================================================
   مودال جستجو (Ctrl+K)
   ========================================================================== */
.search-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.7);
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
  width: 92%;
  max-width: 620px;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.search-input-wrap {
  display: flex;
  align-items: center;
  padding: 14px 18px;
  border-bottom: 1px solid var(--color-border);
  gap: 12px;
}
.search-input-field {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  font-family: inherit;
  font-size: 15px;
  color: var(--color-text);
}
.search-results-list {
  max-height: 400px;
  overflow-y: auto;
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.search-result-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 9px 12px;
  border-radius: var(--radius-sm);
  text-decoration: none;
  color: var(--color-text);
}
.search-result-item:hover, .search-result-item.selected {
  background: var(--primary-08);
  color: var(--color-primary);
}
.search-result-title {
  font-weight: 700;
  font-size: 13.5px;
}
.search-result-sub {
  font-size: 12px;
  color: var(--color-text-muted);
}
.search-footer {
  padding: 8px 18px;
  border-top: 1px solid var(--color-border);
  background: var(--color-bg);
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 11px;
  color: var(--color-text-muted);
}

/* ==========================================================================
   طراحی فوق‌العاده واکنش‌گرا (Responsive Rules)
   ========================================================================== */
/* ۱. تبلت‌ها و صفحات زیر 1150px: ستون TOC سمت چپ جمع می‌شود و بالای محتوا می‌آید */
@media (max-width: 1150px) {
  .docs-toc-sidebar { display: none; }
  .mobile-inline-toc { display: block; }
  .docs-main-container { padding: 24px 28px 50px; }
}

/* ۲. صفحات زیر 860px (موبایل و تبلت عمودی): سایدبار راست تبدیل به دراور متحرک می‌شود */
@media (max-width: 860px) {
  .mobile-menu-btn { display: inline-flex; }
  .sidebar-header-mobile { display: flex; }
  
  .docs-nav-sidebar {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 90;
    width: min(320px, 86vw);
    height: 100vh;
    transform: translateX(100%);
    transition: transform 0.28s var(--ease);
    box-shadow: -10px 0 30px rgba(0,0,0,0.25);
    background: var(--color-surface);
  }
  .docs-nav-sidebar.open {
    transform: translateX(0);
  }

  .search-trigger-btn {
    width: 38px;
    padding: 0;
    justify-content: center;
    border-radius: 50%;
  }
  .search-trigger-btn span, .search-trigger-btn kbd {
    display: none;
  }

  .docs-main-container {
    padding: 18px 16px 40px;
  }
}

/* ۳. گوشی‌های با صفحه کوچک‌تر (زیر 520px) */
@media (max-width: 520px) {
  .version-badge { display: none; }
  .brand-sub { display: none; }
  .brand-title { font-size: 14.5px; }
  
  .article-pagination {
    grid-template-columns: 1fr;
  }
  .pagination-card.next {
    align-items: flex-start;
    text-align: right;
  }

  .article-tools {
    width: 100%;
    justify-content: flex-end;
  }

  .header-btn-primary span {
    display: none;
  }
  .header-btn-primary {
    width: 38px;
    padding: 0;
    justify-content: center;
    border-radius: 50%;
  }
}
</style>
</head>
<body>

<!-- هدر اصلی -->
<header class="docs-header">
  <div class="header-right">
    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="منوی سرفصل‌ها" title="فهرست مستندات">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
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
    <button class="search-trigger-btn" id="searchTriggerBtn" title="جستجو در مستندات">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <span>جستجو در مستندات...</span>
      <kbd>Ctrl K</kbd>
    </button>
  </div>

  <div class="header-left">
    <!-- کلید تغییر حالت شب/روز -->
    <button class="header-btn header-btn-icon" id="themeToggleBtn" title="تغییر حالت شب/روز">
      <svg id="themeIconSun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      <svg id="themeIconMoon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    </button>

    <!-- بازگشت به پیشخوان مدیریت -->
    <a href="index.php" class="header-btn header-btn-primary" target="_top" title="بازگشت به پیشخوان مدیریت">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span>بازگشت به پیشخوان</span>
    </a>
  </div>
</header>

<!-- ساختار اصلی ۳ ستونه -->
<div class="docs-layout">

  <!-- پوشش پشت دراور برای بستن با کلیک بیرون -->
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- ستون راست: سایدبار ناوبری (Nav Drawer) -->
  <aside class="docs-nav-sidebar" id="docsSidebar">
    <div class="sidebar-header-mobile">
      <span style="font-weight:800;font-size:14px;color:var(--color-primary)">فهرست مستندات مکسا</span>
      <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="بستن منو">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="sidebar-filter-wrap">
      <input type="text" class="sidebar-filter-input" id="sidebarFilter" placeholder="فیلتر سرفصل‌ها..." autocomplete="off">
      <svg class="sidebar-filter-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    </div>

    <!-- گروه اول: راهنمای کاربران (User Manual) -->
    <div>
      <div class="nav-group-title">
        <span style="display:inline-flex;align-items:center;gap:7px">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
          </svg>
          <span>راهنمای کاربران (User Manual)</span>
        </span>
        <span style="font-size:10px;opacity:0.7">۲۲ بخش</span>
      </div>
      <ul class="nav-group-list" id="userManualNavList"></ul>
    </div>

    <!-- گروه دوم: مستندات فنی و مهندسی (Developer Docs) -->
    <div>
      <div class="nav-group-title">
        <span style="display:inline-flex;align-items:center;gap:7px">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="16 18 22 12 16 6"/>
            <polyline points="8 6 2 12 8 18"/>
          </svg>
          <span>مستندات فنی (Engineering)</span>
        </span>
      </div>
      <ul class="nav-group-list" id="devDocsNavList"></ul>
    </div>
  </aside>

  <!-- ستون وسط: محتوای سند (Article) -->
  <main class="docs-main-container">
    <div class="docs-article-wrapper">
      
      <!-- متادیتا و ابزارها -->
      <div class="article-header-meta">
        <div class="article-breadcrumbs" id="breadcrumbs">
          <a href="docs.php">مستندات مکسا</a>
          <span class="sep">/</span>
          <span id="breadcrumbCategory">راهنمای کاربران</span>
          <span class="sep">/</span>
          <span id="breadcrumbCurrent" style="color:var(--color-primary);font-weight:700">مقدمه</span>
        </div>

        <div class="article-tools">
          <span class="tool-pill font-rooyin" id="readingTimePill" title="تخمین زمان مطالعه">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span id="readingTimeText" class="font-rooyin">۳ دقیقه مطالعه</span>
          </span>
          <button class="tool-pill font-rooyin" id="copyPageLinkBtn" type="button" title="کپی پیوند این سند">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            <span class="font-rooyin">کپی پیوند</span>
          </button>
        </div>
      </div>

      <!-- جعبه آکاردئونی فهرست مطالب درون مقاله برای موبایل و تبلت -->
      <div class="mobile-inline-toc" id="mobileInlineToc">
        <div class="mobile-inline-toc-header" id="mobileInlineTocHeader">
          <span style="display:flex;align-items:center;gap:8px">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            <span>فهرست سرفصل‌های این بخش</span>
          </span>
          <svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="mobile-inline-toc-body" id="mobileInlineTocBody">
          <ul class="toc-list" id="mobileTocList"></ul>
        </div>
      </div>

      <!-- بدنه اصلی مارک‌داون -->
      <article class="markdown-body" id="markdownContent"></article>

      <!-- ناوبری صفحه قبلی و بعدی -->
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

  <!-- ستون چپ: فهرست مطالب این صفحه در دسکتاپ (TOC) -->
  <aside class="docs-toc-sidebar">
    <div class="toc-title">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      <span>در این صفحه</span>
    </div>
    <ul class="toc-list" id="tocList"></ul>
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
      <input type="text" class="search-input-field" id="searchModalInput" placeholder="جستجو در تمام سرفصل‌ها و آموزش‌ها..." autocomplete="off">
      <kbd>ESC</kbd>
    </div>
    <div class="search-results-list" id="searchResultsList"></div>
    <div class="search-footer">
      <span>پیمایش: ↑ و ↓</span>
      <span>انتخاب: Enter</span>
      <span>بستن: ESC</span>
    </div>
  </div>
</div>

<!-- متن سند اولیه برای لود صفر تأخیر -->
<script type="text/markdown" id="rawInitialContent"><?= htmlspecialchars($initialContent, ENT_QUOTES, 'UTF-8') ?></script>

<script>
/* ==========================================================================
   ابزار ذخیره‌سازی امن (مقاوم در برابر مسدودکننده‌های کوکی و Tracking Prevention)
   ========================================================================== */
const safeStorage = {
  get: function(key, fallback) {
    try {
      const val = localStorage.getItem(key);
      return val !== null ? val : fallback;
    } catch(e) {
      return fallback;
    }
  },
  set: function(key, value) {
    try {
      localStorage.setItem(key, value);
    } catch(e) {}
  }
};

/* ==========================================================================
   کاتالوگ جامع مستندات مکسا
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
   مدیریت تم با همگام‌سازی کامل
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
  const isDark = safeStorage.get('maxa-theme', 'light') === 'dark';
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
  safeStorage.set('maxa-theme', newTheme);
  updateThemeIcons(newTheme === 'dark');
  renderAllMermaidDiagrams();
});

window.addEventListener('storage', (e) => {
  if (!e || e.key === 'maxa-theme' || e.key === null) {
    initTheme();
  }
});

/* ==========================================================================
   رندر منوی کناری (Sidebar Navigation)
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
      closeMobileSidebar();
    });

    if (doc.group === 'manual') {
      manualList.appendChild(li);
    } else {
      devList.appendChild(li);
    }
  });
}

document.getElementById('sidebarFilter').addEventListener('input', (e) => {
  const q = e.target.value.trim().toLowerCase();
  document.querySelectorAll('.nav-item-link').forEach(a => {
    const text = a.textContent.toLowerCase();
    const li = a.closest('li');
    li.style.display = (!q || text.includes(q)) ? '' : 'none';
  });
});

/* مدیریت سایدبار در موبایل و تبلت */
const docsSidebar = document.getElementById('docsSidebar');
const sidebarBackdrop = document.getElementById('sidebarBackdrop');
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

function openMobileSidebar() {
  docsSidebar.classList.add('open');
  sidebarBackdrop.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeMobileSidebar() {
  docsSidebar.classList.remove('open');
  sidebarBackdrop.classList.remove('open');
  document.body.style.overflow = '';
}

mobileMenuBtn.addEventListener('click', openMobileSidebar);
sidebarCloseBtn.addEventListener('click', closeMobileSidebar);
sidebarBackdrop.addEventListener('click', closeMobileSidebar);

/* آکاردئون فهرست درون صفحه موبایل */
const mobileInlineToc = document.getElementById('mobileInlineToc');
const mobileInlineTocHeader = document.getElementById('mobileInlineTocHeader');
mobileInlineTocHeader.addEventListener('click', () => {
  mobileInlineToc.classList.toggle('open');
});

/* ==========================================================================
   تشخیص و حل هوشمند مسیر فایل‌های پیوند (Path Resolver)
   ========================================================================== */
function resolveDocPath(currentDoc, relativeHref) {
  if (!relativeHref) return { doc: currentDoc || 'user-manual/README.md', hash: '' };

  const parts = relativeHref.split('#');
  let targetPath = parts[0].replace(/\\/g, '/').trim();
  const targetHash = parts[1] || '';

  if (!targetPath) {
    return { doc: currentDoc || 'user-manual/README.md', hash: targetHash };
  }

  // حذف اسلش اول در صورت وجود
  targetPath = targetPath.replace(/^\/+/, '');

  // حذف پیشوند docs/ در صورتی که در مسیر آمده باشد
  if (targetPath.startsWith('docs/')) {
    targetPath = targetPath.substring(5);
  }

  const docContext = currentDoc || 'user-manual/README.md';
  const lastSlash = docContext.lastIndexOf('/');
  const currentDir = lastSlash !== -1 ? docContext.substring(0, lastSlash) : '';

  let fullPath = '';
  if (targetPath.startsWith('./') || targetPath.startsWith('../') || !targetPath.includes('/')) {
    fullPath = currentDir ? (currentDir + '/' + targetPath) : targetPath;
  } else {
    fullPath = targetPath;
  }

  // حل و استانداردسازی سگمنت‌های نقطه و دو‌نقطه (Canonicalize)
  const segments = fullPath.split('/');
  const resolved = [];
  for (const seg of segments) {
    if (!seg || seg === '.') continue;
    if (seg === '..') {
      if (resolved.length > 0) resolved.pop();
    } else {
      resolved.push(seg);
    }
  }

  let finalDoc = resolved.join('/');
  if (finalDoc.startsWith('docs/')) {
    finalDoc = finalDoc.substring(5);
  }

  return {
    doc: finalDoc,
    hash: targetHash
  };
}

/* ==========================================================================
   ابزار پالایش و رمزگشایی کدهای دیاگرام Mermaid (حذف قطعی انتیتی‌های HTML مانند &quot;)
   ========================================================================== */
const mermaidDiagramsMap = new Map();

function cleanMermaidCode(raw) {
  if (!raw) return '';
  let str = raw;

  // حذف قطعی هر نوع تگ احتمالی مانند span هایلایتر
  str = str.replace(/<span[^>]*>/gi, '').replace(/<\/span>/gi, '');

  try {
    const txt = document.createElement('textarea');
    txt.innerHTML = str;
    str = txt.value;
    if (/&(?:quot|amp|lt|gt|#39|#039|apos);/i.test(str)) {
      txt.innerHTML = str;
      str = txt.value;
    }
  } catch(e) {}

  str = str.replace(/&quot;/g, '"')
           .replace(/&#039;/g, "'")
           .replace(/&apos;/g, "'")
           .replace(/&lt;/g, '<')
           .replace(/&gt;/g, '>')
           .replace(/&amp;/g, '&');

  return str.replace(/\r\n/g, '\n').replace(/\r/g, '\n').trim();
}

/* ==========================================================================
   پردازش و شخصی‌سازی مارک‌داون
   ========================================================================== */
function processCustomMarkdownElements(html, currentDocId) {
  const activeDoc = currentDocId || activeDocId || 'user-manual/README.md';
  const alertTypes = {
    'NOTE':      { cls: 'callout-note',      title: 'یادداشت',         icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>' },
    'TIP':       { cls: 'callout-tip',       title: 'نکته کاربردی',     icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>' },
    'IMPORTANT': { cls: 'callout-important', title: 'مهم',             icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' },
    'WARNING':   { cls: 'callout-warning',   title: 'هشدار و توجه',    icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>' },
    'CAUTION':   { cls: 'callout-caution',   title: 'احتیاط و امنیت',   icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' }
  };

  const container = document.createElement('div');
  container.innerHTML = html;

  // ۱. تبدیل Calloutهای گیت‌هاب
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

  // ۲. بسته‌بندی جداول
  container.querySelectorAll('table').forEach(tbl => {
    if (!tbl.parentElement.classList.contains('table-container')) {
      const wrap = document.createElement('div');
      wrap.className = 'table-container';
      tbl.parentNode.insertBefore(wrap, tbl);
      wrap.appendChild(tbl);
    }
  });

  // ۳. هدر و دکمه کپی کد و رندر فلوچارت‌های Mermaid
  container.querySelectorAll('pre').forEach((pre, pIdx) => {
    const code = pre.querySelector('code');
    let lang = 'CODE';
    if (code) {
      const cls = code.className || '';
      const match = cls.match(/language-([a-zA-Z0-9_-]+)/);
      if (match) lang = match[1].toUpperCase();
    }

    // اگر بلاک دیاگرام و فلوچارت گرافیکی Mermaid باشد:
    if (lang === 'MERMAID') {
      const rawDiagramCode = cleanMermaidCode(code ? (code.innerText || code.textContent) : pre.textContent);
      const diagramId = 'mermaid-chart-' + pIdx + '-' + Math.random().toString(36).substring(2, 8);
      mermaidDiagramsMap.set(diagramId, rawDiagramCode);

      const diagramCard = document.createElement('div');
      diagramCard.className = 'mermaid-diagram-card';
      diagramCard.setAttribute('data-diagram-id', diagramId);
      const safeEscaped = rawDiagramCode.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
      diagramCard.innerHTML = `
        <div class="mermaid-diagram-header">
          <div class="mermaid-diagram-title">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="7" height="7"></rect>
              <rect x="14" y="3" width="7" height="7"></rect>
              <rect x="14" y="14" width="7" height="7"></rect>
              <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>فلوچارت فرآیند (دیاگرام بصری)</span>
          </div>
          <div class="mermaid-diagram-actions">
            <button class="mermaid-action-btn mermaid-toggle-src" type="button" title="مشاهده کد متنی این دیاگرام">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
              <span>کد منبع</span>
            </button>
          </div>
        </div>
        <div class="mermaid-canvas-area">
          <div class="mermaid-render-container" id="${diagramId}">
            <div style="display:flex;align-items:center;gap:8px;color:var(--color-text-muted);font-size:12px;padding:20px;">
              <svg class="docs-spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
              <span>در حال ترسیم نمودار گرافیکی...</span>
            </div>
          </div>
        </div>
        <div class="mermaid-source-wrapper" style="display:none">
          <pre><code class="mermaid-code-text">${safeEscaped}</code></pre>
        </div>
      `;
      pre.parentNode.insertBefore(diagramCard, pre);
      pre.remove();
      return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'code-block-wrapper';
    wrapper.innerHTML = `
      <div class="code-block-header">
        <div class="code-mac-dots">
          <span class="mac-dot dot-red"></span>
          <span class="mac-dot dot-yellow"></span>
          <span class="mac-dot dot-green"></span>
        </div>
        <span class="code-lang-pill">${lang}</span>
        <button class="code-copy-btn font-rooyin" type="button" title="کپی کد">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
          <span class="font-rooyin">کپی کد</span>
        </button>
      </div>
    `;
    pre.parentNode.insertBefore(wrapper, pre);
    wrapper.appendChild(pre);
  });

  // ۴. افزودن شناسه و لنگر به سرتیترهای H2 و H3
  container.querySelectorAll('h2, h3').forEach((heading, idx) => {
    const rawText = heading.textContent.trim();
    let slug = heading.id || ('sec-' + idx + '-' + rawText.toLowerCase().replace(/[^a-zA-Z0-9\u0600-\u06FF]+/g, '-').replace(/^-+|-+$/g, ''));
    heading.id = slug;

    const anchor = document.createElement('a');
    anchor.className = 'header-anchor';
    anchor.href = '#' + slug;
    anchor.innerHTML = '#';
    anchor.title = 'پیوند به این بخش';
    heading.appendChild(anchor);
  });

  // ۵. اصلاح و استانداردسازی تمام لینک‌ها (داخلی، مارک‌داون و خارجی)
  container.querySelectorAll('a[href]').forEach(a => {
    const rawHref = a.getAttribute('href');
    if (!rawHref) return;

    // الف) لینک‌های خارجی
    if (/^(https?:|\/\/|mailto:|tel:)/i.test(rawHref)) {
      a.setAttribute('target', '_blank');
      a.setAttribute('rel', 'noopener noreferrer');
      return;
    }

    // ب) لنگرهای درون‌صفحه‌ای (#heading)
    if (rawHref.startsWith('#')) {
      a.setAttribute('data-anchor-link', 'true');
      return;
    }

    // ج) لینک‌های اسناد مارک‌داون (.md)
    if (/\.md($|#)/i.test(rawHref)) {
      const resolved = resolveDocPath(activeDoc, rawHref);
      const safeHref = `docs.php?doc=${encodeURIComponent(resolved.doc)}${resolved.hash ? '#' + resolved.hash : ''}`;
      a.setAttribute('href', safeHref);
      a.setAttribute('data-doc-link', 'true');
      a.setAttribute('data-target-doc', resolved.doc);
      if (resolved.hash) {
        a.setAttribute('data-target-hash', resolved.hash);
      }
    }
  });

  // ۶. اعمال قلم «رویین» و اتصال روان حروف برای قطعه‌کدهای فارسی درون‌خطی (Inline Codes)
  container.querySelectorAll('code').forEach(c => {
    if (c.closest('pre')) return;
    const text = c.textContent;
    if (/[\u0600-\u06FF]/.test(text)) {
      c.classList.add('has-persian');
      c.innerHTML = c.innerHTML.replace(/([\u0600-\u06FF\u200C][\u0600-\u06FF\u200C\s]*[\u0600-\u06FF\u200C]|[\u0600-\u06FF\u200C])/g, '<span class="font-rooyin">$1</span>');
    }
  });

  // ۷. تبدیل تگ‌های متنی وضعیت به برچسب‌های برداری مدرن با آیکون‌های Iconly Pro
  let rawHtml = container.innerHTML;

  rawHtml = rawHtml.replace(/\[(فعال|تأیید)\]/g, 
    '<span class="status-badge status-active"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>$1</span></span>');

  rawHtml = rawHtml.replace(/\[(آزمایشی|در حال توسعه)\]/g, 
    '<span class="status-badge status-warning"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>$1</span></span>');

  rawHtml = rawHtml.replace(/\[(برنامه‌ریزی‌شده|عدم دسترسی)\]/g, 
    '<span class="status-badge status-muted"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="15" y1="9" x2="9" y2="15"/></svg><span>$1</span></span>');

  // ۷. حذف قطعی و تضمینی هرگونه ایموجی یونیکد باقی‌مانده از محتوا
  rawHtml = rawHtml.replace(/[\u{1F000}-\u{1FFFF}\u{2600}-\u{27BF}\u{2300}-\u{23FF}\u{2B50}\u{200D}\u{FE0F}]/gu, '');

  return rawHtml;
}

/* ==========================================================================
   ایجاد فهرست محتوا (TOC) برای دسکتاپ و موبایل
   ========================================================================== */
function buildTableOfContents() {
  const tocList = document.getElementById('tocList');
  const mobileTocList = document.getElementById('mobileTocList');
  tocList.innerHTML = '';
  mobileTocList.innerHTML = '';

  const headings = document.querySelectorAll('#markdownContent h2, #markdownContent h3');
  if (!headings.length) {
    tocList.innerHTML = '<li style="font-size:12px;color:var(--color-text-muted)">سرفصل فرعی در این سند ثبت نشده است.</li>';
    mobileTocList.innerHTML = '<li style="font-size:12px;color:var(--color-text-muted)">سرفصل فرعی در این سند ثبت نشده است.</li>';
    return;
  }

  headings.forEach(h => {
    const isH3 = h.tagName.toLowerCase() === 'h3';
    const text = h.childNodes[0] ? h.childNodes[0].textContent.trim() : h.textContent.trim();
    const id = h.id;

    // آیتم دسکتاپ
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

    // آیتم موبایل
    const mLi = document.createElement('li');
    mLi.innerHTML = `
      <a href="#${id}" class="toc-item-link ${isH3 ? 'toc-h3' : ''}" data-target="${id}">
        ${text}
      </a>
    `;
    mLi.querySelector('a').addEventListener('click', (e) => {
      e.preventDefault();
      mobileInlineToc.classList.remove('open');
      const targetEl = document.getElementById(id);
      if (targetEl) {
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        history.replaceState(null, null, '#' + id);
      }
    });
    mobileTocList.appendChild(mLi);
  });

  setupScrollSpy();
}

/* Scroll-Spy برای هایلایت سرفصل در حال مطالعه */
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

document.getElementById('backToTopBtn').addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

/* ==========================================================================
   به‌روزرسانی متادیتا و کارت‌های قبلی / بعدی
   ========================================================================== */
function updateDocMetadata(docId, rawMarkdown) {
  const meta = DOCS_CATALOG.find(d => d.id === docId) || {
    title: 'سند راهنما',
    group: 'manual'
  };

  document.title = meta.title + ' | مستندات سامانه مکسا';

  document.getElementById('breadcrumbCategory').textContent = 
    meta.group === 'manual' ? 'راهنمای کاربران' : 'مستندات مهندسی';
  document.getElementById('breadcrumbCurrent').textContent = meta.title;

  const wordCount = rawMarkdown.replace(/[`#*_\-[\]()]/g, ' ').trim().split(/\s+/).length;
  const minutes = Math.max(1, Math.ceil(wordCount / 180));
  document.getElementById('readingTimeText').textContent = minutes + ' دقیقه مطالعه';

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
   لود و رندر کلاینت (با کش حافظه و فال‌بک مطمئن)
   ========================================================================== */
const docCache = {};

/* موتور هایلایت رنگی دقیق (متغیرها، کلیدواژه‌ها، رشته‌ها، اعداد و توابع) */
function enrichCodeSyntaxHighlighting() {
  // فقط بلاک‌های کد واقعی را پردازش کن (نه کدهای داخل دیاگرام Mermaid)
  document.querySelectorAll('#markdownContent .code-block-wrapper pre code').forEach(block => {
    // اگر هیچ برچسب توکن رنگی وجود نداشت یا فقط متن خام بود:
    if (!block.querySelector('.hljs-keyword, .hljs-string, .hljs-variable, .hljs-number, .hljs-title')) {
      let codeText = block.innerHTML;

      // ۱. کامنت‌ها (سبز مایل به خاکستری ایتالیک)
      codeText = codeText.replace(/(\/\/[^\n<]*|#[^\n<]*|\/\*[\s\S]*?\*\/)/g, '<span class="hljs-comment">$1</span>');

      // ۲. رشته‌ها (سبز روشن نعنایی)
      codeText = codeText.replace(/(&quot;[^<]*?&quot;|&#039;[^<]*?&#039;|"[^"\n<]*"|'[^'\n<]*')/g, '<span class="hljs-string">$1</span>');

      // ۳. متغیرها و پارامترها (آبی فیروزه‌ای خوشرنگ مانند $db و --option)
      codeText = codeText.replace(/(\$[a-zA-Z0-9_]+|--[a-zA-Z0-9_-]+)/g, '<span class="hljs-variable">$1</span>');

      // ۴. کلمات کلیدی زبان‌های برنامه‌نویسی و وب (قرمز/مرجانی شیک)
      codeText = codeText.replace(/\b(function|class|return|if|else|foreach|while|for|const|let|var|public|private|protected|static|new|try|catch|throw|echo|print|require|include|require_once|include_once|SELECT|FROM|WHERE|INSERT|INTO|UPDATE|SET|DELETE|JOIN|LEFT|RIGHT|INNER|GROUP BY|ORDER BY|LIMIT|CREATE|TABLE|ALTER|DROP|AND|OR|NOT|IN|AS|docker|compose|git|bash|sudo|apt|curl|chmod|chown)\b/g, '<span class="hljs-keyword">$1</span>');

      // ۵. اعداد و مقادیر بولین (نارنجی طلایی)
      codeText = codeText.replace(/\b(\d+(\.\d+)?|true|false|null)\b/gi, '<span class="hljs-number">$1</span>');

      // ۶. نام توابع و فراخوانی‌ها (بنفش ملایم)
      codeText = codeText.replace(/\b([a-zA-Z0-9_]+)(?=\s*\()/g, '<span class="hljs-title function_">$1</span>');

      block.innerHTML = codeText;
    }

    // اعمال قلم رویین برای رشته‌ها و کامنت‌های حاوی کاراکترهای فارسی
    block.querySelectorAll('.hljs-string, .hljs-comment').forEach(token => {
      if (/[\u0600-\u06FF]/.test(token.textContent)) {
        token.classList.add('font-rooyin');
      }
    });
  });
}

/* ==========================================================================
   موتور رندر بلادرنگ فلوچارت‌ها و دیاگرام‌های گرافیکی (Mermaid Engine)
   ========================================================================== */
async function renderAllMermaidDiagrams() {
  if (!window.mermaid) return;
  const cards = document.querySelectorAll('.mermaid-diagram-card');
  if (!cards.length) return;

  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

  try {
    mermaid.initialize({
      startOnLoad: false,
      theme: isDark ? 'dark' : 'neutral',
      themeVariables: isDark ? {
        primaryColor: '#163836',
        primaryTextColor: '#f1f5f9',
        primaryBorderColor: '#4fb2b0',
        lineColor: '#4fb2b0',
        secondaryColor: '#1a2232',
        tertiaryColor: '#141b26',
        mainBkg: '#141b26',
        nodeBorder: '#334155',
        clusterBkg: '#0f1520',
        clusterBorder: '#334155',
        titleColor: '#f1f5f9',
        edgeLabelBackground: '#1e293b'
      } : {
        primaryColor: '#e6f7f6',
        primaryTextColor: '#005554',
        primaryBorderColor: '#007b7a',
        lineColor: '#007b7a',
        secondaryColor: '#f8fafc',
        tertiaryColor: '#ffffff',
        mainBkg: '#ffffff',
        nodeBorder: '#cbd5e1',
        clusterBkg: '#f8fafc',
        clusterBorder: '#e2e8f0',
        titleColor: '#1e293b',
        edgeLabelBackground: '#ffffff'
      },
      fontFamily: 'Rooyin, Vazirmatn, -apple-system, sans-serif',
      fontSize: '13px',
      securityLevel: 'loose',
      flowchart: {
        useMaxWidth: true,
        htmlLabels: true,
        curve: 'basis'
      }
    });

    for (let i = 0; i < cards.length; i++) {
      const card = cards[i];
      const diagramId = card.getAttribute('data-diagram-id');
      let rawCode = (diagramId && mermaidDiagramsMap.has(diagramId)) ? mermaidDiagramsMap.get(diagramId) : '';
      if (!rawCode) {
        const srcCodeEl = card.querySelector('.mermaid-source-wrapper code');
        rawCode = srcCodeEl ? (srcCodeEl.innerText || srcCodeEl.textContent) : (card.getAttribute('data-diagram-raw') || '');
      }
      rawCode = cleanMermaidCode(rawCode);
      const renderEl = card.querySelector('.mermaid-render-container');
      if (!rawCode || !renderEl) continue;

      const renderId = 'mermaid-svg-' + i + '-' + Math.random().toString(36).substring(2, 7);
      try {
        const { svg } = await mermaid.render(renderId, rawCode);
        renderEl.innerHTML = svg;
        const svgEl = renderEl.querySelector('svg');
        if (svgEl) {
          svgEl.removeAttribute('height');
          svgEl.style.maxWidth = '100%';
          svgEl.style.height = 'auto';
        }
      } catch (err) {
        console.warn('Mermaid render issue for diagram', i, err);
        const errEl = document.getElementById(renderId) || document.getElementById('d' + renderId);
        if (errEl) errEl.remove();
        renderEl.innerHTML = `
          <div style="color:var(--color-text-muted);font-size:12px;padding:12px;text-align:center">
            امکان رسم این دیاگرام بصری وجود نداشت. برای مشاهده متن، کلید «کد منبع» را بزنید.
          </div>
        `;
      }
    }
  } catch (err) {
    console.warn('Mermaid engine error:', err);
  }
}

function renderMarkdownText(rawMarkdown, docId, targetHash = '') {
  let html = '';
  if (window.marked) {
    marked.setOptions({
      gfm: true,
      breaks: false,
      highlight: function(code, lang) {
        if (lang && lang.toLowerCase() === 'mermaid') {
          return code;
        }
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
    html = rawMarkdown
      .replace(/^# (.*$)/gim, '<h1>$1</h1>')
      .replace(/^## (.*$)/gim, '<h2>$1</h2>')
      .replace(/^### (.*$)/gim, '<h3>$1</h3>')
      .replace(/\n\n/g, '<p></p>');
  }

  activeDocId = docId;
  const processedHtml = processCustomMarkdownElements(html, docId);
  document.getElementById('markdownContent').innerHTML = processedHtml;

  if (window.hljs) {
    document.querySelectorAll('#markdownContent .code-block-wrapper pre code').forEach(block => {
      try { hljs.highlightElement(block); } catch(e){}
    });
  }

  // هایلایت پیشرفته و پرکنتراست سینتکس (متغیرها، کلیدواژه‌ها، رشته‌ها، اعداد)
  enrichCodeSyntaxHighlighting();

  // رندر خودکار تمام نمودارها و فلوچارت‌های گرافیکی با موتور Mermaid
  renderAllMermaidDiagrams();

  updateDocMetadata(docId, rawMarkdown);
  buildTableOfContents();

  document.querySelectorAll('.nav-item-link').forEach(a => {
    if (a.getAttribute('data-doc') === docId) {
      a.classList.add('active');
    } else {
      a.classList.remove('active');
    }
  });

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
  let cleanId = docId.replace(/\\/g, '/').replace(/^\/+/, '');
  if (cleanId.startsWith('docs/')) {
    cleanId = cleanId.substring(5);
  }

  if (docCache[cleanId]) {
    history.pushState({ docId: cleanId }, '', `docs.php?doc=${encodeURIComponent(cleanId)}${targetHash ? '#' + targetHash : ''}`);
    renderMarkdownText(docCache[cleanId], cleanId, targetHash);
    return;
  }

  document.getElementById('markdownContent').innerHTML = `
    <div style="text-align:center;padding:70px 20px;color:var(--color-text-muted)">
      <svg class="docs-spinner" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
      </svg>
      <p style="font-weight:700;margin-top:14px;font-size:14px">در حال بارگذاری سند...</p>
    </div>
  `;

  fetch(`docs.php?raw=1&file=${encodeURIComponent(cleanId)}`)
    .then(res => {
      if (!res.ok) throw new Error('وضعیت ' + res.status);
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
          <p>امکان بارگذاری پرونده <code>${cleanId}</code> میسر نشد.</p>
          <div style="margin-top:12px">
            <button class="tool-pill" onclick="loadDoc('${cleanId}', '${targetHash}')">تلاش مجدد</button>
          </div>
        </div>
      `;
    });
}

/* مدیریت سراسری و مطمئن کلیک‌ها (Event Delegation) برای پیوندها و کپی کد */
function setupMarkdownContentDelegation() {
  const mdContent = document.getElementById('markdownContent');
  if (!mdContent) return;

  mdContent.addEventListener('click', (e) => {
    // ۱. کلیک روی لینک‌های اسناد داخلی
    const docLink = e.target.closest('a[data-doc-link="true"]');
    if (docLink) {
      if (!e.ctrlKey && !e.metaKey && e.button === 0) {
        e.preventDefault();
        const targetDoc = docLink.getAttribute('data-target-doc');
        const targetHash = docLink.getAttribute('data-target-hash') || '';
        if (targetDoc) {
          loadDoc(targetDoc, targetHash);
        }
      }
      return;
    }

    // ۲. کلیک روی لنگرهای درون‌صفحه‌ای (#heading)
    const anchorLink = e.target.closest('a[data-anchor-link="true"], a.header-anchor');
    if (anchorLink) {
      const href = anchorLink.getAttribute('href') || '';
      const id = href.replace(/^#/, '');
      if (id) {
        e.preventDefault();
        const targetEl = document.getElementById(id);
        if (targetEl) {
          targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
          history.replaceState(null, null, '#' + id);
        }
      }
      return;
    }

    // ۳. کلیک روی دکمه‌های کپی کد
    const copyBtn = e.target.closest('.code-copy-btn');
    if (copyBtn) {
      e.preventDefault();
      const wrapper = copyBtn.closest('.code-block-wrapper');
      const code = wrapper ? (wrapper.querySelector('pre code') || wrapper.querySelector('pre')) : null;
      if (code) {
        const rawText = code.innerText || code.textContent;
        navigator.clipboard.writeText(rawText).then(() => {
          const prevHtml = copyBtn.innerHTML;
          copyBtn.innerHTML = `
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#2ea043"><polyline points="20 6 9 17 4 12"/></svg>
            <span class="font-rooyin" style="color:#2ea043">کپی شد!</span>
          `;
          setTimeout(() => { copyBtn.innerHTML = prevHtml; }, 2000);
        }).catch(() => {});
      }
      return;
    }

    // ۴. کلیک روی دکمه تغییر حالت سورس‌کد نمودار Mermaid
    const mermaidToggleBtn = e.target.closest('.mermaid-toggle-src');
    if (mermaidToggleBtn) {
      e.preventDefault();
      const card = mermaidToggleBtn.closest('.mermaid-diagram-card');
      if (card) {
        const srcWrap = card.querySelector('.mermaid-source-wrapper');
        if (srcWrap) {
          const isCurrentlyHidden = window.getComputedStyle(srcWrap).display === 'none';
          srcWrap.style.display = isCurrentlyHidden ? 'block' : 'none';
          mermaidToggleBtn.classList.toggle('active', isCurrentlyHidden);
          const txt = mermaidToggleBtn.querySelector('span');
          if (txt) txt.textContent = isCurrentlyHidden ? 'بستن کد' : 'کد منبع';
        }
      }
      return;
    }
  });
}

window.addEventListener('popstate', () => {
  const urlParams = new URLSearchParams(window.location.search);
  const docFromUrl = urlParams.get('doc') || 'user-manual/README.md';
  const hashFromUrl = window.location.hash.replace('#', '');
  if (docFromUrl !== activeDocId) {
    loadDoc(docFromUrl, hashFromUrl);
  }
});

document.getElementById('copyPageLinkBtn').addEventListener('click', () => {
  navigator.clipboard.writeText(window.location.href).then(() => {
    const btn = document.getElementById('copyPageLinkBtn');
    const prev = btn.innerHTML;
    btn.innerHTML = `
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#2ea043"><polyline points="20 6 9 17 4 12"/></svg>
      <span class="font-rooyin" style="color:#2ea043">پیوند کپی شد!</span>
    `;
    setTimeout(() => { btn.innerHTML = prev; }, 2000);
  });
});

/* ==========================================================================
   مودال جستجوی بلادرنگ (Ctrl+K)
   ========================================================================== */
const searchModal = document.getElementById('searchModal');
const searchTriggerBtn = document.getElementById('searchTriggerBtn');
const searchModalInput = document.getElementById('searchModalInput');
const searchResultsList = document.getElementById('searchResultsList');

function openSearchModal() {
  searchModal.classList.add('open');
  searchModalInput.value = '';
  renderSearchResults('');
  setTimeout(() => searchModalInput.focus(), 60);
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
      <div style="text-align:center;padding:28px;color:var(--color-text-muted);font-size:13px">
        موردی مطابق با «${query}» یافت نشد.
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
   راه‌اندازی اولیه
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  renderSidebarNav();
  setupMarkdownContentDelegation();

  const initialRaw = document.getElementById('rawInitialContent').textContent;
  docCache[activeDocId] = initialRaw;
  const hash = window.location.hash.replace('#', '');
  renderMarkdownText(initialRaw, activeDocId, hash);
});
</script>

</body>
</html>

<?php
declare(strict_types=1);
if (!defined('IN_SNAPSHOT')) {
    require_once __DIR__ . '/_guard.php';
    dash_require('events');
    dash_require_hq();
}
require_once __DIR__ . '/../event-lib.php';

try {
    $pdo = dash_pdo();
} catch (Throwable $e) {
    $pdo = null;
}
$id = (int)($_GET['id'] ?? 0);

$event = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'event_date' => '',
    'start_time' => '08:00:00',
    'end_time' => '18:00:00',
    'short_description' => '',
    'about' => '',
    'poster' => null,
    'schedule_pdf' => null,
    'registration_url' => '',
    'status' => 'draft',
    'banner_active' => 0,
    'banner_label' => 'رویداد پیش‌رو',
    'banner_cta' => 'مشاهده رویداد',
    'banner_link' => '',
    'banner_theme' => 'teal',
    'banner_dismissible' => 1,
    'banner_background' => '#007b7a',
    'banner_text_color' => '#ffffff',
    'banner_accent_color' => '#f4a61e',
    'updated_at' => date('Y-m-d H:i:s'),
];

$heroes = $people = $speakers = $partners = $news = [];

if ($id && $pdo) {
    $st = $pdo->prepare('SELECT * FROM events WHERE id = ?');
    $st->execute([$id]);
    $event = array_merge($event, $st->fetch() ?: []);

    $q = function (string $sql) use ($pdo, $id) {
        $s = $pdo->prepare($sql);
        $s->execute([$id]);
        return $s->fetchAll();
    };

    $heroes = $q('SELECT * FROM event_heroes WHERE event_id = ? ORDER BY sort_order, id');
    $people = $q('SELECT * FROM event_people WHERE event_id = ? ORDER BY sort_order, id');
    $speakers = $q('SELECT * FROM event_speakers WHERE event_id = ? ORDER BY sort_order, id');
    $partners = $q('SELECT * FROM event_partners WHERE event_id = ? ORDER BY sort_order, id');
    $news = $q('SELECT * FROM event_news WHERE event_id = ? ORDER BY id DESC');
}

[$jy, $jm, $jd] = $event['event_date']
    ? event_gregorian_to_jalali((int)substr($event['event_date'], 0, 4), (int)substr($event['event_date'], 5, 2), (int)substr($event['event_date'], 8, 2))
    : [1405, 7, 16];

$jalaliDate = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
$formattedJalaliDate = event_date_label($event['event_date'] ?: '2026-10-08');

// Format last updated Jalali
$updatedTimestamp = strtotime($event['updated_at'] ?? 'now');
[$uy, $um, $ud] = event_gregorian_to_jalali((int)date('Y', $updatedTimestamp), (int)date('m', $updatedTimestamp), (int)date('d', $updatedTimestamp));
$monthsFa = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
$formattedUpdated = $ud . ' ' . $monthsFa[$um - 1] . ' ' . $uy . ' · ' . date('H:i', $updatedTimestamp);

$PANEL_TITLE = $id ? 'فرم مدیریت رویداد · ویرایش' : 'فرم مدیریت رویداد · ایجاد رویداد جدید';
require __DIR__ . '/_panel_head.php';

function rowVal(array $row, string $key, string $default = ''): string {
    return event_h((string)($row[$key] ?? $default));
}
require_once __DIR__ . '/event-icons.php';
?>
<link rel="stylesheet" href="/font.css">
<link rel="stylesheet" href="/assets/events/datepicker.css">
<!-- Theme synchronizer for iframe / standalone -->
<script>
(function() {
  function applyMaxaTheme() {
    var isDark = false;
    try {
      var localVal = localStorage.getItem('maxa-theme');
      if (localVal !== null) {
        isDark = (localVal === 'dark');
      } else if (window.parent && window.parent !== window) {
        try {
          isDark = window.parent.document.documentElement.getAttribute('data-theme') === 'dark';
        } catch(e) {
          isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
      } else {
        isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      }
    } catch(e) {
      isDark = false;
    }
    if (isDark) {
      document.documentElement.setAttribute('data-theme', 'dark');
      if (document.body) document.body.setAttribute('data-theme', 'dark');
    } else {
      document.documentElement.removeAttribute('data-theme');
      if (document.body) document.body.removeAttribute('data-theme');
    }
  }

  applyMaxaTheme();
  window.addEventListener('storage', function(e) {
    if (!e || e.key === 'maxa-theme' || e.key === null) applyMaxaTheme();
  });

  if (window.parent && window.parent !== window) {
    try {
      const parentRoot = window.parent.document.documentElement;
      const obs = new MutationObserver(function() { applyMaxaTheme(); });
      obs.observe(parentRoot, { attributes: true, attributeFilter: ['data-theme'] });
    } catch(e) {}
  }
})();
</script>

<style>
/* ============================================================================
   Font: Self-Hosted Vazirmatn (Variable & Static Weights)
   Guaranteed 100% offline and Iran-network resilience.
   ============================================================================ */
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn[wght].woff2') format('woff2 supports variations'),
       url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations');
  font-weight: 100 900;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn-Regular.woff2') format('woff2');
  font-weight: 400;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn-Medium.woff2') format('woff2');
  font-weight: 500;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn-SemiBold.woff2') format('woff2');
  font-weight: 600;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn-Bold.woff2') format('woff2');
  font-weight: 700;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn-ExtraBold.woff2') format('woff2');
  font-weight: 800;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn-Black.woff2') format('woff2');
  font-weight: 900;
  font-style: normal;
  font-display: swap;
}

*, *::before, *::after {
  box-sizing: border-box;
  font-family: 'Vazirmatn', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

input,
button,
select,
textarea,
optgroup,
option,
.hq-input,
.hq-select,
.hq-textarea,
.hq-btn,
.hq-pill,
.hq-wizard-title,
.hq-wizard-circle,
.hq-sidebar-link,
.hq-step-nav-btn,
.hq-repeat-pill-number,
.hq-repeat-pill-status,
.pdp-wrapper,
.pdp-popover,
.pdp-popover * {
  font-family: 'Vazirmatn', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
}

/* ============================================================================
   Figma Design #2:286 "admin-event-form" (1440px canvas layout)
   Complete Dark & Light Mode Theme Support
   - Desktop: Quick-Jump Sticky Sidebar (all sections visible)
   - Mobile: Step Wizard (focused step-by-step navigation)
   ============================================================================ */

:root {
  --hq-bg: #f3f4f6;
  --hq-surface: #ffffff;
  --hq-surface-tint: #f0fdfa;
  --hq-surface-tint-border: #0d7a87;
  --hq-surface-muted: #f9fafb;
  --hq-border: #e5e7eb;
  --hq-border-subtle: #f3f4f6;
  --hq-text-main: #1f2937;
  --hq-text-muted: #4b5563;
  --hq-text-light: #6b7280;
  --hq-input-bg: #f9fafb;
  --hq-input-border: #e5e7eb;
  --hq-input-focus: #0d7a87;
  --hq-primary: #0d7a87;
  --hq-primary-dark: #0a5c66;
  --hq-primary-light: #f0fdfa;
  --hq-danger: #dc2626;
  --hq-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  --hq-shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
  --hq-pill-gray-bg: #f3f4f6;
  --hq-pill-gray-border: #e5e7eb;
  --hq-pill-gray-text: #4b5563;
  --hq-pill-danger-bg: #fef2f2;
  --hq-pill-danger-border: #fca5a5;
  --hq-pill-danger-text: #dc2626;
  --hq-btn-white-bg: #ffffff;
  --hq-btn-white-border: #d1d5db;
  --hq-btn-white-text: #374151;
  --hq-sidebar-active-bg: rgba(13, 122, 135, 0.08);
  --hq-sidebar-active-border: #0d7a87;
  --hq-sticky-footer-bg: rgba(255, 255, 255, 0.95);
}

:root[data-theme="dark"], body[data-theme="dark"] {
  --hq-bg: #0f1518;
  --hq-surface: #19232a;
  --hq-surface-tint: rgba(13, 122, 135, 0.14);
  --hq-surface-tint-border: #14b8a6;
  --hq-surface-muted: #131b20;
  --hq-border: #2a343a;
  --hq-border-subtle: #222b31;
  --hq-text-main: #e7ecee;
  --hq-text-muted: #9ca3af;
  --hq-text-light: #6b7280;
  --hq-input-bg: #131b20;
  --hq-input-border: #2e3840;
  --hq-input-focus: #14b8a6;
  --hq-primary: #14b8a6;
  --hq-primary-dark: #0d7a87;
  --hq-primary-light: rgba(20, 184, 166, 0.15);
  --hq-danger: #ef4444;
  --hq-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
  --hq-shadow-lg: 0 16px 32px -8px rgba(0, 0, 0, 0.6);
  --hq-pill-gray-bg: #222b31;
  --hq-pill-gray-border: #2e3840;
  --hq-pill-gray-text: #9ca3af;
  --hq-pill-danger-bg: rgba(220, 38, 38, 0.15);
  --hq-pill-danger-border: rgba(239, 68, 68, 0.35);
  --hq-pill-danger-text: #f87171;
  --hq-btn-white-bg: #1f2937;
  --hq-btn-white-border: #374151;
  --hq-btn-white-text: #e5e7eb;
  --hq-sidebar-active-bg: rgba(20, 184, 166, 0.15);
  --hq-sidebar-active-border: #14b8a6;
  --hq-sticky-footer-bg: rgba(25, 35, 42, 0.95);
  color-scheme: dark;
}

html {
  scroll-behavior: smooth;
}

body {
  background: var(--hq-bg) !important;
  color: var(--hq-text-main) !important;
  font-family: 'Vazirmatn', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
  transition: background 0.2s ease, color 0.2s ease;
}

.wrap {
  max-width: 1400px !important;
  margin: 0 auto !important;
  padding: 0 12px !important;
}

.hq-form-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
  margin: 0 auto;
  padding-bottom: 120px;
}

/* 1. HQ Header */
.hq-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--hq-surface);
  border: 1px solid var(--hq-border);
  border-radius: 16px;
  padding: 24px;
  box-shadow: var(--hq-shadow);
}
.hq-header-title-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.hq-header-title {
  font-size: 24px;
  font-weight: 800;
  color: var(--hq-text-main);
  margin: 0;
  line-height: 1.3;
}
.hq-header-subtitle {
  font-size: 14px;
  color: var(--hq-text-muted);
  margin: 0;
}
.hq-header-status-group {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.hq-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 14px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  line-height: 1;
  white-space: nowrap;
}
.hq-pill-teal {
  background: var(--hq-primary);
  color: #ffffff;
}
.hq-pill-gray {
  background: var(--hq-pill-gray-bg);
  border: 1px solid var(--hq-pill-gray-border);
  color: var(--hq-pill-gray-text);
}
.hq-pill-danger {
  background: var(--hq-pill-danger-bg);
  border: 1px solid var(--hq-pill-danger-border);
  color: var(--hq-pill-danger-text);
}

/* Iconoir Vector Icons */
.iconoir {
  display: inline-block;
  vertical-align: middle;
  flex-shrink: 0;
  stroke: currentColor;
  transition: color 0.15s ease, stroke 0.15s ease, transform 0.15s ease;
}

/* Step Wizard (Optimized for Mobile/Phone) */

/* Mode 2: Wizard Stepper */
.hq-wizard-stepper {
  display: none;
  flex-direction: column;
  gap: 12px;
  background: var(--hq-surface);
  border: 1px solid var(--hq-border);
  border-radius: 16px;
  padding: 20px;
  box-shadow: var(--hq-shadow);
}
.hq-wizard-steps {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}
.hq-wizard-step {
  display: flex;
  align-items: center;
  gap: 8px;
  z-index: 1;
  background: var(--hq-surface);
  padding: 0 8px;
  cursor: pointer;
}
.hq-wizard-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 800;
  background: var(--hq-input-bg);
  border: 2px solid var(--hq-border);
  color: var(--hq-text-muted);
  transition: all 0.2s ease;
}
.hq-wizard-step.is-active .hq-wizard-circle {
  background: var(--hq-primary);
  border-color: var(--hq-primary);
  color: #fff;
}
.hq-wizard-step.is-done .hq-wizard-circle {
  background: #16a37a;
  border-color: #16a37a;
  color: #fff;
}
.hq-wizard-title {
  font-size: 13px;
  font-weight: 700;
  color: var(--hq-text-muted);
}
.hq-wizard-step.is-active .hq-wizard-title {
  color: var(--hq-text-main);
}
.hq-wizard-progress {
  height: 4px;
  background: var(--hq-border);
  border-radius: 999px;
  overflow: hidden;
}
.hq-wizard-progress-bar {
  height: 100%;
  background: var(--hq-primary);
  width: 25%;
  transition: width 0.3s ease;
}

/* Mode 3: Sidebar Quick-Jump Layout */
.hq-sidebar-layout {
  display: flex;
  gap: 24px;
  align-items: flex-start;
}
.hq-sidebar-content {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.hq-sidebar-nav {
  display: none;
  width: 280px;
  position: sticky;
  top: 20px;
  background: var(--hq-surface);
  border: 1px solid var(--hq-border);
  border-radius: 16px;
  padding: 16px;
  box-shadow: var(--hq-shadow);
  flex-direction: column;
  gap: 12px;
}
.hq-sidebar-title {
  font-size: 13px;
  font-weight: 800;
  color: var(--hq-text-main);
  padding-bottom: 8px;
  border-bottom: 1px solid var(--hq-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.hq-sidebar-links {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.hq-sidebar-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  color: var(--hq-text-muted);
  text-decoration: none;
  transition: all 0.2s ease;
  gap: 8px;
}
.hq-sidebar-link:hover {
  background: var(--hq-input-bg);
  color: var(--hq-text-main);
}
.hq-sidebar-link.is-active {
  background: var(--hq-sidebar-active-bg);
  color: var(--hq-primary);
  font-weight: 700;
}
.hq-sidebar-link-label {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}
.hq-sidebar-ico {
  color: var(--hq-text-muted);
  opacity: 0.75;
}
.hq-sidebar-link:hover .hq-sidebar-ico,
.hq-sidebar-link.is-active .hq-sidebar-ico {
  color: var(--hq-primary);
  opacity: 1;
}

.hq-check {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}
.hq-check .hq-icon-done {
  display: none;
  color: #10b981;
}
.hq-check .hq-icon-empty {
  display: inline-block;
  color: var(--hq-text-muted);
  opacity: 0.35;
}
.hq-check.is-done .hq-icon-done {
  display: inline-block;
}
.hq-check.is-done .hq-icon-empty {
  display: none;
}

.hq-sidebar-tools {
  padding-top: 10px;
  border-top: 1px solid var(--hq-border);
  display: flex;
  gap: 6px;
}
.hq-sidebar-btn {
  flex: 1;
  font-size: 11px;
  font-weight: 700;
  padding: 7px 8px;
  border-radius: 6px;
  border: 1px solid var(--hq-border);
  background: var(--hq-input-bg);
  color: var(--hq-text-muted);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.2s ease;
}
.hq-sidebar-btn:hover {
  background: var(--hq-surface);
  color: var(--hq-text-main);
  border-color: var(--hq-primary);
}

/* Step Group Base */
.hq-step-group {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Desktop Layout (>= 901px): Quick-Jump Sticky Sidebar (all sections visible) */
@media (min-width: 901px) {
  .hq-wizard-stepper {
    display: none !important;
  }
  .hq-sidebar-nav {
    display: flex !important;
    width: 280px;
    position: sticky;
    top: 20px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
  }
  .hq-step-group {
    display: flex !important;
  }
  #stepNavButtons {
    display: none !important;
  }
}

/* 2. Intro Box */
.hq-intro-card {
  background: var(--hq-surface);
  border: 1px solid var(--hq-border);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  box-shadow: var(--hq-shadow);
}
.hq-intro-title {
  font-size: 18px;
  font-weight: 800;
  color: var(--hq-text-main);
  margin: 0;
}
.hq-intro-desc {
  font-size: 14px;
  line-height: 1.8;
  color: var(--hq-text-muted);
  margin: 0;
}
.hq-note-box {
  background: var(--hq-surface-tint);
  border: 1px solid var(--hq-surface-tint-border);
  border-radius: 10px;
  padding: 12px 16px;
  font-size: 12px;
  font-weight: 600;
  color: var(--hq-primary-dark);
  display: flex;
  align-items: center;
  gap: 10px;
}
.hq-note-box svg {
  flex-shrink: 0;
  width: 18px;
  height: 18px;
  stroke: var(--hq-primary);
}

/* 3. Cards */
.hq-card {
  background: var(--hq-surface);
  border: 1px solid var(--hq-border);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  box-shadow: var(--hq-shadow);
  scroll-margin-top: 40px;
}
.hq-card-header {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.hq-card-title {
  font-size: 18px;
  font-weight: 800;
  color: var(--hq-text-main);
  margin: 0;
}
.hq-card-subtitle {
  font-size: 13px;
  color: var(--hq-text-muted);
  margin: 0;
}

/* Grids & Inputs */
.hq-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.hq-grid-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 16px;
}
.hq-field-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.hq-field-label {
  font-size: 13px;
  font-weight: 700;
  color: var(--hq-text-muted);
}
.hq-input, .hq-select, .hq-textarea {
  width: 100%;
  background: var(--hq-input-bg);
  border: 1px solid var(--hq-input-border);
  border-radius: 10px;
  padding: 12px 14px;
  font-size: 13px;
  font-family: inherit;
  color: var(--hq-text-main);
  outline: none;
  transition: border-color 0.2s ease, background 0.2s ease;
  box-sizing: border-box;
}
.hq-input:focus, .hq-select:focus, .hq-textarea:focus {
  border-color: var(--hq-input-focus);
  background: var(--hq-surface);
}
.hq-input-readonly {
  background: var(--hq-surface-muted) !important;
  color: var(--hq-text-muted) !important;
  cursor: default;
}
.hq-textarea {
  resize: vertical;
  min-height: 110px;
  line-height: 1.7;
}

/* Buttons */
.hq-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
  text-decoration: none;
  line-height: 1;
}
.hq-btn-primary {
  background: var(--hq-primary);
  color: #ffffff;
}
.hq-btn-primary:hover {
  background: var(--hq-primary-dark);
}
.hq-btn-white {
  background: var(--hq-btn-white-bg);
  border: 1px solid var(--hq-btn-white-border);
  color: var(--hq-btn-white-text);
}
.hq-btn-white:hover {
  background: var(--hq-input-bg);
}
.hq-btn-danger {
  background: var(--hq-pill-danger-bg);
  border: 1px solid var(--hq-pill-danger-border);
  color: var(--hq-danger);
}
.hq-btn-danger:hover {
  background: var(--hq-danger);
  color: #fff;
}
.hq-btn-row {
  display: flex;
  gap: 8px;
  margin-top: 10px;
  align-items: center;
}

/* Poster & PDF boxes */
.hq-state-box {
  background: var(--hq-surface);
  border: 1px solid var(--hq-border);
  border-radius: 14px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.hq-state-box-tint {
  background: var(--hq-surface-tint);
  border-color: var(--hq-surface-tint-border);
}
.hq-state-box-title {
  font-size: 14px;
  font-weight: 800;
  color: var(--hq-text-main);
  display: flex;
  align-items: center;
  gap: 6px;
}
.hq-dropzone {
  border: 2px dashed var(--hq-border);
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  background: var(--hq-input-bg);
  transition: all 0.2s;
}
.hq-dropzone:hover {
  border-color: var(--hq-primary);
}
.hq-dropzone svg {
  width: 32px;
  height: 32px;
  stroke: var(--hq-primary);
}
.hq-dropzone-text {
  font-size: 13px;
  font-weight: 700;
  color: var(--hq-text-main);
}
.hq-dropzone-sub {
  font-size: 11px;
  color: var(--hq-text-light);
}
.hq-poster-preview-img {
  width: 100%;
  max-height: 260px;
  object-fit: cover;
  border-radius: 10px;
  border: 1px solid var(--hq-border);
}
.hq-meta-pills {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}
.hq-meta-pill {
  padding: 4px 10px;
  background: var(--hq-surface-muted);
  border: 1px solid var(--hq-border);
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  color: var(--hq-text-muted);
}

/* Repeatable Cards */
.hq-repeat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}
.hq-repeat-card {
  background: var(--hq-surface-tint);
  border: 1px solid var(--hq-surface-tint-border);
  border-radius: 14px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  position: relative;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hq-repeat-card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.hq-repeat-pill-number {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  background: var(--hq-surface);
  border: 1px solid var(--hq-border);
  border-radius: 999px;
  color: var(--hq-primary-dark);
}
.hq-repeat-pill-status {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  background: var(--hq-surface);
  border: 1px solid #10b981;
  border-radius: 999px;
  color: #10b981;
}
.hq-repeat-media {
  width: 100%;
  height: 160px;
  border-radius: 10px;
  overflow: hidden;
  background: var(--hq-surface);
  border: 1px solid var(--hq-border);
  display: flex;
  align-items: center;
  justify-content: center;
}
.hq-repeat-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.hq-repeat-media-empty {
  color: var(--hq-text-light);
  font-size: 12px;
  font-weight: 600;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}
.hq-repeat-title {
  font-size: 15px;
  font-weight: 800;
  color: var(--hq-text-main);
  margin: 0;
}
.hq-repeat-desc {
  font-size: 12px;
  line-height: 1.6;
  color: var(--hq-text-muted);
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.hq-repeat-actions {
  display: flex;
  gap: 8px;
  margin-top: 4px;
}
.hq-repeat-drawer {
  display: none;
  flex-direction: column;
  gap: 10px;
  padding-top: 12px;
  border-top: 1px dashed var(--hq-border);
  margin-top: 8px;
}
.hq-repeat-drawer.is-open {
  display: flex;
}

/* Banner Preview */
.hq-banner-preview-box {
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #ffffff;
  background-color: #007b7a;
  transition: all 0.2s ease;
  flex-wrap: wrap;
  gap: 12px;
}
.hq-banner-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.hq-banner-countdown {
  background: rgba(0, 0, 0, 0.2);
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.5px;
}

/* Sticky Bottom Actions Bar */
.hq-actions-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 100;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--hq-sticky-footer-bg);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-top: 1px solid var(--hq-border);
  padding: 14px 24px;
  box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.06);
}
.hq-actions-bar-inner {
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
}
.hq-actions-left {
  display: flex;
  gap: 10px;
  align-items: center;
}
.hq-step-nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 18px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  border: 1px solid var(--hq-border);
  background: var(--hq-surface);
  color: var(--hq-text-main);
  transition: all 0.2s ease;
}
.hq-step-nav-btn:hover {
  background: var(--hq-input-bg);
}

/* Mobile Structure (<= 900px): Step Wizard */
@media (max-width: 900px) {
  .hq-grid-3 { grid-template-columns: 1fr; }
  .hq-grid-2 { grid-template-columns: 1fr; }
  .hq-header { flex-direction: column; align-items: flex-start; gap: 16px; }
  .hq-sidebar-layout { flex-direction: column; }
  .hq-sidebar-nav { display: none !important; }
  
  /* Normal flow on mobile: sits right under header, never slides under dashboard topbar */
  .hq-wizard-stepper {
    display: flex !important;
    position: relative !important;
    top: auto !important;
    z-index: 1 !important;
    backdrop-filter: none !important;
    margin-bottom: 20px;
    background: var(--hq-surface) !important;
  }
  :root[data-theme="dark"] .hq-wizard-stepper,
  body[data-theme="dark"] .hq-wizard-stepper {
    background: var(--hq-surface) !important;
  }

  /* Generous bottom clearance so the last items of every list/step are 100% visible above the save footer */
  .hq-form-container {
    margin-bottom: 0 !important;
    padding-bottom: 240px !important;
  }
  .hq-sidebar-content {
    padding-bottom: 30px;
  }
  .hq-step-group {
    padding-bottom: 30px;
  }
  .hq-step-group:not(.is-active-step) {
    display: none !important;
  }
  .hq-step-group.is-active-step {
    display: flex !important;
    flex-direction: column;
    gap: 20px;
  }

  .hq-repeat-grid {
    margin-bottom: 16px;
  }

  /* Actions Bar Mobile Layout: 2 streamlined, touch-friendly rows */
  .hq-actions-bar {
    padding: 10px 14px;
    padding-bottom: max(10px, env(safe-area-inset-bottom, 10px));
  }
  .hq-actions-bar-inner {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }
  .hq-actions-left {
    width: 100%;
    display: flex;
    flex-direction: row;
    gap: 8px;
  }
  .hq-actions-left .hq-btn-primary {
    flex: 1;
    justify-content: center;
    padding: 10px 14px;
    font-size: 13.5px;
  }
  .hq-actions-left .hq-btn-white {
    flex: 0 0 auto;
    padding: 10px 14px;
    font-size: 12.5px;
    white-space: nowrap;
  }
  .hq-actions-nav {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }
  .hq-actions-nav .hq-btn-white {
    flex: 0 0 auto;
    padding: 8px 14px;
    font-size: 12px;
  }
  #stepNavButtons {
    display: inline-flex !important;
    flex: 1;
    justify-content: flex-end;
    gap: 8px;
  }
  #stepNavButtons .hq-step-nav-btn {
    flex: 1;
    justify-content: center;
    padding: 8px 10px;
    font-size: 12px;
    white-space: nowrap;
  }
}

@media (max-width: 640px) {
  body {
    padding: 12px 10px !important;
  }
  .wrap {
    padding: 0 !important;
  }
  .hq-wizard-stepper {
    padding: 14px 8px;
    margin-bottom: 16px;
  }
  .hq-wizard-steps {
    gap: 2px;
  }
  .hq-wizard-step {
    flex-direction: column;
    gap: 4px;
    padding: 0 4px;
    text-align: center;
  }
  .hq-wizard-circle {
    width: 28px;
    height: 28px;
    font-size: 12px;
  }
  .hq-wizard-title {
    font-size: 11px;
    max-width: 68px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .hq-repeat-grid {
    grid-template-columns: 1fr;
  }
  .hq-card {
    padding: 16px;
  }
}

/* Auto-save Draft Banner & Footer Indicator */
.hq-draft-banner {
  background: var(--hq-surface);
  border: 1px solid #10b981;
  border-right: 5px solid #10b981;
  border-radius: 14px;
  padding: 16px 20px;
  margin-bottom: 22px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  box-shadow: 0 4px 18px rgba(16, 185, 129, 0.12);
  animation: slideDownFade 0.3s ease;
}
@keyframes slideDownFade {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
.hq-draft-banner-content {
  display: flex;
  align-items: center;
  gap: 14px;
}
.hq-draft-banner-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.hq-draft-banner-text {
  font-size: 13.5px;
  color: var(--hq-text-main);
  line-height: 1.6;
}
.hq-draft-banner-text strong {
  color: #10b981;
  margin-left: 6px;
}
.hq-draft-banner-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}
.hq-autosave-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  background: var(--hq-surface-muted);
  border: 1px solid var(--hq-border);
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  color: var(--hq-text-muted);
}
.hq-autosave-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
  transition: all 0.3s ease;
}
.hq-autosave-dot.is-saving {
  background: #f59e0b;
  box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.25);
}
</style>

<form class="hq-form-container" action="event-save.php" method="post" enctype="multipart/form-data" id="eventForm"
      data-banner-bg="<?= event_h($event['banner_background']) ?>"
      data-banner-text="<?= event_h($event['banner_text_color']) ?>"
      data-banner-accent="<?= event_h($event['banner_accent_color']) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$event['id'] ?>">

  <!-- Auto-save Draft Restore Banner (Instant auto-draft) -->
  <div id="draftRestoreBanner" class="hq-draft-banner" style="display:none;">
    <div class="hq-draft-banner-content">
      <div class="hq-draft-banner-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
      </div>
      <div class="hq-draft-banner-text">
        <strong>پیش‌نویس ذخیره‌شده پیدا شد:</strong>
        <span id="draftTimeLabel">اطلاعاتی از ویرایش قبلی شما به‌صورت لحظه‌ای در مرورگر ذخیره شده است.</span>
      </div>
    </div>
    <div class="hq-draft-banner-actions">
      <button type="button" class="hq-btn hq-btn-primary" id="btnRestoreDraft" style="padding:7px 16px; font-size:12.5px;">بازیابی اطلاعات</button>
      <button type="button" class="hq-btn hq-btn-white" id="btnDiscardDraft" style="padding:7px 14px; font-size:12.5px;">حذف پیش‌نویس</button>
    </div>
  </div>

  <!-- Step Wizard Stepper (Active on Mobile Phone Screens, <= 900px) -->

  <!-- Mode 2: Wizard Stepper Progress Bar -->
  <div class="hq-wizard-stepper" id="wizardStepper">
    <div class="hq-wizard-steps">
      <div class="hq-wizard-step is-active" data-step-target="1">
        <div class="hq-wizard-circle">۱</div>
        <div class="hq-wizard-title">اطلاعات پایه و زمان‌بندی</div>
      </div>
      <div class="hq-wizard-step" data-step-target="2">
        <div class="hq-wizard-circle">۲</div>
        <div class="hq-wizard-title">پوستر و فایل برنامه</div>
      </div>
      <div class="hq-wizard-step" data-step-target="3">
        <div class="hq-wizard-circle">۳</div>
        <div class="hq-wizard-title">ارکان، اساتید و حامیان</div>
      </div>
      <div class="hq-wizard-step" data-step-target="4">
        <div class="hq-wizard-circle">۴</div>
        <div class="hq-wizard-title">اخبار و بنر سراسری</div>
      </div>
    </div>
    <div class="hq-wizard-progress">
      <div class="hq-wizard-progress-bar" id="wizardProgressBar"></div>
    </div>
  </div>

  <!-- Form Layout Container (with Sidebar mode support) -->
  <div class="hq-sidebar-layout">
    
    <!-- Main Content Sections -->
    <div class="hq-sidebar-content">

      <!-- ================= GROUP 1: اطلاعات پایه و زمان‌بندی ================= -->
      <div class="hq-step-group is-active-tab is-active-step" data-group="1">
        
        <!-- 2. Intro Box (#8:141) -->
        <section class="hq-intro-card" id="sec-intro">
          <h2 class="hq-intro-title">فرم مدیریت رویداد</h2>
          <p class="hq-intro-desc">
            این فرم برای مدیریت کامل یک رویداد در ستاد مرکزی طراحی شده است. بخش‌های تکرارشونده را می‌توانید ویرایش، مرتب‌سازی و فعال/غیرفعال کنید. برای هر فیلد و فایل، حالت‌های خالی، آپلود و ویرایش مشخص شده است.
          </p>
          <div class="hq-note-box">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>نکته مهم: فقط یک رویداد می‌تواند بنر سراسری فعال داشته باشد. در صورت فعال‌سازی برای رویداد دیگری، بنر فعلی غیرفعال می‌شود.</span>
          </div>
        </section>

        <!-- 3. اطلاعات پایه (#8:154) -->
        <section class="hq-card" id="sec-base">
          <div class="hq-card-header">
            <h2 class="hq-card-title">اطلاعات پایه</h2>
            <p class="hq-card-subtitle">اطلاعات اصلی رویداد را در این بخش مدیریت کنید.</p>
          </div>
          <div class="hq-field-group">
            <label class="hq-field-label">عنوان کامل همایش / رویداد <span style="color:var(--hq-danger)">*</span></label>
            <input type="text" name="title" required class="hq-input" placeholder="ششمین همایش ملی مراقبت‌های حمایتی و تسکینی" value="<?= event_h($event['title']) ?>">
          </div>
          <div class="hq-grid-2">
            <div class="hq-field-group">
              <label class="hq-field-label">کد داخلی رویداد (Slug)</label>
              <input type="text" name="slug" dir="ltr" class="hq-input" placeholder="HQ-2024-018" value="<?= event_h($event['slug']) ?>">
            </div>
            <div class="hq-field-group">
              <label class="hq-field-label">وضعیت انتشار</label>
              <select name="status" class="hq-select">
                <option value="draft" <?= $event['status'] === 'draft' ? 'selected' : '' ?>>پیش‌نویس</option>
                <option value="published" <?= $event['status'] === 'published' ? 'selected' : '' ?>>منتشرشده</option>
                <option value="archived" <?= $event['status'] === 'archived' ? 'selected' : '' ?>>بایگانی‌شده</option>
              </select>
            </div>
          </div>
          <div class="hq-grid-2">
            <div class="hq-field-group">
              <label class="hq-field-label">لینک ثبت‌نام</label>
              <input type="url" name="registration_url" dir="ltr" class="hq-input" placeholder="https://hq.example.com/register/event-018" value="<?= event_h($event['registration_url']) ?>">
            </div>
            <div class="hq-field-group">
              <label class="hq-field-label">لینک صفحه رویداد</label>
              <div style="display: flex; gap: 8px;">
                <input type="text" readonly dir="ltr" class="hq-input hq-input-readonly" value="<?= $event['slug'] ? 'https://mymacsa.ir/event.php?slug=' . urlencode($event['slug']) : 'پس از ذخیره مشخص می‌شود' ?>">
                <?php if ($event['slug']): ?>
                  <a href="/event.php?slug=<?= urlencode($event['slug']) ?>" target="_blank" class="hq-btn hq-btn-white" title="مشاهده صفحه">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </section>

        <!-- 4. تاریخ شمسی و ساعت (#8:172) -->
        <section class="hq-card" id="sec-date">
          <div class="hq-card-header">
            <h2 class="hq-card-title">تاریخ شمسی و ساعت</h2>
            <p class="hq-card-subtitle">تاریخ و ساعت برگزاری را بر اساس تقویم شمسی و ساعت تهران تنظیم کنید.</p>
          </div>
          <div class="hq-grid-3">
            <div class="hq-field-group">
              <label class="hq-field-label">تاریخ برگزاری <span style="color:var(--hq-danger)">*</span></label>
              <div data-persian-datepicker>
                <input type="hidden" name="jalali_date" required value="<?= event_h($jalaliDate) ?>">
                <input type="hidden" name="start_time" value="<?= event_h(substr((string)$event['start_time'], 0, 5)) ?>">
              </div>
            </div>
            <div class="hq-field-group">
              <label class="hq-field-label">ساعت شروع (به وقت تهران)</label>
              <input type="text" class="hq-input" dir="ltr" value="<?= event_h(substr((string)$event['start_time'], 0, 5) ?: '08:00') ?>" readonly id="startTimeDisplay">
            </div>
            <div class="hq-field-group">
              <label class="hq-field-label">ساعت پایان</label>
              <input type="text" name="end_time" class="hq-input" dir="ltr" value="<?= event_h(substr((string)$event['end_time'], 0, 5) ?: '18:00') ?>">
            </div>
          </div>
          <div class="hq-grid-2">
            <div class="hq-field-group">
              <label class="hq-field-label">تاریخ پایان ثبت‌نام</label>
              <input type="text" name="registration_deadline_fa" class="hq-input" placeholder="مثال: ۱۴ مهر ۱۴۰۵" value="<?= event_h($formattedJalaliDate) ?>">
            </div>
            <div class="hq-field-group">
              <label class="hq-field-label">زمان آخرین بروزرسانی</label>
              <input type="text" readonly class="hq-input hq-input-readonly" value="<?= event_h($formattedUpdated) ?>">
            </div>
          </div>
        </section>

        <!-- 5. توضیح کوتاه (#8:198) & درباره رویداد (#8:210) -->
        <section class="hq-card" id="sec-about">
          <div class="hq-card-header">
            <h2 class="hq-card-title">توضیحات و درباره رویداد</h2>
            <p class="hq-card-subtitle">خلاصه رویداد برای نمایش در بنرها و متن تفصیلی برای صفحه رویداد.</p>
          </div>
          <div class="hq-field-group">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <label class="hq-field-label">توضیح کوتاه (حداکثر ۱۴۰ کاراکتر)</label>
              <span id="shortDescCounter" style="font-size: 11px; color: var(--hq-text-light); font-weight:700;">۰ / ۱۴۰</span>
            </div>
            <input type="text" name="short_description" id="shortDescInput" maxlength="140" class="hq-input" placeholder="همایش ملی مراقبت‌های حمایتی و تسکینی" value="<?= event_h($event['short_description']) ?>">
          </div>
          <div class="hq-field-group" style="margin-top: 10px;">
            <label class="hq-field-label">متن کامل درباره رویداد</label>
            <textarea name="about" class="hq-textarea" placeholder="متن توضیحات کامل رویداد..."><?= event_h($event['about']) ?></textarea>
          </div>
        </section>

      </div>

      <!-- ================= GROUP 2: رسانه و فایل‌ها ================= -->
      <div class="hq-step-group" data-group="2">
        
        <!-- 7. پوستر و فایل‌های همایش (#8:221) -->
        <section class="hq-card" id="sec-media">
          <div class="hq-card-header">
            <h2 class="hq-card-title">پوستر و فایل‌های همایش</h2>
            <p class="hq-card-subtitle">پوستر اصلی، فایل PDF برنامه و فایل‌های کمکی را در این بخش مدیریت کنید.</p>
          </div>
          <div class="hq-grid-2">
            <!-- پوستر رویداد (#8:225) -->
            <div class="hq-state-box" id="posterBox">
              <div class="hq-state-box-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span>پوستر رویداد</span>
              </div>
              <div class="hq-dropzone" onclick="document.getElementById('posterInput').click();" <?= $event['poster'] ? 'style="display:none;"' : '' ?>>
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <span class="hq-dropzone-text">فایل را اینجا رها کنید یا برای انتخاب کلیک کنید</span>
                <span class="hq-dropzone-sub">تصویر JPG, PNG یا WEBP تا ۱۲ مگابایت</span>
              </div>
              <?php if ($event['poster']): ?>
                <img src="<?= event_h($event['poster']) ?>" alt="پوستر رویداد" class="hq-poster-preview-img" id="posterImg">
                <div class="hq-meta-pills">
                  <span class="hq-meta-pill"><?= basename((string)$event['poster']) ?></span>
                  <span class="hq-meta-pill">تصویر ثبت‌شده</span>
                </div>
              <?php endif; ?>
              <input type="file" name="poster" id="posterInput" accept="image/*" style="display:none;">
              <div class="hq-btn-row">
                <button type="button" class="hq-btn hq-btn-primary" onclick="document.getElementById('posterInput').click();">آپلود جدید</button>
                <?php if ($event['poster']): ?>
                  <button type="button" class="hq-btn hq-btn-white" onclick="document.getElementById('posterInput').click();">ویرایش</button>
                <?php endif; ?>
              </div>
            </div>

            <!-- فایل برنامه همایش (#8:251) -->
            <div class="hq-state-box hq-state-box-tint" id="pdfBox">
              <input type="hidden" name="delete_schedule_pdf" id="deleteSchedulePdf" value="0">
              <div class="hq-state-box-title" style="color:var(--hq-primary-dark);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>فایل PDF برنامه</span>
              </div>
              <?php if ($event['schedule_pdf']): ?>
                <div id="pdfCurrentContainer" style="background:var(--hq-surface); border:1px solid var(--hq-border); border-radius:10px; padding:16px; display:flex; align-items:center; justify-content:space-between;">
                  <div>
                    <strong style="display:block; font-size:13px; color:var(--hq-text-main);"><?= basename((string)$event['schedule_pdf']) ?></strong>
                    <span style="font-size:11px; color:var(--hq-text-muted);">برنامه کامل زمان‌بندی همایش</span>
                  </div>
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--hq-primary)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                </div>
              <?php endif; ?>
              <div class="hq-dropzone" id="pdfDropzone" onclick="document.getElementById('pdfInput').click();" <?= $event['schedule_pdf'] ? 'style="display:none;"' : '' ?>>
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <span class="hq-dropzone-text">فایل PDF برنامه را انتخاب کنید</span>
                <span class="hq-dropzone-sub">حداکثر تا ۲۵ مگابایت</span>
              </div>
              <input type="file" name="schedule_pdf" id="pdfInput" accept="application/pdf" style="display:none;">
              <div class="hq-btn-row">
                <button type="button" class="hq-btn hq-btn-primary" onclick="document.getElementById('pdfInput').click();">آپلود جدید</button>
                <?php if ($event['schedule_pdf']): ?>
                  <a href="<?= event_h($event['schedule_pdf']) ?>" target="_blank" class="hq-btn hq-btn-white" id="pdfViewBtn">مشاهده PDF</a>
                  <button type="button" class="hq-btn hq-btn-danger" id="pdfDeleteBtn" onclick="deleteCurrentPdf()">حذف فایل PDF</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </section>

      </div>

      <!-- ================= GROUP 3: ارکان، اساتید و حامیان ================= -->
      <div class="hq-step-group" data-group="3">
        
        <!-- 8. بخش‌های هیرو تکرارشونده (#8:275) -->
        <section class="hq-card" id="sec-heroes">
          <div class="hq-card-header">
            <h2 class="hq-card-title">بخش‌های هیرو (تکرارشونده)</h2>
            <p class="hq-card-subtitle">اسلایدرها و هیروهای بالای صفحه رویداد را در این بخش مدیریت کنید.</p>
          </div>
          <div class="hq-repeat-grid" id="hero-list">
            <?php foreach ($heroes as $idx => $h): ?>
              <div class="hq-repeat-card">
                <div class="hq-repeat-card-top">
                  <span class="hq-repeat-pill-number">هیرو <?= sprintf('%02d', $idx + 1) ?></span>
                  <span class="hq-repeat-pill-status">فعال</span>
                </div>
                <div class="hq-repeat-media" title="برای تغییر یا انتخاب تصویر کلیک کنید یا فایل را اینجا رها نمایید">
                  <?php if (!empty($h['image'])): ?>
                    <img src="<?= event_h($h['image']) ?>" alt="<?= event_h($h['title']) ?>">
                  <?php else: ?>
                    <div class="hq-repeat-media-empty">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                      <span>بدون تصویر (کلیک یا رهاسازی)</span>
                    </div>
                  <?php endif; ?>
                  <div class="hq-repeat-media-overlay">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span>تغییر تصویر</span>
                  </div>
                </div>
                <h3 class="hq-repeat-title"><?= event_h($h['title']) ?: 'بدون عنوان' ?></h3>
                <p class="hq-repeat-desc"><?= event_h($h['description']) ?: 'توضیحات هیرو وارد نشده است.' ?></p>
                <div class="hq-repeat-actions">
                  <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">ویرایش</button>
                  <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
                </div>
                <div class="hq-repeat-drawer">
                  <label class="hq-field-label">عنوان هیرو<input name="hero_title[]" value="<?= event_h($h['title']) ?>" class="hq-input"></label>
                  <label class="hq-field-label">توضیح<input name="hero_description[]" value="<?= event_h($h['description']) ?>" class="hq-input"></label>
                  <label class="hq-field-label">متن دکمه<input name="hero_button_label[]" value="<?= event_h($h['button_label']) ?>" class="hq-input"></label>
                  <label class="hq-field-label">لینک دکمه<input name="hero_link[]" value="<?= event_h($h['button_link'] ?? $h['link'] ?? '') ?>" dir="ltr" class="hq-input"></label>
                  <label class="hq-field-label">تغییر تصویر<input type="file" name="hero_image[]" accept="image/*" class="hq-input"></label>
                  <input type="hidden" name="hero_existing[]" value="<?= event_h($h['image']) ?>">
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div>
            <button type="button" class="hq-btn hq-btn-primary" data-add="hero">+ افزودن هیرو جدید</button>
          </div>
        </section>

        <!-- 9. دبیر علمی و اجرایی (#8:356) -->
        <section class="hq-card" id="sec-organizers">
          <div class="hq-card-header">
            <h2 class="hq-card-title">دبیر علمی و اجرایی</h2>
            <p class="hq-card-subtitle">اطلاعات دبیران علمی و اجرایی رویداد را در این بخش مدیریت کنید.</p>
          </div>
          <div class="hq-repeat-grid" id="person-list">
            <?php foreach ($people as $idx => $p): ?>
              <div class="hq-repeat-card">
                <div class="hq-repeat-card-top">
                  <span class="hq-repeat-pill-number"><?= $p['role'] === 'scientific_secretary' ? 'دبیر علمی' : 'دبیر اجرایی' ?></span>
                  <span class="hq-repeat-pill-status">فعال</span>
                </div>
                <div class="hq-repeat-media" title="برای تغییر یا انتخاب عکس کلیک کنید یا فایل را اینجا رها نمایید">
                  <?php if (!empty($p['image'])): ?>
                    <img src="<?= event_h($p['image']) ?>" alt="<?= event_h($p['name']) ?>">
                  <?php else: ?>
                    <div class="hq-repeat-media-empty">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                      <span>بدون عکس (کلیک یا رهاسازی)</span>
                    </div>
                  <?php endif; ?>
                  <div class="hq-repeat-media-overlay">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span>تغییر عکس</span>
                  </div>
                </div>
                <h3 class="hq-repeat-title"><?= event_h($p['name']) ?: 'نام مشخص نشده' ?></h3>
                <p class="hq-repeat-desc"><?= event_h($p['title']) ?: 'سمت مشخص نشده' ?></p>
                <div class="hq-repeat-actions">
                  <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">ویرایش</button>
                  <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
                </div>
                <div class="hq-repeat-drawer">
                  <label class="hq-field-label">نوع سمت
                    <select name="person_role[]" class="hq-select">
                      <option value="scientific_secretary" <?= $p['role'] === 'scientific_secretary' ? 'selected' : '' ?>>دبیر علمی</option>
                      <option value="executive_secretary" <?= $p['role'] === 'executive_secretary' ? 'selected' : '' ?>>دبیر اجرایی</option>
                    </select>
                  </label>
                  <label class="hq-field-label">نام و نام خانوادگی<input name="person_name[]" value="<?= event_h($p['name']) ?>" class="hq-input"></label>
                  <label class="hq-field-label">سمت / عنوان<input name="person_title[]" value="<?= event_h($p['title']) ?>" class="hq-input"></label>
                  <label class="hq-field-label">عکس<input type="file" name="person_image[]" accept="image/*" class="hq-input"></label>
                  <input type="hidden" name="person_existing[]" value="<?= event_h($p['image']) ?>">
                  <input type="hidden" name="person_order[]" value="<?= (int)$p['sort_order'] ?>">
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div>
            <button type="button" class="hq-btn hq-btn-primary" data-add="person">+ افزودن دبیر جدید</button>
          </div>
        </section>

        <!-- 10. اساتید تکرارشونده (#8:411) -->
        <section class="hq-card" id="sec-speakers">
          <div class="hq-card-header">
            <h2 class="hq-card-title">اساتید و سخنرانان</h2>
            <p class="hq-card-subtitle">اسامی اساتید و سخنرانان را به صورت تکرارشونده مدیریت کنید.</p>
          </div>
          <div class="hq-repeat-grid" id="speaker-list">
            <?php foreach ($speakers as $idx => $s): ?>
              <div class="hq-repeat-card">
                <div class="hq-repeat-card-top">
                  <span class="hq-repeat-pill-number">سخنران <?= sprintf('%02d', $idx + 1) ?></span>
                  <span class="hq-repeat-pill-status">فعال</span>
                </div>
                <div class="hq-repeat-media" title="برای تغییر یا انتخاب عکس کلیک کنید یا فایل را اینجا رها نمایید">
                  <?php if (!empty($s['image'])): ?>
                    <img src="<?= event_h($s['image']) ?>" alt="<?= event_h($s['name']) ?>">
                  <?php else: ?>
                    <div class="hq-repeat-media-empty">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                      <span>بدون عکس (کلیک یا رهاسازی)</span>
                    </div>
                  <?php endif; ?>
                  <div class="hq-repeat-media-overlay">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span>تغییر عکس</span>
                  </div>
                </div>
                <h3 class="hq-repeat-title"><?= event_h($s['name']) ?: 'نام استاد' ?></h3>
                <p class="hq-repeat-desc"><?= event_h($s['title']) ?: 'تخصص یا عنوان علمی' ?></p>
                <div class="hq-repeat-actions">
                  <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">ویرایش</button>
                  <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
                </div>
                <div class="hq-repeat-drawer">
                  <label class="hq-field-label">نام استاد<input name="speaker_name[]" value="<?= event_h($s['name']) ?>" class="hq-input"></label>
                  <label class="hq-field-label">سمت / تخصص<input name="speaker_title[]" value="<?= event_h($s['title']) ?>" class="hq-input"></label>
                  <label class="hq-field-label">ترتیب نمایش<input name="speaker_order[]" type="text" dir="ltr" value="<?= (int)$s['sort_order'] ?>" class="hq-input"></label>
                  <label class="hq-field-label">عکس<input type="file" name="speaker_image[]" accept="image/*" class="hq-input"></label>
                  <input type="hidden" name="speaker_existing[]" value="<?= event_h($s['image']) ?>">
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div>
            <button type="button" class="hq-btn hq-btn-primary" data-add="speaker">+ افزودن سخنران جدید</button>
          </div>
        </section>

        <!-- 11. همراهان تکرارشونده (#8:466) -->
        <section class="hq-card" id="sec-partners">
          <div class="hq-card-header">
            <h2 class="hq-card-title">همراهان و حامیان</h2>
            <p class="hq-card-subtitle">همراهان و سازمان‌های همکار رویداد را به صورت تکرارشونده مدیریت کنید.</p>
          </div>
          <div class="hq-repeat-grid" id="partner-list">
            <?php foreach ($partners as $idx => $p): ?>
              <div class="hq-repeat-card">
                <div class="hq-repeat-card-top">
                  <span class="hq-repeat-pill-number">همراه <?= sprintf('%02d', $idx + 1) ?></span>
                  <span class="hq-repeat-pill-status">فعال</span>
                </div>
                <div class="hq-repeat-media" title="برای تغییر یا انتخاب لوگو کلیک کنید یا فایل را اینجا رها نمایید">
                  <?php if (!empty($p['logo'])): ?>
                    <img src="<?= event_h($p['logo']) ?>" alt="<?= event_h($p['name']) ?>">
                  <?php else: ?>
                    <div class="hq-repeat-media-empty">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                      <span>بدون لوگو (کلیک یا رهاسازی)</span>
                    </div>
                  <?php endif; ?>
                  <div class="hq-repeat-media-overlay">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span>تغییر لوگو</span>
                  </div>
                </div>
                <h3 class="hq-repeat-title"><?= event_h($p['name']) ?: 'نام سازمان / حامی' ?></h3>
                <p class="hq-repeat-desc">سازمان همکار و حامی رویداد</p>
                <div class="hq-repeat-actions">
                  <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">ویرایش</button>
                  <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
                </div>
                <div class="hq-repeat-drawer">
                  <label class="hq-field-label">نام همراه / حامی<input name="partner_name[]" value="<?= event_h($p['name']) ?>" class="hq-input"></label>
                  <label class="hq-field-label">ترتیب نمایش<input name="partner_order[]" type="text" dir="ltr" value="<?= (int)$p['sort_order'] ?>" class="hq-input"></label>
                  <label class="hq-field-label">لوگو<input type="file" name="partner_logo[]" accept="image/*" class="hq-input"></label>
                  <input type="hidden" name="partner_existing[]" value="<?= event_h($p['logo']) ?>">
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div>
            <button type="button" class="hq-btn hq-btn-primary" data-add="partner">+ افزودن همراه جدید</button>
          </div>
        </section>

      </div>

      <!-- ================= GROUP 4: اخبار و بنر سراسری ================= -->
      <div class="hq-step-group" data-group="4">
        
        <!-- 12. اخبار اختصاصی رویداد (#8:521) -->
        <section class="hq-card" id="sec-news">
          <div class="hq-card-header">
            <h2 class="hq-card-title">ایجاد و ویرایش اخبار اختصاصی رویداد</h2>
            <p class="hq-card-subtitle">اخبار و اطلاعیه‌های اختصاصی رویداد را در این بخش مدیریت کنید.</p>
          </div>
          <div class="hq-repeat-grid">
            <?php foreach ($news as $idx => $n): ?>
              <div class="hq-repeat-card">
                <div class="hq-repeat-card-top">
                  <span class="hq-repeat-pill-number">خبر <?= sprintf('%02d', $idx + 1) ?></span>
                  <span class="hq-repeat-pill-status" style="<?= $n['status'] === 'published' ? '' : 'color:#f59e0b; border-color:#f59e0b;' ?>"><?= $n['status'] === 'published' ? 'فعال' : 'پیش‌نویس' ?></span>
                </div>
                <div class="hq-repeat-media">
                  <?php if (!empty($n['image'])): ?>
                    <img src="<?= event_h($n['image']) ?>" alt="<?= event_h($n['title']) ?>">
                  <?php else: ?>
                    <div class="hq-repeat-media-empty">بدون تصویر</div>
                  <?php endif; ?>
                </div>
                <h3 class="hq-repeat-title"><?= event_h($n['title']) ?></h3>
                <p class="hq-repeat-desc"><?= event_h($n['excerpt']) ?: mb_substr(strip_tags((string)$n['content']), 0, 100) . '...' ?></p>
                <div class="hq-repeat-actions">
                  <a href="event-news-create.php?id=<?= (int)$n['id'] ?>&event_id=<?= (int)$event['id'] ?>" class="hq-btn hq-btn-white" style="width: 100%;">ویرایش خبر</a>
                </div>
              </div>
            <?php endforeach; ?>
            <?php if (!$news): ?>
              <div style="grid-column: 1 / -1; padding: 24px; text-align: center; color: var(--hq-text-muted); background: var(--hq-surface-muted); border: 1px dashed var(--hq-border); border-radius: 12px; font-size: 13px;">
                هنوز خبری برای این رویداد ثبت نشده است. با دکمه زیر می‌توانید خبر اختصاصی جدید ایجاد نمایید.
              </div>
            <?php endif; ?>
          </div>
          <div>
            <?php if ($event['id']): ?>
              <a href="event-news-create.php?event_id=<?= (int)$event['id'] ?>" class="hq-btn hq-btn-primary">+ افزودن خبر جدید</a>
            <?php else: ?>
              <button type="button" class="hq-btn hq-btn-primary" onclick="alert('ابتدا رویداد را ذخیره نمایید سپس اخبار را اضافه کنید.');">+ افزودن خبر جدید</button>
            <?php endif; ?>
          </div>
        </section>

        <!-- 13. تنظیمات بنر سراسری (#8:576) -->
        <section class="hq-card" id="sec-banner">
          <div class="hq-card-header">
            <h2 class="hq-card-title">تنظیمات بنر سراسری</h2>
            <p class="hq-card-subtitle">تنظیمات نمایش بنر سراسری و شمارش معکوس را در این بخش مدیریت کنید.</p>
          </div>

          <label style="display:flex; align-items:center; gap:10px; background:var(--hq-surface-muted); padding:14px; border-radius:10px; border:1px solid var(--hq-border); cursor:pointer; font-size:13px; font-weight:700; color:var(--hq-text-main);">
            <input type="checkbox" name="banner_active" value="1" <?= (int)$event['banner_active'] ? 'checked' : '' ?> style="width:18px; height:18px; accent-color:var(--hq-primary);">
            نمایش بنر این رویداد در بالای تمام صفحات وب‌سایت
          </label>

          <!-- پیش‌نمایش زنده بنر -->
          <div class="hq-field-group">
            <label class="hq-field-label">پیش‌نمایش زنده بنر بالای سایت</label>
            <div id="bannerPreview" class="hq-banner-preview-box" style="background-color: <?= event_h($event['banner_background']) ?>; color: <?= event_h($event['banner_text_color']) ?>;">
              <div class="hq-banner-left">
                <span class="hq-banner-countdown">۱۲ روز ۰۸ ساعت ۲۴ دقیقه</span>
                <span style="font-weight: 800; font-size: 13px;" data-preview="title"><?= event_h($event['title']) ?: 'عنوان رویداد' ?></span>
                <span style="font-size: 12px; opacity: 0.9;" data-preview="label"><?= event_h($event['banner_label']) ?: 'رویداد پیش‌رو' ?></span>
              </div>
              <div style="display: flex; align-items: center; gap: 12px;">
                <span class="hq-btn" style="background: transparent; border: 1px solid <?= event_h($event['banner_accent_color']) ?>; color: inherit; padding: 6px 14px; font-size: 12px;" data-preview="cta">
                  <?= event_h($event['banner_cta']) ?: 'مشاهده رویداد' ?>
                </span>
                <span style="cursor: pointer; opacity: 0.8;">✕</span>
              </div>
            </div>
          </div>

          <div class="hq-grid-2">
            <div class="hq-field-group">
              <label class="hq-field-label">متن کوچک بنر</label>
              <input type="text" name="banner_label" class="hq-input" value="<?= event_h($event['banner_label']) ?>" data-banner-preview="label">
            </div>
            <div class="hq-field-group">
              <label class="hq-field-label">متن دکمه اکشن (CTA)</label>
              <input type="text" name="banner_cta" class="hq-input" value="<?= event_h($event['banner_cta']) ?>" data-banner-preview="cta">
            </div>
          </div>

          <div class="hq-grid-2">
            <div class="hq-field-group">
              <label class="hq-field-label">لینک دکمه بنر</label>
              <input type="text" name="banner_link" dir="ltr" class="hq-input" placeholder="https://..." value="<?= event_h($event['banner_link']) ?>">
            </div>
            <div class="hq-field-group">
              <label class="hq-field-label">تم رنگی بنر</label>
              <select name="banner_theme" class="hq-select" data-banner-preview="theme">
                <option value="teal" <?= $event['banner_theme'] === 'teal' ? 'selected' : '' ?>>فیروزه‌ای مکسا</option>
                <option value="amber" <?= $event['banner_theme'] === 'amber' ? 'selected' : '' ?>>کهربایی</option>
                <option value="dark" <?= $event['banner_theme'] === 'dark' ? 'selected' : '' ?>>تیره اختصاصی</option>
              </select>
            </div>
          </div>

          <!-- Color Pickers -->
          <div style="display:flex; gap:16px; flex-wrap:wrap; align-items:center; background:var(--hq-surface-muted); padding:14px; border-radius:10px; border:1px solid var(--hq-border);">
            <div style="display:flex; align-items:center; gap:8px;">
              <input type="color" id="pickerBg" value="<?= event_h($event['banner_background']) ?>" style="width:36px; height:36px; border:none; border-radius:6px; cursor:pointer;">
              <span style="font-size:12px; font-weight:700; color:var(--hq-text-muted);">رنگ زمینه:</span>
              <input type="text" name="banner_background" id="textBg" value="<?= event_h($event['banner_background']) ?>" maxlength="7" dir="ltr" style="width:80px; font-size:12px;" class="hq-input">
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <input type="color" id="pickerText" value="<?= event_h($event['banner_text_color']) ?>" style="width:36px; height:36px; border:none; border-radius:6px; cursor:pointer;">
              <span style="font-size:12px; font-weight:700; color:var(--hq-text-muted);">رنگ متن:</span>
              <input type="text" name="banner_text_color" id="textText" value="<?= event_h($event['banner_text_color']) ?>" maxlength="7" dir="ltr" style="width:80px; font-size:12px;" class="hq-input">
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <input type="color" id="pickerAccent" value="<?= event_h($event['banner_accent_color']) ?>" style="width:36px; height:36px; border:none; border-radius:6px; cursor:pointer;">
              <span style="font-size:12px; font-weight:700; color:var(--hq-text-muted);">رنگ تأکید:</span>
              <input type="text" name="banner_accent_color" id="textAccent" value="<?= event_h($event['banner_accent_color']) ?>" maxlength="7" dir="ltr" style="width:80px; font-size:12px;" class="hq-input">
            </div>
          </div>

          <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--hq-text-muted); cursor:pointer; margin-top:8px;">
            <input type="checkbox" name="banner_dismissible" value="1" <?= (int)$event['banner_dismissible'] ? 'checked' : '' ?> style="accent-color:var(--hq-primary);">
            امکان بستن موقت بنر توسط کاربران سایت
          </label>

          <div class="hq-note-box" style="background:var(--hq-surface-muted); color:var(--hq-text-muted); border-color:var(--hq-border);">
            <span>نکته مهم: فقط یک رویداد می‌تواند بنر سراسری فعال داشته باشد. در صورت فعال‌سازی برای رویداد دیگری، بنر فعلی غیرفعال می‌شود.</span>
          </div>
        </section>

      </div>

    </div>

    <!-- Mode 3: Sticky Quick-Jump Sidebar Navigation -->
    <aside class="hq-sidebar-nav" id="sidebarNav">
      <div class="hq-sidebar-title">
        <span>فهرست سریع بخش‌ها</span>
        <span style="font-size: 11px; font-weight: 600; color: var(--hq-primary);" id="sidebarFilledCount">۰ از ۹ پر شده</span>
      </div>
      <div class="hq-sidebar-links">
        <a href="#sec-intro" class="hq-sidebar-link is-active" data-anchor="sec-intro">
          <div class="hq-sidebar-link-label">
            <?= hq_iconoir('info', 'hq-sidebar-ico', 16) ?>
            <span>معرفی فرم</span>
          </div>
          <span class="hq-check is-done" style="color:var(--hq-primary);">
            <?= hq_iconoir('info', '', 15) ?>
          </span>
        </a>
        <a href="#sec-base" class="hq-sidebar-link" data-anchor="sec-base">
          <div class="hq-sidebar-link-label">
            <?= hq_iconoir('base', 'hq-sidebar-ico', 16) ?>
            <span>اطلاعات پایه رویداد</span>
          </div>
          <span class="hq-check <?= !empty($event['title']) ? 'is-done' : '' ?>" id="chk-base">
            <?= hq_iconoir('check', 'hq-icon-done', 15) ?>
            <?= hq_iconoir('circle', 'hq-icon-empty', 15) ?>
          </span>
        </a>
        <a href="#sec-date" class="hq-sidebar-link" data-anchor="sec-date">
          <div class="hq-sidebar-link-label">
            <?= hq_iconoir('base', 'hq-sidebar-ico', 16) ?>
            <span>تاریخ شمسی و زمان</span>
          </div>
          <span class="hq-check <?= !empty($event['event_date']) ? 'is-done' : '' ?>" id="chk-date">
            <?= hq_iconoir('check', 'hq-icon-done', 15) ?>
            <?= hq_iconoir('circle', 'hq-icon-empty', 15) ?>
          </span>
        </a>
        <a href="#sec-about" class="hq-sidebar-link" data-anchor="sec-about">
          <div class="hq-sidebar-link-label">
            <?= hq_iconoir('wizard', 'hq-sidebar-ico', 16) ?>
            <span>توضیح کوتاه و متن</span>
          </div>
          <span class="hq-check <?= !empty($event['short_description']) ? 'is-done' : '' ?>" id="chk-about">
            <?= hq_iconoir('check', 'hq-icon-done', 15) ?>
            <?= hq_iconoir('circle', 'hq-icon-empty', 15) ?>
          </span>
        </a>
        <a href="#sec-media" class="hq-sidebar-link" data-anchor="sec-media">
          <div class="hq-sidebar-link-label">
            <?= hq_iconoir('media', 'hq-sidebar-ico', 16) ?>
            <span>پوستر و برنامه PDF</span>
          </div>
          <span class="hq-check <?= (!empty($event['poster']) || !empty($event['schedule_pdf'])) ? 'is-done' : '' ?>" id="chk-media">
            <?= hq_iconoir('check', 'hq-icon-done', 15) ?>
            <?= hq_iconoir('circle', 'hq-icon-empty', 15) ?>
          </span>
        </a>
        <a href="#sec-heroes" class="hq-sidebar-link" data-anchor="sec-heroes">
          <div class="hq-sidebar-link-label">
            <?= hq_iconoir('media', 'hq-sidebar-ico', 16) ?>
            <span>بخش‌های هیرو</span>
          </div>
          <span class="hq-check <?= count($heroes) ? 'is-done' : '' ?>" id="chk-heroes">
            <?= hq_iconoir('check', 'hq-icon-done', 15) ?>
            <?= hq_iconoir('circle', 'hq-icon-empty', 15) ?>
          </span>
        </a>
        <a href="#sec-organizers" class="hq-sidebar-link" data-anchor="sec-organizers">
          <div class="hq-sidebar-link-label">
            <?= hq_iconoir('people', 'hq-sidebar-ico', 16) ?>
            <span>دبیر علمی و اجرایی</span>
          </div>
          <span class="hq-check <?= count($people) ? 'is-done' : '' ?>" id="chk-organizers">
            <?= hq_iconoir('check', 'hq-icon-done', 15) ?>
            <?= hq_iconoir('circle', 'hq-icon-empty', 15) ?>
          </span>
        </a>
        <a href="#sec-speakers" class="hq-sidebar-link" data-anchor="sec-speakers">
          <div class="hq-sidebar-link-label">
            <?= hq_iconoir('people', 'hq-sidebar-ico', 16) ?>
            <span>اساتید و سخنرانان</span>
          </div>
          <span class="hq-check <?= count($speakers) ? 'is-done' : '' ?>" id="chk-speakers">
            <?= hq_iconoir('check', 'hq-icon-done', 15) ?>
            <?= hq_iconoir('circle', 'hq-icon-empty', 15) ?>
          </span>
        </a>
        <a href="#sec-partners" class="hq-sidebar-link" data-anchor="sec-partners">
          <div class="hq-sidebar-link-label">
            <?= hq_iconoir('people', 'hq-sidebar-ico', 16) ?>
            <span>همراهان و حامیان</span>
          </div>
          <span class="hq-check <?= count($partners) ? 'is-done' : '' ?>" id="chk-partners">
            <?= hq_iconoir('check', 'hq-icon-done', 15) ?>
            <?= hq_iconoir('circle', 'hq-icon-empty', 15) ?>
          </span>
        </a>
        <a href="#sec-news" class="hq-sidebar-link" data-anchor="sec-news">
          <div class="hq-sidebar-link-label">
            <?= hq_iconoir('news', 'hq-sidebar-ico', 16) ?>
            <span>اخبار اختصاصی</span>
          </div>
          <span class="hq-check <?= count($news) ? 'is-done' : '' ?>" id="chk-news">
            <?= hq_iconoir('check', 'hq-icon-done', 15) ?>
            <?= hq_iconoir('circle', 'hq-icon-empty', 15) ?>
          </span>
        </a>
        <a href="#sec-banner" class="hq-sidebar-link" data-anchor="sec-banner">
          <div class="hq-sidebar-link-label">
            <?= hq_iconoir('news', 'hq-sidebar-ico', 16) ?>
            <span>تنظیمات بنر سراسری</span>
          </div>
          <span class="hq-check <?= (int)$event['banner_active'] ? 'is-done' : '' ?>" id="chk-banner">
            <?= hq_iconoir('check', 'hq-icon-done', 15) ?>
            <?= hq_iconoir('circle', 'hq-icon-empty', 15) ?>
          </span>
        </a>
      </div>
      <div class="hq-sidebar-tools">
        <button type="button" class="hq-sidebar-btn" onclick="toggleAllDrawers(true)">
          <?= hq_iconoir('expand', '', 14) ?>
          <span>باز کردن کارت‌ها</span>
        </button>
        <button type="button" class="hq-sidebar-btn" onclick="toggleAllDrawers(false)">
          <?= hq_iconoir('collapse', '', 14) ?>
          <span>بستن کارت‌ها</span>
        </button>
      </div>
    </aside>

  </div>

  <!-- Sticky Bottom Actions Bar (#8:629) -->
  <footer class="hq-actions-bar">
    <div class="hq-actions-bar-inner">
      <div class="hq-actions-nav" style="display:flex; align-items:center; gap:10px;">
        <a href="event-list.php" class="hq-btn hq-btn-white" style="padding:10px 20px; display:inline-flex; align-items:center; gap:6px;">
          <?= hq_iconoir('cancel', '', 15) ?>
          <span>انصراف</span>
        </a>
        <div id="stepNavButtons" style="display:none; gap:8px;">
          <button type="button" class="hq-step-nav-btn" id="btnPrevStep" style="display:inline-flex; align-items:center; gap:6px;">
            <?= hq_iconoir('prev', '', 15) ?>
            <span>گام قبلی</span>
          </button>
          <button type="button" class="hq-step-nav-btn" id="btnNextStep" style="background:var(--hq-primary-light); color:var(--hq-primary); border-color:var(--hq-primary); display:inline-flex; align-items:center; gap:6px;">
            <span>گام بعدی</span>
            <?= hq_iconoir('next', '', 15) ?>
          </button>
        </div>
      </div>
      <div class="hq-actions-left" style="display:flex; align-items:center; gap:12px;">
        <div id="autoSaveIndicator" class="hq-autosave-indicator" title="پیش‌نویس اطلاعات این فرم به‌صورت لحظه‌ای در مرورگر ذخیره می‌شود">
          <span class="hq-autosave-dot" id="autoSaveDot"></span>
          <span id="autoSaveStatusText">ذخیره خودکار پیش‌نویس فعال است</span>
        </div>
        <?php if ($event['slug']): ?>
          <a href="/event.php?slug=<?= urlencode($event['slug']) ?>" target="_blank" class="hq-btn hq-btn-white" style="display:inline-flex; align-items:center; gap:6px;">
            <?= hq_iconoir('preview', '', 15) ?>
            <span>پیش‌نمایش رویداد</span>
          </a>
        <?php else: ?>
          <button type="button" class="hq-btn hq-btn-white" onclick="alert('برای مشاهده پیش‌نمایش ابتدا رویداد را ذخیره نمایید.')" style="display:inline-flex; align-items:center; gap:6px;">
            <?= hq_iconoir('preview', '', 15) ?>
            <span>پیش‌نمایش</span>
          </button>
        <?php endif; ?>
        <button type="submit" class="hq-btn hq-btn-primary" style="padding:10px 24px; font-size:14px; display:inline-flex; align-items:center; gap:8px;">
          <?= hq_iconoir('save', '', 16) ?>
          <span>ذخیره تغییرات رویداد</span>
        </button>
      </div>
    </div>
  </footer>
</form>

<script>
function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function toggleDrawer(btn) {
  const card = btn.closest('.hq-repeat-card');
  const drawer = card?.querySelector('.hq-repeat-drawer');
  if (drawer) {
    drawer.classList.toggle('is-open');
    btn.textContent = drawer.classList.contains('is-open') ? 'بستن ویرایش' : 'ویرایش';
  }
}

function toggleAllDrawers(open) {
  document.querySelectorAll('.hq-repeat-drawer').forEach(d => {
    d.classList.toggle('is-open', open);
    const card = d.closest('.hq-repeat-card');
    if (card) {
      const btn = card.querySelector('.hq-repeat-actions .hq-btn-white');
      if (btn) btn.textContent = open ? 'بستن ویرایش' : 'ویرایش';
    }
  });
}

function deleteCurrentPdf() {
  if (confirm('آیا از حذف فایل PDF برنامه رویداد اطمینان دارید؟')) {
    const hidden = document.getElementById('deleteSchedulePdf');
    if (hidden) hidden.value = '1';
    const container = document.getElementById('pdfCurrentContainer');
    if (container) container.style.display = 'none';
    const viewBtn = document.getElementById('pdfViewBtn');
    if (viewBtn) viewBtn.style.display = 'none';
    const delBtn = document.getElementById('pdfDeleteBtn');
    if (delBtn) delBtn.style.display = 'none';
    const drop = document.getElementById('pdfDropzone');
    if (drop) {
      drop.style.display = 'flex';
      drop.innerHTML = '<span class="hq-dropzone-text" style="color:#ef4444;">فایل PDF برنامه حذف خواهد شد. می‌توانید در صورت تمایل فایل جدید انتخاب کنید.</span>';
    }
    if (typeof window.triggerAutoSave === 'function') window.triggerAutoSave();
  }
}

(function() {
  // =========================================================================
  // 1. Responsive Layout: Mobile Step Wizard vs Desktop Quick-Jump Sidebar
  // =========================================================================
  let currentStep = 1;
  const totalSteps = 4;

  const wizardSteps = document.querySelectorAll('.hq-wizard-step');
  const groups = document.querySelectorAll('.hq-step-group');
  const btnPrev = document.getElementById('btnPrevStep');
  const btnNext = document.getElementById('btnNextStep');
  const progressBar = document.getElementById('wizardProgressBar');

  function isMobile() {
    return window.innerWidth <= 900;
  }

  function setStep(stepNum) {
    stepNum = Math.max(1, Math.min(totalSteps, stepNum));
    currentStep = stepNum;

    groups.forEach(g => {
      const matches = g.dataset.group === String(stepNum);
      g.classList.toggle('is-active-step', matches);
    });

    wizardSteps.forEach((s, idx) => {
      const n = idx + 1;
      s.classList.toggle('is-active', n === stepNum);
      s.classList.toggle('is-done', n < stepNum);
    });

    if (progressBar) {
      progressBar.style.width = (stepNum / totalSteps * 100) + '%';
    }

    if (btnPrev) {
      btnPrev.style.visibility = stepNum === 1 ? 'hidden' : 'visible';
    }
    if (btnNext) {
      btnNext.style.display = stepNum === totalSteps ? 'none' : 'inline-flex';
    }

    if (isMobile()) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  wizardSteps.forEach(s => {
    s.addEventListener('click', () => setStep(Number(s.dataset.stepTarget)));
  });

  if (btnPrev) btnPrev.addEventListener('click', () => setStep(currentStep - 1));
  if (btnNext) btnNext.addEventListener('click', () => setStep(currentStep + 1));

  // Initialize
  setStep(1);

  window.addEventListener('resize', () => {
    if (!isMobile()) {
      groups.forEach(g => g.classList.add('is-active-step'));
    } else {
      setStep(currentStep);
    }
  });

  // =========================================================================
  // 2. Desktop Sidebar Scrollspy & Filled Counter
  // =========================================================================
  const sidebarLinks = document.querySelectorAll('.hq-sidebar-link');
  function updateSidebarSpy() {
    if (isMobile()) return;
    const scrollY = window.scrollY + 160;
    sidebarLinks.forEach(link => {
      const target = document.getElementById(link.dataset.anchor);
      if (target) {
        const top = target.offsetTop;
        const height = target.offsetHeight;
        if (scrollY >= top && scrollY < top + height) {
          sidebarLinks.forEach(l => l.classList.remove('is-active'));
          link.classList.add('is-active');
        }
      }
    });
  }
  window.addEventListener('scroll', updateSidebarSpy, { passive: true });

  // Update checkmarks based on values
  function updateCheckmarks() {
    const title = document.querySelector('input[name="title"]');
    const chkBase = document.getElementById('chk-base');
    if (chkBase && title) chkBase.classList.toggle('is-done', !!title.value.trim());

    const shortDesc = document.getElementById('shortDescInput');
    const chkAbout = document.getElementById('chk-about');
    if (chkAbout && shortDesc) chkAbout.classList.toggle('is-done', !!shortDesc.value.trim());

    const chkHeroes = document.getElementById('chk-heroes');
    if (chkHeroes) chkHeroes.classList.toggle('is-done', document.querySelectorAll('#hero-list .hq-repeat-card').length > 0);

    const chkOrg = document.getElementById('chk-organizers');
    if (chkOrg) chkOrg.classList.toggle('is-done', document.querySelectorAll('#person-list .hq-repeat-card').length > 0);

    const chkSpk = document.getElementById('chk-speakers');
    if (chkSpk) chkSpk.classList.toggle('is-done', document.querySelectorAll('#speaker-list .hq-repeat-card').length > 0);

    const chkPart = document.getElementById('chk-partners');
    if (chkPart) chkPart.classList.toggle('is-done', document.querySelectorAll('#partner-list .hq-repeat-card').length > 0);

    const doneCount = document.querySelectorAll('.hq-sidebar-links .hq-check.is-done').length;
    const countEl = document.getElementById('sidebarFilledCount');
    if (countEl) {
      const faCount = String(doneCount).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
      countEl.textContent = faCount + ' از ۹ پر شده';
    }
  }
  window.updateCheckmarks = updateCheckmarks;
  updateCheckmarks();
  document.addEventListener('input', updateCheckmarks);

  // =========================================================================
  // 3. Short description counter
  // =========================================================================
  const shortDesc = document.getElementById('shortDescInput');
  const counter = document.getElementById('shortDescCounter');
  if (shortDesc && counter) {
    const updateCount = () => {
      counter.textContent = String(shortDesc.value.length).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]) + ' / ۱۴۰';
    };
    shortDesc.addEventListener('input', updateCount);
    updateCount();
  }

  // =========================================================================
  // 4. Live banner preview sync
  // =========================================================================
  const preview = document.getElementById('bannerPreview');
  const titleInput = document.querySelector('input[name="title"]');
  const labelInput = document.querySelector('[data-banner-preview="label"]');
  const ctaInput = document.querySelector('[data-banner-preview="cta"]');
  const themeSelect = document.querySelector('[data-banner-preview="theme"]');

  const pBg = document.getElementById('pickerBg');
  const tBg = document.getElementById('textBg');
  const pText = document.getElementById('pickerText');
  const tText = document.getElementById('textText');
  const pAcc = document.getElementById('pickerAccent');
  const tAcc = document.getElementById('textAccent');

  function syncBanner() {
    if (!preview) return;
    if (titleInput) {
      const titleEl = preview.querySelector('[data-preview="title"]');
      if (titleEl) titleEl.textContent = titleInput.value || 'عنوان رویداد';
    }
    if (labelInput) {
      const labelEl = preview.querySelector('[data-preview="label"]');
      if (labelEl) labelEl.textContent = labelInput.value || 'رویداد پیش‌رو';
    }
    if (ctaInput) {
      const ctaEl = preview.querySelector('[data-preview="cta"]');
      if (ctaEl) ctaEl.textContent = ctaInput.value || 'مشاهده رویداد';
    }
    if (tBg && tText && tAcc) {
      preview.style.backgroundColor = tBg.value;
      preview.style.color = tText.value;
      const ctaEl = preview.querySelector('[data-preview="cta"]');
      if (ctaEl) ctaEl.style.borderColor = tAcc.value;
    }
  }
  window.syncBanner = syncBanner;

  [titleInput, labelInput, ctaInput].forEach(el => {
    if (el) el.addEventListener('input', syncBanner);
  });

  const presets = {
    teal: ['#007b7a', '#ffffff', '#f4a61e'],
    amber: ['#e89a16', '#123a3d', '#007b7a'],
    dark: ['#123a3d', '#ffffff', '#f4a61e']
  };

  if (themeSelect) {
    themeSelect.addEventListener('change', () => {
      const c = presets[themeSelect.value];
      if (c && pBg && tBg && pText && tText && pAcc && tAcc) {
        pBg.value = tBg.value = c[0];
        pText.value = tText.value = c[1];
        pAcc.value = tAcc.value = c[2];
        syncBanner();
      }
    });
  }

  function bindColorPair(picker, text) {
    if (!picker || !text) return;
    picker.addEventListener('input', () => { text.value = picker.value; syncBanner(); });
    text.addEventListener('input', () => {
      if (/^#[0-9a-fA-F]{6}$/.test(text.value)) {
        picker.value = text.value;
        syncBanner();
      }
    });
  }
  bindColorPair(pBg, tBg);
  bindColorPair(pText, tText);
  bindColorPair(pAcc, tAcc);
  syncBanner();

  // =========================================================================
  // 5. File upload previews & Dropzones (Poster & PDF)
  // =========================================================================
  const posterInput = document.getElementById('posterInput');
  const posterBox = document.getElementById('posterBox');
  if (posterInput && posterBox) {
    posterInput.addEventListener('change', () => {
      const file = posterInput.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = e => {
          let img = posterBox.querySelector('.hq-poster-preview-img');
          if (!img) {
            const dropzone = posterBox.querySelector('.hq-dropzone');
            if (dropzone) dropzone.style.display = 'none';
            img = document.createElement('img');
            img.className = 'hq-poster-preview-img';
            posterBox.insertBefore(img, posterBox.querySelector('.hq-btn-row') || posterInput);
          }
          img.src = e.target.result;
          triggerAutoSave();
        };
        reader.readAsDataURL(file);
      }
    });

    posterBox.addEventListener('dragover', e => { e.preventDefault(); posterBox.style.borderColor = 'var(--hq-primary)'; });
    posterBox.addEventListener('dragleave', () => { posterBox.style.borderColor = ''; });
    posterBox.addEventListener('drop', e => {
      e.preventDefault();
      posterBox.style.borderColor = '';
      const file = e.dataTransfer?.files?.[0];
      if (file && file.type.startsWith('image/')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        posterInput.files = dt.files;
        posterInput.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  }

  const pdfInput = document.getElementById('pdfInput');
  const pdfBox = document.getElementById('pdfBox');
  if (pdfInput && pdfBox) {
    pdfInput.addEventListener('change', () => {
      const file = pdfInput.files[0];
      if (file) {
        const drop = pdfBox.querySelector('.hq-dropzone');
        if (drop) {
          drop.style.display = 'flex';
          drop.innerHTML = '<span class="hq-dropzone-text" style="color:#16a37a;">فایل ' + escapeHtml(file.name) + ' با موفقیت انتخاب شد</span>';
        }
        triggerAutoSave();
      }
    });

    pdfBox.addEventListener('dragover', e => { e.preventDefault(); pdfBox.style.borderColor = 'var(--hq-primary)'; });
    pdfBox.addEventListener('dragleave', () => { pdfBox.style.borderColor = ''; });
    pdfBox.addEventListener('drop', e => {
      e.preventDefault();
      pdfBox.style.borderColor = '';
      const file = e.dataTransfer?.files?.[0];
      if (file && (file.type === 'application/pdf' || file.name.endsWith('.pdf'))) {
        const dt = new DataTransfer();
        dt.items.add(file);
        pdfInput.files = dt.files;
        pdfInput.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  }

  // =========================================================================
  // 6. Repeatables logic (Templates & Actions)
  // =========================================================================
  const templates = {
    hero: (data = {}) => {
      const title = data.title || '';
      const desc = data.description || '';
      const btnLabel = data.button_label || '';
      const link = data.link || data.button_link || '';
      const existing = data.existing || '';
      const imgSrc = data.preview || existing;
      const mediaHtml = imgSrc
        ? `<img src="${escapeHtml(imgSrc)}" alt="${escapeHtml(title)}">`
        : `<div class="hq-repeat-media-empty">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <span>تصویر هیرو جدید (کلیک یا رهاسازی)</span>
          </div>`;
      const openClass = data.title ? '' : 'is-open';
      const btnText = data.title ? 'ویرایش' : 'بستن ویرایش';
      return `
        <div class="hq-repeat-card">
          <div class="hq-repeat-card-top">
            <span class="hq-repeat-pill-number">بخش هیرو</span>
            <span class="hq-repeat-pill-status">فعال</span>
          </div>
          <div class="hq-repeat-media" title="برای تغییر یا انتخاب تصویر کلیک کنید یا فایل را اینجا رها نمایید">
            ${mediaHtml}
            <div class="hq-repeat-media-overlay">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <span>تغییر تصویر</span>
            </div>
          </div>
          <h3 class="hq-repeat-title">${escapeHtml(title) || 'عنوان هیرو جدید'}</h3>
          <p class="hq-repeat-desc">${escapeHtml(desc) || 'توضیحات این بخش هیرو...'}</p>
          <div class="hq-repeat-actions">
            <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">${btnText}</button>
            <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
          </div>
          <div class="hq-repeat-drawer ${openClass}">
            <label class="hq-field-label">عنوان هیرو<input name="hero_title[]" value="${escapeHtml(title)}" class="hq-input" placeholder="عنوان هیرو"></label>
            <label class="hq-field-label">توضیح<input name="hero_description[]" value="${escapeHtml(desc)}" class="hq-input" placeholder="توضیح هیرو"></label>
            <label class="hq-field-label">متن دکمه<input name="hero_button_label[]" value="${escapeHtml(btnLabel)}" class="hq-input" placeholder="مشاهده بیشتر"></label>
            <label class="hq-field-label">لینک دکمه<input name="hero_link[]" value="${escapeHtml(link)}" dir="ltr" class="hq-input" placeholder="https://..."></label>
            <label class="hq-field-label">تصویر هیرو<input type="file" name="hero_image[]" accept="image/*" class="hq-input"></label>
            <input type="hidden" name="hero_existing[]" value="${escapeHtml(existing)}">
          </div>
        </div>`;
    },
    person: (data = {}) => {
      const role = data.role || 'scientific_secretary';
      const name = data.name || '';
      const title = data.title || '';
      const order = data.order || '0';
      const existing = data.existing || '';
      const imgSrc = data.preview || existing;
      const mediaHtml = imgSrc
        ? `<img src="${escapeHtml(imgSrc)}" alt="${escapeHtml(name)}">`
        : `<div class="hq-repeat-media-empty">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>بدون عکس (کلیک یا رهاسازی)</span>
          </div>`;
      const openClass = data.name ? '' : 'is-open';
      const btnText = data.name ? 'ویرایش' : 'بستن ویرایش';
      const roleName = role === 'scientific_secretary' ? 'دبیر علمی' : 'دبیر اجرایی';
      return `
        <div class="hq-repeat-card">
          <div class="hq-repeat-card-top">
            <span class="hq-repeat-pill-number">${roleName}</span>
            <span class="hq-repeat-pill-status">فعال</span>
          </div>
          <div class="hq-repeat-media" title="برای تغییر یا انتخاب عکس کلیک کنید یا فایل را اینجا رها نمایید">
            ${mediaHtml}
            <div class="hq-repeat-media-overlay">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <span>تغییر عکس</span>
            </div>
          </div>
          <h3 class="hq-repeat-title">${escapeHtml(name) || 'نام دبیر جدید'}</h3>
          <p class="hq-repeat-desc">${escapeHtml(title) || 'سمت دبیر...'}</p>
          <div class="hq-repeat-actions">
            <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">${btnText}</button>
            <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
          </div>
          <div class="hq-repeat-drawer ${openClass}">
            <label class="hq-field-label">نوع سمت
              <select name="person_role[]" class="hq-select">
                <option value="scientific_secretary" ${role === 'scientific_secretary' ? 'selected' : ''}>دبیر علمی</option>
                <option value="executive_secretary" ${role === 'executive_secretary' ? 'selected' : ''}>دبیر اجرایی</option>
              </select>
            </label>
            <label class="hq-field-label">نام و نام خانوادگی<input name="person_name[]" value="${escapeHtml(name)}" class="hq-input" placeholder="دکتر ..."></label>
            <label class="hq-field-label">سمت / عنوان<input name="person_title[]" value="${escapeHtml(title)}" class="hq-input" placeholder="دبیر علمی همایش..."></label>
            <label class="hq-field-label">عکس<input type="file" name="person_image[]" accept="image/*" class="hq-input"></label>
            <input type="hidden" name="person_existing[]" value="${escapeHtml(existing)}">
            <input type="hidden" name="person_order[]" value="${escapeHtml(order)}">
          </div>
        </div>`;
    },
    speaker: (data = {}) => {
      const name = data.name || '';
      const title = data.title || '';
      const order = data.order || '0';
      const existing = data.existing || '';
      const imgSrc = data.preview || existing;
      const mediaHtml = imgSrc
        ? `<img src="${escapeHtml(imgSrc)}" alt="${escapeHtml(name)}">`
        : `<div class="hq-repeat-media-empty">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>بدون عکس (کلیک یا رهاسازی)</span>
          </div>`;
      const openClass = data.name ? '' : 'is-open';
      const btnText = data.name ? 'ویرایش' : 'بستن ویرایش';
      return `
        <div class="hq-repeat-card">
          <div class="hq-repeat-card-top">
            <span class="hq-repeat-pill-number">سخنران</span>
            <span class="hq-repeat-pill-status">فعال</span>
          </div>
          <div class="hq-repeat-media" title="برای تغییر یا انتخاب عکس کلیک کنید یا فایل را اینجا رها نمایید">
            ${mediaHtml}
            <div class="hq-repeat-media-overlay">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <span>تغییر عکس</span>
            </div>
          </div>
          <h3 class="hq-repeat-title">${escapeHtml(name) || 'نام استاد / سخنران'}</h3>
          <p class="hq-repeat-desc">${escapeHtml(title) || 'تخصص یا موضوع سخنرانی...'}</p>
          <div class="hq-repeat-actions">
            <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">${btnText}</button>
            <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
          </div>
          <div class="hq-repeat-drawer ${openClass}">
            <label class="hq-field-label">نام استاد<input name="speaker_name[]" value="${escapeHtml(name)}" class="hq-input" placeholder="دکتر ..."></label>
            <label class="hq-field-label">سمت / تخصص<input name="speaker_title[]" value="${escapeHtml(title)}" class="hq-input" placeholder="متخصص ..."></label>
            <label class="hq-field-label">ترتیب نمایش<input name="speaker_order[]" type="text" dir="ltr" class="hq-input" value="${escapeHtml(order)}"></label>
            <label class="hq-field-label">عکس<input type="file" name="speaker_image[]" accept="image/*" class="hq-input"></label>
            <input type="hidden" name="speaker_existing[]" value="${escapeHtml(existing)}">
          </div>
        </div>`;
    },
    partner: (data = {}) => {
      const name = data.name || '';
      const order = data.order || '0';
      const existing = data.existing || '';
      const imgSrc = data.preview || existing;
      const mediaHtml = imgSrc
        ? `<img src="${escapeHtml(imgSrc)}" alt="${escapeHtml(name)}">`
        : `<div class="hq-repeat-media-empty">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
            <span>بدون لوگو (کلیک یا رهاسازی)</span>
          </div>`;
      const openClass = data.name ? '' : 'is-open';
      const btnText = data.name ? 'ویرایش' : 'بستن ویرایش';
      return `
        <div class="hq-repeat-card">
          <div class="hq-repeat-card-top">
            <span class="hq-repeat-pill-number">همراه</span>
            <span class="hq-repeat-pill-status">فعال</span>
          </div>
          <div class="hq-repeat-media" title="برای تغییر یا انتخاب لوگو کلیک کنید یا فایل را اینجا رها نمایید">
            ${mediaHtml}
            <div class="hq-repeat-media-overlay">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <span>تغییر لوگو</span>
            </div>
          </div>
          <h3 class="hq-repeat-title">${escapeHtml(name) || 'نام همراه جدید'}</h3>
          <p class="hq-repeat-desc">سازمان همکار یا حامی...</p>
          <div class="hq-repeat-actions">
            <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">${btnText}</button>
            <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
          </div>
          <div class="hq-repeat-drawer ${openClass}">
            <label class="hq-field-label">نام همراه / حامی<input name="partner_name[]" value="${escapeHtml(name)}" class="hq-input" placeholder="شرکت یا سازمان"></label>
            <label class="hq-field-label">ترتیب نمایش<input name="partner_order[]" type="text" dir="ltr" class="hq-input" value="${escapeHtml(order)}"></label>
            <label class="hq-field-label">لوگو<input type="file" name="partner_logo[]" accept="image/*" class="hq-input"></label>
            <input type="hidden" name="partner_existing[]" value="${escapeHtml(existing)}">
          </div>
        </div>`;
    }
  };

  // Add and remove repeatable items
  document.addEventListener('click', e => {
    const addBtn = e.target.closest('[data-add]');
    if (addBtn) {
      const type = addBtn.dataset.add;
      const list = document.getElementById(type + '-list');
      if (list && templates[type]) {
        list.insertAdjacentHTML('beforeend', templates[type]());
        triggerAutoSave();
        updateCheckmarks();
      }
    }
    const remBtn = e.target.closest('.remove-row');
    if (remBtn) {
      const card = remBtn.closest('.hq-repeat-card');
      if (card && confirm('آیا از حذف این مورد اطمینان دارید؟')) {
        card.remove();
        triggerAutoSave();
        updateCheckmarks();
      }
    }
  });

  // Repeatable media click & drag-and-drop file upload
  document.addEventListener('click', e => {
    const media = e.target.closest('.hq-repeat-media');
    if (media) {
      const card = media.closest('.hq-repeat-card');
      const fileInput = card?.querySelector('input[type="file"]');
      if (fileInput) fileInput.click();
    }
  });

  document.addEventListener('dragover', e => {
    const media = e.target.closest('.hq-repeat-media');
    if (media) {
      e.preventDefault();
      media.style.borderColor = 'var(--hq-primary)';
      media.style.background = 'rgba(0, 123, 122, 0.08)';
    }
  });

  document.addEventListener('dragleave', e => {
    const media = e.target.closest('.hq-repeat-media');
    if (media) {
      media.style.borderColor = '';
      media.style.background = '';
    }
  });

  document.addEventListener('drop', e => {
    const media = e.target.closest('.hq-repeat-media');
    if (media) {
      e.preventDefault();
      media.style.borderColor = '';
      media.style.background = '';
      const file = e.dataTransfer?.files?.[0];
      if (file && file.type.startsWith('image/')) {
        const card = media.closest('.hq-repeat-card');
        const fileInput = card?.querySelector('input[type="file"]');
        if (fileInput) {
          const dt = new DataTransfer();
          dt.items.add(file);
          fileInput.files = dt.files;
          fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
      }
    }
  });

  // Dynamic preview when file input changes in repeatable cards
  document.addEventListener('change', e => {
    if (e.target.matches('.hq-repeat-card input[type="file"]')) {
      const file = e.target.files?.[0];
      if (file && file.type.startsWith('image/')) {
        const card = e.target.closest('.hq-repeat-card');
        const media = card?.querySelector('.hq-repeat-media');
        if (media) {
          const reader = new FileReader();
          reader.onload = ev => {
            let img = media.querySelector('img');
            if (!img) {
              const empty = media.querySelector('.hq-repeat-media-empty');
              if (empty) empty.style.display = 'none';
              img = document.createElement('img');
              media.insertBefore(img, media.firstChild);
            }
            img.src = ev.target.result;
            triggerAutoSave();
          };
          reader.readAsDataURL(file);
        }
      }
    }
  });

  // Live sync card text when typing in drawer inputs
  document.addEventListener('input', e => {
    const card = e.target.closest('.hq-repeat-card');
    if (!card) return;
    const name = e.target.name || '';
    if (name.includes('title[]') || name.includes('name[]')) {
      const titleEl = card.querySelector('.hq-repeat-title');
      if (titleEl) titleEl.textContent = e.target.value.trim() || 'بدون عنوان';
    } else if (name.includes('description[]') || name.includes('person_title[]') || name.includes('speaker_title[]')) {
      const descEl = card.querySelector('.hq-repeat-desc');
      if (descEl) descEl.textContent = e.target.value.trim() || 'توضیحات...';
    }
  });

  // =========================================================================
  // 7. Instant Auto-Save Draft System (ذخیره خودکار پیش‌نویس لحظه‌ای)
  // =========================================================================
  const eventId = document.querySelector('input[name="id"]')?.value || 'new';
  const draftKey = 'maxa_event_draft_' + eventId;
  const autoSaveDot = document.getElementById('autoSaveDot');
  const autoSaveStatusText = document.getElementById('autoSaveStatusText');
  let autoSaveTimeout = null;

  function collectFormData() {
    const form = document.getElementById('eventForm');
    if (!form) return null;

    // Collect standard inputs
    const standardFields = {};
    const standardNames = [
      'title', 'slug', 'event_type', 'registration_status',
      'start_time', 'end_time', 'location_address', 'location_map_url',
      'short_description', 'about', 'banner_active', 'banner_label',
      'banner_cta_text', 'banner_theme', 'banner_background',
      'banner_text_color', 'banner_accent_color'
    ];

    standardNames.forEach(name => {
      const el = form.querySelector(`[name="${name}"]`);
      if (el) {
        if (el.type === 'checkbox') {
          standardFields[name] = el.checked ? 1 : 0;
        } else {
          standardFields[name] = el.value;
        }
      }
    });

    // Collect repeatables
    const heroes = [];
    document.querySelectorAll('#hero-list .hq-repeat-card').forEach(card => {
      heroes.push({
        title: card.querySelector('input[name="hero_title[]"]')?.value || '',
        description: card.querySelector('input[name="hero_description[]"]')?.value || '',
        button_label: card.querySelector('input[name="hero_button_label[]"]')?.value || '',
        link: card.querySelector('input[name="hero_link[]"]')?.value || '',
        existing: card.querySelector('input[name="hero_existing[]"]')?.value || '',
        preview: card.querySelector('.hq-repeat-media img')?.src || ''
      });
    });

    const people = [];
    document.querySelectorAll('#person-list .hq-repeat-card').forEach(card => {
      people.push({
        role: card.querySelector('select[name="person_role[]"]')?.value || 'scientific_secretary',
        name: card.querySelector('input[name="person_name[]"]')?.value || '',
        title: card.querySelector('input[name="person_title[]"]')?.value || '',
        order: card.querySelector('input[name="person_order[]"]')?.value || '0',
        existing: card.querySelector('input[name="person_existing[]"]')?.value || '',
        preview: card.querySelector('.hq-repeat-media img')?.src || ''
      });
    });

    const speakers = [];
    document.querySelectorAll('#speaker-list .hq-repeat-card').forEach(card => {
      speakers.push({
        name: card.querySelector('input[name="speaker_name[]"]')?.value || '',
        title: card.querySelector('input[name="speaker_title[]"]')?.value || '',
        order: card.querySelector('input[name="speaker_order[]"]')?.value || '0',
        existing: card.querySelector('input[name="speaker_existing[]"]')?.value || '',
        preview: card.querySelector('.hq-repeat-media img')?.src || ''
      });
    });

    const partners = [];
    document.querySelectorAll('#partner-list .hq-repeat-card').forEach(card => {
      partners.push({
        name: card.querySelector('input[name="partner_name[]"]')?.value || '',
        order: card.querySelector('input[name="partner_order[]"]')?.value || '0',
        existing: card.querySelector('input[name="partner_existing[]"]')?.value || '',
        preview: card.querySelector('.hq-repeat-media img')?.src || ''
      });
    });

    return {
      timestamp: Date.now(),
      eventId: eventId,
      fields: standardFields,
      heroes,
      people,
      speakers,
      partners
    };
  }

  function saveDraft() {
    try {
      const data = collectFormData();
      if (!data) return;
      localStorage.setItem(draftKey, JSON.stringify(data));
      if (autoSaveDot) autoSaveDot.classList.remove('is-saving');
      if (autoSaveStatusText) {
        const timeStr = new Date().toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        autoSaveStatusText.textContent = `پیش‌نویس ذخیره شد (${timeStr})`;
      }
    } catch(err) {
      console.warn('LocalStorage draft save error:', err);
    }
  }

  function triggerAutoSave() {
    if (autoSaveDot) autoSaveDot.classList.add('is-saving');
    if (autoSaveStatusText) autoSaveStatusText.textContent = 'در حال ذخیره پیش‌نویس...';
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(saveDraft, 600);
  }
  window.triggerAutoSave = triggerAutoSave;

  // Hook input/change events across the form
  const form = document.getElementById('eventForm');
  if (form) {
    form.addEventListener('input', triggerAutoSave);
    form.addEventListener('change', triggerAutoSave);
    // Clear draft on successful submit
    form.addEventListener('submit', () => {
      localStorage.removeItem(draftKey);
    });
  }

  // Restore draft handler
  function restoreDraftData() {
    const raw = localStorage.getItem(draftKey);
    if (!raw) return;
    try {
      const draft = JSON.parse(raw);
      if (!draft) return;

      // Restore standard fields
      if (draft.fields) {
        Object.entries(draft.fields).forEach(([name, val]) => {
          const el = form?.querySelector(`[name="${name}"]`);
          if (el) {
            if (el.type === 'checkbox') {
              el.checked = Boolean(val);
            } else {
              el.value = val;
            }
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
          }
        });
      }

      // Restore repeatables if present in draft
      if (Array.isArray(draft.heroes) && draft.heroes.length > 0) {
        const heroList = document.getElementById('hero-list');
        if (heroList) {
          heroList.innerHTML = draft.heroes.map(h => templates.hero(h)).join('');
        }
      }

      if (Array.isArray(draft.people) && draft.people.length > 0) {
        const personList = document.getElementById('person-list');
        if (personList) {
          personList.innerHTML = draft.people.map(p => templates.person(p)).join('');
        }
      }

      if (Array.isArray(draft.speakers) && draft.speakers.length > 0) {
        const speakerList = document.getElementById('speaker-list');
        if (speakerList) {
          speakerList.innerHTML = draft.speakers.map(s => templates.speaker(s)).join('');
        }
      }

      if (Array.isArray(draft.partners) && draft.partners.length > 0) {
        const partnerList = document.getElementById('partner-list');
        if (partnerList) {
          partnerList.innerHTML = draft.partners.map(p => templates.partner(p)).join('');
        }
      }

      // Hide restore banner
      const banner = document.getElementById('draftRestoreBanner');
      if (banner) banner.style.display = 'none';

      // Update checks, counters & banner
      updateCheckmarks();
      syncBanner();

      alert('اطلاعات پیش‌نویس ذخیره‌شده با موفقیت بازیابی شد.');
    } catch(err) {
      console.error('Failed to restore draft:', err);
      alert('خطا در بازیابی پیش‌نویس.');
    }
  }

  // Check draft existence on load
  const rawDraft = localStorage.getItem(draftKey);
  if (rawDraft) {
    try {
      const draft = JSON.parse(rawDraft);
      if (draft && draft.timestamp) {
        const banner = document.getElementById('draftRestoreBanner');
        const timeLabel = document.getElementById('draftTimeLabel');
        if (banner) {
          const d = new Date(draft.timestamp);
          const timeStr = d.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
          const dateStr = d.toLocaleDateString('fa-IR');
          if (timeLabel) {
            timeLabel.textContent = `پیش‌نویس ذخیره‌شده از ویرایش قبلی شما در تاریخ ${dateStr} ساعت ${timeStr} در این مرورگر موجود است.`;
          }
          banner.style.display = 'flex';
        }
      }
    } catch(err) {}
  }

  const btnRestore = document.getElementById('btnRestoreDraft');
  if (btnRestore) {
    btnRestore.addEventListener('click', restoreDraftData);
  }

  const btnDiscard = document.getElementById('btnDiscardDraft');
  if (btnDiscard) {
    btnDiscard.addEventListener('click', () => {
      if (confirm('آیا از حذف پیش‌نویس ذخیره‌شده اطمینان دارید؟')) {
        localStorage.removeItem(draftKey);
        const banner = document.getElementById('draftRestoreBanner');
        if (banner) banner.style.display = 'none';
        if (autoSaveStatusText) autoSaveStatusText.textContent = 'پیش‌نویس حذف گردید';
      }
    });
  }
})();
</script>

<script src="/assets/events/datepicker.js"></script>
<script>
// Auto sync start_time display with datepicker
document.addEventListener('DOMContentLoaded', () => {
  const pickerInput = document.querySelector('input[name="start_time"]');
  const display = document.getElementById('startTimeDisplay');
  if (pickerInput && display) {
    const syncTime = () => {
      if (pickerInput.value) display.value = pickerInput.value;
    };
    pickerInput.addEventListener('change', syncTime);
    pickerInput.addEventListener('input', syncTime);
  }
});
</script>
</div>
</body>
</html>

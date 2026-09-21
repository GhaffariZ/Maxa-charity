<?php
declare(strict_types=1);
require_once __DIR__ . '/_guard.php';
dash_require('events');
dash_require_hq();
require_once __DIR__ . '/../event-lib.php';

$pdo = dash_pdo();
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

if ($id) {
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
?>
<link rel="stylesheet" href="/assets/events/datepicker.css">
<style>
/* Figma Design #2:286 "admin-event-form" (1440px canvas layout) */
body {
  background: #f3f4f6 !important;
  color: #1f2937 !important;
  font-family: 'Vazirmatn', sans-serif !important;
}
.wrap {
  max-width: 1400px !important;
  margin: 0 auto !important;
  padding: 0 12px !important;
}

/* Base resets & typography */
.hq-form-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
  margin: 0 auto 60px;
}

/* 1. HQ Header (#8:129) */
.hq-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}
.hq-header-title-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.hq-header-title {
  font-size: 24px;
  font-weight: 800;
  color: #1f2937;
  margin: 0;
  line-height: 1.3;
}
.hq-header-subtitle {
  font-size: 14px;
  color: #4b5563;
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
  background: #0d7a87;
  color: #ffffff;
}
.hq-pill-gray {
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  color: #4b5563;
}
.hq-pill-danger {
  background: #fef2f2;
  border: 1px solid #fca5a5;
  color: #dc2626;
}

/* 2. Intro Box (#8:141) */
.hq-intro-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}
.hq-intro-title {
  font-size: 20px;
  font-weight: 800;
  color: #1f2937;
  margin: 0;
}
.hq-intro-desc {
  font-size: 14px;
  color: #4b5563;
  line-height: 1.8;
  margin: 0;
}
.hq-note-box {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f0fdfa;
  border-radius: 12px;
  padding: 14px 16px;
  color: #0a5c66;
  font-size: 12px;
  font-weight: 700;
  line-height: 1.7;
}
.hq-note-box svg {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  stroke: #0d7a87;
}

/* 3. Section Cards (#8:146) */
.hq-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}
.hq-card-header {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.hq-card-title {
  font-size: 18px;
  font-weight: 800;
  color: #1f2937;
  margin: 0;
}
.hq-card-subtitle {
  font-size: 13px;
  color: #4b5563;
  margin: 0;
}

/* Form Controls */
.hq-field-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 100%;
}
.hq-field-label {
  font-size: 14px;
  font-weight: 500;
  color: #4b5563;
  margin: 0;
}
.hq-input, .hq-select, .hq-textarea {
  width: 100%;
  background: #f3f4f6;
  border: 1px solid transparent;
  border-radius: 8px;
  padding: 12px 14px;
  font-family: inherit;
  font-size: 14px;
  color: #1f2937;
  transition: all 0.2s ease;
  box-sizing: border-box;
}
.hq-input:focus, .hq-select:focus, .hq-textarea:focus {
  outline: none;
  border-color: #0d7a87;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(13, 122, 135, 0.12);
}
.hq-textarea {
  min-height: 120px;
  resize: vertical;
  line-height: 1.8;
}
.hq-input-readonly {
  background: #f9fafb;
  color: #6b7280;
  cursor: default;
}
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

/* 4. Poster & PDF Cards (#8:218 & #8:260) */
.hq-media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
}
.hq-state-box {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.hq-state-box-title {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}
.hq-dropzone {
  min-height: 180px;
  border: 1.5px dashed #0d7a87;
  border-radius: 12px;
  background: #f0fdfa;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 20px;
  cursor: pointer;
  text-align: center;
  transition: all 0.2s ease;
}
.hq-dropzone:hover, .hq-dropzone.is-dragover {
  background: #e0f7f5;
  border-color: #0a5c66;
}
.hq-dropzone-text {
  font-size: 14px;
  font-weight: 600;
  color: #0a5c66;
}
.hq-dropzone-hint {
  font-size: 11px;
  color: #4b5563;
}
.hq-poster-preview-img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 12px;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
}
.hq-file-pills {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.hq-file-pill {
  background: #f3f4f6;
  border-radius: 999px;
  padding: 6px 12px;
  font-size: 12px;
  color: #4b5563;
  font-weight: 500;
}
.hq-btn-row {
  display: flex;
  gap: 12px;
  margin-top: auto;
}
.hq-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 8px;
  font-family: inherit;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
  text-decoration: none;
}
.hq-btn-primary {
  background: #0d7a87;
  color: #ffffff;
}
.hq-btn-primary:hover {
  background: #0a5c66;
  color: #ffffff;
}
.hq-btn-white {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  color: #4b5563;
}
.hq-btn-white:hover {
  border-color: #d1d5db;
  background: #f9fafb;
}
.hq-btn-danger {
  background: #fef2f2;
  border: 1px solid #fca5a5;
  color: #dc2626;
}
.hq-btn-danger:hover {
  background: #fee2e2;
}

/* PDF Box (#8:260) */
.hq-pdf-container {
  background: #f0fdfa;
  border: 1px solid #0d7a87;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.hq-pdf-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.hq-pdf-badge {
  background: #ffffff;
  color: #0a5c66;
  font-size: 12px;
  font-weight: 700;
  padding: 6px 14px;
  border-radius: 999px;
  border: 1px solid rgba(13, 122, 135, 0.2);
}

/* 5. Repeatable Cards (#8:301 - #8:574) */
.hq-repeat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 20px;
}
.hq-repeat-card {
  background: #f0fdfa;
  border: 1px solid #0d7a87;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  position: relative;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hq-repeat-card:hover {
  box-shadow: 0 4px 14px rgba(13, 122, 135, 0.08);
}
.hq-repeat-card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.hq-repeat-pill-number {
  background: #ffffff;
  color: #0a5c66;
  font-size: 12px;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 999px;
  border: 1px solid rgba(13, 122, 135, 0.2);
}
.hq-repeat-pill-status {
  background: #ffffff;
  color: #0a5c66;
  font-size: 12px;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 999px;
  border: 1px solid rgba(13, 122, 135, 0.2);
}
.hq-repeat-pill-status.off {
  color: #dc2626;
  border-color: #fca5a5;
  background: #fef2f2;
}
.hq-repeat-media {
  width: 100%;
  height: 160px;
  border-radius: 10px;
  background: #e5e7eb;
  object-fit: cover;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}
.hq-repeat-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.hq-repeat-media-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: #ffffff;
  border: 1px dashed #0d7a87;
  color: #0a5c66;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}
.hq-repeat-title {
  font-size: 16px;
  font-weight: 800;
  color: #1f2937;
  margin: 0;
  line-height: 1.4;
}
.hq-repeat-desc {
  font-size: 13px;
  color: #4b5563;
  margin: 0;
  line-height: 1.7;
}
.hq-repeat-actions {
  display: flex;
  gap: 10px;
  margin-top: auto;
  align-items: center;
}
.hq-repeat-drawer {
  display: none;
  flex-direction: column;
  gap: 12px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 14px;
  margin-top: 8px;
}
.hq-repeat-drawer.is-open {
  display: flex;
}

/* 6. Banner Settings (#8:576) */
.hq-banner-preview-box {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  flex-wrap: wrap;
  min-height: 64px;
  padding: 14px 44px 14px 16px;
  border-radius: 12px;
  background: #007b7a;
  color: #ffffff;
  font-size: 13px;
  text-align: center;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.hq-banner-preview-box.amber { background: #e89a16; }
.hq-banner-preview-box.dark { background: #123a3d; }
.hq-banner-cta-btn {
  border: 1px solid rgba(255, 255, 255, 0.7);
  border-radius: 8px;
  background: transparent;
  color: inherit;
  padding: 6px 14px;
  font-family: inherit;
  font-weight: 700;
  font-size: 12px;
}
.hq-banner-close-btn {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  color: inherit;
  font-size: 20px;
  line-height: 1;
  cursor: pointer;
}
.hq-check-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  cursor: pointer;
}
.hq-check-card input[type="checkbox"] {
  width: 20px;
  height: 20px;
  accent-color: #0d7a87;
  cursor: pointer;
}

/* 7. Bottom Actions (#8:629) */
.hq-actions-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 20px 24px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}
.hq-actions-left {
  display: flex;
  gap: 12px;
  align-items: center;
}

/* Responsive */
@media (max-width: 900px) {
  .hq-grid-3 { grid-template-columns: 1fr; }
  .hq-grid-2 { grid-template-columns: 1fr; }
  .hq-header { flex-direction: column; align-items: flex-start; gap: 16px; }
  .hq-actions-bar { flex-direction: column-reverse; gap: 14px; align-items: stretch; }
  .hq-actions-left { flex-direction: column; }
  .hq-btn { width: 100%; }
}
</style>

<form class="hq-form-container" action="event-save.php" method="post" enctype="multipart/form-data" id="eventForm"
      data-banner-bg="<?= event_h($event['banner_background']) ?>"
      data-banner-text="<?= event_h($event['banner_text_color']) ?>"
      data-banner-accent="<?= event_h($event['banner_accent_color']) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$event['id'] ?>">

  <!-- 1. HQ Header (#8:129) -->
  <header class="hq-header">
    <div class="hq-header-title-group">
      <h1 class="hq-header-title">ستاد مرکزی (HQ)</h1>
      <p class="hq-header-subtitle">فرم مدیریت رویداد · دسترسی اختصاصی ستاد مرکزی</p>
    </div>
    <div class="hq-header-status-group">
      <span class="hq-pill hq-pill-teal">ستاد مرکزی (HQ)</span>
      <span class="hq-pill hq-pill-gray">دسترسی اختصاصی</span>
      <span class="hq-pill hq-pill-danger">شعبه‌ها: غیرمجاز / قفل‌شده</span>
    </div>
  </header>

  <!-- 2. Intro Box (#8:141) -->
  <section class="hq-intro-card">
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

  <!-- 3. اطلاعات پایه (#8:146) -->
  <section class="hq-card">
    <div class="hq-card-header">
      <h2 class="hq-card-title">اطلاعات پایه</h2>
      <p class="hq-card-subtitle">اطلاعات اصلی رویداد را در این بخش مدیریت کنید.</p>
    </div>
    <div class="hq-field-group">
      <label class="hq-field-label">عنوان کامل همایش / رویداد</label>
      <input type="text" name="title" required class="hq-input" placeholder="مثال: ششمین همایش ملی مراقبت‌های حمایتی و تسکینی" value="<?= event_h($event['title']) ?>">
    </div>
    <div class="hq-grid-2">
      <div class="hq-field-group">
        <label class="hq-field-label">کد داخلی رویداد (Slug)</label>
        <input type="text" name="slug" class="hq-input" placeholder="HQ-2024-018" value="<?= event_h($event['slug']) ?>">
      </div>
      <div class="hq-field-group">
        <label class="hq-field-label">وضعیت انتشار</label>
        <select name="status" class="hq-select">
          <option value="draft" <?= $event['status'] === 'draft' ? 'selected' : '' ?>>پیش‌نویس</option>
          <option value="published" <?= $event['status'] === 'published' ? 'selected' : '' ?>>منتشرشده</option>
          <option value="archived" <?= $event['status'] === 'archived' ? 'selected' : '' ?>>آرشیو</option>
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
  <section class="hq-card">
    <div class="hq-card-header">
      <h2 class="hq-card-title">تاریخ شمسی و ساعت</h2>
      <p class="hq-card-subtitle">تاریخ و ساعت برگزاری را بر اساس تقویم شمسی و ساعت تهران تنظیم کنید.</p>
    </div>
    <div class="hq-grid-3">
      <div class="hq-field-group">
        <label class="hq-field-label">تاریخ برگزاری</label>
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

  <!-- 5. توضیح کوتاه (#8:198) -->
  <section class="hq-card">
    <div class="hq-card-header">
      <h2 class="hq-card-title">توضیح کوتاه</h2>
      <p class="hq-card-subtitle">خلاصه‌ای از رویداد برای نمایش در صفحات اصلی و بنرها.</p>
    </div>
    <div class="hq-field-group">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <label class="hq-field-label">توضیح کوتاه (حداکثر ۱۴۰ کاراکتر)</label>
        <span id="shortDescCounter" style="font-size: 11px; color: #6b7280; font-weight:700;">۰ / ۱۴۰</span>
      </div>
      <input type="text" name="short_description" id="shortDescInput" maxlength="140" class="hq-input" placeholder="همایش ملی مراقبت‌های حمایتی و تسکینی" value="<?= event_h($event['short_description']) ?>">
    </div>
  </section>

  <!-- 6. درباره رویداد (#8:210) -->
  <section class="hq-card">
    <div class="hq-card-header">
      <h2 class="hq-card-title">درباره رویداد</h2>
      <p class="hq-card-subtitle">متن کامل رویداد برای صفحه اختصاصی و بخش‌های توضیحات.</p>
    </div>
    <div class="hq-field-group">
      <label class="hq-field-label">متن کامل درباره رویداد</label>
      <textarea name="about" class="hq-textarea" placeholder="متن توضیحات کامل رویداد..."><?= event_h($event['about']) ?></textarea>
    </div>
  </section>

  <!-- 7. پوستر و فایل‌های همایش (#8:218 & #8:260) -->
  <section class="hq-card">
    <div class="hq-card-header">
      <h2 class="hq-card-title">پوستر و فایل‌های همایش</h2>
      <p class="hq-card-subtitle">پوستر اصلی، فایل PDF برنامه و فایل‌های کمکی را در این بخش مدیریت کنید.</p>
    </div>
    <div class="hq-media-grid">
      <!-- Poster Box -->
      <div class="hq-state-box" id="posterBox">
        <h3 class="hq-state-box-title"><?= $event['poster'] ? 'پوستر آپلود شده' : 'پوستر خالی' ?></h3>
        <?php if ($event['poster']): ?>
          <img src="<?= event_h($event['poster']) ?>" alt="پوستر رویداد" class="hq-poster-preview-img" id="posterImg">
          <div class="hq-file-pills">
            <span class="hq-file-pill"><?= event_h(basename($event['poster'])) ?></span>
            <span class="hq-file-pill">تصویر ثبت‌شده</span>
          </div>
          <div class="hq-btn-row">
            <button type="button" class="hq-btn hq-btn-white" onclick="document.getElementById('posterInput').click()">ویرایش</button>
            <button type="button" class="hq-btn hq-btn-primary" onclick="document.getElementById('posterInput').click()">آپلود جدید</button>
          </div>
        <?php else: ?>
          <div class="hq-dropzone" onclick="document.getElementById('posterInput').click()" id="posterDropzone">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#0d7a87" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"></path>
              <polyline points="12 13 12 17"></polyline>
              <polyline points="9 16 12 13 15 16"></polyline>
            </svg>
            <span class="hq-dropzone-text">فایل پوستر را اینجا بکشید یا کلیک کنید</span>
            <span class="hq-dropzone-hint">حداکثر حجم مجاز: ۱۲ مگابایت</span>
          </div>
          <div class="hq-btn-row">
            <button type="button" class="hq-btn hq-btn-primary" onclick="document.getElementById('posterInput').click()">افزودن پوستر</button>
          </div>
        <?php endif; ?>
        <input type="file" name="poster" id="posterInput" accept="image/*" style="display:none">
      </div>

      <!-- PDF Program Box (#8:260) -->
      <div class="hq-pdf-container" id="pdfBox">
        <div class="hq-pdf-header">
          <span class="hq-pdf-badge">فایل PDF برنامه</span>
          <h3 class="hq-state-box-title" style="margin:0;">فایل PDF برنامه</h3>
        </div>
        <?php if ($event['schedule_pdf']): ?>
          <div style="display:flex; align-items:center; gap:12px; background:#ffffff; border-radius:12px; padding:16px; border:1px solid #e5e7eb;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0d7a87" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <div>
              <div style="font-weight:700; color:#1f2937; font-size:14px;"><?= event_h(basename($event['schedule_pdf'])) ?></div>
              <div style="font-size:12px; color:#4b5563;">فایل PDF ثبت‌شده در سرور</div>
            </div>
          </div>
          <div class="hq-btn-row">
            <a href="<?= event_h($event['schedule_pdf']) ?>" target="_blank" class="hq-btn hq-btn-white">مشاهده PDF</a>
            <button type="button" class="hq-btn hq-btn-primary" onclick="document.getElementById('pdfInput').click()">آپلود جدید</button>
          </div>
        <?php else: ?>
          <div class="hq-dropzone" style="background:#ffffff; min-height:120px;" onclick="document.getElementById('pdfInput').click()" id="pdfDropzone">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0d7a87" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            <span class="hq-dropzone-text">فایل PDF برنامه را آپلود کنید</span>
            <span class="hq-dropzone-hint">حداکثر حجم مجاز: ۲۵ مگابایت</span>
          </div>
          <div class="hq-btn-row">
            <button type="button" class="hq-btn hq-btn-primary" onclick="document.getElementById('pdfInput').click()">آپلود فایل</button>
          </div>
        <?php endif; ?>
        <input type="file" name="schedule_pdf" id="pdfInput" accept="application/pdf" style="display:none">
      </div>
    </div>
  </section>

  <!-- 8. هیروهای اختصاصی تکرارشونده (#8:301) -->
  <section class="hq-card">
    <div class="hq-card-header">
      <h2 class="hq-card-title">هیروهای اختصاصی تکرارشونده</h2>
      <p class="hq-card-subtitle">برای هر هیرو می‌توانید تصویر، عنوان، توضیح، وضعیت فعال و دکمه را مدیریت کنید.</p>
    </div>
    <div class="hq-repeat-grid" id="hero-list">
      <?php foreach ($heroes as $idx => $r): ?>
        <div class="hq-repeat-card">
          <div class="hq-repeat-card-top">
            <span class="hq-repeat-pill-number">هیرو <?= sprintf('%02d', $idx + 1) ?></span>
            <span class="hq-repeat-pill-status">فعال</span>
          </div>
          <div class="hq-repeat-media">
            <?php if (!empty($r['image'])): ?>
              <img src="<?= event_h($r['image']) ?>" alt="<?= rowVal($r, 'title') ?>">
            <?php else: ?>
              <div class="hq-repeat-media-empty">تصویر ثبت نشده</div>
            <?php endif; ?>
          </div>
          <h3 class="hq-repeat-title"><?= rowVal($r, 'title', 'عنوان هیرو') ?></h3>
          <p class="hq-repeat-desc"><?= rowVal($r, 'description', 'توضیحات هیرو...') ?></p>
          <div class="hq-repeat-actions">
            <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">ویرایش</button>
            <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
          </div>
          <div class="hq-repeat-drawer">
            <label class="hq-field-label">عنوان هیرو<input name="hero_title[]" class="hq-input" value="<?= rowVal($r, 'title') ?>"></label>
            <label class="hq-field-label">توضیح<input name="hero_description[]" class="hq-input" value="<?= rowVal($r, 'description') ?>"></label>
            <label class="hq-field-label">متن دکمه<input name="hero_button_label[]" class="hq-input" value="<?= rowVal($r, 'button_label') ?>"></label>
            <label class="hq-field-label">لینک دکمه<input name="hero_link[]" dir="ltr" class="hq-input" value="<?= rowVal($r, 'button_link') ?>"></label>
            <label class="hq-field-label">تصویر جدید<input type="file" name="hero_image[]" accept="image/*" class="hq-input"></label>
            <input type="hidden" name="hero_existing[]" value="<?= rowVal($r, 'image') ?>">
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div>
      <button type="button" class="hq-btn hq-btn-primary" data-add="hero">＋ افزودن هیرو جدید</button>
    </div>
  </section>

  <!-- 9. دبیر علمی و اجرایی (#8:356) -->
  <section class="hq-card">
    <div class="hq-card-header">
      <h2 class="hq-card-title">دبیر علمی و اجرایی</h2>
      <p class="hq-card-subtitle">اطلاعات دبیران علمی و اجرایی رویداد را در این بخش مدیریت کنید.</p>
    </div>
    <div class="hq-repeat-grid" id="person-list">
      <?php foreach ($people as $idx => $r): ?>
        <div class="hq-repeat-card">
          <div class="hq-repeat-card-top">
            <span class="hq-repeat-pill-number"><?= $r['role'] === 'executive_secretary' ? 'دبیر اجرایی' : 'دبیر علمی' ?></span>
            <span class="hq-repeat-pill-status">فعال</span>
          </div>
          <div class="hq-repeat-media">
            <?php if (!empty($r['image'])): ?>
              <img src="<?= event_h($r['image']) ?>" alt="<?= rowVal($r, 'name') ?>">
            <?php else: ?>
              <div class="hq-repeat-media-empty">عکس ثبت نشده</div>
            <?php endif; ?>
          </div>
          <h3 class="hq-repeat-title"><?= rowVal($r, 'name', 'نام دبیر') ?></h3>
          <p class="hq-repeat-desc"><?= rowVal($r, 'title', 'سمت دبیر') ?></p>
          <div class="hq-repeat-actions">
            <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">ویرایش</button>
            <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
          </div>
          <div class="hq-repeat-drawer">
            <label class="hq-field-label">نوع سمت
              <select name="person_role[]" class="hq-select">
                <option value="scientific_secretary" <?= $r['role'] === 'scientific_secretary' ? 'selected' : '' ?>>دبیر علمی</option>
                <option value="executive_secretary" <?= $r['role'] === 'executive_secretary' ? 'selected' : '' ?>>دبیر اجرایی</option>
              </select>
            </label>
            <label class="hq-field-label">نام و نام خانوادگی<input name="person_name[]" class="hq-input" value="<?= rowVal($r, 'name') ?>"></label>
            <label class="hq-field-label">سمت / عنوان<input name="person_title[]" class="hq-input" value="<?= rowVal($r, 'title') ?>"></label>
            <label class="hq-field-label">عکس جدید<input type="file" name="person_image[]" accept="image/*" class="hq-input"></label>
            <input type="hidden" name="person_existing[]" value="<?= rowVal($r, 'image') ?>">
            <input type="hidden" name="person_order[]" value="<?= (int)($r['sort_order'] ?? 0) ?>">
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div>
      <button type="button" class="hq-btn hq-btn-primary" data-add="person">＋ افزودن دبیر جدید</button>
    </div>
  </section>

  <!-- 10. اساتید تکرارشونده (#8:411) -->
  <section class="hq-card">
    <div class="hq-card-header">
      <h2 class="hq-card-title">اساتید تکرارشونده</h2>
      <p class="hq-card-subtitle">اسامی اساتید و سخنرانان را به صورت تکرارشونده مدیریت کنید.</p>
    </div>
    <div class="hq-repeat-grid" id="speaker-list">
      <?php foreach ($speakers as $idx => $r): ?>
        <div class="hq-repeat-card">
          <div class="hq-repeat-card-top">
            <span class="hq-repeat-pill-number">سخنران <?= sprintf('%02d', $idx + 1) ?></span>
            <span class="hq-repeat-pill-status">فعال</span>
          </div>
          <div class="hq-repeat-media">
            <?php if (!empty($r['image'])): ?>
              <img src="<?= event_h($r['image']) ?>" alt="<?= rowVal($r, 'name') ?>">
            <?php else: ?>
              <div class="hq-repeat-media-empty">عکس ثبت نشده</div>
            <?php endif; ?>
          </div>
          <h3 class="hq-repeat-title"><?= rowVal($r, 'name', 'نام استاد') ?></h3>
          <p class="hq-repeat-desc"><?= rowVal($r, 'title', 'تخصص / سمت') ?></p>
          <div class="hq-repeat-actions">
            <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">ویرایش</button>
            <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
          </div>
          <div class="hq-repeat-drawer">
            <label class="hq-field-label">نام استاد<input name="speaker_name[]" class="hq-input" value="<?= rowVal($r, 'name') ?>"></label>
            <label class="hq-field-label">سمت / تخصص<input name="speaker_title[]" class="hq-input" value="<?= rowVal($r, 'title') ?>"></label>
            <label class="hq-field-label">ترتیب نمایش<input name="speaker_order[]" type="text" dir="ltr" class="hq-input" value="<?= rowVal($r, 'sort_order', '0') ?>"></label>
            <label class="hq-field-label">عکس جدید<input type="file" name="speaker_image[]" accept="image/*" class="hq-input"></label>
            <input type="hidden" name="speaker_existing[]" value="<?= rowVal($r, 'image') ?>">
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div>
      <button type="button" class="hq-btn hq-btn-primary" data-add="speaker">＋ افزودن سخنران جدید</button>
    </div>
  </section>

  <!-- 11. همراهان تکرارشونده (#8:466) -->
  <section class="hq-card">
    <div class="hq-card-header">
      <h2 class="hq-card-title">همراهان تکرارشونده</h2>
      <p class="hq-card-subtitle">همراهان و همکاران رویداد را به صورت تکرارشونده مدیریت کنید.</p>
    </div>
    <div class="hq-repeat-grid" id="partner-list">
      <?php foreach ($partners as $idx => $r): ?>
        <div class="hq-repeat-card">
          <div class="hq-repeat-card-top">
            <span class="hq-repeat-pill-number">همراه <?= sprintf('%02d', $idx + 1) ?></span>
            <span class="hq-repeat-pill-status">فعال</span>
          </div>
          <div class="hq-repeat-media" style="background:#ffffff; padding:16px;">
            <?php if (!empty($r['logo'])): ?>
              <img src="<?= event_h($r['logo']) ?>" alt="<?= rowVal($r, 'name') ?>" style="object-fit:contain;">
            <?php else: ?>
              <div class="hq-repeat-media-empty">لوگو ثبت نشده</div>
            <?php endif; ?>
          </div>
          <h3 class="hq-repeat-title"><?= rowVal($r, 'name', 'نام سازمان / شرکت') ?></h3>
          <div class="hq-repeat-actions">
            <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">ویرایش</button>
            <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
          </div>
          <div class="hq-repeat-drawer">
            <label class="hq-field-label">نام شرکت / سازمان<input name="partner_name[]" class="hq-input" value="<?= rowVal($r, 'name') ?>"></label>
            <label class="hq-field-label">ترتیب نمایش<input name="partner_order[]" type="text" dir="ltr" class="hq-input" value="<?= rowVal($r, 'sort_order', '0') ?>"></label>
            <label class="hq-field-label">لوگو جدید<input type="file" name="partner_logo[]" accept="image/*" class="hq-input"></label>
            <input type="hidden" name="partner_existing[]" value="<?= rowVal($r, 'logo') ?>">
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div>
      <button type="button" class="hq-btn hq-btn-primary" data-add="partner">＋ افزودن همراه جدید</button>
    </div>
  </section>

  <!-- 12. ایجاد و ویرایش اخبار اختصاصی رویداد (#8:521) -->
  <section class="hq-card">
    <div class="hq-card-header">
      <h2 class="hq-card-title">ایجاد و ویرایش اخبار اختصاصی رویداد</h2>
      <p class="hq-card-subtitle">اخبار و اطلاعیه‌های اختصاصی رویداد را در این بخش مدیریت کنید.</p>
    </div>
    <?php if ($id && !empty($news)): ?>
      <div class="hq-repeat-grid">
        <?php foreach ($news as $idx => $n): ?>
          <div class="hq-repeat-card">
            <div class="hq-repeat-card-top">
              <span class="hq-repeat-pill-number">خبر <?= sprintf('%02d', $idx + 1) ?></span>
              <span class="hq-repeat-pill-status <?= ($n['status'] ?? 'published') === 'published' ? '' : 'off' ?>">
                <?= ($n['status'] ?? 'published') === 'published' ? 'فعال' : 'پیش‌نویس' ?>
              </span>
            </div>
            <div class="hq-repeat-media">
              <?php if (!empty($n['image'])): ?>
                <img src="<?= event_h($n['image']) ?>" alt="<?= event_h($n['title']) ?>">
              <?php else: ?>
                <div class="hq-repeat-media-empty">تصویر خبر</div>
              <?php endif; ?>
            </div>
            <h3 class="hq-repeat-title"><?= event_h($n['title']) ?></h3>
            <p class="hq-repeat-desc"><?= event_h(mb_substr(strip_tags((string)($n['summary'] ?? $n['content'] ?? '')), 0, 90)) ?>...</p>
            <div class="hq-repeat-actions">
              <a href="event-news-create.php?id=<?= (int)$n['id'] ?>&event_id=<?= $id ?>" class="hq-btn hq-btn-white">ویرایش خبر</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div>
      <?php if ($id): ?>
        <a href="event-news-create.php?event_id=<?= $id ?>" class="hq-btn hq-btn-primary">＋ افزودن خبر جدید</a>
      <?php else: ?>
        <p style="color:#6b7280; font-size:13px; margin:0;">پس از ذخیره اولیه رویداد، می‌توانید اخبار اختصاصی را به آن متصل کنید.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- 13. تنظیمات بنر سراسری (#8:576) -->
  <section class="hq-card">
    <div class="hq-card-header">
      <h2 class="hq-card-title">تنظیمات بنر سراسری</h2>
      <p class="hq-card-subtitle">تنظیمات نمایش بنر سراسری و شمارش معکوس را در این بخش مدیریت کنید.</p>
    </div>

    <label class="hq-check-card">
      <input type="checkbox" name="banner_active" value="1" <?= (int)$event['banner_active'] ? 'checked' : '' ?>>
      <span>نمایش بنر این رویداد در بالای تمام صفحات وب‌سایت</span>
    </label>

    <div class="hq-field-group">
      <label class="hq-field-label">پیش‌نمایش زنده بنر بالای سایت</label>
      <div id="bannerPreview" class="hq-banner-preview-box <?= event_h($event['banner_theme']) ?>">
        <span data-preview="label" style="font-weight:700; opacity:0.9;"><?= event_h($event['banner_label']) ?></span>
        <strong data-preview="title" style="font-size:14px;"><?= event_h($event['title'] ?: 'عنوان رویداد') ?></strong>
        <span style="font-size:12px; font-weight:700; background:rgba(255,255,255,0.15); padding:4px 10px; border-radius:6px;">۱۲ روز · ۰۸ ساعت · ۲۴ دقیقه</span>
        <button type="button" class="hq-banner-cta-btn" data-preview="cta"><?= event_h($event['banner_cta']) ?></button>
        <button type="button" class="hq-banner-close-btn" aria-label="بستن">×</button>
      </div>
    </div>

    <div class="hq-grid-2">
      <div class="hq-field-group">
        <label class="hq-field-label">متن کوچک بنر</label>
        <input type="text" name="banner_label" data-banner-preview="label" class="hq-input" value="<?= event_h($event['banner_label']) ?>">
      </div>
      <div class="hq-field-group">
        <label class="hq-field-label">متن دکمه اکشن (CTA)</label>
        <input type="text" name="banner_cta" data-banner-preview="cta" class="hq-input" value="<?= event_h($event['banner_cta']) ?>">
      </div>
    </div>

    <div class="hq-grid-2">
      <div class="hq-field-group">
        <label class="hq-field-label">لینک دکمه بنر</label>
        <input type="url" name="banner_link" dir="ltr" class="hq-input" placeholder="https://..." value="<?= event_h($event['banner_link']) ?>">
      </div>
      <div class="hq-field-group">
        <label class="hq-field-label">تم رنگی بنر</label>
        <select name="banner_theme" data-banner-preview="theme" class="hq-select">
          <option value="teal" <?= $event['banner_theme'] === 'teal' ? 'selected' : '' ?>>فیروزه‌ای مکسا</option>
          <option value="amber" <?= $event['banner_theme'] === 'amber' ? 'selected' : '' ?>>کهربایی</option>
          <option value="dark" <?= $event['banner_theme'] === 'dark' ? 'selected' : '' ?>>تیره و رسمی</option>
        </select>
      </div>
    </div>

    <!-- Custom Hex Pickers -->
    <div style="display:flex; gap:16px; flex-wrap:wrap; margin-top:8px;" id="customColorsContainer">
      <div style="display:flex; align-items:center; gap:8px;">
        <input type="color" id="pickerBg" value="<?= event_h($event['banner_background']) ?>" style="width:36px; height:36px; border:none; border-radius:6px; cursor:pointer;">
        <span style="font-size:12px; font-weight:700; color:#4b5563;">رنگ زمینه:</span>
        <input type="text" name="banner_background" id="textBg" value="<?= event_h($event['banner_background']) ?>" maxlength="7" dir="ltr" style="width:80px; font-size:12px;" class="hq-input">
      </div>
      <div style="display:flex; align-items:center; gap:8px;">
        <input type="color" id="pickerText" value="<?= event_h($event['banner_text_color']) ?>" style="width:36px; height:36px; border:none; border-radius:6px; cursor:pointer;">
        <span style="font-size:12px; font-weight:700; color:#4b5563;">رنگ متن:</span>
        <input type="text" name="banner_text_color" id="textText" value="<?= event_h($event['banner_text_color']) ?>" maxlength="7" dir="ltr" style="width:80px; font-size:12px;" class="hq-input">
      </div>
      <div style="display:flex; align-items:center; gap:8px;">
        <input type="color" id="pickerAccent" value="<?= event_h($event['banner_accent_color']) ?>" style="width:36px; height:36px; border:none; border-radius:6px; cursor:pointer;">
        <span style="font-size:12px; font-weight:700; color:#4b5563;">رنگ تأکید:</span>
        <input type="text" name="banner_accent_color" id="textAccent" value="<?= event_h($event['banner_accent_color']) ?>" maxlength="7" dir="ltr" style="width:80px; font-size:12px;" class="hq-input">
      </div>
    </div>

    <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:#4b5563; cursor:pointer; margin-top:8px;">
      <input type="checkbox" name="banner_dismissible" value="1" <?= (int)$event['banner_dismissible'] ? 'checked' : '' ?> style="accent-color:#0d7a87;">
      امکان بستن موقت بنر توسط کاربران سایت
    </label>

    <div class="hq-note-box" style="background:#f3f4f6; color:#4b5563;">
      <span>نکته مهم: فقط یک رویداد می‌تواند بنر سراسری فعال داشته باشد. در صورت فعال‌سازی برای رویداد دیگری، بنر فعلی غیرفعال می‌شود.</span>
    </div>
  </section>

  <!-- 14. دکمه‌های پایین فرم (#8:629) -->
  <footer class="hq-actions-bar">
    <a href="event-list.php" class="hq-btn hq-btn-white" style="border-color:#4b5563; color:#4b5563; padding:10px 24px;">انصراف</a>
    <div class="hq-actions-left">
      <?php if ($event['slug']): ?>
        <a href="/event.php?slug=<?= urlencode($event['slug']) ?>" target="_blank" class="hq-btn hq-btn-white">پیش‌نمایش</a>
      <?php else: ?>
        <button type="button" class="hq-btn hq-btn-white" onclick="alert('برای مشاهده پیش‌نمایش ابتدا رویداد را ذخیره نمایید.')">پیش‌نمایش</button>
      <?php endif; ?>
      <button type="submit" class="hq-btn hq-btn-primary" style="padding:10px 28px; font-size:14px;">ذخیره تغییرات رویداد</button>
    </div>
  </footer>
</form>

<script>
function toggleDrawer(btn) {
  const card = btn.closest('.hq-repeat-card');
  const drawer = card.querySelector('.hq-repeat-drawer');
  if (drawer) {
    drawer.classList.toggle('is-open');
    btn.textContent = drawer.classList.contains('is-open') ? 'بستن ویرایش' : 'ویرایش';
  }
}

(function() {
  // Character counter for short description
  const shortDesc = document.getElementById('shortDescInput');
  const counter = document.getElementById('shortDescCounter');
  if (shortDesc && counter) {
    const updateCount = () => {
      counter.textContent = String(shortDesc.value.length).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]) + ' / ۱۴۰';
    };
    shortDesc.addEventListener('input', updateCount);
    updateCount();
  }

  // Live banner preview sync
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

  // File Upload Handlers (Poster & PDF)
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
          let title = posterBox.querySelector('.hq-state-box-title');
          if (title) title.textContent = 'پوستر در حال ویرایش';
        };
        reader.readAsDataURL(file);
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
          drop.innerHTML = '<span class="hq-dropzone-text" style="color:#16a37a;">فایل ' + file.name + ' انتخاب شد</span>';
        }
      }
    });
  }

  // Repeater Templates & Logic
  const templates = {
    hero: () => `
      <div class="hq-repeat-card">
        <div class="hq-repeat-card-top">
          <span class="hq-repeat-pill-number">هیرو جدید</span>
          <span class="hq-repeat-pill-status">فعال</span>
        </div>
        <div class="hq-repeat-media">
          <div class="hq-repeat-media-empty">تصویر هیرو جدید</div>
        </div>
        <h3 class="hq-repeat-title">عنوان هیرو جدید</h3>
        <p class="hq-repeat-desc">توضیحات این بخش هیرو...</p>
        <div class="hq-repeat-actions">
          <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">بستن ویرایش</button>
          <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
        </div>
        <div class="hq-repeat-drawer is-open">
          <label class="hq-field-label">عنوان هیرو<input name="hero_title[]" class="hq-input" placeholder="عنوان هیرو"></label>
          <label class="hq-field-label">توضیح<input name="hero_description[]" class="hq-input" placeholder="توضیح هیرو"></label>
          <label class="hq-field-label">متن دکمه<input name="hero_button_label[]" class="hq-input" placeholder="مشاهده بیشتر"></label>
          <label class="hq-field-label">لینک دکمه<input name="hero_link[]" dir="ltr" class="hq-input" placeholder="https://..."></label>
          <label class="hq-field-label">تصویر هیرو<input type="file" name="hero_image[]" accept="image/*" class="hq-input"></label>
          <input type="hidden" name="hero_existing[]" value="">
        </div>
      </div>`,
    person: () => `
      <div class="hq-repeat-card">
        <div class="hq-repeat-card-top">
          <span class="hq-repeat-pill-number">دبیر جدید</span>
          <span class="hq-repeat-pill-status">فعال</span>
        </div>
        <div class="hq-repeat-media">
          <div class="hq-repeat-media-empty">عکس دبیر</div>
        </div>
        <h3 class="hq-repeat-title">نام دبیر جدید</h3>
        <p class="hq-repeat-desc">سمت دبیر...</p>
        <div class="hq-repeat-actions">
          <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">بستن ویرایش</button>
          <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
        </div>
        <div class="hq-repeat-drawer is-open">
          <label class="hq-field-label">نوع سمت
            <select name="person_role[]" class="hq-select">
              <option value="scientific_secretary">دبیر علمی</option>
              <option value="executive_secretary">دبیر اجرایی</option>
            </select>
          </label>
          <label class="hq-field-label">نام و نام خانوادگی<input name="person_name[]" class="hq-input" placeholder="دکتر ..."></label>
          <label class="hq-field-label">سمت / عنوان<input name="person_title[]" class="hq-input" placeholder="دبیر علمی همایش..."></label>
          <label class="hq-field-label">عکس<input type="file" name="person_image[]" accept="image/*" class="hq-input"></label>
          <input type="hidden" name="person_existing[]" value="">
          <input type="hidden" name="person_order[]" value="0">
        </div>
      </div>`,
    speaker: () => `
      <div class="hq-repeat-card">
        <div class="hq-repeat-card-top">
          <span class="hq-repeat-pill-number">سخنران جدید</span>
          <span class="hq-repeat-pill-status">فعال</span>
        </div>
        <div class="hq-repeat-media">
          <div class="hq-repeat-media-empty">عکس سخنران</div>
        </div>
        <h3 class="hq-repeat-title">نام استاد / سخنران</h3>
        <p class="hq-repeat-desc">تخصص / موضوع سخنرانی...</p>
        <div class="hq-repeat-actions">
          <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">بستن ویرایش</button>
          <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
        </div>
        <div class="hq-repeat-drawer is-open">
          <label class="hq-field-label">نام استاد<input name="speaker_name[]" class="hq-input" placeholder="دکتر ..."></label>
          <label class="hq-field-label">سمت / تخصص<input name="speaker_title[]" class="hq-input" placeholder="متخصص ..."></label>
          <label class="hq-field-label">ترتیب نمایش<input name="speaker_order[]" type="text" dir="ltr" class="hq-input" value="0"></label>
          <label class="hq-field-label">عکس<input type="file" name="speaker_image[]" accept="image/*" class="hq-input"></label>
          <input type="hidden" name="speaker_existing[]" value="">
        </div>
      </div>`,
    partner: () => `
      <div class="hq-repeat-card">
        <div class="hq-repeat-card-top">
          <span class="hq-repeat-pill-number">همراه جدید</span>
          <span class="hq-repeat-pill-status">فعال</span>
        </div>
        <div class="hq-repeat-media" style="background:#ffffff; padding:16px;">
          <div class="hq-repeat-media-empty">لوگو همراه</div>
        </div>
        <h3 class="hq-repeat-title">نام سازمان / شرکت</h3>
        <div class="hq-repeat-actions">
          <button type="button" class="hq-btn hq-btn-white" onclick="toggleDrawer(this)">بستن ویرایش</button>
          <button type="button" class="hq-btn hq-btn-danger remove-row">حذف</button>
        </div>
        <div class="hq-repeat-drawer is-open">
          <label class="hq-field-label">نام شرکت / سازمان<input name="partner_name[]" class="hq-input" placeholder="شرکت دارویی..."></label>
          <label class="hq-field-label">ترتیب نمایش<input name="partner_order[]" type="text" dir="ltr" class="hq-input" value="0"></label>
          <label class="hq-field-label">لوگو<input type="file" name="partner_logo[]" accept="image/*" class="hq-input"></label>
          <input type="hidden" name="partner_existing[]" value="">
        </div>
      </div>`
  };

  document.addEventListener('click', e => {
    const addBtn = e.target.closest('[data-add]');
    if (addBtn) {
      const type = addBtn.dataset.add;
      const list = document.getElementById(type + '-list');
      if (list && templates[type]) {
        list.insertAdjacentHTML('beforeend', templates[type]());
      }
    }
    const remBtn = e.target.closest('.remove-row');
    if (remBtn) {
      const card = remBtn.closest('.hq-repeat-card');
      if (card && confirm('آیا از حذف این مورد اطمینان دارید؟')) {
        card.remove();
      }
    }
  });
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

<?php
declare(strict_types=1);

require_once __DIR__ . '/_guard.php';
dash_require('events');
dash_require_hq();
require_once __DIR__ . '/../event-lib.php';

$pdo = dash_pdo();

// Handle Form Submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $eventId = (int)($_POST['event_id'] ?? 0);
    $bannerActive = !empty($_POST['banner_active']) ? 1 : 0;
    $bannerTitle = trim((string)($_POST['banner_title'] ?? ''));
    $bannerLabel = trim((string)($_POST['banner_label'] ?? 'رویداد ویژه جاری'));
    $bannerCta = trim((string)($_POST['banner_cta'] ?? 'ثبت‌نام مستقیم'));
    $bannerLink = trim((string)($_POST['banner_link'] ?? ''));
    $bannerBg = trim((string)($_POST['banner_background'] ?? '#0A5C66'));
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $bannerBg)) {
        $bannerBg = '#0A5C66';
    }
    $bannerDismissible = !empty($_POST['banner_dismissible']) ? 1 : 0;

    if ($eventId > 0) {
        if ($bannerActive === 1) {
            // Global Rule: Only one event can have active banner at a time.
            // Deactivate all others first
            $pdo->prepare("UPDATE events SET banner_active = 0 WHERE id != ?")->execute([$eventId]);
        }

        // Update the target event
        $st = $pdo->prepare("UPDATE events SET 
            banner_active = ?, 
            banner_label = ?, 
            banner_cta = ?, 
            banner_link = ?, 
            banner_background = ?, 
            banner_dismissible = ?" . ($bannerTitle !== '' ? ", title = ?" : "") . "
            WHERE id = ?");

        $params = [
            $bannerActive,
            $bannerLabel,
            $bannerCta,
            $bannerLink,
            $bannerBg,
            $bannerDismissible
        ];
        if ($bannerTitle !== '') {
            $params[] = $bannerTitle;
        }
        $params[] = $eventId;

        $st->execute($params);

        $message = $bannerActive === 1 
            ? 'بنر سراسری رویداد با موفقیت ذخیره و در بالای تمام صفحات وب‌سایت فعال گردید.'
            : 'تنظیمات ذخیره شد. نمایش بنر سراسری غیرفعال گردید.';
        $messageType = 'success';
    } else {
        $message = 'لطفاً یک رویداد معتبر را انتخاب نمایید.';
        $messageType = 'danger';
    }
}

// Fetch all available published/upcoming events
$events = $pdo->query("SELECT id, title, slug, event_date, start_time, banner_active, banner_label, banner_cta, banner_link, banner_background, banner_dismissible FROM events WHERE status != 'archived' ORDER BY event_date DESC, id DESC")->fetchAll();

// Identify currently active banner event, or default to the first event
$activeEvent = null;
foreach ($events as $ev) {
    if ((int)$ev['banner_active'] === 1) {
        $activeEvent = $ev;
        break;
    }
}
if (!$activeEvent && !empty($events)) {
    $activeEvent = $events[0];
}

$PANEL_TITLE = 'تنظیمات بنر سراسری هوشمند';
require __DIR__ . '/_panel_head.php';
?>

<style>
/* ============================================================================
   Figma Node 8:648 - Global Banner Settings (Designed Width: 1440px)
   ============================================================================ */
.hq-global-wrapper {
  max-width: 1360px;
  margin: 0 auto;
  padding: 8px 12px 48px;
  direction: rtl;
  font-family: 'Vazirmatn', sans-serif;
}

/* Header & Status Badges (#8:649) */
.hq-header-card {
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-radius: 18px;
  padding: 24px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 18px;
  margin-bottom: 28px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}
.hq-status-group {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.hq-badge-pill {
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 12.5px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.hq-badge-hq {
  background: #0D7A87;
  color: #FFFFFF;
}
.hq-badge-locked {
  background: #F3F4F6;
  border: 1px solid #E5E7EB;
  color: #4B5563;
}
.hq-badge-branches {
  background: #FEE2E2;
  border: 1px solid #FCA5A5;
  color: #DC2626;
}
.hq-header-titles {
  display: flex;
  flex-direction: column;
  gap: 4px;
  text-align: right;
}
.hq-main-title {
  font-size: 22px;
  font-weight: 800;
  color: #1F2937;
  margin: 0;
}
.hq-main-sub {
  font-size: 13px;
  color: #6B7280;
  margin: 0;
}

/* Two-Column Master Layout (#8:660) */
.hq-content-grid {
  display: grid;
  grid-template-columns: 480px 1fr;
  gap: 28px;
  align-items: start;
}
@media (max-width: 1080px) {
  .hq-content-grid {
    grid-template-columns: 1fr;
  }
}

/* LEFT COLUMN: Specimen Preview & System Rules */
.hq-preview-col {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.hq-specimen-card {
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 22px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
}
.hq-specimen-title {
  font-size: 15px;
  font-weight: 800;
  color: #1F2937;
  margin: 0;
}

/* The Live Specimen Top Banner Bar (#8:664) */
.live-specimen-banner {
  background: <?= event_h($activeEvent['banner_background'] ?? '#0A5C66') ?>;
  border-radius: 10px;
  padding: 10px 14px;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  transition: background 0.3s ease;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
  min-height: 48px;
}
.live-specimen-right {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}
.specimen-badge {
  background: #F0FDFA;
  color: #0A5C66;
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 800;
  white-space: nowrap;
}
.specimen-title {
  font-size: 12.5px;
  font-weight: 700;
  color: #FFFFFF;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 170px;
}
.live-specimen-center {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  color: #FFFFFF;
  white-space: nowrap;
}
.specimen-countdown-val {
  color: #F5A623;
  font-weight: 800;
}
.live-specimen-left {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}
.specimen-cta-btn {
  background: #D97706;
  color: #FFFFFF;
  border-radius: 6px;
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 800;
  text-decoration: none;
  white-space: nowrap;
}
.specimen-dismiss-btn {
  color: #FFFFFF;
  opacity: 0.8;
  font-size: 16px;
  cursor: pointer;
  line-height: 1;
}

.specimen-note {
  font-size: 12px;
  color: #6B7280;
  line-height: 1.6;
  margin: 0;
}

/* System Rule & Limitation Box (#8:681) */
.hq-rule-card {
  background: #FFFBEB;
  border: 1px solid #D97706;
  border-radius: 16px;
  padding: 22px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.hq-rule-title {
  font-size: 14.5px;
  font-weight: 800;
  color: #B45309;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}
.hq-rule-desc {
  font-size: 13px;
  color: #78350F;
  line-height: 1.7;
  margin: 0;
}

/* RIGHT COLUMN: Settings Form (#8:685) */
.hq-admin-card {
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 28px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
}
.hq-card-title {
  font-size: 17.5px;
  font-weight: 800;
  color: #1F2937;
  margin: 0;
  border-bottom: 1px solid #F3F4F6;
  padding-bottom: 16px;
}

.hq-form-rows {
  display: flex;
  flex-direction: column;
  gap: 22px;
}

.hq-form-row {
  display: grid;
  grid-template-columns: 1fr 220px;
  gap: 18px;
  align-items: start;
}
@media (max-width: 720px) {
  .hq-form-row {
    grid-template-columns: 1fr;
  }
}

.hq-field-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.hq-field-label {
  font-size: 13px;
  font-weight: 700;
  color: #374151;
}
.hq-input-styled, .hq-select-styled {
  width: 100%;
  padding: 11px 14px;
  border: 1px solid #D1D5DB;
  border-radius: 10px;
  font-size: 13.5px;
  font-family: inherit;
  color: #1F2937;
  background: #FFFFFF;
  transition: all 0.2s ease;
  box-sizing: border-box;
}
.hq-input-styled:focus, .hq-select-styled:focus {
  outline: none;
  border-color: #0D7A87;
  box-shadow: 0 0 0 3px rgba(13, 122, 135, 0.12);
}

/* Toggle Switch Control (#8:698) */
.hq-toggle-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  border: 1.5px solid #0D7A87;
  border-radius: 10px;
  background: #F0FDFA;
  cursor: pointer;
  user-select: none;
  height: 44px;
  box-sizing: border-box;
}
.hq-toggle-label {
  font-size: 13px;
  font-weight: 800;
  color: #0A5C66;
}
.hq-toggle-pill {
  width: 40px;
  height: 22px;
  background: #0D7A87;
  border-radius: 999px;
  position: relative;
  transition: background 0.25s ease;
}
.hq-toggle-knob {
  width: 16px;
  height: 16px;
  background: #FFFFFF;
  border-radius: 50%;
  position: absolute;
  top: 3px;
  right: 4px;
  transition: transform 0.25s ease;
}
.hq-toggle-container.is-off {
  border-color: #D1D5DB;
  background: #F9FAFB;
}
.hq-toggle-container.is-off .hq-toggle-label {
  color: #6B7280;
}
.hq-toggle-container.is-off .hq-toggle-pill {
  background: #D1D5DB;
}
.hq-toggle-container.is-off .hq-toggle-knob {
  transform: translateX(-16px);
}

/* Color Palettes (#8:723) */
.hq-color-selector {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
  padding: 4px 0;
}
.color-dot-group {
  display: flex;
  align-items: center;
  gap: 10px;
}
.color-dot-btn {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 2px solid #E5E7EB;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  outline: none;
}
.color-dot-btn:hover {
  transform: scale(1.1);
}
.color-dot-btn.is-active {
  border-color: #0D7A87;
  box-shadow: 0 0 0 3px rgba(13, 122, 135, 0.35);
  transform: scale(1.08);
}
.color-palette-label {
  font-size: 13px;
  color: #4B5563;
  font-weight: 600;
}

/* Dismissible Checkbox Control (#8:732) */
.hq-checkbox-control {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 0;
  cursor: pointer;
  user-select: none;
}
.hq-checkbox-control input {
  width: 18px;
  height: 18px;
  accent-color: #0D7A87;
  cursor: pointer;
}
.hq-checkbox-control span {
  font-size: 13px;
  color: #374151;
  font-weight: 600;
}

/* Form Actions Row (#8:735) */
.hq-actions-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 24px;
  border-top: 1px solid #F3F4F6;
  gap: 14px;
  flex-wrap: wrap;
}
.hq-actions-left {
  display: flex;
  align-items: center;
  gap: 12px;
}
.hq-btn-cancel {
  padding: 10px 22px;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  color: #4B5563;
  font-size: 13.5px;
  font-weight: 700;
  background: #FFFFFF;
  text-decoration: none;
  transition: all 0.2s;
}
.hq-btn-cancel:hover {
  background: #F9FAFB;
  color: #1F2937;
}
.hq-btn-archive {
  padding: 10px 18px;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  color: #4B5563;
  font-size: 13.5px;
  font-weight: 700;
  background: #FFFFFF;
  cursor: pointer;
  transition: all 0.2s;
}
.hq-btn-archive:hover {
  background: #F3F4F6;
}
.hq-btn-save {
  padding: 11px 26px;
  background: #0D7A87;
  color: #FFFFFF;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(13, 122, 135, 0.25);
  transition: all 0.2s;
}
.hq-btn-save:hover {
  background: #0A5C66;
  box-shadow: 0 6px 18px rgba(13, 122, 135, 0.35);
  transform: translateY(-1px);
}

/* Alert Notification */
.hq-alert {
  padding: 14px 18px;
  border-radius: 12px;
  font-size: 13.5px;
  font-weight: 700;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.hq-alert-success {
  background: #ECFDF5;
  color: #065F46;
  border: 1px solid #A7F3D0;
}
.hq-alert-danger {
  background: #FEF2F2;
  color: #991B1B;
  border: 1px solid #FECACA;
}
</style>

<div class="hq-global-wrapper">

  <?php if (!empty($message)): ?>
    <div class="hq-alert hq-alert-<?= $messageType ?>">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      <span><?= event_h($message) ?></span>
    </div>
  <?php endif; ?>

  <!-- 1. Header & HQ Badges (Figma Node #8:649) -->
  <div class="hq-header-card">
    <div class="hq-status-group">
      <span class="hq-badge-pill hq-badge-hq">ستاد مرکزی (HQ)</span>
      <span class="hq-badge-pill hq-badge-locked">دسترسی اختصاصی</span>
      <span class="hq-badge-pill hq-badge-branches">شعب سراسر کشور: غیرمجاز / قفل‌شده 🔒</span>
    </div>
    <div class="hq-header-titles">
      <h1 class="hq-main-title">تنظیمات بنر سراسری هوشمند</h1>
      <p class="hq-main-sub">مدیریت نمایش بنر شمارش معکوس و اطلاع‌رسانی همایش‌ها در بالاترین بخش سایت</p>
    </div>
  </div>

  <form method="post" id="bannerConfigForm">
    <?= csrf_field() ?>
    <input type="hidden" name="banner_active" id="inputBannerActive" value="<?= (int)($activeEvent['banner_active'] ?? 0) ?>">
    <input type="hidden" name="banner_background" id="inputBannerBg" value="<?= event_h($activeEvent['banner_background'] ?? '#0A5C66') ?>">

    <!-- 2. Two-Column Main Content (Figma Node #8:660) -->
    <div class="hq-content-grid">

      <!-- LEFT COLUMN: Live Specimen Preview & System Rule (#8:661) -->
      <div class="hq-preview-col">
        
        <!-- Live Specimen Card (#8:662) -->
        <div class="hq-specimen-card">
          <h3 class="hq-specimen-title">پیش‌نمایش زنده بنر دسکتاپ</h3>
          
          <div class="live-specimen-banner" id="specimenBannerBox" style="background: <?= event_h($activeEvent['banner_background'] ?? '#0A5C66') ?>;">
            <div class="live-specimen-right">
              <span class="specimen-badge" id="specimenBadgeText"><?= event_h($activeEvent['banner_label'] ?: 'رویداد ویژه') ?></span>
              <span class="specimen-title" id="specimenTitleText"><?= event_h($activeEvent['title'] ?: 'عنوان رویداد انتخاب‌شده') ?></span>
            </div>

            <div class="live-specimen-center">
              <span>مانده تا آغاز:</span>
              <span class="specimen-countdown-val">۲۴ روز و ۱۸ ساعت</span>
            </div>

            <div class="live-specimen-left">
              <span class="specimen-cta-btn" id="specimenCtaText"><?= event_h($activeEvent['banner_cta'] ?: 'ثبت‌نام مستقیم') ?></span>
              <span class="specimen-dismiss-btn" id="specimenDismissBtn" style="<?= empty($activeEvent['banner_dismissible']) ? 'display:none;' : '' ?>">×</span>
            </div>
          </div>

          <p class="specimen-note">
            * این بنر به‌صورت رسپانسیو طراحی شده و در موبایل المان‌های ثانویه آن خودکار حذف می‌شوند.
          </p>
        </div>

        <!-- System Rule & Limitation Warning Card (#8:681) -->
        <div class="hq-rule-card">
          <h4 class="hq-rule-title">
            <span>قانون سراسری و محدودیت سیستم</span>
            <span>⚠️</span>
          </h4>
          <p class="hq-rule-desc">
            فقط یک رویداد فعال می‌تواند در هر لحظه بنر سراسری داشته باشد. فعال‌سازی بنر برای هر همایش، خودکار بنر همایش قبلی را متوقف و آرشیو می‌کند.
          </p>
        </div>

      </div>

      <!-- RIGHT COLUMN: Content & Behavior Settings Card (#8:684) -->
      <div class="hq-admin-card">
        <h2 class="hq-card-title">تنظیمات محتوا و رفتار بنر</h2>

        <div class="hq-form-rows">
          
          <!-- Row 1: Event Selector & Active Switch (#8:689) -->
          <div class="hq-form-row">
            <div class="hq-field-group">
              <label class="hq-field-label" for="eventSelector">انتخاب رویداد مرجع برای بنر و شمارش معکوس</label>
              <select name="event_id" id="eventSelector" class="hq-select-styled">
                <?php foreach ($events as $ev): ?>
                  <?php
                    $isSel = $activeEvent && (int)$ev['id'] === (int)$activeEvent['id'];
                    $hasBanner = (int)$ev['banner_active'] === 1;
                  ?>
                  <option value="<?= (int)$ev['id'] ?>" 
                          <?= $isSel ? 'selected' : '' ?>
                          data-title="<?= event_h($ev['title']) ?>"
                          data-label="<?= event_h($ev['banner_label'] ?: 'رویداد ویژه جاری') ?>"
                          data-cta="<?= event_h($ev['banner_cta'] ?: 'ثبت‌نام مستقیم') ?>"
                          data-link="<?= event_h($ev['banner_link'] ?: '/event.php?slug=' . rawurlencode((string)$ev['slug'])) ?>"
                          data-bg="<?= event_h($ev['banner_background'] ?: '#0A5C66') ?>"
                          data-active="<?= (int)$ev['banner_active'] ?>"
                          data-dismiss="<?= (int)($ev['banner_dismissible'] ?? 1) ?>">
                    <?= event_h($ev['title']) ?> <?= $hasBanner ? '★ (بنر فعال جاری)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="hq-field-group">
              <label class="hq-field-label">وضعیت نمایش بنر</label>
              <div class="hq-toggle-container <?= empty($activeEvent['banner_active']) ? 'is-off' : '' ?>" id="bannerActiveToggle">
                <span class="hq-toggle-label" id="toggleStatusText"><?= !empty($activeEvent['banner_active']) ? 'فعال و روشن' : 'خاموش و غیرفعال' ?></span>
                <div class="hq-toggle-pill">
                  <div class="hq-toggle-knob"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 2: Banner Title & Badge Label (#8:702) -->
          <div class="hq-form-row">
            <div class="hq-field-group">
              <label class="hq-field-label" for="inputBannerTitle">عنوان بنر (تا حداکثر ۷۰ کاراکتر)</label>
              <input type="text" name="banner_title" id="inputBannerTitle" maxlength="70" class="hq-input-styled" value="<?= event_h($activeEvent['title'] ?? '') ?>">
            </div>

            <div class="hq-field-group">
              <label class="hq-field-label" for="inputBannerLabel">متن برچسب (Badge)</label>
              <input type="text" name="banner_label" id="inputBannerLabel" class="hq-input-styled" value="<?= event_h($activeEvent['banner_label'] ?: 'رویداد ویژه جاری') ?>">
            </div>
          </div>

          <!-- Row 3: CTA URL & CTA Label (#8:711) -->
          <div class="hq-form-row">
            <div class="hq-field-group">
              <label class="hq-field-label" for="inputBannerLink">لینک کلیک دکمه (URL)</label>
              <input type="text" name="banner_link" id="inputBannerLink" dir="ltr" class="hq-input-styled" value="<?= event_h($activeEvent['banner_link'] ?? '') ?>" placeholder="https://macsa.ir/events/...">
            </div>

            <div class="hq-field-group">
              <label class="hq-field-label" for="inputBannerCta">متن دکمه (CTA)</label>
              <input type="text" name="banner_cta" id="inputBannerCta" class="hq-input-styled" value="<?= event_h($activeEvent['banner_cta'] ?: 'ثبت‌نام مستقیم') ?>">
            </div>
          </div>

          <!-- Row 4: Theme Palette & Dismissible (#8:720) -->
          <div class="hq-form-row">
            <div class="hq-field-group">
              <label class="hq-field-label">تم رنگی بنر سراسری</label>
              <div class="hq-color-selector">
                <div class="color-dot-group">
                  <button type="button" class="color-dot-btn <?= ($activeEvent['banner_background'] ?? '') === '#1E293B' ? 'is-active' : '' ?>" data-color="#1E293B" data-name="دودی گرافیت" style="background:#1E293B;" title="دودی گرافیت"></button>
                  <button type="button" class="color-dot-btn <?= ($activeEvent['banner_background'] ?? '') === '#701A75' ? 'is-active' : '' ?>" data-color="#701A75" data-name="زرشکی ارغوانی" style="background:#701A75;" title="زرشکی ارغوانی"></button>
                  <button type="button" class="color-dot-btn <?= ($activeEvent['banner_background'] ?? '') === '#1E3A8A' ? 'is-active' : '' ?>" data-color="#1E3A8A" data-name="سرمه‌ای تیره" style="background:#1E3A8A;" title="سرمه‌ای تیره"></button>
                  <button type="button" class="color-dot-btn <?= empty($activeEvent['banner_background']) || $activeEvent['banner_background'] === '#0A5C66' ? 'is-active' : '' ?>" data-color="#0A5C66" data-name="سبز کله‌غازی مکسا (پیش‌فرض)" style="background:#0A5C66;" title="سبز کله‌غازی مکسا (پیش‌فرض)"></button>
                </div>
                <span class="color-palette-label" id="colorNameLabel">سبز کله‌غازی مکسا (پیش‌فرض)</span>
              </div>
            </div>

            <div class="hq-field-group">
              <label class="hq-field-label">قابلیت بستن موقت توسط کاربر</label>
              <label class="hq-checkbox-control">
                <input type="checkbox" name="banner_dismissible" id="chkDismissible" value="1" <?= !empty($activeEvent['banner_dismissible']) ? 'checked' : '' ?>>
                <span>بله، دکمه ضربدر فعال باشد</span>
              </label>
            </div>
          </div>

        </div>

        <!-- Form Actions (#8:735) -->
        <div class="hq-actions-row">
          <a href="event-list.php" class="hq-btn-cancel">انصراف</a>

          <div class="hq-actions-left">
            <a href="event-list.php" class="hq-btn-archive">آرشیو بنرهای قبلی</a>
            <button type="submit" class="hq-btn-save">ذخیره و انتشار سراسری بنر</button>
          </div>
        </div>

      </div>

    </div>
  </form>

</div>

<script>
(function() {
  const eventSelect = document.getElementById('eventSelector');
  const toggleBtn = document.getElementById('bannerActiveToggle');
  const toggleText = document.getElementById('toggleStatusText');
  const hiddenActive = document.getElementById('inputBannerActive');
  
  const titleInput = document.getElementById('inputBannerTitle');
  const labelInput = document.getElementById('inputBannerLabel');
  const ctaInput = document.getElementById('inputBannerCta');
  const linkInput = document.getElementById('inputBannerLink');
  const hiddenBg = document.getElementById('inputBannerBg');
  const chkDismiss = document.getElementById('chkDismissible');

  // Specimen Elements
  const specimenBox = document.getElementById('specimenBannerBox');
  const specimenTitle = document.getElementById('specimenTitleText');
  const specimenBadge = document.getElementById('specimenBadgeText');
  const specimenCta = document.getElementById('specimenCtaText');
  const specimenDismiss = document.getElementById('specimenDismissBtn');
  const colorNameLabel = document.getElementById('colorNameLabel');
  const colorBtns = document.querySelectorAll('.color-dot-btn');

  // 1. Live Input Sync
  titleInput.addEventListener('input', () => {
    specimenTitle.textContent = titleInput.value || 'عنوان رویداد انتخاب‌شده';
  });
  labelInput.addEventListener('input', () => {
    specimenBadge.textContent = labelInput.value || 'رویداد ویژه';
  });
  ctaInput.addEventListener('input', () => {
    specimenCta.textContent = ctaInput.value || 'ثبت‌نام مستقیم';
  });
  chkDismiss.addEventListener('change', () => {
    specimenDismiss.style.display = chkDismiss.checked ? 'inline' : 'none';
  });

  // 2. Toggle Switch Handler
  toggleBtn.addEventListener('click', () => {
    const isNowActive = hiddenActive.value === '0';
    hiddenActive.value = isNowActive ? '1' : '0';
    toggleBtn.classList.toggle('is-off', !isNowActive);
    toggleText.textContent = isNowActive ? 'فعال و روشن' : 'خاموش و غیرفعال';
  });

  // 3. Color Dot Selector
  colorBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      colorBtns.forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');
      const color = btn.dataset.color;
      const name = btn.dataset.name;
      hiddenBg.value = color;
      specimenBox.style.background = color;
      colorNameLabel.textContent = name;
    });
  });

  // 4. On Event Change in Dropdown -> Populate Form & Specimen
  eventSelect.addEventListener('change', () => {
    const opt = eventSelect.options[eventSelect.selectedIndex];
    if (!opt) return;

    titleInput.value = opt.dataset.title || '';
    labelInput.value = opt.dataset.label || 'رویداد ویژه جاری';
    ctaInput.value = opt.dataset.cta || 'ثبت‌نام مستقیم';
    linkInput.value = opt.dataset.link || '';

    const bg = opt.dataset.bg || '#0A5C66';
    hiddenBg.value = bg;
    specimenBox.style.background = bg;

    // Select matching color dot
    colorBtns.forEach(btn => {
      const match = btn.dataset.color.toLowerCase() === bg.toLowerCase();
      btn.classList.toggle('is-active', match);
      if (match) colorNameLabel.textContent = btn.dataset.name;
    });

    const isActive = opt.dataset.active === '1';
    hiddenActive.value = isActive ? '1' : '0';
    toggleBtn.classList.toggle('is-off', !isActive);
    toggleText.textContent = isActive ? 'فعال و روشن' : 'خاموش و غیرفعال';

    const isDismiss = opt.dataset.dismiss !== '0';
    chkDismiss.checked = isDismiss;
    specimenDismiss.style.display = isDismiss ? 'inline' : 'none';

    // Trigger visual sync
    specimenTitle.textContent = titleInput.value || 'عنوان رویداد انتخاب‌شده';
    specimenBadge.textContent = labelInput.value || 'رویداد ویژه';
    specimenCta.textContent = ctaInput.value || 'ثبت‌نام مستقیم';
  });
})();
</script>

</body>
</html>

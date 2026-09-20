<?php
/* ============================================================================
 *  کامپوننت انتخابگر موقعیت شعبه روی نقشه ایران (Draggable Branch Map Picker)
 * ----------------------------------------------------------------------------
 *  استفاده در: branch-create.php و branch-edit.php
 *  ورودی‌ها:
 *    - $old['map_x'] و $old['map_y'] (مختصات فعلی یا خالی)
 *    - $old['city'] یا $old['province'] (اختیاری جهت راهنمایی)
 * ========================================================================== */

$curX = isset($old['map_x']) && $old['map_x'] !== '' ? (float)$old['map_x'] : null;
$curY = isset($old['map_y']) && $old['map_y'] !== '' ? (float)$old['map_y'] : null;
$hasPin = ($curX !== null && $curY !== null);

// استخراج لایه استان‌ها و برچسب‌های استانی از map-svg.php جهت عدم تکرار کد وکتور
static $cachedCountryLayer = null;
static $cachedInactiveLabels = null;

if ($cachedCountryLayer === null) {
    $svgFile = __DIR__ . '/map-svg.php';
    if (file_exists($svgFile)) {
        $rawSvg = file_get_contents($svgFile);
        // استخراج لایه استان‌ها
        if (preg_match('/(<g id="layercountry"[^>]*>.*?<\/g>)\s*<g id="layerlabels"/s', $rawSvg, $mCountry)) {
            $cleaned = $mCountry[1];
            // حذف تمام تگ‌های <a> و </a> و هرگونه ویژگی href تا هیچ کلیکی باعث انتقال به صفحات شعب نشود
            $cleaned = preg_replace('/<a\b[^>]*>/i', '<g class="province-wrapper">', $cleaned);
            $cleaned = str_ireplace('</a>', '</g>', $cleaned);
            $cleaned = preg_replace('/\s*(?:xlink:href|href)="[^"]*"/i', '', $cleaned);
            $cachedCountryLayer = $cleaned;
        }
        // استخراج برچسب‌های متنی استان‌ها
        if (preg_match_all('/<text[^>]*class="lbl-inactive"[^>]*>.*?<\/text>/s', $rawSvg, $mLabels)) {
            $cachedInactiveLabels = implode("\n", $mLabels[0]);
        }
    }
}

// شعب موجود به عنوان نقاط مرجع جهت جهت‌یابی راحت کاربر (فقط نقاط راهنما، بدون هیچ‌گونه لینک)
$referenceBranches = [
    ['name' => 'تهران', 'x' => 475.0, 'y' => 283.0],
    ['name' => 'اصفهان', 'x' => 510.0, 'y' => 468.0],
    ['name' => 'کاشان', 'x' => 472.0, 'y' => 398.0],
    ['name' => 'مشهد', 'x' => 905.0, 'y' => 263.0],
    ['name' => 'تبریز', 'x' => 185.0, 'y' => 100.0],
    ['name' => 'اهواز', 'x' => 315.0, 'y' => 573.0],
    ['name' => 'قم', 'x' => 430.0, 'y' => 343.0],
];

if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $stRef = $pdo->query("SELECT name, city, map_x, map_y FROM branches WHERE status = 'active' AND map_x IS NOT NULL AND map_y IS NOT NULL");
        $dbRefs = $stRef->fetchAll();
        foreach ($dbRefs as $dbr) {
            $lbl = trim($dbr['city'] ?? '');
            if ($lbl === '') {
                $lbl = preg_replace('/^شعبه\s+/u', '', trim($dbr['name']));
            }
            $bx = (float)$dbr['map_x'];
            $by = (float)$dbr['map_y'];
            $exists = false;
            foreach ($referenceBranches as &$rf) {
                if ($rf['name'] === $lbl || (abs($rf['x'] - $bx) < 6 && abs($rf['y'] - $by) < 6)) {
                    $rf['x'] = $bx;
                    $rf['y'] = $by;
                    $exists = true;
                    break;
                }
            }
            unset($rf);
            if (!$exists && $lbl !== '') {
                $referenceBranches[] = ['name' => $lbl, 'x' => $bx, 'y' => $by];
            }
        }
    } catch (Throwable $e) {}
}
?>

<div class="card map-picker-card">
  <div class="map-picker-head">
    <div class="map-picker-titles">
      <h2>موقعیت شعبه روی نقشه ایران</h2>
      <p class="hint">نشانگر را با درگ کردن (کشیدن و رها کردن) روی نقشه جابجا کنید، یا مستقیماً روی نقطه مورد نظر کلیک/تپ نمایید.</p>
    </div>
    <div class="map-picker-status">
      <div class="mp-badge <?= $hasPin ? 'is-set' : '' ?>" id="mpBadge">
        <span class="mp-badge-dot"></span>
        <span id="mpBadgeText"><?= $hasPin ? 'موقعیت: X=' . round($curX, 1) . ' , Y=' . round($curY, 1) : 'موقعیتی مشخص نشده (روی نقشه کلیک یا درگ کنید)' ?></span>
      </div>
      <button type="button" class="btn-mp-clear" id="mpClearBtn" style="<?= $hasPin ? '' : 'display:none;' ?>" title="حذف موقعیت از روی نقشه">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="12"/></svg>
        پاک کردن موقعیت
      </button>
    </div>
  </div>

  <div class="map-picker-viewport" id="mpViewport" title="برای تنظیم نشانگر، روی نقشه کلیک کنید یا نشانگر را بکشید">
    <svg id="IranPickerSvg"
         viewBox="0 0 1155.9022 1015.9907"
         preserveAspectRatio="xMidYMid meet"
         xmlns="http://www.w3.org/2000/svg"
         xmlns:xlink="http://www.w3.org/1999/xlink">
      
      <defs>
        <!-- فیلتر سایه عمیق برای نشانگر درگ‌بل -->
        <filter id="mpPinShadow" x="-50%" y="-30%" width="200%" height="200%">
          <feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#000000" flood-opacity="0.45" />
          <feDropShadow dx="0" dy="1" stdDeviation="2" flood-color="#004d4c" flood-opacity="0.3" />
        </filter>
        <filter id="mpPinGlow" x="-50%" y="-50%" width="200%" height="200%">
          <feGaussianBlur stdDeviation="3.5" result="glow" />
          <feMerge>
            <feMergeNode in="glow" />
            <feMergeNode in="SourceGraphic" />
          </feMerge>
        </filter>
      </defs>

      <!-- ۱. لایه شکل استان‌های کشور (تمامی لینک‌ها حذف شده‌اند) -->
      <g class="mp-country-layer">
        <?= $cachedCountryLayer ?? '' ?>
      </g>

      <!-- ۲. برچسب‌های متنی نام استان‌ها جهت راهنمایی دقیق جغرافیایی -->
      <g class="mp-province-labels" pointer-events="none">
        <?= $cachedInactiveLabels ?? '' ?>
      </g>

      <!-- ۳. پین‌های راهنما برای شعب موجود جهت جهت‌یابی سریع مدیر (کاملاً استاتیک و بدون لینک) -->
      <g class="mp-reference-branches" pointer-events="none">
        <?php foreach ($referenceBranches as $rb): ?>
          <circle cx="<?= $rb['x'] ?>" cy="<?= $rb['y'] ?>" r="3.5" class="mp-ref-dot" />
          <text x="<?= $rb['x'] ?>" y="<?= $rb['y'] - 8 ?>" class="mp-ref-text"><?= htmlspecialchars($rb['name'], ENT_QUOTES, 'UTF-8') ?></text>
        <?php endforeach; ?>
      </g>

      <!-- ۴. نشانگر متحرک و درگ‌بل شعبه انتخابی (Active Draggable Pin) -->
      <g id="mpActivePin" class="mp-active-pin <?= $hasPin ? 'is-visible' : '' ?>" transform="translate(<?= $hasPin ? $curX : 475.0 ?>, <?= $hasPin ? $curY : 350.0 ?>)">
        <!-- رادارهای پالس متحرک -->
        <circle cx="0" cy="0" r="8" class="mp-pin-radar mp-pin-radar--1" />
        <circle cx="0" cy="0" r="8" class="mp-pin-radar mp-pin-radar--2" />

        <!-- سایه روی زمین -->
        <ellipse cx="0" cy="2" rx="7" ry="3" class="mp-ground-shadow" />

        <!-- آیکون پین اختصاصی مکسا با نوک دقیق در مرکز (0, 0) -->
        <g class="mp-pin-body" filter="url(#mpPinShadow)">
          <!-- شکل قطره‌ای پین مکان‌یاب (نوک آن دقیقا در 0, 0 قرار دارد) -->
          <path d="M 0,0 C -2,-4 -14,-17 -14,-28 C -14,-37 -7,-44 0,-44 C 7,-44 14,-37 14,-28 C 14,-17 2,-4 0,0 Z" class="mp-pin-shape" />
          <!-- حلقه بیرونی سر پین -->
          <circle cx="0" cy="-28" r="7.5" class="mp-pin-head-outer" />
          <!-- مرکز پین طلایی مکسا -->
          <circle cx="0" cy="-28" r="4.2" class="mp-pin-head-inner" />
        </g>

        <!-- برچسب راهنمای شناور روی نشانگر -->
        <g class="mp-pin-tooltip" transform="translate(0, -52)">
          <rect x="-65" y="-12" width="130" height="24" rx="12" class="mp-tt-bg" />
          <text x="0" y="4" class="mp-tt-text">محل شعبه (بکشید)</text>
        </g>

        <!-- هیت‌باکس بزرگ نامرئی جهت گرفتن و کشیدن آسان با موس و لمس انگشت -->
        <circle cx="0" cy="-22" r="32" class="mp-pin-drag-hitbox" />
      </g>
    </svg>
  </div>

  <div class="map-picker-foot">
    <div class="mp-guide-tips">
      <span class="mp-tip-icon">💡</span>
      <span>روی نقشه کلیک کنید یا نشانگر قرمز را با ماوس/لمس گرفته و به شهر یا استان مورد نظر بکشید. مختصات به صورت خودکار محاسبه و ذخیره می‌شود.</span>
    </div>
  </div>

  <!-- فیلدهای مخفی ارسال مختصات به کنترلر POST -->
  <input type="hidden" name="map_x" id="mapXInput" value="<?= e($old['map_x'] ?? '') ?>">
  <input type="hidden" name="map_y" id="mapYInput" value="<?= e($old['map_y'] ?? '') ?>">
</div>

<style>
/* === استایل اختصاصی نقشه تعاملی با نشانگر درگ‌بل در پنل مدیریت === */
.map-picker-card {
  position: relative;
  background: var(--color-surface, #ffffff);
  border: 1px solid var(--color-border, #e6e8ea);
  border-radius: var(--radius, 18px);
  padding: 22px 24px;
  margin-bottom: 22px;
  box-shadow: var(--shadow-sm, 0 2px 6px rgba(16,40,40,.04));
}

.map-picker-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.map-picker-titles h2 {
  font-size: 16px;
  font-weight: 800;
  color: var(--color-text, #1e293b);
  margin-bottom: 4px;
}

.map-picker-titles .hint {
  font-size: 13px;
  color: var(--color-muted, #64748b);
  line-height: 1.6;
}

.map-picker-status {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.mp-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  background: rgba(100, 116, 139, 0.08);
  color: var(--color-muted, #64748b);
  border: 1px solid rgba(100, 116, 139, 0.15);
  transition: all 0.2s ease;
}

.mp-badge.is-set {
  background: rgba(0, 123, 122, 0.09);
  color: #007b7a;
  border-color: rgba(0, 123, 122, 0.3);
}

.mp-badge-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #94a3b8;
}

.mp-badge.is-set .mp-badge-dot {
  background: #10b981;
  box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);
}

.btn-mp-clear {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  font-family: inherit;
  background: #fff;
  border: 1px solid #fca5a5;
  color: #ef4444;
  cursor: pointer;
  transition: all 0.18s ease;
}

.btn-mp-clear:hover {
  background: #fef2f2;
  border-color: #ef4444;
  color: #dc2626;
}

/* ویوپورت نقشه */
.map-picker-viewport {
  position: relative;
  width: 100%;
  max-width: 860px;
  margin: 0 auto;
  background: #f8fafc;
  border: 1.5px solid rgba(0, 123, 122, 0.16);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.03);
  cursor: crosshair;
  touch-action: none;
  user-select: none;
}

:root[data-theme="dark"] .map-picker-viewport {
  background: #111a1f;
  border-color: rgba(79, 178, 176, 0.22);
}

#IranPickerSvg {
  display: block;
  width: 100%;
  height: auto;
  max-height: 480px;
  margin: 0 auto;
  filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.05));
}

/* ظاهر استان‌ها در انتخابگر */
.mp-country-layer .province-shape {
  fill: #fdfefe !important;
  stroke: #cbd5e1 !important;
  stroke-width: 1.1 !important;
  transition: fill 0.15s ease, stroke 0.15s ease;
  cursor: crosshair !important;
  pointer-events: all !important;
}

:root[data-theme="dark"] .mp-country-layer .province-shape {
  fill: #1e293b !important;
  stroke: #334155 !important;
}

.mp-country-layer .province-shape:hover {
  fill: #e6f6f6 !important;
  stroke: #007b7a !important;
  stroke-width: 1.4 !important;
}

:root[data-theme="dark"] .mp-country-layer .province-shape:hover {
  fill: #134e4a !important;
  stroke: #2dd4bf !important;
}

/* ممانعت قطعی از کلیک‌پذیری هرگونه لینک یا جابجایی صفحه در انتخابگر نقشه */
.map-picker-viewport a,
.mp-country-layer a,
.mp-country-layer .province-wrapper,
.mp-reference-branches,
.mp-province-labels {
  pointer-events: none !important;
  cursor: crosshair !important;
  text-decoration: none !important;
}

/* برچسب‌های کم‌رنگ استانی برای جهت‌یابی */
.mp-province-labels text {
  font-family: 'Vazirmatn', sans-serif;
  fill: #94a3b8;
  font-weight: 500;
  text-anchor: middle;
  pointer-events: none !important;
  user-select: none !important;
}

:root[data-theme="dark"] .mp-province-labels text {
  fill: #64748b;
}

/* پین‌های مرجع (Reference Branches) */
.mp-ref-dot {
  fill: #007b7a;
  stroke: #ffffff;
  stroke-width: 1.2;
}

.mp-ref-text {
  font-family: 'Vazirmatn', sans-serif;
  font-size: 11px;
  font-weight: 700;
  fill: #0f766e;
  text-anchor: middle;
}

:root[data-theme="dark"] .mp-ref-dot {
  fill: #2dd4bf;
  stroke: #0f172a;
}
:root[data-theme="dark"] .mp-ref-text {
  fill: #5eead4;
}

/* === نشانگر فعال و درگ‌بل === */
.mp-active-pin {
  cursor: grab;
  touch-action: none;
  transition: transform 0.08s cubic-bezier(0.2, 0, 0, 1);
  display: none;
}

.mp-active-pin.is-visible {
  display: block;
}

.mp-active-pin.is-dragging {
  cursor: grabbing !important;
  transition: none;
}

.mp-pin-drag-hitbox {
  fill: #000000;
  fill-opacity: 0.001;
  opacity: 0.001;
  cursor: grab;
  pointer-events: all !important;
}

.mp-active-pin.is-dragging .mp-pin-drag-hitbox {
  cursor: grabbing !important;
}

/* رادارهای پالس */
@keyframes mpPulse {
  0% { r: 6px; opacity: 0.9; stroke-width: 2px; }
  65% { r: 24px; opacity: 0; stroke-width: 2.5px; }
  100% { r: 24px; opacity: 0; }
}

.mp-pin-radar {
  fill: rgba(244, 166, 30, 0.2);
  stroke: #f4a61e;
  pointer-events: none;
  animation: mpPulse 2.2s cubic-bezier(0.1, 0.7, 0.3, 1) infinite;
}

.mp-pin-radar--2 {
  animation-delay: 1.1s;
}

.mp-ground-shadow {
  fill: rgba(0, 0, 0, 0.28);
  filter: blur(1.5px);
  pointer-events: none;
}

/* شکل بدنه پین مکان‌یاب */
.mp-pin-shape {
  fill: #dc2626;
  stroke: #ffffff;
  stroke-width: 2.2;
  transition: fill 0.2s ease, transform 0.2s ease;
  pointer-events: none;
}

.mp-active-pin:hover .mp-pin-shape,
.mp-active-pin.is-dragging .mp-pin-shape {
  fill: #b91c1c;
}

.mp-pin-head-outer {
  fill: #ffffff;
  pointer-events: none;
}

.mp-pin-head-inner {
  fill: #f4a61e;
  pointer-events: none;
}

/* تولتیپ برچسب بالای پین */
.mp-tt-bg {
  fill: #0f172a;
  stroke: #f4a61e;
  stroke-width: 1.2;
  pointer-events: none;
}

.mp-tt-text {
  font-family: 'Vazirmatn', sans-serif;
  font-size: 11px;
  font-weight: 700;
  fill: #ffffff;
  text-anchor: middle;
  pointer-events: none;
}

.map-picker-foot {
  margin-top: 14px;
}

.mp-guide-tips {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  color: var(--color-muted, #64748b);
  background: rgba(0, 123, 122, 0.05);
  border: 1px dashed rgba(0, 123, 122, 0.2);
  border-radius: 10px;
  padding: 10px 14px;
}

.mp-tip-icon {
  font-size: 16px;
  flex-shrink: 0;
}
</style>

<script>
(function() {
  const svg = document.getElementById('IranPickerSvg');
  const viewport = document.getElementById('mpViewport');
  const pin = document.getElementById('mpActivePin');
  const xInput = document.getElementById('mapXInput');
  const yInput = document.getElementById('mapYInput');
  const badge = document.getElementById('mpBadge');
  const badgeText = document.getElementById('mpBadgeText');
  const clearBtn = document.getElementById('mpClearBtn');

  if (!svg || !viewport || !pin || !xInput || !yInput) return;

  // مختصات در سیستم مختصات SVG: [0..1155.9, 0..1016.0]
  const SVG_WIDTH = 1155.9022;
  const SVG_HEIGHT = 1015.9907;

  let currentX = parseFloat(xInput.value);
  let currentY = parseFloat(yInput.value);
  let hasLocation = !isNaN(currentX) && !isNaN(currentY);

  let isDragging = false;
  let dragPointerId = null;

  // تابع تبدیل مختصات رویداد صفحه به مختصات دقیق داخل SVG
  function screenToSvg(e) {
    if (svg.createSVGPoint && svg.getScreenCTM) {
      try {
        const pt = svg.createSVGPoint();
        pt.x = e.clientX;
        pt.y = e.clientY;
        const ctm = svg.getScreenCTM();
        if (ctm) {
          const inv = ctm.inverse();
          const p = pt.matrixTransform(inv);
          return {
            x: Math.max(15, Math.min(SVG_WIDTH - 15, p.x)),
            y: Math.max(15, Math.min(SVG_HEIGHT - 15, p.y))
          };
        }
      } catch (err) {}
    }
    // Fallback با BoundingClientRect
    const rect = svg.getBoundingClientRect();
    const rx = (e.clientX - rect.left) / rect.width;
    const ry = (e.clientY - rect.top) / rect.height;
    return {
      x: Math.max(15, Math.min(SVG_WIDTH - 15, rx * SVG_WIDTH)),
      y: Math.max(15, Math.min(SVG_HEIGHT - 15, ry * SVG_HEIGHT))
    };
  }

  // به‌روزرسانی موقعیت پین روی نقشه و ورودی‌های فرم
  function setPinPosition(x, y, updateInputs = true) {
    x = Math.round(x * 10) / 10;
    y = Math.round(y * 10) / 10;

    currentX = x;
    currentY = y;
    hasLocation = true;

    pin.setAttribute('transform', 'translate(' + x + ', ' + y + ')');
    pin.classList.add('is-visible');

    if (updateInputs) {
      xInput.value = x.toFixed(1);
      yInput.value = y.toFixed(1);
    }

    if (badge) badge.classList.add('is-set');
    if (badgeText) badgeText.textContent = 'موقعیت تنظیم شده: X=' + x.toFixed(1) + ' , Y=' + y.toFixed(1);
    if (clearBtn) clearBtn.style.display = 'inline-flex';
  }

  // حذف پین
  function clearPinPosition() {
    hasLocation = false;
    currentX = NaN;
    currentY = NaN;
    xInput.value = '';
    yInput.value = '';
    pin.classList.remove('is-visible');

    if (badge) badge.classList.remove('is-set');
    if (badgeText) badgeText.textContent = 'موقعیتی مشخص نشده (روی نقشه کلیک یا درگ کنید)';
    if (clearBtn) clearBtn.style.display = 'none';
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      clearPinPosition();
    });
  }

  // ۱. شروع درگ کردن روی پین (Pointer Events برای ماوس، قلم و تاچ)
  const dragHitbox = pin.querySelector('.mp-pin-drag-hitbox') || pin;
  dragHitbox.addEventListener('pointerdown', function(e) {
    e.preventDefault();
    e.stopPropagation();

    isDragging = true;
    dragPointerId = e.pointerId;
    pin.classList.add('is-dragging');

    try {
      dragHitbox.setPointerCapture(e.pointerId);
    } catch (err) {}
  });

  // ۲. جابجایی پین حین درگ
  dragHitbox.addEventListener('pointermove', function(e) {
    if (!isDragging) return;
    e.preventDefault();
    e.stopPropagation();

    const pt = screenToSvg(e);
    setPinPosition(pt.x, pt.y);
  });

  // ۳. پایان درگ
  function onDragEnd(e) {
    if (!isDragging) return;
    isDragging = false;
    pin.classList.remove('is-dragging');

    if (dragPointerId !== null) {
      try {
        dragHitbox.releasePointerCapture(dragPointerId);
      } catch (err) {}
      dragPointerId = null;
    }
  }

  dragHitbox.addEventListener('pointerup', onDragEnd);
  dragHitbox.addEventListener('pointercancel', onDragEnd);

  // پیشگیری قطعی از هرگونه باز شدن لینک در ویوپورت انتخابگر
  viewport.addEventListener('click', function(e) {
    if (e.target && e.target.closest('a')) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);

  // ۴. کلیک مستقیم روی نقشه برای قراردادن یا جابجا کردن پین در آن نقطه
  svg.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    // اگر کاربر در حال کشیدن پین بود یا روی خود پین کلیک کرد، کاری نکن
    if (isDragging) return;
    if (e.target && (e.target.closest('#mpActivePin') || e.target.classList.contains('mp-pin-drag-hitbox'))) {
      return;
    }

    const pt = screenToSvg(e);
    setPinPosition(pt.x, pt.y);

    // اگر روی استانی کلیک شد و فیلد استان خالی بود، به‌صورت هوشمند نام استان را درج کن
    const clickedProvEl = e.target.closest ? e.target.closest('[data-province]') : null;
    if (clickedProvEl) {
      const provName = clickedProvEl.getAttribute('data-province');
      const provInput = document.getElementById('branchProvinceInput') || document.querySelector('input[name="province"]');
      if (provName && provInput && (!provInput.value || provInput.value.trim() === '')) {
        provInput.value = provName;
      }
    }
  });

  // مقداردهی اولیه اگر از قبل موقعیتی ثبت شده بود
  if (hasLocation) {
    setPinPosition(currentX, currentY, false);
  }
})();
</script>

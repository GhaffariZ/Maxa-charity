<?php require_once __DIR__ . '/../../../core/component-lang.php'; ?>
<section class="branches" dir="rtl">
  <div class="branches__card">
    <header class="branches__head">
      <span class="branches__eyebrow">گستره خدمت‌رسانی مکسا در سراسر کشور</span>
      <h2 class="branches__title">شبکه شعب و مراکز تخصصی مکسا</h2>
      <p class="branches__subtitle">
        برای مشاهده اطلاعات هر شعبه، روی استان‌های فعال (آبی‌رنگ) کلیک کنید.
      </p>
    </header>

    <div class="branches__mapbox">
      <?php require __DIR__ . '/map-svg.php'; ?>
    </div>

    <!-- پنجره پاپ‌اور انتخاب شعبه برای استان اصفهان (شامل شعب اصفهان و کاشان) -->
    <div id="esfahanBranchModal" class="branch-popover" aria-hidden="true" role="dialog" aria-labelledby="ebm-title">
      <div class="branch-popover__backdrop" data-close-popover></div>
      <div class="branch-popover__box">
        <button type="button" class="branch-popover__close" data-close-popover aria-label="بستن پنجره">&times;</button>
        <div class="branch-popover__head">
          <span class="branch-popover__eyebrow">استان اصفهان</span>
          <h3 id="ebm-title" class="branch-popover__title">شعب فعال مکسا در استان اصفهان</h3>
          <p class="branch-popover__desc">در گستره استان اصفهان، ۲ شعبه فعال مکسا آماده خدمت‌رسانی به بیماران و خانواده‌های آنان هستند. لطفاً شعبه مورد نظر را انتخاب فرمایید:</p>
        </div>
        <div class="branch-popover__cards">
          <a href="/esfahan-branch" class="branch-popover__card" title="ورود به صفحه اختصاصی شعبه اصفهان">
            <div class="branch-popover__icon">
              <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-4.418 0-8-3.582-8-8 0-4.418 8-11 8-11s8 6.582 8 11c0 4.418-3.582 8-8 8z"/><circle cx="12" cy="13" r="3"/></svg>
            </div>
            <div class="branch-popover__card-info">
              <h4>شعبه اصفهان</h4>
              <span>مرکز استان اصفهان · درمانگاه طب تسکینی و شبکه مراقبت در منزل</span>
            </div>
            <span class="branch-popover__arrow">&larr;</span>
          </a>

          <a href="/kashan-branch" class="branch-popover__card" title="ورود به صفحه اختصاصی شعبه کاشان">
            <div class="branch-popover__icon branch-popover__icon--kashan">
              <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-4.418 0-8-3.582-8-8 0-4.418 8-11 8-11s8 6.582 8 11c0 4.418-3.582 8-8 8z"/><circle cx="12" cy="13" r="3"/></svg>
            </div>
            <div class="branch-popover__card-info">
              <h4>شعبه کاشان</h4>
              <span>شمال استان اصفهان · خدمات تخصصی مراقبت تسکینی و مشاوره</span>
            </div>
            <span class="branch-popover__arrow">&larr;</span>
          </a>
        </div>
      </div>
    </div>

    <!-- راهنمای نقشه و ۳ مرکز ویژه زیر نقشه -->
    <div class="branches__bottom-bar">
      <!-- راهنمای رنگ‌ها -->
      <div class="branches__legend">
        <div class="branches__legend-item branches__legend-item--active">
          <span class="branches__swatch branches__swatch--active"></span>
          <span class="branches__legend-text">
            <strong>استان‌های فعال (دارای شعبه)</strong>
          </span>
        </div>
        <div class="branches__legend-item">
          <span class="branches__swatch branches__swatch--inactive"></span>
          <span class="branches__legend-text">سایر استان‌ها</span>
        </div>
      </div>

      <!-- ۳ آیکون-دکمه جمع‌وجور برای مراکز ویژه -->
      <div class="branches__quick-centers">
        <!-- ۱. دفتر ستاد مرکزی -->
        <a href="/home" class="bqc-pill bqc-pill--hq" title="دفتر ستاد مرکزی مکسا (تهران)">
          <span class="bqc-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 21h18"/>
              <path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/>
              <path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/>
              <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/>
            </svg>
          </span>
          <span class="bqc-label">دفتر ستاد مرکزی</span>
        </a>

        <!-- ۲. مرکز ارتباطات و دورپزشکی -->
        <a href="/contact-center" class="bqc-pill bqc-pill--telemed" title="مرکز ارتباطات و دورپزشکی (تله‌مدیسین ۲۴ ساعته)">
          <span class="bqc-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
          </span>
          <span class="bqc-label">مرکز ارتباطات و دورپزشکی</span>
        </a>

        <!-- ۳. مرکز رویش استعدادهای دانشجویی -->
        <a href="/cdst" class="bqc-pill bqc-pill--student" title="مرکز رویش استعدادهای دانشجویی مکسا (CDST)">
          <span class="bqc-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
              <path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
          </span>
          <span class="bqc-label">مرکز رویش استعدادهای دانشجویی</span>
        </a>
      </div>
    </div>

  </div>
</section>

<style>
/* === Branches Map & Centers — Modern Maxa Design System ==================== */
.branches {
  --color-primary: #007b7a;
  --color-primary-dark: #004d4c;
  --color-primary-light: #0ea5e9;
  --color-primary-soft: rgba(0, 123, 122, 0.08);
  --color-secondary: #f4a61e;
  --color-secondary-dark: #d97706;
  --color-text: #1e293b;
  --color-muted: #64748b;
  --color-border: #e2e8f0;

  box-sizing: border-box;
  width: 100%;
  padding: 24px 12px 36px;
  background: transparent;
  font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: var(--color-text);
  direction: rtl;
}

.branches *,
.branches *::before,
.branches *::after {
  box-sizing: border-box;
}

/* کارت اصلی */
.branches__card {
  max-width: 1050px;
  margin: 0 auto;
  background: #ffffff;
  border: 1px solid rgba(0, 123, 122, 0.12);
  border-radius: 24px;
  padding: clamp(20px, 3.5vw, 36px);
  box-shadow: 0 10px 30px rgba(0, 77, 76, 0.04), 0 2px 6px rgba(0, 0, 0, 0.02);
}

/* سربرگ */
.branches__head {
  text-align: center;
  margin-bottom: 18px;
}

.branches__eyebrow {
  display: inline-block;
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--color-primary);
  background: var(--color-primary-soft);
  border: 1px solid rgba(0, 123, 122, 0.15);
  padding: 5px 16px;
  border-radius: 999px;
  margin-bottom: 10px;
}

.branches__title {
  margin: 0 0 10px;
  font-size: clamp(1.5rem, 3vw, 2.1rem);
  font-weight: 900;
  line-height: 1.35;
  color: #0f172a;
}

.branches__title::after {
  content: "";
  display: block;
  width: 50px;
  height: 4px;
  margin: 10px auto 0;
  border-radius: 999px;
  background: linear-gradient(90deg, #007b7a, #f4a61e);
}

.branches__subtitle {
  max-width: 580px;
  margin: 0 auto;
  font-size: 0.94rem;
  line-height: 1.85;
  color: var(--color-muted);
}

/* نقشه */
.branches__mapbox {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 16px auto 10px;
  position: relative;
}

#Iran {
  display: block;
  width: 100%;
  max-width: 780px;
  height: auto;
  margin: 0 auto;
  transform: none;
  filter: drop-shadow(0 8px 20px rgba(0, 0, 0, 0.06));
}

/* مرزها و شکل استان‌ها — تفکیک واضح و چشم‌نواز */
#Iran .province-shape {
  stroke-linejoin: round !important;
  stroke-linecap: round !important;
  transition: fill 0.2s ease, filter 0.2s ease, stroke 0.2s ease, stroke-width 0.2s ease;
}

/* استان‌های غیرفعال (زرد اصیل مکسا) — بدون هاور و بدون کلیک با خط مرزی واضح */
#Iran .province-shape.is-inactive,
#Iran g.is-inactive path,
#Iran g.is-inactive polygon {
  fill: #f4a61e !important;
  stroke: #b86a00 !important;
  stroke-width: 1.05 !important;
  cursor: default !important;
  pointer-events: none !important;
  filter: none !important;
}

/* استان‌های فعال (فیروزه‌ای شاخص مکسا) — دارای هاور و کلیک با خط مرزی مشخص */
#Iran a.province-link {
  cursor: pointer;
  outline: none;
}

#Iran .province-shape.is-active,
#Iran path.province-shape.is-active,
#Iran polygon.province-shape.is-active,
#Iran .province-item.is-active .province-shape,
#Iran .province-item.is-active path,
#Iran .province-item.is-active polygon,
#Iran g.is-active .province-shape,
#Iran g.is-active path,
#Iran g.is-active polygon,
#Iran a.province-link .province-shape,
#Iran a.province-link .province-shape.is-active,
#Iran [data-province].is-active path,
#Iran [data-province].is-active polygon {
  fill: #007b7a !important;
  stroke: #004544 !important;
  stroke-width: 1.25 !important;
  cursor: pointer !important;
  pointer-events: auto !important;
  filter: drop-shadow(0 2px 6px rgba(0, 123, 122, 0.3)) !important;
}

#Iran .province-shape.is-active:hover,
#Iran path.province-shape.is-active:hover,
#Iran polygon.province-shape.is-active:hover,
#Iran .province-item.is-active:hover .province-shape,
#Iran g.is-active:hover path,
#Iran a.province-link:hover .province-shape,
#Iran a.province-link:hover .province-shape.is-active,
#Iran a.province-link:focus-visible .province-shape.is-active {
  fill: #10aeb8 !important;
  stroke: #002d2c !important;
  stroke-width: 1.6 !important;
  filter: drop-shadow(0 8px 18px rgba(16, 174, 184, 0.5)) !important;
}

#Iran .province-shape.is-active:active,
#Iran a.province-link:active .province-shape.is-active {
  fill: #005958 !important;
}

/* نوار پایین نقشه: لژاند و ۳ دکمه کوچک */
.branches__bottom-bar {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  margin-top: 14px;
  padding-top: 18px;
  border-top: 1px solid #edf2f7;
}

/* لژاند نقشه */
.branches__legend {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 24px;
  flex-wrap: wrap;
}

.branches__legend-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.88rem;
  color: #334155;
}

.branches__swatch {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border: 1.5px solid #ffffff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
  display: inline-block;
  flex-shrink: 0;
}

.branches__swatch--active {
  background: #007b7a;
}

.branches__swatch--inactive {
  background: #f4a61e;
}

.branches__legend-text strong {
  font-weight: 700;
}

/* ۳ آیکون-دکمه جمع‌وجور زیر نقشه */
.branches__quick-centers {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  flex-wrap: wrap;
  width: 100%;
}

.bqc-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 700;
  text-decoration: none;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #334155;
  transition: all 0.2s ease;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

.bqc-pill:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
}

.bqc-pill--hq {
  border-color: rgba(244, 166, 30, 0.3);
  background: #fffdfa;
}
.bqc-pill--hq:hover {
  background: #fff8eb;
  border-color: #f4a61e;
  color: #b45309;
}
.bqc-pill--hq .bqc-icon {
  color: #d97706;
}

.bqc-pill--telemed {
  border-color: rgba(0, 123, 122, 0.25);
  background: #f8fdfd;
}
.bqc-pill--telemed:hover {
  background: #edfafa;
  border-color: #007b7a;
  color: #007b7a;
}
.bqc-pill--telemed .bqc-icon {
  color: #007b7a;
}

.bqc-pill--student {
  border-color: rgba(37, 99, 235, 0.25);
  background: #f8faff;
}
.bqc-pill--student:hover {
  background: #eff6ff;
  border-color: #2563eb;
  color: #1d4ed8;
}
.bqc-pill--student .bqc-icon {
  color: #2563eb;
}

.bqc-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.bqc-label {
  white-space: nowrap;
}

/* واکنش‌گرایی */
@media (max-width: 768px) {
  .branches {
    padding: 16px 8px 30px;
  }
  .branches__card {
    padding: 18px 12px;
    border-radius: 18px;
  }
  .branches__quick-centers {
    flex-direction: column;
    align-items: stretch;
  }
  .bqc-pill {
    justify-content: center;
    padding: 10px 14px;
  }
}

@media (prefers-reduced-motion: reduce) {
  #Iran a path,
  .bqc-pill {
    transition: none !important;
  }
}

/* === پاپ‌اور انتخاب شعبه برای استان اصفهان (اصفهان و کاشان) === */
.branch-popover {
  position: fixed;
  inset: 0;
  z-index: 100000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.24s ease, visibility 0.24s;
  visibility: hidden;
  direction: rtl;
}
.branch-popover.is-open {
  opacity: 1;
  pointer-events: auto;
  visibility: visible;
}
.branch-popover__backdrop {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.branch-popover__box {
  position: relative;
  width: min(480px, 100%);
  background: #ffffff;
  border-radius: 22px;
  padding: 26px 24px;
  box-shadow: 0 24px 50px rgba(0, 77, 76, 0.24), 0 4px 12px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(0, 123, 122, 0.16);
  transform: translateY(16px) scale(0.96);
  transition: transform 0.26s cubic-bezier(0.16, 1, 0.3, 1);
  z-index: 2;
}
.branch-popover.is-open .branch-popover__box {
  transform: translateY(0) scale(1);
}
.branch-popover__close {
  position: absolute;
  top: 14px;
  left: 14px;
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  font-size: 20px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.18s ease;
}
.branch-popover__close:hover {
  background: #fee2e2;
  border-color: #fca5a5;
  color: #dc2626;
  transform: scale(1.05);
}
.branch-popover__head {
  text-align: right;
  margin-bottom: 20px;
  padding-left: 30px;
}
.branch-popover__eyebrow {
  display: inline-block;
  font-size: 0.78rem;
  font-weight: 800;
  color: #007b7a;
  background: rgba(0, 123, 122, 0.08);
  border: 1px solid rgba(0, 123, 122, 0.16);
  padding: 3px 12px;
  border-radius: 999px;
  margin-bottom: 8px;
}
.branch-popover__title {
  margin: 0 0 8px;
  font-size: 1.25rem;
  font-weight: 900;
  color: #0f172a;
  line-height: 1.35;
}
.branch-popover__desc {
  margin: 0;
  font-size: 0.88rem;
  line-height: 1.8;
  color: #64748b;
}
.branch-popover__cards {
  display: grid;
  grid-template-columns: 1fr;
  gap: 12px;
}
.branch-popover__card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 16px;
  text-decoration: none;
  color: #1e293b;
  transition: all 0.2s ease;
}
.branch-popover__card:hover {
  border-color: #007b7a;
  background: #f8fdfd;
  transform: translateX(-4px);
  box-shadow: 0 8px 20px rgba(0, 123, 122, 0.12);
}
.branch-popover__icon {
  flex: 0 0 42px;
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: rgba(0, 123, 122, 0.1);
  color: #007b7a;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease, background 0.2s ease;
}
.branch-popover__icon--kashan {
  background: rgba(244, 166, 30, 0.14);
  color: #b45309;
}
.branch-popover__card:hover .branch-popover__icon {
  transform: scale(1.08);
}
.branch-popover__card-info {
  flex: 1;
  min-width: 0;
}
.branch-popover__card-info h4 {
  margin: 0 0 3px;
  font-size: 1.02rem;
  font-weight: 800;
  color: #0f172a;
}
.branch-popover__card-info span {
  display: block;
  font-size: 0.78rem;
  color: #64748b;
  line-height: 1.6;
}
.branch-popover__arrow {
  color: #94a3b8;
  font-size: 1.2rem;
  transition: transform 0.2s ease, color 0.2s ease;
}
.branch-popover__card:hover .branch-popover__arrow {
  color: #007b7a;
  transform: translateX(-4px);
}
</style>

<script>
(function() {
  function navigateTo(url) {
    if (!url || url === '#' || url.startsWith('javascript:')) return;
    window.location.href = url;
  }

  // 0. Activate any provinces that have active branches registered
  if (window.__activeBranchProvinces && Array.isArray(window.__activeBranchProvinces)) {
    window.__activeBranchProvinces.forEach(function(rawProv) {
      if (!rawProv) return;
      var prov = String(rawProv).replace(/^استان\s+/u, '').trim();
      if (!prov) return;

      var provElements = document.querySelectorAll(
        '#Iran [data-province="' + prov + '"], ' +
        '#Iran [data-province="استان ' + prov + '"], ' +
        '#Iran [data-province="' + rawProv + '"]'
      );

      provElements.forEach(function(el) {
        el.classList.remove('is-inactive');
        el.classList.add('is-active');

        var shapes = el.querySelectorAll ? el.querySelectorAll('.province-shape, path, polygon') : [];
        shapes.forEach(function(shape) {
          shape.classList.remove('is-inactive');
          shape.classList.add('is-active');
          shape.style.setProperty('fill', '#007b7a', 'important');
          shape.style.setProperty('stroke', '#004544', 'important');
          shape.style.setProperty('stroke-width', '1.25', 'important');
          shape.style.setProperty('cursor', 'pointer', 'important');
        });

        if (el.classList.contains('province-shape') || el.tagName.toLowerCase() === 'path' || el.tagName.toLowerCase() === 'polygon') {
          el.style.setProperty('fill', '#007b7a', 'important');
          el.style.setProperty('stroke', '#004544', 'important');
          el.style.setProperty('stroke-width', '1.25', 'important');
          el.style.setProperty('cursor', 'pointer', 'important');
        }
      });

      // Hide inactive province label so it doesn't overlap the active pin
      document.querySelectorAll('#Iran text.lbl-inactive').forEach(function(txt) {
        var textContent = txt.textContent.trim().replace(/^استان\s+/u, '');
        var dataProv = (txt.getAttribute('data-province') || '').trim().replace(/^استان\s+/u, '');
        if (textContent === prov || dataProv === prov || textContent === rawProv) {
          txt.style.setProperty('display', 'none', 'important');
          txt.setAttribute('data-hidden', 'true');
        }
      });
    });
  }

  // 1. Direct Click & Touch handler for all map branch pins
  const pinLinks = document.querySelectorAll('#Iran a.branch-pin-link');
  pinLinks.forEach(function(pin) {
    const href = pin.getAttribute('href') || pin.getAttribute('xlink:href');
    if (!href) return;

    const onPinActivate = function(e) {
      e.preventDefault();
      e.stopPropagation();
      navigateTo(href);
    };

    pin.addEventListener('click', onPinActivate);
  });

  // 2. Specific foolproof direct handler for Kashan pin (touch + click)
  const kashanPins = document.querySelectorAll('#Iran [data-branch="kashan"], #Iran .pin-kashan');
  kashanPins.forEach(function(el) {
    const onKashanActivate = function(e) {
      e.preventDefault();
      e.stopPropagation();
      navigateTo('/kashan-branch');
    };
    el.addEventListener('click', onKashanActivate);
    el.addEventListener('touchend', onKashanActivate);
  });

  // 3. Modal logic & intelligent territory click for Isfahan province
  const modal = document.getElementById('esfahanBranchModal');
  if (modal) {
    const closeBtns = modal.querySelectorAll('[data-close-popover]');
    const closeModal = function() {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
    };
    const openModal = function() {
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
    };

    closeBtns.forEach(function(btn) {
      btn.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });

    // When clicking Isfahan province shape on the map
    const esfahanLink = document.querySelector('#Iran a.province-link[data-province="اصفهان"]');
    if (esfahanLink) {
      esfahanLink.addEventListener('click', function(e) {
        // If click was on or inside Kashan pin or city pin, let the pin handler do it
        if (e.target.closest && (e.target.closest('.branch-pin-link') || e.target.closest('[data-branch="kashan"]'))) {
          return;
        }

        // Check if the click coordinates fall inside northern Isfahan (the Kashan territory)
        const svg = document.getElementById('Iran');
        if (svg && svg.createSVGPoint && svg.getScreenCTM) {
          try {
            const pt = svg.createSVGPoint();
            pt.x = e.clientX;
            pt.y = e.clientY;
            const svgP = pt.matrixTransform(svg.getScreenCTM().inverse());
            // In SVG coordinates, Kashan territory is northern Isfahan (Y < 440 and X between 405 and 535)
            if (svgP.y < 440 && svgP.x >= 405 && svgP.x <= 535) {
              e.preventDefault();
              e.stopPropagation();
              navigateTo('/kashan-branch');
              return;
            }
          } catch (err) {
            // Coordinate check fallback
          }
        }

        e.preventDefault();
        openModal();
      });
    }
  }

  // 4. Universal province link click handler
  const otherProvLinks = document.querySelectorAll('#Iran a.province-link:not([data-province="اصفهان"])');
  otherProvLinks.forEach(function(link) {
    const href = link.getAttribute('href') || link.getAttribute('xlink:href');
    if (!href) return;
    link.addEventListener('click', function(e) {
      e.preventDefault();
      navigateTo(href);
    });
  });

  // 5. Cross-hover synchronization between map pins and sidebar list
  pinLinks.forEach(function(pin) {
    const href = pin.getAttribute('href') || pin.getAttribute('xlink:href');
    const slug = href ? href.replace(/^\//, '') : '';
    if (!slug) return;

    pin.addEventListener('mouseenter', function() {
      const item = document.querySelector('.br-item[data-slug="' + slug + '"]');
      if (item) item.classList.add('br-item--hovered');
    });
    pin.addEventListener('mouseleave', function() {
      const item = document.querySelector('.br-item[data-slug="' + slug + '"]');
      if (item) item.classList.remove('br-item--hovered');
    });
  });

  const sidebarItems = document.querySelectorAll('.br-item[data-slug]');
  sidebarItems.forEach(function(item) {
    const slug = item.getAttribute('data-slug');
    if (!slug) return;
    const pin = document.querySelector('#Iran a.branch-pin-link[href="/' + slug + '"], #Iran a.branch-pin-link[xlink\\:href="/' + slug + '"]');
    if (pin) {
      item.addEventListener('mouseenter', function() {
        pin.classList.add('is-hovered');
      });
      item.addEventListener('mouseleave', function() {
        pin.classList.remove('is-hovered');
      });
    }
  });
})();
</script>

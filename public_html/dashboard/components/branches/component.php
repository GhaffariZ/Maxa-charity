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
        <div class="bqc-pill bqc-pill--telemed" title="مرکز ارتباطات و دورپزشکی (تله‌مدیسین ۲۴ ساعته)">
          <span class="bqc-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
          </span>
          <span class="bqc-label">مرکز ارتباطات و دورپزشکی</span>
        </div>

        <!-- ۳. مرکز رویش استعدادهای دانشجویی -->
        <div class="bqc-pill bqc-pill--student" title="مرکز رویش استعدادهای دانشجویی مکسا">
          <span class="bqc-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
              <path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
          </span>
          <span class="bqc-label">مرکز رویش استعدادهای دانشجویی</span>
        </div>
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

/* مرزها و شکل استان‌ها — طراحی ملایم و چشم‌نواز */
#Iran .province-shape {
  stroke-linejoin: round !important;
  stroke-linecap: round !important;
  transition: fill 0.2s ease, filter 0.2s ease, stroke 0.2s ease;
}

/* استان‌های غیرفعال (زرد گرم برند مکسا) — بدون هاور و بدون کلیک با مرز نرم */
#Iran .province-shape.is-inactive,
#Iran g.is-inactive path,
#Iran g.is-inactive polygon {
  fill: #f4a61e !important;
  stroke: #d9820a !important;
  stroke-width: 0.75 !important;
  cursor: default !important;
  pointer-events: none !important;
  filter: none !important;
}

/* استان‌های فعال (آبی فیروزه‌ای شاخص مکسا) — دارای هاور و کلیک */
#Iran a.province-link {
  cursor: pointer;
  outline: none;
}

#Iran a.province-link .province-shape.is-active {
  fill: #007b7a !important;
  stroke: #004d4c !important;
  stroke-width: 1.1 !important;
  cursor: pointer !important;
  filter: drop-shadow(0 2px 5px rgba(0, 123, 122, 0.28));
}

#Iran a.province-link:hover .province-shape.is-active,
#Iran a.province-link:focus-visible .province-shape.is-active {
  fill: #10aeb8 !important;
  stroke: #003635 !important;
  stroke-width: 1.3 !important;
  filter: drop-shadow(0 6px 14px rgba(0, 123, 122, 0.45)) !important;
}

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
</style>

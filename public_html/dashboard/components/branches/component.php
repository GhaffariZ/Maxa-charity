<section class="branches" dir="rtl">
  <div class="branches__card">
    <header class="branches__head">
      <span class="branches__eyebrow">گستره خدمت‌رسانی مکسا در سراسر کشور</span>
      <h2 class="branches__title">شبکه شعب و مراکز تخصصی مکسا</h2>
      <p class="branches__subtitle">
        شبکه‌ای یکپارچه از متخصصان، داوطلبان و مراکز مراقبت تسکینی در استان‌های فعال در کنار بیماران مبتلا به سرطان و خانواده‌های آنان است.
      </p>
    </header>

    <div class="branches__mapbox">
      <?php require __DIR__ . '/map-svg.php'; ?>
    </div>

    <!-- راهنمای رنگ نقشه -->
    <div class="branches__legend">
      <div class="branches__legend-item branches__legend-item--active">
        <span class="branches__swatch branches__swatch--active">
          <span class="branches__swatch-dot"></span>
        </span>
        <span class="branches__legend-text">
          <strong>استان‌های دارای شعبه فعال</strong>
          <small>(تهران، اصفهان، خوزستان، آذربایجان شرقی، قم، خراسان رضوی)</small>
        </span>
      </div>
      <div class="branches__legend-item">
        <span class="branches__swatch branches__swatch--inactive"></span>
        <span class="branches__legend-text">سایر استان‌های کشور</span>
      </div>
    </div>

    <!-- ۳ مرکز و دفتر ویژه زیر نقشه کشور -->
    <div class="branches__special-centers">
      <div class="bsc-header">
        <span class="bsc-badge">واحدهای ستادی و راهبردی</span>
        <h3 class="bsc-title">مراکز ملی و توسعه خدمات مکسا</h3>
      </div>

      <div class="bsc-grid">
        <!-- ۱. دفتر ستاد مرکزی -->
        <div class="bsc-card bsc-card--hq">
          <div class="bsc-card__top">
            <div class="bsc-icon bsc-icon--hq">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 21h18"/>
                <path d="M9 8h1"/>
                <path d="M9 12h1"/>
                <path d="M9 16h1"/>
                <path d="M14 8h1"/>
                <path d="M14 12h1"/>
                <path d="M14 16h1"/>
                <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/>
              </svg>
            </div>
            <span class="bsc-tag bsc-tag--hq">ستاد مرکزی</span>
          </div>
          <div class="bsc-card__body">
            <h4 class="bsc-name">دفتر ستاد مرکزی</h4>
            <p class="bsc-desc">
              سیاست‌گذاری کلان، مدیریت یکپارچه شبکه شعب سراسر کشور، نظارت بر استانداردهای مراقبت تسکینی و راهبری راهبردی موسسه.
            </p>
          </div>
          <div class="bsc-card__footer">
            <span class="bsc-meta">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              تهران، ستاد مرکزی مکسا
            </span>
          </div>
        </div>

        <!-- ۲. مرکز ارتباطات و دورپزشکی -->
        <div class="bsc-card bsc-card--telemed">
          <div class="bsc-card__top">
            <div class="bsc-icon bsc-icon--telemed">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
              </svg>
            </div>
            <span class="bsc-tag bsc-tag--telemed">تله‌مدیسین و پایش</span>
          </div>
          <div class="bsc-card__body">
            <h4 class="bsc-name">مرکز ارتباطات و دورپزشکی</h4>
            <p class="bsc-desc">
              ارائه مشاوره‌های تخصصی پزشکی، تریاژ تلفنی و دورپزشکی ۲۴ ساعته جهت ارائه خدمات تسکینی به بیماران در اقصی‌نقاط کشور.
            </p>
          </div>
          <div class="bsc-card__footer">
            <span class="bsc-meta">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              پشتیبانی و مشاوره ۲۴ ساعته
            </span>
          </div>
        </div>

        <!-- ۳. مرکز رویش استعدادهای دانشجویی -->
        <div class="bsc-card bsc-card--student">
          <div class="bsc-card__top">
            <div class="bsc-icon bsc-icon--student">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
              </svg>
            </div>
            <span class="bsc-tag bsc-tag--student">جوانان و نوآوری</span>
          </div>
          <div class="bsc-card__body">
            <h4 class="bsc-name">مرکز رویش استعدادهای دانشجویی</h4>
            <p class="bsc-desc">
              شناسایی، آموزش و توانمندسازی دانشجویان داوطلب و نخبه دانشگاهی در عرصه‌های بهداشت، مراقبت تسکینی، مددکاری و نوآوری اجتماعی.
            </p>
          </div>
          <div class="bsc-card__footer">
            <span class="bsc-meta">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              شبکه دانشجویان داوطلب مکسا
            </span>
          </div>
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
  --color-primary-light: #10aeb8;
  --color-primary-soft: rgba(0, 123, 122, 0.08);
  --color-secondary: #f4a61e;
  --color-secondary-dark: #d98c0a;
  --color-text: #1e293b;
  --color-muted: #64748b;
  --color-border: #e2e8f0;
  --color-bg-card: #ffffff;
  --color-inactive-shape: #e6ebec;
  --color-inactive-stroke: #c8d3d5;

  box-sizing: border-box;
  width: 100%;
  padding: 30px 16px 50px;
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
  max-width: 1100px;
  margin: 0 auto;
  background: #ffffff;
  border: 1px solid rgba(0, 123, 122, 0.12);
  border-radius: 28px;
  padding: clamp(24px, 4vw, 48px);
  box-shadow: 0 12px 36px rgba(0, 77, 76, 0.04), 0 2px 8px rgba(0, 0, 0, 0.02);
}

/* سربرگ */
.branches__head {
  text-align: center;
  margin-bottom: 24px;
}

.branches__eyebrow {
  display: inline-block;
  font-size: 0.84rem;
  font-weight: 700;
  color: var(--color-primary);
  background: var(--color-primary-soft);
  border: 1px solid rgba(0, 123, 122, 0.15);
  padding: 6px 18px;
  border-radius: 999px;
  margin-bottom: 12px;
}

.branches__title {
  margin: 0 0 12px;
  font-size: clamp(1.6rem, 3.2vw, 2.3rem);
  font-weight: 900;
  line-height: 1.35;
  color: #0f172a;
}

.branches__title::after {
  content: "";
  display: block;
  width: 60px;
  height: 4px;
  margin: 12px auto 0;
  border-radius: 999px;
  background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
}

.branches__subtitle {
  max-width: 640px;
  margin: 0 auto;
  font-size: 0.98rem;
  line-height: 1.9;
  color: var(--color-muted);
}

/* نقشه */
.branches__mapbox {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 20px auto 14px;
  position: relative;
  overflow: visible;
}

#Iran {
  display: block;
  width: 100%;
  max-width: 820px;
  height: auto;
  margin: 0 auto;
  transform: none;
  filter: drop-shadow(0 10px 24px rgba(0, 77, 76, 0.06));
}

/* استایل استان‌های فعال */
#Iran a.province-link {
  cursor: pointer;
  outline: none;
}

#Iran path.province-shape.is-active,
#Iran polygon.province-shape.is-active {
  fill: var(--color-primary);
  stroke: var(--color-primary-dark);
  stroke-width: 1.3;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  filter: drop-shadow(0 2px 6px rgba(0, 123, 122, 0.3));
}

#Iran a.province-link:hover path.province-shape.is-active,
#Iran a.province-link:hover polygon.province-shape.is-active,
#Iran a.province-link:focus-visible path.province-shape.is-active {
  fill: var(--color-primary-light);
  stroke: #002b2a;
  stroke-width: 1.8;
  filter: drop-shadow(0 8px 18px rgba(0, 123, 122, 0.45));
}

#Iran a.province-link:active path.province-shape.is-active {
  fill: #005958;
}

/* استایل استان‌های غیرفعال: بدون هاور و بدون کلیک */
#Iran .province-shape.is-inactive,
#Iran path.province-shape.is-inactive,
#Iran polygon.province-shape.is-inactive {
  fill: var(--color-inactive-shape) !important;
  stroke: var(--color-inactive-stroke) !important;
  stroke-width: 0.85 !important;
  cursor: default !important;
  pointer-events: none !important;
  filter: none !important;
}

/* برچسب‌های متنی روی نقشه */
#Iran .map-labels text {
  font-family: 'Vazirmatn', sans-serif;
  text-anchor: middle;
  dominant-baseline: central;
  pointer-events: none;
  user-select: none;
}

#Iran .map-labels .lbl-inactive {
  fill: #64748b;
  font-weight: 600;
  opacity: 0.85;
}

#Iran .map-labels .lbl-active {
  fill: #ffffff;
  font-weight: 900;
  filter: drop-shadow(0 1.5px 3px rgba(0, 0, 0, 0.85));
}

#Iran .map-labels .lbl-active-dot {
  fill: var(--color-secondary);
  stroke: #ffffff;
  stroke-width: 1.4;
  filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.4));
}

/* راهنمای رنگ‌ها */
.branches__legend {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 16px 36px;
  margin: 16px auto 36px;
  padding: 16px 20px;
  background: #f8fafc;
  border: 1px solid #edf2f7;
  border-radius: 16px;
  max-width: 760px;
}

.branches__legend-item {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 0.92rem;
  color: #334155;
}

.branches__legend-item--active {
  color: var(--color-primary-dark);
}

.branches__swatch {
  width: 20px;
  height: 20px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.branches__swatch--active {
  background: var(--color-primary);
  border: 1.5px solid var(--color-primary-dark);
  box-shadow: 0 2px 6px rgba(0, 123, 122, 0.3);
}

.branches__swatch-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--color-secondary);
}

.branches__swatch--inactive {
  background: var(--color-inactive-shape);
  border: 1px solid var(--color-inactive-stroke);
}

.branches__legend-text strong {
  display: inline-block;
  font-weight: 800;
}

.branches__legend-text small {
  display: inline-block;
  color: var(--color-muted);
  font-weight: 500;
  margin-right: 4px;
}

/* === ۳ مرکز ویژه زیر نقشه =============================================== */
.branches__special-centers {
  margin-top: 40px;
  padding-top: 36px;
  border-top: 1.5px dashed #e2e8f0;
}

.bsc-header {
  text-align: center;
  margin-bottom: 24px;
}

.bsc-badge {
  display: inline-block;
  font-size: 0.78rem;
  font-weight: 700;
  color: #c2410c;
  background: rgba(249, 115, 22, 0.1);
  padding: 4px 14px;
  border-radius: 999px;
  margin-bottom: 8px;
}

.bsc-title {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 800;
  color: #1e293b;
}

.bsc-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.bsc-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 24px 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  overflow: hidden;
}

.bsc-card::before {
  content: "";
  position: absolute;
  top: 0;
  right: 0;
  left: 0;
  height: 4px;
  background: transparent;
  transition: background 0.25s ease;
}

.bsc-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 32px rgba(0, 123, 122, 0.08);
}

.bsc-card--hq:hover {
  border-color: #f4a61e;
}
.bsc-card--hq::before {
  background: linear-gradient(90deg, #f4a61e, #d98c0a);
}

.bsc-card--telemed:hover {
  border-color: #007b7a;
}
.bsc-card--telemed::before {
  background: linear-gradient(90deg, #007b7a, #10aeb8);
}

.bsc-card--student:hover {
  border-color: #2563eb;
}
.bsc-card--student::before {
  background: linear-gradient(90deg, #2563eb, #38bdf8);
}

.bsc-card__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.bsc-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.bsc-icon--hq {
  background: rgba(244, 166, 30, 0.14);
  color: #b45309;
}

.bsc-icon--telemed {
  background: rgba(0, 123, 122, 0.12);
  color: #007b7a;
}

.bsc-icon--student {
  background: rgba(37, 99, 235, 0.12);
  color: #1d4ed8;
}

.bsc-tag {
  font-size: 0.76rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 8px;
}

.bsc-tag--hq {
  background: rgba(244, 166, 30, 0.12);
  color: #b45309;
}

.bsc-tag--telemed {
  background: rgba(0, 123, 122, 0.12);
  color: #007b7a;
}

.bsc-tag--student {
  background: rgba(37, 99, 235, 0.12);
  color: #1d4ed8;
}

.bsc-card__body {
  flex: 1 1 auto;
}

.bsc-name {
  margin: 0 0 10px;
  font-size: 1.15rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.4;
}

.bsc-desc {
  margin: 0;
  font-size: 0.88rem;
  line-height: 1.85;
  color: var(--color-muted);
}

.bsc-card__footer {
  margin-top: 18px;
  padding-top: 14px;
  border-top: 1px solid #f1f5f9;
}

.bsc-meta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #475569;
}

.bsc-meta svg {
  color: var(--color-primary);
  flex-shrink: 0;
}

/* فونت وزیرمتن */
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
       url('/webfont/Vazirmatn[wght].woff2') format('woff2');
  font-weight: 100 900;
  font-style: normal;
  font-display: swap;
}

/* واکنش‌گرایی */
@media (max-width: 992px) {
  .bsc-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .bsc-card--student {
    grid-column: span 2;
  }
}

@media (max-width: 768px) {
  .branches {
    padding: 20px 10px 40px;
  }
  .branches__card {
    padding: 20px 14px;
    border-radius: 20px;
  }
  #Iran {
    width: 98%;
  }
  .branches__legend {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .bsc-grid {
    grid-template-columns: 1fr;
  }
  .bsc-card--student {
    grid-column: auto;
  }
}

@media (prefers-reduced-motion: reduce) {
  #Iran a path,
  #Iran a polygon,
  .bsc-card {
    transition: none !important;
  }
}
</style>

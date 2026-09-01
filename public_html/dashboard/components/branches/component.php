<section class="branches" dir="rtl">
  <div class="branches__card">
    <header class="branches__head">
      <span class="branches__eyebrow">شعب ماکسا در سراسر کشور</span>
      <h2 class="branches__title">گستره خدمات ما در ایران</h2>
      <p class="branches__subtitle">
        برای دیدن استان‌ها روی نقشه حرکت کنید. شبکه‌ای از داوطلبان و مراکز
        مراقبت تسکینی در سراسر کشور در کنار شماست.
      </p>
    </header>

    <div class="branches__mapbox">
<?php require __DIR__ . '/map-svg.php'; ?>
    </div>

    <footer class="branches__legend">
      <span class="branches__legend-item">
        <span class="branches__swatch branches__swatch--province"></span>
        استان تحت پوشش
      </span>
      <span class="branches__legend-item">
        <span class="branches__swatch branches__swatch--active"></span>
        استان فعال (با اشاره‌گر)
      </span>
    </footer>
  </div>
</section>

    <style>
/* === Branches map — Maxa palette ============================================ */
.branches {
  /* پالت رنگی پروژه */
  --color-primary: #007b7a;
  --color-primary-dark: #006665;
  --color-primary-light: #4fb2b0;
  --color-secondary: #f4a61e;
  --color-text: #2f3437;
  --color-muted: #9d9d9d;
  --color-border: #e6e8ea;
  --color-bg: #f8f9fa;
  --color-surface: #ffffff;

  box-sizing: border-box;
  width: 100%;
  padding: 40px 16px;
  background: transparent;
  font-family: 'Vazirmatn', sans-serif;
  color: var(--color-text);
}
.branches *,
.branches *::before,
.branches *::after { box-sizing: border-box; }

/* کارت اصلی */
.branches__card {
  max-width: 980px;
  margin: 0 auto;
  background: transparent;
  padding: clamp(20px, 4vw, 44px);
}

/* سربرگ */
.branches__head { text-align: center; margin-bottom: 8px; }
.branches__eyebrow {
  display: inline-block;
  font-size: 0.82rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  color: var(--color-primary);
  background: rgba(0, 123, 122, 0.08);
  padding: 6px 16px;
  border-radius: 999px;
  margin-bottom: 14px;
}
.branches__title {
  margin: 0 0 12px;
  font-size: clamp(1.5rem, 3.6vw, 2.25rem);
  font-weight: 800;
  line-height: 1.3;
  color: var(--color-text);
}
.branches__title::after {
  content: "";
  display: block;
  width: 56px;
  height: 4px;
  margin: 14px auto 0;
  border-radius: 999px;
  background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
}
.branches__subtitle {
  max-width: 540px;
  margin: 0 auto;
  font-size: 0.98rem;
  line-height: 1.9;
  color: var(--color-muted);
}

/* جعبه نقشه */
.branches__mapbox {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  margin: 28px auto 8px;
}

/* SVG — کنترل با عرض واقعی، بدون scale */
#Iran {
  display: block;
  width: 78%;
  max-width: 760px;
  height: auto;
  margin: 0 auto;
  transform: none;
  transform-origin: top center;
  vertical-align: top;
}

/* استان‌ها: استایل پایه + انیمیشن روان */
#Iran a path,
#Iran a polygon {
  fill: var(--color-secondary);
  stroke: #d98c0a;
  stroke-width: 0.9;
  transition: fill 0.2s ease, filter 0.2s ease, transform 0.2s ease;
  transform-box: fill-box;
  transform-origin: center;
  cursor: pointer;
}

/* استان‌های فعال (دارای شعبه): تأکید با رنگ اصلی برند (فیروزه‌ای) */
#Iran a path.is-active,
#Iran path.is-active {
  fill: var(--color-primary-light);
  stroke: var(--color-primary-dark);
}

/* هاور: روشن‌شدن استان با رنگ ثانویه کهربایی */
#Iran a:hover path,
#Iran a:hover polygon,
#Iran a:focus-visible path,
#Iran a:focus-visible polygon {
  fill: #e0900c;
  filter: drop-shadow(0 4px 8px rgba(217, 140, 10, 0.45));
}

/* هاور روی استان فعال: فیروزه‌ای پررنگ‌تر (تأکید بیشتر، نه از‌دست‌رفتن هویت) */
#Iran a:hover path.is-active,
#Iran a:focus-visible path.is-active {
  fill: var(--color-primary);
  filter: drop-shadow(0 4px 8px rgba(0, 102, 101, 0.35));
}

/* کلیک: تیره‌ترین حالت */
#Iran a:active path {
  fill: #c97e08;
}
#Iran a:active path.is-active {
  fill: var(--color-primary-dark);
}

#Iran a:focus { outline: none; }

/* برچسب استان‌ها */
#Iran text {
  font-family: 'Vazirmatn', sans-serif;
  fill: var(--color-primary-dark);
  font-weight: 600;
  pointer-events: none;
}

/* خطوط راهنمای استان‌های کوچک */
#Iran line { stroke: var(--color-primary-dark); }

/* فونت — Self-hosted Vazirmatn (بدون CDN خارجی، مناسب شبکه داخلی) */
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
       url('/webfont/Vazirmatn[wght].woff2') format('woff2');
  font-weight: 100 900;
  font-style: normal;
  font-display: swap;
}

/* راهنمای رنگ‌ها */
.branches__legend {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 14px 28px;
  margin-top: 20px;
  padding-top: 22px;
  border-top: 1px solid var(--color-border);
}
.branches__legend-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  color: var(--color-text);
}
.branches__swatch {
  width: 16px;
  height: 16px;
  border-radius: 5px;
  border: 1px solid var(--color-primary-dark);
}
.branches__swatch--province {
  background: var(--color-secondary);
  border-color: #d98c0a;
}
.branches__swatch--active { background: var(--color-primary-light); }

/* واکنش‌گرا */
@media (max-width: 768px) {
  .branches { padding: 28px 12px; }
  #Iran { width: 94%; }
  .branches__subtitle { font-size: 0.92rem; }
}
@media (max-width: 480px) {
  .branches__card { border-radius: 16px; }
  .branches__legend { gap: 10px 18px; }
}

/* احترام به ترجیح کاهش حرکت */
@media (prefers-reduced-motion: reduce) {
  #Iran a path,
  #Iran a polygon { transition: none; }
}
    </style>

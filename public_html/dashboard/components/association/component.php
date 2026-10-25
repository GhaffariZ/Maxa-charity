<?php require_once __DIR__ . '/../../../core/site-locale.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

<section class="charity-doc-component" dir="<?= site_direction() ?>">
<div class="page-header-wrapper">
    <header class="page-header-capsule" dir="<?= site_direction() ?>">
        <h1><?= site_e('اساسنامه') ?> </h1>
        <div class="page-header-breadcrumb"><?= site_e('صفحه اصلی / خدمات') ?></div>
    </header>
</div>
  <div class="charity-doc-card">

    <header class="charity-doc-header">
      <div class="charity-doc-actions">
        <button type="button" class="charity-doc-btn js-zoom-out" title="<?= site_e('کوچک‌نمایی') ?>">
          −
        </button>

        <span class="charity-doc-zoom-label js-zoom-label">
          100%
        </span>

        <button type="button" class="charity-doc-btn js-zoom-in" title="<?= site_e('بزرگ‌نمایی') ?>">
          +
        </button>

        <button type="button" class="charity-doc-btn js-reset-zoom" title="<?= site_e('بازگشت به حالت عادی') ?>">
          <?= site_e('۱:۱') ?>
        </button>

        <button type="button" class="charity-doc-btn charity-doc-btn-primary js-fullscreen" title="<?= site_e('نمایش تمام‌صفحه') ?>" aria-pressed="false">
          <?= site_e('تمام‌صفحه') ?>
        </button>
      </div>
    </header>

    <div class="charity-doc-toolbar">
      <div class="charity-doc-page-status">
        <span class="js-page-current">1</span>
        <span><?= site_e('از') ?></span>
        <span class="js-page-total">6</span>
      </div>

      <div class="charity-doc-note">
        <?= site_e('برای جابه‌جایی بین صفحات از فلش‌ها استفاده کنید. در حالت زوم، تصویر را با موس بگیرید و حرکت دهید.') ?>
      </div>
    </div>

    <div class="charity-doc-viewer-shell" oncontextmenu="return false;">

      <div class="swiper charityDocSwiper">

        <div class="swiper-wrapper">

          <div class="swiper-slide">
            <div class="charity-doc-pan-area">
              <div class="charity-doc-page">
                <img src="{{image1}}" class="charity-doc-img" alt="<?= site_e('صفحه ۱ اساسنامه') ?>" draggable="false">
                <div class="charity-doc-watermark"><?= site_e('مکسا - نسخه نمایشی عمومی') ?></div>
              </div>
            </div>
          </div>

          <div class="swiper-slide">
            <div class="charity-doc-pan-area">
              <div class="charity-doc-page">
                <img src="{{image2}}" class="charity-doc-img" alt="<?= site_e('صفحه ۲ اساسنامه') ?>" draggable="false">
                <div class="charity-doc-watermark"><?= site_e('مکسا - نسخه نمایشی عمومی') ?></div>
              </div>
            </div>
          </div>

          <div class="swiper-slide">
            <div class="charity-doc-pan-area">
              <div class="charity-doc-page">
                <img src="{{image3}}" class="charity-doc-img" alt="<?= site_e('صفحه ۳ اساسنامه') ?>" draggable="false">
                <div class="charity-doc-watermark"><?= site_e('مکسا - نسخه نمایشی عمومی') ?></div>
              </div>
            </div>
          </div>

          <div class="swiper-slide">
            <div class="charity-doc-pan-area">
              <div class="charity-doc-page">
                <img src="{{image4}}" class="charity-doc-img" alt="<?= site_e('صفحه ۴ اساسنامه') ?>" draggable="false">
                <div class="charity-doc-watermark"><?= site_e('مکسا - نسخه نمایشی عمومی') ?></div>
              </div>
            </div>
          </div>

          <div class="swiper-slide">
            <div class="charity-doc-pan-area">
              <div class="charity-doc-page">
                <img src="{{image5}}" class="charity-doc-img" alt="<?= site_e('صفحه ۵ اساسنامه') ?>" draggable="false">
                <div class="charity-doc-watermark"><?= site_e('مکسا - نسخه نمایشی عمومی') ?></div>
              </div>
            </div>
          </div>

          <div class="swiper-slide">
            <div class="charity-doc-pan-area">
              <div class="charity-doc-page">
                <img src="{{image6}}" class="charity-doc-img" alt="<?= site_e('صفحه ۶ اساسنامه') ?>" draggable="false">
                <div class="charity-doc-watermark"><?= site_e('مکسا - نسخه نمایشی عمومی') ?></div>
              </div>
            </div>
          </div>

        </div>

        <div class="swiper-button-next charity-doc-next"></div>
        <div class="swiper-button-prev charity-doc-prev"></div>
        <div class="swiper-pagination charity-doc-pagination"></div>

      </div>

      <div class="charity-doc-protection-layer js-protection-layer">
        <div class="charity-doc-protection-message">
          <?= site_e('نمایش سند موقتاً مخفی شد.') ?>
        </div>
      </div>

    </div>

    <footer class="charity-doc-footer">
      <span><?= site_e('© تمامی حقوق این سند متعلق به مکسا است.') ?></span>
      <span><?= site_e('کپی‌برداری، ذخیره‌سازی یا انتشار بدون مجوز مجاز نیست.') ?></span>
    </footer>

  </div>

</section>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<style>
/* Self-hosted Vazirmatn variable font (reliable on the Iran network, no external CDN) */
@font-face {
    font-family: 'Vazirmatn';
    src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
         url('/webfont/Vazirmatn[wght].woff2') format('woff2');
    font-weight: 100 900;
    font-style: normal;
    font-display: swap;
}
.page-header-capsule {
    background: linear-gradient(180deg, #f8f9fa 0%, #eef3f6 100%);
    border-radius: 50px;
    padding: 50px 30px;
    margin-bottom: 40px;
    text-align: center;
    font-family: 'Vazirmatn', sans-serif;
}

.page-header-capsule h1 {
    font-size: 2.5rem;
    margin: 0 0 15px 0;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.4;
}

.page-header-breadcrumb {
    background-color: #008f8a;
    color: #ffffff;
    padding: 6px 20px;
    border-radius: 50px;
    font-size: 0.85rem;
    display: inline-block;
    line-height: 1.8;
}

@media (max-width: 768px) {
    .page-header-capsule {
        border-radius: 30px;
        padding: 30px 15px;
        margin-bottom: 30px;
    }

    .page-header-capsule h1 {
        font-size: 1.8rem;
    }

    .page-header-breadcrumb {
        font-size: 0.8rem;
        padding: 5px 16px;
    }
}
  .charity-doc-component,
  .charity-doc-component * {
    box-sizing: border-box;
  }

  .charity-doc-component {
    --doc-bg: #f4f7fb;
    --doc-card: #ffffff;
    --doc-border: #e5e7eb;
    --doc-text: #1f2937;
    --doc-muted: #6b7280;
    --doc-primary: #0f766e;
    --doc-primary-hover: #0d5f59;
    --doc-soft: #f8fafc;
    --doc-shadow: 0 24px 70px rgba(15, 23, 42, 0.10);

    width: 100%;
    max-width: 960px;
    margin: 40px auto;
    padding: 0 14px;
    font-family: 'Vazirmatn', sans-serif;
    color: var(--doc-text);
  }

  .charity-doc-card {
    background: var(--doc-card);
    border: 1px solid var(--doc-border);
    border-radius: 22px;
    box-shadow: var(--doc-shadow);
    overflow: hidden;
  }

  .charity-doc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 18px 22px;
    background:
      radial-gradient(circle at top right, rgba(15, 118, 110, 0.10), transparent 35%),
      linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    border-bottom: 1px solid var(--doc-border);
  }

  .charity-doc-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
  }

  .charity-doc-icon {
    width: 46px;
    height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: rgba(15, 118, 110, 0.10);
    font-size: 22px;
    flex: 0 0 auto;
  }

  .charity-doc-title {
    margin: 0;
    font-size: 19px;
    font-weight: 800;
    line-height: 1.6;
    color: var(--doc-text);
  }

  .charity-doc-subtitle {
    margin: 2px 0 0;
    font-size: 13px;
    color: var(--doc-muted);
    line-height: 1.8;
  }

  .charity-doc-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }

  .charity-doc-btn {
    appearance: none;
    border: 1px solid var(--doc-border);
    background: #ffffff;
    color: var(--doc-text);
    min-width: 36px;
    height: 36px;
    padding: 0 11px;
    border-radius: 11px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    user-select: none;
  }

  .charity-doc-btn:hover {
    background: #f3f4f6;
    transform: translateY(-1px);
  }

  .charity-doc-btn-primary {
    background: var(--doc-primary);
    color: #ffffff;
    border-color: var(--doc-primary);
    padding-inline: 15px;
  }

  .charity-doc-btn-primary:hover {
    background: var(--doc-primary-hover);
  }

  .charity-doc-zoom-label {
    min-width: 54px;
    height: 36px;
    padding: 0 8px;
    border-radius: 11px;
    background: var(--doc-soft);
    color: var(--doc-muted);
    font-size: 13px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--doc-border);
  }

  .charity-doc-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 10px 22px;
    background: #fcfcfd;
    border-bottom: 1px solid var(--doc-border);
  }

  .charity-doc-page-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 32px;
    padding: 0 12px;
    border-radius: 999px;
    background: rgba(15, 118, 110, 0.08);
    color: var(--doc-primary);
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
  }

  .charity-doc-note {
    color: var(--doc-muted);
    font-size: 12px;
    line-height: 1.8;
    text-align: left;
  }

  .charity-doc-viewer-shell {
    position: relative;
    background:
      linear-gradient(45deg, #f8fafc 25%, transparent 25%),
      linear-gradient(-45deg, #f8fafc 25%, transparent 25%),
      linear-gradient(45deg, transparent 75%, #f8fafc 75%),
      linear-gradient(-45deg, transparent 75%, #f8fafc 75%);
    background-size: 22px 22px;
    background-position: 0 0, 0 11px, 11px -11px, -11px 0;
  }

  .charity-doc-component .charityDocSwiper {
    width: 100%;
    height: 680px;
    background: rgba(248, 250, 252, 0.82);
  }

  .charity-doc-component .swiper-slide {
    width: 100%;
    height: 100%;
  }

.charity-doc-pan-area {
  width: 100%;
  height: 100%;
  overflow: auto;
  padding: 26px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  cursor: default;
  scroll-behavior: smooth;

  /* مهم برای درست شدن حرکت افقی با موس */
  direction: ltr;
}

.charity-doc-pan-area.is-zoomed {
  cursor: grab;
  justify-content: flex-start;
  align-items: flex-start;
}

  .charity-doc-pan-area.is-dragging {
    cursor: grabbing;
    scroll-behavior: auto;
  }

.charity-doc-page {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;

  /* بهتر برای Drag افقی */
  transform-origin: top left;

  transition: transform 0.2s ease;
  background: #ffffff;
  border-radius: 12px;
  box-shadow:
    0 20px 35px rgba(15, 23, 42, 0.12),
    0 0 0 1px rgba(15, 23, 42, 0.08);
  overflow: hidden;

  /* محتوای داخل سند همچنان راست‌به‌چپ بماند */
  direction: var(--site-direction, rtl);
}

  .charity-doc-img {
    display: block;
    width: auto;
    height: auto;
    max-width: 100%;
    max-height: 610px;
    object-fit: contain;
    user-select: none;
    -webkit-user-select: none;
    -webkit-user-drag: none;
    pointer-events: none;
  }

.charity-doc-watermark {
    position: absolute;
    inset: 0;
    pointer-events: none;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;

    transform: rotate(-28deg);

    /* سایز کوچک‌تر و بدون خروج از صفحه */
    font-size: clamp(14px, 2.6vw, 26px);
    font-weight: 800;

    color: rgba(15, 118, 110, 0.12);
    letter-spacing: 0px;

    white-space: nowrap;
    user-select: none;

    /* کنترل بهتر برای نمایش */
    padding: 0 20px;
}


  .charity-doc-component .swiper-button-next,
  .charity-doc-component .swiper-button-prev {
    width: 42px;
    height: 42px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.92);
    color: var(--doc-primary);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
    border: 1px solid rgba(229, 231, 235, 0.9);
  }

  .charity-doc-component .swiper-button-next::after,
  .charity-doc-component .swiper-button-prev::after {
    font-size: 16px;
    font-weight: 900;
  }

  .charity-doc-component .swiper-pagination-bullet {
    width: 7px;
    height: 7px;
    opacity: 0.35;
    background: var(--doc-primary);
  }

  .charity-doc-component .swiper-pagination-bullet-active {
    width: 20px;
    border-radius: 99px;
    opacity: 1;
    background: var(--doc-primary);
  }

  .charity-doc-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 12px 22px;
    background: #ffffff;
    border-top: 1px solid var(--doc-border);
    color: var(--doc-muted);
    font-size: 12px;
    line-height: 1.8;
  }

  .charity-doc-protection-layer {
    position: absolute;
    inset: 0;
    z-index: 30;
    background: rgba(15, 23, 42, 0.96);
    color: #ffffff;
    display: none;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 24px;
  }

  .charity-doc-protection-layer.is-active {
    display: flex;
  }

  .charity-doc-protection-message {
    max-width: 360px;
    font-size: 15px;
    line-height: 2;
    background: rgba(255, 255, 255, 0.08);
    padding: 18px 20px;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.12);
  }

  .charity-doc-component .charity-doc-card.is-fullscreen {
    position: fixed;
    inset: 0;
    z-index: 200001;
    width: 100%;
    height: 100vh;
    height: 100dvh;
    max-width: none;
    margin: 0;
    border: 0;
    border-radius: 0;
    display: flex;
    flex-direction: column;
  }

  .charity-doc-card.is-fullscreen .charity-doc-header,
  .charity-doc-card.is-fullscreen .charity-doc-toolbar,
  .charity-doc-card.is-fullscreen .charity-doc-footer {
    flex-shrink: 0;
    padding: 8px 12px;
  }

  .charity-doc-card.is-fullscreen .charity-doc-note {
    display: none;
  }

  .charity-doc-card.is-fullscreen .charity-doc-viewer-shell {
    flex: 1;
    min-height: 0;
  }

  .charity-doc-component .charity-doc-card.is-fullscreen .charityDocSwiper {
    height: 100%;
  }

  .charity-doc-card.is-fullscreen .charity-doc-pan-area {
    padding: 12px;
  }

  .charity-doc-card.is-fullscreen .charity-doc-pan-area:not(.is-zoomed) {
    align-items: center;
  }

  .charity-doc-card.is-fullscreen .charity-doc-page {
    height: 100%;
    max-width: 100%;
    background: transparent;
    box-shadow: none;
  }

  .charity-doc-card.is-fullscreen .charity-doc-img {
    height: 100%;
    max-height: 100%;
  }

  @media (max-width: 768px) {
    .charity-doc-component {
      margin: 20px auto;
      padding: 0 10px;
    }

    .charity-doc-header {
      flex-direction: column;
      align-items: stretch;
      padding: 16px;
    }

    .charity-doc-actions {
      justify-content: flex-start;
    }

    .charity-doc-toolbar {
      flex-direction: column;
      align-items: flex-start;
      padding: 10px 16px;
    }

    .charity-doc-note {
      text-align: start;
    }

    .charity-doc-component .charityDocSwiper {
      height: 540px;
    }

    .charity-doc-pan-area {
      padding: 16px;
    }

    .charity-doc-img {
      max-height: 490px;
    }

    .charity-doc-footer {
      flex-direction: column;
      align-items: flex-start;
      padding: 12px 16px;
    }
  }

  @media (max-width: 480px) {
    .charity-doc-component .charityDocSwiper {
      height: 470px;
    }

    .charity-doc-title {
      font-size: 17px;
    }

    .charity-doc-subtitle {
      font-size: 12px;
    }

    .charity-doc-btn {
      height: 34px;
      min-width: 34px;
      font-size: 13px;
    }

    .charity-doc-img {
      max-height: 420px;
    }
  }
</style>

<script>
  (function () {
    const component = document.querySelector(".charity-doc-component");
    if (!component) return;

    const currentPageEl = component.querySelector(".js-page-current");
    const totalPageEl = component.querySelector(".js-page-total");
    const zoomLabel = component.querySelector(".js-zoom-label");
    const zoomInBtn = component.querySelector(".js-zoom-in");
    const zoomOutBtn = component.querySelector(".js-zoom-out");
    const resetZoomBtn = component.querySelector(".js-reset-zoom");
    const fullscreenBtn = component.querySelector(".js-fullscreen");
    const card = component.querySelector(".charity-doc-card");
    const protectionLayer = component.querySelector(".js-protection-layer");
    let fallbackFullscreen = false;
    let previousOverflow = null;

    const slidesCount = component.querySelectorAll(".swiper-slide").length;
    totalPageEl.textContent = slidesCount;

    let zoom = 1;
    const minZoom = 1;
    const maxZoom = 2.4;
    const zoomStep = 0.2;

    const swiper = new Swiper(".charityDocSwiper", {
      slidesPerView: 1,
      spaceBetween: 0,
      speed: 350,
      grabCursor: false,
      allowTouchMove: true,

      navigation: {
        nextEl: ".charity-doc-next",
        prevEl: ".charity-doc-prev"
      },

      pagination: {
        el: ".charity-doc-pagination",
        clickable: true
      },

      keyboard: {
        enabled: true
      },

      on: {
        slideChange: function () {
          currentPageEl.textContent = this.activeIndex + 1;
          resetPan();
        }
      }
    });

    function getActivePanArea() {
      return component.querySelector(".swiper-slide-active .charity-doc-pan-area");
    }

    function getPages() {
      return component.querySelectorAll(".charity-doc-page");
    }

    function applyZoom() {
      zoom = Math.max(minZoom, Math.min(maxZoom, zoom));

      getPages().forEach(function (page) {
        page.style.transform = "scale(" + zoom + ")";
      });

      zoomLabel.textContent = Math.round(zoom * 100) + "%";

      component.querySelectorAll(".charity-doc-pan-area").forEach(function (area) {
        if (zoom > 1) {
          area.classList.add("is-zoomed");
        } else {
          area.classList.remove("is-zoomed");
          area.scrollTop = 0;
          area.scrollLeft = 0;
        }
      });

      swiper.allowTouchMove = zoom <= 1;
    }

    function resetPan() {
      component.querySelectorAll(".charity-doc-pan-area").forEach(function (area) {
        area.scrollTop = 0;
        area.scrollLeft = 0;
      });
    }

    zoomInBtn.addEventListener("click", function () {
      zoom += zoomStep;
      applyZoom();
    });

    zoomOutBtn.addEventListener("click", function () {
      zoom -= zoomStep;
      applyZoom();
    });

    resetZoomBtn.addEventListener("click", function () {
      zoom = 1;
      applyZoom();
      resetPan();
    });

    function fullscreenElement() {
      return document.fullscreenElement || document.webkitFullscreenElement;
    }

    function updateFullscreen() {
      const expanded = fallbackFullscreen || fullscreenElement() === card;
      card.classList.toggle("is-fullscreen", expanded);
      fullscreenBtn.textContent = expanded ? <?= site_js_text('خروج از تمام‌صفحه') ?> : <?= site_js_text('تمام‌صفحه') ?>;
      fullscreenBtn.title = fullscreenBtn.textContent;
      fullscreenBtn.setAttribute("aria-pressed", String(expanded));
      if (expanded) {
        if (previousOverflow === null) {
          previousOverflow = [document.documentElement.style.overflow, document.body.style.overflow];
        }
        document.documentElement.style.overflow = "hidden";
        document.body.style.overflow = "hidden";
        card.setAttribute("role", "dialog");
        card.setAttribute("aria-modal", "true");
        card.setAttribute("aria-label", <?= site_js_text('نمایشگر اساسنامه') ?>);
      } else {
        if (previousOverflow !== null) {
          document.documentElement.style.overflow = previousOverflow[0];
          document.body.style.overflow = previousOverflow[1];
          previousOverflow = null;
          fullscreenBtn.focus({ preventScroll: true });
        }
        card.removeAttribute("role");
        card.removeAttribute("aria-modal");
        card.removeAttribute("aria-label");
      }
      // Size Swiper after the card has its final height, excluding the page title.
      requestAnimationFrame(function () {
        swiper.update();
        resetPan();
        if (!document.hidden) protectionLayer.classList.remove("is-active");
      });
    }

    fullscreenBtn.addEventListener("click", async function () {
      if (fallbackFullscreen) {
        fallbackFullscreen = false;
        updateFullscreen();
        return;
      }
      if (fullscreenElement() === card) {
        const exit = document.exitFullscreen || document.webkitExitFullscreen;
        try {
          if (exit) await exit.call(document);
        } catch (error) {
          updateFullscreen();
        }
        return;
      }
      fullscreenBtn.disabled = true;
      try {
        const request = card.requestFullscreen || card.webkitRequestFullscreen;
        if (!request) throw new Error("Fullscreen API unavailable");
        await request.call(card);
      } catch (error) {
        // iPhone and restricted browsers can still expand the viewer to the viewport.
        fallbackFullscreen = true;
      } finally {
        fullscreenBtn.disabled = false;
        updateFullscreen();
      }
    });

    document.addEventListener("fullscreenchange", updateFullscreen);
    document.addEventListener("webkitfullscreenchange", updateFullscreen);
    document.addEventListener("keydown", function (event) {
      if (card.classList.contains("is-fullscreen") && event.key === "Escape") {
        event.preventDefault();
        fullscreenBtn.click();
      } else if (card.classList.contains("is-fullscreen") && event.key === "Tab") {
        const controls = card.querySelectorAll('button:not(:disabled), [tabindex="0"]');
        const first = controls[0];
        const last = controls[controls.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      }
    });

    // زوم با چرخ موس در حالی که Ctrl نگه داشته شده
    component.addEventListener("wheel", function (e) {
      if (!e.ctrlKey) return;

      e.preventDefault();

      if (e.deltaY < 0) {
        zoom += 0.1;
      } else {
        zoom -= 0.1;
      }

      applyZoom();
    }, { passive: false });

    // Drag to pan با موس در حالت زوم
    component.querySelectorAll(".charity-doc-pan-area").forEach(function (area) {
      let isDown = false;
      let startX;
      let startY;
      let scrollLeft;
      let scrollTop;

      area.addEventListener("mousedown", function (e) {
        if (zoom <= 1) return;

        isDown = true;
        area.classList.add("is-dragging");

        startX = e.pageX - area.offsetLeft;
        startY = e.pageY - area.offsetTop;
        scrollLeft = area.scrollLeft;
        scrollTop = area.scrollTop;

        e.preventDefault();
      });

      area.addEventListener("mouseleave", function () {
        isDown = false;
        area.classList.remove("is-dragging");
      });

      area.addEventListener("mouseup", function () {
        isDown = false;
        area.classList.remove("is-dragging");
      });

      area.addEventListener("mousemove", function (e) {
        if (!isDown || zoom <= 1) return;

        e.preventDefault();

        const x = e.pageX - area.offsetLeft;
        const y = e.pageY - area.offsetTop;

        const walkX = x - startX;
        const walkY = y - startY;

        area.scrollLeft = scrollLeft - walkX;
        area.scrollTop = scrollTop - walkY;
      });
    });

    // جلوگیری‌های سطحی و بازدارنده
    component.addEventListener("contextmenu", function (e) {
      e.preventDefault();
    });

    component.addEventListener("selectstart", function (e) {
      e.preventDefault();
    });

    component.addEventListener("dragstart", function (e) {
      e.preventDefault();
    });

    // تلاش برای واکنش به PrintScreen - تضمینی نیست
    document.addEventListener("keyup", function (e) {
      if (e.key === "PrintScreen") {
        showProtectionLayer();
      }
    });

    // وقتی کاربر از تب خارج می‌شود، سند را موقتاً مخفی کن
    document.addEventListener("visibilitychange", function () {
      if (document.hidden) {
        showProtectionLayer();
      } else {
        hideProtectionLayerDelayed();
      }
    });

    function showProtectionLayer() {
      protectionLayer.classList.add("is-active");
      setTimeout(function () {
        protectionLayer.classList.remove("is-active");
      }, 1800);
    }

    function hideProtectionLayerDelayed() {
      setTimeout(function () {
        protectionLayer.classList.remove("is-active");
      }, 700);
    }

    applyZoom();
  })();
</script>

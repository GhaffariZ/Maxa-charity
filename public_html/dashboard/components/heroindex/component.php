<?php
require_once __DIR__ . '/../../../core/site-locale.php';
if (!function_exists('iconoir')) {
    require_once __DIR__ . '/../../../core/icons.php';
}

// Render the unified Header navbar so Home page and all other pages are 100% identical
if (!defined('MAXA_HEADER_COMPONENT_RENDERED')) {
    if (!defined('MAXA_HTML_SHELL_OPENED') && !defined('MAXA_PAGE_VIEW_ACTIVE')) {
        define('MAXA_HTML_SHELL_OPENED', true);
    }
    require __DIR__ . '/../header/component.php';
}
?>
<style>
/* ===== HERO ===== */
    .cta-hero{
      position:relative;
      width:100%;
      min-height: 640px;
      isolation:isolate;
      overflow:hidden;
      background:#111;
    }

    .cta-hero::before{
  content:"";
  position:absolute;
  inset:0;
  background: radial-gradient(circle at 20% 20%, rgba(255,255,255,.08), transparent 35%),
              radial-gradient(circle at 80% 10%, rgba(255,255,255,.06), transparent 40%),
              linear-gradient(to bottom, rgba(0,0,0,.12), rgba(0,0,0,.12));
  mix-blend-mode: overlay;
  opacity:.55;
  pointer-events:none;
  z-index:2;
}

    
.cta-slider{ position:absolute; inset:0; z-index:1; }
    .cta-track{
      height:100%;
      display:flex;
      width:100%;
      transition: transform 650ms cubic-bezier(.2,.8,.2,1);
      will-change: transform;
    }
    .cta-slide{
      position:relative;
      min-width:100%;
      height:100%;
      background-size: cover;
      background-position: center;
      filter: saturate(1.02) contrast(1.03);
    }
    .cta-slide::after{
      content:"";
      position:absolute;
      inset:0;
      background:
        linear-gradient(to left, rgba(0,0,0,.40), rgba(0,0,0,.18) 55%, rgba(0,0,0,.08)),
        radial-gradient(circle at 15% 30%, rgba(0,0,0,.08), transparent 55%);
      z-index:0;
    }

    /* ===== hero content ===== */
    .cta-content{
      position:relative;
      z-index:6;
      padding-top: calc(var(--cta-nav-h) + 30px);
      padding-bottom: 72px;
      min-height: 640px;
      display:flex;
      align-items:center;
    }

    .cta-content .cta-container{
      display:flex;
      align-items:center;
      justify-content:flex-start;
      padding-inline:72px;
    }

    .cta-text{
      width:min(560px, 100%);
      color: var(--cta-text);
      text-align:start;
    }

    .cta-kicker{
      font-size:13px;
      color: rgba(255,255,255,.7);
      margin-bottom:10px;
    }
    .cta-title{
      font-size:52px;
      line-height:1.4;
      margin:0 0 14px 0;
      font-weight:800;
      text-shadow: 0 10px 18px rgba(0,0,0,.20);
    }
    .cta-desc{
      margin:0 0 18px 0;
      color: var(--cta-muted);
      font-size:15px;
      line-height:1.9;
      max-width: 58ch;
    }

    .cta-btn{
      display:inline-flex;
      align-items:center;
      gap:10px;
      border:none;
      cursor:pointer;
      background: var(--cta-orange);
      color:#111;
      font-weight:800;
      padding:11px 16px;
      border-radius:10px;
      box-shadow: 0 10px 20px rgba(250, 166, 26,.22);
      transition:.2s ease;
    }
    .cta-btn:hover{ transform: translateY(-1px); filter: brightness(1.03); }
    .cta-btn:active{ transform: translateY(0px); }
    .cta-btn:focus-visible{
      outline:none;
      box-shadow: 0 0 0 3px rgba(250, 166, 26,.20), 0 10px 20px rgba(250, 166, 26,.22);
    }

    /* arrows */
    .cta-arrow{
      position:absolute;
      top:50%;
      transform: translateY(-50%);
      z-index:7;
      width:44px; height:44px;
      border-radius:999px;
      border:1px solid rgba(255,255,255,.22);
      background: rgba(0,0,0,.26);
      color:#fff;
      display:grid;
      place-items:center;
      cursor:pointer;
      transition:.2s ease;
    }
.cta-glass {
  backdrop-filter: blur(6px);
  box-shadow: var(--cta-shadow-soft);
}
    .cta-arrow:hover{ background: rgba(0,0,0,.40); }
    .cta-arrow.prev{ right:10px; left: auto; }
    .cta-arrow.next{ left:16px; right: auto; }
    .cta-arrow svg{ width:18px; height:18px; opacity:.9; }

    /* dots */
    .cta-dots{
      position:absolute;
      bottom: 18px;
      left:50%;
      transform: translateX(-50%);
      z-index:7;
      display:flex;
      gap:8px;
      align-items:center;
    }
    .cta-dot{
      width:9px; height:9px;
      border-radius:999px;
      border:1px solid rgba(255,255,255,.60);
      background: rgba(255,255,255,.15);
      cursor:pointer;
      transition:.2s ease;
    }
    .cta-dot.active{
      width:20px;
      background: var(--cta-orange);
      border-color: transparent;
    }

    /* ===== ORANGE BAND ===== */
    .cta-band{
      position:relative;
      background: linear-gradient(180deg, var(--cta-orange) 0%, var(--cta-orange-2) 100%);
      padding: 34px 0 80px 0;
    }

    .cta-band .cta-container{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      align-items:center;
      gap:24px;
      color:#fff;
    }

    .cta-band-text{
      text-align:center;
      grid-column: 2;
    }

    .cta-band h3{
      margin:0;
      font-size:20px;
      font-weight:900;
      text-align:center;
    }

    .cta-band p{
      margin:6px auto 0 auto;
      opacity:.92;
      font-size:13px;
      line-height:1.7;
      max-width: 58ch;
      text-align:center;
    }

    .cta-band-cta {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: linear-gradient(135deg, #e53935, #c62828); /* Red gradient matching brand tone */
      color: #fff !important;
      border: none;
      padding: 14px 28px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 16px;
      text-decoration: none;
      white-space: nowrap;
      cursor: pointer;
      transition: all 0.25s ease;
      box-shadow: 0 6px 20px rgba(198, 40, 40, 0.4);
      width: 100%;
      grid-column: 1;
    }
    .cta-band-cta:hover {
      background: linear-gradient(135deg, #c62828, #b71c1c);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(198, 40, 40, 0.5);
    }
    .cta-band-cta:active {
      transform: translateY(1px);
    }

    /* ===== Cards (همون قبلی) ===== */
    .cta-cards-wrap{
      position:relative;
      margin-top: -52px;
      padding-bottom: 40px;
    }

    .cta-cards{
      display:grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap:24px;
    }

    .cta-card{
      background:#fff;
      border-radius: 10px;
      box-shadow: var(--cta-shadow);
      padding:26px 24px 22px;
      border:1px solid rgba(0,0,0,.06);
      text-align:center;
      cursor:pointer;
      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
      position:relative;
      outline:none;
    }

    .cta-card:hover{
      transform: translateY(-3px);
      box-shadow: 0 14px 30px rgba(0,0,0,.16);
      border-color: rgba(250, 166, 26,.30);
    }

    .cta-card.is-active{
      border-color: rgba(250, 166, 26,.55);
      box-shadow: 0 14px 30px rgba(250, 166, 26,.16), 0 0 0 3px rgba(250, 166, 26,.12);
      transform: translateY(-3px);
    }

    .cta-icon{
      width:44px; height:44px;
      margin:0 auto 10px;
      border-radius: 12px;
      display:grid;
      place-items:center;
      background: rgba(250, 166, 26,.15);
      border:1px solid rgba(250, 166, 26,.25);
      transition: .18s ease;
      color:#111;
    }

    .cta-card:hover .cta-icon,
    .cta-card.is-active .cta-icon{
      background: rgba(250, 166, 26,.22);
      border-color: rgba(250, 166, 26,.40);
      transform: scale(1.03);
    }

    .cta-card h4{
      margin:6px 0 6px 0;
      font-size:15px;
      font-weight:900;
      color:#1a1a1a;
    }

    .cta-card p{
      margin:0 auto 10px;
      color:#666;
      font-size:12.8px;
      line-height:1.75;
      max-width: 42ch;
    }

    .cta-card a{
      color: var(--cta-orange-2);
      font-weight:800;
      font-size:13px;
    }



    /* ===== Responsive ===== */
    /* ===== Responsive ===== */
    html, body {
      overflow-x: hidden;
      max-width: 100%;
    }

    * {
      box-sizing: border-box;
    }

    /* تنظیمات تبلت و دسکتاپ متوسط */
    @media (max-width: 1440px) {
      .cta-menu { gap: 4px; }
      .cta-menu > li > a { padding: 8px 7px; font-size: 13px; }
      .cta-search input { width: 150px; }
    }

    /* نقطه شکست موبایل و تبلت برای سایدبار همبرگری */
    @media (max-width: 1279px) {
      :root {
        --cta-nav-h: 56px;
      }

      .cta-center {
        display: none !important;
      }

      .menu-icon {
        display: flex;
        order: 3;
        position: relative;
        z-index: 1000;
      }

      .cta-left {
        min-width:0;
        margin-left:0;
        padding-left:0;
        max-width: 100%;
        flex: 1 1 auto;
        justify-content: flex-end;
        gap: 12px;
      }

      .cta-search {
        display: none !important;
      }

      .cta-donate {
        display: none;
      }

      .cta-auth {
        order: 2;
        font-size: 13px;
        padding: 0 12px;
        height: 36px;
      }

      .cta-header {
        padding: 6px 14px;
      }

      #mobileMenu {
        display: block;
      }

      #mobileBackdrop {
        display: block;
      }
    }

    @media (max-width: 600px) {
      .cta-header {
        padding: 5px 12px;
        gap: 8px;
      }

      .cta-brand img {
        height: 26px;
      }

      .cta-donate,
      .cta-auth {
        padding: 0 10px;
        font-size: 12px;
        height: 32px;
      }

      .menu-icon {
        width: 34px;
        height: 34px;
      }
    }

    @media (max-width: 420px) {
      .cta-header {
        padding: 5px 10px;
        gap: 6px;
      }

      .cta-brand img {
        height: 22px;
      }

      .menu-icon {
        width: 32px;
        height: 32px;
      }

      .menu-icon div {
        width: 15px;
      }
    }

    /* غیرفعال‌سازی سایدبار و بک‌دراپ در دسکتاپ بزرگ */
    @media (min-width: 1280px) {
      .mobile-menu-sidebar {
        display: none !important;
      }
      .mobile-backdrop {
        display: none !important;
      }
    }

    /* تبلت‌ها و صفحات متوسط */
    @media (max-width: 960px) {
      .cta-title {
        font-size: 40px;
      }

      .cta-band .cta-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 20px;
      }

      .cta-band-text {
        order: 1;
        text-align: center;
      }

      .cta-band h3, .cta-band p {
        text-align: center;
      }

      .cta-band-cta {
        order: 2;
        align-self: center;
      }

      .cta-cards {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    /* موبایل‌ها و صفحات بسیار کوچک */
    @media (max-width: 768px) {
      .cta-hero {
        min-height: 480px;
      }

      .cta-content {
        min-height: 480px;
        padding-top: 48px;
        padding-bottom: 88px;
      }

      .cta-content .cta-container {
        justify-content: center;
        padding-inline:20px;
      }

      .cta-text {
        width: min(560px, 100%);
        margin-inline: auto;
        text-align: center;
        padding-inline:0;
      }

      .cta-title {
        font-size: 32px;
        line-height: 1.35;
      }

      .cta-desc {
        font-size: 14px;
        line-height: 1.8;
      }

      .cta-arrow {
        width: 36px;
        height: 36px;
        top:auto;
        bottom:18px;
        transform:none;
      }

      .cta-arrow.prev {
        right: 10px;
      }

      .cta-arrow.next {
        left: 10px;
      }

      .cta-dots {
        bottom:30px;
      }
    }

    /* گوشی‌های بسیار باریک */
    @media (max-width: 420px) {
      .cta-title {
        font-size: 26px;
      }

      .cta-desc {
        font-size: 14px;
        line-height: 1.7;
      }

      .cta-donate,
      .cta-auth {
        padding: 0 10px;
        font-size: 12px;
        height: 30px;
        gap: 4px;
      }

      .cta-text {
        padding-inline:0;
      }
    }

    @media(max-width:540px){
      .cta-cards{grid-template-columns:minmax(0,1fr);gap:16px}
      .cta-card{padding:20px}
    }

    @media (max-width: 600px) {
      .cta-header {
        padding: 0 12px;
      }
      .cta-right {
        flex-shrink: 0;
        padding-left: 20px;
        padding-right: calc(var(--cta-edge-gap) + 6px);
      }
      .cta-glass-underlay {
        clip-path: polygon(20px 0, 100% 0, 100% 100%, 0 100%);
      }
      .cta-glass-underlay::before {
        width: 30px;
        clip-path: polygon(20px 0, 30px 0, 10px 100%, 0 100%);
      }
      .cta-glass-underlay::after {
        width: 22px;
        clip-path: polygon(18.5px 0, 20px 0, 1.5px 100%, 0 100%);
      }
      .cta-brand img {
        height: 28px;
      }
    }

    @media (max-width: 420px) {
      .cta-header {
        padding: 0 8px;
      }
      .cta-right {
        padding-left: 14px;
        padding-right: calc(var(--cta-edge-gap) + 4px);
      }
      .cta-glass-underlay {
        clip-path: polygon(15px 0, 100% 0, 100% 100%, 0 100%);
      }
      .cta-glass-underlay::before {
        width: 22px;
        clip-path: polygon(15px 0, 22px 0, 7px 100%, 0 100%);
      }
      .cta-glass-underlay::after {
        width: 17px;
        clip-path: polygon(13.5px 0, 15px 0, 1.5px 100%, 0 100%);
      }
      .cta-brand img {
        height: 24px;
      }
    }

    .cta-card a {
      color: var(--cta-orange-2);
      font-weight: 800;
      font-size: 13px;
    }

    /* ===== Account widget (logged-in state) ===== */
    .cta-auth-slot{position:relative;display:inline-flex;align-items:center}
    .cta-account{position:relative}
    .cta-account-btn{
      height:38px;border-radius:10px;border:1px solid rgba(0, 143, 138,.4);
      background:linear-gradient(135deg, #008f8a, #00736f);color:#fff;
      padding:0 12px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;
      white-space:nowrap;font-weight:700;box-shadow:0 0 12px rgba(0, 143, 138,.3);
      transition:all .3s cubic-bezier(.16,1,.3,1);
    }
    .cta-account-btn:hover{transform:translateY(-2px);box-shadow:0 0 20px rgba(0, 143, 138,.6);background:linear-gradient(135deg, #05a8a2, #008f8a)}
    .cta-account-btn:active{transform:translateY(1px)}
    .cta-account-avatar{width:26px;height:26px;border-radius:50%;background:rgba(255,255,255,.18);
      display:grid;place-items:center;font-weight:800;font-size:13px;overflow:hidden;flex-shrink:0;color:#fff}
    .cta-account-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%}
    .cta-account-avatar svg{width:16px;height:16px}
    .cta-account-name{max-width:120px;overflow:hidden;text-overflow:ellipsis;font-size:13px}
    .cta-account-caret{width:14px;height:14px;transition:transform .25s ease;opacity:.85;flex-shrink:0}
    .cta-account.open .cta-account-caret{transform:rotate(180deg)}
    .cta-account-menu{
      position:absolute;top:calc(100% + 10px);left:0;min-width:236px;
      background:rgba(15,23,42,.94);backdrop-filter:blur(16px) saturate(160%);
      -webkit-backdrop-filter:blur(16px) saturate(160%);
      border:1px solid rgba(255,255,255,.12);border-radius:14px;
      box-shadow:0 20px 40px rgba(0,0,0,.45);padding:8px;
      opacity:0;visibility:hidden;transform:translateY(-8px) scale(.98);transform-origin:top left;
      transition:opacity .2s,transform .22s cubic-bezier(.16,1,.3,1),visibility .2s;
      z-index:100001;direction:var(--site-direction, rtl);text-align:start;
      max-width: calc(100vw - 20px);
    }

    @media (max-width: 480px) {
      .cta-account-menu {
        min-width: auto;
        right: 10px;
        left: auto;
        transform-origin: top right;
      }
    }
    .cta-account.open .cta-account-menu{opacity:1;visibility:visible;transform:translateY(0) scale(1)}
    .cta-account-head{display:flex;align-items:center;gap:10px;padding:8px 8px 12px;
      border-bottom:1px solid rgba(255,255,255,.1);margin-bottom:6px}
    .cta-account-head .cta-account-avatar{width:42px;height:42px;font-size:16px}
    .cta-account-head strong{display:block;color:#fff;font-size:13.5px;font-weight:800;
      max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .cta-account-head span{display:block;color:rgba(255,255,255,.6);font-size:11.5px;
      max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;direction:ltr;text-align:start}
    .cta-account-menu a,.cta-account-menu button{
      display:flex;align-items:center;gap:10px;width:100%;text-align:start;
      padding:10px 12px;border-radius:10px;color:rgba(255,255,255,.9);font-size:13px;font-weight:600;
      background:none;border:none;cursor:pointer;transition:background .18s,color .18s;font-family:inherit;text-decoration:none;
    }
    .cta-account-menu a:hover,.cta-account-menu button:hover{background:rgba(255,255,255,.1);color:#fff}
    .cta-account-menu svg{width:17px;height:17px;opacity:.85;flex-shrink:0}
    .cta-account-logout{color:#ffb4b4 !important;margin-top:6px;border-top:1px solid rgba(255,255,255,.1) !important;border-radius:0 0 8px 8px !important}
    .cta-account-logout:hover{background:rgba(229,57,53,.28) !important;color:#fff !important}
    [hidden]{display:none !important}

  
</style>
<div class="cta">
<section class="cta-hero" aria-label="CTA Hero Slider" hidden>

      <div class="cta-slider" id="ctaSlider">
         <div class="cta-track" id="ctaTrack">
           <div class="cta-slide active" style="background-image: url('/uploads/hero/hero_1790160692_9543.webp');"></div>
        </div>
      </div>

      <div class="cta-content">
        <div class="cta-container">
          <div class="cta-text">
            <h1 class="cta-title" id="heroTitle"><?= site_e('مکسا؛ مؤسسه نیکوکاری کنترل سرطان ایرانیان') ?></h1>
            <p class="cta-desc" id="heroDesc"><?= site_e('مدیریت بهینه سرطان و ارائه رایگان خدمات تخصصی مراقبت‌های حمایتی و تسکینی') ?></p>
            <a class="cta-btn" id="heroBtn" href="/single-fundraising-option" aria-label="<?= site_e('روش‌های حمایت از مکسا') ?>">
              <span id="heroBtnText"><?= site_e('روش‌های حمایت') ?></span>
              <span aria-hidden="true">←</span>
            </a>
          </div>
        </div>
      </div>

      <button class="cta-arrow prev" id="ctaPrev" aria-label="<?= site_e('اسلاید قبلی') ?>" type="button">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M9.5 5L16 12l-6.5 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <button class="cta-arrow next" id="ctaNext" aria-label="<?= site_e('اسلاید بعدی') ?>" type="button">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M14.5 5L8 12l6.5 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <div class="cta-dots" id="ctaDots" aria-label="<?= site_e('نشانگر اسلایدها') ?>"></div>

    </section>

    <section class="cta-band" aria-label="Band">
      <div class="cta-container">
        <a class="cta-band-cta" href="/onlinedonation"><?= iconoir('heart', 'cta-heart-ic', 18) ?> <span><?= site_e('می‌خواهم کمک کنم') ?></span></a>
        <div class="cta-band-text">
          <h3><?= site_e('با هم برای جهانی بهتر') ?></h3>
          <p>
            <?= site_e('مشارکت شما می‌تونه یک تغییر واقعی بسازه؛ کافی‌ست مسیر درست رو انتخاب کنیم و کنار هم ادامه بدیم.') ?>
          </p>
        </div>
      </div>
    </section>

    <section class="cta-cards-wrap" aria-label="Cards">
      <div class="cta-container">
<div class="cta-cards" id="ctaCards">

  <div class="cta-card" role="link" tabindex="0" data-link="/supportprojects" aria-label="<?= site_e('مشاهده بسته‌های نیکوکاری') ?>">
    <div class="cta-icon" aria-hidden="true">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </div>

    <h4><?= site_e('بسته های نیکوکاری') ?></h4>
    <p><?= site_e('یک قدم کوچک شما، برای یک خانواده می‌تونه بزرگ‌ترین امید باشه.') ?></p>
  </div>

  <div class="cta-card" role="link" tabindex="0" data-link="/single-fundraising-option" aria-label="<?= site_e('مشاهده روش‌های حمایت') ?>">
    <div class="cta-icon" aria-hidden="true">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M12 21C12 21 4 14.5 4 9.5C4 7 6 5 8.5 5C10.2 5 11.4 5.9 12 7C12.6 5.9 13.8 5 15.5 5C18 5 20 7 20 9.5C20 14.5 12 21 12 21Z"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"/>
      </svg>
    </div>

    <h4><?= site_e('روش های حمایت') ?></h4>
    <p><?= site_e('از راه های مختلف کمک های نقدی و غیر نقدی خود را به زندگی بیماران هدیه کنید.') ?></p>
  </div>

  <div class="cta-card" role="link" tabindex="0" data-link="/patientintake" aria-label="<?= site_e('تشکیل پرونده اولیه مجازی') ?>">
    <div class="cta-icon" aria-hidden="true">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z" stroke="currentColor" stroke-width="2"/>
        <path d="M4 21c1.2-4 4.4-6 8-6s6.8 2 8 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </div>

    <h4><?= site_e('تشکیل پرونده اولیه مجازی') ?></h4>
    <p><?= site_e('برای دریافت مشاوره تخصصی و بررسی پرونده درمانی، فرم مجازی را تکمیل کنید.') ?></p>
  </div>

  <div class="cta-card" role="link" tabindex="0" data-link="/stand-order.php" aria-label="<?= site_e('استندها و کارت‌های تبریک و تسلیت') ?>">
    <div class="cta-icon" aria-hidden="true">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
         <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>

    <h4><?= site_e('استندها و کارت‌های تبریک و تسلیت') ?></h4>
    <p><?= site_e('در شادی‌ها و غم‌ها با سفارش استند، حامی بیماران مبتلا به سرطان باشید.') ?></p>
  </div>

</div>


      </div>
    </section>

  </section>
</div>
<script>
(async function loadHeroSlides() {
    const track = document.getElementById("ctaTrack");
    const heroTitle = document.getElementById("heroTitle");
    const heroDesc = document.getElementById("heroDesc");
    const heroBtn = document.getElementById("heroBtn");

    try {
        // ۱. دریافت داده‌ها از API
        const response = await fetch("/dashboard/hero-list.php");
        const json = await response.json();

        if (json.status === "success" && json.data.length > 0) {
            const slides = json.data;
            document.querySelector('.cta-hero').hidden=false;
            // The feed contains only records explicitly published for this page's language.
            [heroTitle, heroDesc].forEach(node => {
                if (node) { node.lang = document.documentElement.lang; node.dir = document.documentElement.dir; node.dataset.siteDynamic = ''; }
            });

            // ۲. پر کردن هیرو (اولین آیتم به عنوان پیش‌فرض)
            if (heroTitle) heroTitle.innerText = slides[0].title;
            if (heroDesc) heroDesc.innerHTML = slides[0].description;
            if (heroBtn) {
                if (slides[0].button_link) {
                    heroBtn.href = slides[0].button_link;
                    heroBtn.style.display = "inline-flex";
                } else {
                    heroBtn.style.display = "none";
                }
            }

            // ۳. ساخت اسلایدهای پس‌زمینه در cta-track
            track.innerHTML = ""; // پاکسازی قبلی‌ها
            slides.forEach((s) => {
                const slideDiv = document.createElement("div");
                slideDiv.className = "cta-slide";
                slideDiv.style.backgroundImage = `url('${s.image}')`;
                track.appendChild(slideDiv);
            });

            // ذخیره در ویندوز برای توابع اسلایدر
            window.__HERO_SLIDES__ = slides;

            // ۴. راه‌اندازی اسلایدر بعد از لود داده‌ها
            initSlider();
        }
    } catch (e) {
        console.error("Failed to load hero slides:", e);
    }
})();

function initSlider() {
    const track = document.getElementById("ctaTrack");
    const slides = track ? Array.from(track.children) : [];
    const prevBtn = document.getElementById("ctaPrev");
    const nextBtn = document.getElementById("ctaNext");
    const dotsWrap = document.getElementById("ctaDots");

    if(!track || slides.length === 0){ return; }

    const AUTOPLAY_MS = 4000;
    let index = 0;
    let timer = null;

    // پاک کردن دات‌های قدیمی در صورت وجود
    dotsWrap.innerHTML = "";

    slides.forEach(function(_, i){
      const b = document.createElement("button");
      b.className = "cta-dot" + (i === 0 ? " active" : "");
      b.type = "button";
      b.setAttribute("aria-label", "رفتن به اسلاید " + (i + 1));
      b.addEventListener("click", function(){ goTo(i, true); });
      dotsWrap.appendChild(b);
    });

    const dots = Array.from(dotsWrap.children);

    function setActiveDot(i){
      dots.forEach(function(d, di){
        d.classList.toggle("active", di === i);
      });
    }

    function applyTransform(){
      // در ساختار راست به چپ (RTL)، اسلاید بعدی در سمت چپ اسلاید فعلی قرار دارد.
      // بنابراین برای نمایش اسلاید با ایندکس بزرگتر، باید تراک را به سمت راست (مقدار مثبت) حرکت دهیم.
      track.style.transform = "translateX(" + (index * 100) + "%)";
    }

    function goTo(i, userAction){
      index = (i + slides.length) % slides.length;
      applyTransform();
      setActiveDot(index);
      
      // آپدیت متن‌ها با تغییر اسلاید
      const data = window.__HERO_SLIDES__;
      if (data && data[index]) {
        const s = data[index];
        const heroTitle  = document.getElementById("heroTitle");
        const heroDesc   = document.getElementById("heroDesc");
        const heroBtn    = document.getElementById("heroBtn");

        if(heroTitle)  heroTitle.innerHTML  = s.title || "";
        if(heroDesc)   heroDesc.innerHTML   = s.description || "";
        if(heroBtn) {
            if (s.button_link) {
                heroBtn.href = s.button_link;
                heroBtn.style.display = "inline-flex";
            } else {
                heroBtn.style.display = "none";
            }
        }
      }

      if(userAction){ restartAutoplay(); }
    }

    function next(userAction){ goTo(index + 1, userAction); }
    function prev(userAction){ goTo(index - 1, userAction); }

    if(prevBtn){ 
      prevBtn.onclick = function(){ prev(true); };
    }
    if(nextBtn){ 
      nextBtn.onclick = function(){ next(true); };
    }

    function startAutoplay(){
      stopAutoplay();
      timer = setInterval(function(){ next(false); }, AUTOPLAY_MS);
    }

    function stopAutoplay(){
      if(timer){ clearInterval(timer); }
      timer = null;
    }

    function restartAutoplay(){ startAutoplay(); }

    const hero = document.querySelector(".cta-hero");
    if(hero){
      hero.onmouseenter = stopAutoplay;
      hero.onmouseleave = startAutoplay;

      let startX = 0;
      let dragging = false;

      hero.ontouchstart = function(e){
        if(!e.touches || !e.touches.length){ return; }
        dragging = true;
        startX = e.touches[0].clientX;
        stopAutoplay();
      };

      hero.ontouchend = function(e){
        if(!dragging){ return; }
        dragging = false;
        const endX = (e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0].clientX : startX;
        const dx = endX - startX;
        if(Math.abs(dx) > 40){
          // در RTL سوایپ به چپ یعنی رفتن به اسلاید بعدی
          if(dx < 0){ next(true); }
          else{ prev(true); }
        }
        startAutoplay();
      };
    }

    window.onkeydown = function(e){
      if(e.key === "ArrowLeft"){ next(true); }
      if(e.key === "ArrowRight"){ prev(true); }
    };

    applyTransform();
    startAutoplay();
}



  </script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const cards = document.querySelectorAll("#ctaCards .cta-card");

  cards.forEach(function(card){
    card.addEventListener("click", function(){
      const link = card.getAttribute("data-link");
      if(link){
        window.location.href = link;
      }
    });

    card.addEventListener("keydown", function(e){
      if(e.key === "Enter" || e.key === " "){
        e.preventDefault();

        const link = card.getAttribute("data-link");
        if(link){
          window.location.href = link;
        }
      }
    });
  });
});
</script>

  <!-- ===== وضعیت ورود کاربر: تبدیل دکمه «ورود/ثبت‌نام» به منوی حساب کاربری ===== -->
  
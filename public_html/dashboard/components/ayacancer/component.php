<?php require_once __DIR__ . '/../../../core/site-locale.php'; ?>
<!DOCTYPE html>
<html lang="fa" dir="<?= site_direction() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= site_e('نوجوانان و جوانان مبتلا به سرطان') ?></title>

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
  </style>
  <style>
    :root {
      --primary-teal: #008f8a;
      --sidebar-teal: #008f8a;
      --header-bg: #eef3f6;
      --text-dark: #1e293b;
      --text-muted: #475569;
      --border-light: #e2e8f0;
      --bg-soft: #f8fafc;
      --main-width: 1400px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Vazirmatn', sans-serif;
      background-color: #ffffff;
      margin: 0;
      padding: 0;
      color: var(--text-dark);
      line-height: 1.8;
    }

    a {
      color: inherit;
    }

    /* ---------------- page wrapper ---------------- */

    .page-container {
      max-width: var(--main-width);
      margin: 40px auto;
      padding: 0 20px;
    }

    /* ---------------- header ---------------- */

    .header-capsule {
      background: linear-gradient(180deg, #f8f9fa 0%, #eef3f6 100%);
      border-radius: 50px;
      padding: 50px 30px;
      margin-bottom: 40px;
      text-align: center;
    }

    .header-capsule h1 {
      font-size: 2.5rem;
      margin: 0 0 15px 0;
      font-weight: 800;
      color: var(--text-dark);
    }

    .breadcrumb-pill {
      background-color: var(--primary-teal);
      color: white;
      padding: 6px 20px;
      border-radius: 50px;
      font-size: 0.85rem;
      display: inline-block;
      text-decoration: none;
      transition: 0.2s;
    }

    .breadcrumb-pill:hover {
      opacity: 0.85;
      text-decoration: none;
    }

    /* ---------------- main grid ---------------- */

    .article-layout {
      display: grid;
      grid-template-columns: 360px minmax(0, 1fr);
      grid-template-areas: "sidebar content";
      gap: 32px;
      align-items: start;
      direction: var(--site-direction, rtl);
    }

    .content-column {
      grid-area: content;
      min-width: 0;
    }

    .article-sidebar {
      grid-area: sidebar;
      align-self: stretch;
      min-width: 0;
    }

    /* ---------------- hero image ---------------- */

    .hero-image-container {
      width: 100%;
      height: 450px;
      margin: 0 0 40px 0;
      overflow: hidden;
      border-radius: 40px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      background: #f1f5f9;
    }

    .hero-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      display: block;
    }

    /* ---------------- sidebar wrapper ---------------- */

    .sidebar-stack {
      position: -webkit-sticky;
      position: sticky;
      top: 96px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* ---------------- contact card ---------------- */

    .contact-card {
      position: relative;
      overflow: hidden;
      min-height: 335px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background:
        radial-gradient(circle at 20% 10%, rgba(255,255,255,0.28), transparent 30%),
        radial-gradient(circle at 90% 95%, rgba(255,255,255,0.16), transparent 34%),
        linear-gradient(155deg, #05a8a2 0%, #008f8a 54%, #00605d 100%);
      color: #fff;
      border-radius: 34px;
      padding: 34px 26px 28px;
      text-align: center;
      box-shadow:
        0 22px 48px rgba(15, 159, 170, 0.25),
        inset 0 1px 0 rgba(255,255,255,0.22);
    }

    .contact-card::before {
      content: "";
      position: absolute;
      width: 155px;
      height: 155px;
      left: -58px;
      bottom: -65px;
      border-radius: 50%;
      background: rgba(255,255,255,0.12);
    }

    .contact-card::after {
      content: "";
      position: absolute;
      width: 105px;
      height: 105px;
      right: -42px;
      top: -42px;
      border-radius: 50%;
      background: rgba(255,255,255,0.12);
    }

    .contact-card > * {
      position: relative;
      z-index: 1;
    }

    .chat-icon {
      width: 72px;
      height: 72px;
      margin: 0 auto 20px;
      border-radius: 24px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      background: rgba(255,255,255,0.17);
      border: 1px solid rgba(255,255,255,0.25);
      box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.25),
        0 16px 28px rgba(0,0,0,0.10);
      backdrop-filter: blur(8px);
    }

    .chat-icon svg {
      width: 34px;
      height: 34px;
    }

    .contact-card-title {
      margin: 0 0 16px 0;
      font-size: 1.85rem;
      line-height: 1.55;
      font-weight: 950;
      color: #ffffff;
      letter-spacing: -0.04em;
    }

    @media (max-width: 768px) {
      .contact-card-title {
        font-size: 1.45rem;
      }
    }

    .contact-card-text {
      margin: 0 0 26px 0;
      color: rgba(255,255,255,0.92);
      font-size: 1rem;
      line-height: 2.05;
      text-align: center;
    }

    .contact-card-btn {
      font-family: inherit;
      position: relative;
      isolation: isolate;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      width: 100%;
      min-height: 54px;
      padding: 14px 22px;
      border: 0;
      border-radius: 999px;
      background: linear-gradient(180deg, #ffffff 0%, #f1ffff 100%);
      color: #087985;
      text-decoration: none;
      font-weight: 950;
      font-size: 1.02rem;
      cursor: pointer;
      box-shadow:
        0 14px 24px rgba(3, 105, 118, 0.22),
        inset 0 1px 0 rgba(255,255,255,0.9);
      transition:
        transform 0.22s ease,
        box-shadow 0.22s ease,
        background 0.22s ease,
        color 0.22s ease;
    }

    .contact-card-btn::before {
      content: "";
      width: 22px;
      height: 22px;
      flex: 0 0 22px;
      background-color: currentColor;
      mask-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M21 15.46v3.04A2.5 2.5 0 0 1 18.27 21 16.5 16.5 0 0 1 3 5.73 2.5 2.5 0 0 1 5.5 3h3.04A2 2 0 0 1 10.48 4.5l.68 2.72a2 2 0 0 1-.52 1.9l-1.1 1.1a12.2 12.2 0 0 0 4.24 4.24l1.1-1.1a2 2 0 0 1 1.9-.52l2.72.68A2 2 0 0 1 21 15.46Z'/%3E%3C/svg%3E");
      mask-repeat: no-repeat;
      mask-position: center;
      mask-size: contain;
    }

    .contact-card-btn:hover {
      transform: translateY(-3px);
      background: #ffffff;
      color: #05636d;
      box-shadow:
        0 18px 30px rgba(3, 105, 118, 0.28),
        inset 0 1px 0 rgba(255,255,255,0.95);
    }

    .contact-card-btn:active {
      transform: translateY(-1px) scale(0.99);
    }

    /* ---------------- related card ---------------- */

    .related-card {
      background: #ffffff;
      border: 1px solid var(--border-light);
      border-radius: 28px;
      padding: 24px;
      box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .related-card-title {
      font-size: 1.25rem;
      font-weight: 800;
      margin: 0 0 16px 0;
      color: var(--text-dark);
      padding-bottom: 12px;
      border-bottom: 1px solid var(--border-light);
      position: relative;
    }

    .related-card-title::after {
      content: "";
      position: absolute;
      right: 0;
      bottom: -1px;
      width: 70px;
      height: 3px;
      border-radius: 99px;
      background: var(--primary-teal);
    }

    .related-list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .related-list li {
      margin: 0;
    }

    .related-list a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 14px 16px;
      background: var(--bg-soft);
      border-radius: 16px;
      color: var(--text-dark);
      text-decoration: none;
      font-size: 0.98rem;
      font-weight: 600;
      transition: 0.2s ease;
    }

    .related-list a::after {
      content: "←";
      margin-right: auto;
      color: #94a3b8;
      font-weight: 900;
      transition: 0.22s ease;
    }

    .related-list a:hover {
      background: rgba(0, 168, 168, 0.09);
      color: var(--primary-teal);
      transform: translateX(-2px);
      text-decoration: none;
    }

    .related-list a:hover::after {
      color: var(--primary-teal);
      transform: translateX(-3px);
    }

    /* ---------------- call popup ---------------- */

    .call-popup-overlay {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 99999;
      align-items: center;
      justify-content: center;
      padding: 18px;
      background:
        radial-gradient(circle at center, rgba(15, 159, 170, 0.18), transparent 38%),
        rgba(15, 23, 42, 0.58);
      backdrop-filter: blur(8px);
    }

    .popup-box {
      position: relative;
      width: min(420px, 100%);
      overflow: hidden;
      background: #ffffff;
      border-radius: 32px;
      padding: 34px 28px 26px;
      text-align: center;
      box-shadow:
        0 30px 80px rgba(15, 23, 42, 0.28),
        inset 0 1px 0 rgba(255,255,255,0.9);
      animation: popupIn 0.24s ease both;
    }

    .popup-box::before {
      content: "";
      position: absolute;
      inset: 0 0 auto 0;
      height: 8px;
      background: linear-gradient(90deg, #008f8a, #10b981, #008f8a);
    }

    .popup-icon {
      width: 74px;
      height: 74px;
      margin: 0 auto 18px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 26px;
      color: #008f8a;
      background:
        linear-gradient(180deg, rgba(0,168,168,0.13), rgba(0,168,168,0.06));
      box-shadow: 0 14px 28px rgba(0, 168, 168, 0.12);
    }

    .popup-box h3 {
      margin: 0 0 10px 0;
      color: #0f172a;
      font-size: 1.35rem;
      font-weight: 950;
      line-height: 1.6;
    }

    .popup-desc {
      margin: 0 auto 20px;
      max-width: 320px;
      color: #64748b;
      font-size: 0.95rem;
      line-height: 2;
    }

    .popup-phone-link {
      direction: ltr;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 86px;
      margin: 0 0 18px 0;
      padding: 16px;
      border-radius: 24px;
      text-decoration: none;
      background:
        linear-gradient(180deg, #f8ffff 0%, #f1fbfb 100%);
      border: 1px solid rgba(0,168,168,0.16);
      color: #008f8a;
      transition: 0.22s ease;
    }

    .popup-phone-link:hover {
      transform: translateY(-2px);
      border-color: rgba(0,168,168,0.32);
      box-shadow: 0 14px 30px rgba(0,168,168,0.12);
      text-decoration: none;
    }

    .popup-phone-label {
      direction: var(--site-direction, rtl);
      margin-bottom: 4px;
      color: #64748b;
      font-size: 0.8rem;
      font-weight: 700;
    }

    .popup-phone-link strong {
      font-size: 1.65rem;
      font-weight: 950;
      letter-spacing: 0.02em;
      color: #008f8a;
    }

    .popup-actions {
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: 12px;
      margin-top: 8px;
    }

    .popup-actions button {
      font-family: inherit;
      position: relative;
      isolation: isolate;
      min-height: 52px;
      padding: 13px 18px;
      border: 0;
      border-radius: 999px;
      font-size: 0.96rem;
      font-weight: 900;
      line-height: 1;
      cursor: pointer;
      white-space: nowrap;
      overflow: hidden;
      transition:
        transform 0.22s ease,
        box-shadow 0.22s ease,
        background 0.22s ease,
        color 0.22s ease,
        border-color 0.22s ease;
    }

    .popup-actions button::before {
      content: "";
      width: 19px;
      height: 19px;
      flex: 0 0 19px;
      background-color: currentColor;
      opacity: 0.95;
      transition: transform 0.22s ease, opacity 0.22s ease;
    }

    .popup-copy-btn,
    .popup-secondary-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 9px;
    }

    .popup-copy-btn {
      color: #ffffff;
      background:
        radial-gradient(circle at 25% 10%, rgba(255,255,255,0.28), transparent 30%),
        linear-gradient(180deg, #12b7bd 0%, #078894 100%);
      box-shadow:
        0 14px 28px rgba(15, 159, 170, 0.28),
        inset 0 1px 0 rgba(255,255,255,0.28);
    }

    .popup-copy-btn::before {
      mask-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M8 7.5A2.5 2.5 0 0 1 10.5 5H18a2.5 2.5 0 0 1 2.5 2.5V15A2.5 2.5 0 0 1 18 17.5h-7.5A2.5 2.5 0 0 1 8 15V7.5Zm-4.5 3A2.5 2.5 0 0 1 6 8h.5v2H6a.5.5 0 0 0-.5.5V18A2.5 2.5 0 0 0 8 20.5h7.5a.5.5 0 0 0 .5-.5v-.5h2v.5a2.5 2.5 0 0 1-2.5 2.5H8A4.5 4.5 0 0 1 3.5 18v-7.5Z'/%3E%3C/svg%3E");
      mask-repeat: no-repeat;
      mask-position: center;
      mask-size: contain;
    }

    .popup-copy-btn:hover {
      transform: translateY(-3px);
      background:
        radial-gradient(circle at 25% 10%, rgba(255,255,255,0.32), transparent 32%),
        linear-gradient(180deg, #16c7cd 0%, #067985 100%);
      box-shadow:
        0 18px 34px rgba(15, 159, 170, 0.36),
        inset 0 1px 0 rgba(255,255,255,0.32);
    }

    .popup-copy-btn:hover::before {
      transform: translateY(-1px) scale(1.05);
    }

    .popup-secondary-btn {
      color: #475569;
      background:
        linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
      border: 1px solid #e2e8f0 !important;
      box-shadow:
        0 10px 22px rgba(15, 23, 42, 0.06),
        inset 0 1px 0 rgba(255,255,255,0.9);
    }

    .popup-secondary-btn::before {
      mask-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6.4 5.1 12 10.7l5.6-5.6 1.3 1.3L13.3 12l5.6 5.6-1.3 1.3L12 13.3l-5.6 5.6-1.3-1.3L10.7 12 5.1 6.4l1.3-1.3Z'/%3E%3C/svg%3E");
      mask-repeat: no-repeat;
      mask-position: center;
      mask-size: contain;
    }

    .popup-secondary-btn:hover {
      transform: translateY(-3px);
      color: #0f172a;
      background:
        linear-gradient(180deg, #ffffff 0%, #e8eef5 100%);
      border-color: #cbd5e1 !important;
      box-shadow:
        0 14px 28px rgba(15, 23, 42, 0.10),
        inset 0 1px 0 rgba(255,255,255,0.95);
    }

    .popup-secondary-btn:hover::before {
      transform: rotate(90deg) scale(1.05);
    }

    .popup-actions button:active {
      transform: translateY(-1px) scale(0.985);
    }

    .popup-actions button:focus-visible {
      outline: 3px solid rgba(15, 159, 170, 0.24);
      outline-offset: 3px;
    }

    @keyframes popupIn {
      from {
        opacity: 0;
        transform: translateY(14px) scale(0.96);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    /* ---------------- article ---------------- */

    .article-content {
      padding: 0 10px;
    }

    .article-content h2 {
      font-size: 1.8rem;
      margin-top: 40px;
      margin-bottom: 20px;
      color: var(--primary-teal);
      border-right: 4px solid var(--primary-teal);
      padding-right: 15px;
    }

    .article-content p {
      font-size: 1.15rem;
      color: var(--text-muted);
      margin-bottom: 20px;
      text-align: justify;
    }

    .article-content ul {
      font-size: 1.1rem;
      color: var(--text-muted);
      padding-right: 24px;
      margin-bottom: 24px;
    }

    .article-content li {
      margin-bottom: 10px;
    }

    /* ---------------- responsive ---------------- */

    @media (max-width: 1100px) {
      .article-layout {
        grid-template-columns: 1fr;
        grid-template-areas:
          "content"
          "sidebar";
      }

      .article-sidebar {
        margin-top: 20px;
      }

      .sidebar-stack {
        position: static;
        max-height: none;
        overflow: visible;
      }
    }

    @media (max-width: 768px) {
      .page-container {
        margin: 20px auto;
        padding: 0 14px;
      }

      .header-capsule {
        border-radius: 30px;
        padding: 30px 15px;
        margin-bottom: 24px;
      }

      .header-capsule h1 {
        font-size: 1.65rem;
        line-height: 1.6;
      }

      .article-layout {
        gap: 24px;
      }

      .hero-image-container {
        height: 250px;
        border-radius: 22px;
        margin-bottom: 28px;
      }

      .article-content {
        padding: 0;
      }

      .article-content h2 {
        font-size: 1.35rem;
      }

      .article-content p {
        font-size: 1rem;
      }
    }

    @media (max-width: 480px) {
      .popup-actions {
        grid-template-columns: 1fr;
      }

      .popup-actions button {
        width: 100%;
        min-height: 50px;
      }
    }

    /* ---------------- ultra-small phones & large screens ---------------- */
    @media (max-width: 380px) {
      .header-capsule h1 { font-size: 1.4rem; }
      .article-content h2 { font-size: 1.2rem; }
      .article-content p, .article-content ul { font-size: 0.95rem; }
      .hero-image-container { height: 200px; border-radius: 18px; }
      .contact-card, .related-card { border-radius: 18px; padding: 18px 14px; }
      .popup-box { border-radius: 24px; padding: 26px 18px 20px; }
    }
    @media (min-width: 1600px) {
      .article-content p { font-size: 1.22rem; }
    }

    /* ---------------- entrance animations (CSS-only, no external JS) ---------------- */
    @keyframes maxaFadeInUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .wow,
    .fadeInUp {
      opacity: 0;
      animation: maxaFadeInUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }
    /* stagger via data-wow-delay values */
    [data-wow-delay="0.2s"] { animation-delay: 0.2s; }
    [data-wow-delay="0.4s"] { animation-delay: 0.4s; }
    [data-wow-delay="0.6s"] { animation-delay: 0.6s; }
    [data-wow-delay="0.8s"] { animation-delay: 0.8s; }
    [data-wow-delay="1.0s"] { animation-delay: 1.0s; }
    [data-wow-delay="1.2s"] { animation-delay: 1.2s; }
    [data-wow-delay="1.4s"] { animation-delay: 1.4s; }
    [data-wow-delay="1.6s"] { animation-delay: 1.6s; }
    [data-wow-delay="1.8s"] { animation-delay: 1.8s; }
    @media (prefers-reduced-motion: reduce) {
      .wow, .fadeInUp { opacity: 1 !important; animation: none !important; }
    }

  </style>

</head>

<body>

  <main class="page-container">

    <header class="header-capsule">
      <h1><?= site_e('نوجوانان و جوانان مبتلا به سرطان') ?></h1>

      <a href="/home" class="breadcrumb-pill">
        <?= site_e('صفحه اصلی / علل، عوامل و فرآیند ایجاد سرطان / نوجوانان و جوانان مبتلا به سرطان') ?>
      </a>
    </header>

    <div class="article-layout">

      <!-- سایدبار از کنار عکس شاخص شروع می‌شود -->
      <aside class="article-sidebar">
        <div class="sidebar-stack">
          <!-- باکس ارتباطات -->
          <div class="contact-card">
            <div class="chat-icon">
              <svg viewBox="0 0 24 24" width="34" height="34" fill="none" aria-hidden="true">
                <path d="M7.5 18.5H7a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v6.5a4 4 0 0 1-4 4h-4.2L8.7 21.2a.75.75 0 0 1-1.2-.6v-2.1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
              </svg>
            </div>
            <h3 class="contact-card-title"><?= site_e('برای مشاوره نیاز به کمک دارید؟') ?></h3>
            <p class="contact-card-text">
              <?= site_e('با مرکز تخصصی ارتباطات و سلامت از راه دور به صورت 24 ساعت روز و 7 روز هفته در تماس باشید') ?>
            </p>
            <button class="contact-card-btn js-call-btn"><?= site_e('تماس با ما') ?></button>
          </div>

          <!-- باکس مطالب مرتبط -->
          <div class="related-card">
            <h3 class="related-card-title"><?= site_e('مطالب مرتبط') ?></h3>
            <ul class="related-list">
              <li><a href="/carcinogenesis"><?= site_e('نحوه ایجاد سرطان') ?></a></li>
              <li><a href="/cancergenes"><?= site_e('ژن‌های دخیل در سرطان') ?></a></li>
              <li><a href="/riskfactors"><?= site_e('عوامل خطر در بروز سرطان') ?></a></li>
            </ul>
          </div>
        </div>
      </aside>

      <!-- باکس پاپ‌آپ برای دسکتاپ -->
      <div id="call-popup" class="call-popup-overlay" aria-hidden="true">
        <div class="popup-box" role="dialog" aria-modal="true" aria-labelledby="callPopupTitle">

          <div class="popup-icon">
            <svg viewBox="0 0 24 24" width="34" height="34" fill="none" aria-hidden="true">
              <path d="M21 15.5V19a2 2 0 0 1-2.18 2A18.8 18.8 0 0 1 3 5.18 2 2 0 0 1 5 3h3.5a1.6 1.6 0 0 1 1.55 1.2l.72 2.9a1.7 1.7 0 0 1-.45 1.62L8.9 10.14a14.3 14.3 0 0 0 4.96 4.96l1.42-1.42a1.7 1.7 0 0 1 1.62-.45l2.9.72A1.6 1.6 0 0 1 21 15.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>

          <h3 id="callPopupTitle"><?= site_e('تماس با مرکز تخصصی ارتباطات و سلامت از راه دور') ?></h3>

          <p class="popup-desc">
            <?= site_e('برای دریافت راهنمایی و مشاوره، می‌توانید با شماره زیر تماس بگیرید.') ?>
          </p>

          <a href="tel:03191092030" class="popup-phone-link">
            <span class="popup-phone-label"><?= site_e('شماره تماس') ?></span>
            <strong><?= site_e('۰۳۱-۹۱۰۹۲۰۳۰') ?></strong>
          </a>

          <div class="popup-actions">
            <button class="popup-copy-btn" type="button" data-phone="03191092030">
              <?= site_e('کپی شماره') ?>
            </button>

            <button class="popup-secondary-btn" type="button">
              <?= site_e('بستن') ?>
            </button>
          </div>

        </div>
      </div>

      <!-- ستون اصلی: عکس + متن مقاله -->
      <section class="content-column">

        <div class="hero-image-container">
          <img src="{{image1}}" alt="<?= site_e('نوجوانان و جوانان مبتلا به سرطان') ?>" class="hero-image">
        </div>

        <article class="article-content main-article">

          <h2 class=""><?= site_e('نوجوانان و جوانان مبتلا به سرطان') ?></h2>
          <p class="wow fadeInUp">
            <?= site_e('تخمین زده می‌شود که در سال ۲۰۲۵، ۸۵۴۸۰ نوجوان و جوان بین ۱۵ تا ۳۹ سال در ایالات متحده به سرطان مبتلا خواهند شد. این رقم حدود ۴.۲٪ از کل تشخیص‌های سرطان را تشکیل می‌دهد.') ?>
          </p>
          <p class="wow fadeInUp" data-wow-delay="0.2s">
            <?= site_e('بر اساس داده‌های موجود در ۱۰ سال گذشته، میزان موارد جدید سرطان در هر محل در بین نوجوانان و جوانان بین سال‌های ۲۰۱۳ تا ۲۰۲۲، به طور متوسط سالانه ۰.۳٪ افزایش یافته است. بر اساس آمار سرطان: سرطان در بین نوجوانان و جوانان (۱۵ تا ۳۹ سال)، برنامه نظارت، اپیدمیولوژی و نتایج نهایی NCI که از مدل‌های آماری برای تجزیه و تحلیل استفاده می‌کند، میزان مرگ و میر در بین سال‌های ۲۰۱۴ تا ۲۰۲۳ به طور متوسط سالانه ۰.۹٪ کاهش یافته است. شایع‌ترین سرطان‌ها در نوجوانان و بزرگسالان جوان (۱۵ تا ۳۹ سال) عبارتند از: سرطان سینه، سرطان تیروئید، سرطان بیضه و ملانوما.') ?>
          </p>
          <p class="wow fadeInUp" data-wow-delay="0.4s">
            <?= site_e('سایر سرطان‌ها در نوجوانان و بزرگسالان جوان شامل تومورهای مغز و سایر تومورهای سیستم عصبی مرکزی، سرطان دهانه رحم، سرطان روده بزرگ، لوسمی، لنفوم و سارکوم‌ها (از جمله سرطان استخوان و سارکوم بافت نرم) است.') ?>
          </p>

          <h2 class="wow fadeInUp" data-wow-delay="0.6s"><?= site_e('چالش‌های منحصربه‌فرد نوجوانان و جوانان مبتلا به سرطان') ?></h2>
          <p class="wow fadeInUp" data-wow-delay="0.8s">
            <?= site_e('تشخیص سرطان در سنین نوجوانی و جوانی (گروه سنی ۱۵ تا ۳۹ سال)، فرد را در یکی از حساس‌ترین مقاطع زندگی با بحرانی بزرگ مواجه می‌سازد. این دوران زمانی است که افراد در حال اتمام تحصیلات، آغاز مسیر شغلی، استقلال مالی، پایه‌ریزی روابط اجتماعی و عاطفی و تشکیل خانواده هستند. بروز بیماری و درمان‌های پیچیده آن می‌تواند این فرآیندهای طبیعی رشد را با وقفه یا بحران‌های جدی روبرو کند.') ?>
          </p>
          <p class="wow fadeInUp" data-wow-delay="1.0s">
            <?= site_e('علاوه بر این، ویژگی‌های زیست‌شناختی و ژنتیکی تومورها در این گروه سنی اغلب با تومورهای کودکان و سالمندان متفاوت است و نیاز به رویکردهای تشخیصی و درمانی متمایز و متناسب با شرایط جوانان دارد.') ?>
          </p>

          <h2 class="wow fadeInUp" data-wow-delay="0.6s"><?= site_e('مسائل روانی و سلامت عاطفی') ?></h2>
          <p class="wow fadeInUp" data-wow-delay="0.8s">
            <?= site_e('احساس انزوا از جمع دوستان و همسالان، تغییر تصویر بدنی ناشی از جراحی، شیمی‌درمانی یا پرتودرمانی (مانند ریزش مو یا تغییرات وزن)، اضطراب نسبت به آینده نامعلوم و نوسانات خلقی از شایع‌ترین چالش‌های روانی در این سنین است. دریافت خدمات مشاوره روان‌شناختی فردی و حضور در گروه‌های حمایتی همسالان نقشی اساسی در تقویت تاب‌آوری روحی و انگیزه درمان بیماران جوان دارد.') ?>
          </p>

          <h2 class="wow fadeInUp" data-wow-delay="0.6s"><?= site_e('حفظ باروری و سلامت جنسی') ?></h2>
          <p class="wow fadeInUp" data-wow-delay="0.8s">
            <?= site_e('بسیاری از روش‌های درمانی سرطان، به ویژه شیمی‌درمانی‌های خاص و پرتودرمانی در ناحیه لگن، می‌توانند بر توانایی باروری و سلامت جنسی آینده جوانان تأثیر بگذارند. بسیار مهم است که تیم‌های درمانی پیش از شروع مداخله، گزینه‌های حفظ باروری (نظیر فریز اسپرم، فریز تخمک یا جنین) را با بیمار و خانواده او در میان بگذارند تا فرصت فرزندآوری در سال‌های پس از بهبودی حفظ شود.') ?>
          </p>

          <h2 class="wow fadeInUp" data-wow-delay="0.6s"><?= site_e('بازگشت به تحصیل، کار و استقلال اجتماعی') ?></h2>
          <p class="wow fadeInUp" data-wow-delay="0.8s">
            <?= site_e('پس از اتمام دوره درمان، مرحله دشوار بازگشت به روال روزمره زندگی آغاز می‌شود. مدیریت خستگی مزمن، بازیابی تمرکز و حافظه، هماهنگی با دانشگاه یا محیط کار و رفع نگرانی‌های مالی و بیمه‌ای نیازمند حمایت ساختاریافته و تسهیلات ویژه اجتماعی است.') ?>
          </p>

          <h2 class="wow fadeInUp" data-wow-delay="0.6s"><?= site_e('مراقبت‌های حمایتی و تسکینی ویژه در مکسا') ?></h2>
          <p class="wow fadeInUp" data-wow-delay="0.8s">
            <?= site_e('مؤسسه مکسا با درک کامل شرایط ویژه بیماران نوجوان و جوان و خانواده‌های آنان، مجموعه‌ای از خدمات تخصصی حمایتی و تسکینی را به صورت رایگان ارائه می‌دهد:') ?>
          </p>
          <ul class="wow fadeInUp" data-wow-delay="1.0s">
            <li><?= site_e('کنترل و تسکین تخصصی درد و عوارض جانبی حاد و مزمن درمان‌های انکولوژی؛') ?></li>
            <li><?= site_e('مشاوره‌های فردی و گروهی روان‌شناختی متناسب با روحیات و نیازهای جوانان؛') ?></li>
            <li><?= site_e('خدمات تخصصی تغذیه و سبک زندگی برای حفظ توان بدنی و تسهیل روند بهبود؛') ?></li>
            <li><?= site_e('مددکاری اجتماعی، راهنمایی‌های بیمه‌ای و تسهیل بازگشت به جامعه؛') ?></li>
            <li><?= site_e('پاسخگویی و مشاوره شبانه‌روزی (۲۴/۷) از طریق مرکز ارتباطات و تله‌هلث مکسا.') ?></li>
          </ul>

        </article>

      </section>

    </div>

  </main>

  <script>
    const callBtn = document.querySelector('.js-call-btn');
    const callPopup = document.getElementById('call-popup');
    const closePopupBtn = document.querySelector('.popup-secondary-btn');
    const copyBtn = document.querySelector('.popup-copy-btn');

    const phoneNumber = "03191092030";

    function isMobileDevice() {
      return window.innerWidth <= 1100 || /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent);
    }

    function openPopup() {
      if (!callPopup) return;

      callPopup.style.display = 'flex';
      callPopup.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function closePopup() {
      if (!callPopup) return;

      callPopup.style.display = 'none';
      callPopup.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    if (callBtn) {
      callBtn.addEventListener('click', function() {
        if (isMobileDevice()) {
          window.location.href = "tel:" + phoneNumber;
        } else {
          openPopup();
        }
      });
    }

    if (closePopupBtn) {
      closePopupBtn.addEventListener('click', closePopup);
    }

    if (callPopup) {
      callPopup.addEventListener('click', function(e) {
        if (e.target === callPopup) {
          closePopup();
        }
      });
    }

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && callPopup && callPopup.style.display === 'flex') {
        closePopup();
      }
    });

    if (copyBtn) {
      copyBtn.addEventListener('click', async function() {
        try {
          await navigator.clipboard.writeText(phoneNumber);
          copyBtn.textContent = <?= site_js_text('کپی شد') ?>;
          setTimeout(function() {
            copyBtn.textContent = <?= site_js_text('کپی شماره') ?>;
          }, 1600);
        } catch (err) {
          copyBtn.textContent = <?= site_js_text('امکان کپی نبود') ?>;
          setTimeout(function() {
            copyBtn.textContent = <?= site_js_text('کپی شماره') ?>;
          }, 1600);
        }
      });
    }
  </script>

</body>
</html>

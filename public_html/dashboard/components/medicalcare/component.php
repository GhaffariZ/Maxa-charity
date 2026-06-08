<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>صفحه مقاله استاندارد</title>

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
      --primary-teal: #00a8a8;
      --sidebar-teal: #0f9faa;
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
    /*
      این گرید باعث می‌شود سایدبار از کنار تصویر شاخص شروع شود.
      چون تصویر و مقاله داخل ستون content-column هستند
      و سایدبار هم‌سطح همان ستون شروع می‌شود.
    */

    .article-layout {
      display: grid;
      grid-template-columns: 360px minmax(0, 1fr);
      grid-template-areas: "sidebar content";
      gap: 32px;
      align-items: start;
      direction: rtl;
    }

    .content-column {
      grid-area: content;
      min-width: 0;
    }

    .article-sidebar {
      grid-area: sidebar;
      position: sticky;
      top: 24px;
      align-self: start;
      display: flex;
      flex-direction: column;
      gap: 22px;
      height: fit-content;
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

    /* ---------------- sidebar: contact card ---------------- */

/* ---------------- sidebar wrapper ---------------- */

.article-sidebar {
  grid-area: sidebar;
  align-self: start;
  min-width: 0;
}

/*
  نکته مهم:
  sticky را روی sidebar-stack گذاشتیم نه خود aside.
  این باعث می‌شود کنترل بهتری روی ارتفاع، فاصله بالا و خروج از پایین صفحه داشته باشیم.
*/

/* اصلاح sticky برای جلوگیری از تداخل با هدر */
.sidebar-stack {
  position: sticky;
  top: 150px; /* مقدار بیشتر برای جلوگیری از برخورد با هدر */
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ظاهر جدید باکس تماس */
.contact-card {
  background: linear-gradient(135deg, #0f9faa 0%, #086b73 100%);
  color: #fff;
  border-radius: 35px;
  padding: 40px 25px; /* کشیده‌تر */
  text-align: center;
  box-shadow: 0 20px 40px rgba(15, 159, 170, 0.2);
}

.chat-icon {
  background: rgba(255,255,255,0.2);
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
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

.contact-card-btn {
  background: #fff;
  color: #0f9faa;
  border: none;
  padding: 15px 40px;
  border-radius: 999px;
  font-weight: 800;
  cursor: pointer;
  width: 100%;
  font-size: 1rem;
}

/* استایل مطالب مرتبط (بدون بولت و نشان) */
.related-card { background: #fff; padding: 25px; border-radius: 25px; border: 1px solid #edf2f7; }
.related-card-title { font-size: 1.2rem; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #edf2f7; }
.related-list { list-style: none; padding: 0; margin: 0; }
.related-list li { margin-bottom: 12px; }
.related-list a { 
  display: block; 
  padding: 15px; 
  background: #f8fafc; 
  border-radius: 12px; 
  text-decoration: none; 
  color: #334155; 
  font-weight: 600;
  transition: 0.2s;
}
.related-list a:hover { background: #eef3f6; color: #0f9faa; }

/* استایل پاپ‌آپ */
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
  background: linear-gradient(90deg, #00a8a8, #10b981, #00a8a8);
}


.popup-icon {
  width: 74px;
  height: 74px;
  margin: 0 auto 18px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 26px;
  color: #0f9faa;
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
  color: #0f9faa;
  transition: 0.22s ease;
}

.popup-phone-link:hover {
  transform: translateY(-2px);
  border-color: rgba(0,168,168,0.32);
  box-shadow: 0 14px 30px rgba(0,168,168,0.12);
  text-decoration: none;
}

.popup-phone-label {
  direction: rtl;
  margin-bottom: 4px;
  color: #64748b;
  font-size: 0.8rem;
  font-weight: 700;
}

.popup-phone-link strong {
  font-size: 1.65rem;
  font-weight: 950;
  letter-spacing: 0.02em;
  color: #0f9faa;
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

.popup-secondary-btn {
  background: #f1f5f9;
  color: #334155;
  border: 1px solid #e2e8f0;
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

/* دکمه بستن */
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

/* حالت کلیک */
.popup-actions button:active {
  transform: translateY(-1px) scale(0.985);
}

/* فوکوس برای دسترسی‌پذیری */
.popup-actions button:focus-visible {
  outline: 3px solid rgba(15, 159, 170, 0.24);
  outline-offset: 3px;
}

/* موبایل */
@media (max-width: 480px) {
  .popup-actions {
    grid-template-columns: 1fr;
  }

  .popup-actions button {
    width: 100%;
    min-height: 50px;
  }
}


.popup-secondary-btn:hover {
  background: #e2e8f0;
  color: #0f172a;
  transform: translateY(-2px);
}

.popup-secondary-btn {
  background: #f1f5f9;
  color: #475569;
}

.popup-secondary-btn:hover {
  background: #e2e8f0;
  color: #0f172a;
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


/* ---------------- contact card ---------------- */

.contact-card {
  position: relative;
  overflow: hidden;
  background:
    radial-gradient(circle at top left, rgba(255,255,255,0.22), transparent 34%),
    linear-gradient(160deg, #0f9faa 0%, #078994 100%);
  color: #fff;
  border-radius: 30px;
  padding: 26px 24px 24px;
  box-shadow: 0 18px 45px rgba(15, 159, 170, 0.24);
}

.contact-card::before {
  content: "";
  position: absolute;
  width: 130px;
  height: 130px;
  left: -55px;
  bottom: -55px;
  border-radius: 50%;
  background: rgba(255,255,255,0.12);
}

.contact-card::after {
  content: "";
  position: absolute;
  width: 90px;
  height: 90px;
  right: -38px;
  top: -38px;
  border-radius: 50%;
  background: rgba(255,255,255,0.10);
}

.contact-card > * {
  position: relative;
  z-index: 1;
}

.contact-card-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 5px 13px;
  margin-bottom: 14px;
  border-radius: 999px;
  background: rgba(255,255,255,0.17);
  color: rgba(255,255,255,0.95);
  font-size: 0.78rem;
  font-weight: 700;
  border: 1px solid rgba(255,255,255,0.18);
}

.contact-card-title {
  margin: 0 0 12px 0;
  font-size: 1.45rem;
  line-height: 1.5;
  font-weight: 900;
  color: #fff;
}

.contact-card-text {
  margin: 0 0 20px 0;
  color: rgba(255,255,255,0.92);
  font-size: 0.98rem;
  line-height: 2;
  text-align: right;
}

.contact-info-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
}

.contact-info-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 13px 14px;
  border-radius: 18px;
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.13);
  backdrop-filter: blur(8px);
}

.contact-info-icon {
  width: 32px;
  height: 32px;
  flex: 0 0 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(255,255,255,0.18);
  font-size: 0.95rem;
  line-height: 1;
}

.contact-info-item strong {
  display: block;
  margin-bottom: 2px;
  font-size: 0.95rem;
  font-weight: 800;
  color: #fff;
}

.contact-info-item span:not(.contact-info-icon) {
  display: block;
  font-size: 0.82rem;
  color: rgba(255,255,255,0.78);
  line-height: 1.8;
}

.contact-card-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  min-height: 48px;
  padding: 12px 18px;
  border-radius: 999px;
  background: #ffffff;
  color: #0f9faa;
  text-decoration: none;
  font-weight: 900;
  font-size: 0.98rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.12);
  transition: 0.22s ease;
}

.contact-card-btn:hover {
  transform: translateY(-2px);
  background: #f8fafc;
  color: #078994;
  text-decoration: none;
}

/* ---------------- related card ---------------- */

.related-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 28px;
  padding: 22px;
  box-shadow: 0 14px 34px rgba(15, 23, 42, 0.07);
}

.related-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
  padding-bottom: 14px;
  border-bottom: 1px solid #e2e8f0;
}

.related-card-title {
  position: relative;
  margin: 0;
  font-size: 1.18rem;
  line-height: 1.5;
  font-weight: 900;
  color: var(--text-dark);
}

.related-card-title::after {
  content: "";
  position: absolute;
  right: 0;
  bottom: -15px;
  width: 58px;
  height: 3px;
  border-radius: 999px;
  background: var(--primary-teal);
}

.related-card-count {
  flex: 0 0 auto;
  padding: 4px 10px;
  border-radius: 999px;
  background: rgba(0, 168, 168, 0.09);
  color: var(--primary-teal);
  font-size: 0.76rem;
  font-weight: 800;
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
  position: relative;
  display: flex;
  align-items: center;
  min-height: 52px;
  padding: 12px 42px 12px 14px;
  background: #f8fafc;
  border: 1px solid transparent;
  border-radius: 18px;
  color: var(--text-dark);
  text-decoration: none;
  font-size: 0.94rem;
  line-height: 1.8;
  font-weight: 700;
  transition: 0.22s ease;
}

.related-card .related-list a::before {
  content: none !important;
  display: none !important;
}

.related-card .related-list a {
  padding: 14px 16px !important;
  gap: 0 !important;
}

.related-card .related-list a::after {
  content: "←";
  margin-right: auto;
  color: #94a3b8;
  font-weight: 900;
  transition: 0.22s ease;
}

.related-list a::after {
  content: "←";
  margin-right: auto;
  color: #94a3b8;
  font-weight: 900;
  transition: 0.22s ease;
}

.related-list a:hover {
  background: rgba(0, 168, 168, 0.07);
  border-color: rgba(0, 168, 168, 0.20);
  color: var(--primary-teal);
  transform: translateX(-2px);
  text-decoration: none;
}

.related-list a:hover::after {
  color: var(--primary-teal);
  transform: translateX(-3px);
}


    /* ---------------- sidebar: related posts card ---------------- */

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
      padding: 12px 14px;
      background: var(--bg-soft);
      border-radius: 16px;
      color: var(--text-dark);
      text-decoration: none;
      font-size: 0.98rem;
      font-weight: 600;
      transition: 0.2s ease;
    }

    .related-list a::before {
      content: "";
      width: 8px;
      height: 8px;
      flex: 0 0 8px;
      border-radius: 50%;
      background: var(--primary-teal);
    }

    .related-list a:hover {
      background: rgba(0, 168, 168, 0.09);
      color: var(--primary-teal);
      transform: translateX(-2px);
      text-decoration: none;
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
.contact-card,
.related-card {
  border-radius: 22px;
  padding: 22px 18px;
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
    linear-gradient(155deg, #10aeb8 0%, #07828e 54%, #05646e 100%);
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

.contact-card-text {
  margin: 0 0 26px 0;
  color: rgba(255,255,255,0.92);
  font-size: 1rem;
  line-height: 2.05;
  text-align: center;
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
    /* stagger via data-wow-delay values already present in the markup */
    [data-wow-delay="0.2s"] { animation-delay: 0.2s; }
    [data-wow-delay="0.6s"] { animation-delay: 0.6s; }
    [data-wow-delay="0.8s"] { animation-delay: 0.8s; }
    [data-wow-delay="1.2s"] { animation-delay: 1.2s; }
    [data-wow-delay="1.4s"] { animation-delay: 1.4s; }
    @media (prefers-reduced-motion: reduce) {
      .wow, .fadeInUp { opacity: 1 !important; animation: none !important; }
    }

  </style>

</head>

<body>

  <main class="page-container">

    <header class="header-capsule">
      <h1>مراقبت‌های پزشکی تسکینی</h1>

      <a href="/home" class="breadcrumb-pill">
        صفحه اصلی / خدمات
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
      <h3 class="contact-card-title">برای مشاوره نیاز به کمک دارید؟</h3>
      <p class="contact-card-text">
با مرکز ارتباطات کشوری به صورت 24 ساعت روز و 7 روز هفته در تماس باشید
      </p>
      <button class="contact-card-btn js-call-btn">تماس با ما</button>
    </div>

    <!-- باکس مطالب مرتبط -->
    <div class="related-card">
      <h3 class="related-card-title">مطالب مرتبط</h3>
      <ul class="related-list">
        <li><a href="#">مراقبت حمایتی چیست؟</a></li>
        <li><a href="#">خدمات تسکینی مکسا</a></li>
        <li><a href="#">راهنمای دریافت مشاوره</a></li>
      </ul>
    </div>
  </div>
</aside>

<!-- باکس پاپ‌آپ برای دسکتاپ (Hidden by default) -->
<!-- باکس پاپ‌آپ برای دسکتاپ -->
<div id="call-popup" class="call-popup-overlay" aria-hidden="true">
  <div class="popup-box" role="dialog" aria-modal="true" aria-labelledby="callPopupTitle">

    <div class="popup-icon">
      <svg viewBox="0 0 24 24" width="34" height="34" fill="none" aria-hidden="true">
        <path d="M21 15.5V19a2 2 0 0 1-2.18 2A18.8 18.8 0 0 1 3 5.18 2 2 0 0 1 5 3h3.5a1.6 1.6 0 0 1 1.55 1.2l.72 2.9a1.7 1.7 0 0 1-.45 1.62L8.9 10.14a14.3 14.3 0 0 0 4.96 4.96l1.42-1.42a1.7 1.7 0 0 1 1.62-.45l2.9.72A1.6 1.6 0 0 1 21 15.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>

    <h3 id="callPopupTitle">تماس با مرکز ارتباطات مکسا</h3>

    <p class="popup-desc">
      برای دریافت راهنمایی و مشاوره، می‌توانید با شماره زیر تماس بگیرید.
    </p>

    <a href="tel:03191092030" class="popup-phone-link">
      <span class="popup-phone-label">شماره تماس</span>
      <strong>۰۳۱-۹۱۰۹۲۰۳۰</strong>
    </a>

    <div class="popup-actions">
      <button class="popup-copy-btn" type="button" data-phone="03191092030">
        کپی شماره
      </button>

      <button class="popup-secondary-btn" type="button">
        بستن
      </button>
    </div>

  </div>
</div>


      <!-- ستون اصلی: عکس + متن مقاله -->
      <section class="content-column">

        <div class="hero-image-container">
          <img src="{{image1}}" alt="Hero Image" class="hero-image">
        </div>

        <article class="article-content main-article">

                                <h2 class="">مراقبت‌های پزشکی تسکینی</h2>
                                <p class="wow fadeInUp">
مراقبت‌های پزشکی تسکینی (که به آن طب تسکینی نیز گفته می‌شود) حوزۀ ‌تخصصی جدیدی در پزشکی است. بخش بزرگی از مشکلاتی که بیمار مبتلا به سرطان با آنها مواجه است مشکلات جسمی هستند. این مشکلات جسمی یا به علت وجود سلول‌های ‌سرطانی و فعالیت تومور ایجاد می‌شوند و یا عوارضی هستند که درمان سرطان ایجاد می‌کند. در حالی که هدف اصلی درمانِ علاجی، از بین بردن علت بیماری و علاج آن است، طب تسکینی به تسکین علامت‌ها و کنترل عوارضی می‌پردازد که بیمار مبتلا به سرطان با آنها روبرو می‌شود.
</p>
                                <p class="wow fadeInUp" data-wow-delay="0.2s">
خیلی وقت‌ها، تیم ارائه دهندۀ ‌مراقبت های حمایتی و ‌تسکینی، همان تیم اصلی درمان، شامل پزشک درمانگر (انکولوژیست یا رادیوتراپیست) و پرستاران است، اما این تیم قادر به پاسخگویی تمامی نیاز‌های ‌بیمار نیستند. بنابراین تیم طب تسکینی با همکاریِ تیم درمان، تلاش می‌کنند تا با کنترل علامت‌ها و عوارض بیماری، بیمار و خانوادۀ او را یاری کنند.
</p>
                                <p class="wow fadeInUp" data-wow-delay="0.2s">
کم کردن رنج‌ها و ناراحتی‌های جسمی به بیمار کمک خواهدکرد تا مراحل درمان را راحت‌‌تر پشت سر بگذارد. همچنین کمک می‌کند که استقلال خود را حفظ کرده و کیفیت زندگی بالاتری داشته باشد.

مکسا، اولین و بزرگ‌ترین مرکز ارائۀ خدمات طب تسکینی در ایران است. تمامی خدمات مکسا رایگان هستند. در اینجا می‌توانید خدمات طب تسکینی که توسط مکسا ارائه می‌شود را مشاهده کنید؛ برای دریافت این خدمات، کافیست در مکسا پرونده تشکیل دهید.
</p>
                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">مدیریت فرآیند مراقبت</h2>
                                <p class="wow fadeInUp" data-wow-delay="0.2s">
ابتلا به سرطان، شروع مسیری پرفراز و نشیب و پر پیچ و خم برای بیمار و خانوادۀ ‌اوست. بیمار و خانوادۀ ‌او از زمانی که از بیماری آگاه می‌شوند با سوالات فراوانی مواجه می‌شوند؛ سوالاتی دربارۀ ‌آینده و سرنوشت بیماری، انتخاب روش درمانی و مراقبت‌های ‌لازم برای بیمار و اطرافیان.
ارائۀ اطلاعات معتبر در خصوص نوع بیماری و شرایط بیمار، مسیر درمان و کارهایی که باید برای بیمار انجام شود به زبان ساده و شفاف، برای بیمار و خانواده، از وظایفی است که پزشک طب تسکینی بر عهده دارد. تیم پزشکی تسکینی به تصمیم‌گیری درست کمک و از تبدیل شدن بیماری، به یک بحران بلند مدت خانوادگی جلوگیری می‌کند.

پزشک طب تسکینی، همچنین شرایط بیمار را ارزیابی کرده و مراقبت‌های ‌پزشکی و پرستاری مورد نیاز او را تعیین می‌کند و ارائۀ ‌ خدمات به او را برنامه ریزی می‌کند.
</p>

                                <h2 class="">جلسات مراقبت از خانواده</h2>
                                <p class="wow fadeInUp" data-wow-delay="0.2s">
مراقبت از کسی که به بیماری پیشرفته مبتلاست، کار بسیار دشواری است. مراقبین بیمار و اطرافیان او از سویی تحت فشار روانی و عاطفی قرار دارند و از طرف دیگر برای مراقبت از بیمار به آموزش نیاز دارند. جلسات مراقبت از خانواده، وظیفۀ پاسخ به این نیاز را بر عهده دارد. جلساتی که طی آن وضعیت بیمار به اطلاع خانواده می‌رسد آنها را در مورد مسیر پیشِ رو آگاه می‌کند و در مواقع حساس و ضروری به خانواده در گرفتن تصمیم‌های مهم کمک می‌کند. این جلسات که با همکاری بین تخصص‌های مختلف، خصوصاً پزشک و روان‌شناس، برگزار می‌شود، نقشی مهم در حمایت عاطفی و روانی از خانواده و ایجاد احساس امنیت و آرامش در آنها ایفا می‌کند.
</p>
                                <h2 class="">آموزش به مراقبین</h2>
                                <p class="wow fadeInUp" data-wow-delay="0.2s">
مطالعات نشان داده است که اگر مراقبین بیمار، زودتر با مراقبت‌های حمایتی و تسکینی آشنا شوند، با شرایط مراقبت از بیمار، بهتر تطبیق می‌یابند. پزشک و پرستار تسکینی، مراقبین بیمار را برای مدیریت شرایط و شیوۀ ‌مراقبت از بیمار آموزش می‌دهند. به علاوه بسیاری از آموزش‌های عملی، توسط پرستار به خانواده ارائه‌‌ می‌شود.
</p>
                                <h2 class="">ویزیت تلفنی</h2>
                                <p class="wow fadeInUp" data-wow-delay="0.2s">
پزشکان مکسا به‌طور منظم با بیمار یا مراقبین او تماس می‌گیرند تا از آخرین وضعیت او مطلع شوند و اثربخشی اقدامات قبلی را ارزیابی کنند. ویزیت‌های ‌تلفنی به پزشک هماهنگ کننده کمک می‌کند تا اقدامات بعدی را بر اساس نیاز بیمار، برنامه ریزی کند.
</p>
                                <h2 class="">اقدامات تشخیصی</h2>
                                <p class="wow fadeInUp" data-wow-delay="0.2s">
تشخیص به موقع و زود هنگام عوارض مربوط به بیماری، به کنترل بهتر آن و جلوگیری از پیشرفت آن‌ها کمک می کند. با این حال بسیاری از بیماران به دلیل طولانی بودن صف انتظار برای اقدامات تشخیصی و هزینه‌های بالا، از چنین خدماتی محروم می‌مانند. مکسا، طیفی از خدمات تشخیصی مانند سونوگرافی و سونوگرافی داپلر، گرفتن نوار عصب و عضله و نوار قلب را در مکان‌های مختلف، مانند منزل بیمار، بیمارستان و کلینیک بازتوانی ارائه می دهد.
</p>
                                <h2 class="">کنترل عوارض بیماری</h2>
                                <p class="wow fadeInUp" data-wow-delay="0.2s">
مبتلایان به سرطان ممکن است با عوارض گوناگونی مواجه شوند که ناشی از فعالیت تومور سرطانی یا اثرات جانبی درمان باشد. درد، عوارض گوارشی، آسیت و مشکلات عصبی، مثال‌هایی از این عوارض هستند. یکی از مهم‌ترین کارهایی که پزشکان مکسا انجام می‌دهند، کمک به بیمار با کنترل چنین عوارضی است.
</p>

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
        copyBtn.textContent = 'کپی شد';
        setTimeout(function() {
          copyBtn.textContent = 'کپی شماره';
        }, 1600);
      } catch (err) {
        copyBtn.textContent = 'امکان کپی نبود';
        setTimeout(function() {
          copyBtn.textContent = 'کپی شماره';
        }, 1600);
      }
    });
  }
</script>


</body>
</html>

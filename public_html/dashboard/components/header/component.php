<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') : 'مکسا' ?></title>

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
  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }
    :root{
      --cta-orange:#f5a623;
      --cta-orange-2:#f39a20;
      --cta-text:#ffffff;
      --cta-muted: rgba(255,255,255,.78);
      --cta-container: 1440px;
      --cta-nav-h: 78px;

      --cta-shadow: 0 10px 22px rgba(0,0,0,.14);
      --cta-shadow-soft: 0 8px 18px rgba(0,0,0,.12);
      --cta-radius: 14px;
      --cta-edge-gap: 10px;
    }

    .cta {
      font-family: inherit;
      position: relative;
    }
    .cta a{ color:inherit; text-decoration:none; }
    .cta button, .cta input{ font-family: inherit; }

    .cta-container{
    width: min(var(--cta-container), 100%);
    margin-inline:auto;
    padding-inline:16px;
    }

    /* ===== TOP BAR ===== */
    .cta-topbar{
      position:fixed;
      top:0; left:0; right:0;
      height: var(--cta-nav-h);
      z-index:99999;
      display:flex;
      align-items:center;
      overflow: visible;
      padding-inline: 16px;
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), top 0.3s ease, height 0.3s ease;
    }

    /* Detached look when scrolled */
    .cta-topbar.scrolled {
      top: 12px;
      height: 68px;
    }

    /* فاصله‌گذار هم‌ارتفاع نوار ثابت تا محتوای صفحه زیر آن پنهان نشود */
    .cta-navbar-spacer {
      height: var(--cta-nav-h);
    }

    .cta-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      position: relative;
      z-index: 60;
      background: rgba(40, 32, 10, 0.6); /* Translucent dark slate glass, warmed with a touch of yellow */
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 16px;
      backdrop-filter: blur(16px) saturate(180%);
      -webkit-backdrop-filter: blur(16px) saturate(180%);
      box-shadow:
        0 4px 30px rgba(0, 0, 0, 0.3),
        inset 0 1px 1px rgba(245, 166, 35, 0.14);
      padding: 8px 16px;
    }

    /* راست: لوگو */
    .cta-right{
      display:flex;
      align-items:center;
      flex:0 0 auto;
      margin-right: calc(-1 * var(--cta-edge-gap)); /* نزدیک‌تر به لبه */
      padding-right: var(--cta-edge-gap);
    }

    .cta-brand{
      display:flex;
      align-items:center;
      justify-content:center;
    }

    .cta-brand img{
      height: 34px;
      width: auto;
      display:block;
      object-fit: contain;
      filter: drop-shadow(0 6px 12px rgba(0,0,0,.18));
    }

    /* وسط: منو */
    .cta-center{
      display:flex;
      justify-content:center;
      flex:1 1 auto;
      min-width: 0;
      padding-inline: 8px;
    }

    /* باکس دور منو مثل سرچ/ثبت‌نام */
    .cta-navbox{
      background: transparent;
      border: none;
      padding: 6px 14px;
    }

    .cta-glass {
      backdrop-filter: blur(6px);
      box-shadow: var(--cta-shadow-soft);
    }

    /* ✅ منو افقی (حل عمودی شدن) */
    .cta-menu{
      list-style: none;
      margin: 0;
      padding: 0;
      display:flex;
      align-items:center;
      gap: 6px;
      white-space: nowrap;
      flex-wrap: nowrap;
    }

    .cta-menu > li{
      position: relative;
      display:flex;
      align-items:center;
    }

    .cta-menu > li > a{
      display:inline-flex;
      align-items:center;
      padding:8px 10px;
      border-radius:10px;
      transition: .2s ease;
      outline: none;
      font-size: 14px;
      color: rgba(255,255,255,.92);
    }

    .cta-menu > li > a:hover{
      background: rgba(255,255,255,.10);
    }

    .cta-menu > li > a:focus-visible{
      box-shadow: 0 0 0 3px rgba(245,166,35,.25);
      background: rgba(255,255,255,.10);
    }

    .highlighted-menu > a{
      background: rgba(245,166,35,.18);
      border: 1px solid rgba(245,166,35,.35);
    }
    .highlighted-menu > a:hover{
      background: rgba(245,166,35,.26);
    }

    /* چپ: سرچ + ورود/ثبت‌نام */
    .cta-left{
      display:flex;
      align-items:center;
      gap:10px;
      flex:0 0 auto;
      margin-left: calc(-1 * var(--cta-edge-gap)); /* نزدیک‌تر به لبه */
      padding-left: var(--cta-edge-gap);
    }

    .cta-donate {
      height: 38px;
      border-radius: 10px;
      border: 1px solid rgba(245, 166, 35, 0.5);
      background: linear-gradient(135deg, #f5a623, #f39a20);
      color: #1a1a1a !important;
      padding: 0 16px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      white-space: nowrap;
      font-weight: 800;
      box-shadow: 0 0 12px rgba(245, 166, 35, 0.3);
    }

    .cta-donate:hover {
      transform: translateY(-2px);
      box-shadow: 0 0 20px rgba(245, 166, 35, 0.6);
      background: linear-gradient(135deg, #ffc24d, #f5a623);
    }

    .cta-donate:active {
      transform: translateY(1px);
      box-shadow: 0 0 8px rgba(245, 166, 35, 0.4);
    }

    .cta-auth {
      height: 38px;
      border-radius: 10px;
      border: 1px solid rgba(8, 153, 169, 0.4);
      background: linear-gradient(135deg, #0899A9, #067d8a);
      color: #ffffff !important;
      padding: 0 16px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      white-space: nowrap;
      font-weight: 700;
      box-shadow: 0 0 12px rgba(8, 153, 169, 0.3);
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .cta-auth:hover {
      transform: translateY(-2px);
      box-shadow: 0 0 20px rgba(8, 153, 169, 0.6);
      background: linear-gradient(135deg, #0ab2c5, #0899A9);
    }

    .cta-auth:active {
      transform: translateY(1px);
      box-shadow: 0 0 8px rgba(8, 153, 169, 0.4);
    }

    /* ===== Mega Menu ===== */
    .mega-menu{
      position: relative;
    }

    /* ✅ مگا منو با عرض کامل صفحه */
    .mega-menu .mega-menu-content {
      display: block;
      opacity: 0;
      visibility: hidden;
      transform: translateY(16px) scale(0.98);
      transform-origin: top center;
      transition:
        opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1),
        transform 0.25s cubic-bezier(0.4, 0, 0.2, 1),
        visibility 0.25s;
      position: fixed;
      top: calc(var(--cta-nav-h) + 10px);
      right: 16px;
      left: 16px;
      max-width: var(--cta-container);
      margin-inline: auto;
      background: rgba(8, 153, 169, 0.88); /* Translucent premium teal glass */
      backdrop-filter: blur(20px) saturate(140%);
      -webkit-backdrop-filter: blur(20px) saturate(140%);
      border: 1px solid rgba(255, 255, 255, 0.15);
      padding: 28px 20px;
      box-shadow:
        0 20px 40px rgba(0, 0, 0, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
      z-index: 9999;
      direction: rtl;
      text-align: right;
      border-radius: 16px;
      overflow: hidden;
      box-sizing: border-box;
    }

    /* باز شدن مگا منو */
    .mega-menu.open .mega-menu-content {
      opacity: 1;
      visibility: visible;
      transform: translateY(0) scale(1);
    }

    /* ساختار ردیف و چهار ستون */
    .mega-menu-content .mega-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr); /* چهار ستون در یک ردیف */
      gap: 20px; /* فاصله بین ستون‌ها */
      width: 100%; /* عرض کامل صفحه */
    }

    /* هر ستون (مگا کال) */
    .mega-menu-content .mega-col {
      padding: 0 16px;
      border-left: 1px solid rgba(255,255,255,0.55);
    }

    /* حذف مرز از آخرین ستون */
    .mega-menu-content .mega-col:last-child {
      border-left: none;
    }

    /* استایل متن‌ها */
    .mega-menu-content h6 {
      font-weight: bold;
      margin: 0 0 12px 0;
      color: #fff;
      font-size: 15px;
    }

    .mega-menu-content a {
      display: block;
      color: #f9f9f9;
      font-size: 14px;
      margin-bottom: 10px;
      transition: all .25s ease;
    }

    .mega-menu-content a:hover {
      color: #FFD700;
      padding-right: 5px;
    }

    @media (max-width: 1200px){
      .mega-menu-content .mega-row{
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
    }

    @media (max-width: 980px){
      .mega-menu-content .mega-row{
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 768px){
      .mega-menu .mega-menu-content{
        padding: 20px;
      }
      .mega-menu-content .mega-row{
        grid-template-columns: 1fr;
        gap: 16px;
      }
      .mega-menu-content .mega-col{
        border-left: none;
        padding: 0;
      }
    }

    @media (max-width: 480px){
      .mega-menu-content{
        right: 12px;
        left: 12px;
      }
      .mega-menu-content .mega-col h6{
        font-size: 13px;
      }
      .mega-menu-content a{
        font-size: 13px;
      }
    }

    /* ===== HAMBURGER ICON ===== */
    .menu-icon{
      width: 38px;
      height: 38px;
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 5px;
      cursor: pointer;
      border-radius: 10px;
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.14);
      transition: background .2s ease, border-color .2s ease;
    }

    .menu-icon:hover{
      background: rgba(255,255,255,.14);
      border-color: rgba(255,255,255,.22);
    }

    .menu-icon div{
      width: 17px;
      height: 2px;
      background: #fff;
      border-radius: 2px;
      transition: transform .3s ease, opacity .3s ease;
    }

    .menu-icon.active div:nth-child(1){ transform: translateY(7px) rotate(45deg); }
    .menu-icon.active div:nth-child(2){ opacity: 0; }
    .menu-icon.active div:nth-child(3){ transform: translateY(-7px) rotate(-45deg); }

    /* فقط موبایل و تبلت */
    @media (max-width: 1024px){
      .menu-icon{ display:flex; }
    }

    /* ===== MOBILE MENU (SAME AS DESKTOP) ===== */
    .mobile-menu-sidebar{
      position: fixed;
      top: 0;
      right: -100%;
      width: min(320px, 85vw);
      height: 100vh;
      background: rgba(15, 23, 42, 0.75); /* Dark translucent slate glass */
      border-left: 1px solid rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(20px) saturate(160%);
      -webkit-backdrop-filter: blur(20px) saturate(160%);
      box-shadow: inset 1px 0 0 rgba(255, 255, 255, 0.1);
      z-index: 99999;
      padding: 32px 20px;
      overflow-y: auto;
      /* در حالت بسته کاملاً پنهان تا سایه/بلور به سمت راست صفحه نشت نکند */
      visibility: hidden;
      transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.4s;
      direction: rtl;
    }

    .mobile-menu-sidebar.open{
      right: 0;
      visibility: visible;
      box-shadow:
        -10px 0 30px rgba(0, 0, 0, 0.5),
        inset 1px 0 0 rgba(255, 255, 255, 0.1);
      width: min(320px, 85vw);
    }

    .mobile-menu{
      list-style: none;
      padding: 0;
      margin-top: 20px;
    }

    .mobile-menu > li{
      border-bottom: 1px solid rgba(255,255,255,.1);
      width: 100%;
    }

    .mobile-menu > li > a{
      display: block;
      padding: 16px 12px;
      color: #fff;
      text-decoration: none;
      font-size: 15px;
      transition: .2s;
    }

    .mobile-menu > li > a:hover{
      background: rgba(255,255,255,0.05);
    }

    /* برای منوهای خاص مثل نیکوکاری */
    .mobile-menu .highlighted-menu > a {
      background: rgba(245,166,35,0.2);
      color: #f5a623;
      font-weight: bold;
    }

    /* مگا منو در موبایل */
    .mobile-menu .mega-menu-content{
      display: none;
      position: static;
      background: rgba(0,0,0,.22);
      border: 1px solid rgba(255,255,255,.12);
      border-radius: 10px;
      margin: 8px 0 12px;
      padding: 12px;
      opacity: 1 !important;
      visibility: visible !important;
      transform: none !important;
      transition: none !important;
    }

    .mobile-menu .mega-menu-content.active{
      display: block;
    }

    .mobile-menu .mega-row{
      display: block;
    }

    .mobile-menu .mega-col{
      border: none;
      padding: 0;
      margin-bottom: 14px;
    }

    .mobile-menu .mega-col h6{
      color: #FFD700;
      font-size: 13px;
      margin-bottom: 6px;
    }

    .mobile-menu .mega-col a{
      font-size: 13px;
      padding: 6px 0;
      color: #fff;
    }

    /* فلش آکاردئونی برای مگا منوی موبایل */
    .mobile-menu .mega-toggle {
      position: relative;
      display: flex !important;
      align-items: center;
      justify-content: space-between;
    }

    .mobile-menu .mega-toggle::after {
      content: "▾";
      font-size: 16px;
      transition: transform 0.3s ease;
    }

    .mobile-menu .mega-toggle.open::after {
      transform: rotate(180deg);
    }

    /* ===== SUB MENU ===== */
    /* مخفی کردن سایدبار در حالت عادی (دسکتاپ) */
    .mobile-menu-sidebar {
      display: block; /* برای اینکه CSS های داخلیش لود بشه */
      right: -320px;
    }

    /* فقط وقتی که کلاس open دارد، در موبایل نمایش داده شود */
    @media (max-width: 1024px) {
      .mobile-menu-sidebar.open {
        right: 0;
      }
    }

    /* جلوگیری از اسکرول خوردن دسکتاپ بخاطر سایدبار */
    @media (min-width: 1025px) {
      .mobile-menu-sidebar { display: none !important; }
    }

    .toggle-sub{
      cursor: pointer;
      position: relative;
    }

    .toggle-sub::after{
      content: "▾";
      position: absolute;
      left: 0;
      transition: .3s;
    }

    .sub-menu.active ~ .toggle-sub::after{
      transform: rotate(180deg);
    }

    /* ===== BACKDROP ===== */
    .mobile-backdrop{
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.45);
      z-index: 99998;
      opacity: 0;
      pointer-events: none;
      transition: .3s;
    }

    .mobile-backdrop.show{
      opacity: 1;
      pointer-events: auto;
    }

    /* ===== Responsive ===== */
    html, body {
      margin: 0;            /* حذفِ حاشیه‌ی پیش‌فرضِ مرورگر (۸px) که دورِ کلِ صفحه می‌افتاد */
      padding: 0;
      overflow-x: hidden;
      max-width: 100%;
    }

    * {
      box-sizing: border-box;
    }

    /* تنظیمات تبلت و دسکتاپ متوسط */
    @media (max-width: 1200px) {
      .cta-menu { gap: 4px; }
      .cta-menu > li > a { padding: 8px 7px; font-size: 13px; }
    }

    /* نقطه شکست موبایل و تبلت برای سایدبار همبرگری */
    @media (max-width: 1024px) {
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
        max-width: 100%;
        flex: 1 1 auto;
        justify-content: flex-end;
        gap: 10px;
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

      #mobileMenu {
        display: block;
      }

      #mobileBackdrop {
        display: block;
      }
    }

    /* غیرفعال‌سازی سایدبار و بک‌دراپ در دسکتاپ بزرگ */
    @media (min-width: 1025px) {
      .mobile-menu-sidebar {
        display: none !important;
      }
      .mobile-backdrop {
        display: none !important;
      }
    }

    /* تبلت های کوچک */
    @media (max-width: 600px) {
      .cta-header {
        padding: 6px 12px;
        gap: 8px;
      }

      .cta-right {
        flex-shrink: 0;
      }

      .cta-brand img {
        height: 26px;
      }

      .cta-left {
        gap: 8px;
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

    /* گوشی‌های بسیار باریک */
    @media (max-width: 420px) {
      .cta-header {
        padding: 5px 10px;
        gap: 6px;
      }

      .cta-brand img {
        height: 22px;
      }

      .cta-donate,
      .cta-auth {
        padding: 0 8px;
        font-size: 11px;
        height: 30px;
        gap: 4px;
      }

      .menu-icon {
        width: 32px;
        height: 32px;
      }

      .menu-icon div {
        width: 15px;
      }
    }

    /* ===== Account widget (logged-in state) ===== */
    .cta-auth-slot{position:relative;display:inline-flex;align-items:center}
    .cta-account{position:relative}
    .cta-account-btn{
      height:38px;border-radius:10px;border:1px solid rgba(8,153,169,.4);
      background:linear-gradient(135deg,#0899A9,#067d8a);color:#fff;
      padding:0 12px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;
      white-space:nowrap;font-weight:700;box-shadow:0 0 12px rgba(8,153,169,.3);
      transition:all .3s cubic-bezier(.16,1,.3,1);
    }
    .cta-account-btn:hover{transform:translateY(-2px);box-shadow:0 0 20px rgba(8,153,169,.6);background:linear-gradient(135deg,#0ab2c5,#0899A9)}
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
      z-index:100001;direction:rtl;text-align:right;
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
      max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;direction:ltr;text-align:right}
    .cta-account-menu a,.cta-account-menu button{
      display:flex;align-items:center;gap:10px;width:100%;text-align:right;
      padding:10px 12px;border-radius:10px;color:rgba(255,255,255,.9);font-size:13px;font-weight:600;
      background:none;border:none;cursor:pointer;transition:background .18s,color .18s;font-family:inherit;text-decoration:none;
    }
    .cta-account-menu a:hover,.cta-account-menu button:hover{background:rgba(255,255,255,.1);color:#fff}
    .cta-account-menu svg{width:17px;height:17px;opacity:.85;flex-shrink:0}
    .cta-account-cart{position:relative}
    .cta-cart-badge{margin-inline-start:auto;min-width:20px;height:20px;padding:0 6px;border-radius:99px;
      background:#f5a623;color:#3a2a00;font-size:11px;font-weight:800;display:none;place-items:center;
      font-family:'Vazirmatn',sans-serif;line-height:1}
    .cta-cart-badge[hidden]{display:none}
    .cta-account-logout{color:#ffb4b4 !important;margin-top:6px;border-top:1px solid rgba(255,255,255,.1) !important;border-radius:0 0 8px 8px !important}
    .cta-account-logout:hover{background:rgba(229,57,53,.28) !important;color:#fff !important}
    [hidden]{display:none !important}
  </style>
</head>

<body>

  <div class="cta">

    <div class="cta-topbar">
      <div class="cta-container cta-header">

        <div class="cta-right">
          <a class="cta-brand" href="/home">
            <img src="/dashboard/components/header/images/1.png" alt="مکسا">
          </a>
        </div>

        <!-- ✅ منوی افقی + مگا منو -->
        <div class="cta-center">
          <nav class="cta-navbox" aria-label="منوی بالا">
            <ul class="cta-menu" id="menu">

              <li>
                <a href="/home">خانه</a>
              </li>

              <li class="mega-menu">
                <a class="mega-toggle" href="/ashnaei.html">آشنایی با مکسا</a>
                <div class="mega-menu-content">
                  <div class="mega-row">
                    <div class="mega-col">
                      <h6>معرفی</h6>
                      <a href="/history">تاریخچه و نحوه تاسیس</a>
                      <a href="/mission-vision">ماموریت و چشم انداز</a>
                      <a href="/association">اساسنامه</a>
                      <a href="/organizationalchart">چارت سازمانی</a>
                      <a href="/headdirectors.html">شورای عالی</a>
                      <a href="/directors.html">هیئت مدیره</a>
                      <a href="/CEOoffice.html">مدیرعامل</a>
                      <a href="/geneticistspage.html">کادر اداری</a>
                    </div>
                    <div class="mega-col">
                      <h6>کادر درمان</h6>
                      <a href="/doctorspage.html">پزشکان</a>
                      <a href="/nursespage.html">پرستاران</a>
                      <a href="/psychologistspage.html">روانشناسان</a>
                      <a href="/socialworkerspage.html">مددکاران اجتماعی</a>
                      <a href="/chaplainspage.html">مراقبین معنوی</a>
                      <a href="/dietitianspage.html">متخصصین تغذیه</a>
                      <a href="/physiotherapistspage.html">متخصصین توانبخشی</a>
                      <a href="/geneticistspage.html">مشاوران ژنتیک و غربالگری</a>
                    </div>
                    <div class="mega-col">
                      <h6>مراکز تابعه مکسا</h6>
                      <a href="/CDSTMACSA.html">مرکز رویش استعدادهای دانشجویی مکسا</a>
                      <a href="/amoozesh_maharati.html">مرکز آموزش مهارتی مکسا</a>
                    </div>
                    <div class="mega-col">
                      <h6>گزارش‌ها</h6>
                      <a href="/image-gallery.html">گزارش‌های سالانه</a>
                      <a href="/video-gallery.html">صورت‌های مالی</a>
                    </div>
                  </div>
                </div>
              </li>

              <li class="mega-menu">
                <a class="mega-toggle" href="/MACSAservices.html">خدمات مکسا</a>
                <div class="mega-menu-content">
                  <div class="mega-row">
                    <div class="mega-col">
                      <h6>مراقبت های حمایتی و تسکینی</h6>
                      <a href="/supportiveandpalliativecare">مفهوم مراقبت های حمایتی و تسکینی</a>
                      <a href="/supportiveandpalliativecareteam">اعضای تیم حمایتی و تسکینی</a>
                      <a href="/endoflifecare">مراقبت‌های پایان زندگی</a>
                      <a href="/iran-situation-in-palliative">وضعیت مراقبت های حمایتی و تسکینی در ایران</a>
                    </div>
                    <div class="mega-col">
                      <h6>مراقبت های حمایتی و تسکینی</h6>
                      <a href="/medicalcare">مراقبت های پزشکی تسکینی</a>
                      <a href="/nursecare">مراقبت های پرستاری تسکینی</a>
                      <a href="/psychologicalcare">مراقبت های روانشناختی</a>
                      <a href="/socialworkcare">مددکاری اجتماعی</a>
                      <a href="/spritualcare">مراقبت های معنوی</a>
                      <a href="/nutritioncare">مراقبت های تغذیه</a>
                      <a href="/physiotherapycare">مراقبت های بازتوانی و توانبخشی</a>
                      <a href="/geneticconseuling">مشاوره ژنتیک و غربالگری</a>
                      <a href="/medicalequipments">تامین تجهیزات پزشکی</a>
                    </div>
                    <div class="mega-col">
                      <h6>بخش‌ها</h6>
                      <a href="#">مراقبت در منزل</a>
                      <a href="#">بستری</a>
                      <a href="#">سرپایی</a>
                      <a href="#">کلینیک</a>
                    </div>
                    <div class="mega-col">
                      <h6>پذیرش بیمار</h6>
                      <a href="#">نوبت دهی</a>
                      <a href="#">مدارک موردنیاز</a>
                    </div>
                  </div>
                </div>
              </li>

              <li class="mega-menu">
                <a class="mega-toggle" href="/single-fundraising-option">روش های حمایت</a>
                <div class="mega-menu-content">
                  <div class="mega-row">
                    <div class="mega-col">
                      <h6>همیاری مالی</h6>
                      <a href="/onlinedonation">حمایت مالی آنلاین</a>
                      <a href="/Vows.html">نذورات</a>
                      <a href="/piggybank.html">قلک مکسا</a>
                      <a href="#">پرداخت مستمر هدایای نقدی</a>
                    </div>
                    <div class="mega-col">
                      <h6>همیاری اجتماعی و فرهنگی</h6>
                      <a href="/stand-sell-section">استندها و کارتهای دیجیتال تبریک و تسلیت</a>
                      <a href="/csr.html">مسئولیت اجتماعی</a>
                      <a href="#">حمایت های خلاقانه و هنری</a>
                    </div>
                    <div class="mega-col">
                      <h6>همیاری علمی و تخصصی</h6>
                      <a href="/professionalaid.html">حمایت علمی و تخصصی</a>
                      <a href="/equipmentaid.html">اهدای تجهیزات پزشکی</a>
                    </div>
                    <div class="mega-col">
                      <h6>مشارکت داوطلبی</h6>
                      <a href="/getinvolved.html">همکاری داوطلبانه</a>
                      <a href="#">شرایط جذب داوطلبان</a>
                    </div>
                  </div>
                </div>
              </li>

              <li><a href="/branches.html">شعب</a></li>
              <li><a href="/news.php">اخبار</a></li>
              <li><a href="/macsapedia.php">مکساپدیا</a></li>
              <li><a href="/courses">دوره‌ها</a></li>
              <li><a href="contactus.html">تماس با ما</a></li>
            </ul>
          </nav>
        </div>

        <div class="cta-left">
          <div class="menu-icon" id="menuToggle" aria-label="منوی موبایل">
            <div></div>
            <div></div>
            <div></div>
          </div>

          <a class="cta-donate" href="/onlinedonation" aria-label="کمک آنلاین">
            کمک آنلاین
          </a>

          <div class="cta-auth-slot">
            <a class="cta-auth js-cta-login" href="/benefactor-dashboard/" aria-label="ورود یا ثبت نام">
              ورود / ثبت‌نام
            </a>

            <div class="cta-account js-cta-account" hidden>
              <button class="cta-account-btn" type="button" aria-haspopup="true" aria-expanded="false" aria-label="حساب کاربری">
                <span class="cta-account-avatar js-acc-avatar">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z" stroke="currentColor" stroke-width="2"/><path d="M4 20c1.2-3.5 4.2-5.5 8-5.5s6.8 2 8 5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </span>
                <span class="cta-account-name js-acc-name">حساب کاربری</span>
                <svg class="cta-account-caret" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>

              <div class="cta-account-menu" role="menu">
                <div class="cta-account-head">
                  <span class="cta-account-avatar js-acc-avatar-lg">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z" stroke="currentColor" stroke-width="2"/><path d="M4 20c1.2-3.5 4.2-5.5 8-5.5s6.8 2 8 5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  </span>
                  <div>
                    <strong class="js-acc-fullname">کاربر مکسا</strong>
                    <span class="js-acc-email"></span>
                  </div>
                </div>

                <a role="menuitem" href="/benefactor-dashboard/">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="3" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="12" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="3" y="16" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="2"/></svg>
                  داشبورد من
                </a>
                <a role="menuitem" href="/benefactor-dashboard/profile">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z" stroke="currentColor" stroke-width="2"/><path d="M4 20c1.2-3.5 4.2-5.5 8-5.5s6.8 2 8 5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  پروفایل کاربری
                </a>
                <a role="menuitem" href="/benefactor-dashboard/history">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/><path d="M3 10h18" stroke="currentColor" stroke-width="2"/></svg>
                  پرداخت‌های من
                </a>
                <a role="menuitem" href="/onlinedonation">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21C12 21 4 14.5 4 9.5C4 7 6 5 8.5 5C10.2 5 11.4 5.9 12 7C12.6 5.9 13.8 5 15.5 5C18 5 20 7 20 9.5C20 14.5 12 21 12 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  کمک آنلاین
                </a>

                <?php if (!empty($maxaCoursesContext)): /* فقط داخل صفحه‌های دوره‌ها نمایش داده می‌شود */ ?>
                <a role="menuitem" href="/courses/my" class="cta-account-courses">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 12v5c0 1 3 3 6 3s6-2 6-3v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  دوره‌های من
                </a>
                <a role="menuitem" href="/courses/checkout" class="cta-account-cart">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="9" cy="21" r="1.6" stroke="currentColor" stroke-width="2"/><circle cx="19" cy="21" r="1.6" stroke="currentColor" stroke-width="2"/><path d="M2.5 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 2-1.6L23 7H5.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  سبد خرید
                  <span class="cta-cart-badge" data-cart-badge hidden>۰</span>
                </a>
                <?php endif; ?>

                <button type="button" class="cta-account-logout js-cta-logout" role="menuitem">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10 17l-5-5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12H5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  خروج از حساب
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>

  <!-- فاصله‌گذار برای جبران ارتفاع نوار ثابت بالا -->
  <div class="cta-navbar-spacer" aria-hidden="true"></div>

  <nav class="mobile-menu-sidebar cta-glass" id="mobileMenu" aria-label="منوی موبایل">
  </nav>
  <div class="mobile-backdrop" id="mobileBackdrop"></div>

  <script>
  (function(){
    const toggleBtn = document.getElementById("menuToggle");
    const menu = document.getElementById("mobileMenu");
    const backdrop = document.getElementById("mobileBackdrop");

    if(!toggleBtn || !menu) return;

    function openMenu(){
      menu.classList.add("open");
      backdrop.classList.add("show");
      toggleBtn.classList.add("active");
      document.body.style.overflow = "hidden";
    }

    function closeMenu(){
      menu.classList.remove("open");
      backdrop.classList.remove("show");
      toggleBtn.classList.remove("active");
      document.body.style.overflow = "";
    }

    toggleBtn.addEventListener("click", function(e){
      e.stopPropagation();
      menu.classList.contains("open") ? closeMenu() : openMenu();
    });

    backdrop.addEventListener("click", closeMenu);

    document.querySelectorAll(".toggle-sub").forEach(function(btn){
      btn.addEventListener("click", function(){
        const sub = btn.nextElementSibling;
        if(sub) sub.classList.toggle("active");
      });
    });

    // مگا منو دسکتاپ: باز/بسته شدن با هاور
    (function () {
      const DELAY = 200; // مدت ماندگاری (ms)
      const menus = document.querySelectorAll(".mega-menu");

      menus.forEach(function (menu) {
        let closeTimer = null;

        function openMenu() {
          if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
          }

          // بستن فوری بقیه مگا منوها بدون تاخیر
          menus.forEach(function (other) {
            if (other !== menu) {
              if (other.__closeTimer__) {
                clearTimeout(other.__closeTimer__);
                other.__closeTimer__ = null;
              }
              other.classList.remove("open");
            }
          });

          menu.classList.add("open");
        }

        function closeMenu() {
          if (closeTimer) clearTimeout(closeTimer);

          closeTimer = setTimeout(function () {
            menu.classList.remove("open");
            closeTimer = null;
            menu.__closeTimer__ = null;
          }, DELAY);

          menu.__closeTimer__ = closeTimer;
        }

        const toggle = menu.querySelector(".mega-toggle");
        const content = menu.querySelector(".mega-menu-content");

        if (toggle) {
          toggle.addEventListener("mouseenter", openMenu);
          toggle.addEventListener("mouseleave", closeMenu);
        }

        if (content) {
          content.addEventListener("mouseenter", openMenu);
          content.addEventListener("mouseleave", closeMenu);
        }
      });
    })();

    document.addEventListener("DOMContentLoaded", function(){
      const desktopMenu = document.querySelector(".cta-menu");
      const mobileMenu = document.getElementById("mobileMenu");

      if(!desktopMenu || !mobileMenu) return;

      // کپی کامل منو
      const clonedMenu = desktopMenu.cloneNode(true);
      clonedMenu.classList.remove("cta-menu");
      clonedMenu.classList.add("mobile-menu");
      mobileMenu.appendChild(clonedMenu);

      // تبدیل مگا منو به آکاردئون
      mobileMenu.querySelectorAll(".mega-menu").forEach(function(item){
        const toggle = item.querySelector(".mega-toggle");
        const content = item.querySelector(".mega-menu-content");

        if(toggle && content){
          toggle.addEventListener("click", function(e){
            e.preventDefault();
            content.classList.toggle("active");
            toggle.classList.toggle("open");
          });
        }
      });

      // هدر همیشه ثابت و قابل مشاهده؛ فقط ظاهر «جداشده» هنگام اسکرول
      (function() {
        const topbar = document.querySelector(".cta-topbar");
        if (!topbar) return;

        window.addEventListener("scroll", function() {
          // اگر اسکرول بیشتر از 50 پیکسل بود هدر جدا شود (بدون مخفی شدن)
          if (window.scrollY > 50) {
            topbar.classList.add("scrolled");
          } else {
            topbar.classList.remove("scrolled");
          }
        });
      })();
    });
  })();
  </script>

  <!-- ===== وضعیت ورود کاربر: تبدیل دکمه «ورود/ثبت‌نام» به منوی حساب کاربری ===== -->
  <script>
  (function(){
    if (window.__ctaAuthWidget__) return;   // فقط یک‌بار اجرا شود حتی اگر چند کامپوننت نوار در صفحه باشد
    window.__ctaAuthWidget__ = true;

    function ready(fn){
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
      else fn();
    }
    function initials(u){
      var f=(u.first_name||'').trim(), l=(u.last_name||'').trim();
      if(f||l) return ((f[0]||'')+(l[0]||'')) || (f[0]||l[0]||'');
      var e=(u.email||'').trim();
      return e ? e[0].toUpperCase() : '؟';
    }
    function displayName(u){
      var n=((u.first_name||'')+' '+(u.last_name||'')).trim();
      if(n) return n;
      return u.email ? u.email.split('@')[0] : 'حساب کاربری';
    }
    function setAvatar(el, u){
      if(!el) return;
      if(u.avatar_url){
        el.innerHTML='';
        var img=document.createElement('img'); img.src=u.avatar_url; img.alt=''; el.appendChild(img);
      } else {
        el.textContent = initials(u);
      }
    }
    function renderLoggedIn(slot, u){
      var login = slot.querySelector('.js-cta-login');
      var account = slot.querySelector('.js-cta-account');
      if(login) login.setAttribute('hidden','');
      if(!account) return;
      account.removeAttribute('hidden');

      var name = displayName(u);
      var nameEl = slot.querySelector('.js-acc-name'); if(nameEl) nameEl.textContent = name;
      var fullEl = slot.querySelector('.js-acc-fullname'); if(fullEl) fullEl.textContent = name;
      var emailEl = slot.querySelector('.js-acc-email'); if(emailEl) emailEl.textContent = u.email || '';
      setAvatar(slot.querySelector('.js-acc-avatar'), u);
      setAvatar(slot.querySelector('.js-acc-avatar-lg'), u);

      var btn = slot.querySelector('.cta-account-btn');
      if(btn){
        btn.addEventListener('click', function(e){
          e.stopPropagation();
          var open = account.classList.toggle('open');
          btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
      }
      var menu = slot.querySelector('.cta-account-menu');
      if(menu){ menu.addEventListener('click', function(e){ e.stopPropagation(); }); }

      var logout = slot.querySelector('.js-cta-logout');
      if(logout){
        logout.addEventListener('click', function(){
          logout.disabled = true;
          fetch('/api/auth/logout', { method:'POST', credentials:'include' })
            .catch(function(){})
            .then(function(){ window.location.reload(); });
        });
      }
    }

    // کلیک بیرون / Escape همه‌ی منوهای باز را می‌بندد
    document.addEventListener('click', function(){
      document.querySelectorAll('.cta-account.open').forEach(function(a){
        a.classList.remove('open');
        var b=a.querySelector('.cta-account-btn'); if(b) b.setAttribute('aria-expanded','false');
      });
    });
    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape'){
        document.querySelectorAll('.cta-account.open').forEach(function(a){
          a.classList.remove('open');
          var b=a.querySelector('.cta-account-btn'); if(b) b.setAttribute('aria-expanded','false');
        });
      }
    });

    ready(function(){
      var slots = Array.prototype.slice.call(document.querySelectorAll('.cta-auth-slot'));
      if(!slots.length) return;

      // با کوکی رفرش (httpOnly) یک access_token تازه می‌گیریم؛ این کار نشست را هم تمدید می‌کند.
      fetch('/api/auth/refresh', { method:'POST', credentials:'include', headers:{ 'Accept':'application/json' } })
        .then(function(res){ return res.ok ? res.json() : null; })
        .then(function(j){
          var token = j && j.data && j.data.access_token;
          if(!token) return null;
          return fetch('/api/user/me', {
            headers:{ 'Authorization':'Bearer '+token, 'Accept':'application/json' },
            credentials:'include'
          })
          .then(function(r){ return r.ok ? r.json() : null; })
          .then(function(me){ return (me && me.data && me.data.user) ? me.data.user : {}; });
        })
        .then(function(user){
          if(!user) return;   // وارد نشده: همان دکمه ورود/ثبت‌نام بماند
          slots.forEach(function(slot){ renderLoggedIn(slot, user); });
        })
        .catch(function(){ /* وارد نشده */ });
    });
  })();
  </script>

</body>
</html>

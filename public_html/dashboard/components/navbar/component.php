<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Navbar</title>

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
      background: rgba(15, 23, 42, 0.6); /* Translucent dark slate glass */
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 16px;
      backdrop-filter: blur(16px) saturate(180%);
      -webkit-backdrop-filter: blur(16px) saturate(180%);
      box-shadow:
        0 4px 30px rgba(0, 0, 0, 0.3),
        inset 0 1px 1px rgba(255, 255, 255, 0.15);
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
      right: 0;
      left: 0;
      width: calc(100% - 32px);
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

    @media (max-width: 1024px){
      .mega-menu-content{
        width: calc(100vw - 32px); /* هم‌چنان با حاشیه‌ها هم‌خوانی داشته باشه */
      }
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

    @media (max-width: 640px){
      .mega-menu-content .mega-row{
        grid-template-columns: 1fr;
      }
    }

    @media (max-width:768px){
      .mega-menu .mega-menu-content{
        right:0;
        left:0;
        width:100%;
        max-width:100%;
        padding:20px;
        box-sizing:border-box;
      }
    }

    /* ===== HAMBURGER ICON ===== */
    .menu-icon{
      width: 28px;
      height: 20px;
      display: none;
      flex-direction: column;
      justify-content: space-between;
      cursor: pointer;
    }

    .menu-icon div{
      height: 3px;
      background: #fff;
      border-radius: 3px;
      transition: .3s ease;
    }

    /* فقط موبایل و تبلت */
    @media (max-width: 1024px){
      .menu-icon{ display:flex; }
    }

    /* ===== MOBILE MENU (SAME AS DESKTOP) ===== */
    .mobile-menu-sidebar{
      position: fixed;
      top: 0;
      right: -320px;
      width: 100%;
      max-width: 320px;
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
        --cta-nav-h: 64px;
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
        gap: 12px;
      }

      .cta-donate {
        order: 1;
        font-size: 13px;
        padding: 0 12px;
        height: 36px;
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

    /* گوشی‌های بسیار باریک */
    @media (max-width: 420px) {
      .cta-auth,
      .cta-donate {
        padding: 0 10px;
        font-size: 12px;
      }
    }
  </style>
</head>

<body>

  <div class="cta">

    <div class="cta-topbar">
      <div class="cta-container cta-header">

        <div class="cta-right">
          <a class="cta-brand" href="/home">
            <img src="{{image1}}">
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
              <li><a href="/macsapedia.html">مکساپدیا</a></li>
              <li><a href="/dashboard/courses.php">دوره‌ها</a></li>
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

          <a class="cta-auth" href="/benefactor-dashboard/" aria-label="ورود یا ثبت نام">
            ورود / ثبت‌نام
          </a>
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
      document.body.style.overflow = "hidden";
    }

    function closeMenu(){
      menu.classList.remove("open");
      backdrop.classList.remove("show");
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

</body>
</html>

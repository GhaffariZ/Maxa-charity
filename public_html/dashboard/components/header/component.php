<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Pure Menu</title>

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
:root{
  --gold:#FFBF00;
  --gold-dark:#DAA520;
  --container:1180px;
  --nav-h:78px;
}

/* ===== RESET ===== */
*{box-sizing:border-box}
body{
  margin:0;
  background:#fff;
  color:#000;
}
a{text-decoration:none;color:inherit}

/* ===== CONTAINER ===== */
.cta-container{
  width:min(var(--container),100%);
  margin-inline:auto;
  padding-inline:16px;
}

/* ===== TOPBAR ===== */
.cta-topbar{
  position: sticky;
  top: 0;
  z-index: 9999999;
  width: 100%;
  isolation: isolate;
  background: var(--gold);
}
.cta-header{
  width:100%;
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:10px 20px;
  position:relative;
}

.cta-header > *{
  position: relative;
  z-index: 1;
}

/* ===== RIGHT (LOGO) ===== */
.cta-right{
  display:flex;
  align-items:center;
  margin-left:auto;
  position:relative;
  z-index:10;
}

.cta-left{
  display:flex;
  align-items:center;
  gap:10px;
}
.cta-brand{
  position:relative;
  z-index:20;
}

.cta-brand img{
  height:34px;
  width:auto;
  display:block;
  position:relative;
  z-index:20;
}

.glass-menu{
  backdrop-filter: blur(14px);
  background: rgba(255,255,255,0.35);
  border-radius:14px;
  padding:6px 10px;
  display:inline-flex;
}

/* ===== CENTER MENU ===== */
.cta-center{
  position:absolute;
  left:50%;
  transform:translateX(-50%);
  display:flex;
  justify-content:center;
  z-index:5;
}

.cta-center.glass-menu{
  flex:0 0 auto;
}
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

@media(max-width:720px){
  .cta-center{ display:none; }
  .btn{ display:none; }
  .menu-icon{ display:flex; }
}
/* ✅ مگا منو با عرض کامل صفحه */
.mega-menu-content{
  display:none;
  position:fixed;
  top:78px; /* ارتفاع هدر */
  left:50%;
  transform:translateX(-50%);
  width:min(1200px,92vw);
  background:#0899A9;
  padding:28px 30px;
  border-radius:14px;
  box-shadow:0 20px 60px rgba(0,0,0,.35);
  z-index:9999;
}

.mega-menu-content .mega-row{
  display:grid;
  grid-template-columns: repeat(4,1fr);
  gap:30px;
}

/* باز شدن مگا منو */
.mega-menu.open .mega-menu-content{
  display:block;
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
  z-index: 9999999;
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

/* فقط موبایل */
@media (max-width: 720px){
  .menu-icon{ display:flex; }
}


/* ===== MOBILE MENU (SAME AS DESKTOP) ===== */
.mobile-menu-sidebar{
  position: fixed;
  top: 0;
  right: -320px;
  width: 320px;
  height: 100vh;
  background: rgba(0,0,0,.28);
  border-left: 1px solid rgba(255,255,255,.20);
  backdrop-filter: blur(6px);
  box-shadow: var(--cta-shadow);
  z-index: 10000000;
  padding: 16px;
  overflow-y: auto;
  transition: right .35s ease;
  direction: rtl;
}

.mobile-menu-sidebar.open{
  right: 0;
}

.mobile-menu{
  list-style: none;
  padding: 0;
  margin: 0;
}

.mobile-menu > li{
  border-bottom: 1px solid rgba(255,255,255,.12);
}

.mobile-menu a{
  display: block;
  padding: 14px 8px;
  color: rgba(255,255,255,.92);
  font-size: 14px;
  border-radius: 8px;
}

.mobile-menu a:hover{
  background: rgba(255,255,255,.10);
}

/* مگا منو در موبایل */
.mobile-menu-sidebar{
  position: fixed;
  top: 0;
  right: -320px;
  width: 320px;
  height: 100vh;
  background: rgba(0,0,0,.28);
  border-left: 1px solid rgba(255,255,255,.20);
  backdrop-filter: blur(6px);
  box-shadow: var(--cta-shadow);
  z-index: 10000000;
  padding: 16px;
  overflow-y: auto;
  transition: right .35s ease;
  direction: rtl;
}

.mobile-menu-sidebar.open{
  right: 0;
}

.mobile-menu{
  list-style: none;
  padding: 0;
  margin: 0;
}

.mobile-menu > li{
  border-bottom: 1px solid rgba(255,255,255,.12);
}

.mobile-menu a{
  display: block;
  padding: 14px 8px;
  color: rgba(255,255,255,.92);
  font-size: 14px;
  border-radius: 8px;
}

.mobile-menu a:hover{
  background: rgba(255,255,255,.10);
}

/* مگا منو در موبایل */
.mobile-menu .mega-menu-content{
  display:none;
  position:static;
  transform:none;
  left:auto;
  right:auto;
  width:100%;
  max-width:100%;
  background: rgba(0,0,0,.22);
  border:1px solid rgba(255,255,255,.12);
  border-radius:10px;
  margin:8px 0 12px;
  padding:12px;
  box-sizing:border-box;
}

.mobile-menu .mega-menu-content.active{
  display:block;
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
/* ===== SUB MENU ===== */
.mobile-menu-sidebar .sub-menu{
  display: none;
  padding-right: 10px;
  background: rgba(255,255,255,.04);
}

.mobile-menu-sidebar .sub-menu.active{
  display: block;
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

.btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:11px 22px;
  border-radius:12px;
  font-size:14px;
  font-weight:600;
  border:none;
  cursor:pointer;
  transition: transform .18s ease, box-shadow .18s ease;
}
/* حالت hover */
.btn:hover{
  transform: translateY(-3px);
  box-shadow:
    0 7px 0 rgba(0,0,0,.25),
    0 12px 20px rgba(0,0,0,.25);
}
/* حالت کلیک (فشرده شدن دکمه) */
.btn:active{
  transform:translateY(4px);
  box-shadow:
    0 2px 0 rgba(0,0,0,.25),
    0 5px 10px rgba(0,0,0,.25);
}

.btn-primary{
  background:#0899A9;
  color:#fff;

  box-shadow:
  0 4px 0 #067A88,
  0 6px 10px rgba(0,0,0,0.2);

  transition:all .15s ease;
}


.btn-primary:active{
  transform:translateY(3px);
  box-shadow:
  0 1px 0 #0899A9,
  0 3px 6px rgba(0,0,0,0.2);
}

.btn-primary:hover{
  background:#0899A9;
  color:#fff;
}
.btn-outline{
  background:#dc2626;
  color:#fff;

  box-shadow:
  0 4px 0 #b91c1c,
  0 6px 10px rgba(0,0,0,0.2);

  transition:all .15s ease;
}

.btn-outline:active{
  transform:translateY(3px);
  box-shadow:
  0 1px 0 #b91c1c,
  0 3px 6px rgba(0,0,0,0.2);
}
.btn-outline:hover{
  background:#dc2626;
  color:#fff;
}
.btn-primary,
.btn-primary:hover,
.btn-primary:focus{
  background:#0899A9 !important;
  color:#fff !important;
}

.btn-outline,
.btn-outline:hover,
.btn-outline:focus{
  background:#dc2626 !important;
  color:#fff !important;
}

.cta-menu .mega-toggle{
  color:#000 !important;
}

.cta-menu .mega-toggle:hover{
  color:#000 !important;
}
  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }
/* ===== RESPONSIVE ===== */
@media(max-width:720px){
  .cta-center{display:none}
  .menu-icon{display:flex}
  .cta-search{display:none}
}

@media (max-width: 720px) {
  .cta-left .btn,
  .cta-left .btn-primary,
  .cta-left .btn-outline {
    padding: 7px 14px;
    font-size: 12px;
    border-radius: 8px;
    min-width: 70px;
    /* اگر خواستی دکمه‌ها نزدیک‌تر هم باشند */
    margin-left: 4px;
    margin-right: 0px;
  }
  .cta-left {
    gap: 4px;
  }
}

/* ===== ویجت حساب کاربری (وضعیت ورود مشترک در همه‌ی صفحات) ===== */
.cta-auth-slot{position:relative;display:inline-flex;align-items:center}
.cta-auth{
  height:38px;border-radius:10px;border:1px solid rgba(8,153,169,.4);
  background:linear-gradient(135deg,#0899A9,#067d8a);color:#fff !important;
  padding:0 16px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;
  white-space:nowrap;font-weight:700;text-decoration:none;font-size:14px;
  box-shadow:0 0 12px rgba(8,153,169,.3);transition:all .3s cubic-bezier(.16,1,.3,1);
}
.cta-auth:hover{transform:translateY(-2px);box-shadow:0 0 20px rgba(8,153,169,.6);background:linear-gradient(135deg,#0ab2c5,#0899A9)}
.cta-account{position:relative}
.cta-account-btn{
  height:38px;border-radius:10px;border:1px solid rgba(8,153,169,.4);
  background:linear-gradient(135deg,#0899A9,#067d8a);color:#fff;
  padding:0 12px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;
  white-space:nowrap;font-weight:700;box-shadow:0 0 12px rgba(8,153,169,.3);
  transition:all .3s cubic-bezier(.16,1,.3,1);font-family:inherit;
}
.cta-account-btn:hover{transform:translateY(-2px);box-shadow:0 0 20px rgba(8,153,169,.6);background:linear-gradient(135deg,#0ab2c5,#0899A9)}
.cta-account-avatar{width:26px;height:26px;border-radius:50%;background:rgba(255,255,255,.18);
  display:grid;place-items:center;font-weight:800;font-size:13px;overflow:hidden;flex-shrink:0;color:#fff}
.cta-account-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%}
.cta-account-avatar svg{width:16px;height:16px}
.cta-account-name{max-width:120px;overflow:hidden;text-overflow:ellipsis;font-size:13px}
.cta-account-caret{width:14px;height:14px;transition:transform .25s ease;opacity:.85;flex-shrink:0}
.cta-account.open .cta-account-caret{transform:rotate(180deg)}
.cta-account-menu{
  position:absolute;top:calc(100% + 10px);left:0;min-width:236px;
  background:rgba(15,23,42,.96);backdrop-filter:blur(16px) saturate(160%);
  -webkit-backdrop-filter:blur(16px) saturate(160%);
  border:1px solid rgba(255,255,255,.12);border-radius:14px;
  box-shadow:0 20px 40px rgba(0,0,0,.45);padding:8px;
  opacity:0;visibility:hidden;transform:translateY(-8px) scale(.98);transform-origin:top left;
  transition:opacity .2s,transform .22s cubic-bezier(.16,1,.3,1),visibility .2s;
  z-index:100001;direction:rtl;text-align:right;max-width:calc(100vw - 20px);
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
.cta-account-logout{color:#ffb4b4 !important;margin-top:6px;border-top:1px solid rgba(255,255,255,.1) !important;border-radius:0 0 8px 8px !important}
.cta-account-logout:hover{background:rgba(229,57,53,.28) !important;color:#fff !important}
@media (max-width:480px){
  .cta-account-menu{min-width:auto;right:10px;left:auto;transform-origin:top right}
}
[hidden]{display:none !important}
</style>
</head>

<body>
<div class="macsa-menu-wrapper">
<header class="cta-topbar">
  <div class="cta-header">

    <!-- راست: لوگو -->
    <div class="cta-right">
      <a class="cta-brand" href="/">
        <img src="{{image1}}">
      </a>
    </div>

    <div class="cta-center glass-menu">
      <ul class="cta-menu">

                <li>
                  <a class="mega-toggle" href="/home">خانه</a>
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
                        <a href="/headdirectors.html">هیئت امنا</a>
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

                <li><a class="mega-toggle" href="/branches.html">شعب</a></li>
                <li><a class="mega-toggle" href="/news.php">اخبار</a></li>
                <li><a class="mega-toggle" href="/macsapedia.html">مکساپدیا</a></li>
                <li><a class="mega-toggle" href="/courses">دوره‌ها</a></li>
                <li><a class="mega-toggle" href="contactus.html">تماس با ما</a></li>
              </ul>
          </div>

    <div class="cta-left">
      <a class="btn btn-primary" href="/onlinedonation">کمک آنلاین</a>
      <a class="btn btn-outline" href="/macsa.phealth.ir/index.html">شرایط بحران</a>

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
            <a role="menuitem" href="/my-courses">
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="2"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" stroke="currentColor" stroke-width="2"/></svg>
              دوره‌های من
            </a>
            <a role="menuitem" href="/benefactor-dashboard/profile">
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z" stroke="currentColor" stroke-width="2"/><path d="M4 20c1.2-3.5 4.2-5.5 8-5.5s6.8 2 8 5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              پروفایل کاربری
            </a>

            <button type="button" class="cta-account-logout js-cta-logout" role="menuitem">
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10 17l-5-5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12H5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              خروج از حساب
            </button>
          </div>
        </div>
      </div>

      <div class="menu-icon" id="menuToggle">
        <div></div><div></div><div></div>
      </div>
    </div>

  </div>
</header>

<nav class="mobile-menu-sidebar" id="mobileMenu"></nav>
<div class="mobile-backdrop" id="mobileBackdrop"></div>
 <script>
 (function(){
    const track = document.getElementById("ctaTrack");
    const slides = track ? Array.from(track.children) : [];
    const prevBtn = document.getElementById("ctaPrev");
    const nextBtn = document.getElementById("ctaNext");
    const dotsWrap = document.getElementById("ctaDots");

    const AUTOPLAY_MS = 3000;
    let index = 0;
    let timer = null;

    if(!track || slides.length === 0){ return; }

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
      track.style.transform = "translateX(" + (-index * 100) + "%)";
    }

    function goTo(i, userAction){
      index = (i + slides.length) % slides.length;
      applyTransform();
      setActiveDot(index);
      if(userAction){ restartAutoplay(); }
    }

    function next(userAction){ goTo(index + 1, userAction); }
    function prev(userAction){ goTo(index - 1, userAction); }

    if(prevBtn){ prevBtn.addEventListener("click", function(){ prev(true); }); }
    if(nextBtn){ nextBtn.addEventListener("click", function(){ next(true); }); }

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
      hero.addEventListener("mouseenter", stopAutoplay);
      hero.addEventListener("mouseleave", startAutoplay);

      let startX = 0;
      let dragging = false;

      hero.addEventListener("touchstart", function(e){
        if(!e.touches || !e.touches.length){ return; }
        dragging = true;
        startX = e.touches[0].clientX;
        stopAutoplay();
      }, {passive:true});

      hero.addEventListener("touchend", function(e){
        if(!dragging){ return; }
        dragging = false;
        const endX = (e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0].clientX : startX;
        const dx = endX - startX;
        if(Math.abs(dx) > 40){
          if(dx < 0){ next(true); }
          else{ prev(true); }
        }
        startAutoplay();
      }, {passive:true});
    }

    window.addEventListener("keydown", function(e){
      if(e.key === "ArrowLeft"){ next(true); }
      if(e.key === "ArrowRight"){ prev(true); }
    });

    applyTransform();
    startAutoplay();

    const cardsWrap = document.getElementById("ctaCards");
    if(!cardsWrap){ return; }
    const cards = Array.from(cardsWrap.querySelectorAll(".cta-card"));

    function setActiveCard(card){
      cards.forEach(function(c){
        const isActive = (c === card);
        c.classList.toggle("is-active", isActive);
        c.setAttribute("aria-pressed", isActive ? "true" : "false");
      });
    }

function activate(card){
  setActiveCard(card);
  const link = card.getAttribute("data-link");
  if(!link){ return; }

  if(link.indexOf("#") === 0){
    const el = document.querySelector(link);
    if(el){
      el.scrollIntoView({behavior:"smooth", block:"start"});
    }
  } else {
    window.location.href = link;
  }
}


    cards.forEach(function(card){
      card.addEventListener("click", function(){ activate(card); });
      card.addEventListener("keydown", function(e){
        if(e.key === "Enter" || e.key === " "){
          e.preventDefault();
          activate(card);
        }
      });
    });
  })();

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
(function () {
  const DELAY = 250; // مدت ماندگاری (ms)

  document.querySelectorAll(".mega-menu").forEach(function (menu) {
    let closeTimer = null;

    function openMenu() {
      if (closeTimer) {
        clearTimeout(closeTimer);
        closeTimer = null;
      }
      menu.classList.add("open");
    }

    function closeMenu() {
      closeTimer = setTimeout(function () {
        menu.classList.remove("open");
      }, DELAY);
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
      });
    }
  });

});
})();
  </script>

  <!-- ===== وضعیت ورود کاربر: تبدیل دکمه «ورود/ثبت‌نام» به منوی حساب کاربری ===== -->
  <script>
  (function(){
    if (window.__ctaAuthWidget__) return;   // فقط یک‌بار اجرا شود
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

      // با کوکی رفرش (httpOnly) یک access_token تازه می‌گیریم؛ نشست را هم تمدید می‌کند.
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
          .then(function(me){ return (me && me.data && me.data.user) ? me.data.user : null; });
        })
        .then(function(user){
          if(!user) return;   // وارد نشده: دکمه ورود/ثبت‌نام بماند
          slots.forEach(function(slot){ renderLoggedIn(slot, user); });
        })
        .catch(function(){ /* وارد نشده */ });
    });
  })();
  </script>

</body>
</html>

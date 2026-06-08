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
                <li><a class="mega-toggle" href="contactus.html">تماس با ما</a></li>
              </ul>
          </div>

    <div class="cta-left">
      <a class="btn btn-primary" href="/onlinedonation">کمک آنلاین</a>
      <a class="btn btn-outline" href="/macsa.phealth.ir/index.html">شرایط بحران</a>

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

</body>
</html>

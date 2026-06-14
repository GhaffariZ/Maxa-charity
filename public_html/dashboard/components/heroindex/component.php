<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CTA Hero Slider</title>

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
      --cta-container: 1180px;
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

    /* ===== HERO ===== */
    .cta-hero{
      position:relative;
      width:100%;
      min-height: 560px;
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

    /* Hide on scroll down */
    .cta-topbar.scroll-down {
      transform: translateY(-120%);
    }

    /* Force show on hover/reveal */
    .cta-topbar.scroll-down.force-show {
      transform: translateY(0);
    }

    .cta-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      position: relative;
      z-index: 60;
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

    .cta-search{
      position:relative;
      display:flex;
      align-items:center;
      gap:8px;
    }

    .cta-search input{
      width: 170px;
      height: 38px;
      border-radius: 10px;
      border: 1px solid rgba(255,255,255,.20);
      background: rgba(0,0,0,.28);
      color: #fff;
      padding: 0 12px;
      outline: none;
      transition: .2s ease;
    }

.cta-glass {
  backdrop-filter: blur(6px);
  box-shadow: var(--cta-shadow-soft);
}
    .cta-search input::placeholder{
      color: rgba(255,255,255,.65);
    }

    .cta-search input:focus{
      border-color: rgba(245,166,35,.55);
      box-shadow: 0 0 0 2px rgba(245,166,35,.08);
      background: rgba(0,0,0,.34);
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

/* .cta-band-cta is defined below with unified styles */
    /* ===== Mega Menu (از نمونه شما) ===== */
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

.cta-header{
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
  box-shadow: 
    -10px 0 30px rgba(0, 0, 0, 0.5), 
    inset 1px 0 0 rgba(255, 255, 255, 0.1);
  z-index: 99999;
  padding: 32px 20px;
  overflow-y: auto;
  transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1);
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

/* استایل باکس جستجو در موبایل */
.mobile-search {
  display: block !important;
  margin-bottom: 20px;
  width: 100%;
}

.mobile-search input {
  width: 100%;
  height: 42px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(0, 0, 0, 0.28);
  color: #fff;
  padding: 0 12px;
  outline: none;
  box-sizing: border-box;
}

.mobile-search input:focus {
  border-color: rgba(245, 166, 35, 0.55);
  background: rgba(0, 0, 0, 0.34);
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

    /* ===== Slider ===== */
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
      padding-top: calc(var(--cta-nav-h) + 20px);
      padding-bottom: 54px;
      min-height: 560px;
      display:flex;
      align-items:center;
    }

    .cta-content .cta-container{
      display:flex;
      align-items:center;
      justify-content:flex-start;
    }

    .cta-text{
      width:min(560px, 100%);
      color: var(--cta-text);
      text-align:right;
    }

    .cta-kicker{
      font-size:13px;
      color: rgba(255,255,255,.7);
      margin-bottom:10px;
    }
    .cta-title{
      font-size:42px;
      line-height:1.15;
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
      box-shadow: 0 10px 20px rgba(245,166,35,.22);
      transition:.2s ease;
    }
    .cta-btn:hover{ transform: translateY(-1px); filter: brightness(1.03); }
    .cta-btn:active{ transform: translateY(0px); }
    .cta-btn:focus-visible{
      outline:none;
      box-shadow: 0 0 0 3px rgba(245,166,35,.20), 0 10px 20px rgba(245,166,35,.22);
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
    .cta-arrow.prev{ right:16px; left: auto; }
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
      padding: 26px 0 64px 0;
    }

    .cta-band .cta-container{
      display:flex;
      align-items:center;
      justify-content:center;
      gap:32px;
      color:#fff;
    }

    .cta-band-text{
      text-align:right;
    }

    .cta-band h3{
      margin:0;
      font-size:20px;
      font-weight:900;
      text-align:right;
    }

    .cta-band p{
      margin:6px 0 0 0;
      opacity:.92;
      font-size:13px;
      line-height:1.7;
      max-width: 58ch;
      text-align:right;
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
      width: fit-content;
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
      margin-top: -42px;
      padding-bottom: 38px;
    }

    .cta-cards{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap:22px;
    }

    .cta-card{
      background:#fff;
      border-radius: 10px;
      box-shadow: var(--cta-shadow);
      padding:18px 18px 16px;
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
      border-color: rgba(245,166,35,.30);
    }

    .cta-card.is-active{
      border-color: rgba(245,166,35,.55);
      box-shadow: 0 14px 30px rgba(245,166,35,.16), 0 0 0 3px rgba(245,166,35,.12);
      transform: translateY(-3px);
    }

    .cta-icon{
      width:44px; height:44px;
      margin:0 auto 10px;
      border-radius: 12px;
      display:grid;
      place-items:center;
      background: rgba(245,166,35,.15);
      border:1px solid rgba(245,166,35,.25);
      transition: .18s ease;
      color:#111;
    }

    .cta-card:hover .cta-icon,
    .cta-card.is-active .cta-icon{
      background: rgba(245,166,35,.22);
      border-color: rgba(245,166,35,.40);
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

/* تنظیم دقیق لیست منو در موبایل */
.mobile-menu {
  list-style: none;
  padding: 0;
  margin-top: 20px;
}

.mobile-menu > li {
  border-bottom: 1px solid rgba(255,255,255,.1);
  width: 100%;
}

.mobile-menu > li > a {
  display: block;
  padding: 16px 12px;
  color: #fff;
  text-decoration: none;
  font-size: 15px;
  transition: .2s;
}

/* برای منوهای خاص مثل نیکوکاری */
.mobile-menu .highlighted-menu > a {
  background: rgba(245,166,35,0.2);
  color: #f5a623;
  font-weight: bold;
}

.mobile-menu > li > a:hover {
  background: rgba(255,255,255,0.05);
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
    @media (max-width: 1200px) {
      .cta-menu { gap: 4px; }
      .cta-menu > li > a { padding: 8px 7px; font-size: 13px; }
      .cta-search input { width: 150px; }
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

      .cta-search {
        display: none !important;
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

    /* تبلت‌ها و صفحات متوسط */
    @media (max-width: 960px) {
      .cta-title {
        font-size: 32px;
      }

      .cta-band .cta-container {
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
      }

      .cta-cards {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      }
    }

    /* موبایل‌ها و صفحات بسیار کوچک */
    @media (max-width: 768px) {
      .cta-hero {
        min-height: 480px;
      }

      .cta-content {
        min-height: 480px;
        padding-top: calc(var(--cta-nav-h) + 24px);
        padding-bottom: 30px;
      }

      .cta-content .cta-container {
        justify-content: center;
      }

      .cta-text {
        width: min(560px, 92%);
        margin-inline: auto;
        text-align: center;
        padding-inline: 44px; /* فضای خالی جانبی برای جلوگیری از هم‌پوشانی دکمه‌های کنترل */
      }

      .cta-title {
        font-size: 26px;
        line-height: 1.35;
      }

      .cta-desc {
        font-size: 13.5px;
        line-height: 1.8;
      }

      .cta-arrow {
        width: 36px;
        height: 36px;
      }

      .cta-arrow.prev {
        right: 10px;
      }

      .cta-arrow.next {
        left: 10px;
      }

      .cta-dots {
        bottom: 10px;
      }
    }

    /* گوشی‌های بسیار باریک */
    @media (max-width: 420px) {
      .cta-title {
        font-size: 22px;
      }

      .cta-desc {
        font-size: 12.8px;
        line-height: 1.7;
      }

      .cta-auth {
        padding: 0 10px;
        font-size: 12px;
      }

      .cta-text {
        padding-inline: 36px;
      }
    }

    .cta-card a {
      color: var(--cta-orange-2);
      font-weight: 800;
      font-size: 13px;
    }

  </style>
</head>

<body>

  <section class="cta">

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
              <li><a class="mega-toggle" href="/courses">دوره‌ها</a></li>
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

          <form class="cta-search" action="#" method="get" role="search" aria-label="جستجو">
            <input type="search" name="q" placeholder="جستجو..." />
          </form>

          <a class="cta-auth" href="/benefactor-dashboard/" aria-label="ورود یا ثبت نام">
            ورود / ثبت‌نام
          </a>
        </div>

      </div>
    </div>

    <section class="cta-hero" aria-label="CTA Hero Slider">

      <div class="cta-slider" id="ctaSlider">
         <div class="cta-track" id="ctaTrack">
        </div>
      </div>

      <div class="cta-content">
        <div class="cta-container">
          <div class="cta-text">
            <h1 class="cta-title" id="heroTitle"></h1>
            <p class="cta-desc" id="heroDesc"></p>
            <a class="cta-btn" id="heroBtn" href="#">
              <span id="heroBtnText">مشاهده بیشتر</span>
              <span aria-hidden="true">←</span>
            </a>
          </div>
        </div>
      </div>

      <button class="cta-arrow prev" id="ctaPrev" aria-label="اسلاید قبلی" type="button">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M9.5 5L16 12l-6.5 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <button class="cta-arrow next" id="ctaNext" aria-label="اسلاید بعدی" type="button">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M14.5 5L8 12l6.5 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <div class="cta-dots" id="ctaDots" aria-label="نشانگر اسلایدها"></div>

    </section>

    <section class="cta-band" aria-label="Band">
      <div class="cta-container">
        <a class="cta-band-cta" href="/onlinedonation">❤ می‌خواهم کمک کنم </a>
        <div class="cta-band-text">
          <h3>با هم برای جهانی بهتر</h3>
          <p>
            مشارکت شما می‌تونه یک تغییر واقعی بسازه؛
            کافی‌ست مسیر درست رو انتخاب کنیم و کنار هم ادامه بدیم.
          </p>
        </div>
      </div>
    </section>

    <section class="cta-cards-wrap" aria-label="Cards">
      <div class="cta-container">
<div class="cta-cards" id="ctaCards">

  <div class="cta-card" role="link" tabindex="0" data-link="/supportprojects" aria-label="مشاهده بسته‌های نیکوکاری">
    <div class="cta-icon" aria-hidden="true">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </div>

    <h4>بسته های نیکوکاری</h4>
    <p>یک قدم کوچک شما، برای یک خانواده می‌تونه بزرگ‌ترین امید باشه.</p>
  </div>

  <div class="cta-card" role="link" tabindex="0" data-link="/single-fundraising-option" aria-label="مشاهده روش‌های حمایت">
    <div class="cta-icon" aria-hidden="true">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M12 21C12 21 4 14.5 4 9.5C4 7 6 5 8.5 5C10.2 5 11.4 5.9 12 7C12.6 5.9 13.8 5 15.5 5C18 5 20 7 20 9.5C20 14.5 12 21 12 21Z"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"/>
      </svg>
    </div>

    <h4>روش های حمایت</h4>
    <p>از راه های مختلف کمک های نقدی و غیر نقدی خود را به زندگی بیماران هدیه کنید.</p>
  </div>

  <div class="cta-card" role="link" tabindex="0" data-link="/patientintake" aria-label="تشکیل پرونده اولیه مجازی">
    <div class="cta-icon" aria-hidden="true">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z" stroke="currentColor" stroke-width="2"/>
        <path d="M4 21c1.2-4 4.4-6 8-6s6.8 2 8 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </div>

    <h4>تشکیل پرونده اولیه مجازی</h4>
    <p>برای دریافت مشاوره تخصصی و بررسی پرونده درمانی، فرم مجازی را تکمیل کنید.</p>
  </div>

</div>


      </div>
    </section>

  </section>
<nav class="mobile-menu-sidebar cta-glass" id="mobileMenu" aria-label="منوی موبایل">
</nav>
<div class="mobile-backdrop" id="mobileBackdrop"></div>

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

    // اضافه کردن باکس جستجو به منوی موبایل
    const searchForm = document.querySelector(".cta-search");
    if (searchForm) {
      const clonedSearch = searchForm.cloneNode(true);
      clonedSearch.className = "mobile-search";
      clonedSearch.style.display = "block";
      mobileMenu.appendChild(clonedSearch);
    }

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
  // راه‌اندازی هدر چسبنده با قابلیت مخفی شدن در اسکرول به پایین و آشکار شدن در هاور
  (function() {
    const topbar = document.querySelector(".cta-topbar");
    if (!topbar) return;

    let lastScrollY = window.scrollY;

    window.addEventListener("scroll", function() {
      const currentScrollY = window.scrollY;

      // ۱. اگر اسکرول بیشتر از 50 پیکسل بود هدر جدا شود
      if (currentScrollY > 50) {
        topbar.classList.add("scrolled");
      } else {
        topbar.classList.remove("scrolled");
      }

      // ۲. مخفی شدن در اسکرول به پایین و ظاهر شدن در اسکرول به بالا
      if (currentScrollY > lastScrollY && currentScrollY > 150) {
        topbar.classList.add("scroll-down");
      } else {
        topbar.classList.remove("scroll-down");
      }
      lastScrollY = currentScrollY;
    });

    // ۳. آشکار شدن هدر در زمان نزدیک شدن موس به بالای صفحه (هاور بخش بالایی)
    window.addEventListener("mousemove", function(e) {
      if (e.clientY <= 30) {
        topbar.classList.add("force-show");
      } else if (e.clientY > 100) {
        topbar.classList.remove("force-show");
      }
    });

    topbar.addEventListener("mouseenter", function() {
      topbar.classList.add("force-show");
    });
    topbar.addEventListener("mouseleave", function() {
      topbar.classList.remove("force-show");
    });
  })();
  });
})();

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


</body>
</html>

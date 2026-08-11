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
      --cta-container: 1360px;
      --cta-nav-h: 86px;

      --cta-shadow: 0 10px 22px rgba(0,0,0,.14);
      --cta-shadow-soft: 0 8px 18px rgba(0,0,0,.12);
      --cta-radius: 14px;
      --cta-edge-gap: 16px;
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
    padding-inline:28px;
    }

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

    /* ===== TOP BAR ===== */
    .cta-topbar{
      position:fixed;
      top:0; left:0; right:0;
      height: var(--cta-nav-h);
      z-index:99999;
      display:flex;
      align-items:center;
      overflow: visible;
      padding-inline: 0;
      background: var(--cta-orange);
      box-shadow: 0 2px 15px rgba(0,0,0,0.1);
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), top 0.3s ease, height 0.3s ease;
    }

    /* Detached look when scrolled */
    .cta-topbar.scrolled {
      top: 0;
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
      background: transparent;
      border: none;
      border-radius: 0;
      box-shadow: none;
      padding: 8px 16px;
      width: 100%;
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
  /* Removed glass effect */
}
    /* ✅ منو افقی (حل عمودی شدن) */
    .cta-menu{
      list-style: none;
      margin: 0;
      padding: 0;
      display:flex;
      align-items:center;
      gap: 4px;
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
      padding:8px 14px;
      border-radius:10px;
      transition: .2s ease;
      outline: none;
      font-size: 14.5px;
      font-weight: 700;
      color: #111111;
    }

    .cta-menu > li > a:hover{
      background: rgba(0,0,0,.06);
    }

    .cta-menu > li > a:focus-visible{
      box-shadow: 0 0 0 3px rgba(0,0,0,.15);
      background: rgba(0,0,0,.06);
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
      border: 1px solid rgba(0,0,0,.15);
      background: rgba(255,255,255,.2);
      color: #111111;
      padding: 0 12px;
      outline: none;
      transition: .2s ease;
    }

.cta-glass {
  backdrop-filter: blur(6px);
  box-shadow: var(--cta-shadow-soft);
}
    .cta-search input::placeholder{
      color: rgba(0,0,0,.65);
    }

    .cta-search input:focus{
      border-color: rgba(0,0,0,.3);
      box-shadow: 0 0 0 2px rgba(0,0,0,.05);
      background: rgba(255,255,255,.3);
    }

    .cta-donate {
      height: 38px;
      border-radius: 10px;
      border: none;
      background: linear-gradient(135deg, #e53935, #c62828);
      color: #ffffff !important;
      padding: 0 16px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      white-space: nowrap;
      font-weight: 800;
      box-shadow: 0 4px 12px rgba(198, 40, 40, 0.3);
    }

    .cta-donate:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(198, 40, 40, 0.4);
      background: linear-gradient(135deg, #c62828, #b71c1c);
    }

    .cta-donate:active {
      transform: translateY(1px);
      box-shadow: 0 2px 8px rgba(198, 40, 40, 0.2);
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
  transition: opacity .3s ease;
}

/* فقط موبایل و تبلت */
@media (max-width: 1024px){
  .menu-icon{ display:flex; }
}

    /* ===== MOBILE MENU REDESIGN ===== */
    .mobile-menu-sidebar {
      position: fixed;
      top: 0;
      right: -100%;
      width: min(340px, 88vw);
      height: 100vh;
      height: 100dvh;
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(24px) saturate(180%);
      -webkit-backdrop-filter: blur(24px) saturate(180%);
      border-left: 1px solid rgba(245, 166, 35, 0.25);
      box-shadow: -12px 0 36px rgba(0, 0, 0, 0.15);
      z-index: 99999;
      display: flex;
      flex-direction: column;
      padding: 0;
      overflow: hidden;
      visibility: hidden;
      transition: right 0.38s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.38s;
      direction: rtl;
    }

    .mobile-menu-sidebar.open {
      right: 0;
      visibility: visible;
    }

    /* Drawer Header - Aligned thickness with main navbar */
    .mobile-menu-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: var(--cta-nav-h, 56px);
      min-height: 56px;
      padding: 0 16px;
      background: linear-gradient(135deg, rgba(245, 166, 35, 0.12), rgba(8, 153, 169, 0.06));
      border-bottom: 1px solid rgba(0, 0, 0, 0.07);
      flex-shrink: 0;
      box-sizing: border-box;
    }

    .mobile-menu-brand {
      display: flex;
      align-items: center;
    }

    .mobile-menu-brand img {
      height: 32px;
      width: auto;
      object-fit: contain;
    }

    .mobile-menu-close {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #f1f5f9;
      border: 1px solid rgba(0, 0, 0, 0.06);
      color: #475569;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
      outline: none;
      padding: 0;
    }

    .mobile-menu-close:hover {
      background: #e2e8f0;
      color: #0f172a;
      transform: scale(1.05);
    }

    .mobile-menu-close:active {
      transform: scale(0.95);
    }

    /* Drawer Action Bar */
    .mobile-menu-actions {
      display: flex;
      gap: 10px;
      padding: 14px 18px 8px 18px;
      flex-shrink: 0;
    }

    .mobile-action-donate {
      flex: 1;
      height: 42px;
      border-radius: 12px;
      background: linear-gradient(135deg, #e53935, #c62828);
      color: #ffffff !important;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      font-weight: 800;
      font-size: 13.5px;
      box-shadow: 0 4px 14px rgba(198, 40, 40, 0.28);
      text-decoration: none;
      transition: all 0.25s ease;
    }

    .mobile-action-donate:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(198, 40, 40, 0.38);
    }

    .mobile-action-auth {
      flex: 1;
      height: 42px;
      border-radius: 12px;
      background: linear-gradient(135deg, #0899A9, #067d8a);
      color: #ffffff !important;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      font-weight: 700;
      font-size: 13.5px;
      box-shadow: 0 4px 14px rgba(8, 153, 169, 0.28);
      text-decoration: none;
      transition: all 0.25s ease;
    }

    .mobile-action-auth:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(8, 153, 169, 0.38);
    }

    /* Drawer Navigation Body */
    .mobile-menu-container {
      flex: 1;
      overflow-y: auto;
      padding: 10px 14px 20px 14px;
      scrollbar-width: thin;
      scrollbar-color: rgba(0, 0, 0, 0.15) transparent;
    }

    .mobile-menu-container::-webkit-scrollbar {
      width: 4px;
    }

    .mobile-menu-container::-webkit-scrollbar-thumb {
      background: rgba(0, 0, 0, 0.15);
      border-radius: 4px;
    }

    .mobile-menu {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .mobile-menu > li {
      width: 100%;
      border-radius: 12px;
      transition: background 0.2s ease;
    }

    .mobile-menu a,
    .mobile-menu > li > a,
    .mobile-menu li a {
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      padding: 12px 14px !important;
      color: #1e293b !important;
      text-decoration: none !important;
      font-size: 14.5px !important;
      font-weight: 700 !important;
      border-radius: 10px !important;
      transition: all 0.2s ease !important;
    }

    .mobile-menu > li > a:hover {
      background: rgba(245, 166, 35, 0.12) !important;
      color: #0899A9 !important;
    }

    .mobile-menu > li > a:active {
      background: rgba(245, 166, 35, 0.18) !important;
    }

    /* Highlighted / Special Menu item */
    .mobile-menu .highlighted-menu > a {
      background: rgba(245, 166, 35, 0.15);
      color: #d97706;
      font-weight: 800;
    }

    /* Mega Menu Accordion in Mobile Drawer */
    .mobile-menu .mega-menu-content {
      display: none;
      position: static;
      background: rgba(248, 250, 252, 0.85);
      border: 1px solid rgba(0, 0, 0, 0.06);
      border-radius: 12px;
      margin: 4px 0 8px 0;
      padding: 14px 16px;
      opacity: 1 !important;
      visibility: visible !important;
      transform: none !important;
      transition: none !important;
      box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.02);
    }

    .mobile-menu .mega-menu-content.active {
      display: block;
      animation: mobileSubMenuFade 0.25s ease forwards;
    }

    @keyframes mobileSubMenuFade {
      from { opacity: 0; transform: translateY(-6px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .mobile-menu .mega-row {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .mobile-menu .mega-col {
      border: none;
      padding: 0;
    }

    .mobile-menu .mega-col h6 {
      color: #0899A9;
      font-size: 13px;
      font-weight: 800;
      margin: 10px 0 6px 0;
      padding-bottom: 4px;
      border-bottom: 1px dashed rgba(8, 153, 169, 0.2);
    }

    .mobile-menu .mega-col:first-child h6 {
      margin-top: 0;
    }

    .mobile-menu .mega-col a {
      font-size: 13.5px;
      font-weight: 600;
      padding: 8px 10px;
      color: #475569;
      display: block;
      border-radius: 8px;
      text-decoration: none;
      transition: all 0.2s ease;
      margin-bottom: 2px;
    }

    .mobile-menu .mega-col a:hover {
      background: #ffffff;
      color: #f5a623;
      padding-right: 14px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    /* Accordion Toggle Indicator */
    .mobile-menu .mega-toggle {
      position: relative;
      display: flex !important;
      align-items: center;
      justify-content: space-between;
    }

    .mobile-menu .mega-toggle::after {
      content: "";
      display: inline-block;
      width: 7px;
      height: 7px;
      border-left: 2px solid #64748b;
      border-bottom: 2px solid #64748b;
      transform: rotate(-45deg);
      transition: transform 0.25s ease, border-color 0.25s ease;
      flex-shrink: 0;
      margin-left: 4px;
    }

    .mobile-menu .mega-toggle.open::after {
      transform: rotate(135deg);
      border-color: #0899A9;
    }

    .mobile-menu .mega-toggle.open {
      color: #0899A9;
      background: rgba(8, 153, 169, 0.08);
    }

    /* Drawer Footer */
    .mobile-menu-footer {
      padding: 14px 18px;
      background: rgba(241, 245, 249, 0.85);
      border-top: 1px solid rgba(0, 0, 0, 0.06);
      flex-shrink: 0;
      direction: rtl;
    }

    .mobile-footer-info {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12.5px;
      font-weight: 700;
      color: #334155;
    }

    .mobile-footer-info svg {
      color: #0899A9;
    }

    .mobile-footer-tagline {
      font-size: 11px;
      color: #94a3b8;
      margin-top: 3px;
      font-weight: 500;
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
      font-size:52px;
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
      padding-bottom: 52px;
    }

    .cta-cards{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap:32px;
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
        font-size: 32px;
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
        font-size: 26px;
      }

      .cta-desc {
        font-size: 12.8px;
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
        padding-inline: 36px;
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
    [hidden]{display:none !important}

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

              <li><a href="/branches.php">شعب</a></li>
              <li><a href="/news.php">اخبار</a></li>
              <li><a href="/macsapedia.php">مکساپدیا</a></li>
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
                <a role="menuitem" href="/benefactor-dashboard/">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z" stroke="currentColor" stroke-width="2"/><path d="M4 20c1.2-3.5 4.2-5.5 8-5.5s6.8 2 8 5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  پروفایل کاربری
                </a>
                <a role="menuitem" href="/benefactor-dashboard/">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/><path d="M3 10h18" stroke="currentColor" stroke-width="2"/></svg>
                  پرداخت‌های من
                </a>
                <a role="menuitem" href="/onlinedonation">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21C12 21 4 14.5 4 9.5C4 7 6 5 8.5 5C10.2 5 11.4 5.9 12 7C12.6 5.9 13.8 5 15.5 5C18 5 20 7 20 9.5C20 14.5 12 21 12 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  کمک آنلاین
                </a>

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
<nav class="mobile-menu-sidebar" id="mobileMenu" aria-label="منوی موبایل">
  <div class="mobile-menu-header">
    <div class="mobile-menu-brand">
      <img src="/dashboard/components/header/images/1.png" alt="مکسا">
    </div>
    <button type="button" class="mobile-menu-close" id="mobileMenuClose" aria-label="بستن منو">
      <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
  </div>

  <div class="mobile-menu-actions">
    <a class="mobile-action-donate" href="/onlinedonation">
      <svg viewBox="0 0 24 24" fill="none" width="17" height="17"><path d="M12 21C12 21 4 14.5 4 9.5C4 7 6 5 8.5 5C10.2 5 11.4 5.9 12 7C12.6 5.9 13.8 5 15.5 5C18 5 20 7 20 9.5C20 14.5 12 21 12 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      کمک آنلاین
    </a>
    <a class="mobile-action-auth js-mobile-login" href="/benefactor-dashboard/">
      <svg viewBox="0 0 24 24" fill="none" width="17" height="17"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z" stroke="currentColor" stroke-width="2"/><path d="M4 20c1.2-3.5 4.2-5.5 8-5.5s6.8 2 8 5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      ورود / ثبت‌نام
    </a>
  </div>

  <div class="mobile-menu-container" id="mobileMenuContainer"></div>

  <div class="mobile-menu-footer">
    <div class="mobile-footer-info">
      <svg viewBox="0 0 24 24" fill="none" width="16" height="16"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2"/></svg>
      <span>پشتیبانی: ۰۲۱-۵۴۱۶۴۰۰۰</span>
    </div>
    <div class="mobile-footer-tagline">مؤسسه خیریه مولی‌الموحدین - مراقبت‌های تسکینی سرطان</div>
  </div>
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
  const closeBtn = document.getElementById("mobileMenuClose");
  const menu = document.getElementById("mobileMenu");
  const backdrop = document.getElementById("mobileBackdrop");

  if(!toggleBtn || !menu) return;

  function openMenu(){
    menu.classList.add("open");
    if(backdrop) backdrop.classList.add("show");
    toggleBtn.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  function closeMenu(){
    menu.classList.remove("open");
    if(backdrop) backdrop.classList.remove("show");
    toggleBtn.classList.remove("active");
    document.body.style.overflow = "";
  }

  toggleBtn.addEventListener("click", function(e){
    e.stopPropagation();
    menu.classList.contains("open") ? closeMenu() : openMenu();
  });

  if(closeBtn) closeBtn.addEventListener("click", closeMenu);
  if(backdrop) backdrop.addEventListener("click", closeMenu);

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
    const container = document.getElementById("mobileMenuContainer");

    if(!desktopMenu || !container) return;

    // کپی کامل منو در کانتینر مخصوص اسکرول سایدبار
    const clonedMenu = desktopMenu.cloneNode(true);
    clonedMenu.classList.remove("cta-menu");
    clonedMenu.classList.add("mobile-menu");
    container.appendChild(clonedMenu);

    // تبدیل مگا منو به آکاردئون
    container.querySelectorAll(".mega-menu").forEach(function(item){
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

      var mobLogin = document.querySelector('.js-mobile-login');
      if(mobLogin){
        mobLogin.href = '/benefactor-dashboard/';
        mobLogin.innerHTML = '<svg viewBox="0 0 24 24" fill="none" width="17" height="17"><rect x="3" y="3" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="12" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="3" y="16" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="2"/></svg> داشبورد من';
      }

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
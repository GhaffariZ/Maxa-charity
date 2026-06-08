
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>پنل حامیان موسسه نیکوکاری مکسا</title>

  <!-- آیکون -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

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
      --primary:#0ea5a4;
      --primary-dark:#0b7f7e;
      --primary-light:#dff8f7;
      --secondary:#14b8a6;
      --bg:#f5f8fa;
      --card:#ffffff;
      --text:#1f2937;
      --muted:#6b7280;
      --border:#e5e7eb;
      --shadow:0 10px 30px rgba(15, 23, 42, .08);
      --shadow-hover:0 16px 40px rgba(14,165,164,.16);
      --gray:#94a3b8;
      --success:#10b981;
      --warning:#f59e0b;
      --danger:#ef4444;
      --bronze:#cd7f32;
      --silver:#b0b7c3;
      --gold:#d4af37;
      --diamond:#56ccf2;
      --sidebar:#ffffff;
      --sidebar-text:#334155;
      --sidebar-active:#e6fffb;
    }

    *{
      margin:0;
      padding:0;
      box-sizing:border-box;
      font-family:'Vazirmatn', sans-serif;
    }

    body{
      background:linear-gradient(135deg,#f8fbfc 0%, #eef7f8 50%, #f5f8fa 100%);
      color:var(--text);
      min-height:100vh;
      overflow-x:hidden;
    }

    a{
      text-decoration:none;
      color:inherit;
    }

    .app{
      display:flex;
      min-height:100vh;
    }

    /* Sidebar */
    .sidebar{
      width:290px;
      background:var(--sidebar);
      border-left:1px solid var(--border);
      box-shadow:var(--shadow);
      padding:24px 18px;
      position:sticky;
      top:0;
      height:100vh;
      overflow-y:auto;
      z-index:10;
    }

    .brand{
      display:flex;
      align-items:center;
      gap:14px;
      margin-bottom:30px;
      padding:10px;
      border-radius:20px;
      background:linear-gradient(135deg, rgba(14,165,164,.08), rgba(20,184,166,.12));
      animation:fadeInDown .7s ease;
    }

    .brand-logo{
      width:56px;
      height:56px;
      border-radius:18px;
      background:linear-gradient(135deg,var(--primary),#34d399);
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-size:24px;
      box-shadow:0 10px 20px rgba(14,165,164,.25);
    }

    .brand-text h2{
      font-size:17px;
      font-weight:800;
      color:var(--text);
      margin-bottom:4px;
    }

    .brand-text p{
      font-size:12px;
      color:var(--muted);
    }

    .menu-title{
      color:var(--gray);
      font-size:12px;
      font-weight:700;
      margin:18px 8px 10px;
    }

    .nav-menu{
      display:flex;
      flex-direction:column;
      gap:8px;
    }

    .nav-item{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:14px 16px;
      border-radius:16px;
      color:var(--sidebar-text);
      transition:.3s ease;
      cursor:pointer;
      position:relative;
      overflow:hidden;
    }

    .nav-item::before{
      content:"";
      position:absolute;
      inset:0;
      background:linear-gradient(90deg, rgba(14,165,164,.08), transparent);
      transform:translateX(100%);
      transition:.4s ease;
    }

    .nav-item:hover::before{
      transform:translateX(0);
    }

    .nav-item:hover{
      background:#f8ffff;
      transform:translateX(-3px);
    }

    .nav-item.active{
      background:var(--sidebar-active);
      color:var(--primary-dark);
      font-weight:700;
      border:1px solid rgba(14,165,164,.15);
    }

    .nav-item .right{
      display:flex;
      align-items:center;
      gap:12px;
      z-index:1;
    }

    .nav-item i{
      font-size:18px;
      width:22px;
      text-align:center;
    }

    .nav-badge{
      font-size:11px;
      padding:4px 8px;
      border-radius:30px;
      background:rgba(20,184,166,.12);
      color:var(--primary-dark);
      z-index:1;
    }

    /* Main */
    .main{
      flex:1;
      padding:28px;
    }

    .topbar{
      display:flex;
      align-items:center;
      justify-content:space-between;
      margin-bottom:24px;
      gap:20px;
      animation:fadeIn .7s ease;
    }

    .welcome-box h1{
      font-size:28px;
      font-weight:800;
      margin-bottom:8px;
    }

    .welcome-box p{
      color:var(--muted);
      font-size:14px;
    }

    .user-box{
      display:flex;
      align-items:center;
      gap:14px;
      background:rgba(255,255,255,.7);
      backdrop-filter:blur(10px);
      padding:12px 16px;
      border-radius:20px;
      box-shadow:var(--shadow);
      border:1px solid rgba(255,255,255,.5);
    }

    .avatar{
      width:54px;
      height:54px;
      border-radius:50%;
      background:linear-gradient(135deg,var(--primary),#67e8f9);
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-weight:800;
      font-size:20px;
      box-shadow:0 8px 20px rgba(14,165,164,.25);
    }

    .user-meta h3{
      font-size:16px;
      margin-bottom:4px;
      display:flex;
      align-items:center;
      gap:10px;
    }

    .user-meta span{
      font-size:13px;
      color:var(--muted);
    }

    .medal{
      display:inline-flex;
      align-items:center;
      gap:6px;
      font-size:12px;
      padding:5px 10px;
      border-radius:30px;
      color:#fff;
    }

    .medal.bronze{background:var(--bronze);}
    .medal.silver{background:var(--silver);}
    .medal.gold{background:var(--gold);}
    .medal.diamond{
      background:linear-gradient(135deg,#36d1dc,#5b86e5);
    }

    /* Daily quote */
    .quote-card{
      background:linear-gradient(135deg,#0ea5a4,#14b8a6,#2dd4bf);
      color:#fff;
      border-radius:28px;
      padding:24px;
      box-shadow:var(--shadow-hover);
      position:relative;
      overflow:hidden;
      margin-bottom:24px;
      animation:floatCard 4s ease-in-out infinite alternate;
    }

    .quote-card::before,
    .quote-card::after{
      content:"";
      position:absolute;
      border-radius:50%;
      background:rgba(255,255,255,.12);
    }

    .quote-card::before{
      width:220px;
      height:220px;
      top:-100px;
      left:-50px;
    }

    .quote-card::after{
      width:160px;
      height:160px;
      bottom:-80px;
      right:-30px;
    }

    .quote-card h2{
      font-size:20px;
      margin-bottom:10px;
      position:relative;
      z-index:1;
    }

    .quote-card p{
      font-size:15px;
      line-height:2;
      max-width:900px;
      position:relative;
      z-index:1;
    }

    /* Banner */
    .hero-slider{
      position:relative;
      border-radius:28px;
      overflow:hidden;
      height:290px;
      box-shadow:var(--shadow);
      margin-bottom:28px;
      background:#d9f4f2;
    }

    .slides{
      display:flex;
      width:300%;
      height:100%;
      transition:transform .8s ease-in-out;
    }

    .slide{
      width:100%;
      flex-shrink:0;
      position:relative;
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:36px;
      color:#fff;
    }

    .slide:nth-child(1){
      background:linear-gradient(135deg,rgba(14,165,164,.88),rgba(11,127,126,.85)),
      url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=1400&q=80') center/cover;
    }

    .slide:nth-child(2){
      background:linear-gradient(135deg,rgba(20,184,166,.88),rgba(17,94,89,.85)),
      url('https://images.unsplash.com/photo-1469571486292-b53601020f40?auto=format&fit=crop&w=1400&q=80') center/cover;
    }

    .slide:nth-child(3){
      background:linear-gradient(135deg,rgba(15,118,110,.88),rgba(45,212,191,.8)),
      url('https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?auto=format&fit=crop&w=1400&q=80') center/cover;
    }

    .slide-content{
      max-width:650px;
      animation:fadeInUp .8s ease;
    }

    .slide-content h2{
      font-size:30px;
      margin-bottom:12px;
      font-weight:800;
    }

    .slide-content p{
      font-size:15px;
      line-height:2;
      margin-bottom:20px;
      opacity:.95;
    }

    .slide-btn{
      background:#fff;
      color:var(--primary-dark);
      border:none;
      border-radius:16px;
      padding:13px 22px;
      font-weight:700;
      cursor:pointer;
      transition:.3s;
      box-shadow:0 8px 20px rgba(0,0,0,.12);
    }

    .slide-btn:hover{
      transform:translateY(-3px) scale(1.02);
    }

    .slider-dots{
      position:absolute;
      bottom:16px;
      right:24px;
      display:flex;
      gap:8px;
      z-index:2;
    }

    .dot{
      width:11px;
      height:11px;
      border-radius:50%;
      background:rgba(255,255,255,.45);
      cursor:pointer;
      transition:.3s;
    }

    .dot.active{
      width:30px;
      border-radius:20px;
      background:#fff;
    }

    /* stats */
    .stats-grid{
      display:grid;
      grid-template-columns:repeat(4,1fr);
      gap:18px;
      margin-bottom:28px;
    }

    .stat-card{
      background:var(--card);
      border-radius:24px;
      padding:20px;
      box-shadow:var(--shadow);
      border:1px solid rgba(255,255,255,.8);
      transition:.3s ease;
      position:relative;
      overflow:hidden;
    }

    .stat-card:hover{
      transform:translateY(-6px);
      box-shadow:var(--shadow-hover);
    }

    .stat-card::after{
      content:"";
      position:absolute;
      width:120px;
      height:120px;
      background:radial-gradient(circle, rgba(20,184,166,.12), transparent 70%);
      left:-30px;
      bottom:-30px;
    }

    .stat-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      margin-bottom:18px;
    }

    .stat-icon{
      width:52px;
      height:52px;
      border-radius:18px;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-size:20px;
    }

    .bg1{background:linear-gradient(135deg,#0ea5a4,#2dd4bf);}
    .bg2{background:linear-gradient(135deg,#64748b,#94a3b8);}
    .bg3{background:linear-gradient(135deg,#10b981,#34d399);}
    .bg4{background:linear-gradient(135deg,#0891b2,#67e8f9);}

    .stat-card h3{
      font-size:13px;
      color:var(--muted);
      margin-bottom:8px;
      font-weight:600;
    }

    .stat-card .value{
      font-size:27px;
      font-weight:800;
      margin-bottom:6px;
    }

    .stat-card .sub{
      font-size:12px;
      color:var(--muted);
    }

    /* content section */
    .content-grid{
      display:grid;
      grid-template-columns:1.5fr 1fr;
      gap:20px;
      margin-bottom:28px;
    }

    .panel{
      background:#fff;
      border-radius:26px;
      box-shadow:var(--shadow);
      padding:22px;
      border:1px solid var(--border);
    }

    .panel-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      margin-bottom:18px;
    }

    .panel-header h3{
      font-size:18px;
      font-weight:800;
    }

    .panel-header a{
      color:var(--primary-dark);
      font-size:13px;
      font-weight:700;
    }

    .activity-list{
      display:flex;
      flex-direction:column;
      gap:14px;
    }

    .activity-item{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:15px;
      padding:14px;
      border-radius:18px;
      background:#f8fbfc;
      transition:.3s;
    }

    .activity-item:hover{
      background:#eefcfb;
      transform:translateX(-3px);
    }

    .activity-right{
      display:flex;
      gap:12px;
    }

    .activity-icon{
      width:46px;
      height:46px;
      border-radius:16px;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      background:linear-gradient(135deg,var(--primary),#2dd4bf);
    }

    .activity-content h4{
      font-size:15px;
      margin-bottom:6px;
    }

    .activity-content p{
      font-size:13px;
      color:var(--muted);
      line-height:1.9;
    }

    .amount{
      white-space:nowrap;
      color:var(--success);
      font-weight:800;
      font-size:14px;
    }

    .impact-box{
      display:flex;
      flex-direction:column;
      gap:14px;
    }

    .impact-item{
      background:linear-gradient(135deg,#f0fdfa,#f8fafc);
      border:1px solid #d1fae5;
      padding:16px;
      border-radius:18px;
    }

    .impact-item h4{
      margin-bottom:8px;
      color:var(--primary-dark);
      font-size:15px;
    }

    .impact-item p{
      font-size:13px;
      color:var(--muted);
      line-height:2;
    }

    .progress-wrap{
      margin-top:10px;
    }

    .progress{
      height:10px;
      background:#e5e7eb;
      border-radius:999px;
      overflow:hidden;
    }

    .progress-bar{
      height:100%;
      background:linear-gradient(90deg,var(--primary),#34d399);
      border-radius:999px;
      animation:growBar 1.5s ease;
    }

    /* Modal */
    .modal{
      position:fixed;
      inset:0;
      background:rgba(15,23,42,.45);
      display:none;
      align-items:center;
      justify-content:center;
      z-index:1000;
      padding:20px;
      backdrop-filter:blur(4px);
    }

    .modal.active{
      display:flex;
      animation:fadeIn .3s ease;
    }

    .modal-content{
      width:min(1000px, 100%);
      max-height:90vh;
      overflow:auto;
      background:#fff;
      border-radius:28px;
      box-shadow:0 30px 80px rgba(0,0,0,.18);
      padding:26px;
      position:relative;
      animation:scaleIn .35s ease;
    }

    .modal-lg{
      width:min(1100px, 100%);
    }

    .close-btn{
      position:absolute;
      left:18px;
      top:18px;
      width:42px;
      height:42px;
      border:none;
      border-radius:50%;
      background:#f3f4f6;
      cursor:pointer;
      font-size:18px;
      transition:.3s;
    }

    .close-btn:hover{
      background:#e5e7eb;
      transform:rotate(90deg);
    }

    .modal-title{
      font-size:24px;
      font-weight:800;
      margin-bottom:10px;
      color:var(--text);
    }

    .modal-sub{
      color:var(--muted);
      margin-bottom:24px;
      font-size:14px;
    }

    table{
      width:100%;
      border-collapse:collapse;
      overflow:hidden;
      border-radius:18px;
      background:#fff;
      margin-bottom:20px;
    }

    table thead{
      background:linear-gradient(90deg,var(--primary),#2dd4bf);
      color:#fff;
    }

    table th, table td{
      padding:14px 12px;
      text-align:center;
      border-bottom:1px solid var(--border);
      font-size:14px;
    }

    table tbody tr:hover{
      background:#f8fffe;
    }

    .receipt-card{
      background:#f8fbfc;
      border:1px dashed #cbd5e1;
      border-radius:20px;
      padding:18px;
    }

    .receipt-preview{
      height:220px;
      border-radius:18px;
      background:linear-gradient(135deg,#f1f5f9,#e2e8f0);
      display:flex;
      align-items:center;
      justify-content:center;
      color:var(--muted);
      margin-top:12px;
      font-size:14px;
    }

    .campaign-cards{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
      gap:18px;
    }

    .campaign-card{
      border-radius:24px;
      overflow:hidden;
      background:#fff;
      border:1px solid var(--border);
      box-shadow:var(--shadow);
      transition:.3s;
    }

    .campaign-card:hover{
      transform:translateY(-6px);
      box-shadow:var(--shadow-hover);
    }

    .campaign-image{
      height:160px;
      background-size:cover;
      background-position:center;
      position:relative;
    }

    .campaign-tag{
      position:absolute;
      top:14px;
      right:14px;
      background:rgba(255,255,255,.9);
      color:var(--primary-dark);
      font-size:12px;
      font-weight:700;
      padding:6px 10px;
      border-radius:30px;
    }

    .campaign-body{
      padding:16px;
    }

    .campaign-body h4{
      margin-bottom:8px;
      font-size:16px;
    }

    .campaign-body p{
      color:var(--muted);
      font-size:13px;
      line-height:1.9;
      margin-bottom:12px;
    }

    .campaign-footer{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
    }

    .btn{
      border:none;
      cursor:pointer;
      padding:12px 18px;
      border-radius:14px;
      font-weight:700;
      transition:.3s ease;
    }

    .btn-primary{
      background:linear-gradient(135deg,var(--primary),#2dd4bf);
      color:#fff;
    }

    .btn-primary:hover{
      transform:translateY(-2px);
      box-shadow:0 10px 24px rgba(14,165,164,.22);
    }

    .btn-secondary{
      background:#eef2f7;
      color:var(--text);
    }

    .btn-secondary:hover{
      background:#e5e7eb;
    }

    .form-grid{
      display:grid;
      grid-template-columns:repeat(2,1fr);
      gap:18px;
    }

    .form-group{
      display:flex;
      flex-direction:column;
      gap:8px;
    }

    .form-group.full{
      grid-column:1 / -1;
    }

    label{
      font-size:13px;
      font-weight:700;
      color:#475569;
    }

    input, select, textarea{
      border:1px solid var(--border);
      border-radius:16px;
      padding:13px 14px;
      font-size:14px;
      outline:none;
      transition:.3s;
      background:#fff;
    }

    input:focus, select:focus, textarea:focus{
      border-color:var(--primary);
      box-shadow:0 0 0 4px rgba(14,165,164,.1);
    }

    textarea{
      min-height:110px;
      resize:vertical;
    }

    .profile-upload{
      display:flex;
      align-items:center;
      gap:20px;
      margin-bottom:20px;
      padding:18px;
      background:#f8fbfc;
      border-radius:20px;
    }

    .profile-pic{
      width:88px;
      height:88px;
      border-radius:50%;
      background:linear-gradient(135deg,var(--primary),#67e8f9);
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-size:28px;
      font-weight:800;
    }

    .impact-summary{
      display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:16px;
      margin-bottom:20px;
    }

    .impact-stat{
      background:linear-gradient(135deg,#f0fdfa,#ffffff);
      border:1px solid #d1fae5;
      border-radius:22px;
      padding:20px;
      text-align:center;
    }

    .impact-stat h4{
      color:var(--muted);
      font-size:13px;
      margin-bottom:10px;
    }

    .impact-stat .big{
      font-size:28px;
      font-weight:800;
      color:var(--primary-dark);
    }

    .upload-box{
      border:2px dashed #cbd5e1;
      border-radius:24px;
      padding:30px;
      text-align:center;
      background:#f8fbfc;
      transition:.3s;
    }

    .upload-box:hover{
      border-color:var(--primary);
      background:#f4fffe;
    }

    .medal-guide{
      display:grid;
      grid-template-columns:repeat(2,1fr);
      gap:18px;
    }

    .guide-card{
      border-radius:22px;
      padding:20px;
      color:#fff;
      box-shadow:var(--shadow);
    }

    .guide-card h4{
      margin-bottom:10px;
      font-size:18px;
    }

    .guide-card p{
      line-height:2;
      font-size:14px;
      opacity:.96;
    }

    .guide-bronze{background:linear-gradient(135deg,#b87333,#cd7f32);}
    .guide-silver{background:linear-gradient(135deg,#8e9eab,#b0b7c3);}
    .guide-gold{background:linear-gradient(135deg,#c9a227,#d4af37);}
    .guide-diamond{background:linear-gradient(135deg,#36d1dc,#5b86e5);}

    /* Responsive */
    @media(max-width:1200px){
      .stats-grid{grid-template-columns:repeat(2,1fr);}
      .content-grid{grid-template-columns:1fr;}
    }

    @media(max-width:992px){
      .app{flex-direction:column;}
      .sidebar{
        width:100%;
        height:auto;
        position:relative;
        border-left:none;
        border-bottom:1px solid var(--border);
      }
    }

    @media(max-width:768px){
      .main{padding:18px;}
      .topbar{flex-direction:column; align-items:flex-start;}
      .stats-grid{grid-template-columns:1fr;}
      .form-grid{grid-template-columns:1fr;}
      .impact-summary{grid-template-columns:1fr;}
      .medal-guide{grid-template-columns:1fr;}
      .slide{padding:24px;}
      .slide-content h2{font-size:24px;}
      .hero-slider{height:340px;}
    }

    @media(max-width:480px){
      .main{padding:12px;}
      .slide{padding:18px;}
      .slide-content h2{font-size:20px;}
      .hero-slider{height:280px;}
    }

    /* Animations */
    @keyframes fadeIn{
      from{opacity:0}
      to{opacity:1}
    }

    @keyframes fadeInDown{
      from{opacity:0;transform:translateY(-20px)}
      to{opacity:1;transform:translateY(0)}
    }

    @keyframes fadeInUp{
      from{opacity:0;transform:translateY(30px)}
      to{opacity:1;transform:translateY(0)}
    }

    @keyframes scaleIn{
      from{opacity:0; transform:scale(.95)}
      to{opacity:1; transform:scale(1)}
    }

    @keyframes floatCard{
      from{transform:translateY(0)}
      to{transform:translateY(-4px)}
    }

    @keyframes growBar{
      from{width:0}
    }
  </style>
</head>
<body>

  <div class="app">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="brand">
        <div class="brand-logo">
          <i class="fa-solid fa-heart-circle-plus"></i>
        </div>
        <div class="brand-text">
          <h2>موسسه نیکوکاری مکسا</h2>
          <p>پنل اختصاصی حامیان و خیرین</p>
        </div>
      </div>

      <div class="menu-title">منوی اصلی</div>
      <nav class="nav-menu">
        <div class="nav-item active">
          <div class="right">
            <i class="fa-solid fa-chart-line"></i>
            <span>داشبورد</span>
          </div>
        </div>

        <div class="nav-item" onclick="goToPayment()">
          <div class="right">
            <i class="fa-solid fa-credit-card"></i>
            <span>پرداخت آنلاین</span>
          </div>
          <span class="nav-badge">درگاه</span>
        </div>

        <div class="nav-item" onclick="openModal('paymentsModal')">
          <div class="right">
            <i class="fa-solid fa-receipt"></i>
            <span>لیست پرداخت‌ها</span>
          </div>
          <span class="nav-badge">سوابق</span>
        </div>

        <div class="nav-item" onclick="openModal('campaignsModal')">
          <div class="right">
            <i class="fa-solid fa-bullhorn"></i>
            <span>کمپین‌های پیشنهادی</span>
          </div>
          <span class="nav-badge">جدید</span>
        </div>

        <div class="nav-item" onclick="openModal('profileModal')">
          <div class="right">
            <i class="fa-solid fa-user-gear"></i>
            <span>پروفایل کاربری</span>
          </div>
        </div>

        <div class="nav-item" onclick="openModal('impactModal')">
          <div class="right">
            <i class="fa-solid fa-hand-holding-heart"></i>
            <span>اثرات کمک‌های من</span>
          </div>
        </div>

        <div class="nav-item" onclick="openModal('medalGuideModal')">
          <div class="right">
            <i class="fa-solid fa-medal"></i>
            <span>راهنمای مدال خیرین</span>
          </div>
        </div>
      </nav>
    </aside>

    <!-- Main -->
    <main class="main">
      <!-- topbar -->
      <div class="topbar">
        <div class="welcome-box">
          <h1>به پنل حامیان مکسا خوش آمدید</h1>
          <p>در این پنل می‌توانید مشارکت‌های خود را مدیریت کنید، کمپین‌های پیشنهادی را ببینید و اثر کمک‌های ارزشمندتان را دنبال کنید.</p>
        </div>

        <div class="user-box">
          <div class="avatar" id="userAvatar">م</div>
          <div class="user-meta">
            <h3>
              <span id="userName">محمد رضایی</span>
              <span id="userMedal" class="medal silver">
                <i class="fa-solid fa-award"></i>
                نشان نقره‌ای
              </span>
            </h3>
            <span>حامی فعال موسسه مکسا</span>
          </div>
        </div>
      </div>

      <!-- quote -->
      <section class="quote-card">
        <h2><i class="fa-solid fa-sparkles"></i> جمله الهام‌بخش روز</h2>
        <p id="dailyQuote">
          هر کمک کوچک شما، می‌تواند نوری بزرگ در زندگی یک بیمار مبتلا به سرطان روشن کند؛ مهربانی شما ادامه امید است.
        </p>
      </section>

      <!-- hero slider -->
      <section class="hero-slider">
        <div class="slides" id="slides">
          <div class="slide">
            <div class="slide-content">
              <h2>تأمین هزینه دارو برای بیماران مبتلا به سرطان</h2>
              <p>
                با مشارکت شما می‌توانیم بخشی از هزینه داروهای ضروری بیماران را تأمین کنیم و فشار مالی خانواده‌ها را کاهش دهیم.
              </p>
              <button class="slide-btn" onclick="goToPayment()">مشارکت در این کمپین</button>
            </div>
          </div>

          <div class="slide">
            <div class="slide-content">
              <h2>حمایت از مراقبت در منزل بیماران</h2>
              <p>
                کمک شما می‌تواند هزینه مراقبت در منزل بیماران را پوشش دهد و آرامش بیشتری برای خانواده‌ها فراهم کند.
              </p>
              <button class="slide-btn" onclick="goToPayment()">پرداخت آنلاین</button>
            </div>
          </div>

          <div class="slide">
            <div class="slide-content">
              <h2>تجهیز بخش‌های درمانی و حمایتی</h2>
              <p>
                با همراهی خیرین، امکان خرید تجهیزات ضروری درمانی و حمایتی برای بهبود خدمات‌رسانی به بیماران فراهم می‌شود.
              </p>
              <button class="slide-btn" onclick="goToPayment()">حمایت از این کمپین</button>
            </div>
          </div>
        </div>

        <div class="slider-dots">
          <span class="dot active" onclick="showSlide(0)"></span>
          <span class="dot" onclick="showSlide(1)"></span>
          <span class="dot" onclick="showSlide(2)"></span>
        </div>
      </section>

      <!-- stats -->
      <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-head">
            <div>
              <h3>جمع کمک‌های شما</h3>
              <div class="value" id="totalDonation">۱۲,۵۰۰,۰۰۰ تومان</div>
              <div class="sub">مجموع پرداخت‌های ثبت شده</div>
            </div>
            <div class="stat-icon bg1">
              <i class="fa-solid fa-wallet"></i>
            </div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-head">
            <div>
              <h3>تعداد کل مشارکت‌ها</h3>
              <div class="value">۱۸</div>
              <div class="sub">در کمپین‌های مختلف</div>
            </div>
            <div class="stat-icon bg2">
              <i class="fa-solid fa-handshake-angle"></i>
            </div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-head">
            <div>
              <h3>کمپین‌های فعال</h3>
              <div class="value">۶</div>
              <div class="sub">قابل مشارکت در حال حاضر</div>
            </div>
            <div class="stat-icon bg3">
              <i class="fa-solid fa-bullseye"></i>
            </div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-head">
            <div>
              <h3>آخرین مشارکت</h3>
              <div class="value">۲,۰۰۰,۰۰۰ تومان</div>
              <div class="sub">در تاریخ ۱۴۰۳/۱۱/۰۵</div>
            </div>
            <div class="stat-icon bg4">
              <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
          </div>
        </div>
      </section>

      <!-- content -->
      <section class="content-grid">
        <div class="panel">
          <div class="panel-header">
            <h3>آخرین فعالیت‌ها و مشارکت‌ها</h3>
            <a href="javascript:void(0)" onclick="openModal('paymentsModal')">مشاهده همه</a>
          </div>

          <div class="activity-list">
            <div class="activity-item">
              <div class="activity-right">
                <div class="activity-icon">
                  <i class="fa-solid fa-heart-circle-check"></i>
                </div>
                <div class="activity-content">
                  <h4>مشارکت در کمپین تأمین دارو</h4>
                  <p>پرداخت شما با موفقیت ثبت شد و در تأمین بخشی از هزینه داروهای بیماران نقش داشت.</p>
                </div>
              </div>
              <div class="amount">۲,۰۰۰,۰۰۰ تومان</div>
            </div>

            <div class="activity-item">
              <div class="activity-right">
                <div class="activity-icon" style="background:linear-gradient(135deg,#64748b,#94a3b8)">
                  <i class="fa-solid fa-house-medical"></i>
                </div>
                <div class="activity-content">
                  <h4>حمایت از مراقبت در منزل</h4>
                  <p>کمک شما به پوشش بخشی از خدمات پرستاری و مراقبت در منزل بیماران اختصاص یافت.</p>
                </div>
              </div>
              <div class="amount">۱,۵۰۰,۰۰۰ تومان</div>
            </div>

            <div class="activity-item">
              <div class="activity-right">
                <div class="activity-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24)">
                  <i class="fa-solid fa-stethoscope"></i>
                </div>
                <div class="activity-content">
                  <h4>کمک به خرید تجهیزات حمایتی</h4>
                  <p>بخشی از کمک شما برای تهیه تجهیزات ضروری مراکز حمایتی بیماران استفاده شد.</p>
                </div>
              </div>
              <div class="amount">۳,۰۰۰,۰۰۰ تومان</div>
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h3>خلاصه اثر کمک‌های شما</h3>
            <a href="javascript:void(0)" onclick="openModal('impactModal')">جزئیات بیشتر</a>
          </div>

          <div class="impact-box">
            <div class="impact-item">
              <h4>تأمین مراقبت در منزل</h4>
              <p>بر اساس مجموع مشارکت شما، هزینه مراقبت در منزل <strong>۳ بیمار</strong> برای یک دوره حمایتی تأمین شده است.</p>
              <div class="progress-wrap">
                <div class="progress">
                  <div class="progress-bar" style="width:72%"></div>
                </div>
              </div>
            </div>

            <div class="impact-item">
              <h4>خرید تجهیزات مصرفی</h4>
              <p>میزان کمک شما به تهیه حداقل <strong>۱۲ قلم تجهیزات و اقلام حمایتی</strong> برای بیماران کمک کرده است.</p>
              <div class="progress-wrap">
                <div class="progress">
                  <div class="progress-bar" style="width:58%"></div>
                </div>
              </div>
            </div>

            <div class="impact-item">
              <h4>تأمین بخشی از هزینه دارو</h4>
              <p>کمک‌های شما در پوشش بخشی از هزینه داروی <strong>۵ بیمار</strong> مؤثر بوده است.</p>
              <div class="progress-wrap">
                <div class="progress">
                  <div class="progress-bar" style="width:81%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Payments Modal -->
  <div class="modal" id="paymentsModal">
    <div class="modal-content modal-lg">
      <button class="close-btn" onclick="closeModal('paymentsModal')">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <h2 class="modal-title">لیست پرداخت‌های خیر</h2>
      <p class="modal-sub">در این بخش سوابق کامل پرداخت‌های شما به همراه تاریخ، مبلغ، کمپین و رسید پرداخت نمایش داده می‌شود.</p>

      <table>
        <thead>
          <tr>
            <th>ردیف</th>
            <th>مبلغ پرداخت</th>
            <th>تاریخ</th>
            <th>کمپین</th>
            <th>وضعیت</th>
            <th>رسید</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>۱</td>
            <td>۲,۰۰۰,۰۰۰ تومان</td>
            <td>۱۴۰۳/۱۱/۰۵</td>
            <td>تأمین دارو</td>
            <td style="color:var(--success);font-weight:700;">موفق</td>
            <td><button class="btn btn-secondary">مشاهده رسید</button></td>
          </tr>
          <tr>
            <td>۲</td>
            <td>۱,۵۰۰,۰۰۰ تومان</td>
            <td>۱۴۰۳/۱۰/۲۱</td>
            <td>مراقبت در منزل</td>
            <td style="color:var(--success);font-weight:700;">موفق</td>
            <td><button class="btn btn-secondary">مشاهده رسید</button></td>
          </tr>
          <tr>
            <td>۳</td>
            <td>۳,۰۰۰,۰۰۰ تومان</td>
            <td>۱۴۰۳/۰۹/۱۵</td>
            <td>خرید تجهیزات حمایتی</td>
            <td style="color:var(--success);font-weight:700;">موفق</td>
            <td><button class="btn btn-secondary">مشاهده رسید</button></td>
          </tr>
        </tbody>
      </table>

      <div class="receipt-card">
        <h3>پیش‌نمایش رسید پرداخت</h3>
        <div class="receipt-preview">
          <i class="fa-solid fa-file-invoice" style="margin-left:8px;"></i>
          تصویر یا فایل رسید پرداخت در این قسمت نمایش داده می‌شود
        </div>
      </div>
    </div>
  </div>

  <!-- Campaigns Modal -->
  <div class="modal" id="campaignsModal">
    <div class="modal-content modal-lg">
      <button class="close-btn" onclick="closeModal('campaignsModal')">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <h2 class="modal-title">کمپین‌های پیشنهادی</h2>
      <p class="modal-sub">بر اساس علایق و سابقه مشارکت شما، این کمپین‌ها برای حمایت پیشنهاد می‌شوند.</p>

      <div class="campaign-cards">
        <div class="campaign-card">
          <div class="campaign-image" style="background-image:url('https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?auto=format&fit=crop&w=1200&q=80')">
            <div class="campaign-tag">پیشنهاد ویژه</div>
          </div>
          <div class="campaign-body">
            <h4>کمپین تأمین داروی بیماران</h4>
            <p>مشارکت شما می‌تواند بخشی از هزینه داروهای ضروری بیماران مبتلا به سرطان را پوشش دهد.</p>
            <div class="campaign-footer">
              <span>پیشرفت: ۷۳٪</span>
              <button class="btn btn-primary" onclick="goToPayment()">حمایت می‌کنم</button>
            </div>
          </div>
        </div>

        <div class="campaign-card">
          <div class="campaign-image" style="background-image:url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1200&q=80')">
            <div class="campaign-tag">محبوب</div>
          </div>
          <div class="campaign-body">
            <h4>حمایت از مراقبت در منزل</h4>
            <p>با کمک شما خدمات پرستاری و مراقبتی در منزل برای بیماران نیازمند فراهم می‌شود.</p>
            <div class="campaign-footer">
              <span>پیشرفت: ۵۸٪</span>
              <button class="btn btn-primary" onclick="goToPayment()">پرداخت آنلاین</button>
            </div>
          </div>
        </div>

        <div class="campaign-card">
          <div class="campaign-image" style="background-image:url('https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=1200&q=80')">
            <div class="campaign-tag">در حال اجرا</div>
          </div>
          <div class="campaign-body">
            <h4>خرید تجهیزات حمایتی</h4>
            <p>برای بهبود کیفیت خدمات حمایتی، این کمپین جهت تأمین تجهیزات ضروری راه‌اندازی شده است.</p>
            <div class="campaign-footer">
              <span>پیشرفت: ۶۴٪</span>
              <button class="btn btn-primary" onclick="goToPayment()">مشارکت</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Profile Modal -->
  <div class="modal" id="profileModal">
    <div class="modal-content modal-lg">
      <button class="close-btn" onclick="closeModal('profileModal')">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <h2 class="modal-title">پروفایل کاربری</h2>
      <p class="modal-sub">در این بخش می‌توانید تصویر و اطلاعات شخصی خود را تکمیل یا ویرایش کنید.</p>

      <div class="profile-upload">
        <div class="profile-pic">م</div>
        <div>
          <h3 style="margin-bottom:8px;">تصویر پروفایل</h3>
          <p style="color:var(--muted);margin-bottom:12px;">عکس نمایه خود را تغییر دهید.</p>
          <button class="btn btn-primary">آپلود تصویر جدید</button>
        </div>
      </div>

      <form class="form-grid">
        <div class="form-group">
          <label>نام</label>
          <input type="text" value="محمد" />
        </div>

        <div class="form-group">
          <label>نام خانوادگی</label>
          <input type="text" value="رضایی" />
        </div>

        <div class="form-group">
          <label>شماره تماس</label>
          <input type="text" value="09121234567" />
        </div>

        <div class="form-group">
          <label>تمایل به فعالیت داوطلبانه</label>
          <select>
            <option>بله</option>
            <option>خیر</option>
          </select>
        </div>

        <div class="form-group">
          <label>آیا سابقه بیماری سرطان داشته‌اید؟</label>
          <select>
            <option>خیر</option>
            <option>بله</option>
          </select>
        </div>

        <div class="form-group">
          <label>نوع عضویت</label>
          <select>
            <option>خیر</option>
            <option>داوطلب</option>
            <option>خیر و داوطلب</option>
          </select>
        </div>

        <div class="form-group full">
          <label>مهارت یا تخصص خاص</label>
          <textarea placeholder="مثلاً: طراحی گرافیک، مشاوره، پرستاری، برنامه‌نویسی، بازاریابی، ارتباطات و ..."></textarea>
        </div>

        <div class="form-group full" style="display:flex;flex-direction:row;gap:12px;justify-content:flex-end;">
          <button type="button" class="btn btn-secondary" onclick="closeModal('profileModal')">انصراف</button>
          <button type="submit" class="btn btn-primary">ذخیره اطلاعات</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Impact Modal -->
  <div class="modal" id="impactModal">
    <div class="modal-content modal-lg">
      <button class="close-btn" onclick="closeModal('impactModal')">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <h2 class="modal-title">اثرات کمک‌های من</h2>
      <p class="modal-sub">در این بخش می‌توانید اثر واقعی مشارکت‌های خود را مشاهده کنید و گزارش تصویری دریافت نمایید.</p>

      <div class="impact-summary">
        <div class="impact-stat">
          <h4>تعداد بیماران بهره‌مند</h4>
          <div class="big">۸ نفر</div>
        </div>
        <div class="impact-stat">
          <h4>تجهیزات تأمین شده</h4>
          <div class="big">۱۲ عدد</div>
        </div>
        <div class="impact-stat">
          <h4>دوره‌های مراقبت در منزل</h4>
          <div class="big">۳ دوره</div>
        </div>
      </div>

      <div class="panel" style="padding:18px;margin-bottom:18px;">
        <h3 style="margin-bottom:12px;">تحلیل اثر کمک‌های شما</h3>
        <p style="color:var(--muted); line-height:2;">
          مجموع کمک‌های شما تاکنون باعث شده است بخشی از هزینه دارو، اقلام مصرفی و خدمات مراقبتی بیماران مبتلا به سرطان تأمین شود.
          بر اساس برآوردهای موسسه، مشارکت شما در تأمین خدمات حمایتی برای چند بیمار و خرید تجهیزات ضروری مؤثر بوده است.
        </p>
      </div>

      <div class="upload-box">
        <i class="fa-solid fa-cloud-arrow-up" style="font-size:34px;color:var(--primary);margin-bottom:12px;"></i>
        <h3 style="margin-bottom:10px;">آپلود گزارش تصویری کار خیر</h3>
        <p style="color:var(--muted);margin-bottom:14px;">
          در این بخش می‌توانید گزارش تصویری، رسید تصویری یا مستندات اثر کمک‌های این خیر را بارگذاری و نمایش دهید.
        </p>
        <button class="btn btn-primary">آپلود تصویر گزارش</button>
      </div>
    </div>
  </div>

  <!-- Medal Guide Modal -->
  <div class="modal" id="medalGuideModal">
    <div class="modal-content">
      <button class="close-btn" onclick="closeModal('medalGuideModal')">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <h2 class="modal-title">راهنمای مدال خیرین</h2>
      <p class="modal-sub">مدال شما بر اساس مجموع مشارکت‌های ثبت‌شده در پنل، به‌صورت پویا تعیین می‌شود.</p>

      <div class="medal-guide">
        <div class="guide-card guide-bronze">
          <h4><i class="fa-solid fa-medal"></i> مدال برنز</h4>
          <p>برای مجموع کمک از ۱ میلیون تومان تا ۵ میلیون تومان</p>
        </div>

        <div class="guide-card guide-silver">
          <h4><i class="fa-solid fa-medal"></i> مدال نقره‌ای</h4>
          <p>برای مجموع کمک بیش از ۵ میلیون تومان تا ۲۰ میلیون تومان</p>
        </div>

        <div class="guide-card guide-gold">
          <h4><i class="fa-solid fa-medal"></i> مدال طلایی</h4>
          <p>برای مجموع کمک بیش از ۲۰ میلیون تومان تا ۱۰۰ میلیون تومان</p>
        </div>

        <div class="guide-card guide-diamond">
          <h4><i class="fa-solid fa-gem"></i> مدال الماس</h4>
          <p>برای مجموع کمک بیش از ۱۰۰ میلیون تومان</p>
        </div>
      </div>
    </div>
  </div>

  <script>
  
     // مدال پویا بر اساس مجموع کمک دریافت شده از PHP
    const totalDonationValue = <?php echo $totalDonationValue; ?>; 

    function getMedalByDonation(amount){
      if(amount >= 1000000 && amount <= 5000000) return { title: "نشان برنز", className: "bronze", icon: "fa-medal" };
      else if(amount > 5000000 && amount <= 20000000) return { title: "نشان نقره‌ای", className: "silver", icon: "fa-medal" };
      else if(amount > 20000000 && amount <= 100000000) return { title: "نشان طلایی", className: "gold", icon: "fa-medal" };
      else if(amount > 100000000) return { title: "نشان الماس", className: "diamond", icon: "fa-gem" };
      else return { title: "بدون نشان", className: "bronze", icon: "fa-award" };
    }

    function setUserMedal(){
      // آپدیت متن تومان در HTML
      document.getElementById("totalDonation").innerText = totalDonationValue.toLocaleString('fa-IR') + " تومان";
      
      const medal = getMedalByDonation(totalDonationValue);
      const medalEl = document.getElementById("userMedal");
      medalEl.className = `medal ${medal.className}`;
      medalEl.innerHTML = `<i class="fa-solid ${medal.icon}"></i> ${medal.title}`;
    }

    // مدیریت مودال‌ها
    function openModal(id){
      document.getElementById(id).classList.add('active');
    }

    function closeModal(id){
      document.getElementById(id).classList.remove('active');
    }

    window.addEventListener('click', function(e){
      document.querySelectorAll('.modal').forEach(modal=>{
        if(e.target === modal){
          modal.classList.remove('active');
        }
      });
    });

    // شبیه‌سازی رفتن به صفحه پرداخت
    function goToPayment(){
      alert('در این بخش باید به صفحه پرداخت آنلاین یا درگاه پرداخت متصل شوید.');
      // مثلا:
      // window.location.href = "/payment";
    }

    // اسلایدر بنر
    let currentSlide = 0;
    const slides = document.getElementById('slides');
    const dots = document.querySelectorAll('.dot');

    function showSlide(index){
      currentSlide = index;
      slides.style.transform = `translateX(${index * 33.3333}%)`;
      slides.style.transform = `translateX(-${index * 100 / 3}%)`;

      dots.forEach(dot => dot.classList.remove('active'));
      dots[index].classList.add('active');
    }

    function autoSlide(){
      currentSlide = (currentSlide + 1) % 3;
      showSlide(currentSlide);
    }

    setInterval(autoSlide, 5000);

    // جمله‌های الهام‌بخش روزانه
    const quotes = [
      "هر کمک کوچک شما، می‌تواند نوری بزرگ در زندگی یک بیمار مبتلا به سرطان روشن کند؛ مهربانی شما ادامه امید است.",
      "دستان مهربان شما، پلی است میان درد و امید؛ هر مشارکت شما یک زندگی را گرم‌تر می‌کند.",
      "خیر بودن فقط بخشیدن پول نیست؛ بخشیدن امید، آرامش و فرصت دوباره برای زندگی است.",
      "گاهی یک کمک به‌موقع، می‌تواند بار یک خانواده را سبک و مسیر درمان را هموارتر کند.",
      "شما با هر قدم خیرخواهانه، در ساختن جهانی انسانی‌تر و مهربان‌تر سهیم هستید."
    ];

    function setDailyQuote(){
      const dayIndex = new Date().getDate() % quotes.length;
      document.getElementById('dailyQuote').innerText = quotes[dayIndex];
    }

    setDailyQuote();

    // مدال پویا بر اساس مجموع کمک
    const totalDonationValue = 12500000; // مبلغ به تومان

    function formatToman(num){
      return num.toLocaleString('fa-IR') + " تومان";
    }

    function getMedalByDonation(amount){
      if(amount >= 1000000 && amount <= 5000000){
        return { title: "نشان برنز", className: "bronze", icon: "fa-medal" };
      } else if(amount > 5000000 && amount <= 20000000){
        return { title: "نشان نقره‌ای", className: "silver", icon: "fa-medal" };
      } else if(amount > 20000000 && amount <= 100000000){
        return { title: "نشان طلایی", className: "gold", icon: "fa-medal" };
      } else if(amount > 100000000){
        return { title: "نشان الماس", className: "diamond", icon: "fa-gem" };
      } else {
        return { title: "بدون نشان", className: "bronze", icon: "fa-award" };
      }
    }

    function setUserMedal(){
      document.getElementById("totalDonation").innerText = formatToman(totalDonationValue);

      const medal = getMedalByDonation(totalDonationValue);
      const medalEl = document.getElementById("userMedal");
      medalEl.className = `medal ${medal.className}`;
      medalEl.innerHTML = `<i class="fa-solid ${medal.icon}"></i> ${medal.title}`;
    }

    setUserMedal();
  </script>
</body>
</html>

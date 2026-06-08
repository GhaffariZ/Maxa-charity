<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>اهداف محبوب | خیریه مکسا</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">

  <style>
    /* ============ توکن‌های طراحی (برگرفته از داشبورد مدیریت) ============ */
    :root {
      --color-primary: #007b7a;
      --color-primary-dark: #006665;
      --color-primary-light: #4fb2b0;
      --color-secondary: #f4a61e;
      --color-text: #2f3437;
      --color-muted: #9d9d9d;
      --color-border: #e6e8ea;
      --color-bg: #f8f9fa;
      --color-surface: #ffffff;
      --success: #16a37a;
      --danger: #e0556b;
      --warning: #f4a61e;
      --primary-08: rgba(0, 123, 122, .08);
      --primary-12: rgba(0, 123, 122, .12);
      --primary-16: rgba(0, 123, 122, .16);
      --secondary-12: rgba(244, 166, 30, .14);
      --radius-sm: 12px;
      --radius: 18px;
      --radius-lg: 24px;
      --shadow-sm: 0 1px 2px rgba(16, 40, 40, .04), 0 2px 5px rgba(16, 40, 40, .05);
      --shadow-md: 0 4px 14px rgba(16, 40, 40, .06), 0 2px 6px rgba(16, 40, 40, .04);
      --shadow-lg: 0 20px 44px -14px rgba(0, 102, 101, .20), 0 8px 20px -10px rgba(16, 40, 40, .12);
      --ease: cubic-bezier(.4, 0, .2, 1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    body {
      font-family: 'Vazirmatn', sans-serif;
      background: var(--color-bg);
      color: var(--color-text);
      direction: rtl;
      font-size: 14px;
      line-height: 1.65;
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
    }

    button {
      font-family: inherit;
      cursor: pointer;
      border: none;
      background: none;
      color: inherit
    }

    input {
      font-family: inherit
    }

    ul {
      list-style: none
    }

    *::-webkit-scrollbar {
      width: 9px;
      height: 9px
    }

    *::-webkit-scrollbar-thumb {
      background: rgba(0, 123, 122, .18);
      border-radius: 99px;
      border: 2px solid transparent;
      background-clip: padding-box
    }

    .projects-section .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 48px 22px 120px;
    }

    /* ============ سربرگ ============ */
    .header {
      text-align: center;
      margin-bottom: 34px;
    }

    .header h1 {
      font-size: 2rem;
      font-weight: 800;
      letter-spacing: -.01em;
      margin-bottom: 12px;
      color: var(--color-text);
    }

    .header h1 span {
      color: var(--color-primary);
    }

    .header p {
      color: var(--color-muted);
      font-size: 1rem;
      max-width: 640px;
      margin: 0 auto;
    }

    /* ============ فیلتر دسته‌بندی (به سبک چیپ‌های داشبورد) ============ */
    .course-categories {
      margin-bottom: 40px;
    }

    .course-categories ul {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 12px;
    }

    .course-categories li {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 18px;
      border: 1.5px solid var(--color-border);
      border-radius: 99px;
      background: var(--color-surface);
      color: #6c7479;
      font-weight: 600;
      font-size: 13px;
      cursor: pointer;
      transition: border-color .22s, background .22s, color .22s, box-shadow .22s;
    }

    .course-categories li:hover {
      border-color: var(--color-primary-light);
      color: var(--color-primary-dark);
    }

    .course-categories li.active {
      border-color: var(--color-primary);
      background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
      color: #fff;
      box-shadow: 0 10px 20px -10px rgba(0, 123, 122, .75);
    }

    /* ============ شبکه کارتها و اسلایدر ============ */
    .cta-cards-wrap {
      position: relative;
      width: 100%;
      margin: 0 auto;
    }

    .cta-cards-viewport {
      width: 100%;
      overflow: hidden;
      padding: 15px 0; /* برای جلوگیری از بریده شدن سایهها و افکتهای هاور */
    }

    .cta-cards {
      display: flex;
      flex-direction: row;
      flex-wrap: nowrap;
      gap: 28px;
      width: 100%;
      transition: transform 0.6s cubic-bezier(0.25, 1, 0.25, 1);
      align-items: flex-start;
    }

    .cta-cards .card {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      position: relative;
      cursor: pointer;
      transition: transform .3s var(--ease), box-shadow .3s var(--ease), border-color .3s;
      flex: 0 0 calc((100% - (2 * 28px)) / 3);
      max-width: calc((100% - (2 * 28px)) / 3);
    }

    /* دکمههای ناوبری اسلایدر */
    .slider-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(8px);
      border: 1px solid var(--color-border);
      box-shadow: var(--shadow-md);
      color: var(--color-primary-dark);
      font-size: 1.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
      transition: all 0.3s var(--ease);
      cursor: pointer;
    }

    .slider-btn:hover {
      background: var(--color-primary);
      color: #fff;
      border-color: var(--color-primary);
      box-shadow: var(--shadow-lg);
      transform: translateY(-50%) scale(1.08);
    }

    .slider-btn:disabled {
      opacity: 0;
      pointer-events: none;
    }

    .slider-btn.prev-btn {
      right: -24px;
    }

    .slider-btn.next-btn {
      left: -24px;
    }

    /* نقاط پایین اسلایدر */
    .slider-dots {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 24px;
    }

    .slider-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--color-border);
      cursor: pointer;
      transition: all 0.3s var(--ease);
    }

    .slider-dot:hover {
      background: var(--color-primary-light);
    }

    .slider-dot.active {
      width: 24px;
      border-radius: 4px;
      background: var(--color-primary);
    }

    .card:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-lg);
      border-color: transparent;
    }

    .card-img-wrapper {
      width: 100%;
      height: 200px;
      overflow: hidden;
      background: var(--color-bg);
    }

    .card-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform .5s var(--ease);
    }

    .card:hover .card-img {
      transform: scale(1.05);
    }

    .card-body {
      padding: 22px;
      display: flex;
      flex-direction: column;
      flex-grow: 1;
    }

    .card-title {
      font-size: 1.15rem;
      font-weight: 800;
      letter-spacing: -.01em;
      margin-bottom: 8px;
      color: var(--color-text);
      text-align: center;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .card-desc {
      color: var(--color-muted);
      font-size: .92rem;
      margin-bottom: 15px;
      line-height: 1.7;
      position: relative;
      max-height: 48px; /* ارتفاع دقیق برای نشان دادن حدود ۲ خط */
      overflow: hidden;
      transition: max-height 0.5s cubic-bezier(0.25, 1, 0.5, 1); /* انیمیشن نرم روان */
    }

    .card-desc::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 25px;
      background: linear-gradient(transparent, var(--color-surface));
      transition: opacity 0.3s ease;
      pointer-events: none; /* جلوگیری از اختلال در کلیک */
    }

    .progress-container {
      margin: 6px 0 0;
    }

    .progress-badge {
      display: inline-block;
      background: var(--secondary-12);
      color: #b9740a;
      font-size: .72rem;
      font-weight: 800;
      padding: 3px 10px;
      border-radius: 99px;
      margin-bottom: 8px;
    }

    .progress-info {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      font-size: .8rem;
      color: var(--color-muted);
      margin-bottom: 7px;
      font-weight: 600;
    }

    .progress-bar {
      height: 8px;
      background: var(--primary-08);
      border-radius: 99px;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--color-secondary), #ffc24d);
      border-radius: 99px;
      transition: width .8s var(--ease);
    }

    .card-footer {
      text-align: center;
      margin-top: 20px;
    }

    .btn-help {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      padding: 12px 20px;
      background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
      color: #fff;
      border-radius: 13px;
      font-weight: 700;
      font-size: .92rem;
      box-shadow: 0 10px 22px -10px rgba(0, 123, 122, .7);
      transition: transform .15s var(--ease), box-shadow .22s;
    }

    .btn-help:hover {
      transform: translateY(-2px);
      box-shadow: 0 16px 30px -10px rgba(0, 123, 122, .85);
    }

    .empty-state {
      grid-column: 1/-1;
      text-align: center;
      padding: 40px 20px;
      color: var(--color-muted);
      font-weight: 600;
    }

    .empty-state.error {
      color: var(--danger);
    }

    /* ============ مودال ============ */
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(16, 40, 40, 0);
      backdrop-filter: blur(0px);
      z-index: 99999;
      justify-content: center;
      align-items: center;
      padding: 20px;
      transition: background 0.4s var(--ease), backdrop-filter 0.4s var(--ease);
    }

    .modal.show {
      display: flex;
      background: rgba(16, 40, 40, .55);
      backdrop-filter: blur(12px);
    }

    .modal-content {
      background: var(--color-surface);
      color: var(--color-text);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-lg);
      padding: 30px;
      max-width: 480px;
      width: 100%;
      position: relative;
      box-shadow: var(--shadow-lg);
      transform: scale(0.8) translateY(50px) rotate(-1deg);
      opacity: 0;
      transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.5s;
    }

    .modal.show .modal-content {
      transform: scale(1) translateY(0) rotate(0deg);
      opacity: 1;
    }

    /* ============ استایل جزئیات مودال ============ */
    .details-modal-content {
      max-width: 550px;
      padding: 24px;
    }

    .details-img-wrapper {
      width: 100%;
      height: 220px;
      border-radius: var(--radius);
      overflow: hidden;
      margin-bottom: 20px;
      box-shadow: var(--shadow-sm);
    }

    .details-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .details-title {
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--color-primary-dark);
      margin-bottom: 15px;
      text-align: center;
    }

    .details-desc-wrapper {
      max-height: 200px;
      overflow-y: auto;
      margin-bottom: 20px;
      padding-left: 8px;
    }

    .details-desc {
      color: var(--color-text);
      font-size: 0.95rem;
      line-height: 1.8;
      text-align: justify;
      white-space: pre-line;
    }

    .details-progress-container {
      background: var(--primary-08);
      border-radius: var(--radius-sm);
      padding: 16px;
      margin-bottom: 20px;
    }

    .details-footer {
      display: flex;
      justify-content: center;
    }

    .modal-title {
      text-align: center;
      font-size: 1.35rem;
      font-weight: 800;
      margin-bottom: 22px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 7px;
      font-weight: 700;
      font-size: .85rem;
      color: var(--color-text);
    }

    .form-group input {
      width: 100%;
      padding: 11px 13px;
      border: 1px solid var(--color-border);
      border-radius: var(--radius-sm);
      background: var(--color-bg);
      color: var(--color-text);
      font-size: .92rem;
      transition: border-color .2s, box-shadow .2s;
    }

    .form-group input:focus {
      outline: none;
      border-color: var(--color-primary-light);
      box-shadow: 0 0 0 4px var(--primary-08);
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 18px;
    }

    .checkbox-group input[type="checkbox"] {
      width: 18px;
      height: 18px;
      accent-color: var(--color-primary);
    }

    .checkbox-group label {
      font-size: .85rem;
      color: var(--color-muted);
    }

    .btn-submit {
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
      color: #fff;
      padding: 13px 20px;
      border-radius: 13px;
      font-size: 1rem;
      font-weight: 700;
      width: 100%;
      margin-top: 18px;
      box-shadow: 0 10px 22px -10px rgba(0, 123, 122, .7);
      transition: transform .15s var(--ease), box-shadow .22s;
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 16px 30px -10px rgba(0, 123, 122, .85);
    }

    .close-btn {
      position: absolute;
      top: 16px;
      left: 16px;
      font-size: 1.5rem;
      line-height: 1;
      color: var(--color-muted);
      cursor: pointer;
      transition: color .2s;
    }

    .close-btn:hover {
      color: var(--color-text);
    }

    @media (max-width:600px) {
      .header h1 {
        font-size: 1.6rem;
      }
    }

    @media (min-width: 1024px) {
      /* انیمیشن باز شدن متن روی دسکتاپ */
      .card:hover .card-desc {
        max-height: 300px; /* مقداری بیشتر از ارتفاع متن برای جا شدن کامل */
      }

      /* حذف افکت محوشدگی هنگام باز شدن */
      .card:hover .card-desc::after {
        opacity: 0;
      }

      .mobile-indicator { display: none; }
    }

    @media (max-width: 1023px) {
      .cta-cards .card {
        flex: 0 0 calc((100% - (1 * 28px)) / 2);
        max-width: calc((100% - (1 * 28px)) / 2);
      }

      .slider-btn.prev-btn {
        right: -12px;
      }

      .slider-btn.next-btn {
        left: -12px;
      }

      .mobile-indicator { 
        position: absolute; 
        bottom: 15px; 
        left: 15px; 
        background: rgba(0,0,0,0.05); 
        color: var(--color-muted); 
        padding: 4px 8px; 
        border-radius: 6px; 
        font-size: 11px; 
      }

      /* کلاس زنده باز شدن روی موبایل */
      .card.expanded .card-desc { 
        max-height: 400px; 
      }

      /* حذف افکت محوشدگی در موبایل */
      .card.expanded .card-desc::after {
        opacity: 0;
      }
    }

    @media (max-width: 639px) {
      .cta-cards .card {
        flex: 0 0 100%;
        max-width: 100%;
      }

      .slider-btn.prev-btn {
        right: 0;
      }

      .slider-btn.next-btn {
        left: 0;
      }

      .slider-btn {
        width: 38px;
        height: 38px;
        font-size: 1rem;
      }
    }
  </style>
</head>

<body>

  <div class="projects-section">
    <div class="container">
      <div class="header">
        <h1>پروژه‌های در حال اجرا جهت کمک به <span>بیماران</span></h1>
        <p>با کمک‌های خود به بیماران زندگی هدیه دهید</p>
      </div>

      <div class="course-categories">
        <ul>
          <li class="active" data-filter="all">همه</li>
          <li data-filter="drug">دارو</li>
          <li data-filter="food">غذا</li>
          <li data-filter="education">مهارت آموزی</li>
        </ul>
      </div>

      <section class="cta-cards-wrap" aria-label="Cards">
        <button class="slider-btn prev-btn" id="sliderPrevBtn" aria-label="صفحه قبلی">
          <i class="fas fa-chevron-right"></i>
        </button>
        <div class="cta-cards-viewport">
          <div class="cta-cards" id="ctaCards"></div>
        </div>
        <button class="slider-btn next-btn" id="sliderNextBtn" aria-label="صفحه بعدی">
          <i class="fas fa-chevron-left"></i>
        </button>
        <div class="slider-dots" id="sliderDots"></div>
      </section>
    </div>

    <!-- Modal -->
    <div id="donationModal" class="modal">
      <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2 class="modal-title">عنوان مودال</h2>
        <form>
          <div class="form-group">
            <label for="name">نام</label>
            <input type="text" id="name" placeholder="نام">
          </div>
          <div style="display:flex; gap:15px;">
            <div class="form-group" style="flex:1;">
              <label for="email">ایمیل</label>
              <input type="email" id="email" placeholder="ایمیل">
            </div>
            <div class="form-group" style="flex:1;">
              <label for="phone">تلفن</label>
              <input type="tel" id="phone" placeholder="تلفن">
            </div>
          </div>
          <div class="form-group">
            <label for="amount">مبلغ کمک (تومان)</label>
            <input type="number" id="amount" placeholder="مبلغ">
          </div>
          <div class="checkbox-group">
            <input type="checkbox" id="agreeTerms" name="agreeTerms">
            <label for="agreeTerms">من شرایط و ضوابط را می‌پذیرم</label>
          </div>
          <button type="submit" class="btn-submit">ثبت کمک</button>
        </form>
      </div>
    </div>

  </div>

  <script>
    // تصویر جایگزین بهصورت SVG درونخطی (بدون درخواست شبکهای تا صفحه هرگز معلق نماند)
    var PLACEHOLDER_IMG = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMjUwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZWVmMWYyIi8+PGcgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYjljMmM2IiBzdHJva2Utd2lkdGg9IjMiPjxyZWN0IHg9IjE1OCIgeT0iOTIiIHdpZHRoPSI4NCIgaGVpZ2h0PSI2NCIgcng9IjciLz48Y2lyY2xlIGN4PSIxODIiIGN5PSIxMTYiIHI9IjkiLz48cGF0aCBkPSJNMTYyIDE1MGwyNC0yMiAxNSAxMyAxOS0xNyAxOCAxNnYxMnoiLz48L2c+PC9zdmc+";

    let allCampaigns = [];
    let campaignsData = [];
    let currentPage = 0;

    (async function loadCampaignsSimple() {
      try {
        // دریافت دادهها از API
        const response = await fetch("/dashboard/campaign-list.php");
        const json = await response.json();

        // حالت اول: موفقیتآمیز و دارای دیتا
        if (json.status === "success" && json.data.length > 0) {
          allCampaigns = json.data;
          renderAndSlideCampaigns(allCampaigns);

          // حالت دوم: متصل شده اما دیتابیس خالی است یا کمپین فعال نیست
        } else if (json.status === "empty") {
          const ctaCards = document.getElementById("ctaCards");
          if (ctaCards) ctaCards.innerHTML = `<p class="empty-state">${json.message}</p>`;
          initSlider([]);

          // حالت سوم: خطا در دیتابیس یا کوئری
        } else if (json.status === "error") {
          const ctaCards = document.getElementById("ctaCards");
          if (ctaCards) ctaCards.innerHTML = `<p class="empty-state error">خطای بکاند: ${json.message}</p>`;
          initSlider([]);
        }

      } catch (error) {
        console.error("خطا در ارتباط:", error);
        const ctaCards = document.getElementById("ctaCards");
        if (ctaCards) ctaCards.innerHTML = "<p class='empty-state error'>خطا در لود فایل API (احتمالاً آدرس اشتباه است یا فایل ارور ۵۰۰ دارد).</p>";
        initSlider([]);
      }
    })();

    function renderAndSlideCampaigns(campaigns) {
      const ctaCards = document.getElementById("ctaCards");
      if (!ctaCards) return;

      ctaCards.innerHTML = ""; // پاکسازی محتوای قدیمی

      if (campaigns.length === 0) {
        ctaCards.innerHTML = `<p class="empty-state">هیچ کمپینی در این دسته‌بندی وجود ندارد.</p>`;
        initSlider([]);
        return;
      }

      campaigns.forEach((camp) => {
        const target = parseFloat(camp.target_amount) || 0;
        const collected = parseFloat(camp.collected_amount) || 0;

        // محاسبه درصد پیشرفت
        let percent = target > 0 ? Math.round((collected / target) * 100) : 0;
        if (percent > 100) percent = 100;

        // ایجاد المان کارت
        const cardDiv = document.createElement("div");
        cardDiv.className = "card";
        // دستهبندی واقعی از دیتابیس برای کارکرد فیلتر
        cardDiv.setAttribute("data-category", camp.category || "all");
        cardDiv.setAttribute("role", "button");
        cardDiv.setAttribute("tabindex", "0");

        const descHtml = camp.description ? camp.description.replace(/\n/g, '<br>') : "";

        cardDiv.innerHTML = `
                <div class="card-img-wrapper">
                    <img src="${camp.image_url || PLACEHOLDER_IMG}" alt="${camp.title}" class="card-img"
                         onerror="this.onerror=null;this.src=PLACEHOLDER_IMG">
                </div>
                <div class="card-body">
                    <h3 class="card-title" title="${camp.title}">${camp.title}</h3>
                    <div class="card-desc">${descHtml}</div>

                    <div class="progress-container">
                        <div class="progress-badge">${Number(percent).toLocaleString('fa-IR')}٪</div>
                        <div class="progress-info">
                            <span>جمع‌آوری شده: ${Number(collected).toLocaleString('fa-IR')} تومان</span>
                            <span>هدف: ${Number(target).toLocaleString('fa-IR')} تومان</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${percent}%;"></div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button class="btn-help" onclick="event.stopPropagation(); openModal('${camp.title.replace(/'/g, "\\'")}')">همین حالا کمک کنید</button>
                    </div>

                    <div class="mobile-indicator"><i class="fas fa-ellipsis-h"></i></div>
                </div>
            `;

        cardDiv.addEventListener('click', () => {
          cardDiv.classList.toggle('expanded');
        });

        ctaCards.appendChild(cardDiv);
      });

      initSlider(campaigns);
    }

    function initSlider(campaigns) {
      campaignsData = campaigns;
      currentPage = 0;
      updateSlider();
    }

    function updateSlider() {
      const ctaCards = document.getElementById("ctaCards");
      const prevBtn = document.getElementById("sliderPrevBtn");
      const nextBtn = document.getElementById("sliderNextBtn");
      const dotsContainer = document.getElementById("sliderDots");
      
      if (!ctaCards || !prevBtn || !nextBtn || !dotsContainer) return;
      
      const totalCards = campaignsData.length;
      if (totalCards === 0) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
        dotsContainer.style.display = 'none';
        ctaCards.style.transform = 'translateX(0)';
        return;
      }
      
      // مشخص کردن تعداد کارت در هر صفحه بر اساس عرض صفحه
      let cardsPerPage = 3;
      const width = window.innerWidth;
      if (width < 640) {
        cardsPerPage = 1;
      } else if (width < 1024) {
        cardsPerPage = 2;
      }
      
      const totalPages = Math.ceil(totalCards / cardsPerPage);
      
      if (currentPage >= totalPages) {
        currentPage = Math.max(0, totalPages - 1);
      }
      
      if (totalPages <= 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
        dotsContainer.style.display = 'none';
        ctaCards.style.transform = 'translateX(0)';
        return;
      } else {
        prevBtn.style.display = 'flex';
        nextBtn.style.display = 'flex';
        dotsContainer.style.display = 'flex';
      }
      
      // فعال/غیرفعال کردن دکمهها
      prevBtn.disabled = currentPage === 0;
      nextBtn.disabled = currentPage === totalPages - 1;
      
      // محاسبه میزان جابجایی
      const isRTL = document.documentElement.dir === 'rtl' || document.body.dir === 'rtl';
      const dirMultiplier = isRTL ? 1 : -1;
      const gap = 28;
      const viewportWidth = ctaCards.parentElement.offsetWidth || 1;
      const gapPercent = (gap / viewportWidth) * 100;
      const translateVal = currentPage * (100 + gapPercent);
      
      ctaCards.style.transform = `translateX(${translateVal * dirMultiplier}%)`;
      
      // رندر دکمههای نقطه
      dotsContainer.innerHTML = '';
      for (let i = 0; i < totalPages; i++) {
        const dot = document.createElement('div');
        dot.className = `slider-dot ${i === currentPage ? 'active' : ''}`;
        dot.setAttribute('role', 'button');
        dot.setAttribute('aria-label', `برو به صفحه ${i + 1}`);
        dot.addEventListener('click', () => {
          currentPage = i;
          updateSlider();
        });
        dotsContainer.appendChild(dot);
      }
    }

    // دکمههای ناوبری اسلایدر
    document.getElementById("sliderPrevBtn").addEventListener('click', () => {
      if (currentPage > 0) {
        currentPage--;
        updateSlider();
      }
    });

    document.getElementById("sliderNextBtn").addEventListener('click', () => {
      const totalCards = campaignsData.length;
      let cardsPerPage = 3;
      const width = window.innerWidth;
      if (width < 640) {
        cardsPerPage = 1;
      } else if (width < 1024) {
        cardsPerPage = 2;
      }
      const totalPages = Math.ceil(totalCards / cardsPerPage);
      
      if (currentPage < totalPages - 1) {
        currentPage++;
        updateSlider();
      }
    });

    window.addEventListener('resize', updateSlider);

    function openModal(title) {
      const modal = document.getElementById('donationModal');
      modal.style.display = 'flex';
      modal.offsetHeight; // force reflow
      modal.classList.add('show');
      document.querySelector('.modal-title').textContent = 'کمک برای: ' + title;
    }

    function closeModal() {
      const modal = document.getElementById('donationModal');
      modal.classList.remove('show');
      setTimeout(() => {
        if (!modal.classList.contains('show')) {
          modal.style.display = 'none';
        }
      }, 500);
    }

    window.onclick = function (event) {
      const donationModal = document.getElementById('donationModal');
      if (event.target == donationModal) { closeModal(); }
    }

    document.querySelector('form').addEventListener('submit', function (e) {
      e.preventDefault();
      alert('فرم ارسال شد.');
      closeModal();
    });

    // فیلترها بر اساس دادههای زنده
    const filterButtons = document.querySelectorAll('.course-categories li');
    filterButtons.forEach(btn => {
      btn.addEventListener('click', function () {
        document.querySelector('.course-categories .active').classList.remove('active');
        this.classList.add('active');
        let filter = this.getAttribute('data-filter');
        
        const filtered = filter === "all" 
          ? allCampaigns 
          : allCampaigns.filter(camp => (camp.category || "all") === filter);
          
        renderAndSlideCampaigns(filtered);
      });
    });
  </script>
</body>

</html>
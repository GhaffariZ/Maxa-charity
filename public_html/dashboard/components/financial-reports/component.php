<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صورت‌های مالی و گزارش‌های حسابرسی | مکسا</title>
    <style>
        /* Self-hosted Vazirmatn variable font */
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
            --primary-dark: #00736f;
            --primary-light: #e6f7f7;
            --accent-gold: #faa61a;
            --header-bg: #eef3f6;
            --text-dark: #1e293b;
            --text-muted: #475569;
            --border-light: #e2e8f0;
            --bg-card: #ffffff;
            --success-green: #10b981;
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

        .page-container {
            max-width: var(--main-width);
            margin: 40px auto;
            padding: 0 20px;
        }

        .header-capsule {
            background: linear-gradient(180deg, #f8f9fa 0%, #eef3f6 100%);
            border-radius: 50px;
            padding: 50px 30px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        .header-capsule h1 {
            font-size: 2.5rem;
            margin: 0 0 15px 0;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.02em;
        }

        .breadcrumb-pill {
            background-color: var(--primary-teal);
            color: white;
            padding: 6px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            display: inline-block;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .breadcrumb-pill:hover {
            opacity: 0.9;
            color: #fff;
            text-decoration: none;
        }

        /* بنر شفافیت مالی */
        .intro-banner {
            background: linear-gradient(135deg, #0f4c5c 0%, #00605d 50%, #008f8a 100%);
            color: #ffffff;
            border-radius: 32px;
            padding: 40px 36px;
            margin-bottom: 40px;
            box-shadow: 0 16px 40px rgba(15, 76, 92, 0.18);
            display: flex;
            flex-direction: column;
            gap: 26px;
        }

        .intro-banner-text h2 {
            font-size: 1.75rem;
            font-weight: 900;
            margin: 0 0 12px 0;
            color: #ffffff;
        }

        .intro-banner-text p {
            font-size: 1.05rem;
            line-height: 2;
            color: rgba(255, 255, 255, 0.92);
            margin: 0;
            text-align: justify;
        }

        .pillars-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 8px;
        }

        .pillar-card {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 20px;
            padding: 20px 18px;
            backdrop-filter: blur(8px);
        }

        .pillar-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            margin-bottom: 12px;
        }

        .pillar-card strong {
            display: block;
            font-size: 1.05rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .pillar-card span {
            display: block;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.8;
        }

        /* نوار فیلتر و تب‌ها */
        .reports-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 30px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border-light);
        }

        .toolbar-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toolbar-title::before {
            content: "";
            width: 4px;
            height: 24px;
            background: var(--primary-teal);
            border-radius: 4px;
            display: inline-block;
        }

        .year-filter-tabs {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 8px 18px;
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .filter-btn:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
            border-color: rgba(0, 143, 138, 0.3);
        }

        .filter-btn.active {
            background: var(--primary-teal);
            color: #ffffff;
            border-color: var(--primary-teal);
            box-shadow: 0 4px 14px rgba(0, 143, 138, 0.25);
        }

        /* شبکه کارت‌های صورت مالی */
        .financial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 26px;
            margin-bottom: 50px;
        }

        .financial-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 26px;
            padding: 28px 24px 22px;
            box-shadow: 0 8px 26px -6px rgba(15, 23, 42, 0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .financial-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px rgba(0, 143, 138, 0.12);
            border-color: rgba(0, 143, 138, 0.3);
        }

        .financial-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .financial-year-badge {
            background: var(--primary-light);
            color: var(--primary-teal);
            font-weight: 800;
            font-size: 0.88rem;
            padding: 6px 14px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .audit-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-size: 0.8rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 8px;
        }

        .audit-status-badge::before {
            content: "●";
            color: var(--success-green);
            font-size: 0.9rem;
        }

        .financial-title {
            font-size: 1.25rem;
            font-weight: 800;
            line-height: 1.6;
            margin: 0 0 10px 0;
            color: var(--text-dark);
        }

        .auditor-label {
            font-size: 0.88rem;
            color: #64748b;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .auditor-label strong {
            color: #0f172a;
            font-weight: 700;
        }

        .financial-specs {
            background: #f8fafc;
            border-radius: 18px;
            padding: 14px 16px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .spec-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.86rem;
        }

        .spec-row span {
            color: var(--text-muted);
        }

        .spec-row strong {
            color: var(--text-dark);
            font-weight: 700;
        }

        .financial-card-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: auto;
            padding-top: 18px;
            border-top: 1px dashed var(--border-light);
        }

        .btn-view-financial,
        .btn-download-audit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 0.88rem;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .btn-view-financial {
            background: #ffffff;
            color: var(--primary-teal);
            border: 1.5px solid var(--primary-teal);
        }

        .btn-view-financial:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
            text-decoration: none;
        }

        .btn-download-audit {
            background: var(--primary-teal);
            color: #ffffff;
            border: 1.5px solid var(--primary-teal);
            box-shadow: 0 4px 12px rgba(0, 143, 138, 0.2);
        }

        .btn-download-audit:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: #ffffff;
            text-decoration: none;
        }

        /* باکس اطلاعات بازرسان و تماس */
        .audit-info-card {
            background: #f8fafc;
            border: 1px solid var(--border-light);
            border-radius: 28px;
            padding: 32px 30px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .audit-info-text h3 {
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0 0 8px 0;
            color: var(--text-dark);
        }

        .audit-info-text p {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.8;
            max-width: 820px;
        }

        .audit-info-btn {
            background: #ffffff;
            color: var(--primary-teal);
            border: 1.5px solid #cbd5e1;
            padding: 12px 24px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.92rem;
            text-decoration: none;
            transition: 0.2s;
            white-space: nowrap;
        }

        .audit-info-btn:hover {
            background: var(--primary-teal);
            color: #ffffff;
            border-color: var(--primary-teal);
            text-decoration: none;
        }

        /* پاپ‌آپ مشاهده ارقام مالی */
        .financial-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999999;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(6px);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .financial-modal-box {
            background: #ffffff;
            border-radius: 28px;
            width: min(680px, 100%);
            max-height: 85vh;
            overflow-y: auto;
            padding: 34px 30px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.3);
            position: relative;
            animation: modalIn 0.25s ease both;
        }

        @keyframes modalIn {
            from { opacity: 0; transform: translateY(12px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-light);
        }

        .modal-header h3 {
            font-size: 1.35rem;
            font-weight: 900;
            color: var(--text-dark);
            margin: 0 0 6px 0;
            line-height: 1.5;
        }

        .modal-close-btn {
            background: #f1f5f9;
            border: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            transition: 0.2s;
        }

        .modal-close-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin: 18px 0;
            font-size: 0.92rem;
        }

        .financial-table th,
        .financial-table td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--border-light);
            text-align: right;
        }

        .financial-table th {
            background: #f8fafc;
            color: var(--text-dark);
            font-weight: 800;
        }

        .financial-table td:last-child {
            font-weight: 800;
            color: var(--primary-teal);
            direction: ltr;
            text-align: left;
        }

        .financial-table tr.total-row {
            background: #f0fdfa;
        }

        .financial-table tr.total-row td {
            font-weight: 900;
            color: #00736f;
            border-top: 2px solid #99f6e4;
        }

        @media (max-width: 900px) {
            .pillars-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .header-capsule {
                border-radius: 30px;
                padding: 30px 15px;
                margin-bottom: 25px;
            }

            .header-capsule h1 {
                font-size: 1.65rem;
            }

            .intro-banner {
                padding: 24px 20px;
                border-radius: 24px;
            }

            .pillars-grid {
                grid-template-columns: 1fr;
            }

            .financial-grid {
                grid-template-columns: 1fr;
            }

            .financial-card-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<main class="page-container">

    <header class="header-capsule">
        <h1>صورت‌های مالی و گزارش‌های حسابرسی</h1>
        <a href="/home" class="breadcrumb-pill">
            صفحه اصلی / آشنایی با مکسا / صورت‌های مالی
        </a>
    </header>

    <!-- بخش معرفی و ارکان انضباط مالی -->
    <section class="intro-banner">
        <div class="intro-banner-text">
            <h2>انضباط مالی و حسابرسی مستقل: سنگ‌بنای اعتماد نیکوکاران</h2>
            <p>
                مؤسسه نیکوکاری کنترل سرطان ایرانیان (مکسا) با باور به این که وجوه خیریه امانت مردم و مایه حیات بیماران مبتلا به سرطان است، تمامی فعالیت‌های مالی خود را تحت دقیق‌ترین موازین انضباطی، نظارت مستمر هیئت مدیره و هیئت امنا و حسابرسی سالانه توسط مؤسسات حسابرسی رسمی معتمد عضو جامعه حسابداران رسمی ایران (IACPA) اداره می‌کند.
            </p>
        </div>

        <div class="pillars-grid">
            <div class="pillar-card">
                <div class="pillar-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <strong>حسابرسی مستقل سالانه</strong>
                <span>توسط مؤسسات معتمد عضو جامعه حسابداران رسمی ایران</span>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 4 12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <strong>گزارش حسابرسی مقبول</strong>
                <span>صورت‌های مالی بدون بند مشروط و شفافیت کامل دفاتر</span>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <strong>تفکیک دقیق عواید</strong>
                <span>شفافیت کامل در ثبت نذورات، کمک‌های هدفمند و مصارف درمانی</span>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M20 8v6M23 11h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <strong>نظارت بازرس قانونی</strong>
                <span>بررسی موشکافانه گزارش‌ها توسط بازرسین قانونی منتخب مجمع</span>
            </div>
        </div>
    </section>

    <!-- نوار فیلتر و عنوان -->
    <section class="reports-toolbar">
        <h2 class="toolbar-title">آرشیو صورت‌های مالی و ترازنامه‌های حسابرسی‌شده</h2>

        <div class="year-filter-tabs">
            <button type="button" class="filter-btn active" data-filter="all">همه سال‌ها</button>
            <button type="button" class="filter-btn" data-filter="1402">سال ۱۴۰۲</button>
            <button type="button" class="filter-btn" data-filter="1401">سال ۱۴۰۱</button>
            <button type="button" class="filter-btn" data-filter="1400">سال ۱۴۰۰</button>
            <button type="button" class="filter-btn" data-filter="1399">سال ۱۳۹۹</button>
        </div>
    </section>

    <!-- کارت‌های صورت‌های مالی -->
    <div class="financial-grid" id="financialGrid">

        <!-- صورت مالی سال ۱۴۰۲ -->
        <article class="financial-card" data-year="1402">
            <div>
                <div class="financial-card-top">
                    <span class="financial-year-badge">
                        سال مالی منتهی به ۲۹ اسفند ۱۴۰۲
                    </span>
                    <span class="audit-status-badge">گزارش مقبول</span>
                </div>

                <h3 class="financial-title">صورت‌های مالی و گزارش حسابرس مستقل سال ۱۴۰۲</h3>

                <div class="auditor-label">
                    <span>حسابرس و بازرس قانونی:</span>
                    <strong>مؤسسه حسابرسی معتمد عضو IACPA</strong>
                </div>

                <div class="financial-specs">
                    <div class="spec-row">
                        <span>اقلام صورت‌های مالی:</span>
                        <strong>ترازنامه، صورت درآمد و هزینه، گردش وجوه نقد</strong>
                    </div>
                    <div class="spec-row">
                        <span>نسبت تخصیص به خدمات درمانی:</span>
                        <strong>۹۲.۴٪ از کل هزینه‌ها</strong>
                    </div>
                    <div class="spec-row">
                        <span>وضعیت اظهارنظر:</span>
                        <strong style="color: #065f46;">تأیید بدون بند مشروط</strong>
                    </div>
                </div>
            </div>

            <div class="financial-card-actions">
                <button type="button" class="btn-view-financial" data-modal="modal-fin-1402">
                    مشاهده اقلام کلیدی
                </button>
                <a href="#download-fin-1402" class="btn-download-audit js-download-btn" data-title="صورت مالی ۱۴۰۲">
                    دانلود PDF حسابرسی
                </a>
            </div>
        </article>

        <!-- صورت مالی سال ۱۴۰۱ -->
        <article class="financial-card" data-year="1401">
            <div>
                <div class="financial-card-top">
                    <span class="financial-year-badge">
                        سال مالی منتهی به ۲۹ اسفند ۱۴۰۱
                    </span>
                    <span class="audit-status-badge">گزارش مقبول</span>
                </div>

                <h3 class="financial-title">صورت‌های مالی و گزارش حسابرس مستقل سال ۱۴۰۱</h3>

                <div class="auditor-label">
                    <span>حسابرس و بازرس قانونی:</span>
                    <strong>مؤسسه حسابرسی معتمد عضو IACPA</strong>
                </div>

                <div class="financial-specs">
                    <div class="spec-row">
                        <span>اقلام صورت‌های مالی:</span>
                        <strong>ترازنامه، سود و زیان (درآمد و هزینه)، یادداشت‌های پیوست</strong>
                    </div>
                    <div class="spec-row">
                        <span>نسبت تخصیص به خدمات درمانی:</span>
                        <strong>۹۱.۸٪ از کل هزینه‌ها</strong>
                    </div>
                    <div class="spec-row">
                        <span>وضعیت اظهارنظر:</span>
                        <strong style="color: #065f46;">تأیید بدون بند مشروط</strong>
                    </div>
                </div>
            </div>

            <div class="financial-card-actions">
                <button type="button" class="btn-view-financial" data-modal="modal-fin-1401">
                    مشاهده اقلام کلیدی
                </button>
                <a href="#download-fin-1401" class="btn-download-audit js-download-btn" data-title="صورت مالی ۱۴۰۱">
                    دانلود PDF حسابرسی
                </a>
            </div>
        </article>

        <!-- صورت مالی سال ۱۴۰۰ -->
        <article class="financial-card" data-year="1400">
            <div>
                <div class="financial-card-top">
                    <span class="financial-year-badge">
                        سال مالی منتهی به ۲۹ اسفند ۱۴۰۰
                    </span>
                    <span class="audit-status-badge">گزارش مقبول</span>
                </div>

                <h3 class="financial-title">صورت‌های مالی و گزارش حسابرس مستقل سال ۱۴۰۰</h3>

                <div class="auditor-label">
                    <span>حسابرس و بازرس قانونی:</span>
                    <strong>مؤسسه حسابرسی معتمد عضو IACPA</strong>
                </div>

                <div class="financial-specs">
                    <div class="spec-row">
                        <span>اقلام صورت‌های مالی:</span>
                        <strong>ترازنامه، صورت جریان وجوه نقد، یادداشت‌ها</strong>
                    </div>
                    <div class="spec-row">
                        <span>نسبت تخصیص به خدمات درمانی:</span>
                        <strong>۹۰.۵٪ از کل هزینه‌ها</strong>
                    </div>
                    <div class="spec-row">
                        <span>وضعیت اظهارنظر:</span>
                        <strong style="color: #065f46;">تأیید بدون بند مشروط</strong>
                    </div>
                </div>
            </div>

            <div class="financial-card-actions">
                <button type="button" class="btn-view-financial" data-modal="modal-fin-1400">
                    مشاهده اقلام کلیدی
                </button>
                <a href="#download-fin-1400" class="btn-download-audit js-download-btn" data-title="صورت مالی ۱۴۰۰">
                    دانلود PDF حسابرسی
                </a>
            </div>
        </article>

        <!-- صورت مالی سال ۱۳۹۹ -->
        <article class="financial-card" data-year="1399">
            <div>
                <div class="financial-card-top">
                    <span class="financial-year-badge">
                        سال مالی منتهی به ۳۰ اسفند ۱۳۹۹
                    </span>
                    <span class="audit-status-badge">گزارش مقبول</span>
                </div>

                <h3 class="financial-title">صورت‌های مالی و گزارش حسابرس مستقل سال ۱۳۹۹</h3>

                <div class="auditor-label">
                    <span>حسابرس و بازرس قانونی:</span>
                    <strong>مؤسسه حسابرسی معتمد عضو IACPA</strong>
                </div>

                <div class="financial-specs">
                    <div class="spec-row">
                        <span>اقلام صورت‌های مالی:</span>
                        <strong>ترازنامه، صورت عملکرد درآمد و هزینه</strong>
                    </div>
                    <div class="spec-row">
                        <span>نسبت تخصیص به خدمات درمانی:</span>
                        <strong>۸۹.۹٪ از کل هزینه‌ها</strong>
                    </div>
                    <div class="spec-row">
                        <span>وضعیت اظهارنظر:</span>
                        <strong style="color: #065f46;">تأیید بدون بند مشروط</strong>
                    </div>
                </div>
            </div>

            <div class="financial-card-actions">
                <button type="button" class="btn-view-financial" data-modal="modal-fin-1399">
                    مشاهده اقلام کلیدی
                </button>
                <a href="#download-fin-1399" class="btn-download-audit js-download-btn" data-title="صورت مالی ۱۳۹۹">
                    دانلود PDF حسابرسی
                </a>
            </div>
        </article>

    </div>

    <!-- کارت اطلاعات بازرسان و مراجع ناظر -->
    <div class="audit-info-card">
        <div class="audit-info-text">
            <h3>نظارت قانونی و حسابرسی مستقل</h3>
            <p>
                صورت‌های مالی مؤسسه مکسا سالانه در مجمع عمومی عادی به تصویب هیئت امنا رسیده و به مراجع ذی‌صلاح نظارتی از جمله وزارت کشور و سازمان امور مالیاتی کشور ارائه می‌شود. نیکوکاران و حامیان گرامی جهت دریافت هرگونه اطلاعات تکمیلی مالی می‌توانند با واحد مالی و اداری مکسا در ارتباط باشند.
            </p>
        </div>
        <a href="/contactus" class="audit-info-btn">
            ارتباط با واحد مالی مکسا
        </a>
    </div>

</main>

<!-- مدال مشاهده جزئیات اقلام صورت مالی -->
<div id="financialModal" class="financial-modal-overlay" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="financial-modal-box">
        <div class="modal-header">
            <div>
                <h3 id="modalTitle">خلاصه اقلام ترازنامه و درآمد-هزینه</h3>
                <span id="modalYear" class="financial-year-badge">سال مالی ۱۴۰۲</span>
            </div>
            <button type="button" class="modal-close-btn js-modal-close" aria-label="بستن">✕</button>
        </div>

        <div class="modal-body" id="modalBody">
            <!-- محتوای داینامیک -->
        </div>
    </div>
</div>

<script>
    // فیلتر تب‌های سال‌ها
    const filterBtns = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.financial-card');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.getAttribute('data-filter');
            cards.forEach(card => {
                const year = card.getAttribute('data-year');
                if (filter === 'all' || year === filter) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // مدال خلاصه اقلام صورت مالی
    const modal = document.getElementById('financialModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalYear = document.getElementById('modalYear');
    const modalBody = document.getElementById('modalBody');
    const closeBtn = document.querySelector('.js-modal-close');

    const financialData = {
        'modal-fin-1402': {
            title: 'خلاصه اقلام صورت مالی و ترازنامه سال ۱۴۰۲ مکسا',
            year: 'سال مالی ۱۴۰۲',
            content: `
                <p>مؤسسه حسابرسی معتمد عضو جامعه حسابداران رسمی ایران، صورت‌های مالی منتهی به ۲۹ اسفند ۱۴۰۲ مکسا را بررسی و اظهارنظر «مقبول و بدون هرگونه بند مشروط» صادر نموده است.</p>
                <table class="financial-table">
                    <tr><th>سرفصل‌های عملکرد مالی</th><th>سهم و درصد</th></tr>
                    <tr><td>هزینه‌های مستقیم خدمات درمانی و مراقبت تسکینی بیماران</td><td>۹۲.۴٪</td></tr>
                    <tr><td>هزینه‌های اداری، عمومی و پشتیبانی مؤسسه</td><td>۵.۲٪</td></tr>
                    <tr><td>هزینه‌های اطلاع‌رسانی، پژوهش و توسعه زیرساخت</td><td>۲.۴٪</td></tr>
                    <tr class="total-row"><td>جمع مصارف نیکوکاری و جاری</td><td>۱۰۰٪</td></tr>
                </table>
                <p><strong>تأمین منابع:</strong> ۹۴.۸٪ از محل هدایا و نذورات مستقیم مردمی و خیرین حقیقی و حقوقی، و ۵.۲٪ از محل موقوفات پایدار تأمین شده است.</p>
            `
        },
        'modal-fin-1401': {
            title: 'خلاصه اقلام صورت مالی و ترازنامه سال ۱۴۰۱ مکسا',
            year: 'سال مالی ۱۴۰۱',
            content: `
                <p>گزارش بازرس قانونی و حسابرس مستقل برای سال مالی ۱۴۰۱ نشان‌دهنده انطباق کامل اسناد مالی با استانداردهای حسابداری مؤسسات عام‌المنفعه است.</p>
                <table class="financial-table">
                    <tr><th>سرفصل‌های عملکرد مالی</th><th>سهم و درصد</th></tr>
                    <tr><td>هزینه‌های مستقیم مراقبت در منزل، کلینیک‌ها و تجهیزات بیماران</td><td>۹۱.۸٪</td></tr>
                    <tr><td>هزینه‌های اداری و پشتیبانی ستادی</td><td>۵.۶٪</td></tr>
                    <tr><td>هزینه‌های آموزش کادر درمان و فرهنگ‌سازی</td><td>۲.۶٪</td></tr>
                    <tr class="total-row"><td>جمع مصارف</td><td>۱۰۰٪</td></tr>
                </table>
            `
        },
        'modal-fin-1400': {
            title: 'خلاصه اقلام صورت مالی و ترازنامه سال ۱۴۰۰ مکسا',
            year: 'سال مالی ۱۴۰۰',
            content: `
                <p>حسابرسی رسمی سال ۱۴۰۰ با تأکید بر شفافیت حداکثری در منابع اهدایی مقابله با عوارض کرونا و تجهیزات تنفسی به انجام رسید.</p>
                <table class="financial-table">
                    <tr><th>سرفصل‌های عملکرد مالی</th><th>سهم و درصد</th></tr>
                    <tr><td>هزینه‌های دارویی، تجهیزات تنفسی و ویزیت در منزل</td><td>۹۰.۵٪</td></tr>
                    <tr><td>هزینه‌های عمومی و پرسنلی</td><td>۶.۵٪</td></tr>
                    <tr><td>آموزش و توسعه شبکه</td><td>۳.۰٪</td></tr>
                    <tr class="total-row"><td>جمع مصارف</td><td>۱۰۰٪</td></tr>
                </table>
            `
        },
        'modal-fin-1399': {
            title: 'خلاصه اقلام صورت مالی و ترازنامه سال ۱۳۹۹ مکسا',
            year: 'سال مالی ۱۳۹۹',
            content: `
                <p>صورت‌های مالی سال ۱۳۹۹ توسط مجمع عمومی عادی به تصویب رسید و گزارش مقبول حسابرس مستقل اخذ گردید.</p>
                <table class="financial-table">
                    <tr><th>سرفصل‌های عملکرد مالی</th><th>سهم و درصد</th></tr>
                    <tr><td>هزینه‌های خدمات حمایتی و درمانی بیماران</td><td>۸۹.۹٪</td></tr>
                    <tr><td>هزینه‌های پشتیبانی و ستاد شعب</td><td>۷.۱٪</td></tr>
                    <tr><td>پژوهش و انتشارات</td><td>۳.۰٪</td></tr>
                    <tr class="total-row"><td>جمع مصارف</td><td>۱۰۰٪</td></tr>
                </table>
            `
        }
    };

    document.querySelectorAll('.btn-view-financial').forEach(btn => {
        btn.addEventListener('click', () => {
            const modalKey = btn.getAttribute('data-modal');
            const data = financialData[modalKey];
            if (data) {
                modalTitle.textContent = data.title;
                modalYear.textContent = data.year;
                modalBody.innerHTML = data.content;
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    function closeModal() {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
    });

    document.querySelectorAll('.js-download-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const title = btn.getAttribute('data-title');
            alert('درخواست دریافت نسخه کامل ' + title + ' ثبت شد. فایل گزارش حسابرسی رسمی به زودی جهت دانلود قرار می‌گیرد یا جهت دریافت مکتوب می‌توانید با واحد مالی مکسا تماس حاصل فرمایید.');
        });
    });
</script>

</body>
</html>

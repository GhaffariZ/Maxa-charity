<?php require_once __DIR__ . '/../../../core/site-locale.php'; ?>
<!DOCTYPE html>
<html lang="fa" dir="<?= site_direction() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= site_e('گزارش‌های سالانه | مکسا') ?></title>
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

        /* بخش معرفی و شاخص‌ها */
        .intro-banner {
            background: linear-gradient(135deg, #05a8a2 0%, #008f8a 54%, #00605d 100%);
            color: #ffffff;
            border-radius: 32px;
            padding: 40px 36px;
            margin-bottom: 40px;
            box-shadow: 0 16px 40px rgba(0, 143, 138, 0.16);
            display: flex;
            flex-direction: column;
            gap: 24px;
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

        .stats-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 8px;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 20px;
            padding: 18px 16px;
            text-align: center;
            backdrop-filter: blur(8px);
        }

        .stat-item strong {
            display: block;
            font-size: 1.85rem;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 4px;
            direction: ltr;
        }

        .stat-item span {
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
        }

        /* فیلترها و نوار ابزار */
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

        /* شبکه کارت‌های گزارش */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 26px;
            margin-bottom: 50px;
        }

        .report-card {
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

        .report-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px rgba(0, 143, 138, 0.12);
            border-color: rgba(0, 143, 138, 0.3);
        }

        .report-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .report-year-badge {
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

        .report-type-badge {
            font-size: 0.78rem;
            color: #64748b;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 600;
        }

        .report-icon-box {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(0, 143, 138, 0.1) 0%, rgba(0, 143, 138, 0.04) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-teal);
            margin-bottom: 16px;
        }

        .report-title {
            font-size: 1.25rem;
            font-weight: 800;
            line-height: 1.6;
            margin: 0 0 10px 0;
            color: var(--text-dark);
        }

        .report-summary {
            font-size: 0.94rem;
            color: var(--text-muted);
            line-height: 1.9;
            margin: 0 0 18px 0;
            text-align: justify;
        }

        .report-highlights {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .report-highlights li {
            font-size: 0.86rem;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .report-highlights li::before {
            content: "✓";
            color: var(--primary-teal);
            font-weight: 900;
            font-size: 0.95rem;
        }

        .report-card-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: auto;
            padding-top: 18px;
            border-top: 1px dashed var(--border-light);
        }

        .btn-view-summary,
        .btn-download-pdf {
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

        .btn-view-summary {
            background: #ffffff;
            color: var(--primary-teal);
            border: 1.5px solid var(--primary-teal);
        }

        .btn-view-summary:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
            text-decoration: none;
        }

        .btn-download-pdf {
            background: var(--primary-teal);
            color: #ffffff;
            border: 1.5px solid var(--primary-teal);
            box-shadow: 0 4px 12px rgba(0, 143, 138, 0.2);
        }

        .btn-download-pdf:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: #ffffff;
            text-decoration: none;
        }

        /* فوتر شفافیت و اطلاعات */
        .transparency-box {
            background: #f8fafc;
            border: 1px solid var(--border-light);
            border-radius: 28px;
            padding: 30px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .transparency-content h3 {
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0 0 6px 0;
            color: var(--text-dark);
        }

        .transparency-content p {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.8;
        }

        .transparency-btn {
            background: #ffffff;
            color: var(--primary-teal);
            border: 1px solid #cbd5e1;
            padding: 12px 24px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.92rem;
            text-decoration: none;
            transition: 0.2s;
            white-space: nowrap;
        }

        .transparency-btn:hover {
            background: var(--primary-teal);
            color: #ffffff;
            border-color: var(--primary-teal);
            text-decoration: none;
        }

        /* پاپ‌آپ پیش‌نمایش جزئیات گزارش */
        .report-modal-overlay {
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

        .report-modal-box {
            background: #ffffff;
            border-radius: 28px;
            width: min(640px, 100%);
            max-height: 85vh;
            overflow-y: auto;
            padding: 32px 28px;
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

        .modal-body {
            font-size: 0.98rem;
            color: var(--text-muted);
            line-height: 2;
        }

        .modal-stat-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 0.92rem;
        }

        .modal-stat-table th,
        .modal-stat-table td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border-light);
            text-align: start;
        }

        .modal-stat-table th {
            background: #f8fafc;
            color: var(--text-dark);
            font-weight: 800;
        }

        .modal-stat-table td:last-child {
            font-weight: 800;
            color: var(--primary-teal);
            direction: ltr;
            text-align: left;
        }

        @media (max-width: 900px) {
            .stats-strip {
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

            .stats-strip {
                grid-template-columns: 1fr;
            }

            .reports-grid {
                grid-template-columns: 1fr;
            }

            .report-card-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<main class="page-container">

    <header class="header-capsule">
        <h1><?= site_e('گزارش‌های سالانه') ?></h1>
        <a href="/home" class="breadcrumb-pill">
            <?= site_e('صفحه اصلی / آشنایی با مکسا / گزارش‌های سالانه') ?>
        </a>
    </header>

    <!-- بخش معرفی و تعهد به شفافیت -->
    <section class="intro-banner">
        <div class="intro-banner-text">
            <h2><?= site_e('پایبندی به اصل شفافیت و پاسخگویی') ?></h2>
            <p>
                <?= site_e('مؤسسه نیکوکاری کنترل سرطان ایرانیان (مکسا) با باور عمیق به اصل شفافیت و امانت‌داری، همه‌ساله کارنامه فعالیت‌ها و گزارش تفصیلی عملکرد خود را در ابعاد درمانی، حمایتی، آموزشی، پژوهشی و توسعه شبکه مراقبت‌های تسکینی به محضر جامعه علمی، خیرین ارجمند و عموم هم‌میهنان ارائه می‌نماید. کلیه خدمات مکسا کاملاً رایگان است و منابع آن توسط نیکوکاران و واقفین نیک‌اندیش تأمین می‌شود.') ?>
            </p>
        </div>

        <div class="stats-strip">
            <div class="stat-item">
                <strong><?= site_e('+۳۵۰,۰۰۰') ?></strong>
                <span><?= site_e('خدمت رایگان ارائه‌شده') ?></span>
            </div>
            <div class="stat-item">
                <strong><?= site_e('+۳۲,۰۰۰') ?></strong>
                <span><?= site_e('بیمار مبتلا به سرطان تحت پوشش') ?></span>
            </div>
            <div class="stat-item">
                <strong><?= site_e('۸ مرکز') ?></strong>
                <span><?= site_e('شعب فعال در سطح کشور') ?></span>
            </div>
            <div class="stat-item">
                <strong><?= site_e('۱۰۰٪') ?></strong>
                <span><?= site_e('خدمات کاملاً رایگان') ?></span>
            </div>
        </div>
    </section>

    <!-- نوار فیلتر و عنوان بخش -->
    <section class="reports-toolbar">
        <h2 class="toolbar-title"><?= site_e('آرشیو گزارش‌های عملکرد سالانه') ?></h2>

        <div class="year-filter-tabs">
            <button type="button" class="filter-btn active" data-filter="all"><?= site_e('همه سال‌ها') ?></button>
            <button type="button" class="filter-btn" data-filter="1403"><?= site_e('سال ۱۴۰۳') ?></button>
            <button type="button" class="filter-btn" data-filter="1402"><?= site_e('سال ۱۴۰۲') ?></button>
            <button type="button" class="filter-btn" data-filter="1401"><?= site_e('سال ۱۴۰۱') ?></button>
            <button type="button" class="filter-btn" data-filter="older"><?= site_e('سال‌های قبل') ?></button>
        </div>
    </section>

    <!-- کارت‌های گزارش‌های سالانه -->
    <div class="reports-grid" id="reportsGrid">

        <!-- گزارش ۱۴۰۳ -->
        <article class="report-card" data-year="1403">
            <div>
                <div class="report-card-top">
                    <span class="report-year-badge">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M8 2v3M16 2v3M3.5 9.09h17M21 8.5V17c0 3-1.5 5-5 5H8c-3.5 0-5-2-5-5V8.5c0-3 1.5-5 5-5h8c3.5 0 5 2 5 5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <?= site_e('سال ۱۴۰۳') ?>
                    </span>
                    <span class="report-type-badge"><?= site_e('نسخه نهایی عملکرد') ?></span>
                </div>

                <div class="report-icon-box">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>

                <h3 class="report-title"><?= site_e('گزارش جامع عملکرد سال ۱۴۰۳ مکسا') ?></h3>
                <p class="report-summary">
                    <?= site_e('توسعه خط ملی ارتباطات کشوری، ارائه خدمات سلامت از راه دور (Telemedicine)، پوشش بیش از ۶۵,۰۰۰ خدمت مراقبت در منزل و ارتقای شبکه خدمات تسکینی در قطب‌های دانشگاهی کشور.') ?>
                </p>

                <ul class="report-highlights">
                    <li><?= site_e('۶۸,۴۰۰ ویزیت تخصصی در منزل و کلینیک') ?></li>
                    <li><?= site_e('راه‌اندازی سامانه‌های هوشمند پایش مستمر بیماران') ?></li>
                    <li><?= site_e('تأمین بیش از ۱۲,۰۰۰ قلم تجهیزات پزشکی امانی') ?></li>
                </ul>
            </div>

            <div class="report-card-actions">
                <button type="button" class="btn-view-summary" data-modal="modal-1403">
                    <?= site_e('مشاهده خلاصه') ?>
                </button>
                <a href="#download-1403" class="btn-download-pdf js-download-btn" data-title="<?= site_e('گزارش سالانه ۱۴۰۳') ?>">
                    <?= site_e('دانلود PDF') ?>
                </a>
            </div>
        </article>

        <!-- گزارش ۱۴۰۲ -->
        <article class="report-card" data-year="1402">
            <div>
                <div class="report-card-top">
                    <span class="report-year-badge">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M8 2v3M16 2v3M3.5 9.09h17M21 8.5V17c0 3-1.5 5-5 5H8c-3.5 0-5-2-5-5V8.5c0-3 1.5-5 5-5h8c3.5 0 5 2 5 5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <?= site_e('سال ۱۴۰۲') ?>
                    </span>
                    <span class="report-type-badge"><?= site_e('نسخه مصوب') ?></span>
                </div>

                <div class="report-icon-box">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none"><path d="M9 17v-6M13 17V7M17 17v-3M3 13.5l4-4 4 4 6-6 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>

                <h3 class="report-title"><?= site_e('گزارش جامع عملکرد سال ۱۴۰۲ مکسا') ?></h3>
                <p class="report-summary">
                    <?= site_e('گسترش فعالیت در بیمارستان‌های بزرگ دانشگاهی، تقویت تیم‌های چندتخصصی (پزشکی، پرستاری، روان‌شناختی، مددکاری و معنوی) و پوشش ۵۸,۰۰۰ خدمت تخصصی رایگان.') ?>
                </p>

                <ul class="report-highlights">
                    <li><?= site_e('افزایش ۴۰ درصدی پوشش مراقبت در منزل') ?></li>
                    <li><?= site_e('برگزاری دوره‌های توانمندسازی تخصصی برای ۱,۲۰۰ پرستار') ?></li>
                    <li><?= site_e('تأسیس مرکز نوآوری و رویش استعدادهای دانشجویی (CDST)') ?></li>
                </ul>
            </div>

            <div class="report-card-actions">
                <button type="button" class="btn-view-summary" data-modal="modal-1402">
                    <?= site_e('مشاهده خلاصه') ?>
                </button>
                <a href="#download-1402" class="btn-download-pdf js-download-btn" data-title="<?= site_e('گزارش سالانه ۱۴۰۲') ?>">
                    <?= site_e('دانلود PDF') ?>
                </a>
            </div>
        </article>

        <!-- گزارش ۱۴۰۱ -->
        <article class="report-card" data-year="1401">
            <div>
                <div class="report-card-top">
                    <span class="report-year-badge">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M8 2v3M16 2v3M3.5 9.09h17M21 8.5V17c0 3-1.5 5-5 5H8c-3.5 0-5-2-5-5V8.5c0-3 1.5-5 5-5h8c3.5 0 5 2 5 5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <?= site_e('سال ۱۴۰۱') ?>
                    </span>
                    <span class="report-type-badge"><?= site_e('نسخه مصوب') ?></span>
                </div>

                <div class="report-icon-box">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>

                <h3 class="report-title"><?= site_e('گزارش جامع عملکرد سال ۱۴۰۱ مکسا') ?></h3>
                <p class="report-summary">
                    <?= site_e('انعقاد تفاهم‌نامه‌های همکاری ملی با دانشگاه‌های علوم پزشکی شهید بهشتی، بقیه‌الله، تبریز، اصفهان، مشهد و اهواز؛ توسعه ناوگان مراقبت در منزل و مددکاری اجتماعی فعال.') ?>
                </p>

                <ul class="report-highlights">
                    <li><?= site_e('۴۷,۵۰۰ خدمت کاملاً رایگان به خانواده بیماران') ?></li>
                    <li><?= site_e('تأمین وام‌های حمایتی و بسته‌های معیشتی بیماران نیازمند') ?></li>
                    <li><?= site_e('انتشار ۸ جلد کتاب و راهنمای تخصصی مراقبت تسکینی') ?></li>
                </ul>
            </div>

            <div class="report-card-actions">
                <button type="button" class="btn-view-summary" data-modal="modal-1401">
                    <?= site_e('مشاهده خلاصه') ?>
                </button>
                <a href="#download-1401" class="btn-download-pdf js-download-btn" data-title="<?= site_e('گزارش سالانه ۱۴۰۱') ?>">
                    <?= site_e('دانلود PDF') ?>
                </a>
            </div>
        </article>

        <!-- گزارش ۱۴۰۰ -->
        <article class="report-card" data-year="older">
            <div>
                <div class="report-card-top">
                    <span class="report-year-badge">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M8 2v3M16 2v3M3.5 9.09h17M21 8.5V17c0 3-1.5 5-5 5H8c-3.5 0-5-2-5-5V8.5c0-3 1.5-5 5-5h8c3.5 0 5 2 5 5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <?= site_e('سال ۱۴۰۰') ?>
                    </span>
                    <span class="report-type-badge"><?= site_e('آرشیو رسمی') ?></span>
                </div>

                <div class="report-icon-box">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>

                <h3 class="report-title"><?= site_e('گزارش عملکرد سال ۱۴۰۰ مکسا') ?></h3>
                <p class="report-summary">
                    <?= site_e('مدیریت مراقبت تسکینی بیماران مبتلا به سرطان در دوران کرونا، ارتقای سلامت روان خانواده‌ها، و ارائه ۳۹,۰۰۰ خدمت پیوسته پزشکی و پرستاری در منزل.') ?>
                </p>

                <ul class="report-highlights">
                    <li><?= site_e('تداوم ۱۰۰٪ خدمات بالینی در قرنطینه‌های همه‌گیری') ?></li>
                    <li><?= site_e('پشتیبانی دارویی و تجهیزات تنفسی شبانه‌روزی') ?></li>
                    <li><?= site_e('مشاوره‌های فردی و گروهی تسکین سوگ و اضطراب') ?></li>
                </ul>
            </div>

            <div class="report-card-actions">
                <button type="button" class="btn-view-summary" data-modal="modal-1400">
                    <?= site_e('مشاهده خلاصه') ?>
                </button>
                <a href="#download-1400" class="btn-download-pdf js-download-btn" data-title="<?= site_e('گزارش سالانه ۱۴۰۰') ?>">
                    <?= site_e('دانلود PDF') ?>
                </a>
            </div>
        </article>

        <!-- گزارش ۱۳۹۹ -->
        <article class="report-card" data-year="older">
            <div>
                <div class="report-card-top">
                    <span class="report-year-badge">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M8 2v3M16 2v3M3.5 9.09h17M21 8.5V17c0 3-1.5 5-5 5H8c-3.5 0-5-2-5-5V8.5c0-3 1.5-5 5-5h8c3.5 0 5 2 5 5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <?= site_e('سال ۱۳۹۹') ?>
                    </span>
                    <span class="report-type-badge"><?= site_e('آرشیو رسمی') ?></span>
                </div>

                <div class="report-icon-box">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none"><path d="M3 21h18M3 10h18M3 7l9-4 9 4M4 10v11M20 10v11M8 14v4M12 14v4M16 14v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>

                <h3 class="report-title"><?= site_e('گزارش عملکرد سال ۱۳۹۹ مکسا') ?></h3>
                <p class="report-summary">
                    <?= site_e('گسترش شعب مکسا در اصفهان و تهران، افتتاح بخش مراقبت حمایتی بیمارستان کودکان مفید، و راه‌اندازی کلینیک‌های لنف‌ادم و مراقبت‌های تسکینی سرپایی.') ?>
                </p>

                <ul class="report-highlights">
                    <li><?= site_e('تأسیس بخش مراقبت تسکینی اطفال در تهران') ?></li>
                    <li><?= site_e('پذیرش و همراهی ۳,۱۰۰ بیمار جدید در شبکه مکسا') ?></li>
                    <li><?= site_e('امضای پیمان‌های استانداردهای جهانی با انستیتو کوری فرانسه') ?></li>
                </ul>
            </div>

            <div class="report-card-actions">
                <button type="button" class="btn-view-summary" data-modal="modal-1399">
                    <?= site_e('مشاهده خلاصه') ?>
                </button>
                <a href="#download-1399" class="btn-download-pdf js-download-btn" data-title="<?= site_e('گزارش سالانه ۱۳۹۹') ?>">
                    <?= site_e('دانلود PDF') ?>
                </a>
            </div>
        </article>

    </div>

    <!-- باکس شفافیت و پاسخگویی -->
    <div class="transparency-box">
        <div class="transparency-content">
            <h3><?= site_e('نیاز به اطلاعات بیشتر یا نسخه‌های چاپی دارید؟') ?></h3>
            <p>
                <?= site_e('گزارش‌های تفصیلی سالانه علاوه بر نسخه دیجیتال، به صورت مکتوب نیز به مراجع نظارتی، دانشگاه‌ها و حامیان گرامی ارسال می‌شود. برای درخواست نسخه‌های تکمیلی با دبیرخانه مکسا در ارتباط باشید.') ?>
            </p>
        </div>
        <a href="/contactus" class="transparency-btn">
            <?= site_e('تماس با دبیرخانه مکسا') ?>
        </a>
    </div>

</main>

<!-- مدال پیش‌نمایش خلاصه گزارش‌ها -->
<div id="reportModal" class="report-modal-overlay" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="report-modal-box">
        <div class="modal-header">
            <div>
                <h3 id="modalTitle"><?= site_e('خلاصه گزارش عملکرد') ?></h3>
                <span id="modalYear" class="report-year-badge"><?= site_e('سال ۱۴۰۳') ?></span>
            </div>
            <button type="button" class="modal-close-btn js-modal-close" aria-label="<?= site_e('بستن') ?>">✕</button>
        </div>

        <div class="modal-body" id="modalBody">
            <!-- محتوای داینامیک -->
        </div>
    </div>
</div>

<script>
    // فیلتر تب‌های سال‌ها
    const filterBtns = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.report-card');

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

    // مدال خلاصه گزارش
    const modal = document.getElementById('reportModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalYear = document.getElementById('modalYear');
    const modalBody = document.getElementById('modalBody');
    const closeBtn = document.querySelector('.js-modal-close');

    const summariesData = {
        'modal-1403': {
            title: 'گزارش عملکرد جامع مؤسسه مکسا در سال ۱۴۰۳',
            year: 'سال ۱۴۰۳',
            content: `
                <p>در سال ۱۴۰۳، شبکه خدمت‌رسانی مکسا موفق شد دامنه مراقبت در منزل و خدمات تله‌مدیسین را به طور چشمگیری وسعت بخشد.</p>
                <table class="modal-stat-table">
                    <tr><th>شاخص کلیدی عملکرد</th><th>مقدار ثبت‌شده</th></tr>
                    <tr><td>کل خدمات درمانی و مراقبتی ارائه‌شده</td><td>۶۸,۴۲۰ خدمت</td></tr>
                    <tr><td>ویزیت‌های انجام‌شده در منزل بیمار (Home Care)</td><td>۲۴,۱۵۰ ویزیت</td></tr>
                    <tr><td>مشاوره‌های روان‌شناختی و معنوی فردی و گروهی</td><td>۱۸,۳۰۰ جلسه</td></tr>
                    <tr><td>تجهیزات پزشکی اهدا/امانت داده‌شده</td><td>۱۲,۴۰۰ قلم</td></tr>
                    <tr><td>پوشش هزینه‌ای بیماران نیازمند</td><td>۱۰۰٪ رایگان</td></tr>
                </table>
                <p>این دستاوردها با اتکا به نذورات و حمایت‌های مردمی و مشارکت بیش از ۱۸۰ متخصص حوزه سلامت میسر شده است.</p>
            `
        },
        'modal-1402': {
            title: 'گزارش عملکرد جامع مؤسسه مکسا در سال ۱۴۰۲',
            year: 'سال ۱۴۰۲',
            content: `
                <p>سال ۱۴۰۲ نقطه عطف ارتقای کیفیت مراقبت‌های تسکینی با راه‌اندازی کلینیک‌های ویژه لنف‌ادم و بخش‌های سرپایی جدید بود.</p>
                <table class="modal-stat-table">
                    <tr><th>شاخص کلیدی عملکرد</th><th>مقدار ثبت‌شده</th></tr>
                    <tr><td>کل خدمات ارائه‌شده</td><td>۵۸,۱۰۰ خدمت</td></tr>
                    <tr><td>پرونده‌های جدید پذیرش‌شده</td><td>۵,۴۵۰ بیمار</td></tr>
                    <tr><td>خدمات مددکاری و حمایت دارویی</td><td>۱۴,۲۰۰ مورد</td></tr>
                    <tr><td>ساعت‌های آموزش تخصصی کادر درمان</td><td>۱,۲۰۰ ساعت</td></tr>
                </table>
            `
        },
        'modal-1401': {
            title: 'گزارش عملکرد جامع مؤسسه مکسا در سال ۱۴۰۱',
            year: 'سال ۱۴۰۱',
            content: `
                <p>در سال ۱۴۰۱ شبکه مراقبت در منزل مکسا در استان‌های اصفهان، تهران، قم و خراسان رضوی ساماندهی فراگیر یافت.</p>
                <table class="modal-stat-table">
                    <tr><th>شاخص کلیدی عملکرد</th><th>مقدار ثبت‌شده</th></tr>
                    <tr><td>کل خدمات رایگان</td><td>۴۷,۵۰۰ خدمت</td></tr>
                    <tr><td>ویزیت پزشک و پرستار در منزل</td><td>۱۷,۸۰۰ نوبت</td></tr>
                    <tr><td>خدمات فیزیوتراپی و بازتوانی</td><td>۶,۲۰۰ جلسه</td></tr>
                </table>
            `
        },
        'modal-1400': {
            title: 'گزارش عملکرد جامع مؤسسه مکسا در سال ۱۴۰۰',
            year: 'سال ۱۴۰۰',
            content: `
                <p>حفظ پیوستگی خدمات مراقبت تسکینی در اوج بحران همه‌گیری کرونا و امدادرسانی بی‌وقفه به بیماران در منزل اولویت اصلی سال ۱۴۰۰ بود.</p>
                <table class="modal-stat-table">
                    <tr><th>شاخص کلیدی عملکرد</th><th>مقدار ثبت‌شده</th></tr>
                    <tr><td>کل خدمات ارائه شده</td><td>۳۹,۲۰۰ خدمت</td></tr>
                    <tr><td>امانت کپسول اکسیژن و ونتیلاتور خانگی</td><td>۸,۷۰۰ نوبت</td></tr>
                </table>
            `
        },
        'modal-1399': {
            title: 'گزارش عملکرد جامع مؤسسه مکسا در سال ۱۳۹۹',
            year: 'سال ۱۳۹۹',
            content: `
                <p>تأسیس نخستین بخش‌های تخصصی مراقبت تسکینی اطفال و گسترش پروتکل‌های استاندارد بالینی با همکاری انستیتو کوری فرانسه.</p>
                <table class="modal-stat-table">
                    <tr><th>شاخص کلیدی عملکرد</th><th>مقدار ثبت‌شده</th></tr>
                    <tr><td>کل خدمات ارائه شده</td><td>۳۱,۸۰۰ خدمت</td></tr>
                    <tr><td>پرونده‌های فعال تحت پوشش</td><td>۳,۱۰۰ پرونده</td></tr>
                </table>
            `
        }
    };

    document.querySelectorAll('.btn-view-summary').forEach(btn => {
        btn.addEventListener('click', () => {
            const modalKey = btn.getAttribute('data-modal');
            const data = summariesData[modalKey];
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

    // دانلود شبیه‌سازی شده یا پیام راهنما
    document.querySelectorAll('.js-download-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const title = btn.getAttribute('data-title');
            alert('درخواست دریافت نسخه کامل ' + title + ' ثبت شد. فایل PDF به زودی در دسترس قرار می‌گیرد یا برای دریافت مستقیم با دبیرخانه مکسا تماس حاصل فرمایید.');
        });
    });
</script>

</body>
</html>

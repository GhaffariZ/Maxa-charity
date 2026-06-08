<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <title>پروفایل کادر درمان | مکسا</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

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
  --primary: #0f766e;          /* teal */
  --primary-700:#0b5f59;
  --primary-soft:#e6fffb;

  --accent: #d4af37;           /* طلایی شیک */
  --accent-700:#b78f1e;
  --accent-soft:#fff7d6;

  --dark:#0f172a;
  --muted:#64748b;
  --bg:#f1f5f9;
  --card-bg:#ffffff;
  --border:#e2e8f0;

  --radius-lg:18px;
  --radius-md:12px;
  --shadow-soft:0 24px 60px rgba(15, 23, 42, 0.14);
}


        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Vazirmatn', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top left, #e0f2fe, #f8fafc 40%, #e0f2f1 100%);
            color: var(--dark);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        img {
            max-width: 100%;
            display: block;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 32px 16px 48px;
        }

        .profile-layout {
            width: 100%;
            max-width: 1100px;
            display: grid;
            grid-template-columns: 340px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }
        }

        /* ستون سمت راست: کارت اصلی پروفایل */
        .card-main {
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(18px);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(148, 163, 184, 0.25);
            padding: 28px 28px 24px;
            position: relative;
            overflow: hidden;
        }

        .card-main::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 55%),
                radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.14), transparent 55%);
            opacity: 0.7;
            pointer-events: none;
        }

        .card-main-inner {
            position: relative;
            z-index: 1;
        }

        .profile-header {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
        }

        @media (max-width: 600px) {
            .profile-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        .avatar-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
            flex-shrink: 0;
        }

.avatar-ring{
  background: conic-gradient(
    from 180deg,
    var(--primary),
    #2dd4bf,
    var(--accent),
    var(--primary)
  );
}
        .avatar-inner {
            width: 100%;
            height: 100%;
            border-radius: 999px;
            overflow: hidden;
            background: linear-gradient(135deg, #e2e8f0, #cbd5f5);
            border: 3px solid #f8fafc;
        }

        .avatar-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .status-chip {
            position: absolute;
            bottom: 0;
            left: 0;
            transform: translate(-8px, -4px);
            background: #22c55e;
            color: #ecfdf5;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #bbf7d0;
        }

        .profile-ident {
            flex: 1;
            min-width: 0;
        }

        .name-row {
            display: flex;
            align-items: baseline;
            gap: 6px;
            flex-wrap: wrap;
        }

        .name-row h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #0f172a;
        }

        .name-row .degree {
            font-size: 14px;
            color: var(--muted);
            background: rgba(148, 163, 184, 0.15);
            padding: 3px 8px;
            border-radius: 999px;
        }

        .role {
            margin-top: 6px;
            font-size: 15px;
            color: #1e293b;
            font-weight: 500;
        }

        .sub-meta {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 13px;
            color: var(--muted);
        }

        .sub-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 8px;
            background: rgba(148, 163, 184, 0.08);
            border-radius: 999px;
        }

        .sub-meta span::before {
            content: "•";
            font-size: 14px;
        }

        .badge-maksa {
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.07);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }

        .badge-maksa-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #22c55e;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin: 18px 0 4px;
        }

        @media (max-width: 900px) {
            .meta-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .meta-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .meta-item {
            background: rgba(15, 118, 110, 0.04);
            border-radius: var(--radius-md);
            padding: 10px 12px;
            border: 1px solid rgba(148, 163, 184, 0.35);
        }

        .meta-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .meta-value {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
        }

        .meta-hint {
            font-size: 11px;
            color: #64748b;
            margin-top: 1px;
        }

        .meta-item.highlight {
            background: radial-gradient(circle at top right, #ecfdf5, #e0f2fe);
            border-color: rgba(56, 189, 248, 0.7);
        }

        .meta-item.highlight .meta-value {
            color: #0369a1;
        }

        .section-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            margin: 16px 0 14px;
            border-top: 1px dashed rgba(148, 163, 184, 0.6);
            padding-top: 10px;
        }

        .nav-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 12px;
        }

.nav-pill{
  background: rgba(15,118,110,0.10);
  color: var(--primary-700);
  border: 1px solid rgba(15,118,110,0.22);
}

.nav-pill.active{
  background: linear-gradient(135deg, var(--primary), #14b8a6);
  color: #ecfeff;
  box-shadow: 0 10px 22px rgba(15,118,110,0.35);
  border-color: rgba(15,118,110,0.35);
}


        .profile-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn {
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: 12px;
            padding: 7px 12px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            color: #0f172a;
        }

.btn-primary{
  background: linear-gradient(135deg, var(--primary), var(--accent));
  color: #0b1320;
  border: 1px solid rgba(212,175,55,0.28);
  box-shadow: 0 12px 26px rgba(15,118,110,0.35);
}


        .btn-outline {
            border-color: rgba(148, 163, 184, 0.7);
            background: rgba(255, 255, 255, 0.9);
        }

        .btn-icon {
            font-size: 16px;
            line-height: 0;
        }

        /* محتوا: بیوگرافی و تخصص‌ها */
        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 2.1fr) minmax(0, 1.4fr);
            gap: 18px;
            margin-top: 12px;
        }

        @media (max-width: 900px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .section-box {
            background: rgba(248, 250, 252, 0.9);
            border-radius: var(--radius-md);
            padding: 12px 14px;
            border: 1px solid rgba(226, 232, 240, 0.9);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 6px;
        }

.section-title-pill{
  color: var(--primary-700);
  background: rgba(15,118,110,0.12);
  border: 1px solid rgba(15,118,110,0.18);
}

        .section-body {
            font-size: 13px;
            color: #475569;
        }

        .section-body p {
            margin: 0 0 6px;
        }

        .section-body p:last-child {
            margin-bottom: 0;
        }

        .tags-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .tag {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.18);
            color: #475569;
        }

        .tag.primary {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }

.tag.accent{
  background: rgba(212,175,55,0.18);
  color: var(--accent-700);
  border: 1px solid rgba(212,175,55,0.25);
}


        .list {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 13px;
            color: #475569;
        }

        .list li {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            margin-bottom: 4px;
        }

        .list-bullet { background: var(--primary); }

        .section-footer-note {
            margin-top: 6px;
            font-size: 11px;
            color: #94a3b8;
        }

        /* ستون چپ: کارت تماس و آمار */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .card-sidebar {
            background: rgba(15, 23, 42, 0.97);
            color: #e5e7eb;
            border-radius: var(--radius-lg);
            padding: 18px 18px 16px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 22px 50px rgba(15, 23, 42, 0.7);
        }

        .card-sidebar::before {
            content: "";
            position: absolute;
            inset: -60px;
            background:
                radial-gradient(circle at top left, rgba(45, 212, 191, 0.35), transparent 60%),
                radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.35), transparent 65%);
            opacity: 0.9;
            pointer-events: none;
        }

        .card-sidebar-inner {
            position: relative;
            z-index: 1;
        }

        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 12px;
        }

        .sidebar-title {
            font-size: 13px;
            font-weight: 700;
        }

        .sidebar-subtitle {
            font-size: 11px;
            color: #cbd5f5;
        }

        .badge-availability {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(148, 163, 184, 0.8);
        }

        .contact-list {
            list-style: none;
            padding: 0;
            margin: 0 0 10px;
            font-size: 13px;
        }

        .contact-list li {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .contact-label {
            font-size: 11px;
            color: #cbd5f5;
            min-width: 52px;
        }

        .contact-value {
            font-weight: 500;
            direction: ltr;
        }

        .contact-links {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 6px;
        }

        .contact-chip {
            font-size: 11px;
            padding: 5px 8px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(148, 163, 184, 0.7);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #e5e7eb;
        }

        .contact-chip-icon {
            font-size: 13px;
        }

        .sidebar-footer-note {
            font-size: 10px;
            color: #cbd5f5;
            opacity: 0.95;
            margin-top: 8px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-top: 12px;
        }

        .stat-box {
            background: rgba(15, 23, 42, 0.72);
            border-radius: 12px;
            padding: 8px 10px;
            border: 1px solid rgba(148, 163, 184, 0.7);
        }

        .stat-label {
            font-size: 10px;
            color: #cbd5f5;
            margin-bottom: 2px;
        }

        .stat-value {
            font-size: 16px;
            font-weight: 700;
        }

        .stat-hint {
            font-size: 9px;
            color: #bfdbfe;
        }

        /* کارت مهارت‌ها / حوزه‌های فعالیت */
        .card-skills {
            background: rgba(255, 255, 255, 0.94);
            border-radius: var(--radius-lg);
            padding: 14px 14px 12px;
            border: 1px solid rgba(148, 163, 184, 0.4);
        }

        .skills-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .chips-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 4px;
        }

.chip{
  background: rgba(15,118,110,0.10);
  color: var(--primary-700);
  border: 1px solid rgba(15,118,110,0.18);
}

.chip.soft{
  background: rgba(212,175,55,0.14);
  color: var(--accent-700);
  border: 1px solid rgba(212,175,55,0.22);
}


        .chip.neutral {
            background: #f1f5f9;
            color: #475569;
        }

        .skills-footer {
            margin-top: 8px;
            font-size: 10px;
            color: #94a3b8;
        }
/* Tabs: make pills clickable buttons */
.nav-pill {
    border: none;
    font-family: inherit;
    cursor: pointer;
}

/* Tab panels */
.tab-panel {
    animation: fadeUp 0.18s ease;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* content grid: show panels full width */
.content-grid {
    grid-template-columns: 1fr;
}
/* مخفی کردن تمام پنل‌ها به جز پنل فعال */
.tab-panel[hidden] {
    display: none;
}

/* نمایش پنل فعال */
.tab-panel:not([hidden]) {
    display: block;
}

    </style>
</head>
<body>

<div class="page-wrapper">
    <div class="profile-layout">

        <!-- ستون چپ: تماس + آمار + مهارت‌ها -->
        <aside class="sidebar">

            <!-- کارت تماس و آمار -->
            <section class="card-sidebar">
                <div class="card-sidebar-inner">
                    <div class="sidebar-header">
                        <div>
                            <div class="sidebar-title">اطلاعات تماس و نوبت‌دهی</div>
                            <div class="sidebar-subtitle">مرکز جامع سرطان مکسا</div>
                        </div>
                        <span class="badge-availability">امکان مشاوره حضوری و آنلاین</span>
                    </div>

                    <ul class="contact-list">
                        <li>
                            <span class="contact-label">تلفن:</span>
                            <span class="contact-value">021-XXXXXXX</span>
                        </li>
                        <li>
                            <span class="contact-label">ایمیل:</span>
                            <span class="contact-value">doctor@example.com</span>
                        </li>
                        <li>
                            <span class="contact-label">آدرس:</span>
                            <span>تهران، ... ، مرکز مکسا</span>
                        </li>
                    </ul>

                    <div class="contact-links">
                        <a href="#" class="contact-chip">
                            <span class="contact-chip-icon">in</span>
                            <span>LinkedIn</span>
                        </a>
                        <a href="#" class="contact-chip">
                            <span class="contact-chip-icon">🌐</span>
                            <span>وب‌سایت شخصی</span>
                        </a>
                        <a href="#" class="contact-chip">
                            <span class="contact-chip-icon">📄</span>
                            <span>رزومه PDF</span>
                        </a>
                    </div>

                    <div class="stats-row">
                        <div class="stat-box">
                            <div class="stat-label">سال تجربه</div>
                            <div class="stat-value">۱۲+</div>
                            <div class="stat-hint">در درمان بیماران سرطانی</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">بیمار تحت درمان</div>
                            <div class="stat-value">۸۰۰+</div>
                            <div class="stat-hint">به‌صورت فردی یا تیمی</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-label">کارگاه / سخنرانی</div>
                            <div class="stat-value">۵۰+</div>
                            <div class="stat-hint">آموزش به تیم‌های درمان</div>
                        </div>
                    </div>

                    <div class="sidebar-footer-note">
                        برای هماهنگی نوبت، لطفاً با واحد پذیرش مکسا تماس بگیرید
                        یا از طریق فرم آنلاین نوبت‌دهی اقدام کنید.
                    </div>
                </div>
<!-- جایگزین باکس زمان‌های پاسخ‌گویی: باکس حوزه‌های تخصصی -->
<section class="card-skills">
    <div class="card-header">
        <h3 class="card-title">حوزه‌های تخصصی و علاقه‌مندی</h3>
    </div>

    <div class="card-body">
        <div class="tags-row">
            <span class="tag">مراقبت‌های تسکینی</span>
            <span class="tag">مدیریت درد</span>
            <span class="tag">انکولوژی بالینی</span>
            <span class="tag">حمایت روانی-اجتماعی</span>
            <span class="tag">آموزش خانواده بیماران</span>
        </div>
    </div>
</section>

        </aside>

        <!-- ستون راست: کارت اصلی پروفایل -->
        <main class="card-main">
            <div class="card-main-inner">

                <!-- هدر پروفایل -->
                <header class="profile-header">
                    <div class="avatar-wrapper">
                        <div class="avatar-ring">
                            <div class="avatar-inner">
                                <!-- اینجا آدرس عکس فرد را بگذارید -->
                                <img src="https://via.placeholder.com/400x400.png?text=Doctor+Photo" alt="عکس پروفایل">
                            </div>
                        </div>
                    </div>

                    <div class="profile-ident">
                        <div class="name-row">
                            <h1>دکتر نام و نام‌خانوادگی</h1>
                            <span class="degree">فوق‌تخصص انکولوژی</span>
                        </div>

                        <div class="role">متخصص درمان حمایتی و تسکینی بیماران سرطانی</div>

                        <div class="sub-meta">
                            <span>شماره نظام پزشکی: ۱۲۳۴۵۶</span>
                            <span>مرکز: مکسا - تهران</span>
                            <span>وضعیت: عضو هیئت علمی / همکار تمام‌وقت</span>
                        </div>

                        <div class="badge-maksa">
                            <span class="badge-maksa-dot"></span>
                            <span>همکاری با مکسا از سال ۱۳۹۵ تاکنون</span>
                        </div>
                    </div>
                </header>

                <!-- اطلاعات کلیدی (سوابق مختصر) -->
                <section class="meta-grid">
                    <div class="meta-item">
                        <div class="meta-label">رشته تخصصی</div>
                        <div class="meta-value">داخلی - انکولوژی</div>
                        <div class="meta-hint">فارغ‌التحصیل دانشگاه تهران</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">سال شروع فعالیت</div>
                        <div class="meta-value">۱۳۸۸</div>
                        <div class="meta-hint">بیش از ۱۵ سال سابقه کار بالینی</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">سال همکاری با مکسا</div>
                        <div class="meta-value">۱۳۹۵</div>
                        <div class="meta-hint">عضو تیم چندرشته‌ای مکسا</div>
                    </div>
                    <div class="meta-item highlight">
                        <div class="meta-label">حوزه تمرکز</div>
                        <div class="meta-value">کنترل درد و مراقبت حمایتی</div>
                        <div class="meta-hint">بهبود کیفیت زندگی بیماران</div>
                    </div>
                </section>

                <!-- نوار ابزار / تب‌ها -->
<section class="section-toolbar">
    <div class="nav-pills" role="tablist" aria-label="تب‌های پروفایل">
        <button class="nav-pill active" type="button" role="tab" aria-selected="true" data-tab="bio">
            بیوگرافی حرفه‌ای
        </button>
        <button class="nav-pill" type="button" role="tab" aria-selected="false" data-tab="research">
            سوابق علمی و پژوهشی
        </button>
        <button class="nav-pill" type="button" role="tab" aria-selected="false" data-tab="maksa">
            مسئولیت‌ها در مکسا
        </button>
    </div>
</section>


                <!-- بدنه محتوا -->
<!-- بدنه محتوا: فقط یکی از اینها در هر لحظه نمایش داده می‌شود -->
<!-- بدنه محتوا: فقط این بخش باید باشد -->
<section class="content-grid">

    <!-- پنل ۱: بیوگرافی -->
    <section class="section-box tab-panel" id="tab-bio" data-panel="bio" role="tabpanel">
        <div class="section-header">
            <div class="section-title">بیوگرافی حرفه‌ای <span class="section-title-pill">معرفی کوتاه</span></div>
        </div>
        <div class="section-body">
            <p>دکتر نام و نام‌خانوادگی، متخصص داخلی و فوق‌تخصص انکولوژی، با بیش از ۱۵ سال سابقه فعالیت بالینی در حوزه درمان و مراقبت از بیماران مبتلا به سرطان است. ایشان از سال ۱۳۹۵ به عنوان عضو فعال تیم چندرشته‌ای مرکز مکسا فعالیت می‌کنند.</p>
            <div class="tags-row">
                <span class="tag primary">نگاه انسان‌محور به درمان</span>
                <span class="tag accent">تأکید بر کیفیت زندگی</span>
            </div>
        </div>
    </section>

    <!-- پنل ۲: سوابق علمی و پژوهشی -->
    <section class="section-box tab-panel" id="tab-research" data-panel="research" role="tabpanel" hidden>
        <div class="section-header">
            <div class="section-title">سوابق علمی و پژوهشی <span class="section-title-pill">رزومه علمی</span></div>
        </div>
        <div class="section-body">
            <ul class="list">
                <li><span class="list-bullet"></span><span>چاپ و پذیرش مقالات در حوزه مراقبت حمایتی و تسکینی.</span></li>
                <li><span class="list-bullet"></span><span>ارائه سخنرانی در کنگره‌ها و کارگاه‌های تخصصی مرتبط.</span></li>
                <li><span class="list-bullet"></span><span>مشارکت در پروژه‌های پژوهشی کیفیت زندگی بیماران.</span></li>
            </ul>
        </div>
    </section>

</section>
    <!-- پنل ۳: مسئولیت‌ها -->
    <section class="section-box tab-panel" id="tab-maksa" data-panel="maksa" role="tabpanel" hidden>
        <div class="section-header">
            <div class="section-title">مسئولیت‌ها در مکسا</div>
        </div>
        <div class="section-body">
            <ul class="list">
                <li><span class="list-bullet"></span><span>عضو تیم چندرشته‌ای درمان تسکینی مکسا...</span></li>
            </ul>
        </div>
    </section>

</section>


            </div>
        </main>

    </div>
</div>
<script>
    const tabButtons = document.querySelectorAll('.nav-pills .nav-pill');
    const panels = document.querySelectorAll('.tab-panel');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;

            // active button
            tabButtons.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');

            // show panel
            panels.forEach(p => {
                const isTarget = p.dataset.panel === target;
                if (isTarget) {
                    p.hidden = false;
                    p.classList.add('is-active');
                } else {
                    p.hidden = true;
                    p.classList.remove('is-active');
                }
            });
        });
    });

</script>
</body>
</html>

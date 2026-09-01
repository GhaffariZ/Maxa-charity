<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>کادر درمان و متخصصان | مکسا</title>
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
            --primary-teal: #00a8a8;
            --primary-dark: #006665;
            --primary-light: #e6f7f7;
            --accent-gold: #f5a623;
            --accent-gold-dark: #d97706;
            --header-bg: #eef3f6;
            --text-dark: #1e293b;
            --text-muted: #475569;
            --card-border: #e2e8f0;
            --main-width: 1400px;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: var(--text-dark);
            line-height: 1.8;
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
        }

        .header-capsule h1 {
            font-size: 2.5rem;
            margin: 0 0 15px 0;
            font-weight: 800;
            color: var(--text-dark);
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
        }

        .hero-image-container {
            width: 100%;
            height: 450px;
            margin: 0 auto 40px auto;
            overflow: hidden;
            border-radius: 40px;
            box-shadow: 0 12px 36px rgba(0, 102, 101, 0.12);
        }

        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .article-content {
            padding: 0 10px;
        }

        .article-content h2 {
            font-size: 1.8rem;
            margin-top: 45px;
            margin-bottom: 22px;
            color: var(--primary-teal);
            border-right: 4px solid var(--primary-teal);
            padding-right: 15px;
            font-weight: 700;
        }

        .article-content p {
            font-size: 1.15rem;
            color: var(--text-muted);
            margin-bottom: 20px;
            text-align: justify;
        }

        /* ---------------- بخش تازه‌های علم (Image + Text Box) ---------------- */
        .science-highlight-box {
            display: flex;
            align-items: center;
            gap: 36px;
            background: linear-gradient(135deg, #f8fafc 0%, #eef7f7 100%);
            border: 1px solid var(--card-border);
            border-radius: 28px;
            padding: 32px;
            margin: 35px 0 50px;
            box-shadow: 0 10px 30px -10px rgba(0, 102, 101, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .science-highlight-box:hover {
            box-shadow: 0 16px 40px -10px rgba(0, 102, 101, 0.14);
        }

        .science-text {
            flex: 1.2;
        }

        .science-text h3 {
            font-size: 1.5rem;
            color: var(--primary-dark);
            margin: 0 0 16px 0;
            font-weight: 800;
        }

        .science-text p {
            font-size: 1.08rem;
            color: var(--text-muted);
            line-height: 1.85;
            margin-bottom: 18px;
        }

        .science-badge {
            display: inline-block;
            background: var(--primary-teal);
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 20px;
            margin-bottom: 12px;
        }

        .science-image-wrap {
            flex: 0.9;
            height: 280px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        .science-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* ---------------- شبکه کارت‌های خدمات کادر درمان (6 Cards Grid) ---------------- */
        .doctor-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 26px;
            margin: 35px 0 50px;
        }

        .doctor-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .doctor-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 8px 24px -8px rgba(0, 102, 101, 0.08);
            transition: all 0.35s cubic-bezier(0.25, 1, 0.5, 1);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
        }

        .doctor-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-teal), var(--accent-gold));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 2;
        }

        .doctor-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 20px 40px -12px rgba(0, 102, 101, 0.22);
            border-color: var(--primary-teal);
        }

        .doctor-card:hover::before {
            opacity: 1;
        }

        .doctor-card-img-wrap {
            width: 100%;
            height: 190px;
            overflow: hidden;
            background: #eef2f5;
            position: relative;
        }

        .doctor-card-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .doctor-card:hover .doctor-card-img-wrap img {
            transform: scale(1.06);
        }

        .doctor-card-body {
            padding: 22px 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
        }

        .doctor-card-body h3 {
            font-size: 1.2rem;
            color: var(--text-dark);
            margin: 0 0 10px 0;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .doctor-card:hover .doctor-card-body h3 {
            color: var(--primary-teal);
        }

        .doctor-card-body p {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin: 0 0 16px 0;
            line-height: 1.6;
            text-align: right;
        }

        .doctor-card-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--primary-teal);
            font-size: 0.9rem;
            font-weight: 700;
            margin-top: auto;
        }

        /* ---------------- دپارتمان‌های چندرشته‌ای (MDT Badges & Grid) ---------------- */
        .teams-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin: 30px 0 45px;
        }

        .team-pill-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 18px;
            padding: 20px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }

        .team-pill-card:hover {
            border-color: var(--primary-teal);
            box-shadow: 0 10px 26px -6px rgba(0, 102, 101, 0.12);
            transform: translateY(-3px);
        }

        .team-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--primary-light);
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .team-info h4 {
            font-size: 1.1rem;
            margin: 0 0 6px 0;
            color: var(--text-dark);
            font-weight: 700;
        }

        .team-info p {
            font-size: 0.9rem;
            margin: 0;
            color: var(--text-muted);
            line-height: 1.55;
            text-align: right;
        }

        /* ---------------- کپسول نقل قول و همکاری ---------------- */
        .quote-capsule {
            background: linear-gradient(135deg, rgba(0, 168, 168, 0.06) 0%, rgba(245, 166, 35, 0.08) 100%);
            border-right: 4px solid var(--accent-gold);
            border-radius: 20px;
            padding: 26px 30px;
            margin: 35px 0;
            font-size: 1.15rem;
            color: var(--text-dark);
            font-weight: 500;
            line-height: 1.9;
        }

        /* ---------------- دکمه‌های اقدام سریع ---------------- */
        .cta-banner {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-teal) 100%);
            color: #ffffff;
            border-radius: 26px;
            padding: 36px 30px;
            text-align: center;
            margin: 45px 0 20px;
            box-shadow: 0 16px 36px -10px rgba(0, 102, 101, 0.35);
        }

        .cta-banner h3 {
            font-size: 1.6rem;
            margin: 0 0 14px 0;
            font-weight: 800;
        }

        .cta-banner p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 750px;
            margin: 0 auto 24px;
            line-height: 1.8;
            text-align: center;
        }

        .cta-buttons-wrap {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .cta-btn-primary {
            background: var(--accent-gold);
            color: #1e293b;
            font-weight: 700;
            font-size: 1rem;
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(245, 166, 35, 0.3);
        }

        .cta-btn-primary:hover {
            background: #ffffff;
            color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .cta-btn-outline {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.4);
            font-weight: 700;
            font-size: 1rem;
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(8px);
        }

        .cta-btn-outline:hover {
            background: #ffffff;
            color: var(--primary-dark);
            border-color: #ffffff;
            transform: translateY(-2px);
        }

        /* ---------------- بخش معرفی و رزومه کادر درمان (Resume Showcase Section) ---------------- */
        .resume-showcase-box {
            background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 50%, #fefce8 100%);
            border: 1.5px solid #ccfbf1;
            border-radius: 28px;
            padding: 38px 36px;
            margin: 45px 0 50px;
            box-shadow: 0 16px 36px -12px rgba(13, 148, 136, 0.12);
            display: flex;
            align-items: center;
            gap: 36px;
            position: relative;
            overflow: hidden;
            transition: all 0.35s ease;
        }

        .resume-showcase-box::before {
            content: '';
            position: absolute;
            top: -60px;
            left: -60px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(0, 168, 168, 0.15) 0%, rgba(0, 168, 168, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .resume-showcase-box:hover {
            box-shadow: 0 22px 48px -12px rgba(13, 148, 136, 0.20);
            border-color: var(--primary-teal);
            transform: translateY(-2px);
        }

        .resume-showcase-content {
            flex: 1.35;
        }

        .resume-badge-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .resume-badge-main {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-teal));
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 168, 168, 0.25);
        }

        .resume-badge-tag {
            display: inline-block;
            background: rgba(245, 166, 35, 0.15);
            color: var(--accent-gold-dark);
            border: 1px solid rgba(245, 166, 35, 0.35);
            font-size: 0.8rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .resume-title {
            font-size: 1.65rem;
            color: var(--primary-dark);
            margin: 0 0 14px 0;
            font-weight: 800;
            line-height: 1.4;
        }

        .resume-desc {
            font-size: 1.05rem;
            color: var(--text-muted);
            line-height: 1.85;
            margin: 0 0 22px 0;
            text-align: justify;
        }

        .resume-features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 26px;
        }

        .resume-feature-card {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(6px);
            transition: all 0.25s ease;
        }

        .resume-feature-card:hover {
            border-color: var(--primary-teal);
            background: #ffffff;
            transform: translateY(-2px);
        }

        .resume-feature-icon {
            font-size: 1.35rem;
            line-height: 1;
            flex-shrink: 0;
        }

        .resume-feature-text strong {
            display: block;
            font-size: 0.88rem;
            color: var(--text-dark);
            font-weight: 700;
        }

        .resume-feature-text small {
            display: block;
            font-size: 0.76rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .resume-action-area {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn-resume-cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--primary-teal) 0%, var(--primary-dark) 100%);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            padding: 13px 26px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 8px 20px -4px rgba(0, 168, 168, 0.4);
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .btn-resume-cta:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #004d4c 100%);
            color: #ffffff;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 26px -4px rgba(0, 102, 101, 0.45);
        }

        .btn-resume-cta svg {
            transition: transform 0.3s ease;
        }

        .btn-resume-cta:hover svg {
            transform: translateX(-4px);
        }

        .resume-action-note {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .resume-showcase-visual {
            flex: 0.95;
            display: flex;
            justify-content: center;
        }

        .resume-preview-widget {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 12px 30px -10px rgba(0, 0, 0, 0.07);
            position: relative;
        }

        .rpw-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 16px;
            margin-bottom: 16px;
        }

        .rpw-avatars {
            display: flex;
            align-items: center;
        }

        .rpw-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            border: 2px solid #ffffff;
            margin-right: -10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .rpw-avatar:first-child {
            margin-right: 0;
        }

        .rpw-av1 { background: #e0f2fe; }
        .rpw-av2 { background: #fef3c7; }
        .rpw-av3 { background: #dcfce7; }
        .rpw-av4 { background: var(--primary-teal); color: #fff; font-size: 14px; font-weight: 800; }

        .rpw-stat-pill {
            text-align: left;
        }

        .rpw-stat-pill strong {
            display: block;
            font-size: 1.1rem;
            color: var(--primary-dark);
            font-weight: 800;
            line-height: 1.1;
        }

        .rpw-stat-pill span {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .rpw-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 0;
        }

        .rpw-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.85rem;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .rpw-item:hover {
            border-color: var(--card-border);
            background: #f1f5f9;
        }

        .rpw-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary-teal);
            flex-shrink: 0;
        }

        .rpw-item-title {
            font-weight: 700;
            color: var(--text-dark);
            flex-grow: 1;
        }

        .rpw-item-tag {
            font-size: 0.72rem;
            background: #ffffff;
            color: var(--text-muted);
            padding: 2px 8px;
            border-radius: 8px;
            border: 1px solid var(--card-border);
        }

        .rpw-footer-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 10px;
            border-radius: 12px;
            background: var(--primary-light);
            color: var(--primary-dark);
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .rpw-footer-link:hover {
            background: var(--primary-teal);
            color: #ffffff;
        }

        /* ---------------- انیمیشن‌های ورودی ---------------- */
        @keyframes maxaFadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .wow, .fadeInUp {
            opacity: 0;
            animation: maxaFadeInUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        [data-wow-delay="0.2s"] { animation-delay: 0.2s; }
        [data-wow-delay="0.4s"] { animation-delay: 0.4s; }
        [data-wow-delay="0.6s"] { animation-delay: 0.6s; }
        [data-wow-delay="0.65s"] { animation-delay: 0.65s; }
        [data-wow-delay="0.7s"] { animation-delay: 0.7s; }
        [data-wow-delay="0.8s"] { animation-delay: 0.8s; }
        [data-wow-delay="1.0s"] { animation-delay: 1.0s; }

        @media (prefers-reduced-motion: reduce) {
            .wow, .fadeInUp { opacity: 1 !important; animation: none !important; }
        }

        /* ---------------- ریسپانسیو ---------------- */
        @media (max-width: 992px) {
            .header-capsule h1 { font-size: 2rem; }
            .hero-image-container { height: 360px; }
            .doctor-cards-grid { grid-template-columns: repeat(2, 1fr); }
            .science-highlight-box { flex-direction: column-reverse; padding: 24px; }
            .science-image-wrap { width: 100%; height: 220px; }
            .resume-showcase-box { flex-direction: column; padding: 28px 24px; }
            .resume-showcase-visual { width: 100%; }
            .resume-preview-widget { max-width: 100%; }
            .resume-features-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .page-container { margin: 20px auto; }
            .hero-image-container { height: 250px; border-radius: 20px; }
            .header-capsule { border-radius: 30px; padding: 30px 15px; }
            .header-capsule h1 { font-size: 1.6rem; }
            .article-content h2 { font-size: 1.4rem; }
            .article-content p { font-size: 1rem; }
            .doctor-cards-grid { grid-template-columns: 1fr; gap: 20px; }
            .teams-grid { grid-template-columns: 1fr; }
            .resume-showcase-box { padding: 22px 18px; border-radius: 20px; }
            .resume-title { font-size: 1.35rem; }
            .btn-resume-cta { width: 100%; justify-content: center; }
            .resume-action-area { flex-direction: column; align-items: stretch; }
        }

        @media (max-width: 380px) {
            .header-capsule h1 { font-size: 1.35rem; }
            .hero-image-container { height: 200px; border-radius: 16px; }
        }
    </style>
</head>
<body>

    <main class="page-container">
        
        <header class="header-capsule">
            <h1>همراه با متخصصان و کادر درمان</h1>
            <div class="breadcrumb-pill">صفحه اصلی / خدمات / کادر درمان</div>
        </header>

        <div class="hero-image-container">
            <img src="{{image1}}" alt="کادر درمان و متخصصان مکسا" class="hero-image">
        </div>
        
        <article class="article-content">
            <h2 class="wow fadeInUp" data-wow-delay="0.2s">جایگاه کادر درمان در منظومه مراقبت‌های تسکینی مکسا</h2>

            <p class="wow fadeInUp" data-wow-delay="0.3s">
                ارائه مراقبت‌های جامع حمایتی و تسکینی به بیماران مبتلا به سرطان و خانواده‌های آنان، نیازمند هم‌افزایی همه‌جانبه یک تیم چندرشته‌ای (Multidisciplinary Team) متخصص و متعهد است. کادر درمان مکسا شامل پزشکان متخصص، پرستاران بالینی، روانشناسان، مددکاران اجتماعی، متخصصان تغذیه و توانبخشی و مشاوران ژنتیک است که در کنار هم، با اتکا به دانش روز جهانی و مهرورزی انسانی، کیفیت زندگی بیماران را از بدو تشخیص تا مراحل مختلف درمان ارتقا می‌بخشند.
            </p>

            <!-- بخش تازه‌های علم و پژوهش -->
            <div class="science-highlight-box wow fadeInUp" data-wow-delay="0.4s">
                <div class="science-text">
                    <span class="science-badge">🔬 پژوهش و دانش بالینی</span>
                    <h3>تازه‌های علم طب تسکینی و انکولوژی</h3>
                    <p>
                        مکسا به عنوان متولی ملی و علمی مراقبت‌های حمایتی و تسکینی در ایران، با همکاری انستیتو کوری فرانسه و دانشگاه‌های علوم پزشکی برتر کشور، جدیدترین گایدلاین‌های مدیریت درد، تسکین علائم، کنترل عوارض درمان و مداخلات روانشناختی انکولوژی را در قالب پروتکل‌های بالینی کاربردی در اختیار کادر درمان قرار می‌دهد.
                    </p>
                    <a href="/macsapedia" class="doctor-card-action">مشاهده منابع علمی در مکساپدیا ←</a>
                </div>
                <div class="science-image-wrap">
                    <img src="{{image2}}" alt="پژوهش و دانش بالینی مکسا">
                </div>
            </div>

            <h2 class="wow fadeInUp" data-wow-delay="0.5s">خدمات و بسترهای همراهی ویژه همکاران کادر درمان</h2>

            <p class="wow fadeInUp" data-wow-delay="0.5s">
                پزشکان، بیمارستان‌ها، کلینیک‌ها و کادر درمان در سراسر کشور می‌توانند از طریق بسترهای زیر با شبکه گسترده خدمات حمایتی و تسکینی مکسا در ارتباط باشند:
            </p>

            <!-- شبکه ۶ کارت خدمات و همکاری با کادر درمان -->
            <div class="doctor-cards-grid wow fadeInUp" data-wow-delay="0.6s">
                
                <a href="/patientintake" class="doctor-card-link">
                    <div class="doctor-card">
                        <div class="doctor-card-img-wrap">
                            <img src="{{image3}}" alt="ارجاع بیمار به مکسا">
                        </div>
                        <div class="doctor-card-body">
                            <h3>ارجاع بیمار به مکسا</h3>
                            <p>پزشکان و مراکز درمانی می‌توانند بیماران نیازمند مراقبت در منزل، تجهیزات پزشکی و خدمات حمایتی را مستقیماً به مکسا معرفی نمایند.</p>
                            <span class="doctor-card-action">فرآیند ارجاع بیمار ←</span>
                        </div>
                    </div>
                </a>

                <a href="/contactus" class="doctor-card-link">
                    <div class="doctor-card">
                        <div class="doctor-card-img-wrap">
                            <img src="{{image4}}" alt="همکاری علمی و درمانی با مکسا">
                        </div>
                        <div class="doctor-card-body">
                            <h3>همکاری بالینی و علمی</h3>
                            <p>پیوستن به شبکه داوطلبانه و تخصصی پزشکان، درمانگران و مشاوران مکسا برای ارائه خدمات در درمانگاه‌ها و شبکه مراقبت در منزل.</p>
                            <span class="doctor-card-action">همکاری با مکسا ←</span>
                        </div>
                    </div>
                </a>

                <a href="/courses" class="doctor-card-link">
                    <div class="doctor-card">
                        <div class="doctor-card-img-wrap">
                            <img src="{{image5}}" alt="آموزش و دوره‌های تخصصی طب تسکینی">
                        </div>
                        <div class="doctor-card-body">
                            <h3>آموزش‌های مهارتی و دوره‌ها</h3>
                            <p>شرکت در دوره‌های مهارتی و بازآموزی مراقبت‌های حمایتی و تسکینی سرطان ویژه پزشکان، پرستاران و کادر توانبخشی سلامت.</p>
                            <span class="doctor-card-action">مشاهده دوره‌ها ←</span>
                        </div>
                    </div>
                </a>

                <a href="/branches.php" class="doctor-card-link">
                    <div class="doctor-card">
                        <div class="doctor-card-img-wrap">
                            <img src="{{image6}}" alt="راه‌اندازی مراکز جدید با مکسا">
                        </div>
                        <div class="doctor-card-body">
                            <h3>راه‌اندازی مراکز جدید</h3>
                            <p>مشاوره، انتقال پروتکل‌های استاندارد و همکاری مشترک با دانشگاه‌های علوم پزشکی جهت تأسیس بخش‌های طب تسکینی بیمارستانی.</p>
                            <span class="doctor-card-action">شعب و مراکز همکار ←</span>
                        </div>
                    </div>
                </a>

                <a href="/macsapedia" class="doctor-card-link">
                    <div class="doctor-card">
                        <div class="doctor-card-img-wrap">
                            <img src="{{image2}}" alt="منابع علمی مراقبت های حمایتی و تسکینی">
                        </div>
                        <div class="doctor-card-body">
                            <h3>منابع علمی و پروتکل‌ها</h3>
                            <p>دسترسی به کتاب‌ها، گایدلاین‌های ترجمه‌شده، مقالات روز و درس‌نامه‌های کاربردی طب حمایتی و تسکینی سرطان.</p>
                            <span class="doctor-card-action">کتابخانه و مقالات ←</span>
                        </div>
                    </div>
                </a>

                <a href="/iran-situation-in-palliative" class="doctor-card-link">
                    <div class="doctor-card">
                        <div class="doctor-card-img-wrap">
                            <img src="{{image1}}" alt="وضعیت مراقبت های حمایتی و تسکینی">
                        </div>
                        <div class="doctor-card-body">
                            <h3>طب تسکینی در ایران و جهان</h3>
                            <p>بررسی آماری، شاخص‌های ملی و مقایسه تطبیقی جایگاه مراقبت‌های تسکینی ایران با استانداردهای نظام جهانی بهداشت.</p>
                            <span class="doctor-card-action">مطالعه گزارش تحلیلی ←</span>
                        </div>
                    </div>
                </a>

            </div>

            <h2 class="wow fadeInUp" data-wow-delay="0.6s">دپارتمان‌های تخصصی تیم سلامت مکسا (MDT)</h2>

            <p class="wow fadeInUp" data-wow-delay="0.6s">
                کادر درمان مکسا در قالب گروه‌های تخصصی به‌هم‌پیوسته، خدمات رایگان پزشکی و حمایتی را ارائه می‌دهند:
            </p>

            <div class="teams-grid wow fadeInUp" data-wow-delay="0.7s">
                
                <div class="team-pill-card">
                    <div class="team-icon-box">🩺</div>
                    <div class="team-info">
                        <h4>پزشکان طب تسکینی و انکولوژی</h4>
                        <p>ارزیابی بالینی جامع، پروتکل‌های تسکین درد، کنترل علائم گوارشی و تنفسی، و نظارت بر سیر بیماری.</p>
                    </div>
                </div>

                <div class="team-pill-card">
                    <div class="team-icon-box">💉</div>
                    <div class="team-info">
                        <h4>پرستاران بالینی و مراقبت در منزل</h4>
                        <p>مراقبت تخصصی از زخم و استومی، کنترل علائم حیاتی، پورت‌گذاری، شیمی‌درمانی حمایتی و آموزش مراقبت به خانواده.</p>
                    </div>
                </div>

                <div class="team-pill-card">
                    <div class="team-icon-box">🧠</div>
                    <div class="team-info">
                        <h4>روانشناسان سلامت و مشاوره</h4>
                        <p>روان‌درمانی حمایتی، مداخلات مدیریت اضطراب و افسردگی بیماری، مشاوره سوگ و جلسات حمایتی خانوادگی.</p>
                    </div>
                </div>

                <div class="team-pill-card">
                    <div class="team-icon-box">🤝</div>
                    <div class="team-info">
                        <h4>مددکاران اجتماعی بالینی</h4>
                        <p>ارزیابی نیازهای اقتصادی و معیشتی، پیگیری امور بیمه و درمان، و اتصال بیمار به زنجیره خدمات خیریه.</p>
                    </div>
                </div>

                <div class="team-pill-card">
                    <div class="team-info-box team-icon-box">🥗</div>
                    <div class="team-info">
                        <h4>متخصصان تغذیه و رژیم‌درمانی</h4>
                        <p>تنظیم رژیم‌های اختصاصی در دوران درمان، مدیریت بی‌اشتهایی و کاشکسی، و تغذیه بالینی انترال/پارنترال.</p>
                    </div>
                </div>

                <div class="team-pill-card">
                    <div class="team-icon-box">🏃‍♂️</div>
                    <div class="team-info">
                        <h4>فیزیوتراپی و مدیریت لنف‌ادم</h4>
                        <p>بازتوانی حرکتی، ماساژ تخصصی تخلیه لنفاوی (CDT)، بانداژ فشاری و پیشگیری از ناتوانی‌های اسکلتی عضلانی.</p>
                    </div>
                </div>

                <div class="team-pill-card">
                    <div class="team-icon-box">🧬</div>
                    <div class="team-info">
                        <h4>مشاوره ژنتیک و غربالگری</h4>
                        <p>بررسی سندروم‌های توارثی سرطان، ارزیابی شجره‌نامه ژنتیکی و راهنمایی آزمایش‌های تشخیصی زودهنگام خانواده.</p>
                    </div>
                </div>

                <div class="team-pill-card">
                    <div class="team-icon-box">🕊️</div>
                    <div class="team-info">
                        <h4>مشاوران مراقبت معنوی</h4>
                        <p>پاسخ به چالش‌های وجودی، بازیابی آرامش قلبی، امیدبخشی و تقویت تاب‌آوری معنوی بیمار و همراهان.</p>
                    </div>
                </div>

            </div>

            <!-- ========================================================= -->
            <!-- بخش اختصاصی معرفی و دسترسی به رزومه کادر درمان مکسا -->
            <!-- ========================================================= -->
            <section class="resume-showcase-box wow fadeInUp" data-wow-delay="0.65s" aria-label="سامانه رزومه کادر درمان مکسا">
                <div class="resume-showcase-content">
                    <div class="resume-badge-group">
                        <span class="resume-badge-main">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            سامانه معرفی و رزومه
                        </span>
                        <span class="resume-badge-tag">پزشکان و درمانگران مکسا</span>
                    </div>

                    <h2 class="resume-title">آشنایی با پزشکان، درمانگران و کادر تخصصی مکسا</h2>

                    <p class="resume-desc">
                        تیم درمان مکسا متشکل از برجسته‌ترین متخصصان انکولوژی، طب تسکینی، پرستاران بالینی، روانشناسان سلامت، مددکاران، متخصصان تغذیه و توانبخشی است. با مراجعه به سامانه رزومه کادر درمان، می‌توانید سوابق علمی، مدارک دانشگاهی، تجارب بالینی، حوزه‌های تخصصی و شعب محل خدمت هر یک از اعضای کادر درمان را مشاهده فرمایید.
                    </p>

                    <div class="resume-features-grid">
                        <div class="resume-feature-card">
                            <span class="resume-feature-icon">🎓</span>
                            <div class="resume-feature-text">
                                <strong>سوابق علمی و دانشگاهی</strong>
                                <small>مدارک تخصصی و فلوشیپ‌ها</small>
                            </div>
                        </div>
                        <div class="resume-feature-card">
                            <span class="resume-feature-icon">🏥</span>
                            <div class="resume-feature-text">
                                <strong>شعب و دپارتمان‌های خدمت</strong>
                                <small>درمانگاه‌ها و مراقبت در منزل</small>
                            </div>
                        </div>
                        <div class="resume-feature-card">
                            <span class="resume-feature-icon">🩺</span>
                            <div class="resume-feature-text">
                                <strong>حوزه‌های تخصصی و بالینی</strong>
                                <small>طب تسکینی، درد و انکولوژی</small>
                            </div>
                        </div>
                    </div>

                    <div class="resume-action-area">
                        <a href="/network.php?role=treatment" class="btn-resume-cta" id="btn-view-doctor-resumes">
                            <span>مشاهده رزومه و سوابق کادر درمان مکسا</span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                        </a>
                        <span class="resume-action-note">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            امکان فیلتر بر اساس شعبه، تخصص و رشته درمانی
                        </span>
                    </div>
                </div>

                <div class="resume-showcase-visual">
                    <div class="resume-preview-widget">
                        <div class="rpw-top">
                            <div class="rpw-avatars">
                                <div class="rpw-avatar rpw-av1" title="پزشک متخصص">🩺</div>
                                <div class="rpw-avatar rpw-av2" title="پرستار بالینی">💉</div>
                                <div class="rpw-avatar rpw-av3" title="روانشناس سلامت">🧠</div>
                                <div class="rpw-avatar rpw-av4">+</div>
                            </div>
                            <div class="rpw-stat-pill">
                                <strong>۱۰۰+ همکار</strong>
                                <span>کادر درمان و سلامت</span>
                            </div>
                        </div>

                        <div class="rpw-list">
                            <div class="rpw-item">
                                <div class="rpw-dot"></div>
                                <span class="rpw-item-title">پزشکان متخصص و فلوشیپ طب تسکینی</span>
                                <span class="rpw-item-tag">درمانگاه و ستاد</span>
                            </div>
                            <div class="rpw-item">
                                <div class="rpw-dot"></div>
                                <span class="rpw-item-title">پرستاران بالینی و مراقبت در منزل</span>
                                <span class="rpw-item-tag">شبکه هوم‌کر</span>
                            </div>
                            <div class="rpw-item">
                                <div class="rpw-dot"></div>
                                <span class="rpw-item-title">روانشناسان، مددکاران و مشاوران سلامت</span>
                                <span class="rpw-item-tag">تیم جامع MDT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="quote-capsule wow fadeInUp" data-wow-delay="0.7s">
                «مکسا پلی استوار میان جامعه پزشکی و بیماران نیازمند؛ جایی که تخصص بالینی با ایثار و محبت درمی‌آمیزد تا هیچ بیماری در مواجهه با سرطان احساس تنهایی و درماندگی نکند.»
            </div>

            <!-- بنر دعوت به اقدام کادر درمان -->
            <div class="cta-banner wow fadeInUp" data-wow-delay="0.8s">
                <h3>به شبکه همکاران و درمانگران مکسا بپیوندید</h3>
                <p>
                    اگر شما نیز پزشک، پرستار، روانشناس، مددکار یا متخصص حوزه سلامت هستید، دست گرم شما را برای هم‌افزایی علمی و خدمت‌رسانی به بیماران عزیز مبتلا به سرطان به گرمی می‌فشاریم.
                </p>
                <div class="cta-buttons-wrap">
                    <a href="/contactus" class="cta-btn-primary">همکاری با کادر درمان مکسا</a>
                    <a href="/network.php?role=treatment" class="cta-btn-outline">رزومه و پروفایل کادر درمان</a>
                    <a href="/courses" class="cta-btn-outline">مشاهده دوره‌های آموزشی</a>
                </div>
            </div>

        </article>

    </main>

</body>
</html>

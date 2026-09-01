<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شورای عالی مکسا</title>
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
            --header-bg: #eef3f6;
            --text-dark: #1e293b;
            --text-muted: #475569;
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
            margin-top: 40px;
            margin-bottom: 20px;
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

        .article-content ul {
            font-size: 1.1rem;
            color: var(--text-muted);
            padding-right: 24px;
            margin-bottom: 24px;
        }
        .article-content li { margin-bottom: 12px; }

        /* کارت‌های شاخص کمیسیون‌ها و ارکان */
        .highlight-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 22px;
            margin: 30px 0 40px;
        }

        .highlight-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 26px 22px;
            box-shadow: 0 8px 24px -8px rgba(0, 102, 101, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .highlight-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-teal), var(--accent-gold));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .highlight-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px -10px rgba(0, 102, 101, 0.16);
            border-color: var(--primary-teal);
        }

        .highlight-card:hover::before {
            opacity: 1;
        }

        .highlight-card-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-light);
            color: var(--primary-dark);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }

        .highlight-card h3 {
            font-size: 1.25rem;
            margin: 0 0 10px 0;
            color: var(--text-dark);
            font-weight: 700;
        }

        .highlight-card p {
            font-size: 1rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.7;
            text-align: right;
        }

        /* باکس نقل قول یا بیانیه راهبردی */
        .quote-capsule {
            background: linear-gradient(135deg, rgba(0, 168, 168, 0.06) 0%, rgba(245, 166, 35, 0.06) 100%);
            border-right: 4px solid var(--accent-gold);
            border-radius: 16px;
            padding: 24px 28px;
            margin: 35px 0;
            font-size: 1.15rem;
            color: var(--text-dark);
            font-weight: 500;
            line-height: 1.9;
        }

        /* ---------------- entrance animations (CSS-only) ---------------- */
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
        [data-wow-delay="0.8s"] { animation-delay: 0.8s; }
        [data-wow-delay="1.0s"] { animation-delay: 1.0s; }
        [data-wow-delay="1.2s"] { animation-delay: 1.2s; }
        [data-wow-delay="1.4s"] { animation-delay: 1.4s; }

        @media (prefers-reduced-motion: reduce) {
            .wow, .fadeInUp { opacity: 1 !important; animation: none !important; }
        }

        /* ---------------- responsive ---------------- */
        @media (max-width: 992px) {
            .header-capsule h1 { font-size: 2rem; }
            .hero-image-container { height: 360px; }
            .highlight-grid { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
        }
        @media (max-width: 768px) {
            .page-container { margin: 20px auto; }
            .hero-image-container { height: 250px; border-radius: 20px; }
            .header-capsule { border-radius: 30px; padding: 30px 15px; }
            .header-capsule h1 { font-size: 1.6rem; }
            .article-content h2 { font-size: 1.4rem; }
            .article-content p, .article-content ul { font-size: 1rem; }
            .highlight-card { padding: 20px 16px; }
        }
        @media (max-width: 380px) {
            .header-capsule h1 { font-size: 1.35rem; }
            .hero-image-container { height: 200px; border-radius: 16px; }
            .article-content h2 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>

    <main class="page-container">
        
        <header class="header-capsule">
            <h1>شورای عالی مکسا</h1>
            <div class="breadcrumb-pill">صفحه اصلی / آشنایی با مکسا / شورای عالی</div>
        </header>

        <div class="hero-image-container">
            <img src="{{image1}}" alt="شورای عالی مکسا" class="hero-image">
        </div>
        
        <article class="article-content">
            <h2 class="wow fadeInUp" data-wow-delay="0.2s">جایگاه و نقش شورای عالی</h2>

            <p class="wow fadeInUp" data-wow-delay="0.4s">
                «شورای عالی مؤسسه نیکوکاری مکسا»، عالی‌ترین مرجع سیاست‌گذاری کلان، هدایت راهبردی و نظارت عالیه بر مسیر تحقق رسالت‌ها و اهداف اساسنامه‌ای مؤسسه است. این شورا با گردهم‌آوری جمعی از فرهیختگان حوزه سلامت، دانشمندان علوم پزشکی، چهره‌های برجسته علمی و دانشگاهی، مدیران ارشد کشور و خیرین خوش‌نام، وظیفه ریل‌گذاری آینده‌نگرانه و صیانت از ارزش‌های اخلاقی و انسانی مکسا را بر عهده دارد.
            </p>

            <div class="quote-capsule wow fadeInUp" data-wow-delay="0.6s">
                شورای عالی مکسا چشم‌انداز توسعه سلامت و گسترش فرهنگ طب تسکینی در کشور را به عنوان یک وظیفه انسانی و اخلاقی هدایت نموده و تضمین‌کننده جهت‌گیری پایدار و ارزش‌محور مؤسسه در خدمت به بیماران مبتلا به سرطان است.
            </div>

            <h2 class="wow fadeInUp" data-wow-delay="0.8s">رسالت‌ها و اهداف کلان شورا</h2>

            <p class="wow fadeInUp" data-wow-delay="0.6s">
                شورای عالی با نگاهی جامع به نظام سلامت و تحولات علم انکولوژی و مراقبت‌های حمایتی در عرصه ملی و بین‌المللی، اهداف زیر را پیگیری می‌نماید:
            </p>

            <ul class="wow fadeInUp" data-wow-delay="0.8s">
                <li><strong>ترسیم افق‌های راهبردی:</strong> تعیین خط‌مشی‌های بلندمدت و تصویب اسناد راهبردی برای توسعه کمی و کیفی خدمات حمایتی و تسکینی.</li>
                <li><strong>صیانت از استانداردهای علمی و اخلاقی:</strong> نظارت بر انطباق شیوه‌های ارائه خدمت با پروتکل‌های به‌روز سازمان جهانی بهداشت (WHO) و مبانی اخلاق پزشکی.</li>
                <li><strong>تسهیل هم‌افزایی ملی:</strong> ایجاد پل ارتباطی مؤثر میان وزارت بهداشت، دانشگاه‌های علوم پزشکی، مجلس شورای اسلامی و نهادهای سیاست‌گذار سلامت.</li>
                <li><strong>جذب و هدایت سرمایه‌های پایدار اجتماعی:</strong> ترویج فرهنگ وقف، نذر سلامت و نیکوکاری سازمان‌یافته برای پشتیبانی همه‌جانبه از بیماران نیازمند.</li>
            </ul>

            <h2 class="wow fadeInUp" data-wow-delay="0.8s">کمیسیون‌های تخصصی شورای عالی</h2>

            <p class="wow fadeInUp" data-wow-delay="0.6s">
                به منظور بررسی عمیق و کارشناسی تصمیمات، شورای عالی فعالیت‌های خود را در قالب کمیسیون‌های تخصصی چهارگانه سازماندهی کرده است:
            </p>

            <div class="highlight-grid wow fadeInUp" data-wow-delay="0.8s">
                <div class="highlight-card">
                    <div class="highlight-card-icon">🩺</div>
                    <h3>کمیسیون سلامت و طب تسکینی</h3>
                    <p>بررسی و هدایت راهبردی پروتکل‌های بالینی، طب حمایتی، خدمات پرستاری تسکینی و یکپارچه‌سازی مراقبت در منزل با بیمارستان‌های تخصصی.</p>
                </div>

                <div class="highlight-card">
                    <div class="highlight-card-icon">🎓</div>
                    <h3>کمیسیون آموزش و پژوهش</h3>
                    <p>سیاست‌گذاری در حوزه تربیت نیروهای متخصص، ترویج مقالات علمی، برگزاری کنگره‌های بین‌المللی و همکاری با دانشگاه‌های معتبر داخلی و خارجی.</p>
                </div>

                <div class="highlight-card">
                    <div class="highlight-card-icon">🤝</div>
                    <h3>کمیسیون مشارکت‌های مردمی</h3>
                    <p>طراحی الگوهای نوین جلب مشارکت خیرین، گسترش شبکه‌های حامیان سلامت و شفاف‌سازی تخصیص منابع به پروژه‌های حمایتی.</p>
                </div>

                <div class="highlight-card">
                    <div class="highlight-card-icon">🌐</div>
                    <h3>کمیسیون توسعه و روابط بین‌الملل</h3>
                    <p>انتقال دانش روز جهانی از مراکزی چون انستیتو کوری و دانشگاه‌های پیشرو، و ارتقای جایگاه مکسا در مراجع بین‌المللی مراقبت تسکینی.</p>
                </div>
            </div>

            <h2 class="wow fadeInUp" data-wow-delay="0.8s">ارزش‌های بنیادین در تصمیم‌گیری‌ها</h2>

            <p class="wow fadeInUp" data-wow-delay="0.6s">
                شورای عالی مکسا تمامی مصوبات و سیاست‌گذاری‌های خود را بر پایه پنج اصل محوری بنا نهاده است:
            </p>

            <ul class="wow fadeInUp" data-wow-delay="1.0s">
                <li><strong>حفظ کرامت انسانی:</strong> اولویت دادن به آسایش جسمی، روحی، روانی و معنوی بیمار و خانواده در تمام مراحل مواجهه با بیماری.</li>
                <li><strong>عدالت در سلامت:</strong> تضمین دسترسی رایگان و عادلانه همه بیماران مبتلا به سرطان به مراقبت‌های تسکینی بدون تبعیض طبقاتی یا جغرافیایی.</li>
                <li><strong>دانش‌محوری و نوآوری:</strong> به‌کارگیری آخرین دستاوردهای علمی و بهره‌گیری از فناوری‌های نوین مراقبت از راه دور (Telemedicine).</li>
                <li><strong>شفافیت و امانت‌داری:</strong> پایبندی کامل به پاسخگویی سازمانی و ارائه گزارش‌های دقیق به مردم شریف ایران و حامیان نیکوکار.</li>
            </ul>

            <h2 class="wow fadeInUp" data-wow-delay="0.8s">افق پیش‌رو</h2>

            <p class="wow fadeInUp" data-wow-delay="1.2s">
                شورای عالی مکسا متعهد است با اتکال به الطاف الهی و همراهی پرسنل متعهد، خیرین گران‌قدر و جامعه پزشکی کشور، شبکه جامع مراقبت‌های حمایتی و تسکینی را در سراسر ایران عزیز گسترش داده و جامعه‌ای توانمند، آرام و امیدوار را در مواجهه با بیماری سرطان محقق سازد.
            </p>
        </article>

    </main>

</body>
</html>

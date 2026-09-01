<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>هیئت مدیره مکسا</title>
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

        /* کارت‌های وظایف و کمیته‌ها */
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
            <h1>هیئت مدیره مکسا</h1>
            <div class="breadcrumb-pill">صفحه اصلی / آشنایی با مکسا / هیئت مدیره</div>
        </header>

        <div class="hero-image-container">
            <img src="{{image1}}" alt="هیئت مدیره مکسا" class="hero-image">
        </div>
        
        <article class="article-content">
            <h2 class="wow fadeInUp" data-wow-delay="0.2s">جایگاه و نقش هیئت مدیره</h2>

            <p class="wow fadeInUp" data-wow-delay="0.4s">
                «هیئت مدیره مؤسسه خیریه حمایت از بیماران مبتلا به سرطان – مکسا»، رکن عالی اداره امور اجرایی و نماینده قانونی مؤسسه در کلیه مراجع رسمی و قانونی کشور است. اعضای هیئت مدیره با تلفیقی از تخصص‌های پزشکی، مدیریتی، مالی و حقوقی، مسئولیت نظارت مستمر بر حسن اجرای مصوبات مجمع عمومی و شورای عالی، تصویب برنامه‌ها و بودجه سالانه، و هدایت مدیرعامل و معاونت‌های تخصصی را بر عهده دارند.
            </p>

            <div class="quote-capsule wow fadeInUp" data-wow-delay="0.6s">
                هیئت مدیره مکسا با تکیه بر اصول حاکمیت سازمانی شفاف و پاسخگو، مأموریت ارائه خدمات رایگان و استاندارد حمایتی و تسکینی را با بالاترین بهره‌وری و صیانت از اعتماد خیرین گرامی به انجام می‌رساند.
            </div>

            <h2 class="wow fadeInUp" data-wow-delay="0.8s">وظایف و حدود اختیارات هیئت مدیره</h2>

            <p class="wow fadeInUp" data-wow-delay="0.6s">
                طبق اساسنامه رسمی مؤسسه، اهم وظایف و اختیارات هیئت مدیره شامل موارد زیر می‌باشد:
            </p>

            <ul class="wow fadeInUp" data-wow-delay="0.8s">
                <li><strong>هدایت اجرایی و تصویب آیین‌نامه‌ها:</strong> تدوین و تصویب آیین‌نامه‌های مالی، معاملاتی، اداری، درمانی و استخدامی مؤسسه متناسب با نیازهای روز.</li>
                <li><strong>بررسی و تصویب بودجه و تراز مالی:</strong> نظارت مستمر بر انضباط مالی، رسیدگی به ترازنامه سالانه و ارائه گزارش‌های مالی حسابرسی‌شده به بازرس قانونی و مجمع.</li>
                <li><strong>انتخاب و نظارت بر مدیرعامل:</strong> انتصاب مدیرعامل، تعیین حدود اختیارات و نظارت بر عملکرد اجرایی وی و مدیران ارشد مراکز تابعه.</li>
                <li><strong>توسعه شعب و پایگاه‌های درمانی:</strong> تصویب طرح‌های راه‌اندازی شعب جدید در استان‌های مختلف کشور و انعقاد تفاهم‌نامه‌های همکاری با بیمارستان‌ها و دانشگاه‌های علوم پزشکی.</li>
                <li><strong>حفظ حقوق قانونی و اموال مؤسسه:</strong> دفاع از منافع قانونی مؤسسه، صیانت از موقوفات و اموال غیرمنقول، و انعقاد قراردادهای رسمی با اشخاص حقیقی و حقوقی.</li>
            </ul>

            <h2 class="wow fadeInUp" data-wow-delay="0.8s">کمیته‌های تخصصی ذیل هیئت مدیره</h2>

            <p class="wow fadeInUp" data-wow-delay="0.6s">
                به منظور افزایش بهره‌وری و نظارت دقیق بر فرآیندهای حساس، کمیته‌های تخصصی زیر تحت نظارت مستقیم هیئت مدیره فعالیت می‌کنند:
            </p>

            <div class="highlight-grid wow fadeInUp" data-wow-delay="0.8s">
                <div class="highlight-card">
                    <div class="highlight-card-icon">📊</div>
                    <h3>کمیته حسابرسی و انطباق</h3>
                    <p>پایش مداوم اسناد مالی، انطباق با قوانین مالیاتی و خیریه‌ای، و اطمینان از سلامت کامل فرآیندهای مالی مؤسسه با همکاری حسابرسان رسمی.</p>
                </div>

                <div class="highlight-card">
                    <div class="highlight-card-icon">🏥</div>
                    <h3>کمیته کیفیت خدمات بالینی</h3>
                    <p>نظارت بر استانداردسازی خدمات مراقبت در منزل، کلینیک‌های سرپایی، ایمنی بیمار و سنجش مستمر رضایت بیماران و خانواده‌ها.</p>
                </div>

                <div class="highlight-card">
                    <div class="highlight-card-icon">🏗️</div>
                    <h3>کمیته توسعه و زیرساخت</h3>
                    <p>برنامه‌ریزی برای گسترش مراکز فیزیکی، تأمین و نگهداری تجهیزات پیشرفته پزشکی و توسعه سامانه‌های دیجیتال و پرونده الکترونیک سلامت.</p>
                </div>

                <div class="highlight-card">
                    <div class="highlight-card-icon">⚖️</div>
                    <h3>کمیته حقوقی و قراردادها</h3>
                    <p>بررسی حقوقی قراردادها، تفاهم‌نامه‌های دانشگاهی و سازمانی، و صیانت از چارچوب‌های قانونی فعالیت‌های عام‌المنفعه مؤسسه.</p>
                </div>
            </div>

            <h2 class="wow fadeInUp" data-wow-delay="0.8s">رویکرد حاکمیت سازمانی و شفافیت</h2>

            <p class="wow fadeInUp" data-wow-delay="0.6s">
                مکسا به عنوان سازمانی پیشرو در حوزه نیکوکاری، مدل حاکمیت سازمانی خود را بر پایه‌های شفافیت، امانت‌داری و پاسخگویی بنا نهاده است:
            </p>

            <ul class="wow fadeInUp" data-wow-delay="1.0s">
                <li><strong>حسابرسی سالانه مستقل:</strong> ارائه صورت‌های مالی به مؤسسات حسابرسی معتمد عضو جامعه حسابداران رسمی ایران.</li>
                <li><strong>گزارش‌دهی مستمر به افکار عمومی:</strong> انتشار سالنامه آماری خدمات، گزارش عملکرد مالی و پروژه‌های توسعه‌ای برای حامیان و جامعه.</li>
                <li><strong>جلسات منظم ادواری:</strong> تشکیل مستمر جلسات هیئت مدیره با حضور بازرسان قانونی و ثبت دقیق مصوبات و پیگیری پیشرفت اقدامات.</li>
            </ul>

            <h2 class="wow fadeInUp" data-wow-delay="0.8s">ارتباط با مدیریت ارشد</h2>

            <p class="wow fadeInUp" data-wow-delay="1.2s">
                هیئت مدیره مکسا همواره مشتاق دریافت دیدگاه‌ها، نظرات تخصصی و پیشنهادات ارزشمند خیرین، متخصصان نظام سلامت و عموم هم‌میهنان گرامی در راستای بهبود مستمر کیفیت خدمات تسکینی است.
            </p>
        </article>

    </main>

</body>
</html>

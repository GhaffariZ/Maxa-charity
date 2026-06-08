<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحه مقاله استاندارد</title>
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
    </style>

    <style>
        :root {
            --primary-teal: #00a8a8;
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
        }

        .breadcrumb-pill {
            background-color: var(--primary-teal);
            color: white;
            padding: 6px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            display: inline-block;
        }

        /* اصلاح بخش تصویر */
        .hero-image-container {
            width: 100%;
            height: 450px; /* ارتفاع ثابت */
            margin: 0 auto 40px auto;
            overflow: hidden; /* این باعث می‌شود بخش‌های اضافی عکس کراپ شود */
            border-radius: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: cover; /* کلید حل مشکل شما: تصویر را مجبور می‌کند کادر را پر کند */
            object-position: center; /* تصویر را از مرکز تراز می‌کند */
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
        .article-content li { margin-bottom: 10px; }

        /* ---------------- entrance animations (CSS-only) ---------------- */
        @keyframes maxaFadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .wow, .fadeInUp {
            opacity: 0;
            animation: maxaFadeInUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        [data-wow-delay="0.6s"] { animation-delay: 0.6s; }
        [data-wow-delay="0.8s"] { animation-delay: 0.8s; }
        [data-wow-delay="1.2s"] { animation-delay: 1.2s; }
        [data-wow-delay="1.4s"] { animation-delay: 1.4s; }
        @media (prefers-reduced-motion: reduce) {
            .wow, .fadeInUp { opacity: 1 !important; animation: none !important; }
        }

        /* ---------------- responsive ---------------- */
        @media (max-width: 992px) {
            .header-capsule h1 { font-size: 2rem; }
            .hero-image-container { height: 360px; }
        }
        @media (max-width: 768px) {
            .page-container { margin: 20px auto; }
            .hero-image-container { height: 250px; border-radius: 20px; }
            .header-capsule { border-radius: 30px; padding: 30px 15px; }
            .header-capsule h1 { font-size: 1.6rem; }
            .article-content h2 { font-size: 1.4rem; }
            .article-content p, .article-content ul { font-size: 1rem; }
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
            <h1>تاریخچه و نحوه تاسیس</h1>
            <div class="breadcrumb-pill">صفحه اصلی / خدمات</div>
        </header>

        <div class="hero-image-container">
            <!-- در اینجا آدرس عکس خود را قرار دهید -->
            <img src="{{image1}}" alt="Hero Image" class="hero-image">
        </div>
        
        <article class="article-content">
                            <p class="wow fadeInUp">سرطان، یکی از بزرگ‌ترین چالش‌های نظام سلامت در سراسر دنیاست؛ بیماری‌ای که تنها به جسم بیمار محدود نمی‌شود، بلکه خانواده و جامعه را نیز تحت تأثیر قرار می‌دهد. در چنین شرایطی، مراقبت‌های حمایتی و تسکینی جایگاهی حیاتی دارند؛ مراقبت‌هایی که نه‌تنها به بهبود کیفیت زندگی بیماران کمک می‌کنند، بلکه بار سنگین درمان را از دوش خانواده‌ها و نظام سلامت کاهش می‌دهند. </p>

                            <p class="wow fadeInUp">در ایران، تا سال‌های پایانی دهه ۱۳۸۰، خدمات حمایتی و تسکینی به‌صورت پراکنده و محدود ارائه می‌شد و خلأ یک نهاد سازمان‌یافته و تخصصی در این زمینه به‌وضوح احساس می‌شد. همین دغدغه باعث شد که در سال ۱۳۸۷، جمعی از متخصصان حوزه سلامت و مدیران باتجربه با حمایت خیرین و علاقه‌مندان به حوزه سرطان گرد هم آیند تا پاسخی برای این نیاز بیابند.</p>

                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">آغاز یک مسیر تازه</h2>


                            <p class="wow fadeInUp" data-wow-delay="0.6s">در سال ۱۳۸۸، این تلاش‌ها به ثمر نشست و «مؤسسه خیریه حمایت از بیماران مبتلا به سرطان – مکسا» با مجوز وزارت کشور فعالیت رسمی خود را آغاز کرد. رسالت اصلی مکسا از همان ابتدا روشن بود: تولید دانش و ارائه خدمات حمایتی و تسکینی مبتنی بر استانداردهای جهانی. سازمان جهانی بهداشت (WHO) چهار محور کلیدی را در کنترل سرطان معرفی کرده است:
۱. پیشگیری
۲. تشخیص بهنگام
۳. تشخیص و درمان
۴. مراقبت‌های حمایتی و تسکینی</p>

                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">نخستین گام‌ها</h2>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">اولین اقدام عملی مکسا، راه‌اندازی درمانگاه طب تسکینی در مجتمع بیمارستانی امید اصفهان در سال ۱۳۸۹ بود. این مرکز به‌سرعت مورد استقبال بیماران و خانواده‌ها قرار گرفت و الگویی تازه برای ارائه خدمات حمایتی در کشور شد. در ادامه، شبکه مراقبت در منزل اصفهان (۱۳۹۱) و کلینیک بازتوانی پس از درمان اصفهان (۱۳۹۲) آغاز به کار کردند.</p>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">با گسترش تجربه‌های موفق، فعالیت مکسا به تهران نیز رسید. در سال ۱۳۹۲ مرکز مراقبت‌های حمایتی و تسکینی بیمارستان فیروزگر افتتاح شد. کمی بعد، مکسا ستاد کشوری خود را در تهران راه‌اندازی کرد و در سال ۱۳۹۳، با امضای تفاهم‌نامه‌ای با وزارت بهداشت، درمان و آموزش پزشکی، رسماً به‌عنوان متولی ملی خدمات حمایتی و تسکینی سرطان معرفی شد.</p>


                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">توسعه در سراسر کشور</h2>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">از آن پس، فعالیت‌های مکسا در سطح ملی گسترش یافت. مراکز جدیدی در بیمارستان‌های بزرگ تهران از جمله حضرت فاطمه(س)، شریعتی، کودکان مفید و حضرت رسول اکرم(ص) افتتاح شد. شبکه‌های مراقبت در منزل نیز در شهرهای مختلف از جمله قم، کاشان، مشهد، کرمان، اهواز و تبریز راه‌اندازی شدند.</p>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">مکسا علاوه بر خدمات بالینی، کلینیک‌های تخصصی دیگری همچون مشاوره ژنتیک و پیشگیری از سرطان، کلینیک لنف‌ادم و واحدهای مراقبت روان‌شناختی را نیز ایجاد کرد. همچنین، با راه‌اندازی مرکز تماس ۲۴ ساعته و مکسای مجازی، امکان دسترسی بیماران به خدمات مشاوره‌ای گسترده‌تر فراهم شد.</p>


                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">همکاری‌های ملی و بین‌المللی</h2>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">مکسا از همان ابتدا کوشید تا فعالیت‌های خود را در چارچوب علمی و مبتنی بر تجربه‌های جهانی توسعه دهد. همکاری با مراکز پیشرو بین‌المللی همچون انستیتو کوری فرانسه و دانشگاه فرایبورگ آلمان فرصت انتقال دانش و همگامی با استانداردهای جهانی را فراهم ساخت.</p>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">در سطح ملی نیز، مکسا با دانشگاه‌های علوم پزشکی معتبر کشور از جمله بقیه‌الله، شهید بهشتی، اصفهان، مشهد، قم، کرمان، یزد، اهواز و تبریز تفاهم‌نامه‌های همکاری امضا کرده است.</p>


                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">تا امروز، مکسا توانسته است:</h2>

                            <ul class="wow fadeInUp" data-wow-delay="1.2s">
                                <li>به بیش از ۴۵ هزار بیمار و خانواده‌هایشان خدمات حمایتی و تسکینی ارائه کند.</li>
                                <li>بیش از ۲۵۰ هزار خدمت رایگان در شبکه مراقبت در منزل ارائه دهد.</li>
                                <li>بیش از ۲۲ هزار بیمار را در بخش‌های بستری تحت مدیریت خود پذیرش کند.</li>
                            </ul>

                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">جایگاه اجتماعی مکسا</h2>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">مکسا تنها یک مؤسسه نیکوکاری نیست، بلکه امروز به‌عنوان بزرگ‌ترین و حرفه‌ای‌ترین سازمان مردم‌نهاد کشور در حوزه مراقبت‌های حمایتی و تسکینی سرطان شناخته می‌شود. این مؤسسه همچنین عضو هیأت مؤسس و هیأت مدیره شبکه ملی تشکل‌های مردمی حوزه سرطان ایران است و دبیرخانه کارگروه مراقبت‌های حمایتی و تسکینی این شبکه را برعهده دارد.</p>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">امروز، پس از سال‌ها تلاش، مکسا توانسته است امید و آرامش را در کنار درمان به بیماران مبتلا به سرطان و خانواده‌هایشان هدیه دهد. با گسترش همکاری‌های علمی، افزایش مراکز ارائه خدمت در کشور و توسعه آموزش‌های تخصصی، مکسا همچنان مسیر خود را برای ارتقای کیفیت زندگی بیماران ادامه می‌دهد.</p>


                            <p class="wow fadeInUp" data-wow-delay="1.4s">امروز مکسا به‌عنوان بزرگ‌ترین همکار نظام سلامت کشور در حوزه مراقبت‌های حمایتی و تسکینی سرطان شناخته می‌شود و همچنان مسیر توسعه خدمات خود را با هدف ارتقای کیفیت زندگی بیماران و خانواده‌هایشان ادامه می‌دهد.</p>
        </article>

    </main>

</body>
</html>

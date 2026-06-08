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
                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">آرمان </h2>

                            <p class="wow fadeInUp">آرمان به عنوان معیاری روشنگر و چراغ هدایت مسئولان و تصمیم‌گیران برای جهت‌دهی تمام قوای سازمان به سمت آن، در صدر اهداف سازمان قرار می‌گیرد.
مکسا آرمان، غایت متعالی و نهایت امید خود را 
دستیابی به جامعه‌ای  توانمند ، راضی  و آرام  در مواجهه با بیماری‌ سرطان در راستای زندگی سعادتمندانه 
می‌داند. </p>


                            <ul class="wow fadeInUp" data-wow-delay="1.2s">
                                <li>نفع‌برنده‌ اصلی مکسا جامعه است که مکسا زمینه ایصال آنها به مطلوب ـ که همان آرامش در قبال بیماری‌ سرطان است ـ را فراهم می‌کند.</li>
                                <li>مکسا در ایصال نفع‌برنده‌ها به آرمان، جلودار، راهبر، صاحب سبک، طراح مدل و الگو است. </li>
                                <li>مکسا تنها بخشی از الزامات ایصال را بر عهده‌ خود نمی‌داند و به تمامی بایسته‌ها و ملزومات آن به صورت همه‌جانبه می‌پردازد.</li>
                            </ul>

                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">ماموریت</h2>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">مکسا مجموعه‌ای است الهام‌بخش، معتقد و ملتزم به اسلام ناب محمدی 9، زمینه‌ساز ظهور حضرت ولی‌عصر، با هدف سامان‌دهی و ارتقاء کیفیت زندگی مخاطبان خود و در دو عرصه مدیریت جامع مراقبت‌های حمایتی و تسکینی  و راهبری ارائه خدمات جامع حمایتی و تسکینی در کشور، با بهره‌گیری از دو رویکرد راهبری و تصدی‌گری، برای خدمت به مبتلایان به انواع سرطان، خانواده‌های مبتلایان به سرطان و مراقبین آنها، افراد مستعد یا در معرض ابتلا به سرطان، بالینگران مبتلایان و بازیگران حوزه سرطان در جامعه در سطح ملی موجودیت می‌یابد</p>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">مکسا، دارای دو عرصه اصلی مأموریتی مستقل شامل مدیریت جامع مراقبت‌های حمایتی و تسکینی و راهبری ارائه خدمات جامع حمایتی و تسکینی در کشور است که می‌توانند با یکدیگر در تعامل باشند. هر دو عرصه از منظر برنامه‌ریزی دارای اصالت هستند و هدف‌گذاری مستقل می‌شوند و می‌توانند مخاطبان مستقل داشته باشند.</p>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">رویکرد اصلی ارائه خدمت در عرصه مدیریت جامع مراقبت‌های حمایتی و تسکینی، رویکرد تصدی‌گری و ارائه مستقیم خدمات از سوی سازمان به مخاطبان است. در پشتیبانی خدمات و در مورد حوزه‌هایی که خود مأموریت اصلی نیستند و پشتیبان مأموریت اصلی محسوب می‌شوند، رویکردهایی مثل مشارکت، برون‌سپاری، قراردادهای پروژه‌ای و راهبری بنا بر شرایط قابل استفاده است.  </p>

                            <p class="wow fadeInUp" data-wow-delay="0.6s"> مکسا با توسعه بسته جامع خدمات حمایتی و تسکینی، رویکرد «مدیریت یکپارچه و قدم به قدم ارائه خدمات جامع حمایتی و تسکینی به مخاطبان» را برای رفع تمامی نیازهای تقاضا شده یا تقاضا نشده از سوی مخاطب از ابتدای ظهور و بروز علائم ابتلا تا پس از فوت، به مبتلا و خانواده آن در دستور قرار می‌دهد. </p>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">رویکرد اصلی ارائه خدمت در حوزه مدیریت دانش مراقبت‌های حمایتی و تسکینی، راهبری بازیگران و مرتبطین عرصه‌های دانشی است. در این راستا حوزه‌های خدمتی بدون متولیِ با کیفیت، با رویکرد تصدی‌گری پایه‌گذاری می‌شوند و پس از کسب سطح معینی از کیفیت، از میزان تصدی‌گری کاسته می‌شود.</p>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">در عرصه فرهنگسازی مراقبت‌های حمایتی و تسکینی، دو رویکرد بالا به پایین و پایین به بالا در رساندن پیام به مخاطبین و جامعه مورد استفاده قرار می‌گیرد. در رویکرد بالا به پایین، پیام‌های ترویج مراقبت از نهادهای بالادست و میانی متولی سلامت، فرهنگ، ادب و هنر به مخاطبین منتقل می‌شود و مکسا، راهبری بازیگران فرهنگی کشور را برعهده می‌گیرد. در رویکرد پایین به بالا، مکسا با محوریت دادن به رویکرد تصدی‌گری، پیام ترویج مراقبت‌های حمایتی و تسکینی را در متن جامعه و به صورت مستقیم به مخاطبان منتقل می‌نماید.</p>


                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">همکاری‌های ملی و بین‌المللی</h2>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">مکسا از همان ابتدا کوشید تا فعالیت‌های خود را در چارچوب علمی و مبتنی بر تجربه‌های جهانی توسعه دهد. همکاری با مراکز پیشرو بین‌المللی همچون انستیتو کوری فرانسه و دانشگاه فرایبورگ آلمان فرصت انتقال دانش و همگامی با استانداردهای جهانی را فراهم ساخت.</p>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">در سطح ملی نیز، مکسا با دانشگاه‌های علوم پزشکی معتبر کشور از جمله بقیه‌الله، شهید بهشتی، اصفهان، مشهد، قم، کرمان، یزد، اهواز و تبریز تفاهم‌نامه‌های همکاری امضا کرده است.</p>


                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">ویژگی‌های ممتاز مکسا</h2>

                            <ul class="wow fadeInUp" data-wow-delay="1.2s">
                                <li>اولین بودن، مبدع بودن و پیشرو بودن.</li>
                                <li>دانش‌محور بودن (دانش تخصصی)</li>
                                <li>به کارگیری دانش مدیریت</li>
                                <li>ارائه خدمات تخصصی رایگان بدون استفاده از تعرفه بیمه (اثبات قابلیت اجرایی این مدل اقتصادی)</li>
                                <li>طراحی ارائه و اداره بسته کامل مراقبت‌های حمایتی و تسکینی</li>
                                <li>تولید و اشاعه دانش تخصصی و مدیریتی</li>
                                <li>معتمد نظام سلامت (وزارت بهداشت و دانشگاه‌های علوم پزشکی) در حوزه آموزش و پژوهش مراقبت‌های حمایتی و تسکینی </li>
                                <li>ارائه ساختارها و عملکرد بر بستر امر اهل بیت</li>
                                <li>نگاه جامع به مخاطبین</li>
                                <li>استفاده همزمان از رویکردهای راهبری و تصدی‌گری</li>
                            </ul>

                            <h2 class="wow fadeInUp" data-wow-delay="0.8s">چشم انداز</h2>

                            <p class="wow fadeInUp" data-wow-delay="0.6s">مکسا در افق ۱۴۰۴ با تکیه بر ارزش‌های انسانی و الهی، نهادی دانش‌محور، جریان‌ساز، تأثیرگذار و الگوساز در حوزه مراقبت‌های حمایتی و تسکینی سرطان خواهد بود.</p>
                            <ul class="wow fadeInUp" data-wow-delay="1.2s">
                                <li>قطب اول کشور در ارائه خدمات جامع حمایتی و تسکینی و ارتقای کیفیت زندگی بیماران و خانواده‌ها، با پوشش بیش از ده درصد بیماران نیازمند.</li>
                                <li>مرجع ملی دانش و مدیریت در حوزه مراقبت‌های حمایتی و تسکینی، با تولید و به‌کارگیری دانش، توسعه مدل‌ها و استانداردها، و پایش مستمر کیفیت خدمات.</li>
                                <li>پیشرو در فرهنگ‌سازی و آموزش، با تربیت نیروی انسانی متخصص و توسعه شبکه‌های علمی، اجتماعی و داوطلبانه در سراسر کشور.</li>
                                <li>نوآور در استفاده از فناوری‌های نوین برای ارائه مراقبت‌های از راه دور و هوشمندسازی خدمات، با هدف دسترسی عادلانه‌تر و افزایش رضایت بیماران.</li>
                            </ul>
                            <p class="wow fadeInUp" data-wow-delay="0.6s">در این چشم‌انداز، مکسا نه‌تنها ارائه‌دهنده خدمت، بلکه مرجع و الگوی ملی در مدیریت، آموزش، فرهنگ‌سازی و بهینه‌سازی نظام مراقبت‌های حمایتی و تسکینی خواهد بود.</p>
        </article>

    </main>

</body>
</html>

<div class="top-bar-wrapper">
    <div class="top-bar-container">
        <!-- این بخش (جستجو و ثبت‌نام) اکنون در سمت چپ قرار می‌گیرد -->
        <div class="top-bar-left-side">
            <div class="top-bar-search">
                <input type="text" placeholder="جستجو...">
                <button type="submit">🔍</button>
            </div>
            <a href="#" class="top-bar-link">ثبت‌نام / ورود</a>
        </div>

        <!-- این بخش (ایمیل و زبان) اکنون در سمت راست قرار می‌گیرد -->
        <div class="top-bar-right-side">
            <a href="mailto:info@macsa.ir" class="top-bar-link">info@macsa.ir ✉️</a>
            <div class="divider"></div>
            <div class="lang-switcher">AR EN <strong>FA</strong></div>
        </div>
    </div>
</div>

    <!-- Vazirmatn Variable Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

 <style>
/* --- استایل یکپارچه Top Bar (هماهنگ با کارت مشاوره) --- */

.top-bar-wrapper {
    position: relative;
    overflow: hidden;
    padding: 10px 0;
    direction: rtl;
    font-family: 'Vazirmatn', sans-serif;
    color: #ffffff;
    
    /* گرادینت اصلی - هماهنگ با کارت مشاوره */
    background:
        radial-gradient(circle at 15% 10%, rgba(255,255,255,0.25), transparent 35%),
        radial-gradient(circle at 85% 90%, rgba(255,255,255,0.15), transparent 40%),
        linear-gradient(155deg, #10aeb8 0%, #07828e 55%, #05646e 100%);

    box-shadow: 0 4px 15px rgba(15,159,170,0.2);
    border-bottom: 1px solid rgba(255,255,255,0.18);
}

/* افکت‌های حبابی پس‌زمینه */
.top-bar-wrapper::before {
    content: "";
    position: absolute;
    width: 140px;
    height: 140px;
    left: -60px;
    bottom: -70px;
    border-radius: 50%;
    background: rgba(255,255,255,0.12);
    pointer-events: none;
}

.top-bar-wrapper::after {
    content: "";
    position: absolute;
    width: 90px;
    height: 90px;
    right: -35px;
    top: -40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.12);
    pointer-events: none;
}

/* کانتینر اصلی - برای اینکه محتوا روی افکت‌ها قرار بگیرد */
.top-bar-container {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    flex-direction: row-reverse; /* برای چیدمان صحیح راست‌چین */
}

/* بخش‌های چپ و راست */
.top-bar-left-side, 
.top-bar-right-side {
    display: flex;
    align-items: center;
    gap: 18px;
}

/* استایل باکس جستجو */
.top-bar-search {
    position: relative;
    display: flex;
    align-items: center;
}

.top-bar-search input {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 6px 12px 6px 35px;
    color: #fff;
    font-size: 13px;
    outline: none;
    width: 160px;
    backdrop-filter: blur(4px);
    transition: 0.3s;
}

.top-bar-search input:focus {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.4);
}

.top-bar-search button {
    position: absolute;
    left: 8px;
    background: none;
    border: none;
    color: #fff;
    cursor: pointer;
    font-size: 14px;
    opacity: 0.8;
}

/* استایل لینک‌ها */
.top-bar-link {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: 0.2s;
}

.top-bar-link:hover {
    color: #ffffff;
    text-shadow: 0 0 8px rgba(255,255,255,0.4);
}

/* جداکننده */
.divider {
    width: 1px;
    height: 16px;
    background-color: rgba(255, 255, 255, 0.3);
}

/* بخش زبان */
.lang-switcher {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
    display: flex;
    gap: 8px;
}

.lang-switcher strong {
    color: #fff;
    font-weight: 700;
}

/* تنظیمات موبایل */
@media (max-width: 768px) {
    .top-bar-container {
        flex-direction: column;
        gap: 12px;
    }
}


</style>
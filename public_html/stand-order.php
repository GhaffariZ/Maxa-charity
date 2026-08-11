<?php
$pageTitle = 'سفارش استند و کارت';
require __DIR__ . '/dashboard/components/header/component.php';

// Get the type of stand from the URL (e.g., ?type=congrats-1)
$type = $_GET['type'] ?? 'congrats-1';

$standData = [
    'congrats-1' => [
        'title' => 'استند تبریک و شادباش - طرح اول',
        'image' => '/uploads/stand/happy/1_cutout.png',
        'price' => '۳۰۰,۰۰۰ تومان',
        'desc' => 'با سفارش این استند، ضمن تبریک به عزیزانتان، حامی بیماران مبتلا به سرطان باشید.'
    ],
    'congrats-2' => [
        'title' => 'استند تبریک و شادباش - طرح دوم',
        'image' => '/uploads/stand/happy/2_cutout.png',
        'price' => '۳۵۰,۰۰۰ تومان',
        'desc' => 'شادی‌های خود را با مهربانی پیوند بزنید.'
    ],
    'condolence-1' => [
        'title' => 'استند تسلیت و ابراز همدردی - طرح اول',
        'image' => '/uploads/stand/sad/1_cutout.png',
        'price' => '۳۰۰,۰۰۰ تومان',
        'desc' => 'تسلی بخش دل بازماندگان و امیدی برای بیماران سرطانی.'
    ],
    'condolence-2' => [
        'title' => 'استند تسلیت و ابراز همدردی - طرح دوم',
        'image' => '/uploads/stand/sad/2_cutout.png',
        'price' => '۴۰۰,۰۰۰ تومان',
        'desc' => 'با اهدای هزینه تاج گل به خیریه، نامی ماندگار از عزیز از دست رفته به یادگار بگذارید.'
    ],
];

if (!array_key_exists($type, $standData)) {
    $type = 'congrats-1';
}

$selectedStand = $standData[$type];
$isCongrats = (strpos($type, 'congrats') !== false);

// If img is passed dynamically, we replace .jpg with _cutout.png so it grabs the transparent version
$imgSrc = !empty($_GET['img']) && strpos($_GET['img'], '{{') === false 
    ? str_replace('.jpg', '_cutout.png', $_GET['img']) 
    : ($selectedStand['image'] ?? '/uploads/stand/happy/1_cutout.png');
?>

<!-- Persian Datepicker CSS -->
<link rel="stylesheet" href="/dashboard/assets/css/persian-datepicker.min.css">

<style>
/* Modern styling for the order page - Matching site's UI/UX */
.so-wrap {
    max-width: var(--cta-container, 1400px);
    margin: 0 auto;
    padding: 56px 20px 90px;
    font-family: 'Vazirmatn', Tahoma, sans-serif;
    direction: rtl;
}
.so-head {
    text-align: center;
    margin-bottom: 48px;
}
.so-head h1 {
    font-size: clamp(28px, 4vw, 36px);
    font-weight: 900;
    color: #2f3437;
    margin: 0 0 12px;
}
.so-head p {
    color: #6b7280;
    font-size: 16px;
    line-height: 2;
    max-width: 680px;
    margin: 0 auto;
}

/* Customizing Datepicker Font and Style */
.datepicker-plot-area {
    font-family: 'Vazirmatn', Tahoma, sans-serif !important;
    border-radius: 12px !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.12) !important;
    border: none !important;
}
.datepicker-plot-area * {
    font-family: 'Vazirmatn', Tahoma, sans-serif !important;
}
.datepicker-plot-area .datepicker-day-view .table-days td.selected span,
.datepicker-plot-area .datepicker-time-view .time-segment:hover {
    background-color: #e53935 !important;
    border-radius: 8px !important;
}
.datepicker-plot-area .datepicker-time-view .up-btn:hover,
.datepicker-plot-area .datepicker-time-view .down-btn:hover {
    background-color: #f3f4f6 !important;
}

/* 
   In RTL, the first element is on the right, the second is on the left. 
   Grid: 1.1fr (Form, Right) and 0.9fr (Image, Left)
*/
.so-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 40px;
    align-items: stretch;
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,.04);
    padding: 40px;
    border: 1px solid #ecedf0;
}

@media (max-width: 960px) { 
    .so-grid { 
        grid-template-columns: 1fr; 
        padding: 24px;
        gap: 32px;
    } 
    .so-image-col {
        order: -1; /* Move image to top on mobile */
    }
}

/* Image Column (Left visually) */
.so-image-col {
    background: #fafbfc;
    border-radius: 16px;
    padding: 32px;
    text-align: center;
    border: 1px dashed #d1d5db;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

/* 3D Stand Model */
.so-scene {
    width: 100%;
    perspective: 1500px;
    display: flex;
    justify-content: center;
    margin-bottom: 24px;
}
.so-stand-model {
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.15s ease-out;
}
.so-face {
    backface-visibility: hidden;
}
.so-front {
    position: relative;
    z-index: 2;
    transform: translateZ(2px);
    background: transparent; /* Transparent for cutout */
}
.so-front img {
    max-width: 100%;
    max-height: 420px;
    width: auto;
    display: block;
    margin: 0 auto;
    /* The image itself is now transparent PNG */
    filter: drop-shadow(0 15px 25px rgba(0,0,0,0.15));
}
.so-glass {
    display: none; /* Disable glass for cutout mode */
}
.so-back {
    position: absolute;
    inset: 0;
    transform: rotateY(180deg) translateZ(2px);
    background: transparent;
    z-index: 1;
}
/* The Canvas on the back */
.so-back-canvas {
    position: absolute;
    top: 5%;
    bottom: 8%;
    left: 15%;
    right: 15%;
    background: #e5e5e5;
    border-radius: 4px;
    box-shadow: inset 0 0 20px rgba(0,0,0,0.05);
}
.so-pole-center {
    position: absolute;
    bottom: 2%;
    left: 50%;
    transform: translateX(-50%);
    width: 12px;
    height: 98%;
    background: linear-gradient(90deg, #d1d5db 0%, #ffffff 50%, #9ca3af 100%);
    border-radius: 6px;
    box-shadow: 2px 0 8px rgba(0,0,0,0.2);
}
.so-pole-base {
    position: absolute;
    bottom: -4%;
    left: 50%;
    transform: translateX(-50%);
    width: 50%;
    height: 28px;
    background: linear-gradient(180deg, #9ca3af 0%, #4b5563 100%);
    border-radius: 8px 8px 4px 4px;
    box-shadow: 0 12px 24px rgba(0,0,0,0.3);
    border-top: 2px solid #e5e7eb;
}
.so-pole-top-bar {
    position: absolute;
    top: 3%;
    left: 10%;
    width: 80%;
    height: 14px;
    background: linear-gradient(180deg, #f3f4f6 0%, #d1d5db 100%);
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* 360 Rotator Styling */
.so-rotator-container {
    width: 100%;
    max-width: 260px;
    margin: 24px auto 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.so-rotator-label {
    font-size: 13.5px;
    font-weight: 700;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 6px;
}
.so-rotator-slider {
    -webkit-appearance: none;
    width: 100%;
    height: 6px;
    background: #e5e7eb;
    border-radius: 4px;
    outline: none;
}
.so-rotator-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #e53935;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(229, 57, 53, 0.4);
    transition: transform 0.15s ease;
}
.so-rotator-slider::-webkit-slider-thumb:hover {
    transform: scale(1.15);
}

.so-price-tag {
    font-size: 19px;
    font-weight: 800;
    color: #e53935;
    margin: 16px 0;
}

.so-image-col .so-badge {
    display: inline-block;
    background: rgba(245, 166, 35, 0.15);
    color: #d97706;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 20px;
}

/* Form Column (Right visually) */
.so-form-col {
    padding: 10px 0;
}

.so-form-col h2 {
    font-size: 22px;
    font-weight: 800;
    color: #2f3437;
    margin: 0 0 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f3f4f6;
}

.so-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media(max-width: 640px){
    .so-field-row { grid-template-columns: 1fr; gap: 0; }
}

.so-field { margin-bottom: 20px; }
.so-field label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 8px;
}
.so-field label .req { color: #e53935; }

.so-field input,
.so-field textarea {
    width: 100%;
    box-sizing: border-box;
    font-family: inherit;
    font-size: 15px;
    color: #1f2937;
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 14px;
    transition: all .2s ease;
}
.so-field textarea { 
    min-height: 110px; 
    resize: vertical; 
}
.so-field input:focus,
.so-field textarea:focus {
    outline: none;
    background: #fff;
    border-color: #f5a623;
    box-shadow: 0 0 0 4px rgba(245, 166, 35, 0.12);
}

/* Submit Button */
.so-submit {
    width: 100%;
    background: linear-gradient(135deg, #e53935, #c62828);
    color: #fff;
    border: none;
    padding: 16px;
    font-size: 17px;
    font-weight: 800;
    border-radius: 12px;
    cursor: pointer;
    transition: all .25s ease;
    box-shadow: 0 8px 20px rgba(198, 40, 40, 0.3);
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    margin-top: 24px;
}
.so-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(198, 40, 40, 0.4);
    background: linear-gradient(135deg, #d32f2f, #b71c1c);
}
.so-submit:active {
    transform: translateY(1px);
    box-shadow: 0 4px 12px rgba(198, 40, 40, 0.2);
}
.so-submit svg { width: 22px; height: 22px; }

.so-note {
    text-align: center;
    font-size: 13.5px;
    color: #6b7280;
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.so-note svg {
    color: #e53935;
    width: 16px;
    height: 16px;
}
</style>

<div class="so-wrap">
    <div class="so-head">
        <h1>ثبت سفارش استند و کارت</h1>
        <p>با سفارش استند و کارت‌های مکسا، پیام محبت‌آمیز خود را به شکلی ماندگار منتقل کنید و امیدبخش مسیر درمان بیماران مبتلا به سرطان باشید.</p>
    </div>

    <div class="so-grid">
        <!-- Right Column (RTL order 1): Form -->
        <div class="so-form-col">
            <h2>اطلاعات سفارش‌دهنده و مراسم</h2>
            <form id="standOrderForm">
                <div class="so-field-row">
                    <div class="so-field">
                        <label>نام و نام خانوادگی شما <span class="req">*</span></label>
                        <input type="text" name="sender_name" required placeholder="مثال: علی احمدی">
                    </div>
                    <div class="so-field">
                        <label>شماره موبایل <span class="req">*</span></label>
                        <input type="tel" name="sender_phone" required placeholder="09123456789">
                    </div>
                </div>

                <div class="so-field">
                    <label><?= $isCongrats ? 'نام گیرنده پیام (صاحب مجلس / عروس و داماد و ...)' : 'نام دریافت‌کننده (یا نام مرحوم/مرحومه)' ?> <span class="req">*</span></label>
                    <input type="text" name="receiver_name" required placeholder="<?= $isCongrats ? 'نام شخصی که تبریک برای ایشان است' : 'نام شخصی که استند برای ایشان ارسال می‌شود' ?>">
                </div>

                <div class="so-field-row">
                    <div class="so-field">
                        <label>تاریخ برگزاری مراسم <span class="req">*</span></label>
                        <input type="text" name="event_date" class="pdate" required placeholder="انتخاب تاریخ" readonly style="background:#fff; cursor:pointer">
                    </div>
                    <div class="so-field">
                        <label>ساعت مراسم <span class="req">*</span></label>
                        <input type="text" name="event_time" class="ptime" required placeholder="انتخاب ساعت" readonly style="background:#fff; cursor:pointer">
                    </div>
                </div>

                <div class="so-field">
                    <label>استان، شهر و آدرس دقیق محل برگزاری <span class="req">*</span></label>
                    <textarea name="event_address" required placeholder="استان، شهر، خیابان، کوچه، پلاک، نام مسجد یا تالار..."></textarea>
                </div>

                <div class="so-field">
                    <label>متن پیام شما (اختیاری)</label>
                    <textarea name="message" placeholder="در صورت تمایل، پیام خاصی که می‌خواهید روی کارت یا استند درج شود را بنویسید..."></textarea>
                </div>

                <button type="submit" class="so-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    افزودن به سبد خرید
                </button>
                <p class="so-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    تمامی اطلاعات شما محفوظ است. عواید این سفارش مستقیماً صرف دارو و درمان بیماران می‌شود.
                </p>
            </form>
        </div>

        <!-- Left Column (RTL order 2): Image -->
        <div class="so-image-col">
            <span class="so-badge">انتخاب شما</span>
            
            <!-- 3D Stand Model -->
            <div class="so-scene">
                <div class="so-stand-model" id="stand3dModel">
                    <div class="so-face so-front">
                        <!-- Fallback image logic handled in HTML if src fails -->
                        <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($selectedStand['title']) ?>" id="standImageFront" onerror="this.src='https://via.placeholder.com/400x600?text=تصویر+استند'">
                        <div class="so-glass"></div>
                    </div>
                    <div class="so-face so-back">
                        <div class="so-back-canvas"></div>
                        <div class="so-pole-center"></div>
                        <div class="so-pole-top-bar"></div>
                        <div class="so-pole-base"></div>
                    </div>
                </div>
            </div>

            <!-- Slider directly under the image -->
            <div class="so-rotator-container">
                <div class="so-rotator-label">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M3 12l5-5M3 12l5 5M21 12l-5-5M21 12l-5 5"></path>
                    </svg>
                    نمای ۳۶۰ درجه استند
                </div>
                <input type="range" min="-180" max="180" value="0" class="so-rotator-slider" id="standRotator">
            </div>

            <div style="margin-top: 36px; text-align: center;">
                <h3 style="margin:0 0 12px; font-size: 20px; font-weight: 800; color: #2f3437;"><?= htmlspecialchars($selectedStand['title']) ?></h3>
                <div class="so-price-tag" style="margin:16px 0;">مبلغ: <?= $selectedStand['price'] ?></div>
                <p style="color: #6b7280; font-size: 14.5px; line-height: 1.8; margin:0;"><?= htmlspecialchars($selectedStand['desc']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- jQuery and Datepicker Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/dashboard/assets/js/datepicker/persian-date.min.js"></script>
<script src="/dashboard/assets/js/datepicker/persian-datepicker.min.js"></script>

<script>
$(document).ready(function() {
    $('.pdate').persianDatepicker({
        format: 'YYYY/MM/DD',
        initialValue: false,
        autoClose: true
    });
    $('.ptime').persianDatepicker({
        format: 'HH:mm',
        onlyTimePicker: true,
        initialValue: false,
        autoClose: true,
        timePicker: {
            enabled: true,
            second: {
                enabled: false
            }
        }
    });
});

document.getElementById('standOrderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = 'در حال پردازش...';
    btn.disabled = true;

    // Simulate an API call or adding to cart
    setTimeout(() => {
        alert('استند با موفقیت به سبد خرید شما افزوده شد! در حال انتقال...');
        btn.innerHTML = originalText;
        btn.disabled = false;
        // window.location.href = '/cart.php'; 
    }, 1500);
});

// 360 Image Rotator
const rotator = document.getElementById('standRotator');
const standModel = document.getElementById('stand3dModel');
if(rotator && standModel) {
    rotator.addEventListener('input', function(e) {
        standModel.style.transform = `rotateY(${e.target.value}deg)`;
    });
}
</script>

<?php
require __DIR__ . '/dashboard/components/footer/component.php';

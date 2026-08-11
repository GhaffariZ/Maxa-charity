<?php
$pageTitle = 'سفارش استند و کارت';
require __DIR__ . '/dashboard/components/header/component.php';

// Get the type of stand from the URL (e.g., ?type=congrats-1)
$type = $_GET['type'] ?? 'congrats-1';

// We map the types to placeholder images and titles since the real images come from the CMS dynamically in event-cards.
// In a real integration, we might pass the image URL or ID via URL, but for now we provide placeholders that match the context.
$standData = [
    'congrats-1' => [
        'title' => 'استند تبریک و شادباش - طرح اول',
        'image' => '/assets/img/congrats-placeholder.jpg',
        'desc' => 'با سفارش این استند، ضمن تبریک به عزیزانتان، حامی بیماران مبتلا به سرطان باشید.'
    ],
    'congrats-2' => [
        'title' => 'استند تبریک و شادباش - طرح دوم',
        'image' => '/assets/img/congrats-placeholder2.jpg',
        'desc' => 'شادی‌های خود را با مهربانی پیوند بزنید.'
    ],
    'condolence-1' => [
        'title' => 'استند تسلیت و ابراز همدردی - طرح اول',
        'image' => '/assets/img/condolence-placeholder.jpg',
        'desc' => 'تسلی بخش دل بازماندگان و امیدی برای بیماران سرطانی.'
    ],
    'condolence-2' => [
        'title' => 'استند تسلیت و ابراز همدردی - طرح دوم',
        'image' => '/assets/img/condolence-placeholder2.jpg',
        'desc' => 'با اهدای هزینه تاج گل به خیریه، نامی ماندگار از عزیز از دست رفته به یادگار بگذارید.'
    ],
];

// Fallback if type is not recognized
if (!array_key_exists($type, $standData)) {
    $type = 'congrats-1';
}

$selectedStand = $standData[$type];
?>

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
.so-image-col img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 12px 24px rgba(0,0,0,.08);
    margin-bottom: 28px;
    background: #fff;
    min-height: 250px; /* Placeholder for missing image */
    object-fit: contain;
}
.so-image-col h3 {
    font-size: 20px;
    font-weight: 800;
    color: #2f3437;
    margin-bottom: 12px;
}
.so-image-col p {
    color: #6b7280;
    font-size: 14.5px;
    line-height: 1.8;
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
                    <label>نام دریافت‌کننده (یا نام مرحوم/مرحومه) <span class="req">*</span></label>
                    <input type="text" name="receiver_name" required placeholder="نام شخصی که استند برای ایشان ارسال می‌شود">
                </div>

                <div class="so-field-row">
                    <div class="so-field">
                        <label>تاریخ برگزاری مراسم <span class="req">*</span></label>
                        <input type="text" name="event_date" required placeholder="مثال: 1402/08/20">
                    </div>
                    <div class="so-field">
                        <label>ساعت مراسم <span class="req">*</span></label>
                        <input type="text" name="event_time" required placeholder="مثال: 16:00 تا 18:00">
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
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 21C12 21 4 14.5 4 9.5C4 7 6 5 8.5 5C10.2 5 11.4 5.9 12 7C12.6 5.9 13.8 5 15.5 5C18 5 20 7 20 9.5C20 14.5 12 21 12 21Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
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
            <!-- Fallback image logic handled in HTML if src fails -->
            <img src="<?= htmlspecialchars($selectedStand['image']) ?>" alt="<?= htmlspecialchars($selectedStand['title']) ?>" id="standImagePreview" onerror="this.src='https://via.placeholder.com/400x600?text=تصویر+استند'">
            <h3><?= htmlspecialchars($selectedStand['title']) ?></h3>
            <p><?= htmlspecialchars($selectedStand['desc']) ?></p>
        </div>
    </div>
</div>

<script>
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
</script>

<?php
require __DIR__ . '/dashboard/components/footer/component.php';

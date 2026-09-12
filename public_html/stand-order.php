<?php
$pageTitle = 'سفارش استند و کارت';
require __DIR__ . '/dashboard/components/header/component.php';

// لود اولیه از دیتابیس در صورت امکان جهت جلوگیری از پرش صفحه
$initialProvinces = [];
$initialStands = [];
$initialBranch = null;

try {
    require_once __DIR__ . '/core/database.php';
    if (isset($pdo)) {
        // استان‌های شعب فعال غیرستادی
        $st = $pdo->query("
            SELECT DISTINCT b.id AS branch_id, b.name AS branch_name, 
                   COALESCE(b.province, b.name) AS province, 
                   COALESCE(b.city, b.name) AS city
            FROM branches b
            LEFT JOIN branch_features bf ON bf.branch_id = b.id AND bf.feature = 'stands'
            WHERE b.is_hq = 0 AND b.status = 'active' AND (bf.enabled = 1 OR bf.enabled IS NULL)
            ORDER BY province ASC
        ");
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $p = trim((string)$r['province']);
            if ($p === '' || str_contains($p, 'ستاد')) continue;
            if (!isset($initialProvinces[$p])) {
                $initialProvinces[$p] = [
                    'province'  => $p,
                    'branch_id' => (int)$r['branch_id'],
                    'cities'    => []
                ];
            }
            $c = trim((string)$r['city']);
            if ($c !== '' && !in_array($c, $initialProvinces[$p]['cities'], true)) {
                $initialProvinces[$p]['cities'][] = $c;
            }
        }
    }
} catch (Throwable $e) {}

$initialProvincesList = array_values($initialProvinces);
$defaultProvince = $initialProvincesList[0]['province'] ?? 'تهران';
$defaultCities = $initialProvincesList[0]['cities'] ?? ['تهران'];
?>

<!-- Persian Datepicker CSS -->
<link rel="stylesheet" href="/dashboard/assets/css/persian-datepicker.min.css">

<style>
.so-wrap {
    max-width: var(--cta-container, 1360px);
    margin: 0 auto;
    padding: 48px 20px 80px;
    font-family: 'Vazirmatn', Tahoma, sans-serif;
    direction: rtl;
}

.so-head {
    text-align: center;
    margin-bottom: 40px;
}
.so-head h1 {
    font-size: clamp(26px, 3.8vw, 34px);
    font-weight: 900;
    color: #1f2937;
    margin: 0 0 10px;
}
.so-head p {
    color: #4b5563;
    font-size: 15.5px;
    line-height: 1.9;
    max-width: 680px;
    margin: 0 auto;
}

.so-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 36px;
    align-items: start;
}
@media (max-width: 992px) {
    .so-grid {
        grid-template-columns: 1fr;
    }
    .so-image-col {
        order: -1;
    }
}

/* Image / Preview Column */
.so-image-col {
    background: #ffffff;
    border-radius: 20px;
    padding: 28px 24px;
    text-align: center;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.06);
    position: sticky;
    top: 24px;
}

.so-branch-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(0, 123, 122, 0.08);
    color: #007b7a;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 20px;
}
.so-branch-badge svg { width: 15px; height: 15px; }

/* 3D Stand Model */
.so-scene {
    width: 100%;
    perspective: 1400px;
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
    min-height: 380px;
    align-items: center;
}
.so-stand-model {
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.12s ease-out;
}
.so-face {
    backface-visibility: hidden;
}
.so-front {
    position: relative;
    z-index: 2;
    transform: translateZ(2px);
    background: transparent;
    border-radius: 8px;
}
.so-front img {
    max-width: 100%;
    max-height: 380px;
    width: auto;
    display: block;
    margin: 0 auto;
    filter: drop-shadow(0 15px 25px rgba(0,0,0,0.14));
    transition: opacity 0.25s ease;
}
.so-back {
    position: absolute;
    inset: 0;
    transform: rotateY(180deg) translateZ(2px);
    background: transparent;
    z-index: 1;
}
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

/* 360 Rotator Slider */
.so-rotator-container {
    width: 100%;
    max-width: 250px;
    margin: 16px auto 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.so-rotator-label {
    font-size: 13px;
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
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #007b7a;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 123, 122, 0.4);
    transition: transform 0.15s ease;
}
.so-rotator-slider::-webkit-slider-thumb:hover {
    transform: scale(1.15);
}

.so-price-box {
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid #f3f4f6;
}
.so-stand-title {
    font-size: 18.5px;
    font-weight: 800;
    color: #1f2937;
    margin: 0 0 8px;
}
.so-price-tag {
    font-size: 20px;
    font-weight: 900;
    color: #007b7a;
    margin: 10px 0;
}
.so-stand-desc {
    color: #6b7280;
    font-size: 13.5px;
    line-height: 1.8;
    margin: 0;
}

/* Form Column */
.so-form-col {
    background: #ffffff;
    border-radius: 20px;
    padding: 32px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.06);
}

.so-form-col h2 {
    font-size: 20px;
    font-weight: 800;
    color: #1f2937;
    margin: 0 0 24px;
    padding-bottom: 14px;
    border-bottom: 2px solid #f3f4f6;
    display: flex;
    align-items: center;
    gap: 10px;
}
.so-form-col h2 svg { color: #007b7a; width: 22px; height: 22px; }

.so-section-title {
    font-size: 15px;
    font-weight: 800;
    color: #374151;
    margin: 24px 0 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.so-section-title::before {
    content: '';
    width: 4px;
    height: 16px;
    background: #007b7a;
    border-radius: 2px;
}

.so-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}
@media(max-width: 640px){
    .so-field-row { grid-template-columns: 1fr; gap: 0; }
}

.so-field { margin-bottom: 18px; }
.so-field label {
    display: block;
    font-size: 13.5px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 8px;
}
.so-field label .req { color: #e53935; }

.so-field input,
.so-field select,
.so-field textarea {
    width: 100%;
    box-sizing: border-box;
    font-family: inherit;
    font-size: 14.5px;
    color: #1f2937;
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 12px 14px;
    transition: all .2s ease;
}
.so-field select {
    cursor: pointer;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: left 14px center;
    padding-left: 36px;
}
.so-field textarea { 
    min-height: 90px; 
    resize: vertical; 
}
.so-field input:focus,
.so-field select:focus,
.so-field textarea:focus {
    outline: none;
    background: #fff;
    border-color: #007b7a;
    box-shadow: 0 0 0 4px rgba(0, 123, 122, 0.12);
}

/* Stand Gallery Selection */
.so-gallery-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 14px;
    overflow-x: auto;
    padding-bottom: 4px;
}
.so-tab-btn {
    background: #f3f4f6;
    border: 1px solid transparent;
    color: #4b5563;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s ease;
    white-space: nowrap;
}
.so-tab-btn.active {
    background: #007b7a;
    color: #fff;
}

.so-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 12px;
    margin-bottom: 22px;
    max-height: 380px;
    overflow-y: auto;
    padding: 4px;
}
.so-stand-card {
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 14px;
    padding: 10px;
    text-align: center;
    cursor: pointer;
    transition: all .2s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.so-stand-card:hover {
    border-color: #4fb2b0;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.so-stand-card.selected {
    border-color: #007b7a;
    background: rgba(0, 123, 122, 0.04);
    box-shadow: 0 0 0 3px rgba(0, 123, 122, 0.2);
}
.so-stand-card-img {
    height: 95px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
}
.so-stand-card-img img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}
.so-stand-card-type {
    font-size: 10.5px;
    font-weight: 800;
    display: inline-block;
    padding: 2px 6px;
    border-radius: 6px;
    margin-bottom: 4px;
}
.so-stand-card-type.congrats {
    background: rgba(22, 163, 122, 0.12);
    color: #16a37a;
}
.so-stand-card-type.condolence {
    background: rgba(107, 114, 128, 0.14);
    color: #4b5563;
}
.so-stand-card-title {
    font-size: 11.5px;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.5;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.so-stand-card-price {
    font-size: 12px;
    font-weight: 900;
    color: #007b7a;
}

.so-gallery-empty {
    text-align: center;
    padding: 24px;
    background: #f9fafb;
    border: 1px dashed #d1d5db;
    border-radius: 12px;
    color: #6b7280;
    font-size: 13.5px;
}

/* Submit Button */
.so-submit {
    width: 100%;
    background: linear-gradient(135deg, #007b7a, #006665);
    color: #fff;
    border: none;
    padding: 15px;
    font-size: 16.5px;
    font-weight: 800;
    border-radius: 12px;
    cursor: pointer;
    transition: all .25s ease;
    box-shadow: 0 8px 20px rgba(0, 123, 122, 0.28);
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 14px;
}
.so-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(0, 123, 122, 0.36);
    background: linear-gradient(135deg, #008f8d, #007b7a);
}
.so-submit:active {
    transform: translateY(1px);
    box-shadow: 0 4px 12px rgba(0, 123, 122, 0.2);
}
.so-submit svg { width: 20px; height: 20px; }

.so-note {
    text-align: center;
    font-size: 13px;
    color: #6b7280;
    margin-top: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.so-note svg {
    color: #007b7a;
    width: 15px;
    height: 15px;
}
</style>

<div class="so-wrap">
    <div class="so-head">
        <h1>ثبت سفارش استند و کارت خیریه</h1>
        <p>با سفارش استند و کارت‌های مکسا، پیام پرمحبت خود را ماندگار سازید و تمام عواید آن را نذر دارو و درمان رایگان بیماران مبتلا به سرطان نمایید.</p>
    </div>

    <div class="so-grid">
        <!-- Right Column: Form & Stand Selection -->
        <div class="so-form-col">
            <h2>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                اطلاعات سفارش و محل برگزاری مراسم
            </h2>

            <form id="standOrderForm">
                <input type="hidden" name="stand_id" id="inputStandId" value="">
                <input type="hidden" name="branch_id" id="inputBranchId" value="">

                <!-- مشخصات سفارش‌دهنده -->
                <div class="so-field-row">
                    <div class="so-field">
                        <label>نام و نام خانوادگی شما <span class="req">*</span></label>
                        <input type="text" name="sender_name" required placeholder="مثال: علی احمدی">
                    </div>
                    <div class="so-field">
                        <label>شماره موبایل جهت هماهنگی <span class="req">*</span></label>
                        <input type="tel" name="sender_phone" required placeholder="09123456789" dir="ltr" pattern="09[0-9]{9}">
                    </div>
                </div>

                <div class="so-field">
                    <label>نام دریافت‌کننده / صاحب مجلس / مرحوم یا مرحومه <span class="req">*</span></label>
                    <input type="text" name="receiver_name" required placeholder="نام شخص یا خانواده‌ای که استند برای ایشان ارسال می‌شود">
                </div>

                <div class="so-field-row">
                    <div class="so-field">
                        <label>تاریخ برگزاری مراسم <span class="req">*</span></label>
                        <input type="text" name="event_date" class="pdate" required placeholder="انتخاب تاریخ" readonly style="background:#fff; cursor:pointer">
                    </div>
                    <div class="so-field">
                        <label>ساعت برگزاری مراسم <span class="req">*</span></label>
                        <input type="text" name="event_time" class="ptime" required placeholder="انتخاب ساعت" readonly style="background:#fff; cursor:pointer">
                    </div>
                </div>

                <!-- تفکیک فیلدهای آدرس: استان و شهر و آدرس دقیق -->
                <div class="so-section-title">محل تحویل و استقرار استند</div>
                <div class="so-field-row">
                    <div class="so-field">
                        <label>استان محل برگزاری (شعب فعال) <span class="req">*</span></label>
                        <select name="province" id="provinceSelect" required>
                            <?php if (empty($initialProvincesList)): ?>
                                <option value="تهران" selected>تهران</option>
                                <option value="اصفهان">اصفهان</option>
                                <option value="خراسان رضوی">خراسان رضوی</option>
                            <?php else: ?>
                                <?php foreach ($initialProvincesList as $idx => $p): ?>
                                    <option value="<?= htmlspecialchars($p['province']) ?>" data-branch="<?= (int)$p['branch_id'] ?>" <?= $idx === 0 ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['province']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="so-field">
                        <label>شهر محل برگزاری <span class="req">*</span></label>
                        <select name="city" id="citySelect" required>
                            <?php foreach ($defaultCities as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="so-field">
                    <label>نشانی دقیق محل برگزاری (مسجد، تالار یا منزل) <span class="req">*</span></label>
                    <textarea name="event_address" required placeholder="خیابان، کوچه، پلاک، نام مسجد، حسینیه یا تالار پذیرایی..."></textarea>
                </div>

                <!-- گالری انتخاب طرح استند اختصاصی شعبه -->
                <div class="so-section-title">طرح‌های استند شعبه انتخابی</div>
                <div class="so-gallery-tabs">
                    <button type="button" class="so-tab-btn active" data-filter="all">همه طرح‌ها</button>
                    <button type="button" class="so-tab-btn" data-filter="congrats">تبریک و شادباش</button>
                    <button type="button" class="so-tab-btn" data-filter="condolence">تسلیت و یادبود</button>
                </div>

                <div class="so-gallery-grid" id="standsGalleryGrid">
                    <!-- به صورت پویا توسط جاوااسکریپت لود می‌شود -->
                    <div class="so-gallery-empty" style="grid-column: 1 / -1;">در حال بارگذاری طرح‌های استند...</div>
                </div>

                <div class="so-field">
                    <label>متن دلخواه جهت درج روی کارت استند (اختیاری)</label>
                    <textarea name="message" placeholder="در صورت تمایل، متن پیام اختصاصی خود را در این بخش بنویسید..."></textarea>
                </div>

                <button type="submit" class="so-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>ثبت نهایی سفارش استند</span>
                </button>

                <p class="so-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    تمامی اطلاعات شما نزد مکسا محفوظ است و عواید مستقیماً صرف حمایت از بیماران می‌شود.
                </p>
            </form>
        </div>

        <!-- Left Column: 3D Stand Model & Price -->
        <div class="so-image-col">
            <div class="so-branch-badge" id="branchBadge">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span id="badgeBranchText">شعبه مکسا</span>
            </div>
            
            <!-- 3D Stand Model -->
            <div class="so-scene">
                <div class="so-stand-model" id="stand3dModel">
                    <div class="so-face so-front">
                        <img src="/dashboard/components/event-cards/images/1-removebg-preview.png" alt="طرح استند" id="standImageFront" onerror="this.src='https://via.placeholder.com/350x550?text=طرح+استند'">
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M3 12l5-5M3 12l5 5M21 12l-5-5M21 12l-5 5"></path>
                    </svg>
                    چرخش ۳۶۰ درجه و بررسی پشت استند
                </div>
                <input type="range" min="-180" max="180" value="0" class="so-rotator-slider" id="standRotator">
            </div>

            <div class="so-price-box">
                <h3 class="so-stand-title" id="previewTitle">استند خیریه مکسا</h3>
                <div class="so-price-tag" id="previewPrice">۳۰۰,۰۰۰ تومان</div>
                <p class="so-stand-desc" id="previewDesc">با سفارش این استند، ضمن اعلام همدردی یا شادباش، امیدبخش بیماران نیازمند باشید.</p>
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

let currentStands = [];
let currentFilter = 'all';
let provincesCache = [];

const provinceSelect = document.getElementById('provinceSelect');
const citySelect = document.getElementById('citySelect');
const galleryGrid = document.getElementById('standsGalleryGrid');
const inputStandId = document.getElementById('inputStandId');
const inputBranchId = document.getElementById('inputBranchId');

const previewImage = document.getElementById('standImageFront');
const previewTitle = document.getElementById('previewTitle');
const previewPrice = document.getElementById('previewPrice');
const previewDesc = document.getElementById('previewDesc');
const badgeBranchText = document.getElementById('badgeBranchText');
const standRotator = document.getElementById('standRotator');
const stand3dModel = document.getElementById('stand3dModel');

// 360 Image Rotator
if (standRotator && stand3dModel) {
    standRotator.addEventListener('input', function(e) {
        stand3dModel.style.transform = `rotateY(${e.target.value}deg)`;
    });
}

// لود استان‌ها از API
async function loadProvinces() {
    try {
        const res = await fetch('/api/stands/provinces');
        if (!res.ok) return;
        const json = await res.json();
        if (json.success && Array.isArray(json.data?.provinces) && json.data.provinces.length > 0) {
            provincesCache = json.data.provinces;
            provinceSelect.innerHTML = '';
            provincesCache.forEach((item, index) => {
                const opt = document.createElement('option');
                opt.value = item.province;
                opt.textContent = item.province;
                opt.dataset.branch = item.branch_id || '';
                if (index === 0) opt.selected = true;
                provinceSelect.appendChild(opt);
            });
            updateCities();
            loadStands(provinceSelect.value);
        }
    } catch (e) {
        console.warn('Error loading provinces:', e);
    }
}

// بروزرسانی دراپ‌داون شهر
function updateCities() {
    const selectedProv = provinceSelect.value;
    const provData = provincesCache.find(p => p.province === selectedProv);
    citySelect.innerHTML = '';

    const cities = (provData && provData.cities && provData.cities.length > 0) 
        ? provData.cities 
        : [selectedProv];

    cities.forEach(city => {
        const opt = document.createElement('option');
        opt.value = city;
        opt.textContent = city;
        citySelect.appendChild(opt);
    });
}

// بارگذاری استندهای اختصاصی شعبه بر اساس استان
async function loadStands(province) {
    galleryGrid.innerHTML = '<div class="so-gallery-empty" style="grid-column:1/-1;">در حال بارگذاری طرح‌های استند شعبه...</div>';

    try {
        const res = await fetch('/api/stands?province=' + encodeURIComponent(province));
        if (!res.ok) throw new Error('Network error');
        const json = await res.json();

        if (json.success && json.data) {
            const branch = json.data.branch;
            if (branch) {
                badgeBranchText.textContent = branch.name || ('شعبه ' + province);
                inputBranchId.value = branch.id || '';
            }

            currentStands = json.data.stands || [];
            renderGallery();

            // انتخاب اولین استند به عنوان پیش‌فرض
            if (currentStands.length > 0) {
                // بررسی اگر در localStorage استندی ذخیره بوده
                let initialStand = currentStands[0];
                const savedOrder = localStorage.getItem('pendingStandOrder');
                if (savedOrder) {
                    try {
                        const parsed = JSON.parse(savedOrder);
                        const match = currentStands.find(s => s.id == parsed.stand_id);
                        if (match) initialStand = match;
                    } catch(ex) {}
                }
                selectStand(initialStand);
            } else {
                galleryGrid.innerHTML = '<div class="so-gallery-empty" style="grid-column:1/-1;">در حال حاضر طرح فعالی برای این شعبه ثبت نشده است.</div>';
            }
        }
    } catch (err) {
        galleryGrid.innerHTML = '<div class="so-gallery-empty" style="grid-column:1/-1;">خطا در دریافت لیست استندها. لطفاً دوباره تلاش کنید.</div>';
    }
}

// رندر کارت‌های گالری استند
function renderGallery() {
    galleryGrid.innerHTML = '';

    const filtered = currentStands.filter(s => {
        if (currentFilter === 'all') return true;
        return s.stand_type === currentFilter;
    });

    if (filtered.length === 0) {
        galleryGrid.innerHTML = '<div class="so-gallery-empty" style="grid-column:1/-1;">طرحی در این دسته‌بندی یافت نشد.</div>';
        return;
    }

    filtered.forEach(stand => {
        const card = document.createElement('div');
        const isSelected = inputStandId.value == stand.id;
        card.className = 'so-stand-card' + (isSelected ? ' selected' : '');
        card.dataset.id = stand.id;

        const typeLabel = stand.stand_type === 'congrats' ? 'تبریک' : 'تسلیت';
        const typeClass = stand.stand_type === 'congrats' ? 'congrats' : 'condolence';

        card.innerHTML = `
            <div>
                <span class="so-stand-card-type ${typeClass}">${typeLabel}</span>
                <div class="so-stand-card-img">
                    <img src="${stand.image}" alt="${stand.title}" onerror="this.src='/dashboard/components/event-cards/images/1-removebg-preview.png'">
                </div>
                <div class="so-stand-card-title">${stand.title}</div>
            </div>
            <div class="so-stand-card-price">${stand.price_label || (Number(stand.unit_price).toLocaleString('fa-IR') + ' تومان')}</div>
        `;

        card.addEventListener('click', () => selectStand(stand));
        galleryGrid.appendChild(card);
    });
}

// انتخاب یک استند و بروزرسانی پیش‌نمایش ۳ بعدی و فرم
function selectStand(stand) {
    if (!stand) return;

    inputStandId.value = stand.id;
    if (stand.branch_id) {
        inputBranchId.value = stand.branch_id;
    }

    // بروزرسانی وضعیت انتخاب کارت‌ها
    document.querySelectorAll('.so-stand-card').forEach(c => {
        c.classList.toggle('selected', c.dataset.id == stand.id);
    });

    // بروزرسانی پیش‌نمایش ۳ بعدی
    previewImage.style.opacity = '0.3';
    setTimeout(() => {
        previewImage.src = stand.image;
        previewImage.style.opacity = '1';
    }, 150);

    previewTitle.textContent = stand.title;
    previewPrice.textContent = stand.price_label || (Number(stand.unit_price).toLocaleString('fa-IR') + ' تومان');
    previewDesc.textContent = stand.description || 'با سفارش این استند، حامی درمان بیماران مبتلا به سرطان باشید.';

    // ریست چرخش ۳۶۰ به حالت روبرو
    if (standRotator && stand3dModel) {
        standRotator.value = 0;
        stand3dModel.style.transform = 'rotateY(0deg)';
    }
}

// هندل تغییر استان
provinceSelect.addEventListener('change', function() {
    updateCities();
    loadStands(this.value);
});

// تب‌های فیلتر گالری
document.querySelectorAll('.so-tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.so-tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentFilter = this.dataset.filter || 'all';
        renderGallery();
    });
});

// بررسی بازگشت از لاگین و پر کردن فرم
function checkSavedOrder() {
    const saved = localStorage.getItem('pendingStandOrder');
    if (!saved) return;
    try {
        const data = JSON.parse(saved);
        for (const [key, value] of Object.entries(data)) {
            const field = document.querySelector(`[name="${key}"]`);
            if (field && value) field.value = value;
        }
        if (data.province) {
            provinceSelect.value = data.province;
            updateCities();
            if (data.city) citySelect.value = data.city;
        }
    } catch(e) {}
}

// ثبت نهایی فرم سفارش
document.getElementById('standOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!inputStandId.value) {
        alert('لطفاً یکی از طرح‌های استند را انتخاب فرمایید.');
        return;
    }

    const btn = this.querySelector('button[type="submit"]');
    const originalContent = btn.innerHTML;
    btn.innerHTML = 'در حال ثبت سفارش...';
    btn.disabled = true;

    const fd = new FormData(this);
    const raw = Object.fromEntries(fd.entries());

    const orderPayload = {
        sender_name:   raw.sender_name || '',
        sender_phone:  raw.sender_phone || '',
        from_user:     (raw.sender_name || '') + (raw.sender_phone ? ' (' + raw.sender_phone + ')' : ''),
        receiver_name: raw.receiver_name || '',
        to_user:       raw.receiver_name || '',
        event_date:    raw.event_date || '',
        event_time:    raw.event_time || '',
        order_date:    (raw.event_date || '') + (raw.event_time ? ' - ' + raw.event_time : ''),
        province:      raw.province || '',
        city:          raw.city || '',
        event_address: raw.event_address || '',
        address:       (raw.province ? raw.province + '، ' : '') + (raw.city ? raw.city + '، ' : '') + (raw.event_address || ''),
        message:       raw.message || '',
        stand_id:      parseInt(inputStandId.value) || 0,
        branch_id:     parseInt(inputBranchId.value) || 0,
        quantity:      1
    };

    try {
        // ۱. بررسی لاگین
        const authRes = await fetch('/api/auth/refresh', { method: 'POST', credentials: 'include' });
        let accessToken = null;

        if (authRes.ok) {
            const authJson = await authRes.json();
            accessToken = authJson?.data?.access_token;
        }

        if (!accessToken) {
            // ذخیره در localStorage و ریدایرکت به صفحه لاگین
            localStorage.setItem('pendingStandOrder', JSON.stringify(orderPayload));
            const returnUrl = encodeURIComponent(window.location.href);
            window.location.href = '/benefactor-dashboard/login?returnUrl=' + returnUrl;
            return;
        }

        // ۲. ارسال سفارش به API امن
        const submitRes = await fetch('/api/orders', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + accessToken
            },
            body: JSON.stringify(orderPayload)
        });

        const result = await submitRes.json();
        if (submitRes.ok && result.success) {
            localStorage.removeItem('pendingStandOrder');
            const tracking = result.data?.tracking_code || '';
            alert('سفارش استند با موفقیت ثبت شد!\nکد پیگیری شما: ' + tracking + '\nکارشناسان شعبه جهت تایید نهایی با شما تماس خواهند گرفت.');
            this.reset();
            loadStands(provinceSelect.value);
        } else {
            alert(result.error?.message || 'خطا در ثبت سفارش. لطفاً اطلاعات را بررسی نمایید.');
        }
    } catch (err) {
        alert('خطای اتصال به سرور. لطفاً اتصال اینترنت خود را بررسی نمایید.');
    } finally {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }
});

// راه‌اندازی صفحه
loadProvinces().then(() => {
    checkSavedOrder();
});
</script>

<?php
require __DIR__ . '/dashboard/components/footer/component.php';
?>

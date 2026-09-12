<?php
// دریافت اولیه لیست استان‌ها و شعب فعال غیرستادی
$initialProvinces = [];

try {
    $dbPath = dirname(__DIR__, 3) . '/core/database.php';
    if (file_exists($dbPath)) {
        require_once $dbPath;
    }
    if (isset($pdo)) {
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
                    'branch_name' => (string)$r['branch_name'],
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
?>

<!-- Persian Datepicker CSS -->
<link rel="stylesheet" href="/dashboard/assets/css/persian-datepicker.min.css">

<style>
.so-wrap {
    max-width: var(--cta-container, 1360px);
    margin: 0 auto;
    padding: 36px 20px 80px;
    font-family: 'Vazirmatn', Tahoma, sans-serif;
    direction: rtl;
}

.so-head {
    text-align: center;
    margin-bottom: 36px;
}
.so-head h1 {
    font-size: clamp(24px, 3.8vw, 32px);
    font-weight: 900;
    color: #1f2937;
    margin: 0 0 10px;
}
.so-head p {
    color: #4b5563;
    font-size: 15px;
    line-height: 1.8;
    max-width: 680px;
    margin: 0 auto;
}

/* دو ستون اصلی: راست (فرم سفارش) و چپ (استندها و پیش‌نمایش بنر) */
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
    .so-left-col {
        order: 2;
    }
    .so-form-col {
        order: 1;
    }
}

/* ================== ستون فرم سمت راست ================== */
.so-form-col {
    background: #ffffff;
    border-radius: 20px;
    padding: 32px 28px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.06);
}

.so-form-col h2 {
    font-size: 18px;
    font-weight: 900;
    color: #1f2937;
    margin: 0 0 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    gap: 8px;
}
.so-form-col h2 svg {
    width: 22px;
    height: 22px;
    color: #007b7a;
}

.so-section-title {
    font-size: 14px;
    font-weight: 800;
    color: #007b7a;
    margin: 24px 0 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.so-section-title::before {
    content: "";
    width: 6px;
    height: 18px;
    background: #007b7a;
    border-radius: 3px;
}

.so-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
@media (max-width: 640px) {
    .so-field-row { grid-template-columns: 1fr; }
}

.so-field {
    margin-bottom: 18px;
}
.so-field label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 6px;
}
.so-field label .req { color: #ef4444; }

.so-field input,
.so-field select,
.so-field textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    font-family: inherit;
    font-size: 13.5px;
    color: #1f2937;
    background: #f9fafb;
    outline: none;
    transition: all 0.2s;
    box-sizing: border-box;
}
.so-field input:focus,
.so-field select:focus,
.so-field textarea:focus {
    border-color: #007b7a;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(0,123,122,0.1);
}
.so-field textarea {
    resize: vertical;
    min-height: 80px;
    line-height: 1.7;
}

/* باکس هشدار محدوده شهری در فرم سفارش */
.so-notice-box {
    background: #fffbeb;
    border: 1px solid #fef3c7;
    border-right: 4px solid #f59e0b;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: #92400e;
    line-height: 1.7;
}
.so-notice-box svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    color: #d97706;
}

.so-submit {
    width: 100%;
    padding: 15px;
    background: #007b7a;
    color: #ffffff;
    border: none;
    border-radius: 14px;
    font-family: inherit;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.25s;
    box-shadow: 0 6px 18px rgba(0,123,122,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
}
.so-submit:hover {
    background: #006665;
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(0,123,122,0.4);
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

/* ================== ستون چپ (استندها و پیش‌نمایش بنر) ================== */
.so-left-col {
    background: #ffffff;
    border-radius: 20px;
    padding: 28px 24px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.06);
    position: sticky;
    top: 90px;
}

/* حالت پیش‌فرض قبل از انتخاب شعبه: خالی با متن راهنما */
.so-empty-guide {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}
.so-empty-guide-icon {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    background: rgba(0,123,122,0.08);
    color: #007b7a;
    display: grid;
    place-items: center;
    margin: 0 auto 20px;
}
.so-empty-guide-icon svg {
    width: 36px;
    height: 36px;
}
.so-empty-guide h3 {
    font-size: 17px;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 10px;
}
.so-empty-guide p {
    font-size: 14px;
    line-height: 1.8;
    max-width: 380px;
    margin: 0 auto;
    color: #6b7280;
}

/* حالت فعال پس از انتخاب شعبه */
.so-stands-content {
    display: flex;
    flex-direction: column;
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
    margin-bottom: 16px;
    align-self: flex-start;
}
.so-branch-badge svg { width: 15px; height: 15px; }

/* بنر و مدل ۳ بعدی استند انتخابی */
.so-preview-banner {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
}

.so-scene {
    width: 100%;
    height: 280px;
    perspective: 1000px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    user-select: none;
    cursor: grab;
}
.so-scene:active {
    cursor: grabbing;
}

.so-stand-3d {
    width: 180px;
    height: 260px;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.1s ease-out;
}

.so-stand-face {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    backface-visibility: hidden;
}

.so-stand-face.front {
    transform: rotateY(0deg) translateZ(6px);
}
.so-stand-face.back {
    transform: rotateY(180deg) translateZ(6px);
    background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}
.so-stand-face.back svg {
    width: 40px;
    height: 40px;
    color: #007b7a;
    margin-bottom: 8px;
}

.so-stand-img-wrap {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
}
.so-stand-img-wrap img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 6px 12px rgba(0,0,0,0.1));
}

.so-360-hint {
    font-size: 11.5px;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    margin-bottom: 12px;
}
.so-360-hint svg { width: 14px; height: 14px; color: #007b7a; }

/* قیمت و عنوان متناسب با بنر انتخابی */
.so-selected-meta {
    background: #ffffff;
    border-radius: 12px;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.so-meta-title {
    font-size: 14.5px;
    font-weight: 800;
    color: #1f2937;
}
.so-meta-price {
    font-size: 18px;
    font-weight: 900;
    color: #007b7a;
    display: flex;
    align-items: baseline;
    gap: 4px;
}
.so-meta-price span {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
}

/* تب‌های دسته‌بندی استندها */
.so-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 14px;
}
.so-tab-btn {
    flex: 1;
    padding: 8px 10px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 10px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    color: #4b5563;
    cursor: pointer;
    transition: all 0.2s;
}
.so-tab-btn.active {
    background: #007b7a;
    color: #ffffff;
    border-color: #007b7a;
}

/* ================== ساختار لیست‌گونه استندها (Stands List View) ================== */
.so-stands-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 380px;
    overflow-y: auto;
    padding-left: 4px;
}

.so-stand-list-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 14px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.22s ease;
    user-select: none;
}
.so-stand-list-item:hover {
    border-color: #4fb2b0;
    background: #fcfdfd;
    transform: translateX(-3px);
}
.so-stand-list-item.active {
    border-color: #007b7a;
    background: rgba(0, 123, 122, 0.04);
    box-shadow: 0 4px 14px rgba(0, 123, 122, 0.12);
}

.so-list-thumb {
    width: 54px;
    height: 72px;
    border-radius: 8px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}
.so-list-thumb img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
}

.so-list-info {
    flex: 1;
    min-width: 0;
}
.so-list-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}
.so-list-title {
    font-size: 13.5px;
    font-weight: 800;
    color: #1f2937;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.so-list-tag {
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 10px;
    flex-shrink: 0;
}
.so-list-tag.congrats {
    background: rgba(22, 163, 122, 0.12);
    color: #16a37a;
}
.so-list-tag.condolence {
    background: rgba(71, 85, 105, 0.12);
    color: #475569;
}

.so-list-price {
    font-size: 13px;
    font-weight: 800;
    color: #007b7a;
}

.so-list-check {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid #d1d5db;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s;
}
.so-stand-list-item.active .so-list-check {
    border-color: #007b7a;
    background: #007b7a;
}
.so-stand-list-item.active .so-list-check::after {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #ffffff;
}

.empty-stands-msg {
    text-align: center;
    padding: 36px 16px;
    color: #9ca3af;
    font-size: 13px;
    background: #f9fafb;
    border-radius: 12px;
    border: 1px dashed #e5e7eb;
}
</style>

<div class="so-wrap">
    <div class="so-head">
        <h1>ثبت سفارش استند و کارت خیریه</h1>
        <p>با سفارش استند و کارت‌های مکسا، پیام پرمحبت خود را ماندگار سازید و تمام عواید آن را نذر دارو و درمان رایگان بیماران مبتلا به سرطان نمایید.</p>
    </div>

    <div class="so-grid">
        <!-- ستون فرم سمت راست -->
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
                <div class="so-section-title">انتخاب شعبه و محل استقرار استند</div>
                <div class="so-field-row">
                    <div class="so-field">
                        <label>استان / شعبه ارائه‌دهنده استند <span class="req">*</span></label>
                        <select name="province" id="provinceSelect" required>
                            <option value="">-- لطفاً ابتدا استان و شعبه را انتخاب کنید --</option>
                            <?php foreach ($initialProvincesList as $p): ?>
                                <option value="<?= htmlspecialchars($p['province']) ?>" data-branch="<?= (int)$p['branch_id'] ?>">
                                    شعبه <?= htmlspecialchars($p['branch_name']) ?> (<?= htmlspecialchars($p['province']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="so-field">
                        <label>شهر محل برگزاری مراسم <span class="req">*</span></label>
                        <select name="city" id="citySelect" required>
                            <option value="">-- ابتدا استان را انتخاب کنید --</option>
                        </select>
                    </div>
                </div>

                <!-- باکس یادآوری محدوده شهری (Notice) -->
                <div class="so-notice-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <strong>نکته مهم:</strong> ارسال، تحویل و استقرار استندهای خیریه مکسا صرفاً در محدوده شهری شعبه انتخابی انجام می‌پذیرد و امکان پذیرش سفارش برای مناطق خارج از محدوده وجود ندارد.
                    </div>
                </div>

                <div class="so-field">
                    <label>نشانی دقیق محل برگزاری (مسجد، تالار یا منزل) <span class="req">*</span></label>
                    <textarea name="event_address" required placeholder="خیابان، کوچه، پلاک، نام مسجد، حسینیه یا تالار پذیرایی..."></textarea>
                </div>

                <div class="so-field">
                    <label>متن پیام اختصاصی روی استند (اختیاری)</label>
                    <textarea name="message" placeholder="در صورت تمایل، متن دلخواه خود را جهت درج روی استند وارد نمایید..."></textarea>
                </div>

                <button type="submit" class="so-submit" id="submitOrderBtn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    ثبت نهایی سفارش استند
                </button>

                <div class="so-note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    پس از ثبت سفارش، کارشناسان مکسا جهت هماهنگی ارسال با شما تماس خواهند گرفت.
                </div>
            </form>
        </div>

        <!-- ستون چپ: استندهای اختصاصی شعبه به صورت لیست‌گونه و پیش‌نمایش بنر -->
        <div class="so-left-col" id="soLeftCol">

            <!-- حالت ۱: قبل از انتخاب شعبه (خالی همراه با پیام راهنما) -->
            <div class="so-empty-guide" id="soEmptyGuide">
                <div class="so-empty-guide-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3>طرح‌های اختصاصی هر شعبه</h3>
                <p>
                    با انتخاب استان و شهر مورد نظر از فرم سمت راست، تمامی طرح‌ها، بنرهای سه‌بعدی و مبالغ استندهای اختصاصی آن شعبه به صورت لیست در این بخش نمایش داده خواهند شد.
                </p>
            </div>

            <!-- حالت ۲: پس از انتخاب شعبه (نمایش پیش‌نمایش بنر و لیست استندها) -->
            <div class="so-stands-content" id="soStandsContent" style="display:none">
                <div class="so-branch-badge" id="branchBadge">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>
                    استندهای اختصاصی شعبه <span id="currentBranchName">...</span>
                </div>

                <!-- پیش‌نمایش بنر و ۳ بعدی استند انتخابی -->
                <div class="so-preview-banner">
                    <div class="so-scene" id="standScene" title="برای چرخاندن استند، ماوس را بکشید">
                        <div class="so-stand-3d" id="stand3D">
                            <div class="so-stand-face front">
                                <div class="so-stand-img-wrap">
                                    <img src="" alt="طرح استند" id="standImageFront" onerror="this.src='/uploads/stand/happy/1.jpg'">
                                </div>
                            </div>
                            <div class="so-stand-face back">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                <h4 style="font-size:13px; font-weight:800; color:#1f2937; margin-bottom:4px">مؤسسه خیریه مکسا</h4>
                                <p style="font-size:11px; color:#6b7280; line-height:1.5">مراقبت‌های جامع حمایتی و تسکینی برای بیماران مبتلا به سرطان</p>
                            </div>
                        </div>
                    </div>

                    <div class="so-360-hint">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        پیش‌نمایش سه‌بعدی بنر (برای چرخش ماوس را بکشید)
                    </div>

                    <!-- قیمت و عنوان هماهنگ با استند انتخابی -->
                    <div class="so-selected-meta">
                        <div class="so-meta-title" id="selectedStandTitle">عنوان استند</div>
                        <div class="so-meta-price" id="selectedStandPrice">۰ <span>تومان</span></div>
                    </div>
                </div>

                <!-- تب‌های فیلتر دسته‌بندی استندها -->
                <div class="so-tabs">
                    <button type="button" class="so-tab-btn active" data-type="all">همه طرح‌ها</button>
                    <button type="button" class="so-tab-btn" data-type="congrats">تبریک و شادباش</button>
                    <button type="button" class="so-tab-btn" data-type="condolence">ابراز همدردی و تسلیت</button>
                </div>

                <!-- لیست‌گونه استندهای شعبه (Stands List View) -->
                <div class="so-stands-list" id="standsCatalog">
                    <!-- آیتم‌های استند به صورت لیست‌گونه لود می‌شوند -->
                </div>
            </div>

        </div>
    </div>
</div>

<!-- اسکریپت‌های تقویم و تعاملات صفحه -->
<script src="/dashboard/assets/js/jquery-3.7.1.min.js"></script>
<script src="/dashboard/assets/js/persian-date.min.js"></script>
<script src="/dashboard/assets/js/persian-datepicker.min.js"></script>

<script>
// مدل داده‌ای وضعیت صفحه
const state = {
    provinces: <?= json_encode($initialProvincesList, JSON_UNESCAPED_UNICODE) ?>,
    stands: [],
    selectedStand: null,
    selectedBranchId: null,
    filterType: 'all'
};

// عناصر DOM
const provinceSelect = document.getElementById('provinceSelect');
const citySelect = document.getElementById('citySelect');
const standsCatalog = document.getElementById('standsCatalog');
const soEmptyGuide = document.getElementById('soEmptyGuide');
const soStandsContent = document.getElementById('soStandsContent');
const standImageFront = document.getElementById('standImageFront');
const selectedStandTitle = document.getElementById('selectedStandTitle');
const selectedStandPrice = document.getElementById('selectedStandPrice');
const currentBranchName = document.getElementById('currentBranchName');
const inputStandId = document.getElementById('inputStandId');
const inputBranchId = document.getElementById('inputBranchId');
const standScene = document.getElementById('standScene');
const stand3D = document.getElementById('stand3D');

// راه‌اندازی DatePicker
if (window.jQuery && jQuery.fn.persianDatepicker) {
    $('.pdate').persianDatepicker({
        format: 'YYYY/MM/DD',
        autoClose: true,
        initialValue: false,
        minDate: new persianDate().valueOf()
    });
    $('.ptime').persianDatepicker({
        format: 'HH:mm',
        onlyTimePicker: true,
        autoClose: true,
        initialValue: false
    });
}

// تعامل چرخش سه‌بعدی با موس و لمس
let isDragging = false;
let startX = 0;
let currentRotationY = 0;

standScene.addEventListener('mousedown', (e) => {
    isDragging = true;
    startX = e.clientX;
});
window.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    const deltaX = e.clientX - startX;
    startX = e.clientX;
    currentRotationY += deltaX * 0.8;
    stand3D.style.transform = `rotateY(${currentRotationY}deg)`;
});
window.addEventListener('mouseup', () => { isDragging = false; });

standScene.addEventListener('touchstart', (e) => {
    isDragging = true;
    startX = e.touches[0].clientX;
});
window.addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    const deltaX = e.touches[0].clientX - startX;
    startX = e.touches[0].clientX;
    currentRotationY += deltaX * 0.8;
    stand3D.style.transform = `rotateY(${currentRotationY}deg)`;
});
window.addEventListener('touchend', () => { isDragging = false; });

function toFa(n) {
    return (n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, "٬")
        .replace(/[0-9]/g, d => "۰۱۲۳۴۵۶۷۸۹"[d]);
}

// بارگذاری استان‌های مجاز از API
async function loadProvinces() {
    try {
        const res = await fetch('/api/stands/provinces');
        const data = await res.json();
        const provList = Array.isArray(data.data) ? data.data : (data.data?.provinces || []);
        if (data.success && provList.length > 0) {
            state.provinces = provList;
            populateProvinces();
        }
    } catch (e) {
        console.warn('Using pre-rendered provinces.');
    }
}

function populateProvinces() {
    const urlParams = new URLSearchParams(window.location.search);
    const targetProvince = urlParams.get('province');

    // حفظ اولین گزینه خالی به عنوان راهنما
    provinceSelect.innerHTML = '<option value="">-- لطفاً ابتدا استان و شعبه را انتخاب کنید --</option>';
    state.provinces.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.province;
        opt.textContent = 'شعبه ' + (p.branch_name || p.province) + ' (' + p.province + ')';
        opt.dataset.branch = p.branch_id;
        if (targetProvince && p.province === targetProvince) {
            opt.selected = true;
        }
        provinceSelect.appendChild(opt);
    });

    if (provinceSelect.value) {
        onProvinceSelected();
    } else {
        showEmptyGuide();
    }
}

function showEmptyGuide() {
    soEmptyGuide.style.display = 'block';
    soStandsContent.style.display = 'none';
    citySelect.innerHTML = '<option value="">-- ابتدا استان را انتخاب کنید --</option>';
    state.selectedStand = null;
    inputStandId.value = '';
    inputBranchId.value = '';
}

function onProvinceSelected() {
    const selectedProv = provinceSelect.value;
    if (!selectedProv) {
        showEmptyGuide();
        return;
    }

    const provData = state.provinces.find(p => p.province === selectedProv);
    citySelect.innerHTML = '';

    if (provData && Array.isArray(provData.cities) && provData.cities.length > 0) {
        provData.cities.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            citySelect.appendChild(opt);
        });
    } else {
        const opt = document.createElement('option');
        opt.value = selectedProv;
        opt.textContent = selectedProv;
        citySelect.appendChild(opt);
    }

    const selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
    const bId = selectedOption ? selectedOption.dataset.branch : (provData ? provData.branch_id : 1);
    state.selectedBranchId = parseInt(bId);
    inputBranchId.value = state.selectedBranchId;
    currentBranchName.textContent = selectedProv;

    // نمایش محتوای استندها در ستون چپ
    soEmptyGuide.style.display = 'none';
    soStandsContent.style.display = 'block';

    loadStands(selectedProv);
}

provinceSelect.addEventListener('change', onProvinceSelected);

// بارگذاری استندهای اختصاصی شعبه
async function loadStands(province) {
    standsCatalog.innerHTML = '<div style="text-align:center;padding:30px;color:#9ca3af">در حال بارگذاری طرح‌های اختصاصی شعبه...</div>';
    try {
        const res = await fetch(`/api/stands?province=${encodeURIComponent(province)}`);
        const data = await res.json();
        const standsList = Array.isArray(data.data) ? data.data : (data.data?.stands || []);
        if (data.success && standsList.length > 0) {
            state.stands = standsList;
            renderStandsCatalog();
        } else {
            standsCatalog.innerHTML = '<div class="empty-stands-msg">هیچ طرح استندی برای این شعبه ثبت نشده است.</div>';
        }
    } catch (e) {
        standsCatalog.innerHTML = '<div class="empty-stands-msg" style="color:#ef4444">خطا در بارگذاری استندها. لطفاً دوباره تلاش کنید.</div>';
    }
}

// رندر لیست‌گونه استندها در ستون چپ
function renderStandsCatalog() {
    const filtered = state.filterType === 'all' 
        ? state.stands 
        : state.stands.filter(s => s.stand_type === state.filterType);

    if (filtered.length === 0) {
        standsCatalog.innerHTML = '<div class="empty-stands-msg">طرحی در این دسته‌بندی برای این شعبه یافت نشد.</div>';
        return;
    }

    // تولید لیست با ساختار مرتب و شیک
    standsCatalog.innerHTML = filtered.map(s => {
        const isSelected = state.selectedStand && state.selectedStand.id === s.id;
        const tagLabel = s.stand_type === 'congrats' ? 'تبریک' : 'تسلیت';
        return `
            <div class="so-stand-list-item ${isSelected ? 'active' : ''}" data-id="${s.id}">
                <div class="so-list-thumb">
                    <img src="${s.image}" alt="${s.title}" onerror="this.src='/uploads/stand/happy/1.jpg'">
                </div>
                <div class="so-list-info">
                    <div class="so-list-title-row">
                        <h4 class="so-list-title">${s.title}</h4>
                        <span class="so-list-tag ${s.stand_type}">${tagLabel}</span>
                    </div>
                    <div class="so-list-price">${toFa(s.unit_price)} تومان</div>
                </div>
                <div class="so-list-check"></div>
            </div>
        `;
    }).join('');

    // بررسی استند انتخابی بر اساس URL یا اولین آیتم لیست
    const urlParams = new URLSearchParams(window.location.search);
    const targetStandId = parseInt(urlParams.get('stand_id'));

    if (targetStandId && filtered.some(s => s.id === targetStandId)) {
        selectStand(targetStandId);
    } else if (!state.selectedStand || !filtered.some(s => s.id === state.selectedStand.id)) {
        selectStand(filtered[0].id);
    } else {
        selectStand(state.selectedStand.id);
    }

    // اضافه کردن رویداد کلیک برای هر استند در لیست
    standsCatalog.querySelectorAll('.so-stand-list-item').forEach(item => {
        item.addEventListener('click', () => {
            selectStand(parseInt(item.dataset.id));
        });
    });
}

// انتخاب استند و تغییر لحظه‌ای عکس بنر، قیمت و عنوان
function selectStand(standId) {
    const stand = state.stands.find(s => s.id === standId);
    if (!stand) return;

    state.selectedStand = stand;
    inputStandId.value = stand.id;

    // هایلایت آیتم فعال در لیست‌گونه
    standsCatalog.querySelectorAll('.so-stand-list-item').forEach(item => {
        item.classList.toggle('active', parseInt(item.dataset.id) === stand.id);
    });

    // تغییر عکس بنر در پیش‌نمایش ۳ بعدی
    standImageFront.src = stand.image;

    // تغییر عنوان بنر
    selectedStandTitle.textContent = stand.title;

    // تغییر قیمت متناسب با استند انتخابی
    selectedStandPrice.innerHTML = `${toFa(stand.unit_price)} <span>تومان</span>`;

    // ریست چرخش بنر
    currentRotationY = 0;
    stand3D.style.transform = `rotateY(0deg)`;
}

// تب‌های دسته‌بندی (همه / تبریک / تسلیت)
document.querySelectorAll('.so-tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.so-tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        state.filterType = this.dataset.type;
        renderStandsCatalog();
    });
});

// حفظ اطلاعات فرم در صورت عدم لاگین
function checkSavedOrder() {
    const saved = localStorage.getItem('pendingStandOrder');
    if (saved) {
        try {
            const data = JSON.parse(saved);
            const form = document.getElementById('standOrderForm');
            Object.keys(data).forEach(k => {
                if (form.elements[k]) form.elements[k].value = data[k];
            });
            if (data.province) {
                provinceSelect.value = data.province;
                onProvinceSelected();
                if (data.stand_id) {
                    setTimeout(() => selectStand(parseInt(data.stand_id)), 400);
                }
            }
        } catch(e) {}
    }
}

// ثبت فرم سفارش استند
document.getElementById('standOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!provinceSelect.value) {
        alert('لطفاً ابتدا استان و شهر مورد نظر خود را انتخاب نمایید.');
        provinceSelect.focus();
        return;
    }

    if (!state.selectedStand) {
        alert('لطفاً یک طرح استند را از لیست سمت چپ انتخاب فرمایید.');
        return;
    }

    const btn = document.getElementById('submitOrderBtn');
    const originalContent = btn.innerHTML;
    btn.innerHTML = 'در حال ثبت سفارش...';
    btn.disabled = true;

    const formData = new FormData(this);
    const payload = Object.fromEntries(formData.entries());
    payload.unit_price = state.selectedStand.unit_price;
    payload.quantity = 1;
    payload.total_price = state.selectedStand.unit_price;
    payload.branch_id = state.selectedBranchId;

    try {
        const token = localStorage.getItem('access_token') || localStorage.getItem('token') || '';
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }

        const submitRes = await fetch('/api/orders', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(payload)
        });

        if (submitRes.status === 401) {
            localStorage.setItem('pendingStandOrder', JSON.stringify(payload));
            if (confirm('برای نهایی‌سازی سفارش، لطفاً وارد حساب خود شوید. آیا مایل به انتقال به صفحه ورود هستید؟')) {
                window.location.href = '/benefactor-dashboard/login?returnUrl=' + encodeURIComponent(window.location.href);
            }
            return;
        }

        const result = await submitRes.json();
        if (submitRes.ok && result.success) {
            localStorage.removeItem('pendingStandOrder');
            const tracking = result.data?.tracking_code || '';
            alert('سفارش استند با موفقیت ثبت شد!\nکد پیگیری شما: ' + tracking + '\nکارشناسان شعبه جهت تایید نهایی با شما تماس خواهند گرفت.');
            this.reset();
            showEmptyGuide();
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

// راه‌اندازی اولیه
loadProvinces().then(() => {
    checkSavedOrder();
});
</script>

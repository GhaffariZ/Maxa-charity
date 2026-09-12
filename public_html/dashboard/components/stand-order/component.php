<?php
// دریافت اولیه لیست استان‌ها و شعب فعال غیرستادی
$initialProvinces = [];
$initialStands = [];

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
    top: 90px;
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
    height: 380px;
    perspective: 1200px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    user-select: none;
    cursor: grab;
}
.so-scene:active {
    cursor: grabbing;
}

.so-stand-3d {
    width: 220px;
    height: 340px;
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
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    display: flex;
    flex-direction: column;
    backface-visibility: hidden;
}

.so-stand-face.front {
    transform: rotateY(0deg) translateZ(8px);
}
.so-stand-face.back {
    transform: rotateY(180deg) translateZ(8px);
    background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}
.so-stand-face.back svg {
    width: 48px;
    height: 48px;
    color: #007b7a;
    margin-bottom: 12px;
}

.so-stand-img-wrap {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
}
.so-stand-img-wrap img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 8px 16px rgba(0,0,0,0.1));
}

.so-360-hint {
    font-size: 12.5px;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 18px;
}
.so-360-hint svg { width: 16px; height: 16px; color: #007b7a; }

.so-selected-meta {
    background: #f9fafb;
    border-radius: 14px;
    padding: 16px;
    border: 1px solid #f3f4f6;
    text-align: right;
}
.so-meta-title {
    font-size: 15px;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 6px;
}
.so-meta-price {
    font-size: 19px;
    font-weight: 900;
    color: #007b7a;
    display: flex;
    align-items: baseline;
    gap: 4px;
}
.so-meta-price span {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
}

/* Form Column */
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

/* Tabs */
.so-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}
.so-tab-btn {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 10px;
    font-family: inherit;
    font-size: 13px;
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

/* Stand Options Grid */
.so-stands-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 12px;
    max-height: 380px;
    overflow-y: auto;
    padding: 4px;
    margin-bottom: 24px;
}

.so-stand-card {
    border: 2px solid #e5e7eb;
    border-radius: 14px;
    padding: 8px;
    text-align: center;
    cursor: pointer;
    background: #ffffff;
    transition: all 0.2s;
    position: relative;
    display: flex;
    flex-direction: column;
}
.so-stand-card:hover {
    border-color: #4fb2b0;
    transform: translateY(-2px);
}
.so-stand-card.selected {
    border-color: #007b7a;
    background: rgba(0,123,122,0.03);
    box-shadow: 0 4px 12px rgba(0,123,122,0.15);
}
.so-stand-card.selected::after {
    content: "✓";
    position: absolute;
    top: 6px;
    right: 6px;
    width: 20px;
    height: 20px;
    background: #007b7a;
    color: #fff;
    border-radius: 50%;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.so-stand-card-img {
    height: 110px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 6px;
}
.so-stand-card-img img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.so-stand-card-title {
    font-size: 12px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.so-stand-card-price {
    font-size: 12px;
    font-weight: 900;
    color: #007b7a;
    margin-top: auto;
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
</style>

<div class="so-wrap">
    <div class="so-head">
        <h1>ثبت سفارش استند و کارت خیریه</h1>
        <p>با سفارش استند و کارت‌های مکسا، پیام پرمحبت خود را ماندگار سازید و تمام عواید آن را نذر دارو و درمان رایگان بیماران مبتلا به سرطان نمایید.</p>
    </div>

    <div class="so-grid">
        <!-- ستون فرم و انتخاب استند -->
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
                        <label>شعبه / استان محل برگزاری (شعب فعال) <span class="req">*</span></label>
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

                <!-- گالری انتخاب طرح استند اختصاصی شعبه -->
                <div class="so-section-title">انتخاب طرح استند</div>

                <div class="so-tabs">
                    <button type="button" class="so-tab-btn active" data-type="all">همه طرح‌ها</button>
                    <button type="button" class="so-tab-btn" data-type="congrats">تبریک و شادباش</button>
                    <button type="button" class="so-tab-btn" data-type="condolence">ابراز همدردی و تسلیت</button>
                </div>

                <div class="so-stands-grid" id="standsCatalog">
                    <!-- به صورت داینامیک لود می‌شود -->
                </div>

                <div class="so-field">
                    <label>متن پیام اختصاصی روی استند (اختیاری)</label>
                    <textarea name="message" placeholder="در صورت تمایل، متن دلخواه خود را جهت چاپ روی استند وارد نمایید..."></textarea>
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

        <!-- ستون چپ: پیش‌نمایش ۳ بعدی استند انتخابی -->
        <div class="so-image-col">
            <div class="so-branch-badge" id="branchBadge">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>
                طرح‌های اختصاصی شعبه <span id="currentBranchName">تهران</span>
            </div>

            <!-- صحنه سه‌بعدی استند با چرخش تعاملی -->
            <div class="so-scene" id="standScene" title="برای چرخاندن استند، ماوس را بکشید یا دکمه‌های زیر را بزنید">
                <div class="so-stand-3d" id="stand3D">
                    <div class="so-stand-face front">
                        <div class="so-stand-img-wrap">
                            <img src="/dashboard/components/event-cards/images/1-removebg-preview.png" alt="طرح استند" id="standImageFront" onerror="this.src='/uploads/stand/happy/1.jpg'">
                        </div>
                    </div>
                    <div class="so-stand-face back">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <h4 style="font-size:14px; font-weight:800; color:#1f2937; margin-bottom:6px">مؤسسه خیریه مکسا</h4>
                        <p style="font-size:11.5px; color:#6b7280; line-height:1.6">نخستین و بزرگترین مرکز مراقبت‌های تسکینی و حمایتی برای بیماران مبتلا به سرطان در ایران</p>
                    </div>
                </div>
            </div>

            <div class="so-360-hint">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                پیش‌نمایش سه‌بعدی (برای چرخش ماوس را بکشید)
            </div>

            <div class="so-selected-meta">
                <div class="so-meta-title" id="selectedStandTitle">استند سفارشی مکسا</div>
                <div class="so-meta-price" id="selectedStandPrice">۱٬۵۰۰٬۰۰۰ <span>تومان</span></div>
            </div>
        </div>
    </div>
</div>

<!-- اسکریپت‌های تقویم و تعاملات صفحه -->
<script src="/dashboard/assets/js/jquery-3.7.1.min.js"></script>
<script src="/dashboard/assets/js/persian-date.min.js"></script>
<script src="/dashboard/assets/js/persian-datepicker.min.js"></script>

<script>
// مدل داده‌ای ایالت صفحه
const state = {
    provinces: [],
    stands: [],
    selectedStand: null,
    selectedBranchId: null,
    filterType: 'all'
};

// عناصر DOM
const provinceSelect = document.getElementById('provinceSelect');
const citySelect = document.getElementById('citySelect');
const standsCatalog = document.getElementById('standsCatalog');
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

// پشتیبانی از لمس موبایل
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
        if (data.success && Array.isArray(data.data) && data.data.length > 0) {
            state.provinces = data.data;
            populateProvinces();
        }
    } catch (e) {
        console.warn('Fallback to server pre-rendered provinces.');
    }
}

function populateProvinces() {
    // خواندن پارامتر استان از URL در صورت وجود
    const urlParams = new URLSearchParams(window.location.search);
    const targetProvince = urlParams.get('province');

    provinceSelect.innerHTML = '';
    state.provinces.forEach((p, i) => {
        const opt = document.createElement('option');
        opt.value = p.province;
        opt.textContent = p.province;
        opt.dataset.branch = p.branch_id;
        if (targetProvince && p.province === targetProvince) {
            opt.selected = true;
        } else if (!targetProvince && i === 0) {
            opt.selected = true;
        }
        provinceSelect.appendChild(opt);
    });
    updateCities();
    loadStands(provinceSelect.value);
}

function updateCities() {
    const selectedProv = provinceSelect.value;
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
}

provinceSelect.addEventListener('change', () => {
    updateCities();
    loadStands(provinceSelect.value);
});

// بارگذاری استندهای اختصاصی شعبه
async function loadStands(province) {
    standsCatalog.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#9ca3af">در حال بارگذاری طرح‌های اختصاصی شعبه...</div>';
    try {
        const res = await fetch(`/api/stands?province=${encodeURIComponent(province)}`);
        const data = await res.json();
        if (data.success && Array.isArray(data.data) && data.data.length > 0) {
            state.stands = data.data;
            renderStandsCatalog();
        } else {
            standsCatalog.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#9ca3af">هیچ طرح استندی برای این شعبه ثبت نشده است.</div>';
        }
    } catch (e) {
        standsCatalog.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#ef4444">خطا در بارگذاری استندها. لطفاً صفحه را تازه‌سازی کنید.</div>';
    }
}

function renderStandsCatalog() {
    const filtered = state.filterType === 'all' 
        ? state.stands 
        : state.stands.filter(s => s.stand_type === state.filterType);

    if (filtered.length === 0) {
        standsCatalog.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:#9ca3af">طرحی در این دسته‌بندی یافت نشد.</div>';
        return;
    }

    standsCatalog.innerHTML = filtered.map(s => `
        <div class="so-stand-card ${state.selectedStand && state.selectedStand.id === s.id ? 'selected' : ''}" data-id="${s.id}">
            <div class="so-stand-card-img">
                <img src="${s.image}" alt="${s.title}" onerror="this.src='/uploads/stand/happy/1.jpg'">
            </div>
            <div class="so-stand-card-title">${s.title}</div>
            <div class="so-stand-card-price">${toFa(s.unit_price)} تومان</div>
        </div>
    `).join('');

    // چک کردن انتخاب از URL یا پیش‌فرض
    const urlParams = new URLSearchParams(window.location.search);
    const targetStandId = parseInt(urlParams.get('stand_id'));

    if (targetStandId && filtered.some(s => s.id === targetStandId)) {
        selectStand(targetStandId);
    } else if (!state.selectedStand || !filtered.some(s => s.id === state.selectedStand.id)) {
        selectStand(filtered[0].id);
    } else {
        selectStand(state.selectedStand.id);
    }

    standsCatalog.querySelectorAll('.so-stand-card').forEach(card => {
        card.addEventListener('click', () => {
            selectStand(parseInt(card.dataset.id));
        });
    });
}

function selectStand(standId) {
    const stand = state.stands.find(s => s.id === standId);
    if (!stand) return;

    state.selectedStand = stand;
    inputStandId.value = stand.id;

    // هایلایت کارت
    standsCatalog.querySelectorAll('.so-stand-card').forEach(card => {
        card.classList.toggle('selected', parseInt(card.dataset.id) === stand.id);
    });

    // بروزرسانی پیش‌نمایش ۳ بعدی
    standImageFront.src = stand.image;
    selectedStandTitle.textContent = stand.title;
    selectedStandPrice.innerHTML = `${toFa(stand.unit_price)} <span>تومان</span>`;

    // چرخش ملایم برای نمایش تغییر طرح
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

// حفظ اطلاعات در صورت نیاز به لاگین
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
                updateCities();
                loadStands(data.province).then(() => {
                    if (data.stand_id) selectStand(parseInt(data.stand_id));
                });
            }
        } catch(e) {}
    }
}

// ثبت فرم سفارش استند
document.getElementById('standOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!state.selectedStand) {
        alert('لطفاً یک طرح استند را انتخاب فرمایید.');
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
        const submitRes = await fetch('/api/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        // کاربر لاگین نبود؟ ذخیره در localStorage و انتقال به ورود
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

// راه‌اندازی اولیه
loadProvinces().then(() => {
    checkSavedOrder();
});
</script>

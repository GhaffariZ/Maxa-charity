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

/* ================== آیکون فیلدهای تاریخ و ساعت ================== */
.so-input-icon-wrap {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}
.so-input-icon-wrap input {
    padding-left: 42px !important;
}
.so-field-icon {
    position: absolute;
    left: 14px;
    width: 20px;
    height: 20px;
    color: #007b7a;
    pointer-events: none;
    transition: color 0.2s;
}
.so-input-icon-wrap input:focus + .so-field-icon {
    color: #006665;
}

/* ================== استایل اختصاصی و مدرن تقویم و ساعت شمسی ================== */
.datepicker-container {
    z-index: 999999 !important;
    font-family: 'Vazirmatn', sans-serif !important;
    direction: rtl !important;
}
.datepicker-plot-area {
    border-radius: 20px !important;
    box-shadow: 0 20px 45px -10px rgba(0, 123, 122, 0.28), 0 8px 24px rgba(0,0,0,0.08) !important;
    border: 1.5px solid rgba(0, 123, 122, 0.22) !important;
    background: #ffffff !important;
    padding: 16px !important;
    min-width: 290px !important;
    width: 290px !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
    animation: dpZoomIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
@keyframes dpZoomIn {
    from { opacity: 0; transform: scale(0.95) translateY(-6px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.datepicker-navigator {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding-bottom: 12px !important;
    margin-bottom: 12px !important;
    border-bottom: 1px solid #f1f5f9 !important;
}
.datepicker-navigator .pwt-btn {
    width: 32px !important;
    height: 32px !important;
    line-height: 32px !important;
    border-radius: 10px !important;
    background: #f0fdfa !important;
    color: #007b7a !important;
    font-size: 16px !important;
    font-weight: 900 !important;
    display: grid !important;
    place-items: center !important;
    transition: all 0.2s !important;
    cursor: pointer !important;
}
.datepicker-navigator .pwt-btn:hover {
    background: #007b7a !important;
    color: #ffffff !important;
}
.datepicker-navigator .pwt-btn-switch {
    width: auto !important;
    flex: 1 !important;
    margin: 0 10px !important;
    padding: 0 14px !important;
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    color: #1e293b !important;
    font-size: 13.5px !important;
    font-weight: 800 !important;
    border-radius: 10px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.datepicker-navigator .pwt-btn-switch:hover {
    border-color: #007b7a !important;
    color: #007b7a !important;
}
.datepicker-day-view .month-grid-box {
    margin: 0 !important;
    width: 100% !important;
}
.datepicker-day-view .month-grid-box .header {
    padding-bottom: 8px !important;
}
.datepicker-day-view .month-grid-box .header .header-row-cell {
    font-size: 11.5px !important;
    font-weight: 800 !important;
    color: #007b7a !important;
    height: 28px !important;
    line-height: 28px !important;
}
.table-days {
    width: 100% !important;
    direction: rtl !important;
}
.table-days td {
    height: 34px !important;
    padding: 2px !important;
}
.table-days td span {
    width: 100% !important;
    height: 30px !important;
    line-height: 30px !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    color: #334155 !important;
    background: transparent !important;
    transition: all 0.18s ease !important;
    display: block !important;
}
.table-days td:hover span {
    background: #f0fdfa !important;
    color: #007b7a !important;
}
.table-days td.selected span {
    background: #007b7a !important;
    color: #ffffff !important;
    font-weight: 900 !important;
    box-shadow: 0 4px 12px rgba(0, 123, 122, 0.35) !important;
}
.table-days td.today span {
    border: 1.5px solid #007b7a !important;
    color: #007b7a !important;
    font-weight: 900 !important;
}
.table-days td.disabled span {
    color: #cbd5e1 !important;
    background: transparent !important;
    cursor: not-allowed !important;
}
.table-days td.other-month span {
    color: #cbd5e1 !important;
    opacity: 0.5 !important;
}

/* حالت انتخاب ساعت */
.datepicker-plot-area.datepicker-state-only-time {
    width: 250px !important;
    min-width: 250px !important;
    padding: 20px !important;
    text-align: center !important;
}
.datepicker-plot-area.datepicker-state-only-time .datepicker-time-view {
    background: #f8fafc !important;
    border-radius: 16px !important;
    border: 1px solid #e2e8f0 !important;
    padding: 16px 12px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 10px !important;
    margin: 0 !important;
    float: none !important;
}
.datepicker-plot-area.datepicker-state-only-time .time-segment {
    width: 76px !important;
    background: #ffffff !important;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 12px !important;
    padding: 6px 4px !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04) !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
}
.datepicker-plot-area.datepicker-state-only-time .time-segment input {
    font-size: 22px !important;
    font-weight: 900 !important;
    color: #007b7a !important;
    font-family: 'Vazirmatn', sans-serif !important;
    height: 36px !important;
    line-height: 36px !important;
    text-align: center !important;
}
.datepicker-plot-area.datepicker-state-only-time .up-btn,
.datepicker-plot-area.datepicker-state-only-time .down-btn {
    height: 24px !important;
    line-height: 24px !important;
    color: #64748b !important;
    font-size: 14px !important;
    font-weight: 900 !important;
    cursor: pointer !important;
    border-radius: 6px !important;
    transition: all 0.15s !important;
    width: 100% !important;
}
.datepicker-plot-area.datepicker-state-only-time .up-btn:hover,
.datepicker-plot-area.datepicker-state-only-time .down-btn:hover {
    color: #007b7a !important;
    background: #f0fdfa !important;
}
.datepicker-plot-area.datepicker-state-only-time .divider {
    font-size: 22px !important;
    font-weight: 900 !important;
    color: #007b7a !important;
    height: auto !important;
    line-height: normal !important;
}
.datepicker-plot-area.datepicker-state-no-second .second.time-segment,
.datepicker-plot-area.datepicker-state-no-second .second-divider {
    display: none !important;
}
.datepicker-plot-area.datepicker-state-no-meridian .meridian.time-segment,
.datepicker-plot-area.datepicker-state-no-meridian .meridian-divider {
    display: none !important;
}
@keyframes soSpin { 100% { transform: rotate(360deg); } }
.datepicker-plot-area .toolbox {
    margin-top: 14px !important;
    display: flex !important;
    justify-content: center !important;
    gap: 8px !important;
    float: none !important;
}
.datepicker-plot-area .toolbox .pwt-btn-today,
.datepicker-plot-area .toolbox .pwt-btn-submit {
    background: #007b7a !important;
    color: #ffffff !important;
    border-radius: 10px !important;
    padding: 8px 18px !important;
    font-size: 12.5px !important;
    font-weight: 800 !important;
    border: none !important;
    cursor: pointer !important;
    transition: all 0.2s !important;
    box-shadow: 0 4px 10px rgba(0, 123, 122, 0.25) !important;
    float: none !important;
    width: 100% !important;
}
.datepicker-plot-area .toolbox .pwt-btn-today:hover,
.datepicker-plot-area .toolbox .pwt-btn-submit:hover {
    background: #006665 !important;
    transform: translateY(-1px) !important;
}

/* ================== مدال سفارشی خیرین (جایگزین آلرت پیش‌فرض) ================== */
.so-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000000;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: soFadeIn 0.25s ease-out;
}
@keyframes soFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.so-modal-card {
    background: #ffffff;
    border-radius: 24px;
    width: 100%;
    max-width: 480px;
    padding: 34px 30px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    position: relative;
    text-align: center;
    animation: soModalZoom 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid rgba(0, 123, 122, 0.12);
    box-sizing: border-box;
}
@keyframes soModalZoom {
    from { opacity: 0; transform: scale(0.92) translateY(12px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.so-modal-close {
    position: absolute;
    top: 18px;
    left: 18px;
    background: #f3f4f6;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
}
.so-modal-close:hover {
    background: #e5e7eb;
    color: #1f2937;
    transform: rotate(90deg);
}
.so-modal-close svg { width: 18px; height: 18px; }

.so-modal-icon-wrap {
    margin-bottom: 20px;
    display: flex;
    justify-content: center;
}
.so-modal-icon {
    width: 72px;
    height: 72px;
    border-radius: 24px;
    display: grid;
    place-items: center;
    position: relative;
}
.so-modal-icon.auth {
    background: linear-gradient(135deg, rgba(0,123,122,0.12), rgba(79,178,176,0.22));
    color: #007b7a;
    box-shadow: 0 10px 24px -6px rgba(0,123,122,0.3);
}
.so-modal-icon.success {
    background: linear-gradient(135deg, rgba(22,163,122,0.14), rgba(34,197,94,0.22));
    color: #16a37a;
    box-shadow: 0 10px 24px -6px rgba(22,163,122,0.3);
}
.so-modal-icon svg { width: 36px; height: 36px; }

.so-modal-title {
    font-size: 20px;
    font-weight: 900;
    color: #1f2937;
    margin: 0 0 10px;
}
.so-modal-desc {
    font-size: 14px;
    color: #4b5563;
    line-height: 1.8;
    margin: 0 0 20px;
}

.so-modal-features {
    background: #f8fafc;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    padding: 14px 16px;
    margin-bottom: 24px;
    text-align: right;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.so-m-feat {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #334155;
    font-weight: 600;
}
.so-m-feat svg {
    width: 16px;
    height: 16px;
    color: #007b7a;
    flex-shrink: 0;
}

.so-modal-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.so-btn-primary {
    width: 100%;
    padding: 14px;
    background: #007b7a;
    color: #ffffff !important;
    border: none;
    border-radius: 14px;
    font-family: inherit;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.25s;
    box-shadow: 0 6px 18px rgba(0,123,122,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    box-sizing: border-box;
}
.so-btn-primary:hover {
    background: #006665;
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(0,123,122,0.4);
    color: #ffffff !important;
}
.so-btn-primary svg { width: 18px; height: 18px; }

.so-btn-ghost {
    width: 100%;
    padding: 12px;
    background: transparent;
    color: #64748b;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    box-sizing: border-box;
}
.so-btn-ghost:hover {
    background: #f8fafc;
    color: #1f2937;
    border-color: #cbd5e1;
}

/* کارت کد رهگیری سفارش موفق */
.so-tracking-card {
    background: #f0fdfa;
    border: 1.5px dashed #007b7a;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 24px;
}
.so-tr-lbl {
    display: block;
    font-size: 12.5px;
    color: #006665;
    font-weight: 700;
    margin-bottom: 8px;
}
.so-tr-val-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 8px;
}
.so-tr-val-row strong {
    font-size: 24px;
    font-weight: 900;
    color: #007b7a;
    letter-spacing: 1.5px;
    font-family: inherit;
}
.so-btn-copy {
    background: #ffffff;
    border: 1px solid #99f6e4;
    color: #007b7a;
    border-radius: 10px;
    padding: 5px 10px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
}
.so-btn-copy:hover {
    background: #007b7a;
    color: #ffffff;
}
.so-btn-copy svg { width: 14px; height: 14px; }
.so-tr-hint {
    font-size: 12px;
    color: #64748b;
    line-height: 1.6;
}

/* Toast */
.so-toast {
    position: fixed;
    bottom: 28px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: #1f2937;
    color: #ffffff;
    padding: 12px 24px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    z-index: 10000000;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    display: flex;
    align-items: center;
    gap: 10px;
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
}
.so-toast.show {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}
.so-toast.warning { background: #b45309; }
.so-toast.error { background: #b91c1c; }
.so-toast.success { background: #047857; }
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
                        <div class="so-input-icon-wrap">
                            <input type="text" name="event_date" id="eventDateInput" class="pdate" required placeholder="انتخاب تاریخ شمسی" readonly style="background:#fff; cursor:pointer">
                            <svg class="so-field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="so-field">
                        <label>ساعت برگزاری مراسم (دقیقه : ساعت) <span class="req">*</span></label>
                        <div class="so-input-icon-wrap">
                            <input type="text" name="event_time" id="eventTimeInput" class="ptime" required placeholder="انتخاب ساعت مراسم" readonly style="background:#fff; cursor:pointer">
                            <svg class="so-field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
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

<!-- مدال اختصاصی ورود نیکوکاران -->
<div class="so-modal-overlay" id="soAuthModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="soAuthTitle">
    <div class="so-modal-card">
        <button type="button" class="so-modal-close" id="soAuthClose" aria-label="بستن">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="so-modal-icon-wrap">
            <div class="so-modal-icon auth">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        </div>
        <h3 class="so-modal-title" id="soAuthTitle">ورود به حساب کاربری نیکوکاران</h3>
        <p class="so-modal-desc">
            برای ثبت نهایی سفارش استند و صدور کد پیگیری رسمی، لطفاً ابتدا وارد حساب کاربری خود شوید یا ثبت‌نام فرمایید. اطلاعات فرم شما محفوظ بوده و پس از ورود باقی خواهد ماند.
        </p>
        <div class="so-modal-features">
            <div class="so-m-feat">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>صدور رسید و کد پیگیری آنلاین سفارش</span>
            </div>
            <div class="so-m-feat">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>پیگیری لحظه‌ای وضعیت ارسال و استقرار استند توسط شعبه</span>
            </div>
            <div class="so-m-feat">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>ثبت در پرونده نیکوکاری و مشاهده سوابق نذورات در مکسا</span>
            </div>
        </div>
        <div class="so-modal-actions">
            <a href="/benefactor-dashboard/login" class="so-btn-primary" id="soLoginBtn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                ورود به حساب کاربری
            </a>
            <a href="/benefactor-dashboard/register" class="so-btn-ghost" id="soRegisterBtn">
                ثبت‌نام نیکوکار جدید
            </a>
        </div>
    </div>
</div>

<!-- مدال ثبت موفق سفارش استند -->
<div class="so-modal-overlay" id="soSuccessModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="soSuccessTitle">
    <div class="so-modal-card">
        <button type="button" class="so-modal-close" id="soSuccessClose" aria-label="بستن">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="so-modal-icon-wrap">
            <div class="so-modal-icon success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
        <h3 class="so-modal-title" id="soSuccessTitle">سفارش استند با موفقیت ثبت شد</h3>
        <p class="so-modal-desc">
            از حسن اعتماد و همدلی شما با بیماران مبتلا به سرطان صمیمانه سپاسگزاریم. سفارش شما با مشخصات زیر جهت هماهنگی و ارسال به شعبه مربوطه تحویل گردید.
        </p>
        <div class="so-tracking-card">
            <span class="so-tr-lbl">شماره و کد پیگیری سفارش شما:</span>
            <div class="so-tr-val-row">
                <strong id="soTrackingCodeVal">---</strong>
                <button type="button" class="so-btn-copy" id="soCopyTrackingBtn" title="کپی کد پیگیری">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    <span>کپی کد</span>
                </button>
            </div>
            <div class="so-tr-hint">کارشناسان شعبه به زودی جهت هماهنگی نهایی با شماره تماس ثبت‌شده ارتباط برقرار خواهند کرد.</div>
        </div>
        <div class="so-modal-actions">
            <a href="/benefactor-dashboard/stands" class="so-btn-primary">
                پیگیری سفارش در پنل کاربری
            </a>
            <button type="button" class="so-btn-ghost" id="soNewOrderBtn">
                ثبت سفارش جدید
            </button>
        </div>
    </div>
</div>

<!-- توست پیام‌های سیستم -->
<div class="so-toast" id="soToast"></div>

<!-- اسکریپت‌های تقویم و تعاملات صفحه -->
<script src="/dashboard/assets/vendor/libs/jquery/jquery.js"></script>
<script src="/dashboard/assets/js/datepicker/persian-date.min.js"></script>
<script src="/dashboard/assets/js/datepicker/persian-datepicker.min.js"></script>

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

// راه‌اندازی تقویم و ساعت شمسی با دقت دقیقه
function initDatePickers() {
    if (!window.jQuery || !jQuery.fn.persianDatepicker) {
        setTimeout(initDatePickers, 60);
        return;
    }

    // تقویم شمسی برای تاریخ مراسم
    $('.pdate').persianDatepicker({
        format: 'YYYY/MM/DD',
        autoClose: true,
        initialValue: false,
        minDate: new persianDate().valueOf(),
        navigator: {
            scroll: { enabled: false }
        },
        toolbox: {
            calendarSwitch: { enabled: false },
            todayButton: { enabled: true }
        }
    });

    // ساعت مراسم با دقت دقیقه و بدون ثانیه
    $('.ptime').persianDatepicker({
        format: 'HH:mm',
        onlyTimePicker: true,
        autoClose: true,
        initialValue: false,
        timePicker: {
            enabled: true,
            step: 1,
            hour: { enabled: true, step: 1 },
            minute: { enabled: true, step: 5 },
            second: { enabled: false },
            meridian: { enabled: false }
        },
        toolbox: {
            submitButton: {
                enabled: true,
                text: { fa: 'تأیید ساعت', en: 'Confirm' }
            }
        }
    });
}
initDatePickers();

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

// دریافت توکن احراز هویت خیرین (بررسی حافظه، استوریج و کوکی رفرش)
async function fetchAccessToken() {
    if (window.__maxa_access_token) return window.__maxa_access_token;

    try {
        const s = sessionStorage.getItem('maxa_access_token');
        if (s) { window.__maxa_access_token = s; return s; }
    } catch (e) {}

    try {
        const l = localStorage.getItem('access_token') || localStorage.getItem('token') || localStorage.getItem('maxa_access_token');
        if (l) { window.__maxa_access_token = l; return l; }
    } catch (e) {}

    try {
        const res = await fetch('/api/auth/refresh', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });
        if (res.ok) {
            const data = await res.json();
            const token = data?.data?.access_token;
            if (token) {
                window.__maxa_access_token = token;
                try {
                    sessionStorage.setItem('maxa_access_token', token);
                    localStorage.setItem('maxa_access_token', token);
                } catch (e) {}
                return token;
            }
        }
    } catch (e) {}

    return null;
}

// پیام‌های توست
function showToast(message, type = 'info') {
    const toast = document.getElementById('soToast');
    if (!toast) return;
    toast.textContent = message;
    toast.className = 'so-toast ' + type;
    toast.classList.add('show');
    clearTimeout(window.__soToastTimer);
    window.__soToastTimer = setTimeout(() => {
        toast.classList.remove('show');
    }, 3800);
}

// کنترل مدال‌ها
function openAuthModal() {
    const modal = document.getElementById('soAuthModal');
    if (!modal) return;
    const currentUrl = window.location.href;
    const loginBtn = document.getElementById('soLoginBtn');
    const regBtn = document.getElementById('soRegisterBtn');
    if (loginBtn) loginBtn.href = '/benefactor-dashboard/login?returnUrl=' + encodeURIComponent(currentUrl);
    if (regBtn) regBtn.href = '/benefactor-dashboard/register?returnUrl=' + encodeURIComponent(currentUrl);
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeAuthModal() {
    const modal = document.getElementById('soAuthModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function openSuccessModal(trackingCode) {
    const modal = document.getElementById('soSuccessModal');
    if (!modal) return;
    const valEl = document.getElementById('soTrackingCodeVal');
    if (valEl) valEl.textContent = trackingCode || '---';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeSuccessModal() {
    const modal = document.getElementById('soSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// رویدادهای بستن و کپی مدال
document.getElementById('soAuthClose')?.addEventListener('click', closeAuthModal);
document.getElementById('soSuccessClose')?.addEventListener('click', closeSuccessModal);
document.getElementById('soNewOrderBtn')?.addEventListener('click', closeSuccessModal);

document.querySelectorAll('.so-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAuthModal();
        closeSuccessModal();
    }
});

document.getElementById('soCopyTrackingBtn')?.addEventListener('click', function() {
    const code = document.getElementById('soTrackingCodeVal')?.textContent;
    if (code && code !== '---') {
        navigator.clipboard.writeText(code).then(() => {
            showToast('کد پیگیری با موفقیت کپی شد', 'success');
        }).catch(() => {
            showToast('کد پیگیری: ' + code, 'info');
        });
    }
});

// ثبت فرم سفارش استند
document.getElementById('standOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!provinceSelect.value) {
        showToast('لطفاً ابتدا استان و شعبه مورد نظر را انتخاب فرمایید.', 'warning');
        provinceSelect.focus();
        return;
    }

    if (!state.selectedStand) {
        showToast('لطفاً یک طرح استند را از لیست سمت چپ انتخاب فرمایید.', 'warning');
        return;
    }

    const eventDate = (document.getElementById('eventDateInput')?.value || '').trim();
    if (!eventDate) {
        showToast('لطفاً تاریخ برگزاری مراسم را مشخص فرمایید.', 'warning');
        document.getElementById('eventDateInput')?.focus();
        return;
    }

    const eventTime = (document.getElementById('eventTimeInput')?.value || '').trim();
    if (!eventTime) {
        showToast('لطفاً ساعت برگزاری مراسم را مشخص فرمایید.', 'warning');
        document.getElementById('eventTimeInput')?.focus();
        return;
    }

    const formData = new FormData(this);
    const payload = Object.fromEntries(formData.entries());
    payload.unit_price = state.selectedStand.unit_price;
    payload.quantity = 1;
    payload.total_price = state.selectedStand.unit_price;
    payload.branch_id = state.selectedBranchId;

    // بررسی وضعیت ورود کاربر
    const token = await fetchAccessToken();
    if (!token) {
        try {
            localStorage.setItem('pendingStandOrder', JSON.stringify(payload));
        } catch(e) {}
        openAuthModal();
        return;
    }

    // ارسال سفارش با توکن معتبر
    const btn = document.getElementById('submitOrderBtn');
    const originalContent = btn.innerHTML;
    btn.innerHTML = `
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:20px;height:20px;animation:soSpin 1s linear infinite"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/></svg>
        در حال ثبت سفارش...
    `;
    btn.disabled = true;

    try {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
        };

        const submitRes = await fetch('/api/orders', {
            method: 'POST',
            credentials: 'include',
            headers: headers,
            body: JSON.stringify(payload)
        });

        if (submitRes.status === 401) {
            window.__maxa_access_token = null;
            try {
                sessionStorage.removeItem('maxa_access_token');
                localStorage.removeItem('maxa_access_token');
                localStorage.setItem('pendingStandOrder', JSON.stringify(payload));
            } catch(e) {}
            openAuthModal();
            return;
        }

        const result = await submitRes.json();
        if (submitRes.ok && result.success) {
            try { localStorage.removeItem('pendingStandOrder'); } catch(e) {}
            const tracking = result.data?.tracking_code || '';
            openSuccessModal(tracking);
            this.reset();
            showEmptyGuide();
        } else {
            showToast(result.error?.message || 'خطا در ثبت سفارش. لطفاً اطلاعات را بررسی نمایید.', 'error');
        }
    } catch (err) {
        showToast('خطای اتصال به سرور. لطفاً اتصال اینترنت خود را بررسی نمایید.', 'error');
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

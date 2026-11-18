<?php
require_once __DIR__ . '/../../../core/site-locale.php';
require_once __DIR__ . '/../../../core/content-locales.php';

// دریافت اولیه لیست استان‌ها و شعب فعال غیرستادی
$initialProvinces = [];

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $cfgPath = dirname(__DIR__, 3) . '/core/db-config.php';
        if (file_exists($cfgPath)) {
            $DB = require $cfgPath;
            $host = $DB['host'] ?? 'localhost';
            $name = $DB['name'] ?? '';
            $user = $DB['user'] ?? '';
            $pass = $DB['pass'] ?? '';
            $charset = $DB['charset'] ?? 'utf8mb4';
            $pdo = new PDO("mysql:host={$host};dbname={$name};charset={$charset}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
    }
    if (isset($pdo) && ($pdo instanceof PDO)) {
        $standScope = content_where($pdo, 'stands', 's.id');
        $st = $pdo->query("
            SELECT DISTINCT b.id AS branch_id, b.name AS branch_name, 
                   COALESCE(b.province, b.name) AS province, 
                   COALESCE(b.city, b.name) AS city
            FROM branches b
            LEFT JOIN branch_features bf ON bf.branch_id = b.id AND bf.feature = 'stands'
            WHERE EXISTS(SELECT 1 FROM stands s WHERE s.branch_id=b.id AND s.is_active=1 AND $standScope) AND b.is_hq = 0 AND b.status = 'active' AND (bf.enabled = 1 OR bf.enabled IS NULL)
            ORDER BY province ASC
        ");
        $rows = content_branch_labels($pdo, $st->fetchAll(PDO::FETCH_ASSOC));
        foreach ($rows as $r) {
            $p = trim((string)$r['province']);
            if ($p === '' || str_contains($p, 'ستاد')) continue;
            $key = (string)(int)$r['branch_id'];
            $initialProvinces[$key] = [
                'province'  => $p,
                'branch_id' => (int)$r['branch_id'],
                'branch_name' => (string)$r['branch_name'],
                'cities'    => []
            ];
            $c = trim((string)$r['city']);
            if ($c !== '') {
                $initialProvinces[$key]['cities'][] = $c;
            }
        }
    }
} catch (Throwable $e) {}

$initialProvincesList = array_values($initialProvinces);

// در صورت عدم اتصال به دیتابیس یا خالی بودن اولیه، شعب پیش‌فرض استاندارد مکسا بارگذاری می‌شوند
if (empty($initialProvincesList)) {
    $initialProvincesList = [
        ['province' => 'تهران', 'branch_id' => 1, 'branch_name' => 'مرکزی تهران', 'cities' => ['تهران', 'ری', 'شمیرانات', 'اسلامشهر']],
        ['province' => 'اصفهان', 'branch_id' => 2, 'branch_name' => 'اصفهان', 'cities' => ['اصفهان', 'کاشان', 'نجف‌آباد', 'خمینی‌شهر']],
        ['province' => 'خراسان رضوی', 'branch_id' => 3, 'branch_name' => 'مشهد مقدس', 'cities' => ['مشهد', 'نیشابور', 'سبزوار']],
        ['province' => 'فارس', 'branch_id' => 4, 'branch_name' => 'شیراز', 'cities' => ['شیراز', 'مرودشت', 'جهرم']],
        ['province' => 'قم', 'branch_id' => 5, 'branch_name' => 'قم', 'cities' => ['قم']],
        ['province' => 'کرمان', 'branch_id' => 6, 'branch_name' => 'کرمان', 'cities' => ['کرمان', 'رفسنجان']],
        ['province' => 'آذربایجان شرقی', 'branch_id' => 7, 'branch_name' => 'تبریز', 'cities' => ['تبریز', 'مراغه']]
    ];
}
?>

<!-- Persian Datepicker CSS -->
<link rel="stylesheet" href="/dashboard/assets/css/persian-datepicker.min.css">

<style>
/* ================== استایل‌های پایه فرم ۳ مرحله‌ای ================== */
.so-wrap {
    max-width: var(--cta-container, 1280px);
    margin: 0 auto;
    padding: 32px 20px 60px;
    font-family: 'Vazirmatn', Tahoma, sans-serif;
    direction: var(--site-direction, rtl);
    color: #1f2937;
    box-sizing: border-box;
}

.so-wrap * {
    box-sizing: border-box;
}

.so-head {
    text-align: center;
    margin-bottom: 32px;
}
.so-head h1 {
    font-size: clamp(24px, 3.8vw, 34px);
    font-weight: 900;
    color: #0f172a;
    margin: 0 0 10px;
    line-height: 1.35;
}
.so-head p {
    color: #475569;
    font-size: 15.5px;
    line-height: 1.85;
    max-width: 720px;
    margin: 0 auto;
}

/* ================== نوار استپر ۳ مرحله‌ای مشابه روند ثبت‌نام ================== */
.so-stepper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 18px 32px;
    margin-bottom: 34px;
    box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.05);
    position: relative;
}

.so-step-item {
    display: flex;
    align-items: center;
    gap: 14px;
    cursor: pointer;
    user-select: none;
    transition: all 0.25s ease;
    flex-shrink: 0;
}

.so-step-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 17px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #f1f5f9;
    border: 2px solid #cbd5e1;
    color: #64748b;
    position: relative;
}

.so-step-circle svg {
    display: none;
    width: 22px;
    height: 22px;
    stroke-width: 2.8;
}

.so-step-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.so-step-title {
    font-size: 15px;
    font-weight: 800;
    color: #64748b;
    transition: color 0.25s;
}

.so-step-desc {
    font-size: 12px;
    color: #94a3b8;
    font-weight: 500;
}

/* خط اتصال بین مراحل */
.so-step-line {
    flex: 1;
    height: 3px;
    background: #e2e8f0;
    margin: 0 16px;
    border-radius: 2px;
    transition: background 0.35s ease;
}

/* حالت مرحله فعال (Active Step) */
.so-step-item.active .so-step-circle {
    background: #008f8a;
    border-color: #008f8a;
    color: #ffffff;
    box-shadow: 0 0 0 5px rgba(0, 143, 138, 0.18);
}
.so-step-item.active .so-step-title {
    color: #008f8a;
}
.so-step-item.active .so-step-desc {
    color: #0f172a;
}

/* حالت مرحله تکمیل‌شده (Completed Step) — تغییر رنگ به سبز مطابق درخواست کاربر */
.so-step-item.completed .so-step-circle {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: #ffffff !important;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.16);
}
.so-step-item.completed .so-step-circle .so-step-num {
    display: none;
}
.so-step-item.completed .so-step-circle svg {
    display: block;
    color: #ffffff;
}
.so-step-item.completed .so-step-title {
    color: #059669;
}
.so-step-item.completed .so-step-desc {
    color: #10b981;
}

/* خط تکمیل‌شده تبدیل به سبز می‌شود */
.so-step-line.filled {
    background: #10b981 !important;
}

@media (max-width: 860px) {
    .so-stepper {
        padding: 14px 18px;
        margin-bottom: 24px;
    }
    .so-step-circle {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }
    .so-step-circle svg {
        width: 18px;
        height: 18px;
    }
    .so-step-title {
        font-size: 13px;
    }
    .so-step-desc {
        display: none;
    }
    .so-step-line {
        margin: 0 8px;
    }
}

@media (max-width: 520px) {
    .so-step-title {
        display: none;
    }
    .so-stepper {
        justify-content: space-around;
        padding: 12px 14px;
    }
    .so-step-line {
        margin: 0 6px;
    }
}

/* ================== کانتینر پنل‌های مراحل ================== */
.so-step-pane {
    display: none;
    animation: soFadeIn 0.3s ease;
}
.so-step-pane.active {
    display: block;
}

@keyframes soFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ================== مرحله ۱: انتخاب استان و شهر (بزرگتر و قشنگ‌تر) ================== */
.so-step1-card {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
    padding: 36px 32px;
    box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.06);
}

.so-step-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
    padding-bottom: 18px;
    border-bottom: 1px solid #f1f5f9;
}
.so-step-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(0, 143, 138, 0.1);
    color: #008f8a;
    font-weight: 800;
    font-size: 18px;
    flex-shrink: 0;
}
.so-step-badge svg {
    width: 22px;
    height: 22px;
}
.so-step-header-text h2 {
    font-size: 20px;
    font-weight: 900;
    color: #0f172a;
    margin: 0 0 4px;
}
.so-step-header-text p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

/* شبکه کارت‌های استان‌ها (بزرگ، شفاف و جذاب) */
.so-prov-grid-title {
    font-size: 15px;
    font-weight: 800;
    color: #334155;
    margin: 0 0 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.so-prov-grid-title svg {
    width: 18px;
    height: 18px;
    color: #008f8a;
}

.so-province-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.so-prov-card {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 18px 16px;
    cursor: pointer;
    transition: all 0.22s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    user-select: none;
}
.so-prov-card:hover {
    border-color: #94a3b8;
    background: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px -4px rgba(15, 23, 42, 0.08);
}
.so-prov-card.selected {
    background: #f0fdfa;
    border-color: #008f8a;
    box-shadow: 0 0 0 3px rgba(0, 143, 138, 0.15);
}

.so-prov-card-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.so-prov-name {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
}
.so-prov-card.selected .so-prov-name {
    color: #008f8a;
}
.so-prov-branch {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
}

.so-prov-radio {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    flex-shrink: 0;
    transition: all 0.2s;
}
.so-prov-card.selected .so-prov-radio {
    border-color: #008f8a;
    background: #008f8a;
}
.so-prov-radio svg {
    display: none;
    width: 14px;
    height: 14px;
    color: #ffffff;
    stroke-width: 3;
}
.so-prov-card.selected .so-prov-radio svg {
    display: block;
}

/* بخش انتخاب شهر (پویا و واضح) */
.so-city-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 22px;
    margin-bottom: 24px;
    transition: all 0.3s ease;
}

.so-city-title {
    font-size: 14.5px;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.so-city-title svg {
    width: 18px;
    height: 18px;
    color: #008f8a;
}

.so-city-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}
.so-city-chip {
    padding: 10px 18px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s;
    user-select: none;
}
.so-city-chip:hover {
    border-color: #008f8a;
    color: #008f8a;
}
.so-city-chip.selected {
    background: #008f8a;
    border-color: #008f8a;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 143, 138, 0.25);
}

.so-city-select-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}
.so-city-select-wrap select {
    width: 100%;
    max-width: 360px;
    padding: 12px 14px;
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    font-family: inherit;
    font-size: 14px;
    background: #ffffff;
    color: #1f2937;
    outline: none;
}
.so-city-select-wrap select:focus {
    border-color: #008f8a;
    box-shadow: 0 0 0 3px rgba(0, 143, 138, 0.12);
}

/* یادآوری محدوده شهری */
.so-notice-box {
    background: #fffbeb;
    border: 1px solid #fef3c7;
    border-right: 4px solid #f59e0b;
    border-radius: 14px;
    padding: 14px 18px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 13.5px;
    color: #92400e;
    line-height: 1.75;
}
.so-notice-box svg {
    width: 22px;
    height: 22px;
    flex-shrink: 0;
    color: #d97706;
}

/* دکمه‌های ناوبری مراحل */
.so-actions-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid #f1f5f9;
}

.so-btn-next,
.so-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 28px;
    background: #008f8a;
    color: #ffffff;
    border: none;
    border-radius: 14px;
    font-family: inherit;
    font-size: 15.5px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.22s ease;
    box-shadow: 0 6px 18px rgba(0, 143, 138, 0.25);
    text-decoration: none;
}
.so-btn-next:hover,
.so-submit:hover {
    background: #007672;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 143, 138, 0.35);
}
.so-btn-next svg,
.so-submit svg {
    width: 20px;
    height: 20px;
}

.so-btn-prev {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 22px;
    background: #f1f5f9;
    color: #475569;
    border: 1.5px solid #cbd5e1;
    border-radius: 14px;
    font-family: inherit;
    font-size: 14.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.so-btn-prev:hover {
    background: #e2e8f0;
    color: #1e293b;
}
.so-btn-prev svg {
    width: 18px;
    height: 18px;
}

/* ================== مرحله ۲: انتخاب طرح استند ================== */
.so-step2-card {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
    padding: 32px 28px;
    box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.06);
}

.so-selected-badge-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f0fdfa;
    border: 1px solid #ccfbf1;
    border-radius: 14px;
    padding: 12px 18px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 10px;
}
.so-sbb-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: #0f766e;
}
.so-sbb-info svg {
    width: 18px;
    height: 18px;
    color: #008f8a;
}
.so-sbb-change {
    background: none;
    border: none;
    color: #008f8a;
    font-family: inherit;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    text-decoration: underline;
    padding: 0;
}

/* تب‌های دسته‌بندی استندها */
.so-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 24px;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 12px;
}
.so-tab-btn {
    padding: 9px 18px;
    border: 1.5px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 12px;
    font-family: inherit;
    font-size: 13.5px;
    font-weight: 700;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}
.so-tab-btn:hover {
    border-color: #cbd5e1;
    color: #1e293b;
}
.so-tab-btn.active {
    background: #008f8a;
    color: #ffffff;
    border-color: #008f8a;
    box-shadow: 0 4px 12px rgba(0, 143, 138, 0.2);
}

/* شبکه استندها و پیش‌نمایش در مرحله ۲ */
.so-step2-grid {
    display: grid;
    grid-template-columns: 1.25fr 0.75fr;
    gap: 28px;
    align-items: start;
}
@media (max-width: 960px) {
    .so-step2-grid {
        grid-template-columns: 1fr;
    }
    .so-preview-col {
        order: -1;
    }
}

/* شبکه کارت‌های استند در مرحله ۲ */
.so-stands-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
    max-height: 640px;
    overflow-y: auto;
    padding-left: 6px;
}

.so-stand-card {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 18px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    cursor: pointer;
    transition: all 0.22s ease;
    user-select: none;
    position: relative;
}
.so-stand-card:hover {
    border-color: #94a3b8;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.08);
}
.so-stand-card.selected {
    border-color: #10b981;
    background: #f0fdf4;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.so-stand-card-img-wrap {
    width: 100%;
    height: 200px;
    border-radius: 12px;
    overflow: hidden;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
}
.so-stand-card-img-wrap img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.25s;
}
.so-stand-card:hover .so-stand-card-img-wrap img {
    transform: scale(1.04);
}

.so-stand-card-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}
.so-stand-card-title {
    font-size: 14.5px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.4;
    margin: 0;
}
.so-stand-card-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    margin-top: auto;
}
.so-stand-tag {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
}
.so-stand-tag.congrats {
    background: #fef3c7;
    color: #b45309;
}
.so-stand-tag.condolence {
    background: #f1f5f9;
    color: #475569;
}

.so-stand-card-price {
    font-size: 15px;
    font-weight: 900;
    color: #008f8a;
}
.so-stand-card-price span {
    font-size: 11.5px;
    font-weight: 600;
    color: #64748b;
}

.so-stand-card-btn {
    width: 100%;
    padding: 9px;
    background: #f1f5f9;
    border: none;
    border-radius: 10px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 800;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}
.so-stand-card.selected .so-stand-card-btn {
    background: #10b981;
    color: #ffffff;
}

/* پنل پیش‌نمایش ۳ بعدی استند در مرحله ۲ */
.so-preview-col {
    position: sticky;
    top: 90px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 22px;
}

.so-preview-title {
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.so-preview-title svg {
    width: 16px;
    height: 16px;
    color: #008f8a;
}

.so-scene {
    width: 100%;
    height: 270px;
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
    width: 170px;
    height: 250px;
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
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    padding: 18px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}
.so-stand-face.back svg {
    width: 38px;
    height: 38px;
    color: #008f8a;
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
}

.so-360-hint {
    font-size: 11px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    margin-bottom: 12px;
}
.so-360-hint svg {
    width: 14px;
    height: 14px;
    color: #008f8a;
}

.so-selected-meta {
    background: #ffffff;
    border-radius: 12px;
    padding: 12px 14px;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.so-meta-title {
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
}
.so-meta-price {
    font-size: 17px;
    font-weight: 900;
    color: #008f8a;
}
.so-meta-price span {
    font-size: 11.5px;
    font-weight: 600;
    color: #64748b;
}

/* پیام عدم وجود استند */
.empty-stands-msg {
    text-align: center;
    padding: 40px 20px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 16px;
    color: #64748b;
    font-size: 14px;
    grid-column: 1 / -1;
}

/* ================== مرحله ۳: ثبت اطلاعات و تایید نهایی ================== */
.so-step3-card {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
    padding: 36px 32px;
    box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.06);
}

/* کارت خلاصه سفارش انتخابی در بالای مرحله ۳ */
.so-order-summary-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f0fdfa;
    border: 1.5px solid #99f6e4;
    border-radius: 18px;
    padding: 16px 20px;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
}

.so-os-right {
    display: flex;
    align-items: center;
    gap: 16px;
}
.so-os-thumb {
    width: 60px;
    height: 70px;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.so-os-thumb img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.so-os-title-wrap {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.so-os-title {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
}
.so-os-loc {
    font-size: 13px;
    color: #0f766e;
    font-weight: 600;
}

.so-os-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.so-os-price {
    font-size: 19px;
    font-weight: 900;
    color: #008f8a;
    display: flex;
    align-items: baseline;
    gap: 4px;
}
.so-os-price span {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
}
.so-os-change-btn {
    padding: 8px 14px;
    background: #ffffff;
    border: 1.5px solid #008f8a;
    border-radius: 10px;
    color: #008f8a;
    font-family: inherit;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}
.so-os-change-btn:hover {
    background: #008f8a;
    color: #ffffff;
}

/* گروه‌های فیلدها */
.so-section-title {
    font-size: 15px;
    font-weight: 800;
    color: #008f8a;
    margin: 24px 0 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.so-section-title::before {
    content: "";
    width: 6px;
    height: 18px;
    background: #008f8a;
    border-radius: 3px;
}

.so-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
@media (max-width: 640px) {
    .so-field-row {
        grid-template-columns: 1fr;
    }
}

.so-field {
    margin-bottom: 18px;
}
.so-field label {
    display: block;
    font-size: 13.5px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
}
.so-field label .req {
    color: #ef4444;
}

.so-field input,
.so-field select,
.so-field textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    font-family: inherit;
    font-size: 13.5px;
    color: #1f2937;
    background: #f8fafc;
    outline: none;
    transition: all 0.2s;
}
.so-field input:focus,
.so-field select:focus,
.so-field textarea:focus {
    border-color: #008f8a;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(0, 143, 138, 0.12);
}
.so-field textarea {
    resize: vertical;
    min-height: 80px;
    line-height: 1.7;
}

.so-input-icon-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.so-field-icon {
    position: absolute;
    left: 14px;
    width: 18px;
    height: 18px;
    color: #94a3b8;
    pointer-events: none;
}

/* باکس ارزش نیکوکاری و وقف */
.so-charity-box {
    background: #ecfdf5;
    border: 1.5px solid #a7f3d0;
    border-radius: 14px;
    padding: 14px 18px;
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13.5px;
    color: #065f46;
    line-height: 1.7;
}
.so-charity-box svg {
    width: 22px;
    height: 22px;
    color: #10b981;
    flex-shrink: 0;
}

/* یادداشت نهایی */
.so-note {
    text-align: center;
    font-size: 13px;
    color: #64748b;
    margin-top: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.so-note svg {
    color: #008f8a;
    width: 15px;
    height: 15px;
}

/* ================== مدال‌ها و نوتیفیکیشن‌ها ================== */
.so-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    padding: 20px;
}
.so-modal-card {
    background: #ffffff;
    border-radius: 24px;
    max-width: 480px;
    width: 100%;
    padding: 32px 28px;
    text-align: center;
    position: relative;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
    animation: soFadeIn 0.25s ease;
}
.so-modal-close {
    position: absolute;
    top: 16px;
    left: 16px;
    background: none;
    border: none;
    cursor: pointer;
    color: #94a3b8;
    padding: 6px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    transition: all 0.2s;
}
.so-modal-close:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.so-modal-icon-wrap {
    margin-bottom: 18px;
}
.so-modal-icon {
    width: 64px;
    height: 64px;
    border-radius: 20px;
    display: grid;
    place-items: center;
    margin: 0 auto;
}
.so-modal-icon.auth {
    background: rgba(0, 143, 138, 0.1);
    color: #008f8a;
}
.so-modal-icon.success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}
.so-modal-icon svg {
    width: 32px;
    height: 32px;
}
.so-modal-title {
    font-size: 19px;
    font-weight: 900;
    color: #0f172a;
    margin: 0 0 10px;
}
.so-modal-desc {
    font-size: 14px;
    color: #64748b;
    line-height: 1.75;
    margin: 0 0 20px;
}

.so-tracking-card {
    background: #f8fafc;
    border: 1.5px dashed #cbd5e1;
    border-radius: 14px;
    padding: 16px;
    margin-bottom: 24px;
}
.so-tr-lbl {
    display: block;
    font-size: 12.5px;
    color: #64748b;
    margin-bottom: 8px;
}
.so-tr-val-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}
.so-tr-val-row strong {
    font-size: 21px;
    font-weight: 900;
    color: #008f8a;
    letter-spacing: 1px;
}
.so-btn-copy {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    border-radius: 8px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}
.so-btn-copy:hover {
    border-color: #008f8a;
    color: #008f8a;
}
.so-btn-copy svg {
    width: 14px;
    height: 14px;
}

.so-modal-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.so-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px;
    background: #008f8a;
    color: #ffffff;
    border-radius: 12px;
    font-weight: 800;
    font-size: 14.5px;
    text-decoration: none;
    transition: all 0.2s;
}
.so-btn-primary:hover {
    background: #007672;
}
.so-btn-ghost {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 11px;
    background: #f1f5f9;
    color: #475569;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}
.so-btn-ghost:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.so-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 14px 20px;
    border-radius: 12px;
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    z-index: 999999;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    display: none;
    animation: soFadeIn 0.2s ease;
}
.so-toast.info { background: #0284c7; }
.so-toast.warning { background: #d97706; }
.so-toast.error { background: #dc2626; }
.so-toast.success { background: #059669; }
</style>

<div class="so-wrap">
    <!-- عنوان و توضیح صفحه -->
    <div class="so-head">
        <h1><?= site_e('استندها و کارت‌های تبریک و تسلیت') ?></h1>
        <p><?= site_e('با سفارش استندها و کارت‌های مکسا، پیام پرمحبت خود را ماندگار سازید و تمام عواید آن را نذر دارو و درمان رایگان بیماران مبتلا به سرطان نمایید.') ?></p>
    </div>

    <!-- ================== استپر ۳ مرحله‌ای مشابه روند ثبت‌نام ================== -->
    <div class="so-stepper" id="soStepper" role="navigation" aria-label="<?= site_e('مراحل سفارش استند') ?>">

        <!-- مرحله ۱: انتخاب استان و شهر -->
        <div class="so-step-item active" data-step="1" id="stepIndicator1" onclick="goToStep(1)">
            <div class="so-step-circle">
                <span class="so-step-num">۱</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="so-step-text">
                <span class="so-step-title"><?= site_e('انتخاب استان و شهر') ?></span>
                <span class="so-step-desc"><?= site_e('تعیین شعبه و محل برگزاری') ?></span>
            </div>
        </div>

        <div class="so-step-line" id="stepLine1"></div>

        <!-- مرحله ۲: انتخاب طرح استند -->
        <div class="so-step-item" data-step="2" id="stepIndicator2" onclick="goToStep(2)">
            <div class="so-step-circle">
                <span class="so-step-num">۲</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="so-step-text">
                <span class="so-step-title"><?= site_e('انتخاب طرح استند') ?></span>
                <span class="so-step-desc"><?= site_e('تبریک یا ابراز همدردی') ?></span>
            </div>
        </div>

        <div class="so-step-line" id="stepLine2"></div>

        <!-- مرحله ۳: ثبت اطلاعات و تایید نهایی -->
        <div class="so-step-item" data-step="3" id="stepIndicator3" onclick="goToStep(3)">
            <div class="so-step-circle">
                <span class="so-step-num">۳</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="so-step-text">
                <span class="so-step-title"><?= site_e('ثبت اطلاعات و تایید نهایی') ?></span>
                <span class="so-step-desc"><?= site_e('مشخصات، زمان و محل مراسم') ?></span>
            </div>
        </div>
    </div>

    <!-- فرم جامع سفارش استند شامل هر ۳ مرحله -->
    <form id="standOrderForm">
        <input type="hidden" name="stand_id" id="inputStandId" value="">
        <input type="hidden" name="branch_id" id="inputBranchId" value="">
        <input type="hidden" name="province" id="inputProvince" value="">
        <input type="hidden" name="city" id="inputCity" value="">

        <!-- ================== مرحله ۱: انتخاب استان و شهر ================== -->
        <div class="so-step-pane active" id="paneStep1">
            <div class="so-step1-card">
                <div class="so-step-header">
                    <div class="so-step-badge">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="so-step-header-text">
                        <h2><?= site_e('مرحله اول: انتخاب استان و شهر محل برگزاری مراسم') ?></h2>
                        <p><?= site_e('لطفاً ابتدا استان و شهر مورد نظر را مشخص کنید تا طرح‌ها و استندهای فعال در محدوده شعبه مربوطه بارگذاری شوند.') ?></p>
                    </div>
                </div>

                <!-- شبکه کارت‌های استان‌ها -->
                <div class="so-prov-grid-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span><?= site_e('استان / شعبه ارائه‌دهنده استند را انتخاب کنید:') ?></span>
                </div>

                <div class="so-province-cards" id="provinceCards">
                    <?php foreach ($initialProvincesList as $idx => $p): ?>
                        <div class="so-prov-card" data-province="<?= htmlspecialchars($p['province']) ?>" data-branch="<?= (int)$p['branch_id'] ?>" onclick="selectProvinceCard(this)">
                            <div class="so-prov-card-info">
                                <span class="so-prov-name"><?= htmlspecialchars($p['province']) ?></span>
                                <span class="so-prov-branch"><?= site_e('شعبه') ?> <?= htmlspecialchars($p['branch_name']) ?></span>
                            </div>
                            <div class="so-prov-radio">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- دراپ‌داون استان جهت سازگاری و انتخاب سایر استان‌ها -->
                <div style="display:none;">
                    <select id="provinceSelect">
                        <option value=""><?= site_e('-- انتخاب استان --') ?></option>
                        <?php foreach ($initialProvincesList as $p): ?>
                            <option value="<?= htmlspecialchars($p['province']) ?>" data-branch="<?= (int)$p['branch_id'] ?>">
                                <?= htmlspecialchars($p['province']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- بخش انتخاب شهر -->
                <div class="so-city-box" id="citySelectionBox">
                    <div class="so-city-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span><?= site_e('شهر محل برگزاری مراسم را مشخص نمایید:') ?></span>
                    </div>

                    <div class="so-city-chips" id="cityChips">
                        <!-- دکمه‌های شهر به صورت پویا ساخته می‌شوند -->
                    </div>

                    <div class="so-city-select-wrap">
                        <select id="citySelect" aria-label="<?= site_e('انتخاب شهر') ?>">
                            <option value=""><?= site_e('-- ابتدا استان را انتخاب فرمایید --') ?></option>
                        </select>
                    </div>
                </div>

                <!-- هشدار محدوده شهری شعبه -->
                <div class="so-notice-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <strong><?= site_e('نکته مهم:') ?></strong> <?= site_e('ارسال، تحویل، استقرار و جمع‌آوری استندهای خیریه مکسا منحصراً در محدوده شهری شعبه انتخابی انجام می‌پذیرد و پذیرش سفارش برای خارج از محدوده شهری مقدور نیست.') ?>
                    </div>
                </div>

                <!-- اکشن رفتن به مرحله ۲ -->
                <div class="so-actions-row" style="justify-content:flex-end;">
                    <button type="button" class="so-btn-next" onclick="validateStep1AndProceed()">
                        <span><?= site_e('مرحله بعد: انتخاب طرح استند') ?></span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ================== مرحله ۲: انتخاب طرح استند ================== -->
        <div class="so-step-pane" id="paneStep2">
            <div class="so-step2-card">
                <div class="so-step-header">
                    <div class="so-step-badge">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="so-step-header-text">
                        <h2><?= site_e('مرحله دوم: انتخاب طرح استند') ?></h2>
                        <p><?= site_e('از میان طرح‌های تبریک یا تسلیت فعال در شعبه انتخابی، طرح مدنظر خود را انتخاب فرمایید.') ?></p>
                    </div>
                </div>

                <!-- بار مشخصات شهر انتخابی -->
                <div class="so-selected-badge-bar">
                    <div class="so-sbb-info">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span><?= site_e('استان و شهر انتخابی:') ?> <strong id="step2LocLabel">...</strong></span>
                    </div>
                    <button type="button" class="so-sbb-change" onclick="goToStep(1)">
                        <?= site_e('تغییر استان یا شهر') ?>
                    </button>
                </div>

                <!-- تب‌های فیلتر دسته‌بندی استند -->
                <div class="so-tabs">
                    <button type="button" class="so-tab-btn active" data-type="congrats"><?= site_e('تبریک و شادباش') ?></button>
                    <button type="button" class="so-tab-btn" data-type="condolence"><?= site_e('ابراز همدردی و تسلیت') ?></button>
                    <button type="button" class="so-tab-btn" data-type="all"><?= site_e('همه طرح‌ها') ?></button>
                </div>

                <!-- شبکه استندها و پیش‌نمایش ۳ بعدی -->
                <div class="so-step2-grid">
                    <div class="so-stands-grid" id="standsCatalog">
                        <!-- کارت‌های استند به صورت پویا با جاوااسکریپت لود می‌شوند -->
                    </div>

                    <!-- ستون پیش‌نمایش زنده ۳ بعدی -->
                    <div class="so-preview-col" id="soPreviewCol">
                        <div class="so-preview-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span><?= site_e('پیش‌نمایش طرح انتخابی') ?></span>
                        </div>

                        <div class="so-scene" id="standScene" title="<?= site_e('برای چرخاندن استند، ماوس یا انگشت خود را بکشید') ?>">
                            <div class="so-stand-3d" id="stand3D">
                                <div class="so-stand-face front">
                                    <div class="so-stand-img-wrap">
                                        <img src="" alt="<?= site_e('طرح استند') ?>" id="standImageFront" onerror="this.src='/uploads/stand/happy/1.jpg'">
                                    </div>
                                </div>
                                <div class="so-stand-face back">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    <h4 style="font-size:13px; font-weight:800; color:#1f2937; margin:0 0 4px;"><?= site_e('مؤسسه خیریه مکسا') ?></h4>
                                    <p style="font-size:11px; color:#6b7280; line-height:1.5; margin:0;"><?= site_e('مراقبت‌های جامع حمایتی و تسکینی بیماران مبتلا به سرطان') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="so-360-hint">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span><?= site_e('چرخش ۳۶۰ درجه استند (کلیک و کشیدن)') ?></span>
                        </div>

                        <div class="so-selected-meta">
                            <div class="so-meta-title" id="selectedStandTitle"><?= site_e('عنوان استند') ?></div>
                            <div class="so-meta-price" id="selectedStandPrice"><?= site_e('۰') ?> <span><?= site_e('تومان') ?></span></div>
                        </div>
                    </div>
                </div>

                <!-- اکشن‌های مرحله ۲ -->
                <div class="so-actions-row">
                    <button type="button" class="so-btn-prev" onclick="goToStep(1)">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        <span><?= site_e('بازگشت به انتخاب استان و شهر') ?></span>
                    </button>
                    <button type="button" class="so-btn-next" onclick="validateStep2AndProceed()">
                        <span><?= site_e('مرحله بعد: ثبت اطلاعات و تایید نهایی') ?></span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ================== مرحله ۳: ثبت اطلاعات و تایید نهایی ================== -->
        <div class="so-step-pane" id="paneStep3">
            <div class="so-step3-card">
                <div class="so-step-header">
                    <div class="so-step-badge">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="so-step-header-text">
                        <h2><?= site_e('مرحله سوم: ثبت اطلاعات و تایید نهایی سفارش') ?></h2>
                        <p><?= site_e('لطفاً مشخصات سفارش‌دهنده، زمان و نشانی دقیق محل برگزاری مراسم را وارد فرمایید.') ?></p>
                    </div>
                </div>

                <!-- باکس خلاصه استند و شهر انتخابی -->
                <div class="so-order-summary-box">
                    <div class="so-os-right">
                        <div class="so-os-thumb">
                            <img src="" id="step3Thumb" alt="<?= site_e('طرح استند') ?>">
                        </div>
                        <div class="so-os-title-wrap">
                            <span class="so-os-title" id="step3StandTitle">...</span>
                            <span class="so-os-loc" id="step3Loc">...</span>
                        </div>
                    </div>
                    <div class="so-os-left">
                        <div class="so-os-price">
                            <strong id="step3Price">۰</strong>
                            <span><?= site_e('تومان') ?></span>
                        </div>
                        <button type="button" class="so-os-change-btn" onclick="goToStep(2)">
                            <?= site_e('تغییر طرح استند') ?>
                        </button>
                    </div>
                </div>

                <!-- بخش ۱: مشخصات سفارش‌دهنده -->
                <div class="so-section-title"><?= site_e('۱. مشخصات فرد سفارش‌دهنده') ?></div>
                <div class="so-field-row">
                    <div class="so-field">
                        <label><?= site_e('نام و نام خانوادگی شما') ?> <span class="req">*</span></label>
                        <input type="text" name="sender_name" required placeholder="<?= site_e('مثال: علیرضا محمدی') ?>">
                    </div>
                    <div class="so-field">
                        <label><?= site_e('شماره موبایل جهت هماهنگی تحویل') ?> <span class="req">*</span></label>
                        <input type="tel" name="sender_phone" required placeholder="09123456789" dir="ltr" pattern="09[0-9]{9}">
                    </div>
                </div>

                <!-- بخش ۲: مشخصات مراسم و دریافت‌کننده -->
                <div class="so-section-title"><?= site_e('۲. مشخصات مراسم و دریافت‌کننده') ?></div>
                <div class="so-field">
                    <label><?= site_e('نام دریافت‌کننده / صاحب مجلس / مرحوم یا مرحومه') ?> <span class="req">*</span></label>
                    <input type="text" name="receiver_name" required placeholder="<?= site_e('نام شخص یا خانواده‌ای که استند برای ایشان ارسال می‌شود') ?>">
                </div>

                <div class="so-field-row">
                    <div class="so-field">
                        <label><?= site_e('تاریخ برگزاری مراسم') ?> <span class="req">*</span></label>
                        <div class="so-input-icon-wrap">
                            <input type="text" name="event_date" id="eventDateInput" class="pdate" required placeholder="<?= site_e('انتخاب تاریخ شمسی') ?>" readonly style="background:#fff; cursor:pointer">
                            <svg class="so-field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="so-field">
                        <label><?= site_e('ساعت برگزاری مراسم (دقیقه : ساعت)') ?> <span class="req">*</span></label>
                        <div class="so-input-icon-wrap">
                            <input type="text" name="event_time" id="eventTimeInput" class="ptime" required placeholder="<?= site_e('انتخاب ساعت مراسم') ?>" readonly style="background:#fff; cursor:pointer">
                            <svg class="so-field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="so-field">
                    <label><?= site_e('نشانی دقیق محل برگزاری (مسجد، تالار یا منزل)') ?> <span class="req">*</span></label>
                    <textarea name="event_address" required placeholder="<?= site_e('خیابان، کوچه، پلاک، نام مسجد، حسینیه یا تالار پذیرایی...') ?>"></textarea>
                </div>

                <!-- بخش ۳: متن پیام اختصاصی -->
                <div class="so-section-title"><?= site_e('۳. پیام اختصاصی روی استند (اختیاری)') ?></div>
                <div class="so-field">
                    <label><?= site_e('متن دلخواه جهت درج روی پلاک یا بنر استند') ?></label>
                    <textarea name="message" placeholder="<?= site_e('در صورت تمایل، متن دلخواه و نام اهداکنندگان را وارد نمایید...') ?>"></textarea>
                </div>

                <!-- باکس اثرگذاری و وقف صددرصدی -->
                <div class="so-charity-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div>
                        <strong><?= site_e('شفافیت و ارزش معنوی:') ?></strong> <?= site_e('تمامی عواید حاصل از سفارش این استند به صورت ۱۰۰٪ صرف تأمین هزینه‌های دارو، درمان و مراقبت‌های تسکینی رایگان به بیماران مبتلا به سرطان می‌شود.') ?>
                    </div>
                </div>

                <!-- اکشن‌های مرحله ۳ -->
                <div class="so-actions-row">
                    <button type="button" class="so-btn-prev" onclick="goToStep(2)">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        <span><?= site_e('بازگشت به انتخاب طرح') ?></span>
                    </button>
                    <button type="submit" class="so-submit" id="submitOrderBtn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span><?= site_e('ثبت سفارش و پرداخت') ?></span>
                    </button>
                </div>

                <div class="so-note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><?= site_e('سفارش پس از تأیید پرداخت نهایی می‌شود؛ سپس کارشناسان مکسا برای هماهنگی ارسال با شما تماس می‌گیرند.') ?></span>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ================== مدال ورود نیکوکاران ================== -->
<div class="so-modal-overlay" id="soAuthModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="soAuthTitle">
    <div class="so-modal-card">
        <button type="button" class="so-modal-close" id="soAuthClose" aria-label="<?= site_e('بستن') ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="so-modal-icon-wrap">
            <div class="so-modal-icon auth">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        </div>
        <h3 class="so-modal-title" id="soAuthTitle"><?= site_e('ورود به حساب کاربری نیکوکاران') ?></h3>
        <p class="so-modal-desc">
            <?= site_e('جهت ثبت نهایی سفارش استند و صدور کد پیگیری رسمی، لطفاً وارد حساب خود شوید یا ثبت‌نام فرمایید. اطلاعات فرم شما کاملاً محفوظ مانده و حفظ خواهد شد.') ?>
        </p>
        <div class="so-modal-actions">
            <a href="/benefactor-dashboard/login" class="so-btn-primary" id="soLoginBtn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                <?= site_e('ورود به حساب کاربری') ?>
            </a>
            <a href="/benefactor-dashboard/register" class="so-btn-ghost" id="soRegisterBtn">
                <?= site_e('ثبت‌نام نیکوکار جدید') ?>
            </a>
        </div>
    </div>
</div>

<!-- ================== مدال ثبت موفق سفارش ================== -->
<div class="so-modal-overlay" id="soSuccessModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="soSuccessTitle">
    <div class="so-modal-card">
        <button type="button" class="so-modal-close" id="soSuccessClose" aria-label="<?= site_e('بستن') ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="so-modal-icon-wrap">
            <div class="so-modal-icon success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
        <h3 class="so-modal-title" id="soSuccessTitle"><?= site_e('سفارش استند با موفقیت ثبت شد') ?></h3>
        <p class="so-modal-desc">
            <?= site_e('از همراهی و همدلی صمیمانه شما با بیماران مبتلا به سرطان سپاسگزاریم. سفارش شما با مشخصات زیر جهت هماهنگی و ارسال به شعبه مربوطه تحویل گردید.') ?>
        </p>
        <div class="so-tracking-card">
            <span class="so-tr-lbl"><?= site_e('کد پیگیری رسمی سفارش:') ?></span>
            <div class="so-tr-val-row">
                <strong id="soTrackingCodeVal">---</strong>
                <button type="button" class="so-btn-copy" id="soCopyTrackingBtn" title="<?= site_e('کپی کد پیگیری') ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    <span><?= site_e('کپی') ?></span>
                </button>
            </div>
        </div>
        <div class="so-modal-actions">
            <a href="/benefactor-dashboard/stands" class="so-btn-primary">
                <?= site_e('پیگیری سفارش در پنل کاربری') ?>
            </a>
            <button type="button" class="so-btn-ghost" id="soNewOrderBtn">
                <?= site_e('ثبت یک سفارش دیگر') ?>
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
<script src="/js/stand-checkout.js"></script>

<script>
// مدل وضعیت صفحه
const state = {
    currentStep: 1,
    step1Completed: false,
    step2Completed: false,
    step3Completed: false,
    provinces: <?= json_encode($initialProvincesList, JSON_UNESCAPED_UNICODE) ?>,
    selectedProvince: '',
    selectedCity: '',
    selectedBranchId: null,
    selectedBranchName: '',
    stands: [],
    selectedStand: null,
    filterType: 'congrats'
};

// عناصر DOM
const inputStandId = document.getElementById('inputStandId');
const inputBranchId = document.getElementById('inputBranchId');
const inputProvince = document.getElementById('inputProvince');
const inputCity = document.getElementById('inputCity');
const provinceSelect = document.getElementById('provinceSelect');
const citySelect = document.getElementById('citySelect');
const cityChips = document.getElementById('cityChips');
const standsCatalog = document.getElementById('standsCatalog');
const standImageFront = document.getElementById('standImageFront');
const selectedStandTitle = document.getElementById('selectedStandTitle');
const selectedStandPrice = document.getElementById('selectedStandPrice');
const standScene = document.getElementById('standScene');
const stand3D = document.getElementById('stand3D');

// تبدیل اعداد به فارسی
function toFa(n) {
    return (n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, "٬")
        .replace(/[0-9]/g, d => "۰۱۲۳۴۵۶۷۸۹"[d]);
}

// نمایش نوتیفیکیشن
function showToast(msg, type = 'info') {
    const t = document.getElementById('soToast');
    if (!t) return;
    t.className = 'so-toast ' + type;
    t.textContent = msg;
    t.style.display = 'block';
    setTimeout(() => { t.style.display = 'none'; }, 4200);
}

// تغییر مرحله در ویزارد
function updateStepperUI() {
    // مرحله ۱
    const s1 = document.getElementById('stepIndicator1');
    const l1 = document.getElementById('stepLine1');
    const s2 = document.getElementById('stepIndicator2');
    const l2 = document.getElementById('stepLine2');
    const s3 = document.getElementById('stepIndicator3');

    // ریست کلاس‌ها
    [s1, s2, s3].forEach(el => el.classList.remove('active', 'completed'));
    [l1, l2].forEach(el => el.classList.remove('filled'));

    // مرحله ۱ تکمیل شده؟ (سبز)
    if (state.step1Completed) {
        s1.classList.add('completed');
        l1.classList.add('filled');
    }
    // مرحله ۲ تکمیل شده؟ (سبز)
    if (state.step2Completed) {
        s2.classList.add('completed');
        l2.classList.add('filled');
    }
    // مرحله ۳ تکمیل شده؟ (سبز)
    if (state.step3Completed) {
        s3.classList.add('completed');
    }

    // هایلایت مرحله جاری
    if (state.currentStep === 1) {
        s1.classList.add('active');
    } else if (state.currentStep === 2) {
        s2.classList.add('active');
    } else if (state.currentStep === 3) {
        s3.classList.add('active');
    }

    // جابجایی پنل‌های محتوا
    document.getElementById('paneStep1').classList.toggle('active', state.currentStep === 1);
    document.getElementById('paneStep2').classList.toggle('active', state.currentStep === 2);
    document.getElementById('paneStep3').classList.toggle('active', state.currentStep === 3);

    // اسکرول نرم به بالای بخش
    document.getElementById('soStepper')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function goToStep(step) {
    if (step === 2 && !state.step1Completed && !state.selectedProvince) {
        showToast(<?= site_js_text('لطفاً ابتدا استان و شهر مورد نظر را انتخاب فرمایید.') ?>, 'warning');
        return;
    }
    if (step === 3 && !state.selectedStand) {
        showToast(<?= site_js_text('لطفاً یک طرح استند را انتخاب فرمایید.') ?>, 'warning');
        return;
    }
    state.currentStep = step;
    updateStepperUI();
}

// انتخاب کارت استان
function selectProvinceCard(cardEl) {
    const prov = cardEl.dataset.province;
    const branchId = parseInt(cardEl.dataset.branch || '1', 10);

    document.querySelectorAll('.so-prov-card').forEach(c => c.classList.remove('selected'));
    cardEl.classList.add('selected');

    state.selectedProvince = prov;
    state.selectedBranchId = branchId;
    inputProvince.value = prov;
    inputBranchId.value = branchId;

    if (provinceSelect) {
        provinceSelect.value = prov;
    }

    // بارگذاری شهرهای این استان
    const provData = state.provinces.find(p => p.province === prov || parseInt(p.branch_id, 10) === branchId);
    state.selectedBranchName = provData ? (provData.branch_name || prov) : prov;

    cityChips.innerHTML = '';
    citySelect.innerHTML = '';

    const cities = (provData && Array.isArray(provData.cities) && provData.cities.length > 0)
        ? provData.cities
        : [prov];

    cities.forEach((c, idx) => {
        // ایجاد چیپ
        const chip = document.createElement('div');
        chip.className = 'so-city-chip' + (idx === 0 ? ' selected' : '');
        chip.textContent = c;
        chip.onclick = function() {
            document.querySelectorAll('.so-city-chip').forEach(x => x.classList.remove('selected'));
            chip.classList.add('selected');
            state.selectedCity = c;
            inputCity.value = c;
            citySelect.value = c;
        };
        cityChips.appendChild(chip);

        // ایجاد گزینه در سلکت
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        if (idx === 0) opt.selected = true;
        citySelect.appendChild(opt);
    });

    state.selectedCity = cities[0] || prov;
    inputCity.value = state.selectedCity;

    // بارگذاری استندهای اختصاصی این شعبه
    loadStands(prov, branchId);
}

citySelect.addEventListener('change', function() {
    const val = this.value;
    state.selectedCity = val;
    inputCity.value = val;
    document.querySelectorAll('.so-city-chip').forEach(ch => {
        ch.classList.toggle('selected', ch.textContent.trim() === val);
    });
});

// اعتبارسنجی مرحله ۱ و رفتن به مرحله ۲
function validateStep1AndProceed() {
    if (!state.selectedProvince) {
        showToast(<?= site_js_text('لطفاً ابتدا استان و شعبه مورد نظر را انتخاب فرمایید.') ?>, 'warning');
        return;
    }
    if (!state.selectedCity) {
        state.selectedCity = state.selectedProvince;
        inputCity.value = state.selectedCity;
    }

    state.step1Completed = true; // تبدیل مرحله ۱ به سبز!
    state.currentStep = 2;

    // به‌روزرسانی برچسب شهر در مرحله ۲
    document.getElementById('step2LocLabel').textContent = state.selectedProvince + ' - ' + state.selectedCity + ' (' + <?= site_js_text('شعبه ') ?> + state.selectedBranchName + ')';

    updateStepperUI();
}

// بارگذاری استندهای اختصاصی شعبه
async function loadStands(province, branchId) {
    standsCatalog.innerHTML = <?= site_js_text('<div class="empty-stands-msg">در حال بارگذاری طرح‌های اختصاصی شعبه...</div>') ?>;
    try {
        const query = new URLSearchParams({ province });
        if (branchId) query.set('branch_id', String(branchId));
        const res = await fetch(`/api/stands?${query.toString()}`);
        const data = await res.json();
        const list = Array.isArray(data.data) ? data.data : (data.data?.stands || []);
        if (data.success && list.length > 0) {
            state.stands = list;
            renderStandsCatalog();
        } else {
            // در صورتی که دیتابیس لوکال آفلاین باشد، استندهای پیش‌فرض شیک نمایش داده می‌شوند
            state.stands = getFallbackStands();
            renderStandsCatalog();
        }
    } catch (e) {
        state.stands = getFallbackStands();
        renderStandsCatalog();
    }
}

function getFallbackStands() {
    return [
        { id: 101, title: 'طرح ترنم بهار (تبریک)', unit_price: 1500000, stand_type: 'congrats', image: '/uploads/stand/happy/1.jpg' },
        { id: 102, title: 'طرح شکوفه مهر (تبریک)', unit_price: 1800000, stand_type: 'congrats', image: '/uploads/stand/happy/2.jpg' },
        { id: 103, title: 'طرح یاس سپید (تسلیت)', unit_price: 1500000, stand_type: 'condolence', image: '/uploads/stand/sad/1.jpg' },
        { id: 104, title: 'طرح غروب آرامش (تسلیت)', unit_price: 2000000, stand_type: 'condolence', image: '/uploads/stand/sad/2.jpg' },
        { id: 105, title: 'طرح آوای جاودان (تسلیت)', unit_price: 2200000, stand_type: 'condolence', image: '/uploads/stand/sad/3.jpg' }
    ];
}

// رندر کاتالوگ استندها
function renderStandsCatalog() {
    const filtered = state.filterType === 'all'
        ? state.stands
        : state.stands.filter(s => s.stand_type === state.filterType);

    if (filtered.length === 0) {
        standsCatalog.innerHTML = <?= site_js_text('<div class="empty-stands-msg">طرحی در این دسته‌بندی برای این شعبه یافت نشد.</div>') ?>;
        return;
    }

    standsCatalog.innerHTML = filtered.map(s => {
        const isSelected = state.selectedStand && state.selectedStand.id === s.id;
        const tagLabel = s.stand_type === 'congrats' ? <?= site_js_text('تبریک') ?> : <?= site_js_text('تسلیت') ?>;
        const btnText = isSelected ? <?= site_js_text('طرح انتخابی ✓') ?> : <?= site_js_text('انتخاب این طرح') ?>;

        return `
            <div class="so-stand-card ${isSelected ? 'selected' : ''}" data-id="${s.id}" onclick="selectStand(${s.id})">
                <div class="so-stand-card-img-wrap">
                    <img src="${s.image}" alt="${s.title}" onerror="this.src='/uploads/stand/happy/1.jpg'">
                </div>
                <div class="so-stand-card-info">
                    <h4 class="so-stand-card-title">${s.title}</h4>
                    <div class="so-stand-card-meta">
                        <span class="so-stand-tag ${s.stand_type}">${tagLabel}</span>
                        <div class="so-stand-card-price">${toFa(s.unit_price)} <span>تومان</span></div>
                    </div>
                </div>
                <button type="button" class="so-stand-card-btn">${btnText}</button>
            </div>
        `;
    }).join('');

    // انتخاب پیش‌فرض اگر چیزی انتخاب نشده است
    if (!state.selectedStand || !filtered.some(s => s.id === state.selectedStand.id)) {
        if (filtered[0]) selectStand(filtered[0].id);
    } else {
        selectStand(state.selectedStand.id);
    }
}

// انتخاب استند و به‌روزرسانی پیش‌نمایش ۳ بعدی
function selectStand(standId) {
    const stand = state.stands.find(s => s.id === standId);
    if (!stand) return;

    state.selectedStand = stand;
    inputStandId.value = stand.id;

    // به‌روزرسانی هایلایت کارت‌ها
    document.querySelectorAll('.so-stand-card').forEach(card => {
        const isCurrent = parseInt(card.dataset.id, 10) === stand.id;
        card.classList.toggle('selected', isCurrent);
        const btn = card.querySelector('.so-stand-card-btn');
        if (btn) btn.textContent = isCurrent ? <?= site_js_text('طرح انتخابی ✓') ?> : <?= site_js_text('انتخاب این طرح') ?>;
    });

    // پیش‌نمایش ۳ بعدی
    standImageFront.src = stand.image;
    selectedStandTitle.textContent = stand.title;
    selectedStandPrice.innerHTML = `${toFa(stand.unit_price)} <span>تومان</span>`;

    // مقادیر مرحله ۳
    document.getElementById('step3Thumb').src = stand.image;
    document.getElementById('step3StandTitle').textContent = stand.title;
    document.getElementById('step3Price').textContent = toFa(stand.unit_price);
}

// تب‌های دسته‌بندی استند در مرحله ۲
document.querySelectorAll('.so-tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.so-tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        state.filterType = this.dataset.type;
        renderStandsCatalog();
    });
});

// اعتبارسنجی مرحله ۲ و رفتن به مرحله ۳
function validateStep2AndProceed() {
    if (!state.selectedStand) {
        showToast(<?= site_js_text('لطفاً یک طرح استند را انتخاب فرمایید.') ?>, 'warning');
        return;
    }

    state.step2Completed = true; // تبدیل مرحله ۲ به سبز!
    state.currentStep = 3;

    // تنظیم لوکیشن در خلاصه مرحله ۳
    document.getElementById('step3Loc').textContent = state.selectedProvince + ' - ' + state.selectedCity;

    updateStepperUI();
}

// راه‌اندازی تقویم و ساعت شمسی
function initDatePickers() {
    if (!window.jQuery || !jQuery.fn.persianDatepicker) {
        setTimeout(initDatePickers, 80);
        return;
    }

    $('.pdate').persianDatepicker({
        format: 'YYYY/MM/DD',
        autoClose: true,
        initialValue: false,
        minDate: new persianDate().valueOf(),
        navigator: { scroll: { enabled: false } },
        toolbox: { calendarSwitch: { enabled: false }, todayButton: { enabled: true } }
    });

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
            submitButton: { enabled: true, text: { fa: 'تأیید ساعت', en: 'Confirm' } }
        }
    });
}
initDatePickers();

// درگ و چرخش ۳۶۰ درجه استند
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

// احراز هویت خیرین
async function fetchAccessToken() {
    if (window.__maxa_access_token) return window.__maxa_access_token;
    try {
        const s = sessionStorage.getItem('maxa_access_token');
        if (s) { window.__maxa_access_token = s; return s; }
        const l = localStorage.getItem('maxa_access_token');
        if (l) { window.__maxa_access_token = l; return l; }
        const res = await fetch('/api/auth/refresh', { method: 'POST', credentials: 'include' });
        if (res.ok) {
            const data = await res.json();
            const tok = data.data?.access_token || data.access_token;
            if (tok) {
                window.__maxa_access_token = tok;
                try { sessionStorage.setItem('maxa_access_token', tok); } catch(e) {}
                return tok;
            }
        }
    } catch(e) {}
    return null;
}

function openAuthModal() {
    const modal = document.getElementById('soAuthModal');
    if (!modal) return;
    const currentUrl = window.location.pathname + window.location.search;
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

    // تبدیل مرحله ۳ به سبز نهایی
    state.step3Completed = true;
    updateStepperUI();
}

function closeSuccessModal() {
    const modal = document.getElementById('soSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

document.getElementById('soAuthClose')?.addEventListener('click', closeAuthModal);
document.getElementById('soSuccessClose')?.addEventListener('click', closeSuccessModal);
document.getElementById('soNewOrderBtn')?.addEventListener('click', () => {
    closeSuccessModal();
    window.location.reload();
});

document.querySelectorAll('.so-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
});

document.getElementById('soCopyTrackingBtn')?.addEventListener('click', function() {
    const code = document.getElementById('soTrackingCodeVal')?.textContent;
    if (code && code !== '---') {
        navigator.clipboard.writeText(code).then(() => {
            showToast(<?= site_js_text('کد پیگیری با موفقیت کپی شد') ?>, 'success');
        }).catch(() => {
            showToast('کد پیگیری: ' + code, 'info');
        });
    }
});

// ثبت نهایی فرم در مرحله ۳
document.getElementById('standOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!state.selectedProvince) {
        showToast(<?= site_js_text('لطفاً استان و شهر را انتخاب فرمایید.') ?>, 'warning');
        goToStep(1);
        return;
    }

    if (!state.selectedStand) {
        showToast(<?= site_js_text('لطفاً طرح استند را انتخاب فرمایید.') ?>, 'warning');
        goToStep(2);
        return;
    }

    const eventDate = (document.getElementById('eventDateInput')?.value || '').trim();
    if (!eventDate) {
        showToast(<?= site_js_text('لطفاً تاریخ برگزاری مراسم را مشخص فرمایید.') ?>, 'warning');
        document.getElementById('eventDateInput')?.focus();
        return;
    }

    const eventTime = (document.getElementById('eventTimeInput')?.value || '').trim();
    if (!eventTime) {
        showToast(<?= site_js_text('لطفاً ساعت برگزاری مراسم را مشخص فرمایید.') ?>, 'warning');
        document.getElementById('eventTimeInput')?.focus();
        return;
    }

    const formData = new FormData(this);
    const payload = Object.fromEntries(formData.entries());
    payload.quantity = 1;
    payload.branch_id = state.selectedBranchId;
    payload.stand_id = state.selectedStand.id;
    payload.province = state.selectedProvince;
    payload.city = state.selectedCity;

    // بررسی ورود کاربر
    const token = await fetchAccessToken();
    if (!token) {
        try {
            localStorage.setItem('pendingStandOrder', JSON.stringify(payload));
        } catch(e) {}
        openAuthModal();
        return;
    }

    // ارسال سفارش
    const btn = document.getElementById('submitOrderBtn');
    const originalContent = btn.innerHTML;
    btn.innerHTML = `
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:20px;height:20px;animation:soSpin 1s linear infinite"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/></svg>
        ${<?= site_js_text('در حال ثبت سفارش...') ?>}
    `;
    btn.disabled = true;

    try {
        const payment = await window.MaksaStandCheckout.start(payload, token);
        try { localStorage.removeItem('pendingStandOrder'); } catch(e) {}
        if (payment.status === 'success') {
            openSuccessModal(payment.tracking_code);
        }
    } catch (err) {
        if (err.status === 401) {
            window.__maxa_access_token = null;
            try {
                sessionStorage.removeItem('maxa_access_token');
                localStorage.removeItem('maxa_access_token');
                localStorage.setItem('pendingStandOrder', JSON.stringify(payload));
            } catch(e) {}
            openAuthModal();
            return;
        }

        showToast(err.message || <?= site_js_text('خطای اتصال به سرور. لطفاً اتصال اینترنت خود را بررسی نمایید.') ?>, 'error');
    } finally {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }
});

// پر کردن خودکار اطلاعات نیکوکار
function applyStandOrderAutofill() {
    function fill(u) {
        if (!u) return;
        const nameInput = document.querySelector('#standOrderForm [name="sender_name"]');
        const phoneInput = document.querySelector('#standOrderForm [name="sender_phone"]');
        const fullName = u.full_name || [u.first_name, u.last_name].filter(Boolean).join(' ').trim();
        if (nameInput && fullName && !nameInput.value) nameInput.value = fullName;
        if (phoneInput && u.phone && !phoneInput.value) phoneInput.value = u.phone;
    }

    try {
        const raw = localStorage.getItem('maksa_benefactor_user');
        if (raw) {
            fill(JSON.parse(raw));
        } else {
            fetchAccessToken().then(token => {
                if (!token) return;
                fetch('/api/user/me', {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                    credentials: 'include'
                })
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    const u = data?.data?.user;
                    if (u) {
                        const profile = {
                            first_name: u.first_name || '',
                            last_name: u.last_name || '',
                            full_name: [u.first_name, u.last_name].filter(Boolean).join(' ').trim(),
                            phone: u.phone || '',
                            national_code: u.national_code || '',
                            email: u.email || ''
                        };
                        localStorage.setItem('maksa_benefactor_user', JSON.stringify(profile));
                        fill(profile);
                    }
                })
                .catch(() => {});
            });
        }
    } catch (e) {}
}

// بررسی پارامترهای آدرس URL یا سفارش ذخیره‌شده قبلی
function initFromUrlOrStorage() {
    const urlParams = new URLSearchParams(window.location.search);
    const targetProv = urlParams.get('province');
    const targetType = urlParams.get('type');
    const targetStandId = parseInt(urlParams.get('stand_id'), 10);

    if (targetType) {
        state.filterType = targetType;
        document.querySelectorAll('.so-tab-btn').forEach(b => {
            b.classList.toggle('active', b.dataset.type === targetType);
        });
    }

    // انتخاب کارت استان
    const cards = document.querySelectorAll('.so-prov-card');
    let matchedCard = null;

    if (targetProv) {
        matchedCard = Array.from(cards).find(c => c.dataset.province === targetProv);
    }
    if (!matchedCard && cards.length > 0) {
        matchedCard = cards[0]; // اولین کارت پیش‌فرض انتخاب می‌شود
    }

    if (matchedCard) {
        selectProvinceCard(matchedCard);
    }

    // اگر از صفحه اصلی آمده باشند و استان مشخص باشد، به مرحله دوم هدایت می‌شوند
    if (targetProv && targetStandId) {
        setTimeout(() => {
            state.step1Completed = true;
            if (targetStandId) selectStand(targetStandId);
            goToStep(2);
        }, 300);
    }

    // بررسی pending order در localStorage
    const saved = localStorage.getItem('pendingStandOrder');
    if (saved) {
        try {
            const data = JSON.parse(saved);
            const form = document.getElementById('standOrderForm');
            Object.keys(data).forEach(k => {
                if (form.elements[k]) form.elements[k].value = data[k];
            });
            if (data.province) {
                const c = Array.from(cards).find(x => x.dataset.province === data.province);
                if (c) selectProvinceCard(c);
            }
            if (data.stand_id) {
                setTimeout(() => {
                    selectStand(parseInt(data.stand_id));
                    state.step1Completed = true;
                    state.step2Completed = true;
                    goToStep(3);
                }, 400);
            }
        } catch(e) {}
    }
}

// راه‌اندازی اولیه
window.addEventListener('DOMContentLoaded', () => {
    initFromUrlOrStorage();
    applyStandOrderAutofill();
});
</script>

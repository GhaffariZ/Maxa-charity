<?php
require_once __DIR__ . '/_guard.php';
dash_require('partners');
// --- Database Configuration (from config file outside git) ---
$DB = require __DIR__ . '/../core/db-config.php';
$servername = $DB['host'];
$username = $DB['user'];
$password = $DB['pass'];
$dbname = $DB['name'];

// ایجاد اتصال به دیتابیس
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("خطا در اتصال به دیتابیس: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// دریافت نام از متد GET برای URL اختصاصی و سئو محور (تبدیل خط تیره به فاصله)
$name_slug = isset($_GET['name']) ? trim($_GET['name']) : '';

if (empty($name_slug)) {
    die("<div style='text-align:center; padding:50px; font-family:tahoma; direction:rtl;'>نام همکار مشخص نشده است. <a href='../personal-resume-list.php'>بازگشت به لیست</a></div>");
}

$fullname_search = str_replace('-', ' ', $name_slug);

// واکشی اطلاعات بر اساس نام کامل (مطابق با ساختار URL)
$stmt = $conn->prepare("SELECT * FROM employee_profiles WHERE fullname = ?");
$stmt->bind_param("s", $fullname_search);
$stmt->execute();
$result = $stmt->get_result();

// جستجوی جایگزین (اگر فرد با فاصله اضافه ثبت شده بود)
if ($result->num_rows === 0) {
    $search_like = "%" . $fullname_search . "%";
    $stmt2 = $conn->prepare("SELECT * FROM employee_profiles WHERE fullname LIKE ? LIMIT 1");
    $stmt2->bind_param("s", $search_like);
    $stmt2->execute();
    $result = $stmt2->get_result();
}

if ($result->num_rows === 0) {
    die("<div style='text-align:center; padding:50px; font-family:tahoma; direction:rtl;'>همکاری با نام '" . htmlspecialchars($fullname_search) . "' یافت نشد. <a href='../personal-resume-list.php'>بازگشت به لیست</a></div>");
}

$row = $result->fetch_assoc();

// بررسی وضعیت فعال بودن همکار — اگر غیرفعال باشد صفحه نمایش داده نمی‌شود
if (isset($row['is_active']) && (int)$row['is_active'] === 0) {
    $conn->close();
    die("<div style='text-align:center; padding:60px; font-family:tahoma; direction:rtl; color:#64748b;'>پروفایل این همکار در حال حاضر در دسترس نیست. <a href='../personal-resume-list.php' style='color:#0d9488;'>بازگشت به لیست</a></div>");
}

// آرایه‌های نگاشت ترجمه فارسی مکسا
$branches = [
    'tehran' => 'شعبه تهران',
    'isfahan' => 'شعبه اصفهان',
    'mashhad' => 'شعبه مشهد',
    'qom' => 'شعبه قم',
    'tabriz' => 'شعبه تبریز',
    'ahvaz' => 'شعبه اهواز',
    'kerman' => 'شعبه کرمان',
    'kashan' => 'شعبه کاشان',
    'telemedicine' => 'پزشکی از راه دور مکسا',
    'setad_markazi' => 'ستاد مرکزی'
];

$roles = [
    'administrative' => 'سمت‌های اداری', 
    'doctors' => 'پزشک متخصص',
    'nurses' => 'کادر پرستاری',
    'psychologists' => 'روانشناس سلامت',
    'social_workers' => 'مددکار اجتماعی',
    'spiritual_care' => 'مراقب معنوی',
    'nutritionists' => 'متخصص تغذیه',
    'rehabilitation' => 'متخصص توانبخشی',
    'genetic_counselors' => 'مشاور ژنتیک',
    'deputies' => 'معاونت'
];

// --- تطبیق ۱۰۰٪ با ستون‌های دیتابیس ---
$fullname = htmlspecialchars($row['fullname'] ?: 'همکار مکسا');
$branch_text = isset($branches[$row['branch']]) ? $branches[$row['branch']] : 'نامشخص';
$role_text = isset($roles[$row['role']]) ? $roles[$row['role']] : ($row['role'] ?: 'ثبت نشده');

$profile_pic = isset($row['profile_pic']) && !empty($row['profile_pic']) ? $row['profile_pic'] : '';
$email = !empty($row['email']) ? htmlspecialchars($row['email']) : 'ثبت نشده';

// خالی گذاشتن متغیر نظام پزشکی در صورت عدم وجود (برای مخفی شدن باکس)
$medical_id = !empty($row['medical_id']) ? htmlspecialchars($row['medical_id']) : '';

$study_field = !empty($row['study_field']) ? htmlspecialchars($row['study_field']) : 'ثبت نشده';
$start_work_year = !empty($row['start_work_year']) ? htmlspecialchars($row['start_work_year']) : 'نامشخص';
$maxa_join_year = !empty($row['maxa_join_year']) ? htmlspecialchars($row['maxa_join_year']) : 'نامشخص';

$bio_professional = !empty($row['bio_professional']) ? htmlspecialchars($row['bio_professional']) : 'توضیحاتی ثبت نشده است.';
$academic_background = !empty($row['academic_background']) ? htmlspecialchars($row['academic_background']) : 'سوابقی ثبت نشده است.';
$maxa_responsibilities = !empty($row['maxa_responsibilities']) ? htmlspecialchars($row['maxa_responsibilities']) : 'موردی ثبت نشده است.';

$instagram = !empty($row['instagram']) ? htmlspecialchars($row['instagram']) : '';
$linkedin = !empty($row['linkedin']) ? htmlspecialchars($row['linkedin']) : '';

$conn->close();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>پروفایل تخصصی | <?php echo $fullname; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">

    <style>
    :root {
        --bg-global: #f4f7f6;
        --bg-surface: #ffffff;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        
        --brand-teal: #0d9488;
        --brand-teal-glow: rgba(13, 148, 136, 0.15);
        --brand-gold: #d97706;
        --brand-gold-glow: rgba(217, 119, 6, 0.1);
        --social-bg: #f8fafc;
        
        --panel-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05);
        --ease-premium: cubic-bezier(0.25, 1, 0.5, 1);
    }

    [data-theme="dark"] {
        --bg-global: #0f172a;
        --bg-surface: #1e293b;
        --text-main: #f1f5f9;
        --text-muted: #94a3b8;
        --border-color: #334155;
        --social-bg: #0f172a;
        --panel-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
    }

    * { box-sizing: border-box; transition: background-color 0.4s var(--ease-premium), border-color 0.4s, color 0.3s; }
    body { font-family: 'Vazirmatn', sans-serif; background-color: var(--bg-global); color: var(--text-main); margin: 0; padding: 0; min-height: 100vh; overflow-x: hidden; }

    /* هدر */
    .premium-header {
        position: sticky; top: 0; z-index: 100; background: var(--bg-surface);
        backdrop-filter: blur(12px); border-bottom: 1px solid var(--border-color);
        padding: 14px 32px; display: flex; justify-content: space-between; align-items: center;
    }
    .brand-section { display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; }
    .brand-dot { width: 12px; height: 12px; background: linear-gradient(135deg, var(--brand-teal), #2dd4bf); border-radius: 50%; }
    .header-title { margin: 0; font-size: 1.2rem; font-weight: 800; }

    .theme-switch-wrapper {
        background: var(--bg-global); border: 1px solid var(--border-color); padding: 4px;
        border-radius: 30px; cursor: pointer; display: flex; align-items: center; gap: 4px;
    }
    .theme-btn-icon { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
    .theme-switch-wrapper[data-active="light"] .light-icon, .theme-switch-wrapper[data-active="dark"] .dark-icon {
        color: var(--brand-teal); background: var(--bg-surface); box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 50%;
    }
    .theme-btn-icon svg { width: 18px; height: 18px; fill: currentColor; }

    /* بدنه اصلی */
    .main-container { max-width: 1100px; margin: 40px auto; padding: 0 24px; animation: pageEntrance 0.8s var(--ease-premium) both; }
    @keyframes pageEntrance { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }

    .btn-back {
        display: inline-flex; align-items: center; gap: 8px; background: var(--bg-surface);
        border: 1px solid var(--border-color); padding: 10px 20px; border-radius: 14px;
        font-family: 'Vazirmatn', sans-serif; font-size: 14px; font-weight: 700; color: var(--text-main);
        text-decoration: none; cursor: pointer; box-shadow: var(--panel-shadow); margin-bottom: 24px;
        transition: border-color 0.35s var(--ease-premium), color 0.35s var(--ease-premium), transform 0.4s var(--ease-premium), box-shadow 0.35s var(--ease-premium);
    }
    .btn-back:hover { border-color: var(--brand-teal); color: var(--brand-teal); transform: translateX(-5px); box-shadow: 0 8px 24px var(--brand-teal-glow); }
    .btn-back svg { width: 16px; height: 16px; fill: currentColor; transform: rotate(180deg); }

    /* رفع مشکل کشیدگی سایدبار با اضافه کردن align-items: start */
    .profile-layout-grid { display: grid; grid-template-columns: 1fr; gap: 20px; align-items: start; }
    @media (min-width: 992px) {
        .profile-layout-grid { grid-template-columns: 340px 1fr; gap: 32px; }
    }
    .premium-panel { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 28px; padding: 30px; box-shadow: var(--panel-shadow); overflow: hidden; min-width: 0; }

    /* سایدبار */
    .profile-sidebar-card { text-align: center; display: flex; flex-direction: column; align-items: center; min-width: 0; }
    .profile-avatar-frame { width: 180px; height: 180px; border-radius: 40px; overflow: hidden; background: var(--bg-global); border: 4px solid var(--bg-surface); box-shadow: 0 15px 35px rgba(13, 148, 136, 0.2); margin-bottom: 20px; }
    .profile-avatar-frame img { width: 100%; height: 100%; object-fit: cover; }
    .profile-avatar-frame .placeholder-icon { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 70px; color: var(--text-muted); }

    .profile-main-name { font-size: 1.4rem; font-weight: 900; margin: 0 0 16px 0; }
    .badges-column { display: flex; flex-direction: column; gap: 10px; width: 100%; margin-bottom: 24px; }
    .pill-badge { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 10px 16px; border-radius: 14px; font-size: 13.5px; font-weight: 700; }
    .pill-branch { background: var(--bg-global); border: 1px solid var(--border-color); }
    .pill-branch .dot { background: var(--brand-teal); width: 6px; height: 6px; border-radius: 50%; }
    .pill-role { background: var(--brand-gold-glow); color: var(--brand-gold); }
    .pill-role .dot { background: var(--brand-gold); width: 6px; height: 6px; border-radius: 50%; }

    .divider-line { width: 100%; height: 1px; background: var(--border-color); margin: 20px 0; }

    /* لیست اطلاعات در سایدبار */
    .contact-info-list { width: 100%; display: flex; flex-direction: column; gap: 16px; text-align: right; }
    .contact-item { display: flex; align-items: center; gap: 12px; }
    .contact-icon-box { width: 36px; height: 36px; border-radius: 10px; background: var(--brand-teal-glow); color: var(--brand-teal); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .contact-icon-box svg { width: 18px; height: 18px; fill: currentColor; }
    .contact-details { display: flex; flex-direction: column; min-width: 0; flex: 1; }
    .contact-label { font-size: 11px; color: var(--text-muted); font-weight: 700; }
    .contact-value { font-size: 13.5px; font-weight: 600; word-break: break-all; overflow-wrap: anywhere; }

    /* شبکه‌های اجتماعی */
    .social-links-row { display: flex; gap: 10px; width: 100%; justify-content: center; margin-top: 10px; }
    .social-btn { width: 40px; height: 40px; border-radius: 12px; background: var(--social-bg); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-main); transition: 0.3s; }
    .social-btn:hover { background: var(--brand-teal); color: #fff; border-color: var(--brand-teal); transform: translateY(-3px); }
    .social-btn svg { width: 20px; height: 20px; fill: currentColor; }

    /* محتوای اصلی */
    .profile-main-content { display: flex; flex-direction: column; gap: 32px; min-width: 0; }
    .content-section-title { display: flex; align-items: center; gap: 10px; margin: 0 0 20px 0; font-size: 1.2rem; font-weight: 800; color: var(--brand-teal); }
    .content-section-title::before { content: ''; width: 4px; height: 18px; background: var(--brand-teal); border-radius: 10px; display: inline-block; margin-inline-end: 4px; }
    
    .desc-text { font-size: 15px; line-height: 1.8; color: var(--text-main); text-align: justify; background: var(--bg-global); padding: 20px; border-radius: 16px; border: 1px solid var(--border-color); }

    .info-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(180px, 100%), 1fr)); gap: 16px; }
    .meta-card { background: var(--bg-global); border: 1px solid var(--border-color); padding: 18px; border-radius: 18px; display: flex; flex-direction: column; gap: 6px; }
    .meta-card-title { font-size: 12px; color: var(--text-muted); font-weight: 700; }
    .meta-card-value { font-size: 14px; font-weight: 800; color: var(--brand-teal); }

    /* استایل‌های تب */
    .tabs-header { display: flex; flex-wrap: wrap; gap: 10px; border-bottom: 2px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px; }
    .tab-btn { background: var(--bg-global); border: 1px solid var(--border-color); color: var(--text-muted); padding: 10px 16px; border-radius: 12px; cursor: pointer; font-family: 'Vazirmatn', sans-serif; font-size: 14px; font-weight: 700; transition: all 0.3s ease; flex: 1 1 0; text-align: center; min-width: 0; }
    .tab-btn.active { background: var(--brand-teal); color: #fff; border-color: var(--brand-teal); box-shadow: 0 4px 12px var(--brand-teal-glow); }
    .tab-btn:hover:not(.active) { background: var(--brand-teal-glow); color: var(--brand-teal); }
    
    .tab-content { display: none; animation: fadeInTab 0.5s var(--ease-premium) forwards; }
    .tab-content.active { display: block; }
    @keyframes fadeInTab { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    /* Large tablets (768px – 991px): horizontal sidebar layout */
    @media (max-width: 991px) {
        .premium-header { padding: 12px 20px; }
        .main-container { margin: 24px auto; padding: 0 20px; }
        .premium-panel { padding: 24px; border-radius: 22px; }
        .profile-sidebar-card { flex-direction: row; flex-wrap: wrap; justify-content: flex-start; text-align: right; gap: 0; align-items: flex-start; }
        .profile-avatar-frame { width: 120px; height: 120px; border-radius: 24px; margin-bottom: 0; margin-left: 24px; flex-shrink: 0; }
        .profile-main-name { font-size: 1.3rem; margin-bottom: 10px; }
        .badges-column { width: auto; flex-direction: row; flex-wrap: wrap; gap: 8px; margin-bottom: 0; }
        .sidebar-text-block { flex: 1; min-width: 0; }
        .divider-line { width: 100%; }
        .social-links-row { justify-content: flex-start; }
        .info-meta-grid { grid-template-columns: repeat(2, 1fr); }
        .profile-layout-grid { gap: 24px; }
    }

    /* Small tablets & large phones (480px – 767px): back to vertical sidebar */
    @media (max-width: 767px) {
        .premium-header { padding: 11px 16px; }
        .header-title { font-size: 1rem; }
        .main-container { margin: 16px auto; padding: 0 14px; }
        .premium-panel { padding: 20px; border-radius: 18px; }
        .profile-sidebar-card { flex-direction: column; align-items: center; text-align: center; }
        .profile-avatar-frame { width: 130px; height: 130px; margin-left: 0; margin-bottom: 16px; }
        .sidebar-text-block { width: 100%; min-width: 0; flex: none; }
        .badges-column { flex-direction: column; width: 100%; align-items: stretch; }
        .social-links-row { justify-content: center; }
        .info-meta-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .tab-btn { padding: 9px 16px; font-size: 13px; }
        .btn-back { padding: 9px 16px; font-size: 13px; }
        .btn-back:hover { transform: none; }
        .profile-layout-grid { gap: 16px; }
    }

    /* Mobile phones (max 479px) */
    @media (max-width: 479px) {
        .premium-header { padding: 10px 12px; }
        .header-title { font-size: 0.88rem; }
        .main-container { margin: 12px auto; padding: 0 10px; }
        .premium-panel { padding: 14px; border-radius: 16px; }
        .profile-avatar-frame { width: 100px; height: 100px; border-radius: 20px; }
        .profile-main-name { font-size: 1.1rem; }
        .info-meta-grid { grid-template-columns: 1fr; gap: 10px; }
        .meta-card { padding: 12px; }
        .tab-btn { padding: 8px 11px; font-size: 12px; }
        .tabs-header { gap: 7px; padding-bottom: 12px; margin-bottom: 16px; }
        .desc-text { font-size: 13px; padding: 12px; line-height: 1.9; }
        .content-section-title { font-size: 1rem; margin-bottom: 12px; }
        .contact-value { font-size: 12px; }
        .social-btn { width: 36px; height: 36px; }
        .pill-badge { font-size: 12px; padding: 7px 12px; }
        .btn-back { padding: 8px 12px; font-size: 12.5px; }
        .btn-back:hover { transform: none; }
        .profile-layout-grid { gap: 12px; }
        .profile-main-content { gap: 12px; }
    }

    /* Very small phones (max 360px) */
    @media (max-width: 360px) {
        .premium-header { padding: 9px 10px; }
        .header-title { font-size: 0.82rem; }
        .main-container { padding: 0 8px; }
        .premium-panel { padding: 12px; border-radius: 14px; }
        .profile-avatar-frame { width: 84px; height: 84px; border-radius: 16px; }
        .tab-btn { padding: 7px 9px; font-size: 11px; }
        .tabs-header { gap: 6px; }
    }
    </style>
</head>
<body>

<header class="premium-header">
    <a href="../personal-resume-list.php" class="brand-section">
        <div class="brand-dot"></div>
        <h1 class="header-title">شبکه همکاران مکسا</h1>
    </a>
    <!-- سوییچ تغییر تم حذف شد — تم از «داشبورد مدیریت» کنترل می‌شود -->
</header>

<div class="main-container">
    <!-- تصحیح آدرس بازگشت برای جلوگیری از خطای ۵۰۰ -->
    <a href="../personal-resume-list.php" class="btn-back">
        <svg viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg> بازگشت به شبکه همکاران
    </a>

    <div class="profile-layout-grid">
        
        <!-- سایدبار (کارت همکار) -->
        <div class="premium-panel profile-sidebar-card">
            <div class="profile-avatar-frame">
                <?php if ($profile_pic): ?>
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="<?php echo $fullname; ?>">
                <?php else: ?>
                    <div class="placeholder-icon">👤</div>
                <?php endif; ?>
            </div>

            <div class="sidebar-text-block">
            <h2 class="profile-main-name"><?php echo $fullname; ?></h2>

            <div class="badges-column">
                <div class="pill-badge pill-branch"><span class="dot"></span><span><?php echo $branch_text; ?></span></div>
                <div class="pill-badge pill-role"><span class="dot"></span><span><?php echo $role_text; ?></span></div>
            </div>
            </div>

            <div class="divider-line"></div>

            <div class="contact-info-list">
                <div class="contact-item">
                    <div class="contact-icon-box"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg></div>
                    <div class="contact-details"><span class="contact-label">پست الکترونیک</span><span class="contact-value" style="direction:ltr;"><?php echo $email; ?></span></div>
                </div>
                
                <?php if ($medical_id): ?>
                <div class="contact-item">
                    <div class="contact-icon-box"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.88 4H8.12c-.44 0-.81-.3-.92-.73-.24-1.04 1.4-1.77 4.8-1.77 3.42 0 5.05.74 4.8 1.77-.11.43-.48.73-.92.73z"/></svg></div>
                    <div class="contact-details"><span class="contact-label">شماره نظام پزشکی</span><span class="contact-value"><?php echo $medical_id; ?></span></div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($instagram || $linkedin): ?>
            <div class="divider-line"></div>
            <div class="social-links-row">
                <?php if ($instagram): ?>
                <a href="<?php echo $instagram; ?>" target="_blank" class="social-btn" title="اینستاگرام">
                    <svg viewBox="0 0 24 24"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2zm-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6zm9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8 1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25zM12 7a5 5 0 0 1 5 5 5 5 0 0 1-5 5 5 5 0 0 1-5-5 5 5 0 0 1 5-5zm0 2a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3z"/></svg>
                </a>
                <?php endif; ?>
                
                <?php if ($linkedin): ?>
                <a href="<?php echo $linkedin; ?>" target="_blank" class="social-btn" title="لینکدین">
                    <svg viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- محتوای اصلی -->
        <div class="profile-main-content">
            
            <!-- کارت آمار و سال‌ها -->
            <div class="premium-panel">
                <h3 class="content-section-title">اطلاعات تحصیلی و شغلی</h3>
                <div class="info-meta-grid">
                    <div class="meta-card">
                        <span class="meta-card-title">رشته تحصیلی</span>
                        <span class="meta-card-value"><?php echo $study_field; ?></span>
                    </div>
                    <div class="meta-card">
                        <span class="meta-card-title">سال شروع به کار</span>
                        <span class="meta-card-value"><?php echo $start_work_year; ?></span>
                    </div>
                    <div class="meta-card">
                        <!-- تغییر متن بر اساس درخواست شما -->
                        <span class="meta-card-title">سال همکاری با مکسا</span>
                        <span class="meta-card-value"><?php echo $maxa_join_year; ?></span>
                    </div>
                </div>
            </div>

            <!-- باکس تب‌ها -->
            <div class="premium-panel">
                <div class="tabs-header">
                    <button class="tab-btn active" onclick="openTab(event, 'tab-bio')">بیوگرافی حرفه‌ای</button>
                    <button class="tab-btn" onclick="openTab(event, 'tab-academic')">سوابق علمی و پژوهشی</button>
                    <button class="tab-btn" onclick="openTab(event, 'tab-responsibilities')">مسئولیت‌ها در مکسا</button>
                </div>
                
                <div id="tab-bio" class="tab-content active">
                    <div class="desc-text"><?php echo nl2br($bio_professional); ?></div>
                </div>
                
                <div id="tab-academic" class="tab-content">
                    <div class="desc-text"><?php echo nl2br($academic_background); ?></div>
                </div>
                
                <div id="tab-responsibilities" class="tab-content">
                    <div class="desc-text"><?php echo nl2br($maxa_responsibilities); ?></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function openTab(evt, tabName) {
    let i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].classList.remove("active");
    }
    tablinks = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}

document.addEventListener('DOMContentLoaded', () => {
    const toggleWrapper = document.getElementById('theme-toggle-wrapper');
    const savedTheme = localStorage.getItem('premium-theme') || 'light';
    
    const setTheme = (theme) => {
        var d = theme === 'dark';
        if(d){ document.documentElement.setAttribute('data-theme','dark'); if(document.body) document.body.setAttribute('data-theme','dark'); }
        else { document.documentElement.removeAttribute('data-theme'); if(document.body) document.body.removeAttribute('data-theme'); }
    };

    setTheme(localStorage.getItem('maxa-theme') === 'dark' ? 'dark' : 'light');
    window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) setTheme(localStorage.getItem('maxa-theme')==='dark'?'dark':'light'); });

    if (toggleWrapper) toggleWrapper.addEventListener('click', () => {
        const currentActive = toggleWrapper.getAttribute('data-active');
        setTheme(currentActive === 'light' ? 'dark' : 'light');
    });
});
</script>

</body>
</html>
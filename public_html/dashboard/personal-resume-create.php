<?php
require_once __DIR__ . '/_guard.php';
dash_require('partners');
// --- START OF BACKEND LOGIC (API MODE) ---

// اطلاعات اتصال به دیتابیس از فایل کانفیگ خارج از گیت
$DB = require __DIR__ . '/../core/db-config.php';

// ==========================================
// حالت ویرایش: بارگذاری داده‌های فعلی از دیتابیس
// ==========================================
$is_edit_mode = false;
$edit_data    = null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $eid = (int)$_GET['id'];
    if ($eid > 0) {
        $c_get = new mysqli($DB['host'], $DB['user'], $DB['pass'], $DB['name']);
        if (!$c_get->connect_error) {
            $c_get->set_charset('utf8mb4');
            $s_get = $c_get->prepare('SELECT * FROM employee_profiles WHERE id = ? LIMIT 1');
            $s_get->bind_param('i', $eid);
            $s_get->execute();
            $r_get = $s_get->get_result();
            if ($r_get->num_rows > 0) {
                $edit_data    = $r_get->fetch_assoc();
                $is_edit_mode = true;
            }
            $s_get->close();
            $c_get->close();
        }
    }
}

// بررسی اینکه آیا درخواست از نوع POST (توسط fetch جاوااسکریپت) است
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // تنظیم هدر برای پاسخ به صورت JSON
    header('Content-Type: application/json');

    // Database Configuration (from config file outside git)
    $servername = $DB['host'];
    $username = $DB['user'];
    $password = $DB['pass'];
    $dbname = $DB['name'];

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        echo json_encode(["status" => "error", "message" => "خطا در اتصال به دیتابیس"]);
        exit;
    }

    $conn->set_charset("utf8mb4");

    // ==========================================
    // آپلود عکس (مشترک بین ثبت و ویرایش)
    // ==========================================
    $edit_id    = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $is_update  = ($edit_id > 0);

    // اگر ویرایش است، مسیر عکس قدیمی را به عنوان پیش‌فرض نگه می‌داریم
    $raw_existing = isset($_POST['existing_pic']) ? trim($_POST['existing_pic']) : '';
    $pic_path = !empty($raw_existing) ? $raw_existing : NULL;

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/users-profile-pic/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_info      = pathinfo($_FILES['profile_pic']['name']);
        $file_extension = strtolower($file_info['extension']);
        $new_file_name  = uniqid('profile_') . '.' . $file_extension;
        $target_file    = $upload_dir . $new_file_name;
        $allowed_types  = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                $pic_path = '/uploads/users-profile-pic/' . $new_file_name;
            } else {
                echo json_encode(["status" => "error", "message" => "خطا در ذخیره فایل عکس در سرور."]);
                exit;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "فرمت عکس نامعتبر است. فقط JPG, PNG, WEBP مجاز هستند."]);
            exit;
        }
    }

    // ==========================================
    // ذخیره در دیتابیس (INSERT یا UPDATE)
    // ==========================================
    try {
        $job_category    = isset($_POST['job_category'])    ? $_POST['job_category']    : '';
        $role            = isset($_POST['role'])            ? $_POST['role']            : '';
        $medical_id      = isset($_POST['medical_id'])      ? $_POST['medical_id']      : '';
        $start_work_year = isset($_POST['start_work_year']) ? (int)$_POST['start_work_year'] : 0;
        $maxa_join_year  = isset($_POST['maxa_join_year'])  ? (int)$_POST['maxa_join_year']  : 0;

        if ($is_update) {
            // ─── UPDATE ───
            $stmt = $conn->prepare(
                "UPDATE employee_profiles
                 SET fullname=?, profile_pic=?, email=?, branch=?, job_category=?, role=?,
                     medical_id=?, study_field=?, start_work_year=?, maxa_join_year=?,
                     instagram=?, linkedin=?, bio_professional=?, academic_background=?,
                     maxa_responsibilities=?
                 WHERE id=?"
            );
            $stmt->bind_param("ssssssssiisssssi",
                $_POST['fullname'],
                $pic_path,
                $_POST['email'],
                $_POST['branch'],
                $job_category,
                $role,
                $medical_id,
                $_POST['study_field'],
                $start_work_year,
                $maxa_join_year,
                $_POST['instagram'],
                $_POST['linkedin'],
                $_POST['bio_professional'],
                $_POST['academic_background'],
                $_POST['maxa_responsibilities'],
                $edit_id
            );
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "پروفایل همکار با موفقیت بروزرسانی شد."]);
            } else {
                echo json_encode(["status" => "error", "message" => "خطا در بروزرسانی: " . $stmt->error]);
            }
        } else {
            // ─── INSERT ───
            $stmt = $conn->prepare(
                "INSERT INTO employee_profiles
                 (fullname, profile_pic, email, branch, job_category, role, medical_id,
                  study_field, start_work_year, maxa_join_year, instagram, linkedin,
                  bio_professional, academic_background, maxa_responsibilities)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssssssiisssss",
                $_POST['fullname'],
                $pic_path,
                $_POST['email'],
                $_POST['branch'],
                $job_category,
                $role,
                $medical_id,
                $_POST['study_field'],
                $start_work_year,
                $maxa_join_year,
                $_POST['instagram'],
                $_POST['linkedin'],
                $_POST['bio_professional'],
                $_POST['academic_background'],
                $_POST['maxa_responsibilities']
            );
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "پروفایل همکار با موفقیت در دیتابیس ثبت شد."]);
            } else {
                echo json_encode(["status" => "error", "message" => "خطا در ثبت اطلاعات: " . $stmt->error]);
            }
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "خطای سیستم: " . $e->getMessage()]);
    }

    $conn->close();
    // پایان اجرای اسکریپت پس از بازگرداندن JSON تا کدهای HTML رندر نشوند
    exit;
}
// --- END OF BACKEND LOGIC ---
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $is_edit_mode ? 'ویرایش پروفایل همکار' : 'ثبت پروفایل همکار'; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

    <style>
    :root {
        --color-primary: #007b7a;
        --color-primary-dark: #006665;
        --color-primary-light: #4fb2b0;
        --color-secondary: #f4a61e;
        --color-text: #2f3437;
        --color-muted: #9d9d9d;
        --color-border: #e6e8ea;
        --color-bg: #f8f9fa;
        --color-surface: #ffffff;

        --primary-color: var(--color-primary);
        --secondary-color: var(--color-secondary);
        --bg-color: var(--color-bg);
        --text-color: var(--color-text);
        --panel-bg: var(--color-surface);
        --border-color: var(--color-border);
        --input-bg: var(--color-surface);
        --header-text: var(--color-primary-dark);
        --btn-hover-opacity: 0.9;
        --modal-overlay: rgba(47, 52, 55, 0.6);
        --anim-fast: 220ms;
        --anim-mid: 420ms;
        --anim-slow: 700ms;
    }

    [data-theme="dark"] {
        --bg-color: #121212;
        --text-color: #e0e0e0;
        --panel-bg: #1e1e1e;
        --border-color: #444444;
        --input-bg: #2d2d2d;
        --modal-overlay: rgba(0,0,0,0.8);
    }

    * { box-sizing: border-box; }

    body {
        font-family: 'Vazirmatn', sans-serif !important;
        background-color: var(--bg-color);
        color: var(--text-color);
        transition: background-color 0.3s, color 0.3s;
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }

    .container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px 15px;
        animation: pageRise var(--anim-slow) cubic-bezier(.2,.8,.2,1) both;
    }

    .card {
        background-color: var(--panel-bg);
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-radius: 12px;
        overflow: hidden;
    }

    .panel-heading {
        background-color: var(--panel-bg);
        color: var(--header-text);
        border-bottom: 2px solid var(--primary-color);
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .panel-title { margin: 0; font-weight: bold; font-size: 1.25rem; }

    .theme-toggle-btn {
        background-color: var(--secondary-color);
        color: white;
        border: none;
        border-radius: 50%;
        width: 42px;
        height: 42px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        transition: transform 0.2s, background-color 0.3s;
    }

    .theme-toggle-btn:hover { transform: scale(1.08); }
    .theme-toggle-btn svg { width: 20px; height: 20px; fill: currentColor; }

    .panel-body { 
        padding: 30px; 
        padding-bottom: 60px;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin-left: -10px;
        margin-right: -10px;
    }

    .col-3, .col-4, .col-6, .col-12 {
        padding: 0 10px;
        margin-bottom: 20px;
    }

    .col-3 { width: 25%; }
    .col-4 { width: 33.333%; }
    .col-6 { width: 50%; }
    .col-12 { width: 100%; }

    .input-group { width: 100%; position: relative; }

    .input-group label {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: bold;
        color: var(--primary-color);
    }

    .social-icon { width: 18px; height: 18px; display: inline-block; vertical-align: middle; }
    .instagram-color { fill: #E1306C; }
    .linkedin-color { fill: #0077B5; }

    .input, select.input {
        width: 100%;
        background-color: var(--input-bg);
        color: var(--text-color);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 12px;
        font-size: 15px;
        font-family: inherit;
        transition: border-color var(--anim-fast), box-shadow var(--anim-fast);
        appearance: none;
    }

    input[type="file"].input { padding: 8px 12px; cursor: pointer; }

    .input:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 125, 117, 0.15);
    }

    select.input {
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="%23007D75" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>');
        background-repeat: no-repeat;
        background-position: left 12px center;
        padding-left: 35px;
    }

    textarea.input-large { min-height: 110px; resize: vertical; line-height: 1.6; }

    .char-counter {
        display: none;
        font-size: 12px;
        color: var(--muted);
        margin-top: 5px;
        text-align: left;
        direction: ltr;
        transition: color 0.2s;
    }

    .builder-toolbar {
        display: flex;
        gap: 15px;
        margin-top: 25px;
    }

    .btn {
        padding: 14px 24px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        font-size: 15px;
        transition: transform var(--anim-fast), box-shadow var(--anim-fast), opacity var(--anim-fast);
        font-family: inherit;
        flex-grow: 1;
        text-align: center;
    }

    .btn:hover {
        opacity: var(--btn-hover-opacity);
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }

    .btn-save { background: linear-gradient(135deg, var(--primary-color), #009b90); color: white; }
    .btn-preview { background: linear-gradient(135deg, var(--secondary-color), #ffbf5a); color: white; }

    #previewModal {
        position: fixed; inset: 0; background: var(--modal-overlay);
        display: none; z-index: 9999; overflow-y: auto; padding: 20px;
    }

    .modal-content {
        max-width: 290px; 
        margin: 50px auto;
        background: transparent;
        padding: 0;
        border-radius: 24px;
        overflow: visible;
        position: relative;
        transform: translateY(14px) scale(0.98);
        opacity: 0;
        transition: transform var(--anim-mid) ease, opacity var(--anim-mid) ease;
    }

    #previewModal.show .modal-content { transform: translateY(0) scale(1); opacity: 1; }

    /* ================================================================= */
    /* استایل‌های اختصاصی پیش‌نمایش کارت همکار عینا مطابق با صفحه لیست */
    /* ================================================================= */
    :root {
        --bg-global-preview: #f4f7f6;
        --bg-surface-preview: #ffffff;
        --text-main-preview: #1e293b;
        --text-muted-preview: #64748b;
        --border-color-preview: #e2e8f0;
        --brand-teal: #0d9488;
        --brand-teal-glow: rgba(13, 148, 136, 0.15);
        --brand-gold: #d97706;
        --brand-gold-glow: rgba(217, 119, 6, 0.1);
        --card-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    [data-theme="dark"] {
        --bg-global-preview: #0f172a;
        --bg-surface-preview: #1e293b;
        --text-main-preview: #f1f5f9;
        --text-muted-preview: #94a3b8;
        --border-color-preview: #334155;
        --card-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3);
    }
    .modern-card {
        background: var(--bg-surface-preview);
        border: 1px solid var(--border-color-preview);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        display: flex;
        flex-direction: column;
        position: relative;
        width: 100%;
        text-align: right;
    }
    .image-wrapper {
        width: 100%;
        height: 280px;
        overflow: hidden;
        position: relative;
        background: var(--bg-global-preview);
    }
    .card-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .avatar-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--text-muted-preview);
        gap: 8px;
    }
    .avatar-placeholder .icon { font-size: 55px; }
    .card-info-box {
        padding: 24px 20px 16px 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        text-align: center;
    }
    .employee-name {
        margin: 0 0 16px 0;
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text-main-preview);
    }
    .badges-stack {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .pill-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 9px 14px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .pill-branch { background: var(--bg-global-preview); color: var(--text-main-preview); border-color: var(--border-color-preview); }
    .pill-branch .dot { background: var(--brand-teal); }
    .pill-role { background: var(--brand-gold-glow); color: var(--brand-gold); }
    .pill-role .dot { background: var(--brand-gold); }
    .pill-badge .dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .pill-badge .value-text { max-width: 100%; }
    .pill-branch .value-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pill-role .value-text {
        white-space: normal; 
        display: -webkit-box;
        -webkit-line-clamp: 2; 
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-align: center;
    }
    .action-drawer {
        display: flex;
        padding: 16px 20px;
        background: rgba(0, 0, 0, 0.015);
        border-top: 1px solid var(--border-color-preview);
    }
    [data-theme="dark"] .action-drawer { background: rgba(255, 255, 255, 0.02); }
    .btn-more-info {
        width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px 14px; border-radius: 14px; font-size: 14px; font-weight: 700;
        font-family: 'Vazirmatn', sans-serif; border: none; cursor: pointer;
        background: var(--brand-teal); color: #ffffff;
    }
    .btn-more-info svg { width: 18px; height: 18px; fill: currentColor; }

    .status-msg { margin-top: 15px; padding: 12px; border-radius: 6px; display: none; text-align: center; font-weight: bold; line-height: 1.6; }
    .status-msg:not(:empty) { display: block; }
    .status-ok { background: #e8f8f5; color: #007D75; border: 1px solid #007D75; }
    .status-error { background: #fdedec; color: #c0392b; border: 1px solid #e74c3c; }

    .fade-in-field { animation: fieldFadeIn 300ms ease forwards; }

    @media (max-width: 768px) {
        .col-3, .col-4, .col-6 { width: 100%; }
        .builder-toolbar { flex-direction: column; }
        .modal-content { margin: 20px auto; }
    }

    @keyframes pageRise { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fieldFadeIn { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $is_edit_mode ? ('✏️ ویرایش پروفایل ' . htmlspecialchars($edit_data['fullname'] ?? '')) : '👥 ثبت پروفایل همکار جدید'; ?></h3>
            <!-- دکمه‌ی تغییر تم حذف شد — تم از «داشبورد مدیریت» کنترل می‌شود -->
        </div>

        <div class="panel-body">
            
            <?php if ($is_edit_mode && $edit_data): ?>
            <input type="hidden" id="edit_id"      value="<?php echo (int)$edit_data['id']; ?>">
            <input type="hidden" id="existing_pic" value="<?php echo htmlspecialchars($edit_data['profile_pic'] ?? ''); ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-4">
                    <div class="input-group">
                        <label>نام و نام خانوادگی</label>
                        <input type="text" id="fullname" class="input" placeholder="نام همکار را وارد کنید" required>
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <label>📸 آپلود عکس پرسنلی</label>
                        <input type="file" id="profile_pic" class="input" accept="image/*">
                        <?php if ($is_edit_mode && !empty($edit_data['profile_pic'])): ?>
                        <div style="margin-top:9px; display:flex; align-items:center; gap:10px;">
                            <img src="<?php echo htmlspecialchars($edit_data['profile_pic']); ?>"
                                 style="width:70px; height:70px; object-fit:cover; border-radius:8px; border:2px solid var(--color-primary);"
                                 alt="عکس فعلی">
                            <span style="font-size:12px; color:var(--color-muted); line-height:1.5;">عکس فعلی<br>در صورت عدم انتخاب فایل جدید،<br>همین عکس حفظ می‌شود</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <label>ایمیل سازمان/شخصی</label>
                        <input type="email" id="email" class="input" placeholder="email@example.com" style="text-align: left; dir: ltr;" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-3" id="branch_container">
                    <div class="input-group">
                        <label>📍 محل خدمت</label>
                        <select id="branch" class="input" onchange="handleBranchChange()" required>
                            <option value="">انتخاب محل خدمت...</option>
                            <option value="tehran">شعبه تهران</option>
                            <option value="isfahan">شعبه اصفهان</option>
                            <option value="mashhad">شعبه مشهد</option>
                            <option value="qom">شعبه قم</option>
                            <option value="tabriz">شعبه تبریز</option>
                            <option value="ahvaz">شعبه اهواز</option>
                            <option value="kerman">شعبه کرمان</option>
                            <option value="kashan">شعبه کاشان</option>
                            <option value="telemedicine">مرکز تخصصی ارتباطات و پزشکی از راه دور مکسا</option>
                            <option value="setad_markazi">ستاد مرکزی</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-3" id="job_category_container" style="display: none;">
                    <div class="input-group">
                        <label>🗂️ دسته سمت شغلی</label>
                        <select id="job_category" class="input" onchange="handleCategoryChange()">
                        </select>
                    </div>
                </div>

                <div class="col-3" id="role_container" style="display: none;">
                    <div class="input-group">
                        <label>💼 سمت شغلی</label>
                        <select id="role_select" class="input">
                            <option value="">انتخاب سمت...</option>
                        </select>
                        <input type="text" id="role_input" class="input" placeholder="عنوان سمت را تایپ کنید..." maxlength="40" style="display: none;">
                        <div id="role_char_count" class="char-counter">40 / 40</div>
                    </div>
                </div>

                <div class="col-3" id="medical_id_container" style="display: none;">
                    <div class="input-group">
                        <label>🩺 شماره نظام پزشکی</label>
                        <input type="text" id="medical_id" class="input" placeholder="فقط عدد وارد کنید" style="text-align: left; dir: ltr;" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-4">
                    <div class="input-group">
                        <label>آخرین رشته تخصصی</label>
                        <input type="text" id="study_field" class="input" placeholder="مثلا مهندسی نرم‌افزار" required>
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <label>سال شروع فعالیت (کلی)</label>
                        <select id="start_work_year" class="input" required>
                            <option value="">انتخاب سال...</option>
                        </select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <label>سال همکاری با مکسا</label>
                        <select id="maxa_join_year" class="input" required>
                            <option value="">انتخاب سال...</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="input-group">
                        <label>
                            <svg class="social-icon instagram-color" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                            لینک اینستاگرام (اختیاری)
                        </label>
                        <input type="url" id="instagram" class="input" placeholder="https://instagram.com/username" style="text-align: left; dir: ltr;">
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group">
                        <label>
                            <svg class="social-icon linkedin-color" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            لینک لینکدین (اختیاری)
                        </label>
                        <input type="url" id="linkedin" class="input" placeholder="https://linkedin.com/in/username" style="text-align: left; dir: ltr;">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="input-group">
                        <label>📝 بیوگرافی حرفه ای</label>
                        <textarea id="bio_professional" class="input input-large" placeholder="خلاصه سوابق کلیدی..." required></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="input-group">
                        <label>🔬 سوابق علمی و پژوهشی (اختیاری)</label>
                        <textarea id="academic_background" class="input input-large" placeholder="کتب، مقالات و مدارک بین‌المللی..."></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="input-group">
                        <label>🏢 مسئولیت ها در مکسا</label>
                        <textarea id="maxa_responsibilities" class="input input-large" placeholder="شرح دقیق وظایف در مکسا..."></textarea>
                    </div>
                </div>
            </div>

            <div class="builder-toolbar">
                <button type="button" class="btn btn-save" onclick="saveData()">💾 ثبت نهایی</button>
                <button type="button" class="btn btn-preview" onclick="openPreview()">👁️ پیش‌نمایش کارت همکار</button>
            </div>

            <div id="statusBox" class="status-msg"></div>

        </div>
    </div>
</div>

<div id="previewModal" onclick="if(event.target===this) closePreview()">
    <div class="modal-content">
        <button onclick="closePreview()" style="position:absolute; top:12px; left:12px; background:#000000; color:#ffffff; border:none; width:30px; height:30px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:10; box-shadow:0 3px 10px rgba(0,0,0,0.4); padding:0; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <div id="previewContent"></div>
    </div>
</div>

<script>
const subRolesData = {
    treatment: [
        { value: "doctors", text: "پزشکان" },
        { value: "nurses", text: "پرستاران" },
        { value: "psychologists", text: "روانشناسان" },
        { value: "social_workers", text: "مددکاران اجتماعی" },
        { value: "spiritual_care", text: "مراقبین معنوی" },
        { value: "nutritionists", text: "متخصصین تغذیه" },
        { value: "rehabilitation", text: "متخصصین توانبخشی" },
        { value: "genetic_counselors", text: "مشاوران ژنتیک و غربالگری" }
    ]
};

// تابع آپدیت شمارنده با مدیریت هوشمند رنگ‌ها
function updateRoleCharCount() {
    const roleInput = document.getElementById('role_input');
    const charCount = document.getElementById('role_char_count');
    const remaining = 40 - roleInput.value.length;
    charCount.textContent = remaining + ' / 40';
    
    // شرط رنگ‌بندی بر اساس تعداد کاراکتر باقی‌مانده
    if (remaining <= 0) {
        charCount.style.color = '#e74c3c'; // تغییر به رنگ قرمز در صورت اتمام کاراکترها
    } else if (remaining <= 5) {
        charCount.style.color = 'var(--secondary-color)'; // رنگ نارنجی برای ۵ کاراکتر آخر
    } else {
        charCount.style.color = 'var(--muted)'; // رنگ خاکستری پیش‌فرض
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // اتصال لیسنر برای تایپ در فیلد متنی سمت
    document.getElementById('role_input').addEventListener('input', updateRoleCharCount);
});

function handleBranchChange() {
    const branchSelect = document.getElementById("branch");
    const branchContainer = document.getElementById("branch_container");
    const catContainer = document.getElementById("job_category_container");
    const catSelect = document.getElementById("job_category");
    const roleContainer = document.getElementById("role_container");
    const roleSelect = document.getElementById("role_select");
    const roleInput = document.getElementById("role_input");
    const medContainer = document.getElementById("medical_id_container");
    const charCount = document.getElementById("role_char_count");

    const selectedBranch = branchSelect.value;

    roleSelect.value = "";
    roleInput.value = "";
    updateRoleCharCount(); // ریست کردن شمارنده
    document.getElementById("medical_id").value = "";
    medContainer.style.display = "none";

    if (!selectedBranch) {
        catContainer.style.display = "none";
        roleContainer.style.display = "none";
        branchContainer.className = "col-6";
        return;
    }

    branchContainer.className = "col-3";

    if (selectedBranch === "setad_markazi") {
        catContainer.style.display = "block";
        catContainer.classList.add("fade-in-field");
        catSelect.innerHTML = '<option value="deputies" selected>معاونت‌ها</option>';
        
        roleContainer.style.display = "block";
        roleContainer.classList.add("fade-in-field");
        roleSelect.style.display = "none";
        roleInput.style.display = "block";
        charCount.style.display = "block"; 
    } else {
        catContainer.style.display = "block";
        catContainer.classList.add("fade-in-field");
        catSelect.innerHTML = `
            <option value="">انتخاب دسته...</option>
            <option value="treatment">کادر درمان</option>
            <option value="admin">کادر اداری</option>
        `;
        
        roleContainer.style.display = "none";
        roleSelect.style.display = "none";
        roleInput.style.display = "none";
        charCount.style.display = "none";
    }
}

function handleCategoryChange() {
    const branchSelect = document.getElementById("branch");
    const categorySelect = document.getElementById("job_category");
    const roleContainer = document.getElementById("role_container");
    const roleSelect = document.getElementById("role_select");
    const roleInput = document.getElementById("role_input");
    const medContainer = document.getElementById("medical_id_container");
    const charCount = document.getElementById("role_char_count");
    
    if (branchSelect.value === "setad_markazi") return;

    const selectedCategory = categorySelect.value;
    
    roleSelect.innerHTML = '<option value="">انتخاب سمت...</option>';
    roleInput.value = "";
    updateRoleCharCount(); // ریست کردن شمارنده
    document.getElementById("medical_id").value = "";

    if (selectedCategory === "treatment") {
        subRolesData.treatment.forEach(role => {
            roleSelect.add(new Option(role.text, role.value));
        });
        roleContainer.style.display = "block";
        roleContainer.classList.add("fade-in-field");
        roleSelect.style.display = "block";
        roleInput.style.display = "none";
        charCount.style.display = "none"; 
        
        medContainer.style.display = "block";
        medContainer.classList.add("fade-in-field");
        
    } else if (selectedCategory === "admin") {
        roleContainer.style.display = "block";
        roleContainer.classList.add("fade-in-field");
        roleSelect.style.display = "none";
        roleInput.style.display = "block";
        charCount.style.display = "block"; 
        
        medContainer.style.display = "none";
    } else {
        roleContainer.style.display = "none";
        medContainer.style.display = "none";
        charCount.style.display = "none";
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const currentYear = new Date().getFullYear() - 621;
    const startWorkSelect = document.getElementById('start_work_year');
    const maxaJoinSelect = document.getElementById('maxa_join_year');

    for (let year = currentYear; year >= 1360; year--) {
        startWorkSelect.add(new Option(year, year));
    }
    for (let year = currentYear; year >= 1388; year--) {
        maxaJoinSelect.add(new Option(year, year));
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const currentTheme = localStorage.getItem('theme') || 'light';
    const sunIcon = `<svg viewBox="0 0 24 24"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41L5.99 4.58zm12.37 12.37c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41l-1.06-1.06zm1.06-10.96c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06zM7.05 18.36c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06z"/></svg>`;
    const moonIcon = `<svg viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9c0-.46-.04-.92-.1-1.36-.98 1.37-2.58 2.26-4.4 2.26-2.98 0-5.4-2.42-5.4-5.4 0-1.81.89-3.42 2.26-4.4-.44-.06-.9-.1-1.36-.1z"/></svg>`;

    // تم از «داشبورد مدیریت» کنترل می‌شود (کلید مشترک: maxa-theme) — فقط روی body، بدونِ دست‌زدن به html/dir/فونت
    var applyMaxaTheme = function(){
        var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
        if(document.body){ if(d) document.body.setAttribute('data-theme','dark'); else document.body.removeAttribute('data-theme'); }
    };
    applyMaxaTheme();
    window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });

    if (themeToggleBtn) themeToggleBtn.addEventListener('click', () => {
        let theme = document.body.getAttribute('data-theme');
        if (theme === 'dark') {
            document.body.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
            themeToggleBtn.innerHTML = moonIcon;
        } else {
            document.body.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            themeToggleBtn.innerHTML = sunIcon;
        }
    });
});

// حالت ویرایش (مقدار از PHP)
const EDIT_MODE = <?php echo $is_edit_mode ? 'true' : 'false'; ?>;

function validateForm() {
    const missingFields = [];
    
    if (!document.getElementById("fullname").value.trim()) missingFields.push("نام و نام خانوادگی");
    
    const fileInput = document.getElementById("profile_pic");
    // در حالت ویرایش، آپلود عکس اجباری نیست
    if (!EDIT_MODE && (!fileInput.files || !fileInput.files[0])) missingFields.push("آپلود عکس پرسنلی");
    
    if (!document.getElementById("email").value.trim()) missingFields.push("ایمیل سازمان/شخصی");
    
    const branch = document.getElementById("branch").value;
    if (!branch) {
        missingFields.push("محل خدمت");
    } else {
        const catContainer = document.getElementById("job_category_container");
        if (catContainer.style.display !== "none") {
            const cat = document.getElementById("job_category").value;
            if (!cat) missingFields.push("دسته سمت شغلی");
        }
        
        const roleContainer = document.getElementById("role_container");
        if (roleContainer.style.display !== "none") {
            const roleSelect = document.getElementById("role_select");
            const roleInput = document.getElementById("role_input");
            if (roleSelect.style.display !== "none" && !roleSelect.value) {
                missingFields.push("سمت شغلی");
            } else if (roleInput.style.display !== "none" && !roleInput.value.trim()) {
                missingFields.push("سمت شغلی");
            }
        }
        
        const medContainer = document.getElementById("medical_id_container");
        if (medContainer.style.display !== "none" && !document.getElementById("medical_id").value.trim()) {
            missingFields.push("شماره نظام پزشکی");
        }
    }
    
    if (!document.getElementById("study_field").value.trim()) missingFields.push("آخرین رشته تخصصی");
    if (!document.getElementById("start_work_year").value) missingFields.push("سال شروع فعالیت");
    if (!document.getElementById("maxa_join_year").value) missingFields.push("سال همکاری با مکسا");
    if (!document.getElementById("bio_professional").value.trim()) missingFields.push("بیوگرافی حرفه ای");
    if (!document.getElementById("maxa_responsibilities").value.trim()) missingFields.push("مسئولیت ها در مکسا");
    
    return missingFields;
}

function saveData() {
    const errors = validateForm();
    const statusBox = document.getElementById("statusBox");
    
    if (errors.length > 0) {
        statusBox.innerHTML = "⚠️ تکمیل فیلدهای اجباری زیر الزامی است:<br>" + errors.join("، ");
        statusBox.className = "status-msg status-error";
        return;
    }
    
    statusBox.textContent = "⏳ در حال ارسال و ذخیره‌سازی اطلاعات...";
    statusBox.className = "status-msg";
    statusBox.style.display = "block";
    statusBox.style.backgroundColor = "#fff3cd";
    statusBox.style.color = "#856404";
    statusBox.style.borderColor = "#ffeeba";
    
    const formData = new FormData();
    formData.append("fullname", document.getElementById("fullname").value.trim());
    
    const fileInput = document.getElementById("profile_pic");
    if (fileInput.files[0]) {
        formData.append("profile_pic", fileInput.files[0]);
    }
    
    formData.append("email", document.getElementById("email").value.trim());
    formData.append("branch", document.getElementById("branch").value);
    
    if (document.getElementById("job_category_container").style.display !== "none") {
        formData.append("job_category", document.getElementById("job_category").value);
    }
    
    const roleSelect = document.getElementById("role_select");
    const roleInput = document.getElementById("role_input");
    if (roleSelect.style.display !== "none") {
        formData.append("role", roleSelect.value);
    } else {
        formData.append("role", roleInput.value.trim());
    }
    
    if (document.getElementById("medical_id_container").style.display !== "none") {
        formData.append("medical_id", document.getElementById("medical_id").value.trim());
    }
    
    formData.append("study_field", document.getElementById("study_field").value.trim());
    formData.append("start_work_year", document.getElementById("start_work_year").value);
    formData.append("maxa_join_year", document.getElementById("maxa_join_year").value);
    formData.append("instagram", document.getElementById("instagram").value.trim());
    formData.append("linkedin", document.getElementById("linkedin").value.trim());
    formData.append("bio_professional", document.getElementById("bio_professional").value.trim());
    formData.append("academic_background", document.getElementById("academic_background").value.trim());
    formData.append("maxa_responsibilities", document.getElementById("maxa_responsibilities").value.trim());

    // در حالت ویرایش: شناسه رکورد و مسیر عکس فعلی را ارسال می‌کنیم
    const editIdEl = document.getElementById('edit_id');
    if (editIdEl) formData.append('edit_id', editIdEl.value);
    const existingPicEl = document.getElementById('existing_pic');
    if (existingPicEl) formData.append('existing_pic', existingPicEl.value);

    const backendUrl = ""; 

    fetch(backendUrl, {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("خطا در پاسخ سرور");
        }
        return response.json();
    })
    .then(data => {
        statusBox.style.backgroundColor = "";
        statusBox.style.color = "";
        statusBox.style.borderColor = "";
        
        if(data.status === 'success') {
            statusBox.textContent = "✅ " + data.message;
            statusBox.className = "status-msg status-ok";
        } else {
            statusBox.innerHTML = "❌ خطا: " + data.message;
            statusBox.className = "status-msg status-error";
        }
    })
    .catch(error => {
        console.error("Error:", error);
        statusBox.style.backgroundColor = "";
        statusBox.style.color = "";
        statusBox.style.borderColor = "";
        statusBox.innerHTML = "❌ خطا در برقراری ارتباط با سرور.";
        statusBox.className = "status-msg status-error";
    });
}

function openPreview() {
    const errors = validateForm();
    const statusBox = document.getElementById("statusBox");
    
    if (errors.length > 0) {
        statusBox.innerHTML = "⚠️ برای مشاهده پیش‌نمایش، ابتدا فیلدهای اجباری را تکمیل کنید:<br>" + errors.join("، ");
        statusBox.className = "status-msg status-error";
        return;
    }
    
    statusBox.style.display = "none";
    statusBox.textContent = "";

    const fullname = document.getElementById("fullname").value.trim();
    const branchEl = document.getElementById("branch");
    const branchText = branchEl.options[branchEl.selectedIndex].text;
    
    const catEl = document.getElementById("job_category");
    let roleText = "";
    
    if (branchEl.value === "setad_markazi" || catEl.value === "admin") {
        roleText = document.getElementById("role_input").value.trim();
    } else if (catEl.value === "treatment") {
        const roleEl = document.getElementById("role_select");
        roleText = roleEl.options[roleEl.selectedIndex].text;
    }

    const fileInput = document.getElementById("profile_pic");
    let imageHtml = "";
    
    const existingPicEl2 = document.getElementById('existing_pic');
    const existingPicSrc = existingPicEl2 ? existingPicEl2.value : '';
    if (fileInput.files && fileInput.files[0]) {
        const imgUrl = URL.createObjectURL(fileInput.files[0]);
        imageHtml = `<img src="${imgUrl}" class="card-img" alt="${fullname}">`;
    } else if (existingPicSrc) {
        imageHtml = `<img src="${existingPicSrc}" class="card-img" alt="${fullname}">`;
    } else {
        imageHtml = `
        <div class="avatar-placeholder">
            <span class="icon">👤</span>
        </div>`;
    }

    // تولید کدهای HTML دقیقا مطابق با ساختار کارت‌های صفحه لیست همکاران
    const html = `
        <div class="modern-card">
            <div class="image-wrapper">
                ${imageHtml}
            </div>
            
            <div class="card-info-box">
                <h2 class="employee-name" title="${fullname}">${fullname}</h2>
                
                <div class="badges-stack">
                    <div class="pill-badge pill-branch" title="محل خدمت: ${branchText}">
                        <span class="dot"></span>
                        <span class="value-text">${branchText}</span>
                    </div>
                    
                    <div class="pill-badge pill-role" title="سمت شغلی: ${roleText}">
                        <span class="dot"></span>
                        <span class="value-text">${roleText}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById("previewContent").innerHTML = html;
    const modal = document.getElementById("previewModal");
    modal.style.display = "block";
    requestAnimationFrame(() => modal.classList.add("show"));
}

function closePreview() {
    const modal = document.getElementById("previewModal");
    modal.classList.remove("show");
    setTimeout(() => { modal.style.display = "none"; }, 260);
}

// ==========================================
// پیش‌پر کردن فرم در حالت ویرایش
// ==========================================
<?php if ($is_edit_mode && $edit_data): ?>
document.addEventListener('DOMContentLoaded', () => {

    // فیلدهای متنی ساده
    document.getElementById('fullname').value              = <?php echo json_encode($edit_data['fullname']              ?? ''); ?>;
    document.getElementById('email').value                 = <?php echo json_encode($edit_data['email']                 ?? ''); ?>;
    document.getElementById('study_field').value           = <?php echo json_encode($edit_data['study_field']           ?? ''); ?>;
    document.getElementById('instagram').value             = <?php echo json_encode($edit_data['instagram']             ?? ''); ?>;
    document.getElementById('linkedin').value              = <?php echo json_encode($edit_data['linkedin']              ?? ''); ?>;
    document.getElementById('bio_professional').value      = <?php echo json_encode($edit_data['bio_professional']      ?? ''); ?>;
    document.getElementById('academic_background').value   = <?php echo json_encode($edit_data['academic_background']   ?? ''); ?>;
    document.getElementById('maxa_responsibilities').value = <?php echo json_encode($edit_data['maxa_responsibilities'] ?? ''); ?>;

    // Cascade: شاخه → دسته → سمت
    const branchVal = <?php echo json_encode($edit_data['branch']       ?? ''); ?>;
    const catVal    = <?php echo json_encode($edit_data['job_category'] ?? ''); ?>;
    const roleVal   = <?php echo json_encode($edit_data['role']         ?? ''); ?>;
    const medVal    = <?php echo json_encode($edit_data['medical_id']   ?? ''); ?>;

    if (branchVal) {
        document.getElementById('branch').value = branchVal;
        handleBranchChange();
    }

    // برای ستاد مرکزی، handleBranchChange دسته را به deputies تنظیم می‌کند؛ نیازی به handleCategoryChange نیست
    if (branchVal && branchVal !== 'setad_markazi' && catVal) {
        const catEl = document.getElementById('job_category');
        if (catEl) { catEl.value = catVal; handleCategoryChange(); }
    }

    // تنظیم سمت در فیلد مناسب (select یا input text)
    if (roleVal) {
        const roleSelectEl = document.getElementById('role_select');
        const roleInputEl  = document.getElementById('role_input');
        if (roleSelectEl && roleSelectEl.style.display !== 'none') {
            roleSelectEl.value = roleVal;
        } else if (roleInputEl && roleInputEl.style.display !== 'none') {
            roleInputEl.value = roleVal;
            updateRoleCharCount();
        }
    }

    // شماره نظام پزشکی
    if (medVal) document.getElementById('medical_id').value = medVal;

    // سال‌ها — پس از پر شدن آپشن‌ها توسط DOMContentLoaded قبلی
    const startYear = <?php echo json_encode((string)($edit_data['start_work_year'] ?? '')); ?>;
    const joinYear  = <?php echo json_encode((string)($edit_data['maxa_join_year']  ?? '')); ?>;
    if (startYear) document.getElementById('start_work_year').value = startYear;
    if (joinYear)  document.getElementById('maxa_join_year').value  = joinYear;
});
<?php endif; ?>
</script>

</body>
</html>
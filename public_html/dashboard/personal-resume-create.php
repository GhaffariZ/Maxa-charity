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
    // ایزولاسیون شعبه: «محل خدمت» همیشه شعبه‌ی فعالِ کاربر است (سمتِ سرور تعیین می‌شود،
    // نه از روی ورودیِ فرم). نام شعبه در ستونِ متنیِ branch و شناسه در branch_id ذخیره می‌شود.
    // ==========================================
    $__branchId   = dash_active_branch_id();
    $__branchRow  = dash_load_branch($__branchId);
    $__branchName = $__branchRow['name'] ?? '';

    // ==========================================
    // آپلود عکس (مشترک بین ثبت و ویرایش)
    // ==========================================
    $edit_id    = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $is_update  = ($edit_id > 0);

    // IDOR: در حالتِ ویرایش، رکوردِ هدف باید متعلق به همین شعبه باشد (مگر ستاد مرکزی)
    if ($is_update && !dash_is_hq_view()) {
        $own = $conn->prepare('SELECT branch_id FROM employee_profiles WHERE id = ? LIMIT 1');
        $own->bind_param('i', $edit_id);
        $own->execute();
        $ownRes = $own->get_result()->fetch_assoc();
        $own->close();
        if (!$ownRes || (int)$ownRes['branch_id'] !== $__branchId) {
            echo json_encode(["status" => "error", "message" => "دسترسی غیرمجاز به این رکورد."]);
            $conn->close();
            exit;
        }
    }

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
            // محل خدمت (branch) و branch_id همیشه از شعبه‌ی فعالِ سرور؛ نه از فرم.
            $stmt = $conn->prepare(
                "UPDATE employee_profiles
                 SET fullname=?, profile_pic=?, email=?, branch=?, job_category=?, role=?,
                     medical_id=?, study_field=?, start_work_year=?, maxa_join_year=?,
                     instagram=?, linkedin=?, bio_professional=?, academic_background=?,
                     maxa_responsibilities=?, branch_id=?
                 WHERE id=?"
            );
            $stmt->bind_param("ssssssssiisssssii",
                $_POST['fullname'],
                $pic_path,
                $_POST['email'],
                $__branchName,
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
                $__branchId,
                $edit_id
            );
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "پروفایل همکار با موفقیت بروزرسانی شد."]);
            } else {
                echo json_encode(["status" => "error", "message" => "خطا در بروزرسانی: " . $stmt->error]);
            }
        } else {
            // ─── INSERT ───
            // محل خدمت (branch) و branch_id همیشه از شعبه‌ی فعالِ سرور؛ نه از فرم.
            $stmt = $conn->prepare(
                "INSERT INTO employee_profiles
                 (fullname, profile_pic, email, branch, job_category, role, medical_id,
                  study_field, start_work_year, maxa_join_year, instagram, linkedin,
                  bio_professional, academic_background, maxa_responsibilities, branch_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssssssiisssssi",
                $_POST['fullname'],
                $pic_path,
                $_POST['email'],
                $__branchName,
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
                $__branchId
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

// سیستم یکپارچه آیکون‌های برداری Iconoir (https://iconoir.com/)
if (!function_exists('iconoir_icon')) {
    function iconoir_icon($name, $extraClass = '', $size = null) {
        return iconoir($name, $extraClass, $size);
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $is_edit_mode ? 'ویرایش پروفایل همکار' : 'ثبت پروفایل همکار'; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
    :root {
        /* پالت رنگی اختصاصی، مدرن و هماهنگ با هویت بصری مکسا */
        --color-primary: #0d7a6e;
        --color-primary-dark: #08554d;
        --color-primary-light: #149486;
        --color-primary-soft: rgba(13, 122, 110, 0.08);
        --color-primary-glow: rgba(13, 122, 110, 0.16);

        --color-secondary: #d97706;
        --color-secondary-light: #f59e0b;
        --color-secondary-soft: rgba(217, 119, 6, 0.1);

        --color-text: #1e293b;
        --color-text-secondary: #475569;
        --color-label: #334155;
        --color-muted: #94a3b8;
        --color-border: #e2e8f0;
        --color-border-hover: #cbd5e1;
        --color-bg: #f8fafc;
        --color-surface: #ffffff;
        --color-input-bg: #ffffff;
        --color-input-disabled: #f1f5f9;

        --primary-color: var(--color-primary);
        --secondary-color: var(--color-secondary);
        --bg-color: var(--color-bg);
        --text-color: var(--color-text);
        --panel-bg: var(--color-surface);
        --border-color: var(--color-border);
        --input-bg: var(--color-input-bg);
        --header-text: #0f172a;
        --btn-hover-opacity: 0.94;
        --modal-overlay: rgba(15, 23, 42, 0.65);
        --anim-fast: 180ms cubic-bezier(0.4, 0, 0.2, 1);
        --anim-mid: 350ms cubic-bezier(0.4, 0, 0.2, 1);
        --anim-slow: 600ms cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    [data-theme="dark"], body[data-theme="dark"] {
        --color-primary: #14b8a6;
        --color-primary-dark: #0d9488;
        --color-primary-light: #2dd4bf;
        --color-primary-soft: rgba(20, 184, 166, 0.12);
        --color-primary-glow: rgba(20, 184, 166, 0.25);

        --color-secondary: #f59e0b;
        --color-secondary-light: #fbbf24;
        --color-secondary-soft: rgba(245, 158, 11, 0.15);

        --color-text: #f1f5f9;
        --color-text-secondary: #cbd5e1;
        --color-label: #e2e8f0;
        --color-muted: #64748b;
        --color-border: #334155;
        --color-border-hover: #475569;
        --color-bg: #0f172a;
        --color-surface: #1e293b;
        --color-input-bg: #152238;
        --color-input-disabled: #1e293b;

        --primary-color: var(--color-primary);
        --secondary-color: var(--color-secondary);
        --bg-color: var(--color-bg);
        --text-color: var(--color-text);
        --panel-bg: var(--color-surface);
        --border-color: var(--color-border);
        --input-bg: var(--color-input-bg);
        --header-text: #ffffff;
        --modal-overlay: rgba(0, 0, 0, 0.85);
        color-scheme: dark;
    }

    * { box-sizing: border-box; }

    body {
        font-family: 'Vazirmatn', sans-serif !important;
        background-color: var(--bg-color);
        color: var(--text-color);
        transition: background-color var(--anim-fast), color var(--anim-fast);
        margin: 0;
        padding: 0;
        min-height: 100vh;
        -webkit-font-smoothing: antialiased;
    }

    .container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 36px 16px;
        animation: pageRise var(--anim-slow) cubic-bezier(.2,.8,.2,1) both;
    }

    .card {
        background-color: var(--panel-bg);
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04), 0 8px 10px -6px rgba(0,0,0,0.02);
        border-radius: 20px;
        overflow: hidden;
        transition: border-color var(--anim-fast), box-shadow var(--anim-fast);
    }

    .panel-heading {
        background-color: var(--panel-bg);
        color: var(--header-text);
        border-bottom: 1px solid var(--border-color);
        padding: 22px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .panel-title {
        margin: 0;
        font-weight: 800;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.015em;
    }

    .panel-title-badge {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        background: var(--color-primary-soft);
        color: var(--color-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .panel-title-badge svg {
        width: 20px;
        height: 20px;
    }

    .panel-body { 
        padding: 32px 28px; 
        padding-bottom: 50px;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin-left: -10px;
        margin-right: -10px;
    }

    .col-3, .col-4, .col-6, .col-12 {
        padding: 0 10px;
        margin-bottom: 22px;
    }

    .col-3 { width: 25%; }
    .col-4 { width: 33.333%; }
    .col-6 { width: 50%; }
    .col-12 { width: 100%; }

    .input-group { width: 100%; position: relative; }

    /* استایل استاندارد، شیک و خوانای لیبل‌ها بدون رنگ سبز خسته‌کننده */
    .input-group label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 8px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--color-label);
        letter-spacing: -0.01em;
    }

    .input-group label .iconoir-icon {
        width: 17px;
        height: 17px;
        stroke-width: 1.8;
        color: var(--color-primary);
        flex-shrink: 0;
        display: inline-flex;
    }

    /* اینپوت‌ها و سلکتورها */
    .input, select.input {
        width: 100%;
        background-color: var(--input-bg);
        color: var(--text-color);
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color var(--anim-fast), box-shadow var(--anim-fast), background-color var(--anim-fast);
        appearance: none;
    }

    .input::placeholder {
        color: var(--color-muted);
        opacity: 0.85;
    }

    .input:hover {
        border-color: var(--color-border-hover);
    }

    .input:focus {
        border-color: var(--color-primary);
        outline: none;
        box-shadow: 0 0 0 3.5px var(--color-primary-glow);
    }

    select.input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%230d7a6e' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: left 14px center;
        padding-left: 38px;
        cursor: pointer;
    }

    [data-theme="dark"] select.input, body[data-theme="dark"] select.input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2314b8a6' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    }

    select.input-disabled {
        background-color: var(--color-input-disabled) !important;
        cursor: not-allowed;
        pointer-events: none;
        opacity: 0.88;
    }

    input[type="file"].input { padding: 9px 12px; cursor: pointer; }

    textarea.input-large { min-height: 115px; resize: vertical; line-height: 1.65; }

    .char-counter {
        display: none;
        font-size: 12px;
        color: var(--color-muted);
        margin-top: 6px;
        text-align: left;
        direction: ltr;
        transition: color 0.2s;
    }

    /* نوار ابزار و دکمه‌ها */
    .builder-toolbar {
        display: flex;
        gap: 16px;
        margin-top: 28px;
    }

    .btn {
        padding: 13px 24px;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 700;
        font-size: 14.5px;
        transition: transform var(--anim-fast), box-shadow var(--anim-fast), opacity var(--anim-fast);
        font-family: inherit;
        flex-grow: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
    }

    .btn .iconoir-icon {
        width: 19px;
        height: 19px;
        stroke-width: 2;
        display: inline-flex;
    }

    .btn-save {
        background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 55%, var(--color-primary-light) 100%);
        color: #ffffff;
        box-shadow: 0 4px 14px var(--color-primary-glow);
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px var(--color-primary-glow);
    }

    .btn-preview {
        background: linear-gradient(135deg, #b45309 0%, var(--color-secondary) 55%, var(--color-secondary-light) 100%);
        color: #ffffff;
        box-shadow: 0 4px 14px var(--color-secondary-soft);
    }

    .btn-preview:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(217, 119, 6, 0.32);
    }

    #previewModal {
        position: fixed; inset: 0; background: var(--modal-overlay);
        display: none; z-index: 9999; overflow-y: auto; padding: 20px;
        backdrop-filter: blur(4px);
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
    [data-theme="dark"], body[data-theme="dark"] {
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
    [data-theme="dark"] .action-drawer, body[data-theme="dark"] .action-drawer { background: rgba(255, 255, 255, 0.02); }
    .btn-more-info {
        width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px 14px; border-radius: 14px; font-size: 14px; font-weight: 700;
        font-family: 'Vazirmatn', sans-serif; border: none; cursor: pointer;
        background: var(--brand-teal); color: #ffffff;
    }
    .btn-more-info svg { width: 18px; height: 18px; fill: currentColor; }

    .status-msg {
        margin-top: 18px;
        padding: 13px 18px;
        border-radius: 12px;
        display: none;
        text-align: center;
        font-weight: 600;
        font-size: 13.5px;
        line-height: 1.7;
    }
    .status-msg:not(:empty) { display: block; }
    .status-ok {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.28);
    }
    [data-theme="dark"] .status-ok, body[data-theme="dark"] .status-ok {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border-color: rgba(52, 211, 153, 0.35);
    }
    .status-error {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.28);
    }
    [data-theme="dark"] .status-error, body[data-theme="dark"] .status-error {
        background: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border-color: rgba(248, 113, 113, 0.35);
    }
    .status-loading {
        background: rgba(217, 119, 6, 0.1);
        color: #b45309;
        border: 1px solid rgba(217, 119, 6, 0.25);
    }
    [data-theme="dark"] .status-loading, body[data-theme="dark"] .status-loading {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
        border-color: rgba(251, 191, 36, 0.3);
    }

    .fade-in-field { animation: fieldFadeIn 300ms ease forwards; }

    @media (max-width: 768px) {
        .col-3, .col-4, .col-6 { width: 100%; }
        .builder-toolbar { flex-direction: column; }
        .modal-content { margin: 20px auto; }
        .panel-body { padding: 22px 18px; }
        .panel-heading { padding: 18px 20px; }
    }

    @keyframes pageRise { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fieldFadeIn { from { opacity: 0; transform: scale(0.97); } to { opacity: 1; transform: scale(1); } }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        
        <div class="panel-heading">
            <h3 class="panel-title">
                <span class="panel-title-badge"><?php echo iconoir_icon($is_edit_mode ? 'edit-pencil' : 'user-plus'); ?></span>
                <span><?php echo $is_edit_mode ? ('ویرایش پروفایل ' . htmlspecialchars($edit_data['fullname'] ?? '')) : 'ثبت پروفایل همکار جدید'; ?></span>
            </h3>
        </div>

        <div class="panel-body">
            
            <?php if ($is_edit_mode && $edit_data): ?>
            <input type="hidden" id="edit_id"      value="<?php echo (int)$edit_data['id']; ?>">
            <input type="hidden" id="existing_pic" value="<?php echo htmlspecialchars($edit_data['profile_pic'] ?? ''); ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-4">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('user'); ?> نام و نام خانوادگی</label>
                        <input type="text" id="fullname" class="input" placeholder="نام همکار را وارد کنید" required>
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('camera'); ?> آپلود عکس پرسنلی</label>
                        <input type="file" id="profile_pic" class="input" accept="image/*">
                        <?php if ($is_edit_mode && !empty($edit_data['profile_pic'])): ?>
                        <div style="margin-top:9px; display:flex; align-items:center; gap:10px;">
                            <img src="<?php echo htmlspecialchars($edit_data['profile_pic']); ?>"
                                 style="width:70px; height:70px; object-fit:cover; border-radius:10px; border:2px solid var(--color-primary);"
                                 alt="عکس فعلی">
                            <span style="font-size:12px; color:var(--color-muted); line-height:1.5;">عکس فعلی<br>در صورت عدم انتخاب فایل جدید،<br>همین عکس حفظ می‌شود</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('mail'); ?> ایمیل سازمان/شخصی</label>
                        <input type="email" id="email" class="input" placeholder="email@example.com" style="text-align: left; dir: ltr;" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-3" id="branch_container">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('pin-alt'); ?> محل خدمت</label>
                        <?php
                          // محل خدمت قفل‌شده به شعبه‌ی فعالِ کاربر است؛ امکانِ انتخابِ شعبه‌ی دیگر وجود ندارد.
                          $__pbRow  = dash_load_branch(dash_active_branch_id());
                          $__pbName = $__pbRow['name'] ?? '';
                          // مقدارِ option همان «کلیدِ منطقِ فرم» است تا cascadeِ موجود کار کند:
                          //   ستاد مرکزی → setad_markazi (مسیرِ معاونت‌ها)، سایر شعب → branch (مسیرِ کادر درمان/اداری)
                          $__pbKey  = (int)($__pbRow['is_hq'] ?? 0) === 1 ? 'setad_markazi' : 'branch';
                        ?>
                        <select id="branch" class="input input-disabled" required onchange="handleBranchChange()"
                                tabindex="-1"
                                title="محل خدمت بر اساس شعبه‌ی شما تعیین می‌شود">
                            <option value="<?= htmlspecialchars($__pbKey) ?>" selected><?= htmlspecialchars($__pbName) ?></option>
                        </select>
                        <small style="color:var(--color-muted);font-size:11.5px;margin-top:4px;display:block;">محل خدمت به‌صورت خودکار، شعبه‌ی شما است.</small>
                    </div>
                </div>
                
                <div class="col-3" id="job_category_container" style="display: none;">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('view-grid'); ?> دسته سمت شغلی</label>
                        <select id="job_category" class="input" onchange="handleCategoryChange()">
                        </select>
                    </div>
                </div>

                <div class="col-3" id="role_container" style="display: none;">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('suitcase'); ?> سمت شغلی</label>
                        <select id="role_select" class="input">
                            <option value="">انتخاب سمت...</option>
                        </select>
                        <input type="text" id="role_input" class="input" placeholder="عنوان سمت را تایپ کنید..." maxlength="40" style="display: none;">
                        <div id="role_char_count" class="char-counter">40 / 40</div>
                    </div>
                </div>

                <div class="col-3" id="medical_id_container" style="display: none;">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('stethoscope'); ?> شماره نظام پزشکی</label>
                        <input type="text" id="medical_id" class="input" placeholder="فقط عدد وارد کنید" style="text-align: left; dir: ltr;" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-4">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('graduation-cap'); ?> آخرین رشته تخصصی</label>
                        <input type="text" id="study_field" class="input" placeholder="مثلا مهندسی نرم‌افزار" required>
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('calendar'); ?> سال شروع فعالیت (کلی)</label>
                        <select id="start_work_year" class="input" required>
                            <option value="">انتخاب سال...</option>
                        </select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('building'); ?> سال همکاری با مکسا</label>
                        <select id="maxa_join_year" class="input" required>
                            <option value="">انتخاب سال...</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('instagram'); ?> لینک اینستاگرام (اختیاری)</label>
                        <input type="url" id="instagram" class="input" placeholder="https://instagram.com/username" style="text-align: left; dir: ltr;">
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('linkedin'); ?> لینک لینکدین (اختیاری)</label>
                        <input type="url" id="linkedin" class="input" placeholder="https://linkedin.com/in/username" style="text-align: left; dir: ltr;">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('notes'); ?> بیوگرافی حرفه‌ای</label>
                        <textarea id="bio_professional" class="input input-large" placeholder="خلاصه سوابق کلیدی..." required></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('microscope'); ?> سوابق علمی و پژوهشی (اختیاری)</label>
                        <textarea id="academic_background" class="input input-large" placeholder="کتب، مقالات و مدارک بین‌المللی..."></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="input-group">
                        <label><?php echo iconoir_icon('task-list'); ?> مسئولیت‌ها در مکسا</label>
                        <textarea id="maxa_responsibilities" class="input input-large" placeholder="شرح دقیق وظایف در مکسا..."></textarea>
                    </div>
                </div>
            </div>

            <div class="builder-toolbar">
                <button type="button" class="btn btn-save" onclick="saveData()">
                    <?php echo iconoir_icon('floppy-disk'); ?>
                    <span>ثبت نهایی اطلاعات</span>
                </button>
                <button type="button" class="btn btn-preview" onclick="openPreview()">
                    <?php echo iconoir_icon('eye'); ?>
                    <span>پیش‌نمایش کارت همکار</span>
                </button>
            </div>

            <div id="statusBox" class="status-msg"></div>

        </div>
    </div>
</div>

<div id="previewModal" onclick="if(event.target===this) closePreview()">
    <div class="modal-content">
        <button onclick="closePreview()" style="position:absolute; top:12px; left:12px; background:rgba(15,23,42,0.85); color:#ffffff; border:none; width:34px; height:34px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:10; box-shadow:0 3px 12px rgba(0,0,0,0.3); padding:0; transition: transform 0.2s, background 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
            <?php echo iconoir_icon('xmark'); ?>
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
        catSelect.innerHTML = `
            <option value="deputies" selected>معاونت‌ها</option>
            <option value="admin">کادر اداری</option>
            <option value="ceo_office">حوزه مدیر عامل</option>
        `;
        
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

    for (let year = currentYear; year >= 1340; year--) {
        startWorkSelect.add(new Option(year, year));
    }
    for (let year = currentYear; year >= 1388; year--) {
        maxaJoinSelect.add(new Option(year, year));
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // تم از «داشبورد مدیریت» کنترل می‌شود (کلید مشترک: maxa-theme) — فقط روی body، بدونِ دست‌زدن به html/dir/فونت
    var applyMaxaTheme = function(){
        var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
        if(document.body){ if(d) document.body.setAttribute('data-theme','dark'); else document.body.removeAttribute('data-theme'); }
    };
    applyMaxaTheme();
    window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });
});

// حالت ویرایش (مقدار از PHP)
const EDIT_MODE = <?php echo $is_edit_mode ? 'true' : 'false'; ?>;

const STATUS_ICONS = {
    warn:    <?= json_encode(iconoir('warning-triangle', '', 18)) ?>,
    loading: <?= json_encode(iconoir('hourglass', '', 18)) ?>,
    ok:      <?= json_encode(iconoir('check-circle', '', 18)) ?>,
    error:   <?= json_encode(iconoir('xmark-circle', '', 18)) ?>
};

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
        statusBox.innerHTML = STATUS_ICONS.warn + " تکمیل فیلدهای اجباری زیر الزامی است:<br>" + errors.join("، ");
        statusBox.className = "status-msg status-error";
        statusBox.style.backgroundColor = "";
        statusBox.style.color = "";
        statusBox.style.borderColor = "";
        return;
    }
    
    statusBox.innerHTML = STATUS_ICONS.loading + " در حال ارسال و ذخیره‌سازی اطلاعات...";
    statusBox.className = "status-msg status-loading";
    statusBox.style.display = "block";
    statusBox.style.backgroundColor = "";
    statusBox.style.color = "";
    statusBox.style.borderColor = "";
    
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
            statusBox.innerHTML = STATUS_ICONS.ok + " " + data.message;
            statusBox.className = "status-msg status-ok";
        } else {
            statusBox.innerHTML = STATUS_ICONS.error + " خطا: " + data.message;
            statusBox.className = "status-msg status-error";
        }
    })
    .catch(error => {
        console.error("Error:", error);
        statusBox.style.backgroundColor = "";
        statusBox.style.color = "";
        statusBox.style.borderColor = "";
        statusBox.innerHTML = STATUS_ICONS.error + " خطا در برقراری ارتباط با سرور.";
        statusBox.className = "status-msg status-error";
    });
}

function openPreview() {
    const errors = validateForm();
    const statusBox = document.getElementById("statusBox");
    
    if (errors.length > 0) {
        statusBox.innerHTML = STATUS_ICONS.warn + " برای مشاهده پیش‌نمایش، ابتدا فیلدهای اجباری را تکمیل کنید:<br>" + errors.join("، ");
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
            <svg class="iconoir-icon" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M5 20V19C5 15.134 8.13401 12 12 12V12C15.866 12 19 15.134 19 19V20"/><path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z"/></svg>
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
    // محل خدمت قفل است؛ مقدارِ select همان کلیدِ منطق (setad_markazi یا branch) را دارد.
    const catVal    = <?php echo json_encode($edit_data['job_category'] ?? ''); ?>;
    const roleVal   = <?php echo json_encode($edit_data['role']         ?? ''); ?>;
    const medVal    = <?php echo json_encode($edit_data['medical_id']   ?? ''); ?>;
    const branchKey = document.getElementById('branch').value; // setad_markazi | branch

    // ساختِ فیلدهای دسته/سمت بر اساس کلیدِ شعبه
    handleBranchChange();

    // برای ستاد مرکزی، handleBranchChange دسته را به معاونت‌ها تنظیم می‌کند؛ نیازی به handleCategoryChange نیست
    if (branchKey !== 'setad_markazi' && catVal) {
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
<?php if (!$is_edit_mode): ?>
// حالتِ ساخت: چون «محل خدمت» قفل‌شده است، منطقِ نمایشِ دسته/سمت را بلافاصله اجرا کن.
document.addEventListener('DOMContentLoaded', function(){
    if (typeof handleBranchChange === 'function') handleBranchChange();
});
<?php endif; ?>
</script>

</body>
</html>
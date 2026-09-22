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

// تابع تولید آیکون‌های استاندارد و برداری Iconoir (https://iconoir.com/)
function iconoir_icon($name, $extraClass = '') {
    $class = 'iconoir-icon' . ($extraClass ? ' ' . $extraClass : '');
    $icons = [
        'user' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M5 20V19C5 15.134 8.13401 12 12 12V12C15.866 12 19 15.134 19 19V20"/><path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z"/></svg>',
        'user-plus' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12V8M17 10H21"/><path d="M5 20V19C5 15.134 8.13401 12 12 12V12C14.1843 12 16.1432 12.999 17.4411 14.5684"/><path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z"/></svg>',
        'edit-pencil' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20H20.5"/><path d="M16.5 3.5L18.5 5.5L7 17H5V15L16.5 3.5Z"/></svg>',
        'camera' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 19V9C2 7.89543 2.89543 7 4 7H4.5C5.12951 7 5.72229 6.70361 6.1 6.2L8.32 3.24C8.43331 3.08892 8.61115 3 8.8 3H15.2C15.3889 3 15.5667 3.08892 15.68 3.24L17.9 6.2C18.2777 6.70361 18.8705 7 19.5 7H20C21.1046 7 22 7.89543 22 9V19C22 20.1046 21.1046 21 20 21H4C2.89543 21 2 20.1046 2 19Z"/><path d="M12 17C14.2091 17 16 15.2091 16 13C16 10.7909 14.2091 9 12 9C9.79086 9 8 10.7909 8 13C8 15.2091 9.79086 17 12 17Z"/></svg>',
        'mail' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M7 9L12 12.5L17 9"/><path d="M2 17V7C2 5.89543 2.89543 5 4 5H20C21.1046 5 22 5.89543 22 7V17C22 18.1046 21.1046 19 20 19H4C2.89543 19 2 18.1046 2 17Z"/></svg>',
        'pin-alt' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21C15.5 17.4 19 14.1764 19 10.2C19 6.22355 15.866 3 12 3C8.13401 3 5 6.22355 5 10.2C5 14.1764 8.5 17.4 12 21Z"/><path d="M12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z"/></svg>',
        'view-grid' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4H10V10H4V4Z"/><path d="M14 4H20V10H14V4Z"/><path d="M4 14H10V20H4V14Z"/><path d="M14 14H20V20H14V14Z"/></svg>',
        'suitcase' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 19V9C2 7.89543 2.89543 7 4 7H20C21.1046 7 22 7.89543 22 9V19C22 20.1046 21.1046 21 20 21H4C2.89543 21 2 20.1046 2 19Z"/><path d="M8 7V5C8 3.89543 8.89543 3 10 3H14C15.1046 3 16 3.89543 16 5V7"/><path d="M12 12V14"/></svg>',
        'stethoscope' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4V10C4 14.4183 7.58172 18 12 18C16.4183 18 20 14.4183 20 10V4"/><path d="M2 4H6"/><path d="M18 4H22"/><path d="M12 18V21C12 21.5523 12.4477 22 13 22H15C16.1046 22 17 21.1046 17 20V19C17 17.8954 17.8954 17 19 17H20"/><path d="M20 16V18"/></svg>',
        'graduation-cap' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10V16"/><path d="M12 4L2 9L12 14L22 9L12 4Z"/><path d="M6 11.5V16.5C6 16.5 8 19 12 19C16 19 18 16.5 18 16.5V11.5"/></svg>',
        'calendar' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9H21M7 3V5M17 3V5M6 13H8M6 17H8M11 13H13M11 17H13M16 13H18M16 17H18M6.2 21H17.8C18.9201 21 19.4802 21 19.908 20.782C20.2843 20.5903 20.5903 20.2843 20.782 19.908C21 19.4802 21 18.9201 21 17.8V8.2C21 7.07989 21 6.51984 20.782 6.09202C20.5903 5.71569 20.2843 5.40973 19.908 5.21799C19.4802 5 18.9201 5 17.8 5H6.2C5.0799 5 4.51984 5 4.09202 5.21799C3.71569 5.40973 3.40973 5.71569 3.21799 6.09202C3 6.51984 3 7.07989 3 8.2V17.8C3 18.9201 3 19.4802 3.21799 19.908C3.40973 20.2843 3.71569 20.5903 4.09202 20.782C4.51984 21 5.07989 21 6.2 21Z"/></svg>',
        'building' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21H21"/><path d="M6 21V5C6 3.89543 6.89543 3 8 3H16C17.1046 3 18 3.89543 18 5V21"/><path d="M10 7H14"/><path d="M10 11H14"/><path d="M10 15H14"/></svg>',
        'instagram' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z"/><path d="M3 16V8C3 5.23858 5.23858 3 8 3H16C18.7614 3 21 5.23858 21 8V16C21 18.7614 18.7614 21 16 21H8C5.23858 21 3 18.7614 3 16Z"/><path d="M17.5 6.51L17.51 6.49889"/></svg>',
        'linkedin' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8V16C21 18.7614 18.7614 21 16 21H8C5.23858 21 3 18.7614 3 16V8C3 5.23858 5.23858 3 8 3H16C18.7614 3 21 5.23858 21 8Z"/><path d="M7 17V11"/><path d="M11 17V11"/><path d="M11 14C11 12.3431 12.3431 11 14 11C15.6569 11 17 12.3431 17 14V17"/><path d="M7 7.01L7.01 6.99889"/></svg>',
        'notes' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M8 14H16M8 18H12M10 3H14C16.8284 3 18.2426 3 19.1213 3.87868C20 4.75736 20 6.17157 20 9V15C20 17.8284 20 19.2426 19.1213 20.1213C18.2426 21 16.8284 21 14 21H10C7.17157 21 5.75736 21 4.87868 20.1213C4 19.2426 4 17.8284 4 15V9C4 6.17157 4 4.75736 4.87868 3.87868C5.75736 3 7.17157 3 10 3Z"/><path d="M10 7H14"/></svg>',
        'microscope' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21H20"/><path d="M9 21V18C9 14.6863 11.6863 12 15 12V12C18.3137 12 21 14.6863 21 18V21"/><path d="M7.5 4.5L13.5 10.5M5.5 6.5L11.5 12.5"/><path d="M10 2L14 6L6 14L2 10L10 2Z"/></svg>',
        'task-list' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6L20 6M9 12L20 12M9 18L20 18"/><path d="M4 6L5 7L7 5M4 12L5 13L7 11M4 18L5 19L7 17"/></svg>',
        'floppy-disk' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21.4V2.6C4 2.26863 4.26863 2 4.6 2H16.2515C16.4106 2 16.5632 2.06321 16.6757 2.17574L19.8243 5.32426C19.9368 5.43679 20 5.5894 20 5.74853V21.4C20 21.7314 19.7314 22 19.4 22H4.6C4.26863 22 4 21.7314 4 21.4Z"/><path d="M8 2V8H16V2"/><path d="M8 22V14H16V22"/></svg>',
        'eye' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12C3 12 6.5 5 12 5C17.5 5 21 12 21 12C21 12 17.5 19 12 19C6.5 19 3 12 3 12Z"/><path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z"/></svg>',
        'xmark' => '<svg class="'.$class.'" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6L18 18M18 6L6 18"/></svg>',
    ];
    return $icons[$name] ?? '';
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
        statusBox.style.backgroundColor = "";
        statusBox.style.color = "";
        statusBox.style.borderColor = "";
        return;
    }
    
    statusBox.textContent = "⏳ در حال ارسال و ذخیره‌سازی اطلاعات...";
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
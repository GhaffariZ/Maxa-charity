<?php
// --- Database Configuration ---
$servername = "localhost";
$username   = "erfantey_fantasticfour";
$password   = "Diamond19971376@macsa";
$dbname     = "erfantey_macsacharity";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    header('Location: Admin-personal-resume-list.php');
    exit;
}
$conn->set_charset("utf8mb4");

// اعتبارسنجی پارامترهای ورودی
$id     = (isset($_GET['id'])     && is_numeric($_GET['id']))     ? (int)$_GET['id']     : 0;
$status = (isset($_GET['status']) && is_numeric($_GET['status'])) ? (int)$_GET['status'] : -1;

// اگر پارامترها معتبر نبودند، برگشت به پنل مدیریت
if ($id <= 0 || !in_array($status, [0, 1])) {
    $conn->close();
    header('Location: Admin-personal-resume-list.php');
    exit;
}

// اضافه کردن خودکار ستون is_active در صورت عدم وجود در جدول
$col_check = $conn->query("SHOW COLUMNS FROM `employee_profiles` LIKE 'is_active'");
if ($col_check && $col_check->num_rows === 0) {
    $conn->query("ALTER TABLE `employee_profiles` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1");
}

// آپدیت وضعیت همکار در دیتابیس
$stmt = $conn->prepare("UPDATE `employee_profiles` SET `is_active` = ? WHERE `id` = ?");
$stmt->bind_param("ii", $status, $id);
$stmt->execute();
$stmt->close();
$conn->close();

// بازگشت به پنل مدیریت
header('Location: Admin-personal-resume-list.php');
exit;
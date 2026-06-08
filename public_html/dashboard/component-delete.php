<?php
require_once __DIR__ . "/../../config/database.php";

// بررسی اینکه مقدار ارسال شده یا نه
if (!isset($_POST['component_name'])) {
    header("Location: component-create.php");
    exit;
}

$component_name = trim($_POST['component_name']);

// جلوگیری از نفوذ مسیر
if (strpos($component_name, '..') !== false ||
    strpos($component_name, '/') !== false ||
    strpos($component_name, '\\') !== false) {
    die("نام نامعتبر است.");
}

// مسیر پوشهٔ فیزیکی
$path = __DIR__ . "/components/" . $component_name;

// تابع حذف کامل فولدر
function deleteFolder($folder)
{
    if (!is_dir($folder)) return;

    $files = scandir($folder);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $fullPath = $folder . '/' . $file;

            if (is_dir($fullPath)) {
                deleteFolder($fullPath);
            } else {
                unlink($fullPath);
            }
        }
    }

    rmdir($folder);
}

// حذف فولدر فیزیکی
if (is_dir($path)) {
    deleteFolder($path);
}

// حذف از دیتابیس (اگر در جدول ذخیره می‌کنی)
try {
    $stmt = $pdo->prepare("DELETE FROM components WHERE name = ?");
    $stmt->execute([$component_name]);
} catch (Exception $e) {
    // اگر جدول components وجود نداشت، این بخش نادیده گرفته می‌شود
}

// بازگشت به صفحهٔ اصلی
header("Location: component-create.php?deleted=1");
exit;

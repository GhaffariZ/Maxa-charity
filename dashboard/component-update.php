<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: component-create.php");
    exit;
}

if (!isset($_POST['component_name'], $_POST['component_tag'])) {
    header("Location: component-create.php?error=missing_data");
    exit;
}

$component_name = trim($_POST['component_name']);

// فقط جلوگیری از خروج از مسیر
if (
    strpos($component_name, '..') !== false ||
    strpos($component_name, '/') !== false ||
    strpos($component_name, '\\') !== false
) {
    die("نام کامپوننت نامعتبر است.");
}

$tag = trim($_POST['component_tag']);

$path = __DIR__ . "/components/" . $component_name . "/";

if (!is_dir($path)) {
    mkdir($path, 0755, true);
}

/*
|--------------------------------------------------------------------------
| ذخیره کد کامپوننت
|--------------------------------------------------------------------------
| چون textarea در حالت عادی disabled است، ممکن است component_code ارسال نشود.
| پس فقط وقتی ارسال شده بود ذخیره می‌کنیم.
*/
if (isset($_POST['component_code'])) {
    $code = $_POST['component_code'];
    file_put_contents($path . "component.php", $code);
}

/*
|--------------------------------------------------------------------------
| ذخیره meta.json
|--------------------------------------------------------------------------
*/
file_put_contents(
    $path . "meta.json",
    json_encode(['tag' => $tag], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

/*
|--------------------------------------------------------------------------
| مسیر تصاویر کامپوننت
|--------------------------------------------------------------------------
*/
$images_path = $path . "images/";

if (!is_dir($images_path)) {
    mkdir($images_path, 0755, true);
}

/*
|--------------------------------------------------------------------------
| حذف تصاویر انتخاب‌شده
|--------------------------------------------------------------------------
| داخل فرم ادیت باید checkbox هایی با name="delete_images[]" داشته باشی.
*/
if (!empty($_POST['delete_images']) && is_array($_POST['delete_images'])) {

    foreach ($_POST['delete_images'] as $image) {

        $image = basename($image);

        $image_file = $images_path . $image;

        if (is_file($image_file)) {
            unlink($image_file);
        }
    }
}

/*
|--------------------------------------------------------------------------
| آپلود تصاویر جدید
|--------------------------------------------------------------------------
| داخل فرم ادیت باید input file با name="new_component_images[]" داشته باشی.
*/
if (
    isset($_FILES['new_component_images']) &&
    isset($_FILES['new_component_images']['name']) &&
    is_array($_FILES['new_component_images']['name'])
) {

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    foreach ($_FILES['new_component_images']['name'] as $index => $original_name) {

        if (empty($original_name)) {
            continue;
        }

        if ($_FILES['new_component_images']['error'][$index] !== UPLOAD_ERR_OK) {
            continue;
        }

        $tmp_name = $_FILES['new_component_images']['tmp_name'][$index];

        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowed_extensions)) {
            continue;
        }

        /*
         * فعلاً با نام موقت ذخیره می‌کنیم.
         * پایین‌تر همه تصاویر دوباره مرتب و شماره‌گذاری می‌شوند.
        */
        $temp_image_name = "__new_" . uniqid('', true) . "." . $extension;

        move_uploaded_file($tmp_name, $images_path . $temp_image_name);
    }
}

/*
|--------------------------------------------------------------------------
| مرتب‌سازی و شماره‌گذاری مجدد تصاویر
|--------------------------------------------------------------------------
| خروجی نهایی مثلاً:
| 1.png
| 2.jpg
| 3.webp
|--------------------------------------------------------------------------
| این کار باعث می‌شود {{image1}} همیشه تصویر اول باشد،
| {{image2}} تصویر دوم و ...
*/
$image_files = [];

if (is_dir($images_path)) {

    $files = scandir($images_path);

    foreach ($files as $file) {

        if ($file === '.' || $file === '..') {
            continue;
        }

        $file_path = $images_path . $file;

        if (!is_file($file_path)) {
            continue;
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $image_files[] = $file;
        }
    }
}

natsort($image_files);
$image_files = array_values($image_files);

/*
|--------------------------------------------------------------------------
| مرحله اول: تغییر نام همه تصاویر به نام موقت
|--------------------------------------------------------------------------
| برای جلوگیری از تداخل نام‌ها مثل 1.png و 2.png
*/
$temp_files = [];

foreach ($image_files as $file) {

    $old_path = $images_path . $file;

    if (!is_file($old_path)) {
        continue;
    }

    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    $temporary_name = "__tmp_" . uniqid('', true) . "." . $extension;

    $temporary_path = $images_path . $temporary_name;

    if (rename($old_path, $temporary_path)) {
        $temp_files[] = $temporary_name;
    }
}

/*
|--------------------------------------------------------------------------
| مرحله دوم: شماره‌گذاری نهایی تصاویر
|--------------------------------------------------------------------------
*/
$counter = 1;

foreach ($temp_files as $file) {

    $old_path = $images_path . $file;

    if (!is_file($old_path)) {
        continue;
    }

    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    $new_name = $counter . "." . $extension;

    $new_path = $images_path . $new_name;

    rename($old_path, $new_path);

    $counter++;
}

header("Location: component-create.php?updated=1");
exit;

?>

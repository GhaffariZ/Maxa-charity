<?php
header('Content-Type: application/json');

// دریافت اطلاعات خام ارسالی از فرانت‌اند
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data) {
    $image       = $data['image'];
    $date        = $data['date'];
    $quantity    = (int)$data['quantity'];
    $unit_price  = (int)$data['unit_price'];
    $total_price = (int)$data['total_price'];
    $from_user   = $data['from_user'];
    $to_user     = $data['to_user'];
    $message     = $data['message'];
    $address     = $data['address'];

    // اتصال به دیتابیس - مشخصات خودت را اینجا وارد کن
    $conn = new mysqli("localhost", "erfantey_fantasticfour", "Diamond19971376@macsa", "erfantey_macsacharity");
    
    if ($conn->connect_error) {
        echo json_encode(["success" => false, "message" => "خطا در اتصال به دیتابیس"]);
        exit;
    }
    
    // ست کردن انکودینگ برای پشتیبانی کامل از زبان فارسی
    $conn->set_charset("utf8mb4");

    // آماده‌سازی دستور امن SQL
    $stmt = $conn->prepare("INSERT INTO orders (image, order_date, quantity, unit_price, total_price, from_user, to_user, message, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // s = string, i = integer
    $stmt->bind_param("ssiiissss", $image, $date, $quantity, $unit_price, $total_price, $from_user, $to_user, $message, $address);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "خطا در ذخیره‌سازی: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "داده‌ای دریافت نشد."]);
}
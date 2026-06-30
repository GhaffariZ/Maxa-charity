<?php

// لطفاً اطلاعات دیتابیس خود را در اینجا وارد کنید:
$DB_HOST = 'localhost';
$DB_NAME = 'maxa_charity'; // نام دیتابیس خود را اینجا بنویسید (اگر متفاوت است)
$DB_USER = 'root';         // نام کاربری دیتابیس (در زمپ و ومپ معمولاً root است)
$DB_PASS = '';             // رمز عبور دیتابیس (در زمپ معمولاً خالی است)
$DB_CHARSET = 'utf8mb4';

try {
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
        $opts[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$DB_CHARSET}";
    }
    
    // اتصال به دیتابیس
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME}",
        $DB_USER, 
        $DB_PASS,
        $opts
    );
    try { $pdo->exec("SET NAMES {$DB_CHARSET}"); } catch (Throwable $e) {}

    // ساخت جدول
    $sql = "
    CREATE TABLE IF NOT EXISTS `medical_records` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `full_name` varchar(255) NOT NULL,
        `mobile` varchar(20) NOT NULL,
        `age` int(11) DEFAULT NULL,
        `gender` varchar(50) DEFAULT NULL,
        `province` varchar(100) NOT NULL,
        `city` varchar(100) NOT NULL,
        `cancer_type` varchar(100) DEFAULT NULL,
        `diagnosis_status` varchar(100) DEFAULT NULL,
        `description` text,
        `documents` json DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "<h1>عملیات موفق!</h1><p>جدول medical_records با موفقیت ساخته شد.</p>";

} catch (Throwable $e) {
    echo "<h1>خطا در اتصال یا ساخت جدول</h1>";
    echo "<p style='color:red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>لطفاً متغیرهای بالای فایل install_medical_records.php را با اطلاعات صحیح دیتابیس لوکال خود جایگزین کنید.</p>";
}

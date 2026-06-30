<?php
require_once __DIR__ . '/core/db-config.php';

try {
    $pdo = new PDO(
        "mysql:host={$DB['host']};dbname={$DB['name']}",
        $DB['user'],
        $DB['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

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
    echo "Table medical_records created successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

<?php
// cleanup-old-view-logs.php
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

try {
    // حذف رکوردهای قدیمی‌تر از 30 روز
    $stmt = $pdo->prepare("
        DELETE FROM news_view_logs 
        WHERE viewed_at < DATE_SUB(NOW(), INTERVAL 15 DAY)
    ");
    $stmt->execute();
    
    $deleted = $stmt->rowCount();
    error_log("Cleaned up $deleted old view logs");
    
} catch (Exception $e) {
    error_log("Error cleaning up view logs: " . $e->getMessage());
}

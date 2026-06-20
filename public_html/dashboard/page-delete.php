<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {

    // گرفتن اطلاعات صفحه برای لاگ
    $pageStmt = $pdo->prepare("SELECT title FROM pages WHERE id = ?");
    $pageStmt->execute([$id]);
    $page = $pageStmt->fetch(PDO::FETCH_ASSOC);

    // حذف واقعی از دیتابیس
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$id]);

    // ثبت لاگ
    $logStmt = $pdo->prepare("
        INSERT INTO page_logs (page_id, action, user_name, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $actionText = "حذف صفحه (" . ($page['title'] ?? '') . ")";
    $logStmt->execute([$id, $actionText, 'admin']);
}
?>

<script>
// قرمز کردن ردیف در صفحه قبلی
if (window.opener) {
    const row = window.opener.document.querySelector('a[href="page-delete.php?id=<?=$id?>"]')?.closest("tr");
    if(row){
        row.style.background = "#ffebee";
        row.style.color = "#c62828";
    }
}

// رفرش صفحه لیست
window.location.href = "page-list.php";
</script>

<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
require_once __DIR__ . "/../../config/database.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {

    // گرفتن اطلاعات صفحه
    $stmt = $pdo->prepare("SELECT id, title, status FROM pages WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($page) {

        // اگر پیش‌نویس بود منتشر شود
        if ($page['status'] === 'draft') {

            $update = $pdo->prepare("UPDATE pages SET status='published' WHERE id=?");
            $update->execute([$id]);

            // ثبت لاگ
            $log = $pdo->prepare("
                INSERT INTO page_logs (page_id, action, user_name)
                VALUES (?, ?, ?)
            ");

            $log->execute([
                $id,
                "انتشار صفحه",
                "admin"
            ]);
        }
    }
}

header("Location: page-list.php");
exit;

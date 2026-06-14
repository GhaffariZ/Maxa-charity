<?php
require_once __DIR__ . '/_guard.php';
dash_require('news');
// news-delete.php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

try {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) throw new Exception("شناسه خبر نامعتبر است.");

    // خواندن news_code
    $stmt = $pdo->prepare("SELECT news_code FROM news WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception("خبر یافت نشد.");
    $news_code = $row['news_code'];

    // حذف از دیتابیس
    $pdo->prepare("DELETE FROM news WHERE id=?")->execute([$id]);

    // حذف پوشه فایل‌ها
    $folder = $_SERVER['DOCUMENT_ROOT'] . "/uploads/news/" . $news_code;
    if (is_dir($folder)) {
        $files = scandir($folder);
        foreach ($files as $f) {
            if ($f == "." || $f == "..") continue;
            @unlink($folder . "/" . $f);
        }
        @rmdir($folder);
    }

    echo json_encode(["status"=>"success"], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode([
        "status"=>"error",
        "message"=>$e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

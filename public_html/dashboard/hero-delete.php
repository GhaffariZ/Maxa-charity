<?php
require_once __DIR__ . '/_guard.php';
dash_require('hero');
// hero-delete.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../../config/database.php";

try {
    csrf_check();

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) throw new Exception("شناسه هیرو نامعتبر است.");

    // ایزولاسیون: فقط هیروهای شعبه‌ی فعال قابل حذف‌اند
    $__branch = dash_active_branch_id();

    $stmt = $pdo->prepare("SELECT image, branch_id FROM hero_slides WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception("هیرو یافت نشد.");
    if ((int)$row['branch_id'] !== $__branch) {
        http_response_code(403);
        throw new Exception("دسترسی غیرمجاز.");
    }

    // حذف از دیتابیس (قید branch_id برای ایمنی مضاعف)
    $pdo->prepare("DELETE FROM hero_slides WHERE id = ? AND branch_id = ?")->execute([$id, $__branch]);

    // حذفِ فایلِ تصویر (مسیر مثل /uploads/hero/hero_xxx.png ذخیره شده)
    $image = trim((string)($row['image'] ?? ''));
    if ($image !== '') {
        $file = $_SERVER['DOCUMENT_ROOT'] . $image;
        if (is_file($file)) {
            @unlink($file);
        }
    }

    echo json_encode(["status" => "success"], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

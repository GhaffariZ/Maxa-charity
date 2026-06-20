<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

try {

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        throw new Exception("داده JSON دریافت نشد");
    }

    $name = trim($data["name"] ?? "");
    $components = $data["components"] ?? [];

    if ($name === "") {
        throw new Exception("نام صفحه وارد نشده است");
    }

    if (!is_array($components) || empty($components)) {
        throw new Exception("حداقل یک کامپوننت انتخاب کنید");
    }

    // ساخت slug
    $baseSlug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $name);
    $baseSlug = preg_replace('/[\s-]+/u', '-', $baseSlug);
    $baseSlug = trim($baseSlug, '-');

    if ($baseSlug === "") {
        $baseSlug = "page-" . time();
    }

    $slug = $baseSlug;

    // ایزولاسیون: یکتایی slug در محدوده‌ی همین شعبه (composite)
    $__branch = dash_active_branch_id();
    $check = $pdo->prepare("SELECT COUNT(*) FROM pages WHERE slug=? AND branch_id=?");
    $i = 1;

    while (true) {
        $check->execute([$slug, $__branch]);
        if (!$check->fetchColumn()) break;

        $slug = $baseSlug . "-" . $i;
        $i++;
    }

    $components_json = json_encode($components, JSON_UNESCAPED_UNICODE);

$stmt = $pdo->prepare("
    INSERT INTO pages (title, slug, components, status, created_at, branch_id)
    VALUES (?, ?, ?, 'draft', NOW(), ?)
");

$stmt->execute([
    $name,
    $slug,
    $components_json,
    $__branch
]);


    echo json_encode([
        "status" => "success",
        "slug" => $slug
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

}

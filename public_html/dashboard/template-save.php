<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/component-renderer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}
csrf_check();

try {

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        throw new Exception("داده JSON دریافت نشد");
    }

    if(is_string($data['content_languages']??null))$_POST['content_languages']=$data['content_languages'];
    content_prepare($pdo,'pages');
    $editPageId=(int)($data['id']??0);if($editPageId)content_access($pdo,'pages',(string)$editPageId);
    $name = trim($data["name"] ?? "");
    $components = $data["components"] ?? [];

    if ($name === "") {
        throw new Exception("نام صفحه وارد نشده است");
    }

    if (!is_array($components) || empty($components)) {
        throw new Exception("حداقل یک کامپوننت انتخاب کنید");
    }

    $components = array_values(array_filter(array_map(static function ($component): ?string {
        return is_string($component) ? dashboard_component_name($component) : null;
    }, $components)));
    if ($components === []) {
        throw new Exception("هیچ کامپوننت معتبری انتخاب نشده است");
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

$pdo->beginTransaction();
if($editPageId){
    $existing=$pdo->prepare('SELECT slug FROM pages WHERE id=?');$existing->execute([$editPageId]);$slug=(string)$existing->fetchColumn();
    $pdo->prepare('UPDATE pages SET title=?,components=? WHERE id=?')->execute([$name,$components_json,$editPageId]);
    $pageId=$editPageId;
}else{
    $pdo->prepare("INSERT INTO pages(title,slug,components,status,created_at,branch_id) VALUES(?,?,?,'draft',NOW(),?)")->execute([$name,$slug,$components_json,$__branch]);
    $pageId=(int)$pdo->lastInsertId();
}
content_save($pdo,'pages',(string)$pageId);$pdo->commit();

    echo json_encode([
        "status" => "success",
        "slug" => $slug
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    if($pdo->inTransaction())$pdo->rollBack();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

}

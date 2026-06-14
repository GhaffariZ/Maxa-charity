<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data["id"] ?? null;
$status = $data["status"] ?? null;

$valid = ["draft", "published", "suspended"];

if (!$id || !in_array($status, $valid)) {
    echo json_encode(["status" => "error"]);
    exit;
}

$stmt = $pdo->prepare("UPDATE pages SET status=? WHERE id=?");
$stmt->execute([$status, $id]);

echo json_encode(["status" => "success"]);

$stmt = $pdo->prepare("UPDATE pages SET status=? WHERE id=?");
$stmt->execute([$status,$id]);

$page = $pdo->prepare("SELECT title FROM pages WHERE id=?");
$page->execute([$id]);
$title = $page->fetchColumn();

$log = $pdo->prepare("
INSERT INTO page_logs (page_id,action,user_name)
VALUES (?,?,?)
");

$log->execute([
    $id,
    "تغییر وضعیت به ".$status,
    "admin"
]);


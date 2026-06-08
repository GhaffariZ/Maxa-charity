<?php
// news-status-update.php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

$id = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? '';
$reason = $_POST['reason'] ?? '';

if($id > 0) {
    $stmt = $pdo->prepare("UPDATE news SET status = ?, reject_reason = ? WHERE id = ?");
    $stmt->execute([$status, $reason, $id]);
    echo json_encode(['status' => 'success']);
}
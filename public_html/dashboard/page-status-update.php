<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

// ── Enforce POST method ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    exit;
}

// ── CSRF validation (from body or header) ──────────────────────────────────
csrf_check();

$data = json_decode(file_get_contents("php://input"), true);

$id = isset($data["id"]) ? (int)$data["id"] : 0;
$status = $data["status"] ?? null;

// ── Strict status allowlist ────────────────────────────────────────────────
$valid = ["draft", "published", "suspended"];

if ($id <= 0 || !is_string($status) || !in_array($status, $valid, true)) {
    echo json_encode(["status" => "error", "message" => "Invalid parameters."]);
    exit;
}

// ── Branch-scoped atomic update ────────────────────────────────────────────
// Only update pages belonging to the current branch.
$stmt = $pdo->prepare("UPDATE pages SET status = ? WHERE id = ? AND branch_id = ?");
$stmt->execute([$status, $id, $ACTIVE_BRANCH]);

if ($stmt->rowCount() === 0) {
    // MySQL also returns zero when the requested status is already persisted.
    $exists = $pdo->prepare("SELECT status FROM pages WHERE id = ? AND branch_id = ? LIMIT 1");
    $exists->execute([$id, $ACTIVE_BRANCH]);
    $currentStatus = $exists->fetchColumn();
    if ($currentStatus === false) {
        echo json_encode(["status" => "error", "message" => "Page not found."]);
        exit;
    }
    echo json_encode(["status" => "success"]);
    exit;
}

// ── Log the action ─────────────────────────────────────────────────────────
$page = $pdo->prepare("SELECT title FROM pages WHERE id = ? AND branch_id = ? LIMIT 1");
$page->execute([$id, $ACTIVE_BRANCH]);
$title = $page->fetchColumn() ?: '';

$log = $pdo->prepare("
    INSERT INTO page_logs (page_id, action, user_name)
    VALUES (?, ?, ?)
");
$log->execute([
    $id,
    "تغییر وضعیت به " . $status,
    $DASH_USER['username'] ?? 'admin'
]);

echo json_encode(["status" => "success"]);

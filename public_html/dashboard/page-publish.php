<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

// ── Enforce POST method ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// ── CSRF validation ────────────────────────────────────────────────────────
csrf_check();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header("Location: page-list.php");
    exit;
}

// ── Branch-scoped authorization + atomic publish ───────────────────────────
// Only publish pages belonging to the current branch that are in 'draft' status.
$update = $pdo->prepare(
    "UPDATE pages SET status = 'published' WHERE id = ? AND branch_id = ? AND status = 'draft'"
);
$update->execute([$id, $ACTIVE_BRANCH]);

if ($update->rowCount() > 0) {
    // Log the action
    $page = $pdo->prepare("SELECT title FROM pages WHERE id = ? AND branch_id = ? LIMIT 1");
    $page->execute([$id, $ACTIVE_BRANCH]);
    $title = $page->fetchColumn() ?: '';

    $log = $pdo->prepare("
        INSERT INTO page_logs (page_id, action, user_name)
        VALUES (?, ?, ?)
    ");
    $log->execute([$id, "انتشار صفحه", $DASH_USER['username'] ?? 'admin']);
}

header("Location: page-list.php");
exit;

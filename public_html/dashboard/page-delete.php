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
    // Generic response — do not reveal whether the page exists
    header("Location: page-list.php");
    exit;
}

// ── Branch-scoped authorization: only delete pages belonging to this branch ─
$pageStmt = $pdo->prepare("SELECT id, title FROM pages WHERE id = ? AND branch_id = ? LIMIT 1");
$pageStmt->execute([$id, $ACTIVE_BRANCH]);
$page = $pageStmt->fetch(PDO::FETCH_ASSOC);

if (!$page) {
    // Generic not-found — do not reveal whether another branch owns this page
    header("Location: page-list.php");
    exit;
}

// ── Atomic branch-scoped delete ────────────────────────────────────────────
$stmt = $pdo->prepare("DELETE FROM pages WHERE id = ? AND branch_id = ?");
$stmt->execute([$id, $ACTIVE_BRANCH]);

if ($stmt->rowCount() > 0) {
    // Log the action
    $logStmt = $pdo->prepare("
        INSERT INTO page_logs (page_id, action, user_name, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $actionText = "حذف صفحه (" . ($page['title'] ?? '') . ")";
    $logStmt->execute([$id, $actionText, $DASH_USER['username'] ?? 'admin']);
}

header("Location: page-list.php");
exit;

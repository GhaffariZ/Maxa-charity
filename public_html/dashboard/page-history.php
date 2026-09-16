<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
SELECT l.action, l.user_name, l.created_at
FROM page_logs l
INNER JOIN pages p ON p.id = l.page_id
WHERE l.page_id = ? AND p.branch_id = ?
ORDER BY l.id DESC
");

$stmt->execute([$id, $ACTIVE_BRANCH]);

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!$logs){
    echo "هیچ سابقه‌ای ثبت نشده";
    exit;
}

echo "<ul>";

foreach($logs as $log){

echo "<li>";
echo e((string)$log["created_at"])." - ";
echo e((string)$log["user_name"])." : ";
echo e((string)$log["action"]);
echo "</li>";

}

echo "</ul>";

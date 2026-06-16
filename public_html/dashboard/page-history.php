<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

$id = intval($_GET["id"]);

$stmt = $pdo->prepare("
SELECT action,user_name,created_at
FROM page_logs
WHERE page_id=?
ORDER BY id DESC
");

$stmt->execute([$id]);

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!$logs){
    echo '<div class="hist-empty">هیچ سابقه‌ای ثبت نشده است.</div>';
    exit;
}

echo '<div class="hist-list">';

foreach($logs as $log){
    $action = htmlspecialchars($log["action"], ENT_QUOTES, "UTF-8");
    $user   = htmlspecialchars($log["user_name"], ENT_QUOTES, "UTF-8");
    $date   = htmlspecialchars($log["created_at"], ENT_QUOTES, "UTF-8");

    echo '<div class="hist-item">';
    echo   '<div class="hist-main"><span class="hist-action">'.$action.'</span>';
    echo     '<span class="hist-user">'.$user.'</span></div>';
    echo   '<time class="hist-date">'.$date.'</time>';
    echo '</div>';
}

echo '</div>';

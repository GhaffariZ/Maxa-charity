<?php
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
    echo "هیچ سابقه‌ای ثبت نشده";
    exit;
}

echo "<ul>";

foreach($logs as $log){

echo "<li>";
echo $log["created_at"]." - ";
echo $log["user_name"]." : ";
echo $log["action"];
echo "</li>";

}

echo "</ul>";

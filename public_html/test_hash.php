<?php
require_once "/home/erfantey/eo/executive-core/executive_db.php";

$stmt = $pdo->query("SELECT id, username, password FROM admin_user");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($rows);
echo "</pre>";

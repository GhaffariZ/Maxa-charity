<?php
require_once __DIR__ . '/_guard.php';
dash_require('partners');
$DB         = require __DIR__ . '/../core/db-config.php';
$servername = $DB['host'];
$username   = $DB['user'];
$password   = $DB['pass'];
$dbname     = $DB['name'];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    header('Location: Admin-personal-resume-list.php');
    exit;
}
$conn->set_charset("utf8mb4");

$id = (isset($_GET['id']) && is_numeric($_GET['id'])) ? (int)$_GET['id'] : 0;

if ($id <= 0 || !dash_is_hq_view()) {
    $conn->close();
    header('Location: Admin-personal-resume-list.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM `employee_profiles` WHERE `id` = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: Admin-personal-resume-list.php');
exit;

<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
header('Content-Type: application/json');
$dir = $_SERVER['DOCUMENT_ROOT'].'/uploads/editor/';
if(!is_dir($dir)) mkdir($dir,0755,true);

$name = uniqid().'.jpg';
move_uploaded_file($_FILES['image']['tmp_name'], $dir.$name);

echo json_encode([
  "url"=>"/uploads/editor/".$name
]);

<?php
// 301 Redirect to clean root URL
$id = (int)($_GET['id'] ?? 0);
header('Location: /courses' . ($id ? '/' . $id : ''), true, 301);
exit;

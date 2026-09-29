<?php
<<<<<<< HEAD
// 301 Redirect to clean root URL
$id = (int)($_GET['id'] ?? 0);
header('Location: /courses' . ($id ? '/' . $id : ''), true, 301);
=======
/* Redirect old dashboard course details URL to clean public URL */
$id = (int)($_GET['id'] ?? 0);
$target = $id ? ('/courses/' . $id) : '/courses';
header('Location: ' . $target, true, 301);
>>>>>>> ac86287 (feat: add course management system with frontend and dashboard pages)
exit;

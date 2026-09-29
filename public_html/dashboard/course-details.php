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
>>>>>>> 6f4a3a01e1a4f4ce5627bd424dc9a2e123fec134
exit;

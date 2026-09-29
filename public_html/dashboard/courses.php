<?php
<<<<<<< HEAD
// 301 Redirect to clean root URL
header('Location: /courses', true, 301);
=======
/* Redirect old dashboard course URL to clean public course URL */
$qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: /courses' . $qs, true, 301);
>>>>>>> ac86287 (feat: add course management system with frontend and dashboard pages)
exit;

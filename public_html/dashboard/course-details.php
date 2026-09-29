<?php
/* Redirect old dashboard course details URL to clean public URL */
$id = (int)($_GET['id'] ?? 0);
$target = $id ? ('/courses/' . $id) : '/courses';
header('Location: ' . $target, true, 301);
exit;

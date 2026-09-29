<?php
/* Redirect old dashboard course-learn URL to clean public URL */
$id = (int)($_GET['id'] ?? 0);
$target = $id ? ('/courses/learn/' . $id) : '/courses';
header('Location: ' . $target, true, 301);
exit;

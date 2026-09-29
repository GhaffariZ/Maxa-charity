<?php
/* Redirect old dashboard course URL to clean public course URL */
$qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: /courses' . $qs, true, 301);
exit;

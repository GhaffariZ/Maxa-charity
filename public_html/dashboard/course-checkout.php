<?php
/* Redirect old dashboard course checkout URL to clean public URL */
$qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: /courses/checkout' . $qs, true, 301);
exit;

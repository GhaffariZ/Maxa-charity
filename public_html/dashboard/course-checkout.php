<?php
<<<<<<< HEAD
// 301 Redirect to clean root URL
header('Location: /courses/checkout', true, 301);
=======
/* Redirect old dashboard course checkout URL to clean public URL */
$qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: /courses/checkout' . $qs, true, 301);
>>>>>>> 6f4a3a01e1a4f4ce5627bd424dc9a2e123fec134
exit;

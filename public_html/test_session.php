<?php

$path = '/home/erfantey/sessions_data';

echo "Path exists: ";
var_dump(is_dir($path));

echo "<br>";

echo "Writable: ";
var_dump(is_writable($path));

echo "<br>";

ini_set('session.save_path', $path);

session_start();

echo "Session ID: ";
var_dump(session_id());

echo "<br>";

$_SESSION['test'] = 'OK';

echo "<pre>";
print_r($_SESSION);
echo "</pre>";

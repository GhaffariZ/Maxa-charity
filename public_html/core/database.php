
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $DB = require __DIR__ . '/db-config.php';
    $host    = $DB['host'];
    $db      = $DB['name'];
    $user    = $DB['user'];
    $pass    = $DB['pass'];
    $charset = $DB['charset'];

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    die('DATABASE ERROR: ' . $e->getMessage());
}

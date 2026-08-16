<?php
require_once __DIR__ . '/_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php"; 
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE news ADD COLUMN subtitle VARCHAR(255) NULL AFTER title");
    echo "SUCCESS: Added subtitle column";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "SUCCESS: Column already exists";
    } else {
        echo "ERROR: " . $e->getMessage();
    }
}

<?php
require_once __DIR__ . '/_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php"; 
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $queries = [
        "ALTER TABLE news ADD COLUMN subtitle VARCHAR(255) NULL AFTER title",
        "ALTER TABLE news ADD COLUMN keywords VARCHAR(255) NULL AFTER category_id",
        "ALTER TABLE news ADD COLUMN tags VARCHAR(255) NULL AFTER keywords",
        "ALTER TABLE news ADD COLUMN read_time INT(11) DEFAULT 0 AFTER viewed"
    ];
    
    foreach ($queries as $q) {
        try {
            $pdo->exec($q);
            echo "SUCCESS: Executed -> $q <br>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "SKIPPED: Column already exists for -> $q <br>";
            } else {
                echo "ERROR on -> $q : " . $e->getMessage() . "<br>";
            }
        }
    }
    echo "<br><b>Database update completed! You can now use the news system.</b>";
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage();
}

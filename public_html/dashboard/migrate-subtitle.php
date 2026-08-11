<?php
require_once __DIR__ . '/_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php"; 
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $queries = [
        "ALTER TABLE news ADD COLUMN subtitle VARCHAR(255) NULL AFTER title",
        "ALTER TABLE news ADD COLUMN keywords VARCHAR(255) NULL AFTER category_id",
        "ALTER TABLE news ADD COLUMN read_time INT(11) DEFAULT 0 AFTER viewed",
        "ALTER TABLE news DROP COLUMN tags",
        "DROP TABLE IF EXISTS news_tags_map"
    ];
    
    foreach ($queries as $q) {
        try {
            $pdo->exec($q);
            echo "SUCCESS: Executed -> " . substr($q, 0, 50) . "... <br>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "SKIPPED: Already exists -> " . substr($q, 0, 50) . "... <br>";
            } elseif (strpos($e->getMessage(), 'check that column/key exists') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
                echo "SKIPPED: Column already removed -> " . substr($q, 0, 50) . "... <br>";
            } else {
                echo "ERROR on -> " . substr($q, 0, 50) . "... : " . $e->getMessage() . "<br>";
            }
        }
    }
    echo "<br><b>Database update completed! You can now use the news system.</b>";
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage();
}

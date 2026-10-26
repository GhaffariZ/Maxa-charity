<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300');

require_once __DIR__ . '/core/database.php';
require_once __DIR__.'/core/content-locales.php';
$languageScope=content_where($pdo,'macsa_stories');

try {
    // Ensure schema; public reads never create sample content.
    $pdo->exec("CREATE TABLE IF NOT EXISTS `macsa_stories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `narrator_name` VARCHAR(255) NOT NULL,
        `narrator_role` VARCHAR(255) NULL,
        `tag` VARCHAR(100) NOT NULL DEFAULT 'روایت کادر درمان',
        `excerpt` TEXT NULL,
        `content` LONGTEXT NOT NULL,
        `image` VARCHAR(500) NULL,
        `read_time` VARCHAR(50) NULL DEFAULT '۴ دقیقه مطالعه',
        `status` ENUM('published', 'draft') DEFAULT 'published',
        `sort_order` INT DEFAULT 0,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM `macsa_stories` WHERE $languageScope AND `id` = ? AND `status` = 'published' LIMIT 1");
        $stmt->execute([$id]);
        $story = content_row($pdo,'macsa_stories',$stmt->fetch(PDO::FETCH_ASSOC)?:null);
        echo json_encode(['success' => true, 'data' => $story], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $limit = (int)($_GET['limit'] ?? 50);
    if ($limit <= 0 || $limit > 100) $limit = 50;

    $stmt = $pdo->prepare("SELECT * FROM `macsa_stories` WHERE $languageScope AND `status` = 'published' ORDER BY `sort_order` ASC, `id` DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $stories = content_rows($pdo,'macsa_stories',$stmt->fetchAll(PDO::FETCH_ASSOC));

    echo json_encode(['success' => true, 'data' => $stories], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

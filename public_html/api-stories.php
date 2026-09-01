<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/core/database.php';

try {
    // Check if table exists, create and seed if needed
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

    $count = (int)$pdo->query("SELECT COUNT(*) FROM `macsa_stories`")->fetchColumn();
    if ($count === 0) {
        $seedStmt = $pdo->prepare("INSERT INTO `macsa_stories` (`title`, `narrator_name`, `narrator_role`, `tag`, `excerpt`, `content`, `read_time`, `status`, `sort_order`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $seedStmt->execute([
            'زندگی تا آخرین لحظه',
            'دکتر زهرا جعفری',
            'روانشناس مراقبت درمنزل شعبه تهران',
            'روایت کادر درمان',
            'یکی از بهترین خاطرات من در بخش مراقبت در منزل، مرتبط با مرجان، خانم جوانی بود که سال‌ها در آمریکا زندگی کرده بود. همسر ایشان ایرانی‌الاصل بود، اما در آمریکا بزرگ شده بود ...',
            "یکی از بهترین خاطرات من در بخش مراقبت در منزل، مرتبط با مرجان، خانم جوانی بود که سال‌ها در آمریکا زندگی کرده بود. همسر ایشان ایرانی‌الاصل بود، اما در آمریکا بزرگ شده بود.\n\nدر جریان درمان و همراهی با این خانواده، شاهد پیوند عمیق عاطفی و امید بی‌پایان به زندگی بودیم که تا آخرین لحظات نیز جریان داشت. تیم مراقبت در منزل مکسا با حضور مداوم، امید و آرامش را به خانه آنها هدیه داد.",
            '۴ دقیقه مطالعه',
            'published',
            1
        ]);
        $seedStmt->execute([
            'وقتی نوشتن، درمان است',
            'آقای جواد چنگی',
            'روانشناس مراقبت در منزل شعبه تهران',
            'روایت کادر درمان',
            'در تابستان امسال، خانمی در دهه پنجم زندگی‌اش با تشخیص سرطان پستان متاستاز داده به مکسا ارجاع داده شد و....',
            "در تابستان امسال، خانمی در دهه پنجم زندگی‌اش با تشخیص سرطان پستان متاستاز داده به مکسا ارجاع داده شد.\n\nبا شروع جلسات مشاوره روانشناختی و ترغیب ایشان به ثبت احساسات و نوشتن خاطرات روزانه، شاهد بازگشت آرامش و انگیزه برای مبارزه با بیماری بودیم. نوشتن برای او تبدیل به پنجره‌ای رو به امید شد.",
            '۴ دقیقه مطالعه',
            'published',
            2
        ]);
        $seedStmt->execute([
            'از بحران تا ثبات؛ تجربه‌ای از همراهی مستمر اجتماعی',
            'آقای علی یزدانی',
            'مددکار اجتماعی مراقبت در منزل شعبه تهران',
            'روایت کادر درمان',
            'سرپرست یک خانواده به دلیل درگیری مغزی ناشی از سرطان، بستری شده بود و به قول معروف وابسته به تخت بود و متاسفانه دچار زخم بستر هم شده بود. توان انجام هیچ فعالیتی را نداشت و....',
            "سرپرست یک خانواده به دلیل درگیری مغزی ناشی از سرطان، بستری شده بود و به قول معروف وابسته به تخت بود و متاسفانه دچار زخم بستر هم شده بود. توان انجام هیچ فعالیتی را نداشت.\n\nتیم مددکاری و پرستاری مکسا با حضور منظم در منزل، پانسمان تخصصی، تأمین تشک مواج و داروها و حمایت همه‌جانبه، توانست شرایط بیمار را پایدار کرده و باری سنگین را از دوش خانواده بردارد.",
            '۵ دقیقه مطالعه',
            'published',
            3
        ]);
    }

    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM `macsa_stories` WHERE `id` = ? AND `status` = 'published' LIMIT 1");
        $stmt->execute([$id]);
        $story = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $story], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $limit = (int)($_GET['limit'] ?? 50);
    if ($limit <= 0 || $limit > 100) $limit = 50;

    $stmt = $pdo->prepare("SELECT * FROM `macsa_stories` WHERE `status` = 'published' ORDER BY `sort_order` ASC, `id` DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $stories], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

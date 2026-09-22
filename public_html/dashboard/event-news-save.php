<?php
declare(strict_types=1);

require_once __DIR__ . '/_guard.php';
dash_require('events');
dash_require_hq();
require_once __DIR__ . '/../event-lib.php';
require_once __DIR__ . '/../core/html-sanitizer.php';

$isJson = str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');

function sendJson(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isJson) {
        sendJson(['status' => 'error', 'message' => 'متد درخواست نامعتبر است.'], 405);
    }
    header('Location: event-news-list.php');
    exit;
}

// بررسی CSRF
$sentToken = (string)($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sentToken)) {
    if ($isJson) {
        sendJson(['status' => 'error', 'message' => 'اعتبار نشست امن منقضی شده است. لطفاً صفحه را بازنشانی کنید (خطای CSRF).'], 419);
    }
    http_response_code(419);
    exit('درخواست نامعتبر (CSRF).');
}

try {
    $pdo = dash_pdo();
    $id = (int)($_POST['id'] ?? 0);
    $eventId = (int)($_POST['event_id'] ?? 0);
    $title = trim((string)($_POST['title'] ?? ''));
    $rawContent = (string)($_POST['content'] ?? '');

    if (!$eventId) {
        throw new InvalidArgumentException('رویداد مشخص نشده است.');
    }
    if ($title === '') {
        throw new InvalidArgumentException('عنوان خبر الزامی است.');
    }
    if (trim(strip_tags($rawContent)) === '' && !str_contains($rawContent, '<img')) {
        throw new InvalidArgumentException('متن خبر الزامی است.');
    }

    // پاکسازی امن HTML
    $content = HtmlSanitizer::sanitize($rawContent);
    $excerpt = trim((string)($_POST['excerpt'] ?? ($_POST['subtitle'] ?? '')));
    if ($excerpt === '') {
        $excerpt = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($content))), 0, 180, 'UTF-8');
    }

    $slug = event_slug($title, $id);
    $status = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';

    // مدیریت تصویر شاخص
    $image = null;
    $removeFlag = (string)($_POST['remove_featured_flag'] ?? '0');

    if (!empty($_FILES['image']['name']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        // اطمینان از وجود پوشه آپلود
        $targetDir = dirname(__DIR__) . '/uploads/events';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }
        $image = event_asset($_FILES['image'], 'event-news', ['jpg', 'jpeg', 'png', 'webp'], 12000000);
    }

    if ($id > 0) {
        // ویرایش خبر
        $fields = ['event_id = ?', 'title = ?', 'slug = ?', 'excerpt = ?', 'content = ?', 'status = ?'];
        $params = [$eventId, $title, $slug, $excerpt, $content, $status];

        if ($image !== null) {
            $fields[] = 'image = ?';
            $params[] = $image;
        } elseif ($removeFlag === '1') {
            $fields[] = 'image = NULL';
        }

        if ($status === 'published') {
            $fields[] = 'published_at = IFNULL(published_at, NOW())';
        }

        $params[] = $id;
        $sql = 'UPDATE event_news SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $pdo->prepare($sql)->execute($params);
    } else {
        // ایجاد خبر جدید
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
        $userId = (int)(dash_user()['id'] ?? 0) ?: null;

        $stmt = $pdo->prepare(
            'INSERT INTO event_news (event_id, title, slug, excerpt, content, image, status, published_at, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$eventId, $title, $slug, $excerpt, $content, $image, $status, $publishedAt, $userId]);
        $id = (int)$pdo->lastInsertId();
    }

    if ($isJson) {
        sendJson([
            'status' => 'success',
            'message' => 'خبر رویداد با موفقیت ذخیره شد.',
            'id' => $id,
            'event_id' => $eventId,
        ]);
    }

    header('Location: event-news-list.php?event_id=' . $eventId);
    exit;

} catch (Throwable $e) {
    if ($isJson) {
        sendJson([
            'status' => 'error',
            'message' => 'خطا در ذخیره‌سازی: ' . $e->getMessage(),
        ], 400);
    }
    http_response_code(400);
    exit('خطا در ذخیره خبر: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

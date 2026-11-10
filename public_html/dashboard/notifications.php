<?php
declare(strict_types=1);

// Automatic refresh must not keep an unattended admin session alive.
define('DASH_PASSIVE_REQUEST', $_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['passive'] ?? '') === '1');
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../core/dashboard-notifications.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

try {
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        http_response_code(405);
        echo json_encode(['error' => 'متد نامعتبر است.']);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_check();
    if (!$pdo) throw new RuntimeException('اتصال به پایگاه داده برقرار نیست.');
    try {
        $pdo->query('SELECT 1 FROM dashboard_notification_reads LIMIT 0');
    } catch (PDOException $e) {
        throw new RuntimeException('اعلان‌ها آماده نیستند؛ مدیر سامانه باید مهاجرت 026_dashboard_notification_reads.sql را اجرا کند.');
    }
    $context = dash_notification_context();
    $query = dash_notification_query($pdo, $context);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if (!is_string($action) || !in_array($action, ['read', 'read-all'], true)) throw new InvalidArgumentException('دستور نامعتبر است.');
        $key = $_POST['key'] ?? '';
        if (!is_string($key) || ($action === 'read' && $key === '')) throw new InvalidArgumentException('شناسهٔ اعلان نامعتبر است.');
        dash_notification_mark($pdo, $query, $context, $action === 'read' ? $key : null);
    }
    $filter = ($_GET['filter'] ?? '') === 'unread' ? 'unread' : 'all';
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]);
    if ($page === false) throw new InvalidArgumentException('شمارهٔ صفحه نامعتبر است.');
    echo json_encode(dash_notification_feed($pdo, $query, $context, $filter, $page), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(503);
    error_log('Dashboard notifications: ' . $e->getMessage());
    $message = $e instanceof RuntimeException && !($e instanceof PDOException) ? $e->getMessage() : 'دریافت اعلان‌ها ناموفق بود؛ دوباره تلاش کنید.';
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
}

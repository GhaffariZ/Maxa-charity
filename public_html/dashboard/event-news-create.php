<?php
declare(strict_types=1);
require_once __DIR__ . '/_guard.php';
dash_require('events');
dash_require_hq();

$query = [];
if (isset($_GET['id']) && (int)$_GET['id'] > 0) $query['id'] = (int)$_GET['id'];
if (isset($_GET['event_id']) && (int)$_GET['event_id'] > 0) $query['event_id'] = (int)$_GET['event_id'];
if (isset($_GET['event_news_id']) && (int)$_GET['event_news_id'] > 0) $query['event_news_id'] = (int)$_GET['event_news_id'];

if (empty($query['event_id']) && empty($query['id'])) {
    $pdo = dash_pdo();
    $latest = $pdo->query("SELECT id FROM events WHERE status != 'archived' ORDER BY id DESC LIMIT 1")->fetchColumn();
    if ($latest) {
        $query['event_id'] = (int)$latest;
    } else {
        header('Location: event-news-list.php', true, 302);
        exit;
    }
}

$target = 'news-create.php?' . http_build_query($query);
header('Location: ' . $target, true, 302);
exit;

<?php
declare(strict_types=1);
require_once __DIR__ . '/_guard.php';
dash_require('events');
dash_require_hq();

$query = [];
if (isset($_GET['id'])) $query['id'] = (int)$_GET['id'];
if (isset($_GET['event_id'])) $query['event_id'] = (int)$_GET['event_id'];
if (isset($_GET['event_news_id'])) $query['event_news_id'] = (int)$_GET['event_news_id'];
$target = 'news-create.php' . ($query ? '?' . http_build_query($query) : '?event_id=0');
header('Location: ' . $target, true, 302);
exit;

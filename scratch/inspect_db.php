<?php
require_once __DIR__ . '/../public_html/core/database.php';

echo "=== EVENTS ===\n";
$st = $pdo->query('SELECT id, title, slug, status, event_date FROM events');
print_r($st->fetchAll(PDO::FETCH_ASSOC));

echo "=== EVENT HEROES ===\n";
$st = $pdo->query('SELECT * FROM event_heroes');
print_r($st->fetchAll(PDO::FETCH_ASSOC));

echo "=== EVENT NEWS ===\n";
$st = $pdo->query('SELECT id, event_id, title, slug, status, published_at FROM event_news');
print_r($st->fetchAll(PDO::FETCH_ASSOC));

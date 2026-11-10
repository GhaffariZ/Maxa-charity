<?php
declare(strict_types=1);

// Exercise the page's actual listing query with in-memory editorial fixtures.
$source = file_get_contents(__DIR__ . '/../public_html/dashboard/components/doctorspage/component.php');
if (!preg_match('/\$articleStmt = \$pdo->prepare\("(.*?)"\);/s', $source, $match)) {
    throw new RuntimeException('Doctors article query not found');
}
$query = str_replace('$articleScope', '1=1', $match[1]);
$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->sqliteCreateFunction('NOW', static fn() => '2026-11-03 12:00:00');
$pdo->exec('CREATE TABLE news_categories (id INTEGER, slug TEXT, is_active INTEGER)');
$pdo->exec("INSERT INTO news_categories VALUES (1, 'doctors-articles', 1), (2, 'other', 1), (3, 'doctors-articles', 0)");
$pdo->exec('CREATE TABLE news (id INTEGER, title TEXT, subtitle TEXT, content TEXT, author TEXT, category_id INTEGER, publish_date TEXT, news_code TEXT, featured_image TEXT, read_time INTEGER, status TEXT, reject_reason TEXT)');
$insert = $pdo->prepare('INSERT INTO news VALUES (?, ?, NULL, ?, NULL, ?, ?, NULL, NULL, 1, ?, ?)');
foreach ([
    [1, 'Visible', 'Body', 1, '2026-11-03 11:00:00', 'published', null],
    [2, 'Draft', 'Body', 1, '2026-11-03 11:00:00', 'draft', null],
    [3, 'Scheduled', 'Body', 1, '2026-11-04 11:00:00', 'published', null],
    [4, 'Rejected', 'Body', 1, '2026-11-03 11:00:00', 'published', 'Needs edits'],
    [5, 'Other category', 'Body', 2, '2026-11-03 11:00:00', 'published', null],
    [6, 'Inactive category', 'Body', 3, '2026-11-03 11:00:00', 'published', null],
    [7, 'Newest', 'Body', 1, '2026-11-03 11:30:00', 'published', null],
] as $row) $insert->execute($row);
$actual = array_map('intval', $pdo->query($query)->fetchAll(PDO::FETCH_COLUMN));
if ($actual !== [7, 1]) throw new RuntimeException('Unexpected doctors articles: ' . json_encode($actual));
echo "Doctors articles: published and current category only, newest first: OK\n";

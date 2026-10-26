<?php
declare(strict_types=1);

// Run the actual controller query against local fixtures, without a site database.
// Locale SQL/overlays are exercised against MySQL in content-locales.php.
function content_where(PDO $db,string $type,string $column): string { if($type!=='news')throw new RuntimeException('Unexpected content type');return '1=1'; }
function content_rows(PDO $db,string $type,array $rows): array { return $rows; }
$source = file_get_contents(__DIR__ . '/../public_html/dashboard/news-view.php');
if (!preg_match('/\$relatedNews = \[\];.*?\n\}\s*(?=\/\/)/s', $source, $match)) {
    throw new RuntimeException('Related-news controller block not found.');
}
$queryBlock = $match[0];
$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->sqliteCreateFunction('NOW', static fn() => '2026-10-20 12:00:00');
$pdo->exec('CREATE TABLE news_categories (id INTEGER PRIMARY KEY, name TEXT)');
$pdo->exec("INSERT INTO news_categories VALUES (11, 'Same name'), (12, 'Same name'), (13, 'Empty'), (14, 'Single')");
$pdo->exec('CREATE TABLE news (id INTEGER PRIMARY KEY, title TEXT, category_id INTEGER, status TEXT, publish_date TEXT, reject_reason TEXT)');
$insert = $pdo->prepare('INSERT INTO news VALUES (?, ?, ?, ?, ?, ?)');
foreach ([
    [90, 'Current', 11, 'published', '2026-10-20 11:00:00', null],
    [1, 'Newest eligible', 11, 'published', '2026-10-19 12:00:00', null],
    [2, 'Older, same timestamp', 11, 'published', '2026-10-18 12:00:00', ''],
    [3, 'Higher ID, same timestamp', 11, 'published', '2026-10-18 12:00:00', '   '],
    [80, 'Old date, high ID', 11, 'published', '2026-10-17 12:00:00', null],
    [99, 'Other category', 12, 'published', '2026-10-20 11:00:00', null],
    [5, 'Draft', 11, 'draft', '2026-10-20 11:00:00', null],
    [6, 'Scheduled', 11, 'published', '2026-10-21 12:00:00', null],
    [7, 'Rejected', 11, 'published', '2026-10-20 11:00:00', 'Rejected'],
    [140, 'Only article in category', 14, 'published', '2026-10-20 11:00:00', null],
    [150, 'Uncategorized', null, 'published', '2026-10-20 11:00:00', null],
] as $row) {
    $insert->execute($row);
}
$run = static function (array $news, array $expected) use ($pdo, $queryBlock): void {
    $id = $news['id'];
    eval($queryBlock);
    $actual = array_map('intval', array_column($relatedNews, 'id'));
    if ($actual !== $expected) {
        throw new RuntimeException('Unexpected related news: ' . json_encode($actual));
    }
};
$run(['id' => 90, 'category_id' => 11], [1, 3, 2]);
$run(['id' => 1, 'category_id' => 11], [90, 3, 2]);
$run(['id' => 90, 'category_id' => 12], [99]);
$run(['id' => 90, 'category_id' => 13], []);
$run(['id' => 140, 'category_id' => 14], []);
$run(['id' => 150, 'category_id' => null], []);
$run(['id' => 150], []);
echo "Related news: category isolation, newest first, tie order, limit, current/draft/future/rejected exclusion, no fallback: OK\n";

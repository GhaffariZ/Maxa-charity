<?php
declare(strict_types=1);

$source = file_get_contents(__DIR__ . '/../public_html/dashboard/news-list.php');
if (!preg_match('/\$__branch\s*=\s*dash_active_branch_id\(\);.*?\$news_list = \$stmt->fetchAll\(PDO::FETCH_ASSOC\);/s', $source, $match)) {
    throw new RuntimeException('News listing query not found');
}
$listing = $match[0];
$editor = false;
function dash_active_branch_id(): int { return 1; }
function dash_is_news_editor(): bool { return $GLOBALS['editor']; }
function listed(PDO $pdo, string $listing, array $filters): array {
    $_GET = $filters;
    eval($listing);
    return array_map('intval', array_column($news_list, 'id'));
}
function check_ids(array $expected, array $actual): void {
    if ($expected !== $actual) throw new RuntimeException('Unexpected news scope: ' . json_encode($actual));
}
$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec("CREATE TABLE branches (id INTEGER, name TEXT, slug TEXT);
    INSERT INTO branches VALUES (1, 'HQ', 'hq'), (2, 'Other', 'other');
    CREATE TABLE news_categories (id INTEGER, slug TEXT);
    INSERT INTO news_categories VALUES (10, 'doctors-articles'), (20, 'general');
    CREATE TABLE news (id INTEGER, branch_id INTEGER, category_id INTEGER, status TEXT);
    INSERT INTO news VALUES (1,1,10,'draft'), (2,2,10,'review'), (3,1,20,'review'), (4,1,10,'review'), (5,2,20,'published');");
check_ids([4,3,1], listed($pdo, $listing, []));
check_ids([], listed($pdo, $listing, ['id'=>2]));
$editor = true;
check_ids([4,3,2,5,1], listed($pdo, $listing, []));
check_ids([4,2,1], listed($pdo, $listing, ['section'=>'doctors']));
check_ids([4], listed($pdo, $listing, ['section'=>'doctors','review'=>1,'scope'=>'active']));
check_ids([2], listed($pdo, $listing, ['section'=>'doctors','id'=>2]));
$editor = false;
check_ids([], listed($pdo, $listing, ['section'=>'doctors','id'=>2]));
$pdo->exec('DELETE FROM news_categories WHERE id=10');
check_ids([], listed($pdo, $listing, ['section'=>'doctors']));
echo "PASS news branch isolation, editor scope, doctors category, review and record filters\n";

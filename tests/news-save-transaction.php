<?php
declare(strict_types=1);
require_once __DIR__ . '/../public_html/core/html-sanitizer.php';

class NewsTestPdo extends PDO {
    public function exec(string $statement): int|false {
        return str_starts_with($statement, 'SET NAMES') ? 0 : parent::exec($statement);
    }
}
$db = new NewsTestPdo('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec("CREATE TABLE news_categories(id INTEGER, is_active INTEGER);
    INSERT INTO news_categories VALUES(1,1);
    CREATE TABLE news(id INTEGER PRIMARY KEY, news_code TEXT, title TEXT, subtitle TEXT, content TEXT,
        author TEXT, publish_date TEXT, category_id INTEGER, keywords TEXT, tags TEXT, status TEXT,
        viewed INTEGER, read_time INTEGER, branch_id INTEGER, featured_image TEXT);
    CREATE TABLE news_tags_map(news_id INTEGER, tag_id INTEGER);
    CREATE TABLE locales(news_id INTEGER);");
function dash_pdo(): PDO { return $GLOBALS['db']; }
function dash_require(string $section): void {}
function dash_active_branch_id(): int { return 1; }
function dash_is_hq_view(): bool { return true; }
function csrf_check(): void {}
function content_prepare(PDO $pdo, string $type): void {}
function content_save(PDO $pdo, string $type, string $id): void {
    $pdo->prepare('INSERT INTO locales VALUES(?)')->execute([$id]);
    if ($GLOBALS['failTranslation']) throw new RuntimeException('Translation failed');
}
$source = file_get_contents(__DIR__ . '/../public_html/dashboard/news-save.php');
$source = preg_replace('/^require_once .*;\r?$/m', '', $source);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_FILES = [];
$failTranslation = false;
function save_news(string $source, array $input): array {
    $_POST = $input + ['title'=>'Original', 'content'=>'<p>Safe news</p>', 'category_id'=>1];
    ob_start();
    eval('?>' . $source);
    return json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);
}
$created = save_news($source, []);
if ($created['status'] !== 'success' || $db->inTransaction()
    || (int)$db->query('SELECT count(*) FROM locales')->fetchColumn() !== 1) {
    throw new RuntimeException('News and translations must commit together');
}
$failTranslation = true;
$failed = save_news($source, ['id'=>1, 'title'=>'Changed']);
if ($failed['status'] !== 'error' || $db->inTransaction()
    || $db->query('SELECT title FROM news WHERE id=1')->fetchColumn() !== 'Original'
    || (int)$db->query('SELECT count(*) FROM locales')->fetchColumn() !== 1) {
    throw new RuntimeException('Translation failure must roll back news and translations');
}
echo "PASS news save commits once and rolls back news and translations on failure\n";

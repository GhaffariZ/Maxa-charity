<?php
// Render the exported home component order without a production database.
class HomeLayoutStatement extends PDOStatement
{
    public function execute(?array $params = null): bool { return true; }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed { return false; }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array { return []; }
}
class HomeLayoutPDO extends PDO
{
    public function __construct() {}
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false { return new HomeLayoutStatement(); }
    public function prepare(string $query, array $options = []): PDOStatement|false { return new HomeLayoutStatement(); }
}
$pdo = new HomeLayoutPDO();
require __DIR__ . '/../../public_html/core/component-renderer.php';
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home layout check</title>
<link rel="stylesheet" href="/assets/site-locale.css">
<link rel="stylesheet" href="/assets/site-navigation.css">
<script src="/assets/public-content.js"></script>
<script src="/assets/site-locale.js" defer></script>
<script src="/assets/site-navigation.js" defer></script>
<!-- Same critical sizing rules as page-view.php. -->
<style>*{box-sizing:border-box}html,body{margin:0;padding:0;overflow-x:hidden}body{font-family:Vazirmatn,Tahoma,sans-serif;color:#111}img{max-width:100%;height:auto}</style>
</head>
<body><main id="main-content">
<?php
foreach (['heroindex', 'hamrah', 'aboutus', 'macsa_stories', 'recent-news-hero-v2', 'ourexcellence', 'footer'] as $component) {
    render_dashboard_component($component, ['pdo' => $pdo]);
}
?>
</main></body></html>

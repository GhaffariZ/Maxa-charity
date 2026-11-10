<?php
// Render the real public components without a database or authenticated session.
class BannerTestStatement extends PDOStatement
{
    public function execute(?array $params = null): bool { return true; }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array { return []; }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        if (($GLOBALS['argv'][2] ?? '') === 'disabled') return false;
        return [
            'id' => 1, 'event_date' => '2099-10-08', 'start_time' => '08:00:00',
            'slug' => 'conference', 'banner_link' => '', 'banner_theme' => 'teal',
            'title' => 'ششمین همایش ملی مراقبت های حمایتی و تسکینی',
            'banner_label' => 'رویداد پیش رو', 'banner_cta' => 'مشاهده رویداد',
        ];
    }
}
class BannerTestPDO extends PDO
{
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false { return new BannerTestStatement(); }
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        return new BannerTestStatement();
    }
}
$pdo = new BannerTestPDO();
$component = ($argv[1] ?? '') === 'home' ? 'heroindex' : 'header';
require __DIR__ . '/../../public_html/dashboard/components/' . $component . '/component.php';
?>
<style>body { margin: 0; } #test-content { min-height: 2400px; background: #e9f4f4; }</style>
<main id="test-content"></main>

<?php
if (!function_exists('event_h')) {
    function event_h($str): string {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

function event_global_banner(?PDO $pdo = null): string {
    static $rendered = false;
    if ($rendered) {
        return '';
    }

    if (!$pdo) {
        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
            $pdo = $GLOBALS['pdo'];
        } else {
            // Safely attempt standalone connection without triggering core/database.php's die()
            try {
                $cfgFile = __DIR__ . '/core/db-config.php';
                if (file_exists($cfgFile)) {
                    $DB = require $cfgFile;
                    if (!empty($DB['name']) && !empty($DB['user'])) {
                        $dsn = "mysql:host={$DB['host']};dbname={$DB['name']};charset=" . ($DB['charset'] ?? 'utf8mb4');
                        $pdo = new PDO($dsn, $DB['user'], $DB['pass'], [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                        ]);
                    }
                }
            } catch (Throwable $t) {
                $pdo = null;
            }
            if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
                $pdo = $GLOBALS['pdo'];
            }
        }
    }

    $e = null;
    if ($pdo instanceof PDO) {
        try {
            // 1. Explicitly active banner
            $st = $pdo->query("SELECT * FROM events WHERE banner_active = 1 AND status != 'archived' ORDER BY id DESC LIMIT 1");
            $e = $st ? $st->fetch() : null;

            if (!$e) {
                $st = $pdo->query("SELECT * FROM events WHERE banner_active = 1 ORDER BY id DESC LIMIT 1");
                $e = $st ? $st->fetch() : null;
            }

            // 2. Upcoming published event
            if (!$e) {
                $st = $pdo->query("SELECT * FROM events WHERE status = 'published' AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 1");
                $e = $st ? $st->fetch() : null;
            }

            // 3. Any published event
            if (!$e) {
                $st = $pdo->query("SELECT * FROM events WHERE status = 'published' ORDER BY event_date DESC, id DESC LIMIT 1");
                $e = $st ? $st->fetch() : null;
            }
        } catch (Throwable $ex) {
            $e = null;
        }
    }

    // Fallback if DB query fails or DB is unavailable
    if (!$e) {
        $e = [
            'id' => 1,
            'title' => 'همایش ملی مراقبت‌های حمایتی و تسکینی در بیماران مبتلا به سرطان',
            'slug' => 'cancer-palliative-care-national-congress-2026',
            'event_date' => '2026-10-08',
            'start_time' => '08:00:00',
            'banner_active' => 1,
            'banner_label' => 'رویداد ویژه جاری',
            'banner_cta' => 'ثبت‌نام مستقیم',
            'banner_link' => '/event.php?slug=cancer-palliative-care-national-congress-2026',
            'banner_background' => '#0A5C66',
            'banner_text_color' => '#ffffff',
            'banner_accent_color' => '#f4a61e',
            'banner_dismissible' => 1
        ];
    }

    $rendered = true;

    $targetDate = !empty($e['event_date']) ? (string)$e['event_date'] : '2026-10-08';
    $targetTime = !empty($e['start_time']) ? substr((string)$e['start_time'], 0, 8) : '08:00:00';
    $target = $targetDate . 'T' . $targetTime . '+03:30';
    
    $link = !empty($e['banner_link']) ? $e['banner_link'] : ('/event.php?slug=' . rawurlencode((string)($e['slug'] ?? '')));
    $hex = static function($value, $fallback): string {
        $value = strtolower(trim((string)$value));
        return preg_match('/^#[0-9a-f]{6}$/', $value) ? $value : $fallback;
    };
    $bg = $hex($e['banner_background'] ?? '', ($e['banner_theme'] ?? '') === 'amber' ? '#e89a16' : (($e['banner_theme'] ?? '') === 'dark' ? '#123a3d' : '#0a5c66'));
    $text = $hex($e['banner_text_color'] ?? '', '#ffffff');
    $accent = $hex($e['banner_accent_color'] ?? '', '#f4a61e');
    $dismissible = (int)($e['banner_dismissible'] ?? 1) === 1;
    $dismiss = $dismissible ? '<button type="button" class="event-banner-dismiss" aria-label="بستن موقت بنر">×</button>' : '';

    ob_start();
    ?>
    <link rel="stylesheet" href="/assets/events/banner.css">
    <div class="event-global-banner" style="--banner-bg:<?= $bg ?>;--banner-text:<?= $text ?>;--banner-accent:<?= $accent ?>"
         data-banner-id="<?= (int)($e['id'] ?? 1) ?>" data-banner-target="<?= event_h($target) ?>" role="status">
        <?= $dismiss ?>
        <div class="event-banner-copy">
            <span class="event-banner-label"><?= event_h($e['banner_label'] ?? 'رویداد جاری') ?></span>
            <b class="event-banner-title"><?= event_h($e['title'] ?? '') ?></b>
            <span class="event-banner-count">در حال آماده‌سازی...</span>
        </div>
        <a href="<?= event_h($link) ?>" class="event-banner-cta"><?= event_h(!empty($e['banner_cta']) ? $e['banner_cta'] : 'ثبت‌نام مستقیم') ?></a>
    </div>
    <script src="/assets/events/banner.js"></script>
    <?php
    return (string)ob_get_clean();
}


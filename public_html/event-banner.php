<?php
function event_global_banner(PDO $pdo): string {
    $st = $pdo->query("SELECT * FROM events WHERE status='published' AND banner_active=1 AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 1");
    $e = $st->fetch(); if (!$e) return '';
    $target = $e['event_date'].'T'.substr((string)$e['start_time'], 0, 8).'+03:30';
    $link = $e['banner_link'] ?: '/event.php?slug='.rawurlencode($e['slug']);
    $hex = static function($value, $fallback): string { $value = strtolower(trim((string)$value)); return preg_match('/^#[0-9a-f]{6}$/', $value) ? $value : $fallback; };
    $bg = $hex($e['banner_background'] ?? '', $e['banner_theme'] === 'amber' ? '#e89a16' : ($e['banner_theme'] === 'dark' ? '#123a3d' : '#007b7a'));
    $text = $hex($e['banner_text_color'] ?? '', '#ffffff');
    $accent = $hex($e['banner_accent_color'] ?? '', '#f4a61e');
    $dismissible = (int)($e['banner_dismissible'] ?? 1) === 1;
    $dismiss = $dismissible ? '<button type="button" class="event-banner-dismiss" aria-label="بستن موقت بنر">×</button>' : '';
    ob_start();
    ?>
    <link rel="stylesheet" href="/assets/events/banner.css">
    <div class="event-global-banner" style="--banner-bg:<?= $bg ?>;--banner-text:<?= $text ?>;--banner-accent:<?= $accent ?>"
         data-banner-id="<?= (int)$e['id'] ?>" data-banner-target="<?= event_h($target) ?>" role="status">
        <div class="event-banner-copy">
            <span><?= event_h($e['banner_label']) ?></span>
            <b><?= event_h($e['title']) ?></b>
            <span class="event-banner-count">در حال آماده‌سازی...</span>
        </div>
        <a href="<?= event_h($link) ?>"><?= event_h($e['banner_cta']) ?></a>
        <?= $dismiss ?>
    </div>
    <script src="/assets/events/banner.js"></script>
    <?php
    return (string)ob_get_clean();
}

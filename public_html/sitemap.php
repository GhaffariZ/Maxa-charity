<?php
/**
 * Dynamic Sitemap Generator for MACSA Charity (https://mymacsa.ir)
 * 
 * Generates an up-to-date sitemap.xml dynamically from database content
 * (published pages, news, branches, courses, events, Macsapedia sections)
 * and core application routes, compliant with Sitemaps XML protocol 0.9.
 */
declare(strict_types=1);

// Send appropriate XML headers
header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$baseUrl = 'https://mymacsa.ir';

// Helper: Format date to ISO 8601 YYYY-MM-DD
function sitemap_format_date($dateStr): string {
    if (empty($dateStr)) {
        return date('Y-m-d');
    }
    $ts = is_numeric($dateStr) ? (int)$dateStr : strtotime((string)$dateStr);
    if (!$ts || $ts <= 0) {
        return date('Y-m-d');
    }
    return date('Y-m-d', $ts);
}

// Helper: Escape XML special characters
function sitemap_xml_escape(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

// Helper: Slugify Persian/English text
function sitemap_slugify(string $text): string {
    $text = trim($text);
    $text = (string)preg_replace('/[^a-zA-Z0-9\x{0600}-\x{06FF}\-]+/u', '-', $text);
    $text = (string)preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// Database connection
$pdo = null;
try {
    if (file_exists(__DIR__ . '/core/database.php')) {
        require_once __DIR__ . '/core/database.php';
    } elseif (file_exists(__DIR__ . '/../config/database.php')) {
        require_once __DIR__ . '/../config/database.php';
    }
} catch (Throwable $e) {
    // Graceful fallback if database connection cannot be established
    $pdo = null;
}

$urls = [];
$seen = [];

// Helper: Add URL entry to collection
$addUrl = function(string $loc, ?string $lastmod = null, string $changefreq = 'weekly', float $priority = 0.8) use (&$urls, &$seen) {
    $loc = trim($loc);
    if ($loc === '' || isset($seen[$loc])) {
        return;
    }
    $seen[$loc] = true;
    $urls[] = [
        'loc' => $loc,
        'lastmod' => sitemap_format_date($lastmod),
        'changefreq' => $changefreq,
        'priority' => number_format(max(0.1, min(1.0, $priority)), 1, '.', '')
    ];
};

// =========================================================================
// 1. Core & Hub Pages
// =========================================================================
$addUrl($baseUrl . '/', date('Y-m-d'), 'daily', 1.0);
$addUrl($baseUrl . '/courses', null, 'weekly', 0.9);
$addUrl($baseUrl . '/news.php', null, 'daily', 0.9);
$addUrl($baseUrl . '/events.php', null, 'weekly', 0.8);
$addUrl($baseUrl . '/branches.php', null, 'monthly', 0.8);
$addUrl($baseUrl . '/contactus', null, 'monthly', 0.8);
$addUrl($baseUrl . '/macsa-story', null, 'monthly', 0.8);
$addUrl($baseUrl . '/network', null, 'weekly', 0.8);
$addUrl($baseUrl . '/stand-order', null, 'monthly', 0.8);
$addUrl($baseUrl . '/headdirectors', null, 'monthly', 0.7);
$addUrl($baseUrl . '/directors', null, 'monthly', 0.7);
$addUrl($baseUrl . '/doctorspage', null, 'monthly', 0.7);

// =========================================================================
// 2. Macsapedia & Sections
// =========================================================================
$addUrl($baseUrl . '/macsapedia', null, 'weekly', 0.8);
$macsapediaSections = ['videos', 'brochures', 'books', 'podcasts', 'clips', 'gallery'];
foreach ($macsapediaSections as $sec) {
    $addUrl("{$baseUrl}/macsapedia/{$sec}", null, 'weekly', 0.7);
}

// =========================================================================
// 3. Standard Branches
// =========================================================================
$standardBranches = [
    'tehran-branch', 'esfahan-branch', 'kashan-branch', 'mashhad-branch',
    'ahvaz-branch', 'tabriz-branch', 'qom-branch', 'contact-center', 'cdst'
];
foreach ($standardBranches as $br) {
    $addUrl("{$baseUrl}/{$br}", null, 'weekly', 0.8);
    $addUrl("{$baseUrl}/{$br}/about", null, 'monthly', 0.7);
    $addUrl("{$baseUrl}/{$br}/network", null, 'weekly', 0.7);
}

// =========================================================================
// 4. Dynamic Content from Database
// =========================================================================
if ($pdo instanceof PDO) {
    // 4.1 CMS Pages from 'pages' table
    try {
        $st = $pdo->query("SELECT slug, updated_at, created_at FROM pages WHERE status = 'published'");
        $excludedSlugs = [
            'home', 'userpanel', 'userpanel-1', 'csr-1', 'hellods', 'news-page',
            'stand-sell-section', 'branches', 'ikhc', 'under-construction'
        ];
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $slug = trim((string)($row['slug'] ?? ''));
            if ($slug === '' || in_array($slug, $excludedSlugs, true)) {
                continue;
            }
            $date = !empty($row['updated_at']) ? $row['updated_at'] : ($row['created_at'] ?? null);
            $priority = in_array($slug, ['onlinedonation', 'single-fundraising-option', 'hamrah'], true) ? 0.9 : 0.8;
            $addUrl("{$baseUrl}/{$slug}", $date, 'weekly', $priority);
        }
    } catch (Throwable $e) {}

    // 4.2 Dynamic Branches from 'branches' table
    try {
        $st = $pdo->query("SELECT slug, updated_at, created_at FROM branches WHERE status = 'active' AND is_hq = 0");
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $slug = trim((string)($row['slug'] ?? ''));
            if ($slug !== '') {
                $date = !empty($row['updated_at']) ? $row['updated_at'] : ($row['created_at'] ?? null);
                $addUrl("{$baseUrl}/{$slug}", $date, 'weekly', 0.8);
                $addUrl("{$baseUrl}/{$slug}/about", $date, 'monthly', 0.7);
                $addUrl("{$baseUrl}/{$slug}/network", $date, 'weekly', 0.7);
            }
        }
    } catch (Throwable $e) {}

    // 4.3 News Articles from 'news' table
    try {
        $st = $pdo->query("SELECT id, title, publish_date FROM news WHERE status = 'published' ORDER BY publish_date DESC");
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $id = (int)$row['id'];
            $title = (string)($row['title'] ?? '');
            $date = $row['publish_date'] ?? null;
            $slug = sitemap_slugify($title);
            $encodedSlug = rawurlencode($slug);
            $url = "{$baseUrl}/{$id}/" . ($encodedSlug !== '' ? "{$encodedSlug}/" : "");
            $addUrl($url, $date, 'monthly', 0.7);
        }
    } catch (Throwable $e) {}

    // 4.4 Courses from 'courses' table
    try {
        $st = $pdo->query("SELECT id, updated_at, created_at FROM courses WHERE status = 'published'");
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $id = (int)$row['id'];
            $date = !empty($row['updated_at']) ? $row['updated_at'] : ($row['created_at'] ?? null);
            $addUrl("{$baseUrl}/courses/{$id}", $date, 'weekly', 0.8);
        }
    } catch (Throwable $e) {}

    // 4.5 Events from 'events' table
    try {
        $st = $pdo->query("SELECT slug, updated_at, created_at FROM events WHERE status = 'published'");
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $slug = trim((string)($row['slug'] ?? ''));
            if ($slug !== '') {
                $date = !empty($row['updated_at']) ? $row['updated_at'] : ($row['created_at'] ?? null);
                $addUrl("{$baseUrl}/event.php?slug=" . rawurlencode($slug), $date, 'weekly', 0.7);
            }
        }
    } catch (Throwable $e) {}
}

// =========================================================================
// 5. XML Output Generation
// =========================================================================
$xml = [];
$xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
$xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($urls as $u) {
    $xml[] = '  <url>';
    $xml[] = '    <loc>' . sitemap_xml_escape($u['loc']) . '</loc>';
    if (!empty($u['lastmod'])) {
        $xml[] = '    <lastmod>' . sitemap_xml_escape($u['lastmod']) . '</lastmod>';
    }
    if (!empty($u['changefreq'])) {
        $xml[] = '    <changefreq>' . sitemap_xml_escape($u['changefreq']) . '</changefreq>';
    }
    if (!empty($u['priority'])) {
        $xml[] = '    <priority>' . sitemap_xml_escape($u['priority']) . '</priority>';
    }
    $xml[] = '  </url>';
}

$xml[] = '</urlset>';
$output = implode("\n", $xml);

// Attempt to keep static sitemap.xml refreshed if writable
@file_put_contents(__DIR__ . '/sitemap.xml', $output);

echo $output;

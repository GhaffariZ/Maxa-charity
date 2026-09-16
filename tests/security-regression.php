<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/html-sanitizer.php';
require_once __DIR__ . '/../public_html/core/component-renderer.php';
require_once __DIR__ . '/../public_html/api/src/Services/StandCatalog.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$article = HtmlSanitizer::sanitize(
    '<p onclick="alert(1)">safe</p><img src="javascript:alert(1)" onerror="alert(1)" data-mce-x="1">'
    . '<script>alert(1)</script><a href="https://example.org">link</a>'
);
check(!str_contains(strtolower($article), 'onclick'), 'Event handler survived article sanitization.');
check(!str_contains(strtolower($article), 'onerror'), 'Image event handler survived article sanitization.');
check(!str_contains(strtolower($article), 'javascript:'), 'Unsafe URL survived article sanitization.');
check(!str_contains(strtolower($article), 'data-mce-'), 'TinyMCE internal attribute survived sanitization.');
check(!str_contains(strtolower($article), '<script'), 'Script survived article sanitization.');
check(str_contains($article, 'https://example.org'), 'Safe HTTPS link was removed.');

$component = HtmlSanitizer::sanitizeComponent(
    '<style>.card{color:red}</style><section class="card"><details open><summary>title</summary>ok</details></section><?php system($_GET["x"]); ?>'
    . '<script>alert(1)</script>'
);
check(str_contains($component, '.card{color:red}'), 'Safe component CSS was removed.');
check(str_contains($component, '<section'), 'Safe semantic component markup was removed.');
check(!str_contains(strtolower($component), '<?php'), 'PHP survived component sanitization.');
check(!str_contains(strtolower($component), '<script'), 'Script survived component sanitization.');

$unsafeCss = HtmlSanitizer::sanitizeComponent(
    '<style>.x{background:url(javascript:alert(1))}</style><div>ok</div>'
);
check(!str_contains(strtolower($unsafeCss), 'url('), 'External CSS primitive survived sanitization.');

$assetCss = HtmlSanitizer::sanitizeComponent(
    '<style>@font-face{src:url("/webfont/font.woff2")}.x{color:red}</style>'
    . '<section style="background-image:url(/images/bg.webp);color:red">ok</section>'
);
check(str_contains($assetCss, '/webfont/font.woff2'), 'Safe relative component asset URL was removed.');
check(str_contains($assetCss, '/images/bg.webp'), 'Safe relative inline-style URL was removed.');

$obfuscatedCss = HtmlSanitizer::sanitizeComponent(
    '<style>@im\\port url(https://attacker.example/x.css);</style><div style="back\\ground:url(javascript:alert(1))">ok</div>'
);
check(!str_contains($obfuscatedCss, 'attacker.example'), 'Escaped CSS import survived sanitization.');
check(!str_contains($obfuscatedCss, 'back\\ground'), 'Escaped CSS property survived sanitization.');

check(dashboard_component_name('../header') === null, 'Component path traversal was accepted.');
check(dashboard_component_name('header') === 'header', 'Valid legacy component was rejected.');
check(dashboard_component_name('فونت') === 'فونت', 'Valid Unicode component name was rejected.');

$legacyFile = __DIR__ . '/../public_html/dashboard/components/فونت/component.php';
ob_start();
include $legacyFile;
$directLegacy = (string)ob_get_clean();
ob_start();
render_dashboard_component('فونت', get_defined_vars());
$renderedLegacy = (string)ob_get_clean();
check($renderedLegacy === $directLegacy, 'Immutable legacy component rendering changed.');

$design = \Maksa\Services\StandCatalog::resolve('design1.jpg');
check($design !== null && $design['unit_price'] === 150000, 'Authoritative stand catalog changed unexpectedly.');
check(\Maksa\Services\StandCatalog::resolve('../../invalid.php') === null, 'Invalid stand design was accepted.');

require_once __DIR__ . '/../public_html/api/src/Core/Database.php';
require_once __DIR__ . '/../public_html/api/src/Repositories/DonationRepository.php';

$sqlite = new PDO('sqlite::memory:');
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlite->sqliteCreateFunction('UTC_TIMESTAMP', static fn(): string => gmdate('Y-m-d H:i:s'));
$sqlite->exec('CREATE TABLE panel_donations (
    id INTEGER PRIMARY KEY, status TEXT NOT NULL, gateway_ref_id TEXT,
    gateway_track_id TEXT, receipt_number TEXT, paid_at TEXT
)');
$sqlite->exec("INSERT INTO panel_donations (id, status) VALUES (1, 'pending')");
$databaseProperty = new ReflectionProperty(\Maksa\Core\Database::class, 'pdo');
$databaseProperty->setAccessible(true);
$databaseProperty->setValue(null, $sqlite);
$donations = new \Maksa\Repositories\DonationRepository();
$firstClaim = $donations->markSuccess(1, 'gateway-ref', 'track');
$secondClaim = $donations->markSuccess(1, 'gateway-ref', 'track');
check($firstClaim['row_count'] === 1, 'First payment callback did not claim the donation.');
check($secondClaim['row_count'] === 0, 'Duplicate payment callback claimed the donation twice.');
check($donations->statusById(1) === 'success', 'Claimed donation did not persist success status.');
$databaseProperty->setValue(null, null);

echo "security-regression: OK\n";

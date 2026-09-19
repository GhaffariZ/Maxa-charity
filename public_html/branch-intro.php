<?php
/* ============================================================================
 *  صفحه‌ی عمومی معرفی شعبه (ورودی مستقیم با هدر و فوتر استاندارد)
 * ========================================================================== */

require_once __DIR__ . '/core/database.php';

$slug = trim((string)($_GET['branch'] ?? ($_GET['slug'] ?? '')));

if ($slug === '') {
    // در صورت نبود شعبه مشخص به فهرست شعب هدایت می‌شود
    header('Location: /branches.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM branches WHERE slug = ? AND status = 'active' LIMIT 1");
$stmt->execute([$slug]);
$branch = $stmt->fetch();

if (!$branch) {
    http_response_code(404);
    include __DIR__ . '/404.html';
    exit;
}

$branchSlug = (string)$branch['slug'];
$branchName = (string)$branch['name'];
$pageTitle = 'معرفی شعبه ' . $branchName . ' | خیریه مکسا';

echo '<script>window.__MAXA_BRANCH__=' . json_encode($branchSlug, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
   . ';window.__MAXA_BRANCH_NAME__=' . json_encode($branchName, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . ";</script>\n";

require_once __DIR__ . '/dashboard/components/header/component.php';
require_once __DIR__ . '/dashboard/components/branch-intro/component.php';
require_once __DIR__ . '/dashboard/components/footer/component.php';

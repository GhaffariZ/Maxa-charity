<?php
require_once __DIR__.'/../core/content-locales.php';
/* ============================================================================
 *  فید عمومیِ کمپین‌های مالی از Campaign Service.
 *  ایزولاسیون: با ?branch=<slug> مرکز همان شعبه؛ بدون آن، ستاد مرکزی (hq).
 * ========================================================================== */
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

try {
    $DB = require __DIR__ . '/../core/db-config.php';
    $pdo = new PDO(
        "mysql:host={$DB['host']};dbname={$DB['name']};charset={$DB['charset']}",
        $DB['user'], $DB['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    require_once __DIR__ . '/../core/campaign-service.php';
    if(content_language()!=='fa'){
        $has=content_ready($pdo)?$pdo->prepare("SELECT 1 FROM public_content_locales WHERE content_type='campaigns' AND locale=? AND enabled=1 LIMIT 1"):null;
        if($has)$has->execute([content_language()]);
        if(!$has||!$has->fetchColumn()){echo json_encode(['status'=>'empty','data'=>[]]);exit;}
    }


    // تعیین شعبه از روی slug (وگرنه HQ)
    $slug = isset($_GET['branch']) ? trim(strtolower((string)$_GET['branch'])) : '';
    if ($slug !== '') {
        $slug = campaign_service_normalize_slug($slug);
    }
    $branch = null;
    if ($slug !== '') {
        $bs = $pdo->prepare("SELECT id, name, slug FROM branches WHERE slug = ? AND status = 'active' LIMIT 1");
        $bs->execute([$slug]);
        $branch = $bs->fetch() ?: null;
    } else {
        $bs = $pdo->query("SELECT id, name, slug FROM branches WHERE is_hq = 1 AND status = 'active' LIMIT 1");
        $branch = $bs->fetch() ?: null;
    }
    if (!$branch) {
        throw new RuntimeException('مرکز انتخاب‌شده یافت نشد.');
    }

    $branch=content_branch_context($pdo,$branch);
    $remote = (new CampaignServiceClient((string)$branch['slug']))->publicCampaigns();
    $campaigns = array_values(array_filter(array_map(
        static fn(array $campaign): array => campaign_service_present($campaign, $branch),
        $remote
    ), static fn(array $campaign): bool => (int)$campaign['is_active'] === 1));

    $campaigns=content_rows($pdo,'campaigns',$campaigns);
    if (count($campaigns) > 0) {
        echo json_encode(["status" => "success", "data" => $campaigns], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "empty", "message" => "کمپینی برای نمایش یافت نشد.", "data" => []], JSON_UNESCAPED_UNICODE);
    }

} catch (Throwable $e) {
    http_response_code(502);
    echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

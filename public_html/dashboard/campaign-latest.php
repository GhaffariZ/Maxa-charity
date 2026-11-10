<?php
require_once __DIR__.'/../core/content-locales.php';
/* Latest public campaign for one active center, with a short shared cache. */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=120, stale-while-revalidate=300');
ini_set('display_errors', '0');

function latest_campaign_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $DB = require __DIR__ . '/../core/db-config.php';
    $pdo = new PDO(
        "mysql:host={$DB['host']};dbname={$DB['name']};charset={$DB['charset']}",
        $DB['user'],
        $DB['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    require_once __DIR__ . '/../core/campaign-service.php';
    if(content_language()!=='fa'){
        $has=content_ready($pdo)?$pdo->prepare("SELECT 1 FROM public_content_locales WHERE content_type='campaigns' AND locale=? AND enabled=1 LIMIT 1"):null;
        if($has)$has->execute([content_language()]);
        if(!$has||!$has->fetchColumn()){echo json_encode(['status'=>'empty','data'=>null]);exit;}
    }


    $slug = campaign_service_normalize_slug((string)($_GET['branch'] ?? ''));
    $statement = $pdo->prepare("SELECT id, name, slug, is_hq FROM branches WHERE slug = ? AND status = 'active' LIMIT 1");
    $statement->execute([$slug]);
    $branch = $statement->fetch() ?: null;
    if (!$branch) {
        latest_campaign_json(['status' => 'error', 'message' => 'مرکز انتخاب‌شده یافت نشد.'], 404);
    }

    $branch=content_branch_context($pdo,$branch);
    $revision='';
    if(content_ready($pdo)){
        $versions=$pdo->prepare("SELECT content_id,enabled,payload FROM public_content_locales WHERE content_type IN ('campaigns','branches') AND locale=? ORDER BY content_type,content_id");
        $versions->execute([content_language()]);$revision=hash('sha256',json_encode($versions->fetchAll()));
    }
    $cacheFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR . 'macsa-latest-campaign-v4-' . hash('sha256', $slug.'/'.content_language().'/'.$revision) . '.json';
    $readCache = static function (string $path): ?array {
        if (!is_file($path)) {
            return null;
        }
        $decoded = json_decode((string)@file_get_contents($path), true);
        return is_array($decoded) && isset($decoded['cached_at'], $decoded['payload']) && is_array($decoded['payload'])
            ? $decoded
            : null;
    };

    $cached = $readCache($cacheFile);
    if ($cached && (int)$cached['cached_at'] >= time() - 120) {
        latest_campaign_json($cached['payload']);
    }

    $lock = @fopen($cacheFile . '.lock', 'c');
    if (is_resource($lock)) {
        @flock($lock, LOCK_EX);
        $cached = $readCache($cacheFile);
        if ($cached && (int)$cached['cached_at'] >= time() - 120) {
            @flock($lock, LOCK_UN);
            @fclose($lock);
            latest_campaign_json($cached['payload']);
        }
    }

    try {
        $excludedIds = [];
        if ((int)($branch['is_hq'] ?? 0) === 1) {
            campaign_service_load_env();
            $onlineDonationId = campaign_service_env('CAMPAIGN_HQ_ONLINE_DONATION_CAMPAIGN_ID');
            if ($onlineDonationId !== '') {
                $excludedIds[] = $onlineDonationId;
            }
        }
        $client=new CampaignServiceClient($slug);
        $remote=content_language()==='fa'?$client->latestPublicCampaign($excludedIds):null;
        $campaign=$remote?content_row($pdo,'campaigns',campaign_service_present($remote,$branch)):null;
        if(content_language()!=='fa'||($remote&&!$campaign)){
            $items=(new CampaignServiceClient($slug))->publicCampaigns();
            $items=array_map(static fn($row)=>campaign_service_present($row,$branch),$items);
            $items=array_values(array_filter(content_rows($pdo,'campaigns',$items),static fn($row)=>!in_array((string)$row['id'],$excludedIds,true)));
            usort($items,static fn($a,$b)=>strcmp((string)($b['created_at']??''),(string)($a['created_at']??'')));
            $campaign=$items[0]??null;
        }
        $payload = $campaign
            ? ['status' => 'success', 'data' => $campaign]
            : ['status' => 'empty', 'data' => null];

        $temporary = $cacheFile . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (@file_put_contents($temporary, json_encode([
            'cached_at' => time(),
            'payload' => $payload,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) !== false) {
            @rename($temporary, $cacheFile);
        }
    } catch (Throwable $remoteError) {
        if ($cached && (int)$cached['cached_at'] >= time() - 3600) {
            $payload = $cached['payload'];
            $payload['stale'] = true;
        } else {
            throw $remoteError;
        }
    } finally {
        if (is_resource($lock)) {
            @flock($lock, LOCK_UN);
            @fclose($lock);
        }
    }

    latest_campaign_json($payload);
} catch (Throwable $error) {
    error_log('[campaign-latest] ' . get_class($error) . ': ' . $error->getMessage());
    latest_campaign_json(['status' => 'error', 'message' => 'دریافت کمپین این مرکز در حال حاضر ممکن نیست.'], 502);
}

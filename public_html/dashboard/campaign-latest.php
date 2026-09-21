<?php
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

    $slug = campaign_service_normalize_slug((string)($_GET['branch'] ?? ''));
    $statement = $pdo->prepare("SELECT id, name, slug, is_hq FROM branches WHERE slug = ? AND status = 'active' LIMIT 1");
    $statement->execute([$slug]);
    $branch = $statement->fetch() ?: null;
    if (!$branch) {
        latest_campaign_json(['status' => 'error', 'message' => 'مرکز انتخاب‌شده یافت نشد.'], 404);
    }

    $cacheFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR . 'macsa-latest-campaign-v2-' . hash('sha256', $slug) . '.json';
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
        $remote = (new CampaignServiceClient($slug))->latestPublicCampaign($excludedIds);
        $campaign = $remote ? campaign_service_present($remote, $branch) : null;
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

<?php

declare(strict_types=1);

/**
 * Small server-side adapter for the multi-tenant Campaign Service.
 *
 * Credentials are resolved per branch slug.  For example `tabriz-branch`
 * reads CAMPAIGN_CENTER_TABRIZ_BRANCH_CLIENT_ID / CLIENT_SECRET / PUBLIC_KEY.
 * Secrets never need to be exposed to browser JavaScript.
 */

final class CampaignServiceException extends RuntimeException
{
    public int $status;
    public ?string $errorCode;
    public ?string $requestId;

    public function __construct(string $message, int $status = 0, ?string $errorCode = null, ?string $requestId = null)
    {
        parent::__construct($message);
        $this->status = $status;
        $this->errorCode = $errorCode;
        $this->requestId = $requestId;
    }
}

final class CampaignServiceClient
{
    private string $baseUrl;
    private string $centerSlug;
    private string $clientId;
    private string $clientSecret;
    private string $publicKey;
    private ?string $managementAccessToken = null;

    public function __construct(string $centerSlug)
    {
        campaign_service_load_env();
        $this->baseUrl = rtrim(campaign_service_env('CAMPAIGN_API_BASE_URL', 'https://campaigns.bonyadala.ir'), '/');
        $this->centerSlug = campaign_service_normalize_slug($centerSlug);
        $prefix = campaign_service_center_prefix($this->centerSlug);
        $this->clientId = campaign_service_env($prefix . 'CLIENT_ID');
        $this->clientSecret = campaign_service_env($prefix . 'CLIENT_SECRET');
        $this->publicKey = campaign_service_env($prefix . 'PUBLIC_KEY');
    }

    /** @return array<string,mixed> */
    public function publicCampaigns(bool $includeInactive = false): array
    {
        $query = ['limit' => 100, 'participation_type' => 'FINANCIAL'];
        if (!$includeInactive) {
            $query['status'] = 'ACTIVE';
        }
        return $this->collectPages('/public/v1/campaigns', $query, false);
    }

    /** @return array<string,mixed> */
    public function publicCampaign(string $id): array
    {
        return $this->entity($this->request('GET', '/public/v1/campaigns/' . rawurlencode($id)));
    }

    /** @return array<string,mixed> */
    public function managementCampaigns(): array
    {
        return $this->collectPages('/api/v1/campaigns', [
            'limit' => 100,
            'participation_type' => 'FINANCIAL',
            'sort' => 'created_at',
            'direction' => 'desc',
        ], true);
    }

    /**
     * Resolve and cache the management token while the PHP session is still
     * writable. The caller may then release the session lock before slow API
     * requests without forcing a new authentication request.
     */
    public function prepareManagement(): void
    {
        $this->managementToken();
    }

    /** @return array<string,mixed> */
    public function createCampaign(array $payload): array
    {
        return $this->entity($this->request('POST', '/api/v1/campaigns', $payload, true));
    }

    /** @return array<string,mixed> */
    public function updateCampaign(string $id, array $payload): array
    {
        return $this->entity($this->request('PATCH', '/api/v1/campaigns/' . rawurlencode($id), $payload, true));
    }

    public function ensureCategory(string $name): string
    {
        $categories = $this->collectPages('/api/v1/campaign-categories', ['limit' => 100], true);
        foreach ($categories as $category) {
            if (isset($category['id'], $category['name']) && trim((string)$category['name']) === $name) {
                return (string)$category['id'];
            }
        }
        $created = $this->entity($this->request('POST', '/api/v1/campaign-categories', ['name' => $name], true));
        if (empty($created['id'])) {
            throw new CampaignServiceException('شناسه دسته‌بندی از سرویس دریافت نشد.');
        }
        return (string)$created['id'];
    }

    /** @return array<string,mixed> */
    public function uploadImage(string $path, string $originalName, string $mimeType): array
    {
        if (!function_exists('curl_init') || !class_exists('CURLFile')) {
            throw new CampaignServiceException('افزونه cURL برای آپلود تصویر روی سرور فعال نیست.');
        }
        $token = $this->managementToken();
        $response = $this->curl('POST', '/api/v1/files', [
            'file' => new CURLFile($path, $mimeType, $originalName),
        ], [
            'Authorization: Bearer ' . $token,
        ], true);
        return $this->entity($response);
    }

    /** @return array<string,mixed> */
    public function startPayment(string $campaignId, string $mobile, string $fullName, int $amountIrr, string $idempotencyKey): array
    {
        return $this->entity($this->request(
            'POST',
            '/public/v1/campaigns/' . rawurlencode($campaignId) . '/payments',
            ['mobile' => $mobile, 'full_name' => $fullName, 'amount' => $amountIrr],
            false,
            ['Idempotency-Key: ' . $idempotencyKey]
        ));
    }

    /** @return array<string,mixed> */
    public function paymentStatus(string $paymentId): array
    {
        return $this->entity($this->request('GET', '/public/v1/payments/' . rawurlencode($paymentId) . '/status'));
    }

    /** @return list<array<string,mixed>> */
    private function collectPages(string $path, array $query, bool $management): array
    {
        $items = [];
        $cursor = null;
        do {
            $pageQuery = $query;
            if ($cursor !== null) {
                $pageQuery['cursor'] = $cursor;
            }
            $response = $this->request('GET', $path . '?' . http_build_query($pageQuery), null, $management);
            foreach (($response['data'] ?? []) as $item) {
                if (is_array($item)) {
                    $items[] = $item;
                }
            }
            $next = $response['page']['next_cursor'] ?? null;
            $cursor = is_string($next) && $next !== '' ? $next : null;
        } while ($cursor !== null);
        return $items;
    }

    /** @return array<string,mixed> */
    private function request(string $method, string $path, ?array $payload = null, bool $management = false, array $headers = []): array
    {
        if ($management) {
            $headers[] = 'Authorization: Bearer ' . $this->managementToken();
        } else {
            if ($this->publicKey === '') {
                throw new CampaignServiceException('کلید عمومی کمپین‌ساز برای مرکز «' . $this->centerSlug . '» تنظیم نشده است.');
            }
            $headers[] = 'X-Public-API-Key: ' . $this->publicKey;
        }
        return $this->curl($method, $path, $payload, $headers, false);
    }

    private function managementToken(): string
    {
        if ($this->managementAccessToken !== null && $this->managementAccessToken !== '') {
            return $this->managementAccessToken;
        }
        if ($this->clientId === '' || $this->clientSecret === '') {
            throw new CampaignServiceException('اعتبارنامه مدیریتی کمپین‌ساز برای مرکز «' . $this->centerSlug . '» تنظیم نشده است.');
        }
        $cacheKey = 'campaign_service_token_' . hash('sha256', $this->centerSlug . '|' . $this->clientId);
        if (session_status() === PHP_SESSION_ACTIVE) {
            $cached = $_SESSION[$cacheKey] ?? null;
            if (is_array($cached) && !empty($cached['token']) && (int)($cached['expires_at'] ?? 0) > time() + 30) {
                $this->managementAccessToken = (string)$cached['token'];
                return $this->managementAccessToken;
            }
        }
        $response = $this->curl('POST', '/api/v1/auth/client/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ], [], false);
        $data = $this->entity($response);
        $token = (string)($data['access_token'] ?? '');
        if ($token === '') {
            throw new CampaignServiceException('توکن مدیریتی از کمپین‌ساز دریافت نشد.');
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[$cacheKey] = [
                'token' => $token,
                'expires_at' => time() + max(60, (int)($data['expires_in'] ?? 900)),
            ];
        }
        $this->managementAccessToken = $token;
        return $this->managementAccessToken;
    }

    /** @return array<string,mixed> */
    private function curl(string $method, string $path, mixed $payload, array $headers, bool $multipart): array
    {
        if (!function_exists('curl_init')) {
            throw new CampaignServiceException('افزونه cURL روی سرور فعال نیست.');
        }
        $ch = curl_init($this->baseUrl . $path);
        if ($ch === false) {
            throw new CampaignServiceException('ساخت درخواست کمپین‌ساز ناموفق بود.');
        }
        $headers[] = 'Accept: application/json';
        $headers[] = 'X-Request-Id: macsa-' . bin2hex(random_bytes(12));
        if (!$multipart && $payload !== null) {
            $headers[] = 'Content-Type: application/json';
            $payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($raw === false) {
            throw new CampaignServiceException('ارتباط با کمپین‌ساز برقرار نشد: ' . $curlError);
        }
        $decoded = json_decode((string)$raw, true);
        if (!is_array($decoded)) {
            throw new CampaignServiceException('پاسخ نامعتبر از کمپین‌ساز دریافت شد.', $status);
        }
        if ($status < 200 || $status >= 300) {
            $error = is_array($decoded['error'] ?? null) ? $decoded['error'] : [];
            throw new CampaignServiceException(
                campaign_service_error_message((string)($error['message'] ?? 'عملیات کمپین‌ساز ناموفق بود.')),
                $status,
                isset($error['code']) ? (string)$error['code'] : null,
                isset($error['request_id']) ? (string)$error['request_id'] : null
            );
        }
        return $decoded;
    }

    /** @return array<string,mixed> */
    private function entity(array $response): array
    {
        $data = $response['data'] ?? $response;
        if (!is_array($data)) {
            throw new CampaignServiceException('ساختار پاسخ کمپین‌ساز نامعتبر است.');
        }
        return $data;
    }
}

function campaign_service_normalize_slug(string $slug): string
{
    $slug = strtolower(trim($slug));
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        throw new CampaignServiceException('شناسه مرکز نامعتبر است.');
    }
    return $slug;
}

function campaign_service_center_prefix(string $slug): string
{
    return 'CAMPAIGN_CENTER_' . strtoupper(str_replace('-', '_', $slug)) . '_';
}

function campaign_service_is_uuid(string $value): bool
{
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
}

function campaign_service_env(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value !== false && trim((string)$value) !== '') {
        return trim((string)$value, " \t\n\r\0\x0B\"'");
    }
    return $default;
}

function campaign_service_load_env(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;
    $candidates = [];
    $directory = __DIR__;

    for ($level = 0; $level < 10; $level++) {
        $candidates[] = $directory . '/maksa-private/.env';

        $parent = dirname($directory);

        if ($parent === $directory) {
            break;
        }

        $directory = $parent;
    }

    $candidates[] = dirname(__DIR__) . '/.env';
    foreach ($candidates as $path) {
        if (!is_file($path) || !is_readable($path)) {
            continue;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (str_starts_with(ltrim($line), '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if (!preg_match('/^[A-Z_][A-Z0-9_]*$/', $key) || getenv($key) !== false) {
                continue;
            }
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

/** @return array<string,mixed> */
function campaign_service_present(array $campaign, array $branch): array
{
    $goalIrr = (int)($campaign['goalAmount'] ?? 0);
    $raisedIrr = (int)($campaign['raised_amount'] ?? 0);
    $effective = strtoupper((string)($campaign['effective_status'] ?? $campaign['status'] ?? 'INACTIVE'));
    $categoryName = trim((string)($campaign['category']['name'] ?? ''));
    $categoryMap = ['غذا' => 'food', 'دارو' => 'drug', 'مهارت‌آموزی' => 'education', 'مهارت آموزی' => 'education'];

    return [
        'id' => (string)($campaign['id'] ?? ''),
        'campaign_code' => (string)($campaign['id'] ?? ''),
        'title' => (string)($campaign['title'] ?? ''),
        'short_description' => (string)($campaign['shortDescription'] ?? ''),
        'description' => (string)($campaign['description'] ?? $campaign['shortDescription'] ?? ''),
        'category' => $categoryMap[$categoryName] ?? 'all',
        'category_name' => $categoryName,
        'target_amount' => intdiv($goalIrr, 10),
        'collected_amount' => intdiv($raisedIrr, 10),
        'image_url' => $campaign['image_url'] ?? null,
        'is_active' => $effective === 'ACTIVE' ? 1 : 0,
        'status' => $effective,
        'branch_name' => (string)($branch['name'] ?? ''),
        'branch_slug' => (string)($branch['slug'] ?? ''),
    ];
}

function campaign_service_category_name(string $code): string
{
    return match ($code) {
        'drug' => 'دارو',
        'education' => 'مهارت‌آموزی',
        default => 'غذا',
    };
}

function campaign_service_error_message(string $message): string
{
    return match ($message) {
        'Invalid client credentials' => 'اعتبارنامه مرکز در کمپین‌ساز معتبر نیست.',
        'Invalid public API key' => 'کلید عمومی مرکز در کمپین‌ساز معتبر نیست.',
        'Campaign not found' => 'کمپین در مرکز انتخاب‌شده یافت نشد.',
        'Campaign does not accept new participations' => 'این کمپین در حال حاضر حمایت جدید نمی‌پذیرد.',
        'Payment gateway initialization failed' => 'اتصال به درگاه پرداخت ناموفق بود.',
        default => $message,
    };
}

<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

require_once __DIR__ . '/../core/campaign-service.php';

function payment_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $input = $method === 'POST'
        ? json_decode((string)file_get_contents('php://input'), true)
        : $_GET;
    if (!is_array($input)) {
        payment_json(['status' => 'error', 'message' => 'درخواست نامعتبر است.'], 400);
    }

    $centerSlug = campaign_service_normalize_slug((string)($input['center_slug'] ?? 'hq'));
    $DB = require __DIR__ . '/../core/db-config.php';
    $pdo = new PDO(
        "mysql:host={$DB['host']};dbname={$DB['name']};charset={$DB['charset']}",
        $DB['user'],
        $DB['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $centerCheck = $pdo->prepare("SELECT 1 FROM branches WHERE slug = ? AND status = 'active' LIMIT 1");
    $centerCheck->execute([$centerSlug]);
    if (!$centerCheck->fetchColumn()) {
        payment_json(['status' => 'error', 'message' => 'مرکز انتخاب‌شده معتبر نیست.'], 404);
    }

    $client = new CampaignServiceClient($centerSlug);
    if ($method === 'GET') {
        $paymentId = trim((string)($input['payment_id'] ?? ''));
        if (!campaign_service_is_uuid($paymentId)) {
            payment_json(['status' => 'error', 'message' => 'شناسه پرداخت نامعتبر است.'], 400);
        }
        payment_json(['status' => 'success', 'data' => $client->paymentStatus($paymentId)]);
    }

    if ($method !== 'POST') {
        payment_json(['status' => 'error', 'message' => 'متد درخواست مجاز نیست.'], 405);
    }

    $campaignId = trim((string)($input['campaign_id'] ?? ''));
    if ($campaignId === '' && $centerSlug === 'hq') {
        campaign_service_load_env();
        $campaignId = campaign_service_env('CAMPAIGN_HQ_ONLINE_DONATION_CAMPAIGN_ID');
    }
    if (!campaign_service_is_uuid($campaignId)) {
        payment_json(['status' => 'error', 'message' => 'کمپین کمک آنلاین برای این مرکز تنظیم نشده است.'], 422);
    }

    $mobile = preg_replace('/[^0-9۰-۹٠-٩+]/u', '', (string)($input['mobile'] ?? ''));
    $mobile = strtr($mobile, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
    if (!preg_match('/^(?:\+98|0098|98|0)?9\d{9}$/', $mobile)) {
        payment_json(['status' => 'error', 'message' => 'شماره همراه معتبر وارد کنید.'], 422);
    }
    $fullName = trim(strip_tags((string)($input['full_name'] ?? '')));
    if (mb_strlen($fullName) < 2) {
        payment_json(['status' => 'error', 'message' => 'نام و نام خانوادگی را وارد کنید.'], 422);
    }
    if (mb_strlen($fullName) > 200) {
        $fullName = mb_substr($fullName, 0, 200);
    }
    if (array_key_exists('amount_rial', $input)) {
        $amountRial = filter_var($input['amount_rial'], FILTER_VALIDATE_INT);
    } else {
        $amountToman = filter_var($input['amount_toman'] ?? null, FILTER_VALIDATE_INT);
        $amountRial = $amountToman === false ? false : $amountToman * 10;
    }
    if ($amountRial === false || $amountRial < 10000 || $amountRial > 5000000000 || $amountRial % 10 !== 0) {
        payment_json(['status' => 'error', 'message' => 'مبلغ کمک معتبر نیست.'], 422);
    }
    $idempotencyKey = trim((string)($input['idempotency_key'] ?? ''));
    if (!preg_match('/^[A-Za-z0-9._:-]{8,200}$/', $idempotencyKey)) {
        payment_json(['status' => 'error', 'message' => 'شناسه یکتای پرداخت نامعتبر است.'], 400);
    }

    $payment = $client->startPayment($campaignId, $mobile, $fullName, $amountRial, $idempotencyKey);
    payment_json(['status' => 'success', 'data' => $payment], 201);
} catch (CampaignServiceException $e) {
    $status = $e->status >= 400 && $e->status < 500 ? $e->status : 502;
    payment_json([
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $e->errorCode,
        'request_id' => $e->requestId,
    ], $status);
} catch (Throwable $e) {
    error_log('[campaign-payment] ' . $e->getMessage());
    payment_json(['status' => 'error', 'message' => 'ارتباط با سامانه پرداخت ناموفق بود.'], 500);
}

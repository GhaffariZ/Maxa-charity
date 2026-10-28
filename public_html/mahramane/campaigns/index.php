<?php
declare(strict_types=1);

// Stateless merchant bridge. Campaign Service owns the payment and participation.
// No donor details, credentials or cookies are placed in the browser URL.
ini_set('display_errors', '0');
header('Cache-Control: no-store, max-age=0');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');
require_once dirname(__DIR__, 2) . '/core/campaign-service.php';
campaign_service_load_env();

function campaign_bridge_setting(string $key): string {
    $value = campaign_service_env($key);
    if ($value === '') throw new RuntimeException('CONFIG_MISSING');
    return $value;
}
function campaign_bridge_sign(string $value): string {
    $secret = campaign_bridge_setting('MAHRAMANE_BRIDGE_SECRET');
    if (strlen($secret) < 32) throw new RuntimeException('CONFIG_MISSING');
    return hash_hmac('sha256', $value, $secret);
}
function campaign_bridge_encode(array $value): string {
    return rtrim(strtr(base64_encode(json_encode($value, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
}
function campaign_bridge_https(string $value): bool {
    $parts = parse_url($value);
    return is_array($parts) && ($parts['scheme'] ?? '') === 'https' && !empty($parts['host']) && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['fragment']);
}
function campaign_bridge_reply(?string $nonce, array $data, int $status = 200): never {
    http_response_code($status);
    $raw = json_encode(['nonce' => $nonce] + $data, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    header('Content-Type: application/json');
    header('X-Mahramane-Signature: ' . campaign_bridge_sign($raw));
    echo $raw;
    exit;
}
function campaign_bridge_payment(array $payment): array {
    $id = (string)($payment['id'] ?? '');
    if (!campaign_service_is_uuid($id) || !isset($payment['amount'], $payment['status'])) throw new RuntimeException('INVALID_PAYMENT');
    $result = ['id' => $id, 'amount' => $payment['amount'], 'status' => $payment['status'],
        'gatewayReference' => $payment['gatewayReference'] ?? null, 'paidAt' => $payment['paidAt'] ?? null];
    if (!empty($payment['redirect_url'])) {
        $url = (string)$payment['redirect_url'];
        $method = strtoupper((string)($payment['redirect_method'] ?? 'GET'));
        if (!campaign_bridge_https($url) || !in_array($method, ['GET', 'POST'], true)) throw new RuntimeException('INVALID_REDIRECT');
        $fields = $payment['redirect_fields'] ?? [];
        if (!is_array($fields) || count($fields) > 30) throw new RuntimeException('INVALID_REDIRECT');
        foreach ($fields as $key => $value) if (!is_string($key) || !is_string($value) || strlen($value) > 2000) throw new RuntimeException('INVALID_REDIRECT');
        $result['launch'] = ['url' => $url, 'method' => $method, 'fields' => (object)$fields];
    }
    return $result;
}

$nonce = null;
$returnUrl = '';
try {
    $returnUrl = campaign_bridge_setting('MAHRAMANE_CAMPAIGN_CALLBACK_URL');
    $bridgeUrl = campaign_service_env('MAHRAMANE_CAMPAIGN_BRIDGE_URL', 'https://macsa.ir/mahramane/campaigns/');
    if (!campaign_bridge_https($returnUrl) || !str_ends_with($returnUrl, '/api/v1/campaigns/payments/callback') || !campaign_bridge_https($bridgeUrl) || str_contains($bridgeUrl, '?')) throw new RuntimeException('CONFIG_MISSING');
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['callback'])) {
        $raw = file_get_contents('php://input', false, null, 0, 16385);
        if (!is_string($raw) || strlen($raw) > 16384 || !hash_equals(campaign_bridge_sign($raw), (string)($_SERVER['HTTP_X_MAHRAMANE_SIGNATURE'] ?? ''))) campaign_bridge_reply(null, ['error' => 'INVALID_SIGNATURE'], 403);
        $data = json_decode($raw, true, 24, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !preg_match('/^[a-f0-9]{48}$/D', (string)($data['nonce'] ?? ''))) throw new RuntimeException('INVALID_REQUEST');
        $nonce = $data['nonce'];
        if (!is_int($data['at'] ?? null) || abs(time() - $data['at']) > 60 || ($data['callback'] ?? '') !== $returnUrl) campaign_bridge_reply($nonce, ['error' => 'INVALID_REQUEST'], 400);
        $client = new CampaignServiceClient('hq');
        if (($data['action'] ?? '') === 'start') {
            $attempt = (string)($data['attemptId'] ?? '');
            $campaign = (string)($data['campaignId'] ?? '');
            $phone = (string)($data['phone'] ?? '');
            $name = trim((string)($data['fullName'] ?? ''));
            $amount = $data['amountRial'] ?? null;
            if (!campaign_service_is_uuid($attempt) || !campaign_service_is_uuid($campaign) || !preg_match('/^09[0-9]{9}$/D', $phone) || $name === '' || strlen($name) > 800 || !is_int($amount) || $amount < 10000 || $amount > 2000000000 || $campaign === campaign_service_env('CAMPAIGN_HQ_ONLINE_DONATION_CAMPAIGN_ID')) campaign_bridge_reply($nonce, ['error' => 'INVALID_REQUEST'], 400);
            $callback = $bridgeUrl . '?' . http_build_query(['callback' => '1', 'receipt' => $attempt, 'proof' => campaign_bridge_sign('campaign-receipt:' . $attempt)]);
            $payment = $client->startPayment($campaign, $phone, $name, $amount, 'mahramane:campaign:' . $attempt, $callback);
            campaign_bridge_reply($nonce, ['result' => campaign_bridge_payment($payment)]);
        }
        if (($data['action'] ?? '') !== 'status' || !campaign_service_is_uuid((string)($data['paymentId'] ?? ''))) campaign_bridge_reply($nonce, ['error' => 'INVALID_REQUEST'], 400);
        $callback = $data['callbackData'] ?? null;
        if (is_array($callback)) {
            $gateway = (string)($callback['gateway'] ?? '');
            $fields = $callback['fields'] ?? null;
            if (!in_array($gateway, ['vandar', 'zarinpal', 'behpardakht'], true) || !is_array($fields) || count($fields) > 12) throw new RuntimeException('INVALID_CALLBACK');
            // Backend stores the signed callback durably, so transient verify errors are retried.
            $client->forwardPaymentCallback($gateway, $fields);
        }
        campaign_bridge_reply($nonce, ['result' => campaign_bridge_payment($client->paymentStatus((string)$data['paymentId']))]);
    }
    if (isset($_GET['callback']) && $_GET['callback'] === '1') {
        $receipt = (string)($_GET['receipt'] ?? '');
        if (!campaign_service_is_uuid($receipt) || !hash_equals(campaign_bridge_sign('campaign-receipt:' . $receipt), (string)($_GET['proof'] ?? ''))) throw new RuntimeException('INVALID_CALLBACK');
        $params = array_merge($_GET, $_POST);
        $gateway = isset($params['token']) ? 'vandar' : (isset($params['Authority']) ? 'zarinpal' : 'behpardakht');
        $fields = [];
        foreach (['token', 'payment_status', 'Authority', 'Status', 'RefId', 'ResCode', 'SaleOrderId', 'SaleReferenceId'] as $key) {
            if (isset($params[$key]) && is_scalar($params[$key]) && strlen((string)$params[$key]) <= 500) $fields[$key] = (string)$params[$key];
        }
        $payload = campaign_bridge_encode(['receipt' => $receipt, 'gateway' => $gateway, 'fields' => (object)$fields]);
        header('Location: ' . $returnUrl . '?' . http_build_query(['payload' => $payload, 'signature' => campaign_bridge_sign('campaign-return:' . $payload)]), true, 303);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') throw new RuntimeException('INVALID_REQUEST');
    $payload = (string)($_GET['launch'] ?? '');
    if (strlen($payload) > 12000 || !preg_match('/^[A-Za-z0-9_-]+$/D', $payload) || !hash_equals(campaign_bridge_sign('campaign-launch:' . $payload), (string)($_GET['signature'] ?? ''))) throw new RuntimeException('INVALID_LAUNCH');
    $launch = json_decode((string)base64_decode(strtr($payload, '-_', '+/'), true), true, 16, JSON_THROW_ON_ERROR);
    if (!is_array($launch) || !is_int($launch['expires'] ?? null) || $launch['expires'] < time() || $launch['expires'] > time() + 1200 || !campaign_service_is_uuid((string)($launch['paymentId'] ?? ''))) throw new RuntimeException('EXPIRED_LAUNCH');
    $url = (string)($launch['url'] ?? '');
    $method = (string)($launch['method'] ?? '');
    $fields = $launch['fields'] ?? [];
    if (!campaign_bridge_https($url) || !in_array($method, ['GET', 'POST'], true) || !is_array($fields) || count($fields) > 30) throw new RuntimeException('INVALID_LAUNCH');
    $scriptNonce = base64_encode(random_bytes(24));
    header('Content-Type: text/html; charset=utf-8');
    header('Referrer-Policy: origin');
    header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; script-src 'nonce-{$scriptNonce}'; form-action https:; base-uri 'none'; frame-ancestors 'none'");
    $escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    ?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="referrer" content="origin"><title>حمایت | محرمانه</title>
<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0c0d0e;color:#eee9df;font-family:Tahoma,sans-serif}main{max-width:440px;margin:20px;padding:32px;border:1px solid #393127;border-radius:18px;background:#151618;line-height:2}h1{font-size:22px;color:#e4c890}p{font-size:14px}button,a{display:inline-block;border:0;border-radius:10px;background:#e4c890;color:#20190f;padding:12px 22px;font:inherit;text-decoration:none;cursor:pointer}</style></head><body><main><h1>حمایت شما، ادامهٔ همراهی ما</h1><p role="status">در حال اتصال به درگاه پرداخت؛ لطفاً چند لحظه منتظر بمانید.</p>
<?php if ($method === 'POST'): ?>
<form id="payment" action="<?= $escape($url) ?>" method="post"><?php foreach ($fields as $key => $value): ?><input type="hidden" name="<?= $escape((string)$key) ?>" value="<?= $escape((string)$value) ?>"><?php endforeach; ?><button type="submit">ادامهٔ پرداخت</button></form>
<script nonce="<?= $scriptNonce ?>">document.getElementById('payment').submit();</script>
<?php else: ?><a href="<?= $escape($url) ?>" referrerpolicy="origin">ادامهٔ پرداخت</a><script nonce="<?= $scriptNonce ?>">window.location.replace(<?= json_encode($url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>);</script><?php endif; ?>
</main></body></html>
    <?php exit;
} catch (Throwable $error) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['callback'])) {
        try { campaign_bridge_reply($nonce, ['error' => 'CAMPAIGN_PAYMENT_UNAVAILABLE'], 503); } catch (Throwable) { http_response_code(503); exit; }
    }
    http_response_code(503);
}
?>
<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>حمایت | محرمانه</title><body style="background:#0c0d0e;color:#e4c890;font-family:Tahoma;padding:30px;line-height:2"><h1>انتقال انجام نشد</h1><p>به صفحهٔ حمایت در محرمانه برگردید و وضعیت همان درخواست را بررسی کنید. اگر پرداخت کرده‌اید، تا مشخص‌شدن نتیجه دوباره پرداخت نکنید.</p></body></html>

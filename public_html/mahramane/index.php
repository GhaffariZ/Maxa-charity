<?php
declare(strict_types=1);

// Stateless bridge: no database, session, cookies, filesystem writes or application logs.
ini_set('display_errors', '0');
header('Cache-Control: no-store, max-age=0');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; frame-ancestors 'none'; base-uri 'none'");
require_once dirname(__DIR__) . '/core/campaign-service.php';
campaign_service_load_env();

function setting(string $key): string {
    $value = campaign_service_env($key);
    if ($value === '') throw new RuntimeException('Configuration missing');
    return $value;
}
function signature(string $value): string {
    $secret = setting('MAHRAMANE_BRIDGE_SECRET');
    if (strlen($secret) < 32) throw new RuntimeException('Configuration invalid');
    return hash_hmac('sha256', $value, $secret);
}
function tokenValid(string $token): bool {
    return preg_match('/^[A-Za-z0-9._~-]{8,500}$/D', $token) === 1;
}
/** @return array<string,mixed> */
function gateway(string $method, array $payload): array {
    $handle = curl_init('https://ipg.vandar.io/api/v3/' . $method);
    curl_setopt_array($handle, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['api_key' => setting('MAHRAMANE_VANDAR_API_KEY')] + $payload, JSON_THROW_ON_ERROR),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
    ]);
    $raw = curl_exec($handle);
    $status = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    if (!is_string($raw) || $status >= 500 || $status === 0) throw new RuntimeException('Gateway unavailable');
    $result = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($result)) throw new RuntimeException('Gateway response invalid');
    return $result;
}
function respond(string $nonce, array $result): never {
    $raw = json_encode(['nonce' => $nonce, 'result' => $result], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    header('Content-Type: application/json');
    header('X-Mahramane-Signature: ' . signature($raw));
    echo $raw;
    exit;
}
function details(array $transaction): array {
    $result = [];
    foreach (['trackingCode' => 'trackingCode', 'refNumber' => 'refnumber', 'paymentDate' => 'paymentDate'] as $target => $source) {
        if (isset($transaction[$source])) $result[$target] = substr((string)$transaction[$source], 0, 300);
    }
    $card = (string)($transaction['cardNumber'] ?? '');
    if (preg_match('/^[0-9*Xx -]{8,30}$/D', $card) && str_contains($card, '*')) $result['maskedCard'] = $card;
    if (is_numeric($transaction['wage'] ?? null)) $result['feeRial'] = (float)$transaction['wage'];
    if (is_numeric($transaction['realAmount'] ?? null)) $result['netRial'] = (float)$transaction['realAmount'];
    return $result;
}
try {
    $returnUrl = setting('MAHRAMANE_CALLBACK_URL');
    if (!str_starts_with($returnUrl, 'https://') || !str_ends_with($returnUrl, '/api/v1/payments/callback')) throw new RuntimeException('Invalid callback configuration');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents('php://input', false, null, 0, 8193);
        if (!is_string($raw) || strlen($raw) > 8192 || !hash_equals(signature($raw), (string)($_SERVER['HTTP_X_MAHRAMANE_SIGNATURE'] ?? ''))) {
            http_response_code(403); exit;
        }
        $data = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !is_int($data['at'] ?? null) || abs(time() - $data['at']) > 60 || !preg_match('/^[a-f0-9]{48}$/D', (string)($data['nonce'] ?? '')) || ($data['callback'] ?? '') !== $returnUrl) {
            http_response_code(400); exit;
        }
        $nonce = $data['nonce'];
        $amount = $data['amountRial'] ?? null;
        $attempt = (string)($data['attemptId'] ?? '');
        if (!is_int($amount) || $amount < 1100000 || $amount > 2000000000 || !preg_match('/^[a-f0-9-]{36}$/D', $attempt)) {
            http_response_code(400); exit;
        }
        if (($data['action'] ?? '') === 'request') {
            $result = gateway('send', ['amount' => $amount, 'callback_url' => 'https://macsa.ir/mahramane/?callback=1', 'factorNumber' => $attempt, 'description' => 'حمایت از بیماران سرطانی؛ نمایش محرمانه']);
            $token = (string)($result['token'] ?? '');
            respond($nonce, ($result['status'] ?? 0) == 1 && tokenValid($token) ? ['code' => 100, 'authority' => $token] : ['code' => -1]);
        }
        if (($data['action'] ?? '') !== 'verify' || !tokenValid((string)($data['authority'] ?? ''))) {
            http_response_code(400); exit;
        }
        $payload = ['token' => $data['authority']];
        $transaction = gateway('transaction', $payload);
        // The merchant must bear the gateway fee: exact IRR amount is checked before verification.
        if (($transaction['status'] ?? 0) != 1 || (string)($transaction['factorNumber'] ?? '') !== $attempt || !is_numeric($transaction['amount'] ?? null) || (float)$transaction['amount'] !== (float)$amount) {
            throw new RuntimeException('Transaction binding or amount mismatch');
        }
        $code = (int)($transaction['code'] ?? 0);
        if ($code === 1) {
            $verified = gateway('verify', $payload);
            if (($verified['status'] ?? 0) == 1) {
                if ((float)($verified['amount'] ?? 0) !== (float)$amount || (string)($verified['factorNumber'] ?? '') !== $attempt || empty($verified['transId'])) throw new RuntimeException('Verification mismatch');
                respond($nonce, ['code' => 100, 'ref_id' => (string)$verified['transId'], 'amountRial' => $amount, 'details' => (object)details($verified + $transaction)]);
            }
            // A concurrent verifier or a lost response may have completed verification.
            $transaction = gateway('transaction', $payload);
            $code = (int)($transaction['code'] ?? 0);
        }
        if ($code === 2 && ($transaction['status'] ?? 0) == 1 && (string)($transaction['factorNumber'] ?? '') === $attempt && (float)($transaction['amount'] ?? 0) === (float)$amount && !empty($transaction['transId'])) {
            respond($nonce, ['code' => 101, 'ref_id' => (string)$transaction['transId'], 'amountRial' => $amount, 'details' => (object)details($transaction)]);
        }
        if (in_array($code, [3, 4], true)) respond($nonce, ['code' => -$code]);
        throw new RuntimeException('Verification pending');
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }
    $token = (string)($_GET['token'] ?? '');
    if (!tokenValid($token)) { http_response_code(400); throw new RuntimeException('Invalid token'); }
    if (($_GET['callback'] ?? '') === '1') {
        // A browser callback never proves payment. Ticketing verifies server-to-server above.
        $status = ($_GET['payment_status'] ?? '') === 'OK' ? 'OK' : 'NOK';
        header('Location: ' . $returnUrl . '?Authority=' . rawurlencode($token) . '&Status=' . $status, true, 303);
        exit;
    }
    $expires = filter_var($_GET['expires'] ?? '', FILTER_VALIDATE_INT);
    if (!$expires || $expires < time() || $expires > time() + 1200 || !hash_equals(signature('launch:' . $token . ':' . $expires), (string)($_GET['signature'] ?? ''))) {
        http_response_code(403); throw new RuntimeException('Expired launch');
    }
    header('Location: https://ipg.vandar.io/v3/' . rawurlencode($token), true, 303);
    exit;
} catch (Throwable $error) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(503);
        header('Content-Type: application/json');
        echo '{"error":"PAYMENT_UNCONFIRMED"}';
        exit;
    }
    if (http_response_code() < 400) http_response_code(503);
}
?>
<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>حمایت برای نمایش محرمانه</title>
<style>body{font-family:Tahoma,sans-serif;min-height:90vh;display:grid;place-items:center;background:#f5faf9;color:#063a3c;margin:0;padding:20px}main{max-width:440px;background:white;border:1px solid #dcebea;border-radius:20px;padding:28px;line-height:2}a{color:#007b7a}</style>
<main><h1>انتقال انجام نشد</h1><p>به بخش بلیط‌های من برگردید و وضعیت حمایت را بررسی کنید. اگر مبلغی پرداخت کرده‌اید، تا مشخص‌شدن نتیجه دوباره پرداخت نکنید.</p><a href="https://mahramane.com/tickets">بازگشت به بلیط‌های من</a></main></html>

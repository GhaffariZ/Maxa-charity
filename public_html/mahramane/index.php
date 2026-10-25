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
    if ($value === '') throw new RuntimeException('MACSA_CONFIG_MISSING');
    return $value;
}
function signature(string $value): string {
    $secret = setting('MAHRAMANE_BRIDGE_SECRET');
    if (strlen($secret) < 32) throw new RuntimeException('MACSA_CONFIG_MISSING');
    return hash_hmac('sha256', $value, $secret);
}
function tokenValid(string $token): bool {
    return preg_match('/^[A-Za-z0-9._~-]{8,500}$/D', $token) === 1;
}
/** Bounded error text only; exclude response data and redact credentials before signing. */
function providerMessages(array $result): array {
    $messages = [];
    $walk = function (mixed $value, int $depth = 0) use (&$walk, &$messages): void {
        if ($depth > 4 || count($messages) >= 6) return;
        if (is_array($value)) {
            foreach ($value as $item) $walk($item, $depth + 1);
        } elseif (is_string($value) && $value !== '') {
            foreach (['MAHRAMANE_VANDAR_API_KEY', 'MAHRAMANE_BRIDGE_SECRET'] as $key) {
                $secret = campaign_service_env($key);
                if ($secret !== '') $value = str_replace($secret, '[redacted]', $value);
            }
            $value = strip_tags($value);
            $value = preg_replace('~https?://[^\s<>]+|[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}|[A-Za-z0-9_=-]{24,}|[0-9]{7,}~u', '[redacted]', $value) ?? '';
            $value = trim(preg_replace('/[\p{Cc}\p{Cf}\s]+/u', ' ', $value) ?? '');
            // No mbstring dependency: truncate at a valid UTF-8 boundary.
            $value = substr($value, 0, 400);
            while ($value !== '' && preg_match('//u', $value) !== 1) $value = substr($value, 0, -1);
            if ($value !== '') $messages[] = $value;
        }
    };
    foreach (['errors', 'error', 'message'] as $field) $walk($result[$field] ?? null);
    return array_values(array_unique($messages));
}
/** Only fixed categories are shown to public users. */
function rejectionReason(array $messages): string {
    $known = [
        'وارد کردن api key الزامی است' => 'API_KEY_INVALID',
        'api_key معتبر نیست' => 'API_KEY_INVALID',
        'IP پذیرنده معتبر نیست' => 'MERCHANT_IP_INVALID',
        'وارد کردن callback_url الزامی است' => 'CALLBACK_INVALID',
        'callback_url معتبر نیست' => 'CALLBACK_INVALID',
        'وارد کردن amount الزامی است' => 'AMOUNT_INVALID',
        'amount نباید کوچکتر از 1000 باشد' => 'AMOUNT_INVALID',
        'amount باید عدد یا رشته‌ای از اعداد باشد' => 'AMOUNT_INVALID',
    ];
    foreach ($messages as $error) {
        $normalized = trim(strtr($error, ['ي' => 'ی', 'ك' => 'ک']), " .\t\n\r\0\x0B");
        if (isset($known[$normalized])) return $known[$normalized];
    }
    return 'UNKNOWN';
}
/** @return array<string,mixed> */
function gateway(string $method, array $payload): array {
    if (!function_exists('curl_init')) throw new RuntimeException('MACSA_CURL_MISSING');
    static $startedAt = null;
    $startedAt ??= microtime(true);
    // All provider calls must finish inside the caller's 25-second deadline.
    $remainingSeconds = (int)floor(20 - (microtime(true) - $startedAt));
    if ($remainingSeconds < 1) throw new RuntimeException('VANDAR_UNAVAILABLE');
    $handle = curl_init('https://ipg.vandar.io/api/v3/' . $method);
    curl_setopt_array($handle, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['api_key' => setting('MAHRAMANE_VANDAR_API_KEY')] + $payload, JSON_THROW_ON_ERROR),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => min(5, $remainingSeconds),
        CURLOPT_TIMEOUT => min(15, $remainingSeconds),
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
    ]);
    $raw = curl_exec($handle);
    $status = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    if (!is_string($raw) || $status >= 500 || $status === 0) throw new RuntimeException('VANDAR_UNAVAILABLE');
    $result = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($result)) throw new RuntimeException('VANDAR_UNAVAILABLE');
    // Local metadata, never obtained from a provider-controlled field.
    $result['_bridgeHttpStatus'] = $status;
    return $result;
}
function respond(string $nonce, array $result): never {
    $raw = json_encode(['nonce' => $nonce, 'result' => $result], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    header('Content-Type: application/json');
    header('X-Mahramane-Signature: ' . signature($raw));
    echo $raw;
    exit;
}
function rejectRequest(int $status, string $code, ?string $nonce = null): never {
    http_response_code($status);
    header('Content-Type: application/json');
    $raw = json_encode(['nonce' => $nonce, 'error' => $code], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $secret = campaign_service_env('MAHRAMANE_BRIDGE_SECRET');
    if (strlen($secret) >= 32) header('X-Mahramane-Signature: ' . hash_hmac('sha256', $raw, $secret));
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
/** Failure responses can omit payment metadata; supplied metadata must still match. */
function checkBinding(array $transaction, int $amount, string $attempt, bool $required = true): void {
    if (($required || isset($transaction['factorNumber'])) && (string)($transaction['factorNumber'] ?? '') !== $attempt) throw new RuntimeException('TRANSACTION_MISMATCH');
    if (($required || isset($transaction['amount'])) && (!is_numeric($transaction['amount'] ?? null) || (float)$transaction['amount'] !== (float)$amount)) throw new RuntimeException('TRANSACTION_MISMATCH');
}
/** Only authenticated provider evidence, never a browser NOK, makes failure terminal. */
function transactionFailure(array $result, int $amount, string $attempt): ?int {
    $status = $result['status'] ?? null;
    $code = (int)($result['code'] ?? 0);
    if (in_array($status, [0, 1, '0', '1'], true) && in_array($code, [3, 4], true)) {
        checkBinding($result, $amount, $attempt, false);
        return -$code;
    }
    $messages = providerMessages($result);
    if (($status === 0 || $status === '0') && count($messages) === 1 && trim(strtr($messages[0], ['ي' => 'ی', 'ك' => 'ک']), " .\t\n\r") === 'تراکنش با خطا مواجه شده است') {
        checkBinding($result, $amount, $attempt, false);
        return -4;
    }
    return null;
}
function pendingResult(string $nonce, array $result): never {
    respond($nonce, ['code' => 0, 'rejectionDiagnostics' => ['version' => 1, 'httpStatus' => $result['_bridgeHttpStatus'] ?? 200, 'messages' => providerMessages($result)]]);
}
$nonce = null;
try {
    $returnUrl = setting('MAHRAMANE_CALLBACK_URL');
    if (!str_starts_with($returnUrl, 'https://') || !str_ends_with($returnUrl, '/api/v1/payments/callback')) throw new RuntimeException('Invalid callback configuration');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents('php://input', false, null, 0, 8193);
        if (!is_string($raw) || strlen($raw) > 8192 || !hash_equals(signature($raw), (string)($_SERVER['HTTP_X_MAHRAMANE_SIGNATURE'] ?? ''))) {
            rejectRequest(403, 'MACSA_AUTH_FAILED');
        }
        $data = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !preg_match('/^[a-f0-9]{48}$/D', (string)($data['nonce'] ?? ''))) rejectRequest(400, 'MACSA_INVALID_REQUEST');
        $nonce = $data['nonce'];
        if (!is_int($data['at'] ?? null) || abs(time() - $data['at']) > 60) rejectRequest(400, 'MACSA_CLOCK_MISMATCH', $nonce);
        if (($data['callback'] ?? '') !== $returnUrl) rejectRequest(400, 'MACSA_CALLBACK_MISMATCH', $nonce);
        if (($data['action'] ?? '') === 'health') {
            setting('MAHRAMANE_VANDAR_API_KEY');
            if (!function_exists('curl_init')) throw new RuntimeException('MACSA_CURL_MISSING');
            respond($nonce, ['code' => 100]);
        }
        $amount = $data['amountRial'] ?? null;
        $attempt = (string)($data['attemptId'] ?? '');
        if (!is_int($amount) || $amount < 1 || $amount > 2000000000 || !preg_match('/^[a-f0-9-]{36}$/D', $attempt)) {
            rejectRequest(400, 'MACSA_INVALID_REQUEST', $nonce);
        }
        if (($data['action'] ?? '') === 'request') {
            if ($amount < 10000) respond($nonce, ['code' => -1, 'rejectionReason' => 'AMOUNT_BELOW_MINIMUM', 'rejectionDiagnostics' => ['version' => 1, 'httpStatus' => 400, 'messages' => ['حداقل مبلغ حمایت ۱۰٬۰۰۰ ریال است.']]]);
            $result = gateway('send', ['amount' => $amount, 'callback_url' => 'https://macsa.ir/mahramane/?callback=1', 'factorNumber' => $attempt, 'description' => 'حمایت از بیماران سرطانی؛ نمایش محرمانه']);
            $token = (string)($result['token'] ?? '');
            if (($result['status'] ?? 0) == 1 && tokenValid($token)) respond($nonce, ['code' => 100, 'authority' => $token]);
            if (($result['status'] ?? null) != 0 || !array_key_exists('status', $result)) throw new RuntimeException('VANDAR_UNAVAILABLE');
            $messages = providerMessages($result);
            respond($nonce, ['code' => -1, 'rejectionReason' => rejectionReason($messages), 'rejectionDiagnostics' => ['version' => 1, 'httpStatus' => $result['_bridgeHttpStatus'], 'messages' => $messages]]);
        }
        if (($data['action'] ?? '') !== 'verify' || !tokenValid((string)($data['authority'] ?? ''))) {
            http_response_code(400); exit;
        }
        $payload = ['token' => $data['authority']];
        $transaction = gateway('transaction', $payload);
        $failure = transactionFailure($transaction, $amount, $attempt);
        if ($failure !== null) respond($nonce, ['code' => $failure]);
        if (($transaction['status'] ?? 0) != 1) pendingResult($nonce, $transaction);
        $code = (int)($transaction['code'] ?? 0);
        if (!in_array($code, [1, 2], true)) {
            checkBinding($transaction, $amount, $attempt, false);
            pendingResult($nonce, $transaction);
        }
        // The merchant must bear the gateway fee: exact IRR amount is checked before verification.
        checkBinding($transaction, $amount, $attempt);
        if ($code === 1) {
            $verified = gateway('verify', $payload);
            if (($verified['status'] ?? 0) == 1) {
                if ((float)($verified['amount'] ?? 0) !== (float)$amount || (string)($verified['factorNumber'] ?? '') !== $attempt || empty($verified['transId'])) throw new RuntimeException('TRANSACTION_MISMATCH');
                respond($nonce, ['code' => 100, 'ref_id' => (string)$verified['transId'], 'amountRial' => $amount, 'details' => (object)details($verified + $transaction)]);
            }
            if (($verified['status'] ?? 0) == 3) {
                checkBinding($verified, $amount, $attempt, false);
                respond($nonce, ['code' => -3]);
            }
            $failure = transactionFailure($verified, $amount, $attempt);
            if ($failure !== null) respond($nonce, ['code' => $failure]);
            // A concurrent verifier or a lost response may have completed verification.
            $transaction = gateway('transaction', $payload);
            $failure = transactionFailure($transaction, $amount, $attempt);
            if ($failure !== null) respond($nonce, ['code' => $failure]);
            $code = (int)($transaction['code'] ?? 0);
        }
        if ($code === 2 && ($transaction['status'] ?? 0) == 1 && (string)($transaction['factorNumber'] ?? '') === $attempt && (float)($transaction['amount'] ?? 0) === (float)$amount && !empty($transaction['transId'])) {
            respond($nonce, ['code' => 101, 'ref_id' => (string)$transaction['transId'], 'amountRial' => $amount, 'details' => (object)details($transaction)]);
        }
        pendingResult($nonce, $transaction);
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
    // Commit a real merchant document before navigating: a redirect chain does not
    // make Macsa the referring document. Never include the signed query in Referer.
    $gatewayUrl = 'https://ipg.vandar.io/v3/' . rawurlencode($token);
    $scriptNonce = base64_encode(random_bytes(24));
    $escapedUrl = htmlspecialchars($gatewayUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    header('Content-Type: text/html; charset=utf-8');
    header('Referrer-Policy: origin');
    header("Content-Security-Policy: default-src 'none'; script-src 'nonce-{$scriptNonce}'; style-src 'unsafe-inline'; frame-ancestors 'none'; base-uri 'none'; form-action 'none'");
    ?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="referrer" content="origin">
<title>انتقال به درگاه حمایت | مکسا</title>
<style>body{font-family:Tahoma,sans-serif;min-height:90vh;display:grid;place-items:center;background:#f5faf9;color:#063a3c;margin:0;padding:20px}main{box-sizing:border-box;width:100%;max-width:440px;background:white;border:1px solid #dcebea;border-radius:20px;padding:28px;line-height:2}h1{font-size:1.25rem}a{display:inline-block;background:#007b7a;color:white;border-radius:10px;padding:10px 20px;text-decoration:none}a:focus-visible{outline:3px solid #063a3c;outline-offset:4px}</style>
</head>
<body>
<main aria-labelledby="transfer-title"><h1 id="transfer-title">در حال انتقال به درگاه حمایت</h1><p role="status">لطفاً چند لحظه منتظر بمانید. اگر انتقال انجام نشد، دکمهٔ زیر را بزنید.</p><a href="<?= $escapedUrl ?>" referrerpolicy="origin">ادامه به درگاه پرداخت</a></main>
<script nonce="<?= $scriptNonce ?>">window.location.replace(<?= json_encode($gatewayUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>);</script>
</body></html>
    <?php
    exit;
} catch (Throwable $error) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $known = ['MACSA_CONFIG_MISSING', 'MACSA_CURL_MISSING', 'VANDAR_UNAVAILABLE', 'TRANSACTION_MISMATCH'];
        rejectRequest(503, in_array($error->getMessage(), $known, true) ? $error->getMessage() : 'PAYMENT_UNCONFIRMED', $nonce);
    }
    if (http_response_code() < 400) http_response_code(503);
}
?>
<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>حمایت برای نمایش محرمانه</title>
<style>body{font-family:Tahoma,sans-serif;min-height:90vh;display:grid;place-items:center;background:#f5faf9;color:#063a3c;margin:0;padding:20px}main{max-width:440px;background:white;border:1px solid #dcebea;border-radius:20px;padding:28px;line-height:2}a{color:#007b7a}</style>
<main><h1>انتقال انجام نشد</h1><p>به بخش بلیط‌های من برگردید و وضعیت حمایت را بررسی کنید. اگر مبلغی پرداخت کرده‌اید، تا مشخص‌شدن نتیجه دوباره پرداخت نکنید.</p><a href="https://mahramane.com/tickets">بازگشت به بلیط‌های من</a></main></html>

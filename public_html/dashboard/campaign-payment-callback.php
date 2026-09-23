<?php

declare(strict_types=1);

ini_set('display_errors', '0');
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../core/campaign-service.php';

$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
$paymentStatus = strtoupper(trim((string)($_GET['payment_status'] ?? $_POST['payment_status'] ?? '')));
$validInput = preg_match('/^[A-Za-z0-9._~-]{8,500}$/', $token) === 1
    && in_array($paymentStatus, ['OK', 'FAILED'], true);

$finalStatus = 'PENDING';
$paymentId = '';
if ($validInput) {
    try {
        $payment = (new CampaignServiceClient('hq'))->forwardPaymentCallback('vandar', [
            'token' => $token,
            'payment_status' => $paymentStatus,
        ]);
        $finalStatus = strtoupper((string)($payment['status'] ?? 'PENDING'));
        $paymentId = (string)($payment['id'] ?? '');
    } catch (Throwable $error) {
        error_log('[campaign-payment-callback] ' . get_class($error) . ': ' . $error->getMessage());
    }
} else {
    http_response_code(400);
    $finalStatus = 'INVALID';
}

$succeeded = $finalStatus === 'SUCCEEDED';
$failed = in_array($finalStatus, ['FAILED', 'INVALID'], true);
$title = $succeeded ? 'پرداخت با موفقیت انجام شد' : ($failed ? 'پرداخت انجام نشد' : 'پرداخت در حال بررسی است');
$message = $succeeded
    ? 'کمک شما با موفقیت در کمپین مکسا ثبت شد. از همراهی شما سپاسگزاریم.'
    : ($failed
        ? 'تراکنش لغو شد یا مورد تأیید درگاه قرار نگرفت. مبلغی از حساب شما کسر نخواهد شد.'
        : 'پاسخ درگاه دریافت شد اما تأیید نهایی هنوز کامل نشده است. وضعیت پرداخت در سایت مکسا دوباره بررسی می‌شود.');
$accent = $succeeded ? '#0a8f78' : ($failed ? '#b44b5b' : '#d38a13');
$nonce = base64_encode(random_bytes(18));
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; script-src 'nonce-{$nonce}'; base-uri 'none'; frame-ancestors 'none'");
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <style>
    body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f5faf9;color:#063a3c;font-family:Vazirmatn,Tahoma,sans-serif}
    main{width:min(460px,calc(100% - 32px));padding:36px 25px;text-align:center;background:#fff;border:1px solid #dcebea;border-radius:22px;box-shadow:0 20px 50px rgba(4,61,61,.12)}
    .mark{width:62px;height:62px;margin:0 auto 18px;display:grid;place-items:center;border-radius:50%;background:<?= $accent ?>16;color:<?= $accent ?>;font-size:30px;font-weight:900}
    h1{font-size:20px;margin:0 0 10px;color:<?= $accent ?>}p{font-size:13px;color:#687879;line-height:2;margin:0}.id{margin-top:13px;font-size:10px;color:#9aa5a5;direction:ltr;word-break:break-all}button{margin-top:20px;border:0;border-radius:12px;padding:11px 22px;background:#007b7a;color:#fff;font-family:inherit;font-weight:800;cursor:pointer}
  </style>
</head>
<body>
<main>
  <div class="mark" aria-hidden="true"><?= $succeeded ? '✓' : ($failed ? '×' : '…') ?></div>
  <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
  <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
  <?php if ($paymentId !== ''): ?><div class="id">Payment: <?= htmlspecialchars($paymentId, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
  <button type="button" id="close-window">بستن پنجره</button>
</main>
<script nonce="<?= htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') ?>">
  document.getElementById('close-window').addEventListener('click', function(){ window.close(); });
  <?php if ($succeeded || $failed): ?>setTimeout(function(){ window.close(); }, 5000);<?php endif; ?>
</script>
</body>
</html>

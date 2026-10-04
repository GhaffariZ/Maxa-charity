<?php

declare(strict_types=1);

ini_set('display_errors', '0');
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');
header('X-Macsa-Payment-Callback-Version: 2026-09-23.2');

require_once __DIR__ . '/../core/campaign-service.php';

$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
$paymentStatus = strtoupper(trim((string)($_GET['payment_status'] ?? $_POST['payment_status'] ?? '')));
$retryAttempt = max(0, min(2, (int)($_GET['retry'] ?? 0)));
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
    ? 'کمک ارزشمند شما با موفقیت در کمپین مکسا ثبت شد. از همراهی و اعتمادتان سپاسگزاریم.'
    : ($failed
        ? 'تراکنش لغو شد یا مورد تأیید درگاه قرار نگرفت. مبلغی از حساب شما کسر نخواهد شد.'
        : 'پاسخ درگاه دریافت شده است و تأیید نهایی پرداخت در حال انجام است. لطفاً این صفحه را نبندید و دوباره پرداخت نکنید.');
$statusClass = $succeeded ? 'success' : ($failed ? 'failed' : 'pending');
$statusLabel = $succeeded ? 'پرداخت تأیید شد' : ($failed ? 'پرداخت ناموفق' : 'در انتظار تأیید');
$nonce = base64_encode(random_bytes(18));
header("Content-Security-Policy: default-src 'none'; img-src 'self'; font-src 'self'; style-src 'unsafe-inline'; script-src 'nonce-{$nonce}'; base-uri 'none'; frame-ancestors 'none'");
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <style>
    @font-face{font-family:Vazirmatn;src:url('/webfont/Vazirmatn[wght].woff2') format('woff2');font-weight:100 900;font-display:swap}
    :root{--primary:#007b7a;--primary-dark:#006665;--secondary:#f4a61e;--success:#16a37a;--danger:#e0556b;--warning:#d98a0b;--text:#2f3437;--muted:#7e898d;--border:#e6e8ea;--bg:#f8f9fa;--surface:#fff}
    *{box-sizing:border-box}
    body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(circle at 50% 0,rgba(0,123,122,.10),transparent 38%),var(--bg);color:var(--text);font-family:Vazirmatn,Tahoma,sans-serif}
    main{width:min(480px,100%);overflow:hidden;text-align:center;background:var(--surface);border:1px solid var(--border);border-radius:24px;box-shadow:0 24px 56px -20px rgba(0,102,101,.28)}
    .brand{display:flex;justify-content:center;padding:22px 24px 16px;border-bottom:1px solid var(--border)}
    .brand img{display:block;width:190px;max-width:62%;height:auto}
    .content{padding:30px 28px 28px}
    .mark{width:72px;height:72px;margin:0 auto 18px;display:grid;place-items:center;border-radius:22px}
    .mark svg{width:36px;height:36px;fill:none;stroke:currentColor;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}
    .success .mark{color:var(--success);background:rgba(22,163,122,.11)}
    .failed .mark{color:var(--danger);background:rgba(224,85,107,.11)}
    .pending .mark{color:var(--warning);background:rgba(244,166,30,.14)}
    .pending .mark svg{animation:spin 1.1s linear infinite}
    .status{display:inline-flex;align-items:center;gap:7px;margin-bottom:12px;padding:5px 12px;border-radius:99px;font-size:11.5px;font-weight:800}
    .success .status{color:var(--success);background:rgba(22,163,122,.10)}
    .failed .status{color:var(--danger);background:rgba(224,85,107,.10)}
    .pending .status{color:var(--warning);background:rgba(244,166,30,.13)}
    .status::before{content:'';width:7px;height:7px;border-radius:50%;background:currentColor}
    h1{font-size:21px;line-height:1.6;margin:0 0 8px;font-weight:900;color:var(--text)}
    p{font-size:13px;color:var(--muted);line-height:2;margin:0 auto;max-width:390px}
    .receipt{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:22px;padding:12px 14px;border:1px dashed var(--border);border-radius:13px;background:var(--bg);font-size:11.5px;color:var(--muted)}
    .receipt code{direction:ltr;color:var(--text);font-family:ui-monospace,SFMono-Regular,Consolas,monospace;word-break:break-all}
    .actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:22px}
    .btn{display:flex;align-items:center;justify-content:center;min-height:46px;border-radius:13px;padding:0 16px;font-family:inherit;font-size:13px;font-weight:800;text-decoration:none;cursor:pointer}
    .btn-primary{border:0;color:#fff;background:linear-gradient(135deg,var(--primary),var(--primary-dark));box-shadow:0 12px 24px -14px rgba(0,123,122,.85)}
    .btn-ghost{border:1px solid var(--border);color:var(--text);background:var(--surface)}
    @keyframes spin{to{transform:rotate(360deg)}}
    @media(max-width:420px){body{padding:14px}.content{padding:26px 18px 22px}.actions{grid-template-columns:1fr}.brand img{max-width:75%}}
  </style>
</head>
<body>
<main class="<?= $statusClass ?>">
  <div class="brand"><img src="/dashboard/assets/img/logo/logo.png" alt="مؤسسه نیکوکاری کنترل سرطان ایرانیان مکسا"></div>
  <div class="content">
    <div class="mark" aria-hidden="true">
      <?php if ($succeeded): ?>
        <svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>
      <?php elseif ($failed): ?>
        <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
      <?php else: ?>
        <svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-2.64-6.36"/></svg>
      <?php endif; ?>
    </div>
    <div class="status"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></div>
    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php if ($paymentId !== ''): ?>
      <div class="receipt"><span>شناسه پرداخت</span><code><?= htmlspecialchars($paymentId, ENT_QUOTES, 'UTF-8') ?></code></div>
    <?php endif; ?>
    <div class="actions">
      <a class="btn btn-primary" href="/">بازگشت به سایت مکسا</a>
      <button class="btn btn-ghost" type="button" id="close-window">چاپ رسید</button>
    </div>
  </div>
</main>
<script nonce="<?= htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') ?>">
  document.getElementById('close-window').addEventListener('click', function(){ window.print(); });
  if (window.opener) {
    window.opener.postMessage({
      type: 'macsa-payment-result',
      status: <?= json_encode($finalStatus, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      paymentId: <?= json_encode($paymentId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    }, window.location.origin);
  }
  <?php if (!$succeeded && !$failed && $retryAttempt < 2): ?>
  setTimeout(function () {
    var retryUrl = new URL(window.location.href);
    retryUrl.searchParams.set('retry', <?= $retryAttempt + 1 ?>);
    window.location.replace(retryUrl.toString());
  }, 3500);
  <?php endif; ?>
</script>
</body>
</html>

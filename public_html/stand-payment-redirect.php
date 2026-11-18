<?php

declare(strict_types=1);

ini_set('display_errors', '0');
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Referrer-Policy: origin');
header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');

$encoded = trim((string)($_GET['target'] ?? ''));
$padded = strtr($encoded, '-_', '+/');
$padding = strlen($padded) % 4;
if ($padding !== 0) {
    $padded .= str_repeat('=', 4 - $padding);
}
$target = base64_decode($padded, true);
$parts = is_string($target) ? parse_url($target) : false;
$valid = is_array($parts)
    && ($parts['scheme'] ?? '') === 'https'
    && strtolower((string)($parts['host'] ?? '')) === 'ipg.vandar.io'
    && !isset($parts['user'])
    && !isset($parts['pass'])
    && !isset($parts['port'])
    && !isset($parts['query'])
    && !isset($parts['fragment'])
    && preg_match('#^/v(?:3|4)/[A-Za-z0-9._~-]{8,500}$#', (string)($parts['path'] ?? '')) === 1;

if (!$valid) {
    http_response_code(400);
    $target = '';
}

$nonce = base64_encode(random_bytes(18));
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; script-src 'nonce-{$nonce}'; base-uri 'none'; form-action https://ipg.vandar.io; frame-ancestors 'none'");
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="referrer" content="origin">
  <title>انتقال به درگاه امن</title>
  <style>
    body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f5faf9;color:#063a3c;font-family:Vazirmatn,Tahoma,sans-serif}
    main{width:min(420px,calc(100% - 32px));padding:34px 24px;text-align:center;background:#fff;border:1px solid #dcebea;border-radius:22px;box-shadow:0 20px 50px rgba(4,61,61,.12)}
    i{display:block;width:42px;height:42px;margin:0 auto 18px;border:4px solid #dcebea;border-top-color:#007b7a;border-radius:50%;animation:r .8s linear infinite}
    h1{font-size:19px;margin:0 0 8px}p{font-size:13px;color:#687879;line-height:1.9;margin:0}a{display:inline-block;margin-top:18px;color:#007b7a;font-weight:800}
    @keyframes r{to{transform:rotate(360deg)}}
  </style>
</head>
<body>
<main>
<?php if ($valid): ?>
  <i aria-hidden="true"></i>
  <h1>در حال انتقال به درگاه امن وندار</h1>
  <p>لطفاً این صفحه را نبندید.</p>
  <a href="<?= htmlspecialchars((string)$target, ENT_QUOTES, 'UTF-8') ?>">اگر منتقل نشدید، اینجا بزنید</a>
  <script nonce="<?= htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') ?>">
    window.location.replace(<?= json_encode($target, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
  </script>
<?php else: ?>
  <h1>آدرس درگاه معتبر نیست</h1>
  <p>لطفاً این پنجره را ببندید و دوباره تلاش کنید.</p>
<?php endif; ?>
</main>
</body>
</html>

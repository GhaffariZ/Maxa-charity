<?php
declare(strict_types=1);
require_once __DIR__.'/core/stand-payments.php';
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');
$receipt = null;
$receiptError = '';
try {
    $DB = require __DIR__.'/core/db-config.php';
    $db = new PDO("mysql:host={$DB['host']};dbname={$DB['name']};charset={$DB['charset']}", $DB['user'],$DB['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $receipt = (new StandPayments($db))->callback((string)($_GET['receipt'] ?? ''),(string)($_GET['token'] ?? $_POST['token'] ?? ''));
} catch (Throwable $e) {
    error_log('[stand-payment-callback] '.get_class($e));
    $receiptError = 'تأیید پرداخت فعلاً ممکن نشد. از بخش سفارشات نتیجه را بررسی کنید و دوباره پرداخت نکنید.';
}
require __DIR__.'/core/stand-receipt-view.php';

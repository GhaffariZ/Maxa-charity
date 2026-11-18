<?php
$state = $receipt['status'] ?? 'pending';
$receiptEscape = static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>رسید خرید استند مکسا</title>
<link rel="stylesheet" href="/font.css"><style>
*{box-sizing:border-box}body{margin:0;background:#f6f8f8;color:#263b3c;font-family:Vazirmatn,Tahoma,sans-serif;min-height:100vh;display:grid;place-items:center;padding:24px}.receipt{width:min(520px,100%);background:white;border:1px solid #dce8e6;border-radius:18px;padding:36px}h1{font-size:23px;margin:18px 0 12px}p{line-height:1.9;color:#617576}.tag{background:#e1f3ef;color:#006e65;padding:6px 15px;border-radius:30px;display:inline-block}.row{display:flex;justify-content:space-between;gap:16px;border-bottom:1px solid #edf1f0;padding:16px 0}.actions{display:flex;gap:14px;flex-wrap:wrap;margin-top:26px}a,button{font:inherit;color:#006f68}a{padding:12px 0}button{background:#007b73;color:white;border:0;border-radius:9px;padding:12px 20px;cursor:pointer}
</style></head><body><main class="receipt">
<?php if (!empty($standPreview)): ?><p>پیش‌نمایش آزمایشی — هیچ پرداخت واقعی انجام نشده است.</p><?php endif; ?>
<span class="tag">استند</span>
<h1><?= $state==='success' ? 'پرداخت استند تأیید شد' : ($state==='failed' ? 'پرداخت استند انجام نشد' : 'پرداخت در انتظار بررسی است') ?></h1>
<p><?= $receiptEscape($receiptError ?: ($state==='success' ? 'سفارش شما ثبت و وجه پرداختی با تگ «استند» در گزارش مالی درج شد.' : 'نتیجه پرداخت را از بخش سفارشات پیگیری کنید.')) ?></p>
<?php if ($receipt): ?>
<div class="row"><span>شماره سفارش</span><b><?= $receiptEscape($receipt['order_id']) ?></b></div>
<div class="row"><span>مبلغ سفارش</span><b><?= number_format((int)$receipt['amount']) ?> تومان</b></div>
<?php if ($state==='success'): ?><div class="row"><span>شناسه تراکنش</span><b dir="ltr"><?= $receiptEscape($receipt['gateway_ref_id']) ?></b></div><?php endif; ?>
<?php endif; ?>
<div class="actions"><button type="button" onclick="window.print()">چاپ رسید</button><a href="/stand-order.php">سفارش استند</a>
<?php if (!empty($standPreview)): ?><a href="/dashboard/orders.php">سفارشات</a><a href="/dashboard/financial-management.php">مشاهده گزارش مالی</a><?php else: ?><a href="/benefactor-dashboard/orders">سفارش‌های من</a><?php endif; ?>
</div></main></body></html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/core/site-locale.php';
require_once __DIR__ . '/core/component-renderer.php';
require_once __DIR__ . '/core/shop-db.php';

// Format price in Tomans with Persian numerals
if (!function_exists('shop_format_toman')) {
    function shop_format_toman(float|int|string $price): string {
        $num = (int)$price;
        $formatted = number_format($num);
        return strtr($formatted, [
            '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',','=>'٬'
        ]);
    }
}

// Persian digits converter
if (!function_exists('fa_digits')) {
    function fa_digits($s): string {
        return strtr((string)$s, [
            '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹','.'=>'٫'
        ]);
    }
}

// Establish database connection if not provided
if (!isset($pdo) || !($pdo instanceof PDO)) {
    try {
        $dbPath = __DIR__ . '/core/database.php';
        if (file_exists($dbPath)) {
            require_once $dbPath;
        }
    } catch (Throwable $e) {}
}

if (isset($pdo) && ($pdo instanceof PDO)) {
    try {
        shop_ensure_schema($pdo);
    } catch (Throwable $e) {}
}

$trackingCode = trim((string)($_GET['tracking_code'] ?? $_POST['tracking_code'] ?? ''));
$statusParam  = strtoupper(trim((string)($_GET['status'] ?? $_GET['payment_status'] ?? $_POST['status'] ?? $_POST['payment_status'] ?? '')));
$isGatewayOk  = in_array($statusParam, ['OK', 'SUCCESS', 'SUCCEEDED', '100', 'PAID'], true);

$order = null;
$orderItems = [];

if ($trackingCode !== '' && isset($pdo) && ($pdo instanceof PDO)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE tracking_code = ? AND order_type = 'product' LIMIT 1");
    $stmt->execute([$trackingCode]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($order) {
        $orderId = (int)$order['id'];

        if ($isGatewayOk) {
            // Idempotent Stock Deduction & Payment Status Update
            if ($order['payment_status'] !== 'paid') {
                $pdo->beginTransaction();
                try {
                    $nowStr = date('Y-m-d H:i:s');
                    $updOrder = $pdo->prepare("UPDATE orders SET payment_status = 'paid', paid_at = ? WHERE id = ?");
                    $updOrder->execute([$nowStr, $orderId]);

                    $itemsStmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                    $itemsStmt->execute([$orderId]);
                    $rawItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

                    $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                    $outStmt = $pdo->prepare("UPDATE products SET status = 'out_of_stock' WHERE id = ? AND stock <= 0");

                    foreach ($rawItems as $it) {
                        $pId = (int)$it['product_id'];
                        $qty = (int)$it['quantity'];
                        if ($pId > 0 && $qty > 0) {
                            $stockStmt->execute([$qty, $pId, $qty]);
                            $outStmt->execute([$pId]);
                        }
                    }

                    $pdo->commit();
                    $order['payment_status'] = 'paid';
                    $order['paid_at'] = $nowStr;
                } catch (Throwable $e) {
                    $pdo->rollBack();
                }
            }

            // Fetch order items for receipt
            $itemDetailsStmt = $pdo->prepare("
                SELECT oi.*, p.image_url 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $itemDetailsStmt->execute([$orderId]);
            $orderItems = $itemDetailsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } else {
            // Payment Failed or Cancelled
            if ($order['payment_status'] === 'pending') {
                $updOrder = $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?");
                $updOrder->execute([$orderId]);
                $order['payment_status'] = 'failed';
            }
        }
    }
}

$pageTitle = site_text('رسید و نتیجه پرداخت | فروشگاه مکسا');
render_dashboard_component('header', get_defined_vars());
?>

<div class="shop-receipt-page">
  <style>
    :root {
      --shop-primary: #faa61a;
      --shop-primary-hover: #e99508;
      --shop-primary-subtle: #fef3c7;
      --shop-dark: #0f172a;
      --shop-slate-800: #1e293b;
      --shop-slate-600: #475569;
      --shop-slate-500: #64748b;
      --shop-slate-200: #e2e8f0;
      --shop-slate-100: #f1f5f9;
      --shop-slate-50: #f8fafc;
      --shop-green: #10b981;
      --shop-green-dark: #059669;
      --shop-green-subtle: #d1fae5;
      --shop-red: #ef4444;
      --shop-red-dark: #dc2626;
      --shop-red-subtle: #fee2e2;
      --shop-radius: 16px;
      --shop-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
    }

    .shop-receipt-page {
      direction: var(--site-direction, rtl);
      font-family: 'Vazirmatn', sans-serif;
      color: var(--shop-dark);
      background: #fafaf9;
      min-height: 80vh;
      padding-top: 40px;
      padding-bottom: 80px;
    }

    .receipt-container {
      width: min(840px, 100%);
      margin-inline: auto;
      padding-inline: 20px;
      box-sizing: border-box;
    }

    .receipt-card {
      background: #ffffff;
      border: 1px solid var(--shop-slate-200);
      border-radius: var(--shop-radius);
      box-shadow: var(--shop-shadow);
      padding: 36px 32px;
      text-align: center;
      margin-bottom: 24px;
    }
    @media (max-width: 640px) {
      .receipt-card {
        padding: 24px 18px;
      }
    }

    /* Status Icons */
    .receipt-icon-wrapper {
      width: 76px;
      height: 76px;
      border-radius: 50%;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .receipt-icon-success {
      background: var(--shop-green-subtle);
      color: var(--shop-green-dark);
    }
    .receipt-icon-failed {
      background: var(--shop-red-subtle);
      color: var(--shop-red-dark);
    }
    .receipt-icon-wrapper svg {
      width: 40px;
      height: 40px;
      stroke-width: 2.4;
    }

    .receipt-status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      font-weight: 800;
      padding: 6px 14px;
      border-radius: 99px;
      margin-bottom: 12px;
    }
    .badge-success {
      background: var(--shop-green-subtle);
      color: var(--shop-green-dark);
    }
    .badge-failed {
      background: var(--shop-red-subtle);
      color: var(--shop-red-dark);
    }

    .receipt-title {
      font-size: 22px;
      font-weight: 900;
      color: var(--shop-dark);
      margin: 0 0 10px;
    }
    .receipt-subtitle {
      font-size: 14.5px;
      color: var(--shop-slate-600);
      line-height: 1.7;
      max-width: 600px;
      margin: 0 auto 24px;
    }

    /* Tracking Code Box */
    .tracking-code-banner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      background: var(--shop-slate-50);
      border: 1.5px dashed var(--shop-slate-200);
      border-radius: 12px;
      padding: 14px 20px;
      margin-bottom: 28px;
      text-align: right;
    }
    .tracking-code-label {
      font-size: 13px;
      color: var(--shop-slate-500);
      margin-bottom: 4px;
    }
    .tracking-code-value {
      font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
      font-size: 18px;
      font-weight: 800;
      color: var(--shop-dark);
      direction: ltr;
      letter-spacing: 0.5px;
    }
    .copy-btn {
      background: #ffffff;
      border: 1px solid var(--shop-slate-200);
      border-radius: 8px;
      padding: 8px 14px;
      font-family: inherit;
      font-size: 12.5px;
      font-weight: 700;
      color: var(--shop-slate-800);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.15s;
    }
    .copy-btn:hover {
      border-color: var(--shop-primary);
      color: var(--shop-primary-hover);
    }

    /* Details Section */
    .receipt-details-section {
      text-align: right;
      border-top: 1px solid var(--shop-slate-100);
      padding-top: 24px;
      margin-top: 24px;
    }
    .section-heading {
      font-size: 16px;
      font-weight: 800;
      color: var(--shop-dark);
      margin-bottom: 16px;
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-bottom: 24px;
    }
    @media (max-width: 600px) {
      .info-grid {
        grid-template-columns: 1fr;
      }
    }
    .info-card {
      background: var(--shop-slate-50);
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 13px;
    }
    .info-label {
      color: var(--shop-slate-500);
      margin-bottom: 4px;
      font-size: 12px;
    }
    .info-val {
      font-weight: 700;
      color: var(--shop-slate-800);
    }

    /* Items Table */
    .receipt-items-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
      margin-bottom: 20px;
    }
    .receipt-items-table th {
      background: var(--shop-slate-50);
      padding: 10px 12px;
      font-weight: 800;
      color: var(--shop-slate-600);
      border-bottom: 1px solid var(--shop-slate-200);
      text-align: right;
    }
    .receipt-items-table td {
      padding: 12px;
      border-bottom: 1px solid var(--shop-slate-100);
      color: var(--shop-dark);
    }
    .receipt-total-row td {
      padding-top: 16px;
      font-size: 15px;
      font-weight: 900;
      color: var(--shop-dark);
      border-bottom: none;
    }

    /* Actions */
    .receipt-actions {
      display: flex;
      gap: 14px;
      justify-content: center;
      margin-top: 28px;
    }
    @media (max-width: 600px) {
      .receipt-actions {
        flex-direction: column;
      }
    }
    .btn-action {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      height: 48px;
      padding-inline: 24px;
      border-radius: 12px;
      font-family: inherit;
      font-size: 14px;
      font-weight: 800;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.15s;
    }
    .btn-action-primary {
      background: linear-gradient(135deg, var(--shop-primary), var(--shop-primary-hover));
      color: #ffffff;
      border: none;
      box-shadow: 0 4px 12px rgba(250, 166, 26, 0.35);
    }
    .btn-action-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(250, 166, 26, 0.45);
    }
    .btn-action-secondary {
      background: #ffffff;
      color: var(--shop-slate-800);
      border: 1.5px solid var(--shop-slate-200);
    }
    .btn-action-secondary:hover {
      background: var(--shop-slate-50);
      border-color: var(--shop-slate-600);
    }
  </style>

  <div class="receipt-container">
    <?php if (!$order): ?>
      <!-- Order Not Found -->
      <div class="receipt-card">
        <div class="receipt-icon-wrapper receipt-icon-failed">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h1 class="receipt-title"><?= site_e('سفارش مورد نظر یافت نشد.') ?></h1>
        <p class="receipt-subtitle">کد پیگیری معتبری ارسال نشده است یا این سفارش در سیستم موجود نمی‌باشد.</p>
        <div class="receipt-actions">
          <a href="/shop.php" class="btn-action btn-action-primary">
            <?= site_e('بازگشت به فروشگاه') ?>
          </a>
        </div>
      </div>

    <?php elseif ($isGatewayOk && $order['payment_status'] === 'paid'): ?>
      <!-- Clear Cart in Browser -->
      <script>
        try {
          localStorage.removeItem('maxa_shop_cart');
        } catch(e) {}
      </script>

      <!-- Successful Payment Receipt -->
      <div class="receipt-card">
        <div class="receipt-icon-wrapper receipt-icon-success">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        
        <span class="receipt-status-badge badge-success"><?= site_e('پرداخت موفق و ثبت سفارش') ?></span>
        <h1 class="receipt-title"><?= site_e('سفارش شما با موفقیت ثبت شد') ?></h1>
        
        <p class="receipt-subtitle">
          <?= site_e('سفارش شما با موفقیت ثبت شد و به زودی جهت ارسال آماده خواهد شد. هزینه ارسال به صورت پس‌کرایه زمان تحویل مرسوله دریافت می‌شود.') ?>
        </p>

        <!-- Tracking Code Banner -->
        <div class="tracking-code-banner">
          <div>
            <div class="tracking-code-label"><?= site_e('کد پیگیری سفارش شما:') ?></div>
            <div class="tracking-code-value" id="receiptTrackingCode"><?= htmlspecialchars((string)$order['tracking_code'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>
          <button type="button" class="copy-btn" id="btnCopyTracking">
            <svg style="width:16px;height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            <span id="copyBtnText"><?= site_e('کپی کد') ?></span>
          </button>
        </div>

        <!-- Recipient & Shipping Information -->
        <div class="receipt-details-section">
          <h2 class="section-heading"><?= site_e('مشخصات ارسال مرسوله') ?></h2>
          <div class="info-grid">
            <div class="info-card">
              <div class="info-label"><?= site_e('نام و نام خانوادگی تحویل‌گیرنده') ?></div>
              <div class="info-val"><?= htmlspecialchars((string)($order['recipient_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="info-card">
              <div class="info-label"><?= site_e('شماره تماس تحویل‌گیرنده') ?></div>
              <div class="info-val" dir="ltr" style="text-align: right;"><?= htmlspecialchars(fa_digits((string)($order['recipient_phone'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="info-card">
              <div class="info-label"><?= site_e('استان') ?> / <?= site_e('شهر') ?></div>
              <div class="info-val"><?= htmlspecialchars((string)($order['province'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string)($order['city'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="info-card">
              <div class="info-label"><?= site_e('کد پستی ۱۰ رقمی') ?></div>
              <div class="info-val" dir="ltr" style="text-align: right;"><?= htmlspecialchars(fa_digits((string)($order['postal_code'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="info-card" style="grid-column: 1 / -1;">
              <div class="info-label"><?= site_e('آدرس دقیق پستی') ?></div>
              <div class="info-val"><?= htmlspecialchars((string)($order['address'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <?php if (!empty($order['customer_note'])): ?>
              <div class="info-card" style="grid-column: 1 / -1;">
                <div class="info-label"><?= site_e('یادداشت یا توضیحات سفارش') ?></div>
                <div class="info-val"><?= htmlspecialchars((string)$order['customer_note'], ENT_QUOTES, 'UTF-8') ?></div>
              </div>
            <?php endif; ?>
          </div>

          <!-- Order Items Table -->
          <?php if (!empty($orderItems)): ?>
            <h2 class="section-heading"><?= site_e('اقلام سفارش') ?></h2>
            <table class="receipt-items-table">
              <thead>
                <tr>
                  <th><?= site_e('اقلام سفارش') ?></th>
                  <th style="text-align: center;"><?= site_e('تعداد') ?></th>
                  <th><?= site_e('قیمت واحد') ?></th>
                  <th><?= site_e('مبلغ کل') ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($orderItems as $item): ?>
                  <tr>
                    <td>
                      <div style="display:flex; align-items:center; gap:10px;">
                        <?php if (!empty($item['image_url'])): ?>
                          <img src="<?= htmlspecialchars($item['image_url'], ENT_QUOTES, 'UTF-8') ?>" style="width:36px;height:36px;border-radius:6px;object-fit:cover;" alt="">
                        <?php endif; ?>
                        <strong><?= htmlspecialchars((string)$item['product_title'], ENT_QUOTES, 'UTF-8') ?></strong>
                      </div>
                    </td>
                    <td style="text-align: center;"><?= fa_digits((string)$item['quantity']) ?></td>
                    <td><?= shop_format_toman($item['unit_price']) ?> تومان</td>
                    <td><strong><?= shop_format_toman($item['total_price']) ?> تومان</strong></td>
                  </tr>
                <?php endforeach; ?>
                <tr class="receipt-total-row">
                  <td colspan="3"><?= site_e('مبلغ قابل پرداخت:') ?></td>
                  <td style="color:#b45309;"><?= shop_format_toman($order['total_price']) ?> تومان</td>
                </tr>
              </tbody>
            </table>
          <?php endif; ?>
        </div>

        <!-- Action CTAs -->
        <div class="receipt-actions">
          <a href="/benefactor-dashboard/#/orders" class="btn-action btn-action-primary">
            <svg style="width:18px;height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <?= site_e('مشاهده و پیگیری سفارش در پنل کاربری') ?>
          </a>
          <a href="/shop.php" class="btn-action btn-action-secondary">
            <svg style="width:18px;height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="15 18 9 12 15 6"/></svg>
            <?= site_e('بازگشت به فروشگاه') ?>
          </a>
        </div>
      </div>

    <?php else: ?>
      <!-- Failed Payment Notice -->
      <div class="receipt-card">
        <div class="receipt-icon-wrapper receipt-icon-failed">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        
        <span class="receipt-status-badge badge-failed"><?= site_e('پرداخت ناموفق') ?></span>
        <h1 class="receipt-title"><?= site_e('پرداخت انجام نشد') ?></h1>
        
        <p class="receipt-subtitle">
          <?= site_e('تراکنش لغو شد یا از سوی درگاه بانکی مورد تأیید قرار نگرفت. مبلغی از حساب شما کسر نشده است.') ?>
        </p>

        <?php if (!empty($order['tracking_code'])): ?>
          <div class="tracking-code-banner" style="justify-content: center;">
            <div style="text-align: center;">
              <div class="tracking-code-label"><?= site_e('کد پیگیری سفارش شما:') ?></div>
              <div class="tracking-code-value"><?= htmlspecialchars((string)$order['tracking_code'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>
        <?php endif; ?>

        <div class="receipt-actions">
          <a href="/shop-checkout.php" class="btn-action btn-action-primary">
            <svg style="width:18px;height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            <?= site_e('تلاش مجدد برای پرداخت') ?>
          </a>
          <a href="/shop.php" class="btn-action btn-action-secondary">
            <?= site_e('بازگشت به فروشگاه') ?>
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
(function() {
  var btnCopy = document.getElementById('btnCopyTracking');
  var codeEl = document.getElementById('receiptTrackingCode');
  var copyText = document.getElementById('copyBtnText');

  if (btnCopy && codeEl) {
    btnCopy.addEventListener('click', function() {
      var code = (codeEl.textContent || '').trim();
      if (!code) return;

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(function() {
          if (copyText) copyText.textContent = 'کپی شد';
          btnCopy.style.borderColor = 'var(--shop-green)';
          setTimeout(function() {
            if (copyText) copyText.textContent = 'کپی کد';
            btnCopy.style.borderColor = '';
          }, 2500);
        });
      } else {
        var ta = document.createElement('textarea');
        ta.value = code;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        if (copyText) copyText.textContent = 'کپی شد';
        setTimeout(function() {
          if (copyText) copyText.textContent = 'کپی کد';
        }, 2500);
      }
    });
  }
})();
</script>

<?php
render_dashboard_component('footer', get_defined_vars());

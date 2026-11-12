<?php
declare(strict_types=1);

require_once __DIR__ . '/core/site-locale.php';
require_once __DIR__ . '/core/component-renderer.php';
require_once __DIR__ . '/core/shop-db.php';

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

if (!isset($_SESSION['maxa_shop_csrf'])) {
    $_SESSION['maxa_shop_csrf'] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION['maxa_shop_csrf'];

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
        try {
            $pdo->exec("ALTER TABLE orders ADD COLUMN created_at DATETIME NULL");
        } catch (Throwable $e) {}
    } catch (Throwable $e) {}
}

/**
 * Resolve authenticated user ID from Session, Bearer token, or Cookie
 */
function shop_resolve_user_id(?PDO $pdo = null, ?string $explicitToken = null): ?int {
    if (!empty($_SESSION['user']['id'])) {
        return (int)$_SESSION['user']['id'];
    }
    if (!empty($_SESSION['benefactor_user']['id'])) {
        return (int)$_SESSION['benefactor_user']['id'];
    }
    if (!empty($_SESSION['user_id'])) {
        return (int)$_SESSION['user_id'];
    }

    $token = $explicitToken;
    if (!$token) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            $token = $matches[1];
        } elseif (!empty($_COOKIE['maksa_access_token'])) {
            $token = trim((string)$_COOKIE['maksa_access_token']);
        }
    }

    if ($token) {
        try {
            $jwtFile = __DIR__ . '/api/src/Auth/Jwt.php';
            if (file_exists($jwtFile)) {
                require_once $jwtFile;
                $payload = \Maksa\Auth\Jwt::verify($token);
                if (!empty($payload['sub'])) {
                    return (int)$payload['sub'];
                }
            }
        } catch (Throwable $e) {}
    }

    $rt = $_COOKIE['maksa_rt'] ?? $_COOKIE['maxa_refresh_token'] ?? null;
    if ($rt && $pdo) {
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM refresh_tokens WHERE token_hash = ? AND expires_at > NOW() AND revoked_at IS NULL LIMIT 1");
            $stmt->execute([hash('sha256', $rt)]);
            $uid = $stmt->fetchColumn();
            if ($uid) {
                return (int)$uid;
            }
        } catch (Throwable $e) {}
    }

    return null;
}

// ── POST Handler: Create Order ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $json = json_decode($rawInput, true);
    $isJson = is_array($json);
    $data = $isJson ? $json : $_POST;

    $authToken = !empty($data['auth_token']) ? trim((string)$data['auth_token']) : null;
    $userId = shop_resolve_user_id($pdo ?? null, $authToken);

    if (!$userId) {
        $msg = 'لطفاً ابتدا وارد حساب کاربری خود شوید.';
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $_SESSION['checkout_error'] = $msg;
            header('Location: /shop-checkout.php');
            exit;
        }
    }

    $items = $data['items'] ?? [];
    if (is_string($items)) {
        $items = json_decode($items, true) ?: [];
    }
    if (empty($items) || !is_array($items)) {
        $msg = 'سبد خرید شما خالی است';
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $_SESSION['checkout_error'] = $msg;
            header('Location: /shop-checkout.php');
            exit;
        }
    }

    $recipientName  = trim((string)($data['recipient_name'] ?? ''));
    $recipientPhone = trim((string)($data['recipient_phone'] ?? ''));
    $province       = trim((string)($data['province'] ?? ''));
    $city           = trim((string)($data['city'] ?? ''));
    $postalCode     = trim((string)($data['postal_code'] ?? ''));
    $address        = trim((string)($data['address'] ?? ''));
    $customerNote   = trim((string)($data['customer_note'] ?? ''));

    if ($recipientName === '' || $recipientPhone === '' || $province === '' || $city === '' || $address === '') {
        $msg = 'لطفاً فیلدهای الزامی را تکمیل کنید.';
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $_SESSION['checkout_error'] = $msg;
            header('Location: /shop-checkout.php');
            exit;
        }
    }

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $msg = 'خطا در اتصال به پایگاه داده.';
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            die($msg);
        }
    }

    // ── Server-Authoritative Pricing & Stock Validation in DB Transaction ──
    $pdo->beginTransaction();
    $totalPrice = 0;
    $orderItemsToInsert = [];

    foreach ($items as $item) {
        $pId = (int)($item['product_id'] ?? $item['id'] ?? 0);
        $qty = (int)($item['quantity'] ?? $item['qty'] ?? 1);
        if ($pId <= 0 || $qty <= 0) {
            continue;
        }

        $prod = shop_get_product_by_id($pdo, $pId);
        if (!$prod || $prod['status'] !== 'active' || (int)$prod['stock'] < $qty) {
            $pdo->rollBack();
            $title = $prod ? $prod['title'] : "شناسه #{$pId}";
            $msg = "موجودی محصول «{$title}» برای ثبت سفارش کافی نیست.";
            if ($isJson) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                $_SESSION['checkout_error'] = $msg;
                header('Location: /shop-checkout.php');
                exit;
            }
        }

        $unitPrice = (float)$prod['price'];
        $lineTotal = $unitPrice * $qty;
        $totalPrice += $lineTotal;

        $orderItemsToInsert[] = [
            'product_id' => (int)$prod['id'],
            'title'      => (string)$prod['title'],
            'qty'        => $qty,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ];
    }

    if (empty($orderItemsToInsert)) {
        $pdo->rollBack();
        $msg = 'سبد خرید شما خالی است';
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $_SESSION['checkout_error'] = $msg;
            header('Location: /shop-checkout.php');
            exit;
        }
    }

    $trackingCode = 'ORD-' . time() . '-' . rand(1000, 9999);
    $nowStr = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, tracking_code, order_type, payment_status, fulfillment_status,
            total_price, recipient_name, recipient_phone, province, city,
            postal_code, address, customer_note, created_at
        ) VALUES (
            ?, ?, 'product', 'pending', 'pending',
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?
        )
    ");
    $stmt->execute([
        $userId,
        $trackingCode,
        $totalPrice,
        $recipientName,
        $recipientPhone,
        $province,
        $city,
        $postalCode,
        $address,
        $customerNote,
        $nowStr,
    ]);
    $orderId = (int)$pdo->lastInsertId();

    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id, product_id, product_title, quantity, unit_price, total_price
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    foreach ($orderItemsToInsert as $ins) {
        $itemStmt->execute([
            $orderId,
            $ins['product_id'],
            $ins['title'],
            $ins['qty'],
            $ins['unit_price'],
            $ins['line_total'],
        ]);
    }

    $pdo->commit();

    $callbackUrl = '/shop-payment-callback.php?tracking_code=' . urlencode($trackingCode) . '&status=OK';

    if ($isJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'       => true,
            'tracking_code' => $trackingCode,
            'order_id'      => $orderId,
            'redirect_url'  => $callbackUrl,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        header('Location: ' . $callbackUrl);
        exit;
    }
}

// ── GET View Rendering ───────────────────────────────────────────────────────
$serverUserId = shop_resolve_user_id($pdo ?? null);
$isUserLoggedIn = ($serverUserId !== null && $serverUserId > 0);

$pageTitle = site_text('تسویه‌حساب و ثبت سفارش | فروشگاه مکسا');
render_dashboard_component('header', get_defined_vars());

$iranProvinces = [
    'تهران', 'اصفهان', 'فارس', 'خراسان رضوی', 'آذربایجان شرقی', 'آذربایجان غربی',
    'مازندران', 'گیلان', 'خوزستان', 'البرز', 'قم', 'کرمان', 'یزد', 'همدان',
    'مرکزی', 'قزوین', 'زنجان', 'سمنان', 'گلستان', 'اردبیل', 'کرمانشاه', 'لرستان',
    'کردستان', 'بوشهر', 'هرمزگان', 'سیستان و بلوچستان', 'چهارمحال و بختیاری',
    'کهگیلویه و بویراحمد', 'ایلام', 'خراسان جنوبی', 'خراسان شمالی'
];
?>

<div class="shop-checkout-page">
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
      --shop-green-subtle: #d1fae5;
      --shop-red: #ef4444;
      --shop-red-subtle: #fee2e2;
      --shop-radius: 16px;
      --shop-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
    }

    .shop-checkout-page {
      direction: var(--site-direction, rtl);
      font-family: 'Vazirmatn', sans-serif;
      color: var(--shop-dark);
      background: #fafaf9;
      min-height: 80vh;
      padding-top: 36px;
      padding-bottom: 80px;
    }

    .checkout-container {
      width: min(var(--cta-container, 1280px), 100%);
      margin-inline: auto;
      padding-inline: 20px;
      box-sizing: border-box;
    }

    /* Breadcrumbs */
    .checkout-breadcrumbs {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 13px;
      color: var(--shop-slate-500);
      margin-bottom: 24px;
    }
    .checkout-breadcrumbs a {
      color: var(--shop-slate-600);
      text-decoration: none;
      transition: color 0.15s;
    }
    .checkout-breadcrumbs a:hover {
      color: var(--shop-primary-hover);
    }

    /* Main Grid */
    .checkout-layout {
      display: grid;
      grid-template-columns: 1.5fr 1fr;
      gap: 32px;
      align-items: start;
    }
    @media (max-width: 960px) {
      .checkout-layout {
        grid-template-columns: 1fr;
      }
    }

    /* Cards */
    .checkout-card {
      background: #ffffff;
      border: 1px solid var(--shop-slate-200);
      border-radius: var(--shop-radius);
      box-shadow: var(--shop-shadow);
      padding: 28px;
      margin-bottom: 24px;
    }
    .checkout-card-header {
      display: flex;
      align-items: center;
      gap: 12px;
      padding-bottom: 18px;
      margin-bottom: 22px;
      border-bottom: 1px solid var(--shop-slate-100);
    }
    .checkout-card-icon {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--shop-primary-subtle);
      color: var(--shop-primary-hover);
      flex-shrink: 0;
    }
    .checkout-card-icon svg {
      width: 22px;
      height: 22px;
      stroke-width: 2.2;
    }
    .checkout-card-title {
      font-size: 18px;
      font-weight: 800;
      color: var(--shop-dark);
      margin: 0;
    }
    .checkout-card-desc {
      font-size: 13.5px;
      color: var(--shop-slate-500);
      margin-top: 4px;
      line-height: 1.6;
    }

    /* Form Fields */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
    }
    .form-col-full {
      grid-column: 1 / -1;
    }
    @media (max-width: 640px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
    }
    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .form-label {
      font-size: 13px;
      font-weight: 700;
      color: var(--shop-slate-800);
    }
    .form-label .req {
      color: var(--shop-red);
      margin-inline-start: 3px;
    }
    .form-input, .form-select, .form-textarea {
      width: 100%;
      box-sizing: border-box;
      font-family: inherit;
      font-size: 14px;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1.5px solid var(--shop-slate-200);
      background: #ffffff;
      color: var(--shop-dark);
      transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
      outline: none;
      border-color: var(--shop-primary);
      box-shadow: 0 0 0 3px rgba(250, 166, 26, 0.15);
    }
    .form-textarea {
      resize: vertical;
      min-height: 76px;
    }
    .form-help {
      font-size: 11.5px;
      color: var(--shop-slate-500);
    }

    /* Summary Card (Sticky) */
    .summary-card {
      position: sticky;
      top: 24px;
      background: #ffffff;
      border: 1px solid var(--shop-slate-200);
      border-radius: var(--shop-radius);
      box-shadow: var(--shop-shadow);
      padding: 24px;
    }
    .summary-title {
      font-size: 17px;
      font-weight: 800;
      margin: 0 0 16px;
      padding-bottom: 14px;
      border-bottom: 1px solid var(--shop-slate-100);
    }
    .summary-items {
      display: flex;
      flex-direction: column;
      gap: 12px;
      max-height: 280px;
      overflow-y: auto;
      margin-bottom: 18px;
      padding-inline-end: 4px;
    }
    .summary-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 8px;
      background: var(--shop-slate-50);
      border-radius: 10px;
    }
    .summary-item-img {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      object-fit: cover;
      background: var(--shop-slate-100);
      flex-shrink: 0;
    }
    .summary-item-info {
      flex: 1;
      min-width: 0;
    }
    .summary-item-title {
      font-size: 13px;
      font-weight: 700;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-bottom: 4px;
    }
    .summary-item-meta {
      font-size: 12px;
      color: var(--shop-slate-500);
    }
    .summary-item-price {
      font-size: 13px;
      font-weight: 800;
      color: var(--shop-dark);
      white-space: nowrap;
    }

    /* Summary Rows */
    .summary-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 13.5px;
      color: var(--shop-slate-600);
      margin-bottom: 10px;
    }
    .summary-row.total-row {
      font-size: 16px;
      font-weight: 900;
      color: var(--shop-dark);
      padding-top: 14px;
      margin-top: 14px;
      border-top: 2px dashed var(--shop-slate-200);
    }
    .summary-total-amount {
      color: #b45309;
      font-size: 18px;
    }

    /* Badges */
    .shipping-badge {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: 10px;
      padding: 12px;
      margin-block: 16px;
      font-size: 12.5px;
      color: #166534;
      line-height: 1.5;
    }
    .shipping-badge svg {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
      margin-top: 2px;
    }

    /* Action Buttons */
    .checkout-btn {
      width: 100%;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: linear-gradient(135deg, var(--shop-primary), var(--shop-primary-hover));
      color: #ffffff;
      border: none;
      border-radius: 12px;
      font-family: inherit;
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
      box-shadow: 0 4px 14px rgba(250, 166, 26, 0.4);
    }
    .checkout-btn:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(250, 166, 26, 0.5);
    }
    .checkout-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .trust-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-top: 20px;
      padding-top: 16px;
      border-top: 1px solid var(--shop-slate-100);
      font-size: 12px;
      color: var(--shop-slate-500);
    }
    .trust-item {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .trust-item svg {
      width: 16px;
      height: 16px;
      color: var(--shop-green);
      flex-shrink: 0;
    }

    /* Empty Cart Box */
    .empty-cart-box {
      text-align: center;
      padding: 60px 20px;
      background: #ffffff;
      border-radius: var(--shop-radius);
      border: 1px solid var(--shop-slate-200);
      box-shadow: var(--shop-shadow);
      max-width: 560px;
      margin: 40px auto;
    }
    .empty-cart-icon {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: var(--shop-primary-subtle);
      color: var(--shop-primary-hover);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
    }
    .empty-cart-icon svg {
      width: 40px;
      height: 40px;
    }

    /* Alert Notice */
    .checkout-alert {
      padding: 14px 18px;
      border-radius: 12px;
      font-size: 13.5px;
      margin-bottom: 20px;
      display: none;
    }
    .checkout-alert-error {
      background: var(--shop-red-subtle);
      color: #991b1b;
      border: 1px solid #fecaca;
    }
    .checkout-alert-success {
      background: var(--shop-green-subtle);
      color: #065f46;
      border: 1px solid #a7f3d0;
    }
  </style>

  <div class="checkout-container">
    <!-- Breadcrumbs -->
    <div class="checkout-breadcrumbs">
      <a href="/"><?= site_text('مکسا') ?></a>
      <span>/</span>
      <a href="/shop.php"><?= site_text('فروشگاه خیریه مکسا') ?></a>
      <span>/</span>
      <span><?= site_text('تسویه‌حساب و ثبت سفارش') ?></span>
    </div>

    <!-- Empty Cart Box -->
    <div id="emptyCartBox" class="empty-cart-box" style="display: none;">
      <div class="empty-cart-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
      </div>
      <h2 style="font-size: 20px; font-weight: 800; margin-bottom: 8px;"><?= site_e('سبد خرید شما خالی است') ?></h2>
      <p style="font-size: 14px; color: var(--shop-slate-500); margin-bottom: 24px;"><?= site_e('در حال حاضر سبد خرید شما خالی است.') ?></p>
      <a href="/shop.php" class="checkout-btn" style="display: inline-flex; width: auto; padding-inline: 28px; text-decoration: none;">
        <?= site_e('بازگشت به فروشگاه') ?>
      </a>
    </div>

    <!-- Main Checkout Container -->
    <div id="checkoutMainContainer" class="checkout-layout" style="display: none;">
      
      <!-- Right Column: Auth or Shipping -->
      <div class="checkout-flow-column">
        <div id="checkoutErrorBox" class="checkout-alert checkout-alert-error"></div>

        <!-- 1. Authentication Box (If Not Logged In) -->
        <div id="authCard" class="checkout-card" style="<?= $isUserLoggedIn ? 'display: none;' : '' ?>">
          <div class="checkout-card-header">
            <div class="checkout-card-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
              <h2 class="checkout-card-title"><?= site_e('ورود به حساب کاربری برای ثبت نهایی سفارش') ?></h2>
              <div class="checkout-card-desc"><?= site_e('برای تکمیل فرایند سفارش و صدور فاکتور و کد پیگیری رسمی، لطفاً شماره همراه خود را وارد نمایید. اقلام سبد خرید شما محفوظ خواهند ماند.') ?></div>
            </div>
          </div>

          <!-- Step 1: Phone input -->
          <div id="otpStepPhone">
            <div class="form-group" style="max-width: 380px; margin-bottom: 18px;">
              <label class="form-label" for="loginPhone"><?= site_e('شماره موبایل') ?><span class="req">*</span></label>
              <input type="tel" id="loginPhone" class="form-input" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" maxlength="11">
            </div>
            <button type="button" id="btnSendOtp" class="checkout-btn" style="width: auto; min-width: 180px; padding-inline: 24px;">
              <span><?= site_e('ارسال کد تأیید') ?></span>
            </button>
          </div>

          <!-- Step 2: Code input -->
          <div id="otpStepCode" style="display: none;">
            <div style="font-size: 13.5px; color: var(--shop-slate-600); margin-bottom: 14px;">
              <span>کد تأیید به شماره </span><strong id="displayPhone" dir="ltr"></strong><span> پیامک شد. </span>
              <button type="button" id="btnChangePhone" style="background:none;border:none;color:var(--shop-primary-hover);font-family:inherit;font-weight:700;cursor:pointer;padding:0 6px;">
                <?= site_e('تغییر شماره موبایل') ?>
              </button>
            </div>
            <div class="form-group" style="max-width: 240px; margin-bottom: 18px;">
              <label class="form-label" for="loginCode"><?= site_e('کد تأیید پیامک‌شده') ?><span class="req">*</span></label>
              <input type="text" id="loginCode" class="form-input" placeholder="<?= site_text('کد ۵ رقمی') ?>" dir="ltr" maxlength="8">
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
              <button type="button" id="btnVerifyOtp" class="checkout-btn" style="width: auto; min-width: 190px; padding-inline: 24px;">
                <span><?= site_e('ورود و ادامه ثبت سفارش') ?></span>
              </button>
              <button type="button" id="btnResendOtp" style="background: none; border: 1px solid var(--shop-slate-200); border-radius: 10px; height: 48px; padding-inline: 16px; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer;">
                <?= site_e('ارسال مجدد کد') ?>
              </button>
            </div>
          </div>
        </div>

        <!-- 2. Shipping Address Card -->
        <div id="shippingCard" class="checkout-card" style="<?= !$isUserLoggedIn ? 'display: none;' : '' ?>">
          <div class="checkout-card-header">
            <div class="checkout-card-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <h2 class="checkout-card-title"><?= site_e('مشخصات تحویل‌گیرنده و آدرس ارسال') ?></h2>
              <div id="shippingUserGreet" class="checkout-card-desc"></div>
            </div>
          </div>

          <form id="checkoutForm" onsubmit="return false;">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label" for="recipientName"><?= site_e('نام و نام خانوادگی تحویل‌گیرنده') ?><span class="req">*</span></label>
                <input type="text" id="recipientName" name="recipient_name" class="form-input" required>
              </div>

              <div class="form-group">
                <label class="form-label" for="recipientPhone"><?= site_e('شماره تماس تحویل‌گیرنده') ?><span class="req">*</span></label>
                <input type="tel" id="recipientPhone" name="recipient_phone" class="form-input" dir="ltr" placeholder="۰۹..." required>
              </div>

              <div class="form-group">
                <label class="form-label" for="provinceSelect"><?= site_e('استان') ?><span class="req">*</span></label>
                <select id="provinceSelect" name="province" class="form-select" required>
                  <option value="">-- انتخاب استان --</option>
                  <?php foreach ($iranProvinces as $prov): ?>
                    <option value="<?= htmlspecialchars($prov, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($prov, ENT_QUOTES, 'UTF-8') ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label" for="cityName"><?= site_e('شهر') ?><span class="req">*</span></label>
                <input type="text" id="cityName" name="city" class="form-input" required>
              </div>

              <div class="form-group">
                <label class="form-label" for="postalCode"><?= site_e('کد پستی ۱۰ رقمی') ?><span class="req">*</span></label>
                <input type="text" id="postalCode" name="postal_code" class="form-input" dir="ltr" maxlength="10" placeholder="۱۰ رقم بدون خط تیره" required>
              </div>

              <div class="form-group form-col-full">
                <label class="form-label" for="shippingAddress"><?= site_e('آدرس دقیق پستی') ?><span class="req">*</span></label>
                <textarea id="shippingAddress" name="address" class="form-textarea" placeholder="خیابان، کوچه، پلاک، طبقه و واحد" required></textarea>
              </div>

              <div class="form-group form-col-full">
                <label class="form-label" for="customerNote"><?= site_e('یادداشت یا توضیحات سفارش') ?></label>
                <textarea id="customerNote" name="customer_note" class="form-textarea" placeholder="<?= site_text('اختیاری: زمان مناسب تحویل، پلاک، زنگ یا هماهنگی بیشتر') ?>"></textarea>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Left Column: Sticky Order Summary -->
      <div class="checkout-summary-column">
        <div class="summary-card">
          <h3 class="summary-title"><?= site_e('خلاصه‌ی سفارش') ?></h3>
          
          <div id="summaryItemsList" class="summary-items"></div>

          <div class="summary-row">
            <span><?= site_e('مجموع اقلام:') ?></span>
            <span id="summarySubtotal" style="font-weight: 700;">۰ تومان</span>
          </div>

          <div class="shipping-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <div>
              <strong><?= site_e('روش ارسال: پست پیشتاز / تیپاکس (پس‌کرایه - پرداخت درب منزل)') ?></strong>
              <div style="font-size: 11.5px; opacity: 0.9; margin-top: 2px;">
                <?= site_e('هزینه ارسال به صورت پس‌کرایه است و زمان تحویل توسط مامور پست دریافت می‌شود') ?>
              </div>
            </div>
          </div>

          <div class="summary-row total-row">
            <span><?= site_e('مبلغ قابل پرداخت:') ?></span>
            <span id="summaryGrandTotal" class="summary-total-amount">۰ تومان</span>
          </div>

          <button type="button" id="btnSubmitOrder" class="checkout-btn" style="margin-top: 20px;">
            <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span id="submitOrderBtnText"><?= site_e('تکمیل سفارش و پرداخت اینترنتی') ?></span>
          </button>

          <div class="trust-list">
            <div class="trust-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <span><?= site_e('پرداخت امن بانکی') ?></span>
            </div>
            <div class="trust-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              <span><?= site_e('تضمین اصالت و کیفیت کالا') ?></span>
            </div>
            <div class="trust-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              <span><?= site_e('۱۰۰٪ سود صرف بیماران مبتلا به سرطان') ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';

  var CART_KEY = 'maxa_shop_cart';
  var TOKEN_KEY = 'maksa_access_token';
  var USER_KEY = 'maksa_benefactor_user';

  var isServerLoggedIn = <?= $isUserLoggedIn ? 'true' : 'false' ?>;

  function toFaDigits(str) {
    return String(str).replace(/[0-9]/g, function(d) {
      return '۰۱۲۳۴۵۶۷۸۹'[d];
    });
  }

  function formatToman(price) {
    var formatted = (parseInt(price, 10) || 0).toLocaleString('en-US');
    return toFaDigits(formatted) + ' تومان';
  }

  function getCart() {
    try {
      var saved = localStorage.getItem(CART_KEY);
      if (saved) {
        var parsed = JSON.parse(saved);
        if (Array.isArray(parsed)) return parsed;
      }
    } catch(e) {}
    return [];
  }

  function getToken() {
    try {
      return localStorage.getItem(TOKEN_KEY) || '';
    } catch(e) {
      return '';
    }
  }

  function setToken(tok) {
    try {
      localStorage.setItem(TOKEN_KEY, tok);
    } catch(e) {}
  }

  function getSavedUser() {
    try {
      var saved = localStorage.getItem(USER_KEY);
      if (saved) return JSON.parse(saved);
    } catch(e) {}
    return null;
  }

  function setSavedUser(usr) {
    try {
      localStorage.setItem(USER_KEY, JSON.stringify(usr));
    } catch(e) {}
  }

  function showError(msg) {
    var errBox = document.getElementById('checkoutErrorBox');
    if (errBox) {
      errBox.textContent = msg;
      errBox.style.display = 'block';
      errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  function clearError() {
    var errBox = document.getElementById('checkoutErrorBox');
    if (errBox) {
      errBox.style.display = 'none';
      errBox.textContent = '';
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    var cart = getCart();

    var emptyCartBox = document.getElementById('emptyCartBox');
    var checkoutMainContainer = document.getElementById('checkoutMainContainer');

    if (!cart || cart.length === 0) {
      if (emptyCartBox) emptyCartBox.style.display = 'block';
      if (checkoutMainContainer) checkoutMainContainer.style.display = 'none';
      return;
    }

    if (emptyCartBox) emptyCartBox.style.display = 'none';
    if (checkoutMainContainer) checkoutMainContainer.style.display = 'grid';

    // Render Summary Items
    var itemsListEl = document.getElementById('summaryItemsList');
    var subtotalEl = document.getElementById('summarySubtotal');
    var grandTotalEl = document.getElementById('summaryGrandTotal');

    var subtotal = 0;
    if (itemsListEl) {
      itemsListEl.innerHTML = '';
      cart.forEach(function(item) {
        var price = parseFloat(item.price) || 0;
        var qty = parseInt(item.quantity, 10) || 1;
        var lineTotal = price * qty;
        subtotal += lineTotal;

        var row = document.createElement('div');
        row.className = 'summary-item';
        row.innerHTML = 
          '<img class="summary-item-img" src="' + (item.image_url || '/dashboard/assets/img/product/product-1.jpg') + '" alt="' + (item.title || '') + '">' +
          '<div class="summary-item-info">' +
            '<div class="summary-item-title">' + (item.title || '') + '</div>' +
            '<div class="summary-item-meta">' + toFaDigits(qty) + ' × ' + formatToman(price) + '</div>' +
          '</div>' +
          '<div class="summary-item-price">' + formatToman(lineTotal) + '</div>';
        itemsListEl.appendChild(row);
      });
    }

    if (subtotalEl) subtotalEl.textContent = formatToman(subtotal);
    if (grandTotalEl) grandTotalEl.textContent = formatToman(subtotal);

    // Auth State Resolution
    var token = getToken();
    var authCard = document.getElementById('authCard');
    var shippingCard = document.getElementById('shippingCard');
    var shippingGreet = document.getElementById('shippingUserGreet');

    function applyUser(u) {
      if (!u) return;
      var nameInput = document.getElementById('recipientName');
      var phoneInput = document.getElementById('recipientPhone');
      var fullName = u.full_name || [u.first_name, u.last_name].filter(Boolean).join(' ').trim();
      if (nameInput && !nameInput.value && fullName) {
        nameInput.value = fullName;
      }
      if (phoneInput && !phoneInput.value && u.phone) {
        phoneInput.value = u.phone;
      }
      if (shippingGreet) {
        shippingGreet.textContent = fullName ? ('خوش آمدید، ' + fullName + ' عزیز') : 'حساب کاربری شما تأیید شد.';
      }
    }

    if (isServerLoggedIn || (token && token.length > 20)) {
      if (authCard) authCard.style.display = 'none';
      if (shippingCard) shippingCard.style.display = 'block';
      applyUser(getSavedUser());

      if (token) {
        fetch('/api/user/me', {
          headers: { 'Authorization': 'Bearer ' + token }
        }).then(function(res) {
          if (res.ok) return res.json();
          throw new Error('Not auth');
        }).then(function(data) {
          var u = data?.data?.user;
          if (u) {
            setSavedUser(u);
            applyUser(u);
          }
        }).catch(function() {
          // Keep active if server session valid
        });
      }
    }

    // OTP Auth Handling
    var btnSendOtp = document.getElementById('btnSendOtp');
    var btnVerifyOtp = document.getElementById('btnVerifyOtp');
    var btnChangePhone = document.getElementById('btnChangePhone');
    var btnResendOtp = document.getElementById('btnResendOtp');
    var inputPhone = document.getElementById('loginPhone');
    var inputCode = document.getElementById('loginCode');
    var otpStepPhone = document.getElementById('otpStepPhone');
    var otpStepCode = document.getElementById('otpStepCode');
    var displayPhone = document.getElementById('displayPhone');

    var currentPhone = '';

    if (btnSendOtp && inputPhone) {
      btnSendOtp.addEventListener('click', function() {
        var rawPhone = inputPhone.value.trim();
        if (!/^09\d{9}$/.test(rawPhone)) {
          showError('لطفاً شماره موبایل معتبر ۱۱ رقمی (مثال: ۰۹۱۲۳۴۵۶۷۸۹) وارد فرمایید.');
          return;
        }
        clearError();
        btnSendOtp.disabled = true;
        btnSendOtp.textContent = 'در حال ارسال...';

        fetch('/api/auth/otp/send', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ phone: rawPhone, purpose: 'login' })
        }).then(function(r) { return r.json(); })
          .then(function(res) {
            btnSendOtp.disabled = false;
            btnSendOtp.textContent = 'ارسال کد تأیید';
            if (res.status === 'success' || res.success) {
              currentPhone = rawPhone;
              if (displayPhone) displayPhone.textContent = rawPhone;
              if (otpStepPhone) otpStepPhone.style.display = 'none';
              if (otpStepCode) otpStepCode.style.display = 'block';
              if (inputCode) inputCode.focus();
            } else {
              showError(res.message || 'خطا در ارسال کد تأیید.');
            }
          }).catch(function() {
            btnSendOtp.disabled = false;
            btnSendOtp.textContent = 'ارسال کد تأیید';
            showError('ارتباط با سرور برقرار نشد. لطفاً مجدداً تلاش کنید.');
          });
      });
    }

    if (btnChangePhone) {
      btnChangePhone.addEventListener('click', function() {
        if (otpStepPhone) otpStepPhone.style.display = 'block';
        if (otpStepCode) otpStepCode.style.display = 'none';
        if (inputPhone) inputPhone.focus();
      });
    }

    if (btnResendOtp) {
      btnResendOtp.addEventListener('click', function() {
        if (!currentPhone) return;
        btnResendOtp.disabled = true;
        fetch('/api/auth/otp/send', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ phone: currentPhone, purpose: 'login' })
        }).then(function() {
          btnResendOtp.textContent = 'ارسال مجدد انجام شد';
          setTimeout(function() {
            btnResendOtp.disabled = false;
            btnResendOtp.textContent = 'ارسال مجدد کد';
          }, 30000);
        });
      });
    }

    if (btnVerifyOtp && inputCode) {
      btnVerifyOtp.addEventListener('click', function() {
        var code = inputCode.value.trim();
        if (!code || code.length < 4) {
          showError('لطفاً کد تأیید معتبر را وارد نمایید.');
          return;
        }
        clearError();
        btnVerifyOtp.disabled = true;
        btnVerifyOtp.textContent = 'در حال بررسی...';

        fetch('/api/auth/login-otp', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ phone: currentPhone, code: code })
        }).then(function(r) { return r.json(); })
          .then(function(res) {
            btnVerifyOtp.disabled = false;
            btnVerifyOtp.textContent = 'ورود و ادامه ثبت سفارش';
            var tok = res?.data?.access_token;
            if (tok) {
              setToken(tok);
              var usr = res?.data?.user;
              if (usr) {
                setSavedUser(usr);
                applyUser(usr);
              }
              if (authCard) authCard.style.display = 'none';
              if (shippingCard) {
                shippingCard.style.display = 'block';
                shippingCard.scrollIntoView({ behavior: 'smooth' });
              }
            } else {
              showError(res.message || 'کد تأیید نادرست است.');
            }
          }).catch(function() {
            btnVerifyOtp.disabled = false;
            btnVerifyOtp.textContent = 'ورود و ادامه ثبت سفارش';
            showError('خطا در احراز هویت. لطفاً مجدداً امتحان کنید.');
          });
      });
    }

    // Submit Order Handling
    var btnSubmitOrder = document.getElementById('btnSubmitOrder');
    var btnSubmitText = document.getElementById('submitOrderBtnText');

    if (btnSubmitOrder) {
      btnSubmitOrder.addEventListener('click', function() {
        clearError();

        // Ensure user is authenticated
        var curToken = getToken();
        if (!isServerLoggedIn && !curToken) {
          showError('لطفاً ابتدا وارد حساب کاربری خود شوید.');
          if (authCard) {
            authCard.style.display = 'block';
            authCard.scrollIntoView({ behavior: 'smooth' });
          }
          return;
        }

        var recipientName = (document.getElementById('recipientName')?.value || '').trim();
        var recipientPhone = (document.getElementById('recipientPhone')?.value || '').trim();
        var province = (document.getElementById('provinceSelect')?.value || '').trim();
        var city = (document.getElementById('cityName')?.value || '').trim();
        var postalCode = (document.getElementById('postalCode')?.value || '').trim();
        var address = (document.getElementById('shippingAddress')?.value || '').trim();
        var customerNote = (document.getElementById('customerNote')?.value || '').trim();

        if (!recipientName || !recipientPhone || !province || !city || !address) {
          showError('لطفاً تمامی فیلدهای الزامی فرم تحویل (نام، شماره تماس، استان، شهر و آدرس) را پر کنید.');
          return;
        }

        if (postalCode && !/^\d{10}$/.test(postalCode)) {
          showError('کد پستی نامعتبر است (۱۰ رقم وارد کنید).');
          return;
        }

        var orderItems = cart.map(function(it) {
          return {
            product_id: parseInt(it.id, 10),
            quantity: parseInt(it.quantity, 10) || 1
          };
        });

        var payload = {
          recipient_name: recipientName,
          recipient_phone: recipientPhone,
          province: province,
          city: city,
          postal_code: postalCode,
          address: address,
          customer_note: customerNote,
          items: orderItems,
          auth_token: curToken
        };

        btnSubmitOrder.disabled = true;
        if (btnSubmitText) btnSubmitText.textContent = 'در حال ثبت سفارش و انتقال به درگاه...';

        var headers = {
          'Content-Type': 'application/json'
        };
        if (curToken) {
          headers['Authorization'] = 'Bearer ' + curToken;
        }

        fetch('/shop-checkout.php', {
          method: 'POST',
          headers: headers,
          body: JSON.stringify(payload)
        }).then(function(r) { return r.json(); })
          .then(function(res) {
            if (res.success && res.redirect_url) {
              window.location.href = res.redirect_url;
            } else {
              btnSubmitOrder.disabled = false;
              if (btnSubmitText) btnSubmitText.textContent = 'تکمیل سفارش و پرداخت اینترنتی';
              showError(res.message || 'خطا در ثبت سفارش. لطفاً مجدداً بررسی فرمایید.');
            }
          }).catch(function(err) {
            btnSubmitOrder.disabled = false;
            if (btnSubmitText) btnSubmitText.textContent = 'تکمیل سفارش و پرداخت اینترنتی';
            showError('ارتباط با سرور با خطا مواجه شد. لطفاً دوباره تلاش نمایید.');
          });
      });
    }
  });
})();
</script>

<?php
render_dashboard_component('footer', get_defined_vars());

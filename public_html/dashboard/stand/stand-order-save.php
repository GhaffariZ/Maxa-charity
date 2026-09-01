<?php
/**
 * Secure order creation endpoint for the stand customization page.
 *
 * Requires dashboard authentication and CSRF token.
 * Uses server-authoritative pricing via StandCatalog — client-supplied
 * unit_price / total_price are completely ignored.
 */
require_once __DIR__ . '/../_guard.php';
dash_require('pages');
header('Content-Type: application/json; charset=utf-8');

// ── Enforce POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── CSRF validation ────────────────────────────────────────────────────────
csrf_check();

// ── Load the server-side catalog ───────────────────────────────────────────
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/src/Services/StandCatalog.php';
use Maksa\Services\StandCatalog;

// ── Parse input ────────────────────────────────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'داده‌ای دریافت نشد.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Extract non-financial fields only ──────────────────────────────────────
$designId = trim((string)($input['image'] ?? ''));
$date     = trim((string)($input['date'] ?? ''));
$fromUser = trim((string)($input['from_user'] ?? ''));
$toUser   = trim((string)($input['to_user'] ?? ''));
$message  = trim((string)($input['message'] ?? ''));
$address  = trim((string)($input['address'] ?? ''));

// ── Validate quantity (server-enforced bounds) ─────────────────────────────
$rawQuantity = (int)($input['quantity'] ?? 1);
try {
    $quantity = StandCatalog::validateQuantity($rawQuantity);
} catch (\InvalidArgumentException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Validate required fields ───────────────────────────────────────────────
if ($fromUser === '' || $toUser === '' || $address === '' || $designId === '') {
    echo json_encode([
        'success' => false,
        'message' => 'لطفاً تمامی فیلدهای الزامی را پر کنید.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── SERVER-AUTHORITATIVE PRICING ───────────────────────────────────────────
// Client-supplied unit_price and total_price are COMPLETELY IGNORED.
// All pricing comes from the server catalog.
$catalogEntry = StandCatalog::resolve($designId);
if ($catalogEntry === null) {
    echo json_encode([
        'success' => false,
        'message' => 'طرح انتخاب‌شده معتبر نیست.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$unitPrice  = $catalogEntry['unit_price'];
$totalPrice = StandCatalog::calculateTotal($unitPrice, $quantity);
$image      = $catalogEntry['image'];

// ── Create order with server-calculated values ─────────────────────────────
$DB   = require $_SERVER['DOCUMENT_ROOT'] . '/core/db-config.php';
$pdo  = $dash_pdo ?? dash_pdo();
$user = dash_user();

$trackingCode = 'ORD-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

$stmt = $pdo->prepare("
    INSERT INTO orders (user_id, tracking_code, image, order_date, quantity, unit_price, total_price, from_user, to_user, message, address, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->execute([
    $user['id'] ?? null,
    $trackingCode,
    $image,
    $date,
    $quantity,
    $unitPrice,
    $totalPrice,
    $fromUser,
    $toUser,
    $message,
    $address,
]);

echo json_encode([
    'success' => true,
    'tracking_code' => $trackingCode,
    'unit_price'    => $unitPrice,
    'total_price'   => $totalPrice,
], JSON_UNESCAPED_UNICODE);

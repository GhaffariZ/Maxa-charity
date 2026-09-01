<?php
/**
 * DEPRECATED — This endpoint has been disabled for security reasons.
 *
 * The legacy order creation path accepted client-supplied pricing
 * (unit_price, total_price) without authentication or server-side
 * validation, allowing price manipulation.
 *
 * All orders must now be created through the secure API endpoint:
 *   POST /api/orders  (requires JWT authentication)
 *
 * See: StandCatalog.php for server-authoritative pricing.
 */
header('Content-Type: application/json; charset=utf-8');
http_response_code(410);
echo json_encode([
    'success' => false,
    'message' => 'این endpoint دیگر فعال نیست. لطفاً از API امن استفاده کنید.',
], JSON_UNESCAPED_UNICODE);
exit;

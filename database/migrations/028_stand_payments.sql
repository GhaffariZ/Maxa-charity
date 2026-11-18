-- Stand receipts are separate from panel_donations. Legacy orders are NOT paid receipts.
ALTER TABLE orders ADD COLUMN IF NOT EXISTS checkout_key CHAR(64) NULL;
ALTER TABLE orders ADD UNIQUE KEY IF NOT EXISTS uq_orders_checkout_key (checkout_key);

CREATE TABLE IF NOT EXISTS stand_payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    branch_id INT UNSIGNED NOT NULL,
    source VARCHAR(20) NOT NULL DEFAULT 'stands',
    receipt_token CHAR(64) NOT NULL,
    center_slug VARCHAR(100) NOT NULL,
    gateway VARCHAR(20) NOT NULL DEFAULT 'vandar',
    amount BIGINT UNSIGNED NOT NULL COMMENT 'Toman; immutable order price at checkout',
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    remote_payment_id VARCHAR(500) NULL COMMENT 'Direct gateway authority/token',
    gateway_ref_id VARCHAR(100) NULL,
    redirect_data LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME NULL COMMENT 'Asia/Tehran; authoritative verification time',
    UNIQUE KEY uq_stand_payment_order (order_id),
    UNIQUE KEY uq_stand_receipt (receipt_token),
    UNIQUE KEY uq_stand_remote_payment (remote_payment_id),
    UNIQUE KEY uq_stand_gateway_ref (gateway_ref_id),
    KEY idx_stand_finance (branch_id, status, paid_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

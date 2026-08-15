<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Config;
use Maksa\Core\Response;
use Maksa\Core\Security;
use Maksa\Core\Database;

class OrderController
{
    public static function create(): void
    {
        $user = require_auth();

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            Response::json(['error' => ['code' => 'invalid_payload', 'message' => 'Invalid JSON payload']], 400);
        }

        $image = $data['image'] ?? '';
        $order_date = date('Y/m/d');
        $quantity = (int)($data['quantity'] ?? 1);
        $unit_price = (int)($data['unit_price'] ?? 0);
        $total_price = $quantity * $unit_price;
        $from_user = $data['from_user'] ?? '';
        $to_user = $data['to_user'] ?? '';
        $message = $data['message'] ?? '';
        $address = $data['address'] ?? '';

        if (empty($from_user) || empty($to_user) || empty($address)) {
            Response::json(['error' => ['code' => 'validation_failed', 'message' => 'Required fields missing']], 400);
        }

        $tracking_code = 'ORD-' . strtoupper(substr(uniqid(), -6));

        $pdo = Database::getPDO();
        
        try {
            // Auto-migrate table if needed
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `user_id` INT(11) NULL AFTER `id`");
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `tracking_code` VARCHAR(50) NULL AFTER `user_id`");
        } catch (\Throwable $e) {}

        $stmt = $pdo->prepare("
            INSERT INTO `orders` 
            (`user_id`, `tracking_code`, `image`, `order_date`, `quantity`, `unit_price`, `total_price`, `from_user`, `to_user`, `message`, `address`, `created_at`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $user['id'],
            $tracking_code,
            $image,
            $order_date,
            $quantity,
            $unit_price,
            $total_price,
            $from_user,
            $to_user,
            $message,
            $address
        ]);

        Response::json([
            'message' => 'Order created successfully',
            'tracking_code' => $tracking_code
        ]);
    }

    public static function getMyOrders(): void
    {
        $user = require_auth();
        $pdo = Database::getPDO();
        
        try {
            // Auto-migrate table if needed
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `user_id` INT(11) NULL AFTER `id`");
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `tracking_code` VARCHAR(50) NULL AFTER `user_id`");
        } catch (\Throwable $e) {}

        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `user_id` = ? ORDER BY `created_at` DESC");
        $stmt->execute([$user['id']]);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        Response::json(['orders' => $orders]);
    }
}

<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Database;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;

final class OrderController
{
    public function create(Request $request): void
    {
        $userId = $request->userId();

        $data = (new Validator($request->body))
            ->string('from_user', max: 255)
            ->string('to_user', max: 255)
            ->string('address', max: 2000)
            ->string('message', max: 2000, required: false)
            ->string('image', max: 500, required: false)
            ->int('quantity', min: 1, max: 100, required: false)
            ->int('unit_price', min: 0, required: false)
            ->validated();

        $image = $data['image'] ?? '';
        $orderDate = date('Y/m/d');
        $quantity = (int)($data['quantity'] ?? 1);
        $unitPrice = (int)($data['unit_price'] ?? 0);
        $totalPrice = $quantity * $unitPrice;
        $fromUser = $data['from_user'];
        $toUser = $data['to_user'];
        $message = $data['message'] ?? '';
        $address = $data['address'];

        $trackingCode = 'ORD-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        $pdo = Database::connection();
        
        try {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `user_id` BIGINT(20) UNSIGNED NULL AFTER `id`");
        } catch (\Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `tracking_code` VARCHAR(50) NULL AFTER `user_id`");
        } catch (\Throwable $e) {}

        $stmt = $pdo->prepare("
            INSERT INTO `orders` 
            (`user_id`, `tracking_code`, `image`, `order_date`, `quantity`, `unit_price`, `total_price`, `from_user`, `to_user`, `message`, `address`, `created_at`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $userId,
            $trackingCode,
            $image,
            $orderDate,
            $quantity,
            $unitPrice,
            $totalPrice,
            $fromUser,
            $toUser,
            $message,
            $address
        ]);

        Response::success([
            'message' => 'سفارش با موفقیت ثبت شد.',
            'tracking_code' => $trackingCode
        ], 201);
    }

    public function getMyOrders(Request $request): void
    {
        $userId = $request->userId();
        $pdo = Database::connection();
        
        try {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `user_id` BIGINT(20) UNSIGNED NULL AFTER `id`");
        } catch (\Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `tracking_code` VARCHAR(50) NULL AFTER `user_id`");
        } catch (\Throwable $e) {}

        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `user_id` = ? ORDER BY `created_at` DESC");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        Response::success(['orders' => $orders]);
    }
}

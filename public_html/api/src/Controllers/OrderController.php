<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Database;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;

final class OrderController
{
    public function create(Request $request): void
    {
        $userId = $request->userId();
        $body = is_array($request->body) ? $request->body : [];

        $senderName = trim((string)($body['sender_name'] ?? $body['from_user'] ?? ''));
        $senderPhone = trim((string)($body['sender_phone'] ?? ''));
        $fromUser = $senderName;
        if ($senderPhone !== '' && !str_contains($fromUser, $senderPhone)) {
            $fromUser .= " ($senderPhone)";
        }

        $toUser = trim((string)($body['receiver_name'] ?? $body['to_user'] ?? ''));
        $address = trim((string)($body['event_address'] ?? $body['address'] ?? ''));
        $message = trim((string)($body['message'] ?? ''));
        $image = trim((string)($body['image'] ?? ''));

        $eventDate = trim((string)($body['event_date'] ?? $body['order_date'] ?? ''));
        $eventTime = trim((string)($body['event_time'] ?? ''));
        $orderDate = $eventDate !== '' ? ($eventDate . ($eventTime !== '' ? " - $eventTime" : '')) : date('Y/m/d');

        $quantity = max(1, (int)($body['quantity'] ?? 1));
        $unitPrice = max(0, (int)($body['unit_price'] ?? 0));
        $totalPrice = $quantity * $unitPrice;

        if ($fromUser === '' || $toUser === '' || $address === '') {
            throw ApiException::validation('لطفاً تمامی فیلدهای الزامی (نام فرستنده، نام گیرنده و آدرس) را پر کنید.', [
                'from_user' => $fromUser === '' ? 'نام سفارش‌دهنده الزامی است.' : null,
                'to_user'   => $toUser === '' ? 'نام دریافت‌کننده الزامی است.' : null,
                'address'   => $address === '' ? 'آدرس محل برگزاری الزامی است.' : null,
            ]);
        }

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

        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `user_id` = ? ORDER BY `id` DESC");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        Response::success(['orders' => $orders]);
    }
}

<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Database;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Services\StandCatalog;
use PDO;
use Throwable;

final class OrderController
{
    public function __construct(private ?\StandPayments $standPayments = null) {}
    private static function ensureOrdersSchema(PDO $pdo): void
    {
        try {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `user_id` BIGINT(20) UNSIGNED NULL AFTER `id`");
        } catch (Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `tracking_code` VARCHAR(50) NULL AFTER `user_id`");
        } catch (Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `branch_id` INT UNSIGNED NULL AFTER `user_id`");
        } catch (Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `stand_id` INT UNSIGNED NULL AFTER `branch_id`");
        } catch (Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `province` VARCHAR(100) NULL AFTER `address`");
        } catch (Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `city` VARCHAR(100) NULL AFTER `province`");
        } catch (Throwable $e) {}
    }

    public function create(Request $request): void
    {
        $userId = $request->userId();
        $body = is_array($request->body) ? $request->body : [];
        $pdo = Database::connection();
        self::ensureOrdersSchema($pdo);

        $checkoutKey = trim((string)($body['checkout_key'] ?? ''));
        if ($checkoutKey !== '' && !preg_match('/^[A-Za-z0-9_-]{16,80}$/D', $checkoutKey)) {
            throw ApiException::badRequest('شناسه ثبت سفارش نامعتبر است.', 'invalid_checkout_key');
        }
        $checkoutKey = $checkoutKey !== '' ? hash('sha256', $userId . ':' . $checkoutKey) : null;
        if ($checkoutKey !== null) {
            $existing = $pdo->prepare('SELECT * FROM orders WHERE checkout_key = ? AND user_id = ? LIMIT 1');
            $existing->execute([$checkoutKey, $userId]);
            if ($order = $existing->fetch(PDO::FETCH_ASSOC)) $this->createdResponse($order);
        }

        // ── ۱. استخراج اطلاعات هویتی و غیرمالی ────────────────────────────────
        $senderName = trim((string)($body['sender_name'] ?? $body['from_user'] ?? ''));
        $senderPhone = trim((string)($body['sender_phone'] ?? ''));
        $fromUser = $senderName;
        if ($senderPhone !== '' && !str_contains($fromUser, $senderPhone)) {
            $fromUser .= " ($senderPhone)";
        }

        $toUser = trim((string)($body['receiver_name'] ?? $body['to_user'] ?? ''));
        $address = trim((string)($body['event_address'] ?? $body['address'] ?? ''));
        $message = trim((string)($body['message'] ?? ''));

        $province = trim((string)($body['province'] ?? ''));
        $city = trim((string)($body['city'] ?? ''));
        $branchId = (int)($body['branch_id'] ?? 0);
        $standId = (int)($body['stand_id'] ?? 0);

        $eventDate = trim((string)($body['event_date'] ?? $body['order_date'] ?? ''));
        $eventTime = trim((string)($body['event_time'] ?? ''));
        $orderDate = $eventDate !== '' ? ($eventDate . ($eventTime !== '' ? " - $eventTime" : '')) : date('Y/m/d');

        // ── ۲. اعتبارسنجی تعداد (Server-enforced bounds) ──────────────────────
        $rawQuantity = (int)($body['quantity'] ?? 1);
        try {
            $quantity = StandCatalog::validateQuantity($rawQuantity);
        } catch (\InvalidArgumentException $e) {
            throw ApiException::badRequest($e->getMessage(), 'invalid_quantity');
        }

        // ── ۳. اعتبارسنجی فیلدهای الزامی فرم ─────────────────────────────────
        if ($fromUser === '' || $toUser === '' || $address === '') {
            throw ApiException::validation('لطفاً تمامی فیلدهای الزامی (نام فرستنده، نام گیرنده و آدرس) را پر کنید.', [
                'from_user' => $fromUser === '' ? 'نام سفارش‌دهنده الزامی است.' : null,
                'to_user'   => $toUser === '' ? 'نام دریافت‌کننده الزامی است.' : null,
                'address'   => $address === '' ? 'آدرس محل برگزاری الزامی است.' : null,
            ]);
        }

        // ── ۴. اعتبارسنجی امنیتی شعبه و استند (Server-Authoritative Pricing) ──
        $selectedStand = null;
        $selectedBranch = null;

        if ($standId > 0) {
            $stmt = $pdo->prepare("
                SELECT s.*, b.id AS b_id, b.name AS b_name, b.province AS b_prov, b.city AS b_city, b.is_hq, b.status AS b_status
                FROM stands s
                INNER JOIN branches b ON s.branch_id = b.id
                WHERE s.id = ? AND s.is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$standId]);
            $selectedStand = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            if (!$selectedStand) {
                throw ApiException::badRequest('استند انتخاب‌شده معتبر یا فعال نیست.', 'invalid_stand');
            }

            // دفتر مرکزی به هیچ عنوان نباید استند ثبت کند
            if ((int)$selectedStand['is_hq'] === 1 || $selectedStand['b_status'] !== 'active') {
                throw ApiException::badRequest('شعبه انتخاب‌شده مجاز به ارائه استند نیست.', 'branch_not_allowed');
            }

            $branchId = (int)$selectedStand['b_id'];
            if ($province === '') {
                $province = (string)($selectedStand['b_prov'] ?: $selectedStand['b_name']);
            }
            if ($city === '') {
                $city = (string)($selectedStand['b_city'] ?: $selectedStand['b_name']);
            }

            $unitPrice = (int)$selectedStand['unit_price'];
            $image = (string)$selectedStand['image'];
        } else {
            // حالتی که کاربر از طرح‌های لگاسی یا کاتالوگ عمومی انتخاب کرده است
            $designId = trim((string)($body['design_id'] ?? $body['image'] ?? ''));

            // اگر branch_id ارسال شده بود، اعتبارسنجی شعبه
            if ($branchId > 0) {
                $stmt = $pdo->prepare("SELECT * FROM branches WHERE id = ? AND is_hq = 0 AND status = 'active' LIMIT 1");
                $stmt->execute([$branchId]);
                $selectedBranch = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } elseif ($province !== '') {
                $stmt = $pdo->prepare("SELECT * FROM branches WHERE (province = ? OR name LIKE ?) AND is_hq = 0 AND status = 'active' LIMIT 1");
                $stmt->execute([$province, "%$province%"]);
                $selectedBranch = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            if (!$selectedBranch) {
                // انتساب به اولین شعبه غیر ستادی فعال
                $stmt = $pdo->query("SELECT * FROM branches WHERE is_hq = 0 AND status = 'active' ORDER BY id ASC LIMIT 1");
                $selectedBranch = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            if (!$selectedBranch) {
                throw ApiException::badRequest('شعبه فعالی برای دریافت سفارش استند یافت نشد.', 'no_active_branch');
            }

            $branchId = (int)$selectedBranch['id'];
            if ($province === '') {
                $province = (string)($selectedBranch['province'] ?: $selectedBranch['name']);
            }
            if ($city === '') {
                $city = (string)($selectedBranch['city'] ?: $selectedBranch['name']);
            }

            // استخراج قیمت بر اساس کاتالوگ
            $catalogEntry = StandCatalog::resolve($designId);
            if ($catalogEntry !== null) {
                $unitPrice = $catalogEntry['unit_price'];
                $image = $catalogEntry['image'];
            } elseif ($designId !== '') {
                throw ApiException::badRequest('طرح انتخاب‌شده معتبر نیست.', 'invalid_design');
            } else {
                // واکشی اولین استند شعبه
                $stmt = $pdo->prepare("SELECT * FROM stands WHERE branch_id = ? AND is_active = 1 ORDER BY id ASC LIMIT 1");
                $stmt->execute([$branchId]);
                $stRow = $stmt->fetch(PDO::FETCH_ASSOC);
                $unitPrice = $stRow ? (int)$stRow['unit_price'] : 300000;
                $image = $stRow ? (string)$stRow['image'] : '/dashboard/components/event-cards/images/1-removebg-preview.png';
                $standId = $stRow ? (int)$stRow['id'] : null;
            }
        }

        // محاسبه قطعی قیمت کل در سمت سرور
        $totalPrice = $unitPrice * $quantity;
        if ($totalPrice < 1000 || $totalPrice > 500000000) {
            throw ApiException::badRequest('مبلغ سفارش برای پرداخت معتبر نیست.', 'invalid_amount');
        }
        $enabled = $pdo->prepare("SELECT 1 FROM branch_features WHERE branch_id = ? AND feature = 'stands' AND enabled = 1");
        $enabled->execute([$branchId]);
        if (!$enabled->fetchColumn()) throw ApiException::badRequest('سفارش استند برای این شعبه فعال نیست.', 'stands_disabled');

        // ── ۵. ایجاد سفارش و ذخیره در دیتابیس ─────────────────────────────────
        $trackingCode = 'ORD-' . strtoupper(bin2hex(random_bytes(8)));

        $stmt = $pdo->prepare("
            INSERT INTO `orders`
            (`user_id`, `tracking_code`, `branch_id`, `stand_id`, `province`, `city`, `image`, `order_date`, `quantity`, `unit_price`, `total_price`, `from_user`, `to_user`, `message`, `address`, `checkout_key`, `created_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        try { $stmt->execute([
            $userId,
            $trackingCode,
            $branchId,
            $standId > 0 ? $standId : null,
            $province,
            $city,
            $image,
            $orderDate,
            $quantity,
            $unitPrice,
            $totalPrice,
            $fromUser,
            $toUser,
            $message,
            $address,
            $checkoutKey,
        ]); } catch (\PDOException $e) {
            if ($checkoutKey !== null && $e->getCode() === '23000') {
                $existing->execute([$checkoutKey, $userId]);
                if ($order = $existing->fetch(PDO::FETCH_ASSOC)) $this->createdResponse($order);
            }
            throw $e;
        }

        Response::success([
            'message'       => 'سفارش ثبت شد؛ برای نهایی‌شدن، پرداخت را انجام دهید.',
            'tracking_code' => $trackingCode,
            'branch_id'     => $branchId,
            'unit_price'    => $unitPrice,
            'total_price'   => $totalPrice,
            'province'      => $province,
            'city'          => $city,
        ], 201);
    }

    private function createdResponse(array $order): void
    {
        Response::success(array_intersect_key($order, array_flip([
            'tracking_code', 'branch_id', 'unit_price', 'total_price', 'province', 'city'
        ])), 201);
    }

    private function payments(): \StandPayments
    {
        require_once dirname(__DIR__, 3) . '/core/stand-payments.php';
        return $this->standPayments ?? new \StandPayments(Database::connection());
    }

    public function startPayment(Request $request): void
    {
        $payments = $this->payments();
        try { $order = $payments->orderForUser((string)$request->params['tracking'], $request->userId()); }
        catch (\StandPaymentException $e) { throw ApiException::notFound('سفارش یافت نشد.'); }
        $profile = (new \Maksa\Repositories\ProfileRepository())->fullProfile($request->userId());
        try {
            $result = $payments->start($order, (string)($_SERVER['HTTP_HOST'] ?? ''),
                (string)($request->body['mobile'] ?? $profile['phone'] ?? ''), (string)$order['from_user']);
        } catch (\StandPaymentException $e) {
            throw ApiException::badRequest($e->getMessage(), 'stand_payment_unavailable');
        }
        Response::success($result);
    }

    public function paymentStatus(Request $request): void
    {
        $payments = $this->payments();
        try { $order = $payments->orderForUser((string)$request->params['tracking'], $request->userId()); }
        catch (\StandPaymentException $e) { throw ApiException::notFound('سفارش یافت نشد.'); }
        $receipt = $payments->byOrder((int)$order['id']);
        if ($receipt) $receipt = $payments->sync($receipt);
        Response::success(['tracking_code'=>$order['tracking_code'], 'source'=>'stands',
            'status'=>$receipt['status'] ?? 'unpaid', 'amount'=>(int)$order['total_price']]);
    }

    public function getMyOrders(Request $request): void
    {
        $userId = $request->userId();
        $pdo = Database::connection();
        self::ensureOrdersSchema($pdo);

        $stmt = $pdo->prepare("
            SELECT o.*, b.name AS branch_name, s.title AS stand_title,
                   COALESCE(sp.status, 'unpaid') AS payment_status, sp.paid_at
            FROM `orders` o
            LEFT JOIN `branches` b ON o.branch_id = b.id
            LEFT JOIN `stands` s ON o.stand_id = s.id
            LEFT JOIN stand_payments sp ON sp.order_id = o.id
            WHERE o.`user_id` = ? 
            ORDER BY o.`id` DESC
        ");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success(['orders' => $orders]);
    }
}

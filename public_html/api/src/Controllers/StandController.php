<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Database;
use Maksa\Core\Request;
use Maksa\Core\Response;
use PDO;
use Throwable;

final class StandController
{
    /**
     * اطمینان از وجود جداول و ستون‌های مورد نیاز (Self-healing schema migration)
     */
    private static function ensureSchema(PDO $pdo): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            $pdo->exec("ALTER TABLE `branches` ADD COLUMN `province` VARCHAR(100) NULL AFTER `name`");
        } catch (Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE `branches` ADD COLUMN `city` VARCHAR(100) NULL AFTER `province`");
        } catch (Throwable $e) {}

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `stands` (
                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `branch_id` INT UNSIGNED NOT NULL,
                  `title` VARCHAR(191) NOT NULL,
                  `stand_type` ENUM('congrats', 'condolence') NOT NULL DEFAULT 'congrats',
                  `image` VARCHAR(255) NOT NULL,
                  `unit_price` BIGINT UNSIGNED NOT NULL DEFAULT 0,
                  `description` TEXT NULL,
                  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                  `sort_order` INT NOT NULL DEFAULT 0,
                  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_stands_branch` (`branch_id`),
                  KEY `idx_stands_active` (`is_active`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
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

        $checked = true;
    }

    /**
     * GET /api/stands/provinces
     * دریافت لیست استان‌هایی که شعبه فعال با فیچر stands و حداقل یک استند فعال دارند.
     * دفتر مرکزی (is_hq = 1) هرگز استندی ارائه نمی‌کند.
     */
    public function getProvinces(Request $request): void
    {
        $pdo = Database::connection();
        self::ensureSchema($pdo);

        // واکشی استان‌ها و شهرهای شعب فعال دارای استند فعال (بدون ستاد مرکزی)
        $sql = "
            SELECT DISTINCT
                b.id AS branch_id,
                b.name AS branch_name,
                COALESCE(b.province, b.name) AS province,
                COALESCE(b.city, b.name) AS city,
                COUNT(s.id) AS stands_count
            FROM branches b
            INNER JOIN stands s ON s.branch_id = b.id AND s.is_active = 1
            LEFT JOIN branch_features bf ON bf.branch_id = b.id AND bf.feature = 'stands'
            WHERE b.is_hq = 0 
              AND b.status = 'active'
              AND (bf.enabled = 1 OR bf.enabled IS NULL)
            GROUP BY b.id, b.name, b.province, b.city
            HAVING stands_count > 0
            ORDER BY province ASC, city ASC
        ";

        try {
            $stmt = $pdo->query($sql);
            $branchesWithStands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $branchesWithStands = [];
        }

        // اگر هیچ استندی هنوز در جدول ثبت نشده باشد، شعب غیر مرکزی نمونه را برمی‌گردانیم تا کاتالوگ آماده باشد
        if (empty($branchesWithStands)) {
            try {
                $stmt = $pdo->query("
                    SELECT id AS branch_id, name AS branch_name, 
                           COALESCE(province, name) AS province, 
                           COALESCE(city, name) AS city
                    FROM branches 
                    WHERE is_hq = 0 AND status = 'active'
                    ORDER BY id ASC
                ");
                $branchesWithStands = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
                $branchesWithStands = [];
            }
        }

        // تجمیع به تفکیک استان و شهرهای تحت پوشش
        $provincesMap = [];
        foreach ($branchesWithStands as $row) {
            $prov = trim((string)$row['province']);
            if ($prov === '' || str_contains($prov, 'ستاد') || str_contains($prov, 'مرکزی')) {
                continue;
            }
            if (!isset($provincesMap[$prov])) {
                $provincesMap[$prov] = [
                    'province'   => $prov,
                    'branch_id'  => (int)$row['branch_id'],
                    'cities'     => [],
                ];
            }
            $cityName = trim((string)$row['city']);
            if ($cityName !== '' && !in_array($cityName, $provincesMap[$prov]['cities'], true)) {
                $provincesMap[$prov]['cities'][] = $cityName;
            }
        }

        Response::success(array_values($provincesMap));
    }

    /**
     * GET /api/stands?province=...[&branch_id=...]
     * دریافت استندهای فعال یک شعبه بر اساس نام استان یا شناسه شعبه.
     */
    public function getStandsByProvince(Request $request): void
    {
        $pdo = Database::connection();
        self::ensureSchema($pdo);

        $province = trim((string)($request->query['province'] ?? ''));
        $branchId = (int)($request->query['branch_id'] ?? 0);

        $targetBranch = null;

        if ($branchId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM branches WHERE id = ? AND is_hq = 0 AND status = 'active' LIMIT 1");
            $stmt->execute([$branchId]);
            $targetBranch = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } elseif ($province !== '') {
            $stmt = $pdo->prepare("
                SELECT * FROM branches 
                WHERE (province = ? OR name LIKE ?) 
                  AND is_hq = 0 
                  AND status = 'active' 
                ORDER BY id ASC LIMIT 1
            ");
            $stmt->execute([$province, "%$province%"]);
            $targetBranch = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        if (!$targetBranch) {
            // اگر استانی انتخاب نشده بود یا شعبه‌ای یافت نشد، اولین شعبه مجاز استند را برمی‌داریم
            $stmt = $pdo->query("SELECT * FROM branches WHERE is_hq = 0 AND status = 'active' ORDER BY id ASC LIMIT 1");
            $targetBranch = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        if (!$targetBranch) {
            Response::success([
                'branch' => null,
                'stands' => []
            ]);
            return;
        }

        $bId = (int)$targetBranch['id'];

        // بررسی اینکه آیا برای این شعبه استندی ثبت شده است یا خیر
        $stmt = $pdo->prepare("
            SELECT id, branch_id, title, stand_type, image, unit_price, description, is_active, sort_order 
            FROM stands 
            WHERE branch_id = ? AND is_active = 1 
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$bId]);
        $stands = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // اگر این شعبه استند اختصاصی در دیتابیس نداشت، ۴ استند پیش‌فرض مکسا را برایش اینسرت می‌کنیم تا همیشه نمایش داده شود
        if (empty($stands)) {
            $defaults = [
                ['title' => 'استند تبریک و شادباش - طرح اول', 'type' => 'congrats', 'image' => '/dashboard/components/event-cards/images/1-removebg-preview.png', 'price' => 300000, 'desc' => 'با سفارش این استند، ضمن تبریک به عزیزانتان، حامی بیماران مبتلا به سرطان باشید.'],
                ['title' => 'استند تبریک و شادباش - طرح دوم', 'type' => 'congrats', 'image' => '/dashboard/components/event-cards/images/2-removebg-preview.png', 'price' => 350000, 'desc' => 'شادی‌های خود را با مهربانی پیوند بزنید.'],
                ['title' => 'استند تسلیت و ابراز همدردی - طرح اول', 'type' => 'condolence', 'image' => '/dashboard/components/event-cards/images/3-removebg-preview.png', 'price' => 300000, 'desc' => 'تسلی بخش دل بازماندگان و امیدی برای بیماران سرطانی.'],
                ['title' => 'استند تسلیت و ابراز همدردی - طرح دوم', 'type' => 'condolence', 'image' => '/dashboard/components/event-cards/images/4-removebg-preview.png', 'price' => 400000, 'desc' => 'با اهدای هزینه تاج گل به خیریه، نامی ماندگار از عزیز از دست رفته به یادگار بگذارید.'],
            ];
            $ins = $pdo->prepare("INSERT INTO stands (branch_id, title, stand_type, image, unit_price, description, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, 1, ?)");
            $orderNum = 1;
            foreach ($defaults as $d) {
                try {
                    $ins->execute([$bId, $d['title'], $d['type'], $d['image'], $d['price'], $d['desc'], $orderNum++]);
                } catch (Throwable $e) {}
            }

            $stmt->execute([$bId]);
            $stands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // فرمت کردن مبالغ و افزودن نام شهر و استان شعبه
        $formattedStands = array_map(function ($s) use ($targetBranch) {
            $priceInt = (int)$s['unit_price'];
            return [
                'id'          => (int)$s['id'],
                'branch_id'   => (int)$s['branch_id'],
                'branch_name' => $targetBranch['name'],
                'province'    => $targetBranch['province'] ?: $targetBranch['name'],
                'city'        => $targetBranch['city'] ?: $targetBranch['name'],
                'title'       => $s['title'],
                'stand_type'  => $s['stand_type'],
                'image'       => $s['image'],
                'unit_price'  => $priceInt,
                'price_label' => number_format($priceInt) . ' تومان',
                'description' => $s['description'] ?? '',
                'sort_order'  => (int)$s['sort_order'],
            ];
        }, $stands);

        Response::success([
            'branch' => [
                'id'       => $bId,
                'name'     => $targetBranch['name'],
                'province' => $targetBranch['province'] ?: $targetBranch['name'],
                'city'     => $targetBranch['city'] ?: $targetBranch['name'],
            ],
            'stands' => $formattedStands
        ]);
    }
}

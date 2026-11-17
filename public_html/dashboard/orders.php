<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../core/shop-db.php';

// Safe self-healing schema migration
if ($pdo) {
    try {
        shop_ensure_schema($pdo);
    } catch (Throwable $e) {}
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

$isSuper = dash_is_super();
$isHq = dash_is_hq_view();
$activeBranchId = dash_active_branch_id();

// Access check: Super Admin, branches with stands permission, or users with pages permission
if (!$isSuper && !dash_can('stands') && !dash_can('pages')) {
    http_response_code(403);
    exit('۴۰۳ | دسترسی به کارتابل سفارشات برای این شعبه مجاز نمی‌باشد.');
}

/**
 * Helper to convert Persian/Arabic numerals to standard ASCII digits
 */
function normalize_digits_str(string $s): string {
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    return str_replace($ar, $en, str_replace($fa, $en, $s));
}

// Status definitions & labels
$fulfillmentStatusLabels = [
    'pending' => 'در انتظار پردازش',
    'processing' => 'در حال آماده‌سازی',
    'shipped' => 'ارسال شده',
    'delivered' => 'تحویل شده',
    'cancelled' => 'لغو شده',
];

$paymentStatusLabels = [
    'paid' => 'پرداخت شده',
    'pending' => 'در انتظار پرداخت',
    'failed' => 'پرداخت ناموفق',
];

// Handle POST action: update_fulfillment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_fulfillment') {
    csrf_check();

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
        || isset($_POST['ajax']);

    $orderId = (int)($_POST['order_id'] ?? 0);
    $status = trim((string)($_POST['fulfillment_status'] ?? ''));
    $carrier = trim((string)($_POST['carrier'] ?? ''));
    $trackingCode = normalize_digits_str(trim((string)($_POST['postal_tracking_code'] ?? '')));

    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    if ($orderId <= 0 || !in_array($status, $validStatuses, true)) {
        if ($isAjax) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'اطلاعات ارسالی نامعتبر است.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $_SESSION['flash_error'] = 'اطلاعات وضعیت ارسال نامعتبر است.';
        header('Location: orders.php');
        exit;
    }

    // Permission and tenant verification
    $checkStmt = $pdo->prepare("SELECT id, branch_id, order_type FROM orders WHERE id = ?");
    $checkStmt->execute([$orderId]);
    $targetOrder = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$targetOrder) {
        if ($isAjax) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'سفارش مورد نظر یافت نشد.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $_SESSION['flash_error'] = 'سفارش مورد نظر یافت نشد.';
        header('Location: orders.php');
        exit;
    }

    if (!$isSuper && !$isHq) {
        $canAccess = false;
        if (($targetOrder['order_type'] ?? '') === 'product' && dash_can('pages')) {
            $canAccess = true;
        } elseif ((int)($targetOrder['branch_id'] ?? 0) === (int)$activeBranchId && dash_can('stands')) {
            $canAccess = true;
        }
        if (!$canAccess) {
            if ($isAjax) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'شما دسترسی مجاز برای ویرایش این سفارش را ندارید.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            http_response_code(403);
            exit('۴۰۳ | عدم دسترسی');
        }
    }

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $nowExpr = ($driver === 'sqlite') ? 'CURRENT_TIMESTAMP' : 'NOW()';

    $stmt = $pdo->prepare("
        UPDATE orders 
        SET fulfillment_status = ?, carrier = ?, postal_tracking_code = ?,
            shipped_at = CASE WHEN ? IN ('shipped', 'delivered') AND (shipped_at IS NULL OR shipped_at = '') THEN {$nowExpr} ELSE shipped_at END
        WHERE id = ?
    ");
    $stmt->execute([$status, $carrier, $trackingCode, $status, $orderId]);

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'وضعیت ارسال با موفقیت به‌روزرسانی شد.',
            'order_id' => $orderId,
            'fulfillment_status' => $status,
            'fulfillment_status_label' => $fulfillmentStatusLabels[$status] ?? $status,
            'carrier' => $carrier,
            'postal_tracking_code' => $trackingCode
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $_SESSION['flash_success'] = 'وضعیت ارسال با موفقیت به‌روزرسانی شد.';
    $redirectParams = array_diff_key($_GET, ['updated' => 1]);
    $redirectUrl = 'orders.php' . (!empty($redirectParams) ? ('?' . http_build_query($redirectParams)) : '');
    header('Location: ' . $redirectUrl);
    exit;
}

// Branches list for Super Admin dropdown filter (only in HQ view)
$filterBranches = [];
if ($isSuper && $isHq) {
    try {
        $st = $pdo->query("SELECT id, name, province, city FROM branches WHERE is_hq = 0 AND status = 'active' ORDER BY name ASC");
        $filterBranches = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}
}

// Current active branch row
$currentBranchRow = dash_load_branch($activeBranchId);

// Build query conditions
$where = [];
$params = [];

$selectedBranchFilter = trim((string)($_GET['branch'] ?? 'all'));

// Multi-tenant branch scope:
if (!$isSuper || !$isHq) {
    if (dash_can('stands') && !dash_can('pages')) {
        $where[] = "o.branch_id = ?";
        $params[] = $activeBranchId;
    } elseif (!dash_can('stands') && dash_can('pages')) {
        $where[] = "o.order_type = 'product'";
    } else {
        $where[] = "(o.branch_id = ? OR o.order_type = 'product')";
        $params[] = $activeBranchId;
    }
} else {
    // Super admin in Central HQ overview
    if ($selectedBranchFilter !== 'all' && is_numeric($selectedBranchFilter)) {
        $where[] = "o.branch_id = ?";
        $params[] = (int)$selectedBranchFilter;
    }
}

// Filter by Order Type Tab (all, product, stand)
$orderType = trim((string)($_GET['order_type'] ?? ($_GET['type'] ?? 'all')));
if ($orderType === 'product') {
    $where[] = "o.order_type = 'product'";
} elseif ($orderType === 'stand') {
    $where[] = "(o.order_type = 'stand' OR o.order_type IS NULL OR o.order_type = '')";
}

// Filter by stand type (congrats / condolence)
$standType = trim((string)($_GET['stand_type'] ?? 'all'));
if ($standType === 'all' && in_array($orderType, ['congrats', 'condolence'], true)) {
    $standType = $orderType;
    $orderType = 'stand';
}
if (in_array($standType, ['congrats', 'condolence'], true)) {
    $where[] = "s.stand_type = ?";
    $params[] = $standType;
}

// Filter by fulfillment status
$fulfillmentFilter = trim((string)($_GET['fulfillment_status'] ?? 'all'));
if (in_array($fulfillmentFilter, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'], true)) {
    $where[] = "o.fulfillment_status = ?";
    $params[] = $fulfillmentFilter;
}

// Search term: matches order ID, tracking code, recipient name, phone, address, and postal tracking code
$search = trim((string)($_GET['q'] ?? ''));
if ($search !== '') {
    $where[] = "(o.id LIKE ? OR o.tracking_code LIKE ? OR o.from_user LIKE ? OR o.to_user LIKE ? OR o.recipient_name LIKE ? OR o.recipient_phone LIKE ? OR o.postal_tracking_code LIKE ? OR o.province LIKE ? OR o.city LIKE ? OR o.address LIKE ?)";
    $term = "%{$search}%";
    array_push($params, $term, $term, $term, $term, $term, $term, $term, $term, $term, $term);
}

$whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$orders = [];
$statsTotalCount = 0;
$statsTotalPrice = 0;
$statsPendingFulfillment = 0;

try {
    $sql = "
        SELECT o.*, 
               b.name AS branch_name,
               b.province AS branch_province,
               b.city AS branch_city,
               s.title AS stand_title,
               s.image AS stand_image,
               s.stand_type,
               TRIM(CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, ''))) AS benefactor_name, 
               u.email AS benefactor_email,
               p.phone AS benefactor_phone
        FROM orders o 
        LEFT JOIN branches b ON o.branch_id = b.id
        LEFT JOIN stands s ON o.stand_id = s.id
        LEFT JOIN panel_users u ON o.user_id = u.id 
        LEFT JOIN user_profiles p ON o.user_id = p.user_id 
        {$whereSql}
        ORDER BY o.id DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as $ord) {
        $statsTotalCount++;
        $statsTotalPrice += (int)($ord['total_price'] ?? 0);
        $ful = $ord['fulfillment_status'] ?? 'pending';
        if (in_array($ful, ['pending', 'processing'], true)) {
            $statsPendingFulfillment++;
        }
    }
} catch (Throwable $e) {
    $orders = [];
}

// Batch load order_items for product orders
$orderItemsMap = [];
if (!empty($orders)) {
    $productOrderIds = [];
    foreach ($orders as $ord) {
        if (($ord['order_type'] ?? '') === 'product' || empty($ord['stand_id'])) {
            $productOrderIds[] = (int)$ord['id'];
        }
    }
    if (!empty($productOrderIds)) {
        try {
            $inClause = implode(',', array_fill(0, count($productOrderIds), '?'));
            $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($inClause) ORDER BY id ASC");
            $itemStmt->execute($productOrderIds);
            foreach ($itemStmt->fetchAll(PDO::FETCH_ASSOC) as $it) {
                $orderItemsMap[(int)$it['order_id']][] = $it;
            }
        } catch (Throwable $e) {}
    }
}

// Tab counts query for badges
$tabCounts = ['all' => 0, 'product' => 0, 'stand' => 0];
try {
    $countWhere = [];
    $countParams = [];
    if (!$isSuper || !$isHq) {
        if (dash_can('stands') && !dash_can('pages')) {
            $countWhere[] = "o.branch_id = ?";
            $countParams[] = $activeBranchId;
        } elseif (!dash_can('stands') && dash_can('pages')) {
            $countWhere[] = "o.order_type = 'product'";
        } else {
            $countWhere[] = "(o.branch_id = ? OR o.order_type = 'product')";
            $countParams[] = $activeBranchId;
        }
    }
    $cWhereSql = !empty($countWhere) ? ('WHERE ' . implode(' AND ', $countWhere)) : '';
    $cSql = "
        SELECT 
            COUNT(*) as total_count,
            SUM(CASE WHEN o.order_type = 'product' THEN 1 ELSE 0 END) as product_count,
            SUM(CASE WHEN o.order_type = 'stand' OR o.order_type IS NULL OR o.order_type = '' THEN 1 ELSE 0 END) as stand_count
        FROM orders o
        {$cWhereSql}
    ";
    $cStmt = $pdo->prepare($cSql);
    $cStmt->execute($countParams);
    if ($cRow = $cStmt->fetch(PDO::FETCH_ASSOC)) {
        $tabCounts = [
            'all' => (int)($cRow['total_count'] ?? 0),
            'product' => (int)($cRow['product_count'] ?? 0),
            'stand' => (int)($cRow['stand_count'] ?? 0),
        ];
    }
} catch (Throwable $e) {}

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Helper to preserve GET parameters
function build_filter_url(array $overrides): string {
    $params = array_merge($_GET, $overrides);
    return 'orders.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مدیریت سفارشات و ارسال مرسولات | پنل مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#008f8a; --color-primary-dark:#00736f; --color-primary-light:#4fb2b0;
  --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b; --warning:#f59e0b; --info:#0284c7; --purple:#9333ea;
  --success-12:rgba(22,163,122,.14); --danger-12:rgba(224,85,107,.12); --warning-12:rgba(245,158,11,.12);
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --shadow-lg:0 10px 25px rgba(16,40,40,.12),0 4px 10px rgba(16,40,40,.06);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --success-12:rgba(22,163,122,.18); --danger-12:rgba(224,85,107,.16); --warning-12:rgba(245,158,11,.18);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  --shadow-lg:0 10px 25px rgba(0,0,0,.6),0 4px 12px rgba(0,0,0,.45);
  color-scheme:dark; background-color:var(--color-bg);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;padding:28px 22px;transition:background .3s,color .3s}

.wrap{max-width:1200px;margin:0 auto}

.top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:12px}
.nav-back{display:inline-flex;align-items:center;gap:6px;color:var(--color-muted);text-decoration:none;font-weight:600;transition:color .2s;font-size:13px}
.nav-back:hover{color:var(--color-primary)}
.nav-back svg{width:16px;height:16px}

.btn-header-action{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:var(--color-surface);border:1px solid var(--color-border);border-radius:12px;font-size:13px;font-weight:600;color:var(--color-text);text-decoration:none;transition:background-color .2s,border-color .2s;box-shadow:var(--shadow-sm)}
.btn-header-action:hover{border-color:var(--color-primary);color:var(--color-primary);transform:translateY(-1px)}
.btn-header-action svg{width:16px;height:16px;color:var(--color-primary)}

.head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.head-left{display:flex;align-items:center;gap:16px}
.head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;background:var(--color-primary-dark);box-shadow:none}
.head-ic svg{width:27px;height:27px}

.branch-scope-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:rgba(0,143,138,.1);border:1px solid rgba(0,143,138,.2);border-radius:20px;font-size:12.5px;font-weight:600;color:var(--color-primary)}

/* Flash Alerts */
.flash-alert{padding:14px 18px;border-radius:var(--radius-sm);margin-bottom:20px;font-size:13.5px;font-weight:600;display:flex;align-items:center;gap:10px}
.flash-alert.success{background:rgba(22,163,122,.12);color:var(--success);border:1px solid rgba(22,163,122,.25)}
.flash-alert.error{background:rgba(224,85,107,.12);color:var(--danger);border:1px solid rgba(224,85,107,.25)}

/* Stats Bar */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:22px}
.stat-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:18px 20px;box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:14px}
.stat-ic{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;flex-shrink:0}
.stat-ic.orders{background:rgba(0,143,138,.12);color:var(--color-primary)}
.stat-ic.amount{background:rgba(22,163,122,.12);color:var(--success)}
.stat-ic.pending{background:rgba(245,158,11,.12);color:var(--warning)}
.stat-ic.branch{background:rgba(147,51,234,.12);color:var(--purple)}
.stat-ic svg{width:22px;height:22px}
.stat-val{font-size:20px;font-weight:700;color:var(--color-text)}
.stat-lbl{font-size:12px;color:var(--color-muted)}

/* Order Type Tabs */
.orders-nav-tabs{display:flex;align-items:center;gap:10px;margin-bottom:18px;border-bottom:2px solid var(--color-border);padding-bottom:12px;overflow-x:auto}
.order-nav-tab{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:12px;font-size:13.5px;font-weight:600;text-decoration:none;color:var(--color-muted);background:transparent;transition:all .2s;border:1px solid transparent;white-space:nowrap}
.order-nav-tab:hover{color:var(--color-text);background:var(--color-surface);border-color:var(--color-border)}
.order-nav-tab.active{color:var(--color-primary);background:rgba(0,143,138,.1);border-color:rgba(0,143,138,.25)}
.order-nav-tab svg{width:17px;height:17px}
.tab-pill{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:10px;font-size:11.5px;background:rgba(0,0,0,.08);color:inherit}
:root[data-theme="dark"] .tab-pill{background:rgba(255,255,255,.1)}
.order-nav-tab.active .tab-pill{background:var(--color-primary);color:#fff}

/* Filters Bar */
.filter-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:18px 22px;margin-bottom:22px;box-shadow:var(--shadow-sm)}
.filter-form{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.filter-input{flex:1;min-width:220px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:10px;padding:10px 14px;font-family:inherit;font-size:13.5px;color:var(--color-text);outline:none;transition:border-color .2s}
.filter-input:focus{border-color:var(--color-primary)}
.filter-select{background:var(--color-bg);border:1px solid var(--color-border);border-radius:10px;padding:10px 14px;font-family:inherit;font-size:13.5px;color:var(--color-text);outline:none;transition:border-color .2s;cursor:pointer}
.filter-select:focus{border-color:var(--color-primary)}
.btn-filter{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:var(--color-primary);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:13.5px;font-weight:600;cursor:pointer;transition:opacity .2s}
.btn-filter:hover{opacity:.9}
.btn-filter-reset{display:inline-flex;align-items:center;padding:10px 16px;background:var(--color-bg);color:var(--color-muted);border:1px solid var(--color-border);border-radius:10px;font-family:inherit;font-size:13px;text-decoration:none;transition:background-color .2s}
.btn-filter-reset:hover{color:var(--color-text);border-color:var(--color-muted)}

/* Orders List */
.card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:24px;box-shadow:var(--shadow-sm)}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--color-border)}
.card-title{font-size:18px;font-weight:700;display:flex;align-items:center;gap:8px}

.order-grid{display:grid;gap:18px}
.order-item{background:var(--color-bg);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:20px;display:flex;flex-direction:column;gap:16px;transition:border-color .25s var(--ease),box-shadow .25s var(--ease)}
.order-item:hover{border-color:var(--color-primary-light);box-shadow:var(--shadow-md)}

.order-top{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.order-top-left{display:flex;align-items:center;gap:8px;flex-wrap:wrap}

/* Badges */
.badge-order-type{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:16px;font-size:12px;font-weight:700}
.badge-order-type.product{background:rgba(2,132,199,.12);color:#0284c7;border:1px solid rgba(2,132,199,.25)}
.badge-order-type.stand{background:rgba(147,51,234,.12);color:#9333ea;border:1px solid rgba(147,51,234,.25)}

.order-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:30px;background:var(--color-surface);border:1px solid var(--color-border);color:var(--color-text);font-weight:600;font-size:12.5px}
.order-badge svg{width:15px;height:15px;color:var(--color-muted)}

.badge-branch{background:rgba(0,143,138,.12);color:var(--color-primary);font-weight:600;font-size:12px;padding:4px 10px;border-radius:20px;border:1px solid rgba(0,143,138,.2)}
.badge-geo{background:rgba(245,158,11,.12);color:var(--warning);font-weight:600;font-size:12px;padding:4px 10px;border-radius:20px}

.badge-pay{font-size:12px;padding:4px 10px;border-radius:18px;font-weight:600}
.badge-pay.paid{background:rgba(22,163,122,.14);color:var(--success);border:1px solid rgba(22,163,122,.25)}
.badge-pay.pending{background:rgba(245,158,11,.14);color:var(--warning);border:1px solid rgba(245,158,11,.25)}
.badge-pay.failed{background:rgba(220,38,38,.14);color:var(--danger);border:1px solid rgba(220,38,38,.25)}

/* Fulfillment Status Badges */
.badge-fulfillment{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700}
.badge-fulfillment::before{content:'';display:inline-block;width:7px;height:7px;border-radius:50%;background:currentColor}
.badge-fulfillment.badge-ful-pending{background:rgba(245,158,11,.14);color:#d97706;border:1px solid rgba(245,158,11,.3)}
.badge-fulfillment.badge-ful-processing{background:rgba(37,99,235,.14);color:#2563eb;border:1px solid rgba(37,99,235,.3)}
.badge-fulfillment.badge-ful-shipped{background:rgba(147,51,234,.14);color:#9333ea;border:1px solid rgba(147,51,234,.3)}
.badge-fulfillment.badge-ful-delivered{background:rgba(22,163,122,.14);color:#16a37a;border:1px solid rgba(22,163,122,.3)}
.badge-fulfillment.badge-ful-cancelled{background:rgba(220,38,38,.14);color:#dc2626;border:1px solid rgba(220,38,38,.3)}

.order-date{font-size:12.5px;color:var(--color-muted);display:flex;align-items:center;gap:6px;font-weight:500}

/* Postal Tracking Strip */
.order-tracking-strip{background:var(--color-surface);border:1px dashed var(--color-border);border-radius:10px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:13px}
.tracking-strip-info{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.tracking-strip-info svg{width:18px;height:18px;color:var(--color-primary)}
.carrier-name{font-weight:700;color:var(--color-text)}
.tracking-sep{color:var(--color-border);margin:0 4px}
.tracking-num{font-family:monospace;direction:ltr;background:var(--color-bg);padding:3px 8px;border-radius:6px;border:1px solid var(--color-border);letter-spacing:1px;font-weight:700;color:var(--color-text)}
.btn-copy-code{display:inline-flex;align-items:center;gap:4px;padding:4px 8px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:6px;font-size:11.5px;font-family:inherit;font-weight:600;color:var(--color-text);cursor:pointer;transition:all .2s}
.btn-copy-code:hover{background:var(--color-primary);color:#fff;border-color:var(--color-primary)}
.btn-copy-code svg{width:13px;height:13px}
.btn-track-external{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:rgba(0,143,138,.1);color:var(--color-primary);border:1px solid rgba(0,143,138,.25);border-radius:6px;font-size:11.5px;font-weight:600;text-decoration:none;transition:all .2s}
.btn-track-external:hover{background:var(--color-primary);color:#fff}
.btn-track-external svg{width:13px;height:13px}

/* Order Body & Fields */
.order-body{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;font-size:13.5px}
.order-field{background:var(--color-surface);padding:12px 14px;border-radius:10px;border:1px solid var(--color-border)}
.order-field span{display:block;color:var(--color-muted);font-size:12px;margin-bottom:2px}
.order-field strong{display:block;color:var(--color-text);font-weight:600;word-break:break-word}

/* Order Items Box */
.order-items-box{grid-column:1 / -1;background:var(--color-surface);padding:14px;border-radius:10px;border:1px solid var(--color-border)}
.order-items-head{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;margin-bottom:10px;color:var(--color-text)}
.order-items-head svg{width:18px;height:18px;color:var(--color-primary)}
.order-items-list{display:flex;flex-wrap:wrap;gap:10px}
.order-item-pill{display:inline-flex;align-items:center;gap:8px;background:var(--color-bg);padding:6px 12px;border-radius:8px;border:1px solid var(--color-border);font-size:12.5px}
.item-title{font-weight:600;color:var(--color-text)}
.item-qty{background:rgba(0,143,138,.12);color:var(--color-primary);font-weight:700;padding:2px 8px;border-radius:10px;font-size:11.5px}
.item-price{color:var(--color-muted);font-size:12px}

.order-msg{grid-column:1 / -1;background:var(--color-surface);padding:14px;border-radius:10px;border:1px solid var(--color-border)}
.order-msg span{display:block;color:var(--color-muted);font-size:12px;margin-bottom:4px}
.order-msg p{color:var(--color-text);font-style:italic;line-height:1.7}

.order-footer{display:flex;justify-content:space-between;align-items:center;background:var(--color-surface);border:1px solid var(--color-border);padding:14px 18px;border-radius:12px;flex-wrap:wrap;gap:14px}
.order-price-box span{display:block;font-size:12px;color:var(--color-muted)}
.order-price{font-size:19px;font-weight:700;color:var(--color-primary)}

.order-benefactor{font-size:12.5px;color:var(--color-muted)}
.order-benefactor strong{color:var(--color-primary);font-weight:600}

.order-stand-thumb{display:flex;align-items:center;gap:12px}
.order-img{width:64px;height:84px;object-fit:contain;background:var(--color-bg);border:1px solid var(--color-border);border-radius:8px;padding:4px;transition:transform .2s}
.order-img:hover{transform:scale(1.08)}
.order-stand-info{display:flex;flex-direction:column;gap:2px}
.order-stand-title{font-weight:600;font-size:13.5px;color:var(--color-text)}

/* Manage Fulfillment Button */
.btn-manage-fulfillment{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;background:var(--color-surface);border:1.5px solid var(--color-primary);color:var(--color-primary);border-radius:10px;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;transition:all .2s;box-shadow:var(--shadow-sm)}
.btn-manage-fulfillment:hover{background:var(--color-primary);color:#fff;transform:translateY(-1px);box-shadow:var(--shadow-md)}
.btn-manage-fulfillment svg{width:16px;height:16px}

/* Modal Dialog */
.modal-backdrop{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,.65);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:16px;overflow-y:auto}
.modal-backdrop.open{display:flex}
.modal-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-lg);width:100%;max-width:620px;box-shadow:var(--shadow-lg);overflow:hidden;animation:modalIn .25s var(--ease)}
@keyframes modalIn{from{opacity:0;transform:scale(.96) translateY(8px)}to{opacity:1;transform:scale(1) translateY(0)}}

.modal-header{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--color-border)}
.modal-title{font-size:17px;font-weight:800;display:flex;align-items:center;gap:8px;color:var(--color-text)}
.btn-modal-close{background:none;border:none;cursor:pointer;color:var(--color-muted);width:32px;height:32px;border-radius:8px;display:grid;place-items:center;transition:background-color .2s}
.btn-modal-close:hover{background:var(--color-bg);color:var(--color-text)}
.btn-modal-close svg{width:20px;height:20px}

.modal-body{padding:22px 24px;display:flex;flex-direction:column;gap:18px;max-height:calc(85vh - 140px);overflow-y:auto}

.modal-info-box{background:var(--color-bg);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;font-size:13px;display:flex;flex-direction:column;gap:10px}
.info-row{display:flex;justify-content:space-between;gap:10px}
.info-row span{color:var(--color-muted)}
.info-row strong{color:var(--color-text);text-align:left;direction:ltr}
.info-row.rtl-val strong{direction:rtl;text-align:right}

.modal-items-table{width:100%;border-collapse:collapse;font-size:12.5px;margin-top:6px}
.modal-items-table th,.modal-items-table td{padding:8px 10px;text-align:right;border-bottom:1px solid var(--color-border)}
.modal-items-table th{color:var(--color-muted);font-weight:600;background:var(--color-surface)}

.form-group{display:flex;flex-direction:column;gap:6px}
.form-label{font-size:13px;font-weight:700;color:var(--color-text)}
.form-control{background:var(--color-bg);border:1px solid var(--color-border);border-radius:10px;padding:10px 14px;font-family:inherit;font-size:13.5px;color:var(--color-text);outline:none;transition:border-color .2s}
.form-control:focus{border-color:var(--color-primary)}

.carrier-presets{display:flex;flex-wrap:wrap;gap:8px;margin-top:4px}
.preset-chip{background:var(--color-bg);border:1px solid var(--color-border);padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;transition:all .2s;color:var(--color-text)}
.preset-chip:hover,.preset-chip.active{background:rgba(0,143,138,.12);border-color:var(--color-primary);color:var(--color-primary)}

.modal-alert{padding:10px 14px;border-radius:8px;font-size:13px;font-weight:600}
.modal-alert.success{background:rgba(22,163,122,.14);color:var(--success);border:1px solid rgba(22,163,122,.3)}
.modal-alert.error{background:rgba(220,38,38,.14);color:var(--danger);border:1px solid rgba(220,38,38,.3)}

.modal-footer{padding:16px 24px;border-top:1px solid var(--color-border);display:flex;justify-content:flex-end;gap:12px;background:var(--color-surface)}
.btn-modal-cancel{padding:10px 18px;border-radius:10px;border:1px solid var(--color-border);background:var(--color-bg);color:var(--color-muted);font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s}
.btn-modal-cancel:hover{color:var(--color-text);border-color:var(--color-muted)}
.btn-modal-save{display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border-radius:10px;border:none;background:var(--color-primary);color:#fff;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;transition:opacity .2s}
.btn-modal-save:hover{opacity:.9}
.btn-modal-save:disabled{opacity:.6;cursor:not-allowed}

.empty{text-align:center;padding:70px 20px;color:var(--color-muted)}
.empty svg{width:56px;height:56px;opacity:0.25;margin-bottom:16px}
.empty p{font-size:16px;font-weight:600}
</style>
<link rel="stylesheet" href="/dashboard/assets/css/admin-ledger.css">
</head>
<body>

<div class="wrap">
  <div class="top-bar">
    <a href="index.php" class="nav-back">
      <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      بازگشت به داشبورد
    </a>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <?php if ($isSuper || dash_can('pages')): ?>
        <a href="products.php" class="btn-header-action">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          مدیریت محصولات فروشگاه
        </a>
      <?php endif; ?>
      <?php if ($isSuper || dash_can('stands')): ?>
        <a href="stands.php" class="btn-header-action">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="9" r="5.5"/><path d="M8.2 13.2 6.5 21l5.5-3 5.5 3-1.7-7.8"/></svg>
          مدیریت استندهای شعبه
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="head">
    <div class="head-left">
      <div class="head-ic">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      </div>
      <div>
        <h1 style="font-size:24px;font-weight:900">مدیریت سفارشات و ارسال مرسولات</h1>
        <p style="color:var(--color-muted);font-size:13px">کارتابل متمرکز رهگیری، آماده‌سازی و ثبت بارنامه محصولات فروشگاه و استندهای خیریه</p>
      </div>
    </div>
    <div>
      <?php if ($isSuper && $isHq): ?>
        <span class="branch-scope-badge">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          نمای مدیر مرکزی (سراسر شعب)
        </span>
      <?php else: ?>
        <span class="branch-scope-badge" style="background:rgba(22,163,122,.12);border-color:rgba(22,163,122,.3);color:var(--success)">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          سفارشات شعبه: <?= htmlspecialchars($currentBranchRow['name'] ?? 'شعبه فعال') ?> (<?= htmlspecialchars($currentBranchRow['province'] ?? $currentBranchRow['city'] ?? '') ?>)
        </span>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($flashSuccess): ?>
    <div class="flash-alert success">
      <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      <?= htmlspecialchars($flashSuccess) ?>
    </div>
  <?php endif; ?>

  <?php if ($flashError): ?>
    <div class="flash-alert error">
      <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:20px;height:20px"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
      <?= htmlspecialchars($flashError) ?>
    </div>
  <?php endif; ?>

  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-ic orders">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= number_format($statsTotalCount) ?></div>
        <div class="stat-lbl">تعداد کل سفارشات بارگذاری‌شده</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-ic amount">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= number_format($statsTotalPrice) ?> <span style="font-size:13px;font-weight:600">تومان</span></div>
        <div class="stat-lbl">مجموع مبالغ اهدایی و خریدها</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-ic pending">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= number_format($statsPendingFulfillment) ?></div>
        <div class="stat-lbl">سفارشات نیازمند پردازش و ارسال</div>
      </div>
    </div>
    <?php if ($isSuper && $isHq): ?>
      <div class="stat-card">
        <div class="stat-ic branch">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <div>
          <div class="stat-val"><?= count($filterBranches) ?></div>
          <div class="stat-lbl">شعب استند و خدمات مکسا</div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Order Type Tabs -->
  <div class="orders-nav-tabs">
    <a href="<?= build_filter_url(['order_type' => 'all']) ?>" class="order-nav-tab <?= $orderType === 'all' ? 'active' : '' ?>">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
      همه سفارشات
      <span class="tab-pill"><?= number_format($tabCounts['all']) ?></span>
    </a>
    <a href="<?= build_filter_url(['order_type' => 'product']) ?>" class="order-nav-tab <?= $orderType === 'product' ? 'active' : '' ?>">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
      محصولات فروشگاه
      <span class="tab-pill"><?= number_format($tabCounts['product']) ?></span>
    </a>
    <a href="<?= build_filter_url(['order_type' => 'stand']) ?>" class="order-nav-tab <?= $orderType === 'stand' ? 'active' : '' ?>">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="9" r="5.5"/><path d="M8.2 13.2 6.5 21l5.5-3 5.5 3-1.7-7.8"/></svg>
      استند و تاج گل
      <span class="tab-pill"><?= number_format($tabCounts['stand']) ?></span>
    </a>
  </div>

  <!-- Filters Bar -->
  <div class="filter-card">
    <form method="GET" action="orders.php" class="filter-form">
      <input type="hidden" name="order_type" value="<?= htmlspecialchars($orderType) ?>">

      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="جستجو در شناسه، کد رهگیری، فرستنده، گیرنده، نشانی، تلفن و بارنامه..." class="filter-input">

      <select name="fulfillment_status" class="filter-select" onchange="this.form.submit()">
        <option value="all" <?= ($fulfillmentFilter === 'all') ? 'selected' : '' ?>>همه وضعیت‌های ارسال</option>
        <option value="pending" <?= ($fulfillmentFilter === 'pending') ? 'selected' : '' ?>>در انتظار پردازش</option>
        <option value="processing" <?= ($fulfillmentFilter === 'processing') ? 'selected' : '' ?>>در حال آماده‌سازی</option>
        <option value="shipped" <?= ($fulfillmentFilter === 'shipped') ? 'selected' : '' ?>>ارسال شده</option>
        <option value="delivered" <?= ($fulfillmentFilter === 'delivered') ? 'selected' : '' ?>>تحویل شده</option>
        <option value="cancelled" <?= ($fulfillmentFilter === 'cancelled') ? 'selected' : '' ?>>لغو شده</option>
      </select>

      <?php if ($orderType !== 'product'): ?>
        <select name="stand_type" class="filter-select" onchange="this.form.submit()">
          <option value="all" <?= ($standType === 'all') ? 'selected' : '' ?>>همه انواع استند</option>
          <option value="congrats" <?= ($standType === 'congrats') ? 'selected' : '' ?>>فقط تبریک</option>
          <option value="condolence" <?= ($standType === 'condolence') ? 'selected' : '' ?>>فقط تسلیت</option>
        </select>
      <?php endif; ?>

      <?php if ($isSuper && $isHq): ?>
        <select name="branch" class="filter-select" onchange="this.form.submit()">
          <option value="all" <?= ($selectedBranchFilter === 'all') ? 'selected' : '' ?>>همه شعب مکسا</option>
          <?php foreach ($filterBranches as $fb): ?>
            <option value="<?= $fb['id'] ?>" <?= ($selectedBranchFilter == $fb['id']) ? 'selected' : '' ?>>
              شعبه <?= htmlspecialchars($fb['name']) ?> (<?= htmlspecialchars($fb['province'] ?: '—') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <button type="submit" class="btn-filter">
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:16px;height:16px"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        اعمال فیلتر
      </button>

      <?php if ($search !== '' || $fulfillmentFilter !== 'all' || ($orderType !== 'all') || ($standType !== 'all') || ($isSuper && $isHq && $selectedBranchFilter !== 'all')): ?>
        <a href="orders.php?order_type=<?= urlencode($orderType) ?>" class="btn-filter-reset">پاکسازی فیلتر</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:20px;height:20px;color:var(--color-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
        لیست سفارشات
      </div>
      <div class="order-count" style="font-size:13px;color:var(--color-muted);background:var(--color-bg);padding:4px 12px;border-radius:20px;border:1px solid var(--color-border)">
        <?= count($orders) ?> سفارش یافت شد
      </div>
    </div>

    <?php if (empty($orders)): ?>
      <div class="empty">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <p>هیچ سفارشی مطابق معیارهای انتخابی یافت نشد.</p>
      </div>
    <?php else: ?>
      <div class="order-grid">
        <?php foreach ($orders as $order): ?>
          <?php
            $isProductOrder = (($order['order_type'] ?? '') === 'product');
            $fulStatus = $order['fulfillment_status'] ?? 'pending';
            $payStatus = $order['payment_status'] ?? 'pending';
            $items = $orderItemsMap[(int)$order['id']] ?? [];
            $totalItemsCount = 0;
            foreach ($items as $it) {
                $totalItemsCount += (int)$it['quantity'];
            }
            $recipientName = $order['recipient_name'] ?: ($order['to_user'] ?: ($order['benefactor_name'] ?: '—'));
            $recipientPhone = $order['recipient_phone'] ?: ($order['benefactor_phone'] ?: '—');
            $formattedAddress = trim(($order['province'] ? $order['province'] . ' - ' : '') . ($order['city'] ? $order['city'] . ' - ' : '') . ($order['address'] ?: ''));
            if (!empty($order['postal_code'])) {
                $formattedAddress .= ' (کد پستی: ' . $order['postal_code'] . ')';
            }
            $customerNote = $order['customer_note'] ?: ($order['message'] ?: '');
          ?>
          <div class="order-item" id="order-row-<?= (int)$order['id'] ?>">
            <div class="order-top">
              <div class="order-top-left">
                <!-- Order Type Badge -->
                <?php if ($isProductOrder): ?>
                  <span class="badge-order-type product">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    محصول فیزیکی
                  </span>
                <?php else: ?>
                  <span class="badge-order-type stand">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px"><circle cx="12" cy="9" r="5.5"/><path d="M8.2 13.2 6.5 21l5.5-3 5.5 3-1.7-7.8"/></svg>
                    استند و کارت
                  </span>
                <?php endif; ?>

                <!-- Tracking Code Badge -->
                <div class="order-badge">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                  سفارش: <?= htmlspecialchars($order['tracking_code'] ?: (string)$order['id']) ?>
                </div>

                <!-- Payment Status Badge -->
                <span class="badge-pay <?= htmlspecialchars($payStatus) ?>">
                  <?= htmlspecialchars($paymentStatusLabels[$payStatus] ?? $payStatus) ?>
                </span>

                <!-- Fulfillment Status Badge -->
                <span class="badge-fulfillment badge-ful-<?= htmlspecialchars($fulStatus) ?>" id="ful-badge-<?= (int)$order['id'] ?>">
                  <?= htmlspecialchars($fulfillmentStatusLabels[$fulStatus] ?? $fulStatus) ?>
                </span>

                <?php if ($isSuper && $isHq && !empty($order['branch_name'])): ?>
                  <span class="badge-branch">
                    شعبه: <?= htmlspecialchars($order['branch_name'] ?: 'مرکزی') ?>
                  </span>
                <?php endif; ?>

                <?php if (!empty($order['province']) || !empty($order['city'])): ?>
                  <span class="badge-geo">
                    <?= htmlspecialchars(trim(($order['province'] ?? '') . ' - ' . ($order['city'] ?? ''), ' -')) ?>
                  </span>
                <?php endif; ?>

                <?php if (!$isProductOrder && !empty($order['stand_type'])): ?>
                  <span class="badge-type <?= $order['stand_type'] === 'congrats' ? 'congrats' : 'condolence' ?>">
                    <?= $order['stand_type'] === 'congrats' ? 'طرح تبریک' : 'طرح تسلیت' ?>
                  </span>
                <?php endif; ?>
              </div>

              <div class="order-date">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <?= htmlspecialchars($order['created_at'] ? substr((string)$order['created_at'], 0, 10) : ($order['order_date'] ?: '—')) ?>
              </div>
            </div>

            <!-- Carrier & Postal Tracking Strip -->
            <div class="order-tracking-strip" id="tracking-strip-<?= (int)$order['id'] ?>" <?= empty($order['postal_tracking_code']) && empty($order['carrier']) ? 'style="display:none"' : '' ?>>
              <div class="tracking-strip-info">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                <span>حامل / روش ارسال:</span>
                <span class="carrier-name" id="carrier-text-<?= (int)$order['id'] ?>"><?= htmlspecialchars($order['carrier'] ?: 'تعیین‌نشده') ?></span>

                <?php if (!empty($order['postal_tracking_code'])): ?>
                  <span class="tracking-sep">|</span>
                  <span>کد رهگیری پستی / بارنامه:</span>
                  <span class="tracking-num" id="tracking-text-<?= (int)$order['id'] ?>"><?= htmlspecialchars($order['postal_tracking_code']) ?></span>
                  <button type="button" class="btn-copy-code" onclick="copyTracking('<?= htmlspecialchars($order['postal_tracking_code'], ENT_QUOTES) ?>', this)" title="کپی کد رهگیری">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    <span>کپی</span>
                  </button>
                  <a href="https://tracking.post.ir/" target="_blank" rel="noopener noreferrer" class="btn-track-external" title="سامانه رهگیری مرسولات پستی">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    رهگیری پست
                  </a>
                <?php else: ?>
                  <span class="tracking-num" id="tracking-text-<?= (int)$order['id'] ?>" style="display:none"></span>
                <?php endif; ?>
              </div>
            </div>

            <!-- Order Body -->
            <div class="order-body">
              <?php if ($isProductOrder): ?>
                <div class="order-field">
                  <span>تحویل‌گیرنده:</span>
                  <strong><?= htmlspecialchars($recipientName) ?></strong>
                </div>
                <div class="order-field">
                  <span>تلفن تماس:</span>
                  <strong><?= htmlspecialchars($recipientPhone) ?></strong>
                </div>
                <div class="order-field">
                  <span>مقصد تحویل:</span>
                  <strong><?= htmlspecialchars(trim(($order['province'] ?? '') . ' - ' . ($order['city'] ?? ''), ' -') ?: '—') ?></strong>
                </div>
                <div class="order-field" style="grid-column:1 / -1">
                  <span>نشانی کامل پستی:</span>
                  <strong><?= htmlspecialchars($formattedAddress ?: '—') ?></strong>
                </div>

                <!-- Products Items List -->
                <?php if (!empty($items)): ?>
                  <div class="order-items-box">
                    <div class="order-items-head">
                      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                      <span>اقلام سفارش (<?= count($items) ?> ردیف کالا، مجموع <?= $totalItemsCount ?> عدد):</span>
                    </div>
                    <div class="order-items-list">
                      <?php foreach ($items as $it): ?>
                        <div class="order-item-pill">
                          <span class="item-title"><?= htmlspecialchars($it['product_title']) ?></span>
                          <span class="item-qty">× <?= (int)$it['quantity'] ?></span>
                          <span class="item-price"><?= number_format((float)$it['unit_price']) ?> تومان</span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>

              <?php else: ?>
                <!-- Stand Order Fields -->
                <div class="order-field">
                  <span>از طرف (سفارش دهنده):</span>
                  <strong><?= htmlspecialchars($order['from_user'] ?: '—') ?></strong>
                </div>
                <div class="order-field">
                  <span>تقدیم به (گیرنده):</span>
                  <strong><?= htmlspecialchars($order['to_user'] ?: '—') ?></strong>
                </div>
                <div class="order-field">
                  <span>طرح و مدل استند:</span>
                  <strong><?= htmlspecialchars($order['stand_title'] ?: 'استند سفارشی مکسا') ?></strong>
                </div>
                <div class="order-field" style="grid-column:1 / -1">
                  <span>نشانی دقیق برگزاری مراسم:</span>
                  <strong><?= htmlspecialchars($formattedAddress ?: '—') ?></strong>
                </div>
              <?php endif; ?>

              <?php if (!empty($customerNote)): ?>
                <div class="order-msg">
                  <span><?= $isProductOrder ? 'یادداشت خریدار:' : 'متن پیام اختصاصی روی استند:' ?></span>
                  <p>"<?= nl2br(htmlspecialchars($customerNote)) ?>"</p>
                </div>
              <?php endif; ?>
            </div>

            <!-- Order Footer -->
            <div class="order-footer">
              <div class="order-price-box">
                <span>مبلغ سفارش:</span>
                <div class="order-price"><?= number_format((int)($order['total_price'] ?? 0)) ?> تومان</div>
                <?php if (!empty($order['benefactor_name']) || !empty($order['benefactor_email'])): ?>
                  <div class="order-benefactor" style="margin-top:6px">
                    کاربر حامی: <strong><?= htmlspecialchars($order['benefactor_name'] ?: $order['benefactor_email']) ?></strong>
                    <?php if (!empty($order['benefactor_phone'])): ?>
                      (<?= htmlspecialchars($order['benefactor_phone']) ?>)
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>

              <?php if (!$isProductOrder): ?>
                <?php $imgUrl = !empty($order['stand_image']) ? $order['stand_image'] : (!empty($order['image']) ? $order['image'] : ''); ?>
                <?php if (!empty($imgUrl)): ?>
                  <div class="order-stand-thumb">
                    <div class="order-stand-info" style="text-align:left">
                      <span style="font-size:11px;color:var(--color-muted)">طرح استند</span>
                      <span class="order-stand-title"><?= htmlspecialchars($order['stand_title'] ?: 'طرح انتخابی') ?></span>
                    </div>
                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="استند" class="order-img" onerror="this.style.display='none'">
                  </div>
                <?php endif; ?>
              <?php endif; ?>

              <!-- Fulfillment Action Button -->
              <div>
                <button type="button" 
                        class="btn-manage-fulfillment" 
                        data-order-id="<?= (int)$order['id'] ?>"
                        data-order-tracking="<?= htmlspecialchars($order['tracking_code'] ?: (string)$order['id']) ?>"
                        data-type="<?= htmlspecialchars($order['order_type'] ?: 'stand') ?>"
                        data-status="<?= htmlspecialchars($fulStatus) ?>"
                        data-carrier="<?= htmlspecialchars($order['carrier'] ?? '') ?>"
                        data-tracking-code="<?= htmlspecialchars($order['postal_tracking_code'] ?? '') ?>"
                        data-recipient="<?= htmlspecialchars($recipientName) ?>"
                        data-phone="<?= htmlspecialchars($recipientPhone) ?>"
                        data-address="<?= htmlspecialchars($formattedAddress) ?>"
                        data-note="<?= htmlspecialchars($customerNote) ?>"
                        data-items="<?= htmlspecialchars(json_encode($items, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>"
                        onclick="openFulfillmentModal(this)">
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  مدیریت وضعیت و ارسال
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Dialog: مدیریت وضعیت و ارسال مرسوله -->
<div class="modal-backdrop" id="fulfillment-modal" onclick="onBackdropClick(event)">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
    <div class="modal-header">
      <div class="modal-title" id="modal-headline">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:22px;height:22px;color:var(--color-primary)"><path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
        مدیریت وضعیت و ارسال مرسوله
      </div>
      <button type="button" class="btn-modal-close" onclick="closeFulfillmentModal()" title="بستن">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form id="fulfillment-form" method="POST" onsubmit="submitFulfillment(event)">
      <div class="modal-body">
        <div id="modal-alert" class="modal-alert" style="display:none"></div>

        <!-- Order Info Box -->
        <div class="modal-info-box">
          <div class="info-row rtl-val">
            <span>شناسه و کد سفارش:</span>
            <strong>#<span id="modal-order-id">—</span> (<span id="modal-order-tracking">—</span>)</strong>
          </div>
          <div class="info-row rtl-val">
            <span>تحویل‌گیرنده:</span>
            <strong id="modal-recipient">—</strong>
          </div>
          <div class="info-row rtl-val">
            <span>شماره تماس:</span>
            <strong id="modal-phone">—</strong>
          </div>
          <div class="info-row rtl-val">
            <span>نشانی پستی تحویل:</span>
            <strong id="modal-address">—</strong>
          </div>
          <div class="info-row rtl-val" id="modal-note-row" style="display:none">
            <span>یادداشت سفارش:</span>
            <strong id="modal-note" style="font-weight:normal;font-style:italic">—</strong>
          </div>
        </div>

        <!-- Shop Order Items Container -->
        <div id="modal-items-container" style="display:none">
          <label class="form-label">اقلام سفارش:</label>
          <table class="modal-items-table">
            <thead>
              <tr>
                <th>عنوان محصول</th>
                <th style="width:70px">تعداد</th>
                <th style="width:110px">قیمت واحد</th>
              </tr>
            </thead>
            <tbody id="modal-items-tbody"></tbody>
          </table>
        </div>

        <!-- Hidden Form Inputs -->
        <input type="hidden" name="action" value="update_fulfillment">
        <input type="hidden" name="order_id" id="form-order-id" value="">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">

        <!-- Fulfillment Status Dropdown -->
        <div class="form-group">
          <label class="form-label" for="form-fulfillment-status">وضعیت ارسال مرسوله:</label>
          <select name="fulfillment_status" id="form-fulfillment-status" class="form-control" required>
            <option value="pending">در انتظار پردازش (آماده‌سازی نشده)</option>
            <option value="processing">در حال آماده‌سازی (بسته‌بندی در جریان)</option>
            <option value="shipped">ارسال شده (تحویل شرکت پست / پیک شده)</option>
            <option value="delivered">تحویل شده (به دست مشتری رسیده)</option>
            <option value="cancelled">لغو شده</option>
          </select>
        </div>

        <!-- Carrier Input & Presets -->
        <div class="form-group">
          <label class="form-label" for="form-carrier">روش یا شرکت حمل‌ونقل (حامل):</label>
          <input type="text" name="carrier" id="form-carrier" class="form-control" placeholder="مثال: پست پیشتاز، تیپاکس، پیک موتوری">
          <div class="carrier-presets">
            <button type="button" class="preset-chip" onclick="setCarrier('پست پیشتاز')">پست پیشتاز</button>
            <button type="button" class="preset-chip" onclick="setCarrier('تیپاکس')">تیپاکس</button>
            <button type="button" class="preset-chip" onclick="setCarrier('پیک موتوری')">پیک موتوری</button>
            <button type="button" class="preset-chip" onclick="setCarrier('تحویل حضوری')">تحویل حضوری</button>
            <button type="button" class="preset-chip" onclick="setCarrier('سایر')">سایر</button>
          </div>
        </div>

        <!-- Postal Tracking Code -->
        <div class="form-group">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <label class="form-label" for="form-postal-tracking">کد ۲۴ رقمی رهگیری پست یا شماره بارنامه:</label>
            <a href="https://tracking.post.ir/" target="_blank" rel="noopener noreferrer" style="font-size:11.5px;color:var(--color-primary);text-decoration:none">
              سامانه رهگیری پست ↗
            </a>
          </div>
          <input type="text" name="postal_tracking_code" id="form-postal-tracking" class="form-control" placeholder="کد رهگیری مرسوله پستی یا شماره بارنامه/پیک" style="direction:ltr;font-family:monospace">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" onclick="closeFulfillmentModal()">انصراف</button>
        <button type="submit" class="btn-modal-save" id="btn-save-fulfillment">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          ذخیره تغییرات
        </button>
      </div>
    </form>
  </div>
</div>

<script>
const statusLabelsMap = {
  'pending': 'در انتظار پردازش',
  'processing': 'در حال آماده‌سازی',
  'shipped': 'ارسال شده',
  'delivered': 'تحویل شده',
  'cancelled': 'لغو شده'
};

function openFulfillmentModal(btn) {
  const d = btn.dataset;
  document.getElementById('form-order-id').value = d.orderId || '';
  document.getElementById('modal-order-id').textContent = d.orderId || '—';
  document.getElementById('modal-order-tracking').textContent = d.orderTracking || '—';
  document.getElementById('modal-recipient').textContent = d.recipient || '—';
  document.getElementById('modal-phone').textContent = d.phone || '—';
  document.getElementById('modal-address').textContent = d.address || '—';

  const noteEl = document.getElementById('modal-note');
  const noteRow = document.getElementById('modal-note-row');
  if (d.note && d.note.trim() !== '') {
    noteEl.textContent = d.note;
    noteRow.style.display = 'flex';
  } else {
    noteRow.style.display = 'none';
  }

  // Items table
  const itemsContainer = document.getElementById('modal-items-container');
  const itemsTbody = document.getElementById('modal-items-tbody');
  itemsTbody.innerHTML = '';
  if (d.items) {
    try {
      const items = JSON.parse(d.items);
      if (Array.isArray(items) && items.length > 0) {
        items.forEach(it => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td><strong>${escapeHtml(it.product_title || '—')}</strong></td>
            <td>${Number(it.quantity || 1).toLocaleString('fa-IR')}</td>
            <td>${Number(it.unit_price || 0).toLocaleString('fa-IR')} تومان</td>
          `;
          itemsTbody.appendChild(tr);
        });
        itemsContainer.style.display = 'block';
      } else {
        itemsContainer.style.display = 'none';
      }
    } catch (e) {
      itemsContainer.style.display = 'none';
    }
  } else {
    itemsContainer.style.display = 'none';
  }

  // Form values
  document.getElementById('form-fulfillment-status').value = d.status || 'pending';
  document.getElementById('form-carrier').value = d.carrier || '';
  document.getElementById('form-postal-tracking').value = d.trackingCode || '';

  // Clear alerts
  const alert = document.getElementById('modal-alert');
  alert.style.display = 'none';
  alert.className = 'modal-alert';
  alert.textContent = '';

  // Open modal
  const modal = document.getElementById('fulfillment-modal');
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeFulfillmentModal() {
  const modal = document.getElementById('fulfillment-modal');
  modal.classList.remove('open');
  document.body.style.overflow = '';
}

function onBackdropClick(e) {
  if (e.target.id === 'fulfillment-modal') {
    closeFulfillmentModal();
  }
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeFulfillmentModal();
  }
});

function setCarrier(val) {
  const input = document.getElementById('form-carrier');
  input.value = val;
  input.focus();
}

async function submitFulfillment(e) {
  e.preventDefault();
  const form = document.getElementById('fulfillment-form');
  const btn = document.getElementById('btn-save-fulfillment');
  const alert = document.getElementById('modal-alert');
  const orderId = document.getElementById('form-order-id').value;

  btn.disabled = true;
  btn.innerHTML = 'در حال ذخیره...';

  try {
    const formData = new FormData(form);
    formData.append('ajax', '1');

    const res = await fetch('orders.php', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });

    const data = await res.json();
    if (!res.ok || !data.success) {
      throw new Error(data.message || 'خطا در ثبت تغییرات.');
    }

    // Success feedback
    alert.className = 'modal-alert success';
    alert.textContent = data.message || 'وضعیت ارسال با موفقیت به‌روزرسانی شد.';
    alert.style.display = 'block';

    // Update row in DOM
    const row = document.getElementById('order-row-' + orderId);
    if (row) {
      // Update badge
      const badge = document.getElementById('ful-badge-' + orderId);
      if (badge) {
        badge.className = 'badge-fulfillment badge-ful-' + data.fulfillment_status;
        badge.textContent = statusLabelsMap[data.fulfillment_status] || data.fulfillment_status;
      }

      // Update tracking strip
      const strip = document.getElementById('tracking-strip-' + orderId);
      const carrierTxt = document.getElementById('carrier-text-' + orderId);
      const trkTxt = document.getElementById('tracking-text-' + orderId);

      if (strip && carrierTxt) {
        if (data.carrier || data.postal_tracking_code) {
          strip.style.display = 'flex';
          carrierTxt.textContent = data.carrier || 'روش ارسال ثبت‌شده';
          if (trkTxt) {
            trkTxt.textContent = data.postal_tracking_code || '';
            trkTxt.style.display = data.postal_tracking_code ? 'inline-block' : 'none';
          }
        }
      }

      // Update action button datasets
      const manageBtn = row.querySelector('.btn-manage-fulfillment');
      if (manageBtn) {
        manageBtn.dataset.status = data.fulfillment_status;
        manageBtn.dataset.carrier = data.carrier || '';
        manageBtn.dataset.trackingCode = data.postal_tracking_code || '';
      }
    }

    setTimeout(() => {
      closeFulfillmentModal();
    }, 1100);

  } catch (err) {
    alert.className = 'modal-alert error';
    alert.textContent = err.message || 'خطا در ارتباط با سرور.';
    alert.style.display = 'block';
  } finally {
    btn.disabled = false;
    btn.innerHTML = `
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      ذخیره تغییرات
    `;
  }
}

function copyTracking(code, btn) {
  if (!code) return;
  navigator.clipboard.writeText(code).then(() => {
    const span = btn.querySelector('span');
    const orig = span ? span.textContent : '';
    if (span) span.textContent = 'کپی شد!';
    btn.style.borderColor = 'var(--color-primary)';
    setTimeout(() => {
      if (span) span.textContent = orig;
      btn.style.borderColor = '';
    }, 1800);
  }).catch(() => {});
}

function escapeHtml(str) {
  return String(str).replace(/[&<>"']/g, function(m) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
  });
}
</script>

</body>
</html>

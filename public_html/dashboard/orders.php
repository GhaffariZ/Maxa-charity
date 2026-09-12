<?php
require_once __DIR__ . '/_guard.php';

// Safe self-healing schema migration for orders table
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

$isSuper = dash_is_super();
$isHq = dash_is_hq_view();
$activeBranchId = dash_active_branch_id();

// Access check: Only super admin or branches with 'stands' feature enabled
if (!$isSuper && !dash_can('stands')) {
    http_response_code(403);
    exit('۴۰۳ | دسترسی به کارتابل سفارشات برای این شعبه مجاز نمی‌باشد.');
}

// Branches list for Super Admin dropdown filter
$filterBranches = [];
if ($isSuper) {
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
if (!$isSuper) {
    // Non-superadmin: strictly locked to active branch
    $where[] = "o.branch_id = ?";
    $params[] = $activeBranchId;
} else {
    // Super admin
    if ($selectedBranchFilter !== 'all' && is_numeric($selectedBranchFilter)) {
        $where[] = "o.branch_id = ?";
        $params[] = (int)$selectedBranchFilter;
    }
}

// Search term
$search = trim((string)($_GET['q'] ?? ''));
if ($search !== '') {
    $where[] = "(o.tracking_code LIKE ? OR o.from_user LIKE ? OR o.to_user LIKE ? OR o.province LIKE ? OR o.city LIKE ? OR o.address LIKE ?)";
    $term = "%{$search}%";
    array_push($params, $term, $term, $term, $term, $term, $term);
}

// Filter by stand type (congrats / condolence)
$typeFilter = trim((string)($_GET['type'] ?? 'all'));
if (in_array($typeFilter, ['congrats', 'condolence'], true)) {
    $where[] = "s.stand_type = ?";
    $params[] = $typeFilter;
}

$whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$orders = [];
$statsTotalCount = 0;
$statsTotalPrice = 0;

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
    }
} catch (Throwable $e) {
    $orders = [];
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مدیریت سفارشات استند و کارت | پنل مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b; --warning:#f59e0b;
  --success-12:rgba(22,163,122,.14); --danger-12:rgba(224,85,107,.12); --warning-12:rgba(245,158,11,.12);
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --success-12:rgba(22,163,122,.18); --danger-12:rgba(224,85,107,.16); --warning-12:rgba(245,158,11,.18);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  color-scheme:dark; background-color:var(--color-bg);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;padding:28px 22px;transition:background .3s,color .3s}

.wrap{max-width:1160px;margin:0 auto}

.top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:12px}
.nav-back{display:inline-flex;align-items:center;gap:6px;color:var(--color-muted);text-decoration:none;font-weight:600;transition:color .2s;font-size:13px}
.nav-back:hover{color:var(--color-primary)}
.nav-back svg{width:16px;height:16px}

.btn-header-action{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:var(--color-surface);border:1px solid var(--color-border);border-radius:12px;font-size:13px;font-weight:700;color:var(--color-text);text-decoration:none;transition:all .2s;box-shadow:var(--shadow-sm)}
.btn-header-action:hover{border-color:var(--color-primary);color:var(--color-primary);transform:translateY(-1px)}
.btn-header-action svg{width:16px;height:16px;color:var(--color-primary)}

.head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.head-left{display:flex;align-items:center;gap:16px}
.head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.head-ic svg{width:27px;height:27px}

.branch-scope-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:rgba(0,123,122,.1);border:1px solid rgba(0,123,122,.2);border-radius:20px;font-size:12.5px;font-weight:700;color:var(--color-primary)}

/* Stats Bar */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:22px}
.stat-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:18px 20px;box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:14px}
.stat-ic{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;flex-shrink:0}
.stat-ic.orders{background:rgba(0,123,122,.12);color:var(--color-primary)}
.stat-ic.amount{background:rgba(22,163,122,.12);color:var(--success)}
.stat-ic.branch{background:rgba(245,158,11,.12);color:var(--warning)}
.stat-ic svg{width:22px;height:22px}
.stat-val{font-size:20px;font-weight:900;color:var(--color-text)}
.stat-lbl{font-size:12px;color:var(--color-muted)}

/* Filters Bar */
.filter-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:18px 22px;margin-bottom:22px;box-shadow:var(--shadow-sm)}
.filter-form{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.filter-input{flex:1;min-width:200px;background:var(--color-bg);border:1px solid var(--color-border);border-radius:10px;padding:10px 14px;font-family:inherit;font-size:13.5px;color:var(--color-text);outline:none;transition:border-color .2s}
.filter-input:focus{border-color:var(--color-primary)}
.filter-select{background:var(--color-bg);border:1px solid var(--color-border);border-radius:10px;padding:10px 14px;font-family:inherit;font-size:13.5px;color:var(--color-text);outline:none;transition:border-color .2s;cursor:pointer}
.filter-select:focus{border-color:var(--color-primary)}
.btn-filter{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:var(--color-primary);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:13.5px;font-weight:700;cursor:pointer;transition:opacity .2s}
.btn-filter:hover{opacity:.9}
.btn-filter-reset{display:inline-flex;align-items:center;padding:10px 16px;background:var(--color-bg);color:var(--color-muted);border:1px solid var(--color-border);border-radius:10px;font-family:inherit;font-size:13px;text-decoration:none;transition:all .2s}
.btn-filter-reset:hover{color:var(--color-text);border-color:var(--color-muted)}

/* Orders List */
.card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:24px;box-shadow:var(--shadow-sm)}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--color-border)}
.card-title{font-size:18px;font-weight:800;display:flex;align-items:center;gap:8px}

.order-grid{display:grid;gap:18px}
.order-item{background:var(--color-bg);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:20px;display:flex;flex-direction:column;gap:16px;transition:border-color .25s var(--ease),box-shadow .25s var(--ease)}
.order-item:hover{border-color:var(--color-primary-light);box-shadow:var(--shadow-md)}

.order-top{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.order-top-left{display:flex;align-items:center;gap:8px;flex-wrap:wrap}

.order-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:30px;background:var(--success-12);color:var(--success);font-weight:800;font-size:12.5px}
.order-badge svg{width:15px;height:15px}
.badge-branch{background:rgba(0,123,122,.12);color:var(--color-primary);font-weight:700;font-size:12px;padding:4px 10px;border-radius:20px;border:1px solid rgba(0,123,122,.2)}
.badge-geo{background:rgba(245,158,11,.12);color:var(--warning);font-weight:700;font-size:12px;padding:4px 10px;border-radius:20px}
.badge-type{font-size:11.5px;padding:3px 8px;border-radius:14px;font-weight:700}
.badge-type.congrats{background:rgba(22,163,122,.15);color:var(--success)}
.badge-type.condolence{background:rgba(100,116,139,.15);color:#475569}

.order-date{font-size:12.5px;color:var(--color-muted);display:flex;align-items:center;gap:6px;font-weight:500}

.order-body{display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;font-size:13.5px}
.order-field{background:var(--color-surface);padding:12px 14px;border-radius:10px;border:1px solid var(--color-border)}
.order-field span{display:block;color:var(--color-muted);font-size:12px;margin-bottom:2px}
.order-field strong{display:block;color:var(--color-text);font-weight:700;word-break:break-word}

.order-msg{grid-column:1 / -1;background:var(--color-surface);padding:14px;border-radius:10px;border:1px solid var(--color-border)}
.order-msg span{display:block;color:var(--color-muted);font-size:12px;margin-bottom:4px}
.order-msg p{color:var(--color-text);font-style:italic;line-height:1.7}

.order-footer{display:flex;justify-content:space-between;align-items:center;background:var(--color-surface);border:1px solid var(--color-border);padding:14px 18px;border-radius:12px;flex-wrap:wrap;gap:14px}
.order-price-box span{display:block;font-size:12px;color:var(--color-muted)}
.order-price{font-size:20px;font-weight:900;color:var(--color-primary)}

.order-benefactor{font-size:12.5px;color:var(--color-muted)}
.order-benefactor strong{color:var(--color-primary);font-weight:700}

.order-stand-thumb{display:flex;align-items:center;gap:12px}
.order-img{width:64px;height:84px;object-fit:contain;background:var(--color-bg);border:1px solid var(--color-border);border-radius:8px;padding:4px;transition:transform .2s}
.order-img:hover{transform:scale(1.08)}
.order-stand-info{display:flex;flex-direction:column;gap:2px}
.order-stand-title{font-weight:800;font-size:13.5px;color:var(--color-text)}

.empty{text-align:center;padding:70px 20px;color:var(--color-muted)}
.empty svg{width:56px;height:56px;opacity:0.25;margin-bottom:16px}
.empty p{font-size:16px;font-weight:600}
</style>
</head>
<body>

<div class="wrap">
  <div class="top-bar">
    <a href="index.php" class="nav-back">
      <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      بازگشت به داشبورد
    </a>
    <div style="display:flex;gap:10px;align-items:center">
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
        <h1 style="font-size:24px;font-weight:900">مدیریت سفارشات استند و کارت</h1>
        <p style="color:var(--color-muted);font-size:13px">مشاهده، رهگیری و ایزولاسیون سفارش‌های اختصاصی استند خیرین</p>
      </div>
    </div>
    <div>
      <?php if ($isSuper): ?>
        <span class="branch-scope-badge">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          نمای مدیر مرکزی (سراسر شعب)
        </span>
      <?php else: ?>
        <span class="branch-scope-badge">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px"><circle cx="12" cy="12" r="9"/></svg>
          شعبه: <?= htmlspecialchars($currentBranchRow['name'] ?? 'نامشخص') ?> (<?= htmlspecialchars($currentBranchRow['province'] ?? '') ?>)
        </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-ic orders">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= number_format($statsTotalCount) ?></div>
        <div class="stat-lbl">تعداد سفارشات بارگذاری‌شده</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-ic amount">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= number_format($statsTotalPrice) ?> <span style="font-size:13px;font-weight:600">تومان</span></div>
        <div class="stat-lbl">مجموع مبالغ اهدایی سفارشات</div>
      </div>
    </div>
    <?php if ($isSuper): ?>
      <div class="stat-card">
        <div class="stat-ic branch">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <div>
          <div class="stat-val"><?= count($filterBranches) ?></div>
          <div class="stat-lbl">شعب ارائه‌دهنده استند</div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Filters -->
  <div class="filter-card">
    <form method="GET" action="orders.php" class="filter-form">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="جستجو بر اساس کد رهگیری، فرستنده، گیرنده، استان یا شهر..." class="filter-input">

      <?php if ($isSuper): ?>
        <select name="branch" class="filter-select" onchange="this.form.submit()">
          <option value="all" <?= ($selectedBranchFilter === 'all') ? 'selected' : '' ?>>همه شعب مکسا</option>
          <?php foreach ($filterBranches as $fb): ?>
            <option value="<?= $fb['id'] ?>" <?= ($selectedBranchFilter == $fb['id']) ? 'selected' : '' ?>>
              شعبه <?= htmlspecialchars($fb['name']) ?> (<?= htmlspecialchars($fb['province'] ?: '—') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <select name="type" class="filter-select" onchange="this.form.submit()">
        <option value="all" <?= ($typeFilter === 'all') ? 'selected' : '' ?>>همه انواع استند</option>
        <option value="congrats" <?= ($typeFilter === 'congrats') ? 'selected' : '' ?>>فقط تبریک</option>
        <option value="condolence" <?= ($typeFilter === 'condolence') ? 'selected' : '' ?>>فقط تسلیت</option>
      </select>

      <button type="submit" class="btn-filter">
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:16px;height:16px"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        فیلتر
      </button>

      <?php if ($search !== '' || ($isSuper && $selectedBranchFilter !== 'all') || $typeFilter !== 'all'): ?>
        <a href="orders.php" class="btn-filter-reset">پاکسازی فیلتر</a>
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
          <div class="order-item">
            <div class="order-top">
              <div class="order-top-left">
                <div class="order-badge">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                  کد رهگیری: <?= htmlspecialchars($order['tracking_code'] ?: 'ثبت نشده') ?>
                </div>

                <?php if ($isSuper || !empty($order['branch_name'])): ?>
                  <span class="badge-branch">
                    شعبه: <?= htmlspecialchars($order['branch_name'] ?: 'مرکزی/ثبت قدیمی') ?>
                  </span>
                <?php endif; ?>

                <?php if (!empty($order['province']) || !empty($order['city'])): ?>
                  <span class="badge-geo">
                    <?= htmlspecialchars(trim(($order['province'] ?? '') . ' - ' . ($order['city'] ?? ''), ' -')) ?>
                  </span>
                <?php endif; ?>

                <?php if (!empty($order['stand_type'])): ?>
                  <span class="badge-type <?= $order['stand_type'] === 'congrats' ? 'congrats' : 'condolence' ?>">
                    <?= $order['stand_type'] === 'congrats' ? 'طرح تبریک' : 'طرح تسلیت' ?>
                  </span>
                <?php endif; ?>
              </div>

              <div class="order-date">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <?= htmlspecialchars($order['order_date'] ?: substr((string)$order['created_at'], 0, 10)) ?>
              </div>
            </div>

            <div class="order-body">
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
              <div class="order-field" style="grid-column: 1 / -1">
                <span>نشانی دقیق تحویل و برگزاری مراسم:</span>
                <strong><?= htmlspecialchars($order['address'] ?: '—') ?></strong>
              </div>
              <?php if (!empty($order['message'])): ?>
                <div class="order-msg">
                  <span>متن پیام اختصاصی روی استند:</span>
                  <p>"<?= nl2br(htmlspecialchars($order['message'])) ?>"</p>
                </div>
              <?php endif; ?>
            </div>

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

              <?php 
                $imgUrl = !empty($order['stand_image']) ? $order['stand_image'] : (!empty($order['image']) ? $order['image'] : '');
              ?>
              <?php if (!empty($imgUrl)): ?>
                <div class="order-stand-thumb">
                  <div class="order-stand-info" style="text-align:left">
                    <span style="font-size:11px;color:var(--color-muted)">طرح انتخابی</span>
                    <span class="order-stand-title"><?= htmlspecialchars($order['stand_title'] ?: 'طرح استاندارد') ?></span>
                  </div>
                  <img src="<?= htmlspecialchars($imgUrl) ?>" alt="استند" class="order-img" onerror="this.style.display='none'">
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>

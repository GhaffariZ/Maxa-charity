<?php
require_once __DIR__ . '/_guard.php';

try {
    $pdo->exec("ALTER TABLE `orders` ADD COLUMN `user_id` INT(11) NULL AFTER `id`");
    $pdo->exec("ALTER TABLE `orders` ADD COLUMN `tracking_code` VARCHAR(50) NULL AFTER `user_id`");
} catch(Exception $e) {}

// Fetch existing orders with user information
$stmt = $pdo->query("
    SELECT o.*, 
           CONCAT(u.first_name, ' ', u.last_name) AS benefactor_name, 
           u.email AS benefactor_email 
    FROM orders o 
    LEFT JOIN panel_users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>سفارشات | پنل مدیریت مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b;
  --success-12:rgba(22,163,122,.14); --danger-12:rgba(224,85,107,.12);
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --success-12:rgba(22,163,122,.18); --danger-12:rgba(224,85,107,.16);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  color-scheme:dark; background-color:var(--color-bg);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;padding:28px 22px;transition:background .3s,color .3s}

.wrap{max-width:1100px;margin:0 auto}

.head{display:flex;align-items:center;gap:16px;margin-bottom:20px}
.head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.head-ic svg{width:27px;height:27px}

.nav-back{display:inline-flex;align-items:center;gap:6px;color:var(--color-muted);text-decoration:none;font-weight:600;margin-bottom:12px;transition:color .2s}
.nav-back:hover{color:var(--color-primary)}
.nav-back svg{width:16px;height:16px}

.card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:24px;box-shadow:var(--shadow-sm);margin-bottom:24px}
.card h2{font-size:18px;font-weight:800;margin-bottom:20px;display:flex;align-items:center;gap:8px}

.order-grid{display:grid;gap:16px}
.order-item{background:var(--color-bg);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;display:flex;flex-direction:column;gap:12px;transition:border-color .25s var(--ease),box-shadow .25s var(--ease)}
.order-item:hover{border-color:var(--color-primary);box-shadow:var(--shadow-sm)}
.order-header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--color-border);padding-bottom:12px;margin-bottom:4px}
.order-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:30px;background:var(--success-12);color:var(--success);font-weight:700;font-size:13px}
.order-badge svg{width:15px;height:15px}
.order-date{font-size:13px;color:var(--color-muted);display:flex;align-items:center;gap:4px}

.order-details{display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:13.5px}
.order-details div span{display:block;color:var(--color-muted);font-size:12px;margin-bottom:4px}
.order-details div strong{display:block;color:var(--color-text);font-weight:700}

.order-footer{display:flex;justify-content:space-between;align-items:center;background:rgba(0,0,0,.02);padding:12px;border-radius:8px;margin-top:4px}
.order-footer.dark{background:rgba(255,255,255,.02)}
.order-price{font-size:18px;font-weight:900;color:var(--color-primary)}
.order-image{width:40px;height:60px;object-fit:contain;background:var(--color-surface);border:1px solid var(--color-border);border-radius:6px;padding:2px}

.empty{text-align:center;padding:60px 20px;color:var(--color-muted)}
.empty svg{width:48px;height:48px;opacity:0.3;margin-bottom:16px}
</style>
</head>
<body>

<div class="wrap">
  <a href="index.php" class="nav-back">
    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    بازگشت به داشبورد
  </a>

  <div class="head">
    <div class="head-ic">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
    </div>
    <div>
      <h1 style="font-size:24px;font-weight:900">سفارشات</h1>
      <p style="color:var(--color-muted)">لیست تمامی سفارشات استند و تاج گل</p>
    </div>
  </div>

  <div class="card">
    <h2>
      <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:20px;height:20px;color:var(--color-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
      لیست سفارشات
    </h2>

    <?php if (empty($orders)): ?>
      <div class="empty">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <p>هیچ سفارشی یافت نشد.</p>
      </div>
    <?php else: ?>
      <div class="order-grid">
        <?php foreach ($orders as $order): ?>
          <div class="order-item">
            <div class="order-header">
              <div class="order-badge">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                کد رهگیری: <?= htmlspecialchars($order['tracking_code'] ?: 'ندارد') ?>
              </div>
              <div class="order-date">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <?= htmlspecialchars($order['order_date'] ?: $order['created_at']) ?>
              </div>
            </div>

            <div class="order-details">
              <div>
                <span>از طرف:</span>
                <strong><?= htmlspecialchars($order['from_user']) ?></strong>
              </div>
              <div>
                <span>تقدیم به:</span>
                <strong><?= htmlspecialchars($order['to_user']) ?></strong>
              </div>
              <div style="grid-column: 1 / -1">
                <span>آدرس گیرنده:</span>
                <strong><?= htmlspecialchars($order['address']) ?></strong>
              </div>
              <?php if (!empty($order['message'])): ?>
                <div style="grid-column: 1 / -1; padding: 12px; background: rgba(0,0,0,.03); border-radius: 8px; font-style: italic;">
                  "<?= nl2br(htmlspecialchars($order['message'])) ?>"
                </div>
              <?php endif; ?>
            </div>

            <div class="order-footer" id="of-<?= $order['id'] ?>">
              <div>
                <div style="color:var(--color-muted);font-size:12px;margin-bottom:4px">مبلغ سفارش:</div>
                <div class="order-price"><?= number_format($order['total_price'] ?: 0) ?> تومان</div>
                <?php if ($order['benefactor_name']): ?>
                  <div style="font-size:12px; margin-top:8px; color:var(--color-primary);">سفارش دهنده (کاربر سایت): <?= htmlspecialchars($order['benefactor_name']) ?></div>
                <?php endif; ?>
              </div>
              <?php if (!empty($order['image'])): ?>
                <img src="<?= htmlspecialchars($order['image']) ?>" alt="Stand" class="order-image">
              <?php endif; ?>
            </div>
            <script>try{if(localStorage.getItem('maxa-theme')==='dark')document.getElementById('of-<?= $order['id'] ?>').classList.add('dark');}catch(e){}</script>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>

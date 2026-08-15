<?php
require_once __DIR__ . '/_guard.php';

// Safe schema migration for orders table
try {
    $pdo->exec("ALTER TABLE `orders` ADD COLUMN `user_id` BIGINT(20) UNSIGNED NULL AFTER `id`");
} catch (Throwable $e) {}
try {
    $pdo->exec("ALTER TABLE `orders` ADD COLUMN `tracking_code` VARCHAR(50) NULL AFTER `user_id`");
} catch (Throwable $e) {}

// Fetch existing orders with benefactor user information
$orders = [];
try {
    $stmt = $pdo->query("
        SELECT o.*, 
               TRIM(CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, ''))) AS benefactor_name, 
               u.email AS benefactor_email,
               p.phone AS benefactor_phone
        FROM orders o 
        LEFT JOIN panel_users u ON o.user_id = u.id 
        LEFT JOIN user_profiles p ON o.user_id = p.user_id 
        ORDER BY o.id DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    // Fallback if JOIN fails
    try {
        $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $ex) {
        $orders = [];
    }
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

.head{display:flex;align-items:center;gap:16px;margin-bottom:24px}
.head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.head-ic svg{width:27px;height:27px}

.nav-back{display:inline-flex;align-items:center;gap:6px;color:var(--color-muted);text-decoration:none;font-weight:600;margin-bottom:14px;transition:color .2s;font-size:13px}
.nav-back:hover{color:var(--color-primary)}
.nav-back svg{width:16px;height:16px}

.card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:24px;box-shadow:var(--shadow-sm);margin-bottom:24px}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--color-border)}
.card-title{font-size:18px;font-weight:800;display:flex;align-items:center;gap:8px}

.order-count{font-size:13px;color:var(--color-muted);background:var(--color-bg);padding:4px 12px;border-radius:20px;border:1px solid var(--color-border)}

.order-grid{display:grid;gap:16px}
.order-item{background:var(--color-bg);border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:20px;display:flex;flex-direction:column;gap:16px;transition:border-color .25s var(--ease),box-shadow .25s var(--ease)}
.order-item:hover{border-color:var(--color-primary-light);box-shadow:var(--shadow-md)}

.order-top{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.order-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:30px;background:var(--success-12);color:var(--success);font-weight:800;font-size:13px}
.order-badge svg{width:16px;height:16px}
.order-date{font-size:13px;color:var(--color-muted);display:flex;align-items:center;gap:6px;font-weight:500}

.order-body{display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;font-size:13.5px}
.order-field{background:var(--color-surface);padding:12px 14px;border-radius:10px;border:1px solid var(--color-border)}
.order-field span{display:block;color:var(--color-muted);font-size:12px;margin-bottom:2px}
.order-field strong{display:block;color:var(--color-text);font-weight:700;word-break:break-word}

.order-msg{grid-column:1 / -1;background:var(--color-surface);padding:14px;border-radius:10px;border:1px solid var(--color-border)}
.order-msg span{display:block;color:var(--color-muted);font-size:12px;margin-bottom:4px}
.order-msg p{color:var(--color-text);font-style:italic;line-height:1.7}

.order-footer{display:flex;justify-content:space-between;align-items:center;background:var(--color-surface);border:1px solid var(--color-border);padding:14px 18px;border-radius:12px;flex-wrap:wrap;gap:12px}
.order-price-box span{display:block;font-size:12px;color:var(--color-muted)}
.order-price{font-size:20px;font-weight:900;color:var(--color-primary)}

.order-benefactor{font-size:12.5px;color:var(--color-muted)}
.order-benefactor strong{color:var(--color-primary);font-weight:700}

.order-img{width:54px;height:74px;object-fit:contain;background:var(--color-bg);border:1px solid var(--color-border);border-radius:8px;padding:4px}

.empty{text-align:center;padding:70px 20px;color:var(--color-muted)}
.empty svg{width:56px;height:56px;opacity:0.25;margin-bottom:16px}
.empty p{font-size:16px;font-weight:600}
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
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
    </div>
    <div>
      <h1 style="font-size:24px;font-weight:900">مدیریت سفارشات</h1>
      <p style="color:var(--color-muted);font-size:13px">مشاهده و رهگیری تمامی سفارش‌های استند تسلیت و تبریک</p>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:20px;height:20px;color:var(--color-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
        لیست سفارشات ثبت‌شده
      </div>
      <div class="order-count"><?= count($orders) ?> سفارش</div>
    </div>

    <?php if (empty($orders)): ?>
      <div class="empty">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <p>هنوز هیچ سفارشی در سیستم ثبت نشده است.</p>
      </div>
    <?php else: ?>
      <div class="order-grid">
        <?php foreach ($orders as $order): ?>
          <div class="order-item">
            <div class="order-top">
              <div class="order-badge">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                کد رهگیری: <?= htmlspecialchars($order['tracking_code'] ?: 'ثبت نشده') ?>
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
              <div class="order-field" style="grid-column: 1 / -1">
                <span>آدرس تحویل استند:</span>
                <strong><?= htmlspecialchars($order['address'] ?: '—') ?></strong>
              </div>
              <?php if (!empty($order['message'])): ?>
                <div class="order-msg">
                  <span>متن پیام روی استند:</span>
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
              <?php if (!empty($order['image'])): ?>
                <img src="<?= htmlspecialchars($order['image']) ?>" alt="استند" class="order-img" onerror="this.style.display='none'">
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

<?php
require_once __DIR__ . '/_guard.php';

// Create table if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS donation_impacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    quantity INT NOT NULL,
    quantity_unit VARCHAR(50) NOT NULL,
    unit_price BIGINT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    csrf_check();
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $quantity_unit = trim($_POST['quantity_unit'] ?? '');
    $unit_price = isset($_POST['unit_price']) && $_POST['unit_price'] !== '' ? (int)$_POST['unit_price'] : null;
    
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
            $imageName = time() . '_' . random_int(100, 999) . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/impacts/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (!move_uploaded_file($tmpName, $uploadDir . $imageName)) {
                $imageName = '';
            }
        }
    }
    
    if ($title && $description && $quantity > 0 && $quantity_unit && $imageName) {
        $stmt = $pdo->prepare("INSERT INTO donation_impacts (title, image, description, quantity, quantity_unit, unit_price, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $imageName, $description, $quantity, $quantity_unit, $unit_price]);
        $_SESSION['impact_flash'] = ['type' => 'ok', 'text' => 'اثر کمک با موفقیت ثبت شد.'];
        header("Location: donation-impacts.php");
        exit;
    } else {
        $_SESSION['impact_flash'] = ['type' => 'err', 'text' => 'لطفاً تمامی فیلدهای الزامی را پر کنید و یک تصویر معتبر آپلود نمایید.'];
    }
}

// Fetch existing impacts
$stmt = $pdo->query("SELECT * FROM donation_impacts ORDER BY id DESC");
$impacts = $stmt->fetchAll();

$FLASH = $_SESSION['impact_flash'] ?? null;
unset($_SESSION['impact_flash']);

$isCreateView = isset($_GET['view']) && $_GET['view'] === 'create';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>اثرات کمک | پنل مکسا</title>
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

.wrap{max-width:920px;margin:0 auto}

.head{display:flex;align-items:center;gap:16px;margin-bottom:20px}
.head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.head-ic svg{width:27px;height:27px}
.head h1{font-size:22px;font-weight:800;letter-spacing:-.01em}
.head p{font-size:13px;color:var(--color-muted);margin-top:3px}
.head .spacer{flex:1}

.btn{font-family:inherit;font-weight:700;border:none;cursor:pointer;border-radius:12px;display:inline-flex;align-items:center;gap:7px;
  transition:filter .2s,transform .14s,box-shadow .22s,border-color .2s; text-decoration:none;}
.btn svg{width:16px;height:16px}
.btn:active{transform:scale(.97)}
.btn-primary{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff;padding:11px 18px;font-size:13px;box-shadow:0 12px 22px -12px rgba(0,123,122,.8)}
.btn-primary:hover{transform:translateY(-1px); color:#fff;}
.btn-ghost{background:var(--color-surface);color:var(--color-text);border:1px solid var(--color-border);padding:10px 15px;font-size:12.5px}
.btn-ghost:hover{border-color:var(--color-primary-light)}

.flash{border-radius:13px;padding:12px 16px;font-size:13px;font-weight:700;margin-bottom:18px;line-height:1.8;display:flex;align-items:center;gap:9px}
.flash.ok{background:var(--success-12);color:var(--success);border:1px solid rgba(22,163,122,.25)}
.flash.err{background:var(--danger-12);color:var(--danger);border:1px solid rgba(224,85,107,.25)}

/* Cards Grid */
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 20px; }
.card { background: var(--color-surface); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; position: relative; overflow: hidden; border: 1px solid var(--color-border); }
.card-icon { width: 64px; height: 64px; border-radius: 18px; margin-bottom: 16px; object-fit: cover; align-self: flex-start; background: #007b7a; display: flex; align-items: center; justify-content: center; }
.card-icon img { width: 100%; height: 100%; border-radius: 18px; object-fit: cover; }
.card-title { font-size: 18px; font-weight: 800; color: var(--color-text); margin-bottom: 16px; text-align: right; flex-grow: 1; }
.card-divider { height: 1px; background: var(--color-border); margin: 0 0 16px 0; border: none; }
.card-footer { display: flex; justify-content: space-between; align-items: center; }
.card-qty { font-size: 16px; font-weight: 800; color: var(--color-primary); }
.card-ach { font-size: 13px; color: var(--color-muted); font-weight: 600; }
.card-total { display: flex; justify-content: space-between; align-items: center; gap: 6px; margin-top: 12px; border-top: 1px dashed var(--color-border); padding-top: 12px; }
.card-total-amount { font-size: 18px; font-weight: 800; color: var(--color-primary-light); }
.card-total-label { font-size: 13px; color: var(--color-muted); font-weight: 600; }

/* Form Elements */
.panel{background:var(--color-surface);border:1.5px solid var(--color-border);border-radius:var(--radius);box-shadow:var(--shadow-sm);padding:24px;}
.field{margin-bottom:18px}
.field label{display:block;font-size:13px;font-weight:700;margin-bottom:8px;color:var(--color-text)}
.field input[type=text], .field input[type=number], .field input[type=file], .field select, .field textarea{width:100%;font-family:inherit;font-size:13.5px;color:var(--color-text);background:var(--color-bg);border:1.5px solid var(--color-border);border-radius:var(--radius-sm);padding:11px 13px;line-height:1.9;transition:border-color .2s}
.field input:focus, .field select:focus, .field textarea:focus{outline:none;border-color:var(--color-primary-light);background:var(--color-surface)}
.field textarea{resize:vertical;min-height:100px}
.form-row { display: flex; gap: 16px; }
.form-row .field { flex: 1; }
</style>
</head>
<body>

<div class="wrap">
  <div class="head">
    <div class="head-ic">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
    </div>
    <div>
      <h1>اثرات کمک</h1>
      <p>مدیریت و نمایش دستاوردهای حاصل از حمایت‌های مردمی</p>
    </div>
    <div class="spacer"></div>
    <?php if ($isCreateView): ?>
      <a href="donation-impacts.php" class="btn btn-ghost">بازگشت به لیست</a>
    <?php else: ?>
      <a href="donation-impacts.php?view=create" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        افزودن اثر کمک
      </a>
    <?php endif; ?>
  </div>

  <?php if ($FLASH): ?>
    <div class="flash <?= $FLASH['type'] ?>"><?= htmlspecialchars($FLASH['text']) ?></div>
  <?php endif; ?>

  <?php if ($isCreateView): ?>
    <div class="panel">
      <form action="donation-impacts.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="add">
        
        <div class="field">
          <label>نام کمک (عنوان)</label>
          <input type="text" name="title" required placeholder="مثال: حمایت از بیماران نیازمند به ویلچر">
        </div>
        
        <div class="field">
          <label>توضیح کمک</label>
          <textarea name="description" required placeholder="مثال: با کمک‌های شما ۱۰ ویلچر..."></textarea>
        </div>
        
        <div class="form-row">
          <div class="field">
            <label>تعداد دستاورد (عدد)</label>
            <input type="number" name="quantity" required placeholder="مثال: 10" min="1">
          </div>
          <div class="field">
            <label>واحد تعداد</label>
            <input type="text" name="quantity_unit" required placeholder="مثال: ویلچر">
          </div>
        </div>
        
        <div class="form-row">
          <div class="field">
            <label>قیمت تمام شده هر واحد (اختیاری)</label>
            <input type="number" name="unit_price" placeholder="به تومان">
          </div>
          <div class="field">
            <label>تصویر کمک</label>
            <input type="file" name="image" accept="image/*" required>
          </div>
        </div>
        
        <div style="margin-top: 24px;">
          <button type="submit" class="btn btn-primary">ذخیره اثر کمک</button>
        </div>
      </form>
    </div>
  <?php else: ?>
    <?php if (empty($impacts)): ?>
      <div style="text-align:center; padding:50px; color:var(--color-muted); border:2px dashed var(--color-border); border-radius:var(--radius-lg);">
        هنوز هیچ اثر کمکی ثبت نشده است.
      </div>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($impacts as $item): ?>
          <div class="card">
            <div class="card-icon">
              <img src="/uploads/impacts/<?= htmlspecialchars($item['image']) ?>" alt="">
            </div>
            <div class="card-title"><?= htmlspecialchars($item['title']) ?></div>
            <hr class="card-divider">
            <div class="card-footer">
              <div class="card-ach">تعداد:</div>
              <div class="card-qty"><?= strtr((string)$item['quantity'], ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']) ?> <?= htmlspecialchars($item['quantity_unit']) ?></div>
            </div>
            <?php 
              if ($item['unit_price'] && $item['quantity']) {
                $total_amount = $item['unit_price'] * $item['quantity'];
                echo '<div class="card-total">';
                echo '  <div class="card-total-label">مبلغ کل:</div>';
                echo '  <div class="card-total-amount">' . strtr((string)number_format($total_amount), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']) . ' تومان</div>';
                echo '</div>';
              }
            ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

</div>

<script>
(function(){
  "use strict";
  function applyTheme(){
    var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
    if(d) document.documentElement.setAttribute('data-theme','dark'); else document.documentElement.removeAttribute('data-theme');
  }
  applyTheme();
  window.addEventListener('storage',function(e){ if(!e||e.key==='maxa-theme'||e.key===null) applyTheme(); });
})();
</script>
</body>
</html>

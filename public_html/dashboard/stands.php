<?php
require_once __DIR__ . '/_guard.php';

$isSuper = dash_is_super();
$isHq = dash_is_hq_view();
$userActiveBranch = dash_active_branch_id();

// Safe self-healing schema creation
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Throwable $e) {}

// For Super Admin: allow selecting which branch to manage stands for
$targetBranchId = $userActiveBranch;
if ($isSuper) {
    if (isset($_GET['branch_id']) && is_numeric($_GET['branch_id'])) {
        $targetBranchId = (int)$_GET['branch_id'];
    }
}

// Load branch details
$targetBranch = dash_load_branch($targetBranchId);

// Access check:
// 1. Super Admin can manage any branch.
// 2. Branch admin must have 'stands' feature enabled for their branch.
if (!$isSuper && !dash_can('stands')) {
    http_response_code(403);
    exit('۴۰۳ | دسترسی به مدیریت استندها برای این شعبه مجاز نمی‌باشد.');
}

// Ensure upload directory exists
$uploadDir = __DIR__ . '/../uploads/stands';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$msg = '';
$msgType = 'success';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    // Verify target branch is valid and NOT HQ
    if (!$targetBranch || (int)$targetBranch['is_hq'] === 1) {
        $msg = 'خطا: استندها فقط برای شعب غیراز ستاد مرکزی قابل تعریف هستند.';
        $msgType = 'danger';
    } else {
        if ($action === 'create') {
            $title = trim((string)($_POST['title'] ?? ''));
            $standType = in_array($_POST['stand_type'] ?? '', ['congrats', 'condolence'], true) ? $_POST['stand_type'] : 'congrats';
            $unitPrice = max(0, (int)($_POST['unit_price'] ?? 0));
            $description = trim((string)($_POST['description'] ?? ''));
            $sortOrder = (int)($_POST['sort_order'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $imagePath = '';

            // Handle file upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['image']['tmp_name'];
                $fileName = $_FILES['image']['name'];
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($ext, $allowed, true)) {
                    $newFileName = 'stand_' . $targetBranchId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $destPath = $uploadDir . '/' . $newFileName;
                    if (move_uploaded_file($fileTmp, $destPath)) {
                        $imagePath = '/uploads/stands/' . $newFileName;
                    }
                }
            }

            // Fallback to preset or URL if no file uploaded
            if (empty($imagePath)) {
                $imagePath = trim((string)($_POST['preset_image'] ?? ''));
            }

            if (empty($title)) {
                $msg = 'لطفاً عنوان استند را وارد کنید.';
                $msgType = 'danger';
            } elseif (empty($imagePath)) {
                $msg = 'لطفاً یک تصویر برای استند انتخاب یا آپلود کنید.';
                $msgType = 'danger';
            } else {
                $st = $pdo->prepare("
                    INSERT INTO `stands` (`branch_id`, `title`, `stand_type`, `image`, `unit_price`, `description`, `is_active`, `sort_order`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $st->execute([$targetBranchId, $title, $standType, $imagePath, $unitPrice, $description, $isActive, $sortOrder]);
                dash_audit('stand_create', ['branch_id' => $targetBranchId, 'title' => $title]);
                $msg = 'طرح استند با موفقیت ایجاد شد.';
                $msgType = 'success';
            }

        } elseif ($action === 'update') {
            $standId = (int)($_POST['id'] ?? 0);
            // Verify ownership
            $st = $pdo->prepare("SELECT * FROM `stands` WHERE `id` = ? AND `branch_id` = ? LIMIT 1");
            $st->execute([$standId, $targetBranchId]);
            $existing = $st->fetch();

            if (!$existing) {
                $msg = 'استند مورد نظر یافت نشد.';
                $msgType = 'danger';
            } else {
                $title = trim((string)($_POST['title'] ?? ''));
                $standType = in_array($_POST['stand_type'] ?? '', ['congrats', 'condolence'], true) ? $_POST['stand_type'] : 'congrats';
                $unitPrice = max(0, (int)($_POST['unit_price'] ?? 0));
                $description = trim((string)($_POST['description'] ?? ''));
                $sortOrder = (int)($_POST['sort_order'] ?? 0);
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                $imagePath = $existing['image'];

                // If new image uploaded
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['image']['tmp_name'];
                    $fileName = $_FILES['image']['name'];
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                    if (in_array($ext, $allowed, true)) {
                        $newFileName = 'stand_' . $targetBranchId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $destPath = $uploadDir . '/' . $newFileName;
                        if (move_uploaded_file($fileTmp, $destPath)) {
                            $imagePath = '/uploads/stands/' . $newFileName;
                        }
                    }
                } elseif (!empty($_POST['preset_image'])) {
                    $imagePath = trim((string)$_POST['preset_image']);
                }

                if (empty($title)) {
                    $msg = 'لطفاً عنوان استند را وارد کنید.';
                    $msgType = 'danger';
                } else {
                    $st = $pdo->prepare("
                        UPDATE `stands` 
                        SET `title` = ?, `stand_type` = ?, `image` = ?, `unit_price` = ?, `description` = ?, `is_active` = ?, `sort_order` = ?
                        WHERE `id` = ? AND `branch_id` = ?
                    ");
                    $st->execute([$title, $standType, $imagePath, $unitPrice, $description, $isActive, $sortOrder, $standId, $targetBranchId]);
                    dash_audit('stand_update', ['id' => $standId, 'title' => $title]);
                    $msg = 'طرح استند با موفقیت بروزرسانی شد.';
                    $msgType = 'success';
                }
            }

        } elseif ($action === 'toggle') {
            $standId = (int)($_POST['id'] ?? 0);
            $st = $pdo->prepare("UPDATE `stands` SET `is_active` = 1 - `is_active` WHERE `id` = ? AND `branch_id` = ?");
            $st->execute([$standId, $targetBranchId]);
            $msg = 'وضعیت نمایش استند تغییر یافت.';
            $msgType = 'success';

        } elseif ($action === 'delete') {
            $standId = (int)($_POST['id'] ?? 0);
            $st = $pdo->prepare("DELETE FROM `stands` WHERE `id` = ? AND `branch_id` = ?");
            $st->execute([$standId, $targetBranchId]);
            dash_audit('stand_delete', ['id' => $standId]);
            $msg = 'طرح استند با موفقیت حذف شد.';
            $msgType = 'success';
        }
    }
}

// Fetch all branches with stands feature for Super Admin
$branchesList = [];
if ($isSuper) {
    try {
        $st = $pdo->query("SELECT id, name, province, city, is_hq FROM branches WHERE is_hq = 0 AND status = 'active' ORDER BY name ASC");
        $branchesList = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}
}

// Fetch stands for target branch
$stands = [];
if ($targetBranch && (int)$targetBranch['is_hq'] === 0) {
    try {
        $st = $pdo->prepare("SELECT * FROM `stands` WHERE `branch_id` = ? ORDER BY `sort_order` ASC, `id` DESC");
        $st->execute([$targetBranchId]);
        $stands = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مدیریت طرح‌های استند | پنل مکسا</title>
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

.btn-orders-link{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:var(--color-surface);border:1px solid var(--color-border);border-radius:12px;font-size:13px;font-weight:700;color:var(--color-text);text-decoration:none;transition:all .2s;box-shadow:var(--shadow-sm)}
.btn-orders-link:hover{border-color:var(--color-primary);color:var(--color-primary);transform:translateY(-1px)}
.btn-orders-link svg{width:16px;height:16px;color:var(--color-primary)}

.head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.head-left{display:flex;align-items:center;gap:16px}
.head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.head-ic svg{width:27px;height:27px}

.branch-scope-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:rgba(0,123,122,.1);border:1px solid rgba(0,123,122,.2);border-radius:20px;font-size:13px;font-weight:700;color:var(--color-primary)}

.alert{padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:13.5px;font-weight:600;display:flex;align-items:center;gap:10px}
.alert-success{background:var(--success-12);color:var(--success);border:1px solid rgba(22,163,122,.3)}
.alert-danger{background:var(--danger-12);color:var(--danger);border:1px solid rgba(224,85,107,.3)}
.alert-warning{background:var(--warning-12);color:var(--warning);border:1px solid rgba(245,158,11,.3)}

/* Branch Selector Card for Super Admin */
.branch-selector-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:18px 22px;margin-bottom:22px;box-shadow:var(--shadow-sm);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px}
.branch-select-form{display:flex;align-items:center;gap:10px}
.branch-select{background:var(--color-bg);border:1px solid var(--color-border);border-radius:10px;padding:10px 14px;font-family:inherit;font-size:13.5px;color:var(--color-text);outline:none;cursor:pointer}

/* Main Cards */
.card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:24px;box-shadow:var(--shadow-sm);margin-bottom:24px}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--color-border);flex-wrap:wrap;gap:12px}
.card-title{font-size:18px;font-weight:800;display:flex;align-items:center;gap:8px}

.btn-add{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;background:var(--color-primary);color:#fff;border:none;border-radius:12px;font-family:inherit;font-size:13.5px;font-weight:700;cursor:pointer;transition:all .2s;box-shadow:0 6px 16px -4px rgba(0,123,122,.4)}
.btn-add:hover{background:var(--color-primary-dark);transform:translateY(-1px)}
.btn-add svg{width:16px;height:16px}

/* Stands Grid */
.stands-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:20px}
.stand-card{background:var(--color-bg);border:1px solid var(--color-border);border-radius:var(--radius-sm);overflow:hidden;display:flex;flex-direction:column;transition:border-color .25s var(--ease),box-shadow .25s var(--ease)}
.stand-card:hover{border-color:var(--color-primary-light);box-shadow:var(--shadow-md)}

.stand-card-img-wrap{height:220px;background:var(--color-surface);position:relative;display:grid;place-items:center;border-bottom:1px solid var(--color-border);padding:16px;overflow:hidden}
.stand-card-img{max-height:100%;max-width:100%;object-fit:contain;transition:transform .3s var(--ease)}
.stand-card:hover .stand-card-img{transform:scale(1.05)}

.stand-type-badge{position:absolute;top:12px;right:12px;padding:4px 10px;border-radius:16px;font-size:11.5px;font-weight:800}
.stand-type-badge.congrats{background:rgba(22,163,122,.9);color:#fff}
.stand-type-badge.condolence{background:rgba(51,65,85,.9);color:#fff}

.stand-status-dot{position:absolute;top:12px;left:12px;display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:16px;font-size:11px;font-weight:700}
.stand-status-dot.active{background:var(--success-12);color:var(--success)}
.stand-status-dot.inactive{background:var(--danger-12);color:var(--danger)}

.stand-card-body{padding:18px;display:flex;flex-direction:column;gap:10px;flex:1}
.stand-title{font-size:16px;font-weight:800;color:var(--color-text)}
.stand-desc{font-size:12.5px;color:var(--color-muted);line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.stand-price{font-size:17px;font-weight:900;color:var(--color-primary);margin-top:auto}

.stand-card-footer{padding:12px 18px;background:var(--color-surface);border-top:1px solid var(--color-border);display:flex;align-items:center;justify-content:space-between;gap:8px}
.btn-action{padding:7px 12px;border-radius:8px;font-family:inherit;font-size:12px;font-weight:700;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:5px;border:1px solid var(--color-border);background:var(--color-bg);color:var(--color-text)}
.btn-action:hover{border-color:var(--color-primary);color:var(--color-primary)}
.btn-action.delete:hover{border-color:var(--danger);color:var(--danger)}

/* Modal Styles */
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;display:none;align-items:center;justify-content:center;padding:20px}
.modal-backdrop.open{display:flex}
.modal{background:var(--color-surface);border-radius:var(--radius);max-width:560px;width:100%;border:1px solid var(--color-border);box-shadow:var(--shadow-md);overflow:hidden;animation:modalIn .25s var(--ease)}
@keyframes modalIn{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}
.modal-head{padding:18px 24px;border-bottom:1px solid var(--color-border);display:flex;align-items:center;justify-content:space-between}
.modal-title{font-size:16px;font-weight:800}
.modal-close{background:none;border:none;color:var(--color-muted);cursor:pointer;font-size:20px;padding:4px}
.modal-body{padding:24px;max-height:80vh;overflow-y:auto}

.form-group{margin-bottom:16px}
.form-label{display:block;font-size:13px;font-weight:700;margin-bottom:6px;color:var(--color-text)}
.form-control{width:100%;background:var(--color-bg);border:1px solid var(--color-border);border-radius:10px;padding:10px 14px;font-family:inherit;font-size:13.5px;color:var(--color-text);outline:none;transition:border-color .2s}
.form-control:focus{border-color:var(--color-primary)}

.preview-box{width:100%;height:140px;border:2px dashed var(--color-border);border-radius:12px;display:grid;place-items:center;margin-top:8px;background:var(--color-bg);overflow:hidden}
.preview-box img{max-height:100%;max-width:100%;object-fit:contain}

.switch-label{display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:700;font-size:13.5px}

.empty{text-align:center;padding:60px 20px;color:var(--color-muted)}
.empty svg{width:56px;height:56px;opacity:0.25;margin-bottom:14px}
</style>
</head>
<body>

<div class="wrap">
  <div class="top-bar">
    <a href="index.php" class="nav-back">
      <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      بازگشت به داشبورد
    </a>
    <a href="orders.php" class="btn-orders-link">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      مشاهده سفارشات استند
    </a>
  </div>

  <div class="head">
    <div class="head-left">
      <div class="head-ic">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="9" r="5.5"/><path d="M8.2 13.2 6.5 21l5.5-3 5.5 3-1.7-7.8"/></svg>
      </div>
      <div>
        <h1 style="font-size:24px;font-weight:900">مدیریت استندهای اختصاصی شعب</h1>
        <p style="color:var(--color-muted);font-size:13px">تعریف، ویرایش، قیمت‌گذاری و تنظیم طرح‌های استند تبریک و تسلیت</p>
      </div>
    </div>
    <div>
      <span class="branch-scope-badge">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px"><circle cx="12" cy="12" r="9"/></svg>
        شعبه در حال مدیریت: <?= htmlspecialchars($targetBranch['name'] ?? 'نامشخص') ?> (<?= htmlspecialchars($targetBranch['province'] ?? '') ?>)
      </span>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
    <div class="alert alert-<?= $msgType ?>">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:18px;height:18px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <?php if ($targetBranch && (int)$targetBranch['is_hq'] === 1): ?>
    <div class="alert alert-warning">
      <strong>توجه ستاد مرکزی:</strong> دفتر مرکزی به هیچ عنوان استندی ارائه نمی‌دهد. لطفاً برای مشاهده یا تعریف استندها، یکی از شعب اجرایی را از فرم زیر انتخاب کنید.
    </div>
  <?php endif; ?>

  <?php if ($isSuper && !empty($branchesList)): ?>
    <div class="branch-selector-card">
      <div>
        <strong>انتخاب شعبه جهت مدیریت استندها:</strong>
        <p style="font-size:12.5px;color:var(--color-muted)">به عنوان مدیر کل، می‌توانید استندهای اختصاصی هر یک از شعب را مدیریت نمایید.</p>
      </div>
      <form method="GET" action="stands.php" class="branch-select-form">
        <select name="branch_id" class="branch-select" onchange="this.form.submit()">
          <?php foreach ($branchesList as $b): ?>
            <option value="<?= $b['id'] ?>" <?= ($targetBranchId == $b['id']) ? 'selected' : '' ?>>
              شعبه <?= htmlspecialchars($b['name']) ?> (<?= htmlspecialchars($b['province'] ?: '—') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($targetBranch && (int)$targetBranch['is_hq'] === 0): ?>
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:20px;height:20px;color:var(--color-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
          طرح‌های استند شعبه <?= htmlspecialchars($targetBranch['name']) ?> (<?= count($stands) ?> طرح)
        </div>
        <button type="button" class="btn-add" onclick="openAddModal()">
          <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          افزودن طرح استند جدید
        </button>
      </div>

      <?php if (empty($stands)): ?>
        <div class="empty">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="9" r="5.5"/><path d="M8.2 13.2 6.5 21l5.5-3 5.5 3-1.7-7.8"/></svg>
          <p>هنوز هیچ طرح استندی برای این شعبه ثبت نشده است.</p>
          <p style="font-size:12.5px;color:var(--color-muted);margin-top:6px">با کلیک بر روی دکمه «افزودن طرح استند جدید»، اولین طرح استند را تعریف نمایید.</p>
        </div>
      <?php else: ?>
        <div class="stands-grid">
          <?php foreach ($stands as $st): ?>
            <div class="stand-card">
              <div class="stand-card-img-wrap">
                <span class="stand-type-badge <?= $st['stand_type'] === 'congrats' ? 'congrats' : 'condolence' ?>">
                  <?= $st['stand_type'] === 'congrats' ? 'استند تبریک' : 'استند تسلیت' ?>
                </span>
                <span class="stand-status-dot <?= (int)$st['is_active'] === 1 ? 'active' : 'inactive' ?>">
                  <?= (int)$st['is_active'] === 1 ? '● فعال در سایت' : '○ غیرفعال' ?>
                </span>
                <img src="<?= htmlspecialchars($st['image']) ?>" alt="<?= htmlspecialchars($st['title']) ?>" class="stand-card-img" onerror="this.src='/uploads/stand/happy/1.jpg'">
              </div>

              <div class="stand-card-body">
                <h3 class="stand-title"><?= htmlspecialchars($st['title']) ?></h3>
                <p class="stand-desc"><?= htmlspecialchars($st['description'] ?: 'بدون توضیحات تکمیلی') ?></p>
                <div class="stand-price">
                  <?= number_format((int)$st['unit_price']) ?> <span style="font-size:12px;font-weight:600">تومان</span>
                </div>
              </div>

              <div class="stand-card-footer">
                <div style="display:flex;gap:6px">
                  <button type="button" class="btn-action" onclick='openEditModal(<?= json_encode($st, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>)'>
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    ویرایش
                  </button>

                  <form method="POST" style="display:inline" onsubmit="return confirm('آیا از تغییر وضعیت این استند اطمینان دارید؟')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= $st['id'] ?>">
                    <button type="submit" class="btn-action">
                      <?= (int)$st['is_active'] === 1 ? 'غیرفعال‌سازی' : 'فعال‌سازی' ?>
                    </button>
                  </form>
                </div>

                <form method="POST" style="display:inline" onsubmit="return confirm('آیا از حذف کامل این طرح استند اطمینان دارید؟')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $st['id'] ?>">
                  <button type="submit" class="btn-action delete">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    حذف
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Modal: Add / Edit Stand -->
<div class="modal-backdrop" id="standModal">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title" id="modalTitle">افزودن طرح استند جدید</div>
      <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
    </div>
    <form method="POST" action="stands.php<?= $isSuper ? '?branch_id=' . $targetBranchId : '' ?>" enctype="multipart/form-data" id="standForm">
      <?= csrf_field() ?>
      <input type="hidden" name="action" id="formAction" value="create">
      <input type="hidden" name="id" id="standId" value="">

      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="inputTitle">عنوان طرح استند *</label>
          <input type="text" name="title" id="inputTitle" required class="form-control" placeholder="مثال: استند گل و پروانه تبریک">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="form-group">
            <label class="form-label" for="inputType">نوع استند</label>
            <select name="stand_type" id="inputType" class="form-control">
              <option value="congrats">تبریک و شادباش</option>
              <option value="condolence">تسلیت و یادبود</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="inputPrice">مبلغ واحد (تومان) *</label>
            <input type="number" name="unit_price" id="inputPrice" required min="0" step="10000" class="form-control" placeholder="مثال: 1500000">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="inputDesc">توضیحات کوتاه</label>
          <textarea name="description" id="inputDesc" rows="2" class="form-control" placeholder="مشخصات ابعاد، متریال یا نوع گل‌آرایی..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">تصویر استند (فرمت JPG, PNG با پس‌زمینه شفاف)</label>
          <input type="file" name="image" id="inputImageFile" accept="image/png,image/jpeg,image/webp" class="form-control" onchange="previewUpload(this)">
          <div style="font-size:12px;color:var(--color-muted);margin-top:4px">یا انتخاب از طرح‌های پیش‌فرض سامانه:</div>
          <select name="preset_image" id="inputPresetImage" class="form-control" style="margin-top:4px" onchange="previewPreset(this.value)">
            <option value="">-- آپلود فایل جدید اختصاصی --</option>
            <option value="/uploads/stand/happy/1.jpg">طرح پیش‌فرض ۱ تبریک</option>
            <option value="/uploads/stand/happy/2.jpg">طرح پیش‌فرض ۲ تبریک</option>
            <option value="/uploads/stand/sad/1.jpg">طرح پیش‌فرض ۱ تسلیت</option>
            <option value="/uploads/stand/sad/2.jpg">طرح پیش‌فرض ۲ تسلیت</option>
            <option value="/uploads/stand/sad/3.jpg">طرح پیش‌فرض ۳ تسلیت</option>
          </select>

          <div class="preview-box">
            <img id="imagePreview" src="/uploads/stand/happy/1.jpg" alt="پیش‌نمایش تصویر">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:center;margin-top:10px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" for="inputSort">ترتیب نمایش</label>
            <input type="number" name="sort_order" id="inputSort" value="0" class="form-control">
          </div>

          <div class="form-group" style="margin-bottom:0">
            <label class="switch-label">
              <input type="checkbox" name="is_active" id="inputActive" value="1" checked style="width:18px;height:18px">
              نمایش فعال در سایت
            </label>
          </div>
        </div>
      </div>

      <div style="padding:16px 24px;border-top:1px solid var(--color-border);display:flex;justify-content:flex-end;gap:10px;background:var(--color-bg)">
        <button type="button" class="btn-action" onclick="closeModal()">انصراف</button>
        <button type="submit" class="btn-add">ذخیره و ثبت</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('modalTitle').textContent = 'افزودن طرح استند جدید';
  document.getElementById('formAction').value = 'create';
  document.getElementById('standId').value = '';
  document.getElementById('inputTitle').value = '';
  document.getElementById('inputType').value = 'congrats';
  document.getElementById('inputPrice').value = '1500000';
  document.getElementById('inputDesc').value = '';
  document.getElementById('inputSort').value = '0';
  document.getElementById('inputActive').checked = true;
  document.getElementById('inputImageFile').value = '';
  document.getElementById('inputPresetImage').value = '/uploads/stand/happy/1.jpg';
  document.getElementById('imagePreview').src = '/uploads/stand/happy/1.jpg';
  document.getElementById('standModal').classList.add('open');
}

function openEditModal(st) {
  document.getElementById('modalTitle').textContent = 'ویرایش طرح استند: ' + st.title;
  document.getElementById('formAction').value = 'update';
  document.getElementById('standId').value = st.id;
  document.getElementById('inputTitle').value = st.title || '';
  document.getElementById('inputType').value = st.stand_type || 'congrats';
  document.getElementById('inputPrice').value = st.unit_price || '0';
  document.getElementById('inputDesc').value = st.description || '';
  document.getElementById('inputSort').value = st.sort_order || '0';
  document.getElementById('inputActive').checked = parseInt(st.is_active) === 1;
  document.getElementById('inputImageFile').value = '';
  document.getElementById('inputPresetImage').value = '';
  document.getElementById('imagePreview').src = st.image || '/uploads/stand/happy/1.jpg';
  document.getElementById('standModal').classList.add('open');
}

function closeModal() {
  document.getElementById('standModal').classList.remove('open');
}

function previewUpload(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('imagePreview').src = e.target.result;
      document.getElementById('inputPresetImage').value = '';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function previewPreset(val) {
  if (val) {
    document.getElementById('imagePreview').src = val;
    document.getElementById('inputImageFile').value = '';
  }
}

// Close on backdrop click
document.getElementById('standModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>

</body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../core/shop-db.php';
require_once __DIR__ . '/../core/image-optimizer.php';

// Access check: Super Admin, Stands Manager, or Pages Manager
$isSuper = dash_is_super();
if (!$isSuper && !dash_can('stands') && !dash_can('pages')) {
    dash_require('pages');
}

// Self-healing schema migration & default catalog seeding
if ($pdo instanceof PDO) {
    shop_ensure_schema($pdo);
    shop_seed_default_catalog($pdo);
}

// Persian digits converter helper
if (!function_exists('fa_digits')) {
    function fa_digits($s): string {
        return strtr((string)$s, [
            '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹','.'=>'٫'
        ]);
    }
}

// Unique slug generator
function shop_generate_unique_slug(PDO $pdo, string $title, string $requestedSlug = '', int $excludeId = 0, string $table = 'products'): string {
    $validTable = in_array($table, ['products', 'product_categories'], true) ? $table : 'products';
    $baseSlug = trim($requestedSlug);
    if ($baseSlug === '') {
        $baseSlug = preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower($title));
        $baseSlug = trim($baseSlug, '-');
    } else {
        $baseSlug = preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower($baseSlug));
        $baseSlug = trim($baseSlug, '-');
    }
    if ($baseSlug === '') {
        $baseSlug = ($validTable === 'product_categories' ? 'cat-' : 'product-') . time();
    }

    $slug = $baseSlug;
    $counter = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM `{$validTable}` WHERE slug = ? AND id != ? LIMIT 1");
        $stmt->execute([$slug, $excludeId]);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $counter++;
        $slug = $baseSlug . '-' . $counter;
    }
}

// Auto-seed default categories if empty
$catCount = (int)$pdo->query("SELECT COUNT(*) FROM product_categories")->fetchColumn();
if ($catCount === 0) {
    $defaultCategories = [
        ['title' => 'پوشاک و منسوجات', 'slug' => 'apparel', 'sort_order' => 1],
        ['title' => 'صنایع دستی و یادبود', 'slug' => 'handicrafts', 'sort_order' => 2],
        ['title' => 'کتاب و نشریات', 'slug' => 'books', 'sort_order' => 3],
        ['title' => 'ماگ و لوازم پذیرایی', 'slug' => 'mugs-drinkware', 'sort_order' => 4],
        ['title' => 'لوازم تحریر و اکسسوری', 'slug' => 'stationery-accessories', 'sort_order' => 5],
    ];
    $catStmt = $pdo->prepare("INSERT INTO product_categories (title, slug, sort_order, status) VALUES (?, ?, ?, 'active')");
    foreach ($defaultCategories as $dc) {
        try {
            $catStmt->execute([$dc['title'], $dc['slug'], $dc['sort_order']]);
        } catch (Throwable $e) {}
    }
}
$categories = shop_get_categories($pdo);

// Ensure upload directory exists
$uploadDir = __DIR__ . '/../uploads/products';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$msg = '';
$msgType = 'success';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_product' || $action === 'create' || $action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $title = trim((string)($_POST['title'] ?? ''));
            $slugInput = trim((string)($_POST['slug'] ?? ''));
            $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $price = max(0.0, (float)($_POST['price'] ?? 0));
            $stock = max(0, (int)($_POST['stock'] ?? 0));
            $description = trim((string)($_POST['description'] ?? ''));
            $status = in_array($_POST['status'] ?? '', ['active', 'draft', 'out_of_stock'], true) ? $_POST['status'] : 'active';

            if ($title === '') {
                $msg = 'لطفاً عنوان محصول را وارد کنید.';
                $msgType = 'danger';
            } else {
                $imageUrl = null;
                if ($id > 0) {
                    $existing = shop_get_product_by_id($pdo, $id);
                    if ($existing) {
                        $imageUrl = $existing['image_url'];
                    }
                }

                // Handle Image Upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        try {
                            $imageUrl = maxa_upload_and_optimize($_FILES['image'], 'products', 'prod_', 82, 1920);
                        } catch (Throwable $e) {
                            $msg = 'خطا در بهینه‌سازی و آپلود تصویر: ' . $e->getMessage();
                            $msgType = 'danger';
                        }
                    } else {
                        $errCode = (int)$_FILES['image']['error'];
                        switch ($errCode) {
                            case UPLOAD_ERR_INI_SIZE:
                            case UPLOAD_ERR_FORM_SIZE:
                                $msg = 'حجم فایل تصویر بیش از حد مجاز سرور است.';
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                $msg = 'آپلود تصویر به صورت ناقص انجام شد، لطفاً مجدداً تلاش کنید.';
                                break;
                            default:
                                $msg = 'خطا در ارسال فایل تصویر (کد: ' . $errCode . ').';
                                break;
                        }
                        $msgType = 'danger';
                    }
                } elseif (!empty($_POST['image_url'])) {
                    $imageUrl = trim((string)$_POST['image_url']);
                }

                if ($msgType !== 'danger') {
                    $slug = shop_generate_unique_slug($pdo, $title, $slugInput, $id);

                    if ($id > 0) {
                        $st = $pdo->prepare("
                            UPDATE products 
                            SET category_id = ?, title = ?, slug = ?, description = ?, price = ?, stock = ?, image_url = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                            WHERE id = ?
                        ");
                        $st->execute([$categoryId, $title, $slug, $description, $price, $stock, $imageUrl, $status, $id]);
                        $msg = 'محصول با موفقیت ویرایش شد.';
                        $msgType = 'success';
                    } else {
                        $st = $pdo->prepare("
                            INSERT INTO products (category_id, title, slug, description, price, stock, image_url, status)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $st->execute([$categoryId, $title, $slug, $description, $price, $stock, $imageUrl, $status]);
                        $msg = 'محصول جدید با موفقیت به فروشگاه اضافه شد.';
                        $msgType = 'success';
                    }
                }
            }
        } elseif ($action === 'toggle_status') {
            $id = (int)($_POST['id'] ?? 0);
            $prod = shop_get_product_by_id($pdo, $id);
            if ($prod) {
                $newStatus = ($prod['status'] === 'active') ? 'out_of_stock' : 'active';
                $st = $pdo->prepare("UPDATE products SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $st->execute([$newStatus, $id]);
                $msg = 'وضعیت نمایش محصول با موفقیت تغییر کرد.';
                $msgType = 'success';
            } else {
                $msg = 'محصول مورد نظر یافت نشد.';
                $msgType = 'danger';
            }
        } elseif ($action === 'delete' || $action === 'delete_product') {
            $id = (int)($_POST['id'] ?? 0);
            $st = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $st->execute([$id]);
            $msg = 'محصول با موفقیت حذف شد.';
            $msgType = 'success';
        } elseif ($action === 'create_category') {
            $catTitle = trim((string)($_POST['category_title'] ?? ''));
            if ($catTitle === '') {
                $msg = 'لطفاً نام دسته‌بندی را وارد کنید.';
                $msgType = 'danger';
            } else {
                $catSlug = shop_generate_unique_slug($pdo, $catTitle, '', 0, 'product_categories');
                $st = $pdo->prepare("INSERT INTO product_categories (title, slug, status) VALUES (?, ?, 'active')");
                $st->execute([$catTitle, $catSlug]);
                $categories = shop_get_categories($pdo);
                $msg = 'دسته‌بندی جدید با موفقیت اضافه شد.';
                $msgType = 'success';
            }
        }
    } catch (Throwable $e) {
        $msg = 'خطای سیستم: ' . $e->getMessage();
        $msgType = 'danger';
    }
}

// Fetch Filter parameters
$searchQuery = trim((string)($_GET['q'] ?? ''));
$filterCategory = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$filterStatus = trim((string)($_GET['status'] ?? 'all'));

// Query Products
$where = [];
$params = [];

if ($searchQuery !== '') {
    $where[] = "(p.title LIKE ? OR p.description LIKE ? OR p.slug LIKE ?)";
    $term = "%{$searchQuery}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($filterCategory > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $filterCategory;
}

if (in_array($filterStatus, ['active', 'out_of_stock', 'draft'], true)) {
    $where[] = "p.status = ?";
    $params[] = $filterStatus;
}

$whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
$sql = "
    SELECT p.*, c.title AS category_title 
    FROM products p 
    LEFT JOIN product_categories c ON p.category_id = c.id 
    {$whereSql}
    ORDER BY p.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inventory Statistics
$statTotal = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$statActive = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
$statOutOfStock = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status = 'out_of_stock' OR stock <= 0")->fetchColumn();
$statTotalStock = (int)$pdo->query("SELECT COALESCE(SUM(stock), 0) FROM products")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مدیریت محصولات فروشگاه خیریه | پنل مکسا</title>
<script>
try {
  if (localStorage.getItem('maxa-theme') === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
  }
} catch(e) {}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#008f8a; --color-primary-dark:#00736f; --color-primary-light:#4fb2b0;
  --color-secondary:#faa61a; --color-text:#2f3437; --color-muted:#8e989d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b; --warning:#f59e0b;
  --success-12:rgba(22,163,122,.14); --danger-12:rgba(224,85,107,.12); --warning-12:rgba(245,158,11,.12);
  --primary-12:rgba(0,143,138,.12);
  --radius-sm:10px; --radius:16px; --radius-lg:22px;
  --shadow-sm:0 1px 3px rgba(16,40,40,.05),0 2px 6px rgba(16,40,40,.04);
  --shadow-md:0 6px 18px rgba(16,40,40,.07),0 2px 8px rgba(16,40,40,.04);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --success-12:rgba(22,163,122,.18); --danger-12:rgba(224,85,107,.16); --warning-12:rgba(245,158,11,.18);
  --primary-12:rgba(79,178,176,.16);
  --shadow-sm:0 1px 3px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 6px 18px rgba(0,0,0,.45),0 2px 8px rgba(0,0,0,.35);
  color-scheme:dark; background-color:var(--color-bg);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;padding:26px 20px;transition:background .3s,color .3s}

.wrap{max-width:1240px;margin:0 auto}

.top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px}
.nav-back{display:inline-flex;align-items:center;gap:6px;color:var(--color-muted);text-decoration:none;font-weight:600;transition:color .2s;font-size:13px}
.nav-back:hover{color:var(--color-primary)}
.nav-back svg{width:16px;height:16px}

.quick-links{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.btn-nav-link{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;background:var(--color-surface);border:1px solid var(--color-border);border-radius:12px;font-size:12.5px;font-weight:600;color:var(--color-text);text-decoration:none;transition:all .2s;box-shadow:var(--shadow-sm)}
.btn-nav-link:hover{border-color:var(--color-primary);color:var(--color-primary);transform:translateY(-1px)}
.btn-nav-link svg{width:15px;height:15px;color:var(--color-primary)}

.head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.head-left{display:flex;align-items:center;gap:16px}
.head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;background:linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);box-shadow:var(--shadow-sm)}
.head-ic svg{width:28px;height:28px}

.alert{padding:14px 18px;border-radius:12px;margin-bottom:22px;font-size:13.5px;font-weight:600;display:flex;align-items:center;gap:10px}
.alert-success{background:var(--success-12);color:var(--success);border:1px solid rgba(22,163,122,.3)}
.alert-danger{background:var(--danger-12);color:var(--danger);border:1px solid rgba(224,85,107,.3)}

/* Stats Cards */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:24px}
.stat-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:18px 20px;box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:16px;transition:transform .2s,box-shadow .2s}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.stat-icon{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;flex-shrink:0}
.stat-icon.total{background:var(--primary-12);color:var(--color-primary)}
.stat-icon.active{background:var(--success-12);color:var(--success)}
.stat-icon.out{background:var(--danger-12);color:var(--danger)}
.stat-icon.stock{background:var(--warning-12);color:var(--warning)}
.stat-icon svg{width:22px;height:22px}
.stat-val{font-size:22px;font-weight:900;line-height:1.2;color:var(--color-text)}
.stat-label{font-size:12px;color:var(--color-muted);font-weight:600;margin-top:2px}

/* Main Card */
.card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:24px}
.card-header{padding:20px 24px;border-bottom:1px solid var(--color-border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px}
.card-title{font-size:17px;font-weight:700;display:flex;align-items:center;gap:10px}

.btn{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:12px;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;text-decoration:none;border:none}
.btn-primary{background:var(--color-primary);color:#fff}
.btn-primary:hover{background:var(--color-primary-dark);transform:translateY(-1px);color:#fff}
.btn-outline{background:transparent;border:1px solid var(--color-border);color:var(--color-text)}
.btn-outline:hover{border-color:var(--color-primary);color:var(--color-primary)}

/* Filters Toolbar */
.toolbar{padding:16px 24px;background:rgba(0,0,0,.015);border-bottom:1px solid var(--color-border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px}
.filter-form{display:flex;align-items:center;gap:10px;flex-wrap:wrap;flex:1}
.input-search{padding:9px 14px;background:var(--color-surface);border:1px solid var(--color-border);border-radius:10px;font-family:inherit;font-size:13px;color:var(--color-text);min-width:220px;outline:none;transition:border-color .2s}
.input-search:focus{border-color:var(--color-primary)}
.select-filter{padding:9px 12px;background:var(--color-surface);border:1px solid var(--color-border);border-radius:10px;font-family:inherit;font-size:13px;color:var(--color-text);outline:none;cursor:pointer}
.select-filter:focus{border-color:var(--color-primary)}

/* Table Styles */
.table-wrap{width:100%;overflow-x:auto}
.products-table{width:100%;border-collapse:collapse;text-align:right}
.products-table th{background:rgba(0,0,0,.02);padding:14px 18px;font-size:12.5px;font-weight:700;color:var(--color-muted);border-bottom:1px solid var(--color-border);white-space:nowrap}
.products-table td{padding:14px 18px;border-bottom:1px solid var(--color-border);vertical-align:middle;font-size:13.5px}
.products-table tr:hover td{background:rgba(0,143,138,.02)}

.product-thumb{width:56px;height:56px;border-radius:12px;object-fit:cover;background:var(--color-bg);border:1px solid var(--color-border);display:block;flex-shrink:0}
.product-cell{display:flex;align-items:center;gap:14px}
.product-title-wrap{display:flex;flex-direction:column;gap:3px}
.product-name{font-weight:700;color:var(--color-text);font-size:14px;text-decoration:none}
.product-slug{font-size:11.5px;color:var(--color-muted);direction:ltr;text-align:right}

/* Badges */
.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:600;white-space:nowrap}
.badge-category{background:var(--primary-12);color:var(--color-primary)}
.badge-active{background:var(--success-12);color:var(--success)}
.badge-out{background:var(--danger-12);color:var(--danger)}
.badge-draft{background:rgba(142,152,157,.15);color:var(--color-muted)}

.stock-pill{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:700}
.stock-pill.in-stock{background:var(--success-12);color:var(--success)}
.stock-pill.low-stock{background:var(--warning-12);color:var(--warning)}
.stock-pill.zero-stock{background:var(--danger-12);color:var(--danger)}

.price-text{font-size:14.5px;font-weight:800;color:var(--color-text)}
.price-unit{font-size:11.5px;font-weight:500;color:var(--color-muted);margin-right:2px}

/* Action Buttons */
.action-buttons{display:flex;align-items:center;gap:6px}
.btn-action{padding:6px 10px;border-radius:8px;font-family:inherit;font-size:12px;font-weight:600;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:4px;border:1px solid var(--color-border);background:var(--color-surface);color:var(--color-text);text-decoration:none}
.btn-action:hover{border-color:var(--color-primary);color:var(--color-primary);background:var(--primary-12)}
.btn-action.toggle:hover{border-color:var(--warning);color:var(--warning);background:var(--warning-12)}
.btn-action.delete:hover{border-color:var(--danger);color:var(--danger);background:var(--danger-12)}
.btn-action svg{width:14px;height:14px}

/* Empty State */
.empty-state{text-align:center;padding:70px 20px;color:var(--color-muted)}
.empty-state svg{width:64px;height:64px;opacity:.25;margin-bottom:16px;stroke-width:1.5}

/* Modal Dialog */
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)}
.modal-backdrop.open{display:flex}
.modal{background:var(--color-surface);border-radius:var(--radius);max-width:640px;width:100%;border:1px solid var(--color-border);box-shadow:var(--shadow-md);overflow:hidden;animation:modalIn .22s var(--ease)}
@keyframes modalIn{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}

.modal-head{padding:18px 24px;border-bottom:1px solid var(--color-border);display:flex;align-items:center;justify-content:space-between}
.modal-title{font-size:16.5px;font-weight:700}
.modal-close{background:none;border:none;color:var(--color-muted);cursor:pointer;font-size:22px;line-height:1;padding:4px;border-radius:8px;transition:color .2s}
.modal-close:hover{color:var(--danger)}
.modal-body{padding:24px;max-height:82vh;overflow-y:auto}

.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-grid-full{grid-column:1 / -1}
.form-group{margin-bottom:14px}
.form-label{display:block;font-size:12.5px;font-weight:700;margin-bottom:6px;color:var(--color-text)}
.form-control{width:100%;background:var(--color-bg);border:1px solid var(--color-border);border-radius:10px;padding:10px 12px;font-family:inherit;font-size:13.5px;color:var(--color-text);outline:none;transition:border-color .2s}
.form-control:focus{border-color:var(--color-primary)}
.form-hint{font-size:11.5px;color:var(--color-muted);margin-top:4px}

.preview-box{width:100%;height:140px;border:2px dashed var(--color-border);border-radius:12px;display:grid;place-items:center;margin-top:8px;background:var(--color-bg);overflow:hidden;position:relative}
.preview-box img{max-height:100%;max-width:100%;object-fit:contain}
.preview-box.has-image{border-style:solid}

.modal-foot{padding:16px 24px;border-top:1px solid var(--color-border);display:flex;align-items:center;justify-content:flex-end;gap:10px;background:rgba(0,0,0,.015)}

@media(max-width:768px){
  .form-grid{grid-template-columns:1fr}
  .card-header{flex-direction:column;align-items:flex-start}
  .toolbar{flex-direction:column;align-items:stretch}
  .filter-form{flex-direction:column;align-items:stretch}
  .input-search{min-width:100%}
}
</style>
</head>
<body>

<div class="wrap">
  <!-- Top Bar -->
  <div class="top-bar">
    <a href="index.php" class="nav-back">
      <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      بازگشت به داشبورد
    </a>
    <div class="quick-links">
      <a href="orders.php" class="btn-nav-link">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        مدیریت سفارشات
      </a>
      <a href="/shop/index.php" target="_blank" class="btn-nav-link">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        مشاهده فروشگاه در سایت
      </a>
    </div>
  </div>

  <!-- Header -->
  <div class="head">
    <div class="head-left">
      <div class="head-ic">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 8.5 12 3 3 8.5 12 14l9-5.5Z"/><path d="M3 8.5v7L12 21l9-5.5v-7"/><line x1="12" y1="14" x2="12" y2="21"/></svg>
      </div>
      <div>
        <h1 style="font-size:23px;font-weight:900">مدیریت محصولات فروشگاه خیریه</h1>
        <p style="color:var(--color-muted);font-size:13px">تعریف کالاها، انبارداری و موجودی، قیمت‌گذاری و مدیریت دسته‌بندی‌های فروشگاه مهربانی مکسا</p>
      </div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <button type="button" class="btn btn-outline" id="btnOpenCategoryModal">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        دسته‌بندی جدید
      </button>
      <button type="button" class="btn btn-primary" id="btnOpenProductModal">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        افزودن محصول جدید
      </button>
    </div>
  </div>

  <!-- Flash Messages -->
  <?php if (!empty($msg)): ?>
    <div class="alert alert-<?= e($msgType) ?>">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:18px;height:18px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= e($msg) ?>
    </div>
  <?php endif; ?>

  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon total">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 8.5 12 3 3 8.5 12 14l9-5.5Z"/><path d="M3 8.5v7L12 21l9-5.5v-7"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= fa_digits($statTotal) ?></div>
        <div class="stat-label">تعداد کل محصولات</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon active">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= fa_digits($statActive) ?></div>
        <div class="stat-label">محصولات فعال و در دسترس</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon out">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= fa_digits($statOutOfStock) ?></div>
        <div class="stat-label">ناموجود یا اتمام موجودی</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stock">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= fa_digits(number_format($statTotalStock)) ?></div>
        <div class="stat-label">کل واحدهای موجود در انبار</div>
      </div>
    </div>
  </div>

  <!-- Products Main Table Card -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:20px;height:20px;color:var(--color-primary)"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        فهرست محصولات فروشگاه
        <span style="font-size:12px;color:var(--color-muted);font-weight:500">(<?= fa_digits(count($products)) ?> مورد یافت شد)</span>
      </div>
    </div>

    <!-- Toolbar Filters -->
    <div class="toolbar">
      <form method="GET" class="filter-form">
        <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="جست‌وجوی عنوان، اسلاگ، توضیحات..." class="input-search">
        <select name="category_id" class="select-filter">
          <option value="0">تمام دسته‌بندی‌ها</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= $filterCategory === (int)$cat['id'] ? 'selected' : '' ?>>
              <?= e($cat['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <select name="status" class="select-filter">
          <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>همه وضعیت‌ها</option>
          <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>فقط فعال</option>
          <option value="out_of_stock" <?= $filterStatus === 'out_of_stock' ? 'selected' : '' ?>>ناموجود</option>
          <option value="draft" <?= $filterStatus === 'draft' ? 'selected' : '' ?>>پیش‌نویس</option>
        </select>
        <button type="submit" class="btn btn-outline" style="padding:8px 14px">اعمال فیلتر</button>
        <?php if ($searchQuery !== '' || $filterCategory > 0 || $filterStatus !== 'all'): ?>
          <a href="products.php" class="btn btn-outline" style="padding:8px 12px;color:var(--danger);border-color:var(--danger-12)">حذف فیلترها</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Products Table -->
    <div class="table-wrap">
      <?php if (empty($products)): ?>
        <div class="empty-state">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 8.5 12 3 3 8.5 12 14l9-5.5Z"/><path d="M3 8.5v7L12 21l9-5.5v-7"/><line x1="12" y1="14" x2="12" y2="21"/></svg>
          <h3 style="font-size:16px;font-weight:700;margin-bottom:6px">هیچ محصولی یافت نشد</h3>
          <p style="font-size:13px">می‌توانید با دکمهٔ «افزودن محصول جدید» اولین کالای خیریه را ثبت کنید.</p>
        </div>
      <?php else: ?>
        <table class="products-table">
          <thead>
            <tr>
              <th style="width:50px">شناسه</th>
              <th>مشخصات محصول</th>
              <th>دسته‌بندی</th>
              <th>قیمت (تومان)</th>
              <th>موجودی انبار</th>
              <th>وضعیت</th>
              <th style="width:160px;text-align:center">عملیات</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <tr>
                <td style="color:var(--color-muted);font-weight:600">#<?= fa_digits($p['id']) ?></td>
                <td>
                  <div class="product-cell">
                    <?php if (!empty($p['image_url'])): ?>
                      <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['title']) ?>" class="product-thumb">
                    <?php else: ?>
                      <div class="product-thumb" style="display:grid;place-items:center;color:var(--color-muted)">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="width:24px;height:24px;opacity:0.4"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                      </div>
                    <?php endif; ?>
                    <div class="product-title-wrap">
                      <span class="product-name"><?= e($p['title']) ?></span>
                      <span class="product-slug">/shop/<?= e($p['slug']) ?></span>
                    </div>
                  </div>
                </td>
                <td>
                  <?php if (!empty($p['category_title'])): ?>
                    <span class="badge badge-category"><?= e($p['category_title']) ?></span>
                  <?php else: ?>
                    <span style="color:var(--color-muted);font-size:12px">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="price-text"><?= fa_digits(number_format((float)$p['price'])) ?></span>
                  <span class="price-unit">تومان</span>
                </td>
                <td>
                  <?php
                    $stk = (int)$p['stock'];
                    $stkClass = $stk > 5 ? 'in-stock' : ($stk > 0 ? 'low-stock' : 'zero-stock');
                    $stkLabel = $stk > 0 ? (fa_digits($stk) . ' عدد') : 'اتمام موجودی';
                  ?>
                  <span class="stock-pill <?= $stkClass ?>"><?= $stkLabel ?></span>
                </td>
                <td>
                  <?php if ($p['status'] === 'active'): ?>
                    <span class="badge badge-active">● فعال</span>
                  <?php elseif ($p['status'] === 'out_of_stock'): ?>
                    <span class="badge badge-out">● ناموجود</span>
                  <?php else: ?>
                    <span class="badge badge-draft">● پیش‌نویس</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="action-buttons" style="justify-content:center">
                    <button type="button" class="btn-action btn-edit-product"
                      data-id="<?= (int)$p['id'] ?>"
                      data-title="<?= e($p['title']) ?>"
                      data-slug="<?= e($p['slug']) ?>"
                      data-category-id="<?= (int)($p['category_id'] ?? 0) ?>"
                      data-price="<?= (float)$p['price'] ?>"
                      data-stock="<?= (int)$p['stock'] ?>"
                      data-status="<?= e($p['status']) ?>"
                      data-description="<?= e($p['description'] ?? '') ?>"
                      data-image="<?= e($p['image_url'] ?? '') ?>"
                      title="ویرایش">
                      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      ویرایش
                    </button>

                    <form method="POST" style="display:inline" onsubmit="return confirm('آیا از تغییر وضعیت این محصول مطمئن هستید؟')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="toggle_status">
                      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                      <button type="submit" class="btn-action toggle" title="تغییر وضعیت">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                      </button>
                    </form>

                    <form method="POST" style="display:inline" onsubmit="return confirm('آیا از حذف این محصول اطمینان دارید؟ این عمل غیرقابل بازگشت است.')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete_product">
                      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                      <button type="submit" class="btn-action delete" title="حذف محصول">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal: Create / Edit Product -->
<div class="modal-backdrop" id="productModalBackdrop">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="productModalTitle">
    <div class="modal-head">
      <h3 class="modal-title" id="productModalTitle">افزودن محصول جدید</h3>
      <button type="button" class="modal-close" data-close-modal aria-label="بستن">✕</button>
    </div>
    <form method="POST" enctype="multipart/form-data" id="productForm">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_product">
      <input type="hidden" name="id" id="prodId" value="0">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group form-grid-full">
            <label class="form-label" for="prodTitle">عنوان محصول <span style="color:var(--danger)">*</span></label>
            <input type="text" name="title" id="prodTitle" class="form-control" required placeholder="مثال: ماگ سرامیکی یادبود مکسا">
          </div>

          <div class="form-group">
            <label class="form-label" for="prodSlug">نامک انگلیسی / اسلاگ</label>
            <input type="text" name="slug" id="prodSlug" class="form-control" placeholder="اختیاری (خودکار تولید می‌شود)">
            <div class="form-hint">در صورت خالی بودن، به صورت خودکار از روی عنوان تولید می‌شود.</div>
          </div>

          <div class="form-group">
            <label class="form-label" for="prodCategory">دسته‌بندی</label>
            <select name="category_id" id="prodCategory" class="form-control">
              <option value="">بدون دسته‌بندی</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"><?= e($cat['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="prodPrice">قیمت (تومان) <span style="color:var(--danger)">*</span></label>
            <input type="number" name="price" id="prodPrice" class="form-control" min="0" step="1000" required placeholder="مثال: 150000">
          </div>

          <div class="form-group">
            <label class="form-label" for="prodStock">موجودی انبار <span style="color:var(--danger)">*</span></label>
            <input type="number" name="stock" id="prodStock" class="form-control" min="0" step="1" required placeholder="مثال: 20">
          </div>

          <div class="form-group form-grid-full">
            <label class="form-label" for="prodStatus">وضعیت انتشار</label>
            <select name="status" id="prodStatus" class="form-control">
              <option value="active">فعال (قابل خرید در فروشگاه)</option>
              <option value="out_of_stock">اتمام موجودی (نمایش بدون امکان خرید)</option>
              <option value="draft">پیش‌نویس (مخفی در فروشگاه)</option>
            </select>
          </div>

          <div class="form-group form-grid-full">
            <label class="form-label" for="prodImage">تصویر محصول (JPG / PNG / WebP)</label>
            <input type="file" name="image" id="prodImage" class="form-control" accept="image/jpeg,image/png,image/webp">
            <input type="hidden" name="image_url" id="prodImageUrl" value="">
            <div class="preview-box" id="imagePreviewBox">
              <span id="previewPlaceholder" style="color:var(--color-muted);font-size:12px">پیش‌نمایش تصویر انتخابی</span>
              <img id="previewImg" src="" alt="" style="display:none">
            </div>
          </div>

          <div class="form-group form-grid-full">
            <label class="form-label" for="prodDescription">توضیحات و مشخصات محصول</label>
            <textarea name="description" id="prodDescription" class="form-control" rows="4" placeholder="توضیحات، ابعاد، جنس، ارزش معنوی و هدف خیریه این محصول..."></textarea>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal>انصراف</button>
        <button type="submit" class="btn btn-primary" id="btnSaveProduct">ذخیره محصول</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Create Category -->
<div class="modal-backdrop" id="categoryModalBackdrop">
  <div class="modal" role="dialog" aria-modal="true" style="max-width:440px">
    <div class="modal-head">
      <h3 class="modal-title">افزودن دسته‌بندی جدید</h3>
      <button type="button" class="modal-close" data-close-modal aria-label="بستن">✕</button>
    </div>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create_category">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="catTitle">نام دسته‌بندی <span style="color:var(--danger)">*</span></label>
          <input type="text" name="category_title" id="catTitle" class="form-control" required placeholder="مثال: قاب و تابلوهای یادبود">
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal>انصراف</button>
        <button type="submit" class="btn btn-primary">ثبت دسته‌بندی</button>
      </div>
    </form>
  </div>
</div>

<script>
(function() {
  const productModal = document.getElementById('productModalBackdrop');
  const categoryModal = document.getElementById('categoryModalBackdrop');
  const btnOpenProduct = document.getElementById('btnOpenProductModal');
  const btnOpenCategory = document.getElementById('btnOpenCategoryModal');
  const productForm = document.getElementById('productForm');
  const modalTitle = document.getElementById('productModalTitle');
  const prodId = document.getElementById('prodId');
  const prodTitle = document.getElementById('prodTitle');
  const prodSlug = document.getElementById('prodSlug');
  const prodCategory = document.getElementById('prodCategory');
  const prodPrice = document.getElementById('prodPrice');
  const prodStock = document.getElementById('prodStock');
  const prodStatus = document.getElementById('prodStatus');
  const prodDesc = document.getElementById('prodDescription');
  const prodImage = document.getElementById('prodImage');
  const prodImageUrl = document.getElementById('prodImageUrl');
  const previewBox = document.getElementById('imagePreviewBox');
  const previewImg = document.getElementById('previewImg');
  const previewPlaceholder = document.getElementById('previewPlaceholder');

  function openModal(modal) {
    if (modal) {
      modal.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeModal(modal) {
    if (modal) {
      modal.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  // Close modals on clicking backdrop or close buttons
  document.querySelectorAll('[data-close-modal]').forEach(function(btn) {
    btn.addEventListener('click', function() {
      closeModal(productModal);
      closeModal(categoryModal);
    });
  });

  [productModal, categoryModal].forEach(function(modal) {
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeModal(modal);
        }
      });
    }
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeModal(productModal);
      closeModal(categoryModal);
    }
  });

  // Open Add Product Modal
  if (btnOpenProduct) {
    btnOpenProduct.addEventListener('click', function() {
      modalTitle.textContent = 'افزودن محصول جدید';
      productForm.reset();
      prodId.value = '0';
      prodImageUrl.value = '';
      previewImg.src = '';
      previewImg.style.display = 'none';
      previewPlaceholder.style.display = 'block';
      previewBox.classList.remove('has-image');
      openModal(productModal);
      prodTitle.focus();
    });
  }

  // Open Add Category Modal
  if (btnOpenCategory) {
    btnOpenCategory.addEventListener('click', function() {
      openModal(categoryModal);
    });
  }

  // Image preview on file selection
  if (prodImage) {
    prodImage.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
          previewImg.src = evt.target.result;
          previewImg.style.display = 'block';
          previewPlaceholder.style.display = 'none';
          previewBox.classList.add('has-image');
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Edit Product Buttons
  document.querySelectorAll('.btn-edit-product').forEach(function(btn) {
    btn.addEventListener('click', function() {
      productForm.reset();
      modalTitle.textContent = 'ویرایش محصول: ' + btn.dataset.title;
      prodId.value = btn.dataset.id;
      prodTitle.value = btn.dataset.title || '';
      prodSlug.value = btn.dataset.slug || '';
      prodCategory.value = btn.dataset.categoryId || '';
      prodPrice.value = btn.dataset.price || '0';
      prodStock.value = btn.dataset.stock || '0';
      prodStatus.value = btn.dataset.status || 'active';
      prodDesc.value = btn.dataset.description || '';
      prodImageUrl.value = btn.dataset.image || '';

      if (btn.dataset.image) {
        previewImg.src = btn.dataset.image;
        previewImg.style.display = 'block';
        previewPlaceholder.style.display = 'none';
        previewBox.classList.add('has-image');
      } else {
        previewImg.src = '';
        previewImg.style.display = 'none';
        previewPlaceholder.style.display = 'block';
        previewBox.classList.remove('has-image');
      }

      openModal(productModal);
      prodTitle.focus();
    });
  });
})();
</script>
</body>
</html>

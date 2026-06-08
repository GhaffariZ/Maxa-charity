<?php
require_once __DIR__ . "/../../config/database.php";

$pages = [];
$errorMsg = null;

try {
    $stmt = $pdo->query("
        SELECT id, title, slug, status, created_at
        FROM pages
        ORDER BY id DESC
    ");
    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مدیریت صفحات | پنل مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b; --violet:#7c4ddb;
  --primary-08:rgba(0,123,122,.08); --primary-12:rgba(0,123,122,.12);
  --radius-sm:12px; --radius:18px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --shadow-lg:0 20px 44px -14px rgba(0,102,101,.20),0 8px 20px -10px rgba(16,40,40,.12);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --primary-08:rgba(79,178,176,.10); --primary-12:rgba(79,178,176,.16);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  --shadow-lg:0 24px 48px -16px rgba(0,0,0,.62),0 10px 24px -12px rgba(0,0,0,.5);
  color-scheme:dark; background-color:var(--color-bg);
}
*{box-sizing:border-box}
body{
    font-family:'Vazirmatn',sans-serif;
    background:var(--color-bg);
    color:var(--color-text);
    margin:0;
    font-size:14px;
    line-height:1.7;
    -webkit-font-smoothing:antialiased;
    transition:background .3s,color .3s;
}
*::-webkit-scrollbar{width:9px;height:9px}
*::-webkit-scrollbar-thumb{background:rgba(0,123,122,.20);border-radius:99px;border:2px solid transparent;background-clip:padding-box}
[data-theme="dark"] *::-webkit-scrollbar-thumb{background:rgba(255,255,255,.16);background-clip:padding-box}
.container{
    max-width:1100px;
    margin:0 auto;
    padding:28px 22px;
}
.card{
    background:var(--color-surface);
    border:1px solid var(--color-border);
    padding:24px;
    border-radius:var(--radius);
    box-shadow:var(--shadow-sm);
}
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}
.topbar h2{font-size:19px;font-weight:800;letter-spacing:-.01em}
.new-btn{
    display:inline-flex;align-items:center;gap:6px;
    background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));
    color:#fff;
    text-decoration:none;
    padding:11px 18px;
    border-radius:13px;
    font-size:13.5px;
    font-weight:800;
    box-shadow:0 10px 22px -10px rgba(0,123,122,.7);
    transition:transform .15s var(--ease),box-shadow .25s;
}
.new-btn:hover{transform:translateY(-2px);box-shadow:0 16px 30px -10px rgba(0,123,122,.8)}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    font-size:13px;
}
th,td{
    padding:13px 14px;
    border-bottom:1px solid var(--color-border);
    text-align:center;
}
th{
    background:transparent;
    font-size:11.5px;
    font-weight:700;
    color:var(--color-muted);
    white-space:nowrap;
}
tr:hover td{background:var(--color-bg)}
.badge{
    padding:6px 14px;
    border-radius:99px;
    font-size:11.5px;
    font-weight:700;
    display:inline-block;
}
.draft{ background:rgba(244,166,30,.16); color:#b9760a; }
.published{ background:rgba(22,163,122,.14); color:var(--success); }
.suspended{ background:rgba(224,85,107,.14); color:var(--danger); }

.status-select{
    font-family:inherit;
    padding:8px 12px;
    border-radius:10px;
    border:1.5px solid var(--color-border);
    background:var(--color-bg);
    color:var(--color-text);
    font-size:12.5px;
    font-weight:600;
    cursor:pointer;
    transition:border-color .2s,box-shadow .2s;
}
.status-select:focus{outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08)}
.status-select:disabled{opacity:.6;cursor:not-allowed}

.actions a{
    margin:0 6px;
    text-decoration:none;
    font-weight:700;
    font-size:12.5px;
    transition:opacity .2s;
}
.actions a:hover{opacity:.65}

.view{ color:var(--color-primary); }
.edit{ color:var(--violet); }
.delete{ color:var(--danger); }

td button{
    font-family:inherit;font-weight:700;font-size:12.5px;
    color:var(--color-primary-dark);background:var(--primary-08);
    border:none;padding:8px 14px;border-radius:10px;cursor:pointer;
    transition:filter .2s,transform .14s;
}
td button:hover{filter:brightness(.96)}
td button:active{transform:scale(.95)}

.empty{
    text-align:center;
    padding:40px;
    color:var(--color-muted);
    font-weight:600;
}
.error{
    background:rgba(224,85,107,.12);
    border:1px solid rgba(224,85,107,.25);
    padding:14px 16px;
    border-radius:12px;
    text-align:center;
    color:var(--danger);
    margin-top:20px;
    font-weight:600;
}
.deleted-row {
    background:transparent !important;
    opacity:0.6;
}
.deleted-row td {
    color:var(--danger);
}
.deleted {
    background:var(--danger);
    color:#fff;
}

/* مودال سابقه (بازنویسی استایل‌های inline) */
#historyModal{background:rgba(16,40,40,.5)!important;backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);z-index:1000}
#historyModal > div{
    background:var(--color-surface)!important;
    color:var(--color-text);
    border:1px solid var(--color-border);
    border-radius:18px!important;
    box-shadow:var(--shadow-lg);
    padding:24px!important;
    width:520px!important;
    max-width:92vw;
}
#historyModal h3{font-size:16px;font-weight:800;margin:0 0 14px}
#historyContent{font-size:13px;line-height:1.9;color:var(--color-text)}
#historyModal button{
    font-family:inherit;font-weight:700;font-size:13px;color:#fff;
    background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));
    border:none;padding:10px 20px;border-radius:11px;cursor:pointer;margin-top:16px;
}

@media (max-width:768px){
  .container{padding:18px 12px}
  .card{padding:16px}
  table{display:block;overflow-x:auto;white-space:nowrap}
  th,td{padding:10px 10px}
}

</style>
</head>
<body>

<div class="container">
  <div class="card">
    <div class="topbar">
      <h2 style="margin:0;">📄 لیست صفحات</h2>
      <a class="new-btn" href="template-create.php">+ ساخت صفحه جدید</a>
    </div>

    <?php if ($errorMsg): ?>
      <div class="error">خطا در خواندن دیتابیس: <?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></div>

    <?php elseif (empty($pages)): ?>
      <div class="empty">هیچ صفحه‌ای ساخته نشده است.</div>

    <?php else: ?>
      <table>
        <tr>
          <th>#</th>
          <th>عنوان</th>
          <th>Slug</th>
          <th>لینک صفحه</th>
          <th>وضعیت</th>
          <th>تاریخ</th>
          <th>عملیات</th>
          <th>سابقه</th>

        </tr>

<?php foreach ($pages as $i => $p): ?>
<?php
    $rowClass = '';
    if ($p['status'] === 'deleted') {
        $rowClass = 'deleted-row';
    }
?>
<tr class="<?= $rowClass ?>">

          <td><?= $i + 1 ?></td>

          <td><?= htmlspecialchars($p["title"], ENT_QUOTES, "UTF-8") ?></td>

          <td><?= htmlspecialchars($p["slug"], ENT_QUOTES, "UTF-8") ?></td>

          <td>
<a href="/<?= urlencode($p['slug']) ?>" target="_blank" class="view">
                مشاهده
            </a>
          </td>

<td>
    <select class="status-select"
            onchange="changeStatus(<?= $p['id'] ?>, this.value)"
            <?= $p['status'] === 'deleted' ? 'disabled' : '' ?>>
        <option value="draft" <?= $p['status']=='draft'?'selected':'' ?>>پیش‌نویس</option>
        <option value="published" <?= $p['status']=='published'?'selected':'' ?>>منتشر شده</option>
        <option value="suspended" <?= $p['status']=='suspended'?'selected':'' ?>>در حال تعمیر</option>
        <?php if ($p['status'] === 'deleted'): ?>
            <option value="deleted" selected>حذف شده</option>
        <?php endif; ?>
    </select>
</td>


          <td><?= htmlspecialchars($p["created_at"], ENT_QUOTES, "UTF-8") ?></td>

<td class="actions">
    <?php if ($p['status'] === 'deleted'): ?>
        <span style="color:#c62828;font-weight:bold;">حذف شده</span>
    <?php else: ?>
        <a class="edit" href="template-create.php?id=<?= (int)$p["id"] ?>">ویرایش</a>
        <a class="delete" href="page-delete.php?id=<?= (int)$p["id"] ?>" onclick="return confirm('آیا از حذف مطمئن هستید؟')">حذف</a>
    <?php endif; ?>
</td>


          <td>
            <button onclick="showHistory(<?= $p['id'] ?>)">مشاهده</button>
          </td>

        </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>
<div id="historyModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);align-items:center;justify-content:center;">
<div style="background:white;padding:20px;border-radius:10px;width:500px;max-height:400px;overflow:auto;">
<h3>سابقه اقدامات</h3>
<div id="historyContent">در حال بارگذاری...</div>
<button onclick="closeHistory()">بستن</button>
</div>
</div>

<script>
function changeStatus(id, status) {
    fetch("page-status-update.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: id, status: status })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status !== "success") {
            alert("خطا در تغییر وضعیت");
        }
    })
    .catch(err => {
        alert("مشکل در ارتباط با سرور");
    });
}

function showHistory(id){

fetch("page-history.php?id="+id)
.then(res=>res.text())
.then(html=>{
document.getElementById("historyContent").innerHTML=html;
document.getElementById("historyModal").style.display="flex";
});

}

function closeHistory(){
document.getElementById("historyModal").style.display="none";
}

/* تم (دارک/لایت) از «داشبورد مدیریت» کنترل می‌شود — کلید مشترک: maxa-theme */
window.addEventListener('storage', function(e){
  if(e && e.key && e.key!=='maxa-theme') return;
  if(localStorage.getItem('maxa-theme')==='dark') document.documentElement.setAttribute('data-theme','dark');
  else document.documentElement.removeAttribute('data-theme');
});
</script>

</body>
</html>
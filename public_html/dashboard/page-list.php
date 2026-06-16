<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
require_once __DIR__ . "/../../config/database.php";

$pages = [];
$errorMsg = null;

try {
    // ایزولاسیون چندمستأجری: فقط صفحات شعبه‌ی فعال
    $__branch = dash_active_branch_id();
    $stmt = $pdo->prepare("
        SELECT id, title, slug, status, created_at
        FROM pages
        WHERE branch_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$__branch]);
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
<link rel="stylesheet" href="assets/css/panel.css">

<style>
/* استایل‌های ویژه‌ی این صفحه (توکن‌ها و کامپوننت‌های مشترک از panel.css می‌آیند) */
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

/* محتوای مودال سابقه (پوسته‌ی مودال از panel.css می‌آید) */
#historyContent{font-size:13px;line-height:1.9;color:var(--color-text)}
.hist-list{display:flex;flex-direction:column;gap:8px}
.hist-item{display:flex;align-items:center;justify-content:space-between;gap:14px;
  padding:11px 13px;border:1px solid var(--color-border);border-radius:12px;background:var(--color-bg)}
.hist-main{display:flex;align-items:center;gap:10px;min-width:0;flex-wrap:wrap}
.hist-action{font-weight:800;font-size:13px;color:var(--color-text)}
.hist-user{font-size:12px;color:var(--color-muted);background:var(--primary-08);padding:3px 9px;border-radius:99px;font-weight:700}
.hist-date{font-size:11.5px;color:var(--color-muted);direction:ltr;white-space:nowrap;flex-shrink:0}
.hist-empty{text-align:center;padding:24px;color:var(--color-muted);font-weight:600}

@media (max-width:640px){
  /* روی موبایل سلولِ «عملیات» نباید زیرِ هم بشکند */
  table.tbl td.actions{flex-wrap:wrap;justify-content:flex-end}
  table.tbl td.actions a{margin:0 0 0 10px}
}

</style>
</head>
<body>

<div class="container">

  <div class="page-head">
    <div class="ph-ic">
      <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8.5 12 3 3 8.5 12 14l9-5.5Z"/><path d="M3 8.5v7L12 21l9-5.5v-7"/><line x1="12" y1="14" x2="12" y2="21"/></svg>
    </div>
    <div class="ph-tx">
      <h1>مدیریت صفحات</h1>
      <p>صفحه‌های ساخته‌شده‌ی این شعبه را مشاهده، ویرایش و منتشر کنید.</p>
    </div>
    <div class="ph-actions">
      <a class="btn btn-primary" href="template-create.php">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        ساخت صفحه جدید
      </a>
    </div>
  </div>

  <div class="card">

    <?php if ($errorMsg): ?>
      <div class="error">خطا در خواندن دیتابیس: <?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></div>

    <?php elseif (empty($pages)): ?>
      <div class="empty">هیچ صفحه‌ای ساخته نشده است.</div>

    <?php else: ?>
      <table class="tbl">
        <thead>
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
        </thead>
        <tbody>

<?php foreach ($pages as $i => $p): ?>
<?php
    $rowClass = '';
    if ($p['status'] === 'deleted') {
        $rowClass = 'deleted-row';
    }
?>
<tr class="<?= $rowClass ?>">

          <td data-label="#"><?= $i + 1 ?></td>

          <td data-label="عنوان"><?= htmlspecialchars($p["title"], ENT_QUOTES, "UTF-8") ?></td>

          <td data-label="Slug" style="direction:ltr;text-align:left"><?= htmlspecialchars($p["slug"], ENT_QUOTES, "UTF-8") ?></td>

          <td data-label="لینک صفحه">
            <a href="/<?= urlencode($p['slug']) ?>" target="_blank" class="view">مشاهده</a>
          </td>

<td data-label="وضعیت">
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

          <td data-label="تاریخ" style="white-space:nowrap"><?= htmlspecialchars($p["created_at"], ENT_QUOTES, "UTF-8") ?></td>

<td class="actions" data-label="عملیات">
    <?php if ($p['status'] === 'deleted'): ?>
        <span class="badge off">حذف شده</span>
    <?php else: ?>
        <a class="edit" href="template-create.php?id=<?= (int)$p["id"] ?>">ویرایش</a>
        <a class="delete" href="page-delete.php?id=<?= (int)$p["id"] ?>" onclick="return confirm('آیا از حذف مطمئن هستید؟')">حذف</a>
    <?php endif; ?>
</td>

          <td data-label="سابقه">
            <button onclick="showHistory(<?= $p['id'] ?>)">مشاهده</button>
          </td>

        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<div id="historyModal" class="modal" role="dialog" aria-modal="true">
  <div class="modal-content" style="max-width:min(520px,94vw)">
    <h3>سابقه اقدامات</h3>
    <div id="historyContent">در حال بارگذاری...</div>
    <div style="margin-top:16px;display:flex;justify-content:flex-end">
      <button class="btn btn-primary" onclick="closeHistory()">بستن</button>
    </div>
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
  document.getElementById("historyContent").innerHTML = "در حال بارگذاری...";
  document.getElementById("historyModal").classList.add("show");
  fetch("page-history.php?id="+id)
    .then(res=>res.text())
    .then(html=>{ document.getElementById("historyContent").innerHTML = html; });
}

function closeHistory(){
  document.getElementById("historyModal").classList.remove("show");
}

/* بستن مودال با کلیک روی پس‌زمینه یا کلید Escape */
document.getElementById("historyModal").addEventListener("click", function(e){
  if(e.target === this) closeHistory();
});
document.addEventListener("keydown", function(e){
  if(e.key === "Escape") closeHistory();
});

/* تم (دارک/لایت) از «داشبورد مدیریت» کنترل می‌شود — کلید مشترک: maxa-theme */
window.addEventListener('storage', function(e){
  if(e && e.key && e.key!=='maxa-theme') return;
  if(localStorage.getItem('maxa-theme')==='dark') document.documentElement.setAttribute('data-theme','dark');
  else document.documentElement.removeAttribute('data-theme');
});
</script>

</body>
</html>
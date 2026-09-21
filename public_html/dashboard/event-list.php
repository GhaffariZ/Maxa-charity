<?php
declare(strict_types=1);
require_once __DIR__ . '/_guard.php';
dash_require('events'); dash_require_hq();
require_once __DIR__ . '/../event-lib.php';
$pdo = dash_pdo();
$events = $pdo->query("SELECT e.*, (SELECT COUNT(*) FROM event_news n WHERE n.event_id=e.id) news_count, (SELECT COUNT(*) FROM event_speakers s WHERE s.event_id=e.id) speaker_count FROM events e ORDER BY e.event_date DESC, e.id DESC")->fetchAll();
$PANEL_TITLE = 'مدیریت رویدادها'; require __DIR__ . '/_panel_head.php';
?>
<style>
.event-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap}.event-toolbar h1{margin:0}.event-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.event-card{padding:0;overflow:hidden;position:relative}.event-card-cover{height:145px;background:linear-gradient(135deg,#0a6668,#003f4a);position:relative;overflow:hidden}.event-card-cover img{width:100%;height:100%;object-fit:cover;opacity:.8}.event-card-cover:after{content:'';position:absolute;inset:0;background:linear-gradient(0deg,rgba(0,35,40,.8),transparent 75%)}.event-card-cover .status{position:absolute;z-index:1;top:14px;right:14px}.event-card-body{padding:18px 20px}.event-card h2{font-size:17px;margin:0 0 7px;line-height:1.7}.event-meta{color:var(--color-muted);font-size:12px;display:flex;gap:14px;flex-wrap:wrap}.event-actions{display:flex;gap:8px;margin-top:16px;flex-wrap:wrap}.empty-state{padding:50px 20px;text-align:center;color:var(--color-muted)}.empty-state strong{display:block;color:var(--color-text);font-size:18px;margin-bottom:6px}@media(max-width:720px){.event-grid{grid-template-columns:1fr}}
</style>
<div class="event-toolbar"><div><h1>رویدادها</h1><p class="sub">رویدادهای عمومی و محتوای اختصاصی آن‌ها در یک نگاه</p></div><a class="btn btn-primary" href="event-create.php">＋ ساخت رویداد جدید</a></div>
<?php if (!$events): ?>
  <div class="card empty-state"><strong>هنوز رویدادی ساخته نشده است</strong><span>اولین رویداد را برای همایش ششم مراقبت‌های حمایتی و تسکینی بسازید.</span></div>
<?php else: ?><div class="event-grid">
<?php foreach ($events as $event): $status = ['draft'=>'پیش‌نویس','published'=>'منتشرشده','archived'=>'آرشیو'][$event['status']] ?? $event['status']; ?>
  <article class="card event-card">
    <div class="event-card-cover"> <?php if ($event['poster']): ?><img src="<?= event_h($event['poster']) ?>" alt=""><?php endif; ?><span class="badge <?= $event['status']==='published'?'ok':'off' ?> status"><?= $status ?></span></div>
    <div class="event-card-body"><h2><?= event_h($event['title']) ?></h2><div class="event-meta"><span>◷ <?= event_h(event_date_label($event['event_date'])) ?></span><span>خبر: <?= (int)$event['news_count'] ?></span><span>استاد: <?= (int)$event['speaker_count'] ?></span><?php if ((int)$event['banner_active']): ?><span class="badge hq">بنر فعال</span><?php endif; ?></div><div class="event-actions"><a class="tbtn" href="event-create.php?id=<?= (int)$event['id'] ?>">ویرایش</a><a class="tbtn" target="_blank" href="/event.php?slug=<?= rawurlencode($event['slug']) ?>">مشاهده صفحه</a><a class="tbtn danger" href="event-delete.php?id=<?= (int)$event['id'] ?>" onclick="return confirm('این رویداد و محتوای وابسته حذف شود؟')">حذف</a></div></div>
  </article>
<?php endforeach; ?></div><?php endif; ?>
</div></body></html>

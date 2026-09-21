<?php
declare(strict_types=1);

require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/event-lib.php';
require_once __DIR__ . '/core/html-sanitizer.php';

$st = $pdo->prepare("SELECT n.*, e.title AS event_title, e.slug AS event_slug FROM event_news n JOIN events e ON e.id = n.event_id WHERE n.slug = ? AND n.status = 'published' LIMIT 1");
$st->execute([trim((string)($_GET['slug'] ?? ''))]);
$news = $st->fetch();

if (!$news) {
    http_response_code(404);
    $pageTitle = 'خبر یافت نشد';
    require __DIR__ . '/dashboard/components/header/component.php';
    echo '<div style="direction:rtl;text-align:center;padding:100px 20px;font-family:Vazirmatn,sans-serif;"><h2>خبر مورد نظر یافت نشد.</h2><p><a href="/events.php" style="color:#007b7a;">مشاهده رویدادها</a></p></div>';
    require __DIR__ . '/dashboard/components/footer/component.php';
    exit;
}

$pageTitle = $news['title'] . ' · ' . $news['event_title'];
require __DIR__ . '/dashboard/components/header/component.php';

$relatedStmt = $pdo->prepare("SELECT id, title, slug, published_at, created_at, image FROM event_news WHERE event_id = ? AND status = 'published' AND id <> ? ORDER BY published_at DESC, id DESC LIMIT 5");
$relatedStmt->execute([(int)$news['event_id'], (int)$news['id']]);
$related = $relatedStmt->fetchAll();
?>
<style>
.event-news-page {
  direction: rtl;
  background: #f4f8f7;
  padding: 56px 20px 88px;
  min-height: 75vh;
  font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.event-news-layout {
  max-width: 1140px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 300px;
  gap: 32px;
  align-items: start;
}

.event-news-wrap {
  min-width: 0;
}

.event-news-breadcrumbs {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #64748b;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.event-news-breadcrumbs a {
  color: #007b7a;
  text-decoration: none;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.event-news-breadcrumbs a:hover {
  text-decoration: underline;
}

.event-news-card {
  background: #ffffff;
  border: 1px solid #e2eceb;
  border-radius: 20px;
  box-shadow: 0 10px 32px rgba(18, 58, 61, 0.06);
  padding: 40px;
  overflow: hidden;
}

.event-news-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #e6f6f5;
  color: #007b7a;
  padding: 5px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 800;
  margin-bottom: 16px;
}

.event-news-card h1 {
  font-size: clamp(24px, 3.5vw, 36px);
  font-weight: 900;
  line-height: 1.45;
  color: #123a3d;
  margin: 0 0 18px;
}

.event-news-meta-row {
  display: flex;
  align-items: center;
  gap: 16px;
  font-size: 13px;
  color: #788c8f;
  margin-bottom: 28px;
  padding-bottom: 18px;
  border-bottom: 1px solid #f1f5f9;
}

.event-news-meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.event-news-figure {
  width: 100%;
  max-height: 440px;
  border-radius: 14px;
  overflow: hidden;
  margin-bottom: 32px;
  background: #e2eceb;
}

.event-news-figure img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.event-news-body {
  font-size: 16px;
  line-height: 2.3;
  color: #334155;
  overflow-wrap: anywhere;
}

.event-news-body p {
  margin: 0 0 20px;
}

.event-news-body a {
  color: #007b7a;
  text-decoration: underline;
  text-underline-offset: 3px;
}

.event-news-sidebar {
  position: sticky;
  top: 32px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.event-news-sidebox {
  background: #ffffff;
  border: 1px solid #e2eceb;
  border-radius: 18px;
  padding: 22px;
  box-shadow: 0 4px 16px rgba(18, 58, 61, 0.04);
}

.event-news-sidebox h3 {
  font-size: 16px;
  font-weight: 800;
  color: #123a3d;
  margin: 0 0 16px;
  padding-bottom: 10px;
  border-bottom: 2px solid #e6f6f5;
  display: flex;
  align-items: center;
  gap: 8px;
}

.event-related-link {
  display: block;
  padding: 12px 0;
  border-bottom: 1px dashed #e2eceb;
  color: #1e293b;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.7;
  transition: color 0.15s, transform 0.15s;
}

.event-related-link:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.event-related-link:hover {
  color: #007b7a;
  transform: translateX(-3px);
}

.event-related-date {
  display: block;
  font-size: 11px;
  color: #94a3b8;
  margin-top: 4px;
}

@media (max-width: 900px) {
  .event-news-layout {
    grid-template-columns: 1fr;
  }
  .event-news-sidebar {
    position: static;
  }
  .event-news-card {
    padding: 24px;
  }
}
</style>

<main class="event-news-page">
  <div class="event-news-layout">
    <article class="event-news-wrap">
      <!-- Breadcrumbs -->
      <nav class="event-news-breadcrumbs" aria-label="موقعیت در سایت">
        <a href="/events.php">رویدادهای مکسا</a>
        <span>/</span>
        <a href="/event.php?slug=<?= rawurlencode($news['event_slug']) ?>"><?= event_h($news['event_title']) ?></a>
        <span>/</span>
        <span>خبر اختصاصی</span>
      </nav>

      <div class="event-news-card">
        <div class="event-news-tag">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
          <span>خبر رویداد · <?= event_h($news['event_title']) ?></span>
        </div>

        <h1><?= event_h($news['title']) ?></h1>

        <div class="event-news-meta-row">
          <div class="event-news-meta-item">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <span><?= event_h(event_date_label(substr((string)($news['published_at'] ?: $news['created_at']), 0, 10))) ?></span>
          </div>
        </div>

        <?php if (!empty($news['image'])): ?>
          <div class="event-news-figure">
            <img src="<?= event_h($news['image']) ?>" alt="<?= event_h($news['title']) ?>">
          </div>
        <?php endif; ?>

        <div class="event-news-body">
          <?= HtmlSanitizer::sanitize((string)$news['content']) ?>
        </div>
      </div>
    </article>

    <aside class="event-news-sidebar">
      <div class="event-news-sidebox">
        <h3>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
          <span>همایش مرتبط</span>
        </h3>
        <p style="font-size:13px;color:#475569;line-height:1.7;margin:0 0 14px;">
          <?= event_h($news['event_title']) ?>
        </p>
        <a href="/event.php?slug=<?= rawurlencode($news['event_slug']) ?>" style="display:inline-flex;align-items:center;gap:6px;color:#007b7a;font-size:13px;font-weight:800;text-decoration:none;">
          <span>مشاهده صفحه همایش</span>
          <span>→</span>
        </a>
      </div>

      <?php if (!empty($related)): ?>
        <div class="event-news-sidebox">
          <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" y1="10" x2="3" y2="10"></line><line x1="21" y1="6" x2="3" y2="6"></line><line x1="21" y1="14" x2="3" y2="14"></line><line x1="21" y1="18" x2="3" y2="18"></line></svg>
            <span>سایر اخبار این همایش</span>
          </h3>
          <?php foreach ($related as $rel): ?>
            <a class="event-related-link" href="/event-news.php?slug=<?= rawurlencode($rel['slug']) ?>">
              <div><?= event_h($rel['title']) ?></div>
              <span class="event-related-date">
                <?= event_h(event_date_label(substr((string)($rel['published_at'] ?: $rel['created_at']), 0, 10))) ?>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </aside>
  </div>
</main>

<?php require __DIR__ . '/dashboard/components/footer/component.php';

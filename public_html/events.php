<?php
declare(strict_types=1);

require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/event-lib.php';

$filter = in_array($_GET['filter'] ?? 'all', ['all', 'upcoming', 'past'], true) ? (string)$_GET['filter'] : 'all';
$q = trim((string)($_GET['q'] ?? ''));

// Get counts for tabs
$countAll = (int)$pdo->query("SELECT COUNT(*) FROM events WHERE status='published'")->fetchColumn();
$countUpcoming = (int)$pdo->query("SELECT COUNT(*) FROM events WHERE status='published' AND event_date >= CURDATE()")->fetchColumn();
$countPast = (int)$pdo->query("SELECT COUNT(*) FROM events WHERE status='published' AND event_date < CURDATE()")->fetchColumn();

$where = "status='published'";
$params = [];

if ($filter === 'upcoming') {
    $where .= ' AND event_date >= CURDATE()';
} elseif ($filter === 'past') {
    $where .= ' AND event_date < CURDATE()';
}

if ($q !== '') {
    $where .= ' AND (title LIKE ? OR short_description LIKE ? OR description LIKE ? OR location LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like, $like];
}

$orderBy = $filter === 'past' ? 'event_date DESC, id DESC' : 'event_date ASC, id DESC';
$st = $pdo->prepare("SELECT * FROM events WHERE {$where} ORDER BY {$orderBy}");
$st->execute($params);
$events = $st->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'رویدادها و همایش‌ها · مؤسسه خیریه مکسا';
require __DIR__ . '/dashboard/components/header/component.php';
?>
<style>
/* CSS Variables & Base Theme */
:root {
  --ev-primary: #007b7a;
  --ev-primary-dark: #07474e;
  --ev-primary-deep: #0a5c66;
  --ev-primary-light: #e6f6f5;
  --ev-primary-hover: #006362;
  --ev-accent: #f4a61e;
  --ev-accent-light: #fff8eb;
  --ev-text-main: #123a3d;
  --ev-text-body: #4a5e62;
  --ev-text-muted: #7b8e91;
  --ev-border: #e2eceb;
  --ev-bg-soft: #f4f8f7;
  --ev-white: #ffffff;
  --ev-shadow-card: 0 12px 36px rgba(18, 58, 61, 0.08);
  --ev-shadow-hover: 0 20px 48px rgba(18, 58, 61, 0.14);
}

.events-root {
  direction: rtl;
  font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background-color: var(--ev-bg-soft);
  min-height: 80vh;
  padding-bottom: 80px;
}

/* Hero Section */
.events-hero {
  background: linear-gradient(135deg, #06393f 0%, #0a5c66 55%, #0d7a87 100%);
  color: var(--ev-white);
  padding: 56px 24px 64px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(7, 71, 78, 0.15);
}

.events-hero::before {
  content: '';
  position: absolute;
  top: -40%;
  left: -20%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(0, 209, 193, 0.15) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
}

.events-hero::after {
  content: '';
  position: absolute;
  bottom: -30%;
  right: -10%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(244, 166, 30, 0.12) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
}

.events-hero-inner {
  max-width: 1040px;
  margin: 0 auto;
  position: relative;
  z-index: 2;
  text-align: center;
}

.events-hero-kicker {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(8px);
  padding: 6px 14px;
  border-radius: 30px;
  font-size: 13px;
  font-weight: 700;
  color: #a7f3d0;
  margin-bottom: 18px;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.events-hero h1 {
  font-size: clamp(26px, 4vw, 42px);
  font-weight: 900;
  margin: 0 0 14px;
  color: var(--ev-white);
  line-height: 1.35;
}

.events-hero-lead {
  font-size: clamp(14px, 1.8vw, 17px);
  color: #e0f2f1;
  max-width: 720px;
  margin: 0 auto 32px;
  line-height: 1.8;
  opacity: 0.95;
}

/* Search Box in Hero */
.events-search-wrap {
  max-width: 640px;
  margin: 0 auto;
}

.events-search-form {
  position: relative;
  display: flex;
  align-items: center;
  background: var(--ev-white);
  border-radius: 14px;
  padding: 6px 8px 6px 16px;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
  transition: box-shadow 0.2s, transform 0.2s;
}

.events-search-form:focus-within {
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25), 0 0 0 3px rgba(0, 123, 122, 0.35);
  transform: translateY(-2px);
}

.events-search-icon {
  width: 22px;
  height: 22px;
  color: #94a3b8;
  flex-shrink: 0;
  margin-left: 10px;
}

.events-search-input {
  flex: 1;
  border: none;
  outline: none;
  font-family: inherit;
  font-size: 14px;
  color: #1e293b;
  background: transparent;
  padding: 8px 4px;
}

.events-search-input::placeholder {
  color: #94a3b8;
}

.events-search-btn {
  background: var(--ev-primary);
  color: var(--ev-white);
  border: none;
  border-radius: 10px;
  padding: 10px 20px;
  font-family: inherit;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.15s, transform 0.1s;
  flex-shrink: 0;
}

.events-search-btn:hover {
  background: var(--ev-primary-hover);
}

.events-search-clear {
  color: #94a3b8;
  text-decoration: none;
  font-size: 13px;
  padding: 6px 10px;
  margin-left: 4px;
}

/* Container */
.events-main {
  max-width: 1140px;
  margin: -24px auto 0;
  padding: 0 20px;
  position: relative;
  z-index: 10;
}

/* Toolbar: Filters & Counter */
.events-nav-bar {
  background: var(--ev-white);
  border-radius: 14px;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
  box-shadow: 0 4px 16px rgba(18, 58, 61, 0.05);
  border: 1px solid var(--ev-border);
  margin-bottom: 28px;
}

.events-filter-pills {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.events-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  color: #4b5563;
  text-decoration: none;
  background: #f3f4f6;
  transition: all 0.2s;
  border: 1px solid transparent;
}

.events-pill:hover {
  background: #e5e7eb;
  color: #1f2937;
}

.events-pill.active {
  background: #f0fdfa;
  color: var(--ev-primary-deep);
  border-color: #0d7a87;
  box-shadow: 0 2px 8px rgba(13, 122, 135, 0.12);
}

.events-pill-count {
  font-size: 11px;
  background: rgba(0, 0, 0, 0.08);
  padding: 2px 7px;
  border-radius: 20px;
  font-weight: 800;
}

.events-pill.active .events-pill-count {
  background: rgba(13, 122, 135, 0.15);
  color: var(--ev-primary-deep);
}

.events-stat-count {
  font-size: 13px;
  color: var(--ev-text-muted);
  font-weight: 600;
}

/* Featured / Single Event Card Layout (Figma Node #2:37) */
.event-featured-card {
  background: var(--ev-white);
  border-radius: 20px;
  border: 1px solid var(--ev-border);
  box-shadow: var(--ev-shadow-card);
  display: grid;
  grid-template-columns: 360px 1fr;
  overflow: hidden;
  transition: box-shadow 0.25s, transform 0.25s;
  margin-bottom: 24px;
}

.event-featured-card:hover {
  box-shadow: var(--ev-shadow-hover);
  transform: translateY(-3px);
}

.featured-poster {
  position: relative;
  min-height: 280px;
  background: linear-gradient(135deg, #07474e, #0a5c66);
  overflow: hidden;
}

.featured-poster img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.4s ease;
}

.event-featured-card:hover .featured-poster img {
  transform: scale(1.03);
}

.featured-poster-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.85);
  padding: 24px;
  text-align: center;
}

.featured-badge {
  position: absolute;
  top: 16px;
  right: 16px;
  z-index: 2;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 800;
  backdrop-filter: blur(6px);
}

.featured-badge.active {
  background: rgba(16, 185, 129, 0.9);
  color: #ffffff;
}

.featured-badge.past {
  background: rgba(75, 85, 99, 0.85);
  color: #ffffff;
}

.featured-content {
  padding: 32px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.featured-kicker {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f0fdfa;
  color: var(--ev-primary-deep);
  border: 1px solid #ccfbf1;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 800;
  width: fit-content;
  margin-bottom: 12px;
}

.featured-title {
  font-size: clamp(20px, 2.2vw, 24px);
  font-weight: 800;
  color: var(--ev-text-main);
  line-height: 1.5;
  margin: 0 0 14px;
}

.featured-title a {
  color: inherit;
  text-decoration: none;
  transition: color 0.15s;
}

.featured-title a:hover {
  color: var(--ev-primary);
}

.featured-meta-list {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f1f5f9;
}

.featured-meta-item {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 13px;
  color: var(--ev-text-body);
  font-weight: 500;
}

.featured-meta-item svg {
  width: 17px;
  height: 17px;
  color: var(--ev-primary);
  flex-shrink: 0;
}

.featured-desc {
  font-size: 14px;
  line-height: 1.85;
  color: var(--ev-text-body);
  margin: 0 0 24px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.featured-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.btn-ev-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--ev-primary);
  color: var(--ev-white);
  padding: 11px 22px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 800;
  text-decoration: none;
  transition: background 0.15s, transform 0.1s;
}

.btn-ev-primary:hover {
  background: var(--ev-primary-hover);
  transform: translateY(-1px);
}

.btn-ev-outline {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  color: var(--ev-primary);
  border: 1px solid var(--ev-border);
  padding: 10px 18px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.15s;
}

.btn-ev-outline:hover {
  background: #f0fdfa;
  border-color: var(--ev-primary);
}

/* Grid Layout for Multiple Events */
.events-grid-layout {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
}

.event-grid-card {
  background: var(--ev-white);
  border-radius: 16px;
  border: 1px solid var(--ev-border);
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(18, 58, 61, 0.05);
  display: flex;
  flex-direction: column;
  transition: box-shadow 0.2s, transform 0.2s;
}

.event-grid-card:hover {
  box-shadow: var(--ev-shadow-hover);
  transform: translateY(-3px);
}

.grid-card-cover {
  height: 190px;
  position: relative;
  background: linear-gradient(135deg, #07474e, #0a5c66);
  overflow: hidden;
}

.grid-card-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.event-grid-card:hover .grid-card-cover img {
  transform: scale(1.04);
}

.grid-card-cover-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.8);
}

.grid-card-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 800;
  backdrop-filter: blur(6px);
}

.grid-card-badge.active {
  background: rgba(16, 185, 129, 0.9);
  color: #fff;
}

.grid-card-badge.past {
  background: rgba(75, 85, 99, 0.85);
  color: #fff;
}

.grid-card-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.grid-card-title {
  font-size: 17px;
  font-weight: 800;
  color: var(--ev-text-main);
  line-height: 1.55;
  margin: 0 0 12px;
}

.grid-card-title a {
  color: inherit;
  text-decoration: none;
}

.grid-card-title a:hover {
  color: var(--ev-primary);
}

.grid-card-meta {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 14px;
  font-size: 12px;
  color: var(--ev-text-muted);
}

.grid-card-meta span {
  display: flex;
  align-items: center;
  gap: 6px;
}

.grid-card-meta svg {
  width: 14px;
  height: 14px;
  color: var(--ev-primary);
}

.grid-card-desc {
  font-size: 13px;
  color: var(--ev-text-body);
  line-height: 1.8;
  margin: 0 0 18px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  flex: 1;
}

.grid-card-footer {
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.grid-card-link {
  color: var(--ev-primary);
  font-weight: 800;
  font-size: 13px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: gap 0.15s;
}

.grid-card-link:hover {
  gap: 8px;
}

/* Empty State (Figma Node #2:50) */
.events-empty-state {
  background: var(--ev-white);
  border: 1px dashed #cbd5e1;
  border-radius: 20px;
  padding: 56px 24px;
  text-align: center;
  max-width: 680px;
  margin: 32px auto;
  box-shadow: 0 4px 16px rgba(18, 58, 61, 0.03);
}

.empty-state-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 16px;
  color: #94a3b8;
  opacity: 0.8;
}

.events-empty-state h3 {
  font-size: 18px;
  font-weight: 800;
  color: var(--ev-text-main);
  margin: 0 0 8px;
}

.events-empty-state p {
  font-size: 14px;
  color: var(--ev-text-muted);
  max-width: 480px;
  margin: 0 auto 24px;
  line-height: 1.8;
}

/* Responsive adjustments */
@media (max-width: 900px) {
  .event-featured-card {
    grid-template-columns: 1fr;
  }
  .featured-poster {
    height: 220px;
    min-height: unset;
  }
  .featured-content {
    padding: 24px;
  }
}

@media (max-width: 640px) {
  .events-hero {
    padding: 40px 16px 52px;
  }
  .events-main {
    padding: 0 16px;
  }
  .events-nav-bar {
    flex-direction: column;
    align-items: stretch;
  }
  .events-filter-pills {
    justify-content: stretch;
  }
  .events-pill {
    flex: 1;
    justify-content: center;
    padding: 8px 10px;
    font-size: 12px;
  }
}
</style>

<div class="events-root">
  <!-- Hero Section -->
  <section class="events-hero">
    <div class="events-hero-inner">
      <div class="events-hero-kicker">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        <span>تقویم و همایش‌های تخصصی مکسا</span>
      </div>
      <h1>رویدادها، همایش‌ها و کارگاه‌های مکسا</h1>
      <p class="events-hero-lead">
        آموزش، توانمندسازی و توسعه دانش مراقبت‌های حمایتی و تسکینی برای پزشکان، پرستاران، داوطلبان و عموم جامعه در سراسر کشور.
      </p>

      <!-- Live Search Box -->
      <div class="events-search-wrap">
        <form class="events-search-form" method="get" action="/events.php">
          <?php if ($filter !== 'all'): ?>
            <input type="hidden" name="filter" value="<?= event_h($filter) ?>">
          <?php endif; ?>
          <svg class="events-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" name="q" class="events-search-input" id="eventsFilterInput"
                 placeholder="جستجو در عنوان همایش، محل برگزاری یا محورهای علمی..."
                 value="<?= event_h($q) ?>" autocomplete="off">
          <?php if ($q !== ''): ?>
            <a href="/events.php<?= $filter !== 'all' ? '?filter=' . urlencode($filter) : '' ?>" class="events-search-clear" title="حذف جستجو">✕</a>
          <?php endif; ?>
          <button type="submit" class="events-search-btn">جستجو</button>
        </form>
      </div>
    </div>
  </section>

  <!-- Main Content Area -->
  <main class="events-main">
    <!-- Filter Bar -->
    <div class="events-nav-bar">
      <div class="events-filter-pills" role="tablist" aria-label="فیلتر رویدادها">
        <a class="events-pill <?= $filter === 'all' ? 'active' : '' ?>" href="/events.php?filter=all<?= $q ? '&q=' . urlencode($q) : '' ?>">
          <span>همه رویدادها</span>
          <span class="events-pill-count"><?= $countAll ?></span>
        </a>
        <a class="events-pill <?= $filter === 'upcoming' ? 'active' : '' ?>" href="/events.php?filter=upcoming<?= $q ? '&q=' . urlencode($q) : '' ?>">
          <span>پیش‌رو و جاری</span>
          <span class="events-pill-count"><?= $countUpcoming ?></span>
        </a>
        <a class="events-pill <?= $filter === 'past' ? 'active' : '' ?>" href="/events.php?filter=past<?= $q ? '&q=' . urlencode($q) : '' ?>">
          <span>برگزارشده</span>
          <span class="events-pill-count"><?= $countPast ?></span>
        </a>
      </div>
      <div class="events-stat-count">
        نمایش <?= count($events) ?> رویداد<?= $q ? ' منطبق با «' . event_h($q) . '»' : '' ?>
      </div>
    </div>

    <!-- Events List / Empty State -->
    <?php if (empty($events)): ?>
      <div class="events-empty-state">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
          <line x1="10" y1="14" x2="14" y2="18"></line>
          <line x1="14" y1="14" x2="10" y2="18"></line>
        </svg>
        <h3>رویدادی در این دسته‌بندی یافت نشد</h3>
        <p>
          <?= $q !== '' ? 'هیچ رویدادی منطبق با عبارت جستجوی «' . event_h($q) . '» پیدا نشد.' : 'در حال حاضر برنامه‌ای در این بخش تعریف نشده است.' ?>
          می‌توانید فیلترها را تغییر داده یا از تمامی رویدادها دیدن فرمایید.
        </p>
        <a href="/events.php" class="btn-ev-primary">مشاهده تمامی رویدادها</a>
      </div>
    <?php elseif (count($events) === 1): ?>
      <?php
        $ev = $events[0];
        $isUpcoming = strtotime($ev['event_date']) >= strtotime(date('Y-m-d'));
      ?>
      <!-- Single/Featured Card Layout (Balanced & High-Impact) -->
      <article class="event-featured-card" data-event-item>
        <div class="featured-poster">
          <?php if (!empty($ev['poster'])): ?>
            <img src="<?= event_h($ev['poster']) ?>" alt="پوستر <?= event_h($ev['title']) ?>">
          <?php else: ?>
            <div class="featured-poster-fallback">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
              <div style="font-weight:700;margin-top:10px;">همایش تخصصی مکسا</div>
            </div>
          <?php endif; ?>
          <span class="featured-badge <?= $isUpcoming ? 'active' : 'past' ?>">
            <?= $isUpcoming ? 'در حال ثبت‌نام' : 'برگزارشده' ?>
          </span>
        </div>

        <div class="featured-content">
          <div>
            <div class="featured-kicker">
              <span>همایش ملی و تخصصی</span>
            </div>
            <h2 class="featured-title">
              <a href="/event.php?slug=<?= rawurlencode($ev['slug']) ?>"><?= event_h($ev['title']) ?></a>
            </h2>

            <div class="featured-meta-list">
              <div class="featured-meta-item">
                <!-- Iconly Calendar SVG -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>تاریخ: <?= event_h(event_date_label($ev['event_date'])) ?></span>
              </div>
              <?php if (!empty($ev['start_time'])): ?>
                <div class="featured-meta-item">
                  <!-- Iconly Clock SVG -->
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                  <span>شروع: <?= event_h(substr((string)$ev['start_time'], 0, 5)) ?></span>
                </div>
              <?php endif; ?>
              <?php if (!empty($ev['location'])): ?>
                <div class="featured-meta-item">
                  <!-- Iconly Location SVG -->
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                  <span><?= event_h($ev['location']) ?></span>
                </div>
              <?php endif; ?>
            </div>

            <?php if (!empty($ev['short_description'])): ?>
              <p class="featured-desc"><?= event_h($ev['short_description']) ?></p>
            <?php endif; ?>
          </div>

          <div class="featured-actions">
            <a href="/event.php?slug=<?= rawurlencode($ev['slug']) ?>" class="btn-ev-primary">
              <span>مشاهده جزئیات و صفحه همایش</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <?php if (!empty($ev['schedule_file'])): ?>
              <a href="<?= event_h($ev['schedule_file']) ?>" download class="btn-ev-outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>دانلود سین برنامه</span>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </article>
    <?php else: ?>
      <!-- Multi-Event Grid Layout -->
      <div class="events-grid-layout">
        <?php foreach ($events as $ev):
          $isUpcoming = strtotime($ev['event_date']) >= strtotime(date('Y-m-d'));
        ?>
          <article class="event-grid-card" data-event-item>
            <div class="grid-card-cover">
              <?php if (!empty($ev['poster'])): ?>
                <img src="<?= event_h($ev['poster']) ?>" alt="پوستر <?= event_h($ev['title']) ?>" loading="lazy">
              <?php else: ?>
                <div class="grid-card-cover-fallback">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
              <?php endif; ?>
              <span class="grid-card-badge <?= $isUpcoming ? 'active' : 'past' ?>">
                <?= $isUpcoming ? 'در حال ثبت‌نام' : 'برگزارشده' ?>
              </span>
            </div>

            <div class="grid-card-body">
              <h2 class="grid-card-title">
                <a href="/event.php?slug=<?= rawurlencode($ev['slug']) ?>"><?= event_h($ev['title']) ?></a>
              </h2>

              <div class="grid-card-meta">
                <span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                  <?= event_h(event_date_label($ev['event_date'])) ?>
                </span>
                <?php if (!empty($ev['location'])): ?>
                  <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <?= event_h($ev['location']) ?>
                  </span>
                <?php endif; ?>
              </div>

              <?php if (!empty($ev['short_description'])): ?>
                <p class="grid-card-desc"><?= event_h($ev['short_description']) ?></p>
              <?php endif; ?>

              <div class="grid-card-footer">
                <a href="/event.php?slug=<?= rawurlencode($ev['slug']) ?>" class="grid-card-link">
                  <span>مشاهده جزئیات</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<script>
// Client-side quick filter on input keystroke (in addition to GET form submit)
document.addEventListener('DOMContentLoaded', function() {
  var input = document.getElementById('eventsFilterInput');
  if (!input) return;
  var items = document.querySelectorAll('[data-event-item]');
  if (!items.length) return;

  input.addEventListener('input', function() {
    var val = input.value.trim().toLowerCase();
    if (!val) {
      items.forEach(function(el) { el.style.display = ''; });
      return;
    }
    items.forEach(function(el) {
      var text = el.innerText.toLowerCase();
      el.style.display = text.indexOf(val) !== -1 ? '' : 'none';
    });
  });
});
</script>

<?php require __DIR__ . '/dashboard/components/footer/component.php';

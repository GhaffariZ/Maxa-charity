<?php
/* صفحه‌ی «شعب مکسا».
   فهرست شعبه‌های فعال + کامپوننت نقشه‌ی ایران (همان نقشه‌ی رنگی شعب).
   ساختار صفحه از الگوی contactus.php پیروی می‌کند: هدر مشترک، محتوا، فوتر مشترک. */

require_once __DIR__ . '/core/database.php';

$branches = [];
try {
    $stmt = $pdo->query(
        "SELECT name, slug, is_hq FROM branches
         WHERE status = 'active'
         ORDER BY is_hq DESC, name ASC"
    );
    $branches = $stmt->fetchAll();
} catch (Throwable $e) {
    $branches = [];
}

$pageTitle = 'شعب مکسا';
require __DIR__ . '/dashboard/components/header/component.php';
?>

<style>
  .br-wrap {
    max-width: var(--cta-container, 1440px);
    margin: 0 auto;
    padding: 56px 20px 40px;
    font-family: 'Vazirmatn', sans-serif;
    direction: rtl;
  }
  .br-head {
    text-align: center;
    margin-bottom: 40px;
  }
  .br-head h1 {
    font-size: clamp(28px, 4vw, 44px);
    font-weight: 900;
    color: #2f3437;
    margin: 0 0 12px;
  }
  .br-head p {
    color: #6b7280;
    font-size: 16px;
    line-height: 2;
    max-width: 720px;
    margin: 0 auto;
  }

  /* فهرست شعب */
  .br-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 18px;
    margin: 0 auto 8px;
  }
  .br-item {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fff;
    border: 1px solid #ecedf0;
    border-radius: 16px;
    padding: 18px 20px;
    box-shadow: 0 6px 18px rgba(0,0,0,.04);
    text-decoration: none;
    color: inherit;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  }
  a.br-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 30px rgba(7,130,142,.12);
    border-color: #10aeb8;
  }
  .br-icon {
    flex: 0 0 46px;
    width: 46px; height: 46px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 14px;
    font-size: 22px;
    background: rgba(16,174,184,.12);
    color: #07828e;
  }
  .br-item h3 { margin: 0; font-size: 16px; font-weight: 800; color: #2f3437; }
  .br-item .br-tag {
    display: inline-block;
    margin-top: 6px;
    font-size: 12.5px;
    font-weight: 700;
    color: #07828e;
  }
  .br-item .br-tag--hq { color: #d9820a; }

  .br-empty {
    text-align: center;
    color: #8b8f96;
    font-size: 15px;
    padding: 24px;
    background: #fafbfc;
    border: 1px dashed #e2e5ea;
    border-radius: 16px;
  }
</style>

<div class="br-wrap">
  <div class="br-head">
    <h1>شعب مکسا</h1>
    <p>شبکه‌ای از شعب و مراکز مراقبت تسکینی مکسا در سراسر کشور در کنار شماست. در فهرست زیر شعب فعال را ببینید و روی نقشه، استان‌های دارای شعبه را مشاهده کنید.</p>
  </div>

  <?php if (!empty($branches)): ?>
    <div class="br-list">
      <?php foreach ($branches as $b):
        $isHq  = !empty($b['is_hq']);
        $name  = htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8');
        $slug  = trim((string)($b['slug'] ?? ''));
        // شعب (غیر از دفتر مرکزی) به صفحه‌ی خانه‌ی شعبه لینک می‌شوند.
        $href  = (!$isHq && $slug !== '') ? '/' . rawurlencode($slug) : '';
        $tag   = $isHq ? 'دفتر مرکزی' : 'شعبه';
        $tagCls = $isHq ? 'br-tag br-tag--hq' : 'br-tag';
        $el    = $href !== '' ? 'a' : 'div';
      ?>
        <<?= $el ?> class="br-item"<?= $href !== '' ? ' href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
          <span class="br-icon">📍</span>
          <div>
            <h3><?= $name ?></h3>
            <span class="<?= $tagCls ?>"><?= $tag ?></span>
          </div>
        </<?= $el ?>>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="br-empty">در حال حاضر فهرست شعب در دسترس نیست. لطفاً بعداً دوباره مراجعه کنید.</div>
  <?php endif; ?>
</div>

<?php
/* کامپوننت نقشه‌ی ایران با شعب رنگی (همان کامپوننت صفحه‌ی شعب). */
require __DIR__ . '/dashboard/components/branches/component.php';

require __DIR__ . '/dashboard/components/footer/component.php';

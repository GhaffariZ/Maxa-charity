<?php
/* صفحه‌ی «شعب مکسا».
   فهرست شعبه‌های فعال + کامپوننت نقشه‌ی ایران (همان نقشه‌ی رنگی شعب).
   ساختار صفحه از الگوی contactus.php پیروی می‌کند: هدر مشترک، محتوا، فوتر مشترک. */

require_once __DIR__ . '/core/database.php';

// نگاشت آیکون‌های وکتور به سبک Iconly Pro برای شعب و مراکز
if (!function_exists('get_branch_icon')) {
    function get_branch_icon(string $key): string {
        switch ($key) {
            case 'hq':
            case 'building':
            case '🏛️':
                // Iconly Work / Building Style (ستاد مرکزی)
                return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h1M14 10h1M9 14h1M14 14h1M11 21v-4a1 1 0 0 1 1-1h0a1 1 0 0 1 1 1v4"/></svg>';
            case 'headset':
            case 'telehealth':
            case 'contact-center':
            case '🎧':
                // Iconly Voice / Calling / Headset Style (دورپزشکی و ارتباطات)
                return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 13v-2a9 9 0 0 1 18 0v2"/><rect x="2" y="13" width="4" height="6" rx="2"/><rect x="18" y="13" width="4" height="6" rx="2"/><path d="M20 19v1a3 3 0 0 1-3 3h-3"/></svg>';
            case 'cdst':
            case 'graduation':
            case 'education':
            case '🎓':
                // Iconly Discovery / Graduation Style (استعدادهای دانشجویی)
                return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5Z"/><path d="M6 12.5v4.5c0 2 2.7 4 6 4s6-2 6-4v-4.5"/></svg>';
            case 'location':
            case '📍':
            default:
                // Iconly Location Style (شعب استانی)
                return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 13.43c1.78 0 3.23-1.44 3.23-3.23s-1.45-3.23-3.23-3.23c-1.79 0-3.23 1.45-3.23 3.23s1.44 3.23 3.23 3.23Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M3.62 10.2c0 6.07 7.07 10.98 7.77 11.45.37.25.86.25 1.23 0 .7-.47 7.76-5.38 7.76-11.45C20.38 5.56 16.63 2 12 2 7.37 2 3.62 5.56 3.62 10.2Z"/></svg>';
        }
    }
}

// فهرست استاندارد شعب و مراکز به ترتیب درخواستی (مشهد بالاتر در کنار بقیه شهرها)
$defaultBranches = [
    [
        'name' => 'دفتر ستاد مرکزی مکسا',
        'href' => '/home',
        'is_hq' => true,
        'tag' => 'دفتر مرکزی',
        'icon' => 'hq'
    ],
    [
        'name' => 'شعبه تهران',
        'href' => '/tehran-branch',
        'is_hq' => false,
        'tag' => 'شعبه فعال',
        'icon' => 'location'
    ],
    [
        'name' => 'شعبه اصفهان',
        'href' => '/esfahan-branch',
        'is_hq' => false,
        'tag' => 'شعبه فعال',
        'icon' => 'location'
    ],
    [
        'name' => 'شعبه مشهد',
        'href' => '/mashhad-branch',
        'is_hq' => false,
        'tag' => 'شعبه فعال',
        'icon' => 'location'
    ],
    [
        'name' => 'شعبه اهواز (خوزستان)',
        'href' => '/ahvaz-branch',
        'is_hq' => false,
        'tag' => 'شعبه فعال',
        'icon' => 'location'
    ],
    [
        'name' => 'شعبه تبریز (آذربایجان شرقی)',
        'href' => '/tabriz-branch',
        'is_hq' => false,
        'tag' => 'شعبه فعال',
        'icon' => 'location'
    ],
    [
        'name' => 'شعبه قم',
        'href' => '/qom-branch',
        'is_hq' => false,
        'tag' => 'شعبه فعال',
        'icon' => 'location'
    ],
    [
        'name' => 'شعبه کاشان',
        'href' => '/kashan-branch',
        'is_hq' => false,
        'tag' => 'شعبه فعال',
        'icon' => 'location'
    ],
    [
        'name' => 'مرکز ارتباطات و دورپزشکی',
        'href' => '/contact-center',
        'is_hq' => false,
        'tag' => 'مرکز تخصصی',
        'icon' => 'headset'
    ],
    [
        'name' => 'مرکز رویش استعدادهای دانشجویی (CDST)',
        'href' => '/cdst',
        'is_hq' => false,
        'tag' => 'مرکز نوآوری',
        'icon' => 'cdst'
    ]
];

$branches = $defaultBranches;

$pageTitle = 'شعب مکسا';
require __DIR__ . '/dashboard/components/header/component.php';
?>

<style>
  .br-wrap {
    max-width: var(--cta-container, 1440px);
    margin: 0 auto;
    padding: 40px 20px 50px;
    font-family: 'Vazirmatn', sans-serif;
    direction: rtl;
  }

  /* چیدمان دوستونه: ستون راست = عنوان + فهرست شعب، ستون چپ = نقشه */
  .br-layout {
    display: flex;
    align-items: flex-start;
    gap: 32px;
  }
  .br-side {           /* ستون راست */
    flex: 0 0 380px;
    max-width: 380px;
  }
  .br-map {            /* ستون چپ (نقشه) */
    flex: 1 1 auto;
    min-width: 0;
  }
  /* نقشه‌ی درون ستون چپ بدون فاصله‌ی عمودی اضافی و تمام‌عرض ستون */
  .br-map .branches { padding: 0; }
  .br-map #Iran { width: 100%; max-width: none; }

  .br-head {
    text-align: right;
    margin-bottom: 24px;
  }
  .br-head h1 {
    font-size: clamp(24px, 3.2vw, 36px);
    font-weight: 900;
    color: #0f172a;
    margin: 0 0 10px;
    line-height: 1.35;
  }
  .br-head p {
    color: #64748b;
    font-size: 14.5px;
    line-height: 1.9;
    margin: 0;
  }

  /* فهرست شعب — ستونی (عمودی) */
  .br-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
    margin: 0;
  }
  .br-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 16px;
    box-shadow: 0 2px 6px rgba(0,0,0,.02);
    text-decoration: none;
    color: inherit;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background-color .18s ease;
  }
  a.br-item:hover {
    transform: translateX(-4px);
    box-shadow: 0 10px 24px rgba(0,123,122,.1);
    border-color: #007b7a;
    background: #f8fdfd;
  }
  .br-icon {
    flex: 0 0 42px;
    width: 42px; height: 42px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 11px;
    background: rgba(0,123,122,.08);
    color: #007b7a;
    flex-shrink: 0;
    transition: transform .18s ease, background-color .18s ease, color .18s ease;
  }
  .br-icon svg {
    width: 21px;
    height: 21px;
    display: block;
  }
  .br-item--hq .br-icon {
    background: rgba(244,166,30,.14);
    color: #b45309;
  }
  a.br-item:hover .br-icon {
    transform: scale(1.08);
    background: rgba(0,123,122,.14);
  }
  a.br-item--hq:hover .br-icon {
    background: rgba(244,166,30,.22);
  }
  .br-arrow {
    margin-right: auto;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform .18s ease, color .18s ease;
  }
  a.br-item:hover .br-arrow {
    color: #007b7a;
    transform: translateX(-3px);
  }
  a.br-item--hq:hover .br-arrow {
    color: #b45309;
  }
  .br-item h3 { margin: 0; font-size: 15px; font-weight: 800; color: #1e293b; line-height: 1.4; }
  .br-item .br-tag {
    display: inline-block;
    margin-top: 4px;
    font-size: 11.5px;
    font-weight: 700;
    color: #007b7a;
    background: rgba(0,123,122,.08);
    padding: 2px 8px;
    border-radius: 6px;
  }
  .br-item .br-tag--hq {
    color: #b45309;
    background: rgba(244,166,30,.14);
  }

  .br-empty {
    text-align: center;
    color: #8b8f96;
    font-size: 15px;
    padding: 24px;
    background: #fafbfc;
    border: 1px dashed #e2e5ea;
    border-radius: 16px;
  }

  /* روی صفحه‌های کوچک: ستون‌ها زیر هم بچینند (عنوان+فهرست، سپس نقشه) */
  @media (max-width: 992px) {
    .br-layout { flex-direction: column; }
    .br-side { flex-basis: auto; max-width: none; width: 100%; }
    .br-head { text-align: center; }
    .br-list {
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    }
  }
</style>

<div class="br-wrap">
  <div class="br-layout">

    <!-- ستون راست: عنوان + فهرست شعب -->
    <div class="br-side">
      <div class="br-head">
        <h1>شعب و مراکز مکسا</h1>
        <p>شبکه‌ای از شعب و مراکز مراقبت تسکینی مکسا در سراسر کشور در کنار شماست. با انتخاب هر شعبه، به صفحه اختصاصی آن هدایت می‌شوید.</p>
      </div>

      <?php if (!empty($branches)): ?>
        <div class="br-list">
          <?php foreach ($branches as $b):
            $isHq    = !empty($b['is_hq']);
            $name    = htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8');
            $href    = htmlspecialchars($b['href'] ?? '#', ENT_QUOTES, 'UTF-8');
            $tag     = htmlspecialchars($b['tag'] ?? 'شعبه', ENT_QUOTES, 'UTF-8');
            $iconKey = $b['icon'] ?? ($isHq ? 'hq' : 'location');
            $tagCls  = $isHq ? 'br-tag br-tag--hq' : 'br-tag';
            $itemCls = $isHq ? 'br-item br-item--hq' : 'br-item';
          ?>
            <a class="<?= $itemCls ?>" href="<?= $href ?>" title="<?= $name ?>">
              <span class="br-icon" aria-hidden="true"><?= get_branch_icon($iconKey) ?></span>
              <div style="flex: 1; min-width: 0;">
                <h3><?= $name ?></h3>
                <span class="<?= $tagCls ?>"><?= $tag ?></span>
              </div>
              <span class="br-arrow" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M15 18l-6-6 6-6"/>
                </svg>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="br-empty">در حال حاضر فهرست شعب در دسترس نیست. لطفاً بعداً دوباره مراجعه کنید.</div>
      <?php endif; ?>
    </div>

    <!-- ستون چپ: نقشه‌ی ایران با شعب رنگی و دکمه‌های ویژه -->
    <div class="br-map">
      <?php require __DIR__ . '/dashboard/components/branches/component.php'; ?>
    </div>

  </div>
</div>

<?php
require __DIR__ . '/dashboard/components/footer/component.php';

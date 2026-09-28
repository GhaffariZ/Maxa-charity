<?php
declare(strict_types=1);

require_once __DIR__ . '/event-lib.php';

$pdo = null;
try {
    require_once __DIR__ . '/core/database.php';
} catch (Throwable $e) {
    $pdo = null;
}

$slug = trim((string)($_GET['slug'] ?? ''));
$event = null;
$heroes = $people = $speakers = $partners = $news = [];

if ($pdo && ($slug !== '' || isset($_GET['id']))) {
    $eventIdParam = (int)($_GET['id'] ?? 0);
    if ($slug !== '') {
        $st = $pdo->prepare("SELECT * FROM events WHERE (slug = ? OR id = ?) AND status != 'archived' LIMIT 1");
        $st->execute([$slug, is_numeric($slug) ? (int)$slug : 0]);
    } else {
        $st = $pdo->prepare("SELECT * FROM events WHERE id = ? AND status != 'archived' LIMIT 1");
        $st->execute([$eventIdParam]);
    }
    $event = $st->fetch();

    if ($event) {
        $q = function(string $sql) use ($pdo, $event) {
            $s = $pdo->prepare($sql);
            $s->execute([(int)$event['id']]);
            return $s->fetchAll();
        };

        // 1. Heroes: query with resilience (no strict is_active=1 failure)
        try {
            $heroes = $q("SELECT * FROM event_heroes WHERE event_id = ? AND (is_active IS NULL OR is_active != 0) ORDER BY sort_order ASC, id ASC");
        } catch (Throwable $e) {
            try {
                $heroes = $q("SELECT * FROM event_heroes WHERE event_id = ? ORDER BY sort_order ASC, id ASC");
            } catch (Throwable $e2) {
                $heroes = [];
            }
        }
        if (empty($heroes)) {
            try {
                $heroes = $q("SELECT * FROM event_heroes WHERE event_id = ? ORDER BY sort_order ASC, id ASC");
            } catch (Throwable $e) {}
        }

        // 2. Organizers, Speakers, Partners
        try { $people = $q('SELECT * FROM event_people WHERE event_id = ? ORDER BY sort_order, id'); } catch (Throwable $e) { $people = []; }
        try { $speakers = $q('SELECT * FROM event_speakers WHERE event_id = ? ORDER BY sort_order, id'); } catch (Throwable $e) { $speakers = []; }
        try { $partners = $q('SELECT * FROM event_partners WHERE event_id = ? ORDER BY sort_order, id'); } catch (Throwable $e) { $partners = []; }

        // 3. News: fetch all non-archived news (published or freshly created)
        try {
            $news = $q("SELECT * FROM event_news WHERE event_id = ? AND status != 'archived' ORDER BY COALESCE(published_at, created_at) DESC, id DESC");
        } catch (Throwable $e) {
            try {
                $news = $q("SELECT * FROM event_news WHERE event_id = ? ORDER BY id DESC");
            } catch (Throwable $e2) {
                $news = [];
            }
        }
    }
}

// Fallback for snapshot / preview / direct demonstration
if (!$event && (defined('IN_SNAPSHOT') || $slug === '' || !$pdo)) {
    $event = [
        'id' => 1,
        'title' => 'ششمین همایش ملی مراقبت‌های حمایتی و تسکینی',
        'slug' => 'palliativeconference',
        'event_date' => '2026-10-08',
        'start_time' => '08:00:00',
        'end_time' => '18:00:00',
        'short_description' => 'همایشی ملی برای همگرایی دانش نوین، تبادل تجربیات و ارتقای کیفیت زندگی بیماران مبتلا به سرطان با محوریت ادغام مراقبت‌های تسکینی در نظام بهداشتی کشور.',
        'about' => "مرکز رویش استعدادهای دانشجویی مکسا برگزار می‌کند:\n📌 سلسله جلسات مکسا تاک\n👤 ارائه‌دهنده: دکتر استادهاشمی\nعضو هیئت علمی دانشگاه علوم توانبخشی و سلامت اجتماعی\nدبیر علمی واحد مددکاری اجتماعی مکسا\n👤 با نظارت: دکتر بابک ارجمند\nدانشیار دانشگاه علوم پزشکی تهران\nفلوشیپ هماتولوژی و مدیکال انکولوژی\n🗓 تاریخ برگزاری: سه‌شنبه ۱۴۰۵/۰۶/۳۱",
        'poster' => '/assets/events/demo-poster.jpg',
        'schedule_pdf' => '/uploads/events/schedule.pdf',
        'registration_url' => 'https://mymacsa.ir/register.php',
        'status' => 'published',
        'banner_active' => 1
    ];
    $heroes = [
        [
            'id' => 1,
            'title' => 'همایش ملی مراقبت‌های حمایتی و تسکینی',
            'description' => 'رویدادی برای متخصصان، بیماران و خانواده‌ها با محوریت کیفیت زندگی و حمایت‌های همه‌جانبه.',
            'image' => '/assets/events/hero-sample-1.jpg',
            'button_label' => 'ثبت نام در همایش',
            'button_link' => '#sec-about'
        ],
        [
            'id' => 2,
            'title' => 'کارگاه‌های تخصصی بین‌رشته‌ای',
            'description' => 'پنل‌های تخصصی با حضور برجسته‌ترین اساتید آنکولوژی و اخلاق پزشکی کشور.',
            'image' => '/assets/events/hero-sample-2.jpg',
            'button_label' => 'برنامه کارگاه‌ها',
            'button_link' => '#sec-speakers'
        ]
    ];
    $people = [
        ['name' => 'جناب دکتر محمدرضا شریفی', 'role' => 'scientific_secretary', 'title' => 'فوق تخصص انکولوژی و عضو هیئت علمی', 'image' => ''],
        ['name' => 'سرکار خانم دکتر سارا کریمی', 'role' => 'executive_secretary', 'title' => 'مدیر اجرایی طرح‌های جامع مراقبت تسکینی', 'image' => '']
    ];
    $speakers = [
        ['name' => 'دکتر مسعود سلیمانی', 'title' => 'فوق تخصص انکولوژی', 'image' => ''],
        ['name' => 'دکتر زهرا هاشمی', 'title' => 'متخصص طب تسکینی', 'image' => ''],
        ['name' => 'دکتر امین رضایی', 'title' => 'دکترای اخلاق پزشکی', 'image' => ''],
        ['name' => 'دکتر مریم نوری', 'title' => 'روانپزشک سلامت', 'image' => '']
    ];
    $partners = [
        ['name' => 'مؤسسه خیریه مکسا', 'logo' => ''],
        ['name' => 'دانشگاه علوم پزشکی تهران', 'logo' => ''],
        ['name' => 'انجمن سرطان ایران', 'logo' => ''],
        ['name' => 'جمعیت هلال احمر', 'logo' => ''],
        ['name' => 'سازمان نظام پزشکی ایران', 'logo' => ''],
        ['name' => 'وزارت بهداشت، درمان و آموزش پزشکی', 'logo' => ''],
        ['name' => 'سازمان بیمه سلامت ایران', 'logo' => ''],
        ['name' => 'ستاد ملی مبارزه با سرطان', 'logo' => '']
    ];
    $news = [
        ['id' => 1, 'slug' => 'news-1', 'title' => 'انتشار برنامه نهایی همایش', 'excerpt' => 'برنامه کامل سخنرانی‌ها، کارگاه‌ها و زمان‌بندی اصلی همایش منتشر شد.', 'published_at' => '2026-10-04 10:00:00', 'image' => ''],
        ['id' => 2, 'slug' => 'news-2', 'title' => 'اعلام زمان ثبت‌نام کارگاه‌ها', 'excerpt' => 'ثبت‌نام کارگاه‌های همایش از طریق سامانه اصلی و با ظرفیت محدود آغاز می‌شود.', 'published_at' => '2026-09-30 14:30:00', 'image' => ''],
        ['id' => 3, 'slug' => 'news-3', 'title' => 'معرفی سخنرانان و کارگاه‌های ویژه', 'excerpt' => 'اسامی سخنرانان و کارگاه‌های ویژه همایش اعلام شد و ثبت‌نام ادامه دارد.', 'published_at' => '2026-10-02 09:00:00', 'image' => '']
    ];
}

if (!$event) {
    http_response_code(404);
    exit('رویداد پیدا نشد.');
}

$pageTitle = $event['title'];
$iso = ($event['event_date'] ?: '2026-10-08') . 'T' . substr((string)($event['start_time'] ?: '08:00:00'), 0, 8) . '+03:30';

if (!defined('IN_SNAPSHOT') && file_exists(__DIR__ . '/dashboard/components/header/component.php')) {
    require __DIR__ . '/dashboard/components/header/component.php';
} else {
    echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . event_h($event['title']) . ' | مکسا</title><link rel="stylesheet" href="/font.css"></head><body>';
}
?>
<style>
  /* ============================================================================
     Design Tokens & Variables
     Deep Teal (#0A5C66), Warm Amber (#D97706), Soft White Surfaces
     ============================================================================ */
  :root {
    --event-teal-dark: #0A5C66;
    --event-primary: #0D7A87;
    --event-primary-light: #F0FDFA;
    --event-amber: #F5A623;
    --event-amber-dark: #D97706;
    --event-amber-hover: #E0921B;
    --event-amber-light: #FFFBEB;
    --event-surface: #FFFFFF;
    --event-bg-light: #F8FAFB;
    --event-text-main: #1F2937;
    --event-text-muted: #4B5563;
    --event-text-light: #6B7280;
    --event-border: #E5E7EB;
    --event-border-subtle: #F3F4F6;
    --event-radius: 16px;
    --event-radius-lg: 24px;
    --event-shadow: 0 4px 16px rgba(10, 92, 102, 0.06);
    --event-shadow-hover: 0 12px 30px rgba(10, 92, 102, 0.12);
  }

  .event-public-page {
    direction: rtl;
    color: var(--event-text-main);
    background: #FFFFFF;
    font-family: 'Vazirmatn', system-ui, -apple-system, sans-serif !important;
    overflow-x: clip;
    width: 100%;
  }

  .event-public-page *,
  .event-public-page *::before,
  .event-public-page *::after {
    box-sizing: border-box;
    font-family: 'Vazirmatn', system-ui, -apple-system, sans-serif !important;
  }

  .event-public-page a {
    color: inherit;
    text-decoration: none;
  }

  .event-container {
    max-width: 1320px;
    margin: 0 auto;
    padding: 0 24px;
  }

  /* ============================================================================
     1. HERO SECTION
     ============================================================================ */
  .event-hero {
    background: radial-gradient(circle at 15% 25%, #0d7a87 0%, #0A5C66 70%, #053b42 100%);
    color: #FFFFFF;
    padding: 64px 0 72px;
    position: relative;
    overflow: hidden;
  }
  .event-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 90% 10%, rgba(255, 255, 255, 0.06) 0%, transparent 60%);
    pointer-events: none;
  }
  .event-hero-inner {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 48px;
    align-items: center;
    position: relative;
    z-index: 2;
  }

  .event-hero-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .event-hero-title {
    font-size: clamp(26px, 3.6vw, 42px);
    font-weight: 900;
    line-height: 1.3;
    color: #FFFFFF;
    margin: 0;
  }

  .event-hero-desc {
    font-size: 16px;
    line-height: 1.8;
    color: #F0FDFA;
    opacity: 0.95;
    max-width: 620px;
    margin: 0;
  }

  /* Event Hero Meta Chips */
  .event-hero-chips {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    margin: 4px 0 2px;
  }
  .event-hero-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 999px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 600;
    color: #FFFFFF;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, background 0.2s ease;
  }
  .event-hero-chip:hover {
    background: rgba(255, 255, 255, 0.18);
    transform: translateY(-1px);
  }
  .event-hero-chip svg {
    flex-shrink: 0;
    opacity: 0.95;
    color: #A7F3D0;
  }
  .event-hero-chip-status {
    background: rgba(16, 185, 129, 0.2);
    border-color: rgba(16, 185, 129, 0.45);
    color: #D1FAE5;
    font-weight: 700;
  }
  .chip-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10B981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulseDot 2s infinite;
  }
  @keyframes pulseDot {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
  }

  /* Countdown Timer */
  .event-countdown-grid {
    display: grid;
    grid-template-columns: repeat(4, 82px);
    gap: 12px;
  }
  .countdown-box {
    background: #FFFFFF;
    border-radius: 14px;
    padding: 10px 8px;
    text-align: center;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.16);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  .countdown-val {
    display: block;
    font-size: 26px;
    font-weight: 900;
    color: var(--event-teal-dark);
    line-height: 1.1;
    font-variant-numeric: tabular-nums;
  }
  .countdown-lbl {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--event-text-light);
    margin-top: 4px;
  }

  /* Hero Action Buttons */
  .event-hero-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 4px;
  }
  .btn-register-hero {
    background: linear-gradient(135deg, #F5A623 0%, #E0921B 100%);
    color: #FFFFFF;
    padding: 13px 26px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 8px 24px rgba(245, 166, 35, 0.38);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
    text-decoration: none;
  }
  .btn-register-hero:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(245, 166, 35, 0.48);
  }
  .btn-register-hero:active {
    transform: scale(0.98);
  }
  .btn-pdf-hero {
    border: 1.5px solid rgba(255, 255, 255, 0.65);
    background: rgba(255, 255, 255, 0.12);
    color: #FFFFFF;
    padding: 12px 22px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none;
  }
  .btn-pdf-hero:hover {
    background: rgba(255, 255, 255, 0.24);
    border-color: #FFFFFF;
    transform: translateY(-2px);
  }

  /* Dedicated Hero Showcase Carousel & Animation Selector Toolbar */
  .hero-showcase-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 560px;
    margin-right: auto;
  }
  .hero-anim-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(4, 46, 52, 0.55);
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 999px;
    padding: 4px 8px 4px 14px;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    gap: 8px;
  }
  .anim-toolbar-title {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    font-weight: 700;
    color: #A7F3D0;
    white-space: nowrap;
  }
  .anim-toolbar-pills {
    display: flex;
    align-items: center;
    gap: 5px;
  }
  .anim-pill {
    background: transparent;
    border: none;
    color: #E2E8F0;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 999px;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .anim-pill:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #FFFFFF;
  }
  .anim-pill.is-active {
    background: #F5A623;
    color: #042E34;
    font-weight: 800;
    box-shadow: 0 2px 8px rgba(245, 166, 35, 0.45);
  }

  .hero-showcase-wrapper {
    width: 100%;
    height: 420px;
    background: #042e34;
    border: 2px solid var(--event-primary);
    border-radius: var(--event-radius-lg);
    position: relative;
    overflow: hidden;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.28);
    user-select: none;
  }
  .hero-slider-track {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
  }
  .hero-slide {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 32px;
    background-size: cover;
    background-position: center;
  }
  .hero-slide-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(4, 46, 52, 0.15) 0%, rgba(4, 46, 52, 0.85) 65%, #042e34 100%);
    z-index: 1;
  }
  .hero-slide-body {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
  }
  .hero-slide-badge {
    background: #FFFFFF;
    color: var(--event-teal-dark);
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 800;
  }
  .hero-slide-title {
    font-size: 22px;
    font-weight: 800;
    color: #FFFFFF;
    line-height: 1.35;
    margin: 0;
  }
  .hero-slide-desc {
    font-size: 13.5px;
    line-height: 1.7;
    color: #E2E8F0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
  }
  .hero-slide-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    background: linear-gradient(135deg, #F5A623 0%, #E0921B 100%);
    color: #FFFFFF;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 800;
    margin-top: 4px;
    box-shadow: 0 4px 14px rgba(245, 166, 35, 0.35);
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .hero-slide-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(245, 166, 35, 0.45);
  }

  /* -------------------------------------------------------------
     ANIMATION MODEL 1: Cinematic 3D Parallax & Depth Drift
     ------------------------------------------------------------- */
  .hero-showcase-wrapper.anim-cinematic {
    perspective: 1200px;
  }
  .hero-showcase-wrapper.anim-cinematic .hero-slide {
    transform-style: preserve-3d;
    opacity: 0;
    visibility: hidden;
    filter: blur(4px);
    transform: translate3d(0, 0, -90px) rotateY(-8deg) scale(0.93);
    transition: opacity 0.75s cubic-bezier(0.2, 0.8, 0.2, 1), 
                transform 0.85s cubic-bezier(0.2, 0.8, 0.2, 1), 
                filter 0.75s ease;
  }
  .hero-showcase-wrapper.anim-cinematic .hero-slide.is-active {
    opacity: 1;
    visibility: visible;
    filter: blur(0);
    transform: translate3d(0, 0, 0) rotateY(0deg) scale(1);
    z-index: 2;
  }
  .hero-showcase-wrapper.anim-cinematic .hero-slide-body > * {
    transform: translateY(22px) translateZ(40px);
    opacity: 0;
    transition: transform 0.65s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.65s ease;
  }
  .hero-showcase-wrapper.anim-cinematic .hero-slide.is-active .hero-slide-body > * {
    transform: translateY(0) translateZ(0);
    opacity: 1;
  }
  .hero-showcase-wrapper.anim-cinematic .hero-slide.is-active .hero-slide-badge { transition-delay: 0.15s; }
  .hero-showcase-wrapper.anim-cinematic .hero-slide.is-active .hero-slide-title { transition-delay: 0.25s; }
  .hero-showcase-wrapper.anim-cinematic .hero-slide.is-active .hero-slide-desc { transition-delay: 0.35s; }
  .hero-showcase-wrapper.anim-cinematic .hero-slide.is-active .hero-slide-link { transition-delay: 0.45s; }

  /* -------------------------------------------------------------
     ANIMATION MODEL 2: Liquid Curtain & Dynamic Lens Zoom
     ------------------------------------------------------------- */
  .hero-showcase-wrapper.anim-curtain .hero-slide {
    opacity: 1;
    visibility: visible;
    clip-path: polygon(0 0, 0 0, 0 100%, 0 100%);
    transform: scale(1.1);
    transition: clip-path 0.9s cubic-bezier(0.65, 0, 0.35, 1), 
                transform 1.3s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 1;
  }
  .hero-showcase-wrapper.anim-curtain .hero-slide.is-active {
    clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
    transform: scale(1);
    z-index: 2;
  }
  .hero-showcase-wrapper.anim-curtain .hero-slide-body {
    transform: translateX(-24px);
    opacity: 0;
    transition: transform 0.75s cubic-bezier(0.16, 1, 0.3, 1) 0.25s, opacity 0.75s ease 0.25s;
  }
  .hero-showcase-wrapper.anim-curtain .hero-slide.is-active .hero-slide-body {
    transform: translateX(0);
    opacity: 1;
  }

  /* -------------------------------------------------------------
     ANIMATION MODEL 3: Elastic Tactile Card Stack & Toss
     ------------------------------------------------------------- */
  .hero-showcase-wrapper.anim-cardstack {
    overflow: visible;
  }
  .hero-showcase-wrapper.anim-cardstack::before {
    content: '';
    position: absolute;
    inset: -8px -8px -14px -8px;
    background: rgba(10, 92, 102, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: calc(var(--event-radius-lg) + 6px);
    transform: rotate(-1.5deg) scale(0.975);
    z-index: 0;
    pointer-events: none;
    transition: transform 0.4s ease;
  }
  .hero-showcase-wrapper.anim-cardstack .hero-slider-track {
    border-radius: var(--event-radius-lg);
    background: #042e34;
    position: relative;
    z-index: 1;
  }
  .hero-showcase-wrapper.anim-cardstack .hero-slide {
    opacity: 0;
    visibility: hidden;
    transform: translateX(110%) rotate(5deg) scale(0.92);
    transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.5s ease;
  }
  .hero-showcase-wrapper.anim-cardstack .hero-slide.is-active {
    opacity: 1;
    visibility: visible;
    transform: translateX(0) rotate(0deg) scale(1);
    z-index: 2;
  }
  .hero-showcase-wrapper.anim-cardstack .hero-slide.is-prev {
    opacity: 0;
    transform: translateX(-110%) rotate(-5deg) scale(0.92);
  }

  .hero-slider-nav {
    position: absolute;
    top: 18px;
    left: 18px;
    z-index: 10;
    display: flex;
    gap: 8px;
  }
  .hero-slider-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s ease;
  }
  .hero-slider-btn:hover {
    background: rgba(255, 255, 255, 0.45);
  }
  .hero-slider-dots {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
    display: flex;
    gap: 6px;
  }
  .hero-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.4);
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .hero-dot.is-active {
    width: 22px;
    border-radius: 999px;
    background: var(--event-amber);
  }

  /* ============================================================================
     SECTIONS & TYPOGRAPHY
     ============================================================================ */
  .event-section {
    padding: 72px 0;
  }
  .event-section-light {
    background: var(--event-bg-light);
  }
  .event-section-white {
    background: #FFFFFF;
  }
  .section-header {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 36px;
  }
  .section-kicker {
    font-size: 13px;
    font-weight: 800;
    color: var(--event-primary);
    letter-spacing: 0.5px;
  }
  .section-title {
    font-size: clamp(22px, 2.5vw, 32px);
    font-weight: 800;
    color: var(--event-teal-dark);
    line-height: 1.3;
    margin: 0;
  }
  .section-subtitle {
    font-size: 15px;
    color: var(--event-text-muted);
    max-width: 760px;
    line-height: 1.7;
    margin: 0;
  }

  /* ============================================================================
     SECTION 1: معرفی و محورهای همایش (About Trio Layout)
     Layout: Right = Poster | Center = Title & Copy | Left = 2 Action Spotlight Cards
     ============================================================================ */
  .about-trio-layout {
    display: grid;
    grid-template-columns: 320px minmax(0, 1.25fr) 340px;
    gap: 30px;
    align-items: stretch;
  }

  /* 1. Right Column: Poster Card */
  .about-poster-col {
    display: flex;
    flex-direction: column;
  }
  .event-poster-card {
    background: #FFFFFF;
    border: 1.5px solid rgba(13, 122, 135, 0.22);
    border-radius: var(--event-radius);
    padding: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    text-align: center;
    box-shadow: 0 8px 24px rgba(10, 92, 102, 0.07);
    height: 100%;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .event-poster-card:hover {
    box-shadow: 0 14px 32px rgba(10, 92, 102, 0.12);
  }
  .poster-card-top-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 999px;
    background: var(--event-primary-light);
    color: var(--event-teal-dark);
    font-size: 12px;
    font-weight: 800;
    align-self: center;
  }
  .event-poster-media {
    position: relative;
    width: 100%;
    flex: 1;
    min-height: 380px;
    cursor: pointer;
    border-radius: 12px;
    overflow: hidden;
    background: #F8FAFB;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .event-poster-img {
    width: 100%;
    height: 100%;
    max-height: 480px;
    object-fit: contain;
    border-radius: 12px;
    display: block;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .event-poster-media:hover .event-poster-img {
    transform: scale(1.03);
  }
  .poster-zoom-badge {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(10, 92, 102, 0.92);
    color: #FFFFFF;
    border-radius: 999px;
    padding: 6px 14px;
    font-size: 11.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    pointer-events: none;
    white-space: nowrap;
    transition: background 0.2s ease;
  }
  .event-poster-media:hover .poster-zoom-badge {
    background: #0A5C66;
  }
  .poster-empty-state {
    width: 100%;
    min-height: 360px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: var(--event-teal-dark);
    font-weight: 700;
    font-size: 14px;
    border: 2px dashed rgba(13, 122, 135, 0.35);
    border-radius: 12px;
    background: var(--event-primary-light);
  }

  /* 2. Middle Column: Title & Description */
  .about-content-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  .about-content-header {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .about-content-title {
    font-size: clamp(22px, 2.2vw, 30px);
    font-weight: 900;
    color: var(--event-teal-dark);
    line-height: 1.35;
    margin: 0;
  }
  .about-content-subtitle {
    font-size: 14.5px;
    color: var(--event-text-muted);
    line-height: 1.6;
    margin: 0;
  }
  .about-copy-box {
    font-size: 15.5px;
    line-height: 1.9;
    color: var(--event-text-main);
    white-space: pre-line;
    background: #FFFFFF;
    border: 1px solid var(--event-border);
    border-radius: var(--event-radius);
    padding: 28px;
    box-shadow: var(--event-shadow);
    flex: 1;
  }

  /* 3. Left Column: 2 Distinct Spotlight Action Cards */
  .about-actions-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  /* Base Spotlight Card */
  .action-card {
    background: #FFFFFF;
    border-radius: var(--event-radius);
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    box-shadow: 0 6px 20px rgba(10, 92, 102, 0.08);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    position: relative;
    overflow: hidden;
  }
  .action-card:hover {
    transform: translateY(-3px);
  }
  .action-card-badge-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }
  .badge-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 800;
    background: #FEF3C7;
    color: #92400E;
  }
  .badge-pdf-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 800;
    background: #E6F4F5;
    color: var(--event-teal-dark);
  }
  .action-card-tag {
    font-size: 11px;
    font-weight: 700;
    color: #9CA3AF;
  }
  .action-card-tag.action-tag-teal {
    color: var(--event-primary);
  }
  .action-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .action-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .action-icon-amber {
    background: #FFFBEB;
    color: #F5A623;
    border: 1px solid #FDE68A;
  }
  .action-icon-teal {
    background: var(--event-primary-light);
    color: var(--event-primary);
    border: 1px solid #CCFBF1;
  }
  .action-card-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--event-teal-dark);
    margin: 0 0 2px;
  }
  .action-card-desc {
    font-size: 12px;
    color: var(--event-text-muted);
    margin: 0;
    line-height: 1.5;
  }

  /* Card 1 Specific: Registration */
  .action-card-register {
    border: 1.5px solid rgba(245, 166, 35, 0.45);
    background: linear-gradient(180deg, #FFFDF8 0%, #FFFFFF 100%);
  }
  .action-card-register:hover {
    box-shadow: 0 12px 28px rgba(245, 166, 35, 0.18);
    border-color: #F5A623;
  }
  .action-card-features {
    list-style: none;
    margin: 2px 0 4px;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .action-card-features li {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: var(--event-text-main);
    line-height: 1.4;
  }
  .btn-action-primary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 13px 18px;
    background: linear-gradient(135deg, #F5A623 0%, #E0921B 100%);
    color: #FFFFFF;
    border: none;
    border-radius: 12px;
    font-size: 14.5px;
    font-weight: 800;
    text-decoration: none;
    box-shadow: 0 6px 18px rgba(245, 166, 35, 0.35);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .btn-action-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(245, 166, 35, 0.48);
  }
  .btn-action-primary.is-disabled {
    background: #E5E7EB;
    color: #9CA3AF;
    box-shadow: none;
    cursor: not-allowed;
    justify-content: center;
  }

  /* Card 2 Specific: PDF Schedule */
  .action-card-pdf {
    border: 1.5px solid rgba(13, 122, 135, 0.25);
    background: linear-gradient(180deg, #F8FAFB 0%, #FFFFFF 100%);
  }
  .action-card-pdf:hover {
    box-shadow: 0 12px 28px rgba(10, 92, 102, 0.14);
    border-color: var(--event-primary);
  }
  .action-pdf-lead {
    font-size: 12.5px;
    color: var(--event-text-muted);
    line-height: 1.6;
    margin: 0;
  }
  .btn-action-secondary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 12px 18px;
    background: linear-gradient(135deg, #0D7A87 0%, #0A5C66 100%);
    color: #FFFFFF;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 800;
    text-decoration: none;
    box-shadow: 0 6px 18px rgba(10, 92, 102, 0.22);
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .btn-action-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(10, 92, 102, 0.32);
  }
  .pdf-not-available {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 12px;
    color: var(--event-text-light);
    text-align: center;
    padding: 10px;
    background: #F3F4F6;
    border-radius: 8px;
  }

  /* ============================================================================
     SECTION 2: دبیران علمی و اجرایی (#sec-organizers)
     ============================================================================ */
  .people-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
  }
  .person-card {
    background: #FFFFFF;
    border: 1px solid var(--event-border);
    border-radius: var(--event-radius);
    overflow: hidden;
    box-shadow: var(--event-shadow);
    display: flex;
    flex-direction: column;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .person-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--event-shadow-hover);
  }
  .person-card-media {
    width: 100%;
    height: 240px;
    background: var(--event-primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }
  .person-card-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .person-card-body {
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .person-role-badge {
    display: inline-flex;
    align-self: flex-start;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 800;
    background: var(--event-primary-light);
    color: var(--event-primary);
    margin-bottom: 4px;
  }
  .person-role-badge.is-executive {
    background: #EFF6FF;
    color: #2563EB;
  }
  .person-name {
    font-size: 17px;
    font-weight: 800;
    color: var(--event-teal-dark);
    margin: 0;
  }
  .person-title {
    font-size: 13px;
    color: var(--event-text-muted);
    line-height: 1.6;
    margin: 0;
  }

  /* ============================================================================
     SECTION 3: سخنرانان برجسته همایش (#sec-speakers)
     ============================================================================ */
  .speaker-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 20px;
  }
  .speaker-card {
    background: #FFFFFF;
    border: 1px solid var(--event-border);
    border-radius: var(--event-radius);
    padding: 24px 18px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    box-shadow: var(--event-shadow);
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .speaker-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--event-shadow-hover);
  }
  .speaker-avatar {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: var(--event-primary-light);
    border: 2px solid var(--event-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }
  .speaker-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .speaker-name {
    font-size: 16px;
    font-weight: 800;
    color: var(--event-text-main);
    margin: 0;
  }
  .speaker-title {
    font-size: 12.5px;
    color: var(--event-text-light);
    line-height: 1.5;
    margin: 0;
  }

  /* ============================================================================
     SECTION 4: همراهان همایش (#sec-partners)
     ============================================================================ */
  .partners-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 20px;
  }
  .partner-card {
    background: var(--event-primary-light);
    border: 1px solid var(--event-primary);
    border-radius: var(--event-radius);
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    text-align: center;
    min-height: 140px;
    transition: transform 0.2s ease;
  }
  .partner-card:hover {
    transform: translateY(-2px);
  }
  .partner-logo-box {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    background: #FFFFFF;
    border: 1px solid var(--event-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 8px;
  }
  .partner-logo-box img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
  .partner-name {
    font-size: 14px;
    font-weight: 800;
    color: var(--event-teal-dark);
    line-height: 1.4;
    margin: 0;
  }

  /* ============================================================================
     SECTION 5: اخبار همایش (#sec-news)
     ============================================================================ */
  .news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
  }
  .news-card {
    background: #FFFFFF;
    border: 1px solid var(--event-border);
    border-radius: var(--event-radius);
    overflow: hidden;
    box-shadow: var(--event-shadow);
    display: flex;
    flex-direction: column;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .news-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--event-shadow-hover);
  }
  .news-card-media {
    width: 100%;
    height: 180px;
    background: var(--event-primary-light);
    position: relative;
    overflow: hidden;
  }
  .news-card-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .news-date-badge {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(10, 92, 102, 0.9);
    color: #FFFFFF;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 700;
    backdrop-filter: blur(4px);
  }
  .news-card-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1;
  }
  .news-card-title {
    font-size: 17px;
    font-weight: 800;
    color: var(--event-teal-dark);
    line-height: 1.4;
    margin: 0;
  }
  .news-card-desc {
    font-size: 13px;
    color: var(--event-text-muted);
    line-height: 1.7;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
    margin: 0;
  }
  .news-card-btn {
    align-self: flex-start;
    margin-top: 8px;
    padding: 8px 16px;
    background: var(--event-primary-light);
    color: var(--event-primary);
    border: 1px solid rgba(13, 122, 135, 0.28);
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
  }
  .news-card-btn:hover {
    background: var(--event-primary);
    color: #FFFFFF;
    border-color: var(--event-primary);
    transform: translateY(-1px);
  }

  /* ============================================================================
     POSTER LIGHTBOX MODAL
     ============================================================================ */
  .event-poster-lightbox {
    position: fixed;
    inset: 0;
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, visibility 0.25s ease;
  }
  .event-poster-lightbox.is-open {
    opacity: 1;
    visibility: visible;
  }
  .lightbox-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(4, 38, 43, 0.85);
    backdrop-filter: blur(8px);
  }
  .lightbox-dialog {
    position: relative;
    z-index: 2;
    max-width: 92vw;
    max-height: 90vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .lightbox-img {
    max-width: 100%;
    max-height: 84vh;
    object-fit: contain;
    border-radius: 14px;
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.5);
    background: #FFFFFF;
  }
  .lightbox-close {
    position: absolute;
    top: -46px;
    left: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    color: #FFFFFF;
    border: 1px solid rgba(255, 255, 255, 0.35);
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
  }

  /* ============================================================================
     MOBILE BOTTOM STICKY BAR
     ============================================================================ */
  .mobile-sticky-bar {
    display: none;
  }

  /* ============================================================================
     RESPONSIVE BREAKPOINTS (Tablets: <= 990px)
     ============================================================================ */
  @media (max-width: 1120px) {
    .about-trio-layout {
      grid-template-columns: 290px minmax(0, 1fr);
      gap: 24px;
    }
    .about-actions-col {
      grid-column: 1 / -1;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
  }

  /* ============================================================================
     RESPONSIVE BREAKPOINTS (Tablets: <= 990px)
     ============================================================================ */
  @media (max-width: 990px) {
    .event-hero-inner {
      grid-template-columns: 1fr;
      gap: 36px;
    }
    .hero-showcase-container {
      max-width: 100%;
    }
    .hero-showcase-wrapper {
      max-width: 100%;
      height: 380px;
    }
    .people-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    .speaker-grid {
      grid-template-columns: repeat(3, 1fr);
    }
    .partners-grid {
      grid-template-columns: repeat(4, 1fr);
    }
    .news-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  /* ============================================================================
     MOBILE ARCHITECTURE (Smartphones: <= 768px)
     ============================================================================ */
  @media (max-width: 768px) {
    .event-container {
      padding: 0 16px;
    }
    .event-section {
      padding: 44px 0;
    }
    .section-header {
      margin-bottom: 24px;
      gap: 4px;
    }
    .section-title {
      font-size: 22px;
    }
    .section-subtitle {
      font-size: 13.5px;
      line-height: 1.65;
    }

    /* Hero Mobile Overhaul */
    .event-hero {
      padding: 32px 0 40px;
    }
    .event-hero-content {
      gap: 16px;
    }
    .event-hero-title {
      font-size: clamp(21px, 6.2vw, 28px);
      line-height: 1.35;
    }
    .event-hero-desc {
      font-size: 14px;
      line-height: 1.75;
    }

    /* Meta Chips Bar on Mobile */
    .event-hero-chips {
      gap: 8px;
    }
    .event-hero-chip {
      font-size: 11.5px;
      padding: 6px 11px;
      border-radius: 999px;
    }

    /* Countdown Grid: Strict 4-Col Fluid Grid */
    .event-countdown-grid {
      grid-template-columns: repeat(4, 1fr);
      gap: 8px;
      width: 100%;
      max-width: 400px;
    }
    .countdown-box {
      width: auto;
      padding: 8px 4px;
      border-radius: 10px;
    }
    .countdown-val {
      font-size: 20px;
    }
    .countdown-lbl {
      font-size: 10px;
    }

    /* Hero Actions Mobile */
    .event-hero-actions {
      flex-direction: column;
      width: 100%;
      gap: 10px;
    }
    .btn-register-hero,
    .btn-pdf-hero {
      width: 100%;
      justify-content: center;
      padding: 13px 18px;
      font-size: 14px;
      border-radius: 10px;
    }

    /* Hero Showcase Frame Mobile */
    .hero-showcase-container {
      max-width: 100%;
    }
    .hero-anim-toolbar {
      overflow-x: auto;
      border-radius: 12px;
      padding: 6px 10px;
      gap: 6px;
    }
    .anim-toolbar-title {
      font-size: 10.5px;
    }
    .anim-pill {
      font-size: 10.5px;
      padding: 3px 8px;
      white-space: nowrap;
    }
    .hero-showcase-wrapper {
      height: auto;
      aspect-ratio: 16 / 10;
      min-height: 240px;
      max-height: 300px;
      border-radius: 16px;
      touch-action: pan-y;
    }
    .hero-slide {
      padding: 16px 14px 20px;
    }
    .hero-slide-body {
      gap: 8px;
    }
    .hero-slide-badge {
      font-size: 10.5px;
      padding: 2px 8px;
    }
    .hero-slide-title {
      font-size: 16px;
      line-height: 1.35;
    }
    .hero-slide-desc {
      font-size: 12px;
      line-height: 1.5;
      -webkit-line-clamp: 2;
    }
    .hero-slide-link {
      font-size: 11.5px;
      padding: 6px 12px;
    }
    .hero-slider-nav {
      top: 10px;
      left: 10px;
      gap: 6px;
    }
    .hero-slider-btn {
      width: 28px;
      height: 28px;
      font-size: 11px;
    }
    .hero-slider-dots {
      bottom: 8px;
    }

    /* About Section Mobile */
    .about-trio-layout {
      grid-template-columns: 1fr;
      gap: 20px;
    }
    .about-actions-col {
      grid-column: auto;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .about-copy-box {
      padding: 18px 14px;
      font-size: 14.5px;
      line-height: 1.85;
    }
    .event-poster-card {
      padding: 12px;
    }
    .event-poster-media {
      min-height: 260px;
    }
    .event-poster-img {
      max-height: 320px;
    }
    .action-card {
      padding: 18px 14px;
      gap: 12px;
    }
    .btn-action-primary,
    .btn-action-secondary {
      padding: 12px 16px;
      font-size: 14px;
    }

    /* Section 2: Organizers Horizontal Cards on Mobile */
    .people-grid {
      grid-template-columns: 1fr;
      gap: 12px;
    }
    .person-card {
      flex-direction: row;
      align-items: center;
      padding: 12px 14px;
      gap: 14px;
      border-radius: 16px;
    }
    .person-card-media {
      width: 72px;
      height: 72px;
      border-radius: 12px;
      flex-shrink: 0;
    }
    .person-card-body {
      padding: 0;
      gap: 3px;
      min-width: 0;
    }
    .person-name {
      font-size: 14.5px;
    }
    .person-title {
      font-size: 11.5px;
      line-height: 1.45;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    /* Section 3: Speakers 2-Column Mobile Grid */
    .speaker-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
    }
    .speaker-card {
      padding: 14px 8px;
      gap: 8px;
      border-radius: 14px;
    }
    .speaker-avatar {
      width: 58px;
      height: 58px;
    }
    .speaker-name {
      font-size: 13px;
    }
    .speaker-title {
      font-size: 11px;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    /* Section 4: Partners 3-Column Mobile Grid */
    .partners-grid {
      grid-template-columns: repeat(3, 1fr);
      gap: 8px;
    }
    .partner-card {
      padding: 12px 4px;
      min-height: 100px;
      gap: 6px;
      border-radius: 12px;
    }
    .partner-logo-box {
      width: 44px;
      height: 44px;
      padding: 4px;
    }
    .partner-name {
      font-size: 10.5px;
      line-height: 1.35;
    }

    /* Section 5: News 1-Column Mobile */
    .news-grid {
      grid-template-columns: 1fr;
      gap: 14px;
    }
    .news-card-media {
      height: 150px;
    }
    .news-card-body {
      padding: 14px;
      gap: 6px;
    }
    .news-card-title {
      font-size: 15px;
    }
    .news-card-desc {
      font-size: 12px;
      -webkit-line-clamp: 2;
    }
    .news-card-btn {
      width: 100%;
      justify-content: center;
      padding: 9px 14px;
    }

    /* Mobile Floating Sticky CTA */
    .mobile-sticky-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      z-index: 9999;
      background: rgba(10, 92, 102, 0.94);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-top: 1px solid rgba(255, 255, 255, 0.18);
      padding: 10px 16px calc(10px + env(safe-area-inset-bottom, 0px));
      box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.2);
      transform: translateY(110%);
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .mobile-sticky-bar.is-visible {
      transform: translateY(0);
    }
    .mobile-sticky-meta {
      display: flex;
      flex-direction: column;
      gap: 2px;
      max-width: 58%;
    }
    .mobile-sticky-title {
      font-size: 12.5px;
      font-weight: 800;
      color: #FFFFFF;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .mobile-sticky-date {
      font-size: 11px;
      color: #FDE68A;
      font-weight: 600;
    }
    .mobile-sticky-btn {
      background: linear-gradient(135deg, #F5A623 0%, #E0921B 100%);
      color: #FFFFFF;
      font-size: 13px;
      font-weight: 800;
      padding: 10px 18px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      text-decoration: none;
      box-shadow: 0 4px 14px rgba(245, 166, 35, 0.4);
      flex-shrink: 0;
    }
  }

  @media (max-width: 360px) {
    .partners-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    .countdown-val {
      font-size: 18px;
    }
    .countdown-lbl {
      font-size: 9.5px;
    }
  }
</style>

<main class="event-public-page">

  <!-- ==========================================================================
       1. HERO SECTION
       ========================================================================== -->
  <section class="event-hero">
    <div class="event-container">
      <div class="event-hero-inner">
        
        <!-- Content Column: Event Info, Countdown & CTAs -->
        <div class="event-hero-content">
          <h1 class="event-hero-title"><?= event_h($event['title']) ?></h1>

          <p class="event-hero-desc">
            <?= event_h($event['short_description'] ?: 'همایشی ملی برای همگرایی دانش نوین و ارتقای کیفیت زندگی بیماران مبتلا به سرطان.') ?>
          </p>

          <?php
            $startTime = !empty($event['start_time']) ? substr((string)$event['start_time'], 0, 5) : '۰۸:۰۰';
            $endTime = !empty($event['end_time']) ? substr((string)$event['end_time'], 0, 5) : '';
            $timeText = ($endTime !== '' && $endTime !== $startTime) 
              ? "ساعت {$startTime} تا {$endTime}" 
              : "ساعت {$startTime}";
          ?>

          <!-- Quick Meta Chips Bar (Ordered: Date -> Time -> Active Registration) -->
          <div class="event-hero-chips">
            <div class="event-hero-chip">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
              <span><?= event_h(event_date_label($event['event_date'])) ?></span>
            </div>
            <div class="event-hero-chip">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              <span><?= event_h($timeText) ?></span>
            </div>
            <div class="event-hero-chip event-hero-chip-status">
              <span class="chip-pulse-dot"></span>
              <span><?= !empty($event['registration_url']) ? 'ثبت‌نام فعال' : 'ثبت‌نام به‌زودی' ?></span>
            </div>
          </div>

          <!-- Countdown Timer -->
          <div class="event-countdown-grid" data-countdown="<?= event_h($iso) ?>">
            <div class="countdown-box">
              <span class="countdown-val" data-unit="days">۰</span>
              <span class="countdown-lbl">روز</span>
            </div>
            <div class="countdown-box">
              <span class="countdown-val" data-unit="hours">۰</span>
              <span class="countdown-lbl">ساعت</span>
            </div>
            <div class="countdown-box">
              <span class="countdown-val" data-unit="minutes">۰</span>
              <span class="countdown-lbl">دقیقه</span>
            </div>
            <div class="countdown-box">
              <span class="countdown-val" data-unit="seconds">۰</span>
              <span class="countdown-lbl">ثانیه</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="event-hero-actions">
            <?php if (!empty($event['registration_url'])): ?>
              <a href="<?= event_h($event['registration_url']) ?>" target="_blank" rel="noopener" class="btn-register-hero">
                <span>ثبت‌نام سریع در همایش</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </a>
            <?php else: ?>
              <a href="#sec-about" class="btn-register-hero">
                <span>مشاهده جزئیات همایش</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
              </a>
            <?php endif; ?>

            <?php if (!empty($event['schedule_pdf'])): ?>
              <a href="<?= event_h($event['schedule_pdf']) ?>" target="_blank" download class="btn-pdf-hero">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>برنامه زمان‌بندی (PDF)</span>
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Showcase Column: Dedicated Hero Carousel with 3 Animation Models -->
        <div class="hero-showcase-container">
          <!-- Animation Model Switcher Toolbar -->
          <div class="hero-anim-toolbar" id="heroAnimToolbar" title="تغییر زنده مدل انیمیشن اسلایدر">
            <span class="anim-toolbar-title">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
              حالت انیمیشن:
            </span>
            <div class="anim-toolbar-pills">
              <button type="button" class="anim-pill is-active" data-model="anim-cinematic">۱. سینمایی ۳D</button>
              <button type="button" class="anim-pill" data-model="anim-curtain">۲. پرده‌ای مایع</button>
              <button type="button" class="anim-pill" data-model="anim-cardstack">۳. کارت شناور</button>
            </div>
          </div>

          <div class="hero-showcase-wrapper anim-cinematic" id="heroShowcase">
            <div class="hero-slider-track" id="heroSliderTrack">
              <?php if (!empty($heroes)): ?>
                <?php foreach ($heroes as $idx => $h): ?>
                  <?php
                    $hBtnLabel = trim((string)($h['button_label'] ?? ''));
                    $hBtnLink = trim((string)($h['button_link'] ?? ($h['link'] ?? '')));
                    $hBgStyle = !empty($h['image']) 
                      ? "background-image: url('" . event_h($h['image']) . "'); background-size: cover; background-position: center;" 
                      : "background: linear-gradient(135deg, #0d7a87 0%, #064047 100%);";
                  ?>
                  <div class="hero-slide <?= $idx === 0 ? 'is-active' : '' ?>" data-slide-index="<?= $idx ?>" style="<?= $hBgStyle ?>">
                    <div class="hero-slide-overlay"></div>
                    <div class="hero-slide-body">
                      <span class="hero-slide-badge">بخش ویژه رویداد <?= count($heroes) > 1 ? '(' . ($idx + 1) . ' از ' . count($heroes) . ')' : '' ?></span>
                      <h3 class="hero-slide-title"><?= event_h($h['title']) ?></h3>
                      <?php if (!empty($h['description'])): ?>
                        <p class="hero-slide-desc"><?= event_h($h['description']) ?></p>
                      <?php endif; ?>
                      <?php if ($hBtnLabel !== ''): ?>
                        <a href="<?= event_h($hBtnLink ?: '#sec-about') ?>" <?= ($hBtnLink !== '' && str_starts_with($hBtnLink, 'http')) ? 'target="_blank" rel="noopener"' : '' ?> class="hero-slide-link">
                          <span><?= event_h($hBtnLabel) ?></span>
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php elseif (!empty($event['poster'])): ?>
                <div class="hero-slide is-active" style="background-image: url('<?= event_h($event['poster']) ?>'); background-size: cover; background-position: center;">
                  <div class="hero-slide-overlay"></div>
                  <div class="hero-slide-body">
                    <span class="hero-slide-badge">پوستر رسمی همایش</span>
                    <h3 class="hero-slide-title"><?= event_h($event['title']) ?></h3>
                    <p class="hero-slide-desc"><?= event_h($event['short_description']) ?></p>
                  </div>
                </div>
              <?php else: ?>
                <div class="hero-slide is-active" style="background: linear-gradient(135deg, #0d7a87 0%, #064047 100%);">
                  <div class="hero-slide-overlay"></div>
                  <div class="hero-slide-body">
                    <span class="hero-slide-badge">رویداد ملی</span>
                    <h3 class="hero-slide-title"><?= event_h($event['title']) ?></h3>
                    <p class="hero-slide-desc"><?= event_h($event['short_description']) ?></p>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <?php if (count($heroes) > 1): ?>
              <div class="hero-slider-nav">
                <button type="button" class="hero-slider-btn" id="heroPrevBtn" aria-label="اسلاید قبلی">❮</button>
                <button type="button" class="hero-slider-btn" id="heroNextBtn" aria-label="اسلاید بعدی">❯</button>
              </div>
              <div class="hero-slider-dots" id="heroDots">
                <?php foreach ($heroes as $idx => $h): ?>
                  <span class="hero-dot <?= $idx === 0 ? 'is-active' : '' ?>" data-dot-index="<?= $idx ?>"></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ==========================================================================
       2. SECTION 1: معرفی و محورهای همایش (About Trio Layout)
       Right: Poster Card | Center: Title & Description | Left: 2 Spotlight Cards
       ========================================================================== -->
  <section class="event-section event-section-white" id="sec-about">
    <div class="event-container">
      
      <div class="about-trio-layout">

        <!-- 1. RIGHT: Official Poster Card (پوستر رسمی همایش) -->
        <div class="about-poster-col">
          <div class="event-poster-card">
            <div class="poster-card-top-badge">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <span>پوستر رسمی رویداد</span>
            </div>
            <?php if (!empty($event['poster'])): ?>
              <div class="event-poster-media" id="posterMediaTrigger" title="کلیک برای بزرگ‌نمایی تصویر پوستر">
                <img src="<?= event_h($event['poster']) ?>" alt="پوستر <?= event_h($event['title']) ?>" class="event-poster-img">
                <span class="poster-zoom-badge">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                  <span>مشاهده پوستر با اندازه کامل</span>
                </span>
              </div>
            <?php else: ?>
              <div class="poster-empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span>پوستر رسمی این رویداد</span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- 2. CENTER: Title, Kicker & Full Description (تیتر و توضیحات همایش کنار پوستر) -->
        <div class="about-content-col">
          <div class="about-content-header">
            <span class="section-kicker">معرفی و محورهای همایش</span>
            <h2 class="about-content-title"><?= event_h($event['title']) ?></h2>
            <p class="about-content-subtitle">اطلاعات تفصیلی، اهداف برگزاری و محورهای علمی همایش</p>
          </div>
          <div class="about-copy-box">
            <?= event_h($event['about'] ?: $event['short_description']) ?>
          </div>
        </div>

        <!-- 3. LEFT: Two Distinct Spotlight Cards (دکمه ثبت‌نام و دانلود پی‌دی‌اف مجزا) -->
        <div class="about-actions-col">
          
          <!-- Card A: Registration Spotlight Card -->
          <div class="action-card action-card-register">
            <div class="action-card-badge-row">
              <span class="badge-status-pill">
                <span class="chip-pulse-dot"></span>
                <span>ثبت‌نام آنلاین</span>
              </span>
              <span class="action-card-tag">ظرفیت محدود</span>
            </div>

            <div class="action-card-header">
              <div class="action-icon-wrap action-icon-amber">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
              </div>
              <div>
                <h3 class="action-card-title">ثبت‌نام در این همایش</h3>
                <p class="action-card-desc">رزرو جایگاه و دریافت شناسه اختصاصی</p>
              </div>
            </div>

            <ul class="action-card-features">
              <li>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#F5A623" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>حضور در سخنرانی‌ها و پنل‌های تخصصی</span>
              </li>
              <li>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#F5A623" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>صدور گواهی رسمی حضور در همایش</span>
              </li>
              <li>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#F5A623" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>دسترسی به پکیج خلاصه مقالات</span>
              </li>
            </ul>

            <?php if (!empty($event['registration_url'])): ?>
              <a href="<?= event_h($event['registration_url']) ?>" target="_blank" rel="noopener" class="btn-action-primary">
                <span>ثبت‌نام در این همایش</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </a>
            <?php else: ?>
              <button type="button" class="btn-action-primary is-disabled" disabled>
                <span>ثبت‌نام به‌زودی فعال می‌شود</span>
              </button>
            <?php endif; ?>
          </div>

          <!-- Card B: Schedule PDF Spotlight Card -->
          <div class="action-card action-card-pdf">
            <div class="action-card-badge-row">
              <span class="badge-pdf-pill">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>سند رویداد (PDF)</span>
              </span>
              <span class="action-card-tag action-tag-teal">نسخه رسمی</span>
            </div>

            <div class="action-card-header">
              <div class="action-icon-wrap action-icon-teal">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              </div>
              <div>
                <h3 class="action-card-title">سین همایش و زمان‌بندی</h3>
                <p class="action-card-desc">جدول دقیق ساعت سخنرانی‌ها و پنل‌ها</p>
              </div>
            </div>

            <p class="action-pdf-lead">
              جدول زمان‌بندی کامل برنامه‌ها شامل اسامی سخنرانان، کارگاه‌ها و نشست‌های تخصصی در قالب فایل PDF قابل دریافت است.
            </p>

            <?php if (!empty($event['schedule_pdf'])): ?>
              <a href="<?= event_h($event['schedule_pdf']) ?>" target="_blank" download class="btn-action-secondary">
                <span>دریافت فایل برنامه (PDF)</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              </a>
            <?php else: ?>
              <div class="pdf-not-available">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>فایل زمان‌بندی برنامه به‌زودی بارگذاری می‌شود.</span>
              </div>
            <?php endif; ?>
          </div>

        </div>

      </div>

    </div>
  </section>

  <!-- ==========================================================================
       3. SECTION 2: دبیران علمی و اجرایی
       ========================================================================== -->
  <?php if (!empty($people)): ?>
    <section class="event-section event-section-light" id="sec-organizers">
      <div class="event-container">
        
        <div class="section-header">
          <span class="section-kicker">ارکان علمی و اجرایی</span>
          <h2 class="section-title">دبیران همایش</h2>
          <p class="section-subtitle">اطلاعات دبیران علمی و اجرایی مسئول برگزاری همایش</p>
        </div>

        <div class="people-grid">
          <?php foreach ($people as $p): ?>
            <article class="person-card">
              <div class="person-card-media">
                <?php if (!empty($p['image'])): ?>
                  <img src="<?= event_h($p['image']) ?>" alt="<?= event_h($p['name']) ?>">
                <?php else: ?>
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--event-primary)" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <?php endif; ?>
              </div>
              <div class="person-card-body">
                <span class="person-role-badge <?= ($p['role'] ?? '') === 'executive_secretary' ? 'is-executive' : '' ?>">
                  <?= ($p['role'] ?? '') === 'scientific_secretary' ? 'دبیر علمی' : 'دبیر اجرایی' ?>
                </span>
                <h3 class="person-name"><?= event_h($p['name']) ?></h3>
                <div class="person-title"><?= event_h($p['title']) ?></div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

      </div>
    </section>
  <?php endif; ?>

  <!-- ==========================================================================
       4. SECTION 3: سخنرانان برجسته همایش
       ========================================================================== -->
  <?php if (!empty($speakers)): ?>
    <section class="event-section event-section-white" id="sec-speakers">
      <div class="event-container">
        
        <div class="section-header">
          <span class="section-kicker">سخنرانان و اساتید</span>
          <h2 class="section-title">سخنرانان برجسته همایش</h2>
          <p class="section-subtitle">اساتید و چهره‌های برجسته علمی و تخصصی حاضر در این رویداد</p>
        </div>

        <div class="speaker-grid">
          <?php foreach ($speakers as $s): ?>
            <article class="speaker-card">
              <div class="speaker-avatar">
                <?php if (!empty($s['image'])): ?>
                  <img src="<?= event_h($s['image']) ?>" alt="<?= event_h($s['name']) ?>">
                <?php else: ?>
                  <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--event-primary)" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <?php endif; ?>
              </div>
              <h3 class="speaker-name"><?= event_h($s['name']) ?></h3>
              <div class="speaker-title"><?= event_h($s['title']) ?></div>
            </article>
          <?php endforeach; ?>
        </div>

      </div>
    </section>
  <?php endif; ?>

  <!-- ==========================================================================
       5. SECTION 4: همراهان همایش
       ========================================================================== -->
  <?php if (!empty($partners)): ?>
    <section class="event-section event-section-light" id="sec-partners">
      <div class="event-container">
        
        <div class="section-header">
          <span class="section-kicker">همراهان و حامیان</span>
          <h2 class="section-title">همراهان همایش</h2>
          <p class="section-subtitle">شبکه‌ای از سازمان‌ها، مراکز علمی و حامیانی که در برگزاری این همایش مشارکت دارند.</p>
        </div>

        <div class="partners-grid">
          <?php foreach ($partners as $partner): ?>
            <div class="partner-card">
              <div class="partner-logo-box">
                <?php if (!empty($partner['logo'])): ?>
                  <img src="<?= event_h($partner['logo']) ?>" alt="<?= event_h($partner['name']) ?>">
                <?php else: ?>
                  <span style="font-weight:800; font-size:15px; color:var(--event-teal-dark);">مکسا</span>
                <?php endif; ?>
              </div>
              <h3 class="partner-name"><?= event_h($partner['name']) ?></h3>
            </div>
          <?php endforeach; ?>
        </div>

      </div>
    </section>
  <?php endif; ?>

  <!-- ==========================================================================
       6. SECTION 5: اخبار همایش
       ========================================================================== -->
  <section class="event-section event-section-white" id="sec-news">
    <div class="event-container">
      
      <div class="section-header">
        <span class="section-kicker">رویدادنامه</span>
        <h2 class="section-title">اخبار همایش</h2>
        <p class="section-subtitle">آخرین اخبار، اطلاعیه‌ها و جزئیات برنامه‌های همایش</p>
      </div>

      <?php if (!empty($news)): ?>
        <div class="news-grid">
          <?php foreach ($news as $n): ?>
            <?php
              $pubDate = !empty($n['published_at']) 
                ? event_date_label(substr((string)$n['published_at'], 0, 10)) 
                : (!empty($n['created_at']) ? event_date_label(substr((string)$n['created_at'], 0, 10)) : 'تازه');
            ?>
            <article class="news-card">
              <div class="news-card-media">
                <?php if (!empty($n['image'])): ?>
                  <img src="<?= event_h($n['image']) ?>" alt="<?= event_h($n['title']) ?>">
                <?php else: ?>
                  <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--event-primary-light); color:var(--event-primary); font-size:13px; font-weight:700;">خبر اختصاصی همایش</div>
                <?php endif; ?>
                <span class="news-date-badge"><?= event_h($pubDate) ?></span>
              </div>
              <div class="news-card-body">
                <h3 class="news-card-title"><?= event_h($n['title']) ?></h3>
                <p class="news-card-desc"><?= event_h($n['excerpt'] ?: mb_substr(strip_tags((string)($n['content'] ?? '')), 0, 110) . '...') ?></p>
                <a href="/event-news.php?slug=<?= rawurlencode((string)$n['slug']) ?>" class="news-card-btn">
                  <span>مشاهده خبر</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div style="padding: 40px 24px; text-align: center; background: var(--event-bg-light); border: 1px dashed var(--event-border); border-radius: var(--event-radius); color: var(--event-text-muted); font-size: 14px;">
          <p style="margin: 0; font-weight: 600;">در حال حاضر خبر جدیدی برای این رویداد منتشر نشده است. به زودی اطلاعیه‌های مربوط به این همایش در این بخش قرار می‌گیرد.</p>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- Mobile Floating Sticky CTA Bar -->
  <aside class="mobile-sticky-bar" id="mobileStickyBar" aria-label="دسترسی سریع به ثبت‌نام">
    <div class="mobile-sticky-meta">
      <span class="mobile-sticky-title"><?= event_h($event['title']) ?></span>
      <span class="mobile-sticky-date">🗓 <?= event_h(event_date_label($event['event_date'])) ?></span>
    </div>
    <?php if (!empty($event['registration_url'])): ?>
      <a href="<?= event_h($event['registration_url']) ?>" target="_blank" rel="noopener" class="mobile-sticky-btn">
        <span>ثبت‌نام سریع</span>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    <?php else: ?>
      <a href="#sec-about" class="mobile-sticky-btn">
        <span>جزئیات همایش</span>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
      </a>
    <?php endif; ?>
  </aside>

  <!-- Poster Lightbox Modal -->
  <?php if (!empty($event['poster'])): ?>
    <div class="event-poster-lightbox" id="posterLightbox" aria-hidden="true" role="dialog">
      <div class="lightbox-backdrop" id="lightboxBackdrop"></div>
      <div class="lightbox-dialog">
        <button type="button" class="lightbox-close" id="lightboxClose" aria-label="بستن پوستر">✕</button>
        <img src="<?= event_h($event['poster']) ?>" alt="پوستر کامل <?= event_h($event['title']) ?>" class="lightbox-img">
      </div>
    </div>
  <?php endif; ?>

</main>

<!-- Scripts: Countdown Timer, Carousel with Swipe, Sticky Bar & Lightbox -->
<script>
  (function() {
    // 1. Countdown Timer
    const root = document.querySelector('[data-countdown]');
    if (root) {
      const fa = n => String(Math.max(0, n)).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
      const tick = () => {
        const target = Date.parse(root.dataset.countdown);
        let left = Math.max(0, target - Date.now());
        let s = Math.floor(left / 1000);
        let d = Math.floor(s / 86400);
        s %= 86400;
        let h = Math.floor(s / 3600);
        s %= 3600;
        let m = Math.floor(s / 60);
        let sec = s % 60;
        [['days', d], ['hours', h], ['minutes', m], ['seconds', sec]].forEach(([u, v]) => {
          const el = root.querySelector('[data-unit="' + u + '"]');
          if (el) el.textContent = fa(String(v).padStart(2, '0'));
        });
      };
      tick();
      setInterval(tick, 1000);
    }

    // 2. Interactive Hero Carousel with 3 Animation Models & Gestures
    const showcase = document.getElementById('heroShowcase');
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    const btnPrev = document.getElementById('heroPrevBtn');
    const btnNext = document.getElementById('heroNextBtn');
    const animPills = document.querySelectorAll('#heroAnimToolbar .anim-pill');

    let activeIdx = 0;
    let timer = null;

    function goToSlide(idx) {
      if (slides.length <= 1) return;
      const prevIdx = activeIdx;
      activeIdx = (idx + slides.length) % slides.length;
      slides.forEach((s, i) => {
        s.classList.remove('is-active', 'is-prev');
        if (i === activeIdx) {
          s.classList.add('is-active');
        } else if (i === prevIdx) {
          s.classList.add('is-prev');
        }
      });
      dots.forEach((d, i) => d.classList.toggle('is-active', i === activeIdx));
    }

    function setHeroAnimModel(modelName, advanceSlide = false) {
      if (!showcase) return;
      showcase.classList.remove('anim-cinematic', 'anim-curtain', 'anim-cardstack');
      showcase.classList.add(modelName);
      try {
        localStorage.setItem('hero_anim_model', modelName);
      } catch (e) {}
      animPills.forEach(pill => {
        pill.classList.toggle('is-active', pill.dataset.model === modelName);
      });
      if (advanceSlide && slides.length > 1) {
        goToSlide(activeIdx + 1);
        startAuto();
      }
    }

    animPills.forEach(pill => {
      pill.addEventListener('click', e => {
        e.preventDefault();
        setHeroAnimModel(pill.dataset.model, true);
      });
    });

    let savedModel = 'anim-cinematic';
    try {
      savedModel = localStorage.getItem('hero_anim_model') || 'anim-cinematic';
    } catch (e) {}
    setHeroAnimModel(savedModel, false);

    function startAuto() {
      stopAuto();
      if (slides.length > 1) {
        timer = setInterval(() => goToSlide(activeIdx + 1), 5000);
      }
    }
    function stopAuto() {
      if (timer) clearInterval(timer);
    }

    if (slides.length > 1) {
      if (btnNext) btnNext.addEventListener('click', () => { goToSlide(activeIdx + 1); startAuto(); });
      if (btnPrev) btnPrev.addEventListener('click', () => { goToSlide(activeIdx - 1); startAuto(); });
      dots.forEach((d, i) => d.addEventListener('click', () => { goToSlide(i); startAuto(); }));

      if (showcase) {
        showcase.addEventListener('mouseenter', stopAuto);
        showcase.addEventListener('mouseleave', startAuto);

        // Touch Swipe
        let touchStartX = 0;
        let touchEndX = 0;
        showcase.addEventListener('touchstart', e => {
          touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        showcase.addEventListener('touchend', e => {
          touchEndX = e.changedTouches[0].screenX;
          const diff = touchEndX - touchStartX;
          if (diff < -35) {
            goToSlide(activeIdx + 1);
            startAuto();
          } else if (diff > 35) {
            goToSlide(activeIdx - 1);
            startAuto();
          }
        }, { passive: true });
      }
      startAuto();
    }

    // 3. Mobile Floating Sticky CTA Observer
    const stickyBar = document.getElementById('mobileStickyBar');
    if (stickyBar) {
      const onScroll = () => {
        if (window.scrollY > 340) {
          stickyBar.classList.add('is-visible');
        } else {
          stickyBar.classList.remove('is-visible');
        }
      };
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();
    }

    // 4. Poster Lightbox Modal
    const posterTrigger = document.getElementById('posterMediaTrigger');
    const lightbox = document.getElementById('posterLightbox');
    const lightboxClose = document.getElementById('lightboxClose');
    const lightboxBackdrop = document.getElementById('lightboxBackdrop');

    if (posterTrigger && lightbox) {
      const openLightbox = () => {
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      };
      const closeLightbox = () => {
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      };
      posterTrigger.addEventListener('click', openLightbox);
      if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
      if (lightboxBackdrop) lightboxBackdrop.addEventListener('click', closeLightbox);
      document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && lightbox.classList.contains('is-open')) closeLightbox();
      });
    }
  })();
</script>

<?php
if (!defined('IN_SNAPSHOT') && file_exists(__DIR__ . '/dashboard/components/footer/component.php')) {
    require __DIR__ . '/dashboard/components/footer/component.php';
} else {
    echo '</body></html>';
}
?>

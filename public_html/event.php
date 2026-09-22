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
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= event_h($event['title']) ?> | مکسا</title>
  <link rel="stylesheet" href="/font.css">
  <style>
    /* ============================================================================
       Figma Design #2:96 "public-event-detail" (1440px canvas layout)
       Design Tokens:
       - Deep Teal Hero Background: #0A5C66
       - Brand Primary Teal: #0D7A87 / #007B7A
       - Warm Amber Accent: #D97706 / #F59E0B
       - Soft Surfaces: #F0FDFA, #F8FAFB, #FFFFFF
       - Dark Text: #1F2937, #4B5563, #6B7280
       ============================================================================ */
    :root {
      --event-teal-dark: #0A5C66;
      --event-primary: #0D7A87;
      --event-primary-light: #F0FDFA;
      --event-amber: #D97706;
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

    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Vazirmatn', system-ui, -apple-system, sans-serif !important;
    }

    body {
      background: #FFFFFF;
      color: var(--event-text-main);
      direction: rtl;
      font-family: 'Vazirmatn', system-ui, -apple-system, sans-serif !important;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    .event-container {
      max-width: 1320px;
      margin: 0 auto;
      padding: 0 24px;
    }

    /* ============================================================================
       1. HERO SECTION (#2:113)
       ============================================================================ */
    .event-hero {
      background: var(--event-teal-dark);
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
      background: radial-gradient(circle at 90% 10%, rgba(255, 255, 255, 0.05) 0%, transparent 60%);
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
      gap: 22px;
    }

    .event-hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 16px;
      background: var(--event-amber-light);
      color: var(--event-amber);
      border-radius: 999px;
      font-size: 13px;
      font-weight: 700;
      align-self: flex-start;
    }

    .event-hero-title {
      font-size: clamp(28px, 3.8vw, 42px);
      font-weight: 800;
      line-height: 1.25;
      color: #FFFFFF;
    }

    .event-hero-desc {
      font-size: 16px;
      line-height: 1.8;
      color: #F0FDFA;
      opacity: 0.95;
      max-width: 620px;
    }

    /* Countdown Timer Boxes (#8:9) */
    .event-countdown-grid {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
    }
    .countdown-box {
      width: 82px;
      background: #FFFFFF;
      border-radius: 12px;
      padding: 10px 8px;
      text-align: center;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
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

    /* Hero Action Buttons (#8:19) */
    .event-hero-actions {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
      margin-top: 6px;
    }
    .btn-register-hero {
      background: var(--event-amber);
      color: #FFFFFF;
      padding: 14px 30px;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 6px 18px rgba(217, 119, 6, 0.35);
      transition: transform 0.2s ease, background 0.2s ease;
    }
    .btn-register-hero:hover {
      background: #B45309;
      transform: translateY(-2px);
    }
    .btn-pdf-hero {
      border: 1px solid rgba(255, 255, 255, 0.7);
      background: rgba(255, 255, 255, 0.08);
      color: #FFFFFF;
      padding: 14px 24px;
      border-radius: 10px;
      font-size: 14.5px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      backdrop-filter: blur(6px);
      transition: all 0.2s ease;
    }
    .btn-pdf-hero:hover {
      background: rgba(255, 255, 255, 0.18);
      border-color: #FFFFFF;
      transform: translateY(-2px);
    }

    /* Event Date & Status Info (#8:24) */
    .event-hero-status {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      font-size: 13.5px;
      color: #F0FDFA;
    }
    .status-pill-active {
      background: var(--event-amber);
      color: #FFFFFF;
      padding: 4px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
    }

    /* ============================================================================
       DEDICATED HERO SHOWCASE CAROUSEL (#8:28)
       A dedicated 560x420px spotlight frame for Event Heroes
       ============================================================================ */
    .hero-showcase-wrapper {
      width: 100%;
      max-width: 560px;
      height: 420px;
      background: #042e34;
      border: 2px solid var(--event-primary);
      border-radius: var(--event-radius-lg);
      position: relative;
      overflow: hidden;
      box-shadow: 0 16px 36px rgba(0, 0, 0, 0.25);
    }
    .hero-slider-track {
      width: 100%;
      height: 100%;
      position: relative;
    }
    .hero-slide {
      position: absolute;
      inset: 0;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.5s ease, transform 0.5s ease;
      transform: scale(0.98);
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      padding: 32px;
      background-size: cover;
      background-position: center;
    }
    .hero-slide.is-active {
      opacity: 1;
      visibility: visible;
      transform: scale(1);
    }
    .hero-slide-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(4, 46, 52, 0.25) 0%, rgba(4, 46, 52, 0.88) 70%, #042e34 100%);
      z-index: 1;
    }
    .hero-slide-body {
      position: relative;
      z-index: 2;
      display: flex;
      flex-direction: column;
      gap: 12px;
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
      font-size: 24px;
      font-weight: 800;
      color: #FFFFFF;
      line-height: 1.3;
    }
    .hero-slide-desc {
      font-size: 13.5px;
      line-height: 1.7;
      color: #E2E8F0;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .hero-slide-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 18px;
      background: var(--event-amber);
      color: #FFFFFF;
      border-radius: 8px;
      font-size: 12.5px;
      font-weight: 700;
      margin-top: 4px;
      transition: background 0.2s;
    }
    .hero-slide-link:hover {
      background: #B45309;
    }

    /* Slider Arrows & Dots */
    .hero-slider-nav {
      position: absolute;
      top: 20px;
      left: 20px;
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
       SECTION HEADERS & GENERAL STYLES
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
    }
    .section-subtitle {
      font-size: 15px;
      color: var(--event-text-muted);
      max-width: 760px;
      line-height: 1.7;
    }

    /* ============================================================================
       SECTION 1: معرفی و محورهای همایش (#2:133)
       ============================================================================ */
    .about-main-grid {
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: 40px;
      align-items: start;
    }
    .about-text-column {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }
    /* Fixed line-height and normal spacing: eliminates huge line gaps */
    .about-copy {
      font-size: 15.5px;
      line-height: 1.85;
      color: var(--event-text-main);
      white-space: pre-line;
      background: #FFFFFF;
      border: 1px solid var(--event-border);
      border-radius: var(--event-radius);
      padding: 28px;
      box-shadow: var(--event-shadow);
    }
    .about-register-bar {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    /* Side Cards Column: Official Poster + Schedule PDF */
    .about-side-column {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }
    .event-poster-card {
      background: var(--event-primary-light);
      border: 2px solid var(--event-primary);
      border-radius: var(--event-radius);
      padding: 16px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
      text-align: center;
      box-shadow: var(--event-shadow);
    }
    .event-poster-img {
      width: 100%;
      max-height: 440px;
      object-fit: contain;
      border-radius: 12px;
      background: #FFFFFF;
    }
    .poster-empty-state {
      width: 100%;
      min-height: 280px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 10px;
      color: var(--event-teal-dark);
      font-weight: 700;
      font-size: 14px;
      border: 2px dashed rgba(13, 122, 135, 0.35);
      border-radius: 12px;
    }

    /* Schedule PDF Download Card */
    .schedule-pdf-card {
      background: #FFFFFF;
      border: 1px solid var(--event-border);
      border-radius: var(--event-radius);
      padding: 24px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      box-shadow: var(--event-shadow);
    }
    .schedule-pdf-header {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .pdf-icon-badge {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: #FEE2E2;
      border: 1px solid #FECACA;
      color: #DC2626;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      font-size: 13px;
      flex-shrink: 0;
    }
    .schedule-pdf-info h3 {
      font-size: 16px;
      font-weight: 800;
      color: var(--event-teal-dark);
      margin: 0 0 4px;
    }
    .schedule-pdf-info p {
      font-size: 12px;
      color: var(--event-text-muted);
      margin: 0;
    }
    .btn-download-pdf {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      padding: 12px 18px;
      background: var(--event-primary);
      color: #FFFFFF;
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 700;
      transition: background 0.2s, transform 0.2s;
    }
    .btn-download-pdf:hover {
      background: var(--event-teal-dark);
      transform: translateY(-1px);
    }

    /* ============================================================================
       SECTION 2: دبیران علمی و اجرایی (#2:139)
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
    }
    .person-title {
      font-size: 13px;
      color: var(--event-text-muted);
      line-height: 1.6;
    }

    /* ============================================================================
       SECTION 3: سخنرانان برجسته همایش (#2:148)
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
    }
    .speaker-title {
      font-size: 12.5px;
      color: var(--event-text-light);
      line-height: 1.5;
    }

    /* ============================================================================
       SECTION 4: همراهان همایش (#8:66)
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
    }

    /* ============================================================================
       SECTION 5: اخبار همایش (#8:34)
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
    }
    .news-card-btn {
      align-self: flex-start;
      margin-top: 8px;
      padding: 8px 16px;
      background: var(--event-amber);
      color: #FFFFFF;
      border-radius: 8px;
      font-size: 12.5px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: background 0.2s;
    }
    .news-card-btn:hover {
      background: #B45309;
    }

    /* Responsive Queries */
    @media (max-width: 990px) {
      .event-hero-inner {
        grid-template-columns: 1fr;
      }
      .hero-showcase-wrapper {
        max-width: 100%;
        height: 360px;
      }
      .about-main-grid {
        grid-template-columns: 1fr;
      }
    }
    @media (max-width: 640px) {
      .event-hero {
        padding: 40px 0 48px;
      }
      .hero-showcase-wrapper {
        height: 320px;
      }
      .countdown-box {
        width: 68px;
        padding: 8px 4px;
      }
      .countdown-val {
        font-size: 20px;
      }
      .event-section {
        padding: 48px 0;
      }
      .about-copy {
        padding: 20px 16px;
      }
    }
  </style>
</head>
<body>

  <!-- ==========================================================================
       1. HERO SECTION
       ========================================================================== -->
  <section class="event-hero">
    <div class="event-container">
      <div class="event-hero-inner">
        
        <!-- Right Column: Event Info & Call-to-Actions -->
        <div class="event-hero-content">
          <div class="event-hero-badge">
            <span>●</span>
            <span>بزرگترین گردهمایی علمی حمایتی کشور</span>
          </div>

          <h1 class="event-hero-title"><?= event_h($event['title']) ?></h1>

          <p class="event-hero-desc">
            <?= event_h($event['short_description'] ?: 'همایشی ملی برای همگرایی دانش نوین و ارتقای کیفیت زندگی بیماران مبتلا به سرطان.') ?>
          </p>

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
            <?php if ($event['registration_url']): ?>
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

            <?php if ($event['schedule_pdf']): ?>
              <a href="<?= event_h($event['schedule_pdf']) ?>" target="_blank" download class="btn-pdf-hero">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>برنامه زمان‌بندی (PDF)</span>
              </a>
            <?php endif; ?>
          </div>

          <!-- Status & Date -->
          <div class="event-hero-status">
            <span class="status-pill-active">ثبت‌نام فعال</span>
            <span>تاریخ برگزاری: <?= event_h(event_date_label($event['event_date'])) ?> ساعت <?= event_h(substr((string)$event['start_time'], 0, 5)) ?> به وقت تهران</span>
          </div>
        </div>

        <!-- Left Column: Dedicated Hero Showcase Frame (#8:28) -->
        <div class="hero-showcase-wrapper" id="heroShowcase">
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
            <!-- Carousel Navigation Arrows -->
            <div class="hero-slider-nav">
              <button type="button" class="hero-slider-btn" id="heroPrevBtn" aria-label="اسلاید قبلی">❮</button>
              <button type="button" class="hero-slider-btn" id="heroNextBtn" aria-label="اسلاید بعدی">❯</button>
            </div>
            <!-- Indicator Dots -->
            <div class="hero-slider-dots" id="heroDots">
              <?php foreach ($heroes as $idx => $h): ?>
                <span class="hero-dot <?= $idx === 0 ? 'is-active' : '' ?>" data-dot-index="<?= $idx ?>"></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </section>

  <!-- ==========================================================================
       2. SECTION 1: معرفی و محورهای همایش (About & Schedule PDF)
       ========================================================================== -->
  <section class="event-section event-section-white" id="sec-about">
    <div class="event-container">
      
      <div class="section-header">
        <span class="section-kicker">معرفی و محورهای همایش</span>
        <h2 class="section-title"><?= event_h($event['title']) ?></h2>
        <p class="section-subtitle">اطلاعات تفصیلی، محورهای علمی و جدول زمان‌بندی کامل همایش</p>
      </div>

      <div class="about-main-grid">
        <!-- Text Column with Perfect Typography & Spacing -->
        <div class="about-text-column">
          <div class="about-copy"><?= event_h($event['about'] ?: $event['short_description']) ?></div>

          <?php if ($event['registration_url']): ?>
            <div class="about-register-bar">
              <a href="<?= event_h($event['registration_url']) ?>" target="_blank" rel="noopener" class="btn-register-hero">
                <span>ثبت‌نام در این همایش</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </a>
            </div>
          <?php endif; ?>
        </div>

        <!-- Side Column: Official Poster + Schedule PDF Download -->
        <div class="about-side-column">
          <!-- Poster -->
          <div class="event-poster-card">
            <?php if (!empty($event['poster'])): ?>
              <img src="<?= event_h($event['poster']) ?>" alt="پوستر <?= event_h($event['title']) ?>" class="event-poster-img">
            <?php else: ?>
              <div class="poster-empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span>پوستر رسمی این رویداد</span>
              </div>
            <?php endif; ?>
          </div>

          <!-- Schedule PDF Download Card -->
          <div class="schedule-pdf-card">
            <div class="schedule-pdf-header">
              <div class="pdf-icon-badge">PDF</div>
              <div class="schedule-pdf-info">
                <h3>سین همایش</h3>
                <p>جدول زمان‌بندی سخنرانی‌ها، پنل‌ها و کارگاه‌ها</p>
              </div>
            </div>
            <?php if (!empty($event['schedule_pdf'])): ?>
              <a href="<?= event_h($event['schedule_pdf']) ?>" target="_blank" download class="btn-download-pdf">
                <span>دریافت فایل برنامه (PDF)</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              </a>
            <?php else: ?>
              <div style="font-size:12.5px; color:var(--event-text-light); text-align:center; padding:10px; background:var(--event-bg-light); border-radius:8px;">
                فایل زمان‌بندی برنامه به‌زودی بارگذاری می‌شود.
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
                  <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--event-primary)" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
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
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--event-primary)" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
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
                  <span style="font-weight:800; font-size:16px; color:var(--event-teal-dark);">مکسا</span>
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
       6. SECTION 5: اخبار همایش (در پایان صفحه بر اساس دستور کاربر)
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

  <!-- Scripts: Countdown Timer & Interactive Hero Slider -->
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

      // 2. Interactive Hero Carousel
      const slides = document.querySelectorAll('.hero-slide');
      const dots = document.querySelectorAll('.hero-dot');
      const btnPrev = document.getElementById('heroPrevBtn');
      const btnNext = document.getElementById('heroNextBtn');
      if (slides.length > 1) {
        let activeIdx = 0;
        let timer = null;

        function goToSlide(idx) {
          activeIdx = (idx + slides.length) % slides.length;
          slides.forEach((s, i) => s.classList.toggle('is-active', i === activeIdx));
          dots.forEach((d, i) => d.classList.toggle('is-active', i === activeIdx));
        }

        function startAuto() {
          stopAuto();
          timer = setInterval(() => goToSlide(activeIdx + 1), 5000);
        }
        function stopAuto() {
          if (timer) clearInterval(timer);
        }

        if (btnNext) btnNext.addEventListener('click', () => { goToSlide(activeIdx + 1); startAuto(); });
        if (btnPrev) btnPrev.addEventListener('click', () => { goToSlide(activeIdx - 1); startAuto(); });
        dots.forEach((d, i) => d.addEventListener('click', () => { goToSlide(i); startAuto(); }));

        const showcase = document.getElementById('heroShowcase');
        if (showcase) {
          showcase.addEventListener('mouseenter', stopAuto);
          showcase.addEventListener('mouseleave', startAuto);
        }
        startAuto();
      }
    })();
  </script>

<?php
if (!defined('IN_SNAPSHOT') && file_exists(__DIR__ . '/dashboard/components/footer/component.php')) {
    require __DIR__ . '/dashboard/components/footer/component.php';
}
?>
</body>
</html>

<?php
declare(strict_types=1);
require_once __DIR__ . '/core/database.php'; require_once __DIR__ . '/event-lib.php';
$slug=trim((string)($_GET['slug']??'')); $st=$pdo->prepare("SELECT * FROM events WHERE slug=? AND status='published' LIMIT 1");$st->execute([$slug]);$event=$st->fetch(); if(!$event){http_response_code(404);exit('رویداد پیدا نشد.');}
$q=function(string $sql)use($pdo,$event){$s=$pdo->prepare($sql);$s->execute([(int)$event['id']]);return $s->fetchAll();};$heroes=$q('SELECT * FROM event_heroes WHERE event_id=? AND is_active=1 ORDER BY sort_order,id');$people=$q('SELECT * FROM event_people WHERE event_id=? ORDER BY sort_order,id');$speakers=$q('SELECT * FROM event_speakers WHERE event_id=? ORDER BY sort_order,id');$partners=$q('SELECT * FROM event_partners WHERE event_id=? ORDER BY sort_order,id');$news=$q("SELECT * FROM event_news WHERE event_id=? AND status='published' ORDER BY published_at DESC,id DESC");$pageTitle=$event['title']; require __DIR__ . '/dashboard/components/header/component.php';
$iso=$event['event_date'].'T'.substr((string)$event['start_time'],0,8).'+03:30';
$primaryHero = $heroes[0] ?? null;
$secondaryHeroes = count($heroes) > 1 ? array_slice($heroes, 1) : [];
$heroStyle = $primaryHero && $primaryHero['image']
    ? " style=\"background-image:linear-gradient(90deg,rgba(0,35,40,.82),rgba(0,123,122,.62)),url('" . event_h($primaryHero['image']) . "')\""
    : '';
?>
<?php if($secondaryHeroes): ?><section class="event-hero-strip" aria-label="هیروهای رویداد"><?php foreach($secondaryHeroes as $hero): ?><article <?php if($hero['image']): ?>style="background-image:linear-gradient(180deg,transparent,rgba(0,30,35,.88)),url('<?= event_h($hero['image']) ?>')"<?php endif; ?>><h2><?= event_h($hero['title']) ?></h2><p><?= event_h($hero['description']) ?></p><?php if($hero['button_link']): ?><a href="<?= event_h($hero['button_link']) ?>" target="_blank" rel="noopener">مشاهده بیشتر ↗</a><?php endif; ?></article><?php endforeach; ?></section><?php endif; ?>
<style>
.event-hero-strip{max-width:1180px;margin:0 auto;padding:24px 20px 0;display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px}
.event-hero-strip article{min-height:170px;border-radius:18px;overflow:hidden;background:#0b5e63;color:#fff;padding:24px;display:flex;flex-direction:column;justify-content:flex-end;background-size:cover;background-position:center;box-shadow:0 10px 24px rgba(11,94,99,.15)}
.event-hero-strip h2{font-size:20px;font-weight:800;margin:0 0 6px}
.event-hero-strip p{font-size:13px;margin:0 0 12px;color:rgba(255,255,255,.82);line-height:1.7}
.event-hero-strip a{color:#ffe1a2;font-size:12px;font-weight:800;display:inline-flex;align-items:center;gap:6px}
.event-page{--teal:#007b7a;--teal-dark:#085759;--teal-light:#e6f5f4;--ink:#123a3d;--muted:#5c7478;--border:#e2eceb;--card-bg:#ffffff;direction:rtl;color:var(--ink);background:#f6f9f8;font-family:inherit}
.event-hero{min-height:560px;background:linear-gradient(135deg,#05383e 0%,#007b7a 58%,#0ba3a0 100%);color:#fff;position:relative;overflow:hidden}
.event-hero:after{content:'';position:absolute;inset:0;background:radial-gradient(circle at 10% 20%,rgba(255,255,255,.08) 0%,transparent 60%),linear-gradient(90deg,rgba(0,0,0,.42),transparent 70%);pointer-events:none}
.event-hero-inner{max-width:1180px;min-height:560px;margin:auto;padding:70px 20px;display:grid;grid-template-columns:1.15fr .85fr;gap:44px;align-items:center;position:relative;z-index:2}
.event-hero-copy{max-width:640px}
.event-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:800;color:#ffe1a2;background:rgba(255,225,162,.15);border:1px solid rgba(255,225,162,.3);padding:5px 12px;border-radius:100px;margin-bottom:18px}
.event-hero h1{font-size:clamp(30px,4.5vw,56px);line-height:1.32;margin:0 0 16px;font-weight:900}
.event-hero p{font-size:15px;line-height:2.1;color:rgba(255,255,255,.88);margin-bottom:0}
.event-meta-bar{display:flex;align-items:center;gap:18px;flex-wrap:wrap;margin-top:22px}
.event-meta-item{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:7px 13px;font-size:13px;font-weight:700;color:#fff}
.event-meta-item svg{width:16px;height:16px;opacity:.9}
.countdown{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:28px}
.countdown div{background:#fff;color:var(--ink);border-radius:14px;padding:12px 8px;text-align:center;box-shadow:0 8px 24px rgba(0,35,40,.18)}
.countdown strong{display:block;font-size:28px;font-weight:900;color:var(--teal);font-variant-numeric:tabular-nums;line-height:1.1}
.countdown span{font-size:11px;font-weight:700;color:var(--muted);margin-top:4px;display:block}
.event-poster{min-height:360px;border-radius:22px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.22);padding:14px;display:grid;place-items:center;box-shadow:0 24px 50px rgba(0,30,35,.28);backdrop-filter:blur(6px)}
.event-poster img{width:100%;max-height:440px;object-fit:contain;border-radius:14px}
.poster-empty{width:100%;height:320px;display:grid;place-items:center;text-align:center;color:rgba(255,255,255,.7);border:1.5px dashed rgba(255,255,255,.3);border-radius:14px;font-size:13px;font-weight:700}
.event-container{max-width:1180px;margin:auto;padding:52px 20px 80px}
.event-section{padding:48px 0}
.event-section+.event-section{border-top:1px solid var(--border)}
.section-kicker{font-size:12px;font-weight:800;color:var(--teal);margin-bottom:8px;letter-spacing:.04em}
.event-section h2{font-size:clamp(24px,2.8vw,36px);margin:0 0 16px;font-weight:900;color:#13393c}
.about-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:36px;align-items:start}
.about-copy{font-size:15px;line-height:2.2;color:#4c6265;white-space:pre-line}
.register-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;background:#e99b16;color:#fff;border-radius:12px;padding:14px 26px;font-weight:800;font-size:14px;margin-top:22px;box-shadow:0 8px 20px rgba(233,155,22,.28);transition:all .2s ease}
.register-btn:hover{background:#d58a0e;transform:translateY(-1px);color:#fff}
.register-btn svg{width:16px;height:16px}
/* Modern React-Inspired Schedule Card */
.schedule-card{background:linear-gradient(150deg,#ffffff 0%,#f5f9f8 100%);border:1.5px solid var(--border);border-radius:22px;box-shadow:0 16px 36px rgba(18,58,61,.07);padding:28px 24px;position:relative;overflow:hidden}
.schedule-card-tag{display:inline-flex;align-items:center;gap:7px;background:#e6f5f4;color:var(--teal);font-size:11px;font-weight:800;padding:5px 12px;border-radius:100px;margin-bottom:18px}
.schedule-card-tag .dot{width:6px;height:6px;border-radius:50%;background:var(--teal)}
.schedule-doc-box{display:flex;align-items:center;gap:16px;margin-bottom:20px}
.schedule-icon-wrap{width:58px;height:58px;border-radius:16px;background:linear-gradient(135deg,#fee2e2,#fef2f2);border:1px solid #fecaca;color:#ef4444;display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;position:relative}
.schedule-icon-wrap svg{width:24px;height:24px}
.schedule-icon-wrap span{font-size:9px;font-weight:900;letter-spacing:.05em;margin-top:2px}
.schedule-info h3{font-size:18px;font-weight:800;margin:0 0 5px;color:#13393c}
.schedule-info p{font-size:12px;color:var(--muted);line-height:1.7;margin:0}
.schedule-badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px}
.schedule-pill{display:inline-flex;align-items:center;gap:5px;background:#fff;border:1px solid #e1e9e8;border-radius:8px;padding:4px 9px;font-size:11px;font-weight:700;color:#5a7275}
.schedule-pill svg{width:12px;height:12px;color:var(--teal)}
.schedule-download-btn{display:flex;align-items:center;justify-content:space-between;width:100%;height:52px;padding:0 20px;border-radius:14px;background:var(--teal);color:#fff;font-size:14px;font-weight:800;box-shadow:0 8px 20px rgba(0,123,122,.22);transition:all .2s ease}
.schedule-download-btn:hover{background:var(--teal-dark);transform:translateY(-1px);color:#fff}
.schedule-download-btn svg{width:18px;height:18px}
.schedule-empty{padding:22px 16px;background:#fff;border:1.5px dashed #cadada;border-radius:14px;text-align:center;color:var(--muted);font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:8px}
.schedule-empty svg{width:18px;height:18px;color:#f59e0b}
/* Grids with Intelligent Balancing */
.people-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,320px));gap:24px;max-width:100%}
.speaker-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,280px));gap:20px}
.news-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,360px));gap:20px}
.partner-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,220px));gap:16px}
.person-card,.speaker-card,.news-card,.partner-card{background:var(--card-bg);border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:0 6px 18px rgba(18,58,61,.04);transition:transform .2s,box-shadow .2s}
.person-card{max-width:320px;width:100%}
.speaker-card{max-width:280px;width:100%}
.news-card{max-width:360px;width:100%}
.person-card:hover,.speaker-card:hover,.news-card:hover,.partner-card:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(18,58,61,.08)}
.person-card img,.speaker-card img{width:100%;aspect-ratio:1;object-fit:cover;background:#eaf2f1}
.person-card .inner,.speaker-card .inner,.news-card .inner{padding:16px}
.person-badge{display:inline-block;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:800;background:var(--teal-light);color:var(--teal);margin-bottom:8px}
.person-card h3,.speaker-card h3{font-size:16px;font-weight:800;margin:0 0 5px;color:#13393c}
.news-card img{width:100%;height:165px;object-fit:cover;background:#eaf2f1}
.news-card h3{font-size:16px;font-weight:800;line-height:1.6;margin:0 0 8px;color:#13393c}
.news-card a{display:inline-flex;align-items:center;gap:6px;color:var(--teal);font-weight:800;font-size:13px;margin-top:10px}
.partner-card{text-align:center;padding:22px 16px;display:flex;flex-direction:column;align-items:center;justify-content:center}
.partner-card img{max-width:110px;height:55px;object-fit:contain;margin-bottom:12px;filter:grayscale(30%);transition:filter .2s}
.partner-card:hover img{filter:none}
.partner-card h3{font-size:13px;font-weight:700;margin:0;color:#355357}
@media(max-width:860px){.event-hero-inner,.about-grid{grid-template-columns:1fr}.event-poster{min-height:240px;max-width:420px;margin:auto;width:100%}}
@media(max-width:560px){.countdown{grid-template-columns:repeat(2,1fr)}.event-container{padding:36px 16px}.event-hero h1{font-size:28px}}
</style>
<main class="event-page">
  <section class="event-hero<?= $primaryHero && $primaryHero['image'] ? ' has-primary-image' : '' ?>"<?= $heroStyle ?>>
    <div class="event-hero-inner">
      <div class="event-hero-copy">
        <div class="event-eyebrow">
          <span>●</span>
          <span><?= $primaryHero ? event_h($primaryHero['title']) : 'رویداد و همایش ملی مکسا' ?></span>
        </div>
        <h1><?= event_h($event['title']) ?></h1>
        <p><?= event_h($primaryHero['description'] ?? $event['short_description']) ?></p>
        <div class="event-meta-bar">
          <div class="event-meta-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M8 2v4"></path><path d="M16 2v4"></path><path d="M3 10h18"></path></svg>
            <span><?= event_h(event_date_label($event['event_date'])) ?></span>
          </div>
          <div class="event-meta-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>ساعت شروع: <?= event_h(substr((string)$event['start_time'],0,5)) ?></span>
          </div>
        </div>
        <div class="countdown" data-countdown="<?= event_h($iso) ?>">
          <div><strong data-unit="days">۰</strong><span>روز</span></div>
          <div><strong data-unit="hours">۰</strong><span>ساعت</span></div>
          <div><strong data-unit="minutes">۰</strong><span>دقیقه</span></div>
          <div><strong data-unit="seconds">۰</strong><span>ثانیه</span></div>
        </div>
      </div>
      <div class="event-poster">
        <?php if($event['poster']): ?>
          <img src="<?= event_h($event['poster']) ?>" alt="پوستر <?= event_h($event['title']) ?>">
        <?php else: ?>
          <div class="poster-empty">پوستر رسمی این رویداد به‌زودی بارگذاری می‌شود.</div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <div class="event-container">
    <section class="event-section about-grid">
      <div>
        <div class="section-kicker">درباره همایش</div>
        <h2>همراه با تازه‌ترین نگاه‌ها به مراقبت تسکینی</h2>
        <div class="about-copy"><?= nl2br(event_h($event['about'])) ?></div>
        <?php if($event['registration_url']): ?>
          <a class="register-btn" href="<?= event_h($event['registration_url']) ?>" target="_blank" rel="noopener">
            <span>ثبت‌نام در این همایش</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
          </a>
        <?php endif; ?>
      </div>

      <!-- Modern Schedule PDF Card -->
      <div class="schedule-card">
        <div class="schedule-card-tag">
          <span class="dot"></span>
          <span>برنامه و زمان‌بندی رسمی</span>
        </div>
        <div class="schedule-doc-box">
          <div class="schedule-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            <span>PDF</span>
          </div>
          <div class="schedule-info">
            <h3>سین همایش</h3>
            <p>جدول زمان‌بندی سخنرانی‌ها، پنل‌ها و کارگاه‌ها</p>
          </div>
        </div>
        <div class="schedule-badges">
          <span class="schedule-pill">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            نسخه رسمی دبیرخانه
          </span>
          <span class="schedule-pill">سند دیجیتال</span>
        </div>
        <?php if($event['schedule_pdf']): ?>
          <a class="schedule-download-btn" href="<?= event_h($event['schedule_pdf']) ?>" target="_blank" download>
            <span>دریافت فایل برنامه (PDF)</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          </a>
        <?php else: ?>
          <div class="schedule-empty">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span>فایل زمان‌بندی برنامه به‌زودی بارگذاری می‌شود.</span>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <?php if($people): ?>
      <section class="event-section">
        <div class="section-kicker">ارکان علمی و اجرایی</div>
        <h2>دبیران همایش</h2>
        <div class="people-grid">
          <?php foreach($people as $p): ?>
            <article class="person-card">
              <?php if($p['image']): ?>
                <img src="<?= event_h($p['image']) ?>" alt="<?= event_h($p['name']) ?>">
              <?php endif; ?>
              <div class="inner">
                <span class="person-badge"><?= $p['role']==='scientific_secretary'?'دبیر علمی':'دبیر اجرایی' ?></span>
                <h3><?= event_h($p['name']) ?></h3>
                <div class="muted"><?= event_h($p['title']) ?></div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if($speakers): ?>
      <section class="event-section">
        <div class="section-kicker">سخنرانان و اساتید</div>
        <h2>چهره‌های حاضر در همایش</h2>
        <div class="speaker-grid">
          <?php foreach($speakers as $p): ?>
            <article class="speaker-card">
              <?php if($p['image']): ?>
                <img src="<?= event_h($p['image']) ?>" alt="<?= event_h($p['name']) ?>">
              <?php endif; ?>
              <div class="inner">
                <h3><?= event_h($p['name']) ?></h3>
                <div class="muted"><?= event_h($p['title']) ?></div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if($news): ?>
      <section class="event-section">
        <div class="section-kicker">رویدادنامه</div>
        <h2>اخبار همایش</h2>
        <div class="news-grid">
          <?php foreach($news as $n): ?>
            <article class="news-card">
              <?php if($n['image']): ?>
                <img src="<?= event_h($n['image']) ?>" alt="<?= event_h($n['title']) ?>">
              <?php endif; ?>
              <div class="inner">
                <h3><?= event_h($n['title']) ?></h3>
                <p class="muted"><?= event_h($n['excerpt']) ?></p>
                <a href="/event-news.php?slug=<?= rawurlencode($n['slug']) ?>">
                  <span>ادامه خبر</span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"></path></svg>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if($partners): ?>
      <section class="event-section">
        <div class="section-kicker">همراهان و حامیان</div>
        <h2>با همراهی شما</h2>
        <div class="partner-grid">
          <?php foreach($partners as $p): ?>
            <div class="partner-card">
              <?php if($p['logo']): ?>
                <img src="<?= event_h($p['logo']) ?>" alt="<?= event_h($p['name']) ?>">
              <?php endif; ?>
              <h3><?= event_h($p['name']) ?></h3>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </div>
</main>
<script>(function(){const root=document.querySelector('[data-countdown]');if(!root)return;const fa=n=>String(Math.max(0,n)).replace(/\d/g,d=>'۰۱۲۳۴۵۶۷۸۹'[d]);const tick=()=>{let left=Math.max(0,Date.parse(root.dataset.countdown)-Date.now()),s=Math.floor(left/1000),d=Math.floor(s/86400);s%=86400;let h=Math.floor(s/3600);s%=3600;let m=Math.floor(s/60),sec=s%60;[['days',d],['hours',h],['minutes',m],['seconds',sec]].forEach(([u,v])=>{const e=root.querySelector('[data-unit="'+u+'"]');if(e)e.textContent=fa(String(v).padStart(2,'0'))});};tick();setInterval(tick,1000)})();</script>
<?php require __DIR__ . '/dashboard/components/footer/component.php';

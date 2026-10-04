<?php $macsaHeroContext = true; require __DIR__ . '/../header/component.php'; ?>
<style>
    /* ===== HERO ===== */
    .cta-hero-wrap{
      background:#0b0c10;
      padding: 20px 16px 32px;
    }

    .cta-hero{
      position:relative;
      width:min(var(--cta-container), 100%);
      margin-inline:auto;
      min-height: 560px;
      border-radius: 28px;
      isolation:isolate;
      overflow:hidden;
      background:#111;
      box-shadow: 0 24px 60px rgba(0,0,0,.45);
    }

    .cta-hero::before{
  content:"";
  position:absolute;
  inset:0;
  background: radial-gradient(circle at 20% 20%, rgba(255,255,255,.08), transparent 35%),
              radial-gradient(circle at 80% 10%, rgba(255,255,255,.06), transparent 40%),
              linear-gradient(to bottom, rgba(0,0,0,.12), rgba(0,0,0,.12));
  mix-blend-mode: overlay;
  opacity:.55;
  pointer-events:none;
  z-index:2;
}

    /* ===== Slider ===== */
    .cta-slider{ position:absolute; inset:0; z-index:1; }
    .cta-track{
      height:100%;
      display:flex;
      width:100%;
      transition: transform 650ms cubic-bezier(.2,.8,.2,1);
      will-change: transform;
    }
    .cta-slide{
      position:relative;
      min-width:100%;
      height:100%;
      background-size: cover;
      background-position: center;
      filter: saturate(1.02) contrast(1.03);
    }
    .cta-slide::after{
      content:"";
      position:absolute;
      inset:0;
      background:
        linear-gradient(to left, rgba(0,0,0,.40), rgba(0,0,0,.18) 55%, rgba(0,0,0,.08)),
        radial-gradient(circle at 15% 30%, rgba(0,0,0,.08), transparent 55%);
      z-index:0;
    }

    /* ===== hero content ===== */
    .cta-content{
      position:relative;
      z-index:6;
      padding: 32px 0;
      min-height: 560px;
      display:flex;
      align-items:flex-end;
    }

    .cta-content .cta-container{
      display:flex;
      align-items:flex-end;
      justify-content:space-between;
      gap: 24px;
      flex-wrap: wrap;
    }

    .cta-text{
      width:min(640px, 100%);
      color: var(--cta-text);
      text-align:right;
    }

    .cta-badge{
      display:inline-flex;
      align-items:center;
      gap:6px;
      background: rgba(250,166,26,.14);
      border: 1px solid rgba(250,166,26,.35);
      color: var(--cta-orange);
      font-size:12px;
      font-weight:700;
      padding:6px 12px;
      border-radius:999px;
      margin-bottom:14px;
    }

    .cta-kicker{
      font-size:13px;
      color: rgba(255,255,255,.7);
      margin-bottom:10px;
    }
    .cta-title{
      font-size:40px;
      line-height:1.25;
      margin:0 0 12px 0;
      font-weight:800;
      letter-spacing:-.01em;
      text-wrap: balance;
      text-shadow: 0 10px 18px rgba(0,0,0,.20);
    }

    .cta-desc{
      margin:0 0 22px 0;
      color: var(--cta-muted);
      font-size:15px;
      line-height:1.9;
      max-width: 58ch;
    }

    .cta-btn{
      display:inline-flex;
      align-items:center;
      gap:10px;
      border:none;
      cursor:pointer;
      background: var(--cta-orange);
      color:#111;
      font-weight:800;
      padding:11px 16px;
      border-radius:10px;
      box-shadow: 0 10px 20px rgba(250, 166, 26,.22);
      transition:.2s ease;
    }
    .cta-btn:hover{ transform: translateY(-1px); filter: brightness(1.03); }
    .cta-btn:active{ transform: translateY(0px); }
    .cta-btn:focus-visible{
      outline:none;
      box-shadow: 0 0 0 3px rgba(250, 166, 26,.20), 0 10px 20px rgba(250, 166, 26,.22);
    }

    /* arrows */
    .cta-arrow{
      position:absolute;
      top:50%;
      transform: translateY(-50%);
      z-index:7;
      width:44px; height:44px;
      border-radius:999px;
      border:1px solid rgba(255,255,255,.22);
      background: rgba(0,0,0,.26);
      color:#fff;
      display:grid;
      place-items:center;
      cursor:pointer;
      transition:.2s ease;
    }
.cta-glass {
  backdrop-filter: blur(6px);
  box-shadow: var(--cta-shadow-soft);
}
    .cta-arrow:hover{ background: rgba(0,0,0,.40); }
    .cta-arrow:focus-visible{ outline:none; box-shadow: 0 0 0 3px rgba(250,166,26,.45); }
    .cta-arrow.prev{ right:10px; left: auto; }
    .cta-arrow.next{ left:16px; right: auto; }
    .cta-arrow svg{ width:18px; height:18px; opacity:.9; }

    /* dots */
    .cta-dots{
      position:absolute;
      bottom: 18px;
      left:50%;
      transform: translateX(-50%);
      z-index:7;
      display:flex;
      gap:8px;
      align-items:center;
    }
    .cta-dot{
      width:9px; height:9px;
      border-radius:999px;
      border:1px solid rgba(255,255,255,.60);
      background: rgba(255,255,255,.15);
      cursor:pointer;
      transition:.2s ease;
    }
    .cta-dot.active{
      width:20px;
      background: var(--cta-orange);
      border-color: transparent;
    }
    .cta-dot:focus-visible{ outline:none; box-shadow: 0 0 0 3px rgba(250,166,26,.45); }

    /* ===== ORANGE BAND ===== */
    .cta-band{
      position:relative;
      background: linear-gradient(180deg, var(--cta-orange) 0%, var(--cta-orange-2) 100%);
      padding: 34px 0 80px 0;
    }

    .cta-band .cta-container{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      align-items:center;
      gap:24px;
      color:#fff;
    }

    .cta-band-text{
      text-align:center;
      grid-column: 2;
    }

    .cta-band h3{
      margin:0;
      font-size:20px;
      font-weight:900;
      text-align:center;
    }

    .cta-band p{
      margin:6px auto 0 auto;
      opacity:.92;
      font-size:13px;
      line-height:1.7;
      max-width: 58ch;
      text-align:center;
    }

    .cta-band-cta {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: linear-gradient(135deg, #e53935, #c62828); /* Red gradient matching brand tone */
      color: #fff !important;
      border: none;
      padding: 14px 28px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 16px;
      text-decoration: none;
      white-space: nowrap;
      cursor: pointer;
      transition: all 0.25s ease;
      box-shadow: 0 6px 20px rgba(198, 40, 40, 0.4);
      width: 100%;
      grid-column: 1;
    }
    .cta-band-cta:hover {
      background: linear-gradient(135deg, #c62828, #b71c1c);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(198, 40, 40, 0.5);
    }
    .cta-band-cta:active {
      transform: translateY(1px);
    }

    /* ===== Cards (همون قبلی) ===== */
    .cta-cards-wrap{
      position:relative;
      margin-top: -52px;
      padding-bottom: 40px;
    }

    .cta-cards{
      display:grid;
      grid-template-columns: repeat(4, 1fr);
      gap:24px;
    }

    .cta-card{
      background:#fff;
      border-radius: 10px;
      box-shadow: var(--cta-shadow);
      padding:26px 24px 22px;
      border:1px solid rgba(0,0,0,.06);
      text-align:center;
      cursor:pointer;
      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
      position:relative;
      outline:none;
    }

    .cta-card:hover{
      transform: translateY(-3px);
      box-shadow: 0 14px 30px rgba(0,0,0,.16);
      border-color: rgba(250, 166, 26,.30);
    }

    .cta-card.is-active{
      border-color: rgba(250, 166, 26,.55);
      box-shadow: 0 14px 30px rgba(250, 166, 26,.16), 0 0 0 3px rgba(250, 166, 26,.12);
      transform: translateY(-3px);
    }

    .cta-icon{
      width:44px; height:44px;
      margin:0 auto 10px;
      border-radius: 12px;
      display:grid;
      place-items:center;
      background: rgba(250, 166, 26,.15);
      border:1px solid rgba(250, 166, 26,.25);
      transition: .18s ease;
      color:#111;
    }

    .cta-card:hover .cta-icon,
    .cta-card.is-active .cta-icon{
      background: rgba(250, 166, 26,.22);
      border-color: rgba(250, 166, 26,.40);
      transform: scale(1.03);
    }

    .cta-card h4{
      margin:6px 0 6px 0;
      font-size:15px;
      font-weight:900;
      color:#1a1a1a;
    }

    .cta-card p{
      margin:0 auto 10px;
      color:#666;
      font-size:12.8px;
      line-height:1.75;
      max-width: 42ch;
    }

    .cta-card a{
      color: var(--cta-orange-2);
      font-weight:800;
      font-size:13px;
    }




</style>
<main class="cta">
    <div class="cta-hero-wrap">
      <section class="cta-hero" aria-label="معرفی مکسا">

        <div class="cta-slider" id="ctaSlider">
           <div class="cta-track" id="ctaTrack">
             <div class="cta-slide"><picture><source srcset="/uploads/hero/hero_1780827718_9482.mobile.webp" type="image/webp" media="(max-width: 767px)"><source srcset="/uploads/hero/hero_1780827718_9482.png.webp" type="image/webp"><img src="/uploads/hero/hero_1780827718_9482.png" alt="" width="1400" height="800" fetchpriority="high"></picture></div>
          </div>
        </div>

        <div class="cta-content">
          <div class="cta-container">
            <div class="cta-text">
              <span class="cta-badge"><?= macsa_icon('heart', '', 14) ?> مشارکت در خیریه</span>
              <h1 class="cta-title" id="heroTitle">موسسه نیکوکاری کنترل سرطان ایرانیان (مکسا)</h1>
              <p class="cta-desc" id="heroDesc">هم سنگر بیماران در مبارزه با سرطان</p>
              <a class="cta-btn" id="heroBtn" href="#" hidden>
                <span id="heroBtnText">مشاهده بیشتر</span>
                <span aria-hidden="true">←</span>
              </a>
            </div>
          </div>
        </div>

        <button class="cta-arrow prev" id="ctaPrev" hidden aria-label="اسلاید قبلی" type="button">
          <?= macsa_icon('chevron-right', '', 24) ?>
        </button>

        <button class="cta-arrow next" id="ctaNext" hidden aria-label="اسلاید بعدی" type="button">
          <?= macsa_icon('chevron-left', '', 24) ?>
        </button>

        <div class="cta-dots" id="ctaDots" aria-label="نشانگر اسلایدها"></div>

      </section>
    </div>

    <section class="cta-band" aria-label="Band">
      <div class="cta-container">
        <a class="cta-band-cta" href="/onlinedonation"><?= macsa_icon('heart', 'cta-heart-ic', 18) ?> <span>می‌خواهم کمک کنم</span></a>
        <div class="cta-band-text">
          <h2>با هم برای جهانی بهتر</h2>
          <p>
            مشارکت شما می‌تونه یک تغییر واقعی بسازه؛
            کافی‌ست مسیر درست رو انتخاب کنیم و کنار هم ادامه بدیم.
          </p>
        </div>
      </div>
    </section>

    <section class="cta-cards-wrap" aria-label="Cards">
      <div class="cta-container">
<div class="cta-cards" id="ctaCards">

  <div class="cta-card" role="link" tabindex="0" data-link="/supportprojects">
    <div class="cta-icon" aria-hidden="true">
      <?= macsa_icon('menu-2', '', 24) ?>
    </div>

    <h3>بسته های نیکوکاری</h3>
    <p>یک قدم کوچک شما، برای یک خانواده می‌تونه بزرگ‌ترین امید باشه.</p>
  </div>

  <div class="cta-card" role="link" tabindex="0" data-link="/single-fundraising-option" aria-label="مشاهده روش‌های حمایت">
    <div class="cta-icon" aria-hidden="true">
      <?= macsa_icon('heart', '', 24) ?>
    </div>

    <h3>روش های حمایت</h3>
    <p>از راه های مختلف کمک های نقدی و غیر نقدی خود را به زندگی بیماران هدیه کنید.</p>
  </div>

  <div class="cta-card" role="link" tabindex="0" data-link="/patientintake" aria-label="تشکیل پرونده اولیه مجازی">
    <div class="cta-icon" aria-hidden="true">
      <?= macsa_icon('user', '', 24) ?>
    </div>

    <h3>تشکیل پرونده اولیه مجازی</h3>
    <p>برای دریافت مشاوره تخصصی و بررسی پرونده درمانی، فرم مجازی را تکمیل کنید.</p>
  </div>

  <div class="cta-card" role="link" tabindex="0" data-link="/stand-order.php">
    <div class="cta-icon" aria-hidden="true">
      <?= macsa_icon('star', '', 24) ?>
    </div>

    <h3>استند و کارت تسلیت</h3>
    <p>در شادی‌ها و غم‌ها با سفارش استند، حامی بیماران مبتلا به سرطان باشید.</p>
  </div>

</div>


      </div>
    </section>

<p class="hero-status" role="status" aria-live="polite">در حال بارگذاری معرفی مکسا...</p>

</main>

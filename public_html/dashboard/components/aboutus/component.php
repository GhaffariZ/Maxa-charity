<?php require_once __DIR__ . '/../../../core/site-locale.php'; ?>
<section class="skill-hero">
  <div class="skill-hero-container">

    <div class="skill-hero-left">
      <h2 class="skill-hero-title">
        <?= site_e('خدمات مکسا') ?> <br>
        <span><?= site_e('در حیطه مراقبت های حمایتی و تسکینی سرطان') ?></span>
      </h2>

      <ul class="skill-hero-features">
        <li><?= site_e('خدمات پزشکی و مراقبتی') ?></li>
        <li><?= site_e('مراقبت های روانشناختی و معنوی') ?></li>
        <li><?= site_e('مراقبت های توانبخشی') ?>  </li>
        <li><?= site_e('خدمات مددکاری اجتماعی') ?></li>
        <li><?= site_e('ارائه نجهیزات پزشکی') ?></li>
        <li><?= site_e('مشاوره ژنتیک و غربالگری') ?></li>
        <li><?= site_e('پشتیبانی جامع بیمار و خانواده') ?></li>
        <li><?= site_e('مرکز پاسخگویی ۲۴ ساعته') ?></li>
      </ul>
    </div>

    <div class="skill-hero-right">

      <!-- ستون ویدئو -->
      <div class="skill-video-column">
        <div class="skill-video-box">
          <img src="{{image1}}" alt="<?= site_e('تیم درمان و مراقبت در منزل مکسا') ?>" width="800" height="800" loading="lazy" decoding="async">
        </div>

        <div class="skill-exp-box">
          <span>+16</span>
          <p><?= site_e('سال تجربه') ?></p>
        </div>
      </div>

      <!-- تصویر بلند سمت راست -->
      <div class="skill-main-image">
        <img src="{{image2}}" alt="<?= site_e('خدمات تخصصی مراقبت‌های حمایتی و تسکینی مکسا') ?>" width="600" height="800" loading="lazy" decoding="async">
      </div>

    </div>

  </div>
</section>
<div class="skill-divider"></div>




<style>
.skill-hero{
  padding:40px 0;
  direction:var(--site-direction, rtl);
  position:relative;
  font-family:'Vazirmatn', Tahoma, sans-serif;
}

.skill-hero-container{
  max-width:1200px;
  margin:auto;
  padding-inline:16px;
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:clamp(24px,4vw,48px);
  align-items:center;
  direction:ltr;
}

/* LEFT TEXT */

.skill-hero-left{
  min-width:0;
  direction:var(--site-direction, rtl);
  text-align:center; /* وسط‌چین دسکتاپ */
}

.skill-hero-tag{
  background:#f4f1e6;
  padding:6px 14px;
  border-radius:20px;
  font-size:14px;
  margin-bottom:20px;
  display:inline-block;
}

.skill-hero-title{
  font-size:clamp(26px,3vw,40px);
  line-height:1.7;
  margin:0 0 20px;
}

.skill-hero-title span{
  border-bottom:0px solid #000;
}

.skill-hero-text{
  line-height:2;
  opacity:.8;
  max-width:520px;
  margin-bottom:25px;
}

.skill-hero-features{
  list-style:none;
  padding:0;
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:12px;
  margin-bottom:30px;
}

.skill-hero-features li{
  position:relative;
  padding-inline-start:22px;
}

.skill-hero-features li::before{
  content:"";
  position:absolute;
  inset-inline-start:0;
  top:4px;
  width:15px;
  height:15px;
  background-color:#d4a300;
  -webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E") no-repeat center/contain;
  mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E") no-repeat center/contain;
}

/* RIGHT IMAGES */

.skill-hero-right{
  min-width:0;
  width:100%;
  max-width:590px;
  margin-inline:auto;
  direction:var(--site-direction, rtl);
  display:flex;
  gap:clamp(12px,2.5vw,30px);
  align-items:flex-end; /* به جای flex-start */
  position:relative;
  flex-direction:row-reverse;
}

/* yellow circle */

.skill-hero-right::before{
  content:"";
  position:absolute;
  width:150px;
  height:150px;
  background:#f4c400;
  border-radius:50%;
  top:-40px;
  right:0;
  z-index:0;
}

/* dotted circle */

.skill-hero-right::after{
  content:"";
  position:absolute;
  width:120px;
  height:120px;
  right:20px;
  top:-40px;
  background-image:radial-gradient(#3d2fb3 2px, transparent 2px);
  background-size:10px 10px;
  border-radius:50%;
  z-index:0;
}

/* VIDEO COLUMN */

.skill-video-column{
  display:flex;
  flex-direction:column;
  gap:20px;
  z-index:1;
  flex:0 0 46%;
  min-width:0;
}

.skill-video-box{
  width:100%;
  aspect-ratio:13/17;
  border-radius:20px;
  overflow:hidden;
  position:relative;
}

.skill-video-box img{
  width:100%;
  height:100%;
  object-fit:cover;
}

/* EXPERIENCE */

.skill-exp-box{
  width:100%;
  background:#008B8B;
  color:#fff;
  padding:clamp(10px,2vw,22px);
  border-radius:16px;
  display:flex;
  align-items:center;
  justify-content:center;
  flex-wrap:wrap;
  gap:10px;
}

.skill-exp-box span{
  font-size:clamp(24px,3vw,34px);
  font-weight:bold;
}

.skill-exp-box p{
  margin:0;
  opacity:.9;
}

/* MAIN IMAGE */

.skill-main-image{
  flex:1;
  min-width:0;
  width:auto;
  height:auto;
  aspect-ratio:10/13;
  border-radius:24px;
  overflow:hidden;
  position:relative;
  z-index:1;
}

.skill-main-image img{
  width:100%;
  height:100%;
  object-fit:cover;
}

.skill-divider{
  width:100%;
  height:1px;
  margin:0;
  background:linear-gradient(
    90deg,
    transparent,
    rgba(0,0,0,0.15),
    rgba(0,0,0,0.35),
    rgba(0,0,0,0.15),
    transparent
  );
}


/* RESPONSIVE */

@media(max-width:900px){

  .skill-hero-container{
    grid-template-columns:minmax(0,1fr);
  }

}

@media(max-width:560px){
  .skill-hero{ padding:32px 0; }
}

@media(max-width:480px){
  .skill-hero-features{ grid-template-columns:1fr; }
}

</style>

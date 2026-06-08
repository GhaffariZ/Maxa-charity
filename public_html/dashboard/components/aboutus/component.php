<section class="skill-hero">
  <div class="skill-hero-container">

    <div class="skill-hero-left">
      <h1 class="skill-hero-title">
        خدمات مکسا <br>
        <span>در حیطه مراقبت های حمایتی و تسکینی سرطان</span>
      </h1>

      <ul class="skill-hero-features">
        <li>خدمات پزشکی و مراقبتی</li>
        <li>مراقبت های روانشناختی و معنوی</li>
        <li>مراقبت های توانبخشی  </li>
        <li>خدمات مددکاری اجتماعی</li>
        <li>ارائه نجهیزات پزشکی</li>
        <li>مشاوره ژنتیک و غربالگری</li>
        <li>پشتیبانی جامع بیمار و خانواده</li>
        <li>مرکز پاسخگویی ۲۴ ساعته</li>
      </ul>

      <a href="MACSAservices.html" class="skill-hero-btn">
        اطلاعات بیشتر درباره خدمات مکسا
      </a>
    </div>

    <div class="skill-hero-right">

      <!-- ستون ویدئو -->
      <div class="skill-video-column">
        <div class="skill-video-box">
          <img src="{{image1}}">
        </div>

        <div class="skill-exp-box">
          <span>+16</span>
          <p>سال تجربه</p>
        </div>
      </div>

      <!-- تصویر بلند سمت راست -->
      <div class="skill-main-image">
        <img src="{{image2}}">
      </div>

    </div>

  </div>
</section>
<div class="skill-divider"></div>




<style>
.skill-hero{
  padding:90px 0;
  direction:rtl;
  position:relative;
  font-family:'Vazirmatn', Tahoma, sans-serif;
}

.skill-hero-container{
  max-width:1200px;
  margin:auto;
  display:flex;
  gap:70px;
  align-items:center;
  flex-wrap:wrap;
  flex-direction:row-reverse;
}

/* LEFT TEXT */

.skill-hero-left{
  flex:1;
  min-width:320px;
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
  font-size:40px;
  line-height:1.7;
  margin-bottom:20px;
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
  grid-template-columns:repeat(2,1fr);
  gap:12px;
  margin-bottom:30px;
}

.skill-hero-features li{
  position:relative;
  padding-right:22px;
}

.skill-hero-features li::before{
  content:"✔";
  position:absolute;
  right:0;
  color:#d4a300;
}

.skill-hero-btn{
  background:#f5a623;
  color:#000;
  padding:12px 28px;
  border-radius:10px;
  text-decoration:none;
  display:inline-block;
  font-weight:600;
  transition:all .25s ease;
  box-shadow:0 6px 14px rgba(0,0,0,0.15);
}
/* وقتی موس میره روش */

.skill-hero-btn:hover{
  background:#FFD700;
  transform:translateY(-3px);
  box-shadow:0 10px 20px rgba(0,0,0,0.2);
  cursor:pointer;
}

/* وقتی کلیک میشه */

.skill-hero-btn:active{
  transform:translateY(-1px);
  box-shadow:0 4px 10px rgba(0,0,0,0.15);
}


/* RIGHT IMAGES */

.skill-hero-right{
  flex:1;
  display:flex;
  gap:30px;
  align-items:flex-end; /* به جای flex-start */
  position:relative;
  flex-direction:row-reverse;
}

.skill-main-image{
  width:300px;
  height:390px;
  border-radius:24px;
  overflow:hidden;
  position:relative;
  z-index:1;
}

.skill-video-column{
  display:flex;
  flex-direction:column;
  gap:20px;
  z-index:1;
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
  right:-30px;
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
}

.skill-video-box{
  width:260px;
  height:340px;
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
  width:260px;
  background:#008B8B;
  color:#fff;
  padding:22px;
  border-radius:16px;
  display:flex;
  align-items:center;
  gap:10px;
}

.skill-exp-box span{
  font-size:34px;
  font-weight:bold;
}

.skill-exp-box p{
  margin:0;
  opacity:.9;
}

/* MAIN IMAGE */

.skill-main-image{
  width:300px;
  height:390px;
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
  margin:70px 0;
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
    flex-direction:column;
  }

.skill-hero-left{
  flex:1;
  min-width:320px;
}

  .skill-hero-right{
    flex-direction:row-reverse !important; /* دقیقا مثل دسکتاپ */
    justify-content:center;
    gap:20px;
  }

  .skill-video-column{
    flex-direction:column;
    align-items:center;
  }

}

@media(max-width:560px){
  .skill-hero{ padding:50px 0; }
  .skill-hero-title{ font-size:28px; }
  .skill-hero-features{ grid-template-columns:1fr; }
  .skill-hero-right{ flex-direction:column !important; align-items:center; }
  .skill-main-image,
  .skill-video-box{ width:100%; max-width:300px; height:auto; aspect-ratio:3/4; }
  .skill-exp-box{ width:100%; max-width:300px; }
}

</style>

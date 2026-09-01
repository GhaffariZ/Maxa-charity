<section class="macsa-hero-section">

<!-- عنوان -->
<div class="macsa-hero-header">
  <h1>
    همراهی مکسا در مسیر درمان و امید
    <br>
    <span>راه ارتباطی شما با شبکه درمان، حمایت و همراهی مکسا در سراسر کشور</span>
  </h1>

  <p>
    در مکسا تنها نیستید؛ شبکه‌ای از همراهان، درمانگران و حامیان کنار شماست.
  </p>
</div>

<div class="macsa-gallery">

  <a href="/branches.php" class="gallery-item">
    <div class="card card-lg">
      <img src="{{image1}}">
    </div>
    <span class="macsa-btn">شعب مکسا</span>
  </a>

  <a href="/contact-center" class="gallery-item">
    <div class="card card-md">
      <img src="{{image2}}">
    </div>
    <span class="macsa-btn">مرکز تماس کشوری</span>
  </a>

  <a href="https://mymacsa.ir/single-fundraising-option" class="gallery-item">
    <div class="card card-sm">
      <img src="{{image3}}">
    </div>
    <span class="macsa-btn">حامیان و داوطلبان</span>
  </a>

  <a href="javascript:void(0)" class="gallery-item" aria-disabled="true">
    <div class="card card-md">
      <img src="{{image4}}">
    </div>
    <span class="macsa-btn">کادر درمان</span>
  </a>

  <a href="javascript:void(0)" class="gallery-item" aria-disabled="true">
    <div class="card card-lg">
      <img src="{{image5}}">
    </div>
    <span class="macsa-btn">بیماران و مراقبین</span>
  </a>

</div>

<!-- نقاط راهنما (فقط موبایل) -->
<div class="macsa-dots" aria-hidden="true">
  <button class="macsa-dot" type="button"></button>
  <button class="macsa-dot" type="button"></button>
  <button class="macsa-dot" type="button"></button>
  <button class="macsa-dot" type="button"></button>
  <button class="macsa-dot" type="button"></button>
</div>

</section>
<div class="skill-divider"></div>



<style>
.macsa-hero-section{
  background:transparent; /* یا کلاً این خط را حذف کن */
  width:100%;
  padding:80px 0 70px;
  text-align:center;
  font-family:'Vazirmatn', Tahoma, sans-serif;
}

/* عنوان */
.macsa-hero-header{
  max-width:780px;
  margin:0 auto 60px;
  padding:0 16px;
}

.macsa-hero-header h1{
  font-size:2.2rem;
  font-weight:800;
  line-height:1.4;
  margin-bottom:18px;
}

.macsa-hero-header h1 span{
  color: teal;      /* سبز teal */
  font-size:0.7em;  /* کمی کوچکتر از متن اصلی */
  font-weight:500;  /* کمی سبک‌تر */
  display:inline-block;
}

.macsa-hero-header p{
  font-size:1.05rem;
  color:#666;
  line-height:1.9;
}

/* گالری */
.macsa-gallery{
  max-width:1200px;
  margin:auto;
  display:flex;
  justify-content:center;
  align-items:flex-end;
  gap:22px;
}

/* کارت‌ها */
.macsa-gallery .card{
  position:relative;
  overflow:hidden;
  border-radius:22px;
  cursor:pointer;
  transition:all .35s ease;
  flex-shrink:0;
}

.macsa-gallery .card img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
  transition:transform .45s ease;
}

/* اندازه‌ها */
.card-lg{
  width:210px;
  height:380px;
}

.card-md{
  width:200px;
  height:320px;
}

.card-sm{
  width:190px;
  height:260px;
}

/* افکت هاور */
.macsa-gallery .card:hover{
  transform:translateY(-12px) scale(1.035);
  box-shadow:0 28px 55px rgba(0,0,0,0.15);
}

.macsa-gallery .card:hover img{
  transform:scale(1.08);
}

/* افکت انتخاب */
.macsa-gallery .card::after{
  content:"";
  position:absolute;
  inset:0;
  border-radius:22px;
  border:2px solid transparent;
  transition:.3s ease;
}

.macsa-gallery .card:hover::after{
  border-color:#1f7a63;
}

.gallery-item{
  display:flex;
  flex-direction:column;
  align-items:stretch; /* دکمه کل عرض آیتم را بگیرد */
  gap:12px;
  width:fit-content;   /* عرض آیتم = عرض کارت */
}

.gallery-item a{
  text-decoration: none;
  color: inherit;
  display: block;
}


/* دکمه زیر عکس */
.macsa-btn{
  width:100%;          /* دقیقاً هم‌عرض عکس */
  background:#f5a623;
  border:none;
  padding:12px 0;
  border-radius:14px;
  font-weight:700;
  font-size:.95rem;
  color:#3b2c00;
  cursor:pointer;
  box-shadow:0 8px 18px rgba(212,175,55,0.35);
  transition:.3s ease;
}

.macsa-btn:hover{
  transform:translateY(-3px);
  box-shadow:0 12px 26px rgba(212,175,55,0.5);
}

/* نقاط راهنما — به‌صورت پیش‌فرض پنهان (فقط در موبایل دیده می‌شود) */
.macsa-dots{ display:none; }

/* نسخه دسکتاپ دست نخورده بماند... */

/* تبلت: کارت‌ها را کوچک‌تر کن تا ردیف ۵تایی جا شود */
@media (max-width:1024px) and (min-width:701px){
  .macsa-gallery{ gap:14px; flex-wrap:wrap; }
  .card-lg{ width:170px; height:300px; }
  .card-md{ width:160px; height:260px; }
  .card-sm{ width:150px; height:220px; }
  .macsa-hero-header h1{ font-size:1.9rem; }
}

@media (max-width:700px){

  /* اسلایدر افقی قابل کشیدن با انگشت (swipe) */
  .macsa-gallery{
    position:relative;
    width:100%;
    height:auto;
    display:flex;
    flex-wrap:nowrap;
    justify-content:flex-start;
    align-items:flex-end;
    gap:16px;
    overflow-x:auto;
    overflow-y:hidden;
    scroll-snap-type:x mandatory;
    -webkit-overflow-scrolling:touch;
    scroll-padding:0 10vw;
    padding:10px 10vw 16px;   /* نمایش گوشهٔ کارت بعدی/قبلی */
    scrollbar-width:none;      /* Firefox */
  }
  .macsa-gallery::-webkit-scrollbar{ display:none; } /* WebKit */

  .gallery-item{
    position:static;
    flex:0 0 80vw;
    width:80vw;
    transform:none;
    opacity:1;
    z-index:auto;
    scroll-snap-align:center;
  }

  .card{
    width:100%;
    height:70vh;        /* کشیده‌تر */
    border-radius:22px;
    overflow:hidden;
  }

  .card img{
    width:100%;
    height:100%;
    object-fit:cover;
  }

  /* ✅ دکمه طلایی دقیقاً مثل دسکتاپ */
  .macsa-btn{
    display:block;
    margin-top:18px;
    padding:14px 0;
    text-align:center;
    border-radius:30px;
    font-weight:600;
    background:#c9a227;   /* طلایی */
    color:#fff;
    text-decoration:none;
    box-shadow:0 6px 18px rgba(0,0,0,.15);
  }

  /* نقاط راهنما زیر اسلایدر */
  .macsa-dots{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:10px;
    margin-top:18px;
  }
  .macsa-dot{
    width:9px;
    height:9px;
    padding:0;
    border:none;
    border-radius:50%;
    background:#d9d9d9;
    cursor:pointer;
    transition:transform .25s ease, background .25s ease;
  }
  .macsa-dot.active{
    background:#c9a227;   /* طلایی */
    transform:scale(1.35);
  }

}

</style>
<script>
document.addEventListener("DOMContentLoaded", function(){

  const gallery = document.querySelector(".macsa-gallery");
  const cards   = document.querySelectorAll(".gallery-item");
  const dots    = document.querySelectorAll(".macsa-dot");
  if(!gallery || !cards.length) return;

  const isMobile = () => window.matchMedia("(max-width:700px)").matches;

  /* ---------- نسخهٔ دسکتاپ: همان فید قبلی ---------- */
  let current = 0;
  let fadeTimer = null;

  function showCard(index){
    cards[current].classList.remove("active");
    current = index;
    cards[current].classList.add("active");
  }

  /* ---------- نسخهٔ موبایل: اسلایدر قابل کشیدن + پخش خودکار ملایم ---------- */
  let autoTimer  = null;
  let pauseUntil = 0;   // تا این لحظه پخش خودکار متوقف است (پس از تعامل کاربر)

  function activeSlide(){
    const center = gallery.scrollLeft + gallery.clientWidth / 2;
    let best = 0, bestDist = Infinity;
    cards.forEach((c, i)=>{
      const cc = c.offsetLeft + c.offsetWidth / 2;
      const d  = Math.abs(cc - center);
      if(d < bestDist){ bestDist = d; best = i; }
    });
    return best;
  }

  function goTo(i){
    const card = cards[(i + cards.length) % cards.length];
    const left = card.offsetLeft - (gallery.clientWidth - card.offsetWidth) / 2;
    gallery.scrollTo({ left: left, behavior: "smooth" });
  }

  function syncDots(){
    if(!dots.length) return;
    const active = activeSlide();
    dots.forEach((d, i)=> d.classList.toggle("active", i === active));
  }

  // کلیک روی نقطه = رفتن به همان اسلاید
  dots.forEach((dot, i)=>{
    dot.addEventListener("click", ()=>{
      pauseUntil = Date.now() + 6000;
      goTo(i);
    });
  });

  // به‌روزرسانی نقطهٔ فعال هنگام اسکرول/کشیدن
  let scrollRaf = null;
  gallery.addEventListener("scroll", ()=>{
    if(scrollRaf) return;
    scrollRaf = requestAnimationFrame(()=>{ scrollRaf = null; syncDots(); });
  }, { passive:true });

  function stopAuto(){ if(autoTimer){ clearInterval(autoTimer); autoTimer = null; } }
  function startAuto(){
    stopAuto();
    autoTimer = setInterval(()=>{
      if(Date.now() < pauseUntil) return;          // کاربر در حال تعامل است
      goTo(activeSlide() + 1);
    }, 3500);
  }

  // هر تعامل کاربر، پخش خودکار را برای چند ثانیه متوقف می‌کند
  ["touchstart","pointerdown","wheel","scroll"].forEach(ev=>{
    gallery.addEventListener(ev, ()=>{ pauseUntil = Date.now() + 6000; }, { passive:true });
  });

  function setup(){
    stopAuto();
    if(fadeTimer){ clearInterval(fadeTimer); fadeTimer = null; }
    cards.forEach(c=>c.classList.remove("active"));

    if(isMobile()){
      gallery.scrollLeft = 0;
      syncDots();
      startAuto();
    } else {
      current = 0;
      showCard(0);
      fadeTimer = setInterval(()=>{ showCard((current + 1) % cards.length); }, 2500);
    }
  }

  setup();

  let resizeTimer;
  window.addEventListener("resize", ()=>{
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(setup, 200);
  });

});
</script>

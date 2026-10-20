<section class="macsa-hero-section">

<!-- عنوان -->
<div class="macsa-hero-header">
  <h2>
    همراهی مکسا در مسیر درمان و امید
    <br>
    <span>راه ارتباطی شما با شبکه درمان، حمایت و همراهی مکسا در سراسر کشور</span>
  </h2>

  <p>
    در مکسا تنها نیستید؛ شبکه‌ای از همراهان، درمانگران و حامیان کنار شماست.
  </p>
</div>

<div class="macsa-gallery">

  <a href="/branches.php" class="gallery-item">
    <div class="card card-lg">
      <img src="/dashboard/components/hamrah/images/1.webp" alt="شعب مکسا" width="600" height="600" loading="lazy" decoding="async">
    </div>
    <span class="macsa-btn">شعب مکسا</span>
  </a>

  <a href="/contact-center" class="gallery-item">
    <div class="card card-md">
      <img src="/dashboard/components/hamrah/images/2.webp" alt="مرکز تماس کشوری" width="600" height="600" loading="lazy" decoding="async">
    </div>
    <span class="macsa-btn">مرکز تماس کشوری</span>
  </a>

  <a href="https://mymacsa.ir/single-fundraising-option" class="gallery-item">
    <div class="card card-sm">
      <img src="/dashboard/components/hamrah/images/3.webp" alt="حامیان و داوطلبان" width="600" height="600" loading="lazy" decoding="async">
    </div>
    <span class="macsa-btn">حامیان و داوطلبان</span>
  </a>

  <a href="/doctorspage" class="gallery-item">
    <div class="card card-md">
      <img src="/dashboard/components/hamrah/images/4.webp" alt="کادر درمان" width="600" height="600" loading="lazy" decoding="async">
    </div>
    <span class="macsa-btn">کادر درمان</span>
  </a>

  <a href="/patient-option" class="gallery-item">
    <div class="card card-lg">
      <img src="/dashboard/components/hamrah/images/5.webp" alt="بیماران و مراقبین" width="600" height="600" loading="lazy" decoding="async">
    </div>
    <span class="macsa-btn">بیماران و مراقبین</span>
  </a>

</div>

<!-- نقاط راهنما (فقط موبایل) -->
<div class="macsa-dots">
  <button class="macsa-dot" type="button" aria-label="اسلاید ۱"></button>
  <button class="macsa-dot" type="button" aria-label="اسلاید ۲"></button>
  <button class="macsa-dot" type="button" aria-label="اسلاید ۳"></button>
  <button class="macsa-dot" type="button" aria-label="اسلاید ۴"></button>
  <button class="macsa-dot" type="button" aria-label="اسلاید ۵"></button>
</div>

</section>



<style>
.macsa-hero-section{
  background:transparent; /* یا کلاً این خط را حذف کن */
  width:100%;
  padding:40px 0;
  text-align:center;
  font-family:'Vazirmatn', Tahoma, sans-serif;
}

/* عنوان */
.macsa-hero-header{
  max-width:780px;
  margin:0 auto 32px;
  padding:0 16px;
}

.macsa-hero-header h1,
.macsa-hero-header h2{
  font-size:2.2rem;
  font-weight:800;
  line-height:1.4;
  margin-bottom:18px;
}

.macsa-hero-header h1 span,
.macsa-hero-header h2 span{
  color: #008f8a;   /* سبز برند مکسا */
  font-size:0.72em; /* کمی کوچکتر از متن اصلی */
  font-weight:700;  /* وزن مشخص‌تر */
  display:inline-block;
  margin-top:8px;
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
  padding-inline:16px;
  display:grid;
  grid-template-columns:repeat(5,minmax(0,1fr));
  align-items:end;
  gap:clamp(12px,1.8vw,22px);
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
.macsa-gallery .card-lg{
  height:clamp(260px,30vw,380px);
}

.macsa-gallery .card-md{
  height:clamp(230px,26vw,320px);
}

.macsa-gallery .card-sm{
  height:clamp(200px,22vw,260px);
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

.macsa-gallery > .gallery-item{
  display:flex;
  flex-direction:column;
  align-items:stretch; /* دکمه کل عرض آیتم را بگیرد */
  gap:12px;
  min-width:0;
  width:100%;
}

.macsa-gallery > .gallery-item,
.macsa-gallery > .gallery-item:hover{
  text-decoration: none;
  color: inherit;
}


/* دکمه زیر عکس */
.macsa-btn{
  width:100%;          /* دقیقاً هم‌عرض عکس */
  background:#faa61a;
  border:none;
  padding:12px 6px;
  min-height:48px;
  display:grid;
  place-items:center;
  line-height:1.6;
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

.macsa-hero-header h2{
  font-size:clamp(26px,3.5vw,35px);
}

@media (max-width:700px){

  /* اسلایدر افقی قابل کشیدن با انگشت (swipe) */
  .macsa-gallery{
    --gallery-card-width:min(80vw,320px);
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
    scroll-padding-inline:calc((100% - var(--gallery-card-width))/2);
    padding:10px calc((100% - var(--gallery-card-width))/2) 16px;
    scrollbar-width:none;      /* Firefox */
  }
  .macsa-gallery::-webkit-scrollbar{ display:none; } /* WebKit */

  .macsa-gallery > .gallery-item{
    position:static;
    flex:0 0 var(--gallery-card-width);
    width:var(--gallery-card-width);
    transform:none;
    opacity:1;
    z-index:auto;
    scroll-snap-align:center;
  }

  .macsa-gallery .card{
    width:100%;
    height:auto;
    aspect-ratio:3/4;
    border-radius:22px;
    overflow:hidden;
  }

  .macsa-gallery .card img{
    width:100%;
    height:100%;
    object-fit:cover;
  }

  /* دکمه طلایی دقیقاً مثل دسکتاپ */
  .macsa-btn{
    display:block;
    margin-top:0;
    padding:14px 0;
    text-align:center;
    border-radius:30px;
    font-weight:600;
    background:#faa61a;
    color:#3b2c00;
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
(function(){
  function initHamrah(){
    const gallery = document.querySelector(".macsa-gallery");
    const cards   = gallery ? gallery.querySelectorAll(".gallery-item") : [];
    const dots    = document.querySelectorAll(".macsa-dot");
    if(!gallery || !cards.length) return;

    const isMobile = () => window.matchMedia("(max-width:700px)").matches;

    /* ---------- نسخهٔ موبایل: اسلایدر قابل کشیدن + پخش خودکار ملایم ---------- */
    let autoTimer  = null;
    let pauseUntil = 0;   // تا این لحظه پخش خودکار متوقف است (پس از تعامل کاربر)

    function activeSlide(){
      const bounds = gallery.getBoundingClientRect();
      const center = bounds.left + bounds.width / 2;
      let best = 0, bestDist = Infinity;
      cards.forEach((c, i)=>{
        const rect = c.getBoundingClientRect();
        const cc = rect.left + rect.width / 2;
        const d  = Math.abs(cc - center);
        if(d < bestDist){ bestDist = d; best = i; }
      });
      return best;
    }

    function goTo(i){
      const card = cards[(i + cards.length) % cards.length];
      const bounds = gallery.getBoundingClientRect();
      const rect = card.getBoundingClientRect();
      // Relative geometry works with negative RTL scrollLeft as well as LTR.
      const left = rect.left + rect.width / 2 - bounds.left - bounds.width / 2;
      gallery.scrollBy({ left, behavior: "smooth" });
    }

    function syncDots(){
      if(!dots.length) return;
      const active = activeSlide();
      dots.forEach((d, i)=>{
        d.classList.toggle("active", i === active);
        d.setAttribute("aria-current", i === active ? "true" : "false");
      });
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

      if(isMobile()){
        gallery.scrollLeft = 0;
        syncDots();
        if(!window.matchMedia("(prefers-reduced-motion:reduce)").matches) startAuto();
      }
    }

    setup();

    let resizeTimer;
    window.addEventListener("resize", ()=>{
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(setup, 200);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initHamrah);
  } else {
    initHamrah();
  }
})();
</script>

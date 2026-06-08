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

  <a href="/branches.html" class="gallery-item">
    <div class="card card-lg">
      <img src="{{image1}}">
    </div>
    <span class="macsa-btn">شعب مکسا</span>
  </a>

  <a href="/contactcenter.html" class="gallery-item">
    <div class="card card-md">
      <img src="{{image2}}">
    </div>
    <span class="macsa-btn">مرکز تماس کشوری</span>
  </a>

  <a href="/publicparticipation.html" class="gallery-item">
    <div class="card card-sm">
      <img src="{{image3}}">
    </div>
    <span class="macsa-btn">حامیان و داوطلبان</span>
  </a>

  <a href="/clinicians.html" class="gallery-item">
    <div class="card card-md">
      <img src="{{image4}}">
    </div>
    <span class="macsa-btn">کادر درمان</span>
  </a>

  <a href="/patient.html" class="gallery-item">
    <div class="card card-lg">
      <img src="{{image5}}">
    </div>
    <span class="macsa-btn">بیماران و مراقبین</span>
  </a>

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

  .macsa-gallery{
    position:relative;
    width:100%;
    height:85vh;
    overflow:hidden;
  }

.gallery-item{
  position:absolute;
  top:50%;
  left:50%;
  width:88vw;
  transform:translate(-50%, -50%);
  opacity:0;
  z-index:1;
  transition:opacity 0.2s linear;  /* ← سرعت تعض کارت = 1 ثانیه */
}

.gallery-item.active{
  opacity:1;
  z-index:3;
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

}

</style>
<script>
document.addEventListener("DOMContentLoaded", function(){

  const cards = document.querySelectorAll(".gallery-item");
  let current = 0;

  function showCard(index){
    cards[current].classList.remove("active");
    current = index;
    cards[current].classList.add("active");
  }

  showCard(0);

  // ⏱ توقف روی هر کارت = 2.5 ثانیه
  // ⚡ سرعت تعویض کارت = 1 ثانیه (در CSS)
  setInterval(()=>{
    let next = (current + 1) % cards.length;
    showCard(next);
  }, 2500);  // ← زمان توقف روی کارت

});
</script>

<!-- اضافه کردن این خط به <head> سایت برای نمایش آیکون‌ها -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<section class="support-methods">
    <div class="container">
        <div class="support-grid">
            
            <a href="onlinedonation.html" class="support-card">
                <div class="card-icon heart"><i class="fa-solid fa-hand-holding-heart"></i></div>
                <h3>حمایت مالی آنلاین</h3>
                <p>هرکمک، نوری در دل تاریکی است</p>
            </a>

            <a href="Vows.html" class="support-card">
                <div class="card-icon vow"><i class="fa-solid fa-dove"></i></div>
                <h3>نذورات</h3>
                <p>با هر نذری، همراهی و امید می‌بارد</p>
            </a>

            <a href="/dashboard/stand/stand-customize.php" class="support-card">
                <div class="card-icon flower"><i class="fa-solid fa-seedling"></i></div>
                <h3>استند تبریک و تسلیت</h3>
                <p>هم‌قدم با شما در جشن زندگی و آرامش در لحظه‌های فقدان</p>
            </a>

            <a href="piggybank.html" class="support-card">
                <div class="card-icon pig"><i class="fa-solid fa-piggy-bank"></i></div>
                <h3>قلک مکسا</h3>
                <p>هر سکه، رویانِ امید در دل بیماران</p>
            </a>

            <a href="professionalaid.html" class="support-card">
                <div class="card-icon science"><i class="fa-solid fa-dna"></i></div>
                <h3>حمایت های علمی و تخصصی</h3>
                <p>با دانش، مسیر درمان را تغییر دهیم</p>
            </a>

            <a href="csr.html" class="support-card">
                <div class="card-icon handshake"><i class="fa-solid fa-handshake"></i></div>
                <h3>مسئولیت اجتماعی</h3>
                <p>دست در دست برند شما برای همراهی بیماران مبتلا به سرطان</p>
            </a>

            <a href="onlinedonation.html" class="support-card">
                <div class="card-icon art"><i class="fa-solid fa-palette"></i></div>
                <h3>حمایت خلاقانه و هنری</h3>
                <p>خرید کن، هدیه بده، زندگی ببخش</p>
            </a>

            <a href="getinvolved.html" class="support-card">
                <div class="card-icon volunteer"><i class="fa-solid fa-people-group"></i></div>
                <h3>حامیان و داوطلبان</h3>
                <p>با هر قدم داوطلبانه، داستانی از شفا نوشته می‌شود</p>
            </a>

            <a href="equipmentaid.html" class="support-card">
                <div class="card-icon medical"><i class="fa-solid fa-hospital"></i></div>
                <h3>اهدای تجهیزات پزشکی</h3>
                <p>ایستگاه همدلی: اهدای تجهیزات، پشتیبانی بدون کلام</p>
            </a>

        </div>
    </div>
</section>
<style>
/* Self-hosted Vazirmatn variable font (reliable on the Iran network, no external CDN) */
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
       url('/webfont/Vazirmatn[wght].woff2') format('woff2');
  font-weight: 100 900;
  font-style: normal;
  font-display: swap;
}
/* =========================
   Support Section - Footer Inspired
========================= */
  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }

/* =========================
   Support Section
========================= */

.support-methods{
    padding:100px 20px;
    background:#ffffff;
    direction:rtl;
}

.support-grid{
    max-width:1200px;
    margin:auto;
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:26px;
}


/* =========================
   Premium Buttons (Cards)
========================= */

.support-card{

    position:relative;
    overflow:hidden;

    padding:42px 30px;
    border-radius:22px;

    text-decoration:none;
    text-align:center;

    background:#ffffff;

    border:1px solid rgba(16,174,184,0.15);

    box-shadow:
        0 12px 30px rgba(0,0,0,0.05),
        0 2px 6px rgba(0,0,0,0.04),
        inset 0 1px 0 rgba(255,255,255,0.9);

    transition:
        transform .35s cubic-bezier(.21,.98,.6,.99),
        box-shadow .35s ease,
        border-color .35s ease;
}


/* subtle gradient glow */

.support-card::before{

    content:"";
    position:absolute;
    inset:0;
    border-radius:22px;

    background:
    linear-gradient(135deg,
    rgba(16,174,184,0.08),
    rgba(16,174,184,0.02));

    opacity:0;

    transition:.35s;
}

.support-card:hover::before{
    opacity:1;
}


/* bottom accent line */

.support-card::after{

    content:"";
    position:absolute;
    bottom:0;
    left:50%;

    width:0;
    height:3px;

    background:linear-gradient(90deg,#10aeb8,#07828e);

    border-radius:2px;

    transform:translateX(-50%);
    transition:.35s;
}

.support-card:hover::after{
    width:60%;
}


/* hover motion */

.support-card:hover{

    transform:translateY(-10px);

    border-color:#10aeb8;

    box-shadow:
        0 28px 60px rgba(16,174,184,0.20),
        0 8px 20px rgba(0,0,0,0.05);
}


/* =========================
   Icon Badge
========================= */

.card-icon{

    width:64px;
    height:64px;

    margin:0 auto 18px;

    border-radius:18px;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:24px;

    background:
    linear-gradient(145deg,
    rgba(16,174,184,0.12),
    rgba(16,174,184,0.04));

    border:1px solid rgba(16,174,184,0.25);

    box-shadow:
        0 10px 25px rgba(16,174,184,0.15),
        inset 0 1px 0 rgba(255,255,255,0.9);

    transition:all .35s ease;
}


/* icon hover */

.support-card:hover .card-icon{

    background:linear-gradient(145deg,#10aeb8,#07828e);

    color:#ffffff;

    transform:scale(1.1) rotate(-3deg);

    box-shadow:
        0 16px 40px rgba(16,174,184,0.35);
}


/* =========================
   Typography
========================= */

.support-card h3{

    margin:0 0 10px 0;

    font-size:18px;
    font-weight:900;

    color:#0f172a;

    transition:.25s;
}

.support-card:hover h3{
    color:#07828e;
}

.support-card p{

    margin:0;

    font-size:14px;
    line-height:1.9;

    color:#64748b;
}


/* =========================
   Responsive
========================= */

@media(max-width:1000px){
.support-grid{
grid-template-columns:repeat(2,1fr);
}
}

@media(max-width:600px){
.support-grid{
grid-template-columns:1fr;
}

.support-card{
padding:32px 22px;
}
}


</style>

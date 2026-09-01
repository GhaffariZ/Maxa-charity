<section class="travel-stats-section" dir="rtl">

<div class="travel-box">

<div class="travel-stats-container">

<div class="travel-content">

<!-- RIGHT SIDE -->
<div class="travel-right">

<div class="travel-header">
<h2>روایتی از آمار مکسا</h2>
<p>
این دستاورد حاصل اعتماد و همراهی شما، تلاش همکاران و حمایت ارزشمند خیرینی است که در مسیر خدمت به بیماران در کنار ما هستند.
</p>
</div>

<div class="travel-stats-grid">

<div class="travel-stat">
<div class="travel-number" data-target="110392">۰</div>
<div class="travel-label">جذب کمک های مردمی</div>
</div>

<div class="travel-stat">
<div class="travel-number" data-target="16">۰</div>
<div class="travel-label">سال فعالیت</div>
</div>

<div class="travel-stat">
<div class="travel-number" data-target="690000">۰</div>
<div class="travel-label">خدمات مکسا</div>
</div>

<div class="travel-stat">
<div class="travel-number" data-target="59000">۰</div>
<div class="travel-label">تعداد کل بیماران</div>
</div>

</div>
</div>


<!-- LEFT SIDE: Responsive SVG Map of Iran -->
<div class="travel-left">

<div class="iran-map-box">

<?php require __DIR__ . '/../branches/map-svg.php'; ?>

<div class="map-quick-legend">
  <span class="mql-item">
    <span class="mql-dot mql-dot--active"></span>
    استان‌های دارای شعبه فعال
  </span>
  <span class="mql-item">
    <span class="mql-dot mql-dot--cover"></span>
    سایر استان‌های کشور
  </span>
  <a href="/branches.php" class="mql-link">مشاهده شعب و مراکز مکسا &larr;</a>
</div>

</div>

</div>

</div>
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

.travel-stats-section{
padding:70px 20px 80px;
background:transparent;
font-family:'Vazirmatn', Tahoma, sans-serif;
direction:rtl;
}

/* box */
.travel-box{
max-width:1280px;
margin:auto;
position:relative;
overflow:hidden;
background:#ffffff;
border:1px solid rgba(16,174,184,0.16);
padding:46px 40px;
border-radius:28px;
box-shadow:
0 20px 50px rgba(0,0,0,.04),
0 2px 8px rgba(0,0,0,.02);
color:#0f5f63;
}

.travel-content{
display:flex;
align-items:center;
justify-content:space-between;
gap:48px;
}

.travel-right{
flex:1 1 500px;
min-width:0;
text-align:right;
}

.travel-left{
flex:1 1 560px;
min-width:0;
display:flex;
flex-direction:column;
align-items:center;
justify-content:center;
}

/* header */
.travel-header{
margin-bottom:34px;
}

.travel-header h2{
font-size:clamp(26px, 3.2vw, 36px);
margin:0 0 14px 0;
font-weight:900;
color:#063a3c;
line-height:1.3;
}

.travel-header p{
font-size:15.5px;
line-height:1.9;
color:#4a6b6d;
font-weight:500;
margin:0;
max-width:580px;
}

/* stats grid */
.travel-stats-grid{
display:grid;
grid-template-columns:repeat(2, minmax(0, 1fr));
gap:18px;
margin-top:20px;
}

.travel-stat{
background:#f8fbfb;
border:1px solid #e2f0ef;
border-radius:18px;
padding:22px 20px;
text-align:center;
transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}

.travel-stat:hover{
transform:translateY(-4px);
box-shadow:0 12px 28px rgba(0,123,122,.09);
border-color:#10aeb8;
}

.travel-number{
font-size:clamp(28px, 3vw, 38px);
font-weight:900;
color:#007b7a;
line-height:1.2;
margin-bottom:6px;
letter-spacing:-0.5px;
}

.travel-label{
font-size:14px;
font-weight:600;
color:#5a7375;
}

/* map */
.iran-map-box{
width:100%;
position:relative;
display:flex;
flex-direction:column;
align-items:center;
}

.iran-map-box #Iran{
display:block;
width:100%;
max-width:560px;
height:auto;
margin:0 auto;
transform:none;
filter:drop-shadow(0 8px 20px rgba(0,123,122,.06));
}

/* Map SVG styling */
.iran-map-box #Iran .province-shape.is-inactive,
.iran-map-box #Iran path.province-shape.is-inactive,
.iran-map-box #Iran polygon.province-shape.is-inactive {
fill: #e6ebec !important;
stroke: #c8d3d5 !important;
stroke-width: 0.85 !important;
cursor: default !important;
pointer-events: none !important;
filter: none !important;
}

.iran-map-box #Iran a.province-link {
cursor: pointer;
outline: none;
}

.iran-map-box #Iran path.province-shape.is-active,
.iran-map-box #Iran polygon.province-shape.is-active {
fill: #007b7a;
stroke: #004d4c;
stroke-width: 1.2;
transition: all 0.25s ease;
cursor: pointer;
}

.iran-map-box #Iran a.province-link:hover path.province-shape.is-active,
.iran-map-box #Iran a.province-link:hover polygon.province-shape.is-active {
fill: #10aeb8;
filter: drop-shadow(0 6px 14px rgba(0,123,122,.4));
}


/* Quick Legend */
.map-quick-legend{
display:flex;
align-items:center;
justify-content:center;
gap:16px 22px;
flex-wrap:wrap;
margin-top:16px;
padding-top:14px;
border-top:1px dashed #e2e8ea;
width:100%;
font-size:13px;
color:#4a6b6d;
}

.mql-item{
display:inline-flex;
align-items:center;
gap:7px;
font-weight:600;
}

.mql-dot{
width:13px;
height:13px;
border-radius:4px;
display:inline-block;
}

.mql-dot--active{
background:#10aeb8;
border:1px solid #007b7a;
}

.mql-dot--cover{
background:#f4a61e;
border:1px solid #d98c0a;
}

.mql-link{
color:#007b7a;
text-decoration:none;
font-weight:700;
transition:color .2s, transform .2s;
display:inline-flex;
align-items:center;
gap:4px;
}

.mql-link:hover{
color:#f4a61e;
transform:translateX(-3px);
}

/* Responsive */
@media(max-width:992px){
.travel-box{
padding:36px 24px;
border-radius:22px;
}

.travel-content{
flex-direction:column;
gap:36px;
}

.travel-right{
text-align:center;
flex:1 1 100%;
width:100%;
}

.travel-header p{
margin:0 auto;
}

.travel-left{
flex:1 1 100%;
width:100%;
}

.iran-map-box #Iran{
max-width:480px;
}
}

@media(max-width:600px){
.travel-stats-section{
padding:40px 14px 60px;
}

.travel-box{
padding:26px 16px;
border-radius:18px;
}

.travel-stats-grid{
grid-template-columns:repeat(2, minmax(0, 1fr));
gap:12px;
}

.travel-stat{
padding:16px 10px;
border-radius:14px;
}

.travel-number{
font-size:24px;
}

.travel-label{
font-size:12px;
}

.iran-map-box #Iran{
max-width:100%;
}

.map-quick-legend{
font-size:12px;
gap:10px 14px;
}
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const counters = document.querySelectorAll(".travel-stats-section .travel-number");
  if (!counters.length) return;

  const startCounter = (counter) => {
    const target = parseInt(counter.dataset.target, 10);
    if (isNaN(target)) return;

    const duration = 1800; // ms
    const startTime = performance.now();

    const updateCount = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
      const currentVal = Math.floor(ease * target);
      
      counter.innerText = currentVal.toLocaleString('fa-IR');

      if (progress < 1) {
        requestAnimationFrame(updateCount);
      } else {
        counter.innerText = target.toLocaleString('fa-IR');
      }
    };

    requestAnimationFrame(updateCount);
  };

  const section = document.querySelector(".travel-stats-section");
  if (!section) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        counters.forEach((counter) => startCounter(counter));
        observer.disconnect();
      }
    });
  }, { threshold: 0.2 });

  observer.observe(section);
});
</script>
<section class="pro-stories" dir="rtl">

  <div class="ps-global-bg"></div>

  <div class="ps-container">

    <!-- HEADER -->
    <h2 class="ps-title">
      بانک روایت‌های امید مکسا<br>
      <span>قصه‌های ایستادگی نجات‌یافتگان و همراهان آن‌ها</span>
    </h2>

    <!-- FILTERS -->
    <div class="ps-filters">
      <button class="active" data-filter="all">همه</button>
      <button data-filter="diagnosis">زندگی پس از تشخیص بیماری</button>
      <button data-filter="treatment">مسیر درمان</button>
      <button data-filter="recovery">زندگی پس از بهبودی</button>
      <button data-filter="family">تجربه همراهان</button>
      <button data-filter="staff">روایت کادر درمان</button>
    </div>

    <!-- STORIES GRID -->
    <div class="ps-grid">

      <!-- STORY CARD -->
      <div class="ps-card" data-category="staff">
          <span class="ps-tag">روایت کادر درمان</span>
          <h3>از بحران تا ثبات؛ تجربه‌ای از همراهی مستمر اجتماعی</h3>
          <p>سرپرست یک خانواده به دلیل درگیری مغزی ناشی از سرطان، بستری شده بود ...</p>
<div class="ps-footer">
<span class="ps-time">۴ دقیقه مطالعه</span>
<a href="#" class="ps-read">
خواندن روایت
<svg width="16" height="16" viewBox="0 0 24 24">
<path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none"/>
</svg>
</a>
</div>

      </div>

      <div class="ps-card" data-category="staff">
          <span class="ps-tag">روایت کادر درمان</span>
          <h3>وقتی نوشتن، درمان است</h3>
          <p>در تابستان امسال، خانمی در دهه پنجم زندگی‌اش با تشخیص سرطان پستان متاستاز داده به مکسا ارجاع داده شد...</p>
<div class="ps-footer">
<span class="ps-time">۴ دقیقه مطالعه</span>
<a href="#" class="ps-read">
خواندن روایت
<svg width="16" height="16" viewBox="0 0 24 24">
<path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none"/>
</svg>
</a>
</div>
      </div>

      <div class="ps-card" data-category="staff">
          <span class="ps-tag">روایت کادر درمان</span>
          <h3>زندگی تا آخرین لحظه</h3>
          <p>خانم جوانی بود که سال‌ها در آمریکا زندگی کرده بود...</p>
<div class="ps-footer">
<span class="ps-time">۴ دقیقه مطالعه</span>
<a href="#" class="ps-read">
خواندن روایت
<svg width="16" height="16" viewBox="0 0 24 24">
<path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none"/>
</svg>
</a>
</div>
      </div>

      <div class="ps-card" data-category="recovery">
          <span class="ps-tag">پس از بهبودی</span>
          <h3>بازگشت به زندگی</h3>
          <p>تجربه بیمار در دوران پس از درمان...</p>
<div class="ps-footer">
<span class="ps-time">۴ دقیقه مطالعه</span>
<a href="#" class="ps-read">
خواندن روایت
<svg width="16" height="16" viewBox="0 0 24 24">
<path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none"/>
</svg>
</a>
</div>
      </div>

      <div class="ps-card" data-category="staff">
          <span class="ps-tag">کادر درمان</span>
          <h3>از بحران تا ثبات؛ تجربه‌ای از همراهی مستمر اجتماعی</h3>
          <p>روایتی از تلاش کادر درمان برای بیماران...</p>
<div class="ps-footer">
<span class="ps-time">۴ دقیقه مطالعه</span>
<a href="#" class="ps-read">
خواندن روایت
<svg width="16" height="16" viewBox="0 0 24 24">
<path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none"/>
</svg>
</a>
</div>
      </div>
      <div class="ps-card" data-category="treatment">
          <span class="ps-tag">مسیر درمان</span>
          <h3>عنوان داستان اینجاست</h3>
          <p>خلاصه‌ای از روایت بیمار در این قسمت قرار می‌گیرد...</p>
<div class="ps-footer">
<span class="ps-time">۴ دقیقه مطالعه</span>
<a href="#" class="ps-read">
خواندن روایت
<svg width="16" height="16" viewBox="0 0 24 24">
<path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none"/>
</svg>
</a>
</div>
      </div>

    </div>

  </div>
</section>

<script>
  // Filtering Logic
  const buttons = document.querySelectorAll(".ps-filters button");
  const cards = document.querySelectorAll(".ps-card");

  buttons.forEach(btn => {
    btn.addEventListener("click", () => {

      buttons.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");

      const filter = btn.dataset.filter;

      cards.forEach(card => {
        if(filter === "all" || card.dataset.category === filter){
          card.style.display = "flex";
        } else {
          card.style.display = "none";
        }
      });

    });
  });
</script>


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

/* ambient brand background (matches the stories landing section) */
.ps-global-bg{
  position:fixed;
  top:0;
  left:0;
  width:100%;
  height:100%;
  z-index:-1;
  pointer-events:none;

  background:
  radial-gradient(circle at 10% 20%, rgba(8,153,169,0.08), transparent 40%),
  radial-gradient(circle at 90% 70%, rgba(243,162,27,0.10), transparent 45%),
  linear-gradient(180deg,#fffdf9 0%, #f6fbfb 100%);
}

/* CONTAINER */
.pro-stories {
  padding:90px 0;
  font-family:'Vazirmatn', Tahoma, sans-serif;
  position:relative;
}

.ps-container {
  max-width:1100px;
  margin:auto;
  padding:0 20px;
}

/* TITLE */
.ps-title {
  text-align:center;
  font-size:32px;
  line-height:1.9;
  font-weight:700;
  letter-spacing:.3px;
  margin-bottom:50px;
}

.ps-title span {
  color:#0899A9;
  font-weight:600;
  font-size:.7em;
}

/* FILTERS */
.ps-filters {
  display:flex;
  justify-content:center;
  flex-wrap:wrap;
  gap:12px;
  margin-bottom:50px;
}

.ps-filters button {
  background:rgba(255,255,255,.55);
  backdrop-filter:blur(12px);
  -webkit-backdrop-filter:blur(12px);
  border:1px solid rgba(8,153,169,.18);
  padding:9px 18px;
  border-radius:25px;
  cursor:pointer;
  color:#0a5b65;
  font-family:inherit;
  transition:.25s ease;
  font-size:14px;
}

.ps-filters button:hover {
  border-color:rgba(8,153,169,.45);
  color:#0899A9;
  transform:translateY(-1px);
}

.ps-filters .active {
  background:linear-gradient(135deg, #0899A9, #067c89);
  border-color:transparent;
  color:#fff;
  box-shadow:0 6px 16px rgba(8,153,169,.25);
}

/* GRID */
.ps-grid {
  display:grid;
  grid-template-columns:repeat(auto-fit, minmax(260px,1fr));
  gap:28px;
}

/* CARD STYLE — frosted glass to match the brand cards */
.ps-card {
  backdrop-filter:blur(22px);
  -webkit-backdrop-filter:blur(22px);
  background:rgba(255,255,255,.55);
  border:1px solid rgba(255,255,255,.5);
  padding:26px;
  border-radius:20px;

  box-shadow:
  0 10px 20px rgba(0,0,0,.05),
  0 25px 60px rgba(0,0,0,.07);

  transition:.35s ease;
  position:relative;
  overflow:hidden;

  display:flex;
  flex-direction:column;
  height:100%;
  min-height:260px;
}

/* subtle highlight sheen */
.ps-card:before{
  content:"";
  position:absolute;
  inset:0;
  border-radius:20px;
  background:linear-gradient(120deg, rgba(255,255,255,.5), rgba(255,255,255,0));
  opacity:.3;
  pointer-events:none;
}

.ps-card:hover {
  transform:translateY(-6px);
  box-shadow:
  0 15px 35px rgba(0,0,0,.08),
  0 35px 80px rgba(0,0,0,.12);
}

/* TAG — amber accent pill */
.ps-tag {
  display: inline-block;
  width: auto;
  align-self: flex-start;
  background: linear-gradient(135deg, #f3a21b, #e08c0c);
  color: #fff;
  font-size: 12px;
  font-weight:600;
  padding: 5px 12px;
  border-radius: 12px;
  margin-bottom: 14px;
  box-shadow:0 4px 10px rgba(243,162,27,.25);
  position:relative;
  z-index:1;
}

/* CONTENT */
.ps-card h3 {
  font-size:18px;
  line-height:1.7;
  color:#1d2b2d;
  margin-bottom:10px;
  position:relative;
  z-index:1;
}

.ps-card p {
  font-size:14px;
  line-height:2;
  color:#566;
  margin-bottom:18px;
  position:relative;
  z-index:1;
}

.ps-footer{
  margin-top:auto;
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding-top:14px;
  border-top:1px solid rgba(8,153,169,.12);
  position:relative;
  z-index:1;
}

/* READ BUTTON — teal brand link */
.ps-read{
  display:flex;
  align-items:center;
  gap:6px;
  text-decoration:none;
  color:#0899A9;
  font-weight:600;
  transition:.25s ease;
}

.ps-read svg{
  transition:transform .25s ease;
}

.ps-read:hover{
  color:#067c89;
}

.ps-read:hover svg{
  transform:translateX(-4px);
}

.ps-time{
  font-size:13px;
  color:#889;
}

/* responsive */
@media(max-width:600px){
  .pro-stories{ padding:60px 0; }
  .ps-title{ font-size:23px; line-height:1.8; }
  .ps-card{ font-size:13px; padding:22px; }
}
</style>

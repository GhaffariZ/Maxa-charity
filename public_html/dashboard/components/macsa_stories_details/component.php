<section class="pro-stories">

  <div class="ps-container">

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
  // Filtering Logi
  const buttons = document.querySelectorAll(".ps-filters button");
  const cards = document.querySelectorAll(".ps-card");

  buttons.forEach(btn => {
    btn.addEventListener("click", () => {

      buttons.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");

      const filter = btn.dataset.filter;

      cards.forEach(card => {
        if(filter === "all" || card.dataset.category === filter){
          card.style.display = "block";
        } else {
          card.style.display = "none";
        }
      });

    });
  });
</script>


<style>
/* CONTAINER */
.pro-stories {
  background:#fff;
  padding:60px 0;
  font-family:inherit;
}

.ps-container {
  max-width:1100px;
  margin:auto;
  padding:0 20px;
}

/* TITLES */
.ps-title {
  font-size:32px;
  margin-bottom:8px;
}

.ps-desc {
  color:#777;
  margin-bottom:35px;
}

/* FILTERS */
.ps-filters {
  display:flex;
  justify-content:center;
  flex-wrap:wrap;
  gap:12px;
  margin-bottom:40px;
}

.ps-filters button {
  background:#f5f5f5;
  border:none;
  padding:9px 18px;
  border-radius:25px;
  cursor:pointer;
  font-family:inherit;
  transition:0.25s;
  font-size:14px;
}

.ps-filters .active,
.ps-filters button:hover {
  background:#FFD700;
  color:#000;
}

/* GRID */
.ps-grid {
  display:grid;
  grid-template-columns:repeat(auto-fit, minmax(260px,1fr));
  gap:28px;
}

/* CARD STYLE */
.ps-card {
  background:#fff;
  padding:24px;
  border-radius:18px;
  border:1px solid #e4d7a2;
  box-shadow:0 6px 18px rgba(0,0,0,0.06);
  transition:0.3s ease;

  display:flex;             
  flex-direction:column;     
  height:100%;              
  min-height:260px;          
}

.ps-card:hover {
  transform:translateY(-6px);
  box-shadow:0 10px 24px rgba(0,0,0,0.10);
}

/* TAG */
.ps-tag {
  display: inline-block;    
  width: auto;                
  background: #FFD700;
  color: #000;
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 12px;
  margin-bottom: 12px;
  align-self: flex-start;      
}

/* CONTENT */
.ps-card h3 {
  font-size:18px;
  margin-bottom:10px;
}

.ps-card p {
  font-size:14px;
  color:#666;
  margin-bottom:18px;
}

.ps-footer{
  margin-top:auto;           
  display:flex;
  justify-content:space-between;
  align-items:center;
}

/* READ BUTTON */
.ps-read{
  display:flex;
  align-items:center;
  gap:6px;
  text-decoration:none;
  color:#b48a00;
  font-weight:600;
}

.ps-read:hover{
  color:#8a6b00;
}

.ps-time{
  font-size:13px;
  color:#777;
}
</style>
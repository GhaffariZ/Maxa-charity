<section class="pro-stories" dir="rtl" data-component="macsa-stories-details">

  <div class="ps-global-bg"></div>

  <div class="ps-container">

    <!-- HEADER -->
    <h2 class="ps-title">
      بانک روایت‌های امید مکسا<br>
      <span>قصه‌های ایستادگی نجات‌یافتگان و همراهان آن‌ها</span>
    </h2>

    <!-- FILTERS -->
    <div class="ps-filters" id="psFilters">
      <button class="active" data-filter="all">همه</button>
      <button data-filter="کادر درمان">روایت کادر درمان</button>
      <button data-filter="بهبودی">زندگی پس از بهبودی</button>
      <button data-filter="تشخیص">زندگی پس از تشخیص بیماری</button>
      <button data-filter="درمان">مسیر درمان</button>
      <button data-filter="همراهان">تجربه همراهان</button>
    </div>

    <!-- STORIES GRID -->
    <div class="ps-grid" id="psStoriesGrid">
      <div class="ps-loading">در حال بارگذاری روایت‌های امید مکسا...</div>
    </div>

  </div>
</section>

<script>
(function(){
  const grid = document.getElementById("psStoriesGrid");
  const filterContainer = document.getElementById("psFilters");
  if (!grid) return;

  let allStoriesData = [];
  let activeFilter = "all";

  function renderStories(stories) {
    if (!stories || stories.length === 0) {
      grid.innerHTML = '<div class="ps-empty">روایتی در این دسته‌بندی یافت نشد.</div>';
      return;
    }

    grid.innerHTML = stories.map(story => {
      const excerpt = (story.excerpt || story.content || '').trim();
      const shortExcerpt = excerpt.length > 150 ? excerpt.slice(0, 150) + '...' : excerpt;
      const roleText = story.narrator_role ? `<span class="ps-narrator-role"> - ${escapeHtml(story.narrator_role)}</span>` : '';
      const readTime = story.read_time || '۴ دقیقه مطالعه';
      const tagText = story.tag || 'روایت امید';

      return `
        <div class="ps-card" data-category="${escapeHtml(tagText)}">
          <span class="ps-tag">${escapeHtml(tagText)}</span>
          <h3>${escapeHtml(story.title)}</h3>
          <div class="ps-narrator-info">
            <span class="ps-narrator-name">${escapeHtml(story.narrator_name)}</span>
            ${roleText}
          </div>
          <p>${escapeHtml(shortExcerpt)}</p>
          <div class="ps-footer">
            <span class="ps-time">${escapeHtml(readTime)}</span>
            <a href="/macsa-story.php?id=${story.id}" class="ps-read">
              خواندن روایت
              <svg width="16" height="16" viewBox="0 0 24 24">
                <path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none"/>
              </svg>
            </a>
          </div>
        </div>
      `;
    }).join('');
  }

  function applyFilter() {
    if (activeFilter === "all") {
      renderStories(allStoriesData);
    } else {
      const filtered = allStoriesData.filter(s => {
        const tag = (s.tag || '');
        return tag.includes(activeFilter);
      });
      renderStories(filtered);
    }
  }

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // Fetch stories from API
  fetch('/api-stories.php')
    .then(res => res.json())
    .then(json => {
      if (json && json.success && Array.isArray(json.data) && json.data.length > 0) {
        allStoriesData = json.data;
      } else {
        allStoriesData = [
          {
            id: 1,
            title: "زندگی تا آخرین لحظه",
            narrator_name: "دکتر زهرا جعفری",
            narrator_role: "روانشناس مراقبت درمنزل شعبه تهران",
            tag: "روایت کادر درمان",
            excerpt: "یکی از بهترین خاطرات من در بخش مراقبت در منزل، مرتبط با مرجان، خانم جوانی بود که سال‌ها در آمریکا زندگی کرده بود...",
            read_time: "۴ دقیقه مطالعه"
          },
          {
            id: 2,
            title: "وقتی نوشتن، درمان است",
            narrator_name: "آقای جواد چنگی",
            narrator_role: "روانشناس مراقبت در منزل شعبه تهران",
            tag: "روایت کادر درمان",
            excerpt: "در تابستان امسال، خانمی در دهه پنجم زندگی‌اش با تشخیص سرطان پستان متاستاز داده به مکسا ارجاع داده شد...",
            read_time: "۴ دقیقه مطالعه"
          },
          {
            id: 3,
            title: "از بحران تا ثبات؛ تجربه‌ای از همراهی مستمر اجتماعی",
            narrator_name: "آقای علی یزدانی",
            narrator_role: "مددکار اجتماعی مراقبت در منزل شعبه تهران",
            tag: "روایت کادر درمان",
            excerpt: "سرپرست یک خانواده به دلیل درگیری مغزی ناشی از سرطان، بستری شده بود و به قول معروف وابسته به تخت بود...",
            read_time: "۵ دقیقه مطالعه"
          }
        ];
      }
      applyFilter();
    })
    .catch(() => {
      grid.innerHTML = '<div class="ps-empty">خطا در برقراری ارتباط با پایگاه داده روایات.</div>';
    });

  // Filter Buttons
  if (filterContainer) {
    const btns = filterContainer.querySelectorAll("button");
    btns.forEach(btn => {
      btn.addEventListener("click", () => {
        btns.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        activeFilter = btn.dataset.filter || "all";
        applyFilter();
      });
    });
  }
})();
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
  grid-template-columns:repeat(auto-fit, minmax(280px,1fr));
  gap:28px;
}

/* CARD STYLE — frosted glass to match the brand cards */
.ps-card {
  backdrop-filter:blur(22px);
  -webkit-backdrop-filter:blur(22px);
  background:rgba(255,255,255,.65);
  border:1px solid rgba(255,255,255,.6);
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

.ps-narrator-info {
  font-size: 13px;
  color: #0899A9;
  font-weight: 700;
  margin-bottom: 10px;
  position: relative;
  z-index: 1;
}
.ps-narrator-role {
  font-weight: 400;
  color: #6b7c80;
  font-size: 12px;
}

/* CONTENT */
.ps-card h3 {
  font-size:18px;
  line-height:1.7;
  color:#1d2b2d;
  margin-bottom:8px;
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

.ps-loading, .ps-empty {
  text-align: center;
  padding: 40px 20px;
  color: #889;
  font-size: 14px;
  grid-column: 1 / -1;
}

/* responsive */
@media(max-width:600px){
  .pro-stories{ padding:60px 0; }
  .ps-title{ font-size:23px; line-height:1.8; }
  .ps-card{ font-size:13px; padding:22px; }
}
</style>

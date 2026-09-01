<section class="maxsa-glass" data-component="macsa-stories">

<h2 class="maxsa-title">
بانک روایت‌های امید مکسا<br>
<span>قصه های ایستادگی نجات یافتگان و همراهان آن ها</span>
</h2>
<div class="maxsa-global-bg"></div>

<div class="maxsa-container">

<!-- LEFT -->
<div class="maxsa-left">

<div class="maxsa-testimonial-wrapper">

<div class="maxsa-testimonial-track" id="homeStoriesTrack">
  <!-- Default initial cards, replaced/augmented dynamically via API -->
  <a href="/macsa-stories-details" class="maxsa-card" style="text-decoration:none;color:inherit;display:block">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <span style="display:inline-block;padding:2px 10px;border-radius:12px;background:rgba(8,153,169,.12);color:#0899A9;font-size:11px;font-weight:700">روایت کادر درمان</span>
      <span style="font-size:11px;color:#888">۴ دقیقه مطالعه</span>
    </div>
    <div class="maxsa-author">دکتر زهرا جعفری</div>
    <div class="maxsa-role">روانشناس مراقبت درمنزل شعبه تهران</div>
    <p>یکی از بهترین خاطرات من در بخش مراقبت در منزل، مرتبط با مرجان، خانم جوانی بود که سال‌ها در آمریکا زندگی کرده بود...</p>
  </a>

  <a href="/macsa-stories-details" class="maxsa-card" style="text-decoration:none;color:inherit;display:block">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <span style="display:inline-block;padding:2px 10px;border-radius:12px;background:rgba(8,153,169,.12);color:#0899A9;font-size:11px;font-weight:700">روایت کادر درمان</span>
      <span style="font-size:11px;color:#888">۴ دقیقه مطالعه</span>
    </div>
    <div class="maxsa-author">آقای جواد چنگی</div>
    <div class="maxsa-role">روانشناس مراقبت در منزل شعبه تهران</div>
    <p>در تابستان امسال، خانمی در دهه پنجم زندگی‌اش با تشخیص سرطان پستان متاستاز داده به مکسا ارجاع داده شد و....</p>
  </a>

  <a href="/macsa-stories-details" class="maxsa-card" style="text-decoration:none;color:inherit;display:block">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <span style="display:inline-block;padding:2px 10px;border-radius:12px;background:rgba(8,153,169,.12);color:#0899A9;font-size:11px;font-weight:700">روایت کادر درمان</span>
      <span style="font-size:11px;color:#888">۵ دقیقه مطالعه</span>
    </div>
    <div class="maxsa-author">آقای علی یزدانی</div>
    <div class="maxsa-role">مددکار اجتماعی مراقبت در منزل شعبه تهران</div>
    <p>سرپرست یک خانواده به دلیل درگیری مغزی ناشی از سرطان، بستری شده بود و به قول معروف وابسته به تخت بود...</p>
  </a>
</div>
</div>

<a href="/macsa-stories-details" class="maxsa-btn">
به قصه های زندگی نجات یافتگان مکسا گوش بسپارید</a>

</div>

<!-- RIGHT -->
<div class="maxsa-right">
<img src="{{image1}}">
</div>

</div>

</section>
<div class="skill-divider"></div>

<script>
(function(){
  const track = document.getElementById("homeStoriesTrack");
  if (!track) return;

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  fetch('/api-stories.php?limit=10')
    .then(res => res.json())
    .then(json => {
      if (json && json.success && Array.isArray(json.data) && json.data.length > 0) {
        const stories = json.data;
        const renderCard = (st) => {
          const excerpt = (st.excerpt || st.content || '').trim();
          const shortExcerpt = excerpt.length > 150 ? excerpt.slice(0, 150) + '...' : excerpt;
          const readTime = st.read_time || '۴ دقیقه مطالعه';
          const role = st.narrator_role || 'همراه مکسا';
          const tag = st.tag || 'روایت امید';

          return `
            <a href="/macsa-story.php?id=${st.id}" class="maxsa-card" style="text-decoration:none;color:inherit;display:block">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <span style="display:inline-block;padding:2px 10px;border-radius:12px;background:rgba(8,153,169,.12);color:#0899A9;font-size:11px;font-weight:700">${escapeHtml(tag)}</span>
                <span style="font-size:11px;color:#888">${escapeHtml(readTime)}</span>
              </div>
              <div class="maxsa-author">${escapeHtml(st.narrator_name)}</div>
              <div class="maxsa-role">${escapeHtml(role)}</div>
              <p>${escapeHtml(shortExcerpt)}</p>
            </a>
          `;
        };

        // Render once and duplicate for continuous seamless CSS scroll loop
        const cardsHtml = stories.map(renderCard).join('');
        track.innerHTML = cardsHtml + cardsHtml;
      }
    })
    .catch(() => {
      // Keep default fallback cards
    });
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

.maxsa-global-bg{
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


.maxsa-glass{
padding:100px 0;
font-family:'Vazirmatn', Tahoma, sans-serif;
position:relative;
}


.maxsa-title{
text-align:center;
font-size:32px;
line-height:1.9;
margin-bottom:60px;
font-weight:700;
letter-spacing:.3px;
}

.maxsa-title span{
color:#0899A9;
font-weight:600;
}


.maxsa-container{
max-width:1200px;
margin:auto;
display:flex;
gap:70px;
align-items:center;
}


.maxsa-left {
    flex: 1;
    text-align: center;
}


.maxsa-testimonial-wrapper{
height:420px;
overflow:hidden;
position:relative;
padding:6px 4px;
}

/* gradient fade top & bottom */
.maxsa-testimonial-wrapper:before,
.maxsa-testimonial-wrapper:after{
content:"";
position:absolute;
left:0;
width:100%;
height:70px;
z-index:2;
pointer-events:none;
}

.maxsa-testimonial-wrapper:before{
top:0;
background:linear-gradient(to bottom,#ffffff 0%, rgba(255,255,255,0) 100%);
}

.maxsa-testimonial-wrapper:after{
bottom:0;
background:linear-gradient(to top,#ffffff 0%, rgba(255,255,255,0) 100%);
}


.maxsa-testimonial-track{
display:flex;
flex-direction:column;
gap:30px;
animation:maxsaScroll 26s linear infinite;
}

.maxsa-testimonial-track:hover{
animation-play-state: paused;
}

.maxsa-card{
backdrop-filter:blur(22px);
-webkit-backdrop-filter:blur(22px);

background:rgba(255,255,255,.65);
border:1px solid rgba(255,255,255,.6);

padding:26px;
border-radius:20px;

font-size:14px;
line-height:2.1;

box-shadow:
0 10px 20px rgba(0,0,0,.05),
0 25px 60px rgba(0,0,0,.07);

transition:.35s;
position:relative;
text-align:right;
}

/* subtle highlight */
.maxsa-card:before{
content:"";
position:absolute;
inset:0;
border-radius:20px;
background:linear-gradient(
120deg,
rgba(255,255,255,.5),
rgba(255,255,255,0)
);
opacity:.3;
pointer-events:none;
}

.maxsa-card:hover{
transform:translateY(-6px);
box-shadow:
0 15px 35px rgba(0,0,0,.08),
0 35px 80px rgba(0,0,0,.12);
}


.maxsa-author{
margin-top:12px;
font-weight:700;
font-size:15px;
color:#222;
}

.maxsa-role{
font-size:12px;
opacity:.65;
margin-top:3px;
color:#555;
}


.maxsa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;

    margin-top: 40px;
    padding: 16px 26px;

    background: linear-gradient(135deg, #0899A9, #067c89);
    color: #fff;

    border-radius: 14px;
    text-decoration: none;

    font-size: 16px;
    font-weight: 700;

    box-shadow: 0 10px 25px rgba(8,153,169,.3);
    transition: .3s;
}

.maxsa-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(8,153,169,.45);
    color:#fff;
}


.maxsa-right{
flex:1;
}

.maxsa-right img{
width:100%;
border-radius:24px;
box-shadow:0 20px 40px rgba(0,0,0,.08);
}


@keyframes maxsaScroll{
0%{
transform:translateY(0);
}
100%{
transform:translateY(-50%);
}
}


@media(max-width:992px){
.maxsa-container{
flex-direction:column;
gap:40px;
}
.maxsa-right{
order:-1;
max-width:500px;
margin:auto;
}
}
</style>

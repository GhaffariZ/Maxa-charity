<?php
$homeStories = [];
try {
    if (!isset($pdo) || !$pdo) {
        require_once __DIR__ . '/../../core/database.php';
    }
    if (isset($pdo) && $pdo) {
        $hStmt = $pdo->query("SELECT * FROM `macsa_stories` WHERE `status`='published' ORDER BY `sort_order` ASC, `id` DESC LIMIT 12");
        if ($hStmt) {
            $homeStories = $hStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Throwable $e) {
    $homeStories = [];
}
?>
<section class="maxsa-glass">

<h2 class="maxsa-title">
بانک روایت‌های امید مکسا<br>
<span>قصه های ایستادگی نجات یافتگان و همراهان آن ها</span>
</h2>
<div class="maxsa-global-bg"></div>

<div class="maxsa-container">

<!-- LEFT -->
<div class="maxsa-left">

<div class="maxsa-testimonial-wrapper">

<div class="maxsa-testimonial-track">

<?php if (!empty($homeStories)): ?>
  <?php foreach ($homeStories as $st): ?>
    <a href="/macsa-story.php?id=<?= $st['id'] ?>" class="maxsa-card" style="text-decoration:none;color:inherit;display:block">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
        <span style="display:inline-block;padding:2px 10px;border-radius:12px;background:rgba(8,153,169,.12);color:#0899A9;font-size:11px;font-weight:700"><?= htmlspecialchars($st['tag'] ?: 'روایت امید') ?></span>
        <?php if (!empty($st['read_time'])): ?>
          <span style="font-size:11px;color:#888"><?= htmlspecialchars($st['read_time']) ?></span>
        <?php endif; ?>
      </div>
      <div class="maxsa-author"><?= htmlspecialchars($st['narrator_name']) ?></div>
      <div class="maxsa-role"><?= htmlspecialchars($st['narrator_role'] ?: 'همراه مکسا') ?></div>
      <p><?= htmlspecialchars($st['excerpt'] ?: mb_substr(strip_tags($st['content']), 0, 160, 'UTF-8') . '...') ?></p>
    </a>
  <?php endforeach; ?>

  <!-- Duplicate for seamless infinite loop scroll -->
  <?php foreach ($homeStories as $st): ?>
    <a href="/macsa-story.php?id=<?= $st['id'] ?>" class="maxsa-card" style="text-decoration:none;color:inherit;display:block">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
        <span style="display:inline-block;padding:2px 10px;border-radius:12px;background:rgba(8,153,169,.12);color:#0899A9;font-size:11px;font-weight:700"><?= htmlspecialchars($st['tag'] ?: 'روایت امید') ?></span>
        <?php if (!empty($st['read_time'])): ?>
          <span style="font-size:11px;color:#888"><?= htmlspecialchars($st['read_time']) ?></span>
        <?php endif; ?>
      </div>
      <div class="maxsa-author"><?= htmlspecialchars($st['narrator_name']) ?></div>
      <div class="maxsa-role"><?= htmlspecialchars($st['narrator_role'] ?: 'همراه مکسا') ?></div>
      <p><?= htmlspecialchars($st['excerpt'] ?: mb_substr(strip_tags($st['content']), 0, 160, 'UTF-8') . '...') ?></p>
    </a>
  <?php endforeach; ?>
<?php else: ?>
  <!-- Fallback static cards -->
  <div class="maxsa-card">
    <div class="maxsa-author">دکتر زهرا جعفری</div>
    <div class="maxsa-role">روانشناس مراقبت درمنزل شعبه تهران</div>
    <p>یکی از بهترین خاطرات من در بخش مراقبت در منزل، مرتبط با مرجان، خانم جوانی بود که سال‌ها در آمریکا زندگی کرده بود. همسر ایشان ایرانی‌الاصل بود، اما در آمریکا بزرگ شده بود ...</p>
  </div>

  <div class="maxsa-card">
    <div class="maxsa-author">آقای جواد چنگی</div>
    <div class="maxsa-role">روانشناس مراقبت در منزل شعبه تهران</div>
    <p>در تابستان امسال، خانمی در دهه پنجم زندگی‌اش با تشخیص سرطان پستان متاستاز داده به مکسا ارجاع داده شد و....</p>
  </div>

  <div class="maxsa-card">
    <div class="maxsa-author">آقای علی یزدانی</div>
    <div class="maxsa-role">مددکار اجتماعی مراقبت در منزل شعبه تهران</div>
    <p>سرپرست یک خانواده به دلیل درگیری مغزی ناشی از سرطان، بستری شده بود و به قول معروف وابسته به تخت بود و متاسفانه دچار زخم بستر هم شده بود. توان انجام هیچ فعالیتی را نداشت و....</p>
  </div>
<?php endif; ?>


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
    text-align: center; /* این خط دکمه را وسط‌چین می‌کند */
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


.maxsa-card{
backdrop-filter:blur(22px);
-webkit-backdrop-filter:blur(22px);

background:rgba(255,255,255,.55);
border:1px solid rgba(255,255,255,.5);

padding:26px;
border-radius:20px;

font-size:14px;
line-height:2.1;

box-shadow:
0 10px 20px rgba(0,0,0,.05),
0 25px 60px rgba(0,0,0,.07);

transition:.35s;
position:relative;
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
}


.maxsa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;

    margin-top: 40px; /* فاصله از بالا */
    padding: 16px 26px;

    background: linear-gradient(135deg, #0899A9, #067c89);
    color: #fff;

    border-radius: 14px;
    text-decoration: none;

    font-size: 16px;
    font-weight: 600;

    transition: all .25s ease;

    box-shadow: 0 6px 16px rgba(8,153,169,.25),
                0 12px 30px rgba(8,153,169,.15);

    position: relative;
    overflow: hidden;
}

.maxsa-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(8,153,169,.35),
                0 20px 45px rgba(8,153,169,.25);
}

.maxsa-btn:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8,153,169,.35);
}

.maxsa-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px rgba(8,153,169,.25),
                0 10px 25px rgba(8,153,169,.25);
}

.maxsa-btn:before {
    content: "";
    position: absolute;
    top: 0;
    left: -120%;
    width: 120%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,.35), transparent);
    transition: .6s;
}

.maxsa-btn:hover:before {
    left: 120%;
}
.maxsa-right{
flex:1;
position:relative;
}

.maxsa-right img{
width:100%;
border-radius:28px;

box-shadow:
0 20px 40px rgba(0,0,0,.08),
0 40px 80px rgba(0,0,0,.15);

transition:.6s;
}

.maxsa-right img:hover{
transform:scale(1.02);
}


/* divider */
.skill-divider{
width:100%;
height:1px;
margin:90px 0;

background:linear-gradient(
90deg,
transparent,
rgba(0,0,0,0.12),
rgba(0,0,0,0.35),
rgba(0,0,0,0.12),
transparent
);
}


/* smooth scroll animation */
@keyframes maxsaScroll{
0%{transform:translateY(0)}
100%{transform:translateY(-50%)}
}



/* responsive */
@media(max-width:900px){

.maxsa-container{
flex-direction:column;
gap:40px;
}

.maxsa-testimonial-wrapper{
height:360px;
}

.maxsa-title{
font-size:26px;
}

.maxsa-btn{
font-size:16px;
padding:18px 24px;
}

}

@media(max-width:600px){

.maxsa-title{
font-size:23px;
line-height:1.8;
}

.maxsa-card{
font-size:13px;
padding:22px;
}

}

  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }
</style>

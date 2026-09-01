<footer class="gf-footer">
<div class="gf-container">

<!-- ستون بزرگ -->
<div class="gf-col gf-cta">
<div class="gf-logo">
<img src="{{image1}}">
</div>

<h2 class="gf-title">
همراه با هم به زندگی بیماران نور هدیه کنیم</h2>
</div>


<!-- صفحات اصلی -->
<div class="gf-col">
<h4>آشنایی با مکسا</h4>
<ul>
<li><a href="/home">صفحه اصلی</a></li>
<li><a href="/history">درباره ما</a></li>
<li><a href="/MACSAservices.html">صفحات خدمات</a></li>
<li><a href="/single-fundraising-option">روش های حمایت</a></li>
</ul>
</div>


<!-- شرکت ما -->
<div class="gf-col">
<h4>ارتباط با مکسا</h4>
<ul>
<li><a href="/branches.php">شعب</a></li>
<li><a href="/contact-center">مرکز ارتباطات کشوری</a></li>
<li><a href="/contactus">تماس با ما</a></li>

</ul>
</div>


<!-- خبرنامه -->
<div class="gf-col">
<h4 class="gf-news-title">برای دریافت گزارش کمک‌ها و داستان بیماران عضو شوید</h4>

<div class="gf-newsletter">
<input type="email" placeholder="آدرس ایمیل خود را وارد کنید">
<button>➜</button>
</div>

<p class="gf-privacy">
با عضویت سیاست حفظ حریم خصوصی را می‌پذیرید
</p>
</div>


</div>


<!-- اطلاعات تماس -->
<div class="gf-contact">

<div>
<span>تماس با ما</span>
<p>021-91092030 / 021-86015342</p>
</div>

<div>
<span>آدرس</span>
<p>تهران، بزرگراه جلال‌آل‌احمد(شرق به غرب)، بعد از کوی نصر(گیشا)، پلاک ۱۳۹، ساختمان مکسا
</p>
</div>

<div>
<span>آدرس ایمیل ما</span>
<p>info@macsa.ir</p>
</div>

</div>


<!-- پایین فوتر -->
<div class="gf-bottom">
  <div class="gf-social">
    <a href="#"><img src="{{ API_GET("/pigi","footer","A1").data[1].image | raw }}" alt="instagram"></a>
    <a href="#"><img src="{{ API_GET("/pigi","footer","A1").data[2].image | raw }}" alt="eitaa"></a>
    <a href="#"><img src="{{ API_GET("/pigi","footer","A1").data[3].image | raw }}" alt="bale"></a>
    <a href="#"><img src="{{ API_GET("/pigi","footer","A1").data[4].image | raw }}" alt="whatsapp"></a>
  </div>

<div class="gf-copy">
تمامی حقوق مادی و معنوی متعلق به موسسه نیکوکاری کنترل سرطان ایرانیان (مکسا) است.
</div>
</div>

</footer>



<style>
/* =========================
   Global Font (self-hosted, Iran-network reliable)
========================= */
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
       url('/webfont/Vazirmatn[wght].woff2') format('woff2');
  font-weight: 100 900;
  font-style: normal;
  font-display: swap;
}

body {
  font-family: 'Vazirmatn', sans-serif !important;
}

/* =========================
   Footer - MACSA Style
========================= */

.gf-footer {
  position: relative;
  overflow: hidden;
  direction: rtl;
  font-family: inherit;
  color: #ffffff;
  padding-top: 80px;

  background:
    radial-gradient(circle at 12% 10%, rgba(255,255,255,0.24), transparent 34%),
    radial-gradient(circle at 88% 88%, rgba(255,255,255,0.14), transparent 38%),
    linear-gradient(155deg, #10aeb8 0%, #07828e 54%, #05646e 100%);

  box-shadow:
    inset 0 1px 0 rgba(255,255,255,0.20),
    0 -18px 44px rgba(15, 159, 170, 0.16);
}

/* حباب‌های پس‌زمینه */
.gf-footer::before {
  content: "";
  position: absolute;
  width: 260px;
  height: 260px;
  left: -95px;
  bottom: 80px;
  border-radius: 50%;
  background: rgba(255,255,255,0.10);
  pointer-events: none;
}

.gf-footer::after {
  content: "";
  position: absolute;
  width: 190px;
  height: 190px;
  right: -70px;
  top: -70px;
  border-radius: 50%;
  background: rgba(255,255,255,0.11);
  pointer-events: none;
}

/* یک لایه نور نرم داخل فوتر */
.gf-footer > * {
  position: relative;
  z-index: 1;
}

/* =========================
   Main Container
========================= */

.gf-container {
  max-width: 1200px;
  margin: auto;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1.35fr;
  gap: 54px;
  padding: 0 20px;
}

/* =========================
   Columns
========================= */

.gf-col h4 {
  position: relative;
  margin: 0 0 18px 0;
  font-size: 18px;
  line-height: 1.8;
  font-weight: 900;
  color: #ffffff;
}

/* خط کوچک زیر عنوان ستون‌ها */
.gf-col h4::after {
  content: "";
  display: block;
  width: 42px;
  height: 3px;
  margin-top: 8px;
  border-radius: 999px;
  background: rgba(255,255,255,0.45);
}

.gf-col ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.gf-col li {
  margin-bottom: 11px;
}

.gf-col a {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: rgba(255,255,255,0.88);
  text-decoration: none;
  font-size: 14.5px;
  line-height: 1.9;
  transition:
    color 0.22s ease,
    transform 0.22s ease,
    opacity 0.22s ease;
}

/* فلش ظریف برای لینک‌ها */
.gf-col a::before {
  content: "←";
  color: rgba(255,255,255,0.65);
  font-weight: 900;
  transition:
    transform 0.22s ease,
    color 0.22s ease;
}

.gf-col a:hover {
  color: #ffffff;
  opacity: 1;
  transform: translateX(-3px);
}

.gf-col a:hover::before {
  color: #ffffff;
  transform: translateX(-3px);
}

/* =========================
   CTA Column
========================= */

.gf-cta {
  text-align: right;
}

.gf-logo img {
  width: 280px;
  max-width: 100%;
  margin-bottom: 22px;
  display: block;
  filter: drop-shadow(0 14px 24px rgba(0,0,0,0.13));
}

.gf-small {
  color: #fff3c4;
  margin-bottom: 10px;
  font-weight: 800;
}

.gf-title {
  max-width: 430px;
  margin: 0;
  color: #ffffff;
  font-size: 32px;
  line-height: 1.35;
  font-weight: 950;
  letter-spacing: -0.04em;
  text-shadow: 0 14px 28px rgba(0,0,0,0.10);
}

.gf-title span {
  color: #fff3c4;
}

/* =========================
   Newsletter
========================= */

.gf-news-title {
  font-size: 18px;
  line-height: 1.9;
  font-weight: 900;
}

.gf-newsletter {
  position: relative;
  display: flex;
  align-items: stretch;
  margin-top: 20px;
  overflow: hidden;

  border: 1px solid rgba(255,255,255,0.22);
  border-radius: 16px;
  background: rgba(255,255,255,0.13);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,0.18),
    0 14px 30px rgba(0,0,0,0.08);
  backdrop-filter: blur(8px);
}

.gf-newsletter input {
  font-family: inherit;
  width: 100%;
  min-height: 48px;
  border: 0;
  outline: none;
  padding: 0 16px;
  background: rgba(255,255,255,0.92);
  color: #0f172a;
  font-size: 14px;
  border-radius: 0;
}

.gf-newsletter input::placeholder {
  color: #64748b;
}

.gf-newsletter input:focus {
  background: #ffffff;
}

.gf-newsletter button {
  font-family: inherit;
  min-width: 54px;
  border: 0;
  cursor: pointer;

  background: linear-gradient(180deg, #ffffff 0%, #f1ffff 100%);
  color: #087985;
  font-size: 20px;
  font-weight: 950;

  transition:
    transform 0.22s ease,
    background 0.22s ease,
    color 0.22s ease;
}

.gf-newsletter button:hover {
  background: #ffffff;
  color: #05636d;
  transform: translateX(-2px);
}

.gf-privacy {
  margin-top: 12px;
  font-size: 13px;
  line-height: 1.9;
  color: rgba(255,255,255,0.78);
}

/* =========================
   Contact Info
========================= */

.gf-contact {
  max-width: 1200px;
  margin: 62px auto 0;
  padding: 28px 20px;

  display: grid;
  grid-template-columns: 1fr 2fr 1fr;
  gap: 22px;

  border-top: 1px solid rgba(255,255,255,0.16);
  border-bottom: 1px solid rgba(255,255,255,0.10);
}

.gf-contact > div {
  padding: 18px 18px;
  border-radius: 22px;
  background: rgba(255,255,255,0.10);
  border: 1px solid rgba(255,255,255,0.13);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,0.13),
    0 14px 30px rgba(0,0,0,0.06);
  backdrop-filter: blur(7px);
}

.gf-contact span {
  display: block;
  margin-bottom: 8px;
  color: #fff3c4;
  font-size: 13.5px;
  font-weight: 900;
}

.gf-contact p {
  margin: 0;
  color: rgba(255,255,255,0.90);
  font-size: 14px;
  line-height: 2;
}

/* برای شماره و ایمیل خواناتر */
.gf-contact div:first-child p,
.gf-contact div:last-child p {
  direction: ltr;
  text-align: right;
}

/* =========================
   Bottom Footer
========================= */

.gf-bottom {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 20px 28px;

  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 18px;
}

.gf-social {
  display: flex;
  align-items: center;
  gap: 12px;
}

.gf-social a {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;

  border-radius: 16px;
  background: rgba(255,255,255,0.13);
  border: 1px solid rgba(255,255,255,0.16);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,0.14),
    0 10px 22px rgba(0,0,0,0.08);

  transition:
    transform 0.22s ease,
    background 0.22s ease,
    border-color 0.22s ease;
}

.gf-social a:hover {
  transform: translateY(-3px);
  background: rgba(255,255,255,0.20);
  border-color: rgba(255,255,255,0.28);
}

.gf-social a img {
  width: 23px;
  height: 23px;
  margin: 0;
  display: block;
  object-fit: contain;
  transition:
    transform 0.22s ease,
    opacity 0.22s ease;
}

.gf-social a:hover img {
  transform: scale(1.08);
  opacity: 0.92;
}

.gf-copy {
  color: rgba(255,255,255,0.78);
  font-size: 13.5px;
  line-height: 2;
  text-align: left;
  white-space: nowrap;
}

/* =========================
   Responsive
========================= */

@media (max-width: 1050px) {
  .gf-container {
    grid-template-columns: 1.4fr 1fr 1fr;
    gap: 40px;
  }

  .gf-col.gf-cta {
    grid-column: 1 / -1;
  }

  .gf-title {
    max-width: 620px;
  }

  .gf-contact {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 900px) {
  .gf-footer {
    padding-top: 56px;
  }

  .gf-container {
    grid-template-columns: 1fr;
    gap: 34px;
  }

  .gf-title {
    font-size: 30px;
    line-height: 1.45;
  }

  .gf-logo img {
    width: 240px;
  }

  .gf-contact {
    margin-top: 46px;
    text-align: right;
  }

  .gf-bottom {
    flex-direction: column;
    text-align: center;
  }

  .gf-copy {
    white-space: normal;
    text-align: center;
  }

  .gf-social {
    justify-content: center;
  }
}

@media (max-width: 520px) {
  .gf-footer {
    padding-top: 44px;
  }

  .gf-container,
  .gf-contact,
  .gf-bottom {
    padding-left: 16px;
    padding-right: 16px;
  }

  .gf-title {
    font-size: 25px;
  }

  .gf-col h4,
  .gf-news-title {
    font-size: 16.5px;
  }

  .gf-newsletter {
    border-radius: 14px;
  }

  .gf-newsletter input {
    font-size: 13px;
    padding: 0 12px;
  }

  .gf-newsletter button {
    min-width: 48px;
  }

  .gf-contact > div {
    padding: 16px;
    border-radius: 18px;
  }

  .gf-social a {
    width: 40px;
    height: 40px;
    border-radius: 14px;
  }
}

</style>

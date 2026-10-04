<?php
/* صفحه‌ی «تماس با ما».
   اطلاعات تماس مؤسسه + فرم تماس که به اندپوینت عمومی POST /api/contact ارسال می‌شود.
   ساختار صفحه از الگوی macsapedia.php پیروی می‌کند: هدر مشترک، محتوا، فوتر مشترک. */

$pageTitle = 'تماس با ما';
require __DIR__ . '/dashboard/components/header/component.php';
?>

<style>
  .cu-wrap {
    max-width: var(--cta-container, 1440px);
    margin: 0 auto;
    padding: 32px 20px 48px;
    font-family: 'Vazirmatn', sans-serif;
    direction: rtl;
  }
  .cu-head {
    text-align: center;
    margin-bottom: 48px;
  }
  .cu-head h1 {
    font-size: clamp(28px, 4vw, 44px);
    font-weight: 900;
    color: #2f3437;
    margin: 0 0 12px;
  }
  .cu-head p {
    color: #6b7280;
    font-size: 16px;
    line-height: 2;
    max-width: 680px;
    margin: 0 auto;
  }

  .cu-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 32px;
    align-items: start;
  }
  @media (max-width: 920px) { .cu-grid { grid-template-columns: 1fr; } }

  /* ===== کارت فرم ===== */
  .cu-card {
    background: #fff;
    border: 1px solid #ecedf0;
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 10px 30px rgba(0,0,0,.05);
  }
  .cu-card h2 {
    font-size: 22px;
    font-weight: 800;
    color: #2f3437;
    margin: 0 0 6px;
  }
  .cu-card .cu-sub {
    color: #8b8f96;
    font-size: 14px;
    margin: 0 0 24px;
  }

  .cu-field { margin-bottom: 18px; }
  .cu-field label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #3f4651;
    margin-bottom: 8px;
  }
  .cu-field label .req { color: #e5484d; }
  .cu-field input,
  .cu-field textarea {
    width: 100%;
    box-sizing: border-box;
    font-family: inherit;
    font-size: 15px;
    color: #1f2937;
    background: #fafbfc;
    border: 1px solid #e2e5ea;
    border-radius: 12px;
    padding: 13px 14px;
    transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
  }
  .cu-field textarea { min-height: 140px; resize: vertical; }
  .cu-field input:focus,
  .cu-field textarea:focus {
    outline: none;
    background: #fff;
    border-color: #10aeb8;
    box-shadow: 0 0 0 3px rgba(16,174,184,.14);
  }
  .cu-field .cu-err {
    display: none;
    color: #e5484d;
    font-size: 12.5px;
    margin-top: 6px;
  }
  .cu-field.has-error input,
  .cu-field.has-error textarea { border-color: #e5484d; }
  .cu-field.has-error .cu-err { display: block; }

  .cu-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
  @media (max-width: 540px) { .cu-row { grid-template-columns: 1fr; } }

  /* honeypot - از دید کاربر پنهان است */
  .cu-hp { position: absolute; left: -9999px; top: -9999px; width: 1px; height: 1px; overflow: hidden; }

  .cu-submit {
    width: 100%;
    border: 0;
    cursor: pointer;
    font-family: inherit;
    font-size: 16px;
    font-weight: 800;
    color: #fff;
    border-radius: 12px;
    padding: 15px 18px;
    margin-top: 6px;
    background: linear-gradient(155deg, #10aeb8 0%, #07828e 100%);
    box-shadow: 0 12px 26px rgba(7,130,142,.22);
    transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
  }
  .cu-submit:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(7,130,142,.28); }
  .cu-submit:disabled { opacity: .6; cursor: default; transform: none; box-shadow: none; }

  .cu-alert {
    display: none;
    border-radius: 12px;
    padding: 14px 16px;
    font-size: 14.5px;
    line-height: 1.9;
    margin-bottom: 20px;
  }
  .cu-alert.ok  { display: block; background: #e9f9f1; color: #0b7a4b; border: 1px solid #bfead4; }
  .cu-alert.bad { display: block; background: #fdecec; color: #b42318; border: 1px solid #f6cdcb; }

  /* ===== ستون اطلاعات تماس ===== */
  .cu-info { display: flex; flex-direction: column; gap: 16px; }
  .cu-ibox {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    background: #fff;
    border: 1px solid #ecedf0;
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 6px 18px rgba(0,0,0,.04);
  }
  .cu-icon {
    flex: 0 0 48px;
    width: 48px; height: 48px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 14px;
    background: rgba(16,174,184,.12);
    color: #07828e;
    transition: transform .2s ease, background .2s ease, color .2s ease;
  }
  .cu-ibox:hover .cu-icon {
    transform: scale(1.05);
    background: rgba(16,174,184,.2);
  }
  .cu-icon svg {
    width: 24px;
    height: 24px;
    display: block;
  }
  .cu-ibox h3 { margin: 0 0 6px; font-size: 15px; font-weight: 800; color: #2f3437; }
  .cu-ibox p  { margin: 0; font-size: 14px; line-height: 2; color: #6b7280; }
  .cu-ibox a  { color: #07828e; text-decoration: none; }
  .cu-ibox a:hover { text-decoration: underline; }
  .cu-ltr { direction: ltr; text-align: right; unicode-bidi: plaintext; }

  .cu-map {
    display: inline-flex; align-items: center; gap: 6px;
    margin-top: 10px;
    font-size: 13.5px; font-weight: 700;
    color: #e99508; text-decoration: none;
    transition: color .18s ease, transform .18s ease;
  }
  .cu-map svg {
    width: 15px;
    height: 15px;
    transition: transform .18s ease;
  }
  .cu-map:hover { color: #d9820a; }
  .cu-map:hover svg { transform: translateX(-3px); }

  .cu-social { display: flex; gap: 10px; margin-top: 6px; flex-wrap: wrap; }
  .cu-social a {
    width: 42px; height: 42px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 12px;
    background: rgba(16,174,184,.1);
    color: #07828e;
    border: 1px solid transparent;
    text-decoration: none;
    transition: transform .18s ease, background .18s ease, box-shadow .18s ease, color .18s ease, border-color .18s ease;
  }
  .cu-social a svg {
    width: 20px;
    height: 20px;
    display: block;
    transition: transform .18s ease;
  }
  .cu-social a img {
    width: 20px;
    height: 20px;
    object-fit: contain;
    display: block;
    transition: transform .18s ease;
  }
  .cu-social a:hover {
    transform: translateY(-3px);
  }
  .cu-social a:hover svg,
  .cu-social a:hover img {
    transform: scale(1.1);
  }
  .cu-social a.cu-soc-instagram:hover {
    background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
    color: #fff;
    box-shadow: 0 8px 20px rgba(214, 36, 159, 0.35);
  }
  .cu-social a.cu-soc-bale:hover {
    background: #ffffff;
    box-shadow: 0 8px 20px rgba(0, 177, 117, 0.35);
    border-color: #00B175;
  }
  .cu-social a.cu-soc-eitaa:hover {
    background: #ffffff;
    box-shadow: 0 8px 20px rgba(230, 126, 34, 0.35);
    border-color: #E67E22;
  }
  .cu-social a.cu-soc-aparat:hover {
    background: #ffffff;
    box-shadow: 0 8px 20px rgba(234, 29, 93, 0.35);
    border-color: #EA1D5D;
  }
  .cu-social a.cu-soc-linkedin:hover {
    background: #0077B5;
    color: #fff;
    box-shadow: 0 8px 20px rgba(0, 119, 181, 0.35);
  }
</style>

<div class="cu-wrap">
  <div class="cu-head">
    <h1>تماس با ما</h1>
    <p>برای ارتباط با مؤسسه نیکوکاری کنترل سرطان ایرانیان (مکسا) می‌توانید از راه‌های زیر با ما در ارتباط باشید یا فرم تماس را تکمیل کنید. کارشناسان ما در اسرع وقت پاسخگوی شما خواهند بود.</p>
  </div>

  <div class="cu-grid">

    <!-- فرم تماس -->
    <div class="cu-card">
      <h2>ارسال پیام</h2>
      <p class="cu-sub">فیلدهای ستاره‌دار الزامی هستند.</p>

      <div id="cuAlert" class="cu-alert" role="status" aria-live="polite"></div>

      <form id="cuForm" novalidate>
        <div class="cu-row">
          <div class="cu-field">
            <label for="cu-name">نام و نام خانوادگی <span class="req">*</span></label>
            <input type="text" id="cu-name" name="name" autocomplete="name" required>
            <span class="cu-err" data-for="name"></span>
          </div>
          <div class="cu-field">
            <label for="cu-email">ایمیل <span class="req">*</span></label>
            <input type="email" id="cu-email" name="email" autocomplete="email" dir="ltr" required>
            <span class="cu-err" data-for="email"></span>
          </div>
        </div>

        <div class="cu-row">
          <div class="cu-field">
            <label for="cu-phone">شماره همراه</label>
            <input type="tel" id="cu-phone" name="phone" inputmode="numeric" dir="ltr" placeholder="09xxxxxxxxx">
            <span class="cu-err" data-for="phone"></span>
          </div>
          <div class="cu-field">
            <label for="cu-subject">موضوع <span class="req">*</span></label>
            <input type="text" id="cu-subject" name="subject" required>
            <span class="cu-err" data-for="subject"></span>
          </div>
        </div>

        <div class="cu-field">
          <label for="cu-message">متن پیام <span class="req">*</span></label>
          <textarea id="cu-message" name="message" required></textarea>
          <span class="cu-err" data-for="message"></span>
        </div>

        <!-- honeypot ضدّ ربات -->
        <div class="cu-hp" aria-hidden="true">
          <label>اگر انسان هستید این فیلد را خالی بگذارید
            <input type="text" name="website" tabindex="-1" autocomplete="off">
          </label>
        </div>

        <button type="submit" class="cu-submit" id="cuSubmit">ارسال پیام</button>
      </form>
    </div>

    <!-- اطلاعات تماس -->
    <div class="cu-info">
      <div class="cu-ibox">
        <span class="cu-icon" aria-hidden="true">
          <?= macsa_icon('phone', '', 24) ?>
        </span>
        <div>
          <h3>تلفن تماس</h3>
          <p class="cu-ltr">021-91092030<br>021-86015342</p>
        </div>
      </div>

      <div class="cu-ibox">
        <span class="cu-icon" aria-hidden="true">
          <?= macsa_icon('mail', '', 24) ?>
        </span>
        <div>
          <h3>ایمیل</h3>
          <p><a href="mailto:info@macsa.ir" class="cu-ltr">info@macsa.ir</a></p>
        </div>
      </div>

      <div class="cu-ibox">
        <span class="cu-icon" aria-hidden="true">
          <?= macsa_icon('map-pin', '', 24) ?>
        </span>
        <div>
          <h3>نشانی</h3>
          <p>تهران، بزرگراه جلال‌آل‌احمد (شرق به غرب)، بعد از کوی نصر (گیشا)، پلاک ۱۳۹، ساختمان مکسا</p>
          <a class="cu-map" href="https://neshan.org/maps/search/مؤسسه%20مکسا" target="_blank" rel="noopener">
            <span>مشاهده روی نقشه</span>
            <?= macsa_icon('arrow-left', '', 24) ?>
          </a>
        </div>
      </div>

      <div class="cu-ibox">
        <span class="cu-icon" aria-hidden="true">
          <?= macsa_icon('clock', '', 24) ?>
        </span>
        <div>
          <h3>ساعات پاسخگویی</h3>
          <p>شنبه تا چهارشنبه، ۹ تا ۱۷<br>پنج‌شنبه، ۹ تا ۱۳</p>
        </div>
      </div>

      <div class="cu-ibox">
        <span class="cu-icon" aria-hidden="true">
          <?= macsa_icon('message-circle', '', 24) ?>
        </span>
        <div>
          <h3>شبکه‌های اجتماعی</h3>
          <div class="cu-social">
            <a href="https://www.instagram.com/macsacharity?igsi=OWlla2VxbWZqdDJl" target="_blank" rel="noopener noreferrer" class="cu-soc-instagram" aria-label="اینستاگرام" title="اینستاگرام">
              <?= macsa_icon('brand-instagram', '', 24) ?>
            </a>
            <a href="https://ble.ir/join/DUmacfMgrR" target="_blank" rel="noopener noreferrer" class="cu-soc-bale" aria-label="بله" title="بله">
              <img src="/dashboard/components/footer/images/bale.png" alt="بله">
            </a>
            <a href="https://eitaa.com/macsacharity" target="_blank" rel="noopener noreferrer" class="cu-soc-eitaa" aria-label="ایتا" title="ایتا">
              <img src="/dashboard/components/footer/images/eitaa.png" alt="ایتا">
            </a>
            <a href="https://www.aparat.com/macsa_charity" target="_blank" rel="noopener noreferrer" class="cu-soc-aparat" aria-label="آپارات" title="آپارات">
              <img src="/dashboard/components/footer/images/aparat.png" alt="آپارات">
            </a>
            <a href="https://www.linkedin.com/company/iranian-cancer-control-center-macsa/?viewAsMember=true" target="_blank" rel="noopener noreferrer" class="cu-soc-linkedin" aria-label="لینکدین" title="لینکدین">
              <?= macsa_icon('brand-linkedin', '', 24) ?>
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
(function () {
  var form    = document.getElementById('cuForm');
  var alertEl = document.getElementById('cuAlert');
  var btn     = document.getElementById('cuSubmit');
  if (!form) return;

  function clearErrors() {
    form.querySelectorAll('.cu-field.has-error').forEach(function (f) { f.classList.remove('has-error'); });
    form.querySelectorAll('.cu-err').forEach(function (s) { s.textContent = ''; });
  }
  function showFieldError(field, msg) {
    var input = form.querySelector('[name="' + field + '"]');
    var span  = form.querySelector('.cu-err[data-for="' + field + '"]');
    if (input && input.closest('.cu-field')) input.closest('.cu-field').classList.add('has-error');
    if (span) span.textContent = msg;
  }
  function showAlert(type, msg) {
    alertEl.className = 'cu-alert ' + type;
    alertEl.textContent = msg;
    alertEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearErrors();
    alertEl.className = 'cu-alert';

    var payload = {
      name:    form.name.value.trim(),
      email:   form.email.value.trim(),
      phone:   form.phone.value.trim(),
      subject: form.subject.value.trim(),
      message: form.message.value.trim(),
      website: form.website.value   // honeypot
    };

    // اعتبارسنجی سریع سمت کلاینت (سرور هم دوباره بررسی می‌کند)
    var clientErr = false;
    if (payload.name.length < 2)    { showFieldError('name', 'نام را وارد کنید.'); clientErr = true; }
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(payload.email)) { showFieldError('email', 'ایمیل معتبر وارد کنید.'); clientErr = true; }
    if (payload.subject.length < 2) { showFieldError('subject', 'موضوع را وارد کنید.'); clientErr = true; }
    if (payload.message.length < 10){ showFieldError('message', 'پیام باید حداقل ۱۰ کاراکتر باشد.'); clientErr = true; }
    if (clientErr) return;

    btn.disabled = true;
    var originalText = btn.textContent;
    btn.textContent = 'در حال ارسال…';

    fetch('/api/contact', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    })
      .then(function (res) { return res.json().then(function (j) { return { ok: res.ok, body: j }; }); })
      .then(function (r) {
        if (r.ok && r.body && r.body.success) {
          form.reset();
          showAlert('ok', (r.body.data && r.body.data.message) || 'پیام شما با موفقیت ارسال شد.');
          return;
        }
        var err = r.body && r.body.error;
        if (err && err.code === 'validation_failed' && err.fields) {
          Object.keys(err.fields).forEach(function (f) { showFieldError(f, err.fields[f]); });
          showAlert('bad', 'لطفاً خطاهای فرم را برطرف کنید.');
        } else {
          showAlert('bad', (err && err.message) || 'ارسال پیام ناموفق بود. لطفاً دوباره تلاش کنید.');
        }
      })
      .catch(function () {
        showAlert('bad', 'ارتباط با سرور برقرار نشد. اتصال اینترنت خود را بررسی کنید.');
      })
      .then(function () {
        btn.disabled = false;
        btn.textContent = originalText;
      });
  });
})();
</script>

<?php
require __DIR__ . '/dashboard/components/footer/component.php';

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
    padding: 56px 20px 90px;
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

  /* honeypot — از دید کاربر پنهان است */
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
    flex: 0 0 46px;
    width: 46px; height: 46px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 14px;
    font-size: 22px;
    background: rgba(16,174,184,.12);
    color: #07828e;
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
    color: #f39a20; text-decoration: none;
  }
  .cu-map:hover { color: #d9820a; }

  .cu-social { display: flex; gap: 10px; margin-top: 4px; }
  .cu-social a {
    width: 42px; height: 42px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 13px; font-size: 18px;
    background: rgba(16,174,184,.1); color: #07828e;
    text-decoration: none;
    transition: transform .18s ease, background .18s ease;
  }
  .cu-social a:hover { transform: translateY(-3px); background: rgba(16,174,184,.2); }
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
        <span class="cu-icon">📞</span>
        <div>
          <h3>تلفن تماس</h3>
          <p class="cu-ltr">021-91092030<br>021-86015342</p>
        </div>
      </div>

      <div class="cu-ibox">
        <span class="cu-icon">✉️</span>
        <div>
          <h3>ایمیل</h3>
          <p><a href="mailto:info@macsa.ir" class="cu-ltr">info@macsa.ir</a></p>
        </div>
      </div>

      <div class="cu-ibox">
        <span class="cu-icon">📍</span>
        <div>
          <h3>نشانی</h3>
          <p>تهران، بزرگراه جلال‌آل‌احمد (شرق به غرب)، بعد از کوی نصر (گیشا)، پلاک ۱۳۹، ساختمان مکسا</p>
          <a class="cu-map" href="https://neshan.org/maps/search/مؤسسه%20مکسا" target="_blank" rel="noopener">مشاهده روی نقشه ←</a>
        </div>
      </div>

      <div class="cu-ibox">
        <span class="cu-icon">🕘</span>
        <div>
          <h3>ساعات پاسخگویی</h3>
          <p>شنبه تا چهارشنبه، ۹ تا ۱۷<br>پنج‌شنبه، ۹ تا ۱۳</p>
        </div>
      </div>

      <div class="cu-ibox">
        <span class="cu-icon">💬</span>
        <div>
          <h3>شبکه‌های اجتماعی</h3>
          <div class="cu-social">
            <a href="#" target="_blank" rel="noopener" aria-label="اینستاگرام">📷</a>
            <a href="#" target="_blank" rel="noopener" aria-label="ایتا">🟠</a>
            <a href="#" target="_blank" rel="noopener" aria-label="بله">🔵</a>
            <a href="#" target="_blank" rel="noopener" aria-label="واتساپ">🟢</a>
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

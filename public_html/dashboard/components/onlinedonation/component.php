<section class="donation-shell">
  <div class="donation-card">

    <!-- Right Panel: Info & Trust Elements -->
    <div class="donation-hero">
      <div class="donation-hero-content">

        <!-- Premium Image Area -->
        <div class="donation-media">
          <div class="donation-media-frame">
            <!-- برای استفاده واقعی فقط src را عوض کنید -->
            <img
              src="{{image1}}"/>
            
            <!-- اگر خواستی بدون عکس باشد، این لایه Placeholder را نگه دار و img را حذف/کامنت کن -->
            <!--
            <div class="donation-image-placeholder">
              <div class="placeholder-icon">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <rect x="3" y="3" width="18" height="18" rx="4"></rect>
                  <circle cx="9" cy="9" r="1.8"></circle>
                  <path d="M21 15l-4.2-4.2a1 1 0 0 0-1.4 0L8 18"></path>
                </svg>
              </div>
              <div class="placeholder-text">
                <strong>جایگاه تصویر مناسبتی</strong>
                <span>برای نمایش کمپین، رویداد یا پیام حمایتی</span>
              </div>
            </div>
            -->

            <div class="donation-media-overlay">
              <span class="media-chip">کمپین فعال</span>
            </div>
          </div>
        </div>

        <div class="donation-badge">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          پرداخت مستقیم و امن به خیریه
        </div>

        <h2 class="donation-title">سهم شما در درمان بیماران، <span>ماندگار و امیدبخش</span> است</h2>
        <p class="donation-desc">
          مجموعه مکسا با شفافیت کامل مالی و ارائه گزارش‌های دقیق، کمک‌های نقدی شما را مستقیماً جهت تأمین داروهای خاص و خدمات درمانی بیماران مبتلا به سرطان مصرف می‌کند.
        </p>

        <div class="premium-features">
          <div class="feature-item">
            <div class="feature-icon-wrapper">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0f8b93" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg>
            </div>
            <div class="feature-text">
              <h4>نظارت و شفافیت مالی کامل</h4>
              <p>امکان پیگیری لحظه‌ای مسیر مصرف مبالغ اهدایی توسط نیکوکاران</p>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon-wrapper">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0f8b93" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15.05 5A5 5 0 0 1 19 8.95M15.05 1A9 9 0 0 1 23 8.94m-1 12.01a11 11 0 0 1-9.8-9.8 12 12 0 0 0-3.23-3.23 11 11 0 0 1-9.8-9.8L1 1.05A2 2 0 0 1 3 1h3.18a2 2 0 0 1 2 1.6 8.38 8.38 0 0 0 .5 1.8 2 2 0 0 1-.45 2.11L6.9 7.82a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 8.38 8.38 0 0 0 1.8.5 2 2 0 0 1 1.6 2V21a2 2 0 0 1-1 1.95z"></path>
              </svg>
            </div>
            <div class="feature-text">
              <h4>مرکز پشتیبانی و ارتباط مردمی</h4>
              <p>پاسخگویی سریع از طریق خط کشوری <strong class="phone-link">021-91092030</strong></p>
            </div>
          </div>
        </div>
      </div>

      <div class="donation-stats-strip">
        <div class="stat-box">
          <span class="stat-number">۱۰۰٪</span>
          <span class="stat-label">شفافیت مالی</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-box">
          <span class="stat-number">۲۴/۷</span>
          <span class="stat-label">درگاه فعال امن</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-box">
          <span class="stat-number">۳۰+</span>
          <span class="stat-label">خدمات حمایتی</span>
        </div>
      </div>
    </div>

    <!-- Left Panel: Form -->
    <div class="donation-form-wrap">
      <form class="donation-form" action="#" method="post">
        <div class="form-header">
          <h3>ثبت اطلاعات پرداخت</h3>
          <p>مبالغ اهدایی مشمول معافیت مالیاتی می‌باشند</p>
        </div>

        <div class="form-grid">
          <div class="field">
            <label>نام</label>
            <input type="text" name="first_name" placeholder="مثلاً: رضا" required>
          </div>

          <div class="field">
            <label>نام خانوادگی</label>
            <input type="text" name="last_name" placeholder="مثلاً: محمدی" required>
          </div>
        </div>

        <div class="field">
          <label>شماره همراه (اجباری)</label>
          <input type="tel" name="phone" id="donorPhone" placeholder="09123456789" required
                 pattern="^09[0-9]{9}$" title="شماره تماس معتبر موبایل وارد کنید">
        </div>

        <div class="field">
          <label>کد ملی (اختیاری)</label>
          <input type="text" name="national_code" id="donorNationalCode" placeholder="مثلاً: 0012345678" maxlength="10">
        </div>

        <div class="field">
          <label>مبلغ اهدایی مورد نظر</label>
          <div class="amount-grid">
            <button type="button" class="amount-chip" onclick="setAmount(50000, event)">۵۰,۰۰۰</button>
            <button type="button" class="amount-chip" onclick="setAmount(100000, event)">۱۰۰,۰۰۰</button>
            <button type="button" class="amount-chip active" onclick="setAmount(200000, event)">۲۰۰,۰۰۰</button>
            <button type="button" class="amount-chip" onclick="setAmount(500000, event)">۵۰۰,۰۰۰</button>
          </div>
        </div>

        <div class="field">
          <label>مبلغ دلخواه شما (تومان)</label>
          <input type="number" id="customAmount" name="amount" placeholder="مبلغ دلخواه را وارد کنید" value="200000">
        </div>

        <div class="field">
          <label>بابت درمان (اختیاری)</label>
          <input type="text" name="note" placeholder="مثلاً: تأمین داروی شیمی‌درمانی">
        </div>

        <button type="button" id="startDonationBtn" class="submit-btn" onclick="startDonationFlow()">
          <span>تایید و دریافت کد پیامکی</span>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>

        <p class="form-note">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 4px;">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          تراکنش‌های مالی رمزنگاری‌شده توسط پروتکل امن SSL بانک مرکزی
        </p>
      </form>

      <!-- OTP Verification Modal -->
      <div id="otpModal" class="otp-modal-overlay" style="display: none;">
        <div class="otp-modal-box">
          <div class="otp-modal-header">
            <div class="otp-icon-wrap">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#10aeb8" stroke-width="2">
                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                <line x1="12" y1="18" x2="12.01" y2="18"></line>
              </svg>
            </div>
            <h4>تأیید شماره همراه و ساخت حساب</h4>
            <p>کد ۵ رقمی پیامک‌شده به شماره <strong id="otpPhoneLabel"></strong> را وارد کنید:</p>
          </div>

          <div class="field otp-input-field">
            <input type="text" id="otpCodeInput" maxlength="6" placeholder="• • • • •" autocomplete="one-time-code" dir="ltr">
            <div id="otpErrorMsg" class="otp-error-msg" style="display: none;"></div>
          </div>

          <div class="otp-timer-row">
            <span id="otpTimerText">ارسال مجدد تا: <strong id="otpCountdown">02:00</strong></span>
            <button type="button" id="otpResendBtn" class="otp-resend-btn" style="display: none;" onclick="resendOtp()">ارسال مجدد کد پیامکی</button>
          </div>

          <div class="otp-actions">
            <button type="button" id="otpSubmitBtn" class="submit-btn" onclick="submitOtpAndPay()">
              <span>تایید و انتقال به درگاه بانکی</span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </button>
            <button type="button" class="otp-back-btn" onclick="closeOtpModal()">ویرایش مشخصات</button>
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
  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }

  :root {
    --bg-color: #f7fafc;
    --text-main: #1e293b;
    --text-muted: #64748b;

    --primary-color: #10aeb8;
    --primary-dark: #05646e;
    --primary-mid: #07828e;
    --primary-light: #e9fbfc;

    --accent-gold: #fff3c4;
    --accent-gold-dark: #c29d0b;

    --card-bg: rgba(255, 255, 255, 0.94);
    --border-color: rgba(255,255,255,0.18);

    --shadow-premium: 0 25px 60px -15px rgba(15, 139, 147, 0.16), 0 15px 30px -10px rgba(0, 0, 0, 0.06);
    --radius-large: 28px;
    --radius-medium: 20px;
  }

  * {
    box-sizing: border-box;
  }

  .donation-shell {
    padding: 70px 20px;
    direction: rtl;
    font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, sans-serif;
    background:
      radial-gradient(circle at 12% 10%, rgba(16,174,184,0.08), transparent 30%),
      radial-gradient(circle at 88% 88%, rgba(16,174,184,0.05), transparent 35%),
      linear-gradient(180deg, #f8fcfc 0%, #eff9fa 100%);
    position: relative;
    overflow: hidden;
  }

  .donation-card {
    max-width: 1400px;
    margin: 0 auto;
    background: rgba(255,255,255,0.78);
    border: 1px solid rgba(255,255,255,0.7);
    border-radius: var(--radius-large);
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,0.75),
      0 30px 70px rgba(7, 130, 142, 0.10),
      0 18px 40px rgba(0,0,0,0.04);
    backdrop-filter: blur(12px);
    display: grid;
    grid-template-columns: 0.95fr 1.05fr;
    gap: 34px;
    padding: 34px;
    position: relative;
    overflow: hidden;
    align-items: stretch;
  }

  .donation-card::before {
    content: "";
    position: absolute;
    width: 260px;
    height: 260px;
    left: -90px;
    top: -90px;
    border-radius: 50%;
    background: rgba(16,174,184,0.08);
    pointer-events: none;
  }

  .donation-card::after {
    content: "";
    position: absolute;
    width: 220px;
    height: 220px;
    right: -80px;
    bottom: -80px;
    border-radius: 50%;
    background: rgba(16,174,184,0.06);
    pointer-events: none;
  }

  .donation-card > * {
    position: relative;
    z-index: 1;
  }

  /* Right Panel */
  .donation-hero {
    padding: 40px;
    border-radius: var(--radius-medium);
    background:
      radial-gradient(circle at 10% 10%, rgba(255,255,255,0.55), transparent 34%),
      linear-gradient(155deg, rgba(255,255,255,0.82) 0%, rgba(240,252,253,0.88) 100%);
    border: 1px solid rgba(16,174,184,0.10);
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,0.7),
      0 18px 40px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
  }

  .donation-hero-content {
    display: flex;
    flex-direction: column;
  }

  .donation-media {
    margin-bottom: 28px;
  }

  .donation-media-frame {
    position: relative;
    width: 100%;
    height: 280px;
    border-radius: 24px;
    overflow: hidden;
    background:
      linear-gradient(135deg, rgba(16,174,184,0.14), rgba(16,174,184,0.04)),
      #f8fbfc;
    border: 1px solid rgba(16,174,184,0.14);
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,0.65),
      0 18px 42px rgba(7,130,142,0.12);
  }

  .donation-media-frame img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    object-position: center;
  }

  .donation-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: var(--primary-dark);
    padding: 30px;
    text-align: center;
  }

  .donation-media-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(to top, rgba(15, 41, 59, 0.24) 0%, rgba(15, 41, 59, 0.02) 45%, rgba(255,255,255,0) 100%);
  }

  .media-chip {
    position: absolute;
    top: 16px;
    right: 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.86);
    backdrop-filter: blur(8px);
    color: var(--primary-dark);
    font-size: 12px;
    font-weight: 900;
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  }

  .donation-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    width: fit-content;
    padding: 9px 16px;
    border-radius: 999px;
    background: rgba(16,174,184,0.10);
    color: var(--primary-dark);
    font-weight: 900;
    font-size: 13px;
    margin-bottom: 24px;
    border: 1px solid rgba(16,174,184,0.10);
  }

  .donation-title {
    margin: 0 0 20px;
    font-size: 38px;
    line-height: 1.45;
    font-weight: 950;
    color: var(--text-main);
    letter-spacing: -0.5px;
  }

  .donation-title span {
    color: var(--primary-mid);
    position: relative;
    display: inline-block;
  }

  .donation-title span::after {
    content: '';
    position: absolute;
    bottom: 5px;
    left: 0;
    width: 100%;
    height: 9px;
    background: rgba(16,174,184,0.14);
    z-index: -1;
    border-radius: 999px;
  }

  .donation-desc {
    margin: 0 0 35px;
    color: var(--text-muted);
    font-size: 16px;
    line-height: 2;
  }

  .premium-features {
    display: flex;
    flex-direction: column;
    gap: 22px;
    margin-bottom: 40px;
  }

  .feature-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 18px;
    border-radius: 18px;
    background: rgba(255,255,255,0.60);
    border: 1px solid rgba(16,174,184,0.08);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
  }

  .feature-icon-wrapper {
    background: rgba(16,174,184,0.10);
    border: 1px solid rgba(16,174,184,0.12);
    width: 50px;
    height: 50px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .feature-text h4 {
    margin: 0 0 6px;
    font-size: 16px;
    font-weight: 900;
    color: var(--text-main);
  }

  .feature-text p {
    margin: 0;
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.8;
  }

  .phone-link {
    color: var(--primary-dark);
    font-family: inherit;
    font-weight: 900;
    letter-spacing: 0.5px;
  }

  .donation-stats-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-radius: 20px;
    background: rgba(255,255,255,0.78);
    border: 1px solid rgba(16,174,184,0.10);
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,0.75),
      0 10px 24px rgba(0,0,0,0.04);
  }

  .stat-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
  }

  .stat-number {
    font-size: 21px;
    font-weight: 950;
    color: var(--primary-mid);
    margin-bottom: 2px;
  }

  .stat-label {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 800;
  }

  .stat-divider {
    width: 1px;
    height: 30px;
    background-color: rgba(16,174,184,0.12);
  }

  /* Left Panel */
  .donation-form-wrap {
    position: relative;
    padding: 26px;
    border-radius: var(--radius-medium);
    background:
      radial-gradient(circle at 12% 10%, rgba(255,255,255,0.24), transparent 34%),
      radial-gradient(circle at 88% 88%, rgba(255,255,255,0.14), transparent 38%),
      linear-gradient(155deg, #10aeb8 0%, #07828e 54%, #05646e 100%);
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,0.20),
      0 22px 48px rgba(15, 159, 170, 0.16);
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
  }

  .donation-form-wrap::before {
    content: "";
    position: absolute;
    width: 220px;
    height: 220px;
    left: -90px;
    bottom: -70px;
    border-radius: 50%;
    background: rgba(255,255,255,0.09);
    pointer-events: none;
  }

  .donation-form-wrap::after {
    content: "";
    position: absolute;
    width: 180px;
    height: 180px;
    right: -60px;
    top: -60px;
    border-radius: 50%;
    background: rgba(255,255,255,0.10);
    pointer-events: none;
  }

  .donation-form {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.10);
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 24px;
    padding: 34px;
    color: #ffffff;
    backdrop-filter: blur(10px);
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,0.16),
      0 14px 30px rgba(0,0,0,0.08);
  }

  .form-header {
    margin-bottom: 24px;
  }

  .form-header h3 {
    margin: 0 0 8px;
    font-size: 24px;
    font-weight: 950;
  }

  .form-header p {
    margin: 0;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.82);
    line-height: 1.9;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }

  .field {
    margin-bottom: 20px;
  }

  .field label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 900;
    color: rgba(255, 255, 255, 0.96);
  }

  .field input {
    width: 100%;
    height: 52px;
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.14);
    color: #ffffff;
    padding: 0 16px;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: all 0.22s ease;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
  }

  .field input::placeholder {
    color: rgba(255, 255, 255, 0.55);
  }

  .field input:focus {
    background: rgba(255, 255, 255, 0.20);
    border-color: var(--accent-gold);
    box-shadow: 0 0 0 4px rgba(255, 243, 196, 0.16);
  }

  .amount-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
  }

  .amount-chip {
    height: 48px;
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.12);
    color: white;
    font-family: inherit;
    font-weight: 900;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.22s ease;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
  }

  .amount-chip:hover {
    background: rgba(255, 255, 255, 0.20);
    transform: translateY(-1px);
  }

  .amount-chip.active {
    background: linear-gradient(180deg, #fff3c4 0%, #fff7da 100%);
    color: var(--primary-dark);
    border-color: var(--accent-gold);
    box-shadow: 0 10px 18px rgba(255, 243, 196, 0.18);
  }

  .submit-btn {
    width: 100%;
    height: 58px;
    margin-top: 12px;
    border: none;
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #f1ffff 100%);
    color: #087985;
    font-family: inherit;
    font-size: 16px;
    font-weight: 950;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,0.6),
      0 12px 26px rgba(0, 0, 0, 0.12);
    transition: all 0.25s ease;
  }

  .submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 30px rgba(0, 0, 0, 0.16);
  }

  .submit-btn svg {
    transform: scaleX(-1);
    transition: transform 0.2s;
  }

  .submit-btn:hover svg {
    transform: scaleX(-1) translateX(4px);
  }

  .form-note {
    margin: 16px 0 0;
    font-size: 11px;
    line-height: 1.9;
    color: rgba(255, 255, 255, 0.78);
    text-align: center;
  }

  /* Responsive */
  @media (max-width: 1024px) {
    .donation-card {
      grid-template-columns: 1fr;
      padding: 24px;
    }
  }

  @media (max-width: 768px) {
    .donation-shell {
      padding: 34px 15px;
    }

    .donation-hero {
      padding: 25px;
    }

    .donation-title {
      font-size: 29px;
    }

    .donation-form {
      padding: 22px;
    }

    .amount-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .donation-media-frame {
      height: 220px;
    }
  }

  @media (max-width: 480px) {
    .form-grid {
      grid-template-columns: 1fr;
    }

    .donation-media-frame {
      height: 200px;
      border-radius: 18px;
    }

    .media-chip {
      top: 12px;
      right: 12px;
      font-size: 11px;
      padding: 7px 12px;
    }

    .donation-title {
      font-size: 25px;
    }
  /* OTP Modal Styles */
  .otp-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(5, 100, 110, 0.82);
    backdrop-filter: blur(10px);
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    border-radius: var(--radius-large);
    animation: otpFadeIn 0.25s ease-out;
  }

  @keyframes otpFadeIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
  }

  .otp-modal-box {
    background: rgba(255, 255, 255, 0.96);
    color: var(--text-main);
    border-radius: 24px;
    padding: 32px 28px;
    max-width: 440px;
    width: 100%;
    box-shadow: 0 20px 40px rgba(0,0,0,0.22);
    text-align: center;
    border: 1px solid rgba(255,255,255,0.8);
  }

  .otp-icon-wrap {
    width: 54px;
    height: 54px;
    margin: 0 auto 14px;
    border-radius: 50%;
    background: #e9fbfc;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .otp-modal-header h4 {
    margin: 0 0 6px;
    font-size: 19px;
    font-weight: 800;
    color: var(--primary-dark);
  }

  .otp-modal-header p {
    margin: 0 0 20px;
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.6;
  }

  .otp-input-field input {
    width: 100%;
    height: 58px;
    text-align: center;
    letter-spacing: 10px;
    font-size: 26px;
    font-weight: 900;
    border-radius: 16px;
    border: 2px solid #b2e8eb;
    background: #f8fcfc;
    color: #05646e;
    transition: all 0.2s;
  }

  .otp-input-field input:focus {
    border-color: #10aeb8;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(16, 174, 184, 0.15);
    outline: none;
  }

  .otp-error-msg {
    margin-top: 8px;
    font-size: 13px;
    color: #dc2626;
    background: #fee2e2;
    padding: 6px 12px;
    border-radius: 8px;
  }

  .otp-timer-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 16px 0 22px;
    font-size: 13px;
    color: var(--text-muted);
  }

  .otp-resend-btn {
    background: none;
    border: none;
    color: #10aeb8;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    text-decoration: underline;
    font-size: 13px;
  }

  .otp-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .otp-actions .submit-btn {
    background: linear-gradient(180deg, #10aeb8 0%, #07828e 100%);
    color: white;
  }

  .otp-back-btn {
    background: transparent;
    border: 1px solid #cbd5e1;
    border-radius: 16px;
    height: 48px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: background 0.2s;
  }

  .otp-back-btn:hover {
    background: #f1f5f9;
  }
</style>


<script>
  let otpCountdownTimer = null;
  let remainingSeconds = 120;

  function setAmount(val, event) {
    const amountInput = document.getElementById('customAmount');
    amountInput.value = val;

    document.querySelectorAll('.amount-chip').forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');
  }

  function getFormValues() {
    const form = document.querySelector('.donation-form');
    return {
      first_name: form.querySelector('[name="first_name"]').value.trim(),
      last_name: form.querySelector('[name="last_name"]').value.trim(),
      phone: document.getElementById('donorPhone').value.trim(),
      national_code: document.getElementById('donorNationalCode').value.trim(),
      amount: parseInt(document.getElementById('customAmount').value, 10) || 0,
      note: form.querySelector('[name="note"]').value.trim()
    };
  }

  async function startDonationFlow() {
    const vals = getFormValues();

    if (!vals.first_name || !vals.last_name) {
      alert('لطفاً نام و نام خانوادگی خود را وارد کنید.');
      return;
    }

    const phoneRegex = /^09[0-9]{9}$/;
    if (!phoneRegex.test(vals.phone)) {
      alert('لطفاً شماره تلفن همراه معتبر ۱۱ رقمی (مانند 09123456789) وارد کنید.');
      return;
    }

    if (vals.national_code && !/^[0-9]{10}$/.test(vals.national_code)) {
      alert('کد ملی باید ۱۰ رقم عددی باشد.');
      return;
    }

    if (vals.amount < 1000) {
      alert('حداقل مبلغ اهدایی ۱,۰۰۰ تومان می‌باشد.');
      return;
    }

    const btn = document.getElementById('startDonationBtn');
    btn.disabled = true;
    const origText = btn.innerHTML;
    btn.innerHTML = '<span>در حال ارسال پیامک...</span>';

    try {
      const res = await fetch('/api/auth/otp/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          phone: vals.phone,
          purpose: 'donation_auth'
        })
      });
      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.message || 'خطا در ارسال پیامک کد تأیید');
      }

      openOtpModal(vals.phone, data.data?.debug_code);
    } catch (err) {
      alert(err.message || 'خطا در ارتباط با سرور.');
    } finally {
      btn.disabled = false;
      btn.innerHTML = origText;
    }
  }

  function openOtpModal(phone, debugCode) {
    const modal = document.getElementById('otpModal');
    document.getElementById('otpPhoneLabel').textContent = phone;
    document.getElementById('otpErrorMsg').style.display = 'none';
    const input = document.getElementById('otpCodeInput');
    input.value = debugCode || '';
    modal.style.display = 'flex';

    startTimer(120);
    setTimeout(() => input.focus(), 150);
  }

  function closeOtpModal() {
    clearInterval(otpCountdownTimer);
    document.getElementById('otpModal').style.display = 'none';
  }

  function startTimer(seconds) {
    clearInterval(otpCountdownTimer);
    remainingSeconds = seconds;
    const timerText = document.getElementById('otpTimerText');
    const countdown = document.getElementById('otpCountdown');
    const resendBtn = document.getElementById('otpResendBtn');

    timerText.style.display = 'inline';
    resendBtn.style.display = 'none';

    function update() {
      const m = Math.floor(remainingSeconds / 60);
      const s = remainingSeconds % 60;
      countdown.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

      if (remainingSeconds <= 0) {
        clearInterval(otpCountdownTimer);
        timerText.style.display = 'none';
        resendBtn.style.display = 'inline';
      }
      remainingSeconds--;
    }

    update();
    otpCountdownTimer = setInterval(update, 1000);
  }

  async function resendOtp() {
    const vals = getFormValues();
    const resendBtn = document.getElementById('otpResendBtn');
    resendBtn.textContent = 'در حال ارسال...';

    try {
      const res = await fetch('/api/auth/otp/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          phone: vals.phone,
          purpose: 'donation_auth'
        })
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'خطا در ارسال مجدد');

      startTimer(120);
      if (data.data?.debug_code) {
        document.getElementById('otpCodeInput').value = data.data.debug_code;
      }
    } catch (e) {
      alert(e.message || 'خطا در ارسال مجدد کد');
      resendBtn.textContent = 'ارسال مجدد کد پیامکی';
    }
  }

  async function submitOtpAndPay() {
    const vals = getFormValues();
    const code = document.getElementById('otpCodeInput').value.trim();
    const errorEl = document.getElementById('otpErrorMsg');

    if (code.length < 4) {
      errorEl.textContent = 'لطفاً کد تایید ۵ رقمی را وارد کنید.';
      errorEl.style.display = 'block';
      return;
    }

    const submitBtn = document.getElementById('otpSubmitBtn');
    submitBtn.disabled = true;
    const origText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span>در حال ایجاد حساب و اتصال به بانک...</span>';
    errorEl.style.display = 'none';

    try {
      const res = await fetch('/api/donations/initiate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          phone: vals.phone,
          code: code,
          first_name: vals.first_name,
          last_name: vals.last_name,
          national_code: vals.national_code || null,
          amount: vals.amount
        })
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.message || 'خطا در اعتبارسنجی کد یا اتصال به درگاه');
      }

      // Save token for authenticated session in donor dashboard
      if (data.data?.access_token) {
        localStorage.setItem('maksa_access_token', data.data.access_token);
      }

      // Hand off to the bank gateway
      if (data.data?.redirect_url) {
        window.location.href = data.data.redirect_url;
      } else {
        alert('پرداخت با موفقیت آغاز شد.');
      }
    } catch (err) {
      errorEl.textContent = err.message || 'خطا در اتصال به درگاه بانکی';
      errorEl.style.display = 'block';
      submitBtn.disabled = false;
      submitBtn.innerHTML = origText;
    }
  }
</script>

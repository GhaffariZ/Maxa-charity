# مشخصات فنی و طراحی معماری: احراز هویت پیامکی (OTP)، ثبت کاربر، همگام‌سازی CRM و پرداخت شاپرک

**تاریخ:** 2026-09-15  
**وضعیت:** مصوب (Approved)  
**سرویس‌های درگیر:** API مکسا (`public_html/api`), پایگاه داده (`database/migrations`), کامپوننت پرداخت عمومی (`public_html/dashboard/components/onlinedonation`), پنل خیرین (`benefactor userpanel`)

---

## ۱. اهداف و نیازمندی‌ها
1. **الزامات شاپرک:** دریافت اطلاعات هویتی پرداخت‌کننده شامل نام، نام خانوادگی، شماره موبایل احراز‌شده (با OTP) و فیلد اختیاری کد ملی (برای تطبیق شاهکار و معافیت مالیاتی ماده ۱۳۹).
2. **ثبت‌نام خودکار در پایگاه داده:** به محض ورود کد تایید پیامکی، کاربر به صورت رسمی در جدول `panel_users` و `user_profiles` ثبت یا لاگین شده و به عنوان کاربر فعال شناخته می‌شود.
3. **اتصال ساختاریافته به CRM:** طراحی یک لایه انتزاعی (`CrmServiceInterface` و `StubCrmDriver`) جهت ثبت خودکار لید/مخاطب در CRM در همان لحظه، با قابلیت اتصال آسان به هر سامانه CRM در آینده.
4. **شروع فرآیند اهدا:** ثبت اهدا در `panel_donations`، اتصال اطلاعات اهداکننده به شناسه کاربر، ارسال متادیتای پرداخت به درگاه شاپرک/زرین‌پال و هدایت کاربر به درگاه.
5. **ورود با پیامک در پنل خیرین:** امکان لاگین با شماره موبایل و کد یک‌بار مصرف در صفحه لاگین پنل کاربری.

---

## ۲. معماری داده و تغییرات دیتابیس

### مهاجرت `015_phone_otp_and_crm.sql`:
1. **جدول `panel_users`:**
   - افزودن ستون `phone VARCHAR(20) NULL UNIQUE`.
   - تغییر ستون‌های `email` و `password_hash` به `NULLABLE`.
   - افزودن ستون `phone_verified_at DATETIME NULL`.
   - ایجاد ایندکس `idx_panel_users_phone_status` روی `(phone, status)`.

2. **جدول `user_profiles`:**
   - افزودن ستون `national_code VARCHAR(10) NULL`.
   - افزودن ستون `crm_id VARCHAR(100) NULL` و `crm_synced_at DATETIME NULL`.

3. **جدول جدید `otp_codes`:**
   - `id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY`
   - `phone VARCHAR(20) NOT NULL`
   - `code_hash VARCHAR(255) NOT NULL`
   - `purpose ENUM('donation_auth', 'login') NOT NULL DEFAULT 'donation_auth'`
   - `ip_address VARCHAR(45) NOT NULL`
   - `attempts TINYINT UNSIGNED NOT NULL DEFAULT 0`
   - `expires_at DATETIME NOT NULL`
   - `consumed_at DATETIME NULL DEFAULT NULL`
   - `created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP`
   - ایندکس روی `(phone, purpose, expires_at)`.

---

## ۳. موتور پیامک و اعتبارسنجی OTP

### ساختار کلاس‌ها:
- `Maksa\Services\Sms\SmsProviderInterface`:
  - `sendOtp(string $phone, string $code): bool`
- `Maksa\Services\Sms\MockSmsProvider`:
  - در حالت پیش‌فرض و توسعه، کد را در `logs/app.log` ثبت کرده و برای تست محیط توسعه فعال است.
- `Maksa\Services\Sms\KavenegarSmsProvider` و `FarazSmsProvider`:
  - آماده‌سازی تنظیمات در `.env`:
    ```ini
    SMS_DRIVER=mock # mock | kavenegar | faraz
    SMS_API_KEY=
    SMS_SENDER=
    SMS_OTP_PATTERN=
    ```
- `Maksa\Services\OtpService`:
  - `send(string $phone, string $purpose, string $ip): array`
  - `verify(string $phone, string $code, string $purpose): bool`
  - اعمال Rate Limiting: حداکثر ۱ بار در هر ۶۰ ثانیه به یک شماره، حداکثر ۵ درخواست در ساعت از یک IP، حداکثر ۵ تلاش اشتباه قبل از باطل شدن کد.

---

## ۴. ماژول ارتباط با CRM

### ساختار کلاس‌ها:
- `Maksa\Services\Crm\CrmServiceInterface`:
  - `createOrUpdateDonor(array $donorData): ?string` (بازگرداندن شناسه مخاطب در CRM)
  - `recordDonation(string $crmContactId, array $donationData): bool`
- `Maksa\Services\Crm\Drivers\StubCrmDriver`:
  - پیاده‌سازی اولیه برای ثبت رویدادها در لاگ و بازگرداندن شناسه شبیه‌سازی‌شده (مانند `CRM-xxxx`).
- متغیرهای محیطی:
  ```ini
  CRM_DRIVER=stub # stub | sarv | didar | webhook
  CRM_API_URL=
  CRM_API_KEY=
  ```

---

## ۵. جریان پرداخت آنلاین و اتصال شاپرک

### فرآیند در کنترلر اهدا (`DonationController` / `DonationService`):
1. اندپوینت جدید `POST /api/donations/initiate`:
   - ورودی‌ها: `phone`, `code`, `first_name`, `last_name`, `national_code` (اختیاری), `amount`, `campaign_slug` (اختیاری).
   - مرحله ۱: اعتبارسنجی OTP.
   - مرحله ۲: ایجاد یا واکشی کاربر در `panel_users` و `user_profiles`.
   - مرحله ۳: فراخوانی `CrmService::createOrUpdateDonor`.
   - مرحله ۴: صدور توکن‌های دسترسی JWT و کوکی احراز هویت.
   - مرحله ۵: ثبت اهدا در `panel_donations` و ارسال به درگاه (با متادیتای شماره موبایل و شناسه اهدا مطابق الزامات شاپرک).
   - خروجی: آدرس درگاه پرداخت (`redirect_url`)، شناسه اهدا (`reference`) و توکن احراز هویت.

---

## ۶. رابط کاربری (Frontend UX)

1. **کامپوننت پرداخت آنلاین (`public_html/dashboard/components/onlinedonation/component.php`):**
   - افزودن فیلد کد ملی (اختیاری).
   - تبدیل دکمه پرداخت به یک گام باز شونده یا مودال ورود کد پیامکی (OTP).
   - تایمر معکوس ۱۲۰ ثانیه و دکمه ارسال مجدد کد.
   - هدایت روان به درگاه بدون رفرش کل صفحه.
2. **فرم لاگین پنل کاربری (`benefactor-dashboard`):**
   - افزودن تب «ورود با پیامک» با ارسال کد و ورود بدون رمز عبور.

---

## ۷. برنامه آزمون و راستی‌آزمایی
1. تست تولید و انقضای OTP و تست محدودیت تلاش‌های اشتباه (Rate Limit).
2. تست ایجاد همزمان کاربر در دیتابیس با وضعیت `active` و بررسی رکوردهای `user_profiles`.
3. تست فعال شدن درایور CRM و ثبت متادیتای مخاطب.
4. تست ایجاد تراکنش معلق و تطبیق ساختار ارسالی با درگاه پرداخت.

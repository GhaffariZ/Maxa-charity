# پیاده‌سازی سامانه احراز هویت پیامکی (OTP)، ثبت کاربر، همگام‌سازی CRM و اتصال پرداخت شاپرک

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** پیاده‌سازی دریافت فیلدهای هویتی شاپرک (نام، شماره موبایل با تاییدیه پیامکی OTP، و کد ملی اختیاری) در فرآیند کمک آنلاین و کمپین‌ها، به همراه ثبت‌نام آنی کاربر در پایگاه داده، اتصال به لایه انتزاعی CRM، و انتقال به درگاه پرداخت شاپرک.

**Architecture:** یک سیستم یکپارچه شامل موتور OTP امن با درایور ماژولار پیامک، لایه انتزاعی ارتباط با CRM (در قالب اینترفیس و درایور Stub قابل اتصال به هر CRM)، ارتقای مدل کاربری دیتابیس به ورود موبایل‌محور (بدون پسورد اجباری)، و ارتقای فرم‌های اهدای وب‌سایت و صفحه ورود پنل کاربری.

**Tech Stack:** PHP 8+ (PDO, OpenSSL), MariaDB/MySQL, Vanilla JS / Tailwind CSS (سایت عمومی)، React + TypeScript (پنل کاربری خیرین).

**Spec:** `docs/superpowers/specs/2026-09-15-shaparak-otp-donation-crm-design.md`

## Global Constraints
- اعتبارسنجی دقیق شماره‌های موبایل ایران با الگوی `^09[0-9]{9}$`.
- کدهای OTP باید با انقضای ۱۲۰ ثانیه و حداکثر ۵ تلاش مجاز باشند و در دیتابیس به صورت هش‌شده ذخیره گردند.
- کد ملی (در صورت وارد شدن) با الگوی ۱۰ رقمی و الگوریتم کنترل رقم کنترلی معتبر شود اما اجباری نباشد.
- لایه CRM باید کاملاً ایزوله و درایورمحور باشد تا در آینده بدون دستکاری هسته به هر CRM متصل شود.
- سازگاری کامل با تراکنش‌های قبلی `panel_donations` و حفظ کلید خارجی به `panel_users`.

---

### Task 1: ایجاد مایگریشن پایگاه داده (`database/migrations/015_phone_otp_and_crm.sql`)

**Files:**
- Create: `database/migrations/015_phone_otp_and_crm.sql`
- Test: `scratch/test_migration_015.php`

**Interfaces:**
- Produces: 
  - جدول `otp_codes` با ستون‌های `id`, `phone`, `code_hash`, `purpose`, `ip_address`, `attempts`, `expires_at`, `consumed_at`, `created_at`.
  - ستون `phone` یکتا در `panel_users` و نال‌پذیر شدن `email` و `password_hash`.
  - ستون‌های `national_code`, `crm_id`, `crm_synced_at` در `user_profiles`.

- [ ] **Step 1: ایجاد فایل اسکریپت مایگریشن**

ایجاد فایل `database/migrations/015_phone_otp_and_crm.sql` شامل DDL تغییرات جداول `panel_users`، `user_profiles` و ساخت جدول `otp_codes`.

- [ ] **Step 2: اعتبارسنجی ساختار اسکریپت SQL**

بررسی صحت سینتکس SQL و سازگاری با MariaDB/MySQL.

- [ ] **Step 3: ثبت و کامیت مایگریشن**

```bash
git add database/migrations/015_phone_otp_and_crm.sql
git commit -m "db(migration): add 015_phone_otp_and_crm migration"
```

---

### Task 2: پیاده‌سازی کلاس `Config` و زیرسیستم ارسال پیامک (`SmsProvider`)

**Files:**
- Create: `public_html/api/src/Core/Config.php`
- Create: `public_html/api/src/Services/Sms/SmsProviderInterface.php`
- Create: `public_html/api/src/Services/Sms/MockSmsProvider.php`
- Create: `public_html/api/src/Services/Sms/KavenegarSmsProvider.php`
- Create: `public_html/api/src/Services/Sms/FarazSmsProvider.php`
- Create: `public_html/api/src/Services/Sms/SmsService.php`

**Interfaces:**
- Produces:
  - `Config::get(string $key, mixed $default = null): mixed`
  - `Config::require(string $key): mixed`
  - `Config::bool(string $key, bool $default = false): bool`
  - `Config::int(string $key, int $default = 0): int`
  - `SmsProviderInterface::sendOtp(string $phone, string $code): bool`
  - `SmsService::sendOtp(string $phone, string $code): bool`

- [ ] **Step 1: پیاده‌سازی `Maksa\Core\Config`**
خواندن متغیرهای محیطی از فایل `.env`، پشتیبانی از متدهای کمکی `get`, `require`, `bool`, `int`.

- [ ] **Step 2: پیاده‌سازی اینترفیس `SmsProviderInterface` و درایورها**
ایجاد اینترفیس ارسال پیامک، درایور `MockSmsProvider` (ثبت در فایل لاگ برای تست)، درایور کاوه‌نگار و فراز اس‌ام‌اس.

- [ ] **Step 3: پیاده‌سازی فکتوری `SmsService`**
انتخاب ارائه‌دهنده پیامک بر اساس تنظیمات `SMS_DRIVER` در کانفیگ پروژه.

- [ ] **Step 4: کامیت تسک ۲**

```bash
git add public_html/api/src/Core/Config.php public_html/api/src/Services/Sms/
git commit -m "feat(sms): add config helper and modular sms provider subsystem"
```

---

### Task 3: پیاده‌سازی موتور کد یک‌بار مصرف (`OtpService`) و اندپوینت‌های مربوطه

**Files:**
- Create: `public_html/api/src/Services/OtpService.php`
- Create: `public_html/api/src/Controllers/OtpController.php`
- Modify: `public_html/api/src/routes.php`

**Interfaces:**
- Consumes: `SmsService`, `Database`, `Config`
- Produces:
  - `POST /api/auth/otp/send` -> `{ "phone": "0912...", "purpose": "donation_auth" }`
  - `POST /api/auth/otp/verify` -> `{ "phone": "0912...", "code": "12345", "purpose": "donation_auth" }`

- [ ] **Step 1: پیاده‌سازی لاجیک تولید، هش و راستی‌آزمایی در `OtpService`**
تولید کد ۵ رقمی، بررسی فاصله‌ی زمانی ۶۰ ثانیه‌ای بین درخواست‌ها، بررسی انقضای ۱۲۰ ثانیه‌ای، ذخیره هش در جدول `otp_codes` و تایید صحت کد.

- [ ] **Step 2: ایجاد `OtpController`**
پیاده‌سازی اعتبارسنجی ورودی‌های شماره همراه، جلوگیری از حملات Brute Force و بازگرداندن پاسخ‌های استاندارد JSON.

- [ ] **Step 3: رجیستر کردن مسیرها در `routes.php`**
افزودن مسیرهای عمومی ارسال و بررسی کد OTP.

- [ ] **Step 4: کامیت تسک ۳**

```bash
git add public_html/api/src/Services/OtpService.php public_html/api/src/Controllers/OtpController.php public_html/api/src/routes.php
git commit -m "feat(otp): add OtpService and OTP send/verify endpoints"
```

---

### Task 4: پیاده‌سازی لایه انتزاعی یکپارچه‌سازی با CRM (`CrmService`)

**Files:**
- Create: `public_html/api/src/Services/Crm/CrmDriverInterface.php`
- Create: `public_html/api/src/Services/Crm/Drivers/StubCrmDriver.php`
- Create: `public_html/api/src/Services/Crm/CrmService.php`

**Interfaces:**
- Produces:
  - `CrmDriverInterface::createOrUpdateDonor(array $donor): ?string`
  - `CrmDriverInterface::recordDonation(string $crmId, array $donation): bool`
  - `CrmService::syncDonor(int $userId, array $data): ?string`
  - `CrmService::syncDonation(int $donationId, array $data): bool`

- [ ] **Step 1: پیاده‌سازی اینترفیس `CrmDriverInterface`**
تعریف امضای متدهای ثبت مخاطب/خیر و ثبت تراکنش اهدا در سیستم CRM.

- [ ] **Step 2: پیاده‌سازی `StubCrmDriver` و `CrmService`**
تولید شناسه موقت، ثبت در لاگ سیستم و ذخیره `crm_id` و `crm_synced_at` در پروفایل کاربر.

- [ ] **Step 3: کامیت تسک ۴**

```bash
git add public_html/api/src/Services/Crm/
git commit -m "feat(crm): add modular CRM abstraction layer and Stub driver"
```

---

### Task 5: ارتقای `UserRepository` و ورود موبایل‌محور خیرین

**Files:**
- Modify: `public_html/api/src/Repositories/UserRepository.php`
- Modify: `public_html/api/src/Controllers/AuthController.php`
- Modify: `public_html/api/src/routes.php`

**Interfaces:**
- Consumes: `OtpService`, `Jwt`, `Cookie`, `CrmService`
- Produces:
  - `UserRepository::findByPhone(string $phone): ?array`
  - `UserRepository::createOrUpdateDonorByPhone(string $phone, ?string $firstName, ?string $lastName, ?string $nationalCode): int`
  - `POST /api/auth/login-otp` -> ورود مستقیم خیرین به پنل با شماره و کد OTP

- [ ] **Step 1: افزودن متدهای شماره همراه به `UserRepository`**
یافتن کاربر با شماره، ساخت اتمیک کاربر و پروفایل با شماره همراه و ثبت کد ملی.

- [ ] **Step 2: پیاده‌سازی اکشن `loginWithOtp` در `AuthController`**
بررسی کد پیامکی، واکشی کاربر، صدور توکن‌های JWT و کوکی احراز هویت.

- [ ] **Step 3: کامیت تسک ۵**

```bash
git add public_html/api/src/Repositories/UserRepository.php public_html/api/src/Controllers/AuthController.php public_html/api/src/routes.php
git commit -m "feat(auth): support phone-based donor registration and OTP login"
```

---

### Task 6: جریان پرداخت با OTP و ارسال متادیتای شاپرک

**Files:**
- Modify: `public_html/api/src/Services/Gateway/ZarinpalGateway.php`
- Modify: `public_html/api/src/Services/DonationService.php`
- Modify: `public_html/api/src/Controllers/DonationController.php`
- Modify: `public_html/api/src/routes.php`

**Interfaces:**
- Produces:
  - `POST /api/donations/initiate` (عمومی با فیلدهای `phone`, `code`, `first_name`, `last_name`, `national_code`, `amount`, `campaign_slug`).
  - ارسال فیلدهای شاپرک (`mobile`, `name`, `order_id`) به درگاه بانکی.

- [ ] **Step 1: به‌روزرسانی درگاه پرداخت جهت ارسال شماره موبایل خریدار به شاپرک**
افزودن پارامترهای `mobile` و شناسه در متادیتای ارسالی به درگاه پرداخت.

- [ ] **Step 2: پیاده‌سازی متد `initiateWithOtp` در `DonationService` و `DonationController`**
تایید OTP -> ساخت/یافتن حساب کاربر -> ثبت لید در CRM -> صدور توکن -> ساخت رکورد اهدا -> بازگرداندن URL درگاه.

- [ ] **Step 3: کامیت تسک ۶**

```bash
git add public_html/api/src/Services/DonationService.php public_html/api/src/Controllers/DonationController.php public_html/api/src/Services/Gateway/ public_html/api/src/routes.php
git commit -m "feat(donations): implement initiate donation with OTP, user creation and CRM sync"
```

---

### Task 7: ارتقای رابط کاربری کمک آنلاین در سایت (`onlinedonation/component.php`)

**Files:**
- Modify: `public_html/dashboard/components/onlinedonation/component.php`

**Interfaces:**
- Consumes: `POST /api/auth/otp/send`, `POST /api/donations/initiate`

- [ ] **Step 1: افزودن فیلد کد ملی (اختیاری) و استایل‌های بخش تایید پیامکی**
طراحی استپ ورودی کد تایید ۵ رقمی همراه با شمارنده معکوس ۱۲۰ ثانیه و دکمه ارسال مجدد.

- [ ] **Step 2: اتصال کلاینت جاوااسکریپت به وب‌سرویس‌های OTP و اهدا**
ارسال کد با کلیک، نمایش مودال/فرم تایید، و انتقال مستقیم به درگاه بانکی بدون بارگذاری مجدد صفحه.

- [ ] **Step 3: کامیت تسک ۷**

```bash
git add public_html/dashboard/components/onlinedonation/component.php
git commit -m "feat(ui): add OTP verification step and national code field to online donation"
```

---

### Task 8: افزودن تب ورود با پیامک به پنل کاربری خیرین (`benefactor-dashboard`)

**Files:**
- Modify: `benefactor userpanel/src/api/client.ts`
- Modify: `benefactor userpanel/src/app/views/Login.tsx`

**Interfaces:**
- Consumes: `POST /api/auth/otp/send`, `POST /api/auth/login-otp`

- [ ] **Step 1: افزودن متدهای OTP به کلاینت API پنل ریکت**
متدهای `sendOtp` و `loginWithOtp`.

- [ ] **Step 2: ارتقای ویوی `Login.tsx` با تب ورود پیامکی**
امکان جابجایی بین «ورود با رمز عبور» و «ورود با شماره موبایل و کد یک‌بار مصرف».

- [ ] **Step 3: کامیت تسک ۸**

```bash
git add "benefactor userpanel/src/api/client.ts" "benefactor userpanel/src/app/views/Login.tsx"
git commit -m "feat(panel): add phone OTP login to benefactor dashboard"
```

---

### Task 9: تست نهایی و اعتبارسنجی جامع (Verification)

**Files:**
- Create: `scratch/verify_shaparak_otp_flow.php`

- [ ] **Step 1: اجرای اسکریپت تست انتها به انتها (End-to-End Test)**
تست درخواست OTP، تست بررسی تلاش اشتباه، تست احراز و ساخت کاربر در دیتابیس، بررسی تریگر شدن درایور CRM، و بررسی ساخت رکورد اهدا با ارجاع به درگاه بانکی.

- [ ] **Step 2: اجرای آزمون نهایی و تهیه گزارش Walkthrough**
ثبت گزارش تغییرات در `walkthrough.md`.

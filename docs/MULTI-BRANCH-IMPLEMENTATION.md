# پیاده‌سازی احراز هویت + داشبورد چندشعبه‌ای مکسا

این سند خلاصه‌ی تغییرات و **مراحل استقرار** را شرح می‌دهد. اسپک کامل در
[`PROMPT-multi-branch-dashboard.en.md`](PROMPT-multi-branch-dashboard.en.md) است.

> ⚠️ همه‌چیز فقط با **فایل‌های مهاجرت/اسکریپت** تحویل شده است؛ هیچ تغییری روی دیتابیس
> زنده اعمال نشده. مراحل زیر را خودتان روی دیتابیس اجرا کنید.

---

## ۱) مراحل استقرار (به ترتیب)

### گام ۱ — اجرای مهاجرت دیتابیس
```bash
mysql -u USER -p DBNAME < database/migrations/001_multi_branch.sql
```
این مهاجرت:
- جدول‌های `branches`, `dashboard_users`, `dashboard_roles`, `branch_features` را می‌سازد.
- ستون `branch_id` را به `pages`, `news`, `campaigns`, `hero_slides`, `courses`,
  `employee_profiles`, `panel_donations` اضافه و همه را به **دفتر مرکزی (id=1)** نسبت می‌دهد.
- نقش پیش‌فرض «خبرنگار» را برای دفتر مرکزی می‌سازد.

> **نکته‌ی مهم درباره‌ی `pages.slug`:** برای اینکه هر شعبه بتواند صفحه‌ی `home` خود را
> داشته باشد، یکتایی باید **ترکیبی** شود: `UNIQUE(branch_id, slug)`. اگر روی جدول
> `pages` از قبل یک کلیدِ یکتای تک‌ستونی روی `slug` دارید، ابتدا آن را حذف کنید:
> ```sql
> SHOW INDEX FROM pages;                 -- نام ایندکس یکتای فعلی روی slug را پیدا کنید
> ALTER TABLE pages DROP INDEX <نام_ایندکس>;
> ```
> سپس مهاجرت را (دوباره) اجرا کنید تا `uq_pages_branch_slug` ساخته شود. مهاجرت idempotent است.

### گام ۲ — ساخت ادمین مرکزی (Super Admin)
از طریق CLI (رمز در کد ذخیره نمی‌شود):
```bash
ADMIN_USER=admin ADMIN_PASS='YourStrongPassword!' php database/seed-admin.php
# یا
php database/seed-admin.php --user=admin --pass='YourStrongPassword!' --name="مدیر مرکزی"
```

### گام ۳ — ورود
به `/dashboard/login.php` بروید و وارد شوید. صفحه‌ی `register.php` غیرفعال شده است.

---

## ۲) چه چیزهایی اضافه/تغییر کرد

### فایل‌های جدید
| فایل | نقش |
|------|-----|
| `database/migrations/001_multi_branch.sql` | مهاجرت چندشعبه‌ای + backfill |
| `database/seed-admin.php` | اسکریپت CLI ساخت ادمین مرکزی |
| `public_html/core/dashboard-auth.php` | **هسته‌ی احراز هویت**: PDO، session امن، CSRF، rate-limit، دسترسی‌ها، ایزولاسیون شعبه، audit |
| `public_html/dashboard/_guard.php` | بوت‌استرپِ گارد که بالای همه‌ی صفحات پنل require می‌شود |
| `public_html/dashboard/_panel_head.php` | سربرگ مشترک طراحیِ صفحات فرم |
| `public_html/dashboard/logout.php` | خروج امن |
| `public_html/dashboard/switch-branch.php` | تغییر شعبه‌ی فعال (فقط سوپرادمین، CSRF) |
| `public_html/dashboard/branch-create.php` | تعریف شعبه‌ی جدید (شعبه + ادمین + قابلیت‌ها + پوشه + صفحه‌ی home) |
| `public_html/dashboard/branch-list.php` | مدیریت/فعال‌سازی شعبه‌ها |
| `public_html/dashboard/user-add.php` | افزودن کاربر شعبه + سیستم نقش |
| `public_html/dashboard/user-manage.php` | مدیریت/غیرفعال‌سازی کاربران شعبه |

### فایل‌های تغییریافته
- `public_html/dashboard/login.php` — بازنویسی کامل: واحد، برنددار، CSRF، rate-limit.
- `public_html/dashboard/register.php` — **غیرفعال** (410 + ریدایرکت).
- `public_html/dashboard/index.php` — انتخابگر شعبه (Box 1)، منوی سرور-محورِ محدودشده (Box 2)،
  بخش «شعب» و «کاربران شعبه»، آمارِ محدود به شعبه، کاربر/خروجِ واقعی.
- **۳۷ صفحه‌ی ادمین** — `require_once _guard.php` در بالا + `dash_require('<feature>')` روی صفحات محتوا.
- `public_html/dashboard/page-view.php` — resolveِ شعبه‌محور (ابتدا `branches.slug` سپس صفحه‌ی مرکزی).
- کوئری‌های محتوا scope شدند: `news-list/save`, `campaign-status/save`, `hero-management/save`, `page-list`, `template-save`.
- `public_html/.htaccess` — قاعده‌ی مسیر داخلیِ شعبه `/{slug}/{sub}`.
- `database/schema.sql` — بخش چندشعبه‌ای برای نصب تازه ضمیمه شد.

---

## ۳) مدل دسترسی

| سطح | پرچم در `dashboard_users` | دامنه |
|-----|---------------------------|-------|
| مدیر مرکزی | `is_super=1` | همه‌ی شعب + بخش «شعب» + dropdown انتخاب شعبه |
| مدیر شعبه | `is_branch_admin=1` | فقط شعبه‌ی خودش + مدیریت کاربران شعبه |
| کاربر شعبه | `role_id` → `dashboard_roles.permissions` | فقط بخش‌های مجاز در شعبه‌ی خودش |

**قانون طلایی (Tenant Isolation):** فیلتر `branch_id` روی **همه‌ی** کوئری‌های سمت سرور اعمال
می‌شود (نه فقط در UI). `dash_active_branch_id()` برای غیرسوپرادمین همیشه شعبه‌ی خودِ کاربر را
برمی‌گرداند؛ هرگونه دستکاریِ پارامتر بی‌اثر است. تغییر شعبه فقط با POST + CSRF و فقط برای سوپرادمین.

---

## ۴) موارد امنیتی پوشش‌داده‌شده
- **هش رمز:** فقط `password_hash()`/`password_verify()` (+ rehash خودکار).
- **SQLi:** فقط prepared statement با PDO.
- **CSRF:** توکن در همه‌ی فرم‌ها و POSTها (`csrf_token()`/`csrf_check()`).
- **XSS:** خروجی با `e()` (= `htmlspecialchars`).
- **Session:** `HttpOnly`+`Secure`(در HTTPS)+`SameSite=Lax`، `session_regenerate_id(true)` پس از ورود،
  timeoutِ بیکاری (۳۰ دقیقه) و مطلق (۸ ساعت).
- **Brute-force:** قفلِ موقت پس از ۵ تلاش ناموفق (۱۵ دقیقه) + ثبت در `login_attempts`.
- **IDOR:** پیش از هر عملیات، مالکیتِ شعبه‌ی رکورد بررسی می‌شود؛ `WHERE branch_id=?` مضاعف.
- **Least privilege:** مدیر شعبه نمی‌تواند سوپرادمین/مدیرِ شعبه بسازد یا منابع مرکزی را لمس کند.
- **آپلود/پوشه:** نام پوشه فقط از slugِ تأییدشده (`[a-z0-9-]`) ساخته می‌شود؛ `uploads/.htaccess` اجرای PHP را خاموش می‌کند.
- **Audit:** ساخت شعبه/کاربر، تغییر وضعیت، ورود/خروج، تغییر شعبه در `audit_log` ثبت می‌شوند.

---

## ۵) کارهای باقی‌مانده / یادداشت‌ها (اختیاری برای آینده)
- چند صفحه‌ی محتوا که در این پاس scope نشدند چون فهرست/ذخیره‌ی اصلی نبودند یا هنوز فقط ظاهری‌اند:
  `feedback.php` (هنوز بک‌اند ندارد)، `courses-*` (در صورت فعال‌سازیِ واقعیِ دوره‌ها باید مثل news/campaign
  با `branch_id` scope شوند)، `personal-resume-*`/`employee_profiles`. الگوی scope در news/campaign آماده است.
- فایل‌های ادمین هنوز از includeهای قدیمیِ دیتابیس (`/../config/database.php`) استفاده می‌کنند؛
  طبق تصمیمِ شما این‌ها بازنویسیِ گسترده نشدند. کدِ جدید همگی از `core/db-config.php` استفاده می‌کنند.
- `core/router.php` (front controllerِ قدیمی) دیگر در مسیر اصلی نیست؛ `page-view.php` متولّیِ resolve است.

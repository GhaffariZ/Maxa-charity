# اتصال به دیتابیس (App Database Config)

اطلاعات اتصال دیتابیس دیگر داخل کد نیست. همه‌ی فایل‌ها از طریق
[`db-config.php`](db-config.php) اطلاعات را از یک منبع امن خارج از گیت می‌خوانند.

## ترتیب خواندن (اولین موردی که پیدا شود)

۱. **متغیرهای محیطی** `DB_HOST` / `DB_NAME` / `DB_USER` / `DB_PASS`
   (مثلاً با `SetEnv` در `.htaccess` یا تنظیمات cPanel)
۲. فایل `maksa-private/db.php` — **یک سطح بالاتر از `public_html`** (پیشنهادی برای production)
۳. فایل `core/db.local.php` — فقط برای توسعه‌ی محلی (gitignore شده)

هر کدام باید یک آرایه `return` کنند:

```php
<?php
return [
    'host'    => 'localhost',
    'name'    => 'erfantey_macsacharity',
    'user'    => 'erfantey_fantasticfour',
    'pass'    => 'پسورد-واقعی',
    'charset' => 'utf8mb4',
];
```

## راه‌اندازی روی سرور (production)

فایل را **بیرون از وب‌روت** بساز تا از طریق مرورگر قابل دسترسی نباشد:

```
/home/erfantey/
├── maksa-private/
│   └── db.php          ← اینجا (خارج از public_html)
└── public_html/
```

```bash
mkdir -p ~/maksa-private
# محتوای آرایه‌ی بالا را داخلش بگذار:
nano ~/maksa-private/db.php
```

> چون `.cpanel.yml` فقط `public_html/` را کپی می‌کند، این فایل با دیپلوی
> بازنویسی **نمی‌شود** و یک‌بار ساختنش کافی است.

## راه‌اندازی محلی (development)

```bash
cp core/db.local.php.example core/db.local.php
# مقادیر لوکال خودت را پر کن — این فایل gitignore شده است
```

## ⚠️ تعویض پسورد

پسورد قدیمی قبلاً داخل گیت لو رفته بود. **حتماً از cPanel → MySQL Databases
پسورد کاربر دیتابیس را عوض کن** و مقدار جدید را فقط در `maksa-private/db.php`
(و `database/db.conf` برای اسکریپت دیپلوی) بگذار.

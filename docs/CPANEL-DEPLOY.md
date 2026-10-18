# دیپلوی cPanel بدون Terminal

Push به GitHub به‌تنهایی فایل‌های سایت را روی هاست عوض نمی‌کند. ابتدا باید در
cPanel از **Update from Remote** و سپس **Deploy HEAD Commit** استفاده شود.

## معنی خطای The system cannot deploy

برای دیپلوی، `.cpanel.yml` معتبر و کامیت‌شده در ریشهٔ repository، یک برنچ و یک
working tree تمیز لازم است. متن خطا مشخص نمی‌کند کدام شرط برقرار نیست؛ سالم‌بودن
نسخهٔ GitHub، وضعیت فایل‌ها و برنچ روی هاست را اثبات نمی‌کند.

## ۱. آماده‌سازی

- در Backup یا Backup Wizard سی‌پنل از فایل‌ها و دیتابیس بک‌آپ بگیر.
- در **Domains → Document Root** مسیر اجرای دامنه را ببین. تنظیم فعلی پروژه
  `${HOME}/public_html` است؛ یعنی `public_html` حساب فعلی هاست.
- اگر Document Root متفاوت است، مقدار `DEPLOYPATH` در `.cpanel.yml` باید در
  GitHub یا لوکال به همان مسیر تغییر کند و commit شود. ویرایش فایل داخل کلون
  هاست آن را به تغییر ثبت‌نشده تبدیل می‌کند.
- **Repository Path** باید جدا و بیرون از Document Root باشد؛ مثلاً
  `/home/USERNAME/repositories/Maxa-charity`. به‌جای `USERNAME` نام حساب هاست را بگذار.
  کل پروژه شامل `database` و `.git` را داخل فضای عمومی سایت قرار نده.

## ۲. ریپازیتوری فعلی را به‌روزرسانی کن

۱. وارد **Files → Git Version Control → Manage** شو.
۲. در **Basic Information → Checked-Out Branch**، برنچ `main` را انتخاب کن
   و **Update** را بزن. Remote URL باید همان ریپازیتوری GitHub پروژه باشد.
۳. در **Pull or Deploy**، **Update from Remote** را بزن و منتظر پایانش بمان.
۴. مقدار **HEAD Commit** را با آخرین کامیت `main` در GitHub مقایسه کن.
۵. لینک **Repository Path** را باز کن تا وارد File Manager شوی. در **Settings**
   گزینهٔ **Show Hidden Files (dotfiles)** را روشن کن. `.cpanel.yml` باید کنار
   `README.md` و پوشه‌های `public_html` و `database` باشد، نه داخل `public_html`.
۶. اگر خطا رفع شد، **Deploy HEAD Commit** را بزن. در پایان **Last Deployed SHA**
   باید با HEAD Commit برابر باشد. صفحهٔ اصلی و ورود پنل را بررسی کن.

## ۳. اگر هنوز خطا باقی است: کلون تازه، بدون حذف نسخهٔ قبلی

بدون Terminal نمی‌توان همهٔ تغییرات ثبت‌نشده یا اختلاف تاریخچهٔ کلون هاست را از
File Manager تشخیص داد. برای رفع خطا فایل‌ها یا پوشهٔ `.git` را حذف نکن.

۱. ریپازیتوری قبلی و فایل‌های سایت را نگه دار.
۲. در **Git Version Control → Create**، **Clone a Repository** را فعال کن.
۳. **همان Clone URL و روش دسترسی ریپازیتوری فعلی** را استفاده کن. آدرس HTTPS پروژه
   `https://github.com/GhaffariZ/Maxa-charity.git` است. برای پروژهٔ خصوصی که با SSH
   وصل شده، همان SSH URL را نگه دار؛ تغییر به HTTPS مشکل مجوز را حل نمی‌کند.
۴. یک Repository Path تازه و خالی مثل
   `/home/USERNAME/repositories/Maxa-charity-clean` وارد کن؛ داخل `public_html` نباشد.
۵. نام دلخواه بده، **Create** را بزن و منتظر پایان clone بمان.
۶. مراحل بخش ۲ را برای کلون تازه روی `main` انجام بده. فایل‌های این کلون را در
   File Manager ویرایش نکن و uploads یا تنظیمات خصوصی سایت را به آن کپی نکن.

اگر کلون تازه هم همان خطا را داد، این متن را همراه Repository Path به پشتیبانی بفرست:

> برنچ main از GitHub دریافت شده و .cpanel.yml در ریشه کامیت شده، اما پیام
> The system cannot deploy می‌بینم. لطفاً پیش‌نیازهای deployment، وضعیت working tree،
> تغییرات محلی و مجوزهای اجرای دیپلوی را بررسی کنید. قبل از هر اصلاح از تغییرات
> محلی بک‌آپ بگیرید؛ فایل‌های سایت، uploads، تنظیمات خصوصی و دیتابیس حذف یا
> بازنشانی نشوند.

اگر clone خطای authentication داد، پشتیبانی باید اتصال/Deploy Key پروژهٔ خصوصی را
بررسی کند. رمز، توکن یا کلید خصوصی را در متن خطا یا اسکرین‌شات منتشر نکن.

## ۴. دیتابیس و تنظیمات خصوصی

دیپلوی فقط محتوای `public_html` را کپی می‌کند. فایل‌های اضافهٔ مقصد حذف نمی‌شوند،
اما فایل‌های هم‌نام جایگزین می‌شوند؛ به همین دلیل بک‌آپ لازم است.
`database/import.sh` دیگر خودکار اجرا نمی‌شود: dump شامل حذف جدول و دادهٔ نمونه است.

- **سایت فعال:** دیتابیس را دوباره import نکن. تنظیمات اتصال و فایل‌های خصوصی
  فعلی را حفظ کن؛ تغییرات ساختار دیتابیس جداگانه بررسی می‌شوند.
- **نصب جدید:** در MySQL Databases دیتابیس و کاربر بساز، کاربر را به همان دیتابیس
  با دسترسی لازم متصل کن و فقط روی دیتابیس خالی در **phpMyAdmin → Import** فایل
  `database/schema.sql` را وارد کن. dump را داخل Document Root قرار نده.
- در نصب جدید، برای صفحات PHP پوشهٔ `maksa-private` را کنار Document Root و بیرون
  از فضای عمومی بساز. فایل `db.php` را با اطلاعات واقعی MySQL داخل آن قرار بده:

```php
<?php
return [
    'host' => 'localhost',
    'name' => 'CPANEL_DATABASE_NAME',
    'user' => 'CPANEL_DATABASE_USER',
    'pass' => 'DATABASE_PASSWORD',
    'charset' => 'utf8mb4',
];
```

برای Document Root پیش‌فرض، محل فایل `/home/USERNAME/maksa-private/db.php` است.
API نیز تنظیمات خصوصی `.env.example` را در `maksa-private/.env` نیاز دارد؛ تنظیمات
فعال موجود را جایگزین نکن. فایل‌های خصوصی نباید به GitHub اضافه شوند.

## ۵. ارسال ایمیل ثبت‌نام و تأیید حساب

ارسال ایمیل پنل خیرین از طریق `public_html/api` و PHPMailer انجام می‌شود. فایل‌های
کتابخانه در `public_html/api/vendor/PHPMailer/src` باید روی هاست موجود باشند؛ نبودن
کتابخانه دیگر با PHP `mail()` پنهان نمی‌شود و به‌عنوان خطا ثبت می‌شود.

تنظیمات واقعی را در فایل خصوصی فعلی حفظ و بررسی کن:

- `MAIL_HOST` نام سرور SMTP اعلام‌شده توسط هاست است.
- برای پورت `465` از `MAIL_ENCRYPTION=smtps` و برای پورت `587` از
  `MAIL_ENCRYPTION=starttls` استفاده کن. مقدار قدیمی `tls` با هر دو پورت سازگار است.
- `MAIL_USERNAME`، `MAIL_PASSWORD` و `MAIL_FROM_ADDRESS` باید متعلق به حساب ایمیل
  واقعی و مجاز برای ارسال باشند. رمز دارای فاصله یا `#` را داخل کوتیشن بگذار.
- `APP_URL` نشانی کامل پنل است؛ برای این دامنه:
  `https://mymacsa.ir/benefactor-dashboard`. لینک‌های تأیید و بازیابی از این مقدار ساخته می‌شوند.
- `LOG_PATH` باید مسیر قابل‌نوشتن بیرون از فضای عمومی باشد، مانند
  `/home/USERNAME/maksa-private/logs/app.log`.

متغیرهای محیطی هاست بر فایل‌ها اولویت دارند. مسیر `MAKSA_ENV_PATH` در صورت تنظیم،
پیش از مسیرهای معمول بررسی می‌شود؛ سپس فایل‌های `maksa-private/.env` و در آخر
فایل‌های `.env` توسعه خوانده می‌شوند. فایل کم‌اولویت نباید تنظیمات خصوصی را عوض کند.

اگر ایمیل نرسید، در فرم ثبت‌نام یا پس از خطای «ایمیل تأیید نشده» از **ارسال مجدد**
استفاده کن. فاصلهٔ درخواست‌های یک حساب حداقل ۶۰ ثانیه است. ثبت‌نام دوباره، رمز یا
پروفایل حساب موجود را تغییر نمی‌دهد و می‌تواند ارسال تأیید حساب را تکرار کند.

برای تشخیص مشکل، زمان درخواست را با پیام‌های `Mailer (PHPMailer) failed` یا
`Verification email` در `app.log` تطبیق بده. خطا شامل میزبان، پورت و روش رمزنگاری
است؛ SMTP debug عمومی را فعال نکن و رمز یا لینک تأیید را منتشر نکن. پیام
`SMTP send OK` فقط پذیرش توسط سرور ایمیل را نشان می‌دهد؛ اگر این پیام ثبت شده ولی
ایمیل نرسیده، اسپم، محدودیت ارسال حساب و تنظیمات SPF/DKIM دامنه باید توسط هاست بررسی شوند.

## منابع رسمی

- [الزامات و روش Deployment](https://docs.cpanel.net/knowledge-base/web-services/guide-to-git-deployment/)
- [مدیریت Repository و انتخاب برنچ](https://docs.cpanel.net/cpanel/files/git-version-control/)

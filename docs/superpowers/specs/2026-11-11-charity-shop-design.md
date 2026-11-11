# سند طراحی معماری: فروشگاه اینترنتی محصولات فیزیکی خیریه مکسا (Charity Shop & Unified Order Fulfillment)

- **تاریخ سند:** ۲۰۲۶-۱۱-۱۱ (1405/08/21)
- **وضعیت:** تایید شده توسط کارفرما / آماده تدوین برنامه اجرایی
- **شاخه توسعه:** `feature/charity-shop` (عدم تغییر مستقیم روی شاخه اصلی `main`)

---

## ۱. هدف و چشم‌انداز (Executive Summary)

این سند معماری و جزئیات فنی اضافه شدن **فروشگاه اینترنتی محصولات فیزیکی (Mini Charity Shop)** به پلتفرم خیریه مکسا و همچنین **یکپارچه‌سازی فرآیند مدیریت ارسال (Fulfillment) و پیگیری مرسولات برای سفارشات فروشگاه و سفارشات استند/تاج گل** را تشریح می‌کند.

هدف اصلی:
1. ایجاد جریان درآمد پایدار و ترویج محصولات فرهنگی، صنایع دستی و اقلام نمادین خیریه مکسا.
2. ارائه تجربه خرید سریع، چندمحصولی و دلنشین برای نیکوکاران با حفظ رویکرد چابک و بدون پیچیدگی‌های سنگین لجستیک (ارسال به شیوه پس‌کرایه / پرداخت کرایه در محل).
3. ارتقاء پنل کاربری نیکوکاران جهت نمایش و پیگیری وضعیت ارسال مرسولات با لینک مستقیم به سامانه رهگیری پست.
4. ارتقاء پنل مدیریت برای مدیریت کاتالوگ محصولات، کنترل موجودی انبار و ثبت کد رهگیری پستی مرسولات برای کلیه سفارش‌ها (فروشگاه و استند).

---

## ۲. محدوده و مرزهای پروژه (Scope)

### اقلام درون محدوده (In-Scope):
- **بانک اطلاعاتی:** ایجاد جداول `products`، `product_categories`، `order_items` و افزودن ستون‌های سازگار با گذشته به جدول `orders` همراه با اسکریپت‌های خودترمیم (Self-healing).
- **بخش عمومی سایت:**
  - صفحه اختصاصی فروشگاه (`/shop.php`) با بنر خیریه، فیلتر دسته‌بندی‌ها، کارت محصولات، مودال جزئیات کالا.
  - سبد خرید کشویی شناور (Floating Drawer Cart) با نگهداری در `localStorage` و دکمه کنترل تعداد اقلام.
  - صفحه تسویه‌حساب (`/shop-checkout.php`) با الزام به ورود نیکوکار، فرم آدرس پستی (پس‌کرایه) و جمع فاکتور.
  - بازگشت از درگاه پرداخت و صدور رسید نهایی (`/shop-payment-callback.php`) با کسر اتمیک موجودی انبار.
  - کامپوننت ویترین محصولات در صفحه اصلی سایت (`dashboard/components/shop-showcase.php`).
  - افزودن لینک «فروشگاه» به منوی هدر سایت.
- **پنل کاربری نیکوکاران (`benefactor userpanel` - React/Vite/TS):**
  - ارتقاء `Orders.tsx` با دو تب: «سفارش‌های فروشگاه» و «استند و تاج گل».
  - نمایش وضعیت پرداخت، وضعیت ارسال (در انتظار، در حال آماده‌سازی، ارسال شده، تحویل داده شده)، اقلام سفارش، آدرس پستی، شرکت حمل‌کننده و کد رهگیری پستی به همراه لینک رهگیری.
  - ارتقاء سرویس‌ها و تایپ‌های API در کلاینت (`client.ts`).
- **پنل ادمین مکسا (`public_html/dashboard`):**
  - صفحه مدیریت محصولات (`products.php`) شامل افزودن، ویرایش، حذف، آپلود عکس و تغییر موجودی.
  - ارتقاء صفحه سفارشات (`orders.php`) با فیلتر نوع سفارش، فیلتر وضعیت ارسال و مودال مدیریت ارسال (تغییر وضعیت، انتخاب شرکت پستی و ثبت کد رهگیری مرسوله) هم برای سفارشات فروشگاه و هم برای سفارشات استند.
- **REST API بک‌اند (`public_html/api`):**
  - به‌روزرسانی اندپوینت‌های `/orders` و `/user/orders` جهت پشتیبانی از انواع سفارشات، لیست اقلام و جزئیات ارسال.

### اقلام خارج از محدوده (Out-of-Scope / YAGNI):
- سیستم‌های انبارداری چندگانه و انبارهای توزیع‌شده.
- محصولات با ویژگی‌های متغیر پیچیده (رنگ/سایز چندگانه در یک کالا).
- محاسبه خودکار هزینه‌های پست شهری در فاکتور (ارسال طبق توافق به شیوه پس‌کرایه در نظر گرفته شده است).

---

## ۳. معماری پایگاه داده و مهاجرت خودترمیم (Database Schema)

### ۳.۱. جدول دسته‌بندی محصولات (`product_categories`)
```sql
CREATE TABLE IF NOT EXISTS `product_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### ۳.۲. جدول محصولات فیزیکی (`products`)
```sql
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `stock` INT NOT NULL DEFAULT 0,
    `image_url` VARCHAR(500) NULL,
    `gallery_json` TEXT NULL,
    `status` ENUM('active', 'draft', 'out_of_stock') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### ۳.۳. ارتقاء جدول سفارشات (`orders`)
ستون‌های تکمیلی که با الگوی امن و خودترمیم اضافه می‌شوند:
```sql
ALTER TABLE `orders` 
    ADD COLUMN `order_type` ENUM('stand', 'product') NOT NULL DEFAULT 'stand' AFTER `user_id`,
    ADD COLUMN `payment_status` ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending' AFTER `order_type`,
    ADD COLUMN `fulfillment_status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending' AFTER `payment_status`,
    ADD COLUMN `carrier` VARCHAR(100) NULL AFTER `fulfillment_status`,
    ADD COLUMN `postal_tracking_code` VARCHAR(100) NULL AFTER `carrier`,
    ADD COLUMN `recipient_name` VARCHAR(150) NULL AFTER `postal_tracking_code`,
    ADD COLUMN `recipient_phone` VARCHAR(30) NULL AFTER `recipient_name`,
    ADD COLUMN `postal_code` VARCHAR(20) NULL AFTER `city`,
    ADD COLUMN `customer_note` TEXT NULL AFTER `address`,
    ADD COLUMN `paid_at` DATETIME NULL AFTER `customer_note`,
    ADD COLUMN `shipped_at` DATETIME NULL AFTER `paid_at`;
```

### ۳.۴. جدول اقلام سفارش (`order_items`)
```sql
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NULL,
    `product_title` VARCHAR(255) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## ۴. جریان فرانت‌اند و تجربه کاربری (User Experience & Storefront)

### ۴.۱. صفحه فروشگاه (`/shop.php`)
- بارگذاری ماژولار از طریق `render_dashboard_component` هماهنگ با ساختار کلیدی سایت مکسا.
- بخش Hero با شعار خرید خیرخواهانه و آگاهی‌بخشی در خصوص مراقبت‌های تسکینی سرطان.
- سوییچر تب‌های دسته‌بندی و فیلتر بر اساس نام یا بازه قیمت به همراه شمارنده موجودی.
- کارت محصول مینیمال و استاندارد با نمایش وضعیت موجودی و دکمه تک‌کلیک «افزودن به سبد».
- مودال جزئیات محصول با گالری تصویر، متن کامل داستان محصول و انتخاب تعداد.

### ۴.۲. سبد خرید کشویی (Drawer Cart)
- کامپوننت شناور سمت کلاینت با کلید `maxa_shop_cart` در `localStorage`.
- نمایش زنده اقلام، مجموع قیمت، اعلان شفاف هزینه ارسال پس‌کرایه و دکمه هدایت به تسویه‌حساب.

### ۴.۳. صفحه تسویه‌حساب (`/shop-checkout.php`)
- **احراز هویت اجباری:** بررسی توکن لاگین یا کوکی کاربر. در صورت عدم ورود، کادر لاگین باز می‌شود تا نیکوکار با موبایل وارد شود بدون اینکه سبد خریدش خالی شود.
- **فرم آدرس گیرنده:** نام، موبایل، استان، شهر، کد پستی و آدرس دقیق.
- **ثبت سفارش اولیه و اتصال به درگاه:** ثبت سفارش با `payment_status = 'pending'` و اتصال به درگاه بانکی.

### ۴.۴. اعتبارسنجی پرداخت و صدور رسید (`/shop-payment-callback.php`)
- استعلام وضعیت تراکنش از درگاه؛ در صورت موفقیت:
  - تنظیم `payment_status = 'paid'` و `paid_at = NOW()`.
  - اجرای کوئری کسر اتمیک موجودی انبار:
    `UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?`
  - در صورت صفر شدن موجودی، تغییر وضعیت به `out_of_stock`.
  - پاک کردن سبد خرید محلی کاربر و نمایش شماره فاکتور، کد پیگیری و دکمه ارجاع به پنل کاربری.

---

## ۵. پنل کاربری نیکوکاران (`benefactor userpanel`)

### ۵.۱. ارتقاء رابط کاربری سفارشات (`Orders.tsx`)
- افزودن هدر تب‌دار:
  - تب اول: **«محصولات فروشگاه»**
  - تب دوم: **«استند و تاج گل»**
- کارت سفارش فروشگاه شامل:
  - کد سفارش و تاریخ ثبت.
  - برچسب وضعیت پرداخت (`موفق`).
  - برچسب وضعیت ارسال:
    - `در انتظار پردازش` (نارنجی)
    - `در حال آماده‌سازی و بسته‌بندی` (آبی)
    - `ارسال شده با پست/تیپاکس` (بنفش)
    - `تحویل داده شده` (سبز)
  - ردیف اقلام خریداری شده با عکس کالا، نام، تعداد و قیمت.
  - آدرس مقصد و مشخصات تحویل‌گیرنده.
  - کادر رهگیری مرسوله پستی: نمایش بارنامه، دکمه کپی کد و لینک کلیک‌خور به سامانه پیگیری پست ایران (`https://tracking.post.ir/?id=...`).

### ۵.۲. به‌روزرسانی API نیکوکاران (`client.ts` و `OrderController.php`)
- گسترش مدل `OrderDto` با فیلدهای `order_type`, `fulfillment_status`, `carrier`, `postal_tracking_code`, `items`.
- اتصال اندپوینت `/user/orders` به جداول جدید جهت استخراج هم‌زمان اقلام هر دو نوع سفارش.

---

## ۶. پنل مدیریت مکسا (`public_html/dashboard`)

### ۶.۱. صفحه مدیریت محصولات (`products.php`)
- جدول مدیریت اقلام با قابلیت مرتب‌سازی، فیلتر دسته‌بندی و وضعیت موجودی.
- فرم مودال ایجاد و ویرایش محصول با فیلدهای:
  - عنوان، شناسه متنی (slug)، دسته‌بندی، قیمت، تعداد موجودی، توضیحات و وضعیت.
  - آپلود تصویر شاخص در مسیر امن `public_html/uploads/products/` با اعتبارسنجی فرمت‌های مجاز (JPG, PNG, WebP).
- اکشن‌های سریع: فعال/غیرفعال کردن و اصلاح سریع تعداد موجودی.

### ۶.۲. ارتقاء کارتابل سفارشات (`orders.php`)
- فیلتر تب‌دار: «همه سفارشات»، «فروشگاه»، «استند و تاج گل».
- فیلتر وضعیت ارسال: همه، در انتظار، در حال آماده‌سازی، ارسال شده، تحویل شده، لغو شده.
- دکمه اکشن «مدیریت وضعیت و ارسال»:
  - باز شدن کشو یا مودال با نمایش اقلام سفارش و آدرس کامل خریدار.
  - انتخاب وضعیت جدید ارسال (`fulfillment_status`).
  - انتخاب شرکت حامل مرسوله (`carrier`: پست پیشتاز، تیپاکس، پیک موتوری، تحویل حضوری).
  - درج کد رهگیری پستی مرسوله (`postal_tracking_code`).
  - ذخیره امن با توکن CSRF و ثبت تاریخ ارسال (`shipped_at`).
- اعمال همین سیستم ثبت بارنامه برای سفارشات استند نیز به صورت کامل و یکپارچه.
- اضافه شدن لینک‌های منو در ناوبری سایدبار پنل ادمین.

---

## ۷. الزامات امنیتی، یکپارچگی مالی و مدیریت خطاها

1. **قیمت‌گذاری سمت سرور (Server-Authoritative Pricing):** کلاینت تنها شناسه کالا و تعداد را به سرور می‌فرستد. قیمت‌ها بدون استثنا از پایگاه داده واکشی می‌شوند تا هرگونه دستکاری قیمت ناممکن گردد.
2. **تراکنش‌های پایگاه داده (ACID Transactions):** تمامی مراحل ایجاد سفارش و ثبت اقلام در بلاک `PDO::beginTransaction()` و `commit()` اجرا می‌شوند.
3. **کسر قطعی موجودی فقط پس از تایید تراکنش (Idempotent Stock Decrement):** کسر موجودی صرفاً در متد تأیید تراکنش بانکی صورت می‌پذیرد تا کالای کاربری که پرداخت نکرده کسر نگردد.
4. **کنترل دسترسی و محافظت از داده‌ها:**
   - محافظت از تمام صفحات ادمین با `dash_require()` و بررسی احراز هویت توکن CSRF.
   - اعمال بررسی مالکیت سفارش در پنل نیکوکار (`WHERE o.user_id = :user_id`) جهت جلوگیری از دسترسی غیرمجاز کاربر به سفارشات دیگران.
5. **امنیت آپلود فایل:** کنترل MIME-Type، محدودسازی اندازه فایل به حداکثر ۲ مگابایت، و پاکسازی نام فایل جهت جلوگیری از Path Traversal یا بارگذاری اسکریپت‌های مخرب.

---

## ۸. استراتژی تست و تضمین کیفیت (Testing & Verification Plan)

1. **تست پایگاه داده و میگریشن‌ها:**
   - تست اسکریپت خودترمیم ساخت جداول `products`، `product_categories`، `order_items` و فیلدهای جدول `orders` بدون ایجاد خطا در صورت اجرای چندباره.
2. **تست تایپ‌اسکریپت و بیلد پنل نیکوکاران:**
   - اجرای `npm run build` در پوشه `benefactor userpanel` و بررسی عاری بودن کامپوننت `Orders.tsx` از خطاهای تایپ‌اسکریپت یا JSX.
3. **تست‌های واحد و یکپارچگی بک‌اند:**
   - تست کنترلر سفارشات و فرمول محاسبه مبالغ فاکتور.
   - تست پاسخ اندپوینت `/user/orders` با انواع داده‌های فروشگاه و استند.
4. **سناریوی تست چرخه کامل (End-to-End Walkthrough):**
   - ایجاد دسته‌بندی و محصول تستی در پنل ادمین.
   - ورود به `/shop.php`، افزودن به سبد، بررسی Drawer Cart.
   - رفتن به تسویه‌حساب، ورود با حساب کاربری، ثبت آدرس و نهایی‌سازی.
   - شبیه‌سازی پرداخت موفق درگاه، اعتبارسنجی کسر موجودی انبار.
   - مشاهده سفارش در پنل نیکوکار (`Orders.tsx`).
   - ورود به پنل ادمین، ثبت کد رهگیری پستی و تغییر وضعیت به «ارسال شده».
   - مشاهده کد رهگیری و لینک پست در پنل نیکوکار.

---

## ۹. تعهد و استراتژی شاخه‌های گیت (Branching Strategy)

- **شاخه جاری:** `feature/charity-shop`
- طبق دستور صریح کاربر، تمامی عملیات کدنویسی، ایجاد فایل‌ها، تست‌ها و اعتبارسنجی‌ها منحصراً روی این شاخه انجام خواهد شد و شاخه `main` تا زمان تایید نهایی و تکمیل تمامی تست‌ها دست‌نخورده باقی می‌ماند.

<?php
/**
 * ============================================================================
 * موتور جامع بهینه‌سازی و تبدیل خودکار تصاویر به WebP — خیریه مکسا
 * ============================================================================
 * این ماژول تمامی تصاویر آپلودشده در بخش‌های مختلف سایت را بهینه‌سازی کرده و
 * به صورت خودکار به فرمت مدرن WebP با فشرده‌سازی استاندارد گوگل تبدیل می‌کند.
 *
 * ویژگی‌ها:
 *   • کاهش حجم تصاویر بین ۷۰ الی ۸۵ درصد با کیفیت بصری دست‌نخورده
 *   • چرخش خودکار بر اساس اطلاعات EXIF (جلوگیری از کج شدن عکس‌های دوربین و گوشی)
 *   • حفظ شفافیت کانال آلفا برای تصاویر ترنسپرنت (PNG / WebP)
 *   • تغییر سایز متناسب برای تصاویر با وضوح فوق‌العاده بالا (حداکثر ۱۹۲۰ پیکسل)
 *   • پشتیبانی از انواع پسوندها: JPG, JPEG, PNG, WEBP, GIF, BMP
 *   • عبور امن فایل‌های وکتور SVG بدون دستکاری
 *   • فال‌بک امن در صورت عدم دسترسی به GD
 * ============================================================================
 */
declare(strict_types=1);

if (!defined('MAXA_IMAGE_OPTIMIZER_LOADED')) {
    define('MAXA_IMAGE_OPTIMIZER_LOADED', 1);

    /**
     * بررسی پشتیبانی سرور از کتابخانه GD و فرمت WebP
     */
    function maxa_has_webp_support(): bool {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    /**
     * بهینه‌سازی و تبدیل یک فایل تصویر موجود به فرمت WebP
     *
     * @param string $sourcePath مسیر فیزیکی فایل مبدأ (روی دیسک یا tmp_name)
     * @param string|null $destPath مسیر فیزیکی فایل مقصد (در صورت خالی بودن، فایلی با پسوند webp در همان مسیر ساخته می‌شود)
     * @param int $quality کیفیت خروجی WebP (بین ۱ تا ۱۰۰ - پیش‌فرض ۸۲)
     * @param int $maxWidth حداکثر عرض مجاز (پیش‌فرض ۱۹۲۰ پیکسل)
     * @param int $maxHeight حداکثر ارتفاع مجاز (پیش‌فرض ۱۹۲۰ پیکسل)
     * @return string|null مسیر فایل ذخیره‌شده یا null در صورت خطا
     */
    function maxa_optimize_image_to_webp(
        string $sourcePath,
        ?string $destPath = null,
        int $quality = 82,
        int $maxWidth = 1920,
        int $maxHeight = 1920
    ): ?string {
        if (!is_file($sourcePath) || !is_readable($sourcePath)) {
            return null;
        }

        // اگر فایل SVG باشد، وکتور است و نیازی به تبدیل ندارد
        $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
        $mime = $finfo ? finfo_file($finfo, $sourcePath) : '';
        if ($finfo) { finfo_close($finfo); }

        if ($mime === 'image/svg+xml' || preg_match('/\.svg$/i', $sourcePath)) {
            if ($destPath && $destPath !== $sourcePath) {
                @copy($sourcePath, $destPath);
                return $destPath;
            }
            return $sourcePath;
        }

        // در صورت عدم پشتیبانی از WebP، فایل اصلی حفظ می‌شود
        if (!maxa_has_webp_support()) {
            if ($destPath && $destPath !== $sourcePath) {
                @copy($sourcePath, $destPath);
                return $destPath;
            }
            return $sourcePath;
        }

        $rawData = @file_get_contents($sourcePath);
        if ($rawData === false || strlen($rawData) === 0) {
            return null;
        }

        $img = @imagecreatefromstring($rawData);
        if ($img === false) {
            return null;
        }

        // تصحیح چرخش بر اساس متادیتای EXIF (ویژه عکس‌های گوشی‌های همراه)
        if (function_exists('exif_read_data')) {
            try {
                $exif = @exif_read_data($sourcePath);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $rotated = @imagerotate($img, 180, 0);
                            if ($rotated) { imagedestroy($img); $img = $rotated; }
                            break;
                        case 6:
                            $rotated = @imagerotate($img, -90, 0);
                            if ($rotated) { imagedestroy($img); $img = $rotated; }
                            break;
                        case 8:
                            $rotated = @imagerotate($img, 90, 0);
                            if ($rotated) { imagedestroy($img); $img = $rotated; }
                            break;
                    }
                }
            } catch (Throwable $e) {
                // خطای احتمالی خواندن اگزیف نادیده گرفته می‌شود
            }
        }

        $origW = imagesx($img);
        $origH = imagesy($img);

        // محاسبه ابعاد جدید در صورت تجاوز از سقف مجاز
        $newW = $origW;
        $newH = $origH;
        if ($origW > $maxWidth || $origH > $maxHeight) {
            $ratio = min($maxWidth / $origW, $maxHeight / $origH);
            $newW = (int)round($origW * $ratio);
            $newH = (int)round($origH * $ratio);
        }

        // ساخت تصویر نهایی با حفظ شفافیت کامل
        $canvas = imagecreatetruecolor($newW, $newH);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        // کپی و ریسایز با کیفیت بالا
        imagecopyresampled($canvas, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($img);

        // تعیین مسیر مقصد
        if (!$destPath) {
            $destPath = preg_replace('/\.[^.]+$/', '.webp', $sourcePath);
        }

        $dir = dirname($destPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $saved = @imagewebp($canvas, $destPath, max(1, min(100, $quality)));
        imagedestroy($canvas);

        if ($saved && is_file($destPath) && filesize($destPath) > 0) {
            @chmod($destPath, 0664);
            return $destPath;
        }

        return null;
    }

    /**
     * پردازش جامع و ذخیره خودکار فایل آپلودشده از آرایه $_FILES
     * این تابع ورودی فایل را می‌گیرد، آن را فشرده و به WebP تبدیل می‌کند و در مسیر مقصد ذخیره می‌نماید.
     *
     * @param array $file آرایه فایل از $_FILES['...']
     * @param string $subDir زیرپوشه مورد نظر درون /uploads/ (مثلاً 'gallery', 'news', 'hero', 'stories')
     * @param string $prefix پیشوند نام فایل (مثلاً 'photo_', 'cover_', 'hero_')
     * @param int $quality کیفیت WebP (پیش‌فرض ۸۲)
     * @param int $maxWidth حداکثر عرض (پیش‌فرض ۱۹۲۰)
     * @return string آدرس وب فایل ذخیره‌شده (مثلاً '/uploads/gallery/photo_20261013_abc123.webp')
     * @throws Exception در صورت بروز خطا در اعتبارسنجی
     */
    function maxa_upload_and_optimize(
        array $file,
        string $subDir = 'general',
        string $prefix = '',
        int $quality = 82,
        int $maxWidth = 1920
    ): string {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $code = $file['error'] ?? UPLOAD_ERR_NO_FILE;
            switch ($code) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $max = ini_get('upload_max_filesize');
                    throw new Exception("حجم فایل بیشتر از سقف مجاز سرور است (حداکثر $max).");
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception("آپلود ناقص ماند، لطفاً مجدداً امتحان کنید.");
                case UPLOAD_ERR_NO_FILE:
                    throw new Exception("هیچ فایلی برای آپلود انتخاب نشده است.");
                default:
                    throw new Exception("خطا در ارسال فایل (کد: $code).");
            }
        }

        $origName = (string)($file['name'] ?? 'image');
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        // بررسی پسوندهای مجاز اسناد و تصاویر
        $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'];
        $docExts   = ['pdf', 'epub', 'mobi', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar', '7z', 'mp4', 'mp3', 'svg'];
        $allAllowed = array_merge($imageExts, $docExts);

        if (!in_array($ext, $allAllowed, true)) {
            throw new Exception("فرمت فایل نامعتبر است ($ext).");
        }

        $cleanSubDir = trim(str_replace(['..', '\\'], ['', '/'], $subDir), '/');
        $uploadBase  = dirname(__DIR__) . '/uploads/' . $cleanSubDir;
        if (!is_dir($uploadBase)) {
            @mkdir($uploadBase, 0775, true);
        }

        $safePrefix = preg_replace('/[^a-zA-Z0-9_-]/', '', $prefix);
        if ($safePrefix !== '' && !str_ends_with($safePrefix, '_')) {
            $safePrefix .= '_';
        }

        $randomHash = date('Ymd_His') . '_' . bin2hex(random_bytes(4));

        // اگر فایل تصویر باشد، مستقیماً به عنوان WebP بهینه‌سازی و ذخیره می‌شود
        if (in_array($ext, $imageExts, true)) {
            $destFileName = $safePrefix . $randomHash . '.webp';
            $destFilePath = $uploadBase . '/' . $destFileName;

            $convertedPath = maxa_optimize_image_to_webp($file['tmp_name'], $destFilePath, $quality, $maxWidth);

            if ($convertedPath && is_file($convertedPath)) {
                return '/uploads/' . $cleanSubDir . '/' . $destFileName;
            }

            // در صورت بروز هرگونه مشکل در GD، به عنوان فایل معمولی منتقل می‌شود
            $destFileName = $safePrefix . $randomHash . '.' . $ext;
            $destFilePath = $uploadBase . '/' . $destFileName;
            if (!move_uploaded_file($file['tmp_name'], $destFilePath)) {
                throw new Exception("خطا در ذخیره فایل در مسیر uploads.");
            }
            return '/uploads/' . $cleanSubDir . '/' . $destFileName;
        }

        // اگر فایل غیرتصویر باشد (مثلاً PDF یا وکتور SVG)
        $destFileName = $safePrefix . $randomHash . '.' . $ext;
        $destFilePath = $uploadBase . '/' . $destFileName;
        if (!move_uploaded_file($file['tmp_name'], $destFilePath)) {
            throw new Exception("خطا در ذخیره فایل در مسیر uploads.");
        }

        return '/uploads/' . $cleanSubDir . '/' . $destFileName;
    }

    /**
     * بررسی و بازگرداندن نسخه WebP از آدرس یک تصویر (در صورت موجود بودن)
     * برای بهبود سئو و سرعت لود قالب‌ها
     */
    function maxa_img_webp(string $url): string {
        $clean = trim($url);
        if ($clean === '' || str_ends_with(strtolower($clean), '.webp') || str_ends_with(strtolower($clean), '.svg')) {
            return $clean;
        }

        // اگر آدرس یک فایل محلی است
        if (str_starts_with($clean, '/uploads/')) {
            $webpPath = preg_replace('/\.(jpe?g|png|bmp)$/i', '.webp', $clean);
            $fullPath = dirname(__DIR__) . $webpPath;
            if (is_file($fullPath)) {
                return $webpPath;
            }
        }

        return $clean;
    }
}

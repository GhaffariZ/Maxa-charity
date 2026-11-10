<?php
/**
 * ============================================================================
 * ابزار تبدیل دسته‌ای تصاویر موجود در پوشه uploads به WebP — خیریه مکسا
 * ============================================================================
 * با اجرای این اسکریپت، تمام تصاویر موجود در پوشه uploads که هنوز نسخه WebP
 * ندارند، به طور خودکار به WebP بهینه‌سازی شده تبدیل می‌شوند تا سرعت لود و
 * سئو سایت به حداکثر برسد.
 * ============================================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/core/image-optimizer.php';

// بررسی دسترسی: فقط ادمین یا اجرای مستقیم محلی
session_start();
$isAuthorized = !empty($_SESSION['user_id']) || !empty($_SESSION['admin_logged_in']) || (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true));

if (!$isAuthorized && ($_GET['key'] ?? '') !== 'maxa_webp_sync') {
    http_response_code(403);
    exit('۴۰۳ | دسترسی غیرمجاز. لطفاً ابتدا به عنوان مدیر وارد پنل شوید.');
}

$uploadsDir = __DIR__ . '/uploads';
$scanned = 0;
$converted = 0;
$skipped = 0;
$totalOrigBytes = 0;
$totalWebpBytes = 0;

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS));

$results = [];

foreach ($rii as $file) {
    if (!$file->isFile()) continue;
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'bmp'], true)) continue;

    $scanned++;
    $origPath = $file->getPathname();
    $webpPath = preg_replace('/\.(jpe?g|png|bmp)$/i', '.webp', $origPath);

    if (file_exists($webpPath)) {
        $skipped++;
        continue;
    }

    $origSize = filesize($origPath);
    $newPath = maxa_optimize_image_to_webp($origPath, $webpPath, 82, 1920);

    if ($newPath && file_exists($newPath)) {
        $webpSize = filesize($newPath);
        $converted++;
        $totalOrigBytes += $origSize;
        $totalWebpBytes += $webpSize;
        $savedPct = $origSize > 0 ? round((($origSize - $webpSize) / $origSize) * 100) : 0;
        $results[] = [
            'file' => str_replace(__DIR__, '', $origPath),
            'orig_kb' => round($origSize / 1024, 1),
            'webp_kb' => round($webpSize / 1024, 1),
            'saved' => $savedPct . '%'
        ];
    }
}

$savedBytes = max(0, $totalOrigBytes - $totalWebpBytes);
$savedMb = round($savedBytes / (1024 * 1024), 2);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>بهینه‌سازی و تبدیل تصاویر به WebP | مکسا</title>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700;900&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Vazirmatn', sans-serif; background: #f8f9fa; color: #2f3437; padding: 30px 20px; direction: rtl; }
  .box { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 18px; padding: 30px; box-shadow: 0 4px 18px rgba(0,0,0,0.06); }
  h1 { color: #008f8a; font-size: 22px; margin-bottom: 20px; }
  .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 25px; }
  .stat { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 14px; text-align: center; }
  .stat-val { font-size: 22px; font-weight: 900; color: #166534; }
  .stat-lbl { font-size: 12px; color: #4b5563; margin-top: 4px; }
  table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 20px; }
  th, td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: right; }
  th { background: #f9fafb; font-weight: 800; color: #374151; }
  .badge { background: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11.5px; }
  .btn-back { display: inline-block; background: #008f8a; color: #fff; text-decoration: none; padding: 10px 22px; border-radius: 10px; font-weight: 800; margin-top: 20px; }
</style>
</head>
<body>
<div class="box">
  <h1>⚡ نتیجه بهینه‌سازی و تبدیل خودکار به WebP</h1>
  <div class="stats">
    <div class="stat"><div class="stat-val"><?= $scanned ?></div><div class="stat-lbl">کل تصاویر بررسی‌شده</div></div>
    <div class="stat"><div class="stat-val"><?= $converted ?></div><div class="stat-lbl">تصاویر جدید تبدیل‌شده</div></div>
    <div class="stat"><div class="stat-val"><?= $skipped ?></div><div class="stat-lbl">از قبل دارای نسخه WebP</div></div>
    <div class="stat"><div class="stat-val"><?= $savedMb ?> MB</div><div class="stat-lbl">صرفه‌جویی در پهنای‌باند</div></div>
  </div>

  <?php if (!empty($results)): ?>
    <h3>فهرست فایل‌های تبدیل‌شده اخیر:</h3>
    <table>
      <thead>
        <tr>
          <th>مسیر تصویر</th>
          <th>حجم اولیه</th>
          <th>حجم WebP</th>
          <th>درصد کاهش</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (array_slice($results, 0, 50) as $res): ?>
          <tr>
            <td style="direction:ltr;text-align:left;"><?= htmlspecialchars($res['file']) ?></td>
            <td><?= $res['orig_kb'] ?> KB</td>
            <td><?= $res['webp_kb'] ?> KB</td>
            <td><span class="badge"><?= $res['saved'] ?> کاهش</span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (count($results) > 50): ?>
      <p style="color:#6b7280;font-size:12px;margin-top:10px;">و <?= count($results) - 50 ?> تصویر دیگر...</p>
    <?php endif; ?>
  <?php else: ?>
    <p style="color:#059669;font-weight:700;">تمامی تصاویر موجود در پوشه uploads پیش از این دارای نسخه WebP بودند و نیازی به تبدیل مجدد نبود.</p>
  <?php endif; ?>

  <a href="/dashboard/maxapedia.php" class="btn-back">بازگشت به پیشخوان مدیریت</a>
</div>
</body>
</html>

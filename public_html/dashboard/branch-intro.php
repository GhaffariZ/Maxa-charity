<?php
/* ============================================================================
 *  صفحه مدیریت معرفی شعبه (مخصوص ادمین‌های شعب — غیر از ستاد مرکزی)
 * ----------------------------------------------------------------------------
 *  امکان ثبت و ویرایش:
 *    - متن معرفی شعبه
 *    - آدرس دقیق و تلفن تماس
 *    - بارگذاری هر تعداد تصویر دلخواه برای گالری شعبه
 *    - حذف یا مدیریت تصاویر آپلود شده
 * ========================================================================== */

require_once __DIR__ . '/_guard.php';

$pdo = dash_pdo();
$branchId = dash_active_branch_id();
$branch = dash_load_branch($branchId);

if (!$branch) {
    http_response_code(404);
    exit('۴۰۴ | شعبه یافت نشد.');
}

// بررسی: ستاد مرکزی به این صفحه نیاز ندارد
$isHq = (int)($branch['is_hq'] ?? 0) === 1;
if ($isHq) {
    http_response_code(403);
    $PANEL_TITLE = 'دسترسی غیرمجاز';
    require_once __DIR__ . '/_panel_head.php';
    echo '<div class="wrap"><div class="msg err">این صفحه مختص مدیریت و معرفی شعب است و برای ستاد مرکزی غیرفعال می‌باشد.</div></div></body></html>';
    exit;
}

// اطمینان از وجود جدول branch_intros
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `branch_intros` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `branch_id` INT NOT NULL UNIQUE,
          `title` VARCHAR(255) NULL,
          `intro_text` MEDIUMTEXT NULL,
          `address` TEXT NULL,
          `phone` VARCHAR(100) NULL,
          `working_hours` VARCHAR(150) NULL,
          `email` VARCHAR(150) NULL,
          `images` LONGTEXT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          INDEX `idx_branch_intros_branch` (`branch_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
} catch (Throwable $e) {
    // در صورت بروز خطا در DDL نادیده می‌گیریم
}

// خواندن اطلاعات فعلی شعبه
$stmt = $pdo->prepare("SELECT * FROM branch_intros WHERE branch_id = ? LIMIT 1");
$stmt->execute([$branchId]);
$intro = $stmt->fetch() ?: [
    'title'         => 'معرفی شعبه ' . ($branch['name'] ?? ''),
    'intro_text'    => '',
    'address'       => $branch['address'] ?? '',
    'phone'         => $branch['phone'] ?? '',
    'working_hours' => 'شنبه تا چهارشنبه: ۸:۰۰ الی ۱۶:۰۰',
    'images'        => '[]',
];

$existingImages = json_decode((string)($intro['images'] ?? '[]'), true);
if (!is_array($existingImages)) {
    $existingImages = [];
}

$err = '';
$ok  = '';

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // ۱) حذف تکی تصویر در صورت درخواست
    if (isset($_POST['action']) && $_POST['action'] === 'delete_image') {
        $imgToDelete = trim((string)($_POST['image_path'] ?? ''));
        if ($imgToDelete !== '' && in_array($imgToDelete, $existingImages, true)) {
            $existingImages = array_values(array_diff($existingImages, [$imgToDelete]));
            
            // حذف فایل فیزیکی در صورت وجود در مسیر uploads
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imgToDelete;
            if (file_exists($fullPath) && strpos($imgToDelete, '/uploads/') === 0) {
                @unlink($fullPath);
            }

            $upStmt = $pdo->prepare("UPDATE branch_intros SET images = ? WHERE branch_id = ?");
            $upStmt->execute([json_encode($existingImages, JSON_UNESCAPED_UNICODE), $branchId]);
            dash_audit('branch_intro_delete_image', ['branch_id' => $branchId, 'image' => $imgToDelete]);
            $ok = 'تصویر با موفقیت حذف شد.';
        }
    } 
    // ۲) ذخیره متن معرفی، آدرس و تصاویر جدید
    else {
        $title        = trim((string)($_POST['title'] ?? ''));
        $introText    = trim((string)($_POST['intro_text'] ?? ''));
        $address      = trim((string)($_POST['address'] ?? ''));
        $phone        = trim((string)($_POST['phone'] ?? ''));
        $workingHours = trim((string)($_POST['working_hours'] ?? ''));

        if ($title === '') {
            $title = 'معرفی شعبه ' . ($branch['name'] ?? '');
        }

        // بررسی و ذخیره تصاویر جدید آپلود شده
        if (!empty($_FILES['photos']['name'][0])) {
            $uploadDir = '/uploads/branch_intro/' . $branchId . '/';
            $targetDir = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;

            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $maxFileSize = 10 * 1024 * 1024; // 10MB per image

            $totalFiles = count($_FILES['photos']['name']);
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['photos']['tmp_name'][$i];
                    $origName = $_FILES['photos']['name'][$i];
                    $fileSize = $_FILES['photos']['size'][$i];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                    if (in_array($ext, $allowedExtensions, true) && $fileSize <= $maxFileSize) {
                        // تولید نام تصادفی امن
                        $newFileName = 'intro_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                        $destPath = $targetDir . $newFileName;

                        if (move_uploaded_file($tmpName, $destPath)) {
                            $existingImages[] = $uploadDir . $newFileName;
                        }
                    }
                }
            }
        }

        // ثبت یا به‌روزرسانی در دیتابیس
        try {
            $imagesJson = json_encode($existingImages, JSON_UNESCAPED_UNICODE);
            
            $upsert = $pdo->prepare("
                INSERT INTO branch_intros (branch_id, title, intro_text, address, phone, working_hours, images)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    intro_text = VALUES(intro_text),
                    address = VALUES(address),
                    phone = VALUES(phone),
                    working_hours = VALUES(working_hours),
                    images = VALUES(images)
            ");
            $upsert->execute([$branchId, $title, $introText, $address, $phone, $workingHours, $imagesJson]);

            // به‌روزرسانی متغیرهای نمایشی
            $intro['title']         = $title;
            $intro['intro_text']    = $introText;
            $intro['address']       = $address;
            $intro['phone']         = $phone;
            $intro['working_hours'] = $workingHours;
            $intro['images']        = $imagesJson;

            dash_audit('branch_intro_update', ['branch_id' => $branchId]);
            $ok = 'اطلاعات و تصاویر معرفی شعبه با موفقیت ذخیره شد.';
        } catch (Throwable $e) {
            $err = 'خطا در ذخیره‌سازی اطلاعات: ' . $e->getMessage();
        }
    }
}

$PANEL_TITLE = 'معرفی شعبه ' . ($branch['name'] ?? '');
require_once __DIR__ . '/_panel_head.php';
?>

<style>
  .intro-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 22px;
    box-shadow: var(--shadow-sm);
  }
  .intro-card h2 {
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 6px;
    color: var(--color-text);
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .intro-card .hint {
    font-size: 12.5px;
    color: var(--color-muted);
    margin-bottom: 18px;
    line-height: 1.7;
  }
  .field-group {
    margin-bottom: 18px;
  }
  .field-group label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--color-text);
  }
  .field-group input[type=text],
  .field-group textarea {
    width: 100%;
    border: 1px solid var(--color-border);
    background: var(--color-bg);
    border-radius: 12px;
    padding: 12px 14px;
    font-family: inherit;
    font-size: 13.5px;
    color: var(--color-text);
    transition: border-color .2s, box-shadow .2s;
  }
  .field-group input:focus,
  .field-group textarea:focus {
    outline: none;
    border-color: var(--color-primary-light);
    box-shadow: 0 0 0 4px var(--primary-08);
    background: var(--color-surface);
  }
  .field-group textarea {
    min-height: 160px;
    line-height: 1.8;
    resize: vertical;
  }

  /* آپلود فایل چندگانه با دراگ و دراپ */
  .uploader-box {
    border: 2px dashed var(--color-border);
    border-radius: 16px;
    padding: 32px 20px;
    text-align: center;
    background: var(--color-bg);
    cursor: pointer;
    transition: all .2s ease;
    position: relative;
    overflow: hidden;
  }
  .uploader-box:hover, .uploader-box.dragover {
    border-color: var(--color-primary);
    background: var(--primary-08);
  }
  .uploader-box input[type=file] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
  }
  .uploader-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--primary-08);
    color: var(--color-primary);
    display: grid;
    place-items: center;
    margin: 0 auto 12px;
    font-size: 22px;
  }
  .uploader-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--color-text);
    margin-bottom: 4px;
  }
  .uploader-desc {
    font-size: 12px;
    color: var(--color-muted);
  }

  /* گالری تصاویر موجود */
  .gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
    margin-top: 18px;
  }
  .gallery-item {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    background: #000;
    aspect-ratio: 4/3;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--color-border);
  }
  .gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .3s ease, opacity .3s ease;
  }
  .gallery-item:hover img {
    transform: scale(1.06);
    opacity: .85;
  }
  .gallery-item-actions {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.4);
    opacity: 0;
    transition: opacity .2s ease;
  }
  .gallery-item:hover .gallery-item-actions {
    opacity: 1;
  }
  .btn-del-img {
    background: #e0556b;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 8px 14px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(224,85,107,0.4);
    transition: transform .15s ease;
  }
  .btn-del-img:hover {
    transform: scale(1.05);
  }

  .preview-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 18px;
    background: var(--primary-08);
    border: 1px solid rgba(0,123,122,0.2);
    border-radius: 14px;
    margin-bottom: 22px;
  }
  .preview-bar a {
    font-weight: 800;
    color: var(--color-primary);
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .file-queue {
    margin-top: 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  .file-chip {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 4px 10px;
    font-size: 12px;
    color: var(--color-text);
  }
</style>

<div class="wrap">

  <div class="page-head">
    <div class="ph-ic">
      <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
        <path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>
      </svg>
    </div>
    <div>
      <h1>صفحه معرفی <?= e($branch['name']) ?></h1>
      <p>مدیریت محتوا، آدرس دقیق، اطلاعات تماس و تصاویر اختصاصی برای نمایش در صفحه عمومی معرفی شعبه</p>
    </div>
  </div>

  <?php if ($ok): ?>
    <div class="msg ok"><?= e($ok) ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="msg err"><?= e($err) ?></div>
  <?php endif; ?>

  <div class="preview-bar">
    <span style="font-size: 13px; color: var(--color-text); font-weight: 600;">
      این اطلاعات در سایت اصلی به عنوان صفحه اختصاصی معرفی این شعبه نمایش داده می‌شود.
    </span>
    <a href="/<?= e($branch['slug']) ?>/about" target="_blank">
      <span>مشاهده صفحه معرفی در سایت</span>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
    </a>
  </div>

  <form method="POST" action="branch-intro.php" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- بخش اول: متن و عنوان معرفی -->
    <div class="intro-card">
      <h2>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        متن معرفی و تاریخچه شعبه
      </h2>
      <p class="hint">درباره خدمات، کادر درمانی، امکانات، تاریخچه و رسالت شعبه خود برای بازدیدکنندگان توضیح دهید.</p>

      <div class="field-group">
        <label>عنوان معرفی</label>
        <input type="text" name="title" value="<?= e($intro['title']) ?>" placeholder="مثلاً: آشنایی با شعبه اصفهان مکسا">
      </div>

      <div class="field-group">
        <label>متن معرفی کامل شعبه</label>
        <textarea name="intro_text" placeholder="توضیحات و معرفی کامل شعبه را در این بخش بنویسید..."><?= e($intro['intro_text']) ?></textarea>
      </div>
    </div>

    <!-- بخش دوم: آدرس و اطلاعات تماس -->
    <div class="intro-card">
      <h2>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        آدرس و اطلاعات تماس شعبه
      </h2>
      <p class="hint">موقعیت دقیق فیزیکی و راه‌های ارتباطی با شعبه را مشخص کنید.</p>

      <div class="field-group">
        <label>آدرس پستی شعبه</label>
        <textarea name="address" style="min-height: 80px;" placeholder="استان، شهر، خیابان، پلاک، طبقه و..."><?= e($intro['address']) ?></textarea>
      </div>

      <div class="grid2">
        <div class="field-group">
          <label>شماره تلفن / خطوط ارتباطی</label>
          <input type="text" name="phone" value="<?= e($intro['phone']) ?>" placeholder="مثال: ۰۳۱-۳۲۲۲۰۰۰۰" dir="ltr">
        </div>
        <div class="field-group">
          <label>ساعت کاری و پذیرش</label>
          <input type="text" name="working_hours" value="<?= e($intro['working_hours']) ?>" placeholder="مثال: شنبه تا چهارشنبه ۸:۰۰ الی ۱۶:۰۰">
        </div>
      </div>
    </div>

    <!-- بخش سوم: تصاویر و گالری شعبه -->
    <div class="intro-card">
      <h2>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        گالری تصاویر شعبه
      </h2>
      <p class="hint">می‌توانید هر تعداد تصویر باکیفیت از ساختمان شعبه، امکانات، کادر و فعالیت‌ها بارگذاری کنید.</p>

      <div class="uploader-box" id="dropZone">
        <input type="file" name="photos[]" id="fileInput" multiple accept="image/jpeg,image/png,image/webp">
        <div class="uploader-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
        <div class="uploader-title">برای انتخاب تصاویر کلیک کنید یا آن‌ها را به این کادر بکشید</div>
        <div class="uploader-desc">فرمت‌های مجاز: JPG, PNG, WEBP (بدون محدودیت تعداد)</div>
      </div>

      <div id="fileQueue" class="file-queue"></div>

      <!-- تصاویر قبلی بارگذاری شده -->
      <?php if (!empty($existingImages)): ?>
        <div style="margin-top: 24px;">
          <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 12px; color: var(--color-text);">
            تصاویر بارگذاری‌شده فعلی (<?= count($existingImages) ?> تصویر):
          </h3>
          <div class="gallery-grid">
            <?php foreach ($existingImages as $img): ?>
              <div class="gallery-item">
                <img src="<?= e($img) ?>" alt="تصویر شعبه">
                <div class="gallery-item-actions">
                  <button type="button" class="btn-del-img" onclick="deleteImage('<?= e($img) ?>')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    حذف تصویر
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
      <button type="submit" class="btn btn-primary" style="padding: 0 32px;">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        ذخیره تغییرات معرفی شعبه
      </button>
    </div>
  </form>

  <!-- فرم مخفی برای حذف تکی تصویر -->
  <form id="deleteImageForm" method="POST" action="branch-intro.php" style="display: none;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="delete_image">
    <input type="hidden" name="image_path" id="deleteImagePath" value="">
  </form>

</div>

<script>
  function deleteImage(path) {
    if (confirm('آیا از حذف این تصویر اطمینان دارید؟')) {
      document.getElementById('deleteImagePath').value = path;
      document.getElementById('deleteImageForm').submit();
    }
  }

  // پیش‌نمایش نام فایل‌های انتخاب‌شده
  const fileInput = document.getElementById('fileInput');
  const fileQueue = document.getElementById('fileQueue');
  const dropZone  = document.getElementById('dropZone');

  if (fileInput) {
    fileInput.addEventListener('change', function() {
      fileQueue.innerHTML = '';
      if (this.files && this.files.length > 0) {
        Array.from(this.files).forEach(f => {
          const chip = document.createElement('div');
          chip.className = 'file-chip';
          chip.textContent = '📸 ' + f.name + ' (' + (f.size / 1024).toFixed(0) + ' KB)';
          fileQueue.appendChild(chip);
        });
      }
    });

    ['dragenter', 'dragover'].forEach(eventName => {
      dropZone.addEventListener(eventName, e => {
        e.preventDefault(); e.stopPropagation();
        dropZone.classList.add('dragover');
      });
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropZone.addEventListener(eventName, e => {
        e.preventDefault(); e.stopPropagation();
        dropZone.classList.remove('dragover');
      });
    });
  }
</script>

</body>
</html>

<?php
require_once __DIR__ . '/_guard.php';
dash_require('news');
function slugify($text) {

    $text = trim($text);

    // نرمال‌سازی حروف عربی/فارسی
    $text = str_replace([
        'آ', 'أ', 'إ', 'ٱ',
        'ي', 'ى',
        'ك'
    ], [
        'ا', 'ا', 'ا', 'ا',
        'ی', 'ی',
        'ک'
    ], $text);

    // حذف کاراکترهای غیرمجاز
    $text = preg_replace('/[^\p{Arabic}\p{L}\p{N}\s-]/u', '', $text);

    // تبدیل فاصله به -
    $text = preg_replace('/[\s-]+/u', '-', $text);

    return trim($text, '-');
}
function gregorianToJalali($gy, $gm, $gd) {
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

    if ($gy > 1600) {
        $jy = 979;
        $gy -= 1600;
    } else {
        $jy = 0;
        $gy -= 621;
    }

    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;

    $days = (365 * $gy)
        + intval(($gy2 + 3) / 4)
        - intval(($gy2 + 99) / 100)
        + intval(($gy2 + 399) / 400)
        - 80
        + $gd
        + $g_d_m[$gm - 1];

    $jy += 33 * intval($days / 12053);
    $days %= 12053;

    $jy += 4 * intval($days / 1461);
    $days %= 1461;

    if ($days > 365) {
        $jy += intval(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }

    if ($days < 186) {
        $jm = 1 + intval($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intval(($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }

    return [$jy, $jm, $jd];
}

function formatJalaliDateTime($datetime) {
    if (empty($datetime)) {
        return '---';
    }

    $timestamp = strtotime($datetime);

    if (!$timestamp) {
        return '---';
    }

    $gy = (int)date('Y', $timestamp);
    $gm = (int)date('m', $timestamp);
    $gd = (int)date('d', $timestamp);

    [$jy, $jm, $jd] = gregorianToJalali($gy, $gm, $gd);

    $hour = date('H', $timestamp);
    $minute = date('i', $timestamp);

    return sprintf('%04d/%02d/%02d %s:%s', $jy, $jm, $jd, $hour, $minute);
}

function faNumbers($text) {
    return str_replace(
        ['0','1','2','3','4','5','6','7','8','9'],
        ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'],
        $text
    );
}


// news-list.php
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

// ایزولاسیون چندمستأجری: فقط اخبارِ شعبه‌ی فعال
$__branch = dash_active_branch_id();
$stmt = $pdo->prepare("SELECT * FROM news WHERE branch_id = ? ORDER BY id DESC");
$stmt->execute([$__branch]);
$news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>لیست اخبار | پنل مکسا</title>
<!-- اعمالِ تم پیش از رنگ‌آمیزی تا از پرشِ نور→تاریک جلوگیری شود (کلید مشترک: maxa-theme) -->
<script>(function(){try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/panel.css">
<style>
  /* پالت و توکن‌های این صفحه به رنگ‌بندیِ رسمیِ داشبورد (panel.css) هم‌تراز شده‌اند */
  :root {
      --bg-color: var(--color-bg);
      --card-bg: var(--color-surface);
      --text-main: var(--color-text);
      --text-muted: var(--color-muted);
      --border-color: var(--color-border);
      --row-hover: var(--color-bg);
      --header-bg: var(--color-bg);
      --detail-bg: var(--color-bg);

      /* پالت مکسا (هم‌تراز با داشبورد) */
      --primary-color: var(--color-primary);
      --primary-hover: var(--color-primary-dark);
      --secondary-color: var(--color-secondary);

      /* رنگ‌های وضعیت */
      --st-draft-bg: var(--secondary-12); --st-draft-text: #b9760a;
      --st-review-bg: var(--primary-08); --st-review-text: var(--color-primary-dark);
      --st-rejected-bg: rgba(224,85,107,.12); --st-rejected-text: var(--danger); --st-rejected-border: rgba(224,85,107,.22);
      --st-published-bg: rgba(22,163,122,.14); --st-published-text: var(--success); --st-published-border: rgba(22,163,122,.24);
      --st-scheduled-bg: var(--violet-12); --st-scheduled-text: var(--violet); --st-scheduled-border: rgba(124,77,219,.24);
      --st-review-border: var(--primary-12);
      --rej-accent: var(--danger);
  }

  * { box-sizing: border-box; font-family: 'Vazirmatn', Tahoma, sans-serif; }
  body { direction: rtl; background: var(--color-bg); padding: 26px; color: var(--color-text); margin: 0; transition: background 0.3s, color 0.3s; }

  /* سربرگِ صفحه و نوار هم‌عرضِ جدول (۱۱۰۰px) */
  .news-head-wrap { max-width: 1100px; margin: 0 auto; }
  @media (max-width: 640px){ body{ padding:16px } }
  
  .header-actions { max-width: 1100px; margin: 0 auto 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
  
  .action-buttons { display: flex; align-items: center; gap: 10px; }
  
  .btn-new { background: var(--primary-color); color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: bold; box-shadow: 0 4px 6px rgba(0, 128, 117, 0.2); transition: 0.3s; font-size: 14px; }
  .btn-new:hover { background: var(--primary-hover); }

  /* دکمه دایره‌ای دارک‌مود */
  .btn-theme { background: var(--card-bg); color: var(--text-main); border: 1px solid var(--border-color); width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
  .btn-theme:hover { border-color: var(--primary-color); }

  .table-wrapper { background: var(--card-bg); border-radius: 15px; box-shadow: 0 0.15rem 1.75rem 0 rgba(0,0,0,0.05); margin: 0 auto; max-width: 1100px; overflow-x: auto; transition: background 0.3s; }
  .news-table { width: 100%; border-collapse: collapse; min-width: 600px; }
  .news-table th { background: var(--header-bg); padding: 18px; text-align: right; color: var(--primary-color); font-weight: 700; border-bottom: 2px solid var(--border-color); font-size: 14px; white-space: nowrap; transition: 0.3s; }
  .main-row td { padding: 18px; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: 0.2s; font-size: 14px; }
  .main-row:hover { background: var(--row-hover); }

  .st-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; display: inline-block; text-align: center; min-width: 90px; }
  .st-draft { background: var(--st-draft-bg); color: var(--st-draft-text); }
  .st-review { background: var(--st-review-bg); color: var(--st-review-text); border: 1px solid var(--st-review-border); }
  .st-rejected { background: var(--st-rejected-bg); color: var(--st-rejected-text); border: 1px solid var(--st-rejected-border); }
  .st-published { background: var(--st-published-bg); color: var(--st-published-text); border: 1px solid var(--st-published-border); }
  .st-scheduled { background: var(--st-scheduled-bg); color: var(--st-scheduled-text); border: 1px solid var(--st-scheduled-border); }

  .arrow-icon { transition: 0.3s; color: var(--text-muted); font-size: 14px; }
  .main-row.active .arrow-icon { transform: rotate(180deg); color: var(--secondary-color); }

  .detail-row { display: none; background: var(--detail-bg); }
  .detail-container { padding: 25px; border-bottom: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 25px; }

  /* Stepper */
  .stepper { display: flex; justify-content: space-between; position: relative; margin-bottom: 20px; padding: 0 20px; }
  .stepper::before { content: ''; position: absolute; top: 15px; left: 50px; right: 50px; height: 2px; background: var(--border-color); z-index: 1; }
  
  .step { position: relative; z-index: 2; background: var(--detail-bg); padding: 0 10px; text-align: center; flex: 1; transition: 0.3s; }
  .step-circle { width: 32px; height: 32px; border-radius: 50%; background: var(--card-bg); border: 2px solid var(--border-color); margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: bold; color: var(--text-muted); transition: 0.4s; }
  .step-label { font-size: 12px; font-weight: 600; color: var(--text-muted); }

  .status-review .step.active .step-circle { border-color: var(--secondary-color); background: var(--secondary-color); color: #fff; box-shadow: 0 0 0 4px rgba(248,162,39,0.2); }
  .status-review .step.active .step-label { color: var(--secondary-color); font-weight: 800; }

  .status-rejected .step.active .step-circle { border-color: var(--danger); background: var(--danger); color: #fff; box-shadow: 0 0 0 4px rgba(224,85,107,0.2); }
  .status-rejected .step.active .step-label { color: var(--danger); font-weight: 800; }

  .status-published .step.active .step-circle { border-color: var(--primary-color); background: var(--primary-color); color: #fff; }
  .status-published .step.active .step-label { color: var(--primary-color); font-weight: 800; }

  .status-scheduled .step.active .step-circle { border-color: var(--violet); background: var(--violet); color: #fff; box-shadow: 0 0 0 4px var(--violet-12); }
  .status-scheduled .step.active .step-label { color: var(--violet); font-weight: 800; }

  .step.completed .step-circle { border-color: var(--primary-color); background: var(--primary-color); color: #fff; }

  .minimal-actions { display: flex; gap: 10px; flex-wrap: wrap; }
  .btn-min { padding: 10px 16px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--card-bg); font-size: 13px; font-weight: 600; color: var(--primary-color); cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s; text-decoration: none; }
  .btn-min:hover { background: var(--row-hover); border-color: var(--primary-color); }
  
  .reject-box { background: var(--st-rejected-bg); border-right: 4px solid var(--danger); padding: 15px; border-radius: 10px; font-size: 13px; line-height: 1.6; color: var(--text-main); border: 1px solid var(--st-rejected-border); border-right-width: 4px; }

  /* Modal */
  .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 1000; opacity: 0; transition: 0.3s; }
  .modal-overlay.show { display: flex; opacity: 1; }
  .modal-box { background: var(--card-bg); padding: 30px; border-radius: 15px; width: 90%; max-width: 400px; transform: translateY(-20px); transition: 0.3s; color: var(--text-main); }
  .modal-overlay.show .modal-box { transform: translateY(0); }
  .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
  .modal-btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold; font-size: 14px; }
  .modal-input { width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; outline: none; margin-top: 15px; font-size: 14px; background: var(--bg-color); color: var(--text-main); }

  /* Responsive Design */
  @media (max-width: 768px) {
      .header-actions { flex-direction: column; align-items: stretch; }
      .action-buttons { justify-content: space-between; }
      .btn-new { flex-grow: 1; text-align: center; }
      .stepper::before { display: none; }
      .stepper { flex-direction: column; gap: 15px; padding: 0; }
      .step { display: flex; align-items: center; text-align: right; gap: 15px; padding: 0; }
      .step-circle { margin: 0; }
  }

  /* ---------- Bulk Select Mode ---------- */

.title-area {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-select-mode {
    background: var(--card-bg);
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
    padding: 10px 18px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
    transition: 0.25s;
    box-shadow: 0 4px 8px rgba(0,0,0,0.04);
}

.btn-select-mode:hover {
    background: var(--primary-color);
    color: #fff;
}

.select-col {
    display: none;
    width: 55px;
    text-align: center !important;
}

body.select-mode .select-col {
    display: table-cell;
}

.news-checkbox,
#selectAllCheckbox {
    width: 18px;
    height: 18px;
    accent-color: var(--primary-color);
    cursor: pointer;
}

.bulk-actions-bar {
    max-width: 1100px;
    margin: 0 auto 20px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-right: 5px solid var(--primary-color);
    border-radius: 14px;
    padding: 14px 18px;
    display: none;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    box-shadow: 0 0.15rem 1rem rgba(0,0,0,0.06);
}

.bulk-actions-bar.show {
    display: flex;
}

.bulk-info {
    color: var(--text-main);
    font-size: 14px;
    font-weight: 700;
}

.bulk-info #selectedCount {
    color: var(--primary-color);
    font-size: 18px;
    margin-left: 5px;
}

.bulk-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-bulk {
    border: none;
    padding: 10px 16px;
    border-radius: 9px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
    transition: 0.2s;
    color: #fff;
}

.btn-bulk:hover {
    transform: translateY(-1px);
    opacity: 0.9;
}

.btn-bulk-publish {
    background: var(--primary-color);
}

.btn-bulk-delete {
    background: var(--danger);
}

.btn-bulk-cancel {
    background: var(--text-muted);
}

body.select-mode .main-row td {
    cursor: default;
}

body.select-mode .main-row:hover {
    background: transparent;
}

.main-row.selected {
    background: var(--primary-08) !important;
}

[data-theme="dark"] .main-row.selected {
    background: var(--primary-12) !important;
}

body.select-mode .main-row.selected:hover {
    background: var(--primary-12) !important;
}

@media (max-width: 768px) {
    .bulk-actions-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .bulk-buttons {
        width: 100%;
    }

    .btn-bulk {
        flex: 1;
        text-align: center;
    }
}
</style>
</head>
<body>

<div class="news-head-wrap">
  <div class="page-head">
    <div class="ph-ic">
      <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Z"/><path d="M18 8h1.5A1.5 1.5 0 0 1 21 9.5V18a2 2 0 0 1-2 2"/><line x1="8" y1="8" x2="14" y2="8"/><line x1="8" y1="12" x2="14" y2="12"/><line x1="8" y1="16" x2="12" y2="16"/></svg>
    </div>
    <div class="ph-tx">
      <h1>لیست اخبار</h1>
      <p>اخبار را مدیریت کنید، وضعیت‌شان را تغییر دهید و منتشر کنید.</p>
    </div>
    <div class="ph-actions">
      <button id="selectModeBtn" class="btn btn-ghost" onclick="enterSelectMode()">انتخاب اخبار</button>
      <a href="news-create.php" class="btn btn-primary">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        ساخت خبر جدید
      </a>
    </div>
  </div>
</div>

<div id="bulkActionsBar" class="bulk-actions-bar">
    <div class="bulk-info">
        <span id="selectedCount"> 0 </span>
        خبر انتخاب شده
    </div>

    <div class="bulk-buttons">
        <button onclick="bulkPublish()" class="btn-bulk btn-bulk-publish">
            ✅ انتشار انتخاب‌شده‌ها
        </button>

        <button onclick="bulkDelete()" class="btn-bulk btn-bulk-delete">
            🗑️ حذف انتخاب‌شده‌ها
        </button>

        <button onclick="exitSelectMode()" class="btn-bulk btn-bulk-cancel">
            انصراف
        </button>
    </div>
</div>

<div class="table-wrapper">
  <table class="news-table">
    <thead>
        <tr>
            <th class="select-col">
                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
            </th>
            <th>وضعیت</th>
            <th style="width: 45%;">عنوان خبر</th>
            <th style="text-align: left;">تاریخ انتشار</th>
            <th style="text-align: center;">جزییات</th>
        </tr>
    </thead>
    <tbody>
      <?php foreach($news_list as $news): ?>
        <?php 
        $st = $news['status'] ?? 'draft';

        $reject_reason = trim($news['reject_reason'] ?? '');

        // اگر دلیل عدم تایید وجود داشته باشد، وضعیت نمایشی خبر باید عدم تایید باشد
        if ($reject_reason !== '') {
            $st = 'rejected';
        }

        $is_scheduled = false;

        if (
            $st === 'published' &&
            !empty($news['publish_date']) &&
            strtotime($news['publish_date']) > time()
        ) {
            $is_scheduled = true;
        }

        // تعیین کلاس کلی برای رنگ‌بندی استپر
        $stepper_class = $is_scheduled ? "status-scheduled" : "status-" . $st;
        ?>
        <tr class="main-row" data-id="<?= $news['id'] ?>" onclick="toggleRow(event, this, <?= $news['id'] ?>)">
          <td class="select-col">
                <input 
                    type="checkbox" 
                    class="news-checkbox" 
                    value="<?= $news['id'] ?>" 
                    onclick="handleCheckboxClick(event, this)"
                >
           </td>

          <td>
            <?php 
              if($is_scheduled) echo '<span class="st-badge st-scheduled">زمان‌بندی شده ⏳</span>';
              elseif($st == 'published') echo '<span class="st-badge st-published">منتشر شده</span>';
              elseif($st == 'review') echo '<span class="st-badge st-review">در حال بررسی</span>';
              elseif($st == 'rejected') echo '<span class="st-badge st-rejected">عدم تایید</span>';
              else echo '<span class="st-badge st-draft">ذخیره شده</span>';
            ?>
          </td>
          <td style="font-weight: 600;"><?= htmlspecialchars($news['title']) ?></td>
          <td style="color: var(--text-muted); font-size: 13px; text-align: left; direction: ltr;"><?= faNumbers(formatJalaliDateTime($news['publish_date'] ?? null)) ?></td>
          <td style="text-align: center;"><span class="arrow-icon">▼</span></td>
        </tr>

        <tr class="detail-row" id="row-<?= $news['id'] ?>">
          <td colspan="5">
            <div class="detail-container <?= $stepper_class ?>">
              
              <div class="stepper">
                <div class="step completed">
                  <div class="step-circle">۱</div>
                  <div class="step-label">ذخیره شده</div>
                </div>
                
                <div class="step <?= ($st == 'review') ? 'active' : (($st == 'published' || $st == 'rejected') ? 'completed' : '') ?>">
                  <div class="step-circle">۲</div>
                  <div class="step-label">بررسی سردبیر</div>
                </div>

                <?php if($st == 'rejected'): ?>
                <div class="step active">
                  <div class="step-circle">❌</div>
                  <div class="step-label">عدم تایید</div>
                </div>
                <?php else: ?>
                <div class="step <?= ($st == 'published') ? 'active' : '' ?>">
                  <div class="step-circle"><?= $is_scheduled ? '⏳' : '۳' ?></div>
                  <div class="step-label"><?= $is_scheduled ? 'در صف انتشار' : 'انتشار نهایی' ?></div>
                </div>
                <?php endif; ?>
              </div>

                <?php if($st == 'rejected' && $reject_reason !== ''): ?>
                <div class="reject-box">
                    <strong>💬 علت عدم تایید:</strong> <?= htmlspecialchars($reject_reason) ?>
                </div>
                <?php endif; ?>

              <div class="minimal-actions">
                <?php $slug = slugify($news['title']); ?>
                <a href="/<?= $news['id'] ?>/<?= $slug ?>/" target="_blank" class="btn-min">👁️ مشاهده</a>
                <a href="news-create.php?id=<?= $news['id'] ?>" class="btn-min">📝 ویرایش</a>
                <button onclick="updateStatus(<?= $news['id'] ?>, 'review')" class="btn-min">🧑‍💻 ارسال به سردبیر</button>
                <button onclick="updateStatus(<?= $news['id'] ?>, 'published')" class="btn-min" style="color: var(--primary-color);">✅ انتشار</button>
                <button onclick="confirmReject(<?= $news['id'] ?>)" class="btn-min" style="color: #e74a3b;">❌ عدم تایید</button>
                <button onclick="confirmDelete(<?= $news['id'] ?>)" class="btn-min" style="color: var(--text-muted);">🗑️ حذف</button>
              </div>

            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div id="customModal" class="modal-overlay">
    <div class="modal-box">
        <h3 id="modalTitle" style="margin-top:0;">عنوان</h3>
        <p id="modalText" style="color:var(--text-muted); font-size:14px;"></p>
        <input type="text" id="modalInput" class="modal-input" style="display:none;" placeholder="توضیحات را بنویسید...">
        <div class="modal-actions">
            <button class="modal-btn" style="background:var(--border-color); color:var(--text-main);" onclick="closeModal()">انصراف</button>
            <button id="modalBtnConfirm" class="modal-btn" style="color:#fff;">تایید</button>
        </div>
    </div>
</div>

<script>
// ---------- دارک مود ----------
const themeToggleBtn = document.getElementById('themeToggle');
const htmlElement = document.documentElement;

// تم از «داشبورد مدیریت» کنترل می‌شود (کلید مشترک: maxa-theme)
var applyMaxaTheme = function(){
    var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
    if(d){ document.documentElement.setAttribute('data-theme','dark'); if(document.body) document.body.setAttribute('data-theme','dark'); }
    else { document.documentElement.removeAttribute('data-theme'); if(document.body) document.body.removeAttribute('data-theme'); }
};
applyMaxaTheme();
window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });

if (themeToggleBtn) themeToggleBtn.addEventListener('click', () => {
    if (htmlElement.getAttribute('data-theme') === 'light') {
        htmlElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
        themeToggleBtn.innerText = '☀️';
    } else {
        htmlElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
        themeToggleBtn.innerText = '🌙';
    }
});

// ---------- مودال ----------
let modalCallback = null;

function showModal(cfg) {
    document.getElementById('modalTitle').innerText = cfg.title;
    document.getElementById('modalText').innerText = cfg.text;

    const input = document.getElementById('modalInput');
    input.style.display = cfg.showInput ? 'block' : 'none';
    input.value = '';
    input.placeholder = cfg.placeholder || 'توضیحات را بنویسید...';

    const btn = document.getElementById('modalBtnConfirm');
    btn.style.backgroundColor = cfg.btnColor || 'var(--primary-color)';
    btn.innerText = cfg.btnText || 'تایید';

    modalCallback = cfg.onConfirm;

    const m = document.getElementById('customModal');
    m.style.display = 'flex';
    setTimeout(() => m.classList.add('show'), 10);
}

function closeModal() {
    const m = document.getElementById('customModal');
    m.classList.remove('show');
    setTimeout(() => m.style.display = 'none', 300);
}

document.getElementById('modalBtnConfirm').onclick = () => {
    const val = document.getElementById('modalInput').value;
    if (modalCallback) modalCallback(val);
};

// ---------- باز و بسته شدن ردیف ----------
function toggleRow(event, row, newsId) {
    if (document.body.classList.contains('select-mode')) return;

    if (
        event.target.closest('button') ||
        event.target.closest('a') ||
        event.target.closest('input') ||
        event.target.closest('.select-col')
    ) {
        return;
    }

    const detailRow = document.getElementById('row-' + newsId);
    if (!detailRow) return;

    const isOpen = detailRow.style.display === 'table-row';

    document.querySelectorAll('.detail-row').forEach(r => {
        r.style.display = 'none';
    });

    document.querySelectorAll('.main-row').forEach(r => {
        r.classList.remove('active');
    });

    if (!isOpen) {
        detailRow.style.display = 'table-row';
        row.classList.add('active');
    }
}

// ---------- عملیات تکی ----------
function updateStatus(id, st, reason = '') {
    const fd = new FormData();
    fd.append('id', id);
    fd.append('status', st);
    fd.append('reason', reason);

    if (st === 'delete') {
        fetch('news-delete.php', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert(data.message || 'خطای حذف');
            }
        })
        .catch(() => alert('خطا در ارتباط با سرور'));
        return;
    }

    fetch('news-status-update.php', {
        method: 'POST',
        body: fd
    })
    .then(() => location.reload())
    .catch(() => alert('خطا در ارتباط با سرور'));
}

function confirmReject(id) {
    showModal({
        title: 'عدم تایید خبر',
        text: 'لطفاً دلیل رد شدن خبر را بنویسید:',
        showInput: true,
        btnColor: '#e74a3b',
        btnText: 'ثبت رد خبر',
        onConfirm: (val) => {
            if (!val.trim()) {
                alert('دلیل الزامی است');
                return;
            }
            updateStatus(id, 'rejected', val);
        }
    });
}

function confirmDelete(id) {
    showModal({
        title: 'حذف خبر',
        text: 'آیا از حذف این خبر اطمینان دارید؟',
        showInput: false,
        btnColor: '#e74a3b',
        btnText: 'حذف خبر',
        onConfirm: () => updateStatus(id, 'delete')
    });
}

// ---------- انتخاب گروهی ----------
let isSelectMode = false;

function enterSelectMode() {
    isSelectMode = true;
    document.body.classList.add('select-mode');

    document.getElementById('selectModeBtn').style.display = 'none';
    document.getElementById('bulkActionsBar').classList.add('show');

    document.querySelectorAll('.detail-row').forEach(row => {
        row.style.display = 'none';
    });

    document.querySelectorAll('.main-row').forEach(row => {
        row.classList.remove('active');
        row.classList.remove('selected');
    });

    document.querySelectorAll('.news-checkbox').forEach(cb => {
        cb.checked = false;
    });

    const selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll) selectAll.checked = false;

    updateSelectedCount();
}

function exitSelectMode() {
    isSelectMode = false;
    document.body.classList.remove('select-mode');

    document.getElementById('selectModeBtn').style.display = 'inline-flex';
    document.getElementById('bulkActionsBar').classList.remove('show');

    document.querySelectorAll('.news-checkbox').forEach(cb => {
        cb.checked = false;
    });

    document.querySelectorAll('.main-row').forEach(row => {
        row.classList.remove('selected');
    });

    const selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll) selectAll.checked = false;

    updateSelectedCount();
}

function handleCheckboxClick(e, checkbox) {
    e.stopPropagation();

    const row = checkbox.closest('.main-row');
    if (!row) return;

    if (checkbox.checked) {
        row.classList.add('selected');
    } else {
        row.classList.remove('selected');
    }

    syncSelectAllState();
    updateSelectedCount();
}

function toggleSelectAll(masterCheckbox) {
    const allCheckboxes = document.querySelectorAll('.news-checkbox');

    allCheckboxes.forEach(cb => {
        cb.checked = masterCheckbox.checked;

        const row = cb.closest('.main-row');
        if (row) {
            if (cb.checked) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        }
    });

    updateSelectedCount();
}

function syncSelectAllState() {
    const allCheckboxes = document.querySelectorAll('.news-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.news-checkbox:checked');
    const selectAll = document.getElementById('selectAllCheckbox');

    if (!selectAll) return;

    selectAll.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
}

function getSelectedNewsIds() {
    const ids = [];
    document.querySelectorAll('.news-checkbox:checked').forEach(cb => {
        ids.push(cb.value);
    });
    return ids;
}

function updateSelectedCount() {
    const count = document.querySelectorAll('.news-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
}

function bulkPublish() {
    const ids = getSelectedNewsIds();

    if (ids.length === 0) {
        alert('هیچ خبری انتخاب نشده است.');
        return;
    }

    showModal({
        title: 'انتشار گروهی اخبار',
        text: `آیا از انتشار ${ids.length} خبر انتخاب‌شده اطمینان دارید؟`,
        showInput: false,
        btnColor: 'var(--primary-color)',
        btnText: 'انتشار خبرها',
        onConfirm: () => bulkAction('publish', ids)
    });
}

function bulkDelete() {
    const ids = getSelectedNewsIds();

    if (ids.length === 0) {
        alert('هیچ خبری انتخاب نشده است.');
        return;
    }

    showModal({
        title: 'حذف گروهی اخبار',
        text: `آیا از حذف ${ids.length} خبر انتخاب‌شده اطمینان دارید؟`,
        showInput: false,
        btnColor: '#e74a3b',
        btnText: 'حذف خبرها',
        onConfirm: () => bulkAction('delete', ids)
    });
}

function setBulkButtonsLoading(isLoading) {
    document.querySelectorAll('.btn-bulk').forEach(btn => {
        btn.disabled = isLoading;
        btn.style.opacity = isLoading ? '0.6' : '1';
        btn.style.cursor = isLoading ? 'not-allowed' : 'pointer';
    });
}

function bulkAction(action, ids) {
    if (!ids || ids.length === 0) {
        alert('هیچ خبری انتخاب نشده است.');
        return;
    }

    closeModal();
    setBulkButtonsLoading(true);

    let requests = [];

    if (action === 'publish') {
        requests = ids.map(id => {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', 'published');
            fd.append('reason', '');

            return fetch('news-status-update.php', {
                method: 'POST',
                body: fd
            });
        });
    }

    if (action === 'delete') {
        requests = ids.map(id => {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', 'delete');

            return fetch('news-delete.php', {
                method: 'POST',
                body: fd
            }).then(res => res.json());
        });
    }

    Promise.all(requests)
        .then(() => {
            location.reload();
        })
        .catch(() => {
            setBulkButtonsLoading(false);
            alert('خطا در انجام عملیات گروهی');
        });
}
</script>
</body>
</html>
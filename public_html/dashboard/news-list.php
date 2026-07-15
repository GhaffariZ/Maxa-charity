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

// نقش‌ها: سردبیر (ستاد مرکزی) همه‌ی اخبارِ همه‌ی شعب را می‌بیند؛ سایرین فقط شعبه‌ی خود.
$__branch    = dash_active_branch_id();
$__isEditor  = dash_is_news_editor();
$__reviewOnly = !empty($_GET['review']);  // صفِ بررسیِ سردبیری

if ($__isEditor) {
    // سردبیر: همه‌ی شعب، با نامِ شعبه؛ اخبارِ «در حال بررسی» در ابتدا (اولویتِ نمایش).
    $where = $__reviewOnly ? "WHERE n.status = 'review'" : "";
    $stmt = $pdo->prepare(
        "SELECT n.*, b.name AS branch_name, b.slug AS branch_slug
           FROM news n LEFT JOIN branches b ON b.id = n.branch_id
           $where
          ORDER BY (n.status = 'review') DESC, n.id DESC"
    );
    $stmt->execute();
} else {
    // ایزولاسیون چندمستأجری: فقط اخبارِ شعبه‌ی فعال
    $stmt = $pdo->prepare(
        "SELECT n.*, b.name AS branch_name, b.slug AS branch_slug
           FROM news n LEFT JOIN branches b ON b.id = n.branch_id
          WHERE n.branch_id = ? ORDER BY n.id DESC"
    );
    $stmt->execute([$__branch]);
}
$news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="light">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

<style>
  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }
</style>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مدیریت محتوا</title>
<style>
  /* ===== توکن‌های طراحی، هماهنگ با صفحه‌ی ساخت خبر ===== */
  :root {
      --bg-color: #f4f7f6;
      --card-bg: #ffffff;
      --surface-2: #f7faf9;
      --text-main: #333333;
      --text-muted: #6b7674;
      --border-color: #e3e9e8;
      --row-hover: #f1f5f4;
      --header-bg: #f7faf9;
      --detail-bg: #f7faf9;

      /* پالت مکسا */
      --primary-color: #007D75;   /* سبز-آبی مکسا */
      --primary-dark: #006159;
      --primary-hover: #006159;
      --secondary-color: #F79F1F; /* نارنجی مکسا */
      --danger: #e74c3c;

      --radius: 16px;
      --radius-sm: 10px;
      --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
      --shadow-md: 0 8px 24px rgba(0,0,0,0.07);
      --anim-fast: 220ms;

      /* رنگ‌های وضعیت */
      --st-draft-bg: #eaecf4; --st-draft-text: #5a5c69;
      --st-review-bg: #fff3cd; --st-review-text: #856404; --st-review-border: #ffeeba;
      --st-rejected-bg: #f8d7da; --st-rejected-text: #721c24; --st-rejected-border: #f5c6cb;
      --st-published-bg: #d4edda; --st-published-text: #155724; --st-published-border: #c3e6cb;
      --st-scheduled-bg: #e0f2f1; --st-scheduled-text: #00695c; --st-scheduled-border: #b2dfdb;
  }

  [data-theme="dark"] {
      --bg-color: #121212;
      --card-bg: #1e1e1e;
      --surface-2: #262626;
      --text-main: #e0e0e0;
      --text-muted: #9aa3a1;
      --border-color: #383838;
      --row-hover: #2a2a2a;
      --header-bg: #262626;
      --detail-bg: #1a1a1a;
      --primary-color: #00a89d;
      --primary-dark: #00897e;
      --primary-hover: #00897e;
      --secondary-color: #ffb142;
      --shadow-sm: 0 2px 8px rgba(0,0,0,0.35);
      --shadow-md: 0 8px 24px rgba(0,0,0,0.45);

      --st-draft-bg: #333; --st-draft-text: #ddd;
      --st-review-bg: #4d4013; --st-review-text: #ffdd57; --st-review-border: #665518;
      --st-rejected-bg: #52181d; --st-rejected-text: #ff8a95; --st-rejected-border: #6b1f26;
      --st-published-bg: #173e22; --st-published-text: #8cd69f; --st-published-border: #1f542e;
      --st-scheduled-bg: #003631; --st-scheduled-text: #4db6ac; --st-scheduled-border: #004d40;
  }

  * { box-sizing: border-box; }
  body { direction: rtl; background: var(--bg-color); padding: 0 16px 40px; color: var(--text-main); margin: 0; transition: background 0.3s, color 0.3s; font-family: 'Vazirmatn', Tahoma, sans-serif; }
  .wrap { max-width: 1100px; margin: 0 auto; }

  /* ===== هدر صفحه (هم‌سبکِ editor-header در ساخت خبر) ===== */
  .page-head {
      position: sticky; top: 0; z-index: 20;
      background: color-mix(in srgb, var(--bg-color) 88%, transparent);
      backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--border-color);
      margin: 0 -16px 24px; padding: 14px 16px;
      display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;
  }
  .ph-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
  .ph-ic {
      width: 44px; height: 44px; flex-shrink: 0; border-radius: 12px;
      display: flex; align-items: center; justify-content: center; color: #fff;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      box-shadow: var(--shadow-sm);
  }
  .ph-ic svg { width: 22px; height: 22px; }
  .ph-title h1 { margin: 0; font-size: 1.3rem; font-weight: 800; color: var(--primary-color); }
  .ph-title p { margin: 2px 0 0; font-size: 12px; color: var(--text-muted); }
  .ph-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

  /* ===== دکمه‌ها (هم‌سبکِ ساخت خبر) ===== */
  .btn {
      padding: 10px 18px; border: none; border-radius: 999px; cursor: pointer;
      font-weight: 700; font-size: 14px; font-family: inherit;
      display: inline-flex; align-items: center; justify-content: center; gap: 7px;
      transition: transform var(--anim-fast), box-shadow var(--anim-fast), background var(--anim-fast), opacity var(--anim-fast);
      text-decoration: none;
  }
  .btn svg { width: 17px; height: 17px; }
  .btn:hover { transform: translateY(-2px); }
  .btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
  .btn-new { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: #fff; box-shadow: var(--shadow-sm); }
  .btn-new:hover { box-shadow: var(--shadow-md); }
  .btn-ghost { background: transparent; color: var(--primary-color); border: 1px solid var(--primary-color); }
  .btn-ghost:hover { background: rgba(0,125,117,0.06); }

  /* ===== کارت جدول ===== */
  .table-wrapper { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; transition: background 0.3s; }
  .news-table { width: 100%; border-collapse: collapse; }
  .news-table th { background: var(--header-bg); padding: 15px 18px; text-align: right; color: var(--primary-color); font-weight: 700; border-bottom: 1px solid var(--border-color); font-size: 13px; white-space: nowrap; }
  .main-row td { padding: 15px 18px; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: 0.2s; font-size: 14px; vertical-align: middle; }
  .main-row:last-child td { border-bottom: none; }
  .main-row:hover { background: var(--row-hover); }
  .news-title-text { font-weight: 700; }
  .branch-chip { font-size: 11px; font-weight: 700; color: var(--primary-color); background: rgba(0,125,117,.10); padding: 3px 9px; border-radius: 999px; margin-inline-start: 6px; white-space: nowrap; }

  /* ===== چیپ وضعیت ===== */
  .st-badge { padding: 6px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; text-align: center; }
  .st-badge svg { width: 13px; height: 13px; }
  .st-draft { background: var(--st-draft-bg); color: var(--st-draft-text); }
  .st-review { background: var(--st-review-bg); color: var(--st-review-text); border: 1px solid var(--st-review-border); }
  .st-rejected { background: var(--st-rejected-bg); color: var(--st-rejected-text); border: 1px solid var(--st-rejected-border); }
  .st-published { background: var(--st-published-bg); color: var(--st-published-text); border: 1px solid var(--st-published-border); }
  .st-scheduled { background: var(--st-scheduled-bg); color: var(--st-scheduled-text); border: 1px solid var(--st-scheduled-border); }

  .arrow-icon { display: inline-flex; transition: 0.3s; color: var(--text-muted); }
  .arrow-icon svg { width: 18px; height: 18px; }
  .main-row.active .arrow-icon { transform: rotate(180deg); color: var(--secondary-color); }

  .detail-row { display: none; background: var(--detail-bg); }
  .detail-container { padding: 24px; border-bottom: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 22px; }

  /* ===== Stepper ===== */
  .stepper { display: flex; justify-content: space-between; position: relative; margin-bottom: 4px; padding: 0 20px; }
  .stepper::before { content: ''; position: absolute; top: 16px; left: 50px; right: 50px; height: 2px; background: var(--border-color); z-index: 1; }
  .step { position: relative; z-index: 2; padding: 0 10px; text-align: center; flex: 1; transition: 0.3s; }
  .step-circle { width: 34px; height: 34px; border-radius: 50%; background: var(--card-bg); border: 2px solid var(--border-color); margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: bold; color: var(--text-muted); transition: 0.4s; }
  .step-circle svg { width: 17px; height: 17px; }
  .step-label { font-size: 12px; font-weight: 600; color: var(--text-muted); }
  .step.completed .step-circle { border-color: var(--primary-color); background: var(--primary-color); color: #fff; }
  .status-review .step.active .step-circle { border-color: var(--secondary-color); background: var(--secondary-color); color: #fff; box-shadow: 0 0 0 4px rgba(248,162,39,0.2); }
  .status-review .step.active .step-label { color: var(--secondary-color); font-weight: 800; }
  .status-rejected .step.active .step-circle { border-color: var(--danger); background: var(--danger); color: #fff; box-shadow: 0 0 0 4px rgba(231,74,59,0.2); }
  .status-rejected .step.active .step-label { color: var(--danger); font-weight: 800; }
  .status-published .step.active .step-circle { border-color: var(--primary-color); background: var(--primary-color); color: #fff; }
  .status-published .step.active .step-label { color: var(--primary-color); font-weight: 800; }
  .status-scheduled .step.active .step-circle { border-color: var(--primary-color); background: var(--primary-color); color: #fff; box-shadow: 0 0 0 4px rgba(0, 137, 123, 0.2); }
  .status-scheduled .step.active .step-label { color: var(--primary-color); font-weight: 800; }

  /* ===== دکمه‌های اکشن ردیف ===== */
  .minimal-actions { display: flex; gap: 8px; flex-wrap: wrap; }
  .btn-min { padding: 9px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: var(--card-bg); font-size: 13px; font-weight: 600; color: var(--primary-color); cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: 0.2s; text-decoration: none; font-family: inherit; }
  .btn-min svg { width: 15px; height: 15px; }
  .btn-min:hover { background: var(--row-hover); border-color: var(--primary-color); transform: translateY(-1px); }
  .btn-min.is-danger { color: var(--danger); }
  .btn-min.is-danger:hover { border-color: var(--danger); }
  .btn-min.is-muted { color: var(--text-muted); }
  .btn-min.is-disabled { opacity: .7; cursor: default; }
  .btn-min.is-disabled:hover { transform: none; background: var(--card-bg); border-color: var(--border-color); }

  .reject-box { background: var(--st-rejected-bg); padding: 14px 16px; border-radius: var(--radius-sm); font-size: 13px; line-height: 1.7; color: var(--text-main); border: 1px solid var(--st-rejected-border); border-right: 4px solid var(--danger); display: flex; align-items: flex-start; gap: 8px; }
  .reject-box svg { width: 17px; height: 17px; flex-shrink: 0; color: var(--danger); margin-top: 2px; }

  /* ===== Modal ===== */
  .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 1000; opacity: 0; transition: 0.3s; padding: 16px; }
  .modal-overlay.show { display: flex; opacity: 1; }
  .modal-box { background: var(--card-bg); padding: 26px; border-radius: var(--radius); width: 100%; max-width: 420px; transform: translateY(-20px); transition: 0.3s; color: var(--text-main); box-shadow: var(--shadow-md); }
  .modal-overlay.show .modal-box { transform: translateY(0); }
  .modal-box h3 { margin-top: 0; color: var(--primary-color); font-size: 18px; }
  .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
  .modal-btn { padding: 10px 20px; border-radius: 999px; border: none; cursor: pointer; font-weight: 700; font-size: 14px; font-family: inherit; }
  .modal-input { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); outline: none; margin-top: 15px; font-size: 14px; background: var(--surface-2); color: var(--text-main); font-family: inherit; }
  .modal-input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(0,125,117,0.14); }

  /* ===== انتخاب گروهی ===== */
  .title-area { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
  .select-col { display: none; width: 52px; text-align: center !important; }
  body.select-mode .select-col { display: table-cell; }
  .news-checkbox, #selectAllCheckbox { width: 18px; height: 18px; accent-color: var(--primary-color); cursor: pointer; }

  .bulk-actions-bar { background: var(--card-bg); border: 1px solid var(--border-color); border-right: 5px solid var(--primary-color); border-radius: var(--radius); padding: 14px 18px; display: none; align-items: center; justify-content: space-between; gap: 15px; box-shadow: var(--shadow-sm); margin-bottom: 20px; }
  .bulk-actions-bar.show { display: flex; }
  .bulk-info { color: var(--text-main); font-size: 14px; font-weight: 700; }
  .bulk-info #selectedCount { color: var(--primary-color); font-size: 18px; margin-left: 5px; }
  .bulk-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
  .btn-bulk { border: none; padding: 10px 16px; border-radius: 999px; cursor: pointer; font-size: 13px; font-weight: 700; transition: 0.2s; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-family: inherit; }
  .btn-bulk svg { width: 15px; height: 15px; }
  .btn-bulk:hover { transform: translateY(-1px); opacity: 0.92; }
  .btn-bulk-publish { background: var(--primary-color); }
  .btn-bulk-delete { background: var(--danger); }
  .btn-bulk-cancel { background: var(--text-muted); }

  body.select-mode .main-row td { cursor: default; }
  body.select-mode .main-row:hover { background: transparent; }
  .main-row.selected { background: rgba(0, 128, 117, 0.08) !important; }
  [data-theme="dark"] .main-row.selected { background: rgba(0, 128, 117, 0.18) !important; }
  body.select-mode .main-row.selected:hover { background: rgba(0, 128, 117, 0.12) !important; }

  .empty-state { text-align: center; padding: 50px 20px; color: var(--text-muted); }
  .empty-state svg { width: 54px; height: 54px; opacity: .5; margin-bottom: 12px; }
  .empty-state p { margin: 0; font-size: 15px; }

  /* ===== ریسپانسیو: تبدیل جدول به کارت در موبایل ===== */
  @media (max-width: 700px) {
      .page-head { padding: 12px 16px; }
      .ph-actions { width: 100%; }
      .ph-actions .btn-new { flex: 1; }
      .bulk-actions-bar { flex-direction: column; align-items: stretch; }
      .bulk-buttons { width: 100%; }
      .btn-bulk { flex: 1; justify-content: center; }

      .news-table thead { display: none; }
      .news-table, .news-table tbody, .news-table tr, .news-table td { display: block; width: 100%; }
      .main-row { border: 1px solid var(--border-color); border-radius: var(--radius-sm); margin-bottom: 12px; padding: 6px 4px; background: var(--card-bg); }
      .main-row:last-child td { border-bottom: 1px solid var(--border-color); }
      .main-row td { border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 11px 14px; text-align: left; }
      .main-row td:last-child { border-bottom: none; }
      .main-row td::before { content: attr(data-label); font-weight: 700; color: var(--text-muted); font-size: 12px; flex-shrink: 0; }
      .main-row td.cell-title { flex-direction: column; align-items: flex-start; }
      .main-row td.cell-title::before { margin-bottom: 4px; }
      .main-row td.cell-date { direction: ltr; justify-content: flex-start; }
      .select-col { width: auto; }
      body.select-mode .select-col { display: flex; }
      /* JS ردیفِ جزییات را با display:table-row باز می‌کند؛ در چیدمانِ کارتیِ موبایل به block تبدیلش کن */
      .detail-row[style*="table-row"] { display: block !important; }
      .detail-row td { padding: 0; border: none; }
      .detail-row td::before { display: none; }
      .detail-container { padding: 18px; }
      .stepper::before { display: none; }
      .stepper { flex-direction: column; gap: 14px; padding: 0; }
      .step { display: flex; align-items: center; text-align: right; gap: 14px; padding: 0; }
      .step-circle { margin: 0; }
      .minimal-actions .btn-min { flex: 1; justify-content: center; }
  }
</style>
</head>
<body>

<div class="page-head">
    <div class="ph-title">
        <span class="ph-ic">
            <?php if ($__reviewOnly): ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <?php else: ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8z"/></svg>
            <?php endif; ?>
        </span>
        <div>
            <h1><?= $__reviewOnly ? 'بررسی سردبیری' : 'مدیریت اخبار' ?></h1>
            <p><?= $__reviewOnly ? 'اخبار ارسال‌شده در صف بررسی' : 'فهرست و وضعیت اخبار' ?></p>
        </div>
    </div>

    <div class="ph-actions">
        <button id="selectModeBtn" class="btn btn-ghost" onclick="enterSelectMode()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            انتخاب اخبار
        </button>
        <a href="news-create.php" class="btn btn-new">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            ساخت خبر جدید
        </a>
    </div>
</div>

<div class="wrap">

<div id="bulkActionsBar" class="bulk-actions-bar">
    <div class="bulk-info">
        <span id="selectedCount"> 0 </span>
        خبر انتخاب شده
    </div>

    <div class="bulk-buttons">
        <button onclick="bulkPublish()" class="btn-bulk btn-bulk-publish">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            انتشار انتخاب‌شده‌ها
        </button>

        <button onclick="bulkDelete()" class="btn-bulk btn-bulk-delete">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            حذف انتخاب‌شده‌ها
        </button>

        <button onclick="exitSelectMode()" class="btn-bulk btn-bulk-cancel">انصراف</button>
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
          <td class="select-col" data-label="انتخاب">
                <input
                    type="checkbox"
                    class="news-checkbox"
                    value="<?= $news['id'] ?>"
                    onclick="handleCheckboxClick(event, this)"
                >
           </td>

          <td data-label="وضعیت">
            <?php
              $clock = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
              $check = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
              $eye   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
              $cross = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
              $doc   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';
              if($is_scheduled) echo '<span class="st-badge st-scheduled">'.$clock.'زمان‌بندی شده</span>';
              elseif($st == 'published') echo '<span class="st-badge st-published">'.$check.'منتشر شده</span>';
              elseif($st == 'review') echo '<span class="st-badge st-review">'.$eye.'در حال بررسی</span>';
              elseif($st == 'rejected') echo '<span class="st-badge st-rejected">'.$cross.'عدم تایید</span>';
              else echo '<span class="st-badge st-draft">'.$doc.'ذخیره شده</span>';
            ?>
          </td>
          <td class="cell-title" data-label="عنوان خبر"><span class="news-title-text"><?= htmlspecialchars($news['title']) ?></span><?php if ($__isEditor && !empty($news['branch_name'])): ?><span class="branch-chip">🏢 <?= htmlspecialchars($news['branch_name']) ?></span><?php endif; ?></td>
          <td class="cell-date" data-label="تاریخ انتشار" style="color: var(--text-muted); font-size: 13px; direction: ltr;"><?= faNumbers(formatJalaliDateTime($news['publish_date'] ?? null)) ?></td>
          <td data-label="جزییات" style="text-align: center;"><span class="arrow-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span></td>
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
                  <div class="step-circle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
                  <div class="step-label">عدم تایید</div>
                </div>
                <?php else: ?>
                <div class="step <?= ($st == 'published') ? 'active' : '' ?>">
                  <div class="step-circle"><?php if($is_scheduled): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><?php else: ?>۳<?php endif; ?></div>
                  <div class="step-label"><?= $is_scheduled ? 'در صف انتشار' : 'انتشار نهایی' ?></div>
                </div>
                <?php endif; ?>
              </div>

                <?php if($st == 'rejected' && $reject_reason !== ''): ?>
                <div class="reject-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span><strong>علت عدم تایید:</strong> <?= htmlspecialchars($reject_reason) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($news['subtitle'])): ?>
                <div style="font-size: 13px; color: var(--text-muted); border-right: 3px solid var(--primary-color); padding: 2px 10px; margin: 4px 0 12px 0; line-height: 1.6;">
                    <strong>زیرعنوان / خلاصه خبر:</strong> <?= htmlspecialchars($news['subtitle']) ?>
                </div>
                <?php endif; ?>

              <div class="minimal-actions">
                <?php $slug = slugify($news['title']); ?>
                <a href="/<?= $news['id'] ?>/<?= $slug ?>/" target="_blank" class="btn-min">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  مشاهده
                </a>
                <a href="news-create.php?id=<?= $news['id'] ?>" class="btn-min">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  ویرایش
                </a>
                <?php if (!$__isEditor): ?>
                  <?php /* نویسنده: ارسال به سردبیر فقط وقتی پیش‌نویس یا ردشده است */ ?>
                  <?php if ($st === 'draft' || $st === 'rejected'): ?>
                    <button onclick="updateStatus(<?= $news['id'] ?>, 'review')" class="btn-min">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                      ارسال به سردبیر
                    </button>
                  <?php elseif ($st === 'review'): ?>
                    <span class="btn-min is-disabled is-muted">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                      در انتظار سردبیر
                    </span>
                  <?php endif; ?>
                <?php else: ?>
                  <?php /* سردبیر/ادمین ستاد: تایید و انتشار یا عدم تایید */ ?>
                  <?php if ($st !== 'published'): ?>
                    <button onclick="updateStatus(<?= $news['id'] ?>, 'published')" class="btn-min">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                      تایید و انتشار
                    </button>
                  <?php endif; ?>
                  <?php if ($st !== 'rejected'): ?>
                    <button onclick="confirmReject(<?= $news['id'] ?>)" class="btn-min is-danger">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                      عدم تایید
                    </button>
                  <?php endif; ?>
                <?php endif; ?>
                <button onclick="confirmDelete(<?= $news['id'] ?>)" class="btn-min is-muted">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  حذف
                </button>
              </div>

            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($news_list)): ?>
        <tr>
          <td colspan="5">
            <div class="empty-state">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              <p><?= $__reviewOnly ? 'خبری در صف بررسی سردبیری نیست.' : 'هنوز خبری ثبت نشده است.' ?></p>
            </div>
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</div><!-- /.wrap -->

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
const NEWS_CSRF = <?= json_encode(csrf_token()) ?>;
function updateStatus(id, st, reason = '') {
    const fd = new FormData();
    fd.append('id', id);
    fd.append('status', st);
    fd.append('reason', reason);
    fd.append('csrf_token', NEWS_CSRF);

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
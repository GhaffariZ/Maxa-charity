<?php
// --- Database Configuration ---
$servername = "localhost";
$username   = "erfantey_fantasticfour";
$password   = "Diamond19971376@macsa";
$dbname     = "erfantey_macsacharity";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("خطا در اتصال به دیتابیس: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

/*
 * پیش‌نیاز: اگر ستون is_active در جدول وجود ندارد، آن را با دستور زیر اضافه کنید:
 * ALTER TABLE employee_profiles ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1;
 */

// بررسی هوشمندانه وجود یا عدم وجود ستون is_active در دیتابیس برای جلوگیری از خطای SQL
$column_check = $conn->query("SHOW COLUMNS FROM `employee_profiles` LIKE 'is_active'");
$has_is_active = ($column_check && $column_check->num_rows > 0);

if ($has_is_active) {
    $sql = "SELECT id, fullname, branch, role, job_category, profile_pic, is_active
            FROM employee_profiles
            ORDER BY is_active DESC, id DESC";
} else {
    $sql = "SELECT id, fullname, branch, role, job_category, profile_pic
            FROM employee_profiles
            ORDER BY id DESC";
}
$result = $conn->query($sql);

$employees      = [];
$total          = 0;
$active_count   = 0;
$inactive_count = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!$has_is_active) {
            $row['is_active'] = 1; // مقدار پیش‌فرض اگر ستون در دیتابیس نباشد
        }
        $employees[] = $row;
        $total++;
        $row['is_active'] ? $active_count++ : $inactive_count++;
    }
}
$conn->close();

$branches = [
    'tehran'        => 'شعبه تهران',
    'isfahan'       => 'شعبه اصفهان',
    'mashhad'       => 'شعبه مشهد',
    'qom'           => 'شعبه قم',
    'tabriz'        => 'شعبه تبریز',
    'ahvaz'         => 'شعبه اهواز',
    'kerman'        => 'شعبه کرمان',
    'kashan'        => 'شعبه کاشان',
    'telemedicine'  => 'پزشکی از راه دور مکسا',
    'setad_markazi' => 'ستاد مرکزی',
];

$treatment_roles = [
    'administrative'     => 'سمت‌های اداری',
    'doctors'            => 'پزشک متخصص',
    'nurses'             => 'کادر پرستاری',
    'psychologists'      => 'روانشناس سلامت',
    'social_workers'     => 'مددکار اجتماعی',
    'spiritual_care'     => 'مراقب معنوی',
    'nutritionists'      => 'متخصص تغذیه',
    'rehabilitation'     => 'متخصص توانبخشی',
    'genetic_counselors' => 'مشاور ژنتیک و غربالگری',
    'deputies'           => 'معاونت',
];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت | شبکه همکاران مکسا</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">

    <style>
    /* ===================================================
       CSS Variables
    =================================================== */
    :root {
        --bg-global:        #f0f4f3;
        --bg-surface:       #ffffff;
        --text-main:        #1e293b;
        --text-muted:       #64748b;
        --border-color:     #e2e8f0;

        --brand-teal:       #0d9488;
        --brand-teal-light: #ccfbf1;
        --brand-teal-glow:  rgba(13, 148, 136, 0.15);
        --brand-gold:       #d97706;
        --brand-gold-glow:  rgba(217, 119, 6, 0.10);
        --danger:           #ef4444;
        --danger-glow:      rgba(239, 68, 68, 0.12);
        --danger-bg:        rgba(239, 68, 68, 0.07);
        --success:          #22c55e;
        --success-glow:     rgba(34, 197, 94, 0.12);
        --success-bg:       rgba(34, 197, 94, 0.07);

        --card-shadow:      0 4px 16px -4px rgba(0,0,0,0.05), 0 1px 4px rgba(0,0,0,0.03);
        --card-shadow-hover:0 20px 40px -12px rgba(13,148,136,0.22);
        --ease-premium:     cubic-bezier(0.25, 1, 0.5, 1);
    }

    [data-theme="dark"] {
        --bg-global:        #0f172a;
        --bg-surface:       #1e293b;
        --text-main:        #f1f5f9;
        --text-muted:       #94a3b8;
        --border-color:     #334155;
        --brand-teal-light: rgba(13, 148, 136, 0.18);
        --card-shadow:      0 10px 30px -10px rgba(0,0,0,0.3);
        --card-shadow-hover:0 25px 50px -12px rgba(13,148,136,0.38);
        --danger-bg:        rgba(239, 68, 68, 0.12);
        --success-bg:       rgba(34, 197, 94, 0.10);
    }

    * {
        box-sizing: border-box;
        transition: background-color 0.4s var(--ease-premium), border-color 0.4s var(--ease-premium);
    }

    body {
        font-family: 'Vazirmatn', sans-serif;
        background-color: var(--bg-global);
        color: var(--text-main);
        margin: 0; padding: 0;
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* ===================================================
       Header
    =================================================== */
    .premium-header {
        position: sticky; top: 0; z-index: 100;
        background: var(--bg-surface);
        border-bottom: 1px solid var(--border-color);
        padding: 13px 36px;
        display: flex; justify-content: space-between; align-items: center;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .header-left  { display: flex; align-items: center; gap: 14px; }
    .header-right { display: flex; align-items: center; gap: 10px; }

    .brand-section { display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit; }
    .brand-dot {
        width: 11px; height: 11px;
        background: linear-gradient(135deg, var(--brand-teal), #2dd4bf);
        border-radius: 50%;
        box-shadow: 0 0 10px var(--brand-teal);
    }
    .header-title { margin: 0; font-size: 1.1rem; font-weight: 800; }

    /* Admin badge */
    .admin-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #cbd5e1; padding: 5px 12px; border-radius: 20px;
        font-size: 11.5px; font-weight: 700; letter-spacing: 0.3px;
        border: 1px solid rgba(255,255,255,0.06);
    }
    [data-theme="dark"] .admin-badge { background: linear-gradient(135deg, #334155, #475569); }
    .admin-badge svg { width: 12px; height: 12px; fill: var(--brand-teal); }

    /* Add employee button */
    .btn-add-employee {
        display: inline-flex; align-items: center; gap: 7px;
        background: var(--brand-teal); color: #fff;
        border: none; padding: 9px 18px; border-radius: 12px;
        font-family: 'Vazirmatn', sans-serif; font-size: 13px; font-weight: 700;
        cursor: pointer; text-decoration: none;
        transition: background 0.25s, transform 0.3s var(--ease-premium), box-shadow 0.3s;
    }
    .btn-add-employee:hover { background: #0f766e; transform: translateY(-2px); box-shadow: 0 6px 20px var(--brand-teal-glow); }
    .btn-add-employee svg { width: 16px; height: 16px; fill: currentColor; }

    /* Theme toggle */
    .theme-switch-wrapper {
        background: var(--bg-global); border: 1px solid var(--border-color);
        padding: 4px; border-radius: 30px; cursor: pointer;
        display: flex; align-items: center; gap: 4px;
    }
    .theme-btn-icon { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
    .theme-switch-wrapper[data-active="light"] .light-icon,
    .theme-switch-wrapper[data-active="dark"]  .dark-icon {
        color: var(--brand-teal); background: var(--bg-surface);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 50%;
    }
    .theme-btn-icon svg { width: 17px; height: 17px; fill: currentColor; }

    /* ===================================================
       Main Wrapper
    =================================================== */
    .main-wrapper { max-width: 1320px; margin: 0 auto; padding: 28px 24px; }

    /* ===================================================
       Stats Bar
    =================================================== */
    .stats-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }

    .stat-chip {
        display: flex; align-items: center; gap: 8px;
        background: var(--bg-surface); border: 1px solid var(--border-color);
        padding: 8px 16px; border-radius: 12px;
        font-size: 13px; font-weight: 600;
        box-shadow: var(--card-shadow);
    }
    .stat-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .stat-chip.total   .stat-dot { background: var(--brand-teal); }
    .stat-chip.active  .stat-dot { background: var(--success); }
    .stat-chip.inactive .stat-dot { background: var(--danger); }
    .stat-num   { font-weight: 900; font-size: 15px; color: var(--text-main); }
    .stat-label { color: var(--text-muted); }

    /* ===================================================
       Controls Row
    =================================================== */
    .controls-row { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }

    /* Search */
    .search-wrapper { flex: 1; max-width: 300px; position: relative; }
    .search-input {
        width: 100%; padding: 10px 16px 10px 40px;
        background: var(--bg-surface); border: 1px solid var(--border-color);
        border-radius: 14px; font-family: 'Vazirmatn', sans-serif; font-size: 13.5px;
        color: var(--text-main); outline: none; box-shadow: var(--card-shadow);
        transition: border-color 0.3s var(--ease-premium), box-shadow 0.3s var(--ease-premium);
    }
    .search-input::placeholder { color: var(--text-muted); }
    .search-input:focus { border-color: var(--brand-teal); box-shadow: 0 0 0 3px var(--brand-teal-glow); }
    .search-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none; }
    .search-icon svg { width: 17px; height: 17px; fill: currentColor; display: block; }

    /* Filter button */
    .filter-trigger-btn {
        display: flex; align-items: center; gap: 8px;
        background: var(--bg-surface); border: 1px solid var(--border-color);
        padding: 10px 20px; border-radius: 14px;
        font-family: 'Vazirmatn', sans-serif; font-size: 13.5px; font-weight: 700;
        color: var(--text-main); cursor: pointer; box-shadow: var(--card-shadow);
        transition: border-color 0.3s var(--ease-premium), color 0.3s, transform 0.3s var(--ease-premium);
    }
    .filter-trigger-btn:hover { border-color: var(--brand-teal); color: var(--brand-teal); transform: translateY(-2px); }
    .filter-trigger-btn svg { width: 16px; height: 16px; fill: currentColor; }

    /* ===================================================
       Grid
    =================================================== */
    .premium-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 28px;
        transition: all 0.4s var(--ease-premium);
    }

    /* ===================================================
       Card
    =================================================== */
    .modern-card {
        background: var(--bg-surface); border: 1px solid var(--border-color);
        border-radius: 24px; overflow: hidden;
        box-shadow: var(--card-shadow);
        display: flex; flex-direction: column;
        position: relative; cursor: pointer;
        transition:
            transform 0.5s var(--ease-premium),
            box-shadow 0.5s var(--ease-premium),
            border-color 0.5s var(--ease-premium),
            filter 0.5s var(--ease-premium),
            opacity 0.5s var(--ease-premium);
        animation: cardEntrance 0.65s cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    .modern-card:not(.is-active):hover {
        transform: translateY(-7px);
        box-shadow: var(--card-shadow-hover);
        border-color: var(--brand-teal);
    }

    .premium-grid.has-active .modern-card:not(.is-active) {
        filter: blur(5px) grayscale(15%);
        opacity: 0.30;
        transform: scale(0.95);
        pointer-events: none;
    }

    .modern-card.is-active {
        transform: translateY(0) scale(1.04) !important;
        box-shadow: 0 28px 56px -14px rgba(0,0,0,0.14) !important;
        border-color: var(--brand-teal) !important;
        z-index: 10;
    }

    /* Inactive card */
    .card-inactive { opacity: 0.72; }
    .card-inactive .image-wrapper::after {
        content: '';
        position: absolute; inset: 0;
        background: rgba(239, 68, 68, 0.06);
        pointer-events: none;
    }

    /* Status dot (top-right of image) */
    .status-dot {
        position: absolute; top: 11px; right: 11px; z-index: 3;
        width: 10px; height: 10px; border-radius: 50%;
        border: 2px solid var(--bg-surface);
        box-shadow: 0 1px 6px rgba(0,0,0,0.18);
    }
    .status-dot.online  { background: var(--success); }
    .status-dot.offline { background: var(--danger); }

    /* Inactive pill (top-left of image) */
    .inactive-badge {
        position: absolute; top: 10px; left: 10px; z-index: 3;
        background: rgba(239, 68, 68, 0.88); color: #fff;
        font-size: 9.5px; font-weight: 800; padding: 3px 8px; border-radius: 20px;
        letter-spacing: 0.3px; backdrop-filter: blur(4px);
    }

    /* Image area */
    .image-wrapper { width: 100%; height: 280px; overflow: hidden; position: relative; background: var(--bg-global); }
    .card-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s var(--ease-premium); }
    .modern-card:hover .card-img { transform: scale(1.06); }
    .avatar-placeholder { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted); gap: 8px; }
    .avatar-placeholder .icon { font-size: 54px; }

    /* Card info */
    .card-info-box { padding: 22px 18px 14px; display: flex; flex-direction: column; flex-grow: 1; text-align: center; }
    .employee-name { margin: 0 0 14px; font-size: 1.1rem; font-weight: 800; color: var(--text-main); }
    .badges-stack { display: flex; flex-direction: column; gap: 8px; }

    .pill-badge { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 12px; border-radius: 12px; font-size: 13px; font-weight: 700; border: 1px solid transparent; }
    .pill-branch { background: var(--bg-global); color: var(--text-main); border-color: var(--border-color); }
    .pill-branch .dot { background: var(--brand-teal); }
    .pill-role   { background: var(--brand-gold-glow); color: var(--brand-gold); }
    .pill-role   .dot { background: var(--brand-gold); }
    .pill-badge .dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .pill-badge .value-text { max-width: 100%; }
    .pill-branch .value-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pill-role   .value-text { white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-align: center; }

    /* ===================================================
       Admin Action Drawer (two-button)
    =================================================== */
    .action-drawer {
        max-height: 0; opacity: 0; overflow: hidden;
        display: flex; gap: 8px; padding: 0 16px;
        background: rgba(0,0,0,0.012);
        border-top: 1px solid transparent;
        transition:
            max-height 0.42s var(--ease-premium),
            opacity    0.30s var(--ease-premium),
            padding    0.42s var(--ease-premium),
            border-color 0.40s var(--ease-premium);
    }
    [data-theme="dark"] .action-drawer { background: rgba(255,255,255,0.018); }

    .modern-card.is-active .action-drawer {
        max-height: 88px; opacity: 1;
        padding: 12px 16px; border-color: var(--border-color);
    }

    /* Shared admin button base */
    .btn-admin {
        flex: 1; display: flex; align-items: center; justify-content: center; gap: 7px;
        padding: 11px 8px; border-radius: 12px;
        font-size: 13px; font-weight: 700;
        font-family: 'Vazirmatn', sans-serif;
        border: 1px solid transparent; cursor: pointer;
        transition: background 0.25s var(--ease-premium), color 0.25s, transform 0.3s var(--ease-premium), box-shadow 0.3s;
    }
    .btn-admin svg { width: 15px; height: 15px; fill: currentColor; flex-shrink: 0; }

    /* Edit — teal ghost */
    .btn-edit {
        background: var(--brand-teal-glow); color: var(--brand-teal);
        border-color: rgba(13,148,136,0.22);
    }
    .btn-edit:hover { background: var(--brand-teal); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 16px var(--brand-teal-glow); }

    /* Deactivate — danger ghost */
    .btn-deactivate {
        background: var(--danger-bg); color: var(--danger);
        border-color: rgba(239,68,68,0.20);
    }
    .btn-deactivate:hover { background: var(--danger); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 16px var(--danger-glow); }

    /* Activate — success ghost */
    .btn-activate {
        background: var(--success-bg); color: var(--success);
        border-color: rgba(34,197,94,0.22);
    }
    .btn-activate:hover { background: var(--success); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 16px var(--success-glow); }

    /* ===================================================
       Filter Sidebar
    =================================================== */
    .filter-sidebar {
        position: fixed; top: 0; right: -370px; width: 340px; height: 100%;
        background: var(--bg-surface); z-index: 200;
        box-shadow: -8px 0 32px rgba(0,0,0,0.07);
        display: flex; flex-direction: column;
        transition: right 0.4s var(--ease-premium);
    }
    [data-theme="dark"] .filter-sidebar { box-shadow: -8px 0 32px rgba(0,0,0,0.35); }
    .filter-sidebar.is-open { right: 0; }

    .drawer-header { padding: 22px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
    .drawer-header h3 { margin: 0; font-size: 1.1rem; font-weight: 800; }
    .close-drawer-btn { background: none; border: none; font-size: 22px; line-height: 1; color: var(--text-muted); cursor: pointer; padding: 4px; transition: color 0.2s; }
    .close-drawer-btn:hover { color: var(--danger); }

    .drawer-body { padding: 24px; flex-grow: 1; display: flex; flex-direction: column; gap: 22px; overflow-y: auto; }
    .filter-group { display: flex; flex-direction: column; gap: 9px; }
    .filter-group > label { font-size: 12.5px; font-weight: 700; color: var(--text-muted); }

    .filter-select {
        width: 100%; padding: 11px 12px 11px 34px; border-radius: 12px;
        border: 1px solid var(--border-color);
        background: var(--bg-global); color: var(--text-main);
        font-family: 'Vazirmatn', sans-serif; font-size: 13px; font-weight: 600;
        outline: none; cursor: pointer;
        -webkit-appearance: none; -moz-appearance: none; appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="%230d9488" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>');
        background-repeat: no-repeat; background-position: left 12px center;
        transition: border-color 0.25s;
    }
    .filter-select:focus { border-color: var(--brand-teal); }

    /* Status filter row */
    .status-filter-row { display: flex; gap: 8px; }
    .status-filter-btn {
        flex: 1; padding: 9px 8px; border-radius: 10px;
        border: 1px solid var(--border-color); background: var(--bg-global);
        color: var(--text-muted); font-family: 'Vazirmatn', sans-serif;
        font-size: 12px; font-weight: 700; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        transition: all 0.25s var(--ease-premium);
    }
    .status-filter-btn .s-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
    .status-filter-btn.sf-all.sel  { background: var(--brand-teal); color: #fff; border-color: var(--brand-teal); }
    .status-filter-btn.sf-active   .s-dot { background: var(--success); }
    .status-filter-btn.sf-active.sel   { background: var(--success-bg); color: var(--success); border-color: var(--success); }
    .status-filter-btn.sf-inactive .s-dot { background: var(--danger); }
    .status-filter-btn.sf-inactive.sel { background: var(--danger-bg); color: var(--danger); border-color: var(--danger); }

    .drawer-footer { padding: 20px 24px; border-top: 1px solid var(--border-color); display: flex; gap: 10px; }
    .btn-apply { flex: 2; background: var(--brand-teal); color: #fff; border: none; padding: 13px; border-radius: 12px; font-family: 'Vazirmatn', sans-serif; font-size: 14px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
    .btn-apply:hover { background: #0f766e; }
    .btn-reset { flex: 1; background: transparent; color: var(--text-muted); border: 1px solid var(--border-color); padding: 13px; border-radius: 12px; font-family: 'Vazirmatn', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-reset:hover { background: var(--danger-bg); color: var(--danger); border-color: rgba(239,68,68,0.2); }

    .drawer-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.22); backdrop-filter: blur(4px); z-index: 199; opacity: 0; pointer-events: none; transition: opacity 0.4s var(--ease-premium); }
    .drawer-overlay.is-active { opacity: 1; pointer-events: auto; }

    /* ===================================================
       Confirmation Modal
    =================================================== */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 500;
        display: flex; align-items: center; justify-content: center;
        background: rgba(15,23,42,0.50); backdrop-filter: blur(10px);
        opacity: 0; pointer-events: none;
        transition: opacity 0.3s var(--ease-premium);
    }
    .modal-overlay.visible { opacity: 1; pointer-events: auto; }

    .modal-box {
        background: var(--bg-surface); border: 1px solid var(--border-color);
        border-radius: 28px; padding: 36px 30px; width: 90%; max-width: 360px;
        box-shadow: 0 40px 80px -20px rgba(0,0,0,0.22);
        transform: scale(0.88) translateY(24px); opacity: 0;
        transition: transform 0.38s var(--ease-premium), opacity 0.30s var(--ease-premium);
    }
    .modal-overlay.visible .modal-box { transform: scale(1) translateY(0); opacity: 1; }

    .modal-icon-wrap {
        width: 58px; height: 58px; border-radius: 18px; margin: 0 auto 20px;
        display: flex; align-items: center; justify-content: center; font-size: 28px;
    }
    .modal-icon-wrap.danger  { background: var(--danger-bg); }
    .modal-icon-wrap.success { background: var(--success-bg); }

    .modal-title { margin: 0 0 8px; font-size: 1.1rem; font-weight: 800; text-align: center; }
    .modal-desc  { margin: 0 0 26px; font-size: 13.5px; color: var(--text-muted); text-align: center; line-height: 1.75; }
    .modal-name  { color: var(--text-main); font-weight: 800; }

    .modal-actions { display: flex; gap: 10px; }
    .btn-modal-cancel {
        flex: 1; padding: 12px; border-radius: 14px;
        background: var(--bg-global); border: 1px solid var(--border-color);
        color: var(--text-muted); font-family: 'Vazirmatn', sans-serif;
        font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s;
    }
    .btn-modal-cancel:hover { border-color: var(--text-muted); color: var(--text-main); }
    .btn-modal-confirm {
        flex: 1; padding: 12px; border-radius: 14px; border: none;
        font-family: 'Vazirmatn', sans-serif; font-size: 14px; font-weight: 700; cursor: pointer;
        transition: filter 0.2s, transform 0.2s, box-shadow 0.2s;
    }
    .btn-modal-confirm.danger  { background: var(--danger); color: #fff; }
    .btn-modal-confirm.danger:hover  { filter: brightness(1.08); box-shadow: 0 6px 18px var(--danger-glow); transform: translateY(-1px); }
    .btn-modal-confirm.success { background: var(--success); color: #fff; }
    .btn-modal-confirm.success:hover { filter: brightness(1.08); box-shadow: 0 6px 18px var(--success-glow); transform: translateY(-1px); }

    /* ===================================================
       Animation
    =================================================== */
    @keyframes cardEntrance {
        from { opacity: 0; transform: translateY(28px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ===================================================
       No-results message
    =================================================== */
    .no-results-msg {
        display: none; grid-column: 1/-1; text-align: center;
        padding: 60px 20px; color: var(--text-muted); font-weight: 700; font-size: 15px;
    }

    /* ===================================================
       Responsive — Mobile (≤600px)
    =================================================== */
    @media (max-width: 600px) {
        .premium-header  { padding: 11px 14px; }
        .header-title    { font-size: 0.9rem; }
        .admin-badge     { display: none; }
        .btn-add-employee .btn-label { display: none; }
        .btn-add-employee { padding: 8px 11px; border-radius: 10px; }
        .main-wrapper    { padding: 14px 8px; }
        .stats-bar       { gap: 7px; margin-bottom: 14px; }
        .stat-chip       { padding: 6px 10px; font-size: 11px; }
        .stat-num        { font-size: 13px; }
        .controls-row    { gap: 7px; margin-bottom: 16px; }
        .search-wrapper  { max-width: none; }
        .search-input    { padding: 8px 12px 8px 34px; font-size: 12.5px; border-radius: 10px; }
        .filter-trigger-btn { padding: 8px 13px; font-size: 12.5px; border-radius: 10px; }
        .filter-sidebar  { width: 285px; right: -305px; }

        .premium-grid   { grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .modern-card    { border-radius: 14px; }
        .image-wrapper  { height: 112px; }
        .avatar-placeholder .icon { font-size: 30px; }
        .card-info-box  { padding: 8px 4px 6px; }
        .employee-name  { font-size: 0.78rem; font-weight: 800; margin: 0 0 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .badges-stack   { gap: 3px; }
        .pill-branch    { padding: 3px 4px; border-radius: 6px; font-size: 8.5px; gap: 4px; }
        .pill-role      { padding: 3px 4px; border-radius: 6px; }
        .pill-role .value-text { white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-weight: 700; text-align: center; }
        .status-dot     { width: 7px; height: 7px; top: 6px; right: 6px; }
        .inactive-badge { font-size: 7.5px; padding: 2px 5px; top: 6px; left: 6px; }

        /* Mobile drawer */
        .modern-card.is-active .action-drawer { max-height: 58px; padding: 7px 5px; gap: 5px; }
        .btn-admin      { padding: 7px 3px; font-size: 9px; border-radius: 8px; gap: 3px; }
        .btn-admin svg  { width: 10px; height: 10px; }
        .modern-card.is-active { transform: scale(1.03) !important; }

        /* Modal */
        .modal-box      { padding: 28px 18px; border-radius: 22px; }
        .modal-title    { font-size: 1rem; }
    }
    </style>
</head>
<body>

<div class="main-wrapper">

    <div class="stats-bar">
        <div class="stat-chip total">
            <span class="stat-dot"></span>
            <span class="stat-num"><?php echo $total; ?></span>
            <span class="stat-label">کل همکاران</span>
        </div>
        <div class="stat-chip active">
            <span class="stat-dot"></span>
            <span class="stat-num"><?php echo $active_count; ?></span>
            <span class="stat-label">فعال</span>
        </div>
        <div class="stat-chip inactive">
            <span class="stat-dot"></span>
            <span class="stat-num"><?php echo $inactive_count; ?></span>
            <span class="stat-label">غیرفعال</span>
        </div>
        <a href="personal-resume-create.php" class="btn-add-employee" style="margin-inline-start:auto">
            <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            <span class="btn-label">افزودن همکار</span>
        </a>
    </div>

    <div class="controls-row">
        <div class="search-wrapper">
            <input type="text" id="search-input" class="search-input"
                   placeholder="جستجوی نام همکار…" autocomplete="off">
            <span class="search-icon">
                <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            </span>
        </div>
        <button id="filter-btn" class="filter-trigger-btn">
            <svg viewBox="0 0 24 24"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>
            فیلتر
        </button>
    </div>

    <div class="premium-grid" id="cards-grid">

        <div class="no-results-msg" id="no-results">همکاری با مشخصات انتخاب‌شده یافت نشد.</div>

        <?php foreach ($employees as $i => $row):
            $id          = (int)$row['id'];
            $fullname    = htmlspecialchars($row['fullname'] ?: 'همکار مکسا');
            $raw_branch  = $row['branch'];
            $raw_role    = $row['role'];
            $job_cat     = isset($row['job_category']) ? $row['job_category'] : '';

            // پیدا کردن کلید مناسب برای فیلتر بر اساس ساختار ترکیبی دیتابیس شما
            $filter_role = $raw_role;
            if (!isset($treatment_roles[$filter_role]) && isset($treatment_roles[$job_cat])) {
                $filter_role = $job_cat;
            }

            $is_active   = (int)$row['is_active'];
            $branch_text = isset($branches[$raw_branch]) ? $branches[$raw_branch] : 'نامشخص';
            
            // تعیین عنوان نمایشی سمت شغلی با توجه به ساختار متفاوت ردیف‌ها در دیتابیس
            if (isset($treatment_roles[$raw_role])) {
                $role_text = $treatment_roles[$raw_role];
            } elseif (!empty($raw_role) && !isset($treatment_roles[$raw_role])) {
                $role_text = $raw_role;
            } elseif (isset($treatment_roles[$job_cat])) {
                $role_text = $treatment_roles[$job_cat];
            } else {
                $role_text = 'ثبت نشده';
            }
            $role_text = htmlspecialchars($role_text);

            $card_class   = $is_active ? '' : 'card-inactive';
            $dot_class    = $is_active ? 'online' : 'offline';
            $new_status   = $is_active ? 0 : 1;
            $toggle_url   = "admin-toggle-employee-status.php?id={$id}&status={$new_status}";
            $edit_url     = "personal-resume-create.php?id={$id}";
            $delay        = round(($i % 20) * 0.055, 3);
        ?>
        <div class="modern-card <?php echo $card_class; ?>"
             style="animation-delay: <?php echo $delay; ?>s"
             data-branch="<?php echo htmlspecialchars($raw_branch); ?>"
             data-role="<?php echo htmlspecialchars($filter_role); ?>"
             data-is-active="<?php echo $is_active; ?>"
             data-id="<?php echo $id; ?>"
             data-name="<?php echo $fullname; ?>"
             data-search="<?php echo mb_strtolower($fullname); ?>">

            <div class="image-wrapper">
                <span class="status-dot <?php echo $dot_class; ?>"></span>
                <?php if (!$is_active): ?>
                    <span class="inactive-badge">غیرفعال</span>
                <?php endif; ?>
                <?php if (!empty($row['profile_pic'])): ?>
                    <img src="<?php echo htmlspecialchars($row['profile_pic']); ?>"
                         class="card-img" alt="<?php echo $fullname; ?>">
                <?php else: ?>
                    <div class="avatar-placeholder"><span class="icon">👤</span></div>
                <?php endif; ?>
            </div>

            <div class="card-info-box">
                <h2 class="employee-name" title="<?php echo $fullname; ?>"><?php echo $fullname; ?></h2>
                <div class="badges-stack">
                    <div class="pill-badge pill-branch" title="<?php echo $branch_text; ?>">
                        <span class="dot"></span>
                        <span class="value-text"><?php echo $branch_text; ?></span>
                    </div>
                    <div class="pill-badge pill-role" title="<?php echo $role_text; ?>">
                        <span class="dot"></span>
                        <span class="value-text"><?php echo $role_text; ?></span>
                    </div>
                </div>
            </div>

            <div class="action-drawer">
                <button class="btn-admin btn-edit"
                        onclick="event.stopPropagation(); window.location.href='<?php echo $edit_url; ?>';">
                    <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                    ویرایش
                </button>

                <?php if ($is_active): ?>
                <button class="btn-admin btn-deactivate"
                        data-action="deactivate"
                        data-url="<?php echo $toggle_url; ?>"
                        data-name="<?php echo $fullname; ?>"
                        onclick="event.stopPropagation(); openModal(this);">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11H7v-2h10v2z"/></svg>
                    غیرفعال کردن
                </button>
                <?php else: ?>
                <button class="btn-admin btn-activate"
                        data-action="activate"
                        data-url="<?php echo $toggle_url; ?>"
                        data-name="<?php echo $fullname; ?>"
                        onclick="event.stopPropagation(); openModal(this);">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                    فعال کردن
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</div>

<div id="filter-sidebar" class="filter-sidebar">
    <div class="drawer-header">
        <h3>فیلتر پیشرفته</h3>
        <button id="close-sidebar-btn" class="close-drawer-btn">&times;</button>
    </div>
    <div class="drawer-body">

        <div class="filter-group">
            <label>وضعیت</label>
            <div class="status-filter-row">
                <button class="status-filter-btn sf-all sel" data-sf="all">همه</button>
                <button class="status-filter-btn sf-active"  data-sf="1">
                    <span class="s-dot"></span> فعال
                </button>
                <button class="status-filter-btn sf-inactive" data-sf="0">
                    <span class="s-dot"></span> غیرفعال
                </button>
            </div>
        </div>

        <div class="filter-group">
            <label for="filter-branch">محل خدمت</label>
            <select id="filter-branch" class="filter-select">
                <option value="all">همه شعبه‌ها</option>
                <?php foreach ($branches as $k => $v): ?>
                    <option value="<?php echo htmlspecialchars($k); ?>"><?php echo htmlspecialchars($v); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group" id="filter-role-group">
            <label for="filter-role">سمت شغلی</label>
            <select id="filter-role" class="filter-select">
                <option value="all">همه سمت‌ها</option>
                <?php foreach ($treatment_roles as $k => $v): ?>
                    <option value="<?php echo htmlspecialchars($k); ?>"><?php echo htmlspecialchars($v); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="drawer-footer">
        <button id="apply-filter-btn" class="btn-apply">اعمال فیلتر</button>
        <button id="reset-filter-btn" class="btn-reset">حذف</button>
    </div>
</div>

<div id="drawer-overlay" class="drawer-overlay"></div>

<div id="confirm-modal" class="modal-overlay" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div id="modal-icon" class="modal-icon-wrap danger">⛔</div>
        <h3 id="modal-title" class="modal-title">غیرفعال کردن همکار</h3>
        <p  id="modal-desc"  class="modal-desc">
            آیا از غیرفعال کردن <span id="modal-name" class="modal-name">---</span> اطمینان دارید؟
        </p>
        <div class="modal-actions">
            <button id="modal-cancel"  class="btn-modal-cancel">لغو</button>
            <button id="modal-confirm" class="btn-modal-confirm danger">تأیید</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const grid          = document.getElementById('cards-grid');
    const cards         = document.querySelectorAll('.modern-card');
    const searchInput   = document.getElementById('search-input');
    const filterBtn     = document.getElementById('filter-btn');
    const filterSidebar = document.getElementById('filter-sidebar');
    const drawerOverlay = document.getElementById('drawer-overlay');
    const closeSidebarBtn = document.getElementById('close-sidebar-btn');
    const applyBtn      = document.getElementById('apply-filter-btn');
    const resetBtn      = document.getElementById('reset-filter-btn');
    const branchSelect  = document.getElementById('filter-branch');
    const roleSelect    = document.getElementById('filter-role');
    const roleGroup     = document.getElementById('filter-role-group');
    const noResults     = document.getElementById('no-results');
    const statusBtns    = document.querySelectorAll('.status-filter-btn');

    let activeStatusFilter = 'all';

    /* ---- Search ---- */
    searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        let visible = 0;
        cards.forEach(c => {
            const match = !q || (c.getAttribute('data-search') || '').includes(q);
            c.style.display = match ? '' : 'none';
            if (!match) c.classList.remove('is-active');
            if (match) visible++;
        });
        grid.classList.remove('has-active');
        noResults.style.display = visible === 0 ? 'block' : 'none';
    });

    /* ---- Role font adjustment (mobile) ---- */
    const adjustRoleFonts = () => {
        const isMobile = window.innerWidth <= 600;
        document.querySelectorAll('.pill-role .value-text').forEach(el => {
            if (!isMobile) { el.style.fontSize = '12.5px'; el.style.lineHeight = '1.35'; return; }
            const len = el.textContent.trim().length;
            el.style.fontSize   = len > 18 ? (len > 34 ? '6.8px' : '7.5px') : '9.5px';
            el.style.lineHeight = len > 18 ? '1.1'  : '1.2';
        });
    };
    adjustRoleFonts();
    window.addEventListener('resize', adjustRoleFonts);

    /* ---- Branch → hide role filter for ستاد مرکزی ---- */
    branchSelect.addEventListener('change', () => {
        const hide = branchSelect.value === 'setad_markazi';
        roleGroup.style.display = hide ? 'none' : 'flex';
        if (hide) roleSelect.value = 'all';
    });

    /* ---- Status filter pills ---- */
    statusBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            statusBtns.forEach(b => b.classList.remove('sel'));
            btn.classList.add('sel');
            activeStatusFilter = btn.getAttribute('data-sf');
        });
    });

    /* ---- Open / close sidebar ---- */
    const openSidebar = () => {
        filterSidebar.classList.add('is-open');
        drawerOverlay.classList.add('is-active');
    };
    const closeSidebar = () => {
        filterSidebar.classList.remove('is-open');
        drawerOverlay.classList.remove('is-active');
    };
    filterBtn.addEventListener('click', openSidebar);
    closeSidebarBtn.addEventListener('click', closeSidebar);
    drawerOverlay.addEventListener('click', closeSidebar);

    /* ---- Apply filter ---- */
    applyBtn.addEventListener('click', () => {
        const b = branchSelect.value;
        const r = roleSelect.value;
        let visible = 0;

        cards.forEach(c => {
            const okB = b === 'all' || c.getAttribute('data-branch') === b;
            const okR = r === 'all' || c.getAttribute('data-role')   === r;
            const okS = activeStatusFilter === 'all' || c.getAttribute('data-is-active') === activeStatusFilter;
            const show = okB && okR && okS;
            c.style.display = show ? '' : 'none';
            if (!show) c.classList.remove('is-active');
            if (show) visible++;
        });

        grid.classList.remove('has-active');
        noResults.style.display = visible === 0 ? 'block' : 'none';
        closeSidebar();
        setTimeout(adjustRoleFonts, 50);
    });

    /* ---- Reset filter ---- */
    resetBtn.addEventListener('click', () => {
        branchSelect.value = 'all';
        roleSelect.value   = 'all';
        roleGroup.style.display = 'flex';
        activeStatusFilter = 'all';
        statusBtns.forEach(b => b.classList.remove('sel'));
        document.querySelector('.status-filter-btn.sf-all').classList.add('sel');
        searchInput.value = '';
        cards.forEach(c => { c.style.display = ''; c.classList.remove('is-active'); });
        grid.classList.remove('has-active');
        noResults.style.display = 'none';
        closeSidebar();
        setTimeout(adjustRoleFonts, 50);
    });

    /* ---- Card click → open/close action drawer ---- */
    cards.forEach(card => {
        card.addEventListener('click', e => {
            e.stopPropagation();
            const wasActive = card.classList.contains('is-active');
            cards.forEach(c => c.classList.remove('is-active'));
            grid.classList.remove('has-active');
            if (!wasActive) {
                card.classList.add('is-active');
                grid.classList.add('has-active');
            }
        });
    });

    document.addEventListener('click', () => {
        cards.forEach(c => c.classList.remove('is-active'));
        grid.classList.remove('has-active');
    });

    /* ---- Theme toggle: کنترل از «داشبورد مدیریت» (کلید مشترک maxa-theme) ---- */
    var applyMaxaTheme = function(){
        var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
        if(d){ document.documentElement.setAttribute('data-theme','dark'); if(document.body) document.body.setAttribute('data-theme','dark'); }
        else { document.documentElement.removeAttribute('data-theme'); if(document.body) document.body.removeAttribute('data-theme'); }
    };
    applyMaxaTheme();
    window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });
});

/* ====================================================
   Confirmation Modal (global scope — called from onclick)
==================================================== */
let _pendingUrl = null;

function openModal(btn) {
    const action = btn.getAttribute('data-action'); // 'deactivate' | 'activate'
    const name   = btn.getAttribute('data-name');
    _pendingUrl  = btn.getAttribute('data-url');

    const modal   = document.getElementById('confirm-modal');
    const icon    = document.getElementById('modal-icon');
    const title   = document.getElementById('modal-title');
    const desc    = document.getElementById('modal-desc');
    const nameEl  = document.getElementById('modal-name');
    const confirm = document.getElementById('modal-confirm');

    if (action === 'deactivate') {
        icon.textContent  = '⛔';
        icon.className    = 'modal-icon-wrap danger';
        title.textContent = 'غیرفعال کردن همکار';
        confirm.className = 'btn-modal-confirm danger';
        confirm.textContent = 'غیرفعال کن';
        desc.innerHTML = `آیا از غیرفعال کردن <span class="modal-name">${name}</span> اطمینان دارید؟<br>دسترسی این همکار به سیستم قطع خواهد شد.`;
    } else {
        icon.textContent  = '✅';
        icon.className    = 'modal-icon-wrap success';
        title.textContent = 'فعال کردن همکار';
        confirm.className = 'btn-modal-confirm success';
        confirm.textContent = 'فعال کن';
        desc.innerHTML = `آیا از فعال‌سازی مجدد <span class="modal-name">${name}</span> اطمینان دارید؟<br>دسترسی این همکار بازیابی خواهد شد.`;
    }

    modal.classList.add('visible');
}

document.getElementById('modal-cancel').addEventListener('click', () => {
    document.getElementById('confirm-modal').classList.remove('visible');
    _pendingUrl = null;
});

document.getElementById('modal-confirm').addEventListener('click', () => {
    if (_pendingUrl) window.location.href = _pendingUrl;
});

document.getElementById('confirm-modal').addEventListener('click', e => {
    if (e.target === e.currentTarget) {
        e.currentTarget.classList.remove('visible');
        _pendingUrl = null;
    }
});
</script>

</body>
</html>
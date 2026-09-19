<?php
require_once __DIR__ . '/_guard.php';
dash_require('campaigns'); 
require_once __DIR__ . '/../core/campaign-service.php';

/** Resolve and authorize a campaign center for the current dashboard user. */
function campaign_status_allowed_branch(string $rawSlug): ?array
{
    $slug = campaign_service_normalize_slug($rawSlug);
    $activeBranch = dash_load_branch(dash_active_branch_id());
    if (!$activeBranch) {
        return null;
    }

    if (dash_is_super() && dash_is_hq_view()) {
        foreach (dash_all_branches() as $branch) {
            if ((string)$branch['slug'] === $slug) {
                return $branch;
            }
        }
        return null;
    }

    return (string)$activeBranch['slug'] === $slug ? $activeBranch : null;
}

// Campaign data is loaded asynchronously so the page shell and loader render
// immediately. Each branch is requested independently and requests can run in
// parallel after releasing PHP's session lock.
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    ini_set('display_errors', '0');

    try {
        $branch = campaign_status_allowed_branch((string)($_GET['branch'] ?? ''));
        if (!$branch) {
            http_response_code(403);
            throw new RuntimeException('دسترسی به این مرکز مجاز نیست.');
        }

        $client = new CampaignServiceClient((string)$branch['slug']);
        $client->prepareManagement();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $campaigns = [];
        foreach ($client->managementCampaigns() as $campaign) {
            $campaigns[] = campaign_service_present($campaign, $branch);
        }

        echo json_encode(['status' => 'success', 'data' => $campaigns], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch (Throwable $e) {
        if (http_response_code() < 400) {
            http_response_code(502);
        }
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    exit;
}

// عملیات تغییر وضعیت کمپین (فعال / غیرفعال سازی) بدون نیاز به alert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    csrf_check();
    $campaign_id = trim((string)$_POST['toggle_id']);
    $current_status = (int)$_POST['current_status'];
    $allowedBranch = campaign_status_allowed_branch((string)($_POST['branch_slug'] ?? ''));
    if (!$allowedBranch) {
        http_response_code(403);
        exit('دسترسی به این مرکز مجاز نیست.');
    }
    $allowedSlug = (string)$allowedBranch['slug'];

    (new CampaignServiceClient($allowedSlug))->updateCampaign($campaign_id, [
        'status' => $current_status === 1 ? 'INACTIVE' : 'ACTIVE',
    ]);
    dash_audit('campaign_status_changed', ['campaign_id' => $campaign_id, 'center' => $allowedSlug]);

    // رفرش صفحه برای اعمال تغییرات
    header("Location: campaign-status.php");
    exit;
}

// نمای مرکزی: منابع همه شعب؛ سایر کاربران فقط منبع شعبه خودشان را می‌بینند.
// داده واقعی بعد از نمایش صفحه و به‌صورت AJAX دریافت می‌شود.
$__branch = dash_active_branch_id();
$__row = dash_load_branch($__branch);
$branchesToLoad = ($__row && (int)$__row['is_hq'] === 1 && dash_is_super()) ? dash_all_branches() : array_filter([$__row]);
$campaignSources = array_values(array_map(static fn(array $branch): array => [
    'name' => (string)$branch['name'],
    'slug' => (string)$branch['slug'],
], $branchesToLoad));
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>لیست کمپین‌ها</title>
<!-- تم دارک/لایت از «داشبورد مدیریت» تبعیت می‌کند (کلید مشترک: maxa-theme) -->
<script>
(function(){
  function applyMaxaTheme(){
    var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
    if(d) document.documentElement.setAttribute('data-theme','dark'); else document.documentElement.removeAttribute('data-theme');
  }
  applyMaxaTheme();
  window.addEventListener('storage', function(e){ if(!e || e.key==='maxa-theme' || e.key===null) applyMaxaTheme(); });
})();
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    :root { 
        --primary-color: #007D75; 
        --secondary-color: #F79F1F; 
        --bg-color: #f4f7f6; 
        --card-bg: #ffffff; 
        --text-main: #333; 
        --text-muted: #777; 
        --border-light: rgba(0,0,0,0.06);
        --success-color: #2ed573;
        --danger-color: #ff4757;
    }
    [data-theme="dark"] { 
        --bg-color: #121212; 
        --card-bg: #1e1e1e; 
        --text-main: #eee; 
        --text-muted: #aaa; 
        --border-light: rgba(255,255,255,0.08);
    }

    body { 
        background-color: var(--bg-color); 
        color: var(--text-main); 
        font-family: Tahoma, sans-serif; 
        padding: 40px 20px; 
        margin: 0;
    }

    .container { max-width: 1200px; margin: 0 auto; }

    .page-header {
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--primary-color);
    }
    .page-header h2 { margin: 0; font-size: 22px; font-weight: bold; }

    .campaign-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
        gap: 30px; 
    }

    .card {
        background: var(--card-bg); 
        border-radius: 20px; 
        border: 1px solid var(--border-light);
        overflow: hidden; 
        cursor: pointer; 
        transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s, filter 0.4s, opacity 0.4s;
        display: flex; 
        flex-direction: column; 
        position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }

    /* استایل اختصاصی برای کمپین‌های غیرفعال (خاکستری شدن) */
    .card.disabled-campaign {
        filter: grayscale(1) contrast(0.9);
        opacity: 0.75;
    }

    .card-img-wrapper {
        position: relative;
        width: 100%;
        height: 210px;
        overflow: hidden;
        background: #eee;
    }

    .card-img { 
        width: 100%; 
        height: 100%; 
        object-fit: cover; 
        transition: transform 0.5s ease;
    }

    /* بج وضعیت فعال / غیرفعال */
    .status-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        color: white;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: bold;
        backdrop-filter: blur(5px);
        z-index: 2;
    }
    .status-badge.active { background: rgba(46, 213, 115, 0.9); }
    .status-badge.inactive { background: rgba(255, 71, 87, 0.9); }

    /* دکمه مدیریت وضعیت در بالای کارت */
    .toggle-status-form {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 5;
    }
    .btn-toggle-status {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        color: #333;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-family: Tahoma;
        cursor: pointer;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: 0.2s;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    [data-theme="dark"] .btn-toggle-status { background: rgba(30, 30, 30, 0.9); color: #fff; }
    .btn-toggle-status:hover { transform: scale(1.05); background: #fff; }
    [data-theme="dark"] .btn-toggle-status:hover { background: #2a2a2a; }

    .card-body { 
        padding: 24px; 
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .card-title { 
        margin: 0 0 12px 0; 
        color: var(--primary-color); 
        font-size: 18px; 
        font-weight: bold;
        line-height: 1.5;
    }

    .card-desc {
    font-size: 13px; 
    line-height: 1.8; 
    color: var(--text-muted);
    margin-bottom: 15px;
    position: relative;
    max-height: 48px; /* ارتفاع دقیق برای نشان دادن حدود ۲ خط */
    overflow: hidden;
    transition: max-height 0.5s cubic-bezier(0.25, 1, 0.5, 1); /* انیمیشن نرم روان */
}
.card-desc::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 25px;
    background: linear-gradient(transparent, var(--card-bg));
    transition: opacity 0.3s ease;
    pointer-events: none; /* جلوگیری از اختلال در کلیک */
}

    .progress-container {
        margin-top: auto; 
        padding-top: 15px;
    }

    .progress-bar { 
        height: 8px; 
        background: rgba(0,0,0,0.05); 
        border-radius: 10px; 
        margin: 10px 0; 
        overflow: hidden;
    }
    [data-theme="dark"] .progress-bar { background: rgba(255,255,255,0.1); }

    .progress-fill { 
        height: 100%; 
        background: linear-gradient(90deg, var(--secondary-color), #ffb94b); 
        border-radius: 10px; 
        width: 0;
        transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .funding-stats {
        display: flex; 
        justify-content: space-between; 
        font-size: 12px; 
        font-weight: bold;
    }

    .btn-support {
        width: 100%; 
        padding: 12px; 
        border: 2px solid var(--secondary-color);
        background: none; 
        color: var(--secondary-color); 
        font-weight: bold;
        border-radius: 12px; 
        cursor: pointer; 
        transition: 0.3s; 
        margin-top: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-support:hover { 
        background: var(--secondary-color); 
        color: white; 
        box-shadow: 0 5px 15px rgba(247, 159, 31, 0.3);
    }

    /* کمپین غیرفعال دکمه حمایتش قفل می‌شود */
    .disabled-campaign .btn-support {
        border-color: #aaa;
        color: #aaa;
        cursor: not-allowed;
    }
    .disabled-campaign .btn-support:hover { background: none; color: #aaa; box-shadow: none; }

    .campaign-loading {
        min-height: 420px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 16px;
    }
    .campaign-loading[hidden] { display: none; }
    .loading-card {
        width: min(100%, 390px);
        padding: 34px 28px;
        text-align: center;
        background: var(--card-bg);
        border: 1px solid var(--border-light);
        border-radius: 24px;
        box-shadow: 0 18px 45px rgba(0, 77, 76, 0.08);
    }
    .loading-mark {
        position: relative;
        width: 68px;
        height: 68px;
        margin: 0 auto 20px;
    }
    .loading-mark::before,
    .loading-mark::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 4px solid transparent;
    }
    .loading-mark::before {
        border-top-color: var(--primary-color);
        border-right-color: var(--primary-color);
        animation: campaign-spin 1s linear infinite;
    }
    .loading-mark::after {
        inset: 10px;
        border-bottom-color: var(--secondary-color);
        border-left-color: var(--secondary-color);
        animation: campaign-spin-reverse .8s linear infinite;
    }
    .loading-card h3 { margin: 0 0 8px; color: var(--primary-color); font-size: 18px; }
    .loading-card p { margin: 0; color: var(--text-muted); font-size: 13px; line-height: 1.9; }
    .loading-progress { margin-top: 14px; color: var(--secondary-color); font-size: 12px; font-weight: 700; }
    .campaign-message {
        display: none;
        margin-bottom: 20px;
        padding: 14px 16px;
        border-radius: 12px;
        line-height: 1.9;
        font-size: 13px;
    }
    .campaign-message.show { display: block; }
    .campaign-message.warning { background: #fff3cd; color: #735c0f; border: 1px solid #f4df9a; }
    .campaign-empty {
        grid-column: 1 / -1;
        padding: 48px 20px;
        text-align: center;
        background: var(--card-bg);
        color: var(--text-muted);
        border: 1px dashed var(--border-light);
        border-radius: 20px;
    }
    @keyframes campaign-spin { to { transform: rotate(360deg); } }
    @keyframes campaign-spin-reverse { to { transform: rotate(-360deg); } }

 @media (min-width: 1024px) {
    .card:not(.disabled-campaign):hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 35px rgba(0,0,0,0.08);
        border-color: var(--primary-color);
    }
    
    .card:not(.disabled-campaign):hover .card-img {
        transform: scale(1.06);
    }

    /* انیمیشن باز شدن متن روی دسکتاپ */
    .card:hover .card-desc {
        max-height: 300px; /* مقداری بیشتر از ارتفاع متن برای جا شدن کامل */
    }

    /* حذف افکت محوشدگی هنگام باز شدن */
    .card:hover .card-desc::after {
        opacity: 0;
    }
    
    .mobile-indicator { display: none; }
}

    @media (max-width: 1023px) {
    body { padding: 20px 10px; }
    .campaign-grid { grid-template-columns: 1fr; gap: 20px; }
    
    .mobile-indicator { 
        position: absolute; 
        bottom: 15px; 
        left: 15px; 
        background: rgba(0,0,0,0.05); 
        color: var(--text-muted); 
        padding: 4px 8px; 
        border-radius: 6px; 
        font-size: 11px; 
    }

    /* کلاس زنده باز شدن روی موبایل */
    .card.expanded .card-desc { 
        max-height: 400px; 
    }

    /* حذف افکت محوشدگی در موبایل */
    .card.expanded .card-desc::after {
        opacity: 0;
    }
}
</style>
</head>
<body>

<?php include "./components/back/back.php" ?>

<div class="container">
    <div class="page-header">
        <i class="fas fa-layer-group fa-lg"></i>
        <h2>کمپین‌های حمایتی </h2>
    </div>

    <div class="campaign-message warning" id="campaignMessage" role="status"></div>

    <div class="campaign-loading" id="campaignLoading" role="status" aria-live="polite">
        <div class="loading-card">
            <div class="loading-mark" aria-hidden="true"></div>
            <h3>در حال دریافت کمپین‌ها</h3>
            <p>اطلاعات کمپین‌ها از مراکز مکسا دریافت می‌شود؛ لطفاً چند لحظه صبر کنید.</p>
            <div class="loading-progress" id="loadingProgress">در حال برقراری ارتباط…</div>
        </div>
    </div>

    <div class="campaign-grid" id="campaignGrid" aria-live="polite"></div>
</div>

<script>
(function(){
    'use strict';
    const sources = <?= json_encode($campaignSources, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;
    const csrfToken = <?= json_encode(csrf_token(), JSON_HEX_TAG) ?>;
    const showBranchBadge = <?= dash_is_hq_view() ? 'true' : 'false' ?>;
    const placeholder = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMjUwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZWVmMWYyIi8+PGcgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYjljMmM2IiBzdHJva2Utd2lkdGg9IjMiPjxyZWN0IHg9IjE1OCIgeT0iOTIiIHdpZHRoPSI4NCIgaGVpZ2h0PSI2NCIgcng9IjciLz48Y2lyY2xlIGN4PSIxODIiIGN5PSIxMTYiIHI9IjkiLz48cGF0aCBkPSJNMTYyIDE1MGwyNC0yMiAxNSAxMyAxOS0xNyAxOCAxNnYxMnoiLz48L2c+PC9zdmc+';
    const loading = document.getElementById('campaignLoading');
    const loadingProgress = document.getElementById('loadingProgress');
    const grid = document.getElementById('campaignGrid');
    const message = document.getElementById('campaignMessage');

    function escapeHtml(value) {
        const node = document.createElement('div');
        node.textContent = String(value == null ? '' : value);
        return node.innerHTML;
    }

    function numberFa(value) {
        return new Intl.NumberFormat('fa-IR').format(Number(value) || 0);
    }

    function safeImage(value) {
        const url = String(value || '').trim();
        return /^(?:https?:\/\/|\/)/i.test(url) ? url : placeholder;
    }

    function renderCard(campaign) {
        const active = Number(campaign.is_active) === 1;
        const target = Number(campaign.target_amount) || 0;
        const collected = Number(campaign.collected_amount) || 0;
        const progress = target > 0 ? Math.min(100, Math.round(collected / target * 100)) : 0;
        const badge = showBranchBadge && campaign.branch_name
            ? '<span style="display:inline-block;font-size:11px;font-weight:700;color:#007b7a;background:rgba(0,123,122,.10);padding:3px 10px;border-radius:99px;margin-bottom:8px;">' + escapeHtml(campaign.branch_name) + '</span>'
            : '';

        const card = document.createElement('div');
        card.className = 'card' + (active ? '' : ' disabled-campaign');
        card.innerHTML =
            '<div class="status-badge ' + (active ? 'active">فعال' : 'inactive">غیرفعال') + '</div>' +
            '<form method="POST" class="toggle-status-form">' +
                '<input type="hidden" name="csrf_token" value="' + escapeHtml(csrfToken) + '">' +
                '<input type="hidden" name="toggle_id" value="' + escapeHtml(campaign.id) + '">' +
                '<input type="hidden" name="branch_slug" value="' + escapeHtml(campaign.branch_slug) + '">' +
                '<input type="hidden" name="current_status" value="' + (active ? '1' : '0') + '">' +
                '<button type="submit" class="btn-toggle-status"><i class="fas ' + (active ? 'fa-ban' : 'fa-check') + '" style="color:var(--' + (active ? 'danger' : 'success') + '-color)"></i> ' + (active ? 'غیرفعال‌سازی' : 'فعال‌سازی') + '</button>' +
            '</form>' +
            '<div class="card-img-wrapper"><img class="card-img" alt=""></div>' +
            '<div class="card-body">' +
                '<h4 class="card-title">' + escapeHtml(campaign.title) + '</h4>' + badge +
                '<div class="card-desc">' + escapeHtml(campaign.description).replace(/\n/g, '<br>') + '</div>' +
                '<div class="progress-container">' +
                    '<div class="funding-stats"><span>هدف: ' + numberFa(target) + ' تومان</span><span style="color:var(--secondary-color);font-size:14px">' + numberFa(progress) + '٪</span></div>' +
                    '<div class="progress-bar"><div class="progress-fill" style="width:' + progress + '%"></div></div>' +
                    '<div class="funding-stats" style="color:var(--text-muted);font-size:11px;font-weight:normal;margin-top:4px"><span>جمع‌آوری شده: ' + numberFa(collected) + ' تومان</span></div>' +
                '</div>' +
                '<button class="btn-support" ' + (active ? '' : 'disabled') + '><i class="fas fa-heart"></i> حمایت مالی مستقیم</button>' +
                '<div class="mobile-indicator"><i class="fas fa-ellipsis-h"></i></div>' +
            '</div>';

        const image = card.querySelector('.card-img');
        image.src = safeImage(campaign.image_url);
        image.addEventListener('error', function(){ image.src = placeholder; }, {once:true});
        card.addEventListener('click', function(){ card.classList.toggle('expanded'); });
        card.querySelector('.toggle-status-form').addEventListener('click', function(event){ event.stopPropagation(); });
        return card;
    }

    async function fetchSource(source) {
        const url = 'campaign-status.php?ajax=1&branch=' + encodeURIComponent(source.slug);
        const response = await fetch(url, {credentials: 'same-origin', headers: {'Accept': 'application/json'}});
        const payload = await response.json().catch(function(){ return null; });
        if (!response.ok || !payload || payload.status !== 'success') {
            throw new Error(payload && payload.message ? payload.message : 'پاسخ نامعتبر از سرور');
        }
        return Array.isArray(payload.data) ? payload.data : [];
    }

    async function loadCampaigns() {
        if (!sources.length) {
            loading.hidden = true;
            grid.innerHTML = '<div class="campaign-empty">مرکز فعالی برای دریافت کمپین‌ها پیدا نشد.</div>';
            return;
        }

        let completed = 0;
        const errors = [];
        const requests = sources.map(async function(source){
            try {
                return await fetchSource(source);
            } catch (error) {
                errors.push(source.name + ': ' + error.message);
                return [];
            } finally {
                completed++;
                loadingProgress.textContent = 'دریافت اطلاعات ' + numberFa(completed) + ' از ' + numberFa(sources.length) + ' مرکز';
            }
        });

        const groups = await Promise.all(requests);
        const campaigns = groups.flat();
        loading.hidden = true;

        if (errors.length) {
            message.textContent = errors.join(' | ');
            message.classList.add('show');
        }

        if (!campaigns.length) {
            grid.innerHTML = '<div class="campaign-empty">کمپینی برای نمایش پیدا نشد.</div>';
            return;
        }
        campaigns.forEach(function(campaign){ grid.appendChild(renderCard(campaign)); });
    }

    loadCampaigns();
})();
</script>

</body>
</html>

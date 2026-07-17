<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>شبکه همکاران مکسا</title>
<style>
/* Self-hosted Vazirmatn variable font (reliable on the Iran network, no external CDN) */
@font-face {
    font-family: 'Vazirmatn';
    src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
         url('/webfont/Vazirmatn[wght].woff2') format('woff2');
    font-weight: 100 900;
    font-style: normal;
    font-display: swap;
}
:root {
    --bg-global: #f4f7f6;
    --bg-surface: #ffffff;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --border-color: #e2e8f0;
    --brand-teal: #0d9488;
    --brand-teal-glow: rgba(13,148,136,0.15);
    --brand-gold: #d97706;
    --brand-gold-glow: rgba(217,119,6,0.10);
    --card-shadow: 0 10px 30px -10px rgba(0,0,0,0.04), 0 1px 3px rgba(0,0,0,0.02);
    --card-shadow-hover: 0 20px 40px -15px rgba(13,148,136,0.25);
    --ease-premium: cubic-bezier(0.25,1,0.5,1);
}
[data-theme="dark"] {
    --bg-global: #0f172a;
    --bg-surface: #1e293b;
    --text-main: #f1f5f9;
    --text-muted: #94a3b8;
    --border-color: #334155;
    --card-shadow: 0 10px 30px -10px rgba(0,0,0,0.3);
    --card-shadow-hover: 0 25px 50px -12px rgba(13,148,136,0.4);
}
* { box-sizing: border-box; transition: background-color 0.4s var(--ease-premium), border-color 0.4s var(--ease-premium); }
body { font-family: 'Vazirmatn',sans-serif; background-color: var(--bg-global); color: var(--text-main); margin: 0; padding: 0; min-height: 100vh; overflow-x: hidden; }

/* هدر */
.premium-header { position: sticky; top: 0; z-index: 100; background: var(--bg-surface); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border-color); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; }
.brand-section { display: flex; align-items: center; gap: 12px; }
.brand-dot { width: 12px; height: 12px; background: linear-gradient(135deg,var(--brand-teal),#2dd4bf); border-radius: 50%; box-shadow: 0 0 12px var(--brand-teal); }
.header-title { margin: 0; font-size: 1.3rem; font-weight: 800; }
.theme-switch-wrapper { background: var(--bg-global); border: 1px solid var(--border-color); padding: 4px; border-radius: 30px; cursor: pointer; display: flex; align-items: center; gap: 4px; }
.theme-btn-icon { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
.theme-switch-wrapper[data-active="light"] .light-icon,
.theme-switch-wrapper[data-active="dark"]  .dark-icon { color: var(--brand-teal); background: var(--bg-surface); box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 50%; }
.theme-btn-icon svg { width: 18px; height: 18px; fill: currentColor; }

.main-wrapper { max-width: 1300px; margin: 0 auto; padding: 40px 24px; }

/* دکمه فیلتر */
.controls-row { display: flex; justify-content: flex-start; margin-bottom: 24px; }
.filter-trigger-btn { display: flex; align-items: center; gap: 8px; background: var(--bg-surface); border: 1px solid var(--border-color); padding: 10px 20px; border-radius: 14px; font-family: 'Vazirmatn',sans-serif; font-size: 14px; font-weight: 700; color: var(--text-main); cursor: pointer; box-shadow: var(--card-shadow); transition: all 0.3s var(--ease-premium); }
.filter-trigger-btn:hover { border-color: var(--brand-teal); color: var(--brand-teal); transform: translateY(-2px); }
.filter-trigger-btn svg { width: 16px; height: 16px; fill: currentColor; }

/* گرید */
.premium-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(280px,1fr)); gap: 32px; transition: all 0.4s var(--ease-premium); }

/* کارت */
.modern-card { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 24px; overflow: hidden; box-shadow: var(--card-shadow); display: flex; flex-direction: column; position: relative; cursor: pointer; transition: transform 0.5s var(--ease-premium), box-shadow 0.5s var(--ease-premium), border-color 0.5s var(--ease-premium), filter 0.5s var(--ease-premium), opacity 0.5s var(--ease-premium); animation: cardEntrance 0.7s cubic-bezier(0.2,0.8,0.2,1) both; }
.modern-card:not(.is-active):hover { transform: translateY(-8px); box-shadow: var(--card-shadow-hover); border-color: var(--brand-teal); }
.premium-grid.has-active .modern-card:not(.is-active) { filter: blur(6px) grayscale(20%); opacity: 0.35; transform: scale(0.95); pointer-events: none; }
.modern-card.is-active { transform: translateY(0) scale(1.04) !important; box-shadow: 0 30px 60px -15px rgba(0,0,0,0.15) !important; border-color: var(--brand-teal) !important; z-index: 10; }
.image-wrapper { width: 100%; height: 280px; overflow: hidden; position: relative; background: var(--bg-global); }
.card-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s var(--ease-premium); }
.modern-card:hover .card-img { transform: scale(1.06); }
.avatar-placeholder { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted); gap: 8px; }
.avatar-placeholder .icon { font-size: 55px; }
.card-info-box { padding: 24px 20px 16px; display: flex; flex-direction: column; flex-grow: 1; text-align: center; }
.employee-name { margin: 0 0 16px; font-size: 1.15rem; font-weight: 800; color: var(--text-main); }
.badges-stack { display: flex; flex-direction: column; gap: 8px; }
.pill-badge { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 9px 14px; border-radius: 14px; font-size: 13px; font-weight: 700; border: 1px solid transparent; }
.pill-branch { background: var(--bg-global); color: var(--text-main); border-color: var(--border-color); }
.pill-branch .dot { background: var(--brand-teal); }
.pill-role { background: var(--brand-gold-glow); color: var(--brand-gold); }
.pill-role .dot { background: var(--brand-gold); }
.pill-badge .dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.pill-badge .value-text { max-width: 100%; }
.pill-branch .value-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pill-role .value-text { white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-align: center; }

/* کشو */
.action-drawer { max-height: 0; opacity: 0; overflow: hidden; display: flex; padding: 0 20px; background: rgba(0,0,0,0.015); border-top: 1px solid transparent; transition: max-height 0.4s var(--ease-premium), opacity 0.3s var(--ease-premium), padding 0.4s var(--ease-premium), border-color 0.4s var(--ease-premium); }
[data-theme="dark"] .action-drawer { background: rgba(255,255,255,0.02); }
.modern-card.is-active .action-drawer { max-height: 85px; opacity: 1; padding: 16px 20px; border-color: var(--border-color); }
.btn-more-info { width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 14px; border-radius: 14px; font-size: 14px; font-weight: 700; font-family: 'Vazirmatn',sans-serif; border: none; cursor: pointer; background: var(--brand-teal); color: #fff; transition: all 0.25s var(--ease-premium); }
.btn-more-info:hover { background: #0f766e; box-shadow: 0 4px 12px rgba(13,148,136,0.25); transform: translateY(-1px); }
.btn-more-info svg { width: 18px; height: 18px; fill: currentColor; }

/* فیلتر سایدبار */
.filter-sidebar { position: fixed; top: 0; right: -360px; width: 340px; height: 100%; background: var(--bg-surface); z-index: 200; box-shadow: -10px 0 40px rgba(0,0,0,0.08); display: flex; flex-direction: column; transition: right 0.4s var(--ease-premium); }
[data-theme="dark"] .filter-sidebar { box-shadow: -10px 0 40px rgba(0,0,0,0.4); }
.filter-sidebar.is-open { right: 0; }
.drawer-header { padding: 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
.drawer-header h3 { margin: 0; font-size: 1.15rem; font-weight: 800; }
.close-drawer-btn { background: none; border: none; font-size: 24px; color: var(--text-muted); cursor: pointer; transition: color 0.2s; }
.close-drawer-btn:hover { color: #ef4444; }
.drawer-body { padding: 24px; flex-grow: 1; display: flex; flex-direction: column; gap: 24px; }
.filter-group { display: flex; flex-direction: column; gap: 8px; }
.filter-group label { font-size: 13px; font-weight: 700; color: var(--text-muted); }
.filter-select { width: 100%; padding: 12px; border-radius: 14px; border: 1px solid var(--border-color); background: var(--bg-global); color: var(--text-main); font-family: 'Vazirmatn',sans-serif; font-size: 13.5px; font-weight: 600; outline: none; cursor: pointer; -webkit-appearance: none; -moz-appearance: none; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="%230d9488" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>'); background-repeat: no-repeat; background-position: left 14px center; padding-left: 35px; }
.filter-select:focus { border-color: var(--brand-teal); }
.drawer-footer { padding: 24px; border-top: 1px solid var(--border-color); display: flex; gap: 12px; }
.btn-apply-filter { flex: 2; background: var(--brand-teal); color: #fff; border: none; padding: 13px; border-radius: 12px; font-family: 'Vazirmatn',sans-serif; font-size: 14px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
.btn-apply-filter:hover { background: #0f766e; }
.btn-reset-filter { flex: 1; background: transparent; color: var(--text-muted); border: 1px solid var(--border-color); padding: 13px; border-radius: 12px; font-family: 'Vazirmatn',sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-reset-filter:hover { background: rgba(239,68,68,0.08); color: #ef4444; border-color: rgba(239,68,68,0.15); }
.drawer-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.25); backdrop-filter: blur(4px); z-index: 199; opacity: 0; pointer-events: none; transition: opacity 0.4s var(--ease-premium); }
.drawer-overlay.is-active { opacity: 1; pointer-events: auto; }

/* اسکلتون لودینگ */
.skeleton-card { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 24px; overflow: hidden; box-shadow: var(--card-shadow); }
.skeleton-img { width: 100%; height: 280px; background: linear-gradient(90deg, var(--border-color) 25%, var(--bg-global) 50%, var(--border-color) 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; }
.skeleton-body { padding: 24px 20px 20px; display: flex; flex-direction: column; gap: 12px; align-items: center; }
.skeleton-line { border-radius: 8px; background: linear-gradient(90deg, var(--border-color) 25%, var(--bg-global) 50%, var(--border-color) 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* خطا */
.error-msg { grid-column: 1/-1; text-align: center; padding: 60px; color: #ef4444; font-weight: 700; font-size: 15px; }

/* انیمیشن */
@keyframes cardEntrance { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

/* موبایل */
@media (max-width: 600px) {
    .premium-header { padding: 12px 16px; }
    .header-title { font-size: 1.05rem; }
    .main-wrapper { padding: 20px 8px; }
    .controls-row { margin-bottom: 16px; }
    .filter-trigger-btn { padding: 8px 14px; font-size: 12px; border-radius: 10px; }
    .filter-sidebar { width: 290px; right: -310px; }
    .premium-grid { grid-template-columns: repeat(3,1fr); gap: 8px; }
    .modern-card { border-radius: 14px; }
    .image-wrapper { height: 115px; }
    .avatar-placeholder .icon { font-size: 32px; }
    .card-info-box { padding: 10px 4px 6px; }
    .employee-name { font-size: 0.8rem; font-weight: 800; margin: 0 0 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .badges-stack { gap: 3px; }
    .pill-branch { padding: 3px 4px; border-radius: 6px; font-size: 9px; gap: 4px; }
    .pill-role { padding: 3px 4px; border-radius: 6px; }
    .pill-role .value-text { white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-align: center; font-weight: 700; }
    .modern-card.is-active .action-drawer { max-height: 52px; padding: 8px 6px; }
    .btn-more-info { padding: 6px 4px; font-size: 10px; border-radius: 8px; gap: 3px; }
    .btn-more-info svg { width: 12px; height: 12px; }
    .modern-card.is-active { transform: scale(1.03) !important; }
    .skeleton-img { height: 115px; }
}
</style>
</head>
<body>

<div class="main-wrapper">
    <div class="controls-row">
        <button id="filter-toggle-btn" class="filter-trigger-btn">
            <svg viewBox="0 0 24 24"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>
            فیلتر همکاران
        </button>
    </div>
    <div class="premium-grid" id="cards-grid">
        </div>
</div>

<div id="filter-sidebar" class="filter-sidebar">
    <div class="drawer-header">
        <h3>فیلتر پیشرفته</h3>
        <button id="close-drawer-btn" class="close-drawer-btn">&times;</button>
    </div>
    <div class="drawer-body">
        <div class="filter-group">
            <label for="filter-branch">محل خدمت</label>
            <select id="filter-branch" class="filter-select">
                <option value="all">همه شعبه‌ها</option>
                <option value="tehran">شعبه تهران</option>
                <option value="isfahan">شعبه اصفهان</option>
                <option value="mashhad">شعبه مشهد</option>
                <option value="qom">شعبه قم</option>
                <option value="tabriz">شعبه تبریز</option>
                <option value="ahvaz">شعبه اهواز</option>
                <option value="kerman">شعبه کرمان</option>
                <option value="kashan">شعبه کاشان</option>
                <option value="telemedicine">پزشکی از راه دور مکسا</option>
                <option value="setad_markazi">ستاد مرکزی</option>
            </select>
        </div>
        <div class="filter-group" id="filter-role-group">
            <label for="filter-role">سمت شغلی</label>
            <select id="filter-role" class="filter-select">
                <option value="all">همه سمت‌ها</option>
                <option value="administrative">سمت‌های اداری</option>
                <option value="doctors">پزشک متخصص</option>
                <option value="nurses">کادر پرستاری</option>
                <option value="psychologists">روانشناس سلامت</option>
                <option value="social_workers">مددکار اجتماعی</option>
                <option value="spiritual_care">مراقب معنوی</option>
                <option value="nutritionists">متخصص تغذیه</option>
                <option value="rehabilitation">متخصص توانبخشی</option>
                <option value="genetic_counselors">مشاور ژنتیک و غربالگری</option>
            </select>
        </div>
    </div>
    <div class="drawer-footer">
        <button id="apply-filter-btn" class="btn-apply-filter">اعمال فیلتر</button>
        <button id="reset-filter-btn" class="btn-reset-filter">حذف</button>
    </div>
</div>
<div id="drawer-overlay" class="drawer-overlay"></div>

<script>
// ─────────────────────────────────────────────
// آدرس API — اگه مسیر فایل api-employees.php عوض شد اینجا تغییر بده
const API_URL = '/api-employees.php';
// ─────────────────────────────────────────────

const BRANCHES = {
    tehran:        'شعبه تهران',
    isfahan:       'شعبه اصفهان',
    mashhad:       'شعبه مشهد',
    qom:           'شعبه قم',
    tabriz:        'شعبه تبریز',
    ahvaz:         'شعبه اهواز',
    kerman:        'شعبه کرمان',
    kashan:        'شعبه کاشان',
    telemedicine:  'پزشکی از راه دور مکسا',
    setad_markazi: 'ستاد مرکزی',
};

const ROLES = {
    administrative:    'سمت‌های اداری',
    doctors:           'پزشک متخصص',
    nurses:            'کادر پرستاری',
    psychologists:     'روانشناس سلامت',
    social_workers:    'مددکار اجتماعی',
    spiritual_care:    'مراقب معنوی',
    nutritionists:     'متخصص تغذیه',
    rehabilitation:    'متخصص توانبخشی',
    genetic_counselors:'مشاور ژنتیک و غربالگری',
    deputies:          'معاونت',
    admin:             'کادر اداری',
    ceo_office:        'حوزه مدیر عامل',
};

// ─── لودینگ اسکلتون ───
function renderSkeletons(count = 6) {
    const grid = document.getElementById('cards-grid');
    grid.innerHTML = Array.from({length: count}).map(() => `
        <div class="skeleton-card">
            <div class="skeleton-img"></div>
            <div class="skeleton-body">
                <div class="skeleton-line" style="width:60%;height:18px;"></div>
                <div class="skeleton-line" style="width:80%;height:36px;"></div>
                <div class="skeleton-line" style="width:70%;height:36px;"></div>
            </div>
        </div>`).join('');
}

// ─── ساخت یک کارت ───
function buildCard(emp, index) {
    const fullname    = emp.fullname || 'همکار مکسا';
    const branchText  = BRANCHES[emp.branch] || 'نامشخص';
    const rawRole     = emp.role || '';
    const rawCat      = emp.job_category || '';
    const roleText    = ROLES[rawRole] || (rawRole ? rawRole : (ROLES[rawCat] || 'ثبت نشده'));
    const filterRole  = ROLES[rawRole] ? rawRole : (ROLES[rawCat] ? rawCat : rawRole);
    const slug        = encodeURIComponent(fullname.trim().replace(/\s+/g, '-'));
    const delay       = ((index % 20) * 0.055).toFixed(3);

    const imgHtml = emp.profile_pic
        ? `<img src="${emp.profile_pic}" class="card-img" alt="${fullname}">`
        : `<div class="avatar-placeholder"><span class="icon">👤</span></div>`;

    return `
    <div class="modern-card"
         style="animation-delay:${delay}s"
         data-branch="${emp.branch || ''}"
         data-role="${filterRole}"
         data-search="${fullname.toLowerCase()}">
        <div class="image-wrapper">${imgHtml}</div>
        <div class="card-info-box">
            <h2 class="employee-name" title="${fullname}">${fullname}</h2>
            <div class="badges-stack">
                <div class="pill-badge pill-branch" title="${branchText}">
                    <span class="dot"></span>
                    <span class="value-text">${branchText}</span>
                </div>
                <div class="pill-badge pill-role" title="${roleText}">
                    <span class="dot"></span>
                    <span class="value-text">${roleText}</span>
                </div>
            </div>
        </div>
        <div class="action-drawer">
            <button class="btn-more-info"
                onclick="event.stopPropagation(); window.location.href='/dashboard/personal-resume-detail/${slug}';">
                <svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                اطلاعات بیشتر
            </button>
        </div>
    </div>`;
}

// ─── رندر همه کارت‌ها ───
function renderCards(employees) {
    const grid = document.getElementById('cards-grid');
    if (!employees.length) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--text-muted);font-weight:700;">دیتایی یافت نشد.</div>';
        return;
    }
    grid.innerHTML = employees.map((emp, i) => buildCard(emp, i)).join('');
    bindCardClicks();
    adjustRoleFonts();
}

// ─── کلیک کارت ───
function bindCardClicks() {
    const grid  = document.getElementById('cards-grid');
    const cards = grid.querySelectorAll('.modern-card');
    cards.forEach(card => {
        card.addEventListener('click', e => {
            e.stopPropagation();
            const wasActive = card.classList.contains('is-active');
            cards.forEach(c => c.classList.remove('is-active'));
            grid.classList.remove('has-active');
            if (!wasActive) { card.classList.add('is-active'); grid.classList.add('has-active'); }
        });
    });
    document.addEventListener('click', () => {
        cards.forEach(c => c.classList.remove('is-active'));
        grid.classList.remove('has-active');
    });
}

// ─── فونت موبایل ───
function adjustRoleFonts() {
    const isMobile = window.innerWidth <= 600;
    document.querySelectorAll('.pill-role .value-text').forEach(el => {
        if (!isMobile) { el.style.fontSize = '12.5px'; el.style.lineHeight = '1.35'; return; }
        const len = el.textContent.trim().length;
        el.style.fontSize   = len > 18 ? (len > 34 ? '6.8px' : '7.5px') : '9.5px';
        el.style.lineHeight = len > 18 ? '1.1' : '1.2';
    });
}
window.addEventListener('resize', adjustRoleFonts);

// ─── فچ داده از API ───
let allEmployees = [];

// ایزولاسیونِ شعبه: اگر صفحه از مسیرِ یک شعبه آمده باشد (page-view گلوبالِ branch را
// تزریق می‌کند)، فقط همکارانِ همان شعبه نمایش داده می‌شوند. تطبیق هم با نامِ فارسیِ
// شعبه (مقدارِ ذخیره‌شده در ستونِ branch) و هم با slug/کلید انجام می‌شود تا با هر دو
// مدلِ داده کار کند.
const BRANCH_SLUG = (typeof window.__MAXA_BRANCH__ === 'string') ? window.__MAXA_BRANCH__ : '';
const BRANCH_NAME = (typeof window.__MAXA_BRANCH_NAME__ === 'string') ? window.__MAXA_BRANCH_NAME__ : '';
function empInBranch(emp) {
    if (!BRANCH_SLUG && !BRANCH_NAME) return true;
    const b = (emp.branch || '').trim();
    if (BRANCH_NAME && b === BRANCH_NAME) return true;
    if (BRANCH_SLUG && b === BRANCH_SLUG) return true;
    // تطبیق با نگاشتِ کلید→نام (مثلاً branch='tehran' و نامِ شعبه='شعبه تهران')
    if (BRANCH_NAME && BRANCHES[b] === BRANCH_NAME) return true;
    return false;
}

async function loadEmployees() {
    renderSkeletons();
    try {
        const res  = await fetch(API_URL);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        allEmployees = await res.json();
        if (BRANCH_SLUG || BRANCH_NAME) {
            allEmployees = allEmployees.filter(empInBranch);
            // عنوانِ صفحه را به نامِ شعبه به‌روزرسانی کن (در صورت وجود)
            const ht = document.querySelector('.header-title');
            if (ht && BRANCH_NAME) ht.textContent = 'همکاران ' + BRANCH_NAME;
        }
        renderCards(allEmployees);
    } catch (err) {
        document.getElementById('cards-grid').innerHTML =
            `<div class="error-msg">خطا در بارگذاری داده‌ها.<br><small>${err.message}</small></div>`;
    }
}

// ─── فیلتر ───
document.addEventListener('DOMContentLoaded', () => {
    loadEmployees();

    const filterBtn     = document.getElementById('filter-toggle-btn');
    const filterSidebar = document.getElementById('filter-sidebar');
    const drawerOverlay = document.getElementById('drawer-overlay');
    const closeBtn      = document.getElementById('close-drawer-btn');
    const applyBtn      = document.getElementById('apply-filter-btn');
    const resetBtn      = document.getElementById('reset-filter-btn');
    const branchSelect  = document.getElementById('filter-branch');
    const roleSelect    = document.getElementById('filter-role');
    const roleGroup     = document.getElementById('filter-role-group');

    const openDrawer  = () => { filterSidebar.classList.add('is-open');    drawerOverlay.classList.add('is-active'); };
    const closeDrawer = () => { filterSidebar.classList.remove('is-open'); drawerOverlay.classList.remove('is-active'); };

    filterBtn.addEventListener('click', openDrawer);
    closeBtn.addEventListener('click', closeDrawer);
    drawerOverlay.addEventListener('click', closeDrawer);

    branchSelect.addEventListener('change', () => {
        roleGroup.style.display = branchSelect.value === 'setad_markazi' ? 'none' : 'flex';
        if (branchSelect.value === 'setad_markazi') roleSelect.value = 'all';
    });

    applyBtn.addEventListener('click', () => {
        const b = branchSelect.value;
        const r = roleSelect.value;
        const filtered = allEmployees.filter(emp => {
            const okB = b === 'all' || emp.branch === b;
            const rawRole = emp.role || '';
            const rawCat  = emp.job_category || '';
            const filterRole = ROLES[rawRole] ? rawRole : (ROLES[rawCat] ? rawCat : rawRole);
            const okR = r === 'all' || filterRole === r;
            return okB && okR;
        });
        renderCards(filtered);
        closeDrawer();
    });

    resetBtn.addEventListener('click', () => {
        branchSelect.value = 'all';
        roleSelect.value   = 'all';
        roleGroup.style.display = 'flex';
        renderCards(allEmployees);
        closeDrawer();
    });

    // تم
    const toggleWrapper = document.getElementById('theme-toggle-wrapper');
    const savedTheme    = localStorage.getItem('premium-theme') || 'light';
    const setTheme = t => {
        t === 'dark' ? document.body.setAttribute('data-theme','dark') : document.body.removeAttribute('data-theme');
        if (toggleWrapper) toggleWrapper.setAttribute('data-active', t);
        localStorage.setItem('premium-theme', t);
    };
    setTheme(savedTheme);
    if (toggleWrapper) toggleWrapper.addEventListener('click', () => setTheme(toggleWrapper.getAttribute('data-active') === 'light' ? 'dark' : 'light'));
});
</script>
</body>
</html>
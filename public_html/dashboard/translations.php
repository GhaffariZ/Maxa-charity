<?php
/**
 * Translation Management UI
 * Dashboard page for managing all translations (UI strings + entity content)
 */

require_once __DIR__ . '/_guard.php';
dash_require('pages');

require_once __DIR__ . '/../core/language.php';
require_once __DIR__ . '/../core/translations.php';

$pdo = dash_pdo();
$action = $_GET['action'] ?? 'list';
$entityType = $_GET['entity_type'] ?? '';
$entityId = intval($_GET['entity_id'] ?? 0);
$locale = $_GET['locale'] ?? '';

// Handle AJAX save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) $data = $_POST;
        
        $type = $data['type'] ?? '';
        $id = intval($data['id'] ?? 0);
        $lang = $data['locale'] ?? '';
        $fields = $data['fields'] ?? [];
        
        if (!in_array($lang, SUPPORTED_LOCALES)) {
            throw new Exception('Invalid locale');
        }
        
        if ($type === 'static') {
            // Save static translation (key-value)
            $key = $data['key'] ?? '';
            $value = $data['value'] ?? '';
            
            $stmt = $pdo->prepare("
                INSERT INTO static_translations (translation_key, locale, value) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$key, $lang, $value]);
            
            echo json_encode(['status' => 'ok']);
        } else {
            // Save entity translation
            $success = saveEntityTranslation($type, $id, $lang, $fields);
            echo json_encode(['status' => $success ? 'ok' : 'error']);
        }
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle AJAX load
if ($action === 'api') {
    header('Content-Type: application/json; charset=utf-8');
    $type = $_GET['type'] ?? '';
    $id = intval($_GET['id'] ?? 0);
    
    if ($type === 'static') {
        // Get all static translations grouped by key
        $stmt = $pdo->prepare("SELECT translation_key, locale, value FROM static_translations ORDER BY translation_key, locale");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $translations = [];
        foreach ($rows as $row) {
            $translations[$row['translation_key']][$row['locale']] = $row['value'];
        }
        
        echo json_encode(['status' => 'ok', 'translations' => $translations], JSON_UNESCAPED_UNICODE);
    } elseif ($type === 'php') {
        // Get translations from PHP lang files
        $result = [];
        foreach (SUPPORTED_LOCALES as $loc) {
            $file = __DIR__ . '/../lang/' . $loc . '.php';
            if (file_exists($file)) {
                $result[$loc] = require $file;
            }
        }
        echo json_encode(['status' => 'ok', 'translations' => $result], JSON_UNESCAPED_UNICODE);
    } elseif ($id > 0) {
        $translations = getAllEntityTranslations($type, $id);
        echo json_encode(['status' => 'ok', 'translations' => $translations], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing id']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت ترجمه‌ها | مکسا</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Vazirmatn', Tahoma, sans-serif; background: #f1f5f9; color: #1e293b; }
        
        .tl-header { background: linear-gradient(135deg, #0899A9, #067d8a); color: #fff; padding: 20px 24px; }
        .tl-header h1 { font-size: 22px; font-weight: 800; }
        .tl-header p { font-size: 13px; opacity: 0.85; margin-top: 4px; }
        
        .tl-container { max-width: 1200px; margin: 24px auto; padding: 0 16px; }
        
        .tl-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .tl-tab { padding: 8px 18px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; 
                   cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; }
        .tl-tab:hover { border-color: #0899A9; color: #0899A9; }
        .tl-tab.active { background: #0899A9; color: #fff; border-color: #0899A9; }
        
        .tl-locale-bar { display: flex; gap: 6px; margin-bottom: 16px; }
        .tl-locale-btn { padding: 6px 14px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff;
                         cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.2s; }
        .tl-locale-btn.active { background: #1e293b; color: #fff; border-color: #1e293b; }
        
        .tl-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 16px; overflow: hidden; }
        .tl-card-header { padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; 
                          display: flex; justify-content: space-between; align-items: center; }
        .tl-card-header h3 { font-size: 14px; font-weight: 700; }
        .tl-card-body { padding: 18px; }
        
        .tl-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        @media (max-width: 768px) { .tl-grid { grid-template-columns: 1fr; } }
        
        .tl-field { margin-bottom: 12px; }
        .tl-field label { display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; }
        .tl-field input, .tl-field textarea { width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; 
                                               border-radius: 8px; font-size: 13px; font-family: inherit;
                                               transition: border-color 0.2s; }
        .tl-field input:focus, .tl-field textarea:focus { outline: none; border-color: #0899A9; }
        .tl-field textarea { min-height: 60px; resize: vertical; }
        
        .tl-btn { padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; font-size: 13px;
                  font-weight: 600; transition: all 0.2s; font-family: inherit; }
        .tl-btn-primary { background: #0899A9; color: #fff; }
        .tl-btn-primary:hover { background: #067d8a; }
        .tl-btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .tl-btn-secondary:hover { background: #e2e8f0; }
        
        .tl-status { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .tl-status-ok { background: #dcfce7; color: #166534; }
        .tl-status-missing { background: #fef2f2; color: #991b1b; }
        
        .tl-search { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px;
                     font-size: 14px; font-family: inherit; margin-bottom: 16px; }
        .tl-search:focus { outline: none; border-color: #0899A9; }
        
        .tl-toast { position: fixed; bottom: 20px; left: 20px; padding: 12px 20px; border-radius: 10px;
                     color: #fff; font-size: 13px; font-weight: 600; z-index: 9999; transform: translateY(100px);
                     transition: transform 0.3s ease; }
        .tl-toast.show { transform: translateY(0); }
        .tl-toast.success { background: #16a34a; }
        .tl-toast.error { background: #dc2626; }
        
        .tl-key { font-family: monospace; font-size: 12px; color: #64748b; direction: ltr; text-align: left; }
        .tl-category { font-size: 11px; font-weight: 700; color: #0899A9; text-transform: uppercase; }
    </style>
</head>
<body>

<div class="tl-header">
    <h1>🌐 مدیریت ترجمه‌ها</h1>
    <p>ویرایش رشته‌های رابط کاربری و محتوای ترجمه‌شده</p>
</div>

<div class="tl-container">
    <div class="tl-tabs">
        <button class="tl-tab active" onclick="switchTab('ui')">🎨 رشته‌های رابط کاربری</button>
        <button class="tl-tab" onclick="switchTab('pages')">📄 ترجمه صفحات</button>
        <button class="tl-tab" onclick="switchTab('campaigns')">❤️ ترجمه کمپین‌ها</button>
        <button class="tl-tab" onclick="switchTab('news')">📰 ترجمه اخبار</button>
        <button class="tl-tab" onclick="switchTab('hero')">🖼 ترجمه اسلایدر</button>
    </div>
    
    <div class="tl-locale-bar">
        <button class="tl-locale-btn active" onclick="switchLocale('fa')" data-loc="fa">🇮🇷 فارسی</button>
        <button class="tl-locale-btn" onclick="switchLocale('en')" data-loc="en">🇺🇸 English</button>
        <button class="tl-locale-btn" onclick="switchLocale('ar')" data-loc="ar">🇸🇦 العربية</button>
    </div>
    
    <input type="text" class="tl-search" placeholder="جستجوی کلید ترجمه..." id="searchInput" oninput="filterKeys()">
    
    <div id="contentArea">
        <div class="tl-card">
            <div class="tl-card-header">
                <h3>در حال بارگذاری...</h3>
            </div>
        </div>
    </div>
</div>

<div class="tl-toast" id="toast"></div>

<script>
let currentTab = 'ui';
let currentLocale = 'fa';
let allTranslations = {};
let baseLocale = {};

function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.tl-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    loadTranslations();
}

function switchLocale(loc) {
    currentLocale = loc;
    document.querySelectorAll('.tl-locale-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`[data-loc="${loc}"]`).classList.add('active');
    renderUI();
}

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'tl-toast ' + type + ' show';
    setTimeout(() => t.classList.remove('show'), 3000);
}

async function loadTranslations() {
    const area = document.getElementById('contentArea');
    area.innerHTML = '<div class="tl-card"><div class="tl-card-header"><h3>در حال بارگذاری...</h3></div></div>';
    
    try {
        const resp = await fetch(`/dashboard/translations.php?action=api&type=php`);
        const data = await resp.json();
        allTranslations = data.translations || {};
        baseLocale = allTranslations['fa'] || {};
        renderUI();
    } catch(e) {
        area.innerHTML = '<div class="tl-card"><div class="tl-card-header"><h3>خطا در بارگذاری</h3></div></div>';
    }
}

function renderUI() {
    const area = document.getElementById('contentArea');
    const search = document.getElementById('searchInput').value.toLowerCase();
    
    if (currentTab === 'ui') {
        renderUITranslations(area, search);
    } else {
        renderEntityTab(area, search);
    }
}

function renderUITranslations(area, search) {
    const fa = allTranslations['fa'] || {};
    const current = allTranslations[currentLocale] || {};
    
    let html = '';
    
    for (const [section, keys] of Object.entries(fa)) {
        if (typeof keys !== 'object' || keys === null) continue;
        
        let sectionHtml = '';
        let hasMatch = false;
        
        for (const [key, faValue] of Object.entries(keys)) {
            const fullKey = section + '.' + key;
            if (search && !fullKey.toLowerCase().includes(search) && !faValue.toLowerCase().includes(search)) continue;
            hasMatch = true;
            
            const currentValue = current[section]?.[key] || '';
            const isMissing = !currentValue && currentLocale !== 'fa';
            
            sectionHtml += `
                <div class="tl-field" style="display:flex;gap:12px;align-items:start;">
                    <div style="flex:1;">
                        <label>
                            <span class="tl-key">${fullKey}</span>
                            ${isMissing ? '<span class="tl-status tl-status-missing">ترجمه نشده</span>' : 
                              currentValue ? '<span class="tl-status tl-status-ok">✓</span>' : ''}
                        </label>
                        <div style="display:flex;gap:8px;">
                            <input type="text" value="${escHtml(currentValue)}" 
                                   placeholder="${escHtml(faValue)}"
                                   onchange="saveTranslation('ui', '${section}', '${key}', this.value)"
                                   style="flex:1;">
                            <button class="tl-btn tl-btn-secondary" onclick="this.previousElementSibling.value='${escAttr(faValue)}'; saveTranslation('ui', '${section}', '${key}', '${escAttr(faValue)}')" 
                                    title="کپی متن فارسی" style="font-size:11px;padding:4px 8px;">فارسی</button>
                        </div>
                    </div>
                </div>`;
        }
        
        if (hasMatch) {
            html += `
                <div class="tl-card">
                    <div class="tl-card-header">
                        <span class="tl-category">${section}</span>
                        <span style="font-size:12px;color:#64748b;">${Object.keys(keys).length} کلید</span>
                    </div>
                    <div class="tl-card-body">${sectionHtml}</div>
                </div>`;
        }
    }
    
    area.innerHTML = html || '<div class="tl-card"><div class="tl-card-header"><h3>نتیجه‌ای یافت نشد</h3></div></div>';
}

function renderEntityTab(area, search) {
    const typeMap = { pages: 'page', campaigns: 'campaign', news: 'news', hero: 'hero_slide' };
    const entityType = typeMap[currentTab] || currentTab;
    
    area.innerHTML = `
        <div class="tl-card">
            <div class="tl-card-header">
                <h3>ترجمه ${currentTab}</h3>
                <span style="font-size:12px;color:#64748b;">از API ذخیره/بازیابی کنید</span>
            </div>
            <div class="tl-card-body">
                <p style="color:#64748b;font-size:13px;">
                    برای ترجمه محتوای ${currentTab}، از API ترجمه استفاده کنید:<br>
                    <code style="direction:ltr;display:inline-block;margin-top:8px;background:#f1f5f9;padding:4px 8px;border-radius:6px;font-size:12px;">
                        POST /dashboard/translation-api.php<br>
                        { "entity_type": "${entityType}", "entity_id": ID, "locale": "${currentLocale}", "fields": {...} }
                    </code>
                </p>
                <div style="margin-top:16px;">
                    <label style="font-size:12px;font-weight:600;color:#64748b;">شناسه موجودیت:</label>
                    <input type="number" id="entityIdInput" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;width:120px;margin:0 8px;">
                    <button class="tl-btn tl-btn-primary" onclick="loadEntityTranslations('${entityType}')">بارگذاری</button>
                </div>
                <div id="entityResult" style="margin-top:16px;"></div>
            </div>
        </div>`;
}

async function loadEntityTranslations(type) {
    const id = document.getElementById('entityIdInput').value;
    if (!id) return;
    
    try {
        const resp = await fetch(`/dashboard/translations.php?action=api&type=${type}&id=${id}`);
        const data = await resp.json();
        const result = document.getElementById('entityResult');
        
        if (data.translations && Object.keys(data.translations).length > 0) {
            let html = '<div style="margin-top:12px;">';
            for (const [loc, trans] of Object.entries(data.translations)) {
                html += `<div style="margin-bottom:8px;padding:8px;background:#f8fafc;border-radius:6px;">
                    <strong>${loc}:</strong> <span style="font-size:13px;">${JSON.stringify(trans).substring(0, 200)}</span>
                </div>`;
            }
            html += '</div>';
            result.innerHTML = html;
        } else {
            result.innerHTML = '<p style="color:#64748b;font-size:13px;">ترجمه‌ای یافت نشد</p>';
        }
    } catch(e) {
        showToast('خطا در بارگذاری', 'error');
    }
}

async function saveTranslation(type, section, key, value) {
    try {
        const resp = await fetch('/dashboard/translations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                ajax: true,
                type: 'static',
                locale: currentLocale,
                key: section + '.' + key,
                value: value
            })
        });
        const data = await resp.json();
        if (data.status === 'ok') {
            // Update local cache
            if (!allTranslations[currentLocale]) allTranslations[currentLocale] = {};
            if (!allTranslations[currentLocale][section]) allTranslations[currentLocale][section] = {};
            allTranslations[currentLocale][section][key] = value;
            showToast('ذخیره شد ✓');
        } else {
            showToast('خطا در ذخیره‌سازی', 'error');
        }
    } catch(e) {
        showToast('خطا در ذخیره‌سازی', 'error');
    }
}

function filterKeys() { renderUI(); }

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function escAttr(s) { return String(s||'').replace(/'/g,"\\'").replace(/"/g,'&quot;'); }

// Initial load
loadTranslations();
</script>

</body>
</html>

<?php
// دریافت فهرست شعب فعال ارائه‌دهنده استند از دیتابیس برای دراپ‌داون اولیه
$homeBranches = [];
try {
    $dbPath = dirname(__DIR__, 3) . '/core/database.php';
    if (file_exists($dbPath)) {
        require_once $dbPath;
    }
    if (isset($pdo)) {
        $st = $pdo->query("
            SELECT DISTINCT COALESCE(b.province, b.name) AS province, b.id, b.name, b.city
            FROM branches b
            LEFT JOIN branch_features bf ON bf.branch_id = b.id AND bf.feature = 'stands'
            WHERE b.is_hq = 0 AND b.status = 'active' AND (bf.enabled = 1 OR bf.enabled IS NULL)
            ORDER BY b.name ASC
        ");
        $homeBranches = $st->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {}

// استان پیش‌فرض
$defaultProvince = !empty($homeBranches) ? ($homeBranches[0]['province'] ?: $homeBranches[0]['name']) : 'تهران';
?>
<style>
/* تعریف پالت رنگی هماهنگ با دیزاین سیستم مکسا */
:root {
    --color-primary: #007b7a;
    --color-primary-dark: #006665;
    --color-primary-light: #4fb2b0;
    --color-secondary: #f4a61e;
    --color-text: #2f3437;
    --color-muted: #7b8389;
    --color-border: #e6e8ea;
    --color-bg: #f8f9fa;
    --color-surface: #ffffff;
    --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.04);
}

/* استایل کل بخش */
.creative-section {
    max-width: 1100px;
    margin: 48px auto;
    padding: 40px 28px;
    font-family: 'Vazirmatn', Tahoma, sans-serif;
    direction: rtl;
    background: var(--color-surface);
    border-radius: 28px;
    box-shadow: var(--shadow-soft);
    border: 1px solid var(--color-border);
}

/* تیتر اصلی و توضیحات */
.creative-title-wrapper {
    text-align: center;
    margin-bottom: 28px;
    position: relative;
}

.creative-title {
    font-size: clamp(22px, 3.5vw, 30px);
    font-weight: 900;
    color: var(--color-text);
    margin: 0 0 12px;
    display: inline-block;
    position: relative;
    padding: 0 18px;
}

.creative-title::before, .creative-title::after {
    content: "";
    position: absolute;
    top: 50%;
    width: 36px;
    height: 2px;
    background: var(--color-primary-light);
}
.creative-title::before { right: -42px; }
.creative-title::after { left: -42px; }

.creative-subtitle {
    color: var(--color-muted);
    font-size: 14.5px;
    line-height: 1.8;
    max-width: 680px;
    margin: 0 auto;
}

/* باکس انتخاب شعبه */
.home-stand-branch-box {
    background: #f0fdfa;
    border: 1px solid #ccfbf1;
    border-radius: 18px;
    padding: 20px 24px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.branch-select-label-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.branch-select-icon {
    width: 38px;
    height: 38px;
    background: rgba(0, 123, 122, 0.12);
    color: var(--color-primary);
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.branch-select-icon svg { width: 20px; height: 20px; }

.branch-select-label-text strong {
    display: block;
    font-size: 14.5px;
    font-weight: 800;
    color: var(--color-text);
}
.branch-select-label-text span {
    font-size: 12px;
    color: var(--color-muted);
}

.home-branch-select {
    background: #ffffff;
    border: 1.5px solid var(--color-primary-light);
    border-radius: 12px;
    padding: 10px 18px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    color: var(--color-text);
    outline: none;
    cursor: pointer;
    min-width: 240px;
    transition: all 0.2s;
    box-shadow: 0 2px 6px rgba(0, 123, 122, 0.08);
}
.home-branch-select:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(0, 123, 122, 0.16);
}

/* باکس هشدار و یادآوری محدوده شهری (Refined & Formal Notice) */
.home-stand-notice {
    background: #fffbeb;
    border: 1px solid #fef3c7;
    border-right: 4px solid #f59e0b;
    border-radius: 14px;
    padding: 14px 18px;
    margin-bottom: 36px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13.5px;
    color: #92400e;
    line-height: 1.7;
}
.home-stand-notice svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    color: #d97706;
}

/* ساختار تفکیک‌کننده دو قطبی (تبریک / تسلیت) */
.dual-split-wrapper {
    display: grid;
    grid-template-columns: 1fr 1px 1fr;
    gap: 36px;
    position: relative;
}

.center-divider {
    background: var(--color-border);
    position: relative;
}
.center-divider::after {
    content: "یا";
    position: absolute;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    color: var(--color-muted);
    font-size: 11.5px;
    font-weight: 800;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

.column-block {
    display: flex;
    flex-direction: column;
}

.block-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
}
.block-header .badge {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
.congrats-header .badge { background: var(--color-primary); box-shadow: 0 0 0 3px rgba(0,123,122,0.2); }
.condolence-header .badge { background: #475569; box-shadow: 0 0 0 3px rgba(71,85,105,0.2); }

.block-header h3 {
    font-size: 17px;
    font-weight: 800;
    margin: 0;
    color: var(--color-text);
}

.cards-subgrid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

/* کارت استند */
.creative-card-item {
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 16px;
    padding: 12px;
    transition: transform 0.35s cubic-bezier(0.25, 1, 0.5, 1), box-shadow 0.35s ease, border-color 0.35s;
}
.creative-card-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);
    border-color: var(--color-primary-light);
}

.creative-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 3 / 4; 
    background: #ffffff;
    display: grid;
    place-items: center;
    border: 1px solid rgba(0,0,0,0.03);
}
.creative-card img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    transition: transform 0.4s ease;
}
.creative-card-item:hover .creative-card img {
    transform: scale(1.06);
}

.stand-card-title {
    font-size: 13px;
    font-weight: 800;
    color: var(--color-text);
    margin: 4px 0 2px;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stand-card-price {
    font-size: 12.5px;
    font-weight: 900;
    color: var(--color-primary);
    text-align: center;
    margin-bottom: 6px;
}

.creative-action-btn {
    background: var(--color-surface);
    color: var(--color-text);
    text-align: center;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid var(--color-border);
    transition: all 0.25s ease;
    display: block;
}
.creative-action-btn:hover {
    background: var(--color-primary);
    color: #ffffff;
    border-color: var(--color-primary);
}

.empty-stands-msg {
    grid-column: 1 / -1;
    text-align: center;
    padding: 30px 14px;
    color: var(--color-muted);
    font-size: 13px;
    background: var(--color-bg);
    border-radius: 12px;
    border: 1px dashed var(--color-border);
}

/* انیمیشن بارگذاری */
.loading-shimmer {
    opacity: 0.5;
    pointer-events: none;
    transition: opacity 0.2s;
}

@media (max-width: 768px) {
    .dual-split-wrapper {
        grid-template-columns: 1fr;
        gap: 32px;
    }
    .center-divider {
        height: 1px;
        width: 100%;
        margin: 8px 0;
    }
    .center-divider::after {
        top: 50%;
        left: 50%;
    }
    .home-stand-branch-box {
        flex-direction: column;
        align-items: stretch;
    }
    .home-branch-select {
        width: 100%;
    }
}
@media (max-width: 420px) {
    .cards-subgrid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="creative-section" id="homeStandSection">

    <div class="creative-title-wrapper">
        <h2 class="creative-title">سفارش استند و کارت</h2>
        <p class="creative-subtitle">
            در لحظات شادباش و سوگواری، با سفارش استندهای خیریه مکسا، پیام همدلی خود را ماندگار نموده و هزینه آن را به درمان رایگان بیماران مبتلا به سرطان اختصاص دهید.
        </p>
    </div>

    <!-- باکس انتخاب شعبه -->
    <div class="home-stand-branch-box">
        <div class="branch-select-label-wrap">
            <div class="branch-select-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="branch-select-label-text">
                <strong>انتخاب شعبه یا شهر ارائه‌دهنده استند</strong>
                <span>طرح‌ها و مبالغ استند متناسب با هر شعبه بارگذاری می‌شوند</span>
            </div>
        </div>

        <select id="homeBranchSelect" class="home-branch-select" aria-label="انتخاب شعبه ارائه‌دهنده استند">
            <?php if (!empty($homeBranches)): ?>
                <?php foreach ($homeBranches as $b): ?>
                    <?php 
                        $pName = trim((string)($b['province'] ?: $b['name']));
                        $cityName = trim((string)($b['city'] ?? ''));
                        $label = 'شعبه ' . htmlspecialchars($b['name']) . ($cityName ? ' (' . htmlspecialchars($cityName) . ')' : '');
                    ?>
                    <option value="<?= htmlspecialchars($pName) ?>" <?= ($pName === $defaultProvince) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="تهران">شعبه تهران</option>
                <option value="اصفهان">شعبه اصفهان</option>
            <?php endif; ?>
        </select>
    </div>

    <!-- یادآوری محدوده شهری (Notice) -->
    <div class="home-stand-notice">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <strong>نکته مهم:</strong> ارسال، تحویل و استقرار استندهای خیریه مکسا صرفاً در محدوده شهری شعبه انتخابی انجام می‌پذیرد و امکان پذیرش سفارش برای مناطق خارج از محدوده تحت پوشش وجود ندارد.
        </div>
    </div>

    <!-- گرید استندها (تبریک و تسلیت) -->
    <div class="dual-split-wrapper" id="homeDualSplit">

        <!-- ستون تبریک -->
        <div class="column-block column-congrats">
            <div class="block-header congrats-header">
                <span class="badge"></span>
                <h3>خدمات تبریک و شادباش</h3>
            </div>
            
            <div class="cards-subgrid" id="homeCongratsGrid">
                <!-- فال‌بک اولیه قبل از بارگذاری API -->
                <div class="creative-card-item">
                    <div class="creative-card">
                        <img src="/uploads/stand/happy/1.jpg" alt="استند تبریک" onerror="this.src='/dashboard/components/event-cards/images/1.png'">
                    </div>
                    <div class="stand-card-title">طرح ترنم بهار</div>
                    <div class="stand-card-price">۱٬۵۰۰٬۰۰۰ تومان</div>
                    <a href="/stand-order.php?type=congrats" class="creative-action-btn">سفارش استند</a>
                </div>
                <div class="creative-card-item">
                    <div class="creative-card">
                        <img src="/uploads/stand/happy/2.jpg" alt="استند تبریک" onerror="this.src='/dashboard/components/event-cards/images/2.png'">
                    </div>
                    <div class="stand-card-title">طرح گل و پروانه</div>
                    <div class="stand-card-price">۱٬۸۰۰٬۰۰۰ تومان</div>
                    <a href="/stand-order.php?type=congrats" class="creative-action-btn">سفارش استند</a>
                </div>
            </div>
        </div>

        <div class="center-divider"></div>

        <!-- ستون تسلیت -->
        <div class="column-block column-condolence">
            <div class="block-header condolence-header">
                <span class="badge"></span>
                <h3>ابراز همدردی و تسلیت</h3>
            </div>
            
            <div class="cards-subgrid" id="homeCondolenceGrid">
                <!-- فال‌بک اولیه قبل از بارگذاری API -->
                <div class="creative-card-item">
                    <div class="creative-card">
                        <img src="/uploads/stand/sad/1.jpg" alt="استند تسلیت" onerror="this.src='/dashboard/components/event-cards/images/3.png'">
                    </div>
                    <div class="stand-card-title">طرح یادمان مهر</div>
                    <div class="stand-card-price">۱٬۵۰۰٬۰۰۰ تومان</div>
                    <a href="/stand-order.php?type=condolence" class="creative-action-btn">سفارش استند</a>
                </div>
                <div class="creative-card-item">
                    <div class="creative-card">
                        <img src="/uploads/stand/sad/2.jpg" alt="استند تسلیت" onerror="this.src='/dashboard/components/event-cards/images/4.png'">
                    </div>
                    <div class="stand-card-title">طرح شمع و نیلوفر</div>
                    <div class="stand-card-price">۱٬۸۰۰٬۰۰۰ تومان</div>
                    <a href="/stand-order.php?type=condolence" class="creative-action-btn">سفارش استند</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
(function() {
    const branchSelect = document.getElementById('homeBranchSelect');
    const congratsGrid = document.getElementById('homeCongratsGrid');
    const condolenceGrid = document.getElementById('homeCondolenceGrid');
    const dualSplit = document.getElementById('homeDualSplit');

    if (!branchSelect || !congratsGrid || !condolenceGrid) return;

    function faNumber(num) {
        return (num || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, "٬")
            .replace(/[0-9]/g, d => "۰۱۲۳۴۵۶۷۸۹"[d]);
    }

    // بارگذاری لیست استان‌های دارای استند فعال از API جهت تکمیل گزینه‌ها
    async function syncProvinces() {
        try {
            const res = await fetch('/api/stands/provinces');
            const data = await res.json();
            const provList = Array.isArray(data.data) ? data.data : (data.data?.provinces || []);
            if (data.success && provList.length > 0) {
                const currentVal = branchSelect.value;
                branchSelect.innerHTML = '';
                provList.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.province;
                    opt.textContent = 'شعبه ' + item.province + (item.cities && item.cities[0] ? ' (' + item.cities[0] + ')' : '');
                    if (item.province === currentVal) opt.selected = true;
                    branchSelect.appendChild(opt);
                });
                if (!branchSelect.value && provList[0]) {
                    branchSelect.value = provList[0].province;
                }
            }
        } catch (e) {
            // استفاده از آپشن‌های رندر شده توسط PHP در صورت بروز خطای شبکه
        }
    }

    // بارگذاری استندهای اختصاصی شعبه انتخابی
    async function loadHomeStands(province) {
        if (!province) return;
        dualSplit.classList.add('loading-shimmer');

        try {
            const res = await fetch('/api/stands?province=' + encodeURIComponent(province));
            const data = await res.json();
            const standsList = Array.isArray(data.data) ? data.data : (data.data?.stands || []);

            if (data.success && standsList.length > 0) {
                renderStands(standsList, province);
            } else {
                renderEmpty();
            }
        } catch (err) {
            console.warn('Stands loading error:', err);
        } finally {
            dualSplit.classList.remove('loading-shimmer');
        }
    }

    function renderStands(stands, province) {
        const congratsStands = stands.filter(s => s.stand_type === 'congrats');
        const condolenceStands = stands.filter(s => s.stand_type === 'condolence');

        // رندر تبریک
        if (congratsStands.length > 0) {
            congratsGrid.innerHTML = congratsStands.map(s => `
                <div class="creative-card-item">
                    <div class="creative-card">
                        <img src="${s.image}" alt="${s.title}" onerror="this.src='/uploads/stand/happy/1.jpg'">
                    </div>
                    <div class="stand-card-title">${s.title}</div>
                    <div class="stand-card-price">${faNumber(s.unit_price)} تومان</div>
                    <a href="/stand-order.php?province=${encodeURIComponent(province)}&stand_id=${s.id}" class="creative-action-btn">
                        سفارش استند
                    </a>
                </div>
            `).join('');
        } else {
            congratsGrid.innerHTML = '<div class="empty-stands-msg">در حال حاضر طرح تبریک فعالی برای این شعبه ثبت نشده است.</div>';
        }

        // رندر تسلیت
        if (condolenceStands.length > 0) {
            condolenceGrid.innerHTML = condolenceStands.map(s => `
                <div class="creative-card-item">
                    <div class="creative-card">
                        <img src="${s.image}" alt="${s.title}" onerror="this.src='/uploads/stand/sad/1.jpg'">
                    </div>
                    <div class="stand-card-title">${s.title}</div>
                    <div class="stand-card-price">${faNumber(s.unit_price)} تومان</div>
                    <a href="/stand-order.php?province=${encodeURIComponent(province)}&stand_id=${s.id}" class="creative-action-btn">
                        سفارش استند
                    </a>
                </div>
            `).join('');
        } else {
            condolenceGrid.innerHTML = '<div class="empty-stands-msg">در حال حاضر طرح تسلیت فعالی برای این شعبه ثبت نشده است.</div>';
        }
    }

    function renderEmpty() {
        congratsGrid.innerHTML = '<div class="empty-stands-msg">هیچ استندی برای این شعبه در دسترس نیست.</div>';
        condolenceGrid.innerHTML = '<div class="empty-stands-msg">هیچ استندی برای این شعبه در دسترس نیست.</div>';
    }

    // لیسنر تغییر شعبه
    branchSelect.addEventListener('change', function() {
        loadHomeStands(this.value);
    });

    // راه‌اندازی اولیه
    syncProvinces().then(() => {
        loadHomeStands(branchSelect.value);
    });
})();
</script>
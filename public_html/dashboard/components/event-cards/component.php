<style>
/* تعریف پالت رنگی اختصاصی شما */
:root {
    --color-primary: #007b7a;
    --color-primary-dark: #006665;
    --color-primary-light: #4fb2b0;
    --color-secondary: #f4a61e;
    --color-text: #2f3437;
    --color-muted: #9d9d9d;
    --color-border: #e6e8ea;
    --color-bg: #f8f9fa;
    --color-surface: #ffffff;
}

/* استایل کل بخش */
.creative-section {
    max-width: 1000px;
    margin: 40px auto;
    padding: 32px 24px;
    font-family: 'Vazirmatn', Tahoma, sans-serif;
    direction: rtl;
    background: var(--color-surface);
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
}

/* تیتر اصلی با دیزاین متفاوت و خطی */
.creative-title-wrapper {
    text-align: center;
    margin-bottom: 48px;
    position: relative;
}

.creative-title {
    font-size: 26px;
    font-weight: 900;
    color: var(--color-text);
    margin: 0;
    display: inline-block;
    position: relative;
    padding: 0 16px;
}

.creative-title::before, .creative-title::after {
    content: "";
    position: absolute;
    top: 50%;
    width: 40px;
    height: 2px;
    background: var(--color-primary-light);
}
.creative-title::before { right: -45px; }
.creative-title::after { left: -45px; }

/* ساختار تفکیک‌کننده خلاقانه (گرید دو قطبی) */
.dual-split-wrapper {
    display: grid;
    grid-template-columns: 1fr 1px 1fr;
    gap: 32px;
    position: relative;
}

/* خط عمودی مرکزی هوشمند با دایره میانی */
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
    font-size: 11px;
    font-weight: 700;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* هدرهای ستون‌ها با طراحی مدرن مایل */
.column-block {
    display: flex;
    flex-direction: column;
}

.block-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}

.block-header .badge {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.congrats-header .badge { background: var(--color-primary); }
.condolence-header .badge { background: var(--color-muted); }

.block-header h3 {
    font-size: 16px;
    font-weight: 800;
    margin: 0;
    color: var(--color-text);
}

/* گرید کارت‌های باریک و مینی‌مال */
.cards-subgrid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

/* کارت‌های مینی‌مال با عمق سه‌بعدی متحرک */
.creative-card-item {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.creative-card {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    aspect-ratio: 3 / 4; 
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1), box-shadow 0.4s ease;
}

.creative-card img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    position: absolute;
    inset: 0;
    z-index: 1;
    transition: transform 0.4s ease;
}

/* دکمه اکشن مینی‌مال پایدار با استایل لوکس */
.creative-action-btn {
    background: var(--color-surface);
    color: var(--color-text);
    text-align: center;
    padding: 10px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid var(--color-border);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
}

/* افکت تعاملی جذاب دسکتاپ برای فرار از سادگی */
@media (hover: hover) and (pointer: fine) {
    .creative-card-item:hover .creative-card {
        transform: translateY(-6px);
        box-shadow: 0 12px 24px rgba(0, 77, 76, 0.06);
    }
    
    .creative-card-item:hover .creative-card img {
        transform: scale(1.04);
    }

    /* هاور اختصاصی بخش تبریک */
    .column-congrats .creative-card-item:hover .creative-action-btn {
        background: var(--color-primary);
        color: var(--color-surface);
        border-color: var(--color-primary);
        box-shadow: 0 4px 14px rgba(0, 123, 122, 0.3);
    }

    /* هاور اختصاصی بخش تسلیت */
    .column-condolence .creative-card-item:hover .creative-action-btn {
        background: var(--color-text);
        color: var(--color-surface);
        border-color: var(--color-text);
        box-shadow: 0 4px 14px rgba(47, 52, 55, 0.3);
    }
}

/* رسپانسیو هوشمند موبایل */
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
    
    .cards-subgrid {
        gap: 12px;
    }
    
    .creative-title {
        font-size: 20px;
    }
}

@media (max-width: 380px) {
    .creative-section {
        padding: 24px 14px;
        margin: 24px auto;
    }
    .cards-subgrid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="creative-section">

    <div class="creative-title-wrapper">
        <h2 class="creative-title">سفارش استند</h2>
    </div>

    <div class="dual-split-wrapper">

        <div class="column-block column-congrats">
            <div class="block-header congrats-header">
                <span class="badge"></span>
                <h3>خدمات تبریک و شادباش</h3>
            </div>
            
            <div class="cards-subgrid">
                <div class="creative-card-item">
                    <div class="creative-card">
                        <img src="{{image1}}" alt="بنر تبریک">
                    </div>
                    <a href="#" class="creative-action-btn">سفارش استند</a>
                </div>
                <div class="creative-card-item">
                    <div class="creative-card">
                        <img src="{{image2}}" alt="بنر تبریک">
                    </div>
                    <a href="#" class="creative-action-btn">سفارش استند</a>
                </div>
            </div>
        </div>

        <div class="center-divider"></div>

        <div class="column-block column-condolence">
            <div class="block-header condolence-header">
                <span class="badge"></span>
                <h3>ابراز همدردی و تسلیت</h3>
            </div>
            
            <div class="cards-subgrid">
                <div class="creative-card-item">
                    <div class="creative-card">
                        <img src="{{image3}}" alt="بنر تسلیت">
                    </div>
                    <a href="#" class="creative-action-btn">سفارش استند</a>
                </div>
                <div class="creative-card-item">
                    <div class="creative-card">
                        <img src="{{image4}}" alt="بنر تسلیت">
                    </div>
                    <a href="#" class="creative-action-btn">سفارش استند</a>
                </div>
            </div>
        </div>

    </div>
</div>
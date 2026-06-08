<div class="page-header-wrapper">
    <header class="page-header-capsule" dir="rtl">
        <h1>تاریخچه و نحوه تاسیس</h1>
        <div class="page-header-breadcrumb">صفحه اصلی / خدمات</div>
    </header>
</div>

<style>
.page-header-capsule {
    background: linear-gradient(180deg, #f8f9fa 0%, #eef3f6 100%);
    border-radius: 50px;
    padding: 50px 30px;
    margin-bottom: 40px;
    text-align: center;
    font-family: 'Vazirmatn', sans-serif;
    animation: pageHeaderIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes pageHeaderIn {
    from { opacity: 0; transform: translateY(-14px); }
    to   { opacity: 1; transform: translateY(0); }
}
@media (prefers-reduced-motion: reduce) {
    .page-header-capsule { animation: none; }
}

.page-header-capsule h1 {
    font-size: 2.5rem;
    margin: 0 0 15px 0;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.4;
}

.page-header-breadcrumb {
    background-color: #00a8a8;
    color: #ffffff;
    padding: 6px 20px;
    border-radius: 50px;
    font-size: 0.85rem;
    display: inline-block;
    line-height: 1.8;
}

@media (max-width: 768px) {
    .page-header-capsule {
        border-radius: 30px;
        padding: 30px 15px;
        margin-bottom: 30px;
    }

    .page-header-capsule h1 {
        font-size: 1.8rem;
    }

    .page-header-breadcrumb {
        font-size: 0.8rem;
        padding: 5px 16px;
    }
}

@media (max-width: 380px) {
    .page-header-capsule {
        border-radius: 24px;
        padding: 24px 12px;
    }
    .page-header-capsule h1 {
        font-size: 1.45rem;
    }
}
</style>



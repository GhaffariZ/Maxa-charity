<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>سرطان پستان – نسخه عمومی</title>

<style>

  body {
    font-family: 'Vazirmatn', sans-serif !important;
  }
/* =============================
   PAGE SCOPE
============================= */
.leukemia-page {
    direction: rtl;
    background: #ffffff;
}

.leukemia-container {
    max-width: 1200px;
    margin: auto;
    padding: 20px;
}

/* ===== PAGE HEADER ===== */
.leukemia-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.leukemia-title {
    font-size: 28px;
    font-weight: 700;
}

/* ===== BUTTON ===== */
.professional-btn {
    text-decoration: none;
    background: #00CED1;
    color: #003b3c;
    padding: 10px 18px;
    border-radius: 6px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: 0.2s;
}

.professional-btn::after {
    content: "←";
    font-size: 18px;
}

.professional-btn:hover {
    background: #00b7ba;
}

/* ===== GRID ===== */
.leukemia-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

/* ===== CARD ===== */
.leukemia-card {
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
    background: #fff;
}

/* ===== CARD HEADER ===== */
.leukemia-header {
    padding: 16px;
    font-weight: 700;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.leukemia-header::after {
    content: "";
    position: absolute;
    width: 160px;
    height: 160px;
    background: rgba(255,255,255,0.08);
    border-radius: 24px;
    top: -40px;
    left: -40px;
    transform: rotate(25deg);
}

.leukemia-toggle {
    display: none;
    font-size: 22px;
}

/* ===== CONTENT ===== */
.leukemia-content {
    padding: 16px;
    line-height: 1.9;
    text-align: justify;
    text-justify: inter-word;
}

.leukemia-content ul {
    padding-right: 18px;
    margin: 0;
}

.leukemia-content li {
    margin-bottom: 8px;
}

.section-title {
    margin: 16px 0 8px;
    font-size: 18px;
    font-weight: 700;
    border-right: 4px solid #1e5aa8;
    padding-right: 8px;
}

/* =============================
   SEMANTIC MEDICAL COLORS
============================= */
.overview   { background: #1e5aa8; } /* Awareness */
.diagnosis  { background: #6f42c1; } /* Diagnosis */
.treatment  { background: #198754; } /* Treatment */
.screening  { background: #fd7e14; } /* Prevention */
.support    { background: #0dcaf0; color: #00323a; } /* Support */
.research   { background: #0b3c5d; } /* Research */

/* =============================
   MOBILE
============================= */
@media (max-width: 768px) {
    .leukemia-grid {
        grid-template-columns: 1fr;
    }

    .leukemia-header {
        cursor: pointer;
    }

    .leukemia-toggle {
        display: block;
    }

    .leukemia-content {
        display: none;
    }

    .leukemia-card.open .leukemia-content {
        display: block;
    }

    .leukemia-card.open .leukemia-toggle {
        transform: rotate(45deg);
    }

    .professional-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>

<body>

<div class="leukemia-page">
<div class="leukemia-container">

<div class="leukemia-page-header">
    <div class="leukemia-title">سرطان پستان – نسخه عمومی</div>
    <a href="#" class="professional-btn">نسخه متخصصین</a>
</div>

<div class="leukemia-grid">

<!-- مرور کلی -->
<div class="leukemia-card">
    <div class="leukemia-header overview">
        <span>مرور کلی</span>
        <span class="leukemia-toggle">+</span>
    </div>
    <div class="leukemia-content">
        <p>
            سرطان پستان یکی از شایع‌ترین سرطان‌ها در زنان است که تشخیص زودهنگام آن
            نقش مهمی در بهبود نتایج درمان دارد.
        </p>

        <h4 class="section-title">انواع سرطان پستان</h4>
        <ul>
            <li>کارسینوم مجرایی درجا (DCIS)</li>
            <li>کارسینوم مجرایی تهاجمی (IDC)</li>
            <li>کارسینوم لوبولار تهاجمی (ILC)</li>
            <li>سرطان پستان ارثی</li>
            <li>سرطان پستان التهابی (IBC)</li>
            <li>سرطان پستان در مردان</li>
        </ul>
    </div>
</div>

<!-- تشخیص -->
<div class="leukemia-card">
    <div class="leukemia-header diagnosis">
        <span>تشخیص</span>
        <span class="leukemia-toggle">+</span>
    </div>
    <div class="leukemia-content">
        <ul>
            <li>معاینه پستان</li>
            <li>ماموگرافی</li>
            <li>سونوگرافی</li>
            <li>MRI</li>
            <li>بیوپسی</li>
        </ul>
    </div>
</div>

<!-- درمان -->
<div class="leukemia-card">
    <div class="leukemia-header treatment">
        <span>درمان</span>
        <span class="leukemia-toggle">+</span>
    </div>
    <div class="leukemia-content">
        <ul>
            <li>شیمی درمانی</li>
            <li>پرتو درمانی</li>
            <li>جراحی</li>
            <li>هورمون تراپی</li>
            <li>ایمونوتراپی</li>
        </ul>
    </div>
</div>

<!-- غربالگری -->
<div class="leukemia-card">
    <div class="leukemia-header screening">
        <span>غربالگری</span>
        <span class="leukemia-toggle">+</span>
    </div>
    <div class="leukemia-content">
        <ul>
            <li>معاینه پستان</li>
            <li>ماموگرافی</li>
            <li>سونوگرافی</li>
            <li>MRI</li>
            <li>بیوپسی</li>
        </ul>
    </div>
</div>

<!-- مطالب تکمیلی -->
<div class="leukemia-card">
    <div class="leukemia-header support">
        <span>مطالب تکمیلی</span>
        <span class="leukemia-toggle">+</span>
    </div>
    <div class="leukemia-content">
        <ul>
            <li>علائم اسکلتی-عضلانی مهارکننده‌های آروماتاز</li>
            <li>سرطان پستان و باروری</li>
            <li>سرطان پستان و بزرگسالان مسن</li>
            <li>سرطان پستان و سلامت جنسی</li>
            <li>سرطان پستان و زنان جوان</li>
            <li>بارداری و سرطان پستان</li>
            <li>حمایت روانی-اجتماعی بیماران</li>
            <li>گیرنده‌های تومور</li>
        </ul>
    </div>
</div>

<!-- مقالات -->
<div class="leukemia-card">
    <div class="leukemia-header research">
        <span>مقالات و پژوهش‌های مرتبط</span>
        <span class="leukemia-toggle">+</span>
    </div>
    <div class="leukemia-content">
        آخرین مطالعات و مقالات علمی درباره سرطان پستان.
    </div>
</div>

</div>
</div>
</div>
    <!-- Vazirmatn Variable Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

<script>
document.querySelectorAll(".leukemia-header").forEach(header => {
    header.addEventListener("click", () => {
        if (window.innerWidth <= 768) {
            header.parentElement.classList.toggle("open");
        }
    });
});
</script>

</body>
</html>

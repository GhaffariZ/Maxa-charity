<?php
require_once __DIR__ . '/../_guard.php';
dash_require('pages');
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انتخاب طرح و ثبت اطلاعات</title>
    <style>
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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--color-bg);
            color: var(--color-text);
            padding: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: var(--color-surface);
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--color-border);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.02);
            width: 100%;
            max-width: 1100px;
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.25rem;
            color: var(--color-primary-dark);
        }

        /* چیدمان اصلی دسکتاپ: هم‌ترازی کامل بخش پایینی ستون‌ها */
        .main-layout {
            display: grid;
            grid-template-columns: 105px 230px 1fr;
            gap: 24px;
            align-items: stretch;
        }

        @media (max-width: 992px) {
            .main-layout {
                grid-template-columns: 1fr 1.5fr;
                align-items: start;
            }
        }

        @media (max-width: 768px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
        }

        /* ستون طرح‌ها */
        .gallery-vertical {
            display: flex;
            flex-direction: column;
            gap: 4px;
            height: 100%;
        }

        .image-list-vertical {
            display: flex;
            flex-direction: column;
            gap: 10px;
            height: 480px; /* تراز با پیش‌نمایش */
            overflow-y: auto;
            padding-left: 2px;
        }

        .image-card {
            border: 2px solid var(--color-border);
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s, border-color 0.2s;
            aspect-ratio: 1 / 1;
            background: var(--color-bg);
            flex-shrink: 0;
            width: 100px;
            height: 100px;
        }

        .image-card:hover {
            border-color: var(--color-secondary);
            transform: scale(1.04);
        }

        .image-card.selected {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(0, 123, 122, 0.15);
        }

        .image-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ستون پیش‌نمایش مستطیلی کشیده */
        .preview-section {
            display: flex;
            flex-direction: column;
            gap: 4px;
            height: 100%;
        }

        .large-preview {
            border: 1px solid var(--color-border);
            border-radius: 12px;
            background: var(--color-bg);
            height: 480px; /* تراز اصلی پایینی روی این عدد فیکس است */
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            box-shadow: 0 6px 12px rgba(0,0,0,0.02);
        }

        .large-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            display: none;
        }

        .large-preview .placeholder-text {
            color: var(--color-muted);
            font-size: 0.8rem;
            text-align: center;
            padding: 15px;
            line-height: 1.5;
        }

        /* ستون فرم اطلاعات */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            height: 100%;
            align-content: space-between; /* پخش و چیدمان متغیرها تا انتهای خط پایینی */
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--color-text);
        }

        input, textarea {
            padding: 7px 12px;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--color-text);
            outline: none;
            transition: border-color 0.2s;
            background: var(--color-bg);
        }

        input:focus, textarea:focus {
            border-color: var(--color-primary);
            background: var(--color-surface);
        }

        input[readonly] {
            background: #f1f3f5;
            color: #5c636a;
            cursor: not-allowed;
        }

        /* کنترلر تعداد */
        .quantity-control {
            display: flex;
            align-items: center;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            overflow: hidden;
            background: var(--color-bg);
            height: 34px;
        }

        .quantity-btn {
            background: none;
            border: none;
            width: 35px;
            height: 100%;
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--color-primary);
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover {
            background: var(--color-primary-light);
            color: white;
        }

        .quantity-input {
            flex: 1;
            border: none;
            border-radius: 0;
            text-align: center;
            background: transparent;
            font-weight: bold;
            padding: 0;
            font-size: 1rem;
        }

        /* باکس قیمت کل */
        .price-summary-box {
            background: rgba(0, 123, 122, 0.04);
            border: 1px dashed var(--color-primary-light);
            border-radius: 8px;
            padding: 0 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 2px;
            height: 34px;
        }

        .total-price {
            color: var(--color-primary-dark);
            font-size: 0.95rem;
            font-weight: bold;
        }

        textarea {
            resize: none;
        }
        
        #message {
            height: 85px;
        }
        
        #address {
            height: 42px;
        }

        button[type="submit"] {
            grid-column: 1 / -1;
            padding: 10px;
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        button[type="submit"]:hover {
            background: var(--color-primary-dark);
        }

        .toast {
            position: fixed;
            bottom: 24px;
            left: 24px;
            background: var(--color-primary-dark);
            color: white;
            padding: 14px 24px;
            border-radius: 8px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(150%);
            transition: transform 0.3s ease;
            font-size: 0.95rem;
            z-index: 1000;
        }

        .toast.error { background: #db2777; }
        .toast.show { transform: translateY(0); }
    </style>
</head>
<body>

<div class="container">
    <h2>طرح مورد نظر را انتخاب و اطلاعات را تکمیل کنید</h2>
    
    <form id="submissionForm" class="main-layout">
        
        <div class="gallery-vertical">
    <label>طرح‌ها</label>
    <div class="image-list-vertical">
        
        <div class="image-card" data-id="design1.jpg" data-price="150000" data-full="./happy1.jpg">
            <img src="./happy1.jpg" alt="طرح ۱">
        </div>
        
        <div class="image-card" data-id="design2.jpg" data-price="180000" data-full="./happy2.jpg">
            <img src="./happy2.jpg" alt="طرح ۲"> 
        </div>
        
        <div class="image-card" data-id="design3.jpg" data-price="200000" data-full="./sad1.jpg">
            <img src="./sad1.jpg" alt="طرح ۳">
        </div>
        
        <div class="image-card" data-id="design4.jpg" data-price="250000" data-full="./sad3.jpg">
            <img src="./sad3.jpg" alt="طرح ۴">
        </div>

    </div>
</div>

        <div class="preview-section">
            <label>پیش‌نمایش استند</label>
            <div class="large-preview" id="largePreviewContainer">
                <span class="placeholder-text" id="placeholderText">لطفاً یک طرح انتخاب کنید.</span>
                <img id="previewImage" src="" alt="پیش‌نمایش بزرگ">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="date">تاریخ</label>
                <input type="text" id="date" placeholder="مثال: ۱۴۰۵/۰۳/۰۵" required>
            </div>
            
            <div class="form-group">
                <label for="from_user">از طرف</label>
                <input type="text" id="from_user" required>
            </div>

            <div class="form-group">
                <label>تعداد</label>
                <div class="quantity-control">
                    <button type="button" class="quantity-btn" id="btnMinus">−</button>
                    <input type="number" id="quantity" class="quantity-input" value="1" min="1" readonly>
                    <button type="button" class="quantity-btn" id="btnPlus">+</button>
                </div>
            </div>

            <div class="form-group">
                <label for="to_user">به آقای</label>
                <input type="text" id="to_user" required>
            </div>

            <div class="form-group full-width">
                <label for="message">متن</label>
                <textarea id="message" required placeholder="متن مورد نظر خود را اینجا بنویسید..."></textarea>
            </div>

            <div class="form-group full-width">
                <label for="address">آدرس ارسال</label>
                <textarea id="address" required placeholder="نشانی دقیق جهت ارسال استند..."></textarea>
            </div>

            <div class="form-group">
                <label for="unit_price">قیمت واحد (تومان)</label>
                <input type="text" id="unit_price" readonly value="۰">
            </div>
            
            <div class="form-group">
                <label>قیمت کل</label>
                <div class="price-summary-box">
                    <span class="total-price" id="total_price_display">۰ تومان</span>
                </div>
            </div>

            <button type="submit">ثبت و ارسال نهایی اطلاعات</button>
        </div>
    </form>
</div>

<div id="toast" class="toast">عملیات با موفقیت انجام شد.</div>

<script>
    let selectedImage = null;
    let currentUnitPrice = 0;

    const quantityInput = document.getElementById('quantity');
    const unitPriceInput = document.getElementById('unit_price');
    const totalPriceDisplay = document.getElementById('total_price_display');
    const previewImage = document.getElementById('previewImage');
    const placeholderText = document.getElementById('placeholderText');

    function updateTotalPrice() {
        const qty = parseInt(quantityInput.value) || 1;
        const total = currentUnitPrice * qty;
        totalPriceDisplay.textContent = total.toLocaleString('fa-IR') + ' تومان';
    }

    document.getElementById('btnPlus').addEventListener('click', () => {
        quantityInput.value = parseInt(quantityInput.value) + 1;
        updateTotalPrice();
    });

    document.getElementById('btnMinus').addEventListener('click', () => {
        let val = parseInt(quantityInput.value);
        if (val > 1) {
            quantityInput.value = val - 1;
            updateTotalPrice();
        }
    });

    document.querySelectorAll('.image-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.image-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            
            selectedImage = card.getAttribute('data-id');
            currentUnitPrice = parseInt(card.getAttribute('data-price')) || 0;
            
            const fullImgUrl = card.getAttribute('data-full');
            previewImage.src = fullImgUrl;
            previewImage.style.display = 'block';
            placeholderText.style.display = 'none';

            unitPriceInput.value = currentUnitPrice.toLocaleString('fa-IR');
            updateTotalPrice();
        });
    });

    function showToast(message, isError = false) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        if (isError) toast.classList.add('error');
        else toast.classList.remove('error');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }

    document.getElementById('submissionForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!selectedImage) {
            showToast('لطفاً ابتدا یکی از تصاویر را انتخاب کنید.', true);
            return;
        }

        const formData = {
            image: selectedImage,
            date: document.getElementById('date').value,
            quantity: parseInt(quantityInput.value) || 1,
            from_user: document.getElementById('from_user').value,
            to_user: document.getElementById('to_user').value,
            message: document.getElementById('message').value,
            address: document.getElementById('address').value
        };

        try {
            const response = await fetch('stand-order-save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': <?= json_encode($csrfToken) ?>
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                showToast('اطلاعات با موفقیت ذخیره شد!');
                document.getElementById('submissionForm').reset();
                document.querySelectorAll('.image-card').forEach(c => c.classList.remove('selected'));
                
                selectedImage = null;
                currentUnitPrice = 0;
                unitPriceInput.value = '۰';
                totalPriceDisplay.textContent = '۰ تومان';
                previewImage.style.display = 'none';
                placeholderText.style.display = 'block';
                quantityInput.value = 1;
            } else {
                showToast('خطا: ' + result.message, true);
            }
        } catch (error) {
            showToast('خطا در برقراری ارتباط با سرور.', true);
        }
    });
</script>
</body>
</html>
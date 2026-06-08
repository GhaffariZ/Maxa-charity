<?php
declare(strict_types=1);
require_once '/home/erfantey/meetings/config.php';

$message = "";

// پردازش فرم در صورت ارسال
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction(); // شروع تراکنش

        // ۱. درج جلسه
        $stmt_session = $pdo->prepare("
            INSERT INTO sessions (session_date, day_of_week, session_time, title, attendees) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_session->execute([
            $_POST['session_date'],
            $_POST['day_of_week'],
            $_POST['session_time'],
            $_POST['session_title'],
            $_POST['session_attendees']
        ]);
        $sessionId = $pdo->lastInsertId();

        // ۲. درج مصوبات
        $stmt_decision = $pdo->prepare("
            INSERT INTO decisions (session_id, description, responsible_person, deadline) 
            VALUES (?, ?, ?, ?)
        ");
        
        $decisions_inserted = 0;
        if (isset($_POST['decision_description']) && is_array($_POST['decision_description'])) {
            foreach ($_POST['decision_description'] as $key => $description) {
                if (!empty(trim($description))) { // اطمینان از اینکه فیلد خالی نیست
                    $responsible = $_POST['decision_responsible'][$key] ?? null;
                    $deadline = $_POST['decision_deadline'][$key] ?? null;
                    
                    $stmt_decision->execute([
                        $sessionId,
                        $description,
                        $responsible,
                        ($deadline ?: null) // اگر خالی بود NULL در دیتابیس ذخیره شود
                    ]);
                    $decisions_inserted++;
                }
            }
        }

        $pdo->commit(); // ثبت نهایی تراکنش
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>موفقیت!</strong> جلسه و ' . $decisions_inserted . ' مصوبه با موفقیت ثبت شدند.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';

    } catch (Exception $e) {
        $pdo->rollBack(); // در صورت خطا، تراکنش را لغو کن
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>خطا!</strong> در ثبت اطلاعات مشکلی پیش آمد: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    }
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <title>ثبت جلسه و مصوبات</title>
    <style>
        /* استایل‌های اضافی برای بهبود ظاهر */
        .app-main .container-fluid { max-width: 1000px; }
        .card-header { background-color: #f8f9fa; border-bottom: 1px solid #e9ecef; }
        .decision-item { margin-bottom: 1rem; }
        .decision-item:last-child { margin-bottom: 0; }
        .remove-decision-btn { margin-top: 2px; } /* تنظیم فاصله برای دکمه حذف */
        .form-label { font-weight: 500; }
        .alert { margin-top: 1rem; }
    </style>
</head>
<body>
<div class="app">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="app-main">
        <div class="app-topbar">
            <div class="topbar-title">ثبت جلسه جدید و مصوبات</div>
        </div>
        
        <div class="container-fluid py-4">
            <?= $message ?>

            <div class="card shadow-lg mb-4">
                <div class="card-header">
                    <h5 class="mb-0">اطلاعات کلی جلسه</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="session_date" class="form-label">تاریخ جلسه</label>
                            <input type="date" id="session_date" name="session_date" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label for="day_of_week" class="form-label">روز هفته</label>
                            <input type="text" id="day_of_week" name="day_of_week" class="form-control" placeholder="مثال: شنبه">
                        </div>
                        <div class="col-md-2">
                            <label for="session_time" class="form-label">ساعت</label>
                            <input type="time" id="session_time" name="session_time" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label for="session_title" class="form-label">عنوان جلسه</label>
                            <input type="text" id="session_title" name="session_title" class="form-control" placeholder="عنوان جلسه" required>
                        </div>
                        <div class="col-12">
                            <label for="session_attendees" class="form-label">حاضرین</label>
                            <input type="text" id="session_attendees" name="session_attendees" class="form-control" placeholder="نام‌ها را با کاما جدا کنید">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-lg">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">مصوبات جلسه</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addDecision()">+ افزودن مصوبه</button>
                </div>
                <div class="card-body">
                    <form id="decisionsForm" method="POST">
                        <input type="hidden" name="session_date" value="">
                        <input type="hidden" name="day_of_week" value="">
                        <input type="hidden" name="session_time" value="">
                        <input type="hidden" name="session_title" value="">
                        <input type="hidden" name="session_attendees" value="">

                        <div id="decisionsContainer">
                            <!-- اولین مصوبه (برای جلوگیری از خالی بودن فرم) -->
                            <div class="decision-item row g-2 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">شرح مصوبه</label>
                                    <textarea name="decision_description[]" class="form-control" rows="2" placeholder="جزئیات مصوبه"></textarea>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">مسئول</label>
                                    <input type="text" name="decision_responsible[]" class="form-control" placeholder="نام مسئول">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">مهلت اجرا</label>
                                    <input type="date" name="decision_deadline[]" class="form-control">
                                </div>
                                <div class="col-md-1 d-flex align-items-end remove-decision-btn">
                                    <!-- دکمه حذف بعد از اولین آیتم نمایش داده شود -->
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 py-2">ثبت نهایی جلسه و مصوبات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionForm = document.getElementById('sessionForm');
    const decisionsForm = document.getElementById('decisionsForm');
    const decisionsContainer = document.getElementById('decisionsContainer');
    const sessionInputs = ['session_date', 'day_of_week', 'session_time', 'session_title', 'session_attendees'];

    // انتقال اطلاعات جلسه به فرم مصوبات قبل از submit
    sessionForm.addEventListener('input', function(event) {
        sessionInputs.forEach(inputId => {
            const inputElement = document.getElementById(inputId);
            const hiddenInput = decisionsForm.querySelector(`input[name="${inputId}"]`);
            if (hiddenInput) {
                hiddenInput.value = inputElement.value;
            }
        });
    });
    
    // تنظیم اولیه مقادیر hidden inputs
    sessionInputs.forEach(inputId => {
        const inputElement = document.getElementById(inputId);
        const hiddenInput = decisionsForm.querySelector(`input[name="${inputId}"]`);
        if (hiddenInput) {
            hiddenInput.value = inputElement.value;
        }
    });

    // فعال کردن دکمه حذف برای مصوبات بعدی
    decisionsContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-decision-btn')) {
            const itemToRemove = event.target.closest('.decision-item');
            if (itemToRemove && decisionsContainer.querySelectorAll('.decision-item').length > 1) {
                itemToRemove.remove();
            } else if (decisionsContainer.querySelectorAll('.decision-item').length === 1) {
                // اگر تنها آیتم باقی مانده باشد، فیلدها را پاک کن
                const inputs = itemToRemove.querySelectorAll('input, textarea');
                inputs.forEach(input => input.value = '');
                // دکمه حذف را بردار (اگر به اولین آیتم اعمال شود)
                itemToRemove.querySelector('.remove-decision-btn').innerHTML = ''; 
            }
        }
    });
});

function addDecision() {
    const container = document.getElementById('decisionsContainer');
    const decisionItems = container.querySelectorAll('.decision-item');
    
    // اگر فقط یک آیتم خالی داریم، آن را حذف نکنیم
    if (decisionItems.length === 1) {
        const firstItem = decisionItems[0];
        const description = firstItem.querySelector('textarea[name="decision_description[]"]');
        const responsible = firstItem.querySelector('input[name="decision_responsible[]"]');
        const deadline = firstItem.querySelector('input[name="decision_deadline[]"]');
        
        if (!description.value && !responsible.value && !deadline.value) {
            // اگر فیلدهای اولین آیتم خالی است، آن را پاک کن و آیتم جدید اضافه نکن
             firstItem.querySelector('.remove-decision-btn').innerHTML = ''; // اطمینان از نبود دکمه حذف
             return;
        }
    }

    const div = document.createElement('div');
    div.className = 'decision-item row g-2 mb-3';
    div.innerHTML = `
        <div class="col-md-5">
            <label class="form-label">شرح مصوبه</label>
            <textarea name="decision_description[]" class="form-control" rows="2" placeholder="جزئیات مصوبه"></textarea>
        </div>
        <div class="col-md-3">
            <label class="form-label">مسئول</label>
            <input type="text" name="decision_responsible[]" class="form-control" placeholder="نام مسئول">
        </div>
        <div class="col-md-3">
            <label class="form-label">مهلت اجرا</label>
            <input type="date" name="decision_deadline[]" class="form-control">
        </div>
        <div class="col-md-1 d-flex align-items-end remove-decision-btn">
            <button type="button" class="btn btn-danger btn-sm btn-block">X</button>
        </div>
    `;
    container.appendChild(div);
}
</script>
</body>
</html>

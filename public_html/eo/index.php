<?php
declare(strict_types=1);

// اتصال به دیتابیس
require_once '/home/erfantey/meetings/config.php';
$stmt = $pdo->query("SELECT COUNT(*) FROM decisions");
$total_decisions = $stmt->fetchColumn();

$pageTitle = 'داشبورد مدیریت جلسات';
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <!-- استایل اختصاصی -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="app-topbar">
            <div class="topbar-title">پنل مدیرعامل</div>
            <div class="topbar-actions">
                <!-- بعداً: نام کاربر، خروج، اعلان‌ها -->
            </div>
        </div>

        <div class="container-fluid py-4">
            <h4 class="mb-3">خلاصه وضعیت</h4>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body">
                            <div class="text-muted small mb-1">تعداد کل جلسات</div>
                            <div class="fs-3 fw-bold">—</div>
                            <div class="text-muted small mt-1">در حال حاضر فقط نمایشی است</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body">
                            <div class="text-muted small mb-1">مصوبات در انتظار اجرا</div>
                            <div class="fs-3 fw-bold">—</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body">
                            <div class="text-muted small mb-1">مصوبات انجام‌شده</div>
                            <div class="fs-3 fw-bold">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- بخش آخرین مصوبات (بعداً دیتا وصل می‌کنیم) -->
            <div class="card shadow-sm border-0 rounded-3 mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="m-0">آخرین مصوبات</h6>
                        <a href="decisions_manage.php" class="btn btn-sm btn-outline-primary">مشاهده همه</a>
                    </div>
                    <p class="text-muted small mb-0">
                        فعلاً داده‌ای وجود ندارد؛ بعد از ساخت جدول و ثبت مصوبه، اینجا لیست نمایش داده می‌شود.
                    </p>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

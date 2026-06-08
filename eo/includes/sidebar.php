<?php
// برای تعیین active بودن لینک‌ها
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="app-sidebar">
    <div class="sidebar-brand mb-2">
        مدیریت جلسات
    </div>

    <nav class="sidebar-nav">
        <a class="sidebar-link <?= $current === 'index.php' ? 'active' : '' ?>" href="index.php">
            داشبورد
        </a>
        <a class="sidebar-link <?= $current === 'decisions_create.php' ? 'active' : '' ?>" href="decisions_create.php">
            ثبت مصوبه جدید
        </a>
        <a class="sidebar-link <?= $current === 'decisions_manage.php' ? 'active' : '' ?>" href="decisions_manage.php">
            مدیریت مصوبات
        </a>
    </nav>
</aside>

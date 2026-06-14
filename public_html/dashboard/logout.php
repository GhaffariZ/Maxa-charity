<?php
/* خروج از پنل مدیریت */
require_once __DIR__ . '/../core/dashboard-auth.php';
dash_session_start();
dash_logout();
header('Location: /dashboard/login.php?m=out');
exit;

<?php
/* ============================================================================
 *  تغییر رمز عبورِ کاربرِ واردشده — امن
 * ----------------------------------------------------------------------------
 *  رمز فعلی را با password_verify بررسی می‌کند، رمز جدید را اعتبارسنجی و سپس با
 *  password_hash ذخیره می‌کند. CSRF اجباری است.
 * ========================================================================== */
require_once __DIR__ . '/_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: account.php');
    exit;
}
csrf_check();

$u = dash_user();
if (!$u) {
    dash_redirect_login();
}

$current = (string)($_POST['current_password'] ?? '');
$new     = (string)($_POST['new_password'] ?? '');
$confirm = (string)($_POST['confirm_password'] ?? '');

// اعتبارسنجیِ رمز جدید
if (strlen($new) < 8 || $new !== $confirm) {
    header('Location: account.php?pw=short');
    exit;
}

// رمز فعلی را از دیتابیس بخوان و بررسی کن
$st = dash_pdo()->prepare('SELECT password_hash FROM dashboard_users WHERE id = ? LIMIT 1');
$st->execute([(int)$u['id']]);
$row = $st->fetch();

if (!$row || !password_verify($current, $row['password_hash'])) {
    header('Location: account.php?pw=wrong');
    exit;
}

// ذخیره‌ی رمز جدید
$hash = password_hash($new, PASSWORD_DEFAULT);
dash_pdo()->prepare('UPDATE dashboard_users SET password_hash = ?, failed_attempts = 0, locked_until = NULL WHERE id = ?')
    ->execute([$hash, (int)$u['id']]);

dash_audit('password_changed', ['self' => true]);

header('Location: account.php?pw=ok');
exit;

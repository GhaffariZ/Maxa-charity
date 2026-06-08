<?php
require_once '/home/erfantey/config/database.php';
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO webusers (username, full_name, password, phone) VALUES (?, ?, ?, ?)");
    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
    if ($stmt->execute([$_POST['username'], $_POST['full_name'], $hashed, $_POST['phone']])) {
        $msg = "ثبت‌نام با موفقیت انجام شد. <a href='login.php'>ورود به حساب</a>";
    } else { $msg = "خطا در ثبت‌نام!"; }
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head><title>ثبت‌نام کاربر</title></head>
<body>
    <div class="auth-card">
        <h2>عضویت در حامیان مکسا</h2>
        <?php echo $msg; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="نام کاربری (انگلیسی)" required>
            <input type="text" name="full_name" placeholder="نام و نام خانوادگی">
            <input type="text" name="phone" placeholder="شماره تماس">
            <input type="password" name="password" placeholder="رمز عبور" required>
            <button type="submit">ثبت‌نام</button>
        </form>
        <a href="login.php" class="link">قبلاً ثبت‌نام کرده‌اید؟ ورود</a>
    </div>
</body>
</html>

<?php
session_start();
require_once '/home/erfantey/config/database.php';
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("SELECT id, password FROM webusers WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: /dashboard/components/userpanel/component.php");
        exit;
    } else { $error = "نام کاربری یا رمز عبور اشتباه است."; }
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head><title>ورود به حساب</title></head>
<body>
    <div class="auth-card">
        <h2>ورود به پنل</h2>
        <p style="color:red; text-align:center;"><?php echo $error; ?></p>
        <form method="POST">
            <input type="text" name="username" placeholder="نام کاربری" required>
            <input type="password" name="password" placeholder="رمز عبور" required>
            <button type="submit">ورود</button>
        </form>
        <a href="register.php" class="link">ثبت‌نام نکرده‌اید؟ ایجاد حساب</a>
    </div>
</body>
</html>

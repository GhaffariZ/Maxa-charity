<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

// نمایش خطا (بعداً بردارید)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password']) && $user['role'] === 'admin') {

        session_regenerate_id(true);

        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['role'] = 'admin';
        $_SESSION['last_activity'] = time();

        header("Location: /dashboard/");
        exit;
    }

    $error = "ایمیل یا رمز عبور اشتباه است";
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>صفحه ورود</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

@import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap');

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'Vazirmatn', sans-serif;
    background: radial-gradient(circle at 20% 20%, #0f1c2e, #050b16 70%);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

/* افکت نور متحرک پس زمینه */
body::before {
    content: "";
    position: absolute;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(0,140,255,0.15), transparent 70%);
    top: -150px;
    left: -150px;
    animation: moveLight 12s infinite alternate ease-in-out;
}

@keyframes moveLight {
    from { transform: translate(0,0); }
    to { transform: translate(200px,150px); }
}

/* باکس ورود */
.login-box {
    position: relative;
    width: 100%;
    max-width: 380px;
    padding: 45px 38px;
    border-radius: 16px;
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(18px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.45);
    color: #fff;
    animation: fadeUp 0.8s ease;
}

@keyframes fadeUp {
    from { transform: translateY(40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.login-box h2 {
    text-align: center;
    margin-bottom: 30px;
    font-weight: 600;
    font-size: 24px;
    letter-spacing: 0.5px;
}

/* ورودی ها */
.input-group {
    margin-bottom: 18px;
    width: 100%;
}

.input-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    color: #cfd8e3;
}

.input-group input {
    width: 100%;
    height: 44px;
    padding: 0 14px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.08);
    color: #fff;
    font-size: 14px;
    transition: all 0.25s ease;
}

.input-group input:focus {
    border-color: #008cff;
    background: rgba(255,255,255,0.12);
    outline: none;
    box-shadow: 0 0 0 2px rgba(0,140,255,0.2);
}

/* دکمه کوچکتر و حرفه‌ای */
.btn {
    display: block;
    margin: 15px auto 0 auto;
    width: 65%;
    height: 40px;
    border-radius: 8px;
    border: none;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(135deg, #0061ff, #0044b3);
    transition: all 0.25s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0, 97, 255, 0.4);
}

/* خطا */
.error {
    background: rgba(255, 60, 60, 0.15);
    border-right: 3px solid #ff4b4b;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 13px;
    text-align: center;
}

@media (max-width: 480px) {
    .login-box {
        padding: 35px 25px;
    }

    .btn {
        width: 100%;
    }
}

</style>
</head>

<body>

<div class="login-box">

    <h2>ورود مدیر سایت</h2>

    <?php if(isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="input-group">
            <label>ایمیل</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-group">
            <label>رمز عبور</label>
            <input type="password" name="password" required>
        </div>

        <button class="btn" type="submit">ورود</button>

    </form>

</div>

</body>
</html>

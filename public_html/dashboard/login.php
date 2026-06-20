<?php
/* ============================================================================
 *  ورود به پنل مدیریت مکسا — سامانه‌ی واحد و امن
 * ----------------------------------------------------------------------------
 *  - فقط نام کاربری و رمز عبور. هیچ لینک ثبت‌نامی وجود ندارد.
 *  - توکن CSRF، محدودسازی brute-force و session امن.
 *  - احراز هویت از طریق جدول dashboard_users و core/dashboard-auth.php انجام می‌شود.
 * ========================================================================== */

require_once __DIR__ . '/../core/dashboard-auth.php';
dash_session_start();

// اگر از قبل وارد شده، مستقیم به پنل
if (dash_is_authenticated()) {
    header('Location: /dashboard/index.php');
    exit;
}

$error = '';
$notice = '';
switch ($_GET['m'] ?? '') {
    case 'idle':     $notice = 'به‌دلیل بیکاری طولانی از حساب خارج شدید. دوباره وارد شوید.'; break;
    case 'expired':  $notice = 'نشست شما منقضی شد. دوباره وارد شوید.'; break;
    case 'disabled': $notice = 'حساب شما غیرفعال شده است. با مدیر تماس بگیرید.'; break;
    case 'out':      $notice = 'با موفقیت خارج شدید.'; break;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'نام کاربری و رمز عبور را وارد کنید.';
    } else {
        $res = dash_attempt_login($username, $password);
        if ($res['ok']) {
            header('Location: /dashboard/index.php');
            exit;
        }
        $error = $res['error'] ?? 'خطا در ورود.';
    }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ورود به پنل مدیریت | خیریه مکسا</title>
<!-- اگر صفحه‌ی ورود داخلِ آی‌فریمِ داشبورد بارگذاری شد (خروج یا انقضای نشست)،
     کلِ پوسته را با صفحه‌ی ورودِ تمام‌صفحه جایگزین کن تا سایدبار/منو باقی نماند. -->
<script>if(window.top!==window.self){window.top.location.replace('/dashboard/login.php');}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --danger:#e0556b; --success:#16a37a;
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --primary-08:rgba(0,123,122,.08);
  --shadow-lg:0 20px 44px -14px rgba(0,102,101,.20), 0 8px 20px -10px rgba(16,40,40,.12);
  --ease:cubic-bezier(.4,0,.2,1);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);
  min-height:100vh;display:grid;place-items:center;padding:20px;
  background-image:radial-gradient(120% 80% at 100% 0,rgba(0,123,122,.10),transparent 55%),
                   radial-gradient(90% 70% at 0 100%,rgba(244,166,30,.08),transparent 60%);}
.auth-card{width:100%;max-width:410px;background:var(--color-surface);border:1px solid var(--color-border);
  border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);padding:34px 30px;animation:rise .5s var(--ease) both}
@keyframes rise{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
.brand{display:flex;flex-direction:column;align-items:center;gap:12px;margin-bottom:26px}
.brand-badge{width:62px;height:62px;border-radius:18px;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));
  box-shadow:0 12px 24px -12px rgba(0,102,101,.85);font-weight:800;font-size:22px}
.brand h1{font-size:19px;font-weight:800;letter-spacing:-.01em}
.brand p{font-size:12.5px;color:var(--color-muted);font-weight:500}
.field{margin-bottom:15px}
.field label{display:block;font-size:12.5px;font-weight:700;margin-bottom:7px;color:#5b6469}
.input-wrap{display:flex;align-items:center;gap:10px;background:var(--color-bg);border:1px solid var(--color-border);
  border-radius:13px;padding:0 14px;height:48px;transition:border-color .2s,box-shadow .2s,background .2s}
.input-wrap:focus-within{border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:#fff}
.input-wrap .ic{width:19px;height:19px;color:var(--color-muted);flex-shrink:0}
.input-wrap input{border:none;background:none;outline:none;flex:1;font-family:inherit;font-size:14px;color:var(--color-text)}
.input-wrap input::placeholder{color:var(--color-muted)}
.btn-submit{width:100%;height:50px;margin-top:8px;border:none;border-radius:14px;cursor:pointer;
  font-family:inherit;font-weight:800;font-size:14.5px;color:#fff;
  background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));
  box-shadow:0 12px 24px -12px rgba(0,123,122,.8);transition:transform .15s var(--ease),box-shadow .22s}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 18px 30px -12px rgba(0,123,122,.9)}
.btn-submit:active{transform:scale(.97)}
.msg{border-radius:12px;padding:11px 14px;font-size:12.5px;font-weight:600;margin-bottom:18px;line-height:1.7}
.msg.err{background:rgba(224,85,107,.10);color:#c33850;border:1px solid rgba(224,85,107,.22)}
.msg.note{background:var(--primary-08);color:var(--color-primary-dark);border:1px solid rgba(0,123,122,.18)}
.foot{text-align:center;margin-top:20px;font-size:11.5px;color:var(--color-muted)}
</style>
</head>
<body>
  <form class="auth-card" method="POST" autocomplete="off" novalidate>
    <div class="brand">
      <div class="brand-badge">مکسا</div>
      <h1>ورود به پنل مدیریت</h1>
      <p>سامانه‌ی مدیریت خیریه مکسا</p>
    </div>

    <?php if ($notice): ?><div class="msg note"><?= e($notice) ?></div><?php endif; ?>
    <?php if ($error):  ?><div class="msg err"><?= e($error) ?></div><?php endif; ?>

    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

    <div class="field">
      <label for="username">نام کاربری</label>
      <div class="input-wrap">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <input id="username" type="text" name="username" placeholder="نام کاربری" required autofocus>
      </div>
    </div>

    <div class="field">
      <label for="password">رمز عبور</label>
      <div class="input-wrap">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <input id="password" type="password" name="password" placeholder="••••••••" required>
      </div>
    </div>

    <button class="btn-submit" type="submit">ورود به پنل</button>

    <div class="foot">© موسسه نیکوکاری کنترل سرطان ایرانیان (مکسا)</div>
  </form>
</body>
</html>

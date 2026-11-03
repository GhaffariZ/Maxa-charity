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

// اگر از قبل وارد شده، مستقیم به پنل مناسب
if (dash_is_authenticated()) {
    if (dash_is_finance_only() || dash_is_finance_user()) {
        header('Location: /dashboard/financial-management.php');
    } else {
        header('Location: /dashboard/index.php');
    }
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
            if (dash_is_finance_only() || dash_is_finance_user()) {
                header('Location: /dashboard/financial-management.php');
            } else {
                header('Location: /dashboard/index.php');
            }
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
<link rel="icon" href="/favicon.png" type="image/png">
<link rel="stylesheet" href="/dashboard/assets/css/login.css">
</head>
<body>
  <main class="login">
    <section class="login-main" aria-labelledby="login-title">
      <header class="login-brand">
        <img src="/favicon.png" alt="" width="44" height="44">
        <div>
          <strong>خیریه مکسا</strong>
          <span>پنل مدیریت</span>
        </div>
      </header>

      <div class="login-content">
        <h1 id="login-title">ورود به پنل مدیریت</h1>
        <p class="login-intro">با نام کاربری و رمز عبور سازمانی خود وارد شوید.</p>

        <?php if ($notice): ?><div class="msg note" role="status"><?= e($notice) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="msg err" role="alert"><?= e($error) ?></div><?php endif; ?>

        <form method="POST" novalidate>
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

          <div class="field">
            <label for="username">نام کاربری</label>
            <div class="input-wrap">
              <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input id="username" type="text" name="username" autocomplete="username" autocapitalize="none" spellcheck="false" dir="auto" required>
            </div>
          </div>

          <div class="field">
            <label for="password">رمز عبور</label>
            <div class="input-wrap">
              <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input id="password" type="password" name="password" autocomplete="current-password" dir="auto" required>
            </div>
          </div>

          <button class="btn-submit" type="submit">
            ورود به پنل
            <span class="submit-action" aria-hidden="true">
              <span class="submit-intent"><span>باز کردن داشبورد</span></span>
              <svg viewBox="0 0 24 24"><path d="M19 12H5m6-6-6 6 6 6"/></svg>
            </span>
          </button>
        </form>
      </div>

      <footer class="login-footer">مؤسسه نیکوکاری کنترل سرطان ایرانیان</footer>
    </section>

    <aside class="login-art" aria-hidden="true">
      <span class="art-wordmark" lang="en" dir="ltr">MACSA</span>
      <div class="art-orbit">
        <img src="/favicon.png" alt="" width="104" height="104">
      </div>
      <p>همراه بیماران مبتلا به سرطان</p>
    </aside>
  </main>
  <script>
    const loginArt = document.querySelector('.login-art');
    const canFollowPointer = matchMedia('(any-hover: hover) and (any-pointer: fine) and (prefers-reduced-motion: no-preference)');

    loginArt.addEventListener('pointermove', (event) => {
      if (event.pointerType === 'touch' || !canFollowPointer.matches) return;

      const bounds = loginArt.getBoundingClientRect();
      loginArt.style.setProperty('--pointer-x', (event.clientX - bounds.left) + 'px');
      loginArt.style.setProperty('--pointer-y', (event.clientY - bounds.top) + 'px');
      loginArt.classList.add('is-tracking');
    });

    loginArt.addEventListener('pointerleave', () => loginArt.classList.remove('is-tracking'));
  </script>
</body>
</html>

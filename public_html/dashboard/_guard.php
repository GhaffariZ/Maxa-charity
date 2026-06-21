<?php
/* ============================================================================
 *  گاردِ احراز هویت پنل مدیریت
 * ----------------------------------------------------------------------------
 *  این فایل باید در بالای *هر* صفحه‌ی پنل require شود:
 *      require_once __DIR__ . '/_guard.php';
 *
 *  وظایف:
 *    1) شروعِ نشستِ امن.
 *    2) اگر کاربر وارد نشده → ریدایرکت به صفحه‌ی ورود.
 *    3) اعمالِ timeout بیکاری/مطلق.
 *    4) بررسی وضعیتِ disabled (در هر درخواست، نه فقط هنگام ورود).
 *    5) فراهم‌کردنِ $pdo و کمک‌توابع برای صفحه.
 *
 *  بررسی دسترسیِ هر بخش (feature) با dash_require('news') و امثال آن در خودِ
 *  صفحه انجام می‌شود (چون نام بخش به صفحه بستگی دارد).
 * چه خبر؟
 * ========================================================================== */

require_once __DIR__ . '/../core/dashboard-auth.php';

dash_session_start();

/* وارد نشده؟ → ورود */
if (!dash_is_authenticated()) {
    dash_redirect_login();
}

/* انقضای نشست */
dash_enforce_timeouts();

/* بررسی زنده‌ی وضعیت کاربر در هر درخواست (مثلاً غیرفعال‌شدن توسط ادمین) */
try {
    $st = dash_pdo()->prepare('SELECT status, branch_id FROM dashboard_users WHERE id = ? LIMIT 1');
    $st->execute([(int)$_SESSION['dash_user']['id']]);
    $live = $st->fetch();
    if (!$live || $live['status'] === 'disabled') {
        dash_logout();
        dash_redirect_login('disabled');
    }
} catch (Throwable $ex) {
    // اگر دیتابیس در دسترس نبود، به ورود برنگردانیم تا حلقه نشود؛ خطا را نشان نده.
}

/* در دسترسِ صفحات: اتصال آماده + کاربر + شعبه‌ی فعال */
$pdo            = dash_pdo();
$DASH_USER      = dash_user();
$ACTIVE_BRANCH  = dash_active_branch_id();

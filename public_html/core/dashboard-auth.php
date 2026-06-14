<?php
/* ============================================================================
 *  هسته‌ی احراز هویت و چندشعبه‌ای پنل مدیریت مکسا
 * ----------------------------------------------------------------------------
 *  این فایل تنها منبعِ معتبرِ احراز هویت پنل است و موارد زیر را فراهم می‌کند:
 *    - اتصال PDO امن (فقط از طریق core/db-config.php)
 *    - شروعِ نشستِ امن (HttpOnly/Secure/SameSite) + timeout بیکاری و مطلق
 *    - توکن CSRF و تأیید آن
 *    - ثبت تلاش‌های ورود و محدودسازی brute-force
 *    - بارگذاری کاربر، شعبه‌ی فعال و دسترسی‌ها
 *    - توابع scope چندمستأجری (tenant isolation) برای کوئری‌ها
 *    - ثبتِ audit
 *
 *  نکته‌ی امنیتی: همه‌ی صفحات پنل باید dashboard/_guard.php را require کنند که
 *  خودش این فایل را بار می‌کند. مستقیم به این فایل از وب دسترسی ندهید.
 * ========================================================================== */

declare(strict_types=1);

if (!defined('MAXA_DASH_AUTH')) {
    define('MAXA_DASH_AUTH', 1);
}

/* ---------- ثابت‌های امنیتی ---------- */
const DASH_IDLE_TIMEOUT     = 1800;   // ۳۰ دقیقه بیکاری
const DASH_ABSOLUTE_TIMEOUT = 28800;  // ۸ ساعت سقفِ مطلقِ نشست
const DASH_MAX_ATTEMPTS     = 5;      // پس از این تعداد تلاش ناموفق → قفل
const DASH_LOCK_MINUTES     = 15;     // مدت قفل (دقیقه)

/* فهرست کامل بخش‌های قابل‌واگذاری (مرجعِ واحد در کل سامانه) */
const DASH_FEATURES = [
    'hero', 'news', 'campaigns', 'partners', 'courses',
    'pages', 'financial', 'feedback', 'medical',
];

/* slugهای رزرو شده — نباید به‌عنوان slugِ شعبه استفاده شوند (جلوگیری از تداخل) */
const RESERVED_SLUGS = [
    'api', 'dashboard', 'admin', 'benefactor-dashboard', 'core', 'assets',
    'uploads', 'branches', 'branch', 'news', 'page', 'login', 'logout',
    'register', 'home', 'index', 'hq', 'css', 'js', 'img', 'fonts',
];

/* ---------- اتصال PDO (الگوی core/database.php) ---------- */
function dash_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $DB = require __DIR__ . '/db-config.php';
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
        $opts[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$DB['charset']}";
    }
    $pdo = new PDO(
        "mysql:host={$DB['host']};dbname={$DB['name']}",
        $DB['user'], $DB['pass'], $opts
    );
    try { $pdo->exec("SET NAMES {$DB['charset']}"); } catch (Throwable $e) { /* بی‌اثر */ }
    return $pdo;
}

/* ---------- شروعِ نشستِ امن ---------- */
function dash_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_name('MAXADASH');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',   // Lax تا ریدایرکتِ ورود کار کند؛ برای فرم‌ها CSRF token داریم
    ]);
    session_start();
}

/* ---------- مدیریت انقضای نشست ---------- */
function dash_enforce_timeouts(): void
{
    $now = time();
    if (isset($_SESSION['dash_login_at']) && ($now - (int)$_SESSION['dash_login_at']) > DASH_ABSOLUTE_TIMEOUT) {
        dash_logout();
        dash_redirect_login('expired');
    }
    if (isset($_SESSION['dash_last_seen']) && ($now - (int)$_SESSION['dash_last_seen']) > DASH_IDLE_TIMEOUT) {
        dash_logout();
        dash_redirect_login('idle');
    }
    $_SESSION['dash_last_seen'] = $now;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}
function csrf_check(): void
{
    $sent = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (empty($_SESSION['csrf_token']) || !is_string($sent) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(419);
        exit('درخواست نامعتبر (CSRF).');
    }
}

/* ---------- ابزارِ خروجی امن ---------- */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* ---------- IP و User-Agent ---------- */
function dash_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}
function dash_ua(): string
{
    return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}

/* ---------- ثبت تلاش ورود (جدول موجود login_attempts) ---------- */
function dash_log_attempt(string $username, bool $success): void
{
    try {
        $st = dash_pdo()->prepare(
            'INSERT INTO login_attempts (email, ip, success, user_agent) VALUES (?,?,?,?)'
        );
        $st->execute([$username, dash_ip(), $success ? 1 : 0, dash_ua()]);
    } catch (Throwable $e) { /* ثبتِ ناموفقِ لاگ نباید جریان را بشکند */ }
}

/* ---------- audit ---------- */
function dash_audit(string $action, ?array $meta = null, ?int $userId = null): void
{
    try {
        $uid = $userId ?? (int)($_SESSION['dash_user']['id'] ?? 0) ?: null;
        $st = dash_pdo()->prepare(
            'INSERT INTO audit_log (user_id, action, ip, user_agent, metadata) VALUES (?,?,?,?,?)'
        );
        $st->execute([
            $uid, $action, dash_ip(), dash_ua(),
            $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
        ]);
    } catch (Throwable $e) { /* بی‌اثر */ }
}

/* ---------- محدودسازی brute-force (بر اساس کاربر) ---------- */
function dash_is_locked(array $userRow): bool
{
    return !empty($userRow['locked_until']) && strtotime((string)$userRow['locked_until']) > time();
}
function dash_register_failure(int $userId): void
{
    $pdo = dash_pdo();
    $st = $pdo->prepare(
        'UPDATE dashboard_users
            SET failed_attempts = failed_attempts + 1,
                locked_until = IF(failed_attempts + 1 >= ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), locked_until)
          WHERE id = ?'
    );
    $st->execute([DASH_MAX_ATTEMPTS, DASH_LOCK_MINUTES, $userId]);
}
function dash_reset_failures(int $userId): void
{
    dash_pdo()->prepare('UPDATE dashboard_users SET failed_attempts = 0, locked_until = NULL WHERE id = ?')
        ->execute([$userId]);
}

/* ---------- تلاش برای ورود ---------- */
/**
 * @return array{ok:bool, error?:string}
 */
function dash_attempt_login(string $username, string $password): array
{
    $pdo = dash_pdo();
    $st  = $pdo->prepare('SELECT * FROM dashboard_users WHERE username = ? LIMIT 1');
    $st->execute([$username]);
    $u = $st->fetch();

    if (!$u) {
        dash_log_attempt($username, false);
        return ['ok' => false, 'error' => 'نام کاربری یا رمز عبور اشتباه است.'];
    }
    if ($u['status'] === 'disabled') {
        dash_log_attempt($username, false);
        return ['ok' => false, 'error' => 'این حساب غیرفعال شده است.'];
    }
    if (dash_is_locked($u)) {
        dash_log_attempt($username, false);
        return ['ok' => false, 'error' => 'به‌دلیل تلاش‌های ناموفق، حساب موقتاً قفل شده است. بعداً تلاش کنید.'];
    }
    if (!password_verify($password, $u['password_hash'])) {
        dash_register_failure((int)$u['id']);
        dash_log_attempt($username, false);
        return ['ok' => false, 'error' => 'نام کاربری یا رمز عبور اشتباه است.'];
    }

    // موفق — استانداردِ rehash
    if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
        $pdo->prepare('UPDATE dashboard_users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), (int)$u['id']]);
    }
    dash_reset_failures((int)$u['id']);
    $pdo->prepare('UPDATE dashboard_users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?')
        ->execute([dash_ip(), (int)$u['id']]);
    dash_log_attempt($username, true);

    // جلوگیری از session fixation
    session_regenerate_id(true);
    dash_establish_session($u);
    dash_audit('dashboard_login', ['username' => $username]);
    return ['ok' => true];
}

/* ---------- ساختِ نشست از روی ردیف کاربر ---------- */
function dash_establish_session(array $u): void
{
    $branch = dash_load_branch((int)$u['branch_id']);
    $perms  = dash_resolve_permissions($u);

    $_SESSION['dash_user'] = [
        'id'              => (int)$u['id'],
        'username'        => $u['username'],
        'full_name'       => $u['full_name'],
        'branch_id'       => (int)$u['branch_id'],
        'role_id'         => $u['role_id'] !== null ? (int)$u['role_id'] : null,
        'is_super'        => (int)$u['is_super'] === 1,
        'is_branch_admin' => (int)$u['is_branch_admin'] === 1,
        'permissions'     => $perms,
    ];
    $_SESSION['dash_home_branch'] = (int)$u['branch_id'];
    // شعبه‌ی فعال = شعبه‌ی خودِ کاربر در بدوِ ورود (سوپرادمین می‌تواند تغییر دهد)
    $_SESSION['dash_active_branch'] = (int)$u['branch_id'];
    $_SESSION['dash_login_at']  = time();
    $_SESSION['dash_last_seen'] = time();
    csrf_token();
}

/* ---------- محاسبه‌ی دسترسی‌ها ---------- */
function dash_resolve_permissions(array $u): array
{
    // سوپرادمین و ادمین شعبه: همه‌ی قابلیت‌ها (در محدوده‌ی شعبه‌ی خودشان)
    if ((int)$u['is_super'] === 1 || (int)$u['is_branch_admin'] === 1) {
        return DASH_FEATURES;
    }
    // کاربر شعبه: از روی نقش
    if (!empty($u['role_id'])) {
        $st = dash_pdo()->prepare('SELECT permissions FROM dashboard_roles WHERE id = ? LIMIT 1');
        $st->execute([(int)$u['role_id']]);
        $row = $st->fetch();
        if ($row) {
            $p = json_decode((string)$row['permissions'], true);
            if (is_array($p)) {
                return array_values(array_intersect($p, DASH_FEATURES));
            }
        }
    }
    return [];
}

/* ---------- بارگذاری شعبه ---------- */
function dash_load_branch(int $id): ?array
{
    $st = dash_pdo()->prepare('SELECT * FROM branches WHERE id = ? LIMIT 1');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

/* ---------- خروج ---------- */
function dash_logout(): void
{
    if (!empty($_SESSION['dash_user'])) {
        dash_audit('dashboard_logout');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/* ---------- ریدایرکت به ورود ---------- */
function dash_redirect_login(?string $reason = null): void
{
    $q = $reason ? ('?m=' . urlencode($reason)) : '';
    header('Location: /dashboard/login.php' . $q);
    exit;
}

/* ---------- وضعیت ورود ---------- */
function dash_is_authenticated(): bool
{
    return !empty($_SESSION['dash_user']['id']);
}
function dash_user(): ?array
{
    return $_SESSION['dash_user'] ?? null;
}
function dash_is_super(): bool
{
    return !empty($_SESSION['dash_user']['is_super']);
}
function dash_is_branch_admin(): bool
{
    return !empty($_SESSION['dash_user']['is_branch_admin']);
}

/* ---------- شعبه‌ی فعال (با ایزولاسیون) ----------
 * سوپرادمین می‌تواند شعبه‌ی فعال را عوض کند؛ دیگران همیشه به شعبه‌ی خودشان قفل‌اند.
 */
function dash_active_branch_id(): int
{
    $u = dash_user();
    if (!$u) {
        return 0;
    }
    if ($u['is_super']) {
        return (int)($_SESSION['dash_active_branch'] ?? $u['branch_id']);
    }
    // غیرسوپرادمین: همیشه شعبه‌ی خودش — هر تلاشِ دستکاری بی‌اثر است
    return (int)$u['branch_id'];
}

/**
 * تغییر شعبه‌ی فعال (فقط سوپرادمین). branch_id را اعتبارسنجی می‌کند.
 */
function dash_set_active_branch(int $branchId): bool
{
    if (!dash_is_super()) {
        return false;
    }
    $b = dash_load_branch($branchId);
    if (!$b) {
        return false;
    }
    $_SESSION['dash_active_branch'] = $branchId;
    return true;
}

/* ---------- بررسی دسترسی به یک بخش (feature/permission) ---------- */
function dash_can(string $feature): bool
{
    $u = dash_user();
    if (!$u) {
        return false;
    }
    // باید هم کاربر دسترسی داشته باشد و هم آن قابلیت برای شعبه‌ی فعال روشن باشد
    $hasPerm = in_array($feature, $u['permissions'], true);
    return $hasPerm && dash_branch_feature_enabled(dash_active_branch_id(), $feature);
}

/**
 * بررسی فعال‌بودنِ یک قابلیت برای یک شعبه (با cache در session-request).
 */
function dash_branch_feature_enabled(int $branchId, string $feature): bool
{
    static $cache = [];
    if (!isset($cache[$branchId])) {
        $cache[$branchId] = [];
        $st = dash_pdo()->prepare('SELECT feature FROM branch_features WHERE branch_id = ? AND enabled = 1');
        $st->execute([$branchId]);
        foreach ($st->fetchAll() as $r) {
            $cache[$branchId][$r['feature']] = true;
        }
    }
    return !empty($cache[$branchId][$feature]);
}

/**
 * گاردِ دسترسی به یک صفحه؛ اگر مجاز نباشد 403.
 */
function dash_require(string $feature): void
{
    if (!dash_can($feature)) {
        http_response_code(403);
        exit('۴۰۳ | دسترسی غیرمجاز');
    }
}

/**
 * فهرست همه‌ی شعبه‌ها (برای dropdown سوپرادمین).
 */
function dash_all_branches(): array
{
    return dash_pdo()->query('SELECT id, name, slug, is_hq, status FROM branches ORDER BY is_hq DESC, name ASC')->fetchAll();
}

/* ---------- اعتبارسنجی slug شعبه ---------- */
function dash_sanitize_slug(string $raw): string
{
    $s = strtolower(trim($raw));
    $s = preg_replace('/[^a-z0-9-]+/', '-', $s);
    $s = preg_replace('/-+/', '-', $s);
    return trim($s, '-');
}

/**
 * آیا slug جهانی یکتا و غیررزرو است؟ (هم در branches و هم در pages مرکزی)
 */
function dash_slug_available(string $slug): bool
{
    if ($slug === '' || in_array($slug, RESERVED_SLUGS, true)) {
        return false;
    }
    $pdo = dash_pdo();
    $st = $pdo->prepare('SELECT 1 FROM branches WHERE slug = ? LIMIT 1');
    $st->execute([$slug]);
    if ($st->fetch()) {
        return false;
    }
    // تداخل با صفحه‌ی مرکزی (branch_id = 1) با همان slug
    $st = $pdo->prepare('SELECT 1 FROM pages WHERE slug = ? AND branch_id = 1 LIMIT 1');
    $st->execute([$slug]);
    return !$st->fetch();
}

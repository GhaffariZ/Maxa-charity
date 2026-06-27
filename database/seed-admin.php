<?php
/* ============================================================================
 *  seed-admin.php — ساخت امنِ ادمین مرکزی (Super Admin) از طریق خط فرمان
 * ----------------------------------------------------------------------------
 *  فقط از CLI اجرا می‌شود (نه از وب). رمز عبور را با password_hash ذخیره می‌کند
 *  و آن را در کد hard-code نمی‌کند.
 *
 *  پیش‌نیاز: ابتدا مهاجرت را اجرا کنید:
 *      mysql -u USER -p DBNAME < database/migrations/001_multi_branch.sql
 *
 *  نحوه‌ی اجرا (یکی از این دو):
 *      ADMIN_USER=admin ADMIN_PASS='StrongPass!' php database/seed-admin.php
 *      php database/seed-admin.php --user=admin --pass='StrongPass!' [--name="مدیر اصلی"]
 *
 *  اگر نام کاربری از قبل وجود داشته باشد، فقط رمز عبور به‌روزرسانی می‌شود.
 * ========================================================================== */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("این اسکریپت فقط از خط فرمان قابل اجرا است.\n");
}

/* ---------- خواندن آرگومان‌ها / متغیرهای محیطی ---------- */
$opts = getopt('', ['user:', 'pass:', 'name::', 'branch::']);
$user = $opts['user']   ?? getenv('ADMIN_USER') ?: null;
$pass = $opts['pass']   ?? getenv('ADMIN_PASS') ?: null;
$name = $opts['name']   ?? getenv('ADMIN_NAME') ?: 'مدیر مرکزی';

if (!$user || !$pass) {
    fwrite(STDERR, "خطا: نام کاربری و رمز عبور لازم است.\n");
    fwrite(STDERR, "مثال: ADMIN_USER=admin ADMIN_PASS='StrongPass!' php database/seed-admin.php\n");
    exit(1);
}
if (strlen($pass) < 10) {
    fwrite(STDERR, "خطا: رمز عبور باید حداقل ۱۰ کاراکتر باشد.\n");
    exit(1);
}
if (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $user)) {
    fwrite(STDERR, "خطا: نام کاربری نامعتبر است (۳ تا ۶۰ کاراکترِ A-Z a-z 0-9 _ . -).\n");
    exit(1);
}

/* ---------- اتصال به دیتابیس از طریق loader امن ---------- */
$DB = require __DIR__ . '/../public_html/core/db-config.php';
try {
    $pdo = new PDO(
        "mysql:host={$DB['host']};dbname={$DB['name']};charset={$DB['charset']}",
        $DB['user'], $DB['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (Throwable $e) {
    fwrite(STDERR, "خطای اتصال به دیتابیس: " . $e->getMessage() . "\n");
    exit(1);
}

/* ---------- اطمینان از وجود دفتر مرکزی (HQ) ---------- */
$hq = $pdo->query("SELECT id FROM branches WHERE is_hq = 1 LIMIT 1")->fetch();
if (!$hq) {
    $pdo->exec("INSERT INTO branches (name, slug, is_hq, status) VALUES ('دفتر مرکزی مکسا','hq',1,'active')");
    $hqId = (int)$pdo->lastInsertId();
    echo "✓ دفتر مرکزی (HQ) ساخته شد (id=$hqId).\n";
} else {
    $hqId = (int)$hq['id'];
}

/* ---------- نقش پیش‌فرض خبرنگار برای HQ (در صورت نبود) ---------- */
$pdo->prepare(
    "INSERT INTO dashboard_roles (branch_id, name, permissions, is_preset)
     SELECT ?, 'خبرنگار', JSON_ARRAY('news'), 1
     WHERE NOT EXISTS (SELECT 1 FROM dashboard_roles WHERE branch_id = ? AND name = 'خبرنگار')"
)->execute([$hqId, $hqId]);

/* ---------- نقش پیش‌فرض سردبیر برای HQ (تایید/انتشار خبر) ---------- */
$pdo->prepare(
    "INSERT INTO dashboard_roles (branch_id, name, permissions, is_preset)
     SELECT ?, 'سردبیر', JSON_ARRAY('news','news_editor'), 1
     WHERE NOT EXISTS (SELECT 1 FROM dashboard_roles WHERE branch_id = ? AND name = 'سردبیر')"
)->execute([$hqId, $hqId]);

/* ---------- ساخت/به‌روزرسانی ادمین مرکزی ---------- */
$hash = password_hash($pass, PASSWORD_DEFAULT);
$existing = $pdo->prepare("SELECT id FROM dashboard_users WHERE username = ? LIMIT 1");
$existing->execute([$user]);
$row = $existing->fetch();

if ($row) {
    $pdo->prepare(
        "UPDATE dashboard_users
            SET password_hash = ?, is_super = 1, branch_id = ?, status = 'active',
                full_name = ?, failed_attempts = 0, locked_until = NULL
          WHERE id = ?"
    )->execute([$hash, $hqId, $name, (int)$row['id']]);
    echo "✓ ادمین موجود به‌روزرسانی شد: «$user» (رمز و دسترسی سوپرادمین تنظیم شد).\n";
} else {
    $pdo->prepare(
        "INSERT INTO dashboard_users (branch_id, username, password_hash, full_name, is_super, is_branch_admin, status)
         VALUES (?, ?, ?, ?, 1, 0, 'active')"
    )->execute([$hqId, $user, $hash, $name]);
    echo "✓ ادمین مرکزی ساخته شد: «$user».\n";
}

/* ---------- ثبت در audit_log ---------- */
try {
    $pdo->prepare("INSERT INTO audit_log (user_id, action, ip, metadata) VALUES (NULL, 'seed_admin', 'cli', ?)")
        ->execute([json_encode(['username' => $user], JSON_UNESCAPED_UNICODE)]);
} catch (Throwable $e) { /* بی‌اثر */ }

echo "اکنون می‌توانید از /dashboard/login.php وارد شوید.\n";
exit(0);

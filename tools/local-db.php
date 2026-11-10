<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$secrets = json_decode(file_get_contents($root . '/.local/conf/secrets.json'), true, 512, JSON_THROW_ON_ERROR);
$db = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', $secrets['rootPassword'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('CREATE DATABASE IF NOT EXISTS macsa_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$db->exec('USE macsa_local');
$migrate = static function () use ($db, $root): void {
    // Reviewed, repeatable updates required by this application's current code.
    foreach (['014_campaign_service_donations.sql', '021_rename_contact_center.sql'] as $name) {
        $db->exec(file_get_contents($root . '/database/migrations/' . $name));
    }
};
if ((int)$db->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn() > 0) {
    $migrate();
    echo "Local database already has tables; no data was overwritten.\n";
    exit;
}
$dump = $argv[1] ?? '';
if (!is_file($dump)) { throw new RuntimeException('SQL export not found.'); }
$db->exec(file_get_contents($dump));
$migrate();
$password = $db->quote($secrets['dbPassword']);
$db->exec("CREATE USER IF NOT EXISTS 'macsa_local'@'127.0.0.1' IDENTIFIED BY $password");
$db->exec("GRANT ALL PRIVILEGES ON macsa_local.* TO 'macsa_local'@'127.0.0.1'");
$hash = password_hash($secrets['loginPassword'], PASSWORD_DEFAULT);
$hq = (int)$db->query('SELECT id FROM branches WHERE is_hq=1 LIMIT 1')->fetchColumn();
$db->prepare('INSERT INTO dashboard_users (branch_id, username, password_hash, full_name, is_super, is_branch_admin, status) VALUES (?, ?, ?, ?, 1, 1, ?)')
    ->execute([$hq, 'local-admin', $hash, 'Local administrator', 'active']);
require $root . '/public_html/api/src/Core/Config.php';
require $root . '/public_html/api/src/Core/Database.php';
require $root . '/public_html/api/src/Repositories/UserRepository.php';
foreach (['DB_HOST'=>'127.0.0.1', 'DB_NAME'=>'macsa_local', 'DB_USER'=>'macsa_local', 'DB_PASS'=>$secrets['dbPassword']] as $key=>$value) {
    Maksa\Core\Config::set($key, $value);
}
$users = new Maksa\Repositories\UserRepository();
$userId = $users->createDonor('local-donor@example.test', $hash, 'Local', 'Donor');
$db->prepare("UPDATE panel_users SET status='active', email_verified_at=NOW() WHERE id=?")->execute([$userId]);
file_put_contents($root . '/.local/access.txt', "Local database only.\nAdmin username: local-admin\nDonor email: local-donor@example.test\nPassword for both: " . $secrets['loginPassword'] . "\n");
echo "Imported local snapshot; created separate local demo accounts.\n";

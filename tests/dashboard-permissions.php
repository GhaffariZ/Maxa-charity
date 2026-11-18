<?php
declare(strict_types=1);

// Load the production permission/session functions with an isolated database double.
$source = file_get_contents(__DIR__ . '/../public_html/core/dashboard-auth.php');
foreach (['DASH_FEATURE_LABELS', 'DASH_HQ_FEATURES', 'DASH_SUPER_ONLY_PERMISSIONS'] as $name) {
    preg_match('/const ' . $name . ' = .*?;/s', $source, $constants);
    eval($constants[0]);
}
foreach (['dash_feature_labels', 'dash_refresh_session_user', 'dash_resolve_permissions',
    'dash_user', 'dash_user_role_name', 'dash_is_finance_only', 'dash_is_finance_user',
    'dash_active_branch_id', 'dash_can', 'dash_branch_feature_enabled', 'dash_is_hq_view',
    'dash_load_branch'] as $name) {
    if (!preg_match('/^function ' . $name . '\(.*?^}/ms', $source, $match)) {
        throw new RuntimeException('Missing production function: ' . $name);
    }
    eval($match[0]);
}
final class PermissionStatement extends PDOStatement
{
    private array $rows = [];
    public function __construct(private string $sql) {}
    public function execute(?array $params = null): bool
    {
        $id = (int)$params[0];
        if (str_contains($this->sql, 'FROM dashboard_users')) {
            $this->rows = [$GLOBALS['liveUser']];
        } elseif (str_contains($this->sql, 'FROM dashboard_roles')) {
            $this->rows = isset($GLOBALS['roles'][$id]) ? [$GLOBALS['roles'][$id]] : [];
        } elseif (str_contains($this->sql, 'FROM branch_features')) {
            $this->rows = array_map(static fn($f) => ['feature' => $f], $GLOBALS['features'][$id] ?? []);
        } elseif (str_contains($this->sql, 'FROM branches')) {
            $this->rows = [['id' => $id, 'is_hq' => $id === 1 ? 1 : 0]];
        } else {
            throw new RuntimeException('Unexpected SQL: ' . $this->sql);
        }
        return true;
    }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    { return array_shift($this->rows) ?? false; }
    public function fetchColumn(int $column = 0): mixed
    { $row = $this->fetch(); return $row ? $row['name'] : false; }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    { return $this->rows; }
}
final class PermissionPDO extends PDO
{
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false
    { return new PermissionStatement($query); }
}
function dash_pdo(): PDO { static $db; return $db ??= new PermissionPDO(); }
function check(bool $condition, string $message): void
{ if (!$condition) throw new RuntimeException($message); }

$roles = [7 => ['name' => 'مسئول مالی', 'permissions' => '["financial"]']];
$features = [1 => array_keys(DASH_FEATURE_LABELS), 2 => ['news', 'financial', 'medical', 'tickets']];
$user = ['id' => 15, 'username' => 'operator', 'full_name' => 'Operator', 'branch_id' => 2,
    'role_id' => 7, 'is_super' => 0, 'is_branch_admin' => 0, 'direct_permissions' => null];
$_SESSION = ['dash_login_at' => 123, 'dash_last_seen' => 456, 'dash_active_branch' => 1];
dash_refresh_session_user($user);
check(dash_is_finance_only(), 'Finance-only users keep their specialized panel.');
$roles[7]['permissions'] = '["financial","medical","tickets"]';
dash_refresh_session_user($user);
check(dash_can('medical') && dash_can('tickets'), 'Role changes apply to an existing session.');
check(!dash_is_finance_only() && !dash_is_finance_user(), 'Mixed permissions keep the main sidebar, even with a finance role name.');
check(dash_active_branch_id() === 2, 'Normal users stay in their own branch.');
check($_SESSION['dash_login_at'] === 123 && $_SESSION['dash_last_seen'] === 456, 'Permission refresh cannot extend session lifetime.');
check($_SESSION['dash_active_branch'] === 1, 'Refreshing identity does not reset selected branch.');
$user['role_id'] = null;
$user['direct_permissions'] = '["news","hero","maxapedia","invalid"]';
dash_refresh_session_user($user);
check(dash_can('news') && !dash_can('hero'), 'User grants still require an enabled branch feature.');
check(!dash_can('maxapedia'), 'HQ features cannot leak into a branch.');
check(!in_array('invalid', dash_user()['permissions'], true), 'Unknown permissions are rejected.');
$user['direct_permissions'] = '[]';
dash_refresh_session_user($user);
check(dash_user()['permissions'] === [] && !dash_can('news'), 'Revoking every direct permission takes effect.');
$user['role_id'] = 7;
$user['direct_permissions'] = null;
dash_refresh_session_user($user);
check(!dash_user()['has_direct_permissions'] && dash_can('medical'), 'Returning to inherited role permissions refreshes the session.');
$user['is_branch_admin'] = 1;
dash_refresh_session_user($user);
check(!dash_is_finance_user() && dash_can('news'), 'A branch admin with a finance role still gets branch navigation.');
$user['is_super'] = 1;
dash_refresh_session_user($user);
check(dash_active_branch_id() === 1 && dash_can('maxapedia'), 'Super admins retain their selected HQ view.');

$guard = file_get_contents(__DIR__ . '/../public_html/dashboard/_guard.php');
$liveStart = strpos($guard, '/* بررسی زنده');
$liveCheck = substr($guard, $liveStart, strpos($guard, '/* در دسترس', $liveStart) - $liveStart);
$liveUser = array_replace($user, ['is_super' => 0, 'is_branch_admin' => 0, 'status' => 'active',
    'role_id' => null, 'direct_permissions' => '["tickets"]']);
eval($liveCheck);
check(!dash_user()['is_super'] && !dash_user()['is_branch_admin'], 'The page guard refreshes admin flags too.');
check(dash_user()['permissions'] === ['tickets'], 'The actual guard applies updated direct grants.');
$liveUser['direct_permissions'] = null;
$liveUser['role_id'] = 7;
$roles[7]['permissions'] = '["news"]';
eval($liveCheck);
check(dash_user()['permissions'] === ['news'] && !dash_user()['has_direct_permissions'], 'The actual guard switches back to current role grants.');

foreach (['user-add.php', 'user-edit.php'] as $file) {
    $form = file_get_contents(__DIR__ . '/../public_html/dashboard/' . $file);
    preg_match('/\$FEATURE_LABELS = dash_feature_labels.*?\$GRANTABLE = .*?;/s', $form, $setup);
    $BRANCH_ID = 2;
    $user['is_super'] = 0;
    dash_refresh_session_user($user);
    eval($setup[0]);
    check(in_array('news', $GRANTABLE, true) && !in_array('hero', $GRANTABLE, true), $file . ': grants must match enabled branch features.');
    check(!in_array('maxapedia', $GRANTABLE, true), $file . ': HQ grants stay out of branch forms.');
}
echo "PASS live role/direct permissions, mixed finance navigation, branch isolation and grant forms\n";

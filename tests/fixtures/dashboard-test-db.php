<?php
declare(strict_types=1);

// SQLite executes the production SELECTs; only MySQL interval/upsert spelling is adapted.
final class DashboardTestPDO extends Pdo\Sqlite
{
    public function __construct(string $file = ':memory:')
    {
        parent::__construct('sqlite:' . $file, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $this->createFunction('CONCAT', static fn(...$values) => implode('', $values));
        $this->createFunction('NOW', static fn() => date('Y-m-d H:i:s'));
        $this->createFunction('JSON_UNQUOTE', static fn($value) => $value);
    }
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $query = str_replace('NOW() - INTERVAL 30 DAY', "datetime(NOW(), '-30 days')", $query);
        $query = str_replace('ON DUPLICATE KEY UPDATE read_at = VALUES(read_at)', 'ON CONFLICT(user_id, branch_id, event_key) DO UPDATE SET read_at = excluded.read_at', $query);
        return parent::prepare($query, $options);
    }
}

function dashboard_test_seed(PDO $db): void
{
    $db->exec("CREATE TABLE IF NOT EXISTS tickets (id INTEGER PRIMARY KEY, branch_id INTEGER, target TEXT, created_by INTEGER, status TEXT, escalated_from INTEGER);
      CREATE TABLE IF NOT EXISTS ticket_messages (id INTEGER PRIMARY KEY, ticket_id INTEGER, user_id INTEGER, created_at TEXT);
      CREATE TABLE IF NOT EXISTS ticket_reads (ticket_id INTEGER, user_id INTEGER, last_read_at TEXT);
      CREATE TABLE IF NOT EXISTS news (id INTEGER PRIMARY KEY, branch_id INTEGER, status TEXT);
      CREATE TABLE IF NOT EXISTS audit_log (id INTEGER PRIMARY KEY, action TEXT, metadata TEXT, created_at TEXT);
      CREATE TABLE IF NOT EXISTS medical_records (id INTEGER PRIMARY KEY, branch_id INTEGER, status TEXT, created_at TEXT, full_name TEXT);
      CREATE TABLE IF NOT EXISTS dashboard_notification_reads (user_id INTEGER, branch_id INTEGER, event_key TEXT, read_at TEXT, PRIMARY KEY(user_id, branch_id, event_key));");
    if ($db->query('SELECT COUNT(*) FROM tickets')->fetchColumn()) return;
    $now = date('Y-m-d H:i:s');
    $db->exec("INSERT INTO tickets VALUES
      (1,1,'branch',2,'open',NULL),(2,1,'branch',3,'open',NULL),(3,1,'hq',1,'answered',NULL),
      (4,2,'hq',20,'open',NULL),(5,1,'hq',1,'open',NULL),(6,1,'hq',1,'closed',1);
      INSERT INTO news VALUES (101,1,'review'),(102,1,'review'),(103,2,'review');");
    $stmt = $db->prepare('INSERT INTO ticket_messages VALUES (?,?,?,?)');
    foreach ([[11,1,2],[12,2,3],[13,3,10],[14,4,20],[15,5,1],[16,6,10]] as $row) $stmt->execute(array_merge($row, [$now]));
    $db->prepare("INSERT INTO audit_log VALUES (1,'news_status_changed',?,?)")->execute([json_encode(['news_id'=>101,'status'=>'review']), $now]);
    $stmt = $db->prepare('INSERT INTO medical_records VALUES (?,?,?,?,?)');
    foreach ([[201,1,'new'],[202,1,'reviewing'],[203,1,'closed'],[204,2,'new']] as $row) $stmt->execute(array_merge($row, [$now, 'PRIVATE PATIENT DATA']));
}

function dashboard_test_context(array $overrides = []): array
{
    return array_replace(['user'=>1,'branch'=>1,'super'=>false,'branch_admin'=>true,'tickets'=>true,'news'=>true,'medical'=>true], $overrides);
}

<?php
declare(strict_types=1);
$source = file_get_contents(__DIR__ . '/../public_html/dashboard/medical-records.php');
if (!preg_match('/\$isSuper = dash_is_super\(\);.*?(?=try \{)/s', $source, $filters)
    || !preg_match('/\$stmt = \$pdo->prepare\(.*?\$stmt->execute\(\$params\);/s', $source, $query)) {
    throw new RuntimeException('Medical listing query not found');
}
$admin = false;
function dash_is_super(): bool { return $GLOBALS['admin']; }
function dash_is_hq_view(): bool { return true; }
function dash_active_branch_id(): int { return 1; }
function medical_ids(PDO $pdo, string $code, array $input): array {
    $_GET = $input;
    eval($code);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}
function expect_ids(array $expected, array $actual): void {
    if ($expected !== $actual) throw new RuntimeException('Unexpected medical scope: ' . json_encode($actual));
}
$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec("CREATE TABLE branches(id INTEGER, name TEXT);
    INSERT INTO branches VALUES(1,'HQ'),(2,'Other');
    CREATE TABLE medical_records(id INTEGER, branch_id INTEGER, status TEXT, full_name TEXT, phone TEXT, province TEXT, city TEXT);
    INSERT INTO medical_records VALUES(1,1,'new','One','','',''),(2,1,'closed','Two','','',''),(3,2,'new','Three','','',''),(4,1,'reviewing','Four','','','');");
$code = $filters[0] . $query[0];
expect_ids([4,2,1], medical_ids($pdo, $code, []));
expect_ids([], medical_ids($pdo, $code, ['id'=>3,'export'=>'xlsx']));
$admin = true;
expect_ids([4,1], medical_ids($pdo, $code, ['scope'=>'active','status'=>'pending','export'=>'xlsx']));
expect_ids([3], medical_ids($pdo, $code, ['id'=>3,'export'=>'xlsx']));
expect_ids([4], medical_ids($pdo, $code, ['q'=>'Four','export'=>'xlsx']));
for ($id=5;$id<=209;$id++) $pdo->exec("INSERT INTO medical_records VALUES($id,2,'closed','More','','','')");
if (count(medical_ids($pdo,$code,[])) !== 200 || count(medical_ids($pdo,$code,['export'=>'xlsx'])) !== 209) {
    throw new RuntimeException('Listing/export row limit mismatch');
}
echo "PASS medical listing and XLSX branch scope, pending/id/search filters and export row limit\n";

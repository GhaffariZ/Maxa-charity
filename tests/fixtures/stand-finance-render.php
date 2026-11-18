<?php
declare(strict_types=1);
require __DIR__ . '/../../public_html/core/stand-finance.php';
date_default_timezone_set('Asia/Tehran');
$pdo=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$pdo->sqliteCreateFunction('CONCAT',static fn(...$args)=>implode('',$args));
$pdo->sqliteCreateFunction('DATE_FORMAT',static fn($date,$format)=>date($format==='%Y-%m'?'Y-m':'Y-m-d',strtotime($date)));
$pdo->sqliteCreateFunction('HOUR',static fn($date)=>(int)date('G',strtotime($date)));
$pdo->exec("CREATE TABLE branches(id INTEGER PRIMARY KEY,name TEXT,slug TEXT,status TEXT,is_hq INTEGER);
 INSERT INTO branches VALUES(1,'HQ','hq','active',1),(2,'Tehran','tehran-branch','active',0),(3,'Qom','qom-branch','active',0);
 CREATE TABLE stands(id INTEGER PRIMARY KEY,title TEXT);
 INSERT INTO stands VALUES(1,'Stand');
 CREATE TABLE orders(id INTEGER PRIMARY KEY,tracking_code TEXT,branch_id INTEGER,stand_id INTEGER,total_price INTEGER,from_user TEXT,to_user TEXT,created_at TEXT);
 INSERT INTO orders VALUES(1,'PAID-STAND',2,1,300000,'Sender','Receiver','2025-01-01'),(2,'UNPAID-LEGACY',2,1,7000000,'Sender','Receiver','2026-11-17'),
 (3,'PENDING-STAND',2,1,900000,'Sender','Receiver','2026-11-17'),(4,'FAILED-STAND',2,1,800000,'Sender','Receiver','2026-11-17'),(5,'OTHER-BRANCH',3,1,150000,'Sender','Receiver','2025-01-01');
 CREATE TABLE stand_payments(id INTEGER PRIMARY KEY,order_id INTEGER,branch_id INTEGER,source TEXT,receipt_token TEXT,center_slug TEXT,gateway TEXT,amount INTEGER,status TEXT,remote_payment_id TEXT,redirect_data TEXT,created_at TEXT,paid_at TEXT);
 INSERT INTO stand_payments VALUES
 (1,1,2,'stands','a','tehran-branch','vandar',300000,'success','p1',NULL,'2025-01-01','2026-11-17 12:00:00'),
 (3,3,2,'stands','c','tehran-branch','vandar',900000,'pending','p3',NULL,'2026-11-17',NULL),
 (4,4,2,'stands','d','tehran-branch','vandar',800000,'failed','p4',NULL,'2026-11-17',NULL),
 (5,5,3,'stands','e','qom-branch','vandar',150000,'success','p5',NULL,'2025-01-01','2026-11-17 12:00:00');
 CREATE TABLE panel_donations(id INTEGER PRIMARY KEY,reference TEXT,user_id INTEGER,campaign_id INTEGER,branch_id INTEGER,amount INTEGER,status TEXT,type TEXT,paid_at TEXT,created_at TEXT);
 INSERT INTO panel_donations VALUES(1,'ONLINE-SUPPORT',7,NULL,2,500000,'success','online','2026-11-17 12:00:00','2026-11-17 12:00:00');
 CREATE TABLE panel_users(id INTEGER PRIMARY KEY,email TEXT,phone TEXT);
 INSERT INTO panel_users VALUES(7,'donor@example.test','09121234567');
 CREATE TABLE user_profiles(user_id INTEGER,first_name TEXT,last_name TEXT);
 INSERT INTO user_profiles VALUES(7,'Donor','Name');
 CREATE TABLE campaigns(id INTEGER PRIMARY KEY,title TEXT,target_amount INTEGER,branch_id INTEGER);");
$mode=$argv[1] ?? 'view';
$hq=($argv[2] ?? '')==='hq';
function dash_is_super():bool { return $GLOBALS['hq']; }
function dash_is_hq_view():bool { return $GLOBALS['hq']; }
function dash_active_branch_id():int { return $GLOBALS['hq']?1:2; }
function dash_load_branch(int $id):array { return $GLOBALS['pdo']->query('SELECT * FROM branches WHERE id='.(int)$id)->fetch(); }
function dash_all_branches():array { return $GLOBALS['pdo']->query('SELECT * FROM branches')->fetchAll(); }
function e(?string $v):string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
$_GET=['source'=>'stands','start_date'=>'2026-11-01','end_date'=>'2026-11-30'];
if ($hq) $_GET['branch_id']='all';
if ($mode==='view' || $mode==='all-view') {
    if ($mode==='all-view') unset($_GET['source']);
    $dbError=null;
    $source=file_get_contents(__DIR__.'/../../public_html/dashboard/financial-management.php');
    eval(substr($source,strpos($source,'function q(PDO')));
} else {
    $_GET['format']=$mode;
    $source=file_get_contents(__DIR__.'/../../public_html/dashboard/financial-export.php');
    eval(substr($source,strpos($source,'function gregorian_to_jalali')));
}

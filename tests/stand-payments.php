<?php
declare(strict_types=1);
require __DIR__.'/../public_html/core/stand-payments.php';
require __DIR__.'/../public_html/core/stand-finance.php';
function check(bool $v,string $m):void { if (!$v) throw new RuntimeException($m); }
function rejected(callable $fn):void { try {$fn();} catch(StandPaymentException $e){return;} throw new RuntimeException('Unsafe request was accepted'); }
final class StandTestPDO extends PDO {
 public function prepare(string $sql,array $options=[]):PDOStatement|false {
  return parent::prepare(str_replace('ON DUPLICATE KEY UPDATE order_id = order_id','ON CONFLICT(order_id) DO NOTHING',$sql),$options);
 }
}
$db=new StandTestPDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$db->exec("CREATE TABLE branches(id INTEGER PRIMARY KEY,name TEXT,slug TEXT,status TEXT);
INSERT INTO branches VALUES(2,'Tehran','tehran-branch','active'),(3,'Qom','qom-branch','active');
CREATE TABLE branch_features(branch_id INTEGER,feature TEXT,enabled INTEGER);INSERT INTO branch_features VALUES(2,'stands',1),(3,'stands',1);
CREATE TABLE stands(id INTEGER PRIMARY KEY,title TEXT);INSERT INTO stands VALUES(1,'Stand');
CREATE TABLE orders(id INTEGER PRIMARY KEY,user_id INTEGER,tracking_code TEXT,branch_id INTEGER,stand_id INTEGER,total_price INTEGER,from_user TEXT,to_user TEXT,created_at TEXT);
ALTER TABLE orders ADD COLUMN order_type TEXT NOT NULL DEFAULT 'stand';
CREATE TABLE stand_payments(id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER UNIQUE,branch_id INTEGER,source TEXT DEFAULT 'stands',receipt_token TEXT UNIQUE,center_slug TEXT,amount INTEGER,status TEXT DEFAULT 'pending',remote_payment_id TEXT UNIQUE,gateway_ref_id TEXT UNIQUE,redirect_data TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP,paid_at TEXT);");
for($i=1;$i<=5;$i++) $db->prepare('INSERT INTO orders (id,user_id,tracking_code,branch_id,stand_id,total_price,from_user,to_user,created_at) VALUES(?,7,?,?,1,300000,?,?,?)')->execute([$i,'ORD-'.$i,$i===5?3:2,'Sender','Receiver','2025-01-01']);
\Maksa\Core\Config::set('STAND_VANDAR_API_KEY','test-only-secret');
$calls=[];$tokens=[];$failSend=false;
$transport=function($method,$payload)use(&$calls,&$tokens,&$failSend){
 $calls[]=[$method,$payload];
 if($method==='send') {
  if($failSend){$failSend=false;throw new StandPaymentException('network failure');}
  $token='TOKEN-'.str_pad((string)(count($tokens)+1),12,'0',STR_PAD_LEFT);
  $tokens[$token]=['status'=>1,'code'=>0,'amount'=>(string)$payload['amount'].'.00','factorNumber'=>$payload['factorNumber'],'transId'=>'REF-'.count($tokens),'paymentDate'=>'2026-11-18 12:00:00'];
  return ['status'=>1,'token'=>$token];
 }
 $token=$payload['token'];
 if($method==='verify') {$tokens[$token]['code']=2;return $tokens[$token];}
 return $tokens[$token];
};
$factory=fn($slug)=>new StandVandarGateway($slug,$transport);
$ledger=new StandPayments($db,$factory);
$order=$ledger->orderForUser('ORD-1',7);
rejected(fn()=>$ledger->orderForUser('ORD-1',8));
$db->exec("UPDATE orders SET order_type='product' WHERE id=5");
rejected(fn()=>$ledger->orderForUser('ORD-5',7));
rejected(fn()=>$ledger->orderForUser("' OR 1=1 --",7));
check(stand_finance_rows($db,2)===[],'Unpaid legacy orders cannot become revenue');
$first=$ledger->start($order,'macsa.ir','۰۹۱۲۱۲۳۴۵۶۷','Sender');
check($calls[0][0]==='send' && $calls[0][1]['amount']===3000000,'Direct send must convert Toman to Rial exactly once');
check(!isset($calls[0][1]['campaign_id']) && str_contains($calls[0][1]['callback_url'],'/stand-payment-callback.php?receipt='),'Stand checkout is independent of campaigns');
check($first['source']==='stands' && $first['status']==='pending','Explicit source tag on pending receipt');
check(str_starts_with($first['redirect_url'],'/stand-payment-redirect.php?target='),'Stand redirect is independent from campaign routes');
$ledger->start($order,'macsa.ir','09121234567','Sender');
check(count(array_filter($calls,fn($c)=>$c[0]==='send'))===1,'Repeated request must reuse the saved token');
$receipt=$ledger->byOrder(1);$token=$receipt['remote_payment_id'];
rejected(fn()=>$ledger->callback($receipt['receipt_token'],'wrong-token'));
check(stand_finance_rows($db,2)===[],'Browser return/pending status is not income');
$tokens[$token]['code']=1;$tokens[$token]['amount']='300000.00';
rejected(fn()=>$ledger->sync($receipt));
check(!array_filter($calls,fn($c)=>$c[0]==='verify'),'Incorrect amount must be rejected before verification');
$tokens[$token]['amount']='3000000.00';$tokens[$token]['factorNumber']='wrong-invoice';
rejected(fn()=>$ledger->sync($receipt));
$tokens[$token]['factorNumber']='stand-'.$receipt['id'];
$paid=$ledger->callback($receipt['receipt_token'],$token);
check($paid['status']==='success' && $paid['paid_at']==='2026-11-18 12:00:00','Bank verification and bank date create a paid receipt');
$ledger->callback($receipt['receipt_token'],$token);
check(count(array_filter($calls,fn($c)=>$c[0]==='verify'))===1,'Duplicate bank callbacks must not verify or credit twice');
check(array_sum(array_column(stand_finance_rows($db,2),'amount'))===300000,'Paid stand revenue counted once');
check(stand_finance_rows($db,3)===[] && count(stand_finance_rows($db,null))===1,'Branch isolation and HQ scope');
check(stand_finance_rows($db,2,'2025-01-01','2025-01-31')===[],'Use payment date, not order date');
check($ledger->start($order,'macsa.ir','09121234567','Sender')['status']==='success','Paid orders cannot be charged again');
$second=$ledger->start($ledger->orderForUser('ORD-2',7),'macsa.ir','09121234567','Sender');
$r2=$ledger->byOrder(2);$t2=$r2['remote_payment_id'];$tokens[$t2]['code']=4;
check($ledger->sync($r2)['status']==='failed','Provider-verified failure is terminal');
check(count(stand_finance_rows($db,2))===1,'Failed payment cannot increase revenue');
$failSend=true;
rejected(fn()=>$ledger->start($ledger->orderForUser('ORD-3',7),'macsa.ir','09121234567','Sender'));
check($ledger->byOrder(3)['status']==='pending','A send timeout resets the unexposed-token claim for a safe retry');
$ledger->start($ledger->orderForUser('ORD-3',7),'macsa.ir','09121234567','Sender');
rejected(fn()=>$ledger->start($ledger->orderForUser('ORD-4',7),'localhost','09121234567','Sender'));
rejected(fn()=>$ledger->start($ledger->orderForUser('ORD-4',7),'macsa.ir','bad','Sender'));
$db->exec('UPDATE branch_features SET enabled=0 WHERE branch_id=2');
rejected(fn()=>$ledger->start($ledger->orderForUser('ORD-4',7),'macsa.ir','09121234567','Sender'));
echo "PASS direct Vandar: no campaigns, bank binding/decimal amounts, callback replay, request replay, timeout recovery, paid date, failure/unpaid exclusion and branch finance\n";

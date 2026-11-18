<?php
declare(strict_types=1);
// Local-only demonstration using real controllers, checkout JS, order and finance templates.
if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true)) { http_response_code(403); exit; }
$root=dirname(__DIR__); $web=$root.'/public_html';
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if (preg_match('/\.(?:css|js|png|jpe?g|webp|svg|woff2?|ttf|ico)$/i',$path)) {
    $asset=realpath($web.$path);
    if ($asset && str_starts_with(strtolower($asset),strtolower(realpath($web).DIRECTORY_SEPARATOR))) return false;
    http_response_code(404); exit;
}
date_default_timezone_set('Asia/Tehran');session_name('STAND_PREVIEW');session_start();
spl_autoload_register(static function($class) use($web){if(str_starts_with($class,'Maksa\\')){ $f=$web.'/api/src/'.str_replace('\\','/',substr($class,6)).'.php';if(is_file($f))require $f;}});
require_once $web.'/core/stand-payments.php';require_once $web.'/core/stand-finance.php';
class PreviewPDO extends PDO {
    public function prepare(string $sql,array $options=[]):PDOStatement|false {return parent::prepare(str_replace('ON DUPLICATE KEY UPDATE order_id = order_id','ON CONFLICT(order_id) DO NOTHING',$sql),$options);}
    public function exec(string $sql):int|false {if(str_starts_with(ltrim($sql),'ALTER TABLE'))return 0;return parent::exec($sql);}
}
$dir=$root.'/.local/stand-preview';if(!is_dir($dir))mkdir($dir,0770,true);
$pdo=new PreviewPDO('sqlite:'.$dir.'/preview.sqlite',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$pdo->sqliteCreateFunction('NOW',static fn()=>date('Y-m-d H:i:s'));
$pdo->sqliteCreateFunction('CONCAT',static fn(...$a)=>implode('',$a));
$pdo->sqliteCreateFunction('DATE_FORMAT',static fn($d,$f)=>date($f==='%Y-%m'?'Y-m':'Y-m-d',strtotime($d)));
$pdo->sqliteCreateFunction('HOUR',static fn($d)=>(int)date('G',strtotime($d)));
$pdo->exec("CREATE TABLE IF NOT EXISTS branches(id INTEGER PRIMARY KEY,name TEXT,slug TEXT,province TEXT,city TEXT,status TEXT,is_hq INTEGER);
CREATE TABLE IF NOT EXISTS branch_features(branch_id INTEGER,feature TEXT,enabled INTEGER);
CREATE TABLE IF NOT EXISTS stands(id INTEGER PRIMARY KEY,branch_id INTEGER,title TEXT,stand_type TEXT,unit_price INTEGER,image TEXT,description TEXT,is_active INTEGER,sort_order INTEGER);
CREATE TABLE IF NOT EXISTS orders(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,tracking_code TEXT,branch_id INTEGER,stand_id INTEGER,province TEXT,city TEXT,image TEXT,order_date TEXT,quantity INTEGER,unit_price INTEGER,total_price INTEGER,from_user TEXT,to_user TEXT,message TEXT,address TEXT,checkout_key TEXT UNIQUE,created_at TEXT);
CREATE TABLE IF NOT EXISTS stand_payments(id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER UNIQUE,branch_id INTEGER,source TEXT DEFAULT 'stands',receipt_token TEXT UNIQUE,center_slug TEXT,amount INTEGER,status TEXT DEFAULT 'pending',remote_payment_id TEXT UNIQUE,gateway_ref_id TEXT UNIQUE,redirect_data TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP,paid_at TEXT);
CREATE TABLE IF NOT EXISTS preview_gateway(token TEXT PRIMARY KEY,amount INTEGER,invoice TEXT,receipt TEXT,status TEXT DEFAULT 'pending');
CREATE TABLE IF NOT EXISTS panel_users(id INTEGER PRIMARY KEY,email TEXT,phone TEXT,status TEXT,email_verified_at TEXT,created_at TEXT,last_login_at TEXT);
CREATE TABLE IF NOT EXISTS user_profiles(user_id INTEGER,first_name TEXT,last_name TEXT,phone TEXT,national_code TEXT,avatar_url TEXT,postal_address TEXT,timezone TEXT,locale TEXT,kindness_points INTEGER,donor_tier_id INTEGER);
CREATE TABLE IF NOT EXISTS donor_tiers(id INTEGER PRIMARY KEY,slug TEXT,name_fa TEXT);
CREATE TABLE IF NOT EXISTS panel_donations(id INTEGER PRIMARY KEY,reference TEXT,user_id INTEGER,campaign_id INTEGER,branch_id INTEGER,amount INTEGER,status TEXT,type TEXT,paid_at TEXT,created_at TEXT);
CREATE TABLE IF NOT EXISTS campaigns(id INTEGER PRIMARY KEY,title TEXT,target_amount INTEGER,branch_id INTEGER);");
if(!(int)$pdo->query('SELECT COUNT(*) FROM branches')->fetchColumn()){
 $pdo->exec("INSERT INTO branches VALUES(2,'تهران','tehran-branch','تهران','تهران','active',0);
INSERT INTO branch_features VALUES(2,'stands',1);
INSERT INTO stands VALUES(9,2,'استند تبریک — طرح آفتاب','congrats',300000,'/uploads/stand/happy/1.jpg','استند خیریه مکسا',1,1),(10,2,'استند تسلیت — طرح آرامش','condolence',350000,'/uploads/stand/sad/1.jpg','استند خیریه مکسا',1,2);
INSERT INTO panel_users(id,email,phone,status) VALUES(7,'preview@example.test','09121234567','active');
INSERT INTO user_profiles(user_id,first_name,last_name,phone) VALUES(7,'کاربر','آزمایشی','09121234567');
INSERT INTO panel_donations VALUES(1,'ONLINE-DEMO',7,NULL,2,2000000,'success','online',datetime('now'),datetime('now')); ");
}
(new ReflectionProperty(Maksa\Core\Database::class,'pdo'))->setValue(null,$pdo);
$origin='http://localhost:8092';
$factory=static function($slug)use($pdo,$origin){return new class($pdo,$origin){
 public function __construct(private PDO $db,private string $origin){}
 public function request(int $amount,string $invoice,string $callback,string $mobile):array{
  $token='PREVIEW-'.bin2hex(random_bytes(10));parse_str((string)parse_url($callback,PHP_URL_QUERY),$q);
  $this->db->prepare('INSERT INTO preview_gateway(token,amount,invoice,receipt) VALUES(?,?,?,?)')->execute([$token,$amount,$invoice,$q['receipt']]);
  return ['authority'=>$token,'redirect_url'=>$this->origin.'/preview-bank?token='.$token];
 }
 public function verify(string $token,int $amount,string $invoice):array{
  $s=$this->db->prepare('SELECT * FROM preview_gateway WHERE token=?');$s->execute([$token]);$r=$s->fetch();
  if(!$r || (int)$r['amount']!==$amount || $r['invoice']!==$invoice)throw new StandPaymentException('مغایرت داده آزمایشی');
  return ['status'=>$r['status'],'ref_id'=>'DEMO-'.substr($token,-8),'paid_at'=>date('Y-m-d H:i:s')];
 }
};};
$ledger=new StandPayments($pdo,$factory,$origin);
function e(?string $v):string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function dash_is_super():bool{return false;}function dash_is_hq_view():bool{return false;}
function dash_active_branch_id():int{return 2;}function dash_can(string $v):bool{return true;}
function dash_pdo():PDO{return $GLOBALS['pdo'];}function dash_user():array{return ['id'=>7,'is_super'=>false];}
function dash_load_branch(int $id):array{$s=$GLOBALS['pdo']->prepare('SELECT * FROM branches WHERE id=?');$s->execute([$id]);return $s->fetch();}
function dash_all_branches():array{return $GLOBALS['pdo']->query('SELECT * FROM branches')->fetchAll();}
function csrf_token():string{return $_SESSION['csrf']??=bin2hex(random_bytes(20));}
function csrf_field():string{return '<input type="hidden" name="csrf_token" value="'.csrf_token().'">';}
function csrf_check():void{if(!hash_equals(csrf_token(),(string)($_POST['csrf_token']??''))){http_response_code(419);exit;}}
function preview_json(array $data):never{header('Content-Type: application/json');echo json_encode(['success'=>true,'data'=>$data],JSON_UNESCAPED_UNICODE);exit;}
function preview_nav():void{echo '<div style="position:sticky;top:0;z-index:10000;background:#102e2f;color:#fff;padding:12px 24px;display:flex;gap:24px;flex-wrap:wrap;font:14px Tahoma;direction:rtl"><b>پیش‌نمایش آزمایشی؛ بدون پرداخت واقعی</b><a style="color:#b6eee1" href="/stand-order.php">خرید استند</a><a style="color:#b6eee1" href="/dashboard/orders.php">استندها و سفارشات</a><a style="color:#b6eee1" href="/dashboard/financial-management.php">گزارش مالی</a></div>';}
try{
 if($path==='/api/auth/refresh')preview_json(['access_token'=>'local-preview-only']);
 if($path==='/api/user/me')preview_json(['full_name'=>'کاربر آزمایشی','phone'=>'09121234567']);
 if($path==='/api/stands/provinces')preview_json([['province'=>'تهران','branch_id'=>2,'branch_name'=>'تهران','cities'=>['تهران']]]);
 if($path==='/api/stands')preview_json(['stands'=>$pdo->query('SELECT * FROM stands')->fetchAll()]);
 if($path==='/api/orders' || preg_match('#^/api/orders/([^/]+)/payment$#',$path,$matches)){
  $r=Maksa\Core\Request::capture();$r->authUserId=7;$r->params=['tracking'=>$matches[1]??''];
  $controller=new Maksa\Controllers\OrderController($ledger);
  if($path==='/api/orders')$controller->create($r);elseif($r->method==='POST')$controller->startPayment($r);else $controller->paymentStatus($r);exit;
 }
 if($path==='/preview-bank'){
  $token=(string)($_GET['token']??'');$s=$pdo->prepare('SELECT * FROM preview_gateway WHERE token=?');$s->execute([$token]);$g=$s->fetch();if(!$g){http_response_code(404);exit;}
  if($_SERVER['REQUEST_METHOD']==='POST'){
   csrf_check();$state=($_POST['result']??'')==='success'?'success':'failed';
   $pdo->prepare('UPDATE preview_gateway SET status=? WHERE token=?')->execute([$state,$token]);
   header('Location: /stand-payment-callback.php?receipt='.$g['receipt'].'&token='.$token);exit;
  }
  echo '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><link rel="stylesheet" href="/font.css"><body style="font-family:Vazirmatn,Tahoma;background:#f4f7f6;padding:8vh 24px;text-align:center">';preview_nav();
  echo '<main style="background:white;padding:40px;max-width:540px;margin:40px auto;border:1px solid #d5e6e0;border-radius:16px"><h1>شبیه‌ساز درگاه پرداخت استند</h1><p>این صفحه بانک واقعی نیست و اطلاعات کارت دریافت نمی‌کند.</p><h2>'.number_format((int)$g['amount']).' تومان</h2><p>منبع پرداخت: استند</p><form method="post">'.csrf_field().'<button name="result" value="success" style="padding:16px;background:#007d72;color:white;border:0;border-radius:9px;font:inherit">شبیه‌سازی پرداخت موفق</button> <button name="result" value="failed" style="padding:16px;font:inherit">شبیه‌سازی پرداخت ناموفق</button></form></main></body></html>';exit;
 }
 if($path==='/stand-payment-callback.php'){
  $receipt=$ledger->callback((string)($_GET['receipt']??''),(string)($_GET['token']??''));$receiptError='';$standPreview=true;require $web.'/core/stand-receipt-view.php';exit;
 }
 if($path==='/dashboard/financial-management.php'){
  if(isset($_GET['campaign_finance'])){header('Content-Type: application/json');echo '{"status":"success","data":{"campaigns":[],"payments":[]}}';exit;}
  $dbError=null;$s=file_get_contents($web.$path);ob_start();eval(substr($s,strpos($s,'function q(PDO')));$html=ob_get_clean();echo preg_replace('/(<body[^>]*>)/','$1'.ob_nav(),$html,1);exit;
 }
 if($path==='/dashboard/financial-export.php'){$s=file_get_contents($web.$path);eval(substr($s,strpos($s,'function gregorian_to_jalali')));exit;}
 if($path==='/dashboard/orders.php'){$s=file_get_contents($web.$path);ob_start();eval(substr($s,strpos($s,'$isSuper =')));$html=ob_get_clean();echo preg_replace('/(<body[^>]*>)/','$1'.ob_nav(),$html,1);exit;}
 if($path==='/' || $path==='/stand-order.php'){
  define('MACSA_SITE_LOCALE_BUFFER',true);require $web.'/core/site-locale.php';
  $initialProvincesList=[['province'=>'تهران','branch_id'=>2,'branch_name'=>'تهران','cities'=>['تهران']]];
  echo '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/font.css"></head><body style="margin:0;background:#f6f8f8">';preview_nav();
  echo '<script>window.__maxa_access_token="local-preview-only";</script>';
  $s=file_get_contents($web.'/dashboard/components/stand-order/component.php');eval('?>'.substr($s,strpos($s,'<!-- Persian Datepicker CSS -->')));echo '</body></html>';exit;
 }
 http_response_code(404);echo 'Preview route not found';
}catch(Maksa\Core\Exceptions\ApiException $e){Maksa\Core\Response::error($e->getStatusCode(),$e->getErrorCode(),$e->getMessage());}
catch(Throwable $e){http_response_code(500);header('Content-Type: text/plain');echo get_class($e).': '.$e->getMessage();}
function ob_nav():string{ob_start();preview_nav();return ob_get_clean();}

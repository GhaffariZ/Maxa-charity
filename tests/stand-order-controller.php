<?php
declare(strict_types=1);
namespace Maksa\Core {
    final class Response {
        public static function success(array $data, int $status=200): never { throw new \StandResponse($data); }
    }
}
namespace {
    final class StandResponse extends RuntimeException {
        public function __construct(public array $data) { parent::__construct('response'); }
    }
    spl_autoload_register(static function($class) {
        if (str_starts_with($class, 'Maksa\\')) require __DIR__ . '/../public_html/api/src/' . str_replace('\\','/',substr($class,6)) . '.php';
    });
    function check(bool $ok,string $message):void { if (!$ok) throw new RuntimeException($message); }
    $db=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $db->sqliteCreateFunction('NOW',static fn()=>date('Y-m-d H:i:s'));
    $db->exec("CREATE TABLE branches(id INTEGER PRIMARY KEY,name TEXT,slug TEXT,province TEXT,city TEXT,is_hq INTEGER,status TEXT);
        INSERT INTO branches VALUES(2,'Tehran','tehran-branch','Tehran','Tehran',0,'active');
        CREATE TABLE stands(id INTEGER PRIMARY KEY,branch_id INTEGER,unit_price INTEGER,image TEXT,is_active INTEGER,title TEXT);
        INSERT INTO stands VALUES(9,2,300000,'/stand.jpg',1,'Stand');
        CREATE TABLE branch_features(branch_id INTEGER,feature TEXT,enabled INTEGER);
        INSERT INTO branch_features VALUES(2,'stands',1);
        CREATE TABLE orders(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,tracking_code TEXT,branch_id INTEGER,stand_id INTEGER,
            province TEXT,city TEXT,image TEXT,order_date TEXT,quantity INTEGER,unit_price INTEGER,total_price INTEGER,
            from_user TEXT,to_user TEXT,message TEXT,address TEXT,checkout_key TEXT UNIQUE,created_at TEXT);
        CREATE TABLE stand_payments(order_id INTEGER PRIMARY KEY,status TEXT,paid_at TEXT);");
    (new ReflectionProperty(Maksa\Core\Database::class,'pdo'))->setValue(null,$db);
    function create_order(array $body,int $userId=7): array {
        $r=(new ReflectionClass(Maksa\Core\Request::class))->newInstanceWithoutConstructor();
        (new ReflectionMethod(Maksa\Core\Request::class,'__construct'))->invoke($r,'POST','/orders',$body,[],[],[],[]);
        $r->authUserId=$userId;
        try { (new Maksa\Controllers\OrderController())->create($r); }
        catch (StandResponse $result) { return $result->data; }
        throw new RuntimeException('No response');
    }
    $input=['stand_id'=>9,'branch_id'=>999,'sender_name'=>'Sender','sender_phone'=>'09121234567','receiver_name'=>'Receiver',
        'event_address'=>'Address','quantity'=>2,'unit_price'=>1,'total_price'=>1,'checkout_key'=>str_repeat('a',32)];
    $created=create_order($input);
    check($created['branch_id']===2 && $created['unit_price']===300000 && $created['total_price']===600000,'Server catalog owns branch and price; client values are ignored.');
    check(create_order($input)['tracking_code']===$created['tracking_code'],'Retry returns the original order.');
    check((int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn()===1,'A retry must not create a duplicate order.');
    create_order($input,8);
    check((int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn()===2,'Checkout identities are scoped to the authenticated payer.');
    $input['quantity']=0;
    $input['checkout_key']=str_repeat('b',32);
    try { create_order($input); throw new LogicException('Invalid quantity accepted'); }
    catch (Maksa\Core\Exceptions\ApiException $e) { check($e->getErrorCode()==='invalid_quantity','Quantity validation failed.'); }
    $input['quantity']=1;
    $db->exec('UPDATE branch_features SET enabled=0');
    try { create_order($input); throw new LogicException('Disabled branch feature accepted'); }
    catch (Maksa\Core\Exceptions\ApiException $e) { check($e->getErrorCode()==='stands_disabled','Disabled branch must reject new orders.'); }
    $db->exec("INSERT INTO orders (user_id,order_type,payment_status,paid_at) VALUES (7,'product','paid','2026-11-17')");
    $db->exec("INSERT INTO order_items (order_id,product_title,quantity,unit_price,total_price) VALUES (3,'Shop item',1,10000,10000)");
    $db->exec("INSERT INTO stand_payments VALUES (1,'success','2026-11-18')");
    $request = (new ReflectionClass(Maksa\Core\Request::class))->newInstanceWithoutConstructor();
    $request->authUserId = 7;
    try {
        (new Maksa\Controllers\OrderController())->getMyOrders($request);
        throw new LogicException('No response');
    } catch (StandResponse $result) {
        [$product, $stand] = $result->data['orders'];
        check(count($result->data['orders']) === 2, 'Only orders belonging to the signed-in user are returned.');
        check($product['payment_status'] === 'paid' && $product['paid_at'] === '2026-11-17' &&
            $product['items'][0]['product_title'] === 'Shop item', 'Product payment and item data must be preserved.');
        check($stand['payment_status'] === 'success' && $stand['paid_at'] === '2026-11-18' &&
            $stand['items'] === [], 'Stand payment status and date must come from its receipt.');
    }
    echo "PASS actual order controller: authoritative pricing/branch, duplicate and cross-user checkout, quantity and disabled-feature validation\n";
}

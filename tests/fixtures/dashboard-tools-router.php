<?php
declare(strict_types=1);
// Local preview only: real shell/medical templates and query service, entirely synthetic data.
date_default_timezone_set('Asia/Tehran');
$root = dirname(__DIR__, 2);
$public = $root . '/public_html';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path !== '/dashboard/index.php' && $path !== '/dashboard/notifications.php' && is_file($public . $path)) {
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if (in_array($ext, ['css','js','woff2','woff','png','jpg','svg'], true)) return false;
}
require_once $public . '/core/icons.php';
require_once $public . '/core/loading-ui.php';
require_once $public . '/core/dashboard-notifications.php';
require_once __DIR__ . '/dashboard-test-db.php';
function e(?string $text): string { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
function dash_is_branch_admin(): bool { return true; }
function money_compact($value): string { return number_format((float)$value); }
function medical_e(?string $text): string { return e($text); }
function medical_status(string $status): string { return ['new'=>'جدید','reviewing'=>'در حال بررسی','closed'=>'بسته شده'][$status] ?? $status; }
function render_template(string $file, array $data): void {
    extract($data);
    $source = file_get_contents($file);
    $offset = stripos($source, '<!doctype html>');
    $template = str_replace('__DIR__', var_export(dirname($file), true), substr($source, $offset));
    eval('?>' . $template);
}
$role = ($_COOKIE['dashboard-test-role'] ?? 'admin');
$restricted = $role === 'user';
$context = dashboard_test_context($restricted ? ['user'=>2,'branch_admin'=>false,'news'=>false,'medical'=>false] : []);
if ($path === '/dashboard/notifications.php') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    if (($_COOKIE['dashboard-test-fault'] ?? '') === '1') { http_response_code(503); echo json_encode(['error'=>'خطای آزمایشی در دریافت اعلان‌ها'],JSON_UNESCAPED_UNICODE); exit; }
    $db = new DashboardTestPDO($root . '/.local/dashboard-tools-qa.sqlite'); dashboard_test_seed($db);
    $query = dash_notification_query($db, $context);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '') !== 'fixture-csrf') { http_response_code(419); echo '{}'; exit; }
        dash_notification_mark($db, $query, $context, ($_POST['action'] ?? '') === 'read-all' ? null : (string)($_POST['key'] ?? ''));
    }
    echo json_encode(dash_notification_feed($db, $query, $context, ($_GET['filter'] ?? '') === 'unread' ? 'unread' : 'all', max(1,(int)($_GET['page'] ?? 1))), JSON_UNESCAPED_UNICODE);
    exit;
}
if ($path === '/dashboard/index.php') {
    if (isset($_GET['fault'])) setcookie('dashboard-test-fault', $_GET['fault']==='1'?'1':'0', ['path'=>'/','samesite'=>'Lax']);
    if (isset($_GET['role'])) {
        $restricted = $_GET['role'] === 'user';
        setcookie('dashboard-test-role', $restricted ? 'user' : 'admin', ['path'=>'/','samesite'=>'Lax']);
    }
    $series = ['labels'=>['خرداد','تیر','مرداد','شهریور','مهر','آبان'],'campaigns'=>[120,160,140,210,195,240],'periods'=>[0,0,0,0,0,0],'free'=>[60,80,70,110,80,140],'totalDonors'=>[120,135,150,160,175,187],'activeDonors'=>[35,40,38,50,51,63]];
    render_template($public . '/dashboard/index.php', [
      'isSuper'=>!$restricted,'ACTIVE_BRANCH_ROW'=>['name'=>'دفتر مرکزی مکسا · دادهٔ آزمایشی'],
      'U'=>['full_name'=>'مدیر آزمایشی','username'=>'preview'],
      'SERVER'=>['stats'=>['totalDonors'=>187,'activeDonors'=>63,'totalCollected'=>62000000,'activeCampaigns'=>4,'trends'=>[]],
        'chart'=>array_fill_keys(['day','week','month','year'],$series),
        'donors'=>[['name'=>'حامی آزمایشی','count'=>7,'amt'=>2500000]],
        'tx'=>[['name'=>'حامی آزمایشی','amt'=>2500000,'typeKey'=>'campaign','typeLabel'=>'حمایت از خدمات مراقبتی','stKey'=>'ok','stLabel'=>'موفق','date'=>'۱۴۰۵/۰۸/۱۸ – ۱۲:۳۰','ref'=>'TEST-12345']]],
      'MENU'=>['isSuper'=>!$restricted,'isBranchAdmin'=>!$restricted,'isHqView'=>true,'isNewsEditor'=>!$restricted,'canMaxapedia'=>!$restricted,'can'=>$restricted ? ['tickets'=>true] : array_fill_keys(['hero','news','events','partners','campaigns','courses','pages','financial','feedback','medical','stands','tickets','donation_impacts','macsa_stories','branch_intro'],true),
        'csrf'=>'fixture-csrf','branches'=>[['id'=>1,'name'=>'دفتر مرکزی مکسا','status'=>'active','is_hq'=>1],['id'=>2,'name'=>'شعبهٔ آزمایشی','status'=>'active','is_hq'=>0]],'activeBranch'=>1,'activeBranchName'=>'دفتر مرکزی مکسا']
    ]);
    exit;
}
if ($path === '/dashboard/medical-records.php') {
    $id = (int)($_GET['id'] ?? 0);
    $record = ['id'=>201,'branch_name'=>'شعبهٔ آزمایشی','full_name'=>'بیمار آزمایشی','phone'=>'09000000000','age'=>45,'province'=>'استان آزمایشی','city'=>'شهر آزمایشی','cancer_type'=>'اطلاعات آزمایشی','diagnosis_status'=>'ثبت اولیه','description'=>'اطلاعات این پرونده صرفاً برای بررسی رابط کاربری است.','status'=>'new','created_at'=>date('Y-m-d H:i:s')];
    render_template($public . '/dashboard/medical-records.php', ['records'=>[$record],'filesByRecord'=>[],'recordId'=>$id,'statusFilter'=>$_GET['status']??'','search'=>'','dbError'=>null]);
    exit;
}
if (str_starts_with($path, '/dashboard/')) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/font.css"><title>صفحهٔ آزمایشی | مکسا</title><style>body{font-family:Vazirmatn,sans-serif;padding:28px;background:#f8f9fa;color:#2f3437}.wrap{max-width:1100px;margin:auto}table{min-width:900px;width:100%;background:white;border-collapse:collapse}td,th{padding:18px;text-align:right}button,input{font:inherit;padding:12px;margin:4px}button{cursor:pointer}input{width:280px}form{margin-top:20px}</style></head><body><main class="wrap"><h1>صفحهٔ آزمایشی برای کنترل ناوبری</h1><p>این صفحه دادهٔ واقعی ندارد.</p><table><thead><tr><th>شماره</th><th>عنوان</th><th>شعبه</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody><tr data-id="17"><td>۱۷</td><td>مورد آزمایشی</td><td>مکسا</td><td>پیش‌نویس</td><td><button onclick="document.getElementById(\'result\').textContent=\'ویرایش باز شد\'">ویرایش</button><button disabled>حذف غیرمجاز</button><button hidden>عملیات پنهان</button></td></tr></tbody></table><form onsubmit="event.preventDefault();document.getElementById(\'result\').textContent=\'ذخیره آزمایشی انجام شد\'"><label for="testTitle">عنوان آزمایشی</label><input id="testTitle" name="title" required><button type="submit">ذخیره آزمایشی</button></form><p id="result" role="status"></p></main></body></html>';
    exit;
}
http_response_code(404);

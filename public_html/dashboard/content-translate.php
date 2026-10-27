<?php
declare(strict_types=1);
require_once __DIR__.'/_guard.php';
require_once __DIR__.'/../core/content-translation.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try{
    if($_SERVER['REQUEST_METHOD']!=='POST')throw new RuntimeException('درخواست مجاز نیست.',405);
    // Return a standard JSON 403 instead of the legacy HTML/419 response.
    $token=$_POST['csrf_token']??($_SERVER['HTTP_X_CSRF_TOKEN']??'');
    if(!is_string($token)||empty($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$token))throw new RuntimeException('درخواست معتبر نیست؛ صفحه را تازه کنید و دوباره تلاش کنید.',403);
    foreach(['type','id','locale']as $field)if(isset($_POST[$field])&&!is_string($_POST[$field]))throw new InvalidArgumentException('اطلاعات درخواست معتبر نیست.',422);
    $type=$_POST['type']??'';$id=$_POST['id']??'';$locale=$_POST['locale']??'';
    if(strlen($id)>190)throw new InvalidArgumentException('شناسه نامعتبر است.',422);
    content_access($pdo,$type,$id);
    $raw=$_POST['fields']??'';
    if(!is_string($raw)||strlen($raw)>1000000)throw new InvalidArgumentException('حجم محتوا برای ترجمه بیش از حد مجاز است.',413);
    try{$fields=json_decode($raw,true,32,JSON_THROW_ON_ERROR);}catch(JsonException $error){throw new InvalidArgumentException('ساختار محتوا معتبر نیست.',422);}
    if(!is_array($fields))throw new InvalidArgumentException('ساختار محتوا معتبر نیست.',422);
    $namespace=(string)(dash_user()['id']??0).':'.dash_active_branch_id();
    session_write_close();
    @set_time_limit(90);
    $translated=content_translate_fields($type,$locale,$fields,content_translation_config(),$namespace);
    echo json_encode(['fields'=>$translated,'model'=>CONTENT_TRANSLATION_MODEL],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
}catch(Throwable $error){
    $status=$error->getCode();http_response_code(in_array($status,[403,405,409,413,422,429,502,503,504],true)?$status:403);
    if($status===429)header('Retry-After: 60');
    echo json_encode(['message'=>in_array($status,[403,405,409,413,422,429,502,503,504],true)||$error instanceof InvalidArgumentException?$error->getMessage():'ترجمه انجام نشد؛ دسترسی یا تنظیمات سرویس را بررسی کنید.'],JSON_UNESCAPED_UNICODE);
}
